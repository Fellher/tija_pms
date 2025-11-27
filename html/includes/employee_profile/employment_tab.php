<?php
/**
 * Employment & Job History Tab
 * Displays current employment details from user_details and job history
 * Includes inline edit functionality
 */

// Get job history
$jobHistory = EmployeeProfile::job_history(['employeeID' => $employeeID, 'Suspended' => 'N'], false, $DBConn);

// Get lookup data for dropdowns
$jobTitles = Data::job_titles(['Suspended' => 'N'], false, $DBConn);
// Get all units for the employee's entity
$allUnits = Data::units_full(['entityID' => $employeeDetails->entityID, 'Suspended' => 'N'], false, $DBConn);
// Get departments from tija_units where unitTypeID = 1 (Department type)
$departments = Data::departments(['entityID' => $employeeDetails->entityID, 'Suspended' => 'N'], false, $DBConn);
$employmentStatuses = Admin::tija_employment_status(['Suspended' => 'N'], false, $DBConn);

// Get current user unit assignments
$userUnitAssignments = Employee::user_unit_assignments(['userID' => $employeeID, 'Suspended' => 'N'], false, $DBConn);
?>

<!-- VIEW MODE -->
<div id="employmentViewMode">
    <!-- Tab Header -->
    <div class="tab-header">
        <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1">
                <h5 class="mb-1"><i class="ri-briefcase-line me-2"></i>Employment Details & Job History</h5>
                <p class="text-muted small mb-0">Current employment information, work schedule, and internal job movement history</p>
            </div>
            <?php if ($canEdit): ?>
            <button class="btn btn-sm btn-primary" id="editEmploymentBtn">
                <i class="ri-edit-line me-1"></i> Edit Employment
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Current Employment Details -->
    <div class="row">
        <div class="col-md-6">
            <div class="info-card">
                <h6 class="fw-bold mb-3">Position & Department</h6>

                <div class="data-row">
                    <span class="data-label">Payroll Number:</span>
                    <span class="data-value"><?= htmlspecialchars($employeeDetails->payrollNo ?? 'N/A') ?></span>
                </div>

                <div class="data-row">
                    <span class="data-label">Job Title:</span>
                    <span class="data-value"><?= htmlspecialchars($employeeDetails->jobTitle ?? 'Not assigned') ?></span>
                </div>

                <div class="data-row">
                    <span class="data-label">Unit Assignments:</span>
                    <span class="data-value">
                        <?php if ($userUnitAssignments && count($userUnitAssignments) > 0): ?>
                            <?php foreach ($userUnitAssignments as $index => $assignment): ?>
                                <span class="badge bg-primary-transparent me-1 mb-1">
                                    <?= htmlspecialchars($assignment->unitName ?? 'N/A') ?>
                                    <small class="text-muted">(<?= htmlspecialchars($assignment->unitTypeName ?? 'Unit') ?>)</small>
                                </span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="text-muted">Not assigned to any units</span>
                        <?php endif; ?>
                    </span>
                </div>

                <div class="data-row">
                    <span class="data-label">Employment Status:</span>
                    <span class="data-value">
                        <span class="badge bg-success"><?= htmlspecialchars($employeeDetails->employmentStatusTitle ?? 'Active') ?></span>
                    </span>
                </div>

                <div class="data-row">
                    <span class="data-label">Supervisor:</span>
                    <span class="data-value">
                        <?php if (isset($employeeDetails->supervisorName) && !empty($employeeDetails->supervisorName)): ?>
                            <?= htmlspecialchars($employeeDetails->supervisorName) ?>
                        <?php elseif (isset($employeeDetails->supervisorID) && ($employeeDetails->supervisorID == 0 || $employeeDetails->supervisorID == null)): ?>
                            <span class="badge bg-warning-transparent">
                                <i class="fas fa-crown me-1"></i>No Supervisor - Reports to Board/External
                            </span>
                        <?php else: ?>
                            <span class="text-muted">Not assigned</span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="info-card">
                <h6 class="fw-bold mb-3">Employment Dates & Work Schedule</h6>

                <div class="data-row">
                    <span class="data-label">Employment Start:</span>
                    <span class="data-value">
                        <?= isset($employeeDetails->employmentStartDate) && $employeeDetails->employmentStartDate != '0000-00-00'
                            ? date('F j, Y', strtotime($employeeDetails->employmentStartDate))
                            : 'Not recorded' ?>
                    </span>
                </div>

                <div class="data-row">
                    <span class="data-label">Contract Start:</span>
                    <span class="data-value">
                        <?= !empty($employeeDetails->contractStartDate) && $employeeDetails->contractStartDate != '0000-00-00'
                            ? date('F j, Y', strtotime($employeeDetails->contractStartDate))
                            : 'N/A' ?>
                    </span>
                </div>

                <div class="data-row">
                    <span class="data-label">Contract End:</span>
                    <span class="data-value">
                        <?= !empty($employeeDetails->contractEndDate) && $employeeDetails->contractEndDate != '0000-00-00'
                            ? date('F j, Y', strtotime($employeeDetails->contractEndDate))
                            : 'N/A' ?>
                    </span>
                </div>

                <div class="data-row">
                    <span class="data-label">Daily Work Hours:</span>
                    <span class="data-value"><?= $employeeDetails->dailyHours ?? '8' ?> hours</span>
                </div>

                <div class="data-row">
                    <span class="data-label">Weekly Work Days:</span>
                    <span class="data-value"><?= $employeeDetails->weekWorkDays ?? '5' ?> days</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Job History Section -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="info-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Job History Within Company</h6>
                    <?php if ($canEdit): ?>
                    <button class="btn btn-sm btn-outline-primary" onclick="addJobHistory()">
                        <i class="ri-add-line me-1"></i> Add Job History
                    </button>
                    <?php endif; ?>
                </div>

                <?php if ($jobHistory && count($jobHistory) > 0): ?>
                <div class="timeline">
                    <?php foreach ($jobHistory as $job): ?>
                    <div class="timeline-item">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="d-flex justify-content-between align-items-start" style="width: 70%;" >
                                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($job->jobTitleName ?? $job->jobTitle ?? 'Position') ?></h6>
                                    <p class="text-muted mb-2">
                                        <span class="text-success me-3">
                                            Department

                                        </span>
                                        <a href="<?= $base ?>html/department.php?id=<?= $job->departmentID ?>" target="_blank"><?= htmlspecialchars($job->departmentName ?? '') ?></a>
                                        <?php if ($job->isCurrent == 'Y'): ?>
                                        <span class="badge bg-primary ms-2">Current</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <?php if ($canEdit): ?>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-sm btn-outline-primary" onclick="editJobHistory(<?= $job->jobHistoryID ?>)">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteJobHistory(<?= $job->jobHistoryID ?>)">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>

                            <p class="mb-2">
                                <strong>Period:</strong>
                                <?= date('M Y', strtotime($job->startDate)) ?> -
                                <?= $job->endDate ? date('M Y', strtotime($job->endDate)) : 'Present' ?>
                            </p>

                            <?php if (!empty($job->responsibilities)): ?>
                            <p class="mb-2"><strong>Responsibilities:</strong><br><?= nl2br(htmlspecialchars($job->responsibilities)) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($job->achievements)): ?>
                            <p class="mb-2"><strong>Achievements:</strong><br><?= nl2br(htmlspecialchars($job->achievements)) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($job->changeReason)): ?>
                            <p class="mb-0"><strong>Change Reason:</strong> <?= htmlspecialchars($job->changeReason) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="ri-information-line me-2"></i>
                    No job history recorded. Click "Add Job History" to track internal job movements.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<!-- END VIEW MODE -->

