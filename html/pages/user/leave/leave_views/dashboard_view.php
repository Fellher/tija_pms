<?php
/**
 * Leave Dashboard View
 *
 * Provides comprehensive dashboard with leave balances, upcoming leaves,
 * team overview, and quick actions
 */

// Get additional data for dashboard
$upcomingLeaves = array_filter($myLeaveApplications, function($app) {
    return (int)$app->leaveStatusID === 6 && strtotime($app->startDate) > time();
});

$scheduledLeaves = array_filter($myLeaveApplications, function($app) {
    return (int)$app->leaveStatusID === 1;
});

$recentApplications = array_slice($myLeaveApplications, 0, 5);

$isHRManager = $hrManagerScope['isHRManager'] ?? false;
$hrManagerScopes = $hrManagerScope['scopes'] ?? array();
$canManageHandoverAdmin = $isHRManager
    || ($isAdmin ?? false)
    || ($isValidAdmin ?? false)
    || ($isSuperAdmin ?? false);

// Get team leave data if user is a manager or HR manager
$teamLeaveData = [];
if ($directReport || $departmentHead || $isHRManager) {
    $teamLeaveData = Leave::get_team_leave_overview($userDetails->ID, $DBConn, $hrManagerScope);
}
?>

<!-- Quick Action Buttons -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card leave-summary-card h-100">
            <div class="card-body text-center">
                <div class="leave-type-icon mx-auto mb-3">
                    <i class="ri-calendar-add-line"></i>
                </div>
                <h5 class="card-title">Apply Leave</h5>
                <p class="card-text text-muted">Submit a new leave application</p>
                <button type="button" class="btn btn-primary leave-action-btn" data-action="open-apply-leave-modal">
                    <!-- <a href="<?= "{$base}html/?s=user&ss=leave&p=apply_leave_workflow" ?>" class="btn btn-primary leave-action-btn"> -->
                    <i class="ri-calendar-add-line me-1"></i>Apply Now
                    <!-- </a> -->
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card leave-summary-card h-100">
            <div class="card-body text-center">
                <div class="leave-type-icon mx-auto mb-3">
                    <i class="ri-calendar-line"></i>
                </div>
                <h5 class="card-title">Leave Calendar</h5>
                <p class="card-text text-muted">View team leave calendar</p>
                <button type="button" class="btn btn-info leave-action-btn" data-action="open-leave-calendar-modal">
                    <i class="ri-calendar-line me-1"></i>View Calendar
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card leave-summary-card h-100">
            <div class="card-body text-center">
                <div class="leave-type-icon mx-auto mb-3">
                    <i class="ri-user-settings-line"></i>
                </div>
                <h5 class="card-title">Approvals</h5>
                <p class="card-text text-muted">Manage leave approvals</p>
                <button type="button" class="btn btn-warning leave-action-btn" data-action="open-approval-workflow-modal">
                    <i class="ri-user-settings-line me-1"></i>View Approvals
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Dashboard View Container -->
<div class="row">
    <!-- Leave Balance Overview -->
    <div class="col-xl-8 col-lg-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ri-dashboard-line me-2 text-primary"></i>
                        Leave Balance Overview
                    </h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary active" data-action="switch-balance-view" data-view="current">
                            Current
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-action="switch-balance-view" data-view="projected">
                            Projected
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if ($leaveEntitlements): ?>
                        <?php foreach ($leaveEntitlements as $entitlement):

                        //calculate the used days

                            // var_dump($entitlement);?>
                            <?php if ($entitlement->leaveSegment && strtolower($entitlement->leaveSegment) != strtolower($employeeDetails->gender)) continue; ?>

                            <div class="col-md-4 col-sm-6 mb-4">
                                <div class="leave-balance-card">
                                    <div class="text-center">
                                        <div class="leave-type-icon mb-3">
                                            <?php
                                            $iconClass = 'ri-calendar-line';
                                            $iconColor = 'text-primary';

                                            // Initialize balance variables with default values
                                            $usedDays = 0;
                                            $availableDays = 0;
                                            $totalDays = 0;

                                            //convert leaveTypeName to lowercase and replace spaces with underscore
                                            $leaveTypeName = strtolower(str_replace(' ', '_', $entitlement->leaveTypeName));

                                            // Get balance data if available
                                            if (isset($leaveBalances[$leaveTypeName])) {
                                                $usedDays = $leaveBalances[$leaveTypeName]['used'] ?? 0;
                                                $availableDays = $leaveBalances[$leaveTypeName]['available'] ?? 0;
                                                $totalDays = $leaveBalances[$leaveTypeName]['total'] ?? 0;
                                            }

                                            switch(strtolower($leaveTypeName)) {
                                                case 'annual_leave':
                                                case 'vacation':
                                                    $iconClass = 'ri-calendar-check-line';
                                                    $iconColor = 'text-primary';
                                                    break;
                                                case 'sick_leave':
                                                case 'medical':
                                                    $iconClass = 'ri-heart-pulse-line';
                                                    $iconColor = 'text-success';
                                                    break;
                                                case 'maternity_leave':
                                                    $iconClass = 'ri-parent-line';
                                                    $iconColor = 'text-info';
                                                    break;
                                                case 'paternity_leave':
                                                    $iconClass = 'ri-user-heart-line';
                                                    $iconColor = 'text-warning';
                                                    break;
                                                case 'emergency_leave':
                                                    $iconClass = 'ri-alarm-warning-line';
                                                    $iconColor = 'text-danger';
                                                    break;
                                            }?>
                                            <i class="<?= $iconClass ?> fs-2 <?= $iconColor ?>"></i>
                                        </div>

                                        <h6 class="leave-type-name fw-semibold mb-2">
                                            <?= htmlspecialchars($entitlement->leaveTypeName) ?>
                                        </h6>

                                        <div class="leave-balance-info">
                                            <div class="balance-main">
                                                <span class="available-days fs-3 fw-bold text-primary">
                                                    <?= $availableDays ?>
                                                </span>
                                                <span class="balance-label text-muted">days available</span>
                                            </div>

                                            <div class="balance-details mt-2">
                                                <small class="text-muted">
                                                    <i class="ri-information-line me-1"></i>
                                                    <?= $totalDays ?> total per year
                                                </small>
                                            </div>
                                        </div>

                                        <!-- Progress bar -->
                                        <div class="progress mt-3" style="height: 6px;">
                                            <?php
                                            // Calculate used days if not already set
                                            if (!isset($usedDays) || $usedDays === null) {
                                                $usedDays = ($totalDays > 0) ? ($totalDays - $availableDays) : 0;
                                            }
                                            $percentage = ($totalDays > 0) ? ($usedDays / $totalDays) * 100 : 0;
                                            ?>
                                            <div class="progress-bar bg-<?= $iconColor == 'text-success' ? 'success' : ($iconColor == 'text-info' ? 'info' : 'primary') ?>"
                                                 style="width: <?= $percentage ?>%"></div>
                                        </div>

                                        <!-- Quick action button -->
                                        <div class="mt-3">
                                            <button type="button"
                                                    class="btn btn-outline-primary btn-sm"
                                                    data-action="quick-apply-leave"
                                                    data-leave-type-id="<?= $entitlement->leaveTypeID ?>">
                                                <i class="ri-add-line me-1"></i>Apply
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="ri-calendar-line fs-1 text-muted mb-3"></i>
                                <h5 class="text-muted">No Leave Entitlements</h5>
                                <p class="text-muted">Contact HR to set up your leave entitlements.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Upcoming Leaves -->
    <div class="col-xl-4 col-lg-12">
        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom-0">
                <h5 class="mb-0">
                    <i class="ri-flashlight-line me-2 text-primary"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#applyLeaveModal">
                        <i class="ri-calendar-add-line me-2"></i>Apply for Leave
                    </button>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#leaveCalendarModal">
                        <i class="ri-calendar-line me-2"></i>View Calendar
                    </button>
                    <button type="button" class="btn btn-outline-secondary" data-action="view-leave-history">
                        <i class="ri-history-line me-2"></i>Leave History
                    </button>
                    <button type="button" class="btn btn-outline-info" data-action="download-leave-report">
                        <i class="ri-download-line me-2"></i>Download Report
                    </button>
                    <?php if ($canManageHandoverAdmin): ?>
                    <button type="button" class="btn btn-outline-dark" data-action="open-handover-admin">
                        <i class="ri-briefcase-4-line me-2"></i>Handover Administration
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Upcoming Leaves -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom-0">
                <h5 class="mb-0">
                    <i class="ri-calendar-todo-line me-2 text-primary"></i>
                    Upcoming Leaves
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($upcomingLeaves)): ?>
                    <?php foreach (array_slice($upcomingLeaves, 0, 3) as $leave): ?>
                        <div class="upcoming-leave-item mb-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold"><?= htmlspecialchars($leave->leaveTypeName) ?></h6>
                                    <p class="mb-1 text-muted small">
                                        <i class="ri-calendar-line me-1"></i>
                                        <?= date('M d', strtotime($leave->startDate)) ?> -
                                        <?= date('M d, Y', strtotime($leave->endDate)) ?>
                                    </p>
                                    <span class="badge bg-success-transparent">
                                        <i class="ri-check-line me-1"></i>Approved
                                    </span>
                                </div>
                                <div class="flex-shrink-0">
                                    <span class="leave-days-badge bg-primary text-white rounded-pill px-2 py-1">
                                        <?= $leave->noOfDays ?: Leave::countWeekdays($leave->startDate, $leave->endDate) ?> days
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (count($upcomingLeaves) > 3): ?>
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-outline-primary btn-sm" data-action="view-all-upcoming-leaves">
                                View All (<?= count($upcomingLeaves) ?>)
                            </button>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="ri-calendar-line fs-3 text-muted mb-2"></i>
                        <p class="text-muted mb-0">No upcoming leaves scheduled</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Scheduled Leave Plans -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="ri-draft-line me-2 text-primary"></i>
                    Scheduled Leave Plans
                </h5>
                <?php if (!empty($scheduledLeaves)): ?>
                    <span class="badge bg-info-transparent text-primary fw-semibold">
                        <?= count($scheduledLeaves) ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (!empty($scheduledLeaves)): ?>
                    <?php foreach ($scheduledLeaves as $scheduled): ?>
                        <div class="scheduled-leave-item mb-3 p-3 border rounded">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold"><?= htmlspecialchars($scheduled->leaveTypeName) ?></h6>
                                    <p class="mb-1 text-muted small">
                                        <i class="ri-calendar-event-line me-1"></i>
                                        <?= date('M d', strtotime($scheduled->startDate)) ?> -
                                        <?= date('M d, Y', strtotime($scheduled->endDate)) ?>
                                    </p>
                                    <div class="d-flex flex-wrap gap-2 align-items-center">
                                        <span class="badge bg-secondary-transparent text-secondary">
                                            <i class="ri-time-line me-1"></i>
                                            <?= $scheduled->noOfDays ?: Leave::countWeekdays($scheduled->startDate, $scheduled->endDate) ?> working day(s)
                                        </span>
                                        <span class="badge bg-light text-muted">
                                            Saved on <?= date('M d, Y', strtotime($scheduled->DateAdded)) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 text-end">
                                    <button type="button"
                                            class="btn btn-sm btn-success"
                                            data-action="submit-for-approval"
                                            data-application-id="<?= (int)$scheduled->leaveApplicationID ?>">
                                        <i class="ri-send-plane-line me-1"></i>Submit
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="ri-draft-line fs-3 text-muted mb-2"></i>
                        <p class="text-muted mb-0">No leave plans saved. Schedule a leave from the application form.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Applications -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ri-file-list-line me-2 text-primary"></i>
                        Recent Applications
                    </h5>
                    <a href="?s=user&ss=leave&p=leave_management_enhanced&view=history" class="btn btn-outline-primary btn-sm">
                        View All
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($recentApplications)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Leave Type</th>
                                    <th>Period</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                    <th>Applied Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($recentApplications as $application): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="leave-type-icon me-2 shrink-0 mb-0">
                                                    <?php
                                                    $iconClass = 'ri-calendar-line';
                                                    switch(strtolower($application->leaveTypeName)) {
                                                        case 'annual':
                                                        case 'vacation':
                                                            $iconClass = 'ri-calendar-check-line';
                                                            break;
                                                        case 'sick':
                                                        case 'medical':
                                                            $iconClass = 'ri-heart-pulse-line';
                                                            break;
                                                    }
                                                    ?>
                                                    <i class="<?= $iconClass ?> text-primary"></i>
                                                </div>
                                                <div class="flex-grow-1 d-flex align-items-center">
                                                    <?= htmlspecialchars($application->leaveTypeName) ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="date-range">
                                                <div class="start-date fw-medium">
                                                    <?= date('M d', strtotime($application->startDate)) ?>
                                                </div>
                                                <div class="end-date text-muted small">
                                                    to <?= date('M d, Y', strtotime($application->endDate)) ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <?= $application->noOfDays ?: Leave::countWeekdays($application->startDate, $application->endDate) ?> days
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $statusClass = '';
                                            $statusIcon = '';
                                            $statusId = (int)$application->leaveStatusID;
                                            switch($statusId) {
                                                case 1:
                                                    $statusClass = 'status-draft';
                                                    $statusIcon = 'ri-calendar-todo-line';
                                                    break;
                                                case 2:
                                                    $statusClass = 'status-pending';
                                                    $statusIcon = 'ri-send-plane-line';
                                                    break;
                                                case 3:
                                                    $statusClass = 'status-pending';
                                                    $statusIcon = 'ri-time-line';
                                                    break;
                                                case 4:
                                                    $statusClass = 'status-rejected';
                                                    $statusIcon = 'ri-close-line';
                                                    break;
                                                case 5:
                                                    $statusClass = 'status-rejected';
                                                    $statusIcon = 'ri-close-circle-line';
                                                    break;
                                                case 6:
                                                    $statusClass = 'status-approved';
                                                    $statusIcon = 'ri-check-line';
                                                    break;
                                            }
                                            ?>
                                            <span class="leave-status-badge <?= $statusClass ?>">
                                                <i class="<?= $statusIcon ?> me-1"></i>
                                                <?= htmlspecialchars($application->leaveStatusName) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-muted">
                                                <?= date('M d, Y', strtotime($application->DateAdded)) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-primary"
                                                        data-action="view-application-details"
                                                        data-application-id="<?= $application->leaveApplicationID ?>"
                                                        title="View Details">
                                                    <i class="ri-eye-line"></i>
                                                </button>

                                                <?php if (in_array($statusId, [1, 2], true)): ?>
                                                    <button type="button" class="btn btn-outline-secondary"
                                                            data-action="edit-application"
                                                            data-application-id="<?= $application->leaveApplicationID ?>"
                                                            title="Edit">
                                                        <i class="ri-edit-line"></i>
                                                    </button>
                                                <?php endif; ?>

                                                <?php if (in_array($statusId, [1, 2], true)): ?>
                                                    <button type="button" class="btn btn-outline-success"
                                                            data-action="submit-for-approval"
                                                            data-application-id="<?= $application->leaveApplicationID ?>"
                                                            title="Submit for Approval">
                                                        <i class="ri-send-plane-line"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="ri-file-list-line fs-1 text-muted mb-3"></i>
                        <h5 class="text-muted">No Applications Yet</h5>
                        <p class="text-muted">You haven't submitted any leave applications yet.</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#applyLeaveModal">
                            <i class="ri-add-line me-1"></i>Apply for Leave
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Team Overview (for managers) -->
<?php if (!empty($teamLeaveData)): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0">
                <h5 class="mb-0">
                    <i class="ri-team-line me-2 text-primary"></i>
                    Team Leave Overview
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($teamLeaveData as $member): ?>
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="team-member-card p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1 fw-semibold"><?= htmlspecialchars($member->firstName . ' ' . $member->lastName) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($member->jobTitle ?? 'Employee') ?></small>
                                    </div>
                                    <span class="badge bg-primary-transparent">
                                        <?= $member->availableDays ?? 0 ?> days
                                    </span>
                                </div>

                                <div class="member-stats">
                                    <div class="d-flex justify-content-between small text-muted mb-1">
                                        <span>Used this year:</span>
                                        <span><?= $member->usedDays ?? 0 ?> days</span>
                                    </div>
                                    <div class="d-flex justify-content-between small text-muted">
                                        <span>Pending requests:</span>
                                        <span><?= $member->pendingRequests ?? 0 ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Dashboard JavaScript -->
