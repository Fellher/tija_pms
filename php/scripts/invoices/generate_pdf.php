<?php
/**
 * Invoice PDF Generation Script
 *
 * Generates PDF invoices using templates with DomPDF
 *
 * @package    TIJA_PMS
 * @subpackage Invoicing
 */

session_start();
$base = '../../../';
set_include_path($base);
include 'php/includes.php';

// Check authentication
if (!$isValidUser) {
    die('Unauthorized access');
}

$invoiceID = isset($_GET['invoiceID']) ? intval($_GET['invoiceID']) : 0;
$templateID = isset($_GET['templateID']) ? intval($_GET['templateID']) : 0;
$format = isset($_GET['format']) ? Utility::clean_string($_GET['format']) : 'pdf'; // 'pdf' or 'html'
$isPreview = isset($_GET['preview']) && $_GET['preview'] == '1';

// Handle preview mode (invoiceID = 0)
if ($invoiceID == 0 && $isPreview && $templateID > 0) {
    // Get template
    $template = Invoice::invoice_templates(array('templateID' => $templateID), true, $DBConn);

    if (!$template) {
        die('Template not found');
    }

    // Create sample invoice data
    $invoice = (object) array(
        'invoiceID' => 0,
        'invoiceNumber' => 'INV-PREVIEW-001',
        'invoiceDate' => date('Y-m-d'),
        'dueDate' => date('Y-m-d', strtotime('+30 days')),
        'currency' => $template->currency ?: 'KES',
        'subtotal' => 10000.00,
        'taxAmount' => 1600.00,
        'totalAmount' => 11600.00,
        'invoiceAmount' => 11600.00,
        'paidAmount' => 0,
        'outstandingAmount' => 11600.00,
        'notes' => $template->defaultNotes ?: 'This is a sample invoice preview.',
        'terms' => $template->defaultTerms ?: 'Payment due within 30 days.',
        'clientName' => 'Sample Client Company',
        'projectName' => 'Sample Project',
        'invoiceStatusName' => 'Draft',
        'invoiceStatusColor' => '#6c757d',
        'templateID' => $templateID,
        'items' => array(
            (object) array(
                'itemDescription' => 'Sample Service Item 1',
                'quantity' => 10,
                'unitPrice' => 500.00,
                'discountPercent' => 0,
                'taxPercent' => 16,
                'lineTotal' => 5800.00
            ),
            (object) array(
                'itemDescription' => 'Sample Service Item 2',
                'quantity' => 5,
                'unitPrice' => 800.00,
                'discountPercent' => 5,
                'taxPercent' => 16,
                'lineTotal' => 4408.00
            )
        )
    );

    // Create sample client
    $client = (object) array(
        'clientName' => 'Sample Client Company',
        'clientAddress' => '123 Sample Street\nSample City, Country',
        'clientEmail' => 'client@example.com',
        'clientPhone' => '+254 XXX XXX XXX'
    );
} else {
    if (!$invoiceID) {
        die('Invoice ID is required');
    }

    // Get invoice data
    $invoice = Invoice::invoice_complete($invoiceID, $DBConn);

    if (!$invoice) {
        die('Invoice not found');
    }

// Get client details with full information including addresses
$client = Client::client_full(array('clientID' => $invoice->clientID), true, $DBConn);

// Get billing address if available
$billingAddress = null;
if ($client && isset($client->clientAddresses) && is_array($client->clientAddresses)) {
    foreach ($client->clientAddresses as $address) {
        if (isset($address->billingAddress) && $address->billingAddress == 'Y') {
            $billingAddress = $address;
            break;
        }
    }
    // If no billing address found, use first address
    if (!$billingAddress && count($client->clientAddresses) > 0) {
        $billingAddress = $client->clientAddresses[0];
    }
}

// Get primary contact if available
$primaryContact = null;
if ($client && isset($client->clientContacts) && is_array($client->clientContacts)) {
    foreach ($client->clientContacts as $contact) {
        if (isset($contact->contactTypeID) && $contact->contactTypeID == 1) { // Assuming 1 is primary
            $primaryContact = $contact;
            break;
        }
    }
    // If no primary contact found, use first contact
    if (!$primaryContact && count($client->clientContacts) > 0) {
        $primaryContact = $client->clientContacts[0];
    }
}

    // Get template if exists
    $template = null;
    if (isset($invoice->templateID) && $invoice->templateID) {
        $template = Invoice::invoice_templates(array('templateID' => $invoice->templateID), true, $DBConn);
    } elseif ($templateID > 0) {
        $template = Invoice::invoice_templates(array('templateID' => $templateID), true, $DBConn);
    }
}

