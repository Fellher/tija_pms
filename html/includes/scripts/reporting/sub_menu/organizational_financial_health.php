<?php
/**
 * Organizational Financial Health Dashboard
 * Comprehensive analysis of organizational financial health across units and segments
 */

// Get comprehensive data
$projects = Projects::projects_full([], false, $DBConn);
$employees = Employee::employees([], false, $DBConn);
$clients = Client::clients([], false, $DBConn);

// Initialize financial health metrics
$financialHealth = [
    'overall_metrics' => [
        'total_revenue' => 0,
        'total_costs' => 0,
        'net_profit' => 0,
        'profit_margin' => 0,
        'revenue_growth_rate' => 0,
        'cost_efficiency_ratio' => 0
    ],
    'business_unit_health' => [],
    'client_segment_health' => [],
    'financial_ratios' => [],
    'trend_analysis' => [],
    'risk_indicators' => []
];

// Process projects for revenue calculation
if ($projects) {
    // var_dump($projects);
    foreach ($projects as $project) {
        $projectValue = $project->projectValue ?? 0;
        $billableValue = $project->billableRateValue ?? 0;
        
        $financialHealth['overall_metrics']['total_revenue'] += $billableValue;
        
        // Business unit analysis
        $businessUnit = $project->businessUnitName ?? 'Unknown';
        if (!isset($financialHealth['business_unit_health'][$businessUnit])) {
            $financialHealth['business_unit_health'][$businessUnit] = [
                'revenue' => 0,
                'costs' => 0,
                'profit' => 0,
                'project_count' => 0,
                'avg_project_value' => 0,
                'profit_margin' => 0,
                'project_count' => 0,
                'employee_count' => 0
            ];
        }
        $financialHealth['business_unit_health'][$businessUnit]['revenue'] += $billableValue;
        $financialHealth['business_unit_health'][$businessUnit]['project_count']++;
        
        // Client segment analysis
        $clientName = $project->clientName ?? 'Unknown';
        if (!isset($financialHealth['client_segment_health'][$clientName])) {
            $financialHealth['client_segment_health'][$clientName] = [
                'revenue' => 0,
                'project_count' => 0,
                'avg_project_value' => 0,
                'client_type' => 'Corporate', // Default, could be enhanced with client data
                'project_count' => 0,
                'employee_count' => 0
            ];
        }
        $financialHealth['client_segment_health'][$clientName]['revenue'] += $billableValue;
        $financialHealth['client_segment_health'][$clientName]['project_count']++;
    }
}

// Process employee costs
if ($employees) {
    foreach ($employees as $employee) {
        $unitAssignments = Employee::user_unit_assignments(['userID'=>$employee->ID, 'Suspended'=>'N'], false, $DBConn);
        // var_dump($unitAssignments);
        $businessUnit=$unitAssignments[0]->unitName ?? 'Unknown';
  
        // var_dump($employee);
        $numberOfHoursInCurrentMonth = 0;
        $numberOfHoursInCurrentMonth = Workutils::get_total_hours_in_month(date('m'), date('Y'), $DBConn);

         // Calculate employee costs
        $costPerHour = $employee->basicSalary/$numberOfHoursInCurrentMonth ?? 0;
        $dailyHours = $employee->dailyHours ?? 8;
        $weekWorkDays = $employee->weekWorkDays ?? 5;
        $monthlyHours = $dailyHours * $weekWorkDays * 4.33;
        $annualHours = $dailyHours * $weekWorkDays * 52;
        $annualCost = $employee->basicSalary * 12;
        
        $financialHealth['overall_metrics']['total_costs'] += $annualCost;
        
        // Business unit cost allocation
        // $businessUnit = $employee->businessUnitName ?? 'Unknown';
        if (isset($financialHealth['business_unit_health'][$businessUnit])) {
            $financialHealth['business_unit_health'][$businessUnit]['costs'] += $annualCost;
            $financialHealth['business_unit_health'][$businessUnit]['project_count']++;
            $financialHealth['business_unit_health'][$businessUnit]['employee_count']++; 
        }
    }
}

// Calculate derived metrics
$financialHealth['overall_metrics']['net_profit'] = $financialHealth['overall_metrics']['total_revenue'] - $financialHealth['overall_metrics']['total_costs'];
$financialHealth['overall_metrics']['profit_margin'] = $financialHealth['overall_metrics']['total_revenue'] > 0 ? 
    ($financialHealth['overall_metrics']['net_profit'] / $financialHealth['overall_metrics']['total_revenue']) * 100 : 0;
$financialHealth['overall_metrics']['cost_efficiency_ratio'] = $financialHealth['overall_metrics']['total_costs'] > 0 ? 
    $financialHealth['overall_metrics']['total_revenue'] / $financialHealth['overall_metrics']['total_costs'] : 0;

