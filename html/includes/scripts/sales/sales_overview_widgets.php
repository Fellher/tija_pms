<?php
/**
 * Sales Overview Widgets
 * High-level overview of sales performance and key metrics
 */

// Get sales data
$sales = Sales::sales_case_mid([
    'orgDataID' => $orgDataID,
    'entityID' => $entityID,
    'Suspended' => 'N'
], false, $DBConn);

// Calculate key metrics
$totalPipelineValue = 0;
$totalWonValue = 0;
$totalCases = 0;
$wonCases = 0;
$avgDealSize = 0;
$avgSalesCycle = 0;

// Process sales data
if ($sales) {
    foreach ($sales as $sale) {
        $totalCases++;
        $totalPipelineValue += $sale->salesCaseEstimate ?: 0;

        if ($sale->saleStage == 'won' || $sale->saleStage == 'closed_won') {
            $wonCases++;
            $totalWonValue += $sale->salesCaseEstimate ?: 0;
        }
    }

    if ($totalCases > 0) {
        $avgDealSize = round($totalPipelineValue / $totalCases, 2);
    }
}

// Calculate win rate
$winRate = $totalCases > 0 ? round(($wonCases / $totalCases) * 100, 1) : 0;

// Get sales by month (simplified - you'd need date filtering for accurate monthly data)
$monthlyData = [
    'current' => ['cases' => $totalCases, 'value' => $totalPipelineValue, 'won' => $totalWonValue],
    'previous' => ['cases' => 0, 'value' => 0, 'won' => 0] // Placeholder for previous month data
];

// Calculate growth (placeholder)
$growthRate = 0; // You'd calculate this based on historical data

// Get top performing sales people
$salesPersonPerformance = [];
if ($sales) {
    foreach ($sales as $sale) {
        $personName = $sale->salesPersonName ?: 'Unassigned';
        if (!isset($salesPersonPerformance[$personName])) {
            $salesPersonPerformance[$personName] = [
                'totalCases' => 0,
                'totalValue' => 0,
                'wonValue' => 0,
                'wonCases' => 0
            ];
        }

        $salesPersonPerformance[$personName]['totalCases']++;
        $salesPersonPerformance[$personName]['totalValue'] += $sale->salesCaseEstimate ?: 0;

        if ($sale->saleStage == 'won' || $sale->saleStage == 'closed_won') {
            $salesPersonPerformance[$personName]['wonCases']++;
            $salesPersonPerformance[$personName]['wonValue'] += $sale->salesCaseEstimate ?: 0;
        }
    }
}

// Sort by total value
uasort($salesPersonPerformance, function($a, $b) {
    return $b['totalValue'] - $a['totalValue'];
});

// Get recent activities (placeholder - you'd get this from activities table)
$recentActivities = Activity::activities(['activitySegment'=>'sales'], false, $DBConn);
// var_dump($recentActivities);

//filter activities for recent activities ie activities whose activityDate si within the last 30 days
$recentActivities = array_filter($recentActivities, function($activity) {
    return $activity->activityDate >= date('Y-m-d', strtotime('-30 days'));
});
// var_dump($recentActivities);





