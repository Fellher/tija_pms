<?php
/**
 * Goal Class
 *
 * Core goal management class for Strategic Goals, OKRs, and KPIs
 *
 * @package    TIJA_PMS
 * @subpackage Goals
 * @version    1.0.0
 */

class Goal {

    /**
     * Generate UUID v4
     *
     * @return string UUID
     */
    private static function generateUUID() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Create a new goal
     *
     * @param array $data Goal data
     * @param object $DBConn Database connection
     * @return string|false Goal UUID or false on failure
     */
    public static function createGoal($data, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        // Generate UUID
        $goalUUID = self::generateUUID();

        // Prepare goal data
        $goalData = array(
            'goalUUID' => $goalUUID,
            'parentGoalUUID' => $data['parentGoalUUID'] ?? null,
            'ownerEntityID' => $data['ownerEntityID'] ?? null,
            'ownerUserID' => $data['ownerUserID'] ?? null,
            'libraryRefID' => $data['libraryRefID'] ?? null,
            'goalType' => $data['goalType'] ?? 'Strategic',
            'goalTitle' => $data['goalTitle'] ?? '',
            'goalDescription' => $data['goalDescription'] ?? null,
            'propriety' => $data['propriety'] ?? 'Medium',
            'weight' => $data['weight'] ?? 0.0000,
            'progressMetric' => isset($data['progressMetric']) ? json_encode($data['progressMetric']) : null,
            'evaluatorConfig' => isset($data['evaluatorConfig']) ? json_encode($data['evaluatorConfig']) : null,
            'jurisdictionID' => $data['jurisdictionID'] ?? null,
            'visibility' => $data['visibility'] ?? 'Private',
            'cascadeMode' => $data['cascadeMode'] ?? 'None',
            'startDate' => $data['startDate'] ?? date('Y-m-d'),
            'endDate' => $data['endDate'] ?? date('Y-m-d', strtotime('+1 year')),
            'status' => $data['status'] ?? 'Draft',
            'completionPercentage' => $data['completionPercentage'] ?? 0.00,
            'sysStartTime' => date('Y-m-d H:i:s'),
            'LastUpdatedByID' => $data['LastUpdatedByID'] ?? null
        );

        // Insert goal
        $result = $DBConn->insert_data('tija_goals', $goalData);

        if ($result) {
            // Create type-specific records
            if ($data['goalType'] === 'OKR' && isset($data['okrData'])) {
                self::createOKR($goalUUID, $data['okrData'], $DBConn);
            } elseif ($data['goalType'] === 'KPI' && isset($data['kpiData'])) {
                self::createKPI($goalUUID, $data['kpiData'], $DBConn);
            }

            // Create default evaluation weights if provided
            if (isset($data['evaluatorWeights'])) {
                self::setEvaluationWeights($goalUUID, $data['evaluatorWeights'], $DBConn);
            }

            return $goalUUID;
        }

        return false;
    }

    /**
     * Create OKR-specific data
     *
     * @param string $goalUUID Goal UUID
     * @param array $okrData OKR data
     * @param object $DBConn Database connection
     * @return int|false OKR ID or false
     */
    private static function createOKR($goalUUID, $okrData, $DBConn) {
        $okrDataInsert = array(
            'goalUUID' => $goalUUID,
            'objective' => $okrData['objective'] ?? '',
            'keyResults' => json_encode($okrData['keyResults'] ?? array()),
            'alignmentDirection' => $okrData['alignmentDirection'] ?? 'TopDown',
            'LastUpdatedByID' => $okrData['LastUpdatedByID'] ?? null
        );

        return $DBConn->insert_data('tija_goal_okrs', $okrDataInsert);
    }

    /**
     * Create KPI-specific data
     *
     * @param string $goalUUID Goal UUID
     * @param array $kpiData KPI data
     * @param object $DBConn Database connection
     * @return int|false KPI ID or false
     */
    private static function createKPI($goalUUID, $kpiData, $DBConn) {
        $kpiDataInsert = array(
            'goalUUID' => $goalUUID,
            'kpiName' => $kpiData['kpiName'] ?? '',
            'kpiDescription' => $kpiData['kpiDescription'] ?? null,
            'measurementFrequency' => $kpiData['measurementFrequency'] ?? 'Monthly',
            'baselineValue' => $kpiData['baselineValue'] ?? null,
            'targetValue' => $kpiData['targetValue'] ?? 0,
            'currentValue' => $kpiData['currentValue'] ?? null,
            'unit' => $kpiData['unit'] ?? null,
            'currencyCode' => $kpiData['currencyCode'] ?? null,
            'reportingRate' => $kpiData['reportingRate'] ?? null,
            'isPerpetual' => $kpiData['isPerpetual'] ?? 'N',
            'LastUpdatedByID' => $kpiData['LastUpdatedByID'] ?? null
        );

        return $DBConn->insert_data('tija_goal_kpis', $kpiDataInsert);
    }

