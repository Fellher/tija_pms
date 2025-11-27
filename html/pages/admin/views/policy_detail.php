<?php
/**
 * Policy Detail View (View/Edit)
 */

$isEdit = $action === 'edit';
$hasRules = !empty($rules);
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ri-file-text-line me-2"></i>
                        Policy Details
                    </h5>

                    <div class="btn-group">
                        <?php if ($isEdit): ?>
                        <a href="?action=view&policyID=<?= $policy['policyID'] ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="ri-eye-line me-1"></i>View
                        </a>
                        <?php else: ?>
                        <a href="?action=edit&policyID=<?= $policy['policyID'] ?>" class="btn btn-primary btn-sm">
                            <i class="ri-edit-line me-1"></i>Edit
                        </a>
                        <?php endif; ?>

                        <div class="btn-group">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="ri-more-2-line"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="?action=duplicate&policyID=<?= $policy['policyID'] ?>">
                                        <i class="ri-file-copy-line me-2"></i>Duplicate
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger delete-policy"
                                       href="?action=delete&policyID=<?= $policy['policyID'] ?>">
                                        <i class="ri-delete-bin-line me-2"></i>Delete
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <?php if ($isEdit): ?>
                    <!-- Edit Form -->
                    <form id="policyForm" action="?action=update&policyID=<?= $policy['policyID'] ?>" method="POST">
                        <!-- Include the form fields from policy_form.php -->
                        <?php include 'policy_form.php'; ?>
                    </form>
                <?php else: ?>
                    <!-- View Mode -->
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Policy Name</h6>
                            <p class="h5"><?= htmlspecialchars($policy['policyName']) ?></p>
                        </div>

                        <div class="col-md-3">
                            <h6 class="text-muted mb-2">Priority</h6>
                            <span class="badge priority-badge"><?= $policy['priority'] ?></span>
                        </div>

                        <div class="col-md-3">
                            <h6 class="text-muted mb-2">Status</h6>
                            <span class="badge <?= $policy['isActive'] === 'Y' ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $policy['isActive'] === 'Y' ? 'Active' : 'Inactive' ?>
                            </span>
                        </div>
                    </div>

                    <?php if ($policy['policyDescription']): ?>
                    <div class="mt-3">
                        <h6 class="text-muted mb-2">Description</h6>
                        <p><?= htmlspecialchars($policy['policyDescription']) ?></p>
                    </div>
                    <?php endif; ?>

                    <hr>

                    <!-- Accrual Settings -->
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Leave Type</h6>
                            <p><?= htmlspecialchars($policy['leaveTypeName']) ?></p>
                        </div>

                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Accrual Type</h6>
                            <span class="badge accrual-type-badge bg-primary"><?= $policy['accrualType'] ?></span>
                        </div>

                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Accrual Rate</h6>
                            <p class="h6"><?= number_format($policy['accrualRate'], 2) ?> days per period</p>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Accrual Period</h6>
                            <p>
                                <?php if ($policy['accrualStartDate']): ?>
                                    From <?= date('M d, Y', strtotime($policy['accrualStartDate'])) ?>
                                <?php else: ?>
                                    Immediate
                                <?php endif; ?>

                                <?php if ($policy['accrualEndDate']): ?>
                                    to <?= date('M d, Y', strtotime($policy['accrualEndDate'])) ?>
                                <?php else: ?>
                                    (Indefinite)
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Pro-rated</h6>
                            <span class="badge <?= $policy['proRated'] === 'Y' ? 'bg-success' : 'bg-warning' ?>">
                                <?= $policy['proRated'] === 'Y' ? 'Yes' : 'No' ?>
                            </span>
                        </div>
                    </div>

                    <hr>

                    <!-- Carryover Settings -->
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Max Carryover</h6>
                            <p class="h6"><?= $policy['maxCarryover'] ? $policy['maxCarryover'] . ' days' : 'Unlimited' ?></p>
                        </div>

                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Carryover Expiry</h6>
                            <p class="h6"><?= $policy['carryoverExpiryMonths'] ? $policy['carryoverExpiryMonths'] . ' months' : 'Never' ?></p>
                        </div>

                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Created</h6>
                            <p><?= date('M d, Y H:i', strtotime($policy['DateAdded'])) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Rules Section -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ri-rules-line me-2"></i>
                        Rules (<?= count($rules) ?>)
                    </h5>

                    <?php if ($isEdit): ?>
                    <button type="button" class="btn btn-primary btn-sm" onclick="addRule()">
                        <i class="ri-add-line me-1"></i>Add Rule
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-body">
                <?php if (empty($rules)): ?>
                    <div class="text-center py-4">
                        <i class="ri-rules-line display-4 text-muted"></i>
                        <h6 class="mt-2 text-muted">No Rules Defined</h6>
                        <p class="text-muted">This policy uses the base accrual rate without any modifications.</p>
                        <?php if ($isEdit): ?>
                        <button type="button" class="btn btn-outline-primary" onclick="addRule()">
                            <i class="ri-add-line me-1"></i>Add First Rule
                        </button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div id="rulesContainer">
                        <?php foreach ($rules as $index => $rule): ?>
                        <div class="rule-item p-3 mb-3 border rounded">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h6 class="mb-0">Rule <?= $index + 1 ?></h6>
                                <?php if ($isEdit): ?>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRule(this)">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                                <?php endif; ?>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Rule Name</label>
                                    <?php if ($isEdit): ?>
                                    <input type="text" class="form-control" name="ruleName[]" value="<?= htmlspecialchars($rule['ruleName']) ?>">
                                    <?php else: ?>
                                    <p class="form-control-plaintext"><?= htmlspecialchars($rule['ruleName']) ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Rule Type</label>
                                    <?php if ($isEdit): ?>
                                    <select class="form-select" name="ruleType[]">
                                        <option value="Tenure" <?= $rule['ruleType'] === 'Tenure' ? 'selected' : '' ?>>Tenure</option>
                                        <option value="Performance" <?= $rule['ruleType'] === 'Performance' ? 'selected' : '' ?>>Performance</option>
                                        <option value="Department" <?= $rule['ruleType'] === 'Department' ? 'selected' : '' ?>>Department</option>
                                        <option value="Role" <?= $rule['ruleType'] === 'Role' ? 'selected' : '' ?>>Role</option>
                                        <option value="Custom" <?= $rule['ruleType'] === 'Custom' ? 'selected' : '' ?>>Custom</option>
                                    </select>
                                    <?php else: ?>
                                    <p class="form-control-plaintext"><?= $rule['ruleType'] ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Condition Field</label>
                                    <?php if ($isEdit): ?>
                                    <select class="form-select" name="conditionField[]">
                                        <option value="yearsOfService" <?= $rule['conditionField'] === 'yearsOfService' ? 'selected' : '' ?>>Years of Service</option>
                                        <option value="performanceRating" <?= $rule['conditionField'] === 'performanceRating' ? 'selected' : '' ?>>Performance Rating</option>
                                        <option value="departmentName" <?= $rule['conditionField'] === 'departmentName' ? 'selected' : '' ?>>Department</option>
                                        <option value="jobTitle" <?= $rule['jobTitle'] === 'jobTitle' ? 'selected' : '' ?>>Job Title</option>
                                        <option value="jobLevel" <?= $rule['conditionField'] === 'jobLevel' ? 'selected' : '' ?>>Job Level</option>
                                    </select>
                                    <?php else: ?>
                                    <p class="form-control-plaintext"><?= $rule['conditionField'] ? ucfirst(str_replace('_', ' ', $rule['conditionField'])) : 'N/A' ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Operator</label>
                                    <?php if ($isEdit): ?>
                                    <select class="form-select" name="conditionOperator[]">
                                        <option value="=" <?= $rule['conditionOperator'] === '=' ? 'selected' : '' ?>>Equals</option>
                                        <option value=">" <?= $rule['conditionOperator'] === '>' ? 'selected' : '' ?>>Greater than</option>
                                        <option value=">=" <?= $rule['conditionOperator'] === '>=' ? 'selected' : '' ?>>Greater or equal</option>
                                        <option value="<" <?= $rule['conditionOperator'] === '<' ? 'selected' : '' ?>>Less than</option>
                                        <option value="<=" <?= $rule['conditionOperator'] === '<=' ? 'selected' : '' ?>>Less or equal</option>
                                        <option value="<>" <?= $rule['conditionOperator'] === '<>' ? 'selected' : '' ?>>Not equal</option>
                                    </select>
                                    <?php else: ?>
                                    <p class="form-control-plaintext"><?= $rule['conditionOperator'] ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Value</label>
                                    <?php if ($isEdit): ?>
                                    <input type="text" class="form-control" name="conditionValue[]" value="<?= htmlspecialchars($rule['conditionValue']) ?>">
                                    <?php else: ?>
                                    <p class="form-control-plaintext"><?= htmlspecialchars($rule['conditionValue']) ?></p>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Multiplier</label>
                                    <?php if ($isEdit): ?>
                                    <input type="number" class="form-control" name="accrualMultiplier[]" value="<?= $rule['accrualMultiplier'] ?>" step="0.01" min="0.01">
                                    <?php else: ?>
                                    <p class="form-control-plaintext"><?= number_format($rule['accrualMultiplier'], 2) ?>x</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="ri-flashlight-line me-2"></i>
                    Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-primary" onclick="calculateAccrual()">
                        <i class="ri-calculator-line me-2"></i>Test Calculation
                    </button>

                    <button class="btn btn-outline-info" onclick="viewHistory()">
                        <i class="ri-history-line me-2"></i>View History
                    </button>

                    <button class="btn btn-outline-success" onclick="duplicatePolicy()">
                        <i class="ri-file-copy-line me-2"></i>Duplicate Policy
                    </button>
                </div>
            </div>
        </div>

        <!-- Policy Statistics -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="ri-bar-chart-line me-2"></i>
                    Policy Statistics
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border rounded p-2 mb-2">
                            <div class="h5 text-primary mb-0"><?= count($rules) ?></div>
                            <small class="text-muted">Rules</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2 mb-2">
                            <div class="h5 text-info mb-0"><?= $policy['accrualRate'] ?></div>
                            <small class="text-muted">Base Rate</small>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="small text-muted">
                    <div class="d-flex justify-content-between">
                        <span>Created:</span>
                        <span><?= date('M d, Y', strtotime($policy['DateAdded'])) ?></span>
                    </div>
                    <?php if ($policy['LastUpdate']): ?>
                    <div class="d-flex justify-content-between">
                        <span>Updated:</span>
                        <span><?= date('M d, Y', strtotime($policy['LastUpdate'])) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rule Template for Adding New Rules -->
