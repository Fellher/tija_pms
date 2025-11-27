<?php
/**
 * Check Leave Overlap
 *
 * AJAX endpoint to check if a leave application overlaps with existing applications
 * for the same employee
 */

session_start();
$base = '../../../../';
set_include_path($base);
include 'php/includes.php';

header('Content-Type: application/json');

if (!$isValidUser) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : '';

    if ($action !== 'check_overlap') {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
    }

    $employeeId = isset($_POST['employeeId']) ? (int)$_POST['employeeId'] : 0;
    $entityId = isset($_POST['entityId']) ? (int)$_POST['entityId'] : 0;
    $startDate = isset($_POST['startDate']) ? Utility::clean_string($_POST['startDate']) : '';
    $endDate = isset($_POST['endDate']) ? Utility::clean_string($_POST['endDate']) : '';

    // Validate inputs
    if (empty($employeeId) || empty($entityId) || empty($startDate) || empty($endDate)) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit;
    }

    // Validate dates
    $startDateTime = new DateTime($startDate);
    $endDateTime = new DateTime($endDate);

    if ($endDateTime < $startDateTime) {
        echo json_encode(['success' => false, 'message' => 'End date cannot be before start date']);
        exit;
    }

    // Check for overlapping leave applications
    // Only check applications that are not rejected (status 4) or cancelled (status 5)
    $overlapCheckSQL = "SELECT la.leaveApplicationID, la.startDate, la.endDate,
                               lt.leaveTypeName, ls.leaveStatusName, la.leaveStatusID
                        FROM tija_leave_applications la
                        LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
                        LEFT JOIN tija_leave_status ls ON la.leaveStatusID = ls.leaveStatusID
                        WHERE la.employeeID = ?
                        AND la.entityID = ?
                        AND la.leaveStatusID NOT IN (4, 5) -- Exclude rejected and cancelled
                        AND la.Lapsed = 'N'
                        AND la.Suspended = 'N'
                        AND (
                            -- Overlap occurs when: existing_start <= new_end AND existing_end >= new_start
                            (la.startDate <= ? AND la.endDate >= ?)
                        )
                        ORDER BY la.startDate ASC
                        LIMIT 10";

    $overlappingApplications = $DBConn->fetch_all_rows($overlapCheckSQL, array(
        array($employeeId, 'i'),
        array($entityId, 'i'),
        array($endDate, 's'),   // existing_start <= new_end
        array($startDate, 's')  // existing_end >= new_start
    ));

    if ($overlappingApplications && count($overlappingApplications) > 0) {
        // Convert to array format for JSON response
        $overlapList = array();
        foreach ($overlappingApplications as $overlap) {
            $overlap = is_object($overlap) ? (array)$overlap : $overlap;
            $overlapList[] = array(
                'leaveApplicationID' => isset($overlap['leaveApplicationID']) ? (int)$overlap['leaveApplicationID'] : 0,
                'startDate' => $overlap['startDate'] ?? '',
                'endDate' => $overlap['endDate'] ?? '',
                'leaveTypeName' => $overlap['leaveTypeName'] ?? 'Leave',
                'leaveStatusName' => $overlap['leaveStatusName'] ?? 'Unknown',
                'leaveStatusID' => isset($overlap['leaveStatusID']) ? (int)$overlap['leaveStatusID'] : 0
            );
        }

        echo json_encode([
            'success' => false,
            'hasOverlap' => true,
            'overlappingApplications' => $overlapList,
            'message' => 'Overlapping leave applications found'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'hasOverlap' => false,
            'message' => 'No overlapping applications found'
        ]);
    }

} catch (Exception $e) {
    error_log("Check leave overlap error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error checking for overlapping applications: ' . $e->getMessage()
    ]);
}

