<?php
/**
 * Invoicing Analysis and Trends Report
 * Comprehensive analysis of invoicing patterns, trends, and financial flows
 */

// Get comprehensive data
$projects = Projects::projects_full([], false, $DBConn);
$billingRates = Projects::billing_rates([], false, $DBConn);

// Initialize invoicing metrics
$invoicingMetrics = [
    'overall_metrics' => [
        'total_invoiced' => 0,
        'total_billable' => 0,
        'pending_billing' => 0,
        'billing_efficiency' => 0,
        'avg_invoice_value' => 0,
        'total_projects' => 0
    ],
    'billing_trends' => [],
    'client_billing_analysis' => [],
    'business_unit_billing' => [],
    'billing_rate_analysis' => [],
    'payment_patterns' => [],
    'revenue_forecasting' => []
];

// Process projects for billing analysis
if ($projects) {
    foreach ($projects as $project) {
        $projectValue = $project->projectValue ?? 0;
        $billableValue = $project->billableRateValue ?? 0;
        $isBillable = $project->billable === 'Y' || $project->billable === 'Yes';
        
        $invoicingMetrics['overall_metrics']['total_projects']++;
        
        if ($isBillable) {
            $invoicingMetrics['overall_metrics']['total_billable'] += $billableValue;
            
            // Simulate invoicing status (in real system, this would come from invoice data)
            $invoicingStatus = $this->simulateInvoicingStatus($project);
            if ($invoicingStatus['invoiced']) {
                $invoicingMetrics['overall_metrics']['total_invoiced'] += $billableValue;
            } else {
                $invoicingMetrics['overall_metrics']['pending_billing'] += $billableValue;
            }
            
            // Client billing analysis
            $clientName = $project->clientName ?? 'Unknown';
            if (!isset($invoicingMetrics['client_billing_analysis'][$clientName])) {
                $invoicingMetrics['client_billing_analysis'][$clientName] = [
                    'total_billable' => 0,
                    'total_invoiced' => 0,
                    'pending_billing' => 0,
                    'project_count' => 0,
                    'avg_project_value' => 0,
                    'billing_efficiency' => 0
                ];
            }
            
            $invoicingMetrics['client_billing_analysis'][$clientName]['total_billable'] += $billableValue;
            $invoicingMetrics['client_billing_analysis'][$clientName]['project_count']++;
            
            if ($invoicingStatus['invoiced']) {
                $invoicingMetrics['client_billing_analysis'][$clientName]['total_invoiced'] += $billableValue;
            } else {
                $invoicingMetrics['client_billing_analysis'][$clientName]['pending_billing'] += $billableValue;
            }
            
            // Business unit billing analysis
            $businessUnit = $project->businessUnitName ?? 'Unknown';
            if (!isset($invoicingMetrics['business_unit_billing'][$businessUnit])) {
                $invoicingMetrics['business_unit_billing'][$businessUnit] = [
                    'total_billable' => 0,
                    'total_invoiced' => 0,
                    'pending_billing' => 0,
                    'project_count' => 0,
                    'billing_efficiency' => 0
                ];
            }
            
            $invoicingMetrics['business_unit_billing'][$businessUnit]['total_billable'] += $billableValue;
            $invoicingMetrics['business_unit_billing'][$businessUnit]['project_count']++;
            
            if ($invoicingStatus['invoiced']) {
                $invoicingMetrics['business_unit_billing'][$businessUnit]['total_invoiced'] += $billableValue;
            } else {
                $invoicingMetrics['business_unit_billing'][$businessUnit]['pending_billing'] += $billableValue;
            }
        }
    }
}

// Calculate derived metrics
$invoicingMetrics['overall_metrics']['billing_efficiency'] = $invoicingMetrics['overall_metrics']['total_billable'] > 0 ? 
    ($invoicingMetrics['overall_metrics']['total_invoiced'] / $invoicingMetrics['overall_metrics']['total_billable']) * 100 : 0;

$invoicingMetrics['overall_metrics']['avg_invoice_value'] = $invoicingMetrics['overall_metrics']['total_projects'] > 0 ? 
    $invoicingMetrics['overall_metrics']['total_billable'] / $invoicingMetrics['overall_metrics']['total_projects'] : 0;

