<?php
/**
 * Operational Task Scheduler Service
 *
 * Handles scheduled task processing and instantiation
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

class OperationalTaskScheduler {

    /**
     * Process scheduled tasks (cron job entry point)
     * Only processes templates with processingMode = 'cron' or 'both'
     *
     * @param object $DBConn Database connection
     * @return array Processing results
     */
    public static function processScheduledTasks($DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $results = [
            'templatesEvaluated' => 0,
            'instancesCreated' => 0,
            'errors' => []
        ];

        // Get active templates with cron processing enabled
        $templates = OperationalTaskTemplate::listTemplates(['isActive' => 'Y'], $DBConn);

        if (!$templates) {
            return $results;
        }

        foreach ($templates as $template) {
            // Check processing mode - only process cron or both
            $processingMode = is_object($template) ? ($template->processingMode ?? 'cron') : ($template['processingMode'] ?? 'cron');
            if ($processingMode === 'manual') {
                continue; // Skip manual-only templates
            }

            $results['templatesEvaluated']++;

            try {
                // Evaluate if template needs instantiation
                $dueDate = self::evaluateTemplate($template, $DBConn);

                if ($dueDate) {
                    // Get templateID - handle both object and array
                    $templateID = is_object($template) ? $template->templateID : $template['templateID'];

                    // Create instances
                    $instanceID = OperationalTask::instantiateFromTemplate($templateID, $dueDate, $DBConn);

                    if ($instanceID) {
                        $results['instancesCreated']++;
                    } else {
                        $results['errors'][] = "Failed to create instance for template {$templateID}";
                    }
                }
            } catch (Exception $e) {
                $templateID = is_object($template) ? ($template->templateID ?? 'unknown') : ($template['templateID'] ?? 'unknown');
                $results['errors'][] = "Error processing template {$templateID}: " . $e->getMessage();
            }
        }

        // Handle dependencies
        self::handleDependencies(null, $DBConn);

        return $results;
    }

    /**
     * Check for pending scheduled tasks and create notifications for manual processing
     * Called on user login
     *
     * @param int $employeeID Employee ID
     * @param object $DBConn Database connection
     * @return array Notification results
     */
    public static function checkPendingTasksForUser($employeeID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $results = [
            'notificationsCreated' => 0,
            'tasksReady' => 0,
            'errors' => []
        ];

        // Get templates assigned to this user (or their role) that need manual processing
        // This is a simplified version - would need to check assignment rules
        $templates = OperationalTaskTemplate::listTemplates([
            'isActive' => 'Y',
            'processingMode' => ['IN', ['manual', 'both']] // Special format for IN clause
        ], $DBConn);

        if (!$templates) {
            return $results;
        }

        foreach ($templates as $template) {
            try {
                // Check if this template is assigned to this user
                $isAssigned = self::isTemplateAssignedToUser($template, $employeeID, $DBConn);

                if (!$isAssigned) {
                    continue;
                }

                // Evaluate if template needs instantiation
                $dueDate = self::evaluateTemplate($template, $DBConn);

                if ($dueDate) {
                    // Get templateID - handle both object and array
                    $templateID = is_object($template) ? $template->templateID : $template['templateID'];

                    // Check if notification already exists
                    $existingNotification = $DBConn->retrieve_db_table_rows(
                        'tija_operational_task_notifications',
                        ['notificationID'],
                        [
                            'templateID' => $templateID,
                            'employeeID' => $employeeID,
                            'dueDate' => $dueDate,
                            'status' => ['IN', ['pending', 'sent']]
                        ],
                        true
                    );

                    if (!$existingNotification) {
                        // Create notification
                        $notificationData = [
                            'templateID' => $templateID,
                            'employeeID' => $employeeID,
                            'dueDate' => $dueDate,
                            'notificationType' => 'scheduled_task_ready',
                            'status' => 'pending'
                        ];

                        $cols = array('templateID', 'employeeID', 'dueDate', 'notificationType', 'status');
                        $notificationID = $DBConn->insert_db_table_row('tija_operational_task_notifications', $cols, $notificationData);

                        if ($notificationID) {
                            $results['notificationsCreated']++;
                            $results['tasksReady']++;
                        }
                    } else {
                        $results['tasksReady']++;
                    }
                }
            } catch (Exception $e) {
                $templateID = is_object($template) ? ($template->templateID ?? 'unknown') : ($template['templateID'] ?? 'unknown');
                $results['errors'][] = "Error checking template {$templateID}: " . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Check if template is assigned to user
     *
     * @param object|array $template Template data
     * @param int $employeeID Employee ID
     * @param object $DBConn Database connection
     * @return bool Is assigned
     */
    private static function isTemplateAssignedToUser($template, $employeeID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        // Handle both object and array access
        $assignmentRuleJson = is_object($template) ? ($template->assignmentRule ?? '{}') : ($template['assignmentRule'] ?? '{}');
        $assignmentRule = json_decode($assignmentRuleJson, true);

        if (empty($assignmentRule) || !isset($assignmentRule['type'])) {
            return false;
        }

        switch ($assignmentRule['type']) {
            case 'employee':
                return ($assignmentRule['employeeID'] ?? null) == $employeeID;

            case 'role':
                // Would need to check user's roles
                // Simplified - assume true if role matches
                return true; // Placeholder

            case 'function_head':
                // Check if user is function head for this functional area
                $functionHead = $DBConn->retrieve_db_table_rows(
                    'tija_function_head_assignments',
                    ['assignmentID'],
                    [
                        'employeeID' => $employeeID,
                        'functionalArea' => is_object($template) ? ($template->functionalArea ?? null) : ($template['functionalArea'] ?? null),
                        'isActive' => 'Y'
                    ],
                    true
                );
                return $functionHead ? true : false;

            case 'round_robin':
                // Would need round-robin logic
                return true; // Placeholder

            default:
                return false;
        }
    }

    /**
     * Process pending task from notification (manual processing)
     *
     * @param int $notificationID Notification ID
     * @param object $DBConn Database connection
     * @return int|false Task instance ID or false
     */
    public static function processPendingTaskFromNotification($notificationID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        // Get notification
        $notification = $DBConn->retrieve_db_table_rows(
            'tija_operational_task_notifications',
            ['notificationID', 'templateID', 'employeeID', 'dueDate', 'status'],
            ['notificationID' => $notificationID],
            true
        );

        if (!$notification || $notification['status'] === 'processed') {
            return false;
        }

        // Get template
        $template = OperationalTaskTemplate::getTemplate($notification['templateID'], $DBConn);
        if (!$template) {
            return false;
        }

        // Create task instance
        $instanceID = OperationalTask::instantiateFromTemplate(
            $notification['templateID'],
            $notification['dueDate'],
            $DBConn
        );

        if ($instanceID) {
            // Update notification
            $updateData = [
                'status' => 'processed',
                'processedDate' => date('Y-m-d H:i:s'),
                'taskInstanceID' => $instanceID
            ];

            $DBConn->update_db_table_row(
                'tija_operational_task_notifications',
                $updateData,
                ['notificationID' => $notificationID]
            );
        }

        return $instanceID;
    }

    /**
     * Get pending task notifications for user
     *
     * @param int $employeeID Employee ID
     * @param object $DBConn Database connection
     * @return array|false Notifications or false
     */
    public static function getPendingTaskNotifications($employeeID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array(
            'notificationID', 'templateID', 'employeeID', 'dueDate',
            'notificationType', 'status', 'sentDate', 'DateAdded'
        );

        // Use custom SQL query for IN clause
        $statuses = ['pending', 'sent'];
        $placeholders = implode(',', array_fill(0, count($statuses), '?'));

        $query = "SELECT " . implode(', ', $cols) . "
                  FROM tija_operational_task_notifications
                  WHERE employeeID = ? AND status IN ({$placeholders})";

        $params = [
            array($employeeID, 'i')
        ];
        foreach ($statuses as $status) {
            $params[] = array($status, 's');
        }

        $notifications = $DBConn->retrieve_db_table_rows_custom($query, $params);

        if ($notifications) {
            // Get template details for each notification
            foreach ($notifications as &$notification) {
                $template = OperationalTaskTemplate::getTemplate($notification['templateID'], $DBConn);
                if ($template) {
                    $notification['templateName'] = is_object($template) ? ($template->templateName ?? 'Unknown Template') : ($template['templateName'] ?? 'Unknown Template');
                    $notification['templateDescription'] = is_object($template) ? ($template->templateDescription ?? '') : ($template['templateDescription'] ?? '');
                } else {
                    $notification['templateName'] = 'Unknown Template';
                    $notification['templateDescription'] = '';
                }
            }
        }

        return $notifications ?: false;
    }

    /**
     * Evaluate template to determine if instantiation is needed
     *
     * @param array $template Template data
     * @param object $DBConn Database connection
     * @return string|false Due date or false
     */
    private static function evaluateTemplate($template, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        // Handle both object and array access
        $frequencyType = is_object($template) ? ($template->frequencyType ?? null) : ($template['frequencyType'] ?? null);
        $templateID = is_object($template) ? ($template->templateID ?? null) : ($template['templateID'] ?? null);
        $today = date('Y-m-d');

        // Check if event-driven
        if ($frequencyType === 'event_driven') {
            return false; // Event-driven tasks are handled separately
        }

        // Get last instance for this template
        $lastInstance = $DBConn->retrieve_db_table_rows(
            'tija_operational_tasks',
            ['dueDate', 'nextInstanceDueDate'],
            ['templateID' => $templateID],
            false,
            'ORDER BY dueDate DESC LIMIT 1'
        );

        $nextDueDate = null;

        if ($lastInstance && isset($lastInstance[0])) {
            $nextDueDate = $lastInstance[0]['nextInstanceDueDate'] ?? null;
        }

        // If no next due date, calculate from template
        if (!$nextDueDate) {
            $nextDueDate = self::calculateNextDueDate($template, $today);
        }

        // Check if due date is today or in the past
        if ($nextDueDate && $nextDueDate <= $today) {
            return $nextDueDate;
        }

        return false;
    }

    /**
     * Calculate next due date from template
     *
     * @param object|array $template Template data
     * @param string $baseDate Base date
     * @return string|false Due date or false
     */
    private static function calculateNextDueDate($template, $baseDate) {
        $date = new DateTime($baseDate);
        // Handle both object and array access
        $frequencyType = is_object($template) ? ($template->frequencyType ?? null) : ($template['frequencyType'] ?? null);
        $interval = is_object($template) ? ($template->frequencyInterval ?? 1) : ($template['frequencyInterval'] ?? 1);

        switch ($frequencyType) {
            case 'daily':
                return $date->format('Y-m-d');

            case 'weekly':
                $dayOfWeek = is_object($template) ? ($template->frequencyDayOfWeek ?? $date->format('N')) : ($template['frequencyDayOfWeek'] ?? $date->format('N'));
                $date->modify("next Monday +{$dayOfWeek} days");
                return $date->format('Y-m-d');

            case 'monthly':
                $dayOfMonth = is_object($template) ? ($template->frequencyDayOfMonth ?? $date->format('d')) : ($template['frequencyDayOfMonth'] ?? $date->format('d'));
                $date->setDate($date->format('Y'), $date->format('m'), $dayOfMonth);
                if ($date->format('Y-m-d') < $baseDate) {
                    $date->modify('+1 month');
                }
                return $date->format('Y-m-d');

            case 'quarterly':
                $dayOfMonth = is_object($template) ? ($template->frequencyDayOfMonth ?? $date->format('d')) : ($template['frequencyDayOfMonth'] ?? $date->format('d'));
                $quarter = ceil($date->format('n') / 3);
                $month = ($quarter * 3);
                $date->setDate($date->format('Y'), $month, $dayOfMonth);
                if ($date->format('Y-m-d') < $baseDate) {
                    $date->modify('+3 months');
                }
                return $date->format('Y-m-d');

            case 'annually':
                $month = is_object($template) ? ($template->frequencyMonthOfYear ?? $date->format('m')) : ($template['frequencyMonthOfYear'] ?? $date->format('m'));
                $day = is_object($template) ? ($template->frequencyDayOfMonth ?? $date->format('d')) : ($template['frequencyDayOfMonth'] ?? $date->format('d'));
                $date->setDate($date->format('Y'), $month, $day);
                if ($date->format('Y-m-d') < $baseDate) {
                    $date->modify('+1 year');
                }
                return $date->format('Y-m-d');

            default:
                return false;
        }
    }

    /**
     * Handle task dependencies
     *
     * @param int|null $operationalTaskID Task ID (null for all)
     * @param object $DBConn Database connection
     * @return void
     */
    public static function handleDependencies($operationalTaskID = null, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        // Get dependencies
        $whereArr = [];
        if ($operationalTaskID) {
            $whereArr = ['successorTaskID' => $operationalTaskID];
        }

        $dependencies = $DBConn->retrieve_db_table_rows(
            'tija_operational_task_dependencies',
            ['dependencyID', 'predecessorTaskID', 'successorTaskID', 'dependencyType'],
            $whereArr
        );

        if (!$dependencies) {
            return;
        }

        foreach ($dependencies as $dep) {
            $predecessor = OperationalTask::getInstance($dep['predecessorTaskID'], $DBConn);
            $successor = OperationalTask::getInstance($dep['successorTaskID'], $DBConn);

            if (!$predecessor || !$successor) {
                continue;
            }

            // Check if predecessor is completed
            $canStart = false;

            switch ($dep['dependencyType']) {
                case 'finish_to_start':
                    $canStart = $predecessor['status'] === 'completed';
                    break;

                case 'start_to_start':
                    $canStart = $predecessor['status'] === 'in_progress' || $predecessor['status'] === 'completed';
                    break;

                case 'finish_to_finish':
                    // Both must finish together
                    break;
            }

            // Update successor status
            if (!$canStart && $successor['status'] === 'pending') {
                OperationalTask::updateStatus($dep['successorTaskID'], 'blocked', $DBConn);
            } elseif ($canStart && $successor['status'] === 'blocked') {
                OperationalTask::updateStatus($dep['successorTaskID'], 'pending', $DBConn);
            }
        }
    }

    /**
     * Process event triggers
     *
     * @param string $eventName Event name
     * @param array $eventData Event data
     * @param object $DBConn Database connection
     * @return array Results
     */
    public static function processEventTriggers($eventName, $eventData, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $results = ['instancesCreated' => 0, 'errors' => []];

        // Get templates with matching trigger event
        $templates = OperationalTaskTemplate::listTemplates([
            'frequencyType' => 'event_driven',
            'triggerEvent' => $eventName,
            'isActive' => 'Y'
        ], $DBConn);

        if (!$templates) {
            return $results;
        }

        foreach ($templates as $template) {
            try {
                $dueDate = date('Y-m-d'); // Event-driven tasks are due immediately
                $templateID = is_object($template) ? $template->templateID : $template['templateID'];
                $instanceID = OperationalTask::instantiateFromTemplate($templateID, $dueDate, $DBConn);

                if ($instanceID) {
                    $results['instancesCreated']++;
                } else {
                    $results['errors'][] = "Failed to create instance for template {$templateID}";
                }
            } catch (Exception $e) {
                $templateID = is_object($template) ? ($template->templateID ?? 'unknown') : ($template['templateID'] ?? 'unknown');
                $results['errors'][] = "Error processing template {$templateID}: " . $e->getMessage();
            }
        }

        return $results;
    }
}

