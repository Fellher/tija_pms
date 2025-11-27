<!-- Restricted Edit Administrator Modal -->
<!-- Only allows editing: Admin Role Type, Entity, Unit, and Options -->
<!-- Does NOT allow changing the person (userID) -->

<style>
/* Restricted Edit Modal Styles */
#editAdminRestricted .modal-dialog {
    max-height: 90vh;
    margin: 1.75rem auto;
}

#editAdminRestricted .modal-content {
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

#editAdminRestricted .modal-body {
    overflow-y: auto;
    max-height: calc(90vh - 140px);
    flex: 1 1 auto;
}

#editAdminRestricted .modal-header,
#editAdminRestricted .modal-footer {
    flex-shrink: 0;
}

/* Info Alert */
.admin-info-alert {
    border-left: 4px solid #667eea;
    background-color: #f0f4ff;
    border-radius: 8px;
}

/* Read-only User Info */
.user-info-display {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 1rem;
}

.user-info-display .user-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    font-size: 1.2rem;
}

/* Section Cards */
.edit-section-card {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    transition: all 0.3s ease;
    margin-bottom: 1rem;
}

.edit-section-card:hover {
    border-color: #667eea;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
}

.section-header {
    display: flex;
    align-items-center;
    margin-bottom: 1rem;
}

.section-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    margin-right: 0.75rem;
    background-color: rgba(102, 126, 234, 0.1);
    color: #667eea;
}

/* Disabled/Locked Fields */
.locked-field {
    background-color: #f8f9fa;
    border-color: #dee2e6;
    cursor: not-allowed;
    opacity: 0.7;
}

.locked-field::placeholder {
    color: #6c757d;
}

.lock-icon {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}
</style>

<!-- Hidden Fields -->
<input type="hidden" name="adminID" id="editAdminID">
<input type="hidden" name="userID" id="editUserID">
<input type="hidden" name="action" value="update_restricted">

<!-- Info Alert -->
<div class="alert admin-info-alert mb-4">
    <div class="d-flex align-items-start">
        <i class="fas fa-info-circle text-primary me-2 mt-1"></i>
        <div>
            <strong>Restricted Edit Mode</strong>
            <p class="mb-0 small">You can only edit the administrator's role, entity, unit, and options. The person assigned as administrator cannot be changed.</p>
        </div>
    </div>
</div>

