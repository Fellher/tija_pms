<?php
/**
 * Projects Financial Analysis - Comprehensive Financial Dashboard
 * Advanced financial analysis for active projects with emphasis on profitability,
 * cost management, revenue tracking, and performance metrics
 * @package    Tija CRM
 * @subpackage Projects Financial Analysis
 * @version    2.0 - Enhanced Financial Analysis
 * @created    2024-12-15
 */

// Get filter parameters
$orgDataID = isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : $orgDataID;
$entityID = isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : $entityID;
$clientID = isset($_GET['clientID']) ? Utility::clean_string($_GET['clientID']) : '';
$businessUnitID = isset($_GET['businessUnitID']) ? Utility::clean_string($_GET['businessUnitID']) : '';
$projectStatus = isset($_GET['projectStatus']) ? Utility::clean_string($_GET['projectStatus']) : 'Active';
$dateFrom = isset($_GET['dateFrom']) ? Utility::clean_string($_GET['dateFrom']) : date('Y-01-01');
$dateTo = isset($_GET['dateTo']) ? Utility::clean_string($_GET['dateTo']) : date('Y-m-d');

// Build filter conditions
$projectFilters = array(
    'orgDataID' => $orgDataID,
    'entityID' => $entityID,
    'Suspended' => 'N'
);

if (!empty($clientID)) {
    $projectFilters['clientID'] = $clientID;
}
if (!empty($businessUnitID)) {
    $projectFilters['businessUnitID'] = $businessUnitID;
}
if (!empty($projectStatus)) {
    $projectFilters['projectStatus'] = $projectStatus;
}

// Get comprehensive project data with financial information
$projects = Projects::projects_full($projectFilters, false, $DBConn);
$projectBillings = Projects::project_billings($projectFilters, false, $DBConn);

// Initialize comprehensive financial metrics
$financialAnalysis = [
    'overview' => [
        'total_projects' => 0,
        'active_projects' => 0,
        'completed_projects' => 0,
        'total_project_value' => 0,
        'total_billable_value' => 0,
        'total_billed_amount' => 0,
        'total_paid_amount' => 0,
        'total_outstanding' => 0,
        'total_overdue' => 0,
        'total_expenses' => 0,
        'total_time_cost' => 0,
        'net_profit' => 0,
        'profit_margin' => 0,
        'roi_percentage' => 0,
        'average_project_value' => 0,
        'average_project_duration' => 0
    ],
    'projects' => [],
    'expense_analysis' => [],
    'billing_analysis' => [],
    'profitability_analysis' => [],
    'performance_metrics' => [],
    'trends' => [],
    'alerts' => []
];

