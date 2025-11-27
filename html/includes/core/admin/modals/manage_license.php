<?php
// License Management Modal
// For managing individual organization licenses
?>

<div class="col-12">
    <input type="hidden" name="licenseID" id="licenseID">
    <input type="hidden" name="orgDataID" id="orgDataID">
    <input type="hidden" name="action" value="save_license">

    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>License Management:</strong> Configure and manage the software license for this organization.
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="licenseType" class="form-label">
                License Type <span class="text-danger">*</span>
            </label>
            <select class="form-select" name="licenseType" id="licenseType" required>
                <option value="">Select License Type</option>
                <?php
                $licenseTypes = Admin::license_types(array('Suspended' => 'N'), false, $DBConn);
                if ($licenseTypes) {
                    foreach ($licenseTypes as $type) {
                        $userLimitText = $type->defaultUserLimit == 999999 ? 'Unlimited' : 'Up to ' . number_format($type->defaultUserLimit) . ' users';
                        $priceText = '';
                        if ($type->monthlyPrice > 0) {
                            $priceText = ' - $' . number_format($type->monthlyPrice, 2) . '/mo';
                        } else {
                            $priceText = ' - Free';
                        }
                        echo '<option value="' . htmlspecialchars($type->licenseTypeCode) . '"
                                      data-user-limit="' . $type->defaultUserLimit . '"
                                      data-duration="' . $type->defaultDuration . '"
                                      data-price="' . $type->monthlyPrice . '">';
                        echo htmlspecialchars($type->licenseTypeName) . ' (' . $userLimitText . $priceText . ')';
                        if ($type->isPopular == 'Y') {
                            echo ' ⭐';
                        }
                        echo '</option>';
                    }
                } else {
                    echo '<option value="" disabled>No license types available</option>';
                }
                ?>
            </select>
            <small class="form-text text-muted">
                Choose the appropriate license tier for this organization
            </small>
        </div>

        <div class="col-md-6 mb-3">
            <label for="userLimit" class="form-label">
                User Limit <span class="text-danger">*</span>
            </label>
            <input type="number"
                   class="form-control"
                   name="userLimit"
                   id="userLimit"
                   required
                   min="1"
                   placeholder="Maximum number of users">
            <small class="form-text text-muted">
                Maximum users allowed under this license
            </small>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="licenseIssueDate" class="form-label">
                Issue Date <span class="text-danger">*</span>
            </label>
            <input type="date"
                   class="form-control"
                   name="licenseIssueDate"
                   id="licenseIssueDate"
                   required
                   value="<?= date('Y-m-d') ?>">
            <small class="form-text text-muted component-datepicker">
                Date when the license was issued
            </small>
        </div>

        <div class="col-md-6 mb-3">
            <label for="licenseExpiryDate" class="form-label">
                Expiry Date <span class="text-danger">*</span>
            </label>
            <input type="date"
                   class="form-control"
                   name="licenseExpiryDate"
                   id="licenseExpiryDate"
                   required
                   value="<?= date('Y-m-d', strtotime('+1 year')) ?>">
            <small class="form-text text-muted component-datepicker">
                Date when the license expires
            </small>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="licenseKey" class="form-label">
                License Key
            </label>
            <div class="input-group">
                <input type="text"
                       class="form-control"
                       name="licenseKey"
                       id="licenseKey"
                       placeholder="Auto-generated if left blank">
                <button class="btn btn-outline-secondary" type="button" onclick="generateLicenseKey()">
                    <i class="fas fa-key"></i> Generate
                </button>
            </div>
            <small class="form-text text-muted">
                Unique license key (auto-generated if not provided)
            </small>
        </div>

        <div class="col-md-6 mb-3">
            <label for="licenseStatus" class="form-label">
                License Status <span class="text-danger">*</span>
            </label>
            <select class="form-select" name="licenseStatus" id="licenseStatus" required>
                <option value="active">Active</option>
                <option value="suspended">Suspended</option>
                <option value="expired">Expired</option>
                <option value="trial">Trial</option>
            </select>
            <small class="form-text text-muted">
                Current status of the license
            </small>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-3">
            <label class="form-label">License Features</label>
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="features[]" value="payroll" id="feature_payroll" checked>
                                <label class="form-check-label" for="feature_payroll">
                                    Payroll Management
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="features[]" value="leave" id="feature_leave" checked>
                                <label class="form-check-label" for="feature_leave">
                                    Leave Management
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="features[]" value="attendance" id="feature_attendance" checked>
                                <label class="form-check-label" for="feature_attendance">
                                    Attendance Tracking
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="features[]" value="performance" id="feature_performance">
                                <label class="form-check-label" for="feature_performance">
                                    Performance Management
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="features[]" value="recruitment" id="feature_recruitment">
                                <label class="form-check-label" for="feature_recruitment">
                                    Recruitment
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="features[]" value="training" id="feature_training">
                                <label class="form-check-label" for="feature_training">
                                    Training & Development
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="features[]" value="reports" id="feature_reports" checked>
                                <label class="form-check-label" for="feature_reports">
                                    Advanced Reports
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="features[]" value="api" id="feature_api">
                                <label class="form-check-label" for="feature_api">
                                    API Access
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-3">
            <label for="licenseNotes" class="form-label">
                License Notes
            </label>
            <textarea class="form-control"
                      name="licenseNotes"
                      id="licenseNotes"
                      rows="3"
                      placeholder="Any special notes, terms, or conditions for this license..."></textarea>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-body">
                    <h6 class="card-title text-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>Important Notice
                    </h6>
                    <p class="card-text mb-0 small">
                        Changing the license configuration may affect the organization's access to features.
                        Users exceeding the new limit may be automatically suspended.
                        Please ensure proper communication with the organization before making changes.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize Flatpickr for all date inputs
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date pickers
    flatpickr('#licenseIssueDate', {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'F j, Y',
        defaultDate: new Date(),
        onChange: function(selectedDates, dateStr) {
            // Auto-update expiry date based on selected license type
            updateExpiryDate();
        }
    });

    flatpickr('#licenseExpiryDate', {
        dateFormat: 'Y-m-d',
        altInput: true,
        altFormat: 'F j, Y',
        minDate: 'today'
    });
});

