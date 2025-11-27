<!-- Business Units Tab (Commercial Units/Cost Centers/Product Lines) -->
<div class="row">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Business Units - Cost Centers & Product Lines (<?= $businessUnitsCount ?>)</h5>
                </div>
                <button type="button" class="btn btn-primary btn-sm btn-wave"
                    data-bs-toggle="modal"
                    data-bs-target="#manageBusinessUnitModal"
                    onclick="addBusinessUnitForEntity(<?= $entityID ?>)">
                    <i class="fas fa-plus me-2"></i>Add Business Unit
                </button>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Business Units</strong> are income-generating or cost-tracking units such as Projects, Reporting Units, Tax Units, Commercial Units, or Product Lines.
                </div>
                <?php if ($businessUnits): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Business Unit Name</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($businessUnits as $bu): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($bu->businessUnitName ?? 'N/A') ?></strong></td>
                                        <td>
                                            <span class="badge bg-warning-transparent">
                                                <?= htmlspecialchars($bu->categoryName ?? 'N/A') ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($bu->businessUnitDescription ?? 'N/A') ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-info-light" title="View Business Unit">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-primary-light editBusinessUnit" title="Edit Business Unit"
                                                data-bs-toggle="modal"
                                                data-bs-target="#manageBusinessUnitModal"
                                                data-business-unit-id="<?= $bu->businessUnitID ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="avatar avatar-lg bg-warning-transparent mx-auto mb-3">
                            <i class="fas fa-chart-line fs-24"></i>
                        </div>
                        <h6 class="mb-2">No Business Units Created Yet</h6>
                        <p class="text-muted mb-3">Create business units for tracking projects, cost centers, or product lines.</p>
                        <button type="button" class="btn btn-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#manageBusinessUnitModal"
                            onclick="addBusinessUnitForEntity(<?= $entityID ?>)">
                            <i class="fas fa-plus me-2"></i>Add First Business Unit
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

