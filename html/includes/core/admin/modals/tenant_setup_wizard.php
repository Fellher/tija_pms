<?php
// Tenant Setup Wizard Modal
// This wizard guides the super admin through complete tenant setup
?>

<!-- Tenant Setup Wizard Modal -->
<div class="modal fade" id="tenantSetupWizard" tabindex="-1" aria-labelledby="tenantSetupWizardLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="tenantSetupWizardLabel">
                    <i class="fas fa-magic me-2"></i>Tenant Setup Wizard
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Wizard Progress Steps -->
                <div class="wizard-progress mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="wizard-step active" data-step="1">
                            <div class="step-number">1</div>
                            <div class="step-label">Organization</div>
                        </div>
                        <div class="wizard-line"></div>
                        <div class="wizard-step" data-step="2">
                            <div class="step-number">2</div>
                            <div class="step-label">Entities</div>
                        </div>
                        <div class="wizard-line"></div>
                        <div class="wizard-step" data-step="3">
                            <div class="step-number">3</div>
                            <div class="step-label">License</div>
                        </div>
                        <div class="wizard-line"></div>
                        <div class="wizard-step" data-step="4">
                            <div class="step-number">4</div>
                            <div class="step-label">Administrators</div>
                        </div>
                        <div class="wizard-line"></div>
                        <div class="wizard-step" data-step="5">
                            <div class="step-number">5</div>
                            <div class="step-label">Review</div>
                        </div>
                    </div>
                </div>

                <!-- Wizard Content -->
                <form id="tenantSetupForm">
                    <!-- Step 1: Organization Details -->
                    <div class="wizard-content active" data-step-content="1">
                        <h5 class="mb-3 pb-2 border-bottom">
                            <i class="fas fa-building text-primary me-2"></i>Step 1: Organization/Tenant Details
                        </h5>
                        <p class="text-muted mb-4">Create the main organization/tenant. This will be the parent entity for all sub-entities.</p>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Organization Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="orgName" required placeholder="e.g., ACME Corporation">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Registration Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="registrationNumber" required placeholder="Company registration number">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tax PIN/Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="orgPIN" required placeholder="Tax identification number">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Industry Sector <span class="text-danger">*</span></label>
                                <select class="form-select" name="industrySectorID" required>
                                    <option value="">Select Industry</option>
                                    <?php if ($industrySectors): ?>
                                        <?php foreach ($industrySectors as $sector): ?>
                                            <option value="<?= $sector->industrySectorID ?>">
                                                <?= htmlspecialchars($sector->industryTitle) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Country <span class="text-danger">*</span></label>
                                <select class="form-select" name="countryID" required>
                                    <option value="">Select Country</option>
                                    <?php if ($countries): ?>
                                        <?php foreach ($countries as $country): ?>
                                            <option value="<?= $country->countryID ?>">
                                                <?= htmlspecialchars($country->countryName) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">City <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="orgCity" required placeholder="City">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Postal Code</label>
                                <input type="text" class="form-control" name="orgPostalCode" placeholder="Postal/ZIP code">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="orgAddress" rows="2" placeholder="Complete address"></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="orgEmail" required placeholder="organization@example.com">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" name="orgPhoneNumber1" required placeholder="+254712345678">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Number of Employees</label>
                                <input type="number" class="form-control" name="numberOfEmployees" placeholder="Estimated employee count">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Enable Cost Center</label>
                                <select class="form-select" name="costCenterEnabled">
                                    <option value="N">No</option>
                                    <option value="Y">Yes</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Entities Setup -->
                    <div class="wizard-content" data-step-content="2">
                        <h5 class="mb-3 pb-2 border-bottom">
                            <i class="fas fa-sitemap text-primary me-2"></i>Step 2: Setup Entities
                        </h5>
                        <p class="text-muted mb-4">Add entities (companies/branches) under this organization. You can add more later.</p>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Entities List</h6>
                            <button type="button" class="btn btn-sm btn-primary" onclick="addEntityRow()">
                                <i class="fas fa-plus me-1"></i>Add Entity
                            </button>
                        </div>

                        <div id="entitiesList">
                            <!-- Entity rows will be added here dynamically -->
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>Click "Add Entity" to create entities for this organization.
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: License Configuration -->
                    <div class="wizard-content" data-step-content="3">
                        <h5 class="mb-3 pb-2 border-bottom">
                            <i class="fas fa-certificate text-primary me-2"></i>Step 3: License Configuration
                        </h5>
                        <p class="text-muted mb-4">Configure the license for this tenant.</p>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">License Type <span class="text-danger">*</span></label>
                                <select class="form-select" name="licenseType" id="wizardLicenseType" required>
                                    <option value="">Select License Type</option>
                                    <?php
                                    // Fetch license types from database

                                    if ($licenseTypes):
                                        foreach ($licenseTypes as $type):
                                    ?>
                                        <option value="<?= htmlspecialchars($type->licenseTypeID) ?>"
                                                data-user-limit="<?= $type->defaultUserLimit  ?>"
                                                data-default-duration="<?= $type->defaultDuration ?>">
                                            <?= htmlspecialchars($type->licenseTypeName) ?>
                                            (<?= $type->defaultUserLimit ?> users, <?= $type->defaultDuration ?> days)
                                        </option>
                                    <?php
                                        endforeach;
                                    else:
                                    ?>
                                        <!-- Fallback if license types table is empty -->
                                        <option value="trial" data-user-limit="10" data-duration="1">Trial (10 users, 1 month)</option>
                                        <option value="basic" data-user-limit="50" data-duration="12">Basic (50 users, 12 months)</option>
                                        <option value="standard" data-user-limit="200" data-duration="12">Standard (200 users, 12 months)</option>
                                        <option value="premium" data-user-limit="500" data-duration="12">Premium (500 users, 12 months)</option>
                                        <option value="enterprise" data-user-limit="99999" data-duration="12">Enterprise (Unlimited, 12 months)</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">User Limit <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="userLimit" id="wizardUserLimit" required placeholder="Maximum users allowed" value="50" readonly>
                                <small class="form-text text-muted">Auto-filled based on license type</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Issue Date <span class="text-danger">*</span></label>
                                <input type="text" class="form-control wizard-date-issue" name="licenseIssueDate" required placeholder="Select issue date" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Expiry Date <span class="text-danger">*</span></label>
                                <input type="text" class="form-control wizard-date-expiry" name="licenseExpiryDate" required placeholder="Select expiry date" readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">License Notes</label>
                                <textarea class="form-control" name="licenseNotes" rows="3" placeholder="Any special notes about this license..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Administrator Setup -->
                    <div class="wizard-content" data-step-content="4">
                        <h5 class="mb-3 pb-2 border-bottom">
                            <i class="fas fa-user-shield text-primary me-2"></i>Step 4: Setup Administrators
                        </h5>
                        <p class="text-muted mb-4">Assign administrators to manage this tenant.</p>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Administrators</h6>
                            <button type="button" class="btn btn-sm btn-primary" onclick="addAdminRow()">
                                <i class="fas fa-plus me-1"></i>Add Administrator
                            </button>
                        </div>

                        <div id="adminsList">
                            <!-- Admin rows will be added here dynamically -->
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>Click "Add Administrator" to assign admins to this tenant.
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Review & Confirm -->
                    <div class="wizard-content" data-step-content="5">
                        <h5 class="mb-3 pb-2 border-bottom">
                            <i class="fas fa-check-circle text-primary me-2"></i>Step 5: Review & Confirm
                        </h5>
                        <p class="text-muted mb-4">Review all the information before creating the tenant.</p>

                        <div id="reviewContent">
                            <!-- Review content will be populated dynamically -->
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" id="wizardPrevBtn" onclick="previousStep()" style="display: none;">
                    <i class="fas fa-arrow-left me-2"></i>Previous
                </button>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="wizardNextBtn" onclick="nextStep()">
                    Next<i class="fas fa-arrow-right ms-2"></i>
                </button>
                <button type="button" class="btn btn-success" id="wizardFinishBtn" onclick="finishSetup()" style="display: none;">
                    <i class="fas fa-check me-2"></i>Complete Setup
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.wizard-progress {
    position: relative;
    padding: 20px 0;
}

