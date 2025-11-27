<?php 
/**
 * Enhanced Activity Management System
 * 
 * This page provides a comprehensive activity management interface with multiple views:
 * - Calendar View: Visual calendar with drag-and-drop functionality
 * - Kanban View: Board-style organization by status
 * - List View: Traditional table/list format
 * - Analytics View: Metrics and performance insights
 * 
 * Features:
 * - Real-time updates
 * - Drag-and-drop functionality
 * - Advanced filtering and search
 * - Bulk operations
 * - Activity templates
 * - Time tracking integration
 * - Mobile responsive design
 * 
 * @author System Administrator
 * @version 2.0
 * @since 2024
 */

if(!$isValidUser) {
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}

// ============================================================================
// DATA INITIALIZATION AND VALIDATION
// ============================================================================

/**
 * Initialize date parameters with proper validation
 * Supports ISO date format, year/month, and year/week combinations
 */
$DOF = null;
$dt = new DateTime();

if (isset($_GET['d']) && !empty($_GET['d']) && preg_match($config['ISODateFormat'], $_GET['d'])) {
   $DOF = Utility::clean_string($_GET['d']);
   $dt = date_create($DOF);
} else {	
   $year = (isset($_GET['year']) && !empty($_GET['year'])) ? Utility::clean_string($_GET['year']) : $dt->format('o');   
   $month = (isset($_GET['month']) && !empty($_GET['month'])) ? Utility::clean_string($_GET['month']) : "";
   $week = (isset($_GET['week']) && !empty($_GET['week'])) ? Utility::clean_string($_GET['week']) : "";
   
   if($year && $week){
      $dt->setISODate($year, $week);
   } elseif($year && $month) {
      $dt->setDate($year, $month, 1);
   }    
}

$DOF = $dt->format('Y-m-d');
$getString .= "&d={$DOF}";
$year = (isset($year) && !empty($year))? $year : $dt->format('o');
$week = (isset($week) && !empty($week)) ? $week : $dt->format('W');
$month = (isset($month) && !empty($month) ) ? $month : $dt->format('m');

/**
 * Initialize user and organizational context
 * Determines current user, organization, and entity for data filtering
 */
$employeeID = (isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) : $userDetails->ID;
$employeeDetails = Data::users(array('ID'=>$employeeID), true, $DBConn);
$orgDataID = isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : $employeeDetails->orgDataID;
$entityID = isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : $employeeDetails->entityID;

/**
 * Fetch all required data for activity management
 * Includes employees, clients, projects, and activity-related data
 */
