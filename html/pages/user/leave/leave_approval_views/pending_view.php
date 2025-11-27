<!-- Pending Requests View -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <h5 class="mb-0">
                        <i class="ri-time-line text-warning me-2"></i>
                        Pending Leave Requests (<?= count($pendingApplications) ?>)
                    </h5>
                    <div class="d-flex gap-2">
                        <?php if (count($pendingApplications) > 1): ?>
                        <button class="btn btn-success btn-sm" id="approveAllBtn">
                            <i class="ri-checkbox-line me-1"></i>Approve All
                        </button>
                        <?php endif; ?>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-view="grid" id="gridViewBtn">
                                <i class="ri-layout-grid-line"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary active" data-view="list" id="listViewBtn">
                                <i class="ri-list-check"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($pendingApplications)): ?>
                    <!-- List View -->
                    <div id="listView">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" class="form-check-input" id="selectAllPending">
                                        </th>
                                        <th>Employee</th>
                                        <th>Leave Type</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Days</th>
                                        <th>Requested On</th>
                                        <th>Handover</th>
                                        <th class="text-center">Team Impact</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingApplications as $app):
                                        $initials = Core::get_user_name_initials($app->employeeID, $DBConn);
                                        $urgency = 'normal';
                                        $daysUntilStart = ceil((strtotime($app->startDate) - time()) / 86400);
                                        if ($daysUntilStart <= 3) $urgency = 'urgent';
                                        elseif ($daysUntilStart <= 7) $urgency = 'soon';
                                    ?>
                                    <tr class="<?= $urgency == 'urgent' ? 'table-warning' : '' ?>">
                                        <td>
                                            <input type="checkbox" class="form-check-input pending-checkbox"
                                                   data-application-id="<?= $app->leaveApplicationID ?>">
                                        </td>
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
                                            <?= date('M d, Y', strtotime($app->startDate)) ?>
                                            <?php if ($urgency == 'urgent'): ?>
                                                <br><span class="badge bg-danger-transparent small">Urgent</span>
                                            <?php elseif ($urgency == 'soon'): ?>
                                                <br><span class="badge bg-warning-transparent small">Soon</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($app->endDate)) ?></td>
                                        <td><strong><?= $app->noOfDays ?: Leave::countWeekdays($app->startDate, $app->endDate) ?></strong></td>
                                        <td>
                                            <small class="text-muted">
                                                <?= date('M d, Y', strtotime($app->DateAdded)) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php if (($app->handoverRequired ?? 'N') === 'Y'): ?>
                                                <?php
                                                    $handoverStatus = $app->handoverStatus ?? 'pending';
                                                    $handoverClass = match ($handoverStatus) {
                                                        'completed' => 'bg-success',
                                                        'partial' => 'bg-warning text-dark',
                                                        'in_progress' => 'bg-info',
                                                        default => 'bg-secondary'
                                                    };
                                                ?>
                                                <span class="badge <?= $handoverClass ?> text-uppercase mb-1">
                                                    <?= htmlspecialchars($handoverStatus) ?>
                                                </span>
                                                <div>
                                                    <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=handover_report&applicationID={$app->leaveApplicationID}" ?>"
                                                       class="btn btn-link btn-sm ps-0">
                                                        View report
                                                    </a>
                                                </div>
                                            <?php else: ?>
                                                <small class="text-muted">Not required</small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="team-impact-indicator"
                                                  data-start-date="<?= $app->startDate ?>"
                                                  data-end-date="<?= $app->endDate ?>">
                                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-success approveLeaveBtn"
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
                                                        title="Approve">
                                                    <i class="ri-check-line"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger approveLeaveBtn"
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
                                                        title="Reject">
                                                    <i class="ri-close-line"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Grid View -->
                    <div id="gridView" class="d-none">
                        <div class="row">
                            <?php foreach ($pendingApplications as $app):
                                $initials = Core::get_user_name_initials($app->employeeID, $DBConn);
                                $urgency = 'normal';
                                $daysUntilStart = ceil((strtotime($app->startDate) - time()) / 86400);
                                if ($daysUntilStart <= 3) $urgency = 'urgent';
                                elseif ($daysUntilStart <= 7) $urgency = 'soon';
                            ?>
                            <div class="col-xl-4 col-lg-6 col-md-6 mb-3">
                                <div class="card leave-request-card border-<?= $urgency == 'urgent' ? 'danger' : ($urgency == 'soon' ? 'warning' : '') ?>">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start mb-3">
                                            <div class="avatar avatar-md bg-primary-transparent rounded-circle me-3">
                                                <span class="avatar-initials"><?= $initials['initials'] ?></span>
                                            </div>
                                            <div class="flex-fill">
                                                <h6 class="mb-1"><?= htmlspecialchars($app->employeeName) ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars($app->jobTitle ?? 'N/A') ?></small>
                                            </div>
                                            <?php if ($urgency == 'urgent'): ?>
                                                <span class="badge bg-danger">Urgent</span>
                                            <?php elseif ($urgency == 'soon'): ?>
                                                <span class="badge bg-warning">Soon</span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="mb-3">
                                            <span class="badge mb-2" style="background-color: <?= $app->leaveColor ?? '#6c757d' ?>;">
                                                <?= htmlspecialchars($app->leaveTypeName) ?>
                                            </span>
                                            <p class="mb-1">
                                                <i class="ri-calendar-line me-1"></i>
                                                <?= date('M d', strtotime($app->startDate)) ?> -
                                                <?= date('M d, Y', strtotime($app->endDate)) ?>
                                            </p>
                                            <p class="mb-0">
                                                <i class="ri-time-line me-1"></i>
                                                <strong><?= $app->noOfDays ?: Leave::countWeekdays($app->startDate, $app->endDate) ?></strong> days
                                            </p>
                                        </div>
                                        <div class="mb-3">
                                            <small class="text-muted d-block mb-1">Handover</small>
                                            <?php if (($app->handoverRequired ?? 'N') === 'Y'): ?>
                                                <?php
                                                    $handoverStatus = $app->handoverStatus ?? 'pending';
                                                    $handoverClass = match ($handoverStatus) {
                                                        'completed' => 'bg-success',
                                                        'partial' => 'bg-warning text-dark',
                                                        'in_progress' => 'bg-info',
                                                        default => 'bg-secondary'
                                                    };
                                                ?>
                                                <span class="badge <?= $handoverClass ?>"><?= htmlspecialchars($handoverStatus) ?></span>
                                                <a class="d-block small mt-1"
                                                   href="<?= "{$base}html/?s={$s}&ss={$ss}&p=handover_report&applicationID={$app->leaveApplicationID}" ?>">
                                                    View report
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted small">Not required</span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="d-grid gap-2">
                                            <button class="btn btn-success btn-sm approveLeaveBtn"
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
                                                    data-leave-status-id="6">
                                                <i class="ri-checkbox-circle-line me-1"></i>Approve
                                            </button>
                                            <button class="btn btn-outline-danger btn-sm approveLeaveBtn"
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
                                                    data-leave-status-id="4">
                                                <i class="ri-close-circle-line me-1"></i>Reject
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="ri-checkbox-circle-line text-success" style="font-size: 4rem;"></i>
                        <h4 class="mt-3">All Caught Up!</h4>
                        <p class="text-muted">No pending leave requests require your attention.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View switcher
    const gridViewBtn = document.getElementById('gridViewBtn');
    const listViewBtn = document.getElementById('listViewBtn');
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');

    gridViewBtn?.addEventListener('click', function() {
        gridView.classList.remove('d-none');
        listView.classList.add('d-none');
        gridViewBtn.classList.add('active');
        listViewBtn.classList.remove('active');
    });

    listViewBtn?.addEventListener('click', function() {
        listView.classList.remove('d-none');
        gridView.classList.add('d-none');
        listViewBtn.classList.add('active');
        gridViewBtn.classList.remove('active');
    });

    // Select all
    document.getElementById('selectAllPending')?.addEventListener('change', function() {
        document.querySelectorAll('.pending-checkbox').forEach(cb => {
            cb.checked = this.checked;
        });
    });

    // Calculate team impact indicators
    const approvedApps = <?= json_encode($approvedApplications) ?>;
    const teamSize = <?= $totalTeamSize ?>;

    document.querySelectorAll('.team-impact-indicator').forEach(indicator => {
        const startDate = indicator.dataset.startDate;
        const endDate = indicator.dataset.endDate;

        let overlapping = 0;
        approvedApps.forEach(app => {
            const appStart = new Date(app.startDate);
            const appEnd = new Date(app.endDate);
            const reqStart = new Date(startDate);
            const reqEnd = new Date(endDate);

            if (!(reqEnd < appStart || reqStart > appEnd)) {
                overlapping++;
            }
        });

        const totalOnLeave = overlapping + 1; // Including this request
        const percent = Math.round((totalOnLeave / teamSize) * 100);

        let badgeClass = 'success';
        let icon = 'ri-check-line';
        if (percent > 50) {
            badgeClass = 'danger';
            icon = 'ri-alert-line';
        } else if (percent > 30) {
            badgeClass = 'warning';
            icon = 'ri-error-warning-line';
        }

        indicator.innerHTML = `
            <span class="badge bg-${badgeClass}-transparent" data-bs-toggle="tooltip"
                  title="${totalOnLeave} of ${teamSize} members (${percent}%) will be on leave">
                <i class="${icon} me-1"></i>${percent}%
            </span>
        `;
    });

    // Initialize tooltips
    const tooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltips.map(el => new bootstrap.Tooltip(el));
});
</script>

<style>
.leave-request-card {
    transition: all 0.3s ease;
}

.leave-request-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.table-warning {
    background-color: #fff3cd !important;
}
</style>

