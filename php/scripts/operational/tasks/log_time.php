<?php
/**
 * Log Operational Time API
 *
 * Logs time against operational tasks
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

require_once __DIR__ . '/../../../includes.php';
require_once __DIR__ . '/../../../classes/timeattendance.php';

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

    // Get POST data
    $operationalTaskID = $_POST['operationalTaskID'] ?? null;
    $taskDate = $_POST['taskDate'] ?? date('Y-m-d');
    $startTime = $_POST['startTime'] ?? null;
    $endTime = $_POST['endTime'] ?? null;
    $taskNarrative = $_POST['taskNarrative'] ?? '';
    $workTypeID = $_POST['workTypeID'] ?? 1;
    $clientID = $_POST['clientID'] ?? 0;
    $projectID = $_POST['projectID'] ?? null;
    $operationalProjectID = $_POST['operationalProjectID'] ?? null;
    $processID = $_POST['processID'] ?? null;

    if (!$operationalTaskID) {
        throw new Exception('Operational Task ID is required');
    }

    if (!$startTime || !$endTime) {
        throw new Exception('Start time and end time are required');
    }

    // Calculate duration
    $start = new DateTime($taskDate . ' ' . $startTime);
    $end = new DateTime($taskDate . ' ' . $endTime);
    $duration = $end->diff($start);
    $durationSeconds = ($duration->h * 3600) + ($duration->i * 60) + $duration->s;
    $durationString = sprintf('%02d:%02d:%02d', $duration->h, $duration->i, $duration->s);
    $workHours = $duration->h + ($duration->i / 60);

    // Prepare time log data
    $timeLogData = [
        'taskDate' => $taskDate,
        'employeeID' => $userID,
        'clientID' => $clientID,
        'projectID' => $projectID,
        'operationalTaskID' => $operationalTaskID,
        'operationalProjectID' => $operationalProjectID,
        'processID' => $processID,
        'workTypeID' => $workTypeID,
        'taskNarrative' => $taskNarrative,
        'startTime' => $startTime,
        'endTime' => $endTime,
        'taskDuration' => $durationString,
        'taskDurationSeconds' => $durationSeconds,
        'workHours' => (string)$workHours,
        'billable' => 'N',
        'billableRateValue' => 0,
        'taskType' => 'operational'
    ];

    // Log time
    $timelogID = TimeAttendance::logOperationalTime($timeLogData, $DBConn);

    if ($timelogID) {
        echo json_encode([
            'success' => true,
            'timelogID' => $timelogID,
            'hours' => $workHours
        ]);
    } else {
        throw new Exception('Failed to log time');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