<!-- EDIT MODE (Hidden by default) -->
<?php if ($canEdit): ?>
<div id="employmentEditMode" class="d-none">
    <!-- Edit Header -->
    <div class="tab-header">
        <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1">
                <h5 class="mb-1"><i class="ri-edit-line me-2"></i>Edit Employment Details</h5>
                <p class="text-muted small mb-0">Update job information, work schedule, and employment dates</p>
            </div>
            <button class="btn btn-sm btn-secondary" id="cancelEmploymentEditBtn">
                <i class="ri-close-line me-1"></i> Cancel
            </button>
        </div>
    </div>

    <form id="employmentEditForm" action="<?= $base ?>php/scripts/global/admin/manage_users.php" method="post">
        <input type="hidden" name="ID" value="<?= $employeeID ?>">
        <input type="hidden" name="redirectUrl" value="<?= "?s={$s}&p={$p}&uid={$employeeID}&tab={$currentTab}" ?>">
        <input type="hidden" name="organisationID" value="<?= $employeeDetails->orgDataID ?? '' ?>">
        <input type="hidden" name="entityID" value="<?= $employeeDetails->entityID ?? '' ?>">

        <div class="row">
            <!-- Position & Department -->
            <div class="col-md-12">
                <div class="info-card">
                    <h6 class="fw-bold mb-3 text-primary">Position & Department</h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">Payroll Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="payrollNumber"
                                   value="<?= htmlspecialchars($employeeDetails->payrollNo ?? '') ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">Job Title</label>
                            <select class="form-control form-control-sm" name="jobTitleID">
                                <option value="">Select Job Title</option>
                                <?php if (!empty($jobTitles)): foreach($jobTitles as $title): ?>
                                <option value="<?= $title->jobTitleID ?>" <?= ($employeeDetails->jobTitleID == $title->jobTitleID) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($title->jobTitle) ?>
                                </option>
                                <?php endforeach; else: ?>
                                <option value="" disabled>Job titles not available</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label small text-primary">Unit Assignments</label>
                            <div class="card">
                                <div class="card-body">
                                    <!-- Current Assignments -->
                                    <div id="currentUnitAssignments" class="mb-3">
                                        <?php if ($userUnitAssignments && count($userUnitAssignments) > 0): ?>
                                            <?php foreach ($userUnitAssignments as $assignment): ?>
                                                <div class="unit-assignment-item d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                                                    <div>
                                                        <strong><?= htmlspecialchars($assignment->unitName ?? 'N/A') ?></strong>
                                                        <small class="text-muted d-block"><?= htmlspecialchars($assignment->unitTypeName ?? 'Unit') ?></small>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        onclick="removeUnitAssignment(<?= $assignment->unitAssignmentID ?>)">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="alert alert-info mb-0">
                                                <i class="ri-information-line me-2"></i>No unit assignments yet.
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Add New Assignment -->
                                    <div class="border-top pt-3">
                                        <h6 class="small fw-bold mb-2">Add Unit Assignment</h6>
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <select class="form-control form-control-sm" id="newUnitID">
                                                    <option value="">-- Select Unit/Department --</option>
                                                    <?php if (!empty($allUnits)): ?>
                                                        <?php
                                                        // Group units by type
                                                        $unitsByType = [];
                                                        foreach ($allUnits as $unit) {
                                                            // Handle missing unitTypeName
                                                            $typeName = 'Other';
                                                            if (isset($unit->unitTypeName) && !empty($unit->unitTypeName)) {
                                                                $typeName = $unit->unitTypeName;
                                                            } elseif (isset($unit->unitTypeID)) {
                                                                // Fallback to unit type ID
                                                                $typeMap = [
                                                                    1 => 'Departments',
                                                                    2 => 'Sections',
                                                                    3 => 'Teams',
                                                                    4 => 'Divisions',
                                                                    5 => 'Branches'
                                                                ];
                                                                $typeName = $typeMap[$unit->unitTypeID] ?? 'Other Units';
                                                            }

                                                            if (!isset($unitsByType[$typeName])) {
                                                                $unitsByType[$typeName] = [];
                                                            }
                                                            $unitsByType[$typeName][] = $unit;
                                                        }
                                                        ?>
                                                        <?php foreach ($unitsByType as $typeName => $units): ?>
                                                            <optgroup label="<?= htmlspecialchars($typeName) ?>">
                                                                <?php foreach ($units as $unit): ?>
                                                                    <option value="<?= $unit->unitID ?>" data-type="<?= $unit->unitTypeID ?>">
                                                                        <?= htmlspecialchars($unit->unitName) ?>
                                                                        <?php if (isset($unit->parentUnitName) && !empty($unit->parentUnitName)): ?>
                                                                            - <?= htmlspecialchars($unit->parentUnitName) ?>
                                                                        <?php endif; ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </optgroup>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <button type="button" class="btn btn-success btn-sm" onclick="addUnitAssignment()">
                                                    <i class="ri-add-line me-1"></i>Add Assignment
                                                </button>
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Select department, section, team, or other organizational unit to assign this employee.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">Employment Status</label>
                            <select class="form-control form-control-sm" name="employeeTypeID">
                                <option value="">Select Status</option>
                                <?php if (!empty($employmentStatuses)): foreach($employmentStatuses as $status): ?>
                                <option value="<?= $status->employmentStatusID ?>" <?= ($employeeDetails->employmentStatusID == $status->employmentStatusID) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($status->employmentStatusTitle) ?>
                                </option>
                                <?php endforeach; else: ?>
                                <option value="" disabled>Employment statuses not available</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">Supervisor</label>
                            <select class="form-control form-control-sm" name="supervisorID" id="supervisorSelect">
                                <option value="0" <?= (!isset($employeeDetails->supervisorID) || $employeeDetails->supervisorID == 0 || $employeeDetails->supervisorID == '' || $employeeDetails->supervisorID == null) ? 'selected' : '' ?>>
                                    <i class="fas fa-crown"></i> No Supervisor (Reports to Board/External)
                                </option>
                                <option value="" disabled class="text-muted">──────────────────</option>
                                <?php
                                // Get all users who can be supervisors
                                $allUsers = Employee::employees(['orgDataID' => $employeeDetails->orgDataID, 'entityID' => $employeeDetails->entityID], false  , $DBConn);
                                if ($allUsers):
                                    foreach($allUsers as $user):
                                        if ($user->ID != $employeeID): // Don't show current employee
                                ?>
                                <option value="<?= $user->ID ?>" <?= (isset($employeeDetails->supervisorID) && $employeeDetails->supervisorID == $user->ID) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($user->FirstName . ' ' . $user->Surname) ?>
                                    <?php if (isset($user->jobTitle) && !empty($user->jobTitle)): ?>
                                        - <small class="text-muted"><?= htmlspecialchars($user->jobTitle) ?></small>
                                    <?php endif; ?>
                                </option>
                                <?php
                                        endif;
                                    endforeach;
                                endif;
                                ?>
                            </select>
                            <small class="form-text text-muted d-block mt-1">
                                <i class="fas fa-info-circle me-1"></i>
                                Select "No Supervisor" for top-level positions (CEO, Board members, etc.) who report to external parties or board of directors
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employment Dates -->
            <div class="col-md-12">
                <div class="info-card">
                    <h6 class="fw-bold mb-3 text-primary">Employment Dates</h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">Employment Start Date</label>
                            <input type="text" class="form-control form-control-sm" name="dateOfEmployment"
                                   value="<?= $employeeDetails->employmentStartDate ?? '' ?>"
                                   placeholder="Select employment start date" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">Employment End Date</label>
                            <input type="text" class="form-control form-control-sm" name="dateOfTermination"
                                   value="<?= $employeeDetails->employmentEndDate ?? '' ?>"
                                   placeholder="Select end date (if applicable)" readonly>
                            <small class="text-muted">Leave empty for active employees</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">Contract Start Date</label>
                            <input type="text" class="form-control form-control-sm" name="contractStartDate"
                                   value="<?= $employeeDetails->contractStartDate ?? '' ?>"
                                   placeholder="Select contract start date" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">Contract End Date</label>
                            <input type="text" class="form-control form-control-sm" name="contractEndDate"
                                   value="<?= $employeeDetails->contractEndDate ?? '' ?>"
                                   placeholder="Select contract end date" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Work Schedule -->
            <div class="col-md-12">
                <div class="info-card">
                    <h6 class="fw-bold mb-3 text-primary">Work Schedule</h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">Daily Work Hours</label>
                            <input type="number" class="form-control form-control-sm" name="dailyWorkHours"
                                   value="<?= $employeeDetails->dailyHours ?? '8' ?>"
                                   min="1" max="24" step="0.5">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">Weekly Work Days</label>
                            <input type="number" class="form-control form-control-sm" name="weekWorkDays"
                                   value="<?= $employeeDetails->weekWorkDays ?? '5' ?>"
                                   min="1" max="7" step="1">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">Work Hour Rounding</label>
                            <select class="form-control form-control-sm" name="workHourRounding">
                                <option value="">Select Rounding</option>
                                <option value="1" <?= ($employeeDetails->workHourRoundingID == 1) ? 'selected' : '' ?>>No Rounding</option>
                                <option value="2" <?= ($employeeDetails->workHourRoundingID == 2) ? 'selected' : '' ?>>15 Minutes</option>
                                <option value="3" <?= ($employeeDetails->workHourRoundingID == 3) ? 'selected' : '' ?>>30 Minutes</option>
                                <option value="4" <?= ($employeeDetails->workHourRoundingID == 4) ? 'selected' : '' ?>>1 Hour</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-4">
            <button type="submit" class="btn btn-success">
                <i class="ri-save-line me-1"></i> Save Employment Details
            </button>
            <button type="button" class="btn btn-secondary ms-2" id="cancelEmploymentEditBtn2">
                <i class="ri-close-line me-1"></i> Cancel
            </button>
        </div>
    </form>
