<style>
/* Employee Modal Steps */
.step-item {
    text-align: center;
    flex: 1;
    position: relative;
}

.step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin: 0 auto 5px;
    transition: all 0.3s;
}

.step-item.active .step-circle {
    background-color: #0d6efd;
    color: white;
}

.step-item.completed .step-circle {
    background-color: #28a745;
    color: white;
}

.step-item small {
    display: block;
    font-size: 11px;
    color: #6c757d;
}

.step-line {
    flex: 1;
    height: 2px;
    background-color: #e9ecef;
    margin: 20px 10px 0;
}

.step-item.active ~ .step-line,
.step-item.completed ~ .step-line {
    background-color: #0d6efd;
}

.step-content {
    min-height: 350px;
}

.card-body .fs-15 {
    font-size: 15px;
}

.bg-light.rounded {
    border: 1px solid #e9ecef;
}

.list-group-item {
    border-left: 0;
    border-right: 0;
}

.list-group-item:first-child {
    border-top: 0;
}

.list-group-item:last-child {
    border-bottom: 0;
}
</style>

<script>
// ============================================================================
// ENTITY FUNCTIONS
// ============================================================================

function editEntity(entityID) {
    // Fetch entity data and populate edit modal
    const modal = document.querySelector('#manageEntity');

    if (!modal) {
        console.error('Entity modal not found');
        return;
    }

    // Update modal title
    const modalTitle = modal.querySelector('.modal-title');
    if (modalTitle) {
        modalTitle.textContent = 'Edit Organization Entity';
    }

    // Fetch entity data via AJAX
    fetch('<?= $base ?>php/scripts/global/admin/get_entity.php?entityID=' + entityID)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch entity data');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.entity) {
                const entity = data.entity;
                console.log('Entity data loaded:', entity);

                // Populate form fields
                const fields = {
                    'entityID': entity.entityID,
                    'orgDataID': entity.orgDataID,
                    'entityName': entity.entityName,
                    'entityTypeID': entity.entityTypeID,
                    'registrationNumber': entity.registrationNumber,
                    'entityPIN': entity.entityPIN,
                    'entityDescription': entity.entityDescription,
                    'entityCity': entity.entityCity,
                    'entityCountry': entity.countryID,
                    'entityPhoneNumber': entity.entityPhoneNumber,
                    'entityEmail': entity.entityEmail,
                    'entityParentID': entity.entityParentID,
                    'industrySectorID': entity.industrySectorID
                };

                // Fill in each field
                for (const [fieldName, value] of Object.entries(fields)) {
                    const input = modal.querySelector(`[name="${fieldName}"], #${fieldName}`);
                    if (input) {
                        if (input.tagName === 'SELECT') {
                            input.value = value || '';
                            input.dispatchEvent(new Event('change', { bubbles: true }));
                        } else if (input.type === 'checkbox') {
                            input.checked = value === 'Y';
                        } else if (input.type === 'textarea' || input.tagName === 'TEXTAREA') {
                            input.value = value || '';
                        } else {
                            input.value = value || '';
                        }
                    }
                }

                const suspendedCheckbox = modal.querySelector('#entitySuspended, [name="Suspended"]');
                if (suspendedCheckbox) {
                    suspendedCheckbox.checked = entity.Suspended === 'Y';
                }

            } else {
                if (typeof showToast === 'function') {
                    showToast('Error: ' + (data.message || 'Failed to load entity data'), 'error');
                } else {
                    alert('Error: ' + (data.message || 'Failed to load entity data'));
                }
                console.error('Entity data error:', data);
            }
        })
        .catch(error => {
            console.error('Error loading entity:', error);
            if (typeof showToast === 'function') {
                showToast('Error loading entity data. Please try again.', 'error');
            } else {
                alert('Error loading entity data. Please try again.');
            }
        });
}

function toggleEntitySuspension(entityID, currentStatus) {
    const action = currentStatus === 'N' ? 'suspend' : 'activate';
    if (confirm('Are you sure you want to ' + action + ' this entity?')) {
        if (typeof showToast === 'function') {
            showToast(action + ' entity ID: ' + entityID, 'info');
        } else {
            alert(action + ' entity ID: ' + entityID);
        }
    }
}

