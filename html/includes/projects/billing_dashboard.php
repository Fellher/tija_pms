<?php
/**
 * Project Billing Dashboard
 * Comprehensive billing and financial management for projects
 *
 * @package Tija Practice Management System
 * @subpackage Projects - Billing
 * @version 2.0
 */

// Get billing data for this project
// Use Invoice class for invoices (not Projects class)
$projectInvoices = false;
if (class_exists('Invoice')) {
    try {
        $projectInvoices = Invoice::invoices(['projectID' => $projectID, 'Suspended' => 'N'], false, $DBConn);
    } catch (Exception $e) {
        error_log("Failed to load invoices: " . $e->getMessage());
        $projectInvoices = false;
    }
}

// Load project expenses
$projectExpenses = Projects::project_expenses(['projectID' => $projectID, 'Suspended' => 'N'], false, $DBConn);

// Check if project is recurring and load billing cycles
$isRecurring = false;
$billingCycles = [];
if (isset($projectDetails)) {
    if (isset($projectDetails->isRecurring) && $projectDetails->isRecurring === 'Y') {
        $isRecurring = true;
    } elseif (isset($projectDetails->projectType) && $projectDetails->projectType === 'recurrent') {
        $isRecurring = true;
    }
}

if ($isRecurring) {
    $billingCycles = Projects::get_billing_cycles(['projectID' => $projectID, 'Suspended' => 'N'], false, $DBConn);
}

// Calculate billing metrics
$billingMetrics = [
    'totalInvoiced' => 0,
    'totalPaid' => 0,
    'totalPending' => 0,
    'totalOverdue' => 0,
    'totalExpenses' => 0,
    'profitMargin' => 0,
    'invoiceCount' => 0,
    'expenseCount' => 0
];

if ($projectInvoices && is_array($projectInvoices)) {
    $billingMetrics['invoiceCount'] = count($projectInvoices);
    foreach ($projectInvoices as $invoice) {
        $billingMetrics['totalInvoiced'] += ($invoice->totalAmount ?? 0);

        if ($invoice->invoiceStatus == 'paid') {
            $billingMetrics['totalPaid'] += ($invoice->totalAmount ?? 0);
        } elseif ($invoice->invoiceStatus == 'pending') {
            $billingMetrics['totalPending'] += ($invoice->totalAmount ?? 0);
        } elseif ($invoice->invoiceStatus == 'overdue') {
            $billingMetrics['totalOverdue'] += ($invoice->totalAmount ?? 0);
        }
    }
}

if ($projectExpenses && is_array($projectExpenses)) {
    $billingMetrics['expenseCount'] = count($projectExpenses);
    foreach ($projectExpenses as $expense) {
        if ($expense->status == 'approved') {
            $billingMetrics['totalExpenses'] += ($expense->amount ?? 0);
        }
    }
}

// Calculate profit margin
$revenue = $billingMetrics['totalPaid'];
$costs = $billingMetrics['totalExpenses'];
if ($revenue > 0) {
    $billingMetrics['profitMargin'] = (($revenue - $costs) / $revenue) * 100;
}

$currency = $projectDetails->currency ?? 'KES';
?>

