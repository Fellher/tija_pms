<?php
/**
 * Get Filterable Employees for Calendar
 *
 * Returns list of employees that the current user can view in the calendar
 * Based on permissions: team members, all employees (if admin), or department members
 */

// Suppress warnings/notices that might break JSON output
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

// Include necessary files
$base = '../../../../';
set_include_path($base);
include 'php/includes.php';

// Set JSON header early
header('Content-Type: application/json');

if (!$isValidUser) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized', 'employees' => []]);
    exit;
}

$employeeID = $userDetails->ID;
$entityID = $userDetails->entityID ?? 1;
$orgDataID = $userDetails->orgDataID ?? 1;
$departmentID = $userDetails->businessUnitID ?? null;

// Get filter type from request (optional)
$filterType = isset($_GET['filterType']) ? $_GET['filterType'] : 'team';

$employees = [];

try {
    switch ($filterType) {
        case 'team':
            // Get team members/subordinates
            $teamMembers = Employee::get_team_members($employeeID, $DBConn);
            if ($teamMembers && is_array($teamMembers)) {
                $employees = $teamMembers;
            }
            // Also include current user
            $currentUser = Employee::employees(array('ID' => $employeeID), true, $DBConn);
            if ($currentUser) {
                array_unshift($employees, $currentUser);
            }
            break;

        case 'department':
            // Get department members
            if ($departmentID) {
                $deptMembers = Employee::get_department_members($departmentID, $DBConn);
                if ($deptMembers && is_array($deptMembers)) {
                    $employees = $deptMembers;
                }
            }
            break;

        case 'all':
            // Get all employees (if user has permission)
            if ($isAdmin || $isValidAdmin || $isHRManager) {
                $allEmployees = Employee::get_all_employees($orgDataID, $entityID, $DBConn);
                if ($allEmployees && is_array($allEmployees)) {
                    $employees = $allEmployees;
                }
            } else {
                // Fallback to team members if not admin
                $teamMembers = Employee::get_team_members($employeeID, $DBConn);
                if ($teamMembers && is_array($teamMembers)) {
                    $employees = $teamMembers;
                }
            }
            break;

        default:
            // Default to team members
            $teamMembers = Employee::get_team_members($employeeID, $DBConn);
            if ($teamMembers && is_array($teamMembers)) {
                $employees = $teamMembers;
            }
            $currentUser = Employee::employees(array('ID' => $employeeID), true, $DBConn);
            if ($currentUser) {
                array_unshift($employees, $currentUser);
            }
            break;
    }

    // Format employees for response
    $formattedEmployees = [];
    foreach ($employees as $emp) {
        $formattedEmployees[] = [
            'id' => $emp->ID,
            'name' => trim(($emp->FirstName ?? '') . ' ' . ($emp->Surname ?? '')),
            'firstName' => $emp->FirstName ?? '',
            'surname' => $emp->Surname ?? '',
            'email' => $emp->Email ?? '',
            'jobTitle' => $emp->jobTitle ?? 'N/A',
            'department' => $emp->businessUnitName ?? 'N/A',
            'profileImage' => $emp->profile_image ?? null
        ];
    }

    echo json_encode([
        'success' => true,
        'employees' => $formattedEmployees,
        'count' => count($formattedEmployees),
        'filterType' => $filterType
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

} catch (Exception $e) {
    error_log('Error fetching filterable employees: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching employees',
        'employees' => []
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
}

