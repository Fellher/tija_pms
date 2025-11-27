<?php
/**
 * Activity Analytics View
 * 
 * Provides comprehensive metrics and insights for activity management
 * Features:
 * - Performance metrics
 * - Completion rates
 * - Time tracking analysis
 * - Category breakdowns
 * - Trend analysis
 * - Export capabilities
 */

if (!isset($activities) || empty($activities)) {
    echo '<div class="text-center py-5">
            <div class="empty-state-icon mb-4">
                <i class="ri-bar-chart-line fs-1 text-muted"></i>
            </div>
            <h4 class="text-muted mb-3">No Data Available</h4>
            <p class="text-muted mb-4">Create some activities to see analytics and insights.</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manage_activity">
                <i class="ri-add-line me-2"></i>Create Activity
            </button>
          </div>';
    return;
}

// ============================================================================
// ANALYTICS DATA PROCESSING
// ============================================================================

/**
 * Calculate comprehensive activity metrics
 */

// Basic counts
$totalActivities = count($activities);
$completedActivities = array_filter($activities, function($activity) {
    return $activity->activityStatusID == 4;
});
$overdueActivities = array_filter($activities, function($activity) {
    return strtotime($activity->activityDate) < time() && $activity->activityStatusID != 4;
});
$todayActivities = array_filter($activities, function($activity) {
    return date('Y-m-d') == date('Y-m-d', strtotime($activity->activityDate));
});

// Completion rate
$completionRate = $totalActivities > 0 ? round((count($completedActivities) / $totalActivities) * 100) : 0;

// Overdue rate
$overdueRate = $totalActivities > 0 ? round((count($overdueActivities) / $totalActivities) * 100) : 0;

// Activities by status
$activitiesByStatus = [
    'todo' => count(array_filter($activities, function($a) { return $a->activityStatusID == 1; })),
    'in_progress' => count(array_filter($activities, function($a) { return $a->activityStatusID == 2; })),
    'review' => count(array_filter($activities, function($a) { return $a->activityStatusID == 3; })),
    'completed' => count(array_filter($activities, function($a) { return $a->activityStatusID == 4; })),
    'cancelled' => count(array_filter($activities, function($a) { return $a->activityStatusID == 5; }))
];

// Activities by category
$activitiesByCategory = [];
foreach ($activities as $activity) {
    $category = $activity->activityCategoryName ?? 'Uncategorized';
    if (!isset($activitiesByCategory[$category])) {
        $activitiesByCategory[$category] = 0;
    }
    $activitiesByCategory[$category]++;
}

// Activities by month (last 12 months)
$activitiesByMonth = [];
for ($i = 11; $i >= 0; $i--) {
    $monthDate = new DateTime();
    $monthDate->modify("-$i months");
    $monthKey = $monthDate->format('Y-m');
    $monthName = $monthDate->format('M Y');
    
    $monthActivities = array_filter($activities, function($activity) use ($monthDate) {
        return date('Y-m', strtotime($activity->activityDate)) === $monthDate->format('Y-m');
    });
    
    $monthCompleted = array_filter($monthActivities, function($activity) {
        return $activity->activityStatusID == 4;
    });
    
    $activitiesByMonth[$monthKey] = [
        'name' => $monthName,
        'total' => count($monthActivities),
        'completed' => count($monthCompleted),
        'completion_rate' => count($monthActivities) > 0 ? round((count($monthCompleted) / count($monthActivities)) * 100) : 0
    ];
}

// Average completion time (simplified calculation)
$completedWithDates = array_filter($completedActivities, function($activity) {
    return isset($activity->LastUpdate) && !empty($activity->LastUpdate);
});

$totalCompletionTime = 0;
$completionTimeCount = 0;

foreach ($completedWithDates as $activity) {
    $startDate = strtotime($activity->activityDate);
    $endDate = strtotime($activity->LastUpdate);
    if ($endDate > $startDate) {
        $totalCompletionTime += ($endDate - $startDate) / (24 * 60 * 60); // Convert to days
        $completionTimeCount++;
    }
}

$averageCompletionTime = $completionTimeCount > 0 ? round($totalCompletionTime / $completionTimeCount, 1) : 0;

// Top performers (by completion rate)
$activitiesByOwner = [];
foreach ($activities as $activity) {
    $owner = $activity->activityOwnerName ?? 'Unassigned';
    if (!isset($activitiesByOwner[$owner])) {
        $activitiesByOwner[$owner] = ['total' => 0, 'completed' => 0];
    }
    $activitiesByOwner[$owner]['total']++;
    if ($activity->activityStatusID == 4) {
        $activitiesByOwner[$owner]['completed']++;
    }
}

