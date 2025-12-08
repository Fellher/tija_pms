<?php
/**
 * Process Leave Approval Action
 * Handles approve/reject actions and triggers notifications
 */

session_start();
$base = '../../../../';
set_include_path($base);
include 'php/includes.php';

header('Content-Type: application/json');

if (!$isValidUser) {
    echo json_encode(array('success' => false, 'message' => 'Unauthorized'));
    exit;
}

try {
    $userID = $userDetails->ID;

    // Get parameters
    $action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : '';
    $comments = isset($_POST['comments']) ? Utility::clean_string($_POST['comments']) : null;
    $leaveApplicationID = isset($_POST['leaveApplicationID']) ? (int)$_POST['leaveApplicationID'] : 0;
    $submittedStepID = isset($_POST['stepID']) && !empty($_POST['stepID']) ? (int)$_POST['stepID'] : null;
    $submittedStepOrder = isset($_POST['stepOrder']) && !empty($_POST['stepOrder']) ? (int)$_POST['stepOrder'] : null;

    // Validate inputs
    if (empty($action) || $leaveApplicationID === 0) {
        echo json_encode(array(
            'success' => false,
            'message' => 'Missing required parameters'
        ));
        exit;
    }

    if (!in_array($action, array('approve', 'reject'))) {
        echo json_encode(array(
            'success' => false,
            'message' => 'Invalid action'
        ));
        exit;
    }

    if ($action === 'reject' && (is_null($comments) || $comments === '')) {
        echo json_encode(array(
            'success' => false,
            'message' => 'Rejection comments are required'
        ));
        exit;
    }

    $leaveApplication = Leave::leave_applications_full(
        array('leaveApplicationID' => $leaveApplicationID),
        true,
        $DBConn
    );

    if (!$leaveApplication) {
        echo json_encode(array(
            'success' => false,
            'message' => 'Leave application not found'
        ));
        exit;
    }

    $leaveApplication = is_object($leaveApplication) ? $leaveApplication : (object)$leaveApplication;

    if ((int)$leaveApplication->leaveStatusID !== 3) { // Pending approval
        echo json_encode(array(
            'success' => false,
            'message' => 'This leave application is no longer pending approval'
        ));
        exit;
    }

    $permissions = Leave::check_leave_application_permissions($leaveApplication, $userID);
    $isHrManager = Employee::is_hr_manager($userID, $DBConn, $leaveApplication->entityID ?? null);
    $isDepartmentHead = Employee::is_department_head($userID, $DBConn);
    $entityID = $leaveApplication->entityID ?? null;

    if (empty($permissions['canApprove']) && !$isHrManager && !$isDepartmentHead) {
        echo json_encode(array(
            'success' => false,
            'message' => 'You are not authorized to approve/reject this request'
        ));
        exit;
    }

    // Check for workflow instance
    $lapsedCheck = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_instances LIKE 'Lapsed'", array());
    $hasLapsedColumn = ($lapsedCheck && count($lapsedCheck) > 0);

    $whereClause = "leaveApplicationID = ?";
    $params = array(array($leaveApplicationID, 'i'));

    if ($hasLapsedColumn) {
        $whereClause .= " AND Lapsed = 'N'";
    }

    $workflowInstance = $DBConn->fetch_all_rows(
        "SELECT * FROM tija_leave_approval_instances WHERE {$whereClause}",
        $params
    );

    $hasWorkflow = false;
    $instanceID = null;
    $policyID = null;
    $approvalType = 'parallel';
    $stepID = null;
    $stepOrder = null;
    $maxStepOrder = 0;
    $isFinalStep = false;
    $approverStepID = null;

    if ($workflowInstance && count($workflowInstance) > 0) {
        $instance = is_object($workflowInstance[0]) ? (array)$workflowInstance[0] : $workflowInstance[0];
        $hasWorkflow = true;
        $instanceID = $instance['instanceID'] ?? null;
        $policyID = $instance['policyID'] ?? null;
        $currentStepOrder = isset($instance['currentStepOrder']) ? (int)$instance['currentStepOrder'] : 1;

        // Get policy approval type
        if ($policyID) {
            $policy = Leave::leave_approval_policies(array('policyID' => $policyID), true, $DBConn);
            if ($policy) {
                $policy = is_object($policy) ? (array)$policy : $policy;
                $approvalType = isset($policy['approvalType']) ? $policy['approvalType'] : 'parallel';
            }

            // Get all steps to find max step order
            $allSteps = Leave::leave_approval_steps(
                array('policyID' => $policyID, 'Suspended' => 'N'),
                false,
                $DBConn
            );

            if ($allSteps && count($allSteps) > 0) {
                foreach ($allSteps as $step) {
                    $step = is_object($step) ? (array)$step : $step;
                    $stepOrderVal = isset($step['stepOrder']) ? (int)$step['stepOrder'] : 0;
                    if ($stepOrderVal > $maxStepOrder) {
                        $maxStepOrder = $stepOrderVal;
                    }
                }
            }

            // Get employee who submitted the application (if not already retrieved)
            if (!isset($employeeID) || !$employeeID) {
                $employeeID = $leaveApplication->employeeID ?? null;
            }
            if (!isset($employee) && $employeeID) {
                $employee = Employee::employees(array('ID' => $employeeID), true, $DBConn);
            }

            // PRIORITY 1: Use submitted stepID and stepOrder if provided (from form)
            if ($submittedStepID && $submittedStepOrder) {
                // Validate that the submitted step exists in the policy
                $stepValidation = Leave::leave_approval_steps(
                    array('policyID' => $policyID, 'stepID' => $submittedStepID, 'Suspended' => 'N'),
                    true,
                    $DBConn
                );

                if ($stepValidation) {
                    // Step exists - verify user is authorized for this step
                    $employeeID = $leaveApplication->employeeID ?? null;
                    $employee = null;
                    if ($employeeID) {
                        $employee = Employee::employees(array('ID' => $employeeID), true, $DBConn);
                    }

                    // Quick check: resolve dynamic approvers and verify user matches
                    $isAuthorized = false;
                    if ($employee) {
                        $dynamicApprovers = Leave::resolve_dynamic_workflow_approvers($policyID, $employeeID, $DBConn);
                        foreach ($dynamicApprovers as $approver) {
                            if (isset($approver['approverUserID']) && (int)$approver['approverUserID'] === $userID
                                && isset($approver['stepID']) && (int)$approver['stepID'] === $submittedStepID) {
                                $isAuthorized = true;
                                break;
                            }
                        }
                    }

                    // Also check static approvers
                    if (!$isAuthorized) {
                        $staticApprovers = Leave::get_workflow_approvers($policyID, $DBConn);
                        foreach ($staticApprovers as $approver) {
                            if (isset($approver['approverUserID']) && (int)$approver['approverUserID'] === $userID
                                && isset($approver['stepID']) && (int)$approver['stepID'] === $submittedStepID) {
                                $isAuthorized = true;
                                break;
                            }
                        }
                    }

                    if ($isAuthorized) {
                        $stepID = $submittedStepID;
                        $stepOrder = $submittedStepOrder;
                        error_log("Using submitted step information (VALIDATED): StepID: {$stepID}, StepOrder: {$stepOrder} for User {$userID}");
                    } else {
                        error_log("WARNING: User {$userID} not authorized for submitted stepID {$submittedStepID}. Will re-identify approver.");
                        $stepID = null;
                        $stepOrder = null;
                    }
                } else {
                    error_log("WARNING: Submitted stepID {$submittedStepID} not found in policy {$policyID}. Will re-identify approver.");
                    $stepID = null;
                    $stepOrder = null;
                }
            }

            // PRIORITY 2: If no submitted step info, check dynamic approvers FIRST, then static approvers
            // This ensures dynamic workflows (supervisor, department_head, etc.) are processed first
            if ((!$stepID || !$stepOrder)) {
                // Get employee if not already retrieved
                if (!$employee && $employeeID) {
                    $employee = Employee::employees(array('ID' => $employeeID), true, $DBConn);
                }

                if ($employee) {
                    $dynamicApprovers = array();
                    $staticApprovers = array();

                    // Step 1: Resolve dynamic approvers first
                    $dynamicApprovers = Leave::resolve_dynamic_workflow_approvers($policyID, $employeeID, $DBConn);
                    error_log("Resolved dynamic approvers for employee {$employeeID}: " . count($dynamicApprovers) . " approver(s)");

                    // Verify dynamic approvers have stepID and stepOrder
                    foreach ($dynamicApprovers as &$dynApprover) {
                        if (!isset($dynApprover['stepID']) || !isset($dynApprover['stepOrder'])) {
                            error_log("WARNING: Dynamic approver missing stepID or stepOrder: " . json_encode($dynApprover));
                        }
                    }
                    unset($dynApprover);

                    // Step 2: Check if user is in dynamic approvers list FIRST
                    foreach ($dynamicApprovers as $approver) {
                        if (isset($approver['approverUserID']) && (int)$approver['approverUserID'] === $userID) {
                            $approverStepID = isset($approver['stepID']) ? (int)$approver['stepID'] : null;
                            $approverStepOrder = isset($approver['stepOrder']) ? (int)$approver['stepOrder'] : null;
                            if ($approverStepID && $approverStepOrder) {
                                $stepID = $approverStepID;
                                $stepOrder = $approverStepOrder;
                                error_log("Found approver in DYNAMIC list (PRIORITY): User {$userID}, StepID: {$stepID}, StepOrder: {$stepOrder}");
                                break;
                            }
                        }
                    }

                    // Step 3: Only check static approvers if not found in dynamic approvers
                    if (!$stepID || !$stepOrder) {
                        $staticApprovers = Leave::get_workflow_approvers($policyID, $DBConn);
                        error_log("Checking static approvers (fallback): " . count($staticApprovers) . " approver(s)");

                        foreach ($staticApprovers as $approver) {
                            if (isset($approver['approverUserID']) && (int)$approver['approverUserID'] === $userID) {
                                $approverStepID = isset($approver['stepID']) ? (int)$approver['stepID'] : null;
                                $approverStepOrder = isset($approver['stepOrder']) ? (int)$approver['stepOrder'] : null;
                                if ($approverStepID && $approverStepOrder) {
                                    $stepID = $approverStepID;
                                    $stepOrder = $approverStepOrder;
                                    error_log("Found approver in STATIC list (fallback): User {$userID}, StepID: {$stepID}, StepOrder: {$stepOrder}");
                                    break;
                                }
                            }
                        }
                    }
                } // Close if ($employee) block
            }

            // PRIORITY 3: If still not found, check dynamic approver types by matching step types
            // Don't restrict by currentStepOrder - check all steps to find the approver's step
            if ((!$stepID || !$stepOrder) && $employee && $allSteps && count($allSteps) > 0) {
                foreach ($allSteps as $step) {
                    $stepObj = is_object($step) ? $step : (object)$step;
                    $stepType = $stepObj->stepType ?? '';
                    $stepOrderVal = isset($stepObj->stepOrder) ? (int)$stepObj->stepOrder : 0;
                    $stepIDVal = isset($stepObj->stepID) ? (int)$stepObj->stepID : null;

                    // Check if user matches dynamic approver role
                    $isDynamicApprover = false;

                    switch ($stepType) {
                        case 'supervisor':
                            // Check if user is the employee's supervisor
                            if (!empty($employee->supervisorID) && (int)$employee->supervisorID === $userID) {
                                $isDynamicApprover = true;
                            }
                            break;

                        case 'department_head':
                            // Check if user is the employee's department head
                            $deptHead = Employee::get_employee_department_head($employeeID, $DBConn);
                            if ($deptHead && !empty($deptHead->ID) && (int)$deptHead->ID === $userID) {
                                $isDynamicApprover = true;
                            }
                            break;

                        case 'project_manager':
                            // For now, fall back to supervisor check
                            if (!empty($employee->supervisorID) && (int)$employee->supervisorID === $userID) {
                                $isDynamicApprover = true;
                            }
                            break;

                        case 'hr_manager':
                            // Check if user is HR manager
                            if ($isHrManager) {
                                $isDynamicApprover = true;
                            }
                            break;
                    }

                    if ($isDynamicApprover && $stepIDVal) {
                        $stepID = $stepIDVal;
                        $stepOrder = $stepOrderVal;
                        error_log("Dynamic approver matched by step type: User {$userID} is {$stepType} for employee {$employeeID}, step {$stepID}, order {$stepOrder}");
                        break;
                    }
                }
            }

            // Final fallback: If still no stepID found but user is a direct report, find the first step with supervisor type
            if (!$stepID && $employee && !empty($employee->supervisorID) && (int)$employee->supervisorID === $userID && $allSteps && count($allSteps) > 0) {
                foreach ($allSteps as $step) {
                    $stepObj = is_object($step) ? $step : (object)$step;
                    $stepType = $stepObj->stepType ?? '';
                    $stepOrderVal = isset($stepObj->stepOrder) ? (int)$stepObj->stepOrder : 0;
                    $stepIDVal = isset($stepObj->stepID) ? (int)$stepObj->stepID : null;

                    if (($stepType === 'supervisor' || $stepType === 'project_manager') && $stepIDVal) {
                        $stepID = $stepIDVal;
                        $stepOrder = $stepOrderVal;
                        error_log("Final fallback: Assigned supervisor step. User {$userID} is supervisor for employee {$employeeID}, step {$stepID}, order {$stepOrder}");
                        break;
                    }
                }
            }

            // Check if approver has already acted (prevent duplicate actions)
            if ($instanceID && $stepID) {
                if (LeaveNotifications::hasApproverActed($instanceID, $stepID, $userID, $DBConn)) {
                    echo json_encode(array(
                        'success' => false,
                        'message' => 'You have already acted on this application'
                    ));
                    exit;
                }
            }

            // Check if this is final step (HR manager or last step approver)
            // For HR managers not explicitly in workflow, find the final step
            if ($isHrManager && !$stepID) {
                // HR manager not in workflow steps - find final step
                if ($allSteps && count($allSteps) > 0) {
                    foreach ($allSteps as $step) {
                        $step = is_object($step) ? (array)$step : $step;
                        $stepOrderVal = isset($step['stepOrder']) ? (int)$step['stepOrder'] : 0;
                        if ($stepOrderVal === $maxStepOrder) {
                            $stepID = isset($step['stepID']) ? (int)$step['stepID'] : null;
                            $stepOrder = $maxStepOrder;
                            break;
                        }
                    }
                }
            }

            if ($isHrManager || ($stepOrder && $stepOrder >= $maxStepOrder)) {
                $isFinalStep = true;
            }
        }
    } else {
        // No workflow - single step approval
        $isFinalStep = true;
    }

    // Determine new status - but don't finalize yet, we'll check after recording the action
    if ($action === 'reject') {
        $newStatusID = 4; // Rejected - immediate rejection
    } else {
        // For approvals, keep as pending initially - we'll check after recording the action
        $newStatusID = 3; // Keep pending until we verify all required approvers have approved
    }

    $now = date('Y-m-d H:i:s');

    $DBConn->begin();

    try {

        // Handle workflow actions - Record both approve AND reject actions
        if ($hasWorkflow && $instanceID) {
            // For HR managers not explicitly in workflow, use final step
            if ($isHrManager && !$stepID && $maxStepOrder > 0) {
                // Find final step
                if ($allSteps && count($allSteps) > 0) {
                    foreach ($allSteps as $step) {
                        $step = is_object($step) ? (array)$step : $step;
                        $stepOrderVal = isset($step['stepOrder']) ? (int)$step['stepOrder'] : 0;
                        if ($stepOrderVal === $maxStepOrder) {
                            $stepID = isset($step['stepID']) ? (int)$step['stepID'] : null;
                            $stepOrder = $maxStepOrder;
                            break;
                        }
                    }
                }
            }

            // Record the action (both approve and reject)
            // CRITICAL: Both stepID and stepOrder must be present for approval actions
            if ($stepID && $stepOrder) {
                error_log("Processing approval action: User {$userID}, StepID: {$stepID}, StepOrder: {$stepOrder}, Action: {$action}");
                // Get approver ID from step approvers table
                $stepApprovers = Leave::leave_approval_step_approvers(
                    array('stepID' => $stepID, 'approverUserID' => $userID, 'Suspended' => 'N'),
                    true,
                    $DBConn
                );

                $stepApproverID = null;
                if ($stepApprovers) {
                    $approver = is_object($stepApprovers) ? (array)$stepApprovers : $stepApprovers;
                    $stepApproverID = $approver['stepApproverID'] ?? $approver['approverID'] ?? null;
                }

                // For dynamic approvers (supervisor, department_head, hr_manager, etc.),
                // ensure they are stored in tija_leave_approval_step_approvers table
                if (!$stepApproverID) {
                    // Check table columns to determine the correct structure
                    $allColumns = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_step_approvers", array());
                    $columnNames = array();
                    if ($allColumns && count($allColumns) > 0) {
                        foreach ($allColumns as $col) {
                            $col = is_object($col) ? (array)$col : $col;
                            $columnNames[] = $col['Field'] ?? $col['field'] ?? '';
                        }
                    }

                    $hasStepApproverID = in_array('stepApproverID', $columnNames);
                    $hasApproverID = in_array('approverID', $columnNames);
                    $hasApproverType = in_array('approverType', $columnNames);
                    $hasIsBackup = in_array('isBackup', $columnNames);
                    $hasCreatedAt = in_array('createdAt', $columnNames);

                    // Determine step type for approverType
                    $stepType = null;
                    if ($allSteps && count($allSteps) > 0) {
                        foreach ($allSteps as $step) {
                            $stepObj = is_object($step) ? (array)$step : $step;
                            if (isset($stepObj['stepID']) && (int)$stepObj['stepID'] === (int)$stepID) {
                                $stepType = $stepObj['stepType'] ?? null;
                                break;
                            }
                        }
                    }

                    // Create approver record for dynamic approver
                    $approverData = array(
                        'stepID' => $stepID,
                        'approverUserID' => $userID,
                        'isBackup' => 'N',
                        'notificationOrder' => 1,
                        'Suspended' => 'N'
                    );

                    if ($hasApproverType) {
                        $approverData['approverType'] = 'user';
                    }

                    if ($hasCreatedAt) {
                        $approverData['createdAt'] = $now;
                    }

                    // Insert the dynamic approver record
                    $insertResult = $DBConn->insert_data('tija_leave_approval_step_approvers', $approverData);

                    if ($insertResult) {
                        $stepApproverID = $DBConn->lastInsertId();
                        error_log("Created approver record for dynamic approver: User {$userID}, Step {$stepID}, ApproverID: {$stepApproverID}, StepType: " . ($stepType ?? 'unknown'));
                    } else {
                        // If insert failed, try to use userID as fallback
                        $stepApproverID = $userID;
                        error_log("WARNING: Failed to create approver record for dynamic approver. Using userID as stepApproverID: User {$userID}, Step {$stepID}");
                    }
                }

                if ($stepApproverID) {
                    // Validate that both stepID and stepOrder are present before recording
                    if (!$stepID || !$stepOrder) {
                        $errorMsg = "Cannot record approval action: Missing stepID or stepOrder. stepID: " . ($stepID ?? 'NULL') . ", stepOrder: " . ($stepOrder ?? 'NULL');
                        error_log("ERROR: " . $errorMsg);
                        throw new Exception($errorMsg);
                    }

                    // Record the action (approve or reject)
                    $actionID = LeaveNotifications::recordApprovalAction(
                        $instanceID,
                        (int)$stepID,
                        $stepApproverID,
                        $userID,
                        (int)$stepOrder,
                        $action,
                        $comments,
                        $DBConn
                    );

                    error_log("Approval action recorded successfully. ActionID: {$actionID}, InstanceID: {$instanceID}, StepID: {$stepID}, StepOrder: {$stepOrder}, Action: {$action}, StepApproverID: {$stepApproverID}");
                } else {
                    error_log("WARNING: Could not determine stepApproverID for user {$userID}, step {$stepID}");
                    throw new Exception("Could not determine approver ID for approval action");
                }
            } else {
                $errorMsg = "Missing step information for approval action. stepID: " . ($stepID ?? 'NULL') . ", stepOrder: " . ($stepOrder ?? 'NULL') . ", UserID: {$userID}, LeaveApplicationID: {$leaveApplicationID}";
                error_log("ERROR: " . $errorMsg);

                // Try one more time to resolve if we have employee info
                if ($employee && !$stepID) {
                    error_log("Attempting final resolution for employee {$employeeID}");
                    $finalDynamicApprovers = Leave::resolve_dynamic_workflow_approvers($policyID, $employeeID, $DBConn);
                    foreach ($finalDynamicApprovers as $finalApprover) {
                        if (isset($finalApprover['approverUserID']) && (int)$finalApprover['approverUserID'] === $userID) {
                            if (isset($finalApprover['stepID']) && isset($finalApprover['stepOrder'])) {
                                $stepID = (int)$finalApprover['stepID'];
                                $stepOrder = (int)$finalApprover['stepOrder'];
                                error_log("Final resolution successful: StepID: {$stepID}, StepOrder: {$stepOrder}");
                                break;
                            }
                        }
                    }
                }

                if (!$stepID || !$stepOrder) {
                    throw new Exception($errorMsg);
                }
            }

            // Handle rejection - mark workflow as rejected and remove notifications
            if ($action === 'reject') {
                $columnsCheck = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_instances LIKE 'status'", array());
                $hasStatusColumn = ($columnsCheck && count($columnsCheck) > 0);

                $workflowColumnsCheck = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_instances LIKE 'workflowStatus'", array());
                $hasWorkflowStatusColumn = ($workflowColumnsCheck && count($workflowColumnsCheck) > 0);

                $updateData = array('completedAt' => $now, 'lastActionAt' => $now, 'lastActionBy' => $userID);

                if ($hasStatusColumn) {
                    $updateData['status'] = 'rejected';
                }
                if ($hasWorkflowStatusColumn) {
                    $updateData['workflowStatus'] = 'rejected';
                }

                $rejectionUpdateResult = $DBConn->update_table(
                    'tija_leave_approval_instances',
                    $updateData,
                    array('instanceID' => $instanceID)
                );

                if ($rejectionUpdateResult) {
                    error_log("Workflow instance updated for rejection. InstanceID: {$instanceID}, UpdateData: " . json_encode($updateData));
                } else {
                    error_log("WARNING: Failed to update workflow instance for rejection. InstanceID: {$instanceID}");
                }

                // Remove pending notifications
                LeaveNotifications::removePendingNotifications($leaveApplicationID, $DBConn);
                error_log("Workflow marked as rejected and notifications removed");
            }
        }
        // Note: Final approval workflow instance update is handled after determining newStatusID

        // Handle approval actions - workflow advancement
        if ($hasWorkflow && $instanceID && $action === 'approve' && $stepID && $stepOrder) {
            // Update workflow instance with last action info
            $instanceUpdateData = array('lastActionAt' => $now, 'lastActionBy' => $userID);

            // Check which columns exist
            $instanceColumns = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_instances", array());
            $instanceColumnNames = array();
            if ($instanceColumns && count($instanceColumns) > 0) {
                foreach ($instanceColumns as $col) {
                    $col = is_object($col) ? (array)$col : $col;
                    $instanceColumnNames[] = $col['Field'] ?? $col['field'] ?? '';
                }
            }

            // For sequential workflows, advance to next step if current step is complete
            if ($approvalType === 'sequential' && $action === 'approve') {
                // Check if current step is fully approved
                $isStepComplete = Leave::is_step_fully_approved($instanceID, $stepID, $DBConn);

                if ($isStepComplete && !$isFinalStep) {
                    // Advance to next step
                    $nextStepOrder = $stepOrder + 1;
                    if ($nextStepOrder <= $maxStepOrder) {
                        // Find next step ID
                        $nextStepID = null;
                        foreach ($allSteps as $step) {
                            $step = is_object($step) ? (array)$step : $step;
                            if (isset($step['stepOrder']) && (int)$step['stepOrder'] === $nextStepOrder) {
                                $nextStepID = isset($step['stepID']) ? (int)$step['stepID'] : null;
                                break;
                            }
                        }

                        if ($nextStepID) {
                            $instanceUpdateData['currentStepOrder'] = $nextStepOrder;
                            if (in_array('currentStepID', $instanceColumnNames)) {
                                $instanceUpdateData['currentStepID'] = $nextStepID;
                            }
                            error_log("Workflow advancing to next step. New StepOrder: {$nextStepOrder}, StepID: {$nextStepID}");

                            // Send notifications to next step approvers
                            $nextStepApprovers = array();
                            foreach ($allApprovers as $approver) {
                                if (isset($approver['stepID']) && (int)$approver['stepID'] === $nextStepID) {
                                    $nextStepApprovers[] = $approver;
                                }
                            }

                            // If no saved approvers, resolve dynamic approvers
                            if (empty($nextStepApprovers) && $employee) {
                                $resolvedApprovers = Leave::resolve_dynamic_workflow_approvers($policyID, $employeeID, $DBConn);
                                foreach ($resolvedApprovers as $resolved) {
                                    if (isset($resolved['stepID']) && (int)$resolved['stepID'] === $nextStepID) {
                                        $nextStepApprovers[] = $resolved;
                                    }
                                }
                            }

                            // Send notifications to next step approvers
                            if (!empty($nextStepApprovers)) {
                                $employeeDetails = Employee::employees(array('ID' => $employeeID), true, $DBConn);
                                $employeeName = $employeeDetails ? ($employeeDetails->FirstName . ' ' . $employeeDetails->Surname) : 'Employee';
                                $leaveTypeObj = Leave::leave_types(array('leaveTypeID' => $leaveApplication->leaveTypeID), true, $DBConn);

                                foreach ($nextStepApprovers as $nextApprover) {
                                    $nextApproverUserID = isset($nextApprover['approverUserID']) ? (int)$nextApprover['approverUserID'] : null;
                                    if ($nextApproverUserID) {
                                        $nextStepNotification = Notification::create(array(
                                            'eventSlug' => 'leave_pending_approval',
                                            'userId' => $nextApproverUserID,
                                            'originatorId' => $employeeID,
                                            'data' => array(
                                                'employee_id' => $employeeID,
                                                'employee_name' => $employeeName,
                                                'leave_type' => $leaveTypeObj ? $leaveTypeObj->leaveTypeName : 'Leave',
                                                'start_date' => date('M j, Y', strtotime($leaveApplication->startDate)),
                                                'end_date' => date('M j, Y', strtotime($leaveApplication->endDate)),
                                                'total_days' => $leaveApplication->noOfDays ?? 0,
                                                'application_id' => $leaveApplicationID,
                                                'approval_level' => $nextStepOrder,
                                                'step_name' => $nextApprover['stepName'] ?? 'Approval Step',
                                                'is_final_step' => ($nextStepOrder >= $maxStepOrder)
                                            ),
                                            'link' => '?s=user&ss=leave&p=pending_approvals&id=' . $leaveApplicationID,
                                            'entityID' => $entityID,
                                            'orgDataID' => $leaveApplication->orgDataID ?? null,
                                            'segmentType' => 'leave_application',
                                            'segmentID' => $leaveApplicationID,
                                            'priority' => 'high'
                                        ), $DBConn);

                                        if (is_array($nextStepNotification) && isset($nextStepNotification['success']) && $nextStepNotification['success']) {
                                            error_log("Notification sent to next step approver. UserID: {$nextApproverUserID}, StepOrder: {$nextStepOrder}, LeaveApplicationID: {$leaveApplicationID}");
                                        } else {
                                            error_log("WARNING: Failed to send notification to next step approver. UserID: {$nextApproverUserID}, StepOrder: {$nextStepOrder}, LeaveApplicationID: {$leaveApplicationID}");
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Update workflow instance
            if (!empty($instanceUpdateData)) {
                $instanceUpdateResult = LeaveNotifications::updateApprovalInstance($instanceID, $instanceUpdateData, $DBConn);
                if ($instanceUpdateResult) {
                    error_log("Workflow instance updated successfully. InstanceID: {$instanceID}, UpdateData: " . json_encode($instanceUpdateData));
                } else {
                    error_log("WARNING: Failed to update workflow instance. InstanceID: {$instanceID}, UpdateData: " . json_encode($instanceUpdateData));
                }
            }
        }

        // Now check if all required approvers have approved (after recording the action)
        if ($action === 'approve' && $hasWorkflow && $instanceID && $policyID) {
            $approvalStatus = Leave::check_workflow_approval_status($instanceID, $policyID, $DBConn);

            // If HR Manager approves final step, mark as fully approved
            if ($isHrManager && $isFinalStep) {
                $newStatusID = 6; // Fully approved
                error_log("HR Manager approved final step - marking as fully approved");
            } elseif ($approvalStatus['allRequiredApproved'] && $isFinalStep) {
                $newStatusID = 6; // Fully approved - all required approvers have approved
                error_log("All required approvers have approved final step - marking as fully approved");
            } else {
                $newStatusID = 3; // Keep pending - waiting for other approvers
                error_log("Workflow still pending. AllRequiredApproved: " . ($approvalStatus['allRequiredApproved'] ? 'YES' : 'NO') . ", IsFinalStep: " . ($isFinalStep ? 'YES' : 'NO'));
            }

            // If fully approved, update workflow instance
            if ($newStatusID === 6) {
                $columnsCheck = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_instances LIKE 'status'", array());
                $hasStatusColumn = ($columnsCheck && count($columnsCheck) > 0);

                $workflowColumnsCheck = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_instances LIKE 'workflowStatus'", array());
                $hasWorkflowStatusColumn = ($workflowColumnsCheck && count($workflowColumnsCheck) > 0);

                $finalUpdateData = array('completedAt' => $now);

                if ($hasStatusColumn) {
                    $finalUpdateData['status'] = 'completed';
                }
                if ($hasWorkflowStatusColumn) {
                    $finalUpdateData['workflowStatus'] = 'completed';
                }

                $finalUpdateResult = LeaveNotifications::updateApprovalInstance($instanceID, $finalUpdateData, $DBConn);
                if ($finalUpdateResult) {
                    error_log("Workflow instance marked as completed. InstanceID: {$instanceID}, UpdateData: " . json_encode($finalUpdateData));
                } else {
                    error_log("WARNING: Failed to update workflow instance as completed. InstanceID: {$instanceID}");
                }
            }
        } elseif ($action === 'approve' && $isFinalStep && !$hasWorkflow) {
            $newStatusID = 6; // Fully approved (no workflow)
        }

        // Update leave application status if it changed
        $statusChanged = ($newStatusID !== (int)$leaveApplication->leaveStatusID);
        if ($statusChanged) {
            $updateResult = $DBConn->update_table(
                'tija_leave_applications',
                array(
                    'leaveStatusID' => $newStatusID,
                    'LastUpdate' => $now,
                    'LastUpdateByID' => $userID
                ),
                array('leaveApplicationID' => $leaveApplicationID)
            );

            if (!$updateResult) {
                throw new Exception('Failed to update leave application status');
            }

            // If status changed to approved (6), ensure email notification is sent
            if ($newStatusID === 6 && $action === 'approve') {
                error_log("Leave application {$leaveApplicationID} status changed to Approved (6) - ensuring email notification is sent");
                // The notification will be sent in the notification section below
            }
        }

        // Record approval in legacy table (tija_leave_approvals)
        Leave::ensure_leave_approval_comments_table($DBConn);

        // Prepare data for legacy approvals table
        $legacyApprovalData = array(
            'leaveApplicationID' => $leaveApplicationID,
            'employeeID' => $leaveApplication->employeeID ?? null,
            'leaveTypeID' => $leaveApplication->leaveTypeID ?? null,
            'leavePeriodID' => $leaveApplication->leavePeriodID ?? null,
            'leaveApproverID' => $userID,
            'leaveDate' => date('Y-m-d'), // Date only, not datetime
            'leaveStatusID' => $newStatusID,
            'leaveStatus' => $action === 'approve' ? ($newStatusID === 6 ? 'approved' : 'approved') : 'rejected',
            'approversComments' => ($comments && $comments !== '') ? $comments : '', // Ensure not null
            'LastUpdateByID' => $userID,
            'LastUpdate' => $now,
            'Lapsed' => 'N',
            'Suspended' => 'N'
        );

        // Check if DateAdded column exists and add it
        $approvalColumns = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approvals", array());
        $approvalColumnNames = array();
        if ($approvalColumns && count($approvalColumns) > 0) {
            foreach ($approvalColumns as $col) {
                $col = is_object($col) ? (array)$col : $col;
                $approvalColumnNames[] = $col['Field'] ?? $col['field'] ?? '';
            }
        }

        if (in_array('DateAdded', $approvalColumnNames) && !isset($legacyApprovalData['DateAdded'])) {
            $legacyApprovalData['DateAdded'] = $now;
        }

        $legacyApprovalResult = $DBConn->insert_data('tija_leave_approvals', $legacyApprovalData);
        if (!$legacyApprovalResult) {
            error_log("WARNING: Failed to insert into legacy approvals table, but continuing...");
        } else {
            error_log("Legacy approval record created. LeaveApprovalID: " . $DBConn->lastInsertId() . ", LeaveApplicationID: {$leaveApplicationID}, ApproverID: {$userID}");
        }

        // Save comment if provided
        if (!empty($comments)) {
            $approvalLevel = $isHrManager ? 'hr_manager' : ($isFinalStep ? 'final_approver' : 'intermediate_approver');
            $DBConn->insert_data('tija_leave_approval_comments', array(
                'leaveApplicationID' => $leaveApplicationID,
                'approverID' => $userID,
                'approverUserID' => $userID,
                'approvalLevel' => $approvalLevel,
                'comment' => $comments,
                'commentDate' => $now,
                'DateAdded' => $now,
                'Lapsed' => 'N',
                'Suspended' => 'N'
            ));
        }

        // Record audit trail with comprehensive information
        $auditOldValues = array(
            'leaveStatusID' => (int)$leaveApplication->leaveStatusID,
            'instanceID' => $instanceID,
            'currentStepOrder' => isset($instance) && isset($instance['currentStepOrder']) ? (int)$instance['currentStepOrder'] : null
        );

        $auditNewValues = array(
            'leaveStatusID' => $newStatusID,
            'instanceID' => $instanceID
        );

        if ($hasWorkflow && $instanceID) {
            $auditNewValues['stepID'] = $stepID;
            $auditNewValues['stepOrder'] = $stepOrder;
            $auditNewValues['stepApproverID'] = $stepApproverID ?? null;
            $auditNewValues['isFinalStep'] = $isFinalStep;
            if (isset($instanceUpdateData['currentStepOrder'])) {
                $auditNewValues['currentStepOrder'] = $instanceUpdateData['currentStepOrder'];
            }
        }

        $auditData = array(
            'entityType' => 'approval',
            'entityID' => $leaveApplicationID,
            'action' => $action === 'approve' ? ($isFinalStep ? 'approved' : 'approved_step') : 'rejected',
            'oldValues' => json_encode($auditOldValues),
            'newValues' => json_encode($auditNewValues),
            'performedByID' => $userID,
            'performedDate' => $now,
            'reason' => ($comments && $comments !== '') ? $comments : null
        );

        // Check if ipAddress and userAgent columns exist
        $auditColumns = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_audit_log", array());
        $auditColumnNames = array();
        if ($auditColumns && count($auditColumns) > 0) {
            foreach ($auditColumns as $col) {
                $col = is_object($col) ? (array)$col : $col;
                $auditColumnNames[] = $col['Field'] ?? $col['field'] ?? '';
            }
        }

        if (in_array('ipAddress', $auditColumnNames)) {
            $auditData['ipAddress'] = $_SERVER['REMOTE_ADDR'] ?? null;
        }
        if (in_array('userAgent', $auditColumnNames)) {
            $auditData['userAgent'] = $_SERVER['HTTP_USER_AGENT'] ?? null;
        }

        $auditResult = $DBConn->insert_data('tija_leave_audit_log', $auditData);
        if (!$auditResult) {
            throw new Exception('Failed to log approval action');
        }

        error_log("Audit log entry created. AuditID: " . $DBConn->lastInsertId() . ", Action: {$auditData['action']}, InstanceID: {$instanceID}, StepID: " . ($stepID ?? 'NULL'));

        $DBConn->commit();

        // Send notifications after successful commit
        try {
            if ($action === 'approve') {
                // Notify applicant of approval
                $notifyResult = LeaveNotifications::notifyLeaveApproved($leaveApplicationID, $userID, $stepID ?? 0, $isFinalStep, $comments, $DBConn);
                if (isset($notifyResult['success']) && $notifyResult['success']) {
                    error_log("Approval notification sent successfully. LeaveApplicationID: {$leaveApplicationID}, ApproverID: {$userID}, IsFinalStep: " . ($isFinalStep ? 'YES' : 'NO'));
                } else {
                    error_log("WARNING: Approval notification may have failed. LeaveApplicationID: {$leaveApplicationID}, Result: " . json_encode($notifyResult));
                }
            } else {
                // Rejection - notify applicant (pending notifications already removed above)
                $notifyResult = LeaveNotifications::notifyLeaveRejected($leaveApplicationID, $userID, $comments, $DBConn);
                if (isset($notifyResult['success']) && $notifyResult['success']) {
                    error_log("Rejection notification sent successfully. LeaveApplicationID: {$leaveApplicationID}, ApproverID: {$userID}");
                } else {
                    error_log("WARNING: Rejection notification may have failed. LeaveApplicationID: {$leaveApplicationID}, Result: " . json_encode($notifyResult));
                }
            }
        } catch (Exception $notifyException) {
            // Log notification errors but don't fail the approval action
            error_log("ERROR: Exception while sending notifications: " . $notifyException->getMessage());
        }

        $message = $action === 'approve'
            ? ($isFinalStep
                ? 'Leave application fully approved successfully'
                : 'Approved. Waiting for other approvers.')
            : 'Leave application rejected successfully';

        echo json_encode(array(
            'success' => true,
            'message' => $message,
            'action' => $action,
            'isFinalStep' => $isFinalStep,
            'newStatus' => $newStatusID
        ));

    } catch (Exception $innerException) {
        $DBConn->rollback();
        throw $innerException;
    }

} catch (Exception $e) {
    error_log("Leave approval processing error: " . $e->getMessage());
    echo json_encode(array(
        'success' => false,
        'message' => 'Error processing approval: ' . $e->getMessage()
    ));
}

