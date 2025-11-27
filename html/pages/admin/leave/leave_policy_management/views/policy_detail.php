<?php
/**
 * Comprehensive Leave Policy Detail View
 * Displays complete policy configuration with all sections
 */

if (!$policy) {
    echo '<div class="alert alert-danger">
        <i class="ri-error-warning-line me-2"></i>
        Policy not found or has been deleted.
        <a href="<?= $base ?>html/?s=admin&ss=leave&p=leave_policies&action=list" class="alert-link">Return to list</a>
    </div>';
    return;
}

$isEdit = $action === 'edit';
?>

<!-- Back Navigation -->
<div class="mb-3">
    <a href="<?= $base ?>html/?s=admin&ss=leave&p=leave_policies&action=list" class="btn btn-outline-secondary">
        <i class="ri-arrow-left-line me-1"></i> Back to Policies
    </a>
</div>

<!-- Policy Header Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-start">
                    <div class="policy-icon me-3">
                        <i class="ri-file-list-3-line display-4 text-primary"></i>
                    </div>
                    <div>
                        <h2 class="mb-1"><?= htmlspecialchars($policy->leaveTypeName) ?></h2>
                        <div class="mb-2">
                            <?php if (!empty($policy->leaveTypeCode)): ?>
                            <span class="badge bg-primary me-2">
                                <i class="ri-hashtag"></i><?= htmlspecialchars($policy->leaveTypeCode) ?>
                            </span>
                            <?php endif; ?>
                            <span class="badge bg-<?= $policy->Suspended === 'Y' ? 'warning' : 'success' ?>">
                                <?= $policy->Suspended === 'Y' ? 'Suspended' : 'Active' ?>
                            </span>
                        </div>
                        <?php if (!empty($policy->leaveTypeDescription)): ?>
                        <p class="text-muted mb-0"><?= htmlspecialchars($policy->leaveTypeDescription) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <div class="btn-group" role="group">
                    <?php if (!$isEdit): ?>
                    <a href="<?= $base ?>html/?s=admin&ss=leave&p=leave_policies&action=edit&policyID=<?= $policy->leaveTypeID ?>"
                       class="btn btn-primary">
                        <i class="ri-edit-line me-1"></i> Configure Policy
                    </a>
                    <?php endif; ?>
                    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="ri-printer-line"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($isEdit):

    // var_dump($isEdit);
    ?>
    <!-- Edit Mode: Include the form -->
    <?php include 'policy_form.php'; ?>
