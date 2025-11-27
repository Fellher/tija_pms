<?php
/**
 * Invoice Management Script
 * Handles CRUD operations for invoices including items, payments, etc.
 *
 * @package    TIJA_PMS
 * @subpackage Invoicing
 */

session_start();
$base = '../../../';
set_include_path($base);
include 'php/includes.php';

header('Content-Type: application/json');

// Check authentication
if (!$isValidUser) {
    echo json_encode(array('success' => false, 'message' => 'Unauthorized access'));
    exit;
}

$action = isset($_POST['action']) ? Utility::clean_string($_POST['action']) : '';
$response = array('success' => false, 'message' => 'Invalid action');

try {
    switch ($action) {
        case 'create':
        case 'create_invoice':
            $response = createInvoice($DBConn, $userDetails, $config);
            break;

        case 'update':
            $response = updateInvoice($DBConn, $userDetails, $config);
            break;

        case 'delete':
            $response = deleteInvoice($DBConn, $userDetails);
            break;

        case 'add_item':
            $response = addInvoiceItem($DBConn, $userDetails);
            break;

        case 'update_item':
            $response = updateInvoiceItem($DBConn, $userDetails);
            break;

        case 'delete_item':
            $response = deleteInvoiceItem($DBConn, $userDetails);
            break;

        case 'add_payment':
            $response = addInvoicePayment($DBConn, $userDetails);
            break;

        case 'update_payment':
            $response = updateInvoicePayment($DBConn, $userDetails);
            break;

        case 'delete_payment':
            $response = deleteInvoicePayment($DBConn, $userDetails);
            break;

        case 'update_status':
            $response = updateInvoiceStatus($DBConn, $userDetails);
            break;

        case 'calculate_totals':
            $response = calculateInvoiceTotals($DBConn);
            break;

        default:
            $response = array('success' => false, 'message' => 'Invalid action');
    }
} catch (Exception $e) {
    $response = array('success' => false, 'message' => 'Error: ' . $e->getMessage());
}

echo json_encode($response);
exit;

/**
 * Create a new invoice
 */
