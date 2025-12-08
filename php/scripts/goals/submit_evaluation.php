<?php
/**
 * Goal Evaluation Submission Script
 * Handles evaluation submissions
 *
 * @package    TIJA_PMS
 * @subpackage Goals
 */

session_start();
$base = '../../../';
set_include_path($base);
include 'php/includes.php';

header('Content-Type: application/json');

if (!$isValidUser) {
    echo json_encode(array('success' => false, 'message' => 'Unauthorized access'));
    exit;
}

require_once 'php/classes/goalevaluation.php';
require_once 'php/classes/goalpermissions.php';

$action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : 'submit';
$response = array('success' => false, 'message' => 'Invalid action');

try {
    if ($action === 'submit') {
        $goalUUID = isset($_POST['goalUUID']) ? Utility::clean_string($_POST['goalUUID']) : '';
        $score = isset($_POST['score']) ? floatval($_POST['score']) : 0;
        $comments = isset($_POST['comments']) ? Utility::clean_string($_POST['comments']) : null;

        if (empty($goalUUID)) {
            $response = array('success' => false, 'message' => 'Goal UUID is required');
        } elseif (!GoalPermissions::canEvaluate($goalUUID, $userDetails->ID, $DBConn)) {
            $response = array('success' => false, 'message' => 'Permission denied: Cannot evaluate this goal');
        } elseif ($score < 0 || $score > 100) {
            $response = array('success' => false, 'message' => 'Score must be between 0 and 100');
        } else {
            $evaluationID = GoalEvaluation::submitEvaluation($goalUUID, $userDetails->ID, $score, $comments, $DBConn);
            if ($evaluationID) {
                $response = array('success' => true, 'evaluationID' => $evaluationID, 'message' => 'Evaluation submitted successfully');
            } else {
                $response = array('success' => false, 'message' => 'Failed to submit evaluation');
            }
        }
    } elseif ($action === 'get') {
        $goalUUID = isset($_POST['goalUUID']) ? Utility::clean_string($_POST['goalUUID']) : '';
        $includeAnonymous = isset($_POST['includeAnonymous']) ? (bool)$_POST['includeAnonymous'] : false;

        if (empty($goalUUID)) {
            $response = array('success' => false, 'message' => 'Goal UUID is required');
        } else {
            $evaluations = GoalEvaluation::getEvaluations($goalUUID, $includeAnonymous, $DBConn);
            $response = array('success' => true, 'evaluations' => $evaluations ? $evaluations : array());
        }
    } elseif ($action === 'get_360') {
        $goalUUID = isset($_POST['goalUUID']) ? Utility::clean_string($_POST['goalUUID']) : '';
        if (empty($goalUUID)) {
            $response = array('success' => false, 'message' => 'Goal UUID is required');
        } else {
            $feedback = GoalEvaluation::get360Feedback($goalUUID, $DBConn);
            $response = array('success' => true, 'feedback' => $feedback);
        }
    }
} catch (Exception $e) {
    $response = array('success' => false, 'message' => 'Error: ' . $e->getMessage());
}

echo json_encode($response);
exit;

