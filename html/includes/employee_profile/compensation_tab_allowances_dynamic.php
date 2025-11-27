<?php
/**
 * Dynamic Allowances Section for Compensation Tab
 * Integrates with Salary Components system
 * Replaces static allowance fields with dynamic component-based system
 */

// Get available allowance components from salary components system
$availableAllowances = Data::salary_components_with_category([
    'entityID' => $employeeDetails->entityID,

    'Suspended' => 'N'
], false, $DBConn);

/**
 * filter out the allowance and benefits from the available allowances
 * and store them in a variable called $availableAllowances
 * with category code ALLOWANCES or ALLOWANCE or BENEF
 */
if ($availableAllowances && is_array($availableAllowances)) {
    $availableAllowances = array_filter($availableAllowances, function($allowance) {
        return $allowance->categoryCode == 'ALLOWANCES' || $allowance->categoryCode == 'ALLOWANCE' || $allowance->categoryCode == 'BENEF';
    });
} else {
    $availableAllowances = array();
}

// var_dump($availableAllowances);
// Get employee's currently assigned allowances
$employeeAllowances = Data::employee_salary_components_with_component_details(["employeeID" => $employeeID], false, $DBConn);
// var_dump($employeeAllowances);
// Filter to get only allowances
$assignedAllowances = [];
if ($employeeAllowances) {
    foreach ($employeeAllowances as $component) {
        if ($component->categoryCode == 'ALLOWANCES') {
            $assignedAllowances[] = $component;
        }
    }
}

// Calculate total allowances
$totalAllowances = 0;
if ($assignedAllowances) {
    foreach ($assignedAllowances as $allowance) {
        $amount = 0;
        if ($allowance->valueType == 'fixed') {
            $amount = $allowance->componentValue;
        } elseif ($allowance->valueType == 'percentage') {
            // IMPORTANT: Use $calculationBase instead of $base to avoid overwriting global $base variable
            $calculationBase = ($allowance->applyTo == 'basic_salary') ? ($employeeDetails->basicSalary ?? 0) : $grossSalary;
            $amount = ($calculationBase * $allowance->componentValue) / 100;
        }
        $totalAllowances += $amount;
    }
}

// Recalculate gross salary with dynamic allowances
$grossSalaryDynamic = ($employeeDetails->basicSalary ?? 0) + $totalAllowances;

// Debug: Ensure no accidental output
// Do NOT echo or print anything here
?>

<!-- ========================================
     SECTION 2: DYNAMIC ALLOWANCES (Component-Based)
     ======================================== -->
