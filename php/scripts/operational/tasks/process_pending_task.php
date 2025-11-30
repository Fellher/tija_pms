<?php
/**
 * Process Pending Task from Notification (Manual Processing)
 *
 * Allows users to manually activate scheduled tasks from notifications
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

require_once __DIR__ . '/../../../includes.php';
require_once __DIR__ . '/../../../classes/operationaltaskscheduler.php';

header('Content-Type: application/json');

global $DBConn, $isValidUser, $userID;

// Check authentication
if (!$isValidUser) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    $notificationID = $_POST['notificationID'] ?? null;
    $action = $_POST['action'] ?? 'process';

    if (!$notificationID) {
        throw new Exception('Notification ID is required');
    }

    // Verify notification belongs to current user
    $notification = $DBConn->retrieve_db_table_rows(
        'tija_operational_task_notifications',
        ['notificationID', 'employeeID', 'status'],
        ['notificationID' => $notificationID],
        true
    );

    if (!$notification) {
        throw new Exception('Notification not found');
    }

    if ($notification['employeeID'] != $userID) {
        throw new Exception('Unauthorized - notification does not belong to you');
    }

    if ($action === 'process') {
        // Process the task
        $taskID = OperationalTaskScheduler::processPendingTaskFromNotification($notificationID, $DBConn);

        if ($taskID) {
            echo json_encode([
                'success' => true,
                'message' => 'Task created successfully',
                'taskID' => $taskID
            ]);
        } else {
            throw new Exception('Failed to create task instance');
        }
    } elseif ($action === 'dismiss') {
        // Dismiss notification
        $updateData = [
            'status' => 'dismissed'
        ];

        $success = $DBConn->update_db_table_row(
            'tija_operational_task_notifications',
            $updateData,
            ['notificationID' => $notificationID]
        );

        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Notification dismissed'
            ]);
        } else {
            throw new Exception('Failed to dismiss notification');
        }
    } else {
        throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

