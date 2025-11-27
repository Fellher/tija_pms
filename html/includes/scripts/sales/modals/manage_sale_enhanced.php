<?php
/**
 * Enhanced Sales Management Modal
 * Modern, step-by-step form for creating and editing sales opportunities
 *
 * Features:
 * - Multi-step wizard interface
 * - Real-time validation
 * - Client and contact auto-creation
 * - Smart field dependencies
 * - Progress indicator
 */
?>

<div class="sales-form-wizard">
    <!-- Progress Steps -->
    <div class="wizard-steps mb-4">
        <div class="steps-container">
            <div class="step active" data-step="1">
                <div class="step-number">1</div>
                <div class="step-label">Client & Opportunity</div>
            </div>
            <div class="step" data-step="2">
                <div class="step-number">2</div>
                <div class="step-label">Details & Value</div>
            </div>
            <div class="step" data-step="3">
                <div class="step-number">3</div>
                <div class="step-label">Timeline & Probability</div>
            </div>
        </div>
    </div>

    <form class="enhanced-sales-form" id="enhancedSalesForm">
        <!-- Hidden Fields -->
        <input type="hidden" name="salesCaseID" id="salesCaseID" value="">
        <input type="hidden" name="orgDataID" value="<?= $orgDataID ?>">
        <input type="hidden" name="entityID" value="<?= $entityID ?>">
        <input type="hidden" name="salesPersonID" value="<?= $userDetails->ID ?>">
        <input type="hidden" name="saleStage" value="<?= $state ?>">

        <!-- Step 1: Client & Opportunity -->
        <div class="wizard-step" data-step="1">
            <h5 class="step-title mb-3">
                <i class="ri-building-line text-primary me-2"></i>
                Client & Opportunity Information
            </h5>

            <!-- Client Selection -->
            <div class="form-group mb-3">
                <label for="clientID" class="form-label">
                    Client <span class="text-danger">*</span>
                </label>
                <select id="clientID" name="clientID" class="form-select" required>
                    <option value="">Select a client...</option>
                    <?php
                    $clients = Client::client_full(array('orgDataID' => $orgDataID, 'entityID' => $entityID, 'Suspended' => 'N'), false, $DBConn);
                    if ($clients) {
                        foreach ($clients as $client) {
                            echo "<option value='{$client->clientID}'>{$client->clientName} ({$client->clientCode})</option>";
                        }
                    }
                    ?>
                    <option value="new">+ Add New Client</option>
                </select>
                <div class="form-text">Select an existing client or create a new one</div>
            </div>

            <!-- New Client Fields (Hidden by default) -->
            <div id="newClientFields" class="card bg-light p-3 mb-3 d-none">
                <h6 class="mb-3">
                    <i class="ri-add-circle-line me-2"></i>New Client Information
                    <button type="button" class="btn btn-sm btn-link float-end" id="cancelNewClient">Cancel</button>
                </h6>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="newClientName" class="form-label">Client Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="newClientName" name="newClientName" placeholder="Enter client name">
                    </div>

                    <div class="col-md-6">
                        <label for="newClientCode" class="form-label">Client Code</label>
                        <input type="text" class="form-control" id="newClientCode" name="newClientCode" placeholder="Auto-generated">
                    </div>

                    <div class="col-md-6">
                        <label for="newClientSector" class="form-label">Sector</label>
                        <select class="form-select" id="newClientSector" name="newClientSectorID">
                            <option value="">Select sector...</option>
                            <?php
                            $sectors = Data::tija_sectors([], false, $DBConn);
                            if ($sectors) {
                                foreach ($sectors as $sector) {
                                    echo "<option value='{$sector->sectorID}'>{$sector->sectorName}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="newClientIndustry" class="form-label">Industry</label>
                        <select class="form-select" id="newClientIndustry" name="newClientIndustryID">
                            <option value="">Select industry...</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Contact Person -->
            <div class="form-group mb-3">
                <label for="contactPersonID" class="form-label">Contact Person</label>
                <select id="contactPersonID" name="salesCaseContactID" class="form-select">
                    <option value="">Select contact person...</option>
                </select>
                <div class="form-text">Will be populated based on selected client</div>
            </div>

            <!-- Opportunity Name -->
            <div class="form-group mb-3">
                <label for="salesCaseName" class="form-label">
                    Opportunity Name <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="salesCaseName" name="salesCaseName"
                       placeholder="e.g., Annual Audit 2025" required>
                <div class="form-text">Provide a descriptive name for this opportunity</div>
            </div>

            <!-- Business Unit -->
            <div class="form-group mb-3">
                <label for="businessUnitID" class="form-label">
                    Business Unit <span class="text-danger">*</span>
                </label>
                <select id="businessUnitID" name="businessUnitID" class="form-select" required>
                    <option value="">Select business unit...</option>
                    <?php
                    if ($businessUnits) {
                        foreach ($businessUnits as $unit) {
                            echo "<option value='{$unit->businessUnitID}'>{$unit->businessUnitName}</option>";
                        }
                    }
                    ?>
                </select>
            </div>
        </div>

        <!-- Step 2: Details & Value -->
        <div class="wizard-step d-none" data-step="2">
            <h5 class="step-title mb-3">
                <i class="ri-money-dollar-circle-line text-primary me-2"></i>
                Opportunity Details & Value
            </h5>

            <!-- Estimated Value -->
            <div class="form-group mb-3">
                <label for="salesCaseEstimate" class="form-label">
                    Estimated Value (KES) <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text">KES</span>
                    <input type="number" class="form-control" id="salesCaseEstimate" name="salesCaseEstimate"
                           placeholder="0.00" step="0.01" min="0" required>
                </div>
                <div class="form-text">Enter the estimated value of this opportunity</div>
            </div>

            <!-- Probability -->
            <div class="form-group mb-3">
                <label for="probability" class="form-label">
                    Probability of Winning (%)
                </label>
                <div class="d-flex align-items-center gap-3">
                    <input type="range" class="form-range flex-grow-1" id="probability" name="probability"
                           min="0" max="100" value="50" step="5">
                    <span class="probability-display badge bg-primary">50%</span>
                </div>
                <div class="form-text">
                    Weighted Value: <span class="weighted-value fw-bold">KES 0.00</span>
                </div>
            </div>

            <!-- Status Level -->
            <div class="form-group mb-3">
                <label class="form-label">Status Level <span class="text-danger">*</span></label>
                <div class="status-level-selector">
                    <?php
                    if ($statusLevels) {
                        foreach ($statusLevels as $index => $level) {
                            // Skip the last level (typically "Closed")
                            if ($index < count($statusLevels) - 1) {
                                echo "
                                <div class='form-check'>
                                    <input class='form-check-input' type='radio' name='saleStatusLevelID'
                                           id='statusLevel{$level->saleStatusLevelID}' value='{$level->saleStatusLevelID}'
                                           " . ($index === 0 ? 'checked' : '') . ">
                                    <label class='form-check-label' for='statusLevel{$level->saleStatusLevelID}'>
                                        <span class='fw-semibold'>{$level->statusLevel}</span>
                                        <small class='text-muted d-block'>{$level->StatusLevelDescription}</small>
                                    </label>
                                </div>
                                ";
                            }
                        }
                    }
                    ?>
                </div>
            </div>

            <!-- Lead Source -->
            <div class="form-group mb-3">
                <label for="leadSourceID" class="form-label">Lead Source</label>
                <select id="leadSourceID" name="leadSourceID" class="form-select">
                    <option value="">Select lead source...</option>
                    <?php
                    if ($leadSources && count($leadSources) > 0) {
                        foreach ($leadSources as $source) {
                            echo "<option value='{$source->leadSourceID}'>{$source->leadSourceName}</option>";
                        }
                    } else {
                        echo "<option value='' disabled>No lead sources available - Run setup_lead_sources.php</option>";
                    }
                    ?>
                </select>
                <div class="form-text">
                    <?php if ($leadSources && count($leadSources) > 0): ?>
                        How did you learn about this opportunity?
                    <?php else: ?>
                        <span class="text-warning">
                            <i class="ri-alert-line"></i> No lead sources configured.
                            <a href="<?= $base ?>setup_lead_sources.php" target="_blank" class="text-primary">Click here to set up</a>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Step 3: Timeline & Probability -->
        <div class="wizard-step d-none" data-step="3">
            <h5 class="step-title mb-3">
                <i class="ri-calendar-line text-primary me-2"></i>
                Timeline & Next Steps
            </h5>

            <!-- Expected Close Date -->
            <div class="form-group mb-3">
                <label for="expectedCloseDate" class="form-label">
                    Expected Close Date
                </label>
                <input type="date" class="form-control date" id="expectedCloseDate" name="expectedCloseDate">
                <div class="form-text">When do you expect to close this deal?</div>
            </div>

            <!-- Notes/Description -->
            <div class="form-group mb-3">
                <label for="salesCaseNotes" class="form-label">Notes & Description</label>
                <textarea class="form-control" id="salesCaseNotes" name="salesCaseNotes"
                          rows="4" placeholder="Add any additional notes or description..."></textarea>
            </div>

            <!-- Summary -->
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="ri-file-list-line me-2"></i>Summary
                    </h6>
                    <div id="salesSummary">
                        <div class="row g-3">
                            <div class="col-6">
                                <small class="text-muted d-block">Client</small>
                                <div class="fw-semibold" id="summaryClient">-</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Opportunity</small>
                                <div class="fw-semibold" id="summaryOpportunity">-</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Estimated Value</small>
                                <div class="fw-semibold text-success" id="summaryValue">KES 0.00</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Probability</small>
                                <div class="fw-semibold" id="summaryProbability">0%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="wizard-navigation mt-4 pt-3 border-top">
            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-light" id="wizardPrevBtn" disabled>
                    <i class="ri-arrow-left-line me-1"></i> Previous
                </button>
                <button type="button" class="btn btn-primary" id="wizardNextBtn">
                    Next <i class="ri-arrow-right-line ms-1"></i>
                </button>
                <button type="submit" class="btn btn-success d-none" id="wizardSubmitBtn">
                    <i class="ri-save-line me-1"></i> Save Opportunity
                </button>
            </div>
        </div>
    </form>
</div>

<style>
/* Wizard Styles */
.wizard-steps {
    position: relative;
}

.steps-container {
    display: flex;
    justify-content: space-between;
    position: relative;
}

.steps-container::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 0;
    right: 0;
    height: 2px;
    background: #e9ecef;
    z-index: 0;
}

.step {
    flex: 1;
    text-align: center;
    position: relative;
    z-index: 1;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.step.active .step-number {
    background: #6366f1;
    color: white;
    transform: scale(1.1);
}

.step.completed .step-number {
    background: #10b981;
    color: white;
}

.step-label {
    font-size: 0.875rem;
    color: #6c757d;
    font-weight: 500;
}

.step.active .step-label {
    color: #6366f1;
    font-weight: 600;
}

.step.completed .step-label {
    color: #10b981;
}

/* Form Styles */
.step-title {
    font-size: 1.125rem;
    font-weight: 600;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #e9ecef;
}

.status-level-selector .form-check {
    padding: 0.75rem;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    margin-bottom: 0.5rem;
    transition: all 0.2s ease;
}

.status-level-selector .form-check:hover {
    background: #f8f9fa;
    border-color: #6366f1;
}

.status-level-selector .form-check-input:checked ~ .form-check-label {
    color: #6366f1;
}

.probability-display {
    min-width: 60px;
    font-size: 1rem;
}

.weighted-value {
    color: #10b981;
    font-size: 1.125rem;
}
</style>

<script>
// Enhanced Sales Form Wizard
class SalesFormWizard {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 3;
        this.form = document.getElementById('enhancedSalesForm');
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupDependencies();
        this.setupRealTimeCalculations();
    }

    setupEventListeners() {
        // Navigation buttons
        document.getElementById('wizardNextBtn')?.addEventListener('click', () => this.nextStep());
        document.getElementById('wizardPrevBtn')?.addEventListener('click', () => this.prevStep());

        // Form submission
        this.form?.addEventListener('submit', (e) => this.handleSubmit(e));

        // Client selection
        document.getElementById('clientID')?.addEventListener('change', (e) => this.handleClientChange(e));
        document.getElementById('cancelNewClient')?.addEventListener('click', () => this.cancelNewClient());

        // Sector change for industry filtering
        document.getElementById('newClientSector')?.addEventListener('change', (e) => this.filterIndustries(e));
    }

    setupDependencies() {
        // Update contact list when client changes
        const clientSelect = document.getElementById('clientID');
        if (clientSelect) {
            clientSelect.addEventListener('change', () => {
                this.loadClientContacts(clientSelect.value);
            });
        }
    }

    setupRealTimeCalculations() {
        // Probability slider
        const probabilitySlider = document.getElementById('probability');
        const probabilityDisplay = document.querySelector('.probability-display');
        const estimateInput = document.getElementById('salesCaseEstimate');
        const weightedValueDisplay = document.querySelector('.weighted-value');

        if (probabilitySlider && probabilityDisplay) {
            probabilitySlider.addEventListener('input', (e) => {
                const value = e.target.value;
                probabilityDisplay.textContent = `${value}%`;
                this.updateWeightedValue();
                this.updateSummary();
            });
        }

        if (estimateInput) {
            estimateInput.addEventListener('input', () => {
                this.updateWeightedValue();
                this.updateSummary();
            });
        }

        // Real-time summary updates
        ['clientID', 'salesCaseName', 'salesCaseEstimate', 'probability'].forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('change', () => this.updateSummary());
                element.addEventListener('input', () => this.updateSummary());
            }
        });
    }

    nextStep() {
        if (!this.validateCurrentStep()) {
            return;
        }

        if (this.currentStep < this.totalSteps) {
            this.currentStep++;
            this.updateStepDisplay();
        }
    }

    prevStep() {
        if (this.currentStep > 1) {
            this.currentStep--;
            this.updateStepDisplay();
        }
    }

    updateStepDisplay() {
        // Hide all steps
        document.querySelectorAll('.wizard-step').forEach(step => {
            step.classList.add('d-none');
        });

        // Show current step
        document.querySelector(`.wizard-step[data-step="${this.currentStep}"]`)?.classList.remove('d-none');

        // Update step indicators
        document.querySelectorAll('.step').forEach((step, index) => {
            const stepNum = index + 1;
            step.classList.remove('active', 'completed');

            if (stepNum === this.currentStep) {
                step.classList.add('active');
            } else if (stepNum < this.currentStep) {
                step.classList.add('completed');
            }
        });

        // Update navigation buttons
        const prevBtn = document.getElementById('wizardPrevBtn');
        const nextBtn = document.getElementById('wizardNextBtn');
        const submitBtn = document.getElementById('wizardSubmitBtn');

        if (prevBtn) prevBtn.disabled = this.currentStep === 1;

        if (this.currentStep === this.totalSteps) {
            nextBtn?.classList.add('d-none');
            submitBtn?.classList.remove('d-none');
        } else {
            nextBtn?.classList.remove('d-none');
            submitBtn?.classList.add('d-none');
        }

        // Update summary on last step
        if (this.currentStep === this.totalSteps) {
            this.updateSummary();
        }
    }

    validateCurrentStep() {
        const currentStepElement = document.querySelector(`.wizard-step[data-step="${this.currentStep}"]`);
        if (!currentStepElement) return true;

        const requiredFields = currentStepElement.querySelectorAll('[required]');
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

    handleClientChange(e) {
        const value = e.target.value;
        const newClientFields = document.getElementById('newClientFields');

        if (value === 'new') {
            newClientFields?.classList.remove('d-none');
        } else {
            newClientFields?.classList.add('d-none');
            this.loadClientContacts(value);
        }
    }

    cancelNewClient() {
        document.getElementById('clientID').value = '';
        document.getElementById('newClientFields')?.classList.add('d-none');
    }

    loadClientContacts(clientID) {
        const contactSelect = document.getElementById('contactPersonID');
        if (!contactSelect || !clientID) return;

        // Show loading state
        contactSelect.innerHTML = '<option value="">Loading contacts...</option>';

        // Get base path from SalesDashboard or construct it
        const basePath = (typeof SalesDashboard !== 'undefined' && SalesDashboard.config) ?
                         SalesDashboard.config.base : '/pms_skim.co.ke_rev/';

        // Fetch contacts from API
        fetch(`${basePath}php/scripts/clients/get_client_contacts.php?clientID=${clientID}`)
            .then(response => response.json())
            .then(data => {
                contactSelect.innerHTML = '<option value="">Select contact person...</option>';

                if (data.success && data.contacts && data.contacts.length > 0) {
                    data.contacts.forEach(contact => {
                        const option = document.createElement('option');
                        option.value = contact.clientContactID;
                        option.textContent = `${contact.contactName}${contact.contactEmail ? ' (' + contact.contactEmail + ')' : ''}`;
                        contactSelect.appendChild(option);
                    });
                } else {
                    // No contacts found
                    contactSelect.innerHTML += '<option value="" disabled>No contacts found for this client</option>';
                }
            })
            .catch(error => {
                console.error('Error loading contacts:', error);
                contactSelect.innerHTML = '<option value="">Select contact person...</option>';
                contactSelect.innerHTML += '<option value="" disabled>Error loading contacts</option>';
            });
    }

    filterIndustries(e) {
        const sectorID = e.target.value;
        const industrySelect = document.getElementById('newClientIndustry');

        if (!industrySelect || !sectorID) return;

        // This would filter industries based on sector
        // Implementation depends on your data structure
    }

    updateWeightedValue() {
        const estimate = parseFloat(document.getElementById('salesCaseEstimate')?.value || 0);
        const probability = parseFloat(document.getElementById('probability')?.value || 0);
        const weighted = estimate * (probability / 100);

        const weightedDisplay = document.querySelector('.weighted-value');
        if (weightedDisplay) {
            weightedDisplay.textContent = `KES ${this.formatNumber(weighted)}`;
        }
    }

    updateSummary() {
        // Client
        const clientSelect = document.getElementById('clientID');
        const clientText = clientSelect?.options[clientSelect.selectedIndex]?.text || '-';
        document.getElementById('summaryClient').textContent = clientText !== 'Select a client...' ? clientText : '-';

        // Opportunity
        const opportunityName = document.getElementById('salesCaseName')?.value || '-';
        document.getElementById('summaryOpportunity').textContent = opportunityName;

        // Value
        const estimate = parseFloat(document.getElementById('salesCaseEstimate')?.value || 0);
        document.getElementById('summaryValue').textContent = `KES ${this.formatNumber(estimate)}`;

        // Probability
        const probability = document.getElementById('probability')?.value || '0';
        document.getElementById('summaryProbability').textContent = `${probability}%`;
    }

    handleSubmit(e) {
        e.preventDefault();

        if (!this.validateCurrentStep()) {
            return;
        }

        // Submit form via AJAX
        const formData = new FormData(this.form);

        fetch('<?= $base ?>php/scripts/sales/manage_sale.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Sales opportunity saved successfully!');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to save'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving');
        });
    }

    formatNumber(num) {
        return new Intl.NumberFormat('en-KE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(num || 0);
    }
}

// Initialize wizard when modal is shown
document.getElementById('quickAddModal')?.addEventListener('shown.bs.modal', function() {
    if (!window.salesFormWizard) {
        window.salesFormWizard = new SalesFormWizard();
    }

    // Initialize Flatpickr for expected close date
    const expectedCloseDateInput = document.getElementById('expectedCloseDate');
    if (expectedCloseDateInput && typeof flatpickr !== 'undefined') {
        flatpickr(expectedCloseDateInput, {
            dateFormat: 'Y-m-d',
            minDate: 'today',  // Disable past dates
            enableTime: false,
            altInput: true,
            altFormat: 'M d, Y',
            defaultDate: null,
            onChange: function(selectedDates, dateStr, instance) {
                console.log('Expected close date selected:', dateStr);
            }
        });
    } else if (!expectedCloseDateInput) {
        console.warn('Expected close date input not found');
    } else if (typeof flatpickr === 'undefined') {
        console.warn('Flatpickr library not loaded - using default date input');
    }
});
</script>