<div class="col-md-6">
    <!-- VIEW MODE: Dynamic Allowances -->
    <div id="allowancesViewMode">
        <div class="info-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">
                    <i class="ri-gift-line me-2"></i>Monthly Allowances & Benefits
                </h6>
                <?php if ($canEdit): ?>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-sm btn-outline-success"
                            data-bs-toggle="modal"
                            data-bs-target="#allowanceAssignmentModal"
                            onclick="prepareAddAllowanceModal()">
                        <i class="ri-add-line me-1"></i> Add
                    </button>
                    <button class="btn btn-sm btn-outline-primary" onclick="manageAllowanceComponents()">
                        <i class="ri-settings-3-line"></i> Manage Components
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($assignedAllowances && count($assignedAllowances) > 0): ?>
            <!-- List of assigned allowances -->
            <div class="allowances-list">
                <?php foreach ($assignedAllowances as $allowance): ?>
                    <?php
                    // Calculate amount
                    $amount = 0;
                    $displayValue = '';
                    if ($allowance->valueType == 'fixed') {
                        $amount = $allowance->componentValue;
                        $displayValue = 'KES ' . number_format($amount, 2);
                    } elseif ($allowance->valueType == 'percentage') {
                        // IMPORTANT: Use $calculationBase to avoid overwriting global $base variable
                        $calculationBase = ($allowance->applyTo == 'basic_salary') ? ($employeeDetails->basicSalary ?? 0) : $grossSalaryDynamic;
                        $amount = ($calculationBase * $allowance->componentValue) / 100;
                        $displayValue = number_format($allowance->componentValue, 2) . '% = KES ' . number_format($amount, 2);
                    }
                    ?>
                    <div class="allowance-item data-row" data-component-id="<?= $allowance->salaryComponentID ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center">
                                    <span class="data-label">
                                        <?= htmlspecialchars($allowance->salaryComponentTitle) ?>
                                    </span>
                                    <?php if ($allowance->isStatutory == 'Y'): ?>
                                    <span class="badge bg-warning-transparent ms-2" title="Statutory Allowance">
                                        <i class="ri-government-line"></i>
                                    </span>
                                    <?php endif; ?>
                                    <?php if ($allowance->isTaxable == 'Y'): ?>
                                    <span class="badge bg-secondary-transparent ms-1" title="Taxable">
                                        <i class="ri-money-dollar-line"></i>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($allowance->salaryComponentDescription): ?>
                                <small class="text-muted d-block"><?= htmlspecialchars($allowance->salaryComponentDescription) ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="data-value fw-bold"><?= $displayValue ?></span>
                                <?php if ($canEdit): ?>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-sm btn-icon btn-primary-light"
                                            data-bs-toggle="modal"
                                            data-bs-target="#allowanceAssignmentModal"
                                            onclick="editAllowanceAssignment(<?= $allowance->employeeComponentID ?>)"
                                            title="Edit">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    <button class="btn btn-sm btn-icon btn-danger-light"
                                            onclick="removeAllowanceAssignment(<?= $allowance->employeeComponentID ?>)"
                                            title="Remove">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <hr>

            <div class="data-row">
                <span class="data-label fw-bold">Total Allowances:</span>
                <span class="data-value fw-bold text-info">
                    KES <?= number_format($totalAllowances, 2) ?>
                </span>
            </div>

            <div class="data-row">
                <span class="data-label fw-bold">Gross Salary:</span>
                <span class="data-value fw-bold text-success" style="font-size: 1.2em;">
                    KES <?= number_format($grossSalaryDynamic, 2) ?>
                </span>
            </div>

            <?php else: ?>
            <div class="text-center py-4">
                <i class="ri-gift-line fs-48 text-muted mb-2"></i>
                <p class="text-muted mb-3">No allowances assigned yet</p>
                <?php if ($canEdit): ?>
                <button class="btn btn-sm btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#allowanceAssignmentModal"
                        onclick="prepareAddAllowanceModal()">
                    <i class="ri-add-line me-1"></i> Add First Allowance
                </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if (!$availableAllowances || count($availableAllowances) == 0): ?>
            <div class="alert alert-warning mt-3">
                <i class="ri-alert-line me-2"></i>
                <strong>No allowance components available.</strong><br>
                Please configure allowance components in the <a href="<?= $base ?>html/?s=core&ss=admin&p=jobs&view=salary_components" class="alert-link">Salary Components</a> section first.
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add/Edit Allowance Modal -->
<div class="modal fade" id="allowanceAssignmentModal" tabindex="-1" aria-labelledby="allowanceAssignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="allowanceAssignmentModalLabel">Assign Allowance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="allowanceAssignmentForm" onsubmit="saveAllowanceAssignment(event)">
                <div class="modal-body">
                    <input type="hidden" id="employeeComponentID" name="employeeComponentID">
                    <input type="hidden" name="employeeID" value="<?= $employeeID ?>">

                    <div class="mb-3">
                        <label for="salaryComponentID" class="form-label">Allowance Component <span class="text-danger">*</span></label>
                        <select class="form-select" id="salaryComponentID" name="salaryComponentID" required onchange="handleComponentChange()">
                            <option value="">Select Allowance...</option>
                            <?php if ($availableAllowances): foreach ($availableAllowances as $component): ?>
                            <option value="<?= $component->salaryComponentID ?>"
                                    data-type="<?= $component->salaryComponentType ?>"
                                    data-value-type="<?= $component->salaryComponentValueType ?>"
                                    data-default-value="<?= $component->defaultValue ?>"
                                    data-apply-to="<?= $component->applyTo ?>"
                                    data-description="<?= htmlspecialchars($component->salaryComponentDescription ?? '') ?>">
                                <?= htmlspecialchars($component->salaryComponentTitle) ?>
                                (<?= ucfirst($component->salaryComponentValueType) ?>)
                            </option>
                            <?php endforeach; endif; ?>
                        </select>
                        <small class="text-muted" id="componentDescription"></small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="componentValue" class="form-label">Value <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="componentValue" name="componentValue"
                                       min="0" step="0.01" required>
                                <span class="input-group-text" id="valueTypeIndicator">KES</span>
                            </div>
                            <small class="text-muted">Enter the allowance amount or percentage</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="effectiveDate" class="form-label">Effective Date <span class="text-danger">*</span></label>
                            <input type="text" class="form-control component-datepicker" id="effectiveDate" name="effectiveDate"
                                   value="<?= date('Y-m-d') ?>" placeholder="YYYY-MM-DD" required readonly>
                            <small class="text-muted">Select date when allowance becomes effective</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="frequency" class="form-label">Frequency</label>
                        <select class="form-select" id="frequency" name="frequency">
                            <option value="monthly">Monthly</option>
                            <option value="one-time">One Time</option>
                            <option value="annual">Annual</option>
                        </select>
                    </div>

                    <div class="mb-3" id="oneTimePayrollDateDiv" style="display: none;">
                        <label for="oneTimePayrollDate" class="form-label">Payroll Date (One Time)</label>
                        <input type="text" class="form-control component-datepicker" id="oneTimePayrollDate" name="oneTimePayrollDate" placeholder="YYYY-MM-DD" readonly>
                        <small class="text-muted">Select specific payroll date for one-time payment</small>
                    </div>

                    <div class="mb-3">
                        <label for="assignmentNotes" class="form-label">Notes</label>
                        <textarea class="form-control" id="assignmentNotes" name="notes" rows="2"
                                  placeholder="Optional notes about this allowance assignment"></textarea>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="isActive" name="isActive" value="Y" checked>
                        <label class="form-check-label" for="isActive">
                            Active (appears in payroll)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Save Allowance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Store available allowances data
