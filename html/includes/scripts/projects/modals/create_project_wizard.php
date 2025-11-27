<!-- Project Creation Wizard -->
<div class="project-wizard-container">
    <!-- Wizard Progress Steps -->
    <div class="wizard-steps mb-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between position-relative">
                    <!-- Progress Line -->
                    <div class="wizard-progress-line"></div>

                    <!-- Step 1: Project Type -->
                    <div class="wizard-step active" data-step="1">
                        <div class="step-circle">
                            <i class="ri-file-list-3-line"></i>
                            <span class="step-number">1</span>
                        </div>
                        <div class="step-label">Project Type</div>
                    </div>
                      <!-- Step 2: Recurring information -->
                      <div class="wizard-step" data-step="2">
                        <div class="step-circle">
                            <i class="ri-file-list-3-line"></i>
                            <span class="step-number">2</span>
                        </div>
                        <div class="step-label">Recurring information</div>
                    </div>




                    <!-- Step 3: Basic Info -->
                    <div class="wizard-step" data-step="3">
                        <div class="step-circle">
                            <i class="ri-information-line"></i>
                            <span class="step-number">3</span>
                        </div>
                        <div class="step-label">Basic Info</div>
                    </div>

                    <!-- Step 4: Team -->
                    <div class="wizard-step" data-step="4">
                        <div class="step-circle">
                            <i class="ri-team-line"></i>
                            <span class="step-number">4</span>
                        </div>
                        <div class="step-label">Team</div>
                    </div>

                    <!-- Step 5: Project Plan -->
                    <div class="wizard-step" data-step="5">
                        <div class="step-circle">
                            <i class="ri-task-line"></i>
                            <span class="step-number">5</span>
                        </div>
                        <div class="step-label">Plan</div>
                    </div>

                    <!-- Step 6: Billing -->
                    <div class="wizard-step" data-step="6">
                        <div class="step-circle">
                            <i class="ri-money-dollar-circle-line"></i>
                            <span class="step-number">6</span>
                        </div>
                        <div class="step-label">Billing</div>
                    </div>

                    <!-- Step 7: Review -->
                    <div class="wizard-step" data-step="7">
                        <div class="step-circle">
                            <i class="ri-checkbox-circle-line"></i>
                            <span class="step-number">7</span>
                        </div>
                        <div class="step-label">Review</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Content -->
    <form id="projectWizardForm" action="<?= $base ?>php/scripts/projects/manage_project_wizard.php" method="POST" class="manageProjectsForm">
        <!-- Hidden Fields -->
        <input type="hidden" name="orgDataID" value="<?= $orgDataID ?>">
        <input type="hidden" name="entityID" value="<?= $entityID ?>">
        <input type="hidden" name="projectID" class="projectID" value="">
        <input type="hidden" name="wizardAction" value="create">

        <!-- STEP 1: Project Type & Client Selection -->
        <div class="wizard-content" data-step="1">
            <div class="text-center mb-4">
                <h4 class="fw-semibold">Choose Project Type</h4>
                <p class="text-muted">Select whether this is a client project or internal initiative</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-10">
                    <!-- Project Type Cards -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="project-type-card card custom-card border-2 h-100" data-type="client">
                                <div class="card-body p-4 text-center">
                                    <div class="avatar avatar-xl bg-primary-transparent mx-auto mb-3">
                                        <i class="fas fa-user-tie fs-30"></i>
                                    </div>
                                    <h5 class="mb-2">Client Project</h5>
                                    <p class="text-muted mb-3">External client engagement or billable work</p>
                                    <input type="radio" name="projectTypeID" value="1" id="projectTypeClient" class="form-check-input project-type-radio" required>
                                    <label for="projectTypeClient" class="form-check-label">Select Client Project</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="project-type-card card custom-card border-2 h-100" data-type="internal">
                                <div class="card-body p-4 text-center">
                                    <div class="avatar avatar-xl bg-success-transparent mx-auto mb-3">
                                        <i class="fas fa-building fs-30"></i>
                                    </div>
                                    <h5 class="mb-2">Internal Project</h5>
                                    <p class="text-muted mb-3">In-house initiative or non-billable work</p>
                                    <input type="radio" name="projectTypeID" value="2" id="projectTypeInternal" class="form-check-input project-type-radio" required>
                                    <label for="projectTypeInternal" class="form-check-label">Select Internal Project</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Client Selection (shown only for client projects) -->
                    <div id="clientSelectionDiv" class="card custom-card mb-4" style="display: none;">
                        <div class="card-body">
                            <h6 class="mb-3"><i class="ri-user-line me-2"></i>Select Client</h6>
                            <div class="form-group">
                                <label class="form-label">Client <span class="text-danger">*</span></label>
                                <select name="clientID" id="wizardClientID" class="form-select">
                                    <option value="">Select Client</option>
                                    <?php foreach($clients as $client): ?>
                                        <option value="<?= $client->clientID ?>">
                                            <?= htmlspecialchars($client->clientName) ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <option value="new">+ Add New Client</option>
                                </select>
                            </div>

                            <!-- New Client Form (shown when "Add New Client" is selected) -->
                            <div id="newClientForm" class="mt-3" style="display: none;">
                                <div class="alert alert-info">
                                    <i class="ri-information-line me-2"></i>
                                    <strong>Quick Client Creation</strong> - Fill in basic details to create a new client
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <label class="form-label">Client Name <span class="text-danger">*</span></label>
                                        <input type="text" name="clientName" id="newClientName" class="form-control form-control-sm" placeholder="Enter client name">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Industry Sector</label>
                                        <select name="clientSectorID" id="newClientSector" class="form-select form-control-sm">
                                            <option value="">Select Sector</option>
                                            <?php foreach($industrySectors as $sector): ?>
                                                <option value="<?= $sector->sectorID ?>">
                                                    <?= htmlspecialchars($sector->sectorName) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Industry</label>
                                        <select name="clientIndustryID" id="newClientIndustry" class="form-select form-control-sm">
                                            <option value="">Select Industry</option>
                                            <?php foreach($industries as $industry): ?>
                                                <option value="<?= $industry->industryID ?>" data-sector="<?= $industry->sectorID ?>">
                                                    <?= htmlspecialchars($industry->industryName) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Country</label>
                                        <select name="countryID" id="newClientCountry" class="form-select form-control-sm">
                                            <option value="">Select Country</option>
                                            <?php foreach($countries as $country): ?>
                                                <option value="<?= $country->countryID ?>">
                                                    <?= htmlspecialchars($country->countryName) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">City</label>
                                        <input type="text" name="city" id="newClientCity" class="form-control form-control-sm" placeholder="City">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 2: Recurring Project Selection -->
        <div class="wizard-content" data-step="2" style="display: none;">
            <div class="text-center mb-4">
                <h4 class="fw-semibold">Project Recurring Selection</h4>
                <p class="text-muted">Configure recurring project settings</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-10">
                    <!-- Recurring Project Selection (shown when client is selected) -->
                    <div id="recurringSelectionDiv" style="display: none;" class="mb-4">
                        <div class="card custom-card border-warning">
                            <div class="card-body">
                                <h6 class="mb-3"><i class="ri-repeat-line me-2"></i>Project Type</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-check form-check-card">
                                            <input class="form-check-input" type="radio" name="isRecurring" id="projectTypeOneOff" value="N" checked>
                                            <label class="form-check-label w-100" for="projectTypeOneOff">
                                                <div class="card border h-100 p-3 text-center">
                                                    <i class="ri-file-list-3-line fs-24 text-primary mb-2"></i>
                                                    <strong>One-Off Project</strong>
                                                    <small class="text-muted d-block mt-1">Single project with a defined start and end</small>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check form-check-card">
                                            <input class="form-check-input" type="radio" name="isRecurring" id="projectTypeRecurring" value="Y">
                                            <label class="form-check-label w-100" for="projectTypeRecurring">
                                                <div class="card border h-100 p-3 text-center">
                                                    <i class="ri-repeat-line fs-24 text-warning mb-2"></i>
                                                    <strong>Recurring Project</strong>
                                                    <small class="text-muted d-block mt-1">Periodic service (e.g., monthly bookkeeping, quarterly financials)</small>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recurring Configuration (shown when recurring is selected) -->
                        <div id="recurringConfigSection" style="display: none;" class="mt-4">
                                <!-- Recurrence Type -->
                                <div class="card custom-card mb-3">
                                    <div class="card-body">
                                        <h6 class="mb-3"><i class="ri-repeat-line me-2"></i>Recurrence Pattern</h6>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Recurrence Type <span class="text-danger">*</span></label>
                                                <select name="recurrenceType" id="recurrenceType" class="form-select">
                                                    <option value="">Select frequency</option>
                                                    <option value="weekly">Weekly</option>
                                                    <option value="monthly">Monthly</option>
                                                    <option value="quarterly">Quarterly</option>
                                                    <option value="annually">Annually</option>
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Interval</label>
                                                <input type="number" name="recurrenceInterval" id="recurrenceInterval" class="form-control" value="1" min="1" placeholder="Every N periods">
                                                <small class="text-muted">e.g., Every 2 weeks, Every 3 months</small>
                                            </div>
                                        </div>

                                        <!-- Weekly Options -->
                                        <div id="weeklyOptions" class="mt-3" style="display: none;">
                                            <label class="form-label">Day of Week</label>
                                            <select name="recurrenceDayOfWeek" id="recurrenceDayOfWeek" class="form-select">
                                                <option value="1">Monday</option>
                                                <option value="2">Tuesday</option>
                                                <option value="3">Wednesday</option>
                                                <option value="4">Thursday</option>
                                                <option value="5">Friday</option>
                                                <option value="6">Saturday</option>
                                                <option value="7">Sunday</option>
                                            </select>
                                        </div>

                                        <!-- Monthly/Quarterly Options -->
                                        <div id="monthlyOptions" class="mt-3" style="display: none;">
                                            <label class="form-label">Day of Month</label>
                                            <input type="number" name="recurrenceDayOfMonth" id="recurrenceDayOfMonth" class="form-control" min="1" max="31" placeholder="1-31">
                                        </div>

                                        <!-- Annual Options -->
                                        <div id="annuallyOptions" class="mt-3" style="display: none;">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Month</label>
                                                    <select name="recurrenceMonthOfYear" id="recurrenceMonthOfYear" class="form-select">
                                                        <option value="1">January</option>
                                                        <option value="2">February</option>
                                                        <option value="3">March</option>
                                                        <option value="4">April</option>
                                                        <option value="5">May</option>
                                                        <option value="6">June</option>
                                                        <option value="7">July</option>
                                                        <option value="8">August</option>
                                                        <option value="9">September</option>
                                                        <option value="10">October</option>
                                                        <option value="11">November</option>
                                                        <option value="12">December</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Day of Month</label>
                                                    <input type="number" name="recurrenceDayOfMonth" id="recurrenceDayOfMonthAnnual" class="form-control" min="1" max="31" placeholder="1-31">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Recurrence Dates -->
                                <div class="card custom-card mb-3">
                                    <div class="card-body">
                                        <h6 class="mb-3"><i class="ri-calendar-line me-2"></i>Recurrence Schedule</h6>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Start Date</label>
                                                <input type="text" name="recurrenceStartDate" id="recurrenceStartDate" class="form-control flatpickr-input" placeholder="Select start date" readonly>
                                                <small class="text-muted">Defaults to project start date</small>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">End Date (Optional)</label>
                                                <input type="text" name="recurrenceEndDate" id="recurrenceEndDate" class="form-control flatpickr-input" placeholder="Select end date (optional)" readonly>
                                                <small class="text-muted">Leave empty for indefinite recurrence</small>
                                            </div>
                                        </div>

                                        <div class="row g-3 mt-2">
                                            <div class="col-md-6">
                                                <label class="form-label">Number of Cycles (Optional)</label>
                                                <input type="number" name="recurrenceCount" id="recurrenceCount" class="form-control" min="1" placeholder="e.g., 12 for 12 months">
                                                <small class="text-muted">Leave empty for indefinite</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Billing Configuration -->
                                <div class="card custom-card mb-3">
                                    <div class="card-body">
                                        <h6 class="mb-3"><i class="ri-money-dollar-circle-line me-2"></i>Billing Configuration</h6>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Billing Amount per Cycle</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">KES</span>
                                                    <input type="number" name="billingCycleAmount" id="billingCycleAmount" class="form-control" step="0.01" min="0" placeholder="0.00">
                                                </div>
                                                <small class="text-muted">Amount to bill for each billing cycle</small>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label">Invoice Days Before Due</label>
                                                <input type="number" name="invoiceDaysBeforeDue" id="invoiceDaysBeforeDue" class="form-control" value="7" min="0" max="30">
                                                <small class="text-muted">Days before cycle end to generate invoice draft</small>
                                            </div>
                                        </div>

                                        <div class="form-check form-switch mt-3">
                                            <input class="form-check-input" type="checkbox" id="autoGenerateInvoices" name="autoGenerateInvoices" value="Y">
                                            <label class="form-check-label" for="autoGenerateInvoices">
                                                <strong>Automatically generate invoice drafts</strong>
                                                <small class="text-muted d-block">When enabled, invoice drafts will be created automatically when billing cycles are due</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Plan Reuse Mode -->
                                <div class="card custom-card mb-3">
                                    <div class="card-body">
                                        <h6 class="mb-3"><i class="ri-task-line me-2"></i>Project Plan Reuse</h6>

                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="planReuseMode" id="planReuseSame" value="same" checked>
                                            <label class="form-check-label" for="planReuseSame">
                                                <strong>Use same plan for all cycles</strong>
                                                <small class="text-muted d-block">All billing cycles will use the same project plan</small>
                                            </label>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="planReuseMode" id="planReuseCustomizable" value="customizable">
                                            <label class="form-check-label" for="planReuseCustomizable">
                                                <strong>Allow plan customization per cycle</strong>
                                                <small class="text-muted d-block">You can customize the project plan for each billing cycle</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Team Assignment Mode -->
                                <div class="card custom-card">
                                    <div class="card-body">
                                        <h6 class="mb-3"><i class="ri-team-line me-2"></i>Team Assignment Mode</h6>

                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="teamAssignmentMode" id="teamModeTemplate" value="template" checked>
                                            <label class="form-check-label" for="teamModeTemplate">
                                                <strong>Template (same team for all cycles)</strong>
                                                <small class="text-muted d-block">All billing cycles will use the same team</small>
                                            </label>
                                        </div>

                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="teamAssignmentMode" id="teamModeInstance" value="instance">
                                            <label class="form-check-label" for="teamModeInstance">
                                                <strong>Instance (different team per cycle)</strong>
                                                <small class="text-muted d-block">You can assign different teams for each billing cycle</small>
                                            </label>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="teamAssignmentMode" id="teamModeBoth" value="both">
                                            <label class="form-check-label" for="teamModeBoth">
                                                <strong>Both (template with instance overrides)</strong>
                                                <small class="text-muted d-block">Use template team but allow per-cycle customization</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 3: Basic Project Information -->
        <div class="wizard-content" data-step="3" style="display: none;">
            <div class="text-center mb-4">
                <h4 class="fw-semibold">Project Details</h4>
                <p class="text-muted">Enter the basic information about your project</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Project Name <span class="text-danger">*</span></label>
                            <input type="text" name="projectName" id="wizardProjectName" class="form-control" placeholder="Enter project name" required>
                            <small class="text-muted">A clear, descriptive name for your project</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="ri-calendar-line me-1"></i>Project Start Date <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="projectStart" id="wizardProjectStart" class="form-control flatpickr-input" placeholder="Select start date" value="<?= date('Y-m-d') ?>" required readonly>
                            <small class="text-muted">When does the project begin?</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="ri-calendar-check-line me-1"></i>Project End Date <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="projectClose" id="wizardProjectClose" class="form-control flatpickr-input" placeholder="Select end date" required readonly>
                            <small class="text-muted">When is the project expected to complete?</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="ri-calendar-event-line me-1"></i>Project Deadline
                            </label>
                            <input type="text" name="projectDeadline" id="wizardProjectDeadline" class="form-control flatpickr-input" placeholder="Select deadline (optional)" readonly>
                            <small class="text-muted">Final delivery deadline (defaults to end date)</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Project Value</label>
                            <input type="number" name="projectValue" id="wizardProjectValue" class="form-control" placeholder="0.00" step="0.01" min="0">
                            <small class="text-muted">Estimated project value (<?= $config['project']['display']['currency'] ?>)</small>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Business Unit</label>
                            <select name="businessUnitID" id="wizardBusinessUnit" class="form-select">
                                <option value="">Select Business Unit</option>
                                <?php foreach($businessUnits as $unit): ?>
                                    <option value="<?= $unit->businessUnitID ?>">
                                        <?= htmlspecialchars($unit->businessUnitName) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Assign this project to a specific business unit</small>
                        </div>

                        <!-- Note: Project description field removed as it's not in the database schema -->
                        <!-- Description can be added later via project notes/documents feature -->
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 4: Team Assignment -->
        <div class="wizard-content" data-step="4" style="display: none;">
            <div class="text-center mb-4">
                <h4 class="fw-semibold">Assign Project Team</h4>
                <p class="text-muted">Select the project owner, managers, and team members</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="row g-4">
                        <!-- Project Owner -->
                        <div class="col-md-12">
                            <div class="card custom-card border-primary">
                                <div class="card-body">
                                    <h6 class="mb-3"><i class="ri-user-star-line me-2"></i>Project Owner</h6>
                                    <div class="form-group">
                                        <label class="form-label">Who will own this project? <span class="text-danger">*</span></label>
                                        <select name="projectOwnerID" id="wizardProjectOwner" class="form-select" required>
                                            <option value="">Select Project Owner</option>
                                            <?php
                                            if($employeeCategorised):
                                                foreach($employeeCategorised as $category => $employees): ?>
                                                    <optgroup label="<?= htmlspecialchars($category) ?>">
                                                        <?php foreach($employees as $employee): ?>
                                                            <option value="<?= $employee->ID ?>" <?= $employee->ID == $userDetails->ID ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($employee->employeeNameWithInitials) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </optgroup>
                                                <?php endforeach;
                                            endif; ?>
                                        </select>
                                        <small class="text-muted">The person ultimately responsible for project success</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Project Managers -->
                        <div class="col-md-12">
                            <div class="card custom-card border-success">
                                <div class="card-body">
                                    <h6 class="mb-3"><i class="ri-user-settings-line me-2"></i>Project Managers</h6>
                                    <div class="form-group">
                                        <label class="form-label">Who will manage day-to-day operations?</label>
                                        <select name="projectManagersIDs[]" id="wizardProjectManagers" class="form-select" multiple>
                                            <?php foreach($allEmployees as $employee): ?>
                                                <option value="<?= $employee->ID ?>">
                                                    <?= htmlspecialchars($employee->employeeNameWithInitials) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">You can select multiple managers</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Team Members -->
                        <div class="col-md-12">
                            <div class="card custom-card border-info">
                                <div class="card-body">
                                    <h6 class="mb-3"><i class="ri-team-line me-2"></i>Team Members (Optional)</h6>
                                    <div class="form-group">
                                        <label class="form-label">Add team members who will work on this project</label>
                                        <select name="teamMemberIDs[]" id="wizardTeamMembers" class="form-select" multiple>
                                            <?php foreach($allEmployees as $employee): ?>
                                                <option value="<?= $employee->ID ?>">
                                                    <?= htmlspecialchars($employee->employeeNameWithInitials) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">You can add more team members later</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 5: Project Plan (Optional) -->
        <div class="wizard-content" data-step="5" style="display: none;">
            <div class="text-center mb-4">
                <h4 class="fw-semibold">Project Plan</h4>
                <p class="text-muted">Define project phases or use a template (optional - can be done later)</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="alert alert-info mb-4">
                        <i class="ri-information-line me-2"></i>
                        <strong>Optional Step:</strong> You can skip this and add your project plan later from the project details page.
                    </div>

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" id="skipProjectPlan" name="skipProjectPlan" value="1" checked>
                        <label class="form-check-label" for="skipProjectPlan">
                            <strong>Skip project plan for now</strong>
                            <small class="text-muted d-block">I'll add phases and tasks later</small>
                        </label>
                    </div>

                    <div id="projectPlanSection" style="display: none;">
                        <!-- Template Selection -->
                        <div class="card custom-card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="ri-file-copy-line me-2"></i>Choose a Template
                                </h6>
                                <div class="d-flex gap-2 align-items-center">
                                    <small class="text-muted" id="templateStatusText">
                                        <span id="templateLoadingText">
                                            <i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i>Loading...
                                        </span>
                                        <span id="templateReadyText" style="display:none;"></span>
                                    </small>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="refreshTemplatesBtn" title="Refresh templates list">
                                        <i class="ri-refresh-line me-1"></i>Refresh
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- View Mode Tabs -->
                                <ul class="nav nav-tabs nav-tabs-sm mb-3" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="system-tab" data-bs-toggle="tab" href="#systemTemplatesTab" role="tab">
                                            <i class="ri-star-line me-1"></i>System Templates
                                            <span class="badge bg-primary ms-1" id="systemTemplateCount">0</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="org-tab" data-bs-toggle="tab" href="#orgTemplatesTab" role="tab">
                                            <i class="ri-building-line me-1"></i>Organization
                                            <span class="badge bg-success ms-1" id="orgTemplateCount">0</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="my-tab" data-bs-toggle="tab" href="#myTemplatesTab" role="tab">
                                            <i class="ri-user-line me-1"></i>My Templates
                                            <span class="badge bg-secondary ms-1" id="myTemplateCount">0</span>
                                        </a>
                                    </li>
                                </ul>

                                <!-- Tab Content -->
                                <div class="tab-content">
                                    <!-- System Templates -->
                                    <div class="tab-pane fade show active" id="systemTemplatesTab" role="tabpanel">
                                        <div class="row g-3" id="systemTemplatesContainer">
                                            <div class="col-12 text-center py-4 text-muted">
                                                <i class="ri-loader-4-line spinner-border"></i>
                                                <p class="mt-2 mb-0">Loading templates...</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Organization Templates -->
                                    <div class="tab-pane fade" id="orgTemplatesTab" role="tabpanel">
                                        <div class="row g-3" id="orgTemplatesContainer">
                                            <div class="col-12 text-center py-4 text-muted">
                                                <i class="ri-loader-4-line spinner-border"></i>
                                                <p class="mt-2 mb-0">Loading templates...</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- My Templates -->
                                    <div class="tab-pane fade" id="myTemplatesTab" role="tabpanel">
                                        <div class="row g-3" id="myTemplatesContainer">
                                            <div class="col-12 text-center py-4 text-muted">
                                                <i class="ri-loader-4-line spinner-border"></i>
                                                <p class="mt-2 mb-0">Loading templates...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-info mt-3" id="templateInfoMessage" style="display: none;">
                                    <i class="ri-information-line me-2"></i>
                                    <span id="templateInfoText"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Manual Phases -->
                        <div class="card custom-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="card-title mb-0">
                                        <i class="ri-list-check me-2"></i>Project Phases
                                    </h6>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary" id="clearPhasesBtn" title="Clear all phases">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-primary" id="saveAsTemplateBtn" title="Save as template">
                                            <i class="ri-save-line"></i> Save as Template
                                        </button>
                                    </div>
                                </div>
                                <div id="projectPhasesContainer">
                                    <!-- Phases will be added dynamically -->
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm mt-3" id="addPhaseBtn">
                                    <i class="ri-add-line me-1"></i>Add Phase
                                </button>
                                <small class="text-muted d-block mt-2">
                                    Phases will be automatically distributed across your project timeline
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 6: Billing Setup -->
        <div class="wizard-content" data-step="6" style="display: none;">
            <div class="text-center mb-4">
                <h4 class="fw-semibold">Billing Configuration</h4>
                <p class="text-muted">Set up billing rates and time tracking settings</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Billing Rate</label>
                            <select name="billingRateID" id="wizardBillingRate" class="form-select">
                                <option value="">Select Billing Rate</option>
                                <?php foreach($billingRates as $rate): ?>
                                    <option value="<?= $rate->billingRateID ?>">
                                        <?= htmlspecialchars($rate->billingRate) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Time Rounding</label>
                            <select name="roundingoff" id="wizardRounding" class="form-select">
                                <?php foreach($config['roundingOptions'] as $option): ?>
                                    <option value="<?= $option->key ?>">
                                        <?= htmlspecialchars($option->value) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 roundingIntervalDiv" style="display: none;">
                            <label class="form-label">Rounding Interval</label>
                            <select name="roundingInterval" id="wizardRoundingInterval" class="form-select">
                                <?php foreach($config['roundingOffParams'] as $param): ?>
                                    <option value="<?= $param->key ?>">
                                        <?= htmlspecialchars($param->value) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <div class="alert alert-secondary">
                                <h6 class="alert-heading"><i class="ri-information-line me-2"></i>Time Rounding Explanation</h6>
                                <p class="mb-0 small">
                                    Time rounding automatically adjusts logged hours for billing purposes.
                                    For example, with 15-minute rounding, 1h 8m becomes 1h 15m.
                                    Actual duration is always preserved.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="trackBillableHours" id="trackBillableHours" value="Y" checked>
                                <label class="form-check-label" for="trackBillableHours">
                                    <strong>Track billable hours</strong>
                                    <small class="text-muted d-block">Enable time tracking and billing for this project</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 7: Review & Submit -->
        <div class="wizard-content" data-step="7" style="display: none;">
            <div class="text-center mb-4">
                <h4 class="fw-semibold">Review Your Project</h4>
                <p class="text-muted">Review all details before creating the project</p>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="updateReviewSummary()">
                    <i class="ri-refresh-line me-1"></i>Refresh Summary
                </button>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-10">
                    <!-- Review Summary Cards -->
                    <div class="row g-3">
                        <!-- Project Type & Client -->
                        <div class="col-md-6">
                            <div class="card custom-card border-primary border-2">
                                <div class="card-header bg-primary-transparent">
                                    <h6 class="mb-0"><i class="ri-file-list-3-line me-2"></i>Project Type & Client</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="ri-file-text-line text-primary me-2 fs-18"></i>
                                        <div>
                                            <small class="text-muted d-block">Project Type</small>
                                            <strong id="reviewProjectType">-</strong>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="ri-user-line text-primary me-2 fs-18"></i>
                                        <div>
                                            <small class="text-muted d-block">Client</small>
                                            <strong id="reviewClient">-</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Project Details -->
                        <div class="col-md-6">
                            <div class="card custom-card border-success border-2">
                                <div class="card-header bg-success-transparent">
                                    <h6 class="mb-0"><i class="ri-information-line me-2"></i>Project Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="ri-text text-success me-2 fs-18"></i>
                                        <div class="flex-fill">
                                            <small class="text-muted d-block">Project Name</small>
                                            <strong id="reviewProjectName" class="d-block text-truncate" style="max-width: 300px;">-</strong>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="ri-calendar-line text-success me-2 fs-18"></i>
                                        <div>
                                            <small class="text-muted d-block">Timeline</small>
                                            <strong id="reviewTimeline">-</strong>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="ri-money-dollar-circle-line text-success me-2 fs-18"></i>
                                        <div>
                                            <small class="text-muted d-block">Project Value</small>
                                            <strong id="reviewValue">-</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Team -->
                        <div class="col-md-6">
                            <div class="card custom-card border-info border-2">
                                <div class="card-header bg-info-transparent">
                                    <h6 class="mb-0"><i class="ri-team-line me-2"></i>Project Team</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-start mb-2">
                                        <i class="ri-user-star-line text-info me-2 fs-18"></i>
                                        <div class="flex-fill">
                                            <small class="text-muted d-block">Project Owner</small>
                                            <strong id="reviewOwner">-</strong>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-start mb-2">
                                        <i class="ri-user-settings-line text-info me-2 fs-18"></i>
                                        <div class="flex-fill">
                                            <small class="text-muted d-block">Project Managers</small>
                                            <strong id="reviewManagers" class="small">-</strong>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-start">
                                        <i class="ri-group-line text-info me-2 fs-18"></i>
                                        <div class="flex-fill">
                                            <small class="text-muted d-block">Team Members</small>
                                            <strong id="reviewTeamMembers" class="small">-</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Billing -->
                        <div class="col-md-6">
                            <div class="card custom-card border-warning border-2">
                                <div class="card-header bg-warning-transparent">
                                    <h6 class="mb-0"><i class="ri-money-dollar-circle-line me-2"></i>Billing Settings</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-start mb-2">
                                        <i class="ri-price-tag-3-line text-warning me-2 fs-18"></i>
                                        <div class="flex-fill">
                                            <small class="text-muted d-block">Billing Rate</small>
                                            <strong id="reviewBillingRate">-</strong>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-start">
                                        <i class="ri-time-line text-warning me-2 fs-18"></i>
                                        <div class="flex-fill">
                                            <small class="text-muted d-block">Time Rounding</small>
                                            <strong id="reviewRounding" class="small">-</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Project Plan / Phases -->
                        <div class="col-md-12">
                            <div class="card custom-card border-secondary border-2">
                                <div class="card-header bg-secondary-transparent">
                                    <h6 class="mb-0"><i class="ri-file-list-2-line me-2"></i>Project Plan</h6>
                                </div>
                                <div class="card-body">
                                    <div id="reviewPhasesContainer">
                                        <div class="d-flex align-items-start">
                                            <i class="ri-list-check text-secondary me-2 fs-18"></i>
                                            <div class="flex-fill">
                                                <small class="text-muted d-block">Phases</small>
                                                <div id="reviewPhases" class="mt-2">
                                                    <span class="text-muted">-</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-success mt-4">
                        <i class="ri-check-double-line me-2"></i>
                        <strong>Ready to create!</strong> Click "Create Project" to finalize, or go back to make changes.
                    </div>

                    <!-- Debug Button (Remove in production) -->
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="debugWizardData()">
                            <i class="ri-bug-line me-1"></i>Debug Form Data
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Wizard Navigation -->
    <div class="wizard-footer mt-4 pt-3 border-top">
        <div class="d-flex justify-content-between align-items-center">
            <button type="button" class="btn btn-secondary" id="wizardPrevBtn" style="display: none;">
                <i class="ri-arrow-left-line me-1"></i>Previous
            </button>

            <div class="flex-fill text-center">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="saveDraftBtn">
                    <i class="ri-save-line me-1"></i>Save as Draft
                </button>
            </div>

            <button type="button" class="btn btn-primary" id="wizardNextBtn">
                Next <i class="ri-arrow-right-line ms-1"></i>
            </button>

            <button type="submit" class="btn btn-success" id="wizardSubmitBtn" style="display: none;">
                <i class="ri-check-line me-1"></i>Create Project
            </button>
        </div>
    </div>