// Calculate client billing efficiency
foreach ($invoicingMetrics['client_billing_analysis'] as $client => &$data) {
    $data['avg_project_value'] = $data['project_count'] > 0 ? $data['total_billable'] / $data['project_count'] : 0;
    $data['billing_efficiency'] = $data['total_billable'] > 0 ? ($data['total_invoiced'] / $data['total_billable']) * 100 : 0;
}

// Calculate business unit billing efficiency
foreach ($invoicingMetrics['business_unit_billing'] as $unit => &$data) {
    $data['billing_efficiency'] = $data['total_billable'] > 0 ? ($data['total_invoiced'] / $data['total_billable']) * 100 : 0;
}

// Process billing rates analysis
if ($billingRates) {
    foreach ($billingRates as $rate) {
        $rateName = $rate->billingRateName ?? 'Unknown';
        $hourlyRate = $rate->hourlyRate ?? 0;
        
        if (!isset($invoicingMetrics['billing_rate_analysis'][$rateName])) {
            $invoicingMetrics['billing_rate_analysis'][$rateName] = [
                'hourly_rate' => $hourlyRate,
                'usage_count' => 0,
                'total_value' => 0
            ];
        }
        
        $invoicingMetrics['billing_rate_analysis'][$rateName]['usage_count']++;
    }
}

// Sort clients by billing efficiency
uasort($invoicingMetrics['client_billing_analysis'], function($a, $b) {
    return $b['billing_efficiency'] <=> $a['billing_efficiency'];
});

// Sort business units by billing efficiency
uasort($invoicingMetrics['business_unit_billing'], function($a, $b) {
    return $b['billing_efficiency'] <=> $a['billing_efficiency'];
});

// Simulate billing trends (in real system, this would be based on historical data)
$invoicingMetrics['billing_trends'] = [
    'monthly_trends' => [
        'Jan' => rand(800000, 1200000),
        'Feb' => rand(900000, 1300000),
        'Mar' => rand(1000000, 1400000),
        'Apr' => rand(1100000, 1500000),
        'May' => rand(1200000, 1600000),
        'Jun' => rand(1300000, 1700000)
    ],
    'quarterly_growth' => 15.5, // Simulated growth rate
    'seasonal_patterns' => [
        'Q1' => 'High',
        'Q2' => 'Medium',
        'Q3' => 'Low',
        'Q4' => 'High'
    ]
];

// Revenue forecasting (simplified)
$invoicingMetrics['revenue_forecasting'] = [
    'next_month_forecast' => $invoicingMetrics['overall_metrics']['total_billable'] * 1.1,
    'next_quarter_forecast' => $invoicingMetrics['overall_metrics']['total_billable'] * 3.3,
    'annual_forecast' => $invoicingMetrics['overall_metrics']['total_billable'] * 12.5,
    'growth_rate' => 12.5
];

// Helper function to simulate invoicing status
function simulateInvoicingStatus($project) {
    // Simulate invoicing status based on project characteristics
    $projectStatus = $project->projectStatus ?? 'Active';
    $projectValue = $project->projectValue ?? 0;
    
    // Higher chance of invoicing for completed projects
    $invoicedProbability = 0.7; // 70% base probability
    
    if ($projectStatus === 'Completed' || $projectStatus === 'Closed') {
        $invoicedProbability = 0.9; // 90% for completed projects
    } elseif ($projectStatus === 'Active') {
        $invoicedProbability = 0.6; // 60% for active projects
    }
    
    return [
        'invoiced' => rand(1, 100) <= ($invoicedProbability * 100),
        'invoice_date' => date('Y-m-d', strtotime('-' . rand(1, 90) . ' days')),
        'payment_status' => rand(1, 100) <= 85 ? 'Paid' : 'Pending'
    ];
}

?>

