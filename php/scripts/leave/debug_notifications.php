<?php
/**
 * Debug Leave Notifications
 *
 * This script checks if all required tables exist and provides detailed
 * debugging information for why notifications might not be getting saved.
 */

session_start();
$base = '../../../../';
set_include_path($base);

// Try multiple paths for includes.php
// Script is at: php/scripts/leave/debug_notifications.php
// includes.php should be at: php/includes.php
$includePaths = array(
    dirname(dirname(__DIR__)) . '/includes.php',  // php/includes.php
    __DIR__ . '/../../../includes.php',            // php/includes.php (alternative)
    $base . 'php/includes.php',                    // Relative from base
    'php/includes.php'                             // From include_path
);

$includesLoaded = false;
$debugPaths = array();
foreach ($includePaths as $includePath) {
    $exists = file_exists($includePath);
    $debugPaths[] = array('path' => $includePath, 'exists' => $exists, 'realpath' => $exists ? realpath($includePath) : null);
    if ($exists) {
        include $includePath;
        $includesLoaded = true;
        break;
    }
}

if (!$includesLoaded) {
    // If includes.php not found, try to load minimal requirements
    $configPaths = array(
        dirname(dirname(__DIR__)) . '/config/db.config.inc.php',  // php/config/db.config.inc.php
        __DIR__ . '/../../../config/db.config.inc.php',
        $base . 'php/config/db.config.inc.php'
    );

    foreach ($configPaths as $configPath) {
        if (file_exists($configPath)) {
            include $configPath;
            break;
        }
    }

    // Try to initialize DB connection if class exists
    if (class_exists('mysqlConnect') && isset($dbConfig)) {
        $DBConn = new mysqlConnect($dbConfig['user'], $dbConfig['password'], $dbConfig['host'], $dbConfig['database']);
        $DBConn->connect();
    } elseif (class_exists('mysqlConnect') && file_exists(dirname(dirname(__DIR__)) . '/config/db.config.inc.php')) {
        // Try to load config and connect
        include dirname(dirname(__DIR__)) . '/config/db.config.inc.php';
        if (isset($dbConfig)) {
            $DBConn = new mysqlConnect($dbConfig['user'], $dbConfig['password'], $dbConfig['host'], $dbConfig['database']);
            $DBConn->connect();
        }
    }
}

header('Content-Type: application/json');

// Check if user is admin (with fallback for direct access)
$isAuthorized = false;
if (isset($isAdmin) && $isAdmin) {
    $isAuthorized = true;
} elseif (isset($isValidAdmin) && $isValidAdmin) {
    $isAuthorized = true;
} elseif (isset($isHRManager) && $isHRManager) {
    $isAuthorized = true;
} elseif (!isset($isAdmin) && !isset($isValidAdmin) && !isset($isHRManager)) {
    // If variables don't exist, allow access for debugging (but log it)
    $isAuthorized = true;
    error_log("debug_notifications.php - Warning: Authorization variables not set, allowing access for debugging");
}

if (!$isAuthorized) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if DB connection is available
if (!isset($DBConn) || !$DBConn) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection not available',
        'error' => 'Could not establish database connection. Please check your configuration.',
        'debug_info' => array(
            'script_dir' => __DIR__,
            'script_path' => __FILE__,
            'include_paths_tried' => $debugPaths,
            'includes_loaded' => $includesLoaded,
            'current_dir' => getcwd(),
            'include_path' => get_include_path()
        )
    ], JSON_PRETTY_PRINT);
    exit;
}

$debugInfo = array(
    'timestamp' => date('Y-m-d H:i:s'),
    'tables' => array(),
    'events' => array(),
    'channels' => array(),
    'templates' => array(),
    'test_notification' => null,
    'errors' => array()
);

// Required tables
$requiredTables = array(
    'tija_notifications_enhanced',
    'tija_notification_events',
    'tija_notification_channels',
    'tija_notification_templates',
    'tija_notification_modules',
    'tija_notification_logs',
    'tija_notification_preferences',
    'tija_notification_queue'
);

