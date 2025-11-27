<?php
/**
 * Personal Details Tab
 * Displays personal information from people, user_details, and extended_personal tables
 * Includes inline edit functionality
 */

// Get extended personal details - handle if table doesn't exist
try {
    $extendedPersonal = EmployeeProfileExtended::get_extended_personal(['employeeID' => $employeeID], true, $DBConn);
} catch (Exception $e) {
    // Table doesn't exist yet - create empty object
    $extendedPersonal = (object)[
        'middleName' => null,
        'maidenName' => null,
        'maritalStatus' => null,
        'nationality' => 'Kenyan',
        'passportNumber' => null,
        'passportIssueDate' => null,
        'passportExpiryDate' => null,
        'bloodGroup' => null,
        'religion' => null,
        'ethnicity' => null,
        'languagesSpoken' => null,
        'disabilities' => null
    ];
}
?>

<!-- VIEW MODE -->
<div id="personalDetailsViewMode">
    <!-- Tab Header -->
    <div class="tab-header">
        <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1">
                <h5 class="mb-1"><i class="ri-user-line me-2"></i>Personal Information</h5>
                <p class="text-muted small mb-0">Basic details, identification documents, and additional personal information</p>
            </div>
            <?php if ($canEdit): ?>
            <button class="btn btn-sm btn-primary" id="editPersonalDetailsBtn">
                <i class="ri-edit-line me-1"></i> Edit Personal Details
            </button>
            <?php endif; ?>
        </div>
    </div>

