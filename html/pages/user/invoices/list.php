<?php
/**
 * Invoice List Page
 *
 * Displays all invoices with filtering, sorting, and pagination
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

// Get filters
$statusFilter = isset($_GET['status']) ? intval($_GET['status']) : 0;
$clientFilter = isset($_GET['clientID']) ? intval($_GET['clientID']) : 0;
$projectFilter = isset($_GET['projectID']) ? intval($_GET['projectID']) : 0;
$dateFrom = isset($_GET['dateFrom']) ? Utility::clean_string($_GET['dateFrom']) : '';
$dateTo = isset($_GET['dateTo']) ? Utility::clean_string($_GET['dateTo']) : '';

// Build where array
$whereArr = array(
    'Suspended' => 'N'
);

if ($orgDataID) {
    $whereArr['orgDataID'] = $orgDataID;
}
if ($entityID) {
    $whereArr['entityID'] = $entityID;
}

if ($statusFilter > 0) {
    $whereArr['invoiceStatusID'] = $statusFilter;
}
if ($clientFilter > 0) {
    $whereArr['clientID'] = $clientFilter;
}
if ($projectFilter > 0) {
    $whereArr['projectID'] = $projectFilter;
}

// Get invoices
$invoices = Invoice::invoices_full($whereArr, false, $DBConn);

// Get invoice statistics
$statsWhereArr = array();
if ($orgDataID) {
    $statsWhereArr['orgDataID'] = $orgDataID;
}
if ($entityID) {
    $statsWhereArr['entityID'] = $entityID;
}
$stats = Invoice::invoice_statistics($statsWhereArr, $DBConn);

// Get invoice statuses
$statuses = Invoice::invoice_statuses(array('isActive' => 'Y'), false, $DBConn);
// var_dump($orgDataID);
// var_dump($entityID);
// Get clients for filter
$clientsWhereArr = array('Suspended' => 'N');
if ($orgDataID) {
    $clientsWhereArr['orgDataID'] = $orgDataID;
}
if ($entityID) {
    $clientsWhereArr['entityID'] = $entityID;
}
$clients = Client::clients($clientsWhereArr, false, $DBConn);
// echo "<h4>Clients</h4>";
// var_dump($clients);
// Get projects for filter
$projectsWhereArr = array('Suspended' => 'N');
if ($orgDataID) {
    $projectsWhereArr['orgDataID'] = $orgDataID;
}
if ($entityID) {
    $projectsWhereArr['entityID'] = $entityID;
}
$projects = Projects::projects_mini($projectsWhereArr, false, $DBConn);

$pageTitle = "Invoices";
?>

<div class="main-content">
    <div class="page-header">
        <div class="page-header-content">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?>html/?s=user&ss=invoices&p=list">Invoicing</a></li>
                    <li class="breadcrumb-item active" aria-current="page">All Invoices</li>
                </ol>
            </nav>
            <div class="page-header-title">
                <h2><i class="ri-file-list-3-line me-2"></i>Invoices</h2>
                <p class="text-muted">Manage and track all invoices</p>
            </div>
        </div>
    </div>

    <div class="page-content">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-muted">Total Invoices</p>
                                <h3 class="mb-0"><?= $stats ? number_format($stats->total_invoices) : 0 ?></h3>
                            </div>
                            <div class="avatar avatar-md bg-primary-transparent">
                                <i class="ri-file-list-3-line fs-20"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-muted">Total Value</p>
                                <h3 class="mb-0">KES <?= $stats ? number_format($stats->total_value ?? 0, 2) : '0.00' ?></h3>
                            </div>
                            <div class="avatar avatar-md bg-success-transparent">
                                <i class="ri-money-dollar-circle-line fs-20"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-muted">Paid</p>
                                <h3 class="mb-0">KES <?= $stats ? number_format($stats->paid_value ?? 0, 2) : '0.00' ?></h3>
                            </div>
                            <div class="avatar avatar-md bg-info-transparent">
                                <i class="ri-checkbox-circle-line fs-20"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <p class="mb-1 text-muted">Overdue</p>
                                <h3 class="mb-0"><?= $stats ? number_format($stats->overdue_count ?? 0) : 0 ?></h3>
                            </div>
                            <div class="avatar avatar-md bg-danger-transparent">
                                <i class="ri-alert-line fs-20"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Card -->
        <div class="card custom-card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="ri-filter-line me-2"></i>Filters</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="0">All Statuses</option>
                            <?php if ($statuses && is_array($statuses)): ?>
                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?= $status->statusID ?>" <?= $statusFilter == $status->statusID ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($status->statusName) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Client</label>
                        <select name="clientID" class="form-select">
                            <option value="0">All Clients</option>
                            <?php if ($clients && is_array($clients)): ?>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?= $client->clientID ?>" <?= $clientFilter == $client->clientID ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($client->clientName) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Project</label>
                        <select name="projectID" class="form-select">
                            <option value="0">All Projects</option>
                            <?php if ($projects && is_array($projects)): ?>
                                <?php foreach ($projects as $project): ?>
                                    <option value="<?= $project->projectID ?>" <?= $projectFilter == $project->projectID ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($project->projectName) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="ri-search-line me-1"></i>Filter
                        </button>
                        <a href="<?= $base ?>html/?s=user&ss=invoices&p=list" class="btn btn-outline-secondary">
                            <i class="ri-refresh-line me-1"></i>Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Invoices Table -->
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Invoices</h5>
                <a href="<?= $base ?>html/?s=user&ss=invoices&p=create" class="btn btn-primary btn-sm">
                    <i class="ri-add-line me-1"></i>Create Invoice
                </a>
            </div>
            <div class="card-body">
                <?php if ($invoices && is_array($invoices) && count($invoices) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="invoicesTable">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Date</th>
                                    <th>Client</th>
                                    <th>Project</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Due Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($invoices as $invoice): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($invoice->invoiceNumber) ?></strong>
                                        </td>
                                        <td><?= date('d M Y', strtotime($invoice->invoiceDate)) ?></td>
                                        <td><?= htmlspecialchars($invoice->clientName ?? 'N/A') ?></td>
                                        <td>
                                            <?php if ($invoice->projectID): ?>
                                                <a href="<?= $base ?>html/?s=user&ss=projects&p=project&pid=<?= $invoice->projectID ?>">
                                                    <?= htmlspecialchars($invoice->projectName ?? 'N/A') ?>
                                                </a>
                                            <?php else: ?>
                                                N/A
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong>KES <?= number_format($invoice->totalAmount ?? 0, 2) ?></strong>
                                        </td>
                                        <td>
                                            <?php if ($invoice->invoiceStatusColor): ?>
                                                <span class="badge" style="background-color: <?= htmlspecialchars($invoice->invoiceStatusColor) ?>">
                                                    <?= htmlspecialchars($invoice->invoiceStatusName ?? 'Unknown') ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($invoice->invoiceStatusName ?? 'Unknown') ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $dueDate = strtotime($invoice->dueDate);
                                            $today = time();
                                            $daysDiff = ($dueDate - $today) / (60 * 60 * 24);
                                            ?>
                                            <span class="<?= $daysDiff < 0 && $invoice->invoiceStatusID != 3 ? 'text-danger' : '' ?>">
                                                <?= date('d M Y', $dueDate) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= $base ?>html/?s=user&ss=invoices&p=view&iid=<?= $invoice->invoiceID ?>"
                                                   class="btn btn-outline-primary"
                                                   title="View Invoice">
                                                    <i class="ri-eye-line"></i>
                                                </a>
                                                <?php if ($invoice->invoiceStatusID == 1): // Draft ?>
                                                    <a href="<?= $base ?>html/?s=user&ss=invoices&p=create&iid=<?= $invoice->invoiceID ?>"
                                                       class="btn btn-outline-info"
                                                       title="Edit Invoice">
                                                        <i class="ri-edit-line"></i>
                                                    </a>
                                                    <button type="button"
                                                            class="btn btn-outline-danger deleteInvoice"
                                                            data-invoice-id="<?= $invoice->invoiceID ?>"
                                                            data-invoice-number="<?= htmlspecialchars($invoice->invoiceNumber, ENT_QUOTES) ?>"
                                                            title="Delete Invoice">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="ri-inbox-line fs-48 text-muted"></i>
                        <p class="text-muted mt-3">No invoices found.</p>
                        <a href="<?= $base ?>html/?s=user&ss=invoices&p=create" class="btn btn-primary mt-2">
                            <i class="ri-add-line me-1"></i>Create Your First Invoice
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Use event delegation for delete buttons
if (typeof EventDelegation !== 'undefined') {
    EventDelegation.on('.deleteInvoice', 'click', function(e, target) {
        e.preventDefault();
        const invoiceID = target.getAttribute('data-invoice-id');
        const invoiceNumber = target.getAttribute('data-invoice-number');

        if (confirm(`Are you sure you want to delete invoice ${invoiceNumber}? This action cannot be undone.`)) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('invoiceID', invoiceID);

            fetch('<?= $base ?>php/scripts/invoices/manage_invoice.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (typeof showToast === 'function') {
                        showToast('Invoice deleted successfully', 'success');
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
                    showToast('An error occurred while deleting the invoice', 'error');
                } else {
                    alert('An error occurred while deleting the invoice');
                }
            });
        }
    }, {}, document);
}
</script>
