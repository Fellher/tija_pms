<?php
/**
 * Pay Grades Management Interface
 * Admin page to add, edit, and manage pay grades
 * Shows job titles linked to each pay grade
 */

// Check if showing report
$showReport = isset($_GET['report']) && $_GET['report'] === 'distribution';

if ($showReport) {
    include($basedir . 'html/includes/core/admin/jobs/pay_grade_report.php');
    return;
}



// Get all pay grades for current entity
$payGrades = Data::pay_grades(['entityID' => $employeeDetails->entityID, 'Suspended' => 'N'], false, $DBConn);

// var_dump($payGrades);
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="ri-award-line me-2"></i>Pay Grades Management</h5>
                    <div>
                        <a href="?s=core&p=admin&sp=jobs&state=payGrades&report=distribution" class="btn btn-success btn-sm me-2">
                            <i class="ri-bar-chart-line me-1"></i> View Distribution Report
                        </a>
                        <button class="btn btn-light btn-sm" onclick="openPayGradeModal()">
                            <i class="ri-add-line me-1"></i> Add Pay Grade
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if ($payGrades && count($payGrades) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover" id="payGradesTable">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Grade Name</th>
                                <th>Salary Range (KES)</th>
                                <th>Level</th>
                                <th>Job Titles</th>
                                <th>Eligibility</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payGrades as $grade):
                                // Get job titles for this pay grade
                                $linkedJobs = Data::job_title_pay_grade_mapping([
                                    'payGradeID' => $grade->payGradeID,
                                    'isCurrent' => 'Y'
                                ], false, $DBConn);
                                // var_dump($linkedJobs);
                                // Get employee count in this grade
                                $employeeCount = 0;
                                if($linkedJobs && count($linkedJobs) > 0){
                                  foreach($linkedJobs as $job){
                                      $employeeCount = Data::employee_count(['jobTitleID' => $job->jobTitleID], $DBConn);
                                  }
                                }
                            ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($grade->payGradeCode) ?></strong><br>
                                    <small class="text-muted"><?= $employeeCount ?> employee(s)</small>
                                </td>
                                <td><?= htmlspecialchars($grade->payGradeName) ?></td>
                                <td>
                                    <small>
                                        Min: <?= number_format($grade->minSalary, 0) ?><br>
                                        Mid: <?= number_format($grade->midSalary, 0) ?><br>
                                        Max: <?= number_format($grade->maxSalary, 0) ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-info">Level <?= $grade->gradeLevel ?? 'N/A' ?></span>
                                </td>
                                <td>
                                    <?php if ($linkedJobs && count($linkedJobs) > 0): ?>
                                        <button class="btn btn-sm btn-outline-primary"
                                                onclick="showLinkedJobs(<?= $grade->payGradeID ?>, '<?= htmlspecialchars($grade->payGradeName, ENT_QUOTES) ?>')">
                                            <i class="ri-links-line me-1"></i> <?= count($linkedJobs) ?> Job(s)
                                        </button>
                                    <?php else: ?>
                                        <small class="text-muted">No jobs linked</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small>
                                        <?= $grade->bonusEligible == 'Y' ? '<span class="badge bg-success">Bonus</span> ' : '' ?>
                                        <?= $grade->commissionEligible == 'Y' ? '<span class="badge bg-info">Commission</span> ' : '' ?>
                                        <?= $grade->allowsOvertime == 'Y' ? '<span class="badge bg-warning">OT</span>' : '' ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-sm btn-outline-primary"
                                                onclick="editPayGrade(<?= $grade->payGradeID ?>)">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success"
                                                onclick="linkJobTitles(<?= $grade->payGradeID ?>, '<?= htmlspecialchars($grade->payGradeName, ENT_QUOTES) ?>')">
                                            <i class="ri-link"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger"
                                                onclick="deletePayGrade(<?= $grade->payGradeID ?>)">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="ri-information-line me-2"></i>
                    No pay grades configured yet. Click "Add Pay Grade" to create your first pay grade structure.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Pay Grade Modal -->
