<?php
/**
 * Goal Evaluation Class
 *
 * Multi-rater evaluation engine with weighted scoring
 *
 * @package    TIJA_PMS
 * @subpackage Goals
 * @version    1.0.0
 */

class GoalEvaluation {

    /**
     * Submit evaluation
     *
     * @param string $goalUUID Goal UUID
     * @param int $evaluatorID Evaluator user ID
     * @param float $score Score (0-100)
     * @param string $comments Comments
     * @param object $DBConn Database connection
     * @return int|false Evaluation ID or false
     */
    public static function submitEvaluation($goalUUID, $evaluatorID, $score, $comments = null, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        // Determine evaluator role
        $evaluatorRole = self::determineEvaluatorRole($goalUUID, $evaluatorID, $DBConn);

        // Check if evaluation already exists
        $existing = $DBConn->retrieve_db_table_rows(
            'tija_goal_evaluations',
            array('evaluationID'),
            array('goalUUID' => $goalUUID, 'evaluatorUserID' => $evaluatorID, 'status' => array('Draft', 'Submitted')),
            true
        );

        $evaluationData = array(
            'goalUUID' => $goalUUID,
            'evaluatorUserID' => $evaluatorID,
            'evaluatorRole' => $evaluatorRole,
            'score' => $score,
            'comments' => $comments,
            'isAnonymous' => ($evaluatorRole === 'Peer' || $evaluatorRole === 'Subordinate') ? 'Y' : 'N',
            'status' => 'Submitted'
        );

        if ($existing) {
            // Update existing
            return $DBConn->update_table(
                'tija_goal_evaluations',
                $evaluationData,
                array('evaluationID' => $existing->evaluationID)
            );
        } else {
            // Create new
            $result = $DBConn->insert_data('tija_goal_evaluations', $evaluationData);
            if ($result) {
                // Recalculate weighted score
                self::calculateWeightedScore($goalUUID, $DBConn);
                return $DBConn->lastInsertId();
            }
        }

        return false;
    }

