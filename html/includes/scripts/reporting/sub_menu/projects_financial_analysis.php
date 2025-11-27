<?php
/**
 * Projects Financial Analysis Report
 * Comprehensive analysis of project profitability, costs, and financial performance
 */

// Get projects data with financial information
$projects = Projects::projects_full([], false, $DBConn);
$billingRates = Projects::billing_rates([], false, $DBConn);

// Initialize financial metrics
$financialMetrics = [
    'total_projects' => 0,
    'active_projects' => 0,
    'completed_projects' => 0,
    'total_project_value' => 0,
    'total_billable_value' => 0,
    'total_costs' => 0,
    'profit_margin' => 0,
    'average_project_value' => 0,
    'projects_by_status' => [],
    'projects_by_business_unit' => [],
    'projects_by_client' => [],
    'profitability_analysis' => []
];

// Process projects data
if ($projects) {
    foreach ($projects as $project) {
        $financialMetrics['total_projects']++;
        $financialMetrics['total_project_value'] += $project->projectValue ?? 0;
        
        // Count by status
        $status = $project->projectStatus ?? 'Unknown';
        if (!isset($financialMetrics['projects_by_status'][$status])) {
            $financialMetrics['projects_by_status'][$status] = 0;
        }
        $financialMetrics['projects_by_status'][$status]++;
        
        // Count by business unit
        $businessUnit = $project->businessUnitName ?? 'Unknown';
        if (!isset($financialMetrics['projects_by_business_unit'][$businessUnit])) {
            $financialMetrics['projects_by_business_unit'][$businessUnit] = [
                'count' => 0,
                'value' => 0,
                'billable_value' => 0
            ];
        }
        $financialMetrics['projects_by_business_unit'][$businessUnit]['count']++;
        $financialMetrics['projects_by_business_unit'][$businessUnit]['value'] += $project->projectValue ?? 0;
        $financialMetrics['projects_by_business_unit'][$businessUnit]['billable_value'] += $project->billableRateValue ?? 0;
        
        // Count by client
        $client = $project->clientName ?? 'Unknown';
        if (!isset($financialMetrics['projects_by_client'][$client])) {
            $financialMetrics['projects_by_client'][$client] = [
                'count' => 0,
                'value' => 0,
                'billable_value' => 0
            ];
        }
        $financialMetrics['projects_by_client'][$client]['count']++;
        $financialMetrics['projects_by_client'][$client]['value'] += $project->projectValue ?? 0;
        $financialMetrics['projects_by_client'][$client]['billable_value'] += $project->billableRateValue ?? 0;
        
        // Determine project status
        if ($status === 'Active' || $status === 'In Progress') {
            $financialMetrics['active_projects']++;
        } elseif ($status === 'Completed' || $status === 'Closed') {
            $financialMetrics['completed_projects']++;
        }
        
        // Calculate billable value
        if ($project->billable === 'Y' || $project->billable === 'Yes') {
            $financialMetrics['total_billable_value'] += $project->billableRateValue ?? 0;
        }
        
        // Profitability analysis
        $projectValue = $project->projectValue ?? 0;
        $billableValue = $project->billableRateValue ?? 0;
        $profitability = $projectValue > 0 ? (($billableValue / $projectValue) * 100) : 0;
        
        $financialMetrics['profitability_analysis'][] = [
            'project' => $project,
            'profitability_percentage' => $profitability,
            'profit_amount' => $billableValue - $projectValue
        ];
    }
}

// Calculate derived metrics
$financialMetrics['average_project_value'] = $financialMetrics['total_projects'] > 0 ? 
    $financialMetrics['total_project_value'] / $financialMetrics['total_projects'] : 0;

$financialMetrics['profit_margin'] = $financialMetrics['total_project_value'] > 0 ? 
    (($financialMetrics['total_billable_value'] - $financialMetrics['total_project_value']) / $financialMetrics['total_project_value']) * 100 : 0;

// Sort profitability analysis by profitability percentage
usort($financialMetrics['profitability_analysis'], function($a, $b) {
    return $b['profitability_percentage'] <=> $a['profitability_percentage'];
});

?>

