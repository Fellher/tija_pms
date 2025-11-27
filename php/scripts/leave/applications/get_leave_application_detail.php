<?php
/**
 * Fetch leave application detail for approver modal (AJAX)
 */

session_start();
$base = '../../../../';
set_include_path($base);
include 'php/includes.php';

header('Content-Type: application/json');

if (!$isValidUser) {
    echo json_encode(array('success' => false, 'message' => 'Unauthorized'));
    exit;
}

$leaveApplicationID = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($leaveApplicationID <= 0) {
    echo json_encode(array('success' => false, 'message' => 'Invalid leave application reference'));
    exit;
}

$leaveRecord = Leave::leave_applications_full(
    array('leaveApplicationID' => $leaveApplicationID),
    true,
    $DBConn
);

if (!$leaveRecord) {
    echo json_encode(array('success' => false, 'message' => 'Leave application not found'));
    exit;
}

$leave = is_object($leaveRecord) ? (array) $leaveRecord : $leaveRecord;
$userID = $userDetails->ID;

$permissions = Leave::check_leave_application_permissions((object) $leave, $userID);
$isHrManager = Employee::is_hr_manager($userID, $DBConn, $leave['entityID'] ?? null);
$isDepartmentHead = Employee::is_department_head($userID, $DBConn);

if (empty($permissions['canView']) && !$isHrManager && !$isDepartmentHead) {
    echo json_encode(array('success' => false, 'message' => 'You are not authorised to view this leave application'));
    exit;
}

$employeeDetails = Employee::employees(array('ID' => $leave['employeeID']), true, $DBConn);
$employeeArray = $employeeDetails ? (array) $employeeDetails : array();

$approvalHistory = array();
$approvalsRaw = Leave::leave_approvals(array('leaveApplicationID' => $leaveApplicationID), false, $DBConn);
if ($approvalsRaw) {
    foreach ($approvalsRaw as $row) {
        $approvalHistory[] = is_object($row) ? (array) $row : $row;
    }
}

$statusName = $leave['leaveStatusName'] ?? '';
if ($statusName === '') {
    $statusLookup = Leave::leave_status(array('Suspended' => 'N'), false, $DBConn);
    if ($statusLookup) {
        foreach ($statusLookup as $status) {
            if ((int) $status->leaveStatusID === (int) $leave['leaveStatusID']) {
                $statusName = $status->leaveStatusName;
                break;
            }
        }
    }
}

// decode attachments (if any)
$attachments = array();
if (!empty($leave['leaveFiles'])) {
    $decoded = base64_decode($leave['leaveFiles']);
    if ($decoded !== false) {
        $paths = array_filter(array_map('trim', explode(',', $decoded)));
        foreach ($paths as $path) {
            $attachments[] = array(
                'path' => $path,
                'name' => basename($path),
            );
        }
    }
}

$response = array(
    'success' => true,
    'application' => array(
        'id' => $leaveApplicationID,
        'statusId' => (int) $leave['leaveStatusID'],
        'statusName' => $statusName,
        'leaveType' => $leave['leaveTypeName'] ?? '',
        'leaveTypeDescription' => $leave['leaveTypeDescription'] ?? '',
        'startDate' => $leave['startDate'],
        'endDate' => $leave['endDate'],
        'noOfDays' => $leave['noOfDays'],
        'dateApplied' => $leave['dateApplied'],
        'lastUpdate' => $leave['LastUpdate'],
        'halfDayLeave' => $leave['halfDayLeave'],
        'halfDayPeriod' => $leave['halfDayPeriod'],
        'reason' => $leave['leaveComments'] ?? '',
        'emergencyContact' => $leave['emergencyContact'] ?? '',
        'handoverNotes' => $leave['handoverNotes'] ?? '',
        'entityName' => $leave['entityName'] ?? '',
        'leavePeriodName' => $leave['leavePeriodName'] ?? '',
    ),
    'employee' => array(
        'id' => $employeeArray['ID'] ?? null,
        'name' => $employeeArray['employeeName']
            ?? trim(($employeeArray['FirstName'] ?? '') . ' ' . ($employeeArray['Surname'] ?? '')),
        'email' => $employeeArray['Email'] ?? '',
        'jobTitle' => $employeeArray['jobTitle'] ?? '',
    ),
    'approvals' => $approvalHistory,
    'attachments' => $attachments,
    'permissions' => array(
        'canApprove' => !empty($permissions['canApprove']) || $isHrManager || $isDepartmentHead,
    ),
);

echo json_encode($response);

