<?php
/**
 * Client Creation Wizard
 * Comprehensive multi-step wizard for creating clients with addresses, contacts, and documents
 */

// Get required data
$allEmployees = Employee::employees([], false, $DBConn);
$employeesCategorised = Employee::categorise_employee($allEmployees, 'jobTitle');
$countryList = Data::countries([], false, $DBConn);
$documentTypes = Data::document_types([], false, $DBConn);
$salutations = Admin::salutations([], false, $DBConn);
$contactTypes = Client::contact_types([], false, $DBConn);
?>

<!-- Country Data for JavaScript -->
<script>
    window.clientWizardCountries = <?= json_encode(array_map(function($country) {
        return [
            'id' => $country->countryID,
            'name' => $country->countryName
        ];
    }, $countryList)) ?>;

    window.clientWizardDocumentTypes = <?= json_encode(array_map(function($docType) {
        return [
            'id' => $docType->documentTypeID,
            'name' => $docType->documentTypeName,
            'description' => $docType->documentTypeDescription ?? ''
        ];
    }, $documentTypes ?: [])) ?>;

    window.clientWizardSalutations = <?= json_encode(array_map(function($salutation) {
        return [
            'id' => $salutation->salutationID,
            'name' => $salutation->salutation
        ];
    }, $salutations ?: [])) ?>;

    window.clientWizardContactTypes = <?= json_encode(array_map(function($contactType) {
        return [
            'id' => $contactType->contactTypeID,
            'name' => $contactType->contactType
        ];
    }, $contactTypes ?: [])) ?>;
</script>

