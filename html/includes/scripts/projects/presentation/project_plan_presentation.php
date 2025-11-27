<?php
/**
 * Project Plan Presentation Layer
 * 
 * This file contains all presentation logic for the project plan system.
 * It handles HTML generation, UI components, and display formatting.
 * 
 * @package    PMS
 * @subpackage Projects
 * @author     SKM Development Team
 * @version    3.0.0
 * @since      1.0.0
 * 
 * Architecture:
 * =============
 * - HTML template generation
 * - UI component rendering
 * - Data formatting for display
 * - Responsive design handling
 * - Accessibility features
 */

// This file is meant to be included, not accessed directly

/**
 * Project Plan Presentation Manager Class
 * 
 * Centralized presentation logic for project plan UI.
 * Handles all HTML generation and UI components.
 * 
 * @class ProjectPlanPresentationManager
 * @since 3.0.0
 */
class ProjectPlanPresentationManager {
    
    /**
     * Configuration array
     * 
     * @var array $config
     * @since 3.0.0
     */
    private $config;
    
    /**
     * Theme settings
     * 
     * @var array $theme
     * @since 3.0.0
     */
    private $theme;
    
    /**
     * Constructor
     * 
     * @param array $config Configuration array
     * @since 3.0.0
     */
    public function __construct($config = []) {
        $this->config = $config;
        $this->theme = $this->initializeTheme();
    }
    
    /**
     * Initialize Theme
     * 
     * Sets up theme settings based on configuration.
     * 
     * @return array Theme settings
     * @since 3.0.0
     */
    private function initializeTheme() {
        return [
            'colors' => [
                'primary' => '#007bff',
                'secondary' => '#6c757d',
                'success' => '#28a745',
                'warning' => '#ffc107',
                'danger' => '#dc3545',
                'info' => '#17a2b8',
                'light' => '#f8f9fa',
                'dark' => '#343a40'
            ],
            'spacing' => [
                'xs' => '0.25rem',
                'sm' => '0.5rem',
                'md' => '1rem',
                'lg' => '1.5rem',
                'xl' => '3rem'
            ],
            'breakpoints' => [
                'sm' => '576px',
                'md' => '768px',
                'lg' => '992px',
                'xl' => '1200px'
            ]
        ];
    }
    
    /**
     * Render Project Plan Header
     * 
     * Renders the project plan header section.
     * 
     * @param array $projectData Project data
     * @return string HTML content
     * @since 3.0.0
     */
    public function renderProjectPlanHeader($projectData) {
        $project = $projectData['project'] ?? [];
        $timeline = $projectData['timeline'] ?? [];
        $metrics = $projectData['metrics'] ?? [];
        
        $projectName = htmlspecialchars($project->projectName ?? 'Unnamed Project', ENT_QUOTES, 'UTF-8');
        $projectCode = htmlspecialchars($project->projectCode ?? '', ENT_QUOTES, 'UTF-8');
        $clientName = htmlspecialchars($project->clientName ?? 'Unknown Client', ENT_QUOTES, 'UTF-8');
        $status = htmlspecialchars($project->projectStatus ?? 'Unknown', ENT_QUOTES, 'UTF-8');
        $startDate = $timeline['startDate'] ?? null;
        $endDate = $timeline['endDate'] ?? null;
        $duration = $timeline['duration'] ?? 0;
        $isOverdue = $timeline['isOverdue'] ?? false;
        
        $statusClass = $this->getStatusClass($status);
        $overdueClass = $isOverdue ? 'text-danger' : '';
        
        return '
        <div class="project-plan-header bg-white rounded shadow-sm mb-4">
            <div class="p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center mb-2">
                            <div class="project-icon me-3">
                                <i class="uil uil-folder-open text-primary fs-2"></i>
                            </div>
                            <div>
                                <h1 class="project-title h3 mb-1">' . $projectName . '</h1>
                                <div class="project-meta text-muted">
                                    <span class="project-code me-3">' . $projectCode . '</span>
                                    <span class="project-client me-3">
                                        <i class="uil uil-user me-1"></i>' . $clientName . '
                                    </span>
                                    <span class="project-status badge ' . $statusClass . '">' . $status . '</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="project-timeline">
                            <div class="timeline-item mb-2">
                                <small class="text-muted">Start Date:</small>
                                <div class="fw-semibold">' . ($startDate ? date('M j, Y', strtotime($startDate)) : 'Not set') . '</div>
                            </div>
                            <div class="timeline-item mb-2">
                                <small class="text-muted">End Date:</small>
                                <div class="fw-semibold ' . $overdueClass . '">' . ($endDate ? date('M j, Y', strtotime($endDate)) : 'Not set') . '</div>
                            </div>
                            <div class="timeline-item">
                                <small class="text-muted">Duration:</small>
                                <div class="fw-semibold">' . $this->formatDuration($duration) . '</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
    }
    
    /**
     * Render Project Metrics
     * 
     * Renders the project metrics section.
     * 
     * @param array $metrics Project metrics
     * @return string HTML content
     * @since 3.0.0
     */
    public function renderProjectMetrics($metrics) {
        // Basic metrics
        $phaseProgress = $metrics['phaseProgress'] ?? 0;
        $taskProgress = $metrics['taskProgress'] ?? 0;
        $totalPhases = $metrics['totalPhases'] ?? 0;
        $totalTasks = $metrics['totalTasks'] ?? 0;
        $overdueTasks = $metrics['overdueTasks'] ?? 0;
        $overduePercentage = $metrics['overduePercentage'] ?? 0;
        
        // Enhanced metrics
        $resourceUtilization = $metrics['resourceUtilization'] ?? 0;
        $totalHoursAllocated = $metrics['totalHoursAllocated'] ?? 0;
        $totalHoursUsed = $metrics['totalHoursUsed'] ?? 0;
        $teamUtilization = $metrics['teamUtilization'] ?? 0;
        $totalTeamMembers = $metrics['totalTeamMembers'] ?? 0;
        $activeTeamMembers = $metrics['activeTeamMembers'] ?? 0;
        
        // Project health indicators
        $projectHealth = $metrics['projectHealth'] ?? 'unknown';
        $riskLevel = $metrics['riskLevel'] ?? 'low';
        $scheduleVariance = $metrics['scheduleVariance'] ?? 0;
        $budgetUtilization = $metrics['budgetUtilization'] ?? 0;
        
        // Performance indicators
        $efficiency = $metrics['efficiency'] ?? 0;
        $productivity = $metrics['productivity'] ?? 0;
        $quality = $metrics['quality'] ?? 0;
        
        // Get health and risk colors
        $healthColor = $this->getHealthColor($projectHealth);
        $riskColor = $this->getRiskColor($riskLevel);
        
        // Calculate overall progress
        $overallProgress = round(($phaseProgress + $taskProgress) / 2, 1);
        
        // Create mini summary
        $miniSummary = $this->renderMiniMetricsSummary($metrics);
        
        // Create full enhanced metrics
        $fullMetrics = $this->renderFullEnhancedMetrics($metrics, $healthColor, $riskColor);
        
        return '
        <div class="project-metrics-container mb-4">
            ' . $miniSummary . '
            <div class="collapse mt-3" id="projectMetricsCollapse">
                ' . $fullMetrics . '
            </div>
        </div>';
    }
    
