<?php
/**
 * Accumulation Policy Edit Modal Wizard
 * Dedicated modal-based wizard for editing tija_leave_accumulation_policies
 * Based on table structure, not leave types
 */
?>

<!-- Accumulation Policy Edit Modal -->
<div class="modal fade" id="accumulationPolicyModal" tabindex="-1" aria-labelledby="accumulationPolicyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="accumulationPolicyModalLabel">
                    <i class="ri-refresh-line me-2"></i>
                    <span id="modalTitle">Create Accumulation Policy</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="accumulationPolicyForm" method="POST" action="<?= $base ?>php/scripts/leave/config/manage_accumulation_policy.php">
                <div class="modal-body">
            <input type="hidden" name="action" id="formAction" value="create_policy">
            <input type="hidden" name="policyID" id="policyID" value="">
            <input type="hidden" name="entityID" id="entityID" value="<?= $entityID ?>">
            <input type="hidden" name="LastUpdateByID" value="<?= $currentUserID ?>">

                    <!-- Progress Stepper -->
                    <div class="wizard-stepper mb-4">
                        <div class="row text-center">
                            <div class="col step active" data-step="1">
                                <div class="step-icon"><i class="ri-file-text-line"></i></div>
                                <div class="step-label">Basic Info</div>
                            </div>
                            <div class="col step" data-step="2">
                                <div class="step-icon"><i class="ri-calendar-line"></i></div>
                                <div class="step-label">Accrual Settings</div>
                            </div>
                            <div class="col step" data-step="3">
                                <div class="step-icon"><i class="ri-refresh-line"></i></div>
                                <div class="step-label">Carryover Rules</div>
                            </div>
                            <div class="col step" data-step="4">
                                <div class="step-icon"><i class="ri-settings-3-line"></i></div>
                                <div class="step-label">Advanced</div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 1: Basic Information -->
                    <div class="wizard-step active" id="step-1">
                        <h6 class="mb-3 text-primary">
                            <i class="ri-file-text-line me-2"></i>Basic Information
                        </h6>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="policyName" class="form-label fw-semibold">
                                        Policy Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="policyName" name="policyName"
                                           placeholder="e.g., Annual Leave Monthly Accrual, Senior Staff Quarterly Accrual" required>
                                    <div class="form-text">Unique name to identify this accumulation policy</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="policyScope" class="form-label fw-semibold">
                                        Policy Scope <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="policyScope" name="policyScope" required onchange="handlePolicyScopeChange()">
                                        <option value="Entity" selected>Entity Level</option>
                                        <option value="Global">Global (Parent Entity)</option>
                                        <option value="Cadre">Cadre (Job Category/Band)</option>
                                    </select>
                                    <div class="form-text">
                                        <span id="scopeHelp">Policy applies to all employees in the selected entity</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6" id="entitySelectorContainer">
                                <div class="mb-3">
                                    <label for="entityIDSelect" class="form-label fw-semibold">
                                        Entity <span class="text-danger">*</span>
                                    </label>
                                    <?php
                                    $allEntities = Data::entities_full([], false, $DBConn);
                                    ?>
                                    <select class="form-select" id="entityIDSelect" name="entityIDSelect" onchange="updateEntityID()">
                                        <option value="">-- Select Entity --</option>
                                        <?php if (!empty($allEntities)): ?>
                                            <?php foreach ($allEntities as $ent): ?>
                                            <option value="<?= $ent->entityID ?>" <?= ($entityID == $ent->entityID) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($ent->entityName) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="form-text">Entity this policy applies to</div>
                                </div>
                            </div>
                        </div>

                        <div class="row d-none" id="cadreFieldsContainer">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jobCategoryID" class="form-label fw-semibold">
                                        Job Category
                                    </label>
                                    <?php
                                    $jobCategories = Admin::tija_job_categories([], false, $DBConn) ?? [];
                                    ?>
                                    <select class="form-select" id="jobCategoryID" name="jobCategoryID">
                                        <option value="">-- Select Job Category (Optional) --</option>
                                        <?php if (!empty($jobCategories)): ?>
                                            <?php foreach ($jobCategories as $cat): ?>
                                            <option value="<?= $cat->jobCategoryID ?>">
                                                <?= htmlspecialchars($cat->jobCategoryTitle ?? $cat->jobCategoryName ?? '') ?>
                                            </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="form-text">Leave blank if using Job Band instead</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jobBandID" class="form-label fw-semibold">
                                        Job Band
                                    </label>
                                    <?php
                                    $jobBands = Admin::tija_job_bands([], false, $DBConn) ?? [];
                                    ?>
                                    <select class="form-select" id="jobBandID" name="jobBandID">
                                        <option value="">-- Select Job Band (Optional) --</option>
                                        <?php if (!empty($jobBands)): ?>
                                            <?php foreach ($jobBands as $band): ?>
                                            <option value="<?= $band->jobBandID ?>">
                                                <?= htmlspecialchars($band->jobBandTitle ?? $band->jobBandName ?? '') ?>
                                            </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="form-text">Leave blank if using Job Category instead</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="leaveTypeID" class="form-label fw-semibold">
                                        Leave Type <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="leaveTypeID" name="leaveTypeID" required>
                                        <option value="">-- Select Leave Type --</option>
                                        <?php if (!empty($leaveTypes)): ?>
                                            <?php foreach ($leaveTypes as $lt): ?>
                                            <option value="<?= $lt->leaveTypeID ?>">
                                                <?= htmlspecialchars($lt->leaveTypeName) ?>
                                                <?php if (!empty($lt->leaveTypeCode)): ?>
                                                (<?= htmlspecialchars($lt->leaveTypeCode) ?>)
                                                <?php endif; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="form-text">Leave type this policy applies to</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="priority" class="form-label fw-semibold">Priority</label>
                                    <input type="number" class="form-control" id="priority" name="priority"
                                           value="1" min="1" max="10">
                                    <div class="form-text">Lower numbers have higher priority when multiple policies apply</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="policyDescription" class="form-label fw-semibold">Description</label>
                                    <textarea class="form-control" id="policyDescription" name="policyDescription" rows="3"
                                              placeholder="Describe the purpose and details of this accumulation policy..."></textarea>
                                    <div class="form-text">Optional detailed description</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Accrual Settings -->
                    <div class="wizard-step d-none" id="step-2">
                        <h6 class="mb-3 text-success">
                            <i class="ri-calendar-line me-2"></i>Accrual Settings
                        </h6>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="accrualType" class="form-label fw-semibold">
                                        Accrual Type <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="accrualType" name="accrualType" required onchange="updateAccrualHelp()">
                                        <option value="Monthly">Monthly</option>
                                        <option value="Quarterly">Quarterly</option>
                                        <option value="Annual">Annual</option>
                                        <option value="Continuous">Continuous</option>
                                    </select>
                                    <div class="form-text" id="accrualTypeHelp">Leave accrues every month</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="accrualRate" class="form-label fw-semibold">
                                        Accrual Rate <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="accrualRate" name="accrualRate"
                                               step="0.01" min="0" max="365" placeholder="2.00" required>
                                        <span class="input-group-text">days/period</span>
                                    </div>
                                    <div class="form-text">Days accrued per accrual period</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="proRated" name="proRated" value="Y" checked>
                                        <label class="form-check-label fw-semibold" for="proRated">
                                            Pro-Rated Accrual
                                        </label>
                                    </div>
                                    <div class="form-text">Calculate pro-rata for partial periods</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="isActive" name="isActive" value="Y" checked>
                                        <label class="form-check-label fw-semibold" for="isActive">
                                            Policy Active
                                        </label>
                                    </div>
                                    <div class="form-text">Enable this policy for use</div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Step 3: Carryover Rules -->
                    <div class="wizard-step d-none" id="step-3">
                        <h6 class="mb-3 text-info">
                            <i class="ri-refresh-line me-2"></i>Carryover Rules
                        </h6>

                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>
                            Configure how unused leave days are handled at period end
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="maxCarryover" class="form-label fw-semibold">Maximum Carryover</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="maxCarryover" name="maxCarryover"
                                               min="0" max="365" placeholder="Leave empty for unlimited">
                                        <span class="input-group-text">days</span>
                                    </div>
                                    <div class="form-text">Maximum days that can be carried over (empty = unlimited)</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="carryoverExpiryMonths" class="form-label fw-semibold">Carryover Expiry</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="carryoverExpiryMonths" name="carryoverExpiryMonths"
                                               min="0" max="60" placeholder="Leave empty for no expiry">
                                        <span class="input-group-text">months</span>
                                    </div>
                                    <div class="form-text">Months after which carryover expires (empty = never)</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Advanced Settings -->
                    <div class="wizard-step d-none" id="step-4">
                        <h6 class="mb-3 text-warning">
                            <i class="ri-settings-3-line me-2"></i>Advanced Settings
                        </h6>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="Suspended" class="form-label fw-semibold">Status</label>
                                    <select class="form-select" id="Suspended" name="Suspended">
                                        <option value="N">Active</option>
                                        <option value="Y">Suspended</option>
                                    </select>
                                    <div class="form-text">Temporarily disable this policy</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Policy Information</label>
                                    <div class="bg-light p-3 rounded">
                                        <small class="text-muted d-block">Policy ID: <span id="displayPolicyID">-</span></small>
                                        <small class="text-muted d-block">Entity: <?= htmlspecialchars($_SESSION['entityName'] ?? 'Entity ' . ($entityID ?? 1)) ?></small>
                                        <small class="text-muted d-block">Created: <span id="displayDateAdded">-</span></small>
                                        <small class="text-muted d-block">Last Updated: <span id="displayLastUpdate">-</span></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="prevStepBtn" onclick="prevWizardStep()" style="display:none;">
                        <i class="ri-arrow-left-line me-1"></i>Previous
                    </button>
                    <button type="button" class="btn btn-primary" id="nextStepBtn" onclick="nextWizardStep()">
                        Next <i class="ri-arrow-right-line ms-1"></i>
                    </button>
                    <button type="submit" class="btn btn-success" id="submitBtn" style="display:none;">
                        <i class="ri-check-line me-1"></i>Save Policy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.wizard-stepper {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
}