<div class="modal fade" id="payGradeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="payGradeModalTitle">Add Pay Grade</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="payGradeForm">
                <div class="modal-body">
                    <input type="hidden" id="payGradeID" name="payGradeID">
                    <input type="hidden" name="action" value="save">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Grade Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="payGradeCode" name="payGradeCode"
                                   placeholder="e.g., PG-1, GRD-A" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Grade Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="payGradeName" name="payGradeName"
                                   placeholder="e.g., Entry Level, Senior" required>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="payGradeDescription" name="payGradeDescription"
                                      rows="2" placeholder="Describe this pay grade..."></textarea>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Minimum Salary (KES) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="minSalary" name="minSalary"
                                   min="0" step="0.01" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Midpoint Salary (KES) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="midSalary" name="midSalary"
                                   min="0" step="0.01" required>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Maximum Salary (KES) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="maxSalary" name="maxSalary"
                                   min="0" step="0.01" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Grade Level <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="gradeLevel" name="gradeLevel"
                                   min="1" placeholder="1, 2, 3..." required>
                            <small class="text-muted">Lower number = junior, higher = senior</small>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Eligibility Settings</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="allowsOvertime"
                                       name="allowsOvertime" value="Y">
                                <label class="form-check-label" for="allowsOvertime">
                                    Allows Overtime
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="bonusEligible"
                                       name="bonusEligible" value="Y">
                                <label class="form-check-label" for="bonusEligible">
                                    Bonus Eligible
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="commissionEligible"
                                       name="commissionEligible" value="Y">
                                <label class="form-check-label" for="commissionEligible">
                                    Commission Eligible
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="payGradeSubmitBtn">Save Pay Grade</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Link Job Titles Modal -->
<div class="modal fade" id="linkJobsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="linkJobsModalTitle">Link Job Titles</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="linkPayGradeID">

                <div class="mb-3">
                    <label class="form-label fw-bold">Available Job Titles</label>
                    <div id="jobTitlesList" class="list-group" style="max-height: 400px; overflow-y: auto;">
                        <!-- Will be populated via AJAX -->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Show Linked Jobs Modal -->
<div class="modal fade" id="showLinkedJobsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="showLinkedJobsModalTitle">Linked Job Titles</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="linkedJobsList"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Open add/edit pay grade modal
function openPayGradeModal(gradeID = null) {
    if (gradeID) {
        // Edit mode - fetch data
        fetch('<?= $base ?>php/scripts/global/admin/manage_pay_grades.php?action=get&id=' + gradeID)
            .then(response => response.json())
            .then(data => {
                console.log('Pay grade response:', data); // Debug log
                if (data.success) {
                    populatePayGradeForm(data.data);
                    document.getElementById('payGradeModalTitle').textContent = 'Edit Pay Grade';
                } else {
                    // Show more detailed error message
                    let errorMsg = 'Failed to load pay grade: ' + (data.message || 'Unknown error');
                    if (data.debug) {
                        console.error('Debug info:', data.debug);
                        errorMsg += '\nCheck browser console for details.';
                    }
                    alert(errorMsg);
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Network error while loading pay grade. Check browser console for details.');
            });
    } else {
        // Add mode - reset form
        document.getElementById('payGradeForm').reset();
        document.getElementById('payGradeID').value = '';
        document.getElementById('payGradeModalTitle').textContent = 'Add Pay Grade';
    }

    const modal = new bootstrap.Modal(document.getElementById('payGradeModal'));
    modal.show();
}

function populatePayGradeForm(grade) {
    document.getElementById('payGradeID').value = grade.payGradeID;
    document.getElementById('payGradeCode').value = grade.payGradeCode;
    document.getElementById('payGradeName').value = grade.payGradeName;
    document.getElementById('payGradeDescription').value = grade.payGradeDescription || '';
    document.getElementById('minSalary').value = grade.minSalary;
    document.getElementById('midSalary').value = grade.midSalary;
    document.getElementById('maxSalary').value = grade.maxSalary;
    document.getElementById('gradeLevel').value = grade.gradeLevel;
    document.getElementById('allowsOvertime').checked = (grade.allowsOvertime === 'Y');
    document.getElementById('bonusEligible').checked = (grade.bonusEligible === 'Y');
    document.getElementById('commissionEligible').checked = (grade.commissionEligible === 'Y');
}

function editPayGrade(gradeID) {
    openPayGradeModal(gradeID);
}

function deletePayGrade(gradeID) {
    if (confirm('Are you sure you want to delete this pay grade? This may affect employees assigned to it.')) {
        fetch('<?= $base ?>php/scripts/global/admin/manage_pay_grades.php?action=delete&id=' + gradeID, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Pay grade deleted successfully');
                location.reload();
            } else {
                alert(data.message || 'Failed to delete pay grade');
            }
        });
    }
}

