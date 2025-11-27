<?php
/**
 * Contacts Tab - Complete Implementation
 * Displays and manages contact information, addresses, and emergency contacts
 */

// Get addresses
$addresses = EmployeeProfileExtended::get_addresses(['employeeID' => $employeeID], false, $DBConn);

// Get emergency contacts
$emergencyContacts = EmployeeProfileExtended::get_emergency_contacts(['employeeID' => $employeeID], false, $DBConn);
?>

<!-- Tab Header -->
<div class="tab-header">
    <div class="d-flex justify-content-between align-items-start">
        <div class="flex-grow-1">
            <h5 class="mb-1"><i class="ri-contacts-line me-2"></i>Contact Information</h5>
            <p class="text-muted small mb-0">Addresses, phone numbers, and emergency contacts</p>
        </div>
    </div>
</div>

<!-- Primary Contact Information -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="info-card">
            <h6 class="fw-bold mb-3"><i class="ri-phone-line me-2"></i>Primary Contact Details</h6>
            <div class="row">
                <div class="col-md-6">
                    <div class="data-row">
                        <span class="data-label">Phone Number:</span>
                        <span class="data-value"><?= htmlspecialchars($employeeDetails->phoneNo ?? 'Not provided') ?></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="data-row">
                        <span class="data-label">Email Address:</span>
                        <span class="data-value"><?= htmlspecialchars($employeeDetails->Email ?? 'Not provided') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Addresses Section -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0"><i class="ri-map-pin-line me-2"></i>Addresses</h6>
            <?php if ($canEdit): ?>
            <button class="btn btn-sm btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#addressModal"
                    onclick="prepareAddAddress()">
                <i class="ri-add-line me-1"></i> Add Address
            </button>
            <?php endif; ?>
        </div>

        <?php if ($addresses && count($addresses) > 0): ?>
        <div class="row">
            <?php foreach ($addresses as $address): ?>
            <div class="col-md-6 mb-3">
                <div class="info-card address-card">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="fw-bold text-capitalize mb-0">
                            <i class="ri-home-4-line me-2"></i><?= htmlspecialchars($address->addressType) ?> Address
                            <?php if ($address->isPrimary == 'Y'): ?>
                            <span class="badge bg-primary ms-2">Primary</span>
                            <?php endif; ?>
                        </h6>
                        <?php if ($canEdit): ?>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-sm btn-icon btn-primary-light"
                                    data-bs-toggle="modal"
                                    data-bs-target="#addressModal"
                                    onclick="editAddress(<?= $address->addressID ?>)"
                                    title="Edit">
                                <i class="ri-edit-line"></i>
                            </button>
                            <button class="btn btn-sm btn-icon btn-danger-light"
                                    onclick="deleteAddress(<?= $address->addressID ?>)"
                                    title="Delete">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="address-content">
                        <p class="mb-1"><i class="ri-road-map-line me-2 text-muted"></i><?= htmlspecialchars($address->addressLine1 ?? '') ?></p>
                        <?php if (!empty($address->addressLine2)): ?>
                        <p class="mb-1"><i class="ri-building-line me-2 text-muted"></i><?= htmlspecialchars($address->addressLine2) ?></p>
                        <?php endif; ?>
                        <p class="mb-1">
                            <i class="ri-map-pin-2-line me-2 text-muted"></i>
                            <?= htmlspecialchars($address->city ?? '') ?>
                            <?= !empty($address->county) ? ', ' . htmlspecialchars($address->county) : '' ?>
                            <?= !empty($address->postalCode) ? ' ' . htmlspecialchars($address->postalCode) : '' ?>
                        </p>
                        <p class="mb-0"><i class="ri-global-line me-2 text-muted"></i><?= htmlspecialchars($address->country ?? 'Kenya') ?></p>

                        <?php if (!empty($address->landmark)): ?>
                        <p class="mb-0 mt-2"><small class="text-muted"><i class="ri-map-line me-2"></i>Landmark: <?= htmlspecialchars($address->landmark) ?></small></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="ri-information-line me-2"></i>
            No addresses recorded. Click "Add Address" to add contact addresses.
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Emergency Contacts Section -->
<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0"><i class="ri-hospital-line me-2"></i>Emergency Contacts</h6>
            <?php if ($canEdit): ?>
            <button class="btn btn-sm btn-danger"
                    data-bs-toggle="modal"
                    data-bs-target="#emergencyContactModal"
                    onclick="prepareAddEmergencyContact()">
                <i class="ri-add-line me-1"></i> Add Emergency Contact
            </button>
            <?php endif; ?>
        </div>

        <?php if ($emergencyContacts && count($emergencyContacts) > 0): ?>
        <div class="row">
            <?php foreach ($emergencyContacts as $contact): ?>
            <div class="col-md-6 mb-3">
                <div class="info-card emergency-contact-card">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-1">
                                <i class="ri-user-heart-line me-2"></i><?= htmlspecialchars($contact->contactName) ?>
                                <?php if ($contact->isPrimary == 'Y'): ?>
                                <span class="badge bg-danger ms-2">Primary</span>
                                <?php endif; ?>
                            </h6>
                            <p class="text-muted mb-0"><small><?= htmlspecialchars($contact->relationship) ?></small></p>
                        </div>

                        <?php if ($canEdit): ?>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-sm btn-icon btn-primary-light"
                                    data-bs-toggle="modal"
                                    data-bs-target="#emergencyContactModal"
                                    onclick="editEmergencyContact(<?= $contact->emergencyContactID ?>)"
                                    title="Edit">
                                <i class="ri-edit-line"></i>
                            </button>
                            <button class="btn btn-sm btn-icon btn-danger-light"
                                    onclick="deleteEmergencyContact(<?= $contact->emergencyContactID ?>)"
                                    title="Delete">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="contact-details mt-2">
                        <div class="mb-2">
                            <i class="ri-phone-line me-2 text-primary"></i>
                            <strong>Primary:</strong> <?= htmlspecialchars($contact->primaryPhoneNumber) ?>
                        </div>

                        <?php if (!empty($contact->secondaryPhoneNumber)): ?>
                        <div class="mb-2">
                            <i class="ri-phone-line me-2 text-muted"></i>
                            <strong>Secondary:</strong> <?= htmlspecialchars($contact->secondaryPhoneNumber) ?>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($contact->emailAddress)): ?>
                        <div class="mb-2">
                            <i class="ri-mail-line me-2 text-muted"></i>
                            <?= htmlspecialchars($contact->emailAddress) ?>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($contact->address)): ?>
                        <div class="mt-2 pt-2 border-top">
                            <small class="text-muted">
                                <i class="ri-map-pin-line me-2"></i><?= htmlspecialchars($contact->address) ?>
                                <?= !empty($contact->city) ? ', ' . htmlspecialchars($contact->city) : '' ?>
                            </small>
                        </div>
                        <?php endif; ?>

                        <?php if ($contact->authorizedToCollectSalary == 'Y' || $contact->authorizedForMedicalDecisions == 'Y'): ?>
                        <div class="mt-2 pt-2 border-top">
                            <?php if ($contact->authorizedToCollectSalary == 'Y'): ?>
                            <span class="badge bg-info-transparent me-2"><i class="ri-money-dollar-line me-1"></i>Salary Collection</span>
                            <?php endif; ?>
                            <?php if ($contact->authorizedForMedicalDecisions == 'Y'): ?>
                            <span class="badge bg-warning-transparent"><i class="ri-health-book-line me-1"></i>Medical Decisions</span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">
            <i class="ri-alert-line me-2"></i>
            <strong>Important:</strong> No emergency contacts on file. Please add at least one emergency contact for safety purposes.
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Include Modals -->
<?php include __DIR__ . '/modals/address_modal.php'; ?>
<?php include __DIR__ . '/modals/emergency_contact_modal.php'; ?>

