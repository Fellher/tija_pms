<script>
/**
 * HR Admin Dashboard JavaScript
 * 
 * Handles all interactive functionality for the HR admin dashboard
 */

// Global variables
let policyForm = null;
let leaveTypeForm = null;
let rulesContainer = null;
let ruleTemplate = null;
let searchInput = null;
let statusFilter = null;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializePage();
});

/**
 * Initialize page functionality
 */
function initializePage() {
    // Get DOM elements
    policyForm = document.getElementById('policyForm');
    leaveTypeForm = document.getElementById('leaveTypeForm');
    rulesContainer = document.getElementById('rulesContainer');
    ruleTemplate = document.getElementById('ruleTemplate');
    searchInput = document.getElementById('searchInput');
    statusFilter = document.getElementById('statusFilter');
    
    // Initialize components
    initializeAlerts();
    initializeFormValidation();
    initializeDeleteConfirmations();
    initializeTooltips();
    initializeModals();
    initializeSearch();
    
    // Add fade-in animation to cards
    addFadeInAnimation();
    
    console.log('HR Admin Dashboard page initialized');
}

/**
 * Initialize auto-hiding alerts
 */
function initializeAlerts() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            if (alert.classList.contains('alert-dismissible')) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        });
    }, 5000);
}

/**
 * Initialize form validation
 */
function initializeFormValidation() {
    // Policy form validation
    if (policyForm) {
        const requiredFields = policyForm.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            field.addEventListener('blur', function() {
                validateField(this);
            });
            
            field.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    validateField(this);
                }
            });
        });
        
        policyForm.addEventListener('submit', function(e) {
            if (!validatePolicyForm()) {
                e.preventDefault();
                showFormErrors();
            }
        });
    }
    
    // Leave type form validation
    if (leaveTypeForm) {
        const requiredFields = leaveTypeForm.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            field.addEventListener('blur', function() {
                validateField(this);
            });
            
            field.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    validateField(this);
                }
            });
        });
        
        leaveTypeForm.addEventListener('submit', function(e) {
            if (!validateLeaveTypeForm()) {
                e.preventDefault();
                showFormErrors();
            }
        });
    }
}

/**
 * Validate individual field
 */
function validateField(field) {
    const value = field.value.trim();
    const isValid = value !== '';
    
    if (isValid) {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
    } else {
        field.classList.remove('is-valid');
        field.classList.add('is-invalid');
    }
    
    return isValid;
}

/**
 * Validate entire policy form
 */
function validatePolicyForm() {
    if (!policyForm) return true;
    
    const requiredFields = ['policyName', 'accrualType', 'accrualRate'];
    let isValid = true;
    
    requiredFields.forEach(fieldName => {
        const input = policyForm.querySelector(`[name="${fieldName}"]`);
        if (input && !validateField(input)) {
            isValid = false;
        }
    });
    
    return isValid;
}

/**
 * Validate entire leave type form
 */
function validateLeaveTypeForm() {
    if (!leaveTypeForm) return true;
    
    const requiredFields = ['leaveTypeName', 'leaveTypeCode'];
    let isValid = true;
    
    requiredFields.forEach(fieldName => {
        const input = leaveTypeForm.querySelector(`[name="${fieldName}"]`);
        if (input && !validateField(input)) {
            isValid = false;
        }
    });
    
    // Validate leave type code uniqueness
    const codeInput = leaveTypeForm.querySelector('[name="leaveTypeCode"]');
    if (codeInput && codeInput.value.trim()) {
        if (!isValidLeaveTypeCode(codeInput.value.trim())) {
            codeInput.classList.add('is-invalid');
            isValid = false;
        }
    }
    
    return isValid;
}

/**
 * Check if leave type code is valid (alphanumeric, no spaces)
 */
function isValidLeaveTypeCode(code) {
    const codeRegex = /^[A-Z0-9_]+$/;
    return codeRegex.test(code) && code.length >= 2 && code.length <= 10;
}

/**
 * Show form validation errors
 */
function showFormErrors() {
    const form = policyForm || leaveTypeForm;
    if (!form) return;
    
    const invalidFields = form.querySelectorAll('.is-invalid');
    if (invalidFields.length > 0) {
        showAlert('danger', 'Please fill in all required fields correctly.');
        invalidFields[0].focus();
    }
}

