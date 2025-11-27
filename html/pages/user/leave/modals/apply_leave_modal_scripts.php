<!--
    apply_leave_modal_scripts.php
    ---------------------------------
    Styles and behaviour for the Apply Leave modal experience.
    This file is intentionally self-contained (style + script) because the modal
    can be included on multiple host pages and needs predictable dependencies.
-->

<style>
    /* Apply Leave Modal Styles */
    .leave-type-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
        cursor: pointer;
    }

    .leave-type-card:hover {
        border-color: var(--bs-primary);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .leave-type-card.selected {
        border-color: var(--bs-primary);
        background-color: rgba(var(--bs-primary-rgb), 0.05);
    }

    .leave-type-card[data-disabled="true"] {
        opacity: 0.6;
        cursor: not-allowed;
        background-color: #f8f9fa;
    }

    .leave-type-card[data-disabled="true"]:hover {
        transform: none;
        box-shadow: none;
        border-color: transparent;
    }

    /* Progress Indicator */
    .progress-indicator {
        flex-shrink: 0;
        border-bottom: 1px solid #dee2e6;
    }

    .step-indicator {
        display: flex;
        justify-content: space-between;
        width: 100%;
        max-width: 500px;
    }

    .step-indicator .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 0.5rem;
        color: #6c757d;
        font-size: 0.875rem;
        flex: 1;
        text-align: center;
    }

    .step-indicator .step.active {
        color: var(--bs-primary);
    }

    .step-indicator .step i {
        font-size: 1.25rem;
        margin-bottom: 0.25rem;
    }

    /* Form Steps */
    .form-step {
        display: none;
        min-height: 400px;
    }

    .form-step.active {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    /* Ensure step 1 is always visible when active */
    #step1.active {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    /* Modal Layout */
    .modal-xl .modal-content {
        height: auto;
        min-height: 500px;
    }

    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
        position: relative;
    }

    #applyLeaveForm {
        min-height: 300px;
        position: relative;
    }

    /* Approval Workflow Preview */
    .approval-workflow-preview {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 0.5rem;
    }

    .approval-step {
        display: flex;
        align-items: center;
        flex: 1;
    }

    .approval-icon {
        width: 40px;
        height: 40px;
        background: var(--bs-primary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 0.75rem;
    }

    .approval-info {
        flex: 1;
    }

    .approval-title {
        font-weight: 600;
        font-size: 0.875rem;
    }

    .approval-name {
        color: #6c757d;
        font-size: 0.75rem;
    }

    .approval-arrow {
        color: #6c757d;
        margin: 0 0.5rem;
    }

    /* Utility Classes */
    .cursor-pointer {
        cursor: pointer;
    }

    .spin {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>

<!-- Shared leave UI helpers (wizard navigation, file removal, etc.) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="<?= $base ?>assets/js/src/leave/workflow_controls.js"></script>
<script>
/**
 * Apply Leave Modal JavaScript
 * ---------------------------------
 * Provides the client-side behaviour for the multi-step leave application.
 * Key responsibilities:
 *  - Step navigation and validation.
 *  - Leave balance calculations and summary updates.
 *  - Fetching approval workflow preview data.
 *  - Submitting the leave application via fetch.
 */

(() => {
// Wizard state is kept module-wide so reset logic can run when the modal opens.
let currentStep = 1;
let selectedLeaveType = null;
let selectedEntitlement = null;
let pendingLeaveTypeId = null;
let handoverState = {
    required: false,
    policy: null,
    items: []
};
let handoverColleagues = [];

// Global holidays Set - populated from PHP or parent page
let GLOBAL_HOLIDAY_DATES = new Set();

// Initialize holidays from PHP if available
<?php
$modalHolidayDates = [];
if (isset($globalHolidays) && is_array($globalHolidays)) {
    foreach ($globalHolidays as $holiday) {
        if (is_array($holiday) && isset($holiday['holidayDate'])) {
            $modalHolidayDates[] = date('Y-m-d', strtotime($holiday['holidayDate']));
        } elseif (is_object($holiday) && isset($holiday->holidayDate)) {
            $modalHolidayDates[] = date('Y-m-d', strtotime($holiday->holidayDate));
        }
    }
}
$modalHolidayDates = array_values(array_unique($modalHolidayDates));
echo "const __APPLY_MODAL_HOLIDAY_DATA = " . json_encode($modalHolidayDates) . ";\n";
echo "GLOBAL_HOLIDAY_DATES = new Set(Array.isArray(__APPLY_MODAL_HOLIDAY_DATA) ? __APPLY_MODAL_HOLIDAY_DATA : []);\n";
echo "console.info('[LEAVE DEBUG] Holidays loaded (apply modal):', Array.from(GLOBAL_HOLIDAY_DATES));\n";
?>

// Helper function to parse input date (handles YYYY-MM-DD strings correctly)
function parseInputDate(dateInput) {
    if (!dateInput) return null;
    if (dateInput instanceof Date) {
        return new Date(dateInput.getFullYear(), dateInput.getMonth(), dateInput.getDate());
    }
    if (typeof dateInput === 'string') {
        const parts = dateInput.split('-');
        if (parts.length === 3) {
            const year = parseInt(parts[0], 10);
            const month = parseInt(parts[1], 10) - 1; // Month is 0-indexed
            const day = parseInt(parts[2], 10);
            return new Date(year, month, day);
        }
    }
    return null;
}

// Helper function to format date as YYYY-MM-DD key
function formatDateKey(date) {
    if (!date) return '';
    const d = date instanceof Date ? date : parseInputDate(date);
    if (!d) return '';
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

// Check if date is a holiday
function isHoliday(date) {
    if (!GLOBAL_HOLIDAY_DATES || GLOBAL_HOLIDAY_DATES.size === 0 || !date) {
        return false;
    }
    const key = formatDateKey(date);
    return GLOBAL_HOLIDAY_DATES.has(key);
}

function escapeHtml(input) {
    if (typeof input !== 'string') {
        return '';
    }
    return input
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function getModalRoot() {
    return document.getElementById('applyLeaveModal');
}

function queryModal(selector) {
    const root = getModalRoot();
    return root ? root.querySelector(selector) : null;
}

function queryAllModal(selector) {
    const root = getModalRoot();
    return root ? Array.from(root.querySelectorAll(selector)) : [];
}

function getStepElement(stepNumber) {
    return queryModal(`#step${stepNumber}`);
}

function forceStepVisibility(stepElement) {
    if (!stepElement) {
        return;
    }
    stepElement.style.setProperty('display', 'block', 'important');
    stepElement.style.setProperty('visibility', 'visible', 'important');
    stepElement.style.setProperty('opacity', '1', 'important');
}

function clearStepVisibility(stepElement) {
    if (!stepElement) {
        return;
    }
    stepElement.style.removeProperty('display');
    stepElement.style.removeProperty('visibility');
    stepElement.style.removeProperty('opacity');
    stepElement.style.removeProperty('background-color');
}

function setPendingLeaveType(leaveTypeId) {
    const modalRoot = getModalRoot();
    if (leaveTypeId) {
        pendingLeaveTypeId = String(leaveTypeId);
        if (modalRoot) {
            modalRoot.dataset.pendingLeaveTypeId = pendingLeaveTypeId;
        }
    } else {
        pendingLeaveTypeId = null;
        if (modalRoot) {
            delete modalRoot.dataset.pendingLeaveTypeId;
        }
    }
}

function attemptPendingLeaveTypeSelection() {
    if (!pendingLeaveTypeId) {
        return false;
    }

    goToStep(1);

    const card = queryModal(`.leave-type-card[data-leave-type-id="${pendingLeaveTypeId}"]`);
    if (card) {
        selectLeaveType(card);
        setPendingLeaveType(null);
        return true;
    }

    return false;
}

function queueLeaveTypePreselection(leaveTypeId) {
    if (!leaveTypeId) {
        return;
    }

    setPendingLeaveType(leaveTypeId);

    const modalElement = getModalRoot();
    if (modalElement && modalElement.classList.contains('show')) {
        setTimeout(() => {
            attemptPendingLeaveTypeSelection();
        }, 120);
    }
}

function cleanupModalArtifacts() {
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('padding-right');
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
}

function initializeApplyLeaveModal() {
    const modalRoot = getModalRoot();
    if (!modalRoot) {
        return;
    }

    // Wire up shared helpers (no inline handlers in the markup).
    if (window.leaveUI) {
        if (typeof window.leaveUI.bindWizardNavigation === 'function') {
            window.leaveUI.bindWizardNavigation({
                root: modalRoot,
                onNext: () => nextStep(),
                onPrev: () => prevStep(),
                onSubmit: () => submitLeaveApplication()
            });
        }

        if (typeof window.leaveUI.bindDebugButtons === 'function') {
            window.leaveUI.bindDebugButtons({
                root: modalRoot,
                handler: () => debugFormVisibility()
            });
        }
    }

    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    const startDateInput = modalRoot.querySelector('#startDate');
    const endDateInput = modalRoot.querySelector('#endDate');

    if (startDateInput && startDateInput.type === 'date') {
        startDateInput.setAttribute('min', today);
    }
    if (endDateInput && endDateInput.type === 'date') {
        endDateInput.setAttribute('min', today);
    }

    // Check for pre-filled dates from calendar (sessionStorage)
    const prefillStartDate = sessionStorage.getItem('prefillStartDate');
    const prefillEndDate = sessionStorage.getItem('prefillEndDate');

    if (prefillStartDate && startDateInput) {
        // Set start date if provided
        if (startDateInput._flatpickr) {
            startDateInput._flatpickr.setDate(prefillStartDate, false);
        } else {
            startDateInput.value = prefillStartDate;
        }

        // Clear from sessionStorage after use
        sessionStorage.removeItem('prefillStartDate');

        // If end date is also provided, set it
        if (prefillEndDate && endDateInput) {
            if (endDateInput._flatpickr) {
                endDateInput._flatpickr.setDate(prefillEndDate, false);
            } else {
                endDateInput.value = prefillEndDate;
            }
            sessionStorage.removeItem('prefillEndDate');
        }

        // Trigger date validation if dates are set
        if (typeof validateDates === 'function') {
            setTimeout(() => {
                validateDates();
            }, 100);
        }
    }

    // Leave type selection
    modalRoot.querySelectorAll('.leave-type-card').forEach(card => {
        if (card.dataset.leaveTypeBound === 'true') {
            return;
        }
        card.addEventListener('click', function() {
            selectLeaveType(this);
        });
        card.dataset.leaveTypeBound = 'true';
    });

    // Half day checkbox
    const halfDayCheckbox = modalRoot.querySelector('#halfDayLeave');
    if (halfDayCheckbox && halfDayCheckbox.dataset.leaveUiBound !== 'true') {
        halfDayCheckbox.addEventListener('change', function() {
            const halfDayOptions = modalRoot.querySelector('#halfDayOptions');
            if (halfDayOptions) {
                halfDayOptions.style.display = this.checked ? 'block' : 'none';
            }
            updateSummary();
        });
        halfDayCheckbox.dataset.leaveUiBound = 'true';
    }

    // Date validation
    if (startDateInput && startDateInput.dataset.leaveUiBound !== 'true') {
        startDateInput.addEventListener('change', function() {
            // Auto-set end date to start date for single day leave
            if (endDateInput && !endDateInput.value) {
                endDateInput.value = this.value;
            }
            validateDates();
        });
        startDateInput.dataset.leaveUiBound = 'true';
    }
    if (endDateInput && endDateInput.dataset.leaveUiBound !== 'true') {
        endDateInput.addEventListener('change', validateDates);
        endDateInput.dataset.leaveUiBound = 'true';
    }

    initializeHandoverModule();
    loadHandoverColleagues();

    initializeDatePickers();

    // Load approval workflow
    loadApprovalWorkflow();
}

function initializeDatePickers() {
    if (typeof flatpickr !== 'function') {
        console.warn('flatpickr is not available. Date pickers will fall back to native inputs.');
        return;
    }

    const startDateInput = queryModal('#startDate');
    const endDateInput = queryModal('#endDate');

    // Check for pre-filled dates from calendar
    const prefillStartDate = sessionStorage.getItem('prefillStartDate');
    const prefillEndDate = sessionStorage.getItem('prefillEndDate');
    const initialStartDate = prefillStartDate || null;
    const initialEndDate = prefillEndDate || null;

    if (startDateInput && !startDateInput._flatpickr) {
        startDateInput.type = 'text';
        startDateInput.classList.add('date-picker');

        const startPickerConfig = {
            dateFormat: 'Y-m-d',
            allowInput: true,
            minDate: 'today',
            onChange: (selectedDates, dateStr) => {
                const endPicker = endDateInput?._flatpickr;
                if (endPicker) {
                    endPicker.set('minDate', dateStr || 'today');
                }
                // Mirror existing behaviour
                if (endDateInput && !endDateInput.value) {
                    endDateInput.value = dateStr;
                    if (endPicker) {
                        endPicker.setDate(dateStr, false);
                    }
                }
                validateDates();
                updateSummary();
            }
        };

        // Set initial date if provided
        if (initialStartDate) {
            startPickerConfig.defaultDate = initialStartDate;
            sessionStorage.removeItem('prefillStartDate');
        }

        flatpickr(startDateInput, startPickerConfig);
    } else if (startDateInput && startDateInput._flatpickr && initialStartDate) {
        // Update existing picker
        startDateInput._flatpickr.setDate(initialStartDate, false);
        sessionStorage.removeItem('prefillStartDate');
    }

    if (endDateInput && !endDateInput._flatpickr) {
        endDateInput.type = 'text';
        endDateInput.classList.add('date-picker');

        const endPickerConfig = {
            dateFormat: 'Y-m-d',
            allowInput: true,
            minDate: startDateInput?._flatpickr?.selectedDates?.[0] || initialStartDate || 'today',
            onChange: () => {
                validateDates();
                updateSummary();
            }
        };

        // Set initial date if provided
        if (initialEndDate) {
            endPickerConfig.defaultDate = initialEndDate;
            sessionStorage.removeItem('prefillEndDate');
        }

        flatpickr(endDateInput, endPickerConfig);
    } else if (endDateInput && endDateInput._flatpickr && initialEndDate) {
        // Update existing picker
        endDateInput._flatpickr.setDate(initialEndDate, false);
        sessionStorage.removeItem('prefillEndDate');
    }

    // If dates were pre-filled, trigger validation
    if (initialStartDate && typeof validateDates === 'function') {
        setTimeout(() => {
            validateDates();
        }, 100);
    }
}

function resetDatePickers() {
    const startPicker = queryModal('#startDate')?._flatpickr;
    const endPicker = queryModal('#endDate')?._flatpickr;

    if (startPicker) {
        startPicker.clear();
        startPicker.set('minDate', 'today');
    }
    if (endPicker) {
        endPicker.clear();
        endPicker.set('minDate', 'today');
    }
}

function selectLeaveType(card) {
    // Check if card is disabled
    if (card.dataset.disabled === 'true') {
        showAlert('This leave type has no available balance. Please select another leave type.', 'warning');
        return;
    }

    // Remove previous selection
    queryAllModal('.leave-type-card').forEach(c => {
        c.classList.remove('selected');
    });

    // Add selection
    card.classList.add('selected');

    // Store selection
    selectedLeaveType = {
        id: card.dataset.leaveTypeId,
        name: card.querySelector('.card-title').textContent,
        entitlementId: card.dataset.entitlementId,
        maxDays: parseInt(card.dataset.maxDays) || 0,
        availableDays: parseInt(card.dataset.availableDays) || 0
    };

    selectedEntitlement = {
        id: card.dataset.entitlementId,
        maxDays: parseInt(card.dataset.maxDays) || 0
    };

    // Update form fields
    const leaveTypeIdField = queryModal('#leaveTypeId');
    const leaveEntitlementField = queryModal('#leaveEntitlementId');
    if (leaveTypeIdField) {
        leaveTypeIdField.value = selectedLeaveType.id;
    }
    if (leaveEntitlementField) {
        leaveEntitlementField.value = selectedLeaveType.entitlementId;
    }

    // Enable next button
    const nextButton = queryModal('#nextStepBtn');
    if (nextButton) {
        nextButton.disabled = false;
    }

    // Show success message
    showAlert(`Selected ${selectedLeaveType.name}. Available: ${selectedLeaveType.availableDays} days`, 'success');

    evaluateHandoverRequirement();
    updateSummary();
}

function validateDates() {
    const startDateInput = queryModal('#startDate');
    const endDateInput = queryModal('#endDate');
    const startDate = startDateInput ? startDateInput.value : '';
    const endDate = endDateInput ? endDateInput.value : '';

    if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);

        if (end < start) {
            showAlert('End date cannot be before start date', 'error');
            return false;
        }

        // Check if dates are in the future
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (start < today) {
            showAlert('Leave start date cannot be in the past', 'error');
            return false;
        }

        // Calculate days
        const totalDays = calculateLeaveDays(startDate, endDate);

        // Check against available days
        if (selectedLeaveType && totalDays > selectedLeaveType.availableDays) {
            showAlert(`You only have ${selectedLeaveType.availableDays} days available for ${selectedLeaveType.name}. Please reduce your leave period.`, 'error');
            return false;
        }

        // Check against max days per application
        if (selectedLeaveType && selectedLeaveType.maxDays > 0 && totalDays > selectedLeaveType.maxDays) {
            showAlert(`Maximum ${selectedLeaveType.maxDays} days allowed per application for ${selectedLeaveType.name}. Please reduce your leave period.`, 'error');
            return false;
        }

        updateSummary();
        evaluateHandoverRequirement();
        return true;
    }

    return false;
}

function calculateLeaveDays(startDate, endDate) {
    console.log('[LEAVE DEBUG] calculateLeaveDays called', { startDate, endDate });

    const start = parseInputDate(startDate);
    const end = parseInputDate(endDate);

    if (!start || !end) {
        console.warn('[LEAVE DEBUG] Invalid date parsing', { start, end, startDate, endDate });
        return 0;
    }

    // Calculate total calendar days (for display only)
    const timeDiff = end.getTime() - start.getTime();
    const totalDays = Math.ceil(timeDiff / (1000 * 3600 * 24)) + 1;

    // Calculate working days (excluding weekends and holidays)
    let workingDays = 0;
    let weekendDays = 0;
    let holidayDays = 0;

    const current = new Date(start.getFullYear(), start.getMonth(), start.getDate());
    const endDateObj = new Date(end.getFullYear(), end.getMonth(), end.getDate());

    console.log('[LEAVE DEBUG] Holiday dates available:', GLOBAL_HOLIDAY_DATES.size, 'holidays');
    console.log('[LEAVE DEBUG] Holiday dates:', Array.from(GLOBAL_HOLIDAY_DATES));

    while (current <= endDateObj) {
        const dayOfWeek = current.getDay();
        const dateKey = formatDateKey(current);
        const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
        const isHolidayDate = isHoliday(current);

        console.log(`[LEAVE DEBUG] Date: ${dateKey}, Day: ${['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][dayOfWeek]}, Weekend: ${isWeekend}, Holiday: ${isHolidayDate}`);

        if (isWeekend) {
            weekendDays++;
            console.log(`[LEAVE DEBUG] ${dateKey} - Skipped (weekend)`);
        } else if (isHolidayDate) {
            holidayDays++;
            console.log(`[LEAVE DEBUG] ${dateKey} - Skipped (holiday)`);
        } else {
            workingDays++;
            console.log(`[LEAVE DEBUG] ${dateKey} - Counted as working day`);
    }

        current.setDate(current.getDate() + 1);
    }

    console.log('[LEAVE DEBUG] Summary:', {
        totalDays,
        workingDays,
        weekendDays,
        holidayDays,
        effectiveLeaveDays: workingDays
    });

    // Update display
    const totalDaysDisplay = queryModal('#totalDays');
    const workingDaysDisplay = queryModal('#workingDays');
    const weekendDaysDisplay = queryModal('#weekendDays');
    const holidayDaysDisplay = queryModal('#holidayDays');

    if (totalDaysDisplay) {
        totalDaysDisplay.textContent = totalDays;
    }
    if (workingDaysDisplay) {
        workingDaysDisplay.textContent = workingDays;
    }
    if (weekendDaysDisplay) {
        weekendDaysDisplay.textContent = weekendDays;
    }
    if (holidayDaysDisplay) {
        holidayDaysDisplay.textContent = holidayDays;
    }

    // Update hidden input with working days (not total days)
    const workingDaysInput = queryModal('#workingDaysInput');
    if (workingDaysInput) {
        workingDaysInput.value = workingDays;
    }

    console.log('[LEAVE DEBUG] Final working days returned:', workingDays);

    // Return working days (excluding weekends and holidays) - this is what should be used for leave calculation
    return workingDays;
}

function goToStep(stepNumber) {
    const targetStep = parseInt(stepNumber, 10);
    if (!targetStep || targetStep < 1 || targetStep > 5) {
        return;
    }

    if (currentStep === targetStep) {
        const currentStepElement = getStepElement(currentStep);
        if (currentStepElement) {
            currentStepElement.classList.add('active');
            forceStepVisibility(currentStepElement);
        }
        updateProgressIndicator();
        updateNavigationButtons();
        return;
    }

    const currentStepElement = getStepElement(currentStep);
    if (currentStepElement) {
        currentStepElement.classList.remove('active');
        clearStepVisibility(currentStepElement);
    }

    currentStep = targetStep;

    const targetStepElement = getStepElement(currentStep);
    if (targetStepElement) {
        targetStepElement.classList.add('active');
        forceStepVisibility(targetStepElement);
    }

    updateProgressIndicator();
    updateNavigationButtons();
}

function nextStep() {
    if (currentStep < 5) {


        // Validate current step
        if (!validateCurrentStep()) {
            showAlert('Please complete all previous steps', 'error');
            return;
        }


        // Clear any existing alerts
        clearAlerts();

        console.log(`currentStep: step${currentStep} cleared alerts`);

        // Hide current step
        const currentStepElement = getStepElement(currentStep);
        if (currentStepElement) {
            currentStepElement.classList.remove('active');
            clearStepVisibility(currentStepElement);
        }

        console.log(`currentStep: step${currentStep} removed active`);


        // Show next step
        currentStep++;
        const nextStepElement = getStepElement(currentStep);
        if (nextStepElement) {
            nextStepElement.classList.add('active');
            forceStepVisibility(nextStepElement);
        }

        console.log(`currentStep: step${currentStep} added active`);

        // Update progress indicator
        updateProgressIndicator();


        // Update navigation buttons
        updateNavigationButtons();

        // Update summary if on final step
        if (currentStep === 5) {
            updateReviewSummary();
        }
    }
}

function prevStep() {
    if (currentStep > 1) {
        // Hide current step
        const currentStepElement = getStepElement(currentStep);
        if (currentStepElement) {
            currentStepElement.classList.remove('active');
            clearStepVisibility(currentStepElement);
        }

        // Show previous step
        currentStep--;
        const previousStepElement = getStepElement(currentStep);
        if (previousStepElement) {
            previousStepElement.classList.add('active');
            forceStepVisibility(previousStepElement);
        }

        // Update progress indicator
        updateProgressIndicator();

        // Update navigation buttons
        updateNavigationButtons();
    }
}

function validateCurrentStep() {
    switch (currentStep) {
        case 1:
            if (!selectedLeaveType) {
                showAlert('Please select a leave type', 'error');
                return false;
            }
            if (selectedLeaveType.availableDays <= 0) {
                showAlert('Selected leave type has no available balance', 'error');
                return false;
            }
            break;
        case 2:
            if (!validateDates()) {
                return false;
            }
            // Additional validation for half day
            const halfDayCheckbox = queryModal('#halfDayLeave');
            const halfDayChecked = halfDayCheckbox ? halfDayCheckbox.checked : false;
            if (halfDayChecked) {
                const halfDayPeriodSelect = queryModal('#halfDayPeriod');
                const halfDayPeriod = halfDayPeriodSelect ? halfDayPeriodSelect.value : '';
                if (!halfDayPeriod) {
                    showAlert('Please select a half day period', 'error');
                    return false;
                }
            }
            break;
        case 3:
            const leaveReasonField = queryModal('#leaveReason');
            const reason = leaveReasonField ? leaveReasonField.value.trim() : '';
            if (!reason) {
                showAlert('Please provide a reason for your leave', 'error');
                return false;
            }
            if (reason.length < 10) {
                showAlert('Please provide a more detailed reason (at least 10 characters)', 'error');
                return false;
            }
            break;
        case 4:
            if (handoverState.required && handoverState.items.length === 0) {
                showAlert('A structured handover is required for this request. Please add at least one item and assign it to a colleague.', 'error');
                return false;
            }
            if (handoverState.items.length > 0) {
                const invalidItem = handoverState.items.find(item => !item.assignees || item.assignees.length === 0);
                if (invalidItem) {
                    showAlert(`Please assign at least one colleague to "${invalidItem.itemTitle}".`, 'error');
                    return false;
                }
            }
            break;
        case 5:
            // Final validation before submission
            if (!selectedLeaveType || !validateDates()) {
                showAlert('Please complete all previous steps', 'error');
                return false;
            }
            break;
    }
    return true;
}

function updateProgressIndicator() {
    console.log(`updateProgressIndicator: ${currentStep}`);
    queryAllModal('.step-indicator .step').forEach((step, index) => {
        step.classList.toggle('active', index + 1 <= currentStep);
        console.log(`step: ${step}`);
    });
}

function updateNavigationButtons() {
    const prevBtn = queryModal('#prevStepBtn');
    const nextBtn = queryModal('#nextStepBtn');
    const submitBtn = queryModal('#submitApplicationBtn');

    if (prevBtn) {
    prevBtn.style.display = currentStep > 1 ? 'inline-block' : 'none';
    }
    if (nextBtn) {
    nextBtn.style.display = currentStep < 5 ? 'inline-block' : 'none';
    }
    if (submitBtn) {
    submitBtn.style.display = currentStep === 5 ? 'inline-block' : 'none';
    }
}

function updateSummary() {
    // This function will be called to update the summary in step 4
    if (currentStep === 5) {
        updateReviewSummary();
    }
}

function updateReviewSummary() {
    const reviewLeaveType = queryModal('#reviewLeaveType');
    const reviewStartDate = queryModal('#reviewStartDate');
    const reviewEndDate = queryModal('#reviewEndDate');
    const reviewTotalDays = queryModal('#reviewTotalDays');
    const reviewHalfDay = queryModal('#reviewHalfDay');
    const reviewReason = queryModal('#reviewReason');
    const reviewEmergencyContact = queryModal('#reviewEmergencyContact');
    const reviewHandoverNotes = queryModal('#reviewHandoverNotes');

    const startDateInput = queryModal('#startDate');
    const endDateInput = queryModal('#endDate');
    const totalDaysDisplay = queryModal('#totalDays');
    const halfDayCheckbox = queryModal('#halfDayLeave');
    const halfDayPeriodSelect = queryModal('#halfDayPeriod');
    const leaveReasonField = queryModal('#leaveReason');
    const emergencyContactField = queryModal('#emergencyContact');
    const handoverNotesField = queryModal('#handoverNotes');

    if (reviewLeaveType) {
        reviewLeaveType.textContent = selectedLeaveType ? selectedLeaveType.name : '-';
    }
    if (reviewStartDate) {
        reviewStartDate.textContent = startDateInput && startDateInput.value ? startDateInput.value : '-';
    }
    if (reviewEndDate) {
        reviewEndDate.textContent = endDateInput && endDateInput.value ? endDateInput.value : '-';
    }
    // Use working days instead of total days for the review summary
    const workingDaysDisplay = queryModal('#workingDays');
    if (reviewTotalDays && workingDaysDisplay) {
        reviewTotalDays.textContent = workingDaysDisplay.textContent || '0';
    } else if (reviewTotalDays && totalDaysDisplay) {
        // Fallback: if workingDays display not found, try to get from calculation
        const startDateInput = queryModal('#startDate');
        const endDateInput = queryModal('#endDate');
        if (startDateInput && endDateInput && startDateInput.value && endDateInput.value) {
            const workingDays = calculateLeaveDays(startDateInput.value, endDateInput.value);
            reviewTotalDays.textContent = workingDays;
        } else {
            reviewTotalDays.textContent = '0';
        }
    }

    const halfDayChecked = halfDayCheckbox ? halfDayCheckbox.checked : false;
    if (reviewHalfDay) {
        reviewHalfDay.textContent = halfDayChecked
            ? (halfDayPeriodSelect && halfDayPeriodSelect.value ? halfDayPeriodSelect.value : 'Yes')
            : 'No';
    }

    if (reviewReason) {
        reviewReason.textContent = leaveReasonField && leaveReasonField.value ? leaveReasonField.value : '-';
    }
    if (reviewEmergencyContact) {
        reviewEmergencyContact.textContent = emergencyContactField && emergencyContactField.value ? emergencyContactField.value : '-';
    }
    if (reviewHandoverNotes) {
        reviewHandoverNotes.textContent = handoverNotesField && handoverNotesField.value ? handoverNotesField.value : '-';
    }

    const reviewHandoverStatus = queryModal('#reviewHandoverStatus');
    const reviewHandoverItems = queryModal('#reviewHandoverItems');
    if (reviewHandoverStatus) {
        let statusClass = 'bg-secondary';
        let statusText = 'Not evaluated';
        if (handoverState.required && handoverState.items.length === 0) {
            statusClass = 'bg-danger';
            statusText = 'Required';
        } else if (handoverState.items.length > 0) {
            statusClass = handoverState.required ? 'bg-danger' : 'bg-info';
            statusText = handoverState.required ? 'Required' : 'Included';
        } else {
            statusClass = 'bg-success';
            statusText = 'Not required';
        }
        reviewHandoverStatus.className = `badge ${statusClass}`;
        reviewHandoverStatus.textContent = statusText;
    }

    if (reviewHandoverItems) {
        if (!handoverState.items.length) {
            reviewHandoverItems.innerHTML = '<p class="text-muted mb-0">No structured handover items captured.</p>';
        } else {
            const list = handoverState.items.map((item, index) => {
                const assignees = item.assigneeNames && item.assigneeNames.length > 0
                    ? escapeHtml(item.assigneeNames.join(', '))
                    : 'Not assigned';
                const due = item.dueDate
                    ? `<span class="badge bg-light text-dark ms-2">Due ${new Date(item.dueDate).toLocaleDateString()}</span>`
                    : '';
                return `
                    <div class="border rounded p-3 mb-2">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>${index + 1}. ${escapeHtml(item.itemTitle)}</strong>
                                <div class="text-muted small">${escapeHtml(item.itemDescription || 'No additional instructions provided.')}</div>
                            </div>
                            <span class="badge bg-primary text-uppercase">${escapeHtml(item.priority)}</span>
                        </div>
                        <div class="mt-2 d-flex flex-wrap align-items-center small">
                            <span class="me-3">Assignees: <strong>${assignees}</strong></span>
                            ${due}
                        </div>
                    </div>
                `;
            }).join('');
            reviewHandoverItems.innerHTML = list;
        }
    }
}

function loadApprovalWorkflow() {
    const container = queryModal('#workflowPreviewContainer');
    if (!container) {
        console.warn('Workflow preview container not found');
        return;
    }

    // Show loading state
    container.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div><small class="text-muted d-block mt-2">Loading workflow...</small></div>';

    // Load approval workflow data
    fetch('<?= $base ?>php/scripts/leave/workflows/get_workflow_steps_for_entity.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            employeeId: <?= $employeeDetails->ID ?? 'null' ?>,
            entityId: <?= $entityID ?>
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.workflow) {
            renderWorkflowSteps(data.workflow);
        } else {
            container.innerHTML = '<div class="text-center py-3 text-muted"><small>Unable to load workflow information</small></div>';
        }
    })
    .catch(error => {
        console.error('Error loading approval workflow:', error);
        container.innerHTML = '<div class="text-center py-3 text-danger"><small>Error loading workflow. Please try again.</small></div>';
    });
}

function initializeHandoverModule() {
    renderHandoverItems();
    updateHandoverRequirementUI();

    const addBtn = queryModal('#addHandoverItemBtn');
    if (addBtn && addBtn.dataset.bound !== 'true') {
        addBtn.addEventListener('click', addHandoverItemFromForm);
        addBtn.dataset.bound = 'true';
    }

    const resetBtn = queryModal('#resetHandoverFormBtn');
    if (resetBtn && resetBtn.dataset.bound !== 'true') {
        resetBtn.addEventListener('click', resetHandoverForm);
        resetBtn.dataset.bound = 'true';
    }
}

function loadHandoverColleagues() {
    fetch('<?= $base ?>php/scripts/leave/utilities/get_filterable_employees.php?filterType=team')
        .then(response => response.json())
        .then(data => {
            if (!data || data.success === false || !Array.isArray(data.employees)) {
                throw new Error('Unable to load colleagues');
            }
            handoverColleagues = data.employees;
            const select = queryModal('#handoverAssignees');
            if (select) {
                select.innerHTML = '';
                handoverColleagues.forEach(colleague => {
                    const option = document.createElement('option');
                    option.value = colleague.id;
                    option.textContent = colleague.name || `${colleague.firstName} ${colleague.surname}` || 'Colleague';
                    select.appendChild(option);
                });
            }
        })
        .catch(() => {
            const select = queryModal('#handoverAssignees');
            if (select) {
                select.innerHTML = '<option value="">Unable to load colleagues</option>';
            }
        });
}

function evaluateHandoverRequirement() {
    if (!selectedLeaveType) {
        updateHandoverRequirementUI();
        return;
    }

    const startDateInput = queryModal('#startDate');
    const endDateInput = queryModal('#endDate');
    const entityField = queryModal('input[name="entityId"]');

    if (!startDateInput || !endDateInput || !startDateInput.value || !endDateInput.value || !entityField) {
        updateHandoverRequirementUI();
        return;
    }

    const formData = new FormData();
    formData.append('leaveTypeId', selectedLeaveType.id);
    formData.append('entityId', entityField.value);
    formData.append('startDate', startDateInput.value);
    formData.append('endDate', endDateInput.value);

    fetch('<?= $base ?>php/scripts/leave/handovers/check_requirement.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            handoverState.required = data && data.required === true;
            handoverState.policy = data ? data.policy : null;
            updateHandoverRequirementUI(data);
        })
        .catch(() => {
            updateHandoverRequirementUI();
        });
}

function updateHandoverRequirementUI(result = null) {
    const badge = queryModal('#handoverRequirementBadge');
    const notice = queryModal('#handoverRequirementNotice');
    if (!badge || !notice) {
        return;
    }

    let badgeClass = 'bg-secondary';
    let badgeText = 'Pending selection';
    let noticeText = 'Select a leave type and dates to determine if a handover is required.';
    let showNotice = false;

    if (handoverState.required) {
        badgeClass = 'bg-danger';
        badgeText = 'Handover required';
        noticeText = 'This leave requires a documented handover. Add at least one task with an assigned colleague.';
        showNotice = true;
    } else if (handoverState.items.length > 0) {
        badgeClass = 'bg-info';
        badgeText = 'Optional handover included';
        noticeText = 'You have added optional handover items to keep your team aligned.';
        showNotice = true;
    } else if (result && Object.prototype.hasOwnProperty.call(result, 'required')) {
        badgeClass = 'bg-success';
        badgeText = 'No handover required';
        noticeText = 'No structured handover is required, but consider adding one for continuity.';
        showNotice = true;
    }

    badge.className = `badge ${badgeClass}`;
    badge.textContent = badgeText;

    if (showNotice) {
        notice.classList.remove('d-none');
        const message = notice.querySelector('span');
        if (message) {
            message.textContent = noticeText;
        } else {
            notice.textContent = noticeText;
        }
    } else {
        notice.classList.add('d-none');
    }
}

function addHandoverItemFromForm() {
    const titleInput = queryModal('#handoverItemTitle');
    const descriptionInput = queryModal('#handoverItemDescription');
    const typeSelect = queryModal('#handoverItemType');
    const prioritySelect = queryModal('#handoverItemPriority');
    const dueDateInput = queryModal('#handoverItemDueDate');
    const assigneeSelect = queryModal('#handoverAssignees');

    const title = titleInput && titleInput.value ? titleInput.value.trim() : '';
    if (!title) {
        showAlert('Please provide a title for the handover item.', 'error');
        return;
    }

    const selectedAssignees = assigneeSelect
        ? Array.from(assigneeSelect.selectedOptions).map(option => option.value).filter(Boolean)
        : [];

    if (selectedAssignees.length === 0) {
        showAlert('Please assign at least one colleague.', 'error');
        return;
    }

    const newItem = {
        id: `handover-${Date.now()}`,
        itemTitle: title,
        itemDescription: descriptionInput && descriptionInput.value ? descriptionInput.value.trim() : '',
        itemType: typeSelect && typeSelect.value ? typeSelect.value : 'function',
        priority: prioritySelect && prioritySelect.value ? prioritySelect.value : 'medium',
        dueDate: dueDateInput && dueDateInput.value ? dueDateInput.value : '',
        assignees: selectedAssignees,
        assigneeNames: selectedAssignees.map(id => {
            const colleague = handoverColleagues.find(c => String(c.id) === String(id));
            return colleague ? (colleague.name || `${colleague.firstName || ''} ${colleague.surname || ''}`.trim()) : 'Colleague';
        })
    };

    handoverState.items.push(newItem);
    renderHandoverItems();
    resetHandoverForm();
    showAlert('Handover item added successfully.', 'success');
}

function resetHandoverForm() {
    const titleInput = queryModal('#handoverItemTitle');
    const descriptionInput = queryModal('#handoverItemDescription');
    const typeSelect = queryModal('#handoverItemType');
    const prioritySelect = queryModal('#handoverItemPriority');
    const dueDateInput = queryModal('#handoverItemDueDate');
    const assigneeSelect = queryModal('#handoverAssignees');

    if (titleInput) titleInput.value = '';
    if (descriptionInput) descriptionInput.value = '';
    if (typeSelect) typeSelect.value = 'function';
    if (prioritySelect) prioritySelect.value = 'medium';
    if (dueDateInput) dueDateInput.value = '';
    if (assigneeSelect) {
        Array.from(assigneeSelect.options).forEach(option => {
            option.selected = false;
        });
    }
}

function renderHandoverItems() {
    const listContainer = queryModal('#handoverItemsList');
    const counter = queryModal('#handoverItemsCounter');

    if (!listContainer) {
        return;
    }

    if (handoverState.items.length === 0) {
        listContainer.innerHTML = '<p class="text-muted mb-0">No handover items added yet. Use the form above to capture the responsibilities you need to transfer.</p>';
    } else {
        listContainer.innerHTML = handoverState.items.map(item => {
            const dueBadge = item.dueDate
                ? `<span class="badge bg-light text-dark ms-2">Due ${new Date(item.dueDate).toLocaleDateString()}</span>`
                : '';
            const assignees = item.assigneeNames && item.assigneeNames.length > 0
                ? escapeHtml(item.assigneeNames.join(', '))
                : 'Not assigned';
            return `
                <div class="border rounded p-3 mb-2 position-relative">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>${escapeHtml(item.itemTitle)}</strong>
                            <div class="text-muted small">${escapeHtml(item.itemDescription || 'No description provided.')}</div>
                        </div>
                        <div>
                            <span class="badge bg-primary text-uppercase">${escapeHtml(item.priority)}</span>
                            ${dueBadge}
                        </div>
                    </div>
                    <div class="mt-2 small text-muted">
                        Assigned to: <strong>${assignees}</strong>
                    </div>
                    <button type="button"
                            class="btn btn-sm btn-link text-danger position-absolute top-0 end-0 me-2 mt-2"
                            data-action="remove-handover-item"
                            data-handover-id="${item.id}">
                        <i class="ri-close-circle-line me-1"></i>Remove
                    </button>
                </div>
            `;
        }).join('');
    }

    if (counter) {
        counter.textContent = `${handoverState.items.length} item${handoverState.items.length === 1 ? '' : 's'}`;
    }

    listContainer.querySelectorAll('[data-action="remove-handover-item"]').forEach(button => {
        if (button.dataset.bound === 'true') {
            return;
        }
        button.addEventListener('click', (event) => {
            const id = event.currentTarget.getAttribute('data-handover-id');
            removeHandoverItem(id);
        });
        button.dataset.bound = 'true';
    });

    updateHandoverRequirementUI();
    syncHandoverPayload();
}

function removeHandoverItem(itemId) {
    handoverState.items = handoverState.items.filter(item => item.id !== itemId);
    renderHandoverItems();
}

function syncHandoverPayload() {
    const payloadInput = queryModal('#handoverPayload');
    if (!payloadInput) {
        return;
    }

    if (!handoverState.items.length) {
        payloadInput.value = '';
        return;
    }

    const payload = {
        items: handoverState.items.map(item => ({
            itemTitle: item.itemTitle,
            itemDescription: item.itemDescription,
            itemType: item.itemType,
            priority: item.priority,
            dueDate: item.dueDate,
            assignees: item.assignees
        }))
    };

    payloadInput.value = JSON.stringify(payload);
}

function renderWorkflowSteps(workflowData) {
    const container = queryModal('#workflowPreviewContainer');
    if (!container || !workflowData || !workflowData.steps) {
        return;
    }

    const steps = workflowData.steps || [];
    if (steps.length === 0) {
        container.innerHTML = '<div class="text-center py-3 text-muted"><small>No approval workflow configured</small></div>';
        return;
    }

    // Check if HR manager is already in workflow
    let hasHrManagerInSteps = false;
    steps.forEach(step => {
        // Check step name for HR
        if (step.stepName && step.stepName.toLowerCase().includes('hr')) {
            hasHrManagerInSteps = true;
        }
        // Also check approvers array exists
        if (step.approvers && step.approvers.length > 0) {
            step.approvers.forEach(approver => {
                // Additional check could be added here if needed
            });
        }
    });

    // Build HTML for workflow steps
    let html = '<div class="approval-workflow-preview">';

    steps.forEach((step, index) => {
        // Add arrow before step (except first)
        if (index > 0) {
            html += '<div class="approval-arrow"><i class="ri-arrow-right-line"></i></div>';
        }

        // Get step icon based on step name
        let iconClass = 'ri-user-line';
        if (step.stepName) {
            const stepNameLower = step.stepName.toLowerCase();
            if (stepNameLower.includes('supervisor') || stepNameLower.includes('manager')) {
                iconClass = 'ri-user-line';
            } else if (stepNameLower.includes('department') || stepNameLower.includes('head')) {
                iconClass = 'ri-building-line';
            } else if (stepNameLower.includes('hr')) {
                iconClass = 'ri-user-settings-line';
            }
        }

        html += '<div class="approval-step">';
        html += '<div class="approval-icon"><i class="' + iconClass + '"></i></div>';
        html += '<div class="approval-info">';
        html += '<div class="approval-title">' + (step.stepName || 'Step ' + step.stepOrder) + '</div>';

        // Display approvers
        const approvers = step.approvers || [];
        if (approvers.length === 0) {
            html += '<div class="approval-name">Not assigned</div>';
        } else {
            const primaryApprovers = approvers.filter(a => !a.isBackup);
            const backupApprovers = approvers.filter(a => a.isBackup);

            if (primaryApprovers.length > 0) {
                const approverNames = primaryApprovers.map(a => a.name || 'Not assigned').join(', ');
                html += '<div class="approval-name">' + approverNames + '</div>';
            }

            if (backupApprovers.length > 0) {
                const backupNames = backupApprovers.map(a => a.name || 'Not assigned').join(', ');
                html += '<div class="approval-name text-muted" style="font-size: 0.7rem;"><i class="ri-user-star-line"></i> Backup: ' + backupNames + '</div>';
            }
        }

        html += '</div>'; // approval-info
        html += '</div>'; // approval-step
    });

    // Add HR managers at end if not in workflow (for display only)
    if (!hasHrManagerInSteps && workflowData.hrManagers && workflowData.hrManagers.length > 0) {
        html += '<div class="approval-arrow"><i class="ri-arrow-right-line"></i></div>';
        html += '<div class="approval-step">';
        html += '<div class="approval-icon"><i class="ri-user-settings-line"></i></div>';
        html += '<div class="approval-info">';
        html += '<div class="approval-title">HR Manager</div>';
        const hrNames = workflowData.hrManagers.map(hr => hr.name || 'HR Manager').join(', ');
        html += '<div class="approval-name">' + hrNames + '</div>';
        html += '</div>'; // approval-info
        html += '</div>'; // approval-step
    }

    html += '</div>'; // approval-workflow-preview

    container.innerHTML = html;
}

function submitLeaveApplication() {
    const form = queryModal('#applyLeaveForm');
    if (!form) {
        showAlert('Unable to locate the apply leave form. Please refresh the page.', 'error');
        return;
    }

    syncHandoverPayload();
    // Ensure working days are calculated and stored before submission
    const startDateInput = queryModal('#startDate');
    const endDateInput = queryModal('#endDate');
    if (startDateInput && endDateInput && startDateInput.value && endDateInput.value) {
        const workingDays = calculateLeaveDays(startDateInput.value, endDateInput.value);

        // Update or create hidden input for working days
        let workingDaysInput = queryModal('#workingDaysInput');
        if (!workingDaysInput) {
            workingDaysInput = document.createElement('input');
            workingDaysInput.type = 'hidden';
            workingDaysInput.id = 'workingDaysInput';
            workingDaysInput.name = 'workingDays';
            form.appendChild(workingDaysInput);
        }
        workingDaysInput.value = workingDays;

        // Also update totalDays hidden field if it exists (for backward compatibility)
        // But note: server will recalculate, so this is just for reference
        let totalDaysInput = form.querySelector('input[name="totalDays"][type="hidden"]');
        if (!totalDaysInput) {
            totalDaysInput = document.createElement('input');
            totalDaysInput.type = 'hidden';
            totalDaysInput.name = 'totalDays';
            form.appendChild(totalDaysInput);
        }
        totalDaysInput.value = workingDays; // Store working days, not total days
    }

    const formData = new FormData(form);

    // Show loading state
    const submitBtn = queryModal('#submitApplicationBtn');
    if (!submitBtn) {
        showAlert('Submit button not found. Please refresh the page.', 'error');
        return;
    }
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="ri-loader-4-line spin me-1"></i>Submitting...';
    submitBtn.disabled = true;

    // Submit application
    fetch('<?= $base ?>php/scripts/leave/applications/submit_leave_application.php', {
        method: 'POST',
        body: formData
    })
    .then(async response => {
        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            const rawText = await response.text();
            throw new Error(`Unexpected ${response.status} ${response.statusText}. ${rawText.substring(0, 300)}`);
        }
        const payload = await response.json();
        payload.__httpStatus = response.status;
        return payload;
    })
    .then(data => {
        if (data.success) {
            showToast('success', 'Application Submitted',
                     'Your leave application has been submitted successfully and is pending approval.');

            // Close modal
            const modalElement = getModalRoot();
            if (modalElement) {
                const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
                modalElement.addEventListener('hidden.bs.modal', () => cleanupModalArtifacts(), { once: true });
                modalInstance.hide();
            } else {
                cleanupModalArtifacts();
            }

            // Refresh leave data
            if (typeof refreshLeaveData === 'function') {
                refreshLeaveData();
            }

            // Refresh interactive calendar if it exists
            if (typeof window !== 'undefined' && window.interactiveCalendar) {
                window.interactiveCalendar.refetchEvents();
            }

            // Dispatch custom event for calendar refresh
            window.dispatchEvent(new CustomEvent('leaveApplicationSubmitted'));
        } else {
            const messageParts = [
                data.message || 'Failed to submit leave application.',
                data.errorId ? `Reference: ${data.errorId}` : '',
                data.debug && data.debug.message ? `Details: ${data.debug.message}` : ''
            ].filter(Boolean);
            showAlert(messageParts.join(' '), 'error');
        }
    })
    .catch(error => {
        console.error('Leave application submission failed:', error);
        showAlert(`Unable to submit application. ${error.message || ''}`.trim(), 'error');
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function showAlert(message, type = 'info') {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    // Show alert only in the dedicated alert container
    const container = queryModal('#formAlertContainer');
    if (container) {
        container.appendChild(alertDiv);
    }

    // Auto dismiss after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

function clearAlerts() {
    const container = queryModal('#formAlertContainer');
    if (container) {
        container.innerHTML = '';
    }
}

function debugFormVisibility() {
    console.log('=== FORM VISIBILITY DEBUG ===');

    const modal = getModalRoot();
    const modalBody = document.querySelector('#applyLeaveModal .modal-body');
    const form = queryModal('#applyLeaveForm');
    const step1 = getStepElement(1);
    const alertContainer = queryModal('#formAlertContainer');

    console.log('Elements found:', {
        modal: !!modal,
        modalBody: !!modalBody,
        form: !!form,
        step1: !!step1,
        alertContainer: !!alertContainer
    });

    if (modalBody) {
        console.log('Modal body:', {
            height: modalBody.offsetHeight,
            display: getComputedStyle(modalBody).display,
            overflow: getComputedStyle(modalBody).overflow
        });
    }

    if (form) {
        console.log('Form:', {
            height: form.offsetHeight,
            display: getComputedStyle(form).display,
            visibility: getComputedStyle(form).visibility,
            opacity: getComputedStyle(form).opacity
        });
    }

    if (step1) {
        console.log('Step 1:', {
            height: step1.offsetHeight,
            display: getComputedStyle(step1).display,
            visibility: getComputedStyle(step1).visibility,
            opacity: getComputedStyle(step1).opacity,
            classes: step1.className,
            inlineStyle: step1.style.cssText
        });
    }

    if (alertContainer) {
        console.log('Alert container:', {
            height: alertContainer.offsetHeight,
            children: alertContainer.children.length,
            innerHTML: alertContainer.innerHTML.substring(0, 100) + '...'
        });
    }

    // Force show step 1
    if (step1) {
        step1.classList.add('active');
        forceStepVisibility(step1);
        step1.style.setProperty('background-color', 'rgba(255, 0, 0, 0.1)', 'important');
        console.log('Step 1 forced visible with red background');
    }

    console.log('=== END DEBUG ===');
}

// Initialize when modal is shown
const applyLeaveModalElement = getModalRoot();
if (applyLeaveModalElement) {
    applyLeaveModalElement.addEventListener('shown.bs.modal', function() {
    console.log('Modal opened - initializing...');

    // Reset variables
    currentStep = 1;
    selectedLeaveType = null;
    selectedEntitlement = null;
    handoverState = {
        required: false,
        policy: null,
        items: []
    };

    // Reset form
        const form = queryModal('#applyLeaveForm');
        if (form) {
            form.reset();
        }
        resetDatePickers();
        renderHandoverItems();

    // Reset all steps except step 1
        queryAllModal('.form-step').forEach(step => {
        if (step.id !== 'step1') {
            step.classList.remove('active');
                clearStepVisibility(step);
        }
    });

    // Ensure step 1 is active and visible
        const step1 = getStepElement(1);
    if (step1) {
        step1.classList.add('active');
            forceStepVisibility(step1);
        console.log('Step 1 activated and forced visible');
    }

    // Clear any existing alerts that might interfere
    clearAlerts();

    // Initialize components
    initializeApplyLeaveModal();
    updateProgressIndicator();
    updateNavigationButtons();

    // Ensure form stays visible after a short delay
    setTimeout(() => {
        if (step1) {
                forceStepVisibility(step1);
            console.log('Step 1 visibility confirmed after timeout');
        }
    }, 500);

        const datasetPendingLeaveTypeId = applyLeaveModalElement.dataset.pendingLeaveTypeId;
        if (datasetPendingLeaveTypeId) {
            setPendingLeaveType(datasetPendingLeaveTypeId);
        }
        if (pendingLeaveTypeId) {
            setTimeout(() => {
                attemptPendingLeaveTypeSelection();
            }, 150);
        }

        applyLeaveModalElement.addEventListener('hidden.bs.modal', cleanupModalArtifacts, { once: true });
});
}

window.LeaveApplyModal = window.LeaveApplyModal || {};
window.LeaveApplyModal.preselectLeaveType = queueLeaveTypePreselection;
window.LeaveApplyModal.goToStep = goToStep;
})();
</script>
