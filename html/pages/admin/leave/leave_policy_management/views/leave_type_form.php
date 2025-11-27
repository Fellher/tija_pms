<!-- Leave Type Form View -->
<div class="leave-type-form-wrapper">
    <div class="row">
        <div class="col-lg-10 col-xl-8 mx-auto">
            <!-- Progress Indicator -->
            <div class="card custom-card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg avatar-rounded bg-primary">
                                <i class="ri-calendar-2-line text-white fs-24"></i>
                            </div>
                        </div>
                        <div class="flex-fill ms-3">
                            <h4 class="mb-1 fw-semibold">
                                <?= ($action ?? 'create') === 'create' ? 'Create New Leave Type' : 'Edit Leave Type' ?>
                            </h4>
                            <p class="text-muted mb-0">Define the basic information and display settings for this leave type</p>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            // Construct absolute path to backend script
            $backendScript = '/php/scripts/leave/config/manage_leave_type.php';
            if (isset($config['siteURL']) && !empty($config['siteURL'])) {
                $backendScript = rtrim($config['siteURL'], '/') . '/php/scripts/leave/config/manage_leave_type.php';
            }
            $isEdit = ($action ?? 'create') === 'edit';
            ?>
            <form id="leaveTypeForm" method="POST" action="<?= $backendScript ?>" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="<?= $isEdit ? 'update' : 'create' ?>">
                <?php if ($isEdit && isset($leaveType)): ?>
                <input type="hidden" name="leaveTypeID" value="<?= $leaveType->leaveTypeID ?>">
                <?php endif; ?>

                <!-- Basic Information Section -->
                <div class="card custom-card mb-4">
                    <div class="card-header bg-primary-transparent">
                        <h5 class="mb-0">
                            <i class="ri-information-line me-2"></i>
                            Basic Information
                        </h5>
                        <small class="text-muted">Essential details that identify this leave type</small>
                    </div>
                    <div class="card-body">

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="leaveTypeName" class="form-label">
                                Leave Type Name <span class="text-danger">*</span>
                                <i class="ri-question-line text-muted ms-1"
                                   data-bs-toggle="tooltip"
                                   title="The display name shown to employees when applying for leave. Use clear, descriptive names like 'Annual Leave' or 'Sick Leave'."></i>
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="leaveTypeName"
                                   name="leaveTypeName"
                                   placeholder="e.g., Annual Leave, Sick Leave"
                                   required>
                            <div class="form-text text-muted">
                                <i class="ri-lightbulb-line me-1"></i>
                                Examples: "Annual Leave", "Sick Leave", "Maternity Leave", "Study Leave"
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="leaveTypeCode" class="form-label">
                                Leave Type Code <span class="text-danger">*</span>
                                <i class="ri-question-line text-muted ms-1"
                                   data-bs-toggle="tooltip"
                                   title="A short unique identifier used internally by the system. Must be uppercase letters, numbers, or underscores only."></i>
                            </label>
                            <div class="input-group">
                                <input type="text"
                                       class="form-control"
                                       id="leaveTypeCode"
                                       name="leaveTypeCode"
                                       placeholder="e.g., ANNUAL, SICK"
                                       pattern="[A-Z0-9_]+"
                                       maxlength="10"
                                       required>
                                <button type="button"
                                        class="btn btn-outline-secondary"
                                        onclick="generateLeaveTypeCode()"
                                        data-bs-toggle="tooltip"
                                        title="Auto-generate code from the leave type name">
                                    <i class="ri-refresh-line"></i>
                                </button>
                            </div>
                            <div class="form-text text-muted">
                                <i class="ri-information-line me-1"></i>
                                Uppercase letters, numbers, and underscores only (max 10 characters). Click refresh to auto-generate.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="leaveTypeDescription" class="form-label">
                        Description
                        <i class="ri-question-line text-muted ms-1"
                           data-bs-toggle="tooltip"
                           title="Optional detailed explanation of the leave type, its purpose, and any special rules or requirements."></i>
                    </label>
                    <textarea class="form-control"
                              id="leaveTypeDescription"
                              name="leaveTypeDescription"
                              rows="4"
                              placeholder="Describe the purpose and rules for this leave type..."></textarea>
                    <div class="form-text text-muted">
                        <i class="ri-file-text-line me-1"></i>
                        Optional: Provide additional context about this leave type's purpose, eligibility, or special requirements.
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="isActive" class="form-label">
                                Status
                                <i class="ri-question-line text-muted ms-1"
                                   data-bs-toggle="tooltip"
                                   title="Active leave types are available for employees to use. Suspended types are hidden but can be reactivated later."></i>
                            </label>
                            <select class="form-select" id="isActive" name="isActive">
                                <option value="Y">Active</option>
                                <option value="N">Suspended</option>
                            </select>
                            <div class="form-text text-muted">
                                <i class="ri-toggle-line me-1"></i>
                                Active types are available for use. Suspended types are hidden but can be reactivated.
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="sortOrder" class="form-label">
                                Sort Order
                                <i class="ri-question-line text-muted ms-1"
                                   data-bs-toggle="tooltip"
                                   title="Controls the display order when listing leave types. Lower numbers appear first."></i>
                            </label>
                            <input type="number"
                                   class="form-control"
                                   id="sortOrder"
                                   name="sortOrder"
                                   value="0"
                                   min="0"
                                   max="999">
                            <div class="form-text text-muted">
                                <i class="ri-sort-asc me-1"></i>
                                Lower numbers appear first. Use 0-10 for common types.
                            </div>
                        </div>
                    </div>
                    </div>
                </div>

                <!-- Display Settings Section -->
                <div class="card custom-card mb-4">
                    <div class="card-header bg-info-transparent">
                        <h5 class="mb-0">
                            <i class="ri-palette-line me-2"></i>
                            Display Settings
                        </h5>
                        <small class="text-muted">Customize visual appearance in calendars and reports</small>
                    </div>
                    <div class="card-body">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="leaveColor" class="form-label">
                                    Leave Color
                                    <i class="ri-question-line text-muted ms-1"
                                       data-bs-toggle="tooltip"
                                       title="Choose a color to represent this leave type in calendar views and visual reports. Use distinct colors for easy identification."></i>
                                </label>
                                <div class="input-group">
                                    <input type="color"
                                           class="form-control form-control-color"
                                           id="leaveColor"
                                           name="leaveColor"
                                           value="#3498db"
                                           title="Choose color">
                                    <input type="text"
                                           class="form-control"
                                           id="leaveColorText"
                                           value="#3498db"
                                           placeholder="#3498db"
                                           readonly>
                                </div>
                                <div class="form-text text-muted">
                                    <i class="ri-palette-line me-1"></i>
                                    Visual identifier in calendars and reports. Use contrasting colors for easy distinction.
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="leaveIcon" class="form-label">
                                    Leave Icon
                                    <i class="ri-question-line text-muted ms-1"
                                       data-bs-toggle="tooltip"
                                       title="Select an icon that visually represents this leave type. Icons appear in lists, cards, and quick views."></i>
                                </label>
                                <select class="form-select" id="leaveIcon" name="leaveIcon">
                                    <option value="ri-calendar-line">📅 Calendar</option>
                                    <option value="ri-calendar-check-line">✅ Calendar Check</option>
                                    <option value="ri-calendar-event-line">📆 Calendar Event</option>
                                    <option value="ri-calendar-todo-line">📋 Calendar Todo</option>
                                    <option value="ri-heart-line">❤️ Heart</option>
                                    <option value="ri-medicine-bottle-line">💊 Medicine</option>
                                    <option value="ri-baby-carriage-line">👶 Baby Carriage</option>
                                    <option value="ri-graduation-cap-line">🎓 Graduation</option>
                                    <option value="ri-plane-line">✈️ Plane</option>
                                    <option value="ri-home-line">🏠 Home</option>
                                    <option value="ri-tools-line">🔧 Tools</option>
                                    <option value="ri-book-open-line">📖 Book</option>
                                </select>
                                <div class="form-text text-muted">
                                    <i class="ri-image-line me-1"></i>
                                    Visual icon for lists and cards. Choose intuitive icons for quick identification.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Next Steps Info -->
                <div class="alert alert-info border-0 mb-4">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <i class="ri-information-line fs-20"></i>
                        </div>
                        <div class="flex-fill ms-2">
                            <h6 class="alert-heading mb-2">Next Steps After Creation</h6>
                            <p class="mb-2 small">After creating this leave type, you'll be able to configure:</p>
                            <ul class="small mb-0">
                                <li><strong>Policy Details:</strong> Approval requirements, paid/unpaid status, application rules</li>
                                <li><strong>Entitlement:</strong> Annual leave days allocation per employee</li>
                                <li><strong>Accumulation Policy:</strong> How leave days accumulate and carry-forward rules</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="card custom-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="<?= $base ?>html/?s=admin&ss=leave&p=leave_types&action=list" class="btn btn-outline-secondary btn-lg">
                                <i class="ri-arrow-left-line me-1"></i>
                                Cancel
                            </a>

                            <div>
                                <button type="button" class="btn btn-outline-primary btn-lg me-2" onclick="resetForm()">
                                    <i class="ri-refresh-line me-1"></i>
                                    Reset Form
                                </button>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="ri-save-line me-1"></i>
                                    <?= ($action ?? 'create') === 'create' ? 'Create Leave Type' : 'Update Leave Type' ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.leave-type-form-wrapper {
    animation: fadeIn 0.3s ease-in;
}