<?php else: ?>
    <!-- View Mode: Display all configuration -->

    <!-- Configuration Tabs -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <ul class="nav nav-tabs card-header-tabs" role="tablist" id="policyTabs">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#tab-basic" role="tab" data-tab="basic">
                        <i class="ri-file-list-line me-1"></i> Basic Info
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-entitlements" role="tab" data-tab="entitlements">
                        <i class="ri-calendar-check-line me-1"></i> Entitlements
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-carry-over" role="tab" data-tab="carry-over">
                        <i class="ri-refresh-line me-1"></i> Carry-Over
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-eligibility" role="tab" data-tab="eligibility">
                        <i class="ri-shield-check-line me-1"></i> Eligibility
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-rules" role="tab" data-tab="rules">
                        <i class="ri-calendar-event-line me-1"></i> Application Rules
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-workflows" role="tab" data-tab="workflows">
                        <i class="ri-flow-chart me-1"></i> Workflows
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tab-usage" role="tab" data-tab="usage">
                        <i class="ri-bar-chart-line me-1"></i> Usage Stats
                    </a>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="policyTabContent">
                <!-- Tab 1: Basic Information -->
                <div class="tab-pane fade show active" id="tab-basic" role="tabpanel">
                    <h5 class="mb-3">
                        <i class="ri-information-line me-2 text-primary"></i>
                        Basic Information
                    </h5>

                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Policy Name:</th>
                                    <td><?= htmlspecialchars($policy->leaveTypeName) ?></td>
                                </tr>
                                <tr>
                                    <th>Policy Code:</th>
                                    <td><code><?= htmlspecialchars($policy->leaveTypeCode) ?></code></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge bg-<?= $policy->Suspended === 'Y' ? 'warning' : 'success' ?>">
                                            <?= $policy->Suspended === 'Y' ? 'Suspended' : 'Active' ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Paid Leave:</th>
                                    <td><?= !empty($policy->isPaidLeave) && $policy->isPaidLeave === 'Y' ? 'Yes' : 'No' ?></td>
                                </tr>
                                <tr>
                                    <th>Requires Approval:</th>
                                    <td><?= !empty($policy->requiresApproval) && $policy->requiresApproval === 'N' ? 'No (Auto-Approved)' : 'Yes' ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Created Date:</th>
                                    <td><?= date('M d, Y H:i', strtotime($policy->CreateDate ?? $policy->LastUpdate)) ?></td>
                                </tr>
                                <tr>
                                    <th>Last Updated:</th>
                                    <td><?= date('M d, Y H:i', strtotime($policy->LastUpdate)) ?></td>
                                </tr>
                                <tr>
                                    <th>Updated By:</th>
                                    <td>
                                        <?php
                                        $updater = Employee::employees(['ID' => $policy->LastUpdateByID], true, $DBConn);
                                        echo $updater ? htmlspecialchars($updater->FirstName . ' ' . $updater->Surname) : 'System';
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Entitlements -->
                <div class="tab-pane fade" id="tab-entitlements" role="tabpanel">
                    <h5 class="mb-4">
                        <i class="ri-calendar-check-line me-2 text-success"></i>
                        Entitlement & Accrual Rules
                    </h5>

                    <?php
                    // Prepare entitlements data
                    $entitlementsList = array();
                    if (!empty($policy->entitlements)) {
                        foreach ($policy->entitlements as $entitlement) {
                            if (is_object($entitlement) || (is_array($entitlement) && !empty($entitlement))) {
                                $entitlementsList[] = is_object($entitlement) ? $entitlement : (object)$entitlement;
                            }
                        }
                    }
                    $hasEntitlements = !empty($entitlementsList);
                    $hasAccumulationPolicy = !empty($policy->accumulationPolicy);
                    $hasAccumulationRules = !empty($policy->accumulationRules) && is_array($policy->accumulationRules);
                    ?>

                    <!-- Summary Statistics -->
                    <?php if ($hasEntitlements || $hasAccumulationPolicy): ?>
                    <div class="row mb-4">
                        <?php if ($hasEntitlements): ?>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                                <div class="card-body text-center">
                                    <i class="ri-calendar-check-line display-6 text-primary mb-2"></i>
                                    <h3 class="text-primary mb-1"><?= count($entitlementsList) ?></h3>
                                    <p class="text-muted mb-0 small">Entitlement<?= count($entitlementsList) > 1 ? 's' : '' ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                                <div class="card-body text-center">
                                    <i class="ri-calendar-line display-6 text-success mb-2"></i>
                                    <h3 class="text-success mb-1"><?= isset($entitlementsList[0]->entitlement) ? $entitlementsList[0]->entitlement : '0' ?></h3>
                                    <p class="text-muted mb-0 small">Days/Year</p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($hasAccumulationPolicy): ?>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                                <div class="card-body text-center">
                                    <i class="ri-refresh-line display-6 text-info mb-2"></i>
                                    <h3 class="text-info mb-1"><?= htmlspecialchars($policy->accumulationPolicy->accrualType ?? 'N/A') ?></h3>
                                    <p class="text-muted mb-0 small">Accrual Type</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                                <div class="card-body text-center">
                                    <i class="ri-speed-line display-6 text-warning mb-2"></i>
                                    <h3 class="text-warning mb-1"><?= isset($policy->accumulationPolicy->accrualRate) ? $policy->accumulationPolicy->accrualRate : '0' ?></h3>
                                    <p class="text-muted mb-0 small">Days/Period</p>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Entitlements Section -->
                    <div class="mb-4">
                        <h6 class="mb-3 d-flex align-items-center">
                            <i class="ri-file-list-3-line me-2 text-primary"></i>
                            Leave Entitlements
                            <?php if ($hasEntitlements): ?>
                            <span class="badge bg-primary ms-2"><?= count($entitlementsList) ?></span>
                            <?php endif; ?>
                        </h6>

                        <?php if ($hasEntitlements): ?>
                            <?php foreach ($entitlementsList as $index => $entitlement): ?>
                            <div class="card border mb-3">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">
                                            <i class="ri-building-line me-2 text-primary"></i>
                                            Entitlement <?= $index + 1 ?>
                                            <?php if (!empty($entitlement->entityName)): ?>
                                            <span class="badge bg-info ms-2"><?= htmlspecialchars($entitlement->entityName) ?></span>
                                            <?php endif; ?>
                                        </h6>
                                    </div>
                                    <div>
                                        <?php if (isset($entitlement->Suspended) && $entitlement->Suspended === 'Y'): ?>
                                        <span class="badge bg-warning">Suspended</span>
                                        <?php else: ?>
                                        <span class="badge bg-success">Active</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless mb-0">
                                                <tr>
                                                    <th width="45%" class="text-muted">Annual Entitlement:</th>
                                                    <td>
                                                        <span class="fw-bold text-primary fs-5"><?= isset($entitlement->entitlement) ? number_format($entitlement->entitlement, 0) : '0' ?></span>
                                                        <span class="text-muted ms-1">days/year</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted">Max Per Application:</th>
                                                    <td>
                                                        <?php if (isset($entitlement->maxDaysPerApplication) && $entitlement->maxDaysPerApplication > 0): ?>
                                                        <span class="fw-semibold"><?= number_format($entitlement->maxDaysPerApplication, 0) ?> days</span>
                                                        <?php else: ?>
                                                        <span class="text-muted">Unlimited</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted">Minimum Notice:</th>
                                                    <td>
                                                        <span class="fw-semibold"><?= isset($entitlement->minNoticeDays) ? $entitlement->minNoticeDays : '0' ?></span>
                                                        <span class="text-muted ms-1">day<?= (isset($entitlement->minNoticeDays) && $entitlement->minNoticeDays != 1) ? 's' : '' ?></span>
                                                        <?php if (empty($entitlement->minNoticeDays) || $entitlement->minNoticeDays == 0): ?>
                                                        <span class="badge bg-info ms-2">Same-day allowed</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless mb-0">
                                                <tr>
                                                    <th width="45%" class="text-muted">Entitlement ID:</th>
                                                    <td><code><?= isset($entitlement->leaveEntitlementID) ? $entitlement->leaveEntitlementID : 'N/A' ?></code></td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted">Created:</th>
                                                    <td>
                                                        <?php if (isset($entitlement->DateAdded)): ?>
                                                        <?= date('M d, Y', strtotime($entitlement->DateAdded)) ?>
                                                        <?php else: ?>
                                                        <span class="text-muted">N/A</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted">Last Updated:</th>
                                                    <td>
                                                        <?php if (isset($entitlement->LastUpdate)): ?>
                                                        <?= date('M d, Y H:i', strtotime($entitlement->LastUpdate)) ?>
                                                        <?php else: ?>
                                                        <span class="text-muted">N/A</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-warning border-0 shadow-sm">
                                <div class="d-flex align-items-start">
                                    <i class="ri-alert-line me-2 fs-4"></i>
                                    <div class="flex-grow-1">
                                        <h6 class="alert-heading mb-2">No Entitlements Configured</h6>
                                        <p class="mb-2">This leave policy does not have any entitlements configured. Entitlements define the annual leave allocation for employees.</p>
                                        <a href="<?= $base ?>html/?s=admin&ss=leave&p=leave_entitlements&leaveTypeID=<?= $policy->leaveTypeID ?>" class="btn btn-sm btn-warning">
                                            <i class="ri-add-circle-line me-1"></i> Configure Entitlements
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Accrual Policy Section -->
                    <div class="mb-4">
                        <h6 class="mb-3 d-flex align-items-center">
                            <i class="ri-refresh-line me-2 text-info"></i>
                            Accrual & Accumulation Policy
                        </h6>

                        <?php if ($hasAccumulationPolicy): ?>
                            <div class="card border">
                                <div class="card-header bg-info bg-opacity-10 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">
                                            <i class="ri-file-paper-2-line me-2"></i>
                                            <?= htmlspecialchars($policy->accumulationPolicy->policyName ?? 'Accumulation Policy') ?>
                                        </h6>
                                    </div>
                                    <div>
                                        <a href="<?= $base ?>html/?s=admin&ss=leave&p=accumulation_policies&action=view&policyID=<?= $policy->accumulationPolicy->policyID ?>"
                                           class="btn btn-sm btn-outline-info" target="_blank">
                                            <i class="ri-external-link-line me-1"></i> View Details
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless mb-0">
                                                <tr>
                                                    <th width="50%" class="text-muted">Accrual Type:</th>
                                                    <td>
                                                        <span class="badge bg-info"><?= htmlspecialchars($policy->accumulationPolicy->accrualType ?? 'N/A') ?></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted">Accrual Rate:</th>
                                                    <td>
                                                        <span class="fw-bold text-primary fs-5"><?= isset($policy->accumulationPolicy->accrualRate) ? number_format($policy->accumulationPolicy->accrualRate, 2) : '0' ?></span>
                                                        <span class="text-muted ms-1">days per <?= strtolower($policy->accumulationPolicy->accrualType ?? 'period') ?></span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted">Pro-Rated:</th>
                                                    <td>
                                                        <?php if (isset($policy->accumulationPolicy->proRated) && $policy->accumulationPolicy->proRated === 'Y'): ?>
                                                        <span class="badge bg-success">Yes</span>
                                                        <small class="text-muted ms-2">Accrual adjusted for partial periods</small>
                                                        <?php else: ?>
                                                        <span class="badge bg-secondary">No</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted">Max Carryover:</th>
                                                    <td>
                                                        <?php if (isset($policy->accumulationPolicy->maxCarryover) && $policy->accumulationPolicy->maxCarryover > 0): ?>
                                                        <span class="fw-semibold"><?= $policy->accumulationPolicy->maxCarryover ?></span> days
                                                        <?php else: ?>
                                                        <span class="text-muted">Unlimited</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless mb-0">
                                                <tr>
                                                    <th width="50%" class="text-muted">Carryover Expiry:</th>
                                                    <td>
                                                        <?php if (isset($policy->accumulationPolicy->carryoverExpiryMonths) && $policy->accumulationPolicy->carryoverExpiryMonths > 0): ?>
                                                        <span class="fw-semibold"><?= $policy->accumulationPolicy->carryoverExpiryMonths ?></span> months
                                                        <?php else: ?>
                                                        <span class="text-muted">No expiry</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted">Effective From:</th>
                                                    <td>
                                                        <?php if (isset($policy->accumulationPolicy->accrualStartDate)): ?>
                                                        <?= date('M d, Y', strtotime($policy->accumulationPolicy->accrualStartDate)) ?>
                                                        <?php else: ?>
                                                        <span class="text-muted">Immediate</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted">Effective Until:</th>
                                                    <td>
                                                        <?php if (isset($policy->accumulationPolicy->accrualEndDate)): ?>
                                                        <?= date('M d, Y', strtotime($policy->accumulationPolicy->accrualEndDate)) ?>
                                                        <?php else: ?>
                                                        <span class="text-muted">Indefinite</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th class="text-muted">Policy Status:</th>
                                                    <td>
                                                        <?php if (isset($policy->accumulationPolicy->isActive) && $policy->accumulationPolicy->isActive === 'Y'): ?>
                                                        <span class="badge bg-success">Active</span>
                                                        <?php else: ?>
                                                        <span class="badge bg-warning">Inactive</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Accumulation Rules -->
                            <?php if ($hasAccumulationRules): ?>
                            <div class="card border mt-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="ri-rules-line me-2 text-warning"></i>
                                        Accumulation Rules
                                        <span class="badge bg-warning ms-2"><?= count($policy->accumulationRules) ?></span>
                                    </h6>
                                    <small class="text-muted">Rules that modify accrual rates based on conditions</small>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Rule Name</th>
                                                    <th>Type</th>
                                                    <th>Condition</th>
                                                    <th>Multiplier</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($policy->accumulationRules as $rule): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= htmlspecialchars($rule->ruleName ?? 'N/A') ?></strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary"><?= htmlspecialchars($rule->ruleType ?? 'N/A') ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($rule->conditionField) && !empty($rule->conditionValue)): ?>
                                                        <code><?= htmlspecialchars($rule->conditionField) ?></code>
                                                        <?= htmlspecialchars($rule->conditionOperator ?? '>=') ?>
                                                        <code><?= htmlspecialchars($rule->conditionValue) ?></code>
                                                        <?php else: ?>
                                                        <span class="text-muted">No condition</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="fw-bold text-primary"><?= isset($rule->accrualMultiplier) ? number_format($rule->accrualMultiplier, 2) : '1.00' ?>x</span>
                                                    </td>
                                                    <td>
                                                        <?php if (isset($rule->Suspended) && $rule->Suspended === 'Y'): ?>
                                                        <span class="badge bg-warning">Suspended</span>
                                                        <?php else: ?>
                                                        <span class="badge bg-success">Active</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                        <?php else: ?>
                            <div class="alert alert-warning border-0 shadow-sm">
                                <div class="d-flex align-items-start">
                                    <i class="ri-alert-line me-2 fs-4"></i>
                                    <div class="flex-grow-1">
                                        <h6 class="alert-heading mb-2">No Accumulation Policy Linked</h6>
                                        <p class="mb-2">This leave policy does not have an accumulation policy configured. Without an accumulation policy, entitlements must be allocated manually.</p>
                                        <a href="<?= $base ?>html/?s=admin&ss=leave&p=accumulation_policies&action=create&leaveTypeID=<?= $policy->leaveTypeID ?>" class="btn btn-sm btn-warning">
                                            <i class="ri-add-circle-line me-1"></i> Create Accumulation Policy
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tab 3: Carry-Over -->
                <div class="tab-pane fade" id="tab-carry-over" role="tabpanel">
                    <h5 class="mb-3">
                        <i class="ri-refresh-line me-2 text-info"></i>
                        Carry-Over Policies
                    </h5>

                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Allow Carry-Over:</th>
                            <td>
                                <?php
                                $allowCarryOver = !empty($policy->allowCarryOver) && $policy->allowCarryOver === 'Y';
                                echo $allowCarryOver ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>';
                                ?>
                            </td>
                        </tr>
                        <?php if ($allowCarryOver): ?>
                        <tr>
                            <th>Maximum Carry-Over:</th>
                            <td><?= !empty($policy->maxCarryOver) ? $policy->maxCarryOver . ' days' : 'Unlimited' ?></td>
                        </tr>
                        <tr>
                            <th>Carry-Over Expiry:</th>
                            <td><?= !empty($policy->carryOverExpiry) ? $policy->carryOverExpiry . ' months' : 'No expiry' ?></td>
                        </tr>
                        <tr>
                            <th>Use It or Lose It:</th>
                            <td><?= !empty($policy->useItOrLoseIt) && $policy->useItOrLoseIt === 'Y' ? 'Yes' : 'No' ?></td>
                        </tr>
                        <tr>
                            <th>Allow Cash-Out:</th>
                            <td><?= !empty($policy->allowCashout) && $policy->allowCashout === 'Y' ? 'Yes' : 'No' ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>

                <!-- Tab 4: Eligibility -->
                <div class="tab-pane fade" id="tab-eligibility" role="tabpanel">
                    <h5 class="mb-3">
                        <i class="ri-shield-check-line me-2 text-warning"></i>
                        Eligibility Criteria
                    </h5>

                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Minimum Service Period:</th>
                            <td><?= !empty($policy->minServicePeriod) ? $policy->minServicePeriod . ' months' : 'Immediate (0 months)' ?></td>
                        </tr>
                        <tr>
                            <th>During Probation:</th>
                            <td>
                                <?= !empty($policy->excludeProbation) && $policy->excludeProbation === 'Y'
                                    ? '<span class="badge bg-danger">Excluded</span>'
                                    : '<span class="badge bg-success">Available</span>'
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Gender Restriction:</th>
                            <td><?= ucfirst($policy->genderRestriction ?? 'All Employees') ?></td>
                        </tr>
                        <tr>
                            <th>Employment Type:</th>
                            <td><?= !empty($policy->employmentType) ? htmlspecialchars($policy->employmentType) : 'All Types' ?></td>
                        </tr>
                    </table>
                </div>

                <!-- Tab 5: Application Rules -->
                <div class="tab-pane fade" id="tab-rules" role="tabpanel">
                    <h5 class="mb-3">
                        <i class="ri-calendar-event-line me-2 text-danger"></i>
                        Application Rules & Constraints
                    </h5>

                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Minimum Notice:</th>
                            <td><?= $policy->minNoticeDays ?? '0' ?> days <?= empty($policy->minNoticeDays) ? '(Same-day applications allowed)' : '' ?></td>
                        </tr>
                        <tr>
                            <th>Maximum Advance Booking:</th>
                            <td><?= !empty($policy->maxAdvanceBooking) ? $policy->maxAdvanceBooking . ' days' : 'Unlimited' ?></td>
                        </tr>
                        <tr>
                            <th>Backdated Applications:</th>
                            <td>
                                <?php
                                $backdated = $policy->allowBackdated ?? 'N';
                                if ($backdated === 'Y') echo '<span class="badge bg-success">Allowed</span>';
                                elseif ($backdated === 'approval') echo '<span class="badge bg-warning">With Approval</span>';
                                else echo '<span class="badge bg-danger">Not Allowed</span>';
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Days Per Application:</th>
                            <td>
                                Min: <?= $policy->minDaysPerApplication ?? '0.5' ?> days |
                                Max: <?= !empty($policy->maxDaysPerApplication) ? $policy->maxDaysPerApplication . ' days' : 'Unlimited' ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Half-Day Applications:</th>
                            <td><?= empty($policy->allowHalfDay) || $policy->allowHalfDay === 'Y' ? '<span class="badge bg-success">Allowed</span>' : '<span class="badge bg-danger">Not Allowed</span>' ?></td>
                        </tr>
                        <tr>
                            <th>Documentation Required:</th>
                            <td><?= !empty($policy->requireDocumentation) && $policy->requireDocumentation === 'Y' ? '<span class="badge bg-warning">Yes</span>' : 'No' ?></td>
                        </tr>
                        <?php if (!empty($policy->blackoutPeriods)): ?>
                        <tr>
                            <th>Blackout Periods:</th>
                            <td><?= nl2br(htmlspecialchars($policy->blackoutPeriods)) ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>

                <!-- Tab 6: Workflows & Links -->
                <div class="tab-pane fade" id="tab-workflows" role="tabpanel">
                    <h5 class="mb-3">
                        <i class="ri-flow-chart me-2 text-purple"></i>
                        Workflows & Policy Links
                    </h5>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="ri-refresh-line me-1"></i> Accumulation Policy
                                    </h6>
                                    <?php if (!empty($policy->accumulationPolicy)): ?>
                                        <p class="mb-0">
                                            <a href="<?= $base ?>html/?s=admin&ss=leave&p=accumulation_policies&action=view&policyID=<?= $policy->accumulationPolicy->policyID ?>">
                                                <?= htmlspecialchars($policy->accumulationPolicy->policyName) ?>
                                                <i class="ri-external-link-line"></i>
                                            </a>
                                        </p>
                                        <small class="text-muted">
                                            <?= $policy->accumulationPolicy->accrualType ?> accrual at <?= $policy->accumulationPolicy->accrualRate ?> days/period
                                        </small>
                                    <?php else: ?>
                                        <p class="text-muted mb-0">Not linked</p>
                                        <a href="<?= $base ?>html/?s=admin&ss=leave&p=accumulation_policies&action=create&leaveTypeID=<?= $policy->leaveTypeID ?>" class="btn btn-sm btn-outline-primary mt-2">
                                            Create Link
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="ri-flow-chart me-1"></i> Approval Workflow
                                    </h6>
                                    <p class="text-muted mb-0">Using default workflow</p>
                                    <a href="<?= $base ?>html/?s=admin&ss=leave&p=approval_workflows" class="btn btn-sm btn-outline-primary mt-2">
                                        Manage Workflows
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 7: Usage Statistics -->
                <div class="tab-pane fade" id="tab-usage" role="tabpanel">
                    <h5 class="mb-3">
                        <i class="ri-bar-chart-line me-2 text-primary"></i>
                        Usage Statistics
                    </h5>

                    <?php
                    $applications = Leave::leave_applications(['leaveTypeID' => $policy->leaveTypeID], false, $DBConn);
                    $totalApplications = $applications ? count($applications) : 0;
                    $approvedApps = $applications ? count(array_filter($applications, function($app) { return $app->leaveStatusID == 2; })) : 0;
                    $pendingApps = $applications ? count(array_filter($applications, function($app) { return $app->leaveStatusID == 1; })) : 0;
                    $rejectedApps = $applications ? count(array_filter($applications, function($app) { return $app->leaveStatusID == 3; })) : 0;
                    ?>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-primary bg-opacity-10 border-0">
                                <div class="card-body text-center">
                                    <h2 class="text-primary"><?= $totalApplications ?></h2>
                                    <p class="mb-0">Total Applications</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success bg-opacity-10 border-0">
                                <div class="card-body text-center">
                                    <h2 class="text-success"><?= $approvedApps ?></h2>
                                    <p class="mb-0">Approved</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning bg-opacity-10 border-0">
                                <div class="card-body text-center">
                                    <h2 class="text-warning"><?= $pendingApps ?></h2>
                                    <p class="mb-0">Pending</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger bg-opacity-10 border-0">
                                <div class="card-body text-center">
                                    <h2 class="text-danger"><?= $rejectedApps ?></h2>
                                    <p class="mb-0">Rejected</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($totalApplications > 0): ?>
                    <div class="mt-3">
                        <a href="<?= $base ?>html/?s=admin&ss=leave&p=reports&leaveTypeID=<?= $policy->leaveTypeID ?>" class="btn btn-outline-primary">
                            <i class="ri-file-chart-line me-1"></i>
                            View Detailed Reports
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
(function() {
    // URL-managed tab navigation
    const tabMap = {
        'basic': '#tab-basic',
        'entitlements': '#tab-entitlements',
        'carry-over': '#tab-carry-over',
        'eligibility': '#tab-eligibility',
        'rules': '#tab-rules',
        'workflows': '#tab-workflows',
        'usage': '#tab-usage'
    };

    // Function to get tab name from URL hash
    function getTabFromHash() {
        const hash = window.location.hash;
        if (hash) {
            // Remove #tab- and find matching tab
            const tabId = hash.replace('#tab-', '');
            if (tabMap[tabId]) {
                return tabId;
            }
        }
        return 'basic'; // Default tab
    }

    // Function to activate a tab using Bootstrap's tab API
    function activateTab(tabName) {
        const tabLink = document.querySelector(`[data-tab="${tabName}"]`);

        if (tabLink) {
            // Use Bootstrap's tab API if available
            if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                const tab = new bootstrap.Tab(tabLink);
                tab.show();
            } else {
                // Fallback: manual activation
                document.querySelectorAll('#policyTabs .nav-link').forEach(link => {
                    link.classList.remove('active');
                });
                document.querySelectorAll('#policyTabContent .tab-pane').forEach(pane => {
                    pane.classList.remove('show', 'active');
                });

                tabLink.classList.add('active');
                const tabPane = document.querySelector(tabMap[tabName]);
                if (tabPane) {
                    tabPane.classList.add('show', 'active');
                }
            }

            // Update URL hash without triggering scroll
            const newHash = tabMap[tabName];
            if (window.location.hash !== newHash) {
                history.replaceState(null, null, newHash);
            }
        }
    }

    // Initialize tab on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Set default tab if no hash exists
        if (!window.location.hash) {
            history.replaceState(null, null, '#tab-basic');
        }

        const activeTab = getTabFromHash();
        activateTab(activeTab);

        // Handle tab clicks - update URL when Bootstrap tab is shown
        document.querySelectorAll('#policyTabs .nav-link').forEach(link => {
            link.addEventListener('shown.bs.tab', function(e) {
                const tabName = this.getAttribute('data-tab');
                if (tabName && tabMap[tabName]) {
                    const newHash = tabMap[tabName];
                    if (window.location.hash !== newHash) {
                        history.replaceState(null, null, newHash);
                    }
                }
            });
        });

        // Handle browser back/forward buttons
        window.addEventListener('hashchange', function() {
            const activeTab = getTabFromHash();
            activateTab(activeTab);
        });
    });
})();
</script>
