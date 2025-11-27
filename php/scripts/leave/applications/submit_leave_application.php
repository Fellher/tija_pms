<?php
/**
 * Submit Leave Application Script
 *
 * Handles the submission of new leave applications with validation,
 * approval workflow setup, and notification triggers
 */
session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Get form data
    $leaveTypeId = isset($_POST['leaveTypeId']) ? Utility::clean_string($_POST['leaveTypeId']) : '';
    $leaveEntitlementId = isset($_POST['leaveEntitlementId']) ? Utility::clean_string($_POST['leaveEntitlementId']) : '';
    $employeeId = isset($_POST['employeeId']) ? Utility::clean_string($_POST['employeeId']) : '';
    $orgDataId = isset($_POST['orgDataId']) ? Utility::clean_string($_POST['orgDataId']) : '';
    $entityId = isset($_POST['entityId']) ? Utility::clean_string($_POST['entityId']) : '';
    $leavePeriodId = isset($_POST['leavePeriodId']) ? Utility::clean_string($_POST['leavePeriodId']) : '';
    $startDate = isset($_POST['startDate']) ? Utility::clean_string($_POST['startDate']) : '';
    $endDate = isset($_POST['endDate']) ? Utility::clean_string($_POST['endDate']) : '';
    $halfDayLeave = isset($_POST['halfDayLeave']) ? 'Y' : 'N';
    $halfDayPeriod = isset($_POST['halfDayPeriod']) ? Utility::clean_string($_POST['halfDayPeriod']) : '';
    $leaveReason = isset($_POST['leaveReason']) ? Utility::clean_string($_POST['leaveReason']) : '';
    $emergencyContact = isset($_POST['emergencyContact']) ? Utility::clean_string($_POST['emergencyContact']) : '';
    $handoverNotes = isset($_POST['handoverNotes']) ? Utility::clean_string($_POST['handoverNotes']) : '';
    $handoverPayloadRaw = isset($_POST['handoverPayload']) ? $_POST['handoverPayload'] : '';
    $submissionMode = isset($_POST['submissionMode']) ? strtolower(Utility::clean_string($_POST['submissionMode'])) : 'submit';
    $submissionMode = in_array($submissionMode, array('schedule', 'submit'), true) ? $submissionMode : 'submit';

    $handoverPayload = array();
    if (!empty($handoverPayloadRaw)) {
        $handoverPayload = json_decode($handoverPayloadRaw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode(['success' => false, 'message' => 'Invalid handover payload format']);
            exit;
        }
    }

    // Validate required fields
    if (empty($leaveTypeId) || empty($employeeId) || empty($startDate) || empty($endDate) || empty($leaveReason)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
        exit;
    }

    // Validate dates
    $startDateTime = new DateTime($startDate);
    $endDateTime = new DateTime($endDate);
    $today = new DateTime();

    if ($endDateTime < $startDateTime) {
        echo json_encode(['success' => false, 'message' => 'End date cannot be before start date']);
        exit;
    }

    if ($startDateTime < $today) {
        echo json_encode(['success' => false, 'message' => 'Leave start date cannot be in the past']);
        exit;
    }

    // Check for overlapping leave applications for the same employee
    // Only check applications that are not rejected (status 4) or cancelled (status 5)
    // Status IDs: 1=Draft, 2=Scheduled, 3=Pending, 4=Rejected, 5=Cancelled, 6=Approved
    $overlapCheckSQL = "SELECT la.leaveApplicationID, la.startDate, la.endDate,
                               lt.leaveTypeName, ls.leaveStatusName, la.leaveStatusID
                        FROM tija_leave_applications la
                        LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
                        LEFT JOIN tija_leave_status ls ON la.leaveStatusID = ls.leaveStatusID
                        WHERE la.employeeID = ?
                        AND la.entityID = ?
                        AND la.leaveStatusID NOT IN (4, 5) -- Exclude rejected and cancelled
                        AND la.Lapsed = 'N'
                        AND la.Suspended = 'N'
                        AND (
                            -- Overlap occurs when: existing_start <= new_end AND existing_end >= new_start
                            (la.startDate <= ? AND la.endDate >= ?)
                        )
                        ORDER BY la.startDate ASC
                        LIMIT 10";

    $overlappingApplications = $DBConn->fetch_all_rows($overlapCheckSQL, array(
        array($employeeId, 'i'),
        array($entityId, 'i'),
        array($endDate, 's'),   // existing_start <= new_end
        array($startDate, 's')  // existing_end >= new_start
    ));

    if ($overlappingApplications && count($overlappingApplications) > 0) {
        // Build list of overlapping applications
        $overlapList = array();
        foreach ($overlappingApplications as $overlap) {
            $overlap = is_object($overlap) ? (array)$overlap : $overlap;
            $overlapStartDate = date('M j, Y', strtotime($overlap['startDate']));
            $overlapEndDate = date('M j, Y', strtotime($overlap['endDate']));
            $overlapStatus = $overlap['leaveStatusName'] ?? 'Unknown';
            $overlapType = $overlap['leaveTypeName'] ?? 'Leave';
            $overlapID = isset($overlap['leaveApplicationID']) ? (int)$overlap['leaveApplicationID'] : 0;

            $overlapList[] = "Application #{$overlapID} ({$overlapType}) from {$overlapStartDate} to {$overlapEndDate} - Status: {$overlapStatus}";
        }

        $overlapMessage = "You already have " . (count($overlappingApplications) === 1 ? 'a leave application' : count($overlappingApplications) . ' leave applications') .
                         " that overlaps with the requested dates:\n\n" .
                         implode("\n", $overlapList) .
                         "\n\nPlease cancel the existing application(s) before submitting a new one, or choose different dates.";

        echo json_encode([
            'success' => false,
            'message' => $overlapMessage,
            'overlappingApplications' => $overlappingApplications
        ]);
        exit;
    }

    // Calculate number of days using Leave class method (excludes weekends and holidays)
    // Always use working days calculation - never use totalDays from POST as it includes weekends
    $noOfDays = Leave::calculate_working_days($startDate, $endDate, $entityId, $DBConn);

    if ($halfDayLeave === 'Y') {
        $noOfDays = 0.5;
    }

    // Note: We intentionally ignore any totalDays or workingDays from POST
    // The server-side calculation is the source of truth for working days

    // Determine whether structured handover is required (with employee context)
    $handoverPolicy = LeaveHandover::check_handover_policy($entityId, $leaveTypeId, $noOfDays, $DBConn, $employeeId);
    $handoverRequired = ($handoverPolicy['required'] ?? false) ? 'Y' : 'N';
    $policyDetails = $handoverPolicy['policy'] ?? null;
    $policyObject = $policyDetails ? (is_object($policyDetails) ? $policyDetails : (object)$policyDetails) : null;
    $policyID = $policyObject ? (int)$policyObject->policyID : null;
    $handoverItems = isset($handoverPayload['items']) && is_array($handoverPayload['items']) ? $handoverPayload['items'] : array();
    $nomineeID = isset($handoverPayload['nomineeID']) && !empty($handoverPayload['nomineeID']) ? (int)$handoverPayload['nomineeID'] : null;

    if ($handoverRequired === 'Y' && empty($handoverItems)) {
        echo json_encode([
            'success' => false,
            'message' => 'Handover report is required for this leave type. Please add at least one task/function to hand over.'
        ]);
        exit;
    }

    // Check leave balance
    $leaveBalances = Leave::calculate_leave_balances($employeeId, $entityId, $DBConn);
    $leaveTypeName = '';

    // Get leave type using Leave class method
    $leaveTypeObj = Leave::leave_types(array('leaveTypeID' => $leaveTypeId), true, $DBConn);
    if ($leaveTypeObj) {
        $leaveTypeName = strtolower(str_replace(' ', '_', $leaveTypeObj->leaveTypeName));
    }

    $leaveBalance = isset($leaveBalances[$leaveTypeName]) ? $leaveBalances[$leaveTypeName]['available'] : 0;
    if ($leaveBalance < $noOfDays) {
        echo json_encode(['success' => false, 'message' => 'Insufficient leave balance. Available: ' . $leaveBalance . ' days']);
        exit;
    }

    // Get current leave period if not provided
    if (empty($leavePeriodId)) {
        $currentPeriod = Leave::get_current_leave_period($entityId, $DBConn);
        $leavePeriodId = $currentPeriod ? $currentPeriod->leavePeriodID : null;
    }

    $leaveStatusId = 3;
    if ($submissionMode === 'schedule') {
        $leaveStatusId = 1;
    }

    // Prepare leave application data
    $handoverStatus = $handoverRequired === 'Y'
        ? 'pending'
        : (!empty($handoverItems) ? 'pending' : 'not_required');

    $leaveData = array(
        'leaveTypeID' => $leaveTypeId,
        'leaveEntitlementID' => $leaveEntitlementId,
        'employeeID' => $employeeId,
        'orgDataID' => $orgDataId,
        'entityID' => $entityId,
        'leavePeriodID' => $leavePeriodId,
        'startDate' => $startDate,
        'endDate' => $endDate,
        'noOfDays' => $noOfDays,
        'halfDayLeave' => $halfDayLeave,
        'halfDayPeriod' => $halfDayPeriod,
        'leaveComments' => $leaveReason,
        'emergencyContact' => $emergencyContact,
        'handoverNotes' => $handoverNotes,
        'handoverRequired' => $handoverRequired,
        'handoverStatus' => $handoverStatus,
        'leaveStatusID' => $leaveStatusId,
        'dateApplied' => ($submissionMode === 'submit') ? date('Y-m-d H:i:s') : null,
        'appliedByID' => ($submissionMode === 'submit') ? $employeeId : null,
        'DateAdded' => date('Y-m-d H:i:s'),
        'Lapsed' => 'N',
        'Suspended' => 'N'
    );

    // Insert leave application using mysqlConnect insert_data method
    $insertResult = $DBConn->insert_data('tija_leave_applications', $leaveData);

    if (!$insertResult) {
        echo json_encode(['success' => false, 'message' => 'Failed to create leave application']);
        exit;
    }

    $leaveApplicationId = $DBConn->lastInsertId();

    if (!$leaveApplicationId) {
        echo json_encode(['success' => false, 'message' => 'Failed to create leave application']);
        exit;
    }

    // Handle file uploads
    $uploadedFiles = [];
    if (isset($_FILES['supportingDocuments']) && !empty($_FILES['supportingDocuments']['name'][0])) {
        $uploadDir = '../../../uploads/leave_documents/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $files = $_FILES['supportingDocuments'];
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $fileName = $files['name'][$i];
                $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                $newFileName = $leaveApplicationId . '_' . time() . '_' . $i . '.' . $fileExtension;
                $filePath = $uploadDir . $newFileName;

                if (move_uploaded_file($files['tmp_name'][$i], $filePath)) {
                    $uploadedFiles[] = [
                        'leaveApplicationID' => $leaveApplicationId,
                        'fileName' => $fileName,
                        'filePath' => $filePath,
                        'fileSize' => $files['size'][$i],
                        'fileType' => $files['type'][$i],
                        'uploadedByID' => $employeeId,
                        'uploadDate' => date('Y-m-d H:i:s')
                    ];
                }
            }
        }

        // Save file information to database
        // Check if dedicated documents table exists
        $tableCheck = "SHOW TABLES LIKE 'tija_leave_documents'";
        $tableExists = $DBConn->fetch_all_rows($tableCheck, array());

        if ($tableExists && count($tableExists) > 0) {
            // Use dedicated documents table (preferred method)
            foreach ($uploadedFiles as $file) {
                $documentData = array(
                    'leaveApplicationID' => $file['leaveApplicationID'],
                    'fileName' => $file['fileName'],
                    'filePath' => $file['filePath'],
                    'fileSize' => $file['fileSize'],
                    'fileType' => $file['fileType'],
                    'uploadedByID' => $file['uploadedByID'],
                    'uploadDate' => $file['uploadDate'],
                    'documentType' => 'supporting',
                    'DateAdded' => date('Y-m-d H:i:s'),
                    'Lapsed' => 'N',
                    'Suspended' => 'N'
                );
                $DBConn->insert_data('tija_leave_documents', $documentData);
            }
        } else {
            // Fallback: Store file paths in leaveFiles column (legacy method)
            $filePaths = array_column($uploadedFiles, 'filePath');
            $leaveFilesStr = implode(',', $filePaths);
            $leaveFilesEncoded = base64_encode($leaveFilesStr);

            // Update the application with file paths
            $DBConn->update_table(
                'tija_leave_applications',
                array('leaveFiles' => $leaveFilesEncoded),
                array('leaveApplicationID' => $leaveApplicationId)
            );
        }
    }

    $notificationsSent = false;
    $workflowProcessed = false;
    $handoverID = null;
    $fsmStateID = null;

    // Initialize FSM if handover is required
    if ($handoverRequired === 'Y' && class_exists('LeaveHandoverFSM')) {
        $fsmStateID = LeaveHandoverFSM::initialize_fsm($leaveApplicationId, $employeeId, $DBConn);
    }

    if (($handoverRequired === 'Y' || !empty($handoverItems)) && class_exists('LeaveHandover')) {
        $handoverResult = LeaveHandover::upsert_handover(
            $leaveApplicationId,
            $employeeId,
            $entityId,
            $orgDataId,
            $policyID,
            array('items' => $handoverItems, 'nomineeID' => $nomineeID),
            $DBConn
        );

        if ($handoverResult && isset($handoverResult['handoverID'])) {
            $handoverID = $handoverResult['handoverID'];

            // Update handover with FSM state and nominee
            if ($fsmStateID) {
                $DBConn->update_table('tija_leave_handovers', array(
                    'fsmStateID' => $fsmStateID,
                    'nomineeID' => $nomineeID,
                    'LastUpdate' => date('Y-m-d H:i:s')
                ), array('handoverID' => $handoverID));
            }

            // If handover items are provided and submitted, transition to ST_01 (Handover Composition)
            if (!empty($handoverItems) && $submissionMode === 'submit' && class_exists('LeaveHandoverFSM')) {
                LeaveHandoverFSM::transition_state(
                    $leaveApplicationId,
                    LeaveHandoverFSM::TRIGGER_SUBMIT_DRAFT,
                    $employeeId,
                    array('handoverID' => $handoverID),
                    $DBConn
                );

                // If nominee is selected and handover is submitted, transition to ST_02 (Peer Negotiation)
                if ($nomineeID && !empty($handoverItems)) {
                    $deadlineHours = $policyObject && isset($policyObject->nomineeResponseDeadlineHours)
                        ? (int)$policyObject->nomineeResponseDeadlineHours
                        : 48;

                    LeaveHandoverFSM::transition_state(
                        $leaveApplicationId,
                        LeaveHandoverFSM::TRIGGER_SUBMIT_HANDOVER,
                        $employeeId,
                        array(
                            'handoverID' => $handoverID,
                            'nomineeID' => $nomineeID,
                            'deadlineHours' => $deadlineHours
                        ),
                        $DBConn
                    );

                    // Start timer
                    if (class_exists('LeaveHandoverTimer')) {
                        LeaveHandoverTimer::start_peer_response_timer($handoverID, $nomineeID, $deadlineHours, $DBConn);
                    }
                }
            }
        }
    }

    if ($submissionMode === 'submit') {
        $activeWorkflow = Leave::get_active_approval_workflow($entityId, $DBConn);

        if ($activeWorkflow) {
            $workflowProcessed = true;
            $policyID = is_object($activeWorkflow) ? $activeWorkflow->policyID : (is_array($activeWorkflow) ? $activeWorkflow['policyID'] : null);

            if ($policyID) {
                $instanceID = Leave::create_approval_instance($leaveApplicationId, $policyID, $DBConn);

                if ($instanceID) {
                    $approvers = Leave::get_workflow_approvers($policyID, $DBConn);

                    if (empty($approvers) || !is_array($approvers)) {
                        $approvers = Leave::resolve_dynamic_workflow_approvers($policyID, $employeeId, $DBConn);
                    }

                    if (!empty($approvers)) {
                        $employeeDetails = Employee::employees(array('ID' => $employeeId), true, $DBConn);
                        $employeeName = $employeeDetails ? ($employeeDetails->FirstName . ' ' . $employeeDetails->Surname) : 'Employee';

                        $maxStepOrder = 0;
                        foreach ($approvers as $approver) {
                            $stepOrder = isset($approver['stepOrder']) ? (int)$approver['stepOrder'] : 0;
                            if ($stepOrder > $maxStepOrder) {
                                $maxStepOrder = $stepOrder;
                            }
                        }

                        $notifiedCount = 0;
                        $notifiedUserIDs = array();

                        foreach ($approvers as $approver) {
                            if (empty($approver['approverUserID'])) {
                                continue;
                            }

                            $approverUserID = (int)$approver['approverUserID'];

                            if (in_array($approverUserID, $notifiedUserIDs, true)) {
                                continue;
                            }

                            $isFinalStep = isset($approver['stepOrder']) && (int)$approver['stepOrder'] === $maxStepOrder;

                            $notificationResult = Notification::create(array(
                                'eventSlug' => 'leave_pending_approval',
                                'userId' => $approverUserID,
                                'originatorId' => $employeeId,
                                'data' => array(
                                    'employee_id' => $employeeId,
                                    'employee_name' => $employeeName,
                                    'leave_type' => $leaveTypeObj ? $leaveTypeObj->leaveTypeName : 'Leave',
                                    'start_date' => date('M j, Y', strtotime($startDate)),
                                    'end_date' => date('M j, Y', strtotime($endDate)),
                                    'total_days' => $noOfDays,
                                    'application_id' => $leaveApplicationId,
                                    'approval_level' => $approver['stepOrder'] ?? 1,
                                    'step_name' => $approver['stepName'] ?? 'Approval Step',
                                    'approver_name' => $approver['approverName'] ?? 'Approver',
                                    'is_final_step' => $isFinalStep
                                ),
                                'link' => '?s=user&ss=leave&p=pending_approvals&id=' . $leaveApplicationId,
                                'entityID' => $entityId,
                                'orgDataID' => $orgDataId,
                                'segmentType' => 'leave_application',
                                'segmentID' => $leaveApplicationId,
                                'priority' => 'high'
                            ), $DBConn);

                            $success = !$notificationResult || !is_array($notificationResult)
                                ? (bool)$notificationResult
                                : ($notificationResult['success'] ?? true);

                            if ($success) {
                                $notifiedCount++;
                                $notifiedUserIDs[] = $approverUserID;
                            }
                        }

                        $hrManagers = Employee::get_hr_managers_for_entity($entityId, $DBConn);
                        if (!empty($hrManagers)) {
                            foreach ($hrManagers as $hrManager) {
                                $hrManagerID = is_object($hrManager) ? $hrManager->ID : (is_array($hrManager) ? $hrManager['ID'] : null);
                                if (empty($hrManagerID) || in_array((int)$hrManagerID, $notifiedUserIDs, true)) {
                                    continue;
                                }

                                $hrManagerName = is_object($hrManager)
                                    ? ($hrManager->FirstName . ' ' . $hrManager->Surname)
                                    : (is_array($hrManager) ? ($hrManager['FirstName'] . ' ' . $hrManager['Surname']) : 'HR Manager');

                                $notificationResult = Notification::create(array(
                                    'eventSlug' => 'leave_pending_approval',
                                    'userId' => $hrManagerID,
                                    'originatorId' => $employeeId,
                                    'data' => array(
                                        'employee_id' => $employeeId,
                                        'employee_name' => $employeeName,
                                        'leave_type' => $leaveTypeObj ? $leaveTypeObj->leaveTypeName : 'Leave',
                                        'start_date' => date('M j, Y', strtotime($startDate)),
                                        'end_date' => date('M j, Y', strtotime($endDate)),
                                        'total_days' => $noOfDays,
                                        'application_id' => $leaveApplicationId,
                                        'approval_level' => $maxStepOrder + 1,
                                        'step_name' => 'HR Manager Final Approval',
                                        'approver_name' => $hrManagerName,
                                        'is_final_step' => true,
                                        'is_hr_manager' => true
                                    ),
                                    'link' => '?s=user&ss=leave&p=pending_approvals&id=' . $leaveApplicationId,
                                    'entityID' => $entityId,
                                    'orgDataID' => $orgDataId,
                                    'segmentType' => 'leave_application',
                                    'segmentID' => $leaveApplicationId,
                                    'priority' => 'high'
                                ), $DBConn);

                                $success = !$notificationResult || !is_array($notificationResult)
                                    ? (bool)$notificationResult
                                    : ($notificationResult['success'] ?? true);

                                if ($success) {
                                    $notifiedCount++;
                                    $notifiedUserIDs[] = (int)$hrManagerID;
                                }
                            }
                        }

                        if ($notifiedCount > 0) {
                            $notificationsSent = true;
                        }

                        if (class_exists('LeaveNotifications')) {
                            LeaveNotifications::notifyLeaveSubmitted($leaveApplicationId, $DBConn);
                        }
                    }
                }
            }
        }

        // Fallback: If no workflow found, use legacy notification system
        if (!$workflowProcessed && class_exists('LeaveNotifications')) {
            $notificationResult = LeaveNotifications::notifyLeaveSubmitted($leaveApplicationId, $DBConn);
            $notificationsSent = $notificationResult['success'] ?? false;

            if (!$notificationsSent) {
                $employeeDetails = Employee::employees(array('ID' => $employeeId), true, $DBConn);
                $employeeName = $employeeDetails ? ($employeeDetails->FirstName . ' ' . $employeeDetails->Surname) : 'Employee';
                $fallbackApprovers = array();

                if ($employeeDetails) {
                    $directReport = Employee::get_direct_report($employeeDetails->ID, $DBConn);
                    if ($directReport && isset($directReport->ID)) {
                        $fallbackApprovers[$directReport->ID] = $directReport;
                    }

                    if (isset($employeeDetails->departmentID)) {
                        $departmentHead = Employee::get_department_head($employeeDetails->departmentID, $DBConn);
                        if ($departmentHead && isset($departmentHead->ID)) {
                            $fallbackApprovers[$departmentHead->ID] = $departmentHead;
                        }
                    }

                    $hrManager = Employee::get_hr_manager($employeeDetails->orgDataID ?? null, $employeeDetails->entityID ?? null, $DBConn);
                    if ($hrManager && isset($hrManager->ID)) {
                        $fallbackApprovers[$hrManager->ID] = $hrManager;
                    }
                }

                if (!empty($fallbackApprovers)) {
                    foreach ($fallbackApprovers as $approver) {
                        Notification::create(array(
                            'eventSlug' => 'leave_pending_approval',
                            'userId' => $approver->ID,
                            'originatorId' => $employeeId,
                            'data' => array(
                                'employee_id' => $employeeId,
                                'employee_name' => $employeeName,
                                'leave_type' => $leaveTypeObj ? $leaveTypeObj->leaveTypeName : 'Leave',
                                'start_date' => date('M j, Y', strtotime($startDate)),
                                'end_date' => date('M j, Y', strtotime($endDate)),
                                'total_days' => $noOfDays,
                                'application_id' => $leaveApplicationId,
                                'approver_name' => isset($approver->FirstName)
                                    ? ($approver->FirstName . ' ' . ($approver->Surname ?? ''))
                                    : 'Approver'
                            ),
                            'link' => '?s=user&ss=leave&p=pending_approvals&id=' . $leaveApplicationId,
                            'entityID' => $entityId,
                            'orgDataID' => $orgDataId,
                            'segmentType' => 'leave_application',
                            'segmentID' => $leaveApplicationId,
                            'priority' => 'high'
                        ), $DBConn);
                    }

                    $notificationsSent = true;
                }
            }
        }
    }

    echo json_encode([
        'success' => true,
        'message' => $submissionMode === 'submit'
            ? 'Leave application submitted successfully'
            : 'Leave scheduled successfully',
        'leaveApplicationId' => $leaveApplicationId,
        'uploadedFiles' => count($uploadedFiles),
        'submissionMode' => $submissionMode,
        'notificationsSent' => $notificationsSent,
        'workflowProcessed' => $workflowProcessed
    ]);

} catch (Exception $e) {
    $errorId = uniqid('leave_', true);
    error_log('[LEAVE_SUBMIT][' . $errorId . '] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

    $appEnv = getenv('APP_ENV');
    if (!$appEnv && defined('APP_ENV')) {
        $appEnv = APP_ENV;
    }
    $appEnv = $appEnv ?: 'production';
    $showDebugDetails = strtolower($appEnv) !== 'production';

    $response = [
        'success' => false,
        'message' => 'System error while submitting the leave application. Reference: ' . $errorId,
        'errorId' => $errorId
    ];

    if ($showDebugDetails) {
        $response['debug'] = [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ];
    }

    echo json_encode($response);
}
?>
