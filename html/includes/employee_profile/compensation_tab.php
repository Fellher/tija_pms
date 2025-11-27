<?php
/**
 * Compensation Tab
 * Displays salary and allowances from user_details and allowances tables
 * Only visible to admins
 * Includes separate inline edit functionality for Basic Salary and Allowances
 */

if (!$canViewSalary) {
    echo '<div class="alert alert-warning">You do not have permission to view salary information.</div>';
    return;
}

// Get current allowances (LEGACY - for backward compatibility, keep for now)
$allowances = EmployeeProfileExtended::get_allowances(['employeeID' => $employeeID, 'isCurrent' => 'Y'], true, $DBConn);

// Create default allowances object if none exists
if (!$allowances) {
    $allowances = (object)[
        'housingAllowance' => 0,
        'transportAllowance' => 0,
        'medicalAllowance' => 0,
        'communicationAllowance' => 0,
        'otherAllowances' => 0
    ];
}

// NOTE: Bonus/Commission are now stored in user_details table
// They are accessed via $employeeDetails->bonusEligible, $employeeDetails->commissionEligible, $employeeDetails->commissionRate

// Get salary history
$salaryHistory = EmployeeProfile::get_salary_history(['employeeID' => $employeeID], false, $DBConn);

// Get pay grades for dropdown
$payGrades = Data::pay_grades(['Suspended' => 'N'], false, $DBConn);

// ========================================
// DYNAMIC ALLOWANCES CALCULATION (NEW)
// ========================================
// Get dynamic allowances from salary components
$employeeComponents = Data::employee_salary_components_detailed($employeeID, $DBConn);
$totalDynamicAllowances = 0;
$staticAllowancesTotal = 0;

if ($employeeComponents) {
    foreach ($employeeComponents as $component) {
        if ($component->categoryType == 'allowance' && $component->isActive == 'Y' && $component->isCurrent == 'Y') {
            $amount = 0;
            if ($component->valueType == 'fixed') {
                $amount = $component->componentValue;
            } elseif ($component->valueType == 'percentage') {
                // For percentage, we need to calculate based on what it applies to
                // IMPORTANT: Use $salaryBase instead of $base to avoid overwriting global $base variable
                $salaryBase = 0;
                if ($component->applyTo == 'basic_salary') {
                    $salaryBase = ($employeeDetails->basicSalary ?? 0);
                } elseif ($component->applyTo == 'gross_salary') {
                    // For gross salary percentage, we'll need to calculate iteratively
                    // For now, use basic salary as approximation
                    $salaryBase = ($employeeDetails->basicSalary ?? 0);
                }
                $amount = ($salaryBase * $component->componentValue) / 100;
            }
            $totalDynamicAllowances += $amount;
        }
    }
}

// Calculate static allowances total (legacy)
$staticAllowancesTotal = ($allowances->housingAllowance ?? 0) +
                         ($allowances->transportAllowance ?? 0) +
                         ($allowances->medicalAllowance ?? 0) +
                         ($allowances->communicationAllowance ?? 0) +
                         ($allowances->otherAllowances ?? 0);

// Calculate gross salary - use dynamic allowances if available, otherwise use static
$totalAllowancesForGross = $totalDynamicAllowances > 0 ? $totalDynamicAllowances : $staticAllowancesTotal;
$grossSalary = ($employeeDetails->basicSalary ?? 0) + $totalAllowancesForGross;
?>

<!-- Tab Header -->
<div class="tab-header">
    <div class="d-flex justify-content-between align-items-start">
        <div class="flex-grow-1">
            <h5 class="mb-1"><i class="ri-money-dollar-circle-line me-2"></i>Compensation & Benefits</h5>
            <p class="text-muted small mb-0">Salary, allowances, and compensation history</p>
        </div>
    </div>
</div>