<template id="ruleTemplate">
    <div class="rule-item p-3 mb-3 border rounded">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <h6 class="mb-0">New Rule</h6>
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
                    <option value="=">Equals</option>
                    <option value=">">Greater than</option>
                    <option value=">=">Greater or equal</option>
                    <option value="<">Less than</option>
                    <option value="<=">Less or equal</option>
                    <option value="<>">Not equal</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Value</label>
                <input type="text" class="form-control" name="conditionValue[]" placeholder="e.g., 5">
            </div>

            <div class="col-md-2">
                <label class="form-label">Multiplier</label>
                <input type="number" class="form-control" name="accrualMultiplier[]" value="1.00" step="0.01" min="0.01">
            </div>
        </div>
    </div>
</template>

<script>
function calculateAccrual() {
    // Implementation for testing accrual calculation
    if (typeof showToast === 'function') {
        showToast('Accrual calculation feature coming soon!', 'info');
    } else {
        alert('Accrual calculation feature coming soon!');
    }
}

function viewHistory() {
    // Implementation for viewing policy history
    window.open('?action=history&policyID=<?= $policy['policyID'] ?>', '_blank');
}

function duplicatePolicy() {
    if (confirm('Create a copy of this policy?')) {
        window.location.href = '?action=duplicate&policyID=<?= $policy['policyID'] ?>';
    }
}
</script>