function createInvoice($DBConn, $userDetails, $config) {
    $errors = array();

    // Required fields
    $clientID = isset($_POST['clientID']) ? intval($_POST['clientID']) : 0;
    $projectID = isset($_POST['projectID']) ? intval($_POST['projectID']) : 0;
    $invoiceDate = isset($_POST['invoiceDate']) ? Utility::clean_string($_POST['invoiceDate']) : date('Y-m-d');
    $dueDate = isset($_POST['dueDate']) ? Utility::clean_string($_POST['dueDate']) : date('Y-m-d', strtotime('+30 days'));
    $templateID = isset($_POST['templateID']) ? intval($_POST['templateID']) : null;

    // Validation
    if (!$clientID) {
        $errors[] = 'Client is required';
    }

    if (empty($errors)) {
        // Generate invoice number
        $invoiceNumber = Invoice::generate_invoice_number(array('prefix' => 'INV'), $DBConn);

        // Get default template if not specified
        if (!$templateID) {
            $defaultTemplate = Invoice::invoice_templates(array('isDefault' => 'Y', 'isActive' => 'Y'), true, $DBConn);
            if ($defaultTemplate) {
                $templateID = $defaultTemplate->templateID;
            }
        }

        // Get employee details for organization context
        $employeeDetails = Employee::employees(array('ID' => $userDetails->ID), true, $DBConn);
        $orgDataID = isset($employeeDetails->orgDataID) && !empty($employeeDetails->orgDataID)
            ? $employeeDetails->orgDataID
            : (isset($_SESSION['orgDataID']) && !empty($_SESSION['orgDataID'])
                ? $_SESSION['orgDataID']
                : 1);
        $entityID = isset($employeeDetails->entityID) && !empty($employeeDetails->entityID)
            ? $employeeDetails->entityID
            : (isset($_SESSION['entityID'])
                ? $_SESSION['entityID']
                : 1);

        // Create invoice
        $invoiceData = array(
            'invoiceNumber' => $invoiceNumber,
            'clientID' => $clientID,
            'projectID' => $projectID > 0 ? $projectID : null,
            'invoiceDate' => $invoiceDate,
            'dueDate' => $dueDate,
            'invoiceStatusID' => 1, // Draft
            'templateID' => $templateID,
            'currency' => isset($_POST['currency']) ? Utility::clean_string($_POST['currency']) : 'KES',
            'subtotal' => 0,
            'invoiceAmount' => 0,
            'taxAmount' => 0,
            'totalAmount' => 0,
            'outstandingAmount' => 0,
            'notes' => isset($_POST['notes']) ? Utility::clean_string($_POST['notes']) : null,
            'terms' => isset($_POST['terms']) ? Utility::clean_string($_POST['terms']) : null,
            'orgDataID' => $orgDataID,
            'entityID' => $entityID,
            'LastUpdatedByID' => $userDetails->ID,
            'Suspended' => 'N'
        );

        $invoiceID = $DBConn->insert_data('tija_invoices', $invoiceData);

        if ($invoiceID) {
            // Add items if provided
            if (isset($_POST['items'])) {
                // Decode JSON string if it's a string, otherwise use as-is
                $items = $_POST['items'];
                if (is_string($items)) {
                    $items = json_decode($items, true);
                }

                if (is_array($items) && count($items) > 0) {
                    addInvoiceItems($invoiceID, $items, $DBConn);
                    // Recalculate totals
                    calculateInvoiceTotalsForInvoice($invoiceID, $DBConn);
                }
            }

            return array(
                'success' => true,
                'message' => 'Invoice created successfully',
                'invoiceID' => $invoiceID,
                'invoiceNumber' => $invoiceNumber
            );
        } else {
            return array('success' => false, 'message' => 'Failed to create invoice');
        }
    }

    return array('success' => false, 'message' => implode(', ', $errors), 'errors' => $errors);
}

/**
 * Update an existing invoice
 */
function updateInvoice($DBConn, $userDetails, $config) {
    $invoiceID = isset($_POST['invoiceID']) ? intval($_POST['invoiceID']) : 0;

    if (!$invoiceID) {
        return array('success' => false, 'message' => 'Invoice ID is required');
    }

    // Check if invoice exists
    $invoice = Invoice::invoices(array('invoiceID' => $invoiceID), true, $DBConn);
    if (!$invoice) {
        return array('success' => false, 'message' => 'Invoice not found');
    }

    // Only allow updates to draft invoices
    if ($invoice->invoiceStatusID != 1) {
        return array('success' => false, 'message' => 'Only draft invoices can be modified');
    }

    $updateData = array();

    if (isset($_POST['clientID'])) {
        $updateData['clientID'] = intval($_POST['clientID']);
    }
    if (isset($_POST['projectID'])) {
        $updateData['projectID'] = intval($_POST['projectID']) > 0 ? intval($_POST['projectID']) : null;
    }
    if (isset($_POST['invoiceDate'])) {
        $updateData['invoiceDate'] = Utility::clean_string($_POST['invoiceDate']);
    }
    if (isset($_POST['dueDate'])) {
        $updateData['dueDate'] = Utility::clean_string($_POST['dueDate']);
    }
    if (isset($_POST['templateID'])) {
        $updateData['templateID'] = intval($_POST['templateID']);
    }
    if (isset($_POST['currency'])) {
        $updateData['currency'] = Utility::clean_string($_POST['currency']);
    }
    if (isset($_POST['notes'])) {
        $updateData['notes'] = Utility::clean_string($_POST['notes']);
    }
    if (isset($_POST['terms'])) {
        $updateData['terms'] = Utility::clean_string($_POST['terms']);
    }

    $updateData['LastUpdatedByID'] = $userDetails->ID;
    $updateData['LastUpdate'] = $config['currentDateTimeFormated'];

    $updateSuccess = false;
    if (count($updateData) > 0) {
        $updateSuccess = $DBConn->update_table('tija_invoices', $updateData, array('invoiceID' => $invoiceID));
    }

    // Handle items update - delete existing items and add new ones
    if (isset($_POST['items'])) {
        // Decode JSON string if it's a string, otherwise use as-is
        $items = $_POST['items'];
        if (is_string($items)) {
            $items = json_decode($items, true);
        }

        if (is_array($items)) {
            // Delete all existing items and their related mappings using prepared statements
            deleteInvoiceItemsAndMappings($invoiceID, $DBConn);

            // Add new items if any
            if (count($items) > 0) {
                addInvoiceItems($invoiceID, $items, $DBConn);
            }

            // Recalculate totals
            calculateInvoiceTotalsForInvoice($invoiceID, $DBConn);
            $updateSuccess = true; // Mark as success if items were updated
        }
    }

    if ($updateSuccess) {
        return array('success' => true, 'message' => 'Invoice updated successfully');
    } else {
        return array('success' => false, 'message' => 'Failed to update invoice');
    }
}

