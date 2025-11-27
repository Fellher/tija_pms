<!-- Dashboard View -->
<div class="row">
    <!-- Left Column: Pending Requests -->
    <div class="col-xl-8 col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                <h5 class="mb-0">
                    <i class="ri-time-line text-warning me-2"></i>
                    Pending Approval (<?= count($pendingApplications) ?>)
                </h5>
                <?php if (count($pendingApplications) > 1): ?>
                <button class="btn btn-sm btn-primary batch-approve-btn">
                    <i class="ri-checkbox-multiple-line me-1"></i>Batch Approve
                </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (!empty($pendingApplications)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <?php if (count($pendingApplications) > 1): ?>
                                    <th width="40">
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                    </th>
                                    <?php endif; ?>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Period</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingApplications as $app):
                                    $initials = Core::get_user_name_initials($app->employeeID, $DBConn);
                                ?>
                                <tr>
                                    <?php if (count($pendingApplications) > 1): ?>
                                    <td>
                                        <input type="checkbox" class="form-check-input application-checkbox"
                                               data-application-id="<?= $app->leaveApplicationID ?>">
                                    </td>
                                    <?php endif; ?>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-primary-transparent rounded-circle me-2">
                                                <span class="avatar-initials"><?= $initials['initials'] ?></span>
                                            </div>
                                            <div>
                                                <strong><?= htmlspecialchars($app->employeeName) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($app->jobTitle ?? 'N/A') ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: <?= $app->leaveColor ?? '#6c757d' ?>;">
                                            <?= htmlspecialchars($app->leaveTypeName) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small>
                                            <?= date('M d', strtotime($app->startDate)) ?> -
                                            <?= date('M d, Y', strtotime($app->endDate)) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong><?= $app->noOfDays ?: Leave::countWeekdays($app->startDate, $app->endDate) ?></strong> days
                                    </td>
                                    <td>
                                        <span class="badge bg-warning-transparent">
                                            <i class="ri-time-line me-1"></i>Pending
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="?s=<?= $s ?>&ss=<?= $ss ?>&p=<?= $p ?>&view=detail&applicationID=<?= $app->leaveApplicationID ?>"
                                               class="btn btn-sm btn-info-light"
                                               data-bs-toggle="tooltip"
                                               title="View Details">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            <button class="btn btn-sm btn-success-light approveLeaveBtn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#approveLeaveModal"
                                                    data-date="<?= $app->startDate ?>"
                                                    data-user-friendly-date="<?= date('D F j, Y', strtotime($app->startDate)) ?>"
                                                    data-leave-application-id="<?= $app->leaveApplicationID ?>"
                                                    data-employee-id="<?= $app->employeeID ?>"
                                                    data-leave-type-id="<?= $app->leaveTypeID ?>"
                                                    data-leave-type-name="<?= htmlspecialchars($app->leaveTypeName) ?>"
                                                    data-employee-name="<?= htmlspecialchars($app->employeeName) ?>"
                                                    data-leave-period-id="<?= $app->leavePeriodID ?>"
                                                    data-leave-approver-id="<?= $userDetails->ID ?>"
                                                    data-leave-approver-name="<?= htmlspecialchars($userDetails->FirstName . ' ' . $userDetails->Surname) ?>"
                                                    data-leave-status="approved"
                                                    data-leave-status-id="6"
                                                    data-bs-toggle="tooltip"
                                                    title="Approve">
                                                <i class="ri-checkbox-circle-line"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger-light approveLeaveBtn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#approveLeaveModal"
                                                    data-date="<?= $app->startDate ?>"
                                                    data-user-friendly-date="<?= date('D F j, Y', strtotime($app->startDate)) ?>"
                                                    data-leave-application-id="<?= $app->leaveApplicationID ?>"
                                                    data-employee-id="<?= $app->employeeID ?>"
                                                    data-leave-type-id="<?= $app->leaveTypeID ?>"
                                                    data-leave-type-name="<?= htmlspecialchars($app->leaveTypeName) ?>"
                                                    data-employee-name="<?= htmlspecialchars($app->employeeName) ?>"
                                                    data-leave-period-id="<?= $app->leavePeriodID ?>"
                                                    data-leave-approver-id="<?= $userDetails->ID ?>"
                                                    data-leave-approver-name="<?= htmlspecialchars($userDetails->FirstName . ' ' . $userDetails->Surname) ?>"
                                                    data-leave-status="rejected"
                                                    data-leave-status-id="4"
                                                    data-bs-toggle="tooltip"
                                                    title="Reject">
                                                <i class="ri-close-circle-line"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="ri-checkbox-circle-line text-success" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">All Caught Up!</h5>
                        <p class="text-muted">No pending leave requests at this time.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="ri-history-line text-primary me-2"></i>
                    Recent Activity
                </h5>
            </div>
            <div class="card-body">
                <?php
                $recentActivity = array_merge($approvedApplications, $rejectedApplications);
                usort($recentActivity, function($a, $b) {
                    return strtotime($b->DateAdded ?? '0') - strtotime($a->DateAdded ?? '0');
                });
                $recentActivity = array_slice($recentActivity, 0, 5);

                if (!empty($recentActivity)): ?>
                    <div class="activity-timeline">
                        <?php foreach ($recentActivity as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon bg-<?= $activity->leaveStatusID == 6 ? 'success' : 'danger' ?>-transparent">
                                <i class="ri-<?= $activity->leaveStatusID == 6 ? 'checkbox' : 'close' ?>-circle-line text-<?= $activity->leaveStatusID == 6 ? 'success' : 'danger' ?>"></i>
                            </div>
                            <div class="activity-content">
                                <p class="mb-1">
                                    <strong><?= htmlspecialchars($activity->employeeName) ?></strong>
                                    <span class="badge bg-<?= $activity->leaveStatusID == 6 ? 'success' : 'danger' ?>-transparent">
                                        <?= $activity->leaveStatusID == 6 ? 'Approved' : 'Rejected' ?>
                                    </span>
                                </p>
                                <p class="text-muted small mb-0">
                                    <?= htmlspecialchars($activity->leaveTypeName) ?> •
                                    <?= date('M d - d, Y', strtotime($activity->startDate)) ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center mb-0">No recent activity</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Analytics & Insights -->
    <div class="col-xl-4 col-lg-5">
        <!-- Team Availability Chart -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="ri-pie-chart-line text-primary me-2"></i>
                    Team Availability (Next 30 Days)
                </h6>
            </div>
            <div class="card-body">
                <canvas id="teamAvailabilityChart" height="200"></canvas>
            </div>
        </div>

        <!-- Upcoming Leave -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="ri-calendar-event-line text-primary me-2"></i>
                    Upcoming Leave
                </h6>
            </div>
            <div class="card-body">
                <?php
                $upcomingLeave = array_filter($approvedApplications, function($app) {
                    return strtotime($app->startDate) >= strtotime('today');
                });
                usort($upcomingLeave, function($a, $b) {
                    return strtotime($a->startDate) - strtotime($b->startDate);
                });
                $upcomingLeave = array_slice($upcomingLeave, 0, 5);

                if (!empty($upcomingLeave)): ?>
                    <?php foreach ($upcomingLeave as $leave):
                        $initials = Core::get_user_name_initials($leave->employeeID, $DBConn);
                        $daysUntil = ceil((strtotime($leave->startDate) - time()) / 86400);
                    ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar avatar-sm bg-primary-transparent rounded-circle me-2">
                            <span class="avatar-initials small"><?= $initials['initials'] ?></span>
                        </div>
                        <div class="flex-fill">
                            <p class="mb-0"><strong><?= htmlspecialchars($leave->employeeName) ?></strong></p>
                            <small class="text-muted">
                                <?= date('M d', strtotime($leave->startDate)) ?> -
                                <?= date('M d', strtotime($leave->endDate)) ?>
                                <span class="badge bg-light text-dark ms-1"><?= $daysUntil ?> days</span>
                            </small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center mb-0">No upcoming leave</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Critical Dates Alert -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="ri-alert-line text-danger me-2"></i>
                    Staffing Alerts
                </h6>
            </div>
            <div class="card-body">
                <div id="staffingAlerts">
                    <!-- Populated by JavaScript -->
                    <p class="text-muted text-center small mb-0">Analyzing team availability...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Team Availability Chart
    const teamSize = <?= $totalTeamSize ?>;

    // Count team members currently on leave (today)
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const onLeaveToday = <?= json_encode(array_filter($approvedApplications, function($app) {
        $today = strtotime('today');
        return strtotime($app->startDate) <= $today && strtotime($app->endDate) >= $today;
    })) ?>;

    const onLeave = Object.keys(onLeaveToday).length;
    const available = teamSize - onLeave;

    const ctx = document.getElementById('teamAvailabilityChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Available', 'On Leave'],
                datasets: [{
                    data: [available, onLeave],
                    backgroundColor: ['#28a745', '#ffc107'],
                    borderWidth: 0
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
    }

    // Select all checkbox
    document.getElementById('selectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.application-checkbox').forEach(cb => {
            cb.checked = this.checked;
        });
    });

    // Analyze staffing conflicts
    analyzeStaffingConflicts();
});

