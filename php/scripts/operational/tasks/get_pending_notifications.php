<?php
/**
 * Get Pending Task Notifications
 *
 * Retrieves pending task notifications for the logged-in user
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

// Define base path before including config
$base = '../../../../';

session_start();
$base = '../../../';
set_include_path($base);
require_once 'php/includes.php';

header('Content-Type: application/json');

global $DBConn, $isValidUser, $userID;

// Check authentication
if (!$isValidUser) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $notifications = OperationalTaskScheduler::getPendingTaskNotifications($userID, $DBConn);

    echo json_encode([
        'success' => true,
        'notifications' => $notifications ?: [],
        'count' => is_array($notifications) ? count($notifications) : 0
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

