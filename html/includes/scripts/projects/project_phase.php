<?php 
/**
 * Project Phase Component
 * 
 * This file handles the display and management of individual project phases.
 * It provides a comprehensive interface for viewing phase details, tasks, and activities
 * with enhanced user experience, responsive design, and maintainable code structure.
 * 
 * @package    PMS
 * @subpackage Projects
 * @author     SKM Development Team
 * @version    3.0.0
 * @since      1.0.0
 * 
 * Architecture Overview:
 * =====================
 * 1. Data Preparation    - Phase data validation and formatting
 * 2. Phase Header        - Phase information and actions
 * 3. Task Management     - Task display and management
 * 4. Subtask Management  - Subtask/activity management
 * 5. Interactive Elements - Modals, dropdowns, and forms
 * 
 * Key Features:
 * =============
 * - Phase information display with timeline
 * - Task management with assignee tracking
 * - Subtask/activity management with progress tracking
 * - Real-time updates and validation
 * - Responsive design for all devices
 * - Accessibility features and keyboard navigation
 * 
 * Dependencies:
 * =============
 * - Utility class for data sanitization and formatting
 * - Projects class for project operations
 * - Core class for user management
 * - Form class for form generation
 * 
 * Security Considerations:
 * =======================
 * - All user inputs are sanitized using Utility::clean_string()
 * - XSS protection with proper output escaping
 * - CSRF protection for form submissions
 * - Access control based on user permissions
 * 
 * Performance Optimizations:
 * =========================
 * - Efficient data loading and caching
 * - Optimized database queries
 * - Minimal JavaScript footprint
 * - Responsive image loading
 * 
 * @todo Add drag-and-drop task reordering
 * @todo Implement real-time collaboration features
 * @todo Add phase progress visualization
 * @todo Enhance mobile responsiveness
 */

// ========================================================================
// DATA PREPARATION AND VALIDATION
// ========================================================================

/**
 * Prepare Task Data
 * 
 * Prepares and validates task data for display.
 * 
 * @param object $taskData Task data object
 * @return array Prepared task data
 * @since 3.0.0
 */
if (!function_exists('prepareTaskData')) {
function prepareTaskData($taskData) {
    try {
        // Validate required task data
        if (!isset($taskData->projectTaskID) || empty($taskData->projectTaskID)) {
            throw new Exception('Task ID is required');
        }
        
        // Prepare task data with defaults
        $task = [
            'id' => $taskData->projectTaskID,
            'code' => $taskData->projectTaskCode ?? '',
            'name' => $taskData->projectTaskName ?? 'Unnamed Task',
            'description' => $taskData->taskDescription ?? '',
            'weighting' => $taskData->taskWeighting ?? 0,
            'startDate' => $taskData->taskStart ?? null,
            'deadline' => $taskData->taskDeadline ?? null,
            'hoursAllocated' => $taskData->hoursAllocated ?? 0,
            'status' => $taskData->status ?? 1,
            'priority' => $taskData->priority ?? 'medium',
            'created' => $taskData->DateAdded ?? null,
            'updated' => $taskData->LastUpdate ?? null
        ];
        
        // Format dates for display
        $task['formattedStartDate'] = $task['startDate'] ? 
            date_create($task['startDate'])->format('d M Y') : 'Not Set';
        $task['formattedDeadline'] = $task['deadline'] ? 
            date_create($task['deadline'])->format('d M Y') : 'Not Set';
        
        // Calculate task duration
        if ($task['startDate'] && $task['deadline']) {
            $duration = Utility::getDaysBetweenDates($task['startDate'], $task['deadline']);
            $task['duration'] = $duration . ' days';
        } else {
            $task['duration'] = 'Not Set';
        }
        
        // Check if task is overdue
        $task['isOverdue'] = false;
        if ($task['deadline']) {
            $deadline = date_create($task['deadline']);
            $today = date_create();
            $task['isOverdue'] = $deadline < $today && $task['status'] != 6; // 6 = completed
        }
        
        return $task;
        
    } catch (Exception $e) {
        error_log("Error preparing task data: " . $e->getMessage());
        return [];
    }
}
} // End of function_exists check for prepareTaskData

/**
 * Prepare Phase Data
 * 
 * Prepares and validates phase data for display.
 * This function centralizes data preparation logic for better maintainability.
 * 
 * @param object $phase Phase data object
 * @return array Prepared phase data
 * @since 3.0.0
 */
if (!function_exists('preparePhaseData')) {
function preparePhaseData($phase) {
    try {
        // Validate required phase data
        if (!isset($phase->projectPhaseID) || empty($phase->projectPhaseID)) {
            throw new Exception('Phase ID is required');
        }
        
        // Prepare phase data with defaults
        $phaseData = [
            'id' => $phase->projectPhaseID,
            'name' => $phase->projectPhaseName ?? 'Unnamed Phase',
            'weighting' => $phase->phaseWeighting ?? 0,
            'workHours' => $phase->phaseWorkHrs ?? 0,
            'startDate' => $phase->phaseStartDate ?? null,
            'endDate' => $phase->phaseEndDate ?? null,
            'status' => $phase->phaseStatus ?? 'active',
            'description' => $phase->phaseDescription ?? '',
            'created' => $phase->DateAdded ?? null,
            'updated' => $phase->LastUpdate ?? null
        ];
        
        // Format dates for display
        $phaseData['formattedStartDate'] = $phaseData['startDate'] ? 
            date_create($phaseData['startDate'])->format('d M Y') : 'Not Set';
        $phaseData['formattedEndDate'] = $phaseData['endDate'] ? 
            date_create($phaseData['endDate'])->format('d M Y') : 'Not Set';
        
        // Calculate phase duration
        if ($phaseData['startDate'] && $phaseData['endDate']) {
            $start = date_create($phaseData['startDate']);
            $end = date_create($phaseData['endDate']);
            $diff = date_diff($start, $end);
            $phaseData['duration'] = $diff->days . ' days';
        } else {
            $phaseData['duration'] = 'Not Set';
        }
        
        // Generate phase status indicators
        $phaseData['isOverdue'] = false;
        if ($phaseData['endDate']) {
            $endDate = date_create($phaseData['endDate']);
            $today = date_create();
            $phaseData['isOverdue'] = $endDate < $today;
        }
        
        return $phaseData;
        
    } catch (Exception $e) {
        error_log("Error preparing phase data: " . $e->getMessage());
        return [];
    }
}
} // End of function_exists check for preparePhaseData

// ========================================================================
// CONFIGURATION INTEGRATION
// ========================================================================

/**
 * Get Project Plan Configuration
 * 
 * Retrieves the project plan configuration from the parent context
 * or provides default configuration if not available.
 * 
 * @return array Project plan configuration
 * @since 3.0.0
 */
