<?php
/**
 * Accumulation Policies List View
 * Displays accumulation policies by policyName (not leaveTypeName)
 * Dedicated view for tija_leave_accumulation_policies table
 */
?>

<!-- Search and Filter Section -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text">
                <i class="ri-search-line"></i>
            </span>
            <input type="text" id="searchAccumulationInput" class="form-control"
                   placeholder="Search by policy name, leave type, or accrual type...">
        </div>
    </div>
    <div class="col-md-2">
        <select id="scopeFilter" class="form-select">
            <option value="">All Scopes</option>
            <option value="Global">Global</option>
            <option value="Entity">Entity</option>
            <option value="Cadre">Cadre</option>
        </select>
    </div>
    <div class="col-md-2">
        <select id="statusFilterAccumulation" class="form-select">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="suspended">Suspended</option>
            <option value="lapsed">Lapsed</option>
        </select>
    </div>
    <div class="col-md-2">
        <select id="accrualTypeFilter" class="form-select">
            <option value="">All Accrual Types</option>
            <option value="Monthly">Monthly</option>
            <option value="Quarterly">Quarterly</option>
            <option value="Annual">Annual</option>
            <option value="Continuous">Continuous</option>
        </select>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stats-card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="stats-icon mx-auto mb-2 bg-primary bg-opacity-10 rounded-circle p-3">
                    <i class="ri-refresh-line text-primary fs-3"></i>
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
                    <?= count(array_filter($policies ?? [], function($policy) { return ($policy->isActive ?? 'Y') === 'Y' && ($policy->Suspended ?? 'N') === 'N' && ($policy->Lapsed ?? 'N') === 'N'; })) ?>
                </div>
                <div class="stats-label text-muted">Active Policies</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="stats-icon mx-auto mb-2 bg-info bg-opacity-10 rounded-circle p-3">
                    <i class="ri-calendar-line text-info fs-3"></i>
                </div>
                <div class="stats-number text-info fw-bold fs-3">
                    <?= count(array_filter($policies ?? [], function($policy) { return ($policy->accrualType ?? '') === 'Monthly'; })) ?>
                </div>
                <div class="stats-label text-muted">Monthly Accrual</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stats-card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="stats-icon mx-auto mb-2 bg-warning bg-opacity-10 rounded-circle p-3">
                    <i class="ri-pause-circle-line text-warning fs-3"></i>
                </div>
                <div class="stats-number text-warning fw-bold fs-3">
                    <?= count(array_filter($policies ?? [], function($policy) { return ($policy->Suspended ?? 'N') === 'Y'; })) ?>
                </div>
                <div class="stats-label text-muted">Suspended</div>
            </div>
        </div>
    </div>
</div>