</div>
<?php endif; ?>
<!-- END EDIT MODE -->

<!-- Job History Modal -->
<div class="modal fade" id="jobHistoryModal" tabindex="-1" aria-labelledby="jobHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="jobHistoryModalTitle">Add Job History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="jobHistoryForm">
                <div class="modal-body">
                    <input type="hidden" id="jobHistoryID" name="jobHistoryID">
                    <input type="hidden" name="action" value="save">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Job Title <span class="text-danger">*</span></label>
                            <select class="form-control" id="jhJobTitleID" name="jobTitleID" required>
                                <option value="">Select Job Title</option>
                                <?php if (!empty($jobTitles)): foreach($jobTitles as $title): ?>
                                <option value="<?= $title->jobTitleID ?>">
                                    <?= htmlspecialchars($title->jobTitle) ?>
                                </option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department <span class="text-danger">*</span></label>
                            <select class="form-control" id="jhDepartmentID" name="departmentID" required>
                                <option value="">Select Department</option>
                                <?php if (!empty($departments)): foreach($departments as $dept): ?>
                                <option value="<?= $dept->departmentID ?>">
                                    <?= htmlspecialchars($dept->departmentName) ?>
                                </option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="jhStartDate" name="startDate"
                                   placeholder="Select start date" required readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="text" class="form-control" id="jhEndDate" name="endDate"
                                   placeholder="Select end date (leave empty if current)" readonly>
                            <small class="text-muted">Leave empty if current position</small>
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="jhIsCurrent" name="isCurrent" value="Y">
                                <label class="form-check-label" for="jhIsCurrent">
                                    This is my current position
                                </label>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Responsibilities</label>
                            <textarea class="form-control" id="jhResponsibilities" name="responsibilities" rows="3"
                                      placeholder="Describe your key responsibilities in this role..."></textarea>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Achievements</label>
                            <textarea class="form-control" id="jhAchievements" name="achievements" rows="3"
                                      placeholder="Highlight your major achievements in this role..."></textarea>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Reason for Change</label>
                            <input type="text" class="form-control" id="jhChangeReason" name="changeReason"
                                   placeholder="e.g., Promotion, Transfer, Restructuring">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="jobHistorySubmitBtn">
                        <i class="ri-save-line me-1"></i> Add Job History
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- END Job History Modal -->

