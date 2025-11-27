<?php
/**
 * Invoice Create/Edit Page
 *
 * Create or edit invoices with project, hours, expenses integration
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

// Get employee details for organization context
$employeeID = $userDetails->ID;
$employeeDetails = Employee::employees(array('ID' => $employeeID), true, $DBConn);

// Resolve organization and entity IDs with fallback hierarchy
$orgDataID = isset($_GET['orgDataID'])
    ? Utility::clean_string($_GET['orgDataID'])
    : (isset($employeeDetails->orgDataID) && !empty($employeeDetails->orgDataID)
        ? $employeeDetails->orgDataID
        : (isset($_SESSION['orgDataID']) && !empty($_SESSION['orgDataID'])
            ? $_SESSION['orgDataID']
            : ""));

$entityID = isset($_GET['entityID'])
    ? Utility::clean_string($_GET['entityID'])
    : (isset($employeeDetails->entityID) && !empty($employeeDetails->entityID)
        ? $employeeDetails->entityID
        : (isset($_SESSION['entityID'])
            ? $_SESSION['entityID']
            : ''));

// Ensure orgDataID is not null
$orgDataID = $orgDataID ?: "";

$invoiceID = isset($_GET['iid']) ? intval($_GET['iid']) : 0;
$isEdit = $invoiceID > 0;
$invoice = null;
$invoiceItems = array();

if ($isEdit) {
    $invoice = Invoice::invoice_complete($invoiceID, $DBConn);
    if (!$invoice) {
        Alert::error("Invoice not found", true);
        header("Location: {$base}html/?s=user&ss=invoices&p=list");
        exit;
    }
    $invoiceItems = $invoice->items ?? array();
}

// Get projects first (to filter clients)
$projectsWhereArr = array('Suspended' => 'N');
if ($orgDataID) {
    $projectsWhereArr['orgDataID'] = $orgDataID;
}
if ($entityID) {
    $projectsWhereArr['entityID'] = $entityID;
}
// Exclude closed/inactive/cancelled projects
$allProjects = Projects::projects_mini($projectsWhereArr, false, $DBConn);

// Filter projects to only active ones (not closed, inactive, or cancelled)
$activeProjects = array();
$clientsWithActiveProjects = array();
if ($allProjects && is_array($allProjects)) {
    foreach ($allProjects as $project) {
        $status = strtolower($project->projectStatus ?? '');
        // Include projects that are not closed, inactive, or cancelled
        if (!in_array($status, array('closed', 'inactive', 'cancelled'))) {
            $activeProjects[] = $project;
            // Track which clients have active projects
            if ($project->clientID && !in_array($project->clientID, $clientsWithActiveProjects)) {
                $clientsWithActiveProjects[] = $project->clientID;
            }
        }
    }
}
$projects = $activeProjects;

// Get clients - only those with active projects
$clientsWhereArr = array('Suspended' => 'N');
if ($orgDataID) {
    $clientsWhereArr['orgDataID'] = $orgDataID;
}
if ($entityID) {
    $clientsWhereArr['entityID'] = $entityID;
}
$allClients = Client::clients($clientsWhereArr, false, $DBConn);

// Filter clients to only those with active projects
$clients = array();
if ($allClients && is_array($allClients)) {
    foreach ($allClients as $client) {
        if (in_array($client->clientID, $clientsWithActiveProjects)) {
            $clients[] = $client;
        }
    }
}

// Get templates
$templatesWhereArr = array('isActive' => 'Y', 'Suspended' => 'N');
if ($orgDataID) {
    $templatesWhereArr['orgDataID'] = $orgDataID;
}
if ($entityID) {
    $templatesWhereArr['entityID'] = $entityID;
}
$templates = Invoice::invoice_templates($templatesWhereArr, false, $DBConn);

$pageTitle = $isEdit ? "Edit Invoice" : "Create Invoice";
?>

<div class="main-content">
    <div class="page-header">
        <div class="page-header-content">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?>html/?s=user&ss=invoices&p=list">Invoices</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= $isEdit ? 'Edit' : 'Create' ?> Invoice</li>
                </ol>
            </nav>
            <div class="page-header-title">
                <h2><i class="ri-file-add-line me-2"></i><?= $isEdit ? 'Edit' : 'Create' ?> Invoice</h2>
                <p class="text-muted"><?= $isEdit ? 'Update invoice details' : 'Create a new invoice from projects, hours, and expenses' ?></p>
            </div>
        </div>
    </div>

    <div class="page-content">
        <form id="invoiceForm" method="POST">
            <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="invoiceID" value="<?= $invoiceID ?>">
            <?php endif; ?>

            <div class="row">
                <!-- Left Column - Invoice Details -->
                <div class="col-lg-4">
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Invoice Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Client <span class="text-danger">*</span></label>
                                <select name="clientID" id="clientID" class="form-select" required>
                                    <option value="">Select Client</option>
                                    <?php if ($clients && is_array($clients)): ?>
                                        <?php foreach ($clients as $client): ?>
                                            <option value="<?= $client->clientID ?>"
                                                    <?= ($isEdit && $invoice->clientID == $client->clientID) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($client->clientName) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Project</label>
                                <select name="projectID" id="projectID" class="form-select">
                                    <option value="">No Project</option>
                                    <!-- projects to be loaded dynamically based on client selection -->
                                </select>
                                <input type="hidden" id="preservedProjectID" value="<?= ($isEdit && isset($invoice->projectID)) ? $invoice->projectID : '' ?>">
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                                    <input type="date" name="invoiceDate" class="form-control"
                                           value="<?= $isEdit ? $invoice->invoiceDate : date('Y-m-d') ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Due Date <span class="text-danger">*</span></label>
                                    <input type="date" name="dueDate" class="form-control"
                                           value="<?= $isEdit ? $invoice->dueDate : date('Y-m-d', strtotime('+30 days')) ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Template</label>
                                <select name="templateID" id="templateID" class="form-select">
                                    <option value="">Default Template</option>
                                    <?php if ($templates && is_array($templates)): ?>
                                        <?php foreach ($templates as $template): ?>
                                            <option value="<?= $template->templateID ?>"
                                                    <?= ($isEdit && isset($invoice->templateID) && $invoice->templateID == $template->templateID) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($template->templateName) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Currency</label>
                                <select name="currency" class="form-select">
                                    <option value="KES" <?= (!$isEdit || $invoice->currency == 'KES') ? 'selected' : '' ?>>KES - Kenyan Shilling</option>
                                    <option value="USD" <?= ($isEdit && $invoice->currency == 'USD') ? 'selected' : '' ?>>USD - US Dollar</option>
                                    <option value="EUR" <?= ($isEdit && $invoice->currency == 'EUR') ? 'selected' : '' ?>>EUR - Euro</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Project Resources Card -->
                    <div class="card custom-card mb-4" id="projectResourcesCard" style="display: none;">
                        <div class="card-header">
                            <h5 class="mb-0">Add from Project</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Date Range</label>
                                <div class="row">
                                    <div class="col-6">
                                        <input type="date" id="dateFrom" class="form-control form-control-sm"
                                               value="<?= date('Y-m-01') ?>">
                                    </div>
                                    <div class="col-6">
                                        <input type="date" id="dateTo" class="form-control form-control-sm"
                                               value="<?= date('Y-m-d') ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="loadBillableHours">
                                    <i class="ri-time-line me-1"></i>Load Billable Hours
                                </button>
                                <button type="button" class="btn btn-outline-success btn-sm" id="loadExpenses">
                                    <i class="ri-money-dollar-circle-line me-1"></i>Load Expenses
                                </button>
                                <button type="button" class="btn btn-outline-info btn-sm" id="loadFeeExpenses">
                                    <i class="ri-file-list-3-line me-1"></i>Load Fee Expenses
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Invoice Items -->
                <div class="col-lg-8">
                    <div class="card custom-card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Invoice Items</h5>
                            <button type="button" class="btn btn-primary btn-sm" id="addCustomItem">
                                <i class="ri-add-line me-1"></i>Add Item
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="invoiceItemsTable">
                                    <thead>
                                        <tr>
                                            <th style="width: 5%">#</th>
                                            <th style="width: 30%">Description</th>
                                            <th style="width: 10%">Qty</th>
                                            <th style="width: 15%">Unit Price</th>
                                            <th style="width: 10%">Discount %</th>
                                            <th style="width: 10%">Tax %</th>
                                            <th style="width: 15%">Total</th>
                                            <th style="width: 5%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="invoiceItemsBody">
                                        <?php if ($isEdit && $invoiceItems): ?>
                                            <?php foreach ($invoiceItems as $index => $item): ?>
                                                <tr data-item-id="<?= $item->invoiceItemID ?>">
                                                    <td><?= $index + 1 ?></td>
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm item-description"
                                                               value="<?= htmlspecialchars($item->itemDescription) ?>" required>
                                                        <input type="hidden" class="item-type" value="<?= htmlspecialchars($item->itemType) ?>">
                                                        <input type="hidden" class="item-reference-id" value="<?= $item->itemReferenceID ?>">
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm item-quantity"
                                                               step="0.01" value="<?= $item->quantity ?>" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm item-unit-price"
                                                               step="0.01" value="<?= $item->unitPrice ?>" required>
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm item-discount"
                                                               step="0.01" value="<?= $item->discountPercent ?>" min="0" max="100">
                                                    </td>
                                                    <td>
                                                        <input type="number" class="form-control form-control-sm item-tax"
                                                               step="0.01" value="<?= $item->taxPercent ?>" min="0" max="100">
                                                    </td>
                                                    <td>
                                                        <span class="item-total">KES <?= number_format($item->lineTotal, 2) ?></span>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-outline-danger remove-item">
                                                            <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="6" class="text-end"><strong>Subtotal:</strong></td>
                                            <td><strong id="subtotal">KES 0.00</strong></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="6" class="text-end"><strong>Tax:</strong></td>
                                            <td><strong id="taxTotal">KES 0.00</strong></td>
                                            <td></td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td colspan="6" class="text-end"><strong>Total:</strong></td>
                                            <td><strong id="grandTotal">KES 0.00</strong></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Notes and Terms -->
                    <div class="card custom-card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Additional Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3"
                                          placeholder="Additional notes for the invoice"><?= $isEdit ? htmlspecialchars($invoice->notes ?? '') : '' ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Payment Terms</label>
                                <textarea name="terms" class="form-control" rows="2"
                                          placeholder="Payment terms and conditions"><?= $isEdit ? htmlspecialchars($invoice->terms ?? '') : '' ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="<?= $base ?>html/?s=user&ss=invoices&p=list" class="btn btn-outline-secondary">
                            <i class="ri-arrow-left-line me-1"></i>Cancel
                        </a>
                        <div>
                            <button type="button" class="btn btn-outline-info me-2" id="previewInvoice">
                                <i class="ri-eye-line me-1"></i>Preview
                            </button>
                            <button type="submit" class="btn btn-primary" id="saveInvoice">
                                <i class="ri-save-line me-1"></i><?= $isEdit ? 'Update' : 'Create' ?> Invoice
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Adding Custom Item -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Custom Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Description <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="newItemDescription" required>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="newItemQuantity" value="1" step="0.01" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Unit Price <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="newItemUnitPrice" step="0.01" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Discount %</label>
                        <input type="number" class="form-control" id="newItemDiscount" value="0" step="0.01" min="0" max="100">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tax %</label>
                    <input type="number" class="form-control" id="newItemTax" value="0" step="0.01" min="0" max="100">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAddItem">Add Item</button>
            </div>
        </div>
    </div>
</div>

<script>
// Use window object to avoid duplicate declaration errors
(function() {
    'use strict';
    // Set siteUrl on window if not already set, then use local const
    if (typeof window.siteUrl === 'undefined') {
        window.siteUrl = '<?= $base ?>';
    }
    const siteUrl = window.siteUrl;
    let itemCounter = <?= $isEdit && $invoiceItems ? count($invoiceItems) : 0 ?>;

// Calculate totals
function calculateTotals() {
    let subtotal = 0;
    let taxTotal = 0;

    document.querySelectorAll('#invoiceItemsBody tr').forEach((row, index) => {
        const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
        const unitPrice = parseFloat(row.querySelector('.item-unit-price').value) || 0;
        const discount = parseFloat(row.querySelector('.item-discount').value) || 0;
        const tax = parseFloat(row.querySelector('.item-tax').value) || 0;

        const lineSubtotal = quantity * unitPrice;
        const discountAmount = lineSubtotal * (discount / 100);
        const afterDiscount = lineSubtotal - discountAmount;
        const taxAmount = afterDiscount * (tax / 100);
        const lineTotal = afterDiscount + taxAmount;

        row.querySelector('.item-total').textContent = 'KES ' + lineTotal.toFixed(2);
        row.querySelector('td:first-child').textContent = index + 1;

        subtotal += afterDiscount;
        taxTotal += taxAmount;
    });

    document.getElementById('subtotal').textContent = 'KES ' + subtotal.toFixed(2);
    document.getElementById('taxTotal').textContent = 'KES ' + taxTotal.toFixed(2);
    document.getElementById('grandTotal').textContent = 'KES ' + (subtotal + taxTotal).toFixed(2);
}

// Add custom item
document.getElementById('addCustomItem')?.addEventListener('click', function() {
    const modal = new bootstrap.Modal(document.getElementById('addItemModal'));
    modal.show();
});

document.getElementById('confirmAddItem')?.addEventListener('click', function() {
    const description = document.getElementById('newItemDescription').value;
    const quantity = parseFloat(document.getElementById('newItemQuantity').value) || 1;
    const unitPrice = parseFloat(document.getElementById('newItemUnitPrice').value) || 0;
    const discount = parseFloat(document.getElementById('newItemDiscount').value) || 0;
    const tax = parseFloat(document.getElementById('newItemTax').value) || 0;

    if (!description || !unitPrice) {
        if (typeof showToast === 'function') {
            showToast('Please fill in description and unit price', 'warning');
        } else {
            alert('Please fill in description and unit price');
        }
        return;
    }

    addItemRow('custom', null, description, quantity, unitPrice, discount, tax);

    // Reset modal
    document.getElementById('newItemDescription').value = '';
    document.getElementById('newItemQuantity').value = '1';
    document.getElementById('newItemUnitPrice').value = '';
    document.getElementById('newItemDiscount').value = '0';
    document.getElementById('newItemTax').value = '0';

    bootstrap.Modal.getInstance(document.getElementById('addItemModal')).hide();
});

// Add item row
function addItemRow(type, referenceID, description, quantity, unitPrice, discount, tax) {
    const tbody = document.getElementById('invoiceItemsBody');
    const row = document.createElement('tr');
    itemCounter++;

    const lineSubtotal = quantity * unitPrice;
    const discountAmount = lineSubtotal * (discount / 100);
    const afterDiscount = lineSubtotal - discountAmount;
    const taxAmount = afterDiscount * (tax / 100);
    const lineTotal = afterDiscount + taxAmount;

    row.innerHTML = `
        <td>${itemCounter}</td>
        <td>
            <input type="text" class="form-control form-control-sm item-description" value="${description}" required>
            <input type="hidden" class="item-type" value="${type}">
            <input type="hidden" class="item-reference-id" value="${referenceID || ''}">
        </td>
        <td><input type="number" class="form-control form-control-sm item-quantity" step="0.01" value="${quantity}" required></td>
        <td><input type="number" class="form-control form-control-sm item-unit-price" step="0.01" value="${unitPrice}" required></td>
        <td><input type="number" class="form-control form-control-sm item-discount" step="0.01" value="${discount}" min="0" max="100"></td>
        <td><input type="number" class="form-control form-control-sm item-tax" step="0.01" value="${tax}" min="0" max="100"></td>
        <td><span class="item-total">KES ${lineTotal.toFixed(2)}</span></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="ri-delete-bin-line"></i></button></td>
    `;

    tbody.appendChild(row);

    // Add event listeners
    row.querySelectorAll('.item-quantity, .item-unit-price, .item-discount, .item-tax').forEach(input => {
        input.addEventListener('input', calculateTotals);
    });

    calculateTotals();
}

// Remove item
if (typeof EventDelegation !== 'undefined') {
    EventDelegation.on('.remove-item', 'click', function(e, target) {
        target.closest('tr').remove();
        calculateTotals();
    }, {}, document);
}

// Load billable hours
document.getElementById('loadBillableHours')?.addEventListener('click', function() {
    const projectID = document.getElementById('projectID').value;
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;

    if (!projectID) {
        if (typeof showToast === 'function') {
            showToast('Please select a project first', 'warning');
        } else {
            alert('Please select a project first');
        }
        return;
    }

    fetch(`${siteUrl}php/scripts/invoices/get_billable_data.php?type=hours&projectID=${projectID}&dateFrom=${dateFrom}&dateTo=${dateTo}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                // Show modal to select hours
                // For now, add all hours as a single line item
                const totalHours = data.data.reduce((sum, item) => sum + parseFloat(item.taskDuration || 0), 0);
                const avgRate = data.data.length > 0 ? data.data[0].billingRate || 0 : 0;

                if (totalHours > 0) {
                    addItemRow('work_hours', projectID, `Work hours - ${dateFrom} to ${dateTo}`, totalHours, avgRate, 0, 0);
                    if (typeof showToast === 'function') {
                        showToast('Billable hours loaded successfully', 'success');
                    }
                } else {
                    if (typeof showToast === 'function') {
                        showToast('No billable hours found for the selected period', 'info');
                    } else {
                        alert('No billable hours found for the selected period');
                    }
                }
            } else {
                if (typeof showToast === 'function') {
                    showToast('Error loading billable hours: ' + (data.message || 'Unknown error'), 'error');
                } else {
                    alert('Error loading billable hours: ' + (data.message || 'Unknown error'));
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof showToast === 'function') {
                showToast('An error occurred while loading billable hours', 'error');
            } else {
                alert('An error occurred while loading billable hours');
            }
        });
});

// Function to load projects dynamically based on selected client
function loadProjectsByClient(selectedClientID, preserveSelectedProjectID = null) {
    const projectSelect = document.getElementById('projectID');
    const projectResourcesCard = document.getElementById('projectResourcesCard');

    if (!projectSelect) return;

    // Clear all project options except "No Project"
    const noProjectOption = projectSelect.querySelector('option[value=""]');
    projectSelect.innerHTML = '';
    if (noProjectOption) {
        projectSelect.appendChild(noProjectOption);
    } else {
        // Create "No Project" option if it doesn't exist
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = 'No Project';
        projectSelect.appendChild(defaultOption);
    }

    // Reset project selection
    projectSelect.value = '';
    if (projectResourcesCard) {
        projectResourcesCard.style.display = 'none';
    }

    if (!selectedClientID || selectedClientID === '') {
        // No client selected - projects already cleared
        return;
    }

    // Show loading state
    const loadingOption = document.createElement('option');
    loadingOption.value = '';
    loadingOption.textContent = 'Loading projects...';
    loadingOption.disabled = true;
    projectSelect.appendChild(loadingOption);
    projectSelect.disabled = true;

    // Get organization context from the page (if available)
    const orgDataID = '<?= $orgDataID ?: "" ?>';
    const entityID = '<?= $entityID ?: "" ?>';

    // Build URL with parameters
    let url = `${siteUrl}php/scripts/invoices/get_client_projects.php?clientID=${selectedClientID}`;
    if (orgDataID) {
        url += `&orgDataID=${orgDataID}`;
    }
    if (entityID) {
        url += `&entityID=${entityID}`;
    }

    // Fetch projects from server
    fetch(url)
        .then(response => response.json())
        .then(data => {
            // Remove loading option
            projectSelect.innerHTML = '';
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'No Project';
            projectSelect.appendChild(defaultOption);

            if (data.success && data.projects && data.projects.length > 0) {
                // Add project options
                data.projects.forEach(project => {
                    const option = document.createElement('option');
                    option.value = project.projectID;
                    option.textContent = project.projectName;
                    option.setAttribute('data-client-id', project.clientID);
                    projectSelect.appendChild(option);
                });

                // Restore previously selected project if it exists and matches
                if (preserveSelectedProjectID) {
                    const optionToSelect = projectSelect.querySelector(`option[value="${preserveSelectedProjectID}"]`);
                    if (optionToSelect) {
                        projectSelect.value = preserveSelectedProjectID;
                        if (projectResourcesCard) {
                            projectResourcesCard.style.display = 'block';
                        }
                        // Trigger change event to ensure other handlers are notified
                        projectSelect.dispatchEvent(new Event('change'));
                    }
                }
            } else {
                // No projects found
                const noProjectsOption = document.createElement('option');
                noProjectsOption.value = '';
                noProjectsOption.textContent = 'No active projects found';
                noProjectsOption.disabled = true;
                projectSelect.appendChild(noProjectsOption);
            }

            projectSelect.disabled = false;
        })
        .catch(error => {
            console.error('Error loading projects:', error);

            // Remove loading option and show error
            projectSelect.innerHTML = '';
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'No Project';
            projectSelect.appendChild(defaultOption);

            const errorOption = document.createElement('option');
            errorOption.value = '';
            errorOption.textContent = 'Error loading projects';
            errorOption.disabled = true;
            projectSelect.appendChild(errorOption);
            projectSelect.disabled = false;

            if (typeof showToast === 'function') {
                showToast('An error occurred while loading projects. Please try again.', 'error');
            } else {
                alert('An error occurred while loading projects. Please try again.');
            }
        });
}

// Initialize calculations and event listeners
document.addEventListener('DOMContentLoaded', function() {
    calculateTotals();

    // Add event listeners to existing inputs
    document.querySelectorAll('#invoiceItemsBody .item-quantity, #invoiceItemsBody .item-unit-price, #invoiceItemsBody .item-discount, #invoiceItemsBody .item-tax').forEach(input => {
        input.addEventListener('input', calculateTotals);
    });

    // Get references to elements
    const clientSelect = document.getElementById('clientID');
    const projectSelect = document.getElementById('projectID');
    const projectResourcesCard = document.getElementById('projectResourcesCard');

    if (!clientSelect || !projectSelect) return;

    // Client selection handler - load projects dynamically for selected client
    clientSelect.addEventListener('change', function() {
        const selectedClientID = this.value;
        // Store current project selection before clearing
        const currentProjectID = projectSelect.value;
        loadProjectsByClient(selectedClientID, currentProjectID);
    });

    // Project selection handler
    projectSelect.addEventListener('change', function() {
        const projectID = this.value;
        if (projectResourcesCard) {
            if (projectID) {
                projectResourcesCard.style.display = 'block';
            } else {
                projectResourcesCard.style.display = 'none';
            }
        }
    });

    // Initialize project loading based on selected client (if editing)
    if (clientSelect.value) {
        // Client is already selected - load projects for this client
        // Check for preserved project ID (for edit mode)
        const preservedProjectID = document.getElementById('preservedProjectID')?.value || projectSelect.value || null;
        loadProjectsByClient(clientSelect.value, preservedProjectID);
    } else {
        // No client selected - projects dropdown is already empty
        // Just ensure "No Project" is selected
        projectSelect.value = '';
    }

    // Show project resources if project is selected
    if (projectSelect.value && projectResourcesCard) {
        projectResourcesCard.style.display = 'block';
    }

    // Form submission handler
    const invoiceForm = document.getElementById('invoiceForm');
    if (invoiceForm) {
        invoiceForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const items = [];

            document.querySelectorAll('#invoiceItemsBody tr').forEach(row => {
                items.push({
                    itemType: row.querySelector('.item-type').value,
                    itemReferenceID: row.querySelector('.item-reference-id').value || null,
                    itemDescription: row.querySelector('.item-description').value,
                    quantity: parseFloat(row.querySelector('.item-quantity').value),
                    unitPrice: parseFloat(row.querySelector('.item-unit-price').value),
                    discountPercent: parseFloat(row.querySelector('.item-discount').value) || 0,
                    taxPercent: parseFloat(row.querySelector('.item-tax').value) || 0
                });
            });

            formData.append('items', JSON.stringify(items));

            const submitBtn = document.getElementById('saveInvoice');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ri-loader-4-line spin me-1"></i>Saving...';

            fetch(`${siteUrl}php/scripts/invoices/manage_invoice.php`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (typeof showToast === 'function') {
                        showToast('Invoice saved successfully', 'success');
                    }
                    const redirectID = data.invoiceID || <?= $invoiceID ?: 0 ?>;
                    setTimeout(() => {
                        window.location.href = `${siteUrl}html/?s=user&ss=invoices&p=view&iid=${redirectID}`;
                    }, 1000);
                } else {
                    if (typeof showToast === 'function') {
                        showToast('Error: ' + data.message, 'error');
                    } else {
                        alert('Error: ' + data.message);
                    }
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="ri-save-line me-1"></i><?= $isEdit ? 'Update' : 'Create' ?> Invoice';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof showToast === 'function') {
                    showToast('An error occurred while saving the invoice', 'error');
                } else {
                    alert('An error occurred while saving the invoice');
                }
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="ri-save-line me-1"></i><?= $isEdit ? 'Update' : 'Create' ?> Invoice';
            });
        });
    }
    }); // End of DOMContentLoaded
})(); // End of IIFE - closes the immediately invoked function expression
</script>

<style>
.spin {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

