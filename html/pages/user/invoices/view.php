<?php
/**
 * Invoice View Page
 *
 * Display invoice details and allow printing/downloading
 *
 * @package    TIJA_PMS
 * @subpackage Invoicing
 */

// Security check
if(!$isValidUser) {
    Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

$invoiceID = isset($_GET['iid']) ? intval($_GET['iid']) : 0;

if (!$invoiceID) {
    Alert::error("Invoice ID is required", true);
    header("Location: {$base}html/?s=user&ss=invoices&p=list");
    exit;
}

$invoice = Invoice::invoice_complete($invoiceID, $DBConn);

if (!$invoice) {
    Alert::error("Invoice not found", true);
    header("Location: {$base}html/?s=user&ss=invoices&p=list");
    exit;
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

$pageTitle = "Invoice " . $invoice->invoiceNumber;
?>

<div class="main-content">
    <div class="page-header">
        <div class="page-header-content">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?>html/?s=user&ss=invoices&p=list">Invoices</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($invoice->invoiceNumber) ?></li>
                </ol>
            </nav>
            <div class="page-header-title d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="ri-file-list-3-line me-2"></i>Invoice <?= htmlspecialchars($invoice->invoiceNumber) ?></h2>
                    <p class="text-muted"><?= date('d M Y', strtotime($invoice->invoiceDate)) ?></p>
                </div>
                <div>
                    <?php if ($invoice->invoiceStatusID == 1): // Draft ?>
                        <a href="<?= $base ?>html/?s=user&ss=invoices&p=create&iid=<?= $invoiceID ?>" class="btn btn-primary me-2">
                            <i class="ri-edit-line me-1"></i>Edit
                        </a>
                    <?php endif; ?>
                    <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                        <i class="ri-printer-line me-1"></i>Print
                    </button>
                    <button type="button" class="btn btn-outline-success" id="downloadPDF">
                        <i class="ri-download-line me-1"></i>Download PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="page-content">
        <div class="row">
            <div class="col-lg-8">
                <!-- Invoice Display -->
                <div class="card custom-card mb-4" id="invoiceDisplay">
                    <div class="card-body">
                        <!-- Invoice Header -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h4 class="mb-3">Invoice</h4>
                                <p class="mb-1"><strong>Invoice #:</strong> <?= htmlspecialchars($invoice->invoiceNumber) ?></p>
                                <p class="mb-1"><strong>Date:</strong> <?= date('d M Y', strtotime($invoice->invoiceDate)) ?></p>
                                <p class="mb-1"><strong>Due Date:</strong> <?= date('d M Y', strtotime($invoice->dueDate)) ?></p>
                                <?php if ($invoice->projectName): ?>
                                    <p class="mb-1"><strong>Project:</strong> <?= htmlspecialchars($invoice->projectName) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 text-end">
                                <span class="badge fs-14" style="background-color: <?= htmlspecialchars($invoice->invoiceStatusColor ?? '#6c757d') ?>">
                                    <?= htmlspecialchars($invoice->invoiceStatusName ?? 'Unknown') ?>
                                </span>
                            </div>
                        </div>

                        <!-- Client Information -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5 class="mb-2">Bill To:</h5>
                                <p class="mb-1"><strong><?= htmlspecialchars($invoice->clientName ?? 'N/A') ?></strong></p>
                                <?php if ($client): ?>
                                    <?php if ($billingAddress): ?>
                                        <?php if (!empty($billingAddress->address)): ?>
                                            <p class="mb-1"><?= nl2br(htmlspecialchars(Utility::clean_string($billingAddress->address))) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($billingAddress->city)): ?>
                                            <p class="mb-1"><?= htmlspecialchars($billingAddress->city) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($billingAddress->postalCode)): ?>
                                            <p class="mb-1"><?= htmlspecialchars($billingAddress->postalCode) ?></p>
                                        <?php endif; ?>
                                    <?php elseif (!empty($client->city)): ?>
                                        <p class="mb-1"><?= htmlspecialchars($client->city) ?></p>
                                    <?php endif; ?>

                                    <?php if ($primaryContact): ?>
                                        <?php if (!empty($primaryContact->contactEmail)): ?>
                                            <p class="mb-1"><?= htmlspecialchars($primaryContact->contactEmail) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($primaryContact->contactPhone)): ?>
                                            <p class="mb-1"><?= htmlspecialchars($primaryContact->contactPhone) ?></p>
                                        <?php endif; ?>
                                    <?php elseif ($billingAddress && !empty($billingAddress->clientEmail)): ?>
                                        <p class="mb-1"><?= htmlspecialchars($billingAddress->clientEmail) ?></p>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Invoice Items -->
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Description</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Discount</th>
                                        <th class="text-end">Tax</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($invoice->items && is_array($invoice->items)): ?>
                                        <?php foreach ($invoice->items as $index => $item): ?>
                                            <tr>
                                                <td><?= $index + 1 ?></td>
                                                <td><?= htmlspecialchars($item->itemDescription) ?></td>
                                                <td class="text-end"><?= number_format($item->quantity, 2) ?></td>
                                                <td class="text-end">KES <?= number_format($item->unitPrice, 2) ?></td>
                                                <td class="text-end"><?= number_format($item->discountPercent, 2) ?>%</td>
                                                <td class="text-end"><?= number_format($item->taxPercent, 2) ?>%</td>
                                                <td class="text-end"><strong>KES <?= number_format($item->lineTotal, 2) ?></strong></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">No items</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="6" class="text-end"><strong>Subtotal:</strong></td>
                                        <td class="text-end"><strong>KES <?= number_format($invoice->subtotal ?? $invoice->invoiceAmount ?? 0, 2) ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-end"><strong>Tax:</strong></td>
                                        <td class="text-end"><strong>KES <?= number_format($invoice->taxAmount ?? 0, 2) ?></strong></td>
                                    </tr>
                                    <tr class="table-primary">
                                        <td colspan="6" class="text-end"><strong>Total:</strong></td>
                                        <td class="text-end"><strong>KES <?= number_format($invoice->totalAmount ?? 0, 2) ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Notes and Terms -->
                        <?php if ($invoice->notes || $invoice->terms): ?>
                            <div class="row">
                                <?php if ($invoice->notes): ?>
                                    <div class="col-md-6 mb-3">
                                        <h6>Notes:</h6>
                                        <p class="text-muted"><?= nl2br(htmlspecialchars($invoice->notes)) ?></p>
                                    </div>
                                <?php endif; ?>
                                <?php if ($invoice->terms): ?>
                                    <div class="col-md-6 mb-3">
                                        <h6>Payment Terms:</h6>
                                        <p class="text-muted"><?= nl2br(htmlspecialchars($invoice->terms)) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Payment Information -->
                <div class="card custom-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Payment Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <p class="mb-1 text-muted">Invoice Amount</p>
                            <h4>KES <?= number_format($invoice->totalAmount ?? 0, 2) ?></h4>
                        </div>
                        <div class="mb-3">
                            <p class="mb-1 text-muted">Amount Paid</p>
                            <h5 class="text-success">KES <?= number_format($invoice->paidAmount ?? 0, 2) ?></h5>
                        </div>
                        <div class="mb-3">
                            <p class="mb-1 text-muted">Outstanding</p>
                            <h5 class="<?= ($invoice->outstandingAmount ?? 0) > 0 ? 'text-danger' : 'text-success' ?>">
                                KES <?= number_format($invoice->outstandingAmount ?? 0, 2) ?>
                            </h5>
                        </div>
                    </div>
                </div>

                <!-- Payment History -->
                <?php if ($invoice->payments && is_array($invoice->payments) && count($invoice->payments) > 0): ?>
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Payment History</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($invoice->payments as $payment): ?>
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                                    <div>
                                        <p class="mb-0"><strong><?= htmlspecialchars($payment->paymentNumber) ?></strong></p>
                                        <small class="text-muted"><?= date('d M Y', strtotime($payment->paymentDate)) ?></small>
                                        <br>
                                        <small class="text-muted"><?= ucfirst(str_replace('_', ' ', $payment->paymentMethod)) ?></small>
                                    </div>
                                    <div class="text-end">
                                        <strong>KES <?= number_format($payment->paymentAmount, 2) ?></strong>
                                        <br>
                                        <span class="badge bg-<?= $payment->status == 'verified' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($payment->status) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Actions -->
                <div class="card custom-card">
                    <div class="card-header">
                        <h5 class="mb-0">Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?php if ($invoice->invoiceStatusID == 1): // Draft ?>
                                <a href="<?= $base ?>html/?s=user&ss=invoices&p=create&iid=<?= $invoiceID ?>" class="btn btn-primary">
                                    <i class="ri-edit-line me-1"></i>Edit Invoice
                                </a>
                                <button type="button" class="btn btn-success" id="markAsSent">
                                    <i class="ri-send-plane-line me-1"></i>Mark as Sent
                                </button>
                            <?php elseif ($invoice->invoiceStatusID == 2): // Sent ?>
                                <button type="button" class="btn btn-success" id="addPayment">
                                    <i class="ri-money-dollar-circle-line me-1"></i>Record Payment
                                </button>
                            <?php endif; ?>
                            <a href="<?= $base ?>html/?s=user&ss=invoices&p=list" class="btn btn-outline-secondary">
                                <i class="ri-arrow-left-line me-1"></i>Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';
    // Set siteUrl on window if not already set, then use local const
    if (typeof window.siteUrl === 'undefined') {
        window.siteUrl = '<?= $base ?>';
    }
    const siteUrl = window.siteUrl;
    const invoiceID = <?= $invoiceID ?>;

    // Mark as sent
    document.getElementById('markAsSent')?.addEventListener('click', function() {
        if (confirm('Mark this invoice as sent?')) {
            const formData = new FormData();
            formData.append('action', 'update_status');
            formData.append('invoiceID', invoiceID);
            formData.append('statusID', 2); // Sent

            fetch(`${siteUrl}php/scripts/invoices/manage_invoice.php`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (typeof showToast === 'function') {
                        showToast('Invoice status updated successfully', 'success');
                    }
                    setTimeout(() => location.reload(), 1000);
                } else {
                    if (typeof showToast === 'function') {
                        showToast('Error: ' + data.message, 'error');
                    } else {
                        alert('Error: ' + data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof showToast === 'function') {
                    showToast('An error occurred', 'error');
                } else {
                    alert('An error occurred');
                }
            });
        }
    });

    // Download PDF
    document.getElementById('downloadPDF')?.addEventListener('click', function() {
        window.open(`${siteUrl}php/scripts/invoices/generate_pdf.php?invoiceID=${invoiceID}`, '_blank');
    });

    // Print styles
    const printStyles = `
        @media print {
            .page-header, .page-header-content, .btn, .card-header .btn, #invoiceDisplay .btn {
                display: none !important;
            }
            .card {
                border: none !important;
                box-shadow: none !important;
            }
        }
    `;
    const styleSheet = document.createElement("style");
    styleSheet.textContent = printStyles;
    document.head.appendChild(styleSheet);
})();
</script>