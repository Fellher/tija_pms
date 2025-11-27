<?php
/**
 * Team Calendar Data API
 *
 * Provides calendar data for team leave management
 * Supports both team overview and individual employee calendars
 */

// Include necessary files
session_start();
$base = '../../../../';
set_include_path($base);
include "php/includes.php";

// Set content type to JSON
header('Content-Type: application/json');

// Initialize response
$response = array(
    'success' => false,
    'message' => '',
    'leaves' => array()
);

try {
    // Check if user is logged in
    if (!$isValidUser) {
        throw new Exception('User not logged in');
    }

    // Get request parameters
    $action = isset($_GET['action']) ? Utility::clean_string($_GET['action']) : 'get_team_leave';
    $startDate = isset($_GET['startDate']) ? Utility::clean_string($_GET['startDate']) : date('Y-m-01');
    $endDate = isset($_GET['endDate']) ? Utility::clean_string($_GET['endDate']) : date('Y-m-t');
    $filterType = isset($_GET['filterType']) ? Utility::clean_string($_GET['filterType']) : 'department';
    $filterValue = isset($_GET['filterValue']) ? Utility::clean_string($_GET['filterValue']) : '';
    $employeeID = isset($_GET['employeeID']) ? Utility::clean_string($_GET['employeeID']) : '';
    $leaveType = isset($_GET['leaveType']) ? Utility::clean_string($_GET['leaveType']) : '';
    $entityID = isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : (isset($userDetails->entityID) ? $userDetails->entityID : 1);
    $orgDataID = isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : (isset($userDetails->orgDataID) ? $userDetails->orgDataID : 1);

    // Validate dates
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
        throw new Exception('Invalid date format');
    }

    // Fetch leave applications using class method and custom filtering
    $leaves = getTeamLeaveApplications($filterType, $filterValue, $employeeID, $leaveType,
                                       $entityID, $orgDataID, $startDate, $endDate,
                                       $userDetails, $DBConn);

    if ($leaves) {
        foreach ($leaves as $leave) {
            // Determine color based on status
            $statusColor = getStatusColor($leave->leaveStatusID);

            $response['leaves'][] = array(
                'leaveApplicationID' => $leave->leaveApplicationID,
                'employeeID' => $leave->employeeID,
                'employeeName' => $leave->employeeName,
                'employeeEmail' => $leave->employeeEmail,
                'leaveTypeID' => $leave->leaveTypeID,
                'leaveTypeName' => $leave->leaveTypeName,
                'startDate' => $leave->startDate,
                'endDate' => $leave->endDate,
                'noOfDays' => $leave->noOfDays,
                'leaveReason' => $leave->leaveComments,
                'statusID' => $leave->leaveStatusID,
                'statusName' => $leave->leaveStatusName,
                'statusColor' => $statusColor,
                'departmentName' => $leave->departmentName
            );
        }
    }

    $response['success'] = true;
    $response['message'] = count($response['leaves']) . ' leave applications found';

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Team Calendar Error: ' . $e->getMessage());
}

// Return JSON response
echo json_encode($response);

/**
 * Get team leave applications using class methods
 */
