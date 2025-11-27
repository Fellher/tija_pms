<?php
/**
 * Interactive Leave Calendar
 *
 * Dedicated calendar page for viewing employee leave schedules and applying for leave
 * Features:
 * - Filterable employee selection
 * - Leave type and status filters
 * - Click on calendar days to apply for leave
 * - View leave application details
 * - Month/Week/Day view toggles
 */

if(!$isValidUser) {
    Alert::info("You need to be logged in to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

// Initialize user context
$employeeID = $userDetails->ID;
$entityID = $userDetails->entityID ?? 1;
$orgDataID = $userDetails->orgDataID ?? 1;
$employeeDetails = Employee::employees(array('ID'=>$employeeID), true, $DBConn);

// Get leave types and statuses for filters
$leaveTypes = Leave::leave_types(array('Lapsed'=>'N'), false, $DBConn);
$leaveStatuses = Leave::leave_status(array('Suspended' => 'N'), false, $DBConn);

// Get holidays
$holidays = Leave::get_global_holidays('Kenya', null, $DBConn);
?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-medium fs-24 mb-0">
            <i class="ri-calendar-line me-2 text-primary"></i>
            Interactive Leave Calendar
        </h1>
        <p class="text-muted mb-0 mt-2">View employee leave schedules and apply for leave by clicking on calendar days</p>
    </div>
    <div class="ms-md-1 ms-0">
        <div class="d-flex align-items-center gap-2">
            <a href="<?= $base ?>html/?s=user&ss=leave&p=team_calendar" class="btn btn-outline-secondary btn-sm">
                <i class="ri-team-line me-1"></i>Team Calendar
            </a>
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);">Leave</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Interactive Calendar</li>
                </ol>
            </nav>
        </div>
    </div>
</div>

<!-- Summary Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-md bg-primary bg-opacity-10 text-primary rounded">
                            <i class="ri-calendar-check-line fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1 small">On Leave Today</h6>
                        <h4 class="mb-0 fw-bold" id="onLeaveToday">0</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-md bg-warning bg-opacity-10 text-warning rounded">
                            <i class="ri-time-line fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1 small">Pending Approvals</h6>
                        <h4 class="mb-0 fw-bold" id="pendingApprovals">0</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-md bg-info bg-opacity-10 text-info rounded">
                            <i class="ri-team-line fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1 small">Employees Visible</h6>
                        <h4 class="mb-0 fw-bold" id="employeesVisible">0</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar avatar-md bg-success bg-opacity-10 text-success rounded">
                            <i class="ri-calendar-event-line fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1 small">Leave Events</h6>
                        <h4 class="mb-0 fw-bold" id="leaveEventsCount">0</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Panel -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ri-filter-3-line me-2"></i>Filters
                    </h5>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="clearFiltersBtn">
                        <i class="ri-close-line me-1"></i>Clear All
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <!-- Employee Filter -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Employees</label>
                        <select class="form-select" id="employeeFilter" multiple size="5">
                            <option value="">Loading employees...</option>
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple employees</small>
                    </div>

                    <!-- Leave Type Filter -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Leave Types</label>
                        <div class="filter-checkboxes" style="max-height: 150px; overflow-y: auto;">
                            <?php if ($leaveTypes && is_array($leaveTypes)): ?>
                                <?php foreach ($leaveTypes as $leaveType): ?>
                                <div class="form-check">
                                    <input class="form-check-input leaveTypeFilter" type="checkbox"
                                           value="<?= $leaveType->leaveTypeID ?>"
                                           id="leaveType<?= $leaveType->leaveTypeID ?>" checked>
                                    <label class="form-check-label" for="leaveType<?= $leaveType->leaveTypeID ?>">
                                        <?= htmlspecialchars($leaveType->leaveTypeName) ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted small">No leave types available</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Leave Status</label>
                        <div class="filter-checkboxes" style="max-height: 150px; overflow-y: auto;">
                            <?php if ($leaveStatuses && is_array($leaveStatuses)): ?>
                                <?php foreach ($leaveStatuses as $status): ?>
                                <div class="form-check">
                                    <input class="form-check-input statusFilter" type="checkbox"
                                           value="<?= $status->leaveStatusID ?>"
                                           id="status<?= $status->leaveStatusID ?>" checked>
                                    <label class="form-check-label" for="status<?= $status->leaveStatusID ?>">
                                        <span class="badge" style="background-color: <?= $status->leaveStatusColor ?? '#6c757d' ?>; color: #fff; padding: 2px 8px;">
                                            <?= htmlspecialchars($status->leaveStatusName) ?>
                                        </span>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted small">No statuses available</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Calendar Container -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ri-calendar-line me-2"></i>Calendar
                    </h5>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary active" id="monthViewBtn" data-view="dayGridMonth">
                            <i class="ri-calendar-line me-1"></i>Month
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="weekViewBtn" data-view="timeGridWeek">
                            <i class="ri-calendar-week-line me-1"></i>Week
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="dayViewBtn" data-view="timeGridDay">
                            <i class="ri-calendar-day-line me-1"></i>Day
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="interactiveLeaveCalendar"></div>
            </div>
        </div>
    </div>
</div>

<!-- Legend -->
<div class="row mt-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">Legend</h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <div class="legend-color me-2" style="width: 20px; height: 20px; background-color: #198754; border-radius: 4px; border: 1px solid #dee2e6;"></div>
                            <small>Your Leave Applications</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-2">
                            <div class="legend-color me-2" style="width: 20px; height: 20px; background-color: #0dcaf0; border-radius: 4px; border: 1px solid #dee2e6;"></div>
                            <small>Other Employees' Leave</small>
                        </div>
                    </div>
                </div>
                <?php if ($leaveStatuses && is_array($leaveStatuses)): ?>
                <div class="row mt-2">
                    <div class="col-12">
                        <small class="text-muted">Status Colors: </small>
                        <?php foreach ($leaveStatuses as $status): ?>
                        <span class="badge me-2" style="background-color: <?= $status->leaveStatusColor ?? '#6c757d' ?>; color: #fff;">
                            <?= htmlspecialchars($status->leaveStatusName) ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />

<!-- FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

<script>
let interactiveCalendar = null;
let currentFilters = {
    employeeIDs: [],
    leaveTypeIDs: [],
    statusIDs: []
};

// Load filterable employees
function loadFilterableEmployees() {
    fetch('<?= $base ?>php/scripts/leave/utilities/get_filterable_employees.php?filterType=team')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.employees) {
                const select = document.getElementById('employeeFilter');
                select.innerHTML = '';

                // Add "All Employees" option
                const allOption = document.createElement('option');
                allOption.value = 'all';
                allOption.textContent = 'All Employees';
                allOption.selected = true;
                select.appendChild(allOption);

                // Add individual employees
                data.employees.forEach(emp => {
                    const option = document.createElement('option');
                    option.value = emp.id;
                    option.textContent = `${emp.name} (${emp.jobTitle})`;
                    option.selected = true;
                    select.appendChild(option);
                });

                // Update visible employees count
                document.getElementById('employeesVisible').textContent = data.count;

                // Initialize with all employees selected
                updateEmployeeFilter();
            }
        })
        .catch(error => {
            console.error('Error loading employees:', error);
        });
}

