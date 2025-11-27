<?php
// Get reporting relationships from both new and existing systems
// 1. Get from new tija_reporting_relationships table
$reportingRelationships = Data::reporting_relationships(['entityID' => $entityID, 'isCurrent' => 'Y', 'Suspended' => 'N'], false, $DBConn);

// 2. Get from existing user_details.supervisorID (for backward compatibility)
$existingSupervisorRelationships = array();
if ($entityEmployees) {
    foreach ($entityEmployees as $emp) {
        if (isset($emp->supervisorID) && $emp->supervisorID > 0) {
            // Create a pseudo-relationship object for display
            $pseudoRel = new stdClass();
            $pseudoRel->employeeID = $emp->ID;
            $pseudoRel->supervisorID = $emp->supervisorID;
            $pseudoRel->relationshipType = 'Direct';
            $pseudoRel->effectiveDate = $emp->employmentStartDate ?? date('Y-m-d');
            $pseudoRel->reportingFrequency = 'Weekly';
            $pseudoRel->source = 'legacy'; // Mark as legacy data
            $existingSupervisorRelationships[] = $pseudoRel;
        }
    }
}

// Merge both sources (new table takes precedence)
$allRelationships = array();
$existingEmployeeIDs = array();

// Add new relationships first
if ($reportingRelationships) {
    foreach ($reportingRelationships as $rel) {
        $allRelationships[] = $rel;
        $existingEmployeeIDs[] = $rel->employeeID;
    }
}

// Add legacy relationships for employees not in new system
foreach ($existingSupervisorRelationships as $legacyRel) {
    if (!in_array($legacyRel->employeeID, $existingEmployeeIDs)) {
        $allRelationships[] = $legacyRel;
        $existingEmployeeIDs[] = $legacyRel->employeeID;
    }
}

// Find employees without reporting relationships
$employeesWithoutRelationships = array();
if ($entityEmployees) {
    foreach ($entityEmployees as $emp) {
        if (!in_array($emp->ID, $existingEmployeeIDs)) {
            // Create a pseudo-relationship object for display (no supervisor assigned)
            $pseudoRel = new stdClass();
            $pseudoRel->employeeID = $emp->ID;
            $pseudoRel->supervisorID = null;
            $pseudoRel->relationshipType = null;
            $pseudoRel->effectiveDate = null;
            $pseudoRel->reportingFrequency = null;
            $pseudoRel->source = 'unassigned'; // Mark as unassigned
            $pseudoRel->relationshipID = null;
            $employeesWithoutRelationships[] = $pseudoRel;
        }
    }
}

// Add unassigned employees to the relationships array for display
$allRelationships = array_merge($allRelationships, $employeesWithoutRelationships);

$relationshipCount = count($allRelationships);

// Get roles for this entity
$entityRoles = Data::roles(['isActive' => 'Y', 'Suspended' => 'N'], false, $DBConn);

if ($entityRoles) {
    // Filter by entity or organization
    $entityRoles = array_filter($entityRoles, function($role) use ($entityID, $entity) {
        return ($role->entityID == $entityID) || ($role->entityID == null && $role->orgDataID == ($entity->orgDataID ?? 0));
    });
}
$rolesCount = $entityRoles ? count($entityRoles) : 0;

// Get role types for dropdown
$roleTypes = Data::role_types(['isActive' => 'Y', 'Suspended' => 'N'], false, $DBConn);
if ($roleTypes) {
    // Sort by displayOrder
    usort($roleTypes, function($a, $b) {
        return ($a->displayOrder ?? 0) - ($b->displayOrder ?? 0);
    });
}

// Get role levels for dropdown
$roleLevels = Data::role_levels(['isActive' => 'Y', 'Suspended' => 'N'], false, $DBConn);
if ($roleLevels) {
    // Sort by levelNumber (ascending - lower numbers = higher authority)
    usort($roleLevels, function($a, $b) {
        return ($a->levelNumber ?? 999) - ($b->levelNumber ?? 999);
    });
}

// Get pay grades for this entity
$entityPayGrades = Data::pay_grades(['entityID' => $entityID, 'Suspended' => 'N'], false, $DBConn);
$payGradesCount = $entityPayGrades ? count($entityPayGrades) : 0;

// Get pay grade to job title mappings with pay grade details
$payGradeMappings = array();
if ($entityPayGrades) {
    foreach ($entityPayGrades as $payGrade) {
        $mappings = Data::job_title_pay_grade_mapping([
            'payGradeID' => $payGrade->payGradeID,
            'isCurrent' => 'Y',
            'Suspended' => 'N'
        ], false, $DBConn);
        if ($mappings) {
            foreach ($mappings as $mapping) {
                // Store pay grade object with all details including gradeLevel
                $payGradeMappings[$mapping->jobTitleID] = (object)[
                    'payGradeID' => $payGrade->payGradeID,
                    'payGradeCode' => $payGrade->payGradeCode,
                    'payGradeName' => $payGrade->payGradeName,
                    'gradeLevel' => $payGrade->gradeLevel,
                    'minSalary' => $payGrade->minSalary,
                    'midSalary' => $payGrade->midSalary,
                    'maxSalary' => $payGrade->maxSalary
                ];
            }
        }
    }
}

// Get delegation assignments
$delegations = Data::delegation_assignments(['entityID' => $entityID, 'isActive' => 'Y', 'Suspended' => 'N'], false, $DBConn);
$delegationsCount = $delegations ? count($delegations) : 0;

// Get existing org charts for this entity
$orgCharts = Data::org_charts(['entityID' => $entityID, 'Suspended' => 'N'], false, $DBConn);
$orgChartCount = $orgCharts ? count($orgCharts) : 0;

// Get org chart positions if an org chart exists
$orgChartPositions = array();
if ($orgCharts && count($orgCharts) > 0) {
    $primaryChart = $orgCharts[0];
    $orgChartPositions = Data::org_chart_position_assignments(['orgChartID' => $primaryChart->orgChartID, 'Suspended' => 'N'], false, $DBConn);
}

// Get job titles for role selection
$jobTitles = Data::job_titles(['Suspended' => 'N'], false, $DBConn);

// Get departments for the entity
$entityDepartments = Data::departments(['entityID' => $entityID, 'Suspended' => 'N'], false, $DBConn);

// Get units for the entity
$entityUnits = Data::units(['entityID' => $entityID, 'Suspended' => 'N'], false, $DBConn);

// Get existing roles for parent role selection (exclude current role when editing)
$existingRoles = Data::roles(['isActive' => 'Y', 'Suspended' => 'N'], false, $DBConn);
if ($existingRoles) {
    // Filter by entity or organization
    $existingRoles = array_filter($existingRoles, function($role) use ($entityID, $entity) {
        return ($role->entityID == $entityID) || ($role->entityID == null && $role->orgDataID == ($entity->orgDataID ?? 0));
    });
}

$canManageReportingMigration = ($isValidAdmin || $isAdmin || $isSuperAdmin || $isTenantAdmin || $isHRManager);

$legacyCount = 0;
if (!empty($allRelationships)) {
    foreach ($allRelationships as $rel) {
        if (isset($rel->source) && $rel->source === 'legacy') {
            $legacyCount++;
        }
    }
}
?>

<!-- Quick Actions Bar -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card custom-card border-0 shadow-sm">
            <div class="card-body py-2">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <button type="button" class="btn btn-primary btn-sm btn-wave"
                        data-bs-toggle="modal"
                        data-bs-target="#manageReportingRelationshipModal"
                        onclick="addReportingRelationship()">
                        <i class="fas fa-plus me-2"></i>Add Reporting Line
                    </button>
                    <button type="button" class="btn btn-success btn-sm btn-wave"
                        data-bs-toggle="modal"
                        data-bs-target="#manageRoleModal"
                        onclick="addNewRole()">
                        <i class="fas fa-user-tie me-2"></i>Manage Roles
                    </button>
                    <button type="button" class="btn btn-info btn-sm btn-wave">
                        <i class="fas fa-hand-holding me-2"></i>Add Delegation
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm btn-wave" onclick="generateOrgChart()">
                        <i class="fas fa-sitemap me-2"></i>View Org Chart
                    </button>
                    <button type="button" class="btn btn-light btn-sm btn-wave ms-auto"
                        data-bs-toggle="modal"
                        data-bs-target="#reportingHelpModal"
                        title="Help & Documentation">
                        <i class="fas fa-question-circle me-2"></i>Help
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-4 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md bg-primary-transparent me-3">
                        <i class="fas fa-project-diagram fs-20"></i>
                    </div>
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Reporting Lines</p>
                        <h4 class="mb-0"><?= $relationshipCount ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md bg-success-transparent me-3">
                        <i class="fas fa-user-tie fs-20"></i>
                    </div>
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Defined Roles</p>
                        <h4 class="mb-0"><?= $rolesCount ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md bg-info-transparent me-3">
                        <i class="fas fa-hand-holding fs-20"></i>
                    </div>
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Active Delegations</p>
                        <h4 class="mb-0"><?= $delegationsCount ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reporting Relationships Table -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-project-diagram me-2"></i>Current Reporting Relationships</h5>
                </div>
            </div>
            <div class="card-body">
                <?php
                $assignedCount = isset($employeesWithoutRelationships) ? count($allRelationships) - count($employeesWithoutRelationships) : count($allRelationships);
                $unassignedCount = isset($employeesWithoutRelationships) ? count($employeesWithoutRelationships) : 0;
                if ($allRelationships && count($allRelationships) > 0): ?>
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Total: <?= count($allRelationships) ?> employee(s)</strong> -
                        <?= $assignedCount ?> with reporting relationships,
                        <?= $unassignedCount ?> without assignments.
                        <?php if ($unassignedCount > 0): ?>
                            <span class="badge bg-warning-transparent ms-2">
                                <i class="fas fa-exclamation-triangle me-1"></i><?= $unassignedCount ?> need assignment
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Row Selection and Pagination Controls -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <label for="rowsPerPage" class="mb-0 text-muted small">Show:</label>
                            <select id="rowsPerPage" class="form-select form-select-sm" style="width: auto;">
                                <option value="10">10</option>
                                <option value="30" selected>30</option>
                                <option value="50">50</option>
                                <option value="200">200</option>
                                <option value="all">All</option>
                            </select>
                            <span class="text-muted small" id="paginationInfo">rows per page</span>
                        </div>
                        <div id="paginationControls" class="d-flex align-items-center gap-2">
                            <!-- Pagination will be inserted here by JavaScript -->
                        </div>
                    </div>

                    <?php if ($canManageReportingMigration && $legacyCount > 0): ?>
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div class="text-muted small">
                            Select the legacy relationships you want to migrate to the new reporting system.
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button"
                                    class="btn btn-warning btn-sm btn-wave"
                                    id="migrateSelectedLegacyBtn"
                                    disabled>
                                <i class="fas fa-random me-1"></i>
                                Migrate Selected (<span id="selectedLegacyCount">0</span>)
                            </button>
                            <button type="button"
                                    class="btn btn-outline-secondary btn-sm"
                                    id="clearLegacySelectionBtn">
                                <i class="fas fa-times me-1"></i>
                                Clear Selection
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0" id="reportingRelationshipsTable">
                            <thead class="table-light">
                                <tr>
                                    <?php if ($canManageReportingMigration): ?>
                                    <th style="width: 4%;" class="text-center">
                                        <input type="checkbox"
                                               id="selectAllLegacy"
                                               class="form-check-input"
                                               title="Select/Deselect visible legacy rows"
                                               <?= $legacyCount === 0 ? 'disabled' : '' ?>>
                                    </th>
                                    <?php endif; ?>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 23%;">Employee</th>
                                    <th style="width: 23%;">Reports To</th>
                                    <th style="width: 13%;">Relationship Type</th>
                                    <th style="width: 10%;">Effective Date</th>
                                    <th style="width: 10%;">Frequency</th>
                                    <th style="width: 8%;">Source</th>
                                    <th style="width: 8%;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="reportingRelationshipsTableBody">
                                <?php foreach ($allRelationships as $index => $rel):
                                    $employee = Core::user(['ID' => $rel->employeeID], true, $DBConn);
                                    $isLegacy = isset($rel->source) && $rel->source == 'legacy';
                                    $isUnassigned = isset($rel->source) && $rel->source == 'unassigned';

                                    if ($isUnassigned) {
                                        $supervisor = null;
                                    } else {
                                        $supervisor = Core::user(['ID' => $rel->supervisorID], true, $DBConn);
                                    }
                                ?>
                                    <tr class="reporting-relationship-row <?= $isUnassigned ? 'table-warning' : '' ?>"
                                        data-row-index="<?= $index ?>"
                                        data-employee-id="<?= $rel->employeeID ?>"
                                        data-employee-name="<?= htmlspecialchars(trim(($employee->FirstName ?? '') . ' ' . ($employee->Surname ?? '')), ENT_QUOTES) ?>"
                                        data-supervisor-id="<?= htmlspecialchars($rel->supervisorID ?? '', ENT_QUOTES) ?>">
                                        <?php if ($canManageReportingMigration): ?>
                                        <td class="text-center">
                                            <?php if ($isLegacy): ?>
                                                <input type="checkbox"
                                                       class="form-check-input legacy-migrate-checkbox"
                                                       value="<?= $rel->employeeID ?>"
                                                       data-supervisor-id="<?= $rel->supervisorID ?>"
                                                       <?php if (($rel->supervisorID ?? 0) == 0): ?>disabled<?php endif; ?>>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php endif; ?>
                                        <td class="row-number"><?= $index + 1 ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($employee->FirstName ?? 'N/A') ?> <?= htmlspecialchars($employee->Surname ?? '') ?></strong>
                                            <?php if ($isUnassigned): ?>
                                                <span class="badge bg-warning-transparent ms-2" title="No reporting relationship assigned">
                                                    <i class="fas fa-exclamation-circle"></i> Unassigned
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($isUnassigned): ?>
                                                <span class="text-muted fst-italic">Not assigned</span>
                                            <?php else: ?>
                                                <?= htmlspecialchars($supervisor->FirstName ?? 'N/A') ?> <?= htmlspecialchars($supervisor->Surname ?? '') ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($isUnassigned): ?>
                                                <span class="text-muted">—</span>
                                            <?php else: ?>
                                                <span class="badge bg-<?= $rel->relationshipType == 'Direct' ? 'primary' : 'info' ?>-transparent">
                                                    <?= htmlspecialchars($rel->relationshipType) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($isUnassigned): ?>
                                                <span class="text-muted">—</span>
                                            <?php else: ?>
                                                <?= date('M d, Y', strtotime($rel->effectiveDate)) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($isUnassigned): ?>
                                                <span class="text-muted">—</span>
                                            <?php else: ?>
                                                <?= htmlspecialchars($rel->reportingFrequency ?? 'Weekly') ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($isUnassigned): ?>
                                                <span class="badge bg-danger-transparent" title="No reporting relationship assigned">
                                                    <i class="fas fa-times-circle"></i> Unassigned
                                                </span>
                                            <?php elseif ($isLegacy): ?>
                                                <span class="badge bg-warning-transparent" title="From user_details.supervisorID">
                                                    <i class="fas fa-database"></i> Legacy
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success-transparent" title="From tija_reporting_relationships">
                                                    <i class="fas fa-check"></i> New
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($isUnassigned): ?>
                                                <button class="btn btn-sm btn-success-light addReportingRelationship"
                                                    title="Add Reporting Relationship"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#manageReportingRelationshipModal"
                                                    data-employee-id="<?= $rel->employeeID ?>"
                                                    onclick="addReportingRelationshipForEmployee(<?= $rel->employeeID ?>)">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            <?php elseif (!$isLegacy): ?>
                                                <button class="btn btn-sm btn-primary-light editReportingRelationship"
                                                    title="Edit Reporting Relationship"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#manageReportingRelationshipModal"
                                                    data-relationship-id="<?= $rel->relationshipID ?>">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-secondary-light" title="Migrate to new system"
                                                    onclick="migrateRelationship(<?= $rel->employeeID ?>, <?= $rel->supervisorID ?>, this)">
                                                    <i class="fas fa-arrow-right"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Info Footer -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted small" id="paginationSummary">
                            Showing <span id="showingFrom">1</span> to <span id="showingTo"><?= count($allRelationships) ?></span> of <span id="totalRows"><?= count($allRelationships) ?></span> entries
                            <?php if ($unassignedCount > 0): ?>
                                <span class="badge bg-warning-transparent ms-2">
                                    <i class="fas fa-info-circle me-1"></i><?= $unassignedCount ?> unassigned
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="avatar avatar-lg bg-primary-transparent mx-auto mb-3">
                            <i class="fas fa-project-diagram fs-24"></i>
                        </div>
                        <h6 class="mb-2">No Reporting Relationships Defined</h6>
                        <p class="text-muted mb-3">Start building your reporting structure by adding reporting lines.</p>
                        <button type="button" class="btn btn-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#manageReportingRelationshipModal"
                            onclick="addReportingRelationship()">
                            <i class="fas fa-plus me-2"></i>Add First Reporting Line
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Roles Table -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title d-flex align-items-center">
                    <h5 class="mb-0"><i class="fas fa-user-tie me-2"></i>Organizational Roles</h5>
                    <button type="button"
                        class="btn btn-link btn-sm text-info p-0 ms-2"
                        data-bs-toggle="collapse"
                        data-bs-target="#rolesInfoCollapse"
                        aria-expanded="false"
                        aria-controls="rolesInfoCollapse"
                        title="Click to learn about Organizational Roles and how to manage them"
                        data-bs-placement="top"
                        id="rolesInfoBtn">
                        <i class="fas fa-info-circle fs-16"></i>
                    </button>
                </div>
                <div>
                    <button type="button" class="btn btn-success btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#manageRoleModal"
                        onclick="addNewRole()"
                        title="Add New Role">
                        <i class="fas fa-plus me-2"></i>Add Role
                    </button>
                </div>
            </div>

            <!-- Roles Information Collapse -->
            <div class="collapse" id="rolesInfoCollapse">
                <div class="card-body bg-light border-top">
                    <div class="row">
                        <div class="col-md-12">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-info-circle me-2"></i>What are Organizational Roles?
                            </h6>
                            <p class="mb-3">
                                <strong>Organizational Roles</strong> define positions within your company's hierarchy and establish
                                the authority levels, approval capabilities, and reporting structure. They help structure your
                                organization by categorizing positions based on their level of responsibility and decision-making authority.
                            </p>

                            <h6 class="text-success mb-2">Key Concepts:</h6>
                            <ul class="mb-3">
                                <li><strong>Role Level:</strong> Numeric hierarchy (0-8) where lower numbers indicate higher authority</li>
                                <li><strong>Role Type:</strong> Category (Executive, Management, Supervisory, Operational, Support)</li>
                                <li><strong>Approval Authority:</strong> Whether the role can approve requests (leave, expenses, etc.)</li>
                                <li><strong>Parent Role:</strong> Superior role in the organizational hierarchy</li>
                                <li><strong>Scope:</strong> Entity-specific or organization-wide availability</li>
                            </ul>

                            <h6 class="text-info mb-2">Common Use Cases:</h6>
                            <ul class="mb-3">
                                <li>Define approval workflows for leave requests and expense approvals</li>
                                <li>Establish clear reporting hierarchies and organizational structure</li>
                                <li>Set financial approval limits based on role level</li>
                                <li>Assign roles to employees to determine their authority and responsibilities</li>
                                <li>Create visual organizational charts based on role hierarchy</li>
                            </ul>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary-transparent">
                                            <h6 class="mb-0"><i class="fas fa-plus-circle me-2"></i>How to Add a Role</h6>
                                        </div>
                                        <div class="card-body">
                                            <ol class="mb-0 small">
                                                <li>Click the <strong>"Add Role"</strong> button above</li>
                                                <li>Select an existing job title or create a new one</li>
                                                <li>Choose the <strong>Role Type</strong> (Executive, Management, etc.)</li>
                                                <li>Set the <strong>Role Level</strong> (0-8, lower = higher authority)</li>
                                                <li>Configure approval settings if needed</li>
                                                <li>Set organizational structure (department, unit, parent role)</li>
                                                <li>Customize visual appearance (icon, color)</li>
                                                <li>Choose scope (Entity-specific or Organization-wide)</li>
                                                <li>Click <strong>"Save Role"</strong></li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-success">
                                        <div class="card-header bg-success-transparent">
                                            <h6 class="mb-0"><i class="fas fa-edit me-2"></i>How to Edit a Role</h6>
                                        </div>
                                        <div class="card-body">
                                            <ol class="mb-0 small">
                                                <li>Find the role in the table below</li>
                                                <li>Click the <strong><i class="fas fa-edit"></i> Edit</strong> button</li>
                                                <li>Modify any fields as needed:
                                                    <ul class="mt-2">
                                                        <li>Role name, type, or level</li>
                                                        <li>Approval settings</li>
                                                        <li>Organizational structure</li>
                                                        <li>Visual customization</li>
                                                    </ul>
                                                </li>
                                                <li>Click <strong>"Save Role"</strong> to update</li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-warning mb-0">
                                <h6 class="alert-heading"><i class="fas fa-lightbulb me-2"></i>Best Practices:</h6>
                                <ul class="mb-0">
                                    <li><strong>Start with top-level roles:</strong> Define executive and management roles first</li>
                                    <li><strong>Use consistent naming:</strong> Follow your organization's job title conventions</li>
                                    <li><strong>Set appropriate levels:</strong> Ensure role levels reflect actual authority</li>
                                    <li><strong>Link to job titles:</strong> Connect roles to existing job titles for consistency</li>
                                    <li><strong>Document descriptions:</strong> Add clear descriptions for each role's purpose</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <?php if ($entityRoles):
                  /**
                   * reorder and sort the roles by level number ascending
                   */
                  if ($entityRoles && is_array($entityRoles)) {
                      usort($entityRoles, function($a, $b) {
                          return ($a->levelNumber ?? 999) - ($b->levelNumber ?? 999);
                      });
                    //   $entityRoles = array_reverse($entityRoles);
                  }
                    // var_dump($entityRoles);
                    ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%;">Level</th>
                                    <th style="width: 25%;">Role Name</th>
                                    <th style="width: 15%;">Role Type</th>
                                    <th style="width: 35%;">Description</th>
                                    <th style="width: 10%;">Can Approve</th>
                                    <th style="width: 10%;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($entityRoles as $role): ?>
                                    <tr>
                                        <td><strong><?= isset($role->levelNumber) ? $role->levelNumber : ($role->roleLevel ?? 'N/A') ?></strong></td>
                                        <td>
                                            <strong><?= htmlspecialchars($role->roleName) ?></strong>
                                            <?php if ($role->roleCode): ?>
                                                <small class="text-muted">(<?= htmlspecialchars($role->roleCode) ?>)</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            // Get role type details for badge color and display name
                                            $roleTypeObj = null;
                                            $badgeColor = 'info';
                                            $roleTypeDisplay = 'N/A'; // Default value

                                            // First, check if we have joined role type data (from JOIN in Data::roles())
                                            if (isset($role->roleTypeName) && !empty($role->roleTypeName)) {
                                                // Use the joined data directly
                                                $roleTypeDisplay = $role->roleTypeName;
                                                $roleTypeCode = isset($role->roleTypeCode) ? $role->roleTypeCode : '';

                                                // Map role type codes to Bootstrap badge colors
                                                switch ($roleTypeCode) {
                                                    case 'EXEC':
                                                        $badgeColor = 'danger';
                                                        break;
                                                    case 'MGT':
                                                        $badgeColor = 'warning';
                                                        break;
                                                    case 'SUPV':
                                                        $badgeColor = 'info';
                                                        break;
                                                    case 'OPR':
                                                        $badgeColor = 'success';
                                                        break;
                                                    case 'SUPP':
                                                        $badgeColor = 'secondary';
                                                        break;
                                                    default:
                                                        $badgeColor = 'info';
                                                }
                                            } elseif (isset($role->roleTypeID) && $roleTypes) {
                                                // If we have roleTypeID, find the matching role type
                                                foreach ($roleTypes as $rt) {
                                                    if ($rt->roleTypeID == $role->roleTypeID) {
                                                        $roleTypeObj = $rt;
                                                        break;
                                                    }
                                                }

                                                if ($roleTypeObj) {
                                                    $roleTypeDisplay = $roleTypeObj->roleTypeName;

                                                    // Map role type codes to Bootstrap badge colors
                                                    switch ($roleTypeObj->roleTypeCode) {
                                                        case 'EXEC':
                                                            $badgeColor = 'danger';
                                                            break;
                                                        case 'MGT':
                                                            $badgeColor = 'warning';
                                                            break;
                                                        case 'SUPV':
                                                            $badgeColor = 'info';
                                                            break;
                                                        case 'OPR':
                                                            $badgeColor = 'success';
                                                            break;
                                                        case 'SUPP':
                                                            $badgeColor = 'secondary';
                                                            break;
                                                        default:
                                                            $badgeColor = 'info';
                                                    }
                                                }
                                            } elseif (isset($role->roleType) && $roleTypes) {
                                                // Backward compatibility: try to find by code or name
                                                foreach ($roleTypes as $rt) {
                                                    if ($rt->roleTypeCode === $role->roleType ||
                                                        strcasecmp($rt->roleTypeName, $role->roleType) === 0) {
                                                        $roleTypeObj = $rt;
                                                        break;
                                                    }
                                                }

                                                if ($roleTypeObj) {
                                                    $roleTypeDisplay = $roleTypeObj->roleTypeName;

                                                    // Map role type codes to Bootstrap badge colors
                                                    switch ($roleTypeObj->roleTypeCode) {
                                                        case 'EXEC':
                                                            $badgeColor = 'danger';
                                                            break;
                                                        case 'MGT':
                                                            $badgeColor = 'warning';
                                                            break;
                                                        case 'SUPV':
                                                            $badgeColor = 'info';
                                                            break;
                                                        case 'OPR':
                                                            $badgeColor = 'success';
                                                            break;
                                                        case 'SUPP':
                                                            $badgeColor = 'secondary';
                                                            break;
                                                        default:
                                                            $badgeColor = 'info';
                                                    }
                                                } else {
                                                    // Fallback for old hardcoded values
                                                    $roleTypeDisplay = $role->roleType;
                                                    $roleTypeLower = strtolower($role->roleType ?? '');
                                                    if (strpos($roleTypeLower, 'exec') !== false) {
                                                        $badgeColor = 'danger';
                                                    } elseif (strpos($roleTypeLower, 'manag') !== false) {
                                                        $badgeColor = 'warning';
                                                    } elseif (strpos($roleTypeLower, 'superv') !== false) {
                                                        $badgeColor = 'info';
                                                    } elseif (strpos($roleTypeLower, 'operat') !== false) {
                                                        $badgeColor = 'success';
                                                    } elseif (strpos($roleTypeLower, 'support') !== false) {
                                                        $badgeColor = 'secondary';
                                                    }
                                                }
                                            }
                                            ?>
                                            <span class="badge bg-<?= $badgeColor ?>-transparent">
                                                <?= htmlspecialchars($roleTypeDisplay) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($role->roleDescription ?? 'N/A') ?></td>
                                        <td>
                                            <?= $role->canApprove == 'Y' ? '<i class="fas fa-check text-success"></i> Yes' : '<i class="fas fa-times text-danger"></i> No' ?>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-primary-light editRole"
                                                title="Edit Role"
                                                data-bs-toggle="modal"
                                                data-bs-target="#manageRoleModal"
                                                data-role-id="<?= $role->roleID ?>">
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
                        <div class="avatar avatar-lg bg-success-transparent mx-auto mb-3">
                            <i class="fas fa-user-tie fs-24"></i>
                        </div>
                        <h6 class="mb-2">No Roles Defined</h6>
                        <p class="text-muted mb-3">Define organizational roles to structure your entity.</p>
                        <button type="button" class="btn btn-success btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#manageRoleModal"
                            onclick="addNewRole()">
                            <i class="fas fa-plus me-2"></i>Add First Role
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Existing Org Charts -->
<?php if ($orgCharts && count($orgCharts) > 0): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-sitemap me-2"></i>Existing Organizational Charts</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 40%;">Chart Name</th>
                                <th style="width: 20%;">Positions</th>
                                <th style="width: 15%;">Created</th>
                                <th style="width: 20%;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orgCharts as $index => $chart):
                                $chartPositions = Data::org_chart_position_assignments(['orgChartID' => $chart->orgChartID, 'Suspended' => 'N'], false, $DBConn);
                                $positionCount = $chartPositions ? count($chartPositions) : 0;
                            ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($chart->orgChartName) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-transparent">
                                            <i class="fas fa-layer-group me-1"></i><?= $positionCount ?> Positions
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($chart->DateAdded)) ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-info-light" title="View Chart"
                                            onclick="viewOrgChart(<?= $chart->orgChartID ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-primary-light" title="Edit Chart">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-success-light" title="Export"
                                            onclick="exportOrgChart(<?= $chart->orgChartID ?>)">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Org Chart Visualization -->
