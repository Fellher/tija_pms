<?php
/**
 * Goal Hierarchy Class
 *
 * Manages organizational hierarchy and goal cascading
 *
 * @package    TIJA_PMS
 * @subpackage Goals
 * @version    1.0.0
 */

class GoalHierarchy {

    /**
     * Build/rebuild closure table for entity
     *
     * @param int $entityID Entity ID
     * @param string $hierarchyType Hierarchy type (Administrative/Functional)
     * @param object $DBConn Database connection
     * @return bool Success
     */
    public static function buildClosureTable($entityID = null, $hierarchyType = 'Administrative', $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        // Call stored procedure to rebuild closure table
        $sql = "CALL sp_build_administrative_closure()";
        $DBConn->query($sql);
        return $DBConn->execute();
    }

    /**
     * Get all descendants of an ancestor
     *
     * @param int $ancestorID Ancestor ID
     * @param int $depth Max depth (null = all)
     * @param string $hierarchyType Hierarchy type
     * @param object $DBConn Database connection
     * @return array|false Descendants array or false
     */
    public static function getDescendants($ancestorID, $depth = null, $hierarchyType = 'Administrative', $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $sql = "CALL sp_get_descendants(?, ?, ?)";
        $DBConn->query($sql);
        $DBConn->bind(1, $ancestorID, PDO::PARAM_INT);
        $DBConn->bind(2, $hierarchyType, PDO::PARAM_STR);
        $DBConn->bind(3, $depth, PDO::PARAM_INT);

        return $DBConn->resultSetArr();
    }

    /**
     * Get all ancestors of a descendant
     *
     * @param int $descendantID Descendant ID
     * @param string $hierarchyType Hierarchy type
     * @param int $depth Max depth (null = all)
     * @param object $DBConn Database connection
     * @return array|false Ancestors array or false
     */
    public static function getAncestors($descendantID, $hierarchyType = 'Administrative', $depth = null, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $sql = "CALL sp_get_ancestors(?, ?, ?)";
        $DBConn->query($sql);
        $DBConn->bind(1, $descendantID, PDO::PARAM_INT);
        $DBConn->bind(2, $hierarchyType, PDO::PARAM_STR);
        $DBConn->bind(3, $depth, PDO::PARAM_INT);

        return $DBConn->resultSetArr();
    }

    /**
     * Cascade goal - Strict mode (Mandatory adoption)
     *
     * @param string $parentUUID Parent goal UUID
     * @param array $targets Target entity/user IDs
     * @param object $DBConn Database connection
     * @return array Results array
     */
    public static function cascadeStrict($parentUUID, $targets, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        require_once 'goal.php';
        $parentGoal = Goal::getGoal($parentUUID, $DBConn);
        if (!$parentGoal) {
            return array('success' => false, 'error' => 'Parent goal not found');
        }

        $results = array();
        $cascadedByUserID = $_SESSION['userID'] ?? 1; // Get from session

        foreach ($targets as $target) {
            $targetType = $target['type']; // 'Entity' or 'User'
            $targetID = $target['id'];

            // Create exact copy of parent goal
            $goalData = array(
                'parentGoalUUID' => $parentUUID,
                'ownerEntityID' => $targetType === 'Entity' ? $targetID : null,
                'ownerUserID' => $targetType === 'User' ? $targetID : null,
                'goalType' => $parentGoal->goalType,
                'goalTitle' => $parentGoal->goalTitle,
                'goalDescription' => $parentGoal->goalDescription,
                'propriety' => $parentGoal->propriety,
                'weight' => $parentGoal->weight,
                'progressMetric' => $parentGoal->progressMetric,
                'evaluatorConfig' => $parentGoal->evaluatorConfig,
                'visibility' => $parentGoal->visibility,
                'cascadeMode' => 'Strict',
                'startDate' => $parentGoal->startDate,
                'endDate' => $parentGoal->endDate,
                'status' => 'Active', // Auto-activated in Strict mode
                'LastUpdatedByID' => $cascadedByUserID
            );

            // Copy type-specific data
            if ($parentGoal->goalType === 'OKR' && isset($parentGoal->okrData)) {
                $goalData['okrData'] = (array)$parentGoal->okrData;
            } elseif ($parentGoal->goalType === 'KPI' && isset($parentGoal->kpiData)) {
                $goalData['kpiData'] = (array)$parentGoal->kpiData;
            }

            $childUUID = Goal::createGoal($goalData, $DBConn);

            if ($childUUID) {
                // Log cascade
                $logData = array(
                    'parentGoalUUID' => $parentUUID,
                    'childGoalUUID' => $childUUID,
                    'cascadeMode' => 'Strict',
                    'targetEntityID' => $targetType === 'Entity' ? $targetID : null,
                    'targetUserID' => $targetType === 'User' ? $targetID : null,
                    'cascadedByUserID' => $cascadedByUserID,
                    'status' => 'AutoCreated'
                );
                $DBConn->insert_data('tija_goal_cascade_log', $logData);

                $results[] = array(
                    'targetID' => $targetID,
                    'targetType' => $targetType,
                    'goalUUID' => $childUUID,
                    'status' => 'Created'
                );
            }
        }

        return array('success' => true, 'results' => $results);
    }

