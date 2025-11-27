<?php
/**
 * Leave Calendar Modal
 *
 * Interactive calendar view for leave management with
 * team leave visibility, holiday integration, and leave planning
 */

// Get team members and their leave data
$teamMembers = Employee::get_team_members($employeeDetails->ID, $DBConn);
$teamLeaveApplications = array();
if ($teamMembers) {
    foreach ($teamMembers as $member) {
        $memberLeave = Leave::leave_applications_full(array('Suspended'=>'N', 'employeeID'=>$member->ID), false, $DBConn);
        if ($memberLeave) {
            $teamLeaveApplications = array_merge($teamLeaveApplications, $memberLeave);
        }
    }
}

// Get holidays for the current year
$currentYear = date('Y');
$holidays = Leave::get_global_holidays('Kenya', null, $DBConn); // Using global holidays for Kenya
?>

<!-- Leave Calendar Modal -->
<div class="modal fade" id="leaveCalendarModal" tabindex="-1" aria-labelledby="leaveCalendarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="leaveCalendarModalLabel">
                    <i class="ri-calendar-line me-2"></i>
                    Leave Calendar
                </h5>
                <div class="d-flex align-items-center gap-3">
                    <!-- Calendar Controls -->
                    <div class="calendar-controls d-flex align-items-center gap-2">
                        <button class="btn btn-outline-light btn-sm" type="button" data-action="calendar-prev-month">
                            <i class="ri-arrow-left-line"></i>
                        </button>
                        <span class="fw-bold" id="currentMonthYear"><?= date('F Y') ?></span>
                        <button class="btn btn-outline-light btn-sm" type="button" data-action="calendar-next-month">
                            <i class="ri-arrow-right-line"></i>
                        </button>
                        <button class="btn btn-outline-light btn-sm" type="button" data-action="calendar-go-today">
                            Today
                        </button>
                    </div>

                    <!-- View Toggle -->
                    <div class="btn-group" role="group">
                        <input type="radio" class="btn-check" name="calendarView" id="monthView" autocomplete="off" checked>
                        <label class="btn btn-outline-light btn-sm" for="monthView">Month</label>

                        <input type="radio" class="btn-check" name="calendarView" id="weekView" autocomplete="off">
                        <label class="btn btn-outline-light btn-sm" for="weekView">Week</label>

                        <input type="radio" class="btn-check" name="calendarView" id="dayView" autocomplete="off">
                        <label class="btn btn-outline-light btn-sm" for="dayView">Day</label>
                    </div>

                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-0">
                <!-- Calendar Filters -->
                <div class="calendar-filters bg-light p-3 border-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="showMyLeave" checked>
                                    <label class="form-check-label" for="showMyLeave">
                                        My Leave
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="showTeamLeave" checked>
                                    <label class="form-check-label" for="showTeamLeave">
                                        Team Leave
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="showHolidays" checked>
                                    <label class="form-check-label" for="showHolidays">
                                        Holidays
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center gap-2">
                                <label class="form-label mb-0">Team Member:</label>
                                <select class="form-select form-select-sm" id="teamMemberFilter" style="width: auto;">
                                    <option value="all">All Team Members</option>
                                    <?php if ($teamMembers): ?>
                                        <?php foreach ($teamMembers as $member): ?>
                                            <option value="<?= $member->ID ?>"><?= $member->FirstName ?> <?= $member->Surname ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendar Container -->
                <div class="calendar-container p-3">
                    <!-- Month View -->
                    <div id="monthCalendarView" class="calendar-view">
                        <div class="calendar-grid">
                            <!-- Calendar Header -->
                            <div class="calendar-header">
                                <div class="calendar-day-header">Sun</div>
                                <div class="calendar-day-header">Mon</div>
                                <div class="calendar-day-header">Tue</div>
                                <div class="calendar-day-header">Wed</div>
                                <div class="calendar-day-header">Thu</div>
                                <div class="calendar-day-header">Fri</div>
                                <div class="calendar-day-header">Sat</div>
                            </div>

                            <!-- Calendar Days -->
                            <div class="calendar-days" id="calendarDays">
                                <!-- Days will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>

                    <!-- Week View -->
                    <div id="weekCalendarView" class="calendar-view" style="display: none;">
                        <div class="week-calendar">
                            <div class="week-header">
                                <div class="week-time-column">Time</div>
                                <div class="week-day-column">Sunday</div>
                                <div class="week-day-column">Monday</div>
                                <div class="week-day-column">Tuesday</div>
                                <div class="week-day-column">Wednesday</div>
                                <div class="week-day-column">Thursday</div>
                                <div class="week-day-column">Friday</div>
                                <div class="week-day-column">Saturday</div>
                            </div>
                            <div class="week-body" id="weekCalendarBody">
                                <!-- Week view will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>

                    <!-- Day View -->
                    <div id="dayCalendarView" class="calendar-view" style="display: none;">
                        <div class="day-calendar">
                            <div class="day-header">
                                <h6 id="selectedDayTitle">Today</h6>
                            </div>
                            <div class="day-body" id="dayCalendarBody">
                                <!-- Day view will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <div class="d-flex justify-content-between w-100">
                    <div class="calendar-legend">
                        <div class="d-flex align-items-center gap-3">
                            <div class="legend-item">
                                <span class="legend-color my-leave"></span>
                                <span class="legend-label">My Leave</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-color team-leave"></span>
                                <span class="legend-label">Team Leave</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-color holiday"></span>
                                <span class="legend-label">Holiday</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-color weekend"></span>
                                <span class="legend-label">Weekend</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary" data-action="open-apply-leave-modal">
                            <i class="ri-calendar-add-line me-1"></i>Apply Leave
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="ri-close-line me-1"></i>Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leave Details Modal -->
<div class="modal fade" id="leaveDetailsModal" tabindex="-1" aria-labelledby="leaveDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="leaveDetailsModalLabel">
                    <i class="ri-calendar-event-line me-2"></i>
                    Leave Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="leaveDetailsContent">
                <!-- Leave details will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="editLeaveBtn" style="display: none;">
                    <i class="ri-edit-line me-1"></i>Edit
                </button>
                <button type="button" class="btn btn-danger" id="cancelLeaveBtn" style="display: none;">
                    <i class="ri-close-circle-line me-1"></i>Cancel Leave
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background: #dee2e6;
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    overflow: hidden;
}

