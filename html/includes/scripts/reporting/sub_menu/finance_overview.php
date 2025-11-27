<?php
/**
 * Financial Reports Overview
 * Comprehensive financial analysis and reporting dashboard
 */

// Get basic financial data for overview
$projects = Projects::projects_full([], false, $DBConn);
$employees = Employee::employees([], false, $DBConn);

// Calculate basic metrics
$totalProjects = count($projects);
$totalEmployees = count($employees);
$totalProjectValue = array_sum(array_column($projects, 'projectValue'));
$totalBillableValue = array_sum(array_column($projects, 'billableRateValue'));

// Calculate employee costs
$totalEmployeeCosts = 0;
if ($employees) {
    foreach ($employees as $employee) {
        $costPerHour = $employee->costPerHour ?? 0;
        $dailyHours = $employee->dailyHours ?? 8;
        $weekWorkDays = $employee->weekWorkDays ?? 5;
        $annualHours = $dailyHours * $weekWorkDays * 52;
        $annualCost = $costPerHour * $annualHours;
        $totalEmployeeCosts += $annualCost;
    }
}

$netProfit = $totalBillableValue - $totalEmployeeCosts;
$profitMargin = $totalBillableValue > 0 ? ($netProfit / $totalBillableValue) * 100 : 0;
?>