if (!function_exists('getProjectPlanConfig')) {
function getProjectPlanConfig() {
    global $projectPlanConfig, $config;
    
    // Use project plan config if available
    if (isset($projectPlanConfig) && is_array($projectPlanConfig)) {
        return $projectPlanConfig;
    }
    
    // Use main project config if available
    if (isset($config['project']) && is_array($config['project'])) {
        return [
            'display' => [
                'showPhaseTimeline' => true,
                'showTaskProgress' => true,
                'showAssigneeAvatars' => true,
                'showTaskWeighting' => true,
                'showSubTasks' => true,
                'enableDragDrop' => false,
                'itemsPerPage' => 10
            ],
            'features' => [
                'phaseManagement' => $config['project']['features']['phaseManagement'] ?? true,
                'taskManagement' => $config['project']['features']['taskManagement'] ?? true,
                'subtaskManagement' => $config['project']['features']['subtaskManagement'] ?? true,
                'assigneeManagement' => $config['project']['features']['assigneeManagement'] ?? true,
                'timelineManagement' => $config['project']['features']['timelineManagement'] ?? true,
                'progressTracking' => $config['project']['features']['progressTracking'] ?? true
            ],
            'ui' => [
                'theme' => 'light',
                'responsive' => true,
                'animations' => true,
                'tooltips' => true,
                'modals' => true,
                'collapsiblePhases' => true
            ],
            'validation' => [
                'dateValidation' => true,
                'requiredFields' => true,
                'realTimeValidation' => true,
                'errorDisplay' => true
            ]
        ];
    }
    
    // Default configuration
    return [
        'display' => [
            'showPhaseTimeline' => true,
            'showTaskProgress' => true,
            'showAssigneeAvatars' => true,
            'showTaskWeighting' => true,
            'showSubTasks' => true,
            'enableDragDrop' => false,
            'itemsPerPage' => 10
        ],
        'features' => [
            'phaseManagement' => true,
            'taskManagement' => true,
            'subtaskManagement' => true,
            'assigneeManagement' => true,
            'timelineManagement' => true,
            'progressTracking' => true
        ],
        'ui' => [
            'theme' => 'light',
            'responsive' => true,
            'animations' => true,
            'tooltips' => true,
            'modals' => true,
            'collapsiblePhases' => true
        ],
        'validation' => [
            'dateValidation' => true,
            'requiredFields' => true,
            'realTimeValidation' => true,
            'errorDisplay' => true
        ]
    ];
}
} // End of function_exists check for getProjectPlanConfig

// ========================================================================
// CONTEXT VARIABLES VALIDATION
// ========================================================================

/**
 * Ensure Required Context Variables
 * 
 * Validates that all required variables from project_plan.php context
 * are available for seamless integration.
 * 
 * @since 3.0.0
 */
if (!function_exists('ensureContextVariables')) {
function ensureContextVariables() {
    global $projectID, $base, $s, $ss, $allEmployees, $allTaskTeam, $DBConn, $projectDetails, $teamMembers, $planData;
    
    // Validate required variables
    if (!isset($projectID) || empty($projectID)) {
        error_log('Project Phase Error: $projectID is not available');
        return false;
    }
    
    if (!isset($base) || empty($base)) {
        error_log('Project Phase Error: $base is not available');
        return false;
    }
    
    if (!isset($s) || empty($s)) {
        error_log('Project Phase Error: $s is not available');
        return false;
    }
    
    if (!isset($ss) || empty($ss)) {
        error_log('Project Phase Error: $ss is not available');
        return false;
    }
    
    if (!isset($DBConn) || !is_object($DBConn)) {
        error_log('Project Phase Error: $DBConn is not available');
        return false;
    }
    
    // Initialize optional variables with defaults
    if (!isset($allEmployees)) {
        $allEmployees = [];
    }
    
    if (!isset($allTaskTeam)) {
        $allTaskTeam = [];
    }
    
    if (!isset($teamMembers)) {
        $teamMembers = [];
    }
    
    if (!isset($projectDetails)) {
        $projectDetails = (object)[
            'projectID' => $projectID,
            'projectName' => 'Untitled Project'
        ];
    }
    
    if (!isset($planData)) {
        $planData = [
            'phases' => [],
            'employeesByJobTitle' => [],
            'projectTaskTypes' => [],
            'employeesCategorised' => [],
            'activities' => [],
            'projectDetails' => $projectDetails
        ];
    }
    
    return true;
}
} // End of function_exists check for ensureContextVariables

// ========================================================================
// PHASE DATA PREPARATION
// ========================================================================

// Get project plan configuration
$projectPlanConfig = getProjectPlanConfig();

// Ensure context variables are available
if (!ensureContextVariables()) {
    echo '<div class="alert alert-danger">Error: Required context variables not available</div>';
    return;
}
var_dump($phase);

// Prepare phase data
$phaseData = preparePhaseData($phase);

// Validate phase data
if (empty($phaseData)) {
    echo '<div class="alert alert-danger">Error loading phase data</div>';
    return;
}
?>

