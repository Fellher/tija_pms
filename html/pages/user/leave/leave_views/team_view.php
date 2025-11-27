<?php
/**
 * Team Leave View
 *
 * Manager view for overseeing team leave applications,
 * approvals, and team leave analytics
 */

// Include FullCalendar dependencies
?>
<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/main.min.css' rel='stylesheet'>

<!-- FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.8/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@6.1.8/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@6.1.8/index.global.min.js'></script>

<?php

// Establish HR manager scope
if (!isset($hrManagerScope) || !is_array($hrManagerScope)) {
    $hrManagerScope = Employee::get_hr_manager_scope($userDetails->ID, $DBConn);
}
$isHRManager = $hrManagerScope['isHRManager'];
$hrManagerScopes = $hrManagerScope['scopes'] ?? array();

$orgDataID = $userDetails->orgDataID ?? null;
$entityID = $userDetails->entityID ?? null;

// Check if user is a manager
$isManager = Employee::is_manager($userDetails->ID, $DBConn);
$isDepartmentHead = Employee::is_department_head($userDetails->ID, $DBConn);

if (!$isManager && !$isDepartmentHead && !$isHRManager) {
    echo '<div class="alert alert-info text-center">
        <i class="ri-information-line fs-3 mb-2"></i>
        <h5>Access Restricted</h5>
        <p class="mb-0">You need manager privileges to access the team leave view.</p>
    </div>';
    return;
}

// Get team data based on user role
$teamMembers = [];
$pendingApprovals = [];
$teamLeaveAnalytics = [];

if ($isManager) {
    $teamMembers = Employee::get_team_members($userDetails->ID, $DBConn);
    $pendingApprovals = Leave::get_pending_approvals_for_manager($userDetails->ID, $DBConn, $hrManagerScope);
    $teamLeaveAnalytics = Leave::get_team_leave_analytics($userDetails->ID, $DBConn, $hrManagerScope);
}

if ($isDepartmentHead) {
    $departmentMembers = Employee::get_department_members($userDetails->departmentID ?? null, $DBConn);
    $teamMembers = array_merge($teamMembers, $departmentMembers);
    $pendingApprovals = array_merge($pendingApprovals, Leave::get_pending_approvals_for_department($userDetails->departmentID ?? null, $DBConn));
}

if ($isHRManager) {
    $processedScopes = array();
    $effectiveScopes = $hrManagerScopes;

    if (empty($effectiveScopes)) {
        $effectiveScopes[] = array(
            'entityID' => $entityID,
            'orgDataID' => $orgDataID,
            'global' => false
        );
    }

    foreach ($effectiveScopes as $scopeEntry) {
        $scopeOrgID = $scopeEntry['orgDataID'] ?? $orgDataID;
        if (!$scopeOrgID) {
            continue;
        }

        if (!empty($scopeEntry['global'])) {
            $scopedEntities = Data::entities_full(array('orgDataID' => $scopeOrgID, 'Suspended' => 'N'), false, $DBConn);
            if ($scopedEntities) {
                foreach ($scopedEntities as $entityRow) {
                    $scopeKey = $scopeOrgID . ':' . $entityRow->entityID;
                    if (isset($processedScopes[$scopeKey])) {
                        continue;
                    }
                    $processedScopes[$scopeKey] = true;

                    $allEmployees = Employee::get_all_employees($scopeOrgID, $entityRow->entityID, $DBConn);
                    if ($allEmployees) {
                        $teamMembers = array_merge($teamMembers, $allEmployees);
                    }

                    $allApprovals = Leave::get_all_pending_approvals($scopeOrgID, $entityRow->entityID, $DBConn);
                    if ($allApprovals) {
                        $pendingApprovals = array_merge($pendingApprovals, $allApprovals);
                    }
                }
            }
            continue;
        }

        $scopeEntityID = $scopeEntry['entityID'] ?? null;
        if (!$scopeEntityID) {
            continue;
        }

        $scopeKey = $scopeOrgID . ':' . $scopeEntityID;

        if (isset($processedScopes[$scopeKey])) {
            continue;
        }
        $processedScopes[$scopeKey] = true;

        $allEmployees = Employee::get_all_employees($scopeOrgID, $scopeEntityID, $DBConn);
        if ($allEmployees) {
            $teamMembers = array_merge($teamMembers, $allEmployees);
        }

        $allApprovals = Leave::get_all_pending_approvals($scopeOrgID, $scopeEntityID, $DBConn);
        if ($allApprovals) {
            $pendingApprovals = array_merge($pendingApprovals, $allApprovals);
        }
    }
}