/**
 * Delete invoice items and their related mappings using prepared statements
 * This function uses mysqlConnect class methods instead of raw SQL
 */
function deleteInvoiceItemsAndMappings($invoiceID, $DBConn) {
    // Get all invoice items for this invoice using prepared statement method
    $items = Invoice::invoice_items(array('invoiceID' => $invoiceID), false, $DBConn);

    if ($items && is_array($items)) {
        foreach ($items as $item) {
            // Get work hours mappings using prepared statement method
            $workHoursCols = array('invoiceWorkHourID', 'invoiceItemID', 'timelogID', 'hoursBilled', 'billingRate', 'amount');
            $workHours = $DBConn->retrieve_db_table_rows('tija_invoice_work_hours', $workHoursCols, array('invoiceItemID' => $item->invoiceItemID));
            if ($workHours && is_array($workHours)) {
                foreach ($workHours as $workHour) {
                    // Delete work hours mapping using prepared statement method
                    $DBConn->delete_row('tija_invoice_work_hours', array('invoiceWorkHourID' => $workHour->invoiceWorkHourID));
                }
            }

            // Get expense mappings using prepared statement method
            $expensesCols = array('invoiceExpenseID', 'invoiceItemID', 'expenseID', 'feeExpenseID', 'amount', 'markupPercent');
            $expenses = $DBConn->retrieve_db_table_rows('tija_invoice_expenses', $expensesCols, array('invoiceItemID' => $item->invoiceItemID));
            if ($expenses && is_array($expenses)) {
                foreach ($expenses as $expense) {
                    // Delete expense mapping using prepared statement method
                    $DBConn->delete_row('tija_invoice_expenses', array('invoiceExpenseID' => $expense->invoiceExpenseID));
                }
            }
            // Delete the invoice item using prepared statement method
            $DBConn->delete_row('tija_invoice_items', array('invoiceItemID' => $item->invoiceItemID));
        }
    }
}

/**
 * Delete an invoice
 */
function deleteInvoice($DBConn, $userDetails) {
    $invoiceID = isset($_POST['invoiceID']) ? intval($_POST['invoiceID']) : 0;

    if (!$invoiceID) {
        return array('success' => false, 'message' => 'Invoice ID is required');
    }

    // Check if invoice exists
    $invoice = Invoice::invoices(array('invoiceID' => $invoiceID), true, $DBConn);
    if (!$invoice) {
        return array('success' => false, 'message' => 'Invoice not found');
    }

    // Only allow deletion of draft invoices
    if ($invoice->invoiceStatusID != 1) {
        return array('success' => false, 'message' => 'Only draft invoices can be deleted');
    }

    // Delete invoice items and related mappings first
    deleteInvoiceItemsAndMappings($invoiceID, $DBConn);

    // Delete invoice using prepared statement method
    if ($DBConn->delete_row('tija_invoices', array('invoiceID' => $invoiceID))) {
        return array('success' => true, 'message' => 'Invoice deleted successfully');
    } else {
        return array('success' => false, 'message' => 'Failed to delete invoice');
    }
}

