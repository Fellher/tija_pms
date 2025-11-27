<?php
/**
 * Get Approval Details Script
 *
 * Retrieves detailed information about a specific leave application for approval
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
    $leaveId = isset($input['leaveId']) ? Utility::clean_string($input['leaveId']) : '';

    if (empty($leaveId)) {
        $message = 'Leave ID is required';
        error_log('[get_approval_details] ' . $message);
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }

    // Get comprehensive leave application details using Leave class method
    $leave = Leave::get_leave_application_full_details($leaveId, $DBConn);

    if (!$leave) {
        $message = 'Leave application not found';
        error_log('[get_approval_details] ' . $message . ' (ID: ' . $leaveId . ')');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }

    // Convert to array for easier access
    $leaveData = is_object($leave) ? (array)$leave : $leave;

    // Get approval comments using Leave class method
    $comments = Leave::get_leave_approval_comments($leaveId, $DBConn);

    // Get supporting documents using Leave class method
    $documents = Leave::get_leave_application_documents($leaveId, $DBConn);

    // Check permissions using Leave class method
    $currentUserId = $userDetails->ID ?? null;
    $permissions = Leave::check_leave_application_permissions($leave, $currentUserId);

    // Workflow information temporarily disabled
    $workflowSummary = array(
        'instance' => null,
        'steps' => array(),
        'pendingApprovers' => array()
    );
    $workflow = array();
    $workflowSteps = array();
    $pendingApprovers = array();

    $canApprove = !empty($permissions['canApprove']);
    $isHRManager = !empty($permissions['isHRManager']);

    $response = [
        'success' => true,
        'leave' => [
            'leaveApplicationID' => $leaveData['leaveApplicationID'] ?? null,
            'employeeName' => $leaveData['employeeName'] ?? '',
            'jobTitle' => $leaveData['jobTitle'] ?? '',
            'departmentName' => $leaveData['departmentName'] ?? '',
            'supervisorName' => $leaveData['supervisorName'] ?? '',
            'employmentStartDate' => $leaveData['employmentStartDate'] ?? null,
            'leaveTypeName' => $leaveData['leaveTypeName'] ?? '',
            'startDate' => $leaveData['startDate'] ?? null,
            'endDate' => $leaveData['endDate'] ?? null,
            'noOfDays' => $leaveData['noOfDays'] ?? 0,
            'halfDayLeave' => $leaveData['halfDayLeave'] ?? 'N',
            'halfDayPeriod' => $leaveData['halfDayPeriod'] ?? null,
            'leaveComments' => $leaveData['leaveComments'] ?? '',
            'emergencyContact' => $leaveData['emergencyContact'] ?? '',
            'handoverNotes' => $leaveData['handoverNotes'] ?? '',
            'dateApplied' => $leaveData['dateApplied'] ?? $leaveData['DateAdded'] ?? null,
            'leaveStatusName' => $leaveData['leaveStatusName'] ?? '',
            'leaveStatusID' => $leaveData['leaveStatusID'] ?? null
        ],
        'workflow' => $workflow,
        'workflowSteps' => $workflowSteps,
        'workflowInstance' => $workflowSummary['instance'] ?? null,
        'comments' => $comments,
        'documents' => $documents,
        'permissions' => $permissions,
        'canApprove' => $canApprove,
        'isHRManager' => $isHRManager,
        'pendingApprovers' => $pendingApprovers
    ];

    if (isset($_GET['debug']) && $_GET['debug'] === 'true') {
        $response['source'] = 'get_approval_details';
        $response['timestamp'] = date('c');
    }

    echo json_encode($response);

} catch (Exception $e) {
    error_log('Get approval details error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while retrieving approval details',
        'error' => $e->getMessage()
    ]);
}
?>
