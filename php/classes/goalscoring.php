<?php
/**
 * Goal Scoring Class
 *
 * Scoring and reporting calculations
 *
 * @package    TIJA_PMS
 * @subpackage Goals
 * @version    1.0.0
 */

class GoalScoring {

    /**
     * Calculate entity score
     *
     * @param int $entityID Entity ID
     * @param string $period Period (e.g., '2024-Q1')
     * @param object $DBConn Database connection
     * @return float|false Entity score or false
     */
    public static function calculateEntityScore($entityID, $period = null, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        require_once 'goal.php';

        // Get all active goals for entity
        $goals = Goal::getGoalsByOwner($entityID, 'Entity', array('status' => 'Active'), $DBConn);
        if (!$goals || count($goals) === 0) {
            return false;
        }

        $totalWeightedScore = 0.0;
        $totalWeight = 0.0;

        foreach ($goals as $goal) {
            // Get latest score
            $score = self::getLatestScore($goal->goalUUID, $DBConn);
            if ($score !== false) {
                $weight = (float)$goal->weight;
                $totalWeightedScore += $score * $weight;
                $totalWeight += $weight;
            }
        }

        if ($totalWeight > 0) {
            return $totalWeightedScore / $totalWeight;
        }

        return false;
    }

    /**
     * Normalize currency value
     *
     * @param float $value Value to normalize
     * @param string $currency Source currency code
     * @param string $targetCurrency Target currency code
     * @param string $rateType Rate type ('Budget' or 'Spot')
     * @param object $DBConn Database connection
     * @return float|false Normalized value or false
     */
    public static function normalizeCurrency($value, $currency, $targetCurrency, $rateType = 'Budget', $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        if ($currency === $targetCurrency) {
            return $value;
        }

        // Get current rate
        $where = array(
            'fromCurrency' => $currency,
            'toCurrency' => $targetCurrency,
            'rateType' => $rateType,
            'expiryDate' => 'NULL'
        );

        $rate = $DBConn->retrieve_db_table_rows(
            'tija_goal_currency_rates',
            array('budgetRate', 'spotRate'),
            $where,
            true
        );

        if (!$rate) {
            return false;
        }

        $rateValue = $rateType === 'Budget' ? (float)$rate->budgetRate : (float)$rate->spotRate;
        return $value * $rateValue;
    }

    /**
     * Calculate hierarchical score (roll-up)
     *
     * @param int $rootEntityID Root entity ID
     * @param array $weights Weights configuration
     * @param object $DBConn Database connection
     * @return float|false Hierarchical score or false
     */
    public static function calculateHierarchicalScore($rootEntityID, $weights = null, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        require_once 'goalhierarchy.php';

        // Get all descendant entities
        $descendants = GoalHierarchy::getDescendants($rootEntityID, null, 'Administrative', $DBConn);
        if (!$descendants) {
            return false;
        }

        $entityScores = array();
        $entityWeights = array();

        // Calculate score for each entity
        foreach ($descendants as $descendant) {
            if ($descendant['descendant_type'] === 'Entity') {
                $entityID = $descendant['descendant_id'];
                $score = self::calculateEntityScore($entityID, null, $DBConn);
                if ($score !== false) {
                    $entityScores[$entityID] = $score;
                    // Use depth-based weight or provided weights
                    $entityWeights[$entityID] = $weights[$entityID] ?? (1.0 / count($entityScores));
                }
            }
        }

        // Calculate weighted average
        $totalWeightedScore = 0.0;
        $totalWeight = 0.0;

        foreach ($entityScores as $entityID => $score) {
            $weight = $entityWeights[$entityID];
            $totalWeightedScore += $score * $weight;
            $totalWeight += $weight;
        }

        if ($totalWeight > 0) {
            return $totalWeightedScore / $totalWeight;
        }

        return false;
    }

