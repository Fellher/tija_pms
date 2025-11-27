<!-- Page Header -->
<?php
//  var_dump($isValidAdmin);
if (!$isValidAdmin && !$isAdmin ) {
    Alert::info("You need to be logged in as a valid administrator to access this page", true,
        array('fst-italic', 'text-center', 'font-18'));
        include "includes/core/log_in_script.php";
    exit;
}

// Get active tab from URL parameter (default to 'tenants')
$activeTab = isset($_GET['tab']) ? Utility::clean_string($_GET['tab']) : 'tenants';

// Validate tab parameter
$validTabs = array('tenants', 'entities', 'licenses', 'admins');
if (!in_array($activeTab, $validTabs)) {
    $activeTab = 'tenants';
}
// var_dump($validAdmin);
// Fetch statistics for dashboard

$organisations = Admin::org_data([], false, $DBConn);
$entities = Data::entities_full(['Suspended'=> 'N'], false, $DBConn);
$admins = Core::organisation_admins(['Suspended'=>"N"], false, $DBConn);

$totalAdmins = $admins ? count($admins) : 0;
$tenants=[];
if($isAdmin){
    if($isTenantAdmin || $isSuperAdmin){
       foreach($validAdmin as $admin){
        //filter organisations by orgDataID
        $tenantData = array_filter($organisations, function($organisation) use ($admin){

            return $organisation->orgDataID == $admin->orgDataID;

        });

        //merge $tenantData to $tenants
        $tenants = array_merge($tenants, $tenantData);

       }
    } elseif($isEntityAdmin){
        $adminFilter = array('entityID'=>$entityID);
    } elseif($isUnitAdmin){
        $adminFilter = array('unitID'=>$unitID);
    }
//    var_dump($validAdmin);
//    var_dump($tenants);

$organisations = $tenants;

}

$totalOrganisations = $organisations ? count($organisations) : 0;
$totalEntities = $entities ? count($entities) : 0;
?>

<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <h1 class="page-title fw-semibold fs-24 mb-0">Multi-Tenant Administration Dashboard</h1>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                <li class="breadcrumb-item active d-inline-flex" aria-current="page">Admin Dashboard</li>
            </ol>
        </nav>
    </div>
</div>
<!-- Page Header Close -->

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Total Tenants/Organizations</p>
                        <h3 class="mb-0 fw-semibold"><?= $totalOrganisations ?></h3>
                        <small class="text-muted fs-11">Active instances</small>
                    </div>
                    <div class="ms-2">
                        <span class="avatar avatar-md bg-primary-transparent">
                            <i class="fas fa-building fs-20"></i>
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
                        <p class="mb-1 text-muted">Total Entities</p>
                        <h3 class="mb-0 fw-semibold"><?= $totalEntities ?></h3>
                        <small class="text-muted fs-11">Registered entities</small>
                    </div>
                    <div class="ms-2">
                        <span class="avatar avatar-md bg-secondary-transparent">
                            <i class="fas fa-sitemap fs-20"></i>
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
                        <p class="mb-1 text-muted">System Administrators</p>
                        <h3 class="mb-0 fw-semibold"><?= $totalAdmins ?></h3>
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

    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Active Licenses</p>
                        <h3 class="mb-0 fw-semibold">
                            <?php
                            // Count organizations with licenses (for now, all active orgs)
                            echo $totalOrganisations;
                            ?>
                        </h3>
                        <small class="text-muted fs-11">Licensed tenants</small>
                    </div>
                    <div class="ms-2">
                        <span class="avatar avatar-md bg-warning-transparent">
                            <i class="fas fa-certificate fs-20"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Management Tabs -->