<script>
// Initialize datepickers when modals open
document.addEventListener('DOMContentLoaded', function() {
    initializeContactDatePickers();

    // Reinitialize when modals are shown
    const addressModal = document.getElementById('addressModal');
    const emergencyModal = document.getElementById('emergencyContactModal');

    if (addressModal) {
        addressModal.addEventListener('shown.bs.modal', initializeContactDatePickers);
    }

    if (emergencyModal) {
        emergencyModal.addEventListener('shown.bs.modal', initializeContactDatePickers);
    }
});

function initializeContactDatePickers() {
    if (typeof flatpickr !== 'undefined') {
        const dateInputs = document.querySelectorAll('.component-datepicker');
        dateInputs.forEach(input => {
            if (!input._flatpickr) {
                flatpickr(input, {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'F j, Y',
                    allowInput: true
                });
            }
        });
    }
}

// ========================================
// ADDRESS FUNCTIONS
// ========================================

function prepareAddAddress() {
    const form = document.getElementById('addressForm');
    if (form) {
        form.reset();
    }

    document.getElementById('addressID').value = '';
    document.getElementById('addressModalLabel').textContent = 'Add Address';
    document.getElementById('country').value = 'Kenya';
}

function editAddress(addressID) {
    fetch(`<?= $base ?>php/scripts/global/admin/employee_contacts_api.php?action=get_address&id=${addressID}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateAddressForm(data.data);
                document.getElementById('addressModalLabel').textContent = 'Edit Address';
            } else {
                showToast(data.message || 'Failed to load address', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while loading the address', 'danger');
        });
}

function populateAddressForm(address) {
    document.getElementById('addressID').value = address.addressID;
    document.getElementById('addressType').value = address.addressType;
    document.getElementById('isPrimary').checked = (address.isPrimary === 'Y');
    document.getElementById('addressLine1').value = address.addressLine1 || '';
    document.getElementById('addressLine2').value = address.addressLine2 || '';
    document.getElementById('city').value = address.city || '';
    document.getElementById('county').value = address.county || '';
    document.getElementById('postalCode').value = address.postalCode || '';
    document.getElementById('country').value = address.country || 'Kenya';
    document.getElementById('landmark').value = address.landmark || '';

    // Set dates via flatpickr if available
    if (address.validFrom) {
        const validFromInput = document.getElementById('validFrom');
        if (validFromInput._flatpickr) {
            validFromInput._flatpickr.setDate(address.validFrom, false);
        } else {
            validFromInput.value = address.validFrom;
        }
    }

    if (address.validTo) {
        const validToInput = document.getElementById('validTo');
        if (validToInput._flatpickr) {
            validToInput._flatpickr.setDate(address.validTo, false);
        } else {
            validToInput.value = address.validTo;
        }
    }

    document.getElementById('addressNotes').value = address.notes || '';
}

function saveAddress(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    formData.append('action', 'save_address');

    const submitBtn = event.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i> Saving...';

    fetch('<?= $base ?>php/scripts/global/admin/employee_contacts_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Address saved successfully', 'success');
            setTimeout(() => {
                window.location.href = '?s=<?= $s ?>&p=<?= $p ?>&uid=<?= $employeeID ?>&tab=contacts';
            }, 1500);
        } else {
            showToast(data.message || 'Failed to save address', 'danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="ri-save-line me-1"></i> Save Address';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while saving the address', 'danger');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="ri-save-line me-1"></i> Save Address';
    });
}

function deleteAddress(addressID) {
    if (!confirm('Are you sure you want to delete this address?')) {
        return;
    }

    fetch(`<?= $base ?>php/scripts/global/admin/employee_contacts_api.php?action=delete_address&id=${addressID}`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Address deleted successfully', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast(data.message || 'Failed to delete address', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while deleting the address', 'danger');
    });
}

// ========================================
// EMERGENCY CONTACT FUNCTIONS
// ========================================

function prepareAddEmergencyContact() {
    const form = document.getElementById('emergencyContactForm');
    if (form) {
        form.reset();
    }

    document.getElementById('emergencyContactID').value = '';
    document.getElementById('emergencyContactModalLabel').textContent = 'Add Emergency Contact';
    document.getElementById('ecCountry').value = 'Kenya';
    document.getElementById('contactPriority').value = 'secondary';
}

function editEmergencyContact(contactID) {
    fetch(`<?= $base ?>php/scripts/global/admin/employee_contacts_api.php?action=get_emergency_contact&id=${contactID}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateEmergencyContactForm(data.data);
                document.getElementById('emergencyContactModalLabel').textContent = 'Edit Emergency Contact';
            } else {
                showToast(data.message || 'Failed to load emergency contact', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while loading the emergency contact', 'danger');
        });
}