/**
 * Add invoice items
 */
function addInvoiceItems($invoiceID, $items, $DBConn) {
    $sortOrder = 0;
    foreach ($items as $item) {
        $itemData = array(
            'invoiceID' => $invoiceID,
            'itemType' => isset($item['itemType']) ? Utility::clean_string($item['itemType']) : 'custom',
            'itemReferenceID' => isset($item['itemReferenceID']) ? intval($item['itemReferenceID']) : null,
            'itemCode' => isset($item['itemCode']) ? Utility::clean_string($item['itemCode']) : null,
            'itemDescription' => isset($item['itemDescription']) ? Utility::clean_string($item['itemDescription']) : '',
            'quantity' => isset($item['quantity']) ? floatval($item['quantity']) : 1,
            'unitPrice' => isset($item['unitPrice']) ? floatval($item['unitPrice']) : 0,
            'discountPercent' => isset($item['discountPercent']) ? floatval($item['discountPercent']) : 0,
            'taxPercent' => isset($item['taxPercent']) ? floatval($item['taxPercent']) : 0,
            'sortOrder' => $sortOrder++,
            'Suspended' => 'N'
        );

        // Calculate line totals
        $subtotal = $itemData['quantity'] * $itemData['unitPrice'];
        $itemData['discountAmount'] = $subtotal * ($itemData['discountPercent'] / 100);
        $itemAfterDiscount = $subtotal - $itemData['discountAmount'];
        $itemData['taxAmount'] = $itemAfterDiscount * ($itemData['taxPercent'] / 100);
        $itemData['lineTotal'] = $itemAfterDiscount + $itemData['taxAmount'];

        // Add metadata if provided
        if (isset($item['metadata']) && is_array($item['metadata'])) {
            $itemData['metadata'] = json_encode($item['metadata']);
        }

        $itemID = $DBConn->insert_data('tija_invoice_items', $itemData);

        // Link work hours if applicable
        if ($itemData['itemType'] == 'work_hours' && isset($item['timelogIDs']) && is_array($item['timelogIDs'])) {
            foreach ($item['timelogIDs'] as $timelogID) {
                $hourMapping = array(
                    'invoiceItemID' => $itemID,
                    'timelogID' => intval($timelogID),
                    'hoursBilled' => $itemData['quantity'],
                    'billingRate' => $itemData['unitPrice'],
                    'amount' => $itemData['lineTotal']
                );
                $DBConn->insert_data('tija_invoice_work_hours', $hourMapping);
            }
        }

        // Link expenses if applicable
        if (($itemData['itemType'] == 'expense' || $itemData['itemType'] == 'fee_expense') && isset($item['expenseIDs']) && is_array($item['expenseIDs'])) {
            foreach ($item['expenseIDs'] as $expenseID) {
                $expenseMapping = array(
                    'invoiceItemID' => $itemID,
                    'expenseID' => $itemData['itemType'] == 'expense' ? intval($expenseID) : null,
                    'feeExpenseID' => $itemData['itemType'] == 'fee_expense' ? intval($expenseID) : null,
                    'amount' => $itemData['lineTotal'],
                    'markupPercent' => isset($item['markupPercent']) ? floatval($item['markupPercent']) : 0
                );
                $DBConn->insert_data('tija_invoice_expenses', $expenseMapping);
            }
        }
    }
}

/**
 * Add a single invoice item
 */