<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">
                    <h4 class="fw-semibold">Tenant & Organization Management</h4>
                </div>
                <?php if( $isSuperAdmin || $isValidAdmin){ ?>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-wave" data-bs-toggle="modal" data-bs-target="#tenantSetupWizard">
                        <i class="fas fa-plus me-2"></i>New Tenant Setup Wizard
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-wave" data-bs-toggle="modal" data-bs-target="#manageOrganisationModal" onclick="resetOrganisationModal()">
                        <i class="fas fa-building me-2"></i>Quick Add Organization
                    </button>
                </div>
                <?php } ?>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs nav-justified mb-3" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link <?= $activeTab === 'tenants' ? 'active' : '' ?>"
                           href="<?= $base ?>html/?s=core&ss=admin&p=home&tab=tenants"
                           role="tab"
                           aria-selected="<?= $activeTab === 'tenants' ? 'true' : 'false' ?>">
                            <i class="fas fa-server me-2"></i>Tenants Overview
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $activeTab === 'entities' ? 'active' : '' ?>"
                           href="<?= $base ?>html/?s=core&ss=admin&p=home&tab=entities"
                           role="tab"
                           aria-selected="<?= $activeTab === 'entities' ? 'true' : 'false' ?>">
                            <i class="fas fa-sitemap me-2"></i>Entities Management
                        </a>
                    </li>
                    <?php if($isSuperAdmin || $isValidAdmin){ ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $activeTab === 'licenses' ? 'active' : '' ?>"
                           href="<?= $base ?>html/?s=core&ss=admin&p=home&tab=licenses"
                           role="tab"
                           aria-selected="<?= $activeTab === 'licenses' ? 'true' : 'false' ?>">
                            <i class="fas fa-certificate me-2"></i>License Management
                        </a>
                    </li>
                    <?php } ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $activeTab === 'admins' ? 'active' : '' ?>"
                           href="<?= $base ?>html/?s=core&ss=admin&p=home&tab=admins"
                           role="tab"
                           aria-selected="<?= $activeTab === 'admins' ? 'true' : 'false' ?>">
                            <i class="fas fa-user-shield me-2"></i>Administrators
                        </a>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- Tenants Overview Tab -->
                    <div class="tab-pane <?= $activeTab === 'tenants' ? 'show active' : '' ?> text-muted" id="tenants-tab" role="tabpanel">
                        <?php if ($activeTab === 'tenants') include "includes/core/admin/tabs/tenants_overview.php"; ?>
                    </div>

                    <!-- Entities Management Tab -->
                    <div class="tab-pane <?= $activeTab === 'entities' ? 'show active' : '' ?> text-muted" id="entities-tab" role="tabpanel">
                        <?php if ($activeTab === 'entities') include "includes/core/admin/tabs/entities_management.php"; ?>
                    </div>

                    <!-- License Management Tab -->
                    <div class="tab-pane <?= $activeTab === 'licenses' ? 'show active' : '' ?> text-muted" id="licenses-tab" role="tabpanel">
                        <?php if ($activeTab === 'licenses') include "includes/core/admin/tabs/license_management.php"; ?>
                    </div>

                    <!-- Administrators Tab -->
                    <div class="tab-pane <?= $activeTab === 'admins' ? 'show active' : '' ?> text-muted" id="admins-tab" role="tabpanel">
                        <?php if ($activeTab === 'admins') include "includes/core/admin/tabs/administrators.php"; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$getString = str_replace("&tab={$activeTab}", "", $getString);
$getString .= "&tab={$activeTab}";
?>

<!-- Quick Actions Panel -->
<div class="row mt-4">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    <h5 class="fw-semibold">Quick Actions</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php if($isSuperAdmin || $isValidAdmin){ ?>
                    <div class="col-md-3">
                        <a href="javascript:void(0);" class="card custom-card text-center shadow-sm quick-action-card" data-bs-toggle="modal" data-bs-target="#manageOrganisationModal" onclick="resetOrganisationModal()">
                            <div class="card-body">
                                <div class="avatar avatar-lg bg-primary-transparent mx-auto mb-3">
                                    <i class="fas fa-building fs-24"></i>
                                </div>
                                <h6 class="mb-1">Add Organization</h6>
                                <small class="text-muted">Create new tenant</small>
                            </div>
                        </a>
                    </div>
                    <?php } ?>
                    <?php if($isEntityAdmin || $isSuperAdmin || $isValidAdmin){ ?>
                    <div class="col-md-3">
                        <a href="javascript:void(0);" class="card custom-card text-center shadow-sm quick-action-card" onclick="showAddEntity()">
                            <div class="card-body">
                                <div class="avatar avatar-lg bg-secondary-transparent mx-auto mb-3">
                                    <i class="fas fa-sitemap fs-24"></i>
                                </div>
                                <h6 class="mb-1">Add Entity</h6>
                                <small class="text-muted">Register new entity</small>
                            </div>
                        </a>
                    </div>
                    <?php } ?>
                    <?php if($isSuperAdmin || $isValidAdmin){ ?>
                    <div class="col-md-3">
                        <a href="javascript:void(0);" class="card custom-card text-center shadow-sm quick-action-card" data-bs-toggle="modal" data-bs-target="#manageAdmin">
                            <div class="card-body">
                                <div class="avatar avatar-lg bg-success-transparent mx-auto mb-3">
                                    <i class="fas fa-user-plus fs-24"></i>
                                </div>
                                <h6 class="mb-1">Add Administrator</h6>
                                <small class="text-muted">Assign admin access</small>
                            </div>
                        </a>
                    </div>
                    <?php } ?>
                    <?php if( $isValidAdmin){ ?>
                    <div class="col-md-3">
                        <a href="javascript:void(0);" class="card custom-card text-center shadow-sm quick-action-card" onclick="showLicenseManagement()">
                            <div class="card-body">
                                <div class="avatar avatar-lg bg-warning-transparent mx-auto mb-3">
                                    <i class="fas fa-key fs-24"></i>
                                </div>
                                <h6 class="mb-1">Assign License</h6>
                                <small class="text-muted">Manage licenses</small>
                            </div>
                        </a>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Prepare data for modals
$industrySectors = Data::industry_sectors(array("Suspended"=>'N'), false, $DBConn);
$countries = Data::countries(array(), false, $DBConn);
$entityTypes = Data::entity_types(array(), false, $DBConn);
$adminTypes = Admin::admin_types(array(), false, $DBConn);
$users = Core::user([], false, $DBConn);

// Tenant Setup Wizard Modal
include "includes/core/admin/modals/tenant_setup_wizard.php";

// Modal for adding Organisation
echo Utility::form_modal_header("manageOrganisationModal", "global/manage_organisation_details.php", "Add Organisation", array('modal-md', 'modal-dialog-centered', "modal-xl"), $base);
include "includes/core/admin/organisation/modals/manage_organisation.php";
echo Utility::form_modal_footer('Save Organisation');

// Modal for adding Entity
echo Utility::form_modal_header("manageEntity", "tax/admin/manage_entity.php", "Add Organization Entity", array('modal-md', 'modal-dialog-centered', "modal-xl"), $base);
include "includes/scripts/global/modals/manage_entity.php";
echo Utility::form_modal_footer('Save Entity');

// Delete Confirmation Modal
echo Utility::form_modal_header(
    "deleteEntityModal",
    "tax/admin/manage_entity.php",
    "Delete Organisation Entity",
    array('modal-lg', 'modal-dialog-centered'),
    $base
);
?>
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="entityID" id="entityID">
    <p class="font-18"> Are you sure you want to delete the entity "<span id="entityNameToDelete" class="fw-bold text-danger"></span>"?
    This action cannot be undone.</p>
<?= Utility::form_modal_footer('Yes, Delete Entity', 'deleteConfirmed',  ' btn btn-danger', true);  ?>

<?php
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
.quick-action-card {
    transition: all 0.3s ease;
    text-decoration: none;
    border: 1px solid rgba(0,0,0,0.1);
}

.quick-action-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
    cursor: pointer;
}

