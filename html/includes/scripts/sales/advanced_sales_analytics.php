<?php
/**
 * Advanced Sales Analytics Widgets
 * Enhanced analysis with time-based trends, forecasting, and detailed metrics
 */

// Get sales data with date filtering
$currentMonth = date('Y-m');
$lastMonth = date('Y-m', strtotime('-1 month'));
$currentYear = date('Y');

// Get sales data for current month
$currentMonthSales = Sales::sales_case_mid([
    'orgDataID' => $orgDataID, 
    'entityID' => $entityID, 
    'Suspended' => 'N'
], false, $DBConn);

// Get sales data for last month (you might need to add date filtering to the Sales class)
$lastMonthSales = Sales::sales_case_mid([
    'orgDataID' => $orgDataID, 
    'entityID' => $entityID, 
    'Suspended' => 'N'
], false, $DBConn);

// Process current month data
$currentMonthData = [
    'totalCases' => 0,
    'totalValue' => 0,
    'wonValue' => 0,
    'wonCases' => 0,
    'avgDealSize' => 0,
    'avgSalesCycle' => 0
];

if ($currentMonthSales) {
    foreach ($currentMonthSales as $sale) {
        $currentMonthData['totalCases']++;
        $currentMonthData['totalValue'] += $sale->salesCaseEstimate ?: 0;
        
        if ($sale->saleStage == 'won' || $sale->saleStage == 'closed_won') {
            $currentMonthData['wonValue'] += $sale->salesCaseEstimate ?: 0;
            $currentMonthData['wonCases']++;
        }
    }
    
    if ($currentMonthData['totalCases'] > 0) {
        $currentMonthData['avgDealSize'] = round($currentMonthData['totalValue'] / $currentMonthData['totalCases'], 2);
    }
}

// Calculate win rate
$winRate = $currentMonthData['totalCases'] > 0 ? round(($currentMonthData['wonCases'] / $currentMonthData['totalCases']) * 100, 1) : 0;

// Calculate monthly growth (simplified - you'd need historical data for accurate calculation)
$monthlyGrowth = 0; // Placeholder for growth calculation

// Get sales by probability ranges
$probabilityRanges = [
    '0-25' => ['count' => 0, 'value' => 0],
    '26-50' => ['count' => 0, 'value' => 0],
    '51-75' => ['count' => 0, 'value' => 0],
    '76-100' => ['count' => 0, 'value' => 0]
];

if ($currentMonthSales) {
    foreach ($currentMonthSales as $sale) {
        $probability = $sale->probability ?: 0;
        $value = $sale->salesCaseEstimate ?: 0;
        
        if ($probability <= 25) {
            $probabilityRanges['0-25']['count']++;
            $probabilityRanges['0-25']['value'] += $value;
        } elseif ($probability <= 50) {
            $probabilityRanges['26-50']['count']++;
            $probabilityRanges['26-50']['value'] += $value;
        } elseif ($probability <= 75) {
            $probabilityRanges['51-75']['count']++;
            $probabilityRanges['51-75']['value'] += $value;
        } else {
            $probabilityRanges['76-100']['count']++;
            $probabilityRanges['76-100']['value'] += $value;
        }
    }
}

// Get sales by business unit
$businessUnitData = [];
if ($currentMonthSales) {
    foreach ($currentMonthSales as $sale) {
        $unitName = $sale->businessUnitName ?: 'Unassigned';
        if (!isset($businessUnitData[$unitName])) {
            $businessUnitData[$unitName] = ['count' => 0, 'value' => 0, 'wonValue' => 0];
        }
        $businessUnitData[$unitName]['count']++;
        $businessUnitData[$unitName]['value'] += $sale->salesCaseEstimate ?: 0;
        
        if ($sale->saleStage == 'won' || $sale->saleStage == 'closed_won') {
            $businessUnitData[$unitName]['wonValue'] += $sale->salesCaseEstimate ?: 0;
        }
    }
}
?>