function addInvoiceItem($DBConn, $userDetails) {
    $invoiceID = isset($_POST['invoiceID']) ? intval($_POST['invoiceID']) : 0;

    if (!$invoiceID) {
        return array('success' => false, 'message' => 'Invoice ID is required');
    }

    // Get max sort order
    $existingItems = Invoice::invoice_items(array('invoiceID' => $invoiceID), false, $DBConn);
    $sortOrder = 0;
    if ($existingItems && is_array($existingItems)) {
        foreach ($existingItems as $item) {
            if ($item->sortOrder >= $sortOrder) {
                $sortOrder = $item->sortOrder + 1;
            }
        }
    }

    $itemData = array(
        'invoiceID' => $invoiceID,
        'itemType' => isset($_POST['itemType']) ? Utility::clean_string($_POST['itemType']) : 'custom',
        'itemReferenceID' => isset($_POST['itemReferenceID']) ? intval($_POST['itemReferenceID']) : null,
        'itemCode' => isset($_POST['itemCode']) ? Utility::clean_string($_POST['itemCode']) : null,
        'itemDescription' => isset($_POST['itemDescription']) ? Utility::clean_string($_POST['itemDescription']) : '',
        'quantity' => isset($_POST['quantity']) ? floatval($_POST['quantity']) : 1,
        'unitPrice' => isset($_POST['unitPrice']) ? floatval($_POST['unitPrice']) : 0,
        'discountPercent' => isset($_POST['discountPercent']) ? floatval($_POST['discountPercent']) : 0,
        'taxPercent' => isset($_POST['taxPercent']) ? floatval($_POST['taxPercent']) : 0,
        'sortOrder' => $sortOrder,
        'Suspended' => 'N'
    );

    // Calculate line totals
    $subtotal = $itemData['quantity'] * $itemData['unitPrice'];
    $itemData['discountAmount'] = $subtotal * ($itemData['discountPercent'] / 100);
    $itemAfterDiscount = $subtotal - $itemData['discountAmount'];
    $itemData['taxAmount'] = $itemAfterDiscount * ($itemData['taxPercent'] / 100);
    $itemData['lineTotal'] = $itemAfterDiscount + $itemData['taxAmount'];

    $itemID = $DBConn->insert_data('tija_invoice_items', $itemData);

    if ($itemID) {
        calculateInvoiceTotalsForInvoice($invoiceID, $DBConn);
        return array('success' => true, 'message' => 'Item added successfully', 'itemID' => $itemID);
    } else {
        return array('success' => false, 'message' => 'Failed to add item');
    }
}

/**
 * Update invoice item
 */
function updateInvoiceItem($DBConn, $userDetails) {
    $itemID = isset($_POST['itemID']) ? intval($_POST['itemID']) : 0;

    if (!$itemID) {
        return array('success' => false, 'message' => 'Item ID is required');
    }

    $item = Invoice::invoice_items(array('invoiceItemID' => $itemID), true, $DBConn);
    if (!$item) {
        return array('success' => false, 'message' => 'Item not found');
    }

    // Check if invoice is editable
    $invoice = Invoice::invoices(array('invoiceID' => $item->invoiceID), true, $DBConn);
    if ($invoice->invoiceStatusID != 1) {
        return array('success' => false, 'message' => 'Only draft invoices can be modified');
    }

    $updateData = array();

    if (isset($_POST['itemDescription'])) {
        $updateData['itemDescription'] = Utility::clean_string($_POST['itemDescription']);
    }
    if (isset($_POST['quantity'])) {
        $updateData['quantity'] = floatval($_POST['quantity']);
    }
    if (isset($_POST['unitPrice'])) {
        $updateData['unitPrice'] = floatval($_POST['unitPrice']);
    }
    if (isset($_POST['discountPercent'])) {
        $updateData['discountPercent'] = floatval($_POST['discountPercent']);
    }
    if (isset($_POST['taxPercent'])) {
        $updateData['taxPercent'] = floatval($_POST['taxPercent']);
    }

    // Recalculate line totals
    if (count($updateData) > 0) {
        $quantity = isset($updateData['quantity']) ? $updateData['quantity'] : $item->quantity;
        $unitPrice = isset($updateData['unitPrice']) ? $updateData['unitPrice'] : $item->unitPrice;
        $discountPercent = isset($updateData['discountPercent']) ? $updateData['discountPercent'] : $item->discountPercent;
        $taxPercent = isset($updateData['taxPercent']) ? $updateData['taxPercent'] : $item->taxPercent;

        $subtotal = $quantity * $unitPrice;
        $updateData['discountAmount'] = $subtotal * ($discountPercent / 100);
        $itemAfterDiscount = $subtotal - $updateData['discountAmount'];
        $updateData['taxAmount'] = $itemAfterDiscount * ($taxPercent / 100);
        $updateData['lineTotal'] = $itemAfterDiscount + $updateData['taxAmount'];

        if ($DBConn->update_table('tija_invoice_items', $updateData, array('invoiceItemID' => $itemID))) {
            calculateInvoiceTotalsForInvoice($item->invoiceID, $DBConn);
            return array('success' => true, 'message' => 'Item updated successfully');
        } else {
            return array('success' => false, 'message' => 'Failed to update item');
        }
    }

    return array('success' => false, 'message' => 'No changes to update');
}

