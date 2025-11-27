<?php
/**
 * Accumulation Policy Detail View
 * Displays accumulation policy details based on policyName
 */
?>

<?php if (!$policy): ?>
    <div class="alert alert-danger">
        <i class="ri-error-warning-line me-2"></i>
        Policy not found or has been deleted.
        <a href="<?= $base ?>html/?s=admin&ss=leave&p=accumulation_policies&action=list" class="alert-link">Return to list</a>
    </div>
<?php return; endif; ?>

<!-- Back Navigation -->
<div class="mb-3">
    <a href="<?= $base ?>html/?s=admin&ss=leave&p=accumulation_policies&action=list" class="btn btn-outline-secondary">
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
                        <i class="ri-refresh-line display-4 text-info"></i>
                    </div>
                    <div>
                        <!-- Policy Name is the primary identifier -->
                        <h2 class="mb-1"><?= htmlspecialchars($policy->policyName) ?></h2>
                        <div class="mb-2">
                            <?php
                            $scope = $policy->policyScope ?? 'Entity';
                            $scopeColors = [
                                'Global' => 'danger',
                                'Entity' => 'primary',
                                'Cadre' => 'warning'
                            ];
                            $scopeColor = $scopeColors[$scope] ?? 'secondary';
                            $scopeIcons = [
                                'Global' => 'ri-global-line',
                                'Entity' => 'ri-building-line',
                                'Cadre' => 'ri-group-line'
                            ];
                            $scopeIcon = $scopeIcons[$scope] ?? 'ri-file-line';

                            // Define scopeInfo array for use throughout the template
                            $scopeInfoMap = [
                                'Global' => ['bg' => 'danger', 'text' => 'danger', 'icon' => 'ri-global-line'],
                                'Entity' => ['bg' => 'primary', 'text' => 'primary', 'icon' => 'ri-building-line'],
                                'Cadre' => ['bg' => 'warning', 'text' => 'warning', 'icon' => 'ri-group-line']
                            ];
                            $scopeInfo = $scopeInfoMap[$scope] ?? $scopeInfoMap['Entity'];
                            ?>
                            <span class="badge bg-<?= $scopeColor ?> me-2" title="Policy Scope: <?= $scope ?>">
                                <i class="<?= $scopeIcon ?>"></i> <?= $scope ?>
                            </span>
                            <span class="badge bg-info me-2">
                                <i class="ri-calendar-line"></i><?= htmlspecialchars($policy->leaveTypeName ?? 'N/A') ?>
                            </span>
                            <span class="badge bg-primary me-2">
                                <?= htmlspecialchars($policy->accrualType ?? 'N/A') ?>
                            </span>
                            <span class="badge bg-<?= (($policy->isActive ?? 'Y') === 'Y' && ($policy->Suspended ?? 'N') === 'N' && ($policy->Lapsed ?? 'N') === 'N') ? 'success' : 'warning' ?>">
                                <?= (($policy->isActive ?? 'Y') === 'Y' && ($policy->Suspended ?? 'N') === 'N' && ($policy->Lapsed ?? 'N') === 'N') ? 'Active' : (($policy->Suspended ?? 'N') === 'Y' ? 'Suspended' : 'Lapsed') ?>
                            </span>
                        </div>
                        <?php if (!empty($policy->policyDescription)): ?>
                        <p class="text-muted mb-0"><?= htmlspecialchars($policy->policyDescription) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <div class="btn-group" role="group">
                    <?php if ($action !== 'edit'): ?>
                    <button type="button" class="btn btn-primary" onclick="typeof editAccumulationPolicy === 'function' ? editAccumulationPolicy(<?= $policy->policyID ?>) : window.location.href='<?= $base ?>html/?s=admin&ss=leave&p=accumulation_policies&action=edit&policyID=<?= $policy->policyID ?>'">
                        <i class="ri-edit-line me-1"></i> Edit Policy
                    </button>
                    <?php endif; ?>
                    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="ri-printer-line"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Policy Details Tabs -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab-basic">
                    <i class="ri-file-text-line me-1"></i> Basic Info
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-accrual">
                    <i class="ri-calendar-line me-1"></i> Accrual Settings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-carryover">
                    <i class="ri-refresh-line me-1"></i> Carryover Rules
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-hierarchy">
                    <i class="ri-node-tree me-1"></i> Policy Hierarchy
                </a>
            </li>
            <?php if (!empty($rules)): ?>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-rules">
                    <i class="ri-settings-3-line me-1"></i> Rules (<?= count($rules) ?>)
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="card-body p-4">
        <div class="tab-content">
            <!-- Tab 1: Basic Information -->
            <div class="tab-pane fade show active" id="tab-basic">
                <h5 class="mb-3">
                    <i class="ri-information-line me-2 text-primary"></i>
                    Basic Information
                </h5>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Policy Name:</th>
                                <td><strong><?= htmlspecialchars($policy->policyName) ?></strong></td>
                            </tr>
                            <tr>
                                <th>Leave Type:</th>
                                <td><?= htmlspecialchars($policy->leaveTypeName ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <th>Priority:</th>
                                <td><?= $policy->priority ?? 1 ?></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge bg-<?= (($policy->isActive ?? 'Y') === 'Y' && ($policy->Suspended ?? 'N') === 'N' && ($policy->Lapsed ?? 'N') === 'N') ? 'success' : 'warning' ?>">
                                        <?= (($policy->isActive ?? 'Y') === 'Y' && ($policy->Suspended ?? 'N') === 'N' && ($policy->Lapsed ?? 'N') === 'N') ? 'Active' : (($policy->Suspended ?? 'N') === 'Y' ? 'Suspended' : 'Lapsed') ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Policy ID:</th>
                                <td><?= $policy->policyID ?></td>
                            </tr>
                            <tr>
                                <th>Policy Scope:</th>
                                <td>
                                    <span class="badge bg-<?= $scopeInfo['bg'] ?>">
                                        <i class="<?= $scopeInfo['icon'] ?>"></i> <?= $scope ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Entity:</th>
                                <td>
                                    <?php if ($scope === 'Global'): ?>
                                        <span class="text-muted">All Entities (Global Policy)</span>
                                    <?php else: ?>
                                        <?= htmlspecialchars($entityName ?? $_SESSION['entityName'] ?? 'Entity ' . ($policy->entityID ?? $entityID)) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if ($scope === 'Cadre'): ?>
                            <tr>
                                <th>Job Category:</th>
                                <td><?= htmlspecialchars($jobCategoryName ?? 'Not specified') ?></td>
                            </tr>
                            <tr>
                                <th>Job Band:</th>
                                <td><?= htmlspecialchars($jobBandName ?? 'Not specified') ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <th>Created:</th>
                                <td><?= !empty($policy->DateAdded) ? date('M d, Y H:i', strtotime($policy->DateAdded)) : 'N/A' ?></td>
                            </tr>
                            <tr>
                                <th>Last Updated:</th>
                                <td><?= !empty($policy->LastUpdate) ? date('M d, Y H:i', strtotime($policy->LastUpdate)) : 'N/A' ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php if (!empty($policy->policyDescription)): ?>
                <div class="mt-3">
                    <h6>Description</h6>
                    <p class="text-muted"><?= nl2br(htmlspecialchars($policy->policyDescription)) ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Tab 2: Accrual Settings -->
            <div class="tab-pane fade" id="tab-accrual">
                <h5 class="mb-3">
                    <i class="ri-calendar-line me-2 text-success"></i>
                    Accrual Settings
                </h5>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Accrual Type:</th>
                                <td><strong><?= htmlspecialchars($policy->accrualType ?? 'N/A') ?></strong></td>
                            </tr>
                            <tr>
                                <th>Accrual Rate:</th>
                                <td><strong class="text-primary"><?= number_format($policy->accrualRate ?? 0, 2) ?> days/period</strong></td>
                            </tr>
                            <tr>
                                <th>Pro-Rated:</th>
                                <td><?= ($policy->proRated ?? 'N') === 'Y' ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Start Date:</th>
                                <td><?= !empty($policy->accrualStartDate) ? date('M d, Y', strtotime($policy->accrualStartDate)) : 'Immediate' ?></td>
                            </tr>
                            <tr>
                                <th>End Date:</th>
                                <td><?= !empty($policy->accrualEndDate) ? date('M d, Y', strtotime($policy->accrualEndDate)) : 'Indefinite' ?></td>
                            </tr>
                            <tr>
                                <th>Is Active:</th>
                                <td><?= ($policy->isActive ?? 'Y') === 'Y' ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>' ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Carryover Rules -->
            <div class="tab-pane fade" id="tab-carryover">
                <h5 class="mb-3">
                    <i class="ri-refresh-line me-2 text-info"></i>
                    Carryover Rules
                </h5>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">Max Carryover:</th>
                                <td><?= !empty($policy->maxCarryover) ? $policy->maxCarryover . ' days' : '<span class="text-muted">Unlimited</span>' ?></td>
                            </tr>
                            <tr>
                                <th>Carryover Expiry:</th>
                                <td><?= !empty($policy->carryoverExpiryMonths) ? $policy->carryoverExpiryMonths . ' months' : '<span class="text-muted">Never</span>' ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab 4: Policy Hierarchy -->
            <div class="tab-pane fade" id="tab-hierarchy">
                <h5 class="mb-3">
                    <i class="ri-node-tree me-2 text-primary"></i>
                    Policy Hierarchy & Scope
                </h5>

                <?php
                $scope = $policy->policyScope ?? 'Entity';
                // $scopeInfo is already defined at the top of the file, no need to redefine

                // Get entity name if available
                $entityName = $policy->entityName ?? null;
                if (!$entityName && !empty($policy->entityID)) {
                    $entityData = Data::entities_full(['entityID' => $policy->entityID], true, $DBConn);
                    $entityName = $entityData->entityName ?? 'Entity #' . $policy->entityID;
                }

                // Get job category and band names
                $jobCategoryName = $policy->jobCategoryName ?? null;
                $jobBandName = $policy->jobBandName ?? null;
                if (!$jobCategoryName && !empty($policy->jobCategoryID)) {
                    $catData = Admin::tija_job_categories(['jobCategoryID' => $policy->jobCategoryID], true, $DBConn);
                    $jobCategoryName = $catData->jobCategoryTitle ?? null;
                }
                if (!$jobBandName && !empty($policy->jobBandID)) {
                    $bandData = Admin::tija_job_bands(['jobBandID' => $policy->jobBandID], true, $DBConn);
                    $jobBandName = $bandData->jobBandTitle ?? null;
                }
                ?>

                <!-- Hierarchy Visualization -->
                <div class="hierarchy-visualization mb-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="mb-3">Policy Scope Information</h6>

                            <div class="hierarchy-level mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="hierarchy-icon me-3">
                                        <i class="<?= $scopeInfo['icon'] ?> fs-2 text-<?= $scopeInfo['text'] ?>"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-0">
                                            <span class="badge bg-<?= $scopeInfo['bg'] ?>"><?= $scope ?> Policy</span>
                                        </h5>
                                        <p class="text-muted mb-0 small">
                                            <?php if ($scope === 'Global'): ?>
                                                This policy applies to all entities under the parent organization
                                            <?php elseif ($scope === 'Entity'): ?>
                                                This policy applies to all employees in: <strong><?= htmlspecialchars($entityName ?? 'Selected Entity') ?></strong>
                                            <?php elseif ($scope === 'Cadre'): ?>
                                                This policy applies to specific job categories/bands in: <strong><?= htmlspecialchars($entityName ?? 'Selected Entity') ?></strong>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>

                                <?php if ($scope === 'Entity' || $scope === 'Cadre'): ?>
                                <div class="ms-5 ps-3 border-start border-2 border-<?= $scopeInfo['text'] ?>">
                                    <small class="text-muted d-block mb-1">
                                        <i class="ri-building-line"></i> Entity: <strong><?= htmlspecialchars($entityName ?? 'N/A') ?></strong>
                                    </small>
                                </div>
                                <?php endif; ?>

                                <?php if ($scope === 'Cadre'): ?>
                                <div class="ms-5 ps-3 border-start border-2 border-warning mt-2">
                                    <small class="text-muted d-block mb-1">
                                        <i class="ri-group-line"></i> Cadre Scope:
                                    </small>
                                    <?php if ($jobCategoryName): ?>
                                    <span class="badge bg-info me-1"><?= htmlspecialchars($jobCategoryName) ?></span>
                                    <?php endif; ?>
                                    <?php if ($jobBandName): ?>
                                    <span class="badge bg-info"><?= htmlspecialchars($jobBandName) ?></span>
                                    <?php endif; ?>
                                    <?php if (!$jobCategoryName && !$jobBandName): ?>
                                    <small class="text-muted">Not specified</small>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hierarchy Explanation -->
                <div class="alert alert-info">
                    <h6 class="alert-heading">
                        <i class="ri-information-line me-2"></i>How Policy Hierarchy Works
                    </h6>
                    <p class="mb-2">Policies are resolved in the following order (most specific wins):</p>
                    <ol class="mb-0">
                        <li><strong>Cadre Policies</strong> - Most specific, apply to specific job categories/bands</li>
                        <li><strong>Entity Policies</strong> - Apply to all employees in an entity</li>
                        <li><strong>Global Policies</strong> - Apply to all entities (fallback)</li>
                    </ol>
                    <p class="mb-0 mt-2">
                        <small>When multiple policies match, the system merges them with Cadre > Entity > Global precedence.</small>
                    </p>
                </div>

                <!-- Effective Policy Preview -->
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h6 class="mb-3">Effective Policy Preview</h6>
                        <p class="text-muted small mb-2">
                            This shows how the policy would be resolved for an employee matching this scope:
                        </p>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Setting</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Policy Scope</strong></td>
                                        <td><span class="badge bg-<?= $scopeInfo['bg'] ?>"><?= $scope ?></span></td>
                                    </tr>
                                    <?php if ($scope !== 'Global'): ?>
                                    <tr>
                                        <td><strong>Entity</strong></td>
                                        <td><?= htmlspecialchars($entityName ?? 'N/A') ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if ($scope === 'Cadre'): ?>
                                    <tr>
                                        <td><strong>Job Category</strong></td>
                                        <td><?= htmlspecialchars($jobCategoryName ?? 'Not specified') ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Job Band</strong></td>
                                        <td><?= htmlspecialchars($jobBandName ?? 'Not specified') ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td><strong>Leave Type</strong></td>
                                        <td><?= htmlspecialchars($policy->leaveTypeName ?? 'N/A') ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Accrual Rate</strong></td>
                                        <td><strong class="text-primary"><?= number_format($policy->accrualRate ?? 0, 2) ?> days/period</strong></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Priority</strong></td>
                                        <td><?= $policy->priority ?? 1 ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 5: Rules -->
            <?php if (!empty($rules)): ?>
            <div class="tab-pane fade" id="tab-rules">
                <h5 class="mb-3">
                    <i class="ri-settings-3-line me-2 text-warning"></i>
                    Accumulation Rules
                </h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Rule Name</th>
                                <th>Type</th>
                                <th>Condition</th>
                                <th>Multiplier</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rules as $rule): ?>
                            <tr>
                                <td><?= htmlspecialchars($rule->ruleName ?? 'N/A') ?></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($rule->ruleType ?? 'N/A') ?></span></td>
                                <td>
                                    <?php if (!empty($rule->conditionField)): ?>
                                        <?= htmlspecialchars($rule->conditionField) ?>
                                        <?= htmlspecialchars($rule->conditionOperator ?? '>=') ?>
                                        <?= htmlspecialchars($rule->conditionValue ?? '') ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= number_format($rule->accrualMultiplier ?? 1.00, 2) ?>x</td>
                                <td>
                                    <span class="badge bg-<?= (($rule->Suspended ?? 'N') === 'N' && ($rule->Lapsed ?? 'N') === 'N') ? 'success' : 'secondary' ?>">
                                        <?= (($rule->Suspended ?? 'N') === 'N' && ($rule->Lapsed ?? 'N') === 'N') ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

