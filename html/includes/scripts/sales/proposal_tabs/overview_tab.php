<?php
/**
 * Proposal Details - Overview Tab
 * Contains the basic proposal information form with edit functionality
 */
?>

<!-- Basic Information Section -->
<div class="proposal-section-card">
   <div class="proposal-section-header">
      <h5 class="proposal-section-title">
         <i class="ri-information-line"></i>
         Basic Information
      </h5>
      <div>
         <button type="button" class="btn btn-sm btn-outline-primary editProposalDetailsBtn">
            <i class="ri-pencil-line me-1"></i> Edit
         </button>
      </div>
   </div>
   <div class="proposal-section-body">
      <!-- Edit Mode Indicator -->
      <div id="editModeIndicator" class="alert alert-info d-none mb-3" role="alert">
         <i class="ri-edit-2-line me-2"></i>
         <strong>Edit Mode Active</strong> - Make your changes and click "Save Changes" when done, or "Cancel" to discard.
      </div>

      <form id="proposalDetailsForm" action="<?= $base ?>php/scripts/sales/manage_proposal.php" method="POST">
         <input type="hidden" name="proposalID" value="<?= $proposalDetails->proposalID ?>">
         <input type="hidden" name="orgDataID" value="<?= $proposalDetails->orgDataID ?>">
         <input type="hidden" name="entityID" value="<?= $proposalDetails->entityID ?>">
         <input type="hidden" name="employeID" value="<?= $proposalDetails->employeeID ?>">

         <div class="row g-4">
            <!-- Section 1: Core Information -->
            <div class="col-12">
               <div class="row g-3">
                  <!-- Proposal Title -->
                  <div class="col-md-6 col-lg-4">
                     <label for="proposalTitle" class="form-label text-primary fw-medium">
                        <i class="ri-file-text-line text-primary me-1"></i>
                        Proposal Title <span class="text-danger">*</span>
                     </label>
                     <input type="text"
                            class="form-control"
                            id="proposalTitle"
                            name="proposalTitle"
                            value="<?= htmlspecialchars($proposalDetails->proposalTitle) ?>"
                            readonly>
                     <small class="text-muted">A descriptive title for this proposal</small>
                  </div>

                  <!-- Client Name -->
                  <div class="col-md-6 col-lg-4">
                     <label for="clientID" class="form-label text-primary fw-medium">
                        <i class="ri-building-line text-primary me-1"></i>
                        Client Name <span class="text-danger">*</span>
                     </label>
                     <select class="form-select" id="clientID" name="clientID" readonly>
                        <option value="<?= $proposalDetails->clientID ?>"><?= htmlspecialchars($proposalDetails->clientName) ?></option>
                        <?php if($clients): ?>
                           <?php foreach ($clients as $client): ?>
                              <?php if($client->clientID != $proposalDetails->clientID): ?>
                                 <option value="<?= $client->clientID ?>"><?= htmlspecialchars($client->clientName) ?></option>
                              <?php endif; ?>
                           <?php endforeach; ?>
                        <?php endif; ?>
                     </select>
                     <small class="text-muted">The organization this proposal is for</small>
                  </div>

                  <!-- Sales Case -->
                  <div class="col-md-6 col-lg-4">
                     <label for="salesCaseID" class="form-label text-primary fw-medium">
                        <i class="ri-briefcase-line text-primary me-1"></i>
                        Sales Case
                     </label>
                     <select class="form-select" id="salesCaseID" name="salesCaseID" readonly>
                        <?= Form::populate_select_element_from_object($salesCases, 'salesCaseID', 'salesCaseName', isset($proposalDetails->salesCaseID) ? $proposalDetails->salesCaseID : '', '', 'Select Sales Case') ?>
                     </select>
                     <small class="text-muted">Link to an existing sales opportunity</small>
                  </div>
               </div>
            </div>

            <!-- Section 2: Status & Value -->
            <div class="col-12">
               <div class="row g-3">
                  <!-- Proposal Deadline -->
                  <div class="col-md-6 col-lg-4">
                     <label for="proposalDeadline" class="form-label text-primary fw-medium">
                        <i class="ri-calendar-event-line text-primary me-1"></i>
                        Submission Deadline <span class="text-danger">*</span>
                     </label>
                     <input type="date"
                            class="form-control date"
                            id="proposalDeadline"
                            name="proposalDeadline"
                            value="<?= $proposalDetails->proposalDeadline ?>"
                            readonly>
                     <small class="text-muted">Final date for proposal submission</small>
                  </div>

                  <!-- Proposal Value -->
                  <div class="col-md-6 col-lg-4">
                     <label for="proposalValue" class="form-label text-primary fw-medium">
                        <i class="ri-money-dollar-circle-line text-primary me-1"></i>
                        Proposal Value (KES) <span class="text-danger">*</span>
                     </label>
                     <div class="input-group">
                        <span class="input-group-text">KES</span>
                        <input type="number"
                               class="form-control"
                               id="proposalValue"
                               name="proposalValue"
                               value="<?= $proposalDetails->proposalValue ?>"
                               min="0"
                               step="0.01"
                               readonly>
                     </div>
                     <small class="text-muted">Total monetary value of this proposal</small>
                  </div>

                  <!-- Proposal Status -->
                  <div class="col-md-6 col-lg-4">
                     <label for="proposalStatusID" class="form-label text-primary fw-medium">
                        <i class="ri-flag-line text-primary me-1"></i>
                        Status <span class="text-danger">*</span>
                     </label>
                     <select class="form-select" id="proposalStatusID" name="proposalStatusID" readonly>
                        <?php if($proposalStatuses): ?>
                           <?php foreach ($proposalStatuses as $status): ?>
                              <option value="<?= $status->proposalStatusID ?>" <?= ($status->proposalStatusID == $proposalDetails->proposalStatusID) ? 'selected' : '' ?>>
                                 <?= htmlspecialchars($status->proposalStatusName) ?>
                              </option>
                           <?php endforeach; ?>
                        <?php endif; ?>
                     </select>
                     <small class="text-muted">Current stage of this proposal</small>
                  </div>
               </div>
            </div>

            <!-- Section 3: Description & Notes -->
            <div class="col-12">
               <div class="row g-3">
                  <!-- Proposal Description -->
                  <div class="col-md-6">
                     <label for="proposalDescription" class="form-label text-primary fw-medium">
                        <i class="ri-article-line text-primary me-1"></i>
                        Description
                     </label>
                     <textarea class="form-control"
                               id="proposalDescription"
                               name="proposalDescription"
                               rows="4"
                               maxlength="2000"
                               placeholder="Describe the proposal scope and objectives..."
                               readonly><?= htmlspecialchars($proposalDetails->proposalDescription) ?></textarea>
                     <div class="d-flex justify-content-between">
                        <small class="text-muted">Overview of proposal scope and deliverables</small>
                        <small class="text-muted"><span id="descriptionCounter">0</span>/2000</small>
                     </div>
                  </div>

                  <!-- Internal Comments -->
                  <div class="col-md-6">
                     <label for="proposalComments" class="form-label text-primary fw-medium">
                        <i class="ri-chat-3-line text-primary me-1"></i>
                        Internal Comments
                     </label>
                     <textarea class="form-control"
                               id="proposalComments"
                               name="proposalComments"
                               rows="4"
                               maxlength="1000"
                               placeholder="Add internal notes or team communications..."
                               readonly><?= htmlspecialchars($proposalDetails->proposalComments) ?></textarea>
                     <div class="d-flex justify-content-between">
                        <small class="text-muted"><i class="ri-lock-line"></i> Internal notes (not shared with clients)</small>
                        <small class="text-muted"><span id="commentsCounter">0</span>/1000</small>
                     </div>
                  </div>
               </div>
            </div>

            <!-- Action Buttons (hidden until edit mode) -->
            <div class="col-12 d-none submitButton" id="formActionButtons">
               <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                  <button type="button" class="btn btn-outline-secondary" id="cancelEditBtn">
                     <i class="ri-close-line me-1"></i> Cancel
                  </button>
                  <button type="submit" class="btn btn-primary">
                     <i class="ri-save-line me-1"></i> Save Changes
                  </button>
               </div>
            </div>
         </div>
      </form>
   </div>
