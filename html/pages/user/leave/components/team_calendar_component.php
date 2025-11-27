<?php
/**
 * Team Calendar Component
 *
 * Interactive calendar widget for team leave management
 * Supports both team overview and individual employee calendars
 */
?>

<!-- Team Calendar Container -->
<div id="teamCalendarContainer" class="team-calendar-container">
    <!-- Calendar Controls -->
    <div class="calendar-controls mb-3">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="prevMonthBtn">
                        <i class="ri-arrow-left-line"></i>
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="todayBtn">
                        Today
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="nextMonthBtn">
                        <i class="ri-arrow-right-line"></i>
                    </button>
                    <h6 class="mb-0 ms-3" id="currentMonthDisplay"><?= date('F Y') ?></h6>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-end align-items-center gap-2">
                    <!-- View Type Toggle -->
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary active" id="teamViewBtn" data-view="team">
                            <i class="ri-team-line me-1"></i>Team
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="individualViewBtn" data-view="individual">
                            <i class="ri-user-line me-1"></i>Individual
                        </button>
                    </div>

                    <!-- Employee Selector (for individual view) -->
                    <div id="employeeSelector" class="d-none">
                        <select class="form-select form-select-sm" id="employeeSelect" style="min-width: 200px;">
                            <option value="">Select Employee</option>
                        </select>
                    </div>

                    <!-- Calendar View Toggle -->
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary active" id="monthViewBtn" data-view="month">
                            <i class="ri-calendar-line"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="weekViewBtn" data-view="week">
                            <i class="ri-calendar-week-line"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="dayViewBtn" data-view="day">
                            <i class="ri-calendar-day-line"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Summary Cards -->
    <div class="calendar-summary mb-3" id="calendarSummary">
        <div class="row">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="metric-icon bg-primary bg-opacity-10 text-primary">
                                    <i class="ri-calendar-line fs-5"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="text-muted mb-1 small">On Leave Today</h6>
                                <h5 class="mb-0 fw-bold" id="onLeaveToday">0</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="metric-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="ri-time-line fs-5"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="text-muted mb-1 small">Pending Approvals</h6>
                                <h5 class="mb-0 fw-bold" id="pendingApprovals">0</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="metric-icon bg-info bg-opacity-10 text-info">
                                    <i class="ri-calendar-week-line fs-5"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="text-muted mb-1 small">This Week</h6>
                                <h5 class="mb-0 fw-bold" id="onLeaveThisWeek">0</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="metric-icon bg-success bg-opacity-10 text-success">
                                    <i class="ri-calendar-month-line fs-5"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-2">
                                <h6 class="text-muted mb-1 small">This Month</h6>
                                <h5 class="mb-0 fw-bold" id="onLeaveThisMonth">0</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Widget -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div id="teamLeaveCalendar" class="team-calendar-widget"></div>
        </div>
    </div>

    <!-- Legend -->
    <div class="calendar-legend mt-3">
        <div class="row">
            <div class="col-md-6">
                <h6 class="mb-2">Leave Types</h6>
                <div class="legend-items" id="leaveTypeLegend">
                    <!-- Dynamically populated -->
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="mb-2">Status</h6>
                <div class="legend-items" id="statusLegend">
                    <div class="legend-item d-flex align-items-center mb-1">
                        <div class="legend-color" style="background-color: #fd7e14; width: 16px; height: 16px; border-radius: 3px; margin-right: 8px;"></div>
                        <small>Pending</small>
                    </div>
                    <div class="legend-item d-flex align-items-center mb-1">
                        <div class="legend-color" style="background-color: #28a745; width: 16px; height: 16px; border-radius: 3px; margin-right: 8px;"></div>
                        <small>Approved</small>
                    </div>
                    <div class="legend-item d-flex align-items-center mb-1">
                        <div class="legend-color" style="background-color: #dc3545; width: 16px; height: 16px; border-radius: 3px; margin-right: 8px;"></div>
                        <small>Rejected</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventDetailsModal" tabindex="-1" aria-labelledby="eventDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventDetailsModalLabel">Leave Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="eventDetailsContent">
                <!-- Dynamically populated -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <div id="eventActionButtons">
                    <!-- Dynamically populated based on permissions -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Individual Employee Calendar Modal -->