    /**
     * Render Mini Metrics Summary
     * 
     * Renders a compact one-line summary of project metrics.
     * 
     * @param array $metrics Project metrics
     * @return string HTML content
     * @since 3.0.0
     */
    private function renderMiniMetricsSummary($metrics) {
        $phaseProgress = $metrics['phaseProgress'] ?? 0;
        $taskProgress = $metrics['taskProgress'] ?? 0;
        $totalPhases = $metrics['totalPhases'] ?? 0;
        $totalTasks = $metrics['totalTasks'] ?? 0;
        $overdueTasks = $metrics['overdueTasks'] ?? 0;
        $overduePercentage = $metrics['overduePercentage'] ?? 0;
        $projectHealth = $metrics['projectHealth'] ?? 'unknown';
        $riskLevel = $metrics['riskLevel'] ?? 'low';
        $totalHoursUsed = $metrics['totalHoursUsed'] ?? 0;
        $totalHoursAllocated = $metrics['totalHoursAllocated'] ?? 0;
        $activeTeamMembers = $metrics['activeTeamMembers'] ?? 0;
        $totalTeamMembers = $metrics['totalTeamMembers'] ?? 0;
        
        $overallProgress = round(($phaseProgress + $taskProgress) / 2, 1);
        $healthColor = $this->getHealthColor($projectHealth);
        $riskColor = $this->getRiskColor($riskLevel);
        
        return '
        <div class="mini-metrics-summary bg-white rounded shadow-sm p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center flex-wrap">
                    <div class="metric-item me-4">
                        <span class="metric-value text-primary fw-bold">' . $totalPhases . '</span>
                        <span class="metric-label text-muted ms-1">phases</span>
                        <span class="badge bg-primary ms-2">' . round($phaseProgress, 1) . '%</span>
                    </div>
                    <div class="metric-item me-4">
                        <span class="metric-value text-info fw-bold">' . $totalTasks . '</span>
                        <span class="metric-label text-muted ms-1">tasks</span>
                        <span class="badge bg-info ms-2">' . round($taskProgress, 1) . '%</span>
                    </div>
                    ' . ($overdueTasks > 0 ? '
                    <div class="metric-item me-4">
                        <span class="metric-value text-warning fw-bold">' . $overdueTasks . '</span>
                        <span class="metric-label text-muted ms-1">overdue</span>
                        <span class="badge bg-warning ms-2">' . round($overduePercentage, 1) . '%</span>
                    </div>' : '') . '
                    <div class="metric-item me-4">
                        <span class="metric-value text-success fw-bold">' . $overallProgress . '%</span>
                        <span class="metric-label text-muted ms-1">progress</span>
                    </div>
                    <div class="metric-item me-4">
                        <span class="metric-label text-muted">Health:</span>
                        <span class="badge ' . str_replace('text-', 'bg-', $healthColor) . ' ms-1">' . ucfirst($projectHealth) . '</span>
                    </div>
                    <div class="metric-item me-4">
                        <span class="metric-label text-muted">Risk:</span>
                        <span class="badge ' . str_replace('text-', 'bg-', $riskColor) . ' ms-1">' . ucfirst($riskLevel) . '</span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-value text-secondary fw-bold">' . round($totalHoursUsed, 1) . 'h</span>
                        <span class="metric-label text-muted ms-1">of ' . round($totalHoursAllocated, 1) . 'h</span>
                    </div>
                </div>
                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#projectMetricsCollapse" aria-expanded="false" aria-controls="projectMetricsCollapse">
                    <i class="uil uil-angle-down me-1"></i>
                    <span class="toggle-text">View Details</span>
                </button>
            </div>
        </div>';
    }
    
