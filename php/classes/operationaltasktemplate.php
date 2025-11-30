<?php
/**
 * Operational Task Template Class
 *
 * Manages operational task templates
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

class OperationalTaskTemplate {

    /**
     * Create template
     *
     * @param array $data Template data
     * @param object $DBConn Database connection
     * @return int|false Template ID or false
     */
    public static function createTemplate($data, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array(
            'templateCode', 'templateName', 'templateDescription',
            'processID', 'workflowID', 'sopID', 'functionalArea',
            'frequencyType', 'frequencyInterval', 'frequencyDayOfWeek',
            'frequencyDayOfMonth', 'frequencyMonthOfYear', 'triggerEvent',
            'estimatedDuration', 'assignmentRule', 'requiresApproval',
            'approverRoleID', 'requiresSOPReview', 'isActive',
            'processingMode', 'createdByID', 'functionalAreaOwnerID'
        );

        $data['isActive'] = $data['isActive'] ?? 'Y';
        $data['processingMode'] = $data['processingMode'] ?? 'cron';

        return $DBConn->insert_db_table_row('tija_operational_task_templates', $cols, $data);
    }

    /**
     * Get template
     *
     * @param int $templateID Template ID
     * @param object $DBConn Database connection
     * @return array|false Template data or false
     */
    public static function getTemplate($templateID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array(
            'templateID', 'templateCode', 'templateName', 'templateDescription',
            'processID', 'workflowID', 'sopID', 'functionalArea',
            'frequencyType', 'frequencyInterval', 'frequencyDayOfWeek',
            'frequencyDayOfMonth', 'frequencyMonthOfYear', 'triggerEvent',
            'estimatedDuration', 'assignmentRule', 'requiresApproval',
            'approverRoleID', 'requiresSOPReview', 'isActive',
            'processingMode', 'lastNotificationSent', 'createdByID',
            'functionalAreaOwnerID', 'DateAdded', 'LastUpdate'
        );

        return $DBConn->retrieve_db_table_rows('tija_operational_task_templates', $cols, ['templateID' => $templateID], true);
    }

    /**
     * List templates with filters
     *
     * @param array $filters Filter conditions
     * @param object $DBConn Database connection
     * @return array|false Templates or false
     */
    public static function listTemplates($filters = [], $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array(
            'templateID', 'templateCode', 'templateName', 'templateDescription',
            'processID', 'workflowID', 'sopID', 'functionalArea',
            'frequencyType', 'estimatedDuration', 'isActive', 'processingMode'
        );

        // Check if we need to handle IN clause for processingMode
        $hasInClause = isset($filters['processingMode']) &&
                      is_array($filters['processingMode']) &&
                      isset($filters['processingMode'][0]) &&
                      $filters['processingMode'][0] === 'IN';

        if ($hasInClause) {
            // Use custom SQL query for IN clause
            $modes = $filters['processingMode'][1];
            $placeholders = implode(',', array_fill(0, count($modes), '?'));

            // Build WHERE clause
            $whereConditions = ["Suspended = 'N'"];
            $params = [];

            // Add other filters
            foreach ($filters as $key => $value) {
                if ($key === 'processingMode') continue; // Skip, handled separately
                if ($key === 'Suspended') continue; // Already added

                $whereConditions[] = "{$key} = ?";
                $params[] = array($value, 's');
            }

            // Add IN clause
            $whereConditions[] = "processingMode IN ({$placeholders})";
            foreach ($modes as $mode) {
                $params[] = array($mode, 's');
            }

            $whereClause = implode(' AND ', $whereConditions);
            $colList = implode(', ', $cols);

            $query = "SELECT {$colList} FROM tija_operational_task_templates WHERE {$whereClause}";

            return $DBConn->retrieve_db_table_rows_custom($query, $params);
        } else {
            // Standard query without IN clause
            $whereArr = array_merge(['Suspended' => 'N'], $filters);
            return $DBConn->retrieve_db_table_rows('tija_operational_task_templates', $cols, $whereArr);
        }
    }

    /**
     * Activate template
     *
     * @param int $templateID Template ID
     * @param object $DBConn Database connection
     * @return bool Success
     */
    public static function activateTemplate($templateID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        return $DBConn->update_db_table_row('tija_operational_task_templates', ['isActive' => 'Y'], ['templateID' => $templateID]);
    }

    /**
     * Deactivate template
     *
     * @param int $templateID Template ID
     * @param object $DBConn Database connection
     * @return bool Success
     */
    public static function deactivateTemplate($templateID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        return $DBConn->update_db_table_row('tija_operational_task_templates', ['isActive' => 'N'], ['templateID' => $templateID]);
    }

    /**
     * Update template
     *
     * @param int $templateID Template ID
     * @param array $data Update data
     * @param object $DBConn Database connection
     * @return bool Success
     */
    public static function updateTemplate($templateID, $data, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        return $DBConn->update_db_table_row('tija_operational_task_templates', $data, ['templateID' => $templateID]);
    }
}

