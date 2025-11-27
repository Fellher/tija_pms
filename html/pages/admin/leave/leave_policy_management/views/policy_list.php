<?php
/**
 * Comprehensive Leave Policies List View
 * Displays all leave policies with their complete configuration status
 */
?>

<!-- Search and Filter Section -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="input-group">
            <span class="input-group-text">
                <i class="ri-search-line"></i>
            </span>
            <input type="text" id="searchInput" class="form-control"
                   placeholder="Search policies by name, code, or description...">
        </div>
    </div>
    <div class="col-md-2">
        <select id="statusFilter" class="form-select">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="suspended">Suspended</option>
        </select>
    </div>
    <div class="col-md-2">
        <select id="completionFilter" class="form-select">
            <option value="">All Policies</option>
            <option value="complete">Fully Configured</option>
            <option value="incomplete">Needs Configuration</option>
        </select>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stats-card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="stats-icon mx-auto mb-2 bg-primary bg-opacity-10 rounded-circle p-3">
                    <i class="ri-file-list-line text-primary fs-3"></i>
                </div>
                <div class="stats-number text-primary fw-bold fs-3"><?= count($policies ?? []) ?></div>
                <div class="stats-label text-muted">Total Policies</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="stats-icon mx-auto mb-2 bg-success bg-opacity-10 rounded-circle p-3">
                    <i class="ri-checkbox-circle-line text-success fs-3"></i>
                </div>
                <div class="stats-number text-success fw-bold fs-3">
                    <?= count(array_filter($policies ?? [], function($policy) { return $policy->Suspended === 'N'; })) ?>
                </div>
                <div class="stats-label text-muted">Active Policies</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="stats-icon mx-auto mb-2 bg-warning bg-opacity-10 rounded-circle p-3">
                    <i class="ri-settings-3-line text-warning fs-3"></i>
                </div>
                <div class="stats-number text-warning fw-bold fs-3">
                    <?php
                    $needsConfig = 0;
                    if ($policies) {
                        foreach ($policies as $policy) {
                            $entitlements = Leave::leave_entitlements(['leaveTypeID' => $policy->leaveTypeID], false, $DBConn);
                            if (!$entitlements || count($entitlements) === 0) {
                                $needsConfig++;
                            }
                        }
                    }
                    echo $needsConfig;
                    ?>
                </div>
                <div class="stats-label text-muted">Need Configuration</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="stats-icon mx-auto mb-2 bg-info bg-opacity-10 rounded-circle p-3">
                    <i class="ri-pause-circle-line text-info fs-3"></i>
                </div>
                <div class="stats-number text-info fw-bold fs-3">


                    <?= count(array_filter($policies ?? [], function($policy) { return $policy->Suspended === 'Y'; })) ?>
                </div>
                <div class="stats-label text-muted">Suspended</div>
            </div>
        </div>
    </div>
</div>

