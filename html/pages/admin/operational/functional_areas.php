<?php
/**
 * Functional Areas Management Page
 * Allows admins to define and manage functional areas
 */

// Check authentication
if(!$isValidUser) {
    Alert::info("You need to be logged in to access this page", true);
    include "includes/core/log_in_script.php";
    return;
}

// Check admin permissions
if (!$isAdmin && !$isValidAdmin) {
    Alert::error("Access denied. Administrator privileges required.", true);
    return;
}

// Include help component
include __DIR__ . '/../../../includes/components/operational_help.php';

global $DBConn, $userID, $orgDataID;

$pageTitle = "Functional Areas Management";
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0"><?php echo htmlspecialchars($pageTitle); ?></h4>
                    <p class="text-muted mb-0 help-text">
                        Define and manage functional areas for your organization. Functional areas can be shared across organizations or organization-specific.
                        <?php echo renderHelpPopover('Functional Areas', 'Functional areas represent departments or business units (e.g., Finance, HR, IT). They help organize processes, workflows, and templates. Shared functional areas can be used by multiple organizations, while organization-specific ones are private to your organization.', 'right'); ?>
                    </p>
                </div>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="?s=admin">Admin</a></li>
                        <li class="breadcrumb-item"><a href="?s=admin&ss=operational">Operational Work</a></li>
                        <li class="breadcrumb-item active">Functional Areas</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Functional Areas</h4>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#functionalAreaModal" data-action="open-functional-area-modal" data-functional-area-action="create">
                            <i class="ri-add-line me-1"></i> Create Functional Area
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="functionalAreasTable">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Order</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="functionalAreasTableBody">
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Functional Area Modal -->
<div class="modal fade" id="functionalAreaModal" tabindex="-1" aria-labelledby="functionalAreaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="functionalAreaModalLabel">Create Functional Area</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="functionalAreaForm">
                    <input type="hidden" id="functionalAreaFormAction" name="action" value="create">
                    <input type="hidden" id="functionalAreaFormID" name="functionalAreaID" value="">

                    <div class="mb-3">
                        <label for="functionalAreaCode" class="form-label">
                            Functional Area Code <span class="text-danger">*</span>
                            <?php echo renderHelpPopover('Functional Area Code', 'A unique code identifier for the functional area (e.g., FIN, HR, IT). This code must be unique and is used for system references. Use uppercase letters and keep it short (3-10 characters).', 'right'); ?>
                        </label>
                        <input type="text" class="form-control" id="functionalAreaCode" name="functionalAreaCode" required
                               placeholder="e.g., FIN, HR, IT" maxlength="50">
                        <small class="text-muted">Unique code identifier (e.g., FIN, HR, IT)</small>
                    </div>

                    <div class="mb-3">
                        <label for="functionalAreaName" class="form-label">
                            Functional Area Name <span class="text-danger">*</span>
                            <?php echo renderHelpPopover('Functional Area Name', 'The display name for the functional area (e.g., Finance, Human Resources, Information Technology). This name will appear in dropdowns and lists throughout the system.', 'right'); ?>
                        </label>
                        <input type="text" class="form-control" id="functionalAreaName" name="functionalAreaName" required
                               placeholder="e.g., Finance, Human Resources" maxlength="255">
                    </div>

                    <div class="mb-3">
                        <label for="functionalAreaDescription" class="form-label">
                            Description
                            <?php echo renderHelpPopover('Description', 'A detailed description of what this functional area encompasses, its responsibilities, and scope. This helps users understand the purpose and boundaries of the functional area.', 'right'); ?>
                        </label>
                        <textarea class="form-control" id="functionalAreaDescription" name="functionalAreaDescription" rows="3"
                                  placeholder="Describe the functional area, its responsibilities, and scope..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="isShared" class="form-label">
                                    Sharing
                                    <?php echo renderHelpPopover('Sharing', 'Shared functional areas can be used by multiple organizations. Organization-specific functional areas are private to your organization only. Shared areas are useful for standard departments that exist across organizations.', 'right'); ?>
                                </label>
                                <select class="form-select" id="isShared" name="isShared">
                                    <option value="Y">Shared (Available to all organizations)</option>
                                    <option value="N">Organization-specific (Private to this organization)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="displayOrder" class="form-label">
                                    Display Order
                                    <?php echo renderHelpPopover('Display Order', 'Controls the order in which functional areas appear in dropdowns and lists. Lower numbers appear first. Use this to organize functional areas logically.', 'right'); ?>
                                </label>
                                <input type="number" class="form-control" id="displayOrder" name="displayOrder" value="0" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="functionalAreaStatus" class="form-label">
                            Status
                            <?php echo renderHelpPopover('Status', 'Active functional areas are available for selection when creating processes, workflows, and templates. Inactive functional areas are hidden but remain in the system for historical reference.', 'right'); ?>
                        </label>
                        <select class="form-select" id="functionalAreaStatus" name="isActive">
                            <option value="Y">Active</option>
                            <option value="N">Inactive</option>
                        </select>
                    </div>

                    <div class="alert alert-danger d-none mt-3" id="functionalAreaFormError"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="functionalAreaFormSubmit">
                    <span class="spinner-border spinner-border-sm d-none" id="functionalAreaFormSpinner" role="status"></span>
                    <span id="functionalAreaFormSubmitText">Create Functional Area</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let functionalAreasData = [];

