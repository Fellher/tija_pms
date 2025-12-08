<?php
/**
 * Sales Dashboard - Refactored Version
 * Modern, fast, and intuitive interface for business development and sales management
 *
 * Features:
 * - Real-time dashboard statistics
 * - Interactive sales pipeline visualization
 * - Kanban and list views
 * - Quick actions and filters
 * - AJAX-based data loading
 * - Responsive design
 *
 * @version 2.0
 * @date 2025-10-09
 */

// Security check
if (!$isValidUser) {
    Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

// Get user and organization details
$employeeID = (isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) : $userDetails->ID;
$employeeDetails = Data::users(array('ID' => $employeeID), true, $DBConn);
$orgDataID = isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : $employeeDetails->orgDataID;
$entityID = isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : $employeeDetails->entityID;

// Get reference data (lightweight - for dropdowns only)
$businessUnits = Data::business_units(array('orgDataID' => $orgDataID, 'entityID' => $entityID), false, $DBConn);
$statusLevels = Sales::sales_status_levels(['entityID' => $entityID, 'Suspended' => 'N'], false, $DBConn);

// Load lead sources - try organization-wide first, then entity-specific if available
$leadSources = Sales::lead_sources(['Suspended' => 'N'], false, $DBConn);
if (!$leadSources) {
    // Try with just orgDataID if no global lead sources
    $leadSources = Sales::lead_sources(['orgDataID' => $orgDataID, 'Suspended' => 'N'], false, $DBConn);
}

// Debug: Check if lead sources loaded
// Uncomment to debug: var_dump(['Lead Sources Count' => $leadSources ? count($leadSources) : 0]);

// State management
$state = isset($_GET['state']) ? Utility::clean_string($_GET['state']) : 'opportunities';
$view = isset($_GET['view']) ? Utility::clean_string($_GET['view']) : 'kanban';
$filter = isset($_GET['filter']) ? Utility::clean_string($_GET['filter']) : 'all';

$validStates = ['business_development', 'opportunities', 'order', 'lost', 'clients_directory'];
if (!in_array($state, $validStates)) {
    $state = 'opportunities';
}

// Load clients data if Clients Directory is active
$clients = [];
$openCasesCount = [];
if ($state === 'clients_directory') {
    $clients = Client::clients_full(array('orgDataID' => $orgDataID, 'entityID' => $entityID, 'Suspended' => 'N'), false, $DBConn);

    // Count open cases for each client (only non-suspended cases)
    if ($clients && is_array($clients)) {
        foreach ($clients as $client) {
            $openCases = Sales::sales_cases(array('clientID' => $client->clientID, 'Suspended' => 'N'), false, $DBConn);
            $openCasesCount[$client->clientID] = $openCases ? count($openCases) : 0;
        }
    }
}

$sectors = Data::tija_sectors([], false, $DBConn);
$industries = Data::tija_industry([], false, $DBConn);


// Map display name 'won' to actual stage 'order' for backwards compatibility
if ($state === 'won') {
    $state = 'order';
}

// Build query string for navigation
$getString .= "&orgDataID={$orgDataID}&entityID={$entityID}&state={$state}&view={$view}&filter={$filter}";

// Create a proper base path for JavaScript (web root relative)
// Use the existing $base variable which works for both local and online
$jsBasePath = $base;
?>
<script>
    window.sectors = <?= json_encode($sectors ?: []) ?>;
    window.industries = <?= json_encode($industries ?: []) ?>;
</script>

<!-- Sales Dashboard Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-24 mb-0">
            <i class="ri-line-chart-line text-primary me-2"></i>
            Sales Dashboard
        </h1>
        <p class="text-muted fs-14 mb-0">Manage opportunities, track proposals, and close deals</p>
    </div>
    <div class="ms-md-1 ms-0 d-flex gap-2">
        <?php if ($isValidAdmin || $isAdmin): ?>
            <button type="button" class="btn btn-light btn-sm" id="configBtn">
                <i class="ri-settings-4-line"></i> Configure
            </button>
        <?php endif; ?>

        <?php if ($state === 'business_development'): ?>
            <button type="button" class="btn btn-success btn-sm" id="addBusinessDevBtn" data-bs-toggle="modal" data-bs-target="#addBusinessDevModal">
                <i class="ri-add-line"></i> Add Business Development
            </button>
            <button type="button" class="btn btn-link btn-sm text-primary open-doc-modal" data-modal-id="businessDevelopmentDocModal" title="View Business Development guide">
                <i class="ri-information-line fs-18"></i>
            </button>
        <?php else: ?>
            <button type="button" class="btn btn-primary btn-sm" id="quickAddBtn">
                <i class="ri-add-line"></i> Quick Add
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Dashboard Statistics -->
<div id="dashboardStats" class="mb-4">
    <!-- Loading placeholder -->
    <div class="row g-3">
        <?php for ($i = 0; $i < 4; $i++): ?>
            <div class="col-xl-3 col-lg-6 col-md-6">
                <div class="card custom-card overflow-hidden">
                    <div class="card-body">
                        <div class="placeholder-glow">
                            <span class="placeholder col-6"></span>
                            <span class="placeholder col-8"></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endfor; ?>
    </div>
</div>

<!-- Quick Actions & Filters -->
<div class="card custom-card mb-4">
    <div class="card-body p-3">
        <div class="row align-items-center">
            <div class="col-md-8">
                <!-- Navigation Tabs -->
                <ul class="nav nav-tabs nav-tabs-header mb-0" id="salesTabs" role="tablist">
                    <li class="nav-item position-relative" role="presentation">
                        <a class="nav-link <?= $state === 'business_development' ? 'active' : '' ?>"
                           href="<?= "{$base}html/?s={$s}&ss={$ss}&p={$p}&state=business_development&view={$view}&filter={$filter}" ?>">
                            <i class="ri-seedling-line me-1"></i> Business Development
                        </a>
                        <?php if ($state === 'business_development'): ?>
                        <button type="button"
                                class="btn btn-sm btn-link text-white p-0 open-doc-modal"
                                style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); z-index: 10;"
                                data-modal-id="businessDevelopmentDocModal"
                                title="View Business Development guide">
                            <i class="ri-information-line fs-14"></i>
                        </button>
                        <?php endif; ?>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link <?= $state === 'opportunities' ? 'active' : '' ?>"
                           href="<?= "{$base}html/?s={$s}&ss={$ss}&p={$p}&state=opportunities&view={$view}&filter={$filter}" ?>">
                            <i class="ri-contacts-line me-1"></i> Opportunities
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link <?= $state === 'order' ? 'active' : '' ?>"
                           href="<?= "{$base}html/?s={$s}&ss={$ss}&p={$p}&state=order&view={$view}&filter={$filter}" ?>">
                            <i class="ri-trophy-line me-1"></i> Won
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link <?= $state === 'lost' ? 'active' : '' ?>"
                           href="<?= "{$base}html/?s={$s}&ss={$ss}&p={$p}&state=lost&view={$view}&filter={$filter}" ?>">
                            <i class="ri-archive-line me-1"></i> Lost
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link <?= $state === 'clients_directory' ? 'active' : '' ?>"
                           href="<?= "{$base}html/?s={$s}&ss={$ss}&p={$p}&state=clients_directory" ?>">
                            <i class="ri-building-line me-1"></i> Clients Directory
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-md-4 text-end">
                <?php
                if ($state !== 'clients_directory'): ?>
                    <div class="btn-group btn-group-sm" role="group">
                        <input type="radio" class="btn-check" name="viewType" id="viewKanban" autocomplete="off"
                            <?= $view === 'kanban' ? 'checked' : '' ?>>
                        <label class="btn btn-outline-primary" for="viewKanban" data-view="kanban">
                            <i class="ri-layout-grid-line"></i> Kanban
                        </label>

                        <input type="radio" class="btn-check" name="viewType" id="viewList" autocomplete="off"
                            <?= $view === 'list' ? 'checked' : '' ?>>
                        <label class="btn btn-outline-primary" for="viewList" data-view="list">
                            <i class="ri-list-check"></i> List
                        </label>
                    </div>

                    <div class="btn-group btn-group-sm ms-2" role="group">
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="ri-filter-line"></i>
                            <?= $filter === 'my' ? 'My Sales' : 'All Sales' ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= "{$base}html/?s={$s}&ss={$ss}&p={$p}&state={$state}&view={$view}&filter=all" ?>">All Sales</a></li>
                            <li><a class="dropdown-item" href="<?= "{$base}html/?s={$s}&ss={$ss}&p={$p}&state={$state}&view={$view}&filter=my" ?>">My Sales</a></li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Business Development Inline Guide -->
<?php if ($state === 'business_development'): ?>
<div class="card border-info mb-4 bg-info-transparent">
    <div class="card-body">
        <div class="d-flex align-items-start">
            <div class="flex-shrink-0 me-3">
                <i class="ri-lightbulb-line text-info fs-24"></i>
            </div>
            <div class="flex-grow-1">
                <h6 class="fw-semibold mb-2">
                    <i class="ri-seedling-line me-2"></i>
                    Business Development Quick Guide
                </h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="d-flex align-items-start mb-2">
                            <i class="ri-add-circle-line text-success me-2 mt-1"></i>
                            <div>
                                <strong>Add New Prospect:</strong>
                                <p class="small text-muted mb-0">Click "Add Business Development" to create a new prospect. Fill in prospect name, client, contact person, and business unit.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-start mb-2">
                            <i class="ri-edit-line text-primary me-2 mt-1"></i>
                            <div>
                                <strong>Edit Prospect:</strong>
                                <p class="small text-muted mb-0">Click the edit icon on any prospect card to update information, change ownership, or modify expected revenue.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-start mb-2">
                            <i class="ri-arrow-right-circle-line text-warning me-2 mt-1"></i>
                            <div>
                                <strong>Progress to Opportunity:</strong>
                                <p class="small text-muted mb-0">When a prospect shows interest, click "Progress to Opportunity" to move it to the Opportunities stage with status and probability.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-link text-info p-0 open-doc-modal" data-modal-id="businessDevelopmentDocModal">
                        <i class="ri-information-line me-1"></i> View detailed documentation
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Sales Content Area -->
<?php if ($state === 'clients_directory'): ?>
    <!-- Clients Directory Section -->
    <div id="clientsDirectorySection">
        <div class="card custom-card mb-4">
            <div class="card-body">
                <!-- Search and Filter Controls -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="ri-search-line"></i>
                            </span>
                            <input type="text"
                                   class="form-control"
                                   id="clientSearchInput"
                                   placeholder="Search by client name or account owner...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="clientsPerPageSelect">
                            <option value="30">30 per page</option>
                            <option value="50" selected>50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                    </div>
                    <div class="col-md-3 text-end">
                        <span class="text-muted" id="clientsCountDisplay">Loading...</span>
                    </div>
                </div>

                <!-- Clients Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="clientsDirectoryTable">
                        <thead class="table-light">
                            <tr>
                                <th class="sortable" data-sort="clientName" style="cursor: pointer;">
                                    Client Name
                                    <i class="ri-arrow-up-down-line ms-1"></i>
                                </th>
                                <th class="sortable" data-sort="accountOwner" style="cursor: pointer;">
                                    Account Owner
                                    <i class="ri-arrow-up-down-line ms-1"></i>
                                </th>
                                <th class="sortable text-center" data-sort="openCases" style="cursor: pointer;">
                                    Open Cases
                                    <i class="ri-arrow-up-down-line ms-1"></i>
                                </th>
                                <th>Client Code</th>
                                <th>Date Added</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="clientsTableBody">
                            <!-- Clients will be loaded here via JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3" id="clientsPagination">
                    <div>
                        <span class="text-muted" id="paginationInfo">Showing 0 - 0 of 0</span>
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="paginationControls">
                            <!-- Pagination controls will be generated here -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Store clients data in JavaScript -->
    <script>
        window.clientsData = <?= json_encode($clients ?: []) ?>;
        window.openCasesCount = <?= json_encode($openCasesCount ?: []) ?>;
    </script>
<?php else: ?>
    <div id="salesContent">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2">Loading sales data...</p>
        </div>
    </div>
<?php endif; ?>

<!-- Modals -->
<?php
// Quick Add Modal
// Initialize clientContacts to prevent undefined errors in included files
if (!isset($clientContacts)) {
    $clientContacts = [];
}

// Ensure countries list is available for the Quick Add (new client) form
if (!isset($countries) || !is_array($countries) || empty($countries)) {
    $countries = Data::countries([], false, $DBConn);
}

echo Utility::form_modal_header("quickAddModal", "sales/manage_sale.php", "Quick Add Sale", array('modal-xl', 'modal-dialog-centered'), $base);
include "includes/scripts/sales/modals/manage_sale_enhanced.php";
echo Utility::form_modal_footer('Save', 'saveSale', 'btn btn-primary', true);

