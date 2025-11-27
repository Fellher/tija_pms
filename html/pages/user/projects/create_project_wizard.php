<?php
/**
 * Project Creation Wizard
 * 4-step guided project setup for enterprise-level project management
 *
 * @package Tija Practice Management System
 * @subpackage Projects
 * @version 2.0
 */

// Security check
if (!isset($_SESSION['ID'])) {
    header('Location: ' . $base . 'html/?s=user&p=login');
    exit;
}

// Include security middleware
require_once $base . 'php/middleware/SecurityMiddleware.php';

// Validate session
if (!SecurityMiddleware::validateSession()) {
    header('Location: ' . $base . 'html/?s=user&p=login');
    exit;
}

// Get lookups
$clients = Client::clients_full(['orgDataID' => $userDetails->orgDataID, 'Suspended' => 'N'], false, $DBConn);
$projectTypes = ['fixed_price' => 'Fixed Price', 'time_material' => 'Time & Material', 'retainer' => 'Retainer', 'milestone' => 'Milestone-Based'];
$employees = Employee::employees(['orgDataID' => $userDetails->orgDataID, 'entityID' => $userDetails->entityID, 'Suspended' => 'N'], false, $DBConn);
$jobTitles = Data::job_titles(['Suspended' => 'N'], false, $DBConn);
?>