</div>

<!-- Wizard Styles -->
<style>
/* ================================================================ */
/* WIZARD CONTAINER */
/* ================================================================ */
.project-wizard-container {
    padding: 1.5rem;
}

/* ================================================================ */
/* WIZARD STEPS INDICATOR */
/* ================================================================ */
.wizard-steps {
    position: relative;
}

.wizard-progress-line {
    position: absolute;
    top: 20px;
    left: 8.33%;
    right: 8.33%;
    height: 3px;
    background: #e9ecef;
    z-index: 0;
    --progress: 0%;
}

.wizard-progress-line::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background: linear-gradient(90deg, #0d6efd, #198754);
    width: var(--progress, 0%);
    transition: width 0.5s ease;
    border-radius: 3px;
}

.wizard-step {
    flex: 1;
    text-align: center;
    position: relative;
    z-index: 1;
}

.step-circle {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: #fff;
    border: 3px solid #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    position: relative;
    transition: all 0.3s ease;
}

.step-circle i {
    font-size: 20px;
    color: #6c757d;
    display: block;
}

.step-circle .step-number {
    font-size: 14px;
    font-weight: 600;
    color: #6c757d;
    display: none;
}

.wizard-step.active .step-circle {
    border-color: #0d6efd;
    background: #0d6efd;
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.2);
}