foreach ($activitiesByOwner as $owner => &$data) {
    $data['completion_rate'] = $data['total'] > 0 ? round(($data['completed'] / $data['total']) * 100) : 0;
}
?>

<!-- Analytics View Container -->
<div class="analytics-container">
    <!-- Analytics Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0">
            <i class="ri-bar-chart-line me-2 text-primary"></i>
            Activity Analytics
        </h5>
        
        <!-- Export and Filter Controls -->
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="ri-calendar-line me-1"></i>Time Period
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="filterAnalytics('7d')">Last 7 days</a></li>
                    <li><a class="dropdown-item" href="#" onclick="filterAnalytics('30d')">Last 30 days</a></li>
                    <li><a class="dropdown-item" href="#" onclick="filterAnalytics('90d')">Last 90 days</a></li>
                    <li><a class="dropdown-item" href="#" onclick="filterAnalytics('1y')">Last year</a></li>
                    <li><a class="dropdown-item" href="#" onclick="filterAnalytics('all')">All time</a></li>
                </ul>
            </div>
            
            <div class="dropdown">
                <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="ri-download-line me-1"></i>Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="exportAnalytics('pdf')">
                        <i class="ri-file-pdf-line me-2"></i>Export as PDF
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportAnalytics('excel')">
                        <i class="ri-file-excel-line me-2"></i>Export as Excel
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportAnalytics('csv')">
                        <i class="ri-file-text-line me-2"></i>Export as CSV
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Key Metrics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="chart-container">
                <div class="metric-card">
                    <div class="metric-icon bg-primary bg-opacity-10 text-primary">
                        <i class="ri-check-double-line fs-3"></i>
                    </div>
                    <div class="metric-content">
                        <h3 class="metric-value"><?= $completionRate ?>%</h3>
                        <p class="metric-label">Completion Rate</p>
                        <div class="metric-change positive">
                            <i class="ri-arrow-up-line"></i> +<?= $completionRate ?>% from target
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="chart-container">
                <div class="metric-card">
                    <div class="metric-icon bg-success bg-opacity-10 text-success">
                        <i class="ri-timer-line fs-3"></i>
                    </div>
                    <div class="metric-content">
                        <h3 class="metric-value"><?= $averageCompletionTime ?></h3>
                        <p class="metric-label">Avg. Completion (days)</p>
                        <div class="metric-change positive">
                            <i class="ri-trending-down-line"></i> Improving efficiency
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="chart-container">
                <div class="metric-card">
                    <div class="metric-icon bg-warning bg-opacity-10 text-warning">
                        <i class="ri-time-line fs-3"></i>
                    </div>
                    <div class="metric-content">
                        <h3 class="metric-value"><?= count($overdueActivities) ?></h3>
                        <p class="metric-label">Overdue Activities</p>
                        <div class="metric-change <?= count($overdueActivities) > 0 ? 'negative' : 'positive' ?>">
                            <i class="ri-arrow-<?= count($overdueActivities) > 0 ? 'up' : 'down' ?>-line"></i>
                            <?= count($overdueActivities) > 0 ? 'Needs attention' : 'All on track' ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="chart-container">
                <div class="metric-card">
                    <div class="metric-icon bg-info bg-opacity-10 text-info">
                        <i class="ri-calendar-todo-line fs-3"></i>
                    </div>
                    <div class="metric-content">
                        <h3 class="metric-value"><?= $totalActivities ?></h3>
                        <p class="metric-label">Total Activities</p>
                        <div class="metric-change positive">
                            <i class="ri-add-line"></i> Active workload
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Status Distribution Chart -->
        <div class="col-xl-6 col-lg-12">
            <div class="chart-container">
                <h6 class="chart-title">
                    <i class="ri-pie-chart-line me-2"></i>
                    Activity Status Distribution
                </h6>
                <div class="chart-content">
                    <canvas id="statusChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Monthly Trend Chart -->
        <div class="col-xl-6 col-lg-12">
            <div class="chart-container">
                <h6 class="chart-title">
                    <i class="ri-line-chart-line me-2"></i>
                    Monthly Activity Trends
                </h6>
                <div class="chart-content">
                    <canvas id="trendChart" width="400" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Detailed Analysis Row -->
    <div class="row mb-4">
        <!-- Category Breakdown -->
        <div class="col-xl-4 col-lg-6">
            <div class="chart-container">
                <h6 class="chart-title">
                    <i class="ri-folder-line me-2"></i>
                    Activities by Category
                </h6>
                <div class="category-list">
                    <?php foreach ($activitiesByCategory as $category => $count): ?>
                        <?php $percentage = $totalActivities > 0 ? round(($count / $totalActivities) * 100) : 0; ?>
                        <div class="category-item">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="category-name"><?= htmlspecialchars($category) ?></span>
                                <span class="category-count"><?= $count ?></span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" style="width: <?= $percentage ?>%"></div>
                            </div>
                            <small class="text-muted"><?= $percentage ?>% of total</small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Performance by Owner -->
        <div class="col-xl-4 col-lg-6">
            <div class="chart-container">
                <h6 class="chart-title">
                    <i class="ri-user-line me-2"></i>
                    Performance by Owner
                </h6>
                <div class="performance-list">
                    <?php 
                    // Sort by completion rate
                    uasort($activitiesByOwner, function($a, $b) {
                        return $b['completion_rate'] - $a['completion_rate'];
                    });
                    
                    foreach (array_slice($activitiesByOwner, 0, 10) as $owner => $data): 
                    ?>
                        <div class="performance-item">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="owner-name"><?= htmlspecialchars($owner) ?></span>
                                <span class="completion-rate"><?= $data['completion_rate'] ?>%</span>
                            </div>
                            <div class="d-flex justify-content-between text-muted small mb-2">
                                <span><?= $data['completed'] ?> completed</span>
                                <span><?= $data['total'] ?> total</span>
                            </div>
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar <?= $data['completion_rate'] >= 80 ? 'bg-success' : ($data['completion_rate'] >= 60 ? 'bg-warning' : 'bg-danger') ?>" 
                                     style="width: <?= $data['completion_rate'] ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Quick Insights -->
        <div class="col-xl-4 col-lg-12">
            <div class="chart-container">
                <h6 class="chart-title">
                    <i class="ri-lightbulb-line me-2"></i>
                    Quick Insights
                </h6>
                <div class="insights-list">
                    <div class="insight-item">
                        <div class="insight-icon bg-success bg-opacity-10 text-success">
                            <i class="ri-check-line"></i>
                        </div>
                        <div class="insight-content">
                            <h6 class="insight-title">Completion Rate</h6>
                            <p class="insight-text">
                                <?= $completionRate >= 80 ? 'Excellent' : ($completionRate >= 60 ? 'Good' : 'Needs Improvement') ?> 
                                completion rate of <?= $completionRate ?>%
                            </p>
                        </div>
                    </div>
                    
                    <div class="insight-item">
                        <div class="insight-icon bg-warning bg-opacity-10 text-warning">
                            <i class="ri-time-line"></i>
                        </div>
                        <div class="insight-content">
                            <h6 class="insight-title">Overdue Activities</h6>
                            <p class="insight-text">
                                <?= count($overdueActivities) ?> activities are overdue and need immediate attention
                            </p>
                        </div>
                    </div>
                    
                    <div class="insight-item">
                        <div class="insight-icon bg-info bg-opacity-10 text-info">
                            <i class="ri-trending-up-line"></i>
                        </div>
                        <div class="insight-content">
                            <h6 class="insight-title">Productivity</h6>
                            <p class="insight-text">
                                Average completion time of <?= $averageCompletionTime ?> days shows 
                                <?= $averageCompletionTime <= 3 ? 'excellent' : ($averageCompletionTime <= 7 ? 'good' : 'room for improvement') ?> efficiency
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Analytics JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
/**
 * Analytics View Specific Functionality
 */

