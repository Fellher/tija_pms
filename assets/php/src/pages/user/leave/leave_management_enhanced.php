<?php
// This is a new sub-menu reporting file for 'leave_management_enhanced.php'
// Add your content here.
?>

<script>
    // Global functions for leave management
    function openApplyLeaveModal(leaveTypeId = null) {
        const modalElement = document.getElementById('applyLeaveModal');
        if (!modalElement) {
            return;
        }

        if (leaveTypeId) {
            if (window.LeaveApplyModal && typeof window.LeaveApplyModal.preselectLeaveType === 'function') {
                window.LeaveApplyModal.preselectLeaveType(leaveTypeId);
            } else {
                modalElement.dataset.pendingLeaveTypeId = leaveTypeId;
            }
        }

        const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

        // Listen for modal shown event to initialize date pickers with pre-filled dates
        const handleShown = () => {
            modalElement.removeEventListener('shown.bs.modal', handleShown);
            // Initialize date pickers will handle pre-filled dates from sessionStorage
            if (typeof initializeDatePickers === 'function') {
                setTimeout(() => {
                    initializeDatePickers();
                }, 100);
            }
        };
        modalElement.addEventListener('shown.bs.modal', handleShown, { once: true });

        modal.show();

        if (leaveTypeId && window.LeaveApplyModal && typeof window.LeaveApplyModal.preselectLeaveType === 'function') {
            window.LeaveApplyModal.preselectLeaveType(leaveTypeId);
        }
    }

    // Make function globally available
    if (typeof window !== 'undefined') {
        window.openApplyLeaveModal = openApplyLeaveModal;
    }

    function openLeaveCalendarModal() {
        const modal = new bootstrap.Modal(document.getElementById('leaveCalendarModal'));
        modal.show();
    }

    function openApprovalWorkflowModal(leaveId = null, action = null) {
        const modalEl = document.getElementById('approvalWorkflowModal');
        if (!modalEl) {
            console.warn('Approval workflow modal is not available on this page.');
            return;
        }

        console.log('[Leave] Opening approval workflow modal', { leaveId, action });

        if (leaveId) {
            window.currentLeaveId = leaveId;
        } else {
            delete window.currentLeaveId;
        }

        if (action) {
            modalEl.dataset.pendingApprovalAction = action;
        } else {
            delete modalEl.dataset.pendingApprovalAction;
        }

        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        const isVisible = modalEl.classList.contains('show');

        if (typeof loadApprovals === 'function' && isVisible && !leaveId) {
            try {
                loadApprovals();
            } catch (error) {
                console.error('Failed to refresh approval list:', error);
            }
        }

        if (leaveId && typeof showApprovalDetails === 'function') {
            if (isVisible) {
                showApprovalDetails(leaveId);
            } else {
                const handleShown = () => {
                    modalEl.removeEventListener('shown.bs.modal', handleShown);
                    showApprovalDetails(leaveId);
                };
                modalEl.addEventListener('shown.bs.modal', handleShown);
            }
        }

        modalInstance.show();
    }

    if (typeof window !== 'undefined') {
        window.__leaveBaseOpenApprovalWorkflowModal = openApprovalWorkflowModal;
    }

    function refreshLeaveData() {
        // This function can be called to refresh leave data after operations
        if (typeof window.refreshLeaveDashboard === 'function') {
            window.refreshLeaveDashboard();
        }

        // Trigger custom event for other components to listen
        window.dispatchEvent(new CustomEvent('leaveDataUpdated'));
    }

    function showToast(type, title, message) {
        // Create toast element
        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>${title}</strong><br>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        // Create toast container if it doesn't exist
        let toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toastContainer';
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }

        // Add toast to container
        toastContainer.insertAdjacentHTML('beforeend', toastHtml);

        // Show toast
        const toastElement = toastContainer.lastElementChild;
        const toast = new bootstrap.Toast(toastElement);
        toast.show();

        // Remove toast element after it's hidden
        toastElement.addEventListener('hidden.bs.toast', function() {
            toastElement.remove();
        });
    }

    // Utility functions
    function formatDate(date) {
        if (typeof date === 'string') {
            date = new Date(date);
        }
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    function formatDateTime(date) {
        if (typeof date === 'string') {
            date = new Date(date);
        }
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function calculateDaysBetween(startDate, endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        const timeDiff = end.getTime() - start.getTime();
        return Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;
    }

    function isWeekend(date) {
        const day = new Date(date).getDay();
        return day === 0 || day === 6;
    }

    function getLeaveStatusColor(status) {
        switch (status.toLowerCase()) {
            case 'approved': return 'success';
            case 'pending': return 'warning';
            case 'rejected': return 'danger';
            case 'cancelled': return 'secondary';
            default: return 'primary';
        }
    }

    function getLeaveStatusIcon(status) {
        switch (status.toLowerCase()) {
            case 'approved': return 'ri-check-line';
            case 'pending': return 'ri-time-line';
            case 'rejected': return 'ri-close-line';
            case 'cancelled': return 'ri-close-circle-line';
            default: return 'ri-question-line';
        }
    }

    // Event listeners for global functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + L to open leave calendar
            if ((e.ctrlKey || e.metaKey) && e.key === 'l') {
                e.preventDefault();
                openLeaveCalendarModal();
            }

            // Ctrl/Cmd + Shift + L to apply leave
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'L') {
                e.preventDefault();
                openApplyLeaveModal();
            }

            // Ctrl/Cmd + Shift + A to open approvals
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'A') {
                e.preventDefault();
                openApprovalWorkflowModal();
            }
        });

        // Add tooltips to all elements with data-bs-toggle="tooltip"
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Add popovers to all elements with data-bs-toggle="popover"
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    });

    // Export functions for use in other scripts
    window.LeaveManagement = {
        openApplyLeaveModal,
        openLeaveCalendarModal,
        openApprovalWorkflowModal,
        refreshLeaveData,
        showToast,
        formatDate,
        formatDateTime,
        calculateDaysBetween,
        isWeekend,
        getLeaveStatusColor,
        getLeaveStatusIcon
    };
</script>