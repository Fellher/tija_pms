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

// This file is included from project.php and doesn't need direct access protection

// Include modular components
require_once 'config/project_plan_config.php';

// var_dump($config);
require_once 'data/project_plan_data.php';
require_once 'logic/project_plan_logic.php';
require_once 'presentation/project_plan_presentation.php';
$projectPlanPresentation = null;

// Configuration is already loaded from config/project_plan_config.php
// $projectPlanConfig is now available globally

$planData = null;

// Initialize data layer
$planData = getProjectPlanData($projectID, $DBConn, $projectPlanConfig);

// var_dump($planData['teamMembers']);

// Initialize business logic
$projectPlanLogic = getProjectPlanLogic($projectPlanConfig);

// Initialize presentation
$projectPlanPresentation = getProjectPlanPresentation($projectPlanConfig);

// Generate project summary
$projectSummary = $projectPlanLogic->generateProjectSummary($planData);

// Calculate project timeline
$projectTimeline = $projectPlanLogic->calculateProjectTimeline($planData);

// var_dump($projectTimeline);

// Calculate resource allocation
$resourceAllocation = $projectPlanLogic->calculateResourceAllocation($planData);

// Check if data was loaded successfully
if ($planData === false) {
    echo '<div class="alert alert-danger">Error loading project data. Please try again.</div>';
    return;
}
// var_dump($planData);
// Prepare phases data for presentation
$phasesData = [];
// var_dump($planData);
// var_dump($planData['phases']);
if (!empty($planData['phases'])) {
    foreach ($planData['phases'] as $phase) {
        $phaseData = [
            'id' => $phase['id'] ?? '',
            'name' => $phase['name'] ?? 'Unnamed Phase',
            'billingMilestone' => $phase['billingMilestone'] ?? 0,
            'status' => $phase['status'] ?? 'active',
            'projectId' => $planData['project']->projectID ?? '',
            'clientId' => $planData['project']->clientID ?? '',
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
                    'projectPhaseId' => $phase['id'] ?? '',
                    'projectId' => $planData['project']->projectID ?? '',
                    'clientId' => $planData['project']->clientID ?? '',
                    'projectTaskCode'=>$task['projectTaskCode'] ?? '',
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
                            'assignee' => $subtask['assignee'] ?? null,
                            'taskStatusID'=>$task['taskStatusID'] ?? '',
                            'projectTaskTypeID'=>$task['projectTaskTypeID'] ?? '',
                            'projectPhaseID'=>$task['projectPhaseID'] ?? '',
                            'projectID'=>$task['projectID'] ?? '',
                            'clientID'=>$task['clientID'] ?? '',
                            'projectTaskCode'=>$task['projectTaskCode'] ?? '',
                            'assigneeName'=>$subtask['assigneeName'] ?? '',
                            'assigneeInitials'=>$subtask['assigneeInitials'] ?? '',
                            'projectTaskId'=>$task['id'] ?? '',
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
// var_dump($phaseData);
// Prepare project data for presentation
$projectData = [
    'project' => [
        'id' => $planData['project']->projectID ?? '',
        'name' => $planData['project']->projectName ?? 'Unnamed Project',
        'code' => $planData['project']->projectCode ?? '',
        'clientName' => $planData['project']->clientName ?? 'Unknown Client',
        'status' => $planData['project']->projectStatus ?? 'Unknown'
    ],
    'timeline' => $projectTimeline ?? [],
    'metrics' => $projectSummary['metrics'] ?? [],
    'phases' => $phasesData
];
// var_dump($projectData);

// var_dump($projectData);

?>

<!-- Project Plan Container -->
<div id="project-plan-container" class="project-plan-container">

    <!-- Project Plan Header -->
    <?php   //echo $projectPlanPresentation->renderProjectPlanHeader($planData) ?>

    <!-- Project Metrics -->
    <?=  $projectPlanPresentation ? $projectPlanPresentation->renderProjectMetrics($projectData['metrics'] ?? []) : '' ?>

    <!-- Project Phases -->
    <div class="project-phases">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="h4 mb-0">Project Phases</h2>
            <div class="phase-actions d-flex gap-2">
                <!-- View Toggle -->
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-secondary view-toggle-btn active" data-view="list" title="List View">
                        <i class="ri-list-check me-1"></i>List
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary view-toggle-btn" data-view="kanban" title="Kanban View">
                        <i class="ri-layout-column-line me-1"></i>Kanban
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary view-toggle-btn" data-view="gantt" title="Gantt Chart View">
                        <i class="ri-bar-chart-line me-1"></i>Gantt
                    </button>
                </div>
                <button
                    class="btn btn-outline-secondary btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#applyTemplateModal"
                    title="Apply a template to this project"
                >
                    <i class="ri-file-copy-line me-1"></i>Apply Template
                </button>
                <button
                class="btn btn-primary addPhaseBtn"
                data-bs-toggle="modal"
                data-bs-target="#manage_project_phase"
                data-project-start-date="<?= $projectData['timeline']['setProjectStartDate'] ?>"
                data-project-end-date="<?= $projectData['timeline']['setProjectEndDate'] ?>"
                >
                    <i class="uil uil-plus me-1"></i>Add Phase
                </button>
         </div>
      </div>

        <!-- List View -->
        <div id="projectPlanListView" class="project-plan-view">
            <?= $projectPlanPresentation ? $projectPlanPresentation->renderPhaseList($phasesData) : '' ?>
        </div>

        <!-- Kanban View -->
        <div id="projectPlanKanbanView" class="project-plan-view" style="display: none;">
            <div id="kanbanBoard" class="kanban-board">
                <!-- Kanban columns will be generated by JavaScript -->
            </div>
        </div>

        <!-- Gantt Chart View -->
        <div id="projectPlanGanttView" class="project-plan-view" style="display: none;">
            <div class="gantt-chart-container">
                <div class="gantt-toolbar mb-3 d-flex justify-content-between align-items-center">
                    <div class="gantt-controls d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="ganttZoomIn" title="Zoom In">
                            <i class="ri-zoom-in-line"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="ganttZoomOut" title="Zoom Out">
                            <i class="ri-zoom-out-line"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="ganttFitToScreen" title="Fit to Screen">
                            <i class="ri-fullscreen-line"></i>
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" title="View Mode">
                                <i class="ri-calendar-line me-1"></i>Week
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" data-view-mode="Day">Day</a></li>
                                <li><a class="dropdown-item active" href="#" data-view-mode="Week">Week</a></li>
                                <li><a class="dropdown-item" href="#" data-view-mode="Month">Month</a></li>
                                <li><a class="dropdown-item" href="#" data-view-mode="Quarter">Quarter</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="gantt-actions">
                        <button type="button" class="btn btn-sm btn-primary" id="ganttAddPhase" title="Add Phase">
                            <i class="ri-add-line me-1"></i>Add Phase
                        </button>
                        <button type="button" class="btn btn-sm btn-success" id="ganttAddTask" title="Add Task">
                            <i class="ri-add-line me-1"></i>Add Task
                        </button>
                    </div>
                </div>
                <div class="gantt-legend mb-3 d-flex gap-3 flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <div class="gantt-legend-item gantt-legend-phase"></div>
                        <span class="small">Phases</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="gantt-legend-item gantt-legend-task"></div>
                        <span class="small">Tasks</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="gantt-legend-item gantt-legend-subtask"></div>
                        <span class="small">Subtasks</span>
                    </div>
                </div>
                <div id="ganttChart" class="gantt-chart-wrapper"></div>
            </div>
        </div>
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
<!-- <script src="includes/scripts/projects/js/project_plan_manager.js"></script> -->

<!-- All JavaScript functionality moved to project_plan_manager.js -->

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
   .project-metrics .metric-card,
   .project-metrics-enhanced .metric-card {
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      border: 1px solid #e9ecef;
      position: relative;
      overflow: hidden;
   }

   .project-metrics .metric-card:hover,
   .project-metrics-enhanced .metric-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
   }

   .project-metrics .metric-icon,
   .project-metrics-enhanced .metric-icon {
      margin-bottom: 1rem;
   }

   .project-metrics .metric-value,
   .project-metrics-enhanced .metric-value {
      font-weight: 700;
      color: #495057;
   }

   .project-metrics .metric-label,
   .project-metrics-enhanced .metric-label {
      font-size: 0.875rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
   }

   /* Enhanced Metrics Specific Styles */
   .project-metrics-enhanced .metric-card {
      border-left: 4px solid transparent;
   }

   .project-metrics-enhanced .metric-card:hover {
      border-left-color: #007bff;
   }

   .project-metrics-enhanced .metric-card .progress {
      background-color: rgba(0, 0, 0, 0.1);
   }

   .project-metrics-enhanced .metric-card .progress-bar {
      transition: width 0.6s ease;
   }

   /* Health Status Indicators */
   .project-metrics-enhanced .metric-card[data-health="excellent"] {
      border-left-color: #28a745;
   }

   .project-metrics-enhanced .metric-card[data-health="good"] {
      border-left-color: #007bff;
   }

   .project-metrics-enhanced .metric-card[data-health="fair"] {
      border-left-color: #ffc107;
   }

   .project-metrics-enhanced .metric-card[data-health="poor"] {
      border-left-color: #dc3545;
   }

   /* Risk Level Indicators */
   .project-metrics-enhanced .metric-card[data-risk="low"] {
      border-left-color: #28a745;
   }

   .project-metrics-enhanced .metric-card[data-risk="medium"] {
      border-left-color: #ffc107;
   }

   .project-metrics-enhanced .metric-card[data-risk="high"] {
      border-left-color: #dc3545;
   }

   /* Metric Card Animations */
   .project-metrics-enhanced .metric-card {
      animation: fadeInUp 0.6s ease-out;
   }

   .project-metrics-enhanced .metric-card:nth-child(1) { animation-delay: 0.1s; }
   .project-metrics-enhanced .metric-card:nth-child(2) { animation-delay: 0.2s; }
   .project-metrics-enhanced .metric-card:nth-child(3) { animation-delay: 0.3s; }
   .project-metrics-enhanced .metric-card:nth-child(4) { animation-delay: 0.4s; }

   @keyframes fadeInUp {
      from {
         opacity: 0;
         transform: translateY(30px);
      }
      to {
         opacity: 1;
         transform: translateY(0);
      }
   }

   /* Mini Metrics Summary Styles */
   .mini-metrics-summary {
      transition: all 0.3s ease;
      border: 1px solid #e9ecef;
   }

   .mini-metrics-summary:hover {
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
   }

   .mini-metrics-summary .metric-item {
      display: flex;
      align-items: center;
      white-space: nowrap;
   }

   .mini-metrics-summary .metric-value {
      font-size: 1.1rem;
      font-weight: 600;
   }

   .mini-metrics-summary .metric-label {
      font-size: 0.85rem;
      color: #6c757d;
   }

   .mini-metrics-summary .badge {
      font-size: 0.7rem;
      padding: 0.25rem 0.5rem;
   }

   .mini-metrics-summary .btn {
      transition: all 0.2s ease;
   }

   .mini-metrics-summary .btn i {
      transition: transform 0.3s ease;
   }

   .mini-metrics-summary .btn[aria-expanded="true"] i {
      transform: rotate(180deg);
   }

   .mini-metrics-summary .btn[aria-expanded="true"] .toggle-text {
      display: none;
   }

   .mini-metrics-summary .btn[aria-expanded="true"]::after {
      content: "Hide Details";
   }

   /* Collapse Animation */
   .project-metrics-container .collapse {
      transition: all 0.3s ease;
   }

   /* Responsive adjustments for enhanced metrics */
   @media (max-width: 768px) {
      .project-metrics-enhanced .metric-card {
         margin-bottom: 1rem;
      }

      .project-metrics-enhanced .metric-value {
         font-size: 1.5rem;
      }

      .project-metrics-enhanced .metric-label {
         font-size: 0.75rem;
      }

      /* Mini metrics responsive */
      .mini-metrics-summary .d-flex {
         flex-direction: column;
         align-items: flex-start !important;
      }

      .mini-metrics-summary .metric-item {
         margin-bottom: 0.5rem;
      }

      .mini-metrics-summary .btn {
         margin-top: 1rem;
         align-self: flex-end;
      }
   }

   @media (max-width: 576px) {
      .mini-metrics-summary .metric-item {
         margin-right: 1rem !important;
         margin-bottom: 0.25rem;
      }

      .mini-metrics-summary .metric-value {
         font-size: 1rem;
      }

      .mini-metrics-summary .metric-label {
         font-size: 0.8rem;
      }
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

   /* ========================================================================
      KANBAN BOARD STYLES
      ======================================================================== */
   .project-phases {
      width: 100%;
      max-width: 100%;
      overflow: visible;
   }

   .project-plan-view {
      width: 100%;
      max-width: 100%;
      overflow: visible;
   }

   #projectPlanKanbanView {
      width: 100%;
      max-width: 100%;
      overflow-x: auto;
      overflow-y: visible;
      -webkit-overflow-scrolling: touch;
   }

   #projectPlanKanbanView::-webkit-scrollbar {
      height: 8px;
   }

   #projectPlanKanbanView::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 4px;
   }

   #projectPlanKanbanView::-webkit-scrollbar-thumb {
      background: #888;
      border-radius: 4px;
   }

   #projectPlanKanbanView::-webkit-scrollbar-thumb:hover {
      background: #555;
   }

   .kanban-board {
      display: flex;
      gap: 1rem;
      overflow-x: visible;
      overflow-y: visible;
      padding: 1rem 0;
      min-height: 600px;
      width: max-content;
      min-width: 100%;
   }

   .kanban-column {
      flex: 0 0 320px;
      flex-shrink: 0;
      background: #f8f9fa;
      border-radius: 8px;
      padding: 1rem;
      display: flex;
      flex-direction: column;
      min-height: 500px;
      max-height: calc(100vh - 250px);
   }

   .kanban-column-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
      padding-bottom: 0.75rem;
      border-bottom: 2px solid #dee2e6;
   }

   .kanban-column-title {
      font-weight: 600;
      font-size: 1rem;
      color: #212529;
      margin: 0;
   }

   .kanban-column-count {
      background: #fff;
      border-radius: 12px;
      padding: 0.25rem 0.5rem;
      font-size: 0.875rem;
      font-weight: 600;
      color: #6c757d;
   }

   .kanban-column-content {
      flex: 1;
      overflow-y: auto;
      padding-right: 0.5rem;
   }

   .kanban-column-content::-webkit-scrollbar {
      width: 6px;
   }

   .kanban-column-content::-webkit-scrollbar-thumb {
      background: #dee2e6;
      border-radius: 3px;
   }

   .kanban-column-content::-webkit-scrollbar-thumb:hover {
      background: #adb5bd;
   }

   .kanban-card {
      background: #fff;
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 0.75rem;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
      cursor: move;
      transition: all 0.2s ease;
      border-left: 3px solid #0d6efd;
   }

   .kanban-card:hover {
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      transform: translateY(-2px);
   }

   .kanban-card.dragging,
   .kanban-card-dragging {
      opacity: 0.5;
      transform: rotate(5deg);
   }

   .kanban-card-header {
      display: flex;
      justify-content: space-between;
      align-items: start;
      margin-bottom: 0.5rem;
   }

   .kanban-card-title {
      font-weight: 600;
      font-size: 0.95rem;
      color: #212529;
      margin: 0;
      flex: 1;
   }

   .kanban-card-code {
      font-size: 0.75rem;
      color: #6c757d;
      font-family: monospace;
      margin-top: 0.25rem;
   }

   .kanban-card-body {
      margin-top: 0.75rem;
   }

   .kanban-card-description {
      font-size: 0.875rem;
      color: #6c757d;
      margin-bottom: 0.75rem;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
   }

   .kanban-card-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
      margin-bottom: 0.75rem;
   }

   .kanban-card-meta-item {
      font-size: 0.75rem;
      color: #6c757d;
      display: flex;
      align-items: center;
      gap: 0.25rem;
   }

   .kanban-card-progress {
      margin-bottom: 0.75rem;
   }

   .kanban-card-progress-bar {
      height: 4px;
      background: #e9ecef;
      border-radius: 2px;
      overflow: hidden;
   }

   .kanban-card-progress-fill {
      height: 100%;
      background: #0d6efd;
      transition: width 0.3s ease;
   }

   .kanban-card-assignees {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin-top: 0.75rem;
      padding-top: 0.75rem;
      border-top: 1px solid #e9ecef;
   }

   .kanban-card-assignee-avatar {
      width: 24px;
      height: 24px;
      border-radius: 50%;
      background: #0d6efd;
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.7rem;
      font-weight: 600;
   }

   .kanban-card-subtasks {
      margin-top: 0.5rem;
      padding-top: 0.5rem;
      border-top: 1px solid #f0f0f0;
   }

   .kanban-card-subtask {
      font-size: 0.8rem;
      color: #6c757d;
      padding: 0.25rem 0;
      display: flex;
      align-items: center;
      gap: 0.5rem;
   }

   .kanban-card-subtask i {
      font-size: 0.7rem;
   }

   .kanban-card-subtask.completed {
      text-decoration: line-through;
      opacity: 0.6;
   }

   .kanban-column.drag-over {
      background: #e7f3ff;
      border: 2px dashed #0d6efd;
   }

   .view-toggle-btn.active {
      background-color: #0d6efd;
      color: #fff;
      border-color: #0d6efd;
   }

   @media (max-width: 768px) {
      .kanban-board {
         flex-direction: column;
      }

      .kanban-column {
         flex: 1 1 auto;
         min-width: 100%;
      }
   }

   /* ========================================================================
      GANTT CHART STYLES
      ======================================================================== */
   .gantt-chart-container {
      background: #fff;
      border-radius: 8px;
      padding: 1rem;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
   }

   .gantt-toolbar {
      border-bottom: 1px solid #e9ecef;
      padding-bottom: 1rem;
      margin-bottom: 1rem;
   }

   .gantt-chart-wrapper {
      position: relative;
      overflow-x: auto;
      overflow-y: auto;
      max-height: calc(100vh - 300px);
      min-height: 500px;
      border: 1px solid #e9ecef;
      border-radius: 4px;
      background: #fff;
   }

   /* Frappe Gantt Custom Styles */
   .gantt-container {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
   }

   .gantt .bar-wrapper .bar {
      transition: all 0.2s ease;
   }

   .gantt .bar-wrapper .bar:hover {
      filter: brightness(1.1);
      cursor: move;
   }

   /* Gantt Legend */
   .gantt-legend {
      padding: 0.75rem;
      background: #f8f9fa;
      border-radius: 6px;
      border: 1px solid #e9ecef;
   }

   .gantt-legend-item {
      width: 24px;
      height: 16px;
      border-radius: 3px;
      flex-shrink: 0;
   }

   .gantt-legend-phase {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
   }

   .gantt-legend-task {
      background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
      box-shadow: 0 1px 3px rgba(13, 110, 253, 0.25);
   }

   .gantt-legend-subtask {
      background: linear-gradient(135deg, #20c997 0%, #1aa179 100%);
      box-shadow: 0 1px 2px rgba(32, 201, 151, 0.2);
   }

   /* Phase Bars - Purple/Indigo */
   .gantt-phase .bar,
   .bar-wrapper.gantt-phase .bar {
      fill: #667eea !important;
      stroke: #5568d3 !important;
      stroke-width: 2.5 !important;
      filter: drop-shadow(0 2px 4px rgba(102, 126, 234, 0.3));
   }

   .gantt-phase .bar:hover,
   .bar-wrapper.gantt-phase .bar:hover {
      fill: #7c8ef5 !important;
      stroke: #667eea !important;
      filter: drop-shadow(0 4px 8px rgba(102, 126, 234, 0.4));
   }

   .gantt-phase .bar-progress,
   .bar-wrapper.gantt-phase .bar-progress {
      fill: rgba(255, 255, 255, 0.4) !important;
   }

   /* Task Bars - Blue */
   .gantt-task .bar,
   .bar-wrapper.gantt-task .bar {
      fill: #0d6efd !important;
      stroke: #0a58ca !important;
      stroke-width: 2 !important;
      filter: drop-shadow(0 1px 3px rgba(13, 110, 253, 0.25));
   }

   .gantt-task .bar:hover,
   .bar-wrapper.gantt-task .bar:hover {
      fill: #0d6efd !important;
      stroke: #084298 !important;
      filter: drop-shadow(0 2px 6px rgba(13, 110, 253, 0.35));
   }

   .gantt-task .bar-progress,
   .bar-wrapper.gantt-task .bar-progress {
      fill: rgba(255, 255, 255, 0.35) !important;
   }

   /* Subtask Bars - Teal/Green */
   .gantt-subtask .bar,
   .bar-wrapper.gantt-subtask .bar {
      fill: #20c997 !important;
      stroke: #1aa179 !important;
      stroke-width: 1.5 !important;
      filter: drop-shadow(0 1px 2px rgba(32, 201, 151, 0.2));
   }

   .gantt-subtask .bar:hover,
   .bar-wrapper.gantt-subtask .bar:hover {
      fill: #2dd4a8 !important;
      stroke: #20c997 !important;
      filter: drop-shadow(0 2px 4px rgba(32, 201, 151, 0.3));
   }

   .gantt-subtask .bar-progress,
   .bar-wrapper.gantt-subtask .bar-progress {
      fill: rgba(255, 255, 255, 0.3) !important;
   }

   /* Milestone markers for subtasks */
   .bar-wrapper.gantt-subtask {
      opacity: 0.9;
   }

   .bar-wrapper.gantt-subtask:hover {
      opacity: 1;
   }

   .gantt .bar-progress {
      fill: rgba(255, 255, 255, 0.3);
   }

   .gantt .bar-label {
      fill: #fff;
      font-size: 12px;
      font-weight: 500;
   }

   .gantt .grid-background {
      fill: #f8f9fa;
   }

   .gantt .grid-header {
      fill: #e9ecef;
      font-weight: 600;
   }

   .gantt .row-line {
      stroke: #e9ecef;
   }

   .gantt .tick {
      stroke: #dee2e6;
   }

   .gantt .today-highlight {
      fill: rgba(255, 193, 7, 0.2);
   }

   /* Gantt Popup Styles */
   .gantt-popup {
      background: #fff;
      border-radius: 6px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      padding: 0;
      min-width: 250px;
      max-width: 350px;
   }

   .gantt-popup-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: #fff;
      padding: 0.75rem 1rem;
      border-radius: 6px 6px 0 0;
      font-weight: 600;
   }

   .gantt-popup-body {
      padding: 1rem;
   }

   .gantt-popup-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.5rem 0;
      border-bottom: 1px solid #f0f0f0;
   }

   .gantt-popup-item:last-child {
      border-bottom: none;
   }

   .gantt-popup-label {
      font-weight: 600;
      color: #6c757d;
      font-size: 0.875rem;
   }

   .gantt-popup-value {
      color: #212529;
      font-size: 0.875rem;
      text-align: right;
   }

   /* Gantt Controls */
   .gantt-controls .btn {
      transition: all 0.2s ease;
   }

   .gantt-controls .btn:hover {
      transform: translateY(-1px);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
   }

   /* Responsive Gantt */
   @media (max-width: 768px) {
      .gantt-chart-wrapper {
         max-height: calc(100vh - 400px);
         min-height: 400px;
      }

      .gantt-toolbar {
         flex-direction: column;
         gap: 1rem;
      }

      .gantt-controls,
      .gantt-actions {
         width: 100%;
         justify-content: center;
      }
   }

   /* Gantt Loading State */
   .gantt-loading {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 500px;
      color: #6c757d;
   }

   /* Gantt Empty State */
   .gantt-empty {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 500px;
      color: #6c757d;
   }

   .gantt-empty i {
      font-size: 64px;
      opacity: 0.3;
      margin-bottom: 1rem;
   }

