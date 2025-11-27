<?php
if(!$isValidUser) {
    Alert::info("You need to be logged in as a valid administrator to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

$employeeID = (isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) : $userDetails->ID;
$employeeDetails = Employee::employees(array('ID'=>$employeeID), true, $DBConn);
$orgDataID = isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : $employeeDetails->orgDataID;
$entityID = isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : $employeeDetails->entityID;

// Initialize variables
if (!isset($leaveTypes)) {
    $leaveTypes = Leave::leave_types(array('Lapsed'=>'N'), false, $DBConn);
}
if (!isset($leaveEntitlements)) {
    $leaveEntitlements = Leave::leave_entitlements(array('Suspended'=>'N', 'entityID'=>$entityID), false, $DBConn);
}
if (!isset($employeeLeaveBalances)) {
    $employeeLeaveBalances = Leave::get_employee_leave_balances($employeeDetails->ID, $entityID, $DBConn);

    // var_dump($employeeLeaveBalances);
}
if (!isset($currentLeavePeriod)) {
    $currentLeavePeriod = Leave::get_current_leave_period($entityID, $DBConn);
}

// Check if this is an edit mode
$isEditMode = isset($_GET['edit']) && !empty($_GET['edit']);
$editApplicationId = $isEditMode ? $_GET['edit'] : null;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_leave_application') {
    $result = processLeaveApplication($_POST, $DBConn, $isEditMode, $editApplicationId, $userDetails);
    if ($result['success']) {
        Alert::success($result['message'], true);
        header("Location:{$base}html/?s={$s}&ss={$ss}&p=apply_leave_workflow&success=1");
        exit;
    } else {
        Alert::error($result['message'], true);
    }
}

// Removed - notification creation now handled in processLeaveApplication function

function processLeaveApplication($data, $DBConn, $isEditMode = false, $editApplicationId = null, $userDetails = null) {
    try {
        // Check if userDetails is available
        if (!$userDetails || !isset($userDetails->ID)) {
            return array('success' => false, 'message' => "User session not found. Please log in again.");
        }

        // Validate required fields
        $requiredFields = array('leaveTypeId', 'leaveEntitlementId', 'startDate', 'endDate', 'leaveReason');
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return array('success' => false, 'message' => "Please fill in all required fields.");
            }
        }

        $submissionMode = isset($data['submissionMode'])
            ? strtolower(Utility::clean_string($data['submissionMode']))
            : 'submit';
        $submissionMode = in_array($submissionMode, array('submit', 'schedule'), true) ? $submissionMode : 'submit';
        $isScheduling = ($submissionMode === 'schedule');

        // Calculate leave days (excluding weekends and holidays)
        $startDate = new DateTime($data['startDate']);
        $endDate = new DateTime($data['endDate']);
        $entityID = $data['entityId'];

        // Use calculate_working_days which excludes both weekends and holidays
        $noOfDays = Leave::calculate_working_days($data['startDate'], $data['endDate'], $entityID, $DBConn);

        // Handle half day
        $halfDayValue = isset($data['halfDayLeave']) ? $data['halfDayLeave'] : '';
        if ($halfDayValue == 'on') {
            $noOfDays = 0.5;
        }

        if ($isEditMode && $editApplicationId) {
            // Update existing application using mysqlConnect update_table method
            $updateData = array(
                'leaveEntitlementID' => $data['leaveEntitlementId'],
                'leaveTypeID' => $data['leaveTypeId'],
                'startDate' => $data['startDate'],
                'endDate' => $data['endDate'],
                'noOfDays' => $noOfDays,
                'leaveComments' => $data['leaveReason'],
                'emergencyContact' => isset($data['emergencyContact']) ? $data['emergencyContact'] : '',
                'handoverNotes' => isset($data['handoverNotes']) ? $data['handoverNotes'] : '',
                'modifiedBy' => $userDetails->ID,
                'modifiedDate' => date('Y-m-d H:i:s'),
                'LastUpdate' => date('Y-m-d H:i:s'),
                'LastUpdateByID' => $userDetails->ID
            );

            $whereClause = array('leaveApplicationID' => $editApplicationId);

            $desiredStatus = $isScheduling ? 1 : 3;
            $updateData['leaveStatusID'] = $desiredStatus;
            $updateData['dateApplied'] = $isScheduling ? null : date('Y-m-d H:i:s');
            $updateData['appliedByID'] = $isScheduling ? null : $userDetails->ID;

            $result = $DBConn->update_table('tija_leave_applications', $updateData, $whereClause);

            if ($result) {
                return array('success' => true, 'message' => 'Leave application updated successfully!');
            } else {
                return array('success' => false, 'message' => 'Failed to update leave application. Please try again.');
            }
        } else {
            // Create new application using mysqlConnect insert_data method
            $applicationData = array(
                'leaveEntitlementID' => $data['leaveEntitlementId'],
                'leaveTypeID' => $data['leaveTypeId'],
                'employeeID' => $data['employeeId'],
                'orgDataID' => $data['orgDataId'],
                'entityID' => $data['entityId'],
                'leavePeriodID' => $data['leavePeriodId'],
                'startDate' => $data['startDate'],
                'endDate' => $data['endDate'],
                'noOfDays' => $noOfDays,
                'leaveComments' => $data['leaveReason'],
                'emergencyContact' => isset($data['emergencyContact']) ? $data['emergencyContact'] : '',
                'handoverNotes' => isset($data['handoverNotes']) ? $data['handoverNotes'] : '',
                'leaveStatusID' => $isScheduling ? 1 : 3,
                'dateApplied' => $isScheduling ? null : date('Y-m-d H:i:s'),
                'appliedByID' => $isScheduling ? null : $userDetails->ID,
                'createdBy' => $userDetails->ID,
                'createdDate' => date('Y-m-d H:i:s'),
                'DateAdded' => date('Y-m-d H:i:s'),
                'Lapsed' => 'N',
                'Suspended' => 'N'
            );

            $result = $DBConn->insert_data('tija_leave_applications', $applicationData);

            if ($result) {
                $leaveApplicationID = $DBConn->lastInsertId();

                // Send notifications if this is a submission (not scheduling)
                if (!$isScheduling && class_exists('Leave')) {
                    try {
                        // Check for active approval workflow
                        $activeWorkflow = Leave::get_active_approval_workflow($data['entityId'], $DBConn);

                        if ($activeWorkflow) {
                            $policyID = is_object($activeWorkflow) ? $activeWorkflow->policyID : (is_array($activeWorkflow) ? $activeWorkflow['policyID'] : null);

                            if ($policyID) {
                                // Create approval instance
                                $instanceID = Leave::create_approval_instance($leaveApplicationID, $policyID, $DBConn);

                                if ($instanceID) {
                                    // Get approvers from workflow
                                    $approvers = Leave::get_workflow_approvers($policyID, $DBConn);

                                    if (!empty($approvers)) {
                                        // Get employee details for notification
                                        $employeeDetails = Employee::employees(array('ID' => $data['employeeId']), true, $DBConn);
                                        $employeeName = $employeeDetails ? ($employeeDetails->FirstName . ' ' . $employeeDetails->Surname) : 'Employee';

                                        // Get leave type details
                                        $leaveTypeObj = Leave::leave_types(array('leaveTypeID' => $data['leaveTypeId']), true, $DBConn);
                                        $leaveTypeName = $leaveTypeObj ? $leaveTypeObj->leaveTypeName : 'Leave';

                                        // Get the maximum step order to identify final step
                                        $maxStepOrder = 0;
                                        foreach ($approvers as $approver) {
                                            $stepOrder = isset($approver['stepOrder']) ? (int)$approver['stepOrder'] : 0;
                                            if ($stepOrder > $maxStepOrder) {
                                                $maxStepOrder = $stepOrder;
                                            }
                                        }

                                        // Send notifications to all approvers from workflow
                                        $notifiedUserIDs = array();

                                        foreach ($approvers as $approver) {
                                            if (empty($approver['approverUserID'])) {
                                                continue;
                                            }

                                            $approverUserID = (int)$approver['approverUserID'];
                                            if (in_array($approverUserID, $notifiedUserIDs)) {
                                                continue; // Avoid duplicate notifications
                                            }

                                            $isFinalStep = isset($approver['stepOrder']) && (int)$approver['stepOrder'] === $maxStepOrder;

                                            if (class_exists('Notification')) {
                                                Notification::create(array(
                                                    'eventSlug' => 'leave_pending_approval',
                                                    'userId' => $approverUserID,
                                                    'originatorId' => $data['employeeId'],
                                                    'data' => array(
                                                        'employee_id' => $data['employeeId'],
                                                        'employee_name' => $employeeName,
                                                        'leave_type' => $leaveTypeName,
                                                        'start_date' => date('M j, Y', strtotime($data['startDate'])),
                                                        'end_date' => date('M j, Y', strtotime($data['endDate'])),
                                                        'total_days' => $noOfDays,
                                                        'application_id' => $leaveApplicationID,
                                                        'approval_level' => $approver['stepOrder'] ?? 1,
                                                        'step_name' => $approver['stepName'] ?? 'Approval Step',
                                                        'approver_name' => $approver['approverName'] ?? 'Approver',
                                                        'is_final_step' => $isFinalStep
                                                    ),
                                                    'link' => '?s=user&ss=leave&p=pending_approvals&id=' . $leaveApplicationID,
                                                    'entityID' => $data['entityId'],
                                                    'orgDataID' => $data['orgDataId'],
                                                    'segmentType' => 'leave_application',
                                                    'segmentID' => $leaveApplicationID,
                                                    'priority' => 'high'
                                                ), $DBConn);

                                                $notifiedUserIDs[] = $approverUserID;
                                            }
                                        }

                                        // Send confirmation notification to employee
                                        if (class_exists('Notification')) {
                                            Notification::create(array(
                                                'eventSlug' => 'leave_application_submitted',
                                                'userId' => $data['employeeId'],
                                                'originatorId' => $data['employeeId'],
                                                'data' => array(
                                                    'employee_name' => $employeeName,
                                                    'employee_id' => $data['employeeId'],
                                                    'leave_type' => $leaveTypeName,
                                                    'start_date' => date('M j, Y', strtotime($data['startDate'])),
                                                    'end_date' => date('M j, Y', strtotime($data['endDate'])),
                                                    'total_days' => $noOfDays,
                                                    'leave_reason' => $data['leaveReason'] ?? 'No reason provided',
                                                    'application_id' => $leaveApplicationID,
                                                    'application_link' => '?s=user&ss=leave&p=my_applications&id=' . $leaveApplicationID
                                                ),
                                                'link' => '?s=user&ss=leave&p=my_applications&id=' . $leaveApplicationID,
                                                'entityID' => $data['entityId'],
                                                'orgDataID' => $data['orgDataId'],
                                                'segmentType' => 'leave_application',
                                                'segmentID' => $leaveApplicationID,
                                                'priority' => 'medium'
                                            ), $DBConn);
                                        }
                                    }
                                }
                            }
                        } else {
                            // Fallback: Use LeaveNotifications class if no workflow
                            if (class_exists('LeaveNotifications')) {
                                LeaveNotifications::notifyLeaveSubmitted($leaveApplicationID, $DBConn);
                            }
                        }
                    } catch (Exception $e) {
                        // Log error but don't fail the application submission
                        error_log("Leave notification error for application {$leaveApplicationID}: " . $e->getMessage());
                    }
                }

                error_log("Leave application submitted: ID {$leaveApplicationID} by employee " . $data['employeeId']);

                return array('success' => true, 'message' => 'Leave application submitted successfully!');
            } else {
                return array('success' => false, 'message' => 'Failed to submit leave application. Please try again.');
            }
        }

    } catch (Exception $e) {
        return array('success' => false, 'message' => 'An error occurred: ' . $e->getMessage());
    }
}
?>

