<?php
/**
 * Family Tab - Next of Kin & Dependants (Complete Implementation)
 */

// Get next of kin
$nextOfKin = EmployeeProfileExtended::get_next_of_kin(['employeeID' => $employeeID], false, $DBConn);

// Get dependants
$dependants = EmployeeProfileExtended::get_dependants(['employeeID' => $employeeID], false, $DBConn);
?>

<div class="section-header">
    <h5 class="mb-0"><i class="ri-parent-line me-2"></i>Family Information</h5>
</div>

<!-- Next of Kin -->
<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0"><i class="ri-user-heart-line me-2"></i>Next of Kin</h6>
            <?php if ($canEdit): ?>
            <button class="btn btn-sm btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#nextOfKinModal"
                    onclick="prepareAddNextOfKin()">
                <i class="ri-add-line me-1"></i> Add Next of Kin
            </button>
            <?php endif; ?>
        </div>

        <?php if ($nextOfKin && count($nextOfKin) > 0): ?>
        <div class="row">
            <?php foreach ($nextOfKin as $kin): ?>
            <div class="col-md-6 mb-3">
                <div class="info-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-1">
                                <?= htmlspecialchars($kin->fullName) ?>
                                <?php if ($kin->isPrimary == 'Y'): ?>
                                <span class="badge bg-primary ms-2">Primary</span>
                                <?php endif; ?>
                            </h6>
                            <p class="text-muted mb-2"><i class="ri-user-line me-1"></i><?= htmlspecialchars($kin->relationship) ?></p>

                            <div class="mb-1">
                                <i class="ri-phone-line me-2 text-muted"></i>
                                <span class="text-dark"><?= htmlspecialchars($kin->phoneNumber) ?></span>
                            </div>

                            <?php if (!empty($kin->emailAddress)): ?>
                            <div class="mb-1">
                                <i class="ri-mail-line me-2 text-muted"></i>
                                <span class="text-dark"><?= htmlspecialchars($kin->emailAddress) ?></span>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($kin->nationalID)): ?>
                            <div class="mb-1">
                                <i class="ri-id-card-line me-2 text-muted"></i>
                                <span class="text-dark">ID: <?= htmlspecialchars($kin->nationalID) ?></span>
                            </div>
                            <?php endif; ?>

                            <?php if ($kin->allocationPercentage > 0): ?>
                            <div class="mt-2">
                                <span class="badge bg-info-transparent">
                                    <i class="ri-percent-line me-1"></i>Allocation: <?= number_format($kin->allocationPercentage, 2) ?>%
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($canEdit): ?>
                        <div class="btn-group btn-group-sm ms-2">
                            <button class="btn btn-sm btn-icon btn-primary-light"
                                    data-bs-toggle="modal"
                                    data-bs-target="#nextOfKinModal"
                                    onclick="editNextOfKin(<?= $kin->nextOfKinID ?>)"
                                    title="Edit">
                                <i class="ri-edit-line"></i>
                            </button>
                            <button class="btn btn-sm btn-icon btn-danger-light"
                                    onclick="deleteNextOfKin(<?= $kin->nextOfKinID ?>)"
                                    title="Delete">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="ri-information-line me-2"></i>
            No next of kin recorded. Add beneficiaries for insurance and benefits.
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Dependants -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0"><i class="ri-group-line me-2"></i>Dependants</h6>
            <?php if ($canEdit): ?>
            <button class="btn btn-sm btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#dependantModal"
                    onclick="prepareAddDependant()">
                <i class="ri-add-line me-1"></i> Add Dependant
            </button>
            <?php endif; ?>
        </div>

        <?php if ($dependants && count($dependants) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Relationship</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Beneficiary</th>
                        <th>Student</th>
                        <?php if ($canEdit): ?>
                        <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dependants as $dependant): ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($dependant->fullName) ?></td>
                        <td><?= htmlspecialchars($dependant->relationship) ?></td>
                        <td><?= $dependant->age ?? 'N/A' ?> <?= isset($dependant->age) ? 'years' : '' ?></td>
                        <td class="text-capitalize"><?= htmlspecialchars($dependant->gender ?? 'N/A') ?></td>
                        <td>
                            <?= $dependant->isBeneficiary == 'Y'
                                ? '<span class="badge bg-success">Yes</span>'
                                : '<span class="badge bg-secondary">No</span>' ?>
                        </td>
                        <td>
                            <?= $dependant->isStudent == 'Y'
                                ? '<span class="badge bg-info">Yes</span>'
                                : 'No' ?>
                        </td>
                        <?php if ($canEdit): ?>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-sm btn-icon btn-primary-light"
                                        data-bs-toggle="modal"
                                        data-bs-target="#dependantModal"
                                        onclick="editDependant(<?= $dependant->dependantID ?>)"
                                        title="Edit">
                                    <i class="ri-edit-line"></i>
                                </button>
                                <button class="btn btn-sm btn-icon btn-danger-light"
                                        onclick="deleteDependant(<?= $dependant->dependantID ?>)"
                                        title="Delete">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="ri-information-line me-2"></i>
            No dependants recorded. Add family members eligible for insurance and benefits.
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Include Modals -->
<?php include __DIR__ . '/modals/next_of_kin_modal.php'; ?>
<?php include __DIR__ . '/modals/dependant_modal.php'; ?>