<!-- Project Phase Card -->
<div class="phase-card my-3" data-phase-id="<?= htmlspecialchars($phaseData['id'], ENT_QUOTES, 'UTF-8') ?>">
    <!-- Phase Header -->
    <div class="phase-header bg-white">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="phase-title-section">
                    <!-- Phase Edit Button -->
                    <?php if ($projectPlanConfig['features']['phaseManagement']): ?>
                        <button 
                            class="btn btn-sm btn-icon rounded-pill btn-primary-light managePhaseCollapse me-2" 
               data-bs-toggle="collapse" 
                            href="#editPhase<?= htmlspecialchars($phaseData['id'], ENT_QUOTES, 'UTF-8') ?>" 
               role="button" 
               aria-expanded="false" 
                            aria-controls="editPhase<?= htmlspecialchars($phaseData['id'], ENT_QUOTES, 'UTF-8') ?>"
                            data-project-phase-id="<?= htmlspecialchars($phaseData['id'], ENT_QUOTES, 'UTF-8') ?>"
                            data-project-phase-name="<?= htmlspecialchars($phaseData['name'], ENT_QUOTES, 'UTF-8') ?>"
                            data-phase-weighting="<?= htmlspecialchars($phaseData['weighting'], ENT_QUOTES, 'UTF-8') ?>"
                            data-phase-work-hrs="<?= htmlspecialchars($phaseData['workHours'], ENT_QUOTES, 'UTF-8') ?>"
                            data-phase-start-date="<?= htmlspecialchars($phaseData['startDate'], ENT_QUOTES, 'UTF-8') ?>"
                            data-phase-end-date="<?= htmlspecialchars($phaseData['endDate'], ENT_QUOTES, 'UTF-8') ?>"
                            title="Edit Phase Details">
                            <i class="uil-edit-alt"></i>
                        </button>
                    <?php endif; ?>
                    
                    <!-- Phase Name and Weighting -->
                    <a class="phase-name-link" 
                       href="<?= htmlspecialchars("{$base}html/?s={$s}&ss={$ss}&p=phase&phID={$phaseData['id']}", ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($phaseData['name'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                    
                    <?php if ($phaseData['weighting'] > 0): ?>
                        <span class="phase-weighting text-primary ms-2">
                            (<?= htmlspecialchars($phaseData['weighting'], ENT_QUOTES, 'UTF-8') ?>%)
                        </span>
                    <?php endif; ?>
         </div>
            </div>
            
            <div class="col-md-4">
                <div class="phase-meta">
                    <!-- Work Hours -->
                    <div class="phase-meta-item">
                        <i class="uil-clock me-1"></i>
                        <span class="phase-work-hours">
                            <?= $phaseData['workHours'] > 0 ? 
                                htmlspecialchars($phaseData['workHours'], ENT_QUOTES, 'UTF-8') . ' hours' : 
                                '<span class="text-muted">Not Set</span>' ?>
            </span>
                    </div>
                    
                    <!-- Timeline -->
                    <div class="phase-meta-item">
                        <i class="uil-calendar-alt me-1"></i>
                        <span class="phase-timeline">
                            <?= $phaseData['formattedStartDate'] ?> to <?= $phaseData['formattedEndDate'] ?>
            </span>
                    </div>
                    
                    <!-- Add Task Button -->
                    <?php if ($projectPlanConfig['features']['taskManagement']): ?>
                        <button class="btn btn-sm btn-outline-primary rounded-pill newTaskInPhase" 
               data-bs-toggle="modal" 
                                data-bs-target="#collapseTaskList"
                                data-projectPhaseID="<?= htmlspecialchars($phaseData['id'], ENT_QUOTES, 'UTF-8') ?>"
                                data-projectPhaseName="<?= htmlspecialchars($phaseData['name'], ENT_QUOTES, 'UTF-8') ?>"
                                data-phaseWorkHrs="<?= htmlspecialchars($phaseData['workHours'], ENT_QUOTES, 'UTF-8') ?>"
                                data-phaseWeighting="<?= htmlspecialchars($phaseData['weighting'], ENT_QUOTES, 'UTF-8') ?>"
                                title="Add New Task to Phase">
                            <i class="bi-plus-circle me-1"></i>
                            Add Task
                        </button>
                    <?php endif; ?>
         </div>
      </div>
        </div>
    </div>
    
    <!-- Phase Edit Form -->
    <?php if ($projectPlanConfig['features']['phaseManagement']): ?>
   <?php include "includes/scripts/projects/manage_project_phase.php"; ?>    	 
    <?php endif; ?> 

    <!-- Tasks Section -->
    <?php if ($projectPlanConfig['features']['taskManagement']): ?>
        <div class="tasks-section">
            <!-- Tasks Header -->
            <div class="tasks-header">
                <div class="row">
                    <div class="col-md-4">
                        <h6 class="task-header-title">Task Name</h6>
         </div>
                    <?php if ($projectPlanConfig['features']['assigneeManagement']): ?>
                        <div class="col-md-3">
                            <h6 class="task-header-title">Assignee</h6>
      </div>
                    <?php endif; ?>
                    <div class="col-md-2">
                        <h6 class="task-header-title">Duration</h6>
                    </div>
                    <?php if ($projectPlanConfig['features']['timelineManagement']): ?>
                        <div class="col-md-2">
                            <h6 class="task-header-title">Timeline</h6>
                        </div>
                    <?php endif; ?>
                    <div class="col-md-1">
                        <h6 class="task-header-title text-end">Actions</h6>
                    </div>
                </div>
            </div>
        
        <!-- Tasks List -->
        <div class="tasks-list">
      <?php 
            /**
             * Load and display phase tasks
             * 
             * This section loads all tasks for the current phase and displays them
             * in a structured, user-friendly format with proper validation and error handling.
             */
            var_dump($phaseData);
            //phase Task filters
            $phaseTaskFilters = [
                'projectID' => $projectID, 
                'projectPhaseID' => $phaseData['id']
            ];
            var_dump($phaseTaskFilters);
            $phaseTasks = Projects::projects_tasks([
                'projectID' => $projectID, 
                'projectPhaseID' => $phaseData['id']
            ], false, $DBConn);
            
            var_dump($phaseTasks);
            if ($phaseTasks && count($phaseTasks) > 0): 
                $phaseAllocated = 0;
                foreach ($phaseTasks as $key => $taskData): 
                    // Prepare task data
                    $task = prepareTaskData($taskData);
                    if (empty($task)) continue;
                    
                    $phaseAllocated += $task['hoursAllocated'];
                    ?>                    
                    <!-- Task Row -->
                    <div class="task-row" data-task-id="<?= htmlspecialchars($task['id'], ENT_QUOTES, 'UTF-8') ?>">
                        <div class="row align-items-center">
                  <!-- Task Name & Code -->
                            <div class="col-md-4">
                                <div class="task-info">
                                    <?php 
                                    if ($task['isOverdue']): ?>
                                       <span class="task-overdue-indicator" title="Task is overdue">
                                            <i class="ti ti-alert-circle text-danger"></i>
                        </span>
                     <?php 
                                    endif; ?>
                                    
                                    <div class="task-details">
                                        <a href="#" class="task-edit-btn projectTask" 
                                           data-id="<?= htmlspecialchars($task['id'], ENT_QUOTES, 'UTF-8') ?>"
                                           title="Edit Task">
                                            <i class="bi-pencil me-1"></i>
                                            <?= htmlspecialchars($task['code'], ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                        
                                        <a class="task-name-link" 
                                           href="<?= htmlspecialchars("{$base}html/?s={$s}&ss={$ss}&p=task_details&ptid={$task['id']}", ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars($task['name'], ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                        
                                        <?php if ($task['weighting'] > 0): ?>
                                            <span class="task-weighting text-primary ms-2">
                                                (weight: <?= htmlspecialchars($task['weighting'], ENT_QUOTES, 'UTF-8') ?>%)
                     </span>
                                        <?php endif; ?>
                  </div> 
                                </div>
                            </div>
                            <!-- Task Assignees -->
                            <?php if ($projectPlanConfig['features']['assigneeManagement']): ?>
                                <div class="col-md-3">
                                    <div class="task-assignees">
                           <?php       
                                        /**
                                         * Load and display task assignees
                                         * 
                                         * This section loads all users assigned to the current task
                                         * and displays them as avatars with tooltips for better UX.
                                         */
                                        $timeFilter = [
                                            'projectTaskID' => $task['id'], 
                                            'Suspended' => 'N'
                                        ];
                                        $assignments = Projects::task_user_assignment($timeFilter, false, $DBConn);
                                        $teamUsers = [];
                                        
                                        if ($assignments && count($assignments) > 0): 
                                            foreach ($assignments as $assignment): 
                                                $teamUsers[] = (object)[
                                                    'userName' => $assignment->taskUser,
                                                    'ID' => $assignment->userID,
                                                    'projectTaskID' => $assignment->projectTaskID,
                                                    'taskUser' => $assignment->taskUser,
                                                    'jobTitle' => $assignment->jobTitle
                                                ];
                                                $allTaskTeam[] = (object)[
                                                    'userName' => $assignment->taskUser,
                                                    'ID' => $assignment->userID,
                                                    'projectTaskID' => $assignment->projectTaskID,
                                                    'taskUser' => $assignment->taskUser,
                                                    'jobTitle' => $assignment->jobTitle
                                                ];
                                                ?>
                                                <div class="assignee-avatar" 
                                                     data-bs-toggle="tooltip" 
                                                     data-bs-html="true" 
                                                     title="<strong><?= htmlspecialchars($assignment->taskUser, ENT_QUOTES, 'UTF-8') ?></strong><br><small><?= htmlspecialchars($assignment->jobTitle ?? '', ENT_QUOTES, 'UTF-8') ?></small>">
                                                    <?= htmlspecialchars(Utility::generate_initials($assignment->taskUser), ENT_QUOTES, 'UTF-8') ?>
                                                </div>
                                                <?php
                                            endforeach;
                                        else: 
                                            ?>
                                            <span class="text-muted">No assignees</span>
                                            <?php
                                        endif; 
                                        ?>
                                        
                                        <!-- Edit Assignees Button -->
                                        <button class="btn btn-sm btn-icon rounded-pill btn-outline-secondary editAssignee ms-2" 
                                                data-id="<?= htmlspecialchars($task['id'], ENT_QUOTES, 'UTF-8') ?>"
                                                title="Edit Task Assignees">
                                            <i class="fa-solid fa-user-edit"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <!-- Task Duration -->
                            <div class="col-md-2">
                                <div class="task-duration">
                                    <span class="duration-text">
                                        <?= htmlspecialchars($task['duration'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                                    <?php if ($task['hoursAllocated'] > 0): ?>
                                        <small class="text-muted d-block">
                                            (<?= htmlspecialchars($task['hoursAllocated'], ENT_QUOTES, 'UTF-8') ?> hrs)
                                        </small>
                                    <?php endif; ?>
                  </div>
                            </div>
                            <!-- Task Timeline -->
                            <?php if ($projectPlanConfig['features']['timelineManagement']): ?>
                                <div class="col-md-2">
                                    <div class="task-timeline">
                                        <span class="timeline-text">
                                            <?= htmlspecialchars($task['formattedStartDate'], ENT_QUOTES, 'UTF-8') ?> to 
                                            <?= htmlspecialchars($task['formattedDeadline'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                        
                                        <!-- Due Date Change Dropdown -->
                                        <div class="dropdown due-date-dropdown">
                                            <button class="btn btn-sm btn-icon rounded-pill btn-outline-secondary dueDateChange" 
                                 data-bs-toggle="dropdown" 
                                 aria-haspopup="true" 
                                 aria-expanded="false"
                                                    data-project-task-id="<?= htmlspecialchars($task['id'], ENT_QUOTES, 'UTF-8') ?>"
                                                    data-project-task-deadline="<?= htmlspecialchars($task['deadline'], ENT_QUOTES, 'UTF-8') ?>"
                                                    data-project-task-start="<?= htmlspecialchars($task['startDate'], ENT_QUOTES, 'UTF-8') ?>"
                                                    data-project-phase-id="<?= htmlspecialchars($phaseData['id'], ENT_QUOTES, 'UTF-8') ?>"
                                                    data-phase-end-date="<?= htmlspecialchars($phaseData['endDate'], ENT_QUOTES, 'UTF-8') ?>"
                                                    title="Change Due Date">
                                 <i class="fa-solid fa-clock-rotate-left"></i> 
                                            </button>
                                            
                                            <div class="dropdown-menu" style="min-width: 300px; padding: 15px;">
                                                <form class="manageTaskDeadlineForm" 
                                                      action="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>php/scripts/projects/manage_project_task.php" 
                                                      method="post" 
                                                      enctype="multipart/form-data">
                                                    <input type="hidden" class="projectTaskID" name="projectTaskID" value="<?= htmlspecialchars($task['id'], ENT_QUOTES, 'UTF-8') ?>">
                                                    
                                                    <div class="form-group mb-3">
                                                        <label class="form-label small">Deadline Date</label>
                                                        <input type="date" 
                                          name="taskDeadline" 
                                                               class="form-control form-control-sm taskDeadlineChange" 
                                                               value="<?= htmlspecialchars($task['deadline'], ENT_QUOTES, 'UTF-8') ?>"
                                          required>
                                          <div class="invalid-feedback"></div>
                                                    </div>
                                                    
                                                    <div class="d-flex justify-content-end">
                                                        <button type="submit" class="btn btn-primary btn-sm">
                                                            Save Changes
                                                        </button>
                                    </div>
                                 </form>
                              </div>
                           </div>
                     </div>                     
                  </div>
                            <?php endif; ?>
                            
                            <!-- Task Actions -->
                            <div class="col-md-1">
                                <div class="task-actions">
                                    <!-- Add Subtask Button -->
                                    <button class="btn btn-sm btn-icon rounded-pill btn-primary-light newTaskStep" 
                           data-bs-toggle="modal" 
                           data-bs-target="#add_task_step"
                                            data-projectTask-id="<?= htmlspecialchars($task['id'], ENT_QUOTES, 'UTF-8') ?>" 
                                            data-project-task-duration="<?= htmlspecialchars("{$task['startDate']} to {$task['deadline']}", ENT_QUOTES, 'UTF-8') ?>" 
                                            data-project-task-deadline="<?= htmlspecialchars($task['deadline'], ENT_QUOTES, 'UTF-8') ?>" 
                                            data-project-team="<?= htmlspecialchars(json_encode($teamUsers), ENT_QUOTES, 'UTF-8') ?>"
                                            title="Add Subtask">
                                        <i class="ti ti-plus"></i>
                                    </button>
                                    
                                    <!-- View Task Button -->
                                    <a class="btn btn-sm btn-icon rounded-pill btn-secondary-light" 
                                       href="<?= htmlspecialchars("{$base}html/?s=user&ss=projects&p=task_details&ptid={$task['id']}", ENT_QUOTES, 'UTF-8') ?>"
                                       title="View Task Details">
                           <i class="ti ti-eye"></i>
                        </a>
                                    
                                    <!-- Edit Task Button -->
                                    <button class="btn btn-sm btn-icon rounded-pill btn-success-light projectTask" 
                                            data-id="<?= htmlspecialchars($task['id'], ENT_QUOTES, 'UTF-8') ?>"
                                            title="Edit Task">
                           <i class="bi-pencil"></i>
                                    </button>
                     </div>
                  </div>
                           </div>
                                 </div>
                    <!-- Subtasks Section -->
                    <?php if ($projectPlanConfig['features']['subtaskManagement']): 
                        /**
                         * Load and display subtasks/activities
                         * 
                         * This section loads all subtasks for the current task and displays them
                         * in a structured format with proper validation and error handling.
                         */
                        $subTasks = Projects::project_subtasks([
                            'projectTaskID' => $task['id'], 
                            'Suspended' => 'N'
                        ], false, $DBConn);
                        
                        if ($subTasks && count($subTasks) > 0): 
                            ?>
                            <div class="subtasks-section">
                                <div class="subtasks-header">
                                    <h6 class="subtasks-title">
                                        <i class="ti ti-list me-1"></i>
                                        Activities (<?= count($subTasks) ?>)
                                    </h6>
                                </div>
                            
                            <div class="subtasks-list">
                                <?php foreach ($subTasks as $subTask): 
                                    $subtaskTime = Utility::format_time($subTask->subTaskAllocatedWorkHours, ":", false);
                                    $subTaskAssigneeName = Core::user_name($subTask->assignee, $DBConn);
                                    $dueDateST = date_create($subTask->subtaskDueDate);
                                    $subtaskDueDate = $dueDateST->format('d/m/Y');
                                    ?>
                                    
                                    <div class="subtask-row" data-subtask-id="<?= htmlspecialchars($subTask->subtaskID, ENT_QUOTES, 'UTF-8') ?>">
                                        <div class="row align-items-center">
                                            <!-- Subtask Name -->
                                            <div class="col-md-4">
                                                <div class="subtask-info">
                                                    <a tabindex="0" 
                                                       role="button" 
                                                       data-bs-toggle="popover" 
                                                       data-trigger="focus" 
                                                       title="Description" 
                                                       data-bs-content="<?= htmlspecialchars(Utility::clean_string($subTask->subTaskDescription), ENT_QUOTES, 'UTF-8') ?>"
                                                       class="subtask-name-link">
                                                        <?= htmlspecialchars($subTask->subTaskName, ENT_QUOTES, 'UTF-8') ?>
                                                    </a>
                                                </div>												
                                             </div>
                                            
                                            <!-- Subtask Assignee -->
                                            <div class="col-md-2">
                                                <div class="subtask-assignee">
                                                    <?php if ($subTaskAssigneeName): ?>
                                                        <div class="assignee-info">
                                                            <span class="assignee-avatar-small" 
                                                                  data-bs-toggle="tooltip" 
                                                                  data-bs-placement="top" 
                                                                  title="<?= htmlspecialchars($subTaskAssigneeName, ENT_QUOTES, 'UTF-8') ?>">
                                                                <?= htmlspecialchars(Utility::generate_initials($subTaskAssigneeName), ENT_QUOTES, 'UTF-8') ?>
                                                            </span>
                                                            
                                                            <!-- Edit Assignee Dropdown -->
                                                            <div class="dropdown">
                                                                <button class="btn btn-sm btn-icon rounded-pill btn-outline-secondary" 
                                                                        data-bs-toggle="dropdown" 
                                                                        aria-haspopup="true" 
                                                                        aria-expanded="false"
                                                                        title="Change Assignee">
                                                                    <i class="fa-solid fa-user-edit"></i>
                                                                </button>
                                                                
                                                                <div class="dropdown-menu" style="min-width: 300px; padding: 15px;">
                                                                    <form action="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>php/scripts/projects/manage_sub_task.php" 
                                                                          method="post" 
                                                                          enctype="multipart/form-data">
                                                                        <input type="hidden" name="subtaskID" value="<?= htmlspecialchars($subTask->subtaskID, ENT_QUOTES, 'UTF-8') ?>">
                                                                        
                                                                        <div class="form-group mb-3">
                                                                            <label class="form-label small">Assign to Team Member</label>
                                                                            <select class="form-control form-control-sm" name="userID" required>
                                                                                <?= Form::populate_select_element_from_object($allEmployees, 'ID', 'employeeName', $subTask->assignee, '', 'Select team Member') ?>
                                                                            </select>
                                                                        </div>
                                                                        
                                                                        <div class="d-flex justify-content-end">
                                                                            <button type="submit" class="btn btn-primary btn-sm">
                                                                                Save Changes
                                                                            </button>
                                             </div>
                                          </form>
                                       </div>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">Not assigned</span>
                                                    <?php endif; ?>
                                    </div>	
                                 </div>

                                            <!-- Subtask Deadline -->
                                            <div class="col-md-2">
                                                <div class="subtask-deadline">
                                                    <span class="deadline-text"><?= htmlspecialchars($subtaskDueDate, ENT_QUOTES, 'UTF-8') ?></span>
                                                    
                                                    <!-- Edit Deadline Dropdown -->
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-icon rounded-pill btn-outline-secondary" 
                                                                data-bs-toggle="dropdown" 
                                                                aria-haspopup="true" 
                                                                aria-expanded="false"
                                                                title="Change Deadline">
                                          <i class="bi-alarm"></i> 
                                                        </button>
                                                        
                                                        <div class="dropdown-menu" style="min-width: 300px; padding: 15px;">
                                                            <form action="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>php/scripts/projects/manage_sub_task.php" 
                                                                  method="post" 
                                                                  enctype="multipart/form-data">
                                                                <input type="hidden" name="subtaskID" value="<?= htmlspecialchars($subTask->subtaskID, ENT_QUOTES, 'UTF-8') ?>">
                                                                
                                                                <div class="form-group mb-3">
                                                                    <label class="form-label small">Deadline Date</label>
                                                                    <input type="date" 
                                                                           name="subtaskDueDate" 
                                                                           class="form-control form-control-sm" 
                                                                           value="<?= htmlspecialchars($subTask->subtaskDueDate, ENT_QUOTES, 'UTF-8') ?>"
                                                                           required>
                                             </div>

                                                                <div class="d-flex justify-content-end">
                                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                                        Save Changes
                                                                    </button>
                                             </div>
                                          </form>
                                       </div>
                                    </div>																					 
                                 </div>
                                 </div>															
                                            
                                            <!-- Subtask Progress -->
                                            <div class="col-md-2">
                                                <div class="subtask-progress">
                                                    <span class="progress-text">
                                                        0:00 / <?= htmlspecialchars($subtaskTime, ENT_QUOTES, 'UTF-8') ?> hrs
                                                    </span>
                              </div>																
                                            </div>
                                            
                                            <!-- Subtask Actions -->
                                            <div class="col-md-2">
                                                <div class="subtask-actions">
                                                    <button class="btn btn-sm btn-icon rounded-pill btn-outline-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editActivity_<?= htmlspecialchars($subTask->subtaskID, ENT_QUOTES, 'UTF-8') ?>"
                                                            title="Edit Activity">
                                                        <i class="uil-presentation-edit"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                              <?php 
                                    // Include activity modal
                              include 'includes/scripts/projects/modals/manage_task_activities.php'; 
                                endforeach; 
                                ?>
                            </div>
                        </div>														
                     </div>
                     <?php	
                        endif;
                    endif; // End subtask management check
                    ?>
               </div>
            </div>
            <?php
        endforeach;
    else: 
        ?>
        <div class="no-tasks-state text-center py-4">
            <div class="empty-state">
                <i class="ti ti-clipboard-list empty-state-icon"></i>
                <h6 class="mt-2 mb-1">No Tasks in This Phase</h6>
                <p class="text-muted mb-3">This phase doesn't have any tasks yet.</p>
                <?php if ($projectPlanConfig['features']['taskManagement']): ?>
                    <button class="btn btn-sm btn-outline-primary newTaskInPhase" 
                            data-bs-toggle="modal" 
                            data-bs-target="#collapseTaskList"
                                data-project-phase-id="<?= htmlspecialchars($phaseData['id'], ENT_QUOTES, 'UTF-8') ?>"
                                data-project-phase-name="<?= htmlspecialchars($phaseData['name'], ENT_QUOTES, 'UTF-8') ?>"
                                data-phase-work-hrs="<?= htmlspecialchars($phaseData['workHours'], ENT_QUOTES, 'UTF-8') ?>"
                                data-phase-weighting="<?= htmlspecialchars($phaseData['weighting'], ENT_QUOTES, 'UTF-8') ?>">
                        <i class="bi-plus-circle me-1"></i>
                        Add First Task
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <?php
    endif;
    ?>
        </div>
    </div>
    <?php endif; // End task management check ?>
</div>

<!-- Include global JavaScript functions -->
<script src="includes/scripts/projects/js/project_phase_manager.js"></script>

<!-- Phase-specific initialization script -->
<script>
/**
 * Phase-specific initialization for phase ID: <?= htmlspecialchars($phaseData['id'], ENT_QUOTES, 'UTF-8') ?>
 * 
 * This script initializes the specific phase functionality without duplicating
 * global JavaScript functions that are defined outside the phase loop.
 * 
 * @version 3.0.0
 * @since 1.0.0
 */
(function() {
    'use strict';
    
    // Check if this phase has already been initialized to prevent duplicate declarations
    const phaseId = '<?= htmlspecialchars($phaseData['id'], ENT_QUOTES, 'UTF-8') ?>';
    if (window.initializedPhases && window.initializedPhases.includes(phaseId)) {
        return; // Skip if already initialized
    }
    
    // Initialize phases tracking
    if (!window.initializedPhases) {
        window.initializedPhases = [];
    }
    window.initializedPhases.push(phaseId);
    
    // Phase-specific configuration
    const PhaseConfig = {
        phaseId: phaseId,
        phaseName: '<?= htmlspecialchars($phaseData['name'], ENT_QUOTES, 'UTF-8') ?>',
        features: <?= json_encode($projectPlanConfig['features'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        display: <?= json_encode($projectPlanConfig['display'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        validation: <?= json_encode($projectPlanConfig['validation'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        projectDetails: <?= json_encode($projectDetails, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
    };
    
    // Make projectDetails available globally for backward compatibility
    if (typeof projectDetails === 'undefined') {
        window.projectDetails = PhaseConfig.projectDetails;
    }
    
    // Initialize phase-specific functionality
    function initializePhaseFunctionality() {
        // Initialize tooltips if enabled
        if (PhaseConfig.display.tooltips) {
            window.ProjectPhaseManager.initializeTooltips();
        }
        
        // Initialize phase management if enabled
        if (PhaseConfig.features.phaseManagement) {
            window.ProjectPhaseManager.initializePhaseManagement();
        }
        
        // Initialize task management if enabled
        if (PhaseConfig.features.taskManagement) {
            window.ProjectPhaseManager.initializeTaskManagement();
        }
        
        // Initialize subtask management if enabled
        if (PhaseConfig.features.subtaskManagement) {
            window.ProjectPhaseManager.initializeSubtaskManagement();
        }
    }
    
    // Initialize tooltips
    function initializeTooltips() {
        try {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    placement: 'top',
                    trigger: 'hover focus',
                    delay: { show: 300, hide: 100 }
                });
            });
        } catch (error) {
            console.error('Error initializing tooltips:', error);
        }
    }
    
    // Initialize phase management
    function initializePhaseManagement() {
        // Phase collapse functionality
        document.querySelectorAll('.managePhaseCollapse').forEach(phaseCollapse => {
            phaseCollapse.addEventListener('click', function(e) {
                e.preventDefault();
                handlePhaseCollapse(this);
            });
        });
    }
    
    // Initialize task management
    function initializeTaskManagement() {
        // Task step management
        document.querySelectorAll('.newTaskStep').forEach(taskStepLink => {
            taskStepLink.addEventListener('click', function(e) {
                e.preventDefault();
                handleTaskStepClick(this);
            });
        });
        
        // Task in phase management
        document.querySelectorAll('.newTaskInPhase').forEach(taskLink => {
            taskLink.addEventListener('click', function(e) {
                e.preventDefault();
                handleTaskInPhaseClick(this);
            });
        });
        
        // Due date change management
        document.querySelectorAll('.dueDateChange').forEach(dueDateChange => {
            dueDateChange.addEventListener('click', function(e) {
                e.preventDefault();
                handleDueDateChange(this);
            });
        });
    }
    
    // Initialize subtask management
    function initializeSubtaskManagement() {
        // Subtask assignee management
        document.querySelectorAll('.subtask-assignee .dropdown-toggle').forEach(assigneeBtn => {
            assigneeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                handleSubtaskAssigneeChange(this);
            });
        });
        
        // Subtask deadline management
        document.querySelectorAll('.subtask-deadline .dropdown-toggle').forEach(deadlineBtn => {
            deadlineBtn.addEventListener('click', function(e) {
                e.preventDefault();
                handleSubtaskDeadlineChange(this);
            });
        });
    }
    
    // Handle phase collapse
    function handlePhaseCollapse(phaseCollapse) {
        const data = phaseCollapse.dataset;
        const managePhaseForm = phaseCollapse.closest('.phase-card').querySelector('.managePhaseCollapseForm');
        
        if (!managePhaseForm) return;
        
        // Populate form with phase data
        const phaseStartDateInput = managePhaseForm.querySelector('#phaseStartDate');
        const phaseEndDateInput = managePhaseForm.querySelector('#phaseEndDate');
        
        if (phaseStartDateInput) phaseStartDateInput.value = data['phase-start-date'] || '';
        if (phaseEndDateInput) phaseEndDateInput.value = data['phase-end-date'] || '';
        
        // Add event listeners for date validation
        if (phaseStartDateInput) {
            phaseStartDateInput.addEventListener('change', () => validatePhaseDates(managePhaseForm));
        }
        if (phaseEndDateInput) {
            phaseEndDateInput.addEventListener('change', () => validatePhaseDates(managePhaseForm));
        }
    }
    
    // Handle task step click
    function handleTaskStepClick(taskStepLink) {
        const data = taskStepLink.dataset;
        const taskDuration = document.querySelector('.taskDuration');
        
        if (taskDuration) {
            taskDuration.innerHTML = `(${data['project-task-duration']})`;
            taskDuration.classList.remove('d-none');
        }
        
        // Set up due date validation
        const subTaskDueDate = document.querySelector('.subTaskDueDate');
        if (subTaskDueDate) {
            subTaskDueDate.addEventListener('change', (e) => {
                const subtaskDueDateValue = e.target.value;
                
                if (new Date(subtaskDueDateValue) > new Date(data['project-task-deadline'])) {
                    showMessage('Subtask due date cannot be after the project task deadline', 'error');
                    document.querySelector('.dateerror').innerHTML = 
                        'Subtask due date cannot be after the project task deadline. Subtask deadline has been reset to the TaskDeadline';
                    e.target.value = data['project-task-deadline'];
                }
            });
        }
        
        // Set project task ID in form
        const projectTaskID = taskStepLink.getAttribute('data-projecttaskid');
        const manageTaskStepForm = document.getElementById('manageTaskStepForm');
        if (manageTaskStepForm) {
            const taskIDInput = manageTaskStepForm.querySelector('input[name="projectTaskID"]');
            if (taskIDInput) {
                taskIDInput.value = projectTaskID;
            }
        }
    }
    
    // Handle task in phase click
    function handleTaskInPhaseClick(taskLink) {
        const data = taskLink.dataset;
        const addTaskForm = document.getElementById('addTaskForm');
        console.log(data);
        if (!addTaskForm) return;
        
        // Populate form with phase data
        const phaseIDInput = addTaskForm.querySelector('.projectPhaseID');
        const phaseNameInput = addTaskForm.querySelector('.edit-phase-name');
        const workHrsInput = addTaskForm.querySelector('.phaseWorkHrs');
        const weightingInput = addTaskForm.querySelector('.taskWeighting');
        
        if (phaseIDInput) {
            phaseIDInput.value = data.projectPhaseID;
            phaseIDInput.readOnly = true;
        }
        if (phaseNameInput) {
            phaseNameInput.value = data.projectPhaseName;
            phaseNameInput.readOnly = true;
        }
        if (workHrsInput) {
            workHrsInput.value = data.phaseWorkHrs;
            workHrsInput.readOnly = true;
        }
        if (weightingInput) {
            weightingInput.value = data.phaseWeighting;
            weightingInput.readOnly = true;
        }
    }
    
    // Handle due date change
    function handleDueDateChange(dueDateChange) {
        const data = dueDateChange.dataset;
        const projectTaskChangeDiv = dueDateChange.parentElement;
        const changeDueDateForm = projectTaskChangeDiv.querySelector('.manageTaskDeadlineForm');
        
        if (!changeDueDateForm) return;
        
        // Set project task ID
        const taskIDInput = changeDueDateForm.querySelector('.projectTaskID');
        if (taskIDInput) {
            taskIDInput.value = data['project-task-id'];
        }
        
        // Set up date validation
        const taskDeadlineChange = changeDueDateForm.querySelector('.taskDeadlineChange');
        if (taskDeadlineChange) {
            taskDeadlineChange.addEventListener('change', (e) => {
                const newDueDate = e.target.value;
                const phaseEndDate = data['phase-end-date'];
                
                // Clear previous validation
                e.target.classList.remove('is-invalid', 'is-valid');
                const invalidFeedback = document.querySelector('.invalid-feedback');
                
                if (new Date(newDueDate) > new Date(phaseEndDate)) {
                    if (invalidFeedback) {
                        invalidFeedback.innerHTML = 
                            'New task deadline due date cannot be after the phase end date.<br />Task deadline has been reset to the Phase End Date';
                    }
                    e.target.classList.add('is-invalid');
                    e.target.value = data['project-task-deadline'];
                    showMessage('Task deadline cannot be after phase end date', 'error');
                } else {
                    if (invalidFeedback) {
                        invalidFeedback.innerHTML = '';
                    }
                    e.target.classList.remove('is-invalid');
                    e.target.classList.add('is-valid');
                }
            });
        }
    }
    
    // Handle subtask assignee change
    function handleSubtaskAssigneeChange(assigneeBtn) {
        // Implementation for subtask assignee change
        console.log('Subtask assignee change clicked');
    }
    
    // Handle subtask deadline change
    function handleSubtaskDeadlineChange(deadlineBtn) {
        // Implementation for subtask deadline change
        console.log('Subtask deadline change clicked');
    }
    
    // Validate phase dates
    function validatePhaseDates(form) {
        const phaseStartDateInput = form.querySelector('#phaseStartDate');
        const phaseEndDateInput = form.querySelector('#phaseEndDate');
        const phaseDates = form.querySelector('.phaseDates');
        
        if (!phaseStartDateInput || !phaseEndDateInput || !phaseDates) return;
        
        const startDate = new Date(phaseStartDateInput.value);
        const endDate = new Date(phaseEndDateInput.value);
        
        // Clear previous error messages
        const existingError = phaseDates.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        // Reset input states
        phaseEndDateInput.classList.remove('is-invalid', 'is-valid');
        
        if (endDate < startDate) {
            // Show error
            phaseEndDateInput.value = phaseStartDateInput.value;
            
            const errorMessage = document.createElement('div');
            errorMessage.className = 'error-message text-danger fst-italic font-12 text-center mb-2 border-bottom border-danger';
            errorMessage.textContent = 'Error: End date cannot be before start date.';
            
            phaseDates.appendChild(errorMessage);
            phaseEndDateInput.classList.add('is-invalid');
            
            showMessage('Phase end date cannot be before start date', 'error');
        } else {
            phaseEndDateInput.classList.add('is-valid');
        }
    }
    
    // Show message function
    function showMessage(message, type = 'info') {
        // Use global message function if available
        if (window.ProjectManagement && window.ProjectManagement.utils && window.ProjectManagement.utils.showMessage) {
            window.ProjectManagement.utils.showMessage(message, type);
            return;
        }
        
        // Fallback: Create toast notification
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
   </div>
        `;
        
        // Add to toast container
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        
        toastContainer.appendChild(toast);
        
        // Show toast
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        // Remove from DOM after hiding
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }
    
    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initializePhaseFunctionality();
    });
    
    // Re-initialize on window resize
    window.addEventListener('resize', function() {
        if (PhaseConfig.display.tooltips) {
            initializeTooltips();
        }
    });
    
    // Re-initialize on visibility change
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible' && PhaseConfig.display.tooltips) {
            initializeTooltips();
        }
    });
    
    // Store phase configuration globally for access by other scripts
    if (!window.ProjectManagement) {
        window.ProjectManagement = { phases: {} };
    }
    if (!window.ProjectManagement.phases) {
        window.ProjectManagement.phases = {};
    }
    window.ProjectManagement.phases[phaseId] = PhaseConfig;
    
})();
</script>

<!-- Project Phase Styles -->
<style>
/**
 * Project Phase Component Styles
 * 
 * Comprehensive styling for the project phase component with responsive design,
 * accessibility features, and modern UI components.
 * 
 * @version 3.0.0
 * @since 1.0.0
 */

/* ========================================================================
   PHASE CARD STYLES
   ======================================================================== */

.phase-card {
    background: white;
    border-radius: 0.75rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    border: 1px solid #e9ecef;
    margin-bottom: 1.5rem;
    transition: all 0.3s ease;
    overflow: hidden;
}

.phase-card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.phase-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1.5rem;
    border-bottom: 1px solid #dee2e6;
}

.phase-title-section {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.phase-name-link {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2c3e50;
    text-decoration: none;
    transition: color 0.2s ease;
}

.phase-name-link:hover {
    color: #007bff;
}

.phase-weighting {
    font-size: 0.9rem;
    font-weight: 500;
}

.phase-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.phase-meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #6c757d;
    font-size: 0.9rem;
}

.phase-meta-item i {
    color: #007bff;
}

/* ========================================================================
   TASKS SECTION STYLES
   ======================================================================== */

.tasks-section {
    padding: 0;
}

.tasks-header {
    background: #f8f9fa;
    padding: 1rem 1.5rem;
    border-bottom: 2px solid #dee2e6;
}

.task-header-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #495057;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.tasks-list {
    padding: 0;
}

.task-row {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f1f3f4;
    transition: all 0.2s ease;
}

.task-row:hover {
    background: #f8f9fa;
}

.task-row:last-child {
    border-bottom: none;
}

.task-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.task-overdue-indicator {
    color: #dc3545;
    font-size: 1.1rem;
}

.task-details {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.task-edit-btn {
    color: #6c757d;
    text-decoration: none;
    font-size: 0.85rem;
    transition: color 0.2s ease;
}

.task-edit-btn:hover {
    color: #007bff;
}

.task-name-link {
    font-weight: 500;
    color: #2c3e50;
    text-decoration: none;
    transition: color 0.2s ease;
}

.task-name-link:hover {
    color: #007bff;
}

.task-weighting {
    font-size: 0.85rem;
    font-weight: 500;
}

.task-assignees {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.assignee-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #007bff;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease;
}

.assignee-avatar:hover {
    transform: scale(1.1);
}

.task-duration {
    font-weight: 500;
    color: #007bff;
}

.task-timeline {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.timeline-text {
    font-size: 0.9rem;
    color: #6c757d;
}

.task-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    justify-content: flex-end;
}

.task-action-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: #f8f9fa;
    color: #6c757d;
    transition: all 0.2s ease;
    text-decoration: none;
}

.task-action-btn:hover {
    background: #007bff;
    color: white;
    transform: scale(1.1);
}

/* ========================================================================
   SUBTASKS SECTION STYLES
   ======================================================================== */

.subtasks-section {
    margin-top: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
    border-left: 4px solid #007bff;
}

.subtasks-header {
    margin-bottom: 1rem;
}

.subtasks-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #495057;
    margin: 0;
}

.subtasks-list {
    padding: 0;
}

.subtask-row {
    padding: 0.75rem;
    background: white;
    border-radius: 0.5rem;
    margin-bottom: 0.5rem;
    border: 1px solid #e9ecef;
    transition: all 0.2s ease;
}

.subtask-row:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.subtask-row:last-child {
    margin-bottom: 0;
}

.subtask-info {
    display: flex;
    align-items: center;
}

.subtask-name-link {
    font-weight: 500;
    color: #495057;
    text-decoration: none;
    transition: color 0.2s ease;
}

.subtask-name-link:hover {
    color: #007bff;
}

.subtask-assignee {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.assignee-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.assignee-avatar-small {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #28a745;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: 600;
    border: 1px solid #fff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.subtask-deadline {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.deadline-text {
    font-size: 0.85rem;
    color: #6c757d;
}

.subtask-progress {
    font-size: 0.85rem;
    color: #28a745;
    font-weight: 500;
}

.subtask-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
    justify-content: flex-end;
}

/* ========================================================================
   EMPTY STATE STYLES
   ======================================================================== */

.no-tasks-state {
    padding: 2rem;
}

.empty-state {
    text-align: center;
}

.empty-state-icon {
    font-size: 3rem;
    color: #dee2e6;
    margin-bottom: 1rem;
}

.empty-state h6 {
    color: #6c757d;
    font-weight: 500;
}

/* ========================================================================
   DROPDOWN STYLES
   ======================================================================== */

.due-date-dropdown .dropdown-menu {
    border: none;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    border-radius: 0.5rem;
}

.dropdown-menu .form-label {
    font-weight: 500;
    color: #495057;
}

/* ========================================================================
   RESPONSIVE DESIGN
   ======================================================================== */

@media (max-width: 768px) {
    .phase-header {
        padding: 1rem;
    }
    
    .phase-title-section {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }
    
    .phase-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .task-row .row {
        flex-direction: column;
        gap: 1rem;
    }
    
    .task-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .task-assignees {
        justify-content: flex-start;
    }
    
    .task-actions {
        justify-content: flex-start;
        margin-top: 0.5rem;
    }
    
    .subtask-row .row {
        flex-direction: column;
        gap: 0.75rem;
    }
}

@media (max-width: 576px) {
    .phase-header {
        padding: 0.75rem;
    }
    
    .task-row {
        padding: 0.75rem 1rem;
    }
    
    .subtask-row {
        padding: 0.5rem;
    }
    
    .empty-state-icon {
        font-size: 2.5rem;
    }
}

/* ========================================================================
   ACCESSIBILITY IMPROVEMENTS
   ======================================================================== */

.btn:focus-visible {
    outline: 2px solid #007bff;
    outline-offset: 2px;
}

.task-action-btn:focus-visible {
    outline: 2px solid #007bff;
    outline-offset: 2px;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .phase-card {
        border: 2px solid #000;
    }
    
    .task-row:hover {
        background: #000;
        color: #fff;
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .phase-card,
    .task-row,
    .subtask-row,
    .task-action-btn {
        transition: none;
    }
    
    .phase-card:hover {
        transform: none;
    }
}

/* ========================================================================
   LOADING STATES
   ======================================================================== */

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

/* ========================================================================
   ERROR STATES
   ======================================================================== */

.is-invalid {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

.is-valid {
    border-color: #28a745 !important;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
}

.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #dc3545;
}
</style>