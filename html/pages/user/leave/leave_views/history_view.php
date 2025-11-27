<?php
/**
 * Leave History View
 *
 * Comprehensive view of leave applications history with filtering,
 * searching, and detailed application tracking
 */

// Get filter parameters
$filterStatus = isset($_GET['status']) ? Utility::clean_string($_GET['status']) : '';
$filterType = isset($_GET['type']) ? Utility::clean_string($_GET['type']) : '';
$filterYear = isset($_GET['year']) ? Utility::clean_string($_GET['year']) : date('Y');
$searchTerm = isset($_GET['search']) ? Utility::clean_string($_GET['search']) : '';



// Build filter conditions
$filterConditions = array(
    'Suspended' => 'N',
    'employeeID' => $userDetails->ID
);

if ($filterStatus) {
    $filterConditions['leaveStatusID'] = $filterStatus;
}

if ($filterType) {
    $filterConditions['leaveTypeID'] = $filterType;
}

// Get filtered applications
$filteredApplications = Leave::leave_applications_full($filterConditions, false, $DBConn);

// Ensure we have an array to work with
if (!is_array($filteredApplications)) {
    $filteredApplications = [];
}

//get leave statuses
$leaveStatuses = Leave::leave_status(array('Suspended'=>'N'), false, $DBConn);

// Create status ID mapping for statistics calculation
$statusIds = array();
if ($leaveStatuses && is_array($leaveStatuses)) {
    foreach ($leaveStatuses as $status) {
        $statusName = strtolower($status->leaveStatusName);
        switch ($statusName) {
            case 'approved':
            case 'approve':
                $statusIds['approved'] = $status->leaveStatusID;
                break;
            case 'pending':
            case 'waiting':
            case 'awaiting':
                $statusIds['pending'] = $status->leaveStatusID;
                break;
            case 'rejected':
            case 'reject':
            case 'denied':
                $statusIds['rejected'] = $status->leaveStatusID;
                break;
            case 'draft':
            case 'draft':
                $statusIds['draft'] = $status->leaveStatusID;
                break;
        }
    }
}

// Filter by year and search term
if ($filterYear || $searchTerm) {
    $filteredApplications = array_filter($filteredApplications, function($app) use ($filterYear, $searchTerm, $filterStatus) {
        $yearMatch = !$filterYear || date('Y', strtotime($app->startDate)) == $filterYear;
        $searchMatch = !$searchTerm ||
                      stripos($app->leaveTypeName, $searchTerm) !== false ||
                      stripos($app->leaveComments, $searchTerm) !== false ||
                        stripos($app->leaveStatusName, $searchTerm) !== false ||
                      (!$filterStatus || $app->leaveStatusID == $filterStatus);
        return $yearMatch && $searchMatch;
    });
}

// Calculate statistics from filtered applications using dynamic status IDs
$totalApplications = count($filteredApplications);
$approvedApplications = count(array_filter($filteredApplications, function($app) use ($statusIds) {
    return isset($statusIds['approved']) && $app->leaveStatusID == $statusIds['approved'];
}));
$pendingApplications = count(array_filter($filteredApplications, function($app) use ($statusIds) {
    return isset($statusIds['pending']) && $app->leaveStatusID == $statusIds['pending'];
}));
$rejectedApplications = count(array_filter($filteredApplications, function($app) use ($statusIds) {
    return isset($statusIds['rejected']) && $app->leaveStatusID == $statusIds['rejected'];
}));

