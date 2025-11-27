<?php
/**
 * Leave Approval Dashboard - Enterprise Edition
 *
 * Features:
 * - Calendar view of team leave requests
 * - Conflict detection (understaffing alerts)
 * - Role-based insights
 * - Batch approval capabilities
 * - Analytics and statistics
 */

if(!$isValidUser) {
    Alert::info("You need to be logged in to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

$employeeID = (isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) : $userDetails->ID;
$employeeDetails = Employee::employees(array('ID'=>$employeeID), true, $DBConn);
$orgDataID = isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : $employeeDetails->orgDataID;
$entityID = isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : $employeeDetails->entityID;
$applicationID = isset($_GET['applicationID']) ? Utility::clean_string($_GET['applicationID']) : null;
$view = isset($_GET['view']) ? Utility::clean_string($_GET['view']) : 'dashboard';

// Get direct reports
$directReports = Employee::employees(['supervisorID'=>$userDetails->ID, 'Suspended'=>'N'], false, $DBConn);

// var_dump($directReports);

// Get all pending leave applications for team
$pendingApplications = [];
$approvedApplications = [];
$rejectedApplications = [];
$teamMembers = [];

if ($directReports && is_array($directReports)) {
    foreach ($directReports as $report) {
        $teamMembers[] = $report->ID;
        $applications = Leave::leave_applications_full(['employeeID'=>$report->ID], false, $DBConn);

        if ($applications && is_array($applications)) {
            foreach ($applications as $app) {
                $statusId = (int)$app->leaveStatusID;
                if (in_array($statusId, [2, 3], true)) { // Pending / Submitted
                    $pendingApplications[] = $app;
                } elseif ($statusId === 6) { // Approved
                    $approvedApplications[] = $app;
                } elseif ($statusId === 4) { // Rejected
                    $rejectedApplications[] = $app;
                }
            }
        }
    }
}

// Calculate statistics
$totalPending = count($pendingApplications);
$totalApproved = count($approvedApplications);
$totalRejected = count($rejectedApplications);
$totalTeamSize = count($directReports ?: []);

$getString .= "&uid={$employeeID}&orgDataID={$orgDataID}&entityID={$entityID}&view={$view}";
?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-medium fs-24 mb-0">
            <i class="ri-task-line me-2 text-primary"></i>
            Leave Approval Center
        </h1>
        <p class="text-muted mb-0 mt-1">Manage and approve team leave requests efficiently</p>
    </div>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $s ?></a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $ss ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Leave Approvals</li>
            </ol>
        </nav>
    </div>
</div>

<!-- View Switcher -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="btn-group w-100" role="group">
                    <a href="?s=<?= $s ?>&ss=<?= $ss ?>&p=<?= $p ?>&view=dashboard"
                       class="btn btn-<?= $view == 'dashboard' ? 'primary' : 'outline-primary' ?>">
                        <i class="ri-dashboard-line me-2"></i>Dashboard
                    </a>
                    <a href="?s=<?= $s ?>&ss=<?= $ss ?>&p=<?= $p ?>&view=calendar"
                       class="btn btn-<?= $view == 'calendar' ? 'primary' : 'outline-primary' ?>">
                        <i class="ri-calendar-line me-2"></i>Calendar View
                    </a>
                    <a href="?s=<?= $s ?>&ss=<?= $ss ?>&p=<?= $p ?>&view=pending"
                       class="btn btn-<?= $view == 'pending' ? 'primary' : 'outline-primary' ?>">
                        <i class="ri-time-line me-2"></i>Pending (<?= $totalPending ?>)
                    </a>
                    <a href="?s=<?= $s ?>&ss=<?= $ss ?>&p=<?= $p ?>&view=team"
                       class="btn btn-<?= $view == 'team' ? 'primary' : 'outline-primary' ?>">
                        <i class="ri-team-line me-2"></i>Team Status
                    </a>
                    <a href="?s=<?= $s ?>&ss=<?= $ss ?>&p=<?= $p ?>&view=history"
                       class="btn btn-<?= $view == 'history' ? 'primary' : 'outline-primary' ?>">
                        <i class="ri-history-line me-2"></i>History
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Summary Statistics -->
<div class="row mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card stats-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Pending Approvals</p>
                        <h3 class="mb-0 fw-bold text-warning"><?= $totalPending ?></h3>
                        <small class="text-muted">Require action</small>
                    </div>
                    <div class="stats-icon bg-warning-transparent">
                        <i class="ri-time-line text-warning fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card stats-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Approved (Last 30 days)</p>
                        <h3 class="mb-0 fw-bold text-success"><?= $totalApproved ?></h3>
                        <small class="text-muted">Team members on leave</small>
                    </div>
                    <div class="stats-icon bg-success-transparent">
                        <i class="ri-checkbox-circle-line text-success fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card stats-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Team Size</p>
                        <h3 class="mb-0 fw-bold text-primary"><?= $totalTeamSize ?></h3>
                        <small class="text-muted">Direct reports</small>
                    </div>
                    <div class="stats-icon bg-primary-transparent">
                        <i class="ri-team-line text-primary fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card stats-card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Rejected (Last 30 days)</p>
                        <h3 class="mb-0 fw-bold text-danger"><?= $totalRejected ?></h3>
                        <small class="text-muted">Declined requests</small>
                    </div>
                    <div class="stats-icon bg-danger-transparent">
                        <i class="ri-close-circle-line text-danger fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($view == 'dashboard' || !$view): ?>
    <!-- Dashboard View -->
    <?php include 'leave_approval_views/dashboard_view.php'; ?>

<?php elseif ($view == 'calendar'): ?>
    <!-- Calendar View -->
    <?php include 'leave_approval_views/calendar_view.php'; ?>

<?php elseif ($view == 'pending'): ?>
    <!-- Pending Requests View -->
    <?php include 'leave_approval_views/pending_view.php'; ?>

<?php elseif ($view == 'team'): ?>
    <!-- Team Status View -->
    <?php include 'leave_approval_views/team_status_view.php'; ?>

<?php elseif ($view == 'history'): ?>
    <!-- History View -->
    <?php include 'leave_approval_views/history_view.php'; ?>

<?php endif; ?>

<!-- Approval Modal -->
<?php
echo Utility::form_modal_header('approveLeaveModal', 'leave/applications/process_leave_approval_action.php', 'Leave Approval', array("modal-dialog-centered", "modal-lg"), $base, true);
?>
<div class="modal-body-content">
    <!-- Populated by JavaScript -->
</div>
<?php
echo Utility::form_modal_footer("Approve Leave", "approve_leave_action", 'btn btn-primary btn-sm');
?>

<!-- Batch Approval Modal -->
<?php
echo Utility::form_modal_header('batchApprovalModal', 'leave/applications/batch_approve_leave.php', 'Batch Approval', array("modal-dialog-centered", "modal-xl"), $base, true);
?>
<div class="alert alert-info">
    <i class="ri-information-line me-2"></i>
    <strong>Batch Approval:</strong> Review and approve multiple leave requests at once.
</div>
<div id="batchApprovalContent">
    <!-- Populated by JavaScript -->
</div>
<?php
echo Utility::form_modal_footer("Approve Selected", "batch_approve_action", 'btn btn-primary btn-sm');
?>

<!-- Conflict Alert Modal -->
<div class="modal fade" id="conflictAlertModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="ri-alert-line me-2"></i>Staffing Conflict Detected
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="conflictAlertContent">
                <!-- Populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="viewConflictCalendar">
                    <i class="ri-calendar-line me-1"></i>View Calendar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    'use strict';

    // Team data for conflict detection
    const teamData = <?= json_encode($directReports ?: []) ?>;
    const pendingApplications = <?= json_encode($pendingApplications ?: []) ?>;
    const approvedApplications = <?= json_encode($approvedApplications ?: []) ?>;

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Approve Leave Button Handler
    document.querySelectorAll('.approveLeaveBtn').forEach(function(button) {
        button.addEventListener('click', function() {
            const data = this.dataset;
            const modal = document.getElementById('approveLeaveModal');
            const modalBody = modal.querySelector('.modal-body-content');
            const modalTitle = modal.querySelector('.modal-title');
            const modalFooter = modal.querySelector('.modal-footer');

            const actionText = data.leaveStatus === 'approved' ? 'Approve' : 'Reject';
            const actionColor = data.leaveStatus === 'approved' ? 'success' : 'danger';

            modalTitle.innerHTML = `
                <i class="ri-${data.leaveStatus === 'approved' ? 'checkbox' : 'close'}-circle-line me-2"></i>
                ${actionText} Leave Request
            `;

            modalBody.innerHTML = `
                <div class="alert alert-${actionColor}-transparent">
                    <strong>${actionText} Leave:</strong> ${data.leaveTypeName} for ${data.employeeName}
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>Employee:</strong> ${data.employeeName}</p>
                        <p class="mb-1"><strong>Leave Type:</strong> ${data.leaveTypeName}</p>
                        <p class="mb-1"><strong>Date:</strong> ${data.userFriendlyDate}</p>
                    </div>
                    <div class="col-md-6">
                        <div class="card border bg-light">
                            <div class="card-body">
                                <h6 class="mb-2">Team Impact</h6>
                                <p class="mb-1 small">Team members on this date: <strong id="teamOnLeaveCount">Checking...</strong></p>
                                <p class="mb-0 small">Team availability: <strong id="teamAvailability">Calculating...</strong></p>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="leaveApplicationID" value="${data.leaveApplicationId}">
                <input type="hidden" name="employeeID" value="${data.employeeId}">
                <input type="hidden" name="leaveTypeID" value="${data.leaveTypeId}">
                <input type="hidden" name="leavePeriodID" value="${data.leavePeriodId}">
                <input type="hidden" name="leaveApproverID" value="${data.leaveApproverId}">
                <input type="hidden" name="leaveStatusID" value="${data.leaveStatusId}">
                <input type="hidden" name="leaveDate" value="${data.date}">
                <input type="hidden" name="leaveStatus" value="${data.leaveStatus}">

                <div class="mb-3">
                    <label for="leaveComments" class="form-label">
                        <i class="ri-message-3-line me-1"></i>Your Comments <span class="text-muted">(Optional)</span>
                    </label>
                    <textarea class="form-control" id="leaveComments" name="leaveComments" rows="3"
                              placeholder="Add any comments or notes about this decision..."></textarea>
                </div>
            `;

            // Calculate team impact
            setTimeout(() => checkTeamImpact(data.date), 100);

            modalFooter.innerHTML = `
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancel
                </button>
                <button type="submit" class="btn btn-${actionColor} btn-sm">
                    <i class="ri-${data.leaveStatus === 'approved' ? 'check' : 'close'}-line me-1"></i>
                    Yes, ${actionText} Leave
                </button>
            `;
        });
    });

    // Check team impact for a specific date
    function checkTeamImpact(targetDate) {
        let onLeaveCount = 0;

        // Count approved leaves on this date
        approvedApplications.forEach(app => {
            const start = new Date(app.startDate);
            const end = new Date(app.endDate);
            const target = new Date(targetDate);

            if (target >= start && target <= end) {
                onLeaveCount++;
            }
        });

        // Count pending leaves on this date
        pendingApplications.forEach(app => {
            const start = new Date(app.startDate);
            const end = new Date(app.endDate);
            const target = new Date(targetDate);

            if (target >= start && target <= end) {
                onLeaveCount++;
            }
        });

        const teamSize = teamData.length;
        const availability = teamSize - onLeaveCount;
        const availabilityPercent = teamSize > 0 ? Math.round((availability / teamSize) * 100) : 100;

        const countEl = document.getElementById('teamOnLeaveCount');
        const availEl = document.getElementById('teamAvailability');

        if (countEl) {
            countEl.textContent = `${onLeaveCount} out of ${teamSize}`;
            if (onLeaveCount > teamSize * 0.3) {
                countEl.classList.add('text-danger');
            }
        }

        if (availEl) {
            availEl.textContent = `${availabilityPercent}% (${availability} available)`;
            if (availabilityPercent < 70) {
                availEl.classList.add('text-warning');
            }
            if (availabilityPercent < 50) {
                availEl.classList.remove('text-warning');
                availEl.classList.add('text-danger');
            }
        }

        // Show conflict alert if necessary
        if (availabilityPercent < 50) {
            showConflictWarning(targetDate, onLeaveCount, teamSize);
        }
    }

    // Show conflict warning
    function showConflictWarning(date, onLeave, teamSize) {
        const percent = Math.round((onLeave / teamSize) * 100);
        const content = document.getElementById('conflictAlertContent');

        if (content) {
            content.innerHTML = `
                <div class="text-center mb-3">
                    <i class="ri-alert-line text-danger" style="font-size: 3rem;"></i>
                </div>
                <h5 class="text-center mb-3">High Absence Rate Detected</h5>
                <p class="text-center">
                    Approving this leave will result in <strong>${percent}%</strong> of your team being absent on <strong>${new Date(date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</strong>.
                </p>
                <div class="alert alert-warning">
                    <strong>Impact:</strong>
                    <ul class="mb-0">
                        <li>${onLeave} team members will be on leave</li>
                        <li>Only ${teamSize - onLeave} members available</li>
                        <li>This may impact critical operations</li>
                    </ul>
                </div>
                <p class="text-muted small text-center">
                    Consider reviewing the calendar to reschedule or coordinate with your team.
                </p>
            `;
        }
    }

    // View conflict calendar button
    document.getElementById('viewConflictCalendar')?.addEventListener('click', function() {
        window.location.href = '?s=<?= $s ?>&ss=<?= $ss ?>&p=<?= $p ?>&view=calendar';
    });

    // Batch approval handler
    document.querySelectorAll('.batch-approve-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            const selectedApplications = Array.from(document.querySelectorAll('.application-checkbox:checked'))
                .map(cb => cb.dataset.applicationId);

            if (selectedApplications.length === 0) {
                if (typeof showToast === 'function') {
                    showToast('Please select at least one application to approve', 'warning');
                } else {
                    alert('Please select at least one application to approve');
                }
                return;
            }

            // Populate batch approval modal
            const content = document.getElementById('batchApprovalContent');
            content.innerHTML = `
                <p>You are about to approve <strong>${selectedApplications.length}</strong> leave request(s).</p>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Leave Type</th>
                                <th>Dates</th>
                                <th>Days</th>
                            </tr>
                        </thead>
                        <tbody id="batchApprovalList">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>
                <div class="mb-3">
                    <label for="batchComments" class="form-label">Comments for All</label>
                    <textarea class="form-control" id="batchComments" name="batchComments" rows="2"></textarea>
                </div>
                <input type="hidden" name="applicationIDs" value="${selectedApplications.join(',')}">
            `;
        });
    });
});
</script>