.calendar-header {
    display: contents;
}

.calendar-day-header {
    background: #f8f9fa;
    padding: 0.75rem;
    text-align: center;
    font-weight: 600;
    font-size: 0.875rem;
    color: #495057;
}

.calendar-days {
    display: contents;
}

.calendar-day {
    background: white;
    min-height: 100px;
    padding: 0.5rem;
    position: relative;
    cursor: pointer;
    transition: background-color 0.2s;
}

.calendar-day:hover {
    background: #f8f9fa;
}

.calendar-day.other-month {
    background: #f8f9fa;
    color: #6c757d;
}

.calendar-day.today {
    background: rgba(var(--bs-primary-rgb), 0.1);
    border: 2px solid var(--bs-primary);
}

.calendar-day.weekend {
    background: #f8f9fa;
}

.calendar-day-number {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.calendar-events {
    position: absolute;
    top: 1.5rem;
    left: 0.25rem;
    right: 0.25rem;
    bottom: 0.25rem;
    overflow: hidden;
}

.calendar-event {
    font-size: 0.75rem;
    padding: 0.125rem 0.25rem;
    margin-bottom: 0.125rem;
    border-radius: 0.25rem;
    cursor: pointer;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.calendar-event.my-leave {
    background: #d1ecf1;
    color: #0c5460;
    border-left: 3px solid #17a2b8;
}

.calendar-event.team-leave {
    background: #d4edda;
    color: #155724;
    border-left: 3px solid #28a745;
}

.calendar-event.holiday {
    background: #f8d7da;
    color: #721c24;
    border-left: 3px solid #dc3545;
}

.calendar-event.weekend {
    background: #e2e3e5;
    color: #383d41;
    border-left: 3px solid #6c757d;
}

.week-calendar {
    display: grid;
    grid-template-columns: 80px repeat(7, 1fr);
    gap: 1px;
    background: #dee2e6;
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    overflow: hidden;
}

.week-header {
    display: contents;
}

.week-time-column,
.week-day-column {
    background: #f8f9fa;
    padding: 0.75rem;
    text-align: center;
    font-weight: 600;
    font-size: 0.875rem;
    color: #495057;
}

.week-body {
    display: contents;
}

.week-time-slot {
    background: #f8f9fa;
    padding: 0.5rem;
    text-align: center;
    font-size: 0.75rem;
    color: #6c757d;
    border-top: 1px solid #dee2e6;
}

.week-day-slot {
    background: white;
    min-height: 40px;
    padding: 0.25rem;
    position: relative;
    border-top: 1px solid #dee2e6;
}

.day-calendar {
    background: white;
    border-radius: 0.5rem;
    border: 1px solid #dee2e6;
}

.day-header {
    background: #f8f9fa;
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
    border-radius: 0.5rem 0.5rem 0 0;
}

.day-body {
    padding: 1rem;
}

.day-event {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1rem;
    cursor: pointer;
    transition: all 0.2s;
}

.day-event:hover {
    background: #e9ecef;
    transform: translateY(-1px);
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.legend-color {
    width: 16px;
    height: 16px;
    border-radius: 0.25rem;
    border-left: 3px solid;
}

.legend-color.my-leave {
    background: #d1ecf1;
    border-left-color: #17a2b8;
}

.legend-color.team-leave {
    background: #d4edda;
    border-left-color: #28a745;
}

.legend-color.holiday {
    background: #f8d7da;
    border-left-color: #dc3545;
}

.legend-color.weekend {
    background: #e2e3e5;
    border-left-color: #6c757d;
}

.calendar-view {
    min-height: 500px;
}

@media (max-width: 768px) {
    .calendar-day {
        min-height: 80px;
    }

    .calendar-event {
        font-size: 0.625rem;
    }

    .week-calendar {
        grid-template-columns: 60px repeat(7, 1fr);
    }
}
</style>

<script>
// Leave Calendar Modal JavaScript
let currentDate = new Date();
let currentView = 'month';
let leaveData = <?= json_encode($teamLeaveApplications) ?>;
let holidays = <?= json_encode($holidays) ?>;
let myLeaveApplications = <?= json_encode($myLeaveApplications) ?>;

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-action="calendar-prev-month"]').forEach(button => {
        button.addEventListener('click', previousMonth);
    });

    document.querySelectorAll('[data-action="calendar-next-month"]').forEach(button => {
        button.addEventListener('click', nextMonth);
    });

    document.querySelectorAll('[data-action="calendar-go-today"]').forEach(button => {
        button.addEventListener('click', goToToday);
    });

    document.querySelectorAll('[data-action="open-apply-leave-modal"]').forEach(button => {
        button.addEventListener('click', openApplyLeaveModal);
    });
});

