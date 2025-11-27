<?php
/**
 * Peer Handover Response
 *
 * Allows nominees/peers to review and respond to handover assignments.
 */

if (!$isValidUser) {
    Alert::info("Please log in to review handovers.", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

include_once 'php/functions/leave_handover_ui_helpers.php';

$handoverID = isset($_GET['handoverID']) ? (int)Utility::clean_string($_GET['handoverID']) : 0;
if (!$handoverID) {
    Alert::error('Missing handover reference.', true);
    echo "<div class='container py-5'><a class='btn btn-outline-primary' href='{$base}html/?s=user&ss=leave&p=dashboard_view'>Back to dashboard</a></div>";
    return;
}

$handoverRows = $DBConn->fetch_all_rows(
    "SELECT h.*, la.leaveApplicationID, la.startDate, la.endDate, la.employeeID, la.entityID, la.orgDataID,
            CONCAT(emp.FirstName, ' ', emp.Surname) as employeeName,
            lt.leaveTypeName
     FROM tija_leave_handovers h
     LEFT JOIN tija_leave_applications la ON h.leaveApplicationID = la.leaveApplicationID
     LEFT JOIN people emp ON la.employeeID = emp.ID
     LEFT JOIN tija_leave_types lt ON la.leaveTypeID = lt.leaveTypeID
     WHERE h.handoverID = ?
     LIMIT 1",
    array(array($handoverID, 'i'))
);

if (!$handoverRows || !isset($handoverRows[0])) {
    Alert::error('Handover not found.', true);
    echo "<div class='container py-5'><a class='btn btn-outline-primary' href='{$base}html/?s=user&ss=leave&p=dashboard_view'>Back to dashboard</a></div>";
    return;
}

$handover = is_object($handoverRows[0]) ? $handoverRows[0] : (object)$handoverRows[0];
$currentUserID = $userDetails->ID;

if ((int)$handover->nomineeID !== (int)$currentUserID && !$isHRManager && !$isAdmin) {
    Alert::error('You are not authorized to review this handover.', true, array('text-center'));
    echo "<div class='container py-5'><a class='btn btn-outline-primary' href='{$base}html/?s=user&ss=leave&p=dashboard_view'>Back to dashboard</a></div>";
    return;
}

$assignments = LeaveHandover::get_assignments_for_user($currentUserID, $DBConn, array('pending', 'acknowledged'));
$handoverAssignments = array_filter($assignments, function ($assignment) use ($handoverID) {
    $assignment = is_object($assignment) ? $assignment : (object)$assignment;
    return isset($assignment->handoverID) && (int)$assignment->handoverID === (int)$GLOBALS['handoverID'];
});

$handoverReport = LeaveHandover::get_handover_report($handover->leaveApplicationID, $DBConn);
$fsmState = class_exists('LeaveHandoverFSM') ? LeaveHandoverFSM::get_current_state($handover->leaveApplicationID, $DBConn) : null;
$timerInfo = $fsmState && $fsmState->currentState === LeaveHandoverFSM::STATE_PEER_NEGOTIATION
    ? LeaveHandoverFSM::check_timer_expiry($handover->leaveApplicationID, $DBConn)
    : null;
?>

<div class="container py-4 peer-handover-response">
    <div class="mb-4">
        <a href="<?= "{$base}html/?s=user&ss=leave&p=dashboard_view" ?>" class="btn btn-link ps-0">
            <i class="ri-arrow-left-line me-1"></i>Back to dashboard
        </a>
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h2 class="mb-1">Handover Review</h2>
                <p class="text-muted mb-0">Review tasks handed over from <?= htmlspecialchars($handover->employeeName ?? 'Employee') ?> for <?= htmlspecialchars($handover->leaveTypeName ?? 'Leave') ?>.</p>
            </div>
            <div>
                <?= $fsmState ? get_fsm_state_badge($fsmState->currentState ?? 'ST_02') : '' ?>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Leave Details</h5>
                    <dl class="row mb-0">
                        <dt class="col-5">Employee</dt>
                        <dd class="col-7"><?= htmlspecialchars($handover->employeeName ?? 'Employee') ?></dd>
                        <dt class="col-5">Leave Type</dt>
                        <dd class="col-7"><?= htmlspecialchars($handover->leaveTypeName ?? 'Leave') ?></dd>
                        <dt class="col-5">Dates</dt>
                        <dd class="col-7">
                            <?= htmlspecialchars(date('M j, Y', strtotime($handover->startDate ?? 'now'))) ?>
                            –
                            <?= htmlspecialchars(date('M j, Y', strtotime($handover->endDate ?? 'now'))) ?>
                        </dd>
                        <dt class="col-5">Status</dt>
                        <dd class="col-7"><?= get_handover_status_badge($handover->handoverStatus ?? 'pending') ?></dd>
                        <?php if ($timerInfo): ?>
                            <dt class="col-5">Response Timer</dt>
                            <dd class="col-7">
                                <?php if ($timerInfo['expired'] ?? false): ?>
                                    <span class="text-danger fw-bold">Expired</span>
                                <?php else: ?>
                                    <span class="text-muted">Due in <?= htmlspecialchars(format_timer_remaining($timerInfo['remaining_hours'])) ?></span>
                                <?php endif; ?>
                            </dd>
                        <?php endif; ?>
                    </dl>
                </div>
                <div class="card-footer text-muted small">
                    Handover ID #<?= (int)$handoverID ?> · Leave Application #<?= (int)$handover->leaveApplicationID ?>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Assigned Tasks</h5>
                    <div class="text-muted small"><?= count($handoverAssignments) ?> assignment(s)</div>
                </div>
                <div class="card-body">
                    <?php if (!empty($handoverAssignments)): ?>
                        <div class="list-group">
                            <?php foreach ($handoverAssignments as $assignment):
                                $assignment = is_object($assignment) ? $assignment : (object)$assignment;
                                ?>
                                <div class="list-group-item" data-assignment-id="<?= (int)$assignment->assignmentID ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?= htmlspecialchars($assignment->itemTitle ?? 'Handover Task') ?></h6>
                                            <p class="text-muted mb-2"><?= nl2br(htmlspecialchars($assignment->itemDescription ?? 'No description provided.')) ?></p>
                                            <?php if (!empty($assignment->priority)): ?>
                                                <span class="badge bg-light text-dark me-1 text-uppercase"><?= htmlspecialchars($assignment->priority) ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($assignment->dueDate)): ?>
                                                <span class="badge bg-light text-dark">Due <?= htmlspecialchars(date('M j, Y', strtotime($assignment->dueDate))) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-end">
                                            <div class="badge bg-secondary text-uppercase"><?= htmlspecialchars($assignment->confirmationStatus ?? 'pending') ?></div>
                                            <?php if ($assignment->revisionRequested === 'Y'): ?>
                                                <div class="text-warning small">Revision pending</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            No outstanding tasks found for you.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer d-flex gap-2 flex-wrap">
                    <button class="btn btn-success" data-action="peer-accept" <?= empty($handoverAssignments) ? 'disabled' : '' ?>>
                        <i class="ri-checkbox-circle-line me-1"></i>I Accept the Handover
                    </button>
                    <button class="btn btn-outline-warning" data-bs-toggle="collapse" data-bs-target="#revisionForm">
                        <i class="ri-edit-line me-1"></i>Request Changes
                    </button>
                </div>
            </div>

            <div class="collapse mt-3" id="revisionForm">
                <div class="card border-warning">
                    <div class="card-header bg-warning bg-opacity-25 border-warning">
                        <strong>Request Revisions</strong>
                    </div>
                    <div class="card-body">
                        <form id="peerRevisionForm">
                            <input type="hidden" name="handoverID" value="<?= (int)$handoverID ?>">
                            <div class="mb-3">
                                <label class="form-label">Detail the missing information, access, or blockers.</label>
                                <textarea name="requestedChanges" class="form-control" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-warning">
                                <span class="default-label">Send Revision Request</span>
                                <span class="loading-label d-none"><span class="spinner-border spinner-border-sm me-1"></span>Submitting...</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const acceptBtn = document.querySelector('[data-action=\"peer-accept\"]');
    const revisionForm = document.getElementById('peerRevisionForm');

    async function handleResponse(payload) {
        const formData = new FormData();
        Object.keys(payload).forEach(key => formData.append(key, payload[key]));
        try {
            const response = await fetch('<?= $base ?>php/scripts/leave/handovers/handle_peer_response.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.success) {
                location.reload();
            } else {
                alert(result.message || 'Failed to process response.');
            }
        } catch (error) {
            console.error(error);
            alert('An error occurred. Please try again.');
        }
    }

    acceptBtn?.addEventListener('click', function() {
        if (!confirm('Confirm you have received sufficient handover details and accept responsibility while the requester is away.')) {
            return;
        }
        handleResponse({
            handoverID: '<?= (int)$handoverID ?>',
            response: 'accept'
        });
    });

    revisionForm?.addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(revisionForm);
        const requestedChanges = formData.get('requestedChanges');
        if (!requestedChanges || requestedChanges.trim().length < 10) {
            alert('Please provide sufficient detail for the revision request.');
            return;
        }
        revisionForm.querySelector('.default-label').classList.add('d-none');
        revisionForm.querySelector('.loading-label').classList.remove('d-none');
        handleResponse({
            handoverID: '<?= (int)$handoverID ?>',
            response: 'request_change',
            requestedChanges: requestedChanges
        }).finally(() => {
            revisionForm.querySelector('.default-label').classList.remove('d-none');
            revisionForm.querySelector('.loading-label').classList.add('d-none');
        });
    });
})();
</script>

