<?php
/**
 * Proposal Task Management Script
 * Handles create, update, delete, and assignment of proposal tasks
 *
 * @package    Tija CRM
 * @subpackage Sales Management
 */

// Start output buffering to catch any unwanted output
ob_start();

session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';

// Clear any output that might have been generated
ob_clean();

// Set content type to JSON
header('Content-Type: application/json');

// Initialize response
$response = array(
    'success' => false,
    'message' => '',
    'data' => null
);

try {
    // Check if user is logged in
    if (!$isValidUser) {
        throw new Exception('User not authenticated. Please log in to continue.');
    }

    $userID = $userDetails->ID;
    $action = $_POST['action'] ?? 'create';

    switch ($action) {
        case 'create':
            handleTaskCreate($userID, $DBConn);
            break;
        case 'update':
            handleTaskUpdate($userID, $DBConn);
            break;
        case 'delete':
            handleTaskDelete($userID, $DBConn);
            break;
        case 'assign':
            handleTaskAssign($userID, $DBConn);
            break;
        case 'complete':
            handleTaskComplete($userID, $DBConn);
            break;
        case 'get':
            handleGetTask($userID, $DBConn);
            break;
        default:
            throw new Exception('Invalid action specified.');
    }

} catch (Exception $e) {
    // Clear any output
    ob_clean();

    $response['success'] = false;
    $response['message'] = $e->getMessage();
    error_log("Proposal Task Error [User: " . ($userID ?? 'unknown') . "]: " . $e->getMessage());

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} catch (Error $e) {
    // Catch PHP 7+ errors
    ob_clean();

    $response['success'] = false;
    $response['message'] = 'A system error occurred: ' . $e->getMessage();
    error_log("Proposal Task Fatal Error [User: " . ($userID ?? 'unknown') . "]: " . $e->getMessage());

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

/**
 * Output JSON response with clean headers
 */
function outputJsonResponse($response) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

/**
 * Handle task creation
 */
function handleTaskCreate($userID, $DBConn) {
    global $response;

    try {
        // Validate required fields
        if (!isset($_POST['proposalID']) || empty($_POST['proposalID'])) {
            throw new Exception('Proposal ID is required.');
        }

        if (!isset($_POST['taskName']) || empty(trim($_POST['taskName']))) {
            throw new Exception('Task name is required.');
        }

        if (!isset($_POST['assignedTo']) || empty($_POST['assignedTo'])) {
            throw new Exception('Task must be assigned to a user.');
        }

        if (!isset($_POST['dueDate']) || empty($_POST['dueDate'])) {
            throw new Exception('Due date is required.');
        }

        $proposalID = intval($_POST['proposalID']);
        $taskName = Utility::clean_string($_POST['taskName']);
        $taskDescription = isset($_POST['taskDescription']) ? Utility::clean_string($_POST['taskDescription']) : null;
        $assignedTo = intval($_POST['assignedTo']);
        $dueDate = Utility::clean_string($_POST['dueDate']);
        $priority = isset($_POST['priority']) ? Utility::clean_string($_POST['priority']) : 'medium';
        $isMandatory = isset($_POST['isMandatory']) && $_POST['isMandatory'] === 'Y' ? 'Y' : 'N';
        $orgDataID = isset($_POST['orgDataID']) ? intval($_POST['orgDataID']) : null;
        $entityID = isset($_POST['entityID']) ? intval($_POST['entityID']) : null;

        // Validate due date is not in the past
        $dueDateTime = strtotime($dueDate);
        if ($dueDateTime < time()) {
            throw new Exception('Due date cannot be in the past.');
        }

        // Prepare task data
        $taskData = array(
            'proposalID' => $proposalID,
            'taskName' => $taskName,
            'taskDescription' => $taskDescription,
            'assignedTo' => $assignedTo,
            'assignedBy' => $userID,
            'dueDate' => $dueDate,
            'priority' => $priority,
            'status' => 'pending',
            'completionPercentage' => 0,
            'isMandatory' => $isMandatory,
            'orgDataID' => $orgDataID,
            'entityID' => $entityID,
            'notificationSent' => 'N',
            'DateAdded' => 'NOW()',
            'Suspended' => 'N'
        );

        // Insert task
        $result = $DBConn->insert_data('tija_proposal_tasks', $taskData);

        if ($result) {
            $taskID = $DBConn->lastInsertId();

            if (!$taskID) {
                // Try to get last insert ID if not set
                $taskID = $DBConn->lastInsertId ?? null;
            }

            // Send notification to assigned user
            sendTaskAssignmentNotification($taskID, $assignedTo, $DBConn);

            // Update proposal completion
            try {
                Proposal::update_proposal_completion($proposalID, $DBConn);
            } catch (Exception $e) {
                // Log but don't fail the task creation
                error_log("Failed to update proposal completion: " . $e->getMessage());
            }

            $response['success'] = true;
            $response['message'] = 'Task created and assigned successfully';
            $response['data'] = array('taskID' => $taskID);
        } else {
            $errorMsg = $DBConn->error ?? 'Unknown database error';
            error_log("Database insert failed: " . $errorMsg);
            throw new Exception('Failed to create task. ' . $errorMsg);
        }

    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
        error_log("Proposal Task Create Error [User: {$userID}]: " . $e->getMessage());
    } catch (Error $e) {
        $response['success'] = false;
        $response['message'] = 'A system error occurred: ' . $e->getMessage();
        error_log("Proposal Task Create Fatal Error [User: {$userID}]: " . $e->getMessage());
    }

    // Output JSON response
    outputJsonResponse($response);
}

/**
 * Handle task update
 */
function handleTaskUpdate($userID, $DBConn) {
    global $response;

    try {
        if (!isset($_POST['proposalTaskID']) || empty($_POST['proposalTaskID'])) {
            throw new Exception('Task ID is required for update.');
        }

        $taskID = intval($_POST['proposalTaskID']);
        $task = Proposal::proposal_tasks(array('proposalTaskID' => $taskID), true, $DBConn);

        if (!$task) {
            throw new Exception('Task not found.');
        }

        // Check permissions (user must be assigner, assigned user, or proposal manager)
        $canEdit = ($task->assignedBy == $userID || $task->assignedTo == $userID);
        // Add proposal manager check here if needed

        if (!$canEdit) {
            throw new Exception('You do not have permission to update this task.');
        }

        $changes = array();

        if (isset($_POST['taskName'])) {
            $changes['taskName'] = Utility::clean_string($_POST['taskName']);
        }
        if (isset($_POST['taskDescription'])) {
            $changes['taskDescription'] = Utility::clean_string($_POST['taskDescription']);
        }
        if (isset($_POST['dueDate'])) {
            $dueDate = Utility::clean_string($_POST['dueDate']);
            $dueDateTime = strtotime($dueDate);
            if ($dueDateTime < time() && $task->status !== 'completed') {
                throw new Exception('Due date cannot be in the past for incomplete tasks.');
            }
            $changes['dueDate'] = $dueDate;
        }
        if (isset($_POST['priority'])) {
            $changes['priority'] = Utility::clean_string($_POST['priority']);
        }
        if (isset($_POST['status'])) {
            $changes['status'] = Utility::clean_string($_POST['status']);
            if ($_POST['status'] === 'completed') {
                $changes['completedDate'] = 'NOW()';
                $changes['completedBy'] = $userID;
                $changes['completionPercentage'] = 100;
            }
        }
        if (isset($_POST['isMandatory'])) {
            $changes['isMandatory'] = $_POST['isMandatory'] === 'Y' ? 'Y' : 'N';
        }

        $changes['LastUpdatedByID'] = $userID;

        if (empty($changes)) {
            throw new Exception('No changes to update.');
        }

        $result = $DBConn->update_table('tija_proposal_tasks', $changes, array('proposalTaskID' => $taskID));

        if ($result) {
            // Update proposal completion if status changed
            if (isset($changes['status'])) {
                Proposal::update_proposal_completion($task->proposalID, $DBConn);
            }

            $response['success'] = true;
            $response['message'] = 'Task updated successfully';
        } else {
            throw new Exception('Failed to update task. Please try again.');
        }

    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
        error_log("Proposal Task Update Error [User: {$userID}]: " . $e->getMessage());
    } catch (Error $e) {
        $response['success'] = false;
        $response['message'] = 'A system error occurred: ' . $e->getMessage();
        error_log("Proposal Task Update Fatal Error [User: {$userID}]: " . $e->getMessage());
    }

    // Output JSON response
    outputJsonResponse($response);
}

/**
 * Handle task delete
 */
function handleTaskDelete($userID, $DBConn) {
    global $response;

    try {
        if (!isset($_POST['proposalTaskID']) || empty($_POST['proposalTaskID'])) {
            throw new Exception('Task ID is required for deletion.');
        }

        $taskID = intval($_POST['proposalTaskID']);
        $task = Proposal::proposal_tasks(array('proposalTaskID' => $taskID), true, $DBConn);

        if (!$task) {
            throw new Exception('Task not found.');
        }

        // Check permissions
        $canDelete = ($task->assignedBy == $userID);
        // Add proposal manager check here if needed

        if (!$canDelete) {
            throw new Exception('You do not have permission to delete this task.');
        }

        // Soft delete
        $result = $DBConn->update_table('tija_proposal_tasks',
            array('Suspended' => 'Y', 'LastUpdatedByID' => $userID),
            array('proposalTaskID' => $taskID)
        );

        if ($result) {
            // Update proposal completion
            Proposal::update_proposal_completion($task->proposalID, $DBConn);

            $response['success'] = true;
            $response['message'] = 'Task deleted successfully';
        } else {
            throw new Exception('Failed to delete task. Please try again.');
        }

    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
        error_log("Proposal Task Delete Error [User: {$userID}]: " . $e->getMessage());
    } catch (Error $e) {
        $response['success'] = false;
        $response['message'] = 'A system error occurred: ' . $e->getMessage();
        error_log("Proposal Task Delete Fatal Error [User: {$userID}]: " . $e->getMessage());
    }

    // Output JSON response
    outputJsonResponse($response);
}

/**
 * Handle task assignment
 */
function handleTaskAssign($userID, $DBConn) {
    global $response;

    try {
        if (!isset($_POST['proposalTaskID']) || empty($_POST['proposalTaskID'])) {
            throw new Exception('Task ID is required.');
        }

        if (!isset($_POST['assignedTo']) || empty($_POST['assignedTo'])) {
            throw new Exception('User ID is required for assignment.');
        }

        $taskID = intval($_POST['proposalTaskID']);
        $assignedTo = intval($_POST['assignedTo']);

        $task = Proposal::proposal_tasks(array('proposalTaskID' => $taskID), true, $DBConn);

        if (!$task) {
            throw new Exception('Task not found.');
        }

        // Check permissions
        $canAssign = ($task->assignedBy == $userID);
        // Add proposal manager check here if needed

        if (!$canAssign) {
            throw new Exception('You do not have permission to reassign this task.');
        }

        $changes = array(
            'assignedTo' => $assignedTo,
            'LastUpdatedByID' => $userID
        );

        $result = $DBConn->update_table('tija_proposal_tasks', $changes, array('proposalTaskID' => $taskID));

        if ($result) {
            // Send notification to new assignee
            sendTaskAssignmentNotification($taskID, $assignedTo, $DBConn);

            $response['success'] = true;
            $response['message'] = 'Task reassigned successfully';
        } else {
            throw new Exception('Failed to reassign task. Please try again.');
        }

    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
        error_log("Proposal Task Assign Error [User: {$userID}]: " . $e->getMessage());
    } catch (Error $e) {
        $response['success'] = false;
        $response['message'] = 'A system error occurred: ' . $e->getMessage();
        error_log("Proposal Task Assign Fatal Error [User: {$userID}]: " . $e->getMessage());
    }

    // Output JSON response
    outputJsonResponse($response);
}

/**
 * Handle task completion
 */
function handleTaskComplete($userID, $DBConn) {
    global $response;

    try {
        if (!isset($_POST['proposalTaskID']) || empty($_POST['proposalTaskID'])) {
            throw new Exception('Task ID is required.');
        }

        $taskID = intval($_POST['proposalTaskID']);
        $completionPercentage = isset($_POST['completionPercentage']) ? floatval($_POST['completionPercentage']) : 100;

        if ($completionPercentage < 0 || $completionPercentage > 100) {
            throw new Exception('Completion percentage must be between 0 and 100.');
        }

        $task = Proposal::proposal_tasks(array('proposalTaskID' => $taskID), true, $DBConn);

        if (!$task) {
            throw new Exception('Task not found.');
        }

        // Check permissions (assigned user or manager)
        $canComplete = ($task->assignedTo == $userID || $task->assignedBy == $userID);

        if (!$canComplete) {
            throw new Exception('You do not have permission to complete this task.');
        }

        $changes = array(
            'completionPercentage' => $completionPercentage,
            'LastUpdatedByID' => $userID
        );

        if ($completionPercentage >= 100) {
            $changes['status'] = 'completed';
            $changes['completedDate'] = 'NOW()';
            $changes['completedBy'] = $userID;
        } elseif ($task->status === 'pending') {
            $changes['status'] = 'in_progress';
        }

        $result = $DBConn->update_table('tija_proposal_tasks', $changes, array('proposalTaskID' => $taskID));

        if ($result) {
            // Update proposal completion
            Proposal::update_proposal_completion($task->proposalID, $DBConn);

            $response['success'] = true;
            $response['message'] = 'Task completion updated successfully';
        } else {
            throw new Exception('Failed to update task completion. Please try again.');
        }

    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
        error_log("Proposal Task Complete Error [User: {$userID}]: " . $e->getMessage());
    } catch (Error $e) {
        $response['success'] = false;
        $response['message'] = 'A system error occurred: ' . $e->getMessage();
        error_log("Proposal Task Complete Fatal Error [User: {$userID}]: " . $e->getMessage());
    }

    // Output JSON response
    outputJsonResponse($response);
}

/**
 * Handle get task
 */
function handleGetTask($userID, $DBConn) {
    global $response;

    try {
        if (!isset($_POST['proposalTaskID']) || empty($_POST['proposalTaskID'])) {
            throw new Exception('Task ID is required.');
        }

        $taskID = intval($_POST['proposalTaskID']);
        $task = Proposal::proposal_tasks(array('proposalTaskID' => $taskID), true, $DBConn);

        if (!$task) {
            throw new Exception('Task not found.');
        }

        $response['success'] = true;
        $response['data'] = $task;

    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = $e->getMessage();
        error_log("Proposal Task Get Error [User: {$userID}]: " . $e->getMessage());
    } catch (Error $e) {
        $response['success'] = false;
        $response['message'] = 'A system error occurred: ' . $e->getMessage();
        error_log("Proposal Task Get Fatal Error [User: {$userID}]: " . $e->getMessage());
    }

    // Output JSON response
    outputJsonResponse($response);
}

/**
 * Send task assignment notification
 */
function sendTaskAssignmentNotification($taskID, $assignedTo, $DBConn) {
    // This would integrate with your notification system
    // For now, we'll just update the notification flag
    try {
        $DBConn->update_table('tija_proposal_tasks',
            array('notificationSent' => 'Y', 'notificationSentDate' => 'NOW()'),
            array('proposalTaskID' => $taskID)
        );

        // TODO: Integrate with actual notification system (email, in-app, etc.)
        error_log("Task assignment notification should be sent to user ID: {$assignedTo} for task ID: {$taskID}");
    } catch (Exception $e) {
        error_log("Failed to send task assignment notification: " . $e->getMessage());
    }
}

