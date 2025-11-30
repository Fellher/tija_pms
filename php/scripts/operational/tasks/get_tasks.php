<?php
/**
 * Get Operational Tasks API
 *
 * Retrieves operational tasks with filters
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

require_once __DIR__ . '/../../../includes.php';
require_once __DIR__ . '/../../../classes/operationaltask.php';

header('Content-Type: application/json');

global $DBConn, $isValidUser;

// Check authentication
if (!$isValidUser) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $filters = [];

    // Apply filters from query parameters
    if (isset($_GET['status'])) {
        $filters['status'] = $_GET['status'];
    }

    if (isset($_GET['assigneeID'])) {
        $filters['assigneeID'] = $_GET['assigneeID'];
    }

    if (isset($_GET['functionalArea'])) {
        // Would need to join with template to filter by functional area
        // For now, simplified
    }

    if (isset($_GET['overdue']) && $_GET['overdue'] === 'true') {
        $tasks = OperationalTask::getOverdueTasks($filters, $DBConn);
    } elseif (isset($_GET['upcoming'])) {
        $daysAhead = (int)($_GET['upcoming'] ?? 7);
        $tasks = OperationalTask::getUpcomingTasks($daysAhead, $filters, $DBConn);
    } else {
        // Get all tasks matching filters
        $cols = array(
            'operationalTaskID', 'templateID', 'instanceNumber', 'dueDate',
            'startDate', 'completedDate', 'status', 'assigneeID', 'processID'
        );
        $tasks = $DBConn->retrieve_db_table_rows('tija_operational_tasks', $cols, $filters);
    }

    echo json_encode([
        'success' => true,
        'tasks' => $tasks ?: [],
        'count' => is_array($tasks) ? count($tasks) : 0
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

