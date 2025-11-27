<!-- HR Dashboard View -->
<div class="row">
    <!-- Quick Stats -->
    <div class="col-12 mb-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="stats-icon mx-auto">
                            <i class="ri-calendar-check-line"></i>
                        </div>
                        <div class="stats-number"><?= count($leaveTypes) ?></div>
                        <div class="stats-label">Leave Types</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="stats-icon mx-auto">
                            <i class="ri-settings-3-line"></i>
                        </div>
                        <div class="stats-number"><?= count($policies) ?></div>
                        <div class="stats-label">Accumulation Policies</div>
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
                            <?= count(array_filter($leaveTypes, function($type) { return $type->Suspended === 'N'; })) ?>
                        </div>
                        <div class="stats-label">Active Types</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="stats-icon mx-auto">
                            <i class="ri-calendar-line"></i>
                        </div>
                        <div class="stats-number">
                            <?php 
                            $applications = Leave::leave_applications(array(), false, $DBConn);
                            echo count($applications);
                            ?>
                        </div>
                        <div class="stats-label">Total Applications</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="ri-calendar-check-line me-2"></i>
                    Leave Types Management
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Manage leave types, create new policies, and configure leave settings.</p>
                <div class="d-grid gap-2">
                    <a href="?action=leave_types" class="btn btn-primary">
                        <i class="ri-list-check me-2"></i>
                        View All Leave Types
                    </a>
                    <a href="?action=create_leave_type" class="btn btn-outline-primary">
                        <i class="ri-add-line me-2"></i>
                        Create New Leave Type
                    </a>
                    <a href="?action=statistics" class="btn btn-outline-secondary">
                        <i class="ri-bar-chart-line me-2"></i>
                        View Statistics
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="ri-settings-3-line me-2"></i>
                    Accumulation Policies
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Configure leave accumulation rules, policies, and calculation methods.</p>
                <div class="d-grid gap-2">
                    <a href="?action=accumulation_policies" class="btn btn-primary">
                        <i class="ri-settings-3-line me-2"></i>
                        Manage Policies
                    </a>
                    <a href="?action=create_policy" class="btn btn-outline-primary">
                        <i class="ri-add-line me-2"></i>
                        Create New Policy
                    </a>
                    <a href="?action=statistics" class="btn btn-outline-secondary">
                        <i class="ri-bar-chart-line me-2"></i>
                        View Reports
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Leave Types -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="ri-time-line me-2"></i>
                    Recent Leave Types
                </h5>
            </div>
            <div class="card-body">
                <?php
                // Sort leave types by last update
                $recentTypes = $leaveTypes;
                usort($recentTypes, function($a, $b) {
                    return strtotime($b->LastUpdate) - strtotime($a->LastUpdate);
                });
                ?>
                
                <?php if (!empty($recentTypes)): ?>
                    <?php foreach (array_slice($recentTypes, 0, 5) as $type): ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 40px; height: 40px;">
                                <i class="ri-calendar-check-line text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-medium"><?= htmlspecialchars($type->leaveTypeName) ?></div>
                            <small class="text-muted">
                                Updated <?= date('M d, Y', strtotime($type->LastUpdate)) ?>
                            </small>
                        </div>
                        <div>
                            <span class="status-badge status-<?= $type->Suspended === 'Y' ? 'suspended' : 'active' ?>">
                                <?= $type->Suspended === 'Y' ? 'Suspended' : 'Active' ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-muted">
                        <i class="ri-time-line" style="font-size: 2rem; opacity: 0.3;"></i>
                        <p class="mt-2">No leave types found</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Policies -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="ri-settings-3-line me-2"></i>
                    Recent Policies
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($policies)): ?>
                    <?php foreach (array_slice($policies, 0, 5) as $policy): ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" 
                                 style="width: 40px; height: 40px;">
                                <i class="ri-settings-3-line text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-medium"><?= htmlspecialchars($policy->policyName) ?></div>
                            <small class="text-muted">
                                Created <?= date('M d, Y', strtotime($policy->createdDate)) ?>
                            </small>
                        </div>
                        <div>
                            <span class="status-badge status-<?= $policy->status === 'active' ? 'active' : 'inactive' ?>">
                                <?= ucfirst($policy->status) ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-muted">
                        <i class="ri-settings-3-line" style="font-size: 2rem; opacity: 0.3;"></i>
                        <p class="mt-2">No policies found</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Quick Reports -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="ri-bar-chart-line me-2"></i>
                    Quick Reports
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="fs-3 text-primary mb-2">
                                <?= count(array_filter($leaveTypes, function($type) { return $type->Suspended === 'N'; })) ?>
                            </div>
                            <div class="text-muted">Active Leave Types</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="fs-3 text-warning mb-2">
                                <?= count(array_filter($leaveTypes, function($type) { return $type->Suspended === 'Y'; })) ?>
                            </div>
                            <div class="text-muted">Suspended Types</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="fs-3 text-success mb-2">
                                <?= count(array_filter($policies, function($policy) { return $policy->status === 'active'; })) ?>
                            </div>
                            <div class="text-muted">Active Policies</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="fs-3 text-info mb-2">
                                <?php 
                                $totalRules = 0;
                                foreach ($policies as $policy) {
                                    $rules = AccumulationPolicy::get_policy_rules($policy->policyID, false, $DBConn);
                                    $totalRules += count($rules);
                                }
                                echo $totalRules;
                                ?>
                            </div>
                            <div class="text-muted">Total Rules</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
