<?php
/**
 * Policy Form View (Create/Edit)
 */

$isEdit = isset($policy) && $policy;
$formAction = $isEdit ? "?action=update&policyID={$policy['policyID']}" : "?action=create";
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="ri-file-edit-line me-2"></i>
                    <?= $isEdit ? 'Edit Policy' : 'Create New Policy' ?>
                </h5>
            </div>

            <div class="card-body">
                <form id="policyForm" action="<?= $formAction ?>" method="POST">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h6 class="section-title">
                            <i class="ri-information-line me-2"></i>
                            Basic Information
                        </h6>

                        <div class="row">
                            <div class="col-md-8">
                                <label for="policyName" class="form-label">Policy Name *</label>
                                <input type="text" class="form-control" id="policyName" name="policyName"
                                       value="<?= $isEdit ? htmlspecialchars($policy['policyName']) : '' ?>" required>
                                <div class="invalid-feedback">Please provide a policy name.</div>
                            </div>

                            <div class="col-md-4">
                                <label for="priority" class="form-label">Priority</label>
                                <input type="number" class="form-control" id="priority" name="priority"
                                       value="<?= $isEdit ? $policy['priority'] : '1' ?>" min="1" max="100">
                                <small class="form-text text-muted">Lower numbers have higher priority</small>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label for="policyDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="policyDescription" name="policyDescription" rows="3"><?= $isEdit ? htmlspecialchars($policy['policyDescription']) : '' ?></textarea>
                        </div>
                    </div>

                    <!-- Leave Type and Accrual Settings -->
                    <div class="form-section">
                        <h6 class="section-title">
                            <i class="ri-calendar-check-line me-2"></i>
                            Accrual Settings
                        </h6>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="leaveTypeID" class="form-label">Leave Type *</label>
                                <select class="form-select" id="leaveTypeID" name="leaveTypeID" required>
                                    <option value="">Select Leave Type</option>
                                    <?php foreach ($leaveTypes as $leaveType): ?>
                                    <option value="<?= $leaveType['leaveTypeID'] ?>"
                                            <?= $isEdit && $policy['leaveTypeID'] == $leaveType['leaveTypeID'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($leaveType['leaveTypeName']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Please select a leave type.</div>
                            </div>

                            <div class="col-md-3">
                                <label for="accrualType" class="form-label">Accrual Type *</label>
                                <select class="form-select" id="accrualType" name="accrualType" required onchange="updateAccrualTypeFields(this.value)">
                                    <option value="">Select Type</option>
                                    <option value="Front-Loaded" <?= $isEdit && isset($policy['accrualType']) && $policy['accrualType'] === 'Front-Loaded' ? 'selected' : '' ?>>Front-Loaded</option>
                                    <option value="Periodic" <?= $isEdit && isset($policy['accrualType']) && ($policy['accrualType'] === 'Periodic' || in_array($policy['accrualType'], ['Monthly', 'Quarterly', 'Continuous'])) ? 'selected' : '' ?>>Periodic</option>
                                    <option value="Proration" <?= $isEdit && isset($policy['accrualType']) && $policy['accrualType'] === 'Proration' ? 'selected' : '' ?>>Proration</option>
                                </select>
                                <small class="form-text text-muted" id="accrualTypeHelp"></small>
                                <div class="invalid-feedback">Please select an accrual type.</div>
                            </div>

                            <!-- Accrual Period (for Periodic type) -->
                            <div class="col-md-3" id="accrualPeriodField" style="display: none;">
                                <label for="accrualPeriod" class="form-label">Accrual Period</label>
                                <select class="form-select" id="accrualPeriod" name="accrualPeriod">
                                    <option value="Daily">Daily</option>
                                    <option value="Weekly">Weekly</option>
                                    <option value="Bi-Weekly">Bi-Weekly</option>
                                    <option value="Monthly" <?= $isEdit && isset($policy['accrualPeriod']) && $policy['accrualPeriod'] === 'Monthly' ? 'selected' : 'selected' ?>>Monthly</option>
                                    <option value="Quarterly">Quarterly</option>
                                    <option value="Bi-Annually">Bi-Annually</option>
                                    <option value="Annually">Annually</option>
                                </select>
                                <small class="form-text text-muted">Frequency for periodic accrual</small>
                            </div>

                            <!-- Front Load Date (for Front-Loaded type) -->
                            <div class="col-md-3" id="frontLoadDateField" style="display: none;">
                                <label for="frontLoadDate" class="form-label">Front-Load Date</label>
                                <input type="date" class="form-control" id="frontLoadDate" name="frontLoadDate"
                                       value="<?= $isEdit && isset($policy['frontLoadDate']) ? htmlspecialchars($policy['frontLoadDate']) : '' ?>">
                                <small class="form-text text-muted">Date when full amount is granted</small>
                            </div>

                            <!-- Proration Basis (for Proration type) -->
                            <div class="col-md-3" id="prorationBasisField" style="display: none;">
                                <label for="prorationBasis" class="form-label">Proration Basis</label>
                                <select class="form-select" id="prorationBasis" name="prorationBasis">
                                    <option value="Days Worked" <?= $isEdit && isset($policy['prorationBasis']) && $policy['prorationBasis'] === 'Days Worked' ? 'selected' : 'selected' ?>>Days Worked</option>
                                    <option value="Months Worked" <?= $isEdit && isset($policy['prorationBasis']) && $policy['prorationBasis'] === 'Months Worked' ? 'selected' : '' ?>>Months Worked</option>
                                    <option value="Service Period" <?= $isEdit && isset($policy['prorationBasis']) && $policy['prorationBasis'] === 'Service Period' ? 'selected' : '' ?>>Service Period</option>
                                    <option value="Custom" <?= $isEdit && isset($policy['prorationBasis']) && $policy['prorationBasis'] === 'Custom' ? 'selected' : '' ?>>Custom</option>
                                </select>
                                <small class="form-text text-muted">Basis for proration calculation</small>
                            </div>

                            <div class="col-md-3">
                                <label for="accrualRate" class="form-label">Accrual Rate *</label>
                                <input type="number" class="form-control" id="accrualRate" name="accrualRate"
                                       value="<?= $isEdit ? $policy['accrualRate'] : '' ?>" step="0.01" min="0" required>
                                <small class="form-text text-muted">Days per period</small>
                                <div class="invalid-feedback">Please provide a valid accrual rate.</div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label for="accrualStartDate" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="accrualStartDate" name="accrualStartDate"
                                       value="<?= $isEdit && $policy['accrualStartDate'] ? $policy['accrualStartDate'] : '' ?>">
                                <small class="form-text text-muted">Leave blank for immediate start</small>
                            </div>

                            <div class="col-md-6">
                                <label for="accrualEndDate" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="accrualEndDate" name="accrualEndDate"
                                       value="<?= $isEdit && $policy['accrualEndDate'] ? $policy['accrualEndDate'] : '' ?>">
                                <small class="form-text text-muted">Leave blank for indefinite</small>
                            </div>
                        </div>
                    </div>

                    <!-- Carryover Settings -->
                    <div class="form-section">
                        <h6 class="section-title">
                            <i class="ri-refresh-line me-2"></i>
                            Carryover Settings
                        </h6>

                        <div class="row">
                            <div class="col-md-4">
                                <label for="maxCarryover" class="form-label">Max Carryover Days</label>
                                <input type="number" class="form-control" id="maxCarryover" name="maxCarryover"
                                       value="<?= $isEdit ? $policy['maxCarryover'] : '' ?>" min="0">
                                <small class="form-text text-muted">Leave blank for unlimited</small>
                            </div>

                            <div class="col-md-4">
                                <label for="carryoverExpiryMonths" class="form-label">Carryover Expiry (Months)</label>
                                <input type="number" class="form-control" id="carryoverExpiryMonths" name="carryoverExpiryMonths"
                                       value="<?= $isEdit ? $policy['carryoverExpiryMonths'] : '' ?>" min="1">
                                <small class="form-text text-muted">Leave blank for no expiry</small>
                            </div>

                            <div class="col-md-4">
                                <label for="proRated" class="form-label">Pro-rated Accrual</label>
                                <select class="form-select" id="proRated" name="proRated">
                                    <option value="N" <?= $isEdit && $policy['proRated'] === 'N' ? 'selected' : '' ?>>No</option>
                                    <option value="Y" <?= $isEdit && $policy['proRated'] === 'Y' ? 'selected' : '' ?>>Yes</option>
                                </select>
                                <small class="form-text text-muted">Calculate based on partial periods</small>
                            </div>
                        </div>
                    </div>

                    <!-- Status Settings -->
                    <div class="form-section">
                        <h6 class="section-title">
                            <i class="ri-settings-3-line me-2"></i>
                            Status Settings
                        </h6>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="isActive" class="form-label">Policy Status</label>
                                <select class="form-select" id="isActive" name="isActive">
                                    <option value="Y" <?= $isEdit && $policy['isActive'] === 'Y' ? 'selected' : '' ?>>Active</option>
                                    <option value="N" <?= $isEdit && $policy['isActive'] === 'N' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Rules Section -->
                    <div class="form-section">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="section-title mb-0">
                                <i class="ri-rules-line me-2"></i>
                                Accumulation Rules
                            </h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRule()">
                                <i class="ri-add-line me-1"></i>Add Rule
                            </button>
                        </div>

                        <div id="rulesContainer">
                            <!-- Rules will be added here dynamically -->
                        </div>

                        <small class="text-muted">
                            <i class="ri-information-line me-1"></i>
                            Rules allow you to apply multipliers based on employee characteristics like tenure, performance, or role.
                        </small>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between">
                        <a href="?action=list" class="btn btn-outline-secondary">
                            <i class="ri-arrow-left-line me-1"></i>
                            Back to List
                        </a>

                        <div>
                            <button type="button" class="btn btn-outline-primary me-2" onclick="validatePolicyForm()">
                                <i class="ri-save-line me-1"></i>
                                Save Draft
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-check-line me-1"></i>
                                <?= $isEdit ? 'Update Policy' : 'Create Policy' ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="ri-lightbulb-line me-2"></i>
                    Policy Guidelines
                </h6>
            </div>

            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="ri-information-line me-1"></i> Accrual Types</h6>
                    <ul class="mb-0 small">
                        <li><strong>Monthly:</strong> Days accrued each month</li>
                        <li><strong>Quarterly:</strong> Days accrued every 3 months</li>
                        <li><strong>Annual:</strong> Days accrued once per year</li>
                        <li><strong>Continuous:</strong> Days accrued continuously</li>
                        <li><strong>Custom:</strong> Custom accrual schedule</li>
                    </ul>
                </div>

                <div class="alert alert-warning">
                    <h6><i class="ri-alarm-warning-line me-1"></i> Priority Rules</h6>
                    <p class="mb-0 small">
                        Lower priority numbers are applied first. Multiple policies can apply to the same employee,
                        with rules determining the final accrual amount.
                    </p>
                </div>

                <div class="alert alert-success">
                    <h6><i class="ri-check-line me-1"></i> Best Practices</h6>
                    <ul class="mb-0 small">
                        <li>Use clear, descriptive policy names</li>
                        <li>Set reasonable carryover limits</li>
                        <li>Consider pro-rated accrual for fairness</li>
                        <li>Test policies before activating</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rule Template -->
<template id="ruleTemplate">
    <div class="rule-item p-3 mb-3 border rounded">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <h6 class="mb-0">Rule</h6>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRule(this)">
                <i class="ri-delete-bin-line"></i>
            </button>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Rule Name</label>
                <input type="text" class="form-control" name="ruleName[]" placeholder="e.g., Senior Staff Rule">
            </div>

            <div class="col-md-6">
                <label class="form-label">Rule Type</label>
                <select class="form-select" name="ruleType[]">
                    <option value="Tenure">Tenure</option>
                    <option value="Performance">Performance</option>
                    <option value="Department">Department</option>
                    <option value="Role">Role</option>
                    <option value="Custom">Custom</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label">Condition Field</label>
                <select class="form-select" name="conditionField[]">
                    <option value="yearsOfService">Years of Service</option>
                    <option value="performanceRating">Performance Rating</option>
                    <option value="departmentName">Department</option>
                    <option value="jobTitle">Job Title</option>
                    <option value="jobLevel">Job Level</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Operator</label>
                <select class="form-select" name="conditionOperator[]">
                    <option value=">=">>=</option>
                    <option value=">">></option>
                    <option value="=">=</option>
                    <option value="<"><</option>
                    <option value="<="><=</option>
                    <option value="LIKE">LIKE</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Value</label>
                <input type="text" class="form-control" name="conditionValue[]" placeholder="e.g., 5">
            </div>

            <div class="col-md-2">
                <label class="form-label">Multiplier</label>
                <input type="number" class="form-control" name="accrualMultiplier[]" value="1.00" step="0.01" min="0">
            </div>
        </div>
    </div>
</template>

<script>
// Update accrual type fields visibility
function updateAccrualTypeFields(accrualType) {
    const accrualPeriodField = document.getElementById('accrualPeriodField');
    const frontLoadDateField = document.getElementById('frontLoadDateField');
    const prorationBasisField = document.getElementById('prorationBasisField');
    const accrualTypeFieldsRow = document.getElementById('accrualTypeFieldsRow');
    const helpElement = document.getElementById('accrualTypeHelp');

    const helpTexts = {
        'Front-Loaded': 'Full annual entitlement granted upfront at a specified date (e.g., 30 days on January 1st)',
        'Periodic': 'Leave accrued at regular intervals based on accrual period (e.g., 2.5 days per month)',
        'Proration': 'Leave accrued proportionally based on days/months worked or service period'
    };

    // Update help text
    if (helpElement) {
        helpElement.textContent = helpTexts[accrualType] || '';
    }

    // Hide all fields first
    if (accrualPeriodField) accrualPeriodField.style.display = 'none';
    if (frontLoadDateField) frontLoadDateField.style.display = 'none';
    if (prorationBasisField) prorationBasisField.style.display = 'none';
    if (accrualTypeFieldsRow) accrualTypeFieldsRow.style.display = 'none';

    // Show relevant field based on type
    if (accrualTypeFieldsRow) {
        switch(accrualType) {
            case 'Periodic':
                accrualTypeFieldsRow.style.display = 'flex';
                if (accrualPeriodField) accrualPeriodField.style.display = 'block';
                break;
            case 'Front-Loaded':
                accrualTypeFieldsRow.style.display = 'flex';
                if (frontLoadDateField) frontLoadDateField.style.display = 'block';
                break;
            case 'Proration':
                accrualTypeFieldsRow.style.display = 'flex';
                if (prorationBasisField) prorationBasisField.style.display = 'block';
                break;
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const accrualTypeSelect = document.getElementById('accrualType');
    if (accrualTypeSelect && accrualTypeSelect.value) {
        updateAccrualTypeFields(accrualTypeSelect.value);
    }
});
</script>

