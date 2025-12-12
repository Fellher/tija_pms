<?php
/**
 * Add Next Step Modal - Simplified Version
 * For adding action items to sales cases
 */
?>

<!-- Add Next Step Modal -->
<div class="modal fade" id="addNextStepModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="ri-task-line me-2"></i>Add Next Step
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addNextStepForm">
                    <input type="hidden" name="action" value="addNextStep">
                    <input type="hidden" name="salesCaseID" id="stepSalesCaseID">
                    <input type="hidden" name="saleStatusLevelID" id="stepSaleStatusLevelID">

                    <div class="mb-3">
                        <label for="nextStepDescription" class="form-label">
                            Description <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="nextStepDescription" name="nextStepDescription"
                                  rows="3" required placeholder="What needs to be done?"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="dueDate" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="dueDate" name="dueDate">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="priority" class="form-label">Priority</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="assignedToID" class="form-label">Assign To</label>
                        <select class="form-select" id="assignedToID" name="assignedToID">
                            <option value="">Unassigned</option>
                            <?php
                            if ($allEmployees) {
                                foreach ($allEmployees as $emp) {
                                    $selected = ($emp->ID == $userDetails->ID) ? 'selected' : '';
                                    echo "<option value='{$emp->ID}' {$selected}>{$emp->employeeName}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-success" id="saveNextStepBtn">
                    <i class="ri-save-line me-1"></i>Add Next Step
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Flatpickr for due date
    if (typeof flatpickr !== 'undefined') {
        flatpickr('#dueDate', {
            dateFormat: 'Y-m-d',
            minDate: 'today',
            altInput: true,
            altFormat: 'F j, Y'
        });
    }

    // Save next step
    const saveNextStepBtn = document.getElementById('saveNextStepBtn');
    if (saveNextStepBtn) {
        saveNextStepBtn.addEventListener('click', function() {
            const form = document.getElementById('addNextStepForm');
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
                    bootstrap.Modal.getInstance(document.getElementById('addNextStepModal')).hide();
                    form.reset();
                    if (typeof loadNextSteps === 'function') {
                        loadNextSteps();
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
                    text: 'Failed to add next step'
                });
            });
        });
    }
});

// Function to open add next step modal
function openAddNextStepModal(salesCaseID, saleStatusLevelID) {
    document.getElementById('stepSalesCaseID').value = salesCaseID;
    document.getElementById('stepSaleStatusLevelID').value = saleStatusLevelID || '';
    const modal = new bootstrap.Modal(document.getElementById('addNextStepModal'));
    modal.show();
}
</script>