<!-- JavaScript for Employment Inline Edit -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date pickers
    initializeEmploymentDatePickers();
    initializeJobHistoryDatePickers();

    // Edit button click
    const editEmploymentBtn = document.getElementById('editEmploymentBtn');
    if (editEmploymentBtn) {
        editEmploymentBtn.addEventListener('click', function(e) {
            e.preventDefault();
            toggleEmploymentEdit(true);
            // Reinitialize date pickers when edit mode opens
            setTimeout(initializeEmploymentDatePickers, 100);
        });
    }

    // Cancel buttons
    const cancelBtn1 = document.getElementById('cancelEmploymentEditBtn');
    const cancelBtn2 = document.getElementById('cancelEmploymentEditBtn2');

    [cancelBtn1, cancelBtn2].forEach(btn => {
        if (btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                toggleEmploymentEdit(false);
            });
        }
    });

    // Form submission
    const employmentForm = document.getElementById('employmentEditForm');
    if (employmentForm) {
        employmentForm.addEventListener('submit', function(e) {
            // Validate employment dates
            if (!validateEmploymentDates()) {
                e.preventDefault();
                return false;
            }

            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i> Saving...';
            }

            // Show saving toast
            if (typeof showToast === 'function') {
                showToast('Saving employment details...', 'info');
            }
        });
    }
});