<div class="row">
    <!-- ========================================
         SECTION 1: BASIC SALARY & PAY
         ======================================== -->
    <div class="col-md-6">
        <!-- VIEW MODE: Basic Salary -->
        <div id="basicSalaryViewMode">
            <div class="info-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Basic Salary & Pay</h6>
                    <?php if ($canEdit): ?>
                    <button class="btn btn-sm btn-outline-primary" id="editBasicSalaryBtn">
                        <i class="ri-edit-line me-1"></i> Edit
                    </button>
                    <?php endif; ?>
                </div>

                <div class="data-row">
                    <span class="data-label">Basic Salary:</span>
                    <span class="data-value fw-bold text-primary" style="font-size: 1.2em;">
                        KES <?= number_format($employeeDetails->basicSalary ?? 0, 2) ?>
                    </span>
                </div>

                <div class="data-row">
                    <span class="data-label">Pay Grade:</span>
                    <span class="data-value"><?= htmlspecialchars($employeeDetails->payGrade ?? 'Not assigned') ?></span>
                </div>

                <div class="data-row">
                    <span class="data-label">Cost Per Hour:</span>
                    <span class="data-value">KES <?= number_format($employeeDetails->costPerHour ?? 0, 2) ?></span>
                </div>

                <div class="data-row">
                    <span class="data-label">Overtime Allowed:</span>
                    <span class="data-value">
                        <?= ($employeeDetails->overtimeAllowed ?? 'N') == 'Y'
                            ? '<span class="badge bg-success">Yes</span>'
                            : '<span class="badge bg-secondary">No</span>' ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- EDIT MODE: Basic Salary -->
        <?php if ($canEdit): ?>
        <div id="basicSalaryEditMode" class="d-none">
            <div class="info-card edit-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-primary">Edit Basic Salary & Pay</h6>
                    <button class="btn btn-sm btn-secondary" id="cancelBasicSalaryBtn">
                        <i class="ri-close-line"></i>
                    </button>
                </div>

                <form id="basicSalaryForm" action="<?= $base ?>php/scripts/global/admin/manage_users.php" method="post">
                    <input type="hidden" name="ID" value="<?= $employeeID ?>">
                    <input type="hidden" name="redirectUrl" value="<?= "?s={$s}&p={$p}&uid={$employeeID}&tab=compensation" ?>">
                    <input type="hidden" name="updateType" value="basicSalary">
                    <input type="hidden" name="organisationID" value="<?= $employeeDetails->orgDataID ?? '' ?>">
                    <input type="hidden" name="entityID" value="<?= $employeeDetails->entityID ?? '' ?>">

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Basic Salary (KES) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-sm" name="basicSalary"
                               value="<?= $employeeDetails->basicSalary ?? 0 ?>"
                               min="0" step="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pay Grade</label>
                        <select class="form-control form-control-sm" name="payGradeID" id="payGradeSelect">
                            <option value="">Select Pay Grade</option>
                            <?php if (!empty($payGrades)): foreach($payGrades as $grade): ?>
                            <option value="<?= $grade->payGradeID ?>"
                                    data-min="<?= $grade->minSalary ?>"
                                    data-mid="<?= $grade->midSalary ?>"
                                    data-max="<?= $grade->maxSalary ?>"
                                    <?= ($employeeDetails->payGradeID == $grade->payGradeID) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($grade->payGradeCode) ?> - <?= htmlspecialchars($grade->payGradeName) ?>
                                (KES <?= number_format($grade->minSalary, 0) ?> - <?= number_format($grade->maxSalary, 0) ?>)
                            </option>
                            <?php endforeach; endif; ?>
                        </select>
                        <small class="text-muted">Select a pay grade to see salary range</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Cost Per Hour (KES)</label>
                        <input type="number" class="form-control form-control-sm bg-light"
                               name="costPerHour" id="costPerHourInput"
                               value="<?= $employeeDetails->costPerHour ?? 0 ?>"
                               min="0" step="0.01" readonly>
                        <small class="text-muted">
                            <i class="ri-calculator-line me-1"></i>
                            Auto-calculated: Basic Salary ÷ 168 hours (21 days × 8 hours/day)
                        </small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="overtimeAllowed"
                                   name="overtimeAllowed" value="Y"
                                   <?= ($employeeDetails->overtimeAllowed ?? 'N') == 'Y' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="overtimeAllowed">
                                Employee is eligible for overtime
                            </label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="ri-save-line me-1"></i> Save Changes
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm cancelBasicSalaryBtn">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Bonus & Commission (View Mode) -->
        <div id="bonusCommissionViewMode" class="mt-3">
            <div class="info-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Bonus & Commission</h6>
                    <?php if ($canEdit): ?>
                    <button class="btn btn-sm btn-outline-primary" id="editBonusCommissionBtn">
                        <i class="ri-edit-line me-1"></i> Edit
                    </button>
                    <?php endif; ?>
                </div>

                <?php
                // Read bonus/commission from user_details (NOT allowances)
                $bonusEligible = isset($employeeDetails->bonusEligible) ? $employeeDetails->bonusEligible : 'N';
                $commissionEligible = isset($employeeDetails->commissionEligible) ? $employeeDetails->commissionEligible : 'N';
                $commissionRate = isset($employeeDetails->commissionRate) ? $employeeDetails->commissionRate : 0;
                ?>

                <div class="data-row">
                    <span class="data-label">Bonus Eligible:</span>
                    <span class="data-value">
                        <?= $bonusEligible == 'Y'
                            ? '<span class="badge bg-success"><i class="ri-checkbox-circle-line me-1"></i>Yes</span>'
                            : '<span class="badge bg-secondary"><i class="ri-close-circle-line me-1"></i>No</span>' ?>
                    </span>
                </div>

                <div class="data-row">
                    <span class="data-label">Commission Eligible:</span>
                    <span class="data-value">
                        <?= $commissionEligible == 'Y'
                            ? '<span class="badge bg-success"><i class="ri-checkbox-circle-line me-1"></i>Yes</span>'
                            : '<span class="badge bg-secondary"><i class="ri-close-circle-line me-1"></i>No</span>' ?>
                    </span>
                </div>

                <div class="data-row">
                    <span class="data-label">Commission Rate:</span>
                    <span class="data-value">
                        <strong><?= number_format($commissionRate, 2) ?>%</strong>
                        <?php if ($commissionRate > 0 && $commissionEligible == 'Y'): ?>
                        <small class="text-success ms-2">✓ Active</small>
                        <?php elseif ($commissionRate > 0 && $commissionEligible == 'N'): ?>
                        <small class="text-warning ms-2">⚠ Inactive</small>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Bonus & Commission (Edit Mode) -->
        <?php if ($canEdit): ?>
        <div id="bonusCommissionEditMode" class="mt-3 d-none">
            <div class="info-card edit-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-primary">Edit Bonus & Commission</h6>
                    <button class="btn btn-sm btn-secondary" id="cancelBonusCommissionBtn">
                        <i class="ri-close-line"></i>
                    </button>
                </div>

                <form id="bonusCommissionForm" action="<?= $base ?>php/scripts/global/admin/manage_users.php" method="post">
                    <input type="hidden" name="ID" value="<?= $employeeID ?>">
                    <input type="hidden" name="redirectUrl" value="<?= "?s={$s}&p={$p}&uid={$employeeID}&tab=compensation&refresh=" . time() ?>">
                    <input type="hidden" name="updateType" value="bonusCommission">

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="bonusEligible"
                                   name="bonusEligible" value="Y"
                                   <?= (isset($employeeDetails->bonusEligible) && $employeeDetails->bonusEligible == 'Y') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="bonusEligible">
                                <i class="ri-gift-line me-1"></i>Eligible for performance bonuses
                            </label>
                        </div>
                        <small class="text-muted ms-4">Employee can receive discretionary bonuses</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="commissionEligible"
                                   name="commissionEligible" value="Y"
                                   <?= (isset($employeeDetails->commissionEligible) && $employeeDetails->commissionEligible == 'Y') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="commissionEligible">
                                <i class="ri-percent-line me-1"></i>Eligible for sales commission
                            </label>
                        </div>
                        <small class="text-muted ms-4">Employee earns commission on sales</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Commission Rate (%)</label>
                        <div class="input-group">
                            <input type="number" class="form-control form-control-sm"
                                   name="commissionRate" id="commissionRateInput"
                                   value="<?= isset($employeeDetails->commissionRate) ? $employeeDetails->commissionRate : 0 ?>"
                                   min="0" max="100" step="0.01">
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">Percentage of sales value earned as commission</small>
                    </div>

                    <!-- Debug Info (remove after testing) -->
                    <?php if (isset($_GET['debug'])): ?>
                    <div class="alert alert-info small">
                        <strong>Debug Info (from user_details):</strong><br>
                        Bonus Eligible: <?= var_export($employeeDetails->bonusEligible ?? 'NOT SET', true) ?><br>
                        Commission Eligible: <?= var_export($employeeDetails->commissionEligible ?? 'NOT SET', true) ?><br>
                        Commission Rate: <?= var_export($employeeDetails->commissionRate ?? 'NOT SET', true) ?><br>
                        Employee ID: <?= var_export($employeeDetails->ID ?? 'NOT SET', true) ?>
                    </div>
                    <?php endif; ?>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="ri-save-line me-1"></i> Save Changes
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm cancelBonusCommissionBtn">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ========================================
         SECTION 2: DYNAMIC ALLOWANCES (COMPONENT-BASED)
         ======================================== -->
    <?php include __DIR__ . '/compensation_tab_allowances_dynamic.php'; ?>