// Remove duplicates
$teamMembers = array_unique($teamMembers, SORT_REGULAR);
$pendingApprovals = array_unique($pendingApprovals, SORT_REGULAR);

// Get filter parameters
$filterTeamMember = isset($_GET['member']) ? Utility::clean_string($_GET['member']) : '';
$filterStatus = isset($_GET['status']) ? Utility::clean_string($_GET['status']) : '';
$filterType = isset($_GET['type']) ? Utility::clean_string($_GET['type']) : '';

// Filter pending approvals
if ($filterTeamMember || $filterStatus || $filterType) {
    $pendingApprovals = array_filter($pendingApprovals, function($approval) use ($filterTeamMember, $filterStatus, $filterType) {
        $memberMatch = !$filterTeamMember || $approval->employeeID == $filterTeamMember;
        $statusMatch = !$filterStatus || $approval->status == $filterStatus;
        $typeMatch = !$filterType || $approval->leaveTypeID == $filterType;
        return $memberMatch && $statusMatch && $typeMatch;
    });
}
?>

<!-- Team View Container -->
<div class="row">
    <!-- Team Overview Cards -->
    <div class="col-12 mb-4">
        <div class="row">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="metric-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="ri-team-line fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">Team Members</h6>
                                <h4 class="mb-0 fw-bold"><?= count($teamMembers) ?></h4>
                                <small class="text-primary">
                                    <i class="ri-user-line"></i> Under your management
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
                                <h6 class="text-muted mb-1">Pending Approvals</h6>
                                <h4 class="mb-0 fw-bold"><?= count($pendingApprovals) ?></h4>
                                <small class="text-warning">
                                    <i class="ri-eye-line"></i> Awaiting your action
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
                                    <i class="ri-calendar-check-line fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">Approved This Month</h6>
                                <h4 class="mb-0 fw-bold"><?= $teamLeaveAnalytics['approvedThisMonth'] ?? 0 ?></h4>
                                <small class="text-success">
                                    <i class="ri-check-line"></i> Leave applications
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
                                <h6 class="text-muted mb-1">Team on Leave</h6>
                                <h4 class="mb-0 fw-bold"><?= $teamLeaveAnalytics['currentlyOnLeave'] ?? 0 ?></h4>
                                <small class="text-info">
                                    <i class="ri-calendar-line"></i> Currently away
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Approvals -->
    <div class="col-xl-8 col-lg-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="ri-time-line me-2 text-primary"></i>
                        Pending Approvals
                    </h6>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" data-action="open-approval-workflow">
                            <i class="ri-check-line me-1"></i>Bulk Approve
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#approvalFiltersModal">
                            <i class="ri-filter-line me-1"></i>Filters
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($pendingApprovals)): ?>
                    <div class="approvals-list">
                        <?php foreach ($pendingApprovals as $approval): ?>
                            <div class="approval-item mb-3 p-3 border rounded">
                                <div class="row align-items-center">
                                    <div class="col-md-1">
                                        <div class="form-check">
                                            <input class="form-check-input approval-checkbox"
                                                   type="checkbox"
                                                   value="<?= $approval->leaveApplicationID ?>"
                                                   id="approval_<?= $approval->leaveApplicationID ?>">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="employee-info">
                                            <h6 class="mb-1 fw-semibold">
                                                <?= htmlspecialchars($approval->employeeName) ?>
                                            </h6>
                                            <small class="text-muted"><?= htmlspecialchars($approval->jobTitle ?? 'Employee') ?></small>
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="leave-type-info">
                                            <div class="d-flex align-items-center">
                                                <div class="leave-type-icon me-2">
                                                    <?php
                                                    $iconClass = 'ri-calendar-line';
                                                    $iconColor = 'text-primary';

                                                    switch(strtolower($approval->leaveTypeName)) {
                                                        case 'annual':
                                                        case 'vacation':
                                                            $iconClass = 'ri-calendar-check-line';
                                                            $iconColor = 'text-primary';
                                                            break;
                                                        case 'sick':
                                                        case 'medical':
                                                            $iconClass = 'ri-heart-pulse-line';
                                                            $iconColor = 'text-success';
                                                            break;
                                                        case 'emergency':
                                                            $iconClass = 'ri-alarm-warning-line';
                                                            $iconColor = 'text-danger';
                                                            break;
                                                    }
                                                    ?>
                                                    <i class="<?= $iconClass ?> <?= $iconColor ?>"></i>
                                                </div>
                                                <?= htmlspecialchars($approval->leaveTypeName) ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="leave-period">
                                            <div class="date-range">
                                                <div class="start-date fw-medium">
                                                    <?= date('M d', strtotime($approval->startDate)) ?>
                                                </div>
                                                <div class="end-date text-muted small">
                                                    to <?= date('M d', strtotime($approval->endDate)) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="leave-days">
                                            <span class="badge bg-light text-dark">
                                                <?= $approval->noOfDays ?> days
                                            </span>
                                        </div>
                                    </div>

                                    <div class="col-md-2">
                                        <div class="approval-actions">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button"
                                                        class="btn btn-success approve-leave-btn"
                                                        data-action="approve-leave"
                                                        data-application-id="<?= $approval->leaveApplicationID ?>"
                                                        title="Approve">
                                                    <i class="ri-check-line"></i>
                                                </button>
                                                <button type="button"
                                                        class="btn btn-outline-primary"
                                                        data-action="view-approval-details"
                                                        data-application-id="<?= $approval->leaveApplicationID ?>"
                                                        title="View Details">
                                                    <i class="ri-eye-line"></i>
                                                </button>
                                                <button type="button"
                                                        class="btn btn-danger reject-leave-btn"
                                                        data-action="reject-leave"
                                                        data-application-id="<?= $approval->leaveApplicationID ?>"
                                                        title="Reject">
                                                    <i class="ri-close-line"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Approval Details (Collapsible) -->
                                <div class="approval-details mt-3" id="details_<?= $approval->leaveApplicationID ?>" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="detail-item mb-2">
                                                <strong>Reason:</strong>
                                                <?= htmlspecialchars($approval->leaveComments) ?>
                                            </div>
                                            <?php if ($approval->emergencyContact): ?>
                                                <div class="detail-item mb-2">
                                                    <strong>Emergency Contact:</strong>
                                                    <?= htmlspecialchars($approval->emergencyContact) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <?php if ($approval->handoverNotes): ?>
                                                <div class="detail-item mb-2">
                                                    <strong>Handover Notes:</strong>
                                                    <?= htmlspecialchars($approval->handoverNotes) ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="detail-item mb-2">
                                                <strong>Applied Date:</strong>
                                                <?= date('M d, Y', strtotime($approval->createdDate)) ?>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if ($approval->leaveFiles): ?>
                                        <div class="file-attachments mt-3">
                                            <h6 class="mb-2">Supporting Documents:</h6>
                                            <div class="file-list">
                                                <?php
                                                $files = explode(',', $approval->leaveFiles);
                                                foreach($files as $file):
                                                    $fileName = basename($file);
                                                ?>
                                                    <div class="file-item d-flex align-items-center p-2 border rounded mb-2">
                                                        <i class="ri-file-line me-2"></i>
                                                        <span class="flex-grow-1"><?= htmlspecialchars($fileName) ?></span>
                                                        <a href="<?= $config['DataDir'] ?><?= trim($file) ?>"
                                                           target="_blank"
                                                           class="btn btn-outline-primary btn-sm">
                                                            <i class="ri-download-line"></i>
                                                        </a>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="ri-check-line fs-1 text-muted mb-3"></i>
                        <h5 class="text-muted">All Caught Up!</h5>
                        <p class="text-muted">No pending leave approvals at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Team Leave Calendar -->
    <div class="col-xl-4 col-lg-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="ri-calendar-line me-2 text-primary"></i>
                        Team Leave Calendar
                    </h6>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" data-action="expand-calendar">
                            <i class="ri-fullscreen-line me-1"></i>Expand
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-action="refresh-calendar">
                            <i class="ri-refresh-line me-1"></i>Refresh
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="teamLeaveCalendar" class="team-calendar">
                    <!-- Calendar widget would be implemented here -->
                    <div class="calendar-placeholder text-center py-5">
                        <i class="ri-calendar-2-line fs-1 text-muted mb-3"></i>
                        <h6 class="text-muted">Team Leave Calendar</h6>
                        <p class="text-muted small">Calendar widget will be implemented here</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Members Quick View -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0">
                <h6 class="mb-0">
                    <i class="ri-team-line me-2 text-primary"></i>
                    Team Members
                </h6>
            </div>
            <div class="card-body">
                <div class="team-members-list">
                    <?php foreach (array_slice($teamMembers, 0, 8) as $member): ?>
                        <div class="team-member-item mb-3">
                            <div class="d-flex align-items-center">
                                <div class="member-avatar me-3">
                                    <div class="avatar-circle bg-primary text-white">
                                        <?= strtoupper(substr($member->FirstName, 0, 1) . substr($member->Surname, 0, 1)) ?>
                                    </div>
                                </div>
                                <div class="member-info flex-grow-1">
                                    <h6 class="mb-1 fw-semibold">
                                        <?= htmlspecialchars($member->FirstName . ' ' . $member->Surname) ?>
                                    </h6>
                                    <small class="text-muted"><?= htmlspecialchars($member->jobTitle ?? 'Employee') ?></small>
                                </div>
                                <div class="member-status">
                                    <?php
                                    // Check if member is currently on leave
                                    $currentDate = date('Y-m-d');
                                    $isOnLeave = false;
                                    if (isset($member->ID)) {
                                        // Use a custom query to check for active leave
                                        $sql = "SELECT COUNT(*) as activeCount
                                                FROM tija_leave_applications
                                                WHERE employeeID = ?
                                                AND startDate <= ?
                                                AND endDate >= ?
                                                AND leaveStatusID = 4
                                                AND Lapsed = 'N'
                                                AND Suspended = 'N'";

                                        $params = array(
                                            array($member->ID, 'i'),
                                            array($currentDate, 's'),
                                            array($currentDate, 's')
                                        );

                                        $result = $DBConn->fetch_all_rows($sql, $params);
                                        $isOnLeave = ($result && $result[0]->activeCount > 0);
                                    }
                                    ?>
                                    <div class="d-flex flex-column align-items-end gap-1">
                                        <?php if ($isOnLeave): ?>
                                            <span class="badge bg-warning">
                                                <i class="ri-calendar-line me-1"></i>On Leave
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success">
                                                <i class="ri-check-line me-1"></i>Active
                                            </span>
                                        <?php endif; ?>
                                        <button type="button"
                                                class="btn btn-outline-primary btn-sm"
                                                data-action="view-employee-calendar"
                                                data-employee-id="<?= $member->ID ?>"
                                                data-employee-name="<?= htmlspecialchars($member->FirstName . ' ' . $member->Surname) ?>"
                                                title="View Calendar">
                                            <i class="ri-calendar-line"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (count($teamMembers) > 8): ?>
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-outline-primary btn-sm" data-action="view-all-team-members">
                                View All (<?= count($teamMembers) ?>)
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approval Filters Modal -->
<div class="modal fade" id="approvalFiltersModal" tabindex="-1" aria-labelledby="approvalFiltersModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approvalFiltersModalLabel">Filter Approvals</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="GET">
                <input type="hidden" name="s" value="user">
                <input type="hidden" name="ss" value="leave">
                <input type="hidden" name="p" value="leave_management_enhanced">
                <input type="hidden" name="view" value="team">

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="filterMember" class="form-label">Team Member</label>
                        <select class="form-select" id="filterMember" name="member">
                            <option value="">All Members</option>
                            <?php foreach ($teamMembers as $member): ?>
                                <option value="<?= $member->ID ?>" <?= $filterTeamMember == $member->ID ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($member->FirstName . ' ' . $member->Surname) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="filterStatus" class="form-label">Status</label>
                        <select class="form-select" id="filterStatus" name="status">
                            <option value="">All Statuses</option>
                            <option value="pending" <?= $filterStatus == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="approved" <?= $filterStatus == 'approved' ? 'selected' : '' ?>>Approved</option>
                            <option value="rejected" <?= $filterStatus == 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="filterType" class="form-label">Leave Type</label>
                        <select class="form-select" id="filterType" name="type">
                            <option value="">All Types</option>
                            <?php if ($leaveTypes): ?>
                                <?php foreach ($leaveTypes as $type): ?>
                                    <option value="<?= $type->leaveTypeID ?>" <?= $filterType == $type->leaveTypeID ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($type->leaveTypeName) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Full Calendar Modal -->
