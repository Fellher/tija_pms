<?php
/**
 * Activity Calendar View
 * 
 * Provides a visual calendar with drag-and-drop functionality
 * Features:
 * - Monthly, weekly, and daily views
 * - Drag and drop activities between dates
 * - Activity indicators and colors
 * - Quick add functionality
 * - Date navigation
 * - Activity filtering
 */

if (!isset($activities) || empty($activities)) {
    echo '<div class="text-center py-5">
            <div class="empty-state-icon mb-4">
                <i class="ri-calendar-line fs-1 text-muted"></i>
            </div>
            <h4 class="text-muted mb-3">No Activities Found</h4>
            <p class="text-muted mb-4">Get started by creating your first activity.</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manage_activity">
                <i class="ri-add-line me-2"></i>Create Activity
            </button>
          </div>';
    return;
}

// Group activities by date
$activitiesByDate = [];
foreach ($activities as $activity) {
    $dateKey = date('Y-m-d', strtotime($activity->activityDate));
    if (!isset($activitiesByDate[$dateKey])) {
        $activitiesByDate[$dateKey] = [];
    }
    $activitiesByDate[$dateKey][] = $activity;
}

// Get current date information
$currentDate = new DateTime($DOF);
$year = $currentDate->format('Y');
$month = $currentDate->format('m');
$monthName = $currentDate->format('F');

// Get calendar information
$firstDayOfMonth = new DateTime("$year-$month-01");
$lastDayOfMonth = clone $firstDayOfMonth;
$lastDayOfMonth->modify('last day of this month');
$firstDayWeekday = $firstDayOfMonth->format('N'); // 1 = Monday, 7 = Sunday
$daysInMonth = $lastDayOfMonth->format('d');

// Navigation URLs
$prevMonth = clone $firstDayOfMonth;
$prevMonth->modify('-1 month');
$nextMonth = clone $firstDayOfMonth;
$nextMonth->modify('+1 month');

$prevUrl = $base . "html/?s=user&ss=schedule&p=activities_enhanced&view=calendar&year=" . $prevMonth->format('Y') . "&month=" . $prevMonth->format('m');
$nextUrl = $base . "html/?s=user&ss=schedule&p=activities_enhanced&view=calendar&year=" . $nextMonth->format('Y') . "&month=" . $nextMonth->format('m');
$todayUrl = $base . "html/?s=user&ss=schedule&p=activities_enhanced&view=calendar&d=" . date('Y-m-d');
?>