// Auto-update user limit and duration based on license type
document.getElementById('licenseType').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const userLimitInput = document.getElementById('userLimit');
    const expiryDateInput = document.getElementById('licenseExpiryDate');
    const issueDateInput = document.getElementById('licenseIssueDate');

    // Get data attributes from selected option
    const userLimit = selectedOption.getAttribute('data-user-limit');
    const duration = selectedOption.getAttribute('data-duration');

    // Update user limit
    if (userLimit) {
        userLimitInput.value = userLimit;
    }

    // Update expiry date based on duration
    if (duration && issueDateInput.value) {
        const issueDate = new Date(issueDateInput.value);
        const expiryDate = new Date(issueDate);
        expiryDate.setDate(expiryDate.getDate() + parseInt(duration));
        expiryDateInput.value = expiryDate.toISOString().split('T')[0];
    }
});

// Generate random license key
function generateLicenseKey() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let key = '';

    for (let i = 0; i < 4; i++) {
        if (i > 0) key += '-';
        for (let j = 0; j < 4; j++) {
            key += chars.charAt(Math.floor(Math.random() * chars.length));
        }
    }

    document.getElementById('licenseKey').value = key;
}

// Helper function to update expiry date
function updateExpiryDate() {
    const licenseTypeSelect = document.getElementById('licenseType');
    const selectedOption = licenseTypeSelect.options[licenseTypeSelect.selectedIndex];
    const duration = selectedOption.getAttribute('data-duration');
    const issueDateInput = document.getElementById('licenseIssueDate');

    if (duration && issueDateInput.value) {
        const issueDate = new Date(issueDateInput.value);
        const expiryDate = new Date(issueDate);
        expiryDate.setDate(expiryDate.getDate() + parseInt(duration));

        // Update flatpickr instance
        const expiryPicker = document.getElementById('licenseExpiryDate')._flatpickr;
        if (expiryPicker) {
            expiryPicker.setDate(expiryDate);
        }
    }
}
</script>

