<?php
/**
 * Project Productivity Analysis Dashboard
 * Comprehensive productivity tracking and analysis with visualizations
 *
 * @package Tija Practice Management System
 * @subpackage Projects - Productivity
 * @version 3.0.0
 */

// Get date range filters (default to last 30 days)
$dateFrom = isset($_GET['dateFrom']) ? Utility::clean_string($_GET['dateFrom']) : date('Y-m-d', strtotime('-30 days'));
$dateTo = isset($_GET['dateTo']) ? Utility::clean_string($_GET['dateTo']) : date('Y-m-d');

// Get project details
$projectDetails = Projects::projects_mini(['projectID' => $projectID], true, $DBConn);

// Get time logs for the project within date range
$timeLogs = TimeAttendance::project_tasks_time_logs_between_dates(
    [
        'projectID' => $projectID,
        'Suspended' => 'N',
        'Lapsed' => 'N'
    ],
    $dateFrom,
    $dateTo,
    false,
    $DBConn
);

// Get project tasks and phases
$allProjectTasks = Projects::projects_tasks(['projectID' => $projectID], false, $DBConn);
$projectPhases = Projects::project_phases(['projectID' => $projectID], false, $DBConn);
$projectAssignedTasks = Projects::task_user_assignment(['projectID' => $projectID], false, $DBConn);
$subtasks = Projects::project_subtasks_full(['projectID' => $projectID], false, $DBConn);

// Get team members
$teamMembers = [];
if ($projectAssignedTasks) {
    foreach ($projectAssignedTasks as $task) {
        if (!in_array($task->userID, $teamMembers)) {
            $teamMembers[] = $task->userID;
        }
    }
}
if ($subtasks) {
    foreach ($subtasks as $task) {
        if (!in_array($task->assignee, $teamMembers)) {
            $teamMembers[] = (int)$task->assignee;
        }
    }
}
$teamMembers = array_unique($teamMembers);

// Calculate productivity metrics
$productivityMetrics = [
    'totalHours' => 0,
    'billableHours' => 0,
    'nonBillableHours' => 0,
    'totalValue' => 0,
    'billableValue' => 0,
    'teamMemberStats' => [],
    'phaseStats' => [],
    'taskStats' => [],
    'dailyTrends' => [],
    'workTypeStats' => []
];

// Process time logs
if ($timeLogs && is_array($timeLogs)) {
    foreach ($timeLogs as $log) {
        // Calculate hours
        $hours = 0;
        if (isset($log->workHours) && $log->workHours > 0) {
            $hours = floatval($log->workHours);
        } elseif (isset($log->taskDuration)) {
            // Parse duration (HH:MM format)
            $durationParts = explode(':', $log->taskDuration);
            if (count($durationParts) >= 2) {
                $hours = floatval($durationParts[0]) + (floatval($durationParts[1]) / 60);
            }
        } elseif (isset($log->startTime) && isset($log->endTime)) {
            $start = strtotime($log->startTime);
            $end = strtotime($log->endTime);
            $hours = ($end - $start) / 3600;
        }

        if ($hours <= 0) continue;

        $isBillable = (isset($log->billable) && $log->billable == 'Y') ||
                     (isset($projectDetails->billable) && $projectDetails->billable == 'Y');

        $employeeID = $log->employeeID ?? null;
        $workTypeID = $log->workTypeID ?? null;
        $phaseID = $log->projectPhaseID ?? null;
        $taskID = $log->projectTaskID ?? null;
        $taskDate = $log->taskDate ?? date('Y-m-d');

        // Calculate value (if billable)
        $rate = 0;
        if ($isBillable) {
            $rate = isset($log->billableRateValue) ? floatval($log->billableRateValue) :
                   (isset($projectDetails->billableRateValue) ? floatval($projectDetails->billableRateValue) : 0);
        }
        $value = $hours * $rate;

        // Update totals
        $productivityMetrics['totalHours'] += $hours;
        $productivityMetrics['totalValue'] += $value;

        if ($isBillable) {
            $productivityMetrics['billableHours'] += $hours;
            $productivityMetrics['billableValue'] += $value;
        } else {
            $productivityMetrics['nonBillableHours'] += $hours;
        }

        // Team member stats
        if ($employeeID) {
            if (!isset($productivityMetrics['teamMemberStats'][$employeeID])) {
                $productivityMetrics['teamMemberStats'][$employeeID] = [
                    'totalHours' => 0,
                    'billableHours' => 0,
                    'nonBillableHours' => 0,
                    'totalValue' => 0,
                    'billableValue' => 0,
                    'taskCount' => 0
                ];
            }
            $productivityMetrics['teamMemberStats'][$employeeID]['totalHours'] += $hours;
            $productivityMetrics['teamMemberStats'][$employeeID]['totalValue'] += $value;
            $productivityMetrics['teamMemberStats'][$employeeID]['taskCount']++;

            if ($isBillable) {
                $productivityMetrics['teamMemberStats'][$employeeID]['billableHours'] += $hours;
                $productivityMetrics['teamMemberStats'][$employeeID]['billableValue'] += $value;
            } else {
                $productivityMetrics['teamMemberStats'][$employeeID]['nonBillableHours'] += $hours;
            }
        }

        // Daily trends
        if (!isset($productivityMetrics['dailyTrends'][$taskDate])) {
            $productivityMetrics['dailyTrends'][$taskDate] = [
                'hours' => 0,
                'billableHours' => 0,
                'value' => 0
            ];
        }
        $productivityMetrics['dailyTrends'][$taskDate]['hours'] += $hours;
        $productivityMetrics['dailyTrends'][$taskDate]['value'] += $value;
        if ($isBillable) {
            $productivityMetrics['dailyTrends'][$taskDate]['billableHours'] += $hours;
        }

        // Phase stats
        if ($phaseID) {
            if (!isset($productivityMetrics['phaseStats'][$phaseID])) {
                $productivityMetrics['phaseStats'][$phaseID] = [
                    'hours' => 0,
                    'value' => 0,
                    'name' => $log->projectPhaseName ?? 'Unknown Phase'
                ];
            }
            $productivityMetrics['phaseStats'][$phaseID]['hours'] += $hours;
            $productivityMetrics['phaseStats'][$phaseID]['value'] += $value;
        }

        // Work type stats
        if ($workTypeID) {
            if (!isset($productivityMetrics['workTypeStats'][$workTypeID])) {
                $productivityMetrics['workTypeStats'][$workTypeID] = [
                    'hours' => 0,
                    'value' => 0,
                    'name' => $log->workTypeName ?? 'Unknown Work Type'
                ];
            }
            $productivityMetrics['workTypeStats'][$workTypeID]['hours'] += $hours;
            $productivityMetrics['workTypeStats'][$workTypeID]['value'] += $value;
        }
    }
}

