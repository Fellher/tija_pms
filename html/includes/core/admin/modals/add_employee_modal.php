<?php
// Modal for adding Employee
echo Utility::form_modal_header("addEmployeeModal", "global/admin/manage_employee_from_entity.php", "Add Employee to Entity", array('modal-md', 'modal-dialog-centered', "modal-xl"), $base);
?>
<div id="employee_form" class="manageEmployee">
    <!-- Enhanced Progress Steps with Visual Feedback -->
    <div class="mb-4">
        <div class="progress-wizard">
            <div class="step-item active" id="step1Indicator">
                <div class="step-circle">
                    <i class="fas fa-user"></i>
                    <span class="step-number">1</span>
                </div>
                <div class="step-label">
                    <strong>Personal Info</strong>
                    <small class="d-block text-muted">Basic details</small>
                </div>
            </div>
            <div class="step-connector">
                <div class="step-line"></div>
            </div>
            <div class="step-item" id="step2Indicator">
                <div class="step-circle">
                    <i class="fas fa-briefcase"></i>
                    <span class="step-number">2</span>
                </div>
                <div class="step-label">
                    <strong>Employment</strong>
                    <small class="d-block text-muted">Job details</small>
                </div>
            </div>
            <div class="step-connector">
                <div class="step-line"></div>
            </div>
            <div class="step-item" id="step3Indicator">
                <div class="step-circle">
                    <i class="fas fa-money-bill-wave"></i>
                    <span class="step-number">3</span>
                </div>
                <div class="step-label">
                    <strong>Payroll</strong>
                    <small class="d-block text-muted">Salary info</small>
                </div>
            </div>
        </div>

        <!-- Current Step Indicator -->
        <div class="alert alert-info mt-3 mb-0" id="currentStepAlert">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Step 1 of 3:</strong> <span id="currentStepText">Please enter personal information</span>
        </div>
    </div>

    <!-- Hidden Fields -->
    <input type="hidden" class="form-control" id="emp_orgDataID" name="orgDataID" value="<?= isset($entity->orgDataID) ? $entity->orgDataID : '' ?>">
    <input type="hidden" class="form-control" id="emp_entityID" name="entityID" value="<?= isset($entityID) ? $entityID : '' ?>">
    <input type="hidden" id="employeeID" name="ID" value="">

    <!-- Step 1: Personal Information -->
    <div class="step-content" id="step1Content">
        <h6 class="mb-3 text-primary"><i class="fas fa-user me-2"></i>Personal Information</h6>
        <div class="row g-3">
            <div class="col-md-2">
                <label for="emp_prefixID" class="form-label mb-0">Prefix</label>
                <select id="emp_prefixID" name="prefixID" class="form-select form-control-sm">
                    <option value="">Select</option>
                    <?php if ($namePrefixes): foreach ($namePrefixes as $prefix): ?>
                        <option value="<?= $prefix->prefixID ?>"><?= htmlspecialchars($prefix->prefixName) ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>

            <div class="col-md-5">
                <label for="emp_FirstName" class="form-label mb-0">First Name <span class="text-danger">*</span></label>
                <input type="text" id="emp_FirstName" name="FirstName" class="form-control form-control-sm" placeholder="First Name" required>
            </div>

            <div class="col-md-5">
                <label for="emp_Surname" class="form-label mb-0">Surname <span class="text-danger">*</span></label>
                <input type="text" id="emp_Surname" name="Surname" class="form-control form-control-sm" placeholder="Surname" required>
            </div>

            <div class="col-md-6">
                <label for="emp_OtherNames" class="form-label mb-0">Other Names</label>
                <input type="text" id="emp_OtherNames" name="OtherNames" class="form-control form-control-sm" placeholder="Other Names">
            </div>

            <div class="col-md-6">
                <label for="emp_userInitials" class="form-label mb-0">Initials</label>
                <input type="text" id="emp_userInitials" name="userInitials" class="form-control form-control-sm" placeholder="e.g., J.D.">
            </div>

            <div class="col-md-6">
                <label for="emp_Email" class="form-label mb-0">Email <span class="text-danger">*</span></label>
                <input type="email" id="emp_Email" name="Email" class="form-control form-control-sm" placeholder="email@example.com" required>
            </div>

            <div class="col-md-6">
                <label for="emp_phoneNumber" class="form-label mb-0">Phone Number <span class="text-danger">*</span></label>
                <input type="tel" id="emp_phoneNumber" name="phoneNumber" class="form-control form-control-sm" placeholder="+254 712 345 678" required>
            </div>

            <div class="col-md-4">
                <label for="emp_gender" class="form-label mb-0">Gender <span class="text-danger">*</span></label>
                <select id="emp_gender" name="gender" class="form-select form-control-sm" required>
                    <option value="">Select Gender</option>
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                    <option value="O">Other</option>
                </select>
            </div>

            <div class="col-md-4">
                <label for="emp_dateOfBirth" class="form-label mb-0">Date of Birth</label>
                <input type="text" id="emp_dateOfBirth" name="dateOfBirth" class="form-control form-control-sm" placeholder="Select Date of Birth" readonly>
            </div>

            <div class="col-md-4">
                <label for="emp_nationalID" class="form-label mb-0">National ID</label>
                <input type="text" id="emp_nationalID" name="nationalID" class="form-control form-control-sm" placeholder="National ID Number">
            </div>
        </div>

        <div class="mt-3 d-flex justify-content-end">
            <button type="button" class="btn btn-primary btn-sm" onclick="goToStep(2)">
                Next <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </div>
    </div>

    <!-- Step 2: Employment Details -->
    <div class="step-content" id="step2Content" style="display: none;">
        <h6 class="mb-3 text-primary"><i class="fas fa-briefcase me-2"></i>Employment Details</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="emp_jobTitleID" class="form-label mb-0">Job Title <span class="text-danger">*</span></label>
                <select id="emp_jobTitleID" name="jobTitleID" class="form-select form-control-sm" required>
                    <option value="">Select Job Title</option>
                    <?php if ($jobTitles): foreach ($jobTitles as $jt): ?>
                        <option value="<?= $jt->jobTitleID ?>"><?= htmlspecialchars($jt->jobTitle) ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label for="emp_employeeTypeID" class="form-label mb-0">Employment Status <span class="text-danger">*</span></label>
                <select id="emp_employeeTypeID" name="employeeTypeID" class="form-select form-control-sm" required>
                    <option value="">Select Status</option>
                    <?php if ($employmentStatuses): foreach ($employmentStatuses as $es): ?>
                        <option value="<?= $es->employmentStatusID ?>"><?= htmlspecialchars($es->employmentStatusTitle) ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label for="emp_dateOfEmployment" class="form-label mb-0">Employment Start Date <span class="text-danger">*</span></label>
                <input type="text" id="emp_dateOfEmployment" name="dateOfEmployment" class="form-control form-control-sm" placeholder="Select Employment Start Date" required readonly>
            </div>

            <div class="col-md-6">
                <label for="emp_payrollNumber" class="form-label mb-0">Payroll Number</label>
                <input type="text" id="emp_payrollNumber" name="payrollNumber" class="form-control form-control-sm" placeholder="Employee Number">
            </div>

            <div class="col-md-6">
                <label for="emp_supervisorID" class="form-label mb-0">Supervisor</label>
                <select id="emp_supervisorID" name="supervisorID" class="form-select form-control-sm">
                    <option value="0">
                        <i class="fas fa-crown"></i> No Supervisor (Reports to Board/External)
                    </option>
                    <option value="" disabled>──────────────────</option>
                    <?php if ($entityEmployees): foreach ($entityEmployees as $emp): ?>
                        <option value="<?= $emp->ID ?>"><?= htmlspecialchars($emp->employeeName ?? $emp->employeeName ?? 'N/A') ?></option>
                    <?php endforeach; endif; ?>
                </select>
                <small class="form-text text-muted d-block mt-1">
                    <i class="fas fa-info-circle me-1"></i>
                    Select "No Supervisor" for top-level positions like CEO
                </small>
            </div>

            <div class="col-md-6">
                <label for="emp_dailyWorkHours" class="form-label mb-0">Daily Work Hours</label>
                <input type="number" id="emp_dailyWorkHours" name="dailyWorkHours" class="form-control form-control-sm" placeholder="8" min="1" max="24" step="0.5">
            </div>
        </div>

        <div class="mt-3 d-flex justify-content-between">
            <button type="button" class="btn btn-secondary btn-sm" onclick="goToStep(1)">
                <i class="fas fa-arrow-left me-2"></i> Previous
            </button>
            <button type="button" class="btn btn-primary btn-sm" onclick="goToStep(3)">
                Next <i class="fas fa-arrow-right ms-2"></i>
            </button>
        </div>
    </div>

    <!-- Step 3: Payroll & Benefits -->
    <div class="step-content" id="step3Content" style="display: none;">
        <h6 class="mb-3 text-primary"><i class="fas fa-money-bill-wave me-2"></i>Payroll & Benefits</h6>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="emp_basicSalary" class="form-label mb-0">Basic Salary</label>
                <input type="number" id="emp_basicSalary" name="basicSalary" class="form-control form-control-sm" placeholder="0.00" min="0" step="0.01">
            </div>

            <div class="col-md-6">
                <label for="emp_pin" class="form-label mb-0">KRA PIN</label>
                <input type="text" id="emp_pin" name="pin" class="form-control form-control-sm" placeholder="KRA PIN">
            </div>

            <div class="col-md-4">
                <label for="emp_nhifNumber" class="form-label mb-0">NHIF Number</label>
                <input type="text" id="emp_nhifNumber" name="nhifNumber" class="form-control form-control-sm" placeholder="NHIF Number">
            </div>

            <div class="col-md-4">
                <label for="emp_nssfNumber" class="form-label mb-0">NSSF Number</label>
                <input type="text" id="emp_nssfNumber" name="nssfNumber" class="form-control form-control-sm" placeholder="NSSF Number">
            </div>

            <div class="col-md-4">
                <label for="emp_costPerHour" class="form-label mb-0">Cost Per Hour</label>
                <input type="number" id="emp_costPerHour" name="costPerHour" class="form-control form-control-sm" placeholder="0.00" min="0" step="0.01">
            </div>

            <div class="col-md-12">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="emp_overtimeAllowed" name="overtimeAllowed" value="Y">
                    <label class="form-check-label" for="emp_overtimeAllowed">Overtime Allowed</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="emp_bonusEligible" name="bonusEligible" value="Y">
                    <label class="form-check-label" for="emp_bonusEligible">Bonus Eligible</label>
                </div>
            </div>
        </div>

        <div class="mt-3 d-flex justify-content-between">
            <button type="button" class="btn btn-secondary btn-sm" onclick="goToStep(2)">
                <i class="fas fa-arrow-left me-2"></i> Previous
            </button>
            <div>
                <button type="button" class="btn btn-outline-secondary btn-sm me-2" onclick="skipPayroll()">
                    Skip Payroll
                </button>
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="fas fa-save me-2"></i> Save Employee
                </button>
            </div>
        </div>
    </div>
