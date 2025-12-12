<?php
/**
 * Add Note Modal - Simplified Version
 * For adding notes to sales cases
 */
?>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="ri-sticky-note-line me-2"></i>Add Note
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addNoteForm">
                    <input type="hidden" name="action" value="addNote">
                    <input type="hidden" name="salesCaseID" id="noteSalesCaseID">
                    <input type="hidden" name="saleStatusLevelID" id="noteSaleStatusLevelID">

                    <div class="mb-3">
                        <label for="noteText" class="form-label">Note <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="noteText" name="noteText" rows="4" required
                                  placeholder="Enter your note here..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Note Type</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="noteType" id="noteTypeGeneral"
                                   value="general" checked>
                            <label class="form-check-label" for="noteTypeGeneral">
                                <i class="ri-team-line me-1"></i>General (Visible to all team members)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="noteType" id="noteTypePrivate"
                                   value="private">
                            <label class="form-check-label" for="noteTypePrivate">
                                <i class="ri-lock-line me-1"></i>Private (Select specific recipients)
                            </label>
                        </div>
                    </div>

                    <div class="mb-3" id="recipientsSection" style="display: none;">
                        <label for="recipients" class="form-label">Share with</label>
                        <select class="form-select" id="recipients" name="recipients[]" multiple>
                            <?php
                            if ($allEmployees) {
                                foreach ($allEmployees as $emp) {
                                    if ($emp->ID != $userDetails->ID) {
                                        echo "<option value='{$emp->ID}'>{$emp->employeeName}</option>";
                                    }
                                }
                            }
                            ?>
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple users</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary" id="saveNoteBtn">
                    <i class="ri-save-line me-1"></i>Save Note
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle recipients section based on note type
    document.querySelectorAll('input[name="noteType"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('recipientsSection').style.display =
                this.value === 'private' ? 'block' : 'none';
        });
    });

    // Save note
    const saveNoteBtn = document.getElementById('saveNoteBtn');
    if (saveNoteBtn) {
        saveNoteBtn.addEventListener('click', function() {
            const form = document.getElementById('addNoteForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const formData = new FormData(form);

            fetch('<?= "{$base}php/scripts/sales/manage_sales_case_notes.php" ?>', {
                method: 'POST',
                body: new URLSearchParams(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    bootstrap.Modal.getInstance(document.getElementById('addNoteModal')).hide();
                    form.reset();
                    if (typeof loadNotes === 'function') {
                        loadNotes();
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to save note'
                });
            });
        });
    }
});

// Function to open add note modal
function openAddNoteModal(salesCaseID, saleStatusLevelID) {
    document.getElementById('noteSalesCaseID').value = salesCaseID;
    document.getElementById('noteSaleStatusLevelID').value = saleStatusLevelID || '';
    const modal = new bootstrap.Modal(document.getElementById('addNoteModal'));
    modal.show();
}
</script>