<!-- Client Wizard Modal -->
<div class="modal fade" id="clientWizardModal" tabindex="-1" aria-labelledby="clientWizardModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header bg-gradient-primary text-white">
                <div>
                    <h5 class="modal-title fw-bold" id="clientWizardModalLabel">
                        <i class="ri-user-add-line me-2"></i>New Client Onboarding Wizard
                    </h5>
                    <p class="mb-0 small text-white-75">Complete all steps to create a comprehensive client profile</p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Wizard Progress Steps -->
            <div class="wizard-progress-header bg-light border-bottom">
                <div class="container-fluid py-3">
                    <div class="progress-steps d-flex justify-content-between position-relative">
                        <div class="step-item active" data-step="1">
                            <div class="step-circle">
                                <i class="ri-building-line"></i>
                                <span class="step-number">1</span>
                            </div>
                            <div class="step-label">Basic Info</div>
                        </div>
                        <div class="step-item" data-step="2">
                            <div class="step-circle">
                                <i class="ri-map-pin-line"></i>
                                <span class="step-number">2</span>
                            </div>
                            <div class="step-label">Addresses</div>
                        </div>
                        <div class="step-item" data-step="3">
                            <div class="step-circle">
                                <i class="ri-contacts-line"></i>
                                <span class="step-number">3</span>
                            </div>
                            <div class="step-label">Contacts</div>
                        </div>
                        <div class="step-item" data-step="4">
                            <div class="step-circle">
                                <i class="ri-file-text-line"></i>
                                <span class="step-number">4</span>
                            </div>
                            <div class="step-label">Documents</div>
                        </div>
                        <div class="step-item" data-step="5">
                            <div class="step-circle">
                                <i class="ri-checkbox-circle-line"></i>
                                <span class="step-number">5</span>
                            </div>
                            <div class="step-label">Review</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
                <form id="clientWizardForm">
                    <!-- Hidden Fields -->
                    <input type="hidden" name="orgDataID" id="wizardOrgDataID" value="<?= $orgDataID ?>">
                    <input type="hidden" name="entityID" id="wizardEntityID" value="<?= $entityID ?>">

                    <!-- STEP 1: Basic Client Information -->
                    <div class="wizard-step active" data-step="1">
                        <div class="text-center mb-4">
                            <h4 class="fw-semibold text-primary">Client Identity</h4>
                            <p class="text-muted">Enter the basic information about the client organization</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="wizardClientName" class="form-label fw-semibold">
                                    Client Name <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="ri-building-line"></i></span>
                                    <input type="text" class="form-control" id="wizardClientName" name="clientName"
                                           placeholder="e.g., Acme Corporation Ltd." required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="wizardClientCode" class="form-label fw-semibold">Client Code</label>
                                <input type="text" class="form-control" id="wizardClientCode" name="clientCode"
                                       placeholder="Auto-generated" readonly>
                                <small class="text-muted">Auto-generated from name</small>
                            </div>

                            <div class="col-md-6">
                                <label for="wizardVatNumber" class="form-label fw-semibold">PIN / Tax ID</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="ri-article-line"></i></span>
                                    <input type="text" class="form-control" id="wizardVatNumber" name="vatNumber"
                                           placeholder="e.g., P051234567X">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="wizardAccountOwner" class="form-label fw-semibold">
                                    Account Owner <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="ri-user-star-line"></i></span>
                                    <select class="form-select" id="wizardAccountOwner" name="accountOwnerID" required>
                                        <?php echo Form::populate_select_element_from_grouped_object($employeesCategorised, 'ID', 'employeeNameWithInitials', $userDetails->ID, '', 'Select Account Owner') ?>
                                    </select>
                                </div>
                                <small class="text-muted">Primary relationship manager for this client</small>
                            </div>

                            <div class="col-md-12">
                                <label for="wizardClientDescription" class="form-label fw-semibold">Client Description</label>
                                <textarea class="form-control" id="wizardClientDescription" name="clientDescription"
                                          rows="3" placeholder="Brief description of the client's business..."></textarea>
                            </div>

                            <div class="col-md-12">
                                <div class="card bg-light border-0">
                                    <div class="card-body">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="wizardInHouse" name="inHouse" value="Y">
                                            <label class="form-check-label fw-semibold" for="wizardInHouse">
                                                <i class="ri-building-4-line me-1"></i>In-House Client
                                                <small class="text-muted d-block">Mark this if the client is an internal organization</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 2: Addresses -->
                    <div class="wizard-step" data-step="2">
                        <div class="text-center mb-4">
                            <h4 class="fw-semibold text-primary">Client Addresses</h4>
                            <p class="text-muted">Add one or more addresses for the client</p>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0"><i class="ri-map-pin-line me-2"></i>Address List</h6>
                            <button type="button" class="btn btn-sm btn-primary" id="addAddressBtn">
                                <i class="ri-add-line me-1"></i>Add Address
                            </button>
                        </div>

                        <div id="addressesContainer">
                            <!-- Addresses will be added dynamically -->
                        </div>

                        <div class="alert alert-info mt-3">
                            <i class="ri-information-line me-2"></i>
                            <strong>Tip:</strong> You must add at least one address. Mark one as headquarters for primary location.
                        </div>
                    </div>

                    <!-- STEP 3: Contacts -->
                    <div class="wizard-step" data-step="3">
                        <div class="text-center mb-4">
                            <h4 class="fw-semibold text-primary">Client Contacts</h4>
                            <p class="text-muted">Add contact persons and link them to addresses</p>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0"><i class="ri-contacts-line me-2"></i>Contact List</h6>
                            <button type="button" class="btn btn-sm btn-primary" id="addContactBtn">
                                <i class="ri-add-line me-1"></i>Add Contact
                            </button>
                        </div>

                        <div id="contactsContainer">
                            <!-- Contacts will be added dynamically -->
                        </div>

                        <div class="alert alert-warning mt-3">
                            <i class="ri-alert-line me-2"></i>
                            <strong>Note:</strong> Contacts can be linked to specific addresses. This is optional but recommended.
                        </div>
                    </div>

                    <!-- STEP 4: Documents -->
                    <div class="wizard-step" data-step="4">
                        <div class="text-center mb-4">
                            <h4 class="fw-semibold text-primary">Client Documents</h4>
                            <p class="text-muted">Upload important client documents</p>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0"><i class="ri-file-text-line me-2"></i>Document List</h6>
                            <button type="button" class="btn btn-sm btn-primary" id="addDocumentBtn">
                                <i class="ri-add-line me-1"></i>Add Document
                            </button>
                        </div>

                        <div id="documentsContainer">
                            <!-- Documents will be added dynamically -->
                        </div>

                        <div class="alert alert-secondary mt-3">
                            <h6 class="alert-heading"><i class="ri-information-line me-2"></i>Supported Document Types</h6>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <small><strong>Registration:</strong> Certificate of Incorporation, Business License</small>
                                </div>
                                <div class="col-md-4">
                                    <small><strong>Tax:</strong> PIN Certificate, Tax Compliance</small>
                                </div>
                                <div class="col-md-4">
                                    <small><strong>Contracts:</strong> Service Agreements, NDAs</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- STEP 5: Review & Submit -->
                    <div class="wizard-step" data-step="5">
                        <div class="text-center mb-4">
                            <h4 class="fw-semibold text-primary">Review Client Information</h4>
                            <p class="text-muted">Review all details before creating the client</p>
                        </div>

                        <div class="review-summary">
                            <!-- Basic Info -->
                            <div class="card mb-3">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="ri-building-line me-2"></i>Basic Information</h6>
                                </div>
                                <div class="card-body" id="reviewBasicInfo">
                                    <!-- Populated by JavaScript -->
                                </div>
                            </div>

                            <!-- Addresses -->
                            <div class="card mb-3">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="ri-map-pin-line me-2"></i>Addresses (<span id="reviewAddressCount">0</span>)</h6>
                                </div>
                                <div class="card-body" id="reviewAddresses">
                                    <!-- Populated by JavaScript -->
                                </div>
                            </div>

                            <!-- Contacts -->
                            <div class="card mb-3">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="ri-contacts-line me-2"></i>Contacts (<span id="reviewContactCount">0</span>)</h6>
                                </div>
                                <div class="card-body" id="reviewContacts">
                                    <!-- Populated by JavaScript -->
                                </div>
                            </div>

                            <!-- Documents -->
                            <div class="card">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0"><i class="ri-file-text-line me-2"></i>Documents (<span id="reviewDocumentCount">0</span>)</h6>
                                </div>
                                <div class="card-body" id="reviewDocuments">
                                    <!-- Populated by JavaScript -->
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" id="wizardPrevBtn" style="display: none;">
                    <i class="ri-arrow-left-line me-1"></i>Previous
                </button>
                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="wizardNextBtn">
                        Next <i class="ri-arrow-right-line ms-1"></i>
                    </button>
                    <button type="button" class="btn btn-success" id="wizardSubmitBtn" style="display: none;">
                        <i class="ri-check-line me-1"></i>Create Client
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Wizard Progress Styles */
.wizard-progress-header {
    position: sticky;
    top: 0;
    z-index: 10;
}