function populateEmergencyContactForm(contact) {
    document.getElementById('emergencyContactID').value = contact.emergencyContactID;
    document.getElementById('contactName').value = contact.contactName || '';
    document.getElementById('relationship').value = contact.relationship || '';
    document.getElementById('primaryPhoneNumber').value = contact.primaryPhoneNumber || '';
    document.getElementById('secondaryPhoneNumber').value = contact.secondaryPhoneNumber || '';
    document.getElementById('workPhoneNumber').value = contact.workPhoneNumber || '';
    document.getElementById('emailAddress').value = contact.emailAddress || '';
    document.getElementById('ecAddress').value = contact.address || '';
    document.getElementById('ecCity').value = contact.city || '';
    document.getElementById('ecCounty').value = contact.county || '';
    document.getElementById('ecCountry').value = contact.country || 'Kenya';
    document.getElementById('isPrimaryEC').checked = (contact.isPrimary === 'Y');
    document.getElementById('contactPriority').value = contact.contactPriority || 'secondary';
    document.getElementById('sortOrder').value = contact.sortOrder || 0;
    document.getElementById('occupation').value = contact.occupation || '';
    document.getElementById('employer').value = contact.employer || '';
    document.getElementById('nationalID').value = contact.nationalID || '';
    document.getElementById('bloodType').value = contact.bloodType || '';
    document.getElementById('medicalConditions').value = contact.medicalConditions || '';
    document.getElementById('authorizedToCollectSalary').checked = (contact.authorizedToCollectSalary === 'Y');
    document.getElementById('authorizedForMedicalDecisions').checked = (contact.authorizedForMedicalDecisions === 'Y');
    document.getElementById('ecNotes').value = contact.notes || '';
}

