<?php
/**
 * Invoice Template Management Page
 *
 * Manage invoice templates for the organization
 *
 * @package    TIJA_PMS
 * @subpackage Invoicing
 */

// Security check
if(!$isValidUser) {
    Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

// Admin check
if (!$isAdmin && !$isValidAdmin) {
    Alert::error("You need administrator privileges to manage invoice templates", true);
    header("Location: {$base}html/?s=user&ss=invoices&p=list");
    exit;
}

// Get employee details for organization context
$employeeID = $userDetails->ID;
$employeeDetails = Employee::employees(array('ID' => $employeeID), true, $DBConn);

// Resolve organization and entity IDs
$orgDataID = isset($_GET['orgDataID'])
    ? Utility::clean_string($_GET['orgDataID'])
    : (isset($employeeDetails->orgDataID) && !empty($employeeDetails->orgDataID)
        ? $employeeDetails->orgDataID
        : (isset($_SESSION['orgDataID']) && !empty($_SESSION['orgDataID'])
            ? $_SESSION['orgDataID']
            : ""));

$entityID = isset($_GET['entityID'])
    ? Utility::clean_string($_GET['entityID'])
    : (isset($employeeDetails->entityID) && !empty($employeeDetails->entityID)
        ? $employeeDetails->entityID
        : (isset($_SESSION['entityID'])
            ? $_SESSION['entityID']
            : ''));

$orgDataID = $orgDataID ?: "";

// Get templates
$templatesWhereArr = array('Suspended' => 'N');
if ($orgDataID) {
    $templatesWhereArr['orgDataID'] = $orgDataID;
}
if ($entityID) {
    $templatesWhereArr['entityID'] = $entityID;
}
$templates = Invoice::invoice_templates($templatesWhereArr, false, $DBConn);

$pageTitle = "Invoice Templates";
?>

<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <h1 class="page-title fw-semibold fs-24 mb-0">Invoice Templates</h1>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= $base ?>html/?s=user&ss=invoices&p=list">Invoices</a></li>
                <li class="breadcrumb-item active" aria-current="page">Templates</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-lg-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Total Templates</p>
                        <h3 class="mb-0 fw-semibold"><?= $templates ? count($templates) : 0 ?></h3>
                        <small class="text-muted fs-11">Available templates</small>
                    </div>
                    <div class="ms-2">
                        <span class="avatar avatar-md bg-primary-transparent">
                            <i class="ri-file-list-3-line fs-20"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Default Template</p>
                        <h3 class="mb-0 fw-semibold">
                            <?php
                            $defaultCount = 0;
                            if ($templates) {
                                foreach ($templates as $template) {
                                    if ($template->isDefault == 'Y') $defaultCount++;
                                }
                            }
                            echo $defaultCount;
                            ?>
                        </h3>
                        <small class="text-muted fs-11">System default</small>
                    </div>
                    <div class="ms-2">
                        <span class="avatar avatar-md bg-warning-transparent">
                            <i class="ri-star-line fs-20"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Active Templates</p>
                        <h3 class="mb-0 fw-semibold">
                            <?php
                            $activeCount = 0;
                            if ($templates) {
                                foreach ($templates as $template) {
                                    if ($template->isActive == 'Y') $activeCount++;
                                }
                            }
                            echo $activeCount;
                            ?>
                        </h3>
                        <small class="text-muted fs-11">Currently active</small>
                    </div>
                    <div class="ms-2">
                        <span class="avatar avatar-md bg-success-transparent">
                            <i class="ri-checkbox-circle-line fs-20"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Template Types</p>
                        <h3 class="mb-0 fw-semibold">
                            <?php
                            $types = array();
                            if ($templates) {
                                foreach ($templates as $template) {
                                    $types[$template->templateType] = true;
                                }
                            }
                            echo count($types);
                            ?>
                        </h3>
                        <small class="text-muted fs-11">Different types</small>
                    </div>
                    <div class="ms-2">
                        <span class="avatar avatar-md bg-info-transparent">
                            <i class="ri-layout-line fs-20"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Templates Management -->
<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">
                    <h5 class="mb-0">Invoice Templates</h5>
                    <small class="text-muted">Manage reusable invoice templates</small>
                </div>
                <button type="button" class="btn btn-primary btn-wave" data-bs-toggle="modal" data-bs-target="#manageTemplateModal" data-action="reset-form">
                    <i class="ri-add-line me-2"></i>Add New Template
                </button>
            </div>
            <div class="card-body">
                <?php if (!$templates): ?>
                    <div class="text-center py-5">
                        <div class="avatar avatar-xl bg-primary-transparent mx-auto mb-4">
                            <i class="ri-file-list-3-line fs-32"></i>
                        </div>
                        <h5 class="mb-3">No Templates Found</h5>
                        <p class="text-muted mb-4">Create your first invoice template to get started</p>
                        <button class="btn btn-primary btn-wave" data-bs-toggle="modal" data-bs-target="#manageTemplateModal" data-action="reset-form">
                            <i class="ri-add-line me-2"></i>Add Template
                        </button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-primary">
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 20%;">Template Name</th>
                                    <th style="width: 15%;">Type</th>
                                    <th style="width: 15%;">Code</th>
                                    <th style="width: 10%;">Currency</th>
                                    <th style="width: 10%;">Status</th>
                                    <th style="width: 25%;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($templates as $index => $template): ?>
                                    <tr>
                                        <td class="text-center"><?= $index + 1 ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <strong><?= htmlspecialchars($template->templateName) ?></strong>
                                                    <?php if ($template->isDefault == 'Y'): ?>
                                                        <span class="badge bg-warning-transparent ms-2"><small>Default</small></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info-transparent">
                                                <?= ucfirst(str_replace('_', ' ', $template->templateType)) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <code><?= htmlspecialchars($template->templateCode) ?></code>
                                        </td>
                                        <td><?= htmlspecialchars($template->currency) ?></td>
                                        <td class="text-center">
                                            <?php if ($template->isActive == 'Y'): ?>
                                                <span class="badge bg-success-transparent">
                                                    <i class="ri-check-line me-1"></i>Active
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-transparent">
                                                    <i class="ri-close-line me-1"></i>Inactive
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <button type="button"
                                                    class="btn btn-sm btn-primary-light btn-wave edit-template"
                                                    data-template-id="<?= $template->templateID ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#manageTemplateModal"
                                                    title="Edit">
                                                    <i class="ri-edit-line"></i>
                                                </button>
                                                <button type="button"
                                                    class="btn btn-sm btn-info-light btn-wave preview-template"
                                                    data-template-id="<?= $template->templateID ?>"
                                                    title="Preview">
                                                    <i class="ri-eye-line"></i>
                                                </button>
                                                <?php if ($template->isDefault != 'Y'): ?>
                                                    <button type="button"
                                                        class="btn btn-sm btn-danger-light btn-wave delete-template"
                                                        data-template-id="<?= $template->templateID ?>"
                                                        data-template-name="<?= htmlspecialchars($template->templateName, ENT_QUOTES) ?>"
                                                        title="Delete">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button"
                                                        class="btn btn-sm btn-secondary-light btn-wave"
                                                        disabled
                                                        title="Cannot delete default template">
                                                        <i class="ri-lock-line"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Manage Template Modal -->
<div class="modal fade" id="manageTemplateModal" tabindex="-1" aria-labelledby="manageTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="manageTemplateModalLabel">Add New Invoice Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="manageTemplateForm">
                <div class="modal-body">
                    <input type="hidden" id="templateID" name="templateID" value="">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="templateName" class="form-label">Template Name <span class="text-danger">*</span></label>
                            <input type="text" id="templateName" name="templateName" class="form-control" required maxlength="255">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="templateCode" class="form-label">Template Code <span class="text-danger">*</span></label>
                            <input type="text" id="templateCode" name="templateCode" class="form-control" required maxlength="100" style="text-transform: uppercase;">
                            <small class="text-muted">Unique code (uppercase, no spaces)</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="templateDescription" class="form-label">Description</label>
                        <textarea id="templateDescription" name="templateDescription" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="templateType" class="form-label">Template Type <span class="text-danger">*</span></label>
                            <select id="templateType" name="templateType" class="form-select" required>
                                <option value="standard">Standard</option>
                                <option value="hourly">Hourly</option>
                                <option value="expense">Expense</option>
                                <option value="milestone">Milestone</option>
                                <option value="recurring">Recurring</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="currency" class="form-label">Currency</label>
                            <select id="currency" name="currency" class="form-select">
                                <option value="KES">KES - Kenyan Shilling</option>
                                <option value="USD">USD - US Dollar</option>
                                <option value="EUR">EUR - Euro</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="defaultTaxPercent" class="form-label">Default Tax %</label>
                            <input type="number" id="defaultTaxPercent" name="defaultTaxPercent" class="form-control" step="0.01" value="0" min="0" max="100">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="companyName" class="form-label">Company Name</label>
                            <input type="text" id="companyName" name="companyName" class="form-control" maxlength="255">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="companyTaxID" class="form-label">Tax ID / VAT Number</label>
                            <input type="text" id="companyTaxID" name="companyTaxID" class="form-control" maxlength="100">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="templateLogo" class="form-label">Company Logo</label>
                        <div class="d-flex align-items-center gap-3">
                            <div id="logoPreview" style="display: none;">
                                <img id="logoPreviewImg" src="" alt="Logo Preview" style="max-height: 80px; max-width: 200px; border: 1px solid #ddd; padding: 5px; border-radius: 4px;">
                            </div>
                            <div class="flex-fill">
                                <input type="file" id="templateLogo" name="templateLogo" class="form-control template-logo-input" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                <small class="text-muted">Upload company logo (JPG, PNG, GIF, WebP - Max 2MB)</small>
                                <input type="hidden" id="currentLogoURL" value="">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="companyAddress" class="form-label">Company Address</label>
                        <textarea id="companyAddress" name="companyAddress" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="companyPhone" class="form-label">Phone</label>
                            <input type="text" id="companyPhone" name="companyPhone" class="form-control" maxlength="50">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="companyEmail" class="form-label">Email</label>
                            <input type="email" id="companyEmail" name="companyEmail" class="form-control" maxlength="255">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="companyWebsite" class="form-label">Website</label>
                            <input type="url" id="companyWebsite" name="companyWebsite" class="form-control" maxlength="255">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="defaultTerms" class="form-label">Default Payment Terms</label>
                        <textarea id="defaultTerms" name="defaultTerms" class="form-control" rows="2" placeholder="e.g., Payment due within 30 days"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="defaultNotes" class="form-label">Default Notes</label>
                        <textarea id="defaultNotes" name="defaultNotes" class="form-control" rows="2" placeholder="Default notes to appear on invoices"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="taxEnabled" name="taxEnabled" checked>
                                <label class="form-check-label" for="taxEnabled">Enable Tax Calculation</label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="isDefault" name="isDefault">
                                <label class="form-check-label" for="isDefault">Set as Default Template</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="isActive" name="isActive" checked>
                            <label class="form-check-label" for="isActive">Active</label>
                        </div>
                    </div>

                    <div id="logoUploadSection" style="display: none;">
                        <hr>
                        <div class="mb-3">
                            <label class="form-label">Upload Logo</label>
                            <div class="d-flex gap-2">
                                <input type="file" id="templateLogoEdit" name="templateLogoEdit" class="form-control template-logo-input" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                                <button type="button" class="btn btn-info" id="uploadLogoBtn" data-action="upload-logo">
                                    <i class="ri-upload-line me-1"></i>Upload
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-2"></i>Save Template
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';
    // Set siteUrl on window if not already set, then use local const
    if (typeof window.siteUrl === 'undefined') {
        window.siteUrl = '<?= $base ?>';
    }
    const siteUrl = window.siteUrl;

    // Toast notification function
    function showToast(message, type = 'info') {
        // Create toast container if it doesn't exist
        let toastContainer = document.querySelector('.toast-container-invoices');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container-invoices position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }

        // Icon and color mapping
        const config = {
            success: { bg: 'bg-success', icon: 'ri-checkbox-circle-line', text: 'text-white' },
            error: { bg: 'bg-danger', icon: 'ri-error-warning-line', text: 'text-white' },
            danger: { bg: 'bg-danger', icon: 'ri-error-warning-line', text: 'text-white' },
            warning: { bg: 'bg-warning', icon: 'ri-alert-line', text: 'text-dark' },
            info: { bg: 'bg-info', icon: 'ri-information-line', text: 'text-white' }
        };

        const toastConfig = config[type] || config.info;
        const toastId = 'toast-' + Date.now();

        // Create toast element
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `toast align-items-center ${toastConfig.bg} ${toastConfig.text} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="${toastConfig.icon} me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

        toastContainer.appendChild(toast);

        // Initialize and show toast
        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            const bsToast = new bootstrap.Toast(toast, {
                autohide: true,
                delay: type === 'error' || type === 'danger' ? 5000 : 3000
            });
            bsToast.show();
        } else {
            // Fallback if Bootstrap is not available
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Remove toast after it's hidden
        toast.addEventListener('hidden.bs.toast', function() {
            toast.remove();
        });
    }

    // Helper functions
    function resetTemplateForm() {
        document.getElementById('manageTemplateForm').reset();
        document.getElementById('templateID').value = '';
        document.getElementById('taxEnabled').checked = true;
        document.getElementById('isActive').checked = true;
        document.getElementById('isDefault').checked = false;
        document.getElementById('currentLogoURL').value = '';
        document.getElementById('logoPreview').style.display = 'none';
        document.getElementById('logoUploadSection').style.display = 'none';
        document.querySelector('#manageTemplateModal .modal-title').textContent = 'Add New Invoice Template';
    }

    function editTemplate(id) {
        fetch(`${siteUrl}php/scripts/invoices/manage_template.php?action=get&templateID=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const template = data.template;

                    document.getElementById('templateID').value = template.templateID;
                    document.getElementById('templateName').value = template.templateName;
                    document.getElementById('templateCode').value = template.templateCode;
                    document.getElementById('templateDescription').value = template.templateDescription || '';
                    document.getElementById('templateType').value = template.templateType;
                    document.getElementById('currency').value = template.currency || 'KES';
                    document.getElementById('defaultTaxPercent').value = template.defaultTaxPercent || 0;
                    document.getElementById('companyName').value = template.companyName || '';
                    document.getElementById('companyTaxID').value = template.companyTaxID || '';
                    document.getElementById('companyAddress').value = template.companyAddress || '';
                    document.getElementById('companyPhone').value = template.companyPhone || '';
                    document.getElementById('companyEmail').value = template.companyEmail || '';
                    document.getElementById('companyWebsite').value = template.companyWebsite || '';
                    document.getElementById('defaultTerms').value = template.defaultTerms || '';
                    document.getElementById('defaultNotes').value = template.defaultNotes || '';
                    document.getElementById('taxEnabled').checked = template.taxEnabled === 'Y';
                    document.getElementById('isDefault').checked = template.isDefault === 'Y';
                    document.getElementById('isActive').checked = template.isActive === 'Y';

                    // Handle logo preview
                    if (template.logoURL) {
                        document.getElementById('currentLogoURL').value = template.logoURL;
                        document.getElementById('logoPreviewImg').src = template.logoURL;
                        document.getElementById('logoPreview').style.display = 'block';
                    } else {
                        document.getElementById('currentLogoURL').value = '';
                        document.getElementById('logoPreview').style.display = 'none';
                    }

                    // Show logo upload section when editing
                    document.getElementById('logoUploadSection').style.display = 'block';

                    document.querySelector('#manageTemplateModal .modal-title').textContent = 'Edit Invoice Template';
                } else {
                    showToast('Error: ' + (data.message || 'Failed to load template'), 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while loading the template', 'error');
            });
    }

    function deleteTemplate(id, name) {
        if (confirm(`Are you sure you want to delete the template "${name}"?\n\nWarning: This may affect invoices using this template.`)) {
            fetch(`${siteUrl}php/scripts/invoices/manage_template.php?action=delete&templateID=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Template deleted successfully', 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showToast('Error: ' + (data.message || 'Failed to delete template'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred while deleting the template', 'error');
                });
        }
    }

    function previewTemplate(id) {
        window.open(`${siteUrl}html/?s=user&ss=invoices&p=template_preview&tid=${id}`, '_blank');
    }

    function handleLogoUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            showToast('Invalid file type. Please upload JPG, PNG, GIF, or WebP image.', 'warning');
            event.target.value = '';
            return;
        }

        // Validate file size (2MB)
        if (file.size > 2 * 1024 * 1024) {
            showToast('File size exceeds 2MB limit.', 'warning');
            event.target.value = '';
            return;
        }

        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logoPreviewImg').src = e.target.result;
            document.getElementById('logoPreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }

    function uploadLogo(templateID) {
        const fileInput = document.getElementById('templateLogoEdit') || document.getElementById('templateLogo');
        if (!fileInput || !fileInput.files || !fileInput.files[0]) {
            showToast('Please select a logo file', 'warning');
            return;
        }

        const formData = new FormData();
        formData.append('templateID', templateID);
        formData.append('logo', fileInput.files[0]);

        const submitBtn = document.querySelector('#uploadLogoBtn');
        const originalText = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ri-loader-4-line spin me-1"></i>Uploading...';
        }

        fetch(`${siteUrl}php/scripts/invoices/upload_logo.php`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Logo uploaded successfully', 'success');
                document.getElementById('currentLogoURL').value = data.logoURL;
                document.getElementById('logoPreviewImg').src = data.logoURL;
                document.getElementById('logoPreview').style.display = 'block';
                fileInput.value = '';
            } else {
                showToast('Error: ' + (data.message || 'Failed to upload logo'), 'error');
            }
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while uploading the logo', 'error');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }

    function saveTemplate(event) {
        event.preventDefault();

        const form = document.getElementById('manageTemplateForm');
        const formData = new FormData(form);
        const templateID = document.getElementById('templateID').value;

        formData.append('action', templateID ? 'update' : 'create');
        formData.append('taxEnabled', document.getElementById('taxEnabled').checked ? 'Y' : 'N');
        formData.append('isDefault', document.getElementById('isDefault').checked ? 'Y' : 'N');
        formData.append('isActive', document.getElementById('isActive').checked ? 'Y' : 'N');

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="ri-loader-4-line spin me-1"></i>Saving...';

        fetch(`${siteUrl}php/scripts/invoices/manage_template.php`, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Check if response is actually JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                    console.error('Non-JSON response:', text);
                    throw new Error('Server returned non-JSON response. Check console for details.');
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data); // Debug log
            if (data.success) {
                showToast(data.message || 'Template saved successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Error: ' + (data.message || 'Failed to save template'), 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while saving the template: ' + error.message, 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    }

    // Event delegation setup
    document.addEventListener('DOMContentLoaded', function() {
        // Reset form button handlers
        document.addEventListener('click', function(e) {
            const target = e.target.closest('[data-action="reset-form"]');
            if (target) {
                e.preventDefault();
                resetTemplateForm();
            }
        });

        // Edit template button handlers
        document.addEventListener('click', function(e) {
            const target = e.target.closest('.edit-template');
            if (target) {
                e.preventDefault();
                const templateID = target.getAttribute('data-template-id');
                if (templateID) {
                    editTemplate(templateID);
                }
            }
        });

        // Preview template button handlers
        document.addEventListener('click', function(e) {
            const target = e.target.closest('.preview-template');
            if (target) {
                e.preventDefault();
                const templateID = target.getAttribute('data-template-id');
                if (templateID) {
                    previewTemplate(templateID);
                }
            }
        });

        // Delete template button handlers
        document.addEventListener('click', function(e) {
            const target = e.target.closest('.delete-template');
            if (target) {
                e.preventDefault();
                const templateID = target.getAttribute('data-template-id');
                const templateName = target.getAttribute('data-template-name');
                if (templateID && templateName) {
                    deleteTemplate(templateID, templateName);
                }
            }
        });

        // Logo upload input handlers
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('template-logo-input')) {
                handleLogoUpload(e);
            }
        });

        // Upload logo button handler
        document.addEventListener('click', function(e) {
            const target = e.target.closest('[data-action="upload-logo"]');
            if (target) {
                e.preventDefault();
                const templateID = document.getElementById('templateID').value;
                if (templateID) {
                    uploadLogo(templateID);
                } else {
                    showToast('Please save the template first before uploading a logo', 'warning');
                }
            }
        });

        // Form submission handler
        const form = document.getElementById('manageTemplateForm');
        if (form) {
            form.addEventListener('submit', saveTemplate);
        }

        // Reset form when modal is shown for new template
        const modal = document.getElementById('manageTemplateModal');
        if (modal) {
            modal.addEventListener('show.bs.modal', function(e) {
                // Only reset if it's not an edit action (no edit button was clicked)
                if (!e.relatedTarget || !e.relatedTarget.classList.contains('edit-template')) {
                    resetTemplateForm();
                }
            });
        }
    });
})();
</script>

<style>
.spin {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

