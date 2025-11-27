<?php
/**
 * Apply Leave View
 *
 * Comprehensive leave application form with multi-level approval workflow,
 * project clearance requirements, and global holiday integration
 */

// Get current leave period
$currentLeavePeriod = Leave::get_current_leave_period($entityID, $DBConn);
$leaveAccumulationPolicy = Leave::get_leave_accumulation_policy($entityID, $DBConn);
?>

<!-- Apply Leave View Container -->
<div class="row" id="applyLeaveViewRoot">
    <!-- View Toggle -->
    <div class="col-12 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="ri-calendar-add-line me-2 text-primary"></i>
                            Apply for Leave
                        </h5>
                        <small class="text-muted">Choose your preferred view to apply for leave</small>
                    </div>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary active" id="formViewBtn">
                            <i class="ri-file-edit-line me-1"></i> Form View
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="calendarViewBtn">
                            <i class="ri-calendar-line me-1"></i> Calendar View
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form View -->
    <div class="col-xl-8 col-lg-12" id="formViewContainer">
        <!-- Leave Application Form -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom-0">
                <h5 class="mb-0">
                    <i class="ri-file-edit-line me-2 text-primary"></i>
                    Leave Application Form
                </h5>
            </div>
            <div class="card-body">
                <form id="leaveApplicationForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="submit_leave_application">
                    <input type="hidden" name="leaveTypeId" id="leaveTypeId">
                    <input type="hidden" name="leaveEntitlementId" id="leaveEntitlementId">
                    <input type="hidden" name="employeeId" value="<?= $employeeDetails->ID ?>">
                    <input type="hidden" name="orgDataId" value="<?= $orgDataID ?>">
                    <input type="hidden" name="entityId" value="<?= $entityID ?>">
                    <input type="hidden" name="leavePeriodId" value="<?= $currentLeavePeriod->leavePeriodID ?? '' ?>">
                    <input type="hidden" name="totalDays" id="totalDays" value="0">

                    <!-- Form Alert Container -->
                    <div id="formAlertContainer" class="alert-container mb-3" style="display: none;"></div>

                    <!-- Step 1: Leave Type Selection -->
                    <div class="form-step active" id="step1">
                        <div class="step-header mb-4">
                            <div class="d-flex align-items-center">
                                <div class="step-number bg-primary text-white rounded-circle me-3">1</div>
                                <div>
                                    <h6 class="mb-0">Select Leave Type</h6>
                                    <small class="text-muted">Choose the type of leave you want to apply for</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <?php if ($leaveEntitlements): ?>
                                <?php foreach ($leaveEntitlements as $entitlement): ?>
                                    <?php if ($entitlement->leaveSegment && strtolower($entitlement->leaveSegment) != strtolower($employeeDetails->gender)) continue; ?>

                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="leave-type-card" data-leave-type-id="<?= $entitlement->leaveTypeID ?>" data-entitlement-id="<?= $entitlement->leaveEntitlementID ?>">
                                            <div class="leave-type-icon mb-3">
                                                <?php
                                                $iconClass = 'ri-calendar-line';
                                                $iconColor = 'text-primary';

                                                switch(strtolower($entitlement->leaveTypeName)) {
                                                    case 'annual':
                                                    case 'vacation':
                                                        $iconClass = 'ri-calendar-check-line';
                                                        $iconColor = 'text-primary';
                                                        break;
                                                    case 'sick':
                                                    case 'medical':
                                                        $iconClass = 'ri-heart-pulse-line';
                                                        $iconColor = 'text-success';
                                                        break;
                                                    case 'maternity':
                                                        $iconClass = 'ri-parent-line';
                                                        $iconColor = 'text-info';
                                                        break;
                                                    case 'paternity':
                                                        $iconClass = 'ri-user-heart-line';
                                                        $iconColor = 'text-warning';
                                                        break;
                                                    case 'emergency':
                                                        $iconClass = 'ri-alarm-warning-line';
                                                        $iconColor = 'text-danger';
                                                        break;
                                                }
                                                ?>
                                                <i class="<?= $iconClass ?> fs-2 <?= $iconColor ?>"></i>
                                            </div>

                                            <h6 class="leave-type-name fw-semibold mb-2">
                                                <?= htmlspecialchars($entitlement->leaveTypeName) ?>
                                            </h6>

                                            <div class="leave-balance-info mb-3">
                                                <div class="available-balance">
                                                    <span class="balance-days fs-4 fw-bold text-primary">
                                                        <?= $entitlement->entitlement ?>
                                                    </span>
                                                    <span class="balance-label text-muted d-block">days available</span>
                                                </div>

                                                <div class="balance-details mt-2">
                                                    <small class="text-muted">
                                                        <i class="ri-information-line me-1"></i>
                                                        <?= $entitlement->totalEntitlement ?? $entitlement->entitlement ?> total per year
                                                    </small>
                                                </div>
                                            </div>

                                            <!-- Leave Type Description Information -->
                                            <?php if (!empty($entitlement->leaveTypeDescription)): ?>
                                                <div class="policy-info">
                                                    <small class="text-muted">
                                                        <i class="ri-file-text-line me-1"></i>
                                                        <?= htmlspecialchars($entitlement->leaveTypeDescription) ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Accumulation Info -->
                                            <?php if ($leaveAccumulationPolicy && $leaveAccumulationPolicy->accrualType == 'Monthly'): ?>
                                                <div class="accumulation-info mt-2">
                                                    <small class="text-success">
                                                        <i class="ri-add-circle-line me-1"></i>
                                                        Accumulates <?= $leaveAccumulationPolicy->accrualRate ?> days/month
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <div class="alert alert-info text-center">
                                        <i class="ri-information-line fs-3 mb-2"></i>
                                        <h6>No Leave Entitlements Available</h6>
                                        <p class="mb-0">Contact HR to set up your leave entitlements.</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="step-actions">
                            <button type="button" class="btn btn-primary" data-action="workflow-next-step" data-step="2" disabled id="step1NextBtn">
                                Next <i class="ri-arrow-right-line ms-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Date Selection -->
                    <div class="form-step" id="step2">
                        <div class="step-header mb-4">
                            <div class="d-flex align-items-center">
                                <div class="step-number bg-primary text-white rounded-circle me-3">2</div>
                                <div>
                                    <h6 class="mb-0">Select Leave Dates</h6>
                                    <small class="text-muted">Choose your leave start and end dates</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="startDate" class="form-label required">Start Date</label>
                                    <input type="date"
                                           class="form-control"
                                           id="startDate"
                                           name="startDate"
                                           required
                                           min="<?= date('Y-m-d') ?>">
                                    <div class="form-text">Leave cannot start before today</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="endDate" class="form-label required">End Date</label>
                                    <input type="date"
                                           class="form-control"
                                           id="endDate"
                                           name="endDate"
                                           required>
                                    <div class="form-text">Leave end date</div>
                                </div>
                            </div>
                        </div>

                        <!-- Date Information -->
                        <div class="date-info-card bg-light p-3 rounded mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="info-item text-center">
                                        <div class="info-value fs-4 fw-bold text-primary" id="totalDaysDisplay">0</div>
                                        <div class="info-label text-muted">Working Days</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-item text-center">
                                        <div class="info-value fs-4 fw-bold text-warning" id="weekendDaysDisplay">0</div>
                                        <div class="info-label text-muted">Weekend Days</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-item text-center">
                                        <div class="info-value fs-4 fw-bold text-info" id="holidayDaysDisplay">0</div>
                                        <div class="info-label text-muted">Holiday Days</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Holiday Information -->
                        <div id="holidayInfo" class="alert alert-warning" style="display: none;">
                            <h6><i class="ri-calendar-event-line me-2"></i>Holidays During Leave Period</h6>
                            <div id="holidayList"></div>
                        </div>

                        <div class="step-actions">
                            <button type="button" class="btn btn-outline-secondary" data-action="workflow-prev-step" data-step="1">
                                <i class="ri-arrow-left-line me-1"></i> Previous
                            </button>
                            <button type="button" class="btn btn-primary" data-action="workflow-next-step" data-step="3" disabled id="step2NextBtn">
                                Next <i class="ri-arrow-right-line ms-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Additional Information -->
                    <div class="form-step" id="step3">
                        <div class="step-header mb-4">
                            <div class="d-flex align-items-center">
                                <div class="step-number bg-primary text-white rounded-circle me-3">3</div>
                                <div>
                                    <h6 class="mb-0">Additional Information</h6>
                                    <small class="text-muted">Provide details and supporting documents</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="leaveReason" class="form-label required">Reason for Leave</label>
                            <textarea class="form-control"
                                      id="leaveReason"
                                      name="leaveReason"
                                      rows="3"
                                      placeholder="Please provide a brief reason for your leave request..."
                                      required></textarea>
                            <div class="form-text">This information will be visible to approvers</div>
                        </div>

                        <div class="mb-3">
                            <label for="emergencyContact" class="form-label">Emergency Contact</label>
                            <input type="text"
                                   class="form-control"
                                   id="emergencyContact"
                                   name="emergencyContact"
                                   placeholder="Name and phone number of emergency contact">
                            <div class="form-text">Optional: Contact person during your absence</div>
                        </div>

                        <div class="mb-3">
                            <label for="handoverNotes" class="form-label">Handover Notes</label>
                            <textarea class="form-control"
                                      id="handoverNotes"
                                      name="handoverNotes"
                                      rows="3"
                                      placeholder="Any important notes for your colleagues during your absence..."></textarea>
                            <div class="form-text">Optional: Important information for your team</div>
                        </div>

                        <!-- File Upload -->
                        <div class="mb-3">
                            <label class="form-label">Supporting Documents</label>
                            <div class="file-upload-area" id="fileUploadArea">
                                <div class="upload-content">
                                    <i class="ri-upload-cloud-line fs-1 text-muted mb-3"></i>
                                    <h6 class="text-muted">Drop files here or click to upload</h6>
                                    <p class="text-muted small mb-0">
                                        Supported formats: PDF, DOC, DOCX, JPG, PNG (Max 10MB each)
                                    </p>
                                </div>
                                <input type="file"
                                       id="leaveFiles"
                                       name="leaveFiles[]"
                                       multiple
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                       style="display: none;">
                            </div>
                            <div id="fileList" class="mt-3"></div>
                        </div>

                        <div class="step-actions">
                            <button type="button" class="btn btn-outline-secondary" data-action="workflow-prev-step" data-step="2">
                                <i class="ri-arrow-left-line me-1"></i> Previous
                            </button>
                            <button type="button" class="btn btn-primary" data-action="workflow-next-step" data-step="4">
                                Next <i class="ri-arrow-right-line ms-1"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Step 4: Approval Workflow Preview -->
                    <div class="form-step" id="step4">
                        <div class="step-header mb-4">
                            <div class="d-flex align-items-center">
                                <div class="step-number bg-primary text-white rounded-circle me-3">4</div>
                                <div>
                                    <h6 class="mb-0">Approval Workflow</h6>
                                    <small class="text-muted">Review the approval process for your application</small>
                                </div>
                            </div>
                        </div>

                        <!-- Submission Intent -->
                        <div class="mb-4" id="submissionModeContainer">
                            <h6 class="mb-2">
                                <i class="ri-compass-3-line me-2 text-primary"></i>
                                How would you like to handle this leave?
                            </h6>
                            <div class="btn-group" role="group" aria-label="Leave submission mode">
                                <input type="radio" class="btn-check" name="submissionMode" id="submissionModeSchedule" value="schedule">
                                <label class="btn btn-outline-secondary" for="submissionModeSchedule">
                                    <i class="ri-calendar-todo-line me-1"></i>
                                    Schedule Leave
                                </label>

                                <input type="radio" class="btn-check" name="submissionMode" id="submissionModeSubmit" value="submit" checked>
                                <label class="btn btn-outline-primary" for="submissionModeSubmit">
                                    <i class="ri-send-plane-line me-1"></i>
                                    Apply for Approval
                                </label>
                            </div>
                            <div class="mt-2 text-muted small" id="submissionModeHelpText">
                                Applying starts the approval workflow and notifies your approvers immediately.
                            </div>
                        </div>

                        <!-- Approval Workflow -->
                        <div class="approval-workflow mb-4">
                            <div class="approval-step completed">
                                <div class="approval-step-icon">
                                    <i class="ri-user-line"></i>
                                </div>
                                <div class="approval-step-info">
                                    <h6 class="mb-1">Employee</h6>
                                    <small class="text-muted"><?= htmlspecialchars($employeeDetails->FirstName . ' ' . $employeeDetails->Surname) ?></small>
                                </div>
                            </div>

                            <?php if ($directReport): ?>
                                <div class="approval-step">
                                    <div class="approval-step-icon">
                                        <i class="ri-user-star-line"></i>
                                    </div>
                                    <div class="approval-step-info">
                                        <h6 class="mb-1">Direct Report</h6>
                                        <small class="text-muted"><?= htmlspecialchars($directReport->FirstName . ' ' . $directReport->Surname) ?></small>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($departmentHead): ?>
                                <div class="approval-step">
                                    <div class="approval-step-icon">
                                        <i class="ri-building-line"></i>
                                    </div>
                                    <div class="approval-step-info">
                                        <h6 class="mb-1">Department Head</h6>
                                        <small class="text-muted"><?= htmlspecialchars($departmentHead->FirstName . ' ' . $departmentHead->Surname) ?></small>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="approval-step">
                                <div class="approval-step-icon">
                                    <i class="ri-user-settings-line"></i>
                                </div>
                                <div class="approval-step-info">
                                    <h6 class="mb-1">HR Manager</h6>
                                    <small class="text-muted"><?= $hrManager ? htmlspecialchars($hrManager->FirstName . ' ' . $hrManager->Surname) : 'HR Department' ?></small>
                                </div>
                            </div>
                        </div>

                        <!-- Project Clearance Requirements -->
                        <?php if (!empty($employeeProjects)): ?>
                            <div class="project-clearance-section mb-4">
                                <h6 class="mb-3">
                                    <i class="ri-projector-line me-2"></i>
                                    Project Clearance Requirements
                                </h6>

                                <div class="clearance-requirements">
                                    <?php foreach ($employeeProjects as $project): ?>
                                        <div class="project-clearance-item">
                                            <div class="project-info">
                                                <h6 class="mb-1"><?= htmlspecialchars($project->projectName) ?></h6>
                                                <small class="text-muted">
                                                    Project Manager: <?= htmlspecialchars($project->projectManagerName ?? 'TBD') ?>
                                                </small>
                                            </div>
                                            <div class="clearance-status required">
                                                <i class="ri-time-line me-1"></i>
                                                Pending Clearance
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="alert alert-info mt-3">
                                    <i class="ri-information-line me-2"></i>
                                    <strong>Note:</strong> Your application will be automatically sent to project managers for clearance approval before final HR approval.
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Application Summary -->
                        <div class="application-summary bg-light p-3 rounded mb-4">
                            <h6 class="mb-3">Application Summary</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="summary-item mb-2">
                                        <strong>Leave Type:</strong>
                                        <span id="summaryLeaveType">-</span>
                                    </div>
                                    <div class="summary-item mb-2">
                                        <strong>Duration:</strong>
                                        <span id="summaryDuration">-</span>
                                    </div>
                                    <div class="summary-item mb-2">
                                        <strong>Working Days:</strong>
                                        <span id="summaryWorkingDays">-</span>
                                    </div>
                                    <div class="summary-item mb-2">
                                        <strong>Action:</strong>
                                        <span id="summarySubmissionIntent">Schedule leave (kept as a plan)</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="summary-item mb-2">
                                        <strong>Start Date:</strong>
                                        <span id="summaryStartDate">-</span>
                                    </div>
                                    <div class="summary-item mb-2">
                                        <strong>End Date:</strong>
                                        <span id="summaryEndDate">-</span>
                                    </div>
                                    <div class="summary-item mb-2">
                                        <strong>Return Date:</strong>
                                        <span id="summaryReturnDate">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="step-actions">
                            <button type="button" class="btn btn-outline-secondary" data-action="workflow-prev-step" data-step="3">
                                <i class="ri-arrow-left-line me-1"></i> Previous
                            </button>
                            <button type="submit" class="btn btn-outline-primary" id="submitApplicationBtn">
                                <i class="ri-calendar-check-line me-1"></i> Schedule Leave
                            </button>
                        </div>
                    </div>

                    <!-- Hidden Fields -->
                    <input type="hidden" name="leaveTypeId" id="leaveTypeId">
                    <input type="hidden" name="leaveEntitlementId" id="leaveEntitlementId">
                    <input type="hidden" name="employeeId" value="<?= $employeeDetails->ID ?>">
                    <input type="hidden" name="orgDataId" value="<?= $orgDataID ?>">
                    <input type="hidden" name="entityId" value="<?= $entityID ?>">
                    <input type="hidden" name="leavePeriodId" value="<?= $currentLeavePeriod->leavePeriodID ?? '' ?>">
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-xl-4 col-lg-12">
        <!-- Leave Policy Information -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom-0">
                <h6 class="mb-0">
                    <i class="ri-file-text-line me-2 text-primary"></i>
                    Leave Policy
                </h6>
            </div>
            <div class="card-body">
                <div class="policy-info">
                    <div class="policy-item mb-3">
                        <h6 class="mb-2">Advance Notice</h6>
                        <p class="text-muted small mb-0">
                            Annual leave requires minimum 2 weeks advance notice.
                            Emergency leave can be applied with 24 hours notice.
                        </p>
                    </div>

                    <div class="policy-item mb-3">
                        <h6 class="mb-2">Maximum Consecutive Days</h6>
                        <p class="text-muted small mb-0">
                            Maximum 10 consecutive working days without special approval.
                        </p>
                    </div>

                    <div class="policy-item mb-3">
                        <h6 class="mb-2">Blackout Periods</h6>
                        <p class="text-muted small mb-0">
                            December 15-31 and major project deadlines are restricted periods.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Global Holidays -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom-0">
                <h6 class="mb-0">
                    <i class="ri-calendar-event-line me-2 text-primary"></i>
                    Upcoming Holidays
                </h6>
            </div>
            <div class="card-body">
                <div class="holiday-calendar">
                    <?php if (!empty($globalHolidays)): ?>
                        <?php foreach (array_slice($globalHolidays, 0, 5) as $holiday): ?>
                            <div class="holiday-item">
                                <div class="holiday-info">
                                    <div class="holiday-date"><?= date('M d', strtotime($holiday->holidayDate)) ?></div>
                                    <div class="holiday-name"><?= htmlspecialchars($holiday->holidayName) ?></div>
                                </div>
                                <div class="holiday-jurisdiction">
                                    <?= htmlspecialchars($holiday->jurisdiction ?? 'Global') ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="ri-calendar-line fs-3 text-muted mb-2"></i>
                            <p class="text-muted mb-0">No upcoming holidays</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Help & Support -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom-0">
                <h6 class="mb-0">
                    <i class="ri-question-line me-2 text-primary"></i>
                    Need Help?
                </h6>
            </div>
            <div class="card-body">
                <div class="help-links">
                    <a href="#" class="help-link d-flex align-items-center mb-2">
                        <i class="ri-file-text-line me-2"></i>
                        Leave Policy Document
                    </a>
                    <a href="#" class="help-link d-flex align-items-center mb-2">
                        <i class="ri-customer-service-line me-2"></i>
                        Contact HR Support
                    </a>
                    <a href="#" class="help-link d-flex align-items-center mb-2">
                        <i class="ri-question-answer-line me-2"></i>
                        FAQ
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Apply Leave View JavaScript -->
<script src="<?= $base ?>assets/js/src/leave/workflow_controls.js"></script>
<script>
;(function(window, document) {
    const ROOT_ID = 'applyLeaveViewRoot';
    const TOTAL_STEPS = 4;

    const state = {
        currentStep: 1,
        selectedLeaveType: null,
        lastDaysInfo: null
    };

    function getRoot() {
        return document.getElementById(ROOT_ID);
    }

    function qs(selector) {
        const root = getRoot();
        return root ? root.querySelector(selector) : null;
    }

    function qsa(selector) {
        const root = getRoot();
        return root ? Array.from(root.querySelectorAll(selector)) : [];
    }

    function init() {
        if (!getRoot()) {
            return;
        }

        bindWizardNavigation();
        bindLeaveTypeCards();
        bindDateInputs();
        bindFormSubmission();
        bindFileUpload();
        setupSubmissionModeToggle();
        updateSummary();
    }

    function bindWizardNavigation() {
        if (window.leaveUI && typeof window.leaveUI.bindWizardNavigation === 'function') {
            window.leaveUI.bindWizardNavigation({
                root: getRoot(),
                onNext: () => nextStep(),
                onPrev: () => prevStep(),
                onSubmit: () => submitLeaveApplication()
            });
        }

        if (window.leaveUI && typeof window.leaveUI.bindFileRemovalButtons === 'function') {
            window.leaveUI.bindFileRemovalButtons({
                root: getRoot(),
                onRemove: removeFile
            });
        }
    }

    function bindLeaveTypeCards() {
        qsa('.leave-type-card').forEach(card => {
            if (card.dataset.viewBound === 'true') {
                return;
            }
            card.addEventListener('click', () => selectLeaveType(card));
            card.dataset.viewBound = 'true';
        });
    }

    function selectLeaveType(card) {
        qsa('.leave-type-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');

        state.selectedLeaveType = {
            id: card.dataset.leaveTypeId || '',
            entitlementId: card.dataset.entitlementId || '',
            name: (card.querySelector('.leave-type-name')?.textContent || '').trim()
        };

        const leaveTypeField = qs('#leaveTypeId');
        const entitlementField = qs('#leaveEntitlementId');

        if (leaveTypeField) {
            leaveTypeField.value = state.selectedLeaveType.id;
        }

        if (entitlementField) {
            entitlementField.value = state.selectedLeaveType.entitlementId;
        }

        const nextBtn = qs('#step1NextBtn');
        if (nextBtn) {
            nextBtn.disabled = false;
        }

        updateSummary();
    }

    function bindDateInputs() {
        const startDateInput = qs('#startDate');
        const endDateInput = qs('#endDate');
        const today = new Date().toISOString().split('T')[0];

        if (startDateInput) {
            startDateInput.min = today;
            if (startDateInput.dataset.viewBound !== 'true') {
                startDateInput.addEventListener('change', () => {
            validateDates();
            updateSummary();
        });
                startDateInput.dataset.viewBound = 'true';
            }
        }

        if (endDateInput) {
            endDateInput.min = today;
            if (endDateInput.dataset.viewBound !== 'true') {
                endDateInput.addEventListener('change', () => {
            validateDates();
            updateSummary();
        });
                endDateInput.dataset.viewBound = 'true';
            }
        }
    }

    function bindFormSubmission() {
        const form = qs('#leaveApplicationForm');
        if (!form || form.dataset.viewBound === 'true') {
            return;
        }

        form.addEventListener('submit', event => {
            event.preventDefault();
            validateAndSubmitLeaveApplication();
        });

        form.dataset.viewBound = 'true';
    }

    function bindFileUpload() {
        const fileUploadArea = qs('#fileUploadArea');
        const fileInput = qs('#leaveFiles');

        if (!fileUploadArea || !fileInput || fileUploadArea.dataset.viewBound === 'true') {
            return;
}

        fileUploadArea.addEventListener('click', () => fileInput.click());
        fileUploadArea.addEventListener('dragover', event => {
            event.preventDefault();
            fileUploadArea.classList.add('dragover');
        });
        fileUploadArea.addEventListener('dragleave', event => {
            event.preventDefault();
            fileUploadArea.classList.remove('dragover');
        });
        fileUploadArea.addEventListener('drop', event => {
            event.preventDefault();
            fileUploadArea.classList.remove('dragover');
            handleFileUpload(event.dataTransfer.files);
    });

        fileInput.addEventListener('change', () => handleFileUpload(fileInput.files));

        fileUploadArea.dataset.viewBound = 'true';
    }

    function setupSubmissionModeToggle() {
        const scheduleRadio = qs('#submissionModeSchedule');
        const submitRadio = qs('#submissionModeSubmit');
        const helpText = qs('#submissionModeHelpText');

        const updateHelpText = () => {
            if (!helpText) {
                return;
            }

            if (submitRadio && submitRadio.checked) {
                helpText.textContent = 'Submit immediately to start the approval workflow.';
            } else {
                helpText.textContent = 'Schedule your request. It stays in draft until you submit it.';
            }
        };

        [scheduleRadio, submitRadio].forEach(radio => {
            if (!radio || radio.dataset.viewBound === 'true') {
                return;
            }
            radio.addEventListener('change', updateHelpText);
            radio.dataset.viewBound = 'true';
        });

        updateHelpText();
    }

    function handleFileUpload(files) {
        const fileList = qs('#fileList');
        const fileInput = qs('#leaveFiles');

        if (!fileList || !files) {
            return;
        }

        fileList.innerHTML = '';

        Array.from(files).forEach(file => {
            if (file.size > 10 * 1024 * 1024) {
                showFormAlert('error', `File ${file.name} is too large. Maximum size is 10MB.`);
                return;
            }

            const fileItem = document.createElement('div');
            fileItem.className = 'file-item d-flex justify-content-between align-items-center p-2 border rounded mb-2';
            fileItem.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="ri-file-line me-2"></i>
                    <span>${file.name}</span>
                    <small class="text-muted ms-2">(${formatFileSize(file.size)})</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger" data-action="remove-uploaded-file">
                    <i class="ri-delete-bin-line"></i>
                </button>
            `;

            fileList.appendChild(fileItem);

            if (window.leaveUI && typeof window.leaveUI.bindFileRemovalButtons === 'function') {
                window.leaveUI.bindFileRemovalButtons({
                    root: fileItem,
                    onRemove: removeFile
                });
            }
        });

        if (fileInput) {
            fileInput.value = '';
        }
    }

    function removeFile(button) {
        button.closest('.file-item')?.remove();
    }

    function formatFileSize(bytes) {
        if (bytes === 0) {
            return '0 Bytes';
    }
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return `${parseFloat((bytes / Math.pow(k, i)).toFixed(2))} ${sizes[i]}`;
}

function validateDates() {
        const startDateInput = qs('#startDate');
        const endDateInput = qs('#endDate');

        if (!startDateInput || !endDateInput) {
            return false;
        }

        const startValue = startDateInput.value;
        const endValue = endDateInput.value;

        if (!startValue || !endValue) {
            return false;
        }

        const start = new Date(startValue);
        const end = new Date(endValue);

        if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) {
            return false;
        }

        if (end < start) {
            showFormAlert('error', 'End date cannot be before start date.');
            return false;
        }

        const info = calculateLeaveDays(start, end);
        state.lastDaysInfo = info;

        updateDayBreakdown(info);

        const totalDaysField = qs('#totalDays');
        if (totalDaysField) {
            totalDaysField.value = info.workingDays;
        }

        if (info.holidays.length > 0) {
            showHolidayInfo(info.holidays);
        } else {
            const holidayInfo = qs('#holidayInfo');
            if (holidayInfo) {
                holidayInfo.style.display = 'none';
            }
        }

        const nextBtn = qs('#step2NextBtn');
        if (nextBtn) {
            nextBtn.disabled = false;
        }

        return true;
    }

    function calculateLeaveDays(start, end) {
    let workingDays = 0;
    let weekendDays = 0;
    let holidayDays = 0;
    const holidays = [];

        const cursor = new Date(start);

        while (cursor <= end) {
            const dayOfWeek = cursor.getDay();

        if (dayOfWeek === 0 || dayOfWeek === 6) {
            weekendDays++;
            } else if (isHoliday(cursor)) {
                holidayDays++;
                holidays.push(formatHoliday(cursor));
            } else {
                workingDays++;
        }

            cursor.setDate(cursor.getDate() + 1);
    }

    return {
        workingDays,
        weekendDays,
        holidayDays,
        holidays
    };
}

    function isHoliday() {
    return false;
}

function formatHoliday(date) {
    return {
        date: date.toISOString().split('T')[0],
            name: 'Holiday'
        };
    }

    function updateDayBreakdown(info) {
        const totalDaysDisplay = qs('#totalDaysDisplay');
        const weekendDaysDisplay = qs('#weekendDaysDisplay');
        const holidayDaysDisplay = qs('#holidayDaysDisplay');

        if (totalDaysDisplay) {
            totalDaysDisplay.textContent = info.workingDays;
        }
        if (weekendDaysDisplay) {
            weekendDaysDisplay.textContent = info.weekendDays;
        }
        if (holidayDaysDisplay) {
            holidayDaysDisplay.textContent = info.holidayDays;
        }
}

function showHolidayInfo(holidays) {
        const holidayInfo = qs('#holidayInfo');
        const holidayList = qs('#holidayList');

        if (!holidayInfo || !holidayList) {
            return;
        }

        holidayList.innerHTML = holidays.map(holiday => `
            <div class="holiday-item">
                <strong>${formatDate(holiday.date)}</strong> - ${holiday.name}
            </div>
        `).join('');

    holidayInfo.style.display = 'block';
}

function formatDate(dateString) {
    const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

function updateSummary() {
        const leaveTypeLabel = qs('#summaryLeaveType');
        const startDateLabel = qs('#summaryStartDate');
        const endDateLabel = qs('#summaryEndDate');
        const returnDateLabel = qs('#summaryReturnDate');
        const workingDaysLabel = qs('#summaryWorkingDays');
        const durationLabel = qs('#summaryDuration');

        if (leaveTypeLabel) {
            leaveTypeLabel.textContent = state.selectedLeaveType?.name || '-';
        }

        const startValue = qs('#startDate')?.value || '';
        const endValue = qs('#endDate')?.value || '';

        if (startDateLabel) {
            startDateLabel.textContent = startValue ? formatDate(startValue) : '-';
    }

        if (endDateLabel) {
            endDateLabel.textContent = endValue ? formatDate(endValue) : '-';
        }

        if (returnDateLabel) {
            returnDateLabel.textContent = endValue ? formatDate(calculateReturnDate(endValue)) : '-';
        }

        const info = state.lastDaysInfo;
        if (info) {
            if (workingDaysLabel) {
                workingDaysLabel.textContent = info.workingDays;
            }
            if (durationLabel) {
                const total = info.workingDays + info.weekendDays + info.holidayDays;
                durationLabel.textContent = `${info.workingDays} working days (${total} total days)`;
            }
    }
}

    function calculateReturnDate(endDateValue) {
        const date = new Date(endDateValue);
        if (Number.isNaN(date.getTime())) {
            return endDateValue;
        }

        date.setDate(date.getDate() + 1);

        while (date.getDay() === 0 || date.getDay() === 6) {
            date.setDate(date.getDate() + 1);
    }

        return date.toISOString().split('T')[0];
}

    function nextStep() {
        if (!validateCurrentStep()) {
            return;
        }
        if (state.currentStep >= TOTAL_STEPS) {
            return;
        }
        setStep(state.currentStep + 1);
}

    function prevStep() {
        if (state.currentStep <= 1) {
            return;
        }
        setStep(state.currentStep - 1);
}

    function setStep(step) {
        if (!getRoot()) {
            return;
    }

        qsa('.form-step').forEach(stepEl => {
            stepEl.classList.toggle('active', stepEl.id === `step${step}`);
        });

        state.currentStep = step;
        updateNavigationButtons();

        if (state.currentStep === TOTAL_STEPS) {
        updateSummary();
    }
}

function updateNavigationButtons() {
        const prevBtn = qs('#prevStepBtn');
        const nextBtn = qs('#nextStepBtn');
        const submitBtn = qs('#submitApplicationBtn');

    if (prevBtn) {
            prevBtn.style.display = state.currentStep > 1 ? '' : 'none';
    }
    if (nextBtn) {
            nextBtn.style.display = state.currentStep < TOTAL_STEPS ? '' : 'none';
    }
    if (submitBtn) {
            submitBtn.style.display = state.currentStep === TOTAL_STEPS ? '' : 'none';
    }
}

    function validateCurrentStep() {
        switch (state.currentStep) {
            case 1:
                if (!state.selectedLeaveType) {
                    showFormAlert('error', 'Please select a leave type.');
                    return false;
    }
                return true;
            case 2:
                return validateDates();
            case 3: {
                const reasonField = qs('#leaveReason');
                const reason = reasonField ? reasonField.value.trim() : '';
                if (!reason) {
                    showFormAlert('error', 'Please provide a reason for your leave.');
                    return false;
                }
                if (reason.length < 10) {
                    showFormAlert('error', 'Please provide a more detailed reason (at least 10 characters).');
                    return false;
                }
                return true;
            }
            default:
                return true;
        }
}

function validateForm() {
        if (!state.selectedLeaveType) {
        showFormAlert('error', 'Please select a leave type.');
        return false;
    }

        const startDateInput = qs('#startDate');
        const endDateInput = qs('#endDate');
        const reasonField = qs('#leaveReason');

        if (!startDateInput?.value || !endDateInput?.value) {
        showFormAlert('error', 'Please select both start and end dates.');
        return false;
    }

        if (!reasonField?.value.trim()) {
        showFormAlert('error', 'Please provide a reason for your leave request.');
        return false;
    }

    return true;
}

    function validateAndSubmitLeaveApplication() {
        if (!validateForm()) {
            return;
        }
        submitLeaveApplication();
    }

    function submitLeaveApplication() {
        const form = qs('#leaveApplicationForm');
        if (!form) {
            showFormAlert('error', 'Unable to locate the leave application form.');
            return;
        }

        const submitBtn = qs('#submitApplicationBtn');
        let originalText;

        if (submitBtn) {
            originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="ri-loader-4-line spin me-1"></i>Submitting...';
            submitBtn.disabled = true;
        }

        form.submit();

        if (submitBtn) {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }

function showFormAlert(type, message) {
        const alertContainer = qs('#formAlertContainer');
        if (!alertContainer) {
            return;
        }

    alertContainer.innerHTML = `
        <div class="alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show" role="alert">
            <i class="ri-${type === 'error' ? 'error-warning-line' : 'information-line'} me-2"></i>
            ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    alertContainer.style.display = 'block';
}

    window.applyLeaveView = {
        init
    };
})(window, document);

document.addEventListener('DOMContentLoaded', () => {
    if (window.applyLeaveView && typeof window.applyLeaveView.init === 'function') {
        window.applyLeaveView.init();
    }
});
</script>

<style>
/* Apply Leave View Specific Styles */
.form-step {
    display: none;
}

.form-step.active {
    display: block;
}

.step-header {
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 1rem;
}

.step-number {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.step-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}

.leave-type-card {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
    height: 100%;
}

.leave-type-card:hover {
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.leave-type-card.selected {
    border-color: #007bff;
    background-color: rgba(0, 123, 255, 0.05);
}

.leave-type-icon {
    width: 60px;
    height: 60px;
    margin: 0 auto 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 123, 255, 0.1);
    border-radius: 50%;
}

.balance-days {
    color: #007bff;
    line-height: 1;
}

.date-info-card {
    border: 1px solid #dee2e6;
}

.info-item {
    padding: 0.5rem;
}

.info-value {
    line-height: 1;
}

.info-label {
    font-size: 0.8rem;
}

.application-summary {
    border: 1px solid #dee2e6;
}

.summary-item {
    font-size: 0.9rem;
}

.project-clearance-item {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.project-clearance-item:hover {
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.clearance-status {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.clearance-status.required {
    background: #fff3cd;
    color: #664d03;
}

.file-upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
}

.file-upload-area:hover {
    border-color: #007bff;
    background-color: rgba(0, 123, 255, 0.05);
}

.file-upload-area.dragover {
    border-color: #007bff;
    background-color: rgba(0, 123, 255, 0.1);
}

.upload-content {
    pointer-events: none;
}

.policy-item {
    padding-bottom: 1rem;
    border-bottom: 1px solid #e9ecef;
}

.policy-item:last-child {
    border-bottom: none;
}

.help-link {
    color: #6c757d;
    text-decoration: none;
    padding: 0.5rem 0;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.help-link:hover {
    color: #007bff;
    background: rgba(0, 123, 255, 0.05);
    padding-left: 0.5rem;
}

.holiday-item {
    padding: 0.75rem 0;
    border-bottom: 1px solid #e9ecef;
}

.holiday-item:last-child {
    border-bottom: none;
}

.holiday-info {
    flex: 1;
}

.holiday-date {
    font-weight: 500;
    color: #495057;
}

.holiday-name {
    color: #6c757d;
    font-size: 0.9rem;
}

.holiday-jurisdiction {
    background: #e9ecef;
    color: #495057;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
}

</style>
    <!-- Calendar View -->
    <div class="col-12" id="calendarViewContainer" style="display: none;">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ri-calendar-line me-2 text-primary"></i>
                        Calendar View - Select Date Range
                    </h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="openApplyLeaveModal()">
                            <i class="ri-add-line me-1"></i> Apply Leave
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Calendar Container -->
                <div id="leaveCalendar"></div>

                <!-- Legend -->
                <div class="mt-3 p-3 bg-light rounded">
                    <h6 class="mb-2">Legend</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-2">
                                <div class="legend-color me-2" style="width: 20px; height: 20px; background-color: #198754; border-radius: 4px;"></div>
                                <small>Your Leave Applications</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-2">
                                <div class="legend-color me-2" style="width: 20px; height: 20px; background-color: #0dcaf0; border-radius: 4px;"></div>
                                <small>Team Members' Leave</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />

<!-- FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

<script>
let leaveCalendar = null;
let selectedDateRange = { start: null, end: null };

// View Toggle Functions - Make them globally accessible
window.switchToFormView = function() {
    const formContainer = document.getElementById('formViewContainer');
    const calendarContainer = document.getElementById('calendarViewContainer');
    const formBtn = document.getElementById('formViewBtn');
    const calendarBtn = document.getElementById('calendarViewBtn');

    if (formContainer) formContainer.style.display = 'block';
    if (calendarContainer) calendarContainer.style.display = 'none';
    if (formBtn) formBtn.classList.add('active');
    if (calendarBtn) calendarBtn.classList.remove('active');
};

window.switchToCalendarView = function() {
    const formContainer = document.getElementById('formViewContainer');
    const calendarContainer = document.getElementById('calendarViewContainer');
    const formBtn = document.getElementById('formViewBtn');
    const calendarBtn = document.getElementById('calendarViewBtn');

    if (formContainer) formContainer.style.display = 'none';
    if (calendarContainer) calendarContainer.style.display = 'block';
    if (formBtn) formBtn.classList.remove('active');
    if (calendarBtn) calendarBtn.classList.add('active');

    // Wait a bit for the container to be visible, then initialize calendar
    setTimeout(function() {
        // Check if FullCalendar is loaded
        if (typeof FullCalendar === 'undefined') {
            console.error('FullCalendar is not loaded. Please check if the library is included.');
            if (calendarContainer) {
                calendarContainer.innerHTML = '<div class="alert alert-danger">Calendar library failed to load. Please refresh the page.</div>';
            }
            return;
        }

        // Initialize calendar if not already initialized
        if (!leaveCalendar) {
            initializeLeaveCalendar();
        } else {
            leaveCalendar.render();
        }
    }, 100);
};

// Also create non-window versions for backward compatibility
function switchToFormView() {
    window.switchToFormView();
}

function switchToCalendarView() {
    window.switchToCalendarView();
}

// Initialize FullCalendar
function initializeLeaveCalendar() {
    const calendarEl = document.getElementById('leaveCalendar');
    if (!calendarEl) {
        console.error('Calendar element not found');
        return;
    }

    // Check if FullCalendar is available
    if (typeof FullCalendar === 'undefined') {
        console.error('FullCalendar is not defined');
        calendarEl.innerHTML = '<div class="alert alert-danger">Calendar library not loaded. Please refresh the page.</div>';
        return;
    }

    try {
        leaveCalendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        selectable: true,
        selectMirror: true,
        select: function(info) {
            // User selected a date range - open application modal
            const startDate = info.startStr.split('T')[0]; // Get date part only
            const endDate = new Date(info.end);
            endDate.setDate(endDate.getDate() - 1); // FullCalendar end is exclusive, so subtract 1 day
            const endDateStr = endDate.toISOString().split('T')[0];

            // Open leave application modal with pre-filled dates
            openApplyLeaveModalWithDates(startDate, endDateStr);

            // Clear selection
            leaveCalendar.unselect();
        },
        events: function(fetchInfo, successCallback, failureCallback) {
            // Fetch leave applications
            fetch(`<?= $base ?>php/scripts/leave/utilities/get_calendar_leave_applications.php?start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Ensure we always pass an array to successCallback
                    if (data && data.success && Array.isArray(data.events)) {
                        successCallback(data.events);
                    } else {
                        // If data structure is unexpected, pass empty array instead of failing
                        console.warn('Unexpected data format from API:', data);
                        successCallback([]);
                    }
                })
                .catch(error => {
                    console.error('Error fetching leave applications:', error);
                    // Pass empty array instead of calling failureCallback to prevent calendar errors
                    successCallback([]);
                });
        },
        eventClick: function(info) {
            // Show leave application details
            const event = info.event;
            const props = event.extendedProps;

            let message = `<strong>${props.leaveTypeName}</strong><br>`;
            message += `Employee: ${props.employeeName}<br>`;
            message += `Status: ${props.leaveStatusName}<br>`;
            message += `Days: ${props.noOfDays}<br>`;
            if (props.halfDayLeave === 'Y') {
                message += `Half Day: ${props.halfDayPeriod || 'Yes'}<br>`;
            }
            if (props.leaveComments) {
                message += `Comments: ${props.leaveComments}`;
            }

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Leave Application Details',
                    html: message,
                    icon: 'info',
                    confirmButtonText: 'Close'
                });
            } else {
                alert(message);
            }
        },
        eventDidMount: function(info) {
            // Add tooltip
            if (info.event.extendedProps.isOwn) {
                info.el.setAttribute('title', `${info.event.extendedProps.leaveTypeName} - ${info.event.extendedProps.noOfDays} days`);
            } else {
                info.el.setAttribute('title', `${info.event.extendedProps.employeeName} - ${info.event.extendedProps.leaveTypeName}`);
            }
        },
        height: 'auto',
        editable: false,
        dayMaxEvents: true,
        moreLinkClick: 'popover'
        });

        leaveCalendar.render();
        console.log('Leave calendar initialized successfully');
    } catch (error) {
        console.error('Error initializing calendar:', error);
        calendarEl.innerHTML = '<div class="alert alert-danger">Error initializing calendar: ' + error.message + '</div>';
    }
}

// Open apply leave modal with pre-filled dates
function openApplyLeaveModalWithDates(startDate, endDate) {
    // Check if the modal function exists (from apply_leave_modal.php)
    if (typeof window.openApplyLeaveModal === 'function') {
        // Store dates in sessionStorage or global variable for the modal to pick up
        sessionStorage.setItem('prefillStartDate', startDate);
        sessionStorage.setItem('prefillEndDate', endDate);
        window.openApplyLeaveModal();
    } else {
        // Fallback: redirect to form view with dates
        switchToFormView();
        // Set dates in form if available
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');
        if (startDateInput) startDateInput.value = startDate;
        if (endDateInput) endDateInput.value = endDate;

        // Trigger date validation
        if (typeof validateDates === 'function') {
            validateDates();
        }
    }
}

// Open apply leave modal (from calendar view button)
function openApplyLeaveModal() {
    if (typeof window.openApplyLeaveModal === 'function') {
        window.openApplyLeaveModal();
    } else {
        // Fallback: switch to form view
        switchToFormView();
    }
}

// Initialize on page load if calendar view is active
document.addEventListener('DOMContentLoaded', function() {
    // Wait for FullCalendar to load
    function waitForFullCalendar(callback, maxAttempts = 10) {
        let attempts = 0;
        const checkInterval = setInterval(function() {
            attempts++;
            if (typeof FullCalendar !== 'undefined') {
                clearInterval(checkInterval);
                callback();
            } else if (attempts >= maxAttempts) {
                clearInterval(checkInterval);
                console.error('FullCalendar failed to load after ' + maxAttempts + ' attempts');
            }
        }, 100);
    }

    // Attach event listeners to toggle buttons
    const formBtn = document.getElementById('formViewBtn');
    const calendarBtn = document.getElementById('calendarViewBtn');

    if (formBtn) {
        formBtn.addEventListener('click', window.switchToFormView);
    }

    if (calendarBtn) {
        calendarBtn.addEventListener('click', window.switchToCalendarView);
    }

    // Check if we should show calendar view by default
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('view') === 'calendar') {
        waitForFullCalendar(function() {
            window.switchToCalendarView();
        });
    }
});
</script>

<style>
#leaveCalendar {
    font-family: inherit;
}

#leaveCalendar .fc-event {
    cursor: pointer;
}

#leaveCalendar .fc-daygrid-day:hover {
    background-color: #f8f9fa;
}

#leaveCalendar .fc-day-selected {
    background-color: rgba(13, 110, 253, 0.1) !important;
}

.legend-color {
    border: 1px solid #dee2e6;
}

/* Responsive Design */
@media (max-width: 768px) {
    .step-actions {
        flex-direction: column;
        gap: 1rem;
    }

    .step-actions .btn {
        width: 100%;
    }

    .leave-type-card {
        margin-bottom: 1rem;
    }

    .approval-workflow {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>