function saveEmergencyContact(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    formData.append('action', 'save_emergency_contact');

    const submitBtn = event.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i> Saving...';

    fetch('<?= $base ?>php/scripts/global/admin/employee_contacts_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Emergency contact saved successfully', 'success');
            setTimeout(() => {
                window.location.href = '?s=<?= $s ?>&p=<?= $p ?>&uid=<?= $employeeID ?>&tab=contacts';
            }, 1500);
        } else {
            showToast(data.message || 'Failed to save emergency contact', 'danger');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="ri-save-line me-1"></i> Save Emergency Contact';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while saving the emergency contact', 'danger');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="ri-save-line me-1"></i> Save Emergency Contact';
    });
}

function deleteEmergencyContact(contactID) {
    if (!confirm('Are you sure you want to delete this emergency contact?')) {
        return;
    }

    fetch(`<?= $base ?>php/scripts/global/admin/employee_contacts_api.php?action=delete_emergency_contact&id=${contactID}`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Emergency contact deleted successfully', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast(data.message || 'Failed to delete emergency contact', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while deleting the emergency contact', 'danger');
    });
}

// Toast notification function (if not already defined)
if (typeof showToast === 'undefined') {
    function showToast(message, type = 'info') {
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }

        const iconMap = {
            'danger': 'ri-error-warning-line',
            'warning': 'ri-alert-line',
            'success': 'ri-checkbox-circle-line',
            'info': 'ri-information-line'
        };

        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="${iconMap[type] || 'ri-information-line'} me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        toastContainer.appendChild(toast);

        if (typeof bootstrap !== 'undefined') {
            const bsToast = new bootstrap.Toast(toast, { autohide: true, delay: 5000 });
            bsToast.show();
        } else {
            toast.style.display = 'block';
            setTimeout(() => toast.remove(), 5000);
        }

        toast.addEventListener('hidden.bs.toast', function() {
            toast.remove();
        });
    }
}
</script>

<style>
.address-card {
    border-left: 4px solid #0d6efd;
}

.emergency-contact-card {
    border-left: 4px solid #dc3545;
}

.address-content p {
    line-height: 1.8;
}

.contact-details > div {
    padding: 5px 0;
}

.btn-icon {
    width: 28px;
    height: 28px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.bg-info-transparent {
    background-color: rgba(13, 202, 240, 0.1);
    color: #0dcaf0;
}

.bg-warning-transparent {
    background-color: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}
</style>