// Generate HTML content
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice <?= htmlspecialchars($invoice->invoiceNumber) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            padding: 20px;
            background: #fff;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border: 1px solid #ddd;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }
        .invoice-title {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }
        .invoice-number {
            font-size: 14px;
            color: #666;
        }
        .company-info, .client-info {
            margin-bottom: 30px;
        }
        .company-info h3, .client-info h3 {
            font-size: 16px;
            margin-bottom: 10px;
            color: #333;
        }
        .company-info p, .client-info p {
            margin: 5px 0;
            font-size: 12px;
        }
        .invoice-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .invoice-details div {
            flex: 1;
        }
        .invoice-details strong {
            display: block;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table th, table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        table tfoot td {
            font-weight: bold;
            border-top: 2px solid #333;
        }
        .text-right {
            text-align: right;
        }
        .invoice-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        .invoice-footer p {
            margin: 5px 0;
            font-size: 11px;
            color: #666;
        }
        @media print {
            body {
                padding: 0;
            }
            .invoice-container {
                border: none;
                padding: 20px;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Invoice Header -->
        <div class="invoice-header">
            <div>
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number"><?= htmlspecialchars($invoice->invoiceNumber) ?></div>
            </div>
            <div class="text-right">
                <?php if ($template && $template->logoURL): ?>
                    <img src="<?= htmlspecialchars($template->logoURL) ?>" alt="Logo" style="max-height: 80px;">
                <?php endif; ?>
            </div>
        </div>

        <!-- Company and Client Info -->
        <div style="display: flex; justify-content: space-between; margin-bottom: 30px;">
            <div class="company-info">
                <h3>From:</h3>
                <?php if ($template): ?>
                    <p><strong><?= htmlspecialchars($template->companyName ?: 'Company Name') ?></strong></p>
                    <?php if ($template->companyAddress): ?>
                        <p><?= nl2br(htmlspecialchars($template->companyAddress)) ?></p>
                    <?php endif; ?>
                    <?php if ($template->companyPhone): ?>
                        <p>Phone: <?= htmlspecialchars($template->companyPhone) ?></p>
                    <?php endif; ?>
                    <?php if ($template->companyEmail): ?>
                        <p>Email: <?= htmlspecialchars($template->companyEmail) ?></p>
                    <?php endif; ?>
                    <?php if ($template->companyTaxID): ?>
                        <p>Tax ID: <?= htmlspecialchars($template->companyTaxID) ?></p>
                    <?php endif; ?>
                <?php else: ?>
                    <p><strong>Your Company Name</strong></p>
                    <p>Company Address</p>
                    <p>Phone: +254 XXX XXX XXX</p>
                    <p>Email: info@company.com</p>
                <?php endif; ?>
            </div>

            <div class="client-info">
                <h3>Bill To:</h3>
                <p><strong><?= htmlspecialchars($invoice->clientName ?? 'N/A') ?></strong></p>
                <?php if ($client): ?>
                    <?php if ($billingAddress): ?>
                        <?php if (!empty($billingAddress->address)): ?>
                            <p><?= nl2br(htmlspecialchars($billingAddress->address)) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($billingAddress->city)): ?>
                            <p><?= htmlspecialchars($billingAddress->city) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($billingAddress->postalCode)): ?>
                            <p><?= htmlspecialchars($billingAddress->postalCode) ?></p>
                        <?php endif; ?>
                    <?php elseif (!empty($client->city)): ?>
                        <p><?= htmlspecialchars($client->city) ?></p>
                    <?php endif; ?>

                    <?php if ($primaryContact): ?>
                        <?php if (!empty($primaryContact->contactEmail)): ?>
                            <p><?= htmlspecialchars($primaryContact->contactEmail) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($primaryContact->contactPhone)): ?>
                            <p><?= htmlspecialchars($primaryContact->contactPhone) ?></p>
                        <?php endif; ?>
                    <?php elseif ($billingAddress && !empty($billingAddress->clientEmail)): ?>
                        <p><?= htmlspecialchars($billingAddress->clientEmail) ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            <div>
                <strong>Invoice Date:</strong>
                <?= date('d M Y', strtotime($invoice->invoiceDate)) ?>
            </div>
            <div>
                <strong>Due Date:</strong>
                <?= date('d M Y', strtotime($invoice->dueDate)) ?>
            </div>
            <?php if ($invoice->projectName): ?>
                <div>
                    <strong>Project:</strong>
                    <?= htmlspecialchars($invoice->projectName) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Invoice Items -->
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Discount</th>
                    <th class="text-right">Tax</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($invoice->items && is_array($invoice->items)): ?>
                    <?php foreach ($invoice->items as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><?= htmlspecialchars($item->itemDescription) ?></td>
                            <td class="text-right"><?= number_format($item->quantity, 2) ?></td>
                            <td class="text-right"><?= htmlspecialchars($invoice->currency) ?> <?= number_format($item->unitPrice, 2) ?></td>
                            <td class="text-right"><?= number_format($item->discountPercent, 2) ?>%</td>
                            <td class="text-right"><?= number_format($item->taxPercent, 2) ?>%</td>
                            <td class="text-right"><strong><?= htmlspecialchars($invoice->currency) ?> <?= number_format($item->lineTotal, 2) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No items</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="text-right"><strong>Subtotal:</strong></td>
                    <td class="text-right"><strong><?= htmlspecialchars($invoice->currency) ?> <?= number_format($invoice->subtotal ?? $invoice->invoiceAmount ?? 0, 2) ?></strong></td>
                </tr>
                <tr>
                    <td colspan="6" class="text-right"><strong>Tax:</strong></td>
                    <td class="text-right"><strong><?= htmlspecialchars($invoice->currency) ?> <?= number_format($invoice->taxAmount ?? 0, 2) ?></strong></td>
                </tr>
                <tr>
                    <td colspan="6" class="text-right"><strong>TOTAL:</strong></td>
                    <td class="text-right"><strong><?= htmlspecialchars($invoice->currency) ?> <?= number_format($invoice->totalAmount ?? 0, 2) ?></strong></td>
                </tr>
            </tfoot>
        </table>

        <!-- Notes and Terms -->
        <?php if ($invoice->notes || $invoice->terms): ?>
            <div class="invoice-footer">
                <?php if ($invoice->notes): ?>
                    <p><strong>Notes:</strong></p>
                    <p><?= nl2br(htmlspecialchars($invoice->notes)) ?></p>
                <?php endif; ?>
                <?php if ($invoice->terms): ?>
                    <p><strong>Payment Terms:</strong></p>
                    <p><?= nl2br(htmlspecialchars($invoice->terms)) ?></p>
                <?php elseif ($template && $template->defaultTerms): ?>
                    <p><strong>Payment Terms:</strong></p>
                    <p><?= nl2br(htmlspecialchars($template->defaultTerms)) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Payment Status -->
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
            <p><strong>Payment Status:</strong>
                <span style="color: <?= htmlspecialchars($invoice->invoiceStatusColor ?? '#6c757d') ?>">
                    <?= htmlspecialchars($invoice->invoiceStatusName ?? 'Unknown') ?>
                </span>
            </p>
            <?php if ($invoice->paidAmount > 0): ?>
                <p><strong>Amount Paid:</strong> <?= htmlspecialchars($invoice->currency) ?> <?= number_format($invoice->paidAmount, 2) ?></p>
            <?php endif; ?>
            <?php if ($invoice->outstandingAmount > 0): ?>
                <p><strong>Outstanding:</strong> <?= htmlspecialchars($invoice->currency) ?> <?= number_format($invoice->outstandingAmount, 2) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($format == 'html'): ?>
    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; cursor: pointer;">Print Invoice</button>
    </div>
    <?php endif; ?>
</body>
</html>
<?php
$html = ob_get_clean();

// If HTML format requested, output HTML
if ($format == 'html') {
    header('Content-Type: text/html; charset=utf-8');
    echo $html;
    exit;
}

// Generate PDF using DomPDF
try {
    // Check if DomPDF is available
    $dompdfPath = $base . 'vendor/autoload.php';
    if (file_exists($dompdfPath)) {
        require_once $dompdfPath;

        // Use fully qualified class names since use statements must be at top of file
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Output PDF
        $dompdf->stream('Invoice_' . $invoice->invoiceNumber . '.pdf', array('Attachment' => 0));
    } else {
        // Fallback to HTML if DomPDF not installed
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        echo '<div style="text-align: center; margin-top: 20px; padding: 20px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 5px;">';
        echo '<p><strong>Note:</strong> DomPDF library not installed. Install via Composer: <code>composer install</code></p>';
        echo '<p>Currently showing HTML preview. Use browser print to save as PDF.</p>';
        echo '</div>';
    }
} catch (Exception $e) {
    // Fallback to HTML on error
    header('Content-Type: text/html; charset=utf-8');
    echo $html;
    echo '<div style="text-align: center; margin-top: 20px; padding: 20px; background: #f8d7da; border: 1px solid #dc3545; border-radius: 5px;">';
    echo '<p><strong>Error generating PDF:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p>Showing HTML preview instead. Use browser print to save as PDF.</p>';
    echo '</div>';
}
?>

