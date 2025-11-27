<?php
/**
 * Get Peer Assignments
 *
 * Returns handover assignments for a nominee (peer).
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
    $handoverID = isset($_GET['handoverID']) ? (int)Utility::clean_string($_GET['handoverID']) : 0;
    $nomineeID = isset($_GET['nomineeID']) ? (int)Utility::clean_string($_GET['nomineeID']) : $userDetails->ID;

    if (!$handoverID) {
        echo json_encode(['success' => false, 'message' => 'Handover ID is required']);
        exit;
    }

    // Get handover details
    $handover = $DBConn->fetch_all_rows(
        "SELECT h.*, la.leaveApplicationID, la.startDate, la.endDate, la.employeeID,
                CONCAT(emp.FirstName, ' ', emp.Surname) as employeeName,
                lt.leaveTypeName
         FROM tija_leave_handovers h
         LEFT JOIN tija_leave_applications la ON h.leaveApplicationID = la.leaveApplicationID
         LEFT JOIN people emp ON la.employeeID = emp.ID
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

    // Verify user is the nominee or has admin access
    if ((int)$handover->nomineeID !== (int)$nomineeID && !$isAdmin && !$isValidAdmin && !$isHRManager) {
        echo json_encode(['success' => false, 'message' => 'You are not authorized to view this handover']);
        exit;
    }

    // Get assignments for this nominee
    $assignments = LeaveHandover::get_assignments_for_user($nomineeID, $DBConn, array('pending', 'acknowledged'));

    // Filter by handover ID
    $handoverAssignments = array();
    foreach ($assignments as $assignment) {
        $assignment = is_object($assignment) ? (object)$assignment : (object)$assignment;
        if (isset($assignment->handoverID) && (int)$assignment->handoverID === $handoverID) {
            $handoverAssignments[] = $assignment;
        }
    }

    // Get handover items
    $items = $DBConn->fetch_all_rows(
        "SELECT i.*, a.assignmentID, a.confirmationStatus, a.revisionRequested
         FROM tija_leave_handover_items i
         LEFT JOIN tija_leave_handover_assignments a ON i.handoverItemID = a.handoverItemID AND a.assignedToID = ?
         WHERE i.handoverID = ?
         AND i.Lapsed = 'N'
         AND i.Suspended = 'N'
         ORDER BY i.priority DESC, i.itemTitle ASC",
        array(
            array($nomineeID, 'i'),
            array($handoverID, 'i')
        )
    );

    // Get FSM state
    $fsmState = null;
    if (class_exists('LeaveHandoverFSM')) {
        $fsmState = LeaveHandoverFSM::get_current_state($handover->leaveApplicationID, $DBConn);
    }

    // Get timer info
    $timerInfo = null;
    if ($fsmState && $fsmState->currentState === LeaveHandoverFSM::STATE_PEER_NEGOTIATION) {
        $timerInfo = LeaveHandoverFSM::check_timer_expiry($handover->leaveApplicationID, $DBConn);
    }

    echo json_encode([
        'success' => true,
        'handover' => array(
            'handoverID' => $handover->handoverID,
            'leaveApplicationID' => $handover->leaveApplicationID,
            'employeeName' => $handover->employeeName,
            'leaveTypeName' => $handover->leaveTypeName,
            'startDate' => $handover->startDate,
            'endDate' => $handover->endDate
        ),
        'items' => $items ? $items : array(),
        'assignments' => $handoverAssignments,
        'fsmState' => $fsmState ? array(
            'currentState' => $fsmState->currentState,
            'revisionCount' => (int)$fsmState->revisionCount
        ) : null,
        'timerInfo' => $timerInfo
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to get peer assignments: ' . $e->getMessage()
    ]);
}
?>

