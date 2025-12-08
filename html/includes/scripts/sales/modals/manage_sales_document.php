<?php
/**
 * Sales Document Upload/Edit Modal
 *
 * @package    Tija CRM
 * @subpackage Sales Management
 */

// Document categories
$documentCategories = array(
    'sales_agreement' => 'Sales Agreement',
    'tor' => 'Terms of Reference (TOR)',
    'proposal' => 'Proposal',
    'engagement_letter' => 'Engagement Letter',
    'confidentiality_agreement' => 'Confidentiality Agreement (NDA)',
    'expense_document' => 'Expense Document',
    'correspondence' => 'Correspondence',
    'meeting_notes' => 'Meeting Notes',
    'other' => 'Other'
);
?>

<div class="sales-document-form">
   <!-- Note: Form wrapper is created by form_modal_header, so we don't need another form tag -->
   <input type="hidden" name="salesCaseID" id="documentSalesCaseID" value="<?= $salesCaseDetails->salesCaseID ?? '' ?>">
   <input type="hidden" name="documentID" id="documentID" value="">
   <input type="hidden" name="action" id="documentAction" value="upload">
   <input type="hidden" name="salesStage" id="documentSalesStage" value="<?= $salesCaseDetails->saleStage ?? '' ?>">
   <input type="hidden" name="saleStatusLevelID" id="documentSaleStatusLevelID" value="<?= $salesCaseDetails->saleStatusLevelID ?? '' ?>">

      <!-- Document Name -->
      <div class="form-group mb-3">
         <label for="documentName" class="form-label fw-semibold">
            Document Name <span class="text-danger">*</span>
         </label>
         <input type="text"
                class="form-control"
                id="documentName"
                name="documentName"
                placeholder="Enter document name"
                required>
         <small class="text-muted">A descriptive name for this document</small>
      </div>

      <!-- Document Category -->
      <div class="form-group mb-3">
         <label for="documentCategory" class="form-label fw-semibold">
            Document Category <span class="text-danger">*</span>
         </label>
         <select class="form-select" id="documentCategory" name="documentCategory" required>
            <option value="">Select Category</option>
            <?php foreach($documentCategories as $key => $label): ?>
               <option value="<?= $key ?>"><?= htmlspecialchars($label) ?></option>
            <?php endforeach; ?>
         </select>
      </div>

      <!-- Document Type (Optional) -->
      <div class="form-group mb-3">
         <label for="documentType" class="form-label fw-semibold">Document Type</label>
         <input type="text"
                class="form-control"
                id="documentType"
                name="documentType"
                placeholder="e.g., Final Agreement, Draft Proposal, etc.">
         <small class="text-muted">Optional: Specify document type or version</small>
      </div>

      <!-- Document Stage -->
      <div class="form-group mb-3">
         <label for="documentStage" class="form-label fw-semibold">Document Stage</label>
         <select class="form-select" id="documentStage" name="documentStage">
            <option value="">Not specified</option>
            <option value="draft">Draft</option>
            <option value="revision">Under Revision</option>
            <option value="final">Final Version</option>
            <option value="approved">Approved</option>
            <option value="signed">Signed</option>
         </select>
         <small class="text-muted">Maturity stage of this document</small>
      </div>

      <!-- Sales Stage Display (Read-only) -->
      <div class="form-group mb-3">
         <label class="form-label fw-semibold">
            <i class="ri-flag-line me-1"></i>Current Sales Stage
         </label>
         <div class="card bg-light border-0">
            <div class="card-body py-2">
               <div class="d-flex align-items-center">
                  <span class="badge <?=
                     $salesCaseDetails->saleStage === 'Lead' ? 'bg-info' :
                     ($salesCaseDetails->saleStage === 'Opportunity' ? 'bg-primary' :
                     ($salesCaseDetails->saleStage === 'Proposal' ? 'bg-warning' : 'bg-success'))
                  ?> me-2">
                     <?= htmlspecialchars($salesCaseDetails->saleStage ?? 'Unknown') ?>
                  </span>
                  <small class="text-muted">
                     Document will be tagged with this stage for tracking
                  </small>
               </div>
            </div>
         </div>
      </div>

      <!-- File Upload -->
      <div class="form-group mb-3">
         <label for="documentFile" class="form-label fw-semibold">
            Select File <span class="text-danger">*</span>
         </label>
         <input type="file"
                class="form-control"
                id="documentFile"
                name="documentFile"
                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif"
                required>
         <small class="text-muted">
            Allowed: PDF, Word, Excel, PowerPoint, Images, TXT (Max: 50MB)
         </small>
         <div id="filePreview" class="mt-2" style="display: none;">
            <div class="alert alert-info d-flex align-items-center">
               <i class="ri-file-line me-2"></i>
               <span id="fileNamePreview"></span>
               <button type="button" class="btn btn-sm btn-link ms-auto" onclick="clearFileSelection()">
                  <i class="ri-close-line"></i>
               </button>
            </div>
         </div>
      </div>

      <!-- Description -->
      <div class="form-group mb-3">
         <label for="documentDescription" class="form-label fw-semibold">Description</label>
         <textarea class="form-control"
                   id="documentDescription"
                   name="description"
                   rows="3"
                   placeholder="Optional description or notes about this document"></textarea>
      </div>

      <!-- Tags -->
      <div class="form-group mb-3">
         <label for="documentTags" class="form-label fw-semibold">Tags</label>
         <input type="text"
                class="form-control"
                id="documentTags"
                name="tags"
                placeholder="e.g., contract, pricing, terms, final">
         <small class="text-muted">Comma-separated tags for easy searching (e.g., contract, pricing, final)</small>
      </div>

      <!-- Expiry Date (for time-sensitive documents) -->
      <div class="form-group mb-3">
         <label for="documentExpiryDate" class="form-label fw-semibold">Expiry Date (Optional)</label>
         <input type="text"
                class="form-control"
                id="documentExpiryDate"
                name="expiryDate"
                placeholder="Select expiry date">
         <small class="text-muted">For proposals, quotes, or time-sensitive documents</small>
      </div>

      <!-- Options Row -->
      <div class="row g-3 mb-3">
         <!-- Confidential -->
         <div class="col-md-4">
            <div class="form-check form-switch">
               <input class="form-check-input"
                      type="checkbox"
                      id="isConfidential"
                      name="isConfidential"
                      value="Y">
               <label class="form-check-label" for="isConfidential">
                  <i class="ri-lock-line me-1"></i>Confidential
               </label>
            </div>
         </div>

         <!-- Public to Client -->
         <div class="col-md-4">
            <div class="form-check form-switch">
               <input class="form-check-input"
                      type="checkbox"
                      id="isPublic"
                      name="isPublic"
                      value="Y">
               <label class="form-check-label" for="isPublic">
                  <i class="ri-eye-line me-1"></i>Visible to Client
               </label>
            </div>
         </div>
      </div>

      <!-- Shared with Client Section -->
      <div class="row g-3 mb-3">
         <div class="col-md-6">
            <div class="form-check form-switch">
               <input class="form-check-input"
                      type="checkbox"
                      id="sharedWithClient"
                      name="sharedWithClient"
                      value="Y">
               <label class="form-check-label" for="sharedWithClient">
                  <i class="ri-share-line me-1"></i>Already Shared with Client
               </label>
            </div>
         </div>
         <div class="col-md-6" id="sharedDateField" style="display: none;">
            <label for="documentSharedDate" class="form-label small mb-1">Shared Date</label>
            <input type="text"
                   class="form-control form-control-sm"
                   id="documentSharedDate"
                   name="sharedDate"
                   placeholder="Select date">
         </div>
      </div>

      <!-- Version Information -->
      <div class="row g-3 mb-3">
         <div class="col-md-6">
            <label for="documentVersion" class="form-label fw-semibold">Version</label>
            <input type="text"
                   class="form-control"
                   id="documentVersion"
                   name="version"
                   placeholder="e.g., 1.0, 2.1, Final"
                   value="1.0">
            <small class="text-muted">Document version number</small>
         </div>
         <div class="col-md-6">
            <label for="linkedActivityID" class="form-label fw-semibold">Link to Activity</label>
            <select class="form-select" id="linkedActivityID" name="linkedActivityID">
               <option value="">Not linked</option>
               <?php if(isset($activities) && $activities): ?>
                  <?php foreach($activities as $activity): ?>
                     <option value="<?= $activity->activityID ?>"><?= htmlspecialchars($activity->activityName) ?> (<?= Utility::date_format($activity->activityDate) ?>)</option>
                  <?php endforeach; ?>
               <?php endif; ?>
            </select>
            <small class="text-muted">Activity where document was created</small>
         </div>

         <!-- Requires Approval -->
         <div class="col-md-4">
            <div class="form-check form-switch">
               <input class="form-check-input"
                      type="checkbox"
                      id="requiresApproval"
                      name="requiresApproval"
                      value="Y">
               <label class="form-check-label" for="requiresApproval">
                  <i class="ri-checkbox-circle-line me-1"></i>Requires Approval
               </label>
            </div>
         </div>
      </div>

      <!-- Proposal Link (Optional) -->
      <?php if(isset($proposals) && !empty($proposals)): ?>
      <div class="form-group mb-3">
         <label for="proposalID" class="form-label fw-semibold">Link to Proposal (Optional)</label>
         <select class="form-select" id="proposalID" name="proposalID">
            <option value="">None</option>
            <?php foreach($proposals as $proposal): ?>
               <option value="<?= $proposal->proposalID ?>"><?= htmlspecialchars($proposal->proposalTitle) ?></option>
            <?php endforeach; ?>
         </select>
      </div>
      <?php endif; ?>

      <!-- Error/Success Messages -->
      <div id="documentFormMessages" class="mt-3"></div>