<div class="row">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-sitemap me-2"></i>Reporting Structure Visualization</h5>
                </div>
                <?php if ($orgCharts && count($orgCharts) > 0): ?>
                    <select class="form-select form-control-sm" style="width: auto;" id="chartSelector" onchange="loadSelectedChart(this.value)">
                        <option value="">Select Chart to View</option>
                        <?php foreach ($orgCharts as $chart): ?>
                            <option value="<?= $chart->orgChartID ?>"><?= htmlspecialchars($chart->orgChartName) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div id="orgChartContainer" class="text-center py-5">
                    <?php if ($orgChartCount > 0): ?>
                        <div class="avatar avatar-lg bg-secondary-transparent mx-auto mb-3">
                            <i class="fas fa-sitemap fs-24"></i>
                        </div>
                        <h6 class="mb-2">Select an Organization Chart</h6>
                        <p class="text-muted mb-3">Choose a chart from the dropdown above to visualize the hierarchy.</p>
                    <?php else: ?>
                        <div class="avatar avatar-lg bg-secondary-transparent mx-auto mb-3">
                            <i class="fas fa-sitemap fs-24"></i>
                        </div>
                        <h6 class="mb-2">No Organization Charts</h6>
                        <p class="text-muted mb-3">Create an organizational chart to visualize your reporting structure.</p>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="createOrgChart()">
                            <i class="fas fa-plus me-2"></i>Create Organization Chart
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Org Chart Display Area (populated dynamically) -->
                <div id="orgChartDisplay" style="display: none;">
                    <div id="chartCanvas" class="border rounded p-4 bg-light">
                        <!-- Chart will be rendered here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Live Reporting Structure Chart (Generated from Relationships) -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-project-diagram me-2"></i>Live Reporting Chart</h5>
                    <small class="text-muted">Auto-generated from current reporting relationships</small>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <label class="mb-0 me-2 text-muted small">View Mode:</label>
                    <select class="form-select form-select-sm" style="width: auto;" id="chartViewMode" onchange="generateLiveChart()">
                        <option value="hierarchical">🏢 Hierarchical</option>
                        <option value="matrix">⚡ Matrix</option>
                        <option value="flat">📊 Flat</option>
                        <option value="divisional">🗂️ Divisional</option>
                    </select>
                    <button type="button" class="btn btn-primary btn-sm" onclick="generateLiveChart()">
                        <i class="fas fa-sync me-2"></i>Refresh
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="exportLiveChart()">
                        <i class="fas fa-download me-2"></i>Export
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if ($allRelationships && count($allRelationships) > 0):
                    // var_dump($allRelationships);?>
                    <!-- View Mode Legend -->
                    <div class="alert alert-info mb-3" id="viewModeLegend">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong id="legendTitle">Hierarchical View</strong> -
                        <span id="legendDescription">Traditional top-down organizational structure showing clear reporting lines.</span>
                    </div>

                    <!-- Chart Container -->
                    <div id="liveChartContainer" class="position-relative">
                        <div id="liveChartCanvas" class="border rounded p-4 bg-light" style="min-height: 400px; overflow-x: auto;">
                            <!-- Chart will be dynamically generated here -->
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary mb-3" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted">Building organizational chart...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Chart Statistics -->
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <div class="card bg-primary-transparent">
                                <div class="card-body py-2 text-center">
                                    <h6 class="mb-1" id="totalEmployeesCount">0</h6>
                                    <small class="text-muted">Total Employees</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success-transparent">
                                <div class="card-body py-2 text-center">
                                    <h6 class="mb-1" id="managersCount">0</h6>
                                    <small class="text-muted">Managers/Supervisors</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning-transparent">
                                <div class="card-body py-2 text-center">
                                    <h6 class="mb-1" id="levelsCount">0</h6>
                                    <small class="text-muted">Hierarchy Levels</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info-transparent">
                                <div class="card-body py-2 text-center">
                                    <h6 class="mb-1" id="avgSpanControl">0</h6>
                                    <small class="text-muted">Avg Span of Control</small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="avatar avatar-lg bg-warning-transparent mx-auto mb-3">
                            <i class="fas fa-exclamation-triangle fs-24"></i>
                        </div>
                        <h6 class="mb-2">No Reporting Relationships</h6>
                        <p class="text-muted mb-3">Add reporting relationships to generate an organizational chart.</p>
                        <button type="button" class="btn btn-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#manageReportingRelationshipModal"
                            onclick="addReportingRelationship()">
                            <i class="fas fa-plus me-2"></i>Add Reporting Relationship
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Migration Tool Card -->
<?php if ($legacyCount > 0): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="card custom-card border-warning">
            <div class="card-header bg-warning-transparent">
                <div class="card-title">
                    <h6 class="mb-0 text-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Legacy Reporting Data Detected
                    </h6>
                </div>
            </div>
            <div class="card-body">
                <p class="mb-3">
                    <strong><?= $legacyCount ?> reporting relationship(s)</strong> are still using the old system
                    (stored in user_details.supervisorID).
                </p>
                <p class="mb-3">
                    <i class="fas fa-info-circle text-info me-2"></i>
                    Migrate these to the new reporting structure system for enhanced features like:
                </p>
                <ul class="mb-3">
                    <li>Matrix reporting support</li>
                    <li>Multiple relationship types</li>
                    <li>Historical tracking</li>
                    <li>Delegation capabilities</li>
                    <li>Advanced analytics</li>
                </ul>
                <?php if ($canManageReportingMigration): ?>
                <button type="button" class="btn btn-warning btn-sm" onclick="migrateAllLegacyRelationships()">
                    <i class="fas fa-sync me-2"></i>Migrate All Legacy Relationships
                </button>
                <?php endif; ?>
                <button type="button" class="btn btn-outline-warning btn-sm" onclick="showMigrationGuide()">
                    <i class="fas fa-question-circle me-2"></i>Migration Guide
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif;

?>

<!-- SweetAlert2 Library -->
<link rel="stylesheet" href="<?= $base ?>assets/libs/sweetalert2/sweetalert2.min.css">
<script src="<?= $base ?>assets/libs/sweetalert2/sweetalert2.all.min.js"></script>

<script>
// Reporting relationships data from PHP
const reportingRelationshipsData = <?php echo json_encode($allRelationships ?? []); ?>;
const entityEmployeesData = <?php echo json_encode($entityEmployees ?? []); ?>;
const entityRolesData = <?php echo json_encode($entityRoles ?? []); ?>;
const payGradeMappingsData = <?php echo json_encode($payGradeMappings ?? []); ?>;
const roleTypesData = <?php echo json_encode($roleTypes ?? []); ?>;
const legacyMigrationSelection = new Map();
let legacyControlsInitialized = false;

