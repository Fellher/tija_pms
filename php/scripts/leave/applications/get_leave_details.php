<?php
/**
 * Get Leave Details Script
 *
 * Retrieves detailed information about a specific leave application
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
        echo json_encode(['success' => false, 'message' => 'Leave ID is required']);
        exit;
    }

    // Get comprehensive leave application details using Leave class method
    $leave = Leave::get_leave_application_full_details($leaveId, $DBConn);

    if (!$leave) {
        echo json_encode(['success' => false, 'message' => 'Leave application not found']);
        exit;
    }

    // Get supporting documents using Leave class method
    $documents = Leave::get_leave_application_documents($leaveId, $DBConn);

    // Check permissions using Leave class method
    $currentUserId = isset($userDetails->ID) ? $userDetails->ID : null;
    $permissions = Leave::check_leave_application_permissions($leave, $currentUserId);

    // Convert object to associative array for consistent response
    $leaveData = is_object($leave) ? (array)$leave : $leave;

    // Build response
    echo json_encode([
        'success' => true,
        'leave' => [
            'leaveApplicationID' => $leaveData['leaveApplicationID'] ?? null,
            'employeeName' => $leaveData['employeeName'] ?? '',
            'employeeFirstName' => $leaveData['FirstName'] ?? '',
            'employeeSurname' => $leaveData['Surname'] ?? '',
            'employeeEmail' => $leaveData['Email'] ?? '',
            'jobTitle' => $leaveData['jobTitle'] ?? '',
            'departmentName' => $leaveData['departmentName'] ?? '',
            'supervisorName' => $leaveData['supervisorName'] ?? '',
            'employmentStartDate' => $leaveData['employmentStartDate'] ?? null,
            'leaveTypeName' => $leaveData['leaveTypeName'] ?? '',
            'leaveTypeID' => $leaveData['leaveTypeID'] ?? null,
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
            'leaveStatusID' => $leaveData['leaveStatusID'] ?? null,
            'createdBy' => $leaveData['createdBy'] ?? null,
            'createdDate' => $leaveData['createdDate'] ?? null,
            'modifiedBy' => $leaveData['modifiedBy'] ?? null,
            'modifiedDate' => $leaveData['modifiedDate'] ?? null
        ],
        'documents' => $documents,
        'permissions' => $permissions
    ]);

} catch (Exception $e) {
    error_log('Get leave details error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while retrieving leave details',
        'error' => $e->getMessage()
    ]);
}
?>