function initializeLeaveCalendar() {
    // Set up event listeners
    document.getElementById('monthView').addEventListener('change', () => switchView('month'));
    document.getElementById('weekView').addEventListener('change', () => switchView('week'));
    document.getElementById('dayView').addEventListener('change', () => switchView('day'));

    // Filter event listeners
    document.getElementById('showMyLeave').addEventListener('change', renderCalendar);
    document.getElementById('showTeamLeave').addEventListener('change', renderCalendar);
    document.getElementById('showHolidays').addEventListener('change', renderCalendar);
    document.getElementById('teamMemberFilter').addEventListener('change', renderCalendar);

    // Initial render
    renderCalendar();
}

function switchView(view) {
    currentView = view;

    // Hide all views
    document.querySelectorAll('.calendar-view').forEach(v => v.style.display = 'none');

    // Show selected view
    document.getElementById(`${view}CalendarView`).style.display = 'block';

    renderCalendar();
}

function renderCalendar() {
    switch (currentView) {
        case 'month':
            renderMonthView();
            break;
        case 'week':
            renderWeekView();
            break;
        case 'day':
            renderDayView();
            break;
    }
}

function renderMonthView() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    // Update month/year display
    document.getElementById('currentMonthYear').textContent =
        new Date(year, month).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });

    // Get first day of month and number of days
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startDate = new Date(firstDay);
    startDate.setDate(startDate.getDate() - firstDay.getDay());

    const calendarDays = document.getElementById('calendarDays');
    calendarDays.innerHTML = '';

    // Generate calendar days
    for (let i = 0; i < 42; i++) {
        const date = new Date(startDate);
        date.setDate(startDate.getDate() + i);

        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day';

        if (date.getMonth() !== month) {
            dayElement.classList.add('other-month');
        }

        if (date.getDay() === 0 || date.getDay() === 6) {
            dayElement.classList.add('weekend');
        }

        if (isToday(date)) {
            dayElement.classList.add('today');
        }

        dayElement.innerHTML = `
            <div class="calendar-day-number">${date.getDate()}</div>
            <div class="calendar-events" id="events-${formatDate(date)}"></div>
        `;

        dayElement.addEventListener('click', () => selectDate(date));
        calendarDays.appendChild(dayElement);

        // Add events to this day
        addEventsToDay(date);
    }
}

