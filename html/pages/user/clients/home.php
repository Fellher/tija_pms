<?php
/**
 * Client Management Dashboard - Refactored Version
 *
 * This page provides a comprehensive, optimized client management system with:
 * - Enhanced user experience and responsive design
 * - Advanced data filtering and export capabilities
 * - Modular structure for easy maintenance and updates
 * - Integration with existing configuration system
 * - Best practices implementation
 *
 * Features:
 * - Client listing with advanced search and filtering
 * - Active projects and sales cases tracking with KPIs
 * - Client management modals (add/edit/upload)
 * - In-house client identification and management
 * - Export functionality (Excel, PDF, Print)
 * - Real-time data updates and responsive design
 *
 * @author TIJA PMS System
 * @version 2.0
 * @since 2024
 */

// Security check: Ensure user is logged in and has valid access
if(!$isValidUser) {
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}

// ============================================================================
// CONFIGURATION SECTION
// ============================================================================

// Extend existing configuration with client-specific settings
$config['client'] = [
    // Display Configuration
    'currency' => 'KES',
    'timeFormat' => 'HH:MM:SS',
    'dateFormat' => 'Y-m-d',
    'itemsPerPage' => 25,
    'enableSearch' => true,
    'enableExport' => true,
    'enableBulkOperations' => false, // Currently disabled
    'enableAdvancedFiltering' => true,

    // Table Configuration
    'tableResponsive' => true,
    'tableStriped' => true,
    'tableHover' => true,
    'showRowNumbers' => false,

    // Modal Configuration
    'modalSize' => 'modal-md',
    'modalCentered' => true,

    // KPI Configuration
    'showKPIs' => true,
    'kpiRefreshInterval' => 30000, // 30 seconds

    // Export Configuration
    'exportFormats' => ['excel', 'pdf', 'print'],
    'exportFileName' => 'clients_export_' . date('Y-m-d_H-i-s'),

    // Performance Configuration
    'enableLazyLoading' => true,
    'cacheTimeout' => 300, // 5 minutes
    'maxRecordsPerPage' => 100
];

// Permission Configuration
$permissions = [
    'canAddClients' => $isValidUser ?? false,
    'canEditClients' => $isValidUser ?? false,
    'canDeleteClients' => $isAdmin ?? false,
    'canViewSalaries' => $isValidAdmin ?? false,
    'canBulkUpload' => $isAdmin ?? false,
    'canExportData' => $isValidUser ?? false
];

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Generate client status badge HTML
 *
 * @param object $client - Client object
 * @return string - HTML badge element
 */
function generateClientStatusBadge($client) {
    $status = $client->inHouse ?? 'N';

    switch($status) {
        case 'Y':
            return '<span class="badge rounded-pill bg-danger-transparent">In House</span>';
        case 'N':
        default:
            return '<span class="badge rounded-pill bg-success-transparent">External</span>';
    }
}

/**
 * Calculate client activity metrics
 *
 * @param object $client - Client object
 * @param array $orgDataID - Organization data ID
 * @param array $entityID - Entity ID
 * @param object $DBConn - Database connection
 * @return array - Activity metrics
 */
function calculateClientActivity($client, $orgDataID, $entityID, $DBConn) {
    // Count open sales cases
    $openSalesCases = Sales::sales_cases(array(
        'clientID' => $client->clientID,
        'orgDataID' => $orgDataID,
        'entityID' => $entityID,
        'closeStatus' => 'open'
    ), false, $DBConn);
    $openCasesSales = $openSalesCases ? count($openSalesCases) : 0;

    // Count open projects
    $openProjects = Projects::projects_full(array(
        'clientID' => $client->clientID,
        'orgDataID' => $orgDataID,
        'entityID' => $entityID,
        'projectStatus' => 'open'
    ), false, $DBConn);
    $openCasesProjects = $openProjects ? count($openProjects) : 0;

    $totalOpenCases = $openCasesSales + $openCasesProjects;

    return [
        'sales' => $openCasesSales,
        'projects' => $openCasesProjects,
        'total' => $totalOpenCases,
        'breakdown' => $totalOpenCases > 0 ? "{$totalOpenCases} ({$openCasesSales} Sales, {$openCasesProjects} Projects)" : "0"
    ];
}

/**
 * Generate client profile link
 *
 * @param object $client - Client object
 * @param string $base - Base URL
 * @param array $params - URL parameters
 * @return string - Complete client profile URL
 */
function generateClientProfileLink($client, $base, $params) {
    return "{$base}html/?s={$params['s']}&ss={$params['ss']}&p=client_details&client_id={$client->clientID}";
}

/**
 * Format client address for display
 *
 * @param object $addressDetails - Address details object
 * @param object $country - Country object
 * @return string - Formatted address
 */
function formatClientAddress($addressDetails, $country) {
    if (!$addressDetails) {
        return "Not Set";
    }

    $address = Utility::clean_string($addressDetails->address);
    $city = $addressDetails->city;
    $countryName = isset($country->countryName) ? $country->countryName : "";

    return trim("{$address} {$city} {$countryName}");
}

// ============================================================================
// DATA INITIALIZATION AND USER CONTEXT SETUP
// ============================================================================

// Get employee ID from URL parameter or current user session
$employeeID = (isset($_GET['uid']) && !empty($_GET['uid'])) ?
    Utility::clean_string($_GET['uid']) : $userDetails->ID;
$employeeDetails = Employee::employees(array('ID' => $employeeID), true, $DBConn);

// Load organizational data for dropdowns and context
$allEmployees = Employee::employees([], false, $DBConn);
$employeesCategorised = Employee::categorise_employee($allEmployees, 'jobTitle');
$allOrgs = Admin::organisation_data_mini([], false, $DBConn);
$allEntities = Data::entities([], false, $DBConn);

// ============================================================================
// ORGANIZATION AND ENTITY CONTEXT RESOLUTION
// ============================================================================

/**
 * Resolve organization data ID with fallback hierarchy:
 * 1. URL parameter (orgDataID)
 * 2. Employee's organization
 * 3. Session organization
 * 4. Empty string as final fallback
 */
$orgDataID = isset($_GET['orgDataID']) ?
    Utility::clean_string($_GET['orgDataID']) :
    (isset($employeeDetails->orgDataID) && !empty($employeeDetails->orgDataID) ?
        $employeeDetails->orgDataID :
        ((isset($_SESSION['orgDataID']) && !empty($_SESSION['orgDataID'])) ?
            $_SESSION['orgDataID'] : ""));

/**
 * Resolve entity ID with fallback hierarchy:
 * 1. URL parameter (entityID)
 * 2. Employee's entity
 * 3. Session entity
 * 4. Empty string as final fallback
 */
$entityID = isset($_GET['entityID']) ?
    Utility::clean_string($_GET['entityID']) :
    (isset($employeeDetails->entityID) && !empty($employeeDetails->entityID) ?
        $employeeDetails->entityID :
        (isset($_SESSION['entityID']) ? $_SESSION['entityID'] : ''));

// Ensure orgDataID is not null
$orgDataID = $orgDataID ? $orgDataID : "";

// ============================================================================
// CLIENT DATA RETRIEVAL AND PROCESSING
// ============================================================================

