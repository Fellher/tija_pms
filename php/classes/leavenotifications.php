<?php
/**
 * Leave Notifications Helper Class
 * Handles all notification triggers for leave-related events
 *
 * @version 1.0
 * @date 2025-10-21
 */

class LeaveNotifications {

    /**
     * Notify when leave application is submitted
     * Sends confirmation to employee and alerts to approvers
     */
    public static function notifyLeaveSubmitted($leaveApplicationID, $DBConn) {
        try {
            // Get leave application details
            $leave = self::getLeaveApplicationDetails($leaveApplicationID, $DBConn);

            if (!$leave) {
                return array('success' => false, 'message' => 'Leave application not found');
            }
            if (is_object($leave)) {
                $leave = (array) $leave;
            }

            $results = array();

            // 1. Send confirmation to employee
            $employeeNotif = Notification::create(array(
                'eventSlug' => 'leave_application_submitted',
                'userId' => $leave['employeeID'],
                'originatorId' => $leave['employeeID'],
                'data' => array(
                    'employee_name' => $leave['employeeName'],
                    'employee_id' => $leave['employeeID'],
                    'leave_type' => $leave['leaveTypeName'],
                    'start_date' => date('M j, Y', strtotime($leave['startDate'])),
                    'end_date' => date('M j, Y', strtotime($leave['endDate'])),
                    'total_days' => $leave['noOfDays'],
                    'leave_reason' => $leave['leaveComments'] ?? 'No reason provided',
                    'application_id' => $leaveApplicationID,
                    'application_link' => '?s=user&ss=leave&p=my_applications&id=' . $leaveApplicationID
                ),
                'link' => '?s=user&ss=leave&p=my_applications&id=' . $leaveApplicationID,
                'entityID' => $leave['entityID'],
                'orgDataID' => $leave['orgDataID'],
                'segmentType' => 'leave_application',
                'segmentID' => $leaveApplicationID,
                'priority' => 'medium'
            ), $DBConn);

            $results['employee'] = $employeeNotif;

            // Log notification creation for employee
            if (is_array($employeeNotif) && isset($employeeNotif['notifications'])) {
                $channelsUsed = array();
                foreach ($employeeNotif['notifications'] as $notif) {
                    if (isset($notif['channelSlug'])) {
                        $channelsUsed[] = $notif['channelSlug'];
                    }
                }
                error_log("LeaveNotifications::notifyLeaveSubmitted - Employee notification created. Channels: " . implode(', ', $channelsUsed) . " for application ID: {$leaveApplicationID}");
            }

            // 2. Notify first level approvers
            $approvers = self::getNextApprovers($leaveApplicationID, $DBConn);
            if (is_array($approvers)) {
                $approvers = array_map(function($approver) {
                    return is_object($approver) ? (array)$approver : $approver;
                }, $approvers);
            }

            if ($approvers && count($approvers) > 0) {
                foreach ($approvers as $approver) {
                    $approverNotif = Notification::create(array(
                        'eventSlug' => 'leave_pending_approval',
                        'userId' => $approver['approverUserID'],
                        'originatorId' => $leave['employeeID'],
                        'data' => array(
                            'employee_name' => $leave['employeeName'],
                            'employee_id' => $leave['employeeID'],
                            'leave_type' => $leave['leaveTypeName'],
                            'start_date' => date('M j, Y', strtotime($leave['startDate'])),
                            'end_date' => date('M j, Y', strtotime($leave['endDate'])),
                            'total_days' => $leave['noOfDays'],
                            'application_id' => $leaveApplicationID,
                            'approval_level' => $approver['stepOrder'],
                            'approver_name' => $approver['approverName'],
                            'application_link' => '?s=user&ss=leave&p=pending_approvals&id=' . $leaveApplicationID
                        ),
                        'link' => '?s=user&ss=leave&p=pending_approvals&id=' . $leaveApplicationID,
                        'entityID' => $leave['entityID'],
                        'orgDataID' => $leave['orgDataID'],
                        'segmentType' => 'leave_application',
                        'segmentID' => $leaveApplicationID,
                        'priority' => 'high'
                    ), $DBConn);

                    $results['approvers'][] = $approverNotif;
                }
            }

            return array('success' => true, 'results' => $results);

        } catch (Exception $e) {
            error_log("Leave notification error: " . $e->getMessage());
            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    /**
     * Send reminder notifications to pending approvers
     */
    public static function sendApprovalReminder($leaveApplicationID, $requestedByID, $DBConn, $targetApproverUserID = null) {
        try {
            $leaveRecord = self::getLeaveApplicationDetails($leaveApplicationID, $DBConn);

            if (!$leaveRecord) {
                return array('success' => false, 'message' => 'Leave application not found');
            }
            $leave = is_object($leaveRecord) ? (array)$leaveRecord : $leaveRecord;

            $pendingApprovers = self::getPendingApprovers($leaveApplicationID, $DBConn);
            if (!$pendingApprovers || count($pendingApprovers) === 0) {
                return array('success' => false, 'message' => 'There are no pending approvers to remind');
            }

            if ($targetApproverUserID) {
                $pendingApprovers = array_filter($pendingApprovers, function($approver) use ($targetApproverUserID) {
                    $approver = is_object($approver) ? (array)$approver : $approver;
                    return isset($approver['approverUserID']) && (int)$approver['approverUserID'] === (int)$targetApproverUserID;
                });

                if (count($pendingApprovers) === 0) {
                    return array('success' => false, 'message' => 'Selected approver is not pending or not part of the workflow');
                }
            }

            $results = array();
            foreach ($pendingApprovers as $approver) {
                $approver = is_object($approver) ? (array)$approver : $approver;
                if (empty($approver['approverUserID'])) {
                    continue;
                }

                $notification = Notification::create(array(
                    'eventSlug' => 'leave_pending_approval',
                    'userId' => $approver['approverUserID'],
                    'originatorId' => $requestedByID,
                    'data' => array(
                        'employee_name' => $leave['employeeName'],
                        'employee_id' => $leave['employeeID'],
                        'leave_type' => $leave['leaveTypeName'],
                        'start_date' => date('M j, Y', strtotime($leave['startDate'])),
                        'end_date' => date('M j, Y', strtotime($leave['endDate'])),
                        'total_days' => $leave['noOfDays'],
                        'application_id' => $leaveApplicationID,
                        'approval_level' => $approver['stepOrder'] ?? null,
                        'approver_name' => $approver['approverName'] ?? '',
                        'application_link' => '?s=user&ss=leave&p=pending_approvals&id=' . $leaveApplicationID,
                        'reminder' => true
                    ),
                    'link' => '?s=user&ss=leave&p=pending_approvals&id=' . $leaveApplicationID,
                    'entityID' => $leave['entityID'],
                    'orgDataID' => $leave['orgDataID'],
                    'segmentType' => 'leave_application',
                    'segmentID' => $leaveApplicationID,
                    'priority' => 'high'
                ), $DBConn);

                $results[] = $notification;
            }

            $notifiedCount = count(array_filter($results, function($item) {
                return is_array($item) ? ($item['success'] ?? true) : true;
            }));

            return array(
                'success' => true,
                'message' => 'Reminder notifications sent',
                'notified' => $notifiedCount
            );

        } catch (Exception $e) {
            error_log("Leave reminder error: " . $e->getMessage());
            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    /**
     * Notify when leave is approved at a step
     */
    public static function notifyLeaveApproved($leaveApplicationID, $approverID, $stepID, $isFinalApproval = false, $comments = null, $DBConn) {
        try {
            $leaveRecord = self::getLeaveApplicationDetails($leaveApplicationID, $DBConn);

            if (!$leaveRecord) {
                return array('success' => false, 'message' => 'Leave application not found');
            }
            $leave = is_object($leaveRecord) ? (array) $leaveRecord : $leaveRecord;

            // Get approver details
            $approver = Data::users(array('ID' => $approverID), true, $DBConn);
            $approverName = $approver ? $approver->FirstName . ' ' . $approver->Surname : 'Approver';

            $results = array();

            if ($isFinalApproval) {
                // Final approval - notify employee via in-app and email
                // Get email and in-app channel IDs
                $emailChannel = $DBConn->fetch_all_rows(
                    "SELECT * FROM tija_notification_channels WHERE channelSlug = ? AND Suspended = 'N'",
                    array(array('email', 's'))
                );
                $inAppChannel = $DBConn->fetch_all_rows(
                    "SELECT * FROM tija_notification_channels WHERE channelSlug = ? AND Suspended = 'N'",
                    array(array('in_app', 's'))
                );

                // Ensure both channels are included
                $channels = array();
                if ($inAppChannel && count($inAppChannel) > 0) {
                    $inApp = is_object($inAppChannel[0]) ? (array)$inAppChannel[0] : $inAppChannel[0];
                    $channels[] = isset($inApp['channelID']) ? (int)$inApp['channelID'] : null;
                }
                if ($emailChannel && count($emailChannel) > 0) {
                    $email = is_object($emailChannel[0]) ? (array)$emailChannel[0] : $emailChannel[0];
                    $channels[] = isset($email['channelID']) ? (int)$email['channelID'] : null;
                }

                // Remove null values
                $channels = array_filter($channels);

                // If no channels found, default to in-app
                if (empty($channels) && $inAppChannel && count($inAppChannel) > 0) {
                    $inApp = is_object($inAppChannel[0]) ? (array)$inAppChannel[0] : $inAppChannel[0];
                    $channels[] = isset($inApp['channelID']) ? (int)$inApp['channelID'] : null;
                }

                $employeeNotif = Notification::create(array(
                    'eventSlug' => 'leave_approved',
                    'userId' => $leave['employeeID'],
                    'originatorId' => $approverID,
                    'channels' => array_values($channels), // Explicitly include both in-app and email
                    'data' => array(
                        'employee_name' => $leave['employeeName'],
                        'employee_id' => $leave['employeeID'],
                        'leave_type' => $leave['leaveTypeName'],
                        'start_date' => date('M j, Y', strtotime($leave['startDate'])),
                        'end_date' => date('M j, Y', strtotime($leave['endDate'])),
                        'total_days' => $leave['noOfDays'],
                        'application_id' => $leaveApplicationID,
                        'approver_name' => $approverName,
                        'approver_comments' => $comments ?? 'No comments',
                        'application_link' => '?s=user&ss=leave&p=my_applications&id=' . $leaveApplicationID
                    ),
                    'link' => '?s=user&ss=leave&p=my_applications&id=' . $leaveApplicationID,
                    'entityID' => $leave['entityID'],
                    'orgDataID' => $leave['orgDataID'],
                    'segmentType' => 'leave_application',
                    'segmentID' => $leaveApplicationID,
                    'priority' => 'high'
                ), $DBConn);

                $results['employee'] = $employeeNotif;

                // Log notification creation for employee approval
                if (is_array($employeeNotif) && isset($employeeNotif['notifications'])) {
                    $channelsUsed = array();
                    foreach ($employeeNotif['notifications'] as $notif) {
                        if (isset($notif['channelSlug'])) {
                            $channelsUsed[] = $notif['channelSlug'];
                        }
                    }
                    error_log("LeaveNotifications::notifyLeaveApproved - Employee approval notification created. Channels: " . implode(', ', $channelsUsed) . " for application ID: {$leaveApplicationID}");

                    // Check if email channel was included
                    $hasEmail = in_array('email', $channelsUsed);
                    if (!$hasEmail) {
                        error_log("LeaveNotifications::notifyLeaveApproved - WARNING: Email channel not included in notification channels! Application ID: {$leaveApplicationID}, Employee ID: {$leave['employeeID']}");
                    }
                }

                // Also send direct email notification as fallback/backup
                if (isset($leave['employeeEmail']) && !empty($leave['employeeEmail'])) {
                    try {
                        self::sendLeaveApprovalEmail($leaveApplicationID, $leave, $approverName, $comments, $DBConn);
                    } catch (Exception $e) {
                        error_log("Failed to send leave approval email: " . $e->getMessage());
                    }
                }
            } else {
                // Intermediate approval - notify employee of progress
                $employeeNotif = Notification::create(array(
                    'eventSlug' => 'leave_application_submitted', // Use generic event for progress update
                    'userId' => $leave['employeeID'],
                    'originatorId' => $approverID,
                    'data' => array(
                        'employee_name' => $leave['employeeName'],
                        'employee_id' => $leave['employeeID'],
                        'leave_type' => $leave['leaveTypeName'],
                        'start_date' => date('M j, Y', strtotime($leave['startDate'])),
                        'end_date' => date('M j, Y', strtotime($leave['endDate'])),
                        'total_days' => $leave['noOfDays'],
                        'application_id' => $leaveApplicationID,
                        'application_link' => '?s=user&ss=leave&p=my_applications&id=' . $leaveApplicationID
                    ),
                    'link' => '?s=user&ss=leave&p=my_applications&id=' . $leaveApplicationID,
                    'entityID' => $leave['entityID'],
                    'orgDataID' => $leave['orgDataID'],
                    'segmentType' => 'leave_application',
                    'segmentID' => $leaveApplicationID,
                    'priority' => 'medium'
                ), $DBConn);

                $results['employee'] = $employeeNotif;

                // Notify next level approvers
                $nextApprovers = self::getNextApprovers($leaveApplicationID, $DBConn);

                if ($nextApprovers && count($nextApprovers) > 0) {
                    foreach ($nextApprovers as $nextApprover) {
                        $approverNotif = Notification::create(array(
                            'eventSlug' => 'leave_pending_approval',
                            'userId' => $nextApprover['approverUserID'],
                            'originatorId' => $leave['employeeID'],
                            'data' => array(
                                'employee_name' => $leave['employeeName'],
                                'employee_id' => $leave['employeeID'],
                                'leave_type' => $leave['leaveTypeName'],
                                'start_date' => date('M j, Y', strtotime($leave['startDate'])),
                                'end_date' => date('M j, Y', strtotime($leave['endDate'])),
                                'total_days' => $leave['noOfDays'],
                                'application_id' => $leaveApplicationID,
                                'approval_level' => $nextApprover['stepOrder'],
                                'approver_name' => $nextApprover['approverName'],
                                'application_link' => '?s=user&ss=leave&p=pending_approvals&id=' . $leaveApplicationID
                            ),
                            'link' => '?s=user&ss=leave&p=pending_approvals&id=' . $leaveApplicationID,
                            'entityID' => $leave['entityID'],
                            'orgDataID' => $leave['orgDataID'],
                            'segmentType' => 'leave_application',
                            'segmentID' => $leaveApplicationID,
                            'priority' => 'high'
                        ), $DBConn);

                        $results['nextApprovers'][] = $approverNotif;
                    }
                }
            }

            return array('success' => true, 'results' => $results);

        } catch (Exception $e) {
            error_log("Leave approval notification error: " . $e->getMessage());
            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    /**
     * Notify when leave is rejected
     */
    public static function notifyLeaveRejected($leaveApplicationID, $approverID, $rejectionReason, $DBConn) {
        try {
            $leaveRecord = self::getLeaveApplicationDetails($leaveApplicationID, $DBConn);

            if (!$leaveRecord) {
                return array('success' => false, 'message' => 'Leave application not found');
            }
            $leave = is_object($leaveRecord) ? (array) $leaveRecord : $leaveRecord;

            // Get approver details
            $approver = Data::users(array('ID' => $approverID), true, $DBConn);
            $approverName = $approver ? $approver->FirstName . ' ' . $approver->Surname : 'Approver';

            // Notify employee
            $employeeNotif = Notification::create(array(
                'eventSlug' => 'leave_rejected',
                'userId' => $leave['employeeID'],
                'originatorId' => $approverID,
                'data' => array(
                    'employee_name' => $leave['employeeName'],
                    'employee_id' => $leave['employeeID'],
                    'leave_type' => $leave['leaveTypeName'],
                    'start_date' => date('M j, Y', strtotime($leave['startDate'])),
                    'end_date' => date('M j, Y', strtotime($leave['endDate'])),
                    'total_days' => $leave['noOfDays'],
                    'application_id' => $leaveApplicationID,
                    'approver_name' => $approverName,
                    'rejection_reason' => $rejectionReason ?? 'No reason provided',
                    'application_link' => '?s=user&ss=leave&p=my_applications&id=' . $leaveApplicationID
                ),
                'link' => '?s=user&ss=leave&p=my_applications&id=' . $leaveApplicationID,
                'entityID' => $leave['entityID'],
                'orgDataID' => $leave['orgDataID'],
                'segmentType' => 'leave_application',
                'segmentID' => $leaveApplicationID,
                'priority' => 'high'
            ), $DBConn);

            return array('success' => true, 'results' => array('employee' => $employeeNotif));

        } catch (Exception $e) {
            error_log("Leave rejection notification error: " . $e->getMessage());
            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    /**
     * Notify when leave is cancelled
     */
    public static function notifyLeaveCancelled($leaveApplicationID, $cancelledBy, $reason, $DBConn) {
        try {
            $leaveRecord = self::getLeaveApplicationDetails($leaveApplicationID, $DBConn);

            if (!$leaveRecord) {
                return array('success' => false, 'message' => 'Leave application not found');
            }
            $leave = is_object($leaveRecord) ? (array) $leaveRecord : $leaveRecord;

            // Notify employee if cancelled by someone else
            if ($cancelledBy != $leave['employeeID']) {
                $employeeNotif = Notification::create(array(
                    'eventSlug' => 'leave_cancelled',
                    'userId' => $leave['employeeID'],
                    'originatorId' => $cancelledBy,
                    'data' => array(
                        'employee_name' => $leave['employeeName'],
                        'employee_id' => $leave['employeeID'],
                        'leave_type' => $leave['leaveTypeName'],
                        'start_date' => date('M j, Y', strtotime($leave['startDate'])),
                        'end_date' => date('M j, Y', strtotime($leave['endDate'])),
                        'total_days' => $leave['noOfDays'],
                        'application_id' => $leaveApplicationID,
                        'reason' => $reason ?? 'No reason provided',
                        'application_link' => '?s=user&ss=leave&p=my_applications&id=' . $leaveApplicationID
                    ),
                    'link' => '?s=user&ss=leave&p=my_applications&id=' . $leaveApplicationID,
                    'entityID' => $leave['entityID'],
                    'orgDataID' => $leave['orgDataID'],
                    'segmentType' => 'leave_application',
                    'segmentID' => $leaveApplicationID,
                    'priority' => 'medium'
                ), $DBConn);
            }

            // Notify pending approvers that the leave has been cancelled
            $approvers = self::getPendingApprovers($leaveApplicationID, $DBConn);
            $results = array();

            if ($approvers && count($approvers) > 0) {
                foreach ($approvers as $approver) {
                    $approverNotif = Notification::create(array(
                        'eventSlug' => 'leave_cancelled',
                        'userId' => $approver['approverUserID'],
                        'originatorId' => $cancelledBy,
                        'data' => array(
                            'employee_name' => $leave['employeeName'],
                            'employee_id' => $leave['employeeID'],
                            'leave_type' => $leave['leaveTypeName'],
                            'start_date' => date('M j, Y', strtotime($leave['startDate'])),
                            'end_date' => date('M j, Y', strtotime($leave['endDate'])),
                            'total_days' => $leave['noOfDays'],
                            'application_id' => $leaveApplicationID,
                            'reason' => $reason ?? 'Cancelled by employee',
                            'application_link' => '?s=user&ss=leave&p=pending_approvals'
                        ),
                        'link' => '?s=user&ss=leave&p=pending_approvals',
                        'entityID' => $leave['entityID'],
                        'orgDataID' => $leave['orgDataID'],
                        'segmentType' => 'leave_application',
                        'segmentID' => $leaveApplicationID,
                        'priority' => 'low'
                    ), $DBConn);

                    $results['approvers'][] = $approverNotif;
                }
            }

            return array('success' => true, 'results' => $results);

        } catch (Exception $e) {
            error_log("Leave cancellation notification error: " . $e->getMessage());
            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    /**
     * Get leave application details with related info
     */
    private static function getLeaveApplicationDetails($leaveApplicationID, $DBConn) {
        $sql = "SELECT la.*,
                       lt.leaveTypeName,
                       CONCAT(p.FirstName, ' ', p.Surname) as employeeName,
                       la.entityID, la.orgDataID
                FROM tija_leave_applications la
                INNER JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
                INNER JOIN people p ON la.employeeID = p.ID
                WHERE la.leaveApplicationID = ?";

        $result = $DBConn->fetch_all_rows($sql, array(array($leaveApplicationID, 'i')));

        return is_array($result) && count($result) > 0 ? $result[0] : false;
    }

    /**
     * Get next approvers for a leave application
     */
    private static function getNextApprovers($leaveApplicationID, $DBConn) {
        // Ensure required tables exist before querying
        $requiredTables = array(
            'tija_leave_approval_instances',
            'tija_leave_approval_steps',
            'tija_leave_approval_step_approvers'
        );

        foreach ($requiredTables as $tableName) {
            $tableCheckSql = "SHOW TABLES LIKE '" . addslashes($tableName) . "'";
            $tableCheck = $DBConn->fetch_all_rows($tableCheckSql, array());
            if (!$tableCheck || count($tableCheck) === 0) {
                return array();
            }
        }

        $sql = "SELECT DISTINCT sa.*,
                       s.stepOrder,
                       s.stepDescription,
                       CONCAT(p.FirstName, ' ', p.Surname) as approverName
                FROM tija_leave_approval_instances i
                INNER JOIN tija_leave_approval_steps s ON i.policyID = s.policyID
                INNER JOIN tija_leave_approval_step_approvers sa ON s.stepID = sa.stepID
                LEFT JOIN tija_leave_approval_actions a
                       ON a.instanceID = i.instanceID
                      AND a.stepID = s.stepID
                      AND a.approverID = sa.approverID
                LEFT JOIN people p ON sa.approverUserID = p.ID
                WHERE i.leaveApplicationID = ?
                  AND (a.actionID IS NULL OR a.action = 'pending')
                AND s.Suspended = 'N'
                AND sa.Suspended = 'N'
                ORDER BY s.stepOrder, sa.notificationOrder
                LIMIT 10";

        $records = $DBConn->fetch_all_rows($sql, array(array($leaveApplicationID, 'i')));

        if (!$records) {
            return array();
        }

        return array_map(function($record) {
            $record = is_object($record) ? (array) $record : $record;
            return $record;
        }, $records);
    }


    /**
     * Get pending approvers for a leave application
     */
    private static function getPendingApprovers($leaveApplicationID, $DBConn) {
        return self::getNextApprovers($leaveApplicationID, $DBConn);
    }

    /**
     * Fetch pending approvals assigned to an approver
     */
    public static function getPendingApprovalsForUser($approverUserID, $DBConn, $leaveApplicationID = null) {
        $params = array(array($approverUserID, 'i'));

        $applicationFilter = '';
        if (!empty($leaveApplicationID)) {
            $applicationFilter = ' AND e.leaveApplicationID = ?';
            $params[] = array($leaveApplicationID, 'i');
        }

        $sql = "SELECT
                    i.instanceID,
                    i.policyID,
                    i.currentStepOrder,
                    e.leaveApplicationID,
                    e.startDate,
                    e.endDate,
                    e.noOfDays,
                    e.leaveStatusID,
                    e.leaveComments,
                    e.dateApplied,
                    e.halfDayLeave,
                    e.halfDayPeriod,
                    e.entityID,
                    e.orgDataID,
                    e.LastUpdate,
                    e.LastUpdateByID,
                    lt.leaveTypeName,
                    lt.leaveTypeDescription,
                    s.stepID,
                    s.stepOrder,
                    s.stepDescription,
                    CONCAT(emp.FirstName, ' ', emp.Surname) AS employeeName,
                    emp.Email AS employeeEmail,
                    emp.ID AS employeeID,
                    jt.jobTitle
                FROM tija_leave_approval_instances i
                INNER JOIN tija_leave_applications e ON i.leaveApplicationID = e.leaveApplicationID
                INNER JOIN tija_leave_approval_steps s
                    ON i.policyID = s.policyID
                   AND s.stepOrder = COALESCE(NULLIF(i.currentStepOrder, 0), 1)
                INNER JOIN tija_leave_approval_step_approvers sa ON s.stepID = sa.stepID
                LEFT JOIN tija_leave_types lt ON e.leaveTypeID = lt.leaveTypeID
                LEFT JOIN people emp ON e.employeeID = emp.ID
                LEFT JOIN user_details ud ON emp.ID = ud.ID
                LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
                WHERE sa.approverUserID = ?
                  AND NOT EXISTS (
                        SELECT 1 FROM tija_leave_approval_actions act
                        WHERE act.instanceID = i.instanceID
                          AND act.stepID = s.stepID
                          AND act.approverID = sa.approverID
                          AND act.action IN ('approved', 'rejected', 'delegated', 'escalated', 'cancelled', 'info_requested')
                    )
                  AND e.Lapsed = 'N'
                  AND e.Suspended = 'N'
                  {$applicationFilter}
                ORDER BY e.DateAdded ASC";

        $records = $DBConn->fetch_all_rows($sql, $params);

        if (!$records) {
            return array();
        }

        return array_map(function($record) {
            return is_object($record) ? (array) $record : $record;
        }, $records);
    }

    /**
     * Verify an approver is assigned to a specific step
     */
    public static function verifyApproverForStep($stepID, $approverUserID, $DBConn) {
        $sql = "SELECT sa.approverID AS stepApproverID,
                       s.stepOrder,
                       s.isRequired,
                       s.policyID,
                       (SELECT COUNT(*) FROM tija_leave_approval_steps WHERE policyID = s.policyID AND Suspended = 'N') as totalSteps
                FROM tija_leave_approval_step_approvers sa
                INNER JOIN tija_leave_approval_steps s ON sa.stepID = s.stepID
                WHERE sa.stepID = ?
                  AND sa.approverUserID = ?
                  AND sa.Suspended = 'N'";

        $records = $DBConn->fetch_all_rows($sql, array(
            array($stepID, 'i'),
            array($approverUserID, 'i')
        ));

        if (!$records || count($records) === 0) {
            return false;
        }

        $record = $records[0];
        return is_object($record) ? (array) $record : $record;
    }

    /**
     * Check if an approver has already actioned a step
     */
    public static function hasExistingApprovalAction($instanceID, $stepID, $stepApproverID, $DBConn) {
        $sql = "SELECT actionID FROM tija_leave_approval_actions
                WHERE instanceID = ? AND stepID = ? AND approverID = ?";

        $records = $DBConn->fetch_all_rows($sql, array(
            array($instanceID, 'i'),
            array($stepID, 'i'),
            array($stepApproverID, 'i')
        ));

        return $records && count($records) > 0;
    }

    /**
     * Persist an approval action
     */
    public static function recordApprovalAction($instanceID, $stepID, $stepApproverID, $approverUserID, $stepOrder, $action, $comments, $DBConn) {
        $actionStatus = $action === 'approve' ? 'approved' : 'rejected';
        $now = date('Y-m-d H:i:s');

        // Check column names
        $columnsCheck = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_actions", array());
        $columnNames = array();
        if ($columnsCheck && count($columnsCheck) > 0) {
            foreach ($columnsCheck as $col) {
                $col = is_object($col) ? (array)$col : $col;
                $columnNames[] = $col['Field'] ?? $col['field'] ?? '';
            }
        }

        $hasDecisionDate = in_array('decisionDate', $columnNames);
        $hasActionDate = in_array('actionDate', $columnNames);

        $actionData = array(
            'instanceID' => $instanceID,
            'stepID' => $stepID,
            'approverID' => $stepApproverID,
            'approverUserID' => $approverUserID,
            'action' => $actionStatus
        );

        if ($comments !== null && $comments !== '') {
            $actionData['comments'] = $comments;
        }

        if ($hasDecisionDate) {
            $actionData['decisionDate'] = $now;
        } elseif ($hasActionDate) {
            $actionData['actionDate'] = $now;
        }

        $result = $DBConn->insert_data('tija_leave_approval_actions', $actionData);

        if (!$result) {
            throw new Exception('Failed to record approval action');
        }

        return $DBConn->lastInsertId();
    }

    /**
     * Update leave application base fields
     */
    public static function updateLeaveApplication($leaveApplicationID, array $data, $DBConn) {
        if (!isset($data['LastUpdate'])) {
            $data['LastUpdate'] = date('Y-m-d H:i:s');
        }

        $result = $DBConn->update_table(
            'tija_leave_applications',
            $data,
            array('leaveApplicationID' => $leaveApplicationID)
        );

        if (!$result) {
            throw new Exception('Failed to update leave application');
        }

        return $result;
    }

    /**
     * Update workflow instance fields
     */
    public static function updateApprovalInstance($instanceID, array $data, $DBConn) {
        if (!isset($data['lastActionAt'])) {
            $data['lastActionAt'] = date('Y-m-d H:i:s');
        }

        $result = $DBConn->update_table(
            'tija_leave_approval_instances',
            $data,
            array('instanceID' => $instanceID)
        );

        if (!$result) {
            throw new Exception('Failed to update approval instance');
        }

        return $result;
    }

    /**
     * Advance workflow instance to the next step
     */
    public static function advanceApprovalInstance($instanceID, $policyID, $nextStepOrder, $approverUserID, $DBConn) {
        $nextStepID = self::getStepIdByOrder($policyID, $nextStepOrder, $DBConn);

        return self::updateApprovalInstance($instanceID, array(
            'currentStepOrder' => $nextStepOrder,
            'currentStepID' => $nextStepID !== null ? $nextStepID : 'NULL',
            'workflowStatus' => 'in_progress',
            'lastActionBy' => $approverUserID
        ), $DBConn);
    }

    /**
     * Resolve a step ID by policy/order
     */
    private static function getStepIdByOrder($policyID, $stepOrder, $DBConn) {
        $sql = "SELECT stepID
                FROM tija_leave_approval_steps
                WHERE policyID = ?
                  AND stepOrder = ?
                  AND Suspended = 'N'
                LIMIT 1";

        $records = $DBConn->fetch_all_rows($sql, array(
            array($policyID, 'i'),
            array($stepOrder, 'i')
        ));

        if (!$records || count($records) === 0) {
            return null;
        }

        $record = $records[0];
        $record = is_object($record) ? (array) $record : $record;

        return isset($record['stepID']) ? (int)$record['stepID'] : null;
    }

    /**
     * Remove/cancel pending notifications for a leave application
     *
     * @param int $leaveApplicationID Leave application ID
     * @param object $DBConn Database connection object
     * @return bool Success status
     */
    public static function removePendingNotifications($leaveApplicationID, $DBConn) {
        if (empty($leaveApplicationID)) {
            return false;
        }

        try {
            // Check if notifications table exists
            $tableCheck = $DBConn->fetch_all_rows("SHOW TABLES LIKE 'notifications'", array());
            if (!$tableCheck || count($tableCheck) === 0) {
                return false;
            }

            // Update pending notifications to cancelled/read status
            // We'll mark them as read/cancelled rather than delete for audit trail
            $sql = "UPDATE notifications
                    SET isRead = 'Y',
                        readAt = NOW(),
                        data = JSON_SET(COALESCE(data, '{}'), '$.cancelled', true, '$.cancelledReason', 'Application rejected')
                    WHERE segmentType = 'leave_application'
                    AND segmentID = ?
                    AND isRead = 'N'
                    AND (JSON_EXTRACT(COALESCE(data, '{}'), '$.application_id') = ? OR segmentID = ?)";

            $params = array(
                array($leaveApplicationID, 'i'),
                array($leaveApplicationID, 'i'),
                array($leaveApplicationID, 'i')
            );

            $result = $DBConn->query($sql, $params);
            return $result !== false;
        } catch (Exception $e) {
            error_log('Error removing pending notifications: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if approver has already acted on a step
     *
     * @param int $instanceID Instance ID
     * @param int $stepID Step ID
     * @param int $approverUserID Approver user ID
     * @param object $DBConn Database connection object
     * @return bool True if approver has already acted
     */
    public static function hasApproverActed($instanceID, $stepID, $approverUserID, $DBConn) {
        if (empty($instanceID) || empty($stepID) || empty($approverUserID)) {
            return false;
        }

        $sql = "SELECT actionID FROM tija_leave_approval_actions
                WHERE instanceID = ? AND stepID = ? AND approverUserID = ?
                LIMIT 1";

        $records = $DBConn->fetch_all_rows($sql, array(
            array($instanceID, 'i'),
            array($stepID, 'i'),
            array($approverUserID, 'i')
        ));

        return $records && count($records) > 0;
    }

    /**
     * Send email notification when leave is approved
     *
     * @param int $leaveApplicationID Leave application ID
     * @param array $leave Leave application details
     * @param string $approverName Name of the approver
     * @param string|null $comments Approval comments
     * @param object $DBConn Database connection
     * @return bool Success status
     */
    private static function sendLeaveApprovalEmail($leaveApplicationID, $leave, $approverName, $comments, $DBConn) {
        global $config;

        if (!isset($leave['employeeEmail']) || empty($leave['employeeEmail'])) {
            error_log("sendLeaveApprovalEmail: No email address for employee");
            return false;
        }

        try {
            // Check if email helper exists
            $emailHelperPath = __DIR__ . '/../functions/email_helper.php';
            if (!file_exists($emailHelperPath)) {
                error_log("sendLeaveApprovalEmail: Email helper not found at {$emailHelperPath}");
                return false;
            }

            require_once $emailHelperPath;

            // Prepare email content
            $employeeName = $leave['employeeName'] ?? 'Employee';
            $leaveType = $leave['leaveTypeName'] ?? 'Leave';
            $startDate = date('M j, Y', strtotime($leave['startDate']));
            $endDate = date('M j, Y', strtotime($leave['endDate']));
            $totalDays = $leave['noOfDays'] ?? 0;
            $applicationLink = isset($config['base']) ? $config['base'] . 'html/?s=user&ss=leave&p=my_applications&id=' . $leaveApplicationID : '#';

            $subject = "Leave Application Approved - {$leaveType}";

            $emailBody = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                        .content { background-color: #f9f9f9; padding: 20px; border-radius: 0 0 5px 5px; }
                        .info-box { background-color: white; padding: 15px; margin: 15px 0; border-left: 4px solid #4CAF50; }
                        .info-label { font-weight: bold; color: #666; }
                        .button { display: inline-block; padding: 12px 24px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
                        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>Leave Application Approved</h2>
                        </div>
                        <div class='content'>
                            <p>Dear {$employeeName},</p>

                            <p>We are pleased to inform you that your leave application has been <strong>approved</strong>.</p>

                            <div class='info-box'>
                                <p><span class='info-label'>Leave Type:</span> {$leaveType}</p>
                                <p><span class='info-label'>Start Date:</span> {$startDate}</p>
                                <p><span class='info-label'>End Date:</span> {$endDate}</p>
                                <p><span class='info-label'>Total Days:</span> {$totalDays} day(s)</p>
                                <p><span class='info-label'>Approved By:</span> {$approverName}</p>
                                " . (!empty($comments) ? "<p><span class='info-label'>Comments:</span> " . htmlspecialchars($comments) . "</p>" : "") . "
                            </div>

                            <p>You can view the details of your approved leave application by clicking the button below:</p>

                            <a href='{$applicationLink}' class='button'>View Leave Application</a>

                            <div class='footer'>
                                <p>This is an automated notification. Please do not reply to this email.</p>
                                <p>If you have any questions, please contact your HR department.</p>
                            </div>
                        </div>
                    </div>
                </body>
                </html>
            ";

            $emailText = "Dear {$employeeName},\n\n" .
                        "Your leave application has been approved.\n\n" .
                        "Leave Type: {$leaveType}\n" .
                        "Start Date: {$startDate}\n" .
                        "End Date: {$endDate}\n" .
                        "Total Days: {$totalDays} day(s)\n" .
                        "Approved By: {$approverName}\n" .
                        (!empty($comments) ? "Comments: {$comments}\n" : "") .
                        "\nView your application: {$applicationLink}";

            // Send email using email helper
            $result = send_email(
                $leave['employeeEmail'],
                $employeeName,
                $subject,
                $emailBody,
                $emailText,
                $config,
                false // No debug
            );

            if ($result['success']) {
                error_log("Leave approval email sent successfully to {$leave['employeeEmail']} for application {$leaveApplicationID}");
                return true;
            } else {
                error_log("Failed to send leave approval email: " . $result['message']);
                return false;
            }

        } catch (Exception $e) {
            error_log("Exception sending leave approval email: " . $e->getMessage());
            return false;
        }
    }
}

