<?php
/**
 * Invoice Draft Generator for Recurring Projects
 *
 * This script generates invoice drafts for billing cycles that are due for billing.
 * It should be run daily via cron job or called by the billing cycle scheduler.
 *
 * Cron job example (runs daily at 3 AM):
 * 0 3 * * * /usr/bin/php /path/to/php/scripts/recurring_projects/generate_invoice_drafts.php
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
error_log("=== Invoice Draft Generator Started ===");
error_log("Execution Time: " . date('Y-m-d H:i:s'));

try {
    $DBConn->begin();

    // Get all projects with auto-generate invoices enabled
    $recurringProjects = Projects::projects_mini(
        [
            'isRecurring' => 'Y',
            'autoGenerateInvoices' => 'Y',
            'Suspended' => 'N',
            'projectStatus' => 'Active'
        ],
        false,
        $DBConn
    );

    if (!$recurringProjects || !is_array($recurringProjects)) {
        error_log("No recurring projects with auto-generate enabled found");
        $DBConn->commit();
        exit(0);
    }

    error_log("Found " . count($recurringProjects) . " recurring projects with auto-generate enabled");

    $draftsGenerated = 0;
    $errors = 0;
    $today = date('Y-m-d');

    foreach ($recurringProjects as $project) {
        try {
            // Get billing cycles that are due for billing and don't have invoices yet
            $billingCycles = Projects::get_billing_cycles(
                [
                    'projectID' => $project->projectID,
                    'status' => 'billing_due',
                    'Suspended' => 'N',
                    'invoiceDraftID' => null // No draft exists yet
                ],
                false,
                $DBConn
            );

            if (!$billingCycles || !is_array($billingCycles)) {
                continue;
            }

            foreach ($billingCycles as $cycle) {
                // Check if billing date has arrived
                $billingDate = new DateTime($cycle->billingDate);
                $todayDate = new DateTime($today);

                if ($billingDate > $todayDate) {
                    // Not yet due
                    continue;
                }

                // Check if invoice draft already exists
                if ($cycle->invoiceDraftID) {
                    continue;
                }

                // Get project details
                $projectFull = Projects::projects_mini(['projectID' => $project->projectID], true, $DBConn);
                if (!$projectFull) {
                    $errors++;
                    error_log("ERROR: Could not load project {$project->projectID}");
                    continue;
                }

                // Calculate billing amount
                $billingCalculation = Projects::calculate_cycle_billing($cycle->billingCycleID, $DBConn);
                if (!$billingCalculation) {
                    $errors++;
                    error_log("ERROR: Could not calculate billing for cycle {$cycle->billingCycleID}");
                    continue;
                }

                // Get time logs for this cycle
                $timeLogs = Projects::get_cycle_time_logs($cycle->billingCycleID, $DBConn);

                // Determine invoice amount
                $invoiceAmount = floatval($cycle->amount) > 0
                    ? floatval($cycle->amount)
                    : floatval($billingCalculation['calculatedAmount']);

                if ($invoiceAmount <= 0) {
                    error_log("Skipping cycle {$cycle->billingCycleID} - zero amount");
                    continue;
                }

                // Get client details
                $client = Clients::clients(['clientID' => $projectFull->clientID], true, $DBConn);
                if (!$client) {
                    $errors++;
                    error_log("ERROR: Could not load client {$projectFull->clientID}");
                    continue;
                }

                // Generate invoice number
                $invoiceNumber = Invoice::generate_invoice_number(
                    ['prefix' => 'INV', 'orgDataID' => $projectFull->orgDataID],
                    $DBConn
                );

                // Determine invoice status (draft status ID - typically 1 or a specific draft status)
                // Check what status ID represents 'draft'
                $invoiceStatuses = Invoice::invoice_statuses([], false, $DBConn);
                $draftStatusID = 1; // Default to 1, adjust based on your status IDs
                if ($invoiceStatuses && is_array($invoiceStatuses)) {
                    foreach ($invoiceStatuses as $status) {
                        if (isset($status->statusName) && strtolower($status->statusName) == 'draft') {
                            $draftStatusID = $status->statusID;
                            break;
                        }
                    }
                }

                // Create invoice draft
                $invoiceData = [
                    'invoiceNumber' => $invoiceNumber,
                    'clientID' => $projectFull->clientID,
                    'projectID' => $project->projectID,
                    'invoiceDate' => $today,
                    'dueDate' => $cycle->dueDate,
                    'invoiceAmount' => $invoiceAmount,
                    'taxAmount' => 0, // Can be calculated based on tax settings
                    'totalAmount' => $invoiceAmount,
                    'currency' => 'KES', // Default currency, can be from project/client settings
                    'invoiceStatusID' => $draftStatusID,
                    'orgDataID' => $projectFull->orgDataID,
                    'entityID' => $projectFull->entityID,
                    'DateAdded' => date('Y-m-d H:i:s'),
                    'LastUpdate' => date('Y-m-d H:i:s'),
                    'LastUpdatedByID' => $projectFull->projectOwnerID ?? null,
                    'Lapsed' => 'N',
                    'Suspended' => 'N'
                ];

                // Insert invoice
                if (!$DBConn->insert_data('tija_invoices', $invoiceData)) {
                    $errors++;
                    error_log("ERROR: Failed to create invoice draft for cycle {$cycle->billingCycleID}");
                    continue;
                }

                $invoiceID = $DBConn->lastInsertId();

                // Update billing cycle with invoice draft ID
                Projects::update_billing_cycle_status(
                    $cycle->billingCycleID,
                    'billing_due',
                    ['invoiceDraftID' => $invoiceID],
                    $DBConn
                );

                // Create invoice line items from time logs if needed
                // This depends on your invoice line items table structure
                // For now, we'll just log the time logs count
                if ($timeLogs && is_array($timeLogs)) {
                    error_log("Cycle {$cycle->billingCycleID} has " . count($timeLogs) . " time logs");
                    // TODO: Create invoice line items from time logs
                    // This would require checking your invoice line items table structure
                }

                $draftsGenerated++;
                error_log("Generated invoice draft {$invoiceNumber} (ID: {$invoiceID}) for cycle {$cycle->billingCycleID}");

                // TODO: Send notification to project owner/managers
                // This would require your notification system integration

            }

        } catch (Exception $e) {
            $errors++;
            error_log("ERROR processing project {$project->projectID}: " . $e->getMessage());
            continue;
        }
    }

    $DBConn->commit();

    error_log("=== Invoice Draft Generator Completed ===");
    error_log("Drafts Generated: {$draftsGenerated}");
    error_log("Errors: {$errors}");

    exit(0);

} catch (Exception $e) {
    $DBConn->rollback();
    error_log("FATAL ERROR in invoice draft generator: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    exit(1);
}

