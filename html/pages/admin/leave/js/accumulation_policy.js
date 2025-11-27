/**
 * JavaScript for Leave Accumulation Policy Management
 * Handles form interactions, AJAX calls, and UI updates
 */

class AccumulationPolicyManager {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.initializeFormValidation();
    }

    bindEvents() {
        // Form submission
        const policyForm = document.getElementById('policyForm');
        if (policyForm) {
            policyForm.addEventListener('submit', (e) => this.handleFormSubmit(e));
        }

        // Rule management
        const addRuleBtn = document.querySelector('[onclick="addRule()"]');
        if (addRuleBtn) {
            addRuleBtn.addEventListener('click', () => this.addRule());
        }

        // Delete confirmations
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('delete-policy') || e.target.classList.contains('delete-rule')) {
                this.handleDelete(e);
            }
        });

        // Status toggle
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('toggle-status')) {
                this.togglePolicyStatus(e);
            }
        });

        // Form field changes
        document.addEventListener('change', (e) => {
            if (e.target.name === 'accrualType') {
                this.updateAccrualTypeHelp(e.target.value);
            }
        });
    }

    initializeFormValidation() {
        // Real-time validation
        const form = document.getElementById('policyForm');
        if (!form) return;

        const inputs = form.querySelectorAll('input[required], select[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('input', () => this.clearFieldError(input));
        });
    }

    async handleFormSubmit(e) {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);

        // Add action
        const action = form.action.includes('update') ? 'update_policy' : 'create_policy';
        formData.append('action', action);

        // Validate form
        if (!this.validateForm(form)) {
            this.showAlert('Please correct the errors in the form.', 'danger');
            return;
        }

        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i>Processing...';
        submitBtn.disabled = true;

        try {
            const response = await fetch(siteUrl + 'php/scripts/leave/config/manage_accumulation_policy.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert(result.message, 'success');

                // Redirect after success
                setTimeout(() => {
                    window.location.href = '?action=list';
                }, 1500);
            } else {
                this.showAlert(result.message, 'danger');
            }
        } catch (error) {
            this.showAlert('An error occurred while processing your request.', 'danger');
            console.error('Form submission error:', error);
        } finally {
            // Restore button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    }

    validateForm(form) {
        let isValid = true;
        const requiredFields = ['policyName', 'leaveTypeID', 'accrualType', 'accrualRate'];

        requiredFields.forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (field && !this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        let errorMessage = '';

        // Required field validation
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            errorMessage = 'This field is required.';
        }

        // Specific field validations
        switch (field.name) {
            case 'accrualRate':
                if (value && (isNaN(value) || parseFloat(value) < 0)) {
                    isValid = false;
                    errorMessage = 'Accrual rate must be a positive number.';
                }
                break;

            case 'priority':
                if (value && (isNaN(value) || parseInt(value) < 1)) {
                    isValid = false;
                    errorMessage = 'Priority must be a positive number.';
                }
                break;

            case 'maxCarryover':
                if (value && (isNaN(value) || parseInt(value) < 0)) {
                    isValid = false;
                    errorMessage = 'Max carryover must be a non-negative number.';
                }
                break;
        }

        // Date validations
        if (field.type === 'date' && value) {
            const date = new Date(value);
            if (isNaN(date.getTime())) {
                isValid = false;
                errorMessage = 'Please enter a valid date.';
            }
        }

        // Update UI
        if (isValid) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');

            // Show error message
            let feedback = field.parentNode.querySelector('.invalid-feedback');
            if (!feedback) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                field.parentNode.appendChild(feedback);
            }
            feedback.textContent = errorMessage;
        }

        return isValid;
    }

    clearFieldError(field) {
        field.classList.remove('is-invalid', 'is-valid');
        const feedback = field.parentNode.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.textContent = '';
        }
    }

    addRule() {
        const template = document.getElementById('ruleTemplate');
        const container = document.getElementById('rulesContainer');

        if (template && container) {
            const clone = template.content.cloneNode(true);

            // Add remove functionality
            const removeBtn = clone.querySelector('[onclick="removeRule(this)"]');
            if (removeBtn) {
                removeBtn.addEventListener('click', (e) => this.removeRule(e.target));
            }

            container.appendChild(clone);

            // Add animation
            const newRule = container.lastElementChild;
            newRule.style.opacity = '0';
            newRule.style.transform = 'translateY(-20px)';

            setTimeout(() => {
                newRule.style.transition = 'all 0.3s ease';
                newRule.style.opacity = '1';
                newRule.style.transform = 'translateY(0)';
            }, 10);
        }
    }

    removeRule(button) {
        const ruleItem = button.closest('.rule-item');
        if (ruleItem) {
            ruleItem.style.transition = 'all 0.3s ease';
            ruleItem.style.opacity = '0';
            ruleItem.style.transform = 'translateY(-20px)';

            setTimeout(() => {
                ruleItem.remove();
            }, 300);
        }
    }

    async handleDelete(e) {
        e.preventDefault();

        const url = e.target.getAttribute('href');
        const itemType = e.target.classList.contains('delete-policy') ? 'policy' : 'rule';

        if (confirm(`Are you sure you want to delete this ${itemType}? This action cannot be undone.`)) {
            try {
                const response = await fetch(url);
                const result = await response.json();

                if (result.success) {
                    this.showAlert(result.message, 'success');

                    // Remove item from UI
                    const item = e.target.closest('.policy-card, .rule-item');
                    if (item) {
                        item.style.transition = 'all 0.3s ease';
                        item.style.opacity = '0';
                        item.style.transform = 'scale(0.95)';

                        setTimeout(() => {
                            item.remove();
                        }, 300);
                    }
                } else {
                    this.showAlert(result.message, 'danger');
                }
            } catch (error) {
                this.showAlert('An error occurred while deleting the item.', 'danger');
                console.error('Delete error:', error);
            }
        }
    }

    async togglePolicyStatus(e) {
        e.preventDefault();

        const policyID = e.target.dataset.policyId;
        const currentStatus = e.target.dataset.status;
        const newStatus = currentStatus === 'Y' ? 'N' : 'Y';

        try {
            const formData = new FormData();
            formData.append('action', 'toggle_policy_status');
            formData.append('policyID', policyID);
            formData.append('status', newStatus);

            const response = await fetch(siteUrl + 'php/scripts/leave/config/manage_accumulation_policy.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert(result.message, 'success');

                // Update UI
                const statusBadge = e.target.closest('.card').querySelector('.badge');
                const newStatusText = newStatus === 'Y' ? 'Active' : 'Inactive';
                const newStatusClass = newStatus === 'Y' ? 'bg-success' : 'bg-secondary';

                statusBadge.textContent = newStatusText;
                statusBadge.className = `badge ${newStatusClass}`;

                // Update button
                e.target.dataset.status = newStatus;
                e.target.innerHTML = newStatus === 'Y' ?
                    '<i class="ri-pause-line me-1"></i>Deactivate' :
                    '<i class="ri-play-line me-1"></i>Activate';
            } else {
                this.showAlert(result.message, 'danger');
            }
        } catch (error) {
            this.showAlert('An error occurred while updating the policy status.', 'danger');
            console.error('Status toggle error:', error);
        }
    }

    updateAccrualTypeHelp(accrualType) {
        const helpTexts = {
            'Front-Loaded': 'Full annual entitlement granted upfront at a specified date (e.g., 30 days on January 1st)',
            'Periodic': 'Leave accrued at regular intervals based on accrual period (e.g., 2.5 days per month)',
            'Proration': 'Leave accrued proportionally based on days/months worked or service period'
        };

        let helpElement = document.getElementById('accrualTypeHelp');
        if (!helpElement) {
            helpElement = document.createElement('small');
            helpElement.id = 'accrualTypeHelp';
            helpElement.className = 'form-text text-muted';
            const accrualTypeField = document.getElementById('accrualType');
            if (accrualTypeField && accrualTypeField.parentNode) {
                accrualTypeField.parentNode.appendChild(helpElement);
            }
        }

        helpElement.textContent = helpTexts[accrualType] || '';

        // Show/hide additional fields based on accrual type
        this.toggleAccrualTypeFields(accrualType);
    }

    toggleAccrualTypeFields(accrualType) {
        const accrualPeriodField = document.getElementById('accrualPeriodField');
        const frontLoadDateField = document.getElementById('frontLoadDateField');
        const prorationBasisField = document.getElementById('prorationBasisField');

        // Hide all fields first
        if (accrualPeriodField) accrualPeriodField.style.display = 'none';
        if (frontLoadDateField) frontLoadDateField.style.display = 'none';
        if (prorationBasisField) prorationBasisField.style.display = 'none';

        // Show relevant field based on type
        switch(accrualType) {
            case 'Periodic':
                if (accrualPeriodField) accrualPeriodField.style.display = 'block';
                break;
            case 'Front-Loaded':
                if (frontLoadDateField) frontLoadDateField.style.display = 'block';
                break;
            case 'Proration':
                if (prorationBasisField) prorationBasisField.style.display = 'block';
                break;
        }
    }

    showAlert(message, type = 'info') {
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.alert');
        existingAlerts.forEach(alert => alert.remove());

        // Create new alert
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            <i class="ri-${type === 'success' ? 'check' : type === 'danger' ? 'error-warning' : 'information'}-line me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        // Insert at top of main content
        const mainContent = document.querySelector('.col-md-9, .col-lg-10');
        if (mainContent) {
            mainContent.insertBefore(alertDiv, mainContent.firstChild);

            // Auto-hide after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    const bsAlert = new bootstrap.Alert(alertDiv);
                    bsAlert.close();
                }
            }, 5000);
        }
    }

    // Utility method for making API calls
    async apiCall(action, data = {}) {
        const formData = new FormData();
        formData.append('action', action);

        Object.keys(data).forEach(key => {
            formData.append(key, data[key]);
        });

        try {
            const response = await fetch(siteUrl + 'php/scripts/leave/config/manage_accumulation_policy.php', {
                method: 'POST',
                body: formData
            });

            return await response.json();
        } catch (error) {
            console.error('API call error:', error);
            return { success: false, message: 'An error occurred while processing your request.' };
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.accumulationPolicyManager = new AccumulationPolicyManager();
});

// Global functions for backward compatibility
function addRule() {
    if (window.accumulationPolicyManager) {
        window.accumulationPolicyManager.addRule();
    }
}

function removeRule(button) {
    if (window.accumulationPolicyManager) {
        window.accumulationPolicyManager.removeRule(button);
    }
}

function validatePolicyForm() {
    if (window.accumulationPolicyManager) {
        const form = document.getElementById('policyForm');
        return window.accumulationPolicyManager.validateForm(form);
    }
    return true;
}

