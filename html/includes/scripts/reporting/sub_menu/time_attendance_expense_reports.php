<?php
/**
 * Expense Analysis Reports
 * Comprehensive expense tracking and analysis by employees and segments
 * Enhanced with new unified Expense class methods
 * @package    Tija CRM
 * @subpackage Expense Reports
 * @version    2.0
 */

// Unified expense system - single source of truth
$dataSource = 'Unified';
$useEnhancedTable = true; // Always true for unified system

// Get expense data (same for both tables)
$expenseCategories = Expense::expense_categories(array('orgDataID' => $orgDataID, 'entityID' => $entityID, 'isActive' => 'Y'), false, $DBConn);

// var_dump($expenseCategories);
$expenseStatuses = Expense::expense_status(array('orgDataID' => $orgDataID, 'entityID' => $entityID, 'isActive' => 'Y'), false, $DBConn);
// var_dump($expenseStatuses);
$expenseTypes = Expense::expense_types(array('orgDataID' => $orgDataID, 'entityID' => $entityID, 'isActive' => 'Y'), false, $DBConn);
// var_dump($expenseTypes);
// Build filter array from URL parameters
$filterParams = array('orgDataID' => $orgDataID, 'entityID' => $entityID, 'Suspended' => 'N');

// Add URL parameters to filter
if (isset($_GET['employeeID']) && !empty($_GET['employeeID'])) {
    $filterParams['employeeID'] = $_GET['employeeID'];
}
if (isset($_GET['expenseCategoryID']) && !empty($_GET['expenseCategoryID'])) {
    $filterParams['expenseCategoryID'] = $_GET['expenseCategoryID'];
}
if (isset($_GET['expenseStatusID']) && !empty($_GET['expenseStatusID'])) {
    $filterParams['expenseStatusID'] = $_GET['expenseStatusID'];
}
if (isset($_GET['expenseTypeID']) && !empty($_GET['expenseTypeID'])) {
    $filterParams['expenseTypeID'] = $_GET['expenseTypeID'];
}
if (isset($_GET['dateFrom']) && !empty($_GET['dateFrom'])) {
    $filterParams['dateFrom'] = $_GET['dateFrom'];
}
if (isset($_GET['dateTo']) && !empty($_GET['dateTo'])) {
    $filterParams['dateTo'] = $_GET['dateTo'];
}

// Unified system filters (all enhanced features available)
if (isset($_GET['vendor']) && !empty($_GET['vendor'])) {
    $filterParams['vendor'] = $_GET['vendor'];
}
if (isset($_GET['location']) && !empty($_GET['location'])) {
    $filterParams['location'] = $_GET['location'];
}
if (isset($_GET['amountFrom']) && !empty($_GET['amountFrom'])) {
    $filterParams['amountFrom'] = $_GET['amountFrom'];
}
if (isset($_GET['amountTo']) && !empty($_GET['amountTo'])) {
    $filterParams['amountTo'] = $_GET['amountTo'];
}
if (isset($_GET['isBillable']) && !empty($_GET['isBillable'])) {
    $filterParams['isBillable'] = $_GET['isBillable'];
}
if (isset($_GET['isUrgent']) && !empty($_GET['isUrgent'])) {
    $filterParams['isUrgent'] = $_GET['isUrgent'];
}

// Get expense summaries using unified system
$employeeExpenseSummary = Expense::employee_expense_summary($filterParams, $DBConn);
$overdueExpenses = Expense::overdue_expenses($filterParams, $DBConn);
    
// Check if we have a text search
if (isset($_GET['searchText']) && !empty($_GET['searchText'])) {
    $recentExpenses = Expense::search_expenses($_GET['searchText'], $filterParams, $DBConn);
} else {
    $recentExpenses = Expense::get_expenses($filterParams, false, $DBConn);
}

// Get additional summaries using unified system
$categoryExpenseSummary = Expense::category_expense_summary($filterParams, $DBConn);

// Add default date range for monthly trends if not specified
$monthlyFilterParams = $filterParams;
if (!isset($monthlyFilterParams['dateFrom'])) {
    $monthlyFilterParams['dateFrom'] = date('Y-01-01');
}
$monthlyTrends = Expense::monthly_expense_trends($monthlyFilterParams, $DBConn);

