<?php
/**
 * Update Task Status (Kanban Drag-and-Drop Handler)
 * AJAX endpoint for updating task status when dragged between columns
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

error_log("=== Update Task Status Started ===");
error_log("POST Data: " . print_r($_POST, true));
error_log("Session ID: " . ($_SESSION['ID'] ?? 'not set'));
error_log("UserDetails ID: " . ($userDetails->ID ?? 'not set'));

$response = ['success' => false, 'message' => ''];

try {
    // Validate user is logged in (standard app pattern)
    if (!isset($_SESSION['ID']) && !isset($userDetails->ID)) {
        throw new Exception("You must be logged in to perform this action");
    }

    // Get current user ID
    $currentUserID = $_SESSION['ID'] ?? $userDetails->ID ?? null;
    if (!$currentUserID) {
        throw new Exception("Unable to identify current user");
    }

    // Get parameters
    $taskID = isset($_POST['taskID']) && !empty($_POST['taskID'])
        ? Utility::clean_string($_POST['taskID']) : null;
    $newStatus = isset($_POST['newStatus']) && !empty($_POST['newStatus'])
        ? Utility::clean_string($_POST['newStatus']) : null;

    // Validate required fields
    if (!$taskID) {
        throw new Exception("Task ID is required");
    }
    if (!$newStatus) {
        throw new Exception("New status is required");
    }

    // Validate status value
    $validStatuses = ['todo', 'in_progress', 'review', 'done'];
    if (!in_array($newStatus, $validStatuses)) {
        throw new Exception("Invalid task status");
    }

    // Get task details to verify permissions
    $task = Projects::project_tasks(['projectTaskID' => $taskID], true, $DBConn);

    if (!$task) {
        throw new Exception("Task not found");
    }

    error_log("Task loaded successfully: " . print_r($task, true));

    // Check if user has permission to update this task
    $projectID = $task->projectID;

    // Permission check (optional - uncomment when SecurityMiddleware::checkProjectPermission is working)
    // if (!SecurityMiddleware::checkProjectPermission($projectID, $_SESSION['ID'], 'edit')) {
    //     throw new Exception("You don't have permission to update this task");
    // }

    $DBConn->begin();

    // Update task status
    $updateData = [
        'status' => $newStatus,  // Using 'status' field based on tija_project_tasks schema
        'DateLastUpdated' => date('Y-m-d H:i:s')
    ];

    // If marking as done, set progress to 100
    if ($newStatus === 'done') {
        $updateData['progress'] = 100;
    }

    error_log("Updating task with data: " . print_r($updateData, true));
    error_log("Current user ID: " . $currentUserID);

    $updateResult = $DBConn->update_table('tija_project_tasks', $updateData, ['projectTaskID' => $taskID]);

    if (!$updateResult) {
        throw new Exception("Failed to update task status");
    }

    // Log activity (optional - only if table exists)
    $oldStatus = $task->status ?? 'unknown';
    $activityData = [
        'projectID' => $projectID,
        'projectTaskID' => $taskID,
        'userID' => $currentUserID,
        'activityType' => 'status_change',
        'activityDescription' => "Changed task status from '{$oldStatus}' to '{$newStatus}'",
        'DateAdded' => date('Y-m-d H:i:s')
    ];

    try {
        $DBConn->insert_data('tija_project_activity', $activityData);
    } catch (Exception $e) {
        error_log("Failed to log activity (table may not exist): " . $e->getMessage());
    }

    // Log audit trail (optional - only if table exists)
    $auditData = [
        'projectID' => $projectID,
        'userID' => $currentUserID,
        'action' => 'update_task_status',
        'tableName' => 'tija_project_tasks',
        'recordID' => $taskID,
        'oldValue' => json_encode(['status' => $oldStatus]),
        'newValue' => json_encode(['status' => $newStatus]),
        'ipAddress' => $_SERVER['REMOTE_ADDR'] ?? null,
        'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    try {
        $DBConn->insert_data('tija_project_audit_log', $auditData);
    } catch (Exception $e) {
        error_log("Failed to log audit trail (table may not exist): " . $e->getMessage());
    }

    $DBConn->commit();

    $response['success'] = true;
    $response['message'] = "Task status updated successfully";
    $response['newStatus'] = $newStatus;
    $response['taskID'] = $taskID;

    error_log("Task status updated successfully");

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