function escapeHtml(unsafe = '') {
    return unsafe
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Helper function to check if a role type is executive (for backward compatibility)
function isExecutiveRoleType(roleType) {
    if (!roleType) return false;
    // Check for role type codes (new system)
    const executiveCodes = ['EXEC', 'Executive'];
    // Check for role type names (old system - backward compatibility)
    if (executiveCodes.includes(roleType) || roleType === 'Executive') {
        return true;
    }
    // Check role types data for executive
    if (roleTypesData && Array.isArray(roleTypesData)) {
        const roleTypeObj = roleTypesData.find(rt => rt.roleTypeCode === roleType || rt.roleTypeName === roleType);
        return roleTypeObj && (roleTypeObj.roleTypeCode === 'EXEC' || roleTypeObj.roleTypeName === 'Executive');
    }
    return false;
}

// ============================================================================
// TABLE PAGINATION FUNCTIONALITY
// ============================================================================
(function() {
    let currentPage = 1;
    let rowsPerPage = 30;
    const tableBody = document.getElementById('reportingRelationshipsTableBody');
    const rowsPerPageSelect = document.getElementById('rowsPerPage');
    const paginationControls = document.getElementById('paginationControls');
    const paginationSummary = document.getElementById('paginationSummary');

    if (!tableBody || !rowsPerPageSelect) return;

    let allRows = Array.from(tableBody.querySelectorAll('.reporting-relationship-row'));
    let totalRows = allRows.length;

    // Initialize pagination
    function initPagination() {
        // Get rows per page from select
        const selectedValue = rowsPerPageSelect.value;
        rowsPerPage = selectedValue === 'all' ? totalRows : parseInt(selectedValue);

        // Reset to page 1 when changing rows per page
        if (selectedValue !== 'all') {
            currentPage = 1;
        }

        renderTable();
        renderPagination();
        updatePaginationSummary();
    }

    // Render table rows based on current page
    function renderTable() {
        allRows.forEach((row, index) => {
            if (rowsPerPage === totalRows) {
                // Show all rows
                row.style.display = '';
                // Update row number
                const rowNumberCell = row.querySelector('.row-number');
                if (rowNumberCell) {
                    rowNumberCell.textContent = index + 1;
                }
            } else {
                const startIndex = (currentPage - 1) * rowsPerPage;
                const endIndex = startIndex + rowsPerPage;

                if (index >= startIndex && index < endIndex) {
                    row.style.display = '';
                    // Update row number to show actual position
                    const rowNumberCell = row.querySelector('.row-number');
                    if (rowNumberCell) {
                        rowNumberCell.textContent = index + 1;
                    }
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }

    // Render pagination controls
    function renderPagination() {
        if (rowsPerPage === totalRows || totalRows === 0) {
            paginationControls.innerHTML = '';
            return;
        }

        const totalPages = Math.ceil(totalRows / rowsPerPage);

        let paginationHTML = '<nav><ul class="pagination pagination-sm mb-0">';

        // Previous button
        paginationHTML += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); goToPage(${currentPage - 1}); return false;">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
        `;

        // Page numbers
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

        // Adjust start page if we're near the end
        if (endPage - startPage < maxVisiblePages - 1) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }

        // First page
        if (startPage > 1) {
            paginationHTML += `
                <li class="page-item">
                    <a class="page-link" href="#" onclick="event.preventDefault(); goToPage(1); return false;">1</a>
                </li>
            `;
            if (startPage > 2) {
                paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        // Page range
        for (let i = startPage; i <= endPage; i++) {
            paginationHTML += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault(); goToPage(${i}); return false;">${i}</a>
                </li>
            `;
        }

        // Last page
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            paginationHTML += `
                <li class="page-item">
                    <a class="page-link" href="#" onclick="event.preventDefault(); goToPage(${totalPages}); return false;">${totalPages}</a>
                </li>
            `;
        }

        // Next button
        paginationHTML += `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="event.preventDefault(); goToPage(${currentPage + 1}); return false;">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        `;

        paginationHTML += '</ul></nav>';
        paginationControls.innerHTML = paginationHTML;
    }

    // Update pagination summary
    function updatePaginationSummary() {
        if (!paginationSummary) return;

        if (rowsPerPage === totalRows) {
            document.getElementById('showingFrom').textContent = '1';
            document.getElementById('showingTo').textContent = totalRows.toString();
            document.getElementById('totalRows').textContent = totalRows.toString();
        } else {
            const startIndex = (currentPage - 1) * rowsPerPage + 1;
            const endIndex = Math.min(currentPage * rowsPerPage, totalRows);

            document.getElementById('showingFrom').textContent = startIndex.toString();
            document.getElementById('showingTo').textContent = endIndex.toString();
            document.getElementById('totalRows').textContent = totalRows.toString();
        }
    }

    // Go to specific page
    window.goToPage = function(page) {
        const totalPages = Math.ceil(totalRows / rowsPerPage);
        if (page < 1 || page > totalPages) return;

        currentPage = page;
        renderTable();
        renderPagination();
        updatePaginationSummary();

        // Scroll to top of table
        const table = document.getElementById('reportingRelationshipsTable');
        if (table) {
            table.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    // Event listener for rows per page change
    rowsPerPageSelect.addEventListener('change', function() {
        initPagination();
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        initPagination();
    });

    // Re-initialize if table is dynamically updated
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function(mutations) {
            const newRows = Array.from(tableBody.querySelectorAll('.reporting-relationship-row'));
            if (newRows.length !== allRows.length) {
                allRows = newRows;
                totalRows = allRows.length;
                initPagination();
            }
        });

        observer.observe(tableBody, { childList: true, subtree: true });
    }

    // Update totalRows when rows change
    function updateTotalRows() {
        allRows = Array.from(tableBody.querySelectorAll('.reporting-relationship-row'));
        totalRows = allRows.length;
    }

    // Expose update function for external use if needed
    window.updateReportingTablePagination = function() {
        updateTotalRows();
        initPagination();
    };
})();

// Initialize Flatpickr for relationship dates
function initializeRelationshipDatePickers() {
    if (document.getElementById('rel_effectiveDate') && !document.getElementById('rel_effectiveDate')._flatpickr) {
        flatpickr('#rel_effectiveDate', {
            dateFormat: 'Y-m-d',
            defaultDate: 'today',
            allowInput: true,
            altInput: true,
            altFormat: 'F j, Y',
            clickOpens: true
        });
    }

    if (document.getElementById('rel_endDate') && !document.getElementById('rel_endDate')._flatpickr) {
        flatpickr('#rel_endDate', {
            dateFormat: 'Y-m-d',
            allowInput: true,
            altInput: true,
            altFormat: 'F j, Y',
            clickOpens: true
        });
    }
}

// Add new reporting relationship
function addReportingRelationship() {
    const modal = document.querySelector('#manageReportingRelationshipModal');
    if (modal) {
        // Reset form
        modal.querySelector('form')?.reset();

        // Clear TomSelect values if initialized
        const employeeSelect = document.getElementById('rel_employeeID');
        const supervisorSelect = document.getElementById('rel_supervisorID');

        if (employeeSelect && employeeSelect.tomselect) {
            employeeSelect.tomselect.clear();
        }

        if (supervisorSelect && supervisorSelect.tomselect) {
            supervisorSelect.tomselect.clear();
            // Set default "No Supervisor" option
            supervisorSelect.tomselect.setValue('0', true);
        }

        // Set defaults
        document.getElementById('rel_relationshipType').value = 'Direct';
        document.getElementById('rel_relationshipStrength').value = '100';
        document.getElementById('rel_reportingFrequency').value = 'Weekly';
        document.getElementById('rel_isCurrent').checked = true;

        // Update modal title
        document.getElementById('reportingRelationshipModalTitle').textContent = 'Add Reporting Relationship';

        // Initialize date pickers
        initializeRelationshipDatePickers();
    }
}

// Add reporting relationship for a specific employee (pre-populated)
function addReportingRelationshipForEmployee(employeeID) {
    const modal = document.querySelector('#manageReportingRelationshipModal');
    if (modal) {
        // Reset form
        modal.querySelector('form')?.reset();

        // Clear TomSelect values if initialized
        const employeeSelect = document.getElementById('rel_employeeID');
        const supervisorSelect = document.getElementById('rel_supervisorID');

        // Set the employee ID (with retry for TomSelect initialization)
        const setEmployeeValue = function(retries = 10) {
            if (employeeSelect && employeeSelect.tomselect) {
                // TomSelect is initialized, set value directly
                employeeSelect.tomselect.setValue(employeeID.toString(), true);
            } else if (retries > 0) {
                // TomSelect not ready yet, wait a bit and retry
                setTimeout(() => {
                    setEmployeeValue(retries - 1);
                }, 100);
            } else {
                // Fallback to regular select
                if (employeeSelect) {
                    employeeSelect.value = employeeID.toString();
                    employeeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        };

        // Wait for modal to be shown before setting values
        const modalInstance = bootstrap.Modal.getInstance(modal);
        if (modalInstance) {
            modal.addEventListener('shown.bs.modal', function setValueOnShow() {
                setEmployeeValue();
                modal.removeEventListener('shown.bs.modal', setValueOnShow);
            }, { once: true });
        } else {
            // If modal is not yet initialized, set value immediately
            setTimeout(() => setEmployeeValue(), 100);
        }

        if (supervisorSelect && supervisorSelect.tomselect) {
            supervisorSelect.tomselect.clear();
            // Set default "No Supervisor" option
            supervisorSelect.tomselect.setValue('0', true);
        }

        // Set defaults
        document.getElementById('rel_relationshipType').value = 'Direct';
        document.getElementById('rel_relationshipStrength').value = '100';
        document.getElementById('rel_reportingFrequency').value = 'Weekly';
        document.getElementById('rel_isCurrent').checked = true;

        // Update modal title
        document.getElementById('reportingRelationshipModalTitle').textContent = 'Add Reporting Relationship';

        // Initialize date pickers
        initializeRelationshipDatePickers();
    }
}

// Edit existing reporting relationship
function editReportingRelationship(relationshipID) {
    const modal = document.querySelector('#manageReportingRelationshipModal');

    if (!modal) {
        console.error('Reporting relationship modal not found');
        return;
    }

    // Update modal title
    document.getElementById('reportingRelationshipModalTitle').textContent = 'Edit Reporting Relationship';

    // Fetch relationship data
    const url = '<?= $base ?>php/scripts/global/admin/get_reporting_relationship.php?relationshipID=' + relationshipID;
    console.log('Fetching relationship data from:', url);

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Relationship data:', data);

            if (data.success && data.relationship) {
                const rel = data.relationship;

                // Populate form fields
                document.getElementById('relationshipID').value = rel.relationshipID || '';

                // Function to set select values (waits for TomSelect if needed)
                const setSelectValue = function(selectId, value, retries = 10) {
                    const select = document.getElementById(selectId);
                    if (!select) return;

                    if (select.tomselect) {
                        // TomSelect is initialized, set value directly
                        select.tomselect.setValue(value || '', true);
                    } else if (retries > 0) {
                        // TomSelect not ready yet, wait a bit and retry
                        setTimeout(() => {
                            setSelectValue(selectId, value, retries - 1);
                        }, 100);
                    } else {
                        // Fallback to regular select
                        select.value = value || '';
                    }
                };

                // Handle employee select (with TomSelect support)
                setSelectValue('rel_employeeID', rel.employeeID || '');

                // Handle supervisor select (with TomSelect support)
                const supervisorValue = rel.supervisorID || '0';
                setSelectValue('rel_supervisorID', supervisorValue);

                document.getElementById('rel_relationshipType').value = rel.relationshipType || 'Direct';
                document.getElementById('rel_relationshipStrength').value = rel.relationshipStrength || '100';
                document.getElementById('rel_reportingFrequency').value = rel.reportingFrequency || 'Weekly';
                document.getElementById('rel_canDelegate').checked = (rel.canDelegate === 'Y');
                document.getElementById('rel_canSubstitute').checked = (rel.canSubstitute === 'Y');
                document.getElementById('rel_notes').value = rel.notes || '';
                document.getElementById('rel_isCurrent').checked = (rel.isCurrent === 'Y');

                // Set dates
                initializeRelationshipDatePickers();

                const effectiveDatePicker = document.getElementById('rel_effectiveDate')._flatpickr;
                const endDatePicker = document.getElementById('rel_endDate')._flatpickr;

                if (effectiveDatePicker && rel.effectiveDate) {
                    effectiveDatePicker.setDate(rel.effectiveDate);
                }

                if (endDatePicker && rel.endDate) {
                    endDatePicker.setDate(rel.endDate);
                }

            } else {
                if (typeof showToast === 'function') {
                    showToast('Error: ' + (data.message || 'Failed to load relationship data'), 'error');
                } else {
                    alert('Error: ' + (data.message || 'Failed to load relationship data'));
                }
            }
        })
        .catch(error => {
            console.error('Error loading relationship:', error);
            if (typeof showToast === 'function') {
                showToast('Error loading relationship data: ' + error.message, 'error');
            } else {
                alert('Error loading relationship data: ' + error.message);
            }
        });
}

// Toast notification function
function showToast(message, type = 'success') {
    // Create toast container if it doesn't exist
    let toastContainer = document.querySelector('.toast-container-reporting');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container-reporting position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }

    // Icon and color mapping
    const config = {
        success: { icon: 'ri-checkbox-circle-line', bg: 'bg-success', text: 'text-white' },
        error: { icon: 'ri-error-warning-line', bg: 'bg-danger', text: 'text-white' },
        warning: { icon: 'ri-alert-line', bg: 'bg-warning', text: 'text-dark' },
        info: { icon: 'ri-information-line', bg: 'bg-info', text: 'text-white' }
    };

    const toastConfig = config[type] || config.success;
    const toastId = 'toast-' + Date.now();

    // Create toast element
    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = `toast align-items-center ${toastConfig.bg} ${toastConfig.text} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');

    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="${toastConfig.icon} me-2"></i>${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    toastContainer.appendChild(toast);

    // Initialize and show toast
    const bsToast = new bootstrap.Toast(toast, {
        autohide: true,
        delay: type === 'error' ? 5000 : 3000
    });

    bsToast.show();

    // Remove toast after it's hidden
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}

/**
 * Confirmation helper that prefers SweetAlert when available.
 * Returns a Promise that resolves to true when the user confirms.
 */
function confirmWithSweetAlert(options = {}) {
    const {
        title = 'Are you sure?',
        text = '',
        html = '',
        icon = 'warning',
        confirmButtonText = 'Yes',
        cancelButtonText = 'Cancel'
    } = options;

    if (typeof Swal !== 'undefined') {
        return Swal.fire({
            title,
            text,
            html,
            icon,
            showCancelButton: true,
            confirmButtonText,
            cancelButtonText,
            reverseButtons: true,
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-primary me-2',
                cancelButton: 'btn btn-outline-secondary'
            }
        }).then(result => result.isConfirmed);
    }

    let fallbackText = text || title;
    if (!fallbackText && html) {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        fallbackText = tempDiv.textContent || tempDiv.innerText || html;
    }

    return Promise.resolve(window.confirm(fallbackText));
}

// ============================================================================
// TOM SELECT INITIALIZATION FOR REPORTING RELATIONSHIP MODAL
// ============================================================================
const manageReportingRelationshipModal = document.getElementById('manageReportingRelationshipModal');
if (manageReportingRelationshipModal) {
    let employeeTomSelect = null;
    let supervisorTomSelect = null;

    // Initialize TomSelect when modal is shown
    manageReportingRelationshipModal.addEventListener('shown.bs.modal', function() {
        const employeeSelect = document.getElementById('rel_employeeID');
        const supervisorSelect = document.getElementById('rel_supervisorID');

        // Initialize employee select
        if (employeeSelect && !employeeSelect.tomselect) {
            const existingEmployeeValue = employeeSelect.value;

            employeeTomSelect = new TomSelect(employeeSelect, {
                placeholder: 'Search for employee...',
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                searchField: ['text'],
                maxOptions: null,
                create: false
            });

            // Set the value if it exists (for edit mode)
            if (existingEmployeeValue) {
                employeeTomSelect.setValue(existingEmployeeValue, true);
            }
        }

        // Initialize supervisor select
        if (supervisorSelect && !supervisorSelect.tomselect) {
            const existingSupervisorValue = supervisorSelect.value;

            supervisorTomSelect = new TomSelect(supervisorSelect, {
                placeholder: 'Search for supervisor...',
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                searchField: ['text'],
                maxOptions: null,
                create: false
            });

            // Set the value if it exists (for edit mode)
            if (existingSupervisorValue) {
                supervisorTomSelect.setValue(existingSupervisorValue, true);
            }
        }
    });

    // Clean up TomSelect when modal is hidden
    manageReportingRelationshipModal.addEventListener('hidden.bs.modal', function() {
        const employeeSelect = document.getElementById('rel_employeeID');
        const supervisorSelect = document.getElementById('rel_supervisorID');

        if (employeeSelect && employeeSelect.tomselect) {
            employeeSelect.tomselect.destroy();
            employeeTomSelect = null;
        }

        if (supervisorSelect && supervisorSelect.tomselect) {
            supervisorSelect.tomselect.destroy();
            supervisorTomSelect = null;
        }
    });
}

// Handle reporting relationship form submission
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltip for roles info button (using manual initialization to avoid conflict with collapse)
    const rolesInfoBtn = document.getElementById('rolesInfoBtn');
    if (rolesInfoBtn) {
        new bootstrap.Tooltip(rolesInfoBtn, {
            placement: 'top',
            trigger: 'hover'
        });
    }

    // Initialize date pickers
    initializeRelationshipDatePickers();

    // Attach listeners to edit buttons
    document.querySelectorAll('.editReportingRelationship').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const relationshipID = this.getAttribute('data-relationship-id');
            if (relationshipID) {
                editReportingRelationship(relationshipID);
            }
        });
    });

    // Handle form submission via AJAX
    const reportingRelationshipForm = document.querySelector('#manageReportingRelationshipModal form');
    if (reportingRelationshipForm) {
        reportingRelationshipForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const form = this;
            const submitBtn = document.getElementById('saveReportingRelationshipBtn');
            const originalBtnText = submitBtn.innerHTML;
            const formData = new FormData(form);
            const actionUrl = form.getAttribute('action');

            // Disable submit button and show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

            // Submit via AJAX
            fetch(actionUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;

                if (data.success) {
                    // Show success toast
                    showToast(data.message || 'Reporting relationship saved successfully', 'success');

                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('manageReportingRelationshipModal'));
                    if (modal) {
                        modal.hide();
                    }

                    // Reload page after a short delay to refresh the table
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    // Show error toast
                    showToast(data.message || 'Failed to save reporting relationship', 'error');
                }
            })
            .catch(error => {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;

                // Show error toast
                console.error('Error:', error);
                showToast('An error occurred while saving the relationship. Please try again.', 'error');
            });
        });
    }
});

function initializeLegacyMigrationControls() {
    if (legacyControlsInitialized) return;
    legacyControlsInitialized = true;

    // Delegate checkbox changes
    document.addEventListener('change', function(event) {
        if (event.target.classList && event.target.classList.contains('legacy-migrate-checkbox')) {
            handleLegacyCheckboxChange(event.target);
        }
    });

    const selectAllCheckbox = document.getElementById('selectAllLegacy');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            toggleVisibleLegacyCheckboxes(this.checked);
        });
    }

    const rowsPerPageSelect = document.getElementById('rowsPerPage');
    if (rowsPerPageSelect) {
        rowsPerPageSelect.addEventListener('change', function() {
            setTimeout(updateLegacyMigrationSelectionUI, 150);
        });
    }

    const migrateBtn = document.getElementById('migrateSelectedLegacyBtn');
    if (migrateBtn) {
        migrateBtn.addEventListener('click', function() {
            migrateSelectedLegacyRelationships(migrateBtn);
        });
    }

    const clearBtn = document.getElementById('clearLegacySelectionBtn');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            clearLegacySelection();
        });
    }
}

function handleLegacyCheckboxChange(checkbox) {
    if (!checkbox) return;
    const employeeID = checkbox.value;

    if (checkbox.checked) {
        legacyMigrationSelection.set(employeeID, true);
    } else {
        legacyMigrationSelection.delete(employeeID);
    }

    updateLegacyMigrationSelectionUI();
}

function getVisibleLegacyCheckboxes() {
    const checkboxes = document.querySelectorAll('.legacy-migrate-checkbox');
    return Array.from(checkboxes).filter(cb => {
        const row = cb.closest('tr');
        return row && row.offsetParent !== null;
    });
}

function toggleVisibleLegacyCheckboxes(isChecked) {
    const visibleCheckboxes = getVisibleLegacyCheckboxes();
    visibleCheckboxes.forEach(cb => {
        if (cb.disabled) return;
        cb.checked = isChecked;
        cb.dispatchEvent(new Event('change', { bubbles: true }));
    });
}

function updateLegacyMigrationSelectionUI() {
    const selectedCount = legacyMigrationSelection.size;
    const countElement = document.getElementById('selectedLegacyCount');
    if (countElement) {
        countElement.textContent = selectedCount;
    }

    const migrateBtn = document.getElementById('migrateSelectedLegacyBtn');
    if (migrateBtn) {
        migrateBtn.disabled = selectedCount === 0;
    }

    const clearBtn = document.getElementById('clearLegacySelectionBtn');
    if (clearBtn) {
        clearBtn.disabled = selectedCount === 0;
    }

    const selectAllCheckbox = document.getElementById('selectAllLegacy');
    if (selectAllCheckbox) {
        const visibleCheckboxes = getVisibleLegacyCheckboxes().filter(cb => !cb.disabled);
        const checkedVisible = visibleCheckboxes.filter(cb => cb.checked);

        if (visibleCheckboxes.length === 0) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
        } else if (checkedVisible.length === visibleCheckboxes.length) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = true;
        } else if (checkedVisible.length === 0) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
        } else {
            selectAllCheckbox.indeterminate = true;
            selectAllCheckbox.checked = false;
        }
    }
}

function clearLegacySelection() {
    if (legacyMigrationSelection.size === 0) {
        return;
    }

    legacyMigrationSelection.clear();
    document.querySelectorAll('.legacy-migrate-checkbox').forEach(cb => {
        cb.checked = false;
    });
    updateLegacyMigrationSelectionUI();
}

function migrateSelectedLegacyRelationships(triggerBtn = null) {
    const selectedIds = Array.from(legacyMigrationSelection.keys());
    if (!selectedIds.length) {
        showToast('Please select at least one legacy reporting relationship to migrate.', 'warning');
        return;
    }

    const previewNames = selectedIds.slice(0, 5).map(id => {
        const row = document.querySelector(`.reporting-relationship-row[data-employee-id="${id}"]`);
        return row ? (row.dataset.employeeName || `Employee #${id}`) : `Employee #${id}`;
    });

    const listItemsHtml = previewNames
        .map(name => `<li>${escapeHtml(name)}</li>`)
        .join('');

    let htmlContent = `
        <p class="mb-2">Migrate ${selectedIds.length} selected employee(s) to the new reporting system?</p>
        <ul class="text-start ps-4 mb-0">
            ${listItemsHtml}
        </ul>
    `;

    if (selectedIds.length > previewNames.length) {
        htmlContent += `<p class="mt-2 text-muted">...and ${selectedIds.length - previewNames.length} more</p>`;
    }

    confirmWithSweetAlert({
        title: 'Confirm Migration',
        html: htmlContent,
        icon: 'warning',
        confirmButtonText: selectedIds.length > 1 ? 'Migrate Employees' : 'Migrate Employee',
        cancelButtonText: 'Cancel'
    }).then(confirmed => {
        if (!confirmed) {
            return;
        }
        performLegacyMigrationRequest(selectedIds, triggerBtn);
    });
}

function performLegacyMigrationRequest(employeeIDs, triggerBtn = null) {
    if (!Array.isArray(employeeIDs) || employeeIDs.length === 0) {
        return;
    }

    const ids = employeeIDs.map(id => parseInt(id, 10)).filter(id => id);
    if (!ids.length) {
        showToast('No valid employees selected for migration.', 'warning');
        return;
    }

    const buttonToDisable = triggerBtn || (ids.length > 1 ? document.getElementById('migrateSelectedLegacyBtn') : null);
    let originalButtonHtml = '';

    if (buttonToDisable) {
        originalButtonHtml = buttonToDisable.innerHTML;
        buttonToDisable.disabled = true;
        buttonToDisable.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';
    }

    const formData = new FormData();
    formData.append('entityID', <?= $entityID ?>);

    if (ids.length === 1) {
        formData.append('employeeID', ids[0]);
    } else {
        ids.forEach(id => formData.append('employeeIDs[]', id));
    }

    fetch('<?= $base ?>php/scripts/global/admin/migrate_supervisor_relationships.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success || data.partialSuccess) {
            showToast(data.message || 'Migration completed successfully.', 'success');
            setTimeout(() => window.location.reload(), 1200);
        } else {
            showToast(data.message || 'Failed to migrate selected relationships.', 'error');
        }
    })
    .catch(error => {
        console.error('Migration error:', error);
        showToast('An unexpected error occurred during migration.', 'error');
    })
    .finally(() => {
        if (buttonToDisable) {
            buttonToDisable.disabled = false;
            buttonToDisable.innerHTML = originalButtonHtml || buttonToDisable.innerHTML;
        }
    });
}

