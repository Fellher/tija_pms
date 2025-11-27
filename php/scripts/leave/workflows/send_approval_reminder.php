<?php
/**
 * Send Approval Reminder
 *
 * Allows final approvers (e.g., HR managers) to nudge pending approvers.
 */

session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';

header('Content-Type: application/json');

if (!$isValidUser) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $leaveId = isset($input['leaveId']) ? Utility::clean_string($input['leaveId']) : '';
    $approverId = isset($input['approverId']) ? Utility::clean_string($input['approverId']) : '';

    if (empty($leaveId)) {
        echo json_encode(['success' => false, 'message' => 'Leave ID is required']);
        exit;
    }

    $targetApproverUserID = $approverId !== '' ? (int)$approverId : null;
    $result = LeaveNotifications::sendApprovalReminder($leaveId, $userDetails->ID, $DBConn, $targetApproverUserID);

    echo json_encode($result);

} catch (Exception $e) {
    error_log('Send approval reminder error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while sending reminders']);
}

