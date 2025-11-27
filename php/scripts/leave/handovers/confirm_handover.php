<?php
/**
 * Confirm Handover Assignment
 *
 * Allows assignees to acknowledge/confirm the handover items allocated to them.
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
    $assignmentId = isset($_POST['assignmentId']) ? (int)Utility::clean_string($_POST['assignmentId']) : 0;
    if (!$assignmentId) {
        echo json_encode(['success' => false, 'message' => 'Assignment ID is required']);
        exit;
    }

    $assignmentRows = $DBConn->fetch_all_rows(
        "SELECT * FROM tija_leave_handover_assignments WHERE assignmentID = ? LIMIT 1",
        array(array($assignmentId, 'i'))
    );

    if (!$assignmentRows || !isset($assignmentRows[0])) {
        echo json_encode(['success' => false, 'message' => 'Handover assignment not found']);
        exit;
    }

    $assignment = is_object($assignmentRows[0]) ? $assignmentRows[0] : (object)$assignmentRows[0];

    $currentUserId = $userDetails->ID;
    $isAssignee = (int)$assignment->assignedToID === (int)$currentUserId;

    if (!$isAssignee && !$isAdmin && !$isValidAdmin && !$isHRManager) {
        echo json_encode(['success' => false, 'message' => 'You are not authorized to confirm this handover assignment']);
        exit;
    }

    $confirmationData = array(
        'briefed' => isset($_POST['briefed']) ? Utility::clean_string($_POST['briefed']) : 'Y',
        'briefedDate' => isset($_POST['briefedDate']) ? Utility::clean_string($_POST['briefedDate']) : null,
        'trained' => isset($_POST['trained']) ? Utility::clean_string($_POST['trained']) : 'not_required',
        'trainedDate' => isset($_POST['trainedDate']) ? Utility::clean_string($_POST['trainedDate']) : null,
        'hasCredentials' => isset($_POST['hasCredentials']) ? Utility::clean_string($_POST['hasCredentials']) : 'not_required',
        'credentialsDetails' => isset($_POST['credentialsDetails']) ? Utility::clean_string($_POST['credentialsDetails']) : null,
        'hasTools' => isset($_POST['hasTools']) ? Utility::clean_string($_POST['hasTools']) : 'not_required',
        'toolsDetails' => isset($_POST['toolsDetails']) ? Utility::clean_string($_POST['toolsDetails']) : null,
        'hasDocuments' => isset($_POST['hasDocuments']) ? Utility::clean_string($_POST['hasDocuments']) : 'not_required',
        'documentsDetails' => isset($_POST['documentsDetails']) ? Utility::clean_string($_POST['documentsDetails']) : null,
        'readyToTakeOver' => isset($_POST['readyToTakeOver']) ? Utility::clean_string($_POST['readyToTakeOver']) : 'N',
        'additionalNotes' => isset($_POST['additionalNotes']) ? Utility::clean_string($_POST['additionalNotes']) : null,
        'confirmedByID' => $currentUserId
    );

    LeaveHandover::confirm_handover_assignment($assignmentId, $confirmationData, $DBConn);

    echo json_encode([
        'success' => true,
        'message' => 'Handover assignment updated successfully.'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to confirm handover assignment: ' . $e->getMessage()
    ]);
}
?>