// Initialize employment date pickers
function initializeEmploymentDatePickers() {
    if (typeof flatpickr === 'undefined') return;

    // Employment Start Date
    const employmentStartInput = document.querySelector('input[name="dateOfEmployment"]');
    if (employmentStartInput && !employmentStartInput._flatpickr) {
        flatpickr(employmentStartInput, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'F j, Y',
            defaultDate: employmentStartInput.value || null,
            disableMobile: true,
            allowInput: false,
            clickOpens: true,
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    instance.element.classList.add('is-valid');

                    // Update employment end date minimum
                    const endInput = document.querySelector('input[name="dateOfTermination"]');
                    if (endInput && endInput._flatpickr) {
                        endInput._flatpickr.set('minDate', dateStr);
                    }
                }
            }
        });
    }

    // Employment End Date
    const employmentEndInput = document.querySelector('input[name="dateOfTermination"]');
    if (employmentEndInput && !employmentEndInput._flatpickr) {
        const minDate = employmentStartInput && employmentStartInput.value ? employmentStartInput.value : null;

        flatpickr(employmentEndInput, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'F j, Y',
            minDate: minDate,
            defaultDate: employmentEndInput.value || null,
            disableMobile: true,
            allowInput: true,  // Allow clearing
            clickOpens: true,
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    instance.element.classList.add('is-valid');
                }
            }
        });
    }

    // Contract Start Date
    const contractStartInput = document.querySelector('input[name="contractStartDate"]');
    if (contractStartInput && !contractStartInput._flatpickr) {
        flatpickr(contractStartInput, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'F j, Y',
            defaultDate: contractStartInput.value || null,
            disableMobile: true,
            allowInput: true,
            clickOpens: true,
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    instance.element.classList.add('is-valid');
                    instance.element.classList.remove('is-invalid');

                    // Update contract end date minimum
                    const contractEndInput = document.querySelector('input[name="contractEndDate"]');
                    if (contractEndInput && contractEndInput._flatpickr) {
                        contractEndInput._flatpickr.set('minDate', dateStr);
                    }
                }
            }
        });
    }

    // Contract End Date
    const contractEndInput = document.querySelector('input[name="contractEndDate"]');
    if (contractEndInput && !contractEndInput._flatpickr) {
        const contractMinDate = contractStartInput && contractStartInput.value ? contractStartInput.value : null;

        flatpickr(contractEndInput, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'F j, Y',
            minDate: contractMinDate,
            defaultDate: contractEndInput.value || null,
            disableMobile: true,
            allowInput: true,
            clickOpens: true,
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    instance.element.classList.add('is-valid');
                    instance.element.classList.remove('is-invalid');
                }
            }
        });
    }
}

// Initialize job history date pickers
function initializeJobHistoryDatePickers() {
    if (typeof flatpickr === 'undefined') return;

    // Job History Start Date
    const jhStartDateInput = document.getElementById('jhStartDate');
    if (jhStartDateInput && !jhStartDateInput._flatpickr) {
        flatpickr(jhStartDateInput, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'F j, Y',
            defaultDate: jhStartDateInput.value || null,
            disableMobile: true,
            allowInput: false,
            clickOpens: true,
            maxDate: 'today', // Start date cannot be in the future
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    instance.element.classList.add('is-valid');
                    instance.element.classList.remove('is-invalid');

                    // Update job history end date minimum
                    const jhEndDateInput = document.getElementById('jhEndDate');
                    if (jhEndDateInput && jhEndDateInput._flatpickr) {
                        jhEndDateInput._flatpickr.set('minDate', dateStr);
                    }
                }
            }
        });
    }

    // Job History End Date
    const jhEndDateInput = document.getElementById('jhEndDate');
    if (jhEndDateInput && !jhEndDateInput._flatpickr) {
        const jhMinDate = jhStartDateInput && jhStartDateInput.value ? jhStartDateInput.value : null;

        flatpickr(jhEndDateInput, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'F j, Y',
            minDate: jhMinDate,
            maxDate: 'today', // End date cannot be in the future
            defaultDate: jhEndDateInput.value || null,
            disableMobile: true,
            allowInput: true, // Allow clearing for current positions
            clickOpens: true,
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    instance.element.classList.add('is-valid');
                    instance.element.classList.remove('is-invalid');

                    // Validate that end date is after start date
                    const startValue = jhStartDateInput.value;
                    if (startValue && dateStr < startValue) {
                        instance.element.classList.add('is-invalid');
                        instance.element.classList.remove('is-valid');
                        if (typeof showToast === 'function') {
                            showToast('End date must be after start date', 'warning');
                        }
                    }
                } else {
                    // Clear end date - this is allowed for current positions
                    instance.element.classList.remove('is-invalid');
                    instance.element.classList.remove('is-valid');
                }
            }
        });

        // Update job history end date minDate when start date changes
        if (jhStartDateInput && jhStartDateInput._flatpickr) {
            jhStartDateInput._flatpickr.config.onChange.push(function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0 && jhEndDateInput._flatpickr) {
                    jhEndDateInput._flatpickr.set('minDate', dateStr);
                }
            });
        }
    }
}

