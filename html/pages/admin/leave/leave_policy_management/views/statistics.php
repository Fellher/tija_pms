<!-- Statistics View -->
<div class="row">
    <!-- Overview Statistics -->
    <div class="col-12 mb-4">
        <div class="row">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <div class="stats-icon mx-auto">
                            <i class="ri-calendar-check-line"></i>
                        </div>
                        <div class="stats-number"><?= count($leaveTypes) ?></div>
                        <div class="stats-label">Total Leave Types</div>
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
                            <i class="ri-pause-line"></i>
                        </div>
                        <div class="stats-number">
                            <?= count(array_filter($leaveTypes, function($type) { return $type->Suspended === 'Y'; })) ?>
                        </div>
                        <div class="stats-label">Suspended Types</div>
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
                            <?= count(array_filter($leaveTypes, function($type) { return !empty($type->leaveTypeCode); })) ?>
                        </div>
                        <div class="stats-label">With Codes</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Usage Statistics -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="ri-bar-chart-line me-2"></i>
                    Leave Type Usage
                </h5>
            </div>
            <div class="card-body">
                <?php
                $usageStats = array();
                foreach ($leaveTypes as $type) {
                    $applications = Leave::leave_applications(array('leaveTypeID' => $type->leaveTypeID), false, $DBConn);
                    $usageStats[] = array(
                        'name' => $type->leaveTypeName,
                        'count' => $applications ? count($applications) : 0
                    );
                }
                
                // Sort by usage count
                usort($usageStats, function($a, $b) {
                    return $b['count'] - $a['count'];
                });
                ?>
                
                <?php if (!empty($usageStats)): ?>
                    <?php foreach (array_slice($usageStats, 0, 5) as $stat): ?>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="fw-medium"><?= htmlspecialchars($stat['name']) ?></div>
                            <small class="text-muted"><?= $stat['count'] ?> applications</small>
                        </div>
                        <div class="progress" style="width: 100px; height: 8px;">
                            <?php 
                            $maxCount = max(array_column($usageStats, 'count'));
                            $percentage = $maxCount > 0 ? ($stat['count'] / $maxCount) * 100 : 0;
                            ?>
                            <div class="progress-bar bg-primary" style="width: <?= $percentage ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-muted">
                        <i class="ri-bar-chart-line" style="font-size: 2rem; opacity: 0.3;"></i>
                        <p class="mt-2">No usage data available</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="ri-time-line me-2"></i>
                    Recent Updates
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
                        <p class="mt-2">No recent activity</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Status Distribution -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="ri-pie-chart-line me-2"></i>
                    Status Distribution
                </h5>
            </div>
            <div class="card-body">
                <?php
                $activeCount = count(array_filter($leaveTypes, function($type) { return $type->Suspended === 'N'; }));
                $suspendedCount = count(array_filter($leaveTypes, function($type) { return $type->Suspended === 'Y'; }));
                $totalCount = count($leaveTypes);
                ?>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span>Active</span>
                        <span class="fw-medium"><?= $activeCount ?></span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" 
                             style="width: <?= $totalCount > 0 ? ($activeCount / $totalCount) * 100 : 0 ?>%"></div>
                    </div>
                </div>
                
                <div class="mb-0">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span>Suspended</span>
                        <span class="fw-medium"><?= $suspendedCount ?></span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-warning" 
                             style="width: <?= $totalCount > 0 ? ($suspendedCount / $totalCount) * 100 : 0 ?>%"></div>
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
                    <i class="ri-flashlight-line me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="?action=create" class="btn btn-primary">
                        <i class="ri-add-line me-2"></i>
                        Create New Leave Type
                    </a>
                    
                    <button type="button" class="btn btn-outline-primary" onclick="exportLeaveTypes()">
                        <i class="ri-download-line me-2"></i>
                        Export Leave Types
                    </button>
                    
                    <a href="?action=list" class="btn btn-outline-secondary">
                        <i class="ri-list-check me-2"></i>
                        View All Leave Types
                    </a>
                    
                    <a href="<?= $base ?>/html/pages/admin/accumulation_policies.php" class="btn btn-outline-info">
                        <i class="ri-settings-3-line me-2"></i>
                        Manage Accumulation Policies
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