    /**
     * Update goal
     *
     * @param string $goalUUID Goal UUID
     * @param array $data Update data
     * @param object $DBConn Database connection
     * @return bool Success
     */
    public static function updateGoal($goalUUID, $data, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        // Close current temporal version
        $updateData = array(
            'sysEndTime' => date('Y-m-d H:i:s')
        );
        $where = array('goalUUID' => $goalUUID, 'sysEndTime' => 'NULL');
        $DBConn->update_table('tija_goals', $updateData, $where);

        // Create new version
        $goal = self::getGoal($goalUUID, $DBConn);
        if (!$goal) {
            return false;
        }

        // Merge with new data
        $newGoalData = array_merge((array)$goal, $data);
        $newGoalData['goalUUID'] = self::generateUUID(); // New UUID for new version
        $newGoalData['sysStartTime'] = date('Y-m-d H:i:s');
        $newGoalData['sysEndTime'] = null;

        // Handle JSON fields
        if (isset($data['progressMetric']) && is_array($data['progressMetric'])) {
            $newGoalData['progressMetric'] = json_encode($data['progressMetric']);
        }
        if (isset($data['evaluatorConfig']) && is_array($data['evaluatorConfig'])) {
            $newGoalData['evaluatorConfig'] = json_encode($data['evaluatorConfig']);
        }

        return self::createGoal($newGoalData, $DBConn) !== false;
    }

    /**
     * Delete goal (soft delete)
     *
     * @param string $goalUUID Goal UUID
     * @param object $DBConn Database connection
     * @return bool Success
     */
    public static function deleteGoal($goalUUID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        // Check if Critical goal - requires approval
        $goal = self::getGoal($goalUUID, $DBConn);
        if ($goal && $goal->propriety === 'Critical') {
            // For Critical goals, require L+2 approval - this would be handled in the API layer
            // For now, just soft delete
        }

        $updateData = array('Lapsed' => 'Y');
        $where = array('goalUUID' => $goalUUID);

        return $DBConn->update_table('tija_goals', $updateData, $where);
    }

    /**
     * Get goal by UUID
     *
     * @param string $goalUUID Goal UUID
     * @param object $DBConn Database connection
     * @return object|false Goal data or false
     */
    public static function getGoal($goalUUID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array(
            'goalUUID', 'parentGoalUUID', 'ownerEntityID', 'ownerUserID', 'libraryRefID',
            'goalType', 'goalTitle', 'goalDescription', 'propriety', 'weight',
            'progressMetric', 'evaluatorConfig', 'jurisdictionID', 'visibility',
            'cascadeMode', 'startDate', 'endDate', 'status', 'completionPercentage',
            'sysStartTime', 'sysEndTime', 'DateAdded', 'LastUpdate', 'LastUpdatedByID',
            'Lapsed', 'Suspended'
        );

        $where = array('goalUUID' => $goalUUID, 'sysEndTime' => 'NULL', 'Lapsed' => 'N');
        $goal = $DBConn->retrieve_db_table_rows('tija_goals', $cols, $where, true);

        if (!$goal) {
            return false;
        }

        // Ensure we are working with an object
        if (is_array($goal)) {
            $goal = (object) $goal;
        }

        // Decode JSON fields safely
        if (isset($goal->progressMetric) && $goal->progressMetric) {
            $goal->progressMetric = json_decode($goal->progressMetric, true);
        }
        if (isset($goal->evaluatorConfig) && $goal->evaluatorConfig) {
            $goal->evaluatorConfig = json_decode($goal->evaluatorConfig, true);
        }

        // Get type-specific data
        if (isset($goal->goalType) && $goal->goalType === 'OKR') {
            $goal->okrData = self::getOKR($goalUUID, $DBConn);
        } elseif (isset($goal->goalType) && $goal->goalType === 'KPI') {
            $goal->kpiData = self::getKPI($goalUUID, $DBConn);
        }

        return $goal;
    }

    /**
     * Get OKR data
     *
     * @param string $goalUUID Goal UUID
     * @param object $DBConn Database connection
     * @return object|false OKR data or false
     */
    private static function getOKR($goalUUID, $DBConn) {
        $cols = array('okrID', 'goalUUID', 'objective', 'keyResults', 'alignmentDirection');
        $where = array('goalUUID' => $goalUUID);
        $okr = $DBConn->retrieve_db_table_rows('tija_goal_okrs', $cols, $where, true);

        if ($okr && $okr->keyResults) {
            $okr->keyResults = json_decode($okr->keyResults, true);
        }

        return $okr;
    }

    /**
     * Get KPI data
     *
     * @param string $goalUUID Goal UUID
     * @param object $DBConn Database connection
     * @return object|false KPI data or false
     */
    private static function getKPI($goalUUID, $DBConn) {
        $cols = array(
            'kpiID', 'goalUUID', 'kpiName', 'kpiDescription', 'measurementFrequency',
            'baselineValue', 'targetValue', 'currentValue', 'unit', 'currencyCode',
            'reportingRate', 'isPerpetual'
        );
        $where = array('goalUUID' => $goalUUID);
        return $DBConn->retrieve_db_table_rows('tija_goal_kpis', $cols, $where, true);
    }