</div>

<!-- Overview Form Script -->
<script>
document.addEventListener("DOMContentLoaded", function() {
   const editButton = document.querySelector('.editProposalDetailsBtn');
   const form = document.getElementById('proposalDetailsForm');
   const editModeIndicator = document.getElementById('editModeIndicator');
   const formActionButtons = document.getElementById('formActionButtons');
   const cancelEditBtn = document.getElementById('cancelEditBtn');

   let originalValues = {};

   // Character counter
   function updateCharCounter(textarea, counterId, maxLength) {
      const counter = document.getElementById(counterId);
      if (textarea && counter) {
         const len = textarea.value.length;
         counter.textContent = len;
         counter.classList.toggle('text-danger', len > maxLength * 0.9);
      }
   }

   const descriptionTextarea = document.getElementById('proposalDescription');
   const commentsTextarea = document.getElementById('proposalComments');

   if (descriptionTextarea) {
      updateCharCounter(descriptionTextarea, 'descriptionCounter', 2000);
      descriptionTextarea.addEventListener('input', () => updateCharCounter(descriptionTextarea, 'descriptionCounter', 2000));
   }
   if (commentsTextarea) {
      updateCharCounter(commentsTextarea, 'commentsCounter', 1000);
      commentsTextarea.addEventListener('input', () => updateCharCounter(commentsTextarea, 'commentsCounter', 1000));
   }

   function storeOriginalValues() {
      form.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach(el => {
         originalValues[el.name] = el.value;
      });
   }

   function restoreOriginalValues() {
      form.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach(el => {
         if (originalValues[el.name] !== undefined) {
            el.value = originalValues[el.name];
         }
      });
   }

   function enableEditMode() {
      storeOriginalValues();
      if (editModeIndicator) editModeIndicator.classList.remove('d-none');
      form.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach(el => {
         el.removeAttribute('readonly');
         el.classList.add('border-primary');
      });
      if (formActionButtons) formActionButtons.classList.remove('d-none');
      if (editButton) editButton.style.display = 'none';
   }

   function disableEditMode() {
      restoreOriginalValues();
      if (editModeIndicator) editModeIndicator.classList.add('d-none');
      form.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach(el => {
         el.setAttribute('readonly', 'readonly');
         el.classList.remove('border-primary');
      });
      if (formActionButtons) formActionButtons.classList.add('d-none');
      if (editButton) editButton.style.display = '';
   }

   if (editButton) {
      editButton.addEventListener('click', e => { e.preventDefault(); enableEditMode(); });
   }

   if (cancelEditBtn) {
      cancelEditBtn.addEventListener('click', e => {
         e.preventDefault();
         const hasChanges = Object.keys(originalValues).some(key => {
            const el = form.querySelector(`[name="${key}"]`);
            return el && el.value !== originalValues[key];
         });
         if (hasChanges) {
            if (confirm('Discard unsaved changes?')) disableEditMode();
         } else {
            disableEditMode();
         }
      });
   }

   document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && formActionButtons && !formActionButtons.classList.contains('d-none')) {
         cancelEditBtn.click();
      }
   });
});
</script>