<!-- Project Creation Wizard Container -->
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <!-- Wizard Header -->
            <div class="card custom-card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h3 class="mb-2"><i class="fas fa-magic me-2 text-primary"></i>Create New Project</h3>
                        <p class="text-muted">Follow the guided steps to set up your project</p>
                    </div>

                    <!-- Progress Indicator -->
                    <div class="wizard-progress mb-4">
                        <div class="row text-center">
                            <div class="col-3">
                                <div class="wizard-step active" data-step="1">
                                    <div class="step-icon">
                                        <div class="avatar avatar-lg bg-primary mx-auto">
                                            <i class="fas fa-info-circle"></i>
                                        </div>
                                    </div>
                                    <h6 class="mt-2 mb-0">Basic Info</h6>
                                    <small class="text-muted">Project details</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="wizard-step" data-step="2">
                                    <div class="step-icon">
                                        <div class="avatar avatar-lg bg-secondary mx-auto">
                                            <i class="fas fa-users"></i>
                                        </div>
                                    </div>
                                    <h6 class="mt-2 mb-0">Team</h6>
                                    <small class="text-muted">Assign members</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="wizard-step" data-step="3">
                                    <div class="step-icon">
                                        <div class="avatar avatar-lg bg-secondary mx-auto">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                    </div>
                                    <h6 class="mt-2 mb-0">Budget</h6>
                                    <small class="text-muted">Financial setup</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="wizard-step" data-step="4">
                                    <div class="step-icon">
                                        <div class="avatar avatar-lg bg-secondary mx-auto">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                    </div>
                                    <h6 class="mt-2 mb-0">Review</h6>
                                    <small class="text-muted">Confirm & create</small>
                                </div>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 4px;">
                            <div class="progress-bar bg-primary" id="wizardProgressBar" style="width: 25%"></div>
                        </div>
                    </div>

                    <!-- Wizard Form -->
                    <form id="projectWizardForm" method="POST" action="<?= $base ?>php/scripts/projects/create_project_wizard.php">
                        <?= SecurityMiddleware::csrfTokenField() ?>
                        <input type="hidden" name="orgDataID" value="<?= $userDetails->orgDataID ?>">
                        <input type="hidden" name="entityID" value="<?= $userDetails->entityID ?>">
                        <input type="hidden" name="createdBy" value="<?= $userDetails->ID ?>">

                        <!-- Step 1: Basic Information -->
                        <div class="wizard-content" data-step="1">
                            <h5 class="mb-3"><i class="fas fa-info-circle text-primary me-2"></i>Project Basic Information</h5>

                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">Project Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="projectName" id="projectName" required
                                        placeholder="e.g., Website Redesign 2025">
                                    <div class="invalid-feedback">Please enter a project name (min 3 characters)</div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Project Code</label>
                                    <input type="text" class="form-control" name="projectCode" id="projectCode"
                                        placeholder="e.g., WR-2025">
                                    <small class="text-muted">Auto-generated if empty</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Client <span class="text-danger">*</span></label>
                                    <select class="form-select" name="clientID" id="clientID" required>
                                        <option value="">-- Select Client --</option>
                                        <?php if ($clients && is_array($clients)): foreach ($clients as $client): ?>
                                            <option value="<?= $client->clientID ?>">
                                                <?= htmlspecialchars($client->clientName) ?>
                                            </option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Project Type <span class="text-danger">*</span></label>
                                    <select class="form-select" name="projectType" id="projectType" required>
                                        <option value="">-- Select Type --</option>
                                        <?php foreach ($projectTypes as $key => $label): ?>
                                            <option value="<?= $key ?>"><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="projectStart" id="projectStart" required
                                        value="<?= date('Y-m-d') ?>">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Deadline <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="projectDeadline" id="projectDeadline" required>
                                    <small class="text-muted">Target completion date</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Expected Close Date</label>
                                    <input type="date" class="form-control" name="projectClose" id="projectClose">
                                    <small class="text-muted">Actual/expected close date</small>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Project Description</label>
                                    <textarea class="form-control" name="projectDescription" rows="4"
                                        placeholder="Describe the project scope, objectives, and deliverables..."></textarea>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Priority</label>
                                    <select class="form-select" name="priority">
                                        <option value="medium" selected>Medium</option>
                                        <option value="low">Low</option>
                                        <option value="high">High</option>
                                        <option value="critical">Critical</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Project Status</label>
                                    <select class="form-select" name="projectStatus">
                                        <option value="planning" selected>Planning</option>
                                        <option value="active">Active</option>
                                        <option value="on_hold">On Hold</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Team Selection -->
                        <div class="wizard-content d-none" data-step="2">
                            <h5 class="mb-3"><i class="fas fa-users text-primary me-2"></i>Assign Team Members</h5>

                            <div class="row">
                                                <div class="col-md-6">
                                    <label class="form-label">Project Owner/Manager <span class="text-danger">*</span></label>
                                    <select class="form-select" name="projectOwnerID" id="projectOwnerID" required>
                                        <option value="">-- Select Project Owner --</option>
                                        <?php if ($employees && is_array($employees)): foreach ($employees as $emp): ?>
                                            <option value="<?= $emp->ID ?>" <?= $emp->ID == $userDetails->ID ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($emp->FirstName . ' ' . $emp->Surname) ?>
                                                <?php if (isset($emp->jobTitle)): ?>
                                                    - <?= htmlspecialchars($emp->jobTitle) ?>
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; endif; ?>
                                    </select>
                                </div>

                                <div class="col-md-12 mt-4">
                                    <h6 class="mb-3">Select Team Members</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header bg-light">
                                                    <h6 class="mb-0">Available Team Members</h6>
                                                </div>
                                                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                                    <div id="availableTeamMembers">
                                                        <?php if ($employees && is_array($employees)): foreach ($employees as $emp): ?>
                                                            <div class="form-check mb-2 team-member-item" data-employee-id="<?= $emp->ID ?>">
                                                                <input class="form-check-input" type="checkbox"
                                                                    id="team_<?= $emp->ID ?>"
                                                                    name="teamMembers[]"
                                                                    value="<?= $emp->ID ?>">
                                                                <label class="form-check-label w-100" for="team_<?= $emp->ID ?>">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="avatar avatar-sm bg-primary-transparent me-2">
                                                                            <i class="fas fa-user"></i>
                                                                        </div>
                                                                        <div>
                                                                            <strong><?= htmlspecialchars($emp->FirstName . ' ' . $emp->Surname) ?></strong>
                                                                            <br><small class="text-muted"><?= htmlspecialchars($emp->jobTitle ?? 'Employee') ?></small>
                                                                        </div>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                        <?php endforeach; endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="card border-primary">
                                                <div class="card-header bg-primary text-white">
                                                    <h6 class="mb-0">Selected Team Members (<span id="selectedCount">0</span>)</h6>
                                                </div>
                                                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                                    <div id="selectedTeamMembers">
                                                        <p class="text-muted text-center py-4">No members selected yet</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Budget & Billing -->
                        <div class="wizard-content d-none" data-step="3">
                            <h5 class="mb-3"><i class="fas fa-money-bill-wave text-primary me-2"></i>Budget & Billing Setup</h5>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Total Project Budget <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">KES</span>
                                        <input type="number" class="form-control" name="totalBudget" id="totalBudget"
                                            placeholder="0.00" step="0.01" min="0" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Billing Model</label>
                                    <select class="form-select" name="billingModel" id="billingModel">
                                        <option value="fixed">Fixed Price</option>
                                        <option value="hourly">Hourly Rate</option>
                                        <option value="milestone">Milestone-Based</option>
                                        <option value="retainer">Monthly Retainer</option>
                                    </select>
                                </div>

                                <div class="col-md-6" id="hourlyRateField" style="display: none;">
                                    <label class="form-label">Hourly Rate</label>
                                    <div class="input-group">
                                        <span class="input-group-text">KES</span>
                                        <input type="number" class="form-control" name="hourlyRate"
                                            placeholder="0.00" step="0.01" min="0">
                                        <span class="input-group-text">/hour</span>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Payment Terms</label>
                                    <select class="form-select" name="paymentTerms">
                                        <option value="net_30">Net 30 Days</option>
                                        <option value="net_15">Net 15 Days</option>
                                        <option value="net_7">Net 7 Days</option>
                                        <option value="immediate">Due on Receipt</option>
                                        <option value="advance">Advance Payment</option>
                                    </select>
                                </div>

                                <div class="col-md-12 mt-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="mb-3">Project Milestones (Optional)</h6>
                                            <div id="milestonesContainer">
                                                <div class="milestone-item mb-3">
                                                    <div class="row g-2">
                                                        <div class="col-md-5">
                                                            <input type="text" class="form-control form-control-sm"
                                                                name="milestones[0][name]" placeholder="Milestone name">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="number" class="form-control form-control-sm"
                                                                name="milestones[0][percentage]" placeholder="% of payment"
                                                                min="0" max="100">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="date" class="form-control form-control-sm"
                                                                name="milestones[0][dueDate]">
                                                        </div>
                                                        <div class="col-md-1">
                                                            <button type="button" class="btn btn-sm btn-danger" onclick="removeMilestone(this)">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-success" onclick="addMilestone()">
                                                <i class="fas fa-plus me-1"></i>Add Milestone
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4: Review & Confirm -->
                        <div class="wizard-content d-none" data-step="4">
                            <h5 class="mb-3"><i class="fas fa-check-circle text-primary me-2"></i>Review & Confirm</h5>

                            <div class="alert alert-info mb-4">
                                <i class="fas fa-info-circle me-2"></i>
                                Please review all information before creating the project.
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card mb-3">
                                        <div class="card-header bg-primary text-white">
                                            <h6 class="mb-0">Project Information</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="review-item">
                                                <strong>Project Name:</strong>
                                                <span id="review_projectName">-</span>
                                            </div>
                                            <div class="review-item">
                                                <strong>Client:</strong>
                                                <span id="review_client">-</span>
                                            </div>
                                            <div class="review-item">
                                                <strong>Type:</strong>
                                                <span id="review_type">-</span>
                                            </div>
                                            <div class="review-item">
                                                <strong>Duration:</strong>
                                                <span id="review_duration">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card mb-3">
                                        <div class="card-header bg-success text-white">
                                            <h6 class="mb-0">Team & Budget</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="review-item">
                                                <strong>Project Manager:</strong>
                                                <span id="review_manager">-</span>
                                            </div>
                                            <div class="review-item">
                                                <strong>Team Size:</strong>
                                                <span id="review_teamSize">0</span> members
                                            </div>
                                            <div class="review-item">
                                                <strong>Total Budget:</strong>
                                                <span id="review_budget">KES 0</span>
                                            </div>
                                            <div class="review-item">
                                                <strong>Billing Model:</strong>
                                                <span id="review_billing">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Wizard Navigation -->
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" id="prevBtn" onclick="previousStep()" style="display: none;">
                                <i class="fas fa-arrow-left me-2"></i>Previous
                            </button>

                            <div class="ms-auto d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary" onclick="saveDraft()">
                                    <i class="fas fa-save me-2"></i>Save as Draft
                                </button>
                                <button type="button" class="btn btn-primary" id="nextBtn" onclick="nextStep()">
                                    Next<i class="fas fa-arrow-right ms-2"></i>
                                </button>
                                <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                                    <i class="fas fa-check me-2"></i>Create Project
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentStep = 1;
const totalSteps = 4;