    /**
     * Generate snapshot for goal
     *
     * @param string $goalUUID Goal UUID
     * @param object $DBConn Database connection
     * @return int|false Snapshot ID or false
     */
    public static function generateSnapshot($goalUUID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        require_once 'goal.php';
        $goal = Goal::getGoal($goalUUID, $DBConn);
        if (!$goal) {
            return false;
        }

        // Get current score
        $currentScore = self::getLatestScore($goalUUID, $DBConn);

        // Get progress metrics
        $targetValue = null;
        $actualValue = null;
        if ($goal->progressMetric) {
            $targetValue = $goal->progressMetric['target'] ?? null;
            $actualValue = $goal->progressMetric['current'] ?? null;
        }

        // Calculate completion percentage
        $completionPercentage = $goal->completionPercentage ?? 0.00;

        // Determine status
        $status = 'Active';
        if ($completionPercentage >= 100) {
            $status = 'Completed';
        } elseif ($completionPercentage < 50 && $goal->endDate < date('Y-m-d')) {
            $status = 'Behind';
        } elseif ($completionPercentage < 70) {
            $status = 'AtRisk';
        } else {
            $status = 'OnTrack';
        }

        $snapshotData = array(
            'goalUUID' => $goalUUID,
            'snapshotDate' => date('Y-m-d'),
            'currentScore' => $currentScore,
            'targetValue' => $targetValue,
            'actualValue' => $actualValue,
            'completionPercentage' => $completionPercentage,
            'status' => $status,
            'ownerEntityID' => $goal->ownerEntityID,
            'ownerUserID' => $goal->ownerUserID
        );

        // Check if snapshot exists for today
        $existing = $DBConn->retrieve_db_table_rows(
            'tija_goal_performance_snapshots',
            array('snapshotID'),
            array('goalUUID' => $goalUUID, 'snapshotDate' => date('Y-m-d')),
            true
        );

        if ($existing) {
            return $DBConn->update_table(
                'tija_goal_performance_snapshots',
                $snapshotData,
                array('snapshotID' => $existing->snapshotID)
            );
        } else {
            $result = $DBConn->insert_data('tija_goal_performance_snapshots', $snapshotData);
            return $result ? $DBConn->lastInsertId() : false;
        }
    }

    /**
     * Get performance trend
     *
     * @param string $goalUUID Goal UUID
     * @param string $period Period (e.g., '3months', '1year')
     * @param object $DBConn Database connection
     * @return array|false Trend data or false
     */
    public static function getPerformanceTrend($goalUUID, $period = '3months', $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        // Calculate date range
        $dateFrom = date('Y-m-d', strtotime('-' . $period));
        $dateTo = date('Y-m-d');

        $cols = array('snapshotDate', 'currentScore', 'completionPercentage', 'status');
        $where = array(
            'goalUUID' => $goalUUID,
            'snapshotDate' => array('BETWEEN', $dateFrom, $dateTo)
        );

        $snapshots = $DBConn->retrieve_db_table_rows(
            'tija_goal_performance_snapshots',
            $cols,
            $where,
            false
        );

        if (!$snapshots) {
            return false;
        }

        // Sort by date
        usort($snapshots, function($a, $b) {
            return strtotime($a->snapshotDate) - strtotime($b->snapshotDate);
        });

        return $snapshots;
    }

    /**
     * Get latest score for goal
     *
     * @param string $goalUUID Goal UUID
     * @param object $DBConn Database connection
     * @return float|false Score or false
     */
    public static function getLatestScore($goalUUID, $DBConn) {
        $cols = array('calculatedScore');
        $where = array('goalUUID' => $goalUUID);
        $orderBy = 'ORDER BY calculationDate DESC LIMIT 1';

        $score = $DBConn->retrieve_db_table_rows_custom(
            "SELECT calculatedScore FROM tija_goal_scores WHERE goalUUID = :goalUUID ORDER BY calculationDate DESC LIMIT 1",
            array(array('goalUUID', $goalUUID))
        );

        if ($score && count($score) > 0) {
            return (float)$score[0]['calculatedScore'];
        }

        // Fallback: calculate on the fly
        require_once 'goalevaluation.php';
        return GoalEvaluation::calculateWeightedScore($goalUUID, $DBConn);
    }
}

