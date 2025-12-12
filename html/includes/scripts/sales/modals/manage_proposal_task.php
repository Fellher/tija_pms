<?php
/**
 * Proposal Task Management Modal
 * Create and edit proposal tasks
 *
 * @package    Tija CRM
 * @subpackage Sales Management
 */

// Get employees for assignment
$allEmployees = Employee::employees(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
?>

<div class="proposal-task-form">
      <input type="hidden" name="proposalID" id="taskProposalID" value="<?= $proposalID ?? '' ?>">
      <input type="hidden" name="proposalTaskID" id="proposalTaskID" value="">
      <input type="hidden" name="action" id="taskAction" value="create">
      <input type="hidden" name="orgDataID" value="<?= $orgDataID ?? '' ?>">
      <input type="hidden" name="entityID" value="<?= $entityID ?? '' ?>">

      <!-- Task Name -->
      <div class="form-group mb-3">
         <label for="taskName" class="form-label fw-semibold">
            Task Name <span class="text-danger">*</span>
         </label>
         <input type="text"
                class="form-control"
                id="taskName"
                name="taskName"
                placeholder="Enter task name"
                required>
      </div>

      <!-- Task Description -->
      <div class="form-group mb-3">
         <label for="taskDescription" class="form-label fw-semibold">Task Description</label>
         <textarea class="form-control"
                   id="taskDescription"
                   name="taskDescription"
                   rows="3"
                   placeholder="Describe the task requirements"></textarea>
      </div>

      <!-- Assignment & Due Date Row -->
      <div class="row g-3 mb-3">
         <div class="col-md-6">
            <div class="form-group">
               <label for="assignedTo" class="form-label fw-semibold">
                  Assign To <span class="text-danger">*</span>
               </label>
               <select class="form-select" id="assignedTo" name="assignedTo" required>
                  <option value="">Select User</option>
                  <?php if($allEmployees): ?>
                     <?php foreach($allEmployees as $employee): ?>
                        <option value="<?= $employee->ID ?>">
                           <?= htmlspecialchars($employee->FirstName . ' ' . $employee->Surname) ?>
                        </option>
                     <?php endforeach; ?>
                  <?php endif; ?>
               </select>
            </div>
         </div>
         <div class="col-md-6">
            <div class="form-group">
               <label for="taskDueDate" class="form-label fw-semibold">
                  Due Date <span class="text-danger">*</span>
               </label>
               <input type="text"
                      class="form-control"
                      id="taskDueDate"
                      name="dueDate"
                      placeholder="Select date and time"
                      required>
               <small class="text-muted">Date and time when task is due</small>
            </div>
         </div>
      </div>

      <!-- Priority & Mandatory Row -->
      <div class="row g-3 mb-3">
         <div class="col-md-6">
            <div class="form-group">
               <label for="taskPriority" class="form-label fw-semibold">Priority</label>
               <select class="form-select" id="taskPriority" name="priority">
                  <option value="low">Low</option>
                  <option value="medium" selected>Medium</option>
                  <option value="high">High</option>
                  <option value="urgent">Urgent</option>
               </select>
            </div>
         </div>
         <div class="col-md-6">
            <div class="form-group">
               <label class="form-label fw-semibold">Options</label>
               <div class="form-check form-switch">
                  <input class="form-check-input"
                         type="checkbox"
                         id="isMandatory"
                         name="isMandatory"
                         value="Y">
                  <label class="form-check-label" for="isMandatory">
                     <i class="ri-alert-line me-1"></i>Mandatory Task
                  </label>
               </div>
            </div>
         </div>
      </div>

      <!-- Status (for editing) -->
      <div class="form-group mb-3 d-none" id="taskStatusGroup">
         <label for="taskStatus" class="form-label fw-semibold">Status</label>
         <select class="form-select" id="taskStatus" name="status">
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
         </select>
      </div>

      <!-- File Attachment -->
      <div class="form-group mb-3">
         <label for="taskAttachment" class="form-label fw-semibold">
            <i class="ri-attachment-2 me-1"></i>Attachment
         </label>
         <input type="file"
                class="form-control"
                id="taskAttachment"
                name="taskAttachment"
                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif">
         <small class="text-muted">
            Supported: PDF, Word, Excel, PowerPoint, Images (Max 10MB)
         </small>
         <!-- Show existing attachment if editing -->
         <div id="existingAttachment" class="mt-2 d-none">
            <div class="alert alert-info py-2 d-flex align-items-center justify-content-between">
               <span>
                  <i class="ri-file-line me-1"></i>
                  <span id="existingAttachmentName">Existing file</span>
               </span>
               <a href="#" id="viewExistingAttachment" target="_blank" class="btn btn-sm btn-outline-primary">
                  <i class="ri-eye-line"></i> View
               </a>
            </div>
         </div>
      </div>

      <!-- Error/Success Messages -->
      <div id="taskFormMessages" class="mt-3"></div>
</div>

<script>
(function() {
   'use strict';

   // Get form from modal (created by Utility::form_modal_header)
   const modalElement = document.getElementById('manageProposalTaskModal');
   const form = modalElement ? modalElement.querySelector('form') : null;
   const dueDateInput = document.getElementById('taskDueDate');
   const statusGroup = document.getElementById('taskStatusGroup');
   const statusSelect = document.getElementById('taskStatus');

   // Initialize Flatpickr for task due date (DateTime picker)
   function initTaskDueDatePicker() {
      if (!dueDateInput) return;

      // Destroy existing instance if any
      if (dueDateInput._flatpickr) {
         dueDateInput._flatpickr.destroy();
      }

      if (typeof flatpickr !== 'undefined') {
         flatpickr(dueDateInput, {
            enableTime: true,
            time_24hr: true,
            dateFormat: 'Y-m-d H:i',
            altInput: true,
            altFormat: 'F j, Y at H:i',
            minDate: 'today',
            allowInput: true,
            onReady: function(selectedDates, dateStr, instance) {
               // Add calendar icon
               const wrapper = instance.altInput.parentElement;
               if (wrapper && !wrapper.querySelector('.calendar-icon')) {
                  wrapper.style.position = 'relative';
                  const icon = document.createElement('i');
                  icon.className = 'ri-calendar-line calendar-icon';
                  icon.style.cssText = 'position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6c757d; z-index: 1;';
                  wrapper.appendChild(icon);
               }
            },
            onChange: function(selectedDates, dateStr, instance) {
               dueDateInput.classList.remove('is-invalid', 'border-danger');
               removeTaskDateErrorMessage();

               if (selectedDates.length > 0) {
                  const selectedDate = selectedDates[0];
                  const now = new Date();

                  if (selectedDate < now) {
                     dueDateInput.classList.add('is-invalid', 'border-danger');
                     showTaskDateErrorMessage('Task due date cannot be in the past.');
                     instance.clear();
                     return;
                  }
               }
            }
         });
      } else {
         // Fallback: Use native datetime-local input
         dueDateInput.type = 'datetime-local';
         const now = new Date();
         now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
         dueDateInput.min = now.toISOString().slice(0, 16);
      }
   }

   // Helper functions for error messages
   function showTaskDateErrorMessage(message) {
      removeTaskDateErrorMessage();
      const errorDiv = document.createElement('div');
      errorDiv.className = 'text-danger small mt-1 error-message';
      errorDiv.id = 'taskDueDate_error';
      errorDiv.textContent = message;
      const parent = dueDateInput.parentElement;
      if (parent) {
         parent.appendChild(errorDiv);
      }
   }

   function removeTaskDateErrorMessage() {
      const errorDiv = document.getElementById('taskDueDate_error');
      if (errorDiv) {
         errorDiv.remove();
      }
      const parent = dueDateInput?.parentElement;
      if (parent) {
         const errorMessages = parent.querySelectorAll('.error-message');
         errorMessages.forEach(msg => msg.remove());
      }
   }

   // Also initialize on page load if input is already visible
   if (dueDateInput && dueDateInput.offsetParent !== null) {
      setTimeout(initTaskDueDatePicker, 100);
   }

   // Form submission handler
   window.submitProposalTask = function(e) {
      if (e) {
         e.preventDefault();
         e.stopPropagation();
      }

      // Get form element from modal - ensure it exists
      const modal = document.getElementById('manageProposalTaskModal');
      if (!modal) {
         console.error('Proposal task modal not found');
         if (typeof showToast === 'function') {
            showToast('Modal not found. Please refresh the page.', 'error');
         } else {
            alert('Modal not found. Please refresh the page.');
         }
         return false;
      }

      const formElement = modal.querySelector('form');
      if (!formElement) {
         console.error('Proposal task form not found in modal');
         if (typeof showToast === 'function') {
            showToast('Form not found. Please refresh the page.', 'error');
         } else {
            alert('Form not found. Please refresh the page.');
         }
         return false;
      }

      const formData = new FormData(formElement);
      const action = document.getElementById('taskAction').value;
      formData.append('action', action);

      // Get due date value - handle Flatpickr format
      let dueDateValue = formData.get('dueDate');

      // If Flatpickr is used, get the actual date value
      if (dueDateInput && dueDateInput._flatpickr) {
         const selectedDates = dueDateInput._flatpickr.selectedDates;
         if (selectedDates.length > 0) {
            // Format as Y-m-d H:i for backend
            const date = selectedDates[0];
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            dueDateValue = `${year}-${month}-${day} ${hours}:${minutes}:00`;
            formData.set('dueDate', dueDateValue);
         }
      }

      // Validate due date
      if (dueDateValue) {
         const dueDate = new Date(dueDateValue);
         if (dueDate < new Date()) {
            if (typeof showToast === 'function') {
               showToast('Due date cannot be in the past', 'error');
            } else {
               alert('Due date cannot be in the past');
            }
            return false;
         }
      } else {
         if (typeof showToast === 'function') {
            showToast('Please select a due date', 'error');
         } else {
            alert('Please select a due date');
         }
         return false;
      }

      const submitBtn = document.querySelector('#manageProposalTaskModal .btn-primary');
      const originalBtnText = submitBtn ? submitBtn.innerHTML : '';

      if (submitBtn) {
         submitBtn.disabled = true;
         submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
      }

      fetch('<?= $base ?>php/scripts/sales/manage_proposal_task.php', {
         method: 'POST',
         body: formData
      })
      .then(response => {
         // Check if response is OK
         if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
         }

         // Try to parse as JSON
         return response.text().then(text => {
            try {
               return JSON.parse(text);
            } catch (e) {
               console.error('Response is not JSON:', text);
               throw new Error('Server returned invalid response. Please check the console for details.');
            }
         });
      })
      .then(data => {
         if (data && data.success) {
            if (typeof showToast === 'function') {
               showToast(data.message || 'Task saved successfully', 'success');
            } else {
               alert(data.message || 'Task saved successfully');
            }

            // Close modal and reload
            const modal = bootstrap.Modal.getInstance(document.getElementById('manageProposalTaskModal'));
            if (modal) {
               modal.hide();
            }

            setTimeout(() => {
               location.reload();
            }, 500);
         } else {
            const errorMessage = (data && data.message) ? data.message : 'An error occurred. Please try again.';

            const messagesDiv = document.getElementById('taskFormMessages');
            if (messagesDiv) {
               messagesDiv.innerHTML = '<div class="alert alert-danger">' +
                  errorMessage +
                  '</div>';
            }

            if (typeof showToast === 'function') {
               showToast(errorMessage, 'error');
            } else {
               alert(errorMessage);
            }

            // Re-enable submit button
            if (submitBtn && originalBtnText) {
               submitBtn.disabled = false;
               submitBtn.innerHTML = originalBtnText;
            }
         }
      })
      .catch(error => {
         console.error('Error:', error);
         const errorMessage = error.message || 'Network error. Please try again.';

         const messagesDiv = document.getElementById('taskFormMessages');
         if (messagesDiv) {
            messagesDiv.innerHTML = '<div class="alert alert-danger">' +
               errorMessage +
               '</div>';
         }

         if (typeof showToast === 'function') {
            showToast(errorMessage, 'error');
         } else {
            alert(errorMessage);
         }

         // Re-enable submit button
         if (submitBtn && originalBtnText) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
         }
      })
      .finally(() => {
         if (submitBtn && originalBtnText) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
         }
      });

      return false;
   };

   // Initialize modal handlers (using existing modalElement from above)
   if (modalElement) {
      modalElement.addEventListener('shown.bs.modal', function() {
         const modalForm = this.querySelector('form');
         const submitBtn = document.getElementById('submitProposalTask');

         // Re-initialize date picker when modal is shown
         setTimeout(initTaskDueDatePicker, 100);

         if (modalForm) {
            // Prevent default form submission
            modalForm.action = 'javascript:void(0);';
            modalForm.onsubmit = function(e) {
               e.preventDefault();
               e.stopPropagation();
               submitProposalTask(e);
               return false;
            };
         }

         if (submitBtn) {
            submitBtn.type = 'button';
            submitBtn.onclick = function(e) {
               e.preventDefault();
               e.stopPropagation();
               submitProposalTask(e);
               return false;
            };
         }
      });

      modalElement.addEventListener('hidden.bs.modal', function() {
         const modalForm = this.querySelector('form');
         if (modalForm) {
            modalForm.reset();
            const messagesDiv = document.getElementById('taskFormMessages');
            if (messagesDiv) {
               messagesDiv.innerHTML = '';
            }
            const proposalTaskID = document.getElementById('proposalTaskID');
            if (proposalTaskID) {
               proposalTaskID.value = '';
            }
            const taskAction = document.getElementById('taskAction');
            if (taskAction) {
               taskAction.value = 'create';
            }
            if (statusGroup) {
               statusGroup.classList.add('d-none');
            }

            // Clear Flatpickr if initialized
            if (dueDateInput && dueDateInput._flatpickr) {
               dueDateInput._flatpickr.clear();
            }

            // Hide existing attachment display
            const existingAttachmentDiv = document.getElementById('existingAttachment');
            if (existingAttachmentDiv) {
               existingAttachmentDiv.classList.add('d-none');
            }
         }
      });
   }

   // Edit task handler (called from task list)
   window.editProposalTask = function(taskID) {
      // Fetch task details and populate form
      const formData = new FormData();
      formData.append('action', 'get');
      formData.append('proposalTaskID', taskID);

      fetch('<?= $base ?>php/scripts/sales/manage_proposal_task.php', {
         method: 'POST',
         body: formData
      })
      .then(response => response.json())
      .then(data => {
         if (data.success && data.data) {
            const task = data.data;
            document.getElementById('proposalTaskID').value = task.proposalTaskID;
            document.getElementById('taskAction').value = 'update';
            document.getElementById('taskName').value = task.taskName || '';
            document.getElementById('taskDescription').value = task.taskDescription || '';
            document.getElementById('assignedTo').value = task.assignedTo || '';
            document.getElementById('taskPriority').value = task.priority || 'medium';
            document.getElementById('isMandatory').checked = (task.isMandatory === 'Y');

            // Set due date - Flatpickr will handle formatting
            if (task.dueDate) {
               const dueDateInput = document.getElementById('taskDueDate');
               if (dueDateInput) {
                  // If Flatpickr is initialized, use its API
                  if (dueDateInput._flatpickr) {
                     dueDateInput._flatpickr.setDate(task.dueDate);
                  } else {
                     // Fallback: format for native input
                     const dueDate = new Date(task.dueDate);
                     dueDate.setMinutes(dueDate.getMinutes() - dueDate.getTimezoneOffset());
                     dueDateInput.value = dueDate.toISOString().slice(0, 16);
                  }
               }
            }

            if (statusSelect) {
               statusSelect.value = task.status || 'pending';
               statusGroup.classList.remove('d-none');
            }

            // Handle existing attachment display
            const existingAttachmentDiv = document.getElementById('existingAttachment');
            const existingAttachmentName = document.getElementById('existingAttachmentName');
            const viewExistingAttachment = document.getElementById('viewExistingAttachment');

            if (task.taskAttachment && task.taskAttachment.trim() !== '') {
               if (existingAttachmentDiv && existingAttachmentName && viewExistingAttachment) {
                  existingAttachmentDiv.classList.remove('d-none');
                  // Extract filename from path
                  const fileName = task.taskAttachment.split('/').pop();
                  existingAttachmentName.textContent = fileName;
                  viewExistingAttachment.href = '<?= $config['DataDir'] ?? '' ?>' + task.taskAttachment;
               }
            } else {
               if (existingAttachmentDiv) {
                  existingAttachmentDiv.classList.add('d-none');
               }
            }

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('manageProposalTaskModal'));
            modal.show();
         }
      })
      .catch(error => {
         console.error('Error:', error);
      });
   };
})();
</script>