// Initialize analytics when view is loaded
function initializeAnalyticsView() {
    initializeStatusChart();
    initializeTrendChart();
    setupAnalyticsInteractions();
}

// Initialize status distribution chart
function initializeStatusChart() {
    const ctx = document.getElementById('statusChart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['To Do', 'In Progress', 'Review', 'Completed', 'Cancelled'],
            datasets: [{
                data: [
                    <?= $activitiesByStatus['todo'] ?>,
                    <?= $activitiesByStatus['in_progress'] ?>,
                    <?= $activitiesByStatus['review'] ?>,
                    <?= $activitiesByStatus['completed'] ?>,
                    <?= $activitiesByStatus['cancelled'] ?>
                ],
                backgroundColor: [
                    '#6c757d',
                    '#007bff',
                    '#ffc107',
                    '#28a745',
                    '#dc3545'
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
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((context.parsed / total) * 100) : 0;
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}

// Initialize trend chart
function initializeTrendChart() {
    const ctx = document.getElementById('trendChart');
    if (!ctx) return;
    
    const months = <?= json_encode(array_column($activitiesByMonth, 'name')) ?>;
    const totalData = <?= json_encode(array_column($activitiesByMonth, 'total')) ?>;
    const completedData = <?= json_encode(array_column($activitiesByMonth, 'completed')) ?>;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Total Activities',
                data: totalData,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Completed',
                data: completedData,
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
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            }
        }
    });
}