<div class="modal fade" id="fullCalendarModal" tabindex="-1" aria-labelledby="fullCalendarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fullCalendarModalLabel">Team Leave Calendar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="fullCalendarContainer" class="full-calendar-container">
                    <!-- Full calendar component will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Team View JavaScript -->
<script>
/**
 * Team View Specific Functionality
 */

// Global variables for calendar functionality
let teamCalendarInstance = null;
let miniCalendarInstance = null;

function viewApprovalDetails(applicationId) {
    const detailsElement = document.getElementById(`details_${applicationId}`);
    if (detailsElement.style.display === 'none') {
        detailsElement.style.display = 'block';
    } else {
        detailsElement.style.display = 'none';
    }
}

function viewAllTeamMembers() {
    // Placeholder for future enhancements (e.g., navigate to team management)
}

/**
 * Calendar Functions
 */

function triggerApprovalWorkflowModal(leaveId = null, action = null) {
    const handler =
        typeof window.__leaveBaseOpenApprovalWorkflowModal === 'function'
            ? window.__leaveBaseOpenApprovalWorkflowModal
            : typeof window.openApprovalWorkflowModal === 'function'
                ? window.openApprovalWorkflowModal
                : null;

    if (!handler) {
        console.warn('Approval workflow modal handler is not available.');
        return;
    }

    handler(leaveId, action);
}