<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb border-bottom">
   <h1 class="page-title fw-medium fs-24 mb-0"><?= $isEditMode ? 'Edit Leave Application' : 'Apply for Leave' ?></h1>
   <div class="ms-md-1 ms-0">
      <nav>
         <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $s ?></a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $ss ?></a></li>
            <li class="breadcrumb-item"><a href="?page=leave/apply_leave">Leave Management</a></li>
            <li class="breadcrumb-item active d-inline-flex" aria-current="page">Apply Leave</li>
         </ol>
      </nav>
   </div>
</div>

<?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="ri-check-circle-line me-2"></i>
    <strong>Success!</strong> Your leave application has been submitted successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-12">
        <div class="card custom-card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h4 class="card-title mb-0">
                    <i class="ri-calendar-line me-2"></i>
                    <?= $isEditMode ? 'Edit Leave Application' : 'Leave Application Process' ?>
                </h4>
            </div>
            <div class="card-body p-0">

                <!-- Progress Indicator -->
                <div class="progress-indicator bg-light p-4 border-bottom">
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
                                <i class="ri-check-double-line"></i>
                                <span>Review</span>
                            </span>
                        </div>
                        <div class="leave-balance-summary">
                            <small class="text-muted">Available Leave Balance</small>
                            <div class="d-flex gap-2">
                                <?php if ($employeeLeaveBalances && is_array($employeeLeaveBalances)): ?>
                                    <?php foreach ($employeeLeaveBalances as $type => $balance): ?>
                                        <span class="badge bg-success"><?= ucfirst($type) ?>: <?= $balance['available'] ?> days</span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alert Container -->
                <div id="formAlertContainer" class="p-3"></div>

                <!-- Application Form -->
                <form id="applyLeaveForm" method="POST" enctype="multipart/form-data" class="p-4">
                    <input type="hidden" name="action" value="submit_leave_application">

                    <!-- Step 1: Leave Type Selection -->
                    <div class="form-step active" id="step1">
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="ri-calendar-line me-2 text-primary"></i>
                                Select Leave Type
                            </h5>
                            <p class="text-muted mb-4">Choose the appropriate leave type based on your reason. Each type has different rules and approval requirements.</p>

                            <div class="row g-3">
                                <?php if ($leaveTypes && is_array($leaveTypes) && count($leaveTypes) > 0): ?>
                                    <?php foreach ($leaveTypes as $leaveType): ?>
                                        <?php
                                        $entitlement = array_filter($leaveEntitlements, function($e) use ($leaveType) {
                                            return $e->leaveTypeID == $leaveType->leaveTypeID;
                                        });

                                        // var_dump($entitlement);
                                        $entitlement = reset($entitlement);
                                        $availableDays = 0;
                                        if ($entitlement && is_object($entitlement)) {
                                            $leaveTypeKey = strtolower(str_replace(' ', '_', $leaveType->leaveTypeName));
                                            $availableDays = isset($employeeLeaveBalances[$leaveTypeKey]) ?
                                                $employeeLeaveBalances[$leaveTypeKey]['available'] : 0;
                                        }
                                        // var_dump($availableDays);
                                        ?>
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

                    <!-- Step 2: Date Selection -->
                    <div class="form-step" id="step2">
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="ri-calendar-2-line me-2 text-primary"></i>
                                Select Leave Dates
                            </h5>
                            <p class="text-muted mb-4">Choose your leave start and end dates. The system will automatically calculate working days, weekends, and holidays.</p>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="startDate" class="form-label">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control form-control-sm date" id="startDate" name="startDate" required>
                                    <div class="form-text">Select the first day of your leave</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="endDate" class="form-label">End Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control form-control-sm date" id="endDate" name="endDate" required>
                                    <div class="form-text">Select the last day of your leave</div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="halfDayLeave" name="halfDayLeave">
                                        <label class="form-check-label" for="halfDayLeave">
                                            Half Day Leave
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12" id="halfDayOptions" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">Half Day Period</label>
                                            <select class="form-select form-select-sm" id="halfDayPeriod" name="halfDayPeriod">
                                                <option value="">Select Period</option>
                                                <option value="morning">Morning (8:00 AM - 12:00 PM)</option>
                                                <option value="afternoon">Afternoon (1:00 PM - 5:00 PM)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Leave Summary -->
                            <div class="mt-4 p-3 bg-light rounded">
                                <h6 class="mb-2">Leave Summary</h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <small class="text-muted">Total Days:</small>
                                        <div class="fw-bold" id="totalDays">0</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Working Days:</small>
                                        <div class="fw-bold" id="workingDays">0</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Weekends:</small>
                                        <div class="fw-bold" id="weekendDays">0</div>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Holidays:</small>
                                        <div class="fw-bold" id="holidayDays">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Additional Details -->
                    <div class="form-step" id="step3">
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="ri-file-text-line me-2 text-primary"></i>
                                Additional Information
                            </h5>
                            <p class="text-muted mb-4">Provide details for your leave application and any supporting documents.</p>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="leaveReason" class="form-label">Reason for Leave <span class="text-danger">*</span></label>
                                    <textarea class="form-control form-control-sm form-control-plaintext bg-light-blue border-bottom border-bottom-primary px-2" id="leaveReason" name="leaveReason" rows="3"
                                              placeholder="Please provide a brief reason for your leave..." required></textarea>
                                </div>
                                <div class="col-12">
                                    <label for="emergencyContact" class="form-label">Emergency Contact</label>
                                    <input type="text" class="form-control form-control-sm form-control-plaintext bg-light-blue border-bottom border-bottom-primary px-2" id="emergencyContact" name="emergencyContact"
                                           placeholder="Name and phone number of emergency contact">
                                </div>
                                <div class="col-12">
                                    <label for="handoverNotes" class="form-label">Handover Notes</label>
                                    <textarea class="form-control form-control-sm form-control-plaintext bg-light-blue border-bottom border-bottom-primary px-2" id="handoverNotes" name="handoverNotes" rows="3"
                                              placeholder="Any important information for your colleagues during your absence..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label for="supportingDocuments" class="form-label">Supporting Documents</label>
                                    <input type="file" class="form-control" id="supportingDocuments" name="supportingDocuments[]"
                                           multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                    <div class="form-text">Upload any supporting documents (medical certificates, etc.)</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Handover Requirements -->
                    <div class="form-step" id="step4">
                        <div class="mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h5 class="mb-0">
                                    <i class="ri-briefcase-line me-2 text-primary"></i>
                                    Leave Handover Plan
                                </h5>
                                <span id="handoverRequirementBadge" class="badge bg-secondary">Pending selection</span>
                            </div>
                            <p class="text-muted mb-3">
                                Specify the tasks, projects, or responsibilities that will be transferred while you are on leave.
                                Each item should be assigned to at least one colleague who can confirm readiness.
                            </p>
                        </div>

                        <div id="handoverRequirementNotice" class="alert alert-info d-none">
                            <i class="ri-information-line me-2"></i>
                            <span></span>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Add Handover Item</h6>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="resetHandoverFormBtn">
                                    <i class="ri-refresh-line me-1"></i>Reset Form
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Task / Responsibility Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="handoverItemTitle" placeholder="e.g. Weekly sales report">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Type</label>
                                        <select class="form-select" id="handoverItemType">
                                            <option value="function">Function</option>
                                            <option value="project_task">Project Task</option>
                                            <option value="duty">Duty</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Priority</label>
                                        <select class="form-select" id="handoverItemPriority">
                                            <option value="medium">Medium</option>
                                            <option value="low">Low</option>
                                            <option value="high">High</option>
                                            <option value="critical">Critical</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label">Description / Instructions</label>
                                        <textarea class="form-control" id="handoverItemDescription" rows="2"
                                                  placeholder="Provide context, access instructions, expected deliverables, etc."></textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Due / Check-in Date</label>
                                        <input type="date" class="form-control" id="handoverItemDueDate">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label">Assign Colleagues <span class="text-danger">*</span></label>
                                        <select class="form-select" id="handoverAssignees" multiple>
                                            <option value="">Loading colleagues...</option>
                                        </select>
                                        <div class="form-text">Hold Ctrl (Windows) or Command (Mac) to select multiple colleagues.</div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-3">
                                    <button type="button" class="btn btn-outline-primary" id="addHandoverItemBtn">
                                        <i class="ri-add-line me-1"></i>Add Handover Item
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Planned Handover Items</h6>
                                <small class="text-muted" id="handoverItemsCounter">0 items</small>
                            </div>
                            <div class="card-body" id="handoverItemsList">
                                <p class="text-muted mb-0">No handover items added yet. Use the form above to capture the responsibilities you need to transfer.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Review and Submit -->
                    <div class="form-step" id="step5">
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="ri-check-double-line me-2 text-primary"></i>
                                Review Your Application
                            </h5>
                            <p class="text-muted mb-4">Please review all details before submitting your leave application.</p>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">Leave Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <strong>Leave Type:</strong>
                                                <span id="reviewLeaveType">-</span>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Start Date:</strong>
                                                <span id="reviewStartDate">-</span>
                                            </div>
                                            <div class="mb-2">
                                                <strong>End Date:</strong>
                                                <span id="reviewEndDate">-</span>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Total Days:</strong>
                                                <span id="reviewTotalDays">-</span>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Half Day:</strong>
                                                <span id="reviewHalfDay">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">Additional Information</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <strong>Reason:</strong>
                                                <div id="reviewReason" class="text-muted small">-</div>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Emergency Contact:</strong>
                                                <span id="reviewEmergencyContact">-</span>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Handover Notes:</strong>
                                                <div id="reviewHandoverNotes" class="text-muted small">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header d-flex align-items-center justify-content-between">
                                            <h6 class="mb-0">Handover Overview</h6>
                                            <span class="badge bg-secondary" id="reviewHandoverStatus">Not evaluated</span>
                                        </div>
                                        <div class="card-body" id="reviewHandoverItems">
                                            <p class="text-muted mb-0">No handover items captured.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

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

            <!-- Navigation Footer -->
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <a href="<?= "{$base}html/?s=user&ss=leave&p=leave_management_enhanced" ?>" class="btn btn-outline-secondary">
                            <i class="ri-arrow-left-line me-1"></i>Back to Leave Management
                        </a>
                    </div>
                    <div class="d-flex gap-2">
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
    </div>
