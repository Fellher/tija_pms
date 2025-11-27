<!-- Policies List View -->
<div class="row">
    <!-- Search and Filter Section -->
    <div class="col-12 mb-4">
        <div class="search-container">
            <div class="row">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="ri-search-line"></i>
                        </span>
                        <input type="text" id="searchInput" class="form-control search-input"
                               placeholder="Search policies by name or description...">
                    </div>
                </div>
                <div class="col-md-4">
                    <select id="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="col-12 mb-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="stats-icon mx-auto">
                            <i class="ri-settings-3-line"></i>
                        </div>
                        <div class="stats-number"><?= count($policies) ?></div>
                        <div class="stats-label">Total Policies</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="stats-icon mx-auto">
                            <i class="ri-check-line"></i>
                        </div>
                        <div class="stats-number">
                            <?= count(array_filter($policies, function($policy) { return $policy->status === 'active'; })) ?>
                        </div>
                        <div class="stats-label">Active Policies</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="stats-icon mx-auto">
                            <i class="ri-pause-line"></i>
                        </div>
                        <div class="stats-number">
                            <?= count(array_filter($policies, function($policy) { return $policy->status === 'inactive'; })) ?>
                        </div>
                        <div class="stats-label">Inactive Policies</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="stats-icon mx-auto">
                            <i class="ri-list-check"></i>
                        </div>
                        <div class="stats-number">
                            <?php
                            $totalRules = 0;
                            foreach ($policies as $policy) {
                                $rules = AccumulationPolicy::get_policy_rules($policy->policyID, false, $DBConn);
                                $totalRules += count($rules);
                            }
                            echo $totalRules;
                            ?>
                        </div>
                        <div class="stats-label">Total Rules</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Policies Grid -->
    <div class="col-12">
        <div id="policiesContainer" class="row">
            <?php if (!empty($policies)): ?>
                <?php foreach ($policies as $index => $policy): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="policy-card" data-policy-id="<?= $policy->policyID ?>">
                        <div class="policy-card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="policy-name"><?= htmlspecialchars($policy->policyName) ?></h5>
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
                            <div class="policy-description mb-3">
                                <p class="text-muted mb-0"><?= htmlspecialchars($policy->description) ?></p>
                            </div>
                            <?php endif; ?>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <div class="policy-stat">
                                        <div class="stat-label">Accrual Rate</div>
                                        <div class="stat-value"><?= $policy->accrualRate ?> days</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="policy-stat">
                                        <div class="stat-label">Max Days</div>
                                        <div class="stat-value"><?= $policy->maxDays ?? 'Unlimited' ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="ri-time-line me-1"></i>
                                    Created: <?= date('M d, Y', strtotime($policy->createdDate)) ?>
                                </small>

                                <div class="action-buttons">
                                    <a href="?action=view_policy&policyID=<?= $policy->policyID ?>"
                                       class="btn btn-outline-primary btn-sm action-btn"
                                       data-bs-toggle="tooltip" title="View Details">
                                        <i class="ri-eye-line"></i>
                                    </a>

                                    <a href="?action=edit_policy&policyID=<?= $policy->policyID ?>"
                                       class="btn btn-outline-success btn-sm action-btn"
                                       data-bs-toggle="tooltip" title="Edit">
                                        <i class="ri-edit-line"></i>
                                    </a>

                                    <button type="button"
                                            class="btn btn-outline-<?= $policy->status === 'active' ? 'warning' : 'success' ?> btn-sm action-btn"
                                            data-action="toggle-policy-status"
                                            data-policy-id="<?= $policy->policyID ?>"
                                            data-policy-status="<?= $policy->status ?>"
                                            data-bs-toggle="tooltip"
                                            title="<?= $policy->status === 'active' ? 'Deactivate' : 'Activate' ?>">
                                        <i class="ri-<?= $policy->status === 'active' ? 'pause' : 'play' ?>-line"></i>
                                    </button>

                                    <a href="?action=delete_policy&policyID=<?= $policy->policyID ?>"
                                       class="btn btn-outline-danger btn-sm action-btn delete-policy"
                                       data-name="<?= htmlspecialchars($policy->policyName) ?>"
                                       data-bs-toggle="tooltip" title="Delete">
                                        <i class="ri-delete-bin-line"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div id="emptyState" class="empty-state">
                        <div class="empty-state-icon">
                            <i class="ri-settings-3-line"></i>
                        </div>
                        <h4 class="empty-state-title">No Policies Found</h4>
                        <p class="empty-state-description">
                            Get started by creating your first accumulation policy.
                        </p>
                        <a href="?action=create_policy" class="btn btn-primary">
                            <i class="ri-add-line me-1"></i>
                            Create Policy
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
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
});
</script>