.wizard-step {
    text-align: center;
    flex: 1;
    position: relative;
    z-index: 2;
}

.wizard-step .step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 8px;
    transition: all 0.3s;
}

.wizard-step.active .step-number {
    background: var(--primary-color, #5b6fe3);
    color: white;
}

.wizard-step.completed .step-number {
    background: #28a745;
    color: white;
}

.wizard-step .step-label {
    font-size: 12px;
    font-weight: 500;
    color: #6c757d;
}

.wizard-step.active .step-label {
    color: var(--primary-color, #5b6fe3);
    font-weight: 600;
}

.wizard-line {
    height: 2px;
    background: #e9ecef;
    flex: 1;
    margin: 0 10px;
    position: relative;
    top: -10px;
}

.wizard-content {
    display: none;
    animation: fadeIn 0.3s;
}

.wizard-content.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.entity-item, .admin-item {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
}
</style>

<script>
let currentStep = 1;
const totalSteps = 5;
let entityCounter = 0;
let adminCounter = 0;

function nextStep() {
    if (validateCurrentStep()) {
        if (currentStep < totalSteps) {
            currentStep++;
            updateWizard();
        }
    }
}

function previousStep() {
    if (currentStep > 1) {
        currentStep--;
        updateWizard();
    }
}

function updateWizard() {
    // Update step indicators
    document.querySelectorAll('.wizard-step').forEach((step, index) => {
        step.classList.remove('active', 'completed');
        if (index + 1 < currentStep) {
            step.classList.add('completed');
        } else if (index + 1 === currentStep) {
            step.classList.add('active');
        }
    });

    // Update content visibility
    document.querySelectorAll('.wizard-content').forEach((content, index) => {
        content.classList.remove('active');
        if (index + 1 === currentStep) {
            content.classList.add('active');
        }
    });

    // Update buttons
    document.getElementById('wizardPrevBtn').style.display = currentStep > 1 ? 'inline-block' : 'none';
    document.getElementById('wizardNextBtn').style.display = currentStep < totalSteps ? 'inline-block' : 'none';
    document.getElementById('wizardFinishBtn').style.display = currentStep === totalSteps ? 'inline-block' : 'none';

    // Populate review if on last step
    if (currentStep === totalSteps) {
        populateReview();
    }
}

function validateCurrentStep() {
    const currentContent = document.querySelector(`.wizard-content[data-step-content="${currentStep}"]`);
    const requiredFields = currentContent.querySelectorAll('[required]');

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
        alert('Please fill in all required fields before proceeding.');
    }

    return isValid;
}

function addEntityRow() {
    entityCounter++;

    // Get entity types options (PHP-generated)
    const entityTypesOptions = `
        <?php if ($entityTypes): ?>
            <?php foreach ($entityTypes as $type): ?>
                <option value="<?= $type->entityTypeID ?>">
                    <?= htmlspecialchars($type->entityTypeTitle) ?>
                </option>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Fallback if entity types not loaded -->
            <option value="1">Branch/Office</option>
            <option value="2">Department</option>
            <option value="3">Division</option>
            <option value="4">Subsidiary</option>
        <?php endif; ?>
    `;

    const entityHTML = `
        <div class="entity-item" data-entity-index="${entityCounter}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0"><i class="fas fa-building text-primary me-2"></i>Entity #${entityCounter}</h6>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeEntityRow(${entityCounter})">
                    <i class="fas fa-times"></i> Remove
                </button>
            </div>
            <div class="row">
                <div class="col-md-4 mb-2">
                    <label class="form-label-sm">Entity Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" name="entity_name[]" placeholder="Entity/Branch Name" required>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label-sm">Registration Number</label>
                    <input type="text" class="form-control form-control-sm" name="entity_registration[]" placeholder="Registration Number">
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label-sm">Entity Type <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" name="entityTypeID[]" required>
                        <option value="">Select Type</option>
                        ${entityTypesOptions}
                    </select>
                </div>
            </div>
        </div>
    `;

    if (entityCounter === 1) {
        document.getElementById('entitiesList').innerHTML = '';
    }
    document.getElementById('entitiesList').insertAdjacentHTML('beforeend', entityHTML);
}

function removeEntityRow(index) {
    const row = document.querySelector(`.entity-item[data-entity-index="${index}"]`);
    if (row) {
        row.remove();
    }
}

function addAdminRow() {
    adminCounter++;
    const adminHTML = `
        <div class="admin-item" data-admin-index="${adminCounter}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0"><i class="fas fa-user-shield text-success me-2"></i>Administrator #${adminCounter}</h6>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeAdminRow(${adminCounter})">
                    <i class="fas fa-times"></i> Remove
                </button>
            </div>
            <div class="row">
                <div class="col-md-3 mb-2">
                    <label class="form-label-sm">First Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" name="admin_first_name[]" placeholder="First Name" required>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label-sm">Last Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" name="admin_last_name[]" placeholder="Last Name" required>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label-sm">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control form-control-sm" name="admin_email[]" placeholder="email@example.com" required>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label-sm">Admin Type <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm" name="admin_type[]" required>
                        <option value="">Select Type</option>
                        <?php if ($adminTypes): ?>
                            <?php foreach ($adminTypes as $type): ?>
                                <?php if ($type->adminTypeID != 1): // Skip Super Admin ?>
                                    <option value="<?= $type->adminTypeID ?>">
                                        <?= htmlspecialchars($type->adminTypeName) ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="2">System Admin</option>
                            <option value="3">Entity Admin</option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-2">
                    <div class="form-check form-check-sm">
                        <input class="form-check-input" type="checkbox" name="admin_send_email[]" value="1" checked>
                        <label class="form-check-label small text-muted">
                            Send welcome email with login credentials
                        </label>
                    </div>
                </div>
            </div>
        </div>
    `;

    if (adminCounter === 1) {
        document.getElementById('adminsList').innerHTML = '';
    }
    document.getElementById('adminsList').insertAdjacentHTML('beforeend', adminHTML);
}

function removeAdminRow(index) {
    const row = document.querySelector(`.admin-item[data-admin-index="${index}"]`);
    if (row) {
        row.remove();
    }
}

function populateReview() {
    const form = document.getElementById('tenantSetupForm');
    const formData = new FormData(form);

    // Get license type name
    const licenseTypeSelect = document.getElementById('wizardLicenseType');
    const licenseTypeName = licenseTypeSelect.selectedOptions[0]?.text || formData.get('licenseType');

    // Get entities
    const entityNames = formData.getAll('entity_name[]');
    const entityRegistrations = formData.getAll('entity_registration[]');
    const entityTypeIDs = formData.getAll('entityTypeID[]');

    let entitiesHTML = '';
    if (entityNames.length > 0) {
        entityNames.forEach((name, index) => {
            if (name) {
                // Get entity type name from select option
                let entityTypeName = 'Not specified';
                const entityTypeID = entityTypeIDs[index];
                if (entityTypeID) {
                    const entityTypeSelects = document.querySelectorAll('select[name="entityTypeID[]"]');
                    if (entityTypeSelects[index]) {
                        const selectedOption = entityTypeSelects[index].selectedOptions[0];
                        entityTypeName = selectedOption ? selectedOption.text : 'Not specified';
                    }
                }

                entitiesHTML += `
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>${name}</strong>
                                ${entityRegistrations[index] ? '<br><small class="text-muted">Reg: ' + entityRegistrations[index] + '</small>' : ''}
                            </div>
                            <span class="badge bg-info-transparent">${entityTypeName}</span>
                        </div>
                    </li>
                `;
            }
        });
    } else {
        entitiesHTML = '<li class="list-group-item text-muted">No entities added</li>';
    }

    // Get administrators
    const adminFirstNames = formData.getAll('admin_first_name[]');
    const adminLastNames = formData.getAll('admin_last_name[]');
    const adminEmails = formData.getAll('admin_email[]');
    const adminTypeIDs = formData.getAll('admin_type[]');

    let adminsHTML = '';
    if (adminFirstNames.length > 0) {
        adminFirstNames.forEach((firstName, index) => {
            if (firstName && adminEmails[index]) {
                const lastName = adminLastNames[index] || '';

                // Get admin type name from select option
                let adminTypeName = 'Not specified';
                const adminTypeID = adminTypeIDs[index];
                if (adminTypeID) {
                    const adminTypeSelects = document.querySelectorAll('select[name="admin_type[]"]');
                    if (adminTypeSelects[index]) {
                        const selectedOption = adminTypeSelects[index].selectedOptions[0];
                        adminTypeName = selectedOption ? selectedOption.text : 'Not specified';
                    }
                }

                adminsHTML += `
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${firstName} ${lastName}</strong>
                                <br><small class="text-muted"><i class="fas fa-envelope me-1"></i>${adminEmails[index]}</small>
                            </div>
                            <span class="badge bg-success-transparent">${adminTypeName}</span>
                        </div>
                    </li>
                `;
            }
        });
    } else {
        adminsHTML = '<li class="list-group-item text-muted">No administrators added</li>';
    }

    let reviewHTML = `
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-building me-2"></i>Organization Details</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Organization Name:</dt>
                    <dd class="col-sm-8"><strong>${formData.get('orgName') || 'Not provided'}</strong></dd>
                    <dt class="col-sm-4">Registration Number:</dt>
                    <dd class="col-sm-8">${formData.get('registrationNumber') || 'Not provided'}</dd>
                    <dt class="col-sm-4">Tax PIN:</dt>
                    <dd class="col-sm-8">${formData.get('orgPIN') || 'Not provided'}</dd>
                    <dt class="col-sm-4">Email:</dt>
                    <dd class="col-sm-8">${formData.get('orgEmail') || 'Not provided'}</dd>
                    <dt class="col-sm-4">Phone:</dt>
                    <dd class="col-sm-8">${formData.get('orgPhoneNumber1') || 'Not provided'}</dd>
                    <dt class="col-sm-4">City:</dt>
                    <dd class="col-sm-8">${formData.get('orgCity') || 'Not provided'}</dd>
                </dl>
            </div>
        </div>

        <div class="card mt-3 border-success">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="fas fa-sitemap me-2"></i>Entities (${entityNames.length})</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    ${entitiesHTML}
                </ul>
            </div>
        </div>

        <div class="card mt-3 border-warning">
            <div class="card-header bg-warning text-white">
                <h6 class="mb-0"><i class="fas fa-certificate me-2"></i>License Information</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">License Type:</dt>
                    <dd class="col-sm-8"><strong>${licenseTypeName}</strong></dd>
                    <dt class="col-sm-4">User Limit:</dt>
                    <dd class="col-sm-8">${formData.get('userLimit') || 'Not set'} users</dd>
                    <dt class="col-sm-4">Issue Date:</dt>
                    <dd class="col-sm-8">${formData.get('licenseIssueDate') || 'Not set'}</dd>
                    <dt class="col-sm-4">Expiry Date:</dt>
                    <dd class="col-sm-8">${formData.get('licenseExpiryDate') || 'Not set'}</dd>
                    <dt class="col-sm-4">Notes:</dt>
                    <dd class="col-sm-8">${formData.get('licenseNotes') || 'None'}</dd>
                </dl>
            </div>
        </div>

        <div class="card mt-3 border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-user-shield me-2"></i>Administrators (${adminFirstNames.length})</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    ${adminsHTML}
                </ul>
            </div>
        </div>

        <div class="alert alert-warning mt-3">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Important:</strong> Please review all information carefully. Once you click "Complete Setup",
            the tenant will be created and administrators will receive their login credentials.
        </div>
    `;

    document.getElementById('reviewContent').innerHTML = reviewHTML;
}

function finishSetup() {
    if (!confirm('Are you sure you want to complete this tenant setup? This will create the organization, entities, license, and administrators.')) {
        return;
    }

    const form = document.getElementById('tenantSetupForm');
    const formData = new FormData(form);
    const finishBtn = document.getElementById('wizardFinishBtn');

    // Disable button and show loading state
    finishBtn.disabled = true;
    finishBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Tenant...';

    console.log('Submitting tenant setup...');
    console.log('Form data entries:');
    for (let [key, value] of formData.entries()) {
        console.log('  ' + key + ':', value);
    }

    // Submit via AJAX
    fetch('<?= $base ?>php/scripts/global/admin/setup_tenant.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);

        // Check content type
        const contentType = response.headers.get('content-type');
        console.log('Content-Type:', contentType);

        if (!response.ok) {
            // Try to get error text for 500 errors
            return response.text().then(text => {
                console.error('Server returned error:', text);
                throw new Error('Server error ('+response.status+'). Check console for details.');
            });
        }

        // Check if response is JSON
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                console.error('Response is not JSON:', text);
                throw new Error('Server returned non-JSON response. Check console for details.');
            });
        }

        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);

        if (data.success) {
            // Show success message
            alert('✅ Tenant Setup Completed Successfully!\n\n' +
                  'Organization: ' + data.data.orgName + '\n' +
                  'Organization ID: ' + data.data.orgDataID + '\n' +
                  'License Key: ' + data.data.licenseKey + '\n' +
                  'Entities Created: ' + data.data.entitiesCreated + '\n' +
                  'Administrators Created: ' + data.data.adminsCreated);

            // Close modal and redirect to tenant details
            bootstrap.Modal.getInstance(document.getElementById('tenantSetupWizard')).hide();
            window.location.href = '<?= $base ?>html/?s=core&ss=admin&p=tenant_details&orgDataID=' + data.data.orgDataID;
        } else {
            // Show error messages
            let errorMsg = 'Failed to create tenant:\n\n';
            if (data.errors && Array.isArray(data.errors)) {
                errorMsg += data.errors.join('\n');
            } else if (data.message) {
                errorMsg += data.message;
            }
            alert('❌ ' + errorMsg);

            // Re-enable button
            finishBtn.disabled = false;
            finishBtn.innerHTML = '<i class="fas fa-check me-2"></i>Complete Setup';
        }
    })
    .catch(error => {
        console.error('Error submitting tenant setup:', error);
        alert('❌ Error: ' + error.message + '\n\nPlease check the console for details.');

        // Re-enable button
        finishBtn.disabled = false;
        finishBtn.innerHTML = '<i class="fas fa-check me-2"></i>Complete Setup';
    });
}

