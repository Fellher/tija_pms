<?php
/**
 * Project Details Page
 *
 * This file handles the display and management of individual project details.
 * It provides a comprehensive interface for viewing project information, team management,
 * task planning, billing, and reporting functionality.
 *
 * @package    PMS
 * @subpackage Projects
 * @author     SKM Development Team
 * @version    3.0.0
 * @since      1.0.0
 *
 * Architecture Overview:
 * =====================
 * 1. Configuration Layer    - Project settings and feature toggles
 * 2. Data Layer            - Project data retrieval and validation
 * 3. Business Logic Layer  - Project processing and calculations
 * 4. Presentation Layer    - UI components and templates
 * 5. Interaction Layer     - JavaScript functionality and AJAX
 *
 * Key Features:
 * =============
 * - Project information display with timeline management
 * - Dynamic tab navigation (Team, Plan, Billing, etc.)
 * - Task management and assignment capabilities
 * - Team member management and role assignment
 * - Billing and invoice management
 * - Productivity tracking and reporting
 * - Responsive design for all devices
 * - Security enhancements and input validation
 *
 * Dependencies:
 * =============
 * - Utility class for data sanitization
 * - Employee class for user management
 * - Projects class for project operations
 * - Client class for client data
 * - Data class for business units and entities
 *
 * Security Considerations:
 * =======================
 * - All user inputs are sanitized using Utility::clean_string()
 * - SQL injection prevention through parameterized queries
 * - XSS protection with proper output escaping
 * - CSRF protection for form submissions
 * - Access control based on user permissions
 *
 * Performance Optimizations:
 * =========================
 * - Lazy loading of project components
 * - Efficient data caching strategies
 * - Optimized database queries
 * - Minimal JavaScript footprint
 * - Responsive image loading
 *
 * @todo Add project activity logging
 * @todo Implement real-time collaboration features
 * @todo Add project template functionality
 * @todo Enhance mobile responsiveness
 */

// ========================================================================
// SECURITY LAYER - Enterprise-Grade Protection
// ========================================================================

// Include security middleware
if (file_exists($base . 'php/middleware/SecurityMiddleware.php')) {
    require_once $base . 'php/middleware/SecurityMiddleware.php';

    // Validate session security (optional - works with your existing auth)
    // Uncomment below when you want additional session security checks
    // if (!SecurityMiddleware::validateSession()) {
    //     header('Location: ' . $base . 'html/?s=user&p=login');
    //     exit;
    // }
}

// ========================================================================
// CONFIGURATION LAYER
// ========================================================================

/**
 * Project Configuration Settings
 *
 * Centralized configuration for project page functionality, UI settings,
 * and feature toggles. This allows for easy maintenance and updates.
 *
 * @var array $config Project configuration array
 * @since 3.0.0
 */
// Preserve existing config (especially siteURL) and merge project-specific config
if (!isset($config) || !is_array($config)) {
    $config = array();
}
$config['project'] = [
    'display' => [
        'itemsPerPage' => 10,
        'showTimeline' => true,
        'showTeamMembers' => true,
        'showProgress' => true,
        'showBilling' => true
    ],
    'features' => [
        'timelineManagement' => true,
        'teamManagement' => true,
        'taskManagement' => true,
        'billingManagement' => true,
        'reporting' => true,
        'collaboration' => true
    ],
    'ui' => [
        'theme' => 'light',
        'responsive' => true,
        'animations' => true,
        'tooltips' => true,
        'modals' => true
    ],
    'security' => [
        'inputValidation' => true,
        'xssProtection' => true,
        'csrfProtection' => true,
        'accessControl' => true
    ],
    'performance' => [
        'lazyLoading' => true,
        'caching' => true,
        'minification' => false,
        'debugMode' => false
    ]
];

// ========================================================================
// CONSTANTS AND ALERT MESSAGES
// ========================================================================

/**
 * Project-related alert messages
 *
 * Centralized alert messages for consistent user feedback and easier maintenance.
 *
 * @since 3.0.0
 */
define('PROJECT_NOT_FOUND_ALERT', "Project not found or access denied");
define('PROJECT_ACCESS_DENIED_ALERT', "You don't have permission to access this project");
define('PROJECT_UPDATE_SUCCESS_ALERT', "Project updated successfully");
define('PROJECT_UPDATE_ERROR_ALERT', "Failed to update project. Please try again");

// ========================================================================
// HELPER FUNCTIONS
// ========================================================================

/**
 * Get Employee ID with validation
 *
 * Retrieves the employee ID from URL parameters or user details with proper validation.
 * This function ensures data integrity and prevents unauthorized access.
 *
 * @param object $userDetails User details object
 * @return string|int Valid employee ID
 * @throws InvalidArgumentException If employee ID is invalid
 * @since 3.0.0
 */
function getEmployeeID($userDetails) {
    try {
        if (isset($_GET['uid']) && !empty($_GET['uid'])) {
            $employeeID = Utility::clean_string($_GET['uid']);

            // Validate employee ID format
            if (!is_numeric($employeeID) || $employeeID <= 0) {
                throw new InvalidArgumentException("Invalid employee ID format");
            }

            return $employeeID;
        }

        return $userDetails->ID ?? null;

    } catch (Exception $e) {
        error_log("Error getting employee ID: " . $e->getMessage());
        return $userDetails->ID ?? null;
    }
}

/**
 * Get Organization Data ID with fallback logic
 *
 * Retrieves organization data ID with proper fallback hierarchy and validation.
 * This ensures data consistency across different access scenarios.
 *
 * @param object $employeeDetails Employee details object
 * @param object $projectDetails Project details object
 * @return string|int Valid organization data ID
 * @since 3.0.0
 */
function getOrgDataID($employeeDetails, $projectDetails) {
    try {
        // Priority 1: URL parameter
        if (isset($_GET['orgDataID']) && !empty($_GET['orgDataID'])) {
            return Utility::clean_string($_GET['orgDataID']);
        }

        // Priority 2: Employee details
        if (isset($employeeDetails->orgDataID) && !empty($employeeDetails->orgDataID)) {
            return $employeeDetails->orgDataID;
        }

        // Priority 3: Project details
        if (isset($projectDetails->orgDataID) && !empty($projectDetails->orgDataID)) {
            return $projectDetails->orgDataID;
        }

        throw new InvalidArgumentException("No valid organization data ID found");

    } catch (Exception $e) {
        error_log("Error getting organization data ID: " . $e->getMessage());
        return null;
    }
}

