<!-- Entity Details Page -->
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

// Get departments in this entity (if applicable)
$departments = Data::departments(['entityID' => $entityID, 'Suspended' => 'N'], false, $DBConn);
$departmentCount = $departments ? count($departments) : 0;

// Get cost centers in this entity (if applicable)
// TODO: Uncomment when Data::cost_centers() method is implemented
// $costCenters = Data::cost_centers(['entityID' => $entityID, 'Suspended' => 'N'], false, $DBConn);
// $costCenterCount = $costCenters ? count($costCenters) : 0;
$costCenterCount = 0; // Placeholder until cost centers functionality is implemented

// Get all units for this entity (Departments, Sections, Teams, etc.)
$entityUnits = Data::units_full(['entityID' => $entityID, 'Suspended' => 'N'], false, $DBConn);
$unitsCount = $entityUnits ? count($entityUnits) : 0;

// Get business units for this entity (Commercial units, projects, cost centers, etc.)
$businessUnits = Data::business_units_full(['entityID' => $entityID, 'Suspended' => 'N'], false, $DBConn);
$businessUnitsCount = $businessUnits ? count($businessUnits) : 0;

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

<!-- Quick Actions Bar -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card custom-card border-0 shadow-sm">
            <div class="card-body py-2">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <button type="button" class="btn btn-primary btn-sm btn-wave editEntity"
                        data-bs-toggle="modal"
                        data-bs-target="#manageEntity"
                        data-id="<?= $entityID ?>">
                        <i class="fas fa-edit me-2"></i>Edit Entity
                    </button>
                    <button type="button" class="btn btn-success btn-sm btn-wave" onclick="addDepartment(<?= $entityID ?>)">
                        <i class="fas fa-plus me-2"></i>Add Department
                    </button>
                    <button type="button" class="btn btn-info btn-sm btn-wave"
                        data-bs-toggle="modal"
                        data-bs-target="#addEmployeeModal"
                        onclick="addEmployee(<?= $entityID ?>)">
                        <i class="fas fa-user-plus me-2"></i>Add Employee
                    </button>
                    <button type="button" class="btn btn-<?= (isset($entity->Suspended) && $entity->Suspended == 'N') ? 'warning' : 'success' ?> btn-sm btn-wave"
                        onclick="toggleEntitySuspension(<?= $entityID ?>, '<?= isset($entity->Suspended) ? $entity->Suspended : 'N' ?>')">
                        <i class="fas fa-<?= (isset($entity->Suspended) && $entity->Suspended == 'N') ? 'ban' : 'check' ?> me-2"></i>
                        <?= (isset($entity->Suspended) && $entity->Suspended == 'N') ? 'Suspend' : 'Activate' ?>
                    </button>
                    <a href="<?= $base ?>html/?s=core&ss=admin&p=tenant_details&orgDataID=<?= isset($entity->orgDataID) ? $entity->orgDataID : '' ?>"
                        class="btn btn-light btn-sm btn-wave ms-auto">
                        <i class="fas fa-arrow-left me-2"></i>Back to Organization
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Employees</p>
                        <h3 class="mb-0 fw-semibold"><?= $employeeCount ?></h3>
                        <small class="text-muted fs-11">Active employees</small>
                    </div>
                    <div class="ms-2">
                        <span class="avatar avatar-md bg-primary-transparent">
                            <i class="fas fa-users fs-20"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Units</p>
                        <h3 class="mb-0 fw-semibold"><?= $unitsCount ?></h3>
                        <small class="text-muted fs-11">Dept/Sections/Teams</small>
                    </div>
                    <div class="ms-2">
                        <span class="avatar avatar-md bg-success-transparent">
                            <i class="fas fa-sitemap fs-20"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Business Units</p>
                        <h3 class="mb-0 fw-semibold"><?= $businessUnitsCount ?></h3>
                        <small class="text-muted fs-11">Cost/Profit centers</small>
                    </div>
                    <div class="ms-2">
                        <span class="avatar avatar-md bg-warning-transparent">
                            <i class="fas fa-chart-line fs-20"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Departments</p>
                        <h3 class="mb-0 fw-semibold"><?= $departmentCount ?></h3>
                        <small class="text-muted fs-11">Active departments</small>
                    </div>
                    <div class="ms-2">
                        <span class="avatar avatar-md bg-info-transparent">
                            <i class="fas fa-building fs-20"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Child Entities</p>
                        <h3 class="mb-0 fw-semibold"><?= $childCount ?></h3>
                        <small class="text-muted fs-11">Sub-entities</small>
                    </div>
                    <div class="ms-2">
                        <span class="avatar avatar-md bg-secondary-transparent">
                            <i class="fas fa-project-diagram fs-20"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Status</p>
                        <h3 class="mb-0 fw-semibold">
                            <span class="badge bg-<?= (isset($entity->Suspended) && $entity->Suspended == 'N') ? 'success' : 'danger' ?>-transparent fs-14">
                                <?= (isset($entity->Suspended) && $entity->Suspended == 'N') ? 'Active' : 'Suspended' ?>
                            </span>
                        </h3>
                        <small class="text-muted fs-11">Current status</small>
                    </div>
                    <div class="ms-2">
                        <span class="avatar avatar-md bg-danger-transparent">
                            <i class="fas fa-<?= (isset($entity->Suspended) && $entity->Suspended == 'N') ? 'check-circle' : 'ban' ?> fs-20"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<div class="row">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-body p-0">
                <ul class="nav nav-tabs tab-style-2 justify-content-start border-bottom" id="entityTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link <?= $activeTab == 'overview' ? 'active' : '' ?>"
                            href="?s=core&ss=admin&p=entity_details&entityID=<?= $entityID ?>&tab=overview">
                            <i class="fas fa-info-circle me-2"></i>Overview
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link <?= $activeTab == 'units' ? 'active' : '' ?>"
                            href="?s=core&ss=admin&p=entity_details&entityID=<?= $entityID ?>&tab=units">
                            <i class="fas fa-sitemap me-2"></i>Units (Depts/Sections/Teams)
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link <?= $activeTab == 'business_units' ? 'active' : '' ?>"
                            href="?s=core&ss=admin&p=entity_details&entityID=<?= $entityID ?>&tab=business_units">
                            <i class="fas fa-chart-line me-2"></i>Business Units
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link <?= $activeTab == 'org_structure' ? 'active' : '' ?>"
                            href="?s=core&ss=admin&p=entity_details&entityID=<?= $entityID ?>&tab=org_structure">
                            <i class="fas fa-project-diagram me-2"></i>Org Structure
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link <?= $activeTab == 'reporting' ? 'active' : '' ?>"
                            href="?s=core&ss=admin&p=entity_details&entityID=<?= $entityID ?>&tab=reporting">
                            <i class="fas fa-users-cog me-2"></i>Reporting Structure
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link <?= $activeTab == 'employees' ? 'active' : '' ?>"
                            href="?s=core&ss=admin&p=entity_details&entityID=<?= $entityID ?>&tab=employees">
                            <i class="fas fa-users me-2"></i>Employees
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Tab Content -->
<div class="tab-content mt-4" id="entityTabContent">