// Load functional areas on page load
document.addEventListener('DOMContentLoaded', function() {
    loadFunctionalAreas();

    // Event delegation for functional areas
    document.addEventListener('click', function(e) {
        const action = e.target.closest('[data-action]')?.getAttribute('data-action');
        if (!action) return;

        const element = e.target.closest('[data-action]');

        switch(action) {
            case 'open-functional-area-modal':
                const faAction = element.getAttribute('data-functional-area-action');
                const faID = element.getAttribute('data-functional-area-id');
                openFunctionalAreaModal(faAction, faID ? parseInt(faID) : null);
                break;

            case 'delete-functional-area':
                const deleteFaID = element.getAttribute('data-functional-area-id');
                if (deleteFaID && confirm('Are you sure you want to delete this functional area?')) {
                    deleteFunctionalArea(parseInt(deleteFaID));
                }
                break;
        }
    });
});

function loadFunctionalAreas() {
    fetch('<?php echo $base; ?>php/scripts/operational/functional_areas/manage_functional_area.php?action=list')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                functionalAreasData = data.functionalAreas;
                renderFunctionalAreasTable(data.functionalAreas);
            } else {
                showError('Failed to load functional areas: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Failed to load functional areas');
        });
}

function renderFunctionalAreasTable(functionalAreas) {
    const tbody = document.getElementById('functionalAreasTableBody');

    if (!functionalAreas || functionalAreas.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No functional areas found. Create one to get started.</td></tr>';
        return;
    }

    tbody.innerHTML = functionalAreas.map(fa => `
        <tr>
            <td><code>${escapeHtml(fa.functionalAreaCode)}</code></td>
            <td><strong>${escapeHtml(fa.functionalAreaName)}</strong></td>
            <td>${escapeHtml(fa.functionalAreaDescription || '-')}</td>
            <td>
                ${fa.isShared === 'Y'
                    ? '<span class="badge bg-info">Shared</span>'
                    : '<span class="badge bg-secondary">Organization-specific</span>'}
            </td>
            <td>
                ${fa.isActive === 'Y'
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>'}
            </td>
            <td>${fa.displayOrder}</td>
            <td>
                <button class="btn btn-sm btn-primary" data-action="open-functional-area-modal" data-functional-area-action="edit" data-functional-area-id="${fa.functionalAreaID}" title="Edit">
                    <i class="ri-edit-line"></i>
                </button>
                <button class="btn btn-sm btn-danger" data-action="delete-functional-area" data-functional-area-id="${fa.functionalAreaID}" title="Delete">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function openFunctionalAreaModal(action, functionalAreaID = null) {
    const modal = new bootstrap.Modal(document.getElementById('functionalAreaModal'));
    const form = document.getElementById('functionalAreaForm');
    const modalTitle = document.getElementById('functionalAreaModalLabel');
    const submitText = document.getElementById('functionalAreaFormSubmitText');
    const formAction = document.getElementById('functionalAreaFormAction');
    const formID = document.getElementById('functionalAreaFormID');
    const errorDiv = document.getElementById('functionalAreaFormError');

    form.reset();
    errorDiv.classList.add('d-none');
    errorDiv.textContent = '';
    formAction.value = action;
    formID.value = '';

    if (action === 'create') {
        modalTitle.textContent = 'Create Functional Area';
        submitText.textContent = 'Create Functional Area';
        document.getElementById('isShared').value = 'Y';
        document.getElementById('functionalAreaStatus').value = 'Y';
        document.getElementById('displayOrder').value = '0';
    } else if (action === 'edit' && functionalAreaID) {
        modalTitle.textContent = 'Edit Functional Area';
        submitText.textContent = 'Update Functional Area';
        formID.value = functionalAreaID;

        fetch(`<?php echo $base; ?>php/scripts/operational/functional_areas/manage_functional_area.php?action=get&functionalAreaID=${functionalAreaID}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.functionalArea) {
                    const fa = data.functionalArea;
                    document.getElementById('functionalAreaCode').value = fa.functionalAreaCode || '';
                    document.getElementById('functionalAreaName').value = fa.functionalAreaName || '';
                    document.getElementById('functionalAreaDescription').value = fa.functionalAreaDescription || '';
                    document.getElementById('isShared').value = fa.isShared || 'Y';
                    document.getElementById('displayOrder').value = fa.displayOrder || 0;
                    document.getElementById('functionalAreaStatus').value = fa.isActive || 'Y';
                } else {
                    showFunctionalAreaError(data.message || 'Failed to load functional area data');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showFunctionalAreaError('Failed to load functional area data');
            });
    }

    modal.show();
}