/**
 * Delete invoice item
 */
function deleteInvoiceItem($DBConn, $userDetails) {
    $itemID = isset($_POST['itemID']) ? intval($_POST['itemID']) : 0;

    if (!$itemID) {
        return array('success' => false, 'message' => 'Item ID is required');
    }

    $item = Invoice::invoice_items(array('invoiceItemID' => $itemID), true, $DBConn);
    if (!$item) {
        return array('success' => false, 'message' => 'Item not found');
    }

    // Check if invoice is editable
    $invoice = Invoice::invoices(array('invoiceID' => $item->invoiceID), true, $DBConn);
    if ($invoice->invoiceStatusID != 1) {
        return array('success' => false, 'message' => 'Only draft invoices can be modified');
    }

    // Delete related mappings
    $DBConn->delete_row('tija_invoice_work_hours', array('invoiceItemID' => $itemID));
    $DBConn->delete_row('tija_invoice_expenses', array('invoiceItemID' => $itemID));

    // Delete item
    if ($DBConn->delete_row('tija_invoice_items', array('invoiceItemID' => $itemID))) {
        calculateInvoiceTotalsForInvoice($item->invoiceID, $DBConn);
        return array('success' => true, 'message' => 'Item deleted successfully');
    } else {
        return array('success' => false, 'message' => 'Failed to delete item');
    }
}

/**
 * Add invoice payment
 */
function addInvoicePayment($DBConn, $userDetails) {
    $invoiceID = isset($_POST['invoiceID']) ? intval($_POST['invoiceID']) : 0;
    $paymentAmount = isset($_POST['paymentAmount']) ? floatval($_POST['paymentAmount']) : 0;
    $paymentDate = isset($_POST['paymentDate']) ? Utility::clean_string($_POST['paymentDate']) : date('Y-m-d');

    if (!$invoiceID || !$paymentAmount) {
        return array('success' => false, 'message' => 'Invoice ID and payment amount are required');
    }

    // Get employee details for organization context
    $employeeDetails = Employee::employees(array('ID' => $userDetails->ID), true, $DBConn);
    $orgDataID = isset($employeeDetails->orgDataID) && !empty($employeeDetails->orgDataID)
        ? $employeeDetails->orgDataID
        : (isset($_SESSION['orgDataID']) && !empty($_SESSION['orgDataID'])
            ? $_SESSION['orgDataID']
            : 1);
    $entityID = isset($employeeDetails->entityID) && !empty($employeeDetails->entityID)
        ? $employeeDetails->entityID
        : (isset($_SESSION['entityID'])
            ? $_SESSION['entityID']
            : 1);

    // Generate payment number
    $paymentNumber = 'PAY-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

    $paymentData = array(
        'invoiceID' => $invoiceID,
        'paymentNumber' => $paymentNumber,
        'paymentDate' => $paymentDate,
        'paymentAmount' => $paymentAmount,
        'paymentMethod' => isset($_POST['paymentMethod']) ? Utility::clean_string($_POST['paymentMethod']) : 'bank_transfer',
        'paymentReference' => isset($_POST['paymentReference']) ? Utility::clean_string($_POST['paymentReference']) : null,
        'currency' => isset($_POST['currency']) ? Utility::clean_string($_POST['currency']) : 'KES',
        'notes' => isset($_POST['notes']) ? Utility::clean_string($_POST['notes']) : null,
        'receivedBy' => $userDetails->ID,
        'status' => 'pending',
        'orgDataID' => $orgDataID,
        'entityID' => $entityID,
        'LastUpdatedByID' => $userDetails->ID,
        'Suspended' => 'N'
    );

    $paymentID = $DBConn->insert_data('tija_invoice_payments', $paymentData);

    if ($paymentID) {
        // Update invoice paid amount
        updateInvoicePaidAmount($invoiceID, $DBConn);

        return array('success' => true, 'message' => 'Payment recorded successfully', 'paymentID' => $paymentID);
    } else {
        return array('success' => false, 'message' => 'Failed to record payment');
    }
}

