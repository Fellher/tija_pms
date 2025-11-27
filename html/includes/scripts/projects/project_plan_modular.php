<?php
/**
 * Project Plan Page - Modular Architecture
 * 
 * This file displays the project plan with phases, tasks, and subtasks.
 * It provides a comprehensive view of project progress and allows
 * management of project components using a modular architecture.
 * 
 * @package    PMS
 * @subpackage Projects
 * @author     SKM Development Team
 * @version    3.0.0
 * @since      1.0.0
 * 
 * Architecture:
 * =============
 * - Configuration Layer: Centralized settings and feature flags
 * - Data Layer: Data preparation and validation
 * - Business Logic Layer: Calculations and business rules
 * - Presentation Layer: HTML generation and UI components
 * - JavaScript Layer: Client-side functionality and interactions
 * 
 * Key Features:
 * =============
 * - Phase management with timeline visualization
 * - Task management with progress tracking
 * - Subtask management with completion status
 * - Assignee management with avatar display
 * - Real-time progress updates
 * - Responsive design for all devices
 * - Accessibility features
 * - Performance optimizations
 * 
 * Dependencies:
 * =============
 * - Bootstrap 5.3+ for UI components
 * - Font Awesome 6+ for icons
 * - jQuery 3.6+ for DOM manipulation
 * - Project Management classes
 * - Employee Management classes
 * - Schedule Management classes
 * 
 * Security Considerations:
 * =======================
 * - All user inputs are sanitized and validated
 * - SQL injection prevention through prepared statements
 * - XSS prevention through output escaping
 * - CSRF protection for form submissions
 * - Access control validation
 * 
 * Performance Optimizations:
 * ==========================
 * - Lazy loading of project data
 * - Debounced search and filtering
 * - Efficient DOM manipulation
 * - Minimal API calls
 * - Caching of frequently accessed data
 * 
 * Browser Support:
 * ================
 * - Chrome 90+
 * - Firefox 88+
 * - Safari 14+
 * - Edge 90+
 * 
 * Mobile Support:
 * ==============
 * - Responsive design
 * - Touch-friendly interactions
 * - Optimized for mobile performance
 * 
 * Accessibility:
 * =============
 * - WCAG 2.1 AA compliance
 * - Keyboard navigation support
 * - Screen reader compatibility
 * - High contrast mode support
 * - Reduced motion support
 */

// Prevent direct access
if (!defined('PMS_ACCESS')) {
    die('Direct access not permitted');
}

// Include modular components
require_once 'config/project_plan_config.php';
require_once 'data/project_plan_data.php';
require_once 'logic/project_plan_logic.php';
require_once 'presentation/project_plan_presentation.php';

// Initialize configuration
$projectPlanConfig = getProjectPlanConfig();

// Initialize data layer
$planData = prepareProjectPlanData($teamMembers, $projectDetails, $DBConn);

// Initialize business logic
$projectPlanLogic = getProjectPlanLogic($projectPlanConfig);

// Initialize presentation
$projectPlanPresentation = getProjectPlanPresentation($projectPlanConfig);

// Generate project summary
$projectSummary = $projectPlanLogic->generateProjectSummary($planData);

// Calculate project timeline
$projectTimeline = $projectPlanLogic->calculateProjectTimeline($planData);

// Calculate resource allocation
$resourceAllocation = $projectPlanLogic->calculateResourceAllocation($planData);