<?php if ($activeTab == 'overview'): ?>
<!-- Overview Tab -->
<div class="row">
    <!-- Left Column - Entity Details -->
    <div class="col-xl-8">
        <!-- Entity Information -->
        <div class="card custom-card mb-4">
            <div class="card-header">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Entity Information</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Entity Name</label>
                            <p class="mb-0 fs-15"><?= htmlspecialchars($entity->entityName) ?></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Entity Type</label>
                            <p class="mb-0 fs-15">
                                <i class="fas fa-tag text-primary me-2"></i>
                                <?= htmlspecialchars($entityType->entityTypeTitle ?? 'N/A') ?>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Registration Number</label>
                            <p class="mb-0 fs-15">
                                <i class="fas fa-id-card text-primary me-2"></i>
                                <?= htmlspecialchars($entity->registrationNumber ?? 'N/A') ?>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Entity PIN</label>
                            <p class="mb-0 fs-15">
                                <i class="fas fa-hashtag text-primary me-2"></i>
                                <?= htmlspecialchars($entity->entityPIN ?? 'N/A') ?>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Industry Sector</label>
                            <p class="mb-0 fs-15">
                                <i class="fas fa-industry text-primary me-2"></i>
                                <?= htmlspecialchars($industrySector->industryTitle ?? 'N/A') ?>
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Email</label>
                            <p class="mb-0 fs-15">
                                <i class="fas fa-envelope text-primary me-2"></i>
                                <a href="mailto:<?= htmlspecialchars($entity->entityEmail) ?>">
                                    <?= htmlspecialchars($entity->entityEmail ?? 'N/A') ?>
                                </a>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Phone Number</label>
                            <p class="mb-0 fs-15">
                                <i class="fas fa-phone text-primary me-2"></i>
                                <?= htmlspecialchars($entity->entityPhoneNumber ?? 'N/A') ?>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Location</label>
                            <p class="mb-0 fs-15">
                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                <?= htmlspecialchars($entity->entityCity ?? '') ?>
                                <?= $country ? ', ' . htmlspecialchars($country->countryName) : '' ?>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Parent Entity</label>
                            <p class="mb-0 fs-15">
                                <i class="fas fa-sitemap text-primary me-2"></i>
                                <?php if ($parentEntity): ?>
                                    <a href="<?= $base ?>html/?s=core&ss=admin&p=entity_details&entityID=<?= $parentEntity->entityID ?>">
                                        <?= htmlspecialchars($parentEntity->entityName) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">No parent entity</span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Organization</label>
                            <p class="mb-0 fs-15">
                                <i class="fas fa-building text-primary me-2"></i>
                                <?php if ($organisation && isset($entity->orgDataID)): ?>
                                    <a href="<?= $base ?>html/?s=core&ss=admin&p=tenant_details&orgDataID=<?= $entity->orgDataID ?>">
                                        <?= htmlspecialchars($organisation->orgName) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <?php if (isset($entity->entityDescription) && !empty($entity->entityDescription)): ?>
                    <div class="col-12">
                        <div class="mb-0">
                            <label class="form-label text-muted fw-semibold">Description</label>
                            <p class="mb-0 fs-15"><?= nl2br(htmlspecialchars($entity->entityDescription)) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Child Entities -->
        <?php if ($childEntities): ?>
        <div class="card custom-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-project-diagram me-2"></i>Child Entities (<?= $childCount ?>)</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Entity Name</th>
                                <th>Type</th>
                                <th>Location</th>
                                <th>Employees</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($childEntities as $child):
                                $childEmployees = Employee::employees(['entityID' => $child->entityID], false, $DBConn);
                                $childEmpCount = $childEmployees ? count($childEmployees) : 0;
                            ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($child->entityName) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($child->entityTypeTitle ?? 'N/A') ?></td>
                                    <td>
                                        <?= htmlspecialchars($child->entityCity) ?>,
                                        <?= htmlspecialchars($child->countryName ?? 'N/A') ?>
                                    </td>
                                    <td><?= $childEmpCount ?></td>
                                    <td class="text-center">
                                        <a href="<?= $base ?>html/?s=core&ss=admin&p=entity_details&entityID=<?= $child->entityID ?>"
                                            class="btn btn-sm btn-info-light" title="View Entity">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-sm btn-primary-light editEntity"
                                            data-bs-toggle="modal"
                                            data-bs-target="#manageEntity"
                                            data-id="<?= $child->entityID ?>"
                                            title="Edit Entity">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Departments -->
        <?php if ($departments): ?>
        <div class="card custom-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-sitemap me-2"></i>Departments (<?= $departmentCount ?>)</h5>
                </div>
                <button type="button" class="btn btn-sm btn-primary btn-wave" onclick="addDepartment(<?= $entityID ?>)">
                    <i class="fas fa-plus me-2"></i>Add Department
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Department Name</th>
                                <th>Code</th>
                                <th>Head</th>
                                <th>Employees</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($departments as $dept): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($dept->departmentName ?? 'N/A') ?></strong></td>
                                    <td><?= htmlspecialchars($dept->departmentCode ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($dept->headName ?? 'N/A') ?></td>
                                    <td><?= $dept->employeeCount ?? 0 ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-info-light" title="View Department">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-primary-light" title="Edit Department">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Right Column - Additional Info & Employees -->
    <div class="col-xl-4">
        <!-- Quick Stats -->
        <div class="card custom-card mb-4">
            <div class="card-header">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Quick Stats</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Created On</span>
                        <span><?= isset($entity->DateAdded) ? date('M d, Y', strtotime($entity->DateAdded)) : 'N/A' ?></span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Last Updated</span>
                        <span><?= isset($entity->LastUpdate) ? date('M d, Y', strtotime($entity->LastUpdate)) : 'N/A' ?></span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Entity ID</span>
                        <span><code>#<?= $entityID ?></code></span>
                    </div>

                    <?php if ($costCenterCount > 0): ?>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Cost Centers</span>
                        <span class="badge bg-primary"><?= $costCenterCount ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Employees -->
        <?php if ($entityEmployees): ?>
        <div class="card custom-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Employees</h5>
                </div>
                <button type="button" class="btn btn-sm btn-primary btn-wave" onclick="addEmployee(<?= $entityID ?>)">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <?php
                    $displayEmployees = array_slice($entityEmployees, 0, 10); // Show first 10
                    foreach ($displayEmployees as $emp): ?>
                        <div class="list-group-item px-0">
                            <div class="d-flex align-items-center">
                                <?php if (isset($emp->profile_image) && $emp->profile_image): ?>
                                    <img src="<?= "{$base}data/uploaded_files/{$emp->profile_image}" ?>"
                                        alt="Profile" class="avatar avatar-sm rounded-circle me-2">
                                <?php else: ?>
                                    <div class="avatar avatar-sm bg-primary-transparent rounded-circle me-2">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-fill">
                                    <h6 class="mb-0"><?= htmlspecialchars($emp->fullName ?? $emp->EmployeeName ?? 'N/A') ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars($emp->jobTitle ?? $emp->position ?? 'Employee') ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($employeeCount > 10): ?>
                    <div class="text-center mt-3">
                        <small class="text-muted">Showing 10 of <?= $employeeCount ?> employees</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Additional Actions -->
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Actions</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="viewAllEmployees(<?= $entityID ?>)">
                        <i class="fas fa-users me-2"></i>View All Employees
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="manageDepartments(<?= $entityID ?>)">
                        <i class="fas fa-sitemap me-2"></i>Manage Departments
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm" onclick="generateEntityReport(<?= $entityID ?>)">
                        <i class="fas fa-file-pdf me-2"></i>Generate Report
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="exportEntityData(<?= $entityID ?>)">
                        <i class="fas fa-download me-2"></i>Export Data
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; // End Overview Tab ?>

<?php if ($activeTab == 'units'): ?>
<!-- Units Tab (Departments, Sections, Teams) -->
<div class="row">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-sitemap me-2"></i>Entity Units - Departments, Sections & Teams (<?= $unitsCount ?>)</h5>
                </div>
                <button type="button" class="btn btn-primary btn-sm btn-wave"
                    data-bs-toggle="modal"
                    data-bs-target="#manageUnitModal"
                    onclick="addUnitForEntity(<?= $entityID ?>)">
                    <i class="fas fa-plus me-2"></i>Add Unit
                </button>
            </div>
            <div class="card-body">
                <?php if ($entityUnits):

                  // var_dump($entityUnits);?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Unit Name</th>
                                    <th>Type</th>
                                    <th>Code</th>
                                    <th>Head of Unit</th>
                                    <th>Parent Unit</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($entityUnits as $unit): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($unit->unitName ?? 'N/A') ?></strong></td>
                                        <td>
                                            <span class="badge bg-primary-transparent">
                                                <?= htmlspecialchars($unit->unitTypeName ?? 'N/A') ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($unit->unitCode ?? 'N/A') ?></td>
                                        <td>
                                            <?php if (isset($unit->FirstName)): ?>
                                                <?= htmlspecialchars($unit->FirstName . ' ' . ($unit->Surname ?? '')) ?>
                                            <?php else: ?>
                                                <span class="text-muted">Not assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= isset($unit->parentUnitID) && $unit->parentUnitID > 0 ? 'Sub-unit' : 'Main Unit' ?>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-info-light" title="View Unit">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-primary-light editUnit" title="Edit Unit"
                                                data-bs-toggle="modal"
                                                data-bs-target="#manageUnitModal"
                                                data-unit-id="<?= $unit->unitID ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="avatar avatar-lg bg-secondary-transparent mx-auto mb-3">
                            <i class="fas fa-sitemap fs-24"></i>
                        </div>
                        <h6 class="mb-2">No Units Created Yet</h6>
                        <p class="text-muted mb-3">Create departments, sections, and teams for this entity.</p>
                        <button type="button" class="btn btn-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#manageUnitModal"
                            onclick="addUnitForEntity(<?= $entityID ?>)">
                            <i class="fas fa-plus me-2"></i>Add First Unit
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; // End Units Tab ?>

<?php if ($activeTab == 'business_units'): ?>
<!-- Business Units Tab (Commercial Units/Cost Centers/Product Lines) -->
<div class="row">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Business Units - Cost Centers & Product Lines (<?= $businessUnitsCount ?>)</h5>
                </div>
                <button type="button" class="btn btn-primary btn-sm btn-wave"
                    data-bs-toggle="modal"
                    data-bs-target="#manageBusinessUnitModal"
                    onclick="addBusinessUnitForEntity(<?= $entityID ?>)">
                    <i class="fas fa-plus me-2"></i>Add Business Unit
                </button>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Business Units</strong> are income-generating or cost-tracking units such as Projects, Reporting Units, Tax Units, Commercial Units, or Product Lines.
                </div>
                <?php if ($businessUnits):

                  // var_dump($businessUnits);
                  ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Business Unit Name</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($businessUnits as $bu): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($bu->businessUnitName ?? 'N/A') ?></strong></td>
                                        <td>
                                            <span class="badge bg-warning-transparent">
                                                <?= htmlspecialchars($bu->categoryName ?? 'N/A') ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($bu->businessUnitDescription ?? 'N/A') ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-info-light" title="View Business Unit">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-primary-light editBusinessUnit" title="Edit Business Unit"
                                                data-bs-toggle="modal"
                                                data-bs-target="#manageBusinessUnitModal"
                                                data-business-unit-id="<?= $bu->businessUnitID ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="avatar avatar-lg bg-warning-transparent mx-auto mb-3">
                            <i class="fas fa-chart-line fs-24"></i>
                        </div>
                        <h6 class="mb-2">No Business Units Created Yet</h6>
                        <p class="text-muted mb-3">Create business units for tracking projects, cost centers, or product lines.</p>
                        <button type="button" class="btn btn-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#manageBusinessUnitModal"
                            onclick="addBusinessUnitForEntity(<?= $entityID ?>)">
                            <i class="fas fa-plus me-2"></i>Add First Business Unit
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; // End Business Units Tab ?>

<?php if ($activeTab == 'org_structure'): ?>
<!-- Organization Structure Tab -->
<div class="row">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-project-diagram me-2"></i>Organizational Structure</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-primary mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    This displays the hierarchical structure of units within the entity.
                </div>

                <?php if ($entityUnits): ?>
                    <!-- Organizational Chart View -->
                    <div class="org-chart-container">
                        <?php
                        // Build hierarchical structure
                        if (!function_exists('buildUnitHierarchy')) {
                            function buildUnitHierarchy($units, $parentId = 0) {
                                $hierarchy = [];
                                foreach ($units as $unit) {
                                    if (($unit->parentUnitID ?? 0) == $parentId) {
                                        $children = buildUnitHierarchy($units, $unit->unitID);
                                        if ($children) {
                                            $unit->children = $children;
                                        }
                                        $hierarchy[] = $unit;
                                    }
                                }
                                return $hierarchy;
                            }
                        }

                        if (!function_exists('displayUnitHierarchy')) {
                            function displayUnitHierarchy($hierarchy, $level = 0) {
                                foreach ($hierarchy as $unit) {
                                    $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
                                    echo '<div class="unit-item p-3 mb-2 border rounded" style="margin-left: ' . ($level * 30) . 'px;">';
                                    echo '<div class="d-flex align-items-center justify-content-between">';
                                    echo '<div>';
                                    echo $indent;
                                    if ($level > 0) echo '<i class="fas fa-level-up-alt fa-rotate-90 me-2 text-muted"></i>';
                                    echo '<strong>' . htmlspecialchars($unit->unitName) . '</strong>';
                                    echo ' <span class="badge bg-primary-transparent ms-2">' . htmlspecialchars($unit->unitTypeName ?? '') . '</span>';
                                    if (isset($unit->FirstName)) {
                                        echo '<br>' . $indent . '<small class="text-muted ms-4">Head: ' . htmlspecialchars($unit->FirstName . ' ' . ($unit->Surname ?? '')) . '</small>';
                                    }
                                    echo '</div>';
                                    echo '<div>';
                                    echo '<button class="btn btn-sm btn-info-light me-1"><i class="fas fa-eye"></i></button>';
                                    echo '<button class="btn btn-sm btn-primary-light"><i class="fas fa-edit"></i></button>';
                                    echo '</div>';
                                    echo '</div>';
                                    echo '</div>';

                                    if (isset($unit->children)) {
                                        displayUnitHierarchy($unit->children, $level + 1);
                                    }
                                }
                            }
                        }

                        $hierarchy = buildUnitHierarchy($entityUnits);
                        displayUnitHierarchy($hierarchy);
                        ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="avatar avatar-lg bg-secondary-transparent mx-auto mb-3">
                            <i class="fas fa-project-diagram fs-24"></i>
                        </div>
                        <h6 class="mb-2">No Organizational Structure</h6>
                        <p class="text-muted mb-3">Create units to build your organizational structure.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; // End Org Structure Tab ?>

<?php if ($activeTab == 'reporting'): ?>
<!-- Reporting Structure Tab -->
<?php include 'includes/core/admin/tabs/reporting_structure.php'; ?>
<?php endif; // End Reporting Tab ?>

<?php if ($activeTab == 'employees'): ?>
<!-- Employees Tab -->
<div class="row">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Entity Employees (<?= $employeeCount ?>)</h5>
                </div>
                <button type="button" class="btn btn-primary btn-sm btn-wave"
                    data-bs-toggle="modal"
                    data-bs-target="#addEmployeeModal"
                    onclick="addEmployee(<?= $entityID ?>)">
                    <i class="fas fa-user-plus me-2"></i>Add Employee
                </button>
            </div>
            <div class="card-body">
                <?php if ($entityEmployees): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Payroll Number</th>
                                    <th>Employee</th>
                                    <th>Job Title</th>
                                    <th>Department</th>
                                    <th>Supervisor</th>

                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($entityEmployees as $emp):

                                 //get the department from unit assigned to the employee
                                 $unitsAssigned = Employee::user_unit_assignments(['userID' => $emp->ID], false, $DBConn);
                                 // var_dump($unitsAssigned);
                                 /**
                                  * department unit id is 1 in the unitsAssigned array
                                  * get the unit name from the unit id
                                  */
                                 if($unitsAssigned) {
                                     foreach($unitsAssigned as $unit) {
                                         if($unit->unitTypeID == 1) {
                                             $emp->departmentName = $unit->unitName;
                                         }
                                     }
                                     // var_dump($employee->departmentName);
                                 } else {
                                     $emp->departmentName = 'Unknown';
                                 }

                                  // var_dump($emp);?>
                                  <tr>
                                        <td><?= htmlspecialchars($emp->payrollNo ?? 'N/A') ?></td>
                                        <td>
                                          <div class="d-flex align-items-center">
                                            <?php if (isset($emp->profile_image) && $emp->profile_image): ?>
                                              <img src="<?= "{$base}data/uploaded_files/{$emp->profile_image}" ?>"
                                              alt="Profile" class="avatar avatar-sm rounded-circle me-2">
                                              <?php else: ?>
                                                <div class="avatar avatar-sm bg-primary-transparent rounded-circle me-2">
                                                  <i class="fas fa-user"></i>
                                                </div>
                                                <?php endif; ?>
                                                <div class="d-block">
                                                  <h6 class="mb-0 fw-bold font-12"><?= htmlspecialchars($emp->fullName ?? $emp->employeeName ?? 'N/A') ?></h6>
                                                  <span class="text-muted small ms-2">
                                                    <?= htmlspecialchars($emp->Email ?? '') ?>
                                                  </span>
                                                </div>
                                              </div>
                                            </td>
                                            <td><?= htmlspecialchars($emp->jobTitle ?? $emp->jobTitle ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($emp->departmentName ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($emp->supervisorName ?? 'N/A') ?></td>
                                        <td class="text-center">
                                            <a href="<?= "{$base}html/?s=user&p=profile&uid={$emp->ID}" ?>" class="btn btn-sm btn-info-light" title="View Employee">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= "{$base}html/?s=user&p=profile&uid={$emp->ID}" ?>" class="btn btn-sm btn-primary-light" title="Edit Employee">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="avatar avatar-lg bg-primary-transparent mx-auto mb-3">
                            <i class="fas fa-users fs-24"></i>
                        </div>
                        <h6 class="mb-2">No Employees Assigned</h6>
                        <p class="text-muted mb-3">Add employees to this entity.</p>
                        <button type="button" class="btn btn-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#addEmployeeModal"
                            onclick="addEmployee(<?= $entityID ?>)">
                            <i class="fas fa-user-plus me-2"></i>Add First Employee
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; // End Employees Tab ?>

</div>
<!-- End Tab Content -->

<?php
// Include necessary modals and data
$industrySectors = Data::industry_sectors(array("Suspended"=>'N'), false, $DBConn);
$countries = Data::countries(array(), false, $DBConn);
$entityTypes = Data::entity_types(array(), false, $DBConn);
$entities = isset($entity->orgDataID) ? Data::entities_full(array('Suspended'=> 'N', 'orgDataID' => $entity->orgDataID), false, $DBConn) : array();

// Modal for editing Entity
echo Utility::form_modal_header("manageEntity", "tax/admin/manage_entity.php", "Edit Organization Entity", array('modal-md', 'modal-dialog-centered', "modal-xl"), $base);
include "includes/scripts/global/modals/manage_entity.php";
echo Utility::form_modal_footer('Save Entity');

// Modal for managing Units (Departments, Sections, Teams)
echo Utility::form_modal_header("manageUnitModal", "organisation/manage_entity_unit.php", "Manage Entity Unit", array('modal-md', 'modal-dialog-centered', "modal-lg"), $base);
?>
<div id="unit_form" class="manageUnits">
    <div class="row g-3">
        <input type="hidden" class="form-control form-control-sm" id="unitID" name="unitID" value="">
        <input type="hidden" class="form-control form-control-sm" id="unit_orgDataID" name="orgDataID" value="<?= isset($entity->orgDataID) ? $entity->orgDataID : '' ?>">
        <input type="hidden" class="form-control form-control-sm" id="unit_entityID" name="entityID" value="<?= $entityID ?>">

        <div class="col-md-6">
            <label for="unitName" class="form-label mb-0">Unit Name</label>
            <input type="text" id="unitName" name="unitName" class="form-control form-control-sm" placeholder="Unit Name" required>
        </div>

        <div class="col-md-6">
            <label for="unitCode" class="form-label mb-0">Unit Code</label>
            <input type="text" id="unitCode" name="unitCode" class="form-control form-control-sm" placeholder="Unit Code">
        </div>

        <div class="col-md-6">
            <label for="unitTypeID" class="form-label mb-0">Unit Type</label>
            <select id="unitTypeID" name="unitTypeID" class="form-select form-control-sm" required>
                <option value="">Select Unit Type</option>
                <?php if ($unitTypes): foreach ($unitTypes as $ut): ?>
                    <option value="<?= $ut->unitTypeID ?>"><?= htmlspecialchars($ut->unitTypeName) ?></option>
                <?php endforeach; endif; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label for="parentUnitID" class="form-label mb-0">Parent Unit</label>
            <select id="parentUnitID" name="parentUnitID" class="form-select form-control-sm">
                <option value="0">None (Main Unit)</option>
                <?php if ($entityUnits): foreach ($entityUnits as $u): ?>
                    <option value="<?= $u->unitID ?>"><?= htmlspecialchars($u->unitName) ?></option>
                <?php endforeach; endif; ?>
            </select>
        </div>

        <div class="col-md-12">
            <label for="unitDescription" class="form-label mb-0">Description</label>
            <textarea id="unitDescription" name="unitDescription" class="form-control form-control-sm" rows="3" placeholder="Unit Description"></textarea>
        </div>
    </div>
</div>
<?php
echo Utility::form_modal_footer('Save Unit');

// Modal for managing Business Units
echo Utility::form_modal_header("manageBusinessUnitModal", "organisation/manage_business_unit.php", "Manage Business Unit", array('modal-md', 'modal-dialog-centered', "modal-lg"), $base);
?>
<div id="business_unit_form" class="managebusinessUnits">
    <div class="row g-3">
        <input type="hidden" id="businessUnitID" name="businessUnitID">
        <input type="hidden" id="bu_orgDataID" name="orgDataID" value="<?= isset($entity->orgDataID) ? $entity->orgDataID : '' ?>">
        <input type="hidden" id="bu_entityID" name="entityID" value="<?= $entityID ?>">

        <div class="col-md-12">
            <label for="businessUnitName" class="form-label mb-0">Business Unit Name</label>
            <input type="text" id="businessUnitName" name="businessUnitName" class="form-control form-control-sm" placeholder="e.g., Tax Advisory, Projects, Commercial" required>
            <small class="text-muted">Examples: Projects, Reporting, Tax, Tija, Product Lines, Cost Centers</small>
        </div>

        <div class="col-md-6">
            <label for="bu_unitTypeID" class="form-label mb-0">Business Unit Type</label>
            <select id="bu_unitTypeID" name="unitTypeID" class="form-select form-control-sm" required>
                <option value="">Select Type</option>
                <?php if ($unitTypes): foreach ($unitTypes as $ut): ?>
                    <option value="<?= $ut->unitTypeID ?>"><?= htmlspecialchars($ut->unitTypeName) ?></option>
                <?php endforeach; endif; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label mb-0">Unit Category</label>
            <select class="form-select form-control-sm" id="categoryID" name="categoryID">
                <option value="">Select Category</option>
                <?php if ($businessUnitCategories): foreach ($businessUnitCategories as $cat): ?>
                    <option value="<?= $cat->categoryID ?>">
                        <?= htmlspecialchars($cat->categoryName) ?>
                    </option>
                <?php endforeach; endif; ?>
            </select>
        </div>

        <div class="col-md-12">
            <label for="businessUnitDescription" class="form-label mb-0">Description</label>
            <textarea id="businessUnitDescription" name="businessUnitDescription" class="form-control form-control-sm" rows="3" placeholder="Describe the purpose and scope of this business unit"></textarea>
        </div>
    </div>
</div>
<?php
echo Utility::form_modal_footer('Save Business Unit');

// Modal for adding Employee
echo Utility::form_modal_header("addEmployeeModal", "global/admin/manage_employee_from_entity.php", "Add Employee to Entity", array('modal-md', 'modal-dialog-centered', "modal-xl"), $base);
?>
<div id="employee_form" class="manageEmployee">
    <!-- Progress Steps -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="step-item active" id="step1Indicator">
                <div class="step-circle">1</div>
                <small>Personal Info</small>
            </div>
            <div class="step-line"></div>
            <div class="step-item" id="step2Indicator">
                <div class="step-circle">2</div>
                <small>Employment</small>
            </div>
            <div class="step-line"></div>
            <div class="step-item" id="step3Indicator">
                <div class="step-circle">3</div>
                <small>Payroll</small>
            </div>
        </div>
    </div>

    <!-- Hidden Fields -->
    <input type="hidden" id="emp_orgDataID" name="orgDataID" value="<?= isset($entity->orgDataID) ? $entity->orgDataID : '' ?>">
    <input type="hidden" id="emp_entityID" name="entityID" value="<?= $entityID ?>">
    <input type="hidden" id="employeeID" name="ID" value="">

    <!-- Step 1: Personal Information -->
    <div class="step-content" id="step1Content">
        <h6 class="mb-3 text-primary"><i class="fas fa-user me-2"></i>Personal Information</h6>
        <div class="row g-3">
            <div class="col-md-2">
                <label for="emp_prefixID" class="form-label mb-0">Prefix</label>
                <select id="emp_prefixID" name="prefixID" class="form-select form-control-sm">
                    <option value="">Select</option>
                    <?php if ($namePrefixes): foreach ($namePrefixes as $prefix): ?>
                        <option value="<?= $prefix->prefixID ?>"><?= htmlspecialchars($prefix->prefixName) ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>

            <div class="col-md-5">
                <label for="emp_FirstName" class="form-label mb-0">First Name <span class="text-danger">*</span></label>
                <input type="text" id="emp_FirstName" name="FirstName" class="form-control form-control-sm" placeholder="First Name" required>
            </div>

            <div class="col-md-5">
                <label for="emp_Surname" class="form-label mb-0">Surname <span class="text-danger">*</span></label>
                <input type="text" id="emp_Surname" name="Surname" class="form-control form-control-sm" placeholder="Surname" required>
            </div>

            <div class="col-md-6">
                <label for="emp_OtherNames" class="form-label mb-0">Other Names</label>
                <input type="text" id="emp_OtherNames" name="OtherNames" class="form-control form-control-sm" placeholder="Other Names">
            </div>

            <div class="col-md-6">
                <label for="emp_userInitials" class="form-label mb-0">Initials</label>
                <input type="text" id="emp_userInitials" name="userInitials" class="form-control form-control-sm" placeholder="e.g., J.D.">
            </div>

            <div class="col-md-6">
                <label for="emp_Email" class="form-label mb-0">Email <span class="text-danger">*</span></label>
                <input type="email" id="emp_Email" name="Email" class="form-control form-control-sm" placeholder="email@example.com" required>
            </div>

            <div class="col-md-6">
                <label for="emp_phoneNumber" class="form-label mb-0">Phone Number <span class="text-danger">*</span></label>
                <input type="tel" id="emp_phoneNumber" name="phoneNumber" class="form-control form-control-sm" placeholder="+254 712 345 678" required>
            </div>

            <div class="col-md-4">
                <label for="emp_gender" class="form-label mb-0">Gender <span class="text-danger">*</span></label>
                <select id="emp_gender" name="gender" class="form-select form-control-sm" required>
                    <option value="">Select Gender</option>
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                    <option value="O">Other</option>
                </select>
            </div>

            <div class="col-md-4">
                <label for="emp_dateOfBirth" class="form-label mb-0">Date of Birth</label>
                <input type="text" id="emp_dateOfBirth" name="dateOfBirth" class="form-control form-control-sm" placeholder="Select Date of Birth" readonly>
            </div>

            <div class="col-md-4">
                <label for="emp_nationalID" class="form-label mb-0">National ID</label>
                <input type="text" id="emp_nationalID" name="nationalID" class="form-control form-control-sm" placeholder="National ID Number">
            </div>
        </div>

        <div class="mt-3 d-flex justify-content-end">
            <button type="button" class="btn btn-primary btn-sm" onclick="goToStep(2)">
                Next <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </div>
    </div>

    <!-- Step 2: Employment Details -->
    <div class="step-content" id="step2Content" style="display: none;">
        <h6 class="mb-3 text-primary"><i class="fas fa-briefcase me-2"></i>Employment Details</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="emp_jobTitleID" class="form-label mb-0">Job Title <span class="text-danger">*</span></label>
                <select id="emp_jobTitleID" name="jobTitleID" class="form-select form-control-sm" required>
                    <option value="">Select Job Title</option>
                    <?php if ($jobTitles): foreach ($jobTitles as $jt): ?>
                        <option value="<?= $jt->jobTitleID ?>"><?= htmlspecialchars($jt->jobTitle) ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label for="emp_employeeTypeID" class="form-label mb-0">Employment Status <span class="text-danger">*</span></label>
                <select id="emp_employeeTypeID" name="employeeTypeID" class="form-select form-control-sm" required>
                    <option value="">Select Status</option>
                    <?php if ($employmentStatuses): foreach ($employmentStatuses as $es): ?>
                        <option value="<?= $es->employmentStatusID ?>"><?= htmlspecialchars($es->employmentStatusTitle) ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label for="emp_dateOfEmployment" class="form-label mb-0">Employment Start Date <span class="text-danger">*</span></label>
                <input type="text" id="emp_dateOfEmployment" name="dateOfEmployment" class="form-control form-control-sm" placeholder="Select Employment Start Date" required readonly>
            </div>

            <div class="col-md-6">
                <label for="emp_payrollNumber" class="form-label mb-0">Payroll Number</label>
                <input type="text" id="emp_payrollNumber" name="payrollNumber" class="form-control form-control-sm" placeholder="Employee Number">
            </div>

            <div class="col-md-6">
                <label for="emp_supervisorID" class="form-label mb-0">Supervisor</label>
                <select id="emp_supervisorID" name="supervisorID" class="form-select form-control-sm">
                    <option value="0">
                        <i class="fas fa-crown"></i> No Supervisor (Reports to Board/External)
                    </option>
                    <option value="" disabled>──────────────────</option>
                    <?php if ($entityEmployees): foreach ($entityEmployees as $emp): ?>
                        <option value="<?= $emp->ID ?>"><?= htmlspecialchars($emp->fullName ?? $emp->EmployeeName ?? 'N/A') ?></option>
                    <?php endforeach; endif; ?>
                </select>
                <small class="form-text text-muted d-block mt-1">
                    <i class="fas fa-info-circle me-1"></i>
                    Select "No Supervisor" for top-level positions like CEO
                </small>
            </div>

            <div class="col-md-6">
                <label for="emp_dailyWorkHours" class="form-label mb-0">Daily Work Hours</label>
                <input type="number" id="emp_dailyWorkHours" name="dailyWorkHours" class="form-control form-control-sm" placeholder="8" min="1" max="24" step="0.5">
            </div>
        </div>

        <div class="mt-3 d-flex justify-content-between">
            <button type="button" class="btn btn-secondary btn-sm" onclick="goToStep(1)">
                <i class="fas fa-arrow-left me-2"></i> Previous
            </button>
            <button type="button" class="btn btn-primary btn-sm" onclick="goToStep(3)">
                Next <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </div>
    </div>

    <!-- Step 3: Payroll & Benefits -->
    <div class="step-content" id="step3Content" style="display: none;">
        <h6 class="mb-3 text-primary"><i class="fas fa-money-bill-wave me-2"></i>Payroll & Benefits</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="emp_basicSalary" class="form-label mb-0">Basic Salary</label>
                <input type="number" id="emp_basicSalary" name="basicSalary" class="form-control form-control-sm" placeholder="0.00" min="0" step="0.01">
            </div>

            <div class="col-md-6">
                <label for="emp_pin" class="form-label mb-0">KRA PIN</label>
                <input type="text" id="emp_pin" name="pin" class="form-control form-control-sm" placeholder="KRA PIN">
            </div>

            <div class="col-md-4">
                <label for="emp_nhifNumber" class="form-label mb-0">NHIF Number</label>
                <input type="text" id="emp_nhifNumber" name="nhifNumber" class="form-control form-control-sm" placeholder="NHIF Number">
            </div>

            <div class="col-md-4">
                <label for="emp_nssfNumber" class="form-label mb-0">NSSF Number</label>
                <input type="text" id="emp_nssfNumber" name="nssfNumber" class="form-control form-control-sm" placeholder="NSSF Number">
            </div>

            <div class="col-md-4">
                <label for="emp_costPerHour" class="form-label mb-0">Cost Per Hour</label>
                <input type="number" id="emp_costPerHour" name="costPerHour" class="form-control form-control-sm" placeholder="0.00" min="0" step="0.01">
            </div>

            <div class="col-md-12">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="emp_overtimeAllowed" name="overtimeAllowed" value="Y">
                    <label class="form-check-label" for="emp_overtimeAllowed">Overtime Allowed</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="emp_bonusEligible" name="bonusEligible" value="Y">
                    <label class="form-check-label" for="emp_bonusEligible">Bonus Eligible</label>
                </div>
            </div>
        </div>

        <div class="mt-3 d-flex justify-content-between">
            <button type="button" class="btn btn-secondary btn-sm" onclick="goToStep(2)">
                <i class="fas fa-arrow-left me-2"></i> Previous
            </button>
            <div>
                <button type="button" class="btn btn-outline-secondary btn-sm me-2" onclick="skipPayroll()">
                    Skip Payroll
                </button>
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="fas fa-save me-2"></i> Save Employee
                </button>
            </div>
        </div>
    </div>
</div>
<?php
echo Utility::form_modal_footer('Save Employee', 'submitEmployee', 'btn btn-success btn-sm d-none');
?>

<style>
/* Employee Modal Steps */
.step-item {
    text-align: center;
    flex: 1;
    position: relative;
}

.step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin: 0 auto 5px;
    transition: all 0.3s;
}

.step-item.active .step-circle {
    background-color: #0d6efd;
    color: white;
}

.step-item.completed .step-circle {
    background-color: #28a745;
    color: white;
}

.step-item small {
    display: block;
    font-size: 11px;
    color: #6c757d;
}

.step-line {
    flex: 1;
    height: 2px;
    background-color: #e9ecef;
    margin: 20px 10px 0;
}

.step-item.active ~ .step-line,
.step-item.completed ~ .step-line {
    background-color: #0d6efd;
}

.step-content {
    min-height: 350px;
}

.card-body .fs-15 {
    font-size: 15px;
}

.bg-light.rounded {
    border: 1px solid #e9ecef;
}

.list-group-item {
    border-left: 0;
    border-right: 0;
}

.list-group-item:first-child {
    border-top: 0;
}

.list-group-item:last-child {
    border-bottom: 0;
}
</style>

<script>
function editEntity(entityID) {
    // Fetch entity data and populate edit modal
    const modal = document.querySelector('#manageEntity');

    if (!modal) {
        console.error('Entity modal not found');
        return;
    }

    // Update modal title
    const modalTitle = modal.querySelector('.modal-title');
    if (modalTitle) {
        modalTitle.textContent = 'Edit Organization Entity';
    }

    // Fetch entity data via AJAX
    fetch('<?= $base ?>php/scripts/global/admin/get_entity.php?entityID=' + entityID)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch entity data');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.entity) {
                const entity = data.entity;
                console.log('Entity data loaded:', entity);

                // Populate form fields
                const fields = {
                    'entityID': entity.entityID,
                    'orgDataID': entity.orgDataID,
                    'entityName': entity.entityName,
                    'entityTypeID': entity.entityTypeID,
                    'registrationNumber': entity.registrationNumber,
                    'entityPIN': entity.entityPIN,
                    'entityDescription': entity.entityDescription,
                    'entityCity': entity.entityCity,
                    'entityCountry': entity.countryID,
                    'entityPhoneNumber': entity.entityPhoneNumber,
                    'entityEmail': entity.entityEmail,
                    'entityParentID': entity.entityParentID,
                    'industrySectorID': entity.industrySectorID
                };

                // Fill in each field
                for (const [fieldName, value] of Object.entries(fields)) {
                    const input = modal.querySelector(`[name="${fieldName}"], #${fieldName}`);
                    if (input) {
                        if (input.tagName === 'SELECT') {
                            input.value = value || '';
                            // Trigger change event for dependent fields
                            input.dispatchEvent(new Event('change', { bubbles: true }));
                        } else if (input.type === 'checkbox') {
                            input.checked = value === 'Y';
                        } else if (input.type === 'textarea' || input.tagName === 'TEXTAREA') {
                            input.value = value || '';
                        } else {
                            input.value = value || '';
                        }
                    }
                }

                // Handle any additional checkboxes or special fields
                const suspendedCheckbox = modal.querySelector('#entitySuspended, [name="Suspended"]');
                if (suspendedCheckbox) {
                    suspendedCheckbox.checked = entity.Suspended === 'Y';
                }

            } else {
                alert('Error: ' + (data.message || 'Failed to load entity data'));
                console.error('Entity data error:', data);
            }
        })
        .catch(error => {
            console.error('Error loading entity:', error);
            alert('Error loading entity data. Please try again.');
        });
}

