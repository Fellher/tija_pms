<?php
/**
 * LeaveHandover
 *
 * Provides enterprise-grade handover tracking for leave applications.
 */
class LeaveHandover
{
    /**
     * Determine whether a handover is required based on policies.
     * Enhanced to support employee context for role/job group/job level targeting.
     *
     * @param int $entityID Entity ID
     * @param int $leaveTypeID Leave type ID
     * @param int $noOfDays Number of days
     * @param object $DBConn Database connection
     * @param int|null $employeeID Optional employee ID for context-aware policy matching
     * @return array Policy check result
     */
    public static function check_handover_policy($entityID, $leaveTypeID, $noOfDays, $DBConn, $employeeID = null)
    {
        if (!$entityID || !isset($noOfDays)) {
            return array(
                'required' => false,
                'policy' => null
            );
        }

        // If employee ID provided, use enhanced context-aware matching
        if ($employeeID && class_exists('LeaveHandoverPolicy')) {
            return LeaveHandoverPolicy::get_policy_by_employee_context($employeeID, $entityID, $leaveTypeID, $noOfDays, $DBConn);
        }

        // Fallback to original entity-wide matching
        $params = array(
            array($entityID, 'i')
        );
        $where = "p.entityID = ? AND p.Lapsed = 'N' AND p.Suspended = 'N'
                  AND p.effectiveDate <= CURRENT_DATE()
                  AND (p.expiryDate IS NULL OR p.expiryDate >= CURRENT_DATE())
                  AND (p.policyScope = 'entity_wide' OR p.policyScope IS NULL)";

        if (!empty($leaveTypeID)) {
            $where .= " AND (p.leaveTypeID = ? OR p.leaveTypeID IS NULL)";
            $params[] = array($leaveTypeID, 'i');
        }

        $sql = "SELECT p.* FROM tija_leave_handover_policies p
                WHERE {$where}
                ORDER BY
                    CASE WHEN p.leaveTypeID IS NULL THEN 1 ELSE 0 END ASC,
                    p.minHandoverDays DESC
                LIMIT 1";

        $policyRows = $DBConn->fetch_all_rows($sql, $params);
        if (!$policyRows || count($policyRows) === 0) {
            return array('required' => false, 'policy' => null);
        }

        $policy = is_object($policyRows[0]) ? $policyRows[0] : (object)$policyRows[0];
        $required = ($policy->isMandatory === 'Y') && ((int)$noOfDays >= (int)$policy->minHandoverDays);

        return array(
            'required' => $required,
            'policy' => $policy
        );
    }

