<?php
/**
 * Check Recurring Project Notifications
 *
 * This script checks for recurring projects that need attention:
 * - Projects needing new billing cycles
 * - Billing cycles due for invoice draft creation
 *
 * Should be called on user login or periodically via AJAX
 *
 * @package    TIJA_PMS
 * @subpackage Recurring Projects
 * @version    1.0
 */

// This script can be called directly or via AJAX
if (!isset($_SESSION)) {
    session_start();
}
$base = '../../../';
set_include_path($base);

require_once 'php/includes.php';

$notifications = [];

try {
    if (!$isValidUser) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'User not authenticated', 'notifications' => []]);
        exit;
    }

    // Get user's entity and organization
    $userEntityID = $employeeDetails->entityID ?? null;
    $userOrgID = $employeeDetails->orgDataID ?? null;

    if (!$userEntityID || !$userOrgID) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'User context missing', 'notifications' => []]);
        exit;
    }

    // Check if user is a project manager or project owner
    $isProjectManager = false;
    $userProjects = Projects::projects_mini(
        [
            'projectOwnerID' => $userDetails->ID,
            'entityID' => $userEntityID,
            'orgDataID' => $userOrgID,
            'isRecurring' => 'Y',
            'Suspended' => 'N',
            'projectStatus' => 'Active'
        ],
        false,
        $DBConn
    );

    if ($userProjects && is_array($userProjects) && count($userProjects) > 0) {
        $isProjectManager = true;
    }

    // Also check if user is a manager in any projects
    if (!$isProjectManager) {
        $allRecurringProjects = Projects::projects_mini(
            [
                'entityID' => $userEntityID,
                'orgDataID' => $userOrgID,
                'isRecurring' => 'Y',
                'Suspended' => 'N',
                'projectStatus' => 'Active'
            ],
            false,
            $DBConn
        );

        if ($allRecurringProjects && is_array($allRecurringProjects)) {
            foreach ($allRecurringProjects as $project) {
                // Check if user is in projectManagersIDs
                if (isset($project->projectManagersIDs) && !empty($project->projectManagersIDs)) {
                    $managerIDs = explode(',', $project->projectManagersIDs);
                    if (in_array($userDetails->ID, $managerIDs)) {
                        $isProjectManager = true;
                        break;
                    }
                }
            }
        }
    }

    if (!$isProjectManager) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'notifications' => [], 'count' => 0]);
        exit;
    }

    // Get all active recurring projects for user's entity
    $recurringProjects = Projects::projects_mini(
        [
            'entityID' => $userEntityID,
            'orgDataID' => $userOrgID,
            'isRecurring' => 'Y',
            'Suspended' => 'N',
            'projectStatus' => 'Active'
        ],
        false,
        $DBConn
    );

    if (!$recurringProjects || !is_array($recurringProjects)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'notifications' => [], 'count' => 0]);
        exit;
    }

    $today = date('Y-m-d');

    foreach ($recurringProjects as $project) {
        // Check if user has access to this project
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
            continue;
        }

        // Get existing cycles
        $existingCycles = Projects::get_billing_cycles(
            ['projectID' => $project->projectID, 'Suspended' => 'N'],
            false,
            $DBConn
        );

        // Check if new cycles need to be generated
        $needsNewCycles = false;
        $lastCycle = null;

        if ($existingCycles && is_array($existingCycles)) {
            // Find the last cycle
            foreach ($existingCycles as $cycle) {
                if (!$lastCycle || $cycle->cycleEndDate > $lastCycle->cycleEndDate) {
                    $lastCycle = $cycle;
                }
            }

            if ($lastCycle) {
                $lastCycleEnd = new DateTime($lastCycle->cycleEndDate);
                $todayDate = new DateTime($today);
                $daysUntilEnd = $todayDate->diff($lastCycleEnd)->days;

                // Generate new cycles if last cycle ends within 7 days or has ended
                if ($lastCycleEnd <= $todayDate || $daysUntilEnd <= 7) {
                    $needsNewCycles = true;
                }
            }
        } else {
            // No cycles exist, check if project start date has passed
            if ($project->projectStart && $project->projectStart <= $today) {
                $needsNewCycles = true;
            }
        }

        if ($needsNewCycles) {
            $notifications[] = [
                'type' => 'generate_cycles',
                'title' => 'Generate Billing Cycles',
                'message' => "Project '{$project->projectName}' needs new billing cycles generated.",
                'projectID' => $project->projectID,
                'projectName' => $project->projectName,
                'action' => 'generate_cycles',
                'priority' => 'high'
            ];
        }

        // Check for cycles due for invoice draft creation
        if ($existingCycles && is_array($existingCycles)) {
            foreach ($existingCycles as $cycle) {
                if ($cycle->status == 'billing_due' && !$cycle->invoiceDraftID && !$cycle->invoiceID) {
                    $billingDate = new DateTime($cycle->billingDate);
                    $todayDate = new DateTime($today);

                    if ($billingDate <= $todayDate) {
                        $notifications[] = [
                            'type' => 'create_invoice_draft',
                            'title' => 'Create Invoice Draft',
                            'message' => "Billing cycle #{$cycle->cycleNumber} for '{$project->projectName}' is due for invoice draft creation.",
                            'projectID' => $project->projectID,
                            'projectName' => $project->projectName,
                            'billingCycleID' => $cycle->billingCycleID,
                            'cycleNumber' => $cycle->cycleNumber,
                            'amount' => $cycle->amount,
                            'action' => 'create_invoice_draft',
                            'priority' => 'medium'
                        ];
                    }
                }
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'count' => count($notifications)
    ]);

} catch (Exception $e) {
    error_log("Error checking recurring notifications: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error checking notifications',
        'notifications' => [],
        'count' => 0
    ]);
}

