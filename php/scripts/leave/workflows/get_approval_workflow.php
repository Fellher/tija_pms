<?php
/**
 * Get Approval Workflow Script
 *
 * Retrieves the approval workflow for a specific employee
 * Refactored to use Leave class methods
 */

// Include necessary files
session_start();
$base = '../../../../';
set_include_path($base);
include 'php/includes.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!$isValidUser) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);
    $employeeId = isset($input['employeeId']) ? Utility::clean_string($input['employeeId']) : '';
    $entityId = isset($input['entityId']) ? Utility::clean_string($input['entityId']) : '';

    if (empty($employeeId) || empty($entityId)) {
        echo json_encode(['success' => false, 'message' => 'Employee ID and Entity ID are required']);
        exit;
    }

    // Get approval workflow using Leave class method
    $workflow = Leave::get_approval_workflow($employeeId, $entityId, $DBConn);

    // Build response with approver names
    $workflowData = array();

    foreach ($workflow as $step) {
        $approver = Employee::employees(array('ID' => $step->approverID), true, $DBConn);

        $workflowData[] = array(
            'level' => $step->approvalLevel,
            'type' => $step->approvalType,
            'approverID' => $step->approverID,
            'approverName' => $approver ? ($approver->FirstName . ' ' . $approver->Surname) : 'Not assigned',
            'approverJobTitle' => isset($approver->jobTitle) ? $approver->jobTitle : 'N/A'
        );
    }

    echo json_encode([
        'success' => true,
        'workflow' => $workflowData,
        'totalSteps' => count($workflowData)
    ]);

} catch (Exception $e) {
    error_log('Get approval workflow error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while retrieving approval workflow',
        'error' => $e->getMessage()
    ]);
}
?>