// Add event listener for edit entity buttons
document.addEventListener('DOMContentLoaded', function() {
    // Event delegation for dynamically loaded edit buttons
    document.addEventListener('click', function(e) {
        // Check if clicked element is the edit entity button or its child icon
        const editBtn = e.target.closest('.editEntity');
        if (editBtn) {
            e.preventDefault();
            const entityID = editBtn.getAttribute('data-id');
            if (entityID) {
                editEntity(entityID);
            } else {
                console.error('Entity ID not found on edit button');
            }
        }
    });
});

function toggleEntitySuspension(entityID, currentStatus) {
    const action = currentStatus === 'N' ? 'suspend' : 'activate';
    if (confirm('Are you sure you want to ' + action + ' this entity?')) {
        // TODO: Implement AJAX call to update status
        alert(action + ' entity ID: ' + entityID);
    }
}

function addDepartment(entityID) {
    alert('Add department for entity ID: ' + entityID);
    // TODO: Implement department modal
}

function addEmployee(entityID) {
    const modal = document.querySelector('#addEmployeeModal');
    if (modal) {
        // Reset form
        const form = modal.querySelector('form');
        if (form) form.reset();

        // Set entity ID
        const entityInput = modal.querySelector('#emp_entityID');
        if (entityInput) {
            entityInput.value = entityID;
        }

        // Reset to step 1
        goToStep(1);

        // Initialize Flatpickr for date fields
        initializeEmployeeDatePickers();

        // Update modal title
        const modalTitle = modal.querySelector('.modal-title');
        if (modalTitle) {
            modalTitle.textContent = 'Add Employee to Entity';
        }
    }
}

