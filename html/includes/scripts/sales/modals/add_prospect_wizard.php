<?php
/**
 * Add/Edit Prospect Wizard Modal
 * Multi-step wizard for creating and editing prospects
 */
?>

<!-- Add Prospect Wizard Modal -->
<div class="modal fade" id="addProspectWizardModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Add New Prospect</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Wizard Steps -->
                <div class="wizard-steps mb-4">
                    <div class="d-flex justify-content-between">
                        <div class="wizard-step active" data-step="1">
                            <div class="step-number">1</div>
                            <div class="step-title">Basic Info</div>
                        </div>
                        <div class="wizard-step" data-step="2">
                            <div class="step-number">2</div>
                            <div class="step-title">Classification</div>
                        </div>
                        <div class="wizard-step" data-step="3">
                            <div class="step-number">3</div>
                            <div class="step-title">Qualification</div>
                        </div>
                        <div class="wizard-step" data-step="4">
                            <div class="step-number">4</div>
                            <div class="step-title">Assignment</div>
                        </div>
                        <div class="wizard-step" data-step="5">
                            <div class="step-number">5</div>
                            <div class="step-title">Additional Details</div>
                        </div>
                    </div>
                </div>

                <form id="addProspectWizardForm">
                    <input type="hidden" name="action" value="createProspect">

                    <!-- Step 1: Basic Information -->
                    <div class="wizard-content" data-step="1">
                        <h6 class="mb-3">Basic Information</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Prospect Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="salesProspectName" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="prospectEmail" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="prospectPhone">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Website</label>
                                <input type="url" class="form-control" name="prospectWebsite" placeholder="https://">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Case/Opportunity Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="prospectCaseName" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="address" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Classification -->
                    <div class="wizard-content d-none" data-step="2">
                        <h6 class="mb-3">Classification</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Business Unit <span class="text-danger">*</span></label>
                                <select class="form-select" name="businessUnitID" required>
                                    <?= Form::populate_select_element_from_object($businessUnits, 'businessUnitID', 'businessUnitName', '', '', 'Select Business Unit') ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Lead Source <span class="text-danger">*</span></label>
                                <select class="form-select" name="leadSourceID" required>
                                    <?= Form::populate_select_element_from_object($leadSources, 'leadSourceID', 'leadSourceName', '', '', 'Select Lead Source') ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Industry</label>
                                <select class="form-select" name="industryID">
                                    <?= Form::populate_select_element_from_object($industries, 'industryID', 'industryName', '', '', 'Select Industry') ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Company Size</label>
                                <select class="form-select" name="companySize">
                                    <option value="">Select Size</option>
                                    <option value="small">Small (1-50)</option>
                                    <option value="medium">Medium (51-250)</option>
                                    <option value="large">Large (251-1000)</option>
                                    <option value="enterprise">Enterprise (1000+)</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Source Details</label>
                                <textarea class="form-control" name="sourceDetails" rows="2" placeholder="Additional information about how this lead was acquired..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Qualification -->
                    <div class="wizard-content d-none" data-step="3">
                        <h6 class="mb-3">Qualification & Value</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Estimated Value</label>
                                <input type="number" class="form-control" name="estimatedValue" step="0.01" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Probability (%)</label>
                                <input type="number" class="form-control" name="probability" min="0" max="100" value="50">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Expected Close Date</label>
                                <input type="date" class="form-control" name="expectedCloseDate">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Next Follow-up Date</label>
                                <input type="date" class="form-control" name="nextFollowUpDate">
                            </div>

                            <div class="col-12">
                                <h6 class="mb-2">BANT Qualification</h6>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="budgetConfirmed" value="Y" id="budgetConfirmed">
                                    <label class="form-check-label" for="budgetConfirmed">
                                        Budget Confirmed
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="decisionMakerIdentified" value="Y" id="decisionMakerIdentified">
                                    <label class="form-check-label" for="decisionMakerIdentified">
                                        Decision Maker Identified
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="needIdentified" value="Y" id="needIdentified">
                                    <label class="form-check-label" for="needIdentified">
                                        Need Identified
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="timelineDefined" value="Y" id="timelineDefined">
                                    <label class="form-check-label" for="timelineDefined">
                                        Timeline Defined
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Assignment -->
                    <div class="wizard-content d-none" data-step="4">
                        <h6 class="mb-3">Assignment</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Owner</label>
                                <select class="form-select" name="ownerID">
                                    <?= Form::populate_select_element_from_object($allEmployees, 'ID', 'employeeName', $userDetails->ID, '', 'Select Owner') ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Assigned Team</label>
                                <select class="form-select" name="assignedTeamID">
                                    <?= Form::populate_select_element_from_object($teams, 'teamID', 'teamName', '', '', 'Select Team') ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Territory</label>
                                <select class="form-select" name="territoryID">
                                    <?= Form::populate_select_element_from_object($territories, 'territoryID', 'territoryName', '', '', 'Select Territory') ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="salesProspectStatus">
                                    <option value="open" selected>Open</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Qualification Status</label>
                                <select class="form-select" name="leadQualificationStatus">
                                    <option value="unqualified" selected>Unqualified</option>
                                    <option value="cold">Cold</option>
                                    <option value="warm">Warm</option>
                                    <option value="hot">Hot</option>
                                    <option value="qualified">Qualified</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Additional Details -->
                    <div class="wizard-content d-none" data-step="5">
                        <h6 class="mb-3">Additional Details</h6>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Tags</label>
                                <input type="text" class="form-control" name="tags" placeholder="Enter tags separated by commas">
                                <small class="text-muted">e.g., enterprise, high-priority, referral</small>
                            </div>
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="isClient" value="Y" id="isClient">
                                    <label class="form-check-label" for="isClient">
                                        This prospect is an existing client
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-12 d-none" id="clientSelectionDiv">
                                <label class="form-label">Select Client</label>
                                <select class="form-select" name="clientID">
                                    <option value="">Select Client</option>
                                    <!-- Will be populated dynamically -->
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="wizardPrevBtn" style="display: none;">Previous</button>
                <button type="button" class="btn btn-primary" id="wizardNextBtn">Next</button>
                <button type="button" class="btn btn-success" id="wizardSubmitBtn" style="display: none;">Create Prospect</button>
            </div>
        </div>
    </div>
