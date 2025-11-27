<?php
/**
 * Policy List View
 */

if (empty($policies)): ?>
    <div class="text-center py-5">
        <i class="ri-calendar-check-line display-1 text-muted"></i>
        <h4 class="mt-3 text-muted">No Policies Found</h4>
        <p class="text-muted">Create your first accumulation policy to get started.</p>
        <a href="?action=create" class="btn btn-primary">
            <i class="ri-add-line me-1"></i>
            Create Policy
        </a>
    </div>
<?php else: ?>
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-1">Total Policies</h6>
                            <h3 class="mb-0"><?= count($policies) ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="ri-file-list-3-line display-6"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-1">Active Policies</h6>
                            <h3 class="mb-0"><?= count(array_filter($policies, function($p) { return $p['isActive'] === 'Y'; })) ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="ri-check-circle-line display-6"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-1">Monthly Accrual</h6>
                            <h3 class="mb-0"><?= count(array_filter($policies, function($p) { return $p['accrualType'] === 'Monthly'; })) ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="ri-calendar-line display-6"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-1">With Rules</h6>
                            <h3 class="mb-0"><?= count(array_filter($policies, function($p) { return !empty($p['ruleCount']); })) ?></h3>
                        </div>
                        <div class="align-self-center">
                            <i class="ri-rules-line display-6"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Policies Grid -->
    <div class="row">
        <?php foreach ($policies as $policy): ?>
        <div class="col-lg-6 col-xl-4 mb-4">
            <div class="card policy-card h-100 shadow-sm">
                <div class="card-header bg-white border-bottom-0">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-1">
                                <a href="?action=view&policyID=<?= $policy['policyID'] ?>" class="text-decoration-none">
                                    <?= htmlspecialchars($policy['policyName']) ?>
                                </a>
                            </h5>
                            <small class="text-muted">
                                <i class="ri-calendar-line me-1"></i>
                                <?= htmlspecialchars($policy['leaveTypeName']) ?>
                            </small>
                        </div>
                        
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                <i class="ri-more-2-line"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="?action=view&policyID=<?= $policy['policyID'] ?>">
                                        <i class="ri-eye-line me-2"></i>View
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="?action=edit&policyID=<?= $policy['policyID'] ?>">
                                        <i class="ri-edit-line me-2"></i>Edit
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
                
                <div class="card-body">
                    <?php if ($policy['policyDescription']): ?>
                    <p class="card-text text-muted small">
                        <?= htmlspecialchars(substr($policy['policyDescription'], 0, 100)) ?>
                        <?= strlen($policy['policyDescription']) > 100 ? '...' : '' ?>
                    </p>
                    <?php endif; ?>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <span class="badge accrual-type-badge bg-primary me-2">
                                    <?= $policy['accrualType'] ?>
                                </span>
                                <small class="text-muted">
                                    <?= number_format($policy['accrualRate'], 2) ?> days
                                </small>
                            </div>
                        </div>
                        
                        <div class="col-6">
                            <div class="d-flex align-items-center justify-content-end">
                                <?php if ($policy['priority'] > 1): ?>
                                <span class="badge priority-badge me-2">
                                    Priority <?= $policy['priority'] ?>
                                </span>
                                <?php endif; ?>
                                
                                <span class="badge <?= $policy['isActive'] === 'Y' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $policy['isActive'] === 'Y' ? 'Active' : 'Inactive' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div class="fw-bold text-primary"><?= $policy['maxCarryover'] ?? '∞' ?></div>
                                <small class="text-muted">Max Carryover</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div class="fw-bold text-info"><?= $policy['carryoverExpiryMonths'] ?? '∞' ?></div>
                                <small class="text-muted">Expiry Months</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-2">
                                <div class="fw-bold <?= $policy['proRated'] === 'Y' ? 'text-success' : 'text-warning' ?>">
                                    <?= $policy['proRated'] === 'Y' ? 'Yes' : 'No' ?>
                                </div>
                                <small class="text-muted">Pro-rated</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer bg-white border-top-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="ri-time-line me-1"></i>
                            Created <?= date('M d, Y', strtotime($policy['DateAdded'])) ?>
                        </small>
                        
                        <div>
                            <a href="?action=view&policyID=<?= $policy['policyID'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="ri-eye-line me-1"></i>View
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

