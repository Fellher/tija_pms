<?php
/**
 * Get Leave Applications for Calendar View
 *
 * Returns leave applications for the current user and their team members/subordinates
 * for display on the calendar
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
    echo json_encode(['success' => false, 'message' => 'Unauthorized', 'events' => []]);
    exit;
}

$employeeID = $userDetails->ID;
$entityID = $userDetails->entityID ?? 1;
$orgDataID = $userDetails->orgDataID ?? 1;

// Get date range from request
$startDate = isset($_GET['start']) ? $_GET['start'] : date('Y-m-01'); // First day of current month
$endDate = isset($_GET['end']) ? $_GET['end'] : date('Y-m-t'); // Last day of current month

// Get filter parameters
$employeeIDs = isset($_GET['employeeIDs']) ? $_GET['employeeIDs'] : null;
$leaveTypeIDs = isset($_GET['leaveTypeIDs']) ? $_GET['leaveTypeIDs'] : null;
$statusIDs = isset($_GET['statusIDs']) ? $_GET['statusIDs'] : null;

// Parse employeeIDs if provided as comma-separated string or JSON array
if ($employeeIDs) {
    if (is_string($employeeIDs)) {
        // Try to decode as JSON first
        $decoded = json_decode($employeeIDs, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $teamMemberIDs = array_map('intval', $decoded);
        } else {
            // Treat as comma-separated string
            $teamMemberIDs = array_map('intval', explode(',', $employeeIDs));
        }
    } elseif (is_array($employeeIDs)) {
        $teamMemberIDs = array_map('intval', $employeeIDs);
    } else {
        $teamMemberIDs = [$employeeID]; // Fallback to current user
    }
    // Remove duplicates and ensure current user is included
    $teamMemberIDs = array_unique($teamMemberIDs);
    if (!in_array($employeeID, $teamMemberIDs)) {
        $teamMemberIDs[] = $employeeID;
    }
} else {
    // Default: Get team members/subordinates
    $teamMembers = Employee::get_team_members($employeeID, $DBConn);
    $teamMemberIDs = [$employeeID]; // Include current user

    if ($teamMembers && is_array($teamMembers)) {
        foreach ($teamMembers as $member) {
            $teamMemberIDs[] = $member->ID;
        }
    }

    // Remove duplicates
    $teamMemberIDs = array_unique($teamMemberIDs);
}

// Build SQL query with filters
$placeholders = str_repeat('?,', count($teamMemberIDs) - 1) . '?';
$whereConditions = [];
$params = [[$employeeID, 'i']]; // For CASE statement

// Employee filter
$whereConditions[] = "la.employeeID IN ($placeholders)";
foreach ($teamMemberIDs as $id) {
    $params[] = [$id, 'i'];
}

// Date range filter
$whereConditions[] = "la.startDate <= ?";
$params[] = [$endDate, 's'];
$whereConditions[] = "la.endDate >= ?";
$params[] = [$startDate, 's'];

// Leave type filter
if ($leaveTypeIDs) {
    if (is_string($leaveTypeIDs)) {
        $decoded = json_decode($leaveTypeIDs, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $typeIDs = array_map('intval', $decoded);
        } else {
            $typeIDs = array_map('intval', explode(',', $leaveTypeIDs));
        }
    } elseif (is_array($leaveTypeIDs)) {
        $typeIDs = array_map('intval', $leaveTypeIDs);
    } else {
        $typeIDs = [];
    }

    if (!empty($typeIDs)) {
        $typePlaceholders = str_repeat('?,', count($typeIDs) - 1) . '?';
        $whereConditions[] = "la.leaveTypeID IN ($typePlaceholders)";
        foreach ($typeIDs as $typeID) {
            $params[] = [$typeID, 'i'];
        }
    }
}

// Status filter
if ($statusIDs) {
    if (is_string($statusIDs)) {
        $decoded = json_decode($statusIDs, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $statIDs = array_map('intval', $decoded);
        } else {
            $statIDs = array_map('intval', explode(',', $statusIDs));
        }
    } elseif (is_array($statusIDs)) {
        $statIDs = array_map('intval', $statusIDs);
    } else {
        $statIDs = [];
    }

    if (!empty($statIDs)) {
        $statPlaceholders = str_repeat('?,', count($statIDs) - 1) . '?';
        $whereConditions[] = "la.leaveStatusID IN ($statPlaceholders)";
        foreach ($statIDs as $statID) {
            $params[] = [$statID, 'i'];
        }
    }
}

// Standard filters
$whereConditions[] = "la.Lapsed = 'N'";
$whereConditions[] = "la.Suspended = 'N'";

$whereClause = "WHERE " . implode(" AND ", $whereConditions);

$sql = "SELECT
            la.leaveApplicationID,
            la.employeeID,
            la.startDate,
            la.endDate,
            la.noOfDays,
            la.leaveStatusID,
            la.leaveTypeID,
            la.leaveComments,
            la.halfDayLeave,
            la.halfDayPeriod,
            CONCAT(p.FirstName, ' ', p.Surname) as employeeName,
            p.profile_image as employeeImage,
            lt.leaveTypeName,
            ls.leaveStatusName,
            ls.leaveStatusColor,
            CASE
                WHEN la.employeeID = ? THEN 'own'
                ELSE 'team'
            END as applicationType
        FROM tija_leave_applications la
        LEFT JOIN people p ON la.employeeID = p.ID
        LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
        LEFT JOIN tija_leave_status ls ON la.leaveStatusID = ls.leaveStatusID
        $whereClause
        ORDER BY la.startDate ASC";

try {
    $applications = $DBConn->fetch_all_rows($sql, $params);
} catch (Exception $e) {
    // Log error but return empty events array
    error_log('Error fetching calendar leave applications: ' . $e->getMessage());
    $applications = [];
}

// Format for calendar
$events = [];
if ($applications && is_array($applications) && count($applications) > 0) {
    foreach ($applications as $app) {
        $isOwn = ($app->employeeID == $employeeID);
        $statusColor = $app->leaveStatusColor ?? '#6c757d';

        // Determine event color based on status and ownership
        if ($isOwn) {
            $eventColor = $statusColor;
            $eventTextColor = '#ffffff';
        } else {
            // Team member applications - use lighter color
            $eventColor = $statusColor . '80'; // Add transparency
            $eventTextColor = '#000000';
        }

        // Handle half day
        $title = $isOwn
            ? $app->leaveTypeName
            : $app->employeeName . ' - ' . $app->leaveTypeName;

        if ($app->halfDayLeave == 'Y') {
            $title .= ' (Half Day)';
        }

        $events[] = [
            'id' => $app->leaveApplicationID,
            'title' => $title,
            'start' => $app->startDate,
            'end' => date('Y-m-d', strtotime($app->endDate . ' +1 day')), // FullCalendar uses exclusive end dates
            'allDay' => true,
            'backgroundColor' => $eventColor,
            'borderColor' => $statusColor,
            'textColor' => $eventTextColor,
            'extendedProps' => [
                'employeeID' => $app->employeeID,
                'employeeName' => $app->employeeName,
                'employeeImage' => $app->employeeImage,
                'leaveTypeName' => $app->leaveTypeName,
                'leaveStatusName' => $app->leaveStatusName,
                'noOfDays' => $app->noOfDays,
                'leaveComments' => $app->leaveComments,
                'halfDayLeave' => $app->halfDayLeave,
                'halfDayPeriod' => $app->halfDayPeriod,
                'applicationType' => $app->applicationType,
                'isOwn' => $isOwn
            ]
        ];
    }
}

// Ensure events is always an array
if (!is_array($events)) {
    $events = [];
}

echo json_encode([
    'success' => true,
    'events' => $events,
    'teamMemberCount' => count($teamMemberIDs) - 1 // Exclude current user
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