function renderWeekView() {
    const startOfWeek = getStartOfWeek(currentDate);
    const weekBody = document.getElementById('weekCalendarBody');
    weekBody.innerHTML = '';

    // Generate time slots (8 AM to 6 PM)
    for (let hour = 8; hour <= 18; hour++) {
        const timeSlot = document.createElement('div');
        timeSlot.className = 'week-time-slot';
        timeSlot.textContent = formatTime(hour);
        weekBody.appendChild(timeSlot);

        // Generate day slots for this hour
        for (let day = 0; day < 7; day++) {
            const daySlot = document.createElement('div');
            daySlot.className = 'week-day-slot';
            daySlot.id = `week-${formatDate(new Date(startOfWeek.getTime() + day * 24 * 60 * 60 * 1000))}-${hour}`;
            weekBody.appendChild(daySlot);
        }
    }

    // Add events to week view
    addEventsToWeekView(startOfWeek);
}

function renderDayView() {
    const dayTitle = document.getElementById('selectedDayTitle');
    dayTitle.textContent = currentDate.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });

    const dayBody = document.getElementById('dayCalendarBody');
    dayBody.innerHTML = '';

    // Get events for this day
    const dayEvents = getEventsForDate(currentDate);

    if (dayEvents.length === 0) {
        dayBody.innerHTML = '<div class="text-center text-muted py-4">No events scheduled for this day</div>';
    } else {
        dayEvents.forEach(event => {
            const eventElement = document.createElement('div');
            eventElement.className = 'day-event';
            eventElement.innerHTML = `
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1">${event.title}</h6>
                        <p class="mb-1 text-muted small">${event.description}</p>
                        <small class="text-muted">${event.time}</small>
                    </div>
                    <span class="badge bg-${event.type === 'my-leave' ? 'primary' : event.type === 'team-leave' ? 'success' : 'danger'}">${event.status}</span>
                </div>
            `;
            eventElement.addEventListener('click', () => showLeaveDetails(event.id));
            dayBody.appendChild(eventElement);
        });
    }
}

