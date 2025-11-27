<?php
/**
 * Project Plan Business Logic Layer
 * 
 * This file contains all business logic for the project plan system.
 * It handles calculations, validations, and business rules that are
 * independent of data storage and presentation.
 * 
 * @package    PMS
 * @subpackage Projects
 * @author     SKM Development Team
 * @version    3.0.0
 * @since      1.0.0
 * 
 * Architecture:
 * =============
 * - Business rule validation
 * - Calculation and computation logic
 * - Data transformation and formatting
 * - Workflow and state management
 * - Performance and optimization logic
 */

// This file is meant to be included, not accessed directly

/**
 * Project Plan Business Logic Manager Class
 * 
 * Centralized business logic for project plan operations.
 * Handles all calculations, validations, and business rules.
 * 
 * @class ProjectPlanLogicManager
 * @since 3.0.0
 */
class ProjectPlanLogicManager {
    
    /**
     * Configuration array
     * 
     * @var array $config
     * @since 3.0.0
     */
    private $config;
    
    /**
     * Validation rules
     * 
     * @var array $validationRules
     * @since 3.0.0
     */
    private $validationRules;
    
    /**
     * Constructor
     * 
     * @param array $config Configuration array
     * @since 3.0.0
     */
    public function __construct($config = []) {
        $this->config = $config;
        $this->validationRules = $this->initializeValidationRules();
    }
    
    /**
     * Initialize Validation Rules
     * 
     * Sets up validation rules based on configuration.
     * 
     * @return array Validation rules array
     * @since 3.0.0
     */
    private function initializeValidationRules() {
        return [
            'phase' => [
                'name' => [
                    'required' => true,
                    'min_length' => 3,
                    'max_length' => 100,
                    'pattern' => '/^[a-zA-Z0-9\s\-_]+$/'
                ],
                'weighting' => [
                    'required' => true,
                    'min_value' => 0,
                    'max_value' => 100,
                    'type' => 'numeric'
                ],
                'workHours' => [
                    'required' => false,
                    'min_value' => 0,
                    'max_value' => 9999,
                    'type' => 'integer'
                ],
                'startDate' => [
                    'required' => true,
                    'type' => 'date',
                    'format' => 'Y-m-d'
                ],
                'endDate' => [
                    'required' => true,
                    'type' => 'date',
                    'format' => 'Y-m-d',
                    'after' => 'startDate'
                ]
            ],
            'task' => [
                'name' => [
                    'required' => true,
                    'min_length' => 3,
                    'max_length' => 200,
                    'pattern' => '/^[a-zA-Z0-9\s\-_]+$/'
                ],
                'weighting' => [
                    'required' => false,
                    'min_value' => 0,
                    'max_value' => 100,
                    'type' => 'numeric'
                ],
                'hoursAllocated' => [
                    'required' => false,
                    'min_value' => 0,
                    'max_value' => 9999,
                    'type' => 'integer'
                ],
                'startDate' => [
                    'required' => false,
                    'type' => 'date',
                    'format' => 'Y-m-d'
                ],
                'deadline' => [
                    'required' => true,
                    'type' => 'date',
                    'format' => 'Y-m-d',
                    'after' => 'startDate'
                ]
            ],
            'subtask' => [
                'name' => [
                    'required' => true,
                    'min_length' => 3,
                    'max_length' => 150,
                    'pattern' => '/^[a-zA-Z0-9\s\-_]+$/'
                ],
                'dueDate' => [
                    'required' => true,
                    'type' => 'date',
                    'format' => 'Y-m-d'
                ],
                'allocatedHours' => [
                    'required' => false,
                    'min_value' => 0,
                    'max_value' => 999,
                    'type' => 'numeric'
                ],
                'progress' => [
                    'required' => false,
                    'min_value' => 0,
                    'max_value' => 100,
                    'type' => 'numeric'
                ]
            ]
        ];
    }
    
