<?php
/**
 * Goal Score Calculation Script
 * Calculates and updates goal scores (cron-ready)
 *
 * @package    TIJA_PMS
 * @subpackage Goals
 */

// Can be run from CLI or web
if (php_sapi_name() !== 'cli') {
    session_start();
    $base = '../../../';
    set_include_path($base);
    include 'php/includes.php';

    if (!$isValidUser) {
        echo json_encode(array('success' => false, 'message' => 'Unauthorized access'));
        exit;
    }
} else {
    // CLI mode
    $base = __DIR__ . '/../../../';
    set_include_path($base);
    include 'php/includes.php';
}

require_once 'php/classes/goalevaluation.php';
require_once 'php/classes/goalscoring.php';

header('Content-Type: application/json');

try {
    $goalUUID = isset($_POST['goalUUID']) ? Utility::clean_string($_POST['goalUUID']) : null;
    $entityID = isset($_POST['entityID']) ? intval($_POST['entityID']) : null;

    if ($goalUUID) {
        // Calculate score for specific goal
        $score = GoalEvaluation::calculateWeightedScore($goalUUID, $DBConn);
        $response = array('success' => true, 'goalUUID' => $goalUUID, 'score' => $score);
    } elseif ($entityID) {
        // Calculate score for entity
        $score = GoalScoring::calculateEntityScore($entityID, null, $DBConn);
        $response = array('success' => true, 'entityID' => $entityID, 'score' => $score);
    } else {
        // Calculate all active goals (for cron)
        require_once 'php/classes/goal.php';
        $goals = $DBConn->retrieve_db_table_rows(
            'tija_goals',
            array('goalUUID'),
            array('status' => 'Active', 'sysEndTime' => 'NULL', 'Lapsed' => 'N'),
            false
        );

        $results = array();
        foreach ($goals as $goal) {
            $score = GoalEvaluation::calculateWeightedScore($goal->goalUUID, $DBConn);
            GoalScoring::generateSnapshot($goal->goalUUID, $DBConn);
            $results[] = array('goalUUID' => $goal->goalUUID, 'score' => $score);
        }

        $response = array('success' => true, 'processed' => count($results), 'results' => $results);
    }

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(array('success' => false, 'message' => 'Error: ' . $e->getMessage()));
}
exit;