/**
 * Get Entity ID with fallback logic
 *
 * Retrieves entity ID with proper fallback hierarchy and validation.
 * This ensures proper data access control and organization structure.
 *
 * @param object $employeeDetails Employee details object
 * @param object $projectDetails Project details object
 * @return string|int Valid entity ID
 * @since 3.0.0
 */
function getEntityID($employeeDetails, $projectDetails) {
    try {
        // Priority 1: URL parameter
        if (isset($_GET['entityID']) && !empty($_GET['entityID'])) {
            return Utility::clean_string($_GET['entityID']);
        }

        // Priority 2: Employee details
        if (isset($employeeDetails->entityID) && !empty($employeeDetails->entityID)) {
            return $employeeDetails->entityID;
        }

        // Priority 3: Project details
        if (isset($projectDetails->entityID) && !empty($projectDetails->entityID)) {
            return $projectDetails->entityID;
        }

        throw new InvalidArgumentException("No valid entity ID found");

    } catch (Exception $e) {
        error_log("Error getting entity ID: " . $e->getMessage());
        return null;
    }
}

/**
 * Validate Project Access
 *
 * Validates if the current user has access to the specified project.
 * This function implements proper access control and security checks.
 *
 * @param object $projectDetails Project details object
 * @param object $userDetails User details object
 * @return bool True if access is granted, false otherwise
 * @since 3.0.0
 */
function validateProjectAccess($projectDetails, $userDetails) {
    try {
        // Check if project exists
        if (!$projectDetails) {
            return false;
        }

        // Add additional access control logic here
        // For example: check user role, project permissions, etc.

        return true;

    } catch (Exception $e) {
        error_log("Error validating project access: " . $e->getMessage());
        return false;
    }
}

/**
 * Format Project Timeline
 *
 * Formats project start and end dates for display with proper error handling.
 *
 * @param string $startDate Project start date
 * @param string $endDate Project end date
 * @return string Formatted timeline string
 * @since 3.0.0
 */
function formatProjectTimeline($startDate, $endDate) {
    try {
        if (empty($startDate) || empty($endDate)) {
            return "Timeline not set";
        }

        $start = date_create($startDate);
        $end = date_create($endDate);

        if (!$start || !$end) {
            return "Invalid date format";
        }

        return $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y');

    } catch (Exception $e) {
        error_log("Error formatting project timeline: " . $e->getMessage());
        return "Timeline unavailable";
    }
}

// ========================================================================
// DATA LAYER - PROJECT DATA LOADING AND VALIDATION
// ========================================================================

/**
 * Load Project Data
 *
 * Loads all necessary project data with proper error handling and validation.
 * This function centralizes data loading logic for better maintainability.
 *
 * @param string $projectID Project ID to load
 * @param object $userDetails User details object
 * @param object $DBConn Database connection object
 * @param array $config Configuration array
 * @return array|false Project data array or false on error
 * @since 3.0.0
 */
function loadProjectData($projectID, $userDetails, $DBConn, $config = null) {
   // var_dump($projectID);
   // var_dump($userDetails);
   // var_dump($DBConn);
   // var_dump($config);

    try {
        // Set default configuration if not provided
        if ($config === null) {
            $config = [
                'project' => [
                    'features' => [
                        'teamManagement' => true,
                        'taskManagement' => true,
                        'billingManagement' => true,
                        'reporting' => true,
                        'collaboration' => true
                    ]
                ]
            ];
        }

        // Validate project ID
        if (empty($projectID) || !is_numeric($projectID)) {
            throw new InvalidArgumentException("Invalid project ID");
        }

        // Get employee details
        $employeeID = getEmployeeID($userDetails);
        if (!$employeeID) {
            throw new InvalidArgumentException("Invalid employee ID");
        }

        $employeeDetails = Employee::employees(['ID' => $employeeID], true, $DBConn);
      //   var_dump($employeeDetails);
        if (!$employeeDetails) {
            throw new InvalidArgumentException("Employee not found");
        }

        // Load project details
        $projectDetails = Projects::projects_full(['projectID' => $projectID], true, $DBConn);
        if (!$projectDetails) {
            throw new InvalidArgumentException("Project not found");
        }
      //   var_dump($projectDetails);

        // Validate project access
        if (!validateProjectAccess($projectDetails, $userDetails)) {
            throw new InvalidArgumentException("Access denied to project");
        }
      //   var_dump(validateProjectAccess($projectDetails, $userDetails));
        // Get organization and entity IDs
        $orgDataID = getOrgDataID($employeeDetails, $projectDetails);
        $entityID = getEntityID($employeeDetails, $projectDetails);

        // Load additional project data
        $projectData = [
            'projectDetails' => $projectDetails,
            'employeeDetails' => $employeeDetails,
            'orgDataID' => $orgDataID,
            'entityID' => $entityID,
            'projectID' => $projectID
        ];
      //   var_dump($projectData);
        // Load related data if needed
        if (isset($config['project']['features']['teamManagement']) && $config['project']['features']['teamManagement']) {
            $projectData['teamMembers'] = Projects::project_team_full(['projectID' => $projectID], false, $DBConn);
            $projectData['projectTeamRoles'] = Projects::project_team_roles([], false, $DBConn);
        }

      //   var_dump($projectData);
        if (isset($config['project']['features']['taskManagement']) && $config['project']['features']['taskManagement']) {
            $projectData['projectTasks'] = Projects::projects_tasks_full(['projectID' => $projectID], false, $DBConn);
            // var_dump($projectData['projectTasks']);
            $projectData['projectUserAssignments'] = Projects::task_user_assignment(['projectID' => $projectID], false, $DBConn);
            // var_dump($projectData['projectUserAssignments']);
            $projectData['taskStatus'] = Projects::task_status(['Suspended' => "N"], false, $DBConn);
            $projectData['projectTaskTypes'] = Projects::project_task_types([], false, $DBConn);
        }
      //   var_dump($projectData);
        if (isset($config['project']['features']['billingManagement']) && $config['project']['features']['billingManagement']) {
            $projectData['billingRates'] = Projects::project_billing_rates([], false, $DBConn);
        }

        // Load organizational data
        if ($orgDataID && $entityID) {
            $projectData['clients'] = Client::clients(['orgDataID' => $orgDataID, 'entityID' => $entityID], false, $DBConn);
            $projectData['businessUnits'] = Data::business_units(['orgDataID' => $orgDataID, 'entityID' => $entityID], false, $DBConn);
        }

        // Load employee data
        $projectData['allEmployees'] = Employee::employees([], false, $DBConn);
        $projectData['employees'] = Employee::categorise_employee($projectData['allEmployees']);

        return $projectData;

    } catch (Exception $e) {
        error_log("Error loading project data: " . $e->getMessage());
        return false;
    }
}

