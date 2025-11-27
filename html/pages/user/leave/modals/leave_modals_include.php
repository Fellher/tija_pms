<?php
/**
 * Leave Management Modals Include
 *
 * This file includes all the leave management modals and their functionality.
 * Include this file in your leave management pages to get access to all modals.
 */

// Ensure required variables are available
if (!isset($employeeDetails)) {
    $employeeDetails = Employee::employees(array('ID'=>$userDetails->ID), true, $DBConn);
}

if (!isset($orgDataID)) {
    $orgDataID = $employeeDetails->orgDataID;
}

if (!isset($entityID)) {
    $entityID = $employeeDetails->entityID;
}

if (!isset($leaveTypes)) {
    $leaveTypes = Leave::leave_types(array('Lapsed'=>'N'), false, $DBConn);
}

if (!isset($leaveEntitlements)) {
    $leaveEntitlements = Leave::leave_entitlements(array('Suspended'=>'N', 'entityID'=>$entityID), false, $DBConn);
}

if (!isset($myLeaveApplications)) {
    $myLeaveApplications = Leave::leave_applications_full(array('Suspended'=>'N', 'employeeID'=>$userDetails->ID), false, $DBConn);
}

// Get global holidays for employee's jurisdiction (if not already set)
if (!isset($globalHolidays)) {
    $employeeCountry = $employeeDetails->country ?? 'Kenya';
    $employeeState = $employeeDetails->state ?? null;
    $globalHolidays = Leave::get_global_holidays($employeeCountry, $employeeState, $DBConn);
}
?>

<!-- Include all leave management modals -->
<?php include 'apply_leave_modal.php'; ?>
<?php include 'apply_leave_modal_steps.php'; ?>
<?php include 'apply_leave_modal_scripts.php'; ?>
<?php include 'leave_calendar_modal.php'; ?>
<?php include 'approval_workflow_modal.php'; ?>

<!-- Global Leave Management Functions -->
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

<!-- Additional CSS for global leave management components -->
<style>
/* Global leave management styles */
.leave-status-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.leave-type-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(var(--bs-primary-rgb), 0.1);
    color: var(--bs-primary);
    font-size: 1.25rem;
}

.leave-summary-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.leave-summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.leave-action-btn {
    min-width: 120px;
}

.leave-calendar-day {
    min-height: 100px;
    position: relative;
}

.leave-calendar-event {
    font-size: 0.75rem;
    padding: 0.125rem 0.25rem;
    margin-bottom: 0.125rem;
    border-radius: 0.25rem;
    cursor: pointer;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.leave-calendar-event:hover {
    transform: scale(1.02);
    z-index: 10;
    position: relative;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .leave-calendar-day {
        min-height: 80px;
    }

    .leave-calendar-event {
        font-size: 0.625rem;
    }

    .leave-action-btn {
        min-width: 100px;
        font-size: 0.875rem;
    }
}

/* Animation classes */
.fade-in {
    animation: fadeIn 0.3s ease-in;
}

.slide-in {
    animation: slideIn 0.3s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from { transform: translateY(-10px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* Loading states */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.loading-spinner {
    width: 2rem;
    height: 2rem;
    border: 0.25rem solid #f3f3f3;
    border-top: 0.25rem solid var(--bs-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
