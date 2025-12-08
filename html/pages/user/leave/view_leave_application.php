<?php
/**
 * View Leave Application
 * Read-only detail view for a single leave request
 */

if (!$isValidUser) {
    Alert::info("You need to be logged in to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

$helperPath = 'php/functions/leave_handover_ui_helpers.php';
if (file_exists($helperPath)) {
    include_once $helperPath;
}
$leaveApplicationID = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($leaveApplicationID <= 0) {
    Alert::error('Invalid leave application reference.', true, array('text-center'));
    echo "<div class='container py-5'><a class='btn btn-outline-primary' href='{$base}html/?s=user&ss=leave&p=my_applications'>Back to My Applications</a></div>";
    return;
}

$leaveRecord = Leave::leave_applications_full(array('leaveApplicationID' => $leaveApplicationID), true, $DBConn);
if (!$leaveRecord) {
    Alert::error('Leave application not found.', true, array('text-center'));
    echo "<div class='container py-5'><a class='btn btn-outline-primary' href='{$base}html/?s=user&ss=leave&p=my_applications'>Back to My Applications</a></div>";
    return;
}

$leave = is_object($leaveRecord) ? (array)$leaveRecord : $leaveRecord;
$fsmState = class_exists('LeaveHandoverFSM') ? LeaveHandoverFSM::get_current_state($leaveApplicationID, $DBConn) : null;
$handoverMeta = null;
if (($leave['handoverRequired'] ?? 'N') === 'Y') {
    $handoverRows = $DBConn->fetch_all_rows(
        "SELECT * FROM tija_leave_handovers WHERE leaveApplicationID = ? AND Lapsed = 'N' LIMIT 1",
        array(array($leaveApplicationID, 'i'))
    );
    if ($handoverRows && isset($handoverRows[0])) {
        $handoverMeta = is_object($handoverRows[0]) ? $handoverRows[0] : (object)$handoverRows[0];
    }
}

// Ensure access control: employee, HR, or admins
$isOwner = ((int)$leave['employeeID'] === (int)$userDetails->ID);
$isHRManager = Employee::is_hr_manager($userDetails->ID, $DBConn);
$isAdmin = isset($userDetails->isAdmin) && $userDetails->isAdmin;

if (!$isOwner && !$isHRManager   && !$isAdmin) {
    Alert::error('You are not authorised to view this leave application.', true, array('text-center'));
    echo "<div class='container py-5'><a class='btn btn-outline-primary' href='{$base}html/?s=user&ss=leave&p=my_applications'>Back to My Applications</a></div>";
    return;
}

$employeeDetails = Employee::employees(array('ID' => $leave['employeeID']), true, $DBConn);
$statusLookup = Leave::leave_status(array('Suspended' => 'N'), false, $DBConn);
$statusName = $leave['leaveStatusName'] ?? '';

if (!$statusName && $statusLookup) {
    foreach ($statusLookup as $status) {
        if ($status->leaveStatusID == $leave['leaveStatusID']) {
            $statusName = $status->leaveStatusName;
            break;
        }
    }
}

$start = Utility::date_format($leave['startDate']);
$end = Utility::date_format($leave['endDate']);
$appliedOn = $leave['dateApplied'] ? date('M j, Y g:i a', strtotime($leave['dateApplied'])) : '-';
$updatedOn = $leave['LastUpdate'] ? date('M j, Y g:i a', strtotime($leave['LastUpdate'])) : $appliedOn;
$availableFiles = $leave['leaveFiles'];

// Fetch approval history (if available)
$approvalHistory = array();
$approvalsRaw = Leave::leave_approvals(array('leaveApplicationID' => $leaveApplicationID), false, $DBConn);
if ($approvalsRaw) {
    foreach ($approvalsRaw as $row) {
        $approvalHistory[] = is_object($row) ? (array)$row : $row;
    }
}

function renderApprovalStatus($statusID) {
    switch ((int)$statusID) {
        case 6: return '<span class="badge bg-success">Approved</span>';
        case 4: return '<span class="badge bg-danger">Rejected</span>';
        case 3:
        case 2: return '<span class="badge bg-warning text-dark">Pending</span>';
        default: return '<span class="badge bg-secondary">Draft</span>';
    }
}
?>

<div class="container-fluid p-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-1">
                <i class="ri-file-text-line text-primary me-2"></i>
                Leave Application #<?php echo htmlspecialchars($leaveApplicationID); ?>
            </h2>
            <p class="text-muted mb-0">
                Submitted on <?php echo htmlspecialchars($appliedOn); ?>
            </p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="<?php echo "{$base}html/?s=user&ss=leave&p=my_applications"; ?>" class="btn btn-outline-secondary">
                <i class="ri-arrow-left-line me-1"></i> Back to My Applications
            </a>
            <?php if ($isOwner && in_array((int)$leave['leaveStatusID'], array(1, 2, 3, 4), true)): ?>
                <a href="<?php echo "{$base}html/?s=user&ss=leave&p=apply_leave_workflow&edit={$leaveApplicationID}"; ?>" class="btn btn-primary">
                    <i class="ri-edit-line me-1"></i> Update Application
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small mb-1">Status</h6>
                    <div class="fs-5">
                        <?php echo renderApprovalStatus($leave['leaveStatusID']); ?>
                    </div>
                    <small class="text-muted">Last updated on <?php echo htmlspecialchars($updatedOn); ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small mb-1">Leave Type</h6>
                    <div class="fs-5"><?php echo htmlspecialchars($leave['leaveTypeName'] ?? 'N/A'); ?></div>
                    <small class="text-muted"><?php echo htmlspecialchars($leave['leaveTypeDescription'] ?? ''); ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small mb-1">Leave Dates</h6>
                    <div class="fs-5"><?php echo "{$start} - {$end}"; ?></div>
                    <small class="text-muted"><?php echo htmlspecialchars($leave['noOfDays']); ?> day(s)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small mb-1">Submission Mode</h6>
                    <div class="fs-5">
                        <?php echo $leave['dateApplied'] ? 'Submitted for approval' : 'Scheduled draft'; ?>
                    </div>
                    <small class="text-muted">Employee: <?php echo htmlspecialchars($employeeDetails->employeeName ?? "{$employeeDetails->FirstName} {$employeeDetails->Surname}"); ?></small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="ri-file-text-line me-2 text-primary"></i>Application Details</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Reason for Leave</dt>
                        <dd class="col-sm-8"><?php echo nl2br(htmlspecialchars($leave['leaveComments'] ?? 'Not provided')); ?></dd>

                        <dt class="col-sm-4">Emergency Contact</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($leave['emergencyContact'] ?? 'Not provided'); ?></dd>

                        <dt class="col-sm-4">Handover Notes</dt>
                        <dd class="col-sm-8"><?php echo nl2br(htmlspecialchars($leave['handoverNotes'] ?? 'Not provided')); ?></dd>
                        <dt class="col-sm-4">Handover Status</dt>
                        <dd class="col-sm-8">
                            <?php if (($leave['handoverRequired'] ?? 'N') === 'Y'): ?>
                                <?= function_exists('get_handover_status_badge')
                                    ? get_handover_status_badge($leave['handoverStatus'] ?? 'pending')
                                    : '<span class="badge bg-primary text-uppercase">' . htmlspecialchars($leave['handoverStatus'] ?? 'pending') . '</span>'; ?>
                                <?php if (!empty($leave['handoverStatus']) && $leave['handoverStatus'] !== 'not_required'): ?>
                                    <a href="<?php echo "{$base}html/?s={$s}&ss={$ss}&p=handover_report&applicationID={$leave['leaveApplicationID']}"; ?>"
                                       class="btn btn-link btn-sm ps-0">View handover report</a>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Not required</span>
                            <?php endif; ?>
                        </dd>
                        <?php if (($leave['handoverRequired'] ?? 'N') === 'Y'): ?>
                            <dt class="col-sm-4">Workflow State</dt>
                            <dd class="col-sm-8 d-flex align-items-center gap-2 flex-wrap">
                                <?= $fsmState && function_exists('get_fsm_state_badge')
                                    ? get_fsm_state_badge($fsmState->currentState ?? 'ST_00')
                                    : '<span class="badge bg-secondary">Not started</span>'; ?>
                                <?php if ($fsmState && $fsmState->currentState === LeaveHandoverFSM::STATE_PEER_NEGOTIATION && $fsmState->timerExpiresAt): ?>
                                    <?php $timerInfo = LeaveHandoverFSM::check_timer_expiry($leaveApplicationID, $DBConn); ?>
                                    <span class="text-muted small">
                                        <?php if ($timerInfo['expired'] ?? false): ?>
                                            Response timer expired
                                        <?php else: ?>
                                            Response due in <?= htmlspecialchars(format_timer_remaining($timerInfo['remaining_hours'])) ?>
                                        <?php endif; ?>
                                    </span>
                                <?php endif; ?>
                                <a href="<?= "{$base}html/?s=user&ss=leave&p=handover_audit_trail&applicationID={$leaveApplicationID}" ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="ri-route-fill me-1"></i>Audit Trail
                                </a>
                                <?php if ($handoverMeta && (int)$handoverMeta->nomineeID === (int)$userDetails->ID): ?>
                                    <a href="<?= "{$base}html/?s=user&ss=leave&p=peer_handover_response&handoverID={$handoverMeta->handoverID}" ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="ri-briefcase-line me-1"></i>Open Peer Console
                                    </a>
                                <?php endif; ?>
                            </dd>
                        <?php endif; ?>

                        <dt class="col-sm-4">Half Day</dt>
                        <dd class="col-sm-8">
                            <?php echo ($leave['halfDayLeave'] === 'Y') ? 'Yes (' . htmlspecialchars($leave['halfDayPeriod'] ?: 'unspecified') . ')' : 'No'; ?>
                        </dd>
                        <dt class="col-sm-4">Application ID</dt>
                        <dd class="col-sm-8">#<?php echo htmlspecialchars($leaveApplicationID); ?></dd>
                    </dl>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="ri-team-line me-2 text-primary"></i>Approval Workflow</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="refreshWorkflowBtn" title="Refresh status">
                        <i class="ri-refresh-line"></i>
                    </button>
                </div>
                <div class="card-body" id="workflowStatusContainer">
                    <div class="text-center py-3">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <small class="text-muted d-block mt-2">Loading approval status...</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="ri-user-3-line me-2 text-primary"></i>Employee Details</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Name</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($employeeDetails->employeeName ?? "{$employeeDetails->FirstName} {$employeeDetails->Surname}"); ?></dd>

                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($employeeDetails->Email ?? ''); ?></dd>

                        <dt class="col-sm-4">Organisation</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($leave['entityName'] ?? ''); ?></dd>

                        <dt class="col-sm-4">Leave Period</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($leave['leavePeriodName'] ?? ''); ?></dd>
                    </dl>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="ri-attachment-2 me-2 text-primary"></i>Attachments</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($availableFiles)): ?>
                        <p class="text-muted mb-0">No supporting documents uploaded.</p>
                    <?php else: ?>
                        <?php
                            $decodedFiles = base64_decode($availableFiles);
                            $filePaths = $decodedFiles ? explode(',', $decodedFiles) : array();
                        ?>
                        <?php if (empty($filePaths)): ?>
                            <p class="text-muted mb-0">No supporting documents uploaded.</p>
                        <?php else: ?>
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($filePaths as $path): ?>
                                    <?php $filename = basename($path); ?>
                                    <li class="mb-2 d-flex align-items-center">
                                        <i class="ri-file-line me-2 text-muted"></i>
                                        <a href="<?php echo $base . ltrim($path, '/'); ?>" target="_blank" class="text-decoration-none">
                                            <?php echo htmlspecialchars($filename); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.workflow-step {
    border-left: 3px solid #dee2e6;
    padding-left: 1rem;
    margin-bottom: 1.5rem;
    position: relative;
}

.workflow-step:last-child {
    border-left: none;
}

.workflow-step.completed {
    border-left-color: #28a745;
}

.workflow-step.rejected {
    border-left-color: #dc3545;
}

.workflow-step.pending {
    border-left-color: #ffc107;
}

.workflow-step.partial {
    border-left-color: #17a2b8;
}

.workflow-step-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.workflow-step-title {
    font-weight: 600;
    font-size: 1rem;
    margin: 0;
}

.workflow-step-status {
    font-size: 0.75rem;
}

.approver-item {
    background: #f8f9fa;
    border-radius: 0.375rem;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.approver-item:last-child {
    margin-bottom: 0;
}

.approver-info {
    flex: 1;
}

.approver-name {
    font-weight: 500;
    font-size: 0.875rem;
    margin-bottom: 0.25rem;
}

.approver-email {
    font-size: 0.75rem;
    color: #6c757d;
}

.approver-action {
    text-align: right;
}

.approver-action-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.approver-comment {
    margin-top: 0.5rem;
    padding: 0.75rem;
    border-top: 1px solid #dee2e6;
    font-size: 0.875rem;
    color: #495057;
    border-radius: 0.25rem;
}

.approver-comment.bg-white {
    padding: 1rem;
    margin-left: -0.75rem;
    margin-right: -0.75rem;
    margin-bottom: -0.75rem;
}

.approver-comment-date {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.approver-item.border-danger {
    border-width: 2px !important;
}

.workflow-progress {
    height: 4px;
    background: #e9ecef;
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 1rem;
}

.workflow-progress-bar {
    height: 100%;
    background: #28a745;
    transition: width 0.3s ease;
}

#refreshWorkflowBtn .ri-refresh-line {
    transition: transform 0.3s ease;
}

#refreshWorkflowBtn.refreshing .ri-refresh-line {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

<script>
(function() {
    const leaveApplicationID = <?php echo $leaveApplicationID; ?>;
    const baseUrl = '<?php echo $base; ?>';
    let refreshInterval = null;
    let isRefreshing = false;
    let currentLeaveStatusID = <?php echo $leave['leaveStatusID'] ?? 3; ?>;

    function loadApprovalStatus() {
        if (isRefreshing) return;
        isRefreshing = true;

        const container = document.getElementById('workflowStatusContainer');
        if (!container) return;

        fetch(baseUrl + 'php/scripts/leave/workflows/get_approval_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                leaveApplicationID: leaveApplicationID
            })
        })
        .then(response => response.json())
        .then(data => {
            isRefreshing = false;
            if (data.success) {
                // Update current status for auto-refresh logic
                if (data.leaveStatusID !== undefined) {
                    currentLeaveStatusID = data.leaveStatusID;
                }
                renderWorkflowStatus(data);
                // Update auto-refresh based on new status
                updateAutoRefresh();
            } else {
                container.innerHTML = '<div class="alert alert-warning mb-0">' +
                    (data.message || 'Unable to load approval status') + '</div>';
            }
        })
        .catch(error => {
            isRefreshing = false;
            console.error('Error loading approval status:', error);
            const container = document.getElementById('workflowStatusContainer');
            if (container) {
                container.innerHTML = '<div class="alert alert-danger mb-0">Error loading approval status. Please refresh the page.</div>';
            }
        });
    }

    function renderWorkflowStatus(data) {
        const container = document.getElementById('workflowStatusContainer');
        if (!container) return;

        if (!data.hasWorkflow || !data.workflow || !data.workflow.steps || data.workflow.steps.length === 0) {
            // Fallback to legacy approval history
            const approvalHistory = <?php echo json_encode($approvalHistory); ?>;
            if (approvalHistory && approvalHistory.length > 0) {
                let html = '<div class="timeline">';
                approvalHistory.forEach(approval => {
                    const approverName = escapeHtml(approval.approverName || 'Approver');
                    const actionStatus = renderStatusBadge(approval.leaveStatusID || 3);
                    const actionDate = approval.approvalDateAdded || approval.DateAdded || null;
                    const formattedDate = actionDate ? formatDateTime(actionDate) : '-';
                    const comments = approval.approversComments || '';

                    html += `
                        <div class="timeline-item mb-3 pb-3 border-bottom">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-1">${approverName}</h6>
                                <span>${actionStatus}</span>
                            </div>
                            <p class="text-muted small mb-1"><i class="ri-time-line me-1"></i>${escapeHtml(formattedDate)}</p>
                            ${comments ? '<p class="mb-0">' + escapeHtmlMultiline(comments) + '</p>' : ''}
                        </div>
                    `;
                });
                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<p class="text-muted mb-0">No approval actions recorded yet.</p>';
            }
            return;
        }

        const workflow = data.workflow;
        const steps = workflow.steps || [];
        const comments = data.comments || [];

        // Calculate progress
        const totalSteps = steps.length;
        const completedSteps = steps.filter(s => s.stepStatus === 'approved').length;
        const progressPercent = totalSteps > 0 ? (completedSteps / totalSteps) * 100 : 0;

        let html = '';

        // Progress bar
        html += `
            <div class="workflow-progress mb-3">
                <div class="workflow-progress-bar" style="width: ${progressPercent}%"></div>
            </div>
        `;

        // Overall status with rejection details
        if (workflow.hasRejection) {
            html += '<div class="alert alert-danger mb-3">';
            html += '<h6 class="alert-heading mb-2"><i class="ri-close-circle-line me-2"></i>Application Rejected</h6>';
            html += '<p class="mb-2">Your leave application has been rejected. Please review the comments below to understand the reason and take necessary action.</p>';

            // Show who rejected
            const rejectedApprovers = [];
            steps.forEach(step => {
                if (step.approvers) {
                    step.approvers.forEach(approver => {
                        if (approver.hasActed && approver.action === 'rejected') {
                            const comment = approver.comments || '';
                            const displayDate = approver.actionDate || approver.decisionDate || null;
                            const matchingComment = comments.find(c =>
                                c.approverUserID == approver.approverUserID ||
                                c.approverID == approver.approverUserID
                            );
                            const displayComment = comment || (matchingComment ? matchingComment.comment : '');

                            rejectedApprovers.push({
                                name: approver.approverName || 'Unknown',
                                comment: displayComment,
                                date: displayDate || (matchingComment ? matchingComment.commentDate : null)
                            });
                        }
                    });
                }
            });

            if (rejectedApprovers.length > 0) {
                html += '<hr class="my-2">';
                html += '<strong>Rejected by:</strong>';
                html += '<ul class="mb-0 mt-2">';
                rejectedApprovers.forEach(approver => {
                    html += '<li class="mb-2">';
                    html += '<strong>' + escapeHtml(approver.name) + '</strong>';
                    if (approver.date) {
                        html += ' <small class="text-muted">(' + formatDateTime(approver.date) + ')</small>';
                    }
                    if (approver.comment) {
                        html += '<br><em>"' + escapeHtmlMultiline(approver.comment) + '"</em>';
                    }
                    html += '</li>';
                });
                html += '</ul>';
            }
            html += '</div>';
        } else if (workflow.allRequiredApproved && workflow.isFinalStepComplete) {
            html += '<div class="alert alert-success mb-3">';
            html += '<h6 class="alert-heading mb-2"><i class="ri-checkbox-circle-line me-2"></i>Fully Approved</h6>';
            html += '<p class="mb-0">All required approvals have been completed. Your leave application is now approved.</p>';
            html += '</div>';
        } else {
            // Show partial approvals
            const approvedCount = steps.reduce((sum, s) => sum + (s.approvedCount || 0), 0);
            const totalApprovers = steps.reduce((sum, s) => sum + (s.totalApprovers || 0), 0);

            html += '<div class="alert alert-info mb-3">';
            html += '<h6 class="alert-heading mb-2"><i class="ri-time-line me-2"></i>Approval In Progress</h6>';
            html += `<p class="mb-0">Progress: ${approvedCount} of ${totalApprovers} approver(s) have approved. Waiting for remaining approvals...</p>`;
            html += '</div>';
        }

        // Workflow steps
        if (steps.length === 0) {
            html += '<p class="text-muted mb-0">No workflow steps configured.</p>';
        } else {
            steps.forEach((step, index) => {
                const stepStatus = step.stepStatus || 'pending';
                const stepClass = stepStatus === 'approved' ? 'completed' :
                                 stepStatus === 'rejected' ? 'rejected' :
                                 stepStatus === 'partial' ? 'partial' : 'pending';

                html += `
                    <div class="workflow-step ${stepClass}">
                        <div class="workflow-step-header">
                            <h6 class="workflow-step-title">
                                ${escapeHtml(step.stepName || 'Step ' + step.stepOrder)}
                            </h6>
                            <span class="workflow-step-status badge ${getStatusBadgeClass(stepStatus)}">
                                ${getStatusLabel(stepStatus)}
                            </span>
                        </div>
                        ${step.stepDescription ? '<p class="text-muted small mb-2">' + escapeHtml(step.stepDescription) + '</p>' : ''}
                        <div class="approvers-list">
                `;

                if (step.approvers && step.approvers.length > 0) {
                    step.approvers.forEach(approver => {
                        const hasActed = approver.hasActed || false;
                        const action = approver.action || null;
                        const approverComments = approver.comments || '';
                        const actionDate = approver.actionDate || approver.decisionDate || null; // Support both field names

                        // Find matching comment from comments array
                        const matchingComment = comments.find(c =>
                            c.approverUserID == approver.approverUserID ||
                            c.approverID == approver.approverUserID
                        );

                        const displayComment = approverComments || (matchingComment ? matchingComment.comment : '');
                        const displayDate = actionDate || (matchingComment ? matchingComment.commentDate : null);

                        const isRejected = hasActed && action === 'rejected';
                        const isApproved = hasActed && action === 'approved';
                        const itemClass = isRejected ? 'border-danger bg-danger bg-opacity-10' : '';

                        html += `
                            <div class="approver-item ${itemClass}">
                                <div class="approver-info">
                                    <div class="approver-name">
                                        ${escapeHtml(approver.approverName || 'Unknown Approver')}
                                        ${approver.isBackup ? '<span class="badge bg-secondary ms-2" style="font-size: 0.65rem;">Backup</span>' : ''}
                                    </div>
                                    ${approver.approverEmail ? '<div class="approver-email">' + escapeHtml(approver.approverEmail) + '</div>' : ''}
                                </div>
                                <div class="approver-action">
                                    ${hasActed ?
                                        `<span class="badge approver-action-badge ${isApproved ? 'bg-success' : 'bg-danger'}">
                                            ${isApproved ? '<i class="ri-check-line"></i> Approved' : '<i class="ri-close-line"></i> Rejected'}
                                        </span>` :
                                        `<span class="badge approver-action-badge bg-warning text-dark">
                                            <i class="ri-time-line"></i> Pending
                                        </span>`
                                    }
                                </div>
                            </div>
                        `;

                        if (hasActed && (displayComment || displayDate)) {
                            const commentClass = isRejected ? 'bg-white border border-danger' : '';
                            html += `
                                <div class="approver-comment ${commentClass}">
                                    ${isRejected ? '<div class="text-danger fw-bold mb-1"><i class="ri-alert-line me-1"></i>Rejection Reason:</div>' : ''}
                                    ${displayComment ? '<div class="mb-1">' + escapeHtmlMultiline(displayComment) + '</div>' : ''}
                                    ${displayDate ? '<div class="approver-comment-date"><i class="ri-time-line me-1"></i>' + formatDateTime(displayDate) + '</div>' : ''}
                                </div>
                            `;
                        }
                    });
                } else {
                    html += '<p class="text-muted small mb-0">No approvers assigned to this step.</p>';
                }

                html += `
                        </div>
                    </div>
                `;
            });
        }

        container.innerHTML = html;
    }

    function getStatusBadgeClass(status) {
        switch(status) {
            case 'approved': return 'bg-success';
            case 'rejected': return 'bg-danger';
            case 'partial': return 'bg-info';
            case 'pending': return 'bg-warning text-dark';
            default: return 'bg-secondary';
        }
    }

    function getStatusLabel(status) {
        switch(status) {
            case 'approved': return 'Approved';
            case 'rejected': return 'Rejected';
            case 'partial': return 'Partially Approved';
            case 'pending': return 'Pending';
            default: return 'Unknown';
        }
    }

    function renderStatusBadge(statusID) {
        switch(parseInt(statusID)) {
            case 6: return '<span class="badge bg-success">Approved</span>';
            case 4: return '<span class="badge bg-danger">Rejected</span>';
            case 3:
            case 2: return '<span class="badge bg-warning text-dark">Pending</span>';
            default: return '<span class="badge bg-secondary">Draft</span>';
        }
    }

    function formatDateTime(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit'
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function escapeHtmlMultiline(text) {
        if (!text) return '';
        return escapeHtml(text).replace(/\n/g, '<br>');
    }

    // Refresh button handler
    const refreshBtn = document.getElementById('refreshWorkflowBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            this.classList.add('refreshing');
            loadApprovalStatus();
            setTimeout(() => {
                this.classList.remove('refreshing');
            }, 1000);
        });
    }

    // Initial load
    loadApprovalStatus();

    // Auto-refresh every 30 seconds if status is pending
    function updateAutoRefresh() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
            refreshInterval = null;
        }

        // Only auto-refresh if status is pending (3)
        if (currentLeaveStatusID === 3) {
            refreshInterval = setInterval(() => {
                loadApprovalStatus();
            }, 30000); // 30 seconds
        }
    }

    // Start auto-refresh
    updateAutoRefresh();

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    });
})();
</script>
