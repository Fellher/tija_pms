<?php
/**
 * Operational Task Management API
 *
 * Handles create, update, delete, and status updates for operational tasks
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

require_once __DIR__ . '/../../../includes.php';
require_once __DIR__ . '/../../../classes/operationaltask.php';
require_once __DIR__ . '/../../../classes/operationaltasktemplate.php';

header('Content-Type: application/json');

global $DBConn, $isValidUser;

// Check authentication
if (!$isValidUser) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'POST':
            if ($action === 'create') {
                // Create task instance from template
                $templateID = $_POST['templateID'] ?? null;
                $dueDate = $_POST['dueDate'] ?? date('Y-m-d');

                if (!$templateID) {
                    throw new Exception('Template ID is required');
                }

                $taskID = OperationalTask::instantiateFromTemplate($templateID, $dueDate, $DBConn);

                if ($taskID) {
                    echo json_encode(['success' => true, 'taskID' => $taskID]);
                } else {
                    throw new Exception('Failed to create task instance');
                }
            } elseif ($action === 'update_status') {
                // Update task status
                $taskID = $_POST['taskID'] ?? null;
                $status = $_POST['status'] ?? null;

                if (!$taskID || !$status) {
                    throw new Exception('Task ID and status are required');
                }

                $success = OperationalTask::updateStatus($taskID, $status, $DBConn);

                if ($success) {
                    echo json_encode(['success' => true]);
                } else {
                    throw new Exception('Failed to update task status');
                }
            } elseif ($action === 'complete') {
                // Complete task
                $taskID = $_POST['taskID'] ?? null;
                $actualDuration = $_POST['actualDuration'] ?? 0;
                $checklistData = json_decode($_POST['checklistData'] ?? '[]', true);

                if (!$taskID) {
                    throw new Exception('Task ID is required');
                }

                $success = OperationalTask::completeTask($taskID, $actualDuration, $checklistData, $DBConn);

                if ($success) {
                    echo json_encode(['success' => true]);
                } else {
                    throw new Exception('Failed to complete task. Check mandatory checklist items.');
                }
            } else {
                throw new Exception('Invalid action');
            }
            break;

        case 'GET':
            if ($action === 'get') {
                // Get task instance
                $taskID = $_GET['taskID'] ?? null;

                if (!$taskID) {
                    throw new Exception('Task ID is required');
                }

                $task = OperationalTask::getInstance($taskID, $DBConn);

                if ($task) {
                    echo json_encode(['success' => true, 'task' => $task]);
                } else {
                    throw new Exception('Task not found');
                }
            } else {
                throw new Exception('Invalid action');
            }
            break;

        default:
            throw new Exception('Method not allowed');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