<!-- Advanced Sales Analytics Container -->
<div class="container-fluid">
    
    <!-- Key Performance Indicators Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-primary-subtle rounded">
                                <span class="avatar-title bg-primary-subtle text-primary rounded">
                                    <i class="ri-money-dollar-circle-line fs-20"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Total Pipeline</h6>
                            <h4 class="mb-0">KES <?= number_format($currentMonthData['totalValue'], 0) ?></h4>
                            <small class="text-muted"><?= $currentMonthData['totalCases'] ?> active cases</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-success-subtle rounded">
                                <span class="avatar-title bg-success-subtle text-success rounded">
                                    <i class="ri-trophy-line fs-20"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Won Value</h6>
                            <h4 class="mb-0">KES <?= number_format($currentMonthData['wonValue'], 0) ?></h4>
                            <small class="text-muted"><?= $currentMonthData['wonCases'] ?> won cases</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-info-subtle rounded">
                                <span class="avatar-title bg-info-subtle text-info rounded">
                                    <i class="ri-percent-line fs-20"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Win Rate</h6>
                            <h4 class="mb-0"><?= $winRate ?>%</h4>
                            <small class="text-muted">This month</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-warning-subtle rounded">
                                <span class="avatar-title bg-warning-subtle text-warning rounded">
                                    <i class="ri-line-chart-line fs-20"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Avg Deal Size</h6>
                            <h4 class="mb-0">KES <?= number_format($currentMonthData['avgDealSize'], 0) ?></h4>
                            <small class="text-muted">Per case</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="row mb-4">
        
        <!-- Probability Distribution Chart -->
        <div class="col-xl-6 col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-pie-chart-line me-2"></i>Sales by Probability Range
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="probabilityChart" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Business Unit Performance -->
        <div class="col-xl-6 col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-building-line me-2"></i>Performance by Business Unit
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="businessUnitChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Detailed Analytics Tables -->
    <div class="row">
        
        <!-- Probability Analysis Table -->
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-bar-chart-box-line me-2"></i>Probability Analysis
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Probability Range</th>
                                    <th>Cases</th>
                                    <th>Value</th>
                                    <th>Avg Deal Size</th>
                                    <th>% of Pipeline</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalPipelineValue = $currentMonthData['totalValue'];
                                foreach ($probabilityRanges as $range => $data): 
                                    $avgDealSize = $data['count'] > 0 ? round($data['value'] / $data['count'], 0) : 0;
                                    $percentage = $totalPipelineValue > 0 ? round(($data['value'] / $totalPipelineValue) * 100, 1) : 0;
                                ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-<?= $range == '76-100' ? 'success' : ($range == '51-75' ? 'info' : ($range == '26-50' ? 'warning' : 'secondary')) ?>-subtle text-<?= $range == '76-100' ? 'success' : ($range == '51-75' ? 'info' : ($range == '26-50' ? 'warning' : 'secondary')) ?>">
                                            <?= $range ?>%
                                        </span>
                                    </td>
                                    <td><?= $data['count'] ?></td>
                                    <td>KES <?= number_format($data['value'], 0) ?></td>
                                    <td>KES <?= number_format($avgDealSize, 0) ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress progress-sm flex-grow-1 me-2">
                                                <div class="progress-bar bg-primary" style="width: <?= $percentage ?>%"></div>
                                            </div>
                                            <span class="text-muted"><?= $percentage ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Business Unit Performance Table -->
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-building-2-line me-2"></i>Business Unit Performance
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Business Unit</th>
                                    <th>Cases</th>
                                    <th>Pipeline Value</th>
                                    <th>Won Value</th>
                                    <th>Win Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($businessUnitData as $unitName => $data): 
                                    $unitWinRate = $data['value'] > 0 ? round(($data['wonValue'] / $data['value']) * 100, 1) : 0;
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($unitName) ?></td>
                                    <td><span class="badge bg-primary-subtle text-primary"><?= $data['count'] ?></span></td>
                                    <td>KES <?= number_format($data['value'], 0) ?></td>
                                    <td>KES <?= number_format($data['wonValue'], 0) ?></td>
                                    <td>
                                        <span class="badge <?= $unitWinRate >= 50 ? 'bg-success-subtle text-success' : ($unitWinRate >= 25 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger') ?>">
                                            <?= $unitWinRate ?>%
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
    
    <!-- Sales Forecasting Widget -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-calendar-todo-line me-2"></i>Sales Forecasting
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-primary">KES <?= number_format($probabilityRanges['76-100']['value'], 0) ?></h4>
                                <p class="mb-0 text-muted">High Probability (76-100%)</p>
                                <small class="text-success">Likely to close</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-info">KES <?= number_format($probabilityRanges['51-75']['value'], 0) ?></h4>
                                <p class="mb-0 text-muted">Medium Probability (51-75%)</p>
                                <small class="text-info">Good potential</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-warning">KES <?= number_format($probabilityRanges['26-50']['value'] + $probabilityRanges['0-25']['value'], 0) ?></h4>
                                <p class="mb-0 text-muted">Low Probability (0-50%)</p>
                                <small class="text-warning">Needs attention</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Forecast Chart -->
                    <div class="mt-4">
                        <canvas id="forecastChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Probability Distribution Chart
    const probCtx = document.getElementById('probabilityChart').getContext('2d');
    const probabilityChart = new Chart(probCtx, {
        type: 'doughnut',
        data: {
            labels: ['0-25%', '26-50%', '51-75%', '76-100%'],
            datasets: [{
                data: [
                    <?= $probabilityRanges['0-25']['value'] ?>,
                    <?= $probabilityRanges['26-50']['value'] ?>,
                    <?= $probabilityRanges['51-75']['value'] ?>,
                    <?= $probabilityRanges['76-100']['value'] ?>
                ],
                backgroundColor: [
                    '#6c757d', // 0-25% - Secondary
                    '#ffc107', // 26-50% - Warning
                    '#17a2b8', // 51-75% - Info
                    '#28a745'  // 76-100% - Success
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
                            const percentage = ((value / total) * 100).toFixed(1);
                            return context.label + ': KES ' + value.toLocaleString() + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
    
    // Business Unit Chart
    const buCtx = document.getElementById('businessUnitChart').getContext('2d');
    const businessUnitChart = new Chart(buCtx, {
        type: 'bar',
        data: {
            labels: [<?php 
                $unitNames = array_keys($businessUnitData);
                echo "'" . implode("', '", array_map('addslashes', $unitNames)) . "'";
            ?>],
            datasets: [{
                label: 'Pipeline Value',
                data: [<?php 
                    $unitValues = array_column($businessUnitData, 'value');
                    echo implode(', ', $unitValues);
                ?>],
                backgroundColor: '#17a2b8',
                borderColor: '#17a2b8',
                borderWidth: 1
            }, {
                label: 'Won Value',
                data: [<?php 
                    $unitWonValues = array_column($businessUnitData, 'wonValue');
                    echo implode(', ', $unitWonValues);
                ?>],
                backgroundColor: '#28a745',
                borderColor: '#28a745',
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
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': KES ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    
    // Forecast Chart
    const forecastCtx = document.getElementById('forecastChart').getContext('2d');
    const forecastChart = new Chart(forecastCtx, {
        type: 'line',
        data: {
            labels: ['Current Month', 'Next Month (Forecast)', 'Month +2 (Forecast)', 'Month +3 (Forecast)'],
            datasets: [{
                label: 'Pipeline Value',
                data: [
                    <?= $currentMonthData['totalValue'] ?>,
                    <?= $probabilityRanges['76-100']['value'] + ($probabilityRanges['51-75']['value'] * 0.6) ?>,
                    <?= $probabilityRanges['76-100']['value'] * 0.8 + ($probabilityRanges['51-75']['value'] * 0.4) ?>,
                    <?= $probabilityRanges['76-100']['value'] * 0.6 + ($probabilityRanges['51-75']['value'] * 0.2) ?>
                ],
                borderColor: '#17a2b8',
                backgroundColor: 'rgba(23, 162, 184, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Won Value',
                data: [
                    <?= $currentMonthData['wonValue'] ?>,
                    <?= $probabilityRanges['76-100']['value'] * 0.9 ?>,
                    <?= $probabilityRanges['76-100']['value'] * 0.7 ?>,
                    <?= $probabilityRanges['76-100']['value'] * 0.5 ?>
                ],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
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
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': KES ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>