<script>
// ========================================
// DATE PICKERS INITIALIZATION
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    initializeFamilyDatePickers();

    // Reinitialize when modals are shown
    const kinModal = document.getElementById('nextOfKinModal');
    const depModal = document.getElementById('dependantModal');

    if (kinModal) {
        kinModal.addEventListener('shown.bs.modal', initializeFamilyDatePickers);
    }

    if (depModal) {
        depModal.addEventListener('shown.bs.modal', initializeFamilyDatePickers);
    }
});

function initializeFamilyDatePickers() {
    if (typeof flatpickr !== 'undefined') {
        const kinDateInputs = document.querySelectorAll('.kin-datepicker');
        kinDateInputs.forEach(input => {
            if (!input._flatpickr) {
                // Remove readonly to allow Flatpickr to manage it
                input.removeAttribute('readonly');

                flatpickr(input, {
                    dateFormat: 'Y-m-d',           // Format for actual input value (YYYY-MM-DD)
                    altInput: true,                 // Create user-friendly display
                    altFormat: 'F j, Y',           // Display format (October 20, 2024)
                    allowInput: false,              // Prevent manual typing
                    maxDate: 'today',              // Can't select future dates
                    onChange: function(selectedDates, dateStr, instance) {
                        // Ensure the hidden input has the correct format
                        if (dateStr) {
                            input.value = dateStr;  // YYYY-MM-DD
                        }
                    }
                });
            }
        });

        const depDateInputs = document.querySelectorAll('.dep-datepicker');
        depDateInputs.forEach(input => {
            if (!input._flatpickr) {
                // Remove readonly to allow Flatpickr to manage it
                input.removeAttribute('readonly');

                flatpickr(input, {
                    dateFormat: 'Y-m-d',           // Format for actual input value (YYYY-MM-DD)
                    altInput: true,                 // Create user-friendly display
                    altFormat: 'F j, Y',           // Display format (October 20, 2024)
                    allowInput: false,              // Prevent manual typing
                    maxDate: 'today',              // Can't select future dates
                    onChange: function(selectedDates, dateStr, instance) {
                        // Ensure the hidden input has the correct format
                        if (dateStr) {
                            input.value = dateStr;  // YYYY-MM-DD
                        }
                    }
                });
            }
        });
    }
}

// ========================================
// NEXT OF KIN FUNCTIONS
// ========================================

function prepareAddNextOfKin() {
    const form = document.getElementById('nextOfKinForm');
    if (form) {
        form.reset();
    }

    document.getElementById('nextOfKinID').value = '';
    document.getElementById('nextOfKinModalLabel').textContent = 'Add Next of Kin';
    document.getElementById('kinCountry').value = 'Kenya';
}

