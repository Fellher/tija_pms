<?php
/**
 * Overview Tab - Leave Analytics Dashboard
 * Executive summary and key metrics visualization
 */

// Fetch analytics data
$orgAnalytics = Leave::get_organization_leave_analytics($selectedOrgDataID, $selectedEntityID, $startDate, $endDate, $DBConn);
$leaveTypeDistribution = Leave::get_leave_type_distribution($selectedOrgDataID, $selectedEntityID, $startDate, $endDate, $DBConn);
$monthlyTrends = Leave::get_monthly_leave_trends($selectedOrgDataID, $selectedEntityID, $startDate, $endDate, $DBConn);
$departmentalBreakdown = Leave::get_departmental_leave_breakdown($selectedOrgDataID, $selectedEntityID, $startDate, $endDate, $DBConn);
?>

<!-- Executive Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm metric-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="text-muted text-uppercase small mb-1">Total Leave Days</h6>
                        <h2 class="mb-0 fw-bold text-primary"><?php echo number_format($orgAnalytics['approvedLeaveDays'], 0); ?></h2>
                        <small class="text-muted">Approved in period</small>
                    </div>
                    <div class="icon-box bg-primary bg-opacity-10 p-3 rounded-circle">
                        <i class="ri-calendar-check-line fs-4 text-primary"></i>
                    </div>
                </div>
                <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-primary" role="progressbar"
                         style="width: <?php echo min(100, $orgAnalytics['utilizationRate']); ?>%"></div>
                </div>
                <small class="text-muted">
                    <?php echo $orgAnalytics['utilizationRate']; ?>% utilization rate
                </small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm metric-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="text-muted text-uppercase small mb-1">Applications</h6>
                        <h2 class="mb-0 fw-bold text-success"><?php echo $orgAnalytics['approvedApplications']; ?></h2>
                        <small class="text-muted">of <?php echo $orgAnalytics['totalApplications']; ?> total</small>
                    </div>
                    <div class="icon-box bg-success bg-opacity-10 p-3 rounded-circle">
                        <i class="ri-file-list-3-line fs-4 text-success"></i>
                    </div>
                </div>
                <div class="d-flex gap-2 small">
                    <span class="badge bg-success"><?php echo $orgAnalytics['approvedApplications']; ?> Approved</span>
                    <span class="badge bg-danger"><?php echo $orgAnalytics['rejectedApplications']; ?> Rejected</span>
                    <span class="badge bg-warning text-dark"><?php echo $orgAnalytics['pendingApplications']; ?> Pending</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm metric-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="text-muted text-uppercase small mb-1">Workforce Impact</h6>
                        <h2 class="mb-0 fw-bold text-warning"><?php echo $orgAnalytics['employeesOnLeave']; ?></h2>
                        <small class="text-muted">Currently on leave</small>
                    </div>
                    <div class="icon-box bg-warning bg-opacity-10 p-3 rounded-circle">
                        <i class="ri-team-line fs-4 text-warning"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">
                        Total employees: <?php echo $orgAnalytics['totalEmployees']; ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm metric-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="text-muted text-uppercase small mb-1">Average Duration</h6>
                        <h2 class="mb-0 fw-bold text-info"><?php echo $orgAnalytics['averageApplicationDays']; ?></h2>
                        <small class="text-muted">days per application</small>
                    </div>
                    <div class="icon-box bg-info bg-opacity-10 p-3 rounded-circle">
                        <i class="ri-time-line fs-4 text-info"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-muted">
                        Peak period: <?php echo htmlspecialchars($orgAnalytics['peakAbsencePeriod']); ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
    <!-- Utilization Gauge -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0"><i class="ri-dashboard-line me-2 text-primary"></i>Leave Utilization</h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 250px;">
                    <canvas id="utilizationGaugeChart"></canvas>
                </div>
                <div class="text-center mt-3">
                    <p class="mb-1 text-muted">Most common leave type</p>
                    <h6 class="mb-0"><?php echo htmlspecialchars($orgAnalytics['topLeaveType']); ?></h6>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trends -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0"><i class="ri-line-chart-line me-2 text-primary"></i>Monthly Leave Trends</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="monthlyTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Leave Type Distribution -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0"><i class="ri-pie-chart-line me-2 text-primary"></i>Leave Type Distribution</h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 300px;">
                    <canvas id="leaveTypeChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Comparison -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0"><i class="ri-bar-chart-horizontal-line me-2 text-primary"></i>Department Comparison</h5>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 300px;">
                    <canvas id="departmentChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Data Tables -->
<div class="row g-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0"><i class="ri-table-line me-2 text-primary"></i>Detailed Breakdown</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Department</th>
                                <th class="text-end">Applications</th>
                                <th class="text-end">Approved Days</th>
                                <th class="text-end">Employees</th>
                                <th class="text-end">Avg. Days</th>
                                <th class="text-end">Utilization</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($departmentalBreakdown as $dept): ?>
                            <tr>
                                <td class="fw-semibold"><?php echo htmlspecialchars($dept['departmentName']); ?></td>
                                <td class="text-end"><?php echo $dept['totalApplications']; ?></td>
                                <td class="text-end"><?php echo number_format($dept['approvedDays'], 1); ?></td>
                                <td class="text-end"><?php echo $dept['uniqueEmployees']; ?></td>
                                <td class="text-end"><?php echo $dept['averageDays']; ?></td>
                                <td class="text-end">
                                    <span class="badge <?php echo $dept['utilizationRate'] > 80 ? 'bg-danger' : ($dept['utilizationRate'] > 60 ? 'bg-warning' : 'bg-success'); ?>">
                                        <?php echo $dept['utilizationRate']; ?>%
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

<script>
// Prepare data for charts
const overviewChartData = {
    utilization: <?php echo $orgAnalytics['utilizationRate']; ?>,
    monthlyTrends: <?php echo json_encode($monthlyTrends); ?>,
    leaveTypes: <?php echo json_encode($leaveTypeDistribution); ?>,
    departments: <?php echo json_encode($departmentalBreakdown); ?>
};
</script>

