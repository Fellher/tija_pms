<?php
/**
 * Goal Automation Script
 * Handles manual automation execution and settings
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

require_once 'php/classes/goalautomation.php';

$action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : '';
$response = array('success' => false, 'message' => 'Invalid action');

try {
    if ($action === 'execute_manual') {
        $automationType = isset($_POST['automationType']) ? Utility::clean_string($_POST['automationType']) : '';
        $params = isset($_POST['params']) ? json_decode($_POST['params'], true) : array();

        if (empty($automationType)) {
            $response = array('success' => false, 'message' => 'Automation type is required');
        } else {
            $result = GoalAutomation::executeManual($userDetails->ID, $automationType, $params, $DBConn);
            $response = $result;
        }
    } elseif ($action === 'get_settings') {
        $settings = GoalAutomation::getSettings($userDetails->ID, $DBConn);
        $response = array('success' => true, 'settings' => $settings);
    } elseif ($action === 'update_setting') {
        $automationType = isset($_POST['automationType']) ? Utility::clean_string($_POST['automationType']) : '';
        $data = isset($_POST['data']) ? json_decode($_POST['data'], true) : array();

        if (empty($automationType)) {
            $response = array('success' => false, 'message' => 'Automation type is required');
        } else {
            $result = GoalAutomation::updateSetting($userDetails->ID, $automationType, $data, $DBConn);
            $response = array('success' => $result, 'message' => $result ? 'Setting updated successfully' : 'Failed to update setting');
        }
    }
} catch (Exception $e) {
    $response = array('success' => false, 'message' => 'Error: ' . $e->getMessage());
}

echo json_encode($response);
exit;