// Build query string for navigation and data filtering
$getString .= "&orgDataID={$orgDataID}&entityID={$entityID}";

// Retrieve all clients for the current organization and entity
$clients = Client::clients_full(array(
    'orgDataID' => $orgDataID,
    'entityID' => $entityID
), false, $DBConn);


// var_dump($clients);
// ============================================================================
// SALES CASES AND PROJECT ANALYSIS
// ============================================================================

// Retrieve sales cases for the current organization and entity
$salesCases = Sales::sales_cases(array(
    'orgDataID' => $orgDataID,
    'entityID' => $entityID
), false, $DBConn);

// Initialize arrays for tracking client metrics
$uniqueClientsArray = array();
$newClients = array();
$clientActiveProjects = array();

/**
 * Identify clients with sales cases closing in the next 30 days
 */
if($salesCases) {
    foreach ($salesCases as $case) {
        if($case->expectedCloseDate >= date('Y-m-d') &&
           $case->expectedCloseDate <= date('Y-m-d', strtotime('+30 days'))) {
            $uniqueClientsArray[] = $case->clientID;
        }
    }
}

/**
 * Process clients and gather activity data
 */
if($clients && is_array($clients)) {
    foreach ($clients as $client) {
        // Get clients with active projects
        $projects = Projects::projects_full(array(
            'clientID' => $client->clientID,
            'orgDataID' => $orgDataID,
            'entityID' => $entityID,
            'projectStatus' => 'open'
        ), false, $DBConn);

        if($projects) {
            foreach ($projects as $project) {
                if($project->projectStatus == 'open') {
                    $clientActiveProjects[] = $project;
                }
            }
        }

        // Track newly added clients (within last 30 days)
        if($client->DateAdded >= date('Y-m-d', strtotime('-30 days'))) {
            $newClients[] = $client->clientID;
        }
    }
}

// ============================================================================
// PROSPECT ANALYSIS AND SALES PIPELINE
// ============================================================================

// Remove duplicate client IDs from upcoming sales cases
$uniqueClientsArray = array_unique($uniqueClientsArray);

$prospects = array();
$allSales = Sales::sales_case_full(array(
    'orgDataID' => $orgDataID,
    'entityID' => $entityID
), false, $DBConn);

if($allSales) {
    foreach ($allSales as $sale) {
        if($sale->expectedCloseDate >= date('Y-m-d') &&
           $sale->expectedCloseDate <= date('Y-m-d', strtotime('+30 days'))) {
            $prospects[] = $sale;
        }
    }

    // Filter prospects with high probability (50% or higher)
    if($prospects) {
        $prospects = array_filter($prospects, function($sale) {
            return (int)$sale->levelPercentage >= 50;
        });
    }
}

// ============================================================================
// MODAL DIALOGS SETUP
// ============================================================================

// Modal for individual client management (add/edit)
echo Utility::form_modal_header(
    "manage_client_modal",
    "clients/manage_clients.php",
    "Manage Clients",
    array($config['client']['modalSize'], 'modal-dialog-centered'),
    $base
);
   include "includes/scripts/clients/modals/manage_client_modal.php";
echo Utility::form_modal_footer('Save Client', 'saveClient', 'btn btn-success btn-sm', true);
// var_dump($clients[0]);
// Modal for bulk client upload functionality (if enabled)
if ($permissions['canBulkUpload']) {
    echo Utility::form_modal_header(
        "manage_client_upload_modal",
        "clients/manage_client_upload.php",
        "Upload Clients",
        array($config['client']['modalSize'], 'modal-dialog-centered'),
        $base
    );
    include "includes/scripts/clients/modals/manage_client_upload_modal.php";
    echo Utility::form_modal_footer('Upload Clients', 'uploadClients', 'btn btn-success btn-sm', true);
}
// var_dump($clients[0]);
// Include organization/entity validation modal
include "includes/scripts/check_org_entity.php";

?>

<!-- ============================================================================
     PAGE HEADER AND NAVIGATION
     ============================================================================ -->

<!-- Page Header with Title and Action Buttons -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h1 class="page-title fw-medium fs-24 mb-1">Customer Dashboard</h1>
            <button type="button"
                    class="btn btn-sm btn-link text-primary p-0"
                    data-bs-toggle="modal"
                    data-bs-target="#clientDocumentationModal"
                    title="View client management documentation">
                <i class="ri-information-line fs-20"></i>
            </button>
        </div>
        <p class="text-muted mb-0">Manage and track all client relationships and activities</p>
    </div>
    <div class="ms-md-1 ms-0">
        <!-- Primary Action: Add New Client -->
        <?php if ($permissions['canAddClients']): ?>
        <button type="button"
                class="btn btn-primary-light shadow btn-sm px-4 me-3"
                data-bs-toggle="modal"
                data-bs-target="#manage_client_modal"
                title="Add new client to the system">
            <i class="ri-add-line"></i>
            Add Client
        </button>
        <?php endif; ?>

        <!-- Secondary Action: Bulk Upload (if enabled) -->
        <?php if ($permissions['canBulkUpload']): ?>
        <button type="button"
                class="btn btn-secondary-light shadow btn-sm px-4 me-3"
                data-bs-toggle="modal"
                data-bs-target="#manage_client_upload_modal"
                title="Upload multiple clients from file">
            <i class="ri-upload-line"></i>
            Upload Clients
        </button>
        <?php endif; ?>

        <!-- Export Button (if enabled) -->
        <?php if ($permissions['canExportData']): ?>
        <button type="button"
                class="btn btn-success-light shadow btn-sm px-4"
                id="exportClientsBtn"
                title="Export client data">
            <i class="ri-download-line"></i>
            Export
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Include KPI Dashboard for Client Metrics -->
<?php if ($config['client']['showKPIs']):
      // var_dump($clients);
   include "includes/scripts/clients/clients_header_kpis.php"; ?>
<?php endif; ?>

<!-- ============================================================================
     CLIENT DATA TABLE
     ============================================================================ -->