const availableAllowancesData = <?= json_encode($availableAllowances ?: []) ?>;
const employeeID = <?= $employeeID ?>;

// Initialize flatpickr for date fields
function initializeAllowanceDatePickers() {
    if (typeof flatpickr !== 'undefined') {
        // Initialize effective date picker
        const effectiveDateInput = document.getElementById('effectiveDate');
        if (effectiveDateInput && !effectiveDateInput._flatpickr) {
            flatpickr(effectiveDateInput, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'F j, Y',
                defaultDate: new Date(),
                allowInput: true,
                clickOpens: true
            });
        }

        // Initialize one-time payroll date picker
        const oneTimeDateInput = document.getElementById('oneTimePayrollDate');
        if (oneTimeDateInput && !oneTimeDateInput._flatpickr) {
            flatpickr(oneTimeDateInput, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'F j, Y',
                allowInput: true,
                clickOpens: true,
                minDate: 'today'
            });
        }
    }
}

// Initialize on document ready and when modal is shown
document.addEventListener('DOMContentLoaded', function() {
    initializeAllowanceDatePickers();

    // Initialize datepickers when modal is shown
    const modalElement = document.getElementById('allowanceAssignmentModal');
    if (modalElement) {
        modalElement.addEventListener('shown.bs.modal', function() {
            initializeAllowanceDatePickers();
        });
    }
});

// Prepare modal for adding new allowance (called via onclick before modal opens)
function prepareAddAllowanceModal() {
    // Reset form
    const form = document.getElementById('allowanceAssignmentForm');
    if (form) {
        form.reset();
    }

    document.getElementById('employeeComponentID').value = '';
    document.getElementById('allowanceAssignmentModalLabel').textContent = 'Assign Allowance';

    // Set default date
    const effectiveDateInput = document.getElementById('effectiveDate');
    if (effectiveDateInput._flatpickr) {
        effectiveDateInput._flatpickr.setDate(new Date(), false);
    } else {
        effectiveDateInput.value = '<?= date('Y-m-d') ?>';
    }

    // Reset code input flags
    const codeInput = document.getElementById('componentCode');
    if (codeInput) {
        codeInput.dataset.manuallyEdited = 'false';
        codeInput.dataset.autoGenerated = 'false';
    }
}

// Legacy function for backward compatibility
function openAddAllowanceModal() {
    prepareAddAllowanceModal();
    // Modal will open via data-bs-toggle attribute
}