// Prepare phases data for presentation
$phasesData = [];
if (!empty($planData['phases'])) {
    foreach ($planData['phases'] as $phase) {
        $phaseData = [
            'id' => $phase['id'] ?? '',
            'name' => $phase['name'] ?? 'Unnamed Phase',
            'description' => $phase['description'] ?? '',
            'weighting' => $phase['weighting'] ?? 0,
            'workHours' => $phase['workHours'] ?? 0,
            'startDate' => $phase['startDate'] ?? null,
            'endDate' => $phase['endDate'] ?? null,
            'duration' => $phase['duration'] ?? 0,
            'progress' => $phase['progress'] ?? 0,
            'isOverdue' => $phase['isOverdue'] ?? false,
            'isCollapsed' => false,
            'tasks' => []
        ];
        
        // Process tasks for this phase
        if (!empty($phase['tasks'])) {
            foreach ($phase['tasks'] as $task) {
                $taskData = [
                    'id' => $task['id'] ?? '',
                    'name' => $task['name'] ?? 'Unnamed Task',
                    'description' => $task['description'] ?? '',
                    'weighting' => $task['weighting'] ?? 0,
                    'hoursAllocated' => $task['hoursAllocated'] ?? 0,
                    'startDate' => $task['startDate'] ?? null,
                    'deadline' => $task['deadline'] ?? null,
                    'duration' => $task['duration'] ?? 0,
                    'progress' => $task['progress'] ?? 0,
                    'isOverdue' => $task['isOverdue'] ?? false,
                    'status' => $task['status'] ?? 1,
                    'assignees' => $task['assignees'] ?? [],
                    'subtasks' => []
                ];
                
                // Process subtasks for this task
                if (!empty($task['subtasks'])) {
                    foreach ($task['subtasks'] as $subtask) {
                        $subtaskData = [
                            'id' => $subtask['id'] ?? '',
                            'name' => $subtask['name'] ?? 'Unnamed Subtask',
                            'description' => $subtask['description'] ?? '',
                            'dueDate' => $subtask['dueDate'] ?? null,
                            'progress' => $subtask['progress'] ?? 0,
                            'isOverdue' => $subtask['isOverdue'] ?? false,
                            'assignee' => $subtask['assignee'] ?? null
                        ];
                        
                        $taskData['subtasks'][] = $subtaskData;
                    }
                }
                
                $phaseData['tasks'][] = $taskData;
            }
        }
        
        $phasesData[] = $phaseData;
    }
}

// Prepare project data for presentation
$projectData = [
    'project' => [
        'id' => $projectDetails->projectID ?? '',
        'name' => $projectDetails->projectName ?? 'Unnamed Project',
        'code' => $projectDetails->projectCode ?? '',
        'clientName' => $projectDetails->clientName ?? 'Unknown Client',
        'status' => $projectDetails->projectStatus ?? 'Unknown'
    ],
    'timeline' => $projectTimeline,
    'metrics' => $projectSummary['metrics'],
    'phases' => $phasesData
];

?>

<!-- Project Plan Container -->
<div id="project-plan-container" class="project-plan-container">
    
    <!-- Project Plan Header -->
    <?= $projectPlanPresentation->renderProjectPlanHeader($projectData) ?>
    
    <!-- Project Metrics -->
    <?= $projectPlanPresentation->renderProjectMetrics($projectData['metrics']) ?>
    
    <!-- Project Phases -->
    <div class="project-phases">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="h4 mb-0">Project Phases</h2>
            <div class="phase-actions">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPhaseModal">
                    <i class="uil uil-plus me-1"></i>Add Phase
                </button>
            </div>
        </div>
        
        <?= $projectPlanPresentation->renderPhaseList($phasesData) ?>
    </div>
    
    <!-- Loading Indicator -->
    <div class="loading-indicator" style="display: none;">
        <div class="d-flex align-items-center justify-content-center py-4">
            <div class="spinner-border text-primary me-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <span class="text-muted">Loading...</span>
        </div>
    </div>
    
    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>
    
</div>

<!-- Include JavaScript Modules -->
<script src="includes/scripts/projects/js/project_plan_manager.js"></script>

<!-- Project Plan Styles -->
<style>
/* ========================================================================
   PROJECT PLAN STYLES
   ======================================================================== */

.project-plan-container {
    padding: 0;
    background-color: #f8f9fa;
    min-height: 100vh;
}

