<!-- Overview Tab -->
<div class="row">
    <!-- Left Column - Entity Details -->
    <div class="col-xl-8">
        <!-- Entity Information -->
        <div class="card custom-card mb-4">
            <div class="card-header">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Entity Information</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Entity Name</label>
                            <p class="mb-0 fs-15"><?= htmlspecialchars($entity->entityName) ?></p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Entity Type</label>
                            <p class="mb-0 fs-15">
                                <i class="fas fa-tag text-primary me-2"></i>
                                <?= htmlspecialchars($entityType->entityTypeTitle ?? 'N/A') ?>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Registration Number</label>
                            <p class="mb-0 fs-15">
                                <i class="fas fa-id-card text-primary me-2"></i>
                                <?= htmlspecialchars($entity->registrationNumber ?? 'N/A') ?>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Entity PIN</label>
                            <p class="mb-0 fs-15">
                                <i class="fas fa-hashtag text-primary me-2"></i>
                                <?= htmlspecialchars($entity->entityPIN ?? 'N/A') ?>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Industry Sector</label>
                            <p class="mb-0 fs-15">
                                <i class="fas fa-industry text-primary me-2"></i>
                                <?= htmlspecialchars($industrySector->industryTitle ?? 'N/A') ?>
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Email</label>
                            <p class="mb-0 fs-15">
                                <i class="fas fa-envelope text-primary me-2"></i>
                                <a href="mailto:<?= htmlspecialchars($entity->entityEmail) ?>">
                                    <?= htmlspecialchars($entity->entityEmail ?? 'N/A') ?>
                                </a>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Phone Number</label>
                            <p class="mb-0 fs-15">
                                <i class="fas fa-phone text-primary me-2"></i>
                                <?= htmlspecialchars($entity->entityPhoneNumber ?? 'N/A') ?>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Location</label>
                            <p class="mb-0 fs-15">
                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                <?= htmlspecialchars($entity->entityCity ?? '') ?>
                                <?= $country ? ', ' . htmlspecialchars($country->countryName) : '' ?>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Parent Entity</label>
                            <p class="mb-0 fs-15">
                                <i class="fas fa-sitemap text-primary me-2"></i>
                                <?php if ($parentEntity): ?>
                                    <a href="<?= $base ?>html/?s=core&ss=admin&p=entity_details&entityID=<?= $parentEntity->entityID ?>">
                                        <?= htmlspecialchars($parentEntity->entityName) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">No parent entity</span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted fw-semibold">Organization</label>
                            <p class="mb-0 fs-15">
                                <i class="fas fa-building text-primary me-2"></i>
                                <?php if ($organisation && isset($entity->orgDataID)): ?>
                                    <a href="<?= $base ?>html/?s=core&ss=admin&p=tenant_details&orgDataID=<?= $entity->orgDataID ?>">
                                        <?= htmlspecialchars($organisation->orgName) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <?php if (isset($entity->entityDescription) && !empty($entity->entityDescription)): ?>
                    <div class="col-12">
                        <div class="mb-0">
                            <label class="form-label text-muted fw-semibold">Description</label>
                            <p class="mb-0 fs-15"><?= nl2br(htmlspecialchars($entity->entityDescription)) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Child Entities -->
        <?php if ($childEntities): ?>
        <div class="card custom-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-project-diagram me-2"></i>Child Entities (<?= $childCount ?>)</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Entity Name</th>
                                <th>Type</th>
                                <th>Location</th>
                                <th>Employees</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($childEntities as $child):
                                $childEmployees = Employee::employees(['entityID' => $child->entityID], false, $DBConn);
                                $childEmpCount = $childEmployees ? count($childEmployees) : 0;
                            ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($child->entityName) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($child->entityTypeTitle ?? 'N/A') ?></td>
                                    <td>
                                        <?= htmlspecialchars($child->entityCity) ?>,
                                        <?= htmlspecialchars($child->countryName ?? 'N/A') ?>
                                    </td>
                                    <td><?= $childEmpCount ?></td>
                                    <td class="text-center">
                                        <a href="<?= $base ?>html/?s=core&ss=admin&p=entity_details&entityID=<?= $child->entityID ?>"
                                            class="btn btn-sm btn-info-light" title="View Entity">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-sm btn-primary-light editEntity"
                                            data-bs-toggle="modal"
                                            data-bs-target="#manageEntity"
                                            data-id="<?= $child->entityID ?>"
                                            title="Edit Entity">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Departments -->
        <?php if ($departments): ?>
        <div class="card custom-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-sitemap me-2"></i>Departments (<?= $departmentCount ?>)</h5>
                </div>
                <button type="button" class="btn btn-sm btn-primary btn-wave"
                    data-bs-toggle="modal"
                    data-bs-target="#manageUnitModal"
                    onclick="addUnitForEntity(<?= $entityID ?>, 'Department')">
                    <i class="fas fa-plus me-2"></i>Add Department
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Department Name</th>
                                <th>Code</th>
                                <th>Head</th>
                                <th>Employees</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($departments as $dept): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($dept->departmentName ?? 'N/A') ?></strong></td>
                                    <td><?= htmlspecialchars($dept->departmentCode ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($dept->headName ?? 'N/A') ?></td>
                                    <td><?= $dept->employeeCount ?? 0 ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-info-light"
                                            onclick="viewDepartment(<?= $dept->departmentID ?? $dept->unitID ?>)"
                                            title="View Department">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-primary-light editUnit"
                                            data-bs-toggle="modal"
                                            data-bs-target="#manageUnitModal"
                                            data-unit-id="<?= $dept->departmentID ?? $dept->unitID ?>"
                                            onclick="editUnit(<?= $dept->departmentID ?? $dept->unitID ?>)"
                                            title="Edit Department">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Right Column - Additional Info & Employees -->
    <div class="col-xl-4">
        <!-- Quick Stats -->
        <div class="card custom-card mb-4">
            <div class="card-header">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Quick Stats</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Created On</span>
                        <span><?= isset($entity->DateAdded) ? date('M d, Y', strtotime($entity->DateAdded)) : 'N/A' ?></span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Last Updated</span>
                        <span><?= isset($entity->LastUpdate) ? date('M d, Y', strtotime($entity->LastUpdate)) : 'N/A' ?></span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Entity ID</span>
                        <span><code>#<?= $entityID ?></code></span>
                    </div>

                    <?php if ($costCenterCount > 0): ?>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Cost Centers</span>
                        <span class="badge bg-primary"><?= $costCenterCount ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Employees -->
        <?php if ($entityEmployees): ?>
        <div class="card custom-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Employees</h5>
                </div>
                <button type="button" class="btn btn-sm btn-primary btn-wave" onclick="addEmployee(<?= $entityID ?>)">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <?php
                    $displayEmployees = array_slice($entityEmployees, 0, 10); // Show first 10
                    foreach ($displayEmployees as $emp): ?>
                        <div class="list-group-item px-0">
                            <div class="d-flex align-items-center">
                                <?php if (isset($emp->profile_image) && $emp->profile_image): ?>
                                    <img src="<?= "{$base}data/uploaded_files/{$emp->profile_image}" ?>"
                                        alt="Profile" class="avatar avatar-sm rounded-circle me-2">
                                <?php else: ?>
                                    <div class="avatar avatar-sm bg-primary-transparent rounded-circle me-2">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-fill">
                                    <h6 class="mb-0"><?= htmlspecialchars($emp->employeeName ?? $emp->employeeName ?? 'N/A') ?></h6>
                                    <small class="text-muted"><?= htmlspecialchars($emp->jobTitle ?? $emp->jobTitle ?? 'Employee') ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($employeeCount > 10): ?>
                    <div class="text-center mt-3">
                        <small class="text-muted">Showing 10 of <?= $employeeCount ?> employees</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Additional Actions -->
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Actions</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="viewAllEmployees(<?= $entityID ?>)">
                        <i class="fas fa-users me-2"></i>View All Employees
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="manageDepartments(<?= $entityID ?>)">
                        <i class="fas fa-sitemap me-2"></i>Manage Departments
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm" onclick="generateEntityReport(<?= $entityID ?>)">
                        <i class="fas fa-file-pdf me-2"></i>Generate Report
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="exportEntityData(<?= $entityID ?>)">
                        <i class="fas fa-download me-2"></i>Export Data
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

