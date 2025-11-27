<?php
/**
 * Contacts Tab
 * Displays contact information and emergency contacts
 */

// Get addresses
$addresses = EmployeeProfileExtended::get_addresses(['employeeID' => $employeeID], false, $DBConn);

// Get emergency contacts
$emergencyContacts = EmployeeProfileExtended::get_emergency_contacts(['employeeID' => $employeeID], false, $DBConn);
?>

<div class="section-header">
    <h5 class="mb-0"><i class="ri-contacts-line me-2"></i>Contact Information</h5>
</div>

<!-- Primary Contact Information -->
<div class="row">
    <div class="col-md-6">
        <div class="info-card">
            <h6 class="fw-bold mb-3">Primary Contact</h6>

            <div class="data-row">
                <span class="data-label">Phone Number:</span>
                <span class="data-value"><?= htmlspecialchars($employeeDetails->phoneNo ?? 'Not provided') ?></span>
            </div>

            <div class="data-row">
                <span class="data-label">Email Address:</span>
                <span class="data-value"><?= htmlspecialchars($employeeDetails->Email ?? 'Not provided') ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Addresses -->
<div class="row mt-3">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">Addresses</h6>
            <?php if ($canEdit): ?>
            <button class="btn btn-sm btn-primary" onclick="addAddress()">
                <i class="ri-add-line me-1"></i> Add Address
            </button>
            <?php endif; ?>
        </div>

        <?php if ($addresses && count($addresses) > 0): ?>
        <div class="row">
            <?php foreach ($addresses as $address): ?>
            <div class="col-md-6 mb-3">
                <div class="info-card">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="fw-bold text-capitalize">
                            <?= htmlspecialchars($address->addressType) ?> Address
                            <?php if ($address->isPrimary == 'Y'): ?>
                            <span class="badge bg-primary ms-2">Primary</span>
                            <?php endif; ?>
                        </h6>
                        <?php if ($canEdit): ?>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-sm btn-outline-primary" onclick="editAddress(<?= $address->addressID ?>)">
                                <i class="ri-edit-line"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteAddress(<?= $address->addressID ?>)">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>

                    <p class="mb-1"><?= htmlspecialchars($address->addressLine1 ?? '') ?></p>
                    <?php if (!empty($address->addressLine2)): ?>
                    <p class="mb-1"><?= htmlspecialchars($address->addressLine2) ?></p>
                    <?php endif; ?>
                    <p class="mb-1">
                        <?= htmlspecialchars($address->city ?? '') ?>
                        <?= !empty($address->postalCode) ? ' ' . htmlspecialchars($address->postalCode) : '' ?>
                    </p>
                    <p class="mb-0"><?= htmlspecialchars($address->country ?? '') ?></p>
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

<!-- Emergency Contacts -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">Emergency Contacts</h6>
            <?php if ($canEdit): ?>
            <button class="btn btn-sm btn-primary" onclick="addEmergencyContact()">
                <i class="ri-add-line me-1"></i> Add Emergency Contact
            </button>
            <?php endif; ?>
        </div>

        <?php if ($emergencyContacts && count($emergencyContacts) > 0): ?>
        <div class="row">
            <?php foreach ($emergencyContacts as $contact): ?>
            <div class="col-md-6 mb-3">
                <div class="info-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="fw-bold mb-1">
                                <?= htmlspecialchars($contact->contactName) ?>
                                <?php if ($contact->isPrimary == 'Y'): ?>
                                <span class="badge bg-danger ms-2">Primary</span>
                                <?php endif; ?>
                            </h6>
                            <p class="text-muted mb-2"><?= htmlspecialchars($contact->relationship) ?></p>

                            <div class="mb-1">
                                <i class="ri-phone-line me-2"></i>
                                <strong>Primary:</strong> <?= htmlspecialchars($contact->primaryPhoneNumber) ?>
                            </div>

                            <?php if (!empty($contact->secondaryPhoneNumber)): ?>
                            <div class="mb-1">
                                <i class="ri-phone-line me-2"></i>
                                <strong>Secondary:</strong> <?= htmlspecialchars($contact->secondaryPhoneNumber) ?>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($contact->emailAddress)): ?>
                            <div class="mb-1">
                                <i class="ri-mail-line me-2"></i>
                                <?= htmlspecialchars($contact->emailAddress) ?>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($contact->address)): ?>
                            <div class="mt-2">
                                <small class="text-muted"><?= htmlspecialchars($contact->address) ?></small>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($canEdit): ?>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-sm btn-outline-primary" onclick="editEmergencyContact(<?= $contact->emergencyContactID ?>)">
                                <i class="ri-edit-line"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteEmergencyContact(<?= $contact->emergencyContactID ?>)">
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
        <div class="alert alert-warning">
            <i class="ri-alert-line me-2"></i>
            <strong>Important:</strong> No emergency contacts on file. Please add at least one emergency contact.
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function addAddress() {
    alert('Add address modal will be implemented');
}

function editAddress(id) {
    alert('Edit address ID: ' + id);
}

function deleteAddress(id) {
    if (confirm('Are you sure you want to delete this address?')) {
        alert('Delete address ID: ' + id);
    }
}

function addEmergencyContact() {
    alert('Add emergency contact modal will be implemented');
}

function editEmergencyContact(id) {
    alert('Edit emergency contact ID: ' + id);
}

function deleteEmergencyContact(id) {
    if (confirm('Are you sure you want to delete this emergency contact?')) {
        alert('Delete emergency contact ID: ' + id);
    }
}
</script>

