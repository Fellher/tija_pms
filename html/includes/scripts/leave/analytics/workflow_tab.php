<?php
/**
 * Approval Workflow Analysis Tab - Leave Analytics Dashboard
 * Processing times, approval patterns, and bottleneck identification
 */

// Fetch workflow metrics
$workflowMetrics = Leave::get_approval_workflow_metrics($selectedOrgDataID, $selectedEntityID, $startDate, $endDate, $DBConn);
?>

<div class="row g-3 mb-4">
    <!-- Summary Cards -->
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="text-muted text-uppercase small mb-1">Avg. Approval Time</h6>
                        <h2 class="mb-0 fw-bold text-primary"><?php echo $workflowMetrics['averageApprovalTime']; ?>h</h2>
                    </div>
                    <i class="ri-time-line fs-3 text-primary opacity-50"></i>
                </div>
                <small class="text-muted">Median: <?php echo $workflowMetrics['medianApprovalTime']; ?>h</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="text-muted text-uppercase small mb-1">Approval Rate</h6>
                        <h2 class="mb-0 fw-bold text-success"><?php echo $workflowMetrics['approvalRate']; ?>%</h2>
                    </div>
                    <i class="ri-check-line fs-3 text-success opacity-50"></i>
                </div>
                <small class="text-muted">Rejection Rate: <?php echo $workflowMetrics['rejectionRate']; ?>%</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="text-muted text-uppercase small mb-1">Bottleneck Step</h6>
                        <h6 class="mb-0 fw-bold text-warning"><?php echo htmlspecialchars($workflowMetrics['bottleneckStep']); ?></h6>
                    </div>
                    <i class="ri-alert-line fs-3 text-warning opacity-50"></i>
                </div>
                <small class="text-muted">Longest processing time</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h6 class="text-muted text-uppercase small mb-1">Avg. Steps</h6>
                        <h2 class="mb-0 fw-bold text-info"><?php echo $workflowMetrics['averageStepsToApproval']; ?></h2>
                    </div>
                    <i class="ri-git-branch-line fs-3 text-info opacity-50"></i>
                </div>
                <small class="text-muted">To final approval</small>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0"><i class="ri-time-line me-2 text-primary"></i>Approval Time Distribution</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="approvalTimeChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0"><i class="ri-pie-chart-2-line me-2 text-primary"></i>Approval vs Rejection</h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="approvalRatioChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Step Performance Table -->
<?php if (!empty($workflowMetrics['stepMetrics'])): ?>
<div class="row g-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0"><i class="ri-route-line me-2 text-primary"></i>Workflow Step Performance</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Step</th>
                                <th class="text-end">Total Actions</th>
                                <th class="text-end">Approved</th>
                                <th class="text-end">Rejected</th>
                                <th class="text-end">Avg. Response Time</th>
                                <th class="text-center">Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($workflowMetrics['stepMetrics'] as $step): ?>
                            <?php
                                $approvalRate = $step['actionCount'] > 0
                                    ? round(($step['approvedCount'] / $step['actionCount']) * 100, 1)
                                    : 0;
                                $performance = $step['avgResponseTime'] < 24 ? 'Excellent' :
                                              ($step['avgResponseTime'] < 48 ? 'Good' : 'Needs Improvement');
                                $perfBadge = $step['avgResponseTime'] < 24 ? 'bg-success' :
                                            ($step['avgResponseTime'] < 48 ? 'bg-info' : 'bg-warning text-dark');
                            ?>
                            <tr>
                                <td class="fw-semibold"><?php echo htmlspecialchars($step['stepName']); ?></td>
                                <td class="text-end"><?php echo $step['actionCount']; ?></td>
                                <td class="text-end text-success"><?php echo $step['approvedCount']; ?></td>
                                <td class="text-end text-danger"><?php echo $step['rejectedCount']; ?></td>
                                <td class="text-end"><?php echo $step['avgResponseTime']; ?>h</td>
                                <td class="text-center">
                                    <span class="badge <?php echo $perfBadge; ?>"><?php echo $performance; ?></span>
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
<?php else: ?>
<div class="alert alert-info">
    <i class="ri-information-line me-2"></i>
    Workflow metrics require the leave approval workflow system. No workflow data available for this period.
</div>
<?php endif; ?>

<script>
const workflowChartData = {
    metrics: <?php echo json_encode($workflowMetrics); ?>,
    approvalRate: <?php echo $workflowMetrics['approvalRate']; ?>,
    rejectionRate: <?php echo $workflowMetrics['rejectionRate']; ?>
};
</script>