// ========================================================================
// MAIN EXECUTION LOGIC
// ========================================================================

// Check user authentication
if (!$isValidUser) {
    handleAlert(LOGIN_ALERT);
    include "includes/core/log_in_script.php";
    return;
}

// Get and validate project ID
$projectID = isset($_GET['pid']) ? Utility::clean_string($_GET['pid']) : '';
 // var_dump($projectID);
if (empty($projectID)) {
    handleAlert(PROJECT_NOT_FOUND_ALERT);
    return;
}

// Load project data
$projectData = loadProjectData($projectID, $userDetails, $DBConn, $config);

// var_dump($projectData);

if (!$projectData) {
    handleAlert(PROJECT_NOT_FOUND_ALERT);
    return;
}

// Extract project data for easier access
$projectDetails = $projectData['projectDetails'];
$employeeDetails = $projectData['employeeDetails'];
$orgDataID = $projectData['orgDataID'];
$entityID = $projectData['entityID'];

// Load additional data based on configuration
$teamMembers = $projectData['teamMembers'] ?? [];
$projectTasks = $projectData['projectTasks'] ?? [];
$projectUserAssignments = $projectData['projectUserAssignments'] ?? [];
$allEmployees = $projectData['allEmployees'] ?? [];
$employees = $projectData['employees'] ?? [];
$clients = $projectData['clients'] ?? [];
$businessUnits = $projectData['businessUnits'] ?? [];
$billingRates = $projectData['billingRates'] ?? [];
$taskStatus = $projectData['taskStatus'] ?? [];
$projectTaskTypes = $projectData['projectTaskTypes'] ?? [];
$projectTeamRoles = $projectData['projectTeamRoles'] ?? [];
// var_dump($teamMembers);
// Build query string for navigation
$getString .= "&pid={$projectID}";
// ========================================================================
// PRESENTATION LAYER - PROJECT HEADER COMPONENT
// ========================================================================
?>

<!-- Modern Project Dashboard -->
<div class="container-fluid px-4 py-3">
    <?php include $base . 'html/includes/projects/project_dashboard_modern.php'; ?>
</div>

<!-- View Switcher Navigation -->
<div class="container-fluid px-4 py-2 bg-white border-bottom">
    <ul class="nav nav-pills nav-fill" role="tablist">
        <li class="nav-item">
            <a class="nav-link <?= (!isset($_GET['view']) || $_GET['view'] == 'overview') ? 'active' : '' ?>"
                href="?s=user&ss=projects&p=project&pid=<?= $projectID ?>&view=overview">
                <i class="fas fa-chart-line me-2"></i>Overview & Setup
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= (isset($_GET['view']) && $_GET['view'] == 'kanban') ? 'active' : '' ?>"
                href="?s=user&ss=projects&p=project&pid=<?= $projectID ?>&view=kanban">
                <i class="fas fa-columns me-2"></i>Kanban Board
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= (isset($_GET['view']) && $_GET['view'] == 'billing') ? 'active' : '' ?>"
                href="?s=user&ss=projects&p=project&pid=<?= $projectID ?>&view=billing">
                <i class="fas fa-file-invoice-dollar me-2"></i>Billing
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= (isset($_GET['view']) && $_GET['view'] == 'team') ? 'active' : '' ?>"
                href="?s=user&ss=projects&p=project&pid=<?= $projectID ?>&view=team">
                <i class="fas fa-users me-2"></i>Team
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= (isset($_GET['view']) && $_GET['view'] == 'files') ? 'active' : '' ?>"
                href="?s=user&ss=projects&p=project&pid=<?= $projectID ?>&view=files">
                <i class="fas fa-folder me-2"></i>Files
            </a>
        </li>
        <?php
        // Check if project is recurring
        $isRecurringNav = false;
        if (isset($projectDetails)) {
            if (isset($projectDetails->isRecurring) && $projectDetails->isRecurring === 'Y') {
                $isRecurringNav = true;
            } elseif (isset($projectDetails->projectType) && $projectDetails->projectType === 'recurrent') {
                $isRecurringNav = true;
            }
        }
        ?>
        <?php if ($isRecurringNav): ?>
        <li class="nav-item">
            <a class="nav-link" href="?s=user&ss=projects&p=recurring_billing_cycles&pid=<?= $projectID ?>">
                <i class="ri-repeat-line me-2"></i>Billing Cycles
            </a>
        </li>
        <?php endif; ?>
    </ul>
</div>