function analyzeStaffingConflicts() {
    const approvedApps = <?= json_encode($approvedApplications) ?>;
    const teamSize = <?= $totalTeamSize ?>;
    const alertContainer = document.getElementById('staffingAlerts');

    if (!alertContainer) return;

    // Group by date
    const dateMap = {};
    approvedApps.forEach(app => {
        const start = new Date(app.startDate);
        const end = new Date(app.endDate);

        for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
            const dateStr = d.toISOString().split('T')[0];
            if (!dateMap[dateStr]) dateMap[dateStr] = 0;
            dateMap[dateStr]++;
        }
    });

    // Find critical dates (>30% absence)
    const criticalDates = Object.entries(dateMap)
        .filter(([date, count]) => count / teamSize > 0.3)
        .sort((a, b) => new Date(a[0]) - new Date(b[0]))
        .slice(0, 3);

    if (criticalDates.length > 0) {
        alertContainer.innerHTML = criticalDates.map(([date, count]) => {
            const percent = Math.round((count / teamSize) * 100);
            const severity = percent > 50 ? 'danger' : 'warning';
            return `
                <div class="alert alert-${severity}-transparent mb-2">
                    <strong>${new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}</strong>
                    <br>
                    <small>${count} of ${teamSize} members (${percent}%) on leave</small>
                </div>
            `;
        }).join('');
    } else {
        alertContainer.innerHTML = `
            <div class="alert alert-success-transparent mb-0">
                <i class="ri-checkbox-circle-line me-1"></i>
                <small>No critical staffing issues detected</small>
            </div>
        `;
    }
}
</script>