// Calculate business unit metrics
foreach ($financialHealth['business_unit_health'] as $unit => &$data) {
    $data['profit'] = $data['revenue'] - $data['costs'];
    $data['avg_project_value'] = $data['project_count'] > 0 ? $data['revenue'] / $data['project_count'] : 0;
    $data['profit_margin'] = $data['revenue'] > 0 ? ($data['profit'] / $data['revenue']) * 100 : 0;
}

// Calculate client segment metrics
foreach ($financialHealth['client_segment_health'] as $client => &$data) {
    $data['avg_project_value'] = $data['project_count'] > 0 ? $data['revenue'] / $data['project_count'] : 0;
}

// Calculate financial ratios
$financialHealth['financial_ratios'] = [
    'revenue_per_employee' => count($employees) > 0 ? $financialHealth['overall_metrics']['total_revenue'] / count($employees) : 0,
    'cost_per_employee' => count($employees) > 0 ? $financialHealth['overall_metrics']['total_costs'] / count($employees) : 0,
    'revenue_per_project' => count($projects) > 0 ? $financialHealth['overall_metrics']['total_revenue'] / count($projects) : 0,
    'cost_per_project' => count($projects) > 0 ? $financialHealth['overall_metrics']['total_costs'] / count($projects) : 0,
    'employee_productivity' => count($employees) > 0 ? count($projects) / count($employees) : 0
];

// Risk indicators
$financialHealth['risk_indicators'] = [
    'low_profit_margin' => $financialHealth['overall_metrics']['profit_margin'] < 10,
    'high_cost_ratio' => $financialHealth['overall_metrics']['cost_efficiency_ratio'] < 1.5,
    'concentration_risk' => count($financialHealth['client_segment_health']) < 5,
    'unit_dependency' => count($financialHealth['business_unit_health']) < 3
];

// Sort business units by profitability
uasort($financialHealth['business_unit_health'], function($a, $b) {
    return $b['profit_margin'] <=> $a['profit_margin'];
});

// Sort clients by revenue
uasort($financialHealth['client_segment_health'], function($a, $b) {
    return $b['revenue'] <=> $a['revenue'];
});

?>