function showFunctionalAreaError(message) {
    const errorDiv = document.getElementById('functionalAreaFormError');
    errorDiv.textContent = message;
    errorDiv.classList.remove('d-none');
}

function deleteFunctionalArea(functionalAreaID) {
    if (!confirm('Are you sure you want to delete this functional area? This action cannot be undone and may affect existing processes, workflows, and templates.')) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('functionalAreaID', functionalAreaID);

    fetch('<?php echo $base; ?>php/scripts/operational/functional_areas/manage_functional_area.php?action=delete', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof showToast === 'function') {
                showToast('Functional area deleted successfully', 'success');
            } else {
                alert('Functional area deleted successfully');
            }
            loadFunctionalAreas();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the functional area');
    });
}

// Form submission
document.getElementById('functionalAreaFormSubmit')?.addEventListener('click', function() {
    const form = document.getElementById('functionalAreaForm');
    const formData = new FormData(form);
    const action = formData.get('action');
    const submitBtn = this;
    const spinner = document.getElementById('functionalAreaFormSpinner');
    const errorDiv = document.getElementById('functionalAreaFormError');

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    submitBtn.disabled = true;
    spinner.classList.remove('d-none');
    errorDiv.classList.add('d-none');

    const apiAction = action === 'create' ? 'create' : 'update';
    const url = `<?php echo $base; ?>php/scripts/operational/functional_areas/manage_functional_area.php?action=${apiAction}`;

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('functionalAreaModal'));
            modal.hide();

            if (typeof showToast === 'function') {
                showToast(data.message || 'Functional area saved successfully', 'success');
            } else {
                alert(data.message || 'Functional area saved successfully');
            }

            loadFunctionalAreas();
        } else {
            showFunctionalAreaError(data.message || 'Failed to save functional area');
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showFunctionalAreaError('An error occurred while saving the functional area');
        submitBtn.disabled = false;
        spinner.classList.add('d-none');
    });
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showError(message) {
    const tbody = document.getElementById('functionalAreasTableBody');
    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">${escapeHtml(message)}</td></tr>`;
}
</script>