</div>

<style>
/* Full Page Application Styles */
.leave-type-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
    cursor: pointer;
}

.leave-type-card:hover {
    border-color: var(--bs-primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.leave-type-card.selected {
    border-color: var(--bs-primary);
    background-color: rgba(var(--bs-primary-rgb), 0.05);
}

.leave-type-card[data-disabled="true"] {
    opacity: 0.6;
    cursor: not-allowed;
    background-color: #f8f9fa;
}

.leave-type-card[data-disabled="true"]:hover {
    transform: none;
    box-shadow: none;
    border-color: transparent;
}

/* Progress Indicator */
.progress-indicator {
    flex-shrink: 0;
    border-bottom: 1px solid #dee2e6;
}

.step-indicator {
    display: flex;
    justify-content: space-between;
    width: 100%;
    max-width: 600px;
}

.step-indicator .step {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 0.5rem;
    color: #6c757d;
    font-size: 0.875rem;
    flex: 1;
    text-align: center;
}

.step-indicator .step.active {
    color: var(--bs-primary);
}

.step-indicator .step i {
    font-size: 1.25rem;
    margin-bottom: 0.25rem;
}

/* Form Steps */
.form-step {
    display: none;
    min-height: 400px;
}

.form-step.active {
    display: block;
}

/* Leave Balance Summary */
.leave-balance-summary {
    text-align: right;
}

.leave-balance-summary .badge {
    font-size: 0.75rem;
}

/* Utility Classes */
.cursor-pointer {
    cursor: pointer;
}

.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

<script>
// Full Page Application JavaScript
const WORKFLOW_TOTAL_STEPS = 5;
let workflowCurrentStep = 1;
let workflowSelectedLeaveType = null;
let workflowSelectedEntitlement = null;
let holidayDates = []; // Store holiday dates
let handoverState = {
    required: false,
    policy: null,
    items: []
};
let handoverColleagues = [];

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    // Fetch holidays first
    fetchHolidays();
    initializeLeaveApplication();
    initializeHandoverModule();
    loadHandoverColleagues();

    document.querySelectorAll('[data-action="workflow-next-step"]').forEach(button => {
        button.addEventListener('click', nextStep);
    });

    document.querySelectorAll('[data-action="workflow-prev-step"]').forEach(button => {
        button.addEventListener('click', prevStep);
    });

    document.querySelectorAll('[data-action="workflow-submit"]').forEach(button => {
        button.addEventListener('click', submitLeaveApplication);
    });

    // Prevent default form submission
    const form = document.getElementById('applyLeaveForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitLeaveApplication();
        });
    }

    //initialize date picker with flatpickr and the date class (date only, no time)
    flatpickr('.date', {
        enableTime: false,
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "F j, Y",
        minDate: "today",
        disableMobile: false
    });

    // Handle edit mode if URL parameters are present
    <?php if ($isEditMode): ?>
    initializeEditMode();
    <?php endif; ?>

    document.getElementById('addHandoverItemBtn')?.addEventListener('click', addHandoverItemFromForm);
    document.getElementById('resetHandoverFormBtn')?.addEventListener('click', resetHandoverForm);
});

