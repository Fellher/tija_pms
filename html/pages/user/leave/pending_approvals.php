<?php
/**
 * Pending Leave Approvals
 * Approver dashboard to review and action leave requests
 */
$targetApplicationID = (isset($_GET['id']) && !empty($_GET['id'])) ? (int)Utility::clean_string($_GET['id']) : null;
if (!$isValidUser) {
    // var_dump($getString);
    $getString .= "&id={$targetApplicationID}";
    $_SESSION['returnURL'] = $getString;
    Alert::info("You need to be logged in to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    // var_dump($_SESSION['returnURL']);
    return;
}

$userID = $userDetails->ID;

/**
 * Normalize approval records (object/array) into consistent associative arrays.
 */
function normalizeApprovalRecord($record, $source = 'manager')
{
    $data = is_object($record) ? (array)$record : $record;
    $data['__source'] = $source;
    return $data;
}

/**
 * Check if current user is an approver for a leave application
 *
 * @param int $leaveApplicationID Leave application ID
 * @param int $userID Current user ID
 * @param object $DBConn Database connection
 * @return array Role information: ['isApprover' => bool, 'stepID' => int|null, 'stepOrder' => int|null, 'hasActed' => bool, 'canApprove' => bool, 'instanceID' => int|null, 'policyID' => int|null]
 */
function checkUserApproverRole($leaveApplicationID, $userID, $DBConn) {
    $result = array(
        'isApprover' => false,
        'stepID' => null,
        'stepOrder' => null,
        'hasActed' => false,
        'canApprove' => false,
        'instanceID' => null,
        'policyID' => null,
        'isHrManager' => false
    );

    if (empty($leaveApplicationID) || empty($userID)) {
        return $result;
    }

    // Get leave application to check entity
    $leaveApp = Leave::leave_applications_full(
        array('leaveApplicationID' => $leaveApplicationID),
        true,
        $DBConn
    );



    if (!$leaveApp) {
        return $result;
    }

    $leaveApp = is_object($leaveApp) ? (array)$leaveApp : $leaveApp;
    $entityID = isset($leaveApp['entityID']) ? (int)$leaveApp['entityID'] : null;

    // Check if user is HR manager
    $isHrManager = Employee::is_hr_manager($userID, $DBConn, $entityID);
    $result['isHrManager'] = $isHrManager;

    // Check for workflow instance
    $lapsedCheck = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_instances LIKE 'Lapsed'", array());
    $hasLapsedColumn = ($lapsedCheck && count($lapsedCheck) > 0);

    $whereClause = "leaveApplicationID = ?";
    $params = array(array($leaveApplicationID, 'i'));

    if ($hasLapsedColumn) {
        $whereClause .= " AND Lapsed = 'N'";
    }

    $workflowInstance = $DBConn->fetch_all_rows(
        "SELECT * FROM tija_leave_approval_instances WHERE {$whereClause}",
        $params
    );

    if (!$workflowInstance || count($workflowInstance) === 0) {
        // No workflow - HR manager can approve
        if ($isHrManager) {
            $result['isApprover'] = true;
            $result['canApprove'] = true;
        }
        return $result;
    }

    $inst = is_object($workflowInstance[0]) ? (array)$workflowInstance[0] : $workflowInstance[0];
    $instanceID = isset($inst['instanceID']) ? (int)$inst['instanceID'] : null;
    $policyID = isset($inst['policyID']) ? (int)$inst['policyID'] : null;

    $result['instanceID'] = $instanceID;
    $result['policyID'] = $policyID;

    if (!$instanceID || !$policyID) {
        // Invalid workflow instance
        if ($isHrManager) {
            $result['isApprover'] = true;
            $result['canApprove'] = true;
        }
        return $result;
    }

    // Get employee who submitted the application
    $employeeID = isset($leaveApp['employeeID']) ? (int)$leaveApp['employeeID'] : null;
    if (!$employeeID) {
        return $result;
    }

    $employee = Employee::employees(array('ID' => $employeeID), true, $DBConn);
    if (!$employee) {
        return $result;
    }

    // Get all approvers for the policy (saved approvers)
    $approvers = Leave::get_workflow_approvers($policyID, $DBConn);

    // Check if user is assigned as approver (saved approvers)
    foreach ($approvers as $approver) {
        $approverUserID = isset($approver['approverUserID']) ? (int)$approver['approverUserID'] : null;
        if ($approverUserID === $userID) {
            $result['isApprover'] = true;
            $result['stepID'] = isset($approver['stepID']) ? (int)$approver['stepID'] : null;
            $result['stepOrder'] = isset($approver['stepOrder']) ? (int)$approver['stepOrder'] : null;
            break;
        }
    }

    // If not found in saved approvers, check dynamic approvers
    if (!$result['isApprover']) {
        // Use resolve_dynamic_workflow_approvers to get accurate stepID and stepOrder
        $dynamicApprovers = Leave::resolve_dynamic_workflow_approvers($policyID, $employeeID, $DBConn);

        if ($dynamicApprovers && count($dynamicApprovers) > 0) {
            foreach ($dynamicApprovers as $approver) {
                $approverUserID = isset($approver['approverUserID']) ? (int)$approver['approverUserID'] : null;
                if ($approverUserID === $userID) {
                    $result['isApprover'] = true;
                    $result['stepID'] = isset($approver['stepID']) ? (int)$approver['stepID'] : null;
                    $result['stepOrder'] = isset($approver['stepOrder']) ? (int)$approver['stepOrder'] : null;

                    break;
                }
            }
        }

        // Fallback: If still not found, check workflow steps manually
        if (!$result['isApprover']) {
            // Get workflow steps to check for dynamic approver types
            $steps = Leave::leave_approval_steps(
                array('policyID' => $policyID, 'Suspended' => 'N'),
                false,
                $DBConn
            );

            if ($steps && count($steps) > 0) {
                // Get current step from instance
                $currentStepOrder = isset($inst['currentStepOrder']) ? (int)$inst['currentStepOrder'] : 1;
                $approvalType = isset($inst['approvalType']) ? $inst['approvalType'] : 'parallel';

                foreach ($steps as $step) {
                    $stepObj = is_object($step) ? $step : (object)$step;
                    $stepType = $stepObj->stepType ?? '';
                    $stepOrder = isset($stepObj->stepOrder) ? (int)$stepObj->stepOrder : 0;
                    $stepID = isset($stepObj->stepID) ? (int)$stepObj->stepID : null;

                    // For HR managers, check all steps (parallel workflow allows this)
                    // For others, only check current or past steps in sequential workflow
                    if ($approvalType === 'sequential' && $stepType !== 'hr_manager' && $stepOrder > $currentStepOrder) {
                        continue; // Future steps in sequential workflow
                    }

                    // Check if user matches dynamic approver role
                    $isDynamicApprover = false;

                    switch ($stepType) {
                        case 'supervisor':
                            // Check if user is the employee's supervisor
                            if (!empty($employee->supervisorID) && (int)$employee->supervisorID === (int)$userID) {
                                $isDynamicApprover = true;
                            }
                            break;

                        case 'department_head':
                            // Check if user is the employee's department head
                            $deptHead = Employee::get_employee_department_head($employeeID, $DBConn);
                            if ($deptHead && !empty($deptHead->ID) && (int)$deptHead->ID === (int)$userID) {
                                $isDynamicApprover = true;
                            }
                            break;

                        case 'project_manager':
                            // For now, fall back to supervisor check
                            if (!empty($employee->supervisorID) && (int)$employee->supervisorID === (int)$userID) {
                                $isDynamicApprover = true;
                            }
                            break;

                        case 'hr_manager':
                            // Check if user is HR manager
                            if ($isHrManager) {
                                $isDynamicApprover = true;
                            }
                            break;
                    }

                    if ($isDynamicApprover) {
                        $result['isApprover'] = true;
                        $result['stepID'] = $stepID;
                        $result['stepOrder'] = $stepOrder;
                        error_log("checkUserApproverRole - Found dynamic approver (fallback): UserID={$userID}, StepID={$stepID}, StepOrder={$stepOrder}");
                        break;
                    }
                }
            }
        }
    }

    // HR managers can approve even if not explicitly in workflow
    // Find their step if not already found
    if (!$result['isApprover'] && $isHrManager) {
        $result['isApprover'] = true;

        // Try to find the HR step in the workflow
        $steps = Leave::leave_approval_steps(
            array('policyID' => $policyID, 'Suspended' => 'N'),
            false,
            $DBConn
        );

        if ($steps && count($steps) > 0) {
            // Find the last HR manager step or the final step
            $hrStep = null;
            $finalStep = null;
            $maxStepOrder = 0;

            foreach ($steps as $step) {
                $stepObj = is_object($step) ? $step : (object)$step;
                $stepType = $stepObj->stepType ?? '';
                $stepOrder = isset($stepObj->stepOrder) ? (int)$stepObj->stepOrder : 0;

                if ($stepOrder > $maxStepOrder) {
                    $maxStepOrder = $stepOrder;
                    $finalStep = $stepObj;
                }

                if ($stepType === 'hr_manager') {
                    $hrStep = $stepObj;
                }
            }

            // Prefer HR step, otherwise use final step
            $targetStep = $hrStep ?? $finalStep;
            if ($targetStep) {
                $result['stepID'] = isset($targetStep->stepID) ? (int)$targetStep->stepID : null;
                $result['stepOrder'] = isset($targetStep->stepOrder) ? (int)$targetStep->stepOrder : null;
            }
        }
    }

    // Check if user has already acted
    if ($result['isApprover'] && $result['stepID']) {
        $hasActed = LeaveNotifications::hasApproverActed($instanceID, $result['stepID'], $userID, $DBConn);
        $result['hasActed'] = $hasActed;
        $result['canApprove'] = !$hasActed;
    } elseif ($result['isApprover']) {
        // HR manager or no step ID - check if they've acted on any step
        $actions = $DBConn->fetch_all_rows(
            "SELECT actionID FROM tija_leave_approval_actions WHERE instanceID = ? AND approverUserID = ? LIMIT 1",
            array(
                array($instanceID, 'i'),
                array($userID, 'i')
            )
        );
        $result['hasActed'] = ($actions && count($actions) > 0);
        $result['canApprove'] = !$result['hasActed'];
    }

    return $result;
}

$pendingApprovalsMap = array();
$orgDataID = $userDetails->orgDataID ?? null;
$entityID = $userDetails->entityID ?? null;

if (!isset($hrManagerScope) || !is_array($hrManagerScope)) {
    $hrManagerScope = Employee::get_hr_manager_scope($userID, $DBConn);
}
$isHrManager = $hrManagerScope['isHRManager'];
$hrManagerScopes = $hrManagerScope['scopes'] ?? array();
$isDepartmentHead = Employee::is_department_head($userID, $DBConn);
$userBusinessUnitID = $userDetails->businessUnitID ?? null;

// HR managers can see all pending approvals within their organisation/entity.
if ($isHrManager) {
    $processedScopes = array();
    $effectiveScopes = $hrManagerScopes;

    if (empty($effectiveScopes)) {
        $effectiveScopes[] = array(
            'entityID' => $entityID,
            'orgDataID' => $orgDataID,
            'global' => false
        );
    }

    foreach ($effectiveScopes as $scopeEntry) {
        $scopeOrgID = $scopeEntry['orgDataID'] ?? $orgDataID;
        if (!$scopeOrgID) {
            continue;
        }

        if (!empty($scopeEntry['global'])) {
            $scopedEntities = Data::entities_full(array('orgDataID' => $scopeOrgID, 'Suspended' => 'N'), false, $DBConn);
            if ($scopedEntities) {
                foreach ($scopedEntities as $entityRow) {
                    $scopeKey = $scopeOrgID . ':' . $entityRow->entityID;
                    if (isset($processedScopes[$scopeKey])) {
                        continue;
                    }
                    $processedScopes[$scopeKey] = true;

                    $hrApprovals = Leave::get_all_pending_approvals($scopeOrgID, $entityRow->entityID, $DBConn);
                    if ($hrApprovals) {
                        foreach ($hrApprovals as $approval) {
                            $approval = normalizeApprovalRecord($approval, 'hr');
                            $pendingApprovalsMap[$approval['leaveApplicationID']] = $approval;
                        }
                    }
                }
            }
            continue;
        }

        $scopeEntityID = $scopeEntry['entityID'] ?? null;
        if (!$scopeEntityID) {
            continue;
        }

        $scopeKey = $scopeOrgID . ':' . $scopeEntityID;
        if (isset($processedScopes[$scopeKey])) {
            continue;
        }
        $processedScopes[$scopeKey] = true;

        $hrApprovals = Leave::get_all_pending_approvals($scopeOrgID, $scopeEntityID, $DBConn);
        if ($hrApprovals) {
            foreach ($hrApprovals as $approval) {
                $approval = normalizeApprovalRecord($approval, 'hr');
                $pendingApprovalsMap[$approval['leaveApplicationID']] = $approval;
            }
        }
    }
}

// Direct managers can view their team's pending approvals.
$managerApprovals = Leave::get_pending_approvals_for_manager($userID, $DBConn, $hrManagerScope);

// var_dump($managerApprovals);
if ($managerApprovals) {
    foreach ($managerApprovals as $approval) {
        $approval = normalizeApprovalRecord($approval, 'manager');
        $pendingApprovalsMap[$approval['leaveApplicationID']] = $approval;
    }
}



// Department heads fallback – if user oversees a business unit, surface those approvals.
if (empty($pendingApprovalsMap) && $userBusinessUnitID && $isDepartmentHead) {
    $departmentApprovals = Leave::get_pending_approvals_for_department($userBusinessUnitID, $DBConn);
    if ($departmentApprovals) {
        foreach ($departmentApprovals as $approval) {
            $approval = normalizeApprovalRecord($approval, 'department');
            $pendingApprovalsMap[$approval['leaveApplicationID']] = $approval;
        }
    }
}

// Filter down to approvals the user is actually allowed to action.
// foreach ($pendingApprovalsMap as $applicationID => $approval) {
//     $permissionCheck = Leave::check_leave_application_permissions((object)$approval, $userID);
//     $sourceLabel = $approval['__source'] ?? 'manager';

//     $isAuthorised = !empty($permissionCheck['canApprove'])
//         || $isHrManager
//         || ($sourceLabel === 'department' && $isDepartmentHead);

//     if (!$isAuthorised) {
//         unset($pendingApprovalsMap[$applicationID]);
//     }
// }
// var_dump($pendingApprovalsMap);
$pendingApprovals = array_values($pendingApprovalsMap);
$pendingCount = count($pendingApprovals);

// Pre-fetch workflow rejection status for all pending approvals (for performance)
$workflowRejectionCache = array();
foreach ($pendingApprovals as $approval) {
    $leaveID = (int)($approval['leaveApplicationID'] ?? 0);
    if ($leaveID > 0) {
        $instanceID = $approval['instanceID'] ?? null;
        $policyID = $approval['policyID'] ?? null;

        // Fetch if not already in approval data
        if ((!$instanceID || !$policyID)) {
            $lapsedCheck = $DBConn->fetch_all_rows("SHOW COLUMNS FROM tija_leave_approval_instances LIKE 'Lapsed'", array());
            $hasLapsedColumn = ($lapsedCheck && count($lapsedCheck) > 0);
            $whereClause = "leaveApplicationID = ?";
            $params = array(array($leaveID, 'i'));
            if ($hasLapsedColumn) {
                $whereClause .= " AND Lapsed = 'N'";
            }
            $instance = $DBConn->fetch_all_rows(
                "SELECT instanceID, policyID FROM tija_leave_approval_instances WHERE {$whereClause} LIMIT 1",
                $params
            );
            if ($instance && count($instance) > 0) {
                $instData = is_object($instance[0]) ? (array)$instance[0] : $instance[0];
                $instanceID = $instData['instanceID'] ?? null;
                $policyID = $instData['policyID'] ?? null;
            }
        }

        if ($instanceID && $policyID) {
            $appStatus = Leave::check_workflow_approval_status($instanceID, $policyID, $DBConn);
            $workflowRejectionCache[$leaveID] = isset($appStatus['hasRejection']) && $appStatus['hasRejection'];
        } else {
            $workflowRejectionCache[$leaveID] = false;
        }
    }
}

// Load target application data early if provided
$targetApplicationData = null;
$targetApplicationRole = null;
$targetApprovalStatus = null;

if ($targetApplicationID) {
    $targetApplicationData = Leave::leave_applications_full(
        array(
            'leaveApplicationID' => $targetApplicationID
        ),
        true,
        $DBConn
    );

    if ($targetApplicationData) {
        // Check user's approver role
        $targetApplicationRole = checkUserApproverRole($targetApplicationID, $userID, $DBConn);

        /**
         * Browser-friendly debug output for target application role
         * targetApplicationRole: <pre class="mb-0 bg-light border rounded p-2"><?php echo htmlspecialchars(print_r($targetApplicationRole, true)); ?></pre>
         */?>
        <!-- <div class="alert alert-secondary p-2 mb-2 small">
            <div class="fw-bold mb-1">Target Application Role Debug Info</div>
            <ul class="mb-1 ps-3">
                <li><strong>targetApplicationRole: </strong> <pre class="mb-0 bg-light border rounded p-2"><?php echo htmlspecialchars(print_r($targetApplicationRole, true)); ?></pre></li>
            </ul>
        </div> -->
        <?php
        // Get workflow approval status
        if ($targetApplicationRole['instanceID'] && $targetApplicationRole['policyID']) {

            // var_dump($targetApplicationRole['instanceID']);
            // var_dump($targetApplicationRole['policyID']);
            $targetApprovalStatus = Leave::check_workflow_approval_status(
                $targetApplicationRole['instanceID'],
                $targetApplicationRole['policyID'],
                $DBConn
            );
        }

        // Additional context for approver UX
        $targetAppArray = is_object($targetApplicationData) ? (array)$targetApplicationData : $targetApplicationData;
        $targetEmployeeID = isset($targetAppArray['employeeID']) ? (int)$targetAppArray['employeeID'] : 0;

        // Past leave summary for this employee (current year)
        $targetEmployeeLeaveAnalytics = $targetEmployeeID ? Leave::get_leave_analytics($targetEmployeeID, $DBConn) : null;

        // Recent leave applications for this employee (last 3)
        $targetEmployeeRecentLeaves = array();
        if ($targetEmployeeID) {
            $recentLeavesRaw = Leave::leave_applications_full(
                array(
                    'employeeID' => $targetEmployeeID,
                    'Lapsed' => 'N',
                    'Suspended' => 'N'
                ),
                false,
                $DBConn
            );
            if ($recentLeavesRaw) {
                foreach ($recentLeavesRaw as $row) {
                    $rowArr = is_object($row) ? (array)$row : $row;
                    // Exclude the current application
                    if ((int)($rowArr['leaveApplicationID'] ?? 0) === (int)$targetApplicationID) {
                        continue;
                    }
                    $targetEmployeeRecentLeaves[] = $rowArr;
                }
                // Sort by start date descending and keep top 3
                usort($targetEmployeeRecentLeaves, function($a, $b) {
                    return strcmp($b['startDate'], $a['startDate']);
                });
                $targetEmployeeRecentLeaves = array_slice($targetEmployeeRecentLeaves, 0, 3);
            }
        }

        // Team leave snapshot for the current approver (direct reports + HR scope)
        $targetTeamLeaveAnalytics = Leave::get_team_leave_analytics($userID, $DBConn, $hrManagerScope ?? null);

        // Supporting documents for this leave application
        $targetApplicationDocuments = Leave::get_leave_application_documents($targetApplicationID, $DBConn);
    }
}


// var_dump($targetApplicationData);
// var_dump($targetApprovalStatus);
function formatDateRange($start, $end)
{
    return sprintf(
        '%s - %s',
        Utility::date_format($start),
        Utility::date_format($end)
    );
}
?>

<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-12 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <h2 class="mb-1">
                    <i class="ri-task-line text-primary me-2"></i>
                    Pending Leave Approvals
                </h2>
                <p class="text-muted mb-0">
                    Review leave requests awaiting your decision.
                </p>
            </div>
            <div>
                <span class="badge bg-primary fs-6 py-2 px-3">
                    <?php echo $pendingCount; ?> pending
                </span>
            </div>
        </div>
    </div>

    <?php
    if ($targetApplicationID && $targetApplicationData): ?>
        <?php
            $targetApp = is_object($targetApplicationData) ? (array)$targetApplicationData : $targetApplicationData;
            $targetEmployee = Employee::employees(array('ID' => $targetApp['employeeID']), true, $DBConn);
            $targetEmployeeName = $targetEmployee ? ($targetEmployee->FirstName . ' ' . $targetEmployee->Surname) : 'Employee';
            $targetStatusID = isset($targetApp['leaveStatusID']) ? (int)$targetApp['leaveStatusID'] : 3;
            $targetStatusName = $targetApp['leaveStatusName'] ?? 'Pending';
            $targetStartDate = Utility::date_format($targetApp['startDate']);
            $targetEndDate = Utility::date_format($targetApp['endDate']);
            $targetAppliedOn = $targetApp['dateApplied'] ? date('M j, Y g:i a', strtotime($targetApp['dateApplied'])) : '-';
        ?>


        <div class="card border-0 shadow-sm mb-4" id="targetApplicationCard">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ri-file-text-line me-2"></i>
                        Leave Application #<?php echo htmlspecialchars($targetApplicationID); ?>
                    </h5>
                    <span class="badge bg-light text-dark">
                        <?php echo htmlspecialchars($targetStatusName); ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <h6 class="text-muted text-uppercase small mb-1">Employee</h6>
                        <div class="fw-semibold"><?php echo htmlspecialchars($targetEmployeeName); ?></div>
                        <?php if ($targetEmployee && isset($targetEmployee->jobTitle)): ?>
                            <small class="text-muted"><?php echo htmlspecialchars($targetEmployee->jobTitle); ?></small>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted text-uppercase small mb-1">Leave Type</h6>
                        <div><?php echo htmlspecialchars($targetApp['leaveTypeName'] ?? 'Unknown'); ?></div>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted text-uppercase small mb-1">Leave Dates</h6>
                        <div><?php echo htmlspecialchars($targetStartDate . ' - ' . $targetEndDate); ?></div>
                        <small class="text-muted"><?php echo htmlspecialchars($targetApp['noOfDays'] ?? 0); ?> day(s)</small>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted text-uppercase small mb-1">Submitted</h6>
                        <div><?php echo htmlspecialchars($targetAppliedOn); ?></div>
                    </div>
                </div>

                <?php if (!empty($targetApp['leaveComments'])): ?>
                    <div class="mb-3">
                        <h6 class="text-muted text-uppercase small mb-2">Reason for Leave</h6>
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($targetApp['leaveComments'])); ?></p>
                    </div>
                <?php endif; ?>

                <div class="row g-3 mb-4">
                    <?php if (!empty($targetEmployeeLeaveAnalytics)): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white pb-2">
                                    <h6 class="mb-0 text-uppercase small text-muted">
                                        <i class="ri-bar-chart-2-line me-1 text-primary"></i>Past Leave Summary (This Year)
                                    </h6>
                                </div>
                                <div class="card-body pt-2">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">Total days taken</span>
                                        <span class="fw-semibold">
                                            <?php echo (float)($targetEmployeeLeaveAnalytics['totalLeaveDays'] ?? 0); ?>
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">Applications</span>
                                        <span class="fw-semibold">
                                            <?php echo (int)($targetEmployeeLeaveAnalytics['totalApplications'] ?? 0); ?>
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">Approval rate</span>
                                        <span class="fw-semibold">
                                            <?php echo isset($targetEmployeeLeaveAnalytics['approvalRate'])
                                                ? $targetEmployeeLeaveAnalytics['approvalRate'] . '%'
                                                : 'N/A'; ?>
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">Avg. duration</span>
                                        <span class="fw-semibold">
                                            <?php echo isset($targetEmployeeLeaveAnalytics['averageDuration'])
                                                ? $targetEmployeeLeaveAnalytics['averageDuration'] . ' day(s)'
                                                : 'N/A'; ?>
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">Avg. notice</span>
                                        <span class="fw-semibold">
                                            <?php echo isset($targetEmployeeLeaveAnalytics['averageAdvanceNotice'])
                                                ? $targetEmployeeLeaveAnalytics['averageAdvanceNotice'] . ' day(s)'
                                                : 'N/A'; ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($targetEmployeeLeaveAnalytics['peakLeaveMonths'])): ?>
                                        <div class="mt-1">
                                            <span class="text-muted small d-block">Peak months</span>
                                            <span class="fw-semibold small">
                                                <?php echo htmlspecialchars($targetEmployeeLeaveAnalytics['peakLeaveMonths']); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($targetEmployeeRecentLeaves)): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white pb-2">
                                    <h6 class="mb-0 text-uppercase small text-muted">
                                        <i class="ri-time-line me-1 text-primary"></i>Recent Leave History
                                    </h6>
                                </div>
                                <div class="card-body pt-2">
                                    <ul class="list-unstyled mb-0 small">
                                        <?php foreach ($targetEmployeeRecentLeaves as $recent): ?>
                                            <?php
                                                $recentStart = Utility::date_format($recent['startDate']);
                                                $recentEnd = Utility::date_format($recent['endDate']);
                                                $recentDays = $recent['noOfDays'] ?? '-';
                                                $recentStatus = $recent['leaveStatusName'] ?? '';
                                            ?>
                                            <li class="mb-2">
                                                <div class="fw-semibold">
                                                    <?php echo htmlspecialchars($recent['leaveTypeName'] ?? 'Leave'); ?>
                                                </div>
                                                <div class="text-muted">
                                                    <?php echo "{$recentStart} - {$recentEnd} ({$recentDays} day(s))"; ?>
                                                </div>
                                                <div>
                                                    <span class="badge bg-light text-dark border">
                                                        <?php echo htmlspecialchars($recentStatus); ?>
                                                    </span>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($targetTeamLeaveAnalytics) && !empty($targetTeamLeaveAnalytics['totalTeamMembers'])): ?>
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-white pb-2">
                                    <h6 class="mb-0 text-uppercase small text-muted">
                                        <i class="ri-group-line me-1 text-primary"></i>Your Team Snapshot
                                    </h6>
                                </div>
                                <div class="card-body pt-2">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">Team members</span>
                                        <span class="fw-semibold">
                                            <?php echo (int)$targetTeamLeaveAnalytics['totalTeamMembers']; ?>
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">Pending approvals</span>
                                        <span class="fw-semibold">
                                            <?php echo (int)$targetTeamLeaveAnalytics['pendingApprovals']; ?>
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">Approved this month</span>
                                        <span class="fw-semibold">
                                            <?php echo (int)$targetTeamLeaveAnalytics['approvedThisMonth']; ?>
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted small">Currently on leave</span>
                                        <span class="fw-semibold">
                                            <?php echo (int)$targetTeamLeaveAnalytics['currentlyOnLeave']; ?>
                                        </span>
                                    </div>
                                    <div class="mt-1">
                                        <span class="text-muted small d-block">Team utilization</span>
                                        <span class="fw-semibold small">
                                            <?php echo isset($targetTeamLeaveAnalytics['teamLeaveUtilization'])
                                                ? $targetTeamLeaveAnalytics['teamLeaveUtilization'] . '%'
                                                : 'N/A'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php
                    $handoverRequired = $targetApp['handoverRequired'] ?? 'N';
                    $handoverNotes = $targetApp['handoverNotes'] ?? '';
                ?>

                <?php if ($handoverRequired === 'Y' || !empty($handoverNotes)): ?>
                    <div class="mb-3">
                        <h6 class="text-muted text-uppercase small mb-2">
                            <i class="ri-route-line me-1 text-primary"></i>Handover & Impacted Tasks
                        </h6>
                        <?php if ($handoverRequired === 'Y'): ?>
                            <p class="mb-1 small text-muted">
                                This application requires a formal handover. Review the notes below and, if needed, open the detailed handover report.
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($handoverNotes)): ?>
                            <p class="mb-1"><?php echo nl2br(htmlspecialchars($handoverNotes)); ?></p>
                        <?php endif; ?>
                        <?php if ($handoverRequired === 'Y'): ?>
                            <a href="<?php echo "{$base}html/?s={$s}&ss={$ss}&p=handover_report&applicationID={$targetApplicationID}"; ?>"
                               class="btn btn-sm btn-outline-secondary mt-1">
                                <i class="ri-clipboard-line me-1"></i> View detailed handover & tasks
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($targetApplicationDocuments)): ?>
                    <div class="mb-4">
                        <h6 class="text-muted text-uppercase small mb-2">
                            <i class="ri-attachment-2 me-1 text-primary"></i>Supporting Documents
                        </h6>
                        <ul class="list-unstyled mb-0 small">
                            <?php foreach ($targetApplicationDocuments as $doc): ?>
                                <?php
                                    $docObj = is_object($doc) ? $doc : (object)$doc;
                                    $fileName = $docObj->fileName ?? 'Document';
                                    $filePath = $docObj->filePath ?? '';
                                    $href = $base . ltrim($filePath, '/');
                                ?>
                                <li class="mb-1 d-flex align-items-center">
                                    <i class="ri-file-line me-2 text-muted"></i>
                                    <a href="<?php echo htmlspecialchars($href); ?>" target="_blank" class="text-decoration-none">
                                        <?php echo htmlspecialchars($fileName); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($targetApprovalStatus && isset($targetApprovalStatus['steps'])):

                    // var_dump($targetApprovalStatus);
                    ?>
                    <div class="mb-4">
                        <h6 class="text-muted text-uppercase small mb-3">Approval Workflow</h6>
                        <div id="workflowStatusContainer">
                            <?php
                                $steps = $targetApprovalStatus['steps'];
                                foreach ($steps as $step):
                                    // var_dump($step);
                                    $stepID = isset($step['stepID']) ? (int)$step['stepID'] : null;
                                    $stepOrder = isset($step['stepOrder']) ? (int)$step['stepOrder'] : null;
                                    $stepName = isset($step['stepName']) ? htmlspecialchars($step['stepName']) : 'Step ' . $stepOrder;
                                    $stepStatus = isset($step['stepStatus']) ? $step['stepStatus'] : 'pending';


                                    // var_dump($stepStatus);
                                    $approvers = isset($step['approvers']) ? $step['approvers'] : array();
                                    $isCurrentUserStep = ($targetApplicationRole && $targetApplicationRole['stepID'] == $step['stepID']);?>

                                    <div class="workflow-step mb-3 p-3 border rounded <?php echo $isCurrentUserStep ? 'border-primary bg-primary-subtle text-primary-emphasis' : 'shadow-lg'; ?>">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0">
                                                <?php echo $stepName; ?>
                                                <?php if ($isCurrentUserStep): ?>
                                                    <span class="badge bg-info ms-2">Your Step</span>
                                                <?php endif; ?>
                                            </h6>
                                            <span class="badge <?php
                                                echo $stepStatus === 'approved' ? 'bg-success' :
                                                    ($stepStatus === 'rejected' ? 'bg-danger' : 'bg-warning text-dark');
                                            ?>">
                                                <?php echo ucfirst($stepStatus); ?>
                                            </span>
                                        </div>
                                        <?php if (!empty($approvers)):
                                            // var_dump($approvers);
                                            ?>
                                            <div class="approvers-list">
                                                <?php foreach ($approvers as $approver): ?>
                                                    <?php
                                                        $approverUserID = isset($approver['approverUserID']) ? (int)$approver['approverUserID'] : null;
                                                        $approverName = isset($approver['approverName']) ? htmlspecialchars($approver['approverName']) : 'Unknown';
                                                        $hasActed = isset($approver['hasActed']) ? $approver['hasActed'] : false;
                                                        $action = isset($approver['action']) ? $approver['action'] : null;
                                                        $comments = isset($approver['comments']) ? $approver['comments'] : null;
                                                        $actionDate = isset($approver['actionDate']) ? $approver['actionDate'] : null;
                                                        $isCurrentUser = ($approverUserID == $userID);
                                                    ?>



                                                    <div class="approver-item d-flex justify-content-between align-items-start mb-2 p-2 bg-white rounded <?php echo $isCurrentUser ? 'border border-primary shadow-sm' : ''; ?>">
                                                        <div class="flex-grow-1">
                                                            <div class="d-flex align-items-center">
                                                                <span class="fw-semibold me-2"><?php echo $approverName; ?></span>
                                                                <?php if ($isCurrentUser): ?>
                                                                    <span class="badge bg-info">You</span>
                                                                <?php endif; ?>
                                                                <?php if ($hasActed && $action): ?>
                                                                    <span class="badge ms-2 <?php echo strtolower($action) === 'approved' ? 'bg-success' : 'bg-danger'; ?>">
                                                                        <?php echo ucfirst($action); ?>
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-secondary ms-2">Pending</span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <?php if ($comments): ?>
                                                                <p class="text-muted small mb-1 mt-1"><?php echo nl2br(htmlspecialchars($comments)); ?></p>
                                                            <?php endif; ?>
                                                            <?php if ($actionDate): ?>
                                                                <small class="text-muted">
                                                                    <i class="ri-time-line me-1"></i>
                                                                    <?php echo date('M j, Y g:i a', strtotime($actionDate)); ?>
                                                                </small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php
                    // Check if application has been rejected by any previous approver
                    $hasBeenRejected = isset($targetApprovalStatus['hasRejection']) && $targetApprovalStatus['hasRejection'];

                    // Determine if current user is HR Manager or Final approver
                    $isHrManagerOrFinal = false;
                    if ($targetApplicationRole) {
                        $isHrManagerOrFinal = $targetApplicationRole['isHrManager'] === true;

                        // Also check if they are final step approver
                        if (!$isHrManagerOrFinal && isset($targetApprovalStatus['maxStepOrder'])) {
                            $userStepOrder = $targetApplicationRole['stepOrder'] ?? 0;
                            $maxStepOrder = $targetApprovalStatus['maxStepOrder'] ?? 0;
                            $isHrManagerOrFinal = ($userStepOrder >= $maxStepOrder && $maxStepOrder > 0);
                        }
                    }

                    // HR Manager/Final approver cannot approve if already rejected by someone else
                    $canActOnApplication = $targetApplicationRole && $targetApplicationRole['canApprove'];
                    if ($isHrManagerOrFinal && $hasBeenRejected && $canActOnApplication) {
                        $canActOnApplication = false;
                    }
                ?>

                <?php if ($canActOnApplication): ?>
                    <div class="d-flex gap-2 justify-content-end">
                        <button
                            type="button"
                            class="btn btn-success approval-action"
                            data-action="approve"
                            data-leave-id="<?php echo $targetApplicationID; ?>"
                            data-employee-name="<?php echo htmlspecialchars($targetEmployeeName, ENT_QUOTES); ?>"
                            data-step-id="<?php echo isset($targetApplicationRole['stepID']) && $targetApplicationRole['stepID'] !== null ? (int)$targetApplicationRole['stepID'] : ''; ?>"
                            data-step-order="<?php echo isset($targetApplicationRole['stepOrder']) && $targetApplicationRole['stepOrder'] !== null ? (int)$targetApplicationRole['stepOrder'] : ''; ?>">
                            <i class="ri-check-line me-1"></i> Approve
                        </button>
                        <button
                            type="button"
                            class="btn btn-danger approval-action"
                            data-action="reject"
                            data-leave-id="<?php echo $targetApplicationID; ?>"
                            data-employee-name="<?php echo htmlspecialchars($targetEmployeeName, ENT_QUOTES); ?>"
                            data-step-id="<?php echo isset($targetApplicationRole['stepID']) && $targetApplicationRole['stepID'] !== null ? (int)$targetApplicationRole['stepID'] : ''; ?>"
                            data-step-order="<?php echo isset($targetApplicationRole['stepOrder']) && $targetApplicationRole['stepOrder'] !== null ? (int)$targetApplicationRole['stepOrder'] : ''; ?>">
                            <i class="ri-close-line me-1"></i> Reject
                        </button>
                    </div>
                <?php elseif ($isHrManagerOrFinal && $hasBeenRejected): ?>
                    <div class="alert alert-danger mb-0">
                        <i class="ri-alert-line me-2"></i>
                        <strong>Application Already Rejected</strong>
                        <p class="mb-0 mt-1 small">This application has been rejected by a previous approver in the workflow. You cannot take further action on a rejected application.</p>
                    </div>
                <?php elseif ($targetApplicationRole && $targetApplicationRole['hasActed']): ?>
                    <div class="alert alert-info mb-0">
                        <i class="ri-information-line me-2"></i>
                        You have already acted on this application.
                    </div>
                <?php elseif (!$targetApplicationRole || !$targetApplicationRole['isApprover']): ?>
                    <div class="alert alert-warning mb-0">
                        <i class="ri-alert-line me-2"></i>
                        You are not assigned as an approver for this application.
                    </div>
                <?php endif; ?>

                <div class="mt-3">
                    <a href="<?php echo "{$base}html/?s=user&ss=leave&p=view_leave_application&id={$targetApplicationID}"; ?>" class="text-decoration-none">
                        <i class="ri-external-link-line me-1"></i> View full application details
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($pendingCount === 0): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ri-calendar-check-line fs-1 text-success mb-3"></i>
                <h5 class="text-muted mb-1">No requests awaiting your approval.</h5>
                <?php if ($targetApplicationID): ?>
                    <p class="text-muted mb-0">
                        Leave application <strong>#<?php echo htmlspecialchars($targetApplicationID); ?></strong>
                        <?php if ($targetApplicationData): ?>
                            is currently <strong><?php echo htmlspecialchars($targetApplicationData->leaveStatusName ?? 'processed'); ?></strong>.
                            It may have been actioned already or reassigned.
                        <?php else: ?>
                            could not be located or has already been fully processed.
                        <?php endif; ?>
                        <br>
                        <a href="<?php echo "{$base}html/?s=user&ss=leave&p=view_leave_application&id={$targetApplicationID}"; ?>" class="text-decoration-none">
                            View application details
                        </a>
                    </p>
                <?php else: ?>
                    <p class="text-muted mb-0">You'll be notified when new approvals require your attention.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Employee</th>
                                <th scope="col">Leave Type</th>
                                <th scope="col">Dates</th>
                                <th scope="col">Days</th>
                                <th scope="col">Current Step</th>
                                <th scope="col">Handover</th>
                                <th scope="col">Submitted</th>
                                <th scope="col" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingApprovals as $approval): ?>
                                <?php
                                    $leaveID = (int)($approval['leaveApplicationID'] ?? 0);
                                    $submitted = !empty($approval['dateApplied']) ? date('M j, Y g:i a', strtotime($approval['dateApplied'])) : '-';
                                    $comments = $approval['leaveComments'] ?? '';
                                    $sourceLabel = $approval['__source'] ?? 'manager';

                                    // Determine step label based on workflow
                                    $currentStepOrder = isset($approval['currentStepOrder']) ? (int)$approval['currentStepOrder'] : null;
                                    $maxStepOrder = isset($approval['maxStepOrder']) ? (int)$approval['maxStepOrder'] : null;

                                    // Get rejection status from cache
                                    $appHasRejection = isset($workflowRejectionCache[$leaveID]) && $workflowRejectionCache[$leaveID];

                                    // Check if current user is HR Manager or final approver for this application
                                    $userIsHrOrFinal = $isHrManager || ($currentStepOrder >= $maxStepOrder && $maxStepOrder > 0);

                                    if ($sourceLabel === 'hr') {
                                        if ($currentStepOrder !== null && $maxStepOrder !== null && $currentStepOrder >= $maxStepOrder) {
                                            $stepLabel = 'Pending HR Manager Approval';
                                        } else {
                                            $stepLabel = 'HR Review';
                                        }
                                    } elseif ($sourceLabel === 'department') {
                                        $stepLabel = 'Department Review';
                                    } else {
                                        // Manager approval - check if it's waiting for HR
                                        if ($currentStepOrder !== null && $maxStepOrder !== null && $currentStepOrder >= $maxStepOrder) {
                                            $stepLabel = 'Pending HR Manager Approval';
                                        } else {
                                            $stepName = isset($approval['stepName']) ? $approval['stepName'] : null;
                                            $stepLabel = $stepName ? htmlspecialchars($stepName) : 'Manager Approval';
                                        }
                                    }
                                ?>
                                <?php
                                    $isTargeted = $targetApplicationID && ((int)$approval['leaveApplicationID'] === $targetApplicationID);
                                    $fsmBadge = '<span class="text-muted">Not required</span>';
                                    if (($approval['handoverRequired'] ?? 'N') === 'Y') {
                                        if (class_exists('LeaveHandoverFSM') && function_exists('get_fsm_state_badge')) {
                                            $rowState = LeaveHandoverFSM::get_current_state($leaveID, $DBConn);
                                            $fsmBadge = $rowState
                                                ? get_fsm_state_badge($rowState->currentState ?? 'ST_00')
                                                : '<span class="badge bg-secondary text-uppercase">Draft</span>';
                                        } else {
                                            $fsmBadge = '<span class="badge bg-secondary text-uppercase">Draft</span>';
                                        }
                                    }
                                ?>
                                <tr class="<?php echo $isTargeted ? 'table-warning' : ''; ?>">
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold"><?php echo htmlspecialchars($approval['employeeName'] ?? 'Employee'); ?></span>
                                            <small class="text-muted"><?php echo htmlspecialchars($approval['jobTitle'] ?? ''); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span><?php echo htmlspecialchars($approval['leaveTypeName'] ?? 'Unknown'); ?></span>
                                            <?php if (!empty($approval['leaveTypeDescription'])): ?>
                                                <small class="text-muted"><?php echo htmlspecialchars($approval['leaveTypeDescription']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars(formatDateRange($approval['startDate'], $approval['endDate'])); ?></td>
                                    <td><?php echo htmlspecialchars($approval['noOfDays']); ?></td>
                                    <td>
                                        <span class="badge bg-info text-dark">
                                            <?php echo htmlspecialchars($stepLabel); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo ($approval['handoverRequired'] ?? 'N') === 'Y'
                                            ? $fsmBadge
                                            : '<span class="text-muted">Not required</span>'; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($submitted); ?></td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-secondary view-leave-details"
                                                data-leave-id="<?php echo $leaveID; ?>">
                                                <i class="ri-eye-line me-1"></i> View
                                            </button>
                                            <?php if ($userIsHrOrFinal && $appHasRejection): ?>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    disabled
                                                    title="Application has been rejected by a previous approver">
                                                    <i class="ri-close-circle-line me-1"></i> Already Rejected
                                                </button>
                                            <?php else:
                                                // Get approver role for this specific leave application
                                                $rowApproverRole = checkUserApproverRole($leaveID, $userID, $DBConn);
                                                $rowStepID = (isset($rowApproverRole['stepID']) && $rowApproverRole['stepID'] !== null) ? (int)$rowApproverRole['stepID'] : '';
                                                $rowStepOrder = (isset($rowApproverRole['stepOrder']) && $rowApproverRole['stepOrder'] !== null) ? (int)$rowApproverRole['stepOrder'] : '';

                                                error_log("Table row approver role for leaveID {$leaveID}, userID {$userID}: stepID=" . ($rowStepID ?: 'NULL') . ", stepOrder=" . ($rowStepOrder ?: 'NULL'));
                                            ?>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-success approval-action"
                                                    data-action="approve"
                                                    data-leave-id="<?php echo $leaveID; ?>"
                                                    data-employee-name="<?php echo htmlspecialchars($approval['employeeName'] ?? 'Employee', ENT_QUOTES); ?>"
                                                    data-step-id="<?php echo $rowStepID; ?>"
                                                    data-step-order="<?php echo $rowStepOrder; ?>"
                                                >
                                                    <i class="ri-check-line me-1"></i> Approve
                                                </button>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-danger approval-action"
                                                    data-action="reject"
                                                    data-leave-id="<?php echo $leaveID; ?>"
                                                    data-employee-name="<?php echo htmlspecialchars($approval['employeeName'] ?? 'Employee', ENT_QUOTES); ?>"
                                                    data-step-id="<?php echo $rowStepID; ?>"
                                                    data-step-order="<?php echo $rowStepOrder; ?>"
                                                >
                                                    <i class="ri-close-line me-1"></i> Reject
                                                </button>
                                            <?php endif; ?>
                                        </div>
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

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approvalModalLabel">Approve Leave</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modalAction">
                <input type="hidden" id="modalLeaveID">
                <input type="text" name="modalStepID" class="form-control form-control-sm" id="modalStepID">
                <input type="text" name="modalStepOrder" class="form-control form-control-sm" id="modalStepOrder">
                <p class="mb-3">
                    <strong>Employee:</strong> <span id="modalEmployeeName"></span><br>
                    <strong>Action:</strong> <span id="modalActionText"></span>
                </p>
                <div class="mb-3">
                    <label for="modalComments" class="form-label">Comments</label>
                    <textarea class="form-control" id="modalComments" rows="3" placeholder="Add comments (required for rejection)"></textarea>
                    <div class="form-text">These comments will be attached to the approval record.</div>
                </div>
                <div class="alert alert-warning d-none" id="modalError"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="modalSubmitBtn">
                    <i class="ri-loader-4-line me-1 d-none" id="modalSpinner"></i>
                    <span id="modalSubmitText">Confirm</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Leave Details Modal -->
<div class="modal fade" id="leaveDetailsModal" tabindex="-1" aria-labelledby="leaveDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="leaveDetailsModalLabel">Leave Application Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="leaveDetailsLoading" class="d-flex align-items-center justify-content-center py-5">
                    <div class="spinner-border text-primary me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span>Loading leave application...</span>
                </div>
                <div id="leaveDetailsError" class="alert alert-danger d-none"></div>
                <div id="leaveDetailsContent" class="d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
(function(window, document) {
    const baseUrl = '<?php echo $base; ?>';
    const MAX_BOOTSTRAP_WAIT = 50;

    function waitForBootstrap(callback, attempt = 0) {
        if (window.bootstrap && typeof callback === 'function') {
            callback(window.bootstrap);
            return;
        }
        if (attempt >= MAX_BOOTSTRAP_WAIT) {
            console.warn('Bootstrap JavaScript runtime was not found. Modal behaviour is disabled.');
            return;
        }
        window.setTimeout(() => waitForBootstrap(callback, attempt + 1), 50);
    }

    waitForBootstrap(initializePendingApprovals);

    // Auto-scroll to target application if present
    <?php if ($targetApplicationID && $targetApplicationData): ?>
    window.addEventListener('load', function() {
        const targetCard = document.getElementById('targetApplicationCard');
        if (targetCard) {
            setTimeout(function() {
                targetCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }
    });
    <?php endif; ?>

    function initializePendingApprovals(bootstrapRef) {
        const approvalModalEl = document.getElementById('approvalModal');
        const leaveDetailsModalEl = document.getElementById('leaveDetailsModal');

        const approvalModal = approvalModalEl ? new bootstrapRef.Modal(approvalModalEl) : null;
        const leaveDetailsModal = leaveDetailsModalEl ? new bootstrapRef.Modal(leaveDetailsModalEl) : null;

        const modalActionInput = document.getElementById('modalAction');
        const modalLeaveInput = document.getElementById('modalLeaveID');
        const modalStepIDInput = document.getElementById('modalStepID');
        const modalStepOrderInput = document.getElementById('modalStepOrder');
        const modalCommentsInput = document.getElementById('modalComments');
        const modalEmployeeText = document.getElementById('modalEmployeeName');
        const modalActionText = document.getElementById('modalActionText');
        const modalSubmitBtn = document.getElementById('modalSubmitBtn');
        const modalSpinner = document.getElementById('modalSpinner');
        const modalSubmitText = document.getElementById('modalSubmitText');
        const modalError = document.getElementById('modalError');

        const leaveDetailsLoading = document.getElementById('leaveDetailsLoading');
        const leaveDetailsError = document.getElementById('leaveDetailsError');
        const leaveDetailsContent = document.getElementById('leaveDetailsContent');
        const leaveDetailsModalLabel = document.getElementById('leaveDetailsModalLabel');

        document.querySelectorAll('.approval-action').forEach(button => {
            if (button.dataset.boundApproval === '1') {
                return;
            }
            button.dataset.boundApproval = '1';

            button.addEventListener('click', () => {
                if (!approvalModal) {
                    if (typeof showToast === 'function') {
                        showToast('Unable to open approval modal.', 'error');
                    } else {
                        alert('Unable to open approval modal.');
                    }
                    return;
                }

                const action = button.dataset.action;
                const employeeName = button.dataset.employeeName || 'Employee';
                const stepID = button.dataset.stepId || button.getAttribute('data-step-id') || '';
                const stepOrder = button.dataset.stepOrder || button.getAttribute('data-step-order') || '';

                console.log("Button clicked - Step info:", {
                    stepID,
                    stepOrder,
                    stepIdAttr: button.dataset.stepId,
                    stepOrderAttr: button.dataset.stepOrder,
                    allDataAttrs: Object.keys(button.dataset)
                });

                modalActionInput.value = action;
                modalLeaveInput.value = button.dataset.leaveId;
                if (modalStepIDInput) modalStepIDInput.value = stepID;
                if (modalStepOrderInput) modalStepOrderInput.value = stepOrder;
                modalCommentsInput.value = '';
                modalEmployeeText.textContent = employeeName;
                modalActionText.textContent = action === 'approve' ? 'Approve leave request' : 'Reject leave request';
                modalSubmitText.textContent = action === 'approve' ? 'Approve Request' : 'Reject Request';
                modalSubmitBtn.classList.toggle('btn-danger', action === 'reject');
                modalSubmitBtn.classList.toggle('btn-primary', action === 'approve');
                modalError.classList.add('d-none');
                modalError.textContent = '';

                approvalModal.show();
            });
        });

        document.querySelectorAll('.view-leave-details').forEach(button => {
            if (button.dataset.boundView === '1') {
                return;
            }
            button.dataset.boundView = '1';

            button.addEventListener('click', () => {
                if (!leaveDetailsModal) {
                    if (typeof showToast === 'function') {
                        showToast('Unable to open leave details.', 'error');
                    } else {
                        alert('Unable to open leave details.');
                    }
                    return;
                }

                const leaveID = button.dataset.leaveId;
                if (!leaveID) {
                    if (typeof showToast === 'function') {
                        showToast('Leave application reference missing.', 'error');
                    } else {
                        alert('Leave application reference missing.');
                    }
                    return;
                }

                prepareLeaveDetailsModal();
                leaveDetailsModal.show();
                loadLeaveDetails(leaveID);
            });
        });

        if (modalSubmitBtn && !modalSubmitBtn.dataset.boundSubmit) {
            modalSubmitBtn.dataset.boundSubmit = '1';
            modalSubmitBtn.addEventListener('click', () => {
                const action = modalActionInput.value;
                const leaveID = modalLeaveInput.value;
                const comments = modalCommentsInput.value.trim();

                if (!action || !leaveID) {
                    showModalError('Missing approval information. Please try again.');
                    return;
                }

                if (action === 'reject' && comments.length === 0) {
                    showModalError('Please provide reasons for rejection.');
                    return;
                }

                setModalLoading(true);

                const formData = new FormData();
                formData.append('action', action);
                formData.append('leaveApplicationID', leaveID);
                formData.append('comments', comments);

                // Always include stepID and stepOrder (even if empty, backend will handle validation)
                const stepID = modalStepIDInput ? modalStepIDInput.value.trim() : '';
                const stepOrder = modalStepOrderInput ? modalStepOrderInput.value.trim() : '';

                // Always append, even if empty - backend needs to know if they were provided
                formData.append('stepID', stepID);
                formData.append('stepOrder', stepOrder);

                console.log("Submitting approval:", {
                    action,
                    leaveID,
                    stepID,
                    stepOrder,
                    stepIDType: typeof stepID,
                    stepOrderType: typeof stepOrder,
                    stepIDLength: stepID.length,
                    stepOrderLength: stepOrder.length
                });

                console.log("Form data:", {
                    formData: Object.fromEntries(formData.entries())
                });

                fetch(`${baseUrl}php/scripts/leave/applications/process_leave_approval_action.php`, {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Failed to process approval');
                    }

                    if (typeof showToast === 'function') {
                        showToast(data.message || 'Action completed', data.action === 'rejected' ? 'error' : 'success');
                    }
                    approvalModal.hide();
                    window.location.reload();
                })
                .catch(error => {
                    console.error(error);
                    showModalError(error.message || 'Unable to complete request');
                })
                .finally(() => {
                    setModalLoading(false);
                });
            });
        }

        function setModalLoading(isLoading) {
            if (!modalSubmitBtn) return;
            modalSubmitBtn.disabled = isLoading;
            if (modalSpinner) {
                modalSpinner.classList.toggle('d-none', !isLoading);
            }
            if (modalSubmitText) {
                modalSubmitText.textContent = isLoading
                    ? 'Processing...'
                    : (modalActionInput.value === 'approve' ? 'Approve Request' : 'Reject Request');
            }
        }

        function showModalError(message) {
            if (!modalError) return;
            modalError.textContent = message;
            modalError.classList.remove('d-none');
        }

        function prepareLeaveDetailsModal() {
            if (!leaveDetailsLoading || !leaveDetailsContent || !leaveDetailsError) return;
            leaveDetailsModalLabel.textContent = 'Leave Application Details';
            leaveDetailsLoading.classList.remove('d-none');
            leaveDetailsContent.classList.add('d-none');
            leaveDetailsError.classList.add('d-none');
            leaveDetailsError.textContent = '';
            leaveDetailsContent.innerHTML = '';
        }

        function loadLeaveDetails(leaveID) {
            const url = `${baseUrl}php/scripts/leave/applications/get_leave_application_detail.php?id=${encodeURIComponent(leaveID)}`;
            fetch(url, { credentials: 'same-origin' })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Unable to retrieve leave application');
                    }
                    renderLeaveDetails(data);
                })
                .catch(error => {
                    console.error(error);
                    displayLeaveDetailsError(error.message || 'Unable to retrieve leave application');
                })
                .finally(() => {
                    leaveDetailsLoading.classList.add('d-none');
                });
        }

        function displayLeaveDetailsError(message) {
            if (!leaveDetailsError) return;
            leaveDetailsError.textContent = message;
            leaveDetailsError.classList.remove('d-none');
        }

        function updateFsmStateView(application) {
            const badgeEl = leaveDetailsContent.querySelector('#fsmStateBadge');
            const timerEl = leaveDetailsContent.querySelector('#fsmTimerInfo');
            if (!badgeEl) {
                return;
            }
            badgeEl.innerHTML = '<span class="spinner-border spinner-border-sm text-primary"></span>';
            if (timerEl) {
                timerEl.textContent = '';
            }

            const appId = application.leaveApplicationID || application.id;
            const url = `${baseUrl}php/scripts/leave/handovers/get_fsm_state.php?leaveApplicationID=${encodeURIComponent(appId)}`;
            fetch(url, { credentials: 'same-origin' })
                .then(response => response.json())
                .then(result => {
                    if (!result.success || !result.state) {
                        throw new Error(result.message || 'Unable to load workflow state');
                    }
                    const state = result.state;
                    badgeEl.textContent = state.currentStateName || state.currentState || 'Unknown';
                    badgeEl.className = 'badge bg-light text-dark text-uppercase';
                    if (timerEl && state.timerInfo) {
                        timerEl.textContent = state.timerInfo.expired
                            ? 'Response timer expired'
                            : `Response due in ${state.timerInfo.remaining_hours ? Number(state.timerInfo.remaining_hours).toFixed(1) + 'h' : ''}`;
                    }
                })
                .catch(() => {
                    badgeEl.className = 'badge bg-danger text-uppercase';
                    badgeEl.textContent = 'Unavailable';
                    if (timerEl) {
                        timerEl.textContent = '';
                    }
                });
        }

        function renderLeaveDetails(payload) {
            if (!leaveDetailsContent) return;
            const application = payload.application || {};
            const employee = payload.employee || {};
            const approvals = payload.approvals || [];
            const attachments = payload.attachments || [];

            leaveDetailsModalLabel.textContent = `Leave Application #${sanitize(application.id)}`;

            const appliedOn = formatDateTime(application.dateApplied);
            const updatedOn = formatDateTime(application.lastUpdate);
            const dateRange = `${formatDate(application.startDate)} – ${formatDate(application.endDate)}`;
            const halfDayInfo = application.halfDayLeave === 'Y'
                ? `Yes (${sanitize(application.halfDayPeriod || 'unspecified')})`
                : 'No';

            const statusBadge = renderStatusBadge(application.statusId);

            let approvalsHtml = '<p class="text-muted mb-0">No approval actions recorded yet.</p>';
            if (approvals.length > 0) {
                approvalsHtml = approvals.map(approval => {
                    const approver = sanitize(approval.approverName || 'Approver');
                    const statusId = parseInt(approval.leaveStatusID || approval.approvalStatusID || 0, 10);
                    const badge = renderStatusBadge(statusId);
                    const actionDate = formatDateTime(approval.approvalDateAdded || approval.DateAdded);
                    const comments = approval.approversComments || approval.comments || '';
                    const commentHtml = comments
                        ? `<p class="mb-0">${sanitizeMultiline(comments)}</p>`
                        : '';

                    return `
                        <div class="timeline-item mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1">${approver}</h6>
                                <span>${badge}</span>
                            </div>
                            <p class="text-muted small mb-1"><i class="ri-time-line me-1"></i>${sanitize(actionDate)}</p>
                            ${commentHtml}
                        </div>
                    `;
                }).join('');
            }

            let attachmentsHtml = '<p class="text-muted mb-0">No supporting documents uploaded.</p>';
            if (attachments.length > 0) {
                attachmentsHtml = `
                    <ul class="list-unstyled mb-0">
                        ${attachments.map(file => {
                            const href = `${baseUrl}${sanitize(file.path || '').replace(/^\/+/, '')}`;
                            const name = sanitize(file.name || 'Document');
                            return `
                                <li class="mb-2 d-flex align-items-center">
                                    <i class="ri-file-line me-2 text-muted"></i>
                                    <a href="${href}" target="_blank" class="text-decoration-none">${name}</a>
                                </li>
                            `;
                        }).join('')}
                    </ul>
                `;
            }

            const handoverStatus = application.handoverStatus || 'pending';
            let handoverBadgeClass = 'bg-secondary';
            if (handoverStatus === 'completed') {
                handoverBadgeClass = 'bg-success';
            } else if (handoverStatus === 'in_progress') {
                handoverBadgeClass = 'bg-info';
            } else if (handoverStatus === 'partial') {
                handoverBadgeClass = 'bg-warning text-dark';
            }

            const handoverStatusHtml = application.handoverRequired === 'Y'
                ? `<span class="badge ${handoverBadgeClass} text-uppercase">${sanitize(handoverStatus)}</span>
                   <a class="btn btn-link btn-sm ps-0" href="<?= "{$base}html/?s={$s}&ss={$ss}&p=handover_report&applicationID=" ?>${application.leaveApplicationID}">
                       View handover report
                   </a>`
                : '<span class="text-muted">Not required</span>';

            const handoverWorkflowHtml = application.handoverRequired === 'Y'
                ? `<div class="col-sm-3">
                        <h6 class="text-muted text-uppercase small mb-1">Handover Workflow</h6>
                        <div id="fsmStateBadge" class="badge bg-light text-dark text-uppercase">Loading...</div>
                        <div class="text-muted small mt-1" id="fsmTimerInfo"></div>
                        <div class="mt-2">
                            <a class="btn btn-link btn-sm ps-0" href="<?= "{$base}html/?s={$s}&ss={$ss}&p=handover_audit_trail&applicationID=" ?>${application.leaveApplicationID}">
                                Audit Trail
                            </a>
                        </div>
                    </div>`
                : '';

            leaveDetailsContent.innerHTML = `
                <div class="row g-3">
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-sm-3">
                                        <h6 class="text-muted text-uppercase small mb-1">Status</h6>
                                        <div class="fs-5">${statusBadge}</div>
                                        <small class="text-muted">Last updated on ${sanitize(updatedOn)}</small>
                                    </div>
                                    <div class="col-sm-3">
                                        <h6 class="text-muted text-uppercase small mb-1">Leave Type</h6>
                                        <div class="fs-5">${sanitize(application.leaveType || 'N/A')}</div>
                                        <small class="text-muted">${sanitize(application.leaveTypeDescription || '')}</small>
                                    </div>
                                    <div class="col-sm-3">
                                        <h6 class="text-muted text-uppercase small mb-1">Leave Dates</h6>
                                        <div class="fs-5">${sanitize(dateRange)}</div>
                                        <small class="text-muted">${sanitize(application.noOfDays || '-')} day(s)</small>
                                    </div>
                                    <div class="col-sm-3">
                                        <h6 class="text-muted text-uppercase small mb-1">Submitted</h6>
                                        <div class="fs-6">${sanitize(appliedOn)}</div>
                                    </div>
                                    ${handoverWorkflowHtml}
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="ri-file-text-line me-2 text-primary"></i>Application Details</h5>
                            </div>
                            <div class="card-body">
                                <dl class="row">
                                    <dt class="col-sm-4">Reason for Leave</dt>
                                    <dd class="col-sm-8">${sanitizeMultiline(application.reason || 'Not provided')}</dd>

                                    <dt class="col-sm-4">Emergency Contact</dt>
                                    <dd class="col-sm-8">${sanitize(application.emergencyContact || 'Not provided')}</dd>

                                    <dt class="col-sm-4">Handover Notes</dt>
                                    <dd class="col-sm-8">${sanitizeMultiline(application.handoverNotes || 'Not provided')}</dd>

                                    <dt class="col-sm-4">Handover Status</dt>
                                    <dd class="col-sm-8">${handoverStatusHtml}</dd>

                                    <dt class="col-sm-4">Half Day</dt>
                                    <dd class="col-sm-8">${halfDayInfo}</dd>
                                </dl>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="ri-team-line me-2 text-primary"></i>Approval Workflow</h5>
                            </div>
                            <div class="card-body">
                                ${approvalsHtml}
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="ri-user-3-line me-2 text-primary"></i>Employee Details</h5>
                            </div>
                            <div class="card-body">
                                <dl class="row">
                                    <dt class="col-sm-5">Name</dt>
                                    <dd class="col-sm-7">${sanitize(employee.name || 'Employee')}</dd>

                                    <dt class="col-sm-5">Email</dt>
                                    <dd class="col-sm-7">${sanitize(employee.email || '-')}</dd>

                                    <dt class="col-sm-5">Organisation</dt>
                                    <dd class="col-sm-7">${sanitize(application.entityName || '-')}</dd>

                                    <dt class="col-sm-5">Leave Period</dt>
                                    <dd class="col-sm-7">${sanitize(application.leavePeriodName || '-')}</dd>
                                </dl>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="ri-attachment-2 me-2 text-primary"></i>Attachments</h5>
                            </div>
                            <div class="card-body">
                                ${attachmentsHtml}
                            </div>
                        </div>
                    </div>
                </div>
            `;

            if (application.handoverRequired === 'Y') {
                updateFsmStateView(application);
            }

            leaveDetailsContent.classList.remove('d-none');
        }

        function sanitize(value) {
            if (value === null || value === undefined) return '';
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function sanitizeMultiline(value) {
            return sanitize(value).replace(/\r?\n/g, '<br>');
        }

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            if (Number.isNaN(date.getTime())) return sanitize(dateStr);
            return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
        }

        function formatDateTime(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            if (Number.isNaN(date.getTime())) return sanitize(dateStr);
            return date.toLocaleString(undefined, {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
            });
        }

        function renderStatusBadge(statusId) {
            const id = parseInt(statusId, 10);
            switch (id) {
                case 6:
                    return '<span class="badge bg-success">Approved</span>';
                case 4:
                    return '<span class="badge bg-danger">Rejected</span>';
                case 3:
                case 2:
                    return '<span class="badge bg-warning text-dark">Pending</span>';
                default:
                    return '<span class="badge bg-secondary">Draft</span>';
            }
        }
    }
})(window, document);
</script>