// Validate employment dates
function validateEmploymentDates() {
    const startInput = document.querySelector('input[name="dateOfEmployment"]');
    const endInput = document.querySelector('input[name="dateOfTermination"]');

    // Only validate if both dates are provided
    if (startInput && endInput && startInput.value && endInput.value) {
        const startDate = new Date(startInput.value);
        const endDate = new Date(endInput.value);

        if (endDate <= startDate) {
            endInput.classList.add('is-invalid');
            endInput.classList.remove('is-valid');

            if (typeof showToast === 'function') {
                showToast('Employment end date must be after start date', 'danger');
            } else {
                alert('Employment end date must be after start date');
            }
            return false;
        } else {
            endInput.classList.remove('is-invalid');
            endInput.classList.add('is-valid');
        }
    }

    return true;
}

// Toggle between view and edit modes
function toggleEmploymentEdit(enableEdit) {
    const viewMode = document.getElementById('employmentViewMode');
    const editMode = document.getElementById('employmentEditMode');

    if (!viewMode || !editMode) return;

    if (enableEdit) {
        viewMode.classList.add('d-none');
        editMode.classList.remove('d-none');

        editMode.style.opacity = '0';
        setTimeout(() => {
            editMode.style.transition = 'opacity 0.3s ease-in';
            editMode.style.opacity = '1';
        }, 10);

        editMode.scrollIntoView({ behavior: 'smooth', block: 'start' });
        console.log('✓ Employment edit mode activated');
    } else {
        editMode.classList.add('d-none');
        viewMode.classList.remove('d-none');

        viewMode.style.opacity = '0';
        setTimeout(() => {
            viewMode.style.transition = 'opacity 0.3s ease-in';
            viewMode.style.opacity = '1';
        }, 10);

        console.log('✓ Employment view mode activated');
    }
}

// Job History Management Functions
function addJobHistory() {
    // Reset form
    document.getElementById('jobHistoryForm').reset();
    document.getElementById('jobHistoryID').value = '';
    document.getElementById('jobHistoryModalTitle').textContent = 'Add Job History';
    document.getElementById('jobHistorySubmitBtn').textContent = 'Add Job History';
    document.getElementById('jhIsCurrent').checked = false;

    // Reset date pickers
    const jhStartDateInput = document.getElementById('jhStartDate');
    const jhEndDateInput = document.getElementById('jhEndDate');
    if (jhStartDateInput && jhStartDateInput._flatpickr) {
        jhStartDateInput._flatpickr.clear();
    }
    if (jhEndDateInput && jhEndDateInput._flatpickr) {
        jhEndDateInput._flatpickr.clear();
    }

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('jobHistoryModal'));
    modal.show();

    // Initialize date pickers after modal is shown
    setTimeout(() => {
        initializeJobHistoryDatePickers();
    }, 300);
}