</style>

<!-- All JavaScript functionality moved to project_plan_manager.js -->

<?php
// var_dump($config);
// var_dump($projectPlanConfig['features']['taskManagement']);


   //  var_dump($projectData['timeline']);
;
// var_dump($projectPlanConfig['features']['phaseManagement']);
    // Include modal components
    if ($projectPlanConfig['features']['phaseManagement']) {
        echo Utility::form_modal_header("manage_project_phase", "projects/manage_project_phase.php", "Manage Project Phase edit", array('modal-lg', 'modal-dialog-centered'), $base);
            include 'modals/manage_project_phase.php';
        echo Utility::form_modal_footer("Save Phase", "managePhase", 'btn btn-primary btn-sm');
    }

    if ($projectPlanConfig['features']['taskManagement']) {
        echo Utility::form_modal_header("manage_project_task", "projects/manage_project_task.php", "Manage Project Task Modal", array('modal-lg', 'modal-dialog-centered'), $base);
            include 'modals/manage_project_task.php';
        echo Utility::form_modal_footer("Save Task", "manageTsk", 'btn btn-primary btn-sm');

      //   echo Utility::form_modal_header("addSubtaskModal", "projects/manage_sub_task.php", "Add Subtask", array('modal-lg', 'modal-dialog-centered'), $base);
      //       include 'modals/manage_sub_task.php';
      //   echo Utility::form_modal_footer("Save Subtask", "addSubtask", 'btn btn-primary btn-sm');

        // echo Utility::form_modal_header("add_task_with_list", "projects/add_task_with_list.php", "Add Task with List", array('modal-lg', 'modal-dialog-centered'), $base);
        //     include 'modals/add_task_with_list.php';
        // echo Utility::form_modal_footer("Save Task", "manageTsk", 'btn btn-primary btn-sm');
   }

    if ($projectPlanConfig['features']['subtaskManagement']) {
        echo Utility::form_modal_header("manage_task_step", "projects/manage_sub_task.php", "Manage Task Step", array('modal-lg', 'modal-dialog-centered'), $base);
            include 'modals/manage_task_step.php';
        echo Utility::form_modal_footer("Save Task Step", "manageTaskStep", 'btn btn-primary btn-sm');
    }

    if ($projectPlanConfig['features']['assigneeManagement']) {
        echo Utility::form_modal_header("manage_project_task_collaborations", "projects/manage_project_task_collaborations.php", "Manage Task Collaborations", array('modal-lg', 'modal-dialog-centered'), $base);
            include 'modals/manage_project_task_collaborations.php';
        echo Utility::form_modal_footer("Save Task", "manageTskCollaborations", 'btn btn-primary btn-sm');
      //   var_dump($teamMembers);
      //   var_dump($projectTeamRoles);
        echo Utility::form_modal_header("manage_project_task_assignments", "projects/manage_project_task_assignments.php", "Manage Task assignees", array('modal-lg', 'modal-dialog-centered'), $base);
            include 'modals/manage_project_task_assignments.php';
        echo Utility::form_modal_footer("Save Task", "manageTskAssignments", 'btn btn-primary btn-sm');
    }

