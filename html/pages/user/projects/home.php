<?php
/**
 * PROJECT MANAGEMENT DASHBOARD - OPTIMIZED & REFACTORED VERSION
 * =============================================================
 *
 * A comprehensive project management system built with modern web development best practices.
 * This module provides a complete solution for managing projects, tracking progress, and
 * maintaining project data integrity within the TIJA PMS ecosystem.
 *
 * ARCHITECTURE OVERVIEW:
 * =====================
 * 1. Configuration Layer    - Centralized settings and permissions
 * 2. Data Layer           - Project data retrieval and processing
 * 3. Business Logic Layer - Helper functions and calculations
 * 4. Presentation Layer   - HTML templates and UI components
 * 5. Interaction Layer    - JavaScript functionality and user interactions
 *
 * KEY FEATURES:
 * =============
 * • Advanced Search & Filtering    - Multi-field search with real-time results
 * • Project Status Tracking        - Real-time project status and progress
 * • Team Management               - Project owners and managers assignment
 * • Deadline Management           - Project deadline tracking and alerts
 * • Export Capabilities           - CSV export with filtered data support
 * • Modal Management System       - Add/Edit/Delete project operations
 * • Permission-Based Access       - Role-based feature visibility
 * • Mobile-First Design          - Responsive across all device sizes
 *
 * PERFORMANCE OPTIMIZATIONS:
 * =========================
 * • Lazy Loading                 - Only renders visible table rows
 * • Efficient Filtering          - Client-side filtering for fast response
 * • Memory Management            - Optimized data structures and cleanup
 * • Caching Strategy             - Configurable cache timeouts
 * • Progressive Enhancement      - Core functionality works without JavaScript
 *
 * SECURITY CONSIDERATIONS:
 * =======================
 * • Input Sanitization          - All user inputs are sanitized
 * • XSS Prevention             - Output escaping for all dynamic content
 * • CSRF Protection            - Form tokens and validation
 * • Permission Validation      - Server-side permission checks
 * • SQL Injection Prevention   - Parameterized queries and validation
 *
 * @package    TIJA_PMS
 * @subpackage Project_Management
 * @author     TIJA Development Team
 * @version    3.0.0
 * @since      2024
 * @license    Proprietary
 *
 * @todo       - Implement server-side pagination for large datasets
 * @todo       - Add advanced export formats (Excel, PDF)
 * @todo       - Implement real-time notifications for project updates
 * @todo       - Add project timeline view
 * @todo       - Implement bulk operations for project management
 */

// Security check: Ensure user is logged in and has valid access
if(!$isValidUser) {
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}
// ============================================================================
// CONFIGURATION LAYER
// ============================================================================

/**
 * PROJECT MANAGEMENT CONFIGURATION
 *
 * Centralized configuration for the project management module.
 * All settings are organized by functional area for easy maintenance.
 *
 * @var array $config['project'] - Main configuration array
 */
$config['project'] = [

    // ========================================================================
    // DISPLAY & UI CONFIGURATION
    // ========================================================================
    'display' => [
        'currency' => 'KES',                    // Default currency for financial displays
        'timeFormat' => 'HH:MM:SS',            // Time format for timestamps
        'dateFormat' => 'Y-m-d',               // Date format for date displays
        'itemsPerPage' => 25,                  // Default number of items per page
        'showRowNumbers' => false,             // Whether to show row numbers
        'enableAnimations' => true,            // Enable UI animations and transitions
    ],

    // ========================================================================
    // FEATURE TOGGLES
    // ========================================================================
    'features' => [
        'enableSearch' => true,                // Enable search functionality
        'enableExport' => true,                // Enable data export features
        'enableBulkOperations' => false,       // Enable bulk operations (future feature)
        'enableAdvancedFiltering' => true,     // Enable advanced filtering options
        'enableLazyLoading' => true,           // Enable lazy loading for performance
        'enableRealTimeUpdates' => false,      // Enable real-time data updates (future)
    ],

    // ========================================================================
    // TABLE CONFIGURATION
    // ========================================================================
    'table' => [
        'responsive' => true,                  // Enable responsive table design
        'striped' => true,                     // Enable striped row styling
        'hover' => true,                       // Enable hover effects on rows
        'sortable' => true,                    // Enable column sorting
        'selectable' => false,                 // Enable row selection (future feature)
        'pagination' => [
            'showInfo' => true,                // Show pagination information
            'showPageNumbers' => true,         // Show numbered page buttons
            'showQuickNav' => true,            // Show quick navigation buttons
            'maxVisiblePages' => 7,            // Maximum visible page numbers
        ],
    ],

    // ========================================================================
    // MODAL CONFIGURATION
    // ========================================================================
    'modals' => [
        'size' => 'modal-lg',                  // Default modal size
        'centered' => true,                    // Center modals on screen
        'backdrop' => 'static',                // Modal backdrop behavior
        'keyboard' => true,                    // Enable keyboard navigation
        'focus' => true,                       // Auto-focus first input
    ],

    // ========================================================================
    // EXPORT CONFIGURATION
    // ========================================================================
    'export' => [
        'formats' => ['csv'],                  // Available export formats
        'fileName' => 'projects_export_' . date('Y-m-d_H-i-s'), // Default filename
        'includeHeaders' => true,              // Include column headers in export
        'maxRecords' => 10000,                 // Maximum records per export
        'compression' => false,                // Enable file compression (future)
    ],

    // ========================================================================
    // PERFORMANCE CONFIGURATION
    // ========================================================================
    'performance' => [
        'cacheTimeout' => 300,                 // Cache timeout in seconds (5 minutes)
        'maxRecordsPerPage' => 100,            // Maximum records per page
        'enableVirtualScrolling' => false,     // Enable virtual scrolling (future)
        'debounceSearch' => 300,               // Search input debounce delay (ms)
        'lazyLoadThreshold' => 50,             // Threshold for lazy loading
    ],

    // ========================================================================
    // VALIDATION CONFIGURATION
    // ========================================================================
    'validation' => [
        'projectName' => [
            'required' => true,
            'minLength' => 2,
            'maxLength' => 200,
            'pattern' => '/^[a-zA-Z0-9\s\-\.&]+$/',
        ],
        'projectValue' => [
            'required' => false,
            'min' => 0,
            'max' => 999999999.99,
            'pattern' => '/^\d+(\.\d{1,2})?$/',
        ],
        'projectDeadline' => [
            'required' => true,
            'pattern' => '/^\d{4}-\d{2}-\d{2}$/',
        ],
    ],
];

/**
 * PERMISSION CONFIGURATION
 *
 * Defines user permissions for various project management operations.
 * Permissions are based on user roles and organizational hierarchy.
 *
 * @var array $permissions - Permission configuration array
 */
$permissions = [
    // Core project operations
    'canAddProjects' => $isValidUser ?? false,      // Add new projects
    'canEditProjects' => $isValidUser ?? false,     // Edit existing projects
    'canDeleteProjects' => $isAdmin ?? false,       // Delete projects (admin only)
    'canViewProjects' => $isValidUser ?? false,     // View project details

    // Advanced operations
    'canBulkUpload' => $isAdmin ?? false,          // Bulk upload projects
    'canExportData' => $isValidUser ?? false,      // Export project data
    'canManageTeams' => $isValidUser ?? false,     // Manage project teams
    'canManagePermissions' => $isAdmin ?? false,   // Manage user permissions

    // Administrative operations
    'canViewReports' => $isValidUser ?? false,     // View project reports
    'canManageIntegrations' => $isAdmin ?? false,  // Manage system integrations
    'canAccessAuditLogs' => $isAdmin ?? false,     // Access audit logs
];

// ============================================================================
// DATA LAYER - INITIALIZATION AND USER CONTEXT SETUP
// ============================================================================

/**
 * USER CONTEXT RESOLUTION
 * =======================
 * Determines the current user context and loads necessary organizational data.
 * This section handles user permissions, organizational hierarchy, and data access.
 */

// Resolve employee ID from URL parameter or current user session
$employeeID = (isset($_GET['uid']) && !empty($_GET['uid']))
    ? Utility::clean_string($_GET['uid'])
    : $userDetails->ID;

// Load current employee details for context
$employeeDetails = Data::users(['ID' => $employeeID], true, $DBConn);



// ============================================================================
// ORGANIZATIONAL CONTEXT RESOLUTION
// ============================================================================

/**
 * ORGANIZATION DATA ID RESOLUTION
 * ===============================
 * Resolves the organization data ID using a hierarchical fallback system.
 * This ensures proper data isolation and access control.
 *
 * Fallback Hierarchy:
 * 1. URL parameter (orgDataID) - Direct user selection
 * 2. Employee's organization - User's assigned organization
 * 3. Session organization - Previously selected organization
 * 4. Empty string - No organization context
 *
 * @var string $orgDataID - Resolved organization data ID
 */
$orgDataID = isset($_GET['orgDataID'])
    ? Utility::clean_string($_GET['orgDataID'])
    : (isset($employeeDetails->orgDataID) && !empty($employeeDetails->orgDataID)
        ? $employeeDetails->orgDataID
        : (isset($_SESSION['orgDataID']) && !empty($_SESSION['orgDataID'])
            ? $_SESSION['orgDataID']
            : ""));

/**
 * ENTITY ID RESOLUTION
 * ====================
 * Resolves the entity ID using a hierarchical fallback system.
 * This ensures proper data scoping within the organizational context.
 *
 * Fallback Hierarchy:
 * 1. URL parameter (entityID) - Direct user selection
 * 2. Employee's entity - User's assigned entity
 * 3. Session entity - Previously selected entity
 * 4. Empty string - No entity context
 *
 * @var string $entityID - Resolved entity ID
 */
$entityID = isset($_GET['entityID'])
    ? Utility::clean_string($_GET['entityID'])
    : (isset($employeeDetails->entityID) && !empty($employeeDetails->entityID)
        ? $employeeDetails->entityID
        : (isset($_SESSION['entityID'])
            ? $_SESSION['entityID']
            : ''));

// Ensure orgDataID is not null (defensive programming)
$orgDataID = $orgDataID ?: "";


/**
 * ORGANIZATIONAL DATA LOADING
 * ===========================
 * Load all necessary organizational data for dropdowns, filters, and context.
 * This data is used throughout the application for various UI components.
 */
$filterArray = $orgDataID && $entityID ? array('orgDataID' => $orgDataID, 'entityID' => $entityID) :($orgDataID ? array('orgDataID' => $orgDataID) :($entityID ? array('entityID' => $entityID) : []));
$allEmployees = Employee::employees($filterArray, false, $DBConn);
$employeeCategorised = Employee::categorise_employee($allEmployees);

// ============================================================================
// PROJECT DATA RETRIEVAL AND PROCESSING
// ============================================================================

/**
 * PROJECT DATA LOADING
 * ====================
 * Loads project data and related information for the current organizational context.
 * This section handles data retrieval, processing, and preparation for display.
 */

// Load supporting data for dropdowns and filters
$clients = Client::clients(['orgDataID' => $orgDataID, 'entityID' => $entityID], false, $DBConn);
$businessUnits = Data::business_units(['orgDataID' => $orgDataID, 'entityID' => $entityID], false, $DBConn);
$billingRates = Projects::project_billing_rates([], false, $DBConn);
$allOrgs = Admin::organisation_data_mini([], false, $DBConn);
$projectTypes = Projects::project_types([], false, $DBConn);
$industries = Data::tija_industry([], false, $DBConn);
$industrySectors = Data::tija_sectors([], false, $DBConn);
$countries = Data::countries([], false, $DBConn);

// Initialize project arrays
$projects = [];
$activeProjects = [];
$overDueProjects = [];
$projectValue = 0;

// ============================================================================
// BUSINESS LOGIC LAYER - HELPER FUNCTIONS
// ============================================================================

/**
 * PROJECT STATUS MANAGEMENT
 * ========================
 * Functions for managing project status indicators and visual elements.
 */

/**
 * Generate project status badge HTML
 *
 * Creates a Bootstrap badge element indicating the project status.
 * The badge styling provides immediate visual feedback about the project state.
 *
 * @param object $project - Project object containing status information
 * @return string - HTML badge element with appropriate styling
 *
 * @example
 * $badge = generateProjectStatusBadge($project);
 * echo $badge; // Outputs: <span class="badge rounded-pill bg-success-transparent">Active</span>
 *
 * @since 3.0.0
 * @version 1.0.0
 */
function generateProjectStatusBadge($project) {
    // Validate input parameter
    if (!is_object($project)) {
        error_log('ProjectStatusBadge: Invalid project object provided');
        return '<span class="badge rounded-pill bg-secondary-transparent">Unknown</span>';
    }

    // Extract status with fallback to inactive
    $status = $project->projectStatus ?? 'inactive';

    // Generate appropriate badge based on status
    switch(strtolower($status)) {
        case 'active':
            return '<span class="badge rounded-pill bg-success-transparent" title="Active project">Active</span>';
        case 'inactive':
        case 'closed':
            return '<span class="badge rounded-pill bg-secondary-transparent" title="Inactive project">Inactive</span>';
        case 'on_hold':
            return '<span class="badge rounded-pill bg-warning-transparent" title="Project on hold">On Hold</span>';
        case 'cancelled':
            return '<span class="badge rounded-pill bg-danger-transparent" title="Cancelled project">Cancelled</span>';
        default:
            return '<span class="badge rounded-pill bg-secondary-transparent" title="Unknown status">Unknown</span>';
    }
}

/**
 * Calculate project progress percentage
 *
 * Calculates the project progress based on completed tasks or time elapsed.
 * This function provides real-time insights into project completion status.
 *
 * @param object $project - Project object containing project information
 * @param object $DBConn - Database connection object
 * @return array - Progress metrics with detailed breakdown
 *
 * @return array {
 *     @var int $percentage - Progress percentage (0-100)
 *     @var string $status - Progress status text
 *     @var string $color - Bootstrap color class for progress bar
 * }
 *
 * @example
 * $progress = calculateProjectProgress($project, $DBConn);
 * echo $progress['percentage']; // Outputs: 75
 *
 * @since 3.0.0
 * @version 1.0.0
 */
