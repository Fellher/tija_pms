<!-- Policy Detail View -->
<?php if ($policy): ?>
<div class="row">
    <div class="col-lg-8 mx-auto">
        <!-- Policy Header -->
        <div class="card policy-card mb-4">
            <div class="policy-card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1"><?= htmlspecialchars($policy->policyName) ?></h4>
                        <span class="accrual-type-badge"><?= ucfirst($policy->accrualType) ?></span>
                    </div>
                    <div>
                        <span class="status-badge status-<?= $policy->status ?>">
                            <?= ucfirst($policy->status) ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="policy-card-body">
                <?php if (!empty($policy->description)): ?>
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Description</h6>
                    <p class="mb-0"><?= htmlspecialchars($policy->description) ?></p>
                </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-2">
                            <strong>Accrual Rate:</strong>
                            <span class="text-muted"><?= $policy->accrualRate ?> days per <?= $policy->accrualType ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
                            <strong>Max Days:</strong>
                            <span class="text-muted"><?= $policy->maxDays ?? 'Unlimited' ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
                            <strong>Priority:</strong>
                            <span class="text-muted"><?= $policy->priority ?></span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
                            <strong>Created:</strong>
                            <span class="text-muted"><?= date('M d, Y', strtotime($policy->createdDate)) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-between mb-4">
            <a href="?action=accumulation_policies" class="btn btn-outline-secondary">
                <i class="ri-arrow-left-line me-1"></i>
                Back to Policies
            </a>

            <div class="d-flex gap-2">
                <?php if ($action === 'view_policy'): ?>
                <a href="?action=edit_policy&policyID=<?= $policy->policyID ?>" class="btn btn-primary">
                    <i class="ri-edit-line me-1"></i>
                    Edit Policy
                </a>
                <?php else: ?>
                <a href="?action=view_policy&policyID=<?= $policy->policyID ?>" class="btn btn-outline-primary">
                    <i class="ri-eye-line me-1"></i>
                    View Details
                </a>
                <?php endif; ?>

                <button type="button"
                        class="btn btn-outline-<?= $policy->status === 'active' ? 'warning' : 'success' ?>"
                        data-action="toggle-policy-status"
                        data-policy-id="<?= $policy->policyID ?>"
                        data-policy-status="<?= $policy->status ?>">
                    <i class="ri-<?= $policy->status === 'active' ? 'pause' : 'play' ?>-line me-1"></i>
                    <?= $policy->status === 'active' ? 'Deactivate' : 'Activate' ?>
                </button>

                <button type="button"
                        class="btn btn-outline-info"
                        data-action="copy-policy"
                        data-policy-id="<?= $policy->policyID ?>">
                    <i class="ri-copy-line me-1"></i>
                    Copy
                </button>

                <button type="button"
                        class="btn btn-outline-secondary"
                        data-action="print-policy"
                        data-policy-id="<?= $policy->policyID ?>">
                    <i class="ri-printer-line me-1"></i>
                    Print
                </button>
            </div>
        </div>

        <?php if ($action === 'edit_policy'): ?>
        <!-- Edit Form -->
        <div class="form-section">
            <h4 class="section-title">
                <i class="ri-edit-line me-2"></i>
                Edit Policy
            </h4>

            <form id="policyForm" method="POST" action="<?= $base ?>/php/scripts/leave/config/manage_accumulation_policy.php">
                <input type="hidden" name="policyID" value="<?= $policy->policyID ?>">

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
                                   value="<?= htmlspecialchars($policy->policyName) ?>"
                                   required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="accrualType" class="form-label">
                                Accrual Type <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="accrualType" name="accrualType" required onchange="updateAccrualTypeFields(this.value)">
                                <option value="">Select Accrual Type</option>
                                <option value="Front-Loaded" <?= isset($policy->accrualType) && $policy->accrualType === 'Front-Loaded' ? 'selected' : '' ?>>Front-Loaded</option>
                                <option value="Periodic" <?= isset($policy->accrualType) && ($policy->accrualType === 'Periodic' || in_array($policy->accrualType, ['monthly', 'yearly', 'quarterly', 'weekly', 'Monthly', 'Quarterly', 'Annual', 'Continuous'])) ? 'selected' : '' ?>>Periodic</option>
                                <option value="Proration" <?= isset($policy->accrualType) && $policy->accrualType === 'Proration' ? 'selected' : '' ?>>Proration</option>
                            </select>
                            <small class="form-text text-muted" id="accrualTypeHelp"></small>
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
                                <option value="Monthly" <?= isset($policy->accrualPeriod) && $policy->accrualPeriod === 'Monthly' ? 'selected' : 'selected' ?>>Monthly</option>
                                <option value="Quarterly">Quarterly</option>
                                <option value="Bi-Annually">Bi-Annually</option>
                                <option value="Annually">Annually</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6" id="frontLoadDateField" style="display: none;">
                        <div class="mb-3">
                            <label for="frontLoadDate" class="form-label">Front-Load Date</label>
                            <input type="date" class="form-control" id="frontLoadDate" name="frontLoadDate" value="<?= isset($policy->frontLoadDate) ? htmlspecialchars($policy->frontLoadDate) : '' ?>">
                        </div>
                    </div>

                    <div class="col-md-6" id="prorationBasisField" style="display: none;">
                        <div class="mb-3">
                            <label for="prorationBasis" class="form-label">Proration Basis</label>
                            <select class="form-select" id="prorationBasis" name="prorationBasis">
                                <option value="Days Worked" <?= isset($policy->prorationBasis) && $policy->prorationBasis === 'Days Worked' ? 'selected' : 'selected' ?>>Days Worked</option>
                                <option value="Months Worked" <?= isset($policy->prorationBasis) && $policy->prorationBasis === 'Months Worked' ? 'selected' : '' ?>>Months Worked</option>
                                <option value="Service Period" <?= isset($policy->prorationBasis) && $policy->prorationBasis === 'Service Period' ? 'selected' : '' ?>>Service Period</option>
                                <option value="Custom" <?= isset($policy->prorationBasis) && $policy->prorationBasis === 'Custom' ? 'selected' : '' ?>>Custom</option>
                            </select>
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
                                   value="<?= $policy->accrualRate ?>"
                                   step="0.01"
                                   min="0"
                                   max="365"
                                   required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="maxDays" class="form-label">Maximum Days</label>
                            <input type="number"
                                   class="form-control"
                                   id="maxDays"
                                   name="maxDays"
                                   value="<?= $policy->maxDays ?>"
                                   min="0"
                                   max="365">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control"
                              id="description"
                              name="description"
                              rows="4"><?= htmlspecialchars($policy->description) ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="active" <?= $policy->status === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $policy->status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <input type="number"
                                   class="form-control"
                                   id="priority"
                                   name="priority"
                                   value="<?= $policy->priority ?>"
                                   min="1"
                                   max="10">
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-between">
                    <a href="?action=view_policy&policyID=<?= $policy->policyID ?>" class="btn btn-outline-secondary">
                        <i class="ri-arrow-left-line me-1"></i>
                        Cancel
                    </a>

                    <div>
                        <button type="button" class="btn btn-outline-primary me-2" data-action="reset-policy-form">
                            <i class="ri-refresh-line me-1"></i>
                            Reset
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-1"></i>
                            Update Policy
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <?php else: ?>
        <!-- View Mode - Policy Rules -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="ri-list-check me-2"></i>
                            Policy Rules
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $rules = AccumulationPolicy::get_policy_rules($policy->policyID, false, $DBConn);
                        if ($rules && count($rules) > 0):
                        ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Leave Type</th>
                                            <th>Accrual Rate</th>
                                            <th>Max Days</th>
                                            <th>Priority</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rules as $rule): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($rule->leaveTypeName) ?></td>
                                            <td><?= $rule->accrualRate ?> days</td>
                                            <td><?= $rule->maxDays ?? 'Unlimited' ?></td>
                                            <td><?= $rule->priority ?></td>
                                            <td>
                                                <span class="status-badge status-<?= $rule->status ?>">
                                                    <?= ucfirst($rule->status) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted">
                                <i class="ri-list-check" style="font-size: 2rem; opacity: 0.3;"></i>
                                <p class="mt-2">No rules configured for this policy</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="ri-information-line me-2"></i>
                            Policy Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <strong>Name:</strong>
                            <span class="text-muted"><?= htmlspecialchars($policy->policyName) ?></span>
                        </div>
                        <div class="mb-2">
                            <strong>Type:</strong>
                            <span class="text-muted"><?= ucfirst($policy->accrualType) ?></span>
                        </div>
                        <div class="mb-2">
                            <strong>Status:</strong>
                            <span class="status-badge status-<?= $policy->status ?>">
                                <?= ucfirst($policy->status) ?>
                            </span>
                        </div>
                        <div class="mb-2">
                            <strong>Priority:</strong>
                            <span class="text-muted"><?= $policy->priority ?></span>
                        </div>
                        <div class="mb-0">
                            <strong>Created:</strong>
                            <span class="text-muted"><?= date('M d, Y H:i', strtotime($policy->createdDate)) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-action="toggle-policy-status"]').forEach(button => {
        const policyId = Number(button.dataset.policyId);
        const policyStatus = button.dataset.policyStatus;

        if (typeof togglePolicyStatus === 'function') {
            button.addEventListener('click', () => togglePolicyStatus(policyId, policyStatus));
        }
    });

    document.querySelectorAll('[data-action="copy-policy"]').forEach(button => {
        const policyId = Number(button.dataset.policyId);
        if (typeof copyPolicyToClipboard === 'function') {
            button.addEventListener('click', () => copyPolicyToClipboard(policyId));
        }
    });

    document.querySelectorAll('[data-action="print-policy"]').forEach(button => {
        const policyId = Number(button.dataset.policyId);
        if (typeof printPolicy === 'function') {
            button.addEventListener('click', () => printPolicy(policyId));
        }
    });

    document.querySelectorAll('[data-action="reset-policy-form"]').forEach(button => {
        button.addEventListener('click', () => {
            if (confirm('Are you sure you want to reset the form? All changes will be lost.')) {
                location.reload();
            }
        });
    });
});
</script>

<?php else: ?>
<!-- Policy Not Found -->
<div class="row">
    <div class="col-12">
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="ri-error-warning-line"></i>
            </div>
            <h4 class="empty-state-title">Policy Not Found</h4>
            <p class="empty-state-description">
                The requested policy could not be found or may have been deleted.
            </p>
            <a href="?action=accumulation_policies" class="btn btn-primary">
                <i class="ri-arrow-left-line me-1"></i>
                Back to Policies
            </a>
        </div>
    </div>
</div>
<?php endif; ?>