<!-- Leave Policies Grid -->
<div class="row" id="policiesContainer">

    <?php

    if (!empty($policies)): ?>
        <?php foreach ($policies as $policy):

            // Get policy configuration status
            $entitlements = Leave::leave_entitlements(['leaveTypeID' => $policy->leaveTypeID, 'Suspended' => 'N'], false, $DBConn);
            $hasEntitlements = $entitlements && count($entitlements) > 0;
            $accPolicy = null;
            if (!empty($accumulationPolicies)) {
                foreach ($accumulationPolicies as $ap) {
                    if ($ap->leaveTypeID == $policy->leaveTypeID) {
                        $accPolicy = $ap;
                        break;
                    }
                }
            }
            $hasAccumulation = $accPolicy !== null;

            // Calculate configuration completion
            $configSteps = 0;
            $completedSteps = 0;

            // Basic info (always complete if policy exists)
            $configSteps++; $completedSteps++;

            // Entitlements
            $configSteps++;
            if ($hasEntitlements) $completedSteps++;

            // Accumulation
            $configSteps++;
            if ($hasAccumulation) $completedSteps++;

            // Application rules (check if max/min days set)
            $configSteps++;
            if (!empty($policy->maxDaysPerApplication)) $completedSteps++;

            $completionPercentage = ($completedSteps / $configSteps) * 100;
            $completionClass = $completionPercentage == 100 ? 'success' : ($completionPercentage >= 50 ? 'warning' : 'danger');
        ?>
        <div class="col-md-6 col-lg-4 mb-4" data-policy-id="<?= $policy->leaveTypeID ?>">
            <div class="card policy-card h-100 border-0 shadow-sm hover-shadow">
                <div class="card-header bg-gradient-primary text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-1 fw-bold text-white"><?= htmlspecialchars($policy->leaveTypeName) ?></h5>
                            <?php if (!empty($policy->leaveTypeCode)): ?>
                            <span class="badge bg-white bg-opacity-25 text-dark">
                                <i class="ri-hashtag"></i><?= htmlspecialchars($policy->leaveTypeCode) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        <span class="status-badge badge bg-<?= $policy->Suspended === 'Y' ? 'warning' : 'success' ?> bg-opacity-25">
                            <?= $policy->Suspended === 'Y' ? 'Suspended' : 'Active' ?>
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Configuration Progress -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted fw-semibold">Configuration Status</small>
                            <small class="text-<?= $completionClass ?> fw-bold"><?= number_format($completionPercentage, 0) ?>%</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-<?= $completionClass ?>"
                                 role="progressbar"
                                 style="width: <?= $completionPercentage ?>%"
                                 aria-valuenow="<?= $completionPercentage ?>"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>

                    <!-- Configuration Checklist -->
                    <div class="config-checklist mb-3">
                        <div class="checklist-item d-flex align-items-center mb-2">
                            <i class="ri-checkbox-circle-fill text-success me-2"></i>
                            <small>Basic Information</small>
                        </div>
                        <div class="checklist-item d-flex align-items-center mb-2">
                            <i class="ri-<?= $hasEntitlements ? 'checkbox-circle-fill text-success' : 'checkbox-blank-circle-line text-muted' ?> me-2"></i>
                            <small class="<?= !$hasEntitlements ? 'text-muted' : '' ?>">Entitlements Configured</small>
                        </div>
                        <div class="checklist-item d-flex align-items-center mb-2">
                            <i class="ri-<?= $hasAccumulation ? 'checkbox-circle-fill text-success' : 'checkbox-blank-circle-line text-muted' ?> me-2"></i>
                            <small class="<?= !$hasAccumulation ? 'text-muted' : '' ?>">Accumulation Policy</small>
                        </div>
                        <div class="checklist-item d-flex align-items-center mb-2">
                            <i class="ri-<?= !empty($policy->maxDaysPerApplication) ? 'checkbox-circle-fill text-success' : 'checkbox-blank-circle-line text-muted' ?> me-2"></i>
                            <small class="<?= empty($policy->maxDaysPerApplication) ? 'text-muted' : '' ?>">Application Rules</small>
                        </div>
                    </div>

                    <?php if (!empty($policy->leaveTypeDescription)): ?>
                    <p class="text-muted small mb-3" style="min-height: 40px; max-height: 60px; overflow: hidden;">
                        <?= htmlspecialchars(substr($policy->leaveTypeDescription, 0, 100)) ?>
                        <?= strlen($policy->leaveTypeDescription) > 100 ? '...' : '' ?>
                    </p>
                    <?php endif; ?>

                    <!-- Quick Stats -->
                    <?php if ($hasEntitlements): ?>
                    <div class="quick-stats border-top pt-2">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="stat-value fw-bold text-primary"><?= $entitlements[0]->entitlement ?? 'N/A' ?></div>
                                <div class="stat-label text-muted small">Days/Year</div>
                            </div>
                            <div class="col-6">
                                <div class="stat-value fw-bold text-info"><?= count($entitlements) ?></div>
                                <div class="stat-label text-muted small">Tier<?= count($entitlements) > 1 ? 's' : '' ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="card-footer bg-light border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="ri-time-line me-1"></i>
                            <?= date('M d, Y', strtotime($policy->LastUpdate)) ?>
                        </small>

                        <div class="btn-group btn-group-sm" role="group">
                            <a href="<?= $base ?>html/?s=admin&ss=leave&p=leave_policies&action=view&policyID=<?= $policy->leaveTypeID ?>"
                               class="btn btn-outline-primary"
                               data-bs-toggle="tooltip" title="View Policy">
                                <i class="ri-eye-line"></i>
                            </a>

                            <a href="<?= $base ?>html/?s=admin&ss=leave&p=leave_policies&action=edit&policyID=<?= $policy->leaveTypeID ?>"
                               class="btn btn-outline-success"
                               data-bs-toggle="tooltip" title="Configure Policy">
                                <i class="ri-settings-3-line"></i>
                            </a>

                            <button type="button"
                                    class="btn btn-outline-<?= $policy->Suspended === 'Y' ? 'success' : 'warning' ?>"
                                    onclick="togglePolicyStatus(<?= $policy->leaveTypeID ?>, '<?= $policy->Suspended ?>')"
                                    data-bs-toggle="tooltip"
                                    title="<?= $policy->Suspended === 'Y' ? 'Activate' : 'Suspend' ?>">
                                <i class="ri-<?= $policy->Suspended === 'Y' ? 'play' : 'pause' ?>-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="empty-state text-center py-5">
                <div class="empty-state-icon mb-4">
                    <i class="ri-file-list-3-line display-1 text-muted"></i>
                </div>
                <h4 class="empty-state-title mb-3">No Leave Policies Found</h4>
                <p class="empty-state-description text-muted mb-4">
                    Get started by creating your first comprehensive leave policy with entitlements, rules, and workflows.
                </p>
                <a href="<?= $base ?>html/?s=admin&ss=leave&p=leave_policies&action=create" class="btn btn-primary btn-lg">
                    <i class="ri-add-line me-2"></i>
                    Create Your First Policy
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #0052CC 0%, #0065FF 100%);
}