    /**
     * Calculate weighted score for goal
     *
     * @param string $goalUUID Goal UUID
     * @param object $DBConn Database connection
     * @return float|false Weighted score or false
     */
    public static function calculateWeightedScore($goalUUID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        // Get evaluation weights
        $weights = $DBConn->retrieve_db_table_rows(
            'tija_goal_evaluation_weights',
            array('evaluatorRole', 'weight'),
            array('goalUUID' => $goalUUID),
            false
        );

        if (!$weights || count($weights) === 0) {
            // Use default weights
            $weights = self::getDefaultWeights();
        }

        // Get submitted evaluations
        $evaluations = $DBConn->retrieve_db_table_rows(
            'tija_goal_evaluations',
            array('evaluatorRole', 'score'),
            array('goalUUID' => $goalUUID, 'status' => 'Submitted'),
            false
        );

        if (!$evaluations || count($evaluations) === 0) {
            return false;
        }

        // Build weight map
        $weightMap = array();
        foreach ($weights as $weight) {
            $weightMap[$weight->evaluatorRole] = (float)$weight->weight;
        }

        // Calculate weighted sum
        $weightedSum = 0.0;
        $totalWeight = 0.0;
        $missingRoles = array();

        foreach ($evaluations as $evaluation) {
            $role = $evaluation->evaluatorRole;
            if (isset($weightMap[$role])) {
                $weight = $weightMap[$role];
                $score = (float)$evaluation->score;
                $weightedSum += $score * $weight;
                $totalWeight += $weight;
            }
        }

        // Check for missing evaluators
        foreach ($weightMap as $role => $weight) {
            $found = false;
            foreach ($evaluations as $evaluation) {
                if ($evaluation->evaluatorRole === $role) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $missingRoles[] = $role;
            }
        }

        // Normalize if weights don't sum to 1.0 (due to missing evaluators)
        if ($totalWeight > 0 && $totalWeight < 1.0) {
            // Redistribute missing weights proportionally
            $normalizedSum = $weightedSum / $totalWeight;
            $weightedSum = $normalizedSum; // Already normalized
        } elseif ($totalWeight === 0) {
            return false;
        }

        $calculatedScore = $weightedSum; // Already weighted and normalized

        // Get goal weight
        require_once 'goal.php';
        $goal = Goal::getGoal($goalUUID, $DBConn);
        $goalWeight = $goal ? (float)$goal->weight : 1.0;
        $weightedScore = $calculatedScore * $goalWeight;

        // Store calculated score
        $scoreData = array(
            'goalUUID' => $goalUUID,
            'calculatedScore' => $calculatedScore,
            'weightedScore' => $weightedScore,
            'calculationMethod' => 'WeightedAverage',
            'missingEvaluators' => json_encode($missingRoles)
        );

        // Count evaluators
        $evaluatorCount = count($evaluations);
        $scoreData['evaluatorCount'] = $evaluatorCount;

        // Check if score already exists for today
        $existingScore = $DBConn->retrieve_db_table_rows(
            'tija_goal_scores',
            array('scoreID'),
            array('goalUUID' => $goalUUID, 'calculationDate' => array('BETWEEN', date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59'))),
            true
        );

        if ($existingScore) {
            $DBConn->update_table(
                'tija_goal_scores',
                $scoreData,
                array('scoreID' => $existingScore->scoreID)
            );
        } else {
            $DBConn->insert_data('tija_goal_scores', $scoreData);
        }

        return $calculatedScore;
    }

    /**
     * Normalize missing evaluations (redistribute weights)
     *
     * @param string $goalUUID Goal UUID
     * @param object $DBConn Database connection
     * @return array New weights array
     */
    public static function normalizeMissingEvaluations($goalUUID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        // Get configured weights
        $weights = $DBConn->retrieve_db_table_rows(
            'tija_goal_evaluation_weights',
            array('evaluatorRole', 'weight'),
            array('goalUUID' => $goalUUID),
            false
        );

        // Get submitted evaluations
        $evaluations = $DBConn->retrieve_db_table_rows(
            'tija_goal_evaluations',
            array('evaluatorRole'),
            array('goalUUID' => $goalUUID, 'status' => 'Submitted'),
            false
        );

        $submittedRoles = array();
        if ($evaluations) {
            foreach ($evaluations as $eval) {
                $submittedRoles[] = $eval->evaluatorRole;
            }
        }

        // Calculate new weights (proportional redistribution)
        $totalSubmittedWeight = 0.0;
        $newWeights = array();

        foreach ($weights as $weight) {
            if (in_array($weight->evaluatorRole, $submittedRoles)) {
                $totalSubmittedWeight += (float)$weight->weight;
            }
        }

        if ($totalSubmittedWeight > 0) {
            foreach ($weights as $weight) {
                if (in_array($weight->evaluatorRole, $submittedRoles)) {
                    $newWeight = (float)$weight->weight / $totalSubmittedWeight;
                    $newWeights[$weight->evaluatorRole] = $newWeight;

                    // Update in database
                    $DBConn->update_table(
                        'tija_goal_evaluation_weights',
                        array('weight' => $newWeight),
                        array('goalUUID' => $goalUUID, 'evaluatorRole' => $weight->evaluatorRole)
                    );
                }
            }
        }

        return $newWeights;
    }

    /**
     * Get evaluations for goal
     *
     * @param string $goalUUID Goal UUID
     * @param bool $includeAnonymous Include anonymous evaluations
     * @param object $DBConn Database connection
     * @return array|false Evaluations array or false
     */
    public static function getEvaluations($goalUUID, $includeAnonymous = false, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $cols = array(
            'evaluationID', 'goalUUID', 'evaluatorUserID', 'evaluatorRole',
            'score', 'comments', 'isAnonymous', 'evaluationDate', 'status'
        );

        $where = array('goalUUID' => $goalUUID);
        if (!$includeAnonymous) {
            $where['isAnonymous'] = 'N';
        }

        $evaluations = $DBConn->retrieve_db_table_rows('tija_goal_evaluations', $cols, $where, false);

        // Anonymize if needed
        if ($evaluations && !$includeAnonymous) {
            foreach ($evaluations as $eval) {
                if ($eval->isAnonymous === 'Y') {
                    $eval->evaluatorUserID = null;
                    $eval->comments = 'Anonymous feedback';
                }
            }
        }

        return $evaluations;
    }

    /**
     * Get 360-degree feedback aggregate
     *
     * @param string $goalUUID Goal UUID
     * @param object $DBConn Database connection
     * @return array|false Feedback summary or false
     */
    public static function get360Feedback($goalUUID, $DBConn = null) {
        if (!$DBConn) {
            global $DBConn;
        }

        $evaluations = self::getEvaluations($goalUUID, true, $DBConn);
        if (!$evaluations) {
            return false;
        }

        // Group by role
        $feedback = array(
            'Manager' => array('scores' => array(), 'comments' => array()),
            'Self' => array('scores' => array(), 'comments' => array()),
            'Peer' => array('scores' => array(), 'comments' => array()),
            'Subordinate' => array('scores' => array(), 'comments' => array()),
            'Matrix' => array('scores' => array(), 'comments' => array())
        );

        foreach ($evaluations as $eval) {
            $role = $eval->evaluatorRole;
            if (isset($feedback[$role])) {
                $feedback[$role]['scores'][] = (float)$eval->score;
                if ($eval->comments) {
                    $feedback[$role]['comments'][] = $eval->comments;
                }
            }
        }

        // Calculate averages
        $summary = array();
        foreach ($feedback as $role => $data) {
            if (count($data['scores']) > 0) {
                $summary[$role] = array(
                    'averageScore' => array_sum($data['scores']) / count($data['scores']),
                    'count' => count($data['scores']),
                    'comments' => $data['comments']
                );
            }
        }

        // Get overall weighted score
        $summary['overallScore'] = self::calculateWeightedScore($goalUUID, $DBConn);

        return $summary;
    }

    /**
     * Determine evaluator role
     *
     * @param string $goalUUID Goal UUID
     * @param int $evaluatorID Evaluator user ID
     * @param object $DBConn Database connection
     * @return string Evaluator role
     */
    private static function determineEvaluatorRole($goalUUID, $evaluatorID, $DBConn) {
        require_once 'goal.php';
        $goal = Goal::getGoal($goalUUID, $DBConn);

        if (!$goal) {
            return 'External';
        }

        // Check if self-evaluation
        if ($goal->ownerUserID == $evaluatorID) {
            return 'Self';
        }

        // Check if manager (would need to check supervisor relationship)
        require_once 'employee.php';
        $employee = Employee::employees(array('ID' => $goal->ownerUserID), true, $DBConn);
        if ($employee && $employee->supervisorID == $evaluatorID) {
            return 'Manager';
        }

        // Check if matrix manager
        require_once 'goalmatrix.php';
        $matrixAssignment = GoalMatrix::getMatrixAssignment($goalUUID, $goal->ownerUserID, $DBConn);
        if ($matrixAssignment && $matrixAssignment->matrixManagerID == $evaluatorID) {
            return 'Matrix';
        }

        // Default to Peer (would need more logic to determine Peer vs Subordinate)
        return 'Peer';
    }

    /**
     * Get default evaluation weights
     *
     * @return array Default weights
     */
    private static function getDefaultWeights() {
        return array(
            (object)array('evaluatorRole' => 'Manager', 'weight' => 0.5),
            (object)array('evaluatorRole' => 'Self', 'weight' => 0.2),
            (object)array('evaluatorRole' => 'Peer', 'weight' => 0.3)
        );
    }
}