</div>

<!-- Salary History -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="info-card">
            <h6 class="fw-bold mb-3">Salary History</h6>

            <?php if ($salaryHistory && count($salaryHistory) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Effective Date</th>
                            <th>Previous Salary</th>
                            <th>New Salary</th>
                            <th>Change %</th>
                            <th>Reason</th>
                            <th>Approved By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($salaryHistory as $history): ?>
                        <tr>
                            <td><?= date('M j, Y', strtotime($history->effectiveDate)) ?></td>
                            <td>KES <?= number_format($history->oldBasicSalary, 2) ?></td>
                            <td>KES <?= number_format($history->newBasicSalary, 2) ?></td>
                            <td>
                                <span class="badge bg-<?= $history->changePercentage > 0 ? 'success' : 'danger' ?>">
                                    <?= $history->changePercentage > 0 ? '+' : '' ?><?= number_format($history->changePercentage, 2) ?>%
                                </span>
                            </td>
                            <td><?= htmlspecialchars($history->changeReason ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($history->approvedByName ?? 'System') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="alert alert-info">
                <i class="ri-information-line me-2"></i>
                No salary history recorded yet.
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- JavaScript for Separate Edit Sections -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ==================================================
    // BASIC SALARY SECTION
    // ==================================================
    const editBasicSalaryBtn = document.getElementById('editBasicSalaryBtn');
    const cancelBasicSalaryBtns = document.querySelectorAll('.cancelBasicSalaryBtn, #cancelBasicSalaryBtn');
    const basicSalaryViewMode = document.getElementById('basicSalaryViewMode');
    const basicSalaryEditMode = document.getElementById('basicSalaryEditMode');
    const basicSalaryForm = document.getElementById('basicSalaryForm');

    // Toggle Basic Salary Edit
    if (editBasicSalaryBtn) {
        editBasicSalaryBtn.addEventListener('click', function() {
            toggleSection(basicSalaryViewMode, basicSalaryEditMode);
        });
    }

    cancelBasicSalaryBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            toggleSection(basicSalaryEditMode, basicSalaryViewMode);
        });
    });

    // Basic Salary Form Submit
    if (basicSalaryForm) {
        basicSalaryForm.addEventListener('submit', function(e) {
            if (!validateSalaryRange()) {
                e.preventDefault();
                showToast('Please ensure salary is within the selected pay grade range', 'danger');
                return false;
            }

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = '<i class="ri-save-line me-1"></i> Save Changes';

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="spinner-border spinner-border-sm me-1"></i> Saving...';
                this.classList.add('submitting');

                // Set timeout to re-enable button if form doesn't redirect (error occurred)
                setTimeout(() => {
                    if (submitBtn.disabled) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                        this.classList.remove('submitting');
                        showToast('Request timed out. Please check your connection and try again.', 'danger');
                    }
                }, 30000); // 30 second timeout
            }

            // Store original button text for error handling
            this.setAttribute('data-original-btn-text', originalBtnText);
        });

        // Handle form errors (if validation fails server-side)
        window.addEventListener('pageshow', function(event) {
            // Reset form if coming back to page (e.g., after error)
            const submitBtn = basicSalaryForm.querySelector('button[type="submit"]');
            if (submitBtn && submitBtn.disabled) {
                const originalBtnText = basicSalaryForm.getAttribute('data-original-btn-text') || '<i class="ri-save-line me-1"></i> Save Changes';
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                basicSalaryForm.classList.remove('submitting');
            }
        });
    }

    // Pay Grade Selection
    const payGradeSelect = document.getElementById('payGradeSelect');
    const basicSalaryInput = document.querySelector('#basicSalaryEditMode input[name="basicSalary"]');

    if (payGradeSelect) {
        payGradeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const minSalary = selectedOption.getAttribute('data-min');
            const midSalary = selectedOption.getAttribute('data-mid');
            const maxSalary = selectedOption.getAttribute('data-max');

            if (basicSalaryInput) {
                basicSalaryInput.setAttribute('data-min-salary', minSalary || '0');
                basicSalaryInput.setAttribute('data-max-salary', maxSalary || '999999999');
            }

            let helperText = this.parentElement.querySelector('.salary-range-helper');
            if (!helperText) {
                helperText = document.createElement('div');
                helperText.className = 'salary-range-helper alert alert-info mt-2 py-2';
                this.parentElement.appendChild(helperText);
            }

            if (minSalary && maxSalary) {
                helperText.innerHTML = `
                    <small>
                        <i class="ri-information-line me-1"></i>
                        <strong>Range:</strong> KES ${parseFloat(minSalary).toLocaleString()} - ${parseFloat(maxSalary).toLocaleString()}
                        (Midpoint: ${parseFloat(midSalary).toLocaleString()})
                    </small>
                `;
                helperText.style.display = 'block';
            } else {
                helperText.style.display = 'none';
            }
        });
    }

    if (basicSalaryInput) {
        basicSalaryInput.addEventListener('input', function() {
            validateSalaryRange();
            calculateCostPerHour();
        });

        // Calculate on page load
        calculateCostPerHour();
    }

    // ==================================================
    // BONUS & COMMISSION SECTION
    // ==================================================
    const editBonusCommissionBtn = document.getElementById('editBonusCommissionBtn');
    const cancelBonusCommissionBtns = document.querySelectorAll('.cancelBonusCommissionBtn, #cancelBonusCommissionBtn');
    const bonusCommissionViewMode = document.getElementById('bonusCommissionViewMode');
    const bonusCommissionEditMode = document.getElementById('bonusCommissionEditMode');
    const bonusCommissionForm = document.getElementById('bonusCommissionForm');

    if (editBonusCommissionBtn) {
        editBonusCommissionBtn.addEventListener('click', function() {
            toggleSection(bonusCommissionViewMode, bonusCommissionEditMode);
        });
    }

    cancelBonusCommissionBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            toggleSection(bonusCommissionEditMode, bonusCommissionViewMode);
        });
    });

    if (bonusCommissionForm) {
        bonusCommissionForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = '<i class="ri-save-line me-1"></i> Save Changes';

            // Log what's being submitted (for debugging)
            const bonusCheckbox = document.getElementById('bonusEligible');
            const commissionCheckbox = document.getElementById('commissionEligible');
            const rateInput = document.getElementById('commissionRateInput');

            console.log('Submitting Bonus & Commission:', {
                bonusEligible: bonusCheckbox ? (bonusCheckbox.checked ? 'Y' : 'N') : 'NOT FOUND',
                commissionEligible: commissionCheckbox ? (commissionCheckbox.checked ? 'Y' : 'N') : 'NOT FOUND',
                commissionRate: rateInput ? rateInput.value : 'NOT FOUND'
            });

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="spinner-border spinner-border-sm me-1"></i> Saving...';
                this.classList.add('submitting');

                // Set timeout to re-enable button if form doesn't redirect
                setTimeout(() => {
                    if (submitBtn.disabled) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                        this.classList.remove('submitting');
                        showToast('Request timed out. Please check your connection and try again.', 'danger');
                    }
                }, 30000);
            }

            this.setAttribute('data-original-btn-text', originalBtnText);
        });

        window.addEventListener('pageshow', function(event) {
            const submitBtn = bonusCommissionForm.querySelector('button[type="submit"]');
            if (submitBtn && submitBtn.disabled) {
                const originalBtnText = bonusCommissionForm.getAttribute('data-original-btn-text') || '<i class="ri-save-line me-1"></i> Save Changes';
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                bonusCommissionForm.classList.remove('submitting');
            }
        });
    }

    // ==================================================
    // ALLOWANCES SECTION
    // ==================================================
    const editAllowancesBtn = document.getElementById('editAllowancesBtn');
    const cancelAllowancesBtns = document.querySelectorAll('.cancelAllowancesBtn, #cancelAllowancesBtn');
    const allowancesViewMode = document.getElementById('allowancesViewMode');
    const allowancesEditMode = document.getElementById('allowancesEditMode');
    const allowancesForm = document.getElementById('allowancesForm');

    if (editAllowancesBtn) {
        editAllowancesBtn.addEventListener('click', function() {
            toggleSection(allowancesViewMode, allowancesEditMode);
            updateTotalAllowances();
        });
    }

    cancelAllowancesBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            toggleSection(allowancesEditMode, allowancesViewMode);
        });
    });

    // Real-time Total Calculation
    const allowanceInputs = document.querySelectorAll('.allowance-input');
    allowanceInputs.forEach(input => {
        input.addEventListener('input', updateTotalAllowances);
    });

    if (allowancesForm) {
        allowancesForm.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = '<i class="ri-save-line me-1"></i> Save Changes';

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="spinner-border spinner-border-sm me-1"></i> Saving...';
                this.classList.add('submitting');

                // Set timeout to re-enable button if form doesn't redirect
                setTimeout(() => {
                    if (submitBtn.disabled) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                        this.classList.remove('submitting');
                        showToast('Request timed out. Please check your connection and try again.', 'danger');
                    }
                }, 30000);
            }

            this.setAttribute('data-original-btn-text', originalBtnText);
        });

        window.addEventListener('pageshow', function(event) {
            const submitBtn = allowancesForm.querySelector('button[type="submit"]');
            if (submitBtn && submitBtn.disabled) {
                const originalBtnText = allowancesForm.getAttribute('data-original-btn-text') || '<i class="ri-save-line me-1"></i> Save Changes';
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                allowancesForm.classList.remove('submitting');
            }
        });
    }
});