// Initialize wizard on modal show
document.getElementById('tenantSetupWizard').addEventListener('show.bs.modal', function () {
    currentStep = 1;
    entityCounter = 0;
    adminCounter = 0;
    document.getElementById('tenantSetupForm').reset();
    document.getElementById('entitiesList').innerHTML = '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>Click "Add Entity" to create entities for this organization.</div>';
    document.getElementById('adminsList').innerHTML = '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>Click "Add Administrator" to assign admins to this tenant.</div>';
    updateWizard();

    // Initialize flatpickr for wizard date inputs
    initializeWizardDatePickers();
});

// Initialize flatpickr for wizard date inputs
function initializeWizardDatePickers() {
    // Issue Date
    flatpickr('.wizard-date-issue', {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'F j, Y',
        defaultDate: new Date(),
        onChange: function(selectedDates, dateStr) {
            updateLicenseExpiryDate();
        }
    });

    // Expiry Date
    flatpickr('.wizard-date-expiry', {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'F j, Y',
        defaultDate: new Date(new Date().setFullYear(new Date().getFullYear() + 1)),
        minDate: 'today'
    });
}

// Handle license type change
document.addEventListener('DOMContentLoaded', function() {
    const licenseTypeSelect = document.getElementById('wizardLicenseType');
    if (licenseTypeSelect) {
        licenseTypeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const userLimit = selectedOption.getAttribute('data-user-limit');
            const duration = selectedOption.getAttribute('data-default-duration'); // Changed to data-default-duration

            console.log('License type changed:', {
                value: this.value,
                text: selectedOption.text,
                userLimit: userLimit,
                duration: duration
            });

            // Update user limit
            const userLimitInput = document.getElementById('wizardUserLimit');
            if (userLimitInput && userLimit) {
                userLimitInput.value = userLimit;
                console.log('User limit updated to:', userLimit);
            }

            // Update expiry date based on duration (convert days to months)
            if (duration) {
                const durationDays = parseInt(duration);
                const durationMonths = Math.round(durationDays / 30); // Convert days to months
                console.log('Updating expiry date with', durationDays, 'days (', durationMonths, 'months)');
                updateLicenseExpiryDate(durationMonths);
            }
        });
    }
});

// Update license expiry date
function updateLicenseExpiryDate(durationMonths) {
    const issueDateInput = document.querySelector('.wizard-date-issue');
    const expiryDatePicker = document.querySelector('.wizard-date-expiry')._flatpickr;

    if (!issueDateInput || !expiryDatePicker) return;

    const issueDateValue = issueDateInput.value;
    if (!issueDateValue) return;

    const issueDate = new Date(issueDateValue);
    const expiryDate = new Date(issueDate);

    // If duration is provided from license type, use it, otherwise default to 12 months
    const months = durationMonths || 12;
    expiryDate.setMonth(expiryDate.getMonth() + months);

    expiryDatePicker.setDate(expiryDate);
}
</script>