function fetchHolidays() {
    // Fetch holidays from the server
    fetch('<?= $base ?>php/scripts/leave/holidays/get_holidays.php?entityID=<?= $entityID ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.holidays) {
                // Store holiday dates as simple date strings
                holidayDates = data.holidays.map(h => h.date);
                console.log('Holidays loaded:', holidayDates);
            }
        })
        .catch(error => {
            console.error('Error fetching holidays:', error);
            // Continue without holidays
            holidayDates = [];
        });
}

function initializeLeaveApplication() {
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');

    if (startDateInput) {
        startDateInput.setAttribute('min', today);
    }
    if (endDateInput) {
        endDateInput.setAttribute('min', today);
    }

    // Leave type selection
    document.querySelectorAll('.leave-type-card').forEach(card => {
        card.addEventListener('click', function() {
            selectLeaveType(this);
        });
    });

    // Half day checkbox
    const halfDayCheckbox = document.getElementById('halfDayLeave');
    if (halfDayCheckbox) {
        halfDayCheckbox.addEventListener('change', function() {
            const halfDayOptions = document.getElementById('halfDayOptions');
            if (halfDayOptions) {
                halfDayOptions.style.display = this.checked ? 'block' : 'none';
            }
            updateSummary();
        });
    }

    // Date validation
    if (startDateInput) {
        startDateInput.addEventListener('change', function() {
            // Auto-set end date to start date for single day leave
            if (!endDateInput.value) {
                endDateInput.value = this.value;
            }
            validateDates();
        });
    }
    if (endDateInput) {
        endDateInput.addEventListener('change', validateDates);
    }
}

function selectLeaveType(card) {
    // Check if card is disabled
    if (card.dataset.disabled === 'true') {
        showAlert('This leave type has no available balance. Please select another leave type.', 'warning');
        return;
    }

    // Remove previous selection
    document.querySelectorAll('.leave-type-card').forEach(c => c.classList.remove('selected'));

    // Add selection to clicked card
    card.classList.add('selected');

    // Store selected leave type data
    workflowSelectedLeaveType = {
        id: card.dataset.leaveTypeId,
        entitlementId: card.dataset.entitlementId,
        name: card.querySelector('.card-title').textContent,
        maxDays: parseInt(card.dataset.maxDays),
        availableDays: parseInt(card.dataset.availableDays)
    };

    // Update hidden fields
    document.getElementById('leaveTypeId').value = workflowSelectedLeaveType.id;
    document.getElementById('leaveEntitlementId').value = workflowSelectedLeaveType.entitlementId;

    // Enable next button
    document.getElementById('nextStepBtn').disabled = false;

    // Show success message
    showAlert(`Selected ${workflowSelectedLeaveType.name}. Available: ${workflowSelectedLeaveType.availableDays} days`, 'success');

    updateSummary();
    evaluateHandoverRequirement();
}

