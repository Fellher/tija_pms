<?php
/**
 * Leave Analytics View
 *
 * Comprehensive analytics dashboard for leave management with
 * charts, metrics, and insights for both employees and managers
 */

// Get analytics data
$leaveAnalytics = Leave::get_leave_analytics($userDetails->ID, $DBConn);
$teamAnalytics = [];

// Check if user is a manager
$isManager = Employee::is_manager($userDetails->ID, $DBConn);
if ($isManager) {
    $teamAnalytics = Leave::get_team_leave_analytics($userDetails->ID, $DBConn, $hrManagerScope ?? null);
}

// Get time period for analytics
$timePeriod = isset($_GET['period']) ? Utility::clean_string($_GET['period']) : 'year';
$validPeriods = ['month', 'quarter', 'year'];
if (!in_array($timePeriod, $validPeriods)) {
    $timePeriod = 'year';
}

// Get filtered analytics data
$filteredAnalytics = Leave::get_filtered_analytics($userDetails->ID, $timePeriod, $DBConn);
?>

<!-- Analytics View Container -->
<div class="row">
    <!-- Analytics Controls -->
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="ri-bar-chart-line me-2 text-primary"></i>
                        Leave Analytics Dashboard
                    </h6>
                    <div class="d-flex gap-2">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button"
                                    class="btn <?= $timePeriod == 'month' ? 'btn-primary' : 'btn-outline-primary' ?>"
                                    data-action="change-analytics-period"
                                    data-period="month">
                                Month
                            </button>
                            <button type="button"
                                    class="btn <?= $timePeriod == 'quarter' ? 'btn-primary' : 'btn-outline-primary' ?>"
                                    data-action="change-analytics-period"
                                    data-period="quarter">
                                Quarter
                            </button>
                            <button type="button"
                                    class="btn <?= $timePeriod == 'year' ? 'btn-primary' : 'btn-outline-primary' ?>"
                                    data-action="change-analytics-period"
                                    data-period="year">
                                Year
                            </button>
                        </div>
                        <button type="button" class="btn btn-outline-success btn-sm" data-action="export-analytics">
                            <i class="ri-download-line me-1"></i>Export
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="col-12 mb-4">
        <div class="row">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="metric-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="ri-calendar-check-line fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">Total Leave Days</h6>
                                <h4 class="mb-0 fw-bold"><?= $leaveAnalytics['totalLeaveDays'] ?? 0 ?></h4>
                                <small class="text-primary">
                                    <i class="ri-trending-up-line"></i>
                                    <?= $timePeriod ?>ly usage
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="metric-icon bg-success bg-opacity-10 text-success">
                                    <i class="ri-percent-line fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">Leave Utilization</h6>
                                <h4 class="mb-0 fw-bold"><?= $leaveAnalytics['leaveUtilization'] ?? 0 ?>%</h4>
                                <small class="text-success">
                                    <i class="ri-information-line"></i>
                                    Of annual entitlement
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="metric-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="ri-time-line fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">Average Duration</h6>
                                <h4 class="mb-0 fw-bold"><?= $leaveAnalytics['averageDuration'] ?? 0 ?></h4>
                                <small class="text-warning">
                                    <i class="ri-calendar-line"></i>
                                    Days per application
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="metric-icon bg-info bg-opacity-10 text-info">
                                    <i class="ri-calendar-todo-line fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">Applications</h6>
                                <h4 class="mb-0 fw-bold"><?= $leaveAnalytics['totalApplications'] ?? 0 ?></h4>
                                <small class="text-info">
                                    <i class="ri-file-list-line"></i>
                                    This <?= $timePeriod ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="col-xl-8 col-lg-12 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom-0">
                <h6 class="mb-0">
                    <i class="ri-line-chart-line me-2 text-primary"></i>
                    Leave Trends Over Time
                </h6>
            </div>
            <div class="card-body">
                <canvas id="leaveTrendsChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Leave Type Distribution -->
    <div class="col-xl-4 col-lg-12 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom-0">
                <h6 class="mb-0">
                    <i class="ri-pie-chart-line me-2 text-primary"></i>
                    Leave Type Distribution
                </h6>
            </div>
            <div class="card-body">
                <canvas id="leaveTypeChart" width="400" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Monthly Breakdown -->
    <div class="col-xl-6 col-lg-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0">
                <h6 class="mb-0">
                    <i class="ri-bar-chart-box-line me-2 text-primary"></i>
                    Monthly Leave Breakdown
                </h6>
            </div>
            <div class="card-body">
                <canvas id="monthlyBreakdownChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Leave Balance Tracking -->
    <div class="col-xl-6 col-lg-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0">
                <h6 class="mb-0">
                    <i class="ri-dashboard-line me-2 text-primary"></i>
                    Leave Balance Tracking
                </h6>
            </div>
            <div class="card-body">
                <?php if ($leaveEntitlements): ?>
                    <?php foreach ($leaveEntitlements as $entitlement): ?>
                        <?php if ($entitlement->leaveSegment && strtolower($entitlement->leaveSegment) != strtolower($employeeDetails->gender)) continue; ?>

                        <div class="balance-tracker mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0"><?= htmlspecialchars($entitlement->leaveTypeName) ?></h6>
                                <span class="badge bg-primary">
                                    <?= $entitlement->entitlement ?> / <?= $entitlement->totalEntitlement ?? $entitlement->entitlement ?> days
                                </span>
                            </div>

                            <div class="progress mb-2" style="height: 8px;">
                                <?php
                                $usedDays = ($entitlement->totalEntitlement ?? $entitlement->entitlement) - $entitlement->entitlement;
                                $totalDays = $entitlement->totalEntitlement ?? $entitlement->entitlement;
                                $percentage = $totalDays > 0 ? ($usedDays / $totalDays) * 100 : 0;
                                $colorClass = $percentage > 80 ? 'bg-danger' : ($percentage > 60 ? 'bg-warning' : 'bg-success');
                                ?>
                                <div class="progress-bar <?= $colorClass ?>"
                                     style="width: <?= $percentage ?>%"></div>
                            </div>

                            <div class="d-flex justify-content-between small text-muted">
                                <span>Used: <?= $usedDays ?> days</span>
                                <span>Available: <?= $entitlement->entitlement ?> days</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="ri-calendar-line fs-3 text-muted mb-2"></i>
                        <p class="text-muted mb-0">No leave entitlements found</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Team Analytics (for managers) -->
    <?php if ($isManager && !empty($teamAnalytics)): ?>
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0">
                    <h6 class="mb-0">
                        <i class="ri-team-line me-2 text-primary"></i>
                        Team Leave Analytics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                            <div class="team-metric-card text-center p-3 border rounded">
                                <h4 class="text-primary mb-2"><?= $teamAnalytics['totalTeamMembers'] ?? 0 ?></h4>
                                <h6 class="text-muted mb-0">Team Members</h6>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                            <div class="team-metric-card text-center p-3 border rounded">
                                <h4 class="text-success mb-2"><?= $teamAnalytics['teamLeaveUtilization'] ?? 0 ?>%</h4>
                                <h6 class="text-muted mb-0">Team Utilization</h6>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                            <div class="team-metric-card text-center p-3 border rounded">
                                <h4 class="text-warning mb-2"><?= $teamAnalytics['pendingApprovals'] ?? 0 ?></h4>
                                <h6 class="text-muted mb-0">Pending Approvals</h6>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <canvas id="teamAnalyticsChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Insights and Recommendations -->
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0">
                <h6 class="mb-0">
                    <i class="ri-lightbulb-line me-2 text-primary"></i>
                    Insights & Recommendations
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="insight-item mb-3">
                            <div class="d-flex align-items-start">
                                <div class="insight-icon bg-info bg-opacity-10 text-info me-3">
                                    <i class="ri-information-line"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Leave Pattern Analysis</h6>
                                    <p class="text-muted small mb-0">
                                        You typically take leave during
                                        <?= $leaveAnalytics['peakLeaveMonths'] ?? 'various periods' ?>.
                                        Consider planning ahead for optimal coverage.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="insight-item mb-3">
                            <div class="d-flex align-items-start">
                                <div class="insight-icon bg-warning bg-opacity-10 text-warning me-3">
                                    <i class="ri-alarm-warning-line"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Balance Alert</h6>
                                    <p class="text-muted small mb-0">
                                        <?php
                                        $lowBalanceTypes = array_filter($leaveEntitlements, function($ent) {
                                            return $ent->entitlement < 5;
                                        });
                                        if (!empty($lowBalanceTypes)): ?>
                                            Your <?= implode(', ', array_column($lowBalanceTypes, 'leaveTypeName')) ?>
                                            leave balance is running low.
                                        <?php else: ?>
                                            Your leave balances are healthy.
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="insight-item mb-3">
                            <div class="d-flex align-items-start">
                                <div class="insight-icon bg-success bg-opacity-10 text-success me-3">
                                    <i class="ri-check-line"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Approval Rate</h6>
                                    <p class="text-muted small mb-0">
                                        Your applications have a
                                        <?= $leaveAnalytics['approvalRate'] ?? 0 ?>% approval rate.
                                        Great job on providing complete information!
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="insight-item mb-3">
                            <div class="d-flex align-items-start">
                                <div class="insight-icon bg-primary bg-opacity-10 text-primary me-3">
                                    <i class="ri-calendar-schedule-line"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Planning Suggestion</h6>
                                    <p class="text-muted small mb-0">
                                        Consider submitting leave requests
                                        <?= $leaveAnalytics['averageAdvanceNotice'] ?? 14 ?> days in advance
                                        for better approval rates.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Analytics JavaScript -->
