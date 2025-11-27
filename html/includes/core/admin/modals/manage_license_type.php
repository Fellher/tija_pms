<?php
// License Type Management Modal
?>

<style>
/* Ensure modal scrolls properly and footer is always visible */
#manageLicenseTypeModal .modal-dialog {
    max-height: 90vh;
    margin: 1.75rem auto;
}

#manageLicenseTypeModal .modal-content {
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

#manageLicenseTypeModal .modal-body {
    overflow-y: auto;
    max-height: calc(90vh - 140px); /* Account for header and footer */
    flex: 1 1 auto;
}

#manageLicenseTypeModal .modal-header,
#manageLicenseTypeModal .modal-footer {
    flex-shrink: 0;
}
</style>

<div class="modal fade" id="manageLicenseTypeModal" tabindex="-1" aria-labelledby="manageLicenseTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form id="manageLicenseTypeForm" action="<?= $base ?>php/scripts/global/admin/manage_license_types.php" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="manageLicenseTypeModalLabel">
                        <i class="fas fa-certificate me-2"></i>Add New License Type
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="licenseTypeID" id="licenseTypeID">

                    <!-- Basic Information -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Basic Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="licenseTypeName" class="form-label">
                                        License Type Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="licenseTypeName" id="licenseTypeName" required placeholder="e.g., Standard, Premium">
                                    <small class="form-text text-muted">Display name for the license type</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="licenseTypeCode" class="form-label">
                                        System Code <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="licenseTypeCode" id="licenseTypeCode" required placeholder="e.g., standard, premium">
                                    <small class="form-text text-muted">Unique system identifier (lowercase, no spaces)</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="licenseTypeDescription" class="form-label">
                                        Description <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" name="licenseTypeDescription" id="licenseTypeDescription" rows="3" required placeholder="Detailed description of the license type..."></textarea>
                                    <small class="form-text text-muted">This will be shown to users when selecting a license</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Limits & Duration -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-users me-2"></i>Limits & Duration</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="defaultUserLimit" class="form-label">
                                        Default User Limit <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control" name="defaultUserLimit" id="defaultUserLimit" required min="1" placeholder="50">
                                    <small class="form-text text-muted">Maximum users allowed (use 999999 for unlimited)</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="defaultDuration" class="form-label">
                                        Default Duration (Days) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control" name="defaultDuration" id="defaultDuration" required min="1" value="365" placeholder="365">
                                    <small class="form-text text-muted">Default license duration in days</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-dollar-sign me-2"></i>Pricing</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="monthlyPrice" class="form-label">
                                        Monthly Price
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" name="monthlyPrice" id="monthlyPrice" step="0.01" min="0" placeholder="49.99">
                                    </div>
                                    <small class="form-text text-muted">Leave blank or 0 for free plans</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="yearlyPrice" class="form-label">
                                        Yearly Price
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" name="yearlyPrice" id="yearlyPrice" step="0.01" min="0" placeholder="499.99">
                                    </div>
                                    <small class="form-text text-muted">Discounted annual price (optional)</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Features -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-check-square me-2"></i>Included Features</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="payroll" id="feature_payroll">
                                        <label class="form-check-label" for="feature_payroll">Payroll Management</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="leave" id="feature_leave">
                                        <label class="form-check-label" for="feature_leave">Leave Management</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="attendance" id="feature_attendance">
                                        <label class="form-check-label" for="feature_attendance">Attendance Tracking</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="performance" id="feature_performance">
                                        <label class="form-check-label" for="feature_performance">Performance Management</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="employee_management" id="feature_employee">
                                        <label class="form-check-label" for="feature_employee">Employee Management</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="recruitment" id="feature_recruitment">
                                        <label class="form-check-label" for="feature_recruitment">Recruitment</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="training" id="feature_training">
                                        <label class="form-check-label" for="feature_training">Training & Development</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="reports" id="feature_reports">
                                        <label class="form-check-label" for="feature_reports">Basic Reports</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="advanced_reports" id="feature_advanced_reports">
                                        <label class="form-check-label" for="feature_advanced_reports">Advanced Reports</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="analytics" id="feature_analytics">
                                        <label class="form-check-label" for="feature_analytics">Analytics Dashboard</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="custom_reports" id="feature_custom_reports">
                                        <label class="form-check-label" for="feature_custom_reports">Custom Report Builder</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="api" id="feature_api">
                                        <label class="form-check-label" for="feature_api">API Access</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="whitelabel" id="feature_whitelabel">
                                        <label class="form-check-label" for="feature_whitelabel">White-label Branding</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="sso" id="feature_sso">
                                        <label class="form-check-label" for="feature_sso">Single Sign-On (SSO)</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="features[]" value="custom_development" id="feature_custom_dev">
                                        <label class="form-check-label" for="feature_custom_dev">Custom Development</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Benefits & Restrictions -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-list-ul me-2"></i>Benefits & Restrictions</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="benefits" class="form-label">
                                        Key Benefits
                                    </label>
                                    <textarea class="form-control" name="benefits" id="benefits" rows="5" placeholder="Enter one benefit per line&#10;All basic features&#10;Priority support&#10;Custom workflows"></textarea>
                                    <small class="form-text text-muted">One benefit per line (shown to users)</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="restrictions" class="form-label">
                                        Restrictions/Limitations
                                    </label>
                                    <textarea class="form-control" name="restrictions" id="restrictions" rows="5" placeholder="Enter one restriction per line&#10;Up to 50 users&#10;Email support only&#10;Monthly reports"></textarea>
                                    <small class="form-text text-muted">One restriction per line</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Display Settings -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-palette me-2"></i>Display Settings</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="colorCode" class="form-label">
                                        Color Code
                                    </label>
                                    <input type="color" class="form-control form-control-color" name="colorCode" id="colorCode" value="#5b6fe3">
                                    <small class="form-text text-muted">Badge and icon color</small>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="iconClass" class="form-label">
                                        Icon Class
                                    </label>
                                    <input type="text" class="form-control" name="iconClass" id="iconClass" placeholder="fa-star">
                                    <small class="form-text text-muted">Font Awesome icon (e.g., fa-star)</small>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="isPopular" class="form-label">
                                        Popular/Recommended
                                    </label>
                                    <select class="form-select" name="isPopular" id="isPopular">
                                        <option value="N">No</option>
                                        <option value="Y">Yes (Recommended)</option>
                                    </select>
                                    <small class="form-text text-muted">Mark as popular choice</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save License Type
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('manageLicenseTypeForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'License type saved successfully');
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to save license type'));
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
});

// Auto-generate system code from name
document.getElementById('licenseTypeName').addEventListener('input', function() {
    const codeInput = document.getElementById('licenseTypeCode');
    if (!codeInput.value || codeInput.value === codeInput.defaultValue) {
        codeInput.value = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '_');
    }
});
</script>