</div>

<style>
.wizard-steps {
    position: relative;
}

.wizard-steps::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 0;
    right: 0;
    height: 2px;
    background: #e9ecef;
    z-index: 0;
}

.wizard-step {
    flex: 1;
    text-align: center;
    position: relative;
    z-index: 1;
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
    font-weight: 600;
    margin-bottom: 8px;
    transition: all 0.3s;
}

.wizard-step.active .step-number {
    background: #0d6efd;
    color: white;
}

.wizard-step.completed .step-number {
    background: #198754;
    color: white;
}

.wizard-step .step-title {
    font-size: 12px;
    color: #6c757d;
}

.wizard-step.active .step-title {
    color: #0d6efd;
    font-weight: 600;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const wizardModal = document.getElementById('addProspectWizardModal');
    const wizardForm = document.getElementById('addProspectWizardForm');
    const prevBtn = document.getElementById('wizardPrevBtn');
    const nextBtn = document.getElementById('wizardNextBtn');
    const submitBtn = document.getElementById('wizardSubmitBtn');
    let currentStep = 1;
    const totalSteps = 5;

    // Wizard navigation
    function showStep(step) {
        document.querySelectorAll('.wizard-content').forEach(content => {
            content.classList.add('d-none');
        });
        document.querySelector(`.wizard-content[data-step="${step}"]`).classList.remove('d-none');

        document.querySelectorAll('.wizard-step').forEach(stepEl => {
            const stepNum = parseInt(stepEl.dataset.step);
            stepEl.classList.remove('active', 'completed');
            if (stepNum === step) {
                stepEl.classList.add('active');
            } else if (stepNum < step) {
                stepEl.classList.add('completed');
            }
        });

        prevBtn.style.display = step === 1 ? 'none' : 'inline-block';
        nextBtn.style.display = step === totalSteps ? 'none' : 'inline-block';
        submitBtn.style.display = step === totalSteps ? 'inline-block' : 'none';
    }

    nextBtn.addEventListener('click', function() {
        if (validateStep(currentStep)) {
            currentStep++;
            showStep(currentStep);
        }
    });

    prevBtn.addEventListener('click', function() {
        currentStep--;
        showStep(currentStep);
    });

    function validateStep(step) {
        const stepContent = document.querySelector(`.wizard-content[data-step="${step}"]`);
        const requiredFields = stepContent.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        return isValid;
    }

    // Handle isClient checkbox
    document.getElementById('isClient').addEventListener('change', function() {
        document.getElementById('clientSelectionDiv').classList.toggle('d-none', !this.checked);
    });

    // Submit form
    submitBtn.addEventListener('click', function() {
        if (!validateStep(currentStep)) {
            return;
        }

        const formData = new FormData(wizardForm);

        // Convert checkboxes to Y/N
        ['budgetConfirmed', 'decisionMakerIdentified', 'needIdentified', 'timelineDefined', 'isClient'].forEach(field => {
            if (!formData.get(field)) {
                formData.set(field, 'N');
            }
        });

        // Convert tags to JSON
        const tagsInput = formData.get('tags');
        if (tagsInput) {
            const tagsArray = tagsInput.split(',').map(t => t.trim()).filter(t => t);
            formData.set('tags', JSON.stringify(tagsArray));
        }

        fetch('<?= "{$base}php/scripts/sales/manage_prospect_advanced.php" ?>', {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Prospect created successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error creating prospect: ' + error);
        });
    });

    // Initialize Flatpickr for date inputs
    if (typeof flatpickr !== 'undefined') {
        const expectedCloseDateInputs = document.querySelectorAll('input[name="expectedCloseDate"]');
        const nextFollowUpDateInputs = document.querySelectorAll('input[name="nextFollowUpDate"]');

        expectedCloseDateInputs.forEach(input => {
            flatpickr(input, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'F j, Y',
                allowInput: true
            });
        });

        nextFollowUpDateInputs.forEach(input => {
            flatpickr(input, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'F j, Y',
                allowInput: true,
                minDate: 'today'
            });
        });
    }

    // Reset wizard on modal close
    wizardModal.addEventListener('hidden.bs.modal', function() {
        currentStep = 1;
        showStep(1);
        wizardForm.reset();
    });
});
</script>