</div>
<?php
echo Utility::form_modal_footer('Save Employee', 'submitEmployee', 'btn btn-success btn-sm d-none');
?>

<!-- Enhanced Wizard Styles -->
<style>
.progress-wizard {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    padding: 20px 0;
}

.step-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    flex: 0 0 auto;
    z-index: 2;
    transition: all 0.3s ease;
}

.step-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #e9ecef;
    border: 3px solid #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    transition: all 0.3s ease;
    margin-bottom: 10px;
}

.step-circle i {
    font-size: 24px;
    color: #6c757d;
    display: block;
    transition: all 0.3s ease;
}

.step-circle .step-number {
    position: absolute;
    bottom: -8px;
    right: -8px;
    background: #6c757d;
    color: white;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
    border: 3px solid white;
    transition: all 0.3s ease;
}

.step-label {
    text-align: center;
    max-width: 120px;
}

.step-label strong {
    font-size: 14px;
    color: #495057;
    transition: all 0.3s ease;
}

.step-label small {
    font-size: 11px;
}

.step-connector {
    flex: 1;
    display: flex;
    align-items: center;
    padding: 0 15px;
}

.step-line {
    width: 100%;
    height: 3px;
    background: #dee2e6;
    position: relative;
    transition: all 0.3s ease;
}