    /**
     * Cascade goal - Aligned mode (Interpretive adoption)
     *
     * @param string $parentUUID Parent goal UUID
     * @param array $targets Target entity/user IDs
     * @param object $DBConn Database connection
     * @return array Results array
     */
    public static function cascadeAligned($parentUUID, $targets, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        require_once 'goal.php';
        $parentGoal = Goal::getGoal($parentUUID, $DBConn);
        if (!$parentGoal) {
            return array('success' => false, 'error' => 'Parent goal not found');
        }

        $results = array();
        $cascadedByUserID = $_SESSION['userID'] ?? 1;

        foreach ($targets as $target) {
            $targetType = $target['type'];
            $targetID = $target['id'];

            // Create pending goal that requires target to create their own
            $logData = array(
                'parentGoalUUID' => $parentUUID,
                'childGoalUUID' => null, // Will be set when target creates goal
                'cascadeMode' => 'Aligned',
                'targetEntityID' => $targetType === 'Entity' ? $targetID : null,
                'targetUserID' => $targetType === 'User' ? $targetID : null,
                'cascadedByUserID' => $cascadedByUserID,
                'status' => 'Pending'
            );
            $logID = $DBConn->insert_data('tija_goal_cascade_log', $logData);

            $results[] = array(
                'targetID' => $targetID,
                'targetType' => $targetType,
                'logID' => $logID,
                'status' => 'Pending',
                'message' => 'Target must create aligned goal'
            );
        }

        return array('success' => true, 'results' => $results);
    }

    /**
     * Cascade goal - Hybrid mode (Matrix cascade)
     *
     * @param string $parentUUID Parent goal UUID
     * @param array $functionalFilter Functional filter criteria
     * @param object $DBConn Database connection
     * @return array Results array
     */
    public static function cascadeHybrid($parentUUID, $functionalFilter, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        require_once 'goal.php';
        $parentGoal = Goal::getGoal($parentUUID, $DBConn);
        if (!$parentGoal) {
            return array('success' => false, 'error' => 'Parent goal not found');
        }

        // Find users matching functional criteria
        require_once 'employee.php';
        $whereArr = array('Lapsed' => 'N', 'Suspended' => 'N');

        if (isset($functionalFilter['jobTitleID'])) {
            $whereArr['jobTitleID'] = $functionalFilter['jobTitleID'];
        }
        if (isset($functionalFilter['departmentID'])) {
            $whereArr['departmentID'] = $functionalFilter['departmentID'];
        }
        if (isset($functionalFilter['jobCategoryID'])) {
            $whereArr['jobCategoryID'] = $functionalFilter['jobCategoryID'];
        }

        $employees = Employee::employees($whereArr, false, $DBConn);
        if (!$employees) {
            return array('success' => false, 'error' => 'No employees found matching criteria');
        }

        $results = array();
        $cascadedByUserID = $_SESSION['userID'] ?? 1;

        foreach ($employees as $employee) {
            // Create goal for employee
            $goalData = array(
                'parentGoalUUID' => $parentUUID,
                'ownerUserID' => $employee->ID,
                'goalType' => $parentGoal->goalType,
                'goalTitle' => $parentGoal->goalTitle,
                'goalDescription' => $parentGoal->goalDescription,
                'propriety' => $parentGoal->propriety,
                'weight' => $parentGoal->weight,
                'visibility' => 'Private',
                'cascadeMode' => 'Hybrid',
                'startDate' => $parentGoal->startDate,
                'endDate' => $parentGoal->endDate,
                'status' => 'Active',
                'LastUpdatedByID' => $cascadedByUserID
            );

            $childUUID = Goal::createGoal($goalData, $DBConn);

            if ($childUUID) {
                // Create matrix assignment
                require_once 'goalmatrix.php';
                GoalMatrix::assignMatrixGoal($childUUID, $employee->ID, $cascadedByUserID, $DBConn);

                // Log cascade
                $logData = array(
                    'parentGoalUUID' => $parentUUID,
                    'childGoalUUID' => $childUUID,
                    'cascadeMode' => 'Hybrid',
                    'targetUserID' => $employee->ID,
                    'cascadedByUserID' => $cascadedByUserID,
                    'status' => 'AutoCreated'
                );
                $DBConn->insert_data('tija_goal_cascade_log', $logData);

                $results[] = array(
                    'employeeID' => $employee->ID,
                    'goalUUID' => $childUUID,
                    'status' => 'Created'
                );
            }
        }

        return array('success' => true, 'results' => $results);
    }