function editNextOfKin(kinID) {
    fetch(`<?= $base ?>php/scripts/global/admin/family_api.php?action=get_next_of_kin&id=${kinID}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateNextOfKinForm(data.data);
                document.getElementById('nextOfKinModalLabel').textContent = 'Edit Next of Kin';
            } else {
                showToast(data.message || 'Failed to load next of kin', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while loading next of kin', 'danger');
        });
}

function populateNextOfKinForm(kin) {
    document.getElementById('nextOfKinID').value = kin.nextOfKinID;
    document.getElementById('kinFullName').value = kin.fullName || '';
    document.getElementById('kinRelationship').value = kin.relationship || '';
    document.getElementById('kinGender').value = kin.gender || '';
    document.getElementById('kinNationalID').value = kin.nationalID || '';
    document.getElementById('kinPhoneNumber').value = kin.phoneNumber || '';
    document.getElementById('kinAlternativePhone').value = kin.alternativePhone || '';
    document.getElementById('kinEmailAddress').value = kin.emailAddress || '';
    document.getElementById('kinAddress').value = kin.address || '';
    document.getElementById('kinCity').value = kin.city || '';
    document.getElementById('kinCounty').value = kin.county || '';
    document.getElementById('kinCountry').value = kin.country || 'Kenya';
    document.getElementById('kinOccupation').value = kin.occupation || '';
    document.getElementById('kinEmployer').value = kin.employer || '';
    document.getElementById('kinAllocationPercentage').value = kin.allocationPercentage || 0;
    document.getElementById('kinIsPrimary').checked = (kin.isPrimary === 'Y');
    document.getElementById('kinNotes').value = kin.notes || '';

    // Set date via flatpickr - with reinit
    const dobInput = document.getElementById('kinDateOfBirth');
    if (dobInput) {
        // Clear existing value first
        if (dobInput._flatpickr) {
            dobInput._flatpickr.clear();
        }

        // Set the new date if available
        if (kin.dateOfBirth) {
            // Wait a bit for modal to be fully shown, then set date
            setTimeout(() => {
                if (dobInput._flatpickr) {
                    dobInput._flatpickr.setDate(kin.dateOfBirth, true);
                } else {
                    dobInput.value = kin.dateOfBirth;
                }
            }, 100);
        }
    }
}

function saveNextOfKin(event) {
    event.preventDefault();

    // Ensure Flatpickr value is set before submission
    const dobInput = document.getElementById('kinDateOfBirth');
    if (dobInput && dobInput._flatpickr) {
        const selectedDate = dobInput._flatpickr.selectedDates[0];
        if (selectedDate) {
            // Format as YYYY-MM-DD
            const year = selectedDate.getFullYear();
            const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
            const day = String(selectedDate.getDate()).padStart(2, '0');
            dobInput.value = `${year}-${month}-${day}`;
            console.log('Setting dateOfBirth to:', dobInput.value);
        }
    }

    const formData = new FormData(event.target);
    formData.append('action', 'save_next_of_kin');

    // Log what we're submitting
    console.log('Form data being submitted:');
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }

    const submitBtn = event.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i> Saving...';

    fetch('<?= $base ?>php/scripts/global/admin/family_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Next of kin saved successfully', 'success');
            setTimeout(() => {
                window.location.href = '?s=<?= $s ?>&p=<?= $p ?>&uid=<?= $employeeID ?>&tab=family';
            }, 1500);
        } else {
            showToast(data.message || 'Failed to save next of kin', 'danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="ri-save-line me-1"></i> Save Next of Kin';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while saving next of kin', 'danger');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="ri-save-line me-1"></i> Save Next of Kin';
    });
}

function deleteNextOfKin(kinID) {
    if (!confirm('Are you sure you want to delete this next of kin?')) {
        return;
    }

    fetch(`<?= $base ?>php/scripts/global/admin/family_api.php?action=delete_next_of_kin&id=${kinID}`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Next of kin deleted successfully', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast(data.message || 'Failed to delete next of kin', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while deleting next of kin', 'danger');
    });
}

// ========================================
// DEPENDANT FUNCTIONS
// ========================================

function prepareAddDependant() {
    const form = document.getElementById('dependantForm');
    if (form) {
        form.reset();
    }

    document.getElementById('dependantID').value = '';
    document.getElementById('dependantModalLabel').textContent = 'Add Dependant';

    // Hide education section initially
    document.getElementById('educationSection').style.display = 'none';
    document.getElementById('schoolNameSection').style.display = 'none';
    document.getElementById('gradeSection').style.display = 'none';
    document.getElementById('studentIDSection').style.display = 'none';
}

function editDependant(dependantID) {
    fetch(`<?= $base ?>php/scripts/global/admin/family_api.php?action=get_dependant&id=${dependantID}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateDependantForm(data.data);
                document.getElementById('dependantModalLabel').textContent = 'Edit Dependant';
            } else {
                showToast(data.message || 'Failed to load dependant', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while loading dependant', 'danger');
        });
}

