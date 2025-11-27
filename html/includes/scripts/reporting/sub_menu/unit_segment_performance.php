<?php
/**
 * Unit/Segment Financial Performance Analysis
 * Detailed analysis of financial performance across business units and client segments
 */

// Get comprehensive data
$projects = Projects::projects_full([], false, $DBConn);
$employees = Employee::employees([], false, $DBConn);
$clients = Client::clients([], false, $DBConn);

// Initialize performance metrics
$performanceMetrics = [
    'business_units' => [],
    'client_segments' => [],
    'comparative_analysis' => [],
    'performance_trends' => [],
    'efficiency_metrics' => []
];

// Process business unit performance
if ($projects) {
    foreach ($projects as $project) {
        $businessUnit = $project->businessUnitName ?? 'Unknown';
        
        if (!isset($performanceMetrics['business_units'][$businessUnit])) {
            $performanceMetrics['business_units'][$businessUnit] = [
                'revenue' => 0,
                'costs' => 0,
                'profit' => 0,
                'project_count' => 0,
                'avg_project_value' => 0,
                'profit_margin' => 0,
                'employee_count' => 0,
                'revenue_per_employee' => 0,
                'projects_per_employee' => 0,
                'efficiency_score' => 0
            ];
        }
        
        $performanceMetrics['business_units'][$businessUnit]['revenue'] += $project->billableRateValue ?? 0;
        $performanceMetrics['business_units'][$businessUnit]['project_count']++;
    }
}

// Process employee costs by business unit
if ($employees) {
    foreach ($employees as $employee) {
        $businessUnit = $employee->businessUnitName ?? 'Unknown';
        
        if (isset($performanceMetrics['business_units'][$businessUnit])) {
            $costPerHour = $employee->costPerHour ?? 0;
            $dailyHours = $employee->dailyHours ?? 8;
            $weekWorkDays = $employee->weekWorkDays ?? 5;
            $annualHours = $dailyHours * $weekWorkDays * 52;
            $annualCost = $costPerHour * $annualHours;
            
            $performanceMetrics['business_units'][$businessUnit]['costs'] += $annualCost;
            $performanceMetrics['business_units'][$businessUnit]['employee_count']++;
        }
    }
}

// Calculate business unit metrics
foreach ($performanceMetrics['business_units'] as $unit => &$data) {
    $data['profit'] = $data['revenue'] - $data['costs'];
    $data['avg_project_value'] = $data['project_count'] > 0 ? $data['revenue'] / $data['project_count'] : 0;
    $data['profit_margin'] = $data['revenue'] > 0 ? ($data['profit'] / $data['revenue']) * 100 : 0;
    $data['revenue_per_employee'] = $data['employee_count'] > 0 ? $data['revenue'] / $data['employee_count'] : 0;
    $data['projects_per_employee'] = $data['employee_count'] > 0 ? $data['project_count'] / $data['employee_count'] : 0;
    
    // Calculate efficiency score (weighted combination of metrics)
    $efficiencyScore = 0;
    if ($data['revenue'] > 0) {
        $efficiencyScore += ($data['profit_margin'] / 100) * 30; // 30% weight for profit margin
        $efficiencyScore += min($data['revenue_per_employee'] / 1000000, 1) * 25; // 25% weight for revenue per employee
        $efficiencyScore += min($data['projects_per_employee'], 10) / 10 * 25; // 25% weight for projects per employee
        $efficiencyScore += min($data['avg_project_value'] / 500000, 1) * 20; // 20% weight for avg project value
    }
    $data['efficiency_score'] = $efficiencyScore * 100;
}

// Process client segment performance
if ($projects) {
    foreach ($projects as $project) {
        $clientName = $project->clientName ?? 'Unknown';
        
        if (!isset($performanceMetrics['client_segments'][$clientName])) {
            $performanceMetrics['client_segments'][$clientName] = [
                'revenue' => 0,
                'project_count' => 0,
                'avg_project_value' => 0,
                'business_unit' => $project->businessUnitName ?? 'Unknown',
                'client_type' => 'Corporate', // Default, could be enhanced
                'lifetime_value' => 0,
                'engagement_score' => 0
            ];
        }
        
        $performanceMetrics['client_segments'][$clientName]['revenue'] += $project->billableRateValue ?? 0;
        $performanceMetrics['client_segments'][$clientName]['project_count']++;
    }
}

// Calculate client segment metrics
foreach ($performanceMetrics['client_segments'] as $client => &$data) {
    $data['avg_project_value'] = $data['project_count'] > 0 ? $data['revenue'] / $data['project_count'] : 0;
    $data['lifetime_value'] = $data['revenue']; // Could be enhanced with historical data
    $data['engagement_score'] = min($data['project_count'] * 10, 100); // Simple engagement score
}

