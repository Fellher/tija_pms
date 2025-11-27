<!-- Team Status View -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <h5 class="mb-0">
                        <i class="ri-team-line text-primary me-2"></i>
                        Team Leave Status & Balances
                    </h5>
                    <div class="d-flex gap-2">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-secondary active" id="cardViewBtn">
                                <i class="ri-layout-grid-line me-1"></i>Cards
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="tableViewBtn">
                                <i class="ri-table-line me-1"></i>Table
                            </button>
                        </div>
                        <button class="btn btn-sm btn-outline-primary" data-action="export-team-status">
                            <i class="ri-download-line me-1"></i>Export
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($directReports)): ?>
                    <!-- Card View (Default) -->
                    <div id="cardView">
                        <div class="alert alert-info mb-4">
                            <i class="ri-information-line me-2"></i>
                            <strong>Team Overview:</strong> Click on any team member card to view detailed leave application history and balances.
                        </div>

                        <div class="row">
                            <?php
                            $leaveTypes = Leave::leave_types(['Lapsed' => 'N'], false, $DBConn);

                            foreach ($directReports as $member):
                                $initials = Core::get_user_name_initials($member->ID, $DBConn);

                                // Calculate overall statistics for this member
                                $memberStats = [
                                    'totalEntitlement' => 0,
                                    'totalTaken' => 0,
                                    'totalPending' => 0,
                                    'totalScheduled' => 0,
                                    'totalAvailable' => 0,
                                    'overallUtilization' => 0,
                                    'applicationCount' => 0
                                ];

                                if ($leaveTypes && is_array($leaveTypes)) {
                                    foreach ($leaveTypes as $leaveType) {
                                        $entitlement = Leave::leave_entitlement([
                                            'entityID' => $member->entityID,
                                            'leaveTypeID' => $leaveType->leaveTypeID
                                        ], true, $DBConn);

                                        if ($entitlement) {
                                            $memberStats['totalEntitlement'] += $entitlement->entitlement ?? 0;

                                            $applications = Leave::leave_applications([
                                                'employeeID' => $member->ID,
                                                'leaveTypeID' => $leaveType->leaveTypeID,
                                                'Lapsed' => 'N'
                                            ], false, $DBConn);

                                            if ($applications && is_array($applications)) {
                                                $memberStats['applicationCount'] += count($applications);

                                                foreach ($applications as $app) {
                                                    $days = $app->noOfDays ?: Leave::countWeekdays($app->startDate, $app->endDate);

                                                    // Status mapping with date consideration:
                                                    switch ($app->leaveStatusID) {
                                                        case 1:
                                                            // Draft - count as scheduled
                                                            $memberStats['totalScheduled'] += $days;
                                                            break;
                                                        case 2:
                                                        case 3:
                                                            // Submitted/Pending
                                                            $memberStats['totalPending'] += $days;
                                                            break;
                                                        case 6:
                                                            // Approved - check if past (taken) or future (scheduled)
                                                            if (strtotime($app->startDate) > time()) {
                                                                // Future approved leave = scheduled
                                                                $memberStats['totalScheduled'] += $days;
                                                            } else {
                                                                // Past or current approved leave = taken
                                                                $memberStats['totalTaken'] += $days;
                                                            }
                                                            break;
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    $memberStats['totalAvailable'] = max(0, $memberStats['totalEntitlement'] -
                                        ($memberStats['totalTaken'] + $memberStats['totalPending'] + $memberStats['totalScheduled']));
                                    $memberStats['overallUtilization'] = $memberStats['totalEntitlement'] > 0 ?
                                        round(($memberStats['totalTaken'] / $memberStats['totalEntitlement']) * 100, 1) : 0;
                                }

                                // Determine status color
                                $statusClass = 'primary';
                                $statusIcon = 'ri-user-line';
                                $statusText = 'Active';

                                if ($memberStats['overallUtilization'] > 80) {
                                    $statusClass = 'danger';
                                    $statusIcon = 'ri-alert-line';
                                    $statusText = 'High Usage';
                                } elseif ($memberStats['totalAvailable'] <= 2 && $memberStats['totalAvailable'] > 0) {
                                    $statusClass = 'warning';
                                    $statusIcon = 'ri-error-warning-line';
                                    $statusText = 'Low Balance';
                                } elseif ($memberStats['overallUtilization'] < 30 && $memberStats['totalEntitlement'] > 0) {
                                    $statusClass = 'info';
                                    $statusIcon = 'ri-information-line';
                                    $statusText = 'Low Usage';
                                } elseif ($memberStats['totalAvailable'] <= 0) {
                                    $statusClass = 'danger';
                                    $statusIcon = 'ri-close-circle-line';
                                    $statusText = 'No Balance';
                                }
                            ?>
                            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                                <div class="card team-member-card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <!-- Header with Avatar -->
                                        <div class="text-center mb-3">
                                            <?php if (!empty($member->profile_image)): ?>
                                                <img src="<?= $config['DataDir'] . $member->profile_image ?>"
                                                     class="avatar avatar-lg rounded-circle mb-2"
                                                     alt="Profile"
                                                     data-fallback-avatar>
                                                <div class="avatar avatar-lg bg-primary-transparent rounded-circle mb-2 mx-auto" style="display:none;">
                                                    <span class="avatar-initials fs-5"><?= $initials['initials'] ?></span>
                                                </div>
                                            <?php else: ?>
                                                <div class="avatar avatar-lg bg-primary-transparent rounded-circle mb-2 mx-auto">
                                                    <span class="avatar-initials fs-5"><?= $initials['initials'] ?></span>
                                                </div>
                                            <?php endif; ?>

                                            <h6 class="mb-1"><?= htmlspecialchars($member->FirstName . ' ' . $member->Surname) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($member->jobTitle ?? 'N/A') ?></small>
                                            <br>
                                            <span class="badge bg-<?= $statusClass ?>-transparent mt-2">
                                                <i class="<?= $statusIcon ?> me-1"></i><?= $statusText ?>
                                            </span>
                                        </div>

                                        <!-- Quick Stats -->
                                        <div class="team-quick-stats mb-3">
                                            <div class="stat-row">
                                                <span class="text-muted small">Total Entitlement:</span>
                                                <strong><?= $memberStats['totalEntitlement'] ?> days</strong>
                                            </div>
                                            <div class="stat-row">
                                                <span class="text-muted small">Days Taken:</span>
                                                <span class="badge bg-success-transparent"><?= $memberStats['totalTaken'] ?></span>
                                            </div>
                                            <div class="stat-row">
                                                <span class="text-muted small">Scheduled (Future):</span>
                                                <span class="badge bg-info-transparent"><?= $memberStats['totalScheduled'] ?></span>
                                            </div>
                                            <div class="stat-row">
                                                <span class="text-muted small">Pending Approval:</span>
                                                <span class="badge bg-warning-transparent"><?= $memberStats['totalPending'] ?></span>
                                            </div>
                                            <div class="stat-row">
                                                <span class="text-muted small">Available:</span>
                                                <strong class="text-<?= $memberStats['totalAvailable'] <= 2 ? 'danger' : 'success' ?>">
                                                    <?= $memberStats['totalAvailable'] ?> days
                                                </strong>
                                            </div>
                                        </div>

                                        <!-- Utilization Progress -->
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <small class="text-muted">Utilization</small>
                                                <small class="fw-bold"><?= $memberStats['overallUtilization'] ?>%</small>
                                            </div>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-<?= $statusClass ?>"
                                                     role="progressbar"
                                                     style="width: <?= $memberStats['overallUtilization'] ?>%"
                                                     aria-valuenow="<?= $memberStats['overallUtilization'] ?>"
                                                     aria-valuemin="0"
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Applications Count -->
                                        <div class="text-center mb-3">
                                            <small class="text-muted">
                                                <i class="ri-file-list-line me-1"></i>
                                                <?= $memberStats['applicationCount'] ?> application(s)
                                            </small>
                                        </div>

                                        <!-- View Details Button -->
                                        <button class="btn btn-primary btn-sm w-100 view-member-details"
                                                data-bs-toggle="modal"
                                                data-bs-target="#memberDetailModal"
                                                data-employee-id="<?= $member->ID ?>"
                                                data-employee-name="<?= htmlspecialchars($member->FirstName . ' ' . $member->Surname) ?>"
                                                data-job-title="<?= htmlspecialchars($member->jobTitle ?? 'N/A') ?>">
                                            <i class="ri-eye-line me-1"></i>View Full Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Table View (Hidden by default) -->
                    <div id="tableView" class="d-none">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle" id="teamStatusTable">
                                <thead class="table-light">
                                    <tr>
                                        <th rowspan="2" class="align-middle">Employee</th>
                                        <th rowspan="2" class="align-middle">Leave Type</th>
                                        <th rowspan="2" class="text-center align-middle">Entitlement</th>
                                        <th colspan="4" class="text-center bg-light">Leave Status (Days)</th>
                                        <th rowspan="2" class="text-center align-middle">Available</th>
                                        <th rowspan="2" class="text-center align-middle">Utilization</th>
                                    </tr>
                                    <tr class="bg-light">
                                        <th class="text-center">Taken</th>
                                        <th class="text-center">Pending</th>
                                        <th class="text-center">Scheduled</th>
                                        <th class="text-center">Rejected</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($directReports as $member):
                                        $initials = Core::get_user_name_initials($member->ID, $DBConn);

                                        // Get all leave types for this employee
                                        if ($leaveTypes && is_array($leaveTypes)):
                                            foreach ($leaveTypes as $leaveType):
                                                // Get entitlement
                                                $entitlement = Leave::leave_entitlement([
                                                    'entityID' => $member->entityID,
                                                    'leaveTypeID' => $leaveType->leaveTypeID
                                                ], true, $DBConn);

                                                if (!$entitlement) continue; // Skip if no entitlement

                                                // Get all applications for this employee and leave type
                                                $applications = Leave::leave_applications([
                                                    'employeeID' => $member->ID,
                                                    'leaveTypeID' => $leaveType->leaveTypeID,
                                                    'Lapsed' => 'N'
                                                ], false, $DBConn);

                                                // Calculate balances
                                                $taken = 0;
                                                $pending = 0;
                                                $scheduled = 0;
                                                $rejected = 0;
                                                $cancelled = 0;

                                                if ($applications && is_array($applications)) {
                                                    foreach ($applications as $app) {
                                                        $days = $app->noOfDays ?: Leave::countWeekdays($app->startDate, $app->endDate);

                                                        // Status mapping with date consideration:
                                                        // 1 = Draft (scheduled)
                                                        // 2,3 = Submitted/Pending (awaiting approval)
                                                        // 4 = Rejected
                                                        // 5 = Cancelled
                                                        // 6 = Approved (check if past or future)

                                                        switch ($app->leaveStatusID) {
                                                            case 1:
                                                                // Draft - count as scheduled
                                                                $scheduled += $days;
                                                                break;
                                                            case 2:
                                                            case 3:
                                                                // Submitted/Pending
                                                                $pending += $days;
                                                                break;
                                                            case 4:
                                                                // Rejected
                                                                $rejected += $days;
                                                                break;
                                                            case 5:
                                                                // Cancelled
                                                                $cancelled += $days;
                                                                break;
                                                            case 6:
                                                                // Approved - distinguish between past (taken) and future (scheduled)
                                                                if (strtotime($app->startDate) > time()) {
                                                                    // Future approved leave = scheduled
                                                                    $scheduled += $days;
                                                                } else {
                                                                    // Past or current approved leave = taken
                                                                    $taken += $days;
                                                                }
                                                                break;
                                                        }
                                                    }
                                                }

                                                $totalEntitlement = $entitlement->entitlement ?? 0;
                                                $available = max(0, $totalEntitlement - ($taken + $pending + $scheduled));
                                                $utilization = $totalEntitlement > 0 ? round(($taken / $totalEntitlement) * 100, 1) : 0;

                                                // Determine status color
                                                $statusClass = 'success';
                                                if ($utilization > 80) $statusClass = 'danger';
                                                elseif ($utilization > 60) $statusClass = 'warning';
                                                elseif ($utilization < 30) $statusClass = 'info';
                                    ?>
                                    <tr data-employee-id="<?= $member->ID ?>"
                                        data-leave-type-id="<?= $leaveType->leaveTypeID ?>"
                                        class="team-status-row">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm bg-primary-transparent rounded-circle me-2">
                                                    <span class="avatar-initials small"><?= $initials['initials'] ?></span>
                                                </div>
                                                <div>
                                                    <strong><?= htmlspecialchars($member->FirstName . ' ' . $member->Surname) ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?= htmlspecialchars($member->jobTitle ?? 'N/A') ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge" style="background-color: <?= $leaveType->leaveColor ?? '#6c757d' ?>;">
                                                <?= htmlspecialchars($leaveType->leaveTypeName) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <strong><?= $totalEntitlement ?></strong> days
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success-transparent">
                                                <?= $taken ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($pending > 0): ?>
                                                <span class="badge bg-warning-transparent">
                                                    <?= $pending ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">0</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($scheduled > 0): ?>
                                                <span class="badge bg-info-transparent">
                                                    <?= $scheduled ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">0</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($rejected > 0): ?>
                                                <span class="badge bg-danger-transparent">
                                                    <?= $rejected ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">0</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <strong class="text-<?= $available <= 0 ? 'danger' : 'success' ?>">
                                                <?= $available ?>
                                            </strong> days
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex align-items-center justify-content-center gap-2">
                                                <div class="progress" style="width: 100px; height: 20px;">
                                                    <div class="progress-bar bg-<?= $statusClass ?>"
                                                         role="progressbar"
                                                         style="width: <?= $utilization ?>%"
                                                         aria-valuenow="<?= $utilization ?>"
                                                         aria-valuemin="0"
                                                         aria-valuemax="100">
                                                        <?= $utilization ?>%
                                                    </div>
                                                </div>
                                                <?php if ($utilization > 80): ?>
                                                    <i class="ri-alert-line text-danger"
                                                       data-bs-toggle="tooltip"
                                                       title="High utilization - Consider workload"></i>
                                                <?php elseif ($utilization < 30): ?>
                                                    <i class="ri-information-line text-info"
                                                       data-bs-toggle="tooltip"
                                                       title="Low utilization - Encourage work-life balance"></i>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                            endforeach;
                                        endif;
                                    endforeach;
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="ri-team-line text-muted" style="font-size: 4rem;"></i>
                        <h4 class="mt-3">No Team Members</h4>
                        <p class="text-muted">You don't have any direct reports assigned.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Member Detail Modal -->
<div class="modal fade" id="memberDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md bg-white rounded-circle me-3">
                        <span class="avatar-initials text-primary" id="modalMemberInitials"></span>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="modalMemberName"></h5>
                        <small id="modalMemberTitle"></small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Loading State -->
                <div id="modalLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2">Loading employee details...</p>
                </div>

                <!-- Content -->
                <div id="modalContent" class="d-none">
                    <!-- Summary Cards -->
                    <div class="row mb-4" id="modalSummaryCards">
                        <!-- Populated by JavaScript -->
                    </div>

                    <!-- Leave Type Breakdown -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">
                                <i class="ri-pie-chart-line text-primary me-2"></i>
                                Leave Balance by Type
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle" id="modalLeaveTypeTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Leave Type</th>
                                            <th class="text-center">Entitlement</th>
                                            <th class="text-center">Taken</th>
                                            <th class="text-center">Pending</th>
                                            <th class="text-center">Scheduled</th>
                                            <th class="text-center">Available</th>
                                            <th class="text-center">Utilization</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Application History -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">
                                <i class="ri-file-list-line text-primary me-2"></i>
                                Leave Application History
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle" id="modalApplicationTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Application Date</th>
                                            <th>Leave Type</th>
                                            <th>Period</th>
                                            <th>Days</th>
                                            <th>Status</th>
                                            <th>Comments</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-outline-primary" data-action="print-member-details">
                    <i class="ri-printer-line me-1"></i>Print
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Team Summary Cards -->
<div class="row">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">
                    <i class="ri-user-star-line text-primary me-2"></i>
                    High Utilization
                </h6>
                <div id="highUtilizationList">
                    <!-- Populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">
                    <i class="ri-alert-line text-warning me-2"></i>
                    Low Balances
                </h6>
                <div id="lowBalanceList">
                    <!-- Populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">
                    <i class="ri-calendar-check-line text-success me-2"></i>
                    Healthy Balance
                </h6>
                <div id="healthyBalanceList">
                    <!-- Populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">
                    <i class="ri-information-line text-info me-2"></i>
                    Low Utilization
                </h6>
                <div id="lowUtilizationList">
                    <!-- Populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View switcher
    const cardViewBtn = document.getElementById('cardViewBtn');
    const tableViewBtn = document.getElementById('tableViewBtn');
    const cardView = document.getElementById('cardView');
    const tableView = document.getElementById('tableView');

    document.querySelectorAll('[data-action="export-team-status"]').forEach(button => {
        button.addEventListener('click', function() {
            if (typeof exportTeamStatus === 'function') {
                exportTeamStatus();
            }
        });
    });

    document.querySelectorAll('[data-action="print-member-details"]').forEach(button => {
        button.addEventListener('click', function() {
            if (typeof printMemberDetails === 'function') {
                printMemberDetails();
            }
        });
    });

    document.querySelectorAll('[data-fallback-avatar]').forEach(img => {
        img.addEventListener('error', () => {
            img.style.display = 'none';
            const fallback = img.nextElementSibling;
            if (fallback) {
                fallback.style.display = 'flex';
            }
        }, { once: true });
    });

    cardViewBtn?.addEventListener('click', function() {
        cardView?.classList.remove('d-none');
        tableView?.classList.add('d-none');
        cardViewBtn.classList.add('active');
        tableViewBtn.classList.remove('active');
    });

    tableViewBtn?.addEventListener('click', function() {
        tableView?.classList.remove('d-none');
        cardView?.classList.add('d-none');
        tableViewBtn.classList.add('active');
        cardViewBtn.classList.remove('active');
    });

    // Member detail modal handler
    document.querySelectorAll('.view-member-details').forEach(button => {
        button.addEventListener('click', function() {
            const employeeId = this.dataset.employeeId;
            const employeeName = this.dataset.employeeName;
            const jobTitle = this.dataset.jobTitle;

            loadMemberDetails(employeeId, employeeName, jobTitle);
        });
    });

    // Analyze team status
    analyzeTeamStatus();

    // Initialize tooltips
    const tooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltips.map(el => new bootstrap.Tooltip(el));
});

function loadMemberDetails(employeeId, employeeName, jobTitle) {
    // Set modal header
    const initials = getInitials(employeeName);
    document.getElementById('modalMemberInitials').textContent = initials;
    document.getElementById('modalMemberName').textContent = employeeName;
    document.getElementById('modalMemberTitle').textContent = jobTitle;

    // Show loading
    document.getElementById('modalLoading').classList.remove('d-none');
    document.getElementById('modalContent').classList.add('d-none');

    // Fetch employee details
    fetch('<?= $base ?>php/scripts/leave/utilities/get_employee_leave_details.php?employeeID=' + employeeId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateMemberModal(data);
            } else {
                showError('Failed to load employee details: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('An error occurred while loading details');
        })
        .finally(() => {
            document.getElementById('modalLoading').classList.add('d-none');
            document.getElementById('modalContent').classList.remove('d-none');
        });
}

function populateMemberModal(data) {
    // Summary Cards
    const summaryCards = document.getElementById('modalSummaryCards');
    summaryCards.innerHTML = `
        <div class="col-md-3">
            <div class="card border-0 bg-primary-transparent">
                <div class="card-body text-center">
                    <h3 class="text-primary mb-1">${data.summary.totalEntitlement}</h3>
                    <small class="text-muted">Total Entitlement</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success-transparent">
                <div class="card-body text-center">
                    <h3 class="text-success mb-1">${data.summary.totalTaken}</h3>
                    <small class="text-muted">Days Taken</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-warning-transparent">
                <div class="card-body text-center">
                    <h3 class="text-warning mb-1">${data.summary.totalPending}</h3>
                    <small class="text-muted">Pending Approval</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-info-transparent">
                <div class="card-body text-center">
                    <h3 class="text-info mb-1">${data.summary.totalAvailable}</h3>
                    <small class="text-muted">Days Available</small>
                </div>
            </div>
        </div>
    `;

    // Leave Type Breakdown Table
    const leaveTypeTable = document.getElementById('modalLeaveTypeTable').querySelector('tbody');
    leaveTypeTable.innerHTML = '';

    data.leaveTypes.forEach(lt => {
        const statusClass = lt.utilization > 80 ? 'danger' : (lt.utilization > 60 ? 'warning' : 'success');
        const row = `
            <tr>
                <td>
                    <span class="badge" style="background-color: ${lt.color || '#6c757d'};">
                        ${lt.name}
                    </span>
                </td>
                <td class="text-center"><strong>${lt.entitlement}</strong></td>
                <td class="text-center"><span class="badge bg-success-transparent">${lt.taken}</span></td>
                <td class="text-center"><span class="badge bg-warning-transparent">${lt.pending}</span></td>
                <td class="text-center"><span class="badge bg-info-transparent">${lt.scheduled}</span></td>
                <td class="text-center"><strong class="text-${lt.available <= 2 ? 'danger' : 'success'}">${lt.available}</strong></td>
                <td class="text-center">
                    <div class="progress" style="width: 100px; height: 20px; margin: 0 auto;">
                        <div class="progress-bar bg-${statusClass}"
                             style="width: ${lt.utilization}%">
                            ${lt.utilization}%
                        </div>
                    </div>
                </td>
            </tr>
        `;
        leaveTypeTable.innerHTML += row;
    });

    // Application History Table
    const applicationTable = document.getElementById('modalApplicationTable').querySelector('tbody');
    applicationTable.innerHTML = '';

    if (data.applications && data.applications.length > 0) {
        data.applications.forEach(app => {
            const statusBadge = getStatusBadge(app.statusID, app.statusName);
            const row = `
                <tr>
                    <td><small>${formatDate(app.dateAdded)}</small></td>
                    <td>
                        <span class="badge" style="background-color: ${app.leaveColor || '#6c757d'};">
                            ${app.leaveTypeName}
                        </span>
                    </td>
                    <td><small>${formatDate(app.startDate)} - ${formatDate(app.endDate)}</small></td>
                    <td><strong>${app.days}</strong> days</td>
                    <td>${statusBadge}</td>
                    <td><small>${app.comments || '-'}</small></td>
                </tr>
            `;
            applicationTable.innerHTML += row;
        });
    } else {
        applicationTable.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No applications found</td></tr>';
    }
}

function getInitials(name) {
    const parts = name.split(' ');
    return parts.map(p => p.charAt(0)).join('').substring(0, 2).toUpperCase();
}

function getStatusBadge(statusID, statusName) {
    const statusMap = {
        1: { class: 'info', icon: 'ri-draft-line', text: 'Draft' },
        2: { class: 'warning', icon: 'ri-send-plane-line', text: 'Submitted' },
        3: { class: 'warning', icon: 'ri-time-line', text: 'Pending' },
        4: { class: 'danger', icon: 'ri-close-circle-line', text: 'Rejected' },
        5: { class: 'secondary', icon: 'ri-close-line', text: 'Cancelled' },
        6: { class: 'success', icon: 'ri-checkbox-circle-line', text: 'Approved' }
    };

    const status = statusMap[statusID] || { class: 'secondary', icon: 'ri-question-line', text: statusName };
    return `<span class="badge bg-${status.class}"><i class="${status.icon} me-1"></i>${status.text}</span>`;
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function showError(message) {
    document.getElementById('modalContent').innerHTML = `
        <div class="alert alert-danger">
            <i class="ri-error-warning-line me-2"></i>${message}
        </div>
    `;
}

function printMemberDetails() {
    window.print();
}

function analyzeTeamStatus() {
    const rows = document.querySelectorAll('.team-status-row');
    const highUtil = [];
    const lowBalance = [];
    const healthy = [];
    const lowUtil = [];

    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        const employeeName = cells[0].querySelector('strong').textContent;
        const leaveType = cells[1].querySelector('.badge').textContent;
        const available = parseInt(cells[7].querySelector('strong').textContent);
        const progressBar = cells[8].querySelector('.progress-bar');
        const utilization = parseFloat(progressBar.textContent);

        const data = {
            name: employeeName,
            type: leaveType,
            available: available,
            utilization: utilization
        };

        if (utilization > 80) {
            highUtil.push(data);
        } else if (available <= 2 && available >= 0) {
            lowBalance.push(data);
        } else if (utilization >= 40 && utilization <= 70) {
            healthy.push(data);
        } else if (utilization < 30) {
            lowUtil.push(data);
        }
    });

    // Populate high utilization
    const highUtilEl = document.getElementById('highUtilizationList');
    if (highUtil.length > 0) {
        highUtilEl.innerHTML = highUtil.slice(0, 5).map(d => `
            <div class="mb-2">
                <small><strong>${d.name}</strong></small>
                <br>
                <small class="text-muted">${d.type}: ${d.utilization}% used</small>
            </div>
        `).join('');
    } else {
        highUtilEl.innerHTML = '<p class="text-muted small mb-0">None</p>';
    }

    // Populate low balance
    const lowBalanceEl = document.getElementById('lowBalanceList');
    if (lowBalance.length > 0) {
        lowBalanceEl.innerHTML = lowBalance.slice(0, 5).map(d => `
            <div class="mb-2">
                <small><strong>${d.name}</strong></small>
                <br>
                <small class="text-muted">${d.type}: ${d.available} days left</small>
            </div>
        `).join('');
    } else {
        lowBalanceEl.innerHTML = '<p class="text-muted small mb-0">None</p>';
    }

    // Populate healthy balance
    const healthyEl = document.getElementById('healthyBalanceList');
    if (healthy.length > 0) {
        healthyEl.innerHTML = `<p class="text-success small mb-0"><strong>${healthy.length}</strong> team members with healthy balance</p>`;
    } else {
        healthyEl.innerHTML = '<p class="text-muted small mb-0">None</p>';
    }

    // Populate low utilization
    const lowUtilEl = document.getElementById('lowUtilizationList');
    if (lowUtil.length > 0) {
        lowUtilEl.innerHTML = lowUtil.slice(0, 5).map(d => `
            <div class="mb-2">
                <small><strong>${d.name}</strong></small>
                <br>
                <small class="text-muted">${d.type}: ${d.utilization}% used</small>
            </div>
        `).join('');
    } else {
        lowUtilEl.innerHTML = '<p class="text-muted small mb-0">None</p>';
    }
}

function exportTeamStatus() {
    // Export table to CSV
    const table = document.getElementById('teamStatusTable');
    if (!table) return;

    let csv = [];

    // Get headers
    const headers = [];
    table.querySelectorAll('thead th').forEach(th => {
        if (th.rowSpan || !th.colSpan || th.colSpan == 1) {
            headers.push(th.textContent.trim());
        }
    });
    csv.push(headers.join(','));

    // Get rows
    table.querySelectorAll('tbody tr').forEach(row => {
        const rowData = [];
        row.querySelectorAll('td').forEach((td, index) => {
            if (index === 0) {
                // Employee name
                rowData.push('"' + td.querySelector('strong').textContent + '"');
            } else if (index === 1) {
                // Leave type
                rowData.push('"' + td.querySelector('.badge').textContent + '"');
            } else if (index === 8) {
                // Utilization
                rowData.push(td.querySelector('.progress-bar').textContent);
            } else {
                rowData.push('"' + td.textContent.trim().replace(/\s+/g, ' ') + '"');
            }
        });
        csv.push(rowData.join(','));
    });

    // Download
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'team_leave_status_' + new Date().toISOString().split('T')[0] + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}
</script>
