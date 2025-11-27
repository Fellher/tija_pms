<?php
/**
 * Checklist Item Submission Modal
 * Form for submitting checklist item assignments
 *
 * @package    Tija CRM
 * @subpackage Sales Management
 */
?>

<div class="checklist-submission-form">
   <form id="checklistSubmissionForm" enctype="multipart/form-data">
      <input type="hidden" name="proposalChecklistItemAssignmentID" id="submissionAssignmentID" value="">
      <input type="hidden" name="action" value="submit">
      <input type="hidden" name="orgDataID" value="<?= $orgDataID ?? '' ?>">
      <input type="hidden" name="entityID" value="<?= $entityID ?? '' ?>">
      <input type="hidden" name="submissionStatus" value="submitted">

      <div class="alert alert-info mb-3">
         <i class="ri-information-line me-2"></i>
         <strong>Submitting:</strong> <span id="submissionItemName"></span>
      </div>

      <!-- Submission Notes -->
      <div class="form-group mb-3">
         <label for="submissionNotes" class="form-label fw-semibold">Submission Notes</label>
         <textarea class="form-control"
                   id="submissionNotes"
                   name="submissionNotes"
                   rows="4"
                   placeholder="Add any notes or comments about your submission"></textarea>
         <small class="text-muted">Optional: Provide details about your submission</small>
      </div>

      <!-- File Upload -->
      <div class="form-group mb-3">
         <label for="submissionFiles" class="form-label fw-semibold">Supporting Documents</label>
         <input type="file"
                class="form-control"
                id="submissionFiles"
                name="submissionFiles[]"
                multiple
                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif">
         <small class="text-muted">
            Upload supporting documents (PDF, Word, Excel, Images, etc.) - Max 50MB per file
         </small>
         <div id="fileListPreview" class="mt-2"></div>
      </div>

      <!-- Error/Success Messages -->
      <div id="submissionFormMessages" class="mt-3"></div>
   </form>
</div>

<script>
(function() {
   'use strict';

   const form = document.getElementById('checklistSubmissionForm');
   const fileInput = document.getElementById('submissionFiles');
   const fileListPreview = document.getElementById('fileListPreview');

   // File selection preview
   if (fileInput) {
      fileInput.addEventListener('change', function(e) {
         const files = e.target.files;
         if (files.length > 0) {
            let html = '<div class="list-group">';
            for (let i = 0; i < files.length; i++) {
               const file = files[i];
               const fileSize = (file.size / 1024 / 1024).toFixed(2);
               html += `
                  <div class="list-group-item d-flex justify-content-between align-items-center">
                     <div>
                        <i class="ri-file-line me-2"></i>
                        <span>${file.name}</span>
                        <small class="text-muted ms-2">(${fileSize} MB)</small>
                     </div>
                  </div>
               `;
            }
            html += '</div>';
            fileListPreview.innerHTML = html;
         } else {
            fileListPreview.innerHTML = '';
         }
      });
   }

   // Form submission handler
   window.submitChecklistItem = function(e) {
      if (e) {
         e.preventDefault();
         e.stopPropagation();
      }

      const assignmentID = document.getElementById('submissionAssignmentID').value;
      if (!assignmentID) {
         if (typeof showToast === 'function') {
            showToast('Assignment ID is missing. Please refresh and try again.', 'error');
         }
         return false;
      }

      const formData = new FormData(form);

      const submitBtn = document.querySelector('#submitChecklistItemModal .btn-primary');
      const originalBtnText = submitBtn ? submitBtn.innerHTML : '';

      if (submitBtn) {
         submitBtn.disabled = true;
         submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
      }

      fetch('<?= $base ?>php/scripts/sales/manage_proposal_checklist_submission.php', {
         method: 'POST',
         body: formData
      })
      .then(response => response.json())
      .then(data => {
         if (data.success) {
            if (typeof showToast === 'function') {
               showToast(data.message || 'Submission completed successfully', 'success');
            }

            // Close modal and reload
            const modal = bootstrap.Modal.getInstance(document.getElementById('submitChecklistItemModal'));
            if (modal) {
               modal.hide();
            }

            setTimeout(() => {
               location.reload();
            }, 500);
         } else {
            const messagesDiv = document.getElementById('submissionFormMessages');
            if (messagesDiv) {
               messagesDiv.innerHTML = '<div class="alert alert-danger">' +
                  (data.message || 'An error occurred. Please try again.') +
                  '</div>';
            }

            if (typeof showToast === 'function') {
               showToast(data.message || 'Failed to submit', 'error');
            }
         }
      })
      .catch(error => {
         console.error('Error:', error);
         if (typeof showToast === 'function') {
            showToast('Network error. Please try again.', 'error');
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

   // Initialize modal handlers
   const modalElement = document.getElementById('submitChecklistItemModal');
   if (modalElement) {
      modalElement.addEventListener('shown.bs.modal', function() {
         const modalForm = this.querySelector('form');
         const submitBtn = document.getElementById('submitChecklistItem');

         if (modalForm) {
            modalForm.onsubmit = function(e) {
               e.preventDefault();
               submitChecklistItem(e);
               return false;
            };
         }

         if (submitBtn) {
            submitBtn.type = 'button';
            submitBtn.onclick = function(e) {
               e.preventDefault();
               submitChecklistItem(e);
               return false;
            };
         }
      });

      modalElement.addEventListener('hidden.bs.modal', function() {
         if (form) {
            form.reset();
            document.getElementById('submissionFormMessages').innerHTML = '';
            fileListPreview.innerHTML = '';
            document.getElementById('submissionAssignmentID').value = '';
         }
      });
   }
})();
</script>