<div class="row">
    <!-- Basic Information from people table -->
    <div class="col-md-6">
        <div class="info-card">
            <h6 class="fw-bold mb-3">Basic Information</h6>

            <div class="data-row">
                <span class="data-label">Full Name:</span>
                <span class="data-value">
                    <?= htmlspecialchars($employeeDetails->prefixName ?? '') ?>
                    <?= htmlspecialchars($employeeDetails->FirstName ?? '') ?>
                    <?= htmlspecialchars($extendedPersonal->middleName ?? '') ?>
                    <?= htmlspecialchars($employeeDetails->Surname ?? '') ?>
                </span>
            </div>

            <div class="data-row">
                <span class="data-label">Email:</span>
                <span class="data-value"><?= htmlspecialchars($employeeDetails->Email ?? 'N/A') ?></span>
            </div>

            <div class="data-row">
                <span class="data-label">Date of Birth:</span>
                <span class="data-value">
                    <?= isset($employeeDetails->dateOfBirth) && $employeeDetails->dateOfBirth != '0000-00-00'
                        ? date('F j, Y', strtotime($employeeDetails->dateOfBirth))
                        : 'Not provided' ?>
                </span>
            </div>

            <div class="data-row">
                <span class="data-label">Gender:</span>
                <span class="data-value"><?= ucfirst($employeeDetails->gender ?? 'Not specified') ?></span>
            </div>

            <div class="data-row">
                <span class="data-label">Marital Status:</span>
                <span class="data-value"><?= ucfirst($extendedPersonal->maritalStatus ?? 'Not specified') ?></span>
            </div>

            <div class="data-row">
                <span class="data-label">Nationality:</span>
                <span class="data-value"><?= htmlspecialchars($extendedPersonal->nationality ?? 'Not provided') ?></span>
            </div>
        </div>
    </div>

    <!-- Identification Documents -->
    <div class="col-md-6">
        <div class="info-card">
            <h6 class="fw-bold mb-3">Identification & Documents</h6>

            <div class="data-row">
                <span class="data-label">National ID:</span>
                <span class="data-value"><?= htmlspecialchars($employeeDetails->nationalID ?? 'Not provided') ?></span>
            </div>

            <div class="data-row">
                <span class="data-label">KRA PIN:</span>
                <span class="data-value"><?= htmlspecialchars($employeeDetails->pin ?? 'Not provided') ?></span>
            </div>

            <div class="data-row">
                <span class="data-label">NHIF Number:</span>
                <span class="data-value"><?= htmlspecialchars($employeeDetails->nhifNumber ?? 'Not provided') ?></span>
            </div>

            <div class="data-row">
                <span class="data-label">NSSF Number:</span>
                <span class="data-value"><?= htmlspecialchars($employeeDetails->nssfNumber ?? 'Not provided') ?></span>
            </div>

            <div class="data-row">
                <span class="data-label">Passport Number:</span>
                <span class="data-value"><?= htmlspecialchars($extendedPersonal->passportNumber ?? 'Not provided') ?></span>
            </div>

            <?php if (!empty($extendedPersonal->passportExpiryDate)): ?>
            <div class="data-row">
                <span class="data-label">Passport Expiry:</span>
                <span class="data-value">
                    <?= date('F j, Y', strtotime($extendedPersonal->passportExpiryDate)) ?>
                </span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Additional Personal Information -->
    <div class="col-md-6 mt-3">
        <div class="info-card">
            <h6 class="fw-bold mb-3">Additional Information</h6>

            <div class="data-row">
                <span class="data-label">Blood Group:</span>
                <span class="data-value"><?= htmlspecialchars($extendedPersonal->bloodGroup ?? 'Not provided') ?></span>
            </div>

            <div class="data-row">
                <span class="data-label">Religion:</span>
                <span class="data-value"><?= htmlspecialchars($extendedPersonal->religion ?? 'Not specified') ?></span>
            </div>

            <div class="data-row">
                <span class="data-label">Languages Spoken:</span>
                <span class="data-value">
                    <?php
                    if (!empty($extendedPersonal->languagesSpoken)) {
                        $languages = json_decode($extendedPersonal->languagesSpoken, true);
                        echo is_array($languages) ? implode(', ', $languages) : htmlspecialchars($extendedPersonal->languagesSpoken);
                    } else {
                        echo 'Not provided';
                    }
                    ?>
                </span>
            </div>

            <?php if (!empty($extendedPersonal->disabilities)): ?>
            <div class="data-row">
                <span class="data-label">Disabilities:</span>
                <span class="data-value"><?= htmlspecialchars($extendedPersonal->disabilities) ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Maiden Name if applicable -->
    <?php if (!empty($extendedPersonal->maidenName)): ?>
    <div class="col-md-6 mt-3">
        <div class="info-card">
            <h6 class="fw-bold mb-3">Former Name</h6>

            <div class="data-row">
                <span class="data-label">Maiden Name:</span>
                <span class="data-value"><?= htmlspecialchars($extendedPersonal->maidenName) ?></span>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
</div>
<!-- END VIEW MODE -->

<!-- EDIT MODE (Hidden by default) -->
<?php if ($canEdit):
    $prefixes = Data::prefixes(['Suspended'=>'N'], false, $DBConn);