// Sort business units by efficiency score
uasort($performanceMetrics['business_units'], function($a, $b) {
    return $b['efficiency_score'] <=> $a['efficiency_score'];
});

// Sort client segments by revenue
uasort($performanceMetrics['client_segments'], function($a, $b) {
    return $b['revenue'] <=> $a['revenue'];
});

// Calculate comparative analysis
$totalRevenue = array_sum(array_column($performanceMetrics['business_units'], 'revenue'));
$totalCosts = array_sum(array_column($performanceMetrics['business_units'], 'costs'));
$totalProfit = $totalRevenue - $totalCosts;

foreach ($performanceMetrics['business_units'] as $unit => &$data) {
    $data['revenue_share'] = $totalRevenue > 0 ? ($data['revenue'] / $totalRevenue) * 100 : 0;
    $data['cost_share'] = $totalCosts > 0 ? ($data['costs'] / $totalCosts) * 100 : 0;
    $data['profit_share'] = $totalProfit > 0 ? ($data['profit'] / $totalProfit) * 100 : 0;
}

?>

<div class="container-fluid">
    <!-- Performance Overview -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-dashboard-line me-2"></i>Unit/Segment Performance Overview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card bg-white shadow-sm">
                                <div class="card-body text-center">
                                    <div class="avatar-sm bg-primary-subtle rounded mx-auto mb-3">
                                        <span class="avatar-title bg-primary-subtle text-primary rounded">
                                            <i class="ri-building-line fs-20"></i>
                                        </span>
                                    </div>
                                    <h4 class="mb-1 text-primary"><?= count($performanceMetrics['business_units']) ?></h4>
                                    <p class="text-muted mb-0">Business Units</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card bg-white shadow-sm">
                                <div class="card-body text-center">
                                    <div class="avatar-sm bg-info-subtle rounded mx-auto mb-3">
                                        <span class="avatar-title bg-info-subtle text-info rounded">
                                            <i class="ri-user-star-line fs-20"></i>
                                        </span>
                                    </div>
                                    <h4 class="mb-1 text-info"><?= count($performanceMetrics['client_segments']) ?></h4>
                                    <p class="text-muted mb-0">Client Segments</p>
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
                                    <h4 class="mb-1 text-success">KES <?= number_format($totalRevenue, 0) ?></h4>
                                    <p class="text-muted mb-0">Total Revenue</p>
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
                                    <h4 class="mb-1 <?= $totalProfit >= 0 ? 'text-success' : 'text-danger' ?>">
                                        KES <?= number_format($totalProfit, 0) ?>
                                    </h4>
                                    <p class="text-muted mb-0">Total Profit</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Business Unit Performance -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-building-line me-2"></i>Business Unit Performance Analysis
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
                                    <th>Employees</th>
                                    <th>Revenue/Employee</th>
                                    <th>Projects/Employee</th>
                                    <th>Efficiency Score</th>
                                    <th>Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($performanceMetrics['business_units'] as $unit => $data): ?>
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
                                                <small class="text-muted"><?= number_format($data['revenue_share'], 1) ?>% of total revenue</small>
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
                                        <span class="badge bg-primary-subtle text-primary"><?= $data['employee_count'] ?></span>
                                    </td>
                                    <td>KES <?= number_format($data['revenue_per_employee'], 0) ?></td>
                                    <td><?= number_format($data['projects_per_employee'], 1) ?></td>
                                    <td>
                                        <?php 
                                        $efficiencyClass = $data['efficiency_score'] >= 70 ? 'bg-success-subtle text-success' : 
                                                         ($data['efficiency_score'] >= 50 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger');
                                        ?>
                                        <span class="badge <?= $efficiencyClass ?>"><?= number_format($data['efficiency_score'], 0) ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                        $performance = $data['efficiency_score'] >= 70 ? 'Excellent' : 
                                                     ($data['efficiency_score'] >= 50 ? 'Good' : 
                                                     ($data['efficiency_score'] >= 30 ? 'Fair' : 'Poor'));
                                        $performanceClass = $data['efficiency_score'] >= 70 ? 'bg-success-subtle text-success' : 
                                                          ($data['efficiency_score'] >= 50 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger');
                                        ?>
                                        <span class="badge <?= $performanceClass ?>"><?= $performance ?></span>
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

    <!-- Top Client Segments -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-user-star-line me-2"></i>Top Client Segments Performance
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Client</th>
                                    <th>Business Unit</th>
                                    <th>Revenue</th>
                                    <th>Projects</th>
                                    <th>Avg Project Value</th>
                                    <th>Engagement Score</th>
                                    <th>Client Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $topClients = array_slice($performanceMetrics['client_segments'], 0, 15, true);
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
                                    <td><?= htmlspecialchars($data['business_unit']) ?></td>
                                    <td>KES <?= number_format($data['revenue'], 0) ?></td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info"><?= $data['project_count'] ?></span>
                                    </td>
                                    <td>KES <?= number_format($data['avg_project_value'], 0) ?></td>
                                    <td>
                                        <?php 
                                        $engagementClass = $data['engagement_score'] >= 80 ? 'bg-success-subtle text-success' : 
                                                         ($data['engagement_score'] >= 60 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger');
                                        ?>
                                        <span class="badge <?= $engagementClass ?>"><?= $data['engagement_score'] ?></span>
                                    </td>
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

    <!-- Comparative Analysis -->
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
                        <i class="ri-bar-chart-line me-2"></i>Efficiency Score by Business Unit
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="efficiencyChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-trophy-line me-2"></i>Top Performing Unit
                    </h5>
                </div>
                <div class="card-body">
                    <?php 
                    $topUnit = array_key_first($performanceMetrics['business_units']);
                    $topUnitData = $performanceMetrics['business_units'][$topUnit];
                    ?>
                    <div class="text-center">
                        <div class="avatar-sm bg-success-subtle rounded mx-auto mb-3">
                            <span class="avatar-title bg-success-subtle text-success rounded">
                                <i class="ri-trophy-line fs-20"></i>
                            </span>
                        </div>
                        <h5 class="text-success"><?= htmlspecialchars($topUnit) ?></h5>
                        <p class="text-muted mb-2">Efficiency Score: <strong><?= number_format($topUnitData['efficiency_score'], 0) ?></strong></p>
                        <p class="text-muted mb-2">Revenue: <strong>KES <?= number_format($topUnitData['revenue'], 0) ?></strong></p>
                        <p class="text-muted mb-0">Profit Margin: <strong><?= number_format($topUnitData['profit_margin'], 1) ?>%</strong></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-user-star-line me-2"></i>Top Client
                    </h5>
                </div>
                <div class="card-body">
                    <?php 
                    $topClient = array_key_first($performanceMetrics['client_segments']);
                    $topClientData = $performanceMetrics['client_segments'][$topClient];
                    ?>
                    <div class="text-center">
                        <div class="avatar-sm bg-info-subtle rounded mx-auto mb-3">
                            <span class="avatar-title bg-info-subtle text-info rounded">
                                <i class="ri-user-star-line fs-20"></i>
                            </span>
                        </div>
                        <h5 class="text-info"><?= htmlspecialchars($topClient) ?></h5>
                        <p class="text-muted mb-2">Revenue: <strong>KES <?= number_format($topClientData['revenue'], 0) ?></strong></p>
                        <p class="text-muted mb-2">Projects: <strong><?= $topClientData['project_count'] ?></strong></p>
                        <p class="text-muted mb-0">Engagement: <strong><?= $topClientData['engagement_score'] ?></strong></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-trending-up-line me-2"></i>Performance Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="avatar-sm bg-warning-subtle rounded mx-auto mb-3">
                            <span class="avatar-title bg-warning-subtle text-warning rounded">
                                <i class="ri-trending-up-line fs-20"></i>
                            </span>
                        </div>
                        <h5 class="text-warning">Overall Performance</h5>
                        <p class="text-muted mb-2">Total Revenue: <strong>KES <?= number_format($totalRevenue, 0) ?></strong></p>
                        <p class="text-muted mb-2">Total Profit: <strong>KES <?= number_format($totalProfit, 0) ?></strong></p>
                        <p class="text-muted mb-0">Avg Efficiency: <strong><?= number_format(array_sum(array_column($performanceMetrics['business_units'], 'efficiency_score')) / count($performanceMetrics['business_units']), 0) ?></strong></p>
                    </div>
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
        const businessUnitData = <?= json_encode($performanceMetrics['business_units']) ?>;
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

    // Efficiency Chart
    const efficiencyCtx = document.getElementById('efficiencyChart');
    if (efficiencyCtx) {
        const businessUnitData = <?= json_encode($performanceMetrics['business_units']) ?>;
        const unitLabels = Object.keys(businessUnitData);
        const efficiencyData = unitLabels.map(label => businessUnitData[label]['efficiency_score']);
        
        new Chart(efficiencyCtx, {
            type: 'bar',
            data: {
                labels: unitLabels,
                datasets: [{
                    label: 'Efficiency Score',
                    data: efficiencyData,
                    backgroundColor: efficiencyData.map(score => {
                        if (score >= 70) return '#28a745';
                        if (score >= 50) return '#ffc107';
                        return '#dc3545';
                    }),
                    borderColor: efficiencyData.map(score => {
                        if (score >= 70) return '#28a745';
                        if (score >= 50) return '#ffc107';
                        return '#dc3545';
                    }),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
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
                                return 'Efficiency Score: ' + context.parsed.y + '%';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