/**
 * Initialize delete confirmations
 */
function initializeDeleteConfirmations() {
    // Confirm delete actions
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-policy') || 
            e.target.classList.contains('delete-leave-type') ||
            e.target.classList.contains('delete-rule') ||
            e.target.closest('.delete-policy') ||
            e.target.closest('.delete-leave-type') ||
            e.target.closest('.delete-rule')) {
            
            e.preventDefault();
            
            const itemType = e.target.classList.contains('delete-policy') || 
                           e.target.closest('.delete-policy') ? 'policy' : 
                           e.target.classList.contains('delete-leave-type') || 
                           e.target.closest('.delete-leave-type') ? 'leave type' : 'rule';
            
            const itemName = e.target.getAttribute('data-name') || 
                           e.target.closest('[data-name]')?.getAttribute('data-name') || 
                           'this item';
            
            if (confirm(`Are you sure you want to delete ${itemName}? This action cannot be undone.`)) {
                // Proceed with deletion
                const href = e.target.href || e.target.closest('a')?.href;
                if (href) {
                    window.location.href = href;
                }
            }
        }
    });
}

/**
 * Initialize Bootstrap tooltips
 */
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Initialize Bootstrap modals
 */
function initializeModals() {
    // Handle modal events
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('show.bs.modal', function() {
            // Add loading state if needed
            const form = this.querySelector('form');
            if (form) {
                resetFormValidation(form);
            }
        });
        
        modal.addEventListener('hidden.bs.modal', function() {
            // Clean up when modal is closed
            const form = this.querySelector('form');
            if (form) {
                form.reset();
                resetFormValidation(form);
            }
        });
    });
}

/**
 * Reset form validation state
 */
function resetFormValidation(form) {
    const fields = form.querySelectorAll('.is-invalid, .is-valid');
    fields.forEach(field => {
        field.classList.remove('is-invalid', 'is-valid');
    });
}

/**
 * Initialize search functionality
 */
function initializeSearch() {
    if (searchInput) {
        searchInput.addEventListener('input', debounce(filterItems, 300));
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterItems);
    }
}

/**
 * Filter items based on search and status
 */
function filterItems() {
    const searchTerm = searchInput?.value.toLowerCase() || '';
    const statusValue = statusFilter?.value || '';
    
    // Filter policy cards
    const policyCards = document.querySelectorAll('.policy-card');
    policyCards.forEach(card => {
        const policyName = card.querySelector('.policy-name')?.textContent.toLowerCase() || '';
        const policyDescription = card.querySelector('.policy-description')?.textContent.toLowerCase() || '';
        const statusBadge = card.querySelector('.status-badge')?.textContent.toLowerCase() || '';
        
        const matchesSearch = policyName.includes(searchTerm) || policyDescription.includes(searchTerm);
        const matchesStatus = !statusValue || statusBadge.includes(statusValue);
        
        if (matchesSearch && matchesStatus) {
            card.style.display = 'block';
            card.classList.add('fade-in');
        } else {
            card.style.display = 'none';
        }
    });
    
    // Filter leave type cards
    const leaveTypeCards = document.querySelectorAll('.leave-type-card');
    leaveTypeCards.forEach(card => {
        const leaveTypeName = card.querySelector('.leave-type-title')?.textContent.toLowerCase() || '';
        const leaveTypeDescription = card.querySelector('.leave-type-description')?.textContent.toLowerCase() || '';
        const statusBadge = card.querySelector('.status-badge')?.textContent.toLowerCase() || '';
        
        const matchesSearch = leaveTypeName.includes(searchTerm) || leaveTypeDescription.includes(searchTerm);
        const matchesStatus = !statusValue || statusBadge.includes(statusValue);
        
        if (matchesSearch && matchesStatus) {
            card.style.display = 'block';
            card.classList.add('fade-in');
        } else {
            card.style.display = 'none';
        }
    });
    
    // Show/hide empty state
    const visibleCards = Array.from(document.querySelectorAll('.policy-card, .leave-type-card'))
                              .filter(card => card.style.display !== 'none');
    const emptyState = document.getElementById('emptyState');
    if (emptyState) {
        emptyState.style.display = visibleCards.length === 0 ? 'block' : 'none';
    }
}