// Process projects data
if ($projects && is_array($projects)) {
    foreach ($projects as $project) {
        $projectID = $project->projectID;
        
        // Get project billing data
        $projectBilling = null;
        if ($projectBillings && is_array($projectBillings)) {
            foreach ($projectBillings as $billing) {
                if ($billing->projectID == $projectID) {
                    $projectBilling = $billing;
                    break;
                }
            }
        }
        
        // Get project expenses
        $projectExpenses = Expense::get_expenses(array(
            'projectID' => $projectID,
            'orgDataID' => $orgDataID,
            'entityID' => $entityID,
            'Suspended' => 'N'
        ), false, $DBConn);
        
        // Calculate project metrics
        $projectValue = $project->projectValue ?? 0;
        $billableValue = $project->billableRateValue ?? 0;
        $billedAmount = $projectBilling ? ($projectBilling->total_billed ?? 0) : 0;
        $paidAmount = $projectBilling ? ($projectBilling->paid_amount ?? 0) : 0;
        $outstandingAmount = $projectBilling ? ($projectBilling->outstanding_amount ?? 0) : 0;
        $overdueAmount = $projectBilling ? ($projectBilling->overdue_amount ?? 0) : 0;
        $totalHours = $projectBilling ? ($projectBilling->total_hours_logged ?? 0) : 0;
        $timeValue = $projectBilling ? ($projectBilling->total_time_value ?? 0) : 0;
        
        // Calculate expenses
        $totalExpenses = 0;
        $expenseBreakdown = [];
        if ($projectExpenses && is_array($projectExpenses)) {
            foreach ($projectExpenses as $expense) {
                $totalExpenses += $expense->amount ?? 0;
                $category = $expense->expenseCategoryName ?? 'Unknown';
                if (!isset($expenseBreakdown[$category])) {
                    $expenseBreakdown[$category] = 0;
                }
                $expenseBreakdown[$category] += $expense->amount ?? 0;
            }
        }
        
        // Calculate profitability
        $totalCosts = $totalExpenses + $timeValue;
        $grossProfit = $billedAmount - $totalCosts;
        $profitMargin = $billedAmount > 0 ? (($grossProfit / $billedAmount) * 100) : 0;
        $roi = $projectValue > 0 ? (($grossProfit / $projectValue) * 100) : 0;
        
        // Project duration calculation
        $startDate = $project->projectStart ?? null;
        $endDate = $project->projectClose ?? date('Y-m-d');
        $duration = 0;
        if ($startDate) {
            $start = new DateTime($startDate);
            $end = new DateTime($endDate);
            $duration = $start->diff($end)->days;
        }
        
        // Update overview metrics
        $financialAnalysis['overview']['total_projects']++;
        $financialAnalysis['overview']['total_project_value'] += $projectValue;
        $financialAnalysis['overview']['total_billable_value'] += $billableValue;
        $financialAnalysis['overview']['total_billed_amount'] += $billedAmount;
        $financialAnalysis['overview']['total_paid_amount'] += $paidAmount;
        $financialAnalysis['overview']['total_outstanding'] += $outstandingAmount;
        $financialAnalysis['overview']['total_overdue'] += $overdueAmount;
        $financialAnalysis['overview']['total_expenses'] += $totalExpenses;
        $financialAnalysis['overview']['total_time_cost'] += $timeValue;
        $financialAnalysis['overview']['average_project_duration'] += $duration;
        
        // Count by status
        $status = $project->projectStatus ?? 'Unknown';
        if ($status === 'Active' || $status === 'In Progress') {
            $financialAnalysis['overview']['active_projects']++;
        } elseif ($status === 'Completed' || $status === 'Closed') {
            $financialAnalysis['overview']['completed_projects']++;
        }
        
        // Store detailed project analysis
        $projectAnalysis = [
            'project' => $project,
            'billing' => $projectBilling,
            'expenses' => $projectExpenses,
            'metrics' => [
                'project_value' => $projectValue,
                'billable_value' => $billableValue,
                'billed_amount' => $billedAmount,
                'paid_amount' => $paidAmount,
                'outstanding_amount' => $outstandingAmount,
                'overdue_amount' => $overdueAmount,
                'total_expenses' => $totalExpenses,
                'time_cost' => $timeValue,
                'total_costs' => $totalCosts,
                'gross_profit' => $grossProfit,
                'profit_margin' => $profitMargin,
                'roi' => $roi,
                'duration_days' => $duration,
                'total_hours' => $totalHours,
                'expense_breakdown' => $expenseBreakdown
            ]
        ];
        
        $financialAnalysis['projects'][] = $projectAnalysis;
        $financialAnalysis['profitability_analysis'][] = $projectAnalysis;
    }
}

// Calculate derived metrics
$totalProjects = $financialAnalysis['overview']['total_projects'];
if ($totalProjects > 0) {
    $financialAnalysis['overview']['average_project_value'] = $financialAnalysis['overview']['total_project_value'] / $totalProjects;
    $financialAnalysis['overview']['average_project_duration'] = $financialAnalysis['overview']['average_project_duration'] / $totalProjects;
}

$totalRevenue = $financialAnalysis['overview']['total_billed_amount'];
$totalCosts = $financialAnalysis['overview']['total_expenses'] + $financialAnalysis['overview']['total_time_cost'];
$financialAnalysis['overview']['net_profit'] = $totalRevenue - $totalCosts;
$financialAnalysis['overview']['profit_margin'] = $totalRevenue > 0 ? (($financialAnalysis['overview']['net_profit'] / $totalRevenue) * 100) : 0;
$financialAnalysis['overview']['roi_percentage'] = $financialAnalysis['overview']['total_project_value'] > 0 ? 
    (($financialAnalysis['overview']['net_profit'] / $financialAnalysis['overview']['total_project_value']) * 100) : 0;

// Sort profitability analysis by profit margin
usort($financialAnalysis['profitability_analysis'], function($a, $b) {
    return $b['metrics']['profit_margin'] <=> $a['metrics']['profit_margin'];
});