/**
 * Update invoice payment
 */
function updateInvoicePayment($DBConn, $userDetails) {
    $paymentID = isset($_POST['paymentID']) ? intval($_POST['paymentID']) : 0;

    if (!$paymentID) {
        return array('success' => false, 'message' => 'Payment ID is required');
    }

    $payment = Invoice::invoice_payments(array('paymentID' => $paymentID), true, $DBConn);
    if (!$payment) {
        return array('success' => false, 'message' => 'Payment not found');
    }

    $updateData = array();

    if (isset($_POST['paymentAmount'])) {
        $updateData['paymentAmount'] = floatval($_POST['paymentAmount']);
    }
    if (isset($_POST['paymentDate'])) {
        $updateData['paymentDate'] = Utility::clean_string($_POST['paymentDate']);
    }
    if (isset($_POST['paymentMethod'])) {
        $updateData['paymentMethod'] = Utility::clean_string($_POST['paymentMethod']);
    }
    if (isset($_POST['paymentReference'])) {
        $updateData['paymentReference'] = Utility::clean_string($_POST['paymentReference']);
    }
    if (isset($_POST['status'])) {
        $updateData['status'] = Utility::clean_string($_POST['status']);
        if ($updateData['status'] == 'verified') {
            $updateData['verifiedBy'] = $userDetails->ID;
            $updateData['verificationDate'] = date('Y-m-d H:i:s');
        }
    }

    if (count($updateData) > 0) {
        if ($DBConn->update_table('tija_invoice_payments', $updateData, array('paymentID' => $paymentID))) {
            updateInvoicePaidAmount($payment->invoiceID, $DBConn);
            return array('success' => true, 'message' => 'Payment updated successfully');
        } else {
            return array('success' => false, 'message' => 'Failed to update payment');
        }
    }

    return array('success' => false, 'message' => 'No changes to update');
}

/**
 * Delete invoice payment
 */
function deleteInvoicePayment($DBConn, $userDetails) {
    $paymentID = isset($_POST['paymentID']) ? intval($_POST['paymentID']) : 0;

    if (!$paymentID) {
        return array('success' => false, 'message' => 'Payment ID is required');
    }

    $payment = Invoice::invoice_payments(array('paymentID' => $paymentID), true, $DBConn);
    if (!$payment) {
        return array('success' => false, 'message' => 'Payment not found');
    }

    if ($DBConn->delete_row('tija_invoice_payments', array('paymentID' => $paymentID))) {
        updateInvoicePaidAmount($payment->invoiceID, $DBConn);
        return array('success' => true, 'message' => 'Payment deleted successfully');
    } else {
        return array('success' => false, 'message' => 'Failed to delete payment');
    }
}

/**
 * Update invoice status
 */