</div>

<script>
(function() {
   'use strict';

   // Get the form from the modal (created by form_modal_header)
   const modal = document.getElementById('manageSalesDocumentModal');
   const form = modal ? modal.querySelector('form') : null;
   const fileInput = document.getElementById('documentFile');
   const filePreview = document.getElementById('filePreview');
   const fileNamePreview = document.getElementById('fileNamePreview');

   // File selection preview
   if (fileInput) {
      fileInput.addEventListener('change', function(e) {
         const file = e.target.files[0];
         if (file) {
            fileNamePreview.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
            filePreview.style.display = 'block';
         } else {
            filePreview.style.display = 'none';
         }
      });
   }

   // Format file size
   function formatFileSize(bytes) {
      if (bytes === 0) return '0 Bytes';
      const k = 1024;
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(k));
      return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
   }

   // Clear file selection
   window.clearFileSelection = function() {
      if (fileInput) {
         fileInput.value = '';
         filePreview.style.display = 'none';
      }
   };

   // Form submission handler - Make it globally accessible
   window.submitSalesDocument = function(e) {
      // Prevent default form submission
      if (e) {
         e.preventDefault();
         e.stopPropagation();
      }

      // Get the form from modal
      const modalForm = document.getElementById('manageSalesDocumentModal')?.querySelector('form');
      if (!modalForm) {
         console.error('Form not found in modal');
         alert('Form not found. Please refresh the page.');
         return false;
      }

      // Validate required fields
      const documentName = document.getElementById('documentName')?.value?.trim();
      const documentCategory = document.getElementById('documentCategory')?.value;
      const documentFile = document.getElementById('documentFile')?.files?.[0];

      if (!documentName) {
         if (typeof showToast === 'function') {
            showToast('Please enter a document name', 'error');
         }
         document.getElementById('documentName')?.focus();
         return false;
      }

      if (!documentCategory) {
         if (typeof showToast === 'function') {
            showToast('Please select a document category', 'error');
         }
         document.getElementById('documentCategory')?.focus();
         return false;
      }

      if (!documentFile) {
         if (typeof showToast === 'function') {
            showToast('Please select a file to upload', 'error');
         }
         document.getElementById('documentFile')?.focus();
         return false;
      }

      const formData = new FormData(modalForm);
      const action = document.getElementById('documentAction')?.value || 'upload';
      formData.append('action', action);

      // Show loading state
      const submitBtn = document.getElementById('submitSalesDocument') ||
                        document.querySelector('#manageSalesDocumentModal .btn-success') ||
                        document.querySelector('#manageSalesDocumentModal .btn-primary');
      const originalBtnText = submitBtn ? submitBtn.innerHTML : '';
      if (submitBtn) {
         submitBtn.disabled = true;
         submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';
      }

      fetch('<?= $base ?>php/scripts/sales/manage_sales_document.php', {
         method: 'POST',
         body: formData
      })
      .then(response => {
         if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
         }
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
         if (data.success) {
            // Show success message
            if (typeof showToast === 'function') {
               showToast(data.message || 'Document saved successfully', 'success');
            } else {
               alert(data.message || 'Document saved successfully');
            }

            // Reset button state immediately
            if (submitBtn && originalBtnText) {
               submitBtn.disabled = false;
               submitBtn.innerHTML = originalBtnText;
            }

            // Close modal and reload after a short delay
            setTimeout(function() {
               // Close modal using Bootstrap's method
               const modalElement = document.getElementById('manageSalesDocumentModal');
               if (modalElement) {
                  const bsModal = bootstrap.Modal.getInstance(modalElement);
                  if (bsModal) {
                     bsModal.hide();
                  } else {
                     // Create new instance if it doesn't exist
                     const newModalInstance = new bootstrap.Modal(modalElement);
                     newModalInstance.hide();
                  }

                  // Also ensure modal is fully closed (backup method)
                  setTimeout(function() {
                     modalElement.classList.remove('show');
                     modalElement.style.display = 'none';
                     document.body.classList.remove('modal-open');
                     const backdrop = document.querySelector('.modal-backdrop');
                     if (backdrop) {
                        backdrop.remove();
                     }
                  }, 100);
               }

               // Reload page to show new document
               setTimeout(function() {
                  location.reload();
               }, 400);
            }, 300);
         } else {
            // Show error
            const messagesDiv = document.getElementById('documentFormMessages');
            if (messagesDiv) {
               messagesDiv.innerHTML = '<div class="alert alert-danger">' +
                  (data.message || 'An error occurred. Please try again.') +
                  '</div>';
            }

            if (typeof showToast === 'function') {
               showToast(data.message || 'Failed to save document', 'error');
            }
         }
      })
      .catch(error => {
         console.error('Error:', error);
         const messagesDiv = document.getElementById('documentFormMessages');
         if (messagesDiv) {
            messagesDiv.innerHTML = '<div class="alert alert-danger">Network error. Please check your connection and try again.</div>';
         }
         if (typeof showToast === 'function') {
            showToast('Network error. Please try again.', 'error');
         }
      })
      .finally(() => {
         // Reset button
         if (submitBtn && originalBtnText) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
         }
      });

      return false;
   };

   // Initialize Date Pickers
   function initializeDocumentDatePickers() {
      if (typeof flatpickr === 'undefined') {
         console.warn('Flatpickr not loaded. Retrying...');
         setTimeout(initializeDocumentDatePickers, 100);
         return;
      }

      // Expiry Date Picker
      const expiryDateInput = document.getElementById('documentExpiryDate');
      if (expiryDateInput && !expiryDateInput._flatpickr) {
         flatpickr(expiryDateInput, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'F j, Y',
            allowInput: true,
            minDate: 'today'
         });
      }

      // Shared Date Picker
      const sharedDateInput = document.getElementById('documentSharedDate');
      if (sharedDateInput && !sharedDateInput._flatpickr) {
         flatpickr(sharedDateInput, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'F j, Y',
            allowInput: true,
            maxDate: 'today'
         });
      }
   }

   // Handle shared with client checkbox
   function initializeConditionalFields() {
      const sharedCheckbox = document.getElementById('sharedWithClient');
      const sharedDateField = document.getElementById('sharedDateField');

      if (sharedCheckbox && sharedDateField) {
         sharedCheckbox.addEventListener('change', function() {
            sharedDateField.style.display = this.checked ? 'block' : 'none';
         });
      }
   }

   // Initialize handlers when modal is shown
   const modalElement = document.getElementById('manageSalesDocumentModal');
   if (modalElement) {
      modalElement.addEventListener('shown.bs.modal', function() {
         // Get form and button after modal is shown
         const modalForm = this.querySelector('form');
         const submitBtn = document.getElementById('submitSalesDocument');

         if (!modalForm) {
            console.error('Form not found in modal');
            return;
         }

         // Remove form action to prevent default submission
         modalForm.action = 'javascript:void(0);';
         modalForm.method = 'post';

         // Prevent default form submission
         modalForm.onsubmit = function(e) {
            e.preventDefault();
            e.stopPropagation();
            submitSalesDocument(e);
            return false;
         };

         // Change button type and attach click handler
         if (submitBtn) {
            submitBtn.type = 'button';
            submitBtn.onclick = function(e) {
               e.preventDefault();
               e.stopPropagation();
               submitSalesDocument(e);
               return false;
            };
         } else {
            console.error('Submit button not found');
         }

         // Initialize date pickers and conditional fields
         setTimeout(() => {
            initializeDocumentDatePickers();
            initializeConditionalFields();
         }, 100);
      });
   }

   // Reset form when modal is hidden (using same modalElement)
   if (modalElement) {
      modalElement.addEventListener('hidden.bs.modal', function() {
         const modalForm = this.querySelector('form');
         if (modalForm) {
            modalForm.reset();
            if (filePreview) filePreview.style.display = 'none';
            const messagesDiv = document.getElementById('documentFormMessages');
            if (messagesDiv) messagesDiv.innerHTML = '';
            const docID = document.getElementById('documentID');
            if (docID) docID.value = '';
            const docAction = document.getElementById('documentAction');
            if (docAction) docAction.value = 'upload';
         }
      });
   }
})();
</script>

<style>
.sales-document-form .form-label {
   font-size: 0.875rem;
   margin-bottom: 0.5rem;
}

.sales-document-form .form-check-label {
   font-size: 0.875rem;
   cursor: pointer;
}

#filePreview .alert {
   margin-bottom: 0;
   padding: 0.75rem;
}
</style>