// Calculate productivity percentage
$productivityPercentage = $productivityMetrics['totalHours'] > 0
    ? round(($productivityMetrics['billableHours'] / $productivityMetrics['totalHours']) * 100, 1)
    : 0;

// Calculate average hours per team member
$avgHoursPerMember = count($teamMembers) > 0
    ? round($productivityMetrics['totalHours'] / count($teamMembers), 2)
    : 0;

// Sort team member stats by total hours (descending)
uasort($productivityMetrics['teamMemberStats'], function($a, $b) {
    return $b['totalHours'] <=> $a['totalHours'];
});
?>

<div class="container-fluid my-3" id="projectProductivityContainer">
    <!-- Header with Filters -->
    <div class="card custom-card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="card-title mb-0">
                    <i class="ri-bar-chart-line me-2 text-primary"></i>Project Productivity Analysis
                </h4>
                <p class="text-muted mb-0 small">Track team performance, hours, and productivity metrics</p>
            </div>
            <div class="d-flex gap-2">
                <div class="input-group input-group-sm" style="width: 200px;">
                    <span class="input-group-text"><i class="ri-calendar-line"></i></span>
                    <input type="date" class="form-control" id="dateFrom" value="<?= htmlspecialchars($dateFrom) ?>">
                </div>
                <div class="input-group input-group-sm" style="width: 200px;">
                    <span class="input-group-text"><i class="ri-calendar-line"></i></span>
                    <input type="date" class="form-control" id="dateTo" value="<?= htmlspecialchars($dateTo) ?>">
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="updateProductivityFilters()">
                    <i class="ri-filter-line me-1"></i>Apply Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Hours</h6>
                            <h3 class="mb-0"><?= number_format($productivityMetrics['totalHours'], 1) ?></h3>
                            <small class="text-muted">All logged hours</small>
                        </div>
                        <div class="ms-3">
                            <i class="ri-time-line text-primary" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Billable Hours</h6>
                            <h3 class="mb-0"><?= number_format($productivityMetrics['billableHours'], 1) ?></h3>
                            <small class="text-muted">
                                <?= $productivityPercentage ?>% of total
                            </small>
                        </div>
                        <div class="ms-3">
                            <i class="ri-money-dollar-circle-line text-success" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-success" role="progressbar"
                             style="width: <?= $productivityPercentage ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Value</h6>
                            <h3 class="mb-0"><?= Utility::formatToCurrency($productivityMetrics['totalValue']) ?></h3>
                            <small class="text-muted">Billable work value</small>
                        </div>
                        <div class="ms-3">
                            <i class="ri-wallet-line text-info" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Avg Hours/Member</h6>
                            <h3 class="mb-0"><?= number_format($avgHoursPerMember, 1) ?></h3>
                            <small class="text-muted"><?= count($teamMembers) ?> team members</small>
                        </div>
                        <div class="ms-3">
                            <i class="ri-user-line text-warning" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Team Member Productivity -->
        <div class="col-lg-8">
            <div class="card custom-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-team-line me-2"></i>Team Member Productivity
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($productivityMetrics['teamMemberStats'])): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Team Member</th>
                                        <th class="text-end">Total Hours</th>
                                        <th class="text-end">Billable Hours</th>
                                        <th class="text-end">Non-Billable</th>
                                        <th class="text-end">Value</th>
                                        <th class="text-center">Tasks</th>
                                        <th class="text-center">Productivity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $totalTeamHours = 0;
                                    foreach ($productivityMetrics['teamMemberStats'] as $memberID => $stats):
                                        $userName = Core::user_name($memberID, $DBConn);
                                        $memberProductivity = $stats['totalHours'] > 0
                                            ? round(($stats['billableHours'] / $stats['totalHours']) * 100, 1)
                                            : 0;
                                        $totalTeamHours += $stats['totalHours'];
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center">
                                                        <?= strtoupper(substr($userName, 0, 1)) ?>
                                                    </div>
                                                    <strong><?= htmlspecialchars($userName) ?></strong>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <strong><?= number_format($stats['totalHours'], 1) ?></strong>
                                            </td>
                                            <td class="text-end">
                                                <span class="badge bg-success"><?= number_format($stats['billableHours'], 1) ?></span>
                                            </td>
                                            <td class="text-end">
                                                <span class="badge bg-secondary"><?= number_format($stats['nonBillableHours'], 1) ?></span>
                                            </td>
                                            <td class="text-end">
                                                <strong class="text-success"><?= Utility::formatToCurrency($stats['billableValue']) ?></strong>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info"><?= $stats['taskCount'] ?></span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex align-items-center justify-content-center">
                                                    <div class="progress" style="width: 80px; height: 20px;">
                                                        <div class="progress-bar <?= $memberProductivity >= 80 ? 'bg-success' : ($memberProductivity >= 50 ? 'bg-warning' : 'bg-danger') ?>"
                                                             role="progressbar"
                                                             style="width: <?= $memberProductivity ?>%">
                                                            <?= $memberProductivity ?>%
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th>Total</th>
                                        <th class="text-end"><?= number_format($productivityMetrics['totalHours'], 1) ?></th>
                                        <th class="text-end"><?= number_format($productivityMetrics['billableHours'], 1) ?></th>
                                        <th class="text-end"><?= number_format($productivityMetrics['nonBillableHours'], 1) ?></th>
                                        <th class="text-end"><?= Utility::formatToCurrency($productivityMetrics['billableValue']) ?></th>
                                        <th class="text-center">-</th>
                                        <th class="text-center"><?= $productivityPercentage ?>%</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="ri-inbox-line text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">No productivity data available for the selected period</p>
                            <small class="text-muted">Time logs will appear here once team members start logging hours</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Productivity Insights -->
        <div class="col-lg-4">
            <div class="card custom-card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-lightbulb-line me-2"></i>Productivity Insights
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                    $insights = [];

                    if ($productivityPercentage >= 80) {
                        $insights[] = ['type' => 'success', 'icon' => 'ri-checkbox-circle-line', 'text' => 'Excellent billable ratio! Over 80% of hours are billable.'];
                    } elseif ($productivityPercentage >= 50) {
                        $insights[] = ['type' => 'warning', 'icon' => 'ri-alert-line', 'text' => 'Moderate billable ratio. Consider reviewing non-billable activities.'];
                    } else {
                        $insights[] = ['type' => 'danger', 'icon' => 'ri-error-warning-line', 'text' => 'Low billable ratio. Focus on increasing billable work.'];
                    }

                    if ($avgHoursPerMember > 40) {
                        $insights[] = ['type' => 'info', 'icon' => 'ri-time-line', 'text' => 'High average hours per team member. Monitor workload distribution.'];
                    }

                    if (count($productivityMetrics['teamMemberStats']) > 0) {
                        $topPerformer = array_key_first($productivityMetrics['teamMemberStats']);
                        $topStats = $productivityMetrics['teamMemberStats'][$topPerformer];
                        $topName = Core::user_name($topPerformer, $DBConn);
                        $insights[] = ['type' => 'success', 'icon' => 'ri-star-line', 'text' => "Top performer: {$topName} with " . number_format($topStats['totalHours'], 1) . " hours logged."];
                    }
                    ?>

                    <?php if (!empty($insights)): ?>
                        <?php foreach ($insights as $insight): ?>
                            <div class="alert alert-<?= $insight['type'] ?> d-flex align-items-start mb-2">
                                <i class="<?= $insight['icon'] ?> me-2 mt-1"></i>
                                <small class="mb-0"><?= htmlspecialchars($insight['text']) ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted small mb-0">No insights available yet. Start logging time to see productivity insights.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Phase Distribution -->
            <?php if (!empty($productivityMetrics['phaseStats'])): ?>
            <div class="card custom-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-folder-chart-line me-2"></i>Hours by Phase
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                    $phaseTotalHours = array_sum(array_column($productivityMetrics['phaseStats'], 'hours'));
                    foreach ($productivityMetrics['phaseStats'] as $phaseID => $phaseData):
                        $phasePercentage = $phaseTotalHours > 0
                            ? round(($phaseData['hours'] / $phaseTotalHours) * 100, 1)
                            : 0;
                    ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small"><?= htmlspecialchars($phaseData['name']) ?></span>
                                <span class="small fw-bold"><?= number_format($phaseData['hours'], 1) ?>h</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" role="progressbar"
                                     style="width: <?= $phasePercentage ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mt-3">
        <!-- Daily Trends Chart -->
        <div class="col-lg-6">
            <div class="card custom-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-line-chart-line me-2"></i>Daily Hours Trend
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="dailyTrendsChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Work Type Distribution -->
        <div class="col-lg-6">
            <div class="card custom-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-pie-chart-line me-2"></i>Hours by Work Type
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="workTypeChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
(function() {
    'use strict';

    // Update filters function
    window.updateProductivityFilters = function() {
        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo = document.getElementById('dateTo').value;
        const url = new URL(window.location);
        url.searchParams.set('dateFrom', dateFrom);
        url.searchParams.set('dateTo', dateTo);
        window.location.href = url.toString();
    };

    // Daily Trends Chart
    const dailyTrendsData = <?= json_encode($productivityMetrics['dailyTrends']) ?>;
    const dailyLabels = Object.keys(dailyTrendsData).sort();
    const dailyHours = dailyLabels.map(date => dailyTrendsData[date].hours);
    const dailyBillableHours = dailyLabels.map(date => dailyTrendsData[date].billableHours);

    if (dailyLabels.length > 0) {
        const dailyTrendsCtx = document.getElementById('dailyTrendsChart');
        if (dailyTrendsCtx) {
            new Chart(dailyTrendsCtx, {
                type: 'line',
                data: {
                    labels: dailyLabels,
                    datasets: [{
                        label: 'Total Hours',
                        data: dailyHours,
                        borderColor: 'rgb(13, 110, 253)',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Billable Hours',
                        data: dailyBillableHours,
                        borderColor: 'rgb(25, 135, 84)',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value + 'h';
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    // Work Type Distribution Chart
    const workTypeData = <?= json_encode($productivityMetrics['workTypeStats']) ?>;
    const workTypeLabels = Object.values(workTypeData).map(wt => wt.name);
    const workTypeHours = Object.values(workTypeData).map(wt => wt.hours);

    if (workTypeLabels.length > 0) {
        const workTypeCtx = document.getElementById('workTypeChart');
        if (workTypeCtx) {
            new Chart(workTypeCtx, {
                type: 'doughnut',
                data: {
                    labels: workTypeLabels,
                    datasets: [{
                        data: workTypeHours,
                        backgroundColor: [
                            'rgba(13, 110, 253, 0.8)',
                            'rgba(25, 135, 84, 0.8)',
                            'rgba(255, 193, 7, 0.8)',
                            'rgba(220, 53, 69, 0.8)',
                            'rgba(13, 202, 240, 0.8)',
                            'rgba(108, 117, 125, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return `${label}: ${value.toFixed(1)}h (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
    }
})();
</script>

<style>
#projectProductivityContainer .card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

#projectProductivityContainer .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

#projectProductivityContainer .avatar {
    width: 32px;
    height: 32px;
    font-size: 0.875rem;
    font-weight: 600;
}

#projectProductivityContainer .table th {
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    color: #6c757d;
    border-bottom: 2px solid #dee2e6;
}

#projectProductivityContainer .table td {
    vertical-align: middle;
}

#projectProductivityContainer .progress {
    border-radius: 0.5rem;
}

#projectProductivityContainer .alert {
    font-size: 0.875rem;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
}
</style>