<!-- Billing Dashboard -->
<div class="billing-dashboard">

    <!-- Financial Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card custom-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md bg-primary-transparent me-3">
                            <i class="fas fa-file-invoice-dollar fs-20"></i>
                        </div>
                        <div class="flex-fill">
                            <p class="mb-1 text-muted small">Total Invoiced</p>
                            <h4 class="mb-0"><?= number_format($billingMetrics['totalInvoiced'], 2) ?></h4>
                            <small class="text-muted"><?= $currency ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card custom-card h-100 border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md bg-success-transparent me-3">
                            <i class="fas fa-check-circle fs-20"></i>
                        </div>
                        <div class="flex-fill">
                            <p class="mb-1 text-muted small">Paid</p>
                            <h4 class="mb-0 text-success"><?= number_format($billingMetrics['totalPaid'], 2) ?></h4>
                            <small class="text-muted"><?= $currency ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card custom-card h-100 border-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md bg-warning-transparent me-3">
                            <i class="fas fa-clock fs-20"></i>
                        </div>
                        <div class="flex-fill">
                            <p class="mb-1 text-muted small">Pending</p>
                            <h4 class="mb-0 text-warning"><?= number_format($billingMetrics['totalPending'], 2) ?></h4>
                            <small class="text-muted"><?= $currency ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card custom-card h-100 border-danger">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md bg-danger-transparent me-3">
                            <i class="fas fa-exclamation-triangle fs-20"></i>
                        </div>
                        <div class="flex-fill">
                            <p class="mb-1 text-muted small">Overdue</p>
                            <h4 class="mb-0 text-danger"><?= number_format($billingMetrics['totalOverdue'], 2) ?></h4>
                            <small class="text-muted"><?= $currency ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Billing Cycles Section (for recurring projects) -->
    <?php if ($isRecurring && isset($billingCycles)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <?php include $base . 'html/includes/projects/billing_cycles_display.php'; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Budget vs Actual Row -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card custom-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Budget Performance</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <h6 class="text-muted mb-2">Project Budget</h6>
                            <h3 class="mb-0"><?= number_format($projectDetails->totalBudget ?? 0, 2) ?></h3>
                            <small class="text-muted"><?= $currency ?></small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h6 class="text-muted mb-2">Revenue (Paid)</h6>
                            <h3 class="mb-0 text-success"><?= number_format($billingMetrics['totalPaid'], 2) ?></h3>
                            <small class="text-muted"><?= $currency ?></small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h6 class="text-muted mb-2">Expenses</h6>
                            <h3 class="mb-0 text-danger"><?= number_format($billingMetrics['totalExpenses'], 2) ?></h3>
                            <small class="text-muted"><?= $currency ?></small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h6 class="text-muted mb-2">Profit Margin</h6>
                            <h3 class="mb-0 <?= $billingMetrics['profitMargin'] > 0 ? 'text-success' : 'text-danger' ?>">
                                <?= number_format($billingMetrics['profitMargin'], 1) ?>%
                            </h3>
                            <small class="text-muted">Net profit</small>
                        </div>
                    </div>

                    <!-- Budget Utilization Bar -->
                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Budget Utilization</span>
                            <span class="text-muted">
                                <?= ($projectDetails->totalBudget ?? 0) > 0 ?
                                    number_format(($billingMetrics['totalExpenses'] / ($projectDetails->totalBudget ?? 0)) * 100, 1) : 0 ?>%
                            </span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <?php $budgetUsed = ($projectDetails->totalBudget ?? 0) > 0 ?
                                ($billingMetrics['totalExpenses'] / ($projectDetails->totalBudget ?? 0)) * 100 : 0; ?>
                            <div class="progress-bar bg-<?= $budgetUsed > 90 ? 'danger' : ($budgetUsed > 75 ? 'warning' : 'success') ?>"
                                style="width: <?= min($budgetUsed, 100) ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoices and Expenses Row -->
    <div class="row">
        <!-- Invoices Column -->
        <div class="col-xl-8">
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Invoices</h6>
                    <button type="button" class="btn btn-primary btn-sm" onclick="createInvoice()">
                        <i class="fas fa-plus me-1"></i>Create Invoice
                    </button>
                </div>
                <div class="card-body">
                    <?php if ($projectInvoices && is_array($projectInvoices) && count($projectInvoices) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($projectInvoices as $invoice): ?>
                                        <?php
                                        $statusClass = [
                                            'draft' => 'secondary',
                                            'sent' => 'info',
                                            'pending' => 'warning',
                                            'paid' => 'success',
                                            'overdue' => 'danger',
                                            'cancelled' => 'dark'
                                        ];
                                        $badgeClass = $statusClass[$invoice->invoiceStatus] ?? 'secondary';
                                        ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($invoice->invoiceNumber ?? 'N/A') ?></strong></td>
                                            <td><?= date('M d, Y', strtotime($invoice->invoiceDate ?? 'now')) ?></td>
                                            <td><strong><?= number_format($invoice->totalAmount ?? 0, 2) ?></strong> <?= $currency ?></td>
                                            <td><?= $invoice->dueDate ? date('M d, Y', strtotime($invoice->dueDate)) : 'N/A' ?></td>
                                            <td><span class="badge bg-<?= $badgeClass ?>"><?= ucfirst($invoice->invoiceStatus ?? 'draft') ?></span></td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-sm btn-primary" onclick="viewInvoice(<?= $invoice->invoiceID ?>)" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-info" onclick="downloadInvoicePDF(<?= $invoice->invoiceID ?>)" title="Download PDF">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </button>
                                                    <?php if ($invoice->invoiceStatus != 'paid'): ?>
                                                        <button class="btn btn-sm btn-success" onclick="recordPayment(<?= $invoice->invoiceID ?>)" title="Record Payment">
                                                            <i class="fas fa-dollar-sign"></i>
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
                        <div class="text-center py-4">
                            <div class="avatar avatar-lg bg-primary-transparent mx-auto mb-3">
                                <i class="fas fa-file-invoice fs-24"></i>
                            </div>
                            <p class="text-muted mb-3">No invoices created yet</p>
                            <button type="button" class="btn btn-primary btn-sm" onclick="createInvoice()">
                                <i class="fas fa-plus me-1"></i>Create First Invoice
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Expenses Column -->
        <div class="col-xl-4">
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-receipt me-2"></i>Expenses</h6>
                    <button type="button" class="btn btn-success btn-sm" onclick="addExpense()">
                        <i class="fas fa-plus me-1"></i>Add
                    </button>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <?php if ($projectExpenses && is_array($projectExpenses) && count($projectExpenses) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($projectExpenses as $expense): ?>
                                <?php
                                $statusClass = [
                                    'pending' => 'warning',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'reimbursed' => 'info'
                                ];
                                $badgeClass = $statusClass[$expense->status] ?? 'secondary';
                                ?>
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($expense->expenseName ?? 'Expense') ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($expense->expenseCategory ?? 'General') ?></small>
                                        </div>
                                        <div class="text-end">
                                            <strong><?= number_format($expense->amount ?? 0, 2) ?></strong>
                                            <br><span class="badge bg-<?= $badgeClass ?>-transparent"><?= ucfirst($expense->status ?? 'pending') ?></span>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= date('M d, Y', strtotime($expense->expenseDate ?? 'now')) ?>
                                        </small>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-sm btn-light" onclick="viewExpense(<?= $expense->expenseID ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <?php if ($expense->status == 'pending'): ?>
                                                <button class="btn btn-sm btn-success" onclick="approveExpense(<?= $expense->expenseID ?>)">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-receipt text-muted fs-24 mb-2"></i>
                            <p class="text-muted mb-0">No expenses recorded</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="row">
        <div class="col-12">
            <div class="card custom-card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Recent Transactions</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Merge invoices and expenses for transaction history
                                $transactions = [];

                                if ($projectInvoices && is_array($projectInvoices)) {
                                    foreach ($projectInvoices as $inv) {
                                        $transactions[] = [
                                            'date' => $inv->invoiceDate ?? date('Y-m-d'),
                                            'type' => 'invoice',
                                            'description' => 'Invoice ' . ($inv->invoiceNumber ?? ''),
                                            'amount' => $inv->totalAmount ?? 0,
                                            'status' => $inv->invoiceStatus ?? 'draft',
                                            'isIncome' => true
                                        ];
                                    }
                                }

                                if ($projectExpenses && is_array($projectExpenses)) {
                                    foreach ($projectExpenses as $exp) {
                                        $transactions[] = [
                                            'date' => $exp->expenseDate ?? date('Y-m-d'),
                                            'type' => 'expense',
                                            'description' => $exp->expenseName ?? 'Expense',
                                            'amount' => $exp->amount ?? 0,
                                            'status' => $exp->status ?? 'pending',
                                            'isIncome' => false
                                        ];
                                    }
                                }

                                // Sort by date descending
                                usort($transactions, function($a, $b) {
                                    return strtotime($b['date']) - strtotime($a['date']);
                                });

                                // Display recent 10
                                $recentTransactions = array_slice($transactions, 0, 10);
                                ?>

                                <?php if (count($recentTransactions) > 0): ?>
                                    <?php foreach ($recentTransactions as $trans): ?>
                                        <tr>
                                            <td><?= date('M d, Y', strtotime($trans['date'])) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $trans['type'] == 'invoice' ? 'primary' : 'warning' ?>-transparent">
                                                    <?= ucfirst($trans['type']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($trans['description']) ?></td>
                                            <td>
                                                <span class="<?= $trans['isIncome'] ? 'text-success' : 'text-danger' ?>">
                                                    <?= $trans['isIncome'] ? '+' : '-' ?><?= number_format($trans['amount'], 2) ?> <?= $currency ?>
                                                </span>
                                            </td>
                                            <td><span class="badge bg-secondary-transparent"><?= ucfirst($trans['status']) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">No transactions yet</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Invoice Modal -->
<div class="modal fade" id="createInvoiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?= $base ?>php/scripts/projects/create_invoice.php">
                <?= SecurityMiddleware::csrfTokenField() ?>
                <input type="hidden" name="projectID" value="<?= $projectID ?>">

                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-invoice me-2"></i>Create Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Invoice Number</label>
                            <input type="text" class="form-control" name="invoiceNumber"
                                value="INV-<?= date('Ymd') ?>-<?= str_pad($billingMetrics['invoiceCount'] + 1, 4, '0', STR_PAD_LEFT) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Invoice Date</label>
                            <input type="date" class="form-control" name="invoiceDate" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><?= $currency ?></span>
                                <input type="number" class="form-control" name="amount" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Due Date</label>
                            <input type="date" class="form-control" name="dueDate" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= $base ?>php/scripts/projects/add_expense.php">
                <?= SecurityMiddleware::csrfTokenField() ?>
                <input type="hidden" name="projectID" value="<?= $projectID ?>">

                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-receipt me-2"></i>Add Expense</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Expense Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="expenseName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="expenseCategory">
                                <option value="Travel">Travel</option>
                                <option value="Materials">Materials</option>
                                <option value="Subcontractor">Subcontractor</option>
                                <option value="Equipment">Equipment</option>
                                <option value="Software">Software</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><?= $currency ?></span>
                                <input type="number" class="form-control" name="amount" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Expense Date</label>
                            <input type="date" class="form-control" name="expenseDate" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Record Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= $base ?>php/scripts/projects/record_payment.php">
                <?= SecurityMiddleware::csrfTokenField() ?>
                <input type="hidden" name="invoiceID" id="payment_invoiceID">

                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-money-bill-wave me-2"></i>Record Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><?= $currency ?></span>
                                <input type="number" class="form-control" name="paymentAmount" id="paymentAmount"
                                    step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Date</label>
                            <input type="date" class="form-control" name="paymentDate" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" name="paymentMethod">
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="mpesa">M-Pesa</option>
                                <option value="cheque">Cheque</option>
                                <option value="cash">Cash</option>
                                <option value="credit_card">Credit Card</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Reference Number</label>
                            <input type="text" class="form-control" name="referenceNumber" placeholder="Transaction reference">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function createInvoice() {
    const modal = new bootstrap.Modal(document.getElementById('createInvoiceModal'));
    modal.show();
}

function viewInvoice(invoiceID) {
    window.open('<?= $base ?>php/scripts/projects/view_invoice.php?invoiceID=' + invoiceID, '_blank');
}

function downloadInvoicePDF(invoiceID) {
    window.location.href = '<?= $base ?>php/scripts/projects/generate_invoice_pdf.php?invoiceID=' + invoiceID;
}

function recordPayment(invoiceID) {
    document.getElementById('payment_invoiceID').value = invoiceID;
    const modal = new bootstrap.Modal(document.getElementById('recordPaymentModal'));
    modal.show();
}

function addExpense() {
    const modal = new bootstrap.Modal(document.getElementById('addExpenseModal'));
    modal.show();
}

function viewExpense(expenseID) {
    // Open expense details modal or page
    alert('View expense ID: ' + expenseID);
}

function approveExpense(expenseID) {
    if (confirm('Approve this expense?')) {
        const formData = new FormData();
        formData.append('expenseID', expenseID);
        formData.append('action', 'approve');
        formData.append('csrf_token', '<?= SecurityMiddleware::generateCSRFToken() ?>');

        fetch('<?= $base ?>php/scripts/projects/manage_expense.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}
</script>

<style>
.avatar-md {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
}

.avatar-lg {
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
}
</style>

