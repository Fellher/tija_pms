<?php
/**
 * Get FSM State
 *
 * Returns the current FSM state for a leave application.
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

try {
    $leaveApplicationID = isset($_GET['leaveApplicationID']) ? (int)Utility::clean_string($_GET['leaveApplicationID']) : 0;

    if (!$leaveApplicationID) {
        echo json_encode(['success' => false, 'message' => 'Leave application ID is required']);
        exit;
    }

    if (!class_exists('LeaveHandoverFSM')) {
        echo json_encode(['success' => false, 'message' => 'FSM class not available']);
        exit;
    }

    $fsmState = LeaveHandoverFSM::get_current_state($leaveApplicationID, $DBConn);

    if (!$fsmState) {
        echo json_encode([
            'success' => true,
            'state' => null,
            'message' => 'No FSM state found for this application'
        ]);
        exit;
    }

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

    $timerInfo = null;
    if ($fsmState->currentState === LeaveHandoverFSM::STATE_PEER_NEGOTIATION && $fsmState->timerExpiresAt) {
        $timerInfo = LeaveHandoverFSM::check_timer_expiry($leaveApplicationID, $DBConn);
    }

    echo json_encode([
        'success' => true,
        'state' => array(
            'stateID' => $fsmState->stateID,
            'currentState' => $fsmState->currentState,
            'currentStateName' => $stateNames[$fsmState->currentState] ?? $fsmState->currentState,
            'previousState' => $fsmState->previousState,
            'stateOwnerID' => $fsmState->stateOwnerID,
            'nomineeID' => $fsmState->nomineeID,
            'stateEnteredAt' => $fsmState->stateEnteredAt,
            'revisionCount' => (int)$fsmState->revisionCount,
            'timerInfo' => $timerInfo
        )
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to get FSM state: ' . $e->getMessage()
    ]);
}
?>

