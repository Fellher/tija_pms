<?php
/**
 * Pay Grades Management Tab
 * Displays and manages pay grades for the entity and organization
 */

// Get currencies for dropdown (use method if exists, otherwise use default list)
$currencies = [];
if (method_exists('Data', 'currencies')) {
    $currencies = Data::currencies(['Suspended' => 'N'], false, $DBConn);
} else {
    // Fallback to common currencies
    $currencies = [
        (object)['currencyCode' => 'KES', 'currencyName' => 'Kenyan Shilling'],
        (object)['currencyCode' => 'USD', 'currencyName' => 'US Dollar'],
        (object)['currencyCode' => 'EUR', 'currencyName' => 'Euro'],
        (object)['currencyCode' => 'GBP', 'currencyName' => 'British Pound'],
        (object)['currencyCode' => 'ZAR', 'currencyName' => 'South African Rand'],
        (object)['currencyCode' => 'TZS', 'currencyName' => 'Tanzanian Shilling'],
        (object)['currencyCode' => 'UGX', 'currencyName' => 'Ugandan Shilling'],
    ];
}

// Statistics
$entitySpecificCount = 0;
$orgWideCount = 0;
$minSalaryTotal = 0;
$maxSalaryTotal = 0;

if ($payGrades) {
    foreach ($payGrades as $grade) {
        if ($grade->entityID == $entityID) {
            $entitySpecificCount++;
        } elseif ($grade->entityID == null) {
            $orgWideCount++;
        }
        $minSalaryTotal += ($grade->minSalary ?? 0);
        $maxSalaryTotal += ($grade->maxSalary ?? 0);
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
                        data-bs-target="#managePayGradeModal"
                        onclick="addNewPayGrade()">
                        <i class="fas fa-plus me-2"></i>Add Pay Grade
                    </button>
                    <button type="button" class="btn btn-success btn-sm btn-wave" onclick="importPayGrades()">
                        <i class="fas fa-file-import me-2"></i>Import Pay Grades
                    </button>
                    <button type="button" class="btn btn-info btn-sm btn-wave" onclick="exportPayGrades()">
                        <i class="fas fa-file-export me-2"></i>Export Pay Grades
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm btn-wave"
                        data-bs-toggle="modal"
                        data-bs-target="#payGradeHelpModal">
                        <i class="fas fa-question-circle me-2"></i>Help
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md bg-primary-transparent me-3">
                        <i class="fas fa-layer-group fs-20"></i>
                    </div>
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Total Pay Grades</p>
                        <h4 class="mb-0"><?= $payGradesCount ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md bg-success-transparent me-3">
                        <i class="fas fa-building fs-20"></i>
                    </div>
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Entity-Specific</p>
                        <h4 class="mb-0"><?= $entitySpecificCount ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md bg-info-transparent me-3">
                        <i class="fas fa-globe fs-20"></i>
                    </div>
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Organization-Wide</p>
                        <h4 class="mb-0"><?= $orgWideCount ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md bg-warning-transparent me-3">
                        <i class="fas fa-chart-line fs-20"></i>
                    </div>
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Grade Levels</p>
                        <h4 class="mb-0"><?= $payGrades ? count(array_unique(array_column($payGrades, 'gradeLevel'))) : 0 ?></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pay Grades Table -->
<div class="row">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Pay Grades</h5>
                </div>
                <div>
                    <button type="button" class="btn btn-primary btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#managePayGradeModal"
                        onclick="addNewPayGrade()">
                        <i class="fas fa-plus me-2"></i>Add Pay Grade
                    </button>
                </div>
            </div>
            <div class="card-body">
                <?php if ($payGrades && count($payGrades) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0" id="payGradesTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 10%;">Code</th>
                                    <th style="width: 18%;">Grade Name</th>
                                    <th style="width: 8%;">Level</th>
                                    <th style="width: 12%;">Min Salary</th>
                                    <th style="width: 12%;">Mid Salary</th>
                                    <th style="width: 12%;">Max Salary</th>
                                    <th style="width: 8%;">Currency</th>
                                    <th style="width: 8%;">Scope</th>
                                    <th style="width: 7%;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payGrades as $index => $grade): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <strong class="text-primary"><?= htmlspecialchars($grade->payGradeCode ?? 'N/A') ?></strong>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($grade->payGradeName) ?></strong>
                                            <?php if ($grade->payGradeDescription): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($grade->payGradeDescription) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge bg-secondary-transparent">Level <?= $grade->gradeLevel ?? 'N/A' ?></span></td>
                                        <td><?= number_format($grade->minSalary ?? 0, 2) ?></td>
                                        <td><strong><?= number_format($grade->midSalary ?? 0, 2) ?></strong></td>
                                        <td><?= number_format($grade->maxSalary ?? 0, 2) ?></td>
                                        <td><?= htmlspecialchars($grade->currency ?? 'KES') ?></td>
                                        <td>
                                            <?php if ($grade->entityID == $entityID): ?>
                                                <span class="badge bg-success-transparent">
                                                    <i class="fas fa-building"></i> Entity
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-info-transparent">
                                                    <i class="fas fa-globe"></i> Org-Wide
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-primary-light editPayGrade"
                                                title="Edit Pay Grade"
                                                data-bs-toggle="modal"
                                                data-bs-target="#managePayGradeModal"
                                                data-grade-id="<?= $grade->payGradeID ?>">
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
                        <div class="avatar avatar-lg bg-primary-transparent mx-auto mb-3">
                            <i class="fas fa-money-bill-wave fs-24"></i>
                        </div>
                        <h6 class="mb-2">No Pay Grades Defined</h6>
                        <p class="text-muted mb-3">Create pay grades to structure compensation for this entity.</p>
                        <button type="button" class="btn btn-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#managePayGradeModal"
                            onclick="addNewPayGrade()">
                            <i class="fas fa-plus me-2"></i>Add First Pay Grade
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Pay Grade Benefits/Features Info -->
<?php if ($payGrades && count($payGrades) > 0): ?>
<div class="row mt-4">
    <div class="col-md-4">
        <div class="card custom-card border-primary">
            <div class="card-body text-center">
                <i class="fas fa-clock text-primary fs-30 mb-2"></i>
                <h6 class="mb-2">Overtime Eligible</h6>
                <h4 class="mb-0 text-primary">
                    <?= count(array_filter($payGrades, function($g) { return $g->allowsOvertime == 'Y'; })) ?>
                </h4>
                <small class="text-muted">Pay grades with overtime</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card custom-card border-success">
            <div class="card-body text-center">
                <i class="fas fa-gift text-success fs-30 mb-2"></i>
                <h6 class="mb-2">Bonus Eligible</h6>
                <h4 class="mb-0 text-success">
                    <?= count(array_filter($payGrades, function($g) { return $g->bonusEligible == 'Y'; })) ?>
                </h4>
                <small class="text-muted">Pay grades with bonus</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card custom-card border-warning">
            <div class="card-body text-center">
                <i class="fas fa-percentage text-warning fs-30 mb-2"></i>
                <h6 class="mb-2">Commission Eligible</h6>
                <h4 class="mb-0 text-warning">
                    <?= count(array_filter($payGrades, function($g) { return $g->commissionEligible == 'Y'; })) ?>
                </h4>
                <small class="text-muted">Pay grades with commission</small>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
// Pay Grade Management Functions

function addNewPayGrade() {
    const modal = document.querySelector('#managePayGradeModal');
    if (modal) {
        // Reset form
        modal.querySelector('form')?.reset();

        // Set defaults
        document.getElementById('payGradeScope').value = 'entity';
        document.getElementById('pg_gradeLevel').value = '5';
        document.getElementById('pg_allowsOvertime').checked = false;
        document.getElementById('pg_bonusEligible').checked = false;
        document.getElementById('pg_commissionEligible').checked = false;

        // Set currency default (check if KES option exists, otherwise use first available)
        const currencySelect = document.getElementById('pg_currency');
        if (currencySelect) {
            const kesOption = Array.from(currencySelect.options).find(opt => opt.value === 'KES');
            if (kesOption) {
                currencySelect.value = 'KES';
            } else if (currencySelect.options.length > 1) {
                currencySelect.selectedIndex = 1; // Select first non-empty option
            }
        }

        // Update modal title
        document.getElementById('payGradeModalTitle').textContent = 'Add New Pay Grade';

        // Clear ID (for new grade)
        document.getElementById('payGradeID').value = '';
    }
}

function editPayGrade(payGradeID) {
    const modal = document.querySelector('#managePayGradeModal');

    if (!modal) {
        console.error('Pay grade modal not found');
        return;
    }

    // Update modal title
    document.getElementById('payGradeModalTitle').textContent = 'Edit Pay Grade';

    // Fetch pay grade data
    const url = '<?= $base ?>php/scripts/global/admin/get_pay_grade.php?payGradeID=' + payGradeID;
    console.log('Fetching pay grade data from:', url);

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Pay grade data:', data);

            if (data.success && data.payGrade) {
                const grade = data.payGrade;

                // Populate form fields
                document.getElementById('payGradeID').value = grade.payGradeID || '';
                document.getElementById('pg_payGradeCode').value = grade.payGradeCode || '';
                document.getElementById('pg_payGradeName').value = grade.payGradeName || '';
                document.getElementById('pg_payGradeDescription').value = grade.payGradeDescription || '';
                document.getElementById('pg_gradeLevel').value = grade.gradeLevel || '5';
                document.getElementById('pg_minSalary').value = grade.minSalary || '';
                document.getElementById('pg_midSalary').value = grade.midSalary || '';
                document.getElementById('pg_maxSalary').value = grade.maxSalary || '';
                document.getElementById('pg_currency').value = grade.currency || 'KES';
                document.getElementById('pg_allowsOvertime').checked = (grade.allowsOvertime === 'Y');
                document.getElementById('pg_bonusEligible').checked = (grade.bonusEligible === 'Y');
                document.getElementById('pg_commissionEligible').checked = (grade.commissionEligible === 'Y');
                document.getElementById('pg_notes').value = grade.notes || '';

                // Set scope based on entityID
                if (grade.entityID && grade.entityID != '' && grade.entityID != null) {
                    document.getElementById('payGradeScope').value = 'entity';
                } else {
                    document.getElementById('payGradeScope').value = 'organization';
                }

            } else {
                alert('Error: ' + (data.message || 'Failed to load pay grade data'));
            }
        })
        .catch(error => {
            console.error('Error loading pay grade:', error);
            alert('Error loading pay grade data: ' + error.message);
        });
}

// Calculate mid salary automatically
function calculateMidSalary() {
    const minSalary = parseFloat(document.getElementById('pg_minSalary').value) || 0;
    const maxSalary = parseFloat(document.getElementById('pg_maxSalary').value) || 0;

    if (minSalary > 0 && maxSalary > 0) {
        const midSalary = (minSalary + maxSalary) / 2;
        document.getElementById('pg_midSalary').value = midSalary.toFixed(2);
    }
}

function importPayGrades() {
    alert('Import functionality will be implemented soon.\nYou will be able to import pay grades from CSV/Excel files.');
}

function exportPayGrades() {
    window.location.href = '<?= $base ?>php/scripts/global/admin/export_pay_grades.php?entityID=<?= $entityID ?>';
}

// Add event listeners for edit buttons
document.addEventListener('DOMContentLoaded', function() {
    // Attach listeners to edit buttons
    document.querySelectorAll('.editPayGrade').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const gradeID = this.getAttribute('data-grade-id');
            if (gradeID) {
                editPayGrade(gradeID);
            }
        });
    });

    // Auto-calculate mid salary
    const minInput = document.getElementById('pg_minSalary');
    const maxInput = document.getElementById('pg_maxSalary');

    if (minInput) minInput.addEventListener('blur', calculateMidSalary);
    if (maxInput) maxInput.addEventListener('blur', calculateMidSalary);
});
</script>