function getTeamLeaveApplications($filterType, $filterValue, $employeeID, $leaveType,
                                  $entityID, $orgDataID, $startDate, $endDate,
                                  $userDetails, $DBConn) {
    // Get list of employee IDs based on filter type
    $employeeIDs = array();

    if ($filterType == 'employee' && $employeeID) {
        // Specific employee
        $employeeIDs = array($employeeID);
    } elseif ($filterType == 'department' && $filterValue) {
        // Specific department
        $employees = Employee::get_department_members($filterValue, $DBConn);
        if ($employees) {
            $employeeIDs = array_map(function($emp) { return $emp->ID; }, $employees);
        }
    } elseif ($filterType == 'department' && !$filterValue && isset($userDetails->businessUnitID)) {
        // User's department
        $employees = Employee::get_department_members($userDetails->businessUnitID, $DBConn);
        if ($employees) {
            $employeeIDs = array_map(function($emp) { return $emp->ID; }, $employees);
        }
    } elseif ($filterType == 'entity') {
        // All employees in entity
        $employees = Employee::get_all_employees($orgDataID, $entityID, $DBConn);
        if ($employees) {
            $employeeIDs = array_map(function($emp) { return $emp->ID; }, $employees);
        }
    } elseif ($filterType == 'organization') {
        // All employees in organization
        $employees = Employee::get_all_employees($orgDataID, $entityID, $DBConn);
        if ($employees) {
            $employeeIDs = array_map(function($emp) { return $emp->ID; }, $employees);
        }
    }

    if (empty($employeeIDs)) {
        return array();
    }

    // Build SQL query to get leave applications with details
    // We need to use SQL here because Leave::leave_applications_full doesn't support
    // date range and multiple employee filtering efficiently
    $placeholders = str_repeat('?,', count($employeeIDs) - 1) . '?';

    $sql = "SELECT la.leaveApplicationID, la.employeeID, la.startDate, la.endDate,
                   la.noOfDays, la.leaveComments, la.leaveStatusID,
                   lt.leaveTypeID, lt.leaveTypeName, lt.leaveTypeDescription,
                   CONCAT(emp.FirstName, ' ', emp.Surname) as employeeName,
                   emp.Email as employeeEmail,
                   bu.businessUnitName as departmentName,
                   ls.leaveStatusName
            FROM tija_leave_applications la
            LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
            LEFT JOIN people emp ON la.employeeID = emp.ID
            LEFT JOIN user_details ud ON emp.ID = ud.ID
            LEFT JOIN tija_business_units bu ON ud.businessUnitID = bu.businessUnitID
            LEFT JOIN tija_leave_status ls ON la.leaveStatusID = ls.leaveStatusID
            WHERE la.employeeID IN ({$placeholders})
            AND la.startDate <= ?
            AND la.endDate >= ?
            AND la.Lapsed = 'N'
            AND la.Suspended = 'N'";

    $params = array();
    foreach ($employeeIDs as $empID) {
        $params[] = array($empID, 'i');
    }
    $params[] = array($endDate, 's');
    $params[] = array($startDate, 's');

    // Add leave type filter if specified
    if ($leaveType) {
        $sql .= " AND la.leaveTypeID = ?";
        $params[] = array($leaveType, 'i');
    }

    $sql .= " ORDER BY la.startDate ASC";

    return $DBConn->fetch_all_rows($sql, $params);
}

/**
 * Get status color based on leave status ID
 */
function getStatusColor($statusID) {
    $statusColors = array(
        1 => '#6c757d',  // Draft - Grey
        2 => '#ffc107',  // Pending - Yellow
        3 => '#28a745',  // Approved - Green
        4 => '#dc3545',  // Rejected - Red
        5 => '#6c757d',  // Cancelled - Grey
        6 => '#17a2b8',  // In Progress - Cyan
        7 => '#28a745'   // Completed - Green
    );

    return $statusColors[$statusID] ?? '#6c757d';
}

function resolveHrManagerEntityScopes($hrManagerScope, $fallbackOrgID, $fallbackEntityID, $DBConn) {
    $resolved = array();

    if (!is_array($hrManagerScope) || empty($hrManagerScope['isHRManager'])) {
        return $resolved;
    }

    $entries = $hrManagerScope['scopes'] ?? array();
    if (empty($entries)) {
        $entries[] = array(
            'entityID' => $fallbackEntityID,
            'orgDataID' => $fallbackOrgID,
            'global' => false
        );
    }

    $processed = array();
    foreach ($entries as $entry) {
        $orgID = $entry['orgDataID'] ?? $fallbackOrgID;
        if (!$orgID) {
            continue;
        }

        if (!empty($entry['global'])) {
            $entities = Data::entities_full(array('orgDataID' => $orgID, 'Suspended' => 'N'), false, $DBConn);
            if ($entities) {
                foreach ($entities as $entityRow) {
                    $key = $orgID . ':' . $entityRow->entityID;
                    if (isset($processed[$key])) {
                        continue;
                    }
                    $processed[$key] = true;
                    $resolved[] = array(
                        'orgDataID' => $orgID,
                        'entityID' => (int)$entityRow->entityID
                    );
                }
            }
            continue;
        }

        $entityID = $entry['entityID'] ?? $fallbackEntityID;
        if (!$entityID) {
            continue;
        }

        $key = $orgID . ':' . $entityID;
        if (isset($processed[$key])) {
            continue;
        }
        $processed[$key] = true;

        $resolved[] = array(
            'orgDataID' => $orgID,
            'entityID' => (int)$entityID
        );
    }

    return $resolved;
}

