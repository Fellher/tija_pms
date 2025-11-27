<?php
/**
 * Handle Peer Response
 *
 * Processes peer (nominee) responses to handover requests:
 * - Accept handover (transitions to ST_04)
 * - Request changes (transitions to ST_03)
 * - Handles revision loops with max attempt limits
 */
session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';

header('Content-Type: application/json');

if (!$isValidUser) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $handoverID = isset($_POST['handoverID']) ? (int)Utility::clean_string($_POST['handoverID']) : 0;
    $response = isset($_POST['response']) ? Utility::clean_string($_POST['response']) : ''; // 'accept' or 'request_change'
    $requestedChanges = isset($_POST['requestedChanges']) ? Utility::clean_string($_POST['requestedChanges']) : '';
    $assignmentID = isset($_POST['assignmentID']) ? (int)Utility::clean_string($_POST['assignmentID']) : null;

    if (!$handoverID || !in_array($response, array('accept', 'request_change'))) {
        echo json_encode(['success' => false, 'message' => 'Missing or invalid parameters']);
        exit;
    }

    // Get handover details
    $handover = $DBConn->fetch_all_rows(
        "SELECT h.*, la.leaveApplicationID, la.employeeID, la.entityID, la.orgDataID,
                CONCAT(emp.FirstName, ' ', emp.Surname) as employeeName,
                CONCAT(nom.FirstName, ' ', nom.Surname) as nomineeName,
                lt.leaveTypeName
         FROM tija_leave_handovers h
         LEFT JOIN tija_leave_applications la ON h.leaveApplicationID = la.leaveApplicationID
         LEFT JOIN people emp ON la.employeeID = emp.ID
         LEFT JOIN people nom ON h.nomineeID = nom.ID
         LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
         WHERE h.handoverID = ?
         LIMIT 1",
        array(array($handoverID, 'i'))
    );

    if (!$handover || count($handover) === 0) {
        echo json_encode(['success' => false, 'message' => 'Handover not found']);
        exit;
    }

    $handover = is_object($handover[0]) ? $handover[0] : (object)$handover[0];
    $currentUserId = $userDetails->ID;

    // Verify user is the nominee
    if ((int)$handover->nomineeID !== (int)$currentUserId) {
        echo json_encode(['success' => false, 'message' => 'You are not authorized to respond to this handover']);
        exit;
    }

    // Get current FSM state
    $fsmState = LeaveHandoverFSM::get_current_state($handover->leaveApplicationID, $DBConn);
    if (!$fsmState || $fsmState->currentState !== LeaveHandoverFSM::STATE_PEER_NEGOTIATION) {
        echo json_encode(['success' => false, 'message' => 'Handover is not in peer negotiation state']);
        exit;
    }

    // Get policy to check max revision attempts
    $policy = null;
    if ($handover->policyID) {
        $policyRows = $DBConn->fetch_all_rows(
            "SELECT * FROM tija_leave_handover_policies WHERE policyID = ? LIMIT 1",
            array(array($handover->policyID, 'i'))
        );
        if ($policyRows && count($policyRows) > 0) {
            $policy = is_object($policyRows[0]) ? $policyRows[0] : (object)$policyRows[0];
        }
    }

    $maxRevisionAttempts = $policy && isset($policy->maxRevisionAttempts) ? (int)$policy->maxRevisionAttempts : 3;
    $currentRevisionCount = (int)$fsmState->revisionCount;

    $DBConn->begin();

    try {
        if ($response === 'accept') {
            // Accept handover - transition to ST_04
            $transitionResult = LeaveHandoverFSM::transition_state(
                $handover->leaveApplicationID,
                LeaveHandoverFSM::TRIGGER_PEER_ACCEPT,
                $currentUserId,
                array(
                    'handoverID' => $handoverID,
                    'nomineeID' => $currentUserId
                ),
                $DBConn
            );

            if (!$transitionResult) {
                throw new Exception('Failed to transition FSM state');
            }

            // Log chain of custody
            LeaveHandoverFSM::log_state_transition(
                $handover->leaveApplicationID,
                LeaveHandoverFSM::STATE_PEER_NEGOTIATION,
                LeaveHandoverFSM::STATE_HANDOVER_ACCEPTED,
                LeaveHandoverFSM::TRIGGER_PEER_ACCEPT,
                $currentUserId,
                $DBConn
            );

            // Auto-transition to Manager Review (ST_05)
            LeaveHandoverFSM::transition_state(
                $handover->leaveApplicationID,
                LeaveHandoverFSM::TRIGGER_SYSTEM_AUTO_ROUTE,
                $currentUserId,
                array('handoverID' => $handoverID),
                $DBConn
            );

            // Create negotiation record
            $DBConn->insert_data('tija_leave_handover_peer_negotiations', array(
                'handoverID' => $handoverID,
                'assignmentID' => $assignmentID,
                'nomineeID' => $currentUserId,
                'requesterID' => $handover->employeeID,
                'negotiationType' => 'accept',
                'negotiationStatus' => 'resolved',
                'responseDate' => date('Y-m-d H:i:s'),
                'DateAdded' => date('Y-m-d H:i:s')
            ));

            // Notify requester
            if (class_exists('Notification')) {
                Notification::create(array(
                    'eventSlug' => 'leave_handover_accepted',
                    'userId' => $handover->employeeID,
                    'originatorId' => $currentUserId,
                    'data' => array(
                        'employee_name' => $handover->employeeName ?? 'Employee',
                        'nominee_name' => $handover->nomineeName ?? 'Team Member',
                        'leave_type' => $handover->leaveTypeName ?? 'Leave',
                        'handover_id' => $handoverID,
                        'application_id' => $handover->leaveApplicationID
                    ),
                    'link' => '?s=user&ss=leave&p=view_leave_application&id=' . $handover->leaveApplicationID,
                    'entityID' => $handover->entityID,
                    'orgDataID' => $handover->orgDataID,
                    'segmentType' => 'leave_application',
                    'segmentID' => $handover->leaveApplicationID,
                    'priority' => 'medium'
                ), $DBConn);
            }

            $DBConn->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Handover accepted successfully. The request has been forwarded to manager for review.'
            ]);

        } elseif ($response === 'request_change') {
            // Check revision limit
            if ($currentRevisionCount >= $maxRevisionAttempts) {
                $DBConn->rollback();
                echo json_encode([
                    'success' => false,
                    'message' => "Maximum revision attempts ({$maxRevisionAttempts}) reached. Please contact HR or escalate."
                ]);
                exit;
            }

            if (empty($requestedChanges)) {
                $DBConn->rollback();
                echo json_encode(['success' => false, 'message' => 'Please provide details of requested changes']);
                exit;
            }

            // Request changes - transition to ST_03
            $transitionResult = LeaveHandoverFSM::transition_state(
                $handover->leaveApplicationID,
                LeaveHandoverFSM::TRIGGER_PEER_REQUEST_CHANGE,
                $currentUserId,
                array(
                    'handoverID' => $handoverID,
                    'requestedChanges' => $requestedChanges
                ),
                $DBConn
            );

            if (!$transitionResult) {
                throw new Exception('Failed to transition FSM state');
            }

            // Create negotiation record
            $negotiationID = $DBConn->insert_data('tija_leave_handover_peer_negotiations', array(
                'handoverID' => $handoverID,
                'assignmentID' => $assignmentID,
                'nomineeID' => $currentUserId,
                'requesterID' => $handover->employeeID,
                'negotiationType' => 'request_change',
                'requestedChanges' => $requestedChanges,
                'negotiationStatus' => 'pending',
                'responseDate' => date('Y-m-d H:i:s'),
                'DateAdded' => date('Y-m-d H:i:s')
            ));

            if ($negotiationID && $assignmentID) {
                // Update assignment with negotiation ID
                $DBConn->update_table('tija_leave_handover_assignments', array(
                    'negotiationID' => $negotiationID,
                    'revisionRequested' => 'Y',
                    'LastUpdate' => date('Y-m-d H:i:s')
                ), array('assignmentID' => $assignmentID));
            }

            // Notify requester
            if (class_exists('Notification')) {
                Notification::create(array(
                    'eventSlug' => 'leave_handover_revision_requested',
                    'userId' => $handover->employeeID,
                    'originatorId' => $currentUserId,
                    'data' => array(
                        'employee_name' => $handover->employeeName ?? 'Employee',
                        'nominee_name' => $handover->nomineeName ?? 'Team Member',
                        'leave_type' => $handover->leaveTypeName ?? 'Leave',
                        'handover_id' => $handoverID,
                        'application_id' => $handover->leaveApplicationID,
                        'requested_changes' => $requestedChanges,
                        'revision_count' => $currentRevisionCount + 1,
                        'max_revisions' => $maxRevisionAttempts
                    ),
                    'link' => '?s=user&ss=leave&p=apply_leave_workflow&edit=' . $handover->leaveApplicationID,
                    'entityID' => $handover->entityID,
                    'orgDataID' => $handover->orgDataID,
                    'segmentType' => 'leave_application',
                    'segmentID' => $handover->leaveApplicationID,
                    'priority' => 'high'
                ), $DBConn);
            }

            $DBConn->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Revision requested. The requester has been notified and can update the handover plan.',
                'revision_count' => $currentRevisionCount + 1,
                'max_revisions' => $maxRevisionAttempts
            ]);
        }

    } catch (Exception $e) {
        $DBConn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to process peer response: ' . $e->getMessage()
    ]);
}
?>

