<?php
/**
 * Goal Cascade Script
 * Handles goal cascading operations
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

require_once 'php/classes/goalhierarchy.php';
require_once 'php/classes/goalpermissions.php';

$action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : 'cascade';
$response = array('success' => false, 'message' => 'Invalid action');

try {
    if ($action === 'cascade') {
        $parentUUID = isset($_POST['parentGoalUUID']) ? Utility::clean_string($_POST['parentGoalUUID']) : '';
        $mode = isset($_POST['cascadeMode']) ? Utility::clean_string($_POST['cascadeMode']) : 'Strict';
        $targets = isset($_POST['targets']) ? json_decode($_POST['targets'], true) : array();

        if (empty($parentUUID)) {
            $response = array('success' => false, 'message' => 'Parent goal UUID is required');
        } elseif (!GoalPermissions::canCascade($userDetails->ID, $DBConn)) {
            $response = array('success' => false, 'message' => 'Permission denied: Cannot cascade goals');
        } elseif (empty($targets)) {
            $response = array('success' => false, 'message' => 'Targets are required');
        } else {
            $response = GoalHierarchy::cascadeGoal($parentUUID, $mode, $targets, $DBConn);
        }
    } elseif ($action === 'get_path') {
        $goalUUID = isset($_POST['goalUUID']) ? Utility::clean_string($_POST['goalUUID']) : '';
        if (empty($goalUUID)) {
            $response = array('success' => false, 'message' => 'Goal UUID is required');
        } else {
            $path = GoalHierarchy::getCascadePath($goalUUID, $DBConn);
            $response = array('success' => true, 'path' => $path);
        }
    }
} catch (Exception $e) {
    $response = array('success' => false, 'message' => 'Error: ' . $e->getMessage());
}

echo json_encode($response);
exit;

