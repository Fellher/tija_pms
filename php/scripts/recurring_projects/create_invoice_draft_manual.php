<?php
/**
 * Manual Invoice Draft Creation for Recurring Projects
 *
 * Allows project managers to manually create invoice drafts for billing cycles
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

    $billingCycleID = isset($_POST['billingCycleID']) ? intval($_POST['billingCycleID']) : 0;

    if (!$billingCycleID) {
        throw new Exception("Billing cycle ID is required");
    }

    $DBConn->begin();

    // Get billing cycle
    $billingCycle = Projects::get_billing_cycles(['billingCycleID' => $billingCycleID], true, $DBConn);
    if (!$billingCycle) {
        throw new Exception("Invalid billing cycle");
    }

    // Get project
    $project = Projects::projects_mini(['projectID' => $billingCycle->projectID], true, $DBConn);
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
        throw new Exception("You don't have permission to create invoices for this project");
    }

    // Check if invoice draft already exists
    if ($billingCycle->invoiceDraftID) {
        throw new Exception("Invoice draft already exists for this cycle");
    }

    if ($billingCycle->invoiceID) {
        throw new Exception("Invoice already finalized for this cycle");
    }

    // Calculate billing amount
    $billingCalculation = Projects::calculate_cycle_billing($billingCycleID, $DBConn);
    if (!$billingCalculation) {
        throw new Exception("Could not calculate billing for cycle");
    }

    // Determine invoice amount
    $invoiceAmount = floatval($billingCycle->amount) > 0
        ? floatval($billingCycle->amount)
        : floatval($billingCalculation['calculatedAmount']);

    if ($invoiceAmount <= 0) {
        throw new Exception("Invoice amount must be greater than zero");
    }

    // Get client details
    $client = Clients::clients(['clientID' => $project->clientID], true, $DBConn);
    if (!$client) {
        throw new Exception("Could not load client");
    }

    // Generate invoice number
    $invoiceNumber = Invoice::generate_invoice_number(
        ['prefix' => 'INV', 'orgDataID' => $project->orgDataID],
        $DBConn
    );

    // Get draft status ID
    $invoiceStatuses = Invoice::invoice_statuses([], false, $DBConn);
    $draftStatusID = 1; // Default to 1
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
        'clientID' => $project->clientID,
        'projectID' => $project->projectID,
        'invoiceDate' => date('Y-m-d'),
        'dueDate' => $billingCycle->dueDate,
        'invoiceAmount' => $invoiceAmount,
        'taxAmount' => 0, // Can be calculated based on tax settings
        'totalAmount' => $invoiceAmount,
        'currency' => 'KES', // Default currency
        'invoiceStatusID' => $draftStatusID,
        'orgDataID' => $project->orgDataID,
        'entityID' => $project->entityID,
        'DateAdded' => date('Y-m-d H:i:s'),
        'LastUpdate' => date('Y-m-d H:i:s'),
        'LastUpdatedByID' => $userDetails->ID,
        'Lapsed' => 'N',
        'Suspended' => 'N'
    ];

    // Insert invoice
    if (!$DBConn->insert_data('tija_invoices', $invoiceData)) {
        throw new Exception("Failed to create invoice draft");
    }

    $invoiceID = $DBConn->lastInsertId();

    // Update billing cycle with invoice draft ID
    Projects::update_billing_cycle_status(
        $billingCycleID,
        'billing_due',
        ['invoiceDraftID' => $invoiceID],
        $DBConn
    );

    // Create notification for project owner (if different from creator)
    if ($project->projectOwnerID != $userDetails->ID) {
        $notificationData = [
            'employeeID' => $project->projectOwnerID,
            'approverID' => $userDetails->ID,
            'segmentType' => 'projects',
            'segmentID' => $project->projectID,
            'notificationNotes' => "<p>Invoice draft <strong>{$invoiceNumber}</strong> has been created for billing cycle #{$billingCycle->cycleNumber} of project <strong>{$project->projectName}</strong>.</p>
                                    <p><a href='{$config['siteURL']}html/?s=user&ss=projects&p=invoice_drafts&draftid={$invoiceID}'>Review Invoice Draft</a></p>",
            'notificationType' => "invoice_draft_created",
            'notificationStatus' => 'unread',
            'originatorUserID' => $userDetails->ID,
            'targetUserID' => $project->projectOwnerID
        ];

        $DBConn->insert_data('tija_notifications', $notificationData);
    }

    $DBConn->commit();

    $response['success'] = true;
    $response['message'] = "Invoice draft created successfully";
    $response['data'] = [
        'invoiceID' => $invoiceID,
        'invoiceNumber' => $invoiceNumber,
        'amount' => $invoiceAmount
    ];

} catch (Exception $e) {
    $DBConn->rollback();
    $response['message'] = $e->getMessage();
    error_log("Error in create_invoice_draft_manual: " . $e->getMessage());
}

header('Content-Type: application/json');
echo json_encode($response);
exit;