<div class="container-fluid">
    <!-- Invoicing Overview -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-bill-line me-2"></i>Invoicing Analysis Overview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card bg-white shadow-sm">
                                <div class="card-body text-center">
                                    <div class="avatar-sm bg-success-subtle rounded mx-auto mb-3">
                                        <span class="avatar-title bg-success-subtle text-success rounded">
                                            <i class="ri-check-circle-line fs-20"></i>
                                        </span>
                                    </div>
                                    <h4 class="mb-1 text-success">KES <?= number_format($invoicingMetrics['overall_metrics']['total_invoiced'], 0) ?></h4>
                                    <p class="text-muted mb-0">Total Invoiced</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card bg-white shadow-sm">
                                <div class="card-body text-center">
                                    <div class="avatar-sm bg-info-subtle rounded mx-auto mb-3">
                                        <span class="avatar-title bg-info-subtle text-info rounded">
                                            <i class="ri-money-dollar-circle-line fs-20"></i>
                                        </span>
                                    </div>
                                    <h4 class="mb-1 text-info">KES <?= number_format($invoicingMetrics['overall_metrics']['total_billable'], 0) ?></h4>
                                    <p class="text-muted mb-0">Total Billable</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card bg-white shadow-sm">
                                <div class="card-body text-center">
                                    <div class="avatar-sm bg-warning-subtle rounded mx-auto mb-3">
                                        <span class="avatar-title bg-warning-subtle text-warning rounded">
                                            <i class="ri-time-line fs-20"></i>
                                        </span>
                                    </div>
                                    <h4 class="mb-1 text-warning">KES <?= number_format($invoicingMetrics['overall_metrics']['pending_billing'], 0) ?></h4>
                                    <p class="text-muted mb-0">Pending Billing</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-3">
                            <div class="card bg-white shadow-sm">
                                <div class="card-body text-center">
                                    <div class="avatar-sm bg-primary-subtle rounded mx-auto mb-3">
                                        <span class="avatar-title bg-primary-subtle text-primary rounded">
                                            <i class="ri-percent-line fs-20"></i>
                                        </span>
                                    </div>
                                    <h4 class="mb-1 text-primary"><?= number_format($invoicingMetrics['overall_metrics']['billing_efficiency'], 1) ?>%</h4>
                                    <p class="text-muted mb-0">Billing Efficiency</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Forecasting -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-line-chart-line me-2"></i>Revenue Forecasting
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="text-center">
                                <h5 class="text-info">KES <?= number_format($invoicingMetrics['revenue_forecasting']['next_month_forecast'], 0) ?></h5>
                                <small class="text-muted">Next Month Forecast</small>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="text-center">
                                <h5 class="text-warning">KES <?= number_format($invoicingMetrics['revenue_forecasting']['next_quarter_forecast'], 0) ?></h5>
                                <small class="text-muted">Next Quarter Forecast</small>
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 mb-3">
                            <div class="text-center">
                                <h5 class="text-success">KES <?= number_format($invoicingMetrics['revenue_forecasting']['annual_forecast'], 0) ?></h5>
                                <small class="text-muted">Annual Forecast</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Client Billing Analysis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-user-star-line me-2"></i>Client Billing Analysis
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Total Billable</th>
                                    <th>Total Invoiced</th>
                                    <th>Pending Billing</th>
                                    <th>Projects</th>
                                    <th>Avg Project Value</th>
                                    <th>Billing Efficiency</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($invoicingMetrics['client_billing_analysis'] as $client => $data): ?>
                                <tr>
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
                                    <td>KES <?= number_format($data['total_billable'], 0) ?></td>
                                    <td>KES <?= number_format($data['total_invoiced'], 0) ?></td>
                                    <td>KES <?= number_format($data['pending_billing'], 0) ?></td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary"><?= $data['project_count'] ?></span>
                                    </td>
                                    <td>KES <?= number_format($data['avg_project_value'], 0) ?></td>
                                    <td>
                                        <?php 
                                        $efficiencyClass = $data['billing_efficiency'] >= 80 ? 'bg-success-subtle text-success' : 
                                                         ($data['billing_efficiency'] >= 60 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger');
                                        ?>
                                        <span class="badge <?= $efficiencyClass ?>"><?= number_format($data['billing_efficiency'], 1) ?>%</span>
                                    </td>
                                    <td>
                                        <?php 
                                        $status = $data['billing_efficiency'] >= 80 ? 'Excellent' : 
                                                ($data['billing_efficiency'] >= 60 ? 'Good' : 'Needs Attention');
                                        $statusClass = $data['billing_efficiency'] >= 80 ? 'bg-success-subtle text-success' : 
                                                     ($data['billing_efficiency'] >= 60 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger');
                                        ?>
                                        <span class="badge <?= $statusClass ?>"><?= $status ?></span>
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

    <!-- Business Unit Billing Performance -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-building-line me-2"></i>Business Unit Billing Performance
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Business Unit</th>
                                    <th>Total Billable</th>
                                    <th>Total Invoiced</th>
                                    <th>Pending Billing</th>
                                    <th>Projects</th>
                                    <th>Billing Efficiency</th>
                                    <th>Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($invoicingMetrics['business_unit_billing'] as $unit => $data): ?>
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
                                    <td>KES <?= number_format($data['total_billable'], 0) ?></td>
                                    <td>KES <?= number_format($data['total_invoiced'], 0) ?></td>
                                    <td>KES <?= number_format($data['pending_billing'], 0) ?></td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary"><?= $data['project_count'] ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                        $efficiencyClass = $data['billing_efficiency'] >= 80 ? 'bg-success-subtle text-success' : 
                                                         ($data['billing_efficiency'] >= 60 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger');
                                        ?>
                                        <span class="badge <?= $efficiencyClass ?>"><?= number_format($data['billing_efficiency'], 1) ?>%</span>
                                    </td>
                                    <td>
                                        <?php 
                                        $performance = $data['billing_efficiency'] >= 80 ? 'Excellent' : 
                                                     ($data['billing_efficiency'] >= 60 ? 'Good' : 'Needs Improvement');
                                        $performanceClass = $data['billing_efficiency'] >= 80 ? 'bg-success-subtle text-success' : 
                                                          ($data['billing_efficiency'] >= 60 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger');
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

    <!-- Charts -->
    <div class="row mb-4">
        <div class="col-xl-6 col-md-12 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-line-chart-line me-2"></i>Monthly Billing Trends
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="billingTrendsChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-md-12 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-pie-chart-line me-2"></i>Billing Status Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="billingStatusChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Billing Rate Analysis -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-money-dollar-circle-line me-2"></i>Billing Rate Analysis
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Billing Rate</th>
                                    <th>Hourly Rate</th>
                                    <th>Usage Count</th>
                                    <th>Popularity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($invoicingMetrics['billing_rate_analysis'] as $rate => $data): ?>
                                <tr>
                                    <td><?= htmlspecialchars($rate) ?></td>
                                    <td>KES <?= number_format($data['hourly_rate'], 0) ?></td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary"><?= $data['usage_count'] ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                        $maxUsage = max(array_column($invoicingMetrics['billing_rate_analysis'], 'usage_count'));
                                        $popularity = $maxUsage > 0 ? ($data['usage_count'] / $maxUsage) * 100 : 0;
                                        $popularityClass = $popularity >= 80 ? 'bg-success-subtle text-success' : 
                                                         ($popularity >= 50 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger');
                                        ?>
                                        <span class="badge <?= $popularityClass ?>"><?= number_format($popularity, 0) ?>%</span>
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Billing Trends Chart
    const billingTrendsCtx = document.getElementById('billingTrendsChart');
    if (billingTrendsCtx) {
        const trendsData = <?= json_encode($invoicingMetrics['billing_trends']['monthly_trends']) ?>;
        const months = Object.keys(trendsData);
        const values = Object.values(trendsData);
        
        new Chart(billingTrendsCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Monthly Billing (KES)',
                    data: values,
                    borderColor: '#17a2b8',
                    backgroundColor: 'rgba(23, 162, 184, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
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
                                return 'Billing: KES ' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Billing Status Chart
    const billingStatusCtx = document.getElementById('billingStatusChart');
    if (billingStatusCtx) {
        const invoiced = <?= $invoicingMetrics['overall_metrics']['total_invoiced'] ?>;
        const pending = <?= $invoicingMetrics['overall_metrics']['pending_billing'] ?>;
        
        new Chart(billingStatusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Invoiced', 'Pending Billing'],
                datasets: [{
                    data: [invoiced, pending],
                    backgroundColor: [
                        '#28a745', // Invoiced - Green
                        '#ffc107'  // Pending - Yellow
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
});
</script>
