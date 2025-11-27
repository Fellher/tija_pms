<?php
/**
 * Update Gantt Chart Dates
 * AJAX endpoint for updating phase/task dates when dragged in Gantt chart
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

error_log("=== Update Gantt Dates Started ===");
error_log("POST Data: " . print_r($_POST, true));

$response = ['success' => false, 'message' => ''];

try {
    // Validate user is logged in
    if (!isset($_SESSION['ID']) && !isset($userDetails->ID)) {
        throw new Exception("You must be logged in to perform this action");
    }

    // Get current user ID
    $currentUserID = $_SESSION['ID'] ?? $userDetails->ID ?? null;
    if (!$currentUserID) {
        throw new Exception("Unable to identify current user");
    }

    // Get parameters
    $taskType = isset($_POST['taskType']) && !empty($_POST['taskType'])
        ? Utility::clean_string($_POST['taskType']) : null;
    $taskId = isset($_POST['taskId']) && !empty($_POST['taskId'])
        ? Utility::clean_string($_POST['taskId']) : null;
    $startDate = isset($_POST['startDate']) && !empty($_POST['startDate'])
        ? Utility::clean_string($_POST['startDate']) : null;
    $endDate = isset($_POST['endDate']) && !empty($_POST['endDate'])
        ? Utility::clean_string($_POST['endDate']) : null;
    $projectId = isset($_POST['projectId']) && !empty($_POST['projectId'])
        ? Utility::clean_string($_POST['projectId']) : null;

    // Validate required fields
    if (!$taskType) {
        throw new Exception("Task type is required");
    }
    if (!$taskId) {
        throw new Exception("Task ID is required");
    }
    if (!$startDate) {
        throw new Exception("Start date is required");
    }
    if (!$endDate) {
        throw new Exception("End date is required");
    }
    if (!$projectId) {
        throw new Exception("Project ID is required");
    }

    // Validate date format (YYYY-MM-DD)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
        throw new Exception("Invalid start date format");
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
        throw new Exception("Invalid end date format");
    }

    // Validate end date is not before start date
    if ($endDate < $startDate) {
        throw new Exception("End date cannot be before start date");
    }

    $DBConn->begin();

    // Update based on task type
    if ($taskType === 'phase') {
        // Update phase dates
        $phase = Projects::project_phases(['projectPhaseID' => $taskId], true, $DBConn);

        if (!$phase) {
            throw new Exception("Phase not found");
        }

        if ($phase->projectID != $projectId) {
            throw new Exception("Phase does not belong to this project");
        }

        $updateData = [
            'phaseStartDate' => $startDate,
            'phaseEndDate' => $endDate,
            'LastUpdate' => date('Y-m-d H:i:s'),
            'LastUpdatedByID' => $currentUserID
        ];

        $updateResult = $DBConn->update_table('tija_project_phases', $updateData, ['projectPhaseID' => $taskId]);

        if (!$updateResult) {
            $errorInfo = $DBConn->errorInfo();
            throw new Exception("Failed to update phase dates: " . ($errorInfo[2] ?? 'Unknown database error'));
        }

    } elseif ($taskType === 'task') {
        // Update task dates
        $task = Projects::project_tasks(['projectTaskID' => $taskId], true, $DBConn);

        if (!$task) {
            throw new Exception("Task not found");
        }

        if ($task->projectID != $projectId) {
            throw new Exception("Task does not belong to this project");
        }

        $updateData = [
            'taskStart' => $startDate,
            'taskDeadline' => $endDate,
            'LastUpdate' => date('Y-m-d H:i:s'),
            'LastUpdatedByID' => $currentUserID
        ];

        $updateResult = $DBConn->update_table('tija_project_tasks', $updateData, ['projectTaskID' => $taskId]);

        if (!$updateResult) {
            $errorInfo = $DBConn->errorInfo();
            throw new Exception("Failed to update task dates: " . ($errorInfo[2] ?? 'Unknown database error'));
        }

    } elseif ($taskType === 'subtask') {
        // Update subtask due date
        // Note: Subtasks typically only have a due date, so we'll use endDate as the due date
        $subtask = Projects::project_task_steps(['projectTaskStepID' => $taskId], true, $DBConn);

        if (!$subtask) {
            throw new Exception("Subtask not found");
        }

        // Verify subtask belongs to the project through its parent task
        $parentTask = Projects::project_tasks(['projectTaskID' => $subtask->projectTaskID], true, $DBConn);
        if (!$parentTask || $parentTask->projectID != $projectId) {
            throw new Exception("Subtask does not belong to this project");
        }

        $updateData = [
            'dueDate' => $endDate,
            'LastUpdate' => date('Y-m-d H:i:s'),
            'LastUpdatedByID' => $currentUserID
        ];

        $updateResult = $DBConn->update_table('tija_project_task_steps', $updateData, ['projectTaskStepID' => $taskId]);

        if (!$updateResult) {
            $errorInfo = $DBConn->errorInfo();
            throw new Exception("Failed to update subtask date: " . ($errorInfo[2] ?? 'Unknown database error'));
        }

    } else {
        throw new Exception("Invalid task type: " . $taskType);
    }

    // Log activity (optional - only if table exists)
    $activityData = [
        'projectID' => $projectId,
        'userID' => $currentUserID,
        'activityType' => 'gantt_date_update',
        'activityDescription' => "Updated {$taskType} dates via Gantt chart",
        'DateAdded' => date('Y-m-d H:i:s')
    ];

    if ($taskType === 'task') {
        $activityData['projectTaskID'] = $taskId;
    }

    try {
        $DBConn->insert_data('tija_project_activity', $activityData);
    } catch (Exception $e) {
        error_log("Failed to log activity (table may not exist): " . $e->getMessage());
    }

    $DBConn->commit();

    $response['success'] = true;
    $response['message'] = ucfirst($taskType) . " dates updated successfully";
    $response['taskType'] = $taskType;
    $response['taskId'] = $taskId;
    $response['startDate'] = $startDate;
    $response['endDate'] = $endDate;

    error_log("Gantt dates updated successfully");

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