    /**
     * Ensure a handover record exists for a leave application and replace items.
     *
     * @param array $handoverPayload Structured array of items + assignments.
     */
    public static function upsert_handover($leaveApplicationID, $employeeID, $entityID, $orgDataID, $policyID, $handoverPayload, $DBConn)
    {
        if (!$leaveApplicationID || !$employeeID) {
            return null;
        }

        $handover = self::get_handover_by_application($leaveApplicationID, $DBConn);
        $nomineeID = isset($handoverPayload['nomineeID']) && !empty($handoverPayload['nomineeID'])
            ? (int)$handoverPayload['nomineeID']
            : null;

        if ($handover) {
            $handoverID = $handover->handoverID;
            $DBConn->update_table('tija_leave_handovers', array(
                'LastUpdate' => date('Y-m-d H:i:s'),
                'policyID' => $policyID,
                'handoverStatus' => 'pending',
                'nomineeID' => $nomineeID
            ), array('handoverID' => $handoverID));

            self::soft_delete_children($handoverID, $DBConn);
        } else {
            $handoverData = array(
                'leaveApplicationID' => $leaveApplicationID,
                'employeeID' => $employeeID,
                'entityID' => $entityID,
                'orgDataID' => $orgDataID,
                'policyID' => $policyID,
                'nomineeID' => $nomineeID,
                'handoverStatus' => 'pending',
                'handoverDate' => date('Y-m-d H:i:s'),
                'DateAdded' => date('Y-m-d H:i:s'),
                'LastUpdate' => date('Y-m-d H:i:s'),
                'Lapsed' => 'N',
                'Suspended' => 'N'
            );

            if (!$DBConn->insert_data('tija_leave_handovers', $handoverData)) {
                throw new Exception('Unable to create handover record.');
            }

            $handoverID = $DBConn->lastInsertId();
        }

        $items = isset($handoverPayload['items']) && is_array($handoverPayload['items'])
            ? $handoverPayload['items']
            : array();

        $allAssignments = array();

        foreach ($items as $item) {
            $itemID = self::add_handover_item($handoverID, $item, $DBConn);

            if (!$itemID) {
                continue;
            }

            if (!empty($item['assignees']) && is_array($item['assignees'])) {
                foreach ($item['assignees'] as $assigneeID) {
                    $assignment = self::assign_handover_item(
                        $handoverID,
                        $itemID,
                        (int)$assigneeID,
                        $employeeID,
                        $DBConn
                    );

                    if ($assignment) {
                        $allAssignments[] = $assignment;
                    }
                }
            }
        }

        self::refresh_handover_status($handoverID, $DBConn);
        self::notify_assignees($handoverID, $allAssignments, $DBConn);
        self::notify_handover_submission($handoverID, $employeeID, $nomineeID, $entityID, $orgDataID, $DBConn);

        return array(
            'handoverID' => $handoverID,
            'assignments' => $allAssignments
        );
    }

    /**
     * Insert a handover item.
     */
    public static function add_handover_item($handoverID, $itemData, $DBConn)
    {
        if (!$handoverID || empty($itemData['itemTitle'])) {
            return null;
        }

        $data = array(
            'handoverID' => $handoverID,
            'itemType' => isset($itemData['itemType']) ? Utility::clean_string($itemData['itemType']) : 'other',
            'itemTitle' => Utility::clean_string($itemData['itemTitle']),
            'itemDescription' => isset($itemData['itemDescription']) ? Utility::clean_string($itemData['itemDescription']) : null,
            'projectID' => isset($itemData['projectID']) && $itemData['projectID'] !== '' ? (int)$itemData['projectID'] : null,
            'taskID' => isset($itemData['taskID']) && $itemData['taskID'] !== '' ? (int)$itemData['taskID'] : null,
            'priority' => isset($itemData['priority']) ? Utility::clean_string($itemData['priority']) : 'medium',
            'dueDate' => !empty($itemData['dueDate']) ? Utility::clean_string($itemData['dueDate']) : null,
            'instructions' => isset($itemData['instructions']) ? Utility::clean_string($itemData['instructions']) : null,
            'isMandatory' => (isset($itemData['isMandatory']) && $itemData['isMandatory'] === 'N') ? 'N' : 'Y',
            'DateAdded' => date('Y-m-d H:i:s'),
            'LastUpdate' => date('Y-m-d H:i:s'),
            'Lapsed' => 'N',
            'Suspended' => 'N'
        );

        if ($DBConn->insert_data('tija_leave_handover_items', $data)) {
            return $DBConn->lastInsertId();
        }

        return null;
    }

    /**
     * Assign a handover item to another staff member.
     */
    public static function assign_handover_item($handoverID, $handoverItemID, $assignedToID, $assignedByID, $DBConn, $status = 'pending')
    {
        if (!$handoverID || !$assignedToID || !$assignedByID) {
            return null;
        }

        $data = array(
            'handoverID' => $handoverID,
            'handoverItemID' => $handoverItemID,
            'assignedToID' => $assignedToID,
            'assignedByID' => $assignedByID,
            'assignmentDate' => date('Y-m-d H:i:s'),
            'confirmationStatus' => in_array($status, array('pending','acknowledged','confirmed','rejected'), true) ? $status : 'pending',
            'DateAdded' => date('Y-m-d H:i:s'),
            'LastUpdate' => date('Y-m-d H:i:s'),
            'Lapsed' => 'N',
            'Suspended' => 'N'
        );

        if ($DBConn->insert_data('tija_leave_handover_assignments', $data)) {
            $assignmentID = $DBConn->lastInsertId();
            return array(
                'assignmentID' => $assignmentID,
                'assignedToID' => $assignedToID
            );
        }

        return null;
    }

