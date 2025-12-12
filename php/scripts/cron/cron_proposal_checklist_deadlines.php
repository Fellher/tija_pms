<?php
/**
 * Proposal Checklist Deadline Cron Job
 *
 * This script should be run daily via cron to:
 * 1. Send reminder notifications for upcoming deadlines (within 3 days)
 * 2. Send overdue notifications for past-due items
 *
 * Cron example (run daily at 8:00 AM):
 * 0 8 * * * /usr/bin/php /path/to/cron_proposal_checklist_deadlines.php
 *
 * @version 1.0
 * @date 2025-12-12
 */

// Set up the base path
$base = '../../../';
set_include_path($base);

// Include dependencies
include 'php/includes.php';

// Log start
error_log("[Proposal Checklist Cron] Starting deadline check at " . date('Y-m-d H:i:s'));

// Initialize results
$results = array(
    'start_time' => date('Y-m-d H:i:s'),
    'reminders' => array(),
    'overdue' => array(),
    'errors' => array()
);

try {
    // Check if the ProposalChecklistNotification class exists
    if (!class_exists('ProposalChecklistNotification')) {
        throw new Exception("ProposalChecklistNotification class not found. Please ensure the class file is loaded.");
    }

    // =========================================================================
    // STEP 1: Process deadline reminders (items due within 3 days)
    // =========================================================================
    error_log("[Proposal Checklist Cron] Processing deadline reminders...");

    $reminderResults = ProposalChecklistNotification::processDeadlineReminders(3, $DBConn);
    $results['reminders'] = $reminderResults;

    error_log("[Proposal Checklist Cron] Reminders: Checked {$reminderResults['checked']} items, sent {$reminderResults['notifications_sent']} notifications");

    // =========================================================================
    // STEP 2: Process overdue notifications
    // =========================================================================
    error_log("[Proposal Checklist Cron] Processing overdue notifications...");

    $overdueResults = ProposalChecklistNotification::processOverdueNotifications($DBConn);
    $results['overdue'] = $overdueResults;

    error_log("[Proposal Checklist Cron] Overdue: Checked {$overdueResults['checked']} items, sent {$overdueResults['notifications_sent']} notifications");

    // =========================================================================
    // STEP 3: Process notification queue (send pending emails)
    // =========================================================================
    if (class_exists('Notification')) {
        error_log("[Proposal Checklist Cron] Processing notification queue...");
        $queueResults = Notification::processQueueImmediately(50, $DBConn);
        $results['queue_processed'] = $queueResults;
    }

    $results['success'] = true;
    $results['end_time'] = date('Y-m-d H:i:s');

} catch (Exception $e) {
    $results['success'] = false;
    $results['error'] = $e->getMessage();
    $results['errors'][] = $e->getMessage();
    error_log("[Proposal Checklist Cron] Error: " . $e->getMessage());
}

// Log completion
error_log("[Proposal Checklist Cron] Completed at " . date('Y-m-d H:i:s'));

// If running from CLI, output results
if (php_sapi_name() === 'cli') {
    echo "=== Proposal Checklist Deadline Cron Results ===\n";
    echo "Start: {$results['start_time']}\n";
    echo "End: " . ($results['end_time'] ?? 'N/A') . "\n";
    echo "\n--- Deadline Reminders ---\n";
    echo "Items checked: " . ($results['reminders']['checked'] ?? 0) . "\n";
    echo "Notifications sent: " . ($results['reminders']['notifications_sent'] ?? 0) . "\n";
    echo "\n--- Overdue Notifications ---\n";
    echo "Items checked: " . ($results['overdue']['checked'] ?? 0) . "\n";
    echo "Notifications sent: " . ($results['overdue']['notifications_sent'] ?? 0) . "\n";

    if (!empty($results['errors'])) {
        echo "\n--- Errors ---\n";
        foreach ($results['errors'] as $error) {
            echo "- {$error}\n";
        }
    }

    echo "\n=== End of Report ===\n";
}

// If called via HTTP, return JSON
if (php_sapi_name() !== 'cli') {
    header('Content-Type: application/json');
    echo json_encode($results);
}
