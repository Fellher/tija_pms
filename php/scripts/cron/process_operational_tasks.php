<?php
/**
 * Operational Tasks Processor (Cron Job)
 *
 * Processes scheduled operational tasks and creates instances
 * Run this script hourly via cron
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

// Set execution time limit
set_time_limit(300); // 5 minutes

// Include required files
require_once __DIR__ . '/../../includes.php';
require_once __DIR__ . '/../../classes/operationaltaskscheduler.php';
require_once __DIR__ . '/../../classes/operationaltasktemplate.php';
require_once __DIR__ . '/../../classes/operationaltask.php';

// Initialize database connection
global $DBConn;

if (!$DBConn) {
    die("Database connection not available\n");
}

// Log start
$logFile = __DIR__ . '/../../logs/operational_tasks_cron.log';
$logMessage = date('Y-m-d H:i:s') . " - Starting operational tasks processing\n";
file_put_contents($logFile, $logMessage, FILE_APPEND);

try {
    // Process scheduled tasks
    $results = OperationalTaskScheduler::processScheduledTasks($DBConn);

    // Log results
    $logMessage = date('Y-m-d H:i:s') . " - Processing complete\n";
    $logMessage .= "  Templates evaluated: {$results['templatesEvaluated']}\n";
    $logMessage .= "  Instances created: {$results['instancesCreated']}\n";

    if (!empty($results['errors'])) {
        $logMessage .= "  Errors:\n";
        foreach ($results['errors'] as $error) {
            $logMessage .= "    - {$error}\n";
        }
    }

    file_put_contents($logFile, $logMessage, FILE_APPEND);

    // Output for cron logging
    echo $logMessage;

} catch (Exception $e) {
    $errorMessage = date('Y-m-d H:i:s') . " - ERROR: " . $e->getMessage() . "\n";
    file_put_contents($logFile, $errorMessage, FILE_APPEND);
    echo $errorMessage;
    exit(1);
}

exit(0);