function addEventsToDay(date) {
    const eventsContainer = document.getElementById(`events-${formatDate(date)}`);
    if (!eventsContainer) return;

    const events = getEventsForDate(date);
    const showMyLeave = document.getElementById('showMyLeave').checked;
    const showTeamLeave = document.getElementById('showTeamLeave').checked;
    const showHolidays = document.getElementById('showHolidays').checked;

    events.forEach(event => {
        if ((event.type === 'my-leave' && !showMyLeave) ||
            (event.type === 'team-leave' && !showTeamLeave) ||
            (event.type === 'holiday' && !showHolidays)) {
            return;
        }

        const eventElement = document.createElement('div');
        eventElement.className = `calendar-event ${event.type}`;
        eventElement.textContent = event.title;
        eventElement.title = event.description;
        eventElement.addEventListener('click', (e) => {
            e.stopPropagation();
            showLeaveDetails(event.id);
        });

        eventsContainer.appendChild(eventElement);
    });
}

function addEventsToWeekView(startOfWeek) {
    for (let day = 0; day < 7; day++) {
        const date = new Date(startOfWeek.getTime() + day * 24 * 60 * 60 * 1000);
        const events = getEventsForDate(date);

        events.forEach(event => {
            const hour = event.startHour || 9; // Default to 9 AM if no specific time
            const slot = document.getElementById(`week-${formatDate(date)}-${hour}`);
            if (slot) {
                const eventElement = document.createElement('div');
                eventElement.className = `calendar-event ${event.type}`;
                eventElement.textContent = event.title;
                eventElement.style.position = 'absolute';
                eventElement.style.top = '0';
                eventElement.style.left = '0';
                eventElement.style.right = '0';
                eventElement.addEventListener('click', (e) => {
                    e.stopPropagation();
                    showLeaveDetails(event.id);
                });
                slot.appendChild(eventElement);
            }
        });
    }
}

function getEventsForDate(date) {
    const events = [];
    const dateStr = formatDate(date);

    // Add my leave applications
    if (myLeaveApplications) {
        myLeaveApplications.forEach(leave => {
            if (isDateInRange(date, leave.startDate, leave.endDate)) {
                events.push({
                    id: leave.leaveApplicationID,
                    title: leave.leaveTypeName,
                    description: leave.leaveReason,
                    type: 'my-leave',
                    status: leave.leaveStatusName,
                    startHour: 9
                });
            }
        });
    }

    // Add team leave applications
    if (leaveData) {
        leaveData.forEach(leave => {
            if (isDateInRange(date, leave.startDate, leave.endDate)) {
                events.push({
                    id: leave.leaveApplicationID,
                    title: `${leave.employeeName} - ${leave.leaveTypeName}`,
                    description: leave.leaveReason,
                    type: 'team-leave',
                    status: leave.leaveStatusName,
                    startHour: 9
                });
            }
        });
    }

    // Add holidays
    if (holidays) {
        holidays.forEach(holiday => {
            if (formatDate(date) === formatDate(new Date(holiday.holidayDate))) {
                events.push({
                    id: `holiday-${holiday.holidayID}`,
                    title: holiday.holidayName,
                    description: holiday.holidayDescription,
                    type: 'holiday',
                    status: 'Holiday',
                    startHour: 9
                });
            }
        });
    }

    return events;
}

function isDateInRange(date, startDate, endDate) {
    const checkDate = new Date(date);
    const start = new Date(startDate);
    const end = new Date(endDate);

    return checkDate >= start && checkDate <= end;
}

function isToday(date) {
    const today = new Date();
    return date.toDateString() === today.toDateString();
}

function formatDate(date) {
    return date.toISOString().split('T')[0];
}

function formatTime(hour) {
    return `${hour}:00`;
}

function getStartOfWeek(date) {
    const start = new Date(date);
    start.setDate(start.getDate() - start.getDay());
    return start;
}

function previousMonth() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendar();
}

function nextMonth() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendar();
}