<script>
/**
 * Analytics View Specific Functionality
 */

// Chart.js configuration
const chartColors = {
    primary: '#007bff',
    success: '#28a745',
    warning: '#ffc107',
    danger: '#dc3545',
    info: '#17a2b8',
    secondary: '#6c757d'
};

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();

    document.querySelectorAll('[data-action="change-analytics-period"]').forEach(button => {
        button.addEventListener('click', () => {
            const period = button.dataset.period || 'year';
            changeTimePeriod(period);
        });
    });

    document.querySelectorAll('[data-action="export-analytics"]').forEach(button => {
        button.addEventListener('click', () => {
            exportAnalytics();
        });
    });
});

function initializeCharts() {
    // Leave Trends Chart
    const trendsCtx = document.getElementById('leaveTrendsChart').getContext('2d');
    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($filteredAnalytics['monthlyLabels'] ?? []) ?>,
            datasets: [{
                label: 'Leave Days',
                data: <?= json_encode($filteredAnalytics['monthlyData'] ?? []) ?>,
                borderColor: chartColors.primary,
                backgroundColor: chartColors.primary + '20',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Leave Type Distribution Chart
    const typeCtx = document.getElementById('leaveTypeChart').getContext('2d');
    new Chart(typeCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($filteredAnalytics['leaveTypeLabels'] ?? []) ?>,
            datasets: [{
                data: <?= json_encode($filteredAnalytics['leaveTypeData'] ?? []) ?>,
                backgroundColor: [
                    chartColors.primary,
                    chartColors.success,
                    chartColors.warning,
                    chartColors.danger,
                    chartColors.info
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

    // Monthly Breakdown Chart
    const monthlyCtx = document.getElementById('monthlyBreakdownChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($filteredAnalytics['monthlyLabels'] ?? []) ?>,
            datasets: [{
                label: 'Applications',
                data: <?= json_encode($filteredAnalytics['monthlyApplications'] ?? []) ?>,
                backgroundColor: chartColors.info,
                borderColor: chartColors.info,
                borderWidth: 1
            }, {
                label: 'Days',
                data: <?= json_encode($filteredAnalytics['monthlyDays'] ?? []) ?>,
                backgroundColor: chartColors.primary,
                borderColor: chartColors.primary,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Team Analytics Chart (for managers)
    <?php if ($isManager): ?>
    const teamCtx = document.getElementById('teamAnalyticsChart');
    if (teamCtx) {
        new Chart(teamCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($teamAnalytics['teamMemberNames'] ?? []) ?>,
                datasets: [{
                    label: 'Leave Days Used',
                    data: <?= json_encode($teamAnalytics['teamMemberData'] ?? []) ?>,
                    backgroundColor: chartColors.primary,
                    borderColor: chartColors.primary,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
    <?php endif; ?>
}

function changeTimePeriod(period) {
    const url = new URL(window.location);
    url.searchParams.set('period', period);
    window.location.href = url.toString();
}

function exportAnalytics() {
    showToast('info', 'Exporting Analytics', 'Preparing your analytics data for download...');

    // Simulate export process
    setTimeout(() => {
        showToast('success', 'Export Ready', 'Your analytics report has been generated and is ready for download.');
        // In a real implementation, this would trigger a download
    }, 2000);
}
</script>

<style>
/* Analytics View Specific Styles */
.metric-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.balance-tracker {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.team-metric-card {
    background: #f8f9fa;
    transition: all 0.2s ease;
}

.team-metric-card:hover {
    background: white;
    border-color: #007bff !important;
    transform: translateY(-2px);
}

.insight-item {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    transition: all 0.2s ease;
}

.insight-item:hover {
    background: white;
    border-color: #007bff;
}

.insight-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

/* Chart containers */
.card-body canvas {
    max-height: 300px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .team-metric-card {
        margin-bottom: 1rem;
    }

    .insight-item {
        margin-bottom: 1rem;
    }

    .balance-tracker {
        margin-bottom: 1rem;
    }
}
</style>
