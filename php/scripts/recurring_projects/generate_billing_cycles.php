<?php
/**
 * Billing Cycle Scheduler
 *
 * This script should be run daily via cron job to:
 * 1. Check for recurring projects needing new billing cycles
 * 2. Generate cycles based on recurrence patterns
 * 3. Update cycle statuses (upcoming → active → billing_due)
 * 4. Trigger invoice draft generation when due
 *
 * Cron job example (runs daily at 2 AM):
 * 0 2 * * * /usr/bin/php /path/to/php/scripts/recurring_projects/generate_billing_cycles.php
 *
 * @package    TIJA_PMS
 * @subpackage Recurring Projects
 * @version    1.0
 */

// Set base path
$base = '../../../';
set_include_path($base);

// Include dependencies
require_once 'php/includes.php';

// Enable error logging
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('display_errors', 0);

// Log script execution
error_log("=== Billing Cycle Scheduler Started ===");
error_log("Execution Time: " . date('Y-m-d H:i:s'));

try {
    $DBConn->begin();

    // Get all active recurring projects
    $recurringProjects = Projects::projects_mini(
        ['isRecurring' => 'Y', 'Suspended' => 'N', 'projectStatus' => 'Active'],
        false,
        $DBConn
    );

    if (!$recurringProjects || !is_array($recurringProjects)) {
        error_log("No active recurring projects found");
        $DBConn->commit();
        exit(0);
    }

    error_log("Found " . count($recurringProjects) . " active recurring projects");

    $cyclesGenerated = 0;
    $cyclesUpdated = 0;
    $errors = 0;

    foreach ($recurringProjects as $project) {
        try {
            // Get project recurrence settings
            $projectFull = Projects::projects_mini(['projectID' => $project->projectID], true, $DBConn);

            if (!$projectFull || $projectFull->isRecurring != 'Y') {
                continue;
            }

            // Get existing cycles
            $existingCycles = Projects::get_billing_cycles(
                ['projectID' => $project->projectID, 'Suspended' => 'N'],
                false,
                $DBConn
            );

            // Check if we need to generate new cycles
            $needsNewCycles = false;
            $lastCycle = null;

            if ($existingCycles && is_array($existingCycles)) {
                // Find the last cycle
                foreach ($existingCycles as $cycle) {
                    if (!$lastCycle || $cycle->cycleEndDate > $lastCycle->cycleEndDate) {
                        $lastCycle = $cycle;
                    }
                }

                // Check if last cycle is ending soon or has ended
                if ($lastCycle) {
                    $lastCycleEnd = new DateTime($lastCycle->cycleEndDate);
                    $today = new DateTime();
                    $daysUntilEnd = $today->diff($lastCycleEnd)->days;

                    // Generate new cycles if last cycle ends within 7 days
                    if ($lastCycleEnd <= $today || $daysUntilEnd <= 7) {
                        $needsNewCycles = true;
                    }
                }
            } else {
                // No cycles exist, generate initial cycles
                $needsNewCycles = true;
            }

            // Generate new cycles if needed
            if ($needsNewCycles) {
                $recurrenceData = [
                    'recurrenceType' => $projectFull->recurrenceType ?? null,
                    'recurrenceInterval' => intval($projectFull->recurrenceInterval ?? 1),
                    'recurrenceDayOfWeek' => $projectFull->recurrenceDayOfWeek ?? null,
                    'recurrenceDayOfMonth' => $projectFull->recurrenceDayOfMonth ?? null,
                    'recurrenceMonthOfYear' => $projectFull->recurrenceMonthOfYear ?? null,
                    'recurrenceStartDate' => $projectFull->recurrenceStartDate ?? $projectFull->projectStart,
                    'recurrenceEndDate' => $projectFull->recurrenceEndDate ?? null,
                    'recurrenceCount' => $projectFull->recurrenceCount ?? null,
                    'billingCycleAmount' => $projectFull->billingCycleAmount ?? $projectFull->projectValue ?? 0,
                    'invoiceDaysBeforeDue' => intval($projectFull->invoiceDaysBeforeDue ?? 7)
                ];

                // If we have a last cycle, start from its end date
                if ($lastCycle) {
                    $recurrenceData['recurrenceStartDate'] = $lastCycle->cycleEndDate;
                }

                if (Projects::generate_billing_cycles($project->projectID, $recurrenceData, $DBConn)) {
                    $cyclesGenerated++;
                    error_log("Generated cycles for project {$project->projectID} ({$project->projectName})");
                } else {
                    $errors++;
                    error_log("ERROR: Failed to generate cycles for project {$project->projectID}");
                }
            }

            // Update cycle statuses
            if ($existingCycles && is_array($existingCycles)) {
                $today = new DateTime();

                foreach ($existingCycles as $cycle) {
                    $cycleStart = new DateTime($cycle->cycleStartDate);
                    $cycleEnd = new DateTime($cycle->cycleEndDate);
                    $billingDate = new DateTime($cycle->billingDate);
                    $dueDate = new DateTime($cycle->dueDate);

                    $newStatus = $cycle->status;

                    // Update status based on dates
                    if ($cycle->status == 'upcoming' && $cycleStart <= $today && $cycleEnd >= $today) {
                        $newStatus = 'active';
                    } elseif ($cycle->status == 'active' && $billingDate <= $today && $cycleEnd >= $today) {
                        $newStatus = 'billing_due';
                    } elseif ($cycle->status == 'billing_due' && $cycleEnd < $today && $cycle->invoiceID == null) {
                        $newStatus = 'overdue';
                    } elseif ($cycle->status == 'invoiced' && $dueDate < $today && $cycle->invoiceID != null) {
                        // Check invoice status to see if paid
                        // This would require Invoice class integration
                        // For now, we'll leave it as invoiced
                    }

                    if ($newStatus != $cycle->status) {
                        if (Projects::update_billing_cycle_status($cycle->billingCycleID, $newStatus, [], $DBConn)) {
                            $cyclesUpdated++;
                            error_log("Updated cycle {$cycle->billingCycleID} status from {$cycle->status} to {$newStatus}");
                        }
                    }

                    // Update hours logged for active cycles
                    if ($cycle->status == 'active' || $cycle->status == 'billing_due') {
                        $timeLogs = Projects::get_cycle_time_logs($cycle->billingCycleID, $DBConn);
                        $totalHours = 0;

                        if ($timeLogs && is_array($timeLogs)) {
                            foreach ($timeLogs as $log) {
                                $hours = isset($log->taskDuration) ? floatval($log->taskDuration) : 0;
                                if (isset($log->workHours)) {
                                    $hours = floatval($log->workHours);
                                } elseif (isset($log->startTime) && isset($log->endTime)) {
                                    $start = strtotime($log->startTime);
                                    $end = strtotime($log->endTime);
                                    $hours = ($end - $start) / 3600;
                                }
                                $totalHours += $hours;
                            }
                        }

                        // Update hours logged
                        if ($totalHours != floatval($cycle->hoursLogged)) {
                            Projects::update_billing_cycle_status(
                                $cycle->billingCycleID,
                                $cycle->status,
                                ['hoursLogged' => round($totalHours, 2)],
                                $DBConn
                            );
                        }
                    }
                }
            }

        } catch (Exception $e) {
            $errors++;
            error_log("ERROR processing project {$project->projectID}: " . $e->getMessage());
            continue;
        }
    }

    $DBConn->commit();

    error_log("=== Billing Cycle Scheduler Completed ===");
    error_log("Cycles Generated: {$cyclesGenerated}");
    error_log("Cycles Updated: {$cyclesUpdated}");
    error_log("Errors: {$errors}");

    // Trigger invoice draft generation if auto-generate is enabled
    // This will be handled by a separate script or can be called here
    // For now, we'll just log that cycles are ready for billing
    $billingDueCycles = Projects::get_billing_cycles(
        ['status' => 'billing_due', 'Suspended' => 'N'],
        false,
        $DBConn
    );

    if ($billingDueCycles && is_array($billingDueCycles)) {
        error_log("Found " . count($billingDueCycles) . " cycles due for billing");
        // Note: Invoice draft generation should be handled by generate_invoice_drafts.php
    }

    exit(0);

} catch (Exception $e) {
    $DBConn->rollback();
    error_log("FATAL ERROR in billing cycle scheduler: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    exit(1);
}