// Initialize Flatpickr for all date fields in employee modal
function initializeEmployeeDatePickers() {
    // Date of Birth - with age restriction (18-80 years old typically)
    const dobElement = document.getElementById('emp_dateOfBirth');
    if (dobElement && !dobElement._flatpickr) {
        flatpickr('#emp_dateOfBirth', {
            dateFormat: 'Y-m-d',
            maxDate: new Date(new Date().setFullYear(new Date().getFullYear() - 18)), // At least 18 years old
            minDate: new Date(new Date().setFullYear(new Date().getFullYear() - 80)), // Not more than 80 years old
            defaultDate: null,
            allowInput: true,
            altInput: true,
            altFormat: 'F j, Y',
            clickOpens: true,
            monthSelectorType: 'dropdown',
            yearSelectorType: 'dropdown'
        });
    }

    // Employment Start Date - with reasonable date range
    const employmentDateElement = document.getElementById('emp_dateOfEmployment');
    if (employmentDateElement && !employmentDateElement._flatpickr) {
        flatpickr('#emp_dateOfEmployment', {
            dateFormat: 'Y-m-d',
            maxDate: new Date(new Date().setMonth(new Date().getMonth() + 6)), // Up to 6 months in future
            minDate: new Date(new Date().setFullYear(new Date().getFullYear() - 50)), // Up to 50 years ago
            defaultDate: 'today',
            allowInput: true,
            altInput: true,
            altFormat: 'F j, Y',
            clickOpens: true,
            monthSelectorType: 'dropdown',
            yearSelectorType: 'dropdown'
        });
    }
}