// Edit existing allowance assignment
function editAllowanceAssignment(employeeComponentID) {
    // Fetch assignment details
    fetch(`<?= $base ?>php/scripts/global/admin/salary_components_api.php?action=get_employee_component&id=${employeeComponentID}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const assignment = data.data;

                // Populate form
                document.getElementById('employeeComponentID').value = assignment.employeeComponentID;
                document.getElementById('salaryComponentID').value = assignment.salaryComponentID;
                document.getElementById('componentValue').value = assignment.componentValue;

                // Set effective date value (flatpickr will format it)
                const effectiveDateInput = document.getElementById('effectiveDate');
                if (effectiveDateInput._flatpickr) {
                    effectiveDateInput._flatpickr.setDate(assignment.effectiveDate, false);
                } else {
                    effectiveDateInput.value = assignment.effectiveDate;
                }

                document.getElementById('frequency').value = assignment.frequency || 'monthly';
                document.getElementById('assignmentNotes').value = assignment.notes || '';
                document.getElementById('isActive').checked = (assignment.isActive === 'Y');

                if (assignment.oneTimePayrollDate) {
                    const oneTimeDateInput = document.getElementById('oneTimePayrollDate');
                    if (oneTimeDateInput._flatpickr) {
                        oneTimeDateInput._flatpickr.setDate(assignment.oneTimePayrollDate, false);
                    } else {
                        oneTimeDateInput.value = assignment.oneTimePayrollDate;
                    }
                }

                handleComponentChange();

                document.getElementById('allowanceAssignmentModalLabel').textContent = 'Edit Allowance Assignment';

                // Modal is already opening via data-bs-toggle, just reinitialize datepickers
                // Reinitialize datepickers after modal opens
                setTimeout(initializeAllowanceDatePickers, 150);
            } else {
                showToast('Failed to load assignment: ' + (data.message || 'Unknown error'), 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while loading the assignment', 'danger');
        });
}

// Remove allowance assignment
function removeAllowanceAssignment(employeeComponentID) {
    if (!confirm('Are you sure you want to remove this allowance assignment?')) {
        return;
    }

    fetch(`<?= $base ?>php/scripts/global/admin/salary_components_api.php?action=remove_employee_component&id=${employeeComponentID}`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Allowance removed successfully', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast(data.message || 'Failed to remove allowance', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while removing the allowance', 'danger');
    });
}

// Handle component selection change
function handleComponentChange() {
    const select = document.getElementById('salaryComponentID');
    const selectedOption = select.options[select.selectedIndex];

    if (selectedOption.value) {
        const valueType = selectedOption.getAttribute('data-value-type');
        const defaultValue = selectedOption.getAttribute('data-default-value');
        const description = selectedOption.getAttribute('data-description');

        // Update value indicator
        const indicator = document.getElementById('valueTypeIndicator');
        indicator.textContent = (valueType === 'percentage') ? '%' : 'KES';

        // Set default value if not editing
        if (!document.getElementById('employeeComponentID').value) {
            document.getElementById('componentValue').value = defaultValue || 0;
        }

        // Show description
        document.getElementById('componentDescription').textContent = description || '';
    }
}

// Handle frequency change
document.getElementById('frequency')?.addEventListener('change', function() {
    const oneTimeDiv = document.getElementById('oneTimePayrollDateDiv');
    oneTimeDiv.style.display = (this.value === 'one-time') ? 'block' : 'none';
});

// Save allowance assignment
function saveAllowanceAssignment(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    formData.append('action', 'save_employee_component');

    const submitBtn = event.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i> Saving...';

    fetch('<?= $base ?>php/scripts/global/admin/salary_components_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Allowance saved successfully', 'success');

            // Close modal - try multiple methods for compatibility
            const modalElement = document.getElementById('allowanceAssignmentModal');

            // Method 1: Bootstrap 5 way
            if (typeof bootstrap !== 'undefined') {
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                } else {
                    // If instance doesn't exist, create and hide
                    const newModal = new bootstrap.Modal(modalElement);
                    newModal.hide();
                }
            }
            // Method 2: jQuery way (fallback)
            else if (typeof $ !== 'undefined' && $.fn.modal) {
                $(modalElement).modal('hide');
            }
            // Method 3: Manual hide (last resort)
            else {
                modalElement.classList.remove('show');
                modalElement.style.display = 'none';
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) backdrop.remove();
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('overflow');
                document.body.style.removeProperty('padding-right');
            }

            // Reload page after short delay
            setTimeout(() => {
                window.location.href = '?s=<?= $s ?>&p=<?= $p ?>&uid=<?= $employeeID ?>&tab=compensation';
            }, 1500);
        } else {
            showToast(data.message || 'Failed to save allowance', 'danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="ri-save-line me-1"></i> Save Allowance';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while saving the allowance', 'danger');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="ri-save-line me-1"></i> Save Allowance';
    });
}

// Navigate to salary components management
function manageAllowanceComponents() {
    window.location.href = '<?= $base ?>html/?s=core&ss=admin&p=jobs&view=salary_components';
}

// Show toast notification
function showToast(message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }

    // Icon mapping
    const iconMap = {
        'danger': 'ri-error-warning-line',
        'warning': 'ri-alert-line',
        'success': 'ri-checkbox-circle-line',
        'info': 'ri-information-line'
    };

    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');

    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="${iconMap[type] || 'ri-information-line'} me-2"></i>${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    toastContainer.appendChild(toast);

    // Initialize and show toast
    if (typeof bootstrap !== 'undefined') {
        const bsToast = new bootstrap.Toast(toast, {
            autohide: true,
            delay: 5000
        });
        bsToast.show();
    } else if (typeof $ !== 'undefined' && $.fn.toast) {
        $(toast).toast({
            autohide: true,
            delay: 5000
        });
        $(toast).toast('show');
    } else {
        // Fallback: show as visible element
        toast.style.display = 'block';
        setTimeout(() => {
            toast.remove();
        }, 5000);
    }

    // Remove toast after it's hidden
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}
</script>

<style>
.allowance-item {
    padding: 0.75rem;
    border-left: 3px solid #0d6efd;
    background: #f8f9fa;
    margin-bottom: 0.5rem;
    border-radius: 0.25rem;
}

.allowance-item:hover {
    background: #e9ecef;
}

.btn-icon {
    width: 28px;
    height: 28px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.bg-warning-transparent {
    background-color: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

.bg-secondary-transparent {
    background-color: rgba(108, 117, 125, 0.1);
    color: #6c757d;
}
</style>

