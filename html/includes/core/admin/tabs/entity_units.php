<!-- Units Tab (Departments, Sections, Teams) -->
<div class="row">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-sitemap me-2"></i>Entity Units - Departments, Sections & Teams (<?= $unitsCount ?>)</h5>
                </div>
                <button type="button" class="btn btn-primary btn-sm btn-wave"
                    data-bs-toggle="modal"
                    data-bs-target="#manageUnitModal"
                    onclick="addUnitForEntity(<?= $entityID ?>)">
                    <i class="fas fa-plus me-2"></i>Add Unit
                </button>
            </div>
            <div class="card-body">
                <?php if ($entityUnits): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Unit Name</th>
                                    <th>Type</th>
                                    <th>Code</th>
                                    <th>Head of Unit</th>
                                    <th>Parent Unit</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($entityUnits as $unit): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($unit->unitName ?? 'N/A') ?></strong></td>
                                        <td>
                                            <span class="badge bg-primary-transparent">
                                                <?= htmlspecialchars($unit->unitTypeName ?? 'N/A') ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($unit->unitCode ?? 'N/A') ?></td>
                                        <td>
                                            <?php if (isset($unit->FirstName)): ?>
                                                <?= htmlspecialchars($unit->FirstName . ' ' . ($unit->Surname ?? '')) ?>
                                            <?php else: ?>
                                                <span class="text-muted">Not assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?= isset($unit->parentUnitID) && $unit->parentUnitID > 0 ? 'Sub-unit' : 'Main Unit' ?>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-info-light" title="View Unit">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-primary-light editUnit" title="Edit Unit"
                                                data-bs-toggle="modal"
                                                data-bs-target="#manageUnitModal"
                                                data-unit-id="<?= $unit->unitID ?>"
                                                onclick="editUnit(<?= $unit->unitID ?>)">
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
                        <div class="avatar avatar-lg bg-secondary-transparent mx-auto mb-3">
                            <i class="fas fa-sitemap fs-24"></i>
                        </div>
                        <h6 class="mb-2">No Units Created Yet</h6>
                        <p class="text-muted mb-3">Create departments, sections, and teams for this entity.</p>
                        <button type="button" class="btn btn-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#manageUnitModal"
                            onclick="addUnitForEntity(<?= $entityID ?>)">
                            <i class="fas fa-plus me-2"></i>Add First Unit
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

