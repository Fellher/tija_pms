<?php
/**
 * Departmental Analysis Tab - Leave Analytics Dashboard
 * Department-wise breakdown and comparative analysis
 */

// Fetch departmental data
$departmentalBreakdown = Leave::get_departmental_leave_breakdown($selectedOrgDataID, $selectedEntityID, $startDate, $endDate, $DBConn);
?>

<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0"><i class="ri-bar-chart-grouped-line me-2 text-primary"></i>Department Utilization Comparison</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="deptComparisonChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0"><i class="ri-table-2 me-2 text-primary"></i>Departmental Statistics</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Department</th>
                                <th class="text-end">Total Applications</th>
                                <th class="text-end">Approved</th>
                                <th class="text-end">Rejected</th>
                                <th class="text-end">Total Days</th>
                                <th class="text-end">Approved Days</th>
                                <th class="text-end">Employees</th>
                                <th class="text-end">Avg. Days/App</th>
                                <th class="text-end">Utilization %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($departmentalBreakdown): ?>
                                <?php foreach ($departmentalBreakdown as $dept): ?>
                                <tr>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($dept['departmentName']); ?></td>
                                    <td class="text-end"><?php echo $dept['totalApplications']; ?></td>
                                    <td class="text-end text-success"><?php echo $dept['approvedApplications']; ?></td>
                                    <td class="text-end text-danger"><?php echo $dept['rejectedApplications']; ?></td>
                                    <td class="text-end"><?php echo number_format($dept['totalDays'], 1); ?></td>
                                    <td class="text-end"><?php echo number_format($dept['approvedDays'], 1); ?></td>
                                    <td class="text-end"><?php echo $dept['uniqueEmployees']; ?></td>
                                    <td class="text-end"><?php echo $dept['averageDays']; ?></td>
                                    <td class="text-end">
                                        <span class="badge <?php
                                            echo $dept['utilizationRate'] > 80 ? 'bg-danger' :
                                                ($dept['utilizationRate'] > 60 ? 'bg-warning text-dark' : 'bg-success');
                                        ?>">
                                            <?php echo $dept['utilizationRate']; ?>%
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No departmental data available</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const departmentChartData = {
    departments: <?php echo json_encode($departmentalBreakdown); ?>
};
</script>