/**
 * Add rule to policy form
 */
function addRule() {
    if (!rulesContainer || !ruleTemplate) {
        console.error('Rules container or template not found');
        return;
    }
    
    const ruleClone = ruleTemplate.content.cloneNode(true);
    const ruleElement = ruleClone.querySelector('.rule-item');
    
    // Add animation class
    ruleElement.classList.add('fade-in');
    
    // Add remove functionality
    const removeBtn = ruleElement.querySelector('.remove-rule-btn');
    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            removeRule(this);
        });
    }
    
    rulesContainer.appendChild(ruleElement);
    
    // Focus on first input
    const firstInput = ruleElement.querySelector('input, select');
    if (firstInput) {
        firstInput.focus();
    }
    
    console.log('Rule added');
}

/**
 * Remove rule from policy form
 */
function removeRule(button) {
    const ruleItem = button.closest('.rule-item');
    if (ruleItem) {
        // Add slide-out animation
        ruleItem.style.transition = 'all 0.3s ease-out';
        ruleItem.style.transform = 'translateX(-100%)';
        ruleItem.style.opacity = '0';
        
        setTimeout(() => {
            ruleItem.remove();
        }, 300);
        
        console.log('Rule removed');
    }
}

/**
 * Show alert message
 */
function showAlert(type, message) {
    const alertContainer = document.getElementById('alertContainer') || 
                         document.querySelector('.container-fluid') ||
                         document.body;
    
    const alertId = 'alert-' + Date.now();
    const alertHtml = `
        <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="ri-${type === 'danger' ? 'error-warning' : type === 'success' ? 'check' : 'information'}-line me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Insert at the top of the container
    alertContainer.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        const alert = document.getElementById(alertId);
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
}

/**
 * Add fade-in animation to cards
 */
function addFadeInAnimation() {
    const cards = document.querySelectorAll('.policy-card, .stats-card, .form-section, .leave-type-card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.3s ease-out';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
}

/**
 * Toggle policy status
 */
function togglePolicyStatus(policyId, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    
    if (confirm(`Are you sure you want to ${newStatus === 'active' ? 'activate' : 'deactivate'} this policy?`)) {
        // Show loading state
        showAlert('info', 'Updating policy status...');
        
        // Make AJAX request to update status
        fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=toggle_policy_status&policyID=${policyId}&status=${newStatus}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', `Policy ${newStatus === 'active' ? 'activated' : 'deactivated'} successfully.`);
                // Reload page or update UI
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showAlert('danger', data.message || 'Failed to update policy status.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'An error occurred while updating the policy status.');
        });
    }
}

/**
 * Toggle leave type status
 */
function toggleLeaveTypeStatus(leaveTypeId, currentStatus) {
    const newStatus = currentStatus === 'Y' ? 'N' : 'Y';
    
    if (confirm(`Are you sure you want to ${newStatus === 'N' ? 'activate' : 'suspend'} this leave type?`)) {
        // Show loading state
        showAlert('info', 'Updating leave type status...');
        
        // Make AJAX request to update status
        fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=toggle_leave_type_status&leaveTypeID=${leaveTypeId}&status=${newStatus}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', `Leave type ${newStatus === 'N' ? 'activated' : 'suspended'} successfully.`);
                // Reload page or update UI
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showAlert('danger', data.message || 'Failed to update leave type status.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', 'An error occurred while updating the leave type status.');
        });
    }
}

/**
 * Export policies data
 */
function exportPolicies(format = 'csv') {
    showAlert('info', `Exporting policies data as ${format.toUpperCase()}...`);
    
    // Create export URL
    const exportUrl = `?action=export_policies&format=${format}`;
    
    // Create temporary link and trigger download
    const link = document.createElement('a');
    link.href = exportUrl;
    link.download = `accumulation_policies.${format}`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    setTimeout(() => {
        showAlert('success', 'Export completed successfully.');
    }, 1000);
}

/**
 * Export leave types data
 */
function exportLeaveTypes(format = 'csv') {
    showAlert('info', `Exporting leave types data as ${format.toUpperCase()}...`);
    
    // Create export URL
    const exportUrl = `?action=export_leave_types&format=${format}`;
    
    // Create temporary link and trigger download
    const link = document.createElement('a');
    link.href = exportUrl;
    link.download = `leave_types.${format}`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    setTimeout(() => {
        showAlert('success', 'Export completed successfully.');
    }, 1000);
}

/**
 * Copy policy to clipboard
 */
function copyPolicyToClipboard(policyId) {
    const policyElement = document.querySelector(`[data-policy-id="${policyId}"]`);
    if (policyElement) {
        const policyText = policyElement.textContent;
        
        navigator.clipboard.writeText(policyText).then(() => {
            showAlert('success', 'Policy details copied to clipboard.');
        }).catch(err => {
            console.error('Failed to copy: ', err);
            showAlert('danger', 'Failed to copy policy details.');
        });
    }
}

/**
 * Copy leave type to clipboard
 */
function copyLeaveTypeToClipboard(leaveTypeId) {
    const leaveTypeElement = document.querySelector(`[data-leave-type-id="${leaveTypeId}"]`);
    if (leaveTypeElement) {
        const leaveTypeText = leaveTypeElement.textContent;
        
        navigator.clipboard.writeText(leaveTypeText).then(() => {
            showAlert('success', 'Leave type details copied to clipboard.');
        }).catch(err => {
            console.error('Failed to copy: ', err);
            showAlert('danger', 'Failed to copy leave type details.');
        });
    }
}

/**
 * Print policy details
 */
function printPolicy(policyId) {
    const policyElement = document.querySelector(`[data-policy-id="${policyId}"]`);
    if (policyElement) {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Policy Details</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .policy-header { border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
                        .policy-section { margin-bottom: 15px; }
                        .policy-label { font-weight: bold; }
                    </style>
                </head>
                <body>
                    ${policyElement.innerHTML}
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }
}

/**
 * Print leave type details
 */
function printLeaveType(leaveTypeId) {
    const leaveTypeElement = document.querySelector(`[data-leave-type-id="${leaveTypeId}"]`);
    if (leaveTypeElement) {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Leave Type Details</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .leave-type-header { border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
                        .leave-type-section { margin-bottom: 15px; }
                        .leave-type-label { font-weight: bold; }
                    </style>
                </head>
                <body>
                    ${leaveTypeElement.innerHTML}
                </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.print();
    }
}

/**
 * Generate leave type code from name
 */
function generateLeaveTypeCode() {
    const nameInput = document.querySelector('[name="leaveTypeName"]');
    const codeInput = document.querySelector('[name="leaveTypeCode"]');
    
    if (nameInput && codeInput && !codeInput.value.trim()) {
        const name = nameInput.value.trim();
        if (name) {
            // Convert to uppercase, replace spaces with underscores, remove special characters
            const code = name.toUpperCase()
                           .replace(/\s+/g, '_')
                           .replace(/[^A-Z0-9_]/g, '')
                           .substring(0, 10);
            codeInput.value = code;
            
            // Validate the generated code
            validateField(codeInput);
        }
    }
}

/**
 * Debounce function to limit function calls
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Show loading state
 */
function showLoading(element) {
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'loading-overlay';
    loadingOverlay.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
    
    element.style.position = 'relative';
    element.appendChild(loadingOverlay);
}

/**
 * Hide loading state
 */
function hideLoading(element) {
    const loadingOverlay = element.querySelector('.loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.remove();
    }
}

// Export functions to global scope for HTML onclick handlers
window.addRule = addRule;
window.removeRule = removeRule;
window.togglePolicyStatus = togglePolicyStatus;
window.toggleLeaveTypeStatus = toggleLeaveTypeStatus;
window.exportPolicies = exportPolicies;
window.exportLeaveTypes = exportLeaveTypes;
window.copyPolicyToClipboard = copyPolicyToClipboard;
window.copyLeaveTypeToClipboard = copyLeaveTypeToClipboard;
window.printPolicy = printPolicy;
window.printLeaveType = printLeaveType;
window.generateLeaveTypeCode = generateLeaveTypeCode;
</script>