    /**
     * Render Full Enhanced Metrics
     * 
     * Renders the complete enhanced metrics display.
     * 
     * @param array $metrics Project metrics
     * @param string $healthColor Health color class
     * @param string $riskColor Risk color class
     * @return string HTML content
     * @since 3.0.0
     */
    private function renderFullEnhancedMetrics($metrics, $healthColor, $riskColor) {
        $phaseProgress = $metrics['phaseProgress'] ?? 0;
        $taskProgress = $metrics['taskProgress'] ?? 0;
        $totalPhases = $metrics['totalPhases'] ?? 0;
        $totalTasks = $metrics['totalTasks'] ?? 0;
        $overdueTasks = $metrics['overdueTasks'] ?? 0;
        $overduePercentage = $metrics['overduePercentage'] ?? 0;
        $resourceUtilization = $metrics['resourceUtilization'] ?? 0;
        $totalHoursAllocated = $metrics['totalHoursAllocated'] ?? 0;
        $totalHoursUsed = $metrics['totalHoursUsed'] ?? 0;
        $teamUtilization = $metrics['teamUtilization'] ?? 0;
        $totalTeamMembers = $metrics['totalTeamMembers'] ?? 0;
        $activeTeamMembers = $metrics['activeTeamMembers'] ?? 0;
        $projectHealth = $metrics['projectHealth'] ?? 'unknown';
        $riskLevel = $metrics['riskLevel'] ?? 'low';
        $scheduleVariance = $metrics['scheduleVariance'] ?? 0;
        $budgetUtilization = $metrics['budgetUtilization'] ?? 0;
        $efficiency = $metrics['efficiency'] ?? 0;
        $productivity = $metrics['productivity'] ?? 0;
        $quality = $metrics['quality'] ?? 0;
        
        return '
        <div class="project-metrics-enhanced">
            <!-- Primary Metrics Row -->
            <div class="row g-3 mb-3">
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card bg-white rounded shadow-sm p-3 text-center">
                        <div class="metric-icon text-primary mb-2">
                            <i class="uil uil-layers fs-2"></i>
                        </div>
                        <div class="metric-value h4 mb-1">' . $totalPhases . '</div>
                        <div class="metric-label text-muted">Total Phases</div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-primary" style="width: ' . $phaseProgress . '%"></div>
                        </div>
                        <small class="text-muted">' . round($phaseProgress, 1) . '% Complete</small>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card bg-white rounded shadow-sm p-3 text-center">
                        <div class="metric-icon text-info mb-2">
                            <i class="uil uil-check-circle fs-2"></i>
                        </div>
                        <div class="metric-value h4 mb-1">' . $totalTasks . '</div>
                        <div class="metric-label text-muted">Total Tasks</div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-info" style="width: ' . $taskProgress . '%"></div>
                        </div>
                        <small class="text-muted">' . round($taskProgress, 1) . '% Complete</small>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card bg-white rounded shadow-sm p-3 text-center">
                        <div class="metric-icon text-warning mb-2">
                            <i class="uil uil-clock fs-2"></i>
                        </div>
                        <div class="metric-value h4 mb-1">' . $overdueTasks . '</div>
                        <div class="metric-label text-muted">Overdue Tasks</div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-warning" style="width: ' . $overduePercentage . '%"></div>
                        </div>
                        <small class="text-muted">' . round($overduePercentage, 1) . '% Overdue</small>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card bg-white rounded shadow-sm p-3 text-center">
                        <div class="metric-icon text-success mb-2">
                            <i class="uil uil-chart-line fs-2"></i>
                        </div>
                        <div class="metric-value h4 mb-1">' . round(($phaseProgress + $taskProgress) / 2, 1) . '%</div>
                        <div class="metric-label text-muted">Overall Progress</div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-success" style="width: ' . (($phaseProgress + $taskProgress) / 2) . '%"></div>
                        </div>
                        <small class="text-muted">Project Health</small>
                    </div>
                </div>
            </div>
            
            <!-- Enhanced Metrics Row -->
            <div class="row g-3 mb-3">
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card bg-white rounded shadow-sm p-3 text-center">
                        <div class="metric-icon text-secondary mb-2">
                            <i class="uil uil-clock-eight fs-2"></i>
                        </div>
                        <div class="metric-value h5 mb-1">' . round($totalHoursUsed, 1) . 'h</div>
                        <div class="metric-label text-muted">Hours Used</div>
                        <div class="progress mt-2" style="height: 3px;">
                            <div class="progress-bar bg-secondary" style="width: ' . $resourceUtilization . '%"></div>
                        </div>
                        <small class="text-muted">of ' . round($totalHoursAllocated, 1) . 'h allocated</small>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card bg-white rounded shadow-sm p-3 text-center">
                        <div class="metric-icon text-primary mb-2">
                            <i class="uil uil-users-alt fs-2"></i>
                        </div>
                        <div class="metric-value h5 mb-1">' . $activeTeamMembers . '</div>
                        <div class="metric-label text-muted">Active Members</div>
                        <div class="progress mt-2" style="height: 3px;">
                            <div class="progress-bar bg-primary" style="width: ' . $teamUtilization . '%"></div>
                        </div>
                        <small class="text-muted">of ' . $totalTeamMembers . ' total</small>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card bg-white rounded shadow-sm p-3 text-center">
                        <div class="metric-icon ' . $healthColor . ' mb-2">
                            <i class="uil uil-heart-medical fs-2"></i>
                        </div>
                        <div class="metric-value h5 mb-1">' . ucfirst($projectHealth) . '</div>
                        <div class="metric-label text-muted">Project Health</div>
                        <div class="progress mt-2" style="height: 3px;">
                            <div class="progress-bar ' . $healthColor . '" style="width: ' . $this->getHealthPercentage($projectHealth) . '%"></div>
                        </div>
                        <small class="text-muted">Overall Status</small>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card bg-white rounded shadow-sm p-3 text-center">
                        <div class="metric-icon ' . $riskColor . ' mb-2">
                            <i class="uil uil-shield-exclamation fs-2"></i>
                        </div>
                        <div class="metric-value h5 mb-1">' . ucfirst($riskLevel) . '</div>
                        <div class="metric-label text-muted">Risk Level</div>
                        <div class="progress mt-2" style="height: 3px;">
                            <div class="progress-bar ' . $riskColor . '" style="width: ' . $this->getRiskPercentage($riskLevel) . '%"></div>
                        </div>
                        <small class="text-muted">' . round($scheduleVariance, 1) . '% variance</small>
                    </div>
                </div>
            </div>
            
            <!-- Performance Indicators Row -->
            <div class="row g-3">
                <div class="col-lg-4 col-md-6">
                    <div class="metric-card bg-white rounded shadow-sm p-3 text-center">
                        <div class="metric-icon text-success mb-2">
                            <i class="uil uil-tachometer-fast fs-2"></i>
                        </div>
                        <div class="metric-value h5 mb-1">' . round($efficiency, 1) . '%</div>
                        <div class="metric-label text-muted">Efficiency</div>
                        <div class="progress mt-2" style="height: 3px;">
                            <div class="progress-bar bg-success" style="width: ' . $efficiency . '%"></div>
                        </div>
                        <small class="text-muted">Progress per Resource</small>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="metric-card bg-white rounded shadow-sm p-3 text-center">
                        <div class="metric-icon text-info mb-2">
                            <i class="uil uil-chart-bar fs-2"></i>
                        </div>
                        <div class="metric-value h5 mb-1">' . round($productivity, 1) . '</div>
                        <div class="metric-label text-muted">Productivity</div>
                        <div class="progress mt-2" style="height: 3px;">
                            <div class="progress-bar bg-info" style="width: ' . min(100, $productivity) . '%"></div>
                        </div>
                        <small class="text-muted">Tasks per Hour</small>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="metric-card bg-white rounded shadow-sm p-3 text-center">
                        <div class="metric-icon text-warning mb-2">
                            <i class="uil uil-award fs-2"></i>
                        </div>
                        <div class="metric-value h5 mb-1">' . round($quality, 1) . '%</div>
                        <div class="metric-label text-muted">Quality Score</div>
                        <div class="progress mt-2" style="height: 3px;">
                            <div class="progress-bar bg-warning" style="width: ' . $quality . '%"></div>
                        </div>
                        <small class="text-muted">' . round($budgetUtilization, 1) . '% Budget Used</small>
                    </div>
                </div>
            </div>
        </div>';
    }
    
