<?php
/**
 * Update Task Status - AJAX Handler
 * Handles drag and drop status updates for project tasks
 * @package    Tija CRM
 * @subpackage Project Collaborations
 */

// Set content type to JSON
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

// Validate required fields
if (!isset($input['taskID']) || !isset($input['statusID']) || !isset($input['projectID'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$taskID = Utility::clean_string($input['taskID']);
$statusID = Utility::clean_string($input['statusID']);
$projectID = Utility::clean_string($input['projectID']);

try {
    // Include necessary files
    require_once '../../../php/includes.php';
    
    // Verify task exists and belongs to project
    $task = Projects::projects_tasks(array('projectTaskID' => $taskID, 'projectID' => $projectID), true, $DBConn);
    
    if (!$task) {
        echo json_encode(['success' => false, 'message' => 'Task not found or does not belong to project']);
        exit;
    }
    
    // Verify status exists
    $status = Projects::task_status(array('taskStatusID' => $statusID), true, $DBConn);
    
    if (!$status) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }
    
    // Update task status
    $updateData = array(
        'taskStatusID' => $statusID,
        'lastUpdatedBy' => $userDetails->ID,
        'lastUpdated' => date('Y-m-d H:i:s')
    );
    
    $whereClause = array('projectTaskID' => $taskID);
    
    $result = $DBConn->update_db_table_rows('tija_project_tasks', $updateData, $whereClause);
    
    if ($result) {
        // Log the status change
        $logData = array(
            'projectTaskID' => $taskID,
            'action' => 'Status Changed',
            'oldValue' => $task->taskStatusID,
            'newValue' => $statusID,
            'changedBy' => $userDetails->ID,
            'changeDate' => date('Y-m-d H:i:s'),
            'changeReason' => 'Status updated via drag and drop'
        );
        
        // Insert into task history log (if table exists)
        try {
            $DBConn->insert_db_table_row('tija_task_history', $logData);
        } catch (Exception $e) {
            // Log table might not exist, continue without error
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Task status updated successfully',
            'data' => [
                'taskID' => $taskID,
                'oldStatus' => $task->taskStatusID,
                'newStatus' => $statusID,
                'statusName' => $status->taskStatusName
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update task status']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
