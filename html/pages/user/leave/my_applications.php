<?php
/**
 * My Leave Applications
 * Personal dashboard for an employee's leave requests
 */

if (!$isValidUser) {
    Alert::info(
        "You need to be logged in to access this page",
        true,
        array('fst-italic', 'text-center', 'font-18')
    );
    include "includes/core/log_in_script.php";
    return;
}

$employeeID = $userDetails->ID;

// Optional filters
$statusFilter = isset($_GET['status']) ? Utility::clean_string($_GET['status']) : 'all';
$periodFilter = isset($_GET['period']) ? Utility::clean_string($_GET['period']) : '';

$where = array('employeeID' => $employeeID);
if ($statusFilter !== 'all') {
    $where['leaveStatusID'] = $statusFilter;
}
if (!empty($periodFilter)) {
    $where['leavePeriodID'] = $periodFilter;
}

$applicationsRaw = Leave::leave_applications_full($where, false, $DBConn);
$applications = array();
if ($applicationsRaw) {
    foreach ($applicationsRaw as $row) {
        $applications[] = is_object($row) ? (array)$row : $row;
    }
}

// Get leave statuses for filters
$statusOptions = Leave::leave_status(array('Suspended' => 'N'), false, $DBConn);
$statusLookup = array();
if ($statusOptions) {
    foreach ($statusOptions as $status) {
        $statusLookup[$status->leaveStatusID] = $status->leaveStatusName;
    }
}

// Summary counters
$summary = array(
    'total' => count($applications),
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0,
);

foreach ($applications as $app) {
    $statusID = (int)($app['leaveStatusID'] ?? 0);
    switch ($statusID) {
        case 3:
        case 2:
            $summary['pending']++;
            break;
        case 6:
            $summary['approved']++;
            break;
        case 4:
            $summary['rejected']++;
            break;
    }
}

function getStatusBadgeClass($statusID) {
    switch ((int)$statusID) {
        case 6: // Approved
            return 'bg-success';
        case 4: // Rejected
            return 'bg-danger';
        case 3: // Pending approval
        case 2: // Awaiting review
            return 'bg-warning text-dark';
        case 1: // Draft / Scheduled
        default:
            return 'bg-secondary';
    }
}
?>

<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-1">
                <i class="ri-briefcase-4-fill me-2 text-primary"></i>
                My Leave Applications
            </h2>
            <p class="text-muted mb-0">Track the status of all your leave requests.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small mb-1">Total Applications</h6>
                    <div class="d-flex align-items-center">
                        <span class="display-6 fw-semibold text-primary me-3"><?php echo $summary['total']; ?></span>
                        <i class="ri-calendar-check-line fs-2 text-primary opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small mb-1">Pending</h6>
                    <div class="d-flex align-items-center">
                        <span class="display-6 fw-semibold text-warning me-3"><?php echo $summary['pending']; ?></span>
                        <i class="ri-time-line fs-2 text-warning opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small mb-1">Approved</h6>
                    <div class="d-flex align-items-center">
                        <span class="display-6 fw-semibold text-success me-3"><?php echo $summary['approved']; ?></span>
                        <i class="ri-check-double-line fs-2 text-success opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small mb-1">Rejected</h6>
                    <div class="d-flex align-items-center">
                        <span class="display-6 fw-semibold text-danger me-3"><?php echo $summary['rejected']; ?></span>
                        <i class="ri-close-circle-line fs-2 text-danger opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom-0">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <h5 class="mb-0">Leave History</h5>
                    <small class="text-muted">Click a row to view or update the application.</small>
                </div>
                <div class="d-flex gap-2">
                    <form method="get" class="d-flex gap-2">
                        <input type="hidden" name="s" value="user">
                        <input type="hidden" name="ss" value="leave">
                        <input type="hidden" name="p" value="my_applications">
                        <select name="status" class="form-select form-select-sm">
                            <option value="all">All statuses</option>
                            <?php foreach ($statusLookup as $statusID => $statusName): ?>
                                <option value="<?php echo $statusID; ?>" <?php echo $statusFilter == $statusID ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($statusName); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="ri-filter-2-line me-1"></i> Filter
                        </button>
                    </form>
                    <a href="<?php echo "{$base}html/?s=user&ss=leave&p=apply_leave_workflow"; ?>" class="btn btn-sm btn-primary">
                        <i class="ri-add-line me-1"></i> Apply for Leave
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($applications)): ?>
                <div class="text-center py-5">
                    <i class="ri-inbox-2-line fs-1 text-muted mb-3"></i>
                    <p class="text-muted mb-0">You do not have any leave applications yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Reference</th>
                                <th scope="col">Leave Type</th>
                                <th scope="col">Period</th>
                                <th scope="col">Dates</th>
                                <th scope="col">Days</th>
                                <th scope="col">Status</th>
                                <th scope="col">Approval Progress</th>
                                <th scope="col">Last Updated</th>
                                <th scope="col" class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                                <?php
                                    $leaveID = $app['leaveApplicationID'];
                                    $leaveType = $app['leaveTypeName'] ?? 'N/A';
                                    $periodName = $app['leavePeriodName'] ?? 'N/A';
                                    $start = Utility::date_format($app['startDate']);
                                    $end = Utility::date_format($app['endDate']);
                                    $days = $app['noOfDays'] ?? '-';
                                    $statusID = $app['leaveStatusID'] ?? '';
                                    $statusName = $app['leaveStatusName'] ?? ($statusLookup[$statusID] ?? 'Unknown');
                                    $lastUpdateRaw = $app['LastUpdate'] ?? $app['DateAdded'] ?? null;
                                    $updated = $lastUpdateRaw ? date('M j, Y g:i a', strtotime($lastUpdateRaw)) : null;

                                    // Get workflow status summary
                                    $workflowSummary = Leave::get_leave_workflow_summary($leaveID, $DBConn);
                                ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($leaveID); ?></td>
                                    <td><?php echo htmlspecialchars($leaveType); ?></td>
                                    <td><?php echo htmlspecialchars($periodName); ?></td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span><?php echo $start; ?></span>
                                            <small class="text-muted">to <?php echo $end; ?></small>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($days); ?></td>
                                    <td>
                                        <span class="badge <?php echo getStatusBadgeClass($statusID); ?>">
                                            <?php echo htmlspecialchars($statusName); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($workflowSummary): ?>
                                            <div class="small">
                                                <?php if ($workflowSummary['approvedCount'] > 0): ?>
                                                    <span class="badge bg-success" title="<?php echo htmlspecialchars($workflowSummary['approvedBy']); ?>">
                                                        <i class="ri-check-line"></i> <?php echo $workflowSummary['approvedCount']; ?> Approved
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($workflowSummary['rejectedCount'] > 0): ?>
                                                    <span class="badge bg-danger" title="<?php echo htmlspecialchars($workflowSummary['rejectedBy']); ?>">
                                                        <i class="ri-close-line"></i> <?php echo $workflowSummary['rejectedCount']; ?> Rejected
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($workflowSummary['pendingCount'] > 0): ?>
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="ri-time-line"></i> <?php echo $workflowSummary['pendingCount']; ?> Pending
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $updated ? htmlspecialchars($updated) : '-'; ?></td>
                                    <td class="text-end">
                                        <a href="<?php echo "{$base}html/?s=user&ss=leave&p=view_leave_application&id={$leaveID}"; ?>"
                                           class="btn btn-sm btn-outline-primary">
                                           <i class="ri-eye-line me-1"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

