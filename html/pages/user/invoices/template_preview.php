<?php
/**
 * Invoice Template Preview Page
 *
 * Preview template with sample data
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

$templateID = isset($_GET['tid']) ? intval($_GET['tid']) : 0;

if (!$templateID) {
    Alert::error("Template ID is required", true);
    header("Location: {$base}html/?s=user&ss=invoices&p=templates");
    exit;
}

$template = Invoice::invoice_templates(array('templateID' => $templateID), true, $DBConn);

if (!$template) {
    Alert::error("Template not found", true);
    header("Location: {$base}html/?s=user&ss=invoices&p=templates");
    exit;
}

// Create sample invoice data for preview
$sampleInvoice = (object) array(
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

$pageTitle = "Template Preview: " . $template->templateName;
?>

<div class="main-content">
    <div class="page-header">
        <div class="page-header-content">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?>html/?s=user&ss=invoices&p=templates">Templates</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Preview</li>
                </ol>
            </nav>
            <div class="page-header-title d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="ri-eye-line me-2"></i>Template Preview</h2>
                    <p class="text-muted"><?= htmlspecialchars($template->templateName) ?></p>
                </div>
                <div>
                    <a href="<?= $base ?>php/scripts/invoices/generate_pdf.php?invoiceID=0&format=pdf&templateID=<?= $templateID ?>" class="btn btn-outline-primary" target="_blank">
                        <i class="ri-file-pdf-line me-1"></i>Generate PDF Preview
                    </a>
                    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="ri-printer-line me-1"></i>Print Preview
                    </button>
                    <a href="<?= $base ?>html/?s=user&ss=invoices&p=templates" class="btn btn-secondary">
                        <i class="ri-arrow-left-line me-1"></i>Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="page-content">
        <div class="alert alert-info">
            <i class="ri-information-line me-2"></i>
            <strong>Preview Mode:</strong> This is a sample invoice using the selected template. Actual invoices will use real client and project data.
        </div>

        <div class="card custom-card">
            <div class="card-body">
                <iframe src="<?= $base ?>php/scripts/invoices/generate_pdf.php?invoiceID=0&format=html&templateID=<?= $templateID ?>&preview=1"
                        style="width: 100%; height: 800px; border: 1px solid #ddd; border-radius: 4px;"
                        frameborder="0">
                </iframe>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .page-header, .page-header-content, .btn, .alert {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>