.wizard-stepper .step {
    position: relative;
    opacity: 0.5;
    transition: all 0.3s ease;
}

.wizard-stepper .step.active,
.wizard-stepper .step.completed {
    opacity: 1;
}

.wizard-stepper .step-icon {
    width: 45px;
    height: 45px;
    margin: 0 auto 0.5rem;
    background: #e9ecef;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    color: #6c757d;
    transition: all 0.3s ease;
}

.wizard-stepper .step.active .step-icon {
    background: linear-gradient(135deg, #0052CC 0%, #0065FF 100%);
    color: white;
    transform: scale(1.1);
}

.wizard-stepper .step.completed .step-icon {
    background: #00875A;
    color: white;
}

.wizard-stepper .step-label {
    font-size: 0.8rem;
    font-weight: 500;
}

.wizard-step {
    min-height: 300px;
    padding: 1rem 0;
}
</style>

<script>
let currentWizardStep = 1;
const totalWizardSteps = 4;

// Initialize modal
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('accumulationPolicyModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            resetWizard();
        });
    }
});

function editAccumulationPolicy(policyID) {
    const modal = new bootstrap.Modal(document.getElementById('accumulationPolicyModal'));
    const form = document.getElementById('accumulationPolicyForm');

    if (policyID && policyID > 0) {
        // Edit mode - load policy data
        document.getElementById('modalTitle').textContent = 'Edit Accumulation Policy';
        document.getElementById('formAction').value = 'update_policy';
        document.getElementById('policyID').value = policyID;

        // Load policy data via AJAX
                    fetch('<?= $base ?>php/scripts/leave/config/manage_accumulation_policy.php?action=get_policy&policyID=' + policyID, {
                        credentials: 'same-origin'
                    })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.policy) {
                    const policy = data.policy;

                    // Populate form fields
                    document.getElementById('policyName').value = policy.policyName || '';
                    document.getElementById('leaveTypeID').value = policy.leaveTypeID || '';
                    document.getElementById('priority').value = policy.priority || 1;
                    document.getElementById('policyDescription').value = policy.policyDescription || '';
                    document.getElementById('accrualType').value = policy.accrualType || 'Monthly';
                    document.getElementById('accrualRate').value = policy.accrualRate || '';
                    const proRatedCheckbox = document.getElementById('proRated');
                    const isActiveCheckbox = document.getElementById('isActive');
                    if (proRatedCheckbox) proRatedCheckbox.checked = (policy.proRated || 'N') === 'Y';
                    if (isActiveCheckbox) isActiveCheckbox.checked = (policy.isActive || 'Y') === 'Y';
                    document.getElementById('maxCarryover').value = policy.maxCarryover || '';
                    document.getElementById('carryoverExpiryMonths').value = policy.carryoverExpiryMonths || '';
                    document.getElementById('Suspended').value = policy.Suspended || 'N';

                    // Populate hierarchy fields
                    if (policy.policyScope) {
                        document.getElementById('policyScope').value = policy.policyScope;
                        handlePolicyScopeChange();
                    }
                    if (policy.entityID) {
                        document.getElementById('entityIDSelect').value = policy.entityID;
                        updateEntityID();
                    }
                    if (policy.jobCategoryID) {
                        document.getElementById('jobCategoryID').value = policy.jobCategoryID;
                    }
                    if (policy.jobBandID) {
                        document.getElementById('jobBandID').value = policy.jobBandID;
                    }

                    // Display info
                    document.getElementById('displayPolicyID').textContent = policy.policyID || '-';
                    document.getElementById('displayDateAdded').textContent = policy.DateAdded ? new Date(policy.DateAdded).toLocaleDateString() : '-';
                    document.getElementById('displayLastUpdate').textContent = policy.LastUpdate ? new Date(policy.LastUpdate).toLocaleDateString() : '-';

                    updateAccrualHelp();
                }
            })
            .catch(error => {
                console.error('Error loading policy:', error);
                alert('Failed to load policy data');
            });
    } else {
        // Create mode
        document.getElementById('modalTitle').textContent = 'Create Accumulation Policy';
        document.getElementById('formAction').value = 'create_policy';
        document.getElementById('policyID').value = '';
        form.reset();
        resetWizard();
    }

    modal.show();
}

