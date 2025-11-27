<!-- Tenant Details Page -->
<?php
// Check admin access
if (!$isValidAdmin && !$isAdmin) {
    Alert::info("You need to be logged in as a valid administrator to access this page", true,
        array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    exit;
}

// Get organization ID from URL
$orgDataID = isset($_GET['id']) ? Utility::clean_string($_GET['id']) : (isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : null);

if (!$orgDataID) {
    Alert::danger("Invalid organization ID", true, array('text-center', 'font-18'));
    echo '<div class="text-center mt-3"><a href="' . $base . 'html/?s=core&ss=admin&p=home&tab=tenants" class="btn btn-primary">Back to Tenants</a></div>';
    exit;
}

$getString.="&orgDataID={$orgDataID}";
// var_dump($getString);
// Fetch organization data
$organisation = Admin::org_data(array('orgDataID' => $orgDataID), true, $DBConn);

if (!$organisation) {
    Alert::danger("Organization not found", true, array('text-center', 'font-18'));
    echo '<div class="text-center mt-3"><a href="' . $base . 'html/?s=core&ss=admin&p=home&tab=tenants" class="btn btn-primary">Back to Tenants</a></div>';
    exit;
}

// Fetch related data
$entities = Data::entities_full(['orgDataID' => $orgDataID, 'Suspended' => 'N'], false, $DBConn);
$orgAdmins = Core::organisation_admins(['orgDataID' => $orgDataID, 'Suspended' => 'N'], false, $DBConn);
$country = Data::countries(['countryID' => $organisation->countryID], true, $DBConn);
$industry = Data::industry_sectors(['industrySectorID' => $organisation->industrySectorID], true, $DBConn);

// Get license information (placeholder - adjust based on your license table)
$licenseQuery = "SELECT * FROM tija_licenses WHERE orgDataID = ? AND Suspended = 'N' LIMIT 1";
$licenseParams = array(array($orgDataID, 'i'));
$licenses = $DBConn->fetch_all_rows($licenseQuery, $licenseParams);
$license = $licenses && count($licenses) > 0 ? $licenses[0] : null;

$entityCount = $entities ? count($entities) : 0;
$adminCount = $orgAdmins ? count($orgAdmins) : 0;
?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-24 mb-0">
            <i class="fas fa-building text-primary me-2"></i>
            <?= htmlspecialchars($organisation->orgName) ?>
        </h1>
        <p class="text-muted mb-0 mt-1">Tenant Details & Management</p>
    </div>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="<?= $base ?>html/?s=core&ss=admin&p=home">Admin</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="<?= $base ?>html/?s=core&ss=admin&p=home&tab=tenants">Tenants</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?= htmlspecialchars($organisation->orgName) ?>
                </li>
            </ol>
        </nav>
    </div>
</div>
<?php

//get the adminstrator level for the current user
$adminLevel = Core::app_administrators(array('userID'=>$userID, 'orgDataID'=>$orgDataID), true, $DBConn);
// var_dump($adminLevel);?>

<!-- Quick Actions Bar -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card custom-card border-0 shadow-sm">
            <div class="card-body py-2">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <button type="button" class="btn btn-primary btn-sm btn-wave" data-bs-toggle="modal" data-bs-target="#manageOrganisationModal" onclick="editOrganisation(<?= $orgDataID ?>)">
                        <i class="fas fa-edit me-2"></i>Edit Organization
                    </button>
                    <button type="button" class="btn btn-info btn-sm btn-wave" data-bs-toggle="modal" data-bs-target="#manageEntity" onclick="addEntityForOrg(<?= $orgDataID ?>)">
                        <i class="fas fa-sitemap me-2"></i>Add Entity
                    </button>
                    <?php if(($adminLevel && ($adminLevel->adminCode == 'SUPER')) || $isValidAdmin){ ?>
                    <button type="button" class="btn btn-success btn-sm btn-wave" data-bs-toggle="modal" data-bs-target="#manageLicenseModal" onclick="manageLicense(<?= $orgDataID ?>, '<?= htmlspecialchars($organisation->orgName) ?>')">
                        <i class="fas fa-key me-2"></i>Manage License
                    </button>


                    <button type="button" class="btn btn-secondary btn-sm btn-wave" data-bs-toggle="modal" data-bs-target="#manageAdmin" onclick="addAdminForOrg(<?= $orgDataID ?>)">
                        <i class="fas fa-user-plus me-2"></i>Add Administrator
                    </button>

                    <button type="button" class="btn btn-<?= $organisation->Suspended == 'N' ? 'warning' : 'success' ?> btn-sm btn-wave" onclick="toggleSuspension(<?= $orgDataID ?>, '<?= $organisation->Suspended ?>')">
                        <i class="fas fa-<?= $organisation->Suspended == 'N' ? 'ban' : 'check' ?> me-2"></i>
                        <?= $organisation->Suspended == 'N' ? 'Suspend' : 'Activate' ?>
                    </button>
                    <?php } ?>

                    <a href="<?= $base ?>html/?s=core&ss=admin&p=home&tab=tenants" class="btn btn-light btn-sm btn-wave ms-auto">
                        <i class="fas fa-arrow-left me-2"></i>Back to Tenants
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Total Entities</p>
                        <h3 class="mb-0 fw-semibold"><?= $entityCount ?></h3>
                        <small class="text-muted fs-11">Registered entities</small>
                    </div>
                    <div class="ms-2">
                        <span class="avatar avatar-md bg-primary-transparent">
                            <i class="fas fa-sitemap fs-20"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Administrators</p>
                        <h3 class="mb-0 fw-semibold"><?= $adminCount ?></h3>
                        <small class="text-muted fs-11">Active admins</small>
                    </div>
                    <div class="ms-2">
                        <span class="avatar avatar-md bg-success-transparent">
                            <i class="fas fa-user-shield fs-20"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Employees</p>
                        <h3 class="mb-0 fw-semibold"><?= $organisation->numberOfEmployees ?? 0 ?></h3>
                        <small class="text-muted fs-11">Total employees</small>
                    </div>
                    <div class="ms-2">
                        <span class="avatar avatar-md bg-secondary-transparent">
                            <i class="fas fa-users fs-20"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Status</p>
                        <h3 class="mb-0 fw-semibold">
                            <span class="badge bg-<?= $organisation->Suspended == 'N' ? 'success' : 'danger' ?>-transparent fs-14">
                                <?= $organisation->Suspended == 'N' ? 'Active' : 'Suspended' ?>
                            </span>
                        </h3>
                        <small class="text-muted fs-11">Current status</small>
                    </div>
                    <div class="ms-2">
                        <span class="avatar avatar-md bg-warning-transparent">
                            <i class="fas fa-<?= $organisation->Suspended == 'N' ? 'check-circle' : 'ban' ?> fs-20"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="row">
    <!-- Left Column - Organization Details & License -->
    <div class="col-xl-8">
        <!-- Organization Details -->
        <div class="card custom-card mb-4">
            <div class="card-header">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Organization Details</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Organization Name</label>
                            <p class="mb-0 fs-15"><?= htmlspecialchars($organisation->orgName) ?></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Registration Number</label>
                            <p class="mb-0 fs-15">
                                <i class="fas fa-id-card text-primary me-2"></i>
                                <?= htmlspecialchars($organisation->registrationNumber ?? 'N/A') ?>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Tax PIN/Number</label>
                            <p class="mb-0 fs-15">
                                <i class="fas fa-hashtag text-primary me-2"></i>
                                <?= htmlspecialchars($organisation->orgPIN ?? 'N/A') ?>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Industry Sector</label>
                            <p class="mb-0 fs-15">
                                <i class="fas fa-industry text-primary me-2"></i>
                                <?= htmlspecialchars($industry->industryTitle ?? 'N/A') ?>
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Email</label>
                            <p class="mb-0 fs-15">
                                <i class="fas fa-envelope text-primary me-2"></i>
                                <a href="mailto:<?= htmlspecialchars($organisation->orgEmail) ?>">
                                    <?= htmlspecialchars($organisation->orgEmail ?? 'N/A') ?>
                                </a>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Phone Number</label>
                            <p class="mb-0 fs-15">
                                <i class="fas fa-phone text-primary me-2"></i>
                                <?= htmlspecialchars($organisation->orgPhoneNumber1 ?? 'N/A') ?>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Location</label>
                            <p class="mb-0 fs-15">
                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                <?= htmlspecialchars($organisation->orgCity ?? '') ?>
                                <?= $country ? ', ' . htmlspecialchars($country->countryName) : '' ?>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Address</label>
                            <p class="mb-0 fs-15">
                                <i class="fas fa-map text-primary me-2"></i>
                                <?= htmlspecialchars($organisation->orgAddress ?? 'N/A') ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- License Information -->
        <div class="card custom-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-certificate me-2"></i>License Information</h5>
                </div>
                <?php if(($adminLevel && ($adminLevel->adminCode == 'SUPER')) || $isValidAdmin){ ?>
                <button type="button" class="btn btn-sm btn-primary btn-wave" data-bs-toggle="modal" data-bs-target="#manageLicenseModal" onclick="manageLicense(<?= $orgDataID ?>, '<?= htmlspecialchars($organisation->orgName) ?>')">
                    <i class="fas fa-edit me-2"></i>Manage License
                </button>
                <?php } ?>
            </div>
            <div class="card-body">
                <?php if ($license): ?>
                    <?php
                    // Get license type details
                    $licenseType = Admin::license_types(['licenseTypeCode' => $license->licenseType], true, $DBConn);

                    // Calculate days until expiry
                    $expiryDate = new DateTime($license->licenseExpiryDate);
                    $today = new DateTime();
                    $daysUntilExpiry = $today->diff($expiryDate)->days;
                    $isExpired = $expiryDate < $today;
                    $isExpiringSoon = !$isExpired && $daysUntilExpiry < 30;

                    // Calculate usage percentage
                    $usagePercentage = ($license->currentUsers / $license->userLimit) * 100;
                    ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <label class="form-label text-muted fw-semibold mb-2">License Type</label>
                                <div class="d-flex align-items-center">
                                    <?php if ($licenseType): ?>
                                        <span class="avatar avatar-sm me-2" style="background-color: <?= htmlspecialchars($licenseType->colorCode ?? '#6c757d') ?>;">
                                            <i class="fas <?= htmlspecialchars($licenseType->iconClass ?? 'fa-certificate') ?> text-white"></i>
                                        </span>
                                    <?php endif; ?>
                                    <div>
                                        <strong><?= $licenseType ? htmlspecialchars($licenseType->licenseTypeName) : ucfirst($license->licenseType) ?></strong>
                                        <?php if ($licenseType && $licenseType->isPopular == 'Y'): ?>
                                            <span class="badge bg-warning ms-2">⭐ Popular</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <label class="form-label text-muted fw-semibold mb-2">License Key</label>
                                <div class="d-flex align-items-center">
                                    <code class="fs-14"><?= htmlspecialchars($license->licenseKey) ?></code>
                                    <button class="btn btn-sm btn-light ms-2" onclick="copyToClipboard('<?= htmlspecialchars($license->licenseKey) ?>')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded text-center">
                                <label class="form-label text-muted fw-semibold mb-2">User Limit</label>
                                <h4 class="mb-0"><?= number_format($license->userLimit) ?></h4>
                                <small class="text-muted">Maximum users</small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded text-center">
                                <label class="form-label text-muted fw-semibold mb-2">Active Users</label>
                                <h4 class="mb-0"><?= number_format($license->currentUsers) ?></h4>
                                <div class="progress mt-2" style="height: 8px;">
                                    <div class="progress-bar <?= $usagePercentage > 80 ? 'bg-danger' : ($usagePercentage > 60 ? 'bg-warning' : 'bg-success') ?>"
                                         role="progressbar"
                                         style="width: <?= $usagePercentage ?>%">
                                    </div>
                                </div>
                                <small class="text-muted"><?= number_format($usagePercentage, 1) ?>% used</small>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded text-center">
                                <label class="form-label text-muted fw-semibold mb-2">Status</label>
                                <h4 class="mb-0">
                                    <span class="badge bg-<?= $isExpired ? 'danger' : ($isExpiringSoon ? 'warning' : 'success') ?>">
                                        <?= $isExpired ? 'Expired' : ($isExpiringSoon ? 'Expiring Soon' : 'Active') ?>
                                    </span>
                                </h4>
                                <small class="text-muted">
                                    <?php if (!$isExpired): ?>
                                        <?= $daysUntilExpiry ?> days left
                                    <?php else: ?>
                                        Expired
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <label class="form-label text-muted fw-semibold mb-2">Issue Date</label>
                                <p class="mb-0">
                                    <i class="fas fa-calendar-plus text-success me-2"></i>
                                    <?= date('F d, Y', strtotime($license->licenseIssueDate)) ?>
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <label class="form-label text-muted fw-semibold mb-2">Expiry Date</label>
                                <p class="mb-0">
                                    <i class="fas fa-calendar-times text-<?= $isExpired ? 'danger' : ($isExpiringSoon ? 'warning' : 'info') ?> me-2"></i>
                                    <?= date('F d, Y', strtotime($license->licenseExpiryDate)) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <div class="avatar avatar-lg bg-warning-transparent mx-auto mb-3">
                            <i class="fas fa-exclamation-triangle fs-24"></i>
                        </div>
                        <h6 class="mb-2">No License Assigned</h6>
                        <p class="text-muted mb-3">This organization doesn't have an active license yet.</p>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manageLicenseModal" onclick="manageLicense(<?= $orgDataID ?>, '<?= htmlspecialchars($organisation->orgName) ?>')">
                            <i class="fas fa-plus me-2"></i>Assign License
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Entities List -->
        <div class="card custom-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-sitemap me-2"></i>Entities (<?= $entityCount ?>)</h5>
                </div>
                <button type="button" class="btn btn-sm btn-primary btn-wave" data-bs-toggle="modal" data-bs-target="#manageEntity" onclick="addEntityForOrg(<?= $orgDataID ?>)">
                    <i class="fas fa-plus me-2"></i>Add Entity
                </button>
            </div>
            <div class="card-body">
                <?php if ($entities): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Entity Name</th>
                                    <th>Type</th>
                                    <th>Registration</th>
                                    <th>Location</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Sort and display entities hierarchically
                                usort($entities, function($a, $b) {
                                    return $a->entityParentID <=> $b->entityParentID;
                                });

                                foreach ($entities as $entity):
                                    $isParent = $entity->entityParentID == 0;
                                ?>
                                    <tr>
                                        <td>
                                            <?php if (!$isParent): ?>
                                                <span class="text-muted me-2">└─</span>
                                            <?php endif; ?>
                                            <strong><?= htmlspecialchars($entity->entityName) ?></strong>
                                            <?php if ($isParent): ?>
                                                <span class="badge bg-primary-transparent ms-2">Parent</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($entity->entityTypeTitle ?? 'N/A') ?></td>
                                        <td>
                                            <small><?= htmlspecialchars($entity->registrationNumber) ?></small>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($entity->entityCity) ?>,
                                            <?= htmlspecialchars($entity->countryName ?? 'N/A') ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= $base ?>html/?s=core&ss=admin&p=entity_details&entityID=<?= $entity->entityID ?>"
                                                class="btn btn-sm btn-info-light" title="View Entity">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button class="btn btn-sm btn-primary-light editEntity"
                                                data-bs-toggle="modal"
                                                data-bs-target="#manageEntity"
                                                data-id="<?= $entity->entityID ?>"
                                                title="Edit Entity">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <div class="avatar avatar-lg bg-secondary-transparent mx-auto mb-3">
                            <i class="fas fa-sitemap fs-24"></i>
                        </div>
                        <h6 class="mb-2">No Entities Yet</h6>
                        <p class="text-muted mb-3">Add entities to organize this tenant.</p>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manageEntity" onclick="addEntityForOrg(<?= $orgDataID ?>)">
                            <i class="fas fa-plus me-2"></i>Add First Entity
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column - Admins & Activity -->
    <div class="col-xl-4">
        <!-- Administrators -->
        <div class="card custom-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-user-shield me-2"></i>Administrators</h5>
                </div>
                <?php if(($adminLevel && ($adminLevel->adminCode == 'SUPER')) || $isValidAdmin){ ?>
                <button type="button" class="btn btn-sm btn-primary btn-wave" data-bs-toggle="modal" data-bs-target="#manageAdmin" onclick="addAdminForOrg(<?= $orgDataID ?>)">
                    <i class="fas fa-plus"></i>
                </button>
                <?php } ?>
            </div>
            <div class="card-body">
                <?php if ($orgAdmins): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($orgAdmins as $admin):

                            // var_dump($admin); ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex align-items-center">
                                    <?php if ($admin->profile_image): ?>
                                        <img src="<?= "{$base}data/uploaded_files/{$admin->profile_image}" ?>" alt="Profile" class="avatar avatar-sm rounded-circle me-2">
                                    <?php else: ?>
                                        <div class="avatar avatar-sm bg-primary-transparent rounded-circle me-2">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="flex-fill">
                                        <h6 class="mb-0"><?= htmlspecialchars($admin->AdminName) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($admin->Email) ?></small>
                                        <br>
                                        <span class="badge bg-info-transparent mt-1">
                                            <?= htmlspecialchars($admin->adminTypeName ?? 'Admin') ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3">
                        <div class="avatar avatar-md bg-success-transparent mx-auto mb-2">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <p class="text-muted mb-2 fs-13">No administrators assigned</p>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#manageAdmin" onclick="addAdminForOrg(<?= $orgDataID ?>)">
                            <i class="fas fa-plus me-2"></i>Add Admin
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card custom-card mb-4">
            <div class="card-header">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Quick Stats</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Cost Center</span>
                        <span class="badge bg-<?= $organisation->costCenterEnabled == 'Y' ? 'success' : 'secondary' ?>-transparent">
                            <?= $organisation->costCenterEnabled == 'Y' ? 'Enabled' : 'Disabled' ?>
                        </span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Created On</span>
                        <span><?= date('M d, Y', strtotime($organisation->DateAdded)) ?></span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Last Updated</span>
                        <span><?= date('M d, Y', strtotime($organisation->LastUpdate)) ?></span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Postal Code</span>
                        <span><?= htmlspecialchars($organisation->orgPostalCode ?? 'N/A') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Actions -->
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Actions</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= $base ?>html/?s=core&ss=admin&p=home&tab=entities" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-sitemap me-2"></i>View All Entities
                    </a>
                    <a href="<?= $base ?>html/?s=core&ss=admin&p=home&tab=admins" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-users me-2"></i>View All Admins
                    </a>
                    <button type="button" class="btn btn-outline-info btn-sm" onclick="generateReport(<?= $orgDataID ?>)">
                        <i class="fas fa-file-pdf me-2"></i>Generate Report
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="exportData(<?= $orgDataID ?>)">
                        <i class="fas fa-download me-2"></i>Export Data
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include necessary modals
$industrySectors = Data::industry_sectors(array("Suspended"=>'N'), false, $DBConn);
$countries = Data::countries(array(), false, $DBConn);
$entityTypes = Data::entity_types(array(), false, $DBConn);
$adminTypes = Admin::admin_types(array(), false, $DBConn);
$users = Core::user([], false, $DBConn);
$african_countries = Data::countries(array('isActive' => '1'), false, $DBConn);
$organisations = Admin::org_data(array(), false, $DBConn);
$entities = Data::entities_full(array('Suspended'=> 'N', 'orgDataID' => $orgDataID), false, $DBConn);

$orgUsers = Employee::employees(array('orgDataID' => $orgDataID), false, $DBConn);
// var_dump($orgUsers);


// Modal for editing Organisation
echo Utility::form_modal_header("manageOrganisationModal", "global/manage_organisation_details.php", "Edit Organisation", array('modal-md', 'modal-dialog-centered', "modal-xl"), $base);
include "includes/core/admin/organisation/modals/manage_organisation.php";
echo Utility::form_modal_footer('Save Organisation');

// Modal for adding Entity
echo Utility::form_modal_header("manageEntity", "tax/admin/manage_entity.php", "Add Organization Entity", array('modal-md', 'modal-dialog-centered', "modal-xl"), $base);
include "includes/scripts/global/modals/manage_entity.php";
echo Utility::form_modal_footer('Save Entity');

// Modal for adding Organisation Admin
echo Utility::form_modal_header("manageAdmin", "global/admin/manage_admin.php", "Manage Organisation Admin", array('modal-md', 'modal-dialog-centered', "modal-xl"), $base);
include "includes/scripts/global/modals/manage_org_admin_refactored.php";
echo Utility::form_modal_footer('Save Administrator');

// Modal for restricted edit of Administrator (Role, Entity, Unit, Options only)
echo Utility::form_modal_header("editAdminRestricted", "global/admin/manage_admin.php", "Edit Administrator Role & Assignment", array('modal-md', 'modal-dialog-centered', "modal-lg"), $base);
include "includes/core/admin/modals/edit_admin_restricted.php";
echo Utility::form_modal_footer('Update Administrator');

// License Management Modal
echo Utility::form_modal_header("manageLicenseModal", "global/admin/manage_license.php", "Manage License", array('modal-md', 'modal-dialog-centered', "modal-lg"), $base);
include "includes/core/admin/modals/manage_license.php";
echo Utility::form_modal_footer('Save License');

// Initialize date pickers
include "includes/core/admin/init_date_pickers.php";
?>

<style>
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
function addEntityForOrg(orgId) {
    const modal = document.querySelector('#manageEntity');
    if (modal) {
        // Reset form
        modal.querySelector('form')?.reset();

        // Set organisation ID
        const orgInput = modal.querySelector('select[name="orgDataID"]');
        if (orgInput) {
            orgInput.value = orgId;
        }
    }
}

function addAdminForOrg(orgId) {
    console.log('addAdminForOrg called with orgId:', orgId);
    const modal = document.querySelector('#manageAdmin');
    if (modal) {
        // Don't reset form yet - we need to set the value first

        // Ensure we're in "Select Existing User" mode
        const selectExistingBtn = modal.querySelector('#selectExistingUser');
        if (selectExistingBtn) {
            selectExistingBtn.checked = true;
            // Trigger the mode change
            selectExistingBtn.dispatchEvent(new Event('click'));
        }

        // Set organisation ID - specifically target the SELECT element
        const orgInput = modal.querySelector('select[name="orgDataID"]');
        if (orgInput) {
            console.log('Found organization select element:', orgInput.tagName);
            console.log('Setting orgDataID to:', orgId);
            orgInput.value = orgId;
            console.log('orgDataID value after setting:', orgInput.value);
            console.log('orgDataID element:', orgInput);

            // Trigger change event to load entities and users
            console.log('Triggering change event on orgDataID');
            const changeEvent = new Event('change', { bubbles: true });
            orgInput.dispatchEvent(changeEvent);
        } else {
            console.error('Organization select element not found in modal!');
            console.log('Looking for: select[name="orgDataID"] inside modal');
            // Debug: show all elements with orgDataID
            const allOrgElements = modal.querySelectorAll('[name="orgDataID"]');
            console.log('Found', allOrgElements.length, 'elements with name="orgDataID":', allOrgElements);
        }
    } else {
        console.error('Modal element not found!');
    }
}

function manageLicense(orgId, orgName) {
    // Fetch and populate license management modal with organization data
    const modal = document.querySelector('#manageLicenseModal');

    if (!modal) {
        console.error('License modal not found');
        return;
    }

    // Set organization ID and update title
    modal.querySelector('input[name="orgDataID"]').value = orgId;
    modal.querySelector('.modal-title').textContent = 'Manage License for ' + orgName;

    // Fetch existing license data via AJAX
    fetch('<?= $base ?>php/scripts/global/admin/get_license.php?orgDataID=' + orgId)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch license data');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.hasLicense && data.license) {
                const license = data.license;

                // Populate form fields
                const fields = {
                    'licenseID': license.licenseID,
                    'licenseType': license.licenseType,
                    'userLimit': license.userLimit,
                    'licenseKey': license.licenseKey,
                    'licenseStatus': license.licenseStatus,
                    'licenseNotes': license.licenseNotes
                };

                // Fill in each field
                for (const [fieldName, value] of Object.entries(fields)) {
                    const input = modal.querySelector(`[name="${fieldName}"], #${fieldName}`);
                    if (input) {
                        input.value = value || '';
                    }
                }

                // Set dates using flatpickr
                const issueDatePicker = modal.querySelector('#licenseIssueDate')._flatpickr;
                if (issueDatePicker && license.licenseIssueDate) {
                    issueDatePicker.setDate(license.licenseIssueDate);
                }

                const expiryDatePicker = modal.querySelector('#licenseExpiryDate')._flatpickr;
                if (expiryDatePicker && license.licenseExpiryDate) {
                    expiryDatePicker.setDate(license.licenseExpiryDate);
                }

                // Handle features checkboxes
                if (license.features && Array.isArray(license.features)) {
                    modal.querySelectorAll('input[name="features[]"]').forEach(checkbox => {
                        checkbox.checked = license.features.includes(checkbox.value);
                    });
                }

                console.log('License data loaded:', license);
            } else {
                // No existing license - reset form for new license
                console.log('No existing license found. Ready for new license.');
                const form = modal.querySelector('form');
                if (form) {
                    form.reset();
                    // Keep orgDataID
                    modal.querySelector('input[name="orgDataID"]').value = orgId;
                }

                // Clear licenseID
                const licenseIDInput = modal.querySelector('#licenseID');
                if (licenseIDInput) {
                    licenseIDInput.value = '';
                }
            }
        })
        .catch(error => {
            console.error('Error loading license:', error);
            // Reset form on error - prepare for new license
            const form = modal.querySelector('form');
            if (form) {
                form.reset();
                modal.querySelector('input[name="orgDataID"]').value = orgId;
            }
        });
}