function addDepartment(entityID) {
    if (typeof showToast === 'function') {
        showToast('Add department for entity ID: ' + entityID, 'info');
    } else {
        alert('Add department for entity ID: ' + entityID);
    }
}

function viewAllEmployees(entityID) {
    if (typeof showToast === 'function') {
        showToast('View all employees for entity ID: ' + entityID, 'info');
    } else {
        alert('View all employees for entity ID: ' + entityID);
    }
}

function manageDepartments(entityID) {
    if (typeof showToast === 'function') {
        showToast('Manage departments for entity ID: ' + entityID, 'info');
    } else {
        alert('Manage departments for entity ID: ' + entityID);
    }
}

function generateEntityReport(entityID) {
    if (typeof showToast === 'function') {
        showToast('Generate report for entity ID: ' + entityID, 'info');
    } else {
        alert('Generate report for entity ID: ' + entityID);
    }
}

function exportEntityData(entityID) {
    if (typeof showToast === 'function') {
        showToast('Export data for entity ID: ' + entityID, 'info');
    } else {
        alert('Export data for entity ID: ' + entityID);
    }
}

// ============================================================================
// UNIT FUNCTIONS
// ============================================================================

function addUnitForEntity(entityID, unitTypeName = null) {
    const modal = document.querySelector('#manageUnitModal');
    if (modal) {
        modal.querySelector('form')?.reset();
        const entityInput = modal.querySelector('#unit_entityID');
        if (entityInput) {
            entityInput.value = entityID;
        }
        const modalTitle = modal.querySelector('.modal-title');
        if (modalTitle) {
            if (unitTypeName) {
                modalTitle.textContent = 'Add ' + unitTypeName;
            } else {
                modalTitle.textContent = 'Add Entity Unit';
            }
        }

        // If unitTypeName is provided, pre-select it in the dropdown
        if (unitTypeName) {
            const unitTypeSelect = modal.querySelector('#unit_unitTypeID');
            if (unitTypeSelect) {
                // Find and select the option that matches the unitTypeName
                const options = unitTypeSelect.options;
                for (let i = 0; i < options.length; i++) {
                    if (options[i].text.toLowerCase() === unitTypeName.toLowerCase()) {
                        unitTypeSelect.selectedIndex = i;
                        break;
                    }
                }
            }
        }
    }
}

