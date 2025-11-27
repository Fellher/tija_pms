<!-- Manage Role Type Modal -->
<div class="modal fade" id="manageRoleTypeModal" tabindex="-1" aria-labelledby="manageRoleTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manageRoleTypeModalLabel">Add New Role Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="manageRoleTypeForm" onsubmit="saveRoleType(event)">
                <div class="modal-body">
                    <input type="hidden" id="roleTypeID" name="roleTypeID" value="">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="roleTypeName" class="form-label">Role Type Name <span class="text-danger">*</span></label>
                            <input type="text" id="roleTypeName" name="roleTypeName" class="form-control"
                                placeholder="e.g., Executive, Management" required maxlength="100">
                            <small class="text-muted">Display name for the role type</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="roleTypeCode" class="form-label">Role Type Code <span class="text-danger">*</span></label>
                            <input type="text" id="roleTypeCode" name="roleTypeCode" class="form-control"
                                placeholder="e.g., EXEC, MGT" required maxlength="20" style="text-transform: uppercase;">
                            <small class="text-muted">Short unique code (uppercase)</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="roleTypeDescription" class="form-label">Description</label>
                        <textarea id="roleTypeDescription" name="roleTypeDescription" class="form-control"
                            rows="3" placeholder="Describe the role type..."></textarea>
                        <small class="text-muted">Optional description of the role type</small>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="displayOrder" class="form-label">Display Order</label>
                            <input type="number" id="displayOrder" name="displayOrder" class="form-control"
                                value="0" min="0">
                            <small class="text-muted">Order in dropdown lists (lower = first)</small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="colorCode" class="form-label">Color Code</label>
                            <div class="input-group">
                                <input type="color" id="colorCodePicker" class="form-control form-control-color"
                                    value="#667eea" onchange="document.getElementById('colorCode').value = this.value">
                                <input type="text" id="colorCode" name="colorCode" class="form-control"
                                    value="#667eea" placeholder="#667eea" maxlength="7">
                            </div>
                            <small class="text-muted">Color for badges and icons</small>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="iconClass" class="form-label">Icon Class</label>
                            <input type="text" id="iconClass" name="iconClass" class="form-control"
                                value="fa-user-tie" placeholder="fa-user-tie" maxlength="50">
                            <small class="text-muted">FontAwesome icon class (e.g., fa-user-tie)</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="isActive" name="isActive" checked>
                            <label class="form-check-label" for="isActive">
                                Active
                            </label>
                        </div>
                        <small class="text-muted">Inactive role types won't appear in dropdowns</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Role Type
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function saveRoleType(event) {
    event.preventDefault();

    const form = document.getElementById('manageRoleTypeForm');
    const formData = new FormData(form);
    const roleTypeID = document.getElementById('roleTypeID').value;

    formData.append('action', roleTypeID ? 'update' : 'create');
    formData.append('isActive', document.getElementById('isActive').checked ? 'Y' : 'N');

    fetch('<?= $base ?>php/scripts/global/admin/manage_role_types.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof showToast === 'function') {
                showToast(data.message || 'Role type saved successfully', 'success');
            } else {
                alert(data.message || 'Role type saved successfully');
            }
            setTimeout(() => location.reload(), 1000);
        } else {
            if (typeof showToast === 'function') {
                showToast('Error: ' + (data.message || 'Failed to save role type'), 'error');
            } else {
                alert('Error: ' + (data.message || 'Failed to save role type'));
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof showToast === 'function') {
            showToast('An error occurred while saving the role type', 'error');
        } else {
            alert('An error occurred while saving the role type');
        }
    });
}

// Sync color picker and text input
document.getElementById('colorCode').addEventListener('input', function() {
    const colorPicker = document.getElementById('colorCodePicker');
    if (this.value.match(/^#[0-9A-Fa-f]{6}$/)) {
        colorPicker.value = this.value;
    }
});
</script>

