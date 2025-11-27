<?php
/**
 * Fetch active project tasks assigned to a specific employee.
 */
session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';

header('Content-Type: application/json');

if (!$isValidUser) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$employeeId = isset($_GET['employeeId']) ? (int)Utility::clean_string($_GET['employeeId']) : $userDetails->ID;
$limit = isset($_GET['limit']) ? (int)Utility::clean_string($_GET['limit']) : 50;
$limit = $limit > 0 && $limit <= 200 ? $limit : 50;

try {
    $sql = "SELECT
                t.projectTaskID,
                t.projectTaskName,
                t.taskDescription,
                t.taskStart,
                t.taskDeadline,
                t.taskStatusID,
                p.projectName
            FROM tija_project_tasks t
            LEFT JOIN tija_projects p ON t.projectID = p.projectID
            WHERE t.assigneeID = ?
            AND t.Suspended = 'N'
            AND t.Lapsed = 'N'
            ORDER BY t.taskDeadline IS NULL ASC, t.taskDeadline ASC
            LIMIT {$limit}";

    $rows = $DBConn->fetch_all_rows($sql, array(array($employeeId, 'i')));

    echo json_encode([
        'success' => true,
        'tasks' => $rows ?: array()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Unable to load tasks: ' . $e->getMessage()
    ]);
}
?>