?>
<div id="personalDetailsEditMode" class="d-none">
    <!-- Edit Header -->
    <div class="tab-header">
        <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1">
                <h5 class="mb-1"><i class="ri-edit-line me-2"></i>Edit Personal Information</h5>
                <p class="text-muted small mb-0">Update your personal details, identification, and additional information</p>
            </div>
            <button class="btn btn-sm btn-secondary" id="cancelPersonalEditBtn">
                <i class="ri-close-line me-1"></i> Cancel
            </button>
        </div>
    </div>

    <form id="personalDetailsEditForm" action="<?= $base ?>php/scripts/global/admin/manage_users.php" method="post">
        <input type="hidden" name="ID" value="<?= $employeeID ?>">
        <input type="hidden" name="redirectUrl" value="<?= "?s={$s}&p={$p}&uid={$employeeID}&tab={$currentTab}" ?>">
        <input type="hidden" name="organisationID" value="<?= $employeeDetails->orgDataID ?? '' ?>">
        <input type="hidden" name="entityID" value="<?= $employeeDetails->entityID ?? '' ?>">
        <input type="hidden" name="Email" value="<?= $employeeDetails->Email ?? '' ?>">

        <div class="row">
            <!-- Basic Information -->
            <div class="col-md-12">
                <div class="info-card">
                    <h6 class="fw-bold mb-3 text-primary">Basic Information</h6>

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">Prefix/Title</label>
                            <select class="form-control form-control-sm" name="prefixID">
                                <option value="">Select Prefix</option>
                                <?php foreach($prefixes as $prefix): ?>
                                <option value="<?= $prefix->prefixID ?>" <?= ($employeeDetails->prefixID == $prefix->prefixID) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($prefix->prefixName) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="FirstName" value="<?= htmlspecialchars($employeeDetails->FirstName ?? '') ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">Middle Name</label>
                            <input type="text" class="form-control form-control-sm" name="middleName" value="<?= htmlspecialchars($extendedPersonal->middleName ?? '') ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">Surname <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="Surname" value="<?= htmlspecialchars($employeeDetails->Surname ?? '') ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">Maiden Name (if applicable)</label>
                            <input type="text" class="form-control form-control-sm" name="maidenName" value="<?= htmlspecialchars($extendedPersonal->maidenName ?? '') ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">Date of Birth</label>
                            <input type="text" class="form-control form-control-sm component-datepicker" name="dateOfBirth" value="<?= $employeeDetails->dateOfBirth ?? '' ?>" placeholder="Select date of birth" readonly>
                            <small class="text-muted">Must be at least 18 years old</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">Gender <span class="text-danger">*</span></label>
                            <select class="form-control form-control-sm" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male" <?= ($employeeDetails->gender == 'male') ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= ($employeeDetails->gender == 'female') ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= ($employeeDetails->gender == 'other') ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">Marital Status</label>
                            <select class="form-control form-control-sm" name="maritalStatus">
                                <option value="">Select Marital Status</option>
                                <option value="single" <?= ($extendedPersonal->maritalStatus ?? '') == 'single' ? 'selected' : '' ?>>Single</option>
                                <option value="married" <?= ($extendedPersonal->maritalStatus ?? '') == 'married' ? 'selected' : '' ?>>Married</option>
                                <option value="divorced" <?= ($extendedPersonal->maritalStatus ?? '') == 'divorced' ? 'selected' : '' ?>>Divorced</option>
                                <option value="widowed" <?= ($extendedPersonal->maritalStatus ?? '') == 'widowed' ? 'selected' : '' ?>>Widowed</option>
                                <option value="separated" <?= ($extendedPersonal->maritalStatus ?? '') == 'separated' ? 'selected' : '' ?>>Separated</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">Nationality</label>
                            <input type="text" class="form-control form-control-sm" name="nationality" value="<?= htmlspecialchars($extendedPersonal->nationality ?? 'Kenyan') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Identification Documents -->
            <div class="col-md-12">
                <div class="info-card">
                    <h6 class="fw-bold mb-3 text-primary">Identification & Documents</h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">National ID Number</label>
                            <input type="text" class="form-control form-control-sm" name="nationalID" value="<?= htmlspecialchars($employeeDetails->nationalID ?? '') ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">KRA PIN</label>
                            <input type="text" class="form-control form-control-sm" name="pin" value="<?= htmlspecialchars($employeeDetails->pin ?? '') ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">NHIF Number</label>
                            <input type="text" class="form-control form-control-sm" name="nhifNumber" value="<?= htmlspecialchars($employeeDetails->nhifNumber ?? '') ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">NSSF Number</label>
                            <input type="text" class="form-control form-control-sm" name="nssfNumber" value="<?= htmlspecialchars($employeeDetails->nssfNumber ?? '') ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">Passport Number</label>
                            <input type="text" class="form-control form-control-sm" name="passportNumber" value="<?= htmlspecialchars($extendedPersonal->passportNumber ?? '') ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">Passport Issue Date</label>
                            <input type="text" class="form-control form-control-sm component-datepicker" name="passportIssueDate" value="<?= $extendedPersonal->passportIssueDate ?? '' ?>" placeholder="Select issue date" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">Passport Expiry Date</label>
                            <input type="text" class="form-control form-control-sm component-datepicker" name="passportExpiryDate" value="<?= $extendedPersonal->passportExpiryDate ?? '' ?>" placeholder="Select expiry date" readonly>
                            <small class="text-muted">Must be after issue date</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="col-md-12">
                <div class="info-card">
                    <h6 class="fw-bold mb-3 text-primary">Additional Information</h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">Blood Group</label>
                            <select class="form-control form-control-sm" name="bloodGroup">
                                <option value="">Select Blood Group</option>
                                <option value="A+" <?= ($extendedPersonal->bloodGroup ?? '') == 'A+' ? 'selected' : '' ?>>A+</option>
                                <option value="A-" <?= ($extendedPersonal->bloodGroup ?? '') == 'A-' ? 'selected' : '' ?>>A-</option>
                                <option value="B+" <?= ($extendedPersonal->bloodGroup ?? '') == 'B+' ? 'selected' : '' ?>>B+</option>
                                <option value="B-" <?= ($extendedPersonal->bloodGroup ?? '') == 'B-' ? 'selected' : '' ?>>B-</option>
                                <option value="AB+" <?= ($extendedPersonal->bloodGroup ?? '') == 'AB+' ? 'selected' : '' ?>>AB+</option>
                                <option value="AB-" <?= ($extendedPersonal->bloodGroup ?? '') == 'AB-' ? 'selected' : '' ?>>AB-</option>
                                <option value="O+" <?= ($extendedPersonal->bloodGroup ?? '') == 'O+' ? 'selected' : '' ?>>O+</option>
                                <option value="O-" <?= ($extendedPersonal->bloodGroup ?? '') == 'O-' ? 'selected' : '' ?>>O-</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">Religion</label>
                            <input type="text" class="form-control form-control-sm" name="religion" value="<?= htmlspecialchars($extendedPersonal->religion ?? '') ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">Ethnicity</label>
                            <input type="text" class="form-control form-control-sm" name="ethnicity" value="<?= htmlspecialchars($extendedPersonal->ethnicity ?? '') ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label small text-primary">Languages Spoken</label>
                            <input type="text" class="form-control form-control-sm" name="languagesSpoken"
                                   value="<?= htmlspecialchars($extendedPersonal->languagesSpoken ?? '') ?>"
                                   placeholder="e.g., English, Swahili, French">
                            <small class="text-muted">Comma-separated list</small>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label small text-primary">Disabilities or Special Needs</label>
                            <textarea class="form-control form-control-sm" name="disabilities" rows="2"><?= htmlspecialchars($extendedPersonal->disabilities ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-4">
            <button type="submit" class="btn btn-success">
                <i class="ri-save-line me-1"></i> Save Personal Details
            </button>
            <button type="button" class="btn btn-secondary ms-2" id="cancelPersonalEditBtn2">
                <i class="ri-close-line me-1"></i> Cancel
            </button>
        </div>
    </form>
</div>
<?php endif; ?>
<!-- END EDIT MODE -->

<!-- JavaScript for Personal Details Inline Edit -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date pickers
    initializeDatePickers();

    // Edit button click
    const editPersonalBtn = document.getElementById('editPersonalDetailsBtn');
    if (editPersonalBtn) {
        editPersonalBtn.addEventListener('click', function(e) {
            e.preventDefault();
            togglePersonalDetailsEdit(true);
            // Reinitialize date pickers when edit mode opens
            setTimeout(initializeDatePickers, 100);
        });
    }

    // Cancel buttons (there are two)
    const cancelBtn1 = document.getElementById('cancelPersonalEditBtn');
    const cancelBtn2 = document.getElementById('cancelPersonalEditBtn2');

    if (cancelBtn1) {
        cancelBtn1.addEventListener('click', function(e) {
            e.preventDefault();
            togglePersonalDetailsEdit(false);
        });
    }

    if (cancelBtn2) {
        cancelBtn2.addEventListener('click', function(e) {
            e.preventDefault();
            togglePersonalDetailsEdit(false);
        });
    }

    // Form submission
    const personalForm = document.getElementById('personalDetailsEditForm');
    if (personalForm) {
        personalForm.addEventListener('submit', function(e) {
            // Validate passport dates
            if (!validatePassportDates()) {
                e.preventDefault();
                return false;
            }

            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i> Saving...';
            }

            // Show saving toast
            showToast('Saving personal details...', 'info');
        });
    }

    // Check for success message in URL (after redirect)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('saved') === 'personal') {
        setTimeout(function() {
            showToast('Personal details updated successfully!', 'success');
        }, 500);
    }
});