.wizard-step.active .step-circle i {
    color: white;
}

.wizard-step.completed .step-circle {
    border-color: #198754;
    background: #198754;
}

.wizard-step.completed .step-circle i {
    display: none;
}

.wizard-step.completed .step-circle .step-number {
    display: block;
    color: white;
}

.wizard-step.completed .step-circle::after {
    content: '\2713';
    position: absolute;
    color: white;
    font-size: 20px;
    font-weight: bold;
}

.step-label {
    font-size: 12px;
    color: #6c757d;
    font-weight: 500;
}

.wizard-step.active .step-label {
    color: #0d6efd;
    font-weight: 600;
}

.wizard-step.completed .step-label {
    color: #198754;
}

/* ================================================================ */
/* PROJECT TYPE CARDS */
/* ================================================================ */
.project-type-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border-color: #e9ecef !important;
}

.project-type-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.project-type-card.selected {
    border-color: #0d6efd !important;
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.05), rgba(255, 255, 255, 1));
}

.project-type-card .form-check-input {
    width: 20px;
    height: 20px;
    margin-top: 0;
}

/* ================================================================ */
/* WIZARD CONTENT */
/* ================================================================ */
.wizard-content {
    min-height: 400px;
    animation: fadeInUp 0.4s ease;
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

/* ================================================================ */
/* FLATPICKR DATE PICKER STYLING */
/* ================================================================ */
.flatpickr-input[readonly] {
    background-color: #fff !important;
    cursor: pointer;
}

.flatpickr-calendar {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    border: 1px solid #e9ecef !important;
    border-radius: 0.5rem !important;
}

.flatpickr-day.selected,
.flatpickr-day.selected:hover {
    background: #0d6efd !important;
    border-color: #0d6efd !important;
}

.flatpickr-day.today {
    border-color: #0d6efd !important;
}

.flatpickr-day.today:hover {
    background: rgba(13, 110, 253, 0.1) !important;
}

.flatpickr-months .flatpickr-month {
    background: transparent !important;
    color: #1a1a1a !important;
    padding: 10px 0 !important;
}

.flatpickr-current-month .flatpickr-monthDropdown-months {
    background: transparent !important;
    color: #1a1a1a !important;
    font-weight: 600 !important;
    margin-right: 0.75rem !important;
}

.flatpickr-current-month .numInputWrapper {
    color: #1a1a1a !important;
    font-weight: 600 !important;
    margin-left: 0.5rem !important;
}

.flatpickr-current-month .numInputWrapper input {
    color: #1a1a1a !important;
    font-weight: 600 !important;
}

.flatpickr-months .flatpickr-prev-month,
.flatpickr-months .flatpickr-next-month {
    fill: #1a1a1a !important;
    color: #1a1a1a !important;
}

.flatpickr-months .flatpickr-prev-month:hover,
.flatpickr-months .flatpickr-next-month:hover {
    background: rgba(13, 110, 253, 0.15) !important;
    fill: #0d6efd !important;
    color: #0d6efd !important;
}

.flatpickr-months .flatpickr-prev-month svg,
.flatpickr-months .flatpickr-next-month svg {
    fill: #1a1a1a !important;
}

.flatpickr-months .flatpickr-prev-month:hover svg,
.flatpickr-months .flatpickr-next-month:hover svg {
    fill: #0d6efd !important;
}

.calendar-icon {
    font-size: 18px;
    transition: color 0.2s ease;
}

.form-control:focus + .calendar-icon {
    color: #0d6efd !important;
}

/* Date input wrapper for icon positioning */
.form-group:has(.flatpickr-input) {
    position: relative;
}

/* ================================================================ */
/* RESPONSIVE DESIGN */
/* ================================================================ */
@media (max-width: 768px) {
    .wizard-step {
        flex: 0 0 auto;
        margin: 0 5px;
    }

    .step-circle {
        width: 35px;
        height: 35px;
    }

    .step-circle i {
        font-size: 16px;
    }

    .step-label {
        font-size: 10px;
    }

    .wizard-progress-line {
        display: none;
    }

    .flatpickr-calendar {
        width: 100% !important;
    }
}

/* ================================================================ */
/* PROJECT PLAN TEMPLATES & PHASES */
/* ================================================================ */

/* Template Selection Cards */
.template-selection-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    position: relative;
    overflow: hidden;
}

.template-selection-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(180deg, #0d6efd 0%, #0056b3 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.template-selection-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
    border-color: rgba(13, 110, 253, 0.3);
}

.template-selection-card:hover::before {
    opacity: 1;
}

.template-selection-card.selected {
    border-color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.03);
}

.template-selection-card.selected::before {
    opacity: 1;
    width: 6px;
}

.template-selection-card .load-template-btn {
    transition: all 0.2s ease;
}

.template-selection-card:hover .load-template-btn {
    transform: scale(1.05);
}

.template-selection-card .template-name {
    font-size: 0.95rem;
    font-weight: 600;
    color: #212529;
}

/* Tab styling */
.nav-tabs-sm .nav-link {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.nav-tabs-sm .nav-link.active {
    font-weight: 600;
}

/* Template preview modal */
.template-phases-preview {
    max-height: 400px;
    overflow-y: auto;
}

.template-phases-preview::-webkit-scrollbar {
    width: 6px;
}

.template-phases-preview::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 3px;
}

.template-phases-preview::-webkit-scrollbar-thumb:hover {
    background: #adb5bd;
}

/* Phase Items */
.phase-item {
    border-left: 3px solid #0d6efd;
    transition: all 0.2s ease;
}

.phase-item:hover {
    transform: translateX(5px);
    box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1) !important;
}

.phase-item .remove-phase {
    opacity: 0.6;
    transition: opacity 0.2s ease;
}

.phase-item:hover .remove-phase {
    opacity: 1;
}

/* Template status */
#templateStatusText {
    font-size: 0.8rem;
}

/* Animation */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.wizard-content[data-step="5"] .alert-info {
    animation: fadeIn 0.3s ease;
}

/* Buttons */
#saveAsTemplateBtn {
    white-space: nowrap;
}

/* ================================================================ */
/* REVIEW SUMMARY CARDS */
/* ================================================================ */
.wizard-content[data-step="7"] .card {
    transition: all 0.3s ease;
}

.wizard-content[data-step="7"] .card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
}

.wizard-content[data-step="7"] .card-body strong {
    color: #212529;
    font-size: 0.95rem;
}

.wizard-content[data-step="7"] .card-body small {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.wizard-content[data-step="7"] .card-header {
    border-bottom: 2px solid currentColor;
    padding: 0.75rem 1rem;
}

.review-team-member {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.5rem;
    margin: 0.25rem;
    background: #f8f9fa;
    border-radius: 20px;
    font-size: 0.875rem;
}

/* Review value highlighting */
#reviewValue,
#reviewProjectName,
#reviewTimeline {
    color: #0d6efd !important;
}

/* Empty review values */
.wizard-content[data-step="7"] strong:contains("-") {
    color: #6c757d !important;
    font-style: italic;
}
</style>