    /**
     * Confirm a handover assignment with checklist results.
     */
    public static function confirm_handover_assignment($assignmentID, $confirmationData, $DBConn)
    {
        if (!$assignmentID) {
            throw new Exception('Assignment ID is required.');
        }

        $assignment = self::get_assignment($assignmentID, $DBConn);
        if (!$assignment) {
            throw new Exception('Assigned handover task not found.');
        }

        $status = isset($confirmationData['readyToTakeOver']) && $confirmationData['readyToTakeOver'] === 'Y'
            ? 'confirmed'
            : 'acknowledged';

        $update = array(
            'confirmationStatus' => $status,
            'confirmedDate' => date('Y-m-d H:i:s'),
            'confirmationComments' => isset($confirmationData['additionalNotes']) ? Utility::clean_string($confirmationData['additionalNotes']) : null,
            'LastUpdate' => date('Y-m-d H:i:s')
        );

        $DBConn->update_table('tija_leave_handover_assignments', $update, array('assignmentID' => $assignmentID));

        $confirmation = array(
            'assignmentID' => $assignmentID,
            'handoverItemID' => $assignment->handoverItemID,
            'briefed' => isset($confirmationData['briefed']) ? Utility::clean_string($confirmationData['briefed']) : 'Y',
            'briefedDate' => !empty($confirmationData['briefedDate']) ? Utility::clean_string($confirmationData['briefedDate']) : date('Y-m-d H:i:s'),
            'trained' => isset($confirmationData['trained']) ? Utility::clean_string($confirmationData['trained']) : 'not_required',
            'trainedDate' => !empty($confirmationData['trainedDate']) ? Utility::clean_string($confirmationData['trainedDate']) : null,
            'hasCredentials' => isset($confirmationData['hasCredentials']) ? Utility::clean_string($confirmationData['hasCredentials']) : 'not_required',
            'credentialsDetails' => isset($confirmationData['credentialsDetails']) ? Utility::clean_string($confirmationData['credentialsDetails']) : null,
            'hasTools' => isset($confirmationData['hasTools']) ? Utility::clean_string($confirmationData['hasTools']) : 'not_required',
            'toolsDetails' => isset($confirmationData['toolsDetails']) ? Utility::clean_string($confirmationData['toolsDetails']) : null,
            'hasDocuments' => isset($confirmationData['hasDocuments']) ? Utility::clean_string($confirmationData['hasDocuments']) : 'not_required',
            'documentsDetails' => isset($confirmationData['documentsDetails']) ? Utility::clean_string($confirmationData['documentsDetails']) : null,
            'readyToTakeOver' => isset($confirmationData['readyToTakeOver']) ? Utility::clean_string($confirmationData['readyToTakeOver']) : 'N',
            'additionalNotes' => isset($confirmationData['additionalNotes']) ? Utility::clean_string($confirmationData['additionalNotes']) : null,
            'confirmedByID' => isset($confirmationData['confirmedByID']) ? (int)$confirmationData['confirmedByID'] : $assignment->assignedToID,
            'confirmedDate' => date('Y-m-d H:i:s'),
            'DateAdded' => date('Y-m-d H:i:s'),
            'LastUpdate' => date('Y-m-d H:i:s'),
            'Lapsed' => 'N',
            'Suspended' => 'N'
        );

        $DBConn->insert_data('tija_leave_handover_confirmations', $confirmation);

        self::refresh_handover_status($assignment->handoverID, $DBConn);

        return true;
    }