function validateDates() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;

    if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);

        if (end < start) {
            showAlert('End date cannot be before start date', 'error');
            return false;
        }

        // Check if dates are in the future
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (start < today) {
            showAlert('Leave start date cannot be in the past', 'error');
            return false;
        }

        // Check available days (using working days, not total days)
        if (workflowSelectedLeaveType) {
            const workingDays = calculateLeaveDays(startDate, endDate);
            if (workingDays > workflowSelectedLeaveType.availableDays) {
                showAlert(`You only have ${workflowSelectedLeaveType.availableDays} working days available for ${workflowSelectedLeaveType.name}. Please reduce your leave period.`, 'error');
                return false;
            }

            if (workingDays > workflowSelectedLeaveType.maxDays) {
                showAlert(`Maximum ${workflowSelectedLeaveType.maxDays} working days allowed per application for ${workflowSelectedLeaveType.name}. Please reduce your leave period.`, 'error');
                return false;
            }
        }

        updateSummary();
        evaluateHandoverRequirement();
        return true;
    }
    return false;
}

/**
 * Check for overlapping leave applications via AJAX
 */
function checkOverlappingLeaves(startDate, endDate) {
    const employeeId = document.getElementById('employeeId')?.value ||
                      document.querySelector('[name="employeeId"]')?.value;
    const entityId = document.getElementById('entityId')?.value ||
                    document.querySelector('[name="entityId"]')?.value;

    if (!employeeId || !entityId || !startDate || !endDate) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'check_overlap');
    formData.append('employeeId', employeeId);
    formData.append('entityId', entityId);
    formData.append('startDate', startDate);
    formData.append('endDate', endDate);

    fetch('<?= $base ?>php/scripts/leave/applications/check_leave_overlap.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success === false && data.overlappingApplications && data.overlappingApplications.length > 0) {
            // Format overlap message for display
            let overlapMsg = `You have ${data.overlappingApplications.length} overlapping leave application(s):\n\n`;
            data.overlappingApplications.forEach((app, index) => {
                const start = new Date(app.startDate).toLocaleDateString();
                const end = new Date(app.endDate).toLocaleDateString();
                overlapMsg += `${index + 1}. Application #${app.leaveApplicationID} (${app.leaveTypeName || 'Leave'}) - ${start} to ${end} (${app.leaveStatusName || 'Unknown'})\n`;
            });
            overlapMsg += '\nPlease cancel the existing application(s) or choose different dates.';

            showAlert(overlapMsg, 'error');
        }
    })
    .catch(error => {
        console.error('Error checking leave overlap:', error);
        // Don't show error to user, just log it
    });
}

function calculateLeaveDays(startDate, endDate) {
    const start = new Date(startDate);
    const end = new Date(endDate);

    // Calculate total calendar days
    const timeDiff = end.getTime() - start.getTime();
    const totalDays = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;

    // Calculate working days (excluding weekends and holidays)
    let workingDays = 0;
    let weekendDays = 0;
    let holidayCount = 0;
    let currentDate = new Date(start);

    while (currentDate <= end) {
        const dayOfWeek = currentDate.getDay(); // 0 = Sunday, 6 = Saturday
        const dateString = currentDate.toISOString().split('T')[0]; // Format: YYYY-MM-DD

        if (dayOfWeek !== 0 && dayOfWeek !== 6) {
            // It's a weekday (Monday-Friday)
            // Check if it's a holiday
            if (holidayDates.includes(dateString)) {
                holidayCount++;
            } else {
                workingDays++;
            }
        } else {
            // It's a weekend
            weekendDays++;
        }

        // Move to next day
        currentDate.setDate(currentDate.getDate() + 1);
    }

    // Update summary display
    document.getElementById('totalDays').textContent = totalDays;
    document.getElementById('workingDays').textContent = workingDays;
    document.getElementById('weekendDays').textContent = weekendDays;
    document.getElementById('holidayDays').textContent = holidayCount;

    // Update hidden input with working days (not total days)
    const totalDaysInput = document.getElementById('totalDays');
    if (totalDaysInput) {
        // Store working days in the hidden field (despite the name, we use it for working days)
        totalDaysInput.value = workingDays;
    }

    // Return working days (this is what should be used for leave calculation)
    return workingDays;
}

function updateSummary() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;

    if (startDate && endDate) {
        calculateLeaveDays(startDate, endDate);
    }
}

function nextStep() {
    if (validateCurrentStep()) {
        if (workflowCurrentStep < WORKFLOW_TOTAL_STEPS) {
            // Hide current step
            document.getElementById(`step${workflowCurrentStep}`).classList.remove('active');

            // Show next step
            workflowCurrentStep++;
            document.getElementById(`step${workflowCurrentStep}`).classList.add('active');

            // Update progress indicator
            updateProgressIndicator();
            updateNavigationButtons();

            // Clear alerts
            clearAlerts();

            // Update review step if we're on the final step
            if (workflowCurrentStep === WORKFLOW_TOTAL_STEPS) {
                updateReviewStep();
            }
        }
    }
}

function prevStep() {
    if (workflowCurrentStep > 1) {
        // Hide current step
        document.getElementById(`step${workflowCurrentStep}`).classList.remove('active');

        // Show previous step
        workflowCurrentStep--;
        document.getElementById(`step${workflowCurrentStep}`).classList.add('active');

        // Update progress indicator
        updateProgressIndicator();
        updateNavigationButtons();

        // Clear alerts
        clearAlerts();
    }
}

function validateCurrentStep() {
    switch (workflowCurrentStep) {
        case 1:
            if (!workflowSelectedLeaveType) {
                showAlert('Please select a leave type', 'error');
                return false;
            }
            if (workflowSelectedLeaveType.availableDays <= 0) {
                showAlert('Selected leave type has no available balance', 'error');
                return false;
            }
            break;
        case 2:
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const halfDayLeave = document.getElementById('halfDayLeave').checked;

            if (!startDate || !endDate) {
                showAlert('Please select both start and end dates', 'error');
                return false;
            }
            if (!validateDates()) {
                return false;
            }
            if (halfDayLeave && !document.getElementById('halfDayPeriod').value) {
                showAlert('Please select a half day period', 'error');
                return false;
            }
            break;
        case 3:
            const reason = document.getElementById('leaveReason').value.trim();
            if (!reason) {
                showAlert('Please provide a reason for your leave', 'error');
                return false;
            }
            if (reason.length < 10) {
                showAlert('Please provide a more detailed reason (at least 10 characters)', 'error');
                return false;
            }
            break;
        case 4:
            if (handoverState.required && handoverState.items.length === 0) {
                showAlert('A structured handover is required for this request. Please add at least one item and assign it to a colleague.', 'error');
                return false;
            }
            if (handoverState.items.length > 0) {
                const invalidItem = handoverState.items.find(item => !item.assignees || item.assignees.length === 0);
                if (invalidItem) {
                    showAlert(`Please assign at least one colleague to "${invalidItem.title}".`, 'error');
                    return false;
                }
            }
            break;
    }
    return true;
}

