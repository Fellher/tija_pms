<!-- Policy Form View -->
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="form-section">
            <h4 class="section-title">
                <i class="ri-add-circle-line me-2"></i>
                Create New Accumulation Policy
            </h4>

            <form id="policyForm" method="POST" action="<?= $base ?>/php/scripts/leave/config/manage_accumulation_policy.php">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="policyName" class="form-label">
                                Policy Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="policyName"
                                   name="policyName"
                                   placeholder="e.g., Annual Leave Policy"
                                   required>
                            <div class="form-text">Enter a descriptive name for the policy</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="accrualType" class="form-label">
                                Accrual Type <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="accrualType" name="accrualType" required onchange="updateAccrualTypeFields(this.value)">
                                <option value="">Select Accrual Type</option>
                                <option value="Front-Loaded">Front-Loaded</option>
                                <option value="Periodic">Periodic</option>
                                <option value="Proration">Proration</option>
                            </select>
                            <small class="form-text text-muted" id="accrualTypeHelp"></small>
                            <div class="form-text">How leave is accrued</div>
                        </div>
                    </div>

                    <!-- Additional fields based on accrual type -->
                    <div class="col-md-6" id="accrualPeriodField" style="display: none;">
                        <div class="mb-3">
                            <label for="accrualPeriod" class="form-label">Accrual Period</label>
                            <select class="form-select" id="accrualPeriod" name="accrualPeriod">
                                <option value="Daily">Daily</option>
                                <option value="Weekly">Weekly</option>
                                <option value="Bi-Weekly">Bi-Weekly</option>
                                <option value="Monthly" selected>Monthly</option>
                                <option value="Quarterly">Quarterly</option>
                                <option value="Bi-Annually">Bi-Annually</option>
                                <option value="Annually">Annually</option>
                            </select>
                            <div class="form-text">Frequency for periodic accrual</div>
                        </div>
                    </div>

                    <div class="col-md-6" id="frontLoadDateField" style="display: none;">
                        <div class="mb-3">
                            <label for="frontLoadDate" class="form-label">Front-Load Date</label>
                            <input type="date" class="form-control" id="frontLoadDate" name="frontLoadDate">
                            <div class="form-text">Date when full amount is granted</div>
                        </div>
                    </div>

                    <div class="col-md-6" id="prorationBasisField" style="display: none;">
                        <div class="mb-3">
                            <label for="prorationBasis" class="form-label">Proration Basis</label>
                            <select class="form-select" id="prorationBasis" name="prorationBasis">
                                <option value="Days Worked" selected>Days Worked</option>
                                <option value="Months Worked">Months Worked</option>
                                <option value="Service Period">Service Period</option>
                                <option value="Custom">Custom</option>
                            </select>
                            <div class="form-text">Basis for proration calculation</div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="accrualRate" class="form-label">
                                Accrual Rate <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   class="form-control"
                                   id="accrualRate"
                                   name="accrualRate"
                                   step="0.01"
                                   min="0"
                                   max="365"
                                   placeholder="e.g., 2.5"
                                   required>
                            <div class="form-text">Days accrued per period</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="maxDays" class="form-label">Maximum Days</label>
                            <input type="number"
                                   class="form-control"
                                   id="maxDays"
                                   name="maxDays"
                                   min="0"
                                   max="365"
                                   placeholder="Leave empty for no limit">
                            <div class="form-text">Maximum days that can be accumulated</div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control"
                              id="description"
                              name="description"
                              rows="4"
                              placeholder="Describe the policy rules and conditions..."></textarea>
                    <div class="form-text">Optional detailed description of the policy</div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <div class="form-text">Set the initial status of this policy</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <input type="number"
                                   class="form-control"
                                   id="priority"
                                   name="priority"
                                   value="1"
                                   min="1"
                                   max="10">
                            <div class="form-text">Priority order (1 = highest)</div>
                        </div>
                    </div>
                </div>

                <!-- Policy Rules Section -->
                <div class="mt-4">
                    <h5 class="mb-3">
                        <i class="ri-list-check me-2"></i>
                        Policy Rules
                    </h5>

                    <div id="rulesContainer">
                        <!-- Rules will be added dynamically -->
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-outline-primary" data-action="add-policy-rule">
                            <i class="ri-add-line me-1"></i>
                            Add Rule
                        </button>
                        <small class="text-muted">Add specific rules for different leave types</small>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-between mt-4">
                    <a href="?action=accumulation_policies" class="btn btn-outline-secondary">
                        <i class="ri-arrow-left-line me-1"></i>
                        Back to Policies
                    </a>

                    <div>
                        <button type="button" class="btn btn-outline-primary me-2" data-action="reset-policy-form">
                            <i class="ri-refresh-line me-1"></i>
                            Reset
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-1"></i>
                            Create Policy
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Rule Template for Dynamic Addition -->
<template id="ruleTemplate">
    <div class="rule-item">
        <div class="row">
            <div class="col-md-3">
                <label class="form-label">Leave Type</label>
                <select name="ruleLeaveTypeID[]" class="form-select" required>
                    <option value="">Select Leave Type</option>
                    <?php
                    $leaveTypes = Leave::leave_types(array('Lapsed' => 'N'), false, $DBConn);
                    if ($leaveTypes): ?>
                        <?php foreach ($leaveTypes as $type): ?>
                            <option value="<?= $type->leaveTypeID ?>">
                                <?= htmlspecialchars($type->leaveTypeName) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Accrual Rate</label>
                <input type="number" name="ruleAccrualRate[]" class="form-control"
                       step="0.01" min="0" max="365" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Max Days</label>
                <input type="number" name="ruleMaxDays[]" class="form-control"
                       min="0" max="365">
            </div>
            <div class="col-md-2">
                <label class="form-label">Priority</label>
                <input type="number" name="rulePriority[]" class="form-control"
                       min="1" max="10" value="1">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="ruleStatus[]" class="form-select">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-outline-danger remove-rule-btn" data-action="remove-policy-rule"
                        title="Remove Rule">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('policyForm');
    const rulesContainer = document.getElementById('rulesContainer');
    const ruleTemplate = document.getElementById('ruleTemplate');

    document.querySelectorAll('[data-action="add-policy-rule"]').forEach(button => {
        button.addEventListener('click', () => {
            if (!ruleTemplate || !rulesContainer) {
                return;
            }

            const fragment = ruleTemplate.content.cloneNode(true);
            rulesContainer.appendChild(fragment);
        });
    });

    if (rulesContainer) {
        rulesContainer.addEventListener('click', event => {
            const target = event.target.closest('[data-action="remove-policy-rule"]');
            if (!target) {
                return;
            }

            event.preventDefault();
            const ruleItem = target.closest('.rule-item');
            if (ruleItem) {
                ruleItem.remove();
            }
        });
    }

    document.querySelectorAll('[data-action="reset-policy-form"]').forEach(button => {
        button.addEventListener('click', () => {
            if (!form) {
                return;
            }

            if (confirm('Are you sure you want to reset the form? All entered data will be lost.')) {
                form.reset();

                const fields = document.querySelectorAll('.is-invalid, .is-valid');
                fields.forEach(field => field.classList.remove('is-invalid', 'is-valid'));

                if (rulesContainer) {
                    rulesContainer.innerHTML = '';
                }
            }
        });
    });
});
// Update accrual type fields visibility
function updateAccrualTypeFields(accrualType) {
    const accrualPeriodField = document.getElementById('accrualPeriodField');
    const frontLoadDateField = document.getElementById('frontLoadDateField');
    const prorationBasisField = document.getElementById('prorationBasisField');
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

    // Show relevant field based on type
    switch(accrualType) {
        case 'Periodic':
            if (accrualPeriodField) accrualPeriodField.style.display = 'block';
            break;
        case 'Front-Loaded':
            if (frontLoadDateField) frontLoadDateField.style.display = 'block';
            break;
        case 'Proration':
            if (prorationBasisField) prorationBasisField.style.display = 'block';
            break;
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