// Update employee filter
function updateEmployeeFilter() {
    const select = document.getElementById('employeeFilter');
    const selectedOptions = Array.from(select.selectedOptions);

    if (selectedOptions.some(opt => opt.value === 'all')) {
        // All selected - get all employee IDs
        const allOptions = Array.from(select.options).filter(opt => opt.value !== 'all' && opt.value !== '');
        currentFilters.employeeIDs = allOptions.map(opt => opt.value);
    } else {
        currentFilters.employeeIDs = selectedOptions
            .filter(opt => opt.value !== 'all' && opt.value !== '')
            .map(opt => opt.value);
    }

    refreshCalendar();
}

// Update leave type filter
function updateLeaveTypeFilter() {
    const checked = Array.from(document.querySelectorAll('.leaveTypeFilter:checked'));
    currentFilters.leaveTypeIDs = checked.map(cb => cb.value);
    refreshCalendar();
}

// Update status filter
function updateStatusFilter() {
    const checked = Array.from(document.querySelectorAll('.statusFilter:checked'));
    currentFilters.statusIDs = checked.map(cb => cb.value);
    refreshCalendar();
}

// Clear all filters
function clearAllFilters() {
    // Reset employee filter to "all"
    const employeeSelect = document.getElementById('employeeFilter');
    employeeSelect.selectedIndex = 0; // Select "All Employees"

    // Check all leave types
    document.querySelectorAll('.leaveTypeFilter').forEach(cb => cb.checked = true);

    // Check all statuses
    document.querySelectorAll('.statusFilter').forEach(cb => cb.checked = true);

    // Update filters
    updateEmployeeFilter();
    updateLeaveTypeFilter();
    updateStatusFilter();
}