<!-- Manage Pay Grade Modal -->
<div class="modal fade" id="managePayGradeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <form action="<?= $base ?>php/scripts/global/admin/manage_pay_grade.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="payGradeModalTitle">
                        <i class="fas fa-money-bill-wave me-2"></i>Manage Pay Grade
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="payGradeID" name="payGradeID">
                    <input type="hidden" id="pg_orgDataID" name="orgDataID" value="<?= isset($entity->orgDataID) ? $entity->orgDataID : '' ?>">
                    <input type="hidden" id="pg_entityID" name="entityID" value="<?= $entityID ?>">

                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Pay grades</strong> define salary ranges and compensation structures for positions within your organization.
                    </div>

                    <div class="row g-3">
                        <!-- Basic Information -->
                        <div class="col-md-12">
                            <h6 class="text-primary mb-2">
                                <i class="fas fa-info-circle me-2"></i>Basic Information
                            </h6>
                        </div>

                        <div class="col-md-4">
                            <label for="pg_payGradeCode" class="form-label">Pay Grade Code <span class="text-danger">*</span></label>
                            <input type="text" id="pg_payGradeCode" name="payGradeCode"
                                class="form-control"
                                placeholder="e.g., PG-001, EXEC-1" required>
                            <small class="text-muted">Unique code for this pay grade</small>
                        </div>

                        <div class="col-md-8">
                            <label for="pg_payGradeName" class="form-label">Pay Grade Name <span class="text-danger">*</span></label>
                            <input type="text" id="pg_payGradeName" name="payGradeName"
                                class="form-control"
                                placeholder="e.g., Executive Level 1, Junior Officer" required>
                            <small class="text-muted">Descriptive name for this pay grade</small>
                        </div>

                        <div class="col-md-12">
                            <label for="pg_payGradeDescription" class="form-label">Description</label>
                            <textarea id="pg_payGradeDescription" name="payGradeDescription"
                                class="form-control" rows="2"
                                placeholder="Brief description of this pay grade and typical positions"></textarea>
                        </div>

                        <div class="col-md-4">
                            <label for="pg_gradeLevel" class="form-label">Grade Level</label>
                            <select id="pg_gradeLevel" name="gradeLevel" class="form-select">
                                <option value="1">Level 1 - Executive/C-Suite</option>
                                <option value="2">Level 2 - Senior Management</option>
                                <option value="3">Level 3 - Middle Management</option>
                                <option value="4">Level 4 - Supervisory</option>
                                <option value="5" selected>Level 5 - Professional</option>
                                <option value="6">Level 6 - Support Staff</option>
                                <option value="7">Level 7 - Entry Level</option>
                                <option value="8">Level 8 - Trainee</option>
                            </select>
                            <small class="text-muted">Hierarchy level in organization</small>
                        </div>

                        <div class="col-md-4">
                            <label for="pg_currency" class="form-label">Currency <span class="text-danger">*</span></label>
                            <select id="pg_currency" name="currency" class="form-select" required>
                                <option value="">Select Currency</option>
                                <?php if ($currencies && count($currencies) > 0): ?>
                                    <?php foreach ($currencies as $curr): ?>
                                        <option value="<?= htmlspecialchars($curr->currencyCode) ?>">
                                            <?= htmlspecialchars($curr->currencyCode) ?> - <?= htmlspecialchars($curr->currencyName) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="KES">KES - Kenyan Shilling</option>
                                    <option value="USD">USD - US Dollar</option>
                                    <option value="EUR">EUR - Euro</option>
                                    <option value="GBP">GBP - British Pound</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="payGradeScope" class="form-label">Scope</label>
                            <select id="payGradeScope" name="payGradeScope" class="form-select">
                                <option value="entity">Entity-Specific</option>
                                <option value="organization">Organization-Wide</option>
                            </select>
                            <small class="text-muted">Apply to this entity or all entities</small>
                        </div>

                        <!-- Salary Ranges -->
                        <div class="col-md-12 mt-3">
                            <hr>
                            <h6 class="text-primary mb-2">
                                <i class="fas fa-coins me-2"></i>Salary Ranges
                            </h6>
                        </div>

                        <div class="col-md-4">
                            <label for="pg_minSalary" class="form-label">Minimum Salary <span class="text-danger">*</span></label>
                            <input type="number" id="pg_minSalary" name="minSalary"
                                class="form-control"
                                placeholder="0.00" step="0.01" min="0" required>
                        </div>

                        <div class="col-md-4">
                            <label for="pg_midSalary" class="form-label">Mid-Point Salary <span class="text-danger">*</span></label>
                            <input type="number" id="pg_midSalary" name="midSalary"
                                class="form-control"
                                placeholder="Auto-calculated" step="0.01" min="0" required>
                            <small class="text-muted">Auto-calculates from min/max</small>
                        </div>

                        <div class="col-md-4">
                            <label for="pg_maxSalary" class="form-label">Maximum Salary <span class="text-danger">*</span></label>
                            <input type="number" id="pg_maxSalary" name="maxSalary"
                                class="form-control"
                                placeholder="0.00" step="0.01" min="0" required>
                        </div>

                        <!-- Compensation Features -->
                        <div class="col-md-12 mt-3">
                            <hr>
                            <h6 class="text-primary mb-2">
                                <i class="fas fa-check-circle me-2"></i>Compensation Features
                            </h6>
                        </div>

                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="pg_allowsOvertime"
                                    name="allowsOvertime" value="Y">
                                <label class="form-check-label" for="pg_allowsOvertime">
                                    <strong>Allows Overtime</strong>
                                    <small class="text-muted d-block">Employees can claim overtime pay</small>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="pg_bonusEligible"
                                    name="bonusEligible" value="Y">
                                <label class="form-check-label" for="pg_bonusEligible">
                                    <strong>Bonus Eligible</strong>
                                    <small class="text-muted d-block">Eligible for performance bonuses</small>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="pg_commissionEligible"
                                    name="commissionEligible" value="Y">
                                <label class="form-check-label" for="pg_commissionEligible">
                                    <strong>Commission Eligible</strong>
                                    <small class="text-muted d-block">Can earn commission on sales</small>
                                </label>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="col-md-12">
                            <label for="pg_notes" class="form-label">Notes</label>
                            <textarea id="pg_notes" name="notes" class="form-control" rows="2"
                                placeholder="Additional notes about this pay grade"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Pay Grade
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Help Modal -->
<div class="modal fade" id="payGradeHelpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-book-open me-2"></i>Pay Grades - User Guide
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 class="text-primary mb-3">What are Pay Grades?</h6>
                <p>Pay grades are structured salary ranges that help organizations manage compensation fairly and consistently. Each pay grade defines minimum, mid-point, and maximum salary levels for positions.</p>

                <h6 class="text-primary mb-3 mt-4">Key Components:</h6>
                <ul>
                    <li><strong>Minimum Salary:</strong> Entry point for new hires or minimum acceptable pay</li>
                    <li><strong>Mid-Point:</strong> Target salary for competent, fully qualified employees</li>
                    <li><strong>Maximum Salary:</strong> Highest salary achievable in this grade</li>
                </ul>

                <h6 class="text-primary mb-3 mt-4">Compensation Features:</h6>
                <ul>
                    <li><strong>Overtime:</strong> Can employees in this grade claim overtime pay?</li>
                    <li><strong>Bonus:</strong> Are they eligible for performance bonuses?</li>
                    <li><strong>Commission:</strong> Can they earn commission on sales?</li>
                </ul>

                <h6 class="text-primary mb-3 mt-4">Entity vs Organization Scope:</h6>
                <p><strong>Entity-Specific:</strong> Pay grade applies only to this entity</p>
                <p><strong>Organization-Wide:</strong> Pay grade available to all entities in the organization</p>

                <div class="alert alert-success mt-4">
                    <i class="fas fa-lightbulb me-2"></i>
                    <strong>Best Practice:</strong> Create organization-wide pay grades for consistency across entities,
                    and entity-specific grades only when needed for special cases or local market conditions.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
#payGradesTable tbody tr:hover {
    background-color: #f8f9fa;
    cursor: pointer;
}

.avatar-md {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}
</style>

