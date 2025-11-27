<?php
/**
 * Proposal System Date Pickers Initialization
 * Initializes Flatpickr for all date inputs in the proposal system with validations
 *
 * @package    Tija CRM
 * @subpackage Sales Management
 */
?>

<script>
(function() {
   'use strict';

   // Check if Flatpickr is available
   if (typeof flatpickr === 'undefined') {
      console.warn('Flatpickr library not loaded. Date pickers will use native inputs.');
      return; // Exit if Flatpickr is not available
   }

   // Common Flatpickr configuration
   const commonDateConfig = {
      dateFormat: 'Y-m-d',
      allowInput: true,
      enableTime: false,
      minDate: 'today',
      locale: {
         firstDayOfWeek: 1
      },
      onReady: function(selectedDates, dateStr, instance) {
         // Add calendar icon
         const wrapper = instance.input.parentElement;
         if (wrapper && !wrapper.querySelector('.calendar-icon')) {
            wrapper.style.position = 'relative';
            const icon = document.createElement('i');
            icon.className = 'ri-calendar-line calendar-icon';
            icon.style.cssText = 'position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6c757d; z-index: 1;';
            wrapper.appendChild(icon);
         }
      }
   };

   const commonDateTimeConfig = {
      ...commonDateConfig,
      enableTime: true,
      time_24hr: true,
      dateFormat: 'Y-m-d H:i',
      altInput: true,
      altFormat: 'F j, Y at H:i',
      minDate: 'today'
   };

   /**
    * Initialize Proposal Deadline Date Picker
    * Validates against expected close date if available
    */
   function initProposalDeadlinePicker() {
      const proposalDeadlineInputs = document.querySelectorAll('#proposalDeadline, input[name="proposalDeadline"]');

      proposalDeadlineInputs.forEach(input => {
         if (input.readOnly) return;

         // Destroy existing instance if any
         if (input._flatpickr) {
            input._flatpickr.destroy();
         }

         const expectedCloseDate = input.dataset.expectedCloseDate ||
                                   (input.closest('form')?.querySelector('[name="expectedCloseDate"]')?.value) ||
                                   null;

         const config = {
            ...commonDateConfig,
            minDate: 'today',
            maxDate: expectedCloseDate || null,
            onChange: function(selectedDates, dateStr, instance) {
               input.classList.remove('is-invalid', 'border-danger');
               removeErrorMessage(input);

               if (expectedCloseDate && selectedDates.length > 0) {
                  const selectedDate = selectedDates[0];
                  const maxDate = new Date(expectedCloseDate);

                  if (selectedDate > maxDate) {
                     input.classList.add('is-invalid', 'border-danger');
                     showErrorMessage(input, `Proposal deadline cannot be after the expected close date (${new Date(expectedCloseDate).toLocaleDateString()}).`);
                     instance.clear();
                     return;
                  }
               }
            }
         };

         if (typeof flatpickr !== 'undefined') {
            flatpickr(input, config);
         }
      });
   }

   /**
    * Initialize Proposal Checklist Deadline Date Picker
    * Validates against proposal deadline
    */
   function initChecklistDeadlinePicker() {
      const checklistDeadlineInput = document.getElementById('proposalChecklistDeadlineDate');

      if (!checklistDeadlineInput || checklistDeadlineInput.readOnly) return;

      // Destroy existing instance if any
      if (checklistDeadlineInput._flatpickr) {
         checklistDeadlineInput._flatpickr.destroy();
      }

      const proposalDeadline = checklistDeadlineInput.dataset.proposalDeadline ||
                               (checklistDeadlineInput.closest('form')?.querySelector('[name="proposalDeadline"]')?.value) ||
                               null;

      const config = {
         ...commonDateConfig,
         minDate: 'today',
         maxDate: proposalDeadline || null,
         onChange: function(selectedDates, dateStr, instance) {
            checklistDeadlineInput.classList.remove('is-invalid', 'border-danger');
            removeErrorMessage(checklistDeadlineInput);

            if (proposalDeadline && selectedDates.length > 0) {
               const selectedDate = selectedDates[0];
               const maxDate = new Date(proposalDeadline);

               if (selectedDate > maxDate) {
                  checklistDeadlineInput.classList.add('is-invalid', 'border-danger');
                  showErrorMessage(checklistDeadlineInput, `Checklist deadline cannot be after the proposal deadline (${new Date(proposalDeadline).toLocaleDateString()}).`);
                  instance.clear();
                  return;
               }
            }
         }
      };

      if (typeof flatpickr !== 'undefined') {
         flatpickr(checklistDeadlineInput, config);
      }
   }

   /**
    * Initialize Checklist Item Assignment Due Date Picker
    * Validates against checklist deadline
    */
   function initChecklistItemDueDatePicker() {
      const itemDueDateInput = document.getElementById('proposalChecklistItemAssignmentDueDate');

      if (!itemDueDateInput || itemDueDateInput.readOnly) return;

      // Destroy existing instance if any
      if (itemDueDateInput._flatpickr) {
         itemDueDateInput._flatpickr.destroy();
      }

      // Get checklist deadline from data attribute or find it in the modal/form
      let checklistDeadline = itemDueDateInput.dataset.checklistDeadline;
      if (!checklistDeadline) {
         // Try to find checklist deadline from the checklist modal
         const checklistModal = document.getElementById('manageChecklistModal');
         if (checklistModal) {
            const checklistDeadlineInput = checklistModal.querySelector('#proposalChecklistDeadlineDate');
            if (checklistDeadlineInput) {
               if (checklistDeadlineInput.value) {
                  checklistDeadline = checklistDeadlineInput.value;
               } else if (checklistDeadlineInput.dataset.proposalDeadline) {
                  checklistDeadline = checklistDeadlineInput.dataset.proposalDeadline;
               }
            }
         }

         // Also check if deadline is in the label span
         if (!checklistDeadline) {
            const label = itemDueDateInput.closest('.form-group')?.querySelector('label');
            if (label) {
               const deadlineSpan = label.querySelector('span');
               if (deadlineSpan && deadlineSpan.textContent.includes('ChecklistDeadline:')) {
                  const match = deadlineSpan.textContent.match(/ChecklistDeadline:\s*([^\s]+)/);
                  if (match && match[1]) {
                     checklistDeadline = match[1].trim();
                  }
               }
            }
         }
      }

      const config = {
         ...commonDateConfig,
         minDate: 'today',
         maxDate: checklistDeadline || null,
         onChange: function(selectedDates, dateStr, instance) {
            itemDueDateInput.classList.remove('is-invalid', 'border-danger');
            removeErrorMessage(itemDueDateInput);

            if (checklistDeadline && selectedDates.length > 0) {
               const selectedDate = selectedDates[0];
               const maxDate = new Date(checklistDeadline);

               if (selectedDate > maxDate) {
                  itemDueDateInput.classList.add('is-invalid', 'border-danger');
                  showErrorMessage(itemDueDateInput, `Assignment due date cannot be after the checklist deadline (${new Date(checklistDeadline).toLocaleDateString()}).`);
                  instance.clear();
                  return;
               }
            }
         }
      };

      if (typeof flatpickr !== 'undefined') {
         flatpickr(itemDueDateInput, config);
      }
   }

   /**
    * Initialize Proposal Task Due Date Picker (DateTime)
    * Validates that due date is not in the past
    */
   function initTaskDueDatePicker() {
      const taskDueDateInput = document.getElementById('taskDueDate');

      if (!taskDueDateInput || taskDueDateInput.readOnly) return;

      // Destroy existing instance if any
      if (taskDueDateInput._flatpickr) {
         taskDueDateInput._flatpickr.destroy();
      }

      const config = {
         ...commonDateTimeConfig,
         minDate: 'today',
         onChange: function(selectedDates, dateStr, instance) {
            taskDueDateInput.classList.remove('is-invalid', 'border-danger');
            removeErrorMessage(taskDueDateInput);

            if (selectedDates.length > 0) {
               const selectedDate = selectedDates[0];
               const now = new Date();

               if (selectedDate < now) {
                  taskDueDateInput.classList.add('is-invalid', 'border-danger');
                  showErrorMessage(taskDueDateInput, 'Task due date cannot be in the past.');
                  instance.clear();
                  return;
               }
            }
         }
      };

      if (typeof flatpickr !== 'undefined') {
         flatpickr(taskDueDateInput, config);
      }
   }

   /**
    * Show error message below input
    */
   function showErrorMessage(input, message) {
      removeErrorMessage(input);

      const errorDiv = document.createElement('div');
      errorDiv.className = 'text-danger small mt-1 error-message';
      errorDiv.textContent = message;
      errorDiv.id = input.id + '_error';

      const parent = input.parentElement;
      if (parent) {
         parent.appendChild(errorDiv);
      }
   }

   /**
    * Remove error message
   */
   function removeErrorMessage(input) {
      const errorDiv = document.getElementById(input.id + '_error');
      if (errorDiv) {
         errorDiv.remove();
      }

      // Also remove any error messages that might be siblings
      const parent = input.parentElement;
      if (parent) {
         const errorMessages = parent.querySelectorAll('.error-message');
         errorMessages.forEach(msg => msg.remove());
      }
   }

   /**
    * Initialize all date pickers
    */
   function initializeAllDatePickers() {
      // Wait for DOM to be ready
      if (document.readyState === 'loading') {
         document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initializeAllDatePickers, 100);
         });
         return;
      }

      // Initialize all pickers
      initProposalDeadlinePicker();
      initChecklistDeadlinePicker();
      initChecklistItemDueDatePicker();
      initTaskDueDatePicker();
   }

   // Initialize on page load
   initializeAllDatePickers();

   // Re-initialize when modals are shown (for dynamic content)
   document.addEventListener('shown.bs.modal', function(e) {
      setTimeout(function() {
         initProposalDeadlinePicker();
         initChecklistDeadlinePicker();
         initChecklistItemDueDatePicker();
         initTaskDueDatePicker();
      }, 100);
   });

   // Export functions for manual initialization if needed
   window.initProposalDatePickers = {
      proposalDeadline: initProposalDeadlinePicker,
      checklistDeadline: initChecklistDeadlinePicker,
      checklistItemDueDate: initChecklistItemDueDatePicker,
      taskDueDate: initTaskDueDatePicker,
      all: initializeAllDatePickers
   };

})();
</script>

