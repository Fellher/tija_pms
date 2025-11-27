<!-- Project Plan Templates Management Modal -->
<div class="modal fade" id="manageTemplatesModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri-file-copy-line me-2"></i>Manage Project Plan Templates
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Header Actions -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h6 class="mb-1">Project Plan Templates</h6>
                        <p class="text-muted small mb-0">Create and manage reusable project plan templates for your organization</p>
                    </div>
                    <button type="button" class="btn btn-primary" id="createNewTemplateBtn">
                        <i class="ri-add-line me-1"></i>New Template
                    </button>
                </div>

                <!-- Template Form (Hidden by default) -->
                <div id="templateFormSection" class="card custom-card mb-4" style="display: none;">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0" id="formTitle">
                            <i class="ri-edit-line me-2"></i>Create New Template
                        </h6>
                        <button type="button" class="btn btn-sm btn-light" id="cancelTemplateFormBtn">
                            <i class="ri-close-line"></i> Cancel
                        </button>
                    </div>
                    <div class="card-body">
                        <form id="templateForm" action="<?= $base ?>php/scripts/projects/manage_project_plan_template.php" method="POST">
                            <input type="hidden" name="action" id="templateAction" value="create">
                            <input type="hidden" name="templateID" id="templateID" value="">
                            <input type="hidden" name="orgDataID" value="<?= $orgDataID ?>">
                            <input type="hidden" name="entityID" value="<?= $entityID ?>">

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Template Name <span class="text-danger">*</span></label>
                                    <input type="text" name="templateName" id="templateName" class="form-control" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Category</label>
                                    <select name="templateCategory" id="templateCategory" class="form-select">
                                        <option value="">Select Category</option>
                                        <option value="software">Software Development</option>
                                        <option value="construction">Construction</option>
                                        <option value="marketing">Marketing</option>
                                        <option value="research">Research</option>
                                        <option value="consulting">Consulting</option>
                                        <option value="design">Design</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Description</label>
                                    <textarea name="templateDescription" id="templateDescription" class="form-control" rows="2" placeholder="Brief description of when to use this template"></textarea>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="isPublic" id="isPublic" value="Y">
                                        <label class="form-check-label" for="isPublic">
                                            <strong>Make Public</strong>
                                            <small class="text-muted d-block">Allow all users in your organization to use this template</small>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <hr class="my-3">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">Template Phases</h6>
                                        <div class="text-muted small">
                                            <i class="ri-information-line me-1"></i>
                                            Duration percentages should total 100%
                                        </div>
                                    </div>

                                    <div id="templatePhasesContainer">
                                        <!-- Phases will be added dynamically -->
                                    </div>

                                    <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="addTemplatePhaseBtn">
                                        <i class="ri-add-line me-1"></i>Add Phase
                                    </button>

                                    <div class="alert alert-info mt-3" id="percentageInfo">
                                        <i class="ri-information-line me-2"></i>
                                        Total: <strong id="totalPercent">0</strong>%
                                        <span class="ms-2" id="percentStatus"></span>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-light" id="cancelTemplateFormBtn2">Cancel</button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-save-line me-1"></i>Save Template
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Templates List -->
                <div id="templatesListSection">
                    <div class="row" id="templatesContainer">
                        <!-- Templates will be loaded here -->
                        <div class="col-12 text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted mt-2">Loading templates...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.template-card {
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.template-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.template-card.system-template {
    border-left-color: #0d6efd;
}

.template-card.public-template {
    border-left-color: #198754;
}

.template-card.private-template {
    border-left-color: #6c757d;
}

.template-phase-item {
    border-left: 3px solid #e9ecef;
    transition: all 0.2s ease;
}

.template-phase-item:hover {
    border-left-color: #0d6efd;
    background-color: #f8f9fa;
}

.phase-percent-input {
    width: 80px;
}

#percentageInfo.valid {
    border-color: #198754;
    background-color: rgba(25, 135, 84, 0.1);
}

#percentageInfo.invalid {
    border-color: #dc3545;
    background-color: rgba(220, 53, 69, 0.1);
}
</style>