function collectHrManagedEmployees($hrManagerScope, $fallbackOrgID, $fallbackEntityID, $DBConn) {
    $employees = array();
    $scopes = resolveHrManagerEntityScopes($hrManagerScope, $fallbackOrgID, $fallbackEntityID, $DBConn);

    foreach ($scopes as $scope) {
        $records = Employee::get_all_employees($scope['orgDataID'], $scope['entityID'], $DBConn);
        if ($records) {
            $employees = array_merge($employees, $records);
        }
    }

    return $employees;
}

/**
 * LEGACY FUNCTIONS - Kept for backward compatibility
 * Get team calendar data
 */
function getTeamCalendarData($userDetails, $startDate, $endDate, $DBConn) {
    $data = array(
        'teamMembers' => array(),
        'leaveEvents' => array(),
        'holidays' => array(),
        'summary' => array()
    );

    // Get team members based on user role
    $teamMembers = array();

    if (Employee::is_manager($userDetails->ID, $DBConn)) {
        $teamMembers = Employee::get_team_members($userDetails->ID, $DBConn);
    }

    if (Employee::is_department_head($userDetails->ID, $DBConn)) {
        $departmentMembers = Employee::get_department_members($userDetails->departmentID ?? null, $DBConn);
        $teamMembers = array_merge($teamMembers, $departmentMembers);
    }

    $hrScope = Employee::get_hr_manager_scope($userDetails->ID, $DBConn);
    if (!empty($hrScope['isHRManager'])) {
        $allEmployees = collectHrManagedEmployees($hrScope, $userDetails->orgDataID ?? null, $userDetails->entityID ?? null, $DBConn);
        if ($allEmployees) {
            $teamMembers = array_merge($teamMembers, $allEmployees);
        }
    }

    // Remove duplicates
    $teamMembers = array_unique($teamMembers, SORT_REGULAR);

    // Get leave events for team members
    $leaveEvents = getTeamLeaveEvents($teamMembers, $startDate, $endDate, $DBConn);

    // Get holidays
    $holidays = Leave::get_global_holidays('Kenya', null, $DBConn);

    // Calculate summary statistics
    $summary = calculateTeamSummary($teamMembers, $leaveEvents, $startDate, $endDate, $DBConn);

    return array(
        'teamMembers' => $teamMembers,
        'leaveEvents' => $leaveEvents,
        'holidays' => $holidays,
        'summary' => $summary,
        'dateRange' => array(
            'start' => $startDate,
            'end' => $endDate
        )
    );
}

/**
 * Get individual employee calendar data
 */
function getEmployeeCalendarData($employeeID, $startDate, $endDate, $userDetails, $DBConn) {
    // Verify user has permission to view this employee's calendar
    if (!hasPermissionToViewEmployee($employeeID, $userDetails, $DBConn)) {
        throw new Exception('Permission denied to view employee calendar');
    }

    $employee = Employee::employees(array('ID' => $employeeID), true, $DBConn);
    if (!$employee) {
        throw new Exception('Employee not found');
    }

    // Get employee's leave events
    $leaveEvents = getEmployeeLeaveEvents($employeeID, $startDate, $endDate, $DBConn);

    // Get employee's leave balances
    $leaveBalances = Leave::calculate_leave_balances($employeeID, $employee->entityID ?? 1, $DBConn);

    // Get holidays
    $holidays = Leave::get_global_holidays('Kenya', null, $DBConn);

    return array(
        'employee' => $employee,
        'leaveEvents' => $leaveEvents,
        'leaveBalances' => $leaveBalances,
        'holidays' => $holidays,
        'dateRange' => array(
            'start' => $startDate,
            'end' => $endDate
        )
    );
}

/**
 * Get team members list
 */