<!-- Main Client Listing Table -->
<div class="card card-body">
    <!-- Table Header with Search and Filter Options -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center">
            <h5 class="mb-0 me-3">Client Directory</h5>
            <span class="badge bg-primary-transparent">
                <?= is_array($clients) ? count($clients) : 0 ?> Total Clients
            </span>
        </div>

        <!-- Search and Filter Controls -->
        <div class="d-flex align-items-center">
            <?php if ($config['client']['enableSearch']): ?>
            <div class="input-group me-3" style="width: 300px;">
                <span class="input-group-text">
                    <i class="ri-search-line"></i>
                </span>
                <input type="text"
                       class="form-control"
                       id="clientSearchInput"
                       placeholder="Search clients...">
            </div>
            <?php endif; ?>

            <?php if ($config['client']['enableAdvancedFiltering']): ?>
            <button type="button"
                    class="btn btn-outline-secondary btn-sm me-2"
                    id="filterToggleBtn"
                    title="Advanced filters">
                <i class="ri-filter-line"></i>
                Filter
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Advanced Filter Panel (Collapsible) -->
    <?php if ($config['client']['enableAdvancedFiltering']):
        // var_dump($allEmployees);
        ?>
    <div class="collapse mb-3" id="advancedFilters">
        <div class="card card-body bg-light">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Client Type</label>
                    <select class="form-select form-select-sm" id="clientTypeFilter">
                        <option value="">All Types</option>
                        <option value="Y">In House</option>
                        <option value="N">External</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Account Owner</label>
                    <select class="form-select form-select-sm" id="accountOwnerFilter">
                        <option value="">All Owners</option>
                        <?php if ($allEmployees): ?>
                            <?php foreach ($allEmployees as $employee): ?>
                                <option value="<?= $employee->ID ?>">
                                    <?= htmlspecialchars($employee->employeeName) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Activity Level</label>
                    <select class="form-select form-select-sm" id="activityLevelFilter">
                        <option value="">All Levels</option>
                        <option value="high">High Activity</option>
                        <option value="medium">Medium Activity</option>
                        <option value="low">Low Activity</option>
                        <option value="none">No Activity</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date Added</label>
                    <select class="form-select form-select-sm" id="dateAddedFilter">
                        <option value="">All Time</option>
                        <option value="last7">Last 7 Days</option>
                        <option value="last30">Last 30 Days</option>
                        <option value="last90">Last 90 Days</option>
                        <option value="thisYear">This Year</option>
                    </select>
                </div>
            </div>
            <div class="mt-3">
                <button type="button" class="btn btn-primary btn-sm" id="applyFiltersBtn">
                    <i class="ri-check-line"></i> Apply Filters
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm ms-2" id="clearFiltersBtn">
                    <i class="ri-refresh-line"></i> Clear
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Data Table -->
    <div class="table-responsive">
        <table id="clientsDataTable"
               class="table table-hover <?= $config['client']['tableStriped'] ? 'table-striped' : '' ?> <?= $config['client']['tableHover'] ? 'table-hover' : '' ?> table-borderless table-vcenter text-nowrap table-xs mb-0">
            <thead class="table-light">
                <tr>
                    <th class="fw-semibold">
                        <i class="ri-user-line me-1"></i>Client Name
                    </th>
                    <th class="fw-semibold">
                        <i class="ri-map-pin-line me-1"></i>Address
                    </th>
                    <th class="fw-semibold">
                        <i class="ri-building-line me-1"></i>Location
                    </th>
                    <th class="fw-semibold">
                        <i class="ri-user-settings-line me-1"></i>Account Owner
                    </th>
                    <th class="fw-semibold text-center">
                        <i class="ri-bar-chart-line me-1"></i>Open Cases
                    </th>
                    <th class="fw-semibold">
                        <i class="ri-calendar-line me-1"></i>Date Added
                    </th>
                    <th class="fw-semibold text-center">
                        <i class="ri-settings-3-line me-1"></i>Actions
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php
                if($clients && is_array($clients)) {
                    foreach ($clients as $client) {
                        // Determine styling for in-house clients
                        $inhouse = ($client->inHouse == 'Y') ? "table-danger bg-light-blue" : "";

                        // Get headquarters address details
                        $addressDetails = Client::client_address(array(
                            'clientID' => $client->clientID,
                            'headquarters' => 'Y'
                        ), true, $DBConn);

                        // Get country information
                        $country = $addressDetails ? Data::countries(array(
                            'countryID' => $addressDetails->countryID
                        ), true, $DBConn) : "";

                        // Calculate client activity
                        $activity = calculateClientActivity($client, $orgDataID, $entityID, $DBConn);

                        // Generate client profile link
                        $profileLink = generateClientProfileLink($client, $base, array(
                            's' => $s,
                            'ss' => $ss
                        ));

                        // Format address
                        $formattedAddress = formatClientAddress($addressDetails, $country);

                        // Format date added
                        $dateAdded = $client->DateAdded ? date('M d, Y', strtotime($client->DateAdded)) : 'N/A';

                        // Get account owner name
                        $accountOwner = $client->accountOwnerID ?
                            Core::user_name($client->accountOwnerID, $DBConn) : 'Unassigned';
                        ?>

                        <tr class="<?= $inhouse ?>" data-client-id="<?= $client->clientID ?>">
                            <!-- Client Name Column -->
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <div class="avatar avatar-sm bg-primary-transparent rounded-circle">
                                            <i class="ri-user-line"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <a class="text-primary fw-medium"
                                        href="<?= $profileLink ?>"
                                        title="View client details">
                                            <?= htmlspecialchars($client->clientName) ?>
                                        </a>
                                        <div class="mt-1">
                                            <?= generateClientStatusBadge($client) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Address Column -->
                            <td>
                                <span class="text-muted" title="<?= htmlspecialchars($formattedAddress) ?>">
                                    <?= htmlspecialchars(substr($formattedAddress, 0, 50)) ?>
                                    <?= strlen($formattedAddress) > 50 ? '...' : '' ?>
                                </span>
                            </td>

                            <!-- Location Column -->
                            <td>
                                <?php if ($addressDetails): ?>
                                <div class="d-flex align-items-center">
                                    <i class="ri-map-pin-line text-muted me-1"></i>
                                    <span class="text-muted">
                                        <?= htmlspecialchars($addressDetails->city) ?>
                                        <?= isset($country->countryName) ? '/ ' . htmlspecialchars($country->countryName) : '' ?>
                                    </span>
                                </div>
                                <?php else: ?>
                                <span class="text-muted">Not specified</span>
                                <?php endif; ?>
                            </td>

                            <!-- Account Owner Column -->
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-xs bg-secondary-transparent rounded-circle me-2">
                                        <i class="ri-user-line"></i>
                                    </div>
                                    <span class="text-muted">
                                        <?= htmlspecialchars($accountOwner) ?>
                                    </span>
                                </div>
                            </td>

                            <!-- Open Cases Column -->
                            <td class="text-center">
                                <?php if ($activity['total'] > 0): ?>
                                <span class="badge bg-warning-transparent"
                                    title="<?= htmlspecialchars($activity['breakdown']) ?>">
                                    <?= $activity['total'] ?>
                                </span>
                                <small class="text-muted d-block">
                                    <?= $activity['sales'] ?>S, <?= $activity['projects'] ?>P
                                </small>
                                <?php else: ?>
                                <span class="badge bg-secondary-transparent">0</span>
                                <?php endif; ?>
                            </td>

                            <!-- Date Added Column -->
                            <td>
                                <span class="text-muted"><?= $dateAdded ?></span>
                            </td>

                            <!-- Actions Column -->
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <?php if ($permissions['canEditClients']): ?>
                                    <button type="button"
                                            class="btn btn-primary-light btn-sm editclientButton"
                                            data-bs-toggle="modal"
                                            data-bs-target="#manage_client_modal"
                                            data-clientid="<?= htmlspecialchars($client->clientID) ?>"
                                            data-orgdataid="<?= htmlspecialchars($orgDataID) ?>"
                                            data-entityid="<?= htmlspecialchars($entityID) ?>"
                                            data-clientname="<?= htmlspecialchars($client->clientName) ?>"
                                            data-clientcode="<?= htmlspecialchars($client->clientCode) ?>"
                                            data-vatnumber="<?= htmlspecialchars($client->vatNumber) ?>"
                                            data-accountownerid="<?= htmlspecialchars($client->accountOwnerID) ?>"
                                            title="Edit client information">
                                        <i class="ri-pencil-line fs-12"></i>
                                    </button>
                                    <?php endif; ?>

                                    <a href="<?= $profileLink ?>"
                                    class="btn btn-info-light btn-sm"
                                    title="View client details">
                                        <i class="ri-eye-line fs-12"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <?php
                    }
                } else {
                    ?>
                    <!-- Empty State -->
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted">
                                <i class="ri-user-line fs-48 mb-3"></i>
                                <h5>No Clients Found</h5>
                                <p>Start by adding your first client to the system.</p>
                                <?php if ($permissions['canAddClients']): ?>
                                <button type="button"
                                        class="btn btn-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#manage_client_modal">
                                    <i class="ri-add-line"></i> Add First Client
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Bottom Pagination Links -->
    <div class="d-flex justify-content-between align-items-center mt-3 px-3 py-2 bg-light border-top" id="bottomPaginationContainer">
        <div class="text-muted small">
            <span id="bottomPaginationInfo">Loading...</span>
        </div>
        <div class="d-flex align-items-center gap-2" id="bottomPaginationControls">
            <!-- Pagination controls will be inserted here by JavaScript -->
        </div>
    </div>
</div>

<!-- ============================================================================
     JAVASCRIPT FUNCTIONALITY
     ============================================================================ -->

<script>
/**
 * Client Management JavaScript Functions
 * Handles advanced table functionality, modal interactions, and user experience enhancements
 */
document.addEventListener('DOMContentLoaded', function() {

    // ============================================================================
    // CUSTOM TABLE FUNCTIONALITY (DataTable Alternative)
    // ============================================================================

    // Store all table rows for filtering (convert NodeList to Array)
    const allTableRows = Array.from(document.querySelectorAll('#clientsDataTable tbody tr'));
    let filteredRows = [...allTableRows];
    let currentPage = 1;
    let itemsPerPage = <?= $config['client']['itemsPerPage'] ?>;

    // Initialize table display
    function initializeTable() {
        updateTableDisplay();
        updatePagination();
    }

    // Update table display based on current filters and pagination
    function updateTableDisplay() {
        const tbody = document.querySelector('#clientsDataTable tbody');
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const pageRows = filteredRows.slice(startIndex, endIndex);

        // Clear current display
        tbody.innerHTML = '';

        // Add filtered rows
        pageRows.forEach(row => {
            tbody.appendChild(row.cloneNode(true));
        });

        // Update info display
        updateTableInfo();
    }

    // Update pagination controls
    function updatePagination() {
        const totalPages = Math.ceil(filteredRows.length / itemsPerPage);
        const paginationContainer = document.getElementById('paginationContainer');

        if (!paginationContainer) {
            // Create pagination container if it doesn't exist
            const tableContainer = document.querySelector('.table-responsive');
            const paginationDiv = document.createElement('div');
            paginationDiv.id = 'paginationContainer';
            paginationDiv.className = 'd-flex justify-content-between align-items-center mt-3 px-3';
            tableContainer.parentNode.insertBefore(paginationDiv, tableContainer.nextSibling);
        }

        const container = document.getElementById('paginationContainer');
        container.innerHTML = '';

        // Always show pagination info, even if only one page
        const infoDiv = document.createElement('div');
        infoDiv.className = 'text-muted d-flex align-items-center';

        // Items per page selector
        const itemsPerPageDiv = document.createElement('div');
        itemsPerPageDiv.className = 'd-flex align-items-center me-3';
        itemsPerPageDiv.innerHTML = `
            <label class="form-label me-2 mb-0 small">Show:</label>
            <select class="form-select form-select-sm" style="width: auto;" id="itemsPerPageSelect">
                <option value="10" ${itemsPerPage === 10 ? 'selected' : ''}>10</option>
                <option value="25" ${itemsPerPage === 25 ? 'selected' : ''}>25</option>
                <option value="50" ${itemsPerPage === 50 ? 'selected' : ''}>50</option>
                <option value="100" ${itemsPerPage === 100 ? 'selected' : ''}>100</option>
            </select>
        `;

        // Pagination info
        const paginationInfo = document.createElement('div');
        paginationInfo.className = 'text-muted small';
        const startItem = ((currentPage - 1) * itemsPerPage) + 1;
        const endItem = Math.min(currentPage * itemsPerPage, filteredRows.length);
        paginationInfo.innerHTML = `Showing ${startItem} to ${endItem} of ${filteredRows.length} clients`;

        infoDiv.appendChild(itemsPerPageDiv);
        infoDiv.appendChild(paginationInfo);
        container.appendChild(infoDiv);

        // Handle items per page change
        const itemsPerPageSelect = document.getElementById('itemsPerPageSelect');
        if (itemsPerPageSelect) {
            itemsPerPageSelect.addEventListener('change', function() {
                itemsPerPage = parseInt(this.value);
                currentPage = 1; // Reset to first page
                updateTableDisplay();
                updatePagination();
                updateBottomPagination();
            });
        }

        // Add keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.target.closest('#paginationContainer')) return; // Don't interfere with form inputs

            if (e.key === 'ArrowLeft' && currentPage > 1) {
                e.preventDefault();
                currentPage--;
                updateTableDisplay();
                updatePagination();
                updateBottomPagination();
            } else if (e.key === 'ArrowRight' && currentPage < totalPages) {
                e.preventDefault();
                currentPage++;
                updateTableDisplay();
                updatePagination();
                updateBottomPagination();
            } else if (e.key === 'Home' && currentPage !== 1) {
                e.preventDefault();
                currentPage = 1;
                updateTableDisplay();
                updatePagination();
                updateBottomPagination();
            } else if (e.key === 'End' && currentPage !== totalPages) {
                e.preventDefault();
                currentPage = totalPages;
                updateTableDisplay();
                updatePagination();
                updateBottomPagination();
            }
        });

        if (totalPages > 1) {
            // Pagination buttons container
            const paginationDiv = document.createElement('div');
            paginationDiv.className = 'd-flex align-items-center';

            // Previous button
            const prevBtn = document.createElement('button');
            prevBtn.className = 'btn btn-outline-secondary btn-sm me-2';
            prevBtn.innerHTML = '<i class="ri-arrow-left-line"></i> Previous';
            prevBtn.disabled = currentPage === 1;
            prevBtn.onclick = () => {
                if (currentPage > 1) {
                    currentPage--;
                    updateTableDisplay();
                    updatePagination();
                    updateBottomPagination();
                }
            };
            paginationDiv.appendChild(prevBtn);

            // Page numbers with smart ellipsis
            const maxVisiblePages = 7;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

            // Adjust start page if we're near the end
            if (endPage - startPage < maxVisiblePages - 1) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }

            // First page and ellipsis
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

            // Last page and ellipsis
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
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.onclick = () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    updateTableDisplay();
                    updatePagination();
                    updateBottomPagination();
                }
            };
            paginationDiv.appendChild(nextBtn);

            // Jump to page input (for large datasets)
            if (totalPages > 10) {
                const jumpDiv = document.createElement('div');
                jumpDiv.className = 'd-flex align-items-center ms-3';
                jumpDiv.innerHTML = `
                    <label class="form-label me-2 mb-0 small">Go to:</label>
                    <input type="number" class="form-control form-control-sm"
                           id="jumpToPage" min="1" max="${totalPages}"
                           value="${currentPage}" style="width: 60px;">
                `;

                const jumpInput = jumpDiv.querySelector('#jumpToPage');
                jumpInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        const targetPage = parseInt(this.value);
                        if (targetPage >= 1 && targetPage <= totalPages) {
                            currentPage = targetPage;
                            updateTableDisplay();
                            updatePagination();
                        } else {
                            this.value = currentPage; // Reset to current page if invalid
                        }
                    }
                });

                jumpInput.addEventListener('blur', function() {
                    const targetPage = parseInt(this.value);
                    if (targetPage >= 1 && targetPage <= totalPages) {
                        currentPage = targetPage;
                        updateTableDisplay();
                        updatePagination();
                    } else {
                        this.value = currentPage; // Reset to current page if invalid
                    }
                });

                paginationDiv.appendChild(jumpDiv);
            }

            container.appendChild(paginationDiv);
        }

        // Helper function to create page buttons
        function createPageButton(pageNum) {
            const pageBtn = document.createElement('button');
            pageBtn.className = `btn btn-sm me-1 ${pageNum === currentPage ? 'btn-primary' : 'btn-outline-secondary'}`;
            pageBtn.textContent = pageNum;
            pageBtn.onclick = () => {
                currentPage = pageNum;
                updateTableDisplay();
                updatePagination();
                updateBottomPagination();
            };
            return pageBtn;
        }
    }

    // Update table info
    function updateTableInfo() {
        const infoElement = document.querySelector('.badge.bg-primary-transparent');
        if (infoElement) {
            infoElement.textContent = `${filteredRows.length} Total Clients`;
        }
    }

    // Update bottom pagination controls
    function updateBottomPagination() {
        const totalPages = Math.ceil(filteredRows.length / itemsPerPage);
        const bottomInfo = document.getElementById('bottomPaginationInfo');
        const bottomControls = document.getElementById('bottomPaginationControls');

        if (!bottomInfo || !bottomControls) return;

        // Update pagination info
        const startItem = ((currentPage - 1) * itemsPerPage) + 1;
        const endItem = Math.min(currentPage * itemsPerPage, filteredRows.length);
        bottomInfo.textContent = `Showing ${startItem} to ${endItem} of ${filteredRows.length} clients`;

        // Clear existing controls
        bottomControls.innerHTML = '';

        if (totalPages > 1) {
            // Previous button
            const prevBtn = document.createElement('button');
            prevBtn.className = 'btn btn-outline-primary btn-sm';
            prevBtn.innerHTML = '<i class="ri-arrow-left-line"></i> Previous';
            prevBtn.disabled = currentPage === 1;
            prevBtn.onclick = () => {
                if (currentPage > 1) {
                    currentPage--;
                    updateTableDisplay();
                    updatePagination();
                    updateBottomPagination();
                }
            };
            bottomControls.appendChild(prevBtn);

            // Page indicator
            const pageIndicator = document.createElement('span');
            pageIndicator.className = 'text-muted mx-2';
            pageIndicator.textContent = `Page ${currentPage} of ${totalPages}`;
            bottomControls.appendChild(pageIndicator);

            // Next button
            const nextBtn = document.createElement('button');
            nextBtn.className = 'btn btn-outline-primary btn-sm';
            nextBtn.innerHTML = 'Next <i class="ri-arrow-right-line"></i>';
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.onclick = () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    updateTableDisplay();
                    updatePagination();
                    updateBottomPagination();
                }
            };
            bottomControls.appendChild(nextBtn);

            // Quick page navigation for large datasets
            if (totalPages > 5) {
                const quickNav = document.createElement('div');
                quickNav.className = 'd-flex align-items-center ms-3';
                quickNav.innerHTML = `
                    <span class="text-muted me-2 small">Quick:</span>
                    <button class="btn btn-outline-secondary btn-sm me-1" onclick="goToPage(1)" ${currentPage === 1 ? 'disabled' : ''}>First</button>
                    <button class="btn btn-outline-secondary btn-sm me-1" onclick="goToPage(${Math.max(1, currentPage - 5)})" ${currentPage <= 5 ? 'disabled' : ''}>-5</button>
                    <button class="btn btn-outline-secondary btn-sm me-1" onclick="goToPage(${Math.min(totalPages, currentPage + 5)})" ${currentPage >= totalPages - 4 ? 'disabled' : ''}>+5</button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="goToPage(${totalPages})" ${currentPage === totalPages ? 'disabled' : ''}>Last</button>
                `;
                bottomControls.appendChild(quickNav);
            }
        }
    }

    // Helper function for quick navigation
    function goToPage(pageNum) {
        const totalPages = Math.ceil(filteredRows.length / itemsPerPage);
        if (pageNum >= 1 && pageNum <= totalPages) {
            currentPage = pageNum;
            updateTableDisplay();
            updatePagination();
            updateBottomPagination();
        }
    }

    // Make goToPage globally accessible
    window.goToPage = goToPage;

        // Initialize the table
        initializeTable();

        // Update bottom pagination
        updateBottomPagination();

    // ============================================================================
    // SEARCH FUNCTIONALITY
    // ============================================================================

    <?php if ($config['client']['enableSearch']): ?>
    // Enhanced search functionality with debounce (500ms delay after user stops typing)
    const searchInput = document.getElementById('clientSearchInput');
    let searchTimeout = null;

    // Debounce function to delay search execution
    function debounceSearch(callback, delay = 500) {
        return function(...args) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                callback.apply(this, args);
            }, delay);
        };
    }

    // Search function that filters the table
    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase().trim();

        // Filter rows based on search term
        filteredRows = allTableRows.filter(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length < 4) return false;

            // Get text content from each relevant cell
            const clientName = cells[0].textContent.toLowerCase();
            const address = cells[1].textContent.toLowerCase();
            const location = cells[2].textContent.toLowerCase();
            const accountOwner = cells[3].textContent.toLowerCase();

            // Check if search term matches any of these fields
            return clientName.includes(searchTerm) ||
                   accountOwner.includes(searchTerm) ||
                   address.includes(searchTerm) ||
                   location.includes(searchTerm);
        });

        // Reset to first page and update display
        currentPage = 1;
        updateTableDisplay();
        updatePagination();
        updateBottomPagination();
    }

    // Debounced search function
    const debouncedSearch = debounceSearch(performSearch, 500);

    if (searchInput) {
        // Use input event for better compatibility and debounce the search
        searchInput.addEventListener('input', debouncedSearch);

        // Also handle Enter key for immediate search
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimeout); // Cancel pending debounced search
                performSearch(); // Execute search immediately
            }
        });

        // Add search suggestions/autocomplete functionality
        searchInput.addEventListener('focus', function() {
            this.placeholder = 'Search by client name, account owner, address, or location...';
        });

        searchInput.addEventListener('blur', function() {
            this.placeholder = 'Search clients...';
        });
    }
    <?php endif; ?>

    // ============================================================================
    // ADVANCED FILTERING
    // ============================================================================

    <?php if ($config['client']['enableAdvancedFiltering']): ?>
    // Toggle filter panel
    const filterToggleBtn = document.getElementById('filterToggleBtn');
    const advancedFilters = document.getElementById('advancedFilters');

    if (filterToggleBtn && advancedFilters) {
        filterToggleBtn.addEventListener('click', function() {
            const bsCollapse = new bootstrap.Collapse(advancedFilters, {
                toggle: true
            });
        });
    }

    // Apply filters
    const applyFiltersBtn = document.getElementById('applyFiltersBtn');
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', function() {
            applyAdvancedFilters();
        });
    }

    // Auto-apply filters when dropdown values change
    const filterSelects = ['clientTypeFilter', 'accountOwnerFilter', 'activityLevelFilter', 'dateAddedFilter'];
    filterSelects.forEach(filterId => {
        const filterElement = document.getElementById(filterId);
        if (filterElement) {
            filterElement.addEventListener('change', function() {
                applyAdvancedFilters();
            });
        }
    });

    // Clear filters
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');
    if (clearFiltersBtn) {
        clearFiltersBtn.addEventListener('click', function() {
            clearAdvancedFilters();
        });
    }

    function applyAdvancedFilters() {
        const clientType = document.getElementById('clientTypeFilter').value;
        const accountOwner = document.getElementById('accountOwnerFilter').value;
        const activityLevel = document.getElementById('activityLevelFilter').value;
        const dateAdded = document.getElementById('dateAddedFilter').value;

        // Start with all rows
        filteredRows = [...allTableRows];

        // Apply client type filter (In House vs External)
        if (clientType) {
            filteredRows = filteredRows.filter(row => {
                const clientNameCell = row.querySelector('td:first-child');
                const badge = clientNameCell.querySelector('.badge');

                if (clientType === 'Y' && badge && badge.classList.contains('bg-danger-transparent')) {
                    return true; // In House client
                } else if (clientType === 'N' && badge && badge.classList.contains('bg-success-transparent')) {
                    return true; // External client
                }
                return false;
            });
        }

        // Apply account owner filter
        if (accountOwner) {
            const selectedOption = document.getElementById('accountOwnerFilter').selectedOptions[0];
            const selectedOwnerName = selectedOption ? selectedOption.text.trim() : '';

            // remove all spaces commas and any other punctuation from the selectedOwnerName
            console.log(`selectedOwnerName: ${selectedOwnerName}`);
            const cleanedSelectedOwnerName = selectedOwnerName.replace(/\s/g, '');
            console.log(`cleanedSelectedOwnerName: ${cleanedSelectedOwnerName}`);

            filteredRows = filteredRows.filter(row => {
                const accountOwnerCell = row.querySelector('td:nth-child(4)');
                // console.log(accountOwnerCell);
                const ownerText = accountOwnerCell.textContent.trim().replace(/\s/g, '');
                console.log(`ownerText: ${ownerText} and selectedOwnerName: ${cleanedSelectedOwnerName}`);
                // console.log(ownerText.includes(selectedOwnerName));
                return ownerText.includes(cleanedSelectedOwnerName);
            });
        }

        // Apply activity level filter
        if (activityLevel) {
            filteredRows = filteredRows.filter(row => {
                const activityCell = row.querySelector('td:nth-child(5)');
                const badge = activityCell.querySelector('.badge');
                const activityCount = parseInt(badge.textContent) || 0;

                switch(activityLevel) {
                    case 'high':
                        return activityCount >= 5;
                    case 'medium':
                        return activityCount >= 2 && activityCount < 5;
                    case 'low':
                        return activityCount === 1;
                    case 'none':
                        return activityCount === 0;
                    default:
                        return true;
                }
            });
        }

        // Apply date added filter
        if (dateAdded) {
            filteredRows = filteredRows.filter(row => {
                const dateCell = row.querySelector('td:nth-child(6)');
                const dateText = dateCell.textContent.trim();

                if (dateText === 'N/A') return false;

                const cellDate = new Date(dateText);
                const now = new Date();

                switch(dateAdded) {
                    case 'last7':
                        const sevenDaysAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
                        return cellDate >= sevenDaysAgo;
                    case 'last30':
                        const thirtyDaysAgo = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000);
                        return cellDate >= thirtyDaysAgo;
                    case 'last90':
                        const ninetyDaysAgo = new Date(now.getTime() - 90 * 24 * 60 * 60 * 1000);
                        return cellDate >= ninetyDaysAgo;
                    case 'thisYear':
                        const yearStart = new Date(now.getFullYear(), 0, 1);
                        return cellDate >= yearStart;
                    default:
                        return true;
                }
            });
        }

        // Reset to first page and update display
        currentPage = 1;
        updateTableDisplay();
        updatePagination();
        updateBottomPagination();

        // Update filter indicators
        updateFilterIndicators();
    }

    function updateFilterIndicators() {
        const clientType = document.getElementById('clientTypeFilter').value;
        const accountOwner = document.getElementById('accountOwnerFilter').value;
        const activityLevel = document.getElementById('activityLevelFilter').value;
        const dateAdded = document.getElementById('dateAddedFilter').value;

        // Count active filters
        let activeFilters = 0;
        if (clientType) activeFilters++;
        if (accountOwner) activeFilters++;
        if (activityLevel) activeFilters++;
        if (dateAdded) activeFilters++;

        // Update filter button text to show active count
        const filterToggleBtn = document.getElementById('filterToggleBtn');
        if (filterToggleBtn) {
            if (activeFilters > 0) {
                filterToggleBtn.innerHTML = `<i class="ri-filter-line"></i> Filter (${activeFilters})`;
                filterToggleBtn.classList.add('btn-primary');
                filterToggleBtn.classList.remove('btn-outline-secondary');
            } else {
                filterToggleBtn.innerHTML = `<i class="ri-filter-line"></i> Filter`;
                filterToggleBtn.classList.add('btn-outline-secondary');
                filterToggleBtn.classList.remove('btn-primary');
            }
        }
    }

    function clearAdvancedFilters() {
        // Reset all filter selects
        document.getElementById('clientTypeFilter').value = '';
        document.getElementById('accountOwnerFilter').value = '';
        document.getElementById('activityLevelFilter').value = '';
        document.getElementById('dateAddedFilter').value = '';

        // Clear custom search input if it exists
        const searchInput = document.getElementById('clientSearchInput');
        if (searchInput) {
            searchInput.value = '';
        }

        // Reset filtered rows to show all
        filteredRows = Array.from(allTableRows);

        // Reset to first page and update display
        currentPage = 1;
        updateTableDisplay();
        updatePagination();
        updateBottomPagination();

        // Update filter indicators
        updateFilterIndicators();
    }
    <?php endif; ?>

    // ============================================================================
    // EDIT CLIENT MODAL POPULATION
    // ============================================================================

    const editClientButtons = document.querySelectorAll('.editclientButton');
    editClientButtons.forEach(button => {
        button.addEventListener('click', function() {
            const form = document.getElementById('clientForm');
            if (!form) return;

            // Get data attributes from the clicked button
            const data = this.dataset;

            // Field mappings for form population
            const fieldMappings = {
                clientID: 'clientid',
                orgDataID: 'orgdataid',
                entityID: 'entityid',
                clientName: 'clientname',
                clientCode: 'clientcode',
                vatNumber: 'vatnumber',
                accountOwnerID: 'accountownerid'
            };

            // Populate form fields
            for (const [fieldName, dataAttribute] of Object.entries(fieldMappings)) {
                const input = form.querySelector(`[name="${fieldName}"]`);
                if (input) {
                    input.value = data[dataAttribute] || '';
                }
            }

            // Handle tinyMCE editor if present
            const editor = tinymce.get('entityDescription');
            if (editor) {
                setTimeout(() => {
                    editor.setContent(data.entityDescription || '');
                }, 100);
            }

            // Hide address fields if not in current data set
            const addressFields = ['address', 'postalCode', 'city', 'country'];
            addressFields.forEach(field => {
                const input = form.querySelector(`[name="${field}"]`);
                if (input) {
                    input.parentElement.classList.add('d-none');
                }
            });

            // Hide additional fieldset elements
            const fieldset = document.querySelector('.fieldset');
            if (fieldset) {
                fieldset.classList.add('d-none');
            }
        });
    });

    // ============================================================================
    // EXPORT FUNCTIONALITY
    // ============================================================================

    <?php if ($permissions['canExportData']): ?>
    const exportClientsBtn = document.getElementById('exportClientsBtn');
    if (exportClientsBtn) {
        exportClientsBtn.addEventListener('click', function() {
            // Simple export functionality - could be enhanced with a proper export library
            exportToCSV();
        });
    }

    function exportToCSV() {
        const headers = ['Client Name', 'Address', 'Location', 'Account Owner', 'Open Cases', 'Date Added'];
        const csvContent = [
            headers.join(','),
            ...filteredRows.map(row => {
                const cells = row.querySelectorAll('td');
                return [
                    `"${cells[0].textContent.replace(/"/g, '""')}"`,
                    `"${cells[1].textContent.replace(/"/g, '""')}"`,
                    `"${cells[2].textContent.replace(/"/g, '""')}"`,
                    `"${cells[3].textContent.replace(/"/g, '""')}"`,
                    `"${cells[4].textContent.replace(/"/g, '""')}"`,
                    `"${cells[5].textContent.replace(/"/g, '""')}"`
                ].join(',');
            })
        ].join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', '<?= $config['client']['exportFileName'] ?>.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    <?php endif; ?>

    // ============================================================================
    // PERFORMANCE MONITORING
    // ============================================================================

    // Monitor table performance
    let renderStart = performance.now();

    // Override updateTableDisplay to include performance monitoring
    const originalUpdateTableDisplay = updateTableDisplay;
    updateTableDisplay = function() {
        renderStart = performance.now();
        originalUpdateTableDisplay();
        const renderEnd = performance.now();
        const renderTime = renderEnd - renderStart;

        if (renderTime > 1000) { // If rendering takes more than 1 second
            console.warn(`Table rendering took ${renderTime.toFixed(2)}ms - consider optimizing data or pagination`);
        }
    };

    // ============================================================================
    // ACCESSIBILITY ENHANCEMENTS
    // ============================================================================

    // Add keyboard navigation support
    document.addEventListener('keydown', function(e) {
        // Escape key to close modals
        if (e.key === 'Escape') {
            const openModals = document.querySelectorAll('.modal.show');
            openModals.forEach(modal => {
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                }
            });
        }
    });

    // Add focus management for better accessibility
    const modalElements = document.querySelectorAll('.modal');
    modalElements.forEach(modal => {
        modal.addEventListener('shown.bs.modal', function() {
            const firstInput = this.querySelector('input, select, textarea');
            if (firstInput) {
                firstInput.focus();
            }
        });
    });

    console.log('Client Management Dashboard initialized successfully');
});
</script>

