<?php
/**
 * Update Task Phase (Kanban Drag-and-Drop Handler)
 * AJAX endpoint for updating task phase when dragged between kanban columns
 *
 * @package Tija Practice Management System
 * @subpackage Projects
 * @version 2.0
 */

session_start();
$base = '../../../';
set_include_path($base);

header('Content-Type: application/json');

// Include dependencies
require_once 'php/includes.php';

error_log("=== Update Task Phase Started ===");
error_log("POST Data: " . print_r($_POST, true));

$response = ['success' => false, 'message' => ''];

try {
    // Validate user is logged in
    if (!isset($userDetails->ID) && !$isValidUser) {
        throw new Exception("You must be logged in to perform this action");
    }

    // Get current user ID
    $currentUserID = $userDetails->ID ?? null;
    if (!$currentUserID) {
        throw new Exception("Unable to identify current user");
    }

    // Get parameters
    $taskID = isset($_POST['taskID']) && !empty($_POST['taskID'])
        ? Utility::clean_string($_POST['taskID']) : null;
    $newPhaseID = isset($_POST['newPhaseID']) && !empty($_POST['newPhaseID'])
        ? Utility::clean_string($_POST['newPhaseID']) : null;
    $oldPhaseID = isset($_POST['oldPhaseID']) && !empty($_POST['oldPhaseID'])
        ? Utility::clean_string($_POST['oldPhaseID']) : null;
    $projectID = isset($_POST['projectID']) && !empty($_POST['projectID'])
        ? Utility::clean_string($_POST['projectID']) : null;

    // Validate required fields
    if (!$taskID) {
        throw new Exception("Task ID is required");
    }
    if (!$newPhaseID) {
        throw new Exception("New phase ID is required");
    }
    if (!$projectID) {
        throw new Exception("Project ID is required");
    }

    // Get task details to verify it exists and belongs to the project
    $task = Projects::project_tasks(['projectTaskID' => $taskID], true, $DBConn);

    if (!$task) {
        throw new Exception("Task not found");
    }

    // Verify task belongs to the project
    if ($task->projectID != $projectID) {
        throw new Exception("Task does not belong to this project");
    }

    // Verify new phase exists and belongs to the project
    $phase = Projects::project_phases(['projectPhaseID' => $newPhaseID], true, $DBConn);

    if (!$phase) {
        throw new Exception("Target phase not found");
    }

    if ($phase->projectID != $projectID) {
        throw new Exception("Phase does not belong to this project");
    }

    error_log("Task: " . print_r($task, true));
    error_log("Phase: " . print_r($phase, true));

    $DBConn->begin();

    // Update task phase
    $updateData = [
        'projectPhaseID' => $newPhaseID,
        'LastUpdate' => date('Y-m-d H:i:s'),
        'LastUpdatedByID' => $currentUserID
    ];

    error_log("Updating task with data: " . print_r($updateData, true));

    $updateResult = $DBConn->update_table('tija_project_tasks', $updateData, ['projectTaskID' => $taskID]);

    if (!$updateResult) {
        $errorInfo = $DBConn->errorInfo();
        error_log("Database error: " . print_r($errorInfo, true));
        throw new Exception("Failed to update task phase: " . ($errorInfo[2] ?? 'Unknown database error'));
    }

    // Log activity (optional - only if table exists)
    $activityData = [
        'projectID' => $projectID,
        'projectTaskID' => $taskID,
        'userID' => $currentUserID,
        'activityType' => 'phase_change',
        'activityDescription' => "Moved task from phase ID {$oldPhaseID} to phase ID {$newPhaseID}",
        'DateAdded' => date('Y-m-d H:i:s')
    ];

    try {
        $DBConn->insert_data('tija_project_activity', $activityData);
    } catch (Exception $e) {
        error_log("Failed to log activity (table may not exist): " . $e->getMessage());
    }

    $DBConn->commit();

    $response['success'] = true;
    $response['message'] = "Task moved successfully";
    $response['taskID'] = $taskID;
    $response['newPhaseID'] = $newPhaseID;
    $response['oldPhaseID'] = $oldPhaseID;

    error_log("Task phase updated successfully");

} catch (Exception $e) {
    error_log("=== EXCEPTION CAUGHT ===");
    error_log("Exception: " . $e->getMessage());
    error_log("Trace: " . $e->getTraceAsString());

    if ($DBConn) {
        try {
            $DBConn->rollback();
            error_log("Transaction rolled back");
        } catch (Exception $rollbackError) {
            error_log("Rollback error: " . $rollbackError->getMessage());
        }
    }

    $response['message'] = $e->getMessage();
    $response['success'] = false;
}

error_log("Final response: " . print_r($response, true));

echo json_encode($response);
exit;
?>