<!-- Current Administrator Info (Read-Only) -->
<div class="card edit-section-card mb-4">
    <div class="card-body">
        <div class="section-header">
            <div class="section-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <div>
                <h6 class="mb-0">Current Administrator</h6>
                <small class="text-muted">This information cannot be changed</small>
            </div>
        </div>

        <div class="user-info-display">
            <div class="d-flex align-items-center">
                <div class="user-avatar" id="editUserAvatar">
                    <!-- Populated by JavaScript -->
                </div>
                <div class="ms-3 flex-fill">
                    <h6 class="mb-0" id="editUserName">
                        <!-- Populated by JavaScript -->
                    </h6>
                    <small class="text-muted">
                        <i class="fas fa-envelope me-1"></i>
                        <span id="editUserEmail">
                            <!-- Populated by JavaScript -->
                        </span>
                    </small>
                    <br>
                    <small class="text-muted">
                        <i class="fas fa-building me-1"></i>
                        <span id="editUserOrg">
                            <!-- Populated by JavaScript -->
                        </span>
                    </small>
                </div>
                <div class="text-end">
                    <span class="badge bg-secondary-transparent">
                        <i class="fas fa-lock me-1"></i>Locked
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Editable Fields -->
<div class="row">
    <!-- Admin Role Type -->
    <div class="col-md-6 mb-3">
        <div class="card edit-section-card">
            <div class="card-body">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-user-tag"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">Admin Role Type</h6>
                        <small class="text-muted">Change the administrator's role</small>
                    </div>
                </div>

                <label for="editAdminTypeID" class="form-label">
                    Admin Role/Type <span class="text-danger">*</span>
                </label>
                <select name="adminTypeID" id="editAdminTypeID" class="form-select" required>
                    <option value="">Select Admin Type</option>
                    <?php if (isset($adminTypes) && $adminTypes): ?>
                        <?php foreach ($adminTypes as $role): ?>
                            <option value="<?= $role->adminTypeID ?>">
                                <?= htmlspecialchars($role->adminTypeName) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <small class="form-text text-muted">
                    Super Admin: Full access | System Admin: Org-level | Entity Admin: Entity-level
                </small>
            </div>
        </div>
    </div>

    <!-- Organization (Read-Only) -->
    <div class="col-md-6 mb-3">
        <div class="card edit-section-card">
            <div class="card-body">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">Organization</h6>
                        <small class="text-muted">Cannot be changed</small>
                    </div>
                </div>

                <label for="editOrgDataID" class="form-label">
                    Organization
                </label>
                <div class="position-relative">
                    <select name="orgDataID" id="editOrgDataID" class="form-select locked-field" disabled>
                        <option value="">Select Organization</option>
                        <?php if (isset($organisations) && $organisations): ?>
                            <?php foreach ($organisations as $org): ?>
                                <option value="<?= $org->orgDataID ?>">
                                    <?= htmlspecialchars($org->orgName) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <i class="fas fa-lock lock-icon"></i>
                </div>
                <input type="hidden" name="orgDataID" id="editOrgDataIDHidden">
                <small class="form-text text-muted">
                    Organization assignment cannot be changed
                </small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Entity -->
    <div class="col-md-6 mb-3">
        <div class="card edit-section-card">
            <div class="card-body">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-sitemap"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">Entity</h6>
                        <small class="text-muted">Change the entity assignment</small>
                    </div>
                </div>

                <label for="editEntityID" class="form-label">
                    Entity <small class="text-muted">(Optional)</small>
                </label>
                <select name="entityID" id="editEntityID" class="form-select">
                    <option value="">All Entities</option>
                    <!-- Populated dynamically based on organization -->
                </select>
                <small class="form-text text-muted">
                    Select a specific entity or leave blank for all entities
                </small>
            </div>
        </div>
    </div>

    <!-- Unit Type -->
    <div class="col-md-6 mb-3">
        <div class="card edit-section-card">
            <div class="card-body">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">Unit Type</h6>
                        <small class="text-muted">Change the unit type</small>
                    </div>
                </div>

                <label for="editUnitTypeID" class="form-label">
                    Unit Type <small class="text-muted">(Optional)</small>
                </label>
                <select name="unitTypeID" id="editUnitTypeID" class="form-select">
                    <option value="">Select Unit Type</option>
                    <!-- Populated dynamically if needed -->
                </select>
                <small class="form-text text-muted">
                    Select a unit type for this administrator
                </small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Unit -->
    <div class="col-md-6 mb-3">
        <div class="card edit-section-card">
            <div class="card-body">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-cube"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">Unit</h6>
                        <small class="text-muted">Change the unit assignment</small>
                    </div>
                </div>

                <label for="editUnitID" class="form-label">
                    Unit <small class="text-muted">(Optional)</small>
                </label>
                <select name="unitID" id="editUnitID" class="form-select">
                    <option value="">Select Unit</option>
                    <!-- Populated dynamically based on entity -->
                </select>
                <small class="form-text text-muted">
                    Select a specific unit or leave blank
                </small>
            </div>
        </div>
    </div>

    <!-- Options -->
    <div class="col-md-6 mb-3">
        <div class="card edit-section-card">
            <div class="card-body">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div>
                        <h6 class="mb-0">Options</h6>
                        <small class="text-muted">Additional settings</small>
                    </div>
                </div>

                <label class="form-label">Options</label>
                <div class="form-check mb-2">
                    <input class="form-check-input"
                           type="checkbox"
                           name="isEmployee"
                           id="editIsEmployee"
                           value="Y">
                    <label class="form-check-label" for="editIsEmployee">
                        User is also an employee
                    </label>
                </div>
                <small class="form-text text-muted">
                    Check if this administrator is also an employee in the system
                </small>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    // Get user initials
    function getInitials(firstName, lastName) {
        const first = firstName ? firstName.charAt(0).toUpperCase() : '';
        const last = lastName ? lastName.charAt(0).toUpperCase() : '';
        return first + last;
    }

    // Load entities for organization
    function loadEntitiesForOrg(orgId) {
        if (!orgId) {
            document.getElementById('editEntityID').innerHTML = '<option value="">All Entities</option>';
            return;
        }

        const entitySelect = document.getElementById('editEntityID');
        entitySelect.innerHTML = '<option value="">Loading...</option>';

        fetch('<?= $base ?>php/scripts/global/admin/get_entities_for_org.php?orgDataID=' + orgId)
            .then(response => response.json())
            .then(data => {
                entitySelect.innerHTML = '<option value="">All Entities</option>';

                if (data && data.success && Array.isArray(data.entities) && data.entities.length > 0) {
                    data.entities.forEach(entity => {
                        if (entity && entity.entityID && entity.entityName) {
                            const option = document.createElement('option');
                            option.value = entity.entityID;
                            option.textContent = entity.entityName;
                            entitySelect.appendChild(option);
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error loading entities:', error);
                entitySelect.innerHTML = '<option value="">Error loading entities</option>';
            });
    }

    // Load units for entity
    function loadUnitsForEntity(entityId) {
        if (!entityId) {
            document.getElementById('editUnitID').innerHTML = '<option value="">Select Unit</option>';
            return;
        }

        const unitSelect = document.getElementById('editUnitID');
        unitSelect.innerHTML = '<option value="">Loading...</option>';

        fetch('<?= $base ?>php/scripts/global/admin/get_units_for_entity.php?entityID=' + entityId)
            .then(response => response.json())
            .then(data => {
                unitSelect.innerHTML = '<option value="">Select Unit</option>';

                if (data && data.success && Array.isArray(data.units) && data.units.length > 0) {
                    data.units.forEach(unit => {
                        if (unit && unit.unitID && unit.unitName) {
                            const option = document.createElement('option');
                            option.value = unit.unitID;
                            option.textContent = unit.unitName;
                            unitSelect.appendChild(option);
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error loading units:', error);
                unitSelect.innerHTML = '<option value="">Error loading units</option>';
            });
    }

    // Edit button handler
    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.edit_admin_restricted');

        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const modal = document.querySelector('#editAdminRestricted');
                if (!modal) return;

                const data = this.dataset;

                // Set hidden fields
                document.getElementById('editAdminID').value = data.adminId || '';
                document.getElementById('editUserID').value = data.userId || '';

                // Display user info (read-only)
                const userName = data.adminName || 'Unknown';
                const userEmail = data.adminEmail || '';
                const userOrg = data.orgName || '';
                const initials = getInitials(
                    userName.split(' ')[0] || '',
                    userName.split(' ')[1] || ''
                );

                document.getElementById('editUserAvatar').textContent = initials;
                document.getElementById('editUserName').textContent = userName;
                document.getElementById('editUserEmail').textContent = userEmail;
                document.getElementById('editUserOrg').textContent = userOrg;

                // Set organization (read-only, but sync hidden field)
                const orgSelect = document.getElementById('editOrgDataID');
                const orgHidden = document.getElementById('editOrgDataIDHidden');
                if (orgSelect && data.orgDataId) {
                    orgSelect.value = data.orgDataId;
                    orgHidden.value = data.orgDataId;

                    // Load entities for this organization
                    loadEntitiesForOrg(data.orgDataId);
                }

                // Set editable fields
                if (data.adminTypeId) {
                    document.getElementById('editAdminTypeID').value = data.adminTypeId;
                }
                if (data.entityId) {
                    setTimeout(() => {
                        document.getElementById('editEntityID').value = data.entityId;
                        // Load units for this entity
                        loadUnitsForEntity(data.entityId);
                    }, 500);
                }
                if (data.unitTypeId) {
                    document.getElementById('editUnitTypeID').value = data.unitTypeId;
                }
                if (data.unitId) {
                    setTimeout(() => {
                        document.getElementById('editUnitID').value = data.unitId;
                    }, 1000);
                }
                if (data.isEmployee === 'Y' || data.isEmployee === '1') {
                    document.getElementById('editIsEmployee').checked = true;
                } else {
                    document.getElementById('editIsEmployee').checked = false;
                }
            });
        });

        // Entity change handler - load units
        const entitySelect = document.getElementById('editEntityID');
        if (entitySelect) {
            entitySelect.addEventListener('change', function() {
                loadUnitsForEntity(this.value);
            });
        }

        // Organization change handler - load entities (if enabled)
        const orgSelect = document.getElementById('editOrgDataID');
        if (orgSelect && !orgSelect.disabled) {
            orgSelect.addEventListener('change', function() {
                const orgHidden = document.getElementById('editOrgDataIDHidden');
                if (orgHidden) {
                    orgHidden.value = this.value;
                }
                loadEntitiesForOrg(this.value);
            });
        }
    });
})();
</script>