// Link job titles to pay grade
function linkJobTitles(gradeID, gradeName) {
    document.getElementById('linkPayGradeID').value = gradeID;
    document.getElementById('linkJobsModalTitle').textContent = 'Link Job Titles to ' + gradeName;

    // Fetch all job titles
    fetch('<?= $base ?>php/scripts/global/admin/manage_pay_grades.php?action=get_job_titles&gradeID=' + gradeID)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const jobTitlesList = document.getElementById('jobTitlesList');
                jobTitlesList.innerHTML = '';

                data.jobTitles.forEach(job => {
                    const item = document.createElement('div');
                    item.className = 'list-group-item d-flex justify-content-between align-items-center';
                    item.innerHTML = `
                        <span>${job.jobTitle}</span>
                        <button class="btn btn-sm ${job.isLinked ? 'btn-danger' : 'btn-success'}"
                                onclick="toggleJobLink(${gradeID}, ${job.jobTitleID}, ${job.isLinked ? 1 : 0})">
                            ${job.isLinked ? 'Unlink' : 'Link'}
                        </button>
                    `;
                    jobTitlesList.appendChild(item);
                });
            }
        });

    const modal = new bootstrap.Modal(document.getElementById('linkJobsModal'));
    modal.show();
}

// Toggle job title link
function toggleJobLink(gradeID, jobTitleID, currentlyLinked) {
    const action = currentlyLinked ? 'unlink' : 'link';

    fetch('<?= $base ?>php/scripts/global/admin/manage_pay_grades.php?action=' + action + '_job&gradeID=' + gradeID + '&jobTitleID=' + jobTitleID, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Refresh the job titles list
            linkJobTitles(gradeID, '');
            // Also refresh the main page to update job count
            setTimeout(() => location.reload(), 1000);
        } else {
            alert(data.message || 'Failed to update link');
        }
    });
}

// Show linked jobs
function showLinkedJobs(gradeID, gradeName) {
    fetch('<?= $base ?>php/scripts/global/admin/manage_pay_grades.php?action=get_linked_jobs&gradeID=' + gradeID)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const linkedJobsList = document.getElementById('linkedJobsList');
                linkedJobsList.innerHTML = '<ul class="list-group">';

                data.jobs.forEach(job => {
                    linkedJobsList.innerHTML += `
                        <li class="list-group-item">
                            <strong>${job.jobTitle}</strong>
                            <br><small class="text-muted">Since: ${job.effectiveDate}</small>
                        </li>
                    `;
                });

                linkedJobsList.innerHTML += '</ul>';

                document.getElementById('showLinkedJobsModalTitle').textContent = 'Job Titles in ' + gradeName;
                const modal = new bootstrap.Modal(document.getElementById('showLinkedJobsModal'));
                modal.show();
            }
        });
}

// Form submission
document.getElementById('payGradeForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('<?= $base ?>php/scripts/global/admin/manage_pay_grades.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Pay grade saved successfully');
            location.reload();
        } else {
            alert(data.message || 'Failed to save pay grade');
        }
    });
});
</script>