function migrateRelationship(employeeID, supervisorID, triggerBtn = null) {
    const supervisor = parseInt(supervisorID, 10);
    if (!employeeID || !supervisor) {
        showToast('This employee does not have a supervisor record to migrate.', 'warning');
        return;
    }

    confirmWithSweetAlert({
        title: 'Migrate Relationship?',
        text: 'This will copy the current legacy supervisor record into the new reporting system.',
        confirmButtonText: 'Migrate',
        cancelButtonText: 'Cancel',
        icon: 'question'
    }).then(confirmed => {
        if (!confirmed) {
            return;
        }
        performLegacyMigrationRequest([employeeID], triggerBtn);
    });
}

function migrateAllLegacyRelationships() {
    confirmWithSweetAlert({
        title: 'Migrate All Legacy Relationships?',
        text: 'This will migrate all <?= $legacyCount ?> legacy relationships into the new reporting system while preserving original data.',
        confirmButtonText: 'Migrate All',
        cancelButtonText: 'Cancel',
        icon: 'warning'
    }).then(confirmed => {
        if (!confirmed) {
            return;
        }

        const formData = new FormData();
        formData.append('entityID', <?= $entityID ?>);

        fetch('<?= $base ?>php/scripts/global/admin/migrate_supervisor_relationships.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success || data.partialSuccess) {
                showToast(data.message || 'Legacy relationships migrated successfully.', 'success');
                setTimeout(() => window.location.reload(), 1200);
            } else {
                showToast(data.message || 'Failed to migrate legacy relationships.', 'error');
            }
        })
        .catch(error => {
            console.error('Migration error:', error);
            showToast('An unexpected error occurred during migration.', 'error');
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    initializeLegacyMigrationControls();
    updateLegacyMigrationSelectionUI();

    if (typeof window.goToPage === 'function') {
        const originalGoToPage = window.goToPage;
        window.goToPage = function(page) {
            originalGoToPage(page);
            setTimeout(updateLegacyMigrationSelectionUI, 50);
        };
    }
});

function showMigrationGuide() {
    const guide = `
    <div class="text-start">
        <h6 class="text-primary mb-3">Migration Guide</h6>
        <p><strong>What happens during migration:</strong></p>
        <ol>
            <li>Legacy supervisor relationships are copied to the new system</li>
            <li>Relationship type is set to "Direct"</li>
            <li>Effective date is set from employment start date</li>
            <li>Relationship strength is set to 100% (primary)</li>
            <li>Original data in user_details.supervisorID is preserved</li>
        </ol>
        <p class="mt-3"><strong>Benefits:</strong></p>
        <ul>
            <li>Enhanced relationship management</li>
            <li>Support for matrix reporting</li>
            <li>Historical tracking capabilities</li>
            <li>Advanced delegation features</li>
        </ul>
        <p class="mt-3 text-muted"><em>Note: This is a safe operation - your existing data will not be deleted.</em></p>
    </div>
    `;

    // Show in a modal or alert
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = guide;
    if (typeof showToast === 'function') {
        showToast(tempDiv.textContent, 'info');
    } else {
        alert(tempDiv.textContent);
    }
}

function viewOrgChart(orgChartID) {
    // Load and display the selected org chart
    document.getElementById('chartSelector').value = orgChartID;
    loadSelectedChart(orgChartID);
}

function loadSelectedChart(orgChartID) {
    if (!orgChartID) {
        document.getElementById('orgChartContainer').style.display = 'block';
        document.getElementById('orgChartDisplay').style.display = 'none';
        return;
    }

    document.getElementById('orgChartContainer').style.display = 'none';
    document.getElementById('orgChartDisplay').style.display = 'block';

    // TODO: Load and render the org chart
    document.getElementById('chartCanvas').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Loading organization chart...</p>
        </div>
    `;

    // Fetch chart data
    fetch('<?= $base ?>php/scripts/global/admin/get_org_chart.php?orgChartID=' + orgChartID)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderOrgChart(data.positions);
            } else {
                document.getElementById('chartCanvas').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Error loading chart: ${data.message || 'Unknown error'}
                    </div>
                `;
            }
        })
        .catch(error => {
            document.getElementById('chartCanvas').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Error: ${error.message}
                </div>
            `;
        });
}

function renderOrgChart(positions) {
    if (!positions || positions.length === 0) {
        document.getElementById('chartCanvas').innerHTML = `
            <div class="text-center py-4">
                <p class="text-muted">No positions defined in this chart.</p>
            </div>
        `;
        return;
    }

    // Simple hierarchical display (can be enhanced with a proper chart library)
    let html = '<div class="org-chart-simple">';

    // Build hierarchy
    const hierarchy = buildPositionHierarchy(positions);
    html += renderPositionHierarchy(hierarchy, 0);

    html += '</div>';
    document.getElementById('chartCanvas').innerHTML = html;
}

function buildPositionHierarchy(positions) {
    const positionMap = {};
    const roots = [];

    // Create position map
    positions.forEach(pos => {
        positionMap[pos.positionID] = {...pos, children: []};
    });

    // Build tree
    positions.forEach(pos => {
        if (pos.positionParentID && positionMap[pos.positionParentID]) {
            positionMap[pos.positionParentID].children.push(positionMap[pos.positionID]);
        } else {
            roots.push(positionMap[pos.positionID]);
        }
    });

    return roots;
}

function renderPositionHierarchy(positions, level) {
    let html = '';
    const indent = level * 40;

    positions.forEach(pos => {
        html += `
            <div class="position-node" style="margin-left: ${indent}px; margin-bottom: 10px;">
                <div class="card border-primary" style="display: inline-block; min-width: 300px;">
                    <div class="card-body py-2 px-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm bg-primary-transparent me-2">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <strong>${pos.positionTitle}</strong>
                                ${pos.positionCode ? '<small class="text-muted d-block">' + pos.positionCode + '</small>' : ''}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        if (pos.children && pos.children.length > 0) {
            html += renderPositionHierarchy(pos.children, level + 1);
        }
    });

    return html;
}

function createOrgChart() {
    // Open the create org chart modal
    const modal = new bootstrap.Modal(document.getElementById('createOrgChartModal'));
    modal.show();

    // Reset form
    document.getElementById('createOrgChartForm').reset();
}

function exportOrgChart(orgChartID) {
    if (typeof showToast === 'function') {
        showToast('Export org chart ID: ' + orgChartID, 'info');
    } else {
        alert('Export org chart ID: ' + orgChartID);
    }
    // TODO: Implement export functionality
}

function generateOrgChart() {
    if (typeof showToast === 'function') {
        showToast('Organization chart generation feature will be implemented soon.', 'info');
    } else {
        alert('Organization chart generation feature will be implemented soon.');
    }
    // TODO: Implement org chart visualization using a library like OrgChart.js or D3.js
}

// ============================================================================
// Live Reporting Chart Generation Functions
// ============================================================================

// Generate live organizational chart from reporting relationships
function generateLiveChart() {
    const viewMode = document.getElementById('chartViewMode')?.value || 'hierarchical';
    const canvas = document.getElementById('liveChartCanvas');

    if (!canvas) return;

    // Update legend
    updateViewModeLegend(viewMode);
    console.log(`======= entityEmployeesData =======`);
    console.log(entityEmployeesData);

    // Build the organizational structure from relationships, roles, and pay grades
    const orgStructure = buildOrgStructureFromRelationships(reportingRelationshipsData, entityEmployeesData, entityRolesData, payGradeMappingsData);

    console.log(orgStructure);
    // Generate chart based on view mode
    let chartHTML = '';
    switch(viewMode) {
        case 'hierarchical':
            chartHTML = renderHierarchicalChart(orgStructure);
            break;
        case 'matrix':
            chartHTML = renderMatrixChart(orgStructure);
            break;
        case 'flat':
            chartHTML = renderFlatChart(orgStructure);
            break;
        case 'divisional':
            chartHTML = renderDivisionalChart(orgStructure);
            break;
        default:
            chartHTML = renderHierarchicalChart(orgStructure);
    }

    canvas.innerHTML = chartHTML;

    // Update statistics
    updateChartStatistics(orgStructure);
}

// Update view mode legend
function updateViewModeLegend(mode) {
    const legendTitle = document.getElementById('legendTitle');
    const legendDescription = document.getElementById('legendDescription');

    const legends = {
        hierarchical: {
            title: 'Hierarchical View',
            desc: 'Traditional top-down organizational structure showing clear reporting lines.'
        },
        matrix: {
            title: 'Matrix View',
            desc: 'Dual reporting relationships showing both direct and dotted-line connections.'
        },
        flat: {
            title: 'Flat View',
            desc: 'All employees displayed at their reporting levels with minimal hierarchy.'
        },
        divisional: {
            title: 'Divisional View',
            desc: 'Grouped by divisions, departments, or business units showing organizational segments.'
        }
    };

    if (legendTitle && legendDescription && legends[mode]) {
        legendTitle.textContent = legends[mode].title;
        legendDescription.textContent = legends[mode].desc;
    }
}

// Build organizational structure from relationships
function buildOrgStructureFromRelationships(relationships, employees, roles, payGradeMappings) {
    console.log(`======= buildOrgStructureFromRelationships =======`);
    console.log(relationships);
    console.log(employees);
    console.log(roles);
    console.log(payGradeMappings);
    const employeeMap = {};
    const roleMap = {}; // Map jobTitleID or roleName to role
    const payGradeMap = {}; // Map jobTitleID to pay grade
    const structure = {
        employees: {},
        hierarchy: {},
        roots: [],
        stats: {
            totalEmployees: 0,
            managers: 0,
            levels: null,
            avgSpanControl: null
        }
    };

    // Build role map for quick lookup
    if (roles && Array.isArray(roles)) {
        roles.forEach(role => {
            // Map by jobTitleID if available
            if (role.jobTitleID) {
                roleMap[role.jobTitleID] = role;
            }
            // Also map by roleName for fallback matching
            if (role.roleName) {
                roleMap[role.roleName.toLowerCase()] = role;
            }
        });
    }

    // Build pay grade map for quick lookup
    if (payGradeMappings && typeof payGradeMappings === 'object') {
        Object.keys(payGradeMappings).forEach(jobTitleID => {
            const payGrade = payGradeMappings[jobTitleID];
            if (payGrade && payGrade.payGradeID) {
                payGradeMap[jobTitleID] = payGrade;
            }
        });
    }

    console.log(`======= roleMap =======`);
    console.log(roleMap);
    console.log(`======= payGradeMap =======`);
    console.log(payGradeMap);

    // Create employee map with role and pay grade information
    if (employees && Array.isArray(employees)) {
        employees.forEach(emp => {
            // Find matching role for this employee
            let employeeRole = null;
            if (emp.jobTitleID && roleMap[emp.jobTitleID]) {
                employeeRole = roleMap[emp.jobTitleID];
            } else if (emp.jobTitle && roleMap[emp.jobTitle.toLowerCase()]) {
                employeeRole = roleMap[emp.jobTitle.toLowerCase()];
            }

            // Find matching pay grade for this employee
            let employeePayGrade = null;
            if (emp.jobTitleID && payGradeMap[emp.jobTitleID]) {
                employeePayGrade = payGradeMap[emp.jobTitleID];
            }

            // Determine hierarchy level - use the most appropriate level
            // Pay grade levels: 1=Executive/C-Suite, 2=Senior Mgmt, ..., 8=Trainee/Intern
            // Role levels: 0=Board, 1=CEO/Executive, 2=C-Suite, ..., 8=Entry Level
            // Both use lower numbers = higher authority, but pay grade starts at 1, role starts at 0

            let hierarchyLevel = null;

            // Priority 1: Role level (most authoritative for organizational structure)
            if (employeeRole && employeeRole.roleLevel !== null && employeeRole.roleLevel !== undefined) {
                const roleLevel = parseInt(employeeRole.roleLevel);
                // For executives, always use role level
                if (isExecutiveRoleType(employeeRole.roleType) && roleLevel <= 2) {
                    hierarchyLevel = roleLevel;
                } else {
                    hierarchyLevel = roleLevel;
                }
            }

            // Priority 2: Pay grade level (use as fallback only)
            // NOTE: Form says "Lower number = junior, higher = senior" - this is INVERTED from role levels
            // Role levels: 0=top (Board), 8=bottom (Entry)
            // Pay grade (if form is correct): 1=junior (bottom), 8=senior (top) - INVERTED!
            // So we need to invert pay grade levels: payGradeLevel 1-8 → hierarchyLevel 8-1
            if (employeePayGrade && employeePayGrade.gradeLevel !== null && employeePayGrade.gradeLevel !== undefined) {
                const payGradeLevel = parseInt(employeePayGrade.gradeLevel);

                if (hierarchyLevel === null) {
                    // No role level, use pay grade level but INVERT it
                    // Pay grade Level 1 (junior per form) → hierarchy Level 8 (bottom)
                    // Pay grade Level 8 (senior per form) → hierarchy Level 1 (top, but not 0)
                    // Convert: payGradeLevel 1-8 → hierarchyLevel 8-1 (inverted)
                    const invertedLevel = 9 - payGradeLevel; // 1→8, 2→7, ..., 8→1
                    hierarchyLevel = invertedLevel;
                } else {
                    // We have role level - trust it completely, don't override
                    // Role level is authoritative for organizational structure
                    // Pay grade is just for compensation, not hierarchy
                }
            }

            employeeMap[emp.ID] = {
                id: emp.ID,
                name: (emp.fullName || emp.FirstName + ' ' + emp.Surname || emp.EmployeeName || 'N/A').trim(),
                jobTitle: emp.jobTitle || 'N/A',
                email: emp.Email || '',
                jobTitleID: emp.jobTitleID || null,
                role: employeeRole,
                roleLevel: employeeRole ? parseInt(employeeRole.roleLevel) : null,
                roleType: employeeRole ? employeeRole.roleType : null,
                payGrade: employeePayGrade,
                payGradeLevel: employeePayGrade ? parseInt(employeePayGrade.gradeLevel) : null,
                hierarchyLevel: hierarchyLevel, // Combined/prioritized level
                reports: [],
                supervisors: [],
                level: -1,
                isManager: false
            };
        });
    }

    // Build relationships - employee reports TO supervisor
    if (relationships && Array.isArray(relationships)) {
        relationships.forEach(rel => {
            // Skip if supervisorID is 0 or null (top-level employee)
            if (!rel.supervisorID || rel.supervisorID == 0) return;

            const emp = employeeMap[rel.employeeID];
            const supervisor = employeeMap[rel.supervisorID];

            if (emp && supervisor) {
                // Employee reports to supervisor
                if (!emp.supervisors) emp.supervisors = [];
                emp.supervisors.push({
                    supervisorID: rel.supervisorID,
                    type: rel.relationshipType || 'Direct',
                    strength: rel.relationshipStrength || '100'
                });

                // Supervisor has direct reports
                if (!supervisor.directReports) supervisor.directReports = [];
                supervisor.directReports.push({
                    employeeID: rel.employeeID,
                    type: rel.relationshipType || 'Direct',
                    strength: rel.relationshipStrength || '100'
                });

                supervisor.isManager = true;
            }
        });
    }

    // Find root nodes (those with no supervisor - supervisorID is 0/null or no supervisor assigned)
    // Prioritize executives and C-suite based on roles
    structure.roots = Object.values(employeeMap).filter(emp => {
        // Check if this employee has no supervisor in relationships
        const hasSupervisor = relationships && relationships.some(r =>
            r.employeeID === emp.id && r.supervisorID && r.supervisorID != 0
        );
        return !hasSupervisor;
    });

    // Sort roots by role level (executives first) before calculating hierarchy
    structure.roots.sort((a, b) => {
        // Executives and C-suite (roleType = "Executive" or "EXEC", roleLevel 0-2) go first
        const aIsExecutive = isExecutiveRoleType(a.roleType) && a.roleLevel !== null && a.roleLevel <= 2;
        const bIsExecutive = isExecutiveRoleType(b.roleType) && b.roleLevel !== null && b.roleLevel <= 2;

        if (aIsExecutive && !bIsExecutive) return -1;
        if (!aIsExecutive && bIsExecutive) return 1;

        // If both are executives or both are not, sort by role level
        if (a.roleLevel !== null && b.roleLevel !== null) {
            return a.roleLevel - b.roleLevel; // Lower level = higher authority
        }
        if (a.roleLevel !== null) return -1;
        if (b.roleLevel !== null) return 1;

        // Fallback to name sorting
        return a.name.localeCompare(b.name);
    });

    // Calculate hierarchy levels starting from roots, considering role levels
    calculateHierarchyLevels(employeeMap, structure.roots);

    // Ensure all employees have a level assigned (handle any disconnected nodes)
    // Use iterative approach to handle all employees, including those in complex chains
    let changed = true;
    let iterations = 0;
    const maxIterations = 100; // Prevent infinite loops

    while (changed && iterations < maxIterations) {
        changed = false;
        iterations++;

        Object.values(employeeMap).forEach(emp => {
            if (emp.level === -1) {
                // Employee not reached by traversal - try to find their level through supervisor
                if (emp.supervisors && emp.supervisors.length > 0) {
                    // Find the primary supervisor (Direct relationship with highest strength)
                    const primarySupervisor = emp.supervisors
                        .filter(s => s.type === 'Direct' || !s.type)
                        .sort((a, b) => (parseInt(b.strength) || 100) - (parseInt(a.strength) || 100))[0];

                    if (primarySupervisor) {
                        const supervisor = employeeMap[primarySupervisor.supervisorID];
                        if (supervisor && supervisor.level >= 0) {
                            // Use supervisor's level + 1, but consider role/pay grade level as minimum
                            let calculatedLevel = supervisor.level + 1;

                            // Priority: roleLevel (most authoritative), then hierarchyLevel, then payGradeLevel
                            if (emp.roleLevel !== null && emp.roleLevel !== undefined) {
                                // Role level takes highest priority
                                if (isExecutiveRoleType(emp.roleType) && emp.roleLevel <= 2) {
                                    calculatedLevel = emp.roleLevel;
                                } else {
                                    // Use the higher of calculated (from supervisor) or role level
                                    // Role level should not be lower than supervisor + 1 for non-executives
                                    calculatedLevel = Math.max(calculatedLevel, emp.roleLevel);
                                }
                            } else if (emp.hierarchyLevel !== null && emp.hierarchyLevel !== undefined) {
                                // Fallback to combined hierarchy level
                                if (isExecutiveRoleType(emp.roleType) && emp.hierarchyLevel <= 2) {
                                    calculatedLevel = emp.hierarchyLevel;
                                } else {
                                    calculatedLevel = Math.max(calculatedLevel, emp.hierarchyLevel);
                                }
                            } else if (emp.payGradeLevel !== null && emp.payGradeLevel !== undefined) {
                                // Fallback to pay grade level - INVERT it
                                // Pay grade Level 1 (junior per form) → hierarchy Level 8 (bottom)
                                // Pay grade Level 8 (senior per form) → hierarchy Level 1 (top)
                                const invertedPayGradeLevel = 9 - emp.payGradeLevel; // 1→8, 8→1
                                calculatedLevel = Math.max(calculatedLevel, invertedPayGradeLevel);
                            }

                            emp.level = calculatedLevel;
                            changed = true;
                        }
                    }
                } else {
                    // No supervisor - check if executive/leader based on role or pay grade
                    // Priority: roleLevel > hierarchyLevel > payGradeLevel
                    if (emp.roleLevel !== null && emp.roleLevel !== undefined) {
                        // Role level takes highest priority
                        if (isExecutiveRoleType(emp.roleType) && emp.roleLevel <= 2) {
                            emp.level = emp.roleLevel; // 0-2 for executives
                        } else {
                            // Non-executives: use role level, but ensure it's reasonable
                            // Role levels 0-2 should be top level, 3-4 management, 5-6 operational, 7-8 entry
                            emp.level = emp.roleLevel;
                        }
                    } else if (emp.hierarchyLevel !== null && emp.hierarchyLevel !== undefined) {
                        // Fallback to combined hierarchy level
                        if (isExecutiveRoleType(emp.roleType) && emp.hierarchyLevel <= 2) {
                            emp.level = emp.hierarchyLevel;
                        } else {
                            emp.level = emp.hierarchyLevel;
                        }
                    } else if (emp.payGradeLevel !== null && emp.payGradeLevel !== undefined) {
                        // Fallback to pay grade level - INVERT it
                        // Form says "Lower number = junior, higher = senior"
                        // So: Pay grade Level 1 (junior) → hierarchy Level 8 (bottom)
                        //     Pay grade Level 8 (senior) → hierarchy Level 1 (top, but not 0)
                        const invertedPayGradeLevel = 9 - emp.payGradeLevel; // 1→8, 2→7, ..., 8→1
                        emp.level = invertedPayGradeLevel;
                    } else {
                        // No supervisor and no role/pay grade - assign level 0 as root
                        emp.level = 0;
                    }
                    changed = true;
                }
            }
        });
    }

    // Handle any remaining employees without levels (shouldn't happen, but safety check)
    Object.values(employeeMap).forEach(emp => {
        if (emp.level === -1) {
            // Assign to bottom level as fallback
            const maxLevel = Math.max(...Object.values(employeeMap).map(e => e.level >= 0 ? e.level : 0));
            emp.level = maxLevel + 1;
        }
    });

    // Calculate statistics
    structure.employees = employeeMap;
    structure.stats.totalEmployees = Object.keys(employeeMap).length;
    structure.stats.managers = Object.values(employeeMap).filter(e => e.isManager).length;

    // Find the maximum level (excluding any 999 placeholders)
    const validLevels = Object.values(employeeMap).map(e => e.level).filter(l => l >= 0 && l < 999);
    structure.stats.levels = validLevels.length > 0 ? Math.max(...validLevels) + 1 : 1;

    // Fix any employees with placeholder level 999
    Object.values(employeeMap).forEach(emp => {
        if (emp.level === 999) {
            emp.level = structure.stats.levels;
        }
    });

    // Calculate average span of control
    const managersWithReports = Object.values(employeeMap).filter(e =>
        e.isManager && e.directReports && e.directReports.length > 0
    );
    if (managersWithReports.length > 0) {
        const totalReports = managersWithReports.reduce((sum, m) =>
            sum + (m.directReports ? m.directReports.length : 0), 0
        );
        structure.stats.avgSpanControl = (totalReports / managersWithReports.length).toFixed(1);
    }

    return structure;
}