// Navigate to next step
function nextStep() {
    if (!validateCurrentStep()) {
        return;
    }

    if (currentStep < totalSteps) {
        currentStep++;
        updateWizardDisplay();
    }
}

// Navigate to previous step
function previousStep() {
    if (currentStep > 1) {
        currentStep--;
        updateWizardDisplay();
    }
}

// Update wizard UI
function updateWizardDisplay() {
    // Hide all content
    document.querySelectorAll('.wizard-content').forEach(el => el.classList.add('d-none'));

    // Show current step content
    document.querySelector(`.wizard-content[data-step="${currentStep}"]`).classList.remove('d-none');

    // Update step indicators
    document.querySelectorAll('.wizard-step').forEach((el, index) => {
        const step = index + 1;
        if (step < currentStep) {
            el.classList.remove('active');
            el.classList.add('completed');
            el.querySelector('.avatar').classList.remove('bg-secondary', 'bg-primary');
            el.querySelector('.avatar').classList.add('bg-success');
        } else if (step === currentStep) {
            el.classList.add('active');
            el.classList.remove('completed');
            el.querySelector('.avatar').classList.remove('bg-secondary', 'bg-success');
            el.querySelector('.avatar').classList.add('bg-primary');
        } else {
            el.classList.remove('active', 'completed');
            el.querySelector('.avatar').classList.remove('bg-primary', 'bg-success');
            el.querySelector('.avatar').classList.add('bg-secondary');
        }
    });

    // Update progress bar
    const progress = (currentStep / totalSteps) * 100;
    document.getElementById('wizardProgressBar').style.width = progress + '%';

    // Update button visibility
    document.getElementById('prevBtn').style.display = currentStep > 1 ? 'block' : 'none';
    document.getElementById('nextBtn').style.display = currentStep < totalSteps ? 'block' : 'none';
    document.getElementById('submitBtn').style.display = currentStep === totalSteps ? 'block' : 'none';

    // Update review step if on step 4
    if (currentStep === 4) {
        updateReviewStep();
    }
}