function getTeamMembers($userDetails, $DBConn) {
    $teamMembers = array();

    if (Employee::is_manager($userDetails->ID, $DBConn)) {
        $teamMembers = Employee::get_team_members($userDetails->ID, $DBConn);
    }

    if (Employee::is_department_head($userDetails->ID, $DBConn)) {
        $departmentMembers = Employee::get_department_members($userDetails->departmentID ?? null, $DBConn);
        $teamMembers = array_merge($teamMembers, $departmentMembers);
    }

    $hrScope = Employee::get_hr_manager_scope($userDetails->ID, $DBConn);
    if (!empty($hrScope['isHRManager'])) {
        $allEmployees = collectHrManagedEmployees($hrScope, $userDetails->orgDataID ?? null, $userDetails->entityID ?? null, $DBConn);
        if ($allEmployees) {
            $teamMembers = array_merge($teamMembers, $allEmployees);
        }
    }

    // Remove duplicates and format for calendar
    $teamMembers = array_unique($teamMembers, SORT_REGULAR);
    $formattedMembers = array();

    foreach ($teamMembers as $member) {
        $formattedMembers[] = array(
            'id' => $member->ID,
            'name' => $member->FirstName . ' ' . $member->Surname,
            'jobTitle' => $member->jobTitle ?? 'Employee',
            'email' => $member->Email,
            'initials' => strtoupper(substr($member->FirstName, 0, 1) . substr($member->Surname, 0, 1))
        );
    }

    return $formattedMembers;
}

/**
 * Get calendar events for team or individual
 */
function getCalendarEvents($userDetails, $startDate, $endDate, $employeeID = null, $DBConn) {
    if ($employeeID) {
        // Individual employee events
        return getEmployeeLeaveEvents($employeeID, $startDate, $endDate, $DBConn);
    } else {
        // Team events
        $teamMembers = array();

        if (Employee::is_manager($userDetails->ID, $DBConn)) {
            $teamMembers = Employee::get_team_members($userDetails->ID, $DBConn);
        }

        if (Employee::is_department_head($userDetails->ID, $DBConn)) {
            $departmentMembers = Employee::get_department_members($userDetails->departmentID ?? null, $DBConn);
            $teamMembers = array_merge($teamMembers, $departmentMembers);
        }

        $hrScope = Employee::get_hr_manager_scope($userDetails->ID, $DBConn);
        if (!empty($hrScope['isHRManager'])) {
            $allEmployees = collectHrManagedEmployees($hrScope, $userDetails->orgDataID ?? null, $userDetails->entityID ?? null, $DBConn);
            if ($allEmployees) {
                $teamMembers = array_merge($teamMembers, $allEmployees);
            }
        }

        $teamMembers = array_unique($teamMembers, SORT_REGULAR);
        return getTeamLeaveEvents($teamMembers, $startDate, $endDate, $DBConn);
    }
}

/**
 * Get team leave events
 */
function getTeamLeaveEvents($teamMembers, $startDate, $endDate, $DBConn) {
    if (empty($teamMembers)) {
        return array();
    }

    $teamMemberIDs = array_column($teamMembers, 'ID');
    $placeholders = str_repeat('?,', count($teamMemberIDs) - 1) . '?';

    $sql = "SELECT la.leaveApplicationID, la.employeeID, la.startDate, la.endDate,
                   la.noOfDays, la.leaveComments, la.leaveStatusID,
                   lt.leaveTypeName, lt.leaveTypeDescription,
                   CONCAT(emp.FirstName, ' ', emp.Surname) as employeeName,
                   jt.jobTitle,
                   ls.leaveStatusName
            FROM tija_leave_applications la
            LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
            LEFT JOIN people emp ON la.employeeID = emp.ID
            LEFT JOIN user_details ud ON emp.ID = ud.ID
            LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
            LEFT JOIN tija_leave_status ls ON la.leaveStatusID = ls.leaveStatusID
            WHERE la.employeeID IN ({$placeholders})
            AND la.startDate <= ?
            AND la.endDate >= ?
            AND la.Lapsed = 'N'
            AND la.Suspended = 'N'
            ORDER BY la.startDate ASC";

    $params = array();
    foreach ($teamMemberIDs as $id) {
        $params[] = array($id, 'i');
    }
    $params[] = array($endDate, 's');
    $params[] = array($startDate, 's');

    $rows = $DBConn->fetch_all_rows($sql, $params);

    if (!$rows) {
        return array();
    }

    // Format events for calendar
    $events = array();
    foreach ($rows as $row) {
        $events[] = array(
            'id' => $row->leaveApplicationID,
            'title' => $row->employeeName . ' - ' . $row->leaveTypeName,
            'start' => $row->startDate,
            'end' => date('Y-m-d', strtotime($row->endDate . ' +1 day')), // FullCalendar expects exclusive end date
            'allDay' => true,
            'employeeID' => $row->employeeID,
            'employeeName' => $row->employeeName,
            'leaveType' => $row->leaveTypeName,
            'leaveTypeDescription' => $row->leaveTypeDescription,
            'noOfDays' => $row->noOfDays,
            'status' => $row->leaveStatusName,
            'statusID' => $row->leaveStatusID,
            'comments' => $row->leaveComments,
            'color' => getLeaveTypeColor($row->leaveTypeName, $row->leaveStatusID),
            'textColor' => '#ffffff'
        );
    }

    return $events;
}

