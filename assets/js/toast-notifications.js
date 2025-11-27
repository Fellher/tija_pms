/**
 * Global Toast Notification Utility
 *
 * Provides a consistent toast notification system across the application
 * Replaces alert() popups with Bootstrap Toast notifications
 *
 * @package    TIJA_PMS
 * @version    1.0.0
 */

(function() {
    'use strict';

    // Create global toast function
    window.showToast = function(message, type = 'info', duration = null) {
        // Create toast container if it doesn't exist
        let toastContainer = document.querySelector('.toast-container-global');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container-global position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }

        // Icon and color mapping
        const config = {
            success: {
                bg: 'bg-success',
                icon: 'ri-checkbox-circle-line',
                text: 'text-white',
                defaultDuration: 3000
            },
            error: {
                bg: 'bg-danger',
                icon: 'ri-error-warning-line',
                text: 'text-white',
                defaultDuration: 5000
            },
            danger: {
                bg: 'bg-danger',
                icon: 'ri-error-warning-line',
                text: 'text-white',
                defaultDuration: 5000
            },
            warning: {
                bg: 'bg-warning',
                icon: 'ri-alert-line',
                text: 'text-dark',
                defaultDuration: 4000
            },
            info: {
                bg: 'bg-info',
                icon: 'ri-information-line',
                text: 'text-white',
                defaultDuration: 3000
            }
        };

        const toastConfig = config[type] || config.info;
        const toastId = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
        // Ensure delay is always a number
        const toastDuration = duration !== null ? parseInt(duration, 10) || toastConfig.defaultDuration : toastConfig.defaultDuration;

        // Create toast element
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `toast align-items-center ${toastConfig.bg} ${toastConfig.text} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="${toastConfig.icon} me-2"></i>${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

        toastContainer.appendChild(toast);

        // Initialize and show toast
        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            const bsToast = new bootstrap.Toast(toast, {
                autohide: duration !== 0,
                delay: toastDuration
            });
            bsToast.show();

            // Remove toast after it's hidden
            toast.addEventListener('hidden.bs.toast', function() {
                toast.remove();
                // Remove container if empty
                if (toastContainer.children.length === 0) {
                    toastContainer.remove();
                }
            });
        } else {
            // Fallback if Bootstrap is not available
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    toast.remove();
                    // Remove container if empty
                    if (toastContainer.children.length === 0) {
                        toastContainer.remove();
                    }
                }, 300);
            }, toastDuration);
        }
    };

    // Override native alert() function globally (optional - can be disabled)
    // Uncomment the following if you want to completely replace alert() globally
    /*
    window.originalAlert = window.alert;
    window.alert = function(message) {
        // Try to determine type from message content
        let type = 'info';
        const lowerMessage = message.toLowerCase();
        if (lowerMessage.includes('error') || lowerMessage.includes('failed') || lowerMessage.includes('invalid')) {
            type = 'error';
        } else if (lowerMessage.includes('success') || lowerMessage.includes('saved') || lowerMessage.includes('created')) {
            type = 'success';
        } else if (lowerMessage.includes('warning') || lowerMessage.includes('please')) {
            type = 'warning';
        }
        showToast(message, type);
    };
    */
})();