    /**
     * Get goals by owner
     *
     * @param int $ownerID Owner ID
     * @param string $ownerType 'Entity' or 'User'
     * @param array $filters Additional filters
     * @param object $DBConn Database connection
     * @return array|false Goals array or false
     */
    public static function getGoalsByOwner($ownerID, $ownerType, $filters = array(), $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array(
            'goalUUID', 'parentGoalUUID', 'ownerEntityID', 'ownerUserID', 'goalType',
            'goalTitle', 'goalDescription', 'propriety', 'weight', 'status',
            'startDate', 'endDate', 'completionPercentage', 'visibility'
        );

        $where = array(
            'sysEndTime' => 'NULL',
            'Lapsed' => 'N'
        );

        if ($ownerType === 'Entity') {
            $where['ownerEntityID'] = $ownerID;
        } elseif ($ownerType === 'User') {
            $where['ownerUserID'] = $ownerID;
        }

        // Apply additional filters
        if (isset($filters['status'])) {
            $where['status'] = $filters['status'];
        }
        if (isset($filters['goalType'])) {
            $where['goalType'] = $filters['goalType'];
        }
        if (isset($filters['propriety'])) {
            $where['propriety'] = $filters['propriety'];
        }

        return $DBConn->retrieve_db_table_rows('tija_goals', $cols, $where, false);
    }

    /**
     * Cascade goal to child entities/users
     *
     * @param string $parentUUID Parent goal UUID
     * @param string $mode Cascade mode (Strict, Aligned, Hybrid)
     * @param array $targets Target entities/users
     * @param object $DBConn Database connection
     * @return array Results array
     */
    public static function cascadeGoal($parentUUID, $mode, $targets, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        require_once 'goalhierarchy.php';
        return GoalHierarchy::cascadeGoal($parentUUID, $mode, $targets, $DBConn);
    }

    /**
     * Get goals that can be used as cascade parents
     *
     * @param object $DBConn Database connection
     * @return array|false List of goals or false
     */
    public static function getCascadeableGoals($DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array('goalUUID', 'goalTitle', 'goalType', 'cascadeMode', 'status');
        $where = array(
            'cascadeMode' => array('Strict', 'Aligned', 'Hybrid'),
            'status' => 'Active',
            'sysEndTime' => 'NULL',
            'Lapsed' => 'N'
        );

        return $DBConn->retrieve_db_table_rows('tija_goals', $cols, $where, false);
    }

    /**
     * Calculate goal score
     *
     * @param string $goalUUID Goal UUID
     * @param object $DBConn Database connection
     * @return float|false Score or false
     */
    public static function calculateScore($goalUUID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        require_once 'goalevaluation.php';
        return GoalEvaluation::calculateWeightedScore($goalUUID, $DBConn);
    }

    /**
     * Validate that goal weights sum to 100%
     *
     * @param array $goals Array of goal UUIDs or goal objects with weight
     * @param object $DBConn Database connection
     * @return bool True if valid
     */
    public static function validateWeightSum($goals, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $totalWeight = 0.0;

        foreach ($goals as $goal) {
            if (is_string($goal)) {
                // UUID provided, fetch goal
                $goalObj = self::getGoal($goal, $DBConn);
                if ($goalObj) {
                    $totalWeight += (float)$goalObj->weight;
                }
            } else {
                // Goal object provided
                $totalWeight += (float)($goal->weight ?? 0);
            }
        }

        // Allow small floating point tolerance
        return abs($totalWeight - 1.0) < 0.0001;
    }

    /**
     * Set evaluation weights for a goal
     *
     * @param string $goalUUID Goal UUID
     * @param array $weights Array of ['role' => 'Manager', 'weight' => 0.5]
     * @param object $DBConn Database connection
     * @return bool Success
     */
    public static function setEvaluationWeights($goalUUID, $weights, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        foreach ($weights as $weightConfig) {
            $weightData = array(
                'goalUUID' => $goalUUID,
                'evaluatorRole' => $weightConfig['role'],
                'weight' => $weightConfig['weight'],
                'isDefault' => $weightConfig['isDefault'] ?? 'N'
            );

            // Check if exists
            $existing = $DBConn->retrieve_db_table_rows(
                'tija_goal_evaluation_weights',
                array('weightID'),
                array('goalUUID' => $goalUUID, 'evaluatorRole' => $weightConfig['role']),
                true
            );

            if ($existing) {
                $DBConn->update_table(
                    'tija_goal_evaluation_weights',
                    array('weight' => $weightConfig['weight']),
                    array('goalUUID' => $goalUUID, 'evaluatorRole' => $weightConfig['role'])
                );
            } else {
                $DBConn->insert_data('tija_goal_evaluation_weights', $weightData);
            }
        }

        return true;
    }
}