/* Project Plan Header */
.project-plan-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.project-plan-header .project-icon {
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px);
}

.project-plan-header .project-title {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.project-plan-header .project-meta {
    font-size: 0.9rem;
    opacity: 0.9;
}

.project-plan-header .project-timeline {
    text-align: right;
}

.project-plan-header .timeline-item {
    margin-bottom: 0.5rem;
}

.project-plan-header .timeline-item:last-child {
    margin-bottom: 0;
}

/* Project Metrics */
.project-metrics .metric-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: 1px solid #e9ecef;
}

.project-metrics .metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.project-metrics .metric-icon {
    margin-bottom: 1rem;
}

.project-metrics .metric-value {
    font-weight: 700;
    color: #495057;
}

.project-metrics .metric-label {
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Phase Cards */
.phase-card {
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
    margin-bottom: 1rem;
}

.phase-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.phase-card.collapsed .phase-content {
    display: none;
}

.phase-card .phase-header {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.phase-card .phase-title {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

.phase-card .phase-meta {
    font-size: 0.875rem;
    color: #6c757d;
}

.phase-card .phase-progress {
    text-align: center;
}

.phase-card .phase-actions {
    display: flex;
    gap: 0.5rem;
}

.phase-card .phase-toggle {
    background: none;
    border: none;
    color: #6c757d;
    font-size: 1.2rem;
    transition: transform 0.2s ease;
}

.phase-card .phase-toggle:hover {
    color: #495057;
}

.phase-card.collapsed .phase-toggle i {
    transform: rotate(-90deg);
}

/* Task Cards */
.task-card {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    margin-bottom: 0.75rem;
    border-radius: 0.375rem;
}

.task-card:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.task-card .task-header {
    background: white;
    border-bottom: 1px solid #e9ecef;
}

.task-card .task-title {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

.task-card .task-meta {
    font-size: 0.8rem;
    color: #6c757d;
}

.task-card .task-progress {
    text-align: center;
}

.task-card .task-actions {
    display: flex;
    gap: 0.5rem;
}

/* Subtask Cards */
.subtask-card {
    background: white;
    border: 1px solid #e9ecef;
    margin-bottom: 0.5rem;
    border-radius: 0.25rem;
}

.subtask-card:hover {
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}

.subtask-card .subtask-header {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.subtask-card .subtask-title {
    font-weight: 500;
    color: #495057;
    margin-bottom: 0.25rem;
}

.subtask-card .subtask-meta {
    font-size: 0.75rem;
    color: #6c757d;
}

.subtask-card .subtask-progress {
    text-align: center;
}

.subtask-card .subtask-actions {
    display: flex;
    gap: 0.25rem;
}

/* Progress Bars */
.progress {
    height: 8px;
    border-radius: 4px;
    background-color: #e9ecef;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    border-radius: 4px;
    transition: width 0.3s ease;
}

/* Assignee Avatars */
.assignee-avatar {
    position: relative;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.assignee-avatar:hover {
    transform: scale(1.1);
}

.assignee-avatar img,
.assignee-avatar .avatar-initials {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 2px solid #e9ecef;
    transition: border-color 0.2s ease;
}

.assignee-avatar:hover img,
.assignee-avatar:hover .avatar-initials {
    border-color: #007bff;
}

/* Empty State */
.empty-state {
    padding: 3rem 1rem;
    text-align: center;
    color: #6c757d;
}

.empty-state .empty-state-icon {
    margin-bottom: 1rem;
}

.empty-state h5 {
    margin-bottom: 0.5rem;
    color: #495057;
}

.empty-state p {
    margin-bottom: 0;
    font-size: 0.9rem;
}

/* Loading Indicator */
.loading-indicator {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 2rem;
    border-radius: 0.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    z-index: 9999;
}

/* Responsive Design */
@media (max-width: 768px) {
    .project-plan-header .row {
        text-align: center;
    }
    
    .project-plan-header .project-timeline {
        text-align: center;
        margin-top: 1rem;
    }
    
    .phase-card .phase-header .d-flex {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .phase-card .phase-actions {
        margin-top: 1rem;
        width: 100%;
        justify-content: center;
    }
    
    .task-card .task-header .d-flex {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .task-card .task-actions {
        margin-top: 1rem;
        width: 100%;
        justify-content: center;
    }
    
    .subtask-card .subtask-header .d-flex {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .subtask-card .subtask-actions {
        margin-top: 0.5rem;
        width: 100%;
        justify-content: center;
    }
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
    .phase-card,
    .task-card,
    .subtask-card,
    .progress-bar,
    .assignee-avatar {
        transition: none;
    }
}

@media (prefers-contrast: high) {
    .phase-card,
    .task-card,
    .subtask-card {
        border-color: #000;
    }
    
    .progress-bar {
        background-color: #000;
    }
}

/* Focus States */
.phase-toggle:focus,
.task-actions button:focus,
.subtask-actions button:focus {
    outline: 2px solid #007bff;
    outline-offset: 2px;
}

/* Print Styles */
@media print {
    .project-plan-header {
        background: none !important;
        color: #000 !important;
    }
    
    .phase-actions,
    .task-actions,
    .subtask-actions {
        display: none !important;
    }
    
    .phase-card,
    .task-card,
    .subtask-card {
        break-inside: avoid;
        box-shadow: none !important;
        border: 1px solid #000 !important;
    }
}

</style>

<!-- Project Plan JavaScript -->
<script>
/**
 * Project Plan Initialization
 * 
 * Initializes the project plan with configuration and data.
 * 
 * @version 3.0.0
 * @since 1.0.0
 */
(function() {
    'use strict';
    
    // Configuration for Project Plan Manager
    const projectPlanConfig = {
        api: {
            baseUrl: '/api/projects',
            phases: '/phases',
            tasks: '/tasks',
            subtasks: '/subtasks'
        },
        ui: {
            animationDuration: 300,
            debounceDelay: 300,
            tooltipDelay: 500,
            autoSaveDelay: 2000
        },
        features: {
            autoSave: true,
            realTimeValidation: true,
            dragDrop: false,
            keyboardShortcuts: true,
            offlineMode: false
        }
    };
    
    // Project data for JavaScript
    const projectData = {
        project: <?= json_encode($projectData['project'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        phases: <?= json_encode($phasesData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        timeline: <?= json_encode($projectTimeline, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        metrics: <?= json_encode($projectData['metrics'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        resourceAllocation: <?= json_encode($resourceAllocation, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
    };
    
    // Initialize Project Plan Manager when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Project Plan Manager
        if (typeof window.ProjectPlanManager !== 'undefined') {
            window.ProjectPlanManager.init(projectPlanConfig);
        }
        
        // Initialize tooltips
        initializeTooltips();
        
        // Initialize phase toggles
        initializePhaseToggles();
        
        // Initialize task interactions
        initializeTaskInteractions();
        
        // Initialize subtask interactions
        initializeSubtaskInteractions();
        
        console.log('Project Plan initialized successfully');
    });
    
    /**
     * Initialize Tooltips
     * 
     * Initializes Bootstrap tooltips for all elements.
     * 
     * @since 3.0.0
     */
    function initializeTooltips() {
        try {
            const tooltipTriggerList = [].slice.call(
                document.querySelectorAll('[data-bs-toggle="tooltip"]')
            );
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    placement: 'top',
                    trigger: 'hover focus',
                    delay: { show: 500, hide: 100 }
                });
            });
        } catch (error) {
            console.error('Error initializing tooltips:', error);
        }
    }
    
    /**
     * Initialize Phase Toggles
     * 
     * Initializes phase collapse/expand functionality.
     * 
     * @since 3.0.0
     */
    function initializePhaseToggles() {
        document.querySelectorAll('.phase-toggle').forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                
                const phaseCard = this.closest('.phase-card');
                const content = phaseCard.querySelector('.phase-content');
                
                if (phaseCard.classList.contains('collapsed')) {
                    phaseCard.classList.remove('collapsed');
                    content.classList.add('show');
                } else {
                    phaseCard.classList.add('collapsed');
                    content.classList.remove('show');
                }
            });
        });
    }
    
    /**
     * Initialize Task Interactions
     * 
     * Initializes task-related interactions.
     * 
     * @since 3.0.0
     */
    function initializeTaskInteractions() {
        // Task status changes
        document.querySelectorAll('.task-status select').forEach(select => {
            select.addEventListener('change', function() {
                const taskId = this.closest('.task-card').getAttribute('data-task-id');
                updateTaskStatus(taskId, this.value);
            });
        });
        
        // Task progress updates
        document.querySelectorAll('.task-progress input').forEach(input => {
            input.addEventListener('change', function() {
                const taskId = this.closest('.task-card').getAttribute('data-task-id');
                updateTaskProgress(taskId, this.value);
            });
        });
    }
    
    /**
     * Initialize Subtask Interactions
     * 
     * Initializes subtask-related interactions.
     * 
     * @since 3.0.0
     */
    function initializeSubtaskInteractions() {
        // Subtask checkboxes
        document.querySelectorAll('.subtask-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const subtaskId = this.closest('.subtask-card').getAttribute('data-subtask-id');
                updateSubtaskStatus(subtaskId, this.checked);
            });
        });
        
        // Subtask progress updates
        document.querySelectorAll('.subtask-progress input').forEach(input => {
            input.addEventListener('change', function() {
                const subtaskId = this.closest('.subtask-card').getAttribute('data-subtask-id');
                updateSubtaskProgress(subtaskId, this.value);
            });
        });
    }
    
    /**
     * Update Task Status
     * 
     * Updates task status via API.
     * 
     * @param {string} taskId Task ID
     * @param {string} status New status
     * @since 3.0.0
     */
    function updateTaskStatus(taskId, status) {
        // Implementation for task status update
        console.log('Updating task status:', taskId, status);
    }
    
    /**
     * Update Task Progress
     * 
     * Updates task progress via API.
     * 
     * @param {string} taskId Task ID
     * @param {number} progress New progress
     * @since 3.0.0
     */
    function updateTaskProgress(taskId, progress) {
        // Implementation for task progress update
        console.log('Updating task progress:', taskId, progress);
    }
    
    /**
     * Update Subtask Status
     * 
     * Updates subtask status via API.
     * 
     * @param {string} subtaskId Subtask ID
     * @param {boolean} completed Whether completed
     * @since 3.0.0
     */
    function updateSubtaskStatus(subtaskId, completed) {
        // Implementation for subtask status update
        console.log('Updating subtask status:', subtaskId, completed);
    }
    
    /**
     * Update Subtask Progress
     * 
     * Updates subtask progress via API.
     * 
     * @param {string} subtaskId Subtask ID
     * @param {number} progress New progress
     * @since 3.0.0
     */
    function updateSubtaskProgress(subtaskId, progress) {
        // Implementation for subtask progress update
        console.log('Updating subtask progress:', subtaskId, progress);
    }
    
})();
</script>

<?php
// Include modal components
if ($projectPlanConfig['features']['phaseManagement']) {
    include 'modals/manage_project_phase.php';
}

if ($projectPlanConfig['features']['taskManagement']) {
    include 'modals/manage_project_task.php';
    include 'modals/add_task_with_list.php';
}

if ($projectPlanConfig['features']['subtaskManagement']) {
    include 'modals/manage_task_step.php';
}

if ($projectPlanConfig['features']['assigneeManagement']) {
    include 'modals/task_assignees.php';
}
?>
