<?php
/**
 * Activity Management Modal
 * 
 * Provides a comprehensive modal for creating and editing activities
 * Features:
 * - Form validation
 * - Auto-save drafts
 * - Template selection
 * - Priority settings
 * - Due date picker
 * - File attachments
 * - Recurring activity options
 */

// Get form data for editing (if applicable)
$editActivity = null;
$isEditMode = false;

if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $activityId = Utility::clean_string($_GET['edit']);
    $editActivity = Schedule::tija_activities(array('activityID' => $activityId), true, $DBConn);
    $isEditMode = !empty($editActivity);
}
?>

<!-- Activity Management Modal -->
<div class="modal fade" id="manage_activity" tabindex="-1" aria-labelledby="manageActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="activityForm" action="<?= $base ?>php/scripts/schedule/manage_activity.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="manageActivityModalLabel">
                        <i class="ri-add-line me-2"></i>
                        <?= $isEditMode ? 'Edit Activity' : 'Create New Activity' ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <!-- Form Alert Container -->
                    <div id="formAlertContainer" class="alert-container mb-3" style="display: none;"></div>
                    
                    <div class="row">
                        <!-- Left Column - Main Details -->
                        <div class="col-lg-8">
                            <!-- Activity Basic Information -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="ri-file-text-line me-2"></i>
                                        Basic Information
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <!-- Activity Name -->
                                    <div class="mb-3">
                                        <label for="activityName" class="form-label required">Activity Name</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="activityName" 
                                               name="activityName" 
                                               value="<?= $isEditMode ? htmlspecialchars($editActivity->activityName) : '' ?>"
                                               placeholder="Enter activity name..."
                                               required>
                                        <div class="invalid-feedback">
                                            Please provide a valid activity name.
                                        </div>
                                    </div>
                                    
                                    <!-- Activity Description -->
                                    <div class="mb-3">
                                        <label for="activityDescription" class="form-label">Description</label>
                                        <textarea class="form-control" 
                                                  id="activityDescription" 
                                                  name="activityDescription" 
                                                  rows="4"
                                                  placeholder="Describe the activity details..."><?= $isEditMode ? htmlspecialchars($editActivity->activityDescription) : '' ?></textarea>
                                        <div class="form-text">
                                            Provide detailed information about what needs to be done.
                                        </div>
                                    </div>
                                    
                                    <!-- Activity Category -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="activityCategoryID" class="form-label">Category</label>
                                                <select class="form-select" id="activityCategoryID" name="activityCategoryID">
                                                    <option value="">Select Category</option>
                                                    <?php foreach ($activityCategories as $category): ?>
                                                        <option value="<?= $category->activityCategoryID ?>" 
                                                                <?= $isEditMode && $editActivity->activityCategoryID == $category->activityCategoryID ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($category->activityCategoryName) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="activityTypeID" class="form-label">Activity Type</label>
                                                <select class="form-select" id="activityTypeID" name="activityTypeID">
                                                    <option value="">Select Type</option>
                                                    <?php foreach ($activityTypes as $type): ?>
                                                        <option value="<?= $type->activityTypeID ?>" 
                                                                <?= $isEditMode && $editActivity->activityTypeID == $type->activityTypeID ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($type->activityTypeName) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Scheduling and Priority -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="ri-calendar-line me-2"></i>
                                        Scheduling & Priority
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Due Date -->
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="activityDate" class="form-label required">Due Date</label>
                                                <input type="date" 
                                                       class="form-control" 
                                                       id="activityDate" 
                                                       name="activityDate" 
                                                       value="<?= $isEditMode ? $editActivity->activityDate : '' ?>"
                                                       required>
                                                <div class="invalid-feedback">
                                                    Please select a due date.
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Start Time -->
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="activityStartTime" class="form-label">Start Time</label>
                                                <input type="time" 
                                                       class="form-control" 
                                                       id="activityStartTime" 
                                                       name="activityStartTime" 
                                                       value="<?= $isEditMode ? $editActivity->activityStartTime : '' ?>">
                                            </div>
                                        </div>
                                        
                                        <!-- Duration -->
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="activityDuration" class="form-label">Duration (hours)</label>
                                                <input type="number" 
                                                       class="form-control" 
                                                       id="activityDuration" 
                                                       name="activityDuration" 
                                                       min="0.5" 
                                                       max="24" 
                                                       step="0.5"
                                                       value="<?= $isEditMode ? $editActivity->activityDuration : '' ?>"
                                                       placeholder="2.5">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Priority and Status -->
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="priority" class="form-label">Priority</label>
                                                <select class="form-select" id="priority" name="priority">
                                                    <option value="low" <?= $isEditMode && $editActivity->priority == 'low' ? 'selected' : '' ?>>Low</option>
                                                    <option value="medium" <?= $isEditMode && $editActivity->priority == 'medium' ? 'selected' : '' ?>>Medium</option>
                                                    <option value="high" <?= $isEditMode && $editActivity->priority == 'high' ? 'selected' : '' ?>>High</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="activityStatusID" class="form-label">Status</label>
                                                <select class="form-select" id="activityStatusID" name="activityStatusID">
                                                    <?php foreach ($activityStatuses as $status): ?>
                                                        <option value="<?= $status->activityStatusID ?>" 
                                                                <?= $isEditMode && $editActivity->activityStatusID == $status->activityStatusID ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($status->activityStatusName) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Assignment and Context -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="ri-user-line me-2"></i>
                                        Assignment & Context
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Assignee -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="activityOwnerID" class="form-label">Assignee</label>
                                                <select class="form-select" id="activityOwnerID" name="activityOwnerID">
                                                    <option value="">Select Assignee</option>
                                                    <?php foreach ($employees as $employee): ?>
                                                        <option value="<?= $employee->ID ?>" 
                                                                <?= $isEditMode && $editActivity->activityOwnerID == $employee->ID ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($employee->firstName . ' ' . $employee->lastName) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <!-- Project -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="projectID" class="form-label">Project</label>
                                                <select class="form-select" id="projectID" name="projectID">
                                                    <option value="">Select Project</option>
                                                    <?php foreach ($projects as $project): ?>
                                                        <option value="<?= $project->projectID ?>" 
                                                                <?= $isEditMode && $editActivity->projectID == $project->projectID ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($project->projectName) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <!-- Client -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="clientID" class="form-label">Client</label>
                                                <select class="form-select" id="clientID" name="clientID">
                                                    <option value="">Select Client</option>
                                                    <?php foreach ($clients as $client): ?>
                                                        <option value="<?= $client->clientID ?>" 
                                                                <?= $isEditMode && $editActivity->clientID == $client->clientID ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($client->clientName) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <!-- Business Unit -->
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="businessUnitID" class="form-label">Business Unit</label>
                                                <select class="form-select" id="businessUnitID" name="businessUnitID">
                                                    <option value="">Select Business Unit</option>
                                                    <?php foreach ($businessUnits as $unit): ?>
                                                        <option value="<?= $unit->businessUnitID ?>" 
                                                                <?= $isEditMode && $editActivity->businessUnitID == $unit->businessUnitID ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($unit->businessUnitName) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Recurring Options -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="ri-repeat-line me-2"></i>
                                        Recurring Options
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           id="recurring" 
                                                           name="recurring" 
                                                           value="Y"
                                                           <?= $isEditMode && $editActivity->recurring == 'Y' ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="recurring">
                                                        Make this activity recurring
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3" id="recurringOptions" style="display: none;">
                                                <label for="recurringFrequency" class="form-label">Frequency</label>
                                                <select class="form-select" id="recurringFrequency" name="recurringFrequency">
                                                    <option value="daily">Daily</option>
                                                    <option value="weekly">Weekly</option>
                                                    <option value="monthly">Monthly</option>
                                                    <option value="yearly">Yearly</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row" id="recurringEndOptions" style="display: none;">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="recurringEndDate" class="form-label">End Date</label>
                                                <input type="date" 
                                                       class="form-control" 
                                                       id="recurringEndDate" 
                                                       name="recurringEndDate">
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="recurringOccurrences" class="form-label">Number of Occurrences</label>
                                                <input type="number" 
                                                       class="form-control" 
                                                       id="recurringOccurrences" 
                                                       name="recurringOccurrences" 
                                                       min="1" 
                                                       max="365"
                                                       placeholder="10">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column - Additional Options -->
                        <div class="col-lg-4">
                            <!-- Quick Templates -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="ri-file-copy-line me-2"></i>
                                        Quick Templates
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="applyTemplate('meeting')">
                                            <i class="ri-calendar-event-line me-2"></i>Meeting
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-sm" onclick="applyTemplate('task')">
                                            <i class="ri-task-line me-2"></i>Task
                                        </button>
                                        <button type="button" class="btn btn-outline-warning btn-sm" onclick="applyTemplate('review')">
                                            <i class="ri-eye-line me-2"></i>Review
                                        </button>
                                        <button type="button" class="btn btn-outline-info btn-sm" onclick="applyTemplate('followup')">
                                            <i class="ri-phone-line me-2"></i>Follow-up
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- File Attachments -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="ri-attachment-line me-2"></i>
                                        Attachments
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <input type="file" 
                                               class="form-control" 
                                               id="activityAttachments" 
                                               name="activityAttachments[]" 
                                               multiple
                                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.txt">
                                        <div class="form-text">
                                            Upload files related to this activity (max 10MB each).
                                        </div>
                                    </div>
                                    
                                    <!-- File Preview -->
                                    <div id="filePreview" class="file-preview-list"></div>
                                </div>
                            </div>
                            
                            <!-- Activity Notes -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="ri-sticky-note-line me-2"></i>
                                        Notes
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <textarea class="form-control" 
                                              id="activityNotes" 
                                              name="activityNotes" 
                                              rows="4"
                                              placeholder="Add any additional notes..."><?= $isEditMode ? htmlspecialchars($editActivity->activityNotes) : '' ?></textarea>
                                </div>
                            </div>
                            
                            <!-- Save Options -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="ri-save-line me-2"></i>
                                        Save Options
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="autoSave" 
                                                   name="autoSave" 
                                                   value="Y" 
                                                   checked>
                                            <label class="form-check-label" for="autoSave">
                                                Auto-save as draft
                                            </label>
                                        </div>
                                        
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="sendNotification" 
                                                   name="sendNotification" 
                                                   value="Y" 
                                                   checked>
                                            <label class="form-check-label" for="sendNotification">
                                                Send notification to assignee
                                            </label>
                                        </div>
                                        
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="addToCalendar" 
                                                   name="addToCalendar" 
                                                   value="Y">
                                            <label class="form-check-label" for="addToCalendar">
                                                Add to calendar
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancel
                    </button>
                    
                    <button type="button" class="btn btn-outline-primary" id="saveDraftBtn">
                        <i class="ri-save-line me-1"></i>Save Draft
                    </button>
                    
                    <button type="submit" class="btn btn-primary" id="saveActivityBtn">
                        <i class="ri-check-line me-1"></i>
                        <?= $isEditMode ? 'Update Activity' : 'Create Activity' ?>
                    </button>
                </div>
                
                <!-- Hidden Fields -->
                <input type="hidden" name="activityID" value="<?= $isEditMode ? $editActivity->activityID : '' ?>">
                <input type="hidden" name="orgDataID" value="<?= $orgDataID ?>">
                <input type="hidden" name="entityID" value="<?= $entityID ?>">
            </form>
        </div>
    </div>