$employees = Employee::employees([], false, $DBConn);
$clients = Client::clients(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
$businessUnits = Data::business_units(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
$projects = Projects::projects_full([], false, $DBConn);
$salesCases = Sales::sales_case_full([], false, $DBConn);
$activityCategories = Schedule::activity_categories([], false, $DBConn);
$activityTypes = Schedule::tija_activity_types([], false, $DBConn);
$activities = Schedule::tija_activities(array('activityOwnerID'=>$employeeID), false, $DBConn);
$workTypes = Work::work_types([], false, $DBConn);
$activityStatuses = Schedule::activity_status([], false, $DBConn);

/**
 * Process recurring activities
 * Handles recurring activity instances and merges them with regular activities
 */
$recurringActivities = array_filter($activities, function($activity) {
   return $activity->recurring == 'Y' || $activity->recurring == 'recurring';
});

$newActivity = array();
if($recurringActivities){
   foreach ($recurringActivities as $rekey => $activity) { 
      $recurrencies = Schedule::recurring_activity_instances(['activityID' =>$activity->activityID], false, $DBConn);
      
      if($recurrencies){
         foreach ($recurrencies as $key => $recurrency) { 
            $newActivity[] = $activity;
            unset($activities[$rekey]); 
            $activity->activityDate = $recurrency->activityInstanceDate;
            $activity->activityStartTime = $recurrency->activityinstanceStartTime;
            $activity->activityDurationEndTime = $recurrency->activityInstanceDurationEndTime;
            $activity->activityStatusID = $recurrency->activityStatusID;
            $activity->instance = 'Y';
            $activity->recurringInstanceID = $recurrency->recurringInstanceID;
         }
      } 
   }
}

// Merge recurring activities with regular activities
$activities = array_merge($activities, $newActivity);

/**
 * Calculate activity metrics for dashboard
 * Provides insights into completion rates, overdue items, and performance
 */
$completedThisMonth = array_filter($activities, function($activity) {
   return $activity->activityStatusID == 4 && date('Y-m') == date('Y-m', strtotime($activity->activityDate));
});

$completedActivities = array_filter($activities, function($activity) {
   return $activity->activityStatusID == 4;
});

$overdueActivities = array_filter($activities, function($activity) {
   return $activity->activityStatusID != 4 && strtotime($activity->activityDate) < time();
});

$todayActivities = array_filter($activities, function($activity) {
   return date('Y-m-d') == date('Y-m-d', strtotime($activity->activityDate));
});

// Get current view mode (calendar, kanban, list, analytics)
$currentView = isset($_GET['view']) ? Utility::clean_string($_GET['view']) : 'list';
$validViews = ['calendar', 'kanban', 'list', 'analytics'];
if (!in_array($currentView, $validViews)) {
   $currentView = 'list';
}
?>

<!-- ============================================================================
     ENHANCED ACTIVITY MANAGEMENT INTERFACE
     ============================================================================ -->

<!-- Page Header with View Toggle -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div class="d-flex align-items-center">
        <h1 class="page-title fw-medium fs-24 mb-0 me-3">Activity Management</h1>
        
        <!-- View Toggle Buttons -->
        <div class="btn-group" role="group" aria-label="View Toggle">
            <button type="button" class="btn btn-outline-primary btn-sm view-toggle <?= $currentView == 'list' ? 'active' : '' ?>" data-view="list">
                <i class="ri-list-check"></i> List
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm view-toggle <?= $currentView == 'kanban' ? 'active' : '' ?>" data-view="kanban">
                <i class="ri-dashboard-line"></i> Kanban
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm view-toggle <?= $currentView == 'calendar' ? 'active' : '' ?>" data-view="calendar">
                <i class="ri-calendar-line"></i> Calendar
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm view-toggle <?= $currentView == 'analytics' ? 'active' : '' ?>" data-view="analytics">
                <i class="ri-bar-chart-line"></i> Analytics
            </button>
        </div>
    </div>
    
    <div class="ms-md-1 ms-0">
        <!-- Quick Actions -->
        <div class="btn-group me-2" role="group">
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manage_activity">
                <i class="ri-add-line"></i> Add Activity
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="bulkActionsBtn" style="display: none;">
                <i class="ri-checkbox-multiple-line"></i> Bulk Actions
            </button>
        </div>
        
        <!-- Filter and Search -->
        <div class="d-flex gap-2">
            <div class="input-group input-group-sm" style="width: 200px;">
                <input type="text" class="form-control" id="searchActivities" placeholder="Search activities...">
                <button class="btn btn-outline-secondary" type="button">
                    <i class="ri-search-line"></i>
                </button>
            </div>
            <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="ri-filter-line"></i> Filters
            </button>
        </div>
    </div>
</div>

<!-- Activity Metrics Dashboard -->
<div class="container-fluid">
    <div class="row mb-4">
        <!-- Completed This Month -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="metric-icon bg-success bg-opacity-10 text-success">
                                <i class="ri-check-double-line fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Completed This Month</h6>
                            <h4 class="mb-0 fw-bold"><?= count($completedThisMonth) ?></h4>
                            <small class="text-success">
                                <i class="ri-arrow-up-line"></i> +<?= count($completedThisMonth) ?>% from last month
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overdue Activities -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="metric-icon bg-danger bg-opacity-10 text-danger">
                                <i class="ri-time-line fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Overdue Activities</h6>
                            <h4 class="mb-0 fw-bold text-danger"><?= count($overdueActivities) ?></h4>
                            <small class="text-danger">
                                <i class="ri-arrow-up-line"></i> +<?= count($overdueActivities) ?>% from last week
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Activities -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="metric-icon bg-info bg-opacity-10 text-info">
                                <i class="ri-calendar-todo-line fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Today's Activities</h6>
                            <h4 class="mb-0 fw-bold"><?= count($todayActivities) ?></h4>
                            <small class="text-info">
                                <i class="ri-calendar-line"></i> <?= date('M d, Y') ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completion Rate -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="metric-icon bg-primary bg-opacity-10 text-primary">
                                <i class="ri-percent-line fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Completion Rate</h6>
                            <h4 class="mb-0 fw-bold">
                                <?= count($activities) > 0 ? round((count($completedActivities) / count($activities)) * 100) : 0 ?>%
                            </h4>
                            <small class="text-primary">
                                <i class="ri-target-line"></i> This month
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Area -->
<div class="container-fluid">
    <div class="row">
        <!-- List View -->
        <div id="list-view" class="view-container <?= $currentView == 'list' ? 'active' : '' ?>">
            <?php include 'activity_views/list_view.php'; ?>
        </div>

        <!-- Kanban View -->
        <div id="kanban-view" class="view-container <?= $currentView == 'kanban' ? 'active' : '' ?>">
            <?php include 'activity_views/kanban_view.php'; ?>
        </div>

        <!-- Calendar View -->
        <div id="calendar-view" class="view-container <?= $currentView == 'calendar' ? 'active' : '' ?>">
            <?php include 'activity_views/calendar_view.php'; ?>
        </div>

        <!-- Analytics View -->
        <div id="analytics-view" class="view-container <?= $currentView == 'analytics' ? 'active' : '' ?>">
            <?php include 'activity_views/analytics_view.php'; ?>
        </div>
    </div>
</div>

<!-- Enhanced Styles -->
<style>
/* ============================================================================
   ENHANCED ACTIVITY MANAGEMENT STYLES
   ============================================================================ */

/* View Toggle Styling */
.view-toggle.active {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

.view-container {
    display: none;
}

.view-container.active {
    display: block;
}

/* Metric Icons */
.metric-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Activity Cards */
.activity-card {
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    margin-bottom: 1rem;
}

.activity-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.activity-card.priority-high {
    border-left: 4px solid #dc3545;
}

.activity-card.priority-medium {
    border-left: 4px solid #ffc107;
}

.activity-card.priority-low {
    border-left: 4px solid #28a745;
}

/* Status Badges */
.status-badge {
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
}

.status-todo { background-color: #e9ecef; color: #495057; }
.status-in-progress { background-color: #cff4fc; color: #055160; }
.status-review { background-color: #fff3cd; color: #664d03; }
.status-completed { background-color: #d1e7dd; color: #0f5132; }
.status-cancelled { background-color: #f8d7da; color: #842029; }

/* Kanban Board Styling */
.kanban-board {
    display: flex;
    gap: 1rem;
    overflow-x: auto;
    padding-bottom: 1rem;
}

.kanban-column {
    min-width: 300px;
    background: #f8f9fa;
    border-radius: 12px;
    padding: 1rem;
}

.kanban-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #dee2e6;
}

.kanban-count {
    background: #6c757d;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: bold;
}

/* Drag and Drop */
.draggable {
    cursor: move;
}

.dragging {
    opacity: 0.5;
    transform: rotate(5deg);
}

.drop-zone {
    min-height: 100px;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.drop-zone.drag-over {
    border-color: #007bff;
    background-color: rgba(0, 123, 255, 0.1);
}

/* Calendar Styling */
.calendar-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    overflow: hidden;
}

.calendar-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    padding: 1rem;
    text-align: center;
}

.calendar-day {
    min-height: 120px;
    border: 1px solid #e9ecef;
    padding: 0.5rem;
    position: relative;
}

.calendar-day.has-activities {
    background-color: rgba(0, 123, 255, 0.05);
}

.calendar-day.today {
    background-color: rgba(255, 193, 7, 0.1);
    border: 2px solid #ffc107;
}

.activity-indicator {
    position: absolute;
    top: 5px;
    right: 5px;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #007bff;
}

/* Analytics Charts */
.chart-container {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .kanban-board {
        flex-direction: column;
    }
    
    .kanban-column {
        min-width: 100%;
    }
    
    .btn-group .btn {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
}

/* Loading States */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Toast Notifications */
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1055;
}

/* Filter Panel */
.filter-panel {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

/* Bulk Actions */
.bulk-actions-panel {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    padding: 1rem 1.5rem;
    z-index: 1040;
    display: none;
}

.bulk-actions-panel.show {
    display: block;
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateX(-50%) translateY(100%);
    }
    to {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }
}
</style>

<!-- Enhanced JavaScript -->
<script>
/**
 * Enhanced Activity Management JavaScript
 * 
 * Handles:
 * - View switching
 * - Drag and drop functionality
 * - Real-time updates
 * - Search and filtering
 * - Bulk operations
 * - Toast notifications
 */

document.addEventListener('DOMContentLoaded', function() {
    // ============================================================================
    // VIEW MANAGEMENT
    // ============================================================================
    
    /**
     * Initialize view toggle functionality
     * Allows switching between different activity views
     */
    const viewToggles = document.querySelectorAll('.view-toggle');
    const viewContainers = document.querySelectorAll('.view-container');
    
    viewToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const targetView = this.dataset.view;
            
            // Update URL without page reload
            const url = new URL(window.location);
            url.searchParams.set('view', targetView);
            window.history.pushState({}, '', url);
            
            // Update active states
            viewToggles.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            viewContainers.forEach(container => {
                container.classList.remove('active');
            });
            
            document.getElementById(targetView + '-view').classList.add('active');
            
            // Initialize view-specific functionality
            initializeView(targetView);
        });
    });
    
    /**
     * Initialize view-specific functionality
     * @param {string} view - The view to initialize
     */
    function initializeView(view) {
        switch(view) {
            case 'calendar':
                initializeCalendarView();
                break;
            case 'kanban':
                initializeKanbanView();
                break;
            case 'list':
                initializeListView();
                break;
            case 'analytics':
                initializeAnalyticsView();
                break;
        }
    }
    
    // ============================================================================
    // SEARCH AND FILTERING
    // ============================================================================
    
    /**
     * Initialize search functionality
     */
    const searchInput = document.getElementById('searchActivities');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            const searchTerm = this.value.toLowerCase();
            filterActivities(searchTerm);
        }, 300));
    }
    
    /**
     * Filter activities based on search term
     * @param {string} searchTerm - The search term to filter by
     */
    function filterActivities(searchTerm) {
        const activityCards = document.querySelectorAll('.activity-card');
        
        activityCards.forEach(card => {
            const title = card.querySelector('.activity-title')?.textContent.toLowerCase() || '';
            const description = card.querySelector('.activity-description')?.textContent.toLowerCase() || '';
            
            if (title.includes(searchTerm) || description.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    // ============================================================================
    // DRAG AND DROP FUNCTIONALITY
    // ============================================================================
    
    /**
     * Initialize drag and drop for kanban view
     */
    function initializeKanbanView() {
        const draggableElements = document.querySelectorAll('.draggable');
        const dropZones = document.querySelectorAll('.kanban-column');
        
        draggableElements.forEach(element => {
            element.draggable = true;
            
            element.addEventListener('dragstart', function(e) {
                this.classList.add('dragging');
                e.dataTransfer.setData('text/plain', this.dataset.activityId);
            });
            
            element.addEventListener('dragend', function() {
                this.classList.remove('dragging');
            });
        });
        
        dropZones.forEach(zone => {
            zone.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('drag-over');
            });
            
            zone.addEventListener('dragleave', function() {
                this.classList.remove('drag-over');
            });
            
            zone.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('drag-over');
                
                const activityId = e.dataTransfer.getData('text/plain');
                const newStatus = this.dataset.status;
                
                updateActivityStatus(activityId, newStatus);
            });
        });
    }
    
    // ============================================================================
    // ACTIVITY STATUS UPDATES
    // ============================================================================
    
    /**
     * Update activity status via AJAX
     * @param {string} activityId - The ID of the activity to update
     * @param {string} newStatus - The new status to set
     */
    function updateActivityStatus(activityId, newStatus) {
        showToast('info', 'Updating...', 'Updating activity status...');
        
        fetch('<?= $base ?>php/scripts/schedule/update_activity_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                activityId: activityId,
                status: newStatus
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('success', 'Updated', 'Activity status updated successfully');
                // Refresh the current view
                location.reload();
            } else {
                showToast('error', 'Error', data.message || 'Failed to update activity status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Error', 'Failed to update activity status');
        });
    }
    
    // ============================================================================
    // BULK OPERATIONS
    // ============================================================================
    
    /**
     * Initialize bulk selection functionality
     */
    const bulkActionsBtn = document.getElementById('bulkActionsBtn');
    const bulkActionsPanel = document.createElement('div');
    bulkActionsPanel.className = 'bulk-actions-panel';
    bulkActionsPanel.innerHTML = `
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted">Selected: <span id="selectedCount">0</span> activities</span>
            <div class="btn-group btn-group-sm">
                <button class="btn btn-outline-primary" onclick="bulkUpdateStatus('completed')">
                    <i class="ri-check-line"></i> Complete
                </button>
                <button class="btn btn-outline-warning" onclick="bulkUpdateStatus('in-progress')">
                    <i class="ri-play-line"></i> Start
                </button>
                <button class="btn btn-outline-danger" onclick="bulkDelete()">
                    <i class="ri-delete-bin-line"></i> Delete
                </button>
            </div>
            <button class="btn btn-outline-secondary btn-sm" onclick="clearSelection()">
                <i class="ri-close-line"></i>
            </button>
        </div>
    `;
    document.body.appendChild(bulkActionsPanel);
    
    // ============================================================================
    // TOAST NOTIFICATIONS
    // ============================================================================
    
    /**
     * Show toast notification
     * @param {string} type - The type of toast (success, error, warning, info)
     * @param {string} title - The title of the toast
     * @param {string} message - The message content
     */
    function showToast(type, title, message) {
        const toastContainer = document.querySelector('.toast-container') || createToastContainer();
        
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <strong>${title}</strong><br>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        const bsToast = new bootstrap.Toast(toast, {
            autohide: true,
            delay: 5000
        });
        
        bsToast.show();
        
        // Remove toast element after it's hidden
        toast.addEventListener('hidden.bs.toast', function() {
            this.remove();
        });
    }
    
    /**
     * Create toast container if it doesn't exist
     * @returns {HTMLElement} The toast container element
     */
    function createToastContainer() {
        const container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
        return container;
    }
    
    // ============================================================================
    // UTILITY FUNCTIONS
    // ============================================================================
    
    /**
     * Debounce function to limit function calls
     * @param {Function} func - The function to debounce
     * @param {number} wait - The delay in milliseconds
     * @returns {Function} The debounced function
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // ============================================================================
    // INITIALIZATION
    // ============================================================================
    
    // Initialize the current view
    initializeView('<?= $currentView ?>');
    
    // Initialize other functionality
    if (document.getElementById('searchActivities')) {
        document.getElementById('searchActivities').addEventListener('input', debounce(function() {
            filterActivities(this.value.toLowerCase());
        }, 300));
    }
});

// ============================================================================
// GLOBAL FUNCTIONS
// ============================================================================

/**
 * Bulk update status for selected activities
 * @param {string} status - The status to set
 */