<!-- Calendar View Container -->
<div class="calendar-container">
    <!-- Calendar Header -->
    <div class="calendar-header">
        <div class="d-flex justify-content-between align-items-center">
            <!-- Date Navigation -->
            <div class="d-flex align-items-center">
                <a href="<?= $prevUrl ?>" class="btn btn-outline-light btn-sm me-2">
                    <i class="ri-arrow-left-line"></i>
                </a>
                <h4 class="mb-0 me-3"><?= $monthName ?> <?= $year ?></h4>
                <a href="<?= $nextUrl ?>" class="btn btn-outline-light btn-sm">
                    <i class="ri-arrow-right-line"></i>
                </a>
            </div>
            
            <!-- View Controls -->
            <div class="d-flex align-items-center gap-2">
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-light active" data-view="month">
                        Month
                    </button>
                    <button type="button" class="btn btn-outline-light" data-view="week">
                        Week
                    </button>
                    <button type="button" class="btn btn-outline-light" data-view="day">
                        Day
                    </button>
                </div>
                
                <a href="<?= $todayUrl ?>" class="btn btn-outline-light btn-sm">
                    <i class="ri-calendar-check-line me-1"></i>Today
                </a>
                
                <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#manage_activity">
                    <i class="ri-add-line me-1"></i>Add Activity
                </button>
            </div>
        </div>
    </div>
    
    <!-- Calendar Body -->
    <div class="calendar-body">
        <!-- Weekday Headers -->
        <div class="calendar-weekdays">
            <div class="weekday-header">Mon</div>
            <div class="weekday-header">Tue</div>
            <div class="weekday-header">Wed</div>
            <div class="weekday-header">Thu</div>
            <div class="weekday-header">Fri</div>
            <div class="weekday-header">Sat</div>
            <div class="weekday-header">Sun</div>
        </div>
        
        <!-- Calendar Grid -->
        <div class="calendar-grid">
            <?php
            // Add empty cells for days before the first day of the month
            for ($i = 1; $i < $firstDayWeekday; $i++) {
                echo '<div class="calendar-day empty-day"></div>';
            }
            
            // Add days of the month
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dateKey = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $isToday = $dateKey === date('Y-m-d');
                $hasActivities = isset($activitiesByDate[$dateKey]);
                $dayActivities = $hasActivities ? $activitiesByDate[$dateKey] : [];
                
                echo '<div class="calendar-day ' . ($isToday ? 'today' : '') . ($hasActivities ? ' has-activities' : '') . '" 
                           data-date="' . $dateKey . '">';
                
                // Day number
                echo '<div class="day-number">' . $day . '</div>';
                
                // Activity indicators
                if ($hasActivities) {
                    echo '<div class="activity-indicators">';
                    foreach ($dayActivities as $activity) {
                        $statusClass = '';
                        $statusColor = '';
                        
                        switch($activity->activityStatusID) {
                            case 1:
                                $statusClass = 'status-todo';
                                $statusColor = '#6c757d';
                                break;
                            case 2:
                                $statusClass = 'status-in-progress';
                                $statusColor = '#007bff';
                                break;
                            case 3:
                                $statusClass = 'status-review';
                                $statusColor = '#ffc107';
                                break;
                            case 4:
                                $statusClass = 'status-completed';
                                $statusColor = '#28a745';
                                break;
                            case 5:
                                $statusClass = 'status-cancelled';
                                $statusColor = '#dc3545';
                                break;
                        }
                        
                        echo '<div class="activity-indicator ' . $statusClass . '" 
                                     style="background-color: ' . $statusColor . '"
                                     data-activity-id="' . $activity->activityID . '"
                                     title="' . htmlspecialchars($activity->activityName) . '"
                                     draggable="true">
                               </div>';
                    }
                    echo '</div>';
                }
                
                // Quick add button
                echo '<div class="quick-add" style="display: none;">
                        <button class="btn btn-primary btn-sm" 
                                onclick="quickAddActivity(\'' . $dateKey . '\')"
                                title="Add activity to this date">
                            <i class="ri-add-line"></i>
                        </button>
                      </div>';
                
                echo '</div>';
            }
            ?>
        </div>
    </div>
</div>

<!-- Activity Details Panel -->
<div class="activity-details-panel" id="activityDetailsPanel" style="display: none;">
    <div class="panel-header">
        <h5 class="mb-0">Activity Details</h5>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="closeActivityDetails()">
            <i class="ri-close-line"></i>
        </button>
    </div>
    <div class="panel-body" id="activityDetailsContent">
        <!-- Activity details will be loaded here -->
    </div>
</div>

<!-- Calendar View JavaScript -->
<script>
/**
 * Calendar View Specific Functionality
 */

// Initialize calendar functionality
function initializeCalendarView() {
    setupCalendarDragDrop();
    setupCalendarInteractions();
    setupCalendarViewToggle();
}

// Setup drag and drop for calendar
function setupCalendarDragDrop() {
    const activityIndicators = document.querySelectorAll('.activity-indicator');
    const calendarDays = document.querySelectorAll('.calendar-day:not(.empty-day)');
    
    activityIndicators.forEach(indicator => {
        indicator.addEventListener('dragstart', handleActivityDragStart);
        indicator.addEventListener('dragend', handleActivityDragEnd);
    });
    
    calendarDays.forEach(day => {
        day.addEventListener('dragover', handleDayDragOver);
        day.addEventListener('drop', handleDayDrop);
        day.addEventListener('dragenter', handleDayDragEnter);
        day.addEventListener('dragleave', handleDayDragLeave);
    });
}

