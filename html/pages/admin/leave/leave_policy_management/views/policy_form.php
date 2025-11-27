<?php
/**
 * Comprehensive Leave Policy Configuration Form
 * Multi-section form for complete policy setup
 */

$isEdit = isset($policy) && $policy;
$policyID = $isEdit ? $policy->leaveTypeID : null;
?>

<div class="row">
    <div class="col-12">
        <!-- Progress Stepper -->
        <div class="config-stepper mb-4">
            <div class="row text-center">
                <div class="col step active" data-step="1">
                    <div class="step-icon"><i class="ri-file-list-line"></i></div>
                    <div class="step-label">Basic Info</div>
                </div>
                <div class="col step" data-step="2">
                    <div class="step-icon"><i class="ri-calendar-check-line"></i></div>
                    <div class="step-label">Entitlements</div>
                </div>
                <div class="col step" data-step="3">
                    <div class="step-icon"><i class="ri-refresh-line"></i></div>
                    <div class="step-label">Carry-Over</div>
                </div>
                <div class="col step" data-step="4">
                    <div class="step-icon"><i class="ri-shield-check-line"></i></div>
                    <div class="step-label">Eligibility</div>
                </div>
                <div class="col step" data-step="5">
                    <div class="step-icon"><i class="ri-calendar-event-line"></i></div>
                    <div class="step-label">Application Rules</div>
                </div>
                <div class="col step" data-step="6">
                    <div class="step-icon"><i class="ri-flow-chart"></i></div>
                    <div class="step-label">Workflows</div>
                </div>
            </div>
        </div>

        <form id="policyConfigForm" method="POST" action="<?= $base ?>php/scripts/leave/config/manage_leave_policy.php" class="needs-validation" novalidate>
            <input type="hidden" name="policyID" value="<?= $policyID ?>">
            <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">

            <!-- Step 1: Basic Information -->
            <div class="config-section" id="section-1">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="ri-file-list-line me-2"></i>
                            Step 1: Basic Information
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!$isEdit): ?>
                        <!-- For new policies, select leave type and entity -->
                        <div class="alert alert-info mb-4">
                            <i class="ri-information-line me-2"></i>
                            <strong>Creating Policy:</strong> Select an existing leave type and entity to configure the policy for.
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="leaveTypeID" class="form-label fw-semibold">
                                        Leave Type <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="leaveTypeID" name="leaveTypeID" required onchange="loadLeaveTypeDetails()">
                                        <option value="">-- Select Leave Type --</option>
                                        <?php if (!empty($leaveTypes)): ?>
                                            <?php foreach ($leaveTypes as $lt): ?>
                                            <option value="<?= $lt->leaveTypeID ?>" <?= (isset($selectedLeaveType) && $selectedLeaveType->leaveTypeID == $lt->leaveTypeID) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($lt->leaveTypeName) ?>
                                                <?php if (!empty($lt->leaveTypeCode)): ?>
                                                (<?= htmlspecialchars($lt->leaveTypeCode) ?>)
                                                <?php endif; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="form-text">
                                        <a href="<?= $base ?>html/?s=admin&ss=leave&p=leave_types&action=create" target="_blank" class="text-decoration-none">
                                            <i class="ri-add-line me-1"></i>Create New Leave Type
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="entityID" class="form-label fw-semibold">
                                        Entity <span class="text-danger">*</span>
                                    </label>
                                    <?php
                                    $allEntities = Data::entities_full([], false, $DBConn);
                                    ?>
                                    <select class="form-select" id="entityID" name="entityID" required>
                                        <option value="">-- Select Entity --</option>
                                        <?php if (!empty($allEntities)): ?>
                                            <?php foreach ($allEntities as $ent): ?>
                                            <option value="<?= $ent->entityID ?>" <?= ($entityID == $ent->entityID) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($ent->entityName) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="form-text">Policy will be scoped to this entity</div>
                                </div>
                            </div>
                        </div>

                        <!-- Leave Type Details (auto-filled when selected) -->
                        <div id="leaveTypeDetails" class="d-none mb-4">
                            <div class="card bg-light border">
                                <div class="card-body">
                                    <h6 class="card-title">Leave Type Information</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Name:</strong> <span id="selectedLeaveTypeName" class="text-muted">-</span><br>
                                            <strong>Code:</strong> <span id="selectedLeaveTypeCode" class="text-muted">-</span>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Description:</strong> <span id="selectedLeaveTypeDescription" class="text-muted">-</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <!-- For editing, show policy info -->
                        <input type="hidden" name="leaveTypeID" value="<?= $policy->leaveTypeID ?>">
                        <input type="hidden" name="entityID" value="<?= $entityID ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="leaveTypeName" class="form-label fw-semibold">
                                        Policy Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="leaveTypeName" name="leaveTypeName"
                                           value="<?= $isEdit ? htmlspecialchars($policy->leaveTypeName) : '' ?>"
                                           placeholder="e.g., Annual Leave, Sick Leave, Maternity Leave"
                                           <?= $isEdit ? 'readonly' : '' ?> required>
                                    <div class="form-text"><?= $isEdit ? 'Leave type name (cannot be changed)' : 'Enter a clear, descriptive name for this leave policy' ?></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="leaveTypeCode" class="form-label fw-semibold">
                                        Policy Code <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="form-control text-uppercase" id="leaveTypeCode" name="leaveTypeCode"
                                               value="<?= $isEdit ? htmlspecialchars($policy->leaveTypeCode) : '' ?>"
                                               placeholder="e.g., ANNUAL, SICK, MATERNITY"
                                               pattern="[A-Z0-9_]+" maxlength="15" <?= $isEdit ? 'readonly' : '' ?> required>
                                        <?php if (!$isEdit): ?>
                                        <button type="button" class="btn btn-outline-secondary" onclick="generateCode()"
                                                data-bs-toggle="tooltip" title="Generate from name">
                                            <i class="ri-refresh-line"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-text"><?= $isEdit ? 'Leave type code (cannot be changed)' : 'Short code for internal reference (uppercase, no spaces)' ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="leaveTypeDescription" class="form-label fw-semibold">Description</label>
                                    <textarea class="form-control" id="leaveTypeDescription" name="leaveTypeDescription" rows="3"
                                              placeholder="Describe the purpose and usage of this leave policy..."><?= $isEdit ? htmlspecialchars($policy->leaveTypeDescription) : '' ?></textarea>
                                    <div class="form-text">Optional detailed description visible to employees</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="isPaidLeave" class="form-label fw-semibold">Paid Leave</label>
                                    <select class="form-select" id="isPaidLeave" name="isPaidLeave">
                                        <option value="Y" <?= ($isEdit && isset($policy->isPaidLeave) && $policy->isPaidLeave == 'Y') ? 'selected' : '' ?>>Yes - Paid</option>
                                        <option value="N" <?= ($isEdit && isset($policy->isPaidLeave) && $policy->isPaidLeave == 'N') ? 'selected' : '' ?>>No - Unpaid</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="requiresApproval" class="form-label fw-semibold">Requires Approval</label>
                                    <select class="form-select" id="requiresApproval" name="requiresApproval">
                                        <option value="Y" <?= ($isEdit && isset($policy->requiresApproval) && $policy->requiresApproval == 'Y') ? 'selected' : '' ?>>Yes</option>
                                        <option value="N" <?= ($isEdit && isset($policy->requiresApproval) && $policy->requiresApproval == 'N') ? 'selected' : '' ?>>No - Auto-Approved</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="status" class="form-label fw-semibold">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="N" <?= ($isEdit && isset($policy->Suspended) && $policy->Suspended == 'N') ? 'selected' : '' ?>>Active</option>
                                        <option value="Y" <?= ($isEdit && isset($policy->Suspended) && $policy->Suspended == 'Y') ? 'selected' : '' ?>>Suspended</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-primary" onclick="nextSection(2)">
                                Next: Entitlements <i class="ri-arrow-right-line ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Entitlement Rules -->
            <div class="config-section d-none" id="section-2">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="ri-calendar-check-line me-2"></i>
                            Step 2: Entitlement & Accrual Rules
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>
                            Define how employees earn leave days under this policy. You can use an existing accrual policy or configure manually.
                        </div>

                        <!-- Accrual Policy Selection -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card bg-light border">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="ri-refresh-line me-2"></i>
                                            Accrual Policy
                                        </h6>
                                        <div class="mb-3">
                                            <label for="accumulationPolicyID" class="form-label fw-semibold">
                                                Select Accrual Policy (Optional)
                                            </label>
                                            <select class="form-select" id="accumulationPolicyID" name="accumulationPolicyID" onchange="toggleAccrualOverride()">
                                                <option value="">None - Manual Configuration</option>
                                                <?php
                                                // Get all accumulation policies for this entity
                                                $allAccrualPolicies = AccumulationPolicy::get_policies($entityID, false, $DBConn) ?? array();
                                                if (!empty($allAccrualPolicies)):
                                                    foreach ($allAccrualPolicies as $ap):
                                                        $selected = ($isEdit && isset($policy->accumulationPolicy) && $policy->accumulationPolicy->policyID == $ap->policyID) ? 'selected' : '';
                                                ?>
                                                    <option value="<?= $ap->policyID ?>" data-type="<?= htmlspecialchars($ap->accrualType) ?>" data-rate="<?= $ap->accrualRate ?>" <?= $selected ?>>
                                                        <?= htmlspecialchars($ap->policyName) ?> (<?= htmlspecialchars($ap->accrualType) ?> - <?= $ap->accrualRate ?> days/period)
                                                    </option>
                                                <?php
                                                    endforeach;
                                                endif;
                                                ?>
                                            </select>
                                            <div class="form-text">
                                                <a href="<?= $base ?>html/?s=admin&ss=leave&p=accumulation_policies" target="_blank" class="text-decoration-none">
                                                    <i class="ri-external-link-line me-1"></i>Manage Accrual Policies
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Override Options (shown when accrual policy is selected) -->
                                        <div id="accrualOverrideSection" class="d-none">
                                            <div class="alert alert-warning mb-3">
                                                <i class="ri-alert-line me-2"></i>
                                                <strong>Override Options:</strong> You can override specific settings for this entity and leave type combination.
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-check form-switch mb-3">
                                                        <input class="form-check-input" type="checkbox" id="overrideAccrualRate" name="overrideAccrualRate" value="Y">
                                                        <label class="form-check-label" for="overrideAccrualRate">
                                                            Override Accrual Rate
                                                        </label>
                                                    </div>
                                                    <div id="overrideRateInput" class="d-none mb-3">
                                                        <label for="customAccrualRate" class="form-label">Custom Accrual Rate</label>
                                                        <div class="input-group">
                                                            <input type="number" class="form-control" id="customAccrualRate" name="customAccrualRate"
                                                                   min="0" step="0.01" placeholder="Enter custom rate">
                                                            <span class="input-group-text">days/period</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check form-switch mb-3">
                                                        <input class="form-check-input" type="checkbox" id="overrideProRated" name="overrideProRated" value="Y">
                                                        <label class="form-check-label" for="overrideProRated">
                                                            Override Pro-Rating Setting
                                                        </label>
                                                    </div>
                                                    <div id="overrideProRatedInput" class="d-none mb-3">
                                                        <label for="customProRated" class="form-label">Pro-Rated</label>
                                                        <select class="form-select" id="customProRated" name="customProRated">
                                                            <option value="Y">Yes</option>
                                                            <option value="N">No</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Manual Entitlement Configuration -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="annualEntitlement" class="form-label fw-semibold">
                                        Annual Entitlement <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="annualEntitlement" name="annualEntitlement"
                                               min="0" max="365" step="0.5" placeholder="21"
                                               value="<?= $isEdit ? (isset($policy->annualEntitlement) ? $policy->annualEntitlement : (isset($policy->entitlements[0]) ? $policy->entitlements[0]->entitlement : '')) : '' ?>"
                                               required>
                                        <span class="input-group-text">days/year</span>
                                    </div>
                                    <div class="form-text">Total days per year</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="maxDaysPerApplication" class="form-label fw-semibold">Max Days Per Application</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="maxDaysPerApplication" name="maxDaysPerApplication"
                                               min="1" max="365" placeholder="Unlimited"
                                               value="<?= $isEdit ? (isset($policy->maxDaysPerApplication) ? $policy->maxDaysPerApplication : (isset($policy->entitlements[0]) ? ($policy->entitlements[0]->maxDaysPerApplication ?? '') : '')) : '' ?>">
                                        <span class="input-group-text">days</span>
                                    </div>
                                    <div class="form-text">Leave empty for unlimited</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="minNoticeDays" class="form-label fw-semibold">Minimum Notice</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="minNoticeDays" name="minNoticeDays"
                                               min="0" max="90" value="<?= $isEdit ? (isset($policy->minNoticeDays) ? $policy->minNoticeDays : (isset($policy->entitlements[0]) ? ($policy->entitlements[0]->minNoticeDays ?? '0') : '0')) : '0' ?>">
                                        <span class="input-group-text">days</span>
                                    </div>
                                    <div class="form-text">0 = same-day applications allowed</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="allowProration" name="allowProration" value="Y" <?= ($isEdit && isset($policy->allowProration) && $policy->allowProration == 'Y') ? 'checked' : 'checked' ?>>
                                        <label class="form-check-label fw-semibold" for="allowProration">
                                            Allow Proration
                                        </label>
                                    </div>
                                    <div class="form-text">Calculate pro-rata for mid-year joiners/leavers</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="allowNegativeBalance" name="allowNegativeBalance" value="Y" <?= ($isEdit && isset($policy->allowNegativeBalance) && $policy->allowNegativeBalance == 'Y') ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-semibold" for="allowNegativeBalance">
                                            Allow Negative Balance
                                        </label>
                                    </div>
                                    <div class="form-text">Permit leave advance (to be recovered)</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="maxAccrual" class="form-label fw-semibold">Maximum Accrual Cap</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="maxAccrual" name="maxAccrual"
                                               min="0" max="999" placeholder="Leave empty for unlimited"
                                               value="<?= $isEdit && isset($policy->maxAccrual) ? $policy->maxAccrual : '' ?>">
                                        <span class="input-group-text">days</span>
                                    </div>
                                    <div class="form-text">Maximum total balance allowed</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="minBalance" class="form-label fw-semibold">Minimum Balance to Apply</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="minBalance" name="minBalance"
                                               min="0" step="0.5" value="<?= $isEdit && isset($policy->minBalance) ? $policy->minBalance : '0.5' ?>">
                                        <span class="input-group-text">days</span>
                                    </div>
                                    <div class="form-text">Minimum balance required to submit application</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary" onclick="prevSection(1)">
                                <i class="ri-arrow-left-line me-1"></i> Previous
                            </button>
                            <button type="button" class="btn btn-success" onclick="nextSection(3)">
                                Next: Carry-Over <i class="ri-arrow-right-line ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 3: Carry-Over Policies -->
            <div class="config-section d-none" id="section-3">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="ri-refresh-line me-2"></i>
                            Step 3: Carry-Over Policies
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-warning">
                            <i class="ri-alarm-warning-line me-2"></i>
                            Configure rules for unused leave at year-end
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="allowCarryOver" name="allowCarryOver" value="Y" <?= ($isEdit && isset($policy->allowCarryOver) && $policy->allowCarryOver == 'Y') ? 'checked' : 'checked' ?>>
                                        <label class="form-check-label fw-semibold" for="allowCarryOver">
                                            Allow Carry-Over to Next Period
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="carryOverOptions" style="<?= ($isEdit && isset($policy->allowCarryOver) && $policy->allowCarryOver == 'N') ? 'display:none;' : 'display:block;' ?>">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="maxCarryOver" class="form-label fw-semibold">Maximum Carry-Over</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="maxCarryOver" name="maxCarryOver"
                                                   min="0" max="365" placeholder="5"
                                                   value="<?= $isEdit && isset($policy->maxCarryOver) ? $policy->maxCarryOver : '' ?>">
                                            <span class="input-group-text">days</span>
                                        </div>
                                        <div class="form-text">Leave empty for unlimited carry-over</div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="carryOverExpiry" class="form-label fw-semibold">Carry-Over Expiry</label>
                                        <select class="form-select" id="carryOverExpiry" name="carryOverExpiry">
                                            <?php
                                            $carryOverExpiryValue = $isEdit && isset($policy->carryOverExpiry) ? (string)$policy->carryOverExpiry : '';
                                            ?>
                                            <option value="" <?= ($carryOverExpiryValue == '' || $carryOverExpiryValue == null) ? 'selected' : '' ?>>No Expiry</option>
                                            <option value="3" <?= ($carryOverExpiryValue == '3') ? 'selected' : '' ?>>3 Months</option>
                                            <option value="6" <?= ($carryOverExpiryValue == '6') ? 'selected' : '' ?>>6 Months</option>
                                            <option value="12" <?= ($carryOverExpiryValue == '12') ? 'selected' : '' ?>>12 Months</option>
                                        </select>
                                        <div class="form-text">When carried-over days expire</div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="useItOrLoseIt" class="form-label fw-semibold">Forfeiture Rule</label>
                                        <select class="form-select" id="useItOrLoseIt" name="useItOrLoseIt">
                                            <option value="N" <?= ($isEdit && isset($policy->useItOrLoseIt) && $policy->useItOrLoseIt == 'N') ? 'selected' : 'selected' ?>>Carry-Over Allowed</option>
                                            <option value="Y" <?= ($isEdit && isset($policy->useItOrLoseIt) && $policy->useItOrLoseIt == 'Y') ? 'selected' : '' ?>>Use It or Lose It</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="allowCashout" name="allowCashout" value="Y" <?= ($isEdit && isset($policy->allowCashout) && $policy->allowCashout == 'Y') ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-semibold" for="allowCashout">
                                                Allow Cash-Out of Unused Days
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="carryOverPriority" name="carryOverPriority" value="Y" <?= ($isEdit && isset($policy->carryOverPriority) && $policy->carryOverPriority == 'Y') ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-semibold" for="carryOverPriority">
                                                Use Carried-Over Days First
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary" onclick="prevSection(2)">
                                <i class="ri-arrow-left-line me-1"></i> Previous
                            </button>
                            <button type="button" class="btn btn-info" onclick="nextSection(4)">
                                Next: Eligibility <i class="ri-arrow-right-line ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 4: Eligibility Criteria -->
            <div class="config-section d-none" id="section-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="ri-shield-check-line me-2"></i>
                            Step 4: Eligibility Criteria
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-primary">
                            <i class="ri-user-settings-line me-2"></i>
                            Define who can use this leave policy
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="minServicePeriod" class="form-label fw-semibold">Minimum Service Period</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="minServicePeriod" name="minServicePeriod"
                                               min="0" max="60" value="<?= $isEdit && isset($policy->minServicePeriod) ? $policy->minServicePeriod : '0' ?>">
                                        <span class="input-group-text">months</span>
                                    </div>
                                    <div class="form-text">Required service before eligibility (0 = immediate)</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="excludeProbation" class="form-label fw-semibold">Probation Rule</label>
                                    <select class="form-select" id="excludeProbation" name="excludeProbation">
                                        <option value="N" <?= ($isEdit && isset($policy->excludeProbation) && $policy->excludeProbation == 'N') ? 'selected' : 'selected' ?>>Available During Probation</option>
                                        <option value="Y" <?= ($isEdit && isset($policy->excludeProbation) && $policy->excludeProbation == 'Y') ? 'selected' : '' ?>>Excluded During Probation</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="genderRestriction" class="form-label fw-semibold">Gender Restriction</label>
                                    <select class="form-select" id="genderRestriction" name="genderRestriction">
                                        <option value="all" <?= ($isEdit && isset($policy->genderRestriction) && ($policy->genderRestriction == 'all' || $policy->genderRestriction == '')) ? 'selected' : 'selected' ?>>All Employees</option>
                                        <option value="female" <?= ($isEdit && isset($policy->genderRestriction) && $policy->genderRestriction == 'female') ? 'selected' : '' ?>>Female Only</option>
                                        <option value="male" <?= ($isEdit && isset($policy->genderRestriction) && $policy->genderRestriction == 'male') ? 'selected' : '' ?>>Male Only</option>
                                    </select>
                                    <div class="form-text">For policies like Maternity/Paternity leave</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="employmentType" class="form-label fw-semibold">Employment Type</label>
                                    <select class="form-select" id="employmentType" name="employmentType" multiple>
                                        <?php
                                        $employmentTypes = array('all', 'permanent', 'contract', 'temporary', 'parttime');
                                        $selectedTypes = array();
                                        if ($isEdit && isset($policy->employmentType)) {
                                            if (is_string($policy->employmentType)) {
                                                $selectedTypes = explode(',', $policy->employmentType);
                                            } elseif (is_array($policy->employmentType)) {
                                                $selectedTypes = $policy->employmentType;
                                            }
                                        } else {
                                            $selectedTypes = array('all');
                                        }
                                        foreach ($employmentTypes as $type):
                                            $selected = in_array($type, $selectedTypes) ? 'selected' : '';
                                        ?>
                                        <option value="<?= $type ?>" <?= $selected ?>><?= ucfirst(str_replace('_', ' ', $type)) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary" onclick="prevSection(3)">
                                <i class="ri-arrow-left-line me-1"></i> Previous
                            </button>
                            <button type="button" class="btn btn-warning" onclick="nextSection(5)">
                                Next: Application Rules <i class="ri-arrow-right-line ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 5: Application Rules -->
            <div class="config-section d-none" id="section-5">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="ri-calendar-event-line me-2"></i>
                            Step 5: Application Rules & Constraints
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-secondary">
                            <i class="ri-file-edit-line me-2"></i>
                            Configure how employees can apply for this leave
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="minNoticeDaysAppRules" class="form-label fw-semibold">Minimum Notice</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="minNoticeDaysAppRules" name="minNoticeDays"
                                               min="0" max="90" value="<?= $isEdit && isset($policy->minNoticeDays) ? $policy->minNoticeDays : '0' ?>">
                                        <span class="input-group-text">days</span>
                                    </div>
                                    <div class="form-text">Days before leave starts (0 = same day)</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="maxAdvanceBooking" class="form-label fw-semibold">Max Advance Booking</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="maxAdvanceBooking" name="maxAdvanceBooking"
                                               min="0" max="365" placeholder="90"
                                               value="<?= $isEdit && isset($policy->maxAdvanceBooking) ? $policy->maxAdvanceBooking : '' ?>">
                                        <span class="input-group-text">days</span>
                                    </div>
                                    <div class="form-text">How far ahead can be booked</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="allowBackdated" class="form-label fw-semibold">Backdated Applications</label>
                                    <select class="form-select" id="allowBackdated" name="allowBackdated">
                                        <option value="N" <?= ($isEdit && isset($policy->allowBackdated) && $policy->allowBackdated == 'N') ? 'selected' : 'selected' ?>>Not Allowed</option>
                                        <option value="Y" <?= ($isEdit && isset($policy->allowBackdated) && $policy->allowBackdated == 'Y') ? 'selected' : '' ?>>Allowed</option>
                                        <option value="approval" <?= ($isEdit && isset($policy->allowBackdated) && $policy->allowBackdated == 'approval') ? 'selected' : '' ?>>With Approval</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="minDaysPerApplication" class="form-label fw-semibold">Minimum Days Per Application</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="minDaysPerApplication" name="minDaysPerApplication"
                                               min="0.5" max="365" step="0.5" value="<?= $isEdit && isset($policy->minDaysPerApplication) ? $policy->minDaysPerApplication : '0.5' ?>">
                                        <span class="input-group-text">days</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="maxDaysPerApplicationAppRules" class="form-label fw-semibold">Maximum Days Per Application</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="maxDaysPerApplicationAppRules" name="maxDaysPerApplication"
                                               min="1" max="365" placeholder="Leave empty for unlimited"
                                               value="<?= $isEdit && isset($policy->maxDaysPerApplication) ? $policy->maxDaysPerApplication : '' ?>">
                                        <span class="input-group-text">days</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="allowHalfDay" name="allowHalfDay" value="Y" <?= ($isEdit && isset($policy->allowHalfDay) && $policy->allowHalfDay == 'Y') ? 'checked' : 'checked' ?>>
                                        <label class="form-check-label fw-semibold" for="allowHalfDay">
                                            Allow Half-Day Applications
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="requireDocumentation" name="requireDocumentation" value="Y" <?= ($isEdit && isset($policy->requireDocumentation) && $policy->requireDocumentation == 'Y') ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-semibold" for="requireDocumentation">
                                            Require Supporting Documentation
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="blackoutPeriods" class="form-label fw-semibold">Blackout Periods (Optional)</label>
                                    <textarea class="form-control" id="blackoutPeriods" name="blackoutPeriods" rows="2"
                                              placeholder="e.g., Dec 15 - Jan 5 (Year-end), Apr 1-15 (Tax season)"><?= $isEdit && isset($policy->blackoutPeriods) ? htmlspecialchars($policy->blackoutPeriods) : '' ?></textarea>
                                    <div class="form-text">Periods when this leave cannot be taken</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary" onclick="prevSection(4)">
                                <i class="ri-arrow-left-line me-1"></i> Previous
                            </button>
                            <button type="button" class="btn btn-danger" onclick="nextSection(6)">
                                Next: Workflows <i class="ri-arrow-right-line ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 6: Workflows & Links -->
            <div class="config-section d-none" id="section-6">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-purple text-white">
                        <h5 class="mb-0">
                            <i class="ri-flow-chart me-2"></i>
                            Step 6: Workflows & Policy Links
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-success">
                            <i class="ri-link me-2"></i>
                            Link this policy to accumulation policies and approval workflows
                        </div>

                        <!-- Approval Workflow Selection -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card bg-light border">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="ri-flow-chart me-2"></i>
                                            Approval Workflow
                                        </h6>
                                        <?php
                                        // Get approval workflows for this entity
                                        $approvalWorkflows = Leave::leave_approval_policies(
                                            array('entityID' => $entityID, 'Lapsed' => 'N', 'isActive' => 'Y'),
                                            false,
                                            $DBConn
                                        ) ?? array();
                                        ?>
                                        <div class="mb-3">
                                            <label for="approvalWorkflowID" class="form-label fw-semibold">
                                                Select Approval Workflow <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select" id="approvalWorkflowID" name="approvalWorkflowID" required>
                                                <option value="">-- Select Workflow --</option>
                                                <?php if (!empty($approvalWorkflows)): ?>
                                                    <?php foreach ($approvalWorkflows as $workflow):
                                                        $selected = ($isEdit && isset($policy->approvalWorkflowID) && $policy->approvalWorkflowID == $workflow->policyID) ? 'selected' :
                                                                    (($isEdit && isset($policy->workflowID) && $policy->workflowID == $workflow->policyID) ? 'selected' : '');
                                                    ?>
                                                    <option value="<?= $workflow->policyID ?>" <?= $selected ?>>
                                                        <?= htmlspecialchars($workflow->policyName) ?>
                                                        <?php if ($workflow->isDefault === 'Y'): ?>
                                                        <span class="badge bg-purple">Default</span>
                                                        <?php endif; ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <option value="" disabled>No workflows available. Please create one first.</option>
                                                <?php endif; ?>
                                            </select>
                                            <div class="form-text">
                                                <a href="<?= $base ?>html/?s=admin&ss=leave&p=approval_workflows" target="_blank" class="text-decoration-none">
                                                    <i class="ri-external-link-line me-1"></i>Manage Approval Workflows
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Delegation Settings -->
                                        <div class="alert alert-info mb-3">
                                            <h6 class="alert-heading mb-2">
                                                <i class="ri-user-settings-line me-2"></i>
                                                Delegation Settings
                                            </h6>
                                            <p class="mb-2 small">Configure how approvals are handled when approvers are unavailable (on leave, etc.)</p>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="enableDelegation" name="enableDelegation" value="Y" <?= ($isEdit && isset($policy->enableDelegation) && $policy->enableDelegation == 'Y') ? 'checked' : 'checked' ?>>
                                                        <label class="form-check-label fw-semibold" for="enableDelegation">
                                                            Enable Automatic Delegation
                                                        </label>
                                                    </div>
                                                    <div class="form-text">Automatically delegate approvals when approver is on leave</div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" id="enableSkipLevel" name="enableSkipLevel" value="Y" <?= ($isEdit && isset($policy->enableSkipLevel) && $policy->enableSkipLevel == 'Y') ? 'checked' : 'checked' ?>>
                                                        <label class="form-check-label fw-semibold" for="enableSkipLevel">
                                                            Enable Skip-Level Approval
                                                        </label>
                                                    </div>
                                                    <div class="form-text">Escalate to manager's manager if delegate unavailable</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label for="delegationPrompt" class="form-label fw-semibold">Delegation Prompt</label>
                                                    <select class="form-select" id="delegationPrompt" name="delegationPrompt">
                                                        <option value="always" <?= ($isEdit && isset($policy->delegationPrompt) && $policy->delegationPrompt == 'always') ? 'selected' : 'selected' ?>>Always prompt manager to assign delegate when booking leave</option>
                                                        <option value="optional" <?= ($isEdit && isset($policy->delegationPrompt) && $policy->delegationPrompt == 'optional') ? 'selected' : '' ?>>Optional - Manager can assign delegate anytime</option>
                                                        <option value="auto" <?= ($isEdit && isset($policy->delegationPrompt) && $policy->delegationPrompt == 'auto') ? 'selected' : '' ?>>Automatic - System assigns delegate automatically</option>
                                                    </select>
                                                    <div class="form-text">When should managers be prompted to assign delegates?</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Accumulation Policy Link (moved from separate section) -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card bg-light border">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="ri-refresh-line me-2"></i>
                                            Accumulation Policy Link
                                        </h6>
                                        <div class="mb-3">
                                            <label for="accumulationPolicyID" class="form-label fw-semibold">Accumulation Policy</label>
                                            <select class="form-select" id="accumulationPolicyID" name="accumulationPolicyID">
                                                <option value="">None - Manual Allocation</option>
                                                <?php if (!empty($accumulationPolicies)): ?>
                                                    <?php foreach ($accumulationPolicies as $ap):
                                                        $selected = ($isEdit && isset($policy->accumulationPolicyID) && $policy->accumulationPolicyID == $ap->policyID) ? 'selected' :
                                                                    (($isEdit && isset($policy->accumulationPolicy) && $policy->accumulationPolicy->policyID == $ap->policyID) ? 'selected' : '');
                                                    ?>
                                                    <option value="<?= $ap->policyID ?>" <?= $selected ?>>
                                                        <?= htmlspecialchars($ap->policyName) ?> (<?= $ap->accrualType ?>)
                                                    </option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                            <div class="form-text">
                                                <a href="<?= $base ?>html/?s=admin&ss=leave&p=accumulation_policies" target="_blank" class="text-decoration-none">
                                                    <i class="ri-external-link-line me-1"></i>Manage Accumulation Policies
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-secondary" onclick="prevSection(5)">
                                <i class="ri-arrow-left-line me-1"></i> Previous
                            </button>
                            <div>
                                <button type="button" class="btn btn-outline-primary me-2" onclick="saveDraft()">
                                    <i class="ri-save-line me-1"></i> Save as Draft
                                </button>
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="ri-check-double-line me-1"></i> Create Complete Policy
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>
</div>