function editJobHistory(id) {
    // Fetch job history data
    fetch('<?= $base ?>php/scripts/global/admin/manage_job_history.php?action=get&id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const history = data.data;

                console.log('Full history object:', history);

                // Populate form fields
                document.getElementById('jobHistoryID').value = history.jobHistoryID || '';
                document.getElementById('jhJobTitleID').value = history.jobTitleID || '';
                document.getElementById('jhDepartmentID').value = history.departmentID || '';
                document.getElementById('jhIsCurrent').checked = (history.isCurrent === 'Y');
                document.getElementById('jhResponsibilities').value = history.responsibilities || '';
                document.getElementById('jhAchievements').value = history.achievements || '';
                document.getElementById('jhChangeReason').value = history.changeReason || '';

                // Format and set dates - with extra debugging
                const startDateInput = document.getElementById('jhStartDate');
                const endDateInput = document.getElementById('jhEndDate');

                console.log('Start date input element:', startDateInput);
                console.log('End date input element:', endDateInput);

                if (history.startDate) {
                    const startDate = history.startDate.split(' ')[0]; // Remove time if present
                    console.log('Setting start date to:', startDate);
                    startDateInput.value = startDate;
                    console.log('Start date input value after setting:', startDateInput.value);
                }

                if (history.endDate && history.endDate !== '0000-00-00' && history.endDate !== null) {
                    const endDate = history.endDate.split(' ')[0]; // Remove time if present
                    console.log('Setting end date to:', endDate);
                    endDateInput.value = endDate;
                    console.log('End date input value after setting:', endDateInput.value);
                } else {
                    endDateInput.value = '';
                }

                // Update modal title
                document.getElementById('jobHistoryModalTitle').textContent = 'Edit Job History';
                document.getElementById('jobHistorySubmitBtn').textContent = 'Update Job History';

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('jobHistoryModal'));
                modal.show();

                // Initialize date pickers and set dates after modal is shown
                setTimeout(() => {
                    initializeJobHistoryDatePickers();

                    // Set dates using flatpickr API
                    const jhStartDateInput = document.getElementById('jhStartDate');
                    const jhEndDateInput = document.getElementById('jhEndDate');

                    if (history.startDate && jhStartDateInput && jhStartDateInput._flatpickr) {
                        const startDate = history.startDate.split(' ')[0];
                        jhStartDateInput._flatpickr.setDate(startDate, false);
                        console.log('Set start date via flatpickr:', startDate);
                    }

                    if (history.endDate && history.endDate !== '0000-00-00' && history.endDate !== null) {
                        if (jhEndDateInput && jhEndDateInput._flatpickr) {
                            const endDate = history.endDate.split(' ')[0];
                            jhEndDateInput._flatpickr.setDate(endDate, false);
                            console.log('Set end date via flatpickr:', endDate);
                        }
                    } else {
                        // Clear end date if it's a current position
                        if (jhEndDateInput && jhEndDateInput._flatpickr) {
                            jhEndDateInput._flatpickr.clear();
                        }
                    }
                }, 300);
            } else {
                showToast(data.message || 'Failed to load job history', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error loading job history', 'danger');
        });
}

function deleteJobHistory(id) {
    if (confirm('Are you sure you want to delete this job history record? This action cannot be undone.')) {
        fetch('<?= $base ?>php/scripts/global/admin/manage_job_history.php?action=delete&id=' + id, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Job history deleted successfully', 'success');
                // Reload page after short delay
                setTimeout(() => {
                    window.location.href = '?s=<?= $s ?>&p=<?= $p ?>&uid=<?= $employeeID ?>&tab=employment';
                }, 1500);
            } else {
                showToast(data.message || 'Failed to delete job history', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error deleting job history', 'danger');
        });
    }
}

// Handle job history form submission
document.addEventListener('DOMContentLoaded', function() {
    const jobHistoryForm = document.getElementById('jobHistoryForm');
    if (jobHistoryForm) {
        jobHistoryForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate dates before submission
            const jhStartDateInput = document.getElementById('jhStartDate');
            const jhEndDateInput = document.getElementById('jhEndDate');
            const isCurrent = document.getElementById('jhIsCurrent').checked;

            // Validate start date
            if (!jhStartDateInput.value || !jhStartDateInput._flatpickr || !jhStartDateInput._flatpickr.selectedDates.length) {
                jhStartDateInput.classList.add('is-invalid');
                showToast('Please select a start date', 'warning');
                return;
            }

            // Validate end date if not current position
            if (!isCurrent && jhEndDateInput.value && jhEndDateInput._flatpickr && jhEndDateInput._flatpickr.selectedDates.length) {
                const startDate = new Date(jhStartDateInput.value);
                const endDate = new Date(jhEndDateInput.value);

                if (endDate <= startDate) {
                    jhEndDateInput.classList.add('is-invalid');
                    showToast('End date must be after start date', 'warning');
                    return;
                }
            }

            // If current position, clear end date
            if (isCurrent && jhEndDateInput._flatpickr) {
                jhEndDateInput._flatpickr.clear();
            }

            const formData = new FormData(this);
            formData.append('employeeID', '<?= $employeeID ?>');

            const submitBtn = document.getElementById('jobHistorySubmitBtn');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i> Saving...';

            fetch('<?= $base ?>php/scripts/global/admin/manage_job_history.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message || 'Job history saved successfully', 'success');

                    // Close modal
                    const modalElement = document.getElementById('jobHistoryModal');
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    modal.hide();

                    // Reload page after short delay
                    setTimeout(() => {
                        window.location.href = '?s=<?= $s ?>&p=<?= $p ?>&uid=<?= $employeeID ?>&tab=employment';
                    }, 1500);
                } else {
                    showToast(data.message || 'Failed to save job history', 'danger');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error saving job history', 'danger');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        });

        // Handle "is current" checkbox change
        const jhIsCurrentCheckbox = document.getElementById('jhIsCurrent');
        if (jhIsCurrentCheckbox) {
            jhIsCurrentCheckbox.addEventListener('change', function() {
                const jhEndDateInput = document.getElementById('jhEndDate');
                if (this.checked && jhEndDateInput && jhEndDateInput._flatpickr) {
                    // Clear end date if marked as current
                    jhEndDateInput._flatpickr.clear();
                    jhEndDateInput.classList.remove('is-invalid');
                }
            });
        }
    }

    // Initialize date pickers when job history modal is shown
    const jobHistoryModal = document.getElementById('jobHistoryModal');
    if (jobHistoryModal) {
        jobHistoryModal.addEventListener('shown.bs.modal', function() {
            // Small delay to ensure modal is fully rendered
            setTimeout(() => {
                initializeJobHistoryDatePickers();
            }, 100);
        });
    }
});

// ============================================================================
// Unit Assignment Management Functions
// ============================================================================

// Add unit assignment
function addUnitAssignment() {
    const unitSelect = document.getElementById('newUnitID');
    const unitID = unitSelect.value;

    if (!unitID) {
        showToast('Please select a unit to assign', 'warning');
        return;
    }

    const selectedOption = unitSelect.options[unitSelect.selectedIndex];
    const unitTypeID = selectedOption.getAttribute('data-type');

    // Prepare data
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('userID', '<?= $employeeID ?>');
    formData.append('unitID', unitID);
    formData.append('unitTypeID', unitTypeID);
    formData.append('orgDataID', '<?= $employeeDetails->orgDataID ?? '' ?>');
    formData.append('entityID', '<?= $employeeDetails->entityID ?? '' ?>');
    formData.append('assignmentStartDate', new Date().toISOString().split('T')[0]);

    // Send to backend
    fetch('<?= $base ?>php/scripts/global/admin/manage_unit_assignment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Unit assigned successfully', 'success');
            // Reload page to show new assignment
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast(data.message || 'Failed to assign unit', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error assigning unit', 'danger');
    });
}