?>

<!-- Apply Template to Project Modal -->
<div class="modal fade" id="applyTemplateModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri-file-copy-line me-2"></i>Apply Template to Project
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="ri-information-line me-2"></i>
                    <strong>Apply a project plan template</strong> to automatically create phases for this project.
                    Phases will be distributed across your project timeline.
                </div>

                <!-- Template Selection Tabs -->
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#systemTemplates" role="tab">
                            <i class="ri-star-line me-1"></i>System Templates
                            <span class="badge bg-primary ms-1" id="applySystemCount">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#orgTemplates" role="tab">
                            <i class="ri-building-line me-1"></i>Organization
                            <span class="badge bg-success ms-1" id="applyOrgCount">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#myTemplates" role="tab">
                            <i class="ri-user-line me-1"></i>My Templates
                            <span class="badge bg-secondary ms-1" id="applyMyCount">0</span>
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="systemTemplates" role="tabpanel">
                        <div class="row g-3" id="applySystemTemplatesContainer">
                            <div class="col-12 text-center py-4">
                                <i class="ri-loader-4-line spinner-border"></i>
                                <p class="text-muted mt-2">Loading templates...</p>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="orgTemplates" role="tabpanel">
                        <div class="row g-3" id="applyOrgTemplatesContainer">
                            <div class="col-12 text-center py-4">
                                <i class="ri-loader-4-line spinner-border"></i>
                                <p class="text-muted mt-2">Loading templates...</p>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="myTemplates" role="tabpanel">
                        <div class="row g-3" id="applyMyTemplatesContainer">
                            <div class="col-12 text-center py-4">
                                <i class="ri-loader-4-line spinner-border"></i>
                                <p class="text-muted mt-2">Loading templates...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Selected Template Preview -->
                <div id="selectedTemplatePreview" class="mt-4" style="display: none;">
                    <div class="card custom-card">
                        <div class="card-header bg-primary-transparent">
                            <h6 class="mb-0">
                                <i class="ri-eye-line me-2"></i>Template Preview
                            </h6>
                        </div>
                        <div class="card-body" id="templatePreviewContent">
                            <!-- Preview content will be inserted here -->
                        </div>
                    </div>
                </div>

                <!-- Application Options -->
                <div id="templateApplicationOptions" class="mt-4" style="display: none;">
                    <div class="card custom-card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="ri-settings-3-line me-2"></i>Application Options
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="templateApplyMode" id="replacePhases" value="replace" checked>
                                <label class="form-check-label" for="replacePhases">
                                    <strong>Replace existing phases</strong>
                                    <small class="text-muted d-block">Remove all current phases and apply template (recommended for empty projects)</small>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="templateApplyMode" id="appendPhases" value="append">
                                <label class="form-check-label" for="appendPhases">
                                    <strong>Append to existing phases</strong>
                                    <small class="text-muted d-block">Keep current phases and add template phases after them</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="applyTemplateBtn" disabled>
                    <i class="ri-checkbox-circle-line me-1"></i>Apply Template
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.template-selection-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.template-selection-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    border-color: rgba(13, 110, 253, 0.3);
}

