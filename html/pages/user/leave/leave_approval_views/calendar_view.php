<!-- Calendar View -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">
                        <i class="ri-calendar-2-line text-primary me-2"></i>
                        Team Leave Calendar
                    </h5>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="prevMonth">
                            <i class="ri-arrow-left-line"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="todayBtn">
                            Today
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="nextMonth">
                            <i class="ri-arrow-right-line"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div id="teamLeaveCalendar"></div>
            </div>
        </div>
    </div>
</div>

<!-- Calendar Legend -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-3">
                    <i class="ri-information-line me-2"></i>
                    Legend & Team Members
                </h6>
                <div class="row">
                    <div class="col-md-6">
                        <div class="d-flex flex-wrap gap-3">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-success me-2">●</span>
                                <small>Approved Leave</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-warning me-2">●</span>
                                <small>Pending Approval</small>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-danger me-2">●</span>
                                <small>High Absence (>30%)</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-0 text-muted small">
                            <strong>Team Size:</strong> <?= $totalTeamSize ?> members
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Day Detail Modal -->
<div class="modal fade" id="dayDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dayDetailTitle">
                    <i class="ri-calendar-event-line me-2"></i>
                    Leave Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="dayDetailContent">
                <!-- Populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('teamLeaveCalendar');

    // Prepare events data
    const events = [];

    // Add approved leaves
    const approvedLeaves = <?= json_encode($approvedApplications) ?>;
    approvedLeaves.forEach(leave => {
        events.push({
            id: leave.leaveApplicationID,
            title: leave.employeeName + ' - ' + leave.leaveTypeName,
            start: leave.startDate,
            end: new Date(new Date(leave.endDate).getTime() + 86400000).toISOString().split('T')[0], // Add 1 day for inclusive end
            backgroundColor: '#28a745',
            borderColor: '#28a745',
            classNames: ['approved-leave'],
            extendedProps: {
                employeeName: leave.employeeName,
                leaveTypeName: leave.leaveTypeName,
                status: 'Approved',
                statusColor: 'success',
                days: leave.noOfDays,
                applicationID: leave.leaveApplicationID,
                employeeID: leave.employeeID
            }
        });
    });

    // Add pending leaves
    const pendingLeaves = <?= json_encode($pendingApplications) ?>;
    pendingLeaves.forEach(leave => {
        events.push({
            id: leave.leaveApplicationID,
            title: leave.employeeName + ' - ' + leave.leaveTypeName + ' (Pending)',
            start: leave.startDate,
            end: new Date(new Date(leave.endDate).getTime() + 86400000).toISOString().split('T')[0],
            backgroundColor: '#ffc107',
            borderColor: '#ffc107',
            classNames: ['pending-leave'],
            extendedProps: {
                employeeName: leave.employeeName,
                leaveTypeName: leave.leaveTypeName,
                status: 'Pending Approval',
                statusColor: 'warning',
                days: leave.noOfDays,
                applicationID: leave.leaveApplicationID,
                employeeID: leave.employeeID,
                isPending: true
            }
        });
    });

    // Initialize FullCalendar
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: false,
        height: 'auto',
        events: events,
        eventClick: function(info) {
            showDayDetail(info.event);
        },
        dayCellDidMount: function(info) {
            // Calculate team members on leave for this day
            const dayEvents = calendar.getEvents().filter(event => {
                const eventStart = new Date(event.start);
                const eventEnd = new Date(event.end);
                const currentDay = new Date(info.date);

                return currentDay >= eventStart && currentDay < eventEnd;
            });

            const teamSize = <?= $totalTeamSize ?>;
            const onLeaveCount = dayEvents.length;
            const percent = teamSize > 0 ? (onLeaveCount / teamSize) * 100 : 0;

            // Add visual indicator for high absence
            if (percent > 30) {
                info.el.style.backgroundColor = percent > 50 ? '#ffe5e5' : '#fff3cd';

                // Add badge
                const badge = document.createElement('div');
                badge.className = 'absence-badge';
                badge.innerHTML = `<small class="badge bg-${percent > 50 ? 'danger' : 'warning'}">${onLeaveCount}/${teamSize}</small>`;
                badge.style.position = 'absolute';
                badge.style.top = '5px';
                badge.style.right = '5px';
                info.el.style.position = 'relative';
                info.el.appendChild(badge);
            }
        },
        eventContent: function(arg) {
            return {
                html: `
                    <div class="fc-event-main-frame" style="font-size: 0.75rem;">
                        <div class="fc-event-time">${arg.event.extendedProps.employeeName}</div>
                        <div class="fc-event-title-container">
                            <div class="fc-event-title fc-sticky">${arg.event.extendedProps.leaveTypeName}</div>
                        </div>
                    </div>
                `
            };
        }
    });

    calendar.render();

    // Navigation buttons
    document.getElementById('prevMonth')?.addEventListener('click', () => {
        calendar.prev();
    });

    document.getElementById('nextMonth')?.addEventListener('click', () => {
        calendar.next();
    });

    document.getElementById('todayBtn')?.addEventListener('click', () => {
        calendar.today();
    });

    // Show day detail modal
    function showDayDetail(event) {
        const modal = new bootstrap.Modal(document.getElementById('dayDetailModal'));
        const title = document.getElementById('dayDetailTitle');
        const content = document.getElementById('dayDetailContent');

        const props = event.extendedProps;

        title.innerHTML = `
            <i class="ri-calendar-event-line me-2"></i>
            ${event.title}
        `;

        content.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-2"><strong>Employee:</strong> ${props.employeeName}</p>
                    <p class="mb-2"><strong>Leave Type:</strong> ${props.leaveTypeName}</p>
                    <p class="mb-2"><strong>Duration:</strong> ${props.days || 'N/A'} day(s)</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2"><strong>Start Date:</strong> ${new Date(event.start).toLocaleDateString()}</p>
                    <p class="mb-2"><strong>End Date:</strong> ${new Date(event.end).toLocaleDateString()}</p>
                    <p class="mb-2">
                        <strong>Status:</strong>
                        <span class="badge bg-${props.statusColor}">${props.status}</span>
                    </p>
                </div>
            </div>

            ${props.isPending ? `
                <div class="mt-3">
                    <hr>
                    <div class="d-flex gap-2 justify-content-end">
                        <button class="btn btn-success btn-sm quick-approve-btn" type="button">
                            <i class="ri-checkbox-circle-line me-1"></i>Approve
                        </button>
                        <button class="btn btn-danger btn-sm quick-reject-btn" type="button">
                            <i class="ri-close-circle-line me-1"></i>Reject
                        </button>
                    </div>
                </div>
            ` : ''}
        `;

        content.querySelectorAll('.quick-approve-btn').forEach(button => {
            button.addEventListener('click', () => quickApprove(props.applicationID, props.employeeName));
        });

        content.querySelectorAll('.quick-reject-btn').forEach(button => {
            button.addEventListener('click', () => quickReject(props.applicationID, props.employeeName));
        });

        modal.show();
    }
});

// Quick approve/reject functions
function quickApprove(applicationID, employeeName) {
    if (confirm(`Approve leave request for ${employeeName}?`)) {
        // Submit approval
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= $base ?>php/scripts/leave/applications/process_leave_approval_action.php';

        const fields = {
            'leaveApplicationID': applicationID,
            'leaveStatus': 'approved',
            'leaveStatusID': '6',
            'leaveApproverID': '<?= $userDetails->ID ?>'
        };

        for (const key in fields) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = fields[key];
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
    }
}

function quickReject(applicationID, employeeName) {
    const reason = prompt(`Reject leave request for ${employeeName}?\n\nPlease provide a reason:`);
    if (reason) {
        // Submit rejection
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= $base ?>php/scripts/leave/applications/process_leave_approval_action.php';

        const fields = {
            'leaveApplicationID': applicationID,
            'leaveStatus': 'rejected',
            'leaveStatusID': '4',
            'leaveApproverID': '<?= $userDetails->ID ?>',
            'leaveComments': reason
        };

        for (const key in fields) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = fields[key];
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<style>
#teamLeaveCalendar {
    max-width: 100%;
}

.fc-event {
    cursor: pointer;
    border-radius: 4px;
    padding: 2px 4px;
}

.fc-daygrid-day {
    position: relative;
}

.absence-badge {
    z-index: 10;
}

.fc-event-main-frame {
    padding: 2px 4px;
}

.fc .fc-daygrid-day-number {
    padding: 4px;
}

.fc-event-time,
.fc-event-title {
    white-space: normal;
    overflow: visible;
}
</style>