function editOrganisation(orgId) {
    // Fetch organization data and populate edit modal
    const modal = document.querySelector('#manageOrganisationModal');

    if (!modal) {
        console.error('Organization modal not found');
        return;
    }

    // Update modal title
    modal.querySelector('.modal-title').textContent = 'Edit Organization';

    // Fetch organization data via AJAX
    fetch('<?= $base ?>php/scripts/global/admin/get_organisation.php?orgDataID=' + orgId)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch organization data');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.organisation) {
                const org = data.organisation;

                // Populate form fields
                const fields = {
                    'orgDataID': org.orgDataID,
                    'orgName': org.orgName,
                    'numberOfEmployees': org.numberOfEmployees,
                    'registrationNumber': org.registrationNumber,
                    'orgPIN': org.orgPIN,
                    'orgAddress': org.orgAddress,
                    'orgPostalCode': org.orgPostalCode,
                    'orgCity': org.orgCity,
                    'countryID': org.countryID,
                    'orgPhoneNumber1': org.orgPhoneNumber1,
                    'orgPhoneNUmber2': org.orgPhoneNUmber2,
                    'orgEmail': org.orgEmail,
                    'industrySectorID': org.industrySectorID
                };

                // Fill in each field
                for (const [fieldName, value] of Object.entries(fields)) {
                    const input = modal.querySelector(`[name="${fieldName}"], #${fieldName}`);
                    if (input) {
                        if (input.tagName === 'SELECT') {
                            input.value = value || '';
                        } else if (input.type === 'checkbox') {
                            input.checked = value === 'Y';
                        } else if (input.type === 'textarea' || input.tagName === 'TEXTAREA') {
                            input.value = value || '';
                        } else {
                            input.value = value || '';
                        }
                    }
                }

                // Handle cost center checkbox
                const costCenterCheckbox = modal.querySelector('#costCenterEnabled');
                if (costCenterCheckbox) {
                    costCenterCheckbox.checked = org.costCenterEnabled === 'Y';
                }

            } else {
                alert('Error: ' + (data.message || 'Failed to load organization data'));
            }
        })
        .catch(error => {
            console.error('Error loading organization:', error);
            alert('Error loading organization data. Please try again.');
        });
}

function toggleSuspension(orgId, currentStatus) {
    const action = currentStatus === 'N' ? 'suspend' : 'activate';
    if (confirm('Are you sure you want to ' + action + ' this organization?')) {
        // Implement suspension toggle
        alert(action + ' organization ID: ' + orgId);
        // TODO: Implement AJAX call to update status
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('License key copied to clipboard!');
    }, function(err) {
        alert('Failed to copy: ' + err);
    });
}

function generateReport(orgId) {
    alert('Generate report for organization ID: ' + orgId);
    // TODO: Implement report generation
}

function exportData(orgId) {
    alert('Export data for organization ID: ' + orgId);
    // TODO: Implement data export
}

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
</script>