<!-- ============================================================================
     ADDITIONAL STYLES
     ============================================================================ -->

<style>
/* Custom styles for enhanced user experience */
.avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    font-size: 0.875rem;
    font-weight: 500;
}

.avatar-xs {
    width: 1.5rem;
    height: 1.5rem;
    font-size: 0.75rem;
}

.avatar-sm {
    width: 2rem;
    height: 2rem;
    font-size: 0.875rem;
}

.table-responsive {
    border-radius: 0.5rem;
}

#advancedFilters {
    transition: all 0.3s ease;
}

.btn-group .btn {
    margin-right: 0.25rem;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .d-md-flex {
        flex-direction: column;
    }

    .ms-md-1 {
        margin-left: 0 !important;
        margin-top: 1rem;
    }

    .table-responsive {
        font-size: 0.875rem;
    }

    .btn-group {
        flex-direction: column;
    }

    .btn-group .btn {
        margin-right: 0;
        margin-bottom: 0.25rem;
    }
}

/* Loading states */
.table-loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Animation for smooth transitions */
.table tbody tr {
    transition: background-color 0.2s ease;
}

.table tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

/* Badge styling improvements */
.badge {
    font-size: 0.75rem;
    font-weight: 500;
}

/* Custom scrollbar for table */
.table-responsive::-webkit-scrollbar {
    height: 8px;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Pagination Styles */
#paginationContainer {
    background: #f8f9fa;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-top: 1rem;
    border: 1px solid #e9ecef;
}