// Multi-step form navigation
function goToStep(stepNumber) {
    // Hide all steps
    document.querySelectorAll('.step-content').forEach(function(step) {
        step.style.display = 'none';
    });

    // Show current step
    const currentStep = document.getElementById('step' + stepNumber + 'Content');
    if (currentStep) {
        currentStep.style.display = 'block';
    }

    // Update indicators
    document.querySelectorAll('.step-item').forEach(function(item, index) {
        item.classList.remove('active', 'completed');
        if (index + 1 < stepNumber) {
            item.classList.add('completed');
        } else if (index + 1 === stepNumber) {
            item.classList.add('active');
        }
    });
}

// Skip payroll step
function skipPayroll() {
    // Clear payroll fields
    document.getElementById('emp_basicSalary').value = '';
    document.getElementById('emp_pin').value = '';
    document.getElementById('emp_nhifNumber').value = '';
    document.getElementById('emp_nssfNumber').value = '';
    document.getElementById('emp_costPerHour').value = '';
    document.getElementById('emp_overtimeAllowed').checked = false;
    document.getElementById('emp_bonusEligible').checked = false;

    // Submit the form
    const form = document.querySelector('#addEmployeeModal form');
    if (form) {
        form.submit();
    }
}

function viewAllEmployees(entityID) {
    alert('View all employees for entity ID: ' + entityID);
    // TODO: Navigate to employees page with filter
}

