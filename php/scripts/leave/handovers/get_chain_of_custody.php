<?php
/**
 * Get Chain of Custody
 *
 * Returns the complete audit trail of FSM state transitions.
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

    $chainOfCustody = LeaveHandoverFSM::get_chain_of_custody($leaveApplicationID, $DBConn);

    // Get employee names for actors
    $actorIDs = array();
    foreach ($chainOfCustody as $entry) {
        if (isset($entry['actor_id'])) {
            $actorIDs[] = (int)$entry['actor_id'];
        }
    }
    $actorIDs = array_unique($actorIDs);

    $actorNames = array();
    if (!empty($actorIDs)) {
        $placeholders = implode(',', array_fill(0, count($actorIDs), '?'));
        $params = array();
        foreach ($actorIDs as $id) {
            $params[] = array($id, 'i');
        }
        $actors = $DBConn->fetch_all_rows(
            "SELECT ID, CONCAT(FirstName, ' ', Surname) as fullName FROM people WHERE ID IN ({$placeholders})",
            $params
        );
        if ($actors) {
            foreach ($actors as $actor) {
                $actor = is_object($actor) ? $actor : (object)$actor;
                $actorNames[$actor->ID] = $actor->fullName;
            }
        }
    }

    // Enhance log entries with actor names
    foreach ($chainOfCustody as &$entry) {
        if (isset($entry['actor_id']) && isset($actorNames[$entry['actor_id']])) {
            $entry['actor_name'] = $actorNames[$entry['actor_id']];
        }
    }

    echo json_encode([
        'success' => true,
        'chainOfCustody' => $chainOfCustody
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to get chain of custody: ' . $e->getMessage()
    ]);
}
?>

