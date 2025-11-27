<?php
/**
 * Manage Billing Cycle
 *
 * Handles manual creation and management of billing cycles
 *
 * @package    TIJA_PMS
 * @subpackage Recurring Projects
 * @version    1.0
 */

session_start();
$base = '../../../';
set_include_path($base);

require_once 'php/includes.php';

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    if (!$isValidUser) {
        throw new Exception("User not authenticated");
    }

    $action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : '';
    $projectID = isset($_POST['projectID']) ? intval($_POST['projectID']) : 0;

    if (!$projectID) {
        throw new Exception("Project ID is required");
    }

    $DBConn->begin();

    // Get project
    $project = Projects::projects_mini(['projectID' => $projectID], true, $DBConn);
    if (!$project || $project->isRecurring != 'Y') {
        throw new Exception("Invalid recurring project");
    }

    // Check user access
    $hasAccess = false;
    if ($project->projectOwnerID == $userDetails->ID) {
        $hasAccess = true;
    } elseif (isset($project->projectManagersIDs) && !empty($project->projectManagersIDs)) {
        $managerIDs = explode(',', $project->projectManagersIDs);
        if (in_array($userDetails->ID, $managerIDs)) {
            $hasAccess = true;
        }
    }

    if (!$hasAccess) {
        throw new Exception("You don't have permission to manage this project");
    }

    switch ($action) {
        case 'generate_next_cycles':
            // Generate next billing cycles for the project
            $recurrenceData = [
                'recurrenceType' => $project->recurrenceType ?? null,
                'recurrenceInterval' => intval($project->recurrenceInterval ?? 1),
                'recurrenceDayOfWeek' => $project->recurrenceDayOfWeek ?? null,
                'recurrenceDayOfMonth' => $project->recurrenceDayOfMonth ?? null,
                'recurrenceMonthOfYear' => $project->recurrenceMonthOfYear ?? null,
                'recurrenceStartDate' => $project->recurrenceStartDate ?? $project->projectStart,
                'recurrenceEndDate' => $project->recurrenceEndDate ?? null,
                'recurrenceCount' => $project->recurrenceCount ?? null,
                'billingCycleAmount' => $project->billingCycleAmount ?? $project->projectValue ?? 0,
                'invoiceDaysBeforeDue' => intval($project->invoiceDaysBeforeDue ?? 7)
            ];

            // Get last cycle to start from its end date
            $existingCycles = Projects::get_billing_cycles(
                ['projectID' => $projectID, 'Suspended' => 'N'],
                false,
                $DBConn
            );

            if ($existingCycles && is_array($existingCycles)) {
                $lastCycle = null;
                foreach ($existingCycles as $cycle) {
                    if (!$lastCycle || $cycle->cycleEndDate > $lastCycle->cycleEndDate) {
                        $lastCycle = $cycle;
                    }
                }
                if ($lastCycle) {
                    $recurrenceData['recurrenceStartDate'] = $lastCycle->cycleEndDate;
                }
            }

            if (Projects::generate_billing_cycles($projectID, $recurrenceData, $DBConn)) {
                $response['success'] = true;
                $response['message'] = "Billing cycles generated successfully";
            } else {
                throw new Exception("Failed to generate billing cycles");
            }
            break;

        case 'create':
            // Create a single billing cycle manually
            $cycleStartDate = isset($_POST['cycleStartDate']) ? Utility::clean_string($_POST['cycleStartDate']) : '';
            $cycleEndDate = isset($_POST['cycleEndDate']) ? Utility::clean_string($_POST['cycleEndDate']) : '';
            $billingDate = isset($_POST['billingDate']) ? Utility::clean_string($_POST['billingDate']) : '';
            $dueDate = isset($_POST['dueDate']) ? Utility::clean_string($_POST['dueDate']) : '';
            $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

            if (!$cycleStartDate || !$cycleEndDate || !$billingDate || !$dueDate) {
                throw new Exception("All dates are required");
            }

            // Get next cycle number
            $existingCycles = Projects::get_billing_cycles(['projectID' => $projectID], false, $DBConn);
            $nextCycleNumber = 1;
            if ($existingCycles && is_array($existingCycles)) {
                $maxCycle = 0;
                foreach ($existingCycles as $cycle) {
                    if ($cycle->cycleNumber > $maxCycle) {
                        $maxCycle = $cycle->cycleNumber;
                    }
                }
                $nextCycleNumber = $maxCycle + 1;
            }

            // Determine status
            $today = date('Y-m-d');
            $status = 'upcoming';
            if ($cycleStartDate <= $today && $cycleEndDate >= $today) {
                $status = 'active';
            } elseif ($billingDate <= $today && $cycleEndDate >= $today) {
                $status = 'billing_due';
            } elseif ($cycleEndDate < $today) {
                $status = 'overdue';
            }

            $cycleData = [
                'projectID' => $projectID,
                'cycleNumber' => $nextCycleNumber,
                'cycleStartDate' => $cycleStartDate,
                'cycleEndDate' => $cycleEndDate,
                'billingDate' => $billingDate,
                'dueDate' => $dueDate,
                'status' => $status,
                'amount' => $amount,
                'hoursLogged' => 0,
                'DateAdded' => date('Y-m-d H:i:s'),
                'Suspended' => 'N'
            ];

            $cycleID = Projects::create_billing_cycle($cycleData, $DBConn);
            if ($cycleID) {
                $response['success'] = true;
                $response['message'] = "Billing cycle created successfully";
                $response['data'] = ['billingCycleID' => $cycleID];
            } else {
                throw new Exception("Failed to create billing cycle");
            }
            break;

        default:
            throw new Exception("Invalid action");
    }

    $DBConn->commit();

} catch (Exception $e) {
    $DBConn->rollback();
    $response['message'] = $e->getMessage();
    error_log("Error in manage_billing_cycle: " . $e->getMessage());
}

header('Content-Type: application/json');
echo json_encode($response);
exit;

