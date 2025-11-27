<?php
/**
 * Promote Scheduled Leave Script
 * Converts a scheduled leave plan (status 1) into a pending application (status 3)
 * and triggers notifications to approvers.
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

try {
    $userId = $userDetails->ID;

    $applicationId = isset($_POST['applicationId']) ? (int)Utility::clean_string($_POST['applicationId']) : 0;

    if ($applicationId <= 0) {
        echo json_encode(array('success' => false, 'message' => 'Invalid application reference'));
        exit;
    }

    $application = Leave::leave_applications_full(array('leaveApplicationID' => $applicationId), true, $DBConn);

    if (!$application) {
        echo json_encode(array('success' => false, 'message' => 'Leave application not found'));
        exit;
    }

    if ((int)$application->employeeID !== (int)$userId) {
        echo json_encode(array('success' => false, 'message' => 'You can only submit your own leave plans'));
        exit;
    }

    if ((int)$application->leaveStatusID !== 1) {
        echo json_encode(array('success' => false, 'message' => 'Only scheduled leave plans can be submitted'));
        exit;
    }

    $now = date('Y-m-d H:i:s');

    $updateData = array(
        'leaveStatusID' => 3, // Pending approval
        'dateApplied' => $now,
        'appliedByID' => $userId,
        'LastUpdate' => $now,
        'LastUpdateByID' => $userId
    );

    $DBConn->update_table('tija_leave_applications', $updateData, array('leaveApplicationID' => $applicationId));

    // Refresh application details for notifications
    $application = Leave::leave_applications_full(array('leaveApplicationID' => $applicationId), true, $DBConn);

    if (!class_exists('LeaveNotifications')) {
        include_once 'php/classes/leavenotifications.php';
    }

    $notificationsSent = false;

    if (class_exists('LeaveNotifications')) {
        $notificationResult = LeaveNotifications::notifyLeaveSubmitted($applicationId, $DBConn);
        $notificationsSent = $notificationResult['success'] ?? false;
    }

    if (!$notificationsSent) {
        // Manual fallback notification
        if (!class_exists('Notification')) {
            include_once 'php/classes/notification.php';
        }

        $employeeDetails = Employee::employees(array('ID' => $userId), true, $DBConn);
        $employeeName = $employeeDetails ? ($employeeDetails->FirstName . ' ' . $employeeDetails->Surname) : 'Employee';
        $fallbackApprovers = array();

        if ($employeeDetails) {
            $directReport = Employee::get_direct_report($employeeDetails->ID, $DBConn);
            if ($directReport && isset($directReport->ID)) {
                $fallbackApprovers[$directReport->ID] = $directReport;
            }

            if (isset($employeeDetails->departmentID)) {
                $departmentHead = Employee::get_department_head($employeeDetails->departmentID, $DBConn);
                if ($departmentHead && isset($departmentHead->ID)) {
                    $fallbackApprovers[$departmentHead->ID] = $departmentHead;
                }
            }

            $hrManager = Employee::get_hr_manager($employeeDetails->orgDataID ?? null, $employeeDetails->entityID ?? null, $DBConn);
            if ($hrManager && isset($hrManager->ID)) {
                $fallbackApprovers[$hrManager->ID] = $hrManager;
            }
        }

        if (!empty($fallbackApprovers)) {
            foreach ($fallbackApprovers as $approver) {
                Notification::create(array(
                    'eventSlug' => 'leave_pending_approval',
                    'userId' => $approver->ID,
                    'originatorId' => $userId,
                    'data' => array(
                        'employee_id' => $userId,
                        'employee_name' => $employeeName,
                        'leave_type' => $application->leaveTypeName,
                        'start_date' => date('M j, Y', strtotime($application->startDate)),
                        'end_date' => date('M j, Y', strtotime($application->endDate)),
                        'total_days' => $application->noOfDays ?: Leave::countWeekdays($application->startDate, $application->endDate),
                        'application_id' => $applicationId,
                        'approver_name' => isset($approver->FirstName)
                            ? ($approver->FirstName . ' ' . ($approver->Surname ?? ''))
                            : 'Approver'
                    ),
                    'link' => '?s=user&ss=leave&p=pending_approvals&id=' . $applicationId,
                    'entityID' => $application->entityID,
                    'orgDataID' => $application->orgDataID,
                    'segmentType' => 'leave_application',
                    'segmentID' => $applicationId,
                    'priority' => 'high'
                ), $DBConn);
            }

            $notificationsSent = true;
        }
    }

    echo json_encode(array(
        'success' => true,
        'message' => 'Leave application submitted for approval successfully',
        'notificationsSent' => $notificationsSent
    ));

} catch (Exception $e) {
    error_log('Promote scheduled leave error: ' . $e->getMessage());
    echo json_encode(array('success' => false, 'message' => 'Unable to submit leave application'));
}


