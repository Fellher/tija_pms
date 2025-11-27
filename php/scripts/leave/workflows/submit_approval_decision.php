<?php
/**
 * Submit Approval Decision Script
 *
 * Handles the submission of approval decisions (approve/reject/request info)
 */

// Include necessary files
session_start();
$base = '../../../../';
set_include_path($base);
include 'php/includes.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!$isValidUser) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);
    $leaveId = isset($input['leaveId']) ? Utility::clean_string($input['leaveId']) : '';
    $decision = isset($input['decision']) ? Utility::clean_string($input['decision']) : '';
    $comment = isset($input['comment']) ? Utility::clean_string($input['comment']) : '';
    $approverId = isset($input['approverId']) ? Utility::clean_string($input['approverId']) : '';

    if (empty($leaveId) || empty($decision) || empty($approverId)) {
        echo json_encode(['success' => false, 'message' => 'Leave ID, decision, and approver ID are required']);
        exit;
    }

    // Validate decision
    $validDecisions = ['approve', 'reject'];
    if (!in_array($decision, $validDecisions)) {
        echo json_encode(['success' => false, 'message' => 'Invalid decision type']);
        exit;
    }

    // Get leave application details using Leave class method
    $leave = Leave::leave_applications_full(array('leaveApplicationID' => $leaveId), true, $DBConn);

    if (!$leave) {
        echo json_encode(['success' => false, 'message' => 'Leave application not found']);
        exit;
    }

    $leaveArray = is_object($leave) ? (array)$leave : $leave;
    $entityID = $leaveArray['entityID'] ?? null;

    // Check for approval workflow instance
    // Check if Lapsed column exists
    $lapsedCheck = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_instances LIKE 'Lapsed'", array());
    $hasLapsedColumn = ($lapsedCheck && count($lapsedCheck) > 0);

    $whereClause = "leaveApplicationID = ?";
    $params = array(array($leaveId, 'i'));

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
    $currentStepOrder = null;
    $isFinalStep = false;

    if ($workflowInstance && count($workflowInstance) > 0) {
        $instance = is_object($workflowInstance[0]) ? (array)$workflowInstance[0] : $workflowInstance[0];
        $hasWorkflow = true;
        $instanceID = $instance['instanceID'] ?? null;
        $policyID = $instance['policyID'] ?? null;
        $currentStepOrder = $instance['currentStepOrder'] ?? null;

        // Check if this is the final step
        if ($policyID) {
            $allSteps = Leave::leave_approval_steps(
                array('policyID' => $policyID, 'Suspended' => 'N'),
                false,
                $DBConn
            );

            if ($allSteps && count($allSteps) > 0) {
                $maxStepOrder = 0;
                foreach ($allSteps as $step) {
                    $step = is_object($step) ? (array)$step : $step;
                    $stepOrder = isset($step['stepOrder']) ? (int)$step['stepOrder'] : 0;
                    if ($stepOrder > $maxStepOrder) {
                        $maxStepOrder = $stepOrder;
                    }
                }

                if ($currentStepOrder >= $maxStepOrder) {
                    $isFinalStep = true;
                }
            }
        }
    } else {
        // No workflow instance found - check if there's an active workflow
        // This handles the case where workflow wasn't set up during submission
        $activeWorkflow = Leave::get_active_approval_workflow($entityID, $DBConn);
        if ($activeWorkflow) {
            $policyID = is_object($activeWorkflow) ? $activeWorkflow->policyID : (is_array($activeWorkflow) ? $activeWorkflow['policyID'] : null);
            if ($policyID) {
                // Create approval instance
                $instanceID = Leave::create_approval_instance($leaveId, $policyID, $DBConn);
                if ($instanceID) {
                    $hasWorkflow = true;
                    // Get the current step order
                    $instanceData = $DBConn->fetch_all_rows(
                        "SELECT * FROM tija_leave_approval_instances WHERE instanceID = ?",
                        array(array($instanceID, 'i'))
                    );
                    if ($instanceData && count($instanceData) > 0) {
                        $inst = is_object($instanceData[0]) ? (array)$instanceData[0] : $instanceData[0];
                        $currentStepOrder = $inst['currentStepOrder'] ?? 1;
                    }
                }
            }
        }
    }

    // Ensure user can approve
    $permissions = Leave::check_leave_application_permissions($leave, $approverId);
    if (empty($permissions['canApprove'])) {
        echo json_encode(['success' => false, 'message' => 'You are not authorized to approve this application']);
        exit;
    }

    // If workflow exists, verify approver is part of current step
    if ($hasWorkflow && $policyID && $currentStepOrder) {
        $currentStepApprovers = Leave::get_workflow_approvers($policyID, $DBConn);
        $isAuthorizedApprover = false;

        foreach ($currentStepApprovers as $approver) {
            if (isset($approver['stepOrder']) && (int)$approver['stepOrder'] === (int)$currentStepOrder) {
                if (isset($approver['approverUserID']) && (int)$approver['approverUserID'] === (int)$approverId) {
                    $isAuthorizedApprover = true;
                    break;
                }
            }
        }

        // Fallback: Check if user is HR manager (for backward compatibility)
        if (!$isAuthorizedApprover) {
            $isHrManager = Employee::is_hr_manager($approverId, $DBConn, $entityID);
            if (!$isHrManager) {
                echo json_encode(['success' => false, 'message' => 'You are not authorized to approve this application at this step']);
                exit;
            }
        }
    } else {
        // No workflow - use legacy HR manager check
        $isHrManager = Employee::is_hr_manager($approverId, $DBConn, $entityID);
        if (!$isHrManager) {
            echo json_encode(['success' => false, 'message' => 'You are not authorized to approve this application']);
            exit;
        }
    }

    // Prevent approval if final step and mandatory handover incomplete
    $leaveRequiresHandover = isset($leaveArray['handoverRequired']) && $leaveArray['handoverRequired'] === 'Y';
    if ($decision === 'approve' && $leaveRequiresHandover && $hasWorkflow && $isFinalStep) {
        // Check FSM state - must be in ST_05 (Manager Review) to approve
        if (class_exists('LeaveHandoverFSM')) {
            $fsmState = LeaveHandoverFSM::get_current_state($leaveId, $DBConn);
            if ($fsmState) {
                if ($fsmState->currentState !== LeaveHandoverFSM::STATE_MANAGER_REVIEW) {
                    $stateNames = array(
                        LeaveHandoverFSM::STATE_DRAFT => 'Draft',
                        LeaveHandoverFSM::STATE_HANDOVER_COMPOSITION => 'Composing Handover',
                        LeaveHandoverFSM::STATE_PEER_NEGOTIATION => 'Awaiting Peer Response',
                        LeaveHandoverFSM::STATE_HANDOVER_REVISION => 'Revision Required',
                        LeaveHandoverFSM::STATE_HANDOVER_ACCEPTED => 'Handover Accepted',
                        LeaveHandoverFSM::STATE_MANAGER_REVIEW => 'Manager Review',
                        LeaveHandoverFSM::STATE_APPROVED => 'Approved',
                        LeaveHandoverFSM::STATE_REJECTED => 'Rejected'
                    );
                    $currentStateName = $stateNames[$fsmState->currentState] ?? $fsmState->currentState;

                    echo json_encode([
                        'success' => false,
                        'message' => "Cannot approve: Handover is in '{$currentStateName}' state. Handover must be accepted by peer before manager approval."
                    ]);
                    exit;
                }
            }
        }

        $handoverStatus = LeaveHandover::get_handover_status($leaveId, $DBConn);
        if (($handoverStatus['pendingAssignments'] ?? 0) > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Handover is not fully confirmed yet. Please ensure all assignments are acknowledged before final approval.'
            ]);
            exit;
        }
    }

    // Start transaction
    $DBConn->begin();

    try {
        $now = date('Y-m-d H:i:s');

        $statusId = $decision === 'approve' ? 6 : 4;

        // Update leave application status
        $updateResult = $DBConn->update_table(
            'tija_leave_applications',
            array(
                'leaveStatusID' => $statusId,
                'LastUpdate' => $now,
                'LastUpdateByID' => $approverId
            ),
            array('leaveApplicationID' => $leaveId)
        );

        // Transition FSM state if handover is required
        if ($leaveRequiresHandover && class_exists('LeaveHandoverFSM')) {
            $trigger = $decision === 'approve'
                ? LeaveHandoverFSM::TRIGGER_MANAGER_APPROVE
                : LeaveHandoverFSM::TRIGGER_MANAGER_REJECT;

            LeaveHandoverFSM::transition_state(
                $leaveId,
                $trigger,
                $approverId,
                array(),
                $DBConn
            );
        }

        if (!$updateResult) {
            throw new Exception('Failed to update leave application');
        }

        Leave::ensure_leave_approval_comments_table($DBConn);

        // Persist approval record
        $DBConn->insert_data('tija_leave_approvals', array(
            'leaveApplicationID' => $leaveArray['leaveApplicationID'] ?? $leaveId,
            'employeeID' => $leaveArray['employeeID'] ?? null,
            'leaveTypeID' => $leaveArray['leaveTypeID'] ?? null,
            'leavePeriodID' => $leaveArray['leavePeriodID'] ?? null,
            'leaveApproverID' => $approverId,
            'leaveDate' => $now,
            'leaveStatusID' => $statusId,
            'leaveStatus' => $decision === 'approve' ? 'approved' : 'rejected',
            'approversComments' => $comment,
            'LastUpdateByID' => $approverId,
            'LastUpdate' => $now,
            'Lapsed' => 'N',
            'Suspended' => 'N'
        ));

        // Save comment if provided
        if (!empty($comment)) {
            $DBConn->insert_data('tija_leave_approval_comments', array(
                'leaveApplicationID' => $leaveId,
                'approverID' => $approverId,
                'approverUserID' => $approverId,
                'approvalLevel' => 'hr_manager',
                'comment' => $comment,
                'commentDate' => $now,
                'DateAdded' => $now,
                'Lapsed' => 'N',
                'Suspended' => 'N'
            ));
        }

        // Audit log
        $DBConn->insert_data('tija_leave_audit_log', array(
            'entityType' => 'approval',
            'entityID' => $leaveId,
            'action' => $decision === 'approve' ? 'approved' : 'rejected',
            'oldValues' => json_encode(array(
                'leaveStatusID' => $leaveArray['leaveStatusID'] ?? null
            )),
            'newValues' => json_encode(array(
                'leaveStatusID' => $statusId
            )),
            'performedByID' => $approverId,
            'performedDate' => 'now()',
            'reason' => $comment ?: 'NULL'
        ));

        // If workflow exists and this is final step, process final approval
        if ($hasWorkflow && $isFinalStep && $decision === 'approve') {
            // Update workflow instance to completed
            if ($instanceID) {
                // Check which status columns exist
                $columnsCheck = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_instances LIKE 'status'", array());
                $hasStatusColumn = ($columnsCheck && count($columnsCheck) > 0);

                $workflowColumnsCheck = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_instances LIKE 'workflowStatus'", array());
                $hasWorkflowStatusColumn = ($workflowColumnsCheck && count($workflowColumnsCheck) > 0);

                $updateData = array('completedAt' => $now);

                if ($hasStatusColumn) {
                    $updateData['status'] = 'completed';
                }
                if ($hasWorkflowStatusColumn) {
                    $updateData['workflowStatus'] = 'completed';
                }

                $DBConn->update_table(
                    'tija_leave_approval_instances',
                    $updateData,
                    array('instanceID' => $instanceID)
                );
            }
        } elseif ($hasWorkflow && $decision === 'approve') {
            // Not final step - advance to next step
            if ($instanceID && $policyID && $currentStepOrder) {
                $nextStepOrder = $currentStepOrder + 1;
                LeaveNotifications::advanceApprovalInstance($instanceID, $policyID, $nextStepOrder, $approverId, $DBConn);

                // Notify next step approvers
                $nextStepApprovers = Leave::get_workflow_approvers($policyID, $DBConn);
                $employeeDetails = Employee::employees(array('ID' => $leaveArray['employeeID'] ?? null), true, $DBConn);
                $employeeName = $employeeDetails ? ($employeeDetails->FirstName . ' ' . $employeeDetails->Surname) : 'Employee';

                foreach ($nextStepApprovers as $nextApprover) {
                    if (isset($nextApprover['stepOrder']) && (int)$nextApprover['stepOrder'] === $nextStepOrder) {
                        if (!empty($nextApprover['approverUserID'])) {
                            Notification::create(array(
                                'eventSlug' => 'leave_pending_approval',
                                'userId' => $nextApprover['approverUserID'],
                                'originatorId' => $leaveArray['employeeID'] ?? null,
                                'data' => array(
                                    'employee_id' => $leaveArray['employeeID'] ?? null,
                                    'employee_name' => $employeeName,
                                    'leave_type' => $leaveArray['leaveTypeName'] ?? 'Leave',
                                    'start_date' => isset($leaveArray['startDate']) ? date('M j, Y', strtotime($leaveArray['startDate'])) : '',
                                    'end_date' => isset($leaveArray['endDate']) ? date('M j, Y', strtotime($leaveArray['endDate'])) : '',
                                    'total_days' => $leaveArray['noOfDays'] ?? 0,
                                    'application_id' => $leaveId,
                                    'approval_level' => $nextStepOrder,
                                    'step_name' => $nextApprover['stepName'] ?? 'Approval Step',
                                    'approver_name' => $nextApprover['approverName'] ?? 'Approver'
                                ),
                                'link' => '?s=user&ss=leave&p=pending_approvals&id=' . $leaveId,
                                'entityID' => $entityID,
                                'orgDataID' => $leaveArray['orgDataID'] ?? null,
                                'segmentType' => 'leave_application',
                                'segmentID' => $leaveId,
                                'priority' => 'high'
                            ), $DBConn);
                        }
                    }
                }
            }
        }

        // Notifications
        if ($decision === 'approve') {
            LeaveNotifications::notifyLeaveApproved($leaveId, $approverId, 0, $isFinalStep, $comment, $DBConn);
        } else {
            // If rejected, mark workflow as rejected
            if ($hasWorkflow && $instanceID) {
                // Check which status columns exist
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
            }
            LeaveNotifications::notifyLeaveRejected($leaveId, $approverId, $comment, $DBConn);
        }

        $DBConn->commit();

        echo json_encode([
            'success' => true,
            'message' => "Leave application {$decision}d successfully",
            'decision' => $decision,
            'finalApproval' => $isFinalStep,
            'hasWorkflow' => $hasWorkflow
        ]);

    } catch (Exception $e) {
        $DBConn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    error_log('Submit approval decision error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while submitting the approval decision']);
}
?>
