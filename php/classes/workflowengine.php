<?php
/**
 * Workflow Engine Class
 *
 * Executes workflow instances
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

class WorkflowEngine {

    /**
     * Start workflow
     *
     * @param int $workflowID Workflow ID
     * @param array $contextData Context data
     * @param object $DBConn Database connection
     * @return int|false Instance ID or false
     */
    public static function startWorkflow($workflowID, $contextData = [], $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        require_once 'workflowdefinition.php';
        $workflow = WorkflowDefinition::getWorkflow($workflowID, $DBConn);

        if (!$workflow || $workflow['isActive'] !== 'Y') {
            return false;
        }

        // Get first step
        $steps = $workflow['steps'] ?: [];
        if (empty($steps)) {
            return false;
        }

        $firstStep = $steps[0];

        // Create workflow instance
        $instanceData = [
            'workflowID' => $workflowID,
            'operationalTaskID' => $contextData['operationalTaskID'] ?? null,
            'currentStepID' => $firstStep['workflowStepID'],
            'status' => 'pending',
            'startedDate' => date('Y-m-d H:i:s'),
            'instanceData' => json_encode($contextData)
        ];

        $cols = array('workflowID', 'operationalTaskID', 'currentStepID', 'status', 'startedDate', 'instanceData');
        $instanceID = $DBConn->insert_db_table_row('tija_workflow_instances', $cols, $instanceData);

        if ($instanceID) {
            // Update status to in_progress
            self::updateInstanceStatus($instanceID, 'in_progress', $DBConn);
        }

        return $instanceID;
    }

    /**
     * Execute workflow step
     *
     * @param int $instanceID Instance ID
     * @param int $stepID Step ID
     * @param array $stepData Step execution data
     * @param object $DBConn Database connection
     * @return bool Success
     */
    public static function executeStep($instanceID, $stepID, $stepData = [], $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $instance = self::getWorkflowInstance($instanceID, $DBConn);
        if (!$instance || $instance['currentStepID'] != $stepID) {
            return false;
        }

        require_once 'workflowdefinition.php';
        $step = WorkflowDefinition::getWorkflowSteps($instance['workflowID'], $DBConn);

        // Find the step
        $currentStep = null;
        foreach ($step as $s) {
            if ($s['workflowStepID'] == $stepID) {
                $currentStep = $s;
                break;
            }
        }

        if (!$currentStep) {
            return false;
        }

        // Execute step based on type
        $success = true;
        switch ($currentStep['stepType']) {
            case 'task':
                // Task step - mark as completed
                break;

            case 'approval':
                // Approval step - check if approved
                if (!isset($stepData['approved']) || !$stepData['approved']) {
                    $success = false;
                }
                break;

            case 'decision':
                // Decision step - evaluate condition
                break;

            case 'notification':
                // Notification step - send notification
                break;

            case 'automation':
                // Automation step - trigger automation
                break;
        }

        if ($success) {
            // Transition to next step
            return self::transitionToNext($instanceID, null, $DBConn);
        }

        return false;
    }

    /**
     * Transition to next step
     *
     * @param int $instanceID Instance ID
     * @param int|null $transitionID Transition ID (null for auto)
     * @param object $DBConn Database connection
     * @return bool Success
     */
    public static function transitionToNext($instanceID, $transitionID = null, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $instance = self::getWorkflowInstance($instanceID, $DBConn);
        if (!$instance) {
            return false;
        }

        require_once 'workflowdefinition.php';
        $transitions = WorkflowDefinition::getWorkflowTransitions($instance['workflowID'], $DBConn);

        // Find transitions from current step
        $availableTransitions = array_filter($transitions, function($t) use ($instance) {
            return $t['fromStepID'] == $instance['currentStepID'];
        });

        if (empty($availableTransitions)) {
            // No more transitions - workflow complete
            return self::completeWorkflow($instanceID, $DBConn);
        }

        // Use specified transition or first available
        $transition = $transitionID
            ? array_filter($availableTransitions, function($t) use ($transitionID) {
                return $t['transitionID'] == $transitionID;
            })[0] ?? reset($availableTransitions)
            : reset($availableTransitions);

        // Evaluate condition if needed
        if ($transition['conditionType'] !== 'always') {
            $conditionMet = self::evaluateConditions($instanceID, $transition['conditionExpression'], $DBConn);
            if (!$conditionMet) {
                return false;
            }
        }

        // Update instance to next step
        return $DBConn->update_db_table_row(
            'tija_workflow_instances',
            ['currentStepID' => $transition['toStepID']],
            ['instanceID' => $instanceID]
        );
    }

    /**
     * Get workflow instance
     *
     * @param int $instanceID Instance ID
     * @param object $DBConn Database connection
     * @return array|false Instance data or false
     */
    public static function getWorkflowInstance($instanceID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array(
            'instanceID', 'workflowID', 'operationalTaskID', 'currentStepID',
            'status', 'startedDate', 'completedDate', 'instanceData'
        );

        return $DBConn->retrieve_db_table_rows('tija_workflow_instances', $cols, ['instanceID' => $instanceID], true);
    }

    /**
     * Evaluate conditions
     *
     * @param int $instanceID Instance ID
     * @param array $conditionExpression Condition expression
     * @param object $DBConn Database connection
     * @return bool Condition met
     */
    public static function evaluateConditions($instanceID, $conditionExpression, $DBConn = null) {
        // Simplified condition evaluation
        // In production, this would be more sophisticated
        if (empty($conditionExpression)) {
            return true;
        }

        // Placeholder - would implement actual condition logic
        return true;
    }

    /**
     * Complete workflow
     *
     * @param int $instanceID Instance ID
     * @param object $DBConn Database connection
     * @return bool Success
     */
    public static function completeWorkflow($instanceID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        return $DBConn->update_db_table_row(
            'tija_workflow_instances',
            [
                'status' => 'completed',
                'completedDate' => date('Y-m-d H:i:s')
            ],
            ['instanceID' => $instanceID]
        );
    }

    /**
     * Update instance status
     *
     * @param int $instanceID Instance ID
     * @param string $status Status
     * @param object $DBConn Database connection
     * @return bool Success
     */
    private static function updateInstanceStatus($instanceID, $status, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        return $DBConn->update_db_table_row(
            'tija_workflow_instances',
            ['status' => $status],
            ['instanceID' => $instanceID]
        );
    }
}