// Generate alerts
$alerts = [];
if ($financialAnalysis['overview']['total_overdue'] > 0) {
    $alerts[] = [
        'type' => 'warning',
        'message' => 'KES ' . number_format($financialAnalysis['overview']['total_overdue'], 0) . ' in overdue payments',
        'icon' => 'ri-alarm-warning-line'
    ];
}
if ($financialAnalysis['overview']['profit_margin'] < 10) {
    $alerts[] = [
        'type' => 'danger',
        'message' => 'Low profit margin: ' . number_format($financialAnalysis['overview']['profit_margin'], 1) . '%',
        'icon' => 'ri-error-warning-line'
    ];
}
if ($financialAnalysis['overview']['total_outstanding'] > ($financialAnalysis['overview']['total_billed_amount'] * 0.3)) {
    $alerts[] = [
        'type' => 'info',
        'message' => 'High outstanding amount: ' . number_format(($financialAnalysis['overview']['total_outstanding'] / $financialAnalysis['overview']['total_billed_amount']) * 100, 1) . '% of billed amount',
        'icon' => 'ri-money-dollar-circle-line'
    ];
}

$financialAnalysis['alerts'] = $alerts;

// Get reference data for filters
$clients = Client::clients(array('orgDataID' => $orgDataID, 'entityID' => $entityID), false, $DBConn);
$businessUnits = Data::business_units(array('orgDataID' => $orgDataID, 'entityID' => $entityID), false, $DBConn);
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="ri-line-chart-line me-2 text-primary"></i>
                        Projects Financial Analysis
                    </h2>
                    <p class="text-muted mb-0">Comprehensive financial performance analysis for active projects</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="exportFinancialReport()">
                        <i class="ri-download-line me-1"></i>Export Report
                    </button>
                    <button class="btn btn-primary" onclick="refreshAnalysis()">
                        <i class="ri-refresh-line me-1"></i>Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Alerts -->
    <?php if (!empty($financialAnalysis['alerts'])): ?>
    <div class="row mb-4">
        <div class="col-12">
            <?php foreach ($financialAnalysis['alerts'] as $alert): ?>
            <div class="alert alert-<?= $alert['type'] ?> alert-dismissible fade show" role="alert">
                <i class="<?= $alert['icon'] ?> me-2"></i>
                <?= $alert['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="ri-filter-line me-2"></i>Analysis Filters
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="<?= "{$base}html/{$getString}&subMenu={$subMenuPage}" ?>" class="row g-3">
                        <input type="hidden" name="orgDataID" value="<?= $orgDataID ?>">
                        <input type="hidden" name="entityID" value="<?= $entityID ?>">                        
                        <div class="col-md-3">
                            <label class="form-label">Client</label>
                            <select name="clientID" class="form-select">
                                <option value="">All Clients</option>
                                <?php if ($clients && is_array($clients)): ?>
                                    <?php foreach ($clients as $client): ?>
                                        <option value="<?= $client->clientID ?>" <?= $clientID == $client->clientID ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($client->clientName) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Business Unit</label>
                            <select name="businessUnitID" class="form-select">
                                <option value="">All Business Units</option>
                                <?php if ($businessUnits && is_array($businessUnits)): ?>
                                    <?php foreach ($businessUnits as $unit): ?>
                                        <option value="<?= $unit->businessUnitID ?>" <?= $businessUnitID == $unit->businessUnitID ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($unit->businessUnitName) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="projectStatus" class="form-select">
                                <option value="">All Statuses</option>
                                <option value="Active" <?= $projectStatus == 'Active' ? 'selected' : '' ?>>Active</option>
                                <option value="In Progress" <?= $projectStatus == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="Completed" <?= $projectStatus == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                <option value="On Hold" <?= $projectStatus == 'On Hold' ? 'selected' : '' ?>>On Hold</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">From Date</label>
                            <input type="date" name="dateFrom" class="form-control" value="<?= $dateFrom ?>">
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">To Date</label>
                            <input type="date" name="dateTo" class="form-control" value="<?= $dateTo ?>">
                        </div>
                        
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-search-line me-1"></i>Apply Filters
                            </button>
                            <a href="?" class="btn btn-outline-secondary ms-2">
                                <i class="ri-refresh-line me-1"></i>Clear Filters
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-white shadow">
                <div class="card-header bg-primary-subtle">
                    <h6 class="mb-0 text-primary">
                        <i class="ri-folder-line me-2"></i>Total Projects
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-primary-subtle rounded">
                                <span class="avatar-title bg-primary-subtle text-primary rounded">
                                    <i class="ri-folder-line fs-20"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0"><?= $financialAnalysis['overview']['total_projects'] ?></h4>
                            <small class="text-muted">
                                Active: <?= $financialAnalysis['overview']['active_projects'] ?> | 
                                Completed: <?= $financialAnalysis['overview']['completed_projects'] ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-white shadow">
                <div class="card-header bg-success-subtle">
                    <h6 class="mb-0 text-success">
                        <i class="ri-money-dollar-circle-line me-2"></i>Total Revenue
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-success-subtle rounded">
                                <span class="avatar-title bg-success-subtle text-success rounded">
                                    <i class="ri-money-dollar-circle-line fs-20"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0">KES <?= number_format($financialAnalysis['overview']['total_billed_amount'], 0) ?></h4>
                            <small class="text-muted">
                                Paid: KES <?= number_format($financialAnalysis['overview']['total_paid_amount'], 0) ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-white shadow">
                <div class="card-header bg-warning-subtle">
                    <h6 class="mb-0 text-warning">
                        <i class="ri-bill-line me-2"></i>Total Costs
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-warning-subtle rounded">
                                <span class="avatar-title bg-warning-subtle text-warning rounded">
                                    <i class="ri-bill-line fs-20"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0">KES <?= number_format($totalCosts, 0) ?></h4>
                            <small class="text-muted">
                                Expenses: KES <?= number_format($financialAnalysis['overview']['total_expenses'], 0) ?> | 
                                Time: KES <?= number_format($financialAnalysis['overview']['total_time_cost'], 0) ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-white shadow">
                <div class="card-header bg-info-subtle">
                    <h6 class="mb-0 text-info">
                        <i class="ri-trending-up-line me-2"></i>Net Profit
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-info-subtle rounded">
                                <span class="avatar-title bg-info-subtle text-info rounded">
                                    <i class="ri-trending-up-line fs-20"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0 <?= $financialAnalysis['overview']['net_profit'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                KES <?= number_format($financialAnalysis['overview']['net_profit'], 0) ?>
                            </h4>
                            <small class="text-muted">
                                Margin: <?= number_format($financialAnalysis['overview']['profit_margin'], 1) ?>% | 
                                ROI: <?= number_format($financialAnalysis['overview']['roi_percentage'], 1) ?>%
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Outstanding Payments Alert -->
    <?php if ($financialAnalysis['overview']['total_outstanding'] > 0): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning-subtle">
                    <h6 class="mb-0 text-warning">
                        <i class="ri-alarm-warning-line me-2"></i>Outstanding Payments
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <h5 class="text-warning">KES <?= number_format($financialAnalysis['overview']['total_outstanding'], 0) ?></h5>
                                <small class="text-muted">Total Outstanding</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h5 class="text-danger">KES <?= number_format($financialAnalysis['overview']['total_overdue'], 0) ?></h5>
                                <small class="text-muted">Overdue Amount</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h5 class="text-info"><?= $totalRevenue > 0 ? number_format(($financialAnalysis['overview']['total_outstanding'] / $totalRevenue) * 100, 1) : 0 ?>%</h5>
                                <small class="text-muted">% of Total Revenue</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <!-- Detailed Project Analysis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-bar-chart-line me-2"></i>Project Financial Performance
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="projectsFinancialTable">
                            <thead>
                                <tr>
                                    <th>Project</th>
                                    <th>Client</th>
                                    <th>Status</th>
                                    <th>Project Value</th>
                                    <th>Billed</th>
                                    <th>Paid</th>
                                    <th>Outstanding</th>
                                    <th>Expenses</th>
                                    <th>Time Cost</th>
                                    <th>Net Profit</th>
                                    <th>Margin %</th>
                                    <th>ROI %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($financialAnalysis['projects'] as $projectData): 
                                    $project = $projectData['project'];
                                    $metrics = $projectData['metrics'];
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-light rounded me-2">
                                                <span class="avatar-title bg-light text-dark rounded">
                                                    <i class="ri-folder-line"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($project->projectName) ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars($project->projectCode) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($project->clientName) ?></td>
                                    <td>
                                        <?php 
                                        $statusClass = $project->projectStatus === 'Active' ? 'bg-success-subtle text-success' : 
                                                     ($project->projectStatus === 'Completed' ? 'bg-info-subtle text-info' : 'bg-secondary-subtle text-secondary');
                                        ?>
                                        <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($project->projectStatus) ?></span>
                                    </td>
                                    <td>KES <?= number_format($metrics['project_value'], 0) ?></td>
                                    <td>KES <?= number_format($metrics['billed_amount'], 0) ?></td>
                                    <td>KES <?= number_format($metrics['paid_amount'], 0) ?></td>
                                    <td>
                                        <span class="<?= $metrics['outstanding_amount'] > 0 ? 'text-warning' : 'text-success' ?>">
                                            KES <?= number_format($metrics['outstanding_amount'], 0) ?>
                                        </span>
                                    </td>
                                    <td>KES <?= number_format($metrics['total_expenses'], 0) ?></td>
                                    <td>KES <?= number_format($metrics['time_cost'], 0) ?></td>
                                    <td>
                                        <span class="<?= $metrics['gross_profit'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                            KES <?= number_format($metrics['gross_profit'], 0) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $marginClass = $metrics['profit_margin'] >= 20 ? 'bg-success-subtle text-success' : 
                                                      ($metrics['profit_margin'] >= 10 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger');
                                        ?>
                                        <span class="badge <?= $marginClass ?>"><?= number_format($metrics['profit_margin'], 1) ?>%</span>
                                    </td>
                                    <td>
                                        <?php 
                                        $roiClass = $metrics['roi'] >= 20 ? 'bg-success-subtle text-success' : 
                                                   ($metrics['roi'] >= 10 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger');
                                        ?>
                                        <span class="badge <?= $roiClass ?>"><?= number_format($metrics['roi'], 1) ?>%</span>
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

    <!-- Charts Section -->
    <div class="row mb-4">
        <div class="col-xl-6 col-md-12 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-pie-chart-line me-2"></i>Revenue vs Costs Analysis
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueCostsChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-md-12 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-bar-chart-line me-2"></i>Project Profitability
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="profitabilityChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Expense Breakdown -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-pie-chart-line me-2"></i>Expense Breakdown by Category
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="expenseBreakdownChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#projectsFinancialTable').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[9, 'desc']], // Sort by Net Profit descending
            columnDefs: [
                { targets: [3, 4, 5, 6, 7, 8, 9], className: 'text-end' },
                { targets: [10, 11], className: 'text-center' }
            ]
        });
    }

    // Revenue vs Costs Chart
    const revenueCtx = document.getElementById('revenueCostsChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'doughnut',
            data: {
                labels: ['Revenue', 'Expenses', 'Time Costs'],
                datasets: [{
                    data: [
                        <?= $financialAnalysis['overview']['total_billed_amount'] ?>,
                        <?= $financialAnalysis['overview']['total_expenses'] ?>,
                        <?= $financialAnalysis['overview']['total_time_cost'] ?>
                    ],
                    backgroundColor: [
                        '#28a745', // Revenue - Green
                        '#ffc107', // Expenses - Warning
                        '#17a2b8'  // Time Costs - Info
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
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': KES ' + context.parsed.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Profitability Chart
    const profitCtx = document.getElementById('profitabilityChart');
    if (profitCtx) {
        const projectNames = <?= json_encode(array_slice(array_map(function($p) { return $p['project']->projectName; }, $financialAnalysis['projects']), 0, 10)) ?>;
        const profitMargins = <?= json_encode(array_slice(array_map(function($p) { return $p['metrics']['profit_margin']; }, $financialAnalysis['projects']), 0, 10)) ?>;
        
        new Chart(profitCtx, {
            type: 'bar',
            data: {
                labels: projectNames,
                datasets: [{
                    label: 'Profit Margin %',
                    data: profitMargins,
                    backgroundColor: profitMargins.map(margin => 
                        margin >= 20 ? '#28a745' : (margin >= 10 ? '#ffc107' : '#dc3545')
                    ),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Profit Margin: ' + context.parsed.y.toFixed(1) + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    // Expense Breakdown Chart
    const expenseCtx = document.getElementById('expenseBreakdownChart');
    if (expenseCtx) {
        // Collect all expense categories and their totals
        const expenseCategories = {};
        <?php foreach ($financialAnalysis['projects'] as $projectData): ?>
            <?php foreach ($projectData['metrics']['expense_breakdown'] as $category => $amount): ?>
                if (!expenseCategories['<?= addslashes($category) ?>']) {
                    expenseCategories['<?= addslashes($category) ?>'] = 0;
                }
                expenseCategories['<?= addslashes($category) ?>'] += <?= $amount ?>;
            <?php endforeach; ?>
        <?php endforeach; ?>

        const categoryLabels = Object.keys(expenseCategories);
        const categoryValues = Object.values(expenseCategories);
        
        new Chart(expenseCtx, {
            type: 'pie',
            data: {
                labels: categoryLabels,
                datasets: [{
                    data: categoryValues,
                    backgroundColor: [
                        '#28a745', '#ffc107', '#17a2b8', '#dc3545', '#6c757d',
                        '#20c997', '#fd7e14', '#6f42c1', '#e83e8c', '#343a40'
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
                        position: 'right',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';
                                return context.label + ': KES ' + value.toLocaleString() + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }
});

// Export function
function exportFinancialReport() {
    // Implementation for exporting financial report
    alert('Export functionality will be implemented here');
}

// Refresh function
function refreshAnalysis() {
    window.location.reload();
}
</script>
