<?php
/**
 * Apply Leave Modal
 *
 * Comprehensive leave application modal with multi-step form,
 * validation, and real-time leave balance checking
 */

// Ensure required variables are available
if (!isset($employeeDetails)) {
    $employeeDetails = Employee::employees(array('ID'=>$userDetails->ID), true, $DBConn);
}

if (!isset($orgDataID)) {
    $orgDataID = $employeeDetails->orgDataID;
}

if (!isset($entityID)) {
    $entityID = $employeeDetails->entityID;
}

if (!isset($leaveTypes)) {
    $leaveTypes = Leave::leave_types(array('Lapsed'=>'N'), false, $DBConn);
}

if (!isset($leaveEntitlements)) {
    $leaveEntitlements = Leave::leave_entitlements(array('Suspended'=>'N', 'entityID'=>$entityID), false, $DBConn);
}

// Get current leave period and policies
$currentLeavePeriod = Leave::get_current_leave_period($entityID, $DBConn);
$leaveAccumulationPolicy = Leave::get_leave_accumulation_policy($entityID, $DBConn);
$employeeLeaveBalances = Leave::calculate_leave_balances($employeeDetails->ID, $entityID, $DBConn);
?>

<!-- Apply Leave Modal -->
<div class="modal fade" id="applyLeaveModal" tabindex="-1" aria-labelledby="applyLeaveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <div class="d-flex align-items-center">
                    <h5 class="modal-title mb-0" id="applyLeaveModalLabel">
                        <i class="ri-calendar-add-line me-2"></i>
                        Apply for Leave
                    </h5>
                    <button
                        type="button"
                        class="btn btn-sm btn-link text-white ms-3 p-0"
                        data-bs-toggle="collapse"
                        data-bs-target="#leaveInstructionsCollapse"
                        aria-expanded="false"
                        aria-controls="leaveInstructionsCollapse"
                        title="View leave application instructions">
                        <i class="ri-information-line fs-5"></i>
                    </button>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Process Guidance Collapse -->
                <div class="collapse" id="leaveInstructionsCollapse">
                    <div class="process-guidance bg-info bg-opacity-10 p-3 border-bottom">
                        <div class="d-flex align-items-start">
                            <i class="ri-information-line text-info me-2 mt-1"></i>
                            <div>
                                <h6 class="mb-2 text-info">Leave Application Process</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <strong>Step 1:</strong> Select your leave type and check available days<br>
                                            <strong>Step 2:</strong> Choose your leave dates and review calculations<br>
                                            <strong>Step 3:</strong> Provide reason and additional information<br>
                                            <strong>Step 4:</strong> Review and submit your application
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <strong>Important:</strong><br>
                                            • Applications require supervisor approval<br>
                                            • Submit at least 24 hours in advance<br>
                                            • Upload supporting documents if required<br>
                                            • Check your leave balance before applying
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alert Container -->
                <div id="formAlertContainer"></div>

                <!-- Debug Button (temporary) -->
                <div class="p-2 bg-warning bg-opacity-25 border-bottom">
                    <button type="button" class="btn btn-sm btn-warning" data-action="workflow-debug">
                        <i class="ri-bug-line me-1"></i>Debug Form Visibility
                    </button>
                    <small class="ms-2 text-muted">Click to check form visibility status</small>
                </div>

                <!-- Progress Indicator -->
                <div class="progress-indicator bg-light p-3 border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="step-indicator">
                            <span class="step active" data-step="1">
                                <i class="ri-calendar-line"></i>
                                <span>Leave Type</span>
                            </span>
                            <span class="step" data-step="2">
                                <i class="ri-calendar-2-line"></i>
                                <span>Dates</span>
                            </span>
                            <span class="step" data-step="3">
                                <i class="ri-file-text-line"></i>
                                <span>Details</span>
                            </span>
                            <span class="step" data-step="4">
                                <i class="ri-briefcase-line"></i>
                                <span>Handover</span>
                            </span>
                            <span class="step" data-step="5">
                                <i class="ri-check-line"></i>
                                <span>Review</span>
                            </span>
                        </div>

                    </div>
                </div>

                <form id="applyLeaveForm" enctype="multipart/form-data">
                    <!-- Step 1: Leave Type Selection -->
                    <div class="form-step active" id="step1">
                        <div class="p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Select Leave Type</h6>
                                <small class="text-muted">Click on a leave type to select it</small>
                            </div>

                            <!-- Leave Type Selection Help -->
                            <div class="alert alert-light border mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="ri-lightbulb-line text-warning me-2 mt-1"></i>
                                    <div>
                                        <small class="text-muted">
                                            <strong>Guidance:</strong> Choose the appropriate leave type based on your reason.
                                            Each type has different rules and approval requirements. Check your available balance
                                            and maximum days per application before selecting.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <?php
                                if ($leaveTypes && is_array($leaveTypes) && count($leaveTypes) > 0): ?>
                                    <?php
                                    foreach ($leaveTypes as $leaveType): ?>
                                        <?php
                                        $entitlement = array_filter($leaveEntitlements, function($e) use ($leaveType) {
                                            return $e->leaveTypeID == $leaveType->leaveTypeID;
                                        });
                                        $entitlement = reset($entitlement);

                                        // Filter by gender/leaveSegment - skip if leave type is restricted to different gender
                                        if ($entitlement && is_object($entitlement) && $entitlement->leaveSegment &&
                                            strtolower($entitlement->leaveSegment) != strtolower($employeeDetails->gender ?? '')) {
                                            continue;
                                        }

                                        $availableDays = 0;
                                        if ($entitlement && is_object($entitlement)) {

                                            // var_dump($employeeLeaveBalances);
                                            //replace spaces with underscore
                                            $leaveTypeKey = strtolower(str_replace(' ', '_', $leaveType->leaveTypeName));
                                            // $leaveTypeKey = strtolower($leaveType->leaveTypeName);
                                            $availableDays = isset($employeeLeaveBalances[$leaveTypeKey]) ?  $employeeLeaveBalances[$leaveTypeKey]['available'] : 0;
                                        }   ?>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="leave-type-card card h-100 cursor-pointer"
                                                 data-leave-type-id="<?= $leaveType->leaveTypeID ?>"
                                                 data-entitlement-id="<?= ($entitlement && is_object($entitlement)) ? $entitlement->leaveEntitlementID : '' ?>"
                                                 data-max-days="<?= ($entitlement && is_object($entitlement)) ? ($entitlement->maxDaysPerApplication ?? $entitlement->entitlement) : 0 ?>"
                                                 data-available-days="<?= $availableDays ?>"
                                                 <?= $availableDays <= 0 ? 'data-disabled="true"' : '' ?>>
                                                <div class="card-body text-center">
                                                    <div class="leave-type-icon mb-3">
                                                        <i class="ri-calendar-line text-primary" style="font-size: 2rem;"></i>
                                                    </div>
                                                    <h6 class="card-title"><?= $leaveType->leaveTypeName ?></h6>
                                                    <p class="card-text text-muted small"><?= $leaveType->leaveTypeDescription ?></p>
                                                    <div class="leave-info">
                                                        <?php if ($availableDays > 0): ?>
                                                            <span class="badge bg-success">Available: <?= $availableDays ?> days</span>
                                                            <?php if ($entitlement && is_object($entitlement) && ($entitlement->maxDaysPerApplication ?? $entitlement->entitlement) > 0): ?>
                                                                <span class="badge bg-info">Max: <?= $entitlement->maxDaysPerApplication ?? $entitlement->entitlement ?> days</span>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">No balance available</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if ($availableDays <= 0): ?>
                                                        <div class="mt-2">
                                                            <small class="text-danger">
                                                                <i class="ri-error-warning-line me-1"></i>
                                                                Cannot apply - no available days
                                                            </small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12">
                                        <div class="alert alert-warning">
                                            <i class="ri-information-line me-2"></i>
                                            <strong>No leave types available.</strong><br>
                                            <small>This could be due to:</small>
                                            <ul class="mb-0 mt-2">
                                                <li>No leave types configured in the system</li>
                                                <li>All leave types are marked as lapsed</li>
                                                <li>Database connection issues</li>
                                            </ul>
                                            <small class="text-muted">Please contact HR or system administrator.</small>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Include Steps 2-4 -->
                    <?php include 'apply_leave_modal_steps.php'; ?>

                    <!-- Hidden Fields -->
                    <input type="hidden" name="leaveTypeId" id="leaveTypeId">
                    <input type="hidden" name="leaveEntitlementId" id="leaveEntitlementId">
                    <input type="hidden" name="employeeId" value="<?= $employeeDetails->ID ?>">
                    <input type="hidden" name="orgDataId" value="<?= $orgDataID ?>">
                    <input type="hidden" name="entityId" value="<?= $entityID ?>">
                    <input type="hidden" name="leavePeriodId" value="<?= $currentLeavePeriod->leavePeriodID ?? '' ?>">
                    <input type="hidden" name="handoverPayload" id="handoverPayload">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-outline-primary" id="prevStepBtn" data-action="workflow-prev-step" style="display: none;">
                    <i class="ri-arrow-left-line me-1"></i>Previous
                </button>
                <button type="button" class="btn btn-primary" id="nextStepBtn" data-action="workflow-next-step">
                    Next <i class="ri-arrow-right-line ms-1"></i>
                </button>
                <button type="button" class="btn btn-success" id="submitApplicationBtn" data-action="workflow-submit" style="display: none;">
                    <i class="ri-send-plane-line me-1"></i>Submit Application
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Include Modal Scripts -->
<?php //include 'apply_leave_modal_scripts.php'; ?>