$activityTypes = Activity::activity_types_mini([], false, $DBConn);
// var_dump($activityTypes);
// $recentActivities = [
//    ['type' => 'Call', 'client' => 'Client A', 'date' => '2023-10-26', 'status' => 'Completed'],
//    ['type' => 'Meeting', 'client' => 'Client B', 'date' => '2023-10-25', 'status' => 'Scheduled'],
//    ['type' => 'Email', 'client' => 'Client C', 'date' => '2023-10-24', 'status' => 'Pending']
// ];
?>
<!-- Sales Overview Widgets -->
<div class="container-fluid">

    <!-- Key Metrics Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
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
                            <h4 class="mb-0">KES <?= number_format($totalPipelineValue, 0) ?></h4>
                            <small class="text-muted"><?= $totalCases ?> active cases</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
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
                            <h4 class="mb-0">KES <?= number_format($totalWonValue, 0) ?></h4>
                            <small class="text-muted"><?= $wonCases ?> won cases</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
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
                            <small class="text-muted">This period</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
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
                            <h4 class="mb-0">KES <?= number_format($avgDealSize, 0) ?></h4>
                            <small class="text-muted">Per case</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Tables Row -->
    <div class="row mb-4">

        <!-- Pipeline Overview Chart -->
        <div class="col-xl-8 col-lg-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-pie-chart-line me-2"></i>Sales Pipeline Overview
                        <button class="btn btn-sm btn-link text-muted p-0 ms-1" data-bs-toggle="tooltip" title="Breakdown of pipeline by status (won / active / lost) with weighted percentages.">
                            <i class="ri-question-line"></i>
                        </button>
                    </h5>
                    <div class="small text-muted">Use this doughnut to see how value is distributed across outcomes.</div>
                </div>
                <div class="card-body">
                    <canvas id="pipelineOverviewChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="col-xl-4 col-lg-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-calendar-todo-line me-2"></i>Recent Activities
                        <button class="btn btn-sm btn-link text-muted p-0 ms-1" data-bs-toggle="tooltip" title="Last 30 days of sales activities to gauge engagement momentum.">
                            <i class="ri-question-line"></i>
                        </button>
                    </h5>
                    <div class="small text-muted">Tracks meetings, calls, and emails logged in the last 30 days.</div>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentActivities as $activity): ?>
                        <div class="list-group-item px-0 py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?= $activity->activityTypeName ?></h6>
                                    <small class="text-muted"><?= $activity->clientName ?></small>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted"><?= $activity->activityDate ?></small>
                                    <br>
                                    <span class="badge bg-success-subtle text-success"><?= $activity->activityStatus ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Tables Row -->
    <div class="row">

        <!-- Top Sales People -->
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-user-star-line me-2"></i>Top Sales Performers
                        <button class="btn btn-sm btn-link text-muted p-0 ms-1" data-bs-toggle="tooltip" title="Ranks sellers by pipeline and won value to spotlight leaders and coverage.">
                            <i class="ri-question-line"></i>
                        </button>
                    </h5>
                    <div class="small text-muted">Sorts by total pipeline; includes cases, won value, and win rate for each seller.</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Sales Person</th>
                                    <th>Cases</th>
                                    <th>Pipeline Value</th>
                                    <th>Won Value</th>
                                    <th>Win Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $topPerformers = array_slice($salesPersonPerformance, 0, 5, true);
                                foreach ($topPerformers as $personName => $data):
                                    $personWinRate = $data['totalCases'] > 0 ? round(($data['wonCases'] / $data['totalCases']) * 100, 1) : 0;
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary-subtle rounded me-2">
                                                <span class="avatar-title bg-primary-subtle text-primary rounded">
                                                    <?= strtoupper(substr($personName, 0, 2)) ?>
                                                </span>
                                            </div>
                                            <span><?= htmlspecialchars($personName) ?></span>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-primary-subtle text-primary"><?= $data['totalCases'] ?></span></td>
                                    <td>KES <?= number_format($data['totalValue'], 0) ?></td>
                                    <td>KES <?= number_format($data['wonValue'], 0) ?></td>
                                    <td>
                                        <span class="badge <?= $personWinRate >= 50 ? 'bg-success-subtle text-success' : ($personWinRate >= 25 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger') ?>">
                                            <?= $personWinRate ?>%
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

        <!-- Sales Goals Progress -->
        <div class="col-xl-6 col-lg-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-target-line me-2"></i>Sales Goals Progress
                        <button class="btn btn-sm btn-link text-muted p-0 ms-1" data-bs-toggle="tooltip" title="Progress against revenue, volume, and win-rate targets for the period.">
                            <i class="ri-question-line"></i>
                        </button>
                    </h5>
                    <div class="small text-muted">Track how close you are to revenue, case volume, and win-rate goals.</div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Monthly Revenue Target</span>
                            <span class="fw-semibold">KES <?= number_format($totalWonValue, 0) ?> / KES 1,000,000</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-success" style="width: <?= min(($totalWonValue / 1000000) * 100, 100) ?>%"></div>
                        </div>
                        <small class="text-muted"><?= round(($totalWonValue / 1000000) * 100, 1) ?>% of target</small>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Cases Target</span>
                            <span class="fw-semibold"><?= $totalCases ?> / 50</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-info" style="width: <?= min(($totalCases / 50) * 100, 100) ?>%"></div>
                        </div>
                        <small class="text-muted"><?= round(($totalCases / 50) * 100, 1) ?>% of target</small>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Win Rate Target</span>
                            <span class="fw-semibold"><?= $winRate ?>% / 60%</span>
                        </div>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-warning" style="width: <?= min(($winRate / 60) * 100, 100) ?>%"></div>
                        </div>
                        <small class="text-muted"><?= round(($winRate / 60) * 100, 1) ?>% of target</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pipeline Overview Chart
    const ctx = document.getElementById('pipelineOverviewChart').getContext('2d');
    const pipelineChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Won Sales', 'Active Pipeline', 'Lost Sales'],
            datasets: [{
                data: [
                    <?= $totalWonValue ?>,
                    <?= $totalPipelineValue - $totalWonValue ?>,
                    <?= 0 ?> // You'd calculate lost sales from your data
                ],
                backgroundColor: [
                    '#28a745', // Won - Success
                    '#17a2b8', // Active - Info
                    '#dc3545'  // Lost - Danger
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
});
</script>