function expandToFullCalendar() {
    const modal = new bootstrap.Modal(document.getElementById('fullCalendarModal'));

    // Load the full calendar component
    loadFullCalendarComponent();

    modal.show();
}

function loadFullCalendarComponent() {
    const container = document.getElementById('fullCalendarContainer');

    // Show loading state
    container.innerHTML = '<div class="d-flex justify-content-center align-items-center" style="height: 400px;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    // Load the calendar component via AJAX
    fetch('<?= $base ?>html/pages/user/leave/components/team_calendar_component.php')
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;

            // Initialize the full calendar
            initializeFullCalendar();
        })
        .catch(error => {
            console.error('Error loading calendar component:', error);
            container.innerHTML = '<div class="alert alert-danger">Failed to load calendar component</div>';
        });
}

function initializeFullCalendar() {
    // Wait for DOM to be ready
    setTimeout(() => {
        if (typeof TeamCalendar !== 'undefined') {
            teamCalendarInstance = new TeamCalendar();
        }
    }, 100);
}

function refreshCalendar() {
    if (miniCalendarInstance) {
        miniCalendarInstance.loadCalendarData();
    }
    if (teamCalendarInstance) {
        teamCalendarInstance.loadCalendarData();
    }
}

function viewEmployeeCalendar(employeeId, employeeName) {
    if (teamCalendarInstance) {
        teamCalendarInstance.currentEmployeeID = employeeId;
        teamCalendarInstance.switchView('individual');
        teamCalendarInstance.loadCalendarData();

        // Update the employee selector
        const employeeSelect = document.getElementById('employeeSelect');
        if (employeeSelect) {
            employeeSelect.value = employeeId;
        }

        showToast('info', 'Calendar Updated', `Now viewing ${employeeName}'s calendar`);
    }
}