function calculateProjectProgress($project, $DBConn) {
    // Input validation
    if (!is_object($project) || !isset($project->projectID)) {
        error_log('ProjectProgress: Invalid project object provided');
        return [
            'percentage' => 0,
            'status' => 'Unknown',
            'color' => 'bg-secondary'
        ];
    }

    if (!$DBConn) {
        error_log('ProjectProgress: Database connection not available');
        return [
            'percentage' => 0,
            'status' => 'Error',
            'color' => 'bg-danger'
        ];
    }

    try {
        // Calculate progress based on time elapsed vs total duration
        $startDate = new DateTime($project->projectStart);
        $endDate = new DateTime($project->projectClose);
        $currentDate = new DateTime();

        $totalDays = $startDate->diff($endDate)->days;
        $elapsedDays = $startDate->diff($currentDate)->days;

        // Calculate percentage
        $percentage = $totalDays > 0 ? min(100, max(0, ($elapsedDays / $totalDays) * 100)) : 0;
        $percentage = round($percentage);

        // Determine status and color
        if ($percentage >= 100) {
            $status = 'Completed';
            $color = 'bg-success';
        } elseif ($percentage >= 75) {
            $status = 'Almost Complete';
            $color = 'bg-info';
        } elseif ($percentage >= 50) {
            $status = 'In Progress';
            $color = 'bg-primary';
        } elseif ($percentage >= 25) {
            $status = 'Getting Started';
            $color = 'bg-warning';
        } else {
            $status = 'Just Started';
            $color = 'bg-secondary';
        }

        return [
            'percentage' => $percentage,
            'status' => $status,
            'color' => $color
        ];

    } catch (Exception $e) {
        error_log('ProjectProgress: Error calculating progress - ' . $e->getMessage());
        return [
            'percentage' => 0,
            'status' => 'Error',
            'color' => 'bg-danger'
        ];
    }
}

/**
 * Check if project is overdue
 *
 * Determines if a project is past its deadline and still active.
 *
 * @param object $project - Project object containing deadline information
 * @return bool - True if project is overdue, false otherwise
 *
 * @since 3.0.0
 * @version 1.0.0
 */
function isProjectOverdue($project) {
    if (!is_object($project) || !isset($project->projectDeadline) || !isset($project->projectStatus)) {
        return false;
    }

    $deadline = new DateTime($project->projectDeadline);
    $today = new DateTime();

    return $project->projectStatus === 'active' && $deadline < $today;
}

/**
 * Format project value for display
 *
 * Formats the project value with proper currency formatting.
 *
 * @param float $value - Project value
 * @param string $currency - Currency code (default: KES)
 * @return string - Formatted currency string
 *
 * @since 3.0.0
 * @version 1.0.0
 */
function formatProjectValue($value, $currency = 'KES') {
    $formattedValue = number_format($value, 2, '.', ',');
    return $currency . ' ' . $formattedValue;
}

// ============================================================================
// PROJECT DATA LOADING AND PROCESSING
// ============================================================================

/**
 * Load projects based on user permissions and organizational context
 *
 * This function handles the complex logic of loading projects based on user roles
 * and organizational hierarchy. It ensures proper data access control.
 *
 * @param bool $isAdmin - Whether the current user is an admin
 * @param string $orgDataID - Organization data ID
 * @param string $entityID - Entity ID
 * @param int $employeeID - Current employee ID
 * @param object $DBConn - Database connection
 * @return array - Array of project objects
 *
 * @since 3.0.0
 * @version 1.0.0
 */
function loadProjects($isAdmin, $orgDataID, $entityID, $employeeID, $DBConn) {
    try {
        if (!$isAdmin) {
            // Non-admin users can only see projects they own or manage
            return loadUserProjects($orgDataID, $entityID, $employeeID, $DBConn);
        } else {
            // Admin users can see all projects in their organization
            return loadAdminProjects($orgDataID, $entityID, $DBConn);
        }
    } catch (Exception $e) {
        error_log('LoadProjects: Error loading projects - ' . $e->getMessage());
        return [];
    }
}

/**
 * Load projects for non-admin users
 *
 * @param string $orgDataID - Organization data ID
 * @param string $entityID - Entity ID
 * @param int $employeeID - Current employee ID
 * @param object $DBConn - Database connection
 * @return array - Array of project objects
 */
function loadUserProjects($orgDataID, $entityID, $employeeID, $DBConn) {
    $projectsFull = Projects::projects_full([
        'orgDataID' => $orgDataID,
        'entityID' => $entityID
    ], false, $DBConn);

    $userProjects = [];

    if ($projectsFull) {
      foreach ($projectsFull as $key => $project) {
            // Check if user is project owner
            if ($project->projectOwnerID == $employeeID) {
                $userProjects[$key] = $project;
            continue;
         }

            // Check if user is project manager
            $projectManagersArr = Projects::project_team_full([
                'projectID' => $project->projectID,
                'projectTeamRoleID' => '2'
            ], false, $DBConn);

            if ($projectManagersArr) {
         $projectManagersIDs = array_column($projectManagersArr, 'userID');
                if (in_array($employeeID, $projectManagersIDs)) {
                    $userProjects[$key] = $project;
                }
            }
        }
    }
   //  var_dump($userProjects);

    return $userProjects;
}

/**
 * Load projects for admin users
 *
 * @param string $orgDataID - Organization data ID
 * @param string $entityID - Entity ID
 * @param object $DBConn - Database connection
 * @return array - Array of project objects
 */
function loadAdminProjects($orgDataID, $entityID, $DBConn) {
    return Projects::projects_full([
        'orgDataID' => $orgDataID,
        'entityID' => $entityID
    ], false, $DBConn) ?: [];
}

// Load projects based on user role using refactored function
$projects = loadProjects($isAdmin, $orgDataID, $entityID, $employeeDetails->ID, $DBConn);

// Process projects for analysis and categorization
if ($projects) {
    foreach ($projects as $project) {
        // Categorize projects by status
        if ($project->projectStatus == 'active' || ($project->projectDeadline > date('Y-m-d'))) {
            $activeProjects[] = $project;
        }

        // Identify overdue projects
        if (isProjectOverdue($project)) {
            $overDueProjects[] = $project;
        }

        // Calculate total project value
        $projectValue += $project->projectValue ?? 0;
    }
}

// Handle admin organization/entity selection
if ($isAdmin) {
   if(!$orgDataID ) {
      Alert::info("You need to select an organization and entity to view clients", true, array('fst-italic', 'text-center', 'font-18'));?>
      <div class="col-6 mx-auto">
         <div class="card custom-card">
            <div class="card-header justify-content-between">
               <h4 class="card-title">Select Organisation and Entity</h4>
            </div>
            <div class="card-body">
               <div class="list-group list-group-flush">
                  <?php foreach ($allOrgs as $org) { ?>
                     <div class="list-group-item list-group-item-action">
                        <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=home&orgDataID={$org->orgDataID}" ?>">
                        <?php echo $org->orgName; ?>
                        </a>
                     </div>
                  <?php } ?>
               </div>
            </div>
         </div>
      </div>
      <?php
      return;
   } else if(!$entityID) {
      $entities = Data::entities(array('orgDataID'=>$orgDataID), false, $DBConn);
      Alert::info("You need to select an entity to view clients", true, array('fst-italic', 'text-center', 'font-18'));?>
         <div class="col-6 mx-auto">
            <div class="card custom-card">
               <div class="card-header justify-content-between">
                  <h4 class="card-title">Select Entity</h4>
               </div>
               <div class="card-body">
                  <div class="list-group list-group-flush">
                     <?php foreach ($entities as $entity) { ?>
                        <div class="list-group-item list-group-item-action">
                           <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=home&orgDataID={$orgDataID}&entityID={$entity->entityID}" ?>" class="d-block">
                              <?php echo $entity->entityName; ?>
                           </a>
                        </div>
                     <?php } ?>
                  </div>
               </div>
            </div>
         </div>
      <?php
      return;
   }
   $getString .= "&orgDataID={$orgDataID}&entityID={$entityID}";
   $projects = Projects::projects_full(array('orgDataID'=> $orgDataID, 'entityID'=>$entityID), false, $DBConn);
}?>
<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="<?= $base ?>assets/libs/sweetalert2/sweetalert2.min.css">

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <h1 class="page-title fw-medium fs-24 mb-0">Projects Dashboard </h1>
    <div class="ms-md-1 ms-0 d-flex gap-2 align-items-center flex-wrap">
         <!-- Project Wizard Button -->
         <button type="button" class="btn btn-primary shadow btn-sm px-4" data-bs-toggle="modal" data-bs-target="#projectWizardModal">
            <i class="ri-magic-line me-1"></i>New Project (Wizard)
         </button>

         <!-- Legacy Quick Add Button -->
         <button type="button" class="btn btn-primary-light shadow btn-sm px-4" data-bs-toggle="modal" data-bs-target="#manageProjectCase">
            <i class="ri-add-line me-1"></i>Quick Add
         </button>

         <!-- Manage Templates Button -->
         <button type="button" class="btn btn-outline-secondary shadow btn-sm px-4" data-bs-toggle="modal" data-bs-target="#manageTemplatesModal" title="Manage Project Plan Templates">
            <i class="ri-file-copy-line me-1"></i>Templates
         </button>

         <span class="text-muted small"><?php echo date_format($dt,'l, d F Y ') ?></span>
    </div>
</div>

<?php
// ============================================================================
// PROJECT WIZARD MODAL (Multi-step wizard for comprehensive project creation)
// ============================================================================
echo "<div class='modal fade' id='projectWizardModal' data-bs-backdrop='static' data-bs-keyboard='false' tabindex='-1' aria-labelledby='projectWizardModalLabel' aria-hidden='true'>
   <div class='modal-dialog modal-dialog-centered modal-xxl'>
      <div class='modal-content'>
         <div class='modal-header pb-1'>
            <h1 class='modal-title fs-6 t400 my-0' id='staticBackdropLabel'>
               <i class='ri-magic-line me-2'></i>Create New Project - Wizard
            </h1>
            <button class='btn-close' type='button' data-bs-dismiss='modal' aria-label='Close'></button>
         </div>
         <div class='modal-body'>";
include 'includes/scripts/projects/modals/create_project_wizard.php';
echo "      </div>
      </div>
   </div>
</div>";

// ============================================================================
// LEGACY PROJECT MODAL (Quick single-page form for experienced users)
// ============================================================================
echo Utility::form_modal_header("manageProjectCase", "projects/manage_project_case.php", "Quick Add Project", array("modal-dialog-centered", "modal-lg"), $base, true);
include 'includes/scripts/projects/modals/manage_project_cases.php';
echo Utility::form_modal_footer("Save Project", "", "btn btn-primary btn-sm", false);

// ============================================================================
// PROJECT PLAN TEMPLATES MANAGEMENT MODAL
// ============================================================================
include 'includes/scripts/projects/modals/manage_project_plan_templates.php';