// Generate available years for filter dropdown
$availableYears = [];
if (is_array($filteredApplications)) {
    foreach ($filteredApplications as $app) {
        $year = date('Y', strtotime($app->startDate));
        if (!in_array($year, $availableYears)) {
            $availableYears[] = $year;
        }
    }
}
// Add current year if no applications exist
if (empty($availableYears)) {
    $availableYears[] = date('Y');
}
// Sort years in descending order
rsort($availableYears);
?>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-12 mb-4">
        <div class="row">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="metric-icon bg-success bg-opacity-10 text-success">
                                    <i class="ri-check-line fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">Approved</h6>
                                <h4 class="mb-0 fw-bold"><?= $approvedApplications ?></h4>
                                <small class="text-success">
                                    <i class="ri-percent-line"></i>
                                    <?= $totalApplications > 0 ? round(($approvedApplications / $totalApplications) * 100, 1) : 0 ?>%
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
                                <h6 class="text-muted mb-1">Pending</h6>
                                <h4 class="mb-0 fw-bold"><?= $pendingApplications ?></h4>
                                <small class="text-warning">
                                    <i class="ri-eye-line"></i> Awaiting approval
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
                                <div class="metric-icon bg-danger bg-opacity-10 text-danger">
                                    <i class="ri-close-line fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">Rejected</h6>
                                <h4 class="mb-0 fw-bold"><?= $rejectedApplications ?></h4>
                                <small class="text-danger">
                                    <i class="ri-percent-line"></i>
                                    <?= $totalApplications > 0 ? round(($rejectedApplications / $totalApplications) * 100, 1) : 0 ?>%
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0">
                <h6 class="mb-0">
                    <i class="ri-filter-line me-2 text-primary"></i>
                    Filters & Search
                </h6>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <input type="hidden" name="s" value="user">
                    <input type="hidden" name="ss" value="leave">
                    <input type="hidden" name="p" value="leave_management_enhanced">
                    <input type="hidden" name="view" value="history">

                    <div class="col-md-3">
                        <label for="filterStatus" class="form-label">Status</label>
                        <select class="form-select" id="filterStatus" name="status">
                            <?php if ($leaveStatuses): ?>
                                <?php foreach ($leaveStatuses as $status): ?>
                                    <option value="<?= $status->leaveStatusID ?>" <?= $filterStatus == $status->leaveStatusID ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($status->leaveStatusName) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>

                        </select>
                    </div>

                    <div class="col-md-3">
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

                    <div class="col-md-3">
                        <label for="filterYear" class="form-label">Year</label>
                        <select class="form-select" id="filterYear" name="year">
                            <option value="">All Years</option>
                            <?php foreach ($availableYears as $year): ?>
                                <option value="<?= $year ?>" <?= $filterYear == $year ? 'selected' : '' ?>>
                                    <?= $year ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="searchTerm" class="form-label">Search</label>
                        <input type="text"
                               class="form-control"
                               id="searchTerm"
                               name="search"
                               value="<?= htmlspecialchars($searchTerm) ?>"
                               placeholder="Search applications...">
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-search-line me-1"></i>Apply Filters
                        </button>
                        <a href="?s=user&ss=leave&p=leave_management_enhanced&view=history" class="btn btn-outline-secondary ms-2">
                            <i class="ri-refresh-line me-1"></i>Clear Filters
                        </a>
                        <button type="button" class="btn btn-outline-success ms-2" data-action="export-history">
                            <i class="ri-download-line me-1"></i>Export
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Applications Table -->
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="ri-history-line me-2 text-primary"></i>
                        Leave Applications History
                    </h6>
                    <div class="d-flex gap-2">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary active" data-action="toggle-history-view" data-view="table">
                                <i class="ri-table-line"></i>
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-action="toggle-history-view" data-view="timeline">
                                <i class="ri-time-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Table View -->
                <div id="tableView" class="view-content">
                    <?php if (!empty($filteredApplications)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Application ID</th>
                                        <th>Leave Type</th>
                                        <th>Period</th>
                                        <th>Days</th>
                                        <th>Status</th>
                                        <th>Applied Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($filteredApplications as $application): ?>
                                        <tr>
                                            <td>
                                                <span class="application-id fw-medium">
                                                    #<?= $application->leaveApplicationID ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="leave-type-icon me-2 shrink-0 mb-0">
                                                        <?php
                                                        $iconClass = 'ri-calendar-line';
                                                        $iconColor = 'text-primary';

                                                        switch(strtolower($application->leaveTypeName)) {
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
                                                            case 'maternity':
                                                                $iconClass = 'ri-parent-line';
                                                                $iconColor = 'text-info';
                                                                break;
                                                            case 'paternity':
                                                                $iconClass = 'ri-user-heart-line';
                                                                $iconColor = 'text-warning';
                                                                break;
                                                            case 'emergency':
                                                                $iconClass = 'ri-alarm-warning-line';
                                                                $iconColor = 'text-danger';
                                                                break;
                                                        }?>
                                                        <i class="<?= $iconClass ?> <?= $iconColor ?>"></i>
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
                                                switch($application->leaveStatusID) {
                                                    case 1:
                                                        $statusClass = 'status-draft';
                                                        $statusIcon = 'ri-draft-line';
                                                        break;
                                                    case 2:
                                                        $statusClass = 'status-draft';
                                                        $statusIcon = 'ri-edit-line';
                                                        break;
                                                    case 3:
                                                        $statusClass = 'status-pending';
                                                        $statusIcon = 'ri-time-line';
                                                        break;
                                                    case 4:
                                                        $statusClass = 'status-approved';
                                                        $statusIcon = 'ri-check-line';
                                                        break;
                                                    case 5:
                                                        $statusClass = 'status-rejected';
                                                        $statusIcon = 'ri-close-line';
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
                                                    <button type="button"
                                                            class="btn btn-outline-primary"
                                                            data-action="view-application-details"
                                                            data-application-id="<?= $application->leaveApplicationID ?>"
                                                            title="View Details">
                                                        <i class="ri-eye-line"></i>
                                                    </button>

                                                    <?php if ($application->leaveStatusID == 1 || $application->leaveStatusID == 2): ?>
                                                        <button type="button"
                                                                class="btn btn-outline-secondary"
                                                                data-action="edit-application"
                                                                data-application-id="<?= $application->leaveApplicationID ?>"
                                                                title="Edit">
                                                            <i class="ri-edit-line"></i>
                                                        </button>
                                                    <?php endif; ?>

                                                    <?php if ($application->leaveStatusID == 2): ?>
                                                        <button type="button"
                                                                class="btn btn-outline-success"
                                                                data-action="submit-for-approval"
                                                                data-application-id="<?= $application->leaveApplicationID ?>"
                                                                title="Submit for Approval">
                                                            <i class="ri-send-plane-line"></i>
                                                        </button>
                                                    <?php endif; ?>

                                                    <button type="button"
                                                            class="btn btn-outline-info"
                                                            data-action="download-application"
                                                            data-application-id="<?= $application->leaveApplicationID ?>"
                                                            title="Download">
                                                        <i class="ri-download-line"></i>
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
                            <i class="ri-file-list-line fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">No Applications Found</h5>
                            <p class="text-muted">No leave applications match your current filters.</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#applyLeaveModal">
                                <i class="ri-add-line me-1"></i>Apply for Leave
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Timeline View -->
                <div id="timelineView" class="view-content" style="display: none;">
                    <?php if (!empty($filteredApplications)): ?>
                        <div class="timeline-container">
                            <?php foreach ($filteredApplications as $index => $application): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker">
                                        <div class="timeline-icon">
                                            <?php
                                            $iconClass = 'ri-calendar-line';
                                            switch($application->leaveStatusID) {
                                                case 4: $iconClass = 'ri-check-line'; break;
                                                case 5: $iconClass = 'ri-close-line'; break;
                                                case 3: $iconClass = 'ri-time-line'; break;
                                                default: $iconClass = 'ri-edit-line'; break;
                                            }
                                            ?>
                                            <i class="<?= $iconClass ?>"></i>
                                        </div>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-header">
                                            <h6 class="mb-1"><?= htmlspecialchars($application->leaveTypeName) ?> Leave</h6>
                                            <span class="timeline-date">
                                                <?= date('M d, Y', strtotime($application->DateAdded)) ?>
                                            </span>
                                        </div>
                                        <div class="timeline-body">
                                            <p class="mb-2">
                                                <strong>Period:</strong>
                                                <?= date('M d', strtotime($application->startDate)) ?> -
                                                <?= date('M d, Y', strtotime($application->endDate)) ?>
                                                (<?= $application->noOfDays ?: Leave::countWeekdays($application->startDate, $application->endDate) ?> days)
                                            </p>
                                            <?php if ($application->leaveComments): ?>
                                                <p class="mb-2">
                                                    <strong>Reason:</strong>
                                                    <?= htmlspecialchars(substr($application->leaveComments, 0, 100)) ?>
                                                    <?= strlen($application->leaveComments) > 100 ? '...' : '' ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                            <div class="timeline-footer">
                                            <span class="leave-status-badge <?= $statusClass ?>">
                                                <i class="<?= $statusIcon ?> me-1"></i>
                                                <?= htmlspecialchars($application->leaveStatusName) ?>
                                            </span>
                                                <div class="timeline-actions">
                                                    <button type="button"
                                                            class="btn btn-sm btn-outline-primary"
                                                            data-action="view-application-details"
                                                            data-application-id="<?= $application->leaveApplicationID ?>">
                                                    View Details
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="ri-time-line fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">No Applications Found</h5>
                            <p class="text-muted">No leave applications match your current filters.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- History View JavaScript -->
<script>
/**
 * History View Specific Functionality
 */

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-action="export-history"]').forEach(button => {
        button.addEventListener('click', () => {
            if (typeof exportHistory === 'function') {
                exportHistory();
            }
        });
    });

    document.querySelectorAll('[data-action="toggle-history-view"]').forEach(button => {
        button.addEventListener('click', () => {
            const viewType = button.dataset.view || 'table';
            toggleView(viewType, button);
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

    document.querySelectorAll('[data-action="submit-for-approval"]').forEach(button => {
        button.addEventListener('click', () => {
            const applicationId = Number(button.dataset.applicationId);
            if (typeof submitForApproval === 'function') {
                submitForApproval(applicationId);
            }
        });
    });

    document.querySelectorAll('[data-action="download-application"]').forEach(button => {
        button.addEventListener('click', () => {
            const applicationId = Number(button.dataset.applicationId);
            if (typeof downloadApplication === 'function') {
                downloadApplication(applicationId);
            }
        });
    });
});

function toggleView(viewType, trigger) {
    const tableView = document.getElementById('tableView');
    const timelineView = document.getElementById('timelineView');
    const buttons = document.querySelectorAll('[data-action="toggle-history-view"]');

    buttons.forEach(btn => btn.classList.remove('active'));

    if (trigger) {
        trigger.classList.add('active');
    } else {
        const fallback = Array.from(buttons).find(btn => btn.dataset.view === viewType);
        fallback?.classList.add('active');
    }

    if (!tableView || !timelineView) {
        return;
    }

    if (viewType === 'table') {
        tableView.style.display = 'block';
        timelineView.style.display = 'none';
    } else {
        tableView.style.display = 'none';
        timelineView.style.display = 'block';
    }
}

function exportHistory() {
    showToast('info', 'Exporting Data', 'Your leave history is being prepared for download...');

    // Simulate export process
    setTimeout(() => {
        showToast('success', 'Export Ready', 'Your leave history has been exported successfully.');
        // In a real implementation, this would trigger a download
    }, 2000);
}

function viewApplicationDetails(applicationId) {
    // Open application details modal
    console.log('Viewing application:', applicationId);
    // Implementation would open a details modal
}

function editApplication(applicationId) {
    // Open edit modal
    console.log('Editing application:', applicationId);
    // Implementation would open the edit modal
}

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
}

function downloadApplication(applicationId) {
    showToast('info', 'Downloading', 'Preparing application for download...');

    // Simulate download
    setTimeout(() => {
        showToast('success', 'Download Started', 'Your application document is being downloaded.');
    }, 1000);
}
</script>

<style>
/* History View Specific Styles */
.metric-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.application-id {
    font-family: 'Courier New', monospace;
    color: #6c757d;
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

.leave-status-badge {
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
}

.status-draft { background-color: #e9ecef; color: #495057; }
.status-pending { background-color: #fff3cd; color: #664d03; }
.status-approved { background-color: #d1e7dd; color: #0f5132; }
.status-rejected { background-color: #f8d7da; color: #842029; }

/* Timeline Styles */
.timeline-container {
    position: relative;
    padding-left: 2rem;
}

.timeline-container::before {
    content: '';
    position: absolute;
    left: 1rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-marker {
    position: absolute;
    left: -2rem;
    top: 0.5rem;
}

.timeline-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: white;
    border: 3px solid #007bff;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #007bff;
    font-size: 0.8rem;
}

.timeline-content {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.timeline-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.timeline-date {
    color: #6c757d;
    font-size: 0.8rem;
}

.timeline-body {
    margin-bottom: 1rem;
}

.timeline-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.timeline-actions {
    display: flex;
    gap: 0.5rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .timeline-container {
        padding-left: 1.5rem;
    }

    .timeline-marker {
        left: -1.5rem;
    }

    .timeline-icon {
        width: 28px;
        height: 28px;
        font-size: 0.7rem;
    }

    .timeline-footer {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}
</style>
