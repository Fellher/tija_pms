<?php
// Entities Management Tab Content
// $organisations = Admin::org_data(array(), false, $DBConn);

// Define the function once outside the loop to prevent redeclaration
if (!function_exists('displayEntityRow')) {
    function displayEntityRow($entity, $entities, $level = 0) {
        $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
        ?>
        <tr class="entity-row" data-entity-id="<?= $entity->entityID ?>" data-level="<?= $level ?>">
            <td>
                <?= $indent ?>
                <?php if ($level > 0): ?>
                    <i class="fas fa-level-up-alt fa-rotate-90 text-muted me-1"></i>
                <?php endif; ?>
                <strong><?= htmlspecialchars($entity->entityName) ?></strong>
                <?php if ($level == 0): ?>
                    <span class="badge bg-primary-transparent ms-2">Parent</span>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($entity->entityTypeTitle ?? 'N/A') ?></td>
            <td>
                <small><?= htmlspecialchars($entity->registrationNumber) ?></small><br>
                <small class="text-muted"><?= htmlspecialchars($entity->entityPIN) ?></small>
            </td>
            <td>
                <?= htmlspecialchars($entity->countryName ?? 'N/A') ?><br>
                <small class="text-muted"><?= htmlspecialchars($entity->entityCity) ?></small>
            </td>
            <td><?= htmlspecialchars($entity->industryTitle ?? 'N/A') ?></td>
            <td class="text-center">
                <div class="btn-group" role="group">
                    <button
                        class="btn btn-sm btn-info-light btn-wave editEntity"
                        data-bs-toggle="modal"
                        data-bs-target="#manageEntity"
                        data-id="<?= $entity->entityID ?>"
                        data-entity-name="<?= htmlspecialchars($entity->entityName) ?>"
                        data-entity-description="<?= htmlspecialchars($entity->entityDescription ?? '') ?>"
                        data-entity-type-id="<?= $entity->entityTypeID ?>"
                        data-org-data-id="<?= $entity->orgDataID ?>"
                        data-entity-parent-id="<?= $entity->entityParentID ?>"
                        data-industry-sector-id="<?= $entity->industrySectorID ?>"
                        data-registration-number="<?= htmlspecialchars($entity->registrationNumber) ?>"
                        data-entity-pin="<?= htmlspecialchars($entity->entityPIN) ?>"
                        data-entity-city="<?= htmlspecialchars($entity->entityCity) ?>"
                        data-entity-country="<?= $entity->entityCountry ?>"
                        data-entity-phone-number="<?= $entity->entityPhoneNumber ?>"
                        data-entity-email="<?= htmlspecialchars($entity->entityEmail) ?>"
                        title="Edit Entity">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button
                        class="btn btn-sm btn-danger-light btn-wave deleteEntity"
                        data-bs-toggle="modal"
                        data-id="<?= $entity->entityID ?>"
                        data-entity-name="<?= htmlspecialchars($entity->entityName) ?>"
                        data-bs-target="#deleteEntityModal"
                        title="Delete Entity">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
        <?php
        // Display child entities
        foreach ($entities as $childEntity) {
            if ($childEntity->entityParentID == $entity->entityID) {
                displayEntityRow($childEntity, $entities, $level + 1);
            }
        }
    }
}

if (!$organisations) {
    Alert::info("No organisations set up. Please create an organisation first.", true, array('fst-italic', 'text-center', 'font-18'));
} else {
    $entityTypes = Data::entity_types(array(), false, $DBConn);
    $african_countries = Data::countries([], false, $DBConn);
    $industrySectors = Data::industry_sectors(["Suspended" => 'N'], false, $DBConn);

    foreach ($organisations as $key => $organisation) {
        ?>
        <div class="card custom-card mb-3 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-light">
                <div>
                    <h5 class="mb-1 fw-semibold">
                        <i class="fas fa-building text-primary me-2"></i>
                        <?= htmlspecialchars($organisation->orgName) ?>
                    </h5>
                    <small class="text-muted">
                        <i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($organisation->orgCity ?? 'N/A') ?>
                    </small>
                </div>
                <button type="button"
                    class="btn btn-primary btn-sm btn-wave manageEntityOrganisation"
                    data-bs-toggle="modal"
                    data-organisationId="<?= $organisation->orgDataID ?>"
                    data-bs-target="#manageEntity">
                    <i class="fas fa-plus me-1"></i>Add New Entity
                </button>
            </div>
            <div class="card-body">
                <?php
                $entities = Data::entities_full(['orgDataID' => $organisation->orgDataID, 'Suspended' => 'N'], false, $DBConn);

                if ($entities) {
                    // Sort entities by entityParentID
                    usort($entities, function($a, $b) {
                        return $a->entityParentID <=> $b->entityParentID;
                    });
                    ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 25%;">Entity Name</th>
                                    <th style="width: 15%;">Type</th>
                                    <th style="width: 15%;">Registration</th>
                                    <th style="width: 20%;">Location</th>
                                    <th style="width: 15%;">Industry</th>
                                    <th class="text-center" style="width: 10%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Display parent entities first (entityParentID = 0)
                                foreach ($entities as $entity) {
                                    if ($entity->entityParentID == 0) {
                                        displayEntityRow($entity, $entities);
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <?php
                } else {
                    Alert::info("No entities set up for {$organisation->orgName}. Click 'Add New Entity' to create one.", true, array('fst-italic', 'text-center', 'font-14'));
                }
                ?>
            </div>
        </div>
        <?php
    }
}
?>

<style>
.entity-row[data-level="0"] {
    background-color: #f8f9fa;
    font-weight: 500;
}

.entity-row[data-level="1"] {
    background-color: #ffffff;
}

.entity-row[data-level="2"] {
    background-color: #f8f9fa;
}

.entity-row:hover {
    background-color: #e3f2fd !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle entity organisation button click
    document.querySelectorAll('.manageEntityOrganisation').forEach(btn => {
        btn.addEventListener('click', function() {
            const orgId = this.getAttribute('data-organisationId');
            const modal = document.querySelector('#manageEntity');

            if (modal) {
                // Reset form
                modal.querySelector('form')?.reset();

                // Set organisation ID
                const orgInput = modal.querySelector('select[name="orgDataID"]');
                if (orgInput) {
                    orgInput.value = orgId;
                }

                // Clear entity ID (for new entity)
                const entityIdInput = modal.querySelector('input[name="entityID"]');
                if (entityIdInput) {
                    entityIdInput.value = '';
                }
            }
        });
    });
});
</script>