    /**
     * Render Phase List
     * 
     * Renders the list of project phases.
     * 
     * @param array $phases Phases data
     * @return string HTML content
     * @since 3.0.0
     */
    public function renderPhaseList($phases) {
        if (empty($phases)) {
            return $this->renderEmptyState('phases', 'No phases found for this project.');
        }
        
        $html = '<div class="phases-list">';
        
        foreach ($phases as $phase) {
            // var_dump($phase);
            $html .= $this->renderPhaseCard($phase);
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Render Phase Card
     * 
     * Renders a single phase card.
     * 
     * @param array $phase Phase data
     * @return string HTML content
     * @since 3.0.0
     */
    public function renderPhaseCard($phase) {
        // var_dump($phase);
        $phaseId = htmlspecialchars($phase['id'] ?? '', ENT_QUOTES, 'UTF-8');
        $phaseName = htmlspecialchars($phase['name'] ?? 'Unnamed Phase', ENT_QUOTES, 'UTF-8');
        $phaseDescription = htmlspecialchars($phase['description'] ?? '', ENT_QUOTES, 'UTF-8');
        $phaseWeighting = $phase['weighting'] ?? 0;
        $phaseWorkHours = $phase['workHours'] ?? 0;
        $phaseStartDate = $phase['startDate'] ?? null;
        $phaseEndDate = $phase['endDate'] ?? null;
        $phaseDuration = $phase['duration'] ?? 0;
        $phaseProgress = $phase['progress'] ?? 0;
        $phaseIsOverdue = $phase['isOverdue'] ?? false;
        $phaseTasks = $phase['tasks'] ?? [];
       
        $phaseIsCollapsed = $phase['isCollapsed'] ?? false;
        $phaseBillingMilestone = $phase['billingMilestone'] ?? 0;
        $overdueClass = $phaseIsOverdue ? 'border-warning' : '';
        $collapsedClass = $phaseIsCollapsed ? 'collapsed' : '';
        $progressClass = $this->getProgressClass($phaseProgress);
        $projectId = $phase['projectId'] ?? '';
        $clientId = $phase['clientId'] ?? '';
        $projectPhaseId = $phase['projectPhaseId'] ?? '';
        // $phaseTasks['phaseId'] = $phaseId;
        // $phaseTasks['projectPhaseId'] = $projectPhaseId;
        // $phaseTasks['projectId'] = $projectId;
        // $phaseTasks['clientId'] = $clientId;
        if(isset($phase['tasks']) && is_array($phase['tasks'])) {
            foreach($phase['tasks'] as $key => $task) {

                // var_dump($task);
                $phaseTasks[$key]['phaseId'] = $phaseId;
               
                $phaseTasks[$key]['projectId'] = $projectId;
                $phaseTasks[$key]['clientId'] = $clientId;
                $phaseTasks[$key]['taskId'] = $task['id'];
                $phaseTasks[$key]['projectTaskCode'] = $task['projectTaskCode'];
              
            }
        } else {
            $phaseTasks = [];
        } 

        $html = '
        <div class="phase-card bg-white rounded shadow-lg my-4 ' . $overdueClass . ' ' . $collapsedClass . '" data-phase-id="' . $phaseId . '">
            <div class="phase-header p-3 border-bottom">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <button class="btn btn-link p-0 me-2 phase-toggle" data-bs-toggle="collapse" data-bs-target="#phase-' . $phaseId . '-content">
                            <i class="uil uil-angle-down transition-transform"></i>
                        </button>
                        <div>
                            <h3 class="phase-title h5 mb-1">' . $phaseName . '</h3>
                            <div class="phase-meta text-muted small">
                                <span class="me-3">Weighting: ' . $phaseWeighting . '%</span>
                                <span class="me-3">Hours: ' . $this->formatHours($phaseWorkHours) . '</span>
                                <span class="me-3">Duration: ' . $this->formatDuration($phaseDuration) . '</span>
                                <span class="me-3">Start: ' . ($phaseStartDate ? date('M j, Y', strtotime($phaseStartDate)) : 'Not set') . '</span>
                                <span>End: ' . ($phaseEndDate ? date('M j, Y', strtotime($phaseEndDate)) : 'Not set') . '</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="phase-progress me-3">
                            <div class="progress" style="width: 100px; height: 8px;">
                                <div class="progress-bar ' . $progressClass . '" style="width: ' . $phaseProgress . '%"></div>
                            </div>
                            <small class="text-muted">' . round($phaseProgress, 1) . '%</small>
                        </div>
                        <div class="phase-actions">
                            <button 
                                class="btn btn-sm btn-outline-primary me-1 editPhaseBtn" 
                                data-bs-toggle="modal" 
                                data-bs-target="#manage_project_phase"
                                data-bs-toggle="tooltip" 
                                data-bs-placement="top" 
                                title="Edit Phase Details"
                                data-project-phase-id="' . $phaseId . '"
                                data-project-id="' . $projectId . '"
                                data-project-phase-name="' . $phaseName . '"
                                data-phase-work-hrs="' . $phaseWorkHours . '"
                                data-phase-weighting="' . $phaseWeighting . '"
                                data-phase-start-date="' . $phaseStartDate . '"
                                data-phase-end-date="' . $phaseEndDate . '"
                                data-billing-milestone="' . $phaseBillingMilestone . '"
                            >
                                <i class="uil uil-edit"></i>
                            </button>
                            <button 
                                class="btn btn-sm btn-outline-success addTaskBtn" 
                                data-bs-toggle="modal" 
                                data-bs-target="#manage_project_task"
                                data-bs-toggle="tooltip" 
                                data-bs-placement="top" 
                                title="Add Task to Phase"
                                data-project-phase-id="' . $phaseId . '"
                                data-project-id="' . $projectId . '"
                                data-client-id="' . $clientId . '"
                                data-project-phase-id="' . $projectPhaseId . '"
                                data-phase-start-date="' . $phaseStartDate . '"
                                data-phase-end-date="' . $phaseEndDate . '"

                            >
                                <i class="uil uil-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="collapse phase-content show" id="phase-' . $phaseId . '-content">
                <div class="p-3">
                    ' . $this->renderPhaseDescription($phaseDescription) . '
                    ' . $this->renderPhaseTasks($phaseTasks, $phaseId, $projectId, $clientId, $phaseStartDate, $phaseEndDate) . '
                </div>
            </div>
        </div>';
        
        return $html;
    }
    
    /**
     * Render Phase Description
     * 
     * Renders phase description section.
     * 
     * @param string $description Phase description
     * @return string HTML content
     * @since 3.0.0
     */
    public function renderPhaseDescription($description) {
        if (empty($description)) {
            return '';
        }
        
        return '
        <div class="phase-description mb-3">
            <h6 class="text-muted mb-2">Description</h6>
            <p class="mb-0">' . nl2br(htmlspecialchars($description, ENT_QUOTES, 'UTF-8')) . '</p>
        </div>';
    }
    
    /**
     * Render Phase Tasks
     * 
     * Renders tasks within a phase.
     * 
     * @param array $tasks Tasks data
     * @return string HTML content
     * @since 3.0.0
     */
    public function renderPhaseTasks($tasks, $phaseId, $projectId, $clientId, $phaseStartDate, $phaseEndDate) {
        if (empty($tasks)) {
            return $this->renderEmptyState('tasks', 'No tasks found for this phase.');
        }
        
        $phaseId = $phaseId ?? '';
        $clientId = $clientId ?? '';
        $projectId = $projectId ?? '';
        $projectPhaseId = $phaseId; // Use the passed phaseId instead of trying to get it from tasks
        $html = '
        <div class="phase-tasks">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="text-muted mb-0">Tasks (' . count($tasks) . ')</h6>
                <button class="btn btn-sm btn-outline-primary addTaskBtn" 
                    data-bs-toggle="modal" 
                    data-bs-target="#manage_project_task"
                    data-bs-toggle="tooltip" 
                    data-bs-placement="top" 
                    title="Add New Task"
                    data-project-phase-id="' . $phaseId . '"
                    data-client-id="' . $clientId . '"
                    data-project-id="' . $projectId . '"
                    data-project-phase-id="' . $projectPhaseId . '"
                    data-phase-start-date="' . $phaseStartDate . '"
                    data-phase-end-date="' . $phaseEndDate . '"
                >
                    <i class="uil uil-plus me-1"></i>Add Task
                </button>
            </div>
            <div class="tasks-list">';
        
                foreach ($tasks as $task) {
                    // var_dump($task);
                    $html .= $this->renderTaskCard($task);
                }
                
                $html .= '
            </div>
        </div>';
        
        return $html;
    }
    
    /**
     * Render Task Card
     * 
     * Renders a single task card.
     * 
     * @param array $task Task data
     * @return string HTML content
     * @since 3.0.0
     */
    public function renderTaskCard($task) {

        // var_dump($task);
        $taskId = htmlspecialchars($task['id'] ?? '', ENT_QUOTES, 'UTF-8');
        $taskName = htmlspecialchars($task['name'] ?? 'Unnamed Task', ENT_QUOTES, 'UTF-8');
        $taskDescription = htmlspecialchars(Utility::clean_string($task['description']) ?? '', ENT_QUOTES, 'UTF-8');
        $taskWeighting = $task['weighting'] ?? 0;
        $taskHoursAllocated = $task['hoursAllocated'] ?? 0;
        $taskStartDate = $task['startDate'] ?? null;
        $taskDeadline = $task['deadline'] ?? null;
        $taskDuration = $task['duration'] ?? 0;
        $taskProgress = $task['progress'] ?? 0;
        $taskIsOverdue = $task['isOverdue'] ?? false;
        $taskStatus = $task['status'] ?? 1;
        $taskSubtasks = $task['subtasks'] ?? [];
        $taskAssignees = $task['assignees'] ?? [];
        $phaseId = $task['projectPhaseId'] ?? '';
        $projectId = $task['projectId'] ?? '';
        $clientId = $task['clientId'] ?? '';
        $taskTypeId = $task['projectTaskTypeId'] ?? '';
        $taskStatusId = $task['taskStatusID'] ?? '';
        $taskCode = $task['projectTaskCode'] ?? '';

        // $taskSubtasks ? var_dump($taskSubtasks) : null;
        $assigneeIDs =array();
        foreach($taskAssignees as $assignee) {
            $assigneeIDs[] = $assignee['userId'];
        }
        // var_dump($assigneeIDs);
        
        $overdueClass = $taskIsOverdue ? 'border-warning' : '';
        $progressClass = $this->getProgressClass($taskProgress);
        $statusClass = $this->getTaskStatusClass($taskStatus);
        
        $html = '
        <div class="task-card bg-light rounded border shadow-lg mb-2 ' . $overdueClass . '" data-task-id="' . $taskId . '" data-project-id="' . $projectId . '">
            <div class="task-header p-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="task-status me-2">
                            <span class="badge ' . $statusClass . '">' . $this->getTaskStatusText($taskStatus) . '</span>
                        </div>
                        <div>
                            <h6 class="task-title mb-1">' . $taskName . '</h6>
                            <div class="task-meta text-muted small">
                                <span class="me-3">Weighting: ' . $taskWeighting . '%</span>
                                <span class="me-3">Hours: ' . $this->formatHours($taskHoursAllocated) . '</span>
                                <span class="me-3">Duration: ' . $this->formatDuration($taskDuration) . '</span>
                                <span class="me-3">Start: ' . ($taskStartDate ? date('M j, Y', strtotime($taskStartDate)) : 'Not set') . '</span>
                                <span>Deadline: ' . ($taskDeadline ? date('M j, Y', strtotime($taskDeadline)) : 'Not set') . '</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="task-progress me-3">
                            <div class="progress" style="width: 80px; height: 6px;">
                                <div class="progress-bar ' . $progressClass . '" style="width: ' . $taskProgress . '%"></div>
                            </div>
                            <small class="text-muted">' . round($taskProgress, 1) . '%</small>
                        </div>
                        <div class="task-actions">
                            <button class="btn btn-sm btn-outline-primary me-1 editTaskBtn" 
                                data-bs-toggle="modal" 
                                data-bs-target="#manage_project_task"
                                data-bs-toggle="tooltip" 
                                data-bs-placement="top" 
                                title="Edit Task Details"
                                data-project-task-id="' . $taskId . '"
                                data-project-phase-id="' . $phaseId . '"
                                data-project-id="' . $projectId . '"
                                data-client-id="' . $clientId . '"
                                data-project-task-name="' . $taskName . '"
                                data-task-description="' . $taskDescription . '"
                                data-task-weighting="' . $taskWeighting . '"
                                data-task-hours-allocated="' . $taskHoursAllocated . '"
                                data-task-start="' . $taskStartDate . '"
                                data-task-deadline="' . $taskDeadline . '"
                                data-task-duration="' . $taskDuration . '"
                                data-project-task-type-id="' . $taskTypeId . '"
                                data-task-status-id="' . $taskStatusId . '"
                                data-project-task-code="' . $taskCode . '"
                                data-assignees="' . json_encode($assigneeIDs) . '"
                                >
                                <i class="uil uil-edit"></i>
                            </button>
                            <button 
                                class="btn btn-sm btn-outline-success manageSubtaskBtn" 
                                data-bs-toggle="modal" 
                                data-bs-target="#manage_task_step"
                                data-bs-toggle="tooltip" 
                                data-bs-placement="top" 
                                title="Add Subtask / Task Activities"
                                data-project-task-id="' . $taskId . '"
                                data-project-phase-id="' . $phaseId . '"
                                data-assignees="' . json_encode($assigneeIDs) . '"
                                >
                                <i class="uil uil-plus"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-info addAssigneeBtn" 
                                data-bs-toggle="modal" 
                                data-bs-target="#manage_project_task_assignments"
                                data-bs-toggle="tooltip" 
                                data-bs-placement="top" 
                                title="Add Assignees"
                                data-project-task-id="' . $taskId . '"
                                data-project-id="' . $projectId . '"
                                data-assignees="' . json_encode($assigneeIDs) . '"
                                >
                                <i class="uil uil-user"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="task-content p-3 pt-0">
                <div class="col-12 d-flex flex-wrap gap-2 justify-content-between ">
                ' . $this->renderTaskDescription($taskDescription) . '
                ' . $this->renderTaskAssignees($taskAssignees) . '
                </div>
                <div class="col-12">
                ' . $this->renderTaskSubtasks($taskSubtasks) . '
                </div>
            </div>
        </div>';
        // var_dump($taskAssignees);
        return $html;
    }
    
    /**
     * Render Task Description
     * 
     * Renders task description section.
     * 
     * @param string $description Task description
     * @return string HTML content
     * @since 3.0.0
     */
    public function renderTaskDescription($description) {
        if (empty($description)) {
            return '';
        }

        $description=Utility::clean_string($description);
        // var_dump($description);
        
        return '
        <div class="task-description mb-3">
            <h6 class="text-muted mb-2 border-bottom">Description</h6>
            <p class="mb-0 small">' . $description . '</p>
        </div>';
    }
    
    /**
     * Render Task Assignees
     * 
     * Renders task assignees section.
     * 
     * @param array $assignees Assignees data
     * @return string HTML content
     * @since 3.0.0
     */
    public function renderTaskAssignees($assignees) {
        if (empty($assignees)) {
            return '';
        }
        $html = '
        <div class="task-assignees mb-3">
            <h6 class="text-muted mb-2">Assignees</h6>
            <div class="d-flex flex-wrap gap-2">';
        
        foreach ($assignees as $assignee) {

            // var_dump($assignee['projectID']);
            $assigneeName = htmlspecialchars($assignee['name'] ?? 'Unknown', ENT_QUOTES, 'UTF-8');
            $assigneeInitials = htmlspecialchars($assignee['initials'] ?? '?', ENT_QUOTES, 'UTF-8');
            $assigneeAvatar = $assignee['avatar'] ?? '';
            $assigneeId = htmlspecialchars($assignee['userId'] ?? '', ENT_QUOTES, 'UTF-8');
            
            $html .= '
                <div class="assignee-container position-relative" 
                     data-assignee-id="' . $assigneeId . '" 
                     data-assignee-name="' . $assigneeName . '">
                    <div class="assignee-avatar" data-bs-toggle="tooltip" title="' . $assigneeName . '">
                        ' . ($assigneeAvatar ? 
                            '<img src="' . htmlspecialchars($assigneeAvatar, ENT_QUOTES, 'UTF-8') . '" alt="' . $assigneeName . '" class="rounded-circle" style="width: 32px; height: 32px;">' :
                            '<div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 12px;">' . $assigneeInitials . '</div>'
                        ) . '
                    </div>
                    <button class="btn btn-sm btn-danger assignee-delete-btn position-absolute top-0 end-0 translate-middle removeAssigneeFromTask" 
                            style="width: 16px; height: 16px; padding: 0; border-radius: 50%; font-size: 10px; display: none; z-index: 10;"
                            data-assignment-id="' . $assignee['assignmentId'] . '"
                            data-assignee-id="' . $assignee['userId'] . '"
                           
                            data-project-id="' . $assignee['projectID'] . '"
                            data-bs-toggle="tooltip" 
                            title="Remove ' . $assigneeName . ' from task"
                           >
                        <i class="uil uil-times"></i>
                    </button>
                </div>';
        }
        
        $html .= '
            </div>
        </div>';
        
        return $html;
    }
    
    /**
     * Render Task Subtasks
     * 
     * Renders subtasks within a task.
     * 
     * @param array $subtasks Subtasks data
     * @return string HTML content
     * @since 3.0.0
     */
    public function renderTaskSubtasks($subtasks) {
        if (empty($subtasks)) {
            return '';
        }
        
        $html = '
        <div class="task-subtasks">
            <h6 class="text-muted mb-2">Subtasks (' . count($subtasks) . ')</h6>
            <div class="subtasks-list">';
        
        foreach ($subtasks as $subtask) {
            // var_dump($subtask);
            $html .= $this->renderSubtaskCard($subtask);
        }
        
        $html .= '
            </div>
        </div>';
        
        return $html;
    }
    
    /**
     * Render Subtask Card
     * 
     * Renders a single subtask card.
     * 
     * @param array $subtask Subtask data
     * @return string HTML content
     * @since 3.0.0
     */
    public function renderSubtaskCard($subtask) {

  
        $subtaskId = htmlspecialchars($subtask['id'] ?? '', ENT_QUOTES, 'UTF-8');
        $subtaskName = htmlspecialchars($subtask['name'] ?? 'Unnamed Subtask', ENT_QUOTES, 'UTF-8');
        $subtaskDescription = htmlspecialchars($subtask['description'] ?? '', ENT_QUOTES, 'UTF-8');
        $subtaskDueDate = $subtask['dueDate'] ?? null;
        $subtaskProgress = $subtask['progress'] ?? 0;
        $subtaskIsOverdue = $subtask['isOverdue'] ?? false;
        $subtaskAssignee = $subtask['assignee'] ?? null;
        $subtaskAssigneeName = $subtask['assigneeName'] ?? '';
        $subtaskAssigneeInitials = $subtask['assigneeInitials'] ?? '';
        $overdueClass = $subtaskIsOverdue ? 'border-warning' : '';
        $progressClass = $this->getProgressClass($subtaskProgress);
        $projectTaskId = $subtask['projectTaskId'] ?? '';
        $subtaskStatusId = $subtask['taskStatusID'] ?? '';
        $subtaskAllocatedWorkHours = $subtask['allocatedWorkHours'] ?? 0;
        $subtaskProjectTaskId = $subtask['projectTaskId'] ?? '';
        // var_dump($subtask);
        
        $html = '
        <div class="subtask-card bg-white rounded border-bottom mb-2 ' . $overdueClass . '" data-subtask-id="' . $subtaskId . '">
            <div class="subtask-header p-2">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="form-check me-2">
                            <input class="form-check-input" type="checkbox" ' . ($subtaskProgress >= 100 ? 'checked' : '') . '>
                        </div>
                        <div>
                            <h6 class="subtask-title mb-1 small">' . $subtaskName . '</h6>
                            <div class="subtask-meta text-muted small">
                                <span class="me-3">Due: ' . ($subtaskDueDate ? date('M j, Y', strtotime($subtaskDueDate)) : 'Not set') . '</span>
                                ' . ($subtaskAssigneeName ? '<span>Assignee: ' . htmlspecialchars($subtaskAssigneeName ?? 'Unknown', ENT_QUOTES, 'UTF-8') . '</span>' : '') . '
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                    <div class="subtask-assignee me-2">
                            <span class="text-muted small text-primary">Assignee: ' . $subtaskAssigneeName . '  (' . $subtaskAssigneeInitials . ')</span>
                        </div>

                        <div class="subtask-progress me-2">
                            <div class="progress" style="width: 60px; height: 4px;">
                                <div class="progress-bar ' . $progressClass . '" style="width: ' . $subtaskProgress . '%"></div>
                            </div>
                            <small class="text-muted">' . round($subtaskProgress, 1) . '%</small>
                        </div>
                        <div class="subtask-actions">
                            <button class="btn btn-sm btn-outline-primary me-1 editSubtaskBtn" 
                                data-bs-toggle="modal" 
                                data-bs-target="#manage_task_step"
                                data-bs-toggle="tooltip" 
                                data-bs-placement="top" 
                                title="Edit Subtask"
                                data-subtask-id="' . $subtaskId . '"
                                 data-project-task-id="' . $subtaskProjectTaskId . '"
                               
                                data-sub-task-name="' . $subtaskName . '"
                                data-sub-task-description="' . $subtaskDescription . '"
                                 data-sub-task-allocated-work-hours="' . $subtaskAllocatedWorkHours . '"
                                data-subtask-due-date="' . $subtaskDueDate . '"
                                data-sub-task-progress="' . $subtaskProgress . '"
                                data-assignee="' . $subtaskAssignee . '"
                                data-sub-task-status-id="' . $subtaskStatusId . '"
                               
                                >
                                <i class="uil uil-edit"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            ' . ($subtaskDescription ? '
            <div class="subtask-content p-2 pt-0">
                <p class="mb-0 small text-muted">' . nl2br(htmlspecialchars($subtaskDescription, ENT_QUOTES, 'UTF-8')) . '</p>
            </div>' : '') . '
        </div>';
        
        return $html;
    }
    
    /**
     * Render Empty State
     * 
     * Renders empty state message.
     * 
     * @param string $type Type of empty state
     * @param string $message Empty state message
     * @return string HTML content
     * @since 3.0.0
     */
    public function renderEmptyState($type, $message) {
        $icons = [
            'phases' => 'uil-layers',
            'tasks' => 'uil-check-circle',
            'subtasks' => 'uil-list-ul',
            'default' => 'uil-inbox'
        ];
        
        $icon = $icons[$type] ?? $icons['default'];
        
        return '
        <div class="empty-state text-center py-5">
            <div class="empty-state-icon mb-3">
                <i class="uil ' . $icon . ' text-muted" style="font-size: 3rem;"></i>
            </div>
            <h5 class="text-muted mb-2">' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</h5>
            <p class="text-muted small">Get started by adding your first ' . $type . '.</p>
        </div>';
    }
    
    /**
     * Get Status Class
     * 
     * Gets CSS class for status.
     * 
     * @param string $status Status value
     * @return string CSS class
     * @since 3.0.0
     */
    private function getStatusClass($status) {
        $statusClasses = [
            'Active' => 'bg-success',
            'Inactive' => 'bg-secondary',
            'Completed' => 'bg-primary',
            'Cancelled' => 'bg-danger',
            'On Hold' => 'bg-warning',
            'default' => 'bg-secondary'
        ];
        
        return $statusClasses[$status] ?? $statusClasses['default'];
    }
    
    /**
     * Get Task Status Class
     * 
     * Gets CSS class for task status.
     * 
     * @param int $status Task status ID
     * @return string CSS class
     * @since 3.0.0
     */
    private function getTaskStatusClass($status) {
        $statusClasses = [
            1 => 'bg-secondary', // Not Started
            2 => 'bg-info',      // In Progress
            3 => 'bg-warning',   // On Hold
            4 => 'bg-primary',   // Under Review
            5 => 'bg-success',   // Completed
            6 => 'bg-success'    // Completed
        ];
        
        return $statusClasses[$status] ?? $statusClasses[1];
    }
    
    /**
     * Get Task Status Text
     * 
     * Gets text for task status.
     * 
     * @param int $status Task status ID
     * @return string Status text
     * @since 3.0.0
     */
    private function getTaskStatusText($status) {
        $statusTexts = [
            1 => 'Not Started',
            2 => 'In Progress',
            3 => 'On Hold',
            4 => 'Under Review',
            5 => 'Completed',
            6 => 'Completed'
        ];
        
        return $statusTexts[$status] ?? 'Unknown';
    }
    
    /**
     * Get Progress Class
     * 
     * Gets CSS class for progress.
     * 
     * @param float $progress Progress value
     * @return string CSS class
     * @since 3.0.0
     */
    private function getProgressClass($progress) {
        if ($progress >= 100) {
            return 'bg-success';
        } elseif ($progress >= 75) {
            return 'bg-primary';
        } elseif ($progress >= 50) {
            return 'bg-info';
        } elseif ($progress >= 25) {
            return 'bg-warning';
        } else {
            return 'bg-secondary';
        }
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
    private function formatDuration($days) {
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
    private function formatHours($hours) {
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
    
    /**
     * Get Health Color
     * 
     * Gets CSS color class for project health status.
     * 
     * @param string $health Health status
     * @return string CSS color class
     * @since 3.0.0
     */
    private function getHealthColor($health) {
        $healthColors = [
            'excellent' => 'text-success',
            'good' => 'text-primary',
            'fair' => 'text-warning',
            'poor' => 'text-danger',
            'unknown' => 'text-secondary'
        ];
        
        return $healthColors[$health] ?? $healthColors['unknown'];
    }
    
    /**
     * Get Risk Color
     * 
     * Gets CSS color class for risk level.
     * 
     * @param string $risk Risk level
     * @return string CSS color class
     * @since 3.0.0
     */
    private function getRiskColor($risk) {
        $riskColors = [
            'low' => 'text-success',
            'medium' => 'text-warning',
            'high' => 'text-danger'
        ];
        
        return $riskColors[$risk] ?? $riskColors['low'];
    }
    
    /**
     * Get Health Percentage
     * 
     * Gets percentage value for health status display.
     * 
     * @param string $health Health status
     * @return int Percentage value
     * @since 3.0.0
     */
    private function getHealthPercentage($health) {
        $healthPercentages = [
            'excellent' => 100,
            'good' => 80,
            'fair' => 60,
            'poor' => 30,
            'unknown' => 0
        ];
        
        return $healthPercentages[$health] ?? $healthPercentages['unknown'];
    }
    
    /**
     * Get Risk Percentage
     * 
     * Gets percentage value for risk level display.
     * 
     * @param string $risk Risk level
     * @return int Percentage value
     * @since 3.0.0
     */
    private function getRiskPercentage($risk) {
        $riskPercentages = [
            'low' => 25,
            'medium' => 60,
            'high' => 90
        ];
        
        return $riskPercentages[$risk] ?? $riskPercentages['low'];
    }
}

/**
 * Get Project Plan Presentation
 * 
 * Convenience function to get presentation manager instance.
 * 
 * @param array $config Configuration array
 * @return ProjectPlanPresentationManager Presentation manager instance
 * @since 3.0.0
 */
function getProjectPlanPresentation($config = []) {
    return new ProjectPlanPresentationManager($config);
}

/**
 * Render Assignee Removal Confirmation Modal
 * 
 * Renders the Bootstrap modal for confirming assignee removal.
 * This should be called once in the main page layout.
 * 
 * @return string HTML content for the modal
 * @since 3.0.0
 */
function renderAssigneeRemovalModal() {
    return '
<!-- Confirmation Modal for Assignee Removal -->
<div class="modal fade" id="confirmRemoveAssigneeModal" tabindex="-1" aria-labelledby="confirmRemoveAssigneeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmRemoveAssigneeModalLabel">
                    <i class="uil uil-exclamation-triangle text-warning me-2"></i>
                    Confirm Removal
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Are you sure you want to remove <strong id="assigneeNameToRemove"></strong> from this task?</p>
                <div class="alert alert-warning d-flex align-items-center" role="alert">
                    <i class="uil uil-info-circle me-2"></i>
                    <div>This action cannot be undone. The assignee will be removed from the task immediately.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="uil uil-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmRemoveBtn">
                    <i class="uil uil-trash-alt me-1"></i>Remove Assignee
                </button>
            </div>
        </div>
    </div>
</div>';
}

/**
 * Include the assignee removal JavaScript file in your page:
 * <script src="html/includes/scripts/projects/js/assignee-removal.js"></script>
 */

// Add CSS for assignee hover delete functionality
echo '
<style>
.assignee-container {
    transition: all 0.2s ease-in-out;
}

.assignee-container:hover .assignee-delete-btn {
    display: block !important;
}

.assignee-container:hover .assignee-avatar {
    opacity: 0.8;
    transform: scale(0.95);
}

.assignee-delete-btn {
    transition: all 0.2s ease-in-out;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.assignee-delete-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}

.assignee-delete-btn:active {
    transform: scale(0.95);
}
</style>';?>