// Initialize mini calendar when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeMiniCalendar();

    document.querySelectorAll('[data-action="open-approval-workflow"]').forEach(button => {
        button.addEventListener('click', () => triggerApprovalWorkflowModal());
    });

    document.querySelectorAll('[data-action="approve-leave"]').forEach(button => {
        button.addEventListener('click', () => {
            const applicationId = Number(button.dataset.applicationId);
            console.log(`Approving leave application ${applicationId}`);
            triggerApprovalWorkflowModal(applicationId, 'approve');
        });
    });

    document.querySelectorAll('[data-action="view-approval-details"]').forEach(button => {
        button.addEventListener('click', () => {
            const applicationId = Number(button.dataset.applicationId);
            viewApprovalDetails(applicationId);
        });
    });

    document.querySelectorAll('[data-action="reject-leave"]').forEach(button => {
        button.addEventListener('click', () => {
            const applicationId = Number(button.dataset.applicationId);
            triggerApprovalWorkflowModal(applicationId, 'reject');
        });
    });

    document.querySelectorAll('[data-action="expand-calendar"]').forEach(button => {
        button.addEventListener('click', expandToFullCalendar);
    });

    document.querySelectorAll('[data-action="refresh-calendar"]').forEach(button => {
        button.addEventListener('click', refreshCalendar);
    });

    document.querySelectorAll('[data-action="view-employee-calendar"]').forEach(button => {
        button.addEventListener('click', () => {
            const employeeId = Number(button.dataset.employeeId);
            const employeeName = button.dataset.employeeName || '';
            viewEmployeeCalendar(employeeId, employeeName);
        });
    });

    document.querySelectorAll('[data-action="view-all-team-members"]').forEach(button => {
        button.addEventListener('click', viewAllTeamMembers);
    });
});

