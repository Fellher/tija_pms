<?php
/**
 * Handover Audit Trail
 *
 * Displays the full chain of custody for a leave application's handover FSM.
 */

if (!$isValidUser) {
    Alert::info("Please log in to view the audit trail.", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

include_once 'php/functions/leave_handover_ui_helpers.php';

$leaveApplicationID = isset($_GET['applicationID']) ? (int)Utility::clean_string($_GET['applicationID']) : 0;
if (!$leaveApplicationID) {
    Alert::error('Missing leave application reference.', true);
    echo "<div class='container py-5'><a class='btn btn-outline-primary' href=\"{$base}html/?s=user&ss=leave&p=my_applications\">Back to applications</a></div>";
    return;
}

$leaveRecord = Leave::leave_applications_full(array('leaveApplicationID' => $leaveApplicationID), true, $DBConn);
if (!$leaveRecord) {
    Alert::error('Leave application not found.', true);
    echo "<div class='container py-5'><a class='btn btn-outline-primary' href=\"{$base}html/?s=user&ss=leave&p=my_applications\">Back to applications</a></div>";
    return;
}

$leave = is_object($leaveRecord) ? (array)$leaveRecord : $leaveRecord;
$isOwner = ((int)$leave['employeeID'] === (int)$userDetails->ID);
$isHRManager = Employee::is_hr_manager($userDetails->ID, $DBConn, $leave['entityID'] ?? null);
$isAdmin = isset($userDetails->isAdmin) && $userDetails->isAdmin;
$isApprover = Leave::is_user_approver($userDetails->ID, $leaveApplicationID, $DBConn);

if (!$isOwner && !$isHRManager && !$isAdmin && !$isApprover) {
    Alert::error('You are not authorised to view this audit trail.', true, array('text-center'));
    echo "<div class='container py-5'><a class='btn btn-outline-primary' href=\"{$base}html/?s=user&ss=leave&p=my_applications\">Back to applications</a></div>";
    return;
}

$fsmState = class_exists('LeaveHandoverFSM') ? LeaveHandoverFSM::get_current_state($leaveApplicationID, $DBConn) : null;
$chainOfCustody = class_exists('LeaveHandoverFSM') ? LeaveHandoverFSM::get_chain_of_custody($leaveApplicationID, $DBConn) : array();
$handoverStatus = get_handover_status_badge($leave['handoverStatus'] ?? 'not_required');

$actorIDs = array();
foreach ($chainOfCustody as $entry) {
    if (isset($entry['actor_id'])) {
        $actorIDs[] = (int)$entry['actor_id'];
    }
}
$actorIDs = array_unique($actorIDs);
$actorNames = array();
if (!empty($actorIDs)) {
    $placeholders = implode(',', array_fill(0, count($actorIDs), '?'));
    $params = array_map(function ($id) {
        return array($id, 'i');
    }, $actorIDs);
    $actors = $DBConn->fetch_all_rows(
        "SELECT ID, CONCAT(FirstName, ' ', Surname) as fullName FROM people WHERE ID IN ({$placeholders})",
        $params
    );
    foreach ($actors as $actor) {
        $actor = is_object($actor) ? $actor : (object)$actor;
        $actorNames[$actor->ID] = $actor->fullName;
    }
}
?>

<div class="container py-4 handover-audit-trail">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="<?= "{$base}html/?s=user&ss=leave&p=view_leave_application&id={$leaveApplicationID}" ?>" class="btn btn-link ps-0">
                <i class="ri-arrow-left-line me-1"></i>Back to application
            </a>
            <h2 class="mb-1">Handover Audit Trail</h2>
            <p class="text-muted mb-0">Trace every state transition and chain-of-custody event.</p>
        </div>
        <div class="text-end">
            <?= $fsmState ? get_fsm_state_badge($fsmState->currentState ?? 'ST_00') : '' ?>
            <div class="mt-2"><?= $handoverStatus ?></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small">Employee</div>
                    <div class="fw-semibold"><?= htmlspecialchars(($leave['FirstName'] ?? '') . ' ' . ($leave['Surname'] ?? '')) ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Leave Type</div>
                    <div class="fw-semibold"><?= htmlspecialchars($leave['leaveTypeName'] ?? 'Leave') ?></div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Dates</div>
                    <div class="fw-semibold">
                        <?= htmlspecialchars(date('M j, Y', strtotime($leave['startDate']))) ?>
                        –
                        <?= htmlspecialchars(date('M j, Y', strtotime($leave['endDate']))) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">State Transitions</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($chainOfCustody)): ?>
                <div class="timeline">
                    <?php foreach ($chainOfCustody as $index => $entry):
                        $fromState = $entry['from_state'] ?? null;
                        $toState = $entry['to_state'] ?? null;
                        $trigger = $entry['trigger'] ?? 'N/A';
                        $timestamp = isset($entry['timestamp']) ? date('M j, Y g:i A', strtotime($entry['timestamp'])) : 'Unknown';
                        $actorID = $entry['actor_id'] ?? null;
                        $actorName = $actorID && isset($actorNames[$actorID]) ? $actorNames[$actorID] : 'System';
                        $metadata = $entry['metadata'] ?? array();
                        ?>
                        <div class="timeline-item">
                            <div class="timeline-marker <?= $index === 0 ? 'timeline-marker-primary' : '' ?>"></div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= $fromState ? get_fsm_state_badge($fromState) : '<span class="badge bg-secondary">Start</span>' ?></strong>
                                        <i class="ri-arrow-right-line mx-2 text-muted"></i>
                                        <strong><?= $toState ? get_fsm_state_badge($toState) : '<span class="badge bg-secondary">N/A</span>' ?></strong>
                                    </div>
                                    <div class="text-muted small"><?= htmlspecialchars($timestamp) ?></div>
                                </div>
                                <div class="mt-2">
                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($trigger) ?></span>
                                    <span class="text-muted ms-2">by <?= htmlspecialchars($actorName) ?></span>
                                </div>
                                <?php if (!empty($metadata)): ?>
                                    <div class="mt-3 bg-light rounded p-2 small">
                                        <strong class="d-block text-muted mb-1">Metadata</strong>
                                        <pre class="mb-0"><?= htmlspecialchars(json_encode($metadata, JSON_PRETTY_PRINT)) ?></pre>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center text-muted py-4">No FSM activity recorded yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    margin-left: 1rem;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: var(--bs-border-color, #e9ecef);
}
.timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
    padding-left: 2rem;
}
.timeline-marker {
    position: absolute;
    left: -2px;
    top: 4px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background-color: #adb5bd;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px var(--bs-border-color, #e9ecef);
}
.timeline-marker-primary {
    background-color: var(--bs-primary);
}
.timeline-content {
    background-color: #fff;
    border: 1px solid var(--bs-border-color, #e9ecef);
    border-radius: 0.5rem;
    padding: 1rem;
    box-shadow: 0 0.15rem 0.3rem rgba(15, 34, 58, 0.08);
}
</style>

