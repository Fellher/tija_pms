<?php
$getString .="&state={$state}";

// Check admin access
if(!$isAdmin && !$isValidAdmin) {
    Alert::info("You need to be logged in as a valid administrator to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    return;
}


// Fetch organisation details
$orgFilter = array("suspended" => 'N');
if(isset($orgDataID) && !empty($orgDataID)) {
    $orgFilter['orgDataID'] = $orgDataID;
}
$OrganisationDetails = Admin::org_data($orgFilter, true, $DBConn);
$orgDetails = $OrganisationDetails;

// Validate organisation details
if (!$orgDetails || !is_object($orgDetails) || !isset($orgDetails->orgDataID)) {
    Alert::danger("Unable to load organisation details. Please ensure your organisation is properly configured.", true, array('fst-italic', 'text-center', 'font-18'));
    return;
}

// Fetch organisation entities
$organisationEntities = Data::entities_full(['orgDataID'=> $orgDetails->orgDataID, 'Suspended'=> 'N'], false, $DBConn);

// Check if no entities exist
if(!$organisationEntities) {?>
    <!-- Empty State: No Entities -->
    <div class="row">
        <div class="col-12">
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title mb-0">
                        <h5 class="mb-0"><i class="fas fa-building me-2"></i><?= htmlspecialchars($orgDetails->orgName) ?></h5>
                    </div>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#manageOrgStructure">
                        <i class="ri-add-line me-1"></i>Add Entity
                    </button>
                </div>
                <div class="card-body">
                    <div class="text-center py-5">
                        <div class="avatar avatar-xl bg-primary-transparent mx-auto mb-3">
                            <i class="fas fa-sitemap fs-40"></i>
                        </div>
                        <h5 class="mb-2">No Organization Structure</h5>
                        <p class="text-muted mb-4">Get started by creating your first entity to build your organizational structure.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manageOrgStructure">
                            <i class="ri-add-circle-line me-2"></i>Create First Entity
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    echo Utility::form_modal_header("manageOrgStructure", "global/admin/organisation/manage_entity_unit.php", "Add New Entity", array("modal-lg", "modal-dialog-centered"), $base);
    include "includes/core/admin/organisation/modals/manage_organisation_units.php";
    echo Utility::form_modal_footer("Save Entity", "submit_entity", "btn btn-success btn-sm");
    return;
}
// Get selected entity ID from URL
$base = $base ?? '';
$entityID = isset($_GET['entityid']) ? Utility::clean_string($_GET['entityid']) : '';

// If only one entity exists, select it automatically
$entityDetails = null;
if($entityID) {
    $entityDetails = Data::entities_full(['entityID'=> $entityID], true, $DBConn);
} elseif(count($organisationEntities) === 1) {
    $entityDetails = $organisationEntities[0];
    $entityID = $entityDetails->entityID;
}

// Scenario 1: No specific entity selected - Show entity hierarchy
if(!$entityDetails) {

    ?>
    <!-- Entity Hierarchy View -->
    <div class="row">
        <div class="col-12">
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title mb-0">
                        <h5 class="mb-0"><i class="fas fa-sitemap me-2"></i>Organization Entities</h5>
                    </div>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#manageOrgStructure">
                        <i class="ri-add-line me-1"></i>Add Entity
                    </button>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Select an entity below to view and manage its organizational units.
                    </div>

                    <?php
                    // Build hierarchical structure
                    function buildEntityHierarchy($entities, $parentId = 0) {
                        $hierarchy = [];
                        foreach ($entities as $entity) {
                            if (($entity->entityParentID ?? 0) == $parentId) {
                                $children = buildEntityHierarchy($entities, $entity->entityID);
                                if ($children) {
                                    $entity->children = $children;
                                }
                                $hierarchy[] = $entity;
                            }
                        }
                        return $hierarchy;
                    }

                    // Function to display the entity hierarchy
                    function displayEntityHierarchy($hierarchy, $level = 0, $base = '', $getString = '') {
                        foreach ($hierarchy as $entity) {
                            $indent = $level * 30;
                            ?>
                            <div class="entity-item mb-2" style="margin-left: <?= $indent ?>px;">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                            <div class="d-flex align-items-center flex-grow-1">
                                                <?php if($level > 0): ?>
                                                    <i class="fas fa-level-up-alt fa-rotate-90 me-3 text-muted"></i>
                                                <?php endif; ?>
                                                <div class="me-3">
                                                    <span class="avatar avatar-md bg-primary-transparent text-primary">
                                                        <i class="fas fa-building"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1 fw-semibold"><?= htmlspecialchars($entity->entityName) ?></h6>
                                                    <div class="d-flex gap-2 flex-wrap">
                                                        <span class="badge bg-primary-transparent">
                                                            <i class="ri-building-line me-1"></i><?= htmlspecialchars($entity->entityTypeTitle ?? 'N/A') ?>
                                                        </span>
                                                        <span class="badge bg-secondary-transparent">
                                                            <i class="ri-map-pin-line me-1"></i><?= htmlspecialchars($entity->entityCity ?? '') ?>, <?= htmlspecialchars($entity->countryName ?? '') ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex gap-2">
                                                <a href="<?= $base ?>html/<?= $getString ?>&entityid=<?= $entity->entityID ?>"
                                                   class="btn btn-sm btn-primary-light"
                                                   data-bs-toggle="tooltip"
                                                   title="View Units">
                                                    <i class="ri-arrow-right-circle-line"></i>
                                                </a>
                                                <button class="btn btn-sm btn-info-light"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#entityDetails<?= $entity->entityID ?>"
                                                        title="View Details">
                                                    <i class="ri-eye-line"></i>
                                                </button>
                                                <button class="btn btn-sm btn-success-light orgStructureModal"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#manageOrgStructure"
                                                        data-entity-id="<?= $entity->entityID ?>"
                                                        title="Edit Entity">
                                                    <i class="ri-edit-line"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Collapsible Details -->
                                        <div class="collapse mt-3" id="entityDetails<?= $entity->entityID ?>">
                                            <div class="border-top pt-3">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <p class="mb-2"><strong>Registration Number:</strong> <?= htmlspecialchars($entity->registrationNumber ?? 'N/A') ?></p>
                                                        <p class="mb-2"><strong>Entity PIN:</strong> <?= htmlspecialchars($entity->entityPIN ?? 'N/A') ?></p>
                                                        <p class="mb-0"><strong>Industry:</strong> <?= htmlspecialchars($entity->industryTitle ?? 'N/A') ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p class="mb-2"><strong>Phone:</strong> <?= htmlspecialchars($entity->entityPhoneNumber ?? 'N/A') ?></p>
                                                        <p class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($entity->entityEmail ?? 'N/A') ?></p>
                                                        <p class="mb-0"><strong>Description:</strong> <?= htmlspecialchars($entity->entityDescription ?? 'N/A') ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                            if (isset($entity->children)) {
                                displayEntityHierarchy($entity->children, $level + 1, $base, $getString);
                            }
                        }
                    }

                    // Build and display the hierarchy
                    $hierarchicalStructure = buildEntityHierarchy($organisationEntities);
                    displayEntityHierarchy($hierarchicalStructure, 0, $base, $getString);
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    echo Utility::form_modal_header("manageOrgStructure", "global/admin/organisation/manage_entity_unit.php", "Manage Entity", array("modal-lg", "modal-dialog-centered"), $base);
    include "includes/core/admin/organisation/modals/manage_organisation_units.php";
    echo Utility::form_modal_footer("Save Entity", "submit_entity", "btn btn-success btn-sm");

// Scenario 2: Entity selected - Show units for that entity
} else {
    $getString .="&entityid={$entityDetails->entityID}";

    // Fetch units for this entity
    $units = Data::units(array('Suspended'=>"N", 'entityID'=> $entityDetails->entityID), false, $DBConn);
    ?>

    <!-- Entity Units View -->
    <div class="row">
        <div class="col-12">
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title mb-0">
                        <h5 class="mb-0">
                            <i class="fas fa-sitemap me-2"></i><?= htmlspecialchars($entityDetails->entityName) ?>
                        </h5>
                        <small class="text-muted"><?= htmlspecialchars($entityDetails->entityDescription ?? '') ?></small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= $base ?>html/<?= str_replace("&entityid={$entityDetails->entityID}", "", $getString) ?>"
                           class="btn btn-sm btn-secondary">
                            <i class="ri-arrow-left-line me-1"></i>Back to Entities
                        </a>
                        <button class="btn btn-sm btn-primary orgStructureModal"
                                data-bs-toggle="modal"
                                data-bs-target="#manageOrgStructure">
                            <i class="ri-add-line me-1"></i>Add Unit
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php
                    if($units) {
                        ?>
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            This displays the hierarchical structure of units within <strong><?= htmlspecialchars($entityDetails->entityName) ?></strong>.
                        </div>

                        <?php
                        // Create array of units indexed by parentUnitID
                        $unitsByParent = array();
                        foreach($units as $unit) {
                            $parentId = $unit->parentUnitID ?: '0';
                            if(!isset($unitsByParent[$parentId])) {
                                $unitsByParent[$parentId] = array();
                            }
                            $unitsByParent[$parentId][] = $unit;
                        }

                        // Recursive function to display units hierarchically
                        function displayUnitHierarchy($unitId, $unitsByParent, $DBConn, $level = 0) {
                            if(!isset($unitsByParent[$unitId])) {
                                return;
                            }

                            foreach($unitsByParent[$unitId] as $unit) {
                                $indent = $level * 30;
                                $headOfUnit = Core::user_name($unit->headOfUnitID, $DBConn);
                                ?>
                                <div class="unit-item mb-2" style="margin-left: <?= $indent ?>px;">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                                <div class="d-flex align-items-center flex-grow-1">
                                                    <?php if($level > 0): ?>
                                                        <i class="fas fa-level-up-alt fa-rotate-90 me-3 text-muted"></i>
                                                    <?php endif; ?>
                                                    <div class="me-3">
                                                        <span class="avatar avatar-md bg-success-transparent text-success">
                                                            <i class="fas fa-folder-tree"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-1 fw-semibold"><?= htmlspecialchars($unit->unitName) ?></h6>
                                                        <div class="d-flex gap-2 flex-wrap">
                                                            <?php if($unit->unitTypeName ?? false): ?>
                                                                <span class="badge bg-success-transparent">
                                                                    <i class="ri-folder-line me-1"></i><?= htmlspecialchars($unit->unitTypeName) ?>
                                                                </span>
                                                            <?php endif; ?>
                                                            <?php if($headOfUnit): ?>
                                                                <span class="badge bg-info-transparent">
                                                                    <i class="ri-user-line me-1"></i>Head: <?= htmlspecialchars($headOfUnit) ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-sm btn-primary-light orgStructureModal"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#manageOrgStructure"
                                                            data-unit-id="<?= $unit->unitID ?>"
                                                            data-unit-code="<?= htmlspecialchars($unit->unitCode ?? '') ?>"
                                                            data-unit-name="<?= htmlspecialchars($unit->unitName) ?>"
                                                            data-unit-type="<?= $unit->unitTypeID ?? '' ?>"
                                                            data-head-of-unit="<?= $unit->headOfUnitID ?? '' ?>"
                                                            data-parent-unit="<?= $unit->parentUnitID ?? '' ?>"
                                                            data-unit-description="<?= htmlspecialchars($unit->unitDescription ?? '') ?>"
                                                            title="Edit Unit">
                                                        <i class="ri-edit-line"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger-light deleteOrgUnit"
                                                            data-unit-id="<?= $unit->unitID ?>"
                                                            data-unit-name="<?= htmlspecialchars($unit->unitName) ?>"
                                                            title="Delete Unit">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                // Recursively display child units
                                displayUnitHierarchy($unit->unitID, $unitsByParent, $DBConn, $level + 1);
                            }
                        }

                        // Display units starting with top-level (parentUnitID = 0)
                        displayUnitHierarchy('0', $unitsByParent, $DBConn);

                    } else {
                        ?>
                        <div class="text-center py-5">
                            <div class="avatar avatar-xl bg-secondary-transparent mx-auto mb-3">
                                <i class="fas fa-folder-tree fs-40"></i>
                            </div>
                            <h5 class="mb-2">No Units Found</h5>
                            <p class="text-muted mb-4">Create your first organizational unit to get started.</p>
                            <button class="btn btn-primary orgStructureModal"
                                    data-bs-toggle="modal"
                                    data-bs-target="#manageOrgStructure">
                                <i class="ri-add-circle-line me-2"></i>Create First Unit
                            </button>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <?php
    echo Utility::form_modal_header("manageOrgStructure", "global/admin/organisation/manage_entity_unit.php", "Manage Unit", array("modal-lg", "modal-dialog-centered"), $base);
    include "includes/core/admin/organisation/modals/manage_organisation_units.php";
    echo Utility::form_modal_footer("Save Unit", "submit_unit", "btn btn-success btn-sm");
}
?>

<!-- JavaScript for Organization Structure -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Handle edit button clicks
    const editButtons = document.querySelectorAll('.orgStructureModal');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const form = document.querySelector('#manageOrgStructure form');
            if(!form) {
                console.error('Form not found');
                return;
            }

            const data = this.dataset;

            // Check if editing unit or entity
            if(data.unitId) {
                // Editing a unit
                const unitId = data.unitId;
                const unitCode = data.unitCode || '';
                const unitName = data.unitName || '';
                const unitType = data.unitType || '';
                const headOfUnit = data.headOfUnit || '';
                const parentUnit = data.parentUnit || '';
                const unitDescription = data.unitDescription || '';

                // Set form values
                if(form.querySelector('input[name="unitID"]')) {
                    form.querySelector('input[name="unitID"]').value = unitId;
                }
                if(form.querySelector('input[name="unitCode"]')) {
                    form.querySelector('input[name="unitCode"]').value = unitCode;
                }
                if(form.querySelector('input[name="unitName"]')) {
                    form.querySelector('input[name="unitName"]').value = unitName;
                }
                if(form.querySelector('select[name="unitType"]')) {
                    form.querySelector('select[name="unitType"]').value = unitType;
                }
                if(form.querySelector('select[name="headOfUnit"]')) {
                    form.querySelector('select[name="headOfUnit"]').value = headOfUnit;
                }
                if(form.querySelector('select[name="parentUnitID"]')) {
                    form.querySelector('select[name="parentUnitID"]').value = parentUnit;
                }

                // Set tinymce content
                if(typeof tinymce !== 'undefined' && tinymce.get('unitDescription')) {
                    tinymce.get('unitDescription').setContent(unitDescription);
                } else if(form.querySelector('textarea[name="unitDescription"]')) {
                    form.querySelector('textarea[name="unitDescription"]').value = unitDescription;
                }
            } else if(data.entityId) {
                // Editing an entity
                // Add entity editing logic here if needed
                console.log('Editing entity:', data.entityId);
            }
        });
    });

    // Handle delete button clicks
    const deleteButtons = document.querySelectorAll('.deleteOrgUnit');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            const unitId = this.getAttribute('data-unit-id');
            const unitName = this.getAttribute('data-unit-name');

            if (confirm(`Are you sure you want to suspend the unit "${unitName}"?\n\nThis unit will no longer be active.`)) {
                // Create and submit form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = siteUrl + 'php/scripts/global/admin/organisation/delete_entity_unit.php';

                const unitInput = document.createElement('input');
                unitInput.type = 'hidden';
                unitInput.name = 'unitID';
                unitInput.value = unitId;
                form.appendChild(unitInput);

                const suspendedInput = document.createElement('input');
                suspendedInput.type = 'hidden';
                suspendedInput.name = 'Suspended';
                suspendedInput.value = 'Y';
                form.appendChild(suspendedInput);

                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