.template-selection-card.selected {
    border-color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.05);
}

.template-selection-card .select-template-btn {
    transition: all 0.2s ease;
}

.template-selection-card:hover .select-template-btn {
    transform: scale(1.05);
}
</style>

<script>
(function() {
    'use strict';

    // Parse configuration from PHP (safer than direct output)
    const templateConfig = {
        projectID: <?= json_encode(isset($projectID) ? $projectID : '') ?>,
        orgDataID: <?= json_encode(isset($orgDataID) ? $orgDataID : '') ?>,
        entityID: <?= json_encode(isset($entityID) ? $entityID : '') ?>,
        projectStart: <?= json_encode(isset($projectData['timeline']['setProjectStartDate']) ? $projectData['timeline']['setProjectStartDate'] : '') ?>,
        projectEnd: <?= json_encode(isset($projectData['timeline']['setProjectEndDate']) ? $projectData['timeline']['setProjectEndDate'] : '') ?>,
        currentUserID: <?= json_encode(isset($userDetails->ID) ? $userDetails->ID : '') ?>,
        basePath: <?= json_encode(isset($base) ? $base : '../../../') ?>,
        siteURL: <?= json_encode(isset($config['siteURL']) ? $config['siteURL'] : '') ?>
    };

    // Get site URL from PHP config or calculate from window location
    let siteUrl = templateConfig.siteURL;
    console.log(siteUrl);
    console.log(templateConfig);

    // Fallback: calculate from window location if PHP config is empty
    if (!siteUrl) {
        // Get the base path from current URL
        const pathArray = window.location.pathname.split('/');
        // For localhost/demo-pms.tija.ke/html/... we want localhost/demo-pms.tija.ke/
        // Find the position of 'html' and take everything before it
        const htmlIndex = pathArray.indexOf('html');
        const basePath = htmlIndex > 0 ? pathArray.slice(0, htmlIndex).join('/') + '/' : '/';
        siteUrl = window.location.origin + basePath;
    }

    // Ensure siteUrl ends with /
    if (siteUrl && !siteUrl.endsWith('/')) {
        siteUrl += '/';
    }

    // Destructure config for easier access
    const {projectID, orgDataID, entityID, projectStart, projectEnd, currentUserID, basePath} = templateConfig;

    let selectedTemplateID = null;
    let templates = [];

    // Validate required variables
    if(!projectID) {
        console.error('Project ID not available', templateConfig);
    }
    if(!orgDataID) {
        console.error('Organization ID not available', templateConfig);
    }

    console.log('Template system initialized:', {
        ...templateConfig,
        siteUrl: siteUrl,
        calculatedFrom: templateConfig.siteURL ? 'PHP config' : 'window.location (fallback)'
    });

    // Load templates when modal opens
    const applyTemplateModal = document.getElementById('applyTemplateModal');
    if(applyTemplateModal) {
        applyTemplateModal.addEventListener('shown.bs.modal', function() {
            loadProjectTemplates();
        });
    }

    // Apply template button
    const applyTemplateBtn = document.getElementById('applyTemplateBtn');
    if(applyTemplateBtn) {
        applyTemplateBtn.addEventListener('click', function() {
            applyTemplateToProject();
        });
    }

    function loadProjectTemplates() {
        console.log('Loading templates for project plan...');
        console.log('Variables:', {projectID, orgDataID, entityID, siteUrl});

        if(!orgDataID || !siteUrl) {
            console.error('Missing required variables for template loading');
            showTemplateError('Configuration error. Please refresh the page.');
            return;
        }
        if (typeof siteUrl === 'string' && siteUrl.slice(-1) !== '/') {
            siteUrl = siteUrl + '/';
        }
        console.log('siteUrl:', siteUrl);
        const url = siteUrl + 'php/scripts/projects/get_project_plan_templates.php?orgDataID=' + orgDataID + '&entityID=' + entityID;
        console.log('url:', url);
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if(data.success && data.templates) {
                    console.log(`Loaded ${data.templates.length} templates`);
                    templates = data.templates;
                    renderProjectTemplates(templates);
                } else {
                    showTemplateError('No templates available. Create templates first.');
                }
            })
            .catch(error => {
                console.error('Error loading templates:', error);
                showTemplateError('Failed to load templates. Please try again.');
            });
    }

    function renderProjectTemplates(templates) {
        const systemTemplates = templates.filter(t => t.isSystemTemplate === 'Y');
        const orgTemplates = templates.filter(t => t.isSystemTemplate === 'N' && t.isPublic === 'Y');
        const myTemplates = templates.filter(t => t.isSystemTemplate === 'N' && t.isPublic === 'N' && t.createdByID == currentUserID);

        // Update counts
        document.getElementById('applySystemCount').textContent = systemTemplates.length;
        document.getElementById('applyOrgCount').textContent = orgTemplates.length;
        document.getElementById('applyMyCount').textContent = myTemplates.length;

        // Render template cards
        renderTemplateCards('applySystemTemplatesContainer', systemTemplates, 'primary');
        renderTemplateCards('applyOrgTemplatesContainer', orgTemplates, 'success');
        renderTemplateCards('applyMyTemplatesContainer', myTemplates, 'secondary');
    }

    function renderTemplateCards(containerId, templates, badgeColor) {
        const container = document.getElementById(containerId);
        if(!container) return;

        if(templates.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center py-4 text-muted">
                    <i class="ri-inbox-line" style="font-size: 36px; opacity: 0.3;"></i>
                    <p class="mt-2 mb-0">No templates in this category</p>
                </div>
            `;
            return;
        }

        let html = '';
        templates.forEach(template => {
            const categoryIcon = getCategoryIcon(template.templateCategory);

            html += `
                <div class="col-md-6 col-lg-4">
                    <div class="card custom-card template-selection-card h-100" data-template-id="${template.templateID}">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-${badgeColor}-transparent">
                                        <i class="${categoryIcon}"></i>
                                    </div>
                                    <h6 class="mb-0">${escapeHtml(template.templateName)}</h6>
                                </div>
                                ${template.isSystemTemplate === 'Y' ? '<span class="badge bg-primary-transparent">System</span>' : ''}
                            </div>

                            ${template.templateDescription ?
                                `<p class="text-muted small mb-2">${escapeHtml(template.templateDescription)}</p>` : ''}

                            <div class="mb-2">
                                <span class="badge bg-light text-dark">
                                    <i class="ri-list-check me-1"></i>${template.phaseCount || 0} phases
                                </span>
                                ${template.templateCategory ?
                                    `<span class="badge bg-light text-muted ms-1">${template.templateCategory}</span>` : ''}
                            </div>

                            ${template.usageCount > 0 ?
                                `<div class="small text-muted mb-2">
                                    <i class="ri-bar-chart-line me-1"></i>Used ${template.usageCount} times
                                </div>` : ''}

                            <div class="d-grid gap-2 mt-3">
                                <button type="button" class="btn btn-sm btn-primary select-template-btn" data-template-id="${template.templateID}">
                                    <i class="ri-checkbox-circle-line me-1"></i>Select This Template
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;

        // Attach event listeners
        container.querySelectorAll('.select-template-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                selectTemplate(this.dataset.templateId);
            });
        });

        container.querySelectorAll('.template-selection-card').forEach(card => {
            card.addEventListener('click', function(e) {
                if(!e.target.closest('button')) {
                    selectTemplate(this.dataset.templateId);
                }
            });
        });
    }

    function selectTemplate(templateID) {
        selectedTemplateID = templateID;

        // Highlight selected card
        document.querySelectorAll('.template-selection-card').forEach(card => {
            card.classList.remove('selected');
        });
        // Ensure siteUrl ends with a single slash
        if (typeof siteUrl === 'string' && siteUrl.slice(-1) !== '/') {
            siteUrl = siteUrl + '/';
        }
        document.querySelector(`.template-selection-card[data-template-id="${templateID}"]`)?.classList.add('selected');
        console.log('siteUrl:', siteUrl);
        if (typeof siteUrl === 'string' && siteUrl.slice(-1) !== '/') {
            siteUrl = siteUrl + '/';
        }
        // Load and show template preview
        const detailUrl = siteUrl + 'php/scripts/projects/get_project_plan_templates.php?templateID=' + templateID;
        console.log('detailUrl:', detailUrl);
        fetch(detailUrl)
            .then(response => response.json())
            .then(data => {
                if(data.success && data.template) {
                    showSelectedTemplatePreview(data.template);
                    document.getElementById('templateApplicationOptions').style.display = 'block';
                    document.getElementById('applyTemplateBtn').disabled = false;
                }
            })
            .catch(error => {
                console.error('Error loading template details:', error);
            });
    }

    function showSelectedTemplatePreview(template) {
        const previewDiv = document.getElementById('selectedTemplatePreview');
        const contentDiv = document.getElementById('templatePreviewContent');

        if(!template.phases || template.phases.length === 0) {
            contentDiv.innerHTML = '<p class="text-muted">This template has no phases defined.</p>';
            previewDiv.style.display = 'block';
            return;
        }

        let phasesHTML = '';
        template.phases.forEach((phase, index) => {
            phasesHTML += `
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 ${index < template.phases.length - 1 ? 'border-bottom' : ''}">
                    <div>
                        <strong class="text-primary">${index + 1}. ${escapeHtml(phase.phaseName)}</strong>
                        ${phase.phaseDescription ? `<p class="mb-0 text-muted small">${escapeHtml(phase.phaseDescription)}</p>` : ''}
                    </div>
                    ${phase.durationPercent ?
                        `<span class="badge bg-info">${phase.durationPercent}%</span>` :
                        `<span class="badge bg-light text-muted">${Math.round(100 / template.phases.length)}%</span>`}
                </div>
            `;
        });

        contentDiv.innerHTML = `
            <h6 class="mb-3">
                <i class="ri-list-check me-2"></i>${template.templateName}
                <span class="badge bg-primary ms-2">${template.phases.length} phases</span>
            </h6>
            ${phasesHTML}
            <div class="alert alert-secondary mt-3 mb-0">
                <i class="ri-calendar-line me-2"></i>
                Phases will be distributed from <strong>${projectStart || 'project start'}</strong> to <strong>${projectEnd || 'project end'}</strong>
            </div>
        `;

        previewDiv.style.display = 'block';
    }

    function applyTemplateToProject() {
        if(!selectedTemplateID) {
            alert('Please select a template first');
            return;
        }

        const applyMode = document.querySelector('input[name="templateApplyMode"]:checked')?.value || 'replace';

        // Confirm action
        const confirmMsg = applyMode === 'replace' ?
            'This will REPLACE all existing phases. Continue?' :
            'This will ADD template phases to your existing phases. Continue?';

        if(!confirm(confirmMsg)) return;

        // Show loading
        const applyBtn = document.getElementById('applyTemplateBtn');
        const originalText = applyBtn.innerHTML;
        applyBtn.innerHTML = '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i>Applying...';
        applyBtn.disabled = true;

        // Submit form
        const formData = new FormData();
        formData.append('action', 'applyTemplate');
        formData.append('projectID', projectID);
        formData.append('templateID', selectedTemplateID);
        formData.append('applyMode', applyMode);
        formData.append('projectStart', projectStart);
        formData.append('projectEnd', projectEnd);
        formData.append('orgDataID', orgDataID);
        formData.append('entityID', entityID);

        const applyUrl = siteUrl + 'php/scripts/projects/apply_template_to_project.php';

        fetch(applyUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // Close modal and reload page
                bootstrap.Modal.getInstance(document.getElementById('applyTemplateModal')).hide();

                // Show success message
                alert(`Template applied successfully! ${data.phasesCreated || 0} phases created.`);

                // Reload page to show new phases
                window.location.reload();
            } else {
                alert('Failed to apply template: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error applying template:', error);
            alert('Failed to apply template. Please try again.');
        })
        .finally(() => {
            applyBtn.innerHTML = originalText;
            applyBtn.disabled = false;
        });
    }

    function showTemplateError(message) {
        ['applySystemTemplatesContainer', 'applyOrgTemplatesContainer', 'applyMyTemplatesContainer'].forEach(id => {
            const container = document.getElementById(id);
            if(container) {
                container.innerHTML = `
                    <div class="col-12 text-center py-4">
                        <i class="ri-error-warning-line text-warning" style="font-size: 36px;"></i>
                        <p class="text-muted mt-2">${message}</p>
                    </div>
                `;
            }
        });
    }

    function getCategoryIcon(category) {
        const icons = {
            'software': 'ri-code-box-line',
            'construction': 'ri-hammer-line',
            'marketing': 'ri-megaphone-line',
            'research': 'ri-flask-line',
            'consulting': 'ri-briefcase-line',
            'design': 'ri-pencil-ruler-2-line',
            'other': 'ri-folder-line'
        };
        return icons[category] || 'ri-file-list-3-line';
    }

    function escapeHtml(text) {
        if(!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
})();
</script>