<!-- Wizard JavaScript -->
<script>
(function() {
    'use strict';

    let currentStep = 1;
    let totalSteps = 7; // Steps: 1-Project Type, 2-Recurring Selection, 3-Basic Info, 4-Team, 5-Plan, 6-Billing, 7-Review
    let wizardData = {};

    document.addEventListener('DOMContentLoaded', function() {
        initializeWizard();
        setupRecurringProjectHandlers();
    });

    // ================================================================
    // RECURRING PROJECT HANDLERS
    // ================================================================
    function setupRecurringProjectHandlers() {
        const clientSelect = document.getElementById('wizardClientID');
        const recurringSelectionDiv = document.getElementById('recurringSelectionDiv');
        const recurringConfigSection = document.getElementById('recurringConfigSection');
        const projectTypeOneOff = document.getElementById('projectTypeOneOff');
        const projectTypeRecurring = document.getElementById('projectTypeRecurring');
        const recurrenceTypeSelect = document.getElementById('recurrenceType');
        const weeklyOptions = document.getElementById('weeklyOptions');
        const monthlyOptions = document.getElementById('monthlyOptions');
        const annuallyOptions = document.getElementById('annuallyOptions');

        // Show recurring selection when client is selected (client selection is now in Step 1)
        if (clientSelect && recurringSelectionDiv) {
            clientSelect.addEventListener('change', function() {
                // Only show recurring selection if we're in Step 2 or will be going to Step 2
                const currentStepContent = document.querySelector('.wizard-content[data-step="2"]');
                if (this.value && this.value !== 'new') {
                    // Show recurring selection if we're currently viewing Step 2
                    if (currentStepContent && currentStepContent.style.display !== 'none') {
                        recurringSelectionDiv.style.display = 'block';
                    }
                } else {
                    recurringSelectionDiv.style.display = 'none';
                    recurringConfigSection.style.display = 'none';
                    // Remove required attribute when recurring section is hidden
                    if (recurrenceTypeSelect) {
                        recurrenceTypeSelect.removeAttribute('required');
                        recurrenceTypeSelect.classList.remove('is-invalid');
                    }
                }
            });
        }

        // Toggle recurring configuration visibility and required attributes
        if (projectTypeOneOff && projectTypeRecurring && recurringConfigSection) {
            projectTypeOneOff.addEventListener('change', function() {
                if (this.checked) {
                    recurringConfigSection.style.display = 'none';
                    // Remove required attribute from recurrence fields when one-off is selected
                    if (recurrenceTypeSelect) {
                        recurrenceTypeSelect.removeAttribute('required');
                        recurrenceTypeSelect.classList.remove('is-invalid');
                    }
                }
            });

            projectTypeRecurring.addEventListener('change', function() {
                if (this.checked) {
                    recurringConfigSection.style.display = 'block';
                    // Add required attribute to recurrence fields when recurring is selected
                    if (recurrenceTypeSelect) {
                        recurrenceTypeSelect.setAttribute('required', 'required');
                    }
                } else {
                    recurringConfigSection.style.display = 'none';
                    // Remove required attribute when recurring is deselected
                    if (recurrenceTypeSelect) {
                        recurrenceTypeSelect.removeAttribute('required');
                        recurrenceTypeSelect.classList.remove('is-invalid');
                    }
                }
            });
        }

        // Show/hide recurrence type options
        if (recurrenceTypeSelect) {
            recurrenceTypeSelect.addEventListener('change', function() {
                const type = this.value;

                // Hide all options
                if (weeklyOptions) weeklyOptions.style.display = 'none';
                if (monthlyOptions) monthlyOptions.style.display = 'none';
                if (annuallyOptions) annuallyOptions.style.display = 'none';

                // Show relevant options
                if (type === 'weekly' && weeklyOptions) {
                    weeklyOptions.style.display = 'block';
                } else if ((type === 'monthly' || type === 'quarterly') && monthlyOptions) {
                    monthlyOptions.style.display = 'block';
                } else if (type === 'annually' && annuallyOptions) {
                    annuallyOptions.style.display = 'block';
                }
            });
        }

        // Set default recurrence start date to project start date
        const projectStartInput = document.getElementById('wizardProjectStart');
        const recurrenceStartDateInput = document.getElementById('recurrenceStartDate');

        if (projectStartInput && recurrenceStartDateInput) {
            // Listen for changes on project start date
            projectStartInput.addEventListener('change', function() {
                const recurrenceStartPicker = recurrenceStartDateInput._flatpickr;
                const recurrenceEndPicker = document.getElementById('recurrenceEndDate')?._flatpickr;

                // Set recurrence start date if not already set
                if (recurrenceStartPicker && !recurrenceStartDateInput.value && this.value) {
                    recurrenceStartPicker.setDate(this.value);

                    // Update minDate for recurrence end date
                    if (recurrenceEndPicker) {
                        recurrenceEndPicker.set('minDate', this.value);
                    }
                }
            });

            // Also sync when project start date picker changes (for flatpickr)
            const projectStartPicker = projectStartInput._flatpickr;
            if (projectStartPicker) {
                projectStartPicker.config.onChange.push(function(selectedDates, dateStr, instance) {
                    const recurrenceStartPicker = recurrenceStartDateInput._flatpickr;
                    const recurrenceEndPicker = document.getElementById('recurrenceEndDate')?._flatpickr;

                    // Set recurrence start date if not already set
                    if (recurrenceStartPicker && !recurrenceStartDateInput.value && dateStr) {
                        recurrenceStartPicker.setDate(dateStr);

                        // Update minDate for recurrence end date
                        if (recurrenceEndPicker) {
                            recurrenceEndPicker.set('minDate', dateStr);
                        }
                    }
                });
            }
        }

        // ================================================================
        // AUTO-CALCULATE PROJECT DATES FROM RECURRENCE SETTINGS
        // ================================================================
        setupRecurrenceDateCalculation();
    }

    /**
     * Setup auto-calculation of project dates based on recurrence settings
     * - When recurrenceEndDate is set, update projectClose
     * - When recurrenceCount is set, calculate projectClose based on recurrence type and interval
     * - When recurrenceStartDate is set, update projectStart
     */
    function setupRecurrenceDateCalculation() {
        const recurrenceStartDateInput = document.getElementById('recurrenceStartDate');
        const recurrenceEndDateInput = document.getElementById('recurrenceEndDate');
        const recurrenceCountInput = document.getElementById('recurrenceCount');
        const recurrenceTypeInput = document.getElementById('recurrenceType');
        const recurrenceIntervalInput = document.getElementById('recurrenceInterval');
        const projectStartInput = document.getElementById('wizardProjectStart');
        const projectCloseInput = document.getElementById('wizardProjectClose');
        const projectDeadlineInput = document.getElementById('wizardProjectDeadline');

        if (!recurrenceStartDateInput || !projectStartInput || !projectCloseInput) {
            return; // Required elements not found
        }

        /**
         * Calculate project end date based on recurrence settings
         */
        function calculateProjectEndDate() {
            const recurrenceType = recurrenceTypeInput?.value || '';
            const recurrenceInterval = parseInt(recurrenceIntervalInput?.value || '1', 10);
            const recurrenceCount = parseInt(recurrenceCountInput?.value || '', 10);
            const recurrenceEndDate = recurrenceEndDateInput?.value || '';
            const recurrenceStartDate = recurrenceStartDateInput?.value || projectStartInput?.value || '';

            // If recurrence end date is set, use it
            if (recurrenceEndDate) {
                const projectClosePicker = projectCloseInput._flatpickr;
                if (projectClosePicker) {
                    projectClosePicker.setDate(recurrenceEndDate, false);
                    // Also update deadline if not set
                    if (projectDeadlineInput && !projectDeadlineInput.value) {
                        const deadlinePicker = projectDeadlineInput._flatpickr;
                        if (deadlinePicker) {
                            deadlinePicker.setDate(recurrenceEndDate, false);
                        }
                    }
                }
                return;
            }

            // If recurrence count is set, calculate end date
            if (recurrenceCount > 0 && recurrenceType && recurrenceStartDate) {
                const startDate = new Date(recurrenceStartDate);
                if (isNaN(startDate.getTime())) {
                    return; // Invalid start date
                }

                let endDate = new Date(startDate);
                const totalCycles = recurrenceCount;

                // Calculate end date based on recurrence type
                switch (recurrenceType) {
                    case 'weekly':
                        // Add weeks: count * interval
                        endDate.setDate(endDate.getDate() + (totalCycles * recurrenceInterval * 7) - 1);
                        break;

                    case 'monthly':
                        // Add months: count * interval
                        endDate.setMonth(endDate.getMonth() + (totalCycles * recurrenceInterval));
                        // Subtract 1 day to get the last day of the last cycle
                        endDate.setDate(endDate.getDate() - 1);
                        break;

                    case 'quarterly':
                        // Add quarters (3 months each): count * interval * 3
                        endDate.setMonth(endDate.getMonth() + (totalCycles * recurrenceInterval * 3));
                        // Subtract 1 day to get the last day of the last cycle
                        endDate.setDate(endDate.getDate() - 1);
                        break;

                    case 'annually':
                        // Add years: count * interval
                        endDate.setFullYear(endDate.getFullYear() + (totalCycles * recurrenceInterval));
                        // Subtract 1 day to get the last day of the last cycle
                        endDate.setDate(endDate.getDate() - 1);
                        break;

                    default:
                        return; // Unknown recurrence type
                }

                // Format date as YYYY-MM-DD
                const year = endDate.getFullYear();
                const month = String(endDate.getMonth() + 1).padStart(2, '0');
                const day = String(endDate.getDate()).padStart(2, '0');
                const formattedDate = `${year}-${month}-${day}`;

                // Update project close date
                const projectClosePicker = projectCloseInput._flatpickr;
                if (projectClosePicker) {
                    projectClosePicker.setDate(formattedDate, false);
                    // Also update deadline if not set
                    if (projectDeadlineInput && !projectDeadlineInput.value) {
                        const deadlinePicker = projectDeadlineInput._flatpickr;
                        if (deadlinePicker) {
                            deadlinePicker.setDate(formattedDate, false);
                        }
                    }
                }
            }
        }

        /**
         * Update project start date from recurrence start date
         */
        function updateProjectStartFromRecurrence() {
            const recurrenceStartDate = recurrenceStartDateInput?.value || '';
            if (recurrenceStartDate && projectStartInput) {
                const projectStartPicker = projectStartInput._flatpickr;
                if (projectStartPicker && (!projectStartInput.value || projectStartInput.value !== recurrenceStartDate)) {
                    projectStartPicker.setDate(recurrenceStartDate, false);
                }
            }
        }

        // Listen for changes on recurrence end date
        if (recurrenceEndDateInput) {
            const recurrenceEndPicker = recurrenceEndDateInput._flatpickr;
            if (recurrenceEndPicker) {
                recurrenceEndPicker.config.onChange.push(function(selectedDates, dateStr, instance) {
                    if (dateStr) {
                        calculateProjectEndDate();
                    }
                });
            }
            // Also listen for direct input changes
            recurrenceEndDateInput.addEventListener('change', function() {
                if (this.value) {
                    calculateProjectEndDate();
                }
            });
        }

        // Listen for changes on recurrence count
        if (recurrenceCountInput) {
            recurrenceCountInput.addEventListener('input', function() {
                if (this.value && parseInt(this.value, 10) > 0) {
                    calculateProjectEndDate();
                }
            });
            recurrenceCountInput.addEventListener('change', function() {
                if (this.value && parseInt(this.value, 10) > 0) {
                    calculateProjectEndDate();
                }
            });
        }

        // Listen for changes on recurrence type
        if (recurrenceTypeInput) {
            recurrenceTypeInput.addEventListener('change', function() {
                // Recalculate if count is set
                if (recurrenceCountInput?.value && parseInt(recurrenceCountInput.value, 10) > 0) {
                    calculateProjectEndDate();
                }
            });
        }

        // Listen for changes on recurrence interval
        if (recurrenceIntervalInput) {
            recurrenceIntervalInput.addEventListener('input', function() {
                // Recalculate if count is set
                if (recurrenceCountInput?.value && parseInt(recurrenceCountInput.value, 10) > 0) {
                    calculateProjectEndDate();
                }
            });
            recurrenceIntervalInput.addEventListener('change', function() {
                // Recalculate if count is set
                if (recurrenceCountInput?.value && parseInt(recurrenceCountInput.value, 10) > 0) {
                    calculateProjectEndDate();
                }
            });
        }

        // Listen for changes on recurrence start date
        if (recurrenceStartDateInput) {
            const recurrenceStartPicker = recurrenceStartDateInput._flatpickr;
            if (recurrenceStartPicker) {
                recurrenceStartPicker.config.onChange.push(function(selectedDates, dateStr, instance) {
                    if (dateStr) {
                        updateProjectStartFromRecurrence();
                        // Recalculate end date if count is set
                        if (recurrenceCountInput?.value && parseInt(recurrenceCountInput.value, 10) > 0) {
                            calculateProjectEndDate();
                        }
                    }
                });
            }
            // Also listen for direct input changes
            recurrenceStartDateInput.addEventListener('change', function() {
                if (this.value) {
                    updateProjectStartFromRecurrence();
                    // Recalculate end date if count is set
                    if (recurrenceCountInput?.value && parseInt(recurrenceCountInput.value, 10) > 0) {
                        calculateProjectEndDate();
                    }
                }
            });
        }
    }

    function initializeWizard() {
        // Initialize form elements
        setupProjectTypeCards();
        setupClientSelection();
        setupTeamSelects();
        setupBillingControls();
        setupNavigationButtons();
        setupProjectPlanToggle();
        setupFormValidation();
        setupDatePickers();
        // Note: Templates loaded when modal opens (see modal shown.bs.modal event)

        // Load from draft if exists
        loadDraft();
    }

    // ================================================================
    // LOAD TEMPLATES FROM DATABASE
    // ================================================================
    function loadCustomTemplates() {
        // Check if template containers exist (card-based UI)
        const systemContainer = document.getElementById('systemTemplatesContainer');
        const orgContainer = document.getElementById('orgTemplatesContainer');
        const myContainer = document.getElementById('myTemplatesContainer');

        if(!systemContainer && !orgContainer && !myContainer) {
            console.warn('Template containers not found - wizard may not be open yet');
            return;
        }

        const orgDataID = document.querySelector('input[name="orgDataID"]')?.value;
        const entityID = document.querySelector('input[name="entityID"]')?.value;

        console.log('Loading templates for orgDataID:', orgDataID, 'entityID:', entityID);

        if(!orgDataID) {
            console.warn('No organization context available for loading templates');
            const loadingText = document.getElementById('templateLoadingText');
            const readyText = document.getElementById('templateReadyText');
            if(loadingText) {
                loadingText.innerHTML = '<span class="text-warning"><i class="ri-alert-line me-1"></i>No organization context</span>';
                loadingText.style.display = 'inline';
            }
            if(readyText) readyText.style.display = 'none';

            // Show error in containers
            [systemContainer, orgContainer, myContainer].forEach(container => {
                if(container) {
                    container.innerHTML = `
                        <div class="col-12 text-center py-4">
                            <i class="ri-alert-line text-warning" style="font-size: 36px;"></i>
                            <p class="text-muted mt-2">No organization context available</p>
                        </div>
                    `;
                }
            });
            return;
        }

        // Show loading state
        const loadingText = document.getElementById('templateLoadingText');
        const readyText = document.getElementById('templateReadyText');
        if(loadingText) {
            loadingText.innerHTML = '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i>Loading templates...';
            loadingText.style.display = 'inline';
        }
        if(readyText) readyText.style.display = 'none';
        console.log('siteUrl:', siteUrl);
        if (typeof siteUrl === 'string' && siteUrl.slice(-1) !== '/') {
            siteUrl = siteUrl + '/';
        }
        // Fetch templates from database
        const url = `${siteUrl}php/scripts/projects/get_project_plan_templates.php?orgDataID=${orgDataID}&entityID=${entityID}`;
        console.log('Fetching templates from:', url);

        fetch(url)
            .then(response => {
                console.log('Template fetch response status:', response.status);
                if(!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Templates received:', data);

                if (data.success && data.templates) {
                    console.log(`Found ${data.templates.length} templates`);
                    populateTemplateDropdown(data.templates);

                    // Store templates globally for later use
                    window.projectPlanTemplatesData = data.templates;

                    if(loadingText) loadingText.style.display = 'none';
                    if(readyText) {
                        readyText.textContent = `${data.templates.length} template${data.templates.length !== 1 ? 's' : ''} available`;
                        readyText.style.display = 'inline';
                    }
                } else {
                    console.error('Failed to load templates:', data.error);
                    if(loadingText) {
                        loadingText.innerHTML = '<span class="text-warning"><i class="ri-alert-line me-1"></i>No templates available</span>';
                        loadingText.style.display = 'inline';
                    }
                    if(readyText) readyText.style.display = 'none';

                    // Show empty state in containers
                    [systemContainer, orgContainer, myContainer].forEach(container => {
                        if(container) {
                            container.innerHTML = `
                                <div class="col-12 text-center py-4">
                                    <i class="ri-inbox-line" style="font-size: 48px; color: #dee2e6;"></i>
                                    <p class="text-muted mt-3">No templates available yet.</p>
                                    <p class="small text-muted">Run the database migration or create your first template!</p>
                                </div>
                            `;
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error loading templates:', error);
                if(loadingText) {
                    loadingText.innerHTML = '<span class="text-danger"><i class="ri-error-warning-line me-1"></i>Error: ' + error.message + '</span>';
                    loadingText.style.display = 'inline';
                }
                if(readyText) readyText.style.display = 'none';

                // Show error in containers
                [systemContainer, orgContainer, myContainer].forEach(container => {
                    if(container) {
                        container.innerHTML = `
                            <div class="col-12 text-center py-4">
                                <i class="ri-error-warning-line text-danger" style="font-size: 36px;"></i>
                                <p class="text-danger mt-2">Failed to load templates</p>
                                <p class="small text-muted">${error.message}</p>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.location.reload()">
                                    <i class="ri-refresh-line me-1"></i>Reload Page
                                </button>
                            </div>
                        `;
                    }
                });
            });
    }

    function populateTemplateDropdown(templates) {
        const systemContainer = document.getElementById('systemTemplatesContainer');
        const orgContainer = document.getElementById('orgTemplatesContainer');
        const myContainer = document.getElementById('myTemplatesContainer');

        if(!systemContainer || !orgContainer || !myContainer) {
            console.error('Template containers not found in DOM');
            return;
        }

        const currentUserID = '<?= $userDetails->ID ?>';
        console.log('Current user ID:', currentUserID);

        // Categorize templates
        const systemTemplates = templates.filter(t => t.isSystemTemplate === 'Y');
        const orgTemplates = templates.filter(t => t.isSystemTemplate === 'N' && t.isPublic === 'Y');
        const myTemplates = templates.filter(t => t.isSystemTemplate === 'N' && t.isPublic === 'N' && t.createdByID == currentUserID);

        console.log(`Templates - System: ${systemTemplates.length}, Org: ${orgTemplates.length}, Personal: ${myTemplates.length}`);

        // Update counts in badges
        document.getElementById('systemTemplateCount').textContent = systemTemplates.length;
        document.getElementById('orgTemplateCount').textContent = orgTemplates.length;
        document.getElementById('myTemplateCount').textContent = myTemplates.length;

        // Populate containers
        renderTemplateCards(systemContainer, systemTemplates, 'primary');
        renderTemplateCards(orgContainer, orgTemplates, 'success');
        renderTemplateCards(myContainer, myTemplates, 'secondary');

        // If no templates at all
        if(templates.length === 0) {
            console.warn('No templates found. Database migration may not have been run.');
            const loadingText = document.getElementById('templateLoadingText');
            if(loadingText) {
                loadingText.innerHTML = '<span class="text-warning"><i class="ri-alert-line me-1"></i>No templates found</span>';
            }

            // Show message in all containers
            [systemContainer, orgContainer, myContainer].forEach(container => {
                container.innerHTML = `
                    <div class="col-12 text-center py-4">
                        <i class="ri-inbox-line" style="font-size: 48px; color: #dee2e6;"></i>
                        <p class="text-muted mt-3">No templates available yet.</p>
                        <p class="small text-muted">Run the database migration or create your first template!</p>
                    </div>
                `;
            });
        }
    }

    function renderTemplateCards(container, templates, badgeColor) {
        if(templates.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center py-4 text-muted">
                    <i class="ri-file-copy-line" style="font-size: 36px; opacity: 0.3;"></i>
                    <p class="mt-2 mb-0">No templates in this category</p>
                </div>
            `;
            return;
        }

        let html = '';
        templates.forEach(template => {
            const categoryIcon = getCategoryIcon(template.templateCategory);
            const isSystem = template.isSystemTemplate === 'Y';

            html += `
                <div class="col-md-6 col-lg-4">
                    <div class="card custom-card template-selection-card h-100" data-template-id="${template.templateID}">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar avatar-sm bg-${badgeColor}-transparent">
                                        <i class="${categoryIcon}"></i>
                                    </div>
                                    <h6 class="mb-0 template-name">${escapeHtml(template.templateName)}</h6>
                                </div>
                                ${isSystem ? '<span class="badge bg-primary-transparent">System</span>' : ''}
                            </div>

                            ${template.templateDescription ?
                                `<p class="text-muted small mb-2">${escapeHtml(template.templateDescription)}</p>` :
                                '<p class="text-muted small mb-2 fst-italic">No description</p>'}

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-light text-dark">
                                    <i class="ri-list-check me-1"></i>${template.phaseCount || 0} phases
                                </span>
                                ${template.templateCategory ?
                                    `<span class="badge bg-light text-muted">${template.templateCategory}</span>` : ''}
                            </div>

                            ${template.usageCount > 0 ?
                                `<div class="small text-muted mb-2">
                                    <i class="ri-bar-chart-line me-1"></i>Used ${template.usageCount} times
                                </div>` : ''}

                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-sm btn-primary load-template-btn" data-template-id="${template.templateID}">
                                    <i class="ri-download-line me-1"></i>Use This Template
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary preview-template-btn" data-template-id="${template.templateID}">
                                    <i class="ri-eye-line me-1"></i>Preview Phases
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;

        // Attach click handlers
        container.querySelectorAll('.load-template-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const templateID = this.dataset.templateId;
                loadTemplateByID(templateID);
            });
        });

        container.querySelectorAll('.preview-template-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const templateID = this.dataset.templateId;
                previewTemplate(templateID);
            });
        });

        // Card hover selection effect
        container.querySelectorAll('.template-selection-card').forEach(card => {
            card.addEventListener('click', function(e) {
                if(!e.target.closest('button')) {
                    // Highlight selected
                    container.querySelectorAll('.template-selection-card').forEach(c => c.classList.remove('selected'));
                    this.classList.add('selected');
                }
            });
        });
    }

    function getCategoryIcon(category) {
        const icons = {
            'software': 'ri-code-box-line',
            'construction': 'ri-hammer-line',
            'marketing': 'ri-megaphone-line',
            'research': 'ri-flask-line',
            'consulting': 'ri-briefcase-line',
            'design': 'ri-pencil-ruler-2-line',
            'other': 'ri-folder-line'
        };
        return icons[category] || 'ri-file-list-3-line';
    }

    function escapeHtml(text) {
        if(!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ================================================================
    // DATE PICKERS
    // ================================================================
    function setupDatePickers() {
        // Check if flatpickr is available
        if(typeof flatpickr === 'undefined') {
            console.warn('Flatpickr library not loaded. Date pickers will use native HTML5 date inputs.');
            return;
        }

        // Common flatpickr configuration
        const commonConfig = {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'F j, Y',
            allowInput: true,
            clickOpens: true,
            monthSelectorType: 'static',
            prevArrow: '<i class="ri-arrow-left-s-line"></i>',
            nextArrow: '<i class="ri-arrow-right-s-line"></i>',
            animate: true
        };

        // Project Start Date
        const startDateInput = document.getElementById('wizardProjectStart');
        if(startDateInput) {
            flatpickr(startDateInput, {
                ...commonConfig,
                defaultDate: new Date(),
                minDate: 'today',
                onReady: function(selectedDates, dateStr, instance) {
                    // Add calendar icon to input
                    const wrapper = instance.altInput.parentNode;
                    if(!wrapper.querySelector('.calendar-icon')) {
                        const icon = document.createElement('i');
                        icon.className = 'ri-calendar-line calendar-icon';
                        icon.style.cssText = 'position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6c757d;';
                        wrapper.style.position = 'relative';
                        wrapper.appendChild(icon);
                    }
                },
                onChange: function(selectedDates, dateStr, instance) {
                    // Clear validation error if any
                    startDateInput.classList.remove('is-invalid');

                    // Update end date minDate
                    const endDatePicker = document.getElementById('wizardProjectClose')?._flatpickr;
                    const deadlinePicker = document.getElementById('wizardProjectDeadline')?._flatpickr;
                    const recurrenceStartPicker = document.getElementById('recurrenceStartDate')?._flatpickr;
                    const recurrenceEndPicker = document.getElementById('recurrenceEndDate')?._flatpickr;

                    if(endDatePicker) {
                        endDatePicker.set('minDate', dateStr);

                        // Clear end date if it's before new start date
                        const endDateValue = document.getElementById('wizardProjectClose').value;
                        if(endDateValue && new Date(endDateValue) < new Date(dateStr)) {
                            endDatePicker.clear();
                        }
                    }

                    if(deadlinePicker) {
                        deadlinePicker.set('minDate', dateStr);

                        // Clear deadline if it's before new start date
                        const deadlineValue = document.getElementById('wizardProjectDeadline').value;
                        if(deadlineValue && new Date(deadlineValue) < new Date(dateStr)) {
                            deadlinePicker.clear();
                        }
                    }

                    // Update recurrence start date minDate and set default if not set
                    if(recurrenceStartPicker) {
                        recurrenceStartPicker.set('minDate', dateStr);

                        // Set recurrence start date to project start date if not already set
                        const recurrenceStartValue = document.getElementById('recurrenceStartDate').value;
                        if(!recurrenceStartValue) {
                            recurrenceStartPicker.setDate(dateStr);
                        }

                        // Update recurrence end date minDate
                        if(recurrenceEndPicker) {
                            recurrenceEndPicker.set('minDate', dateStr);
                        }
                    }
                }
            });
        }

        // Project End Date
        const endDateInput = document.getElementById('wizardProjectClose');
        if(endDateInput) {
            flatpickr(endDateInput, {
                ...commonConfig,
                minDate: startDateInput?.value || 'today',
                onReady: function(selectedDates, dateStr, instance) {
                    // Add calendar icon to input
                    const wrapper = instance.altInput.parentNode;
                    if(!wrapper.querySelector('.calendar-icon')) {
                        const icon = document.createElement('i');
                        icon.className = 'ri-calendar-check-line calendar-icon';
                        icon.style.cssText = 'position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6c757d;';
                        wrapper.style.position = 'relative';
                        wrapper.appendChild(icon);
                    }
                },
                onChange: function(selectedDates, dateStr, instance) {
                    // Clear validation error if any
                    endDateInput.classList.remove('is-invalid');

                    // Auto-set deadline if not set
                    const deadlineInput = document.getElementById('wizardProjectDeadline');
                    if(deadlineInput && !deadlineInput.value) {
                        const deadlinePicker = deadlineInput._flatpickr;
                        if(deadlinePicker) {
                            deadlinePicker.setDate(dateStr);
                        }
                    }

                    // Show helpful message
                    showNotification('Project end date set. Deadline auto-populated.', 'success');
                }
            });
        }

        // Project Deadline
        const deadlineInput = document.getElementById('wizardProjectDeadline');
        if(deadlineInput) {
            flatpickr(deadlineInput, {
                ...commonConfig,
                minDate: startDateInput?.value || 'today',
                defaultDate: null,
                onReady: function(selectedDates, dateStr, instance) {
                    // Add calendar icon to input
                    const wrapper = instance.altInput.parentNode;
                    if(!wrapper.querySelector('.calendar-icon')) {
                        const icon = document.createElement('i');
                        icon.className = 'ri-calendar-event-line calendar-icon';
                        icon.style.cssText = 'position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6c757d;';
                        wrapper.style.position = 'relative';
                        wrapper.appendChild(icon);
                    }
                },
                onChange: function(selectedDates, dateStr, instance) {
                    deadlineInput.classList.remove('is-invalid');
                }
            });
        }

        // Recurrence Start Date
        const recurrenceStartDateInput = document.getElementById('recurrenceStartDate');
        if(recurrenceStartDateInput) {
            flatpickr(recurrenceStartDateInput, {
                ...commonConfig,
                minDate: startDateInput?.value || 'today',
                onReady: function(selectedDates, dateStr, instance) {
                    // Add calendar icon to input
                    const wrapper = instance.altInput.parentNode;
                    if(!wrapper.querySelector('.calendar-icon')) {
                        const icon = document.createElement('i');
                        icon.className = 'ri-calendar-line calendar-icon';
                        icon.style.cssText = 'position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6c757d;';
                        wrapper.style.position = 'relative';
                        wrapper.appendChild(icon);
                    }
                },
                onChange: function(selectedDates, dateStr, instance) {
                    recurrenceStartDateInput.classList.remove('is-invalid');

                    // Update recurrence end date minDate
                    const recurrenceEndDatePicker = document.getElementById('recurrenceEndDate')?._flatpickr;
                    if(recurrenceEndDatePicker) {
                        recurrenceEndDatePicker.set('minDate', dateStr);

                        // Clear end date if it's before new start date
                        const endDateValue = document.getElementById('recurrenceEndDate').value;
                        if(endDateValue && new Date(endDateValue) < new Date(dateStr)) {
                            recurrenceEndDatePicker.clear();
                        }
                    }
                }
            });
        }

        // Recurrence End Date
        const recurrenceEndDateInput = document.getElementById('recurrenceEndDate');
        if(recurrenceEndDateInput) {
            flatpickr(recurrenceEndDateInput, {
                ...commonConfig,
                minDate: recurrenceStartDateInput?.value || startDateInput?.value || 'today',
                onReady: function(selectedDates, dateStr, instance) {
                    // Add calendar icon to input
                    const wrapper = instance.altInput.parentNode;
                    if(!wrapper.querySelector('.calendar-icon')) {
                        const icon = document.createElement('i');
                        icon.className = 'ri-calendar-check-line calendar-icon';
                        icon.style.cssText = 'position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6c757d;';
                        wrapper.style.position = 'relative';
                        wrapper.appendChild(icon);
                    }
                },
                onChange: function(selectedDates, dateStr, instance) {
                    recurrenceEndDateInput.classList.remove('is-invalid');
                }
            });
        }
    }

    // ================================================================
    // PROJECT TYPE CARDS
    // ================================================================
    function setupProjectTypeCards() {
        document.querySelectorAll('.project-type-card').forEach(card => {
            card.addEventListener('click', function() {
                const type = this.getAttribute('data-type');
                const radio = this.querySelector('.project-type-radio');

                // Update visual selection
                document.querySelectorAll('.project-type-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');

                // Check radio
                radio.checked = true;

                // Show/hide client selection
                if(type === 'client') {
                    document.getElementById('clientSelectionDiv').style.display = 'block';
                } else {
                    document.getElementById('clientSelectionDiv').style.display = 'none';
                    document.getElementById('recurringSelectionDiv').style.display = 'none';
                    document.getElementById('recurringConfigSection').style.display = 'none';
                    // Set internal client
                    document.getElementById('wizardClientID').value = '1';
                    // Set one-off project type
                    const oneOffRadio = document.getElementById('projectTypeOneOff');
                    if (oneOffRadio) oneOffRadio.checked = true;
                    // Remove required attribute from recurrence fields for internal projects
                    const recurrenceTypeSelect = document.getElementById('recurrenceType');
                    if (recurrenceTypeSelect) {
                        recurrenceTypeSelect.removeAttribute('required');
                        recurrenceTypeSelect.classList.remove('is-invalid');
                    }
                }
            });
        });
    }

    // ================================================================
    // CLIENT SELECTION
    // ================================================================
    function setupClientSelection() {
        const clientSelect = document.getElementById('wizardClientID');
        if(!clientSelect) return;

        // Initialize TomSelect
        new TomSelect(clientSelect, {
            create: false,
            sortField: 'text',
            placeholder: 'Select a client'
        });

        clientSelect.addEventListener('change', function() {
            if(this.value === 'new') {
                document.getElementById('newClientForm').style.display = 'block';
            } else {
                document.getElementById('newClientForm').style.display = 'none';
            }
        });

        // Setup sector/industry filtering
        const sectorSelect = document.getElementById('newClientSector');
        if(sectorSelect) {
            sectorSelect.addEventListener('change', function() {
                filterIndustriesBySector(this.value);
            });
        }
    }

    function filterIndustriesBySector(sectorID) {
        const industrySelect = document.getElementById('newClientIndustry');
        if(!industrySelect) return;

        const allOptions = industrySelect.querySelectorAll('option');
        allOptions.forEach(option => {
            if(option.value === '') {
                option.style.display = 'block';
                return;
            }

            const optionSector = option.getAttribute('data-sector');
            if(!sectorID || optionSector === sectorID) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });

        industrySelect.value = '';
    }

    // ================================================================
    // TEAM SELECTION
    // ================================================================
    function setupTeamSelects() {
        // Project Owner
        const ownerSelect = document.getElementById('wizardProjectOwner');
        if(ownerSelect) {
            new TomSelect(ownerSelect, {
                create: false,
                sortField: 'text',
                placeholder: 'Select project owner'
            });
        }

        // Project Managers
        const managersSelect = document.getElementById('wizardProjectManagers');
        if(managersSelect) {
            new TomSelect(managersSelect, {
                plugins: ['remove_button'],
                create: false,
                sortField: 'text',
                placeholder: 'Select project managers (multiple)',
                maxItems: null
            });
        }

        // Team Members
        const teamSelect = document.getElementById('wizardTeamMembers');
        if(teamSelect) {
            new TomSelect(teamSelect, {
                plugins: ['remove_button'],
                create: false,
                sortField: 'text',
                placeholder: 'Select team members (multiple)',
                maxItems: null
            });
        }
    }

    // ================================================================
    // BILLING CONTROLS
    // ================================================================
    function setupBillingControls() {
        const roundingSelect = document.getElementById('wizardRounding');
        if(roundingSelect) {
            roundingSelect.addEventListener('change', function() {
                const intervalDiv = document.querySelector('.roundingIntervalDiv');
                if(this.value && this.value !== 'no_rounding') {
                    intervalDiv.style.display = 'block';
                } else {
                    intervalDiv.style.display = 'none';
                }
            });
        }
    }

    // ================================================================
    // PROJECT PLAN TOGGLE
    // ================================================================
    function setupProjectPlanToggle() {
        const skipCheckbox = document.getElementById('skipProjectPlan');
        if(skipCheckbox) {
            skipCheckbox.addEventListener('change', function() {
                const planSection = document.getElementById('projectPlanSection');
                if(this.checked) {
                    planSection.style.display = 'none';
                } else {
                    planSection.style.display = 'block';
                }
            });
        }

        const addPhaseBtn = document.getElementById('addPhaseBtn');
        if(addPhaseBtn) {
            addPhaseBtn.addEventListener('click', addProjectPhase);
        }

        // Template loading
        const loadTemplateBtn = document.getElementById('loadTemplateBtn');
        if(loadTemplateBtn) {
            loadTemplateBtn.addEventListener('click', loadProjectTemplate);
        }

        // Refresh templates
        const refreshTemplatesBtn = document.getElementById('refreshTemplatesBtn');
        if(refreshTemplatesBtn) {
            refreshTemplatesBtn.addEventListener('click', function() {
                console.log('Manual template refresh triggered');
                loadCustomTemplates();
            });
        }

        // Clear phases
        const clearPhasesBtn = document.getElementById('clearPhasesBtn');
        if(clearPhasesBtn) {
            clearPhasesBtn.addEventListener('click', clearAllPhases);
        }

        // Save as template
        const saveAsTemplateBtn = document.getElementById('saveAsTemplateBtn');
        if(saveAsTemplateBtn) {
            saveAsTemplateBtn.addEventListener('click', saveAsTemplate);
        }

        // Template selection warning
        const templateSelect = document.getElementById('projectPlanTemplate');
        if(templateSelect) {
            templateSelect.addEventListener('change', function() {
                const warning = document.getElementById('templateWarning');
                const container = document.getElementById('projectPhasesContainer');
                if(this.value && container.querySelectorAll('.phase-item').length > 0) {
                    warning.style.display = 'block';
                } else {
                    warning.style.display = 'none';
                }
            });
        }
    }

    function loadTemplateByID(templateID) {
        if(!templateID) {
            alert('Please select a template first');
            return;
        }

        // Confirm if there are existing phases
        const container = document.getElementById('projectPhasesContainer');
        if(container.querySelectorAll('.phase-item').length > 0) {
            if(!confirm('This will replace all existing phases. Continue?')) {
                return;
            }
        }

        // Show loading on the button that was clicked
        const clickedBtn = event.target.closest('button');
        const originalText = clickedBtn ? clickedBtn.innerHTML : '';
        if(clickedBtn) {
            clickedBtn.innerHTML = '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i>Loading...';
            clickedBtn.disabled = true;
        }

        // Fetch template details with phases
        fetch(`${siteUrl}php/scripts/projects/get_project_plan_templates.php?templateID=${templateID}`)
            .then(response => response.json())
            .then(data => {
                if(data.success && data.template) {
                    const template = data.template;

                    // Clear existing phases
                    container.innerHTML = '';

                    // Load template phases
                    if(template.phases && template.phases.length > 0) {
                        // Uncheck skipProjectPlan checkbox and show project plan section
                        const skipProjectPlanCheckbox = document.getElementById('skipProjectPlan');
                        const projectPlanSection = document.getElementById('projectPlanSection');
                        if(skipProjectPlanCheckbox) {
                            skipProjectPlanCheckbox.checked = false;
                        }
                        if(projectPlanSection) {
                            projectPlanSection.style.display = 'block';
                        }

                        template.phases.forEach(phase => {
                            addProjectPhase(phase.phaseName, phase.phaseDescription || '');
                        });

                        // Update usage count
                        updateTemplateUsageCount(templateID);

                        // Scroll to phases section
                        setTimeout(() => {
                            document.getElementById('projectPhasesContainer').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }, 300);

                        showAlert('success', `Template "${template.templateName}" loaded with ${template.phases.length} phases!`);
                    } else {
                        showAlert('warning', 'Template loaded but has no phases defined');
                    }
                } else {
                    showAlert('danger', 'Failed to load template: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error loading template:', error);
                showAlert('danger', 'Failed to load template. Please try again.');
            })
            .finally(() => {
                if(clickedBtn) {
                    clickedBtn.innerHTML = originalText;
                    clickedBtn.disabled = false;
                }
            });
    }

    function previewTemplate(templateID) {
        // Fetch template details
        fetch(`${siteUrl}php/scripts/projects/get_project_plan_templates.php?templateID=${templateID}`)
            .then(response => response.json())
            .then(data => {
                if(data.success && data.template) {
                    showTemplatePreview(data.template);
                } else {
                    showAlert('danger', 'Failed to load template preview');
                }
            })
            .catch(error => {
                console.error('Error previewing template:', error);
                showAlert('danger', 'Failed to load template preview');
            });
    }

    function showTemplatePreview(template) {
        const phasesHTML = template.phases && template.phases.length > 0 ?
            template.phases.map((phase, index) => `
                <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom">
                    <div>
                        <strong class="text-primary">${index + 1}. ${escapeHtml(phase.phaseName)}</strong>
                        ${phase.phaseDescription ? `<p class="mb-0 text-muted small">${escapeHtml(phase.phaseDescription)}</p>` : ''}
                    </div>
                    ${phase.durationPercent ? `<span class="badge bg-info">${phase.durationPercent}%</span>` : ''}
                </div>
            `).join('') : '<p class="text-muted">No phases defined</p>';

        const modalContent = `
            <div class="modal fade" id="templatePreviewModal" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="ri-eye-line me-2"></i>${escapeHtml(template.templateName)}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ${template.templateDescription ?
                                `<div class="alert alert-info">
                                    <i class="ri-information-line me-2"></i>${escapeHtml(template.templateDescription)}
                                </div>` : ''}

                            <h6 class="mb-3 border-bottom pb-2">
                                <i class="ri-list-check me-2"></i>Template Phases
                                <span class="badge bg-primary ms-2">${template.phases ? template.phases.length : 0}</span>
                            </h6>
                            <div class="template-phases-preview">
                                ${phasesHTML}
                            </div>

                            ${template.templateCategory ?
                                `<div class="mt-3">
                                    <small class="text-muted">
                                        <i class="ri-folder-line me-1"></i>Category: <strong>${template.templateCategory}</strong>
                                    </small>
                                </div>` : ''}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" onclick="loadTemplateByID(${template.templateID}); bootstrap.Modal.getInstance(document.getElementById('templatePreviewModal')).hide();">
                                <i class="ri-download-line me-1"></i>Use This Template
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing preview modal if any
        document.getElementById('templatePreviewModal')?.remove();

        // Add and show modal
        document.body.insertAdjacentHTML('beforeend', modalContent);
        const modal = new bootstrap.Modal(document.getElementById('templatePreviewModal'));
        modal.show();

        // Clean up on close
        document.getElementById('templatePreviewModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }

    function loadProjectTemplate() {
        // This function is now deprecated - keeping for backward compatibility
        console.warn('loadProjectTemplate() is deprecated. Use loadTemplateByID() instead.');
    }

    function updateTemplateUsageCount(templateID) {
        // Silently update usage count in background
        const formData = new FormData();
        formData.append('action', 'incrementUsage');
        formData.append('templateID', templateID);

        fetch(`${siteUrl}php/scripts/projects/update_template_usage.php`, {
            method: 'POST',
            body: formData
        }).catch(err => console.log('Usage count update failed (non-critical):', err));
    }

    function clearAllPhases() {
        const container = document.getElementById('projectPhasesContainer');
        if(container.querySelectorAll('.phase-item').length === 0) {
            return;
        }

        if(confirm('Are you sure you want to clear all phases?')) {
            container.innerHTML = '';
            showAlert('info', 'All phases cleared');
        }
    }

    function saveAsTemplate() {
        const container = document.getElementById('projectPhasesContainer');
        const phases = container.querySelectorAll('.phase-item');

        if(phases.length === 0) {
            alert('Please add at least one phase before saving as template');
            return;
        }

        const templateName = prompt('Enter a name for this template:');
        if(!templateName) return;

        const templateDescription = prompt('Enter a description (optional):') || '';
        const makePublic = confirm('Make this template available to your entire organization?');

        // Gather phase data
        const phaseNames = [];
        const phaseDescriptions = [];
        const phasePercents = [];

        phases.forEach(phase => {
            const name = phase.querySelector('input[name="phaseName[]"]').value;
            const description = phase.querySelector('textarea[name="phaseDescription[]"]').value;
            if(name) {
                phaseNames.push(name);
                phaseDescriptions.push(description);
                phasePercents.push((100 / phases.length).toFixed(2)); // Auto-calculate equal percentages
            }
        });

        // Save to database via AJAX
        const formData = new FormData();
        formData.append('action', 'create');
        formData.append('templateName', templateName);
        formData.append('templateDescription', templateDescription);
        formData.append('isPublic', makePublic ? 'Y' : 'N');
        formData.append('orgDataID', document.querySelector('input[name="orgDataID"]').value);
        formData.append('entityID', document.querySelector('input[name="entityID"]').value);

        phaseNames.forEach((name, index) => {
            formData.append('phaseName[]', name);
            formData.append('phaseDescription[]', phaseDescriptions[index]);
            formData.append('phasePercent[]', phasePercents[index]);
        });

        fetch(`${siteUrl}php/scripts/projects/manage_project_plan_template.php`, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Reload templates in dropdown
            loadCustomTemplates();
            showAlert('success', `Template "${templateName}" saved successfully!`);
        })
        .catch(error => {
            console.error('Error saving template:', error);
            showAlert('danger', 'Failed to save template. Please try again.');
        });
    }

    function addProjectPhase(phaseName = '', phaseDescription = '') {
        const container = document.getElementById('projectPhasesContainer');
        const phaseCount = container.querySelectorAll('.phase-item').length + 1;

        const phaseHTML = `
            <div class="card custom-card mb-2 phase-item">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0">Phase ${phaseCount}</h6>
                        <button type="button" class="btn btn-sm btn-danger-light remove-phase">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                    <input type="text" name="phaseName[]" class="form-control form-control-sm mb-2" placeholder="Phase name" value="${phaseName}" required>
                    <textarea name="phaseDescription[]" class="form-control form-control-sm" rows="2" placeholder="Phase description">${phaseDescription}</textarea>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', phaseHTML);

        // Add remove handler
        const removeBtn = container.lastElementChild.querySelector('.remove-phase');
        removeBtn.addEventListener('click', function() {
            this.closest('.phase-item').remove();
            renumberPhases();
            // Check if no phases remain, then check skipProjectPlan
            checkPhaseCountAndUpdateSkipCheckbox();
        });

        // Uncheck skipProjectPlan when phases are added
        const skipProjectPlanCheckbox = document.getElementById('skipProjectPlan');
        const projectPlanSection = document.getElementById('projectPlanSection');
        if(skipProjectPlanCheckbox) {
            skipProjectPlanCheckbox.checked = false;
        }
        if(projectPlanSection) {
            projectPlanSection.style.display = 'block';
        }
    }

    function checkPhaseCountAndUpdateSkipCheckbox() {
        const container = document.getElementById('projectPhasesContainer');
        const phaseCount = container.querySelectorAll('.phase-item').length;
        const skipProjectPlanCheckbox = document.getElementById('skipProjectPlan');
        const projectPlanSection = document.getElementById('projectPlanSection');

        if(phaseCount === 0) {
            // No phases, can skip project plan
            if(skipProjectPlanCheckbox) {
                skipProjectPlanCheckbox.checked = true;
            }
            if(projectPlanSection) {
                projectPlanSection.style.display = 'none';
            }
        } else {
            // Has phases, don't skip
            if(skipProjectPlanCheckbox) {
                skipProjectPlanCheckbox.checked = false;
            }
            if(projectPlanSection) {
                projectPlanSection.style.display = 'block';
            }
        }
    }

    function renumberPhases() {
        const container = document.getElementById('projectPhasesContainer');
        const phases = container.querySelectorAll('.phase-item');
        phases.forEach((phase, index) => {
            phase.querySelector('h6').textContent = `Phase ${index + 1}`;
        });
    }

    function showAlert(type, message) {
        // Simple alert system - can be enhanced with better UI
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 80px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);

        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    }

    // ================================================================
    // NAVIGATION
    // ================================================================
    function setupNavigationButtons() {
        document.getElementById('wizardPrevBtn')?.addEventListener('click', previousStep);
        document.getElementById('wizardNextBtn')?.addEventListener('click', nextStep);
        document.getElementById('wizardSubmitBtn')?.addEventListener('click', submitWizard);
        document.getElementById('saveDraftBtn')?.addEventListener('click', saveDraft);
    }

    function nextStep() {
        if(!validateCurrentStep()) {
            return;
        }

        // Mark current step as completed
        const currentStepIndicator = document.querySelector(`.wizard-step[data-step="${currentStep}"]`);
        if (currentStepIndicator && currentStepIndicator.style.display !== 'none') {
            currentStepIndicator.classList.add('completed');
        }

        // Move to next step
        currentStep++;

        // Skip step 2 if internal project is selected
        const projectTypeInternal = document.getElementById('projectTypeInternal');
        if (currentStep === 2 && projectTypeInternal && projectTypeInternal.checked) {
            currentStep = 3; // Skip step 2 for internal projects
        }

        if(currentStep > totalSteps) currentStep = totalSteps;

        updateWizardDisplay();
    }

    function previousStep() {
        currentStep--;

        // Skip step 2 if internal project is selected when going backwards
        const projectTypeInternal = document.getElementById('projectTypeInternal');
        if (currentStep === 2 && projectTypeInternal && projectTypeInternal.checked) {
            currentStep = 1; // Skip step 2 for internal projects, go back to step 1
        }

        if(currentStep < 1) currentStep = 1;

        updateWizardDisplay();
    }

    function updateWizardDisplay() {
        console.log('Updating wizard display for step:', currentStep);

        // Handle step 2 visibility based on project type
        const projectTypeClient = document.getElementById('projectTypeClient');
        const projectTypeInternal = document.getElementById('projectTypeInternal');
        const step2Content = document.querySelector('.wizard-content[data-step="2"]');
        const step2Indicator = document.querySelector('.wizard-step[data-step="2"]');

        // If internal project is selected and we're navigating, skip step 2
        if (projectTypeInternal && projectTypeInternal.checked && currentStep === 2) {
            // Skip step 2 for internal projects, go directly to step 3
            currentStep = 3;
        }

        // Show/hide step 2 indicator based on project type
        if (step2Indicator) {
            if (projectTypeInternal && projectTypeInternal.checked) {
                step2Indicator.style.display = 'none';
            } else {
                step2Indicator.style.display = 'flex';
            }
        }

        // Update step indicators
        document.querySelectorAll('.wizard-step').forEach(step => {
            const stepNum = parseInt(step.getAttribute('data-step'));

            // Skip hidden steps
            if (step.style.display === 'none') return;

            if(stepNum === currentStep) {
                step.classList.add('active');
                step.classList.remove('completed');
            } else if(stepNum < currentStep) {
                step.classList.add('completed');
                step.classList.remove('active');
            } else {
                step.classList.remove('active', 'completed');
            }
        });

        // Update content visibility
        document.querySelectorAll('.wizard-content').forEach(content => {
            const stepNum = parseInt(content.getAttribute('data-step'));

            // Show/hide step 2 content based on project type
            if (stepNum === 2) {
                if (projectTypeInternal && projectTypeInternal.checked) {
                    content.style.display = 'none';
                    return;
                } else {
                    // Show recurring selection for client projects in step 2
                    // Check if client is selected to show recurring options
                    const clientSelect = document.getElementById('wizardClientID');
                    const recurringSelectionDiv = document.getElementById('recurringSelectionDiv');
                    if (clientSelect && recurringSelectionDiv && stepNum === currentStep) {
                        // Show recurring selection if client is selected
                        if (clientSelect.value && clientSelect.value !== 'new' && clientSelect.value !== '') {
                            recurringSelectionDiv.style.display = 'block';
                        } else {
                            recurringSelectionDiv.style.display = 'none';
                        }
                    }
                }
            }

            content.style.display = stepNum === currentStep ? 'block' : 'none';
        });

        // Update navigation buttons
        const prevBtn = document.getElementById('wizardPrevBtn');
        const nextBtn = document.getElementById('wizardNextBtn');
        const submitBtn = document.getElementById('wizardSubmitBtn');

        if(currentStep === 1) {
            prevBtn.style.display = 'none';
        } else {
            prevBtn.style.display = 'inline-block';
        }

        if(currentStep === totalSteps) {
            nextBtn.style.display = 'none';
            submitBtn.style.display = 'inline-block';
            // Update review summary when reaching final step
            console.log('Reached final step, updating review summary...');
            setTimeout(() => {
                updateReviewSummary();
            }, 100);
        } else {
            nextBtn.style.display = 'inline-block';
            submitBtn.style.display = 'none';
        }

        // Update progress line
        const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
        const progressLine = document.querySelector('.wizard-progress-line');
        if(progressLine) {
            progressLine.style.setProperty('--progress', progress + '%');
        }

        // Scroll to top
        document.querySelector('.modal-body')?.scrollTo({top: 0, behavior: 'smooth'});
    }

    // ================================================================
    // VALIDATION
    // ================================================================
    function validateCurrentStep() {
        // Skip validation for step 2 if internal project is selected
        const projectTypeInternal = document.getElementById('projectTypeInternal');
        if (currentStep === 2 && projectTypeInternal && projectTypeInternal.checked) {
            return true;
        }

        const currentContent = document.querySelector(`.wizard-content[data-step="${currentStep}"]`);
        if(!currentContent) return true;

        // Check if recurring project is selected - if not, skip recurrence field validation
        const isRecurring = document.getElementById('projectTypeRecurring')?.checked;
        const recurringConfigSection = document.getElementById('recurringConfigSection');
        const isRecurringSectionVisible = recurringConfigSection && recurringConfigSection.style.display !== 'none';

        const requiredFields = currentContent.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            // Skip validation for recurrence fields if recurring project is not selected or section is hidden
            if ((field.id === 'recurrenceType' || field.closest('#recurringConfigSection')) &&
                (!isRecurring || !isRecurringSectionVisible)) {
                return; // Skip this field
            }

            if(!field.value || field.value.trim() === '') {
                field.classList.add('is-invalid');
                isValid = false;

                // Add error message if not exists
                if(!field.nextElementSibling || !field.nextElementSibling.classList.contains('invalid-feedback')) {
                    const feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';
                    feedback.textContent = 'This field is required';
                    field.parentNode.appendChild(feedback);
                }
            } else {
                field.classList.remove('is-invalid');
            }
        });

        if(!isValid) {
            showNotification('Please fill in all required fields', 'warning');
        }

        return isValid;
    }

    function setupFormValidation() {
        // Remove invalid class on input
        document.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('input', function() {
                this.classList.remove('is-invalid');
            });
        });

        // Date validation
        const startDate = document.getElementById('wizardProjectStart');
        const endDate = document.getElementById('wizardProjectClose');

        if(startDate && endDate) {
            endDate.addEventListener('change', function() {
                if(startDate.value && endDate.value) {
                    if(new Date(endDate.value) < new Date(startDate.value)) {
                        endDate.classList.add('is-invalid');
                        showNotification('End date cannot be before start date', 'danger');
                        endDate.value = '';
                    } else {
                        endDate.classList.remove('is-invalid');
                    }
                }
            });
        }
    }

    // ================================================================
    // REVIEW SUMMARY
    // ================================================================
    function updateReviewSummary() {
        console.log('Updating review summary...');

        const form = document.getElementById('projectWizardForm');
        if(!form) {
            console.error('Form not found. The wizard modal may not be loaded yet.');
            return;
        }

        try {
            // Project Type
            const projectTypeRadio = form.querySelector('input[name="projectTypeID"]:checked');
            const projectTypeText = projectTypeRadio?.value === '1' ? 'Client Project' : 'Internal Project';
            console.log('Project Type:', projectTypeText);
            const reviewProjectType = document.getElementById('reviewProjectType');
            if(reviewProjectType) {
                reviewProjectType.textContent = projectTypeText;
            }

            // Client
            const clientSelect = document.getElementById('wizardClientID');
            let clientText = 'Internal';
            if(clientSelect) {
                // Check if using TomSelect
                if(clientSelect.tomselect) {
                    const selectedValue = clientSelect.tomselect.getValue();
                    const selectedOption = clientSelect.querySelector(`option[value="${selectedValue}"]`);
                    clientText = selectedOption?.text || 'Internal';
                } else {
                    clientText = clientSelect.selectedOptions[0]?.text || 'Internal';
                }
            }
            console.log('Client:', clientText);
            const reviewClient = document.getElementById('reviewClient');
            if(reviewClient) {
                reviewClient.textContent = clientText;
            }

            // Project Name
            const projectNameInput = document.getElementById('wizardProjectName');
            const projectName = projectNameInput?.value || '-';
            console.log('Project Name:', projectName);
            const reviewProjectName = document.getElementById('reviewProjectName');
            if(reviewProjectName) {
                reviewProjectName.textContent = projectName;
            }

            // Timeline
            const startInput = document.getElementById('wizardProjectStart');
            const endInput = document.getElementById('wizardProjectClose');
            const start = startInput?.value;
            const end = endInput?.value;
            const timelineText = start && end ? `${formatDate(start)} - ${formatDate(end)}` : '-';
            console.log('Timeline:', timelineText);
            const reviewTimeline = document.getElementById('reviewTimeline');
            if(reviewTimeline) {
                reviewTimeline.textContent = timelineText;
            }

            // Value
            const valueInput = document.getElementById('wizardProjectValue');
            const value = valueInput?.value;
            const valueText = value && parseFloat(value) > 0 ? `<?= $config['project']['display']['currency'] ?> ${parseFloat(value).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}` : 'Not specified';
            console.log('Value:', valueText);
            const reviewValue = document.getElementById('reviewValue');
            if(reviewValue) {
                reviewValue.textContent = valueText;
            }

            // Owner
            const ownerSelect = document.getElementById('wizardProjectOwner');
            let ownerText = '-';
            if(ownerSelect) {
                if(ownerSelect.tomselect) {
                    const selectedValue = ownerSelect.tomselect.getValue();
                    const selectedOption = ownerSelect.querySelector(`option[value="${selectedValue}"]`);
                    ownerText = selectedOption?.text || '-';
                } else {
                    ownerText = ownerSelect.selectedOptions[0]?.text || '-';
                }
            }
            console.log('Owner:', ownerText);
            const reviewOwner = document.getElementById('reviewOwner');
            if(reviewOwner) {
                reviewOwner.textContent = ownerText;
            }

            // Managers
            const managersSelect = document.getElementById('wizardProjectManagers');
            let managerNames = [];
            if(managersSelect) {
                if(managersSelect.tomselect) {
                    const selectedValues = managersSelect.tomselect.getValue();
                    if(Array.isArray(selectedValues)) {
                        selectedValues.forEach(val => {
                            const option = managersSelect.querySelector(`option[value="${val}"]`);
                            if(option) managerNames.push(option.text);
                        });
                    }
                } else {
                    managerNames = Array.from(managersSelect.selectedOptions || []).map(opt => opt.text);
                }
            }
            const managersText = managerNames.length > 0 ? managerNames.join(', ') : 'None assigned';
            console.log('Managers:', managersText);
            const reviewManagers = document.getElementById('reviewManagers');
            if(reviewManagers) {
                reviewManagers.textContent = managersText;
            }

            // Team Members
            const teamSelect = document.getElementById('wizardTeamMembers');
            let teamNames = [];
            if(teamSelect) {
                if(teamSelect.tomselect) {
                    const selectedValues = teamSelect.tomselect.getValue();
                    if(Array.isArray(selectedValues)) {
                        selectedValues.forEach(val => {
                            const option = teamSelect.querySelector(`option[value="${val}"]`);
                            if(option) teamNames.push(option.text);
                        });
                    }
                } else {
                    teamNames = Array.from(teamSelect.selectedOptions || []).map(opt => opt.text);
                }
            }
            const teamText = teamNames.length > 0 ? `${teamNames.length} member(s): ${teamNames.join(', ')}` : 'None assigned';
            console.log('Team Members:', teamText);
            const reviewTeamMembers = document.getElementById('reviewTeamMembers');
            if(reviewTeamMembers) {
                reviewTeamMembers.textContent = teamText;
            }

            // Billing Rate
            const billingSelect = document.getElementById('wizardBillingRate');
            let billingText = 'Not set';
            if(billingSelect) {
                if(billingSelect.tomselect) {
                    const selectedValue = billingSelect.tomselect.getValue();
                    const selectedOption = billingSelect.querySelector(`option[value="${selectedValue}"]`);
                    billingText = selectedOption?.text || 'Not set';
                } else {
                    billingText = billingSelect.selectedOptions[0]?.text || 'Not set';
                }
            }
            console.log('Billing Rate:', billingText);
            const reviewBillingRate = document.getElementById('reviewBillingRate');
            if(reviewBillingRate) {
                reviewBillingRate.textContent = billingText;
            }

            // Rounding
            const roundingSelect = document.getElementById('wizardRounding');
            let roundingText = 'No rounding';
            if(roundingSelect) {
                if(roundingSelect.tomselect) {
                    const selectedValue = roundingSelect.tomselect.getValue();
                    const selectedOption = roundingSelect.querySelector(`option[value="${selectedValue}"]`);
                    roundingText = selectedOption?.text || 'No rounding';
                } else {
                    roundingText = roundingSelect.selectedOptions[0]?.text || 'No rounding';
                }

                // Add interval if applicable
                const intervalSelect = document.getElementById('wizardRoundingInterval');
                if(intervalSelect && roundingSelect.value && roundingSelect.value !== 'no_rounding') {
                    const intervalText = intervalSelect.tomselect ?
                        intervalSelect.querySelector(`option[value="${intervalSelect.tomselect.getValue()}"]`)?.text :
                        intervalSelect.selectedOptions[0]?.text;
                    if(intervalText) {
                        roundingText += ` (${intervalText})`;
                    }
                }
            }
            console.log('Rounding:', roundingText);
            const reviewRounding = document.getElementById('reviewRounding');
            if(reviewRounding) {
                reviewRounding.textContent = roundingText;
            }

            // Project Plan / Phases
            const skipProjectPlanCheckbox = document.getElementById('skipProjectPlan');
            const skipProjectPlan = skipProjectPlanCheckbox ? skipProjectPlanCheckbox.checked : true;
            const phasesContainer = document.getElementById('projectPhasesContainer');
            const reviewPhases = document.getElementById('reviewPhases');

            if(reviewPhases) {
                if(skipProjectPlan) {
                    reviewPhases.innerHTML = '<span class="text-muted"><i class="ri-information-line me-1"></i>Project plan will be skipped</span>';
                } else if(phasesContainer) {
                    const phaseItems = phasesContainer.querySelectorAll('.phase-item');
                    if(phaseItems.length > 0) {
                        let phasesHTML = '<div class="list-group list-group-flush">';
                        phaseItems.forEach((phase, index) => {
                            const phaseNameInput = phase.querySelector('input[name="phaseName[]"]');
                            const phaseDescTextarea = phase.querySelector('textarea[name="phaseDescription[]"]');
                            const phaseName = phaseNameInput ? phaseNameInput.value.trim() : '';
                            const phaseDesc = phaseDescTextarea ? phaseDescTextarea.value.trim() : '';

                            if(phaseName) {
                                phasesHTML += `
                                    <div class="list-group-item px-0 py-2 border-0 border-bottom">
                                        <div class="d-flex align-items-start">
                                            <span class="badge bg-primary me-2 mt-1">${index + 1}</span>
                                            <div class="flex-fill">
                                                <strong class="d-block">${escapeHtml(phaseName)}</strong>
                                                ${phaseDesc ? `<small class="text-muted d-block mt-1">${escapeHtml(phaseDesc)}</small>` : ''}
                                            </div>
                                        </div>
                                    </div>
                                `;
                            }
                        });
                        phasesHTML += '</div>';
                        reviewPhases.innerHTML = phasesHTML;
                    } else {
                        reviewPhases.innerHTML = '<span class="text-muted"><i class="ri-information-line me-1"></i>No phases added yet</span>';
                    }
                } else {
                    reviewPhases.innerHTML = '<span class="text-muted"><i class="ri-information-line me-1"></i>No phases added yet</span>';
                }
            }
            console.log('Phases:', skipProjectPlan ? 'Skipped' : (phasesContainer ? phasesContainer.querySelectorAll('.phase-item').length + ' phases' : 'None'));

            console.log('Review summary updated successfully');

        } catch(error) {
            console.error('Error updating review summary:', error);
        }
    }

    // ================================================================
    // FORM SUBMISSION
    // ================================================================
    function submitWizard(e) {
        if(e) e.preventDefault();

        const form = document.getElementById('projectWizardForm');
        if(!form) {
            console.error('Form not found for submission!');
            return;
        }

        // Debug: Log form data before submission
        console.log('=== SUBMITTING PROJECT WIZARD ===');
        const formData = new FormData(form);
        console.log('Form action:', form.action);
        console.log('Form method:', form.method);

        console.log('Critical fields:');
        console.log('- orgDataID:', formData.get('orgDataID'));
        console.log('- entityID:', formData.get('entityID'));
        console.log('- projectTypeID:', formData.get('projectTypeID'));
        console.log('- projectName:', formData.get('projectName'));
        console.log('- clientID:', formData.get('clientID'));
        console.log('- skipProjectPlan:', formData.get('skipProjectPlan'));

        // Log phase data - FormData handles array inputs differently
        // When using name="phaseName[]", FormData.getAll('phaseName[]') should work
        // But we also need to check if they're being collected correctly
        const phaseNames = formData.getAll('phaseName[]');
        const phaseDescriptions = formData.getAll('phaseDescription[]');

        // Also check all form inputs directly
        const phaseInputs = form.querySelectorAll('input[name="phaseName[]"]');
        const phaseDescInputs = form.querySelectorAll('textarea[name="phaseDescription[]"]');
        console.log('- Phase Names (FormData):', phaseNames);
        console.log('- Phase Descriptions (FormData):', phaseDescriptions);
        console.log('- Number of phases (FormData):', phaseNames.length);
        console.log('- Phase Inputs found in DOM:', phaseInputs.length);
        console.log('- Phase Description Inputs found in DOM:', phaseDescInputs.length);

        // Log actual values from DOM
        const phaseValuesFromDOM = [];
        phaseInputs.forEach((input, index) => {
            const value = input.value.trim();
            if(value) {
                phaseValuesFromDOM.push(value);
                console.log(`  Phase ${index + 1}: ${value}`);
            }
        });
        console.log('- Phase Values from DOM:', phaseValuesFromDOM);

        // Ensure phase inputs are included in form submission
        // Even if hidden, they should be submitted, but let's make sure
        phaseInputs.forEach(input => {
            if(input.style.display === 'none' || input.closest('[style*="display: none"]')) {
                console.warn('Phase input is hidden:', input);
            }
        });

        // Check if critical fields are present
        if (!formData.get('orgDataID') || !formData.get('entityID')) {
            console.error('CRITICAL: orgDataID or entityID is missing!');
            console.error('orgDataID:', formData.get('orgDataID'));
            console.error('entityID:', formData.get('entityID'));

            // Try to find the hidden inputs
            const orgInput = document.querySelector('input[name="orgDataID"]');
            const entityInput = document.querySelector('input[name="entityID"]');
            console.error('orgDataID input element:', orgInput);
            console.error('orgDataID input value:', orgInput ? orgInput.value : 'NOT FOUND');
            console.error('entityID input element:', entityInput);
            console.error('entityID input value:', entityInput ? entityInput.value : 'NOT FOUND');

            alert('Error: Organization and Entity information is missing. Please refresh the page and try again.');

            const submitBtn = document.getElementById('wizardSubmitBtn');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="ri-checkbox-circle-line me-1"></i>Create Project';
            return;
        }

        // Show loading
        const submitBtn = document.getElementById('wizardSubmitBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i>Creating Project...';
        submitBtn.disabled = true;

        // Collect all form data including phases
        const submitFormData = new FormData(form);

        // Explicitly add phase data to ensure it's included
        // Reuse phaseInputs and phaseDescInputs already declared above

        // Remove existing phase data and re-add to ensure it's correct
        // Note: FormData doesn't have a delete method for array values, so we'll rebuild
        const finalFormData = new FormData();

        // Copy all form data except phase arrays
        for (let [key, value] of submitFormData.entries()) {
            // Skip phase arrays - we'll add them explicitly below
            if (key !== 'phaseName[]' && key !== 'phaseDescription[]' &&
                !key.startsWith('phaseName[') && !key.startsWith('phaseDescription[')) {
                finalFormData.append(key, value);
            }
        }

        // CRITICAL: Ensure skipProjectPlan checkbox is included
        const skipProjectPlanCheckbox = document.getElementById('skipProjectPlan');
        if (skipProjectPlanCheckbox) {
            // If checkbox is checked, add it; if unchecked, explicitly set to empty string
            // This ensures PHP receives the value correctly
            if (skipProjectPlanCheckbox.checked) {
                finalFormData.append('skipProjectPlan', skipProjectPlanCheckbox.value || '1');
            } else {
                // For unchecked checkboxes, we don't send anything (standard HTML behavior)
                // But we can explicitly set it to empty to ensure backend knows it's unchecked
                // Actually, let's not send it if unchecked - backend will default to false
            }
            console.log('skipProjectPlan checkbox:', skipProjectPlanCheckbox.checked ? 'CHECKED' : 'UNCHECKED', 'value:', skipProjectPlanCheckbox.value);
        } else {
            console.warn('skipProjectPlan checkbox not found!');
        }

        // Explicitly add phase data - ensure we're querying from the form, not document
        // Try multiple selectors to find phase inputs
        const formPhaseInputs = form.querySelectorAll('input[name="phaseName[]"]');
        const formPhaseDescInputs = form.querySelectorAll('textarea[name="phaseDescription[]"]');

        // Also try searching in the entire document as fallback
        const docPhaseInputs = document.querySelectorAll('input[name="phaseName[]"]');
        const docPhaseDescInputs = document.querySelectorAll('textarea[name="phaseDescription[]"]');

        // Use form inputs if found, otherwise use document inputs
        const phaseInputsToUse = formPhaseInputs.length > 0 ? formPhaseInputs : docPhaseInputs;
        const phaseDescInputsToUse = formPhaseDescInputs.length > 0 ? formPhaseDescInputs : docPhaseDescInputs;

        console.log('=== PHASE INPUT DETECTION ===');
        console.log('Form phase inputs found:', formPhaseInputs.length);
        console.log('Form phase description inputs found:', formPhaseDescInputs.length);
        console.log('Document phase inputs found:', docPhaseInputs.length);
        console.log('Document phase description inputs found:', docPhaseDescInputs.length);
        console.log('Using phase inputs:', phaseInputsToUse.length);

        // Log all phase inputs with their values
        phaseInputsToUse.forEach((input, index) => {
            console.log(`Phase input ${index + 1}:`, {
                value: input.value,
                name: input.name,
                id: input.id,
                parent: input.closest('.phase-item') ? 'Inside phase-item' : 'Not in phase-item',
                visible: input.offsetParent !== null ? 'Visible' : 'Hidden'
            });
        });

        // Add phase data explicitly
        let phasesAdded = 0;
        phaseInputsToUse.forEach((input, index) => {
            const phaseName = input.value.trim();
            if (phaseName) {
                phasesAdded++;
                console.log(`Adding phase ${phasesAdded}: ${phaseName}`);
                // Use both formats to ensure PHP receives it
                finalFormData.append('phaseName[]', phaseName);
                // Also try without brackets (PHP should handle both)
                // But FormData.append with brackets is standard, so let's stick with that

                // Add corresponding description if available
                if (phaseDescInputsToUse[index]) {
                    const phaseDesc = phaseDescInputsToUse[index].value.trim();
                    finalFormData.append('phaseDescription[]', phaseDesc);
                    console.log(`  Description: ${phaseDesc || '(empty)'}`);
                } else {
                    finalFormData.append('phaseDescription[]', '');
                }
            } else {
                console.log(`Skipping empty phase at index ${index} (value: "${input.value}")`);
            }
        });

        console.log(`Total phases added to form data: ${phasesAdded}`);

        // Log all final form data for debugging
        console.log('=== FINAL FORM DATA SUMMARY ===');
        console.log('skipProjectPlan:', finalFormData.get('skipProjectPlan') || '(not set - will default to false)');
        console.log('Phase Names:', Array.from(finalFormData.getAll('phaseName[]')));
        console.log('Phase Descriptions:', Array.from(finalFormData.getAll('phaseDescription[]')));
        console.log('Total form entries:', Array.from(finalFormData.entries()).length);

        // Debug: Log all keys in finalFormData
        const allKeys = [];
        for (let [key, value] of finalFormData.entries()) {
            allKeys.push(key);
        }
        console.log('All form keys:', allKeys);

        // Submit form via AJAX to have better control and ensure phase data is sent
        fetch(form.action, {
            method: 'POST',
            body: finalFormData,
            credentials: 'same-origin',
            redirect: 'follow' // Follow redirects
        })
        .then(response => {
            // Check if response is a redirect
            if (response.redirected || response.ok) {
                // Get the redirect URL or current URL
                const redirectUrl = response.url || response.headers.get('Location') || window.location.href;
                console.log('Form submitted successfully, redirecting to:', redirectUrl);
                window.location.href = redirectUrl;
            } else {
                // If not a redirect, try to get response text
                return response.text().then(text => {
                    console.error('Unexpected response:', text);
                    // Still try to submit normally as fallback
                    form.submit();
                });
            }
        })
        .catch(error => {
            console.error('Error submitting form:', error);
            console.log('Falling back to standard form submission...');
            // Fallback to standard form submission
            form.submit();
        });
    }

    // ================================================================
    // SAVE DRAFT
    // ================================================================
    function saveDraft() {
        const formData = new FormData(document.getElementById('projectWizardForm'));
        const draftData = {};

        for(let [key, value] of formData.entries()) {
            draftData[key] = value;
        }

        // Save to localStorage
        localStorage.setItem('projectWizardDraft', JSON.stringify(draftData));
        localStorage.setItem('projectWizardStep', currentStep);

        showNotification('Draft saved successfully', 'success');
    }

    function loadDraft() {
        const draft = localStorage.getItem('projectWizardDraft');
        const savedStep = localStorage.getItem('projectWizardStep');

        if(draft) {
            const confirmed = confirm('A saved draft was found. Would you like to continue from where you left off?');
            if(confirmed) {
                const draftData = JSON.parse(draft);
                populateFormWithDraft(draftData);

                if(savedStep) {
                    currentStep = parseInt(savedStep);
                    updateWizardDisplay();
                }
            } else {
                clearDraft();
            }
        }
    }

    function populateFormWithDraft(data) {
        const form = document.getElementById('projectWizardForm');
        if(!form) return;

        Object.keys(data).forEach(key => {
            const field = form.querySelector(`[name="${key}"]`);
            if(field) {
                if(field.type === 'radio' || field.type === 'checkbox') {
                    field.checked = field.value === data[key];
                } else {
                    field.value = data[key];
                }
            }
        });
    }

    function clearDraft() {
        localStorage.removeItem('projectWizardDraft');
        localStorage.removeItem('projectWizardStep');
    }

    // Clear draft on successful submission
    window.addEventListener('beforeunload', function() {
        // Draft will be cleared on form submit
    });

    // ================================================================
    // UTILITIES
    // ================================================================
    function formatDate(dateString) {
        if(!dateString) return '';

        try {
            const date = new Date(dateString);
            // Check if date is valid
            if(isNaN(date.getTime())) {
                return dateString; // Return original if invalid
            }
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            return date.toLocaleDateString('en-US', options);
        } catch(error) {
            console.error('Error formatting date:', error);
            return dateString;
        }
    }

    function showNotification(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);';
        alertDiv.innerHTML = `
            <i class="ri-${type === 'success' ? 'check' : type === 'danger' ? 'error-warning' : 'information'}-line me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(alertDiv);

        setTimeout(() => {
            if(alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 3000);
    }

    // ================================================================
    // DEBUG FUNCTION
    // ================================================================
    window.debugWizardData = function() {
        console.log('=== WIZARD DEBUG DATA ===');
        const form = document.getElementById('projectWizardForm');
        if(!form) {
            console.error('Form not found!');
            return;
        }

        const formData = new FormData(form);
        const data = {};

        for(let [key, value] of formData.entries()) {
            data[key] = value;
        }

        console.table(data);

        // Also check TomSelect values
        console.log('TomSelect Values:');
        const tomSelects = {
            client: document.getElementById('wizardClientID')?.tomselect?.getValue(),
            owner: document.getElementById('wizardProjectOwner')?.tomselect?.getValue(),
            managers: document.getElementById('wizardProjectManagers')?.tomselect?.getValue(),
            team: document.getElementById('wizardTeamMembers')?.tomselect?.getValue(),
            billingRate: document.getElementById('wizardBillingRate')?.tomselect?.getValue(),
            rounding: document.getElementById('wizardRounding')?.tomselect?.getValue()
        };
        console.table(tomSelects);

        alert('Check browser console for detailed wizard data');
    }

    // ================================================================
    // MODAL EVENTS
    // ================================================================
    // Reset wizard when modal is hidden
    const modal = document.getElementById('projectWizardModal');
    if(modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            // Ask if user wants to save draft
            if(currentStep > 1) {
                const save = confirm('Save your progress as a draft?');
                if(save) {
                    saveDraft();
                }
            }
        });

        modal.addEventListener('shown.bs.modal', function() {
            // Initialize first step
            currentStep = 1;
            updateWizardDisplay();

            // Reinitialize date pickers after modal opens
            setTimeout(() => {
                setupDatePickers();
            }, 200);

            // Load templates from database (with slight delay to ensure DOM is ready)
            setTimeout(() => {
                console.log('Loading project plan templates...');
                loadCustomTemplates();
            }, 300);
        });
    }

    // ================================================================
    // EXPOSE FUNCTIONS GLOBALLY
    // ================================================================
    // These functions need to be accessible from inline onclick handlers
    window.updateReviewSummary = updateReviewSummary;
    window.loadTemplateByID = loadTemplateByID;
    window.previewTemplate = previewTemplate;
    window.loadCustomTemplates = loadCustomTemplates;
})();
</script>