// Check each table
foreach ($requiredTables as $tableName) {
    try {
        $tableCheck = $DBConn->fetch_all_rows("SHOW TABLES LIKE '{$tableName}'", array());
        $exists = ($tableCheck && count($tableCheck) > 0);

        $debugInfo['tables'][$tableName] = array(
            'exists' => $exists,
            'columns' => array()
        );

        if ($exists) {
            // Get column information
            $columns = $DBConn->fetch_all_rows("SHOW COLUMNS FROM `{$tableName}`", array());
            if ($columns) {
                foreach ($columns as $col) {
                    $col = is_object($col) ? (array)$col : $col;
                    $debugInfo['tables'][$tableName]['columns'][] = $col['Field'] ?? $col['field'] ?? '';
                }
            }

            // Get row count
            $countResult = $DBConn->fetch_all_rows("SELECT COUNT(*) as cnt FROM `{$tableName}`", array());
            $count = $countResult && count($countResult) > 0
                ? (is_object($countResult[0]) ? $countResult[0]->cnt : $countResult[0]['cnt'])
                : 0;
            $debugInfo['tables'][$tableName]['row_count'] = $count;
        } else {
            $debugInfo['errors'][] = "Table '{$tableName}' does not exist!";
        }
    } catch (Exception $e) {
        $debugInfo['tables'][$tableName] = array(
            'exists' => false,
            'error' => $e->getMessage()
        );
        $debugInfo['errors'][] = "Error checking table '{$tableName}': " . $e->getMessage();
    }
}

// Check leave notification events
try {
    $leaveEvents = array(
        'leave_pending_approval',
        'leave_application_submitted',
        'leave_approved',
        'leave_rejected',
        'leave_cancelled'
    );

    foreach ($leaveEvents as $eventSlug) {
        $event = $DBConn->fetch_all_rows(
            "SELECT * FROM tija_notification_events WHERE eventSlug = ?",
            array(array($eventSlug, 's'))
        );

        if ($event && count($event) > 0) {
            $eventData = is_object($event[0]) ? (array)$event[0] : $event[0];
            $debugInfo['events'][$eventSlug] = array(
                'exists' => true,
                'eventID' => $eventData['eventID'] ?? null,
                'isActive' => $eventData['isActive'] ?? 'N',
                'Suspended' => $eventData['Suspended'] ?? 'N',
                'priorityLevel' => $eventData['priorityLevel'] ?? 'medium'
            );

            if ($eventData['isActive'] !== 'Y' || $eventData['Suspended'] === 'Y') {
                $debugInfo['errors'][] = "Event '{$eventSlug}' is not active or is suspended!";
            }
        } else {
            $debugInfo['events'][$eventSlug] = array('exists' => false);
            $debugInfo['errors'][] = "Event '{$eventSlug}' does not exist in tija_notification_events table!";
        }
    }
} catch (Exception $e) {
    $debugInfo['errors'][] = "Error checking events: " . $e->getMessage();
}

// Check in_app channel
try {
    $channel = $DBConn->fetch_all_rows(
        "SELECT * FROM tija_notification_channels WHERE channelSlug = 'in_app'",
        array()
    );

    if ($channel && count($channel) > 0) {
        $channelData = is_object($channel[0]) ? (array)$channel[0] : $channel[0];
        $debugInfo['channels']['in_app'] = array(
            'exists' => true,
            'channelID' => $channelData['channelID'] ?? null,
            'isActive' => $channelData['isActive'] ?? 'N',
            'Suspended' => $channelData['Suspended'] ?? 'N'
        );
    } else {
        $debugInfo['channels']['in_app'] = array('exists' => false);
        $debugInfo['errors'][] = "In-app channel does not exist!";
    }
} catch (Exception $e) {
    $debugInfo['errors'][] = "Error checking channels: " . $e->getMessage();
}

// Check templates for leave_pending_approval event
try {
    $eventID = null;
    if (isset($debugInfo['events']['leave_pending_approval']['eventID'])) {
        $eventID = $debugInfo['events']['leave_pending_approval']['eventID'];
    }

    if ($eventID) {
        $channelID = isset($debugInfo['channels']['in_app']['channelID'])
            ? $debugInfo['channels']['in_app']['channelID']
            : null;

        if ($channelID) {
            $template = $DBConn->fetch_all_rows(
                "SELECT * FROM tija_notification_templates WHERE eventID = ? AND channelID = ?",
                array(
                    array($eventID, 'i'),
                    array($channelID, 'i')
                )
            );

            if ($template && count($template) > 0) {
                $templateData = is_object($template[0]) ? (array)$template[0] : $template[0];
                $debugInfo['templates']['leave_pending_approval'] = array(
                    'exists' => true,
                    'templateID' => $templateData['templateID'] ?? null,
                    'isActive' => $templateData['isActive'] ?? 'N',
                    'isDefault' => $templateData['isDefault'] ?? 'N'
                );
            } else {
                $debugInfo['templates']['leave_pending_approval'] = array('exists' => false);
                $debugInfo['errors'][] = "Template for 'leave_pending_approval' event does not exist!";
            }
        }
    }
} catch (Exception $e) {
    $debugInfo['errors'][] = "Error checking templates: " . $e->getMessage();
}