function viewAccumulationPolicy(policyID) {
    // Redirect to view page
    window.location.href = '<?= $base ?>html/?s=admin&ss=leave&p=accumulation_policies&action=view&policyID=' + policyID;
}

function handlePolicyScopeChange() {
    const scope = document.getElementById('policyScope').value;
    const entityContainer = document.getElementById('entitySelectorContainer');
    const cadreContainer = document.getElementById('cadreFieldsContainer');
    const scopeHelp = document.getElementById('scopeHelp');
    const entitySelect = document.getElementById('entityIDSelect');

    if (scope === 'Global') {
        // Global: Hide entity selector, hide cadre fields
        entityContainer.style.display = 'none';
        cadreContainer.classList.add('d-none');
        scopeHelp.textContent = 'Policy applies to all entities under the parent organization';
        entitySelect.removeAttribute('required');
        document.getElementById('jobCategoryID').removeAttribute('required');
        document.getElementById('jobBandID').removeAttribute('required');
    } else if (scope === 'Entity') {
        // Entity: Show entity selector, hide cadre fields
        entityContainer.style.display = 'block';
        cadreContainer.classList.add('d-none');
        scopeHelp.textContent = 'Policy applies to all employees in the selected entity';
        entitySelect.setAttribute('required', 'required');
        document.getElementById('jobCategoryID').removeAttribute('required');
        document.getElementById('jobBandID').removeAttribute('required');
    } else if (scope === 'Cadre') {
        // Cadre: Show entity selector, show cadre fields
        entityContainer.style.display = 'block';
        cadreContainer.classList.remove('d-none');
        scopeHelp.textContent = 'Policy applies to employees with specific job category or band in the selected entity';
        entitySelect.setAttribute('required', 'required');
        // At least one of jobCategoryID or jobBandID should be selected
        const jobCategorySelect = document.getElementById('jobCategoryID');
        const jobBandSelect = document.getElementById('jobBandID');
        jobCategorySelect.onchange = validateCadreFields;
        jobBandSelect.onchange = validateCadreFields;
    }
}