function updateProgressIndicator() {
    document.querySelectorAll('.step-indicator .step').forEach((step, index) => {
        if (index + 1 <= workflowCurrentStep) {
            step.classList.add('active');
        } else {
            step.classList.remove('active');
        }
    });
}

function updateNavigationButtons() {
    const prevBtn = document.getElementById('prevStepBtn');
    const nextBtn = document.getElementById('nextStepBtn');
    const submitBtn = document.getElementById('submitApplicationBtn');

    prevBtn.style.display = workflowCurrentStep > 1 ? 'inline-block' : 'none';
    nextBtn.style.display = workflowCurrentStep < WORKFLOW_TOTAL_STEPS ? 'inline-block' : 'none';
    submitBtn.style.display = workflowCurrentStep === WORKFLOW_TOTAL_STEPS ? 'inline-block' : 'none';
}

function updateReviewStep() {
    // Update leave details
    document.getElementById('reviewLeaveType').textContent = workflowSelectedLeaveType ? workflowSelectedLeaveType.name : '-';
    document.getElementById('reviewStartDate').textContent = document.getElementById('startDate').value || '-';
    document.getElementById('reviewEndDate').textContent = document.getElementById('endDate').value || '-';

    // Display working days (excluding weekends and holidays) instead of total days
    const workingDaysText = document.getElementById('workingDays').textContent || '-';
    const weekendDaysText = document.getElementById('weekendDays').textContent || '0';
    const holidayDaysText = document.getElementById('holidayDays').textContent || '0';

    let daysBreakdown = `${workingDaysText} working days`;
    if (parseInt(weekendDaysText) > 0 || parseInt(holidayDaysText) > 0) {
        daysBreakdown += ' (';
        const excluded = [];
        if (parseInt(weekendDaysText) > 0) {
            excluded.push(`${weekendDaysText} weekend`);
        }
        if (parseInt(holidayDaysText) > 0) {
            excluded.push(`${holidayDaysText} holiday`);
        }
        daysBreakdown += excluded.join(', ') + ' days excluded)';
    }

    document.getElementById('reviewTotalDays').textContent = daysBreakdown;

    const halfDayLeave = document.getElementById('halfDayLeave').checked;
    document.getElementById('reviewHalfDay').textContent = halfDayLeave ?
        (document.getElementById('halfDayPeriod').value || 'Yes') : 'No';

    // Update additional information
    document.getElementById('reviewReason').textContent = document.getElementById('leaveReason').value || '-';
    document.getElementById('reviewEmergencyContact').textContent = document.getElementById('emergencyContact').value || '-';
    document.getElementById('reviewHandoverNotes').textContent = document.getElementById('handoverNotes').value || '-';

    const reviewHandoverStatus = document.getElementById('reviewHandoverStatus');
    const reviewHandoverItems = document.getElementById('reviewHandoverItems');

    if (reviewHandoverStatus) {
        const statusClass = handoverState.items.length === 0
            ? 'bg-secondary'
            : (handoverState.required ? 'bg-danger' : 'bg-info');
        reviewHandoverStatus.className = `badge ${statusClass}`;
        reviewHandoverStatus.textContent = handoverState.required
            ? 'Required'
            : (handoverState.items.length > 0 ? 'Included' : 'Not required');
    }

    if (reviewHandoverItems) {
        if (handoverState.items.length === 0) {
            reviewHandoverItems.innerHTML = '<p class="text-muted mb-0">No structured handover items captured.</p>';
        } else {
            const list = handoverState.items.map((item, index) => {
                const assignees = item.assigneeNames && item.assigneeNames.length > 0
                    ? item.assigneeNames.join(', ')
                    : 'Not assigned';
                const due = item.dueDate ? `<span class="badge bg-light text-dark ms-2">Due ${new Date(item.dueDate).toLocaleDateString()}</span>` : '';
                return `
                    <div class="border rounded p-3 mb-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>${index + 1}. ${item.title}</strong>
                                <div class="text-muted small">${item.description || 'No additional instructions provided.'}</div>
                            </div>
                            <span class="badge bg-primary text-uppercase">${item.priority}</span>
                        </div>
                        <div class="mt-2 d-flex flex-wrap align-items-center small">
                            <span class="me-3">Assignees: <strong>${assignees}</strong></span>
                            ${due}
                        </div>
                    </div>
                `;
            }).join('');
            reviewHandoverItems.innerHTML = list;
        }
    }
}

function submitLeaveApplication() {
    if (!validateCurrentStep()) {
        return;
    }

    const form = document.getElementById('applyLeaveForm');
    if (!form) {
        showAlert('Unable to locate the apply leave form. Please refresh the page.', 'error');
        return;
    }

    syncHandoverPayload();

    const formData = new FormData(form);
    const startDate = formData.get('startDate');
    const endDate = formData.get('endDate');
    const employeeId = formData.get('employeeId');
    const entityId = formData.get('entityId');

    // Ensure submissionMode is set
    if (!formData.has('submissionMode')) {
        formData.append('submissionMode', 'submit');
    }

    const submitBtn = document.getElementById('submitApplicationBtn');
    const originalText = submitBtn.innerHTML;

    // Check for overlapping leaves before submission
    if (startDate && endDate && employeeId && entityId) {
        submitBtn.innerHTML = '<i class="ri-loader-4-line spin me-1"></i>Checking...';
        submitBtn.disabled = true;

        const checkFormData = new FormData();
        checkFormData.append('action', 'check_overlap');
        checkFormData.append('employeeId', employeeId);
        checkFormData.append('entityId', entityId);
        checkFormData.append('startDate', startDate);
        checkFormData.append('endDate', endDate);

        // Check for overlaps first
        fetch('<?= $base ?>php/scripts/leave/applications/check_leave_overlap.php', {
            method: 'POST',
            body: checkFormData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success === false && data.hasOverlap) {
                // Format overlap message
                let overlapMsg = `You already have ${data.overlappingApplications.length} overlapping leave application(s):\n\n`;
                data.overlappingApplications.forEach((app, index) => {
                    const start = new Date(app.startDate).toLocaleDateString();
                    const end = new Date(app.endDate).toLocaleDateString();
                    overlapMsg += `${index + 1}. Application #${app.leaveApplicationID} (${app.leaveTypeName || 'Leave'}) - ${start} to ${end} (${app.leaveStatusName || 'Unknown'})\n`;
                });
                overlapMsg += '\nPlease cancel the existing application(s) before submitting a new one, or choose different dates.';

                showAlert(overlapMsg, 'error');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                return;
            }

            // No overlap, proceed with submission
            proceedWithLeaveSubmission(formData, submitBtn, originalText);
        })
        .catch(error => {
            console.error('Error checking overlap:', error);
            // If check fails, proceed with submission anyway (server will catch it)
            proceedWithLeaveSubmission(formData, submitBtn, originalText);
        });
    } else {
        // If dates not available, proceed directly
        proceedWithLeaveSubmission(formData, submitBtn, originalText);
    }
}

