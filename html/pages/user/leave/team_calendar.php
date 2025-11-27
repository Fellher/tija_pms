<?php
/**
 * Team Leave Calendar
 * Interactive calendar showing team leave applications with filters and color coding
 */

if(!$isValidUser) {
    Alert::info("You need to be logged in to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

// Get user details
$employeeID = $userDetails->ID;
$entityID = $userDetails->entityID ?? 1;
$orgDataID = $userDetails->orgDataID ?? 1;
$departmentID = $userDetails->businessUnitID ?? null;

// Get filter parameters
$filterType = isset($_GET['filter']) ? Utility::clean_string($_GET['filter']) : 'department';
$filterValue = isset($_GET['filterValue']) ? Utility::clean_string($_GET['filterValue']) : '';
$selectedEmployeeID = isset($_GET['employeeID']) ? Utility::clean_string($_GET['employeeID']) : '';

// Get leave statuses for color coding
$leaveStatuses = Leave::leave_status(array('Suspended' => 'N'), false, $DBConn);

// Get departments/business units for filter
$departments = Data::business_units(array('entityID' => $entityID, 'Suspended' => 'N'), false, $DBConn);

// Get all employees for filter dropdown (based on access level)
// Use Employee class methods for employee interactions
if ($filterType == 'department' && $filterValue) {
    // Get employees by specific department
    $employees = Employee::get_department_members($filterValue, $DBConn);
} elseif ($filterType == 'department' && $departmentID && !$filterValue) {
    // Get employees from user's department
    $employees = Employee::get_department_members($departmentID, $DBConn);
} else {
    // Get all employees for entity/organization
    $employees = Employee::get_all_employees($orgDataID, $entityID, $DBConn);
}

// Get leave types
$leaveTypes = Leave::leave_types(array('Lapsed' => 'N'), false, $DBConn);

?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-medium fs-24 mb-0">
            <i class="ri-calendar-line me-2 text-primary"></i>
            Team Leave Calendar
        </h1>
        <p class="text-muted mb-0 mt-2">View and track team leave schedules across the organization</p>
    </div>
    <div class="ms-md-1 ms-0">
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#applyLeaveModal">
                <i class="ri-add-line me-1"></i>Apply Leave
            </button>
            <a href="<?= $base ?>html/?s=user&ss=leave&p=leave_calendar_interactive" class="btn btn-outline-primary btn-sm">
                <i class="ri-calendar-2-line me-1"></i>Interactive Calendar
            </a>
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Leave</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Team Calendar</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- Filters and Controls -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="ri-filter-3-line me-2"></i>Filters & Options
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <!-- View Type Filter -->
                    <div class="col-md-3">
                        <label class="form-label">View By</label>
                        <select class="form-select" id="filterType" data-action="calendar-filter-type">
                            <option value="department" <?= $filterType == 'department' ? 'selected' : '' ?>>My Department</option>
                            <option value="organization" <?= $filterType == 'organization' ? 'selected' : '' ?>>Entire Organization</option>
                            <option value="entity" <?= $filterType == 'entity' ? 'selected' : '' ?>>Entity</option>
                            <option value="employee" <?= $filterType == 'employee' ? 'selected' : '' ?>>Specific Employee</option>
                        </select>
                    </div>

                    <!-- Department Filter -->
                    <div class="col-md-3" id="departmentFilterDiv" style="<?= $filterType != 'department' ? 'display: none;' : '' ?>">
                        <label class="form-label">Department</label>
                        <select class="form-select" id="departmentFilter">
                            <option value="">All Departments</option>
                            <?php if ($departments && is_array($departments)): ?>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept->businessUnitID ?>" <?= $filterValue == $dept->businessUnitID ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($dept->businessUnitName) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Employee Filter -->
                    <div class="col-md-3" id="employeeFilterDiv" style="<?= $filterType != 'employee' ? 'display: none;' : '' ?>">
                        <label class="form-label">Employee</label>
                        <select class="form-select" id="employeeFilter">
                            <option value="">Select Employee</option>
                            <?php if ($employees && is_array($employees)): ?>
                                <?php foreach ($employees as $emp): ?>
                                    <option value="<?= $emp->ID ?>" <?= $selectedEmployeeID == $emp->ID ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($emp->FirstName . ' ' . $emp->Surname) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Leave Type Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Leave Type</label>
                        <select class="form-select" id="leaveTypeFilter">
                            <option value="">All Types</option>
                            <?php if ($leaveTypes && is_array($leaveTypes)): ?>
                                <?php foreach ($leaveTypes as $type): ?>
                                    <option value="<?= $type->leaveTypeID ?>">
                                        <?= htmlspecialchars($type->leaveTypeName) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <!-- Apply Filter Button -->
                    <div class="col-md-12">
                        <button class="btn btn-primary" data-action="calendar-apply-filters">
                            <i class="ri-filter-line me-2"></i>Apply Filters
                        </button>
                        <button class="btn btn-secondary" data-action="calendar-reset-filters">
                            <i class="ri-refresh-line me-2"></i>Reset
                        </button>
                        <button class="btn btn-info" data-action="calendar-export">
                            <i class="ri-download-line me-2"></i>Export
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Legend -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <span class="fw-semibold me-2">
                        <i class="ri-information-line me-1"></i>Leave Status:
                    </span>
                    <?php if ($leaveStatuses && is_array($leaveStatuses)): ?>
                        <?php
                        $statusColors = array(
                            'Draft' => '#6c757d',
                            'Pending' => '#ffc107',
                            'Approved' => '#28a745',
                            'Rejected' => '#dc3545',
                            'Cancelled' => '#6c757d',
                            'In Progress' => '#17a2b8',
                            'Completed' => '#28a745'
                        );
                        ?>
                        <?php foreach ($leaveStatuses as $status): ?>
                            <?php $color = $statusColors[$status->leaveStatusName] ?? '#6c757d'; ?>
                            <span class="badge" style="background-color: <?= $color ?>;">
                                <i class="ri-checkbox-blank-circle-fill me-1"></i>
                                <?= htmlspecialchars($status->leaveStatusName) ?>
                            </span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Calendar Container -->
<div class="row">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">
                    <i class="ri-calendar-2-line me-2"></i>
                    <span id="calendarTitle">Leave Calendar</span>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-action="change-calendar-view" data-view="dayGridMonth">
                        Month
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-action="change-calendar-view" data-view="timeGridWeek">
                        Week
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-action="change-calendar-view" data-view="listMonth">
                        List
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="teamLeaveCalendar"></div>
            </div>
        </div>
    </div>
</div>

<!-- Leave Details Modal -->
<div class="modal fade" id="leaveDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri-information-line me-2"></i>Leave Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="leaveDetailsContent">
                <!-- Leave details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Include FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />

<style>
.fc {
    font-size: 14px;
}

.fc-event {
    cursor: pointer;
    border: none;
    padding: 2px 5px;
    margin: 1px 0;
}

.fc-event:hover {
    opacity: 0.8;
}

.fc-daygrid-event {
    white-space: normal;
}

.fc-event-title {
    font-weight: 500;
}

.leave-detail-item {
    padding: 10px;
    margin-bottom: 10px;
    border-left: 3px solid #007bff;
    background-color: #f8f9fa;
}

.leave-detail-item strong {
    display: inline-block;
    width: 150px;
}

#teamLeaveCalendar {
    min-height: 600px;
}

.fc-toolbar-title {
    font-size: 1.5rem !important;
    font-weight: 600;
}

/* Custom status colors */
.status-draft { background-color: #6c757d !important; }
.status-pending { background-color: #ffc107 !important; color: #000 !important; }
.status-approved { background-color: #28a745 !important; }
.status-rejected { background-color: #dc3545 !important; }
.status-cancelled { background-color: #6c757d !important; }
.status-in-progress { background-color: #17a2b8 !important; }
.status-completed { background-color: #28a745 !important; }
</style>

<!-- Include FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

<script>
let calendar;
let currentFilters = {
    filterType: '<?= $filterType ?>',
    filterValue: '<?= $filterValue ?>',
    employeeID: '<?= $selectedEmployeeID ?>',
    leaveType: '',
    entityID: <?= $entityID ?>,
    orgDataID: <?= $orgDataID ?>
};

document.addEventListener('DOMContentLoaded', function() {
    initializeCalendar();
    setupCalendarControls();
});

function setupCalendarControls() {
    document.querySelectorAll('[data-action="calendar-apply-filters"]').forEach(button => {
        button.addEventListener('click', applyFilters);
    });

    document.querySelectorAll('[data-action="calendar-reset-filters"]').forEach(button => {
        button.addEventListener('click', resetFilters);
    });

    document.querySelectorAll('[data-action="calendar-export"]').forEach(button => {
        button.addEventListener('click', exportCalendar);
    });

    document.querySelectorAll('[data-action="change-calendar-view"]').forEach(button => {
        button.addEventListener('click', () => {
            const view = button.dataset.view || 'dayGridMonth';
            changeView(view);
        });
    });

    const filterTypeSelect = document.querySelector('[data-action="calendar-filter-type"]');
    if (filterTypeSelect) {
        filterTypeSelect.addEventListener('change', updateFilterOptions);
    }
}

function initializeCalendar() {
    const calendarEl = document.getElementById('teamLeaveCalendar');

    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listMonth'
        },
        height: 'auto',
        events: function(info, successCallback, failureCallback) {
            fetchLeaveData(info.startStr, info.endStr, successCallback, failureCallback);
        },
        eventClick: function(info) {
            showLeaveDetails(info.event);
        },
        eventDidMount: function(info) {
            // Add tooltip
            info.el.title = info.event.title + '\n' +
                           'Status: ' + info.event.extendedProps.statusName + '\n' +
                           'Days: ' + info.event.extendedProps.noOfDays;
        },
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },
        displayEventTime: false,
        eventDisplay: 'block',
        dayMaxEvents: 3,
        moreLinkClick: 'popover',
        weekends: true,
        navLinks: true,
        selectable: false,
        selectMirror: true,
        nowIndicator: true,
        dayCellDidMount: function(info) {
            // Make day cells clickable to apply for leave
            info.el.style.cursor = 'pointer';
            info.el.title = 'Click to apply for leave on this day';
            info.el.addEventListener('click', function(e) {
                // Don't trigger if clicking on an event, more link, or popover
                if (!e.target.closest('.fc-event') &&
                    !e.target.closest('.fc-more-link') &&
                    !e.target.closest('.fc-popover') &&
                    !e.target.closest('.fc-daygrid-event')) {
                    handleDayClick(info.date);
                }
            });
        }
    });

    calendar.render();
}

// Handle day click - open leave application modal
function handleDayClick(date) {
    const dateStr = date.toISOString().split('T')[0];

    // Store date in sessionStorage for modal to pick up
    sessionStorage.setItem('prefillStartDate', dateStr);
    sessionStorage.removeItem('prefillEndDate'); // Clear end date

    // Open leave application modal
    if (typeof window.openApplyLeaveModal === 'function') {
        window.openApplyLeaveModal();
    } else if (typeof openApplyLeaveModal === 'function') {
        openApplyLeaveModal();
    } else {
        // Fallback: redirect to apply leave page
        window.location.href = '<?= $base ?>html/?s=user&ss=leave&p=apply_leave_workflow&startDate=' + dateStr;
    }
}

function fetchLeaveData(startDate, endDate, successCallback, failureCallback) {
    const params = new URLSearchParams({
        action: 'get_team_leave',
        startDate: startDate,
        endDate: endDate,
        ...currentFilters
    });

    fetch('<?= $base ?>php/scripts/leave/utilities/team_calendar_data.php?' + params)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const events = data.leaves.map(leave => {
                    const statusClass = 'status-' + leave.statusName.toLowerCase().replace(' ', '-');
                    return {
                        id: leave.leaveApplicationID,
                        title: leave.employeeName + ' - ' + leave.leaveTypeName,
                        start: leave.startDate,
                        end: leave.endDate,
                        backgroundColor: leave.statusColor,
                        borderColor: leave.statusColor,
                        className: statusClass,
                        extendedProps: {
                            employeeID: leave.employeeID,
                            employeeName: leave.employeeName,
                            leaveTypeID: leave.leaveTypeID,
                            leaveTypeName: leave.leaveTypeName,
                            statusID: leave.statusID,
                            statusName: leave.statusName,
                            noOfDays: leave.noOfDays,
                            leaveReason: leave.leaveReason,
                            departmentName: leave.departmentName,
                            applicationID: leave.leaveApplicationID
                        }
                    };
                });
                successCallback(events);
            } else {
                failureCallback(data.message || 'Failed to load leave data');
            }
        })
        .catch(error => {
            console.error('Error fetching leave data:', error);
            failureCallback(error);
        });
}

function showLeaveDetails(event) {
    const props = event.extendedProps;
    const startDate = new Date(event.start).toLocaleDateString('en-US', {
        year: 'numeric', month: 'long', day: 'numeric'
    });
    const endDate = new Date(event.end || event.start).toLocaleDateString('en-US', {
        year: 'numeric', month: 'long', day: 'numeric'
    });

    const detailsHtml = `
        <div class="leave-detail-item">
            <strong>Employee:</strong> ${props.employeeName}
        </div>
        <div class="leave-detail-item">
            <strong>Department:</strong> ${props.departmentName || 'N/A'}
        </div>
        <div class="leave-detail-item">
            <strong>Leave Type:</strong> ${props.leaveTypeName}
        </div>
        <div class="leave-detail-item">
            <strong>Status:</strong>
            <span class="badge" style="background-color: ${event.backgroundColor};">
                ${props.statusName}
            </span>
        </div>
        <div class="leave-detail-item">
            <strong>Start Date:</strong> ${startDate}
        </div>
        <div class="leave-detail-item">
            <strong>End Date:</strong> ${endDate}
        </div>
        <div class="leave-detail-item">
            <strong>Number of Days:</strong> ${props.noOfDays}
        </div>
        <div class="leave-detail-item">
            <strong>Reason:</strong> ${props.leaveReason || 'Not provided'}
        </div>
        <div class="mt-3 text-end">
            <a href="<?= $base ?>html/?s=user&ss=leave&p=leave_management_enhanced&view=history&applicationID=${props.applicationID}"
               class="btn btn-sm btn-primary">
                <i class="ri-eye-line me-1"></i>View Full Details
            </a>
        </div>
    `;

    document.getElementById('leaveDetailsContent').innerHTML = detailsHtml;
    const modal = new bootstrap.Modal(document.getElementById('leaveDetailsModal'));
    modal.show();
}

function updateFilterOptions() {
    const filterType = document.getElementById('filterType').value;
    const departmentDiv = document.getElementById('departmentFilterDiv');
    const employeeDiv = document.getElementById('employeeFilterDiv');

    // Hide all filter divs
    departmentDiv.style.display = 'none';
    employeeDiv.style.display = 'none';

    // Show relevant filter
    if (filterType === 'department') {
        departmentDiv.style.display = 'block';
    } else if (filterType === 'employee') {
        employeeDiv.style.display = 'block';
    }
}

function applyFilters() {
    const filterType = document.getElementById('filterType').value;
    currentFilters.filterType = filterType;

    if (filterType === 'department') {
        currentFilters.filterValue = document.getElementById('departmentFilter').value;
        currentFilters.employeeID = '';
    } else if (filterType === 'employee') {
        currentFilters.employeeID = document.getElementById('employeeFilter').value;
        currentFilters.filterValue = '';
    } else {
        currentFilters.filterValue = '';
        currentFilters.employeeID = '';
    }

    currentFilters.leaveType = document.getElementById('leaveTypeFilter').value;

    // Update calendar title
    updateCalendarTitle();

    // Refetch events
    calendar.refetchEvents();
}

function resetFilters() {
    document.getElementById('filterType').value = 'department';
    document.getElementById('departmentFilter').value = '';
    document.getElementById('employeeFilter').value = '';
    document.getElementById('leaveTypeFilter').value = '';

    currentFilters = {
        filterType: 'department',
        filterValue: '',
        employeeID: '',
        leaveType: '',
        entityID: <?= $entityID ?>,
        orgDataID: <?= $orgDataID ?>
    };

    updateFilterOptions();
    updateCalendarTitle();
    calendar.refetchEvents();
}

function updateCalendarTitle() {
    const filterType = currentFilters.filterType;
    let title = 'Leave Calendar';

    if (filterType === 'department') {
        title = 'Department Leave Calendar';
    } else if (filterType === 'organization') {
        title = 'Organization Leave Calendar';
    } else if (filterType === 'entity') {
        title = 'Entity Leave Calendar';
    } else if (filterType === 'employee') {
        const select = document.getElementById('employeeFilter');
        const selectedText = select.options[select.selectedIndex]?.text || 'Employee';
        title = selectedText + ' Leave Calendar';
    }

    document.getElementById('calendarTitle').textContent = title;
}

function changeView(viewType) {
    calendar.changeView(viewType);
}

function exportCalendar() {
    if (typeof showToast === 'function') {
        showToast('Export functionality will be implemented. This will export the current calendar view to PDF/Excel.', 'info');
    } else {
        alert('Export functionality will be implemented. This will export the current calendar view to PDF/Excel.');
    }
}

// Initialize filter options on page load
updateFilterOptions();
updateCalendarTitle();

// Listen for leave application submission to refresh calendar
window.addEventListener('leaveApplicationSubmitted', function() {
    if (calendar) {
        calendar.refetchEvents();
    }
});
</script>

<!-- Include Leave Management Modals -->
<?php include 'modals/leave_modals_include.php'; ?>

