/**
 * Client Wizard JavaScript
 * Handles multi-step client creation with addresses, contacts, and documents
 */

(function() {
    'use strict';

    const wizardModal = document.getElementById('clientWizardModal');
    if (!wizardModal) return;

    let currentStep = 1;
    const totalSteps = 5;
    let addressCounter = 0;
    let contactCounter = 0;
    let documentCounter = 0;

    // Wizard data storage
    let wizardData = {
        basic: {},
        addresses: [],
        contacts: [],
        documents: []
    };

    // Initialize wizard when modal opens
    wizardModal.addEventListener('show.bs.modal', function() {
        initializeWizard();
    });

    function initializeWizard() {
        currentStep = 1;
        addressCounter = 0;
        contactCounter = 0;
        documentCounter = 0;
        wizardData = {
            basic: {},
            addresses: [],
            contacts: [],
            documents: []
        };

        // Reset form
        document.getElementById('clientWizardForm').reset();

        // Clear dynamic containers
        document.getElementById('addressesContainer').innerHTML = '';
        document.getElementById('contactsContainer').innerHTML = '';
        document.getElementById('documentsContainer').innerHTML = '';

        // Add first address by default
        addAddress();

        updateStepDisplay();
        updateNavigationButtons();
        setupEventListeners();
    }

    function setupEventListeners() {
        // Navigation buttons
        document.getElementById('wizardPrevBtn').addEventListener('click', previousStep);
        document.getElementById('wizardNextBtn').addEventListener('click', nextStep);
        document.getElementById('wizardSubmitBtn').addEventListener('click', submitWizard);

        // Add item buttons
        document.getElementById('addAddressBtn').addEventListener('click', addAddress);
        document.getElementById('addContactBtn').addEventListener('click', addContact);
        document.getElementById('addDocumentBtn').addEventListener('click', addDocument);

        // Auto-generate client code from name
        const clientNameInput = document.getElementById('wizardClientName');
        clientNameInput.addEventListener('input', function() {
            const code = generateClientCode(this.value);
            document.getElementById('wizardClientCode').value = code;
        });
    }

    function generateClientCode(name) {
        if (!name) return '';
        // Take first 3 letters of each word, uppercase
        return name.split(' ')
            .filter(word => word.length > 0)
            .map(word => word.substring(0, 3).toUpperCase())
            .join('');
    }

    function generateCountryOptions() {
        if (!window.clientWizardCountries || !Array.isArray(window.clientWizardCountries)) {
            return '<option value="">Select Country</option>';
        }

        let options = '<option value="">Select Country</option>';
        window.clientWizardCountries.forEach(country => {
            const selected = country.id === '25' ? ' selected' : ''; // Kenya as default
            options += `<option value="${country.id}"${selected}>${country.name}</option>`;
        });
        return options;
    }

    function generateDocumentTypeOptions() {
        if (!window.clientWizardDocumentTypes || !Array.isArray(window.clientWizardDocumentTypes)) {
            return '<option value="">Select Document Type</option>';
        }

        let options = '<option value="">Select Document Type</option>';
        window.clientWizardDocumentTypes.forEach(docType => {
            options += `<option value="${docType.id}" title="${docType.description}">${docType.name}</option>`;
        });
        return options;
    }

    function generateSalutationOptions() {
        if (!window.clientWizardSalutations || !Array.isArray(window.clientWizardSalutations)) {
            return '<option value="">Select Salutation</option>';
        }

        let options = '<option value="">Select Salutation</option>';
        window.clientWizardSalutations.forEach(salutation => {
            options += `<option value="${salutation.id}">${salutation.name}</option>`;
        });
        return options;
    }

    function generateContactTypeOptions() {
        if (!window.clientWizardContactTypes || !Array.isArray(window.clientWizardContactTypes)) {
            return '<option value="">Select Contact Type</option>';
        }

        let options = '<option value="">Select Contact Type</option>';
        window.clientWizardContactTypes.forEach(contactType => {
            options += `<option value="${contactType.id}">${contactType.name}</option>`;
        });
        return options;
    }


    function updateStepDisplay() {
        // Update step indicators
        document.querySelectorAll('.step-item').forEach((item, index) => {
            const stepNum = index + 1;
            item.classList.remove('active', 'completed');
            if (stepNum === currentStep) {
                item.classList.add('active');
            } else if (stepNum < currentStep) {
                item.classList.add('completed');
            }
        });

        // Update step content
        document.querySelectorAll('.wizard-step').forEach((step, index) => {
            const stepNum = index + 1;
            if (stepNum === currentStep) {
                step.classList.add('active');
            } else {
                step.classList.remove('active');
            }
        });
    }

    function updateNavigationButtons() {
        const prevBtn = document.getElementById('wizardPrevBtn');
        const nextBtn = document.getElementById('wizardNextBtn');
        const submitBtn = document.getElementById('wizardSubmitBtn');

        prevBtn.style.display = currentStep > 1 ? 'inline-block' : 'none';
        nextBtn.style.display = currentStep < totalSteps ? 'inline-block' : 'none';
        submitBtn.style.display = currentStep === totalSteps ? 'inline-block' : 'none';
    }

    function previousStep() {
        if (currentStep > 1) {
            currentStep--;
            updateStepDisplay();
            updateNavigationButtons();
        }
    }

    function nextStep() {
        // Validate current step
        if (!validateStep(currentStep)) {
            return;
        }

        // Save current step data
        saveStepData(currentStep);

        if (currentStep < totalSteps) {
            currentStep++;

            // If moving to review step, populate review
            if (currentStep === 5) {
                populateReview();
            }

            updateStepDisplay();
            updateNavigationButtons();
        }
    }

    function validateStep(step) {
        const errors = [];

        switch(step) {
            case 1: // Basic Info
                if (!document.getElementById('wizardClientName').value.trim()) {
                    errors.push('Client name is required');
                }
                if (!document.getElementById('wizardAccountOwner').value) {
                    errors.push('Account owner is required');
                }
                break;

            case 2: // Addresses
                const addresses = collectAddresses();
                if (addresses.length === 0) {
                    errors.push('At least one address is required');
                } else {
                    const hasHeadquarters = addresses.some(addr => addr.headquarters === 'Y');
                    if (!hasHeadquarters) {
                        errors.push('Please mark one address as headquarters');
                    }
                }
                break;

            case 3: // Contacts (optional, just validate format if provided)
                const contacts = collectContacts();
                contacts.forEach((contact, index) => {
                    if (contact.contactEmail && !isValidEmail(contact.contactEmail)) {
                        errors.push(`Contact ${index + 1}: Invalid email format`);
                    }
                });
                break;

            case 4: // Documents (optional)
                // No validation needed, documents are optional
                break;
        }

        if (errors.length > 0) {
            alert('Please fix the following errors:\n\n' + errors.join('\n'));
            return false;
        }

        return true;
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function saveStepData(step) {
        switch(step) {
            case 1:
                wizardData.basic = {
                    clientName: document.getElementById('wizardClientName').value,
                    clientCode: document.getElementById('wizardClientCode').value,
                    vatNumber: document.getElementById('wizardVatNumber').value,
                    accountOwnerID: document.getElementById('wizardAccountOwner').value,
                    clientDescription: document.getElementById('wizardClientDescription').value,
                    inHouse: document.getElementById('wizardInHouse').checked ? 'Y' : 'N',
                    orgDataID: document.getElementById('wizardOrgDataID').value,
                    entityID: document.getElementById('wizardEntityID').value
                };
                break;

            case 2:
                wizardData.addresses = collectAddresses();
                break;

            case 3:
                wizardData.contacts = collectContacts();
                break;

            case 4:
                wizardData.documents = collectDocuments();
                break;
        }
    }

    // ==================== ADDRESS MANAGEMENT ====================

    function addAddress() {
        addressCounter++;
        const container = document.getElementById('addressesContainer');

        const addressCard = document.createElement('div');
        addressCard.className = 'item-card';
        addressCard.dataset.addressId = addressCounter;
        addressCard.innerHTML = `
            <button type="button" class="btn btn-sm btn-danger remove-item-btn" onclick="removeAddress(${addressCounter})">
                <i class="ri-delete-bin-line"></i>
            </button>
            <div class="item-card-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="item-card-number">${addressCounter}</div>
                    <h6 class="mb-0">Address ${addressCounter}</h6>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Address <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="address_${addressCounter}" rows="2"
                              placeholder="Street address, P.O. Box, etc." required></textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">City <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="city_${addressCounter}"
                           placeholder="City" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Postal Code</label>
                    <input type="text" class="form-control" name="postalCode_${addressCounter}"
                           placeholder="Postal/ZIP Code">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Country <span class="text-danger">*</span></label>
                    <select class="form-select" name="country_${addressCounter}" required>
                        ${generateCountryOptions()}
                    </select>
                </div>
                <div class="col-md-12">
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="addressType_${addressCounter}"
                                               value="PostalAddress" id="postal_${addressCounter}" checked>
                                        <label class="form-check-label" for="postal_${addressCounter}">
                                            <i class="ri-mail-line me-1"></i>Postal Address
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="addressType_${addressCounter}"
                                               value="OfficeAddress" id="office_${addressCounter}">
                                        <label class="form-check-label" for="office_${addressCounter}">
                                            <i class="ri-building-line me-1"></i>Office Address
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input headquarters-checkbox" type="checkbox"
                                               name="headquarters_${addressCounter}" value="Y"
                                               id="hq_${addressCounter}" onchange="handleHeadquartersChange(${addressCounter})">
                                        <label class="form-check-label text-danger fw-semibold" for="hq_${addressCounter}">
                                            <i class="ri-building-4-line me-1"></i>Main Office (Headquarters)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox"
                                               name="billingAddress_${addressCounter}" value="Y"
                                               id="billing_${addressCounter}">
                                        <label class="form-check-label" for="billing_${addressCounter}">
                                            <i class="ri-money-dollar-circle-line me-1"></i>Billing Address
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        container.appendChild(addressCard);
    }

    window.removeAddress = function(id) {
        const card = document.querySelector(`[data-address-id="${id}"]`);
        if (card) {
            card.remove();
            // Renumber remaining addresses
            renumberItems('addressesContainer', 'Address');
        }
    };

    window.handleHeadquartersChange = function(id) {
        const checkbox = document.getElementById(`hq_${id}`);
        if (checkbox.checked) {
            // Uncheck all other headquarters checkboxes
            document.querySelectorAll('.headquarters-checkbox').forEach(cb => {
                if (cb.id !== `hq_${id}`) {
                    cb.checked = false;
                }
            });
        }
    };

    function collectAddresses() {
        const addresses = [];
        const container = document.getElementById('addressesContainer');
        const cards = container.querySelectorAll('.item-card');

        cards.forEach(card => {
            const id = card.dataset.addressId;
            const address = {
                address: card.querySelector(`[name="address_${id}"]`)?.value || '',
                city: card.querySelector(`[name="city_${id}"]`)?.value || '',
                postalCode: card.querySelector(`[name="postalCode_${id}"]`)?.value || '',
                country: card.querySelector(`[name="country_${id}"]`)?.value || '',
                addressType: card.querySelector(`[name="addressType_${id}"]:checked`)?.value || 'OfficeAddress',
                headquarters: card.querySelector(`[name="headquarters_${id}"]`)?.checked ? 'Y' : 'N',
                billingAddress: card.querySelector(`[name="billingAddress_${id}"]`)?.checked ? 'Y' : 'N'
            };
            addresses.push(address);
        });

        return addresses;
    }

    // ==================== CONTACT MANAGEMENT ====================

    function addContact() {
        contactCounter++;
        const container = document.getElementById('contactsContainer');

        // Get addresses for linking
        const addresses = collectAddresses();
        let addressOptions = '<option value="">Not linked to specific address</option>';
        addresses.forEach((addr, index) => {
            const label = `${addr.city || 'Address'} ${index + 1}`;
            addressOptions += `<option value="${index}">${label}</option>`;
        });

        const contactCard = document.createElement('div');
        contactCard.className = 'item-card';
        contactCard.dataset.contactId = contactCounter;
        contactCard.innerHTML = `
            <button type="button" class="btn btn-sm btn-danger remove-item-btn" onclick="removeContact(${contactCounter})">
                <i class="ri-delete-bin-line"></i>
            </button>
            <div class="item-card-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="item-card-number">${contactCounter}</div>
                    <h6 class="mb-0">Contact ${contactCounter}</h6>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Salutation</label>
                    <select class="form-select" name="salutationID_${contactCounter}">
                        ${generateSalutationOptions()}
                    </select>
                </div>
                <div class="col-md-9">
                    <label class="form-label fw-semibold">Contact Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="contactName_${contactCounter}"
                           placeholder="Full Name" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Position/Title</label>
                    <input type="text" class="form-control" name="title_${contactCounter}"
                           placeholder="e.g., CEO, Finance Manager">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Contact Type <span class="text-danger">*</span></label>
                    <select class="form-select" name="contactTypeID_${contactCounter}" required>
                        ${generateContactTypeOptions()}
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" class="form-control" name="contactEmail_${contactCounter}"
                           placeholder="email@example.com">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Phone</label>
                    <input type="tel" class="form-control" name="contactPhone_${contactCounter}"
                           placeholder="+254 700 000 000">
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">
                        <i class="ri-map-pin-line me-1"></i>Link to Address
                    </label>
                    <select class="form-select" name="clientAddressID_${contactCounter}">
                        ${addressOptions}
                    </select>
                    <small class="text-muted">Optional: Associate this contact with a specific address</small>
                </div>
                <div class="col-md-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input primary-contact-checkbox" type="checkbox"
                               name="primaryContact_${contactCounter}" value="Y"
                               id="primary_${contactCounter}" onchange="handlePrimaryContactChange(${contactCounter})">
                        <label class="form-check-label fw-semibold text-primary" for="primary_${contactCounter}">
                            <i class="ri-star-line me-1"></i>Primary Contact
                        </label>
                    </div>
                </div>
            </div>
        `;

        container.appendChild(contactCard);
    }

    window.removeContact = function(id) {
        const card = document.querySelector(`[data-contact-id="${id}"]`);
        if (card) {
            card.remove();
            renumberItems('contactsContainer', 'Contact');
        }
    };

    window.handlePrimaryContactChange = function(id) {
        const checkbox = document.getElementById(`primary_${id}`);
        if (checkbox.checked) {
            // Uncheck all other primary contact checkboxes
            document.querySelectorAll('.primary-contact-checkbox').forEach(cb => {
                if (cb.id !== `primary_${id}`) {
                    cb.checked = false;
                }
            });
        }
    };

    function collectContacts() {
        const contacts = [];
        const container = document.getElementById('contactsContainer');
        const cards = container.querySelectorAll('.item-card');

        cards.forEach(card => {
            const id = card.dataset.contactId;
            const contact = {
                salutationID: card.querySelector(`[name="salutationID_${id}"]`)?.value || null,
                contactName: card.querySelector(`[name="contactName_${id}"]`)?.value || '',
                title: card.querySelector(`[name="title_${id}"]`)?.value || '',
                contactTypeID: card.querySelector(`[name="contactTypeID_${id}"]`)?.value || null,
                contactEmail: card.querySelector(`[name="contactEmail_${id}"]`)?.value || '',
                contactPhone: card.querySelector(`[name="contactPhone_${id}"]`)?.value || '',
                clientAddressID: card.querySelector(`[name="clientAddressID_${id}"]`)?.value || null,
                primaryContact: card.querySelector(`[name="primaryContact_${id}"]`)?.checked ? 'Y' : 'N'
            };
            if (contact.contactName) { // Only add if name is provided
                contacts.push(contact);
            }
        });

        return contacts;
    }

    // ==================== DOCUMENT MANAGEMENT ====================

    function addDocument() {
        documentCounter++;
        const container = document.getElementById('documentsContainer');

        const documentCard = document.createElement('div');
        documentCard.className = 'item-card';
        documentCard.dataset.documentId = documentCounter;
        documentCard.innerHTML = `
            <button type="button" class="btn btn-sm btn-danger remove-item-btn" onclick="removeDocument(${documentCounter})">
                <i class="ri-delete-bin-line"></i>
            </button>
            <div class="item-card-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="item-card-number">${documentCounter}</div>
                    <h6 class="mb-0">Document ${documentCounter}</h6>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Document Type <span class="text-danger">*</span></label>
                    <select class="form-select" name="documentTypeID_${documentCounter}" required>
                        ${generateDocumentTypeOptions()}
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Document Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="clientDocumentName_${documentCounter}"
                           placeholder="e.g., Certificate of Incorporation 2023" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">File Upload <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" name="clientDocumentFile_${documentCounter}"
                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                    <small class="text-muted">Accepted: PDF, DOC, DOCX, JPG, PNG (Max 10MB)</small>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea class="form-control" name="clientDocumentDescription_${documentCounter}" rows="2"
                              placeholder="Additional description about this document..."></textarea>
                </div>
            </div>
        `;

        container.appendChild(documentCard);
    }

    window.removeDocument = function(id) {
        const card = document.querySelector(`[data-document-id="${id}"]`);
        if (card) {
            card.remove();
            renumberItems('documentsContainer', 'Document');
        }
    };

    function collectDocuments() {
        const documents = [];
        const container = document.getElementById('documentsContainer');
        const cards = container.querySelectorAll('.item-card');

        cards.forEach(card => {
            const id = card.dataset.documentId;
            const fileInput = card.querySelector(`[name="clientDocumentFile_${id}"]`);
            const document = {
                documentTypeID: card.querySelector(`[name="documentTypeID_${id}"]`)?.value || '',
                clientDocumentName: card.querySelector(`[name="clientDocumentName_${id}"]`)?.value || '',
                clientDocumentDescription: card.querySelector(`[name="clientDocumentDescription_${id}"]`)?.value || '',
                file: fileInput?.files[0] || null
            };
            if (document.documentTypeID && document.file) {
                documents.push(document);
            }
        });

        return documents;
    }

    // ==================== UTILITY FUNCTIONS ====================

    function renumberItems(containerId, itemType) {
        const container = document.getElementById(containerId);
        const cards = container.querySelectorAll('.item-card');
        cards.forEach((card, index) => {
            const number = index + 1;
            card.querySelector('.item-card-number').textContent = number;
            card.querySelector('h6').textContent = `${itemType} ${number}`;
        });
    }

    // ==================== REVIEW STEP ====================

    function populateReview() {
        // Basic Info
        const basicInfo = wizardData.basic;
        document.getElementById('reviewBasicInfo').innerHTML = `
            <div class="review-item">
                <span class="review-label">Client Name:</span>
                <span class="review-value">${basicInfo.clientName || 'N/A'}</span>
            </div>
            <div class="review-item">
                <span class="review-label">Client Code:</span>
                <span class="review-value">${basicInfo.clientCode || 'N/A'}</span>
            </div>
            <div class="review-item">
                <span class="review-label">PIN/Tax ID:</span>
                <span class="review-value">${basicInfo.vatNumber || 'N/A'}</span>
            </div>
            <div class="review-item">
                <span class="review-label">Account Owner:</span>
                <span class="review-value">${getEmployeeName(basicInfo.accountOwnerID)}</span>
            </div>
            <div class="review-item">
                <span class="review-label">Type:</span>
                <span class="review-value">
                    ${basicInfo.inHouse === 'Y' ? '<span class="badge bg-danger">In-House</span>' : '<span class="badge bg-success">External</span>'}
                </span>
            </div>
            ${basicInfo.clientDescription ? `
            <div class="review-item">
                <span class="review-label">Description:</span>
                <span class="review-value">${basicInfo.clientDescription}</span>
            </div>
            ` : ''}
        `;

        // Addresses
        document.getElementById('reviewAddressCount').textContent = wizardData.addresses.length;
        let addressesHTML = '';
        if (wizardData.addresses.length === 0) {
            addressesHTML = '<p class="text-muted">No addresses added</p>';
        } else {
            wizardData.addresses.forEach((addr, index) => {
                addressesHTML += `
                    <div class="review-item">
                        <strong>Address ${index + 1}:</strong>
                        <div class="ms-3 mt-1">
                            ${addr.address}<br>
                            ${addr.city}, ${addr.postalCode || ''}<br>
                            ${getCountryName(addr.country)}<br>
                            <small class="text-muted">
                                Type: ${addr.addressType}
                                ${addr.headquarters === 'Y' ? ' | <span class="badge bg-danger">Headquarters</span>' : ''}
                                ${addr.billingAddress === 'Y' ? ' | <span class="badge bg-info">Billing</span>' : ''}
                            </small>
                        </div>
                    </div>
                `;
            });
        }
        document.getElementById('reviewAddresses').innerHTML = addressesHTML;

        // Contacts
        document.getElementById('reviewContactCount').textContent = wizardData.contacts.length;
        let contactsHTML = '';
        if (wizardData.contacts.length === 0) {
            contactsHTML = '<p class="text-muted">No contacts added</p>';
        } else {
            wizardData.contacts.forEach((contact, index) => {
                const linkedAddr = contact.linkedAddress !== '' ? ` (Linked to Address ${parseInt(contact.linkedAddress) + 1})` : '';
                contactsHTML += `
                    <div class="review-item">
                        <strong>${contact.contactName}</strong>
                        ${contact.primaryContact === 'Y' ? '<span class="badge bg-primary ms-2">Primary</span>' : ''}
                        <div class="ms-3 mt-1 small">
                            ${contact.contactPosition ? `<div>${contact.contactPosition}</div>` : ''}
                            ${contact.contactEmail ? `<div><i class="ri-mail-line me-1"></i>${contact.contactEmail}</div>` : ''}
                            ${contact.contactPhone ? `<div><i class="ri-phone-line me-1"></i>${contact.contactPhone}</div>` : ''}
                            ${linkedAddr ? `<div class="text-muted">${linkedAddr}</div>` : ''}
                        </div>
                    </div>
                `;
            });
        }
        document.getElementById('reviewContacts').innerHTML = contactsHTML;

        // Documents
        document.getElementById('reviewDocumentCount').textContent = wizardData.documents.length;
        let documentsHTML = '';
        if (wizardData.documents.length === 0) {
            documentsHTML = '<p class="text-muted">No documents uploaded</p>';
        } else {
            wizardData.documents.forEach((doc, index) => {
                documentsHTML += `
                    <div class="review-item">
                        <strong>${getDocumentTypeName(doc.documentTypeID)}</strong>
                        <div class="ms-3 mt-1 small">
                            ${doc.clientDocumentName ? `<div>Name: ${doc.clientDocumentName}</div>` : ''}
                            <div>File: ${doc.file.name} (${formatFileSize(doc.file.size)})</div>
                            ${doc.clientDocumentDescription ? `<div class="text-muted">${doc.clientDocumentDescription}</div>` : ''}
                        </div>
                    </div>
                `;
            });
        }
        document.getElementById('reviewDocuments').innerHTML = documentsHTML;
    }

    function getEmployeeName(id) {
        const select = document.getElementById('wizardAccountOwner');
        const option = select.querySelector(`option[value="${id}"]`);
        return option ? option.textContent : 'N/A';
    }

    function getCountryName(id) {
        if (!window.clientWizardCountries || !Array.isArray(window.clientWizardCountries)) {
            return 'N/A';
        }
        const country = window.clientWizardCountries.find(c => c.id === id || c.id === String(id));
        return country ? country.name : 'N/A';
    }

    function getDocumentTypeName(id) {
        if (!window.clientWizardDocumentTypes || !Array.isArray(window.clientWizardDocumentTypes)) {
            return 'N/A';
        }
        const docType = window.clientWizardDocumentTypes.find(dt => dt.id === id || dt.id === String(id));
        return docType ? docType.name : 'N/A';
    }


    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    // ==================== FORM SUBMISSION ====================

    function submitWizard() {
        const submitBtn = document.getElementById('wizardSubmitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating Client...';

        // Prepare FormData for file uploads
        const formData = new FormData();

        // Add basic info
        Object.keys(wizardData.basic).forEach(key => {
            formData.append(key, wizardData.basic[key]);
        });

        // Add addresses
        formData.append('addresses', JSON.stringify(wizardData.addresses));

        // Add contacts
        formData.append('contacts', JSON.stringify(wizardData.contacts));

        // Add document files
        wizardData.documents.forEach((doc, index) => {
            formData.append(`clientDocumentFile_${index}`, doc.file);
            formData.append(`documentTypeID_${index}`, doc.documentTypeID);
            formData.append(`clientDocumentName_${index}`, doc.clientDocumentName);
            formData.append(`clientDocumentDescription_${index}`, doc.clientDocumentDescription);
        });
        formData.append('documentCount', wizardData.documents.length);

        // Submit to server
        fetch('../php/scripts/clients/process_client_wizard.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(wizardModal).hide();
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to create client'));
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="ri-check-line me-1"></i>Create Client';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An unexpected error occurred. Please try again.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="ri-check-line me-1"></i>Create Client';
        });
    }

})();