// Calculate hierarchy levels considering role levels and types
function calculateHierarchyLevels(employeeMap, roots, currentLevel = 0) {
    if (!roots || roots.length === 0) return;

    roots.forEach(root => {
        // Determine level based on role, pay grade, or reporting structure
        let assignedLevel = currentLevel;

        // Priority: roleLevel (most authoritative), then hierarchyLevel, then payGradeLevel
        // Role levels: 0=Board, 1=CEO, 2=C-Suite, 3=Director, 4=Manager, 5=Supervisor, 6=Senior Staff, 7=Staff, 8=Entry
        // Pay grade levels: Based on documentation: 1=Executive/C-Suite, 2=Senior Mgmt, ..., 8=Trainee/Intern
        // BUT form says "Lower number = junior, higher = senior" - this contradicts documentation
        // We'll trust role levels as authoritative and only use pay grade as fallback

        if (root.roleLevel !== null && root.roleLevel !== undefined) {
            // Role level takes highest priority - this is the authoritative source
            if (isExecutiveRoleType(root.roleType) && root.roleLevel <= 2) {
                assignedLevel = root.roleLevel; // 0-2 for executives
            } else {
                // Use role level, but don't go below current level from reporting structure
                assignedLevel = Math.min(currentLevel, root.roleLevel);
            }
        } else if (root.hierarchyLevel !== null && root.hierarchyLevel !== undefined) {
            // Use the combined hierarchy level (but role should have been checked first)
            if (isExecutiveRoleType(root.roleType) && root.hierarchyLevel <= 2) {
                assignedLevel = root.hierarchyLevel;
            } else {
                assignedLevel = Math.min(currentLevel, root.hierarchyLevel);
            }
        } else if (root.payGradeLevel !== null && root.payGradeLevel !== undefined) {
            // Fallback to pay grade level - need to handle inversion
            // If form label is correct: Level 1 = junior (bottom), Level 8 = senior (top) - INVERTED
            // If documentation is correct: Level 1 = Executive (top), Level 8 = Trainee (bottom) - NOT inverted
            // Since user reports associates at top and interns at top, suggests pay grades are inverted
            // So we'll invert: payGradeLevel 1-8 → hierarchyLevel 8-1
            const payGradeLevel = root.payGradeLevel; // 1-8
            const convertedLevel = 9 - payGradeLevel; // 1→8, 2→7, ..., 8→1
            assignedLevel = Math.min(currentLevel, convertedLevel);
        }

        // Only set level if not already set or if new level is lower (higher authority)
        if (root.level === -1 || root.level > assignedLevel) {
            root.level = assignedLevel;
        }

        // Find direct reports (employees who report to this supervisor)
        const directReports = root.directReports ?
            root.directReports.map(r => employeeMap[r.employeeID]).filter(e => e && e !== null) : [];

        if (directReports.length > 0) {
            // Sort direct reports by hierarchy level (role/pay grade) before processing
            directReports.sort((a, b) => {
                // Executives first
                const aIsExecutive = isExecutiveRoleType(a.roleType) && a.roleLevel !== null && a.roleLevel <= 2;
                const bIsExecutive = isExecutiveRoleType(b.roleType) && b.roleLevel !== null && b.roleLevel <= 2;

                if (aIsExecutive && !bIsExecutive) return -1;
                if (!aIsExecutive && bIsExecutive) return 1;

                // Then by hierarchy level (combined role/pay grade)
                if (a.hierarchyLevel !== null && b.hierarchyLevel !== null) {
                    return a.hierarchyLevel - b.hierarchyLevel;
                }
                if (a.hierarchyLevel !== null) return -1;
                if (b.hierarchyLevel !== null) return 1;

                // Then by role level
                if (a.roleLevel !== null && b.roleLevel !== null) {
                    return a.roleLevel - b.roleLevel;
                }
                if (a.roleLevel !== null) return -1;
                if (b.roleLevel !== null) return 1;

                // Then by pay grade level
                if (a.payGradeLevel !== null && b.payGradeLevel !== null) {
                    return a.payGradeLevel - b.payGradeLevel;
                }
                if (a.payGradeLevel !== null) return -1;
                if (b.payGradeLevel !== null) return 1;

                return 0;
            });

            // Calculate next level based on current employee's level
            const nextLevel = root.level + 1;

            // Recursively calculate levels for direct reports
            calculateHierarchyLevels(employeeMap, directReports, nextLevel);
        }
    });
}

