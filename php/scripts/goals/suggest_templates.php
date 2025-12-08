<?php
/**
 * Goal Template Suggestions Script
 * Returns suggested templates for user
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

require_once 'php/classes/goallibrary.php';

try {
    $userID = $userDetails->ID;
    $context = array();

    // Get user context if provided
    if (isset($_POST['jobTitleID'])) {
        $context['jobTitleID'] = intval($_POST['jobTitleID']);
    }
    if (isset($_POST['departmentID'])) {
        $context['departmentID'] = intval($_POST['departmentID']);
    }

    $templates = GoalLibrary::suggestTemplates($userID, $context, $DBConn);

    if ($templates) {
        echo json_encode(array('success' => true, 'templates' => $templates));
    } else {
        echo json_encode(array('success' => true, 'templates' => array()));
    }
} catch (Exception $e) {
    echo json_encode(array('success' => false, 'message' => 'Error: ' . $e->getMessage()));
}
exit;

