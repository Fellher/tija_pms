<?php
/**
 * Quick Actions for Time Attendance
 * ==================================
 * Provides quick entry features like templates, copy from previous day, batch entry
 */

require_once '../../../includes/config.php';
require_once '../../../includes/classes/Database.php';
require_once '../../../includes/classes/TimeAttendance.php';
require_once '../../../includes/classes/Utility.php';
require_once '../../../includes/classes/Alert.php';

// Start session and check authentication
session_start();

if (!isset($_SESSION['userID']) || empty($_SESSION['userID'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$userID = $_SESSION['userID'];
$DBConn = Database::getConnection();

// Get action type
$action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : '';

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    switch ($action) {
        case 'copy_previous_day':
            $response = copyPreviousDay($userID, $DBConn);
            break;

        case 'get_templates':
            $response = getTemplates($userID, $DBConn);
            break;

        case 'save_template':
            $response = saveTemplate($userID, $DBConn);
            break;

        case 'load_template':
            $response = loadTemplate($userID, $DBConn);
            break;

        case 'delete_template':
            $response = deleteTemplate($userID, $DBConn);
            break;

        case 'get_previous_day_entries':
            $response = getPreviousDayEntries($userID, $DBConn);
            break;

        default:
            $response['message'] = 'Invalid action';
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
exit;

/**
 * Copy entries from previous working day
 */
function copyPreviousDay($userID, $DBConn) {
    $targetDate = isset($_POST['targetDate']) ? Utility::clean_string($_POST['targetDate']) : date('Y-m-d');

    // Find the previous working day (skip weekends)
    $currentDate = new DateTime($targetDate);
    $currentDate->modify('-1 day');

    // Skip weekends
    while ($currentDate->format('N') >= 6) {
        $currentDate->modify('-1 day');
    }

    $previousDate = $currentDate->format('Y-m-d');

    // Get previous day's time logs
    $previousLogs = TimeAttendance::project_tasks_time_logs_full([
        'taskDate' => $previousDate,
        'employeeID' => $userID,
        'Suspended' => 'N'
    ], false, $DBConn);

    if (!$previousLogs) {
        return [
            'success' => false,
            'message' => 'No entries found for ' . $previousDate
        ];
    }

    $copiedCount = 0;
    $entries = [];

    foreach ($previousLogs as $log) {
        $entries[] = [
            'projectID' => $log->projectID,
            'projectPhaseID' => $log->projectPhaseID,
            'projectTaskID' => $log->projectTaskID,
            'workTypeID' => $log->workTypeID,
            'taskDuration' => $log->taskDuration,
            'taskStatusID' => $log->taskStatusID,
            'taskNarrative' => $log->taskNarrative,
            'projectName' => $log->projectName,
            'clientName' => $log->clientName,
            'projectTaskName' => $log->projectTaskName
        ];
        $copiedCount++;
    }

    return [
        'success' => true,
        'message' => "Found {$copiedCount} entries from {$previousDate}",
        'data' => [
            'entries' => $entries,
            'sourceDate' => $previousDate,
            'count' => $copiedCount
        ]
    ];
}

/**
 * Get previous day entries for display
 */
function getPreviousDayEntries($userID, $DBConn) {
    $targetDate = isset($_POST['date']) ? Utility::clean_string($_POST['date']) : date('Y-m-d');

    $currentDate = new DateTime($targetDate);
    $currentDate->modify('-1 day');

    while ($currentDate->format('N') >= 6) {
        $currentDate->modify('-1 day');
    }

    $previousDate = $currentDate->format('Y-m-d');

    $logs = TimeAttendance::project_tasks_time_logs_full([
        'taskDate' => $previousDate,
        'employeeID' => $userID,
        'Suspended' => 'N'
    ], false, $DBConn);

    $entries = [];
    if ($logs) {
        foreach ($logs as $log) {
            $entries[] = [
                'timelogID' => $log->timelogID,
                'projectID' => $log->projectID,
                'projectName' => $log->projectName,
                'clientName' => $log->clientName,
                'taskName' => $log->projectTaskName,
                'duration' => $log->taskDuration,
                'narrative' => $log->taskNarrative,
                'workType' => $log->workTypeName
            ];
        }
    }

    return [
        'success' => true,
        'data' => [
            'date' => $previousDate,
            'entries' => $entries,
            'count' => count($entries)
        ]
    ];
}

/**
 * Get saved templates for user
 */
function getTemplates($userID, $DBConn) {
    $query = "SELECT * FROM time_entry_templates
              WHERE userID = ? AND Suspended = 'N'
              ORDER BY templateName ASC";

    $stmt = $DBConn->prepare($query);
    $stmt->bind_param('i', $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    $templates = [];
    while ($row = $result->fetch_object()) {
        $templates[] = [
            'templateID' => $row->templateID,
            'templateName' => $row->templateName,
            'templateData' => json_decode($row->templateData, true),
            'createdDate' => $row->createdDate
        ];
    }

    return [
        'success' => true,
        'data' => [
            'templates' => $templates,
            'count' => count($templates)
        ]
    ];
}

/**
 * Save new template
 */
function saveTemplate($userID, $DBConn) {
    $templateName = isset($_POST['templateName']) ? Utility::clean_string($_POST['templateName']) : '';
    $templateData = isset($_POST['templateData']) ? $_POST['templateData'] : [];

    if (empty($templateName)) {
        return ['success' => false, 'message' => 'Template name is required'];
    }

    // Check if template name already exists
    $checkQuery = "SELECT templateID FROM time_entry_templates
                   WHERE userID = ? AND templateName = ? AND Suspended = 'N'";
    $stmt = $DBConn->prepare($checkQuery);
    $stmt->bind_param('is', $userID, $templateName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return ['success' => false, 'message' => 'Template name already exists'];
    }

    // Insert new template
    $templateDataJson = json_encode($templateData);
    $insertQuery = "INSERT INTO time_entry_templates
                    (userID, templateName, templateData, createdDate, Suspended)
                    VALUES (?, ?, ?, NOW(), 'N')";

    $stmt = $DBConn->prepare($insertQuery);
    $stmt->bind_param('iss', $userID, $templateName, $templateDataJson);

    if ($stmt->execute()) {
        return [
            'success' => true,
            'message' => 'Template saved successfully',
            'data' => ['templateID' => $stmt->insert_id]
        ];
    }

    return ['success' => false, 'message' => 'Failed to save template'];
}

/**
 * Load template data
 */
function loadTemplate($userID, $DBConn) {
    $templateID = isset($_POST['templateID']) ? intval($_POST['templateID']) : 0;

    if ($templateID <= 0) {
        return ['success' => false, 'message' => 'Invalid template ID'];
    }

    $query = "SELECT * FROM time_entry_templates
              WHERE templateID = ? AND userID = ? AND Suspended = 'N'";

    $stmt = $DBConn->prepare($query);
    $stmt->bind_param('ii', $templateID, $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return ['success' => false, 'message' => 'Template not found'];
    }

    $template = $result->fetch_object();

    return [
        'success' => true,
        'data' => [
            'templateName' => $template->templateName,
            'templateData' => json_decode($template->templateData, true)
        ]
    ];
}

/**
 * Delete template
 */
function deleteTemplate($userID, $DBConn) {
    $templateID = isset($_POST['templateID']) ? intval($_POST['templateID']) : 0;

    if ($templateID <= 0) {
        return ['success' => false, 'message' => 'Invalid template ID'];
    }

    // Soft delete
    $query = "UPDATE time_entry_templates
              SET Suspended = 'Y'
              WHERE templateID = ? AND userID = ?";

    $stmt = $DBConn->prepare($query);
    $stmt->bind_param('ii', $templateID, $userID);

    if ($stmt->execute()) {
        return ['success' => true, 'message' => 'Template deleted successfully'];
    }

    return ['success' => false, 'message' => 'Failed to delete template'];
}

/**
 * Create the templates table if it doesn't exist
 */
function createTemplateTable($DBConn) {
    $query = "CREATE TABLE IF NOT EXISTS `time_entry_templates` (
        `templateID` int(11) NOT NULL AUTO_INCREMENT,
        `userID` int(11) NOT NULL,
        `templateName` varchar(100) NOT NULL,
        `templateData` text NOT NULL,
        `createdDate` datetime NOT NULL,
        `Suspended` char(1) DEFAULT 'N',
        PRIMARY KEY (`templateID`),
        KEY `userID` (`userID`),
        KEY `Suspended` (`Suspended`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    return $DBConn->query($query);
}

