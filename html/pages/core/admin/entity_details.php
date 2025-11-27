<!-- Entity Details Page - Refactored -->
<?php
// Check admin access
if (!$isValidAdmin && !$isAdmin) {
    Alert::info("You need to be logged in as a valid administrator to access this page", true,
        array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    exit;
}

// Get entity ID from URL
$entityID = isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : null;

if (!$entityID) {
    Alert::danger("Invalid entity ID", true, array('text-center', 'font-18'));
    echo '<div class="text-center mt-3"><a href="' . $base . 'html/?s=core&ss=admin&p=home&tab=entities" class="btn btn-primary">Back to Entities</a></div>';
    exit;
}

$getString .= "&entityID={$entityID}";

// Fetch entity data
$entity = Data::entities_full(['entityID' => $entityID], true, $DBConn);

if (!$entity) {
    Alert::danger("Entity not found", true, array('text-center', 'font-18'));
    echo '<div class="text-center mt-3"><a href="' . $base . 'html/?s=core&ss=admin&p=home&tab=entities" class="btn btn-primary">Back to Entities</a></div>';
    exit;
}

// Fetch related data
$organisation = isset($entity->orgDataID) ? Admin::org_data(['orgDataID' => $entity->orgDataID], true, $DBConn) : null;
// var_dump($organisation);
// Get country ID - check for different possible property names
$countryID = null;
if (isset($entity->countryID)) {
    $countryID = $entity->countryID;
} elseif (isset($entity->entityCountry)) {
    $countryID = $entity->entityCountry;
}
$country = $countryID ? Data::countries(['countryID' => $countryID], true, $DBConn) : null;

$entityType = isset($entity->entityTypeID) ? Data::entity_types(['entityTypeID' => $entity->entityTypeID], true, $DBConn) : null;
$industrySector = isset($entity->industrySectorID) ? Data::industry_sectors(['industrySectorID' => $entity->industrySectorID], true, $DBConn) : null;

// Get parent entity if exists
$parentEntity = null;
if (isset($entity->entityParentID) && $entity->entityParentID > 0) {
    $parentEntity = Data::entities_full(['entityID' => $entity->entityParentID], true, $DBConn);
}

// Get child entities
$childEntities = Data::entities_full(['entityParentID' => $entityID, 'Suspended' => 'N'], false, $DBConn);
$childCount = $childEntities ? count($childEntities) : 0;

// Get employees/users associated with this entity
$entityEmployees = Employee::employees(['entityID' => $entityID, 'Suspended' => 'N'], false, $DBConn);
$employeeCount = $entityEmployees ? count($entityEmployees) : 0;

// Get departments in this entity
$departments = Data::departments(['entityID' => $entityID, 'Suspended' => 'N'], false, $DBConn);
$departmentCount = $departments ? count($departments) : 0;

// Get cost centers (placeholder)
$costCenterCount = 0;

// Get all units for this entity
$entityUnits = Data::units_full(['entityID' => $entityID, 'Suspended' => 'N'], false, $DBConn);
$unitsCount = $entityUnits ? count($entityUnits) : 0;

// Get business units for this entity
$businessUnits = Data::business_units_full(['entityID' => $entityID, 'Suspended' => 'N'], false, $DBConn);
$businessUnitsCount = $businessUnits ? count($businessUnits) : 0;

// Get pay grades for this entity
$payGrades = Data::pay_grades(['Suspended' => 'N'], false, $DBConn);
if ($payGrades) {
    // Filter by entity or organization
    $payGrades = array_filter($payGrades, function($grade) use ($entityID, $entity) {
        return ($grade->entityID == $entityID) || ($grade->entityID == null && $grade->orgDataID == ($entity->orgDataID ?? 0));
    });
}
$payGradesCount = $payGrades ? count($payGrades) : 0;

// Get unit types for dropdown
$unitTypes = Data::unit_types(['Suspended' => 'N'], false, $DBConn);

// Get business unit categories for dropdown
$businessUnitCategories = Data::business_unit_categories(['isActive' => 'Y', 'Suspended' => 'N'], false, $DBConn);

// Get job titles for employee dropdown
$jobTitles = Data::job_titles(['Suspended' => 'N'], false, $DBConn);

// Get name prefixes
$namePrefixes = Data::prefixes(['Suspended' => 'N'], false, $DBConn);

// Get employment statuses
$employmentStatuses = Data::employment_statuses(['Suspended' => 'N'], false, $DBConn);

// Get active tab from URL
$activeTab = isset($_GET['tab']) ? Utility::clean_string($_GET['tab']) : 'overview';
$getString .= "&tab={$activeTab}";

// Define tab configuration array
$tabs = array(
    'overview' => array(
        'label' => 'Overview',
        'icon' => 'fa-info-circle',
        'file' => 'includes/core/admin/tabs/entity_overview.php',
        'enabled' => true
    ),
    'units' => array(
        'label' => 'Units (Depts/Sections/Teams)',
        'icon' => 'fa-sitemap',
        'file' => 'includes/core/admin/tabs/entity_units.php',
        'badge' => $unitsCount,
        'enabled' => true
    ),
    'business_units' => array(
        'label' => 'Business Units',
        'icon' => 'fa-chart-line',
        'file' => 'includes/core/admin/tabs/entity_business_units.php',
        'badge' => $businessUnitsCount,
        'enabled' => true
    ),
    'org_structure' => array(
        'label' => 'Org Structure',
        'icon' => 'fa-project-diagram',
        'file' => 'includes/core/admin/tabs/entity_org_structure.php',
        'enabled' => true
    ),
    'reporting' => array(
        'label' => 'Reporting Structure',
        'icon' => 'fa-users-cog',
        'file' => 'includes/core/admin/tabs/reporting_structure.php',
        'enabled' => true
    ),
    'pay_grades' => array(
        'label' => 'Pay Grades',
        'icon' => 'fa-money-bill-wave',
        'file' => 'includes/core/admin/tabs/pay_grades.php',
        'badge' => $payGradesCount,
        'enabled' => true
    ),
    'employees' => array(
        'label' => 'Employees',
        'icon' => 'fa-users',
        'file' => 'includes/core/admin/tabs/entity_employees.php',
        'badge' => $employeeCount,
        'enabled' => true
    )
);

// Validate active tab exists, fallback to overview
if (!isset($tabs[$activeTab])) {
    $activeTab = 'overview';
}
?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-24 mb-0">
            <i class="fas fa-building text-primary me-2"></i>
            <?= htmlspecialchars($entity->entityName) ?>
        </h1>
        <p class="text-muted mb-0 mt-1">Entity Details & Management</p>
    </div>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="<?= $base ?>html/?s=core&ss=admin&p=home">Admin</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="<?= $base ?>html/?s=core&ss=admin&p=tenant_details&orgDataID=<?= isset($entity->orgDataID) ? $entity->orgDataID : '' ?>">
                        <?= $organisation ? htmlspecialchars($organisation->orgName) : 'Organization' ?>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?= htmlspecialchars($entity->entityName) ?>
                </li>
            </ol>
        </nav>
    </div>
</div>

<?php
// Display flash messages
if (isset($_SESSION['flash_message']) && !empty($_SESSION['flash_message'])) {
    $flashType = isset($_SESSION['flash_type']) ? $_SESSION['flash_type'] : 'info';
    $alertClass = 'alert-' . $flashType;
    $iconClass = $flashType === 'success' ? 'fa-check-circle' :
                 ($flashType === 'danger' ? 'fa-exclamation-circle' : 'fa-info-circle');

    echo '<div class="row mb-3">';
    echo '<div class="col-12">';
    echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
    echo '<i class="fas ' . $iconClass . ' me-2"></i>';
    echo '<strong>' . htmlspecialchars($_SESSION['flash_message']) . '</strong>';
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
    echo '</div>';
    echo '</div>';

    // Clear the flash message
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
}
?>

<?php
// Include Quick Actions Bar
include 'includes/core/admin/entity_details_quick_actions.php';
?>

<?php
// Include Statistics Cards
include 'includes/core/admin/entity_details_statistics.php';
?>

<!-- Tabs Navigation -->
<div class="row">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-body p-0">
                <ul class="nav nav-tabs tab-style-2 justify-content-start border-bottom" id="entityTabs" role="tablist">
                    <?php foreach ($tabs as $tabKey => $tabConfig): ?>
                        <?php if ($tabConfig['enabled']): ?>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link <?= $activeTab == $tabKey ? 'active' : '' ?>"
                                    href="?s=core&ss=admin&p=entity_details&entityID=<?= $entityID ?>&tab=<?= $tabKey ?>">
                                    <i class="fas <?= $tabConfig['icon'] ?> me-2"></i><?= $tabConfig['label'] ?>
                                    <?php if (isset($tabConfig['badge']) && $tabConfig['badge'] > 0): ?>
                                        <span class="badge bg-primary-transparent ms-2"><?= $tabConfig['badge'] ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Tab Content -->
<div class="tab-content mt-4" id="entityTabContent">
    <?php
    // Load active tab content from array configuration
    if (isset($tabs[$activeTab]) && $tabs[$activeTab]['enabled']) {
        $tabFile = $tabs[$activeTab]['file'];
        if (file_exists($tabFile)) {
            include $tabFile;
        } else {
            echo '<div class="alert alert-danger">';
            echo '<i class="fas fa-exclamation-triangle me-2"></i>';
            echo 'Tab file not found: ' . htmlspecialchars($tabFile);
            echo '</div>';
        }
    } else {
        echo '<div class="alert alert-warning">';
        echo '<i class="fas fa-exclamation-circle me-2"></i>';
        echo 'Invalid tab selected. Please choose a valid tab.';
        echo '</div>';
    }
    ?>
</div>
<!-- End Tab Content -->

<?php
// ============================================================================
// MODALS
// ============================================================================

// Include necessary modal data
$industrySectors = Data::industry_sectors(array("Suspended"=>'N'), false, $DBConn);
$countries = Data::countries(array(), false, $DBConn);
$entityTypes = Data::entity_types(array(), false, $DBConn);
$entities = isset($entity->orgDataID) ? Data::entities_full(array('Suspended'=> 'N', 'orgDataID' => $entity->orgDataID), false, $DBConn) : array();

// Modal for editing Entity
echo Utility::form_modal_header("manageEntity", "tax/admin/manage_entity.php", "Edit Organization Entity", array('modal-md', 'modal-dialog-centered', "modal-xl"), $base);
include "includes/scripts/global/modals/manage_entity.php";
echo Utility::form_modal_footer('Save Entity');

// Include modals
include 'includes/core/admin/modals/manage_unit_modal.php';
include 'includes/core/admin/modals/manage_business_unit_modal.php';
include 'includes/core/admin/modals/add_employee_modal.php';
include 'includes/core/admin/modals/assign_hr_manager_modal.php';

// Reset Email Modal
echo Utility::form_modal_header(
    "resetEmail",
    "global/admin/reset_email.php",
    "Reset Password",
    array("modal-lg", "modal-dialog-centered"),
    $base
);
include "includes/core/admin/users/modal/reset_email.php";
echo Utility::form_modal_footer("Send Reset Email", "send_reset_email", "btn btn-success btn-sm", true);

// Include scripts and styles
include 'includes/core/admin/entity_details_scripts.php';
?>