// Initialize all date pickers with flatpickr
function initializeDatePickers() {
    // Date of Birth - must be at least 18 years ago
    const dobInput = document.querySelector('input[name="dateOfBirth"]');
    if (dobInput && !dobInput._flatpickr) {
        const maxDate = new Date();
        maxDate.setFullYear(maxDate.getFullYear() - 18);

        flatpickr(dobInput, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'F j, Y',
            maxDate: maxDate,
            defaultDate: dobInput.value || null,
            disableMobile: true,
            allowInput: false,
            clickOpens: true,
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    instance.element.classList.remove('is-invalid');
                    instance.element.classList.add('is-valid');
                }
            }
        });
    }

    // Passport Issue Date
    const passportIssueDateInput = document.querySelector('input[name="passportIssueDate"]');
    if (passportIssueDateInput && !passportIssueDateInput._flatpickr) {
        flatpickr(passportIssueDateInput, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'F j, Y',
            maxDate: 'today',
            defaultDate: passportIssueDateInput.value || null,
            disableMobile: true,
            allowInput: false,
            clickOpens: true,
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    instance.element.classList.remove('is-invalid');
                    instance.element.classList.add('is-valid');

                    // Update passport expiry date minDate
                    const expiryInput = document.querySelector('input[name="passportExpiryDate"]');
                    if (expiryInput && expiryInput._flatpickr) {
                        expiryInput._flatpickr.set('minDate', dateStr);
                    }
                }
            }
        });
    }

    // Passport Expiry Date - must be after issue date
    const passportExpiryDateInput = document.querySelector('input[name="passportExpiryDate"]');
    if (passportExpiryDateInput && !passportExpiryDateInput._flatpickr) {
        // Get the issue date to set as minimum
        const issueDate = passportIssueDateInput && passportIssueDateInput.value ? passportIssueDateInput.value : 'today';

        flatpickr(passportExpiryDateInput, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'F j, Y',
            minDate: issueDate,
            defaultDate: passportExpiryDateInput.value || null,
            disableMobile: true,
            allowInput: false,
            clickOpens: true,
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    instance.element.classList.remove('is-invalid');
                    instance.element.classList.add('is-valid');
                }
            }
        });
    }
}

