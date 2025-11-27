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

            // Get employee who submitted the application
            $employeeID = $leaveApplication->employeeID ?? null;
            $employee = null;
            if ($employeeID) {
                $employee = Employee::employees(array('ID' => $employeeID), true, $DBConn);
            }

            // Find which step this approver belongs to (check saved approvers first)
            $allApprovers = Leave::get_workflow_approvers($policyID, $DBConn);

            // If no saved approvers found, resolve dynamic approvers for this employee
            if (empty($allApprovers) && $employee) {
                $allApprovers = Leave::resolve_dynamic_workflow_approvers($policyID, $employeeID, $DBConn);
                error_log("Resolved dynamic approvers for employee {$employeeID}: " . count($allApprovers) . " approver(s)");
            }

            foreach ($allApprovers as $approver) {
                if (isset($approver['approverUserID']) && (int)$approver['approverUserID'] === $userID) {
                    $approverStepID = isset($approver['stepID']) ? (int)$approver['stepID'] : null;
                    $stepOrder = isset($approver['stepOrder']) ? (int)$approver['stepOrder'] : null;
                    if ($approverStepID) {
                        $stepID = $approverStepID;
                        break;
                    }
                }
            }

            // If not found in saved approvers, check dynamic approvers
            if (!$stepID && $employee && $allSteps && count($allSteps) > 0) {
                foreach ($allSteps as $step) {
                    $stepObj = is_object($step) ? $step : (object)$step;
                    $stepType = $stepObj->stepType ?? '';
                    $stepOrderVal = isset($stepObj->stepOrder) ? (int)$stepObj->stepOrder : 0;
                    $stepIDVal = isset($stepObj->stepID) ? (int)$stepObj->stepID : null;

                    // Only check steps that match current step order
                    if ($stepOrderVal > $currentStepOrder) {
                        continue; // Future steps
                    }

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
                        error_log("Dynamic approver matched: User {$userID} is {$stepType} for employee {$employeeID}, step {$stepID}, order {$stepOrder}");
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

        // Handle workflow actions
        if ($hasWorkflow && $action === 'reject') {
            // Immediate rejection - mark workflow as rejected and remove notifications
            $columnsCheck = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_instances LIKE 'status'", array());
            $hasStatusColumn = ($columnsCheck && count($columnsCheck) > 0);

            $workflowColumnsCheck = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_instances LIKE 'workflowStatus'", array());
            $hasWorkflowStatusColumn = ($workflowColumnsCheck && count($workflowColumnsCheck) > 0);

            $updateData = array('completedAt' => $now);

            if ($hasStatusColumn) {
                $updateData['status'] = 'rejected';
            }
            if ($hasWorkflowStatusColumn) {
                $updateData['workflowStatus'] = 'rejected';
            }

            $DBConn->update_table(
                'tija_leave_approval_instances',
                $updateData,
                array('instanceID' => $instanceID)
            );

            // Remove pending notifications
            LeaveNotifications::removePendingNotifications($leaveApplicationID, $DBConn);
        }
        // Note: Final approval workflow instance update is handled after determining newStatusID

        // Record approval action in workflow if workflow exists
        if ($hasWorkflow && $instanceID && $action === 'approve') {
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

            if ($stepID && $stepOrder) {
                // Get approver ID from step approvers (or create a virtual one for dynamic approvers)
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

                // For dynamic approvers (supervisor, department_head, etc.) or HR managers not in step approvers,
                // use userID as stepApproverID
                if (!$stepApproverID) {
                    $stepApproverID = $userID;
                    error_log("Using userID as stepApproverID for dynamic approver: User {$userID}, Step {$stepID}");
                }

                if ($stepApproverID) {
                    // Record the approval action
                    $actionID = LeaveNotifications::recordApprovalAction(
                        $instanceID,
                        $stepID,
                        $stepApproverID,
                        $userID,
                        $stepOrder,
                        $action,
                        $comments,
                        $DBConn
                    );

                    error_log("Approval action recorded. ActionID: {$actionID}, InstanceID: {$instanceID}, StepID: {$stepID}, StepOrder: {$stepOrder}");

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
                                                Notification::create(array(
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
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // Update workflow instance
                    if (!empty($instanceUpdateData)) {
                        LeaveNotifications::updateApprovalInstance($instanceID, $instanceUpdateData, $DBConn);
                        error_log("Workflow instance updated: " . json_encode($instanceUpdateData));
                    }
                } else {
                    error_log("WARNING: Could not determine stepApproverID for user {$userID}, step {$stepID}");
                }
            } else {
                error_log("WARNING: stepID or stepOrder is missing. stepID: " . ($stepID ?? 'NULL') . ", stepOrder: " . ($stepOrder ?? 'NULL'));
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

                LeaveNotifications::updateApprovalInstance($instanceID, $finalUpdateData, $DBConn);
                error_log("Workflow instance marked as completed");
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

        // Record approval in legacy table
        Leave::ensure_leave_approval_comments_table($DBConn);
        $DBConn->insert_data('tija_leave_approvals', array(
            'leaveApplicationID' => $leaveApplicationID,
            'employeeID' => $leaveApplication->employeeID ?? null,
            'leaveTypeID' => $leaveApplication->leaveTypeID ?? null,
            'leavePeriodID' => $leaveApplication->leavePeriodID ?? null,
            'leaveApproverID' => $userID,
            'leaveDate' => $now,
            'leaveStatusID' => $newStatusID,
            'leaveStatus' => $action === 'approve' ? ($newStatusID === 6 ? 'approved' : 'pending') : 'rejected',
            'approversComments' => $comments,
            'LastUpdateByID' => $userID,
            'LastUpdate' => $now,
            'Lapsed' => 'N',
            'Suspended' => 'N'
        ));

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

        // Record audit trail
        $auditData = array(
            'entityType' => 'approval',
            'entityID' => $leaveApplicationID,
            'action' => $action === 'approve' ? ($isFinalStep ? 'approved' : 'approved_step') : 'rejected',
            'oldValues' => json_encode(array(
                'leaveStatusID' => (int)$leaveApplication->leaveStatusID
            )),
            'newValues' => json_encode(array(
                'leaveStatusID' => $newStatusID
            )),
            'performedByID' => $userID,
            'performedDate' => 'now()',
            'reason' => ($comments && $comments !== '') ? $comments : 'NULL'
        );

        $auditResult = $DBConn->insert_data('tija_leave_audit_log', $auditData);
        if (!$auditResult) {
            throw new Exception('Failed to log approval action');
        }

        $DBConn->commit();

        // Send notifications
        if ($action === 'approve') {
            // Notify applicant of approval
            LeaveNotifications::notifyLeaveApproved($leaveApplicationID, $userID, $stepID ?? 0, $isFinalStep, $comments, $DBConn);
        } else {
            // Rejection - notify applicant (pending notifications already removed above)
            LeaveNotifications::notifyLeaveRejected($leaveApplicationID, $userID, $comments, $DBConn);
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