<script>
/**
 * Dashboard View Specific Functionality
 */
/*
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-action="open-apply-leave-modal"]').forEach(button => {
        button.addEventListener('click', () => {
            if (typeof openApplyLeaveModal === 'function') {
                openApplyLeaveModal();
            }
        });
    });

    document.querySelectorAll('[data-action="open-leave-calendar-modal"]').forEach(button => {
        button.addEventListener('click', () => {
            if (typeof openLeaveCalendarModal === 'function') {
                openLeaveCalendarModal();
            }
        });
    });

    document.querySelectorAll('[data-action="open-approval-workflow-modal"]').forEach(button => {
        button.addEventListener('click', () => {
            if (typeof openApprovalWorkflowModal === 'function') {
                console.log(`Opening approval workflow modal from dashboard view`);
                openApprovalWorkflowModal();
            }
        });
    });

    document.querySelectorAll('[data-action="switch-balance-view"]').forEach(button => {
        button.addEventListener('click', () => {
            const view = button.dataset.view || 'current';
            switchBalanceView(view, button);
        });
    });

    document.querySelectorAll('[data-action="quick-apply-leave"]').forEach(button => {
        button.addEventListener('click', () => {
            const leaveTypeId = button.dataset.leaveTypeId;
            if (typeof quickApplyLeave === 'function') {
                quickApplyLeave(leaveTypeId);
            }
        });
    });

    document.querySelectorAll('[data-action="view-leave-history"]').forEach(button => {
        button.addEventListener('click', () => {
            if (typeof viewLeaveHistory === 'function') {
                viewLeaveHistory();
            }
        });
    });

    document.querySelectorAll('[data-action="download-leave-report"]').forEach(button => {
        button.addEventListener('click', () => {
            if (typeof downloadLeaveReport === 'function') {
                downloadLeaveReport();
            }
        });
    });

    document.querySelectorAll('[data-action="view-all-upcoming-leaves"]').forEach(button => {
        button.addEventListener('click', () => {
            if (typeof viewAllUpcomingLeaves === 'function') {
                viewAllUpcomingLeaves();
            }
        });
    });

    document.querySelectorAll('[data-action="submit-for-approval"]').forEach(button => {
        button.addEventListener('click', () => {
            const applicationId = Number(button.dataset.applicationId);
            if (typeof submitForApproval === 'function') {
                submitForApproval(applicationId);
            }
        });
    });

    document.querySelectorAll('[data-action="view-application-details"]').forEach(button => {
        button.addEventListener('click', () => {
            const applicationId = Number(button.dataset.applicationId);
            if (typeof viewApplicationDetails === 'function') {
                viewApplicationDetails(applicationId);
            }
        });
    });

    document.querySelectorAll('[data-action="edit-application"]').forEach(button => {
        button.addEventListener('click', () => {
            const applicationId = Number(button.dataset.applicationId);
            if (typeof editApplication === 'function') {
                editApplication(applicationId);
            }
        });
    });
});

// Switch balance view (current vs projected)
function switchBalanceView(view, trigger) {
    const buttons = document.querySelectorAll('[data-action="switch-balance-view"]');
    buttons.forEach(btn => btn.classList.remove('active'));

    if (trigger) {
        trigger.classList.add('active');
    } else {
        const fallback = Array.from(buttons).find(btn => btn.dataset.view === view);
        if (fallback) {
            fallback.classList.add('active');
        }
    }

    // Update balance display based on view
    console.log('Switching to', view, 'view');
    // Implementation would update the balance calculations
}

// Quick apply leave for specific type
function quickApplyLeave(leaveTypeId) {
    if (typeof openApplyLeaveModal === 'function') {
        openApplyLeaveModal(leaveTypeId);
        return;
    }

    if (window.LeaveManagement && typeof window.LeaveManagement.openApplyLeaveModal === 'function') {
        window.LeaveManagement.openApplyLeaveModal(leaveTypeId);
        return;
    }

    const modalElement = document.getElementById('applyLeaveModal');
    if (!modalElement) {
        return;
    }

    if (leaveTypeId) {
        modalElement.dataset.pendingLeaveTypeId = leaveTypeId;
    }

    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
    modalInstance.show();
}

// View leave history
function viewLeaveHistory() {
    window.location.href = '?s=user&ss=leave&p=leave_management_enhanced&view=history';
}

// Download leave report
function downloadLeaveReport() {
    showToast('info', 'Generating Report', 'Your leave report is being generated...');

    // Simulate report generation
    setTimeout(() => {
        showToast('success', 'Report Ready', 'Your leave report has been generated and is ready for download.');
        // In a real implementation, this would trigger a download
    }, 2000);
}

// View application details
function viewApplicationDetails(applicationId) {
    // Open application details modal
    console.log('Viewing application:', applicationId);
    // Implementation would open a details modal
}

// Edit application
function editApplication(applicationId) {
    // Open edit modal with application data
    console.log('Editing application:', applicationId);
    // Implementation would open the edit modal
}

// Submit for approval
function submitForApproval(applicationId) {
    if (confirm('Submit this leave plan for approval now?')) {
        showToast('info', 'Submitting...', 'Promoting your leave plan to an application.');

        const formData = new FormData();
        formData.append('applicationId', applicationId);

        fetch('<?= $base ?>php/scripts/leave/applications/promote_scheduled_leave.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const message = data.notificationsSent
                    ? 'Your application has been submitted and approvers were notified.'
                    : 'Your application has been submitted for approval.';
                showToast('success', 'Submitted', message);
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast('error', 'Submission Failed', data.message || 'Failed to submit application.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Network Error', 'Unable to submit application. Please try again.');
        });
    }
} */

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-action="open-handover-admin"]').forEach(button => {
        button.addEventListener('click', () => {
            window.location.href = '<?= "{$base}html/?s=admin&ss=leave&p=handover_policies" ?>';
        });
    });
});

// View all upcoming leaves
function viewAllUpcomingLeaves() {
    window.location.href = '?s=user&ss=leave&p=leave_management_enhanced&view=history&filter=upcoming';
}
</script>