// ==================================================
// HELPER FUNCTIONS
// ==================================================

// Global error handler for form submissions
function resetFormButton(form) {
    if (!form) return;

    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = form.getAttribute('data-original-btn-text') || '<i class="ri-save-line me-1"></i> Save Changes';

    if (submitBtn && submitBtn.disabled) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
        form.classList.remove('submitting');
    }
}

// Reset all form buttons on page visibility change (handles errors)
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible') {
        // Reset all forms when page becomes visible
        const forms = [
            document.getElementById('basicSalaryForm'),
            document.getElementById('bonusCommissionForm'),
            document.getElementById('allowancesForm')
        ];

        forms.forEach(form => {
            if (form) {
                resetFormButton(form);
            }
        });
    }
});

// Handle browser back/forward button
window.addEventListener('popstate', function() {
    const forms = [
        document.getElementById('basicSalaryForm'),
        document.getElementById('bonusCommissionForm'),
        document.getElementById('allowancesForm')
    ];

    forms.forEach(form => {
        if (form) {
            resetFormButton(form);
        }
    });
});

// Handle page unload errors
window.addEventListener('beforeunload', function() {
    // Clear any pending timeouts
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn && submitBtn.disabled) {
            // Form is submitting, allow it
            return;
        }
    });
});

// Toggle between view and edit modes
function toggleSection(hideElement, showElement) {
    if (hideElement) {
        hideElement.classList.add('d-none');
    }

    if (showElement) {
        showElement.classList.remove('d-none');
        showElement.style.opacity = '0';
        setTimeout(() => {
            showElement.style.transition = 'opacity 0.3s ease-in';
            showElement.style.opacity = '1';
        }, 10);

        // Scroll to element
        showElement.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

// Validate salary within pay grade range
function validateSalaryRange() {
    const basicSalaryInput = document.querySelector('#basicSalaryEditMode input[name="basicSalary"]');
    const payGradeSelect = document.getElementById('payGradeSelect');

    if (!basicSalaryInput || !payGradeSelect) return true;

    const salary = parseFloat(basicSalaryInput.value);
    const minSalary = parseFloat(basicSalaryInput.getAttribute('data-min-salary') || 0);
    const maxSalary = parseFloat(basicSalaryInput.getAttribute('data-max-salary') || 999999999);

    if (payGradeSelect.value && salary > 0) {
        let warningDiv = basicSalaryInput.parentElement.querySelector('.salary-warning');

        if (salary < minSalary || salary > maxSalary) {
            basicSalaryInput.classList.add('is-invalid');
            basicSalaryInput.classList.remove('is-valid');

            if (!warningDiv) {
                warningDiv = document.createElement('div');
                warningDiv.className = 'salary-warning alert alert-warning mt-2 py-2';
                basicSalaryInput.parentElement.appendChild(warningDiv);
            }

            warningDiv.innerHTML = `
                <small>
                    <i class="ri-alert-line me-1"></i>
                    Salary is ${salary < minSalary ? 'below minimum' : 'above maximum'}
                    for selected pay grade
                </small>
            `;
            warningDiv.style.display = 'block';
            return false;
        } else {
            basicSalaryInput.classList.remove('is-invalid');
            basicSalaryInput.classList.add('is-valid');
            if (warningDiv) warningDiv.style.display = 'none';
            return true;
        }
    }

    basicSalaryInput.classList.remove('is-invalid', 'is-valid');
    return true;
}

// Calculate cost per hour based on basic salary
function calculateCostPerHour() {
    const basicSalaryInput = document.querySelector('#basicSalaryEditMode input[name="basicSalary"]');
    const costPerHourInput = document.getElementById('costPerHourInput');

    if (!basicSalaryInput || !costPerHourInput) return;

    const basicSalary = parseFloat(basicSalaryInput.value) || 0;

    // Calculate: Basic Salary / (21 working days × 8 hours per day) = Basic Salary / 168 hours
    const workingHoursPerMonth = 21 * 8; // 168 hours
    const costPerHour = basicSalary / workingHoursPerMonth;

    // Update the field
    costPerHourInput.value = costPerHour.toFixed(2);

    // Add visual feedback
    if (costPerHour > 0) {
        costPerHourInput.classList.remove('bg-light');
        costPerHourInput.classList.add('bg-success-light');

        // Remove highlight after 1 second
        setTimeout(() => {
            costPerHourInput.classList.remove('bg-success-light');
            costPerHourInput.classList.add('bg-light');
        }, 1000);
    }
}

// Update total allowances display
function updateTotalAllowances() {
    const allowanceInputs = document.querySelectorAll('.allowance-input');
    let total = 0;

    allowanceInputs.forEach(input => {
        const value = parseFloat(input.value) || 0;
        total += value;
    });

    const displayElement = document.getElementById('totalAllowancesDisplay');
    if (displayElement) {
        displayElement.textContent = 'KES ' + total.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
}

// Toast notification
function showToast(message, type = 'info') {
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }

    const iconMap = {
        'danger': 'ri-error-warning-line',
        'warning': 'ri-alert-line',
        'success': 'ri-checkbox-circle-line',
        'info': 'ri-information-line'
    };

    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');

    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="${iconMap[type]} me-2"></i>${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

    toastContainer.appendChild(toast);

    const bsToast = new bootstrap.Toast(toast, { autohide: true, delay: 5000 });
    bsToast.show();

    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}
</script>

<!-- Styling -->
<style>
/* Tab Header */
.tab-header {
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 15px;
    margin-bottom: 25px;
}

.tab-header h5 {
    color: #007bff;
    font-weight: 600;
}

/* Info Cards */
.info-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
    transition: all 0.3s ease;
}

.info-card.edit-card {
    background: #f8f9fa;
    border-left: 4px solid #007bff;
}

/* Data Rows */
.data-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.data-row:last-child {
    border-bottom: none;
}

.data-label {
    font-weight: 600;
    color: #495057;
    min-width: 180px;
}

.data-value {
    color: #6c757d;
    text-align: right;
    flex: 1;
}

/* Forms */
.form-control-sm:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.15);
}

