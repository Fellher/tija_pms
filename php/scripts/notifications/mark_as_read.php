<?php
/**
 * Mark Notification as Read
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

    // Get notification ID
    $notificationID = isset($_POST['notificationID']) ? (int)$_POST['notificationID'] : 0;

    if ($notificationID === 0) {
        echo json_encode(array('success' => false, 'message' => 'Invalid notification ID'));
        exit;
    }

    // Mark as read
    $result = Notification::markAsRead($notificationID, $userID, $DBConn);

    if ($result) {
        // Get updated unread count
        $unreadCount = Notification::getUnreadCount($userID, $DBConn);

        echo json_encode(array(
            'success' => true,
            'message' => 'Notification marked as read',
            'unreadCount' => $unreadCount
        ));
    } else {
        echo json_encode(array(
            'success' => false,
            'message' => 'Failed to mark notification as read'
        ));
    }

} catch (Exception $e) {
    echo json_encode(array(
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ));
}
