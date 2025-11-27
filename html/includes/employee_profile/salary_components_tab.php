<?php
/**
 * Employee Salary Components Tab
 * Displays and manages salary components assigned to an employee
 * Only visible to admins with permission
 */

if (!$canViewSalary) {
    echo '<div class="alert alert-warning">You do not have permission to view salary information.</div>';
    return;
}

// Get employee's current components
$employeeComponents = Data::employee_salary_components_detailed($employeeID, $DBConn);

// Get all available components for assignment
$allComponents = Data::salary_components_with_category([
    'entityID' => $employeeDetails->entityID,
    'Suspended' => 'N'
], false, $DBConn);

// Calculate totals
$totalEarnings = 0;
$totalDeductions = 0;

if ($employeeComponents) {
    foreach ($employeeComponents as $comp) {
        if ($comp->salaryComponentType == 'earning') {
            $totalEarnings += $comp->componentValue;
        } else {
            $totalDeductions += $comp->componentValue;
        }
    }
}
?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-light">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="ri-money-dollar-circle-line me-2"></i>Salary Components</h5>
            <?php if ($canEditSalary): ?>
            <button class="btn btn-primary btn-sm" onclick="openAssignComponentModal()">
                <i class="ri-add-line me-1"></i> Assign Component
            </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-success-transparent border-success">
                    <div class="card-body">
                        <p class="text-muted mb-1">Total Earnings/Allowances</p>
                        <h4 class="text-success mb-0">KES <?= number_format($totalEarnings, 2) ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger-transparent border-danger">
                    <div class="card-body">
                        <p class="text-muted mb-1">Total Deductions</p>
                        <h4 class="text-danger mb-0">KES <?= number_format($totalDeductions, 2) ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-primary-transparent border-primary">
                    <div class="card-body">
                        <p class="text-muted mb-1">Net Effect</p>
                        <h4 class="text-primary mb-0">
                            <?php
                            $netEffect = $totalEarnings - $totalDeductions;
                            echo ($netEffect >= 0 ? '+' : '') . 'KES ' . number_format($netEffect, 2);
                            ?>
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Earnings Section -->
        <h6 class="border-bottom pb-2 mb-3 text-success">
            <i class="ri-arrow-up-circle-line me-2"></i>Earnings & Allowances
        </h6>
        <?php if ($employeeComponents):
            $hasEarnings = false;
            foreach ($employeeComponents as $comp):
                if ($comp->salaryComponentType == 'earning'):
                    $hasEarnings = true;
                    $displayValue = $comp->valueType == 'percentage' ?
                        number_format($comp->componentValue, 2) . '%' :
                        'KES ' . number_format($comp->componentValue, 2);
        ?>
        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
            <div>
                <strong><?= htmlspecialchars($comp->salaryComponentTitle) ?></strong>
                <span class="badge bg-info-transparent ms-2"><?= htmlspecialchars($comp->salaryComponentCategoryTitle) ?></span>
                <?php if ($comp->isTaxable == 'Y'): ?>
                <span class="badge bg-secondary ms-1" title="Taxable"><i class="ri-money-dollar-line"></i></span>
                <?php endif; ?>
                <br>
                <small class="text-muted">
                    Effective: <?= date('d M Y', strtotime($comp->effectiveDate)) ?>
                    <?php if ($comp->frequency != 'every_payroll'): ?>
                    | Frequency: <?= ucwords(str_replace('_', ' ', $comp->frequency)) ?>
                    <?php endif; ?>
                </small>
            </div>
            <div class="d-flex align-items-center">
                <span class="me-3"><strong><?= $displayValue ?></strong></span>
                <?php if ($canEditSalary): ?>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-sm btn-outline-primary" onclick="editEmployeeComponent(<?= $comp->employeeComponentID ?>)">
                        <i class="ri-edit-line"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deactivateComponent(<?= $comp->employeeComponentID ?>)">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
                endif;
            endforeach;
            if (!$hasEarnings):
        ?>
        <p class="text-muted text-center py-3">No earnings/allowances assigned</p>
        <?php endif; else: ?>
        <p class="text-muted text-center py-3">No components assigned yet</p>
        <?php endif; ?>

        <!-- Deductions Section -->
        <h6 class="border-bottom pb-2 mb-3 mt-4 text-danger">
            <i class="ri-arrow-down-circle-line me-2"></i>Deductions
        </h6>
        <?php if ($employeeComponents):
            $hasDeductions = false;
            foreach ($employeeComponents as $comp):
                if ($comp->salaryComponentType == 'deduction'):
                    $hasDeductions = true;
                    $displayValue = $comp->valueType == 'percentage' ?
                        number_format($comp->componentValue, 2) . '%' :
                        'KES ' . number_format($comp->componentValue, 2);
        ?>
        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
            <div>
                <strong><?= htmlspecialchars($comp->salaryComponentTitle) ?></strong>
                <span class="badge bg-warning-transparent ms-2"><?= htmlspecialchars($comp->salaryComponentCategoryTitle) ?></span>
                <?php if ($comp->isStatutory == 'Y'): ?>
                <span class="badge bg-warning ms-1" title="Statutory"><i class="ri-government-line"></i></span>
                <?php endif; ?>
                <br>
                <small class="text-muted">
                    Effective: <?= date('d M Y', strtotime($comp->effectiveDate)) ?>
                    <?php if ($comp->frequency != 'every_payroll'): ?>
                    | Frequency: <?= ucwords(str_replace('_', ' ', $comp->frequency)) ?>
                    <?php endif; ?>
                </small>
            </div>
            <div class="d-flex align-items-center">
                <span class="me-3"><strong><?= $displayValue ?></strong></span>
                <?php if ($canEditSalary): ?>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-sm btn-outline-primary" onclick="editEmployeeComponent(<?= $comp->employeeComponentID ?>)">
                        <i class="ri-edit-line"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deactivateComponent(<?= $comp->employeeComponentID ?>)">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
                endif;
            endforeach;
            if (!$hasDeductions):
        ?>
        <p class="text-muted text-center py-3">No deductions assigned</p>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Assign Component Modal -->