function goToToday() {
    currentDate = new Date();
    renderCalendar();
}

function selectDate(date) {
    currentDate = new Date(date);
    if (currentView === 'day') {
        renderDayView();
    } else {
        switchView('day');
    }
}

function showLeaveDetails(leaveId) {
    // Load leave details and show modal
    fetch('<?= $base ?>php/scripts/leave/applications/get_leave_details.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ leaveId: leaveId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const content = document.getElementById('leaveDetailsContent');
            content.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Leave Information</h6>
                        <p><strong>Type:</strong> ${data.leave.leaveTypeName}</p>
                        <p><strong>Employee:</strong> ${data.leave.employeeName}</p>
                        <p><strong>Start Date:</strong> ${data.leave.startDate}</p>
                        <p><strong>End Date:</strong> ${data.leave.endDate}</p>
                        <p><strong>Days:</strong> ${data.leave.noOfDays}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Status & Details</h6>
                        <p><strong>Status:</strong> <span class="badge bg-${getStatusColor(data.leave.leaveStatusName)}">${data.leave.leaveStatusName}</span></p>
                        <p><strong>Reason:</strong> ${data.leave.leaveReason}</p>
                        <p><strong>Applied:</strong> ${data.leave.dateApplied}</p>
                        <p><strong>Emergency Contact:</strong> ${data.leave.emergencyContact || 'Not provided'}</p>
                    </div>
                </div>
                ${data.leave.handoverNotes ? `<div class="mt-3"><h6>Handover Notes</h6><p>${data.leave.handoverNotes}</p></div>` : ''}
            `;

            // Show/hide action buttons based on permissions
            const editBtn = document.getElementById('editLeaveBtn');
            const cancelBtn = document.getElementById('cancelLeaveBtn');

            if (data.canEdit) {
                editBtn.style.display = 'inline-block';
                editBtn.onclick = () => editLeave(leaveId);
            }

            if (data.canCancel) {
                cancelBtn.style.display = 'inline-block';
                cancelBtn.onclick = () => cancelLeave(leaveId);
            }

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('leaveDetailsModal'));
            modal.show();
        }
    })
    .catch(error => {
        console.error('Error loading leave details:', error);
        showToast('error', 'Error', 'Failed to load leave details');
    });
}

function getStatusColor(status) {
    switch (status.toLowerCase()) {
        case 'approved': return 'success';
        case 'pending': return 'warning';
        case 'rejected': return 'danger';
        case 'cancelled': return 'secondary';
        default: return 'primary';
    }
}

function editLeave(leaveId) {
    // Open edit leave modal or redirect to edit page
    window.location.href = `?s=user&ss=leave&p=leave_management_enhanced&view=edit&id=${leaveId}`;
}

function cancelLeave(leaveId) {
    if (confirm('Are you sure you want to cancel this leave application?')) {
        fetch('<?= $base ?>php/scripts/leave/applications/cancel_leave_application.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ leaveId: leaveId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', 'Leave Cancelled', 'Your leave application has been cancelled');
                renderCalendar();
                const modal = bootstrap.Modal.getInstance(document.getElementById('leaveDetailsModal'));
                modal.hide();
            } else {
                showToast('error', 'Error', data.message || 'Failed to cancel leave');
            }
        })
        .catch(error => {
            console.error('Error cancelling leave:', error);
            showToast('error', 'Error', 'Failed to cancel leave');
        });
    }
}

function openApplyLeaveModal() {
    const calendarModal = bootstrap.Modal.getInstance(document.getElementById('leaveCalendarModal'));
    calendarModal.hide();

    setTimeout(() => {
        const applyModal = new bootstrap.Modal(document.getElementById('applyLeaveModal'));
        applyModal.show();
    }, 300);
}

// Initialize when modal is shown
document.getElementById('leaveCalendarModal').addEventListener('shown.bs.modal', function() {
    initializeLeaveCalendar();
});
</script>