<!-- Accumulation Policies Grid -->
<div class="row" id="accumulationPoliciesContainer">
    <?php if (!empty($policies)): ?>
        <?php foreach ($policies as $policy):
            $isActive = ($policy->isActive ?? 'Y') === 'Y' && ($policy->Suspended ?? 'N') === 'N' && ($policy->Lapsed ?? 'N') === 'N';
            $statusClass = $isActive ? 'success' : (($policy->Suspended ?? 'N') === 'Y' ? 'warning' : 'secondary');
            $statusText = $isActive ? 'Active' : (($policy->Suspended ?? 'N') === 'Y' ? 'Suspended' : 'Lapsed');
        ?>
        <div class="col-md-6 col-lg-4 mb-4"
            data-policy-id="<?= $policy->policyID ?>"
            data-policy-name="<?= strtolower(htmlspecialchars($policy->policyName)) ?>"
            data-leave-type="<?= strtolower(htmlspecialchars($policy->leaveTypeName ?? '')) ?>"
            data-accrual-type="<?= htmlspecialchars($policy->accrualType ?? '') ?>"
            data-status="<?= $statusText ?>"
            data-priority="<?= $policy->priority ?? 1 ?>"
            data-policy-scope="<?= htmlspecialchars($policy->policyScope ?? 'Entity') ?>">
            <div class="card accumulation-policy-card h-100 border-0 shadow-sm hover-shadow">
                <div class="card-header bg-gradient-info text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1 policy-name-container d-flex flex-column">
                            <!-- Policy Name is the primary identifier -->
                            <h5 class="card-title mb-1 fw-bold text-white"><?= htmlspecialchars($policy->policyName) ?></h5>
                            <div class="d-flex flex-wrap gap-1 mt-1">
                                <!-- Policy Scope Badge -->
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
                                ?>
                                <span class="badge bg-<?= $scopeColor ?> bg-opacity-75" title="Policy Scope: <?= $scope ?>">
                                    <i class="<?= $scopeIcon ?>"></i> <?= $scope ?>
                                </span>
                                <span class="badge bg-white bg-opacity-25 text-dark">
                                    <i class="ri-calendar-line"></i> <?= htmlspecialchars($policy->leaveTypeName ?? 'N/A') ?>
                                </span>
                                <span class="badge bg-white bg-opacity-25 text-dark">
                                    <?= htmlspecialchars($policy->accrualType ?? 'N/A') ?>
                                </span>
                            </div>
                        </div>
                        <span class="status-badge badge bg-<?= $statusClass ?>">
                            <?= $statusText ?>
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Policy Scope Information -->
                    <div class="policy-scope-info mb-3 p-2 bg-light rounded">
                        <?php if ($scope === 'Global'): ?>
                            <small class="text-muted d-block mb-1">
                                <i class="ri-global-line text-danger"></i> <strong>Global Policy</strong>
                            </small>
                            <small class="text-muted">Applies to all entities</small>
                        <?php elseif ($scope === 'Entity'): ?>
                            <small class="text-muted d-block mb-1">
                                <i class="ri-building-line text-primary"></i> <strong>Entity Policy</strong>
                            </small>
                            <small class="text-muted"><?= htmlspecialchars($policy->entityName ?? 'Entity #' . ($policy->entityID ?? 'N/A')) ?></small>
                        <?php elseif ($scope === 'Cadre'): ?>
                            <small class="text-muted d-block mb-1">
                                <i class="ri-group-line text-warning"></i> <strong>Cadre Policy</strong>
                            </small>
                            <small class="text-muted">
                                <?= htmlspecialchars($policy->entityName ?? 'Entity #' . ($policy->entityID ?? 'N/A')) ?>
                                <?php if (!empty($policy->jobCategoryName) || !empty($policy->jobBandName)): ?>
                                    <br>
                                    <?php if (!empty($policy->jobCategoryName)): ?>
                                        <span class="badge bg-info"><?= htmlspecialchars($policy->jobCategoryName) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($policy->jobBandName)): ?>
                                        <span class="badge bg-info"><?= htmlspecialchars($policy->jobBandName) ?></span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </small>
                        <?php endif; ?>
                    </div>

                    <!-- Policy Details -->
                    <div class="policy-details mb-3">
                        <div class="detail-item d-flex justify-content-between mb-2">
                            <small class="text-muted">Accrual Rate:</small>
                            <strong class="text-primary"><?= number_format($policy->accrualRate ?? 0, 2) ?> days/period</strong>
                        </div>
                        <?php if (!empty($policy->maxCarryover)): ?>
                        <div class="detail-item d-flex justify-content-between mb-2">
                            <small class="text-muted">Max Carryover:</small>
                            <strong><?= $policy->maxCarryover ?> days</strong>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($policy->carryoverExpiryMonths)): ?>
                        <div class="detail-item d-flex justify-content-between mb-2">
                            <small class="text-muted">Carryover Expiry:</small>
                            <strong><?= $policy->carryoverExpiryMonths ?> months</strong>
                        </div>
                        <?php endif; ?>
                        <div class="detail-item d-flex justify-content-between mb-2">
                            <small class="text-muted">Pro-Rated:</small>
                            <strong><?= ($policy->proRated ?? 'N') === 'Y' ? 'Yes' : 'No' ?></strong>
                        </div>
                        <div class="detail-item d-flex justify-content-between">
                            <small class="text-muted">Priority:</small>
                            <strong><?= $policy->priority ?? 1 ?></strong>
                        </div>
                    </div>

                    <?php if (!empty($policy->policyDescription)): ?>
                    <p class="text-muted small mb-3" style="min-height: 40px; max-height: 60px; overflow: hidden;">
                        <?= htmlspecialchars(substr($policy->policyDescription, 0, 100)) ?>
                        <?= strlen($policy->policyDescription) > 100 ? '...' : '' ?>
                    </p>
                    <?php endif; ?>

                </div>

                <div class="card-footer bg-light border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="ri-time-line me-1"></i>
                            <?= !empty($policy->LastUpdate) ? date('M d, Y', strtotime($policy->LastUpdate)) : date('M d, Y', strtotime($policy->DateAdded ?? 'now')) ?>
                        </small>

                        <div class="btn-group btn-group-sm" role="group">
                            <a href="<?= $base ?>html/?s=admin&ss=leave&p=accumulation_policies&action=view&policyID=<?= $policy->policyID ?>"
                               class="btn btn-outline-primary"
                               data-bs-toggle="tooltip" title="View Policy">
                                <i class="ri-eye-line"></i>
                            </a>

                            <button type="button"
                                    class="btn btn-outline-success"
                                    data-action="edit"
                                    data-policy-id="<?= $policy->policyID ?>"
                                    data-edit-url="<?= $base ?>html/?s=admin&ss=leave&p=accumulation_policies&action=edit&policyID=<?= $policy->policyID ?>"
                                    data-bs-toggle="tooltip" title="Edit Policy">
                                <i class="ri-edit-line"></i>
                            </button>

                            <button type="button"
                                    class="btn btn-outline-<?= ($policy->isActive ?? 'Y') === 'Y' ? 'warning' : 'success' ?>"
                                    data-action="toggle-status"
                                    data-policy-id="<?= $policy->policyID ?>"
                                    data-policy-name="<?= htmlspecialchars($policy->policyName, ENT_QUOTES) ?>"
                                    data-new-status="<?= ($policy->isActive ?? 'Y') === 'Y' ? 'N' : 'Y' ?>"
                                    data-bs-toggle="tooltip"
                                    title="<?= ($policy->isActive ?? 'Y') === 'Y' ? 'Deactivate' : 'Activate' ?>">
                                <i class="ri-<?= ($policy->isActive ?? 'Y') === 'Y' ? 'pause' : 'play' ?>-line"></i>
                            </button>

                            <button type="button"
                                    class="btn btn-outline-danger"
                                    data-action="delete"
                                    data-policy-id="<?= $policy->policyID ?>"
                                    data-policy-name="<?= htmlspecialchars($policy->policyName, ENT_QUOTES) ?>"
                                    data-delete-url="<?= $base ?>html/?s=admin&ss=leave&p=accumulation_policies&action=delete&policyID=<?= $policy->policyID ?>"
                                    data-bs-toggle="tooltip" title="Delete Policy">
                                <i class="ri-delete-bin-line"></i>
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
                    <i class="ri-refresh-line display-1 text-muted"></i>
                </div>
                <h4 class="empty-state-title mb-3">No Accumulation Policies Found</h4>
                <p class="empty-state-description text-muted mb-4">
                    Create accumulation policies to define how leave accrues for employees.
                </p>
                <button type="button"
                        class="btn btn-primary btn-lg"
                        data-action="create"
                        data-create-url="<?= $base ?>html/?s=admin&ss=leave&p=accumulation_policies&action=create">
                    <i class="ri-add-line me-2"></i>
                    Create Your First Accumulation Policy
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- SweetAlert2 Library -->
<link rel="stylesheet" href="<?= $base ?>assets/libs/sweetalert2/sweetalert2.min.css">
<script src="<?= $base ?>assets/libs/sweetalert2/sweetalert2.all.min.js"></script>