.form-label.fw-bold {
    color: #495057;
    margin-bottom: 0.5rem;
}

/* Validation */
.is-invalid {
    border-color: #dc3545 !important;
}

.is-valid {
    border-color: #28a745 !important;
}

/* Cost Per Hour Animation */
.bg-success-light {
    background-color: #d4edda !important;
    transition: background-color 0.5s ease;
}

/* Readonly field styling */
input[readonly] {
    background-color: #e9ecef !important;
    cursor: not-allowed;
}

input[readonly]:focus {
    box-shadow: none !important;
    border-color: #ced4da !important;
}

/* Loading State Styling */
button[type="submit"]:disabled {
    opacity: 0.7;
    cursor: not-allowed !important;
    pointer-events: none;
}

button[type="submit"]:disabled .spinner-border {
    animation: spinner-border 0.75s linear infinite;
}

@keyframes spinner-border {
    to { transform: rotate(360deg); }
}

/* Form disabled state overlay */
form.submitting {
    position: relative;
    pointer-events: none;
    opacity: 0.6;
}

form.submitting::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.5);
    z-index: 10;
}

/* Responsive */
@media (max-width: 768px) {
    .data-row {
        flex-direction: column;
        gap: 5px;
    }

    .data-label, .data-value {
        text-align: left;
    }
}
</style>

