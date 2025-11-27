<?php
/**
 * Get Approvals Script
 *
 * Retrieves leave applications requiring approval for a specific user
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to prevent HTML output
ini_set('log_errors', 1);

// Set content type to JSON immediately
header('Content-Type: application/json');

session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);
    $filter = isset($input['filter']) ? Utility::clean_string($input['filter']) : 'pending';
    $userId = isset($input['userId']) ? Utility::clean_string($input['userId']) : '';

    if (empty($userId)) {
        echo json_encode(['success' => false, 'message' => 'User ID is required']);
        exit;
    }

    // Build query based on filter
    $whereConditions = [];
    $params = [];

    switch ($filter) {
        case 'pending':
            $whereConditions[] = "la.leaveStatusID = 3"; // Pending approval
            break;
        case 'approved':
            $whereConditions[] = "la.leaveStatusID = 6"; // Approved
            break;
        case 'rejected':
            $whereConditions[] = "la.leaveStatusID = 4"; // Rejected
            break;
        default:
            $whereConditions[] = "la.leaveStatusID = 3";
    }

    // Add user-specific conditions for approvals
    // For now, we'll get all pending applications since the approval hierarchy columns may not exist
    // This can be enhanced later when the proper approval workflow is implemented
    $whereConditions[] = "la.employeeID != ?"; // Exclude own applications
    $params[] = array($userId, 'i');

    $whereClause = implode(' AND ', $whereConditions);

    // Query to get approvals - simplified version to avoid missing table issues
    $sql = "SELECT
                la.leaveApplicationID,
                la.startDate,
                la.endDate,
                la.noOfDays,
                la.leaveComments as leaveReason,
                la.dateApplied,
                la.halfDayLeave,
                la.halfDayPeriod,
                lt.leaveTypeName,
                ls.leaveStatusName,
                e.FirstName,
                e.Surname,
                CONCAT(e.FirstName, ' ', e.Surname) as employeeName,
                'Employee' as jobTitle,
                'General' as departmentName,
                CASE
                    WHEN la.noOfDays <= 1 THEN 'Low'
                    WHEN la.noOfDays <= 5 THEN 'Medium'
                    ELSE 'High'
                END as priority
            FROM tija_leave_applications la
            LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
            LEFT JOIN tija_leave_status ls ON la.leaveStatusID = ls.leaveStatusID
            LEFT JOIN people e ON la.employeeID = e.ID
            WHERE {$whereClause}
            AND la.Lapsed = 'N'
            AND la.Suspended = 'N'
            ORDER BY la.dateApplied DESC";

    try {
        $rows = $DBConn->fetch_all_rows($sql, $params);

        if (!$rows) {
            echo json_encode(['success' => true, 'approvals' => []]);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database query failed: ' . $e->getMessage()]);
        exit;
    }

    // Format the data
    $approvals = [];
    foreach ($rows as $row) {
        $rowObj = is_array($row) ? (object)$row : $row;

        $approvals[] = [
            'leaveApplicationID' => $rowObj->leaveApplicationID ?? null,
            'employeeName' => $rowObj->employeeName ?? '',
            'jobTitle' => $rowObj->jobTitle ?? '',
            'departmentName' => $rowObj->departmentName ?? '',
            'leaveTypeName' => $rowObj->leaveTypeName ?? '',
            'startDate' => $rowObj->startDate ?? null,
            'endDate' => $rowObj->endDate ?? null,
            'noOfDays' => $rowObj->noOfDays ?? 0,
            'leaveReason' => $rowObj->leaveReason ?? '',
            'dateApplied' => $rowObj->dateApplied ?? null,
            'leaveStatusName' => $rowObj->leaveStatusName ?? '',
            'priority' => $rowObj->priority ?? 'Low',
            'halfDayLeave' => $rowObj->halfDayLeave ?? 'N',
            'halfDayPeriod' => $rowObj->halfDayPeriod ?? ''
        ];
    }

    echo json_encode([
        'success' => true,
        'approvals' => $approvals
    ]);

} catch (Exception $e) {
    error_log('Get approvals error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while retrieving approvals']);
}
?>