/**
 * Get individual employee leave events
 */
function getEmployeeLeaveEvents($employeeID, $startDate, $endDate, $DBConn) {
    $sql = "SELECT la.leaveApplicationID, la.employeeID, la.startDate, la.endDate,
                   la.noOfDays, la.leaveComments, la.leaveStatusID,
                   lt.leaveTypeName, lt.leaveTypeDescription,
                   CONCAT(emp.FirstName, ' ', emp.Surname) as employeeName,
                   jt.jobTitle,
                   ls.leaveStatusName
            FROM tija_leave_applications la
            LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
            LEFT JOIN people emp ON la.employeeID = emp.ID
            LEFT JOIN user_details ud ON emp.ID = ud.ID
            LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
            LEFT JOIN tija_leave_status ls ON la.leaveStatusID = ls.leaveStatusID
            WHERE la.employeeID = ?
            AND la.startDate <= ?
            AND la.endDate >= ?
            AND la.Lapsed = 'N'
            AND la.Suspended = 'N'
            ORDER BY la.startDate ASC";

    $params = array(
        array($employeeID, 'i'),
        array($endDate, 's'),
        array($startDate, 's')
    );

    $rows = $DBConn->fetch_all_rows($sql, $params);

    if (!$rows) {
        return array();
    }

    // Format events for calendar
    $events = array();
    foreach ($rows as $row) {
        $events[] = array(
            'id' => $row->leaveApplicationID,
            'title' => $row->leaveTypeName,
            'start' => $row->startDate,
            'end' => date('Y-m-d', strtotime($row->endDate . ' +1 day')),
            'allDay' => true,
            'employeeID' => $row->employeeID,
            'employeeName' => $row->employeeName,
            'leaveType' => $row->leaveTypeName,
            'leaveTypeDescription' => $row->leaveTypeDescription,
            'noOfDays' => $row->noOfDays,
            'status' => $row->leaveStatusName,
            'statusID' => $row->leaveStatusID,
            'comments' => $row->leaveComments,
            'color' => getLeaveTypeColor($row->leaveTypeName, $row->leaveStatusID),
            'textColor' => '#ffffff'
        );
    }

    return $events;
}

/**
 * Calculate team summary statistics
 */