// Handle activity drag start
function handleActivityDragStart(e) {
    this.classList.add('dragging');
    e.dataTransfer.setData('text/plain', this.dataset.activityId);
    e.dataTransfer.effectAllowed = 'move';
}

// Handle activity drag end
function handleActivityDragEnd(e) {
    this.classList.remove('dragging');
}

// Handle day drag over
function handleDayDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
}

// Handle day drag enter
function handleDayDragEnter(e) {
    e.preventDefault();
    this.classList.add('drag-over');
}

// Handle day drag leave
function handleDayDragLeave(e) {
    this.classList.remove('drag-over');
}

// Handle day drop
function handleDayDrop(e) {
    e.preventDefault();
    this.classList.remove('drag-over');
    
    const activityId = e.dataTransfer.getData('text/plain');
    const newDate = this.dataset.date;
    
    // Update activity date
    updateActivityDate(activityId, newDate);
}

// Update activity date via AJAX
function updateActivityDate(activityId, newDate) {
    showToast('info', 'Updating...', 'Updating activity date...');
    
    fetch('<?= $base ?>php/scripts/schedule/update_activity_date.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            activityId: activityId,
            date: newDate
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Updated', 'Activity date updated successfully');
            // Refresh the calendar
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showToast('error', 'Error', data.message || 'Failed to update activity date');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Error', 'Failed to update activity date');
    });
}

// Setup calendar interactions
function setupCalendarInteractions() {
    const calendarDays = document.querySelectorAll('.calendar-day:not(.empty-day)');
    
    calendarDays.forEach(day => {
        // Show/hide quick add button on hover
        day.addEventListener('mouseenter', function() {
            this.querySelector('.quick-add').style.display = 'block';
        });
        
        day.addEventListener('mouseleave', function() {
            this.querySelector('.quick-add').style.display = 'none';
        });
        
        // Handle activity indicator clicks
        const indicators = day.querySelectorAll('.activity-indicator');
        indicators.forEach(indicator => {
            indicator.addEventListener('click', function() {
                showActivityDetails(this.dataset.activityId);
            });
        });
    });
}

