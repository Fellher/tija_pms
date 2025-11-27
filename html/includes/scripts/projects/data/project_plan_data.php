<?php

/**
 * Project Plan Data Layer
 * 
 * This file handles all data operations for the project plan system.
 * It uses the existing Projects class methods for data retrieval,
 * validation, and manipulation with proper error handling.
 * 
 * @package    PMS
 * @subpackage Projects
 * @author     SKM Development Team
 * @version    3.0.0
 * @since      1.0.0
 * 
 * Architecture:
 * =============
 * - Uses existing Projects class methods
 * - Data validation and sanitization
 * - Error handling and logging
 * - Data transformation and formatting
 * - Caching for performance optimization
 */

// This file is meant to be included, not accessed directly

/**
 * Project Plan Data Manager Class
 * 
 * Centralized data management for project plan operations.
 * Uses existing Projects class methods for data retrieval.
 * 
 * @class ProjectPlanDataManager
 * @since 3.0.0
 */
class ProjectPlanDataManager {
    
    /**
     * Database connection object
     * 
     * @var object $dbConn
     * @since 3.0.0
     */
    private $dbConn;
    
    /**
     * Configuration array
     * 
     * @var array $config
     * @since 3.0.0
     */
    private $config;
    
    /**
     * Cache array for storing retrieved data
     * 
     * @var array $cache
     * @since 3.0.0
     */
    private $cache = [];
    
    /**
     * Error log array
     * 
     * @var array $errors
     * @since 3.0.0
     */
    private $errors = [];
    
    /**
     * Constructor
     * 
     * @param object $dbConn Database connection object
     * @param array $config Configuration array
     * @since 3.0.0
     */
    public function __construct($dbConn, $config = []) {
        $this->dbConn = $dbConn;
        $this->config = $config;
    }
    
