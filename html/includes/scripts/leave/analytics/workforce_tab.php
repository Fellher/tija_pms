<?php
/**
 * Workforce Impact Tab - Leave Analytics Dashboard
 * Concurrent absences and team coverage analysis
 */

// Fetch concurrent absence data
$concurrentAnalysis = Leave::get_concurrent_absence_analysis($selectedOrgDataID, $selectedEntityID, $startDate, $endDate, $DBConn);
$departmentalBreakdown = Leave::get_departmental_leave_breakdown($selectedOrgDataID, $selectedEntityID, $startDate, $endDate, $DBConn);
?>

<div class="row g-3 mb-4">
    <!-- Summary Cards -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="ri-alert-line me-2 text-danger"></i>Peak Concurrent Absences</h6>
            </div>
            <div class="card-body">
                <h2 class="mb-2 fw-bold text-danger"><?php echo $concurrentAnalysis['maxConcurrentAbsences']; ?> employees</h2>
                <p class="text-muted mb-0">
                    Date: <?php echo $concurrentAnalysis['maxConcurrentDate'] ? Utility::date_format($concurrentAnalysis['maxConcurrentDate']) : 'N/A'; ?>
                </p>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Average daily</span>
                    <span class="fw-semibold"><?php echo $concurrentAnalysis['averageConcurrentAbsences']; ?> employees</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0"><i class="ri-calendar-2-line me-2 text-primary"></i>Daily Absence Heatmap</h6>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 200px;">
                    <canvas id="absenceHeatmapChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- High Risk Periods -->
<?php if (!empty($concurrentAnalysis['highRiskDates'])): ?>
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm border-danger border-start border-4">
            <div class="card-header bg-danger bg-opacity-10 border-bottom">
                <h5 class="mb-0 text-danger"><i class="ri-error-warning-line me-2"></i>High Risk Periods Detected</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">The following dates have high concurrent absences that may impact operations:</p>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Employees Absent</th>
                                <th>Employees</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($concurrentAnalysis['highRiskDates'] as $riskDate): ?>
                            <tr>
                                <td><?php echo Utility::date_format($riskDate['date']); ?></td>
                                <td><span class="badge bg-danger"><?php echo $riskDate['count']; ?> employees</span></td>
                                <td class="small"><?php echo htmlspecialchars(substr($riskDate['employees'], 0, 100)); ?><?php echo strlen($riskDate['employees']) > 100 ? '...' : ''; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Department Coverage Table -->
<div class="row g-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0"><i class="ri-building-2-line me-2 text-primary"></i>Department Coverage Analysis</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Department</th>
                                <th class="text-end">Total Staff</th>
                                <th class="text-end">On Leave (Approved)</th>
                                <th class="text-end">Pending Requests</th>
                                <th class="text-end">Coverage %</th>
                                <th class="text-center">Risk Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($departmentalBreakdown as $dept): ?>
                            <?php
                                $coverage = $dept['uniqueEmployees'] > 0
                                    ? round((1 - ($dept['approvedDays'] / ($dept['uniqueEmployees'] * 20))) * 100, 1)
                                    : 100;
                                $riskLevel = $coverage < 70 ? 'High' : ($coverage < 85 ? 'Medium' : 'Low');
                                $riskBadge = $coverage < 70 ? 'bg-danger' : ($coverage < 85 ? 'bg-warning text-dark' : 'bg-success');
                            ?>
                            <tr>
                                <td class="fw-semibold"><?php echo htmlspecialchars($dept['departmentName']); ?></td>
                                <td class="text-end"><?php echo $dept['uniqueEmployees']; ?></td>
                                <td class="text-end"><?php echo round($dept['approvedDays'] / ($dept['averageDays'] ?: 1), 0); ?></td>
                                <td class="text-end">
                                    <span class="badge bg-warning text-dark">
                                        <?php echo $dept['totalApplications'] - $dept['approvedApplications'] - $dept['rejectedApplications']; ?>
                                    </span>
                                </td>
                                <td class="text-end"><?php echo $coverage; ?>%</td>
                                <td class="text-center">
                                    <span class="badge <?php echo $riskBadge; ?>"><?php echo $riskLevel; ?></span>
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
const workforceChartData = {
    dailyAbsences: <?php echo json_encode($concurrentAnalysis['dailyAbsences']); ?>,
    departments: <?php echo json_encode($departmentalBreakdown); ?>
};
</script>