<div class="container-fluid">
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
                            <h4 class="mb-0"><?= $financialMetrics['total_projects'] ?></h4>
                            <small class="text-muted">Active: <?= $financialMetrics['active_projects'] ?> | Completed: <?= $financialMetrics['completed_projects'] ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-white shadow">
                <div class="card-header bg-success-subtle">
                    <h6 class="mb-0 text-success">
                        <i class="ri-money-dollar-circle-line me-2"></i>Total Project Value
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
                            <h4 class="mb-0">KES <?= number_format($financialMetrics['total_project_value'], 0) ?></h4>
                            <small class="text-muted">Avg: KES <?= number_format($financialMetrics['average_project_value'], 0) ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-white shadow">
                <div class="card-header bg-info-subtle">
                    <h6 class="mb-0 text-info">
                        <i class="ri-bill-line me-2"></i>Billable Value
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-info-subtle rounded">
                                <span class="avatar-title bg-info-subtle text-info rounded">
                                    <i class="ri-bill-line fs-20"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0">KES <?= number_format($financialMetrics['total_billable_value'], 0) ?></h4>
                            <small class="text-muted"><?= $financialMetrics['total_projects'] > 0 ? round(($financialMetrics['total_billable_value'] / $financialMetrics['total_project_value']) * 100, 1) : 0 ?>% of total value</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-white shadow">
                <div class="card-header bg-warning-subtle">
                    <h6 class="mb-0 text-warning">
                        <i class="ri-trending-up-line me-2"></i>Profit Margin
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-warning-subtle rounded">
                                <span class="avatar-title bg-warning-subtle text-warning rounded">
                                    <i class="ri-trending-up-line fs-20"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0"><?= number_format($financialMetrics['profit_margin'], 1) ?>%</h4>
                            <small class="text-muted">KES <?= number_format($financialMetrics['total_billable_value'] - $financialMetrics['total_project_value'], 0) ?> profit</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects by Business Unit -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-building-line me-2"></i>Projects by Business Unit
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Business Unit</th>
                                    <th>Projects Count</th>
                                    <th>Total Value</th>
                                    <th>Billable Value</th>
                                    <th>Profit Margin</th>
                                    <th>Avg Project Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($financialMetrics['projects_by_business_unit'] as $unit => $data): ?>
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
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary"><?= $data['count'] ?></span>
                                    </td>
                                    <td>KES <?= number_format($data['value'], 0) ?></td>
                                    <td>KES <?= number_format($data['billable_value'], 0) ?></td>
                                    <td>
                                        <?php 
                                        $margin = $data['value'] > 0 ? (($data['billable_value'] - $data['value']) / $data['value']) * 100 : 0;
                                        $badgeClass = $margin >= 20 ? 'bg-success-subtle text-success' : ($margin >= 10 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger');
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= number_format($margin, 1) ?>%</span>
                                    </td>
                                    <td>KES <?= number_format($data['count'] > 0 ? $data['value'] / $data['count'] : 0, 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performing Projects -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-trophy-line me-2"></i>Top Performing Projects by Profitability
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Project Name</th>
                                    <th>Client</th>
                                    <th>Business Unit</th>
                                    <th>Project Value</th>
                                    <th>Billable Value</th>
                                    <th>Profit Amount</th>
                                    <th>Profitability %</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $topProjects = array_slice($financialMetrics['profitability_analysis'], 0, 10);
                                foreach ($topProjects as $index => $projectData): 
                                    $project = $projectData['project'];
                                ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary">#<?= $index + 1 ?></span>
                                    </td>
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
                                    <td><?= htmlspecialchars($project->businessUnitName) ?></td>
                                    <td>KES <?= number_format($project->projectValue ?? 0, 0) ?></td>
                                    <td>KES <?= number_format($project->billableRateValue ?? 0, 0) ?></td>
                                    <td>
                                        <span class="<?= $projectData['profit_amount'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                            KES <?= number_format($projectData['profit_amount'], 0) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $badgeClass = $projectData['profitability_percentage'] >= 20 ? 'bg-success-subtle text-success' : 
                                                    ($projectData['profitability_percentage'] >= 10 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger');
                                        ?>
                                        <span class="badge <?= $badgeClass ?>"><?= number_format($projectData['profitability_percentage'], 1) ?>%</span>
                                    </td>
                                    <td>
                                        <?php 
                                        $statusClass = $project->projectStatus === 'Active' ? 'bg-success-subtle text-success' : 
                                                     ($project->projectStatus === 'Completed' ? 'bg-info-subtle text-info' : 'bg-secondary-subtle text-secondary');
                                        ?>
                                        <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($project->projectStatus) ?></span>
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

    <!-- Projects by Status Chart -->
    <div class="row mb-4">
        <div class="col-xl-6 col-md-12 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-pie-chart-line me-2"></i>Projects by Status
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="projectsStatusChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-md-12 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-bar-chart-line me-2"></i>Business Unit Performance
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="businessUnitChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Projects Status Chart
    const statusCtx = document.getElementById('projectsStatusChart');
    if (statusCtx) {
        const statusData = <?= json_encode($financialMetrics['projects_by_status']) ?>;
        const statusLabels = Object.keys(statusData);
        const statusValues = Object.values(statusData);
        
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusValues,
                    backgroundColor: [
                        '#28a745', // Active - Green
                        '#17a2b8', // Completed - Info
                        '#ffc107', // In Progress - Warning
                        '#dc3545', // On Hold - Danger
                        '#6c757d'  // Other - Secondary
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
                                return context.label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Business Unit Chart
    const unitCtx = document.getElementById('businessUnitChart');
    if (unitCtx) {
        const unitData = <?= json_encode($financialMetrics['projects_by_business_unit']) ?>;
        const unitLabels = Object.keys(unitData);
        const unitValues = unitLabels.map(label => unitData[label]['value']);
        
        new Chart(unitCtx, {
            type: 'bar',
            data: {
                labels: unitLabels,
                datasets: [{
                    label: 'Project Value (KES)',
                    data: unitValues,
                    backgroundColor: '#17a2b8',
                    borderColor: '#17a2b8',
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
                                return 'Value: KES ' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
