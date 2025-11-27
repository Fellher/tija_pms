<?php
/**
 * Reset Leave Applications & Notifications
 *
 * Removes all leave applications, related workflow/audit data, and all leave
 * notifications so the organisation can restart with a clean slate.
 *
 * SECURITY: Requires tenant/super admin (or HR manager) plus an explicit
 * confirm token in the POST body to prevent accidental execution.
 */

session_start();
$base = '../../../../';
set_include_path($base);
include 'php/includes.php';

header('Content-Type: application/json');

if (!$isValidAdmin && !$isAdmin && !$isSuperAdmin && !$isHRManager) {
    echo json_encode(array(
        'success' => false,
        'message' => 'Unauthorized. Admin or HR manager access required.'
    ));
    exit;
}

$confirmToken = isset($_POST['confirmReset']) ? trim($_POST['confirmReset']) : '';
$dryRun = isset($_POST['dryRun']) && $_POST['dryRun'] === '1';

if ($confirmToken !== 'RESET_LEAVE_DATA') {
    echo json_encode(array(
        'success' => false,
        'message' => 'Invalid confirmation token. Send confirmReset=RESET_LEAVE_DATA to proceed.'
    ));
    exit;
}

/**
 * Utility helpers
 */
function sanitize_identifier($name) {
    return preg_replace('/[^a-zA-Z0-9_]/', '', (string)$name);
}

function table_exists($tableName, $DBConn) {
    $tableName = sanitize_identifier($tableName);
    if (!$tableName) {
        return false;
    }
    $rows = $DBConn->fetch_all_rows("SHOW TABLES LIKE '{$tableName}'", array());
    return $rows && count($rows) > 0;
}

function truncate_table($tableName, $DBConn, &$log, $dryRun = false) {
    $tableName = sanitize_identifier($tableName);
    if (!$tableName) {
        $log[] = "⚠️ Invalid table name encountered.";
        return;
    }

    if (!table_exists($tableName, $DBConn)) {
        $log[] = "ℹ️ Table {$tableName} does not exist. Skipped.";
        return;
    }

    if ($dryRun) {
        $log[] = "🛈 [Dry Run] Would truncate {$tableName}";
        return;
    }

    try {
        $DBConn->query("TRUNCATE TABLE `{$tableName}`");
        $DBConn->execute();
        $log[] = "✅ Truncated {$tableName}";
    } catch (Exception $e) {
        $log[] = "❌ Failed to truncate {$tableName}: " . $e->getMessage();
    }
}

$messages = array();
$notificationMessages = array();

// Leave-related tables (truncate in child -> parent order)
$leaveTables = array(
    'tija_leave_documents',
    'tija_leave_approval_actions',
    'tija_leave_approval_comments',
    'tija_leave_approvals',
    'tija_leave_approval_instances',
    'tija_leave_audit_log',
    'tija_leave_applications'
);