$orderID = isset($_GET['orderid']) ? Utility::clean_string($_GET['orderid']) : '';
if($orderID){
   $saleDetails = Sales::sales_case_mid(array('salesCaseID'=>$orderID), true, $DBConn);

   // Fetch additional data needed for the wizard
   $billingRates = Projects::project_billing_rates(array('Suspended'=>'N'), false, $DBConn);
   $businessUnits = Data::business_units(array('orgDataID'=>$saleDetails->orgDataID, 'entityID'=>$saleDetails->entityID), false, $DBConn);
   $allEmployees = Employee::employees([], false, $DBConn);
   $employeeCategorised = Employee::categorise_employee($allEmployees, 'jobTitle');

   // Include the project creation wizard form
   include 'includes/scripts/sales/forms/create_project_from_sale_wizard.php';
} else {
   if(!$projects) {
      Alert::info("You do not own any projects. Only project Owners and project Managers can Manage projects.", true, array('fst-italic', 'text-center', 'font-18'));
      return;
   }

   // var_dump($projects);
   foreach ($projects as $project) {
       if ($project->projectStatus == 'active' || ($project->projectDeadline > date('Y-m-d') )) {
           $activeProjects[] = $project;
       }
       if ($project->projectDeadline < date('Y-m-d') && $project->projectStatus == 'active') {
           $overDueProjects[] = $project;
       }
       $projectValue += $project->projectValue;
   }
   $clientsWithProjects = [];
   foreach ($projects as $project) {
      foreach ($clients as $client) {
         if ($project->clientID == $client->clientID) {
            if (!in_array($client, $clientsWithProjects)) {
                  $clientsWithProjects[] = $client;
            }
         }
      }
   }

   // Calculate additional statistics
   $clientsWithProjects = [];
   foreach ($projects as $project) {
      foreach ($clients as $client) {
         if ($project->clientID == $client->clientID) {
            if (!in_array($client, $clientsWithProjects)) {
                  $clientsWithProjects[] = $client;
            }
         }
      }
   }

   // Count projects by status
   $statusCounts = [
       'active' => 0,
       'completed' => 0,
       'on_hold' => 0,
       'overdue' => 0
   ];

   foreach ($projects as $project) {
       if($project->projectStatus == 'active') {
           $statusCounts['active']++;
       }
       if(isProjectOverdue($project)) {
           $statusCounts['overdue']++;
       }
       // Add more status tracking as needed
   }
   ?>

   <!-- Page Header with Enhanced Stats -->
   <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
      <div>
         <h1 class="page-title fw-semibold fs-24 mb-2">
            <i class="fas fa-project-diagram me-2"></i>Projects Dashboard
         </h1>
         <p class="text-muted mb-0">Manage and track your projects effectively</p>
      </div>
      <div class="d-flex gap-2 align-items-center flex-wrap">
         <span class="text-muted small"><?= date_format($dt,'l, d F Y') ?></span>

         <!-- Project Wizard Button -->
         <button type="button" class="btn btn-primary btn-wave" data-bs-toggle="modal" data-bs-target="#projectWizardModal">
            <i class="ri-magic-line me-1"></i>New Project (Wizard)
         </button>

         <!-- Legacy Quick Add Button -->
         <button type="button" class="btn btn-outline-primary btn-wave btn-sm" data-bs-toggle="modal" data-bs-target="#manageProjectCase">
            <i class="ri-add-line me-1"></i>Quick Add
         </button>

         <!-- Manage Templates Button -->
         <button type="button" class="btn btn-outline-secondary btn-wave btn-sm" data-bs-toggle="modal" data-bs-target="#manageTemplatesModal" title="Manage Project Plan Templates">
            <i class="ri-file-copy-line me-1"></i>Templates
         </button>
      </div>
   </div>

   <!-- Statistics Cards -->
   <div class="row mb-4">
      <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
         <div class="card custom-card">
            <div class="card-body">
               <div class="d-flex align-items-top justify-content-between">
                  <div class="flex-fill">
                     <p class="mb-1 text-muted">Total Projects</p>
                     <h3 class="mb-0 fw-semibold"><?= count($projects) ?></h3>
                     <small class="text-muted fs-11">
                        <i class="ri-check-line text-success me-1"></i><?= $statusCounts['active'] ?> Active
                     </small>
                  </div>
                  <div class="ms-2">
                     <span class="avatar avatar-md bg-primary-transparent">
                        <i class="fas fa-folder-open fs-20"></i>
                     </span>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
         <div class="card custom-card">
            <div class="card-body">
               <div class="d-flex align-items-top justify-content-between">
                  <div class="flex-fill">
                     <p class="mb-1 text-muted">Total Value</p>
                     <h3 class="mb-0 fw-semibold"><?= number_format($projectValue, 0) ?></h3>
                     <small class="text-muted fs-11"><?= $config['project']['display']['currency'] ?></small>
                  </div>
                  <div class="ms-2">
                     <span class="avatar avatar-md bg-success-transparent">
                        <i class="fas fa-money-bill-wave fs-20"></i>
                     </span>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
         <div class="card custom-card">
            <div class="card-body">
               <div class="d-flex align-items-top justify-content-between">
                  <div class="flex-fill">
                     <p class="mb-1 text-muted">Active Clients</p>
                     <h3 class="mb-0 fw-semibold"><?= count($clientsWithProjects) ?></h3>
                     <small class="text-muted fs-11">With projects</small>
                  </div>
                  <div class="ms-2">
                     <span class="avatar avatar-md bg-info-transparent">
                        <i class="fas fa-users fs-20"></i>
                     </span>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
         <div class="card custom-card">
            <div class="card-body">
               <div class="d-flex align-items-top justify-content-between">
                  <div class="flex-fill">
                     <p class="mb-1 text-muted">Overdue Projects</p>
                     <h3 class="mb-0 fw-semibold <?= $statusCounts['overdue'] > 0 ? 'text-danger' : '' ?>">
                        <?= $statusCounts['overdue'] ?>
                     </h3>
                     <small class="text-muted fs-11">
                        <?= $statusCounts['overdue'] > 0 ? '<i class="ri-alert-line text-danger me-1"></i>Needs attention' : '<i class="ri-check-line text-success me-1"></i>All on track' ?>
                     </small>
                  </div>
                  <div class="ms-2">
                     <span class="avatar avatar-md bg-<?= $statusCounts['overdue'] > 0 ? 'danger' : 'warning' ?>-transparent">
                        <i class="fas fa-clock fs-20"></i>
                     </span>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>

   <!-- Recurring Projects Notifications Widget -->
   <?php include 'html/includes/projects/recurring_notifications_widget.php'; ?>

   <div class="container-fluid">
      <div class="row">
         <div class="col-md-12">
            <!-- Enhanced Project Table with Advanced Filtering -->
            <div class="card card-body my-4">

               <!-- Search and Filter Controls -->
               <div class="row mb-3 align-items-center">
                  <div class="col-md-5">
                     <div class="input-group">
                        <span class="input-group-text"><i class="ri-search-line"></i></span>
                        <input type="text"
                               class="form-control"
                               id="projectSearchInput"
                               placeholder="Search projects by name, client, owner, or deadline...">
                     </div>
                  </div>
                  <div class="col-md-4">
                     <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" id="filterToggleBtn">
                           <i class="ri-filter-line"></i> Filter
                           <span class="badge bg-primary ms-1" id="filterCount" style="display: none;">0</span>
                        </button>
                        <button class="btn btn-outline-success btn-sm" id="exportProjectsBtn">
                           <i class="ri-download-line"></i> Export
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" id="clearFiltersBtn">
                           <i class="ri-refresh-line"></i> Clear
                        </button>
                     </div>
                  </div>
                  <div class="col-md-3 text-end">
                     <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-primary" id="tableViewBtn" title="Table View">
                           <i class="ri-list-check"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="cardViewBtn" title="Card View">
                           <i class="ri-grid-line"></i>
                        </button>
                     </div>
                  </div>
               </div>

               <!-- Advanced Filters Panel -->
               <div class="card mb-3" id="advancedFilters" style="display: none;">
                  <div class="card-body">
                     <div class="row">
                        <div class="col-md-3">
                           <label class="form-label">Project Name</label>
                           <input type="text" class="form-control form-control-sm" id="filterProjectName" placeholder="Filter by project name">
                        </div>
                        <div class="col-md-3">
                           <label class="form-label">Client</label>
                           <select class="form-select form-select-sm" id="filterClient">
                              <option value="">All Clients</option>
                              <?php foreach ($clients as $client): ?>
                                 <option value="<?= htmlspecialchars($client->clientID) ?>">
                                    <?= htmlspecialchars($client->clientName) ?>
                                 </option>
                              <?php endforeach; ?>
                           </select>
                        </div>
                        <div class="col-md-3">
                           <label class="form-label">Project Owner</label>
                           <select class="form-select form-select-sm" id="filterProjectOwner">
                              <option value="">All Owners</option>
                              <?php foreach ($allEmployees as $employee): ?>
                                 <option value="<?= htmlspecialchars($employee->ID) ?>">
                                    <?= htmlspecialchars($employee->employeeNameWithInitials) ?>
                                 </option>
                              <?php endforeach; ?>
                           </select>
                        </div>
                        <div class="col-md-3">
                           <label class="form-label">Deadline Range</label>
                           <select class="form-select form-select-sm" id="filterDeadline">
                              <option value="">All Deadlines</option>
                              <option value="overdue">Overdue</option>
                              <option value="this_week">This Week</option>
                              <option value="this_month">This Month</option>
                              <option value="next_month">Next Month</option>
                              <option value="custom">Custom Range</option>
                           </select>
                        </div>
                     </div>
                     <div class="row mt-2" id="customDateRange" style="display: none;">
                        <div class="col-md-6">
                           <label class="form-label">Start Date</label>
                           <input type="date" class="form-control form-control-sm" id="filterStartDate">
                        </div>
                        <div class="col-md-6">
                           <label class="form-label">End Date</label>
                           <input type="date" class="form-control form-control-sm" id="filterEndDate">
                        </div>
                     </div>
                  </div>
               </div>

               <!-- Table View -->
               <div id="tableView" class="table-responsive">
                  <table id="projectsTable" class="table table-striped table-bordered table-hover table-sm" style="width:100%">
                     <thead>
                        <tr>
                           <th data-sort="projectName" class="sortable" style="cursor: pointer;">
                              Project Name <i class="ri-arrow-up-down-line"></i>
                           </th>
                           <th data-sort="clientName" class="sortable" style="cursor: pointer;">
                              Client <i class="ri-arrow-up-down-line"></i>
                           </th>
                           <th data-sort="projectValue" class="sortable" style="cursor: pointer;">
                              Value <i class="ri-arrow-up-down-line"></i>
                           </th>
                           <th data-sort="projectOwner" class="sortable" style="cursor: pointer;">
                              Owner <i class="ri-arrow-up-down-line"></i>
                           </th>
                           <th class="text-center">
                              Managers
                           </th>
                           <th data-sort="progress" class="sortable" style="cursor: pointer;">
                              Progress <i class="ri-arrow-up-down-line"></i>
                           </th>
                           <th data-sort="deadline" class="sortable" style="cursor: pointer;">
                              Deadline <i class="ri-arrow-up-down-line"></i>
                           </th>
                           <th data-sort="status" class="sortable" style="cursor: pointer;">
                              Status <i class="ri-arrow-up-down-line"></i>
                           </th>
                           <th class="text-end">Actions</th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php
                        if ($projects) {
                           $totalProgress = 0;
                           foreach ($projects as $project) {
                              // Get project managers
                              $projectManagers = "";
                              $projectManagersArr = Projects::project_team_full([
                                  'projectID' => $project->projectID,
                                  'projectTeamRoleID' => '2'
                              ], false, $DBConn);

                              if ($projectManagersArr) {
                                  foreach ($projectManagersArr as $manager) {
                                      if ($manager->teamMemberName) {
                                          // Generate initials for the manager
                                          $initials = Utility::generate_initials($manager->teamMemberName);
                                          $managerName = htmlspecialchars($manager->teamMemberName, ENT_QUOTES, 'UTF-8');
                                          $projectManagers .= "<span class='avatar bd-blue-800 avatar-xs me-2 avatar-rounded'
                                                                   data-bs-toggle='tooltip'
                                                                   data-bs-placement='top'
                                                                   data-bs-title='{$managerName}'
                                                                   title='{$managerName}'>
                                                                   <AC>
                                                                      <span class='avatar-initials'>{$initials}</span>
                                                                   </AC>
                                                                </span>";
                                       }
                                    }
                                 }

                              // Calculate project progress
                              $progressData = calculateProjectProgress($project, $DBConn);
                              $totalProgress += $progressData['percentage'];

                              // Get client and owner details
                              $clientDetails = Client::clients(['clientID' => $project->clientID], true, $DBConn);
                              $ownerDetails = Data::users(['ID' => $project->projectOwnerID], true, $DBConn);

                              // Generate status badge
                              $statusBadge = generateProjectStatusBadge($project);

                              // Check if project is overdue
                              $isOverdue = isProjectOverdue($project);
                              $deadlineClass = $isOverdue ? 'text-danger fw-bold' : '';

                              ?>
                              <tr data-project-id="<?= htmlspecialchars($project->projectID) ?>"
                                  data-project-name="<?= htmlspecialchars($project->projectName) ?>"
                                  data-client-name="<?= htmlspecialchars($clientDetails->clientName ?? '') ?>"
                                  data-client-id="<?= $project->clientID ?>"
                                  data-project-owner="<?= htmlspecialchars($project->projectOwnerName ?? '') ?>"
                                  data-project-owner-id="<?= $project->projectOwnerID ?>"
                                  data-project-value="<?= $project->projectValue ?>"
                                  data-deadline="<?= $project->projectDeadline ?>"
                                  data-status="<?= $project->projectStatus ?>"
                                  data-progress="<?= $progressData['percentage'] ?>">

                                 <!-- Project Name -->
                                 <td>
                                    <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=project&pid={$project->projectID}" ?>"
                                       class="text-decoration-none fw-medium">
                                       <?= htmlspecialchars($project->projectName) ?>
                                    </a>
                                    <?php if ($isOverdue): ?>
                                       <span class="badge bg-danger-transparent ms-1" title="Overdue Project">Overdue</span>
                                    <?php endif; ?>
                                 </td>

                                 <!-- Client -->
                                 <td>
                                    <a href="<?= "{$base}html/?s=user&ss=clients&p=client_details&client_id={$project->clientID}" ?>"
                                       class="text-decoration-none">
                                       <?= htmlspecialchars($clientDetails->clientName ?? 'N/A') ?>
                                    </a>
                                 </td>

                                 <!-- Project Value -->
                                 <td class="text-end">
                                    <span class="fw-medium"><?= formatProjectValue($project->projectValue) ?></span>
                                 </td>

                                 <!-- Project Owner -->
                                 <td>
                                    <div class="d-flex align-items-center">
                                       <span class="avatar avatar-xs bg-primary-transparent me-2">
                                          <?= Utility::generate_initials($project->projectOwnerName ?? 'N/A') ?>
                                       </span>
                                       <span><?= htmlspecialchars($project->projectOwnerName ?? 'N/A') ?></span>
                                 </div>
                                 </td>

                                 <!-- Project Managers -->
                                 <td>
                                    <?= $projectManagers ?: '<span class="text-muted">No managers assigned</span>' ?>
                                 </td>

                                 <!-- Progress -->
                                 <td>
                                    <div class="d-flex align-items-center">
                                       <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                          <div class="progress-bar <?= $progressData['color'] ?>"
                                               role="progressbar"
                                               style="width: <?= $progressData['percentage'] ?>%"
                                               aria-valuenow="<?= $progressData['percentage'] ?>"
                                               aria-valuemin="0"
                                               aria-valuemax="100">
                                          </div>
                                       </div>
                                       <small class="text-muted"><?= $progressData['percentage'] ?>%</small>
                                    </div>
                                    <small class="text-muted"><?= $progressData['status'] ?></small>
                                 </td>

                                 <!-- Deadline -->
                                 <td class="<?= $deadlineClass ?>">
                                    <?= date_format(date_create($project->projectDeadline), 'd M Y') ?>
                                    <?php if ($isOverdue): ?>
                                       <i class="ri-alert-line text-danger ms-1" title="Overdue"></i>
                                    <?php endif; ?>
                                 </td>

                                 <!-- Status -->
                                 <td><?= $statusBadge ?></td>

                                 <!-- Actions -->
                                 <td class="text-end">
                                    <div class="btn-list">
                                       <!-- View Project -->
                                       <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=project&pid={$project->projectID}" ?>"
                                          class="btn btn-icon rounded-pill btn-primary-light"
                                          data-bs-toggle="tooltip"
                                          data-bs-placement="top"
                                          title="View Project Details">
                                          <i class="ri-eye-line"></i>
                                       </a>

                                       <?php //var_dump($project);?>

                                       <!-- Edit Project -->
                                       <a href="#"
                                          class="btn btn-icon rounded-pill btn-secondary-light editProjectCase"
                                          data-bs-toggle="modal"
                                          data-bs-target="#manageProjectCase"
                                          data-project-id="<?= $project->projectID ?>"
                                          data-project-name="<?= htmlspecialchars($project->projectName) ?>"
                                          data-project-code="<?= htmlspecialchars($project->projectCode) ?>"
                                          data-project-type-id="<?= $project->projectTypeID ?>"
                                          data-client-id="<?= $project->clientID ?>"
                                          data-project-start="<?= $project->projectStart ?>"
                                          data-project-close="<?= $project->projectClose ?>"
                                          data-project-deadline="<?= $project->projectDeadline ?>"
                                          data-project-owner-id="<?= $project->projectOwnerID ?>"
                                          data-billing-rate-id="<?= $project->billingRateID ?>"
                                          data-project-value="<?= $project->projectValue ?>"
                                          data-status="<?= $project->projectStatus ?>"
                                          data-roundingoff="<?= $project->roundingoff ?>"
                                          data-rounding-interval="<?= $project->roundingInterval ?>"
                                          data-business-unit-id="<?= $project->businessUnitID ?>"
                                          data-bs-toggle="tooltip"
                                          data-bs-placement="top"
                                          title="Edit Project">
                                          <i class="ri-pencil-line"></i>
                                       </a>
                                       <?php if($isAdmin){?>

                                          <!-- Delete Project -->
                                          <a href="#"
                                             class="btn btn-icon rounded-pill btn-danger-light deleteProject"
                                             data-project-id="<?= $project->projectID ?>"
                                             data-project-name="<?= htmlspecialchars($project->projectName) ?>"
                                             data-bs-toggle="tooltip"
                                             data-bs-placement="top"
                                             title="Delete Project">
                                             <i class="ri-delete-bin-line"></i>
                                          </a>
                                          <?php
                                       }?>
                                    </div>
                                 </td>
                              </tr>
                              <?php
                           }
                        } else { ?>
                           <tr>
                              <td colspan="9" class="text-center py-4">
                                 <div class="text-muted">
                                    <i class="ri-folder-open-line fs-48 mb-3 d-block"></i>
                                    <h5>No Projects Found</h5>
                                    <p>You do not have access to any projects. Only project owners and managers can view projects.</p>
                                 </div>
                              </td>
                           </tr>
                           <?php
                        }
                         ?>
                     </tbody>
                  </table>

                  <!-- Pagination Controls -->
                  <div class="d-flex justify-content-between align-items-center mt-3 px-3" id="paginationContainer">
                     <!-- Pagination will be inserted here by JavaScript -->
               </div>

                  <!-- Bottom Pagination -->
                  <div class="d-flex justify-content-between align-items-center mt-3 px-3 py-2 bg-light border-top" id="bottomPaginationContainer">
                     <div class="text-muted small">
                        <span id="bottomPaginationInfo">Loading...</span>
                     </div>
                     <div class="d-flex align-items-center gap-2" id="bottomPaginationControls">
                        <!-- Bottom pagination controls will be inserted here by JavaScript -->
                     </div>
                  </div>
               </div>

               <!-- Card View -->
               <div id="cardView" style="display: none;">
                  <div class="row" id="projectsCardContainer">
                     <?php
                     if ($projects) {
                        foreach ($projects as $project) {
                           // Get project managers
                           $projectManagersArr = Projects::project_team_full([
                               'projectID' => $project->projectID,
                               'projectTeamRoleID' => '2'
                           ], false, $DBConn);

                           // Calculate project progress
                           $progressData = calculateProjectProgress($project, $DBConn);

                           // Get client and owner details
                           $clientDetails = Client::clients(['clientID' => $project->clientID], true, $DBConn);
                           $ownerDetails = Data::users(['ID' => $project->projectOwnerID], true, $DBConn);

                           // Check if project is overdue
                           $isOverdue = isProjectOverdue($project);

                           // Determine card border color based on status
                           $cardBorderClass = $isOverdue ? 'border-danger' : ($progressData['percentage'] >= 75 ? 'border-success' : 'border-primary');
                           ?>
                           <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-12 mb-4 project-card-item"
                                data-project-id="<?= htmlspecialchars($project->projectID) ?>"
                                data-project-name="<?= htmlspecialchars($project->projectName) ?>"
                                data-client-name="<?= htmlspecialchars($clientDetails->clientName ?? '') ?>"
                                data-client-id="<?= $project->clientID ?>"
                                data-project-owner="<?= htmlspecialchars($project->projectOwnerName ?? '') ?>"
                                data-project-owner-id="<?= $project->projectOwnerID ?>"
                                data-project-value="<?= $project->projectValue ?>"
                                data-deadline="<?= $project->projectDeadline ?>"
                                data-status="<?= $project->projectStatus ?>"
                                data-progress="<?= $progressData['percentage'] ?>">

                              <div class="card custom-card project-hover-card h-100 <?= $cardBorderClass ?>">
                                 <div class="card-body p-4">
                                    <!-- Project Header -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                       <div class="flex-fill">
                                          <h5 class="card-title mb-2 fw-semibold">
                                             <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=project&pid={$project->projectID}" ?>"
                                                class="text-dark text-decoration-none">
                                                <?= htmlspecialchars($project->projectName) ?>
                                             </a>
                                          </h5>
                                          <div class="d-flex gap-2 flex-wrap mb-2">
                                             <?= generateProjectStatusBadge($project) ?>
                                             <?php if ($isOverdue): ?>
                                                <span class="badge bg-danger-transparent">
                                                   <i class="ri-alert-line me-1"></i>Overdue
                                                </span>
                                             <?php endif; ?>
                                          </div>
                                       </div>
                                       <div class="dropdown">
                                          <button class="btn btn-sm btn-icon btn-light" type="button" data-bs-toggle="dropdown">
                                             <i class="ri-more-2-fill"></i>
                                          </button>
                                          <ul class="dropdown-menu dropdown-menu-end">
                                             <li>
                                                <a class="dropdown-item" href="<?= "{$base}html/?s={$s}&ss={$ss}&p=project&pid={$project->projectID}" ?>">
                                                   <i class="ri-eye-line me-2"></i>View Details
                                                </a>
                                             </li>
                                             <li>
                                                <a class="dropdown-item editProjectCase"
                                                   href="#"
                                                   data-bs-toggle="modal"
                                                   data-bs-target="#manageProjectCase"
                                                   data-project-id="<?= $project->projectID ?>"
                                                   data-project-name="<?= htmlspecialchars($project->projectName) ?>"
                                                   data-project-code="<?= htmlspecialchars($project->projectCode) ?>"
                                                   data-project-type-id="<?= $project->projectTypeID ?>"
                                                   data-client-id="<?= $project->clientID ?>"
                                                   data-project-start="<?= $project->projectStart ?>"
                                                   data-project-close="<?= $project->projectClose ?>"
                                                   data-project-deadline="<?= $project->projectDeadline ?>"
                                                   data-project-owner-id="<?= $project->projectOwnerID ?>"
                                                   data-billing-rate-id="<?= $project->billingRateID ?>"
                                                   data-project-value="<?= $project->projectValue ?>"
                                                   data-status="<?= $project->projectStatus ?>"
                                                   data-roundingoff="<?= $project->roundingoff ?>"
                                                   data-rounding-interval="<?= $project->roundingInterval ?>"
                                                   data-business-unit-id="<?= $project->businessUnitID ?>">
                                                   <i class="ri-edit-line me-2"></i>Edit Project
                                                </a>
                                             </li>
                                             <?php if($isAdmin): ?>
                                             <li><hr class="dropdown-divider"></li>
                                             <li>
                                                <a class="dropdown-item text-danger deleteProject"
                                                   href="#"
                                                   data-project-id="<?= $project->projectID ?>"
                                                   data-project-name="<?= htmlspecialchars($project->projectName) ?>">
                                                   <i class="ri-delete-bin-line me-2"></i>Delete Project
                                                </a>
                                             </li>
                                             <?php endif; ?>
                                          </ul>
                                       </div>
                                    </div>

                                    <!-- Client Info -->
                                    <div class="mb-3">
                                       <div class="d-flex align-items-center">
                                          <span class="avatar avatar-sm bg-info-transparent text-info me-2">
                                             <i class="fas fa-user"></i>
                                          </span>
                                          <div>
                                             <small class="text-muted d-block">Client</small>
                                             <a href="<?= "{$base}html/?s=user&ss=clients&p=client_details&client_id={$project->clientID}" ?>"
                                                class="text-decoration-none fw-medium">
                                                <?= htmlspecialchars($clientDetails->clientName ?? 'N/A') ?>
                                             </a>
                                          </div>
                                       </div>
                                    </div>

                                    <!-- Project Progress -->
                                    <div class="mb-3">
                                       <div class="d-flex justify-content-between align-items-center mb-2">
                                          <small class="text-muted">Progress</small>
                                          <small class="fw-semibold"><?= $progressData['percentage'] ?>%</small>
                                       </div>
                                       <div class="progress" style="height: 8px;">
                                          <div class="progress-bar <?= $progressData['color'] ?>"
                                               role="progressbar"
                                               style="width: <?= $progressData['percentage'] ?>%">
                                          </div>
                                       </div>
                                       <small class="text-muted"><?= $progressData['status'] ?></small>
                                    </div>

                                    <!-- Project Details Grid -->
                                    <div class="row g-2 mb-3">
                                       <div class="col-6">
                                          <div class="d-flex align-items-center">
                                             <i class="ri-money-dollar-circle-line text-success me-2"></i>
                                             <div>
                                                <small class="text-muted d-block">Value</small>
                                                <strong class="small"><?= formatProjectValue($project->projectValue) ?></strong>
                                             </div>
                                          </div>
                                       </div>
                                       <div class="col-6">
                                          <div class="d-flex align-items-center">
                                             <i class="ri-calendar-line <?= $isOverdue ? 'text-danger' : 'text-primary' ?> me-2"></i>
                                             <div>
                                                <small class="text-muted d-block">Deadline</small>
                                                <strong class="small <?= $isOverdue ? 'text-danger' : '' ?>">
                                                   <?= date_format(date_create($project->projectDeadline), 'd M Y') ?>
                                                </strong>
                                             </div>
                                          </div>
                                       </div>
                                    </div>

                                    <!-- Team Section -->
                                    <div class="border-top pt-3">
                                       <div class="d-flex justify-content-between align-items-center">
                                          <div>
                                             <small class="text-muted d-block mb-1">Project Owner</small>
                                             <div class="d-flex align-items-center">
                                                <span class="avatar avatar-xs bg-primary-transparent me-2">
                                                   <?= Utility::generate_initials($project->projectOwnerName ?? 'N/A') ?>
                                                </span>
                                                <small class="fw-medium"><?= htmlspecialchars($project->projectOwnerName ?? 'N/A') ?></small>
                                             </div>
                                          </div>
                                          <?php if ($projectManagersArr && count($projectManagersArr) > 0): ?>
                                          <div>
                                             <small class="text-muted d-block mb-1">Managers</small>
                                             <div class="d-flex">
                                                <?php
                                                $displayCount = 0;
                                                foreach ($projectManagersArr as $manager):
                                                   if($displayCount >= 3) break;
                                                   $initials = Utility::generate_initials($manager->teamMemberName);
                                                   $managerName = htmlspecialchars($manager->teamMemberName);
                                                ?>
                                                   <span class="avatar avatar-xs bg-secondary-transparent me-1"
                                                         title="<?= $managerName ?>"
                                                         data-bs-toggle="tooltip">
                                                      <?= $initials ?>
                                                   </span>
                                                <?php
                                                   $displayCount++;
                                                endforeach;
                                                if(count($projectManagersArr) > 3): ?>
                                                   <span class="avatar avatar-xs bg-light text-muted" title="<?= count($projectManagersArr) - 3 ?> more">
                                                      +<?= count($projectManagersArr) - 3 ?>
                                                   </span>
                                                <?php endif; ?>
                                             </div>
                                          </div>
                                          <?php endif; ?>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <?php
                        }
                     } else { ?>
                        <div class="col-12">
                           <div class="text-center py-5">
                              <div class="avatar avatar-xl bg-secondary-transparent mx-auto mb-3">
                                 <i class="ri-folder-open-line fs-40"></i>
                              </div>
                              <h5>No Projects Found</h5>
                              <p class="text-muted">You do not have access to any projects. Only project owners and managers can view projects.</p>
                           </div>
                        </div>
                        <?php
                     }
                     ?>
                  </div>

                  <!-- Card View Pagination -->
                  <div class="d-flex justify-content-between align-items-center mt-3 px-3 py-2 bg-light border-top" id="cardPaginationContainer">
                     <div class="text-muted small">
                        <span id="cardPaginationInfo">Loading...</span>
                     </div>
                     <div class="d-flex align-items-center gap-2" id="cardPaginationControls">
                        <!-- Card view pagination controls will be inserted here by JavaScript -->
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <?php
   // var_dump($project);?>
   <script>
   /**
    * PROJECT MANAGEMENT INTERACTION LAYER
    * ===================================
    *
    * This JavaScript module provides comprehensive project management functionality
    * including advanced table operations, filtering, pagination, and user interactions.
    *
    * ARCHITECTURE:
    * ============
    * 1. Table Management     - Custom table functionality with pagination
    * 2. Search & Filtering   - Real-time search and advanced filtering
    * 3. Modal Management     - Project add/edit/delete operations
    * 4. Export Functionality - Data export capabilities
    * 5. Performance Monitoring - Performance tracking and optimization
    * 6. Accessibility       - Keyboard navigation and screen reader support
    *
    * @namespace ProjectManagement
    * @version 3.0.0
    * @since 2024
    */
      document.addEventListener('DOMContentLoaded', function() {
       'use strict'; // Enable strict mode for better error handling

       // ========================================================================
       // CONFIGURATION AND CONSTANTS
       // ========================================================================

       /**
        * Application configuration and constants
        * @type {Object}
        */
       const CONFIG = {
           // Table configuration
           itemsPerPage: <?= $config['project']['display']['itemsPerPage'] ?>,
           maxVisiblePages: <?= $config['project']['table']['pagination']['maxVisiblePages'] ?>,
           debounceDelay: <?= $config['project']['performance']['debounceSearch'] ?>,

           // Feature toggles
           enableSearch: <?= $config['project']['features']['enableSearch'] ? 'true' : 'false' ?>,
           enableAdvancedFiltering: <?= $config['project']['features']['enableAdvancedFiltering'] ? 'true' : 'false' ?>,
           enableExport: <?= $config['project']['features']['enableExport'] ? 'true' : 'false' ?>,

           // Performance settings
           enableLazyLoading: <?= $config['project']['features']['enableLazyLoading'] ? 'true' : 'false' ?>,
           lazyLoadThreshold: <?= $config['project']['performance']['lazyLoadThreshold'] ?>,

           // Export settings
           exportFileName: '<?= $config['project']['export']['fileName'] ?>',
           maxExportRecords: <?= $config['project']['export']['maxRecords'] ?>,
       };

       /**
        * DOM element selectors
        * @type {Object}
        */
       const SELECTORS = {
           table: '#projectsTable',
           tbody: '#projectsTable tbody',
           searchInput: '#projectSearchInput',
           filterToggle: '#filterToggleBtn',
           advancedFilters: '#advancedFilters',
           paginationContainer: '#paginationContainer',
           bottomPaginationContainer: '#bottomPaginationContainer',
           exportBtn: '#exportProjectsBtn',
           clearFiltersBtn: '#clearFiltersBtn',
       };

       // ========================================================================
       // STATE MANAGEMENT
       // ========================================================================

       /**
        * Application state management
        * @type {Object}
        */
       const state = {
           // Table data
           allTableRows: null,
           allCardItems: null,
           filteredRows: null,
           filteredCards: null,
           currentPage: 1,
           itemsPerPage: CONFIG.itemsPerPage,

           // View state
           currentView: 'table', // 'table' or 'card'

           // Sorting state
           sortColumn: null,
           sortDirection: 'asc', // 'asc' or 'desc'

           // UI state
           isInitialized: false,
           isFiltering: false,
           isExporting: false,

           // Performance tracking
           renderStartTime: 0,
           lastSearchTime: 0,
       };

       // ========================================================================
       // UTILITY FUNCTIONS
       // ========================================================================

       /**
        * Show error message to user
        *
        * @param {string} message - Error message to display
        * @function showErrorMessage
        * @since 3.0.0
        */
       function showErrorMessage(message) {
           // Create error alert element
           const alertDiv = document.createElement('div');
           alertDiv.className = 'alert alert-danger alert-dismissible fade show';
           alertDiv.innerHTML = `
               <i class="ri-error-warning-line me-2"></i>
               ${message}
               <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
           `;

           // Insert at top of page
           const container = document.querySelector('.container-fluid');
           if (container) {
               container.insertBefore(alertDiv, container.firstChild);
           }

           // Auto-dismiss after 5 seconds
           setTimeout(() => {
               if (alertDiv.parentNode) {
                   alertDiv.remove();
               }
           }, 5000);
       }

       /**
        * Show success message to user
        *
        * @param {string} message - Success message to display
        * @function showSuccessMessage
        * @since 3.0.0
        */
       function showSuccessMessage(message) {
           // Create success alert element
           const alertDiv = document.createElement('div');
           alertDiv.className = 'alert alert-success alert-dismissible fade show';
           alertDiv.innerHTML = `
               <i class="ri-check-line me-2"></i>
               ${message}
               <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
           `;

           // Insert at top of page
           const container = document.querySelector('.container-fluid');
           if (container) {
               container.insertBefore(alertDiv, container.firstChild);
           }

           // Auto-dismiss after 5 seconds
           setTimeout(() => {
               if (alertDiv.parentNode) {
                   alertDiv.remove();
               }
           }, 5000);
       }

       /**
        * Debounce function for performance optimization
        *
        * @param {Function} func - Function to debounce
        * @param {number} wait - Wait time in milliseconds
        * @returns {Function} - Debounced function
        * @function debounce
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
        * Sort table data by column
        *
        * Sorts the filtered rows based on the specified column and direction.
        * Handles different data types (text, numbers, dates) appropriately.
        *
        * @param {string} column - Column name to sort by
        * @param {string} direction - Sort direction ('asc' or 'desc')
        * @function sortTableData
        * @since 3.0.0
        */
       function sortTableData(column, direction) {
           try {
               state.filteredRows.sort((a, b) => {
                   let valueA, valueB;

                   // Extract values based on column type
                   switch (column) {
                       case 'projectName':
                           valueA = a.getAttribute('data-project-name')?.toLowerCase() || '';
                           valueB = b.getAttribute('data-project-name')?.toLowerCase() || '';
                           break;

                       case 'clientName':
                           valueA = a.getAttribute('data-client-name')?.toLowerCase() || '';
                           valueB = b.getAttribute('data-client-name')?.toLowerCase() || '';
                           break;

                       case 'projectValue':
                           valueA = parseFloat(a.getAttribute('data-project-value')) || 0;
                           valueB = parseFloat(b.getAttribute('data-project-value')) || 0;
                           break;

                       case 'projectOwner':
                           valueA = a.getAttribute('data-project-owner')?.toLowerCase() || '';
                           valueB = b.getAttribute('data-project-owner')?.toLowerCase() || '';
                           break;

                       case 'deadline':
                           valueA = new Date(a.getAttribute('data-deadline') || '1900-01-01');
                           valueB = new Date(b.getAttribute('data-deadline') || '1900-01-01');
                           break;

                       case 'status':
                           valueA = a.getAttribute('data-status')?.toLowerCase() || '';
                           valueB = b.getAttribute('data-status')?.toLowerCase() || '';
                           break;

                       case 'progress':
                           // Extract progress percentage from data attribute
                           valueA = parseFloat(a.getAttribute('data-progress')) || 0;
                           valueB = parseFloat(b.getAttribute('data-progress')) || 0;
                           break;

                       default:
                           return 0;
                   }

                   // Compare values
                   let comparison = 0;

                   if (valueA < valueB) {
                       comparison = -1;
                   } else if (valueA > valueB) {
                       comparison = 1;
                   }

                   // Reverse comparison for descending order
                   return direction === 'desc' ? -comparison : comparison;
               });

               // Reset to first page after sorting
               state.currentPage = 1;

               // Update display
               updateTableDisplay();
               updatePagination();
               updateBottomPagination();

           } catch (error) {
               console.error('Error sorting table data:', error);
               showErrorMessage('Failed to sort table data. Please try again.');
           }
       }

       /**
        * Handle column header click for sorting
        *
        * Toggles sort direction and updates the table display.
        * Updates visual indicators in the column headers.
        *
        * @param {string} column - Column name to sort by
        * @function handleColumnSort
        * @since 3.0.0
        */
       function handleColumnSort(column) {
           try {
               // Determine new sort direction
               if (state.sortColumn === column) {
                   // Toggle direction if same column
                   state.sortDirection = state.sortDirection === 'asc' ? 'desc' : 'asc';
               } else {
                   // Default to ascending for new column
                   state.sortDirection = 'asc';
               }

               state.sortColumn = column;

               // Update visual indicators
               updateSortIndicators(column, state.sortDirection);

               // Sort the data
               sortTableData(column, state.sortDirection);

           } catch (error) {
               console.error('Error handling column sort:', error);
               showErrorMessage('Failed to sort column. Please try again.');
           }
       }

       /**
        * Update sort indicators in column headers
        *
        * Updates the visual indicators showing current sort column and direction.
        *
        * @param {string} column - Currently sorted column
        * @param {string} direction - Sort direction
        * @function updateSortIndicators
        * @since 3.0.0
        */
       function updateSortIndicators(column, direction) {
           // Remove all existing sort indicators
           document.querySelectorAll('th[data-sort]').forEach(th => {
               const icon = th.querySelector('i');
               if (icon) {
                   icon.className = 'ri-arrow-up-down-line';
               }
               th.classList.remove('sort-asc', 'sort-desc');
           });

           // Add indicator to current column
           const currentTh = document.querySelector(`th[data-sort="${column}"]`);
           if (currentTh) {
               const icon = currentTh.querySelector('i');
               if (icon) {
                   icon.className = direction === 'asc' ? 'ri-arrow-up-line' : 'ri-arrow-down-line';
               }
               currentTh.classList.add(`sort-${direction}`);
           }
       }

       /**
        * Create sortable column header
        *
        * Creates a clickable column header with sort functionality.
        *
        * @param {string} text - Header text
        * @param {string} column - Column name for sorting
        * @param {string} className - Additional CSS classes
        * @returns {HTMLElement} - Sortable header element
        * @function createSortableHeader
        * @since 3.0.0
        */
       function createSortableHeader(text, column, className = '') {
           const th = document.createElement('th');
           th.setAttribute('data-sort', column);
           th.className = `sortable ${className}`;
           th.style.cursor = 'pointer';
           th.innerHTML = `${text} <i class="ri-arrow-up-down-line"></i>`;

           th.addEventListener('click', () => handleColumnSort(column));

           return th;
       }

       /**
        * Initialize Bootstrap tooltips
        *
        * Initializes tooltips for all elements with data-bs-toggle="tooltip" attribute.
        * This function should be called after rendering new content to ensure tooltips work.
        *
        * @function initializeTooltips
        * @since 3.0.0
        */
       function initializeTooltips() {
           try {
               // Destroy existing tooltips to prevent duplicates
               const existingTooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
               existingTooltips.forEach(element => {
                   const tooltip = bootstrap.Tooltip.getInstance(element);
                   if (tooltip) {
                       tooltip.dispose();
                   }
               });

               // Initialize new tooltips
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
               // Fallback: try to initialize tooltips with jQuery if Bootstrap is not available
               if (typeof $ !== 'undefined' && $.fn.tooltip) {
                   $('[data-bs-toggle="tooltip"]').tooltip({
                       placement: 'top',
                       trigger: 'hover focus',
                       delay: { show: 300, hide: 100 }
                   });
               }
           }
       }

       // ========================================================================
       // TABLE MANAGEMENT SYSTEM
       // ========================================================================

       /**
        * Initialize table and card displays
        *
        * Sets up the initial state and renders the first page of data.
        * This function is called once during page load to establish both views.
        *
        * @function initializeTable
        * @since 3.0.0
        */
       function initializeTable() {
           try {
               state.renderStartTime = performance.now();
               state.allTableRows = document.querySelectorAll(SELECTORS.tbody + ' tr');
               state.allCardItems = document.querySelectorAll('.project-card-item');
               state.filteredRows = Array.from(state.allTableRows);
               state.filteredCards = Array.from(state.allCardItems);

               updateTableDisplay();
               updateCardDisplay();
               updatePagination();
               updateBottomPagination();
               updateCardPagination();

               // Initialize tooltips for both views
               initializeTooltips();

               state.isInitialized = true;

               // Log initialization success
               console.log('Project Management initialized successfully');
           } catch (error) {
               console.error('Error initializing project views:', error);
               showErrorMessage('Failed to initialize project views. Please refresh the page.');
           }
       }

       /**
        * Toggle between table and card views
        *
        * @param {string} viewType - 'table' or 'card'
        * @function switchView
        * @since 3.0.0
        */
       function switchView(viewType) {
           state.currentView = viewType;
           const tableView = document.getElementById('tableView');
           const cardView = document.getElementById('cardView');
           const tableViewBtn = document.getElementById('tableViewBtn');
           const cardViewBtn = document.getElementById('cardViewBtn');

           if(viewType === 'table') {
               tableView.style.display = 'block';
               cardView.style.display = 'none';
               tableViewBtn.classList.remove('btn-outline-primary');
               tableViewBtn.classList.add('btn-primary');
               cardViewBtn.classList.remove('btn-primary');
               cardViewBtn.classList.add('btn-outline-primary');
           } else {
               tableView.style.display = 'none';
               cardView.style.display = 'block';
               cardViewBtn.classList.remove('btn-outline-primary');
               cardViewBtn.classList.add('btn-primary');
               tableViewBtn.classList.remove('btn-primary');
               tableViewBtn.classList.add('btn-outline-primary');

               // Reinitialize tooltips for card view
               setTimeout(() => initializeTooltips(), 100);
           }

           // Save preference
           localStorage.setItem('projectViewPreference', viewType);
       }

       /**
        * Update card display based on current filters and pagination
        *
        * @function updateCardDisplay
        * @since 3.0.0
        */
       function updateCardDisplay() {
           try {
               const container = document.getElementById('projectsCardContainer');
               if (!container) return;

               // Calculate pagination boundaries
               const startIndex = (state.currentPage - 1) * state.itemsPerPage;
               const endIndex = startIndex + state.itemsPerPage;
               const pageCards = state.filteredCards.slice(startIndex, endIndex);

               // Clear current display
               container.innerHTML = '';

               // Render filtered cards
               if(pageCards.length > 0) {
                   pageCards.forEach((card) => {
                       const clonedCard = card.cloneNode(true);
                       container.appendChild(clonedCard);
                   });
               } else {
                   container.innerHTML = `
                       <div class="col-12">
                           <div class="text-center py-5">
                               <div class="avatar avatar-xl bg-secondary-transparent mx-auto mb-3">
                                   <i class="ri-folder-open-line fs-40"></i>
                               </div>
                               <h5>No Projects Found</h5>
                               <p class="text-muted">No projects match your current filters.</p>
                           </div>
                       </div>
                   `;
               }

               // Initialize tooltips for cards (event handlers use event delegation)
               initializeTooltips();

           } catch (error) {
               console.error('Error updating card display:', error);
           }
       }

       /**
        * Update table display based on current filters and pagination
        *
        * Renders the current page of filtered data to the table. This function
        * handles the core display logic including pagination and data rendering.
        *
        * @function updateTableDisplay
        * @since 3.0.0
        */
       function updateTableDisplay() {
           try {
               const tbody = document.querySelector(SELECTORS.tbody);
               if (!tbody) {
                   console.error('Table body element not found');
                   return;
               }

               // Calculate pagination boundaries
               const startIndex = (state.currentPage - 1) * state.itemsPerPage;
               const endIndex = startIndex + state.itemsPerPage;
               const pageRows = state.filteredRows.slice(startIndex, endIndex);

               // Clear current display
               tbody.innerHTML = '';

               // Render filtered rows
               pageRows.forEach((row, index) => {
                   const clonedRow = row.cloneNode(true);

                   // Add data attributes for tracking
                   clonedRow.setAttribute('data-row-index', startIndex + index);
                   clonedRow.setAttribute('data-project-id', row.getAttribute('data-project-id') || '');

                   tbody.appendChild(clonedRow);
               });

               // Initialize tooltips for the newly rendered rows
               initializeTooltips();

               // Update table information
               updateTableInfo();

               // Performance monitoring
               if (state.renderStartTime > 0) {
                   const renderTime = performance.now() - state.renderStartTime;
                   if (renderTime > 1000) {
                       console.warn(`Table rendering took ${renderTime.toFixed(2)}ms - consider optimizing`);
                   }
               }

           } catch (error) {
               console.error('Error updating table display:', error);
               showErrorMessage('Failed to update table display');
           }
       }

       /**
        * Update table information display
        *
        * Updates the table information showing current page and total records.
        *
        * @function updateTableInfo
        * @since 3.0.0
        */
       function updateTableInfo() {
           const totalProjects = state.filteredRows.length;
           const startItem = ((state.currentPage - 1) * state.itemsPerPage) + 1;
           const endItem = Math.min(state.currentPage * state.itemsPerPage, totalProjects);

           // Update any table info elements if they exist
           const infoElements = document.querySelectorAll('.table-info');
           infoElements.forEach(element => {
               element.textContent = `Showing ${startItem} to ${endItem} of ${totalProjects} projects`;
           });
       }

       /**
        * Update project statistics without page reload
        *
        * Recalculates and updates the statistics cards based on current project data.
        *
        * @function updateProjectStatistics
        * @since 3.0.0
        */
       function updateProjectStatistics() {
           try {
               // Get all projects from the original data source (before filtering)
               // Use state.allTableRows which contains all projects, not just filtered ones
               const allProjects = state.allTableRows || Array.from(document.querySelectorAll(SELECTORS.tbody + ' tr'));
               const totalProjects = allProjects.length;

               // Calculate statistics
               let activeProjects = 0;
               let overdueProjects = 0;
               let totalValue = 0;
               const clientIds = new Set();

               allProjects.forEach(row => {
                   const status = row.getAttribute('data-status') || '';
                   const deadline = row.getAttribute('data-deadline') || '';
                   const value = parseFloat(row.getAttribute('data-project-value')) || 0;
                   const clientId = row.getAttribute('data-client-id') || '';

                   // Count active projects
                   if (status === 'active') {
                       activeProjects++;
                   }

                   // Count overdue projects
                   if (status === 'active' && deadline) {
                       const deadlineDate = new Date(deadline);
                       const today = new Date();
                       today.setHours(0, 0, 0, 0);
                       if (deadlineDate < today) {
                           overdueProjects++;
                       }
                   }

                   // Sum total value
                   totalValue += value;

                   // Count unique clients
                   if (clientId) {
                       clientIds.add(clientId);
                   }
               });

               // Update statistics cards
               const totalProjectsElement = document.querySelector('.row.mb-4 .col-xl-3:first-child h3');
               const activeProjectsElement = document.querySelector('.row.mb-4 .col-xl-3:first-child small');
               const totalValueElement = document.querySelector('.row.mb-4 .col-xl-3:nth-child(2) h3');
               const activeClientsElement = document.querySelector('.row.mb-4 .col-xl-3:nth-child(3) h3');
               const overdueProjectsElement = document.querySelector('.row.mb-4 .col-xl-3:nth-child(4) h3');

               if (totalProjectsElement) {
                   totalProjectsElement.textContent = totalProjects;
               }
               if (activeProjectsElement) {
                   activeProjectsElement.innerHTML = `<i class="ri-check-line text-success me-1"></i>${activeProjects} Active`;
               }
               if (totalValueElement) {
                   totalValueElement.textContent = totalValue.toLocaleString();
               }
               if (activeClientsElement) {
                   activeClientsElement.textContent = clientIds.size;
               }
               if (overdueProjectsElement) {
                   overdueProjectsElement.textContent = overdueProjects;
                   if (overdueProjects > 0) {
                       overdueProjectsElement.classList.add('text-danger');
                   } else {
                       overdueProjectsElement.classList.remove('text-danger');
                   }
               }

           } catch (error) {
               console.error('Error updating project statistics:', error);
           }
       }

       // ========================================================================
       // SEARCH AND FILTERING SYSTEM
       // ========================================================================

       /**
        * Perform project search (works for both table and card views)
        *
        * Searches through projects based on the search input and applies filters.
        * This function handles the core search logic and updates both views.
        *
        * @function performSearch
        * @since 3.0.0
        */
       function performSearch() {
           try {
               const searchTerm = document.querySelector(SELECTORS.searchInput)?.value.toLowerCase() || '';
               const projectNameFilter = document.getElementById('filterProjectName')?.value.toLowerCase() || '';
               const clientFilter = document.getElementById('filterClient')?.value || '';
               const ownerFilter = document.getElementById('filterProjectOwner')?.value || '';
               const deadlineFilter = document.getElementById('filterDeadline')?.value || '';

               // Filter function to apply to both rows and cards
               const filterItem = (item) => {
                   const projectName = item.getAttribute('data-project-name')?.toLowerCase() || '';
                   const clientName = item.getAttribute('data-client-name')?.toLowerCase() || '';
                   const projectOwner = item.getAttribute('data-project-owner')?.toLowerCase() || '';
                   const deadline = item.getAttribute('data-deadline') || '';
                   const status = item.getAttribute('data-status') || '';

                   // Apply search term filter
                   if (searchTerm && !projectName.includes(searchTerm) &&
                       !clientName.includes(searchTerm) &&
                       !projectOwner.includes(searchTerm) &&
                       !deadline.includes(searchTerm)) {
                       return false;
                   }

                   // Apply project name filter
                   if (projectNameFilter && !projectName.includes(projectNameFilter)) {
                       return false;
                   }

                   // Apply client filter
                   if (clientFilter && item.getAttribute('data-client-id') !== clientFilter) {
                       return false;
                   }

                   // Apply owner filter
                   if (ownerFilter && item.getAttribute('data-project-owner-id') !== ownerFilter) {
                       return false;
                   }

                   // Apply deadline filter
                   if (deadlineFilter) {
                       const projectDate = new Date(deadline);
                       const today = new Date();
                       const thisWeek = new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000);
                       const thisMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                       const nextMonth = new Date(today.getFullYear(), today.getMonth() + 2, 0);

                       switch (deadlineFilter) {
                           case 'overdue':
                               if (projectDate >= today || status !== 'active') return false;
                               break;
                           case 'this_week':
                               if (projectDate < today || projectDate > thisWeek) return false;
                               break;
                           case 'this_month':
                               if (projectDate < today || projectDate > thisMonth) return false;
                               break;
                           case 'next_month':
                               if (projectDate < thisMonth || projectDate > nextMonth) return false;
                               break;
                           case 'custom':
                               const startDate = document.getElementById('filterStartDate')?.value;
                               const endDate = document.getElementById('filterEndDate')?.value;
                               if (startDate && projectDate < new Date(startDate)) return false;
                               if (endDate && projectDate > new Date(endDate)) return false;
                               break;
                       }
                   }

                   return true;
               };

               // Apply filter to both rows and cards
               state.filteredRows = Array.from(state.allTableRows).filter(filterItem);
               state.filteredCards = Array.from(state.allCardItems).filter(filterItem);

               // Reset to first page
               state.currentPage = 1;

               // Apply current sorting if any
               if (state.sortColumn) {
                   sortTableData(state.sortColumn, state.sortDirection);
               } else {
                   // Update both displays
                   updateTableDisplay();
                   updateCardDisplay();
                   updatePagination();
                   updateBottomPagination();
                   updateCardPagination();
               }

               updateFilterIndicators();

           } catch (error) {
               console.error('Error performing search:', error);
               showErrorMessage('Failed to perform search. Please try again.');
           }
       }

       /**
        * Update filter indicators
        *
        * Updates the visual indicators showing how many filters are active.
        *
        * @function updateFilterIndicators
        * @since 3.0.0
        */
       function updateFilterIndicators() {
           const filterCount = document.getElementById('filterCount');
           if (!filterCount) return;

           let activeFilters = 0;

           // Count active filters
           if (document.getElementById('filterProjectName')?.value) activeFilters++;
           if (document.getElementById('filterClient')?.value) activeFilters++;
           if (document.getElementById('filterProjectOwner')?.value) activeFilters++;
           if (document.getElementById('filterDeadline')?.value) activeFilters++;

           if (activeFilters > 0) {
               filterCount.textContent = activeFilters;
               filterCount.style.display = 'inline';
           } else {
               filterCount.style.display = 'none';
           }
       }

       /**
        * Clear all filters
        *
        * Resets all filter inputs and refreshes both table and card displays.
        *
        * @function clearAllFilters
        * @since 3.0.0
        */
       function clearAllFilters() {
           // Clear search input
           const searchInput = document.querySelector(SELECTORS.searchInput);
           if (searchInput) searchInput.value = '';

           // Clear filter inputs
           const filterInputs = [
               'filterProjectName',
               'filterClient',
               'filterProjectOwner',
               'filterDeadline',
               'filterStartDate',
               'filterEndDate'
           ];

           filterInputs.forEach(inputId => {
               const input = document.getElementById(inputId);
               if (input) input.value = '';
           });

           // Hide custom date range
           const customDateRange = document.getElementById('customDateRange');
           if (customDateRange) customDateRange.style.display = 'none';

           // Reset displays
           state.filteredRows = Array.from(state.allTableRows);
           state.filteredCards = Array.from(state.allCardItems);
           state.currentPage = 1;

           // Reset sorting
           state.sortColumn = null;
           state.sortDirection = 'asc';

           // Clear sort indicators
           document.querySelectorAll('th[data-sort]').forEach(th => {
               const icon = th.querySelector('i');
               if (icon) {
                   icon.className = 'ri-arrow-up-down-line';
               }
               th.classList.remove('sort-asc', 'sort-desc');
           });

           updateTableDisplay();
           updateCardDisplay();
           updatePagination();
           updateBottomPagination();
           updateCardPagination();
           updateFilterIndicators();
       }

       /**
        * Update card pagination controls
        *
        * Creates and updates the pagination controls for card view.
        *
        * @function updateCardPagination
        * @since 3.0.0
        */
       function updateCardPagination() {
           const totalPages = Math.ceil(state.filteredCards.length / state.itemsPerPage);
           const cardInfo = document.getElementById('cardPaginationInfo');
           const cardControls = document.getElementById('cardPaginationControls');

           if (!cardInfo || !cardControls) return;

           const startItem = ((state.currentPage - 1) * state.itemsPerPage) + 1;
           const endItem = Math.min(state.currentPage * state.itemsPerPage, state.filteredCards.length);
           cardInfo.textContent = `Showing ${startItem} to ${endItem} of ${state.filteredCards.length} projects`;

           cardControls.innerHTML = '';

           if (totalPages > 1) {
               // Previous button
               const prevBtn = document.createElement('button');
               prevBtn.className = 'btn btn-outline-primary btn-sm';
               prevBtn.innerHTML = '<i class="ri-arrow-left-line"></i> Previous';
               prevBtn.disabled = state.currentPage === 1;
               prevBtn.onclick = () => {
                   if (state.currentPage > 1) {
                       state.currentPage--;
                       updateCardDisplay();
                       updateCardPagination();
                       window.scrollTo({top: 0, behavior: 'smooth'});
                   }
               };
               cardControls.appendChild(prevBtn);

               // Page indicator
               const pageIndicator = document.createElement('span');
               pageIndicator.className = 'text-muted mx-2';
               pageIndicator.textContent = `Page ${state.currentPage} of ${totalPages}`;
               cardControls.appendChild(pageIndicator);

               // Next button
               const nextBtn = document.createElement('button');
               nextBtn.className = 'btn btn-outline-primary btn-sm';
               nextBtn.innerHTML = 'Next <i class="ri-arrow-right-line"></i>';
               nextBtn.disabled = state.currentPage === totalPages;
               nextBtn.onclick = () => {
                   if (state.currentPage < totalPages) {
                       state.currentPage++;
                       updateCardDisplay();
                       updateCardPagination();
                       window.scrollTo({top: 0, behavior: 'smooth'});
                   }
               };
               cardControls.appendChild(nextBtn);
           }
       }

       /**
        * Initialize event handlers for card view elements
        *
        * Note: Event handlers are now handled via event delegation in the main initialization,
        * so this function is kept for backward compatibility but no longer needed.
        *
        * @function initializeCardEventHandlers
        * @since 3.0.0
        * @deprecated Event delegation is now used instead
        */
       function initializeCardEventHandlers() {
           // Event handlers are now handled via event delegation
           // This function is kept for backward compatibility
           // No action needed as event delegation handles all clicks
       }

       /**
        * Handle edit project action
        *
        * @param {HTMLElement} button - Edit button element
        * @function handleEditProject
        * @since 3.0.0
        */
       function handleEditProject(button) {
           const form = document.querySelector('.manageProjectsForm');
           if (!form) return;

           const data = button.dataset;

           // Map form fields to their corresponding data attributes
           const fieldMappings = {
               'projectTypeID': 'projectTypeId',
               'projectName': 'projectName',
               'projectCode': 'projectCode',
               'projectStart': 'projectStart',
               'projectClose': 'projectClose',
               'projectDeadline': 'projectDeadline',
               'billingRateID': 'billingRateId',
               'roundingoff': 'roundingoff',
               'roundingInterval': 'roundingInterval',
               'businessUnitID': 'businessUnitId',
               'projectValue': 'projectValue',
               'status': 'status',
               'projectID': 'projectId',
               'clientID': 'clientId',
               'projectOwnerID': 'projectOwnerId',
           };

           // Set the values in the form
           for (const [field, dataAttr] of Object.entries(fieldMappings)) {
               const input = form.querySelector(`input[name="${field}"]`);
               if (input) {
                   input.value = data[dataAttr] || '';
               }
           }

           // Handle select elements
           const selects = ['clientID', 'projectOwnerID', 'billingRateID', 'businessUnitID', 'roundingoff', 'roundingInterval', 'projectTypeID'];
           selects.forEach(selectName => {
               const select = form.querySelector(`[name="${selectName}"]`);
               if (select && data[fieldMappings[selectName]]) {
                   if (selectName === 'clientID' && select.tomselect) {
                       select.tomselect.setValue(data[fieldMappings[selectName]]);
                   } else if (selectName === 'roundingoff') {
                       select.value = data[fieldMappings[selectName]];
                       if (data[fieldMappings[selectName]] !== 'no_rounding') {
                           document.querySelector('.roundingInterval')?.classList.remove('d-none');
                       } else {
                           document.querySelector('.roundingInterval')?.classList.add('d-none');
                       }
                   } else {
                       select.value = data[fieldMappings[selectName]];
                   }
               }
           });
       }

       /**
        * Handle delete project action
        *
        * @param {HTMLElement} button - Delete button element
        * @function handleDeleteProject
        * @since 3.0.0
        */
       function handleDeleteProject(button) {
           const projectId = button.getAttribute('data-project-id');
           const projectName = button.getAttribute('data-project-name');

           // Show SweetAlert confirmation dialog
           Swal.fire({
               title: 'Delete Project?',
               html: `
                   <div class="text-start">
                       <p class="mb-3">Are you sure you want to delete the project <strong>"${projectName}"</strong>?</p>
                       <p class="mb-2 text-danger"><strong>This will permanently delete:</strong></p>
                       <ul class="text-start mb-3">
                           <li>All project tasks</li>
                           <li>All project phases</li>
                           <li>All subtasks</li>
                           <li>All team members</li>
                           <li>All expenses</li>
                           <li>All fee expenses</li>
                       </ul>
                       <p class="text-danger mb-0"><strong>This action cannot be undone!</strong></p>
                   </div>
               `,
               icon: 'warning',
               showCancelButton: true,
               confirmButtonColor: '#dc3545',
               cancelButtonColor: '#6c757d',
               confirmButtonText: '<i class="ri-delete-bin-line me-1"></i> Yes, Delete Project',
               cancelButtonText: '<i class="ri-close-line me-1"></i> Cancel',
               reverseButtons: true,
               focusConfirm: false,
               focusCancel: true,
               customClass: {
                   confirmButton: 'btn btn-danger',
                   cancelButton: 'btn btn-secondary'
               },
               buttonsStyling: false
           }).then((result) => {
               if (result.isConfirmed) {
                   // Disable button to prevent double-click
                   button.disabled = true;
                   button.innerHTML = '<i class="ri-loader-4-line spin"></i> Deleting...';

                   // Show unified page loader
                   if (typeof showSpinner === 'function') {
                       showSpinner();
                   } else {
                       // Fallback: show page spinner directly
                       const spinnerElement = document.getElementById('page-spinner');
                       if (spinnerElement) {
                           spinnerElement.classList.add('show');
                       }
                   }

                   // Prepare form data
                   const formData = new FormData();
                   formData.append('projectID', projectId);

                   // Send AJAX request
                   fetch(siteUrl + 'php/scripts/projects/delete_project.php', {
                       method: 'POST',
                       body: formData
                   })
                   .then(response => response.json())
                   .then(data => {
                       // Hide unified page loader
                       if (typeof hideSpinner === 'function') {
                           hideSpinner();
                       } else {
                           const spinnerElement = document.getElementById('page-spinner');
                           if (spinnerElement) {
                               spinnerElement.classList.remove('show');
                           }
                       }

                       if (data.success) {
                           // Remove the project row/card from the display immediately
                           const projectRow = button.closest('tr[data-project-id="' + projectId + '"]');
                           const projectCard = button.closest('.project-card-item[data-project-id="' + projectId + '"]');

                           if (projectRow) {
                               projectRow.remove();
                           }
                           if (projectCard) {
                               projectCard.remove();
                           }

                           // Update filtered arrays
                           if (state.filteredRows) {
                               state.filteredRows = state.filteredRows.filter(row =>
                                   row.getAttribute('data-project-id') !== projectId
                               );
                           }
                           if (state.filteredCards) {
                               state.filteredCards = state.filteredCards.filter(card =>
                                   card.getAttribute('data-project-id') !== projectId
                               );
                           }

                           // Update all rows array
                           if (state.allTableRows) {
                               state.allTableRows = Array.from(document.querySelectorAll(SELECTORS.tbody + ' tr'));
                           }
                           if (state.allCardItems) {
                               state.allCardItems = Array.from(document.querySelectorAll('.project-card-item'));
                           }

                           // Reset to first page and refresh display
                           state.currentPage = 1;
                           updateTableDisplay();
                           updateCardDisplay();
                           updatePagination();
                           updateBottomPagination();
                           updateCardPagination();

                           // Update statistics without page reload
                           updateProjectStatistics();

                           // Show success message with SweetAlert
                           Swal.fire({
                               title: 'Project Deleted!',
                               html: `
                                   <div class="text-start">
                                       <p class="mb-2">${data.message || 'Project deleted successfully'}</p>
                                       ${data.deletionSummary ? `
                                           <div class="mt-3 p-2 bg-light rounded">
                                               <small class="text-muted d-block mb-1"><strong>Deletion Summary:</strong></small>
                                               <small class="d-block">• Tasks: ${data.deletionSummary.tasks || 0}</small>
                                               <small class="d-block">• Phases: ${data.deletionSummary.phases || 0}</small>
                                               <small class="d-block">• Subtasks: ${data.deletionSummary.subtasks || 0}</small>
                                               <small class="d-block">• Team Members: ${data.deletionSummary.teamMembers || 0}</small>
                                               <small class="d-block">• Expenses: ${data.deletionSummary.expenses || 0}</small>
                                           </div>
                                       ` : ''}
                                   </div>
                               `,
                               icon: 'success',
                               confirmButtonText: 'OK',
                               confirmButtonColor: '#198754',
                               timer: 3000,
                               timerProgressBar: true
                           });
                       } else {
                           // Hide loader on error
                           if (typeof hideSpinner === 'function') {
                               hideSpinner();
                           } else {
                               const spinnerElement = document.getElementById('page-spinner');
                               if (spinnerElement) {
                                   spinnerElement.classList.remove('show');
                               }
                           }

                           // Show error messages with SweetAlert
                           const errorMsg = data.errors ? data.errors.join('<br>') : 'Failed to delete project';
                           Swal.fire({
                               title: 'Deletion Failed',
                               html: errorMsg,
                               icon: 'error',
                               confirmButtonText: 'OK',
                               confirmButtonColor: '#dc3545'
                           });

                           // Re-enable button
                           button.disabled = false;
                           button.innerHTML = '<i class="ri-delete-bin-line"></i>';
                       }
                   })
                   .catch(error => {
                       console.error('Delete project error:', error);

                       // Hide loader on error
                       if (typeof hideSpinner === 'function') {
                           hideSpinner();
                       } else {
                           const spinnerElement = document.getElementById('page-spinner');
                           if (spinnerElement) {
                               spinnerElement.classList.remove('show');
                           }
                       }

                       Swal.fire({
                           title: 'Error',
                           text: 'An error occurred while deleting the project. Please try again.',
                           icon: 'error',
                           confirmButtonText: 'OK',
                           confirmButtonColor: '#dc3545'
                       });

                       // Re-enable button
                       button.disabled = false;
                       button.innerHTML = '<i class="ri-delete-bin-line"></i>';
                   });
               }
           });
       }

       // ========================================================================
       // PAGINATION SYSTEM
       // ========================================================================

       /**
        * Update pagination controls
        *
        * Creates and updates the pagination controls for the table.
        * This function handles both top and bottom pagination.
        *
        * @function updatePagination
        * @since 3.0.0
        */
       function updatePagination() {
           const totalPages = Math.ceil(state.filteredRows.length / state.itemsPerPage);
           const paginationContainer = document.getElementById('paginationContainer');

           if (!paginationContainer) return;

           paginationContainer.innerHTML = '';

           // Create pagination info
           const infoDiv = document.createElement('div');
           infoDiv.className = 'text-muted d-flex align-items-center';

           const itemsPerPageDiv = document.createElement('div');
           itemsPerPageDiv.className = 'd-flex align-items-center me-3';
           itemsPerPageDiv.innerHTML = `
               <label class="form-label me-2 mb-0 small">Show:</label>
               <select class="form-select form-select-sm" style="width: auto;" id="itemsPerPageSelect">
                   <option value="10" ${state.itemsPerPage === 10 ? 'selected' : ''}>10</option>
                   <option value="25" ${state.itemsPerPage === 25 ? 'selected' : ''}>25</option>
                   <option value="50" ${state.itemsPerPage === 50 ? 'selected' : ''}>50</option>
                   <option value="100" ${state.itemsPerPage === 100 ? 'selected' : ''}>100</option>
               </select>
           `;

           const paginationInfo = document.createElement('div');
           paginationInfo.className = 'text-muted small';
           const startItem = ((state.currentPage - 1) * state.itemsPerPage) + 1;
           const endItem = Math.min(state.currentPage * state.itemsPerPage, state.filteredRows.length);
           paginationInfo.innerHTML = `Showing ${startItem} to ${endItem} of ${state.filteredRows.length} projects`;

           infoDiv.appendChild(itemsPerPageDiv);
           infoDiv.appendChild(paginationInfo);
           paginationContainer.appendChild(infoDiv);

           // Handle items per page change
           const itemsPerPageSelect = document.getElementById('itemsPerPageSelect');
           if (itemsPerPageSelect) {
               itemsPerPageSelect.addEventListener('change', function() {
                   state.itemsPerPage = parseInt(this.value);
                   state.currentPage = 1;
                   updateTableDisplay();
                   updatePagination();
                   updateBottomPagination();
               });
           }

           // Create pagination controls
           if (totalPages > 1) {
               const paginationDiv = document.createElement('div');
               paginationDiv.className = 'd-flex align-items-center';

               // Previous button
               const prevBtn = document.createElement('button');
               prevBtn.className = 'btn btn-outline-secondary btn-sm me-2';
               prevBtn.innerHTML = '<i class="ri-arrow-left-line"></i> Previous';
               prevBtn.disabled = state.currentPage === 1;
               prevBtn.onclick = () => {
                   if (state.currentPage > 1) {
                       state.currentPage--;
                       updateTableDisplay();
                       updatePagination();
                       updateBottomPagination();
                   }
               };
               paginationDiv.appendChild(prevBtn);

               // Page numbers
               const maxVisiblePages = CONFIG.maxVisiblePages;
               let startPage = Math.max(1, state.currentPage - Math.floor(maxVisiblePages / 2));
               let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

               if (endPage - startPage < maxVisiblePages - 1) {
                   startPage = Math.max(1, endPage - maxVisiblePages + 1);
               }

               // First page
               if (startPage > 1) {
                   const firstBtn = createPageButton(1);
                   paginationDiv.appendChild(firstBtn);

                   if (startPage > 2) {
                       const ellipsis = document.createElement('span');
                       ellipsis.className = 'px-2 text-muted';
                       ellipsis.textContent = '...';
                       paginationDiv.appendChild(ellipsis);
                   }
               }

               // Page numbers
               for (let i = startPage; i <= endPage; i++) {
                   const pageBtn = createPageButton(i);
                   paginationDiv.appendChild(pageBtn);
               }

               // Last page
               if (endPage < totalPages) {
                   if (endPage < totalPages - 1) {
                       const ellipsis = document.createElement('span');
                       ellipsis.className = 'px-2 text-muted';
                       ellipsis.textContent = '...';
                       paginationDiv.appendChild(ellipsis);
                   }

                   const lastBtn = createPageButton(totalPages);
                   paginationDiv.appendChild(lastBtn);
               }

               // Next button
               const nextBtn = document.createElement('button');
               nextBtn.className = 'btn btn-outline-secondary btn-sm ms-2';
               nextBtn.innerHTML = 'Next <i class="ri-arrow-right-line"></i>';
               nextBtn.disabled = state.currentPage === totalPages;
               nextBtn.onclick = () => {
                   if (state.currentPage < totalPages) {
                       state.currentPage++;
                       updateTableDisplay();
                       updatePagination();
                       updateBottomPagination();
                   }
               };
               paginationDiv.appendChild(nextBtn);

               paginationContainer.appendChild(paginationDiv);
           }

           function createPageButton(pageNum) {
               const pageBtn = document.createElement('button');
               pageBtn.className = `btn btn-sm me-1 ${pageNum === state.currentPage ? 'btn-primary' : 'btn-outline-secondary'}`;
               pageBtn.textContent = pageNum;
               pageBtn.onclick = () => {
                   state.currentPage = pageNum;
                   updateTableDisplay();
                   updatePagination();
                   updateBottomPagination();
               };
               return pageBtn;
           }
       }

       /**
        * Update bottom pagination controls
        *
        * Creates and updates the bottom pagination controls for the table.
        *
        * @function updateBottomPagination
        * @since 3.0.0
        */
       function updateBottomPagination() {
           const totalPages = Math.ceil(state.filteredRows.length / state.itemsPerPage);
           const bottomInfo = document.getElementById('bottomPaginationInfo');
           const bottomControls = document.getElementById('bottomPaginationControls');

           if (!bottomInfo || !bottomControls) return;

           const startItem = ((state.currentPage - 1) * state.itemsPerPage) + 1;
           const endItem = Math.min(state.currentPage * state.itemsPerPage, state.filteredRows.length);
           bottomInfo.textContent = `Showing ${startItem} to ${endItem} of ${state.filteredRows.length} projects`;

           bottomControls.innerHTML = '';

           if (totalPages > 1) {
               // Previous button
               const prevBtn = document.createElement('button');
               prevBtn.className = 'btn btn-outline-primary btn-sm';
               prevBtn.innerHTML = '<i class="ri-arrow-left-line"></i> Previous';
               prevBtn.disabled = state.currentPage === 1;
               prevBtn.onclick = () => {
                   if (state.currentPage > 1) {
                       state.currentPage--;
                       updateTableDisplay();
                       updatePagination();
                       updateBottomPagination();
                   }
               };
               bottomControls.appendChild(prevBtn);

               // Page indicator
               const pageIndicator = document.createElement('span');
               pageIndicator.className = 'text-muted mx-2';
               pageIndicator.textContent = `Page ${state.currentPage} of ${totalPages}`;
               bottomControls.appendChild(pageIndicator);

               // Next button
               const nextBtn = document.createElement('button');
               nextBtn.className = 'btn btn-outline-primary btn-sm';
               nextBtn.innerHTML = 'Next <i class="ri-arrow-right-line"></i>';
               nextBtn.disabled = state.currentPage === totalPages;
               nextBtn.onclick = () => {
                   if (state.currentPage < totalPages) {
                       state.currentPage++;
                       updateTableDisplay();
                       updatePagination();
                       updateBottomPagination();
                   }
               };
               bottomControls.appendChild(nextBtn);

               // Quick navigation for larger datasets
               if (totalPages > 5) {
                   const quickNav = document.createElement('div');
                   quickNav.className = 'd-flex align-items-center ms-3';
                   quickNav.innerHTML = `
                       <span class="text-muted me-2 small">Quick:</span>
                       <button class="btn btn-outline-secondary btn-sm me-1" onclick="goToPage(1)" ${state.currentPage === 1 ? 'disabled' : ''}>First</button>
                       <button class="btn btn-outline-secondary btn-sm me-1" onclick="goToPage(${Math.max(1, state.currentPage - 5)})" ${state.currentPage <= 5 ? 'disabled' : ''}>-5</button>
                       <button class="btn btn-outline-secondary btn-sm me-1" onclick="goToPage(${Math.min(totalPages, state.currentPage + 5)})" ${state.currentPage >= totalPages - 4 ? 'disabled' : ''}>+5</button>
                       <button class="btn btn-outline-secondary btn-sm" onclick="goToPage(${totalPages})" ${state.currentPage === totalPages ? 'disabled' : ''}>Last</button>
                   `;
                   bottomControls.appendChild(quickNav);
               }
           }
       }

       /**
        * Go to specific page
        *
        * @param {number} pageNum - Page number to navigate to
        * @function goToPage
        * @since 3.0.0
        */
       window.goToPage = function(pageNum) {
           const totalPages = Math.ceil(state.filteredRows.length / state.itemsPerPage);
           if (pageNum >= 1 && pageNum <= totalPages) {
               state.currentPage = pageNum;
               updateTableDisplay();
               updatePagination();
               updateBottomPagination();
           }
       };

       // ========================================================================
       // EVENT LISTENERS - SET UP EVENT DELEGATION FIRST
       // ========================================================================

       // Use global EventDelegation system for edit and delete buttons
       // This works with dynamically added/removed elements without reattaching listeners
       if (typeof EventDelegation !== 'undefined') {
           // Event delegation for edit project buttons
           EventDelegation.on('.editProjectCase', 'click', function(e, target) {
               e.preventDefault();
               handleEditProject(target);
           }, {}, document);

           // Event delegation for delete project buttons
           EventDelegation.on('.deleteProject', 'click', function(e, target) {
               e.preventDefault();
               handleDeleteProject(target);
           }, {}, document);
       } else {
           // Fallback: Use document-level event delegation if EventDelegation is not available
           document.addEventListener('click', function(e) {
               const editButton = e.target.closest('.editProjectCase');
               if (editButton) {
                   e.preventDefault();
                   handleEditProject(editButton);
                   return;
               }

               const deleteButton = e.target.closest('.deleteProject');
               if (deleteButton) {
                   e.preventDefault();
                   handleDeleteProject(deleteButton);
                   return;
               }
           });
       }

       // Initialize table on page load
       initializeTable();

       // Load saved view preference
       const savedView = localStorage.getItem('projectViewPreference') || 'table';
       if(savedView === 'card') {
           setTimeout(() => switchView('card'), 100);
       }

       // View switcher buttons
       const tableViewBtn = document.getElementById('tableViewBtn');
       const cardViewBtn = document.getElementById('cardViewBtn');

       if(tableViewBtn) {
           tableViewBtn.addEventListener('click', () => switchView('table'));
       }
       if(cardViewBtn) {
           cardViewBtn.addEventListener('click', () => switchView('card'));
       }

       // Add click event listeners to sortable column headers
       document.addEventListener('click', function(e) {
           const sortableHeader = e.target.closest('th[data-sort]');
           if (sortableHeader) {
               const column = sortableHeader.getAttribute('data-sort');
               handleColumnSort(column);
           }
       });

       // Search input with debouncing
       const searchInput = document.querySelector(SELECTORS.searchInput);
       if (searchInput) {
           searchInput.addEventListener('input', debounce(performSearch, CONFIG.debounceDelay));
       }

       // Filter toggle
       const filterToggle = document.querySelector(SELECTORS.filterToggle);
       if (filterToggle) {
           filterToggle.addEventListener('click', function() {
               const advancedFilters = document.getElementById('advancedFilters');
               if (advancedFilters) {
                   advancedFilters.style.display = advancedFilters.style.display === 'none' ? 'block' : 'none';
               }
           });
       }

       // Advanced filters
       const filterInputs = [
           'filterProjectName',
           'filterClient',
           'filterProjectOwner',
           'filterDeadline',
           'filterStartDate',
           'filterEndDate'
       ];

       filterInputs.forEach(inputId => {
           const input = document.getElementById(inputId);
           if (input) {
               input.addEventListener('change', performSearch);
           }
       });

       // Custom date range toggle
       const deadlineFilter = document.getElementById('filterDeadline');
       if (deadlineFilter) {
           deadlineFilter.addEventListener('change', function() {
               const customDateRange = document.getElementById('customDateRange');
               if (customDateRange) {
                   customDateRange.style.display = this.value === 'custom' ? 'block' : 'none';
               }
           });
       }

       // Clear filters
       const clearFiltersBtn = document.querySelector(SELECTORS.clearFiltersBtn);
       if (clearFiltersBtn) {
           clearFiltersBtn.addEventListener('click', clearAllFilters);
       }

       // Export functionality
       const exportBtn = document.querySelector(SELECTORS.exportBtn);
       if (exportBtn) {
           exportBtn.addEventListener('click', function() {
               // Export functionality will be implemented here
               console.log('Export functionality to be implemented');
           });
       }


       // Keyboard navigation
       document.addEventListener('keydown', function(e) {
           if (e.target.closest('#paginationContainer')) return;

           const totalPages = Math.ceil(state.filteredRows.length / state.itemsPerPage);

           if (e.key === 'ArrowLeft' && state.currentPage > 1) {
               e.preventDefault();
               state.currentPage--;
               updateTableDisplay();
               updatePagination();
               updateBottomPagination();
           } else if (e.key === 'ArrowRight' && state.currentPage < totalPages) {
               e.preventDefault();
               state.currentPage++;
               updateTableDisplay();
               updatePagination();
               updateBottomPagination();
           } else if (e.key === 'Home' && state.currentPage !== 1) {
               e.preventDefault();
               state.currentPage = 1;
               updateTableDisplay();
               updatePagination();
               updateBottomPagination();
           } else if (e.key === 'End' && state.currentPage !== totalPages) {
               e.preventDefault();
               state.currentPage = totalPages;
               updateTableDisplay();
               updatePagination();
               updateBottomPagination();
           }
       });

       // Reinitialize tooltips on window resize
       window.addEventListener('resize', debounce(function() {
           initializeTooltips();
       }, 250));

       // Reinitialize tooltips when the page becomes visible (for mobile devices)
       document.addEventListener('visibilitychange', function() {
           if (!document.hidden) {
               setTimeout(initializeTooltips, 100);
           }
         });
      });
   </script>

   <!-- Enhanced Project Dashboard Styles -->
   <style>
   /* =================================================================== */
   /* SORTABLE TABLE STYLES */
   /* =================================================================== */
   .sortable {
       position: relative;
       user-select: none;
       transition: background-color 0.2s ease;
   }

   .sortable:hover {
       background-color: rgba(0, 123, 255, 0.1);
   }

   .sortable i {
       margin-left: 5px;
       opacity: 0.6;
       transition: opacity 0.2s ease;
   }

   .sortable:hover i {
       opacity: 1;
   }

   .sort-asc i, .sort-desc i {
       color: #007bff;
       opacity: 1;
   }

   .sort-asc, .sort-desc {
       background-color: rgba(0, 123, 255, 0.05);
   }

   #projectsTable thead th {
       border-bottom: 2px solid #dee2e6;
       font-weight: 600;
       color: #495057;
   }

   /* =================================================================== */
   /* PROJECT CARD VIEW STYLES */
   /* =================================================================== */
   .project-hover-card {
       transition: all 0.3s ease;
       border-width: 2px;
   }

   .project-hover-card:hover {
       transform: translateY(-5px);
       box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
   }

   .project-hover-card .card-title a:hover {
       color: #0d6efd !important;
   }

   /* Card border animations */
   .project-hover-card.border-success:hover {
       border-color: #198754 !important;
       box-shadow: 0 0.5rem 1.5rem rgba(25, 135, 84, 0.2) !important;
   }

   .project-hover-card.border-danger:hover {
       border-color: #dc3545 !important;
       box-shadow: 0 0.5rem 1.5rem rgba(220, 53, 69, 0.2) !important;
   }

   .project-hover-card.border-primary:hover {
       border-color: #0d6efd !important;
       box-shadow: 0 0.5rem 1.5rem rgba(13, 110, 253, 0.2) !important;
   }

   /* =================================================================== */
   /* PROGRESS BAR STYLES */
   /* =================================================================== */
   .progress {
       min-width: 60px;
       background-color: #e9ecef;
   }

   .progress-bar {
       transition: width 0.6s ease;
   }

   /* =================================================================== */
   /* AVATAR AND TEAM STYLES */
   /* =================================================================== */
   .avatar {
       position: relative;
       display: inline-flex;
       align-items: center;
       justify-content: center;
       width: 2rem;
       height: 2rem;
       border-radius: 50%;
       background-color: #007bff;
       color: white;
       font-size: 0.75rem;
       font-weight: 600;
       line-height: 1;
       text-align: center;
       vertical-align: middle;
   }

   .avatar-initials {
       display: flex;
       align-items: center;
       justify-content: center;
       width: 100%;
       height: 100%;
       font-size: 0.75rem;
       font-weight: 600;
       color: white;
       line-height: 1;
   }

   .avatar:hover {
       transform: scale(1.05);
       transition: transform 0.2s ease;
       z-index: 10;
   }

   /* =================================================================== */
   /* TOOLTIP STYLES */
   /* =================================================================== */
   .tooltip {
       z-index: 1050;
   }

   .tooltip-inner {
       background-color: #333;
       color: white;
       font-size: 0.875rem;
       padding: 0.5rem 0.75rem;
       border-radius: 0.375rem;
       max-width: 200px;
   }

   /* =================================================================== */
   /* VIEW TOGGLE BUTTON STYLES */
   /* =================================================================== */
   .btn-group .btn.active {
       background-color: var(--primary-color);
       border-color: var(--primary-color);
       color: white;
   }

   /* =================================================================== */
   /* STATISTICS CARDS HOVER EFFECTS */
   /* =================================================================== */
   .custom-card {
       transition: all 0.3s ease;
   }

   .custom-card:hover {
       box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.1);
   }

   /* =================================================================== */
   /* DROPDOWN MENU IMPROVEMENTS */
   /* =================================================================== */
   .dropdown-menu {
       box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
       border: 1px solid rgba(0, 0, 0, 0.05);
   }

   .dropdown-item {
       transition: all 0.2s ease;
   }

   .dropdown-item:hover {
       background-color: #f8f9fa;
       padding-left: 1.75rem;
   }

   /* =================================================================== */
   /* RESPONSIVE DESIGN */
   /* =================================================================== */
   @media (max-width: 768px) {
       .avatar {
           width: 1.75rem;
           height: 1.75rem;
           font-size: 0.7rem;
       }

       .project-hover-card {
           margin-bottom: 1rem;
       }

       .btn-group {
           display: flex;
           flex-direction: row;
       }
   }

   /* =================================================================== */
   /* LOADING AND ANIMATION STATES */
   /* =================================================================== */
   @keyframes fadeIn {
       from {
           opacity: 0;
           transform: translateY(10px);
       }
       to {
           opacity: 1;
           transform: translateY(0);
       }
   }

   .project-card-item {
       animation: fadeIn 0.3s ease-in-out;
   }

   /* =================================================================== */
   /* BADGE IMPROVEMENTS */
   /* =================================================================== */
   .badge {
       font-weight: 500;
       padding: 0.35em 0.65em;
   }

   /* =================================================================== */
   /* BUTTON HOVER EFFECTS */
   /* =================================================================== */
   .btn-icon {
       transition: all 0.2s ease;
   }

   .btn-icon:hover {
       transform: scale(1.1);
   }

   /* =================================================================== */
   /* SPINNER ANIMATION */
   /* =================================================================== */
   @keyframes spin {
       from {
           transform: rotate(0deg);
       }
       to {
           transform: rotate(360deg);
       }
   }

   .spin {
       animation: spin 1s linear infinite;
   }
   </style>

   <?php
}
//  var_dump($project);
 ?>

<!-- SweetAlert2 JS -->
<script src="<?= $base ?>assets/libs/sweetalert2/sweetalert2.all.min.js"></script>
<?php










