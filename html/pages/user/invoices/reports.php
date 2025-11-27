<?php
/**
 * Invoice Reports Page
 *
 * Generate and view invoice reports
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

// Admin check
if (!$isAdmin && !$isValidAdmin) {
    Alert::error("You need administrator privileges to view reports", true);
    header("Location: {$base}html/?s=user&ss=invoices&p=list");
    exit;
}

// Get employee details for organization context
$employeeID = $userDetails->ID;
$employeeDetails = Employee::employees(array('ID' => $employeeID), true, $DBConn);

// Resolve organization and entity IDs
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

$orgDataID = $orgDataID ?: "";

// Get filter parameters
$dateFrom = isset($_GET['dateFrom']) ? Utility::clean_string($_GET['dateFrom']) : date('Y-m-01'); // First day of current month
$dateTo = isset($_GET['dateTo']) ? Utility::clean_string($_GET['dateTo']) : date('Y-m-t'); // Last day of current month
$statusID = isset($_GET['statusID']) ? intval($_GET['statusID']) : 0;
$clientID = isset($_GET['clientID']) ? intval($_GET['clientID']) : 0;

// Build where clause
$whereArr = array('Suspended' => 'N');
if ($orgDataID) {
    $whereArr['orgDataID'] = $orgDataID;
}
if ($entityID) {
    $whereArr['entityID'] = $entityID;
}
if ($dateFrom) {
    $whereArr['invoiceDate >='] = $dateFrom;
}
if ($dateTo) {
    $whereArr['invoiceDate <='] = $dateTo;
}
if ($statusID > 0) {
    $whereArr['invoiceStatusID'] = $statusID;
}
if ($clientID > 0) {
    $whereArr['clientID'] = $clientID;
}

// Get invoices with full details
$invoices = Invoice::invoices_full($whereArr, false, $DBConn);

// Calculate statistics
$totalInvoices = $invoices ? count($invoices) : 0;
$totalValue = 0;
$totalPaid = 0;
$totalOutstanding = 0;
$overdueCount = 0;
$today = date('Y-m-d');

if ($invoices && is_array($invoices)) {
    foreach ($invoices as $inv) {
        $totalValue += floatval($inv->totalAmount ?? $inv->invoiceAmount ?? 0);
        $totalPaid += floatval($inv->paidAmount ?? 0);
        $totalOutstanding += floatval($inv->outstandingAmount ?? 0);
        if ($inv->invoiceStatusID == 2 && $inv->dueDate < $today && floatval($inv->outstandingAmount ?? 0) > 0) {
            $overdueCount++;
        }
    }
}

// Get clients for filter
$clientsWhereArr = array('Suspended' => 'N');
if ($orgDataID) {
    $clientsWhereArr['orgDataID'] = $orgDataID;
}
if ($entityID) {
    $clientsWhereArr['entityID'] = $entityID;
}
$clients = Client::clients($clientsWhereArr, false, $DBConn);

// Get invoice statuses
$invoiceStatuses = Invoice::invoice_statuses(array('isActive' => 'Y'), false, $DBConn);

$pageTitle = "Invoice Reports";
?>

<div class="main-content">
    <div class="page-header">
        <div class="page-header-content">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?>html/?s=user&ss=invoices&p=list">Invoices</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Reports</li>
                </ol>
            </nav>
            <div class="page-header-title">
                <h2><i class="ri-bar-chart-line me-2"></i>Invoice Reports</h2>
                <p class="text-muted">View detailed invoice analytics and statistics</p>
            </div>
        </div>
    </div>

    <div class="page-content">
        <!-- Filters -->
        <div class="card custom-card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Filters</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="" id="reportFilters">
                    <input type="hidden" name="s" value="user">
                    <input type="hidden" name="ss" value="invoices">
                    <input type="hidden" name="p" value="reports">

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="dateFrom" class="form-label">From Date</label>
                            <input type="date" id="dateFrom" name="dateFrom" class="form-control" value="<?= htmlspecialchars($dateFrom) ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="dateTo" class="form-label">To Date</label>
                            <input type="date" id="dateTo" name="dateTo" class="form-control" value="<?= htmlspecialchars($dateTo) ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="statusID" class="form-label">Status</label>
                            <select id="statusID" name="statusID" class="form-select">
                                <option value="0">All Statuses</option>
                                <?php if ($invoiceStatuses): ?>
                                    <?php foreach ($invoiceStatuses as $status): ?>
                                        <option value="<?= $status->statusID ?>" <?= $statusID == $status->statusID ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($status->statusName) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="clientID" class="form-label">Client</label>
                            <select id="clientID" name="clientID" class="form-select">
                                <option value="0">All Clients</option>
                                <?php if ($clients): ?>
                                    <?php foreach ($clients as $client): ?>
                                        <option value="<?= $client->clientID ?>" <?= $clientID == $client->clientID ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($client->clientName) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-search-line me-1"></i>Apply Filters
                            </button>
                            <a href="<?= $base ?>html/?s=user&ss=invoices&p=reports" class="btn btn-outline-secondary">
                                <i class="ri-refresh-line me-1"></i>Reset
                            </a>
                            <button type="button" class="btn btn-success" onclick="exportReport()">
                                <i class="ri-download-line me-1"></i>Export Report
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-lg-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-top justify-content-between">
                            <div class="flex-fill">
                                <p class="mb-1 text-muted">Total Invoices</p>
                                <h3 class="mb-0 fw-semibold"><?= number_format($totalInvoices) ?></h3>
                                <small class="text-muted fs-11">In selected period</small>
                            </div>
                            <div class="ms-2">
                                <span class="avatar avatar-md bg-primary-transparent">
                                    <i class="ri-file-list-3-line fs-20"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-top justify-content-between">
                            <div class="flex-fill">
                                <p class="mb-1 text-muted">Total Value</p>
                                <h3 class="mb-0 fw-semibold">KES <?= number_format($totalValue, 2) ?></h3>
                                <small class="text-muted fs-11">Invoice amount</small>
                            </div>
                            <div class="ms-2">
                                <span class="avatar avatar-md bg-success-transparent">
                                    <i class="ri-money-dollar-circle-line fs-20"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-top justify-content-between">
                            <div class="flex-fill">
                                <p class="mb-1 text-muted">Total Paid</p>
                                <h3 class="mb-0 fw-semibold">KES <?= number_format($totalPaid, 2) ?></h3>
                                <small class="text-muted fs-11">Amount received</small>
                            </div>
                            <div class="ms-2">
                                <span class="avatar avatar-md bg-info-transparent">
                                    <i class="ri-checkbox-circle-line fs-20"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6">
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex align-items-top justify-content-between">
                            <div class="flex-fill">
                                <p class="mb-1 text-muted">Outstanding</p>
                                <h3 class="mb-0 fw-semibold">KES <?= number_format($totalOutstanding, 2) ?></h3>
                                <small class="text-muted fs-11"><?= $overdueCount ?> overdue</small>
                            </div>
                            <div class="ms-2">
                                <span class="avatar avatar-md bg-warning-transparent">
                                    <i class="ri-alert-line fs-20"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice List -->
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Invoice Details</h5>
                <span class="badge bg-primary"><?= $totalInvoices ?> invoices</span>
            </div>
            <div class="card-body">
                <?php if (!$invoices || count($invoices) == 0): ?>
                    <div class="text-center py-5">
                        <div class="avatar avatar-xl bg-primary-transparent mx-auto mb-4">
                            <i class="ri-file-list-3-line fs-32"></i>
                        </div>
                        <h5 class="mb-3">No Invoices Found</h5>
                        <p class="text-muted mb-4">No invoices match the selected filters</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-primary">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Date</th>
                                    <th>Client</th>
                                    <th>Project</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Outstanding</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($invoices as $invoice): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($invoice->invoiceNumber) ?></strong></td>
                                        <td><?= date('d M Y', strtotime($invoice->invoiceDate)) ?></td>
                                        <td><?= htmlspecialchars($invoice->clientName ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($invoice->projectName ?? '-') ?></td>
                                        <td class="text-end"><?= htmlspecialchars($invoice->currency) ?> <?= number_format($invoice->totalAmount ?? $invoice->invoiceAmount ?? 0, 2) ?></td>
                                        <td class="text-end"><?= htmlspecialchars($invoice->currency) ?> <?= number_format($invoice->paidAmount ?? 0, 2) ?></td>
                                        <td class="text-end">
                                            <?php
                                            $outstanding = floatval($invoice->outstandingAmount ?? 0);
                                            $isOverdue = $invoice->invoiceStatusID == 2 && $invoice->dueDate < $today && $outstanding > 0;
                                            ?>
                                            <span class="<?= $isOverdue ? 'text-danger fw-bold' : '' ?>">
                                                <?= htmlspecialchars($invoice->currency) ?> <?= number_format($outstanding, 2) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge" style="background-color: <?= htmlspecialchars($invoice->invoiceStatusColor ?? '#6c757d') ?>">
                                                <?= htmlspecialchars($invoice->invoiceStatusName ?? 'Unknown') ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= $base ?>html/?s=user&ss=invoices&p=view&iid=<?= $invoice->invoiceID ?>" class="btn btn-sm btn-primary-light btn-wave" title="View">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-secondary">
                                <tr>
                                    <th colspan="4" class="text-end">Totals:</th>
                                    <th class="text-end">KES <?= number_format($totalValue, 2) ?></th>
                                    <th class="text-end">KES <?= number_format($totalPaid, 2) ?></th>
                                    <th class="text-end">KES <?= number_format($totalOutstanding, 2) ?></th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function exportReport() {
    const form = document.getElementById('reportFilters');
    const formData = new FormData(form);
    formData.append('export', '1');

    // Create a temporary form and submit it
    const tempForm = document.createElement('form');
    tempForm.method = 'POST';
    tempForm.action = '<?= $base ?>php/scripts/invoices/export_report.php';

    for (const [key, value] of formData.entries()) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        tempForm.appendChild(input);
    }

    document.body.appendChild(tempForm);
    tempForm.submit();
    document.body.removeChild(tempForm);
}
</script>

