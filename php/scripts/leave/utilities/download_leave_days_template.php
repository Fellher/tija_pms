<?php
/**
 * Download CSV template for leave days taken uploads
 * This template can be pre-filled with existing leave applications or left empty for new entries
 */

session_start();
$base = '../../../../';
require_once $base . 'php/includes.php';

if (!$isValidUser || (!$isAdmin && !$isValidAdmin && !$isHRManager)) {
    http_response_code(403);
    exit('Access denied');
}

$defaultEntityID = (int)($userDetails->entityID ?? ($_SESSION['entityID'] ?? 1));
$requestedEntityID = isset($_GET['entityID']) ? (int)Utility::clean_string($_GET['entityID']) : $defaultEntityID;
$includeExisting = isset($_GET['includeExisting']) && $_GET['includeExisting'] === '1';

if ($isHRManager && !$isAdmin && !$isValidAdmin) {
    $requestedEntityID = $defaultEntityID;
}

$entityID = $requestedEntityID > 0 ? $requestedEntityID : $defaultEntityID;

$leaveTypes = Leave::leave_types(array('Lapsed' => 'N', 'Suspended' => 'N'), false, $DBConn);
if (!$leaveTypes) {
    $leaveTypes = array();
}

$leaveTypeMap = array();
foreach ($leaveTypes as $leaveType) {
    $leaveTypeMap[$leaveType->leaveTypeID] = $leaveType->leaveTypeName;
}

$employeesSql = "
    SELECT
        ud.ID AS employeeID,
        ud.payrollNo,
        ud.entityID,
        ud.orgDataID,
        p.FirstName,
        p.Surname,
        p.Email
    FROM user_details ud
    LEFT JOIN people p ON ud.ID = p.ID
    WHERE ud.entityID = ?
      AND ud.Lapsed = 'N'
      AND ud.Suspended = 'N'
    ORDER BY p.FirstName, p.Surname ASC
";
$employees = $DBConn->fetch_all_rows($employeesSql, array(array($entityID, 'i')));

$filename = sprintf('leave_days_taken_template_entity_%d_%s.csv', $entityID, date('Ymd_His'));
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

$headers = array(
    'Payroll Number',
    'Employee ID',
    'Employee Name',
    'Email',
    'Leave Type',
    'Start Date (YYYY-MM-DD)',
    'End Date (YYYY-MM-DD)',
    'Days Taken',
    'Status (1=New, 3=Pending, 4=Approved)',
    'Comments (Optional)'
);
fputcsv($output, $headers);

if ($includeExisting && $employees) {
    $existingApplicationsSql = "
        SELECT
            la.leaveApplicationID,
            la.employeeID,
            la.leaveTypeID,
            la.startDate,
            la.endDate,
            la.noOfDays,
            la.leaveStatusID,
            la.leaveComments,
            ud.payrollNo,
            p.FirstName,
            p.Surname,
            p.Email,
            lt.leaveTypeName
        FROM tija_leave_applications la
        LEFT JOIN user_details ud ON la.employeeID = ud.ID
        LEFT JOIN people p ON la.employeeID = p.ID
        LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
        WHERE la.entityID = ?
          AND la.Lapsed = 'N'
          AND la.Suspended = 'N'
        ORDER BY la.startDate DESC, p.FirstName, p.Surname ASC
    ";
    $existingApps = $DBConn->fetch_all_rows($existingApplicationsSql, array(array($entityID, 'i')));

    if ($existingApps) {
        foreach ($existingApps as $app) {
            $appObj = is_object($app) ? $app : (object)$app;
            $nameParts = array_filter(array($appObj->FirstName ?? '', $appObj->Surname ?? ''));
            $employeeName = trim(implode(' ', $nameParts));

            $row = array(
                $appObj->payrollNo ?? '',
                $appObj->employeeID ?? '',
                $employeeName,
                $appObj->Email ?? '',
                $appObj->leaveTypeName ?? '',
                $appObj->startDate ?? '',
                $appObj->endDate ?? '',
                $appObj->noOfDays ?? '',
                $appObj->leaveStatusID ?? '4',
                $appObj->leaveComments ?? ''
            );
            fputcsv($output, $row);
        }
    }
} else {
    if ($employees) {
        foreach ($employees as $employee) {
            $employeeObj = is_object($employee) ? $employee : (object)$employee;
            $nameParts = array_filter(array($employeeObj->FirstName ?? '', $employeeObj->Surname ?? ''));
            $employeeName = trim(implode(' ', $nameParts));

            foreach ($leaveTypes as $leaveType) {
                $row = array(
                    $employeeObj->payrollNo ?? '',
                    $employeeObj->employeeID ?? '',
                    $employeeName,
                    $employeeObj->Email ?? '',
                    $leaveType->leaveTypeName ?? '',
                    '',
                    '',
                    '',
                    '4',
                    ''
                );
                fputcsv($output, $row);
            }
        }
    }
}

fclose($output);
exit;

