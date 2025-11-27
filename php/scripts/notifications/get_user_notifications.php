<?php
/**
 * Get User Notifications via AJAX
 * Returns notifications for the current user
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

    // Get filters from request
    $filters = array();

    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $filters['status'] = $_GET['status'];
    }

    if (isset($_GET['priority']) && !empty($_GET['priority'])) {
        $filters['priority'] = $_GET['priority'];
    }

    if (isset($_GET['unreadOnly']) && $_GET['unreadOnly'] === 'true') {
        $filters['unreadOnly'] = true;
    }

    if (isset($_GET['limit'])) {
        $filters['limit'] = (int)$_GET['limit'];
    } else {
        $filters['limit'] = 20;
    }

    if (isset($_GET['offset'])) {
        $filters['offset'] = (int)$_GET['offset'];
    }

    // Get notifications
    $notifications = Notification::getUserNotifications($userID, $filters, $DBConn);

    // Get unread count
    $unreadCount = Notification::getUnreadCount($userID, $DBConn);

    // Format notifications for frontend
    $formattedNotifications = array();
    if ($notifications) {
        foreach ($notifications as $notif) {
            // Handle both object and array results
            $notifArray = is_object($notif) ? (array)$notif : $notif;
            $defaultLink = '?s=user&ss=notifications&p=user_notifications&notificationID=' . $notifArray['notificationID'];

            $formattedNotifications[] = array(
                'notificationID' => $notifArray['notificationID'],
                'title' => $notifArray['notificationTitle'],
                'body' => $notifArray['notificationBody'],
                'link' => $notifArray['notificationLink'] ?? $defaultLink,
                'icon' => $notifArray['notificationIcon'] ?? 'ri-notification-line',
                'priority' => $notifArray['priority'],
                'status' => $notifArray['status'],
                'dateAdded' => $notifArray['DateAdded'],
                'readAt' => $notifArray['readAt'] ?? null,
                'eventName' => $notifArray['eventName'] ?? 'Notification',
                'moduleName' => $notifArray['moduleName'] ?? '',
                'originatorName' => $notifArray['originatorName'] ?? 'System',
                'timeAgo' => timeAgo($notifArray['DateAdded'])
            );
        }
    }

    echo json_encode(array(
        'success' => true,
        'notifications' => $formattedNotifications,
        'unreadCount' => $unreadCount,
        'total' => count($formattedNotifications)
    ));

} catch (Exception $e) {
    echo json_encode(array(
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ));
}

/**
 * Convert timestamp to "time ago" format
 */
function timeAgo($timestamp) {
    $time = strtotime($timestamp);
    $diff = time() - $time;

    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}

