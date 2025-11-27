<?php
/**
 * Check Handover Requirement
 *
 * Determines whether a structured handover is required for a leave request
 * based on the selected leave type, entity policy, and duration.
 */
session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $leaveTypeId = isset($_POST['leaveTypeId']) ? (int)Utility::clean_string($_POST['leaveTypeId']) : 0;
    $entityId = isset($_POST['entityId']) ? (int)Utility::clean_string($_POST['entityId']) : 0;
    $startDate = isset($_POST['startDate']) ? Utility::clean_string($_POST['startDate']) : '';
    $endDate = isset($_POST['endDate']) ? Utility::clean_string($_POST['endDate']) : '';

    if (!$leaveTypeId || !$entityId || empty($startDate) || empty($endDate)) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit;
    }

    $workingDays = Leave::calculate_working_days($startDate, $endDate, $entityId, $DBConn);
    $policyInfo = LeaveHandover::check_handover_policy($entityId, $leaveTypeId, $workingDays, $DBConn);

    $policy = $policyInfo['policy'] ?? null;
    if ($policy && !is_object($policy)) {
        $policy = (object)$policy;
    }

    $policyData = $policy ? array(
        'policyID' => $policy->policyID,
        'isMandatory' => $policy->isMandatory,
        'minHandoverDays' => $policy->minHandoverDays,
        'requireConfirmation' => $policy->requireConfirmation,
        'requireTraining' => $policy->requireTraining,
        'requireCredentials' => $policy->requireCredentials,
        'requireTools' => $policy->requireTools,
        'requireDocuments' => $policy->requireDocuments,
        'allowProjectIntegration' => $policy->allowProjectIntegration
    ) : null;

    echo json_encode([
        'success' => true,
        'required' => $policyInfo['required'] ?? false,
        'workingDays' => $workingDays,
        'policy' => $policyData
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Unable to determine handover requirement: ' . $e->getMessage()
    ]);
}
?>


