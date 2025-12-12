<?php
/**
 * Prospect Notes API
 * Handles CRUD operations for prospect notes/comments
 */

session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';
$DBConn->begin();
// Initialize response
$response = array('success' => false, 'message' => '');

// Check if user is logged in
if (!$isValidUser) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit;
}





// Get action
$action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : '';

error_log("=== PROSPECT NOTES API ===");
error_log("Action: " . $action);
error_log("User ID: " . $userDetails->ID);

try {
    switch ($action) {
        case 'getNotes':
            getNotes($DBConn, $userDetails);
            break;

        case 'addNote':
            addNote($DBConn, $userDetails);
            break;

        case 'editNote':
            editNote($DBConn, $userDetails);
            break;

        case 'deleteNote':
            deleteNote($DBConn, $userDetails);
            break;

        default:
            $response['message'] = 'Invalid action specified.';
            echo json_encode($response);
            exit;
    }

    // Commit transaction
    $DBConn->commit();
    error_log("Transaction committed successfully");

} catch (Exception $e) {
    $DBConn->rollBack();
    error_log("Transaction rolled back: " . $e->getMessage());

    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
    echo json_encode($response);
    exit;
}

/**
 * Get all notes for a prospect
 */
function getNotes($DBConn, $userDetails) {
    global $response;

    if (!isset($_POST['salesProspectID'])) {
        $response['message'] = 'Prospect ID is required.';
        echo json_encode($response);
        exit;
    }

    $prospectID = (int)$_POST['salesProspectID'];
    $notes = Sales::getProspectNotes($prospectID, $DBConn);

    $response['success'] = true;
    $response['data'] = $notes;
    echo json_encode($response);
}

/**
 * Add a new note
 */
function addNote($DBConn, $userDetails) {
    global $response;

    if (!isset($_POST['salesProspectID']) || !isset($_POST['noteContent'])) {
        $response['message'] = 'Prospect ID and note content are required.';
        echo json_encode($response);
        exit;
    }

    $result = Sales::addProspectNote($_POST, $userDetails, $DBConn);

    $response['success'] = $result['success'];
    $response['message'] = $result['message'];
    if (isset($result['noteID'])) {
        $response['data'] = array('prospectNoteID' => $result['noteID']);
    }

    echo json_encode($response);
}

/**
 * Edit an existing note
 */
function editNote($DBConn, $userDetails) {
    global $response;

    if (!isset($_POST['prospectNoteID']) || !isset($_POST['noteContent'])) {
        $response['message'] = 'Note ID and content are required.';
        echo json_encode($response);
        exit;
    }

    $noteID = (int)$_POST['prospectNoteID'];
    $result = Sales::editProspectNote($noteID, $_POST, $userDetails, $DBConn);

    $response = $result;
    echo json_encode($response);
}

/**
 * Delete a note
 */
function deleteNote($DBConn, $userDetails) {
    global $response;

    if (!isset($_POST['prospectNoteID'])) {
        $response['message'] = 'Note ID is required.';
        echo json_encode($response);
        exit;
    }

    $noteID = (int)$_POST['prospectNoteID'];
    $result = Sales::deleteProspectNote($noteID, $userDetails, $DBConn);

    $response = $result;
    echo json_encode($response);
}
?>