function updateEntityID() {
    const entitySelect = document.getElementById('entityIDSelect');
    const hiddenEntityID = document.getElementById('entityID');
    if (entitySelect && hiddenEntityID) {
        hiddenEntityID.value = entitySelect.value;
    }
}

function validateCadreFields() {
    const jobCategoryID = document.getElementById('jobCategoryID').value;
    const jobBandID = document.getElementById('jobBandID').value;
    const scope = document.getElementById('policyScope').value;

    if (scope === 'Cadre' && !jobCategoryID && !jobBandID) {
        // Show warning but don't block - backend will validate
        console.warn('Cadre scope requires either job category or job band');
    }
}

function toggleAccumulationPolicyStatus(policyID, newStatus) {
    const formData = new FormData();
    formData.append('action', 'toggle_policy_status');
    formData.append('policyID', policyID);
    formData.append('status', newStatus);

    fetch('<?= $base ?>php/scripts/leave/config/manage_accumulation_policy.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: data.message || 'Policy status updated successfully',
                confirmButtonText: 'OK',
                confirmButtonColor: '#198754'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to update policy status',
                confirmButtonText: 'OK',
                confirmButtonColor: '#dc3545'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to update policy status. Please try again.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc3545'
        });
    });
}

function deleteAccumulationPolicy(policyID, policyName) {
    const formData = new FormData();
    formData.append('action', 'delete_policy');
    formData.append('policyID', policyID);

    fetch('<?= $base ?>php/scripts/leave/config/manage_accumulation_policy.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.text();
    })
    .then(text => {
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('Failed to parse JSON response:', text);
            throw new Error('Invalid response from server: ' + text.substring(0, 100));
        }

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: data.message || 'Policy deleted successfully',
                confirmButtonText: 'OK',
                confirmButtonColor: '#198754'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to delete policy',
                confirmButtonText: 'OK',
                confirmButtonColor: '#dc3545'
            });
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to delete policy: ' + error.message + '. Please check the console for details.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#dc3545'
        });
    });
}