function manageDepartments(entityID) {
    alert('Manage departments for entity ID: ' + entityID);
    // TODO: Navigate to departments page
}

function generateEntityReport(entityID) {
    alert('Generate report for entity ID: ' + entityID);
    // TODO: Implement report generation
}

function exportEntityData(entityID) {
    alert('Export data for entity ID: ' + entityID);
    // TODO: Implement data export
}

function addUnitForEntity(entityID) {
    const modal = document.querySelector('#manageUnitModal');
    if (modal) {
        // Reset form
        modal.querySelector('form')?.reset();

        // Set entity ID
        const entityInput = modal.querySelector('#unit_entityID');
        if (entityInput) {
            entityInput.value = entityID;
        }

        // Update modal title
        const modalTitle = modal.querySelector('.modal-title');
        if (modalTitle) {
            modalTitle.textContent = 'Add Entity Unit';
        }
    }
}

function editUnit(unitID) {
    // Fetch unit data and populate edit modal
    const modal = document.querySelector('#manageUnitModal');

    if (!modal) {
        console.error('Unit modal not found');
        return;
    }

    // Update modal title
    const modalTitle = modal.querySelector('.modal-title');
    if (modalTitle) {
        modalTitle.textContent = 'Edit Entity Unit';
    }

    // Build the URL
    const url = '<?= $base ?>php/scripts/global/admin/get_unit.php?unitID=' + unitID;
    console.log('Fetching unit data from:', url);

    // Fetch unit data via AJAX
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.text(); // Get as text first to debug
        })
        .then(text => {
            console.log('Raw response:', text);
            try {
                const data = JSON.parse(text);
                console.log('Parsed JSON:', data);

                if (data.success && data.unit) {
                    const unit = data.unit;
                    console.log('Unit data loaded:', unit);

                    // Populate form fields
                    const fields = {
                        'unitID': unit.unitID,
                        'orgDataID': unit.orgDataID,
                        'entityID': unit.entityID,
                        'unitName': unit.unitName,
                        'unitCode': unit.unitCode,
                        'unitTypeID': unit.unitTypeID,
                        'parentUnitID': unit.parentUnitID || '0',
                        'unitDescription': unit.unitDescription
                    };

                    // Fill in each field
                    for (const [fieldName, value] of Object.entries(fields)) {
                        const input = modal.querySelector(`[name="${fieldName}"], #${fieldName}`);
                        if (input) {
                            if (input.tagName === 'SELECT') {
                                input.value = value || '';
                                // Trigger change event for dependent fields
                                input.dispatchEvent(new Event('change', { bubbles: true }));
                            } else if (input.type === 'checkbox') {
                                input.checked = value === 'Y';
                            } else if (input.type === 'textarea' || input.tagName === 'TEXTAREA') {
                                input.value = value || '';
                            } else {
                                input.value = value || '';
                            }
                        } else {
                            console.warn('Field not found in modal:', fieldName);
                        }
                    }

                } else {
                    const errorMsg = data.message || 'Failed to load unit data';
                    alert('Error: ' + errorMsg);
                    console.error('Unit data error:', data);
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response text:', text);
                alert('Error parsing server response. Check console for details.');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Error loading unit data: ' + error.message);
        });
}

function addBusinessUnitForEntity(entityID) {
    const modal = document.querySelector('#manageBusinessUnitModal');
    if (modal) {
        // Reset form
        modal.querySelector('form')?.reset();

        // Set entity ID
        const entityInput = modal.querySelector('#bu_entityID');
        if (entityInput) {
            entityInput.value = entityID;
        }

        // Update modal title
        const modalTitle = modal.querySelector('.modal-title');
        if (modalTitle) {
            modalTitle.textContent = 'Add Business Unit';
        }
    }
}

function editBusinessUnit(businessUnitID) {
    // Fetch business unit data and populate edit modal
    const modal = document.querySelector('#manageBusinessUnitModal');

    if (!modal) {
        console.error('Business unit modal not found');
        return;
    }

    // Update modal title
    const modalTitle = modal.querySelector('.modal-title');
    if (modalTitle) {
        modalTitle.textContent = 'Edit Business Unit';
    }

    // Build the URL
    const url = '<?= $base ?>php/scripts/global/admin/get_business_unit.php?businessUnitID=' + businessUnitID;
    console.log('Fetching business unit data from:', url);

    // Fetch business unit data via AJAX
    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.text(); // Get as text first to debug
        })
        .then(text => {
            console.log('Raw response:', text);
            try {
                const data = JSON.parse(text);
                console.log('Parsed JSON:', data);

                if (data.success && data.businessUnit) {
                    const bu = data.businessUnit;
                    console.log('Business unit data loaded:', bu);

                    // Populate form fields
                    const fields = {
                        'businessUnitID': bu.businessUnitID,
                        'bu_orgDataID': bu.orgDataID,
                        'bu_entityID': bu.entityID,
                        'businessUnitName': bu.businessUnitName,
                        'bu_unitTypeID': bu.unitTypeID,
                        'categoryID': bu.categoryID,
                        'businessUnitDescription': bu.businessUnitDescription
                    };

                    // Fill in each field
                    for (const [fieldName, value] of Object.entries(fields)) {
                        const input = modal.querySelector(`[name="${fieldName}"], #${fieldName}`);
                        if (input) {
                            if (input.tagName === 'SELECT') {
                                input.value = value || '';
                                // Trigger change event for dependent fields
                                input.dispatchEvent(new Event('change', { bubbles: true }));
                            } else if (input.type === 'checkbox') {
                                input.checked = value === 'Y';
                            } else if (input.type === 'textarea' || input.tagName === 'TEXTAREA') {
                                input.value = value || '';
                            } else {
                                input.value = value || '';
                            }
                        } else {
                            console.warn('Field not found in modal:', fieldName);
                        }
                    }

                } else {
                    const errorMsg = data.message || 'Failed to load business unit data';
                    alert('Error: ' + errorMsg);
                    console.error('Business unit data error:', data);
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response text:', text);
                alert('Error parsing server response. Check console for details.');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Error loading business unit data: ' + error.message);
        });
}

// Add event listeners to all edit unit and business unit buttons
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date pickers for employee modal on page load
    initializeEmployeeDatePickers();

    // Select all edit unit buttons and add click event listeners
    const editUnitButtons = document.querySelectorAll('.editUnit');

    editUnitButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const unitID = this.getAttribute('data-unit-id');
            if (unitID) {
                editUnit(unitID);
            } else {
                console.error('Unit ID not found on edit button');
            }
        });
    });

    // Select all edit business unit buttons and add click event listeners
    const editBusinessUnitButtons = document.querySelectorAll('.editBusinessUnit');

    editBusinessUnitButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const businessUnitID = this.getAttribute('data-business-unit-id');
            if (businessUnitID) {
                editBusinessUnit(businessUnitID);
            } else {
                console.error('Business Unit ID not found on edit button');
            }
        });
    });

    // Re-attach listeners when tab content changes (for dynamic content)
    const observer = new MutationObserver(function(mutations) {
        // Handle edit unit buttons
        const newEditButtons = document.querySelectorAll('.editUnit');
        newEditButtons.forEach(function(button) {
            if (!button.hasAttribute('data-listener-attached')) {
                button.setAttribute('data-listener-attached', 'true');
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const unitID = this.getAttribute('data-unit-id');
                    if (unitID) {
                        editUnit(unitID);
                    }
                });
            }
        });

        // Handle edit business unit buttons
        const newEditBusinessUnitButtons = document.querySelectorAll('.editBusinessUnit');
        newEditBusinessUnitButtons.forEach(function(button) {
            if (!button.hasAttribute('data-listener-attached')) {
                button.setAttribute('data-listener-attached', 'true');
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const businessUnitID = this.getAttribute('data-business-unit-id');
                    if (businessUnitID) {
                        editBusinessUnit(businessUnitID);
                    }
                });
            }
        });
    });

    // Observe the tab content for changes
    const tabContent = document.getElementById('entityTabContent');
    if (tabContent) {
        observer.observe(tabContent, { childList: true, subtree: true });
    }
});
</script>