function calculateTeamSummary($teamMembers, $leaveEvents, $startDate, $endDate, $DBConn) {
    $summary = array(
        'totalMembers' => count($teamMembers),
        'onLeaveToday' => 0,
        'onLeaveThisWeek' => 0,
        'onLeaveThisMonth' => 0,
        'pendingApprovals' => 0,
        'leaveTypes' => array(),
        'monthlyStats' => array()
    );

    $currentDate = date('Y-m-d');
    $weekStart = date('Y-m-d', strtotime('monday this week'));
    $weekEnd = date('Y-m-d', strtotime('sunday this week'));
    $monthStart = date('Y-m-01');
    $monthEnd = date('Y-m-t');

    // Count leave types
    $leaveTypeCounts = array();
    foreach ($leaveEvents as $event) {
        $leaveType = $event['leaveType'];
        if (!isset($leaveTypeCounts[$leaveType])) {
            $leaveTypeCounts[$leaveType] = 0;
        }
        $leaveTypeCounts[$leaveType]++;
    }
    $summary['leaveTypes'] = $leaveTypeCounts;

    // Count members on leave
    foreach ($leaveEvents as $event) {
        $eventStart = $event['start'];
        $eventEnd = date('Y-m-d', strtotime($event['end'] . ' -1 day'));

        // Today
        if ($eventStart <= $currentDate && $eventEnd >= $currentDate) {
            $summary['onLeaveToday']++;
        }

        // This week
        if ($eventStart <= $weekEnd && $eventEnd >= $weekStart) {
            $summary['onLeaveThisWeek']++;
        }

        // This month
        if ($eventStart <= $monthEnd && $eventEnd >= $monthStart) {
            $summary['onLeaveThisMonth']++;
        }
    }

    // Count pending approvals
    $teamMemberIDs = array_column($teamMembers, 'ID');
    if (!empty($teamMemberIDs)) {
        $placeholders = str_repeat('?,', count($teamMemberIDs) - 1) . '?';
        $sql = "SELECT COUNT(*) as pendingCount
                FROM tija_leave_applications
                WHERE employeeID IN ({$placeholders})
                AND leaveStatusID = 3
                AND Lapsed = 'N'
                AND Suspended = 'N'";

        $params = array();
        foreach ($teamMemberIDs as $id) {
            $params[] = array($id, 'i');
        }

        $result = $DBConn->fetch_all_rows($sql, $params);
        $summary['pendingApprovals'] = $result ? $result[0]->pendingCount : 0;
    }

    return $summary;
}

/**
 * Get leave type color based on type and status
 */
function getLeaveTypeColor($leaveTypeName, $statusID) {
    $colors = array(
        'Annual' => '#007bff',
        'Vacation' => '#007bff',
        'Sick' => '#28a745',
        'Medical' => '#28a745',
        'Emergency' => '#dc3545',
        'Maternity' => '#6f42c1',
        'Paternity' => '#6f42c1',
        'Study' => '#17a2b8',
        'Personal' => '#ffc107'
    );

    $baseColor = $colors[$leaveTypeName] ?? '#6c757d';

    // Adjust color based on status
    switch ($statusID) {
        case 1: // Draft
            return '#6c757d';
        case 2: // Submitted
            return '#ffc107';
        case 3: // Pending
            return '#fd7e14';
        case 4: // Approved
            return $baseColor;
        case 5: // Rejected
            return '#dc3545';
        default:
            return $baseColor;
    }
}

/**
 * Check if user has permission to view employee calendar
 */
function hasPermissionToViewEmployee($employeeID, $userDetails, $DBConn) {
    // HR managers can view all employees
    $hrScope = Employee::get_hr_manager_scope($userDetails->ID, $DBConn);
    if (!empty($hrScope['isHRManager'])) {
        $targetEmployee = Employee::employees(array('ID' => $employeeID), true, $DBConn);
        $targetEntityID = $targetEmployee ? ($targetEmployee->entityID ?? null) : null;
        if (Employee::is_hr_manager($userDetails->ID, $DBConn, $targetEntityID)) {
            return true;
        }
    }

    // Managers can view their direct reports
    if (Employee::is_manager($userDetails->ID, $DBConn)) {
        $teamMembers = Employee::get_team_members($userDetails->ID, $DBConn);
        $teamMemberIDs = array_column($teamMembers, 'ID');
        return in_array($employeeID, $teamMemberIDs);
    }

    // Department heads can view department members
    if (Employee::is_department_head($userDetails->ID, $DBConn)) {
        $departmentMembers = Employee::get_department_members($userDetails->departmentID ?? null, $DBConn);
        $departmentMemberIDs = array_column($departmentMembers, 'ID');
        return in_array($employeeID, $departmentMemberIDs);
    }

    // Users can view their own calendar
    return $employeeID == $userDetails->ID;
}

?>
