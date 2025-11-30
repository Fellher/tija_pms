<?php
/**
 * Operational Task Class
 *
 * Manages operational task instances
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

class OperationalTask {

    /**
     * Instantiate task from template
     *
     * @param int $templateID Template ID
     * @param date $dueDate Due date
     * @param object $DBConn Database connection
     * @return int|false Task ID or false
     */
    public static function instantiateFromTemplate($templateID, $dueDate, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        // Get template
        $template = OperationalTaskTemplate::getTemplate($templateID, $DBConn);
        if (!$template) {
            return false;
        }

        // Get next instance number
        $lastInstance = $DBConn->retrieve_db_table_rows(
            'tija_operational_tasks',
            ['instanceNumber'],
            ['templateID' => $templateID],
            false,
            'ORDER BY instanceNumber DESC LIMIT 1'
        );

        $instanceNumber = $lastInstance && isset($lastInstance[0])
            ? $lastInstance[0]['instanceNumber'] + 1
            : 1;

        // Determine assignee based on assignment rule
        $assigneeID = self::determineAssignee($template, $DBConn);

        if (!$assigneeID) {
            return false; // Could not determine assignee
        }

        // Create task instance
        $taskData = [
            'templateID' => $templateID,
            'instanceNumber' => $instanceNumber,
            'dueDate' => $dueDate,
            'status' => 'pending',
            'assigneeID' => $assigneeID,
            'processID' => $template['processID']
        ];

        $cols = array(
            'templateID', 'instanceNumber', 'dueDate', 'status',
            'assigneeID', 'processID'
        );

        $taskID = $DBConn->insert_db_table_row('tija_operational_tasks', $cols, $taskData);

        // Start workflow if template has workflow
        if ($taskID && $template['workflowID']) {
            require_once 'workflowengine.php';
            WorkflowEngine::startWorkflow($template['workflowID'], ['operationalTaskID' => $taskID], $DBConn);
        }

        return $taskID;
    }

    /**
     * Determine assignee based on assignment rule
     *
     * @param array $template Template data
     * @param object $DBConn Database connection
     * @return int|false Assignee ID or false
     */
    private static function determineAssignee($template, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $assignmentRule = json_decode($template['assignmentRule'] ?? '{}', true);

        if (empty($assignmentRule) || !isset($assignmentRule['type'])) {
            return false;
        }

        switch ($assignmentRule['type']) {
            case 'role':
                // Get first employee with this role
                // This is a simplified version - would need role-to-employee mapping
                return $assignmentRule['employeeID'] ?? false;

            case 'employee':
                return $assignmentRule['employeeID'] ?? false;

            case 'function_head':
                // Get function head for functional area
                $whereArr = [
                    'functionalArea' => $template['functionalArea'],
                    'isActive' => 'Y'
                ];
                $functionHead = $DBConn->retrieve_db_table_rows(
                    'tija_function_head_assignments',
                    ['employeeID'],
                    $whereArr,
                    true
                );
                return $functionHead ? $functionHead['employeeID'] : false;

            case 'round_robin':
                // Round-robin assignment - get last assignee and rotate
                // Simplified implementation
                return $assignmentRule['employeeID'] ?? false;

            default:
                return false;
        }
    }

    /**
     * Get task instance
     *
     * @param int $operationalTaskID Task ID
     * @param object $DBConn Database connection
     * @return array|false Task data or false
     */
    public static function getInstance($operationalTaskID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array(
            'operationalTaskID', 'templateID', 'workflowInstanceID', 'instanceNumber',
            'dueDate', 'startDate', 'completedDate', 'status', 'assigneeID',
            'processID', 'actualDuration', 'nextInstanceDueDate', 'parentInstanceID',
            'blockedByTaskID', 'sopReviewed', 'DateAdded', 'LastUpdate'
        );

        return $DBConn->retrieve_db_table_rows('tija_operational_tasks', $cols, ['operationalTaskID' => $operationalTaskID], true);
    }

    /**
     * Update task status
     *
     * @param int $operationalTaskID Task ID
     * @param string $status New status
     * @param object $DBConn Database connection
     * @return bool Success
     */
    public static function updateStatus($operationalTaskID, $status, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $updateData = ['status' => $status];

        if ($status === 'in_progress' && !self::getInstance($operationalTaskID, $DBConn)['startDate']) {
            $updateData['startDate'] = date('Y-m-d');
        }

        return $DBConn->update_db_table_row('tija_operational_tasks', $updateData, ['operationalTaskID' => $operationalTaskID]);
    }

    /**
     * Complete task
     *
     * @param int $operationalTaskID Task ID
     * @param float $actualDuration Actual hours
     * @param array $checklistData Checklist completion data
     * @param object $DBConn Database connection
     * @return bool Success
     */
    public static function completeTask($operationalTaskID, $actualDuration, $checklistData = [], $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        // Verify all mandatory checklist items are completed
        $checklist = $DBConn->retrieve_db_table_rows(
            'tija_operational_task_checklists',
            ['checklistItemID', 'isMandatory', 'isCompleted'],
            ['operationalTaskID' => $operationalTaskID, 'isMandatory' => 'Y']
        );

        if ($checklist) {
            foreach ($checklist as $item) {
                if ($item['isCompleted'] !== 'Y') {
                    return false; // Mandatory item not completed
                }
            }
        }

        // Update task
        $updateData = [
            'status' => 'completed',
            'completedDate' => date('Y-m-d H:i:s'),
            'actualDuration' => $actualDuration
        ];

        $success = $DBConn->update_db_table_row('tija_operational_tasks', $updateData, ['operationalTaskID' => $operationalTaskID]);

        if ($success) {
            // Regenerate next instance
            self::regenerateNextInstance($operationalTaskID, $DBConn);
        }

        return $success;
    }

    /**
     * Regenerate next instance
     *
     * @param int $operationalTaskID Task ID
     * @param object $DBConn Database connection
     * @return int|false Next instance ID or false
     */
    public static function regenerateNextInstance($operationalTaskID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $task = self::getInstance($operationalTaskID, $DBConn);
        if (!$task || !$task['templateID']) {
            return false;
        }

        $template = OperationalTaskTemplate::getTemplate($task['templateID'], $DBConn);
        if (!$template || $template['isActive'] !== 'Y') {
            return false;
        }

        // Calculate next due date based on frequency
        $nextDueDate = self::calculateNextDueDate($template, $task['dueDate']);

        if (!$nextDueDate) {
            return false;
        }

        // Create next instance
        $nextTaskID = self::instantiateFromTemplate($task['templateID'], $nextDueDate, $DBConn);

        if ($nextTaskID) {
            // Update parent instance with next due date
            $DBConn->update_db_table_row(
                'tija_operational_tasks',
                ['nextInstanceDueDate' => $nextDueDate],
                ['operationalTaskID' => $operationalTaskID]
            );
        }

        return $nextTaskID;
    }

    /**
     * Calculate next due date based on frequency
     *
     * @param array $template Template data
     * @param string $currentDueDate Current due date
     * @return string|false Next due date or false
     */
    private static function calculateNextDueDate($template, $currentDueDate) {
        $current = new DateTime($currentDueDate);
        $frequencyType = $template['frequencyType'];
        $interval = $template['frequencyInterval'] ?? 1;

        switch ($frequencyType) {
            case 'daily':
                $current->modify("+{$interval} days");
                break;

            case 'weekly':
                $current->modify("+{$interval} weeks");
                break;

            case 'monthly':
                $current->modify("+{$interval} months");
                // Adjust to specific day of month if set
                if ($template['frequencyDayOfMonth']) {
                    $current->setDate($current->format('Y'), $current->format('m'), $template['frequencyDayOfMonth']);
                }
                break;

            case 'quarterly':
                $current->modify("+{$interval} months");
                break;

            case 'annually':
                $current->modify("+{$interval} years");
                // Adjust to specific month and day if set
                if ($template['frequencyMonthOfYear'] && $template['frequencyDayOfMonth']) {
                    $current->setDate($current->format('Y'), $template['frequencyMonthOfYear'], $template['frequencyDayOfMonth']);
                }
                break;

            default:
                return false;
        }

        return $current->format('Y-m-d');
    }

    /**
     * Get overdue tasks
     *
     * @param array $filters Additional filters
     * @param object $DBConn Database connection
     * @return array|false Tasks or false
     */
    public static function getOverdueTasks($filters = [], $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array(
            'operationalTaskID', 'templateID', 'dueDate', 'status',
            'assigneeID', 'processID'
        );

        // Build custom query for complex conditions (IN clause and comparison operators)
        $whereConditions = [];
        $params = [];

        // Status IN clause
        $statuses = ['pending', 'in_progress'];
        $statusPlaceholders = implode(',', array_fill(0, count($statuses), '?'));
        $whereConditions[] = "status IN ({$statusPlaceholders})";
        foreach ($statuses as $status) {
            $params[] = array($status, 's');
        }

        // Due date less than today
        $whereConditions[] = "dueDate < ?";
        $params[] = array(date('Y-m-d'), 's');

        // Handle additional filters (excluding functionalArea which doesn't exist in tasks table)
        foreach ($filters as $key => $value) {
            if ($key === 'functionalArea') {
                // functionalArea is in templates table, not tasks table
                // We'll need to join or filter after retrieval
                continue;
            }

            if (is_array($value) && isset($value[0]) && $value[0] === 'IN') {
                // Handle IN clause
                $values = $value[1];
                $placeholders = implode(',', array_fill(0, count($values), '?'));
                $whereConditions[] = "{$key} IN ({$placeholders})";
                foreach ($values as $val) {
                    $params[] = array($val, 's');
                }
            } elseif (is_array($value) && isset($value[0]) && in_array($value[0], ['<', '>', '<=', '>='])) {
                // Handle comparison operators
                $whereConditions[] = "{$key} {$value[0]} ?";
                $params[] = array($value[1], 's');
            } else {
                // Standard equality
                $whereConditions[] = "{$key} = ?";
                $params[] = array($value, 's');
            }
        }

        $whereClause = implode(' AND ', $whereConditions);
        $colList = implode(', ', $cols);

        $query = "SELECT {$colList} FROM tija_operational_tasks WHERE {$whereClause}";

        $tasks = $DBConn->retrieve_db_table_rows_custom($query, $params);

        // If functionalArea filter was provided, filter results by joining with templates
        if (isset($filters['functionalArea'])) {
            $filteredTasks = [];
            if($tasks) {
                foreach ($tasks as $task) {
                    $template = OperationalTaskTemplate::getTemplate($task['templateID'], $DBConn);
                    if ($template && isset($template['functionalArea']) && $template['functionalArea'] === $filters['functionalArea']) {
                        $filteredTasks[] = $task;
                    }
                }
            }
            return $filteredTasks ?: false;
        }
    }

    /**
     * Get upcoming tasks
     *
     * @param int $daysAhead Days ahead to look
     * @param array $filters Additional filters
     * @param object $DBConn Database connection
     * @return array|false Tasks or false
     */
    public static function getUpcomingTasks($daysAhead = 7, $filters = [], $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $endDate = date('Y-m-d', strtotime("+{$daysAhead} days"));

        $whereArr = array_merge([
            'status' => ['pending', 'in_progress'],
            'dueDate' => ['BETWEEN', date('Y-m-d'), $endDate]
        ], $filters);

        $cols = array(
            'operationalTaskID', 'templateID', 'dueDate', 'status',
            'assigneeID', 'processID'
        );

        return $DBConn->retrieve_db_table_rows('tija_operational_tasks', $cols, $whereArr);
    }
}