// Progress Business Development to Opportunity Modal
$opportunityStatusLevels = Sales::sales_status_levels(['entityID' => $entityID, 'Suspended' => 'N'], false, $DBConn);
// $leadSources already loaded above - no need to reload
$contactTypes = Client::contact_types(['Suspended' => 'N'], false, $DBConn);

echo Utility::form_modal_header("progressBusinessDevModal", "sales/progress_business_development.php", "Progress to Opportunity", array('modal-lg', 'modal-dialog-centered'), $base);
?>
    <div class="d-flex justify-content-end mb-3 px-3 pt-2">
        <button type="button"
                class="btn btn-sm btn-link text-primary p-0"
                data-bs-toggle="modal"
                data-bs-target="#progressBusinessDevDocModal"
                title="View guide for progressing to opportunity"
                onclick="event.preventDefault(); event.stopPropagation();">
            <i class="ri-information-line fs-18 me-1"></i> Need help?
        </button>
    </div>
    <div class="progressBusinessDevForm" id="progressBusinessDevForm">
        <!-- Hidden Fields -->
        <input type="hidden" name="salesCaseID" id="progressSalesCaseID" value="">
        <input type="hidden" name="clientID" id="progressClientID" value="">
        <input type="hidden" name="businessUnitID" id="progressBusinessUnitID" value="">
        <input type="hidden" name="salesPersonID" id="progressSalesPersonID" value="<?= $userDetails->ID ?>">
        <input type="hidden" name="entityID" value="<?= $entityID ?>">
        <input type="hidden" name="orgDataID" value="<?= $orgDataID ?>">
        <input type="hidden" name="newSaleStage" value="opportunities">
        <input type="hidden" name="s" value="<?= $s ?>">
        <input type="hidden" name="ss" value="<?= $ss ?>">

        <!-- Hidden probability field (auto-populated from status level) -->
        <input type="hidden" name="probability" id="progressProbability" value="">

        <!-- Status Level Selection -->
        <div class="row mb-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Select Opportunity Status <span class="text-danger">*</span></label>
            </div>
            <?php
            if ($opportunityStatusLevels) {
                $displayedCount = 0;
                foreach ($opportunityStatusLevels as $opKey => $statusLevel) {
                    // Filter status levels: probability must be between 10% and 50% (inclusive)
                    $probability = isset($statusLevel->levelPercentage) ? (float)$statusLevel->levelPercentage : 0;

                    if ($probability >= 10 && $probability <= 50) {
                        $displayedCount++;
                        ?>
                        <div class="col-sm-12 col-lg-6 mb-2">
                            <input class="btn-check" type="radio" name="saleStatusLevelID"
                                id="progressStatusLevel<?= $opKey ?>"
                                value="<?= $statusLevel->saleStatusLevelID ?>"
                                data-probability="<?= $statusLevel->levelPercentage ?>"
                                <?= $displayedCount === 1 ? 'required' : '' ?>>
                            <label class="form-check-label btn btn-outline-primary w-100 text-start"
                                for="progressStatusLevel<?= $opKey ?>">
                                <h6 class="mb-1"><?= $statusLevel->statusLevel ?></h6>
                                <small class="text-muted"><?= $statusLevel->StatusLevelDescription ?></small>
                                <div class="mt-1">
                                    <span class="badge bg-primary-transparent"><?= $statusLevel->levelPercentage ?>% probability</span>
                                </div>
                            </label>
                        </div>
                        <?php
                    }
                }

                // Show message if no status levels found in the range
                if ($displayedCount === 0) {
                    ?>
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <i class="ri-alert-line me-2"></i>
                            No opportunity status levels found with probability between 10% and 50%.
                            Please contact your administrator to configure appropriate status levels.
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
        </div>

        <hr class="my-3">

        <!-- Progress Notes -->
        <div class="form-group mb-3">
            <label for="progressNotes" class="form-label">Progress Notes</label>
            <textarea class="form-control" name="progressNotes" id="progressNotes"
                    rows="3" placeholder="Enter progress notes..."></textarea>
        </div>

        <div class="row g-3">
            <!-- Expected Close Date - MANDATORY -->
            <div class="col-md-4">
                <label for="progressExpectedCloseDate" class="form-label">
                    Expected Close Date <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" name="expectedCloseDate"
                    id="progressExpectedCloseDate" required
                    placeholder="Select future date">
                <div class="form-text">
                    <i class="ri-information-line"></i> Required when moving to opportunity stage
                </div>
            </div>

            <!-- Sales Case Estimate -->
            <div class="col-md-4">
                <label for="progressSalesCaseEstimate" class="form-label">Estimated Value (KES)</label>
                <div class="input-group">
                    <span class="input-group-text">KES</span>
                    <input type="number" class="form-control" name="salesCaseEstimate"
                        id="progressSalesCaseEstimate" placeholder="0.00" step="0.01" min="0">
                </div>
            </div>

            <!-- Lead Source -->
            <div class="col-md-4">
                <label for="progressLeadSourceID" class="form-label">Lead Source</label>
                <select class="form-select" name="leadSourceID" id="progressLeadSourceID">
                    <option value="">Select Lead Source</option>
                    <?php
                    if ($leadSources) {
                        foreach ($leadSources as $source) {
                            echo "<option value='{$source->leadSourceID}'>{$source->leadSourceName}</option>";
                        }
                    } else {
                        echo "<option value='' disabled>No lead sources available - Contact admin to add</option>";
                    }
                    ?>
                </select>
                <?php if (!$leadSources): ?>
                    <div class="form-text text-warning">
                        <i class="ri-alert-line"></i> No lead sources configured. Contact administrator.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sales Case Contact -->
            <div class="col-md-12">
                <label for="progressSalesCaseContactID" class="form-label">Sales Contact</label>
                <select class="form-select" name="salesCaseContactID" id="progressSalesCaseContactID">
                    <option value="">Select Contact</option>
                    <option value="addNew">+ Add New Contact</option>
                </select>
            </div>
        </div>

        <!-- New Contact Fields (Hidden by default) -->
        <div id="newContactFields" class="card bg-light p-3 mt-3 d-none">
            <h6 class="mb-3">
                <i class="ri-add-circle-line me-2"></i>New Contact Information
                <button type="button" class="btn btn-sm btn-link float-end" id="cancelNewContact">Cancel</button>
            </h6>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="contactFirstName" class="form-label">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="contactFirstName" id="contactFirstName">
                </div>

                <div class="col-md-6">
                    <label for="contactLastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="contactLastName" id="contactLastName">
                </div>

                <div class="col-md-6">
                    <label for="contactTitle" class="form-label">Title</label>
                    <input type="text" class="form-control" name="contactTitle" id="contactTitle">
                </div>

                <div class="col-md-6">
                    <label for="contactEmail" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="contactEmail" id="contactEmail">
                </div>

                <div class="col-md-6">
                    <label for="contactTelephone" class="form-label">Telephone</label>
                    <input type="text" class="form-control" name="contactTelephone" id="contactTelephone">
                </div>

                <div class="col-md-6">
                    <label for="contactTypeID" class="form-label">Contact Role</label>
                    <select class="form-select" name="contactTypeID" id="contactTypeID">
                        <option value="">Select Role</option>
                        <?php
                        if ($contactTypes) {
                            foreach ($contactTypes as $type) {
                                echo "<option value='{$type->contactTypeID}'>{$type->contactType}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

<?php
echo Utility::form_modal_footer('Progress to Opportunity', 'progressBusinessDev', 'btn btn-success', true);

// Add Business Development Modal
$countries = Data::countries([], false, $DBConn);
$allEmployees = Employee::employees([], false, $DBConn);
$employeeCategorize = Employee::categorise_employee($allEmployees);
$clients = Client::client_full(array('orgDataID' => $orgDataID, 'entityID' => $entityID), false, $DBConn);

echo Utility::form_modal_header("addBusinessDevModal", "sales/manage_business_development.php", "Add Business Development", array('modal-lg', 'modal-dialog-centered'), $base);
?>
    <div class="d-flex justify-content-end mb-3 px-3 pt-2">
        <button type="button"
                class="btn btn-sm btn-link text-primary p-0"
                data-bs-toggle="modal"
                data-bs-target="#addBusinessDevDocModal"
                title="View guide for adding business development"
                onclick="event.preventDefault(); event.stopPropagation();">
            <i class="ri-information-line fs-18 me-1"></i> Need help?
        </button>
    </div>
<div class="businessDevForm" id="businessDevForm">
    <!-- Hidden Fields -->
    <input type="hidden" name="orgDataID" value="<?= $orgDataID ?>">
    <input type="hidden" name="entityID" value="<?= $entityID ?>">
    <input type="hidden" name="salesCaseID" id="bdSalesCaseID" value="">
    <input type="hidden" name="saleStage" value="business_development">

    <div class="row g-3">
        <!-- Prospect/Opportunity Name -->
        <div class="col-12">
            <label for="bdSalesCaseName" class="form-label">
                Prospect/Opportunity Name <span class="text-danger">*</span>
            </label>
            <input type="text" id="bdSalesCaseName" name="salesCaseName"
                   class="form-control" placeholder="Enter prospect name" required>
        </div>

        <!-- Client Selection -->
        <div class="col-12">
            <label for="bdClientID" class="form-label">
                Client/Potential Client <span class="text-danger">*</span>
                <a href="javascript:void(0);" class="text-primary float-end addNewClient"
                   title="Add New Client">
                    <i class="ri-add-line"></i> New Prospect
                </a>
            </label>
            <select id="bdClientID" name="clientID" class="form-select" required>
                <option value="">Select/Add Client</option>
                <?php
                if ($clients) {
                    foreach ($clients as $client) {
                        echo "<option value='{$client->clientID}'>{$client->clientName}</option>";
                    }
                }
                ?>
                <option value="newClient">+ Add New Client</option>
            </select>
        </div>

        <!-- New Client Fields (Hidden by default) -->
        <div class="newClientDiv d-none col-12" id="bdNewClientDiv">
            <div class="card bg-light p-3">
                <h6 class="mb-3">
                    <i class="ri-building-add-line me-2"></i>New Client Information
                    <button type="button" class="btn btn-sm btn-link float-end" id="bdCancelNewClient">Cancel</button>
                </h6>

                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="bdClientName" class="form-label">Client Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="bdClientName" name="newClientName"
                               placeholder="Enter client name">
                    </div>

                    <div class="col-md-6">
                        <label for="bdCountryID" class="form-label">Country</label>
                        <select class="form-select" id="bdCountryID" name="countryID">
                            <option value="">Select Country</option>
                            <?php
                            if ($countries) {
                                foreach ($countries as $country) {
                                    echo "<option value='{$country->countryID}'>{$country->countryName}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="bdCity" class="form-label">City</label>
                        <input type="text" class="form-control" id="bdCity" name="city" placeholder="City">
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Person -->
        <div class="col-12">
            <label for="bdClientContactID" class="form-label">Contact Person</label>
            <select id="bdClientContactID" name="clientContactID" class="form-select">
                <option value="">Select Contact</option>
                <option value="newContact">+ Add New Contact</option>
            </select>
        </div>

        <!-- New Contact Fields (Hidden by default) -->
        <div class="new_contact d-none col-12" id="bdNewContactDiv">
            <div class="card bg-light p-3">
                <h6 class="mb-3">
                    <i class="ri-user-add-line me-2"></i>New Contact Information
                    <button type="button" class="btn btn-sm btn-link float-end" id="bdCancelNewContact">Cancel</button>
                </h6>

                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="bdContactName" class="form-label">Contact Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="bdContactName" name="contactName"
                               placeholder="Enter contact name">
                    </div>

                    <div class="col-md-6">
                        <label for="bdContactEmail" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="bdContactEmail" name="contactEmail"
                               placeholder="contact@email.com">
                    </div>

                    <div class="col-md-6">
                        <label for="bdContactPhone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="bdContactPhone" name="contactPhone"
                               placeholder="+254 700 000000">
                    </div>

                    <div class="col-md-12">
                        <label for="bdContactPersonTitle" class="form-label">Position/Title</label>
                        <input type="text" class="form-control" id="bdContactPersonTitle" name="contactPersonTitle"
                               placeholder="e.g., CEO, Manager">
                    </div>
                </div>
            </div>
        </div>

        <!-- Business Unit -->
        <div class="col-12">
            <label for="bdBusinessUnitID" class="form-label">
                Business Unit <span class="text-danger">*</span>
            </label>
            <select id="bdBusinessUnitID" name="businessUnitID" class="form-select" required>
                <option value="">Select Business Unit</option>
                <?php
                if ($businessUnits) {
                    foreach ($businessUnits as $unit) {
                        echo "<option value='{$unit->businessUnitID}'>{$unit->businessUnitName}</option>";
                    }
                }
                ?>
                <option value="newUnit">+ Add New Business Unit</option>
            </select>
        </div>

        <!-- New Business Unit Field (Hidden) -->
        <div id="bdNewBusinessUnit" class="d-none col-12">
            <div class="card bg-light p-3">
                <label class="form-label">
                    New Business Unit Name
                    <button type="button" class="btn btn-sm btn-link float-end" id="bdCancelNewUnit">Cancel</button>
                </label>
                <input type="text" name="newBusinessUnit" class="form-control"
                       placeholder="Enter new business unit name">
            </div>
        </div>

        <!-- Prospect Owner -->
        <div class="col-12">
            <label for="bdSalesPersonID" class="form-label">
                Prospect Owner <span class="text-danger">*</span>
            </label>
            <select id="bdSalesPersonID" name="salesPersonID" class="form-select" required>
                <?php
                if ($employeeCategorize) {
                    echo Form::populate_select_element_from_grouped_object($employeeCategorize, 'ID', 'employeeName', $userDetails->ID, '', 'Select Owner');
                } else {
                    echo "<option value='{$userDetails->ID}'>{$userDetails->FirstName} {$userDetails->Surname}</option>";
                }
                ?>
            </select>
        </div>

        <!-- Expected Revenue -->
        <div class="col-12">
            <label for="bdExpectedRevenue" class="form-label">Expected Revenue (KES)</label>
            <div class="input-group">
                <span class="input-group-text">KES</span>
                <input type="number" id="bdExpectedRevenue" name="expectedRevenue"
                       class="form-control" placeholder="0.00" step="0.01" min="0">
            </div>
            <div class="form-text">Optional: Estimated potential value</div>
        </div>
    </div>
<?php
echo Utility::form_modal_footer('Save Business Development', 'saveBusinessDev', 'btn btn-success', true);

// Sales Details Modal
echo Utility::form_modal_header("salesDetailsModal", "sales/sale_details.php", "Sales Details", array('modal-xl', 'modal-dialog-centered', 'modal-dialog-scrollable'), $base);
    ?>
    <div id="salesDetailsContent">
        <div class="text-center py-5">
            <div class="spinner-border spinner-border-sm" role="status"></div>
        </div>
    </div>
    <?php
echo Utility::form_modal_footer('Close', 'closeSalesDetails', 'btn btn-secondary', false);
?>

<!-- Inline Styles for Enhanced UI -->
<style>
/* Dashboard Stats Cards */
.stats-card {
    border: none;
    border-radius: 12px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
}

.stats-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
}

.stats-value {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1;
    margin: 0.5rem 0;
}

.stats-label {
    font-size: 0.875rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stats-change {
    font-size: 0.875rem;
    font-weight: 600;
}

/* Kanban Board */
.kanban-board {
    display: flex;
    gap: 1rem;
    overflow-x: auto;
    padding-bottom: 1rem;
    width: 100%;
}

.kanban-column {
    flex: 1 1 300px;
    min-width: 280px;
    max-width: 400px;
    background: #f8f9fa;
    border-radius: 12px;
    padding: 1rem;
}

.kanban-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #dee2e6;
}

.kanban-title {
    font-weight: 600;
    font-size: 1rem;
    margin: 0;
}

.kanban-count {
    background: #fff;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
}

.kanban-items {
    min-height: 200px;
}

.kanban-item {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 0.75rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.kanban-item:hover {
    border-color: #6366f1;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
    transform: translateX(4px);
}

.kanban-item-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 0.5rem;
}

.kanban-item-title {
    font-weight: 600;
    font-size: 0.938rem;
    margin: 0;
    color: #1e293b;
}

.kanban-item-value {
    font-weight: 700;
    color: #10b981;
    font-size: 0.875rem;
}

.kanban-item-meta {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-top: 0.75rem;
}

.kanban-item-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

/* List View */
.sales-list-item {
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1.25rem;
    margin-bottom: 1rem;
    transition: all 0.2s ease;
}

.sales-list-item:hover {
    border-color: #6366f1;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
}

/* Custom Tabs */
.nav-tabs-header {
    border-bottom: none;
}

.nav-tabs-header .nav-link {
    border: none;
    color: #64748b;
    padding: 0.75rem 1.25rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.nav-tabs-header .nav-link:hover {
    color: #6366f1;
    background: #f1f5f9;
}

.nav-tabs-header .nav-link.active {
    color: #6366f1;
    background: transparent;
    border-bottom: 2px solid #6366f1;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-state-icon {
    font-size: 4rem;
    color: #cbd5e1;
    margin-bottom: 1rem;
}

.empty-state-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 0.5rem;
}

.empty-state-text {
    color: #94a3b8;
    margin-bottom: 1.5rem;
}

/* Responsive */
@media (max-width: 768px) {
    .kanban-column {
        flex: 1 1 280px;
        min-width: 260px;
        max-width: 100%;
    }

    .stats-value {
        font-size: 1.5rem;
    }
}

@media (min-width: 1400px) {
    .kanban-column {
        max-width: 450px;
    }
}

@media (min-width: 1920px) {
    .kanban-column {
        max-width: 500px;
    }
}

/* Business Development Expandable Details */
.case-details-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.detail-item {
    margin-bottom: 0.75rem;
}

.detail-item label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}

.detail-item p {
    font-size: 0.95rem;
}

/* View Details Button Icon Animation */
.view-details-btn i {
    transition: transform 0.3s ease;
}

.view-details-btn:hover i {
    transform: scale(1.2);
}

/* Toast Notifications */
.toast {
    min-width: 300px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border-radius: 8px;
    animation: slideInRight 0.3s ease-out;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.toast.show {
    display: block !important;
}

.toast .toast-body {
    padding: 12px 16px;
    font-size: 0.95rem;
}

.toast .btn-close-white {
    filter: brightness(0) invert(1);
}

/* Toast icon sizing */
.toast .toast-body i {
    font-size: 1.2rem;
    vertical-align: middle;
}
</style>

<!-- JavaScript for Dynamic Functionality -->
<script>
// Global configuration
const SalesDashboard = {
    config: {
        base: '<?= $jsBasePath ?>',
        apiUrl: '<?= $jsBasePath ?>php/scripts/sales/sales_api.php',
        orgDataID: '<?= $orgDataID ?>',
        entityID: '<?= $entityID ?>',
        userID: '<?= $userDetails->ID ?>',
        currentState: '<?= $state ?>',
        currentView: '<?= $view ?>',
        currentFilter: '<?= $filter ?>'
    },

    data: {
        stats: null,
        salesCases: null,
        pipeline: null
    },

    // Initialize dashboard
    init: function() {
        // Validate required config
        if (!this.config.orgDataID || this.config.orgDataID === '') {
            console.error('WARNING: orgDataID is missing or empty!');
        }
        if (!this.config.entityID || this.config.entityID === '') {
            console.error('WARNING: entityID is missing or empty!');
        }
        this.loadDashboardStats();
        this.loadSalesContent();
        this.setupEventListeners();
    },

    // Load dashboard statistics
    loadDashboardStats: function() {
        const params = new URLSearchParams({
            action: 'get_dashboard_stats',
            orgDataID: this.config.orgDataID,
            entityID: this.config.entityID
        });

        fetch(`${this.config.apiUrl}?${params}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.data.stats = data.data;
                    console.log(this.data.stats);
                    this.renderDashboardStats(data.data);
                } else {
                    throw new Error(data.message || 'Failed to load dashboard statistics');
                }
            })
            .catch(error => {
                console.error('Error loading dashboard stats:', error);
                this.showError('Failed to load dashboard statistics: ' + error.message);
            });
    },

    // Render dashboard statistics
    renderDashboardStats: function(stats) {
        const statsHTML = `
            <div class="row g-3">
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card stats-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-success-transparent text-success me-3">
                                    <i class="ri-money-dollar-circle-line"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="stats-label">Sales Won</div>
                                    <div class="stats-value text-success">KES ${this.formatNumber(stats.won_value)}</div>
                                    <div class="stats-change text-muted">
                                        <i class="ri-trophy-line me-1"></i>${stats.won_cases} deals closed
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card stats-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-primary-transparent text-primary me-3">
                                    <i class="ri-line-chart-line"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="stats-label">Pipeline Value</div>
                                    <div class="stats-value text-primary">KES ${this.formatNumber(stats.estimated_value)}</div>
                                    <div class="stats-change text-muted">
                                        <i class="ri-briefcase-line me-1"></i>${stats.active_cases} active cases
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card stats-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-warning-transparent text-warning me-3">
                                    <i class="ri-alarm-warning-line"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="stats-label">Needs Attention</div>
                                    <div class="stats-value text-warning">${stats.attention_needed}</div>
                                    <div class="stats-change text-muted">
                                        <i class="ri-time-line me-1"></i>Deadline approaching
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="card stats-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="stats-icon bg-info-transparent text-info me-3">
                                    <i class="ri-percent-line"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="stats-label">Win Rate</div>
                                    <div class="stats-value text-info">${stats.conversion_rate}%</div>
                                    <div class="stats-change text-muted">
                                        <i class="ri-arrow-up-line me-1"></i>Conversion rate
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('dashboardStats').innerHTML = statsHTML;
    },

    // Load sales content based on current view
    loadSalesContent: function() {
        const params = new URLSearchParams({
            action: 'get_sales_cases',
            orgDataID: this.config.orgDataID,
            entityID: this.config.entityID,
            stage: this.config.currentState,
            filter: this.config.currentFilter
        });

        fetch(`${this.config.apiUrl}?${params}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.data.salesCases = data.data;

                    // Only use Kanban view for 'opportunities' stage
                    // Other stages (business_development, won, lost) always use list view
                    if (this.config.currentState === 'opportunities' && this.config.currentView === 'kanban') {
                        this.loadPipelineData();
                    } else {
                        this.renderListView(data.data);
                    }
                } else {
                    throw new Error(data.message || 'Failed to load sales data');
                }
            })
            .catch(error => {
                console.error('Error loading sales content:', error);
                console.error('Error details:', error);
                this.showError('Failed to load sales data: ' + error.message);
            });
    },

    // Load pipeline data for kanban view
    loadPipelineData: function() {
        const params = new URLSearchParams({
            action: 'get_sales_pipeline',
            orgDataID: this.config.orgDataID,
            entityID: this.config.entityID
        });

        fetch(`${this.config.apiUrl}?${params}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    this.data.pipeline = data.data;
                    console.log(this.data.pipeline);
                    this.renderKanbanView(data.data);
                } else {
                    throw new Error(data.message || 'Failed to load pipeline data');
                }
            })
            .catch(error => {
                console.error('Error loading pipeline:', error);
                this.showError('Failed to load pipeline data: ' + error.message);
            });
    },

    // Render kanban view
    renderKanbanView: function(pipeline) {
        if (!pipeline || pipeline.length === 0) {
            this.showEmptyState('No sales pipeline configured');
            return;
        }

        let kanbanHTML = '<div class="kanban-board">';

        pipeline.forEach(column => {
            kanbanHTML += `
                <div class="kanban-column">
                    <div class="kanban-header">
                        <h3 class="kanban-title">${column.level}</h3>
                        <span class="kanban-count">${column.count}</span>
                    </div>
                    <div class="kanban-items">
                        ${this.renderKanbanItems(column.cases)}
                    </div>
                    <div class="kanban-footer mt-3 text-center">
                        <small class="text-muted">
                            Total: KES ${this.formatNumber(column.totalValue)}<br>
                            Weighted: KES ${this.formatNumber(column.weightedValue)}
                        </small>
                    </div>
                </div>
            `;
        });

        kanbanHTML += '</div>';
        document.getElementById('salesContent').innerHTML = kanbanHTML;

        // Add click handlers
        this.setupKanbanHandlers();
    },

    // Render kanban items
    renderKanbanItems: function(cases) {
        if (!cases || cases.length === 0) {
            return '<div class="text-center text-muted py-4"><small>No cases</small></div>';
        }

        return cases.map(salesCase => `
            <div class="kanban-item" data-case-id="${salesCase.salesCaseID}">
                <div class="kanban-item-header">
                    <h4 class="kanban-item-title">${salesCase.salesCaseName}</h4>
                    <span class="kanban-item-value">KES ${this.formatNumber(salesCase.salesCaseEstimate)}</span>
                </div>
                <div class="mb-2">
                    <small class="text-muted">
                        <i class="ri-building-line me-1"></i>${salesCase.clientName}
                    </small>
                </div>
                <div class="kanban-item-meta">
                    <span class="badge bg-light text-dark kanban-item-badge">
                        <i class="ri-percent-line"></i> ${salesCase.probability}%
                    </span>
                    ${salesCase.daysUntilClose !== null && salesCase.daysUntilClose > 0 ? `
                        <span class="badge ${salesCase.daysUntilClose <= 7 ? 'bg-warning' : 'bg-light'} text-dark kanban-item-badge">
                            <i class="ri-calendar-line"></i> ${salesCase.daysUntilClose} days
                        </span>
                    ` : ''}
                    ${salesCase.salesPersonName ? `
                        <span class="badge bg-light text-dark kanban-item-badge">
                            <i class="ri-user-line"></i> ${salesCase.salesPersonName}
                        </span>
                    ` : ''}
                </div>
            </div>
        `).join('');
    },

    // Render list view
    renderListView: function(cases) {
        if (!cases || cases.length === 0) {
            this.showEmptyState('No sales cases found');
            return;
        }

        // Get stage-specific styling
        const stageConfig = this.getStageConfig(this.config.currentState);

        let listHTML = '<div class="row">';

        cases.forEach(salesCase => {
            const borderClass = stageConfig.borderClass;
            const valueClass = stageConfig.valueClass;
            const stageBadge = this.config.currentState === 'order' ?
                '<span class="badge bg-success ms-2"><i class="ri-trophy-line me-1"></i>Won</span>' :
                this.config.currentState === 'lost' ?
                '<span class="badge bg-danger ms-2"><i class="ri-close-circle-line me-1"></i>Lost</span>' : '';

            listHTML += `
                <div class="col-lg-6 col-md-12 mb-3">
                    <div class="sales-list-item ${borderClass}" data-case-id="${salesCase.salesCaseID}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="flex-grow-1">
                                <h5 class="mb-1 fw-semibold">
                                    ${salesCase.salesCaseName}
                                    ${stageBadge}
                                </h5>
                                <p class="mb-1 text-muted">
                                    <i class="ri-building-line me-1"></i>${salesCase.clientName}
                                </p>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold ${valueClass} mb-1">KES ${this.formatNumber(salesCase.salesCaseEstimate)}</div>
                                ${salesCase.statusLevel && this.config.currentState !== 'business_development' ? `<span class="badge bg-primary-transparent">${salesCase.statusLevel}</span>` : ''}
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            ${this.config.currentState !== 'order' && this.config.currentState !== 'lost' && this.config.currentState !== 'business_development' ? `
                                <span class="badge bg-light text-dark">
                                    <i class="ri-percent-line"></i> ${salesCase.probability}% probability
                                </span>
                            ` : ''}
                            <span class="badge bg-light text-dark">
                                <i class="ri-briefcase-line"></i> ${salesCase.businessUnitName}
                            </span>
                            ${salesCase.daysUntilClose !== null &&
                              this.config.currentState !== 'order' &&
                              this.config.currentState !== 'lost' &&
                              this.config.currentState !== 'business_development' &&
                              salesCase.daysUntilClose > 0 ? `
                                <span class="badge ${salesCase.daysUntilClose <= 7 ? 'bg-warning' : 'bg-light text-dark'}">
                                    <i class="ri-calendar-line"></i> ${salesCase.daysUntilClose} days to close
                                </span>
                            ` : ''}
                            ${salesCase.dateClosed && (this.config.currentState === 'order' || this.config.currentState === 'lost') ? `
                                <span class="badge bg-light text-dark">
                                    <i class="ri-calendar-check-line"></i> Closed: ${this.formatDate(salesCase.dateClosed)}
                                </span>
                            ` : ''}
                            ${salesCase.salesPersonName ? `
                                <span class="badge bg-light text-dark">
                                    <i class="ri-user-line"></i> ${salesCase.salesPersonName}
                                </span>
                            ` : ''}
                        </div>
                        <div class="mt-3 d-flex gap-2">
                            ${this.config.currentState === 'business_development' ? `
                                <button class="btn btn-sm btn-success progress-to-opportunity-btn"
                                        data-case-id="${salesCase.salesCaseID}"
                                        data-client-id="${salesCase.clientID}"
                                        data-business-unit-id="${salesCase.businessUnitID}"
                                        data-case-name="${salesCase.salesCaseName}">
                                    <i class="ri-arrow-right-circle-line"></i> Progress to Opportunity
                                </button>
                            ` : ''}
                            <button class="btn btn-sm btn-primary-light view-details-btn" data-case-id="${salesCase.salesCaseID}">
                                <i class="ri-arrow-${this.config.currentState === 'business_development' ? 'down' : 'right'}-s-line"></i> View Details
                            </button>
                            ${this.config.currentState !== 'order' && this.config.currentState !== 'lost' ? `
                                <button class="btn btn-sm btn-secondary-light edit-case-btn" data-case-id="${salesCase.salesCaseID}">
                                    <i class="ri-edit-line"></i> Edit
                                </button>
                            ` : ''}
                        </div>

                        ${this.config.currentState === 'business_development' ? `
                            <!-- Expandable Details Section -->
                            <div class="case-details-section mt-3" id="details-${salesCase.salesCaseID}" style="display: none;">
                                <hr class="my-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="detail-item">
                                            <label class="text-muted small">Prospect Name</label>
                                            <p class="mb-0 fw-semibold">${salesCase.salesCaseName || 'N/A'}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-item">
                                            <label class="text-muted small">Client/Potential Client</label>
                                            <p class="mb-0 fw-semibold">${salesCase.clientName || 'N/A'}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-item">
                                            <label class="text-muted small">Business Unit</label>
                                            <p class="mb-0 fw-semibold">${salesCase.businessUnitName || 'N/A'}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-item">
                                            <label class="text-muted small">Prospect Owner</label>
                                            <p class="mb-0 fw-semibold">${salesCase.salesPersonName || 'N/A'}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-item">
                                            <label class="text-muted small">Expected Revenue</label>
                                            <p class="mb-0 fw-semibold ${valueClass}">KES ${this.formatNumber(salesCase.salesCaseEstimate || 0)}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="detail-item">
                                            <label class="text-muted small">Date Added</label>
                                            <p class="mb-0">${salesCase.DateAdded ? this.formatDate(salesCase.DateAdded) : 'N/A'}</p>
                                        </div>
                                    </div>
                                    ${salesCase.leadSourceID ? `
                                        <div class="col-md-6">
                                            <div class="detail-item">
                                                <label class="text-muted small">Lead Source</label>
                                                <p class="mb-0">${salesCase.leadSourceName || 'N/A'}</p>
                                            </div>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        });

        listHTML += '</div>';
        document.getElementById('salesContent').innerHTML = listHTML;

        // Add click handlers
        this.setupListHandlers();
    },

    // Get stage-specific configuration
    getStageConfig: function(stage) {
        const configs = {
            'business_development': {
                borderClass: 'border-start border-info border-4',
                valueClass: 'text-info',
                icon: 'ri-seedling-line',
                color: 'info'
            },
            'opportunities': {
                borderClass: 'border-start border-primary border-4',
                valueClass: 'text-primary',
                icon: 'ri-contacts-line',
                color: 'primary'
            },
            'order': {
                borderClass: 'border-start border-success border-4',
                valueClass: 'text-success',
                icon: 'ri-trophy-line',
                color: 'success'
            },
            'lost': {
                borderClass: 'border-start border-danger border-4',
                valueClass: 'text-muted',
                icon: 'ri-close-circle-line',
                color: 'danger'
            }
        };

        return configs[stage] || configs['opportunities'];
    },

    // Format date helper
    formatDate: function(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-KE', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    },

    // Show empty state
    showEmptyState: function(message) {
        const stageConfig = this.getStageConfig(this.config.currentState);
        const stageMessages = {
            'business_development': {
                title: 'No business development cases yet',
                text: 'Start building your pipeline by adding prospects',
                btnText: 'Add Prospect'
            },
            'opportunities': {
                title: 'No opportunities yet',
                text: 'Convert prospects to opportunities or create new ones',
                btnText: 'Add Opportunity'
            },
            'order': {
                title: 'No won deals yet',
                text: 'Keep working on your pipeline to close more deals',
                btnText: 'View Opportunities'
            },
            'lost': {
                title: 'No lost deals',
                text: 'This is where closed-lost deals will appear',
                btnText: 'View Opportunities'
            }
        };

        const msg = stageMessages[this.config.currentState] || stageMessages['opportunities'];

        const emptyHTML = `
            <div class="empty-state">
                <div class="empty-state-icon text-${stageConfig.color}">
                    <i class="${stageConfig.icon}"></i>
                </div>
                <h3 class="empty-state-title">${msg.title}</h3>
                <p class="empty-state-text">${msg.text}</p>
                ${this.config.currentState !== 'order' && this.config.currentState !== 'lost' ? `
                    <button class="btn btn-${stageConfig.color}" id="emptyStateAddBtn">
                        <i class="ri-add-line"></i> ${msg.btnText}
                    </button>
                ` : `
                    <a href="${this.config.base}html/?s=user&ss=sales&p=home&state=opportunities" class="btn btn-primary">
                        <i class="ri-arrow-right-line"></i> ${msg.btnText}
                    </a>
                `}
            </div>
        `;
        document.getElementById('salesContent').innerHTML = emptyHTML;

        document.getElementById('emptyStateAddBtn')?.addEventListener('click', () => {
            // For Business Development, open the Add Business Development (prospect) modal.
            // For other stages (e.g. Opportunities), open the Quick Add (opportunity) modal.
            const targetModalId = this.config.currentState === 'business_development'
                ? 'addBusinessDevModal'
                : 'quickAddModal';

            const modalElement = document.getElementById(targetModalId);
            if (modalElement && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modal = new bootstrap.Modal(modalElement);
                modal.show();
            }
        });
    },

    // Show error message
    showError: function(message) {
        const errorHTML = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.getElementById('salesContent').innerHTML = errorHTML;
    },

    // Setup event listeners
    setupEventListeners: function() {
        // Quick Add button
        document.getElementById('quickAddBtn')?.addEventListener('click', () => {
            const modal = new bootstrap.Modal(document.getElementById('quickAddModal'));
            modal.show();
        });

        // Show/hide view toggle based on stage
        // Only 'opportunities' stage supports kanban view
        const viewToggleGroup = document.querySelector('.btn-group[role="group"]');
        if (viewToggleGroup) {
            if (this.config.currentState === 'opportunities') {
                viewToggleGroup.style.display = '';
            } else {
                viewToggleGroup.style.display = 'none';
            }
        }

        // View toggle
        document.querySelectorAll('[data-view]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const view = e.currentTarget.dataset.view;
                // Only allow kanban for opportunities
                if (this.config.currentState !== 'opportunities' && view === 'kanban') {
                    return; // Don't navigate
                }

                // Stay on the current page (`p=<?= $p ?>`) instead of hard-coding an unknown page
                window.location.href =
                    this.config.base +
                    'html/?s=<?= $s ?>&ss=<?= $ss ?>&p=<?= $p ?>' +
                    '&state=' + this.config.currentState +
                    '&view=' + view +
                    '&filter=' + this.config.currentFilter;
            });
        });
    },

    // Setup kanban click handlers
    setupKanbanHandlers: function() {
        document.querySelectorAll('.kanban-item').forEach(item => {
            item.addEventListener('click', (e) => {
                const caseID = e.currentTarget.dataset.caseId;
                this.viewSalesDetails(caseID);
            });
        });
    },

    // Setup list click handlers
    setupListHandlers: function() {
        // Progress to Opportunity button (Business Development only)
        document.querySelectorAll('.progress-to-opportunity-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const caseID = e.currentTarget.dataset.caseId;
                const clientID = e.currentTarget.dataset.clientId;
                const businessUnitID = e.currentTarget.dataset.businessUnitId;
                const caseName = e.currentTarget.dataset.caseName;
                this.openProgressModal(caseID, clientID, businessUnitID, caseName);
            });
        });

        // View Details button
        document.querySelectorAll('.view-details-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const caseID = e.currentTarget.dataset.caseId;

                // For business development, toggle expandable section
                if (this.config.currentState === 'business_development') {
                    this.toggleBusinessDevDetails(caseID, e.currentTarget);
                } else {
                    // For other stages, redirect to details page
                    this.viewSalesDetails(caseID);
                }
            });
        });

        // Edit button
        document.querySelectorAll('.edit-case-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const caseID = e.currentTarget.dataset.caseId;
                this.editSalesCase(caseID);
            });
        });
    },

    // Open progress modal
    openProgressModal: function(caseID, clientID, businessUnitID, caseName) {
        // Populate hidden fields
        document.getElementById('progressSalesCaseID').value = caseID;
        document.getElementById('progressClientID').value = clientID;
        document.getElementById('progressBusinessUnitID').value = businessUnitID;

        // Load client contacts
        this.loadClientContacts(clientID);

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('progressBusinessDevModal'));
        modal.show();
    },

    // Load client contacts for progress modal
    loadClientContacts: function(clientID) {
        const contactSelect = document.getElementById('progressSalesCaseContactID');
        if (!contactSelect || !clientID) return;

        // Clear existing options (except default and "Add New")
        contactSelect.innerHTML = '<option value="">Select Contact</option>';

        // Fetch contacts from API or use cached data
        fetch(`${this.config.base}php/scripts/clients/get_client_contacts.php?clientID=${clientID}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.contacts) {
                    data.contacts.forEach(contact => {
                        const option = document.createElement('option');
                        option.value = contact.clientContactID;
                        option.textContent = `${contact.contactName} (${contact.contactEmail})`;
                        contactSelect.appendChild(option);
                    });
                }
                // Always add "Add New" option at the end
                const addNewOption = document.createElement('option');
                addNewOption.value = 'addNew';
                addNewOption.textContent = '+ Add New Contact';
                contactSelect.appendChild(addNewOption);
            })
            .catch(error => {
                console.error('Error loading contacts:', error);
                // Add "Add New" option even on error
                const addNewOption = document.createElement('option');
                addNewOption.value = 'addNew';
                addNewOption.textContent = '+ Add New Contact';
                contactSelect.appendChild(addNewOption);
            });
    },

    // View sales details
    viewSalesDetails: function(caseID) {
        window.location.href = `${this.config.base}html/?s=user&ss=sales&p=sale_details&saleid=${caseID}`;
    },

    // Toggle business development details expansion
    toggleBusinessDevDetails: function(caseID, buttonElement) {
        const detailsSection = document.getElementById(`details-${caseID}`);
        const icon = buttonElement.querySelector('i');

        if (!detailsSection) {
            console.error('Details section not found for case:', caseID);
            return;
        }

        // Toggle visibility
        if (detailsSection.style.display === 'none') {
            // Show details with smooth animation
            detailsSection.style.display = 'block';
            // Change icon to up arrow
            icon.className = 'ri-arrow-up-s-line';

            // Smooth scroll to make expanded content visible
            setTimeout(() => {
                detailsSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 100);
        } else {
            // Hide details
            detailsSection.style.display = 'none';
            // Change icon back to down arrow
            icon.className = 'ri-arrow-down-s-line';
        }
    },

    // Edit sales case
    editSalesCase: function(caseID) {
        // Load case data and open modal
        let salesCase = this.data.salesCases.find(c => c.salesCaseID === caseID);

        if (!salesCase) {
            // Case not in current view data - fetch it from API
            this.fetchAndEditSalesCase(caseID);
            return;
        }

        // Check if this is a business development case
        if (this.config.currentState === 'business_development') {
            // Open Business Development modal
            this.openBusinessDevModal(salesCase);
        } else {
            // Open Quick Add modal for opportunities
            this.openQuickAddModal(salesCase);
        }
    },

    // Fetch sales case data from API and open edit modal
    fetchAndEditSalesCase: function(caseID) {
        // Show loading indicator
        const loadingToast = this.showLoadingToast('Loading sales case...');

        const params = new URLSearchParams({
            action: 'getSalesCase',
            caseID: caseID,
            orgDataID: this.config.orgDataID,
            entityID: this.config.entityID
        });

        fetch(`${this.config.apiUrl}?${params}`)
            .then(response => response.json())
            .then(data => {
                this.hideLoadingToast(loadingToast);

                if (data.success && data.data) {
                    const salesCase = data.data;

                    // Verify we have the minimum required data
                    if (!salesCase.salesCaseID) {
                        this.showToast('Sales case data is incomplete (missing salesCaseID)', 'error', 4000);
                        return;
                    }

                    // Open appropriate modal based on stage
                    if (salesCase.saleStage === 'business_development') {
                        this.openBusinessDevModal(salesCase);
                    } else {
                        this.openQuickAddModal(salesCase);
                    }
                } else {
                    const errorMsg = data.message || 'Failed to load sales case details';
                    this.showToast(errorMsg, 'error', 4000);
                }
            })
            .catch(error => {
                this.hideLoadingToast(loadingToast);
                console.error('Error loading sales case:', error);
                this.showToast('An error occurred while loading the sales case', 'error', 4000);
            });
    },

    // Show loading toast
    showLoadingToast: function(message) {
        const toast = document.createElement('div');
        toast.className = 'position-fixed top-0 end-0 p-3';
        toast.style.zIndex = '9999';
        toast.innerHTML = `
            <div class="toast show" role="alert">
                <div class="toast-body">
                    <span class="spinner-border spinner-border-sm me-2"></span>
                    ${message}
                </div>
            </div>
        `;
        document.body.appendChild(toast);
        return toast;
    },

    // Hide loading toast
    hideLoadingToast: function(toast) {
        if (toast && toast.parentElement) {
            toast.remove();
        }
    },

    // Show toast notification (success, error, warning, info)
    showToast: function(message, type = 'info', duration = 3000) {
        // Define colors and icons for each type
        const types = {
            success: { bg: 'bg-success', icon: 'ri-check-line', textColor: 'text-white' },
            error: { bg: 'bg-danger', icon: 'ri-error-warning-line', textColor: 'text-white' },
            warning: { bg: 'bg-warning', icon: 'ri-alert-line', textColor: 'text-dark' },
            info: { bg: 'bg-primary', icon: 'ri-information-line', textColor: 'text-white' }
        };

        const config = types[type] || types.info;

        const toastContainer = document.createElement('div');
        toastContainer.className = 'position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';

        const toastId = 'toast-' + Date.now();
        toastContainer.innerHTML = `
            <div id="${toastId}" class="toast show align-items-center ${config.bg} ${config.textColor} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="${config.icon} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        document.body.appendChild(toastContainer);

        // Auto-hide after duration
        if (duration > 0) {
            setTimeout(() => {
                const toastElement = document.getElementById(toastId);
                if (toastElement) {
                    toastElement.classList.remove('show');
                    setTimeout(() => {
                        if (toastContainer && toastContainer.parentElement) {
                            toastContainer.remove();
                        }
                    }, 300); // Allow fade animation
                }
            }, duration);
        }

        return toastContainer;
    },

    // Open Business Development modal with case data
    openBusinessDevModal: function(salesCase) {
        const modalElement = document.getElementById('addBusinessDevModal');

        if (!modalElement) {
            console.error('Business Development modal not found!');
            this.showToast('Business Development modal not found on page', 'error', 4000);
            return;
        }

        const modalTitle = modalElement.querySelector('.modal-title');

        if (!modalTitle) {
            console.error('Modal title element not found!');
        }

        // Update modal title
        if (modalTitle) {
            if (salesCase && salesCase.salesCaseID) {
                modalTitle.textContent = 'Edit Business Development';
            } else {
                modalTitle.textContent = 'Add Business Development';
            }
        }

        // Populate form fields safely
        const bdSalesCaseID = document.getElementById('bdSalesCaseID');
        const bdSalesCaseName = document.getElementById('bdSalesCaseName');
        const bdClientID = document.getElementById('bdClientID');
        const bdBusinessUnitID = document.getElementById('bdBusinessUnitID');
        const bdSalesPersonID = document.getElementById('bdSalesPersonID');
        const bdExpectedRevenue = document.getElementById('bdExpectedRevenue');

        // Check for missing required fields
        const missingFields = [];
        if (!bdSalesCaseID) missingFields.push('bdSalesCaseID');
        if (!bdSalesCaseName) missingFields.push('bdSalesCaseName');
        if (!bdClientID) missingFields.push('bdClientID');
        if (!bdBusinessUnitID) missingFields.push('bdBusinessUnitID');
        if (!bdSalesPersonID) missingFields.push('bdSalesPersonID');

        if (missingFields.length > 0) {
            console.error('Missing form fields:', missingFields.join(', '));
        }

        if (bdSalesCaseID) bdSalesCaseID.value = salesCase.salesCaseID || '';
        if (bdSalesCaseName) bdSalesCaseName.value = salesCase.salesCaseName || '';
        if (bdClientID) bdClientID.value = salesCase.clientID || '';
        if (bdBusinessUnitID) bdBusinessUnitID.value = salesCase.businessUnitID || '';
        if (bdSalesPersonID) bdSalesPersonID.value = salesCase.salesPersonID || '';
        if (bdExpectedRevenue) bdExpectedRevenue.value = salesCase.salesCaseEstimate || '';

        // Load contacts for the selected client
        if (salesCase.clientID && typeof loadContactsForBusinessDev === 'function') {
            loadContactsForBusinessDev(salesCase.clientID);

            // Set the contact after contacts are loaded
            setTimeout(() => {
                if (salesCase.salesCaseContactID) {
                    const bdClientContactID = document.getElementById('bdClientContactID');
                    if (bdClientContactID) {
                        bdClientContactID.value = salesCase.salesCaseContactID;
                    }
                }
            }, 500);
        }

        // Show modal
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    },

    // Reset Business Development modal for new entry
    resetBusinessDevModal: function() {
        const modalElement = document.getElementById('addBusinessDevModal');
        const modalTitle = modalElement.querySelector('.modal-title');

        // Update modal title
        modalTitle.textContent = 'Add Business Development';

        // Clear all form fields
        document.getElementById('bdSalesCaseID').value = '';
        document.getElementById('bdSalesCaseName').value = '';
        document.getElementById('bdClientID').value = '';
        document.getElementById('bdClientContactID').value = '';
        document.getElementById('bdBusinessUnitID').value = '';
        // Pre-fill Prospect Owner with current logged-in user
        const bdSalesPersonID = document.getElementById('bdSalesPersonID');
        if (bdSalesPersonID && this.config.userID) {
            bdSalesPersonID.value = this.config.userID;
        }
        document.getElementById('bdExpectedRevenue').value = '';

        // Hide new client/contact/unit sections
        const newClientDiv = document.getElementById('bdNewClientDiv');
        const newContactDiv = document.getElementById('bdNewContactDiv');
        const newUnitDiv = document.getElementById('bdNewBusinessUnit');

        if (newClientDiv) newClientDiv.classList.add('d-none');
        if (newContactDiv) newContactDiv.classList.add('d-none');
        if (newUnitDiv) newUnitDiv.classList.add('d-none');

        // Clear contact dropdown (keep only the default options)
        const contactSelect = document.getElementById('bdClientContactID');
        contactSelect.innerHTML = '<option value="">Select Contact</option><option value="newContact">+ Add New Contact</option>';
    },

    // Open Quick Add modal with case data
    openQuickAddModal: function(salesCase) {
        // Populate Quick Add form fields
        // TODO: Implement population for Quick Add modal

        const modal = new bootstrap.Modal(document.getElementById('quickAddModal'));
        modal.show();
    },

    // Utility: Format number
    formatNumber: function(num) {
        return new Intl.NumberFormat('en-KE', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(num || 0);
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    SalesDashboard.init();

    // Handle Add Business Development Button - Reset form before opening
    const addBusinessDevBtn = document.getElementById('addBusinessDevBtn');
    if (addBusinessDevBtn) {
        addBusinessDevBtn.addEventListener('click', function() {
            SalesDashboard.resetBusinessDevModal();
        });
    }

    // Handle Progress Modal - New Contact Toggle
    const progressContactSelect = document.getElementById('progressSalesCaseContactID');
    const newContactFields = document.getElementById('newContactFields');
    const cancelNewContact = document.getElementById('cancelNewContact');

    if (progressContactSelect) {
        progressContactSelect.addEventListener('change', function() {
            if (this.value === 'addNew') {
                newContactFields?.classList.remove('d-none');
            } else {
                newContactFields?.classList.add('d-none');
            }
        });
    }

    if (cancelNewContact) {
        cancelNewContact.addEventListener('click', function() {
            progressContactSelect.value = '';
            newContactFields?.classList.add('d-none');
        });
    }

    // Auto-populate probability from selected status level
    const statusLevelRadios = document.querySelectorAll('input[name="saleStatusLevelID"]');
    const probabilityField = document.getElementById('progressProbability');

    statusLevelRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked && probabilityField) {
                const probability = this.getAttribute('data-probability');
                probabilityField.value = probability;
            }
        });
    });

    // Initialize flatpickr for Expected Close Date - Future dates only
    const progressExpectedCloseDateInput = document.getElementById('progressExpectedCloseDate');
    if (progressExpectedCloseDateInput && typeof flatpickr !== 'undefined') {
        const fpInstance = flatpickr(progressExpectedCloseDateInput, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'F j, Y',
            minDate: 'today',
            disableMobile: true,
            allowInput: false,
            clickOpens: true,
            onChange: function(selectedDates, dateStr, instance) {
                // Add visual feedback when date is selected
                if (selectedDates.length > 0) {
                    instance.element.classList.remove('is-invalid');
                    instance.element.classList.add('is-valid');

                    // Ensure the hidden input has the correct value in Y-m-d format
                    instance.input.value = dateStr;

                    // Debug log
                    console.log('Expected Close Date selected:', dateStr);
                    console.log('Hidden input value:', instance.input.value);
                    console.log('Alt input value:', instance.altInput.value);
                }
            },
            onReady: function(selectedDates, dateStr, instance) {
                // Add calendar icon to the input
                const calendarIcon = document.createElement('i');
                calendarIcon.className = 'ri-calendar-line';
                calendarIcon.style.position = 'absolute';
                calendarIcon.style.right = '10px';
                calendarIcon.style.top = '50%';
                calendarIcon.style.transform = 'translateY(-50%)';
                calendarIcon.style.pointerEvents = 'none';
                calendarIcon.style.color = '#6c757d';

                const wrapper = instance.altInput.parentElement;
                if (wrapper && !wrapper.querySelector('.ri-calendar-line')) {
                    wrapper.style.position = 'relative';
                    wrapper.appendChild(calendarIcon);
                }
            }
        });

        // Store flatpickr instance for later access
        window.progressDatePicker = fpInstance;
    } else if (!progressExpectedCloseDateInput) {
        console.warn('progressExpectedCloseDate input not found');
    } else if (typeof flatpickr === 'undefined') {
        console.warn('Flatpickr library not loaded');
    }

    // Handle Progress Form Submission
    const progressFormDiv = document.getElementById('progressBusinessDevForm');
    const progressSubmitBtn = document.getElementById('progressBusinessDev');

    // Find the actual form element (parent of the div, created by form_modal_header)
    const progressForm = progressFormDiv ? progressFormDiv.closest('form') : null;

    // Create a function to handle the submission
    const handleProgressSubmit = function(e) {
        if (e) e.preventDefault();

        if (!progressForm && !progressFormDiv) {
            SalesDashboard.showToast('Progress form not found', 'error');
            return;
        }

        const formElement = progressForm || progressFormDiv;

        // Validate required fields
        const statusLevel = formElement.querySelector('input[name="saleStatusLevelID"]:checked');
        if (!statusLevel) {
            SalesDashboard.showToast('Please select an opportunity status level', 'warning', 4000);
            return;
        }

        // Validate expected close date (MANDATORY)
        const expectedCloseDate = formElement.querySelector('#progressExpectedCloseDate');
        if (!expectedCloseDate || !expectedCloseDate.value) {
            SalesDashboard.showToast('Expected Close Date is required when progressing to opportunity stage', 'warning', 4000);
            expectedCloseDate?.focus();
            return;
        }

        // Validate probability is set
        const probability = formElement.querySelector('#progressProbability');
        if (!probability || !probability.value) {
            SalesDashboard.showToast('Please select an opportunity status level to set probability', 'warning', 4000);
            return;
        }

        // Create FormData from the form element
        const formData = new FormData(progressForm || undefined);

        // If no form, collect data manually from the div
        if (!progressForm) {
            progressFormDiv.querySelectorAll('input, select, textarea').forEach(element => {
                if (element.name) {
                    if (element.type === 'radio' || element.type === 'checkbox') {
                        if (element.checked) {
                            formData.append(element.name, element.value);
                        }
                    } else {
                        formData.append(element.name, element.value);
                    }
                }
            });
        }

        // Ensure expectedCloseDate from flatpickr is included (it might be hidden)
        const expectedCloseDateField = document.getElementById('progressExpectedCloseDate');
        if (expectedCloseDateField && expectedCloseDateField.value) {
            // Remove any existing expectedCloseDate entries
            if (formData.has('expectedCloseDate')) {
                formData.delete('expectedCloseDate');
            }
            // Add the correct value from the flatpickr input
            formData.set('expectedCloseDate', expectedCloseDateField.value);
            console.log('Expected Close Date being submitted:', expectedCloseDateField.value);
        }

        // Debug: Log all form data
        console.log('Form data being submitted:');
        for (let [key, value] of formData.entries()) {
            console.log(`  ${key}: ${value}`);
        }

        // Show loading state
        const submitBtn = document.getElementById('progressBusinessDev');
        const originalBtnText = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
        }

        const apiUrl = `${SalesDashboard.config.base}php/scripts/sales/progress_business_development.php`;

        fetch(apiUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(text => {
                // Try to parse JSON
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    throw new Error('Invalid JSON response: ' + text.substring(0, 200));
                }

                if (data.success) {
                    // Show success message
                    SalesDashboard.showToast('Successfully progressed to opportunity! Redirecting...', 'success', 2000);

                    // Close modal
                    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('progressBusinessDevModal'));
                    if (modalInstance) {
                        modalInstance.hide();
                    }

                    // Small delay to allow modal to close and user to see toast
                    setTimeout(() => {
                        // Redirect to sales details page
                        if (data.data && data.data.redirectUrl) {
                            window.location.href = data.data.redirectUrl;
                        } else {
                            // Fallback: go to opportunities tab
                            window.location.href = '<?= $base ?>html/?s=<?= $s ?>&ss=<?= $ss ?>&p=home&state=opportunities';
                        }
                    }, 1000);
                } else {
                    // Show error message
                    SalesDashboard.showToast(data.message || 'Failed to progress to opportunity', 'error', 5000);

                    // Re-enable button
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }

                    // If return URL provided, offer to go back
                    if (data.returnUrl && confirm('Would you like to return to the sales dashboard?')) {
                        window.location.href = data.returnUrl;
                    }
                }
            })
            .catch(error => {
                SalesDashboard.showToast('An error occurred: ' + error.message, 'error', 5000);

                // Re-enable button
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            });
    };

    // Attach the handler to both form submit and button click
    if (progressForm) {
        progressForm.addEventListener('submit', handleProgressSubmit);
    }

    if (progressSubmitBtn) {
        progressSubmitBtn.addEventListener('click', handleProgressSubmit);
    }

    // ========================================
    // Business Development Modal Handlers
    // ========================================

    const bdClientSelect = document.getElementById('bdClientID');
    const bdNewClientDiv = document.getElementById('bdNewClientDiv');
    const bdCancelNewClient = document.getElementById('bdCancelNewClient');
    const bdContactSelect = document.getElementById('bdClientContactID');
    const bdNewContactDiv = document.getElementById('bdNewContactDiv');
    const bdCancelNewContact = document.getElementById('bdCancelNewContact');
    const bdBusinessUnitSelect = document.getElementById('bdBusinessUnitID');
    const bdNewBusinessUnit = document.getElementById('bdNewBusinessUnit');
    const bdCancelNewUnit = document.getElementById('bdCancelNewUnit');

    // Handle Client Selection
    if (bdClientSelect) {
        bdClientSelect.addEventListener('change', function() {
            const clientID = this.value;

            if (clientID === 'newClient') {
                // Show new client fields
                bdNewClientDiv?.classList.remove('d-none');
            } else {
                // Hide new client fields
                bdNewClientDiv?.classList.add('d-none');

                // Load contacts for selected client
                if (clientID && clientID !== '') {
                    loadContactsForBusinessDev(clientID);
                } else {
                    // Reset contact dropdown
                    if (bdContactSelect) {
                        bdContactSelect.innerHTML = '<option value="">Select Contact</option><option value="newContact">+ Add New Contact</option>';
                    }
                }
            }
        });
    }

    // Cancel new client
    if (bdCancelNewClient) {
        bdCancelNewClient.addEventListener('click', function() {
            bdClientSelect.value = '';
            bdNewClientDiv?.classList.add('d-none');
        });
    }

    // Handle Contact Selection
    if (bdContactSelect) {
        bdContactSelect.addEventListener('change', function() {
            if (this.value === 'newContact') {
                bdNewContactDiv?.classList.remove('d-none');
            } else {
                bdNewContactDiv?.classList.add('d-none');
            }
        });
    }

    // Cancel new contact
    if (bdCancelNewContact) {
        bdCancelNewContact.addEventListener('click', function() {
            bdContactSelect.value = '';
            bdNewContactDiv?.classList.add('d-none');
        });
    }

    // Handle Business Unit Selection
    if (bdBusinessUnitSelect) {
        bdBusinessUnitSelect.addEventListener('change', function() {
            if (this.value === 'newUnit') {
                bdNewBusinessUnit?.classList.remove('d-none');
            } else {
                bdNewBusinessUnit?.classList.add('d-none');
            }
        });
    }

    // Cancel new business unit
    if (bdCancelNewUnit) {
        bdCancelNewUnit.addEventListener('click', function() {
            bdBusinessUnitSelect.value = '';
            bdNewBusinessUnit?.classList.add('d-none');
        });
    }

    // Load contacts for business development form
    function loadContactsForBusinessDev(clientID) {
        if (!bdContactSelect || !clientID) return;

        bdContactSelect.innerHTML = '<option value="">Loading...</option>';

        fetch(`${SalesDashboard.config.base}php/scripts/clients/get_client_contacts.php?clientID=${clientID}`)
            .then(response => response.json())
            .then(data => {
                bdContactSelect.innerHTML = '<option value="">Select Contact</option>';

                if (data.success && data.contacts && data.contacts.length > 0) {
                    data.contacts.forEach(contact => {
                        const option = document.createElement('option');
                        option.value = contact.clientContactID;
                        option.textContent = `${contact.contactName} (${contact.contactEmail})`;
                        bdContactSelect.appendChild(option);
                    });
                }

                // Always add "Add New" option
                const addNewOption = document.createElement('option');
                addNewOption.value = 'newContact';
                addNewOption.textContent = '+ Add New Contact';
                bdContactSelect.appendChild(addNewOption);
            })
            .catch(error => {
                console.error('Error loading contacts:', error);
                bdContactSelect.innerHTML = '<option value="">Select Contact</option><option value="newContact">+ Add New Contact</option>';
            });
    }

    // ========================================
    // Business Development Form Submission
    // ========================================

    const businessDevForm = document.getElementById('businessDevForm');
    const businessDevFormEl = businessDevForm ? businessDevForm.closest('form') : null;
    const saveBusinessDevBtn = document.getElementById('saveBusinessDev');

    const handleBusinessDevSubmit = function(e) {
        if (e) e.preventDefault();

        if (!businessDevFormEl && !businessDevForm) {
            SalesDashboard.showToast('Business development form not found', 'error');
            return;
        }

        const formElement = businessDevFormEl || businessDevForm;

        // Validate required fields
        const salesCaseName = formElement.querySelector('#bdSalesCaseName');
        const clientID = formElement.querySelector('#bdClientID');
        const businessUnitID = formElement.querySelector('#bdBusinessUnitID');
        const salesPersonID = formElement.querySelector('#bdSalesPersonID');

        if (!salesCaseName || !salesCaseName.value) {
            SalesDashboard.showToast('Prospect/Opportunity name is required', 'warning', 4000);
            salesCaseName?.focus();
            return;
        }

        if (!clientID || !clientID.value) {
            SalesDashboard.showToast('Client is required', 'warning', 4000);
            clientID?.focus();
            return;
        }

        if (!businessUnitID || !businessUnitID.value) {
            SalesDashboard.showToast('Business unit is required', 'warning', 4000);
            businessUnitID?.focus();
            return;
        }

        if (!salesPersonID || !salesPersonID.value) {
            SalesDashboard.showToast('Prospect owner is required', 'warning', 4000);
            salesPersonID?.focus();
            return;
        }

        // Create FormData
        const formData = new FormData(businessDevFormEl || undefined);

        // If no form, collect manually
        if (!businessDevFormEl) {
            businessDevForm.querySelectorAll('input, select, textarea').forEach(element => {
                if (element.name) {
                    formData.append(element.name, element.value);
                }
            });
        }

        // Show loading state
        const submitBtn = saveBusinessDevBtn;
        const originalBtnText = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
        }

        const apiUrl = `${SalesDashboard.config.base}php/scripts/sales/manage_business_development.php`;

        fetch(apiUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(text => {
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                throw new Error('Invalid response: ' + text.substring(0, 200));
            }

            if (data.success) {
                SalesDashboard.showToast('Business development prospect saved successfully!', 'success', 2000);

                // Close modal
                const modalInstance = bootstrap.Modal.getInstance(document.getElementById('addBusinessDevModal'));
                if (modalInstance) {
                    modalInstance.hide();
                }

                // Reload page to show new prospect
                setTimeout(() => {
                    if (data.data && data.data.redirectUrl) {
                        // Construct full URL using base path
                        const redirectUrl = data.data.redirectUrl.startsWith('?')
                            ? `${SalesDashboard.config.base}html/${data.data.redirectUrl}`
                            : data.data.redirectUrl;
                        window.location.href = redirectUrl;
                    } else {
                        window.location.reload();
                    }
                }, 1000);
            } else {
                SalesDashboard.showToast(data.message || 'Failed to save', 'error', 5000);

                // Re-enable button
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            }
        })
        .catch(error => {
            SalesDashboard.showToast('An error occurred: ' + error.message, 'error', 5000);

            // Re-enable button
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });
    };

    // Attach handlers
    if (businessDevFormEl) {
        businessDevFormEl.addEventListener('submit', handleBusinessDevSubmit);
    }

    if (saveBusinessDevBtn) {
        saveBusinessDevBtn.addEventListener('click', handleBusinessDevSubmit);
    }
});

// ============================================================================
// CLIENTS DIRECTORY FUNCTIONALITY
// ============================================================================
(function() {
    'use strict';

    // Only initialize if clients directory is active
    if (typeof window.clientsData === 'undefined') {
        return;
    }

    const ClientsDirectory = {
        allClients: window.clientsData || [],
        openCasesCount: window.openCasesCount || {},
        filteredClients: [],
        currentPage: 1,
        itemsPerPage: 50,
        sortColumn: 'clientName',
        sortDirection: 'asc',
        searchTerm: '',

        init: function() {
            this.filteredClients = [...this.allClients];
            // Get initial items per page from select
            const perPageSelect = document.getElementById('clientsPerPageSelect');
            if (perPageSelect) {
                this.itemsPerPage = parseInt(perPageSelect.value) || 50;
            }
            this.setupEventListeners();
            this.updateSortIndicators();
            this.render();
        },

        setupEventListeners: function() {
            // Search input
            const searchInput = document.getElementById('clientSearchInput');
            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    this.searchTerm = e.target.value.toLowerCase().trim();
                    this.currentPage = 1;
                    this.filterAndRender();
                });
            }

            // Items per page selector
            const perPageSelect = document.getElementById('clientsPerPageSelect');
            if (perPageSelect) {
                perPageSelect.addEventListener('change', (e) => {
                    this.itemsPerPage = parseInt(e.target.value);
                    this.currentPage = 1;
                    this.render();
                });
            }

            // Sortable column headers
            const sortableHeaders = document.querySelectorAll('.sortable');
            sortableHeaders.forEach(header => {
                header.addEventListener('click', () => {
                    const column = header.getAttribute('data-sort');
                    if (this.sortColumn === column) {
                        this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
                    } else {
                        this.sortColumn = column;
                        this.sortDirection = 'asc';
                    }
                    this.updateSortIndicators();
                    this.sortAndRender();
                });
            });
        },

        filterAndRender: function() {
            if (!this.searchTerm) {
                this.filteredClients = [...this.allClients];
            } else {
                this.filteredClients = this.allClients.filter(client => {
                    const clientName = (client.clientName || '').toLowerCase();
                    const accountOwner = (client.clientOwnerName || 'Unassigned').toLowerCase();
                    return clientName.includes(this.searchTerm) || accountOwner.includes(this.searchTerm);
                });
            }
            this.sortAndRender();
        },

        sortAndRender: function() {
            this.filteredClients.sort((a, b) => {
                let aVal, bVal;

                switch(this.sortColumn) {
                    case 'clientName':
                        aVal = (a.clientName || '').toLowerCase();
                        bVal = (b.clientName || '').toLowerCase();
                        break;
                    case 'accountOwner':
                        aVal = (a.clientOwnerName || 'Unassigned').toLowerCase();
                        bVal = (b.clientOwnerName || 'Unassigned').toLowerCase();
                        break;
                    case 'openCases':
                        aVal = this.openCasesCount[a.clientID] || 0;
                        bVal = this.openCasesCount[b.clientID] || 0;
                        break;
                    default:
                        return 0;
                }

                if (this.sortColumn === 'openCases') {
                    return this.sortDirection === 'asc' ? aVal - bVal : bVal - aVal;
                } else {
                    if (aVal < bVal) return this.sortDirection === 'asc' ? -1 : 1;
                    if (aVal > bVal) return this.sortDirection === 'asc' ? 1 : -1;
                    return 0;
                }
            });

            this.render();
        },

        updateSortIndicators: function() {
            const headers = document.querySelectorAll('.sortable');
            headers.forEach(header => {
                const column = header.getAttribute('data-sort');
                const icon = header.querySelector('i');
                if (icon) {
                    if (column === this.sortColumn) {
                        icon.className = this.sortDirection === 'asc'
                            ? 'ri-arrow-up-line ms-1'
                            : 'ri-arrow-down-line ms-1';
                    } else {
                        icon.className = 'ri-arrow-up-down-line ms-1';
                    }
                }
            });
        },

        render: function() {
            this.renderTable();
            this.renderPagination();
            this.updateCountDisplay();
        },

        renderTable: function() {
            const tbody = document.getElementById('clientsTableBody');
            if (!tbody) return;

            const startIndex = (this.currentPage - 1) * this.itemsPerPage;
            const endIndex = startIndex + this.itemsPerPage;
            const pageClients = this.filteredClients.slice(startIndex, endIndex);

            if (pageClients.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="ri-inbox-line fs-24 d-block mb-2"></i>
                            No clients found
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = pageClients.map(client => {
                const accountOwner = client.clientOwnerName || 'Unassigned';
                const openCases = this.openCasesCount[client.clientID] || 0;
                const dateAdded = client.DateAdded
                    ? new Date(client.DateAdded).toLocaleDateString('en-KE', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric'
                    })
                    : 'N/A';
                const clientCode = client.clientCode || '-';
                const clientLink = `<?= $base ?>html/?s=<?= $s ?>&ss=<?= $ss ?>&p=clients/client_details&client_id=${client.clientID}&orgDataID=<?= $orgDataID ?>&entityID=<?= $entityID ?>`;

                return `
                    <tr>
                        <td>
                            <a href="${clientLink}" class="text-primary fw-semibold">
                                ${this.escapeHtml(client.clientName || 'N/A')}
                            </a>
                        </td>
                        <td>${this.escapeHtml(accountOwner)}</td>
                        <td class="text-center">
                            <span class="badge ${openCases > 0 ? 'bg-primary' : 'bg-secondary'}">
                                ${openCases}
                            </span>
                        </td>
                        <td>${this.escapeHtml(clientCode)}</td>
                        <td>${dateAdded}</td>
                        <td class="text-center">
                            <a href="${clientLink}" class="btn btn-sm btn-outline-primary" title="View Details">
                                <i class="ri-eye-line"></i>
                            </a>
                        </td>
                    </tr>
                `;
            }).join('');
        },

        renderPagination: function() {
            const paginationControls = document.getElementById('paginationControls');
            const paginationInfo = document.getElementById('paginationInfo');
            if (!paginationControls || !paginationInfo) return;

            const totalPages = Math.ceil(this.filteredClients.length / this.itemsPerPage);
            const startItem = totalPages > 0 ? (this.currentPage - 1) * this.itemsPerPage + 1 : 0;
            const endItem = Math.min(this.currentPage * this.itemsPerPage, this.filteredClients.length);

            paginationInfo.textContent = `Showing ${startItem} - ${endItem} of ${this.filteredClients.length}`;

            if (totalPages <= 1) {
                paginationControls.innerHTML = '';
                return;
            }

            let paginationHTML = '';

            // Previous button
            paginationHTML += `
                <li class="page-item ${this.currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${this.currentPage - 1}">
                        <i class="ri-arrow-left-s-line"></i>
                    </a>
                </li>
            `;

            // Page numbers
            const maxVisiblePages = 5;
            let startPage = Math.max(1, this.currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

            if (endPage - startPage < maxVisiblePages - 1) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }

            if (startPage > 1) {
                paginationHTML += `
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="1">1</a>
                    </li>
                `;
                if (startPage > 2) {
                    paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                paginationHTML += `
                    <li class="page-item ${i === this.currentPage ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `;
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
                paginationHTML += `
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a>
                    </li>
                `;
            }

            // Next button
            paginationHTML += `
                <li class="page-item ${this.currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${this.currentPage + 1}">
                        <i class="ri-arrow-right-s-line"></i>
                    </a>
                </li>
            `;

            paginationControls.innerHTML = paginationHTML;

            // Add click handlers
            paginationControls.querySelectorAll('a.page-link').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const page = parseInt(link.getAttribute('data-page'));
                    if (page && page !== this.currentPage && page >= 1 && page <= totalPages) {
                        this.currentPage = page;
                        this.render();
                        // Scroll to top of table
                        document.getElementById('clientsDirectoryTable')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });
        },

        updateCountDisplay: function() {
            const countDisplay = document.getElementById('clientsCountDisplay');
            if (countDisplay) {
                countDisplay.textContent = `${this.filteredClients.length} client${this.filteredClients.length !== 1 ? 's' : ''}`;
            }
        },

        escapeHtml: function(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => ClientsDirectory.init());
    } else {
        ClientsDirectory.init();
    }
})();

// Fix for clientContacts undefined error
if (typeof clientContacts === 'undefined') {
    window.clientContacts = [];
}

// Ensure documentation modals open correctly
// Direct event handlers for all doc modal triggers
(function() {
    'use strict';

    function showDocModal(modalId) {
        console.log('Attempting to show modal:', modalId);
        const modalElement = document.getElementById(modalId);
        if (!modalElement) {
            console.error('Modal not found:', modalId);
            alert('Modal not found: ' + modalId);
            return;
        }

        console.log('Modal element found:', modalElement);

        // Try Bootstrap Modal API first
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            try {
                console.log('Using Bootstrap Modal API');
                const modal = new bootstrap.Modal(modalElement, {
                    backdrop: true,
                    keyboard: true,
                    focus: true
                });
                modal.show();
                console.log('Bootstrap modal.show() called');
                return;
            } catch (e) {
                console.warn('Bootstrap Modal API failed, using fallback:', e);
            }
        }

        // Manual modal display - FORCE IT TO SHOW
        console.log('Using manual modal display');
        modalElement.classList.remove('fade');
        modalElement.classList.add('show');
        modalElement.style.display = 'block';
        modalElement.style.zIndex = '1055';
        modalElement.setAttribute('aria-hidden', 'false');
        modalElement.setAttribute('aria-modal', 'true');
        document.body.classList.add('modal-open');
        document.body.style.overflow = 'hidden';
        document.body.style.paddingRight = '0px';

        // Ensure modal dialog is visible - CRITICAL
        const modalDialog = modalElement.querySelector('.modal-dialog');
        if (modalDialog) {
            modalDialog.style.setProperty('display', 'block', 'important');
            modalDialog.style.setProperty('visibility', 'visible', 'important');
            modalDialog.style.setProperty('opacity', '1', 'important');
            modalDialog.style.setProperty('z-index', '1056', 'important');
            modalDialog.style.setProperty('position', 'relative', 'important');
            modalDialog.classList.add('show');
            console.log('Modal dialog styled');
        }

        // Ensure modal-content is visible
        const modalContent = modalElement.querySelector('.modal-content');
        if (modalContent) {
            modalContent.style.setProperty('display', 'block', 'important');
            modalContent.style.setProperty('visibility', 'visible', 'important');
            modalContent.style.setProperty('opacity', '1', 'important');
        }

        // Create backdrop
        let backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
        backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop fade show';
        backdrop.style.zIndex = '1050';
        document.body.appendChild(backdrop);
        console.log('Backdrop created');

        // Handle close buttons
        modalElement.querySelectorAll('[data-bs-dismiss="modal"], .btn-close').forEach(closeBtn => {
            closeBtn.addEventListener('click', function() {
                modalElement.classList.remove('show');
                modalElement.style.display = 'none';
                modalElement.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                const backdropEl = document.querySelector('.modal-backdrop');
                if (backdropEl) {
                    backdropEl.remove();
                }
            }, { once: true });
        });
    }

    // Wait for DOM to be ready
    function initDocModals() {
        console.log('Initializing doc modals...');

        // Handle all buttons with class 'open-doc-modal'
        document.querySelectorAll('.open-doc-modal').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const modalId = this.getAttribute('data-modal-id');
                console.log('Button clicked, modal ID:', modalId);
                if (modalId) {
                    showDocModal(modalId);
                }
                return false;
            });
        });

        // Also handle buttons with data-modal-target
        document.querySelectorAll('[data-modal-target]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                let modalId = this.getAttribute('data-modal-target');
                if (modalId) {
                    modalId = modalId.replace('#', '');
                    showDocModal(modalId);
                }
                return false;
            });
        });

        // Debug: Log modal elements
        console.log('Modal check:', {
            businessDevelopmentDocModal: !!document.getElementById('businessDevelopmentDocModal'),
            addBusinessDevDocModal: !!document.getElementById('addBusinessDevDocModal'),
            progressBusinessDevDocModal: !!document.getElementById('progressBusinessDevDocModal')
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDocModals);
    } else {
        initDocModals();
    }
})();
</script>