    /**
     * Get Project Details
     * 
     * Retrieves project details using Projects::projects_full method.
     * 
     * @param int $projectID Project ID
     * @return object|false Project details object or false on failure
     * @since 3.0.0
     */
    public function getProjectDetails($projectID) {
        $cacheKey = "project_details_{$projectID}";
        
        // Check cache first
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        try {
            // Validate input
            if (!is_numeric($projectID) || $projectID <= 0) {
                $this->addError("Invalid project ID: {$projectID}", ['projectID' => $projectID], 2);
                return false;
            }
            
            // Use Projects class method
            $whereArr = ['projectID' => $projectID];
            $project = Projects::projects_full($whereArr, true, $this->dbConn);
            
            if (!$project) {
                $this->addError("Project not found with ID: {$projectID}", ['projectID' => $projectID], 3);
                return false;
            }
            
            // Cache the result
            $this->cache[$cacheKey] = $project;
            
            return $project;
            
        } catch (Exception $e) {
            $this->addError("Error retrieving project details: " . $e->getMessage(), [
                'projectID' => $projectID,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 3);
            return false;
        }
    }
    
    /**
     * Get Project Phases
     * 
     * Retrieves project phases using Projects::project_phases method.
     * 
     * @param int $projectID Project ID
     * @return array|false Phases array or false on failure
     * @since 3.0.0
     */
    public function getProjectPhases($projectID) {
        $cacheKey = "project_phases_{$projectID}";
        
        // Check cache first
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        try {
            // Validate input
            if (!is_numeric($projectID) || $projectID <= 0) {
                $this->addError("Invalid project ID: {$projectID}", ['projectID' => $projectID], 2);
                return false;
            }
            
            // Use Projects class method
            $whereArr = ['projectID' => $projectID];
            $phases = Projects::project_phases($whereArr, false, $this->dbConn);
            
            if ($phases === false) {
                $this->addError("No phases found for project ID: {$projectID}", ['projectID' => $projectID], 1);
                return [];
            }
            
            // Process phases data
            $processedPhases = [];
            foreach ($phases as $phase) {
                $processedPhase = [
                    'id' => $phase->projectPhaseID ?? '',
                    'name' => $phase->projectPhaseName ?? 'Unnamed Phase',
                    'description' => $phase->projectPhaseDescription ?? '',
                    'weighting' => $phase->phaseWeighting ?? 0,
                    'workHours' => $phase->phaseWorkHrs ?? 0,
                    'startDate' => $phase->phaseStartDate ?? null,
                    'endDate' => $phase->phaseEndDate ?? null,
                    'duration' => $this->calculateDuration($phase->phaseStartDate, $phase->phaseEndDate),
                    'progress' => 0, // Will be calculated based on tasks
                    'isOverdue' => $this->isOverdue($phase->phaseEndDate),
                    'isCollapsed' => false,
                    'tasks' => []
                ];
                
                // Get tasks for this phase
                $phaseTasks = $this->getPhaseTasks($phase->projectPhaseID);
                if ($phaseTasks !== false) {
                    $processedPhase['tasks'] = $phaseTasks;
                    $processedPhase['progress'] = $this->calculatePhaseProgress($phaseTasks);
                }
                
                $processedPhases[] = $processedPhase;
            }
            
            // Cache the result
            $this->cache[$cacheKey] = $processedPhases;
            
            return $processedPhases;
            
        } catch (Exception $e) {
            $this->addError("Error retrieving project phases: " . $e->getMessage(), [
                'projectID' => $projectID,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 3);
            return false;
        }
    }
    
    /**
     * Get Phase Tasks
     * 
     * Retrieves tasks for a specific phase using Projects::project_tasks_full method.
     * 
     * @param int $phaseID Phase ID
     * @return array|false Tasks array or false on failure
     * @since 3.0.0
     */
    public function getPhaseTasks($phaseID) {
        $cacheKey = "phase_tasks_{$phaseID}";
        
        // Check cache first
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        try {
            // Validate input
            if (!is_numeric($phaseID) || $phaseID <= 0) {
                $this->addError("Invalid phase ID: {$phaseID}", ['phaseID' => $phaseID], 2);
                return false;
            }
            
            // Use Projects class method
            $whereArr = ['projectPhaseID' => $phaseID];
            $tasks = Projects::project_tasks_full($whereArr, false, $this->dbConn);
            
            if ($tasks === false) {
                return [];
            }
            
            // Process tasks data
            $processedTasks = [];
            foreach ($tasks as $task) {
                // var_dump($task);
                $processedTask = [
                    'id' => $task->projectTaskID ?? '',
                    'name' => $task->projectTaskName ?? 'Unnamed Task',
                    'description' => $task->taskDescription ?? '',
                    'weighting' => $task->taskWeighting ?? 0,
                    'hoursAllocated' => $task->hoursAllocated ?? 0,
                    'startDate' => $task->taskStart ?? null,
                    'deadline' => $task->taskDeadline ?? null,
                    'duration' => $this->calculateDuration($task->taskStart, $task->taskDeadline),
                    'progress' => $task->progress ?? 0,
                    'isOverdue' => $this->isOverdue($task->taskDeadline),
                    'status' => $task->status ?? 1,
                    'assignees' => $this->getTaskAssignees($task->projectTaskID),
                    'subtasks' => $this->getTaskSubtasks($task->projectTaskID),
                    'taskStatusID'=>$task->taskStatusID ?? '',
                    'projectTaskTypeID'=>$task->projectTaskTypeID ?? '',
                    'projectPhaseID'=>$task->projectPhaseID ?? '',
                    'projectID'=>$task->projectID ?? '',
                    'clientID'=>$task->clientID ?? '',
                    'projectTaskCode'=>$task->projectTaskCode ?? '',
                ];
                
                $processedTasks[] = $processedTask;
            }
            
            // Cache the result
            $this->cache[$cacheKey] = $processedTasks;
            
            return $processedTasks;
            
        } catch (Exception $e) {
            $this->addError("Error retrieving phase tasks: " . $e->getMessage(), [
                'phaseID' => $phaseID,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 3);
            return false;
        }
    }
    
    /**
     * Get Task Assignees
     * 
     * Retrieves assignees for a specific task.
     * 
     * @param int $taskID Task ID
     * @return array Assignees array
     * @since 3.0.0
     */
    public function getTaskAssignees($taskID) {
        $cacheKey = "task_assignees_{$taskID}";
        //check cache first
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        try {
            //validate input
            if (!is_numeric($taskID) || $taskID <= 0) {
                $this->addError("Invalid task ID: {$taskID}", ['taskID' => $taskID], 2);
                return false;
            }
            //use Projects class method
            $whereArr = ['projectTaskID' => $taskID, 'Suspended'=>"N"];
            $assignees = Projects::project_task_assignees($whereArr, false, $this->dbConn);
            if ($assignees === false) {
                return [];
            }
            // var_dump($assignees);
            //process assignees data
            $processedAssignees = [];
            foreach ($assignees as $assignee) {
                $processedAssignee = [
                    'assignmentId' => $assignee->assignmentTaskID ?? '',
                    'userId' => $assignee->userID ?? '',
                    'name' => $assignee->assigneeName ?? 'Unknown Assignee',
                    'initials' => $assignee->userInitials ?? '?',
                    'suspended' => $assignee->Suspended ?? 'N',
                    'taskID' => $assignee->projectTaskID ?? '',
                    'projectPhaseID' => $assignee->projectPhaseID ?? '',
                    'projectID' => $assignee->projectID ?? '',
                    'clientID' => $assignee->clientID ?? '',
                    'assignmentStatus' => $assignee->assignmentStatus ?? 'Pending',
                    'assignmentDate' => $assignee->DateAdded ?? null,                   
                    'projectID' => $assignee->projectID ?? '',
                ];
                $processedAssignees[] = $processedAssignee;
            }
            //cache result
            $this->cache[$cacheKey] = $processedAssignees;
            return $processedAssignees;
        } catch (Exception $e) {
            $this->addError("Error retrieving task assignees: " . $e->getMessage(), [
                'taskID' => $taskID,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 3);
            return false;
        }
        
       
    }
    
    /**
     * Get Task Subtasks
     * 
     * Retrieves subtasks for a specific task using Projects::project_subtasks_full method.
     * 
     * @param int $taskID Task ID
     * @return array|false Subtasks array or false on failure
     * @since 3.0.0
     */
    public function getTaskSubtasks($taskID) {
        $cacheKey = "task_subtasks_{$taskID}";
        
        // Check cache first
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        try {
            // Validate input
            if (!is_numeric($taskID) || $taskID <= 0) {
                $this->addError("Invalid task ID: {$taskID}", ['taskID' => $taskID], 2);
                return false;
            }
            
            // Use Projects class method
            $whereArr = ['projectTaskID' => $taskID, 'Suspended' => 'N'];
            $subtasks = Projects::project_subtasks_full($whereArr, false, $this->dbConn);

            // var_dump($subtasks);
            
            if ($subtasks === false) {
                return [];
            }
            
            // Process subtasks data
            $processedSubtasks = [];    
            foreach ($subtasks as $subtask) {
                $processedSubtask = [
                    'id' => $subtask->subtaskID ?? '',
                    'name' => $subtask->subTaskName ?? 'Unnamed Subtask',                   
                    'dueDate' => $subtask->subtaskDueDate ?? null,
                    'progress' => $subtask->subTaskStatus ?? 0,
                    'statusId' => $subtask->subTaskStatusID ?? '',
                    'assignee' => $subtask->assignee ?? null,
                    'description' => $subtask->subTaskDescription ?? '',
                    'allocatedWorkHours' => $subtask->subTaskAllocatedWorkHours ?? 0,
                    'assigneeName'=>$subtask->assigneeName ?? '',
                    'assigneeInitials'=>$subtask->userInitials ?? '',
                    'isOverdue' => $this->isOverdue($subtask->subtaskDueDate),
                ];
                
                $processedSubtasks[] = $processedSubtask;
            }
            
            // Cache the result
            $this->cache[$cacheKey] = $processedSubtasks;
            
            return $processedSubtasks;
            
        } catch (Exception $e) {
            $this->addError("Error retrieving task subtasks: " . $e->getMessage(), [
                'taskID' => $taskID,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 3);
            return false;
        }
    }
    
    /**
     * Get Team Members
     * 
     * Retrieves team members using Projects::project_team_full method.
     * 
     * @param int $projectID Project ID
     * @return array|false Team members array or false on failure
     * @since 3.0.0
     */
    public function getTeamMembers($projectID) {
        $cacheKey = "team_members_{$projectID}";
        
        // Check cache first
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        try {
            // Validate input
            if (!is_numeric($projectID) || $projectID <= 0) {
                $this->addError("Invalid project ID: {$projectID}", ['projectID' => $projectID], 2);
                return false;
            }
            
            // Use Projects class method
            $whereArr = ['projectID' => $projectID, 'Suspended' => 'N'];
            $teamMembers = Projects::project_team_full($whereArr, false, $this->dbConn);
            
            if ($teamMembers === false) {
                $this->addError("No team members found for project ID: {$projectID}", ['projectID' => $projectID], 1);
                return [];
            }
            
            // Process team members data
            $processedMembers = [];
            foreach ($teamMembers as $member) {
                // var_dump($member);
                $processedMember = [
                    'userID' => $member->userID ?? '',
                    'name' => $member->teamMemberName ?? 'Unknown Member',
                    'firstName' => $member->teamMemberFirstName ?? '',
                    'lastName' => $member->teamMemberLastName ?? '',
                    'initials' => $member->userInitials ?? '?',
                    'jobTitle' => $member->jobTitle ?? 'Unknown',
                    'role' => $member->projectTeamRoleName ?? 'Member',
                    'roleDescription' => $member->projectTeamRoleDescription ?? '',
                    'employeeName'=>$member->teamMemberName ?? '',
                ];
                
                $processedMembers[] = $processedMember;
            }
            
            // Cache the result
            $this->cache[$cacheKey] = $processedMembers;
            
            return $processedMembers;
            
        } catch (Exception $e) {
            $this->addError("Error retrieving team members: " . $e->getMessage(), [
                'projectID' => $projectID,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 3);
            return false;
        }
    }
    
    /**
     * Get Project Task Types
     * 
     * Retrieves project task types using Projects::project_task_types method.
     * 
     * @return array|false Task types array or false on failure
     * @since 3.0.0
     */
    public function getProjectTaskTypes() {
        $cacheKey = "project_task_types";
        
        // Check cache first
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        try {
            // Use Projects class method
            $whereArr = ['Suspended' => 'N'];
            $taskTypes = Projects::project_task_types($whereArr, false, $this->dbConn);
            
            if ($taskTypes === false) {
                $this->addError("No task types found", [], 1);
                return [];
            }
            
            // Process task types data
            $processedTypes = [];
            foreach ($taskTypes as $type) {
                $processedType = [
                    'id' => $type->projectTaskTypeID ?? '',
                    'name' => $type->projectTaskTypeName ?? 'Unknown Type',
                    'description' => $type->projectTaskTypeDescription ?? ''
                ];
                
                $processedTypes[] = $processedType;
            }
            
            // Cache the result
            $this->cache[$cacheKey] = $processedTypes;
            
            return $processedTypes;
            
        } catch (Exception $e) {
            $this->addError("Error retrieving project task types: " . $e->getMessage(), [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 3);
            return false;
        }
    }
    
    /**
     * Calculate Duration
     * 
     * Calculates duration between two dates in days.
     * 
     * @param string $startDate Start date
     * @param string $endDate End date
     * @return int Duration in days
     * @since 3.0.0
     */
    private function calculateDuration($startDate, $endDate) {
        if (empty($startDate) || empty($endDate)) {
            return 0;
        }
        
        $start = strtotime($startDate);
        $end = strtotime($endDate);
        
        if ($start === false || $end === false) {
            return 0;
        }
        
        return max(0, ($end - $start) / (60 * 60 * 24));
    }
    
    /**
     * Check if Overdue
     * 
     * Checks if a date is overdue.
     * 
     * @param string $date Date to check
     * @return bool True if overdue, false otherwise
     * @since 3.0.0
     */
    private function isOverdue($date) {
        if (empty($date)) {
            return false;
        }
        
        $dateTime = strtotime($date);
        if ($dateTime === false) {
            return false;
        }
        
        return $dateTime < time();
    }
    
    /**
     * Calculate Phase Progress
     * 
     * Calculates overall progress for a phase based on its tasks.
     * 
     * @param array $tasks Tasks array
     * @return float Progress percentage
     * @since 3.0.0
     */
    private function calculatePhaseProgress($tasks) {
        if (empty($tasks)) {
            return 0;
        }
        
        $totalProgress = 0;
        $taskCount = count($tasks);
        
        foreach ($tasks as $task) {
            $totalProgress += $task['progress'] ?? 0;
        }
        
        return $taskCount > 0 ? ($totalProgress / $taskCount) : 0;
    }
    
    /**
     * Add Error
     * 
     * Adds an error to the error log using Projects class methods.
     * 
     * @param string $message Error message
     * @param array $data Additional data to include in log
     * @param int $level Error level (1=INFO, 2=WARNING, 3=ERROR, 4=CRITICAL)
     * @since 3.0.0
     */
    private function addError($message, $data = [], $level = 3) {
        $this->errors[] = $message;
        Projects::logError($message, 'project_plan_data', $data, $level);
    }
    
    /**
     * Get Errors
     * 
     * Returns all errors.
     * 
     * @return array Errors array
     * @since 3.0.0
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Clear Cache
     * 
     * Clears the data cache.
     * 
     * @since 3.0.0
     */
    public function clearCache() {
        $this->cache = [];
    }
    
    /**
     * Clear Errors
     * 
     * Clears all errors.
     * 
     * @since 3.0.0
     */
    public function clearErrors() {
        $this->errors = [];
    }
}

/**
 * Get Project Plan Data
 * 
 * Convenience function to get project plan data using existing Projects class methods.
 * 
 * @param int $projectID Project ID
 * @param object $dbConn Database connection object
 * @param array $config Configuration array
 * @return array|false Project plan data or false on failure
 * @since 3.0.0
 */
function getProjectPlanData($projectID, $dbConn, $config = []) {
    try {
        $dataManager = new ProjectPlanDataManager($dbConn, $config);
        
        $projectDetails = $dataManager->getProjectDetails($projectID);
        if (!$projectDetails) {
            return false;
        }
        
        $phases = $dataManager->getProjectPhases($projectID);
        if ($phases === false) {
            return false;
        }
        
        $teamMembers = $dataManager->getTeamMembers($projectID);
        if ($teamMembers === false) {
            return false;
        }
        
        $taskTypes = $dataManager->getProjectTaskTypes();
        if ($taskTypes === false) {
            return false;
        }
  
        return [
            'project' => $projectDetails,
            'phases' => $phases,
            'teamMembers' => $teamMembers,
            'taskTypes' => $taskTypes,
            'errors' => $dataManager->getErrors()
        ];
        
    } catch (Exception $e) {
        error_log("Error getting project plan data: " . $e->getMessage());
        return false;
    }
}

/**
 * Handle AJAX Actions
 * 
 * Processes AJAX requests for project plan operations.
 * 
 * @since 3.0.0
 */
function handleProjectPlanActions() {
    // Only process POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }
    
    // Get action from POST data
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'remove_task_assignee':
            handleRemoveTaskAssignee();
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            exit;
    }
}

/**
 * Handle Remove Task Assignee Action
 * 
 * Removes an assignee from a task.
 * 
 * @since 3.0.0
 */
function handleRemoveTaskAssignee() {
    try {
        // Validate required parameters
        $projectId = $_POST['project_id'] ?? '';
        $taskId = $_POST['task_id'] ?? '';
        $assigneeId = $_POST['assignee_id'] ?? '';
        $assignmentId = $_POST['assignment_id'] ?? '';
        
        if (empty($projectId) || empty($taskId) || empty($assigneeId)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Missing required parameters'
            ]);
            return;
        }
        
        // Validate that parameters are numeric
        if (!is_numeric($projectId) || !is_numeric($taskId) || !is_numeric($assigneeId)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Invalid parameter format'
            ]);
            return;
        }
        
        // Include necessary files
        require_once __DIR__ . '/../../../../php/includes.php';
        
        // Get database connection
        global $DBConn;
        
        // Check if assignee exists for this task
        $whereArray = [
            'projectTaskID' => $taskId,
            'userID' => $assigneeId,
            'Suspended' => 'N'
        ];
        
        // If assignmentId is provided, use it for more precise targeting
        if (!empty($assignmentId) && is_numeric($assignmentId)) {
            $whereArray['assignmentTaskID'] = $assignmentId;
            $whereArray['Suspended'] = 'N';
        }
        
        $existingAssignees = Projects::assigned_task($whereArray, false, $DBConn);
        
        if (empty($existingAssignees)) {
            echo json_encode([
                'success' => false, 
                'message' => 'Assignee not found for this task'
            ]);
            return;
        }
        
        // Update the assignee record to set suspended to 'Y'
        $updateData = [
            'Suspended' => 'Y',
            'assignmentStatus' => 'suspended'
        ];
        
        $whereArray = [
            'projectTaskID' => $taskId,
            'userID' => $assigneeId,
            'Suspended' => 'N'
        ];
        
        // Use assignmentId if available for more precise update
        if (!empty($assignmentId) && is_numeric($assignmentId)) {
            $whereArray['assignmentTaskID'] = $assignmentId;
            $whereArray['Suspended'] = 'N';
        }
        
        $result = $DBConn->update_table('tija_assigned_project_tasks', $updateData, $whereArray);
        
        if ($result) {
            echo json_encode([
                'success' => true, 
                'message' => 'Assignee removed successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Failed to remove assignee'
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Error removing task assignee: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'An error occurred while removing assignee'
        ]);
    }
}

// Handle AJAX actions if this file is accessed directly
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    handleProjectPlanActions();
}