#paginationContainer .btn-group .btn {
    border-radius: 0.375rem;
    margin-right: 0.25rem;
}

#paginationContainer .btn-group .btn:last-child {
    margin-right: 0;
}

#paginationContainer .form-select-sm {
    font-size: 0.875rem;
    padding: 0.25rem 0.5rem;
}

#paginationContainer .form-control-sm {
    font-size: 0.875rem;
    padding: 0.25rem 0.5rem;
}

#paginationContainer .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Pagination button hover effects */
#paginationContainer .btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

#paginationContainer .btn:active {
    transform: translateY(0);
}

/* Responsive pagination */
@media (max-width: 768px) {
    #paginationContainer {
        flex-direction: column;
        gap: 1rem;
    }

    #paginationContainer .d-flex {
        justify-content: center;
    }

    #paginationContainer .btn-group {
        flex-wrap: wrap;
        justify-content: center;
    }

    #paginationContainer .btn-group .btn {
        margin: 0.125rem;
    }
}

/* Bottom Pagination Styles */
#bottomPaginationContainer {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

#bottomPaginationContainer:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

#bottomPaginationControls .btn {
    border-radius: 0.375rem;
    transition: all 0.2s ease;
}

#bottomPaginationControls .btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

#bottomPaginationControls .btn:active {
    transform: translateY(0);
}