function initializeMiniCalendar() {
    // Create a mini calendar instance for the sidebar
    const miniCalendarEl = document.getElementById('teamLeaveCalendar');

    if (miniCalendarEl && typeof FullCalendar !== 'undefined') {
        miniCalendarInstance = new FullCalendar.Calendar(miniCalendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: false,
            height: 300,
            dayMaxEvents: 2,
            moreLinkClick: 'popover',
            eventClick: (info) => {
                // Show event details in a small modal
                showMiniEventDetails(info.event);
            },
            events: async (info) => {
                try {
                    const startDate = info.start.toISOString().split('T')[0];
                    const endDate = info.end.toISOString().split('T')[0];

                    const response = await fetch(`<?= $base ?>php/scripts/leave/utilities/team_calendar_data.php?action=calendar_events&start=${startDate}&end=${endDate}`);
                    const data = await response.json();

                    if (data.success) {
                        return data.data;
                    }
                    return [];
                } catch (error) {
                    console.error('Error loading mini calendar events:', error);
                    return [];
                }
            },
            eventDisplay: 'block',
            dayMaxEventRows: 2,
            eventMaxStack: 2,
            eventOverlap: false,
            selectable: false,
            editable: false,
            locale: 'en'
        });

        miniCalendarInstance.render();
    }
}