    /**
     * Return holistic status counts for a leave application's handover.
     */
    public static function get_handover_status($leaveApplicationID, $DBConn)
    {
        $handover = self::get_handover_by_application($leaveApplicationID, $DBConn);
        if (!$handover) {
            return array(
                'handoverStatus' => 'not_required',
                'totalAssignments' => 0,
                'confirmedAssignments' => 0,
                'pendingAssignments' => 0
            );
        }

        $countsSql = "SELECT
                COUNT(*) as totalAssignments,
                SUM(CASE WHEN confirmationStatus = 'confirmed' THEN 1 ELSE 0 END) as confirmedAssignments,
                SUM(CASE WHEN confirmationStatus IN ('pending','acknowledged') THEN 1 ELSE 0 END) as pendingAssignments
            FROM tija_leave_handover_assignments
            WHERE handoverID = ?
            AND Lapsed = 'N'
            AND Suspended = 'N'";

        $counts = $DBConn->fetch_all_rows($countsSql, array(array($handover->handoverID, 'i')));
        $counts = $counts && isset($counts[0]) ? (array)$counts[0] : array();

        return array(
            'handoverStatus' => $handover->handoverStatus,
            'totalAssignments' => (int)($counts['totalAssignments'] ?? 0),
            'confirmedAssignments' => (int)($counts['confirmedAssignments'] ?? 0),
            'pendingAssignments' => (int)($counts['pendingAssignments'] ?? 0)
        );
    }

    /**
     * Provide a comprehensive handover report.
     */
    public static function get_handover_report($leaveApplicationID, $DBConn)
    {
        $handover = self::get_handover_by_application($leaveApplicationID, $DBConn);
        if (!$handover) {
            return null;
        }

        $itemsSql = "SELECT i.*, a.assignmentID, a.assignedToID, a.confirmationStatus,
                            a.confirmedDate, p.projectName, t.projectTaskName,
                            CONCAT(emp.FirstName, ' ', emp.Surname) as assigneeName
                     FROM tija_leave_handover_items i
                     LEFT JOIN tija_leave_handover_assignments a ON i.handoverItemID = a.handoverItemID
                     LEFT JOIN people emp ON a.assignedToID = emp.ID
                     LEFT JOIN tija_projects p ON i.projectID = p.projectID
                     LEFT JOIN tija_project_tasks t ON i.taskID = t.projectTaskID
                     WHERE i.handoverID = ?
                     AND i.Lapsed = 'N'
                     AND i.Suspended = 'N'";

        $rows = $DBConn->fetch_all_rows($itemsSql, array(array($handover->handoverID, 'i')));

        return array(
            'handover' => $handover,
            'items' => $rows ?: array()
        );
    }

    /**
     * Get assignments waiting for a specific user.
     */
    public static function get_assignments_for_user($userID, $DBConn, $statusFilter = array('pending','acknowledged'))
    {
        if (!$userID) {
            return array();
        }

        $placeholders = implode(',', array_fill(0, count($statusFilter), '?'));
        $params = array(
            array($userID, 'i')
        );
        foreach ($statusFilter as $status) {
            $params[] = array($status, 's');
        }

        $sql = "SELECT a.*, i.itemTitle, i.itemDescription, i.priority, h.leaveApplicationID,
                       p.projectTaskName, pr.projectName,
                       CONCAT(emp.FirstName, ' ', emp.Surname) as assignedByName
                FROM tija_leave_handover_assignments a
                LEFT JOIN tija_leave_handover_items i ON a.handoverItemID = i.handoverItemID
                LEFT JOIN tija_leave_handovers h ON a.handoverID = h.handoverID
                LEFT JOIN tija_project_tasks p ON i.taskID = p.projectTaskID
                LEFT JOIN tija_projects pr ON i.projectID = pr.projectID
                LEFT JOIN people emp ON a.assignedByID = emp.ID
                WHERE a.assignedToID = ?
                AND a.confirmationStatus IN ({$placeholders})
                AND a.Lapsed = 'N' AND a.Suspended = 'N'";

        return $DBConn->fetch_all_rows($sql, $params) ?: array();
    }