<!-- ============================================================================
     BUSINESS DEVELOPMENT DOCUMENTATION MODALS
     ============================================================================ -->

<!-- Main Business Development Documentation Modal -->
<div class="modal fade" id="businessDevelopmentDocModal" tabindex="-1" aria-labelledby="businessDevelopmentDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="businessDevelopmentDocModalLabel">
                    <i class="ri-seedling-line me-2"></i>
                    Business Development Guide
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4">
                    <h6 class="text-info mb-3">
                        <i class="ri-eye-line me-2"></i>
                        Overview
                    </h6>
                    <p class="text-muted">
                        The Business Development stage is where you track early-stage prospects and potential opportunities.
                        This is the initial phase of the sales pipeline where you identify, qualify, and nurture prospects
                        before they become formal opportunities. Use this stage to manage initial contacts, track potential
                        revenue, and prepare prospects for progression to the Opportunities stage.
                    </p>
                </div>

                <div class="mb-4">
                    <h6 class="text-info mb-3">
                        <i class="ri-navigation-line me-2"></i>
                        Navigation
                    </h6>
                    <div class="card border-info-transparent mb-3">
                        <div class="card-body">
                            <p class="mb-2"><strong>Accessing Business Development:</strong></p>
                            <ol class="mb-0">
                                <li class="mb-2">Navigate to the <strong>Sales Dashboard</strong> from the main menu</li>
                                <li class="mb-2">Click on the <strong>"Business Development"</strong> tab</li>
                                <li class="mb-2">You'll see all prospects in the business development stage</li>
                                <li class="mb-2">Use the filter dropdown to view "All Sales" or "My Sales"</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="text-info mb-3">
                        <i class="ri-tools-line me-2"></i>
                        Key Features
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-start border-success border-3">
                                <div class="card-body">
                                    <h6 class="fw-semibold mb-2">
                                        <i class="ri-add-circle-line text-success me-2"></i>
                                        Add New Prospect
                                    </h6>
                                    <p class="small text-muted mb-0">
                                        Create new business development entries for potential clients. Include prospect name,
                                        client information, contact person, and expected revenue.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-start border-primary border-3">
                                <div class="card-body">
                                    <h6 class="fw-semibold mb-2">
                                        <i class="ri-edit-line text-primary me-2"></i>
                                        Edit Prospect
                                    </h6>
                                    <p class="small text-muted mb-0">
                                        Update prospect information, change ownership, modify expected revenue, or update
                                        contact details as relationships develop.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-start border-warning border-3">
                                <div class="card-body">
                                    <h6 class="fw-semibold mb-2">
                                        <i class="ri-arrow-right-circle-line text-warning me-2"></i>
                                        Progress to Opportunity
                                    </h6>
                                    <p class="small text-muted mb-0">
                                        When a prospect shows genuine interest and potential, progress them to the
                                        Opportunities stage with appropriate status and probability.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-start border-secondary border-3">
                                <div class="card-body">
                                    <h6 class="fw-semibold mb-2">
                                        <i class="ri-eye-line text-secondary me-2"></i>
                                        View Details
                                    </h6>
                                    <p class="small text-muted mb-0">
                                        Click on any prospect card to view comprehensive details including contact information,
                                        notes, and activity history.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mb-0">
                    <h6 class="alert-heading">
                        <i class="ri-lightbulb-line me-2"></i>
                        Best Practices
                    </h6>
                    <ul class="mb-0 small">
                        <li>Keep prospect information up to date as relationships develop</li>
                        <li>Regularly review and qualify prospects before progressing to Opportunities</li>
                        <li>Use expected revenue estimates to prioritize follow-up activities</li>
                        <li>Assign prospects to appropriate team members for accountability</li>
                        <li>Progress prospects to Opportunities only when there's genuine potential</li>
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