<div class="modal fade" id="assignComponentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Salary Component</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignComponentForm">
                <div class="modal-body">
                    <input type="hidden" name="employeeID" value="<?= $employeeID ?>">
                    <input type="hidden" id="employeeComponentID" name="employeeComponentID">

                    <div class="mb-3">
                        <label class="form-label">Salary Component <span class="text-danger">*</span></label>
                        <select class="form-select" id="salaryComponentID" name="salaryComponentID" required onchange="updateComponentDefaults()">
                            <option value="">Select Component</option>
                            <?php if ($allComponents): foreach ($allComponents as $comp): ?>
                            <option value="<?= $comp->salaryComponentID ?>"
                                    data-value-type="<?= $comp->salaryComponentValueType ?>"
                                    data-default-value="<?= $comp->defaultValue ?>"
                                    data-apply-to="<?= $comp->applyTo ?>">
                                <?= htmlspecialchars($comp->salaryComponentTitle) ?>
                                (<?= ucfirst($comp->salaryComponentType) ?>)
                            </option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Value Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="valueType" name="valueType" required>
                                <option value="fixed">Fixed Amount</option>
                                <option value="percentage">Percentage</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Value <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="componentValue" name="componentValue"
                                   step="0.01" min="0" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Apply To</label>
                        <select class="form-select" id="applyTo" name="applyTo">
                            <option value="basic_salary">Basic Salary</option>
                            <option value="gross_salary">Gross Salary</option>
                            <option value="taxable_income">Taxable Income</option>
                            <option value="net_salary">Net Salary</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Effective Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="effectiveDate" name="effectiveDate"
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Frequency</label>
                            <select class="form-select" id="frequency" name="frequency">
                                <option value="every_payroll">Every Payroll</option>
                                <option value="monthly">Monthly Only</option>
                                <option value="one-time">One Time</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Assign Component
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const employeeComponentsData = <?= json_encode($employeeComponents ?: []) ?>;

function openAssignComponentModal() {
    document.getElementById('assignComponentForm').reset();
    document.getElementById('employeeComponentID').value = '';
    const modal = new bootstrap.Modal(document.getElementById('assignComponentModal'));
    modal.show();
}

function updateComponentDefaults() {
    const select = document.getElementById('salaryComponentID');
    const option = select.options[select.selectedIndex];

    if (option.value) {
        document.getElementById('valueType').value = option.dataset.valueType;
        document.getElementById('componentValue').value = option.dataset.defaultValue;
        document.getElementById('applyTo').value = option.dataset.applyTo;
    }
}

function deactivateComponent(componentID) {
    if (confirm('Are you sure you want to deactivate this component? This will stop it from being applied in future payrolls.')) {
        const formData = new FormData();
        formData.append('action', 'deactivate_employee_component');
        formData.append('assignmentID', componentID);

        fetch('<?= $base ?>php/scripts/global/admin/salary_components_api.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Component deactivated successfully');
                location.reload();
            } else {
                alert(data.message || 'Failed to deactivate component');
            }
        });
    }
}

document.getElementById('assignComponentForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append('action', 'assign_to_employee');

    fetch('<?= $base ?>php/scripts/global/admin/salary_components_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Component assigned successfully');
            location.reload();
        } else {
            alert(data.message || 'Failed to assign component');
        }
    });
});
</script>

<style>
.bg-success-transparent {
    background-color: rgba(25, 135, 84, 0.1) !important;
}
.bg-danger-transparent {
    background-color: rgba(220, 53, 69, 0.1) !important;
}
.bg-primary-transparent {
    background-color: rgba(13, 110, 253, 0.1) !important;
}
.bg-info-transparent {
    background-color: rgba(13, 202, 240, 0.1);
    color: #0dcaf0;
}
.bg-warning-transparent {
    background-color: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}
</style>