function editUnit(unitID) {
    const modal = document.querySelector('#manageUnitModal');

    if (!modal) {
        console.error('Unit modal not found');
        return;
    }

    const modalTitle = modal.querySelector('.modal-title');
    if (modalTitle) {
        modalTitle.textContent = 'Edit Entity Unit';
    }

    const url = '<?= $base ?>php/scripts/global/admin/get_unit.php?unitID=' + unitID;
    console.log('Fetching unit data from:', url);

    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.text();
        })
        .then(text => {
            console.log('Raw response:', text);
            try {
                const data = JSON.parse(text);
                console.log('Parsed JSON:', data);

                if (data.success && data.unit) {
                    const unit = data.unit;
                    console.log('Unit data loaded:', unit);

                    const fields = {
                        'unitID': unit.unitID,
                        'orgDataID': unit.orgDataID,
                        'entityID': unit.entityID,
                        'unitName': unit.unitName,
                        'unitCode': unit.unitCode,
                        'unitTypeID': unit.unitTypeID,
                        'parentUnitID': unit.parentUnitID || '0',
                        'headOfUnitID': unit.headOfUnitID || '0',
                        'unitDescription': unit.unitDescription
                    };

                    for (const [fieldName, value] of Object.entries(fields)) {
                        const input = modal.querySelector(`[name="${fieldName}"], #${fieldName}`);
                        if (input) {
                            if (input.tagName === 'SELECT') {
                                // Handle TomSelect if it's initialized (for headOfUnitID)
                                if (input.tomselect) {
                                    input.tomselect.setValue(value || '', true);
                                } else {
                                    input.value = value || '';
                                    input.dispatchEvent(new Event('change', { bubbles: true }));
                                }
                            } else if (input.type === 'checkbox') {
                                input.checked = value === 'Y';
                            } else if (input.type === 'textarea' || input.tagName === 'TEXTAREA') {
                                input.value = value || '';
                            } else {
                                input.value = value || '';
                            }
                        } else {
                            console.warn('Field not found in modal:', fieldName);
                        }
                    }

                } else {
                    const errorMsg = data.message || 'Failed to load unit data';
                    if (typeof showToast === 'function') {
                        showToast('Error: ' + errorMsg, 'error');
                    } else {
                        alert('Error: ' + errorMsg);
                    }
                    console.error('Unit data error:', data);
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response text:', text);
                if (typeof showToast === 'function') {
                    showToast('Error parsing server response. Check console for details.', 'error');
                } else {
                    alert('Error parsing server response. Check console for details.');
                }
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            if (typeof showToast === 'function') {
                showToast('Error loading unit data: ' + error.message, 'error');
            } else {
                alert('Error loading unit data: ' + error.message);
            }
        });
}

function viewDepartment(departmentID) {
    // Redirect to department details or show department info
    // For now, navigate to the units tab with the department highlighted
    window.location.href = '?s=core&ss=admin&p=entity_details&entityID=<?= $entityID ?>&tab=units#unit-' + departmentID;
}

// ============================================================================
// BUSINESS UNIT FUNCTIONS
// ============================================================================

function addBusinessUnitForEntity(entityID) {
    const modal = document.querySelector('#manageBusinessUnitModal');
    if (modal) {
        modal.querySelector('form')?.reset();
        const entityInput = modal.querySelector('#bu_entityID');
        if (entityInput) {
            entityInput.value = entityID;
        }
        const modalTitle = modal.querySelector('.modal-title');
        if (modalTitle) {
            modalTitle.textContent = 'Add Business Unit';
        }
    }
}

function editBusinessUnit(businessUnitID) {
    const modal = document.querySelector('#manageBusinessUnitModal');

    if (!modal) {
        console.error('Business unit modal not found');
        return;
    }

    const modalTitle = modal.querySelector('.modal-title');
    if (modalTitle) {
        modalTitle.textContent = 'Edit Business Unit';
    }

    const url = '<?= $base ?>php/scripts/global/admin/get_business_unit.php?businessUnitID=' + businessUnitID;
    console.log('Fetching business unit data from:', url);

    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.text();
        })
        .then(text => {
            console.log('Raw response:', text);
            try {
                const data = JSON.parse(text);
                console.log('Parsed JSON:', data);

                if (data.success && data.businessUnit) {
                    const bu = data.businessUnit;
                    console.log('Business unit data loaded:', bu);

                    const fields = {
                        'businessUnitID': bu.businessUnitID,
                        'bu_orgDataID': bu.orgDataID,
                        'bu_entityID': bu.entityID,
                        'businessUnitName': bu.businessUnitName,
                        'bu_unitTypeID': bu.unitTypeID,
                        'categoryID': bu.categoryID,
                        'headOfUnitID': bu.headOfUnitID || '0',
                        'businessUnitDescription': bu.businessUnitDescription
                    };

                    for (const [fieldName, value] of Object.entries(fields)) {
                        const input = modal.querySelector(`[name="${fieldName}"], #${fieldName}`);
                        if (input) {
                            if (input.tagName === 'SELECT') {
                                // Handle TomSelect if it's initialized
                                if (input.tomselect) {
                                    input.tomselect.setValue(value || '', true);
                                } else {
                                    input.value = value || '';
                                    input.dispatchEvent(new Event('change', { bubbles: true }));
                                }
                            } else if (input.type === 'checkbox') {
                                input.checked = value === 'Y';
                            } else if (input.type === 'textarea' || input.tagName === 'TEXTAREA') {
                                input.value = value || '';
                            } else {
                                input.value = value || '';
                            }
                        } else {
                            console.warn('Field not found in modal:', fieldName);
                        }
                    }

                } else {
                    const errorMsg = data.message || 'Failed to load business unit data';
                    if (typeof showToast === 'function') {
                        showToast('Error: ' + errorMsg, 'error');
                    } else {
                        alert('Error: ' + errorMsg);
                    }
                    console.error('Business unit data error:', data);
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                console.error('Response text:', text);
                if (typeof showToast === 'function') {
                    showToast('Error parsing server response. Check console for details.', 'error');
                } else {
                    alert('Error parsing server response. Check console for details.');
                }
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            if (typeof showToast === 'function') {
                showToast('Error loading business unit data: ' + error.message, 'error');
            } else {
                alert('Error loading business unit data: ' + error.message);
            }
        });
}

// ============================================================================
// EMPLOYEE FUNCTIONS
// ============================================================================

function addEmployee(entityID) {
    const modal = document.querySelector('#addEmployeeModal');
    if (modal) {
        const form = modal.querySelector('form');
        if (form) form.reset();

        const entityInput = modal.querySelector('#emp_entityID');
        if (entityInput) {
            entityInput.value = entityID;
        }

        goToStep(1);
        initializeEmployeeDatePickers();

        const modalTitle = modal.querySelector('.modal-title');
        if (modalTitle) {
            modalTitle.textContent = 'Add Employee to Entity';
        }
    }
}

function initializeEmployeeDatePickers() {
    const dobElement = document.getElementById('emp_dateOfBirth');
    if (dobElement && !dobElement._flatpickr) {
        flatpickr('#emp_dateOfBirth', {
            dateFormat: 'Y-m-d',
            maxDate: new Date(new Date().setFullYear(new Date().getFullYear() - 18)),
            minDate: new Date(new Date().setFullYear(new Date().getFullYear() - 80)),
            defaultDate: null,
            allowInput: true,
            altInput: true,
            altFormat: 'F j, Y',
            clickOpens: true,
            monthSelectorType: 'dropdown',
            yearSelectorType: 'dropdown'
        });
    }

    const employmentDateElement = document.getElementById('emp_dateOfEmployment');
    if (employmentDateElement && !employmentDateElement._flatpickr) {
        flatpickr('#emp_dateOfEmployment', {
            dateFormat: 'Y-m-d',
            maxDate: new Date(new Date().setMonth(new Date().getMonth() + 6)),
            minDate: new Date(new Date().setFullYear(new Date().getFullYear() - 50)),
            defaultDate: 'today',
            allowInput: true,
            altInput: true,
            altFormat: 'F j, Y',
            clickOpens: true,
            monthSelectorType: 'dropdown',
            yearSelectorType: 'dropdown'
        });
    }
}

function goToStep(stepNumber) {
    document.querySelectorAll('.step-content').forEach(function(step) {
        step.style.display = 'none';
    });

    const currentStep = document.getElementById('step' + stepNumber + 'Content');
    if (currentStep) {
        currentStep.style.display = 'block';
    }

    document.querySelectorAll('.step-item').forEach(function(item, index) {
        item.classList.remove('active', 'completed');
        if (index + 1 < stepNumber) {
            item.classList.add('completed');
        } else if (index + 1 === stepNumber) {
            item.classList.add('active');
        }
    });

    // Update step alert text
    const stepTexts = {
        1: 'Please enter personal information',
        2: 'Please provide employment details',
        3: 'Please enter payroll information (optional - you can skip this step)'
    };

    const stepTextElement = document.getElementById('currentStepText');
    if (stepTextElement && stepTexts[stepNumber]) {
        stepTextElement.textContent = stepTexts[stepNumber];
    }

    const stepAlert = document.getElementById('currentStepAlert');
    if (stepAlert) {
        const stepLabel = stepAlert.querySelector('strong');
        if (stepLabel) {
            stepLabel.textContent = 'Step ' + stepNumber + ' of 3:';
        }
    }
}

function skipPayroll() {
    document.getElementById('emp_basicSalary').value = '';
    document.getElementById('emp_pin').value = '';
    document.getElementById('emp_nhifNumber').value = '';
    document.getElementById('emp_nssfNumber').value = '';
    document.getElementById('emp_costPerHour').value = '';
    document.getElementById('emp_overtimeAllowed').checked = false;
    document.getElementById('emp_bonusEligible').checked = false;

    const form = document.querySelector('#addEmployeeModal form');
    if (form) {
        form.submit();
    }
}

// ============================================================================
// EVENT LISTENERS
// ============================================================================

document.addEventListener('DOMContentLoaded', function() {
    // Initialize date pickers for employee modal
    initializeEmployeeDatePickers();

    // Edit entity buttons
    document.addEventListener('click', function(e) {
        const editBtn = e.target.closest('.editEntity');
        if (editBtn) {
            e.preventDefault();
            const entityID = editBtn.getAttribute('data-id');
            if (entityID) {
                editEntity(entityID);
            } else {
                console.error('Entity ID not found on edit button');
            }
        }
    });

    // Use event delegation for edit buttons (works with dynamically added/removed elements)
    // This replaces the MutationObserver pattern and direct event listeners

    // Event delegation for edit unit buttons
    if (typeof EventDelegation !== 'undefined') {
        // Use global EventDelegation system
        EventDelegation.on('.editUnit', 'click', function(e, target) {
            e.preventDefault();
            const unitID = target.getAttribute('data-unit-id');
            if (unitID) {
                editUnit(unitID);
            } else {
                console.error('Unit ID not found on edit button');
            }
        }, {}, document);

        // Event delegation for edit business unit buttons
        EventDelegation.on('.editBusinessUnit', 'click', function(e, target) {
            e.preventDefault();
            const businessUnitID = target.getAttribute('data-business-unit-id');
            if (businessUnitID) {
                editBusinessUnit(businessUnitID);
            } else {
                console.error('Business Unit ID not found on edit button');
            }
        }, {}, document);
    } else {
        // Fallback: Use document-level event delegation if EventDelegation is not available
        document.addEventListener('click', function(e) {
            const editUnitBtn = e.target.closest('.editUnit');
            if (editUnitBtn) {
                e.preventDefault();
                const unitID = editUnitBtn.getAttribute('data-unit-id');
                if (unitID) {
                    editUnit(unitID);
                }
            }

            const editBusinessUnitBtn = e.target.closest('.editBusinessUnit');
            if (editBusinessUnitBtn) {
                e.preventDefault();
                const businessUnitID = editBusinessUnitBtn.getAttribute('data-business-unit-id');
                if (businessUnitID) {
                    editBusinessUnit(businessUnitID);
                }
            }
        });
    }

    // ========================================
    // RESET PASSWORD EMAIL HANDLER
    // ========================================

    /**
     * Handle reset email modal population
     */
    document.querySelectorAll('.resetemail').forEach(button => {
        button.addEventListener('click', function() {
            const form = document.querySelector('#resetEmail form');
            if (!form) return;

            const id = this.dataset.id;
            const email = this.dataset.email;

            const userIDInput = form.querySelector('[name="userID"]');
            const userEmailInput = form.querySelector('[name="userEmail"]');

            if (userIDInput) userIDInput.value = id;
            if (userEmailInput) userEmailInput.value = email;
        });
    });

    // ========================================
    // ASSIGN HR MANAGER HANDLER
    // ========================================
    const hrManagerModal = document.getElementById('assignHRManagerModal');
    if (hrManagerModal) {
        const nameTarget = hrManagerModal.querySelector('#assignHrManagerEmployeeName');
        const emailTarget = hrManagerModal.querySelector('#assignHrManagerEmployeeEmail');
        const userIdInput = hrManagerModal.querySelector('#assignHrManagerUserID');
        const entityIdInput = hrManagerModal.querySelector('#assignHrManagerEntityID');
        const roleRadios = hrManagerModal.querySelectorAll('input[name="hrRoleType"]');

        const setHrRole = (role) => {
            const desiredRole = role || 'none';
            roleRadios.forEach(radio => {
                radio.checked = radio.value === desiredRole;
            });
        };

        document.addEventListener('click', function(e) {
            const trigger = e.target.closest('.assign-hr-manager');
            if (!trigger) {
                return;
            }

            const userId = trigger.getAttribute('data-user-id') || '';
            const entityId = trigger.getAttribute('data-entity-id') || '';
            const employeeName = trigger.getAttribute('data-employee-name') || 'Employee';
            const employeeEmail = trigger.getAttribute('data-employee-email') || '';
            const hrRole = (trigger.getAttribute('data-hr-role') || '').toLowerCase();

            if (userIdInput) {
                userIdInput.value = userId;
            }
            if (entityIdInput && entityId) {
                entityIdInput.value = entityId;
            }
            if (nameTarget) {
                nameTarget.textContent = employeeName;
            }
            if (emailTarget) {
                emailTarget.textContent = employeeEmail || 'No email on file';
            }

            if (roleRadios.length) {
                if (hrRole === 'primary' || hrRole === 'substitute') {
                    setHrRole(hrRole);
                } else {
                    setHrRole('none');
                }
            }
        });
    }

    // ========================================
    // TOM SELECT INITIALIZATION FOR HEAD OF UNIT (BUSINESS UNIT MODAL)
    // ========================================
    const manageBusinessUnitModal = document.getElementById('manageBusinessUnitModal');
    if (manageBusinessUnitModal) {
        let headOfUnitTomSelect = null;

        // Initialize TomSelect when modal is shown
        manageBusinessUnitModal.addEventListener('shown.bs.modal', function() {
            const headOfUnitSelect = document.getElementById('bu_headOfUnitID');

            if (headOfUnitSelect && !headOfUnitSelect.tomselect) {
                // Get existing value before initializing TomSelect
                const existingValue = headOfUnitSelect.value;

                headOfUnitTomSelect = new TomSelect(headOfUnitSelect, {
                    placeholder: 'Search for head of unit...',
                    sortField: {
                        field: "text",
                        direction: "asc"
                    },
                    searchField: ['text'],
                    maxOptions: null,
                    create: false
                });

                // Set the value if it exists (for edit mode)
                if (existingValue) {
                    headOfUnitTomSelect.setValue(existingValue, true);
                }
            }
        });

        // Clean up TomSelect when modal is hidden
        manageBusinessUnitModal.addEventListener('hidden.bs.modal', function() {
            const headOfUnitSelect = document.getElementById('bu_headOfUnitID');
            if (headOfUnitSelect && headOfUnitSelect.tomselect) {
                headOfUnitSelect.tomselect.destroy();
                headOfUnitTomSelect = null;
            }
        });
    }

    // ========================================
    // TOM SELECT INITIALIZATION FOR HEAD OF UNIT (UNIT MODAL)
    // ========================================
    const manageUnitModal = document.getElementById('manageUnitModal');
    if (manageUnitModal) {
        let unitHeadOfUnitTomSelect = null;

        // Initialize TomSelect when modal is shown
        manageUnitModal.addEventListener('shown.bs.modal', function() {
            const headOfUnitSelect = document.getElementById('headOfUnitID');

            if (headOfUnitSelect && !headOfUnitSelect.tomselect) {
                // Get existing value before initializing TomSelect
                const existingValue = headOfUnitSelect.value;

                unitHeadOfUnitTomSelect = new TomSelect(headOfUnitSelect, {
                    placeholder: 'Search for head of unit...',
                    sortField: {
                        field: "text",
                        direction: "asc"
                    },
                    searchField: ['text'],
                    maxOptions: null,
                    create: false
                });

                // Set the value if it exists (for edit mode)
                if (existingValue) {
                    unitHeadOfUnitTomSelect.setValue(existingValue, true);
                }
            }
        });

        // Clean up TomSelect when modal is hidden
        manageUnitModal.addEventListener('hidden.bs.modal', function() {
            const headOfUnitSelect = document.getElementById('headOfUnitID');
            if (headOfUnitSelect && headOfUnitSelect.tomselect) {
                headOfUnitSelect.tomselect.destroy();
                unitHeadOfUnitTomSelect = null;
            }
        });
    }
});
</script>