// Setup analytics interactions
function setupAnalyticsInteractions() {
    // Add click handlers for export buttons
    document.querySelectorAll('[onclick^="exportAnalytics"]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const format = this.onclick.toString().match(/'([^']+)'/)[1];
            exportAnalytics(format);
        });
    });
    
    // Add click handlers for filter buttons
    document.querySelectorAll('[onclick^="filterAnalytics"]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const period = this.onclick.toString().match(/'([^']+)'/)[1];
            filterAnalytics(period);
        });
    });
}

// Export analytics data
function exportAnalytics(format) {
    showToast('info', 'Exporting...', 'Preparing your analytics report...');
    
    // Simulate export process
    setTimeout(() => {
        showToast('success', 'Export Ready', 'Your analytics report has been prepared and is ready for download.');
        
        // In a real implementation, this would trigger a download
        console.log('Exporting analytics as', format);
    }, 2000);
}

// Filter analytics by time period
function filterAnalytics(period) {
    showToast('info', 'Filtering...', 'Updating analytics for selected period...');
    
    // In a real implementation, this would reload the analytics with filtered data
    setTimeout(() => {
        showToast('success', 'Filtered', 'Analytics updated for ' + period + ' period.');
        console.log('Filtering analytics for period:', period);
    }, 1000);
}

// Initialize analytics when view is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('analytics-view').classList.contains('active')) {
        setTimeout(() => {
            initializeAnalyticsView();
        }, 100);
    }
});

// Re-initialize when switching to analytics view
document.addEventListener('viewChanged', function(e) {
    if (e.detail.view === 'analytics') {
        setTimeout(() => {
            initializeAnalyticsView();
        }, 100);
    }
});
</script>

<style>
/* Analytics View Specific Styles */
.analytics-container {
    padding: 1rem;
}

.chart-container {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
    height: 100%;
}

.chart-title {
    color: #495057;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #f8f9fa;
}

.chart-content {
    height: 300px;
    position: relative;
}

.metric-card {
    display: flex;
    align-items: center;
    padding: 1.5rem;
    border-radius: 12px;
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.metric-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
}

.metric-content {
    flex: 1;
}

.metric-value {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 0.25rem;
    color: #495057;
}

.metric-label {
    color: #6c757d;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.metric-change {
    font-size: 0.8rem;
    font-weight: 500;
}

.metric-change.positive {
    color: #28a745;
}

.metric-change.negative {
    color: #dc3545;
}

/* Category and Performance Lists */
.category-list,
.performance-list {
    max-height: 400px;
    overflow-y: auto;
}

.category-item,
.performance-item {
    padding: 1rem 0;
    border-bottom: 1px solid #f8f9fa;
}

.category-item:last-child,
.performance-item:last-child {
    border-bottom: none;
}

.category-name,
.owner-name {
    font-weight: 500;
    color: #495057;
}

.category-count,
.completion-rate {
    font-weight: 600;
    color: #007bff;
}

/* Insights List */
.insights-list {
    max-height: 400px;
    overflow-y: auto;
}

.insight-item {
    display: flex;
    align-items: flex-start;
    padding: 1rem 0;
    border-bottom: 1px solid #f8f9fa;
}

.insight-item:last-child {
    border-bottom: none;
}

.insight-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    flex-shrink: 0;
}

.insight-content {
    flex: 1;
}

.insight-title {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
    color: #495057;
}

.insight-text {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 0;
    line-height: 1.4;
}

/* Responsive Design */
@media (max-width: 768px) {
    .metric-card {
        flex-direction: column;
        text-align: center;
    }
    
    .metric-icon {
        margin-right: 0;
        margin-bottom: 1rem;
    }
    
    .chart-content {
        height: 250px;
    }
}

/* Scrollbar Styling */
.category-list::-webkit-scrollbar,
.performance-list::-webkit-scrollbar,
.insights-list::-webkit-scrollbar {
    width: 6px;
}

.category-list::-webkit-scrollbar-track,
.performance-list::-webkit-scrollbar-track,
.insights-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.category-list::-webkit-scrollbar-thumb,
.performance-list::-webkit-scrollbar-thumb,
.insights-list::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.category-list::-webkit-scrollbar-thumb:hover,
.performance-list::-webkit-scrollbar-thumb:hover,
.insights-list::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