    /**
     * Cascade goal (wrapper that calls appropriate method)
     *
     * @param string $parentUUID Parent goal UUID
     * @param string $mode Cascade mode
     * @param array $targets Targets or filter
     * @param object $DBConn Database connection
     * @return array Results
     */
    public static function cascadeGoal($parentUUID, $mode, $targets, $DBConn = null) {
        switch ($mode) {
            case 'Strict':
                return self::cascadeStrict($parentUUID, $targets, $DBConn);
            case 'Aligned':
                return self::cascadeAligned($parentUUID, $targets, $DBConn);
            case 'Hybrid':
                return self::cascadeHybrid($parentUUID, $targets, $DBConn);
            default:
                return array('success' => false, 'error' => 'Invalid cascade mode');
        }
    }

    /**
     * Get recent cascade log entries
     *
     * @param int $limit Max number of records
     * @param object $DBConn Database connection
     * @return array|false Log entries or false
     */
    public static function getCascadeLog($limit = 50, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $sql = "SELECT cl.*, g1.goalTitle as parentTitle, g2.goalTitle as childTitle,
                CONCAT(p1.FirstName, ' ', p1.Surname) as cascadedByName,
                CONCAT(p2.FirstName, ' ', p2.Surname) as respondedByName
         FROM tija_goal_cascade_log cl
         LEFT JOIN tija_goals g1 ON cl.parentGoalUUID = g1.goalUUID
         LEFT JOIN tija_goals g2 ON cl.childGoalUUID = g2.goalUUID
         LEFT JOIN people p1 ON cl.cascadedByUserID = p1.ID
         LEFT JOIN people p2 ON cl.respondedByUserID = p2.ID
         ORDER BY cl.cascadeDate DESC
         LIMIT " . (int)$limit;

        return $DBConn->retrieve_db_table_rows_custom($sql, array());
    }

    /**
     * Get cascade path (visualize cascade chain)
     *
     * @param string $goalUUID Goal UUID
     * @param object $DBConn Database connection
     * @return array Cascade path
     */
    public static function getCascadePath($goalUUID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        require_once 'goal.php';
        $path = array();
        $currentUUID = $goalUUID;

        // Walk up the parent chain
        while ($currentUUID) {
            $goal = Goal::getGoal($currentUUID, $DBConn);
            if (!$goal || !is_object($goal)) {
                break;
            }

            // Normalise missing properties safely
            $goalUUIDVal = isset($goal->goalUUID) ? $goal->goalUUID : $currentUUID;
            $goalTitleVal = isset($goal->goalTitle) ? $goal->goalTitle : '';
            $goalTypeVal = isset($goal->goalType) ? $goal->goalType : '';
            $ownerEntityIDVal = isset($goal->ownerEntityID) ? $goal->ownerEntityID : null;
            $ownerUserIDVal = isset($goal->ownerUserID) ? $goal->ownerUserID : null;
            $statusVal = isset($goal->status) ? $goal->status : '';
            $parentUUIDVal = isset($goal->parentGoalUUID) ? $goal->parentGoalUUID : null;

            $path[] = array(
                'goalUUID' => $goalUUIDVal,
                'goalTitle' => $goalTitleVal,
                'goalType' => $goalTypeVal,
                'ownerEntityID' => $ownerEntityIDVal,
                'ownerUserID' => $ownerUserIDVal,
                'status' => $statusVal
            );

            if (!$parentUUIDVal || $parentUUIDVal === $currentUUID) {
                break;
            }

            $currentUUID = $parentUUIDVal;
        }

        return array_reverse($path); // Return from root to current
    }
}