// Debug: Check if data is being retrieved
// echo "<!-- Debug: Monthly Trends Count: " . (is_array($monthlyTrends) ? count($monthlyTrends) : 'Not array') . " -->";
// echo "<!-- Debug: Category Summary Count: " . (is_array($categoryExpenseSummary) ? count($categoryExpenseSummary) : 'Not array') . " -->";

// Calculate totals
$totalExpenses = 0;
$totalAmount = 0;
$totalApproved = 0;
$totalPaid = 0;
$totalOverdue = 0;

if ($employeeExpenseSummary && is_array($employeeExpenseSummary)) {
    foreach ($employeeExpenseSummary as $summary) {
        $totalExpenses += $summary->total_expenses;
        $totalAmount += $summary->total_amount;
        $totalApproved += $summary->approved_amount;
        $totalPaid += $summary->paid_amount;
        $totalOverdue += $summary->overdue_amount;
    }
}

// Get employees for filter
$employees = Employee::employees(array('orgDataID' => $orgDataID, 'entityID' => $entityID, 'Suspended' => 'N'), false, $DBConn);
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">Expense Analysis Reports</h4>
                            <p class="text-muted mb-0">Comprehensive expense tracking and analysis by employees and segments</p>
                            <small class="badge bg-info">Data Source: <?= $dataSource ?> Table</small>
                        </div>
                        <div class="text-end">
                            <div class="btn-group me-2" role="group">
                                <a href="<?= "{$base}html/{$getString}&subMenu={$subMenuPage}&enhanced=0"?>" class="btn <?= !$useEnhancedTable ? 'btn-primary' : 'btn-outline-primary' ?>">
                                    <i class="ri-database-line me-2"></i>Legacy Data
                                </a>
                                <a href="<?= "{$base}html/{$getString}&subMenu={$subMenuPage}&enhanced=1"?>" class="btn <?= $useEnhancedTable ? 'btn-primary' : 'btn-outline-primary' ?>">
                                    <i class="ri-database-2-line me-2"></i>Enhanced Data
                                </a>
                            </div>
                            <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#expenseFiltersModal">
                                <i class="ri-filter-line me-2"></i>Filter Reports
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Overview -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <div class="avatar-sm bg-primary-subtle rounded mx-auto mb-3">
                        <span class="avatar-title bg-primary text-white rounded">
                            <i class="ri-file-list-3-line"></i>
                        </span>
                    </div>
                    <h5 class="mb-1"><?= number_format($totalExpenses) ?></h5>
                    <p class="text-muted mb-0">Total Expenses</p>
                    <small class="text-primary">All Time</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <div class="avatar-sm bg-success-subtle rounded mx-auto mb-3">
                        <span class="avatar-title bg-success text-white rounded">
                            <i class="ri-money-dollar-circle-line"></i>
                        </span>
                    </div>
                    <h5 class="mb-1">KES <?= number_format($totalAmount, 2) ?></h5>
                    <p class="text-muted mb-0">Total Amount</p>
                    <small class="text-success">KES <?= number_format($totalApproved, 2) ?> Approved</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <div class="avatar-sm bg-info-subtle rounded mx-auto mb-3">
                        <span class="avatar-title bg-info text-white rounded">
                            <i class="ri-check-line"></i>
                        </span>
                    </div>
                    <h5 class="mb-1">KES <?= number_format($totalPaid, 2) ?></h5>
                    <p class="text-muted mb-0">Total Paid</p>
                    <small class="text-info"><?= $totalAmount > 0 ? number_format(($totalPaid / $totalAmount) * 100, 1) : 0 ?>% Paid</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <div class="avatar-sm bg-warning-subtle rounded mx-auto mb-3">
                        <span class="avatar-title bg-warning text-white rounded">
                            <i class="ri-alert-line"></i>
                        </span>
                    </div>
                    <h5 class="mb-1">KES <?= number_format($totalOverdue, 2) ?></h5>
                    <p class="text-muted mb-0">Overdue Amount</p>
                    <small class="text-warning"><?= $overdueExpenses ? count($overdueExpenses) : 0 ?> Overdue</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-bar-chart-line me-2"></i>Monthly Expense Trends</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyExpenseChart" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-pie-chart-line me-2"></i>Expenses by Category</h5>
                </div>
                <div class="card-body">
                    <canvas id="categoryExpenseChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Expense Analysis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-team-line me-2"></i>Employee Expense Analysis</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Total Expenses</th>
                                    <th>Total Amount</th>
                                    <th>Approved</th>
                                    <th>Paid</th>
                                    <th>Overdue</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($employeeExpenseSummary && is_array($employeeExpenseSummary)): ?>
                                    <?php foreach ($employeeExpenseSummary as $summary): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-light rounded me-2">
                                                        <span class="avatar-title bg-light text-dark rounded">
                                                            <i class="ri-user-line"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0"><?= htmlspecialchars($summary->employeeName) ?></h6>
                                                        <small class="text-muted"><?= htmlspecialchars($summary->employeeCode) ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center"><?= $summary->total_expenses ?></td>
                                            <td class="text-end">KES <?= number_format($summary->total_amount, 2) ?></td>
                                            <td class="text-end">KES <?= number_format($summary->approved_amount, 2) ?></td>
                                            <td class="text-end">KES <?= number_format($summary->paid_amount, 2) ?></td>
                                            <td class="text-end">
                                                <?php if ($summary->overdue_amount > 0): ?>
                                                    <span class="text-danger">KES <?= number_format($summary->overdue_amount, 2) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">KES 0.00</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $approvalRate = $summary->total_amount > 0 ? ($summary->approved_amount / $summary->total_amount) * 100 : 0;
                                                $paymentRate = $summary->total_amount > 0 ? ($summary->paid_amount / $summary->total_amount) * 100 : 0;
                                                ?>
                                                <div class="d-flex flex-column">
                                                    <small class="text-success"><?= number_format($approvalRate, 1) ?>% Approved</small>
                                                    <small class="text-info"><?= number_format($paymentRate, 1) ?>% Paid</small>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No expense data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Analysis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-folder-line me-2"></i>Expense Category Analysis</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Total Expenses</th>
                                    <th>Total Amount</th>
                                    <th>Average Amount</th>
                                    <th>Approved</th>
                                    <th>Paid</th>
                                    <th>Utilization</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($categoryExpenseSummary && is_array($categoryExpenseSummary)): ?>
                                    <?php foreach ($categoryExpenseSummary as $summary): ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <h6 class="mb-0"><?= htmlspecialchars($summary->categoryName) ?></h6>
                                                    <small class="text-muted"><?= htmlspecialchars($summary->categoryCode) ?></small>
                                                </div>
                                            </td>
                                            <td class="text-center"><?= $summary->total_expenses ?></td>
                                            <td class="text-end">KES <?= number_format($summary->total_amount, 2) ?></td>
                                            <td class="text-end">KES <?= number_format($summary->average_amount, 2) ?></td>
                                            <td class="text-end">KES <?= number_format($summary->approved_amount, 2) ?></td>
                                            <td class="text-end">KES <?= number_format($summary->paid_amount, 2) ?></td>
                                            <td>
                                                <?php 
                                                $utilization = $summary->total_amount > 0 ? ($summary->paid_amount / $summary->total_amount) * 100 : 0;
                                                $utilizationClass = $utilization >= 80 ? 'text-success' : ($utilization >= 60 ? 'text-warning' : 'text-danger');
                                                ?>
                                                <span class="<?= $utilizationClass ?>">
                                                    <?= number_format($utilization, 1) ?>%
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No category data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue Expenses -->
    <?php if ($overdueExpenses && is_array($overdueExpenses)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning-subtle">
                    <h5 class="card-title mb-0"><i class="ri-alert-line me-2"></i>Overdue Expenses</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Expense #</th>
                                    <th>Employee</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>Submitted</th>
                                    <th>Days Overdue</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($overdueExpenses as $expense): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($expense->expenseNumber) ?></td>
                                        <td><?= htmlspecialchars($expense->employeeName) ?></td>
                                        <td><?= htmlspecialchars($expense->expenseCategoryName) ?></td>
                                        <td class="text-end">KES <?= number_format($expense->amount, 2) ?></td>
                                        <td><?= date('M d, Y', strtotime($expense->submissionDate)) ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-danger"><?= $expense->days_overdue ?> days</span>
                                        </td>
                                        <td>
                                            <span class="badge" style="background-color: <?= $expense->expenseStatusColor ?? '#6c757d' ?>">
                                                <?= htmlspecialchars($expense->expenseStatusName) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Expenses -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="ri-time-line me-2"></i>Recent Expenses</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Expense #</th>
                                    <th>Employee</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                    <?php if ($useEnhancedTable): ?>
                                    <th>Vendor</th>
                                    <th>Location</th>
                                    <?php endif; ?>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recentExpenses && is_array($recentExpenses)): ?>
                                    <?php foreach (array_slice($recentExpenses, 0, 10) as $expense): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($expense->expenseNumber) ?></td>
                                            <td><?= htmlspecialchars($expense->employeeName) ?></td>
                                            <td><?= htmlspecialchars($expense->expenseTypeName) ?></td>
                                            <td><?= htmlspecialchars($expense->expenseCategoryName) ?></td>
                                            <?php if ($useEnhancedTable): ?>
                                            <td><?= htmlspecialchars($expense->vendor ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($expense->location ?? 'N/A') ?></td>
                                            <?php endif; ?>
                                            <td class="text-end">KES <?= number_format($expense->amount, 2) ?></td>
                                            <td><?= date('M d, Y', strtotime($expense->expenseDate)) ?></td>
                                            <td>
                                                <span class="badge" style="background-color: <?= $expense->expenseStatusColor ?? '#6c757d' ?>">
                                                    <?= htmlspecialchars($expense->expenseStatusName) ?>
                                                </span>
                                                <?php if ($useEnhancedTable && isset($expense->isUrgent) && $expense->isUrgent == 'Y'): ?>
                                                <br><small class="badge bg-danger">Urgent</small>
                                                <?php endif; ?>
                                                <?php if ($useEnhancedTable && isset($expense->isBillable) && $expense->isBillable == 'Y'): ?>
                                                <br><small class="badge bg-success">Billable</small>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="<?= $useEnhancedTable ? '9' : '7' ?>" class="text-center text-muted">No recent expenses</td>
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

<!-- Filter Modal -->
<div class="modal fade" id="expenseFiltersModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter Expense Reports</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="expenseFiltersForm">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="alert alert-info">
                                <i class="ri-information-line me-2"></i>
                                <strong>Data Source:</strong> <?= $dataSource ?> Table 
                                <?php if ($useEnhancedTable): ?>
                                    <br><small>Enhanced table includes additional fields like vendor, location, and advanced tracking.</small>
                                <?php else: ?>
                                    <br><small>Legacy table provides basic expense tracking functionality.</small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Employee</label>
                            <select class="form-select" name="employeeID">
                                <option value="">All Employees</option>
                                <?php if ($employees && is_array($employees)): ?>
                                    <?php foreach ($employees as $employee): ?>
                                        <option value="<?= $employee->ID ?>"><?= htmlspecialchars($employee->employeeName) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="categoryID">
                                <option value="">All Categories</option>
                                <?php if ($expenseCategories && is_array($expenseCategories)): ?>
                                    <?php foreach ($expenseCategories as $category): ?>
                                        <option value="<?= $category->expenseCategoryID ?>"><?= htmlspecialchars($category->categoryName) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="statusID">
                                <option value="">All Statuses</option>
                                <?php if ($expenseStatuses && is_array($expenseStatuses)): ?>
                                    <?php foreach ($expenseStatuses as $status): ?>
                                        <option value="<?= $status->expenseStatusID ?>"><?= htmlspecialchars($status->statusName) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="typeID">
                                <option value="">All Types</option>
                                <?php if ($expenseTypes && is_array($expenseTypes)): ?>
                                    <?php foreach ($expenseTypes as $type): ?>
                                        <option value="<?= $type->expenseTypeID ?>"><?= htmlspecialchars($type->typeName) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date From</label>
                            <input type="date" class="form-control" name="dateFrom">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date To</label>
                            <input type="date" class="form-control" name="dateTo">
                        </div>
                        <?php if ($useEnhancedTable): ?>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Vendor</label>
                            <input type="text" class="form-control" name="vendor" placeholder="Search by vendor name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" name="location" placeholder="Search by location">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Amount From</label>
                            <input type="number" class="form-control" name="amountFrom" placeholder="Minimum amount">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Amount To</label>
                            <input type="number" class="form-control" name="amountTo" placeholder="Maximum amount">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Text Search</label>
                            <input type="text" class="form-control" name="searchText" placeholder="Search in description, vendor, location">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Billable</label>
                            <select class="form-select" name="isBillable">
                                <option value="">All</option>
                                <option value="Y">Billable Only</option>
                                <option value="N">Non-Billable Only</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Urgent</label>
                            <select class="form-select" name="isUrgent">
                                <option value="">All</option>
                                <option value="Y">Urgent Only</option>
                                <option value="N">Non-Urgent Only</option>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <?php if ($useEnhancedTable): ?>
                <button type="button" class="btn btn-info" onclick="performEnhancedSearch()">
                    <i class="ri-search-line me-2"></i>Enhanced Search
                </button>
                <?php endif; ?>
                <button type="button" class="btn btn-primary" onclick="applyExpenseFilters()">Apply Filters</button>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Expense Trends Chart
    const monthlyCtx = document.getElementById('monthlyExpenseChart');
    if (monthlyCtx) {
        const monthlyData = <?= json_encode($monthlyTrends) ?>;
        
        // Debug: Check if data exists
        console.log('Monthly Trends Data:', monthlyData);
        
        if (!monthlyData || monthlyData.length === 0) {
            monthlyCtx.parentElement.innerHTML = '<p class="text-muted text-center">No monthly trend data available</p>';
        } else {
            new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: monthlyData.map(item => item.month_name),
                datasets: [{
                    label: 'Total Amount',
                    data: monthlyData.map(item => item.total_amount),
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Approved Amount',
                    data: monthlyData.map(item => item.approved_amount),
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Paid Amount',
                    data: monthlyData.map(item => item.paid_amount),
                    borderColor: '#20c997',
                    backgroundColor: 'rgba(32, 201, 151, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Amount (KES)'
                        }
                    }
                }
            }
        });
        }
    }
    
    // Category Expense Chart
    const categoryCtx = document.getElementById('categoryExpenseChart');
    if (categoryCtx) {
        const categoryData = <?= json_encode($categoryExpenseSummary) ?>;
        
        // Debug: Check if data exists
        console.log('Category Data:', categoryData);
        
        if (!categoryData || categoryData.length === 0) {
            categoryCtx.parentElement.innerHTML = '<p class="text-muted text-center">No category data available</p>';
        } else {
            new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: categoryData.map(item => item.categoryName),
                datasets: [{
                    data: categoryData.map(item => item.total_amount),
                    backgroundColor: [
                        '#0d6efd', '#28a745', '#ffc107', '#dc3545', '#17a2b8',
                        '#6f42c1', '#fd7e14', '#20c997', '#6c757d', '#e83e8c'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        }
    }
});

function applyExpenseFilters() {
    const form = document.getElementById('expenseFiltersForm');
    const formData = new FormData(form);
    const params = new URLSearchParams();
    
    // Add all form data to URL parameters
    for (let [key, value] of formData.entries()) {
        if (value.trim() !== '') {
            params.append(key, value);
        }
    }
    
    // Preserve the enhanced parameter
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('enhanced')) {
        params.append('enhanced', urlParams.get('enhanced'));
    }
    
    // Reload page with filters
    const newUrl = window.location.pathname + '?' + params.toString();
    window.location.href = newUrl;
}

// Enhanced search functionality for enhanced table
function performEnhancedSearch() {
    const searchText = document.querySelector('input[name="searchText"]').value;
    if (searchText.trim() === '') {
        alert('Please enter search text');
        return;
    }
    
    // Use AJAX to perform search
    const formData = new FormData(document.getElementById('expenseFiltersForm'));
    const params = new URLSearchParams();
    
    for (let [key, value] of formData.entries()) {
        if (value.trim() !== '') {
            params.append(key, value);
        }
    }
    
    // Preserve enhanced parameter
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('enhanced')) {
        params.append('enhanced', urlParams.get('enhanced'));
    }
    
    // Show loading indicator
    const searchBtn = document.querySelector('button[onclick="performEnhancedSearch()"]');
    const originalText = searchBtn.innerHTML;
    searchBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-2"></i>Searching...';
    searchBtn.disabled = true;
    
    // Perform search (you can implement AJAX here)
    setTimeout(() => {
        const newUrl = window.location.pathname + '?' + params.toString();
        window.location.href = newUrl;
    }, 500);
}
</script>