function nextWizardStep() {
    if (validateCurrentStep()) {
        if (currentWizardStep < totalWizardSteps) {
            currentWizardStep++;
            updateWizardDisplay();
        }
    }
}

function prevWizardStep() {
    if (currentWizardStep > 1) {
        currentWizardStep--;
        updateWizardDisplay();
    }
}

function updateWizardDisplay() {
    // Hide all steps
    for (let i = 1; i <= totalWizardSteps; i++) {
        document.getElementById('step-' + i).classList.add('d-none');
        document.getElementById('step-' + i).classList.remove('active');

        const stepElement = document.querySelector('.wizard-stepper .step[data-step="' + i + '"]');
        if (stepElement) {
            stepElement.classList.remove('active');
            if (i < currentWizardStep) {
                stepElement.classList.add('completed');
            } else {
                stepElement.classList.remove('completed');
            }
        }
    }

    // Show current step
    document.getElementById('step-' + currentWizardStep).classList.remove('d-none');
    document.getElementById('step-' + currentWizardStep).classList.add('active');

    const currentStepElement = document.querySelector('.wizard-stepper .step[data-step="' + currentWizardStep + '"]');
    if (currentStepElement) {
        currentStepElement.classList.add('active');
    }

    // Update buttons
    document.getElementById('prevStepBtn').style.display = currentWizardStep > 1 ? 'inline-block' : 'none';
    document.getElementById('nextStepBtn').style.display = currentWizardStep < totalWizardSteps ? 'inline-block' : 'none';
    document.getElementById('submitBtn').style.display = currentWizardStep === totalWizardSteps ? 'inline-block' : 'none';
}

function validateCurrentStep() {
    const currentStep = document.getElementById('step-' + currentWizardStep);
    const requiredFields = currentStep.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });

    if (!isValid) {
        alert('Please fill in all required fields');
    }

    return isValid;
}

