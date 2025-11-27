<!-- Manage Role Level Modal -->
<div class="modal fade" id="manageRoleLevelModal" tabindex="-1" aria-labelledby="manageRoleLevelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manageRoleLevelModalLabel">Add New Role Level</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="manageRoleLevelForm" onsubmit="saveRoleLevel(event)">
                <div class="modal-body">
                    <input type="hidden" id="roleLevelID" name="roleLevelID" value="">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="levelNumber" class="form-label">Level Number <span class="text-danger">*</span></label>
                            <input type="number" id="levelNumber" name="levelNumber" class="form-control"
                                placeholder="0" required min="0" max="99">
                            <small class="text-muted">Numeric level (0-99). Lower numbers = higher authority</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="levelName" class="form-label">Level Name <span class="text-danger">*</span></label>
                            <input type="text" id="levelName" name="levelName" class="form-control"
                                placeholder="e.g., Board/External, CEO/Executive" required maxlength="100">
                            <small class="text-muted">Display name for the role level</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="levelCode" class="form-label">Level Code</label>
                            <input type="text" id="levelCode" name="levelCode" class="form-control"
                                placeholder="e.g., BOARD, CEO, CSUITE" maxlength="20" style="text-transform: uppercase;">
                            <small class="text-muted">Short unique code (uppercase, optional)</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="displayOrder" class="form-label">Display Order</label>
                            <input type="number" id="displayOrder" name="displayOrder" class="form-control"
                                value="0" min="0">
                            <small class="text-muted">Order in dropdown lists (lower = first)</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="levelDescription" class="form-label">Description</label>
                        <textarea id="levelDescription" name="levelDescription" class="form-control"
                            rows="3" placeholder="Describe the role level..."></textarea>
                        <small class="text-muted">Optional description of the role level</small>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="isActive" name="isActive" checked>
                            <label class="form-check-label" for="isActive">
                                Active
                            </label>
                        </div>
                        <small class="text-muted">Inactive role levels won't appear in dropdowns</small>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> Lower level numbers indicate higher authority in the organizational hierarchy.
                        Level 0 is typically the highest (Board/External), while higher numbers represent lower levels (Entry Level).
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Role Level
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function saveRoleLevel(event) {
    event.preventDefault();

    const form = document.getElementById('manageRoleLevelForm');
    const formData = new FormData(form);
    const roleLevelID = document.getElementById('roleLevelID').value;

    formData.append('action', roleLevelID ? 'update' : 'create');
    formData.append('isActive', document.getElementById('isActive').checked ? 'Y' : 'N');

    fetch('<?= $base ?>php/scripts/global/admin/manage_role_levels.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof showToast === 'function') {
                showToast(data.message || 'Role level saved successfully', 'success');
            } else {
                alert(data.message || 'Role level saved successfully');
            }
            setTimeout(() => location.reload(), 1000);
        } else {
            if (typeof showToast === 'function') {
                showToast('Error: ' + (data.message || 'Failed to save role level'), 'error');
            } else {
                alert('Error: ' + (data.message || 'Failed to save role level'));
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof showToast === 'function') {
            showToast('An error occurred while saving the role level', 'error');
        } else {
            alert('An error occurred while saving the role level');
        }
    });
}
</script>