/* Active Step Styling */
.step-item.active .step-circle {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: #667eea;
    box-shadow: 0 0 20px rgba(102, 126, 234, 0.4);
    transform: scale(1.1);
}

.step-item.active .step-circle i {
    color: white;
}

.step-item.active .step-circle .step-number {
    background: #fff;
    color: #667eea;
}

.step-item.active .step-label strong {
    color: #667eea;
    font-size: 15px;
}

/* Completed Step Styling */
.step-item.completed .step-circle {
    background: #28a745;
    border-color: #28a745;
}

.step-item.completed .step-circle i {
    color: white;
}

.step-item.completed .step-circle .step-number {
    background: #fff;
    color: #28a745;
}

.step-item.completed .step-label strong {
    color: #28a745;
}

.step-item.completed + .step-connector .step-line {
    background: #28a745;
}

/* Hover Effects */
.step-item:not(.active):hover .step-circle {
    transform: scale(1.05);
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

/* Current Step Alert */
#currentStepAlert {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    border-left: 4px solid #667eea;
    border-radius: 8px;
    padding: 12px 20px;
}

#currentStepAlert i {
    color: #667eea;
}

/* Step Content Animation */
.step-content {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .progress-wizard {
        flex-direction: column;
        gap: 20px;
    }

    .step-connector {
        display: none;
    }

    .step-item {
        width: 100%;
    }

    .step-circle {
        width: 50px;
        height: 50px;
    }

    .step-circle i {
        font-size: 20px;
    }
}
</style>