<style>
.config-stepper {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.config-stepper .step {
    position: relative;
    opacity: 0.5;
    cursor: pointer;
    transition: all 0.3s ease;
}

.config-stepper .step.active,
.config-stepper .step.completed {
    opacity: 1;
}

.config-stepper .step-icon {
    width: 50px;
    height: 50px;
    margin: 0 auto 0.5rem;
    background: #e9ecef;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #6c757d;
}

.config-stepper .step.active .step-icon {
    background: linear-gradient(135deg, #0052CC 0%, #0065FF 100%);
    color: white;
}

.config-stepper .step.completed .step-icon {
    background: #00875A;
    color: white;
}

.config-stepper .step-label {
    font-size: 0.875rem;
    font-weight: 500;
}

.bg-purple {
    background: linear-gradient(135deg, #6f42c1 0%, #8b5cf6 100%);
}
</style>

<script>
// Calculate accrual rate based on method
document.getElementById('accrualMethod')?.addEventListener('change', calculateAccrualRate);
document.getElementById('annualEntitlement')?.addEventListener('input', calculateAccrualRate);

function calculateAccrualRate() {
    const annual = parseFloat(document.getElementById('annualEntitlement').value) || 0;
    const method = document.getElementById('accrualMethod').value;
    let rate = 0;

    switch(method) {
        case 'monthly': rate = annual / 12; break;
        case 'quarterly': rate = annual / 4; break;
        case 'biannual': rate = annual / 2; break;
        case 'upfront':
        case 'anniversary': rate = annual; break;
    }

    document.getElementById('accrualRate').value = rate.toFixed(2);
}

// Generate code from name
function generateCode() {
    const name = document.getElementById('leaveTypeName').value;
    const code = name.split(' ').map(word => word.charAt(0)).join('').toUpperCase();
    document.getElementById('leaveTypeCode').value = code.substring(0, 15);
}

// Navigation between sections
function nextSection(section) {
    // Validate current section before moving forward
    const currentSection = document.querySelector('.config-section:not(.d-none)');
    if (!validateSection(currentSection)) {
        return;
    }

    showSection(section);
}

function prevSection(section) {
    showSection(section);
}

function showSection(section) {
    // Hide all sections
    document.querySelectorAll('.config-section').forEach(s => s.classList.add('d-none'));

    // Show target section
    document.getElementById(`section-${section}`).classList.remove('d-none');

    // Update stepper
    document.querySelectorAll('.config-stepper .step').forEach((step, index) => {
        step.classList.remove('active');
        if (index + 1 < section) {
            step.classList.add('completed');
        } else {
            step.classList.remove('completed');
        }
    });

    document.querySelector(`.config-stepper .step[data-step="${section}"]`).classList.add('active');

    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function validateSection(section) {
    const inputs = section.querySelectorAll('[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });

    return isValid;
}

function saveDraft() {
    // Add draft indicator and submit
    const form = document.getElementById('policyConfigForm');
    const draftInput = document.createElement('input');
    draftInput.type = 'hidden';
    draftInput.name = 'isDraft';
    draftInput.value = 'Y';
    form.appendChild(draftInput);
    form.submit();
}

// Load leave type details when selected
function loadLeaveTypeDetails() {
    const leaveTypeID = document.getElementById('leaveTypeID')?.value;
    if (!leaveTypeID) {
        const detailsDiv = document.getElementById('leaveTypeDetails');
        if (detailsDiv) detailsDiv.classList.add('d-none');
        return;
    }

    // Fetch leave type details via AJAX
    fetch('<?= $base ?>php/scripts/leave/config/get_leave_type.php?leaveTypeID=' + leaveTypeID)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.leaveType) {
                const nameEl = document.getElementById('selectedLeaveTypeName');
                const codeEl = document.getElementById('selectedLeaveTypeCode');
                const descEl = document.getElementById('selectedLeaveTypeDescription');

                if (nameEl) nameEl.textContent = data.leaveType.leaveTypeName || '-';
                if (codeEl) codeEl.textContent = data.leaveType.leaveTypeCode || '-';
                if (descEl) descEl.textContent = data.leaveType.leaveTypeDescription || 'No description';

                // Auto-fill form fields
                const nameInput = document.getElementById('leaveTypeName');
                const codeInput = document.getElementById('leaveTypeCode');
                const descInput = document.getElementById('leaveTypeDescription');

                if (nameInput) nameInput.value = data.leaveType.leaveTypeName || '';
                if (codeInput) codeInput.value = data.leaveType.leaveTypeCode || '';
                if (descInput) descInput.value = data.leaveType.leaveTypeDescription || '';

                const detailsDiv = document.getElementById('leaveTypeDetails');
                if (detailsDiv) detailsDiv.classList.remove('d-none');
            }
        })
        .catch(error => {
            console.error('Error loading leave type:', error);
        });
}

// Toggle accrual override section
function toggleAccrualOverride() {
    const accrualPolicySelect = document.getElementById('accumulationPolicyID');
    const overrideSection = document.getElementById('accrualOverrideSection');

    if (accrualPolicySelect && overrideSection) {
        if (accrualPolicySelect.value) {
            overrideSection.classList.remove('d-none');
        } else {
            overrideSection.classList.add('d-none');
        }
    }
}

// Initialize
calculateAccrualRate();

// Handle override checkboxes on page load
document.addEventListener('DOMContentLoaded', function() {
    const overrideRateCheckbox = document.getElementById('overrideAccrualRate');
    const overrideRateInput = document.getElementById('overrideRateInput');
    const overrideProRatedCheckbox = document.getElementById('overrideProRated');
    const overrideProRatedInput = document.getElementById('overrideProRatedInput');

    if (overrideRateCheckbox && overrideRateInput) {
        overrideRateCheckbox.addEventListener('change', function() {
            if (this.checked) {
                overrideRateInput.classList.remove('d-none');
            } else {
                overrideRateInput.classList.add('d-none');
            }
        });
    }

    if (overrideProRatedCheckbox && overrideProRatedInput) {
        overrideProRatedCheckbox.addEventListener('change', function() {
            if (this.checked) {
                overrideProRatedInput.classList.remove('d-none');
            } else {
                overrideProRatedInput.classList.add('d-none');
            }
        });
    }

    // Initialize accrual override section
    toggleAccrualOverride();

    // If leave type is pre-selected, load its details
    const leaveTypeSelect = document.getElementById('leaveTypeID');
    if (leaveTypeSelect && leaveTypeSelect.value) {
        loadLeaveTypeDetails();
    }

    // Initialize carry-over toggle
    const allowCarryOverCheckbox = document.getElementById('allowCarryOver');
    const carryOverOptions = document.getElementById('carryOverOptions');
    if (allowCarryOverCheckbox && carryOverOptions) {
        carryOverOptions.style.display = allowCarryOverCheckbox.checked ? 'block' : 'none';
        allowCarryOverCheckbox.addEventListener('change', function() {
            carryOverOptions.style.display = this.checked ? 'block' : 'none';
        });
    }
});
</script>

