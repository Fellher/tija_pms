<?php
/**
 * Get Employee Leave Details for Analytics
 * Returns detailed leave history for a specific employee
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

// Access control - HR Managers and Admins only
$isHrManager = Employee::is_hr_manager($userDetails->ID, $DBConn);
$isAdmin = isset($userDetails->isAdmin) && $userDetails->isAdmin;

if (!$isHrManager && !$isAdmin) {
    echo json_encode(array('success' => false, 'message' => 'Access denied'));
    exit;
}

try {
    $employeeID = isset($_GET['employeeID']) ? (int)$_GET['employeeID'] : 0;
    $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

    if ($employeeID === 0) {
        echo json_encode(array('success' => false, 'message' => 'Employee ID required'));
        exit;
    }

    $details = Leave::get_employee_leave_detailed($employeeID, $year, $DBConn);

    echo json_encode(array(
        'success' => true,
        'employeeID' => $employeeID,
        'year' => $year,
        'summary' => $details['summary'],
        'applications' => $details['applications']
    ));

} catch (Exception $e) {
    echo json_encode(array(
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ));
}
?>