<div class="container-fluid">
    <!-- Financial Health Overview -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-heart-pulse-line me-2"></i>Organizational Financial Health Overview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card bg-white shadow-sm">
                                <div class="card-body text-center">
                                    <div class="avatar-sm bg-success-subtle rounded mx-auto mb-3">
                                        <span class="avatar-title bg-success-subtle text-success rounded">
                                            <i class="ri-money-dollar-circle-line fs-20"></i>
                                        </span>
                                    </div>
                                    <h4 class="mb-1 text-success">KES <?= number_format($financialHealth['overall_metrics']['total_revenue'], 0) ?></h4>
                                    <p class="text-muted mb-0">Total Revenue</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card bg-white shadow-sm">
                                <div class="card-body text-center">
                                    <div class="avatar-sm bg-danger-subtle rounded mx-auto mb-3">
                                        <span class="avatar-title bg-danger-subtle text-danger rounded">
                                            <i class="ri-money-cny-circle-line fs-20"></i>
                                        </span>
                                    </div>
                                    <h4 class="mb-1 text-danger">KES <?= number_format($financialHealth['overall_metrics']['total_costs'], 0) ?></h4>
                                    <p class="text-muted mb-0">Total Costs</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card bg-white shadow-sm">
                                <div class="card-body text-center">
                                    <div class="avatar-sm bg-info-subtle rounded mx-auto mb-3">
                                        <span class="avatar-title bg-info-subtle text-info rounded">
                                            <i class="ri-trending-up-line fs-20"></i>
                                        </span>
                                    </div>
                                    <h4 class="mb-1 <?= $financialHealth['overall_metrics']['net_profit'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                        KES <?= number_format($financialHealth['overall_metrics']['net_profit'], 0) ?>
                                    </h4>
                                    <p class="text-muted mb-0">Net Profit</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card bg-white shadow-sm">
                                <div class="card-body text-center">
                                    <div class="avatar-sm bg-warning-subtle rounded mx-auto mb-3">
                                        <span class="avatar-title bg-warning-subtle text-warning rounded">
                                            <i class="ri-percent-line fs-20"></i>
                                        </span>
                                    </div>
                                    <h4 class="mb-1 <?= $financialHealth['overall_metrics']['profit_margin'] >= 15 ? 'text-success' : ($financialHealth['overall_metrics']['profit_margin'] >= 5 ? 'text-warning' : 'text-danger') ?>">
                                        <?= number_format($financialHealth['overall_metrics']['profit_margin'], 1) ?>%
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

    <!-- Financial Ratios -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-calculator-line me-2"></i>Key Financial Ratios
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                            <div class="text-center">
                                <h5 class="text-primary">KES <?= number_format($financialHealth['financial_ratios']['revenue_per_employee'], 0) ?></h5>
                                <small class="text-muted">Revenue per Employee</small>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                            <div class="text-center">
                                <h5 class="text-danger">KES <?= number_format($financialHealth['financial_ratios']['cost_per_employee'], 0) ?></h5>
                                <small class="text-muted">Cost per Employee</small>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                            <div class="text-center">
                                <h5 class="text-info">KES <?= number_format($financialHealth['financial_ratios']['revenue_per_project'], 0) ?></h5>
                                <small class="text-muted">Revenue per Project</small>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                            <div class="text-center">
                                <h5 class="text-warning">KES <?= number_format($financialHealth['financial_ratios']['cost_per_project'], 0) ?></h5>
                                <small class="text-muted">Cost per Project</small>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                            <div class="text-center">
                                <h5 class="text-success"><?= number_format($financialHealth['financial_ratios']['employee_productivity'], 1) ?></h5>
                                <small class="text-muted">Projects per Employee</small>
                            </div>
                        </div>
                        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                            <div class="text-center">
                                <h5 class="text-dark"><?= number_format($financialHealth['overall_metrics']['cost_efficiency_ratio'], 2) ?></h5>
                                <small class="text-muted">Cost Efficiency Ratio</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Business Unit Health -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-building-line me-2"></i>Business Unit Financial Health
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Business Unit</th>
                                    <th>Revenue</th>
                                    <th>Costs</th>
                                    <th>Profit</th>
                                    <th>Profit Margin</th>
                                    <th>Projects</th>
                                    <th>Avg Project Value</th>
                                    <th>Health Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($financialHealth['business_unit_health'] as $unit => $data): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-light rounded me-2">
                                                <span class="avatar-title bg-light text-dark rounded">
                                                    <i class="ri-building-line"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($unit) ?></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>KES <?= number_format($data['revenue'], 0) ?></td>
                                    <td>KES <?= number_format($data['costs'], 0) ?></td>
                                    <td>
                                        <span class="<?= $data['profit'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                            KES <?= number_format($data['profit'], 0) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $marginClass = $data['profit_margin'] >= 15 ? 'bg-success-subtle text-success' : 
                                                     ($data['profit_margin'] >= 5 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger');
                                        ?>
                                        <span class="badge <?= $marginClass ?>"><?= number_format($data['profit_margin'], 1) ?>%</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary"><?= $data['project_count'] ?></span>
                                    </td>
                                    <td>KES <?= number_format($data['avg_project_value'], 0) ?></td>
                                    <td>
                                        <?php 
                                        $healthStatus = $data['profit_margin'] >= 15 ? 'Excellent' : 
                                                      ($data['profit_margin'] >= 5 ? 'Good' : 
                                                      ($data['profit_margin'] >= 0 ? 'Fair' : 'Poor'));
                                        $healthClass = $data['profit_margin'] >= 15 ? 'bg-success-subtle text-success' : 
                                                     ($data['profit_margin'] >= 5 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger');
                                        ?>
                                        <span class="badge <?= $healthClass ?>"><?= $healthStatus ?></span>
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

    <!-- Top Clients -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-user-star-line me-2"></i>Top Clients by Revenue
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Client</th>
                                    <th>Revenue</th>
                                    <th>Projects</th>
                                    <th>Avg Project Value</th>
                                    <th>Client Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $topClients = array_slice($financialHealth['client_segment_health'], 0, 10, true);
                                $rank = 1;
                                foreach ($topClients as $client => $data): 
                                ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary">#<?= $rank++ ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-light rounded me-2">
                                                <span class="avatar-title bg-light text-dark rounded">
                                                    <i class="ri-user-line"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($client) ?></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>KES <?= number_format($data['revenue'], 0) ?></td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info"><?= $data['project_count'] ?></span>
                                    </td>
                                    <td>KES <?= number_format($data['avg_project_value'], 0) ?></td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary"><?= $data['client_type'] ?></span>
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

    <!-- Risk Indicators -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-alarm-warning-line me-2"></i>Financial Risk Indicators
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card <?= $financialHealth['risk_indicators']['low_profit_margin'] ? 'border-danger' : 'border-success' ?>">
                                <div class="card-body text-center">
                                    <div class="avatar-sm <?= $financialHealth['risk_indicators']['low_profit_margin'] ? 'bg-danger-subtle' : 'bg-success-subtle' ?> rounded mx-auto mb-3">
                                        <span class="avatar-title <?= $financialHealth['risk_indicators']['low_profit_margin'] ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' ?> rounded">
                                            <i class="ri-percent-line fs-20"></i>
                                        </span>
                                    </div>
                                    <h6 class="<?= $financialHealth['risk_indicators']['low_profit_margin'] ? 'text-danger' : 'text-success' ?>">
                                        <?= $financialHealth['risk_indicators']['low_profit_margin'] ? 'High Risk' : 'Low Risk' ?>
                                    </h6>
                                    <p class="text-muted mb-0">Profit Margin</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card <?= $financialHealth['risk_indicators']['high_cost_ratio'] ? 'border-danger' : 'border-success' ?>">
                                <div class="card-body text-center">
                                    <div class="avatar-sm <?= $financialHealth['risk_indicators']['high_cost_ratio'] ? 'bg-danger-subtle' : 'bg-success-subtle' ?> rounded mx-auto mb-3">
                                        <span class="avatar-title <?= $financialHealth['risk_indicators']['high_cost_ratio'] ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' ?> rounded">
                                            <i class="ri-scales-line fs-20"></i>
                                        </span>
                                    </div>
                                    <h6 class="<?= $financialHealth['risk_indicators']['high_cost_ratio'] ? 'text-danger' : 'text-success' ?>">
                                        <?= $financialHealth['risk_indicators']['high_cost_ratio'] ? 'High Risk' : 'Low Risk' ?>
                                    </h6>
                                    <p class="text-muted mb-0">Cost Efficiency</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card <?= $financialHealth['risk_indicators']['concentration_risk'] ? 'border-warning' : 'border-success' ?>">
                                <div class="card-body text-center">
                                    <div class="avatar-sm <?= $financialHealth['risk_indicators']['concentration_risk'] ? 'bg-warning-subtle' : 'bg-success-subtle' ?> rounded mx-auto mb-3">
                                        <span class="avatar-title <?= $financialHealth['risk_indicators']['concentration_risk'] ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success' ?> rounded">
                                            <i class="ri-focus-line fs-20"></i>
                                        </span>
                                    </div>
                                    <h6 class="<?= $financialHealth['risk_indicators']['concentration_risk'] ? 'text-warning' : 'text-success' ?>">
                                        <?= $financialHealth['risk_indicators']['concentration_risk'] ? 'Medium Risk' : 'Low Risk' ?>
                                    </h6>
                                    <p class="text-muted mb-0">Client Concentration</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card <?= $financialHealth['risk_indicators']['unit_dependency'] ? 'border-warning' : 'border-success' ?>">
                                <div class="card-body text-center">
                                    <div class="avatar-sm <?= $financialHealth['risk_indicators']['unit_dependency'] ? 'bg-warning-subtle' : 'bg-success-subtle' ?> rounded mx-auto mb-3">
                                        <span class="avatar-title <?= $financialHealth['risk_indicators']['unit_dependency'] ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success' ?> rounded">
                                            <i class="ri-building-line fs-20"></i>
                                        </span>
                                    </div>
                                    <h6 class="<?= $financialHealth['risk_indicators']['unit_dependency'] ? 'text-warning' : 'text-success' ?>">
                                        <?= $financialHealth['risk_indicators']['unit_dependency'] ? 'Medium Risk' : 'Low Risk' ?>
                                    </h6>
                                    <p class="text-muted mb-0">Unit Diversity</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mb-4">
        <div class="col-xl-6 col-md-12 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-pie-chart-line me-2"></i>Revenue Distribution by Business Unit
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueDistributionChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-md-12 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-bar-chart-line me-2"></i>Profitability by Business Unit
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="profitabilityChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Distribution Chart
    const revenueDistributionCtx = document.getElementById('revenueDistributionChart');
    if (revenueDistributionCtx) {
        const businessUnitData = <?= json_encode($financialHealth['business_unit_health']) ?>;
        const unitLabels = Object.keys(businessUnitData);
        const revenueData = unitLabels.map(label => businessUnitData[label]['revenue']);
        
        new Chart(revenueDistributionCtx, {
            type: 'doughnut',
            data: {
                labels: unitLabels,
                datasets: [{
                    data: revenueData,
                    backgroundColor: [
                        '#28a745', '#17a2b8', '#ffc107', '#dc3545', '#6f42c1', '#6c757d'
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

    // Profitability Chart
    const profitabilityCtx = document.getElementById('profitabilityChart');
    if (profitabilityCtx) {
        const businessUnitData = <?= json_encode($financialHealth['business_unit_health']) ?>;
        const unitLabels = Object.keys(businessUnitData);
        const profitData = unitLabels.map(label => businessUnitData[label]['profit']);
        
        new Chart(profitabilityCtx, {
            type: 'bar',
            data: {
                labels: unitLabels,
                datasets: [{
                    label: 'Profit (KES)',
                    data: profitData,
                    backgroundColor: profitData.map(profit => profit >= 0 ? '#28a745' : '#dc3545'),
                    borderColor: profitData.map(profit => profit >= 0 ? '#28a745' : '#dc3545'),
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
                                return 'KES ' + value.toLocaleString();
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
                                return 'Profit: KES ' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