    /**
     * Soft delete existing child records before re-adding.
     */
    private static function soft_delete_children($handoverID, $DBConn)
    {
        $now = date('Y-m-d H:i:s');
        $DBConn->update_table('tija_leave_handover_items', array(
            'Lapsed' => 'Y',
            'LastUpdate' => $now
        ), array('handoverID' => $handoverID));

        $DBConn->update_table('tija_leave_handover_assignments', array(
            'Lapsed' => 'Y',
            'LastUpdate' => $now
        ), array('handoverID' => $handoverID));
    }

    private static function get_handover_by_application($leaveApplicationID, $DBConn)
    {
        if (!$leaveApplicationID) {
            return null;
        }

        $rows = $DBConn->fetch_all_rows(
            "SELECT * FROM tija_leave_handovers WHERE leaveApplicationID = ? AND Lapsed = 'N' LIMIT 1",
            array(array($leaveApplicationID, 'i'))
        );

        if (!$rows || count($rows) === 0) {
            return null;
        }

        return is_object($rows[0]) ? $rows[0] : (object)$rows[0];
    }

    private static function get_assignment($assignmentID, $DBConn)
    {
        $rows = $DBConn->fetch_all_rows(
            "SELECT * FROM tija_leave_handover_assignments WHERE assignmentID = ? LIMIT 1",
            array(array($assignmentID, 'i'))
        );
        if (!$rows || count($rows) === 0) {
            return null;
        }
        return is_object($rows[0]) ? $rows[0] : (object)$rows[0];
    }