.leave-type-form-wrapper .card {
    border: 1px solid rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.leave-type-form-wrapper .card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.leave-type-form-wrapper .form-control:focus,
.leave-type-form-wrapper .form-select:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.25);
}

.leave-type-form-wrapper .was-validated .form-control:invalid,
.leave-type-form-wrapper .was-validated .form-select:invalid {
    border-color: var(--bs-danger);
}

.leave-type-form-wrapper .was-validated .form-control:valid,
.leave-type-form-wrapper .was-validated .form-select:valid {
    border-color: var(--bs-success);
}
</style>

<script>
function resetForm() {
    if (confirm('Are you sure you want to reset the form? All entered data will be lost.')) {
        document.getElementById('leaveTypeForm').reset();
        // Reset color picker
        document.getElementById('leaveColor').value = '#3498db';
        document.getElementById('leaveColorText').value = '#3498db';
        // Clear validation states
        const fields = document.querySelectorAll('.is-invalid, .is-valid');
        fields.forEach(field => {
            field.classList.remove('is-invalid', 'is-valid');
        });
    }
}

// Generate leave type code from name
function generateLeaveTypeCode() {
    const name = document.getElementById('leaveTypeName').value.trim();
    if (name) {
        // Convert to uppercase, remove special characters, replace spaces with underscores
        let code = name.toUpperCase()
            .replace(/[^A-Z0-9\s]/g, '')
            .replace(/\s+/g, '_')
            .substring(0, 10);
        document.getElementById('leaveTypeCode').value = code;
    }
}

// Sync color picker with text input
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    const colorPicker = document.getElementById('leaveColor');
    const colorText = document.getElementById('leaveColorText');

    if (colorPicker && colorText) {
        colorPicker.addEventListener('input', function() {
            colorText.value = this.value;
        });

        colorText.addEventListener('input', function() {
            if (/^#[0-9A-F]{6}$/i.test(this.value)) {
                colorPicker.value = this.value;
            }
        });
    }

    // Form submission handler - allow normal form submission
    // The form will submit normally to the backend script
    // The backend will handle the response and redirect accordingly
});
</script>