.hover-shadow {
    transition: all 0.3s ease;
}

.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
}

.policy-card .card-header {
    border-bottom: none;
}

.stats-card {
    transition: all 0.3s ease;
}

.stats-card .stats-icon {
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.stats-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15) !important;
}

.checklist-item {
    font-size: 0.875rem;
}

.quick-stats .stat-value {
    font-size: 1.25rem;
}

.quick-stats .stat-label {
    font-size: 0.75rem;
}
</style>

<script>
// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Search functionality
    document.getElementById('searchInput')?.addEventListener('keyup', filterPolicies);
    document.getElementById('statusFilter')?.addEventListener('change', filterPolicies);
    document.getElementById('completionFilter')?.addEventListener('change', filterPolicies);
});

function filterPolicies() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const completionFilter = document.getElementById('completionFilter').value;

    const policies = document.querySelectorAll('[data-policy-id]');

    policies.forEach(policy => {
        const text = policy.textContent.toLowerCase();
        const matchesSearch = text.includes(searchTerm);

        // Status filter logic would go here
        const matchesStatus = true; // Implement based on your needs
        const matchesCompletion = true; // Implement based on your needs

        if (matchesSearch && matchesStatus && matchesCompletion) {
            policy.style.display = '';
        } else {
            policy.style.display = 'none';
        }
    });
}

// Toggle policy status
function togglePolicyStatus(policyID, currentStatus) {
    if (confirm('Are you sure you want to ' + (currentStatus === 'Y' ? 'activate' : 'suspend') + ' this policy?')) {
        window.location.href = '<?= $base ?>html/?s=admin&ss=leave&p=leave_policies&action=toggle_status&policyID=' + policyID;
    }
}
</script>