// Show toast notification
function showToast(message, type = 'danger') {
    // Create toast container if it doesn't exist
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }

    // Create toast element
    const toastId = 'toast-' + Date.now();
    const iconMap = {
        'danger': 'ri-error-warning-line',
        'warning': 'ri-alert-line',
        'success': 'ri-checkbox-circle-line',
        'info': 'ri-information-line'
    };

    const toast = document.createElement('div');
    toast.id = toastId;
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');

    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="${iconMap[type]} me-2"></i>${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    toastContainer.appendChild(toast);

    // Show toast
    const bsToast = new bootstrap.Toast(toast, {
        autohide: true,
        delay: 5000
    });
    bsToast.show();

    // Remove toast element after it's hidden
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}

// Validate passport dates before form submission
function validatePassportDates() {
    const issueInput = document.querySelector('input[name="passportIssueDate"]');
    const expiryInput = document.querySelector('input[name="passportExpiryDate"]');

    // Only validate if both dates are provided
    if (issueInput && expiryInput && issueInput.value && expiryInput.value) {
        const issueDate = new Date(issueInput.value);
        const expiryDate = new Date(expiryInput.value);

        if (expiryDate <= issueDate) {
            // Show error
            expiryInput.classList.add('is-invalid');
            expiryInput.classList.remove('is-valid');

            // Create or update error message
            let errorMsg = expiryInput.nextElementSibling;
            if (!errorMsg || !errorMsg.classList.contains('invalid-feedback')) {
                errorMsg = document.createElement('div');
                errorMsg.className = 'invalid-feedback';
                expiryInput.parentNode.appendChild(errorMsg);
            }
            errorMsg.textContent = 'Passport expiry date must be after issue date';

            // Show toast notification
            showToast('Passport expiry date must be after issue date', 'danger');
            return false;
        } else {
            // Clear error
            expiryInput.classList.remove('is-invalid');
            expiryInput.classList.add('is-valid');
        }
    }

    return true;
}