// Remove unit assignment
function removeUnitAssignment(assignmentID) {
    if (!confirm('Remove this unit assignment?')) {
        return;
    }

    // Prepare data
    const formData = new FormData();
    formData.append('action', 'remove');
    formData.append('unitAssignmentID', assignmentID);
    formData.append('userID', '<?= $employeeID ?>');

    // Send to backend
    fetch('<?= $base ?>php/scripts/global/admin/manage_unit_assignment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Unit assignment removed', 'success');
            // Reload page to show updated assignments
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast(data.message || 'Failed to remove assignment', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error removing assignment', 'danger');
    });
}

// Toast function (if not already defined globally)
if (typeof showToast === 'undefined') {
    function showToast(message, type = 'info') {
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }

        const iconMap = {
            'danger': 'ri-error-warning-line',
            'warning': 'ri-alert-line',
            'success': 'ri-checkbox-circle-line',
            'info': 'ri-information-line'
        };

        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="${iconMap[type]} me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        toastContainer.appendChild(toast);

        const bsToast = new bootstrap.Toast(toast, {
            autohide: true,
            delay: 5000
        });
        bsToast.show();

        toast.addEventListener('hidden.bs.toast', function() {
            toast.remove();
        });
    }
}
</script>

<!-- Styling -->
<style>
/* Tab Header Styling */
.tab-header {
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 15px;
    margin-bottom: 25px;
}

.tab-header h5 {
    color: #007bff;
    font-weight: 600;
}

.tab-header .text-muted {
    color: #6c757d !important;
    font-size: 0.875rem;
}

/* Info Cards */
.info-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
}

.data-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.data-row:last-child {
    border-bottom: none;
}

.data-label {
    font-weight: 600;
    color: #495057;
    min-width: 180px;
}

.data-value {
    color: #6c757d;
    text-align: right;
    flex: 1;
}

/* Timeline Styling */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 30px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #007bff;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #007bff;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: -25px;
    top: 12px;
    width: 2px;
    height: calc(100% + 18px);
    background: #e9ecef;
}

.timeline-item:last-child:before {
    display: none;
}

.timeline-content {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
}

/* Edit Mode Styling */
#employmentEditMode .form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.15);
}

#employmentEditMode .info-card {
    background: #f8f9fa;
    border-left: 4px solid #007bff;
}

#employmentEditMode .tab-header {
    border-bottom-color: #007bff;
}

/* Date Input Styling */
input[name="dateOfEmployment"],
input[name="dateOfTermination"],
input[name="contractStartDate"],
input[name="contractEndDate"],
#jhStartDate,
#jhEndDate {
    cursor: pointer;
    background-color: #fff !important;
}

/* Job History Date Inputs */
#jhStartDate,
#jhEndDate {
    cursor: pointer;
    background-color: #fff !important;
}

/* Validation States */
.is-valid {
    border-color: #28a745 !important;
}

.is-invalid {
    border-color: #dc3545 !important;
}

/* Unit Assignment Styles */
.unit-assignment-item {
    background: #f8f9fa;
    transition: all 0.2s ease;
}

.unit-assignment-item:hover {
    background: #e9ecef;
    transform: translateX(5px);
}

#currentUnitAssignments .alert {
    font-size: 0.875rem;
}

#newUnitID optgroup {
    font-weight: bold;
    font-style: normal;
}

#newUnitID option {
    font-weight: normal;
    padding-left: 10px;
}
</style>