// Validate current step
function validateCurrentStep() {
    const currentContent = document.querySelector(`.wizard-content[data-step="${currentStep}"]`);
    const requiredFields = currentContent.querySelectorAll('[required]');

    let isValid = true;
    requiredFields.forEach(field => {
        if (!field.value) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        }
    });

    if (!isValid) {
        if (typeof showToast === 'function') {
            showToast('Please fill in all required fields before proceeding.', 'warning');
        } else {
            alert('Please fill in all required fields before proceeding.');
        }
    }

    return isValid;
}

// Update review step with form data
function updateReviewStep() {
    // Project name
    document.getElementById('review_projectName').textContent =
        document.getElementById('projectName').value || '-';

    // Client
    const clientSelect = document.getElementById('clientID');
    document.getElementById('review_client').textContent =
        clientSelect.options[clientSelect.selectedIndex]?.text || '-';

    // Project type
    const typeSelect = document.getElementById('projectType');
    document.getElementById('review_type').textContent =
        typeSelect.options[typeSelect.selectedIndex]?.text || '-';

    // Duration
    const startDate = document.getElementById('projectStart').value;
    const endDate = document.getElementById('projectDeadline').value;
    if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
        document.getElementById('review_duration').textContent =
            `${days} days (${start.toLocaleDateString()} - ${end.toLocaleDateString()})`;
    }

    // Project owner/manager
    const ownerSelect = document.getElementById('projectOwnerID');
    document.getElementById('review_manager').textContent =
        ownerSelect.options[ownerSelect.selectedIndex]?.text || '-';

    // Team size
    const selectedTeam = document.querySelectorAll('input[name="teamMembers[]"]:checked');
    document.getElementById('review_teamSize').textContent = selectedTeam.length;

    // Budget
    const budget = document.getElementById('totalBudget').value;
    document.getElementById('review_budget').textContent =
        budget ? 'KES ' + parseFloat(budget).toLocaleString() : 'KES 0';

    // Billing model
    const billingSelect = document.getElementById('billingModel');
    document.getElementById('review_billing').textContent =
        billingSelect.options[billingSelect.selectedIndex]?.text || '-';
}