    /**
     * Update aggregated handover status + leave application metadata.
     */
    private static function refresh_handover_status($handoverID, $DBConn)
    {
        $handover = $DBConn->fetch_all_rows(
            "SELECT * FROM tija_leave_handovers WHERE handoverID = ? LIMIT 1",
            array(array($handoverID, 'i'))
        );
        if (!$handover || count($handover) === 0) {
            return;
        }
        $handover = is_object($handover[0]) ? $handover[0] : (object)$handover[0];

        $summary = $DBConn->fetch_all_rows(
            "SELECT
                COUNT(*) AS totalAssignments,
                SUM(CASE WHEN confirmationStatus = 'confirmed' THEN 1 ELSE 0 END) AS confirmedAssignments
             FROM tija_leave_handover_assignments
             WHERE handoverID = ?
             AND Lapsed = 'N' AND Suspended = 'N'",
            array(array($handoverID, 'i'))
        );

        $summary = $summary && isset($summary[0]) ? (array)$summary[0] : array('totalAssignments' => 0, 'confirmedAssignments' => 0);
        $total = (int)$summary['totalAssignments'];
        $confirmed = (int)$summary['confirmedAssignments'];

        $status = 'pending';
        if ($total === 0) {
            $status = 'pending';
        } elseif ($confirmed === 0) {
            $status = 'in_progress';
        } elseif ($confirmed < $total) {
            $status = 'partial';
        } elseif ($confirmed >= $total) {
            $status = 'completed';
        }

        $completionDate = ($status === 'completed') ? date('Y-m-d H:i:s') : null;

        $DBConn->update_table('tija_leave_handovers', array(
            'handoverStatus' => $status,
            'completionDate' => $completionDate,
            'LastUpdate' => date('Y-m-d H:i:s')
        ), array('handoverID' => $handoverID));

        $DBConn->update_table('tija_leave_applications', array(
            'handoverStatus' => $status === 'pending' ? 'pending' : $status,
            'handoverCompletedDate' => $completionDate
        ), array('leaveApplicationID' => $handover->leaveApplicationID));

        if ($status === 'completed' && class_exists('Notification')) {
            $leaveDetails = $DBConn->fetch_all_rows(
                "SELECT la.*, CONCAT(emp.FirstName, ' ', emp.Surname) AS employeeName
                 FROM tija_leave_applications la
                 LEFT JOIN people emp ON la.employeeID = emp.ID
                 WHERE la.leaveApplicationID = ?
                 LIMIT 1",
                array(array($handover->leaveApplicationID, 'i'))
            );

            if ($leaveDetails && isset($leaveDetails[0])) {
                $leave = is_object($leaveDetails[0]) ? $leaveDetails[0] : (object)$leaveDetails[0];

                $supervisor = Employee::get_direct_report($leave->employeeID, $DBConn);
                $supervisorID = $supervisor ? $supervisor->ID : null;
                $recipientIDs = array_filter(array($supervisorID, $leave->employeeID));

                foreach ($recipientIDs as $recipientID) {
                    if (!$recipientID) {
                        continue;
                    }
                    Notification::create(array(
                        'eventSlug' => 'leave_handover_completed',
                        'userId' => $recipientID,
                        'originatorId' => $leave->employeeID,
                        'data' => array(
                            'employee_name' => $leave->employeeName ?? 'Employee',
                            'leave_type' => $leave->leaveTypeName ?? 'Leave',
                            'start_date' => isset($leave->startDate) ? date('M j, Y', strtotime($leave->startDate)) : '',
                            'end_date' => isset($leave->endDate) ? date('M j, Y', strtotime($leave->endDate)) : '',
                            'application_id' => $leave->leaveApplicationID,
                            'handover_id' => $handover->handoverID ?? null,
                            'handover_status' => $status
                        ),
                        'link' => '?s=user&ss=leave&p=view_leave_application&id=' . $leave->leaveApplicationID,
                        'entityID' => $leave->entityID,
                        'orgDataID' => $leave->orgDataID,
                        'segmentType' => 'leave_application',
                        'segmentID' => $leave->leaveApplicationID,
                        'priority' => 'medium'
                    ), $DBConn);
                }
            }
        }
    }

    /**
     * Notify assignees that new handover tasks require action.
     */
    private static function notify_assignees($handoverID, $assignments, $DBConn)
    {
        if (empty($assignments) || !class_exists('Notification')) {
            return;
        }

        $handover = self::get_handover_context($handoverID, $DBConn);
        if (!$handover) {
            return;
        }

        $assigneeNames = array();

        foreach ($assignments as $assignment) {
            if (empty($assignment['assignedToID'])) {
                continue;
            }

            $assigneeID = (int)$assignment['assignedToID'];

            if (!isset($assigneeNames[$assigneeID])) {
                $assigneeDetails = Employee::employees(array('ID' => $assigneeID), true, $DBConn);
                if ($assigneeDetails) {
                    $assigneeNames[$assigneeID] = trim(
                        ($assigneeDetails->FirstName ?? '') . ' ' . ($assigneeDetails->Surname ?? '')
                    );
                } else {
                    $assigneeNames[$assigneeID] = 'Team Member';
                }
            }

            $assigneeName = $assigneeNames[$assigneeID];

            Notification::create(array(
                'eventSlug' => 'leave_handover_assignment',
                'userId' => $assigneeID,
                'originatorId' => $handover->employeeID,
                'data' => array(
                    'employee_name' => $handover->employeeName ?? 'Employee',
                    'leave_type' => $handover->leaveTypeName ?? 'Leave',
                    'start_date' => isset($handover->startDate) ? date('M j, Y', strtotime($handover->startDate)) : '',
                    'end_date' => isset($handover->endDate) ? date('M j, Y', strtotime($handover->endDate)) : '',
                    'handover_id' => $handoverID,
                    'assignment_id' => $assignment['assignmentID'] ?? null,
                    'application_id' => $handover->leaveApplicationID ?? null,
                    'nominee_name' => $assigneeName,
                    'handover_link' => '?s=user&ss=leave&p=peer_handover_response&handoverID=' . $handoverID
                ),
                'link' => '?s=user&ss=leave&p=peer_handover_response&handoverID=' . $handoverID,
                'entityID' => $handover->entityID ?? null,
                'orgDataID' => $handover->orgDataID ?? null,
                'segmentType' => 'leave_application',
                'segmentID' => $handover->leaveApplicationID ?? null,
                'priority' => 'high'
            ), $DBConn);
        }
    }

