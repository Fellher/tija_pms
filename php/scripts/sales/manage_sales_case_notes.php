<?php
/**
 * Manage Sales Case Notes and Next Steps API
 * Simplified version with essential features
 */

session_start();
$base = '../../../';
set_include_path($base);
include 'php/includes.php';

// Initialize response
$response = array('success' => false, 'message' => '');

// Check authentication
if (!isset($userDetails->ID) || empty($userDetails->ID)) {
    $response['message'] = 'Unauthorized access.';
    echo json_encode($response);
    exit;
}

$userID = $userDetails->ID;
$action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : '';

try {
    $DBConn->begin();

    switch ($action) {
        case 'addNote':
            $response = addNote($DBConn, $userID);
            break;

        case 'getNotes':
            $response = getNotes($DBConn, $userID);
            break;

        case 'deleteNote':
            $response = deleteNote($DBConn, $userID);
            break;

        case 'addNextStep':
            $response = addNextStep($DBConn, $userID);
            break;

        case 'getNextSteps':
            $response = getNextSteps($DBConn, $userID);
            break;

        case 'updateNextStepStatus':
            $response = updateNextStepStatus($DBConn, $userID);
            break;

        case 'deleteNextStep':
            $response = deleteNextStep($DBConn, $userID);
            break;

        default:
            $response['message'] = 'Invalid action specified.';
            echo json_encode($response);
            exit;
    }

    if ($response['success']) {
        $DBConn->commit();
    } else {
        $DBConn->rollBack();
    }

    echo json_encode($response);

} catch (Exception $e) {
    $DBConn->rollBack();
    error_log("Sales Case Notes Error: " . $e->getMessage());
    $response['success'] = false;
    $response['message'] = 'An error occurred: ' . $e->getMessage();
    echo json_encode($response);
}

/**
 * Add a note to a sales case
 */
function addNote($DBConn, $userID) {
    $salesCaseID = isset($_POST['salesCaseID']) ? (int)$_POST['salesCaseID'] : 0;
    $noteText = isset($_POST['noteText']) ? Utility::clean_string($_POST['noteText']) : '';
    $noteType = isset($_POST['noteType']) ? Utility::clean_string($_POST['noteType']) : 'general';
    $saleStatusLevelID = isset($_POST['saleStatusLevelID']) ? (int)$_POST['saleStatusLevelID'] : null;

    if (!$salesCaseID || empty($noteText)) {
        return array('success' => false, 'message' => 'Sales case ID and note text are required.');
    }

    $noteData = array(
        'salesCaseID' => $salesCaseID,
        'saleStatusLevelID' => $saleStatusLevelID,
        'noteText' => $noteText,
        'noteType' => $noteType,
        'isPrivate' => $noteType === 'private' ? 'Y' : 'N',
        'createdByID' => $userID
    );

    // For private notes, handle recipients
    $recipients = array();
    if ($noteType === 'private' && isset($_POST['recipients'])) {
        $recipients = is_array($_POST['recipients']) ? $_POST['recipients'] : explode(',', $_POST['recipients']);
    }

    $result = Sales::add_sales_case_note($noteData, $recipients, $DBConn);
    return $result;
}

/**
 * Get notes for a sales case
 */
function getNotes($DBConn, $userID) {
    $salesCaseID = isset($_GET['salesCaseID']) ? (int)$_GET['salesCaseID'] : 0;

    if (!$salesCaseID) {
        return array('success' => false, 'message' => 'Sales case ID is required.');
    }

    $notes = Sales::sales_case_notes_full($salesCaseID, $userID, $DBConn);

    return array(
        'success' => true,
        'notes' => $notes ? $notes : array()
    );
}

/**
 * Delete a note
 */
function deleteNote($DBConn, $userID) {
    $noteID = isset($_POST['noteID']) ? (int)$_POST['noteID'] : 0;

    if (!$noteID) {
        return array('success' => false, 'message' => 'Note ID is required.');
    }

    return Sales::delete_sales_case_note($noteID, $userID, $DBConn);
}

/**
 * Add a next step
 */
function addNextStep($DBConn, $userID) {
    $salesCaseID = isset($_POST['salesCaseID']) ? (int)$_POST['salesCaseID'] : 0;
    $description = isset($_POST['nextStepDescription']) ? Utility::clean_string($_POST['nextStepDescription']) : '';
    $dueDate = isset($_POST['dueDate']) ? Utility::clean_string($_POST['dueDate']) : null;
    $priority = isset($_POST['priority']) ? Utility::clean_string($_POST['priority']) : 'medium';
    $assignedToID = isset($_POST['assignedToID']) ? (int)$_POST['assignedToID'] : null;
    $saleStatusLevelID = isset($_POST['saleStatusLevelID']) ? (int)$_POST['saleStatusLevelID'] : null;

    if (!$salesCaseID || empty($description)) {
        return array('success' => false, 'message' => 'Sales case ID and description are required.');
    }

    $stepData = array(
        'salesCaseID' => $salesCaseID,
        'saleStatusLevelID' => $saleStatusLevelID,
        'nextStepDescription' => $description,
        'dueDate' => $dueDate,
        'priority' => $priority,
        'status' => 'pending',
        'assignedToID' => $assignedToID,
        'createdByID' => $userID
    );

    return Sales::add_sales_case_next_step($stepData, $DBConn);
}

/**
 * Get next steps for a sales case
 */
function getNextSteps($DBConn, $userID) {
    $salesCaseID = isset($_GET['salesCaseID']) ? (int)$_GET['salesCaseID'] : 0;

    if (!$salesCaseID) {
        return array('success' => false, 'message' => 'Sales case ID is required.');
    }

    $steps = Sales::sales_case_next_steps_full($salesCaseID, $DBConn);

    return array(
        'success' => true,
        'steps' => $steps ? $steps : array()
    );
}

/**
 * Update next step status
 */
function updateNextStepStatus($DBConn, $userID) {
    $stepID = isset($_POST['stepID']) ? (int)$_POST['stepID'] : 0;
    $status = isset($_POST['status']) ? Utility::clean_string($_POST['status']) : '';

    if (!$stepID || empty($status)) {
        return array('success' => false, 'message' => 'Step ID and status are required.');
    }

    return Sales::update_next_step_status($stepID, $status, $userID, $DBConn);
}

/**
 * Delete a next step
 */
function deleteNextStep($DBConn, $userID) {
    $stepID = isset($_POST['stepID']) ? (int)$_POST['stepID'] : 0;

    if (!$stepID) {
        return array('success' => false, 'message' => 'Step ID is required.');
    }

    return Sales::delete_sales_case_next_step($stepID, $userID, $DBConn);
}
?>