// Update selected team members display
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('input[name="teamMembers[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedTeamDisplay();
        });
    });

    // Billing model change
    document.getElementById('billingModel')?.addEventListener('change', function() {
        const hourlyField = document.getElementById('hourlyRateField');
        if (this.value === 'hourly') {
            hourlyField.style.display = 'block';
        } else {
            hourlyField.style.display = 'none';
        }
    });
});

function updateSelectedTeamDisplay() {
    const selectedCheckboxes = document.querySelectorAll('input[name="teamMembers[]"]:checked');
    const selectedContainer = document.getElementById('selectedTeamMembers');
    const countSpan = document.getElementById('selectedCount');

    countSpan.textContent = selectedCheckboxes.length;

    if (selectedCheckboxes.length === 0) {
        selectedContainer.innerHTML = '<p class="text-muted text-center py-4">No members selected yet</p>';
        return;
    }

    let html = '';
    selectedCheckboxes.forEach(checkbox => {
        const label = checkbox.nextElementSibling;
        const employeeID = checkbox.value;
        html += `
            <div class="selected-member-item mb-2 p-2 border rounded bg-white">
                ${label.innerHTML}
            </div>
        `;
    });

    selectedContainer.innerHTML = html;
}

// Add milestone row
let milestoneCount = 1;
function addMilestone() {
    const container = document.getElementById('milestonesContainer');
    const html = `
        <div class="milestone-item mb-3">
            <div class="row g-2">
                <div class="col-md-5">
                    <input type="text" class="form-control form-control-sm"
                        name="milestones[${milestoneCount}][name]" placeholder="Milestone name">
                </div>
                <div class="col-md-3">
                    <input type="number" class="form-control form-control-sm"
                        name="milestones[${milestoneCount}][percentage]" placeholder="% of payment"
                        min="0" max="100">
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control form-control-sm"
                        name="milestones[${milestoneCount}][dueDate]">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeMilestone(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    milestoneCount++;
}

function removeMilestone(btn) {
    btn.closest('.milestone-item').remove();
}

function saveDraft() {
    const formData = new FormData(document.getElementById('projectWizardForm'));
    formData.append('isDraft', 'Y');

    fetch('<?= $base ?>php/scripts/projects/save_project_draft.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof showToast === 'function') {
                showToast('Draft saved successfully!', 'success');
            } else {
                alert('Draft saved successfully!');
            }
        } else {
            if (typeof showToast === 'function') {
                showToast('Failed to save draft: ' + data.message, 'error');
            } else {
                alert('Failed to save draft: ' + data.message);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof showToast === 'function') {
            showToast('Error saving draft', 'error');
        } else {
            alert('Error saving draft');
        }
    });
}

// Form submission
document.getElementById('projectWizardForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Project...';
});
</script>

<style>
.wizard-step {
    transition: all 0.3s ease;
}

.wizard-step.active .avatar {
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.25);
}

.wizard-step.completed .avatar i::after {
    content: "\f00c";
    font-family: "Font Awesome 5 Free";
    position: absolute;
}

.review-item {
    padding: 10px 0;
    border-bottom: 1px solid #e9ecef;
}

.review-item:last-child {
    border-bottom: none;
}

.review-item strong {
    display: inline-block;
    min-width: 150px;
}

.selected-member-item:hover {
    background-color: #f8f9fa !important;
}

.avatar-lg {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.avatar-sm {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}
</style>