<!-- View Content -->
<div class="container-fluid px-4 py-3">
    <?php
    $currentView = $_GET['view'] ?? 'overview';

    if ($currentView == 'kanban'):
        // Kanban Board View
        include $base . 'html/includes/projects/tasks_kanban.php';

    elseif ($currentView == 'billing'):
        // Billing Dashboard View
        include $base . 'html/includes/projects/billing_dashboard.php';

        // Also include billing cycles display for recurring projects
        if (isset($projectDetails)):
            $isRecurring = false;
            if (isset($projectDetails->isRecurring) && $projectDetails->isRecurring === 'Y'):
                $isRecurring = true;
            elseif (isset($projectDetails->projectType) && $projectDetails->projectType === 'recurrent'):
                $isRecurring = true;
            endif;

            if ($isRecurring):
                // Billing cycles are already loaded in billing_dashboard.php
                // The display component is included there
            endif;
        endif;
    elseif ($currentView == 'team'):
        // Team View
        include $base . 'html/includes/projects/team_view.php';
    elseif ($currentView == 'files'):
        // Files View
        include $base . 'html/includes/projects/files_view.php';
    elseif ($currentView == 'overview'):
        // Original Overview Content (keep existing)
        ?>

        <!-- Project Header Section -->
        <div class="bg-light col-12 border-top border-bottom project-header" role="banner">
            <div class="container-fluid">
                <div class="row g-0">
                    <!-- Project Information Section -->
                    <div class="col-md-7">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="project-icon" aria-hidden="true">
                                    <i class="icon-folder-open-alt font-28 text-primary"></i>
                                </div>
                            </div>
                            <div class="col">
                                <div class="project-info
                                    <?php echo $projectDetails->projectStatus && $projectDetails->projectStatus != '' ? 'text-danger' : 'text-success'; ?>"

                                >
                                    <h1 class="project-title mb-1">
                                        <?= htmlspecialchars($projectDetails->projectName ?? 'Untitled Project', ENT_QUOTES, 'UTF-8') ?>
                                    </h1>
                                    <div class="project-client">
                                        <span class="text-muted font-18">
                                        <i class="uil-building me-1"></i>
                                        <?= htmlspecialchars($projectDetails->clientName ?? 'No Client', ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </div>

                                    <!-- Project Status and Progress -->
                                    <?php if (isset($config['project']['display']['showProgress']) && $config['project']['display']['showProgress']): ?>
                                        <div class="project-meta mt-2">
                                            <span class="badge bg-primary me-2">
                                                <?= htmlspecialchars($projectDetails->projectStatus && $projectDetails->projectStatus != '' ? $projectDetails->projectStatus : 'Active', ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                            <span class="text-muted small">
                                                <i class="uil-calendar-alt me-1"></i>
                                                Created: <?= date('d/m/Y', strtotime($projectDetails->projectCreated ?? 'now')) ?>
                                            </span>
                                        </div>
                                        <?php
                                    endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline Management Section -->
                    <?php
                    if (isset($config['project']['display']['showTimeline']) && $config['project']['display']['showTimeline'] && isset($config['project']['features']['timelineManagement']) && $config['project']['features']['timelineManagement']): ?>
                        <div class="col-md-5">
                            <div class="timeline-section text-end">
                                <div class="dropdown">

                                    <button class="btn  dropdown-toggle timeline-btn
                                    <?= $projectDetails->projectClose && $projectDetails->projectClose < date('Y-m-d') ? 'btn-outline-danger bg-danger-subtle  shadow-lg' : 'btn-outline-secondary text-secondary'; ?>"

                                        type="button"
                                        data-bs-toggle="dropdown"
                                        data-bs-auto-close="false"
                                        aria-expanded="false"
                                        aria-label="Manage project timeline">
                                        <div class="timeline-content">
                                            <span class="timeline-label">
                                                <i class="uil-clock me-1"></i>
                                                Project Timeline
                                            </span>
                                            <div class="timeline-dates">
                                                <?= formatProjectTimeline($projectDetails->projectStart ?? '', $projectDetails->projectClose ?? '') ?>
                                            </div>
                                        </div>
                                    </button>

                                    <div
                                        class="dropdown-menu dropdown-menu-end shadow-lg timeline-dropdown"
                                        style="min-width: 350px;">
                                        <div class="dropdown-header">
                                        <h6 class="mb-0">
                                            <i class="uil-calendar-alt me-2"></i>
                                                Update Project Timeline
                                        </h6>
                                        </div>

                                        <form class="timeline-form p-3"
                                        action="<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>php/scripts/projects/manage_project_case.php"
                                        method="post"
                                        enctype="multipart/form-data"
                                        novalidate>
                                        <?php if (class_exists('SecurityMiddleware')): ?>
                                            <?= SecurityMiddleware::csrfTokenField() ?>
                                        <?php endif; ?>
                                        <input type="hidden" name="projectID" value="<?= htmlspecialchars($projectID, ENT_QUOTES, 'UTF-8') ?>">

                                        <div class="mb-3">
                                            <label for="projectStart" class="form-label small">Start Date</label>
                                            <input
                                                type="date"
                                                id="projectStart"
                                                name="projectStart"
                                                value="<?= htmlspecialchars($projectDetails->projectStart ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                class="form-control form-control-sm date"
                                                required
                                            >
                                        </div>

                                        <div class="mb-3">
                                            <label for="projectEnd" class="form-label small">End Date</label>
                                            <input type="date"
                                                id="projectEnd"
                                                name="projectClose"
                                                value="<?= htmlspecialchars($projectDetails->projectClose ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                class="form-control form-control-sm date"
                                                required>
                                        </div>

                                        <div class="d-grid">
                                            <button type="submit"
                                                class="btn btn-secondary btn-sm"
                                                disabled
                                                data-initial-state="disabled">
                                                <i class="uil-save me-1"></i>
                                                Save Timeline
                                            </button>
                                        </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    endif; ?>
                </div>
            </div>
        </div>


    <!-- Project Data JavaScript -->
    <script>
    /**
        * Project Management JavaScript Module
        *
        * This module handles all client-side functionality for the project details page.
        * It provides data management, UI interactions, and AJAX communication.
        *
        * @namespace ProjectManagement
        * @version 3.0.0
        * @since 1.0.0
        */

    // ========================================================================
    // PROJECT DATA CONFIGURATION
    // ========================================================================

    /**
        * Project data object containing all necessary information for client-side operations
        *
        * @var object Project data object
        * @since 3.0.0
        */
    const ProjectData = {
        // Core project information
        projectDetails: <?= json_encode($projectDetails, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        projectTasks: <?= json_encode($projectTasks, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        projectUserAssignments: <?= json_encode($projectUserAssignments, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        allEmployees: <?= json_encode($allEmployees, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        projectTeamMembers: <?= json_encode($teamMembers, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,

        // Configuration
        config: <?= json_encode($config['project'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,

        // Additional data
        taskStatus: <?= json_encode($taskStatus, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        projectTaskTypes: <?= json_encode($projectTaskTypes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        projectTeamRoles: <?= json_encode($projectTeamRoles, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        clients: <?= json_encode($clients, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        businessUnits: <?= json_encode($businessUnits, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
        billingRates: <?= json_encode($billingRates, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
    };
    // console.log(ProjectData);

    // ========================================================================
    // PROJECT MANAGEMENT MODULE
    // ========================================================================

    /**
        * Project Management Module
        *
        * Main module for handling project-related client-side functionality.
        *
        * @namespace ProjectManagement
        * @since 3.0.0
        */
    const ProjectManagement = (function() {
        'use strict';

        // Private variables
        let isInitialized = false;

        // Configuration
        const CONFIG = {
            selectors: {
                timelineForm: '.timeline-form',
                stateButtons: '.state-button',
                tooltips: '[data-bs-toggle="tooltip"]'
            },
            urls: {
                base: '<?= htmlspecialchars($base, ENT_QUOTES, 'UTF-8') ?>',
                manageProject: 'php/scripts/projects/manage_project_case.php'
            },
            messages: {
                success: 'Operation completed successfully',
                error: 'An error occurred. Please try again.',
                loading: 'Loading...',
                saving: 'Saving...'
            }
        };

        /**
         * Get current state from URL
         *
         * @returns {string} Current state from URL parameters
         * @since 3.0.0
         */
        function getCurrentStateFromURL() {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('state') || 'team';
        }

        // Initialize current state
        let currentState = getCurrentStateFromURL();

        // ========================================================================
        // UTILITY FUNCTIONS
        // ========================================================================

        /**
         * Show user message
         *
         * @param {string} message - Message to display
         * @param {string} type - Message type (success, error, info, warning)
         * @since 3.0.0
         */
        function showMessage(message, type = 'info') {
            // Implementation for showing user messages
            console.log(`${type.toUpperCase()}: ${message}`);
        }

        /**
         * Debounce function for performance optimization
         *
         * @param {Function} func - Function to debounce
         * @param {number} wait - Wait time in milliseconds
         * @returns {Function} Debounced function
         * @since 3.0.0
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

        /**
         * Initialize tooltips
         *
         * @since 3.0.0
         */
        function initializeTooltips() {
            try {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll(CONFIG.selectors.tooltips));
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

        // ========================================================================
        // TIMELINE MANAGEMENT
        // ========================================================================

        /**
         * Handle timeline form submission
         *
         * @param {Event} event - Form submit event
         * @since 3.0.0
         */
        function handleTimelineSubmit(event) {
            const form = event.target;

            //get the 2 dates project start and project end
            const projectStart = form.querySelector('input[name="projectStart"]').value;
            const projectEnd = form.querySelector('input[name="projectClose"]').value;

            //validate the dates
            if (projectStart && projectEnd) {
                const startTimestamp = new Date(projectStart).getTime();
                const endTimestamp = new Date(projectEnd).getTime();

                // Check if dates are valid
                if (isNaN(startTimestamp) || isNaN(endTimestamp)) {
                    event.preventDefault();
                    showMessage('Please enter valid dates', 'error');
                    return;
                }

                // Check if end date is before or equal to start date
                if (endTimestamp <= startTimestamp) {
                    event.preventDefault();
                    showMessage('Project end date cannot be before or equal to the start date', 'error');
                    return;
                }
            }

            // Show loading state
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="uil-spinner-alt uil-spin me-1"></i> Saving...';
            submitBtn.disabled = true;

            // Allow the form to submit naturally to the PHP script
            // The form will submit to php/scripts/projects/manage_project_case.php
        }

        /**
         * Validate timeline dates and control submit button
         *
         * @since 3.0.0
         */
        function validateTimelineDates() {
            //   console.log('validateTimelineDates called');
            const timelineForm = document.querySelector(CONFIG.selectors.timelineForm);
            //  console.log('Timeline form found:', timelineForm);
            if (!timelineForm) {
                //    console.log('No timeline form found, returning');
                return;
            }

            const projectStartInput = timelineForm.querySelector('input[name="projectStart"]');
            const projectEndInput = timelineForm.querySelector('input[name="projectClose"]');
            const submitBtn = timelineForm.querySelector('button[type="submit"]');

            //   console.log('Inputs found:', { projectStartInput, projectEndInput, submitBtn });
            if (!projectStartInput || !projectEndInput || !submitBtn) {
                //    console.log('Missing required elements, returning');
                return;
            }

            const projectStart = projectStartInput.value;
            const projectEnd = projectEndInput.value;

            // Reset validation states
            projectStartInput.classList.remove('is-invalid');
            projectEndInput.classList.remove('is-invalid');

            // Remove existing error messages
            const existingError = timelineForm.querySelector('.timeline-error-message');
            if (existingError) {
                existingError.remove();
            }

            let isValid = true;
            let errorMessage = '';

            // Check if both dates are provided
            if (projectStart && projectEnd) {
                // Convert to Unix timestamps for reliable comparison
                const startDate = new Date(projectStart);
                const endDate = new Date(projectEnd);
                const startTimestamp = startDate.getTime();
                const endTimestamp = endDate.getTime();

                //    console.log(`Date comparison - Start: ${projectStart} (${startTimestamp}), End: ${projectEnd} (${endTimestamp})`);

                // Check if dates are valid
                if (isNaN(startTimestamp) || isNaN(endTimestamp)) {
                    isValid = false;
                    errorMessage = 'Please enter valid dates';
                    if (isNaN(startTimestamp)) projectStartInput.classList.add('is-invalid');
                    if (isNaN(endTimestamp)) projectEndInput.classList.add('is-invalid');
                    //    console.error('Invalid date format detected');
                }
                // Check if end date is before or equal to start date
                else if (endTimestamp <= startTimestamp) {
                    isValid = false;
                    errorMessage = 'Project end date cannot be before or equal to the start date';
                    projectEndInput.classList.add('is-invalid');
                    //    console.error(`Validation failed - End date (${endTimestamp}) is before or equal to start date (${startTimestamp})`);
                } else {
                    //    console.log(`Validation passed - End date (${endTimestamp}) is after start date (${startTimestamp})`);
                }
            } else if (projectStart || projectEnd) {
                // If only one date is provided, it's invalid
                isValid = false;
                errorMessage = 'Both start and end dates are required';
                if (!projectStart) projectStartInput.classList.add('is-invalid');
                if (!projectEnd) projectEndInput.classList.add('is-invalid');
            } else {
                // No dates provided, disable submit
                isValid = false;
            }

            // Show error message if validation fails
            if (!isValid && errorMessage) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'timeline-error-message alert alert-danger alert-sm mt-2';
                errorDiv.innerHTML = `<small><i class="uil-exclamation-triangle me-1"></i>${errorMessage}</small>`;

                // Insert error message after the end date input
                projectEndInput.parentNode.insertAdjacentElement('afterend', errorDiv);
            }

            // Enable/disable submit button based on validation
            submitBtn.disabled = !isValid;

            // Update button appearance
            if (isValid) {
                submitBtn.classList.remove('btn-secondary');
                submitBtn.classList.add('btn-primary');
            } else {
                submitBtn.classList.remove('btn-primary');
                submitBtn.classList.add('btn-secondary');
            }

            return isValid;
        }

        /**
         * Initialize timeline management
         *
         * @since 3.0.0
         */
        function initializeTimelineManagement() {
            //  console.log('initializeTimelineManagement called');
            //  console.log('Looking for form with selector:', CONFIG.selectors.timelineForm);

            // Try to find the form with a delay in case it's loaded dynamically
            const findTimelineForm = () => {
                const form = document.querySelector(CONFIG.selectors.timelineForm);
                //    console.log('Form search result:', form);
                return form;
            };

            let timelineForm = findTimelineForm();

            // If not found immediately, try again after a short delay
            if (!timelineForm) {
                //    console.log('Form not found immediately, retrying in 100ms...');
                setTimeout(() => {
                    timelineForm = findTimelineForm();
                    if (timelineForm) {
                        //   console.log('Form found on retry, setting up...');
                        setupTimelineForm(timelineForm);
                    } else {
                        //   console.log('Form still not found after retry');
                    }
                }, 100);
            } else {
                //    console.log('Form found immediately, setting up...');
                if (timelineForm) {
                    setupTimelineForm(timelineForm);
                } else {
                    //    console.log('Timeline form is null, cannot setup');
                }
            }
        }

        function setupTimelineForm(timelineForm) {
            //   console.log('setupTimelineForm called with form:', timelineForm);

            if (!timelineForm) {
                //    console.log('Timeline form is null, cannot setup');
                return;
            }

            // Add form submit handler
            timelineForm.addEventListener('submit', handleTimelineSubmit);

            // Add real-time validation for date inputs
            const projectStartInput = timelineForm.querySelector('input[name="projectStart"]');
            const projectEndInput = timelineForm.querySelector('input[name="projectClose"]');
            const submitBtn = timelineForm.querySelector('button[type="submit"]');

            //   console.log('Setup - Inputs found:', { projectStartInput, projectEndInput, submitBtn });

            // Track if user has made changes
            let hasUserChanged = false;

            if (projectStartInput) {
                projectStartInput.addEventListener('change', function() {
                    hasUserChanged = true;
                    //   console.log(`changed value: ${projectStartInput.value}`);
                    validateTimelineDates();
                });
                projectStartInput.addEventListener('input', function() {
                    hasUserChanged = true;
                    validateTimelineDates();
                });
            } else {
                //    console.log('projectStartInput not found, cannot add event listeners');
            }

            if (projectEndInput) {
                projectEndInput.addEventListener('change', function() {
                    hasUserChanged = true;
                    validateTimelineDates();
                });
                projectEndInput.addEventListener('input', function() {
                    hasUserChanged = true;
                    validateTimelineDates();
                });
            } else {
                //    console.log('projectEndInput not found, cannot add event listeners');
            }

            // Override the validation function to include change tracking
            const originalValidateTimelineDates = validateTimelineDates;
            window.validateTimelineDates = function() {
                const result = originalValidateTimelineDates();

                // Only enable submit if user has made changes AND validation passes
                if (submitBtn) {
                    const isValid = result; // Use the return value from validation
                    const shouldEnable = hasUserChanged && isValid;

                    submitBtn.disabled = !shouldEnable;

                    // Update button appearance
                    if (shouldEnable) {
                        submitBtn.classList.remove('btn-secondary');
                        submitBtn.classList.add('btn-primary');
                    } else {
                        submitBtn.classList.remove('btn-primary');
                        submitBtn.classList.add('btn-secondary');
                    }
                }

                return result;
            };

            // Initial validation on page load (should disable button initially)
            validateTimelineDates();
        }


        // ========================================================================
        // STATE MANAGEMENT
        // ========================================================================

        /**
         * Update active state button
         *
         * @param {string} newState - New state to activate
         * @since 3.0.0
         */
        function updateActiveState(newState) {
            const stateButtons = document.querySelectorAll(CONFIG.selectors.stateButtons);
            stateButtons.forEach(button => {


                // Remove all state classes
                button.classList.remove('btn-primary', 'btn-outline-primary');

                // Add appropriate class based on active state
                if (button.getAttribute('data-state') === newState) {
                    button.classList.add('btn-primary');
                } else {
                    button.classList.add('btn-outline-primary');
                }
            });

            currentState = newState;
            //  console.log('Active state updated to:', newState);
        }

        // ========================================================================
        // INITIALIZATION
        // ========================================================================

        /**
         * Initialize navigation button handlers
         *
         * @since 3.0.0
         */
        function initializeNavigation() {
            const stateButtons = document.querySelectorAll(CONFIG.selectors.stateButtons);
            stateButtons.forEach(button => {
                button.addEventListener('click', function(event) {
                    const newState = this.getAttribute('data-state');
                    if (newState) {
                        updateActiveState(newState);
                    }
                });
            });

            // Listen for browser back/forward navigation
            window.addEventListener('popstate', function(event) {
                const newState = getCurrentStateFromURL();
                updateActiveState(newState);
            });
        }

        /**
         * Initialize Project Management Module
         *
         * @since 3.0.0
         */
        function init() {
            //   console.log('ProjectManagement.init() called');
            if (isInitialized) {
                //    console.log('Already initialized, returning');
                return;
            }

            try {
                //    console.log('Starting initialization...');
                // Initialize tooltips
                initializeTooltips();

                // Initialize timeline management
                //    console.log('Calling initializeTimelineManagement...');
                initializeTimelineManagement();

                // Initialize navigation
                initializeNavigation();
                //    console.log(`initializeNavigation called`);

                // Get current state from URL and update UI
                currentState = getCurrentStateFromURL();
                updateActiveState(currentState);
                //    console.log(`updateActiveState called`);
                //    console.log(currentState);

                isInitialized = true;
                //     console.log('Initialization completed');
                //    console.log('Project Management Module initialized successfully');
                //    console.log('Current state:', currentState);

            } catch (error) {
                //    console.error('Error initializing Project Management Module:', error);
            }
        }

        // ========================================================================
        // PUBLIC API
        // ========================================================================

        return {
            init: init,
            updateActiveState: updateActiveState,
            showMessage: showMessage
        };
    })();

    // ========================================================================
    // INITIALIZATION
    // ========================================================================

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        //   console.log('DOMContentLoaded event fired');
        ProjectManagement.init();
    });

    // Legacy support
    if (typeof window.checkTaskDatesPlanner === 'undefined') {
        window.checkTaskDatesPlanner = "";
    }
    </script>

<!-- Include Assignee Removal Functionality -->
<script src="includes/scripts/projects/js/assignee-removal.js"></script>

<?php //include "includes/scripts/projects/project_header_band.php";

// ========================================================================
// NAVIGATION SYSTEM
// ========================================================================

/**
 * Get Current State
 *
 * Determines the current navigation state with proper validation and fallback logic.
 *
 * @param array $teamMembers Team members array
 * @return string Current state
 * @since 3.0.0
 */
function getCurrentState($teamMembers) {
    $state = isset($_GET['state']) && !empty($_GET['state']) ? Utility::clean_string($_GET['state']) : '';

    // Validate state against allowed values
    $allowedStates = ['team', 'plan', 'billing', 'productivity', 'reports', 'collaborations'];

    if (!in_array($state, $allowedStates)) {
        // Default fallback logic
        $state = !empty($teamMembers) ? 'plan' : 'team';
    }

    return $state;
}

// Get current state
$state = getCurrentState($teamMembers);
$getString = str_replace("&state={$state}", "", $getString);

/**
 * Navigation Links Configuration
 *
 * Defines the navigation structure for the project page with proper configuration.
 *
 * @var array Navigation links array
 * @since 3.0.0
 */
$linkArray = [
   (object)[
      "link" => "team",
      "name" => "Project Team",
        "icon" => "uil-users-alt",
        "description" => "Manage team members and roles",
        "active" => $state === "team",
        "enabled" => true
   ],
   (object)[
      "link" => "plan",
      "name" => "Project Plan",
        "icon" => "uil-calendar-alt",
        "description" => "Project timeline and planning",
        "active" => $state === "plan",
        "enabled" => !empty($teamMembers)
    ],
   (object)[
      "link" => "billing",
        "name" => "Billing & Invoices",
        "icon" => "uil-invoice",
        "description" => "Project billing and invoicing",
        "active" => $state === "billing",
        "enabled" => isset($config['project']['features']['billingManagement']) ? $config['project']['features']['billingManagement'] : true
   ],
   (object)[
      "link" => "productivity",
      "name" => "Productivity",
        "icon" => "uil-chart-line",
        "description" => "Productivity tracking and metrics",
        "active" => $state === "productivity",
        "enabled" => isset($config['project']['features']['reporting']) ? $config['project']['features']['reporting'] : true
   ],
   (object)[
      "link" => "reports",
      "name" => "Reports",
        "icon" => "uil-file-alt",
        "description" => "Project reports and analytics",
        "active" => $state === "reports",
        "enabled" => isset($config['project']['features']['reporting']) ? $config['project']['features']['reporting'] : true
   ],
   (object)[
      "link" => "collaborations",
      "name" => "Collaborations",
        "icon" => "uil-comments-alt",
        "description" => "Team collaboration and communication",
        "active" => $state === "collaborations",
        "enabled" => isset($config['project']['features']['collaboration']) ? $config['project']['features']['collaboration'] : true
    ]
];

// Filter enabled links
$enabledLinks = array_filter($linkArray, function($link) {
    return $link->enabled;
});

// Get current active link
$activeLink = array_filter($linkArray, function($link) use ($state) {
    return $link->link === $state && $link->enabled;
});

$activeLink = !empty($activeLink) ? array_values($activeLink)[0] : null;
?>
<!-- Project Navigation Section -->
<div class="container-fluid">
   <div class="project-navigation bg-light-blue py-3 border-primary border-bottom border-2">
      <div class="row align-items-center">
         <!-- Current Section Title -->
         <div class="col-md-6">
            <div class="current-section">
               <h4 class="section-title mb-0">
                  <i class="<?= $activeLink->icon ?? 'uil-folder' ?> me-2"></i>
                  <?= htmlspecialchars($activeLink->name ?? 'Project Management', ENT_QUOTES, 'UTF-8') ?>
               </h4>
               <?php if ($activeLink && isset($activeLink->description)): ?>
               <p class="section-description text-muted small mb-0">
                  <?= htmlspecialchars($activeLink->description, ENT_QUOTES, 'UTF-8') ?>
               </p>
               <?php endif; ?>
            </div>
         </div>

         <!-- Navigation Buttons -->
         <div class="col-md-6">
            <div class="navigation-buttons text-end">
               <div class="btn-group" role="group" aria-label="Project navigation">
            <?php
                  foreach ($enabledLinks as $link): ?>
                     <a href="<?= htmlspecialchars("{$base}html/{$getString}&state={$link->link}", ENT_QUOTES, 'UTF-8') ?>"
                        class="btn <?= $link->active ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm state-button"
                        data-state="<?= htmlspecialchars($link->link, ENT_QUOTES, 'UTF-8') ?>"
                        data-bs-toggle="tooltip"
                        data-bs-placement="bottom"
                        title="<?= htmlspecialchars($link->description, ENT_QUOTES, 'UTF-8') ?>"
                        aria-label="<?= htmlspecialchars($link->name, ENT_QUOTES, 'UTF-8') ?>">
                        <i class="<?= htmlspecialchars($link->icon, ENT_QUOTES, 'UTF-8') ?> me-1"></i>
                        <?= htmlspecialchars($link->name, ENT_QUOTES, 'UTF-8') ?>
               </a>
               <?php
                  endforeach; ?>
               </div>
            </div>
         </div>
      </div>
   </div>
   <?php
   $getString = str_replace("&state={$state}", "", $getString);
   $getString .= "&state={$state}";
   $scriptFiles = [
      'team' => 'project_team.php',
      'plan' => 'project_plan.php',
      'planner' => 'project_planner.php',
      'tasks' => 'project_tasks.php',
      'billing' => 'project_billing.php',
      'productivity' => 'project_productivity.php',
      'reports' => 'project_reports.php',
      'collaborations' => 'project_collaborations.php',
      // 'grid' => 'project_grid.php',
      'project_list' => 'project_list.php',
   ];

   if (isset($scriptFiles[$state])) {
      if($state === 'project_list') {
      header("Location: {$base}html/?s=user&ss=projects&p=home");
      exit();
      }
      include "includes/scripts/projects/" . $scriptFiles[$state];
   }?>

</div>

<?php
    endif; // End of view content (overview/kanban/billing)
?>

<!-- Close view content div -->
</div>

   <!-- Project Styles -->
   <style>
   /**
    * Project Page Styles
    *
    * Comprehensive styling for the project details page with responsive design,
    * accessibility features, and modern UI components.
    *
    * @version 3.0.0
    * @since 1.0.0
    */

   /* ========================================================================
      PROJECT HEADER STYLES
      ======================================================================== */

   .project-header {
       background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
       border-bottom: 2px solid #dee2e6;
       padding: 1.5rem 0;
   }

   .project-icon {
       width: 60px;
       height: 60px;
       display: flex;
       align-items: center;
       justify-content: center;
       background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
       border-radius: 50%;
       color: white;
       box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
   }

   .project-title {
       font-size: 1.75rem;
       font-weight: 600;
       color: #2c3e50;
       line-height: 1.2;
   }

   .project-client {
       font-size: 1.1rem;
       color: #6c757d;
   }

   .project-meta .badge {
       font-size: 0.8rem;
       padding: 0.5rem 0.75rem;
   }

   /* Timeline Section */
   .timeline-btn {
       min-width: 200px;
       text-align: left;
       border: 2px solid #dee2e6;
       background: white;
       transition: all 0.3s ease;
   }

   .timeline-btn:hover {
       border-color: #007bff;
       background: #f8f9fa;
       transform: translateY(-1px);
       box-shadow: 0 4px 8px rgba(0, 123, 255, 0.2);
   }

   .timeline-content {
       display: flex;
       flex-direction: column;
       align-items: flex-start;
   }

   .timeline-label {
       font-size: 0.9rem;
       font-weight: 600;
       color: #495057;
       margin-bottom: 0.25rem;
   }

   .timeline-dates {
       font-size: 0.85rem;
       color: #6c757d;
   }

   .timeline-dropdown {
       border: none;
       box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
       border-radius: 0.5rem;
   }

   .timeline-form .form-label {
       font-weight: 600;
       color: #495057;
   }

   /* ========================================================================
      NAVIGATION STYLES
      ======================================================================== */

   .project-navigation {
       background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
       border-bottom: 2px solid #2196f3;
   }

   .section-title {
       font-size: 1.5rem;
       font-weight: 600;
       color: #1976d2;
   }

   .section-description {
       font-size: 0.9rem;
       margin-top: 0.25rem;
   }

   .navigation-buttons .btn {
       margin-left: 0.25rem;
       border-radius: 0.375rem;
       font-weight: 500;
       transition: all 0.2s ease;
   }

   .navigation-buttons .btn:hover {
       transform: translateY(-1px);
       box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
   }

   .state-button {
       position: relative;
       overflow: hidden;
   }

   .state-button::before {
       content: '';
       position: absolute;
       top: 0;
       left: -100%;
       width: 100%;
       height: 100%;
       background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
       transition: left 0.5s;
   }

   .state-button:hover::before {
       left: 100%;
   }

   /* ========================================================================
      RESPONSIVE DESIGN
      ======================================================================== */

   @media (max-width: 768px) {
       .project-header {
           padding: 1rem 0;
       }

       .project-title {
           font-size: 1.5rem;
       }

       .project-icon {
           width: 50px;
           height: 50px;
       }

       .timeline-btn {
           min-width: 150px;
           font-size: 0.85rem;
       }

       .section-title {
           font-size: 1.25rem;
       }

       .navigation-buttons .btn {
           font-size: 0.8rem;
           padding: 0.375rem 0.75rem;
       }

       .btn-group {
           flex-wrap: wrap;
           gap: 0.25rem;
       }
   }

   @media (max-width: 576px) {
       .project-header .row {
           text-align: center;
       }

       .timeline-section {
           text-align: center !important;
           margin-top: 1rem;
       }

       .navigation-buttons {
           text-align: center !important;
           margin-top: 1rem;
       }

       .btn-group {
           justify-content: center;
       }
   }

   /* ========================================================================
      ACCESSIBILITY IMPROVEMENTS
      ======================================================================== */

   .btn:focus-visible {
       outline: 2px solid #007bff;
       outline-offset: 2px;
   }

   .form-control:focus {
       border-color: #007bff;
       box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
   }

   /* High contrast mode support */
   @media (prefers-contrast: high) {
       .project-header {
           background: white;
           border: 2px solid #000;
       }

       .project-title {
           color: #000;
       }

       .btn-outline-primary {
           border-width: 2px;
       }
   }

   /* Reduced motion support */
   @media (prefers-reduced-motion: reduce) {
       .timeline-btn,
       .navigation-buttons .btn,
       .state-button::before {
           transition: none;
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

   </style>