function resetWizard() {
    currentWizardStep = 1;
    updateWizardDisplay();
}

function updateAccrualHelp() {
    const accrualType = document.getElementById('accrualType').value;
    const helpText = document.getElementById('accrualTypeHelp');

    const helpMessages = {
        'Monthly': 'Leave accrues every month',
        'Quarterly': 'Leave accrues every quarter (3 months)',
        'Annual': 'Leave accrues once per year',
        'Continuous': 'Leave accrues continuously based on days worked'
    };

    if (helpText) {
        helpText.textContent = helpMessages[accrualType] || helpMessages['Monthly'];
    }
}

// Handle form submission
    // Initialize policy scope handler
    const policyScopeSelect = document.getElementById('policyScope');
    if (policyScopeSelect) {
        policyScopeSelect.addEventListener('change', handlePolicyScopeChange);
        handlePolicyScopeChange(); // Initialize on load
    }

    const entityIDSelect = document.getElementById('entityIDSelect');
    if (entityIDSelect) {
        entityIDSelect.addEventListener('change', updateEntityID);
    }

    const accumulationPolicyForm = document.getElementById('accumulationPolicyForm');
    if (accumulationPolicyForm) {
        accumulationPolicyForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Ensure policy scope and related fields are included
            const formData = new FormData(this);
            const policyScope = document.getElementById('policyScope').value;
            formData.set('policyScope', policyScope);

            // Update entityID based on scope
            if (policyScope === 'Global') {
                formData.set('entityID', '');
            } else {
                const entityID = document.getElementById('entityIDSelect').value;
                formData.set('entityID', entityID);
            }

            // Add job category and band if cadre scope
            if (policyScope === 'Cadre') {
                const jobCategoryID = document.getElementById('jobCategoryID').value;
                const jobBandID = document.getElementById('jobBandID').value;
                formData.set('jobCategoryID', jobCategoryID || '');
                formData.set('jobBandID', jobBandID || '');
            } else {
                formData.set('jobCategoryID', '');
                formData.set('jobBandID', '');
            }

        if (!validateCurrentStep()) {
            return;
        }

        const formData = new FormData(this);

        // Ensure checkboxes send proper values
        const proRatedCheckbox = document.getElementById('proRated');
        const isActiveCheckbox = document.getElementById('isActive');
        if (proRatedCheckbox) {
            formData.set('proRated', proRatedCheckbox.checked ? 'Y' : 'N');
        }
        if (isActiveCheckbox) {
            formData.set('isActive', isActiveCheckbox.checked ? 'Y' : 'N');
        }

        const submitBtn = document.getElementById('submitBtn');
        const originalText = submitBtn ? submitBtn.innerHTML : '';

        if (submitBtn) {
            submitBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i>Saving...';
            submitBtn.disabled = true;
        }

        // Get the form action URL directly from the form element's action attribute
        const formActionUrl = accumulationPolicyForm.getAttribute('action') || '<?= $base ?>php/scripts/leave/config/manage_accumulation_policy.php';

        fetch(formActionUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(text => {
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Failed to parse JSON response:', text);
                throw new Error('Invalid response from server: ' + text.substring(0, 100));
            }

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: data.message || 'Policy saved successfully',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#198754'
                }).then(() => {
                    const modalElement = document.getElementById('accumulationPolicyModal');
                    if (modalElement) {
                        const modalInstance = bootstrap.Modal.getInstance(modalElement);
                        if (modalInstance) modalInstance.hide();
                    }
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to save policy',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#dc3545'
                });
                if (submitBtn) {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            }
        })
        .catch(error => {
            console.error('Form submission error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to save policy: ' + error.message + '. Please check the console for details.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#dc3545'
            });
            if (submitBtn) {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });
    });
}

// Make functions globally available
window.editAccumulationPolicy = editAccumulationPolicy;
window.viewAccumulationPolicy = viewAccumulationPolicy;
window.toggleAccumulationPolicyStatus = toggleAccumulationPolicyStatus;
window.deleteAccumulationPolicy = deleteAccumulationPolicy;
</script>

