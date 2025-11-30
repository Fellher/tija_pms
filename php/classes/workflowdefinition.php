<?php
/**
 * Workflow Definition Class
 *
 * Manages workflow definitions, steps, and transitions
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

class WorkflowDefinition {

    /**
     * Create workflow
     *
     * @param array $data Workflow data
     * @param object $DBConn Database connection
     * @return int|false Workflow ID or false
     */
    public static function createWorkflow($data, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array(
            'workflowCode', 'workflowName', 'workflowDescription',
            'processID', 'functionalArea', 'workflowType', 'version',
            'isActive', 'workflowDefinition', 'createdByID', 'functionalAreaOwnerID'
        );

        $data['version'] = $data['version'] ?? 1;
        $data['isActive'] = $data['isActive'] ?? 'Y';

        return $DBConn->insert_db_table_row('tija_workflows', $cols, $data);
    }

    /**
     * Get workflow with steps and transitions
     *
     * @param int $workflowID Workflow ID
     * @param object $DBConn Database connection
     * @return array|false Workflow data or false
     */
    public static function getWorkflow($workflowID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array(
            'workflowID', 'workflowCode', 'workflowName', 'workflowDescription',
            'processID', 'functionalArea', 'workflowType', 'version',
            'isActive', 'workflowDefinition', 'createdByID', 'functionalAreaOwnerID',
            'DateAdded', 'LastUpdate', 'Lapsed', 'Suspended'
        );

        $workflow = $DBConn->retrieve_db_table_rows('tija_workflows', $cols, ['workflowID' => $workflowID], true);

        if (!$workflow) {
            return false;
        }

        // Get steps
        $workflow['steps'] = self::getWorkflowSteps($workflowID, $DBConn);

        // Get transitions
        $workflow['transitions'] = self::getWorkflowTransitions($workflowID, $DBConn);

        return $workflow;
    }

    /**
     * Get workflow steps
     *
     * @param int $workflowID Workflow ID
     * @param object $DBConn Database connection
     * @return array|false Steps or false
     */
    public static function getWorkflowSteps($workflowID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array(
            'workflowStepID', 'workflowID', 'stepOrder', 'stepName',
            'stepDescription', 'stepType', 'assigneeType', 'assigneeRoleID',
            'assigneeEmployeeID', 'estimatedDuration', 'isMandatory', 'stepConfig'
        );

        $whereArr = ['workflowID' => $workflowID, 'Suspended' => 'N'];
        $rows = $DBConn->retrieve_db_table_rows('tija_workflow_steps', $cols, $whereArr);

        if ($rows && is_array($rows)) {
            // Sort by stepOrder
            usort($rows, function($a, $b) {
                return $a['stepOrder'] <=> $b['stepOrder'];
            });
        }

        return $rows ?: false;
    }

    /**
     * Get workflow transitions
     *
     * @param int $workflowID Workflow ID
     * @param object $DBConn Database connection
     * @return array|false Transitions or false
     */
    public static function getWorkflowTransitions($workflowID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array(
            'transitionID', 'workflowID', 'fromStepID', 'toStepID',
            'conditionType', 'conditionExpression', 'transitionLabel'
        );

        $whereArr = ['workflowID' => $workflowID];
        return $DBConn->retrieve_db_table_rows('tija_workflow_transitions', $cols, $whereArr);
    }

    /**
     * Add workflow step
     *
     * @param int $workflowID Workflow ID
     * @param array $stepData Step data
     * @param object $DBConn Database connection
     * @return int|false Step ID or false
     */
    public static function addWorkflowStep($workflowID, $stepData, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $stepData['workflowID'] = $workflowID;
        $cols = array(
            'workflowID', 'stepOrder', 'stepName', 'stepDescription',
            'stepType', 'assigneeType', 'assigneeRoleID', 'assigneeEmployeeID',
            'estimatedDuration', 'isMandatory', 'stepConfig'
        );

        return $DBConn->insert_db_table_row('tija_workflow_steps', $cols, $stepData);
    }

    /**
     * Update workflow step
     *
     * @param int $stepID Step ID
     * @param array $stepData Update data
     * @param object $DBConn Database connection
     * @return bool Success
     */
    public static function updateWorkflowStep($stepID, $stepData, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $whereArr = ['workflowStepID' => $stepID];
        return $DBConn->update_table('tija_workflow_steps', $stepData, $whereArr);
    }

    /**
     * Add workflow transition
     *
     * @param int $workflowID Workflow ID
     * @param array $transitionData Transition data
     * @param object $DBConn Database connection
     * @return int|false Transition ID or false
     */
    public static function addWorkflowTransition($workflowID, $transitionData, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $transitionData['workflowID'] = $workflowID;
        $cols = array(
            'workflowID', 'fromStepID', 'toStepID', 'conditionType',
            'conditionExpression', 'transitionLabel'
        );

        return $DBConn->insert_db_table_row('tija_workflow_transitions', $cols, $transitionData);
    }

    /**
     * Validate workflow structure
     *
     * @param int $workflowID Workflow ID
     * @param object $DBConn Database connection
     * @return array Validation result
     */
    public static function validateWorkflow($workflowID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $workflow = self::getWorkflow($workflowID, $DBConn);

        if (!$workflow) {
            return ['valid' => false, 'errors' => ['Workflow not found']];
        }

        $errors = [];
        $steps = $workflow['steps'] ?: [];
        $transitions = $workflow['transitions'] ?: [];

        // Check for orphaned steps (steps with no incoming or outgoing transitions)
        $stepIDs = array_column($steps, 'workflowStepID');
        $fromSteps = array_unique(array_column($transitions, 'fromStepID'));
        $toSteps = array_unique(array_column($transitions, 'toStepID'));

        foreach ($steps as $step) {
            $stepID = $step['workflowStepID'];
            if (!in_array($stepID, $fromSteps) && !in_array($stepID, $toSteps)) {
                $errors[] = "Step '{$step['stepName']}' has no transitions";
            }
        }

        // Check for invalid transitions
        foreach ($transitions as $transition) {
            if (!in_array($transition['fromStepID'], $stepIDs)) {
                $errors[] = "Transition from invalid step ID: {$transition['fromStepID']}";
            }
            if (!in_array($transition['toStepID'], $stepIDs)) {
                $errors[] = "Transition to invalid step ID: {$transition['toStepID']}";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Activate workflow
     *
     * @param int $workflowID Workflow ID
     * @param object $DBConn Database connection
     * @return bool Success
     */
    public static function activateWorkflow($workflowID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $validation = self::validateWorkflow($workflowID, $DBConn);
        if (!$validation['valid']) {
            return false;
        }

        return $DBConn->update_table('tija_workflows', ['isActive' => 'Y'], ['workflowID' => $workflowID]);
    }

    /**
     * Deactivate workflow
     *
     * @param int $workflowID Workflow ID
     * @param object $DBConn Database connection
     * @return bool Success
     */
    public static function deactivateWorkflow($workflowID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        return $DBConn->update_table('tija_workflows', ['isActive' => 'N'], ['workflowID' => $workflowID]);
    }

    /**
     * Get workflows by process
     *
     * @param int $processID Process ID
     * @param object $DBConn Database connection
     * @return array|false Workflows or false
     */
    public static function getWorkflowsByProcess($processID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array(
            'workflowID', 'workflowCode', 'workflowName', 'workflowDescription',
            'processID', 'functionalArea', 'workflowType', 'version', 'isActive'
        );

        $whereArr = ['processID' => $processID, 'isActive' => 'Y', 'Suspended' => 'N'];
        return $DBConn->retrieve_db_table_rows('tija_workflows', $cols, $whereArr);
    }

    /**
     * Get workflows by functional area
     *
     * @param string $functionalArea Functional area
     * @param object $DBConn Database connection
     * @return array|false Workflows or false
     */
    /**
     * Update workflow
     *
     * @param int $workflowID Workflow ID
     * @param array $data Update data
     * @param object $DBConn Database connection
     * @return bool Success
     */
    public static function updateWorkflow($workflowID, $data, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $whereArr = ['workflowID' => $workflowID];
        return $DBConn->update_table('tija_workflows', $data, $whereArr);
    }

    public static function getWorkflowsByFunctionalArea($functionalArea, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array(
            'workflowID', 'workflowCode', 'workflowName', 'workflowDescription',
            'processID', 'functionalArea', 'workflowType', 'version', 'isActive'
        );

        $whereArr = ['functionalArea' => $functionalArea, 'isActive' => 'Y', 'Suspended' => 'N'];
        return $DBConn->retrieve_db_table_rows('tija_workflows', $cols, $whereArr);
    }
}