function showMiniEventDetails(event) {
    const props = event.extendedProps;

    // Create a simple tooltip or small modal
    const tooltip = document.createElement('div');
    tooltip.className = 'mini-event-tooltip';
    tooltip.innerHTML = `
        <div class="tooltip-content">
            <h6>${props.employeeName}</h6>
            <p class="mb-1"><strong>${props.leaveType}</strong></p>
            <p class="mb-1 small">${props.noOfDays} day(s)</p>
            <p class="mb-0 small text-muted">${props.status}</p>
        </div>
    `;

    // Position tooltip
    tooltip.style.position = 'absolute';
    tooltip.style.zIndex = '1000';
    tooltip.style.background = 'white';
    tooltip.style.border = '1px solid #ccc';
    tooltip.style.borderRadius = '4px';
    tooltip.style.padding = '8px';
    tooltip.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';

    document.body.appendChild(tooltip);

    // Remove tooltip after 3 seconds
    setTimeout(() => {
        if (tooltip.parentNode) {
            tooltip.parentNode.removeChild(tooltip);
        }
    }, 3000);
}
</script>

<style>
/* FullCalendar Scoped Styles - Only affects calendar containers */
#teamLeaveCalendar .fc,
#fullCalendarContainer .fc,
.team-calendar-widget .fc,
.employee-calendar-widget .fc {
    /* FullCalendar specific styles are scoped to calendar containers only */
    font-family: inherit !important;
    font-size: inherit !important;
    line-height: inherit !important;
}

/* Prevent FullCalendar from affecting global styles */
.fc {
    /* Reset any global FullCalendar styles that might interfere */
    box-sizing: border-box;
}

/* Ensure FullCalendar doesn't affect page layout */
.fc-view-harness {
    overflow: hidden;
}

/* Scope FullCalendar button styles to calendar containers only */
#teamLeaveCalendar .fc-button,
#fullCalendarContainer .fc-button,
.team-calendar-widget .fc-button,
.employee-calendar-widget .fc-button {
    /* Button styles scoped to calendar containers */
}

/* Scope FullCalendar event styles to calendar containers only */
#teamLeaveCalendar .fc-event,
#fullCalendarContainer .fc-event,
.team-calendar-widget .fc-event,
.employee-calendar-widget .fc-event {
    /* Event styles scoped to calendar containers */
}

/* Prevent FullCalendar from affecting Bootstrap components */
.fc-button {
    /* Ensure FullCalendar buttons don't interfere with Bootstrap buttons */
    vertical-align: middle;
}

.fc-button-group {
    /* Prevent button group conflicts */
    display: inline-block;
}

/* Ensure FullCalendar table styles don't affect other tables */
.fc table {
    /* Scoped table styles */
    border-collapse: separate;
    border-spacing: 0;
}

/* Prevent FullCalendar from affecting page typography */
.fc th,
.fc td {
    /* Scoped cell styles */
    padding: 0;
    vertical-align: top;
}

/* Ensure FullCalendar doesn't affect existing card styles */
.card .fc {
    /* Calendar within cards */
    background: transparent;
}

/* Team View Specific Styles */
.metric-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.approval-item {
    transition: all 0.2s ease;
}

.approval-item:hover {
    background-color: #f8f9fa;
    border-color: #007bff !important;
}

.leave-type-icon {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.date-range {
    font-size: 0.9rem;
}

.start-date {
    color: #495057;
}

.end-date {
    font-size: 0.8rem;
}

.employee-info h6 {
    color: #495057;
}

.approval-details {
    border-top: 1px solid #e9ecef;
    padding-top: 1rem;
}

.detail-item {
    font-size: 0.9rem;
}

.file-item {
    background: #f8f9fa;
    transition: all 0.2s ease;
}

.file-item:hover {
    background: white;
    border-color: #007bff;
}

.team-member-item {
    padding: 0.75rem;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.team-member-item:hover {
    background: #f8f9fa;
    border-color: #007bff;
}

.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.8rem;
}

.member-info h6 {
    color: #495057;
    margin-bottom: 0.25rem;
}

.calendar-placeholder {
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .approval-item .row {
        flex-direction: column;
    }

    .approval-item .col-md-1,
    .approval-item .col-md-2,
    .approval-item .col-md-3 {
        margin-bottom: 0.5rem;
    }

    .approval-actions {
        margin-top: 1rem;
    }

    .approval-actions .btn-group {
        width: 100%;
    }

    .approval-actions .btn {
        flex: 1;
    }
}
</style>
