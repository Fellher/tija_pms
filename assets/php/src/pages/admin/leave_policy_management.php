/**
 * Leave Policy Management Page JavaScript
 * 
 * Handles all interactive functionality for the leave policy management page
 */

// Global variables
let leaveTypeForm = null;
let searchInput = null;
let statusFilter = null;
let leaveTypesContainer = null;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializePage();
});

/**
 * Initialize page functionality
 */
function initializePage() {
    // Get DOM elements
    leaveTypeForm = document.getElementById('leaveTypeForm');
    searchInput = document.getElementById('searchInput');
    statusFilter = document.getElementById('statusFilter');
    leaveTypesContainer = document.getElementById('leaveTypesContainer');
    
    // Initialize components
    initializeAlerts();
    initializeFormValidation();
    initializeDeleteConfirmations();
    initializeTooltips();
    initializeModals();
    initializeSearch();
    
    // Add fade-in animation to cards
    addFadeInAnimation();
    
    console.log('Leave Policy Management page initialized');
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
    if (!leaveTypeForm) return;
    
    // Add real-time validation
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
    
    // Form submission validation
    leaveTypeForm.addEventListener('submit', function(e) {
        if (!validateLeaveTypeForm()) {
            e.preventDefault();
            showFormErrors();
        }
    });
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
    const invalidFields = leaveTypeForm.querySelectorAll('.is-invalid');
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
        if (e.target.classList.contains('delete-leave-type') || 
            e.target.closest('.delete-leave-type')) {
            
            e.preventDefault();
            
            const leaveTypeName = e.target.getAttribute('data-name') || 
                                e.target.closest('[data-name]')?.getAttribute('data-name') || 
                                'this leave type';
            
            if (confirm(`Are you sure you want to delete ${leaveTypeName}? This action cannot be undone.`)) {
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
        searchInput.addEventListener('input', debounce(filterLeaveTypes, 300));
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', filterLeaveTypes);
    }
}

/**
 * Filter leave types based on search and status
 */
function filterLeaveTypes() {
    const searchTerm = searchInput?.value.toLowerCase() || '';
    const statusValue = statusFilter?.value || '';
    
    const leaveTypeCards = document.querySelectorAll('.leave-type-card');
    
    leaveTypeCards.forEach(card => {
        const leaveTypeName = card.querySelector('.leave-type-title')?.textContent.toLowerCase() || '';
        const leaveTypeCode = card.querySelector('.leave-type-code')?.textContent.toLowerCase() || '';
        const leaveTypeDescription = card.querySelector('.leave-type-description')?.textContent.toLowerCase() || '';
        const statusBadge = card.querySelector('.status-badge')?.textContent.toLowerCase() || '';
        
        const matchesSearch = leaveTypeName.includes(searchTerm) || 
                            leaveTypeCode.includes(searchTerm) || 
                            leaveTypeDescription.includes(searchTerm);
        const matchesStatus = !statusValue || statusBadge.includes(statusValue);
        
        if (matchesSearch && matchesStatus) {
            card.style.display = 'block';
            card.classList.add('fade-in');
        } else {
            card.style.display = 'none';
        }
    });
    
    // Show/hide empty state
    const visibleCards = Array.from(leaveTypeCards).filter(card => card.style.display !== 'none');
    const emptyState = document.getElementById('emptyState');
    if (emptyState) {
        emptyState.style.display = visibleCards.length === 0 ? 'block' : 'none';
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
    const cards = document.querySelectorAll('.leave-type-card, .stats-card, .form-section');
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
 * Toggle leave type status
 */
function toggleLeaveTypeStatus(leaveTypeID, currentStatus) {
    const newStatus = currentStatus === 'Y' ? 'N' : 'Y';
    const action = newStatus === 'N' ? 'activate' : 'suspend';
    
    if (confirm(`Are you sure you want to ${action} this leave type?`)) {
        // Show loading state
        showAlert('info', `${action.charAt(0).toUpperCase() + action.slice(1)}ing leave type...`);
        
        // Make AJAX request to update status
        fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=toggle_status&leaveTypeID=${leaveTypeID}&status=${newStatus}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', `Leave type ${action}d successfully.`);
                // Reload page or update UI
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showAlert('danger', data.message || `Failed to ${action} leave type.`);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('danger', `An error occurred while ${action}ing the leave type.`);
        });
    }
}

/**
 * Export leave types data
 */
function exportLeaveTypes(format = 'csv') {
    showAlert('info', `Exporting leave types data as ${format.toUpperCase()}...`);
    
    // Create export URL
    const exportUrl = `?action=export&format=${format}`;
    
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
 * Copy leave type to clipboard
 */
function copyLeaveTypeToClipboard(leaveTypeID) {
    const leaveTypeElement = document.querySelector(`[data-leave-type-id="${leaveTypeID}"]`);
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
 * Print leave type details
 */
function printLeaveType(leaveTypeID) {
    const leaveTypeElement = document.querySelector(`[data-leave-type-id="${leaveTypeID}"]`);
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
 * Auto-generate code when name changes
 */
function initializeAutoCodeGeneration() {
    const nameInput = document.querySelector('[name="leaveTypeName"]');
    const codeInput = document.querySelector('[name="leaveTypeCode"]');
    
    if (nameInput && codeInput) {
        nameInput.addEventListener('input', function() {
            if (!codeInput.value.trim()) {
                generateLeaveTypeCode();
            }
        });
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

// Initialize auto-code generation when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeAutoCodeGeneration();
});

// Export functions to global scope for HTML onclick handlers
window.toggleLeaveTypeStatus = toggleLeaveTypeStatus;
window.exportLeaveTypes = exportLeaveTypes;
window.copyLeaveTypeToClipboard = copyLeaveTypeToClipboard;
window.printLeaveType = printLeaveType;
window.generateLeaveTypeCode = generateLeaveTypeCode;