try {
    if (!$dryRun) {
        $DBConn->query('SET FOREIGN_KEY_CHECKS=0');
        $DBConn->execute();
    }

    foreach ($leaveTables as $table) {
        truncate_table($table, $DBConn, $messages, $dryRun);
    }

    /**
     * Notification cleanup
     */
    $leaveEventIDs = array();
    $leaveNotificationIDs = array();

    $moduleRows = $DBConn->fetch_all_rows(
        "SELECT moduleID FROM tija_notification_modules WHERE moduleSlug = 'leave' LIMIT 1",
        array()
    );
    $leaveModuleID = ($moduleRows && count($moduleRows) > 0)
        ? (int)(is_object($moduleRows[0]) ? $moduleRows[0]->moduleID : $moduleRows[0]['moduleID'])
        : null;

    if ($leaveModuleID) {
        $eventRows = $DBConn->fetch_all_rows(
            "SELECT eventID FROM tija_notification_events WHERE moduleID = ?",
            array(array($leaveModuleID, 'i'))
        );
        if ($eventRows) {
            foreach ($eventRows as $row) {
                $leaveEventIDs[] = (int)(is_object($row) ? $row->eventID : $row['eventID']);
            }
        }
    } else {
        // Fallback: any event slug that starts with leave_
        $eventRows = $DBConn->fetch_all_rows(
            "SELECT eventID FROM tija_notification_events WHERE eventSlug LIKE 'leave_%'",
            array()
        );
        if ($eventRows) {
            foreach ($eventRows as $row) {
                $leaveEventIDs[] = (int)(is_object($row) ? $row->eventID : $row['eventID']);
            }
        }
    }

    $leaveEventIDs = array_values(array_unique(array_filter($leaveEventIDs)));

    if (!empty($leaveEventIDs) && table_exists('tija_notifications_enhanced', $DBConn)) {
        $eventIdListSql = implode(',', $leaveEventIDs);
        $notificationRows = $DBConn->fetch_all_rows(
            "SELECT notificationID FROM tija_notifications_enhanced
             WHERE eventID IN ({$eventIdListSql}) OR segmentType = 'leave_application'",
            array()
        );
        if ($notificationRows) {
            foreach ($notificationRows as $row) {
                $leaveNotificationIDs[] = (int)(is_object($row) ? $row->notificationID : $row['notificationID']);
            }
        }
    } elseif (table_exists('tija_notifications_enhanced', $DBConn)) {
        // No event IDs (module missing), but still wipe leave_application notifications
        $notificationRows = $DBConn->fetch_all_rows(
            "SELECT notificationID FROM tija_notifications_enhanced
             WHERE segmentType = 'leave_application'",
            array()
        );
        if ($notificationRows) {
            foreach ($notificationRows as $row) {
                $leaveNotificationIDs[] = (int)(is_object($row) ? $row->notificationID : $row['notificationID']);
            }
        }
    }

    $leaveNotificationIDs = array_values(array_unique(array_filter($leaveNotificationIDs)));

    $deleteNotificationData = function($table, $whereClause, $dryRun, &$log) use ($DBConn) {
        if (!table_exists($table, $DBConn)) {
            $log[] = "ℹ️ Notification table {$table} missing. Skipped.";
            return;
        }
        if ($dryRun) {
            $log[] = "🛈 [Dry Run] Would delete from {$table} where {$whereClause}";
            return;
        }
        $DBConn->query("DELETE FROM `{$table}` WHERE {$whereClause}");
        $DBConn->execute();
        $log[] = "✅ Deleted records from {$table}";
    };

    if (!empty($leaveNotificationIDs)) {
        $idListSql = implode(',', $leaveNotificationIDs);
        $deleteNotificationData('tija_notification_logs', "notificationID IN ({$idListSql})", $dryRun, $notificationMessages);
        $deleteNotificationData('tija_notification_queue', "notificationID IN ({$idListSql})", $dryRun, $notificationMessages);
        $deleteNotificationData('tija_notifications_enhanced', "notificationID IN ({$idListSql})", $dryRun, $notificationMessages);
    } else {
        $notificationMessages[] = 'ℹ️ No leave notifications found for deletion.';
    }

    if (!empty($leaveEventIDs)) {
        $eventIdListSql = implode(',', $leaveEventIDs);
        $deleteNotificationData('tija_notification_logs', "eventID IN ({$eventIdListSql})", $dryRun, $notificationMessages);
    }

    if (!$dryRun) {
        $DBConn->query('SET FOREIGN_KEY_CHECKS=1');
        $DBConn->execute();
    }

    echo json_encode(array(
        'success' => true,
        'dryRun' => $dryRun,
        'messages' => $messages,
        'notificationMessages' => $notificationMessages
    ));
} catch (Exception $e) {
    if (!$dryRun) {
        try {
            $DBConn->query('SET FOREIGN_KEY_CHECKS=1');
            $DBConn->execute();
        } catch (Exception $inner) {
            // ignore
        }
    }

    error_log('Leave reset error: ' . $e->getMessage());
    echo json_encode(array(
        'success' => false,
        'message' => 'Failed to reset leave data: ' . $e->getMessage()
    ));
}