#bottomPaginationControls .btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Quick navigation buttons */
#bottomPaginationControls .btn-outline-secondary {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

/* Responsive bottom pagination */
@media (max-width: 768px) {
    #bottomPaginationContainer {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }

    #bottomPaginationControls {
        justify-content: center;
        flex-wrap: wrap;
    }

    #bottomPaginationControls .btn {
        margin: 0.125rem;
    }
}
</style>

<!-- ============================================================================
     CLIENT DOCUMENTATION MODAL
     ============================================================================ -->
<div class="modal fade" id="clientDocumentationModal" tabindex="-1" aria-labelledby="clientDocumentationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="clientDocumentationModalLabel">
                    <i class="ri-information-line me-2"></i>
                    Client Management Guide
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Overview Section -->
                <div class="mb-4">
                    <h6 class="text-primary mb-3">
                        <i class="ri-eye-line me-2"></i>
                        Overview
                    </h6>
                    <p class="text-muted">
                        The Customer Dashboard provides a comprehensive view of all clients in your organization.
                        Use this page to manage client information, track relationships, monitor active projects,
                        and analyze client activities across different entities.
                    </p>
                </div>

                <!-- Navigation Section -->
                <div class="mb-4">
                    <h6 class="text-primary mb-3">
                        <i class="ri-compass-3-line me-2"></i>
                        Navigating Through Clients
                    </h6>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0 border-0">
                            <div class="d-flex align-items-start">
                                <span class="badge bg-primary-transparent rounded-pill me-3 mt-1">1</span>
                                <div>
                                    <strong>Search Clients</strong>
                                    <p class="text-muted mb-0 small">
                                        Use the search bar at the top of the client table to quickly find clients by name,
                                        email, or company. The search filters results in real-time as you type.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item px-0 border-0">
                            <div class="d-flex align-items-start">
                                <span class="badge bg-primary-transparent rounded-pill me-3 mt-1">2</span>
                                <div>
                                    <strong>Filter by Organization/Entity</strong>
                                    <p class="text-muted mb-0 small">
                                        Use the advanced filter panel to narrow down clients by organization, entity,
                                        status, or other criteria. Click the "Filter" button to expand filter options.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item px-0 border-0">
                            <div class="d-flex align-items-start">
                                <span class="badge bg-primary-transparent rounded-pill me-3 mt-1">3</span>
                                <div>
                                    <strong>View Client Details</strong>
                                    <p class="text-muted mb-0 small">
                                        Click on any client row or use the action buttons to view detailed information,
                                        including contacts, addresses, documents, and project history.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item px-0 border-0">
                            <div class="d-flex align-items-start">
                                <span class="badge bg-primary-transparent rounded-pill me-3 mt-1">4</span>
                                <div>
                                    <strong>Pagination</strong>
                                    <p class="text-muted mb-0 small">
                                        Navigate through multiple pages of clients using the pagination controls at the
                                        bottom of the table. Adjust items per page if needed.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Managing Clients Section -->
                <div class="mb-4">
                    <h6 class="text-primary mb-3">
                        <i class="ri-user-settings-line me-2"></i>
                        Managing Clients
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-primary-transparent h-100">
                                <div class="card-body">
                                    <h6 class="text-primary">
                                        <i class="ri-add-circle-line me-2"></i>
                                        Adding New Clients
                                    </h6>
                                    <p class="text-muted small mb-0">
                                        Click the "Add Client" button in the header to create a new client record.
                                        Fill in the required information including company name, contact details,
                                        and organizational context.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-success-transparent h-100">
                                <div class="card-body">
                                    <h6 class="text-success">
                                        <i class="ri-edit-line me-2"></i>
                                        Editing Clients
                                    </h6>
                                    <p class="text-muted small mb-0">
                                        Click the edit icon on any client row to modify client information.
                                        You can update contact details, addresses, and organizational assignments.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-info-transparent h-100">
                                <div class="card-body">
                                    <h6 class="text-info">
                                        <i class="ri-upload-cloud-line me-2"></i>
                                        Bulk Upload
                                    </h6>
                                    <p class="text-muted small mb-0">
                                        Use the "Upload Clients" button to import multiple clients from a CSV or Excel file.
                                        Ensure your file follows the required format before uploading.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-warning-transparent h-100">
                                <div class="card-body">
                                    <h6 class="text-warning">
                                        <i class="ri-download-line me-2"></i>
                                        Export Data
                                    </h6>
                                    <p class="text-muted small mb-0">
                                        Export client data to Excel, PDF, or print format using the "Export" button.
                                        Filtered results will be exported based on your current view.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Understanding Client Information Section -->
                <div class="mb-4">
                    <h6 class="text-primary mb-3">
                        <i class="ri-file-list-3-line me-2"></i>
                        Understanding Client Information
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th>Field</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Client Name</strong></td>
                                    <td>The official name of the client organization</td>
                                </tr>
                                <tr>
                                    <td><strong>Status Badge</strong></td>
                                    <td>
                                        <span class="badge bg-danger-transparent">In House</span> - Internal organization
                                        <br>
                                        <span class="badge bg-success-transparent">External</span> - External client
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Active Projects</strong></td>
                                    <td>Number of currently active projects associated with the client</td>
                                </tr>
                                <tr>
                                    <td><strong>Sales Cases</strong></td>
                                    <td>Open sales opportunities and cases linked to the client</td>
                                </tr>
                                <tr>
                                    <td><strong>Entity/Organization</strong></td>
                                    <td>The organizational unit or entity the client belongs to</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Key Features Section -->
                <div class="mb-4">
                    <h6 class="text-primary mb-3">
                        <i class="ri-star-line me-2"></i>
                        Key Features
                    </h6>
                    <div class="row g-2">
                        <div class="col-12">
                            <div class="d-flex align-items-start p-2 bg-light rounded">
                                <i class="ri-dashboard-line text-primary me-2 mt-1"></i>
                                <div>
                                    <strong>KPI Dashboard</strong>
                                    <p class="text-muted small mb-0">
                                        View key performance indicators including total clients, active projects,
                                        and sales metrics at a glance.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-start p-2 bg-light rounded">
                                <i class="ri-search-line text-primary me-2 mt-1"></i>
                                <div>
                                    <strong>Advanced Search & Filter</strong>
                                    <p class="text-muted small mb-0">
                                        Quickly locate clients using multiple search criteria and filter options
                                        to narrow down your view.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-start p-2 bg-light rounded">
                                <i class="ri-table-line text-primary me-2 mt-1"></i>
                                <div>
                                    <strong>Sortable Columns</strong>
                                    <p class="text-muted small mb-0">
                                        Click on column headers to sort clients by name, status, projects, or other criteria.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex align-items-start p-2 bg-light rounded">
                                <i class="ri-eye-line text-primary me-2 mt-1"></i>
                                <div>
                                    <strong>Quick Actions</strong>
                                    <p class="text-muted small mb-0">
                                        Access client details, edit information, view contacts, and manage documents
                                        directly from the client list.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tips Section -->
                <div class="alert alert-info mb-0">
                    <h6 class="alert-heading">
                        <i class="ri-lightbulb-line me-2"></i>
                        Pro Tips
                    </h6>
                    <ul class="mb-0 small">
                        <li>Use keyboard shortcuts: Press <kbd>Ctrl</kbd> + <kbd>F</kbd> to quickly focus on the search box</li>
                        <li>Save frequently used filters for quick access to specific client groups</li>
                        <li>Export filtered views to share client lists with team members</li>
                        <li>Monitor the KPI dashboard to track client growth and engagement metrics</li>
                        <li>Check client status badges to quickly identify internal vs external clients</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