// Test notification creation
if (isset($_GET['test']) && $_GET['test'] === '1') {
    try {
        $testUserId = isset($userDetails->ID) ? $userDetails->ID : 1;

        if (class_exists('Notification')) {
            $testResult = Notification::create(array(
                'eventSlug' => 'leave_pending_approval',
                'userId' => $testUserId,
                'originatorId' => $testUserId,
                'data' => array(
                    'employee_name' => 'Test Employee',
                    'employee_id' => $testUserId,
                    'leave_type' => 'Test Leave',
                    'start_date' => date('M j, Y'),
                    'end_date' => date('M j, Y', strtotime('+1 day')),
                    'total_days' => 1,
                    'application_id' => 999999,
                    'approval_level' => 1
                ),
                'link' => '?s=user&ss=leave&p=pending_approvals&id=999999',
                'entityID' => isset($userDetails->entityID) ? $userDetails->entityID : 1,
                'orgDataID' => isset($userDetails->orgDataID) ? $userDetails->orgDataID : 1,
                'segmentType' => 'leave_application',
                'segmentID' => 999999,
                'priority' => 'high'
            ), $DBConn);

            $debugInfo['test_notification'] = $testResult;

            if (isset($testResult['success']) && $testResult['success']) {
                // Clean up test notification
                if (isset($testResult['notifications']) && is_array($testResult['notifications'])) {
                    foreach ($testResult['notifications'] as $notif) {
                        if (isset($notif['notificationID'])) {
                            $DBConn->query("DELETE FROM tija_notifications_enhanced WHERE notificationID = ?", array());
                            $DBConn->bind(1, $notif['notificationID']);
                            $DBConn->execute();
                        }
                    }
                }
            }
        } else {
            $debugInfo['errors'][] = "Notification class does not exist!";
        }
    } catch (Exception $e) {
        $debugInfo['test_notification'] = array(
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        );
        $debugInfo['errors'][] = "Test notification failed: " . $e->getMessage();
    }
}

// Check recent notifications
try {
    $recentNotifications = $DBConn->fetch_all_rows(
        "SELECT notificationID, DateAdded, userID, notificationTitle, status, segmentType, segmentID
         FROM tija_notifications_enhanced
         WHERE segmentType = 'leave_application'
         ORDER BY DateAdded DESC
         LIMIT 10",
        array()
    );

    $debugInfo['recent_notifications'] = array();
    if ($recentNotifications) {
        foreach ($recentNotifications as $notif) {
            $notif = is_object($notif) ? (array)$notif : $notif;
            $debugInfo['recent_notifications'][] = array(
                'notificationID' => $notif['notificationID'] ?? null,
                'DateAdded' => $notif['DateAdded'] ?? null,
                'userID' => $notif['userID'] ?? null,
                'notificationTitle' => $notif['notificationTitle'] ?? null,
                'status' => $notif['status'] ?? null,
                'segmentType' => $notif['segmentType'] ?? null,
                'segmentID' => $notif['segmentID'] ?? null
            );
        }
    }
} catch (Exception $e) {
    $debugInfo['errors'][] = "Error checking recent notifications: " . $e->getMessage();
}

$debugInfo['summary'] = array(
    'all_tables_exist' => count(array_filter($debugInfo['tables'], function($t) { return !($t['exists'] ?? false); })) === 0,
    'all_events_exist' => count(array_filter($debugInfo['events'], function($e) { return !($e['exists'] ?? false); })) === 0,
    'channel_exists' => $debugInfo['channels']['in_app']['exists'] ?? false,
    'template_exists' => $debugInfo['templates']['leave_pending_approval']['exists'] ?? false,
    'error_count' => count($debugInfo['errors'])
);

echo json_encode($debugInfo, JSON_PRETTY_PRINT);
?>