<script>
(function() {
    'use strict';

    let templates = [];
    let editingTemplateID = null;

    // Initialize when modal is shown
    document.getElementById('manageTemplatesModal')?.addEventListener('shown.bs.modal', function() {
        loadTemplates();
    });

    // ================================================================
    // LOAD TEMPLATES
    // ================================================================
    function loadTemplates() {
        const orgDataID = document.querySelector('input[name="orgDataID"]')?.value;
        const entityID = document.querySelector('input[name="entityID"]')?.value;

        fetch(`<?= $base ?>php/scripts/projects/get_project_plan_templates.php?orgDataID=${orgDataID}&entityID=${entityID}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    templates = data.templates;
                    displayTemplates(templates);
                } else {
                    showError('Failed to load templates: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error loading templates:', error);
                showError('Failed to load templates. Please try again.');
            });
    }

    // ================================================================
    // DISPLAY TEMPLATES
    // ================================================================
    function displayTemplates(templates) {
        const container = document.getElementById('templatesContainer');

        if (!templates || templates.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="ri-file-copy-line" style="font-size: 48px; color: #dee2e6;"></i>
                    <p class="text-muted mt-3">No templates found. Create your first template to get started!</p>
                </div>
            `;
            return;
        }

        let html = '';
        templates.forEach(template => {
            const isSystem = template.isSystemTemplate === 'Y';
            const isPublic = template.isPublic === 'Y';
            const cardClass = isSystem ? 'system-template' : (isPublic ? 'public-template' : 'private-template');
            const badge = isSystem ? '<span class="badge bg-primary">System</span>' :
                         (isPublic ? '<span class="badge bg-success">Public</span>' :
                          '<span class="badge bg-secondary">Private</span>');

            html += `
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card custom-card template-card ${cardClass} h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0">${escapeHtml(template.templateName)}</h6>
                                ${badge}
                            </div>

                            ${template.templateDescription ? `<p class="text-muted small mb-2">${escapeHtml(template.templateDescription)}</p>` : ''}

                            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                <div class="small text-muted">
                                    <i class="ri-list-check me-1"></i>${template.phaseCount || 0} phases
                                    ${template.templateCategory ? `<span class="ms-2"><i class="ri-folder-line me-1"></i>${template.templateCategory}</span>` : ''}
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-primary-light view-template" data-template-id="${template.templateID}" title="View Details">
                                        <i class="ri-eye-line"></i>
                                    </button>
                                    ${!isSystem ? `
                                        <button type="button" class="btn btn-info-light edit-template" data-template-id="${template.templateID}" title="Edit">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                        <button type="button" class="btn btn-warning-light toggle-visibility" data-template-id="${template.templateID}" data-is-public="${template.isPublic}" title="${isPublic ? 'Make Private' : 'Make Public'}">
                                            <i class="ri-${isPublic ? 'lock' : 'global'}-line"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger-light delete-template" data-template-id="${template.templateID}" title="Delete">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    ` : ''}
                                </div>
                            </div>

                            ${template.usageCount > 0 ? `<div class="small text-muted mt-2"><i class="ri-bar-chart-line me-1"></i>Used ${template.usageCount} times</div>` : ''}
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;

        // Attach event listeners
        attachTemplateListeners();
    }

    // ================================================================
    // EVENT LISTENERS
    // ================================================================
    function attachTemplateListeners() {
        // View template
        document.querySelectorAll('.view-template').forEach(btn => {
            btn.addEventListener('click', function() {
                viewTemplate(this.dataset.templateId);
            });
        });

        // Edit template
        document.querySelectorAll('.edit-template').forEach(btn => {
            btn.addEventListener('click', function() {
                editTemplate(this.dataset.templateId);
            });
        });

        // Toggle visibility
        document.querySelectorAll('.toggle-visibility').forEach(btn => {
            btn.addEventListener('click', function() {
                toggleTemplateVisibility(this.dataset.templateId, this.dataset.isPublic);
            });
        });

        // Delete template
        document.querySelectorAll('.delete-template').forEach(btn => {
            btn.addEventListener('click', function() {
                deleteTemplate(this.dataset.templateId);
            });
        });
    }

    // ================================================================
    // FORM MANAGEMENT
    // ================================================================
    document.getElementById('createNewTemplateBtn')?.addEventListener('click', function() {
        showTemplateForm();
    });

    document.getElementById('cancelTemplateFormBtn')?.addEventListener('click', hideTemplateForm);
    document.getElementById('cancelTemplateFormBtn2')?.addEventListener('click', hideTemplateForm);

    document.getElementById('addTemplatePhaseBtn')?.addEventListener('click', function() {
        addTemplatePhase();
    });

    document.getElementById('templateForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        saveTemplate();
    });

    function showTemplateForm(templateData = null) {
        document.getElementById('templateFormSection').style.display = 'block';
        document.getElementById('templatesListSection').style.display = 'none';

        if (templateData) {
            // Edit mode
            document.getElementById('formTitle').innerHTML = '<i class="ri-edit-line me-2"></i>Edit Template';
            document.getElementById('templateAction').value = 'update';
            document.getElementById('templateID').value = templateData.templateID;
            document.getElementById('templateName').value = templateData.templateName;
            document.getElementById('templateDescription').value = templateData.templateDescription || '';
            document.getElementById('templateCategory').value = templateData.templateCategory || '';
            document.getElementById('isPublic').checked = templateData.isPublic === 'Y';

            // Load phases
            document.getElementById('templatePhasesContainer').innerHTML = '';
            if (templateData.phases && templateData.phases.length > 0) {
                templateData.phases.forEach(phase => {
                    addTemplatePhase(phase.phaseName, phase.phaseDescription, phase.durationPercent);
                });
            }
        } else {
            // Create mode
            document.getElementById('formTitle').innerHTML = '<i class="ri-add-line me-2"></i>Create New Template';
            document.getElementById('templateAction').value = 'create';
            document.getElementById('templateForm').reset();
            document.getElementById('templateID').value = '';
            document.getElementById('templatePhasesContainer').innerHTML = '';

            // Add first phase by default
            addTemplatePhase();
        }
    }

    function hideTemplateForm() {
        document.getElementById('templateFormSection').style.display = 'none';
        document.getElementById('templatesListSection').style.display = 'block';
        document.getElementById('templateForm').reset();
        editingTemplateID = null;
    }

    // ================================================================
    // PHASE MANAGEMENT
    // ================================================================
    function addTemplatePhase(name = '', description = '', percent = '') {
        const container = document.getElementById('templatePhasesContainer');
        const phaseCount = container.querySelectorAll('.template-phase-item').length + 1;

        const phaseHTML = `
            <div class="card custom-card mb-2 template-phase-item">
                <div class="card-body p-3">
                    <div class="row g-2">
                        <div class="col-md-5">
                            <label class="form-label small">Phase Name</label>
                            <input type="text" name="phaseName[]" class="form-control form-control-sm" placeholder="Phase name" value="${name}" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small">Description</label>
                            <input type="text" name="phaseDescription[]" class="form-control form-control-sm" placeholder="Phase description" value="${description}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Duration %</label>
                            <div class="input-group input-group-sm">
                                <input type="number" name="phasePercent[]" class="form-control form-control-sm phase-percent-input" placeholder="%" value="${percent}" min="0" max="100" step="0.1">
                                <button type="button" class="btn btn-danger-light remove-template-phase" title="Remove phase">
                                    <i class="ri-close-line"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', phaseHTML);

        // Add remove handler
        const removeBtn = container.lastElementChild.querySelector('.remove-template-phase');
        removeBtn.addEventListener('click', function() {
            this.closest('.template-phase-item').remove();
            updatePercentageTotal();
        });

        // Add percent change handler
        const percentInput = container.lastElementChild.querySelector('.phase-percent-input');
        percentInput.addEventListener('input', updatePercentageTotal);

        updatePercentageTotal();
    }

    function updatePercentageTotal() {
        const percentInputs = document.querySelectorAll('.phase-percent-input');
        let total = 0;

        percentInputs.forEach(input => {
            total += parseFloat(input.value) || 0;
        });

        const totalEl = document.getElementById('totalPercent');
        const statusEl = document.getElementById('percentStatus');
        const infoBox = document.getElementById('percentageInfo');

        totalEl.textContent = total.toFixed(1);

        if (Math.abs(total - 100) < 0.1) {
            statusEl.innerHTML = '<i class="ri-checkbox-circle-fill text-success"></i> Perfect!';
            infoBox.classList.remove('invalid');
            infoBox.classList.add('valid');
        } else if (total > 100) {
            statusEl.innerHTML = '<i class="ri-error-warning-fill text-danger"></i> Over 100%';
            infoBox.classList.remove('valid');
            infoBox.classList.add('invalid');
        } else if (total < 100) {
            statusEl.innerHTML = '<i class="ri-information-fill text-warning"></i> Under 100%';
            infoBox.classList.remove('valid', 'invalid');
        }
    }

    // ================================================================
    // TEMPLATE ACTIONS
    // ================================================================
    function viewTemplate(templateID) {
        const template = templates.find(t => t.templateID == templateID);
        if (!template) return;

        // Load full template with phases
        fetch(`<?= $base ?>php/scripts/projects/get_project_plan_templates.php?templateID=${templateID}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showTemplateDetails(data.template);
                } else {
                    showError(data.error || 'Failed to load template details');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Failed to load template details');
            });
    }

    function showTemplateDetails(template) {
        const phasesHTML = template.phases && template.phases.length > 0 ?
            template.phases.map((phase, index) => `
                <li class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>${index + 1}. ${escapeHtml(phase.phaseName)}</strong>
                            ${phase.phaseDescription ? `<p class="mb-0 text-muted small">${escapeHtml(phase.phaseDescription)}</p>` : ''}
                        </div>
                        ${phase.durationPercent ? `<span class="badge bg-info">${phase.durationPercent}%</span>` : ''}
                    </div>
                </li>
            `).join('') : '<li class="list-group-item text-muted">No phases defined</li>';

        const modalContent = `
            <div class="modal fade" id="templateDetailsModal" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${escapeHtml(template.templateName)}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            ${template.templateDescription ? `<p class="text-muted">${escapeHtml(template.templateDescription)}</p>` : ''}
                            <hr>
                            <h6 class="mb-3">Template Phases (${template.phases.length})</h6>
                            <ul class="list-group">
                                ${phasesHTML}
                            </ul>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal if any
        document.getElementById('templateDetailsModal')?.remove();

        // Add and show modal
        document.body.insertAdjacentHTML('beforeend', modalContent);
        const modal = new bootstrap.Modal(document.getElementById('templateDetailsModal'));
        modal.show();

        // Clean up on close
        document.getElementById('templateDetailsModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }

    function editTemplate(templateID) {
        // Load full template with phases
        fetch(`<?= $base ?>php/scripts/projects/get_project_plan_templates.php?templateID=${templateID}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showTemplateForm(data.template);
                } else {
                    showError(data.error || 'Failed to load template');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showError('Failed to load template');
            });
    }

    function toggleTemplateVisibility(templateID, currentVisibility) {
        const newVisibility = currentVisibility === 'Y' ? 'private' : 'public';
        if (!confirm(`Make this template ${newVisibility}?`)) return;

        const formData = new FormData();
        formData.append('action', 'toggleVisibility');
        formData.append('templateID', templateID);

        fetch('<?= $base ?>php/scripts/projects/manage_project_plan_template.php', {
            method: 'POST',
            body: formData
        })
        .then(() => {
            loadTemplates(); // Reload list
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Failed to update template visibility');
        });
    }

    function deleteTemplate(templateID) {
        if (!confirm('Are you sure you want to delete this template? This action cannot be undone.')) return;

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('templateID', templateID);

        fetch('<?= $base ?>php/scripts/projects/manage_project_plan_template.php', {
            method: 'POST',
            body: formData
        })
        .then(() => {
            loadTemplates(); // Reload list
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Failed to delete template');
        });
    }

    function saveTemplate() {
        const form = document.getElementById('templateForm');
        const formData = new FormData(form);

        fetch('<?= $base ?>php/scripts/projects/manage_project_plan_template.php', {
            method: 'POST',
            body: formData
        })
        .then(() => {
            hideTemplateForm();
            loadTemplates(); // Reload list
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Failed to save template');
        });
    }

    // ================================================================
    // UTILITIES
    // ================================================================
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function showError(message) {
        const container = document.getElementById('templatesContainer');
        container.innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger">
                    <i class="ri-error-warning-line me-2"></i>${message}
                </div>
            </div>
        `;
    }

    // Expose for external access
    window.refreshTemplates = loadTemplates;
})();
</script>