<!-- SortableJS Library for Drag and Drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<!-- Frappe Gantt Library -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.css">
<script src="https://cdn.jsdelivr.net/npm/frappe-gantt@0.6.1/dist/frappe-gantt.min.js"></script>

<!-- Kanban Board JavaScript -->
<script>
(function() {
    'use strict';

    // Project data from PHP
    const projectData = <?= json_encode($projectData) ?>;
    const siteUrl = <?= json_encode(isset($config['siteURL']) ? $config['siteURL'] : '') ?>;
    const currentUserID = <?= json_encode(isset($userDetails->ID) ? $userDetails->ID : '') ?>;

    let kanbanSortables = [];

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeViewToggle();
        initializeKanbanBoard();
    });

    // View Toggle Functionality
    function initializeViewToggle() {
        const viewToggleBtns = document.querySelectorAll('.view-toggle-btn');
        const listView = document.getElementById('projectPlanListView');
        const kanbanView = document.getElementById('projectPlanKanbanView');
        const ganttView = document.getElementById('projectPlanGanttView');

        viewToggleBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const view = this.dataset.view;

                // Update button states
                viewToggleBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                // Show/hide views
                if (view === 'list') {
                    listView.style.display = 'block';
                    kanbanView.style.display = 'none';
                    ganttView.style.display = 'none';
                    // Destroy kanban sortables when switching away
                    destroyKanbanSortables();
                } else if (view === 'kanban') {
                    listView.style.display = 'none';
                    kanbanView.style.display = 'block';
                    ganttView.style.display = 'none';
                    // Initialize kanban when switching to it
                    initializeKanbanBoard();
                } else if (view === 'gantt') {
                    listView.style.display = 'none';
                    kanbanView.style.display = 'none';
                    ganttView.style.display = 'block';
                    // Initialize Gantt chart when switching to it
                    initializeGanttChart();
                }

                // Save preference
                localStorage.setItem('projectPlanView', view);
            });
        });

        // Restore saved view preference
        const savedView = localStorage.getItem('projectPlanView') || 'list';
        const savedBtn = document.querySelector(`.view-toggle-btn[data-view="${savedView}"]`);
        if (savedBtn) {
            savedBtn.click();
        }
    }

    // Initialize Kanban Board
    function initializeKanbanBoard() {
        const kanbanBoard = document.getElementById('kanbanBoard');
        if (!kanbanBoard || !projectData.phases || projectData.phases.length === 0) {
            return;
        }

        // Clear existing content
        kanbanBoard.innerHTML = '';

        // Create kanban columns for each phase
        projectData.phases.forEach(phase => {
            const column = createKanbanColumn(phase);
            kanbanBoard.appendChild(column);
        });

        // Initialize SortableJS for each column
        initializeSortables();
    }

    // Create Kanban Column
    function createKanbanColumn(phase) {
        const column = document.createElement('div');
        column.className = 'kanban-column';
        column.dataset.phaseId = phase.id;
        column.id = `kanban-column-${phase.id}`;

        const taskCount = phase.tasks ? phase.tasks.length : 0;

        column.innerHTML = `
            <div class="kanban-column-header">
                <h5 class="kanban-column-title">${escapeHtml(phase.name)}</h5>
                <span class="kanban-column-count">${taskCount}</span>
            </div>
            <div class="kanban-column-content" id="kanban-column-content-${phase.id}">
                ${phase.tasks && phase.tasks.length > 0 ?
                    phase.tasks.map(task => createKanbanCard(task)).join('') :
                    '<div class="text-center text-muted py-4"><small>No tasks</small></div>'
                }
            </div>
        `;

        return column;
    }

    // Create Kanban Card
    function createKanbanCard(task) {
        const assignees = task.assignees || [];
        const subtasks = task.subtasks || [];
        const completedSubtasks = subtasks.filter(st => st.progress === 100).length;
        const progress = task.progress || 0;
        const deadline = task.deadline ? new Date(task.deadline).toLocaleDateString() : '';

        let assigneesHTML = '';
        if (assignees.length > 0) {
            assigneesHTML = assignees.slice(0, 3).map(assignee => {
                const initials = (assignee.assigneeInitials || assignee.name || 'U').substring(0, 2).toUpperCase();
                return `<div class="kanban-card-assignee-avatar" title="${escapeHtml(assignee.assigneeName || 'Unassigned')}">${initials}</div>`;
            }).join('');
            if (assignees.length > 3) {
                assigneesHTML += `<div class="kanban-card-assignee-avatar" style="background: #6c757d;">+${assignees.length - 3}</div>`;
            }
        }

        let subtasksHTML = '';
        if (subtasks.length > 0) {
            subtasksHTML = `
                <div class="kanban-card-subtasks">
                    ${subtasks.slice(0, 3).map(subtask => {
                        const completed = subtask.progress === 100;
                        return `
                            <div class="kanban-card-subtask ${completed ? 'completed' : ''}">
                                <i class="ri-${completed ? 'checkbox-circle' : 'checkbox-blank-circle'}-line"></i>
                                <span>${escapeHtml(subtask.name)}</span>
                            </div>
                        `;
                    }).join('')}
                    ${subtasks.length > 3 ? `<small class="text-muted">+${subtasks.length - 3} more</small>` : ''}
                </div>
            `;
        }

        return `
            <div class="kanban-card" data-task-id="${task.id}" data-phase-id="${task.projectPhaseId}">
                <div class="kanban-card-header">
                    <div class="flex-fill">
                        <h6 class="kanban-card-title">${escapeHtml(task.name)}</h6>
                        ${task.projectTaskCode ? `<div class="kanban-card-code">${escapeHtml(task.projectTaskCode)}</div>` : ''}
                    </div>
                </div>
                ${task.description ? `<div class="kanban-card-description">${escapeHtml(task.description)}</div>` : ''}
                <div class="kanban-card-meta">
                    ${deadline ? `<div class="kanban-card-meta-item"><i class="ri-calendar-line"></i> ${deadline}</div>` : ''}
                    ${task.hoursAllocated ? `<div class="kanban-card-meta-item"><i class="ri-time-line"></i> ${task.hoursAllocated}h</div>` : ''}
                    ${subtasks.length > 0 ? `<div class="kanban-card-meta-item"><i class="ri-list-check"></i> ${completedSubtasks}/${subtasks.length}</div>` : ''}
                </div>
                ${progress > 0 ? `
                    <div class="kanban-card-progress">
                        <div class="kanban-card-progress-bar">
                            <div class="kanban-card-progress-fill" style="width: ${progress}%"></div>
                        </div>
                        <small class="text-muted">${progress}%</small>
                    </div>
                ` : ''}
                ${subtasksHTML}
                ${assignees.length > 0 ? `
                    <div class="kanban-card-assignees">
                        ${assigneesHTML}
                    </div>
                ` : ''}
            </div>
        `;
    }

    // Initialize SortableJS for drag and drop
    function initializeSortables() {
        // Destroy existing sortables
        destroyKanbanSortables();

        // Get all kanban columns
        const columns = document.querySelectorAll('.kanban-column-content');

        columns.forEach(column => {
            const sortable = new Sortable(column, {
                group: 'kanban-tasks',
                animation: 150,
                ghostClass: 'kanban-card-dragging',
                chosenClass: 'kanban-card-dragging',
                dragClass: 'kanban-card-dragging',
                onMove: function(evt) {
                    // Add visual feedback when dragging over a column
                    const relatedColumn = evt.related.closest('.kanban-column');
                    if (relatedColumn) {
                        relatedColumn.classList.add('drag-over');
                    }
                },
                onEnd: function(evt) {
                    // Remove visual feedback
                    document.querySelectorAll('.kanban-column').forEach(col => {
                        col.classList.remove('drag-over');
                    });
                    handleTaskMove(evt);
                }
            });

            kanbanSortables.push(sortable);
        });
    }

    // Destroy all sortable instances
    function destroyKanbanSortables() {
        kanbanSortables.forEach(sortable => {
            if (sortable && sortable.destroy) {
                sortable.destroy();
            }
        });
        kanbanSortables = [];
    }

    // Handle task move between phases
    function handleTaskMove(evt) {
        const taskCard = evt.item;
        const taskID = taskCard.dataset.taskId;
        const oldPhaseID = taskCard.dataset.phaseId;
        const newPhaseColumn = evt.to.closest('.kanban-column');
        const newPhaseID = newPhaseColumn ? newPhaseColumn.dataset.phaseId : null;

        if (!taskID || !newPhaseID || oldPhaseID === newPhaseID) {
            // Revert the move if invalid
            if (evt.from && evt.to) {
                evt.from.appendChild(taskCard);
            }
            return;
        }

        // Update the task's phase ID in the DOM
        taskCard.dataset.phaseId = newPhaseID;

        // Update task count in column headers
        updateColumnCounts();

        // Show loading state
        taskCard.style.opacity = '0.5';
        taskCard.style.pointerEvents = 'none';

        // Send update to server
        updateTaskPhase(taskID, newPhaseID, oldPhaseID, taskCard);
    }

    // Update column task counts
    function updateColumnCounts() {
        document.querySelectorAll('.kanban-column').forEach(column => {
            const content = column.querySelector('.kanban-column-content');
            const count = content.querySelectorAll('.kanban-card').length;
            const countElement = column.querySelector('.kanban-column-count');
            if (countElement) {
                countElement.textContent = count;
            }
        });
    }

    // Update task phase on server
    function updateTaskPhase(taskID, newPhaseID, oldPhaseID, taskCard) {
        const formData = new FormData();
        formData.append('action', 'updateTaskPhase');
        formData.append('taskID', taskID);
        formData.append('newPhaseID', newPhaseID);
        formData.append('oldPhaseID', oldPhaseID);
        formData.append('projectID', projectData.project.id);

        fetch(siteUrl + 'php/scripts/projects/update_task_phase.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                if (typeof showToast === 'function') {
                    showToast('Task moved successfully', 'success');
                }

                // Restore card appearance
                taskCard.style.opacity = '1';
                taskCard.style.pointerEvents = 'auto';
            } else {
                // Revert the move on error
                const oldColumn = document.getElementById(`kanban-column-content-${oldPhaseID}`);
                if (oldColumn) {
                    oldColumn.appendChild(taskCard);
                    taskCard.dataset.phaseId = oldPhaseID;
                    updateColumnCounts();
                }

                // Show error message
                if (typeof showToast === 'function') {
                    showToast(data.message || 'Failed to move task', 'error');
                } else {
                    alert(data.message || 'Failed to move task');
                }

                // Restore card appearance
                taskCard.style.opacity = '1';
                taskCard.style.pointerEvents = 'auto';
            }
        })
        .catch(error => {
            console.error('Error updating task phase:', error);

            // Revert the move on error
            const oldColumn = document.getElementById(`kanban-column-content-${oldPhaseID}`);
            if (oldColumn) {
                oldColumn.appendChild(taskCard);
                taskCard.dataset.phaseId = oldPhaseID;
                updateColumnCounts();
            }

            // Show error message
            if (typeof showToast === 'function') {
                showToast('Failed to move task. Please try again.', 'error');
            } else {
                alert('Failed to move task. Please try again.');
            }

            // Restore card appearance
            taskCard.style.opacity = '1';
            taskCard.style.pointerEvents = 'auto';
        });
    }

    // Escape HTML helper
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ========================================================================
    // GANTT CHART IMPLEMENTATION
    // ========================================================================
    let ganttChart = null;
    let ganttTasks = [];
    let currentViewMode = 'Week';
    let ganttZoomLevel = 1;

    // Initialize Gantt Chart
    function initializeGanttChart() {
        const ganttContainer = document.getElementById('ganttChart');
        if (!ganttContainer || typeof Gantt === 'undefined') {
            console.error('Gantt library not loaded or container not found');
            return;
        }

        // Transform project data to Gantt format
        ganttTasks = transformProjectDataToGantt(projectData);

        if (ganttTasks.length === 0) {
            ganttContainer.innerHTML = '<div class="text-center py-5 text-muted"><i class="ri-inbox-line" style="font-size: 48px;"></i><p class="mt-3">No phases or tasks to display in Gantt chart</p></div>';
            return;
        }

        // Clear container
        ganttContainer.innerHTML = '';

        // Calculate date range
        const dateRange = calculateGanttDateRange(ganttTasks);

        // Initialize Gantt chart
        ganttChart = new Gantt(ganttContainer, ganttTasks, {
            view_mode: currentViewMode,
            language: 'en',
            header_height: 50,
            column_width: 30,
            step: 24,
            bar_height: 30,
            bar_corner_radius: 3,
            arrow_curve: 5,
            padding: 18,
            date_format: 'YYYY-MM-DD',
            custom_popup_html: generateGanttPopup
        });

        // Apply custom classes to bars after initialization
        setTimeout(() => {
            applyGanttBarColors();
        }, 200);

        // Attach event listeners after initialization
        // Frappe Gantt handles interactions internally, we'll monitor for changes
        if (ganttChart) {
            // Listen for clicks on bars
            setTimeout(() => {
                ganttContainer.querySelectorAll('.bar-wrapper').forEach(barWrapper => {
                    barWrapper.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const taskId = barWrapper.getAttribute('data-id') ||
                                     barWrapper.querySelector('.bar')?.getAttribute('data-id');
                        if (taskId) {
                            const task = ganttTasks.find(t => t.id === taskId);
                            if (task) {
                                handleGanttClick(task);
                            }
                        }
                    });
                });
            }, 100);
        }

        // Setup Gantt controls
        setupGanttControls();

        // Setup context menu
        setupGanttContextMenu();

        // Monitor for changes
        setTimeout(() => {
            monitorGanttChanges();
        }, 500);
    }

    // Transform project data to Gantt format
    function transformProjectDataToGantt(projectData) {
        const tasks = [];
        let taskIndex = 0;

        if (!projectData.phases || projectData.phases.length === 0) {
            return tasks;
        }

        projectData.phases.forEach((phase, phaseIndex) => {
            // Add phase as parent task
            const phaseStartDate = phase.startDate ? new Date(phase.startDate) : new Date();
            const phaseEndDate = phase.endDate ? new Date(phase.endDate) : new Date();

            // Ensure valid dates
            if (isNaN(phaseStartDate.getTime()) || isNaN(phaseEndDate.getTime())) {
                return; // Skip invalid phases
            }

            const phaseTask = {
                id: `phase-${phase.id}`,
                name: phase.name || `Phase ${phaseIndex + 1}`,
                start: formatDateForGantt(phaseStartDate),
                end: formatDateForGantt(phaseEndDate),
                progress: phase.progress || 0,
                type: 'project',
                custom_class: 'gantt-phase',
                metadata: {
                    type: 'phase',
                    id: phase.id,
                    projectId: projectData.project.id
                }
            };

            // Ensure phase bars get the correct class
            phaseTask.class = 'gantt-phase';

            tasks.push(phaseTask);

            // Add tasks as children of phases
            if (phase.tasks && phase.tasks.length > 0) {
                phase.tasks.forEach((task, taskIdx) => {
                    const taskStartDate = task.startDate ? new Date(task.startDate) : phaseStartDate;
                    const taskDeadline = task.deadline ? new Date(task.deadline) : phaseEndDate;

                    // Ensure valid dates
                    if (isNaN(taskStartDate.getTime()) || isNaN(taskDeadline.getTime())) {
                        return; // Skip invalid tasks
                    }

                    const ganttTask = {
                        id: `task-${task.id}`,
                        name: task.name || `Task ${taskIdx + 1}`,
                        start: formatDateForGantt(taskStartDate),
                        end: formatDateForGantt(taskDeadline),
                        progress: task.progress || 0,
                        type: 'task',
                        parent: phaseTask.id,
                        custom_class: 'gantt-task',
                        metadata: {
                            type: 'task',
                            id: task.id,
                            phaseId: phase.id,
                            projectId: projectData.project.id,
                            code: task.projectTaskCode || '',
                            assignees: task.assignees || []
                        }
                    };

                    // Ensure task bars get the correct class
                    ganttTask.class = 'gantt-task';

                    tasks.push(ganttTask);

                    // Add subtasks as children of tasks
                    if (task.subtasks && task.subtasks.length > 0) {
                        task.subtasks.forEach((subtask, subtaskIdx) => {
                            const subtaskDueDate = subtask.dueDate ? new Date(subtask.dueDate) : taskDeadline;

                            if (isNaN(subtaskDueDate.getTime())) {
                                return; // Skip invalid subtasks
                            }

                            // Calculate subtask start date (same day as due date for subtasks)
                            const subtaskStartDate = new Date(subtaskDueDate);
                            subtaskStartDate.setDate(subtaskStartDate.getDate() - 1);

                            const ganttSubtask = {
                                id: `subtask-${subtask.id}`,
                                name: subtask.name || `Subtask ${subtaskIdx + 1}`,
                                start: formatDateForGantt(subtaskStartDate),
                                end: formatDateForGantt(subtaskDueDate),
                                progress: subtask.progress || 0,
                                type: 'milestone',
                                parent: ganttTask.id,
                                custom_class: 'gantt-subtask',
                                metadata: {
                                    type: 'subtask',
                                    id: subtask.id,
                                    taskId: task.id,
                                    phaseId: phase.id,
                                    projectId: projectData.project.id
                                }
                            };

                            // Ensure subtask bars get the correct class
                            ganttSubtask.class = 'gantt-subtask';

                            tasks.push(ganttSubtask);
                        });
                    }
                });
            }
        });

        return tasks;
    }

    // Format date for Gantt (YYYY-MM-DD)
    function formatDateForGantt(date) {
        if (!date || isNaN(date.getTime())) {
            return new Date().toISOString().split('T')[0];
        }
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // Calculate date range for Gantt chart
    function calculateGanttDateRange(tasks) {
        if (tasks.length === 0) {
            const today = new Date();
            return {
                start: today,
                end: new Date(today.getTime() + 30 * 24 * 60 * 60 * 1000) // 30 days from now
            };
        }

        let minDate = null;
        let maxDate = null;

        tasks.forEach(task => {
            const start = new Date(task.start);
            const end = new Date(task.end);

            if (!minDate || start < minDate) {
                minDate = start;
            }
            if (!maxDate || end > maxDate) {
                maxDate = end;
            }
        });

        // Add padding
        minDate.setDate(minDate.getDate() - 7);
        maxDate.setDate(maxDate.getDate() + 7);

        return { start: minDate, end: maxDate };
    }

    // Setup Gantt controls
    function setupGanttControls() {
        // Zoom In
        const zoomInBtn = document.getElementById('ganttZoomIn');
        if (zoomInBtn) {
            zoomInBtn.addEventListener('click', () => {
                if (ganttChart) {
                    ganttZoomLevel = Math.min(ganttZoomLevel + 0.1, 2);
                    ganttChart.change_view_mode(currentViewMode);
                    updateGanttZoom();
                }
            });
        }

        // Zoom Out
        const zoomOutBtn = document.getElementById('ganttZoomOut');
        if (zoomOutBtn) {
            zoomOutBtn.addEventListener('click', () => {
                if (ganttChart) {
                    ganttZoomLevel = Math.max(ganttZoomLevel - 0.1, 0.5);
                    ganttChart.change_view_mode(currentViewMode);
                    updateGanttZoom();
                }
            });
        }

        // Fit to Screen
        const fitToScreenBtn = document.getElementById('ganttFitToScreen');
        if (fitToScreenBtn) {
            fitToScreenBtn.addEventListener('click', () => {
                if (ganttChart) {
                    ganttZoomLevel = 1;
                    updateGanttZoom();
                    ganttChart.refresh();
                }
            });
        }

        // View Mode Dropdown
        const viewModeItems = document.querySelectorAll('[data-view-mode]');
        viewModeItems.forEach(item => {
            // Set active state for default Week view
            if (item.dataset.viewMode === 'Week') {
                item.classList.add('active');
            }

            item.addEventListener('click', (e) => {
                e.preventDefault();
                const mode = item.dataset.viewMode;
                currentViewMode = mode;
                if (ganttChart) {
                    ganttChart.change_view_mode(mode);
                }
                // Update dropdown button text
                const dropdownBtn = item.closest('.dropdown').querySelector('button');
                if (dropdownBtn) {
                    dropdownBtn.innerHTML = `<i class="ri-calendar-line me-1"></i>${mode}`;
                }
                // Update active state
                viewModeItems.forEach(i => i.classList.remove('active'));
                item.classList.add('active');
            });
        });

        // Add Phase button
        const addPhaseBtn = document.getElementById('ganttAddPhase');
        if (addPhaseBtn) {
            addPhaseBtn.addEventListener('click', () => {
                const addPhaseBtnElement = document.querySelector('.addPhaseBtn');
                if (addPhaseBtnElement) {
                    addPhaseBtnElement.click();
                }
            });
        }

        // Add Task button
        const addTaskBtn = document.getElementById('ganttAddTask');
        if (addTaskBtn) {
            addTaskBtn.addEventListener('click', () => {
                // Open task modal - you may need to adjust this based on your modal trigger
                const event = new Event('click');
                document.querySelector('[data-bs-target="#manage_project_task"]')?.dispatchEvent(event);
            });
        }
    }

    // Update Gantt zoom
    function updateGanttZoom() {
        if (!ganttChart) return;
        const container = document.getElementById('ganttChart');
        if (container) {
            container.style.transform = `scale(${ganttZoomLevel})`;
            container.style.transformOrigin = 'top left';
        }
    }

    // Handle Gantt click
    function handleGanttClick(task) {
        console.log('Gantt task clicked:', task);
        // Could open edit modal here
    }

    // Handle Gantt date change (drag and drop)
    // This will be called when the chart detects a date change
    function handleGanttDateChange(task, start, end) {
        console.log('Gantt date changed:', { task, start, end });

        const metadata = task.metadata || {};
        const taskType = metadata.type;
        const taskId = metadata.id;

        if (!taskId) {
            console.error('Task ID not found in metadata');
            return;
        }

        // Format dates for API
        const startDate = typeof start === 'string' ? start : start.toISOString().split('T')[0];
        const endDate = typeof end === 'string' ? end : end.toISOString().split('T')[0];

        // Update task dates on server
        updateGanttDates(taskType, taskId, startDate, endDate, metadata);
    }

    // Handle Gantt progress change
    function handleGanttProgressChange(task, progress) {
        console.log('Gantt progress changed:', { task, progress });

        const metadata = task.metadata || {};
        const taskType = metadata.type;
        const taskId = metadata.id;

        if (!taskId) {
            console.error('Task ID not found in metadata');
            return;
        }

        // Update progress on server
        updateGanttProgress(taskType, taskId, progress, metadata);
    }

    // Handle Gantt view change
    function handleGanttViewChange(mode) {
        currentViewMode = mode;
        console.log('Gantt view changed to:', mode);
    }

    // Apply custom colors to Gantt bars based on their type
    function applyGanttBarColors() {
        const ganttContainer = document.getElementById('ganttChart');
        if (!ganttContainer) return;

        ganttTasks.forEach(task => {
            // Try multiple selectors to find the bar wrapper
            let barWrapper = ganttContainer.querySelector(`[data-id="${task.id}"]`);
            if (!barWrapper) {
                // Try finding by task name or other attributes
                const allBars = ganttContainer.querySelectorAll('.bar-wrapper');
                allBars.forEach(bw => {
                    const bar = bw.querySelector('.bar');
                    if (bar && bar.getAttribute('data-id') === task.id) {
                        barWrapper = bw;
                    }
                });
            }

            if (barWrapper) {
                const bar = barWrapper.querySelector('.bar');
                if (bar) {
                    // Remove existing classes
                    barWrapper.classList.remove('gantt-phase', 'gantt-task', 'gantt-subtask');
                    bar.classList.remove('gantt-phase', 'gantt-task', 'gantt-subtask');

                    // Determine task type from metadata or task structure
                    let taskType = null;
                    if (task.metadata && task.metadata.type) {
                        taskType = task.metadata.type;
                    } else if (task.id.startsWith('phase-')) {
                        taskType = 'phase';
                    } else if (task.id.startsWith('task-')) {
                        taskType = 'task';
                    } else if (task.id.startsWith('subtask-')) {
                        taskType = 'subtask';
                    }

                    // Add appropriate class based on task type
                    if (taskType === 'phase') {
                        barWrapper.classList.add('gantt-phase');
                        bar.classList.add('gantt-phase');
                        // Directly set SVG fill and stroke
                        bar.setAttribute('fill', '#667eea');
                        bar.setAttribute('stroke', '#5568d3');
                        bar.setAttribute('stroke-width', '2.5');
                    } else if (taskType === 'task') {
                        barWrapper.classList.add('gantt-task');
                        bar.classList.add('gantt-task');
                        bar.setAttribute('fill', '#0d6efd');
                        bar.setAttribute('stroke', '#0a58ca');
                        bar.setAttribute('stroke-width', '2');
                    } else if (taskType === 'subtask') {
                        barWrapper.classList.add('gantt-subtask');
                        bar.classList.add('gantt-subtask');
                        bar.setAttribute('fill', '#20c997');
                        bar.setAttribute('stroke', '#1aa179');
                        bar.setAttribute('stroke-width', '1.5');
                    }
                }
            }
        });
    }

    // Monitor Gantt chart for changes
    function monitorGanttChanges() {
        if (!ganttChart) return;

        // Use MutationObserver to detect changes in the Gantt chart
        const ganttContainer = document.getElementById('ganttChart');
        if (!ganttContainer) return;

        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'data-start') {
                    const barWrapper = mutation.target;
                    const taskId = barWrapper.getAttribute('data-id');
                    if (taskId) {
                        const task = ganttTasks.find(t => t.id === taskId);
                        if (task) {
                            const newStart = barWrapper.getAttribute('data-start');
                            const newEnd = barWrapper.getAttribute('data-end');
                            if (newStart && newEnd && (newStart !== task.start || newEnd !== task.end)) {
                                task.start = newStart;
                                task.end = newEnd;
                                handleGanttDateChange(task, newStart, newEnd);
                            }
                        }
                    }
                }

                // Reapply colors when new bars are added
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    setTimeout(() => {
                        applyGanttBarColors();
                    }, 100);
                }
            });
        });

        observer.observe(ganttContainer, {
            attributes: true,
            attributeFilter: ['data-start', 'data-end'],
            subtree: true,
            childList: true
        });
    }

    // Generate custom popup HTML for Gantt tasks
    function generateGanttPopup(task) {
        const metadata = task.metadata || {};
        const progress = task.progress || 0;
        const startDate = new Date(task._start).toLocaleDateString();
        const endDate = new Date(task._end).toLocaleDateString();

        let html = `
            <div class="gantt-popup">
                <div class="gantt-popup-header">
                    <strong>${escapeHtml(task.name)}</strong>
                </div>
                <div class="gantt-popup-body">
                    <div class="gantt-popup-item">
                        <span class="gantt-popup-label">Start:</span>
                        <span class="gantt-popup-value">${startDate}</span>
                    </div>
                    <div class="gantt-popup-item">
                        <span class="gantt-popup-label">End:</span>
                        <span class="gantt-popup-value">${endDate}</span>
                    </div>
                    <div class="gantt-popup-item">
                        <span class="gantt-popup-label">Progress:</span>
                        <span class="gantt-popup-value">${progress}%</span>
                    </div>
        `;

        if (metadata.code) {
            html += `
                    <div class="gantt-popup-item">
                        <span class="gantt-popup-label">Code:</span>
                        <span class="gantt-popup-value">${escapeHtml(metadata.code)}</span>
                    </div>
            `;
        }

        if (metadata.assignees && metadata.assignees.length > 0) {
            html += `
                    <div class="gantt-popup-item">
                        <span class="gantt-popup-label">Assignees:</span>
                        <span class="gantt-popup-value">${metadata.assignees.map(a => escapeHtml(a.assigneeName || 'Unassigned')).join(', ')}</span>
                    </div>
            `;
        }

        html += `
                </div>
            </div>
        `;

        return html;
    }

    // Setup context menu for Gantt
    function setupGanttContextMenu() {
        const ganttContainer = document.getElementById('ganttChart');
        if (!ganttContainer) return;

        ganttContainer.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            // Context menu implementation can be added here
        });
    }

    // Update Gantt dates on server
    function updateGanttDates(taskType, taskId, startDate, endDate, metadata) {
        const formData = new FormData();
        formData.append('action', 'updateGanttDates');
        formData.append('taskType', taskType);
        formData.append('taskId', taskId);
        formData.append('startDate', startDate);
        formData.append('endDate', endDate);
        formData.append('projectId', metadata.projectId || projectData.project.id);

        if (taskType === 'task') {
            formData.append('phaseId', metadata.phaseId);
        }

        fetch(siteUrl + 'php/scripts/projects/update_gantt_dates.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (typeof showToast === 'function') {
                    showToast('Dates updated successfully', 'success');
                }
                // Refresh Gantt chart
                if (ganttChart) {
                    ganttChart.refresh();
                }
            } else {
                if (typeof showToast === 'function') {
                    showToast(data.message || 'Failed to update dates', 'error');
                } else {
                    alert(data.message || 'Failed to update dates');
                }
                // Reload Gantt to revert changes
                initializeGanttChart();
            }
        })
        .catch(error => {
            console.error('Error updating Gantt dates:', error);
            if (typeof showToast === 'function') {
                showToast('Failed to update dates. Please try again.', 'error');
            } else {
                alert('Failed to update dates. Please try again.');
            }
            // Reload Gantt to revert changes
            initializeGanttChart();
        });
    }

    // Update Gantt progress on server
    function updateGanttProgress(taskType, taskId, progress, metadata) {
        const formData = new FormData();
        formData.append('action', 'updateGanttProgress');
        formData.append('taskType', taskType);
        formData.append('taskId', taskId);
        formData.append('progress', progress);
        formData.append('projectId', metadata.projectId || projectData.project.id);

        if (taskType === 'task') {
            formData.append('phaseId', metadata.phaseId);
        }

        fetch(siteUrl + 'php/scripts/projects/update_gantt_progress.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (typeof showToast === 'function') {
                    showToast('Progress updated successfully', 'success');
                }
            } else {
                if (typeof showToast === 'function') {
                    showToast(data.message || 'Failed to update progress', 'error');
                } else {
                    alert(data.message || 'Failed to update progress');
                }
                // Reload Gantt to revert changes
                initializeGanttChart();
            }
        })
        .catch(error => {
            console.error('Error updating Gantt progress:', error);
            if (typeof showToast === 'function') {
                showToast('Failed to update progress. Please try again.', 'error');
            } else {
                alert('Failed to update progress. Please try again.');
            }
            // Reload Gantt to revert changes
            initializeGanttChart();
        });
    }
})();
</script>