<div class="container-fluid">
    <!-- Financial Reports Navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-dashboard-line me-2"></i>Financial Reports Dashboard
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card bg-white shadow-sm h-100">
                                <div class="card-body text-center">
                                    <div class="avatar-sm bg-primary-subtle rounded mx-auto mb-3">
                                        <span class="avatar-title bg-primary-subtle text-primary rounded">
                                            <i class="ri-folder-line fs-20"></i>
                                        </span>
                                    </div>
                                    <h5 class="text-primary">Projects Analysis</h5>
                                    <p class="text-muted mb-3">Comprehensive analysis of project profitability, costs, and financial performance</p>
                                    <a href="<?= "{$base}html/{$getString}&subMenu=projects_financial_analysis"?>" class="btn btn-primary btn-sm">
                                        <i class="ri-eye-line me-1"></i>View Report
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card bg-white shadow-sm h-100">
                                <div class="card-body text-center">
                                    <div class="avatar-sm bg-success-subtle rounded mx-auto mb-3">
                                        <span class="avatar-title bg-success-subtle text-success rounded">
                                            <i class="ri-team-line fs-20"></i>
                                        </span>
                                    </div>
                                    <h5 class="text-success">Employee Cost-Benefit</h5>
                                    <p class="text-muted mb-3">Analysis of employee costs vs value generated for ROI optimization</p>
                                    <a href="<?= "{$base}html/{$getString}&subMenu=employee_cost_benefit_analysis"?>" class="btn btn-success btn-sm">
                                        <i class="ri-eye-line me-1"></i>View Report
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card bg-white shadow-sm h-100">
                                <div class="card-body text-center">
                                    <div class="avatar-sm bg-info-subtle rounded mx-auto mb-3">
                                        <span class="avatar-title bg-info-subtle text-info rounded">
                                            <i class="ri-heart-pulse-line fs-20"></i>
                                        </span>
                                    </div>
                                    <h5 class="text-info">Financial Health</h5>
                                    <p class="text-muted mb-3">Organizational financial health across units and segments</p>
                                    <a href="<?= "{$base}html/{$getString}&subMenu=organizational_financial_health"?>" class="btn btn-info btn-sm">
                                        <i class="ri-eye-line me-1"></i>View Report
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card bg-white shadow-sm h-100">
                                <div class="card-body text-center">
                                    <div class="avatar-sm bg-warning-subtle rounded mx-auto mb-3">
                                        <span class="avatar-title bg-warning-subtle text-warning rounded">
                                            <i class="ri-building-line fs-20"></i>
                                        </span>
                                    </div>
                                    <h5 class="text-warning">Unit Performance</h5>
                                    <p class="text-muted mb-3">Financial performance analysis across business units and client segments</p>
                                    <a href="<?= "{$base}html/{$getString}&subMenu=unit_segment_performance"?>" class="btn btn-warning btn-sm">
                                        <i class="ri-eye-line me-1"></i>View Report
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-xl-6 col-md-12 mb-3">
                            <div class="card bg-white shadow-sm h-100">
                                <div class="card-body text-center">
                                    <div class="avatar-sm bg-danger-subtle rounded mx-auto mb-3">
                                        <span class="avatar-title bg-danger-subtle text-danger rounded">
                                            <i class="ri-bill-line fs-20"></i>
                                        </span>
                                    </div>
                                    <h5 class="text-danger">Invoicing Analysis</h5>
                                    <p class="text-muted mb-3">Comprehensive analysis of invoicing patterns, trends, and financial flows</p>
                                    <a href="<?= "{$base}html/{$getString}&subMenu=invoicing_analysis_trends"?>" class="btn btn-danger btn-sm">
                                        <i class="ri-eye-line me-1"></i>View Report
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-6 col-md-12 mb-3">
                            <div class="card bg-white shadow-sm h-100">
                                <div class="card-body text-center">
                                    <div class="avatar-sm bg-secondary-subtle rounded mx-auto mb-3">
                                        <span class="avatar-title bg-secondary-subtle text-secondary rounded">
                                            <i class="ri-file-chart-line fs-20"></i>
                                        </span>
                                    </div>
                                    <h5 class="text-secondary">All Reports</h5>
                                    <p class="text-muted mb-3">Access all financial reports and analytics in one comprehensive view</p>
                                    <a href="<?= "{$base}html/{$getString}&subMenu=financial_reports_all"?>" class="btn btn-secondary btn-sm">
                                        <i class="ri-eye-line me-1"></i>View All
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Financial Overview -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-dashboard-line me-2"></i>Quick Financial Overview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card bg-white shadow-sm">
                                <div class="card-body text-center">
                                    <div class="avatar-sm bg-primary-subtle rounded mx-auto mb-3">
                                        <span class="avatar-title bg-primary-subtle text-primary rounded">
                                            <i class="ri-folder-line fs-20"></i>
                                        </span>
                                    </div>
                                    <h4 class="mb-1 text-primary"><?= $totalProjects ?></h4>
                                    <p class="text-muted mb-0">Total Projects</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card bg-white shadow-sm">
                                <div class="card-body text-center">
                                    <div class="avatar-sm bg-info-subtle rounded mx-auto mb-3">
                                        <span class="avatar-title bg-info-subtle text-info rounded">
                                            <i class="ri-team-line fs-20"></i>
                                        </span>
                                    </div>
                                    <h4 class="mb-1 text-info"><?= $totalEmployees ?></h4>
                                    <p class="text-muted mb-0">Total Employees</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card bg-white shadow-sm">
                                <div class="card-body text-center">
                                    <div class="avatar-sm bg-success-subtle rounded mx-auto mb-3">
                                        <span class="avatar-title bg-success-subtle text-success rounded">
                                            <i class="ri-money-dollar-circle-line fs-20"></i>
                                        </span>
                                    </div>
                                    <h4 class="mb-1 text-success">KES <?= number_format($totalBillableValue, 0) ?></h4>
                                    <p class="text-muted mb-0">Total Billable Value</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card bg-white shadow-sm">
                                <div class="card-body text-center">
                                    <div class="avatar-sm bg-warning-subtle rounded mx-auto mb-3">
                                        <span class="avatar-title bg-warning-subtle text-warning rounded">
                                            <i class="ri-trending-up-line fs-20"></i>
                                        </span>
                                    </div>
                                    <h4 class="mb-1 <?= $profitMargin >= 15 ? 'text-success' : ($profitMargin >= 5 ? 'text-warning' : 'text-danger') ?>">
                                        <?= number_format($profitMargin, 1) ?>%
                                    </h4>
                                    <p class="text-muted mb-0">Profit Margin</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Financial Health Indicators -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-heart-pulse-line me-2"></i>Financial Health Indicators
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="card <?= $profitMargin >= 15 ? 'border-success' : ($profitMargin >= 5 ? 'border-warning' : 'border-danger') ?>">
                                <div class="card-body text-center">
                                    <div class="avatar-sm <?= $profitMargin >= 15 ? 'bg-success-subtle' : ($profitMargin >= 5 ? 'bg-warning-subtle' : 'bg-danger-subtle') ?> rounded mx-auto mb-3">
                                        <span class="avatar-title <?= $profitMargin >= 15 ? 'bg-success-subtle text-success' : ($profitMargin >= 5 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger') ?> rounded">
                                            <i class="ri-percent-line fs-20"></i>
                                        </span>
                                    </div>
                                    <h6 class="<?= $profitMargin >= 15 ? 'text-success' : ($profitMargin >= 5 ? 'text-warning' : 'text-danger') ?>">
                                        <?= $profitMargin >= 15 ? 'Excellent' : ($profitMargin >= 5 ? 'Good' : 'Needs Improvement') ?>
                                    </h6>
                                    <p class="text-muted mb-0">Profit Margin</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="card <?= $totalBillableValue > $totalEmployeeCosts ? 'border-success' : 'border-danger' ?>">
                                <div class="card-body text-center">
                                    <div class="avatar-sm <?= $totalBillableValue > $totalEmployeeCosts ? 'bg-success-subtle' : 'bg-danger-subtle' ?> rounded mx-auto mb-3">
                                        <span class="avatar-title <?= $totalBillableValue > $totalEmployeeCosts ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?> rounded">
                                            <i class="ri-scales-line fs-20"></i>
                                        </span>
                                    </div>
                                    <h6 class="<?= $totalBillableValue > $totalEmployeeCosts ? 'text-success' : 'text-danger' ?>">
                                        <?= $totalBillableValue > $totalEmployeeCosts ? 'Profitable' : 'Loss Making' ?>
                                    </h6>
                                    <p class="text-muted mb-0">Cost Efficiency</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="card <?= $totalProjects > 0 && $totalEmployees > 0 ? 'border-success' : 'border-warning' ?>">
                                <div class="card-body text-center">
                                    <div class="avatar-sm <?= $totalProjects > 0 && $totalEmployees > 0 ? 'bg-success-subtle' : 'bg-warning-subtle' ?> rounded mx-auto mb-3">
                                        <span class="avatar-title <?= $totalProjects > 0 && $totalEmployees > 0 ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' ?> rounded">
                                            <i class="ri-focus-line fs-20"></i>
                                        </span>
                                    </div>
                                    <h6 class="<?= $totalProjects > 0 && $totalEmployees > 0 ? 'text-success' : 'text-warning' ?>">
                                        <?= $totalProjects > 0 && $totalEmployees > 0 ? 'Active' : 'Inactive' ?>
                                    </h6>
                                    <p class="text-muted mb-0">Operations Status</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