function proceedWithLeaveSubmission(formData, submitBtn, originalText) {
    // Show loading state
    submitBtn.innerHTML = '<i class="ri-loader-4-line spin me-1"></i>Submitting...';
    submitBtn.disabled = true;

    // Debug: Log form data before submission
    console.log('[LEAVE DEBUG] Starting leave application submission...');
    console.log('[LEAVE DEBUG] Form data:', Object.fromEntries(formData));
    console.log('[LEAVE DEBUG] Submission URL:', '<?= $base ?>php/scripts/leave/applications/submit_leave_application.php');

    // Submit application via AJAX
    fetch('<?= $base ?>php/scripts/leave/applications/submit_leave_application.php', {
        method: 'POST',
        body: formData
    })
    .then(async response => {
        console.log('[LEAVE DEBUG] Response status:', response.status, response.statusText);
        console.log('[LEAVE DEBUG] Response headers:', Object.fromEntries(response.headers.entries()));

        // Check if response is OK
        if (!response.ok) {
            const errorText = await response.text();
            console.error('[LEAVE DEBUG] HTTP Error Response:', errorText.substring(0, 500));
            throw new Error(`HTTP error! status: ${response.status}, message: ${errorText.substring(0, 200)}`);
        }

        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        console.log('[LEAVE DEBUG] Response content-type:', contentType);

        if (contentType && contentType.includes('application/json')) {
            const jsonData = await response.json();
            console.log('[LEAVE DEBUG] JSON Response received:', jsonData);
            return jsonData;
        } else {
            // If not JSON, try to parse as text first
            const text = await response.text();
            console.warn('[LEAVE DEBUG] Response is not JSON, received:', text.substring(0, 500));
            try {
                const parsed = JSON.parse(text);
                console.log('[LEAVE DEBUG] Parsed JSON:', parsed);
                return parsed;
            } catch (e) {
                console.error('[LEAVE DEBUG] JSON Parse Error:', e);
                throw new Error('Invalid response format. Expected JSON but got: ' + text.substring(0, 200));
            }
        }
    })
    .then(data => {
        console.log('[LEAVE DEBUG] Final submission response:', data);
        console.log('[LEAVE DEBUG] Notifications sent:', data.notificationsSent);
        console.log('[LEAVE DEBUG] Workflow processed:', data.workflowProcessed);
        console.log('[LEAVE DEBUG] Leave Application ID:', data.leaveApplicationId);

        if (data.debug) {
            console.log('[LEAVE DEBUG] Debug info:', data.debug);
            console.log('[LEAVE DEBUG] Approvers count:', data.debug.approversCount);
            console.log('[LEAVE DEBUG] Notified count:', data.debug.notifiedCount);

            if (data.debug.approversCount > 0 && data.debug.notifiedCount === 0) {
                console.error('[LEAVE DEBUG] WARNING: Approvers found but no notifications were sent!');
            }
        }

        if (data && data.success) {
            showAlert('Your leave application has been submitted successfully and is pending approval.', 'success');

            // Check if we're in a modal/iframe context
            const isInModal = window.self !== window.top || document.querySelector('.modal.show');

            if (isInModal) {
                // If in modal, try to close it
                const modalElement = document.querySelector('.modal.show');
                if (modalElement) {
                    const modalInstance = bootstrap.Modal.getInstance(modalElement);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                }
                // Also try to trigger a refresh in parent window
                if (window.parent && window.parent !== window) {
                    setTimeout(() => {
                        window.parent.location.reload();
                    }, 1000);
                }
            } else {
                // Redirect after a short delay
                setTimeout(() => {
                    window.location.href = '<?= "{$base}html/?s={$s}&ss={$ss}&p=apply_leave_workflow&success=1" ?>';
                }, 1500);
            }
        } else {
            let errorMsg = (data && data.message) ? data.message : 'Failed to submit leave application. Please try again.';

            // If there are overlapping applications, format the message nicely
            if (data && data.overlappingApplications && data.overlappingApplications.length > 0) {
                errorMsg = `You already have ${data.overlappingApplications.length} overlapping leave application(s):\n\n`;
                data.overlappingApplications.forEach((app, index) => {
                    const start = new Date(app.startDate).toLocaleDateString();
                    const end = new Date(app.endDate).toLocaleDateString();
                    errorMsg += `${index + 1}. Application #${app.leaveApplicationID} (${app.leaveTypeName || 'Leave'}) - ${start} to ${end} (${app.leaveStatusName || 'Unknown'})\n`;
                });
                errorMsg += '\nPlease cancel the existing application(s) before submitting a new one, or choose different dates.';
            }

            showAlert(errorMsg, 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Submission error:', error);
        showAlert('Unable to submit application: ' + error.message + '. Please check your connection and try again.', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

function showAlert(message, type = 'info') {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    // Show alert only in the dedicated alert container
    const container = document.getElementById('formAlertContainer');
    if (container) {
        container.appendChild(alertDiv);
    }

    // Auto dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

function clearAlerts() {
    const container = document.getElementById('formAlertContainer');
    if (container) {
        container.innerHTML = '';
    }
}

function initializeHandoverModule() {
    renderHandoverItems();
    updateHandoverRequirementUI();
}

function loadHandoverColleagues() {
    fetch('<?= $base ?>php/scripts/leave/utilities/get_filterable_employees.php?filterType=team')
        .then(response => response.json())
        .then(data => {
            if (!data || data.success === false || !Array.isArray(data.employees)) {
                throw new Error('Unable to load colleagues');
            }
            handoverColleagues = data.employees;
            const select = document.getElementById('handoverAssignees');
            if (select) {
                select.innerHTML = '';
                handoverColleagues.forEach(colleague => {
                    const option = document.createElement('option');
                    option.value = colleague.id;
                    option.textContent = colleague.name || `${colleague.firstName} ${colleague.surname}`;
                    select.appendChild(option);
                });
            }
        })
        .catch(() => {
            const select = document.getElementById('handoverAssignees');
            if (select) {
                select.innerHTML = '<option value="">Unable to load colleagues</option>';
            }
        });
}

function evaluateHandoverRequirement() {
    if (!workflowSelectedLeaveType) {
        updateHandoverRequirementUI();
        return;
    }

    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const entityId = document.querySelector('[name="entityId"]')?.value;

    if (!startDate || !endDate || !entityId) {
        updateHandoverRequirementUI();
        return;
    }

    const formData = new FormData();
    formData.append('leaveTypeId', workflowSelectedLeaveType.id);
    formData.append('entityId', entityId);
    formData.append('startDate', startDate);
    formData.append('endDate', endDate);

    fetch('<?= $base ?>php/scripts/leave/handovers/check_requirement.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        handoverState.required = data.required === true;
        handoverState.policy = data.policy || null;
        updateHandoverRequirementUI(data);
    })
    .catch(() => {
        updateHandoverRequirementUI();
    });
}

function updateHandoverRequirementUI(result = null) {
    const badge = document.getElementById('handoverRequirementBadge');
    const notice = document.getElementById('handoverRequirementNotice');
    if (!badge || !notice) {
        return;
    }

    let badgeClass = 'bg-secondary';
    let badgeText = 'Pending selection';
    let noticeText = 'Select a leave type and dates to determine if a handover is required.';
    let showNotice = false;

    if (handoverState.required) {
        badgeClass = 'bg-danger';
        badgeText = 'Handover required';
        noticeText = 'This leave requires a documented handover. Add at least one task with an assigned colleague.';
        showNotice = true;
    } else if (handoverState.items.length > 0) {
        badgeClass = 'bg-info';
        badgeText = 'Optional handover included';
        noticeText = 'You have added optional handover items to keep your team aligned.';
        showNotice = true;
    } else if (result && result.hasOwnProperty('required')) {
        badgeClass = 'bg-success';
        badgeText = 'No handover required';
        noticeText = 'No structured handover is required, but consider adding one for continuity.';
        showNotice = true;
    }

    badge.className = `badge ${badgeClass}`;
    badge.textContent = badgeText;

    if (showNotice) {
        notice.classList.remove('d-none');
        const noticeMessage = notice.querySelector('span');
        if (noticeMessage) {
            noticeMessage.textContent = noticeText;
        } else {
            notice.textContent = noticeText;
        }
    } else {
        notice.classList.add('d-none');
    }
}

function addHandoverItemFromForm() {
    const titleInput = document.getElementById('handoverItemTitle');
    const descriptionInput = document.getElementById('handoverItemDescription');
    const typeSelect = document.getElementById('handoverItemType');
    const prioritySelect = document.getElementById('handoverItemPriority');
    const dueDateInput = document.getElementById('handoverItemDueDate');
    const assigneeSelect = document.getElementById('handoverAssignees');

    const title = titleInput?.value.trim();
    if (!title) {
        showAlert('Please provide a title for the handover item.', 'error');
        return;
    }

    const selectedAssignees = assigneeSelect
        ? Array.from(assigneeSelect.selectedOptions).map(option => option.value).filter(Boolean)
        : [];

    if (selectedAssignees.length === 0) {
        showAlert('Please assign at least one colleague.', 'error');
        return;
    }

    const newItem = {
        id: `handover-${Date.now()}`,
        itemTitle: title,
        itemDescription: descriptionInput?.value.trim() || '',
        itemType: typeSelect?.value || 'function',
        priority: prioritySelect?.value || 'medium',
        dueDate: dueDateInput?.value || '',
        assignees: selectedAssignees,
        assigneeNames: selectedAssignees.map(id => {
            const colleague = handoverColleagues.find(c => String(c.id) === String(id));
            return colleague ? colleague.name : 'Colleague';
        })
    };

    handoverState.items.push(newItem);
    renderHandoverItems();
    resetHandoverForm();
    showAlert('Handover item added successfully.', 'success');
}

function resetHandoverForm() {
    document.getElementById('handoverItemTitle').value = '';
    document.getElementById('handoverItemDescription').value = '';
    document.getElementById('handoverItemType').value = 'function';
    document.getElementById('handoverItemPriority').value = 'medium';
    document.getElementById('handoverItemDueDate').value = '';
    const assigneeSelect = document.getElementById('handoverAssignees');
    if (assigneeSelect) {
        Array.from(assigneeSelect.options).forEach(option => option.selected = false);
    }
}

function renderHandoverItems() {
    const listContainer = document.getElementById('handoverItemsList');
    const counter = document.getElementById('handoverItemsCounter');

    if (!listContainer) {
        return;
    }

    if (handoverState.items.length === 0) {
        listContainer.innerHTML = '<p class="text-muted mb-0">No handover items added yet. Use the form above to capture responsibilities.</p>';
    } else {
        listContainer.innerHTML = handoverState.items.map(item => {
            const dueBadge = item.dueDate
                ? `<span class="badge bg-light text-dark ms-2">Due ${new Date(item.dueDate).toLocaleDateString()}</span>`
                : '';
            const assignees = item.assigneeNames && item.assigneeNames.length > 0
                ? item.assigneeNames.join(', ')
                : 'Not assigned';
            return `
                <div class="border rounded p-3 mb-2 position-relative">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>${item.itemTitle}</strong>
                            <div class="text-muted small">${item.itemDescription || 'No description provided.'}</div>
                        </div>
                        <div>
                            <span class="badge bg-primary text-uppercase">${item.priority}</span>
                            ${dueBadge}
                        </div>
                    </div>
                    <div class="mt-2 small text-muted">
                        Assigned to: <strong>${assignees}</strong>
                    </div>
                    <button type="button" class="btn btn-sm btn-link text-danger position-absolute top-0 end-0 me-2 mt-2"
                            onclick="removeHandoverItem('${item.id}')">
                        <i class="ri-close-circle-line me-1"></i>Remove
                    </button>
                </div>
            `;
        }).join('');
    }

    if (counter) {
        counter.textContent = `${handoverState.items.length} item${handoverState.items.length === 1 ? '' : 's'}`;
    }

    updateHandoverRequirementUI();
    syncHandoverPayload();
}

function removeHandoverItem(itemId) {
    handoverState.items = handoverState.items.filter(item => item.id !== itemId);
    renderHandoverItems();
}

function syncHandoverPayload() {
    const payloadInput = document.getElementById('handoverPayload');
    if (!payloadInput) {
        return;
    }

    if (handoverState.items.length === 0) {
        payloadInput.value = '';
        return;
    }

    const payload = {
        items: handoverState.items.map(item => ({
            itemTitle: item.itemTitle,
            itemDescription: item.itemDescription,
            itemType: item.itemType,
            priority: item.priority,
            dueDate: item.dueDate,
            assignees: item.assignees
        }))
    };

    payloadInput.value = JSON.stringify(payload);
}

function initializeEditMode() {
    // Get URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const leaveTypeId = urlParams.get('leaveTypeId');
    const entitlementId = urlParams.get('entitlementId');
    const startDate = urlParams.get('startDate');
    const endDate = urlParams.get('endDate');
    const reason = urlParams.get('reason');

    // Pre-select leave type
    if (leaveTypeId) {
        const leaveTypeCard = document.querySelector(`[data-leave-type-id="${leaveTypeId}"]`);
        if (leaveTypeCard) {
            selectLeaveType(leaveTypeCard);
        }
    }

    // Pre-fill dates
    if (startDate) {
        document.getElementById('startDate').value = startDate;
    }
    if (endDate) {
        document.getElementById('endDate').value = endDate;
    }

    // Pre-fill reason
    if (reason) {
        document.getElementById('leaveReason').value = decodeURIComponent(reason);
    }

    // Update summary
    updateSummary();

    // Show success message
    showAlert('Edit mode activated. You can modify your leave application details.', 'info');
}
</script>