function togglePersonalDetailsEdit(enableEdit) {
    const viewMode = document.getElementById('personalDetailsViewMode');
    const editMode = document.getElementById('personalDetailsEditMode');

    if (!viewMode || !editMode) {
        console.error('Personal details view/edit modes not found');
        return;
    }

    if (enableEdit) {
        // Switch to edit mode
        viewMode.classList.add('d-none');
        editMode.classList.remove('d-none');

        // Smooth transition
        editMode.style.opacity = '0';
        setTimeout(function() {
            editMode.style.transition = 'opacity 0.3s ease-in';
            editMode.style.opacity = '1';
        }, 10);

        // Scroll to top of content
        editMode.scrollIntoView({ behavior: 'smooth', block: 'start' });

        console.log('✓ Personal details edit mode activated');
    } else {
        // Switch to view mode
        editMode.classList.add('d-none');
        viewMode.classList.remove('d-none');

        // Smooth transition
        viewMode.style.opacity = '0';
        setTimeout(function() {
            viewMode.style.transition = 'opacity 0.3s ease-in';
            viewMode.style.opacity = '1';
        }, 10);

        console.log('✓ Personal details view mode activated');
    }
}
</script>

<style>
/* Tab Header Styling */
.tab-header {
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 15px;
    margin-bottom: 25px;
}

.tab-header h5 {
    color: #007bff;
    font-weight: 600;
}

.tab-header .text-muted {
    color: #6c757d !important;
    font-size: 0.875rem;
}

/* Info Cards */
.info-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
}

.data-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.data-row:last-child {
    border-bottom: none;
}

.data-label {
    font-weight: 600;
    color: #495057;
    min-width: 180px;
}

.data-value {
    color: #6c757d;
    text-align: right;
    flex: 1;
}

/* Edit Mode Styling */
#personalDetailsEditMode .form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.15);
}

#personalDetailsEditMode .info-card {
    background: #f8f9fa;
    border-left: 4px solid #007bff;
}

#personalDetailsEditMode .tab-header {
    border-bottom-color: #007bff;
}

/* Date Input Styling */
input[name="dateOfBirth"],
input[name="passportIssueDate"],
input[name="passportExpiryDate"] {
    cursor: pointer;
    background-color: #fff !important;
}

input[name="dateOfBirth"]:focus,
input[name="passportIssueDate"]:focus,
input[name="passportExpiryDate"]:focus {
    background-color: #fff !important;
}

/* Flatpickr validation states */
.is-valid {
    border-color: #28a745 !important;
}

.is-invalid {
    border-color: #dc3545 !important;
}

.invalid-feedback {
    display: block;
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}
</style>