<script>
// Search and filter functionality
document.addEventListener('DOMContentLoaded', function() {
    // Ensure SweetAlert is loaded
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 is not loaded. Please ensure the library is included.');
        // Fallback to native alerts if SweetAlert is not available
        window.Swal = {
            fire: function(options) {
                if (options.showCancelButton) {
                    return Promise.resolve({
                        isConfirmed: confirm(options.title + '\n\n' + (options.html || options.text || ''))
                    });
                } else {
                    alert(options.title + '\n\n' + (options.html || options.text || ''));
                    return Promise.resolve({ isConfirmed: true });
                }
            }
        };
    }
    const searchInput = document.getElementById('searchAccumulationInput');
    const statusFilter = document.getElementById('statusFilterAccumulation');
    const accrualTypeFilter = document.getElementById('accrualTypeFilter');
    const scopeFilter = document.getElementById('scopeFilter');
    const container = document.getElementById('accumulationPoliciesContainer');

    function filterPolicies() {
        const searchTerm = (searchInput?.value || '').toLowerCase();
        const statusValue = statusFilter?.value || '';
        const accrualValue = accrualTypeFilter?.value || '';
        const scopeValue = scopeFilter?.value || '';

        const cards = container?.querySelectorAll('.accumulation-policy-card').forEach(card => {
            const cardElement = card.closest('.col-md-6');
            if (!cardElement) return;

            const policyName = (cardElement.dataset.policyName || '').toLowerCase();
            const leaveType = (cardElement.dataset.leaveType || '').toLowerCase();
            const accrualType = cardElement.dataset.accrualType || '';
            const status = cardElement.dataset.status || '';
            const policyScope = cardElement.dataset.policyScope || 'Entity';

            const matchesSearch = !searchTerm ||
                policyName.includes(searchTerm) ||
                leaveType.includes(searchTerm) ||
                accrualType.toLowerCase().includes(searchTerm);

            const matchesStatus = !statusValue || status.toLowerCase() === statusValue.toLowerCase();
            const matchesAccrual = !accrualValue || accrualType === accrualValue;
            const matchesScope = !scopeValue || policyScope === scopeValue;

            if (matchesSearch && matchesStatus && matchesAccrual && matchesScope) {
                cardElement.style.display = '';
            } else {
                cardElement.style.display = 'none';
            }
        });
    }

    if (searchInput) searchInput.addEventListener('input', filterPolicies);
    if (statusFilter) statusFilter.addEventListener('change', filterPolicies);
    if (accrualTypeFilter) accrualTypeFilter.addEventListener('change', filterPolicies);
    if (scopeFilter) scopeFilter.addEventListener('change', filterPolicies);

    // Delegated event handling for policy actions
    document.addEventListener('click', function(e) {
        const button = e.target.closest('[data-action]');
        if (!button) return;

        const action = button.dataset.action;
        const policyId = button.dataset.policyId;
        const policyName = button.dataset.policyName;

        switch (action) {
            case 'edit':
                e.preventDefault();
                if (typeof editAccumulationPolicy === 'function') {
                    editAccumulationPolicy(policyId ? parseInt(policyId) : 0);
                } else {
                    window.location.href = button.dataset.editUrl || button.dataset.createUrl;
                }
                break;

            case 'create':
                e.preventDefault();
                if (typeof editAccumulationPolicy === 'function') {
                    editAccumulationPolicy(0);
                } else {
                    window.location.href = button.dataset.createUrl;
                }
                break;

            case 'toggle-status':
                e.preventDefault();
                const newStatus = button.dataset.newStatus;
                const isActivating = newStatus === 'Y';
                const actionText = isActivating ? 'activate' : 'deactivate';
                const actionTextCapitalized = isActivating ? 'Activate' : 'Deactivate';

                Swal.fire({
                    title: `${actionTextCapitalized} Policy?`,
                    html: `
                        <div class="text-start">
                            <p class="mb-3">Are you sure you want to ${actionText} the policy <strong>"${policyName}"</strong>?</p>
                            ${!isActivating ? '<p class="text-warning mb-0"><strong>This will deactivate the policy and may affect leave accruals.</strong></p>' : ''}
                        </div>
                    `,
                    icon: isActivating ? 'question' : 'warning',
                    showCancelButton: true,
                    confirmButtonColor: isActivating ? '#198754' : '#ffc107',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: `<i class="ri-${isActivating ? 'play' : 'pause'}-line me-1"></i> Yes, ${actionTextCapitalized}`,
                    cancelButtonText: '<i class="ri-close-line me-1"></i> Cancel',
                    reverseButtons: true,
                    focusConfirm: false,
                    focusCancel: true,
                    customClass: {
                        confirmButton: `btn btn-${isActivating ? 'success' : 'warning'}`,
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (typeof toggleAccumulationPolicyStatus === 'function') {
                            toggleAccumulationPolicyStatus(parseInt(policyId), newStatus);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Function not loaded. Please refresh the page and try again.',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    }
                });
                break;

            case 'delete':
                e.preventDefault();
                Swal.fire({
                    title: 'Delete Policy?',
                    html: `
                        <div class="text-start">
                            <p class="mb-3">Are you sure you want to delete the policy <strong>"${policyName}"</strong>?</p>
                            <p class="mb-2 text-danger"><strong>This will permanently delete:</strong></p>
                            <ul class="text-start mb-3">
                                <li>The accumulation policy</li>
                                <li>All associated rules</li>
                                <li>All accrual history</li>
                            </ul>
                            <p class="text-danger mb-0"><strong>This action cannot be undone!</strong></p>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="ri-delete-bin-line me-1"></i> Yes, Delete Policy',
                    cancelButtonText: '<i class="ri-close-line me-1"></i> Cancel',
                    reverseButtons: true,
                    focusConfirm: false,
                    focusCancel: true,
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (typeof deleteAccumulationPolicy === 'function') {
                            deleteAccumulationPolicy(parseInt(policyId), policyName);
                        } else {
                            window.location.href = button.dataset.deleteUrl;
                        }
                    }
                });
                break;
        }
    });
});
</script>

<style>
.accumulation-policy-card {
    transition: all 0.3s ease;
}

.accumulation-policy-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1) !important;
}

.hover-shadow {
    transition: box-shadow 0.3s ease;
}

.stats-card .stats-icon {
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

