<?php
/**
 * Mark All Notifications as Read
 */

session_start();
$base = '../../../';
set_include_path($base);
include 'php/includes.php';

header('Content-Type: application/json');

if (!$isValidUser) {
    echo json_encode(array('success' => false, 'message' => 'Unauthorized'));
    exit;
}

try {
    $userID = $userDetails->ID;

    // Mark all as read
    $result = Notification::markAllAsRead($userID, $DBConn);

    if ($result) {
        echo json_encode(array(
            'success' => true,
            'message' => 'All notifications marked as read',
            'unreadCount' => 0
        ));
    } else {
        echo json_encode(array(
            'success' => false,
            'message' => 'No unread notifications to mark'
        ));
    }

} catch (Exception $e) {
    echo json_encode(array(
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ));
}