<div class="modal fade" id="employeeCalendarModal" tabindex="-1" aria-labelledby="employeeCalendarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="employeeCalendarModalLabel">Employee Leave Calendar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="employeeCalendarWidget" class="employee-calendar-widget"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Team Calendar Styles */
.team-calendar-container {
    position: relative;
}

.calendar-controls {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
}

.metric-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.team-calendar-widget {
    min-height: 600px;
    padding: 1rem;
}

.employee-calendar-widget {
    min-height: 500px;
}

.calendar-summary .card {
    transition: all 0.2s ease;
}

.calendar-summary .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}

.legend-items {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.legend-item {
    font-size: 0.875rem;
}

/* FullCalendar Custom Styles */
.fc-event {
    border-radius: 4px !important;
    border: none !important;
    font-size: 0.75rem !important;
    padding: 2px 4px !important;
}

.fc-event-title {
    font-weight: 500 !important;
}

.fc-daygrid-event {
    margin: 1px 0 !important;
}

.fc-daygrid-day-number {
    font-weight: 500 !important;
}

.fc-col-header-cell {
    background-color: #f8f9fa !important;
    font-weight: 600 !important;
}

.fc-today {
    background-color: rgba(0, 123, 255, 0.1) !important;
}

.fc-button-primary {
    background-color: #007bff !important;
    border-color: #007bff !important;
}

.fc-button-primary:hover {
    background-color: #0056b3 !important;
    border-color: #0056b3 !important;
}

.fc-button-primary:focus {
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
}

.fc-button-primary:disabled {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
}

/* Responsive Design */
@media (max-width: 768px) {
    .calendar-controls .row {
        flex-direction: column;
        gap: 1rem;
    }

    .calendar-controls .col-md-6 {
        width: 100%;
    }

    .calendar-summary .row {
        flex-direction: column;
    }

    .calendar-summary .col-xl-3 {
        width: 100%;
        margin-bottom: 0.5rem;
    }

    .team-calendar-widget {
        min-height: 400px;
    }

    .legend-items {
        flex-direction: column;
        gap: 0.5rem;
    }
}

/* Loading States */
.calendar-loading {
    position: relative;
}

.calendar-loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.calendar-loading::before {
    content: 'Loading...';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1001;
    font-weight: 500;
    color: #007bff;
}

/* Event Hover Effects */
.fc-event:hover {
    opacity: 0.8 !important;
    cursor: pointer !important;
}

/* Custom Event Classes */
.event-approved {
    background-color: #28a745 !important;
}

.event-pending {
    background-color: #fd7e14 !important;
}

.event-rejected {
    background-color: #dc3545 !important;
}

.event-draft {
    background-color: #6c757d !important;
}
</style>

<script>
/**
 * Team Calendar JavaScript
 *
 * Handles interactive calendar functionality for team leave management
 */

class TeamCalendar {
    constructor() {
        this.calendar = null;
        this.currentView = 'team';
        this.currentEmployeeID = null;
        this.currentDate = new Date();
        this.teamMembers = [];
        this.calendarData = null;

        this.init();
    }

    init() {
        this.bindEvents();
        this.loadTeamMembers();
        this.initializeCalendar();
    }

    bindEvents() {
        // Navigation buttons
        document.getElementById('prevMonthBtn').addEventListener('click', () => this.navigateCalendar('prev'));
        document.getElementById('nextMonthBtn').addEventListener('click', () => this.navigateCalendar('next'));
        document.getElementById('todayBtn').addEventListener('click', () => this.goToToday());

        // View type toggles
        document.getElementById('teamViewBtn').addEventListener('click', () => this.switchView('team'));
        document.getElementById('individualViewBtn').addEventListener('click', () => this.switchView('individual'));

        // Calendar view toggles
        document.getElementById('monthViewBtn').addEventListener('click', () => this.switchCalendarView('month'));
        document.getElementById('weekViewBtn').addEventListener('click', () => this.switchCalendarView('week'));
        document.getElementById('dayViewBtn').addEventListener('click', () => this.switchCalendarView('day'));

        // Employee selector
        document.getElementById('employeeSelect').addEventListener('change', (e) => {
            this.currentEmployeeID = e.target.value;
            this.loadCalendarData();
        });
    }

    async loadTeamMembers() {
        try {
            const response = await fetch('<?= $base ?>php/scripts/leave/utilities/team_calendar_data.php?action=team_members');
            const data = await response.json();

            if (data.success) {
                this.teamMembers = data.data;
                this.populateEmployeeSelector();
            } else {
                console.error('Failed to load team members:', data.message);
            }
        } catch (error) {
            console.error('Error loading team members:', error);
        }
    }

    populateEmployeeSelector() {
        const selector = document.getElementById('employeeSelect');
        selector.innerHTML = '<option value="">Select Employee</option>';

        this.teamMembers.forEach(member => {
            const option = document.createElement('option');
            option.value = member.id;
            option.textContent = `${member.name} (${member.jobTitle})`;
            selector.appendChild(option);
        });
    }

    initializeCalendar() {
        const calendarEl = document.getElementById('teamLeaveCalendar');

        this.calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: false, // We're using custom controls
            height: 'auto',
            dayMaxEvents: 3,
            moreLinkClick: 'popover',
            eventClick: (info) => this.handleEventClick(info),
            eventDidMount: (info) => this.handleEventMount(info),
            datesSet: (info) => this.handleDatesSet(info),
            events: (info) => this.loadCalendarEvents(info.start, info.end),
            eventDisplay: 'block',
            dayMaxEventRows: 3,
            eventMaxStack: 3,
            eventOverlap: false,
            selectable: false,
            selectMirror: false,
            unselectAuto: false,
            editable: false,
            droppable: false,
            eventResizableFromStart: false,
            eventResizableFromEnd: false,
            locale: 'en'
        });

        this.calendar.render();
        this.loadCalendarData();
    }

    async loadCalendarData() {
        this.showLoading();

        try {
            const startDate = this.calendar.view.activeStart.toISOString().split('T')[0];
            const endDate = this.calendar.view.activeEnd.toISOString().split('T')[0];

            let url = `<?= $base ?>php/scripts/leave/utilities/team_calendar_data.php?action=calendar_events&start=${startDate}&end=${endDate}`;

            if (this.currentView === 'individual' && this.currentEmployeeID) {
                url += `&employee_id=${this.currentEmployeeID}`;
            }

            const response = await fetch(url);
            const data = await response.json();

            if (data.success) {
                this.calendarData = data.data;
                this.updateCalendarEvents();
                this.updateSummaryCards();
                this.updateLegend();
            } else {
                console.error('Failed to load calendar data:', data.message);
                this.showError('Failed to load calendar data');
            }
        } catch (error) {
            console.error('Error loading calendar data:', error);
            this.showError('Error loading calendar data');
        } finally {
            this.hideLoading();
        }
    }

    async loadCalendarEvents(start, end) {
        try {
            const startDate = start.toISOString().split('T')[0];
            const endDate = end.toISOString().split('T')[0];

            let url = `<?= $base ?>php/scripts/leave/utilities/team_calendar_data.php?action=calendar_events&start=${startDate}&end=${endDate}`;

            if (this.currentView === 'individual' && this.currentEmployeeID) {
                url += `&employee_id=${this.currentEmployeeID}`;
            }

            const response = await fetch(url);
            const data = await response.json();

            if (data.success) {
                return data.data;
            } else {
                console.error('Failed to load events:', data.message);
                return [];
            }
        } catch (error) {
            console.error('Error loading events:', error);
            return [];
        }
    }

    updateCalendarEvents() {
        if (this.calendarData) {
            this.calendar.removeAllEvents();
            this.calendar.addEventSource(this.calendarData);
        }
    }

    updateSummaryCards() {
        if (this.calendarData && this.calendarData.summary) {
            const summary = this.calendarData.summary;
            document.getElementById('onLeaveToday').textContent = summary.onLeaveToday || 0;
            document.getElementById('pendingApprovals').textContent = summary.pendingApprovals || 0;
            document.getElementById('onLeaveThisWeek').textContent = summary.onLeaveThisWeek || 0;
            document.getElementById('onLeaveThisMonth').textContent = summary.onLeaveThisMonth || 0;
        }
    }

    updateLegend() {
        if (this.calendarData && this.calendarData.summary && this.calendarData.summary.leaveTypes) {
            const legendContainer = document.getElementById('leaveTypeLegend');
            legendContainer.innerHTML = '';

            const colors = {
                'Annual': '#007bff',
                'Vacation': '#007bff',
                'Sick': '#28a745',
                'Medical': '#28a745',
                'Emergency': '#dc3545',
                'Maternity': '#6f42c1',
                'Paternity': '#6f42c1',
                'Study': '#17a2b8',
                'Personal': '#ffc107'
            };

            Object.entries(this.calendarData.summary.leaveTypes).forEach(([type, count]) => {
                const legendItem = document.createElement('div');
                legendItem.className = 'legend-item d-flex align-items-center mb-1';
                legendItem.innerHTML = `
                    <div class="legend-color" style="background-color: ${colors[type] || '#6c757d'}; width: 16px; height: 16px; border-radius: 3px; margin-right: 8px;"></div>
                    <small>${type} (${count})</small>
                `;
                legendContainer.appendChild(legendItem);
            });
        }
    }

    switchView(viewType) {
        this.currentView = viewType;

        // Update button states
        document.getElementById('teamViewBtn').classList.toggle('active', viewType === 'team');
        document.getElementById('individualViewBtn').classList.toggle('active', viewType === 'individual');

        // Show/hide employee selector
        const employeeSelector = document.getElementById('employeeSelector');
        employeeSelector.classList.toggle('d-none', viewType === 'team');

        // Reset employee selection when switching to team view
        if (viewType === 'team') {
            this.currentEmployeeID = null;
            document.getElementById('employeeSelect').value = '';
        }

        this.loadCalendarData();
    }

    switchCalendarView(viewType) {
        // Update button states
        document.querySelectorAll('[data-view]').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.view === viewType);
        });

        this.calendar.changeView(viewType);
    }

    navigateCalendar(direction) {
        if (direction === 'prev') {
            this.calendar.prev();
        } else if (direction === 'next') {
            this.calendar.next();
        }
    }

    goToToday() {
        this.calendar.today();
    }

    handleDatesSet(info) {
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                           'July', 'August', 'September', 'October', 'November', 'December'];
        const month = monthNames[info.start.getMonth()];
        const year = info.start.getFullYear();
        document.getElementById('currentMonthDisplay').textContent = `${month} ${year}`;
    }

    handleEventClick(info) {
        const event = info.event;
        this.showEventDetails(event);
    }

    handleEventMount(info) {
        const event = info.event;
        const statusID = event.extendedProps.statusID;

        // Add status-based CSS class
        if (statusID === 3) {
            info.el.classList.add('event-pending');
        } else if (statusID === 4) {
            info.el.classList.add('event-approved');
        } else if (statusID === 5) {
            info.el.classList.add('event-rejected');
        } else if (statusID === 1) {
            info.el.classList.add('event-draft');
        }
    }

    showEventDetails(event) {
        const props = event.extendedProps;
        const modal = new bootstrap.Modal(document.getElementById('eventDetailsModal'));

        document.getElementById('eventDetailsModalLabel').textContent =
            `${props.employeeName} - ${props.leaveType}`;

        document.getElementById('eventDetailsContent').innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong>Employee:</strong>
                        <p class="mb-0">${props.employeeName}</p>
                    </div>
                    <div class="mb-3">
                        <strong>Leave Type:</strong>
                        <p class="mb-0">${props.leaveType}</p>
                    </div>
                    <div class="mb-3">
                        <strong>Duration:</strong>
                        <p class="mb-0">${props.noOfDays} day(s)</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong>Start Date:</strong>
                        <p class="mb-0">${event.start.toLocaleDateString()}</p>
                    </div>
                    <div class="mb-3">
                        <strong>End Date:</strong>
                        <p class="mb-0">${event.end.toLocaleDateString()}</p>
                    </div>
                    <div class="mb-3">
                        <strong>Status:</strong>
                        <p class="mb-0">
                            <span class="badge bg-${this.getStatusColor(props.statusID)}">${props.status}</span>
                        </p>
                    </div>
                </div>
            </div>
            ${props.comments ? `
                <div class="mb-3">
                    <strong>Comments:</strong>
                    <p class="mb-0">${props.comments}</p>
                </div>
            ` : ''}
        `;

        // Add action buttons based on permissions
        const actionButtons = document.getElementById('eventActionButtons');
        actionButtons.innerHTML = '';

        if (props.statusID === 3) { // Pending
            actionButtons.innerHTML = `
                <button type="button" class="btn btn-success me-2" data-action="approve-leave" data-application-id="${event.id}">
                    <i class="ri-check-line me-1"></i>Approve
                </button>
                <button type="button" class="btn btn-danger" data-action="reject-leave" data-application-id="${event.id}">
                    <i class="ri-close-line me-1"></i>Reject
                </button>
            `;

            actionButtons.querySelectorAll('[data-action="approve-leave"]').forEach(button => {
                button.addEventListener('click', () => this.approveLeave(event.id));
            });

            actionButtons.querySelectorAll('[data-action="reject-leave"]').forEach(button => {
                button.addEventListener('click', () => this.rejectLeave(event.id));
            });
        }

        modal.show();
    }

    getStatusColor(statusID) {
        switch (statusID) {
            case 1: return 'secondary'; // Draft
            case 2: return 'warning';  // Submitted
            case 3: return 'warning';  // Pending
            case 4: return 'success';  // Approved
            case 5: return 'danger';   // Rejected
            default: return 'secondary';
        }
    }

    async approveLeave(applicationId) {
        if (confirm('Are you sure you want to approve this leave application?')) {
            await this.processApproval(applicationId, 'approve');
        }
    }

    async rejectLeave(applicationId) {
        const reason = prompt('Please provide a reason for rejection (optional):');
        if (reason !== null) {
            await this.processApproval(applicationId, 'reject', reason);
        }
    }

    async processApproval(applicationId, action, reason = '') {
        try {
            const response = await fetch('<?= $base ?>php/scripts/leave/applications/process_leave_approval_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    applicationId: applicationId,
                    action: action,
                    reason: reason
                })
            });

            const data = await response.json();

            if (data.success) {
                showToast('success', 'Action Completed',
                         `Leave application ${action === 'approve' ? 'approved' : 'rejected'} successfully.`);

                // Close modal and refresh calendar
                bootstrap.Modal.getInstance(document.getElementById('eventDetailsModal')).hide();
                this.loadCalendarData();
            } else {
                showToast('error', 'Action Failed', data.message || 'Failed to process approval.');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('error', 'Network Error', 'Unable to process approval. Please try again.');
        }
    }

    showLoading() {
        document.getElementById('teamLeaveCalendar').classList.add('calendar-loading');
    }

    hideLoading() {
        document.getElementById('teamLeaveCalendar').classList.remove('calendar-loading');
    }

    showError(message) {
        showToast('error', 'Error', message);
    }
}

// Initialize calendar when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.teamCalendar = new TeamCalendar();
});

// Export for global access
window.TeamCalendar = TeamCalendar;
</script>