// Refresh calendar with current filters
function refreshCalendar() {
    if (interactiveCalendar) {
        interactiveCalendar.refetchEvents();
        updateStatistics();
    }
}

// Update statistics
function updateStatistics() {
    // This will be called after events are loaded
    // For now, we'll update after calendar events are fetched
}

// Initialize calendar
function initializeInteractiveCalendar() {
    const calendarEl = document.getElementById('interactiveLeaveCalendar');
    if (!calendarEl) {
        console.error('Calendar element not found');
        return;
    }

    if (typeof FullCalendar === 'undefined') {
        calendarEl.innerHTML = '<div class="alert alert-danger">Calendar library not loaded. Please refresh the page.</div>';
        return;
    }

    try {
        interactiveCalendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            selectable: false, // We handle day clicks manually
            dayCellDidMount: function(info) {
                // Make day cells clickable
                info.el.style.cursor = 'pointer';
                info.el.addEventListener('click', function() {
                    handleDayClick(info.date);
                });
            },
            events: function(fetchInfo, successCallback, failureCallback) {
                // Build query string with filters
                const params = new URLSearchParams({
                    start: fetchInfo.startStr,
                    end: fetchInfo.endStr
                });

                if (currentFilters.employeeIDs.length > 0) {
                    params.append('employeeIDs', JSON.stringify(currentFilters.employeeIDs));
                }
                if (currentFilters.leaveTypeIDs.length > 0) {
                    params.append('leaveTypeIDs', JSON.stringify(currentFilters.leaveTypeIDs));
                }
                if (currentFilters.statusIDs.length > 0) {
                    params.append('statusIDs', JSON.stringify(currentFilters.statusIDs));
                }

                fetch(`<?= $base ?>php/scripts/leave/utilities/get_calendar_leave_applications.php?${params.toString()}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data && data.success && Array.isArray(data.events)) {
                            // Update statistics
                            document.getElementById('leaveEventsCount').textContent = data.events.length;

                            // Count on leave today
                            const today = new Date().toISOString().split('T')[0];
                            const onLeaveToday = data.events.filter(e => {
                                const start = e.start.split('T')[0];
                                const end = e.end ? e.end.split('T')[0] : start;
                                return start <= today && end >= today;
                            }).length;
                            document.getElementById('onLeaveToday').textContent = onLeaveToday;

                            // Count pending approvals
                            const pending = data.events.filter(e =>
                                e.extendedProps.leaveStatusName &&
                                e.extendedProps.leaveStatusName.toLowerCase().includes('pending')
                            ).length;
                            document.getElementById('pendingApprovals').textContent = pending;

                            successCallback(data.events);
                        } else {
                            successCallback([]);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching leave applications:', error);
                        successCallback([]);
                    });
            },
            eventClick: function(info) {
                // Show leave application details
                const event = info.event;
                const props = event.extendedProps;

                let message = `<div class="text-start">`;
                message += `<p><strong>Leave Type:</strong> ${props.leaveTypeName}</p>`;
                message += `<p><strong>Employee:</strong> ${props.employeeName}</p>`;
                message += `<p><strong>Status:</strong> <span class="badge" style="background-color: ${info.event.backgroundColor}">${props.leaveStatusName}</span></p>`;
                message += `<p><strong>Days:</strong> ${props.noOfDays}</p>`;
                message += `<p><strong>Start Date:</strong> ${info.event.start.toLocaleDateString()}</p>`;
                message += `<p><strong>End Date:</strong> ${info.event.end ? new Date(new Date(info.event.end).getTime() - 86400000).toLocaleDateString() : 'N/A'}</p>`;
                if (props.halfDayLeave === 'Y') {
                    message += `<p><strong>Half Day:</strong> ${props.halfDayPeriod || 'Yes'}</p>`;
                }
                if (props.leaveComments) {
                    message += `<p><strong>Comments:</strong> ${props.leaveComments}</p>`;
                }
                message += `</div>`;

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Leave Application Details',
                        html: message,
                        icon: 'info',
                        confirmButtonText: 'Close',
                        width: '600px'
                    });
                } else {
                    alert(message);
                }
            },
            eventDidMount: function(info) {
                // Add tooltip
                const props = info.event.extendedProps;
                const tooltip = `${props.employeeName} - ${props.leaveTypeName} (${props.noOfDays} days)`;
                info.el.setAttribute('title', tooltip);
            },
            height: 'auto',
            editable: false,
            dayMaxEvents: true,
            moreLinkClick: 'popover'
        });

        interactiveCalendar.render();
        console.log('Interactive calendar initialized successfully');
    } catch (error) {
        console.error('Error initializing calendar:', error);
        calendarEl.innerHTML = '<div class="alert alert-danger">Error initializing calendar: ' + error.message + '</div>';
    }
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
    } else {
        // Fallback: redirect to apply leave page
        window.location.href = '<?= $base ?>html/?s=user&ss=leave&p=apply_leave_workflow&startDate=' + dateStr;
    }
}

// View toggle handlers
function switchCalendarView(view) {
    if (interactiveCalendar) {
        interactiveCalendar.changeView(view);

        // Update button states
        document.querySelectorAll('[data-view]').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`[data-view="${view}"]`).classList.add('active');
    }
}

// Make calendar globally accessible for refresh
if (typeof window !== 'undefined') {
    window.interactiveCalendar = null;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Wait for FullCalendar to load
    function waitForFullCalendar(callback, maxAttempts = 10) {
        let attempts = 0;
        const checkInterval = setInterval(function() {
            attempts++;
            if (typeof FullCalendar !== 'undefined') {
                clearInterval(checkInterval);
                callback();
            } else if (attempts >= maxAttempts) {
                clearInterval(checkInterval);
                console.error('FullCalendar failed to load');
            }
        }, 100);
    }

    // Load employees first
    loadFilterableEmployees();

    // Initialize calendar
    waitForFullCalendar(function() {
        initializeInteractiveCalendar();
        // Make calendar globally accessible
        if (typeof window !== 'undefined') {
            window.interactiveCalendar = interactiveCalendar;
        }
    });

    // Event listeners
    const employeeFilter = document.getElementById('employeeFilter');
    if (employeeFilter) {
        employeeFilter.addEventListener('change', updateEmployeeFilter);
    }

    document.querySelectorAll('.leaveTypeFilter').forEach(cb => {
        cb.addEventListener('change', updateLeaveTypeFilter);
    });
    document.querySelectorAll('.statusFilter').forEach(cb => {
        cb.addEventListener('change', updateStatusFilter);
    });

    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', clearAllFilters);
    }

    // View toggle buttons
    document.querySelectorAll('[data-view]').forEach(btn => {
        btn.addEventListener('click', function() {
            switchCalendarView(this.dataset.view);
        });
    });

    // Listen for leave application submission to refresh calendar
    window.addEventListener('leaveApplicationSubmitted', function() {
        if (interactiveCalendar) {
            interactiveCalendar.refetchEvents();
        }
    });

    // Initialize filters
    updateLeaveTypeFilter();
    updateStatusFilter();
});
</script>

<style>
#interactiveLeaveCalendar {
    font-family: inherit;
    min-height: 600px;
}

#interactiveLeaveCalendar .fc-event {
    cursor: pointer;
    border: none;
    padding: 2px 5px;
    margin: 1px 0;
}

#interactiveLeaveCalendar .fc-event:hover {
    opacity: 0.8;
    transform: scale(1.02);
    transition: all 0.2s ease;
}

#interactiveLeaveCalendar .fc-daygrid-day {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

#interactiveLeaveCalendar .fc-daygrid-day:hover {
    background-color: #f8f9fa !important;
}

#interactiveLeaveCalendar .fc-day-today {
    background-color: #e7f3ff !important;
}

.filter-checkboxes {
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 10px;
}

.filter-checkboxes .form-check {
    margin-bottom: 5px;
}

.legend-color {
    border: 1px solid #dee2e6;
}

/* Responsive */
@media (max-width: 768px) {
    #interactiveLeaveCalendar {
        min-height: 400px;
    }

    .filter-checkboxes {
        max-height: 100px !important;
    }
}
</style>

<!-- Include Leave Management Modals -->
<?php include 'modals/leave_modals_include.php'; ?>