function populateDependantForm(dependant) {
    document.getElementById('dependantID').value = dependant.dependantID;
    document.getElementById('depFullName').value = dependant.fullName || '';
    document.getElementById('depRelationship').value = dependant.relationship || '';
    document.getElementById('depGender').value = dependant.gender || '';
    document.getElementById('depNationalID').value = dependant.nationalID || '';
    document.getElementById('depIsBeneficiary').checked = (dependant.isBeneficiary === 'Y');
    document.getElementById('depIsStudent').checked = (dependant.isStudent === 'Y');
    document.getElementById('depIsDisabled').checked = (dependant.isDisabled === 'Y');
    document.getElementById('depIsDependentForTax').checked = (dependant.isDependentForTax === 'Y');
    document.getElementById('depSchoolName').value = dependant.schoolName || '';
    document.getElementById('depGrade').value = dependant.grade || '';
    document.getElementById('depStudentID').value = dependant.studentID || '';
    document.getElementById('depBloodType').value = dependant.bloodType || '';
    document.getElementById('depMedicalConditions').value = dependant.medicalConditions || '';
    document.getElementById('depInsuranceMemberNumber').value = dependant.insuranceMemberNumber || '';
    document.getElementById('depPhoneNumber').value = dependant.phoneNumber || '';
    document.getElementById('depEmailAddress').value = dependant.emailAddress || '';
    document.getElementById('depNotes').value = dependant.notes || '';

    // Set date via flatpickr - with reinit
    const dobInput = document.getElementById('depDateOfBirth');
    if (dobInput) {
        // Clear existing value first
        if (dobInput._flatpickr) {
            dobInput._flatpickr.clear();
        }

        // Set the new date if available
        if (dependant.dateOfBirth) {
            // Wait a bit for modal to be fully shown, then set date
            setTimeout(() => {
                if (dobInput._flatpickr) {
                    dobInput._flatpickr.setDate(dependant.dateOfBirth, true);
                } else {
                    dobInput.value = dependant.dateOfBirth;
                }
            }, 100);
        }
    }

    // Show education section if student
    if (dependant.isStudent === 'Y') {
        document.getElementById('educationSection').style.display = 'block';
        document.getElementById('schoolNameSection').style.display = 'block';
        document.getElementById('gradeSection').style.display = 'block';
        document.getElementById('studentIDSection').style.display = 'block';
    }
}

function saveDependant(event) {
    event.preventDefault();

    // Ensure Flatpickr value is set before submission
    const dobInput = document.getElementById('depDateOfBirth');
    console.log('depDateOfBirth input found:', dobInput);
    console.log('depDateOfBirth has _flatpickr:', dobInput ? (dobInput._flatpickr ? 'YES' : 'NO') : 'input not found');
    console.log('depDateOfBirth current value:', dobInput ? dobInput.value : 'N/A');

    if (dobInput) {
        if (dobInput._flatpickr) {
            // Try to get date from Flatpickr
            const selectedDates = dobInput._flatpickr.selectedDates;
            console.log('Flatpickr selectedDates array:', selectedDates);

            if (selectedDates && selectedDates.length > 0) {
                const selectedDate = selectedDates[0];
                // Format as YYYY-MM-DD
                const year = selectedDate.getFullYear();
                const month = String(selectedDate.getMonth() + 1).padStart(2, '0');
                const day = String(selectedDate.getDate()).padStart(2, '0');
                dobInput.value = `${year}-${month}-${day}`;
                console.log('✅ Set dateOfBirth from Flatpickr to:', dobInput.value);
            } else {
                console.warn('⚠️ Flatpickr selectedDates is empty - no date selected!');
            }
        } else {
            console.warn('⚠️ Flatpickr not initialized - checking for manual input');
        }

        // Final check - if input has a value, log it
        if (dobInput.value) {
            console.log('Final dobInput.value before FormData:', dobInput.value);
        } else {
            console.error('❌ dobInput.value is still empty!');
        }
    }

    const formData = new FormData(event.target);
    formData.append('action', 'save_dependant');

    // Log what we're submitting
    console.log('Dependant form data being submitted:');
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }

    const submitBtn = event.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i> Saving...';

    fetch('<?= $base ?>php/scripts/global/admin/family_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Dependant saved successfully', 'success');
            setTimeout(() => {
                window.location.href = '?s=<?= $s ?>&p=<?= $p ?>&uid=<?= $employeeID ?>&tab=family';
            }, 1500);
        } else {
            showToast(data.message || 'Failed to save dependant', 'danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="ri-save-line me-1"></i> Save Dependant';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while saving dependant', 'danger');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="ri-save-line me-1"></i> Save Dependant';
    });
}

function deleteDependant(dependantID) {
    if (!confirm('Are you sure you want to delete this dependant?')) {
        return;
    }

    fetch(`<?= $base ?>php/scripts/global/admin/family_api.php?action=delete_dependant&id=${dependantID}`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Dependant deleted successfully', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast(data.message || 'Failed to delete dependant', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while deleting dependant', 'danger');
    });
}

// ========================================
// TOAST NOTIFICATION
// ========================================
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();

    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' :
                   type === 'danger' ? 'bg-danger' :
                   type === 'warning' ? 'bg-warning' : 'bg-info';

    const toastHTML = `
        <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    toastContainer.insertAdjacentHTML('beforeend', toastHTML);

    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
    toast.show();

    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}
</script>