    /**
     * Validate Phase Data
     * 
     * Validates phase data against business rules.
     * 
     * @param array $phaseData Phase data to validate
     * @return array Validation result with errors and warnings
     * @since 3.0.0
     */
    public function validatePhaseData($phaseData) {
        $errors = [];
        $warnings = [];
        
        // Validate required fields
        if (empty($phaseData['name'])) {
            $errors[] = 'Phase name is required';
        } elseif (strlen($phaseData['name']) < 3) {
            $errors[] = 'Phase name must be at least 3 characters long';
        } elseif (strlen($phaseData['name']) > 100) {
            $errors[] = 'Phase name must not exceed 100 characters';
        }
        
        // Validate weighting
        if (isset($phaseData['weighting'])) {
            if (!is_numeric($phaseData['weighting'])) {
                $errors[] = 'Phase weighting must be a number';
            } elseif ($phaseData['weighting'] < 0 || $phaseData['weighting'] > 100) {
                $errors[] = 'Phase weighting must be between 0 and 100';
            }
        }
        
        // Validate work hours
        if (isset($phaseData['workHours'])) {
            if (!is_numeric($phaseData['workHours'])) {
                $errors[] = 'Work hours must be a number';
            } elseif ($phaseData['workHours'] < 0) {
                $errors[] = 'Work hours cannot be negative';
            } elseif ($phaseData['workHours'] > 9999) {
                $warnings[] = 'Work hours seem unusually high';
            }
        }
        
        // Validate dates
        if (isset($phaseData['startDate']) && isset($phaseData['endDate'])) {
            if (!empty($phaseData['startDate']) && !empty($phaseData['endDate'])) {
                $startDate = strtotime($phaseData['startDate']);
                $endDate = strtotime($phaseData['endDate']);
                
                if ($startDate === false) {
                    $errors[] = 'Invalid start date format';
                } elseif ($endDate === false) {
                    $errors[] = 'Invalid end date format';
                } elseif ($endDate < $startDate) {
                    $errors[] = 'End date cannot be before start date';
                } else {
                    $duration = ($endDate - $startDate) / (60 * 60 * 24);
                    if ($duration > 365) {
                        $warnings[] = 'Phase duration is longer than one year';
                    }
                }
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }
    
    /**
     * Validate Task Data
     * 
     * Validates task data against business rules.
     * 
     * @param array $taskData Task data to validate
     * @param array $phaseData Parent phase data for context
     * @return array Validation result with errors and warnings
     * @since 3.0.0
     */
    public function validateTaskData($taskData, $phaseData = []) {
        $errors = [];
        $warnings = [];
        
        // Validate required fields
        if (empty($taskData['name'])) {
            $errors[] = 'Task name is required';
        } elseif (strlen($taskData['name']) < 3) {
            $errors[] = 'Task name must be at least 3 characters long';
        } elseif (strlen($taskData['name']) > 200) {
            $errors[] = 'Task name must not exceed 200 characters';
        }
        
        // Validate weighting
        if (isset($taskData['weighting'])) {
            if (!is_numeric($taskData['weighting'])) {
                $errors[] = 'Task weighting must be a number';
            } elseif ($taskData['weighting'] < 0 || $taskData['weighting'] > 100) {
                $errors[] = 'Task weighting must be between 0 and 100';
            }
        }
        
        // Validate hours allocated
        if (isset($taskData['hoursAllocated'])) {
            if (!is_numeric($taskData['hoursAllocated'])) {
                $errors[] = 'Hours allocated must be a number';
            } elseif ($taskData['hoursAllocated'] < 0) {
                $errors[] = 'Hours allocated cannot be negative';
            } elseif ($taskData['hoursAllocated'] > 9999) {
                $warnings[] = 'Hours allocated seem unusually high';
            }
        }
        
        // Validate dates
        if (isset($taskData['startDate']) && isset($taskData['deadline'])) {
            if (!empty($taskData['startDate']) && !empty($taskData['deadline'])) {
                $startDate = strtotime($taskData['startDate']);
                $deadline = strtotime($taskData['deadline']);
                
                if ($startDate === false) {
                    $errors[] = 'Invalid start date format';
                } elseif ($deadline === false) {
                    $errors[] = 'Invalid deadline format';
                } elseif ($deadline < $startDate) {
                    $errors[] = 'Deadline cannot be before start date';
                } else {
                    $duration = ($deadline - $startDate) / (60 * 60 * 24);
                    if ($duration > 365) {
                        $warnings[] = 'Task duration is longer than one year';
                    }
                    
                    // Check if task deadline is within phase bounds
                    if (!empty($phaseData['startDate']) && !empty($phaseData['endDate'])) {
                        $phaseStart = strtotime($phaseData['startDate']);
                        $phaseEnd = strtotime($phaseData['endDate']);
                        
                        if ($startDate < $phaseStart) {
                            $warnings[] = 'Task start date is before phase start date';
                        }
                        if ($deadline > $phaseEnd) {
                            $warnings[] = 'Task deadline is after phase end date';
                        }
                    }
                }
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }
    
    /**
     * Validate Subtask Data
     * 
     * Validates subtask data against business rules.
     * 
     * @param array $subtaskData Subtask data to validate
     * @param array $taskData Parent task data for context
     * @return array Validation result with errors and warnings
     * @since 3.0.0
     */
    public function validateSubtaskData($subtaskData, $taskData = []) {
        $errors = [];
        $warnings = [];
        
        // Validate required fields
        if (empty($subtaskData['name'])) {
            $errors[] = 'Subtask name is required';
        } elseif (strlen($subtaskData['name']) < 3) {
            $errors[] = 'Subtask name must be at least 3 characters long';
        } elseif (strlen($subtaskData['name']) > 150) {
            $errors[] = 'Subtask name must not exceed 150 characters';
        }
        
        // Validate due date
        if (empty($subtaskData['dueDate'])) {
            $errors[] = 'Subtask due date is required';
        } else {
            $dueDate = strtotime($subtaskData['dueDate']);
            if ($dueDate === false) {
                $errors[] = 'Invalid due date format';
            } else {
                // Check if due date is within task bounds
                if (!empty($taskData['deadline'])) {
                    $taskDeadline = strtotime($taskData['deadline']);
                    if ($dueDate > $taskDeadline) {
                        $errors[] = 'Subtask due date cannot be after task deadline';
                    }
                }
            }
        }
        
        // Validate allocated hours
        if (isset($subtaskData['allocatedHours'])) {
            if (!is_numeric($subtaskData['allocatedHours'])) {
                $errors[] = 'Allocated hours must be a number';
            } elseif ($subtaskData['allocatedHours'] < 0) {
                $errors[] = 'Allocated hours cannot be negative';
            } elseif ($subtaskData['allocatedHours'] > 999) {
                $warnings[] = 'Allocated hours seem unusually high';
            }
        }
        
        // Validate progress
        if (isset($subtaskData['progress'])) {
            if (!is_numeric($subtaskData['progress'])) {
                $errors[] = 'Progress must be a number';
            } elseif ($subtaskData['progress'] < 0 || $subtaskData['progress'] > 100) {
                $errors[] = 'Progress must be between 0 and 100';
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }
    
    /**
     * Calculate Phase Progress
     * 
     * Calculates the overall progress of a phase based on its tasks.
     * 
     * @param array $phaseData Phase data with tasks
     * @return array Progress calculation result
     * @since 3.0.0
     */
    public function calculatePhaseProgress($phaseData) {
        if (empty($phaseData['tasks'])) {
            return [
                'progress' => 0,
                'completedTasks' => 0,
                'totalTasks' => 0,
                'weightedProgress' => 0,
                'isCompleted' => false
            ];
        }
        
        $totalTasks = count($phaseData['tasks']);
        $completedTasks = 0;
        $weightedProgress = 0;
        $totalWeight = 0;
        
        foreach ($phaseData['tasks'] as $task) {
            $taskProgress = $this->calculateTaskProgress($task);
            
            if ($taskProgress['isCompleted']) {
                $completedTasks++;
            }
            
            $weight = is_numeric($task['weighting']) ? (float)$task['weighting'] : 1;
            $taskProgressValue = is_numeric($taskProgress['progress']) ? (float)$taskProgress['progress'] : 0;
            $weightedProgress += $taskProgressValue * $weight;
            $totalWeight += $weight;
        }
        
        $progress = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
        $weightedProgress = $totalWeight > 0 ? ($weightedProgress / $totalWeight) : 0;
        $isCompleted = $progress >= 100;
        
        return [
            'progress' => round($progress, 2),
            'completedTasks' => $completedTasks,
            'totalTasks' => $totalTasks,
            'weightedProgress' => round($weightedProgress, 2),
            'isCompleted' => $isCompleted
        ];
    }
    
    /**
     * Calculate Task Progress
     * 
     * Calculates the progress of a task based on its subtasks.
     * 
     * @param array $taskData Task data with subtasks
     * @return array Progress calculation result
     * @since 3.0.0
     */
    public function calculateTaskProgress($taskData) {
        if (empty($taskData['subtasks'])) {
            // If no subtasks, check task status
            $isCompleted = ($taskData['status'] ?? 1) == 6; // 6 = completed
            return [
                'progress' => $isCompleted ? 100 : 0,
                'isCompleted' => $isCompleted,
                'completedSubtasks' => 0,
                'totalSubtasks' => 0
            ];
        }
        
        $totalSubtasks = count($taskData['subtasks']);
        $completedSubtasks = 0;
        $totalProgress = 0;
        
        foreach ($taskData['subtasks'] as $subtask) {
            $subtaskProgress = is_numeric($subtask['progress']) ? (float)$subtask['progress'] : 0;
            $totalProgress += $subtaskProgress;
            
            if ($subtaskProgress >= 100) {
                $completedSubtasks++;
            }
        }
        
        $progress = $totalSubtasks > 0 ? ($totalProgress / $totalSubtasks) : 0;
        $isCompleted = $progress >= 100;
        
        return [
            'progress' => round($progress, 2),
            'isCompleted' => $isCompleted,
            'completedSubtasks' => $completedSubtasks,
            'totalSubtasks' => $totalSubtasks
        ];
    }
    
    /**
     * Calculate Project Timeline
     * 
     * Calculates project timeline based on phases and tasks.
     * 
     * @param array $projectData Project data with phases
     * @return array Timeline calculation result
     * @since 3.0.0
     */
    public function calculateProjectTimeline($projectData) {
        // var_dump($projectData);
        if (empty($projectData['phases'])) {
            return [
                'setProjectStartDate' => $projectData['project']->projectStart ?? null,
                'setProjectEndDate' => $projectData['project']->projectClose ?? null,
                'startDate' => null,
                'endDate' => null,
                'duration' => 0,
                'isOverdue' => false,
                'phases' => []
            ];
        }
        
        $earliestStart = null;
        $latestEnd = null;
        $totalDuration = 0;
        $isOverdue = false;
        $phases = [];
        
        foreach ($projectData['phases'] as $phase) {
            $phaseTimeline = $this->calculatePhaseTimeline($phase);
            $phases[] = $phaseTimeline;
            
            if ($phaseTimeline['startDate']) {
                $startDate = strtotime($phaseTimeline['startDate']);
                if (!$earliestStart || $startDate < $earliestStart) {
                    $earliestStart = $startDate;
                }
            }
            
            if ($phaseTimeline['endDate']) {
                $endDate = strtotime($phaseTimeline['endDate']);
                if (!$latestEnd || $endDate > $latestEnd) {
                    $latestEnd = $endDate;
                }
            }
            
            $totalDuration += $phaseTimeline['duration'];
            
            if ($phaseTimeline['isOverdue']) {
                $isOverdue = true;
            }
        }
        
        return [
            'setProjectStartDate' => $projectData['project']->projectStart ?? null,
            'setProjectEndDate' => $projectData['project']->projectClose ?? null,
            'startDate' => $earliestStart ? date('Y-m-d', $earliestStart) : null,
            'endDate' => $latestEnd ? date('Y-m-d', $latestEnd) : null,
            'duration' => $totalDuration,
            'isOverdue' => $isOverdue,
            'phases' => $phases
        ];
    }
    
    /**
     * Calculate Phase Timeline
     * 
     * Calculates phase timeline based on tasks.
     * 
     * @param array $phaseData Phase data with tasks
     * @return array Timeline calculation result
     * @since 3.0.0
     */
    public function calculatePhaseTimeline($phaseData) {
        if (empty($phaseData['tasks'])) {
            return [
                'startDate' => $phaseData['startDate'] ?? null,
                'endDate' => $phaseData['endDate'] ?? null,
                'duration' => 0,
                'isOverdue' => false,
                'tasks' => []
            ];
        }
        
        $earliestStart = null;
        $latestEnd = null;
        $totalDuration = 0;
        $isOverdue = false;
        $tasks = [];
        
        foreach ($phaseData['tasks'] as $task) {
            $taskTimeline = $this->calculateTaskTimeline($task);
            $tasks[] = $taskTimeline;
            
            if ($taskTimeline['startDate']) {
                $startDate = strtotime($taskTimeline['startDate']);
                if (!$earliestStart || $startDate < $earliestStart) {
                    $earliestStart = $startDate;
                }
            }
            
            if ($taskTimeline['deadline']) {
                $endDate = strtotime($taskTimeline['deadline']);
                if (!$latestEnd || $endDate > $latestEnd) {
                    $latestEnd = $endDate;
                }
            }
            
            $totalDuration += $taskTimeline['duration'];
            
            if ($taskTimeline['isOverdue']) {
                $isOverdue = true;
            }
        }
        
        return [
            'startDate' => $earliestStart ? date('Y-m-d', $earliestStart) : ($phaseData['startDate'] ?? null),
            'endDate' => $latestEnd ? date('Y-m-d', $latestEnd) : ($phaseData['endDate'] ?? null),
            'duration' => $totalDuration,
            'isOverdue' => $isOverdue,
            'tasks' => $tasks
        ];
    }
    
    /**
     * Calculate Task Timeline
     * 
     * Calculates task timeline based on subtasks.
     * 
     * @param array $taskData Task data with subtasks
     * @return array Timeline calculation result
     * @since 3.0.0
     */
    public function calculateTaskTimeline($taskData) {
        $startDate = $taskData['startDate'] ?? null;
        $deadline = $taskData['deadline'] ?? null;
        $duration = 0;
        $isOverdue = false;
        
        if ($startDate && $deadline) {
            $start = strtotime($startDate);
            $end = strtotime($deadline);
            $duration = ($end - $start) / (60 * 60 * 24);
            
            // Check if overdue
            if ($end < time() && ($taskData['status'] ?? 1) != 6) {
                $isOverdue = true;
            }
        }
        
        return [
            'startDate' => $startDate,
            'deadline' => $deadline,
            'duration' => max(0, $duration),
            'isOverdue' => $isOverdue
        ];
    }
    
    /**
     * Calculate Resource Allocation
     * 
     * Calculates resource allocation across phases and tasks.
     * 
     * @param array $projectData Project data with phases
     * @return array Resource allocation result
     * @since 3.0.0
     */
    public function calculateResourceAllocation($projectData) {
        $totalHours = 0;
        $allocatedHours = 0;
        $phases = [];
        
        foreach ($projectData['phases'] as $phase) {
            $phaseHours = is_numeric($phase['workHours']) ? (float)$phase['workHours'] : 0;
            $totalHours += $phaseHours;
            
            $phaseAllocation = [
                'phaseId' => $phase['id'],
                'phaseName' => $phase['name'],
                'allocatedHours' => $phaseHours,
                'usedHours' => 0,
                'remainingHours' => $phaseHours,
                'tasks' => []
            ];
            
            if (!empty($phase['tasks'])) {
                foreach ($phase['tasks'] as $task) {
                    $taskHours = is_numeric($task['hoursAllocated']) ? (float)$task['hoursAllocated'] : 0;
                    $phaseAllocation['usedHours'] += $taskHours;
                    $phaseAllocation['remainingHours'] -= $taskHours;
                    
                    $phaseAllocation['tasks'][] = [
                        'taskId' => $task['id'],
                        'taskName' => $task['name'],
                        'allocatedHours' => $taskHours
                    ];
                }
            }
            
            $phases[] = $phaseAllocation;
            $allocatedHours += $phaseAllocation['usedHours'];
        }
        
        return [
            'totalHours' => $totalHours,
            'allocatedHours' => $allocatedHours,
            'remainingHours' => $totalHours - $allocatedHours,
            'utilization' => $totalHours > 0 ? ($allocatedHours / $totalHours) * 100 : 0,
            'phases' => $phases
        ];
    }
    
    /**
     * Generate Project Summary
     * 
     * Generates a comprehensive project summary with key metrics.
     * 
     * @param array $projectData Project data
     * @return array Project summary
     * @since 3.0.0
     */

   
    public function generateProjectSummary($projectData) {
        $timeline = $this->calculateProjectTimeline($projectData);
        $resourceAllocation = $this->calculateResourceAllocation($projectData);
        $totalPhases = count($projectData['phases'] ?? []);
        $totalTasks = 0;
        $totalSubtasks = 0;
        $completedPhases = 0;
        $completedTasks = 0;
        $overdueTasks = 0;
        $totalHoursAllocated = 0;
        $totalHoursUsed = 0;
        $totalTeamMembers = 0;
        $activeTeamMembers = 0;
        $riskLevel = 'low';
        $budgetUtilization = 0;
        $scheduleVariance = 0;
        $costVariance = 0;
        
        // Enhanced metrics calculation
        foreach ($projectData['phases'] ?? [] as $phase) {
            $phaseProgress = $this->calculatePhaseProgress($phase);
            if ($phaseProgress['isCompleted']) {
                $completedPhases++;
            }
            
            $totalTasks += count($phase['tasks'] ?? []);
            $totalHoursAllocated += is_numeric($phase['workHours']) ? (float)$phase['workHours'] : 0;
            
            foreach ($phase['tasks'] ?? [] as $task) {
                $taskProgress = $this->calculateTaskProgress($task);
                if ($taskProgress['isCompleted']) {
                    $completedTasks++;
                }
                
                // Enhanced overdue detection
                if ($this->isTaskOverdue($task)) {
                    $overdueTasks++;
                }
                
                $totalSubtasks += count($task['subtasks'] ?? []);
                $totalHoursAllocated += is_numeric($task['hoursAllocated']) ? (float)$task['hoursAllocated'] : 0;
                
                // Calculate actual hours used (placeholder - would come from time tracking)
                $totalHoursUsed += $this->calculateActualHoursUsed($task);
                
                // Count team members
                if (!empty($task['assignees'])) {
                    foreach ($task['assignees'] as $assignee) {
                        $totalTeamMembers++;
                        if ($this->isActiveTeamMember($assignee)) {
                            $activeTeamMembers++;
                        }
                    }
                }
            }
        }
        
        // Calculate enhanced metrics
        $phaseProgress = $totalPhases > 0 ? ($completedPhases / $totalPhases) * 100 : 0;
        $taskProgress = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;
        $overduePercentage = $totalTasks > 0 ? ($overdueTasks / $totalTasks) * 100 : 0;
        $resourceUtilization = $totalHoursAllocated > 0 ? ($totalHoursUsed / $totalHoursAllocated) * 100 : 0;
        
        // Calculate project health indicators
        $projectHealth = $this->calculateProjectHealth($phaseProgress, $taskProgress, $overduePercentage, $resourceUtilization);
        $riskLevel = $this->calculateRiskLevel($overduePercentage, $scheduleVariance, $costVariance);
        
        // Calculate schedule variance
        $scheduleVariance = $this->calculateScheduleVariance($timeline, $projectData);
        
        // Calculate budget utilization (placeholder - would integrate with financial data)
        $budgetUtilization = $this->calculateBudgetUtilization($projectData);
        
        return [
            'project' => $projectData['project'] ?? [],
            'timeline' => $timeline,
            'resourceAllocation' => $resourceAllocation,
            'metrics' => [
                // Basic metrics
                'totalPhases' => $totalPhases,
                'completedPhases' => $completedPhases,
                'phaseProgress' => round($phaseProgress, 1),
                'totalTasks' => $totalTasks,
                'completedTasks' => $completedTasks,
                'taskProgress' => round($taskProgress, 1),
                'totalSubtasks' => $totalSubtasks,
                'overdueTasks' => $overdueTasks,
                'overduePercentage' => round($overduePercentage, 1),
                
                // Enhanced metrics
                'totalHoursAllocated' => round($totalHoursAllocated, 1),
                'totalHoursUsed' => round($totalHoursUsed, 1),
                'resourceUtilization' => round($resourceUtilization, 1),
                'totalTeamMembers' => $totalTeamMembers,
                'activeTeamMembers' => $activeTeamMembers,
                'teamUtilization' => $totalTeamMembers > 0 ? round(($activeTeamMembers / $totalTeamMembers) * 100, 1) : 0,
                
                // Project health indicators
                'projectHealth' => $projectHealth,
                'riskLevel' => $riskLevel,
                'scheduleVariance' => round($scheduleVariance, 1),
                'budgetUtilization' => round($budgetUtilization, 1),
                
                // Performance indicators
                'efficiency' => $this->calculateEfficiency($taskProgress, $resourceUtilization),
                'productivity' => $this->calculateProductivity($totalHoursUsed, $completedTasks),
                'quality' => $this->calculateQualityScore($overduePercentage, $projectHealth)
            ]
        ];
    }
    
    /**
     * Check if task is overdue with enhanced logic
     * 
     * @param array $task Task data
     * @return bool True if task is overdue
     * @since 3.0.0
     */
    private function isTaskOverdue($task) {
        $deadline = $task['deadline'] ?? null;
        $status = $task['status'] ?? 1;
        
        if (!$deadline || $status == 6) { // 6 = completed
            return false;
        }
        
        $deadlineTime = strtotime($deadline);
        $currentTime = time();
        
        // Task is overdue if deadline has passed and not completed
        return $deadlineTime < $currentTime;
    }
    
    /**
     * Calculate actual hours used for a task
     * 
     * @param array $task Task data
     * @return float Actual hours used
     * @since 3.0.0
     */
    private function calculateActualHoursUsed($task) {
        // This would integrate with time tracking system
        // For now, estimate based on progress and allocated hours
        $allocatedHours = is_numeric($task['hoursAllocated']) ? (float)$task['hoursAllocated'] : 0;
        $progress = is_numeric($task['progress']) ? (float)$task['progress'] : 0;
        
        // Estimate actual hours based on progress (with some variance)
        $estimatedHours = ($allocatedHours * $progress / 100) * (0.8 + (rand(0, 40) / 100));
        
        return max(0, $estimatedHours);
    }
    
    /**
     * Check if team member is active
     * 
     * @param array $assignee Assignee data
     * @return bool True if active
     * @since 3.0.0
     */
    private function isActiveTeamMember($assignee) {
        // This would check against employee status, recent activity, etc.
        // For now, assume all assignees are active
        return true;
    }
    
    /**
     * Calculate project health score
     * 
     * @param float $phaseProgress Phase progress percentage
     * @param float $taskProgress Task progress percentage
     * @param float $overduePercentage Overdue percentage
     * @param float $resourceUtilization Resource utilization percentage
     * @return string Health status
     * @since 3.0.0
     */
    private function calculateProjectHealth($phaseProgress, $taskProgress, $overduePercentage, $resourceUtilization) {
        $overallProgress = ($phaseProgress + $taskProgress) / 2;
        
        // Health scoring algorithm
        $healthScore = 0;
        
        // Progress component (40% weight)
        $healthScore += ($overallProgress / 100) * 40;
        
        // Overdue component (30% weight) - lower overdue = higher health
        $healthScore += max(0, (100 - $overduePercentage) / 100) * 30;
        
        // Resource utilization component (20% weight) - optimal range 70-90%
        if ($resourceUtilization >= 70 && $resourceUtilization <= 90) {
            $healthScore += 20;
        } elseif ($resourceUtilization >= 60 && $resourceUtilization < 70) {
            $healthScore += 15;
        } elseif ($resourceUtilization > 90 && $resourceUtilization <= 100) {
            $healthScore += 15;
        } else {
            $healthScore += max(0, (100 - abs($resourceUtilization - 80)) / 100) * 20;
        }
        
        // Schedule adherence component (10% weight)
        $healthScore += 10; // Placeholder - would calculate based on schedule variance
        
        if ($healthScore >= 80) {
            return 'excellent';
        } elseif ($healthScore >= 60) {
            return 'good';
        } elseif ($healthScore >= 40) {
            return 'fair';
        } else {
            return 'poor';
        }
    }
    
    /**
     * Calculate risk level
     * 
     * @param float $overduePercentage Overdue percentage
     * @param float $scheduleVariance Schedule variance
     * @param float $costVariance Cost variance
     * @return string Risk level
     * @since 3.0.0
     */
    private function calculateRiskLevel($overduePercentage, $scheduleVariance, $costVariance) {
        $riskScore = 0;
        
        // Overdue risk
        if ($overduePercentage > 30) {
            $riskScore += 40;
        } elseif ($overduePercentage > 15) {
            $riskScore += 25;
        } elseif ($overduePercentage > 5) {
            $riskScore += 10;
        }
        
        // Schedule variance risk
        if (abs($scheduleVariance) > 20) {
            $riskScore += 30;
        } elseif (abs($scheduleVariance) > 10) {
            $riskScore += 20;
        } elseif (abs($scheduleVariance) > 5) {
            $riskScore += 10;
        }
        
        // Cost variance risk
        if (abs($costVariance) > 15) {
            $riskScore += 30;
        } elseif (abs($costVariance) > 10) {
            $riskScore += 20;
        } elseif (abs($costVariance) > 5) {
            $riskScore += 10;
        }
        
        if ($riskScore >= 70) {
            return 'high';
        } elseif ($riskScore >= 40) {
            return 'medium';
        } else {
            return 'low';
        }
    }
    
    /**
     * Calculate schedule variance
     * 
     * @param array $timeline Timeline data
     * @param array $projectData Project data
     * @return float Schedule variance percentage
     * @since 3.0.0
     */
    private function calculateScheduleVariance($timeline, $projectData) {
        $plannedEndDate = $projectData['project']->projectClose ?? null;
        $actualEndDate = $timeline['endDate'] ?? null;
        
        if (!$plannedEndDate || !$actualEndDate) {
            return 0;
        }
        
        $plannedTime = strtotime($plannedEndDate);
        $actualTime = strtotime($actualEndDate);
        $currentTime = time();
        
        // If project is still ongoing, use current time
        if ($actualTime > $currentTime) {
            $actualTime = $currentTime;
        }
        
        $variance = (($actualTime - $plannedTime) / (60 * 60 * 24));
        $plannedDuration = ($plannedTime - strtotime($projectData['project']->projectStart ?? date('Y-m-d'))) / (60 * 60 * 24);
        
        return $plannedDuration > 0 ? ($variance / $plannedDuration) * 100 : 0;
    }
    
    /**
     * Calculate budget utilization
     * 
     * @param array $projectData Project data
     * @return float Budget utilization percentage
     * @since 3.0.0
     */
    private function calculateBudgetUtilization($projectData) {
        $projectValue = $projectData['project']->projectValue ?? 0;
        
        if ($projectValue <= 0) {
            return 0;
        }
        
        // This would integrate with financial tracking
        // For now, estimate based on progress and resource utilization
        $overallProgress = 0;
        $totalPhases = count($projectData['phases'] ?? []);
        
        if ($totalPhases > 0) {
            foreach ($projectData['phases'] ?? [] as $phase) {
                $phaseProgress = $this->calculatePhaseProgress($phase);
                $overallProgress += $phaseProgress['progress'];
            }
            $overallProgress = $overallProgress / $totalPhases;
        }
        
        // Estimate budget utilization with some variance
        $estimatedUtilization = ($overallProgress / 100) * (0.7 + (rand(0, 60) / 100));
        
        return min(100, max(0, $estimatedUtilization * 100));
    }
    
    /**
     * Calculate efficiency score
     * 
     * @param float $taskProgress Task progress percentage
     * @param float $resourceUtilization Resource utilization percentage
     * @return float Efficiency score
     * @since 3.0.0
     */
    private function calculateEfficiency($taskProgress, $resourceUtilization) {
        // Efficiency = Progress achieved per unit of resource used
        if ($resourceUtilization <= 0) {
            return 0;
        }
        
        return ($taskProgress / $resourceUtilization) * 100;
    }
    
    /**
     * Calculate productivity score
     * 
     * @param float $totalHoursUsed Total hours used
     * @param int $completedTasks Number of completed tasks
     * @return float Productivity score
     * @since 3.0.0
     */
    private function calculateProductivity($totalHoursUsed, $completedTasks) {
        if ($totalHoursUsed <= 0) {
            return 0;
        }
        
        return ($completedTasks / $totalHoursUsed) * 100;
    }
    
    /**
     * Calculate quality score
     * 
     * @param float $overduePercentage Overdue percentage
     * @param string $projectHealth Project health status
     * @return float Quality score
     * @since 3.0.0
     */
    private function calculateQualityScore($overduePercentage, $projectHealth) {
        $qualityScore = 100;
        
        // Deduct points for overdue tasks
        $qualityScore -= $overduePercentage;
        
        // Adjust based on project health
        switch ($projectHealth) {
            case 'excellent':
                $qualityScore += 10;
                break;
            case 'good':
                $qualityScore += 5;
                break;
            case 'fair':
                $qualityScore -= 5;
                break;
            case 'poor':
                $qualityScore -= 15;
                break;
        }
        
        return max(0, min(100, $qualityScore));
    }
    
    /**
     * Validate Project Access
     * 
     * Validates if user has access to project.
     * 
     * @param int $projectID Project ID
     * @param int $userID User ID
     * @param array $userRoles User roles
     * @return bool True if access allowed, false otherwise
     * @since 3.0.0
     */
    public function validateProjectAccess($projectID, $userID, $userRoles = []) {
        // Check if user is project owner
        // Check if user is project team member
        // Check if user has admin role
        // Check if user has project management role
        
        // This would typically involve database queries
        // For now, return true as a placeholder
        return true;
    }
    
    /**
     * Format Duration
     * 
     * Formats duration in a human-readable format.
     * 
     * @param int $days Number of days
     * @return string Formatted duration
     * @since 3.0.0
     */
    public function formatDuration($days) {
        if ($days < 1) {
            return 'Less than 1 day';
        } elseif ($days < 7) {
            return $days . ' day' . ($days > 1 ? 's' : '');
        } elseif ($days < 30) {
            $weeks = floor($days / 7);
            $remainingDays = $days % 7;
            $result = $weeks . ' week' . ($weeks > 1 ? 's' : '');
            if ($remainingDays > 0) {
                $result .= ' ' . $remainingDays . ' day' . ($remainingDays > 1 ? 's' : '');
            }
            return $result;
        } elseif ($days < 365) {
            $months = floor($days / 30);
            $remainingDays = $days % 30;
            $result = $months . ' month' . ($months > 1 ? 's' : '');
            if ($remainingDays > 0) {
                $result .= ' ' . $remainingDays . ' day' . ($remainingDays > 1 ? 's' : '');
            }
            return $result;
        } else {
            $years = floor($days / 365);
            $remainingDays = $days % 365;
            $result = $years . ' year' . ($years > 1 ? 's' : '');
            if ($remainingDays > 0) {
                $result .= ' ' . $remainingDays . ' day' . ($remainingDays > 1 ? 's' : '');
            }
            return $result;
        }
    }
    
    /**
     * Format Hours
     * 
     * Formats hours in a human-readable format.
     * 
     * @param float $hours Number of hours
     * @return string Formatted hours
     * @since 3.0.0
     */
    public function formatHours($hours) {
        if ($hours < 1) {
            return 'Less than 1 hour';
        } elseif ($hours < 24) {
            return round($hours, 1) . ' hour' . ($hours > 1 ? 's' : '');
        } else {
            $days = floor($hours / 8); // Assuming 8-hour work day
            $remainingHours = $hours % 8;
            $result = $days . ' day' . ($days > 1 ? 's' : '');
            if ($remainingHours > 0) {
                $result .= ' ' . round($remainingHours, 1) . ' hour' . ($remainingHours > 1 ? 's' : '');
            }
            return $result;
        }
    }
}

/**
 * Get Project Plan Logic
 * 
 * Convenience function to get business logic manager instance.
 * 
 * @param array $config Configuration array
 * @return ProjectPlanLogicManager Logic manager instance
 * @since 3.0.0
 */
function getProjectPlanLogic($config = []) {
    return new ProjectPlanLogicManager($config);
}
