<?php
/**
 * Download CSV template for leave balance uploads
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

if ($isHRManager && !$isAdmin && !$isValidAdmin) {
    // HR managers are limited to their scoped entity
    $requestedEntityID = $defaultEntityID;
}

$entityID = $requestedEntityID > 0 ? $requestedEntityID : $defaultEntityID;

$leaveTypes = Leave::leave_types(array('Lapsed' => 'N', 'Suspended' => 'N'), false, $DBConn);
if (!$leaveTypes) {
    $leaveTypes = array();
}

$employeesSql = "
    SELECT
        ud.ID AS employeeID,
        ud.payrollNo,
        ud.entityID,
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

$filename = sprintf('leave_balance_template_entity_%d_%s.csv', $entityID, date('Ymd_His'));
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

$headers = array('Payroll Number', 'Employee ID', 'Employee Name', 'Email');
foreach ($leaveTypes as $leaveType) {
    $headers[] = trim(($leaveType->leaveTypeName ?? 'Leave Type')) . ' Balance';
}
fputcsv($output, $headers);

if ($employees) {
    foreach ($employees as $employee) {
        $employeeObj = is_object($employee) ? $employee : (object)$employee;
        $nameParts = array_filter(array($employeeObj->FirstName ?? '', $employeeObj->Surname ?? ''));
        $employeeName = trim(implode(' ', $nameParts));

        $row = array(
            $employeeObj->payrollNo ?? '',
            $employeeObj->employeeID ?? '',
            $employeeName,
            $employeeObj->Email ?? ''
        );

        // leave balance placeholders
        foreach ($leaveTypes as $leaveType) {
            $row[] = '';
        }

        fputcsv($output, $row);
    }
}

fclose($output);
exit;