// Render Hierarchical Chart
function renderHierarchicalChart(structure) {
    let html = '<div class="org-chart-hierarchical">';

    // Group by level
    const byLevel = {};
    Object.values(structure.employees).forEach(emp => {
        if (!byLevel[emp.level]) byLevel[emp.level] = [];
        byLevel[emp.level].push(emp);
    });

    // Sort levels to ensure proper order (0 = top, higher numbers = bottom)
    const sortedLevels = Object.keys(byLevel).map(Number).sort((a, b) => a - b);

    // Render level by level from top to bottom
    sortedLevels.forEach(level => {
        if (!byLevel[level] || byLevel[level].length === 0) return;

        // Sort employees within level by role/pay grade hierarchy, then by name
        const sortedEmployees = byLevel[level].sort((a, b) => {
            // Executives first (roleType = "Executive" or "EXEC", roleLevel 0-2)
            const aIsExecutive = isExecutiveRoleType(a.roleType) && a.roleLevel !== null && a.roleLevel <= 2;
            const bIsExecutive = isExecutiveRoleType(b.roleType) && b.roleLevel !== null && b.roleLevel <= 2;

            if (aIsExecutive && !bIsExecutive) return -1;
            if (!aIsExecutive && bIsExecutive) return 1;

            // Then by hierarchy level (combined role/pay grade level)
            if (a.hierarchyLevel !== null && b.hierarchyLevel !== null) {
                if (a.hierarchyLevel !== b.hierarchyLevel) {
                    return a.hierarchyLevel - b.hierarchyLevel; // Lower = higher authority
                }
            } else if (a.hierarchyLevel !== null) return -1;
            else if (b.hierarchyLevel !== null) return 1;

            // Then by role level (lower = higher authority)
            if (a.roleLevel !== null && b.roleLevel !== null) {
                if (a.roleLevel !== b.roleLevel) {
                    return a.roleLevel - b.roleLevel;
                }
            } else if (a.roleLevel !== null) return -1;
            else if (b.roleLevel !== null) return 1;

            // Then by pay grade level (lower = higher authority, assuming same scale)
            if (a.payGradeLevel !== null && b.payGradeLevel !== null) {
                if (a.payGradeLevel !== b.payGradeLevel) {
                    return a.payGradeLevel - b.payGradeLevel;
                }
            } else if (a.payGradeLevel !== null) return -1;
            else if (b.payGradeLevel !== null) return 1;

            // Then managers before non-managers
            if (a.isManager && !b.isManager) return -1;
            if (!a.isManager && b.isManager) return 1;

            // Finally by name
            return a.name.localeCompare(b.name);
        });

        // Determine level label based on role types in this level
        let levelLabel = `Level ${level}`;
        const executivesInLevel = sortedEmployees.filter(e => isExecutiveRoleType(e.roleType) && e.roleLevel !== null && e.roleLevel <= 2);

        if (level === 0) {
            if (executivesInLevel.length > 0) {
                levelLabel = 'Top Level (Executives/C-Suite)';
            } else {
                levelLabel = 'Top Level (Leaders)';
            }
        } else if (level === sortedLevels[sortedLevels.length - 1]) {
            levelLabel = 'Bottom Level (Entry Level/Interns)';
        } else if (level <= 2 && executivesInLevel.length > 0) {
            levelLabel = `Level ${level} (C-Suite/Executive)`;
        } else if (level <= 4) {
            levelLabel = `Level ${level} (Management)`;
        } else {
            levelLabel = `Level ${level} (Operational)`;
        }

        html += `<div class="level-${level} mb-4 position-relative">`;
        html += `<div class="level-header mb-3 p-2 bg-light rounded border-start border-${level === 0 ? 'primary' : level === sortedLevels[sortedLevels.length - 1] ? 'success' : 'info'} border-3">`;
        html += `<strong class="text-${level === 0 ? 'primary' : level === sortedLevels[sortedLevels.length - 1] ? 'success' : 'dark'}">${levelLabel}</strong>`;
        html += ` <small class="text-muted">(${sortedEmployees.length} ${sortedEmployees.length === 1 ? 'employee' : 'employees'})</small>`;
        html += `</div>`;
        html += '<div class="d-flex flex-wrap gap-3 justify-content-center align-items-start">';

        sortedEmployees.forEach(emp => {
            const directReportsCount = emp.directReports ? emp.directReports.length : 0;

            // Find supervisor names for display
            const supervisorNames = [];
            if (emp.supervisors && emp.supervisors.length > 0) {
                emp.supervisors.forEach(s => {
                    const sup = structure.employees[s.supervisorID];
                    if (sup) supervisorNames.push(sup.name);
                });
            }

            // Determine card styling based on role type
            const isExecutive = isExecutiveRoleType(emp.roleType) && emp.roleLevel !== null && emp.roleLevel <= 2;
            // Get role type display name and color
            let roleTypeDisplay = emp.roleType || '';
            let roleTypeBadgeColor = 'info';
            if (roleTypesData && Array.isArray(roleTypesData)) {
                const roleTypeObj = roleTypesData.find(rt => rt.roleTypeCode === emp.roleType || rt.roleTypeName === emp.roleType);
                if (roleTypeObj) {
                    roleTypeDisplay = roleTypeObj.roleTypeName;
                    if (roleTypeObj.roleTypeCode === 'EXEC' || roleTypeObj.roleTypeName === 'Executive') {
                        roleTypeBadgeColor = 'danger';
                    } else if (roleTypeObj.roleTypeCode === 'MGT' || roleTypeObj.roleTypeName === 'Management') {
                        roleTypeBadgeColor = 'warning';
                    }
                } else if (emp.roleType === 'Executive') {
                    roleTypeBadgeColor = 'danger';
                } else if (emp.roleType === 'Management') {
                    roleTypeBadgeColor = 'warning';
                }
            } else if (emp.roleType === 'Executive') {
                roleTypeBadgeColor = 'danger';
            } else if (emp.roleType === 'Management') {
                roleTypeBadgeColor = 'warning';
            }
            const cardBorderClass = isExecutive ? 'danger' : (emp.isManager ? 'primary' : 'secondary');
            const avatarClass = isExecutive ? 'danger' : (emp.isManager ? 'primary' : 'secondary');
            const iconClass = isExecutive ? 'crown' : (emp.isManager ? 'user-tie' : 'user');
            const roleBadge = emp.roleType ? `<span class="badge bg-${roleTypeBadgeColor}-transparent mb-1"><small>${escapeHtml(roleTypeDisplay)}</small></span>` : '';
            const payGradeBadge = emp.payGrade ? `<span class="badge bg-secondary-transparent mb-1"><small>Grade ${escapeHtml(emp.payGrade.payGradeCode || emp.payGradeLevel || '')}</small></span>` : '';
            const levelDisplay = emp.hierarchyLevel !== null ? emp.hierarchyLevel : (emp.roleLevel !== null ? emp.roleLevel : (emp.payGradeLevel !== null ? emp.payGradeLevel : null));

            html += `
                <div class="employee-card ${emp.isManager ? 'manager' : 'employee'} ${isExecutive ? 'executive' : ''}" data-employee-id="${emp.id}" data-level="${emp.level}">
                    <div class="card border-${cardBorderClass} shadow-sm" style="min-width: 220px; max-width: 250px;">
                        <div class="card-body p-3 text-center">
                            <div class="avatar avatar-md bg-${avatarClass}-transparent mx-auto mb-2">
                                <i class="fas fa-${iconClass}"></i>
                            </div>
                            <h6 class="mb-1 fw-bold">${escapeHtml(emp.name)}</h6>
                            <small class="text-muted d-block mb-1">${escapeHtml(emp.jobTitle)}</small>
                            ${roleBadge}
                            ${payGradeBadge}
                            ${levelDisplay !== null ? `<small class="text-muted d-block mb-1">Level ${levelDisplay}</small>` : ''}
                            ${supervisorNames.length > 0 ? `<small class="text-info d-block mb-1"><i class="fas fa-arrow-up me-1"></i>Reports to: ${supervisorNames.map(name => escapeHtml(name)).join(', ')}</small>` : ''}
                            ${directReportsCount > 0 ? `<span class="badge bg-info-transparent mt-1"><i class="fas fa-users me-1"></i>${directReportsCount} ${directReportsCount === 1 ? 'Report' : 'Reports'}</span>` : '<span class="badge bg-secondary-transparent mt-1">No Reports</span>'}
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div></div>';
    });

    html += '</div>';
    return html;
}

// Render Matrix Chart
function renderMatrixChart(structure) {
    let html = '<div class="org-chart-matrix">';
    html += '<div class="alert alert-warning mb-3"><i class="fas fa-info-circle me-2"></i>Matrix view shows direct and dotted-line relationships.</div>';

    html += '<div class="row">';

    Object.values(structure.employees).forEach(emp => {
        const directReportsList = emp.directReports ? emp.directReports.filter(r =>
            r.type === 'Direct' || !r.type || r.type === ''
        ).map(r => {
            const empObj = structure.employees[r.employeeID];
            return empObj ? empObj : null;
        }).filter(e => e) : [];

        const dottedReportsList = emp.directReports ? emp.directReports.filter(r =>
            r.type === 'Dotted'
        ).map(r => {
            const empObj = structure.employees[r.employeeID];
            return empObj ? empObj : null;
        }).filter(e => e) : [];

        html += `
            <div class="col-md-4 mb-3">
                <div class="card ${emp.isManager ? 'border-primary' : 'border-secondary'}">
                    <div class="card-body p-3">
                        <h6 class="mb-2">${escapeHtml(emp.name)}</h6>
                        <small class="text-muted d-block mb-3">${escapeHtml(emp.jobTitle)}</small>

                        ${directReportsList.length > 0 ? `
                            <div class="mb-2">
                                <strong class="text-primary">Direct Reports (${directReportsList.length}):</strong>
                                <ul class="list-unstyled ms-2 mb-0">
                                    ${directReportsList.map(r => `<li class="text-primary">→ ${escapeHtml(r.name)}</li>`).join('')}
                                </ul>
                            </div>
                        ` : ''}

                        ${dottedReportsList.length > 0 ? `
                            <div>
                                <strong class="text-info">Dotted Reports (${dottedReportsList.length}):</strong>
                                <ul class="list-unstyled ms-2 mb-0">
                                    ${dottedReportsList.map(r => `<li class="text-info">⇢ ${escapeHtml(r.name)}</li>`).join('')}
                                </ul>
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
    });

    html += '</div></div>';
    return html;
}

// Render Flat Chart
function renderFlatChart(structure) {
    let html = '<div class="org-chart-flat">';
    html += '<div class="alert alert-info mb-3"><i class="fas fa-info-circle me-2"></i>Flat view shows all employees organized by hierarchy level.</div>';

    // Group by level
    const byLevel = {};
    Object.values(structure.employees).forEach(emp => {
        if (!byLevel[emp.level]) byLevel[emp.level] = [];
        byLevel[emp.level].push(emp);
    });

    Object.keys(byLevel).sort((a, b) => parseInt(a) - parseInt(b)).forEach(level => {
        html += `<div class="mb-4"><h6>Level ${level}</h6>`;
        html += '<div class="row">';

        byLevel[level].forEach(emp => {
            html += `
                <div class="col-md-3 mb-2">
                    <div class="card border-${emp.isManager ? 'primary' : 'secondary'}">
                        <div class="card-body p-2">
                            <h6 class="mb-1 small">${escapeHtml(emp.name)}</h6>
                            <small class="text-muted">${escapeHtml(emp.jobTitle)}</small>
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div></div>';
    });

    html += '</div>';
    return html;
}

// Render Divisional Chart
function renderDivisionalChart(structure) {
    let html = '<div class="org-chart-divisional">';
    html += '<div class="alert alert-secondary mb-3"><i class="fas fa-info-circle me-2"></i>Divisional view grouped by reporting structure segments.</div>';

    // Group by top-level managers (roots)
    structure.roots.forEach((root, rootIndex) => {
        const divisionEmployees = getAllReports(structure.employees, root.id);
        divisionEmployees.unshift(root);

        html += `
            <div class="division mb-4 p-3 border rounded">
                <h5 class="mb-3">Division ${rootIndex + 1}: ${escapeHtml(root.name)}</h5>
                <div class="row">
        `;

        divisionEmployees.forEach(emp => {
            html += `
                <div class="col-md-4 mb-2">
                    <div class="card ${emp.isManager ? 'border-success' : 'border-light'}">
                        <div class="card-body p-2">
                            <h6 class="mb-1 small">${escapeHtml(emp.name)}</h6>
                            <small class="text-muted">${escapeHtml(emp.jobTitle)}</small>
                            ${emp.level > 0 ? `<small class="text-muted d-block">Level ${emp.level}</small>` : ''}
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div></div>';
    });

    html += '</div>';
    return html;
}

// Get all reports recursively
function getAllReports(employeeMap, managerId, visited = new Set()) {
    if (visited.has(managerId)) return [];
    visited.add(managerId);

    const manager = employeeMap[managerId];
    if (!manager || !manager.directReports) return [];

    // Get direct reports
    const reports = manager.directReports.map(r => {
        return employeeMap[r.employeeID];
    }).filter(e => e);

    let allReports = [...reports];
    reports.forEach(report => {
        allReports = allReports.concat(getAllReports(employeeMap, report.id, visited));
    });

    return allReports;
}

// Update chart statistics
function updateChartStatistics(structure) {
    const stats = structure.stats;

    const totalEl = document.getElementById('totalEmployeesCount');
    const managersEl = document.getElementById('managersCount');
    const levelsEl = document.getElementById('levelsCount');
    const avgSpanEl = document.getElementById('avgSpanControl');

    if (totalEl) totalEl.textContent = stats.totalEmployees;
    if (managersEl) managersEl.textContent = stats.managers;
    if (levelsEl) levelsEl.textContent = stats.levels;
    if (avgSpanEl) avgSpanEl.textContent = stats.avgSpanControl;
}

// Export live chart
function exportLiveChart() {
    const canvas = document.getElementById('liveChartCanvas');
    const viewMode = document.getElementById('chartViewMode')?.value || 'hierarchical';

    if (!canvas) {
        if (typeof showToast === 'function') {
            showToast('No chart to export', 'warning');
        } else {
            alert('No chart to export');
        }
        return;
    }

    // Create print window
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Organizational Chart - ${viewMode.charAt(0).toUpperCase() + viewMode.slice(1)} View</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 20px; }
                    .card { border: 1px solid #ddd; margin: 10px; padding: 10px; display: inline-block; }
                    .manager { border-color: #0d6efd !important; }
                    .employee { border-color: #6c757d !important; }
                </style>
            </head>
            <body>
                <h2>Organizational Chart - ${viewMode.charAt(0).toUpperCase() + viewMode.slice(1)} View</h2>
                ${canvas.innerHTML}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

// HTML escape utility
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize chart on page load
document.addEventListener('DOMContentLoaded', function() {
    if (reportingRelationshipsData && reportingRelationshipsData.length > 0) {
        setTimeout(() => {
            generateLiveChart();
        }, 500);
    }
});

// Role Management Functions

// Toggle between selecting existing job title and adding new one
function toggleNewJobTitleFields() {
    const selectValue = document.getElementById('role_jobTitleSelect').value;
    const selectedOption = document.querySelector('#role_jobTitleSelect option:checked');

    const newJobTitleFields = document.getElementById('newJobTitleFields');
    const newJobDescriptionField = document.getElementById('newJobDescriptionField');
    const selectedRoleNameField = document.getElementById('selectedRoleNameField');

    if (selectValue === 'new') {
        // Show fields for adding new job title
        newJobTitleFields.style.display = 'block';
        newJobDescriptionField.style.display = 'block';
        selectedRoleNameField.style.display = 'none';

        // Clear job title ID
        document.getElementById('role_jobTitleID').value = '';
        document.getElementById('role_roleName').value = '';

        // Make new job title required
        document.getElementById('role_newJobTitle').required = true;
    } else if (selectValue !== '') {
        // Hide new fields, show selected role name
        newJobTitleFields.style.display = 'none';
        newJobDescriptionField.style.display = 'none';
        selectedRoleNameField.style.display = 'block';

        // Set the selected job title
        const jobTitle = selectedOption.getAttribute('data-title');
        document.getElementById('role_roleName').value = jobTitle;
        document.getElementById('role_jobTitleID').value = selectValue;

        // Clear new job title fields
        document.getElementById('role_newJobTitle').value = '';
        document.getElementById('role_newJobDescription').value = '';
        document.getElementById('role_newJobTitle').required = false;
    } else {
        // Nothing selected
        newJobTitleFields.style.display = 'none';
        newJobDescriptionField.style.display = 'none';
        selectedRoleNameField.style.display = 'none';

        document.getElementById('role_roleName').value = '';
        document.getElementById('role_jobTitleID').value = '';
        document.getElementById('role_newJobTitle').required = false;
    }
}

// Update icon preview when icon is selected
function updateIconPreview() {
    const selectElement = document.getElementById('role_iconClassSelect');
    const iconPreview = document.getElementById('iconPreview');
    const customInput = document.getElementById('role_iconClass');
    const selectedValue = selectElement.value;

    if (selectedValue === 'custom') {
        // Show custom input field
        customInput.style.display = 'block';
        customInput.value = '';
        customInput.focus();
        // Keep default icon preview
        iconPreview.innerHTML = '<i class="fas fa-icons"></i>';
    } else if (selectedValue) {
        // Update preview with selected icon
        iconPreview.innerHTML = '<i class="' + selectedValue + '"></i>';
        customInput.style.display = 'none';
        customInput.value = selectedValue;
    } else {
        // No selection - default icon
        iconPreview.innerHTML = '<i class="fas fa-user-tie"></i>';
        customInput.style.display = 'none';
        customInput.value = '';
    }
}

// Update icon preview from custom input field
function updateIconPreviewFromCustom() {
    const customInput = document.getElementById('role_iconClass');
    const iconPreview = document.getElementById('iconPreview');
    const iconClass = customInput.value.trim();

    if (iconClass) {
        iconPreview.innerHTML = '<i class="' + iconClass + '"></i>';
    } else {
        iconPreview.innerHTML = '<i class="fas fa-icons"></i>';
    }
}

// Sync color picker with text field
function syncColorCode() {
    const colorPicker = document.getElementById('role_colorCode');
    const colorText = document.getElementById('role_colorCodeText');
    if (colorPicker && colorText) {
        colorPicker.addEventListener('input', function() {
            colorText.value = this.value;
        });
    }
}

// Sync custom icon input with preview
function syncCustomIconInput() {
    const customInput = document.getElementById('role_iconClass');
    if (customInput) {
        customInput.addEventListener('input', updateIconPreviewFromCustom);
    }
}

function addNewRole() {
    const modal = document.querySelector('#manageRoleModal');
    if (modal) {
        // Reset form
        modal.querySelector('form')?.reset();

        // Set defaults
        document.getElementById('role_isActive').checked = true;
        document.getElementById('role_canApprove').checked = false;
        document.getElementById('role_requiresApproval').checked = false;
        // Set default role level (levelNumber 5, but use roleLevelID)
        const roleLevelSelect = document.getElementById('role_roleLevel');
        const defaultLevelOption = Array.from(roleLevelSelect.options).find(opt =>
            opt.getAttribute('data-number') === '5'
        );
        if (defaultLevelOption) {
            roleLevelSelect.value = defaultLevelOption.value;
        }

        // Set default role type (Operational, but use roleTypeID)
        const roleTypeSelect = document.getElementById('role_roleType');
        const defaultTypeOption = Array.from(roleTypeSelect.options).find(opt =>
            opt.getAttribute('data-code') === 'OPR' || opt.textContent.includes('Operational')
        );
        if (defaultTypeOption) {
            roleTypeSelect.value = defaultTypeOption.value;
        }
        document.getElementById('role_reportsCount').value = '0';
        document.getElementById('role_colorCode').value = '#0d6efd';
        document.getElementById('role_colorCodeText').value = '#0d6efd';

        // Reset icon selector
        document.getElementById('role_iconClassSelect').value = '';
        document.getElementById('role_iconClass').value = '';
        document.getElementById('role_iconClass').style.display = 'none';
        document.getElementById('iconPreview').innerHTML = '<i class="fas fa-user-tie"></i>';

        // Reset job title selection
        document.getElementById('role_jobTitleSelect').value = '';
        document.getElementById('role_jobTitleID').value = '';

        // Hide all conditional fields
        document.getElementById('newJobTitleFields').style.display = 'none';
        document.getElementById('newJobDescriptionField').style.display = 'none';
        document.getElementById('selectedRoleNameField').style.display = 'none';

        // Update modal title
        document.getElementById('roleModalTitle').textContent = 'Add New Role';

        // Clear roleID (for new role)
        document.getElementById('roleID').value = '';

        // Initialize color sync
        syncColorCode();
    }
}

function editRole(roleID) {
    const modal = document.querySelector('#manageRoleModal');

    if (!modal) {
        console.error('Role modal not found');
        return;
    }

    // Update modal title
    document.getElementById('roleModalTitle').textContent = 'Edit Role';

    // Fetch role data
    const url = '<?= $base ?>php/scripts/global/admin/get_role.php?roleID=' + roleID;
    console.log('Fetching role data from:', url);

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Role data:', data);

            if (data.success && data.role) {
                const role = data.role;

                // Populate basic fields
                document.getElementById('roleID').value = role.roleID || '';
                document.getElementById('role_roleCode').value = role.roleCode || '';
                // Set roleType - use roleTypeID if available, otherwise try roleTypeCode or roleTypeName
                const roleTypeSelect = document.getElementById('role_roleType');
                if (role.roleTypeID) {
                    roleTypeSelect.value = role.roleTypeID;
                } else if (role.roleType) {
                    // Try to find by code or name (backward compatibility)
                    const options = roleTypeSelect.options;
                    for (let i = 0; i < options.length; i++) {
                        if (options[i].value === role.roleType ||
                            options[i].getAttribute('data-code') === role.roleType ||
                            options[i].getAttribute('data-name') === role.roleType) {
                            roleTypeSelect.value = options[i].value;
                            break;
                        }
                    }
                }

                // Set roleLevel - use roleLevelID if available, otherwise try levelNumber
                const roleLevelSelect = document.getElementById('role_roleLevel');
                if (role.roleLevelID) {
                    roleLevelSelect.value = role.roleLevelID;
                } else if (role.levelNumber !== undefined && role.levelNumber !== null) {
                    // Find option by data-number attribute
                    const options = roleLevelSelect.options;
                    for (let i = 0; i < options.length; i++) {
                        if (options[i].getAttribute('data-number') === role.levelNumber.toString()) {
                            roleLevelSelect.value = options[i].value;
                            break;
                        }
                    }
                } else if (role.roleLevel) {
                    // Try to find by levelNumber (backward compatibility)
                    const options = roleLevelSelect.options;
                    for (let i = 0; i < options.length; i++) {
                        if (options[i].value === role.roleLevel.toString() ||
                            options[i].getAttribute('data-number') === role.roleLevel.toString()) {
                            roleLevelSelect.value = options[i].value;
                            break;
                        }
                    }
                }
                document.getElementById('role_roleDescription').value = role.roleDescription || '';

                // Populate organizational structure fields
                document.getElementById('role_departmentID').value = role.departmentID || '';
                document.getElementById('role_unitID').value = role.unitID || '';
                document.getElementById('role_parentRoleID').value = role.parentRoleID || '';
                document.getElementById('role_reportsCount').value = role.reportsCount || '0';

                // Populate approval fields
                document.getElementById('role_requiresApproval').checked = (role.requiresApproval === 'Y');
                document.getElementById('role_canApprove').checked = (role.canApprove === 'Y');
                document.getElementById('role_approvalLimit').value = role.approvalLimit || '';

                // Populate visual customization fields
                const iconClass = role.iconClass || '';

                // Try to find matching icon in dropdown
                const iconSelect = document.getElementById('role_iconClassSelect');
                let iconFound = false;

                for (let option of iconSelect.options) {
                    if (option.value === iconClass && option.value !== 'custom') {
                        iconSelect.value = iconClass;
                        iconFound = true;
                        break;
                    }
                }

                // If icon not found in dropdown or is custom, show custom input
                if (!iconFound && iconClass) {
                    iconSelect.value = 'custom';
                    document.getElementById('role_iconClass').value = iconClass;
                    document.getElementById('role_iconClass').style.display = 'block';
                    document.getElementById('iconPreview').innerHTML = '<i class="' + iconClass + '"></i>';
                } else if (iconFound) {
                    document.getElementById('role_iconClass').value = iconClass;
                    document.getElementById('role_iconClass').style.display = 'none';
                    document.getElementById('iconPreview').innerHTML = '<i class="' + iconClass + '"></i>';
                } else {
                    // No icon set
                    iconSelect.value = '';
                    document.getElementById('role_iconClass').value = '';
                    document.getElementById('role_iconClass').style.display = 'none';
                    document.getElementById('iconPreview').innerHTML = '<i class="fas fa-user-tie"></i>';
                }

                const colorCode = role.colorCode || '#0d6efd';
                document.getElementById('role_colorCode').value = colorCode;
                document.getElementById('role_colorCodeText').value = colorCode;

                // Populate status
                document.getElementById('role_isActive').checked = (role.isActive === 'Y');

                // Handle job title selection
                const roleName = role.roleName || '';
                document.getElementById('role_roleName').value = roleName;

                // Try to find matching job title in dropdown
                const jobTitleSelect = document.getElementById('role_jobTitleSelect');
                let foundMatch = false;

                for (let option of jobTitleSelect.options) {
                    if (option.getAttribute('data-title') === roleName && option.value !== 'new') {
                        jobTitleSelect.value = option.value;
                        document.getElementById('role_jobTitleID').value = option.value;
                        foundMatch = true;
                        break;
                    }
                }

                // If no match found, show as custom entry
                if (!foundMatch && roleName) {
                    // Don't select anything in dropdown
                    jobTitleSelect.value = '';
                    document.getElementById('selectedRoleNameField').style.display = 'block';
                } else if (foundMatch) {
                    document.getElementById('selectedRoleNameField').style.display = 'block';
                }

                // Hide new job title fields when editing
                document.getElementById('newJobTitleFields').style.display = 'none';
                document.getElementById('newJobDescriptionField').style.display = 'none';

                // Set role scope based on entityID
                if (role.entityID && role.entityID != '' && role.entityID != null) {
                    document.getElementById('roleScope_entity').checked = true;
                } else {
                    document.getElementById('roleScope_org').checked = true;
                }

            } else {
                if (typeof showToast === 'function') {
                    showToast('Error: ' + (data.message || 'Failed to load role data'), 'error');
                } else {
                    alert('Error: ' + (data.message || 'Failed to load role data'));
                }
            }
        })
        .catch(error => {
            console.error('Error loading role:', error);
            if (typeof showToast === 'function') {
                showToast('Error loading role data: ' + error.message, 'error');
            } else {
                alert('Error loading role data: ' + error.message);
            }
        });
}

// Add event listeners for edit role buttons
document.addEventListener('DOMContentLoaded', function() {
    // Attach listeners to edit role buttons
    document.querySelectorAll('.editRole').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const roleID = this.getAttribute('data-role-id');
            if (roleID) {
                editRole(roleID);
            }
        });
    });

    // Initialize color picker sync
    syncColorCode();

    // Initialize custom icon input sync
    syncCustomIconInput();
});
</script>
<?php
// var_dump($entityEmployees);
?>
<!-- Manage Reporting Relationship Modal -->
<div class="modal fade" id="manageReportingRelationshipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= $base ?>php/scripts/global/admin/manage_reporting_relationship.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="reportingRelationshipModalTitle">
                        <i class="fas fa-project-diagram me-2"></i>Manage Reporting Relationship
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="relationshipID" name="relationshipID">
                    <input type="hidden" id="rel_orgDataID" name="orgDataID" value="<?= isset($entity->orgDataID) ? $entity->orgDataID : '' ?>">
                    <input type="hidden" id="rel_entityID" name="entityID" value="<?= $entityID ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="rel_employeeID" class="form-label">Employee <span class="text-danger">*</span></label>
                            <select id="rel_employeeID" name="employeeID" class="form-select form-control-sm" required>
                                <option value="">Select Employee</option>
                                <?php if ($entityEmployees): foreach ($entityEmployees as $emp): ?>
                                    <option value="<?= $emp->ID ?>">
                                        <?= htmlspecialchars($emp->employeeNameWithInitials ?? $emp->employeeNameWithInitials ?? 'N/A') ?>
                                        <?php if (isset($emp->jobTitle)): ?>
                                            - <small><?= htmlspecialchars($emp->jobTitle) ?></small>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                            <small class="text-muted">Employee who will report</small>
                        </div>

                        <div class="col-md-6">
                            <label for="rel_supervisorID" class="form-label">Supervisor <span class="text-danger">*</span></label>
                            <select id="rel_supervisorID" name="supervisorID" class="form-select form-control-sm" required>
                                <option value="0">No Supervisor (Reports to Board/External)</option>
                                <option value="" disabled>──────────────────</option>
                                <?php if ($entityEmployees): foreach ($entityEmployees as $emp): ?>
                                    <option value="<?= $emp->ID ?>">
                                        <?= htmlspecialchars($emp->employeeNameWithInitials ?? $emp->employeeNameWithInitials ?? 'N/A') ?>
                                        <?php if (isset($emp->jobTitle)): ?>
                                            - <small><?= htmlspecialchars($emp->jobTitle) ?></small>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                            <small class="text-muted">Employee they report to</small>
                        </div>

                        <div class="col-md-6">
                            <label for="rel_relationshipType" class="form-label">Relationship Type <span class="text-danger">*</span></label>
                            <select id="rel_relationshipType" name="relationshipType" class="form-select form-control-sm" required>
                                <option value="Direct">Direct - Traditional reporting line</option>
                                <option value="Dotted">Dotted - Secondary reporting line</option>
                                <option value="Matrix">Matrix - Dual reporting</option>
                                <option value="Functional">Functional - Skill-based reporting</option>
                                <option value="Administrative">Administrative - Process-based</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="rel_relationshipStrength" class="form-label">Relationship Strength</label>
                            <select id="rel_relationshipStrength" name="relationshipStrength" class="form-select form-control-sm">
                                <option value="100">100% - Primary reporting line</option>
                                <option value="75">75% - Strong secondary line</option>
                                <option value="50">50% - Equal dual reporting</option>
                                <option value="25">25% - Weak/consultative line</option>
                            </select>
                            <small class="text-muted">100% = Primary supervisor</small>
                        </div>

                        <div class="col-md-6">
                            <label for="rel_effectiveDate" class="form-label">Effective Date <span class="text-danger">*</span></label>
                            <input type="text" id="rel_effectiveDate" name="effectiveDate"
                                class="form-control form-control-sm" placeholder="Select date" required readonly>
                        </div>

                        <div class="col-md-6">
                            <label for="rel_endDate" class="form-label">End Date</label>
                            <input type="text" id="rel_endDate" name="endDate"
                                class="form-control form-control-sm" placeholder="Select end date (optional)" readonly>
                            <small class="text-muted">Leave empty for ongoing</small>
                        </div>

                        <div class="col-md-6">
                            <label for="rel_reportingFrequency" class="form-label">Reporting Frequency</label>
                            <select id="rel_reportingFrequency" name="reportingFrequency" class="form-select form-control-sm">
                                <option value="Daily">Daily</option>
                                <option value="Weekly" selected>Weekly</option>
                                <option value="Biweekly">Bi-weekly</option>
                                <option value="Monthly">Monthly</option>
                                <option value="Quarterly">Quarterly</option>
                                <option value="Adhoc">Ad-hoc</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Permissions</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="rel_canDelegate"
                                        name="canDelegate" value="Y">
                                    <label class="form-check-label" for="rel_canDelegate">Can Delegate</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="rel_canSubstitute"
                                        name="canSubstitute" value="Y">
                                    <label class="form-check-label" for="rel_canSubstitute">Can Substitute</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label for="rel_notes" class="form-label">Notes</label>
                            <textarea id="rel_notes" name="notes" class="form-control form-control-sm"
                                rows="2" placeholder="Additional notes about this reporting relationship"></textarea>
                        </div>

                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="rel_isCurrent"
                                    name="isCurrent" value="Y" checked>
                                <label class="form-check-label" for="rel_isCurrent">
                                    <strong>Current Relationship</strong>
                                    <small class="text-muted d-block">Uncheck to mark as historical</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveReportingRelationshipBtn">
                        <i class="fas fa-save me-2"></i>Save Relationship
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Manage Role Modal -->
<div class="modal fade" id="manageRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= $base ?>php/scripts/global/admin/manage_role.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="roleModalTitle">
                        <i class="fas fa-user-tie me-2"></i>Manage Organizational Role
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="roleID" name="roleID">
                    <input type="hidden" id="role_orgDataID" name="orgDataID" value="<?= isset($entity->orgDataID) ? $entity->orgDataID : '' ?>">
                    <input type="hidden" id="role_entityID" name="entityID" value="<?= $entityID ?>">

                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> Organizational roles define positions in your hierarchy and their authority levels.
                        Roles can be specific to this entity or shared across the organization.
                    </div>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="role_jobTitleSelect" class="form-label">Select Job Title/Role <span class="text-danger">*</span></label>
                            <select id="role_jobTitleSelect" class="form-select form-control-sm" onchange="toggleNewJobTitleFields()">
                                <option value="">-- Select Existing Job Title --</option>
                                <?php if ($jobTitles): foreach ($jobTitles as $jobTitle): ?>
                                    <option value="<?= $jobTitle->jobTitleID ?>"
                                        data-title="<?= htmlspecialchars($jobTitle->jobTitle) ?>"
                                        data-description="<?= htmlspecialchars($jobTitle->jobDescription ?? '') ?>">
                                        <?= htmlspecialchars($jobTitle->jobTitle) ?>
                                    </option>
                                <?php endforeach; endif; ?>
                                <option value="new" style="background-color: #e7f3ff; font-weight: bold;">
                                    ➕ Add New Job Title
                                </option>
                            </select>
                            <small class="text-muted">Select from existing job titles or add a new one</small>
                        </div>

                        <!-- Hidden field for existing job title ID -->
                        <input type="hidden" id="role_jobTitleID" name="jobTitleID">

                        <!-- New Job Title Fields (shown when "Add New" is selected) -->
                        <div class="col-md-6" id="newJobTitleFields" style="display: none;">
                            <label for="role_newJobTitle" class="form-label">New Job Title <span class="text-danger">*</span></label>
                            <input type="text" id="role_newJobTitle" name="newJobTitle"
                                class="form-control form-control-sm"
                                placeholder="e.g., Senior Manager, Director">
                            <small class="text-muted">Enter the new job title name</small>
                        </div>

                        <div class="col-md-6" id="newJobDescriptionField" style="display: none;">
                            <label for="role_newJobDescription" class="form-label">Job Description</label>
                            <textarea id="role_newJobDescription" name="newJobDescription"
                                class="form-control form-control-sm" rows="1"
                                placeholder="Brief description of this job title"></textarea>
                            <small class="text-muted">Optional description for the new job title</small>
                        </div>

                        <!-- Display selected role name (read-only) -->
                        <div class="col-md-6" id="selectedRoleNameField" style="display: none;">
                            <label for="role_roleName" class="form-label">Role Name</label>
                            <input type="text" id="role_roleName" name="roleName"
                                class="form-control form-control-sm" readonly>
                            <small class="text-muted">Selected job title</small>
                        </div>

                        <div class="col-md-6">
                            <label for="role_roleCode" class="form-label">Role Code</label>
                            <input type="text" id="role_roleCode" name="roleCode"
                                class="form-control form-control-sm"
                                placeholder="e.g., SM, DIR" maxlength="10">
                            <small class="text-muted">Short code for the role (optional)</small>
                        </div>

                        <div class="col-md-6">
                            <label for="role_roleType" class="form-label">Role Type <span class="text-danger">*</span></label>
                            <select id="role_roleType" name="roleType" class="form-select form-control-sm" required>
                                <option value="">Select Role Type...</option>
                                <?php if ($roleTypes): ?>
                                    <?php foreach ($roleTypes as $roleType): ?>
                                        <option value="<?= htmlspecialchars($roleType->roleTypeID) ?>"
                                            data-code="<?= htmlspecialchars($roleType->roleTypeCode) ?>"
                                            data-name="<?= htmlspecialchars($roleType->roleTypeName) ?>">
                                            <?= htmlspecialchars($roleType->roleTypeName) ?>
                                            <?php if ($roleType->roleTypeDescription): ?>
                                                - <?= htmlspecialchars($roleType->roleTypeDescription) ?>
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <!-- Fallback to hardcoded values if no role types in database -->
                                    <option value="Executive">Executive - C-Level, Top Leadership</option>
                                    <option value="Management">Management - Directors, Managers</option>
                                    <option value="Supervisory">Supervisory - Team Leads, Supervisors</option>
                                    <option value="Operational">Operational - Officers, Staff</option>
                                    <option value="Support">Support - Administrative, Assistants</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="role_roleLevel" class="form-label">Role Level <span class="text-danger">*</span></label>
                            <select id="role_roleLevel" name="roleLevel" class="form-select form-control-sm" required>
                                <option value="">Select Role Level...</option>
                                <?php if ($roleLevels): ?>
                                    <?php foreach ($roleLevels as $roleLevel): ?>
                                        <option value="<?= htmlspecialchars($roleLevel->roleLevelID) ?>"
                                            <?= $roleLevel->levelNumber == 5 ? 'selected' : '' ?>
                                            data-number="<?= htmlspecialchars($roleLevel->levelNumber) ?>"
                                            data-name="<?= htmlspecialchars($roleLevel->levelName) ?>">
                                            Level <?= htmlspecialchars($roleLevel->levelNumber) ?> - <?= htmlspecialchars($roleLevel->levelName) ?>
                                            <?php if ($roleLevel->levelDescription): ?>
                                                (<?= htmlspecialchars(substr($roleLevel->levelDescription, 0, 50)) ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <!-- Fallback to hardcoded values if no role levels in database -->
                                    <option value="0">Level 0 - Board/External</option>
                                    <option value="1">Level 1 - CEO/Executive</option>
                                    <option value="2">Level 2 - C-Suite</option>
                                    <option value="3">Level 3 - Director</option>
                                    <option value="4">Level 4 - Manager</option>
                                    <option value="5" selected>Level 5 - Supervisor</option>
                                    <option value="6">Level 6 - Senior Staff</option>
                                    <option value="7">Level 7 - Staff</option>
                                    <option value="8">Level 8 - Entry Level</option>
                                <?php endif; ?>
                            </select>
                            <small class="text-muted">Lower numbers = higher authority</small>
                        </div>

                        <div class="col-md-12">
                            <label for="role_roleDescription" class="form-label">Role Description</label>
                            <textarea id="role_roleDescription" name="roleDescription"
                                class="form-control form-control-sm" rows="2"
                                placeholder="Brief description of this role's responsibilities and scope"></textarea>
                        </div>

                        <div class="col-md-12">
                            <hr>
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-sitemap me-2"></i>Organizational Structure
                            </h6>
                        </div>

                        <div class="col-md-6">
                            <label for="role_departmentID" class="form-label">Department</label>
                            <select id="role_departmentID" name="departmentID" class="form-select form-control-sm">
                                <option value="">-- Select Department (Optional) --</option>
                                <?php if ($entityDepartments): foreach ($entityDepartments as $dept): ?>
                                    <option value="<?= $dept->departmentID ?>">
                                        <?= htmlspecialchars($dept->departmentName) ?>
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                            <small class="text-muted">Department this role belongs to</small>
                        </div>

                        <div class="col-md-6">
                            <label for="role_unitID" class="form-label">Unit/Division</label>
                            <select id="role_unitID" name="unitID" class="form-select form-control-sm">
                                <option value="">-- Select Unit (Optional) --</option>
                                <?php if ($entityUnits): foreach ($entityUnits as $unit): ?>
                                    <option value="<?= $unit->unitID ?>">
                                        <?= htmlspecialchars($unit->unitName) ?>
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                            <small class="text-muted">Unit/Division this role belongs to</small>
                        </div>

                        <div class="col-md-6">
                            <label for="role_parentRoleID" class="form-label">Parent Role</label>
                            <select id="role_parentRoleID" name="parentRoleID" class="form-select form-control-sm">
                                <option value="">-- No Parent Role --</option>
                                <?php if ($existingRoles): foreach ($existingRoles as $existingRole): ?>
                                    <option value="<?= $existingRole->roleID ?>">
                                        <?= htmlspecialchars($existingRole->roleName) ?> (Level <?= $existingRole->roleLevel ?>)
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                            <small class="text-muted">Superior role in hierarchy</small>
                        </div>

                        <div class="col-md-6">
                            <label for="role_reportsCount" class="form-label">Expected Reports Count</label>
                            <input type="number" id="role_reportsCount" name="reportsCount"
                                class="form-control form-control-sm"
                                placeholder="0" min="0" value="0">
                            <small class="text-muted">Expected number of direct reports</small>
                        </div>

                        <div class="col-md-12">
                            <hr>
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-check-circle me-2"></i>Approval Authority
                            </h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Approval Settings</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="role_requiresApproval"
                                    name="requiresApproval" value="Y">
                                <label class="form-check-label" for="role_requiresApproval">
                                    <strong>Requires Approval</strong>
                                    <small class="text-muted d-block">Role assignments need approval</small>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="role_canApprove"
                                    name="canApprove" value="Y">
                                <label class="form-check-label" for="role_canApprove">
                                    <strong>Can Approve Requests</strong>
                                    <small class="text-muted d-block">Can approve leave, expenses, etc.</small>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="role_approvalLimit" class="form-label">Approval Limit (Currency)</label>
                            <input type="number" id="role_approvalLimit" name="approvalLimit"
                                class="form-control form-control-sm"
                                placeholder="0.00" step="0.01" min="0">
                            <small class="text-muted">Maximum amount this role can approve</small>
                        </div>

                        <div class="col-md-12">
                            <hr>
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-palette me-2"></i>Visual Customization
                            </h6>
                        </div>

                        <div class="col-md-6">
                            <label for="role_iconClass" class="form-label">Icon Class</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text" id="iconPreview"><i class="fas fa-user-tie"></i></span>
                                <select id="role_iconClassSelect" class="form-select form-control-sm" onchange="updateIconPreview()">
                                    <option value="">-- Select Icon --</option>
                                    <optgroup label="Leadership & Management">
                                        <option value="fas fa-user-tie">👔 User Tie (Manager/Professional)</option>
                                        <option value="fas fa-crown">👑 Crown (Executive/CEO)</option>
                                        <option value="fas fa-chess-king">♔ Chess King (Top Executive)</option>
                                        <option value="fas fa-user-shield">🛡️ User Shield (Director/Supervisor)</option>
                                        <option value="fas fa-star">⭐ Star (Leadership)</option>
                                        <option value="fas fa-medal">🏅 Medal (Senior Role)</option>
                                    </optgroup>
                                    <optgroup label="Team & Collaboration">
                                        <option value="fas fa-users">👥 Users (Team/Group)</option>
                                        <option value="fas fa-user-friends">🤝 User Friends (Collaboration)</option>
                                        <option value="fas fa-people-arrows">↔️ People Arrows (Coordination)</option>
                                        <option value="fas fa-handshake">🤝 Handshake (Partnership)</option>
                                        <option value="fas fa-user-cog">⚙️ User Cog (Technical Manager)</option>
                                    </optgroup>
                                    <optgroup label="Professional & Technical">
                                        <option value="fas fa-user">👤 User (General)</option>
                                        <option value="fas fa-user-graduate">🎓 User Graduate (Professional)</option>
                                        <option value="fas fa-user-md">🩺 User MD (Medical/Specialist)</option>
                                        <option value="fas fa-user-nurse">👨‍⚕️ User Nurse (Healthcare)</option>
                                        <option value="fas fa-briefcase">💼 Briefcase (Business)</option>
                                        <option value="fas fa-laptop">💻 Laptop (IT/Tech)</option>
                                        <option value="fas fa-code">💻 Code (Developer)</option>
                                        <option value="fas fa-palette">🎨 Palette (Creative)</option>
                                        <option value="fas fa-pencil-ruler">📐 Pencil Ruler (Designer)</option>
                                    </optgroup>
                                    <optgroup label="Operations & Support">
                                        <option value="fas fa-tasks">☑️ Tasks (Operations)</option>
                                        <option value="fas fa-clipboard-list">📋 Clipboard List (Admin)</option>
                                        <option value="fas fa-headset">🎧 Headset (Support)</option>
                                        <option value="fas fa-phone">📞 Phone (Customer Service)</option>
                                        <option value="fas fa-tools">🔧 Tools (Maintenance)</option>
                                        <option value="fas fa-cogs">⚙️ Cogs (Engineering)</option>
                                    </optgroup>
                                    <optgroup label="Financial & Legal">
                                        <option value="fas fa-dollar-sign">💲 Dollar Sign (Finance)</option>
                                        <option value="fas fa-calculator">🧮 Calculator (Accounting)</option>
                                        <option value="fas fa-balance-scale">⚖️ Balance Scale (Legal)</option>
                                        <option value="fas fa-gavel">🔨 Gavel (Compliance)</option>
                                        <option value="fas fa-file-invoice-dollar">💰 Invoice Dollar (Billing)</option>
                                    </optgroup>
                                    <optgroup label="Marketing & Sales">
                                        <option value="fas fa-bullhorn">📢 Bullhorn (Marketing)</option>
                                        <option value="fas fa-chart-line">📈 Chart Line (Sales)</option>
                                        <option value="fas fa-rocket">🚀 Rocket (Growth)</option>
                                        <option value="fas fa-lightbulb">💡 Lightbulb (Innovation)</option>
                                        <option value="fas fa-bullseye">🎯 Bullseye (Strategy)</option>
                                    </optgroup>
                                    <optgroup label="HR & Administration">
                                        <option value="fas fa-user-clock">⏰ User Clock (HR)</option>
                                        <option value="fas fa-id-card">🆔 ID Card (Administration)</option>
                                        <option value="fas fa-clipboard-check">✅ Clipboard Check (Compliance)</option>
                                        <option value="fas fa-calendar-alt">📅 Calendar (Scheduling)</option>
                                    </optgroup>
                                    <optgroup label="Specialized">
                                        <option value="fas fa-microscope">🔬 Microscope (Research)</option>
                                        <option value="fas fa-flask">⚗️ Flask (Laboratory)</option>
                                        <option value="fas fa-book">📖 Book (Education)</option>
                                        <option value="fas fa-chalkboard-teacher">👨‍🏫 Chalkboard Teacher (Training)</option>
                                        <option value="fas fa-shield-alt">🛡️ Shield (Security)</option>
                                        <option value="fas fa-lock">🔒 Lock (Security Officer)</option>
                                    </optgroup>
                                    <optgroup label="Custom">
                                        <option value="custom">✏️ Custom Icon Class...</option>
                                    </optgroup>
                                </select>
                            </div>
                            <input type="text" id="role_iconClass" name="iconClass"
                                class="form-control form-control-sm mt-2"
                                placeholder="Or enter custom icon class (e.g., fas fa-star)"
                                style="display: none;">
                            <small class="text-muted">Select an icon or enter custom FontAwesome class</small>
                        </div>

                        <div class="col-md-6">
                            <label for="role_colorCode" class="form-label">Color Code</label>
                            <div class="input-group input-group-sm">
                                <input type="color" id="role_colorCode" name="colorCode"
                                    class="form-control form-control-color"
                                    value="#0d6efd" title="Choose role color">
                                <input type="text" id="role_colorCodeText"
                                    class="form-control form-control-sm"
                                    placeholder="#0d6efd" readonly>
                            </div>
                            <small class="text-muted">Color for role badges and visual elements</small>
                        </div>

                        <div class="col-md-12">
                            <hr>
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-cog me-2"></i>Role Assignment Scope
                            </h6>
                            <p class="text-muted small mb-3">
                                Choose whether this role is specific to this entity only, or available organization-wide.
                            </p>
                        </div>

                        <div class="col-md-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="roleScope" id="roleScope_entity"
                                    value="entity" checked>
                                <label class="form-check-label" for="roleScope_entity">
                                    <i class="fas fa-building me-1"></i><strong>Entity-Specific</strong>
                                    <small class="text-muted d-block">Only for this entity</small>
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="roleScope" id="roleScope_org"
                                    value="organization">
                                <label class="form-check-label" for="roleScope_org">
                                    <i class="fas fa-globe me-1"></i><strong>Organization-Wide</strong>
                                    <small class="text-muted d-block">Available to all entities</small>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="role_isActive"
                                    name="isActive" value="Y" checked>
                                <label class="form-check-label" for="role_isActive">
                                    <strong>Active Role</strong>
                                    <small class="text-muted d-block">Uncheck to deactivate this role</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-2"></i>Save Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Help & Documentation Modal -->
<div class="modal fade" id="reportingHelpModal" tabindex="-1" aria-labelledby="reportingHelpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="reportingHelpModalLabel">
                    <i class="fas fa-book-open me-2"></i>Reporting Structure - User Guide & Documentation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Navigation Tabs -->
                <ul class="nav nav-tabs nav-tabs-header mb-4" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#helpOverview" role="tab">
                            <i class="fas fa-home me-2"></i>Overview
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#helpManager" role="tab">
                            <i class="fas fa-user-tie me-2"></i>For Managers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#helpEmployee" role="tab">
                            <i class="fas fa-user me-2"></i>For Employees
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#helpFeatures" role="tab">
                            <i class="fas fa-star me-2"></i>Features
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#helpMigration" role="tab">
                            <i class="fas fa-sync me-2"></i>Migration
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#helpFAQ" role="tab">
                            <i class="fas fa-question-circle me-2"></i>FAQ
                        </a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Overview Tab -->
                    <div class="tab-pane show active" id="helpOverview" role="tabpanel">
                        <h5 class="mb-3 text-primary"><i class="fas fa-info-circle me-2"></i>What is the Reporting Structure Module?</h5>
                        <p class="mb-3">
                            The Reporting Structure module helps you define and manage how employees report to their supervisors
                            within your organization. It provides a clear view of the organizational hierarchy and reporting lines.
                        </p>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-primary mb-3">
                                    <div class="card-header bg-primary-transparent">
                                        <h6 class="mb-0"><i class="fas fa-users me-2"></i>Key Components</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="mb-0">
                                            <li><strong>Reporting Relationships:</strong> Who reports to whom</li>
                                            <li><strong>Organizational Roles:</strong> Positions in the hierarchy</li>
                                            <li><strong>Delegations:</strong> Temporary authority transfers</li>
                                            <li><strong>Org Charts:</strong> Visual structure diagrams</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-success mb-3">
                                    <div class="card-header bg-success-transparent">
                                        <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Benefits</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="mb-0">
                                            <li>Clear organizational structure</li>
                                            <li>Easy to find who reports to whom</li>
                                            <li>Support for complex reporting (matrix)</li>
                                            <li>Historical tracking of changes</li>
                                            <li>Delegation management</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-lightbulb me-2"></i>
                            <strong>Quick Tip:</strong> Use the tabs above to find specific guidance for managers or employees,
                            learn about features, or understand the migration process.
                        </div>
                    </div>

                    <!-- For Managers Tab -->
                    <div class="tab-pane" id="helpManager" role="tabpanel">
                        <h5 class="mb-3 text-primary"><i class="fas fa-user-tie me-2"></i>Manager's Guide</h5>

                        <div class="accordion" id="managerAccordion">
                            <!-- Adding Reporting Lines -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#managerAdd">
                                        <i class="fas fa-plus-circle me-2 text-primary"></i>
                                        <strong>How to Add Reporting Lines</strong>
                                    </button>
                                </h2>
                                <div id="managerAdd" class="accordion-collapse collapse show" data-bs-parent="#managerAccordion">
                                    <div class="accordion-body">
                                        <ol class="mb-3">
                                            <li>Click the <strong>"Add Reporting Line"</strong> button in the quick actions bar</li>
                                            <li>Select the <strong>employee</strong> who will report</li>
                                            <li>Select the <strong>supervisor</strong> they will report to</li>
                                            <li>Choose the <strong>relationship type:</strong>
                                                <ul>
                                                    <li><span class="badge bg-primary">Direct</span> - Traditional reporting line (most common)</li>
                                                    <li><span class="badge bg-info">Dotted</span> - Secondary reporting line</li>
                                                    <li><span class="badge bg-warning">Matrix</span> - Dual reporting (e.g., functional + project)</li>
                                                    <li><span class="badge bg-success">Functional</span> - Skill-based reporting</li>
                                                    <li><span class="badge bg-secondary">Administrative</span> - Process-based reporting</li>
                                                </ul>
                                            </li>
                                            <li>Set the <strong>effective date</strong> (when the relationship starts)</li>
                                            <li>Choose <strong>reporting frequency</strong> (Daily, Weekly, Monthly, etc.)</li>
                                            <li>Click <strong>"Save"</strong></li>
                                        </ol>
                                        <div class="alert alert-success">
                                            <i class="fas fa-check-circle me-2"></i>
                                            The relationship will appear immediately in the reporting relationships table.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Managing Roles -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#managerRoles">
                                        <i class="fas fa-user-tie me-2 text-success"></i>
                                        <strong>Understanding Organizational Roles</strong>
                                    </button>
                                </h2>
                                <div id="managerRoles" class="accordion-collapse collapse" data-bs-parent="#managerAccordion">
                                    <div class="accordion-body">
                                        <p class="mb-3">Organizational roles define positions within your company hierarchy:</p>

                                        <table class="table table-sm table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Level</th>
                                                    <th>Role Type</th>
                                                    <th>Examples</th>
                                                    <th>Can Approve</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>0-1</td>
                                                    <td><span class="badge bg-danger">Executive</span></td>
                                                    <td>CEO, CFO, COO</td>
                                                    <td><i class="fas fa-check text-success"></i></td>
                                                </tr>
                                                <tr>
                                                    <td>2-3</td>
                                                    <td><span class="badge bg-warning">Management</span></td>
                                                    <td>Director, Manager</td>
                                                    <td><i class="fas fa-check text-success"></i></td>
                                                </tr>
                                                <tr>
                                                    <td>4-5</td>
                                                    <td><span class="badge bg-info">Supervisory</span></td>
                                                    <td>Supervisor, Team Lead</td>
                                                    <td>Varies</td>
                                                </tr>
                                                <tr>
                                                    <td>6-8</td>
                                                    <td><span class="badge bg-secondary">Operational</span></td>
                                                    <td>Officers, Staff</td>
                                                    <td><i class="fas fa-times text-danger"></i></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <div class="alert alert-info mt-3">
                                            <i class="fas fa-lightbulb me-2"></i>
                                            <strong>Tip:</strong> Roles help define approval authorities and reporting hierarchies.
                                            Lower level numbers indicate higher authority.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Delegations -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#managerDelegate">
                                        <i class="fas fa-hand-holding me-2 text-info"></i>
                                        <strong>Using Delegations</strong>
                                    </button>
                                </h2>
                                <div id="managerDelegate" class="accordion-collapse collapse" data-bs-parent="#managerAccordion">
                                    <div class="accordion-body">
                                        <p class="mb-3">Delegations allow temporary transfer of authority (e.g., when on leave):</p>

                                        <h6 class="text-primary">When to Use Delegations:</h6>
                                        <ul class="mb-3">
                                            <li>Manager going on vacation</li>
                                            <li>Temporary project leadership</li>
                                            <li>Acting position assignments</li>
                                            <li>Training/mentorship periods</li>
                                        </ul>

                                        <h6 class="text-primary">How to Create a Delegation:</h6>
                                        <ol class="mb-3">
                                            <li>Click <strong>"Add Delegation"</strong></li>
                                            <li>Select the <strong>delegator</strong> (person delegating authority)</li>
                                            <li>Select the <strong>delegate</strong> (person receiving authority)</li>
                                            <li>Choose delegation type:
                                                <ul>
                                                    <li><strong>Full:</strong> All responsibilities</li>
                                                    <li><strong>Partial:</strong> Limited scope</li>
                                                    <li><strong>Specific:</strong> Particular tasks only</li>
                                                </ul>
                                            </li>
                                            <li>Set start and end dates</li>
                                            <li>Optionally set financial limits</li>
                                            <li>Save the delegation</li>
                                        </ol>

                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <strong>Important:</strong> Delegations automatically expire on the end date.
                                            Remember to extend if needed.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Matrix Reporting -->
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#managerMatrix">
                                        <i class="fas fa-th me-2 text-warning"></i>
                                        <strong>Matrix Reporting Explained</strong>
                                    </button>
                                </h2>
                                <div id="managerMatrix" class="accordion-collapse collapse" data-bs-parent="#managerAccordion">
                                    <div class="accordion-body">
                                        <p class="mb-3">
                                            Matrix reporting allows employees to report to multiple supervisors for different purposes:
                                        </p>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="text-primary">Example Scenario:</h6>
                                                        <p class="mb-2"><strong>John (Project Manager)</strong> reports to:</p>
                                                        <ul class="mb-0">
                                                            <li><span class="badge bg-primary">Direct (100%)</span> IT Director (functional line)</li>
                                                            <li><span class="badge bg-info">Dotted (50%)</span> Program Manager (project line)</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="text-success">Benefits:</h6>
                                                        <ul class="mb-0">
                                                            <li>Flexible organizational structure</li>
                                                            <li>Cross-functional collaboration</li>
                                                            <li>Project-based teams</li>
                                                            <li>Clear accountability</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <h6 class="text-primary">Relationship Strength:</h6>
                                        <ul class="mb-0">
                                            <li><strong>100%</strong> - Primary reporting line (most important)</li>
                                            <li><strong>50-99%</strong> - Secondary/supporting relationship</li>
                                            <li><strong>< 50%</strong> - Minimal/consultative relationship</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- For Employees Tab -->
                    <div class="tab-pane" id="helpEmployee" role="tabpanel">
                        <h5 class="mb-3 text-primary"><i class="fas fa-user me-2"></i>Employee's Guide</h5>

                        <div class="card border-info mb-3">
                            <div class="card-header bg-info-transparent">
                                <h6 class="mb-0"><i class="fas fa-eye me-2"></i>Viewing Your Reporting Structure</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-2"><strong>How to see who you report to:</strong></p>
                                <ol class="mb-3">
                                    <li>Go to your entity's details page</li>
                                    <li>Click the <strong>"Reporting Structure"</strong> tab</li>
                                    <li>Find your name in the "Current Reporting Relationships" table</li>
                                    <li>The "Reports To" column shows your supervisor(s)</li>
                                </ol>

                                <div class="bg-light p-3 rounded">
                                    <p class="mb-2"><strong>What the badges mean:</strong></p>
                                    <ul class="mb-0">
                                        <li><span class="badge bg-primary">Direct</span> - Your main supervisor (day-to-day)</li>
                                        <li><span class="badge bg-info">Dotted</span> - Secondary supervisor (specific projects)</li>
                                        <li><span class="badge bg-warning">Matrix</span> - Dual reporting situation</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="card border-success mb-3">
                            <div class="card-header bg-success-transparent">
                                <h6 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Understanding Your Role</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-2">The "Organizational Roles" section shows:</p>
                                <ul class="mb-3">
                                    <li><strong>Your position level</strong> in the organization</li>
                                    <li><strong>Your role type</strong> (Executive, Management, Supervisory, Operational)</li>
                                    <li><strong>Whether you can approve</strong> requests (leave, expenses, etc.)</li>
                                    <li><strong>Your approval limits</strong> (if applicable)</li>
                                </ul>

                                <div class="alert alert-primary">
                                    <i class="fas fa-info-circle me-2"></i>
                                    If you have questions about your role or reporting line, contact your HR department or manager.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Features Tab -->
                    <div class="tab-pane" id="helpFeatures" role="tabpanel">
                        <h5 class="mb-3 text-primary"><i class="fas fa-star me-2"></i>Features & Capabilities</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="text-primary mb-3">
                                            <i class="fas fa-project-diagram me-2"></i>Reporting Relationships
                                        </h6>
                                        <ul class="mb-0">
                                            <li>Direct reporting lines</li>
                                            <li>Matrix/dual reporting</li>
                                            <li>Multiple relationship types</li>
                                            <li>Relationship strength (0-100%)</li>
                                            <li>Effective date tracking</li>
                                            <li>Historical relationships</li>
                                            <li>Reporting frequency settings</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="text-success mb-3">
                                            <i class="fas fa-user-tie me-2"></i>Organizational Roles
                                        </h6>
                                        <ul class="mb-0">
                                            <li>10 pre-defined roles</li>
                                            <li>Hierarchical levels (0-8)</li>
                                            <li>5 role types</li>
                                            <li>Approval authorities</li>
                                            <li>Financial limits</li>
                                            <li>Parent-child relationships</li>
                                            <li>Custom role creation</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="text-info mb-3">
                                            <i class="fas fa-hand-holding me-2"></i>Delegations
                                        </h6>
                                        <ul class="mb-0">
                                            <li>Temporary authority transfer</li>
                                            <li>Date-bound assignments</li>
                                            <li>Full/Partial/Specific delegation</li>
                                            <li>Approval scope definition</li>
                                            <li>Financial limit setting</li>
                                            <li>Automatic expiration</li>
                                            <li>Delegation tracking</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="text-secondary mb-3">
                                            <i class="fas fa-sitemap me-2"></i>Organization Charts
                                        </h6>
                                        <ul class="mb-0">
                                            <li>Visual hierarchy display</li>
                                            <li>Interactive org charts</li>
                                            <li>Multiple chart support</li>
                                            <li>Position-based structure</li>
                                            <li>Chart export capabilities</li>
                                            <li>Chart selector dropdown</li>
                                            <li>Hierarchical visualization</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-success mt-3">
                            <i class="fas fa-rocket me-2"></i>
                            <strong>All features work together</strong> to provide a complete reporting structure management solution!
                        </div>
                    </div>

                    <!-- Migration Tab -->
                    <div class="tab-pane" id="helpMigration" role="tabpanel">
                        <h5 class="mb-3 text-primary"><i class="fas fa-sync me-2"></i>Migration Guide</h5>

                        <div class="alert alert-warning mb-4">
                            <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>What is Migration?</h6>
                            <p class="mb-0">
                                If you've been using the older supervisor assignment system (user_details.supervisorID),
                                you can migrate that data to the new enhanced reporting structure system for additional features.
                            </p>
                        </div>

                        <h6 class="text-primary mb-3">Why Migrate?</h6>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-danger">
                                    <div class="card-header bg-danger-transparent">
                                        <strong><i class="fas fa-times-circle me-2"></i>Old System Limitations</strong>
                                    </div>
                                    <div class="card-body">
                                        <ul class="mb-0 text-muted">
                                            <li>One supervisor only</li>
                                            <li>No relationship metadata</li>
                                            <li>No historical tracking</li>
                                            <li>No matrix reporting</li>
                                            <li>No delegation support</li>
                                            <li>Limited analytics</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-header bg-success-transparent">
                                        <strong><i class="fas fa-check-circle me-2"></i>New System Benefits</strong>
                                    </div>
                                    <div class="card-body">
                                        <ul class="mb-0">
                                            <li>Multiple supervisors (matrix)</li>
                                            <li>Relationship types & strength</li>
                                            <li>Complete history</li>
                                            <li>Matrix reporting support</li>
                                            <li>Delegation capabilities</li>
                                            <li>Advanced analytics</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h6 class="text-primary mb-3">Migration Process:</h6>
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <div class="d-flex align-items-start mb-3">
                                    <div class="avatar avatar-sm bg-primary text-white me-3 flex-shrink-0">1</div>
                                    <div>
                                        <strong>Detection</strong>
                                        <p class="mb-0 text-muted">System automatically detects legacy supervisor relationships</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start mb-3">
                                    <div class="avatar avatar-sm bg-primary text-white me-3 flex-shrink-0">2</div>
                                    <div>
                                        <strong>Display</strong>
                                        <p class="mb-0 text-muted">Shows warning card with migration option</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start mb-3">
                                    <div class="avatar avatar-sm bg-primary text-white me-3 flex-shrink-0">3</div>
                                    <div>
                                        <strong>Migration</strong>
                                        <p class="mb-0 text-muted">Click "Migrate All" or individual → buttons</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start">
                                    <div class="avatar avatar-sm bg-success text-white me-3 flex-shrink-0">✓</div>
                                    <div>
                                        <strong>Complete</strong>
                                        <p class="mb-0 text-muted">Data copied to new system, original preserved</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h6 class="text-primary mb-3">Is Migration Safe?</h6>
                        <div class="alert alert-success">
                            <h6 class="alert-heading"><i class="fas fa-shield-alt me-2"></i>100% Safe Operation</h6>
                            <ul class="mb-0">
                                <li><i class="fas fa-check text-success me-2"></i>Original data is NOT deleted</li>
                                <li><i class="fas fa-check text-success me-2"></i>user_details.supervisorID remains intact</li>
                                <li><i class="fas fa-check text-success me-2"></i>Data is COPIED, not moved</li>
                                <li><i class="fas fa-check text-success me-2"></i>Can be repeated safely (duplicate prevention)</li>
                                <li><i class="fas fa-check text-success me-2"></i>Transaction-based (rollback on error)</li>
                            </ul>
                        </div>

                        <h6 class="text-primary mb-3">What Gets Migrated?</h6>
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Old System Field</th>
                                    <th>→</th>
                                    <th>New System Field</th>
                                    <th>Default Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>supervisorID</td>
                                    <td>→</td>
                                    <td>supervisorID</td>
                                    <td>(same)</td>
                                </tr>
                                <tr>
                                    <td>employee ID</td>
                                    <td>→</td>
                                    <td>employeeID</td>
                                    <td>(same)</td>
                                </tr>
                                <tr>
                                    <td>-</td>
                                    <td>→</td>
                                    <td>relationshipType</td>
                                    <td>Direct</td>
                                </tr>
                                <tr>
                                    <td>-</td>
                                    <td>→</td>
                                    <td>relationshipStrength</td>
                                    <td>100</td>
                                </tr>
                                <tr>
                                    <td>employmentStartDate</td>
                                    <td>→</td>
                                    <td>effectiveDate</td>
                                    <td>(same)</td>
                                </tr>
                                <tr>
                                    <td>-</td>
                                    <td>→</td>
                                    <td>reportingFrequency</td>
                                    <td>Weekly</td>
                                </tr>
                                <tr>
                                    <td>-</td>
                                    <td>→</td>
                                    <td>isCurrent</td>
                                    <td>Y</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- FAQ Tab -->
                    <div class="tab-pane" id="helpFAQ" role="tabpanel">
                        <h5 class="mb-3 text-primary"><i class="fas fa-question-circle me-2"></i>Frequently Asked Questions</h5>

                        <div class="accordion" id="faqAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                        <strong>Q: What's the difference between "Legacy" and "New" relationships?</strong>
                                    </button>
                                </h2>
                                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p><strong>A:</strong> The system integrates two data sources:</p>
                                        <ul>
                                            <li><span class="badge bg-warning">Legacy</span> - Supervisor relationships from the old system (user_details.supervisorID)</li>
                                            <li><span class="badge bg-success">New</span> - Enhanced relationships from the new system (tija_reporting_relationships)</li>
                                        </ul>
                                        <p class="mb-0">Both are displayed together for a complete view. Legacy relationships can be migrated to unlock enhanced features.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                        <strong>Q: Can an employee have multiple supervisors?</strong>
                                    </button>
                                </h2>
                                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p><strong>A:</strong> Yes! This is called matrix reporting. Examples:</p>
                                        <ul>
                                            <li>A project manager who reports to both IT Director (functional) and Program Manager (project)</li>
                                            <li>A consultant who reports to Department Head (administrative) and Practice Lead (technical)</li>
                                        </ul>
                                        <p class="mb-0">Use relationship strength to indicate primary (100%) vs. secondary (50%) supervisors.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                        <strong>Q: What happens to old data after migration?</strong>
                                    </button>
                                </h2>
                                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p><strong>A:</strong> Nothing! Migration is 100% safe:</p>
                                        <ul>
                                            <li>✅ Original data in user_details.supervisorID is <strong>preserved</strong></li>
                                            <li>✅ Data is <strong>copied</strong> to the new system, not moved</li>
                                            <li>✅ You can continue using the old system if needed</li>
                                            <li>✅ Migration can be run multiple times safely</li>
                                        </ul>
                                        <div class="alert alert-success mb-0">
                                            <i class="fas fa-shield-alt me-2"></i>
                                            <strong>Guaranteed:</strong> No data loss, no system disruption.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                        <strong>Q: How do I create an organization chart?</strong>
                                    </button>
                                </h2>
                                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p><strong>A:</strong> You can work with existing org charts:</p>
                                        <ol>
                                            <li>If you have existing org charts, they'll appear in the "Existing Organizational Charts" section</li>
                                            <li>Select a chart from the dropdown to visualize it</li>
                                            <li>Click "View" to see the hierarchical structure</li>
                                            <li>Use "Export" to save the chart</li>
                                        </ol>
                                        <p class="mb-0">To create new charts, contact your system administrator or use the org chart management module.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                        <strong>Q: What's the difference between roles and job titles?</strong>
                                    </button>
                                </h2>
                                <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p><strong>A:</strong> They serve different purposes:</p>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="text-primary">Job Titles</h6>
                                                        <ul class="mb-0">
                                                            <li>Specific position name</li>
                                                            <li>Examples: "Senior Accountant", "HR Manager"</li>
                                                            <li>Unique to function/department</li>
                                                            <li>Describes what you do</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="text-success">Organizational Roles</h6>
                                                        <ul class="mb-0">
                                                            <li>Hierarchical level</li>
                                                            <li>Examples: "Manager", "Director"</li>
                                                            <li>Defines authority level</li>
                                                            <li>Describes your position in hierarchy</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="mt-3 mb-0">
                                            <i class="fas fa-lightbulb text-warning me-2"></i>
                                            <em>Example: John's <strong>job title</strong> is "Senior Tax Consultant" but his <strong>organizational role</strong> is "Manager" (level 3).</em>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                        <strong>Q: Who can see the reporting structure?</strong>
                                    </button>
                                </h2>
                                <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <p><strong>A:</strong> Access depends on user permissions:</p>
                                        <ul>
                                            <li><strong>Administrators:</strong> Full access to view and modify all reporting structures</li>
                                            <li><strong>Managers:</strong> Can view their team's reporting lines</li>
                                            <li><strong>Employees:</strong> Can view their own reporting relationships</li>
                                        </ul>
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-lock me-2"></i>
                                            All changes to the reporting structure are logged and require appropriate permissions.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
                <a href="<?= $base ?>REPORTING_STRUCTURE_INTEGRATION_GUIDE.md" target="_blank" class="btn btn-primary" download>
                    <i class="fas fa-download me-2"></i>Download Full Guide (PDF)
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.org-chart-simple {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.position-node .card {
    transition: all 0.3s;
}

.position-node .card:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Help Modal Styles */
#reportingHelpModal .accordion-button:not(.collapsed) {
    background-color: #e7f3ff;
    color: #0d6efd;
}

#reportingHelpModal .card {
    border-radius: 8px;
}

#reportingHelpModal .avatar-sm {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-weight: bold;
}

/* Live Chart Styles */
.org-chart-hierarchical .level-0 .card {
    border-width: 2px !important;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.org-chart-hierarchical .employee-card {
    position: relative;
    margin-bottom: 20px;
}

.org-chart-hierarchical .manager .card {
    background: linear-gradient(135deg, #e7f3ff 0%, #ffffff 100%);
}

.org-chart-hierarchical .employee .card {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

.org-chart-matrix .card {
    transition: transform 0.2s;
}

.org-chart-matrix .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.org-chart-flat .card {
    min-height: 80px;
}

.org-chart-divisional .division {
    background: #f8f9fa;
    border-left: 4px solid #0d6efd;
}

.org-chart-divisional .division h5 {
    color: #0d6efd;
}
</style>

<!-- Create Organization Chart Modal -->
<div class="modal fade" id="createOrgChartModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="createOrgChartForm" action="<?= $base ?>php/scripts/global/admin/organisation/manage_org_chart.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-sitemap me-2"></i>Create Organization Chart
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="orgDataID" value="<?= isset($entity->orgDataID) ? $entity->orgDataID : '' ?>">
                    <input type="hidden" name="entityID" value="<?= $entityID ?>">

                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Create a new organizational chart</strong> to visualize your entity's structure.
                        You can add positions and hierarchy after creating the chart.
                    </div>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="orgChartName" class="form-label">
                                Chart Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                id="orgChartName"
                                name="orgChartName"
                                class="form-control"
                                placeholder="e.g., 2024 Organizational Structure"
                                required>
                            <small class="text-muted">Enter a descriptive name for this organizational chart</small>
                        </div>

                        <div class="col-md-12">
                            <label for="orgChartDescription" class="form-label">
                                Description <span class="text-muted">(Optional)</span>
                            </label>
                            <textarea
                                id="orgChartDescription"
                                name="orgChartDescription"
                                class="form-control"
                                rows="3"
                                placeholder="Brief description of this org chart, its purpose, or when it applies"></textarea>
                            <small class="text-muted">Provide additional context about this chart</small>
                        </div>

                        <div class="col-md-6">
                            <label for="orgChartType" class="form-label">
                                Chart Type
                            </label>
                            <select id="orgChartType" name="chartType" class="form-select">
                                <option value="hierarchical">Hierarchical Structure</option>
                                <option value="matrix">Matrix Organization</option>
                                <option value="flat">Flat Organization</option>
                                <option value="divisional">Divisional Structure</option>
                            </select>
                            <small class="text-muted">Select the organizational structure type</small>
                        </div>

                        <div class="col-md-6">
                            <label for="orgChartEffectiveDate" class="form-label">
                                Effective Date
                            </label>
                            <input type="date"
                                id="orgChartEffectiveDate"
                                name="effectiveDate"
                                class="form-control"
                                value="<?= date('Y-m-d') ?>">
                            <small class="text-muted">When does this structure become effective?</small>
                        </div>

                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input"
                                    type="checkbox"
                                    id="isCurrentChart"
                                    name="isCurrent"
                                    value="Y"
                                    checked>
                                <label class="form-check-label" for="isCurrentChart">
                                    <strong>Set as Current Organization Chart</strong>
                                    <small class="text-muted d-block">Mark this as the active organizational structure</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="alert alert-secondary mb-0">
                        <h6 class="alert-heading">
                            <i class="fas fa-lightbulb me-2"></i>Next Steps
                        </h6>
                        <p class="mb-2">After creating the chart, you can:</p>
                        <ul class="mb-0">
                            <li>Add organizational positions</li>
                            <li>Define reporting relationships</li>
                            <li>Assign employees to positions</li>
                            <li>Visualize the complete hierarchy</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create Organization Chart
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