    /**
     * Notify the applicant that their handover plan has been recorded.
     */
    private static function notify_handover_submission($handoverID, $employeeID, $nomineeID, $entityID, $orgDataID, $DBConn)
    {
        if (!class_exists('Notification') || !$employeeID) {
            return;
        }

        $handover = self::get_handover_context($handoverID, $DBConn);
        if (!$handover) {
            return;
        }

        $nomineeName = $handover->nomineeName ?? null;
        if (!$nomineeName && $nomineeID) {
            $nomineeDetails = Employee::employees(array('ID' => $nomineeID), true, $DBConn);
            if ($nomineeDetails) {
                $nomineeName = trim(($nomineeDetails->FirstName ?? '') . ' ' . ($nomineeDetails->Surname ?? ''));
            }
        }

        Notification::create(array(
            'eventSlug' => 'leave_handover_submitted',
            'userId' => $employeeID,
            'originatorId' => $employeeID,
            'data' => array(
                'employee_name' => $handover->employeeName ?? 'Employee',
                'nominee_name' => $nomineeName ?? 'Nominated Team Member',
                'leave_type' => $handover->leaveTypeName ?? 'Leave',
                'start_date' => isset($handover->startDate) ? date('M j, Y', strtotime($handover->startDate)) : '',
                'end_date' => isset($handover->endDate) ? date('M j, Y', strtotime($handover->endDate)) : '',
                'handover_id' => $handoverID,
                'application_id' => $handover->leaveApplicationID ?? null,
                'handover_link' => '?s=user&ss=leave&p=view_leave_application&id=' . ($handover->leaveApplicationID ?? $handoverID)
            ),
            'link' => '?s=user&ss=leave&p=view_leave_application&id=' . ($handover->leaveApplicationID ?? $handoverID),
            'entityID' => $entityID,
            'orgDataID' => $orgDataID,
            'segmentType' => 'leave_application',
            'segmentID' => $handover->leaveApplicationID ?? null,
            'priority' => 'medium'
        ), $DBConn);
    }

    /**
     * Fetch contextual handover + leave information.
     */
    private static function get_handover_context($handoverID, $DBConn)
    {
        if (!$handoverID) {
            return null;
        }

        $rows = $DBConn->fetch_all_rows(
            "SELECT h.*, la.startDate, la.endDate, la.employeeID, la.leaveApplicationID,
                    la.entityID, la.orgDataID,
                    lt.leaveTypeName,
                    CONCAT(emp.FirstName, ' ', emp.Surname) AS employeeName,
                    CONCAT(nom.FirstName, ' ', nom.Surname) AS nomineeName
             FROM tija_leave_handovers h
             LEFT JOIN tija_leave_applications la ON h.leaveApplicationID = la.leaveApplicationID
             LEFT JOIN people emp ON la.employeeID = emp.ID
             LEFT JOIN people nom ON h.nomineeID = nom.ID
             LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
             WHERE h.handoverID = ?
             LIMIT 1",
            array(array($handoverID, 'i'))
        );

        if (!$rows || !isset($rows[0])) {
            return null;
        }

        return is_object($rows[0]) ? $rows[0] : (object)$rows[0];
    }
}
?>