function bulkUpdateStatus(status) {
    const selectedActivities = document.querySelectorAll('.activity-checkbox:checked');
    if (selectedActivities.length === 0) {
        showToast('warning', 'No Selection', 'Please select activities to update');
        return;
    }
    
    const activityIds = Array.from(selectedActivities).map(cb => cb.value);
    
    showToast('info', 'Updating...', `Updating ${activityIds.length} activities...`);
    
    fetch('<?= $base ?>php/scripts/schedule/bulk_update_activities.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            activityIds: activityIds,
            status: status
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Updated', `${data.updated} activities updated successfully`);
            clearSelection();
            location.reload();
        } else {
            showToast('error', 'Error', data.message || 'Failed to update activities');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Error', 'Failed to update activities');
    });
}

/**
 * Clear all selections
 */
function clearSelection() {
    document.querySelectorAll('.activity-checkbox:checked').forEach(cb => cb.checked = false);
    document.querySelector('.bulk-actions-panel').classList.remove('show');
    document.getElementById('bulkActionsBtn').style.display = 'none';
    document.getElementById('selectedCount').textContent = '0';
}

/**
 * Update selection count and show/hide bulk actions
 */
function updateSelection() {
    const selectedCount = document.querySelectorAll('.activity-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = selectedCount;
    
    if (selectedCount > 0) {
        document.querySelector('.bulk-actions-panel').classList.add('show');
        document.getElementById('bulkActionsBtn').style.display = 'inline-block';
    } else {
        document.querySelector('.bulk-actions-panel').classList.remove('show');
        document.getElementById('bulkActionsBtn').style.display = 'none';
    }
}
</script>

<?php
/**
 * Include the activity management modal
 * This modal handles adding, editing, and deleting activities
 */
include 'modals/activity_management_modal.php';
include 'modals/activity_filter_modal.php';
?>
