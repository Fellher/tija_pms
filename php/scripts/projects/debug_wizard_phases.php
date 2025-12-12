<?php
/**
 * DEBUG SCRIPT FOR PROJECT WIZARD PHASE DATA
 *
 * This script helps debug why phases are not being saved
 * when creating a project through the wizard.
 */

session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';

// Set header for JSON response
header('Content-Type: application/json');

$debugData = array(
    'timestamp' => date('Y-m-d H:i:s'),
    'post_data' => $_POST,
    'get_data' => $_GET,
    'session_data' => array(
        'userID' => isset($userDetails->ID) ? $userDetails->ID : 'NOT SET',
        'isValidUser' => isset($isValidUser) ? $isValidUser : 'NOT SET'
    ),
    'phase_analysis' => array(),
    'skip_project_plan' => array(),
    'errors' => array()
);

// Analyze phase data - PHP converts phaseName[] to phaseName array automatically
$debugData['phase_analysis']['phaseName_exists'] = isset($_POST['phaseName']);
$debugData['phase_analysis']['phaseName_is_array'] = is_array($_POST['phaseName'] ?? null);
$debugData['phase_analysis']['phaseName_count'] = isset($_POST['phaseName']) && is_array($_POST['phaseName']) ? count($_POST['phaseName']) : 0;
$debugData['phase_analysis']['phaseName_values'] = isset($_POST['phaseName']) && is_array($_POST['phaseName']) ? $_POST['phaseName'] : array();

// Check raw POST data for phaseName[] format (shouldn't exist, but check anyway)
$debugData['phase_analysis']['phaseName_brackets_exists'] = isset($_POST['phaseName[]']);
$debugData['phase_analysis']['phaseName_brackets_is_array'] = is_array($_POST['phaseName[]'] ?? null);
$debugData['phase_analysis']['phaseName_brackets_count'] = isset($_POST['phaseName[]']) && is_array($_POST['phaseName[]']) ? count($_POST['phaseName[]']) : 0;
$debugData['phase_analysis']['phaseName_brackets_values'] = isset($_POST['phaseName[]']) && is_array($_POST['phaseName[]']) ? $_POST['phaseName[]'] : array();

// Check raw input stream to see what's actually being sent
$rawInput = file_get_contents('php://input');
$debugData['phase_analysis']['raw_input_length'] = strlen($rawInput);
$debugData['phase_analysis']['raw_input_preview'] = substr($rawInput, 0, 500); // First 500 chars

// Check all POST keys that contain "phase"
$debugData['phase_analysis']['all_phase_keys'] = array();
foreach ($_POST as $key => $value) {
    if (stripos($key, 'phase') !== false) {
        $debugData['phase_analysis']['all_phase_keys'][$key] = is_array($value) ? $value : array($value);
    }
}

// Analyze skipProjectPlan
$debugData['skip_project_plan']['exists'] = isset($_POST['skipProjectPlan']);
$debugData['skip_project_plan']['value'] = isset($_POST['skipProjectPlan']) ? $_POST['skipProjectPlan'] : 'NOT SET';
$debugData['skip_project_plan']['will_skip'] = isset($_POST['skipProjectPlan']) && !empty($_POST['skipProjectPlan']);

// Simulate backend logic
$skipProjectPlan = isset($_POST['skipProjectPlan']) ? true : false;
$phaseNames = isset($_POST['phaseName']) && is_array($_POST['phaseName'])
    ? array_map('trim', $_POST['phaseName'])
    : array();

// Also check for phaseName[] format
if (empty($phaseNames) && isset($_POST['phaseName[]']) && is_array($_POST['phaseName[]'])) {
    $phaseNames = array_map('trim', $_POST['phaseName[]']);
    $debugData['phase_analysis']['used_brackets_format'] = true;
}

// Filter empty phase names
$phaseNames = array_filter($phaseNames, function($name) {
    return !empty($name);
});

$debugData['phase_analysis']['filtered_count'] = count($phaseNames);
$debugData['phase_analysis']['filtered_values'] = array_values($phaseNames);

// Simulate the condition check
$debugData['simulation'] = array(
    'skipProjectPlan' => $skipProjectPlan,
    'phaseNames_empty' => empty($phaseNames),
    'phaseNames_is_array' => is_array($phaseNames),
    'will_create_phases' => !$skipProjectPlan && !empty($phaseNames) && is_array($phaseNames),
    'condition_check' => array(
        '!$skipProjectPlan' => !$skipProjectPlan,
        '!empty($phaseNames)' => !empty($phaseNames),
        'is_array($phaseNames)' => is_array($phaseNames)
    )
);

// Check if we have project context
$debugData['project_context'] = array(
    'orgDataID' => isset($_POST['orgDataID']) ? $_POST['orgDataID'] : 'NOT SET',
    'entityID' => isset($_POST['entityID']) ? $_POST['entityID'] : 'NOT SET',
    'projectName' => isset($_POST['projectName']) ? $_POST['projectName'] : 'NOT SET',
    'projectID' => isset($_POST['projectID']) ? $_POST['projectID'] : 'NOT SET (will be created)'
);

// Log to error log
error_log("=== DEBUG WIZARD PHASES ===");
error_log("POST Data: " . print_r($_POST, true));
error_log("Skip Project Plan: " . ($skipProjectPlan ? 'YES' : 'NO'));
error_log("Phase Names Count: " . count($phaseNames));
error_log("Phase Names: " . print_r($phaseNames, true));
error_log("Will Create Phases: " . ($debugData['simulation']['will_create_phases'] ? 'YES' : 'NO'));

echo json_encode($debugData, JSON_PRETTY_PRINT);

