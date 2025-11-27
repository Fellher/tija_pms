<?php
if(!$isValidUser) {
    Alert::info("You need to be logged in to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

$assignments = LeaveHandover::get_assignments_for_user($userDetails->ID, $DBConn);
$pendingCount = count($assignments);
?>

<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-medium fs-24 mb-0">
            <i class="ri-briefcase-line me-2 text-primary"></i>
            Handover Confirmations
        </h1>
        <p class="text-muted mb-0 mt-1">Review and acknowledge responsibilities handed over to you before the applicant's leave starts.</p>
    </div>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $s ?></a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $ss ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Handover Confirmation</li>
            </ol>
        </nav>
    </div>
</div>

<?php if ($pendingCount === 0): ?>
    <div class="alert alert-success">
        <i class="ri-checkbox-circle-line me-2"></i>
        You have no pending handover items to confirm.
    </div>
<?php else: ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Pending Handover Items</h5>
            <span class="badge bg-warning text-dark"><?= $pendingCount ?> awaiting confirmation</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Task / Responsibility</th>
                            <th>Assigned By</th>
                            <th>Leave Period</th>
                            <th>Priority</th>
                            <th>Due Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assignments as $index => $assignment): ?>
                            <?php
                                $assignmentObj = is_object($assignment) ? $assignment : (object)$assignment;
                                $dueDate = $assignmentObj->dueDate ?? $assignmentObj->taskDeadline ?? null;
                                $priority = strtoupper($assignmentObj->priority ?? 'MED');
                            ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td>
                                    <div class="fw-medium"><?= htmlspecialchars($assignmentObj->itemTitle ?? 'Handover Item') ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($assignmentObj->itemDescription ?? '') ?></small>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($assignmentObj->assignedByName ?? 'Supervisor') ?></div>
                                </td>
                                <td>
                                    <small>
                                        <?= isset($assignmentObj->startDate) ? date('M j, Y', strtotime($assignmentObj->startDate)) : '?' ?>
                                        -
                                        <?= isset($assignmentObj->endDate) ? date('M j, Y', strtotime($assignmentObj->endDate)) : '?' ?>
                                    </small>
                                </td>
                                <td><span class="badge bg-primary"><?= htmlspecialchars($priority) ?></span></td>
                                <td><?= $dueDate ? date('M j, Y', strtotime($dueDate)) : '-' ?></td>
                                <td class="text-end">
                                    <button
                                        class="btn btn-outline-primary btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#confirmHandoverModal"
                                        data-assignment='<?= json_encode($assignmentObj) ?>'
                                    >
                                        <i class="ri-check-line me-1"></i>Confirm
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
echo Utility::form_modal_header(
    'confirmHandoverModal',
    'leave/handovers/confirm_handover.php',
    'Confirm Handover Item',
    array("modal-dialog-centered", "modal-lg"),
    $base,
    true
);
?>
<div class="modal-body-content">
    <input type="hidden" name="assignmentId" id="modalAssignmentId">
    <div class="mb-3">
        <label class="form-label">Task / Responsibility</label>
        <div class="fw-medium" id="modalItemTitle">-</div>
        <small class="text-muted" id="modalItemDescription">-</small>
    </div>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Briefed / Aligned</label>
            <select class="form-select" name="briefed" id="modalBriefed">
                <option value="Y">Yes</option>
                <option value="N">No</option>
                <option value="not_required">Not required</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Training completed</label>
            <select class="form-select" name="trained" id="modalTrained">
                <option value="not_required">Not required</option>
                <option value="Y">Completed</option>
                <option value="N">Pending</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Credentials / Access</label>
            <select class="form-select" name="hasCredentials" id="modalCredentials">
                <option value="not_required">Not required</option>
                <option value="Y">Received</option>
                <option value="N">Missing</option>
            </select>
        </div>
    </div>
    <div class="row g-3 mt-0">
        <div class="col-md-4">
            <label class="form-label">Tools / Equipment</label>
            <select class="form-select" name="hasTools" id="modalTools">
                <option value="not_required">Not required</option>
                <option value="Y">Received</option>
                <option value="N">Missing</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Documents</label>
            <select class="form-select" name="hasDocuments" id="modalDocuments">
                <option value="not_required">Not required</option>
                <option value="Y">Received</option>
                <option value="N">Missing</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Ready to take over?</label>
            <select class="form-select" name="readyToTakeOver" id="modalReady">
                <option value="Y">Yes</option>
                <option value="N">Not yet</option>
            </select>
        </div>
    </div>
    <div class="mt-3">
        <label class="form-label">Additional notes</label>
        <textarea class="form-control" name="additionalNotes" id="modalNotes" rows="3"
                  placeholder="Add any blockers, pending information, or context"></textarea>
    </div>
</div>
<?php
echo Utility::form_modal_footer("Submit Confirmation", "confirm_handover_action", 'btn btn-primary btn-sm');
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modalElement = document.getElementById('confirmHandoverModal');
    const assignmentIdInput = document.getElementById('modalAssignmentId');
    const modalTitle = document.getElementById('modalItemTitle');
    const modalDescription = document.getElementById('modalItemDescription');

    modalElement?.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger) return;

        const assignmentData = trigger.dataset.assignment ? JSON.parse(trigger.dataset.assignment) : {};
        assignmentIdInput.value = assignmentData.assignmentID || '';
        modalTitle.textContent = assignmentData.itemTitle || 'Handover item';
        modalDescription.textContent = assignmentData.itemDescription || 'No additional instructions provided.';
    });

    modalElement?.querySelector('form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const submitBtn = modalElement.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ri-loader-4-line spin me-1"></i>Submitting...';

        const formData = new FormData(this);
        fetch('<?= $base ?>php/scripts/leave/handovers/confirm_handover.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Handover item confirmed successfully.', 'success');
                setTimeout(() => window.location.reload(), 800);
            } else {
                showToast(data.message || 'Unable to confirm handover item.', 'danger');
            }
        })
        .catch(() => {
            showToast('Unable to confirm handover item.', 'danger');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    });
});
</script>

