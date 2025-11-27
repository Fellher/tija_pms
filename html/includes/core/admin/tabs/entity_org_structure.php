<!-- Organization Structure Tab -->
<div class="row">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-project-diagram me-2"></i>Organizational Structure</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-primary mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    This displays the hierarchical structure of units within the entity.
                </div>

                <?php if ($entityUnits): ?>
                    <!-- Organizational Chart View -->
                    <div class="org-chart-container">
                        <?php
                        // Build hierarchical structure
                        if (!function_exists('buildUnitHierarchy')) {
                            function buildUnitHierarchy($units, $parentId = 0) {
                                $hierarchy = [];
                                foreach ($units as $unit) {
                                    if (($unit->parentUnitID ?? 0) == $parentId) {
                                        $children = buildUnitHierarchy($units, $unit->unitID);
                                        if ($children) {
                                            $unit->children = $children;
                                        }
                                        $hierarchy[] = $unit;
                                    }
                                }
                                return $hierarchy;
                            }
                        }

                        if (!function_exists('displayUnitHierarchy')) {
                            function displayUnitHierarchy($hierarchy, $level = 0) {
                                foreach ($hierarchy as $unit) {
                                    $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
                                    echo '<div class="unit-item p-3 mb-2 border rounded" style="margin-left: ' . ($level * 30) . 'px;">';
                                    echo '<div class="d-flex align-items-center justify-content-between">';
                                    echo '<div>';
                                    echo $indent;
                                    if ($level > 0) echo '<i class="fas fa-level-up-alt fa-rotate-90 me-2 text-muted"></i>';
                                    echo '<strong>' . htmlspecialchars($unit->unitName) . '</strong>';
                                    echo ' <span class="badge bg-primary-transparent ms-2">' . htmlspecialchars($unit->unitTypeName ?? '') . '</span>';
                                    if (isset($unit->FirstName)) {
                                        echo '<br>' . $indent . '<small class="text-muted ms-4">Head: ' . htmlspecialchars($unit->FirstName . ' ' . ($unit->Surname ?? '')) . '</small>';
                                    }
                                    echo '</div>';
                                    echo '<div>';
                                    // echo '<button class="btn btn-sm btn-info-light me-1"><i class="fas fa-eye"></i></button>';
                                    // echo '<button class="btn btn-sm btn-primary-light"><i class="fas fa-edit"></i></button>';
                                    echo '</div>';
                                    echo '</div>';
                                    echo '</div>';

                                    if (isset($unit->children)) {
                                        displayUnitHierarchy($unit->children, $level + 1);
                                    }
                                }
                            }
                        }

                        $hierarchy = buildUnitHierarchy($entityUnits);
                        displayUnitHierarchy($hierarchy);
                        ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="avatar avatar-lg bg-secondary-transparent mx-auto mb-3">
                            <i class="fas fa-project-diagram fs-24"></i>
                        </div>
                        <h6 class="mb-2">No Organizational Structure</h6>
                        <p class="text-muted mb-3">Create units to build your organizational structure.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