<!-- Add Business Development Documentation Modal -->
<div class="modal fade" id="addBusinessDevDocModal" tabindex="-1" aria-labelledby="addBusinessDevDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="addBusinessDevDocModalLabel">
                    <i class="ri-add-circle-line me-2"></i>
                    Adding Business Development Guide
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <p class="text-muted">
                        Learn how to add a new prospect to the Business Development stage. This is the first step in
                        tracking potential business opportunities.
                    </p>
                </div>

                <div class="card border-success-transparent mb-3">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">Step-by-Step Instructions</h6>
                        <ol class="mb-0">
                            <li class="mb-2">
                                <strong>Click "Add Business Development":</strong> Click the green button in the top-right
                                corner of the Business Development page.
                            </li>
                            <li class="mb-2">
                                <strong>Enter Prospect Name:</strong> Provide a descriptive name for the prospect or opportunity.
                                This should be clear and identifiable (e.g., "ABC Corp - Tax Advisory Services").
                            </li>
                            <li class="mb-2">
                                <strong>Select or Add Client:</strong>
                                <ul class="mt-1">
                                    <li>Choose an existing client from the dropdown if the prospect is associated with a known client</li>
                                    <li>Click "New Prospect" or select "+ Add New Client" to create a new client record</li>
                                    <li>If adding a new client, fill in the client name, country, and city</li>
                                </ul>
                            </li>
                            <li class="mb-2">
                                <strong>Add Contact Person:</strong>
                                <ul class="mt-1">
                                    <li>Select an existing contact from the dropdown if available</li>
                                    <li>Choose "+ Add New Contact" to create a new contact person</li>
                                    <li>For new contacts, provide name, email (required), phone, and position/title</li>
                                </ul>
                            </li>
                            <li class="mb-2">
                                <strong>Select Business Unit:</strong> Choose the business unit that will handle this prospect.
                                This helps with resource allocation and reporting.
                            </li>
                            <li class="mb-2">
                                <strong>Assign Prospect Owner:</strong> Select the team member responsible for managing this
                                prospect. By default, this is set to you.
                            </li>
                            <li class="mb-2">
                                <strong>Set Expected Revenue (Optional):</strong> Enter an estimated potential value in Kenyan
                                Shillings (KES). This helps prioritize prospects and forecast potential revenue.
                            </li>
                            <li class="mb-2">
                                <strong>Save:</strong> Click "Save Business Development" to create the prospect entry.
                            </li>
                        </ol>
                    </div>
                </div>

                <div class="card border-primary-transparent mb-3">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">Field Requirements</h6>
                        <ul class="mb-0">
                            <li class="mb-2">
                                <strong>Required Fields:</strong>
                                <ul>
                                    <li>Prospect/Opportunity Name</li>
                                    <li>Client/Potential Client</li>
                                    <li>Business Unit</li>
                                    <li>Prospect Owner</li>
                                </ul>
                            </li>
                            <li class="mb-2">
                                <strong>Optional Fields:</strong>
                                <ul>
                                    <li>Contact Person (can be added later)</li>
                                    <li>Expected Revenue (estimate)</li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="alert alert-info mb-0">
                    <h6 class="alert-heading">
                        <i class="ri-lightbulb-line me-2"></i>
                        Tips
                    </h6>
                    <ul class="mb-0 small">
                        <li>Use clear, descriptive names that make it easy to identify the prospect</li>
                        <li>Add contact information early to facilitate communication</li>
                        <li>Set realistic expected revenue estimates based on initial discussions</li>
                        <li>Assign prospects to team members based on expertise and workload</li>
                        <li>You can always edit prospect information later if details change</li>
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