.progress-steps::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 10%;
    right: 10%;
    height: 2px;
    background: #e0e0e0;
    z-index: 0;
}

.step-item {
    position: relative;
    z-index: 1;
    text-align: center;
    flex: 1;
}

.step-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #fff;
    border: 3px solid #e0e0e0;
    color: #999;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin: 0 auto 8px;
    transition: all 0.3s;
    position: relative;
}

.step-circle i {
    font-size: 20px;
    position: absolute;
}

.step-circle .step-number {
    display: none;
}

.step-item.active .step-circle {
    background: var(--bs-primary);
    border-color: var(--bs-primary);
    color: white;
    box-shadow: 0 0 0 4px rgba(var(--bs-primary-rgb), 0.2);
}

.step-item.completed .step-circle {
    background: var(--bs-success);
    border-color: var(--bs-success);
    color: white;
}

.step-item.completed .step-circle i {
    display: none;
}

.step-item.completed .step-circle .step-number {
    display: block;
}

.step-item.completed .step-circle .step-number::before {
    content: '✓';
}

.step-label {
    font-size: 13px;
    color: #666;
    font-weight: 500;
}

.step-item.active .step-label {
    color: var(--bs-primary);
    font-weight: 600;
}

.step-item.completed .step-label {
    color: var(--bs-success);
}

/* Wizard Step Content */
.wizard-step {
    display: none;
    animation: fadeInUp 0.4s;
}

.wizard-step.active {
    display: block;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Address/Contact/Document Cards */
.item-card {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
    background: #fff;
    transition: all 0.3s;
    position: relative;
}

.item-card:hover {
    border-color: var(--bs-primary);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.item-card-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e9ecef;
}

.item-card-number {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--bs-primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}

.remove-item-btn {
    position: absolute;
    top: 10px;
    right: 10px;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #999;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.5;
}

/* Review Summary */
.review-summary .card {
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.review-item {
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.review-item:last-child {
    border-bottom: none;
}

.review-label {
    font-weight: 600;
    color: #666;
    min-width: 150px;
    display: inline-block;
}

.review-value {
    color: #333;
}

/* Responsive */
@media (max-width: 768px) {
    .progress-steps {
        overflow-x: auto;
        padding-bottom: 10px;
    }

    .step-label {
        font-size: 11px;
    }

    .step-circle {
        width: 40px;
        height: 40px;
    }

    .step-circle i {
        font-size: 16px;
    }
}
</style>

<script src="../assets/js/src/pages/user/clients/wizard.js?v=<?= time() ?>"></script>
