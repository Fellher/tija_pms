<?php
/**
 * Get Employee Leave Details
 * Fetches comprehensive leave information for a specific employee
 *
 * Returns:
 * - Summary statistics
 * - Leave type breakdown
 * - Application history
 */

header('Content-Type: application/json');

session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';

try {
    // Verify user is logged in
    if (!$isValidUser) {
        throw new Exception("Unauthorized access");
    }

    // Get employee ID
    $employeeID = isset($_GET['employeeID']) ? Utility::clean_string($_GET['employeeID']) : null;

    if (!$employeeID) {
        throw new Exception("Employee ID is required");
    }

    // Get employee details
    $employee = Employee::employees(['ID' => $employeeID], true, $DBConn);

    if (!$employee) {
        throw new Exception("Employee not found");
    }

    // Initialize summary
    $summary = [
        'totalEntitlement' => 0,
        'totalTaken' => 0,
        'totalPending' => 0,
        'totalScheduled' => 0,
        'totalRejected' => 0,
        'totalCancelled' => 0,
        'totalAvailable' => 0,
        'overallUtilization' => 0
    ];

    // Get all leave types
    $leaveTypes = Leave::leave_types(['Lapsed' => 'N'], false, $DBConn);
    $leaveTypeBreakdown = [];

    if ($leaveTypes && is_array($leaveTypes)) {
        foreach ($leaveTypes as $leaveType) {
            // Get entitlement
            $entitlement = Leave::leave_entitlement([
                'entityID' => $employee->entityID,
                'leaveTypeID' => $leaveType->leaveTypeID
            ], true, $DBConn);

            if (!$entitlement) continue;

            $totalEnt = $entitlement->entitlement ?? 0;

            // Get all applications for this leave type
            $applications = Leave::leave_applications([
                'employeeID' => $employeeID,
                'leaveTypeID' => $leaveType->leaveTypeID,
                'Lapsed' => 'N'
            ], false, $DBConn);

            // Calculate days by status
            $taken = 0;
            $pending = 0;
            $scheduled = 0;
            $rejected = 0;
            $cancelled = 0;

            if ($applications && is_array($applications)) {
                foreach ($applications as $app) {
                    $days = $app->noOfDays ?: Leave::countWeekdays($app->startDate, $app->endDate);

                    // Status mapping with date consideration:
                    // 1 = Draft/Scheduled
                    // 2 = Submitted
                    // 3 = Under Review/Pending
                    // 4 = Rejected
                    // 5 = Cancelled
                    // 6 = Approved

                    switch ($app->leaveStatusID) {
                        case 1:
                            // Draft - count as scheduled if future
                            $scheduled += $days;
                            break;
                        case 2:
                        case 3:
                            // Submitted/Pending - count as pending
                            $pending += $days;
                            break;
                        case 4:
                            // Rejected - count separately
                            $rejected += $days;
                            break;
                        case 5:
                            // Cancelled - count separately but don't affect balance
                            $cancelled += $days;
                            break;
                        case 6:
                            // Approved - distinguish between past (taken) and future (scheduled)
                            if (strtotime($app->startDate) > time()) {
                                // Future approved leave = scheduled
                                $scheduled += $days;
                            } else {
                                // Past or current approved leave = taken
                                $taken += $days;
                            }
                            break;
                    }
                }
            }

            $available = max(0, $totalEnt - ($taken + $pending + $scheduled));
            $utilization = $totalEnt > 0 ? round(($taken / $totalEnt) * 100, 1) : 0;

            $leaveTypeBreakdown[] = [
                'name' => $leaveType->leaveTypeName,
                'code' => $leaveType->leaveTypeCode ?? '',
                'color' => $leaveType->leaveColor ?? '#6c757d',
                'entitlement' => $totalEnt,
                'taken' => $taken,
                'pending' => $pending,
                'scheduled' => $scheduled,
                'rejected' => $rejected,
                'cancelled' => $cancelled,
                'available' => $available,
                'utilization' => $utilization
            ];

            // Add to summary
            $summary['totalEntitlement'] += $totalEnt;
            $summary['totalTaken'] += $taken;
            $summary['totalPending'] += $pending;
            $summary['totalScheduled'] += $scheduled;
            $summary['totalRejected'] += $rejected;
            $summary['totalCancelled'] += $cancelled;
            $summary['totalAvailable'] += $available;
        }
    }

    // Calculate overall utilization
    $summary['overallUtilization'] = $summary['totalEntitlement'] > 0 ?
        round(($summary['totalTaken'] / $summary['totalEntitlement']) * 100, 1) : 0;

    // Get all leave applications with full details
    $allApplications = Leave::leave_applications_full([
        'employeeID' => $employeeID,
        'Lapsed' => 'N'
    ], false, $DBConn);

    $applicationHistory = [];
    if ($allApplications && is_array($allApplications)) {
        foreach ($allApplications as $app) {
            $applicationHistory[] = [
                'applicationID' => $app->leaveApplicationID,
                'dateAdded' => $app->DateAdded,
                'leaveTypeName' => $app->leaveTypeName,
                'leaveColor' => $app->leaveColor ?? '#6c757d',
                'leavePeriodName' => $app->leavePeriodName ?? 'N/A',
                'startDate' => $app->startDate,
                'endDate' => $app->endDate,
                'days' => $app->noOfDays ?: Leave::countWeekdays($app->startDate, $app->endDate),
                'statusID' => $app->leaveStatusID,
                'statusName' => $app->leaveStatusName,
                'comments' => $app->leaveComments ?? ''
            ];
        }

        // Sort by date added (newest first)
        usort($applicationHistory, function($a, $b) {
            return strtotime($b['dateAdded']) - strtotime($a['dateAdded']);
        });
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'employee' => [
            'id' => $employee->ID,
            'name' => $employee->FirstName . ' ' . $employee->Surname,
            'jobTitle' => $employee->jobTitle ?? 'N/A',
            'department' => $employee->departmentName ?? 'N/A'
        ],
        'summary' => $summary,
        'leaveTypes' => $leaveTypeBreakdown,
        'applications' => $applicationHistory,
        'count' => count($applicationHistory)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