<!-- Progress to Opportunity Documentation Modal -->
<div class="modal fade" id="progressBusinessDevDocModal" tabindex="-1" aria-labelledby="progressBusinessDevDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="progressBusinessDevDocModalLabel">
                    <i class="ri-arrow-right-circle-line me-2"></i>
                    Progressing to Opportunity Guide
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <p class="text-muted">
                        Learn how to progress a prospect from Business Development to the Opportunities stage. This
                        should be done when a prospect shows genuine interest and potential for conversion.
                    </p>
                </div>

                <div class="card border-warning-transparent mb-3">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">When to Progress a Prospect</h6>
                        <p class="mb-2">Progress a prospect to Opportunity when:</p>
                        <ul class="mb-0">
                            <li>The prospect has expressed genuine interest in your services</li>
                            <li>You've had meaningful discussions about their needs</li>
                            <li>There's a clear potential for a business relationship</li>
                            <li>The prospect has budget and decision-making authority</li>
                            <li>You're ready to move forward with formal proposals or quotes</li>
                        </ul>
                    </div>
                </div>

                <div class="card border-primary-transparent mb-3">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">Step-by-Step Instructions</h6>
                        <ol class="mb-0">
                            <li class="mb-2">
                                <strong>Select the Prospect:</strong> Click on a prospect card in the Business Development
                                stage, then click the "Progress to Opportunity" button or action.
                            </li>
                            <li class="mb-2">
                                <strong>Select Opportunity Status:</strong> Choose the appropriate status level that reflects
                                where the opportunity is in the sales process. Common statuses include:
                                <ul class="mt-1">
                                    <li><strong>Lead:</strong> Initial interest expressed</li>
                                    <li><strong>Qualified:</strong> Prospect meets qualification criteria</li>
                                    <li><strong>Proposal:</strong> Formal proposal submitted</li>
                                    <li><strong>Negotiation:</strong> Discussing terms and conditions</li>
                                    <li><strong>Closing:</strong> Finalizing the deal</li>
                                </ul>
                            </li>
                            <li class="mb-2">
                                <strong>Set Probability:</strong> The probability percentage is automatically set based on
                                the selected status level. This indicates the likelihood of closing the deal.
                            </li>
                            <li class="mb-2">
                                <strong>Update Sales Estimate:</strong> Review and update the sales estimate if it has changed
                                based on discussions with the prospect.
                            </li>
                            <li class="mb-2">
                                <strong>Set Expected Close Date:</strong> Enter the anticipated date when you expect to close
                                this opportunity. This helps with forecasting and planning.
                            </li>
                            <li class="mb-2">
                                <strong>Add Lead Source (Optional):</strong> Select how you learned about this opportunity
                                (e.g., Referral, Website, Cold Call, etc.).
                            </li>
                            <li class="mb-2">
                                <strong>Add Notes:</strong> Include any relevant information about the progression, discussions,
                                or next steps.
                            </li>
                            <li class="mb-2">
                                <strong>Progress:</strong> Click "Progress to Opportunity" to move the prospect to the
                                Opportunities stage.
                            </li>
                        </ol>
                    </div>
                </div>

                <div class="card border-info-transparent mb-3">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">Understanding Status Levels and Probability</h6>
                        <p class="mb-2">Each status level has an associated probability percentage:</p>
                        <ul class="mb-0">
                            <li><strong>Early Stages (Lead/Qualified):</strong> Lower probability (10-30%) - Initial interest</li>
                            <li><strong>Middle Stages (Proposal/Negotiation):</strong> Medium probability (40-70%) - Active discussions</li>
                            <li><strong>Late Stages (Closing):</strong> High probability (80-90%) - Near completion</li>
                        </ul>
                        <p class="mt-2 mb-0 small text-muted">
                            <i class="ri-information-line me-1"></i>
                            Probability helps forecast revenue and prioritize follow-up activities.
                        </p>
                    </div>
                </div>

                <div class="alert alert-warning mb-0">
                    <h6 class="alert-heading">
                        <i class="ri-alert-line me-2"></i>
                        Important Notes
                    </h6>
                    <ul class="mb-0 small">
                        <li>Once progressed, the prospect will appear in the Opportunities stage</li>
                        <li>You can still edit opportunity details after progression</li>
                        <li>Only progress prospects that have genuine potential</li>
                        <li>Keep status levels updated as the opportunity moves through the sales process</li>
                        <li>Regularly review and update probability percentages based on progress</li>
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

<style>
.bg-info-transparent {
    background-color: rgba(13, 202, 240, 0.1) !important;
}

.border-info-transparent {
    border-color: rgba(13, 202, 240, 0.2) !important;
}

.border-success-transparent {
    border-color: rgba(25, 135, 84, 0.2) !important;
}

.border-warning-transparent {
    border-color: rgba(255, 193, 7, 0.3) !important;
}

.border-primary-transparent {
    border-color: rgba(13, 110, 253, 0.2) !important;
}

/* Ensure documentation modals display correctly */
.modal.show .modal-dialog {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
}

.modal.show {
    display: block !important;
}

.modal.show .modal-content {
    display: block !important;
    visibility: visible !important;
}
</style>