function updateInvoiceStatus($DBConn, $userDetails) {
    $invoiceID = isset($_POST['invoiceID']) ? intval($_POST['invoiceID']) : 0;
    $statusID = isset($_POST['statusID']) ? intval($_POST['statusID']) : 0;

    if (!$invoiceID || !$statusID) {
        return array('success' => false, 'message' => 'Invoice ID and status are required');
    }

    $updateData = array(
        'invoiceStatusID' => $statusID,
        'LastUpdatedByID' => $userDetails->ID
    );

    if ($statusID == 2) { // Sent
        $updateData['sentDate'] = date('Y-m-d H:i:s');
    } elseif ($statusID == 3) { // Paid
        $updateData['paidDate'] = date('Y-m-d H:i:s');
    }

    if ($DBConn->update_table('tija_invoices', $updateData, array('invoiceID' => $invoiceID))) {
        return array('success' => true, 'message' => 'Invoice status updated successfully');
    } else {
        return array('success' => false, 'message' => 'Failed to update invoice status');
    }
}

/**
 * Calculate invoice totals
 */
function calculateInvoiceTotals($DBConn) {
    $invoiceID = isset($_POST['invoiceID']) ? intval($_POST['invoiceID']) : 0;

    if (!$invoiceID) {
        return array('success' => false, 'message' => 'Invoice ID is required');
    }

    calculateInvoiceTotalsForInvoice($invoiceID, $DBConn);

    $invoice = Invoice::invoices(array('invoiceID' => $invoiceID), true, $DBConn);

    return array(
        'success' => true,
        'subtotal' => $invoice->subtotal ?? 0,
        'taxAmount' => $invoice->taxAmount ?? 0,
        'totalAmount' => $invoice->totalAmount ?? 0
    );
}

/**
 * Calculate and update invoice totals
 */
function calculateInvoiceTotalsForInvoice($invoiceID, $DBConn) {
    $items = Invoice::invoice_items(array('invoiceID' => $invoiceID, 'Suspended' => 'N'), false, $DBConn);

    $subtotal = 0;
    $totalTax = 0;
    $totalDiscount = 0;

    if ($items && is_array($items)) {
        foreach ($items as $item) {
            $subtotal += $item->lineTotal - $item->taxAmount; // Subtotal before tax
            $totalTax += $item->taxAmount;
            $totalDiscount += $item->discountAmount;
        }
    }

    $totalAmount = $subtotal + $totalTax;

    $updateData = array(
        'subtotal' => $subtotal,
        'invoiceAmount' => $subtotal,
        'taxAmount' => $totalTax,
        'totalAmount' => $totalAmount,
        'LastUpdate' => date('Y-m-d H:i:s')
    );

    $DBConn->update_table('tija_invoices', $updateData, array('invoiceID' => $invoiceID));

    // Update outstanding amount
    updateInvoicePaidAmount($invoiceID, $DBConn);
}

/**
 * Update invoice paid amount and outstanding amount
 */
function updateInvoicePaidAmount($invoiceID, $DBConn) {
    $payments = Invoice::invoice_payments(array('invoiceID' => $invoiceID, 'status' => 'verified', 'Suspended' => 'N'), false, $DBConn);

    $paidAmount = 0;
    if ($payments && is_array($payments)) {
        foreach ($payments as $payment) {
            $paidAmount += $payment->paymentAmount;
        }
    }

    $invoice = Invoice::invoices(array('invoiceID' => $invoiceID), true, $DBConn);
    $outstandingAmount = ($invoice->totalAmount ?? 0) - $paidAmount;

    $updateData = array(
        'paidAmount' => $paidAmount,
        'outstandingAmount' => $outstandingAmount
    );

    // Update status if fully paid
    if ($outstandingAmount <= 0 && $invoice->invoiceStatusID != 3) {
        $updateData['invoiceStatusID'] = 3; // Paid
        $updateData['paidDate'] = date('Y-m-d H:i:s');
    }

    $DBConn->update_table('tija_invoices', $updateData, array('invoiceID' => $invoiceID));
}

?>