.nav-tabs .nav-link {
    font-weight: 500;
}

.nav-tabs .nav-link.active {
    font-weight: 600;
}

.card-header h4, .card-header h5 {
    margin-bottom: 0;
}

.stats-card {
    border-left: 4px solid;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-active {
    background-color: #e8f5e9;
    color: #2e7d32;
}

.status-inactive {
    background-color: #ffebee;
    color: #c62828;
}

.status-pending {
    background-color: #fff3e0;
    color: #ef6c00;
}
</style>

<script>
// JavaScript functions for UI interactions
function showAddEntity() {
    // Navigate to entities tab
    window.location.href = '<?= $base ?>html/?s=core&ss=admin&p=home&tab=entities';
}

function showLicenseManagement() {
    // Navigate to licenses tab
    window.location.href = '<?= $base ?>html/?s=core&ss=admin&p=home&tab=licenses';
}

// Helper function to build URL with tab parameter
function navigateToTab(tabName) {
    const baseUrl = '<?= $base ?>html/';
    const params = new URLSearchParams(window.location.search);
    params.set('tab', tabName);
    // Keep existing parameters
    if (!params.has('s')) params.set('s', 'core');
    if (!params.has('ss')) params.set('ss', 'admin');
    if (!params.has('p')) params.set('p', 'home');

    window.location.href = baseUrl + '?' + params.toString();
}

// Helper function to reset organization modal
function resetOrganisationModal() {
    const modal = document.querySelector('#manageOrganisationModal');

    if (modal) {
        modal.querySelector('.modal-title').textContent = 'Add Organisation';
        const form = modal.querySelector('form');
        if (form) form.reset();

        const orgDataIDInput = modal.querySelector('#orgDataID');
        if (orgDataIDInput) orgDataIDInput.value = '';
    }
}

// Delete entity confirmation
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete entity buttons
    document.querySelectorAll('.deleteEntity').forEach(btn => {
        btn.addEventListener('click', function() {
            const entityId = this.getAttribute('data-id');
            const entityName = this.getAttribute('data-entity-name');

            document.getElementById('entityID').value = entityId;
            document.getElementById('entityNameToDelete').textContent = entityName;
        });
    });

    // Handle edit entity buttons
    document.querySelectorAll('.editEntity').forEach(btn => {
        btn.addEventListener('click', function() {
            const data = this.dataset;
            const modal = document.querySelector('#manageEntity');

            if (modal) {
                // Populate form fields
                const fields = [
                    'entityID', 'entityName', 'entityDescription', 'entityTypeID',
                    'orgDataID', 'entityParentID', 'industrySectorID',
                    'registrationNumber', 'entityPIN', 'entityCity',
                    'entityCountry', 'entityPhoneNumber', 'entityEmail'
                ];

                fields.forEach(field => {
                    const input = modal.querySelector(`[name="${field}"]`);
                    const dataKey = field.replace(/([A-Z])/g, '-$1').toLowerCase();
                    if (input && data[dataKey]) {
                        input.value = data[dataKey];
                    }
                });
            }
        });
    });
});
</script>
