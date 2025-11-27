<?php
/**
 * Get Approval Status
 *
 * Returns detailed approval status for a leave application including
 * all workflow steps, approvers, and their actions
 */

session_start();
$base = '../../../../';
set_include_path($base);
include 'php/includes.php';

header('Content-Type: application/json');

if (!$isValidUser) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $leaveApplicationID = isset($input['leaveApplicationID']) ? (int)$input['leaveApplicationID'] : 0;

    if (empty($leaveApplicationID)) {
        echo json_encode(['success' => false, 'message' => 'Leave application ID is required']);
        exit;
    }

    // Get leave application
    $leaveApplication = Leave::leave_applications_full(
        array('leaveApplicationID' => $leaveApplicationID),
        true,
        $DBConn
    );

    if (!$leaveApplication) {
        echo json_encode(['success' => false, 'message' => 'Leave application not found']);
        exit;
    }

    $leaveApplication = is_object($leaveApplication) ? (array)$leaveApplication : $leaveApplication;
    $userID = $userDetails->ID;
    $employeeID = isset($leaveApplication['employeeID']) ? (int)$leaveApplication['employeeID'] : null;

    // Check permissions - applicant or approver can view
    $permissions = Leave::check_leave_application_permissions((object)$leaveApplication, $userID);
    $isHrManager = Employee::is_hr_manager($userID, $DBConn, $leaveApplication['entityID'] ?? null);
    $isApplicant = ($employeeID === $userID);

    if (empty($permissions['canView']) && !$isHrManager && !$isApplicant) {
        echo json_encode(['success' => false, 'message' => 'You are not authorized to view this application']);
        exit;
    }

    // Get workflow instance
    $lapsedCheck = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_instances LIKE 'Lapsed'", array());
    $hasLapsedColumn = ($lapsedCheck && count($lapsedCheck) > 0);

    $whereClause = "leaveApplicationID = ?";
    $params = array(array($leaveApplicationID, 'i'));

    if ($hasLapsedColumn) {
        $whereClause .= " AND Lapsed = 'N'";
    }

    $instance = $DBConn->fetch_all_rows(
        "SELECT * FROM tija_leave_approval_instances WHERE {$whereClause}",
        $params
    );

    if (!$instance || count($instance) === 0) {
        // No workflow - return simple status
        echo json_encode([
            'success' => true,
            'hasWorkflow' => false,
            'leaveStatusID' => $leaveApplication['leaveStatusID'] ?? 3,
            'steps' => array()
        ]);
        exit;
    }

    $inst = is_object($instance[0]) ? (array)$instance[0] : $instance[0];
    $instanceID = $inst['instanceID'] ?? null;
    $policyID = $inst['policyID'] ?? null;

    if (!$instanceID || !$policyID) {
        echo json_encode([
            'success' => true,
            'hasWorkflow' => false,
            'leaveStatusID' => $leaveApplication['leaveStatusID'] ?? 3,
            'steps' => array()
        ]);
        exit;
    }

    // Get approval status
    $approvalStatus = Leave::check_workflow_approval_status($instanceID, $policyID, $DBConn);

    // Get approval comments
    $comments = Leave::get_leave_approval_comments($leaveApplicationID, $DBConn);

    echo json_encode([
        'success' => true,
        'hasWorkflow' => true,
        'leaveStatusID' => $leaveApplication['leaveStatusID'] ?? 3,
        'workflow' => $approvalStatus,
        'comments' => $comments
    ]);

} catch (Exception $e) {
    error_log('Get approval status error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while retrieving approval status',
        'error' => $e->getMessage()
    ]);
}
?>