// Show activity details
function showActivityDetails(activityId) {
    const panel = document.getElementById('activityDetailsPanel');
    const content = document.getElementById('activityDetailsContent');
    
    // Load activity details via AJAX
    fetch(`<?= $base ?>php/scripts/schedule/get_activity_details.php?id=${activityId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                content.innerHTML = `
                    <div class="activity-detail-card">
                        <h6>${data.activity.activityName}</h6>
                        <p class="text-muted small">${data.activity.activityDescription || 'No description'}</p>
                        <div class="activity-meta">
                            <div class="meta-item">
                                <i class="ri-calendar-line me-2"></i>
                                <span>Due: ${data.activity.activityDate}</span>
                            </div>
                            <div class="meta-item">
                                <i class="ri-user-line me-2"></i>
                                <span>Owner: ${data.activity.activityOwnerName || 'Unassigned'}</span>
                            </div>
                            <div class="meta-item">
                                <i class="ri-folder-line me-2"></i>
                                <span>Category: ${data.activity.activityCategoryName || 'None'}</span>
                            </div>
                        </div>
                        <div class="activity-actions mt-3">
                            <button class="btn btn-primary btn-sm me-2" onclick="editActivity(${activityId})">
                                <i class="ri-edit-line me-1"></i>Edit
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" onclick="viewActivityDetails(${activityId})">
                                <i class="ri-eye-line me-1"></i>View
                            </button>
                        </div>
                    </div>
                `;
                panel.style.display = 'block';
            } else {
                showToast('error', 'Error', 'Failed to load activity details');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Error', 'Failed to load activity details');
        });
}

// Close activity details
function closeActivityDetails() {
    document.getElementById('activityDetailsPanel').style.display = 'none';
}

// Quick add activity
function quickAddActivity(date) {
    // Open the activity management modal with pre-filled date
    const modal = new bootstrap.Modal(document.getElementById('manage_activity'));
    modal.show();
    
    // Set the date field in the modal
    setTimeout(() => {
        const dateField = document.querySelector('#manage_activity input[name="activityDate"]');
        if (dateField) {
            dateField.value = date;
        }
    }, 500);
}

// Setup calendar view toggle
function setupCalendarViewToggle() {
    const viewButtons = document.querySelectorAll('.calendar-header .btn-group button');
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const view = this.dataset.view;
            
            viewButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Switch calendar view
            switchCalendarView(view);
        });
    });
}

// Switch calendar view
function switchCalendarView(view) {
    const calendarBody = document.querySelector('.calendar-body');
    
    switch(view) {
        case 'week':
            // Show week view
            showWeekView();
            break;
        case 'day':
            // Show day view
            showDayView();
            break;
        default:
            // Show month view
            showMonthView();
            break;
    }
}

// Show week view
function showWeekView() {
    // Implementation for week view
    console.log('Switching to week view');
}

// Show day view
function showDayView() {
    // Implementation for day view
    console.log('Switching to day view');
}

// Show month view
function showMonthView() {
    // Implementation for month view
    console.log('Switching to month view');
}

// Initialize calendar when view is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('calendar-view').classList.contains('active')) {
        initializeCalendarView();
    }
});

// Re-initialize when switching to calendar view
document.addEventListener('viewChanged', function(e) {
    if (e.detail.view === 'calendar') {
        setTimeout(() => {
            initializeCalendarView();
        }, 100);
    }
});
</script>

<style>
/* Calendar View Specific Styles */
.calendar-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    overflow: hidden;
}

.calendar-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    padding: 1.5rem;
}

.calendar-body {
    padding: 1rem;
}

.calendar-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    margin-bottom: 1rem;
}

.weekday-header {
    background: #f8f9fa;
    padding: 0.75rem;
    text-align: center;
    font-weight: 600;
    color: #6c757d;
    border-radius: 4px;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1px;
    background: #dee2e6;
    border-radius: 8px;
    overflow: hidden;
}

.calendar-day {
    background: white;
    min-height: 120px;
    padding: 0.5rem;
    position: relative;
    transition: all 0.2s ease;
}

.calendar-day:hover {
    background: #f8f9fa;
}

.calendar-day.today {
    background: rgba(255, 193, 7, 0.1);
    border: 2px solid #ffc107;
}

.calendar-day.has-activities {
    background: rgba(0, 123, 255, 0.05);
}

.calendar-day.drag-over {
    background: rgba(0, 123, 255, 0.1);
    border: 2px dashed #007bff;
}

.calendar-day.empty-day {
    background: #f8f9fa;
    opacity: 0.5;
}

.day-number {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.5rem;
}

.activity-indicators {
    display: flex;
    flex-wrap: wrap;
    gap: 2px;
    margin-bottom: 0.5rem;
}

.activity-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.2s ease;
}

.activity-indicator:hover {
    transform: scale(1.2);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.activity-indicator.dragging {
    opacity: 0.5;
}

.quick-add {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
}

.quick-add .btn {
    padding: 0.25rem;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Activity Details Panel */
.activity-details-panel {
    position: fixed;
    top: 50%;
    right: 20px;
    transform: translateY(-50%);
    width: 350px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    z-index: 1040;
    max-height: 80vh;
    overflow: hidden;
}

.panel-header {
    background: #f8f9fa;
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.panel-body {
    padding: 1rem;
    max-height: 60vh;
    overflow-y: auto;
}

.activity-detail-card {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
}

.activity-meta {
    margin: 1rem 0;
}

.meta-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    color: #6c757d;
}

.activity-actions {
    display: flex;
    gap: 0.5rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .calendar-grid {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    
    .calendar-weekdays {
        display: none;
    }
    
    .calendar-day {
        min-height: 80px;
        margin-bottom: 0.5rem;
    }
    
    .activity-details-panel {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        transform: none;
        border-radius: 0;
    }
}

/* Animation for dragging */
@keyframes dragPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.activity-indicator.dragging {
    animation: dragPulse 0.5s infinite;
}
</style>