</div>

<!-- Modal JavaScript -->
<script>
/**
 * Activity Management Modal JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeActivityModal();
});

function initializeActivityModal() {
    const modal = document.getElementById('manage_activity');
    const form = document.getElementById('activityForm');
    const recurringCheckbox = document.getElementById('recurring');
    const recurringOptions = document.getElementById('recurringOptions');
    const recurringEndOptions = document.getElementById('recurringEndOptions');
    const fileInput = document.getElementById('activityAttachments');
    const filePreview = document.getElementById('filePreview');
    
    // Recurring options toggle
    if (recurringCheckbox) {
        recurringCheckbox.addEventListener('change', function() {
            if (this.checked) {
                recurringOptions.style.display = 'block';
                recurringEndOptions.style.display = 'block';
            } else {
                recurringOptions.style.display = 'none';
                recurringEndOptions.style.display = 'none';
            }
        });
        
        // Trigger on load if checked
        if (recurringCheckbox.checked) {
            recurringOptions.style.display = 'block';
            recurringEndOptions.style.display = 'block';
        }
    }
    
    // File upload preview
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            displayFilePreview(this.files);
        });
    }
    
    // Form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitActivityForm();
        });
    }
    
    // Auto-save functionality
    setupAutoSave();
    
    // Form validation
    setupFormValidation();
}

// Display file preview
function displayFilePreview(files) {
    const preview = document.getElementById('filePreview');
    preview.innerHTML = '';
    
    Array.from(files).forEach(file => {
        const fileItem = document.createElement('div');
        fileItem.className = 'file-preview-item d-flex align-items-center justify-content-between p-2 border rounded mb-2';
        
        const fileInfo = document.createElement('div');
        fileInfo.className = 'd-flex align-items-center';
        
        const icon = getFileIcon(file.type);
        fileInfo.innerHTML = `
            <i class="${icon} me-2"></i>
            <div>
                <div class="file-name">${file.name}</div>
                <small class="text-muted">${formatFileSize(file.size)}</small>
            </div>
        `;
        
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-sm btn-outline-danger';
        removeBtn.innerHTML = '<i class="ri-close-line"></i>';
        removeBtn.onclick = function() {
            fileItem.remove();
        };
        
        fileItem.appendChild(fileInfo);
        fileItem.appendChild(removeBtn);
        preview.appendChild(fileItem);
    });
}

// Get file icon based on type
function getFileIcon(type) {
    if (type.includes('pdf')) return 'ri-file-pdf-line text-danger';
    if (type.includes('word')) return 'ri-file-word-line text-primary';
    if (type.includes('excel') || type.includes('spreadsheet')) return 'ri-file-excel-line text-success';
    if (type.includes('image')) return 'ri-image-line text-info';
    return 'ri-file-line text-secondary';
}

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Apply template
function applyTemplate(template) {
    const templates = {
        meeting: {
            name: 'Team Meeting',
            description: 'Scheduled team meeting to discuss project updates and upcoming tasks.',
            duration: '1',
            priority: 'medium',
            type: 'meeting'
        },
        task: {
            name: 'Task Assignment',
            description: 'Complete assigned task according to specifications and requirements.',
            duration: '2',
            priority: 'medium',
            type: 'task'
        },
        review: {
            name: 'Document Review',
            description: 'Review and provide feedback on submitted documents.',
            duration: '1.5',
            priority: 'high',
            type: 'review'
        },
        followup: {
            name: 'Follow-up Call',
            description: 'Follow-up call with client to discuss project progress and next steps.',
            duration: '0.5',
            priority: 'medium',
            type: 'call'
        }
    };
    
    const templateData = templates[template];
    if (templateData) {
        document.getElementById('activityName').value = templateData.name;
        document.getElementById('activityDescription').value = templateData.description;
        document.getElementById('activityDuration').value = templateData.duration;
        document.getElementById('priority').value = templateData.priority;
        
        showToast('success', 'Template Applied', `${templateData.name} template has been applied.`);
    }
}

// Setup auto-save
function setupAutoSave() {
    const autoSaveCheckbox = document.getElementById('autoSave');
    if (!autoSaveCheckbox || !autoSaveCheckbox.checked) return;
    
    const form = document.getElementById('activityForm');
    const formFields = form.querySelectorAll('input, textarea, select');
    
    formFields.forEach(field => {
        field.addEventListener('change', debounce(saveDraft, 2000));
    });
}

// Save draft
function saveDraft() {
    const formData = new FormData(document.getElementById('activityForm'));
    formData.append('action', 'save_draft');
    
    fetch('<?= $base ?>php/scripts/schedule/save_activity_draft.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('info', 'Auto-saved', 'Draft saved automatically.');
        }
    })
    .catch(error => {
        console.error('Auto-save error:', error);
    });
}

// Setup form validation
function setupFormValidation() {
    const form = document.getElementById('activityForm');
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        field.addEventListener('blur', validateField);
        field.addEventListener('input', clearValidation);
    });
}

// Validate individual field
function validateField(e) {
    const field = e.target;
    const value = field.value.trim();
    
    if (field.hasAttribute('required') && !value) {
        field.classList.add('is-invalid');
        return false;
    } else {
        field.classList.remove('is-invalid');
        field.classList.add('is-valid');
        return true;
    }
}

// Clear validation
function clearValidation(e) {
    e.target.classList.remove('is-invalid', 'is-valid');
}

// Submit activity form
function submitActivityForm() {
    const form = document.getElementById('activityForm');
    const formData = new FormData(form);
    
    // Validate form
    if (!validateForm()) {
        showFormAlert('error', 'Please fill in all required fields correctly.');
        return;
    }
    
    // Show loading state
    const submitBtn = document.getElementById('saveActivityBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="ri-loader-4-line spin me-1"></i>Saving...';
    submitBtn.disabled = true;
    
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('success', 'Success', data.message || 'Activity saved successfully!');
            form.reset();
            bootstrap.Modal.getInstance(document.getElementById('manage_activity')).hide();
            // Refresh the current view
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showToast('error', 'Error', data.message || 'Failed to save activity.');
            showFormAlert('error', data.message || 'Failed to save activity.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('error', 'Error', 'An error occurred while saving the activity.');
        showFormAlert('error', 'An error occurred while saving the activity.');
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Validate entire form
function validateForm() {
    const form = document.getElementById('activityForm');
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!validateField({ target: field })) {
            isValid = false;
        }
    });
    
    return isValid;
}

// Show form alert
function showFormAlert(type, message) {
    const alertContainer = document.getElementById('formAlertContainer');
    if (!alertContainer) return;
    
    alertContainer.innerHTML = `
        <div class="alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show" role="alert">
            <i class="ri-${type === 'error' ? 'error-warning-line' : 'information-line'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    alertContainer.style.display = 'block';
}

// Utility function for debouncing
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
</script>

<style>
/* Modal Specific Styles */
.modal-xl {
    max-width: 1200px;
}

.required::after {
    content: ' *';
    color: #dc3545;
}

.file-preview-item {
    background: #f8f9fa;
    border: 1px solid #dee2e6 !important;
}

.file-name {
    font-weight: 500;
    color: #495057;
}

.alert-container {
    position: relative;
}

.form-check-input:checked {
    background-color: #007bff;
    border-color: #007bff;
}

/* Auto-save indicator */
.auto-save-indicator {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #28a745;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-size: 0.8rem;
    z-index: 1055;
    display: none;
}

/* Loading spinner */
.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .modal-xl {
        max-width: 95%;
    }
    
    .modal-body .row {
        flex-direction: column;
    }
    
    .modal-body .col-lg-8,
    .modal-body .col-lg-4 {
        max-width: 100%;
        flex: 0 0 100%;
    }
}
</style>
