<div class="col-12">
   <div class="form-group d-none">
      <label for="entityID"> Entity ID</label>
      <input type="text" name="entityID" id="entityID" class="form-control form-control-sm" value="<?php echo $entityID; ?>" class="d-none">
      <label for="projectID"> ProjectId</label>
      <input type="text" name="projectID" id="projectID" class="form-control form-control-sm" value="<?php echo $projectID; ?>" class="d-none">
      <label for="billingRateID">Done By Id</label>
      <input type="text" name="doneByID" id="doneByID" class="form-control form-control-sm" value="<?= $userDetails->ID ?>" class="d-none">

   </div>
   <div class="form-group">
      <label for="rateName">Rate Name</label>
      <input type="text" name="rateName" id="rateName" class="form-control form-control-sm" value="" placeholder="input rate name" required>
   </div>
   <?php
   $rateType = Projects::billing_rate_type(array('Suspended' => 'N'), false, $DBConn);
   // var_dump($rateType);
   ?>
   <div class="form-group">
      <label for="rateType">Rate Type <span class="text-danger">*</span></label>
      <select name="billingRateTypeID" id="billingRateTypeID" class="form-control form-control-sm" required>
         <option value="">Select Rate Type</option>
         <?php
         if ($rateType && is_array($rateType) && count($rateType) > 0) {
            foreach ($rateType as $type) {
               $selected = (isset($billingRateTypeID) && $type->billingRateTypeID == $billingRateTypeID) ? 'selected' : '';
               $typeName = htmlspecialchars($type->billingRateTypeName ?? 'Unnamed Type');
               echo "<option value='{$type->billingRateTypeID}' data-type='{$typeName}' {$selected}>{$typeName}</option>";
            }
         } else {
            echo "<option value='' disabled>No Rate Types Available</option>";
         }?>
         <option value="addRateType">+ Add New Rate Type</option>
      </select>
      <small class="text-muted">Select an existing rate type or add a new one</small>
   </div>

   <!-- Inline Add Rate Type Form (hidden by default) -->
   <div id="addRateTypeForm" class="form-group border rounded p-3 bg-light" style="display: none;">
      <div class="d-flex justify-content-between align-items-center mb-2">
         <h6 class="mb-0 text-primary">
            <i class="ri-add-circle-line me-1"></i>Add New Rate Type
         </h6>
         <button type="button" class="btn-close" onclick="cancelAddRateType()" aria-label="Close"></button>
      </div>
      <div class="row g-2">
         <div class="col-12">
            <label for="newBillingRateTypeName" class="form-label small">Rate Type Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control form-control-sm" id="newBillingRateTypeName" placeholder="e.g., Standard Rate, Premium Rate" required>
         </div>
         <div class="col-12">
            <label for="newBillingRateTypeDescription" class="form-label small">Description</label>
            <textarea class="form-control form-control-sm" id="newBillingRateTypeDescription" rows="2" placeholder="Brief description of this rate type"></textarea>
         </div>
         <div class="col-12">
            <div class="d-flex gap-2">
               <button type="button" class="btn btn-primary btn-sm" onclick="saveNewRateType()">
                  <i class="ri-save-line me-1"></i>Save Rate Type
               </button>
               <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cancelAddRateType()">
                  <i class="ri-close-line me-1"></i>Cancel
               </button>
            </div>
         </div>
      </div>
   </div>
   <?php
    $workType = Data::work_types(array('Suspended' => 'N'), false, $DBConn); ?>

   <div class="form-group">
      <label for="workTypeID">Work Type</label>
      <select name="workTypeID" id="workTypeID" class="form-control form-control-sm" required>
         <option value="">Select Work Type</option>
         <?php

         // var_dump($workType);
         if ($workType) {
            foreach ($workType as $type) {
               echo "<option value='{$type->workTypeID}' >{$type->workTypeName}</option>";
            }
         } else {
            // echo "<option value=''>No Work Type Found</option>";
         }?>
         <option value="addWorkType" >Add New Work Type</option>
      </select>
   </div>
   <div class="form-group">
      <label for="hourlyRate">Hourly Rate Amount</label>
      <input type="text" name="hourlyRate" id="hourlyRate" class="form-control form-control-sm" placeholder="input hourly rate" required>
   </div>
</div>

<!-- JavaScript for Inline Rate Type Management -->
<script>
(function() {
    'use strict';

    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        const rateTypeSelect = document.getElementById('billingRateTypeID');
        const addRateTypeForm = document.getElementById('addRateTypeForm');

        if (!rateTypeSelect || !addRateTypeForm) {
            return; // Elements not found, skip initialization
        }

        // Handle dropdown change
        rateTypeSelect.addEventListener('change', function() {
            if (this.value === 'addRateType') {
                // Show inline form
                addRateTypeForm.style.display = 'block';
                // Remove required attribute temporarily to allow form display
                this.removeAttribute('required');
                // Scroll to form
                addRateTypeForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                // Hide inline form
                addRateTypeForm.style.display = 'none';
                // Restore required attribute
                this.setAttribute('required', 'required');
            }
        });
    });

    // Save new rate type function (global scope for onclick)
    window.saveNewRateType = function() {
        const nameInput = document.getElementById('newBillingRateTypeName');
        const descInput = document.getElementById('newBillingRateTypeDescription');
        const rateTypeSelect = document.getElementById('billingRateTypeID');
        const addRateTypeForm = document.getElementById('addRateTypeForm');

        if (!nameInput || !rateTypeSelect) {
            alert('Form elements not found');
            return;
        }

        const rateTypeName = nameInput.value.trim();
        const rateTypeDescription = descInput ? descInput.value.trim() : '';

        // Validation
        if (!rateTypeName) {
            alert('Please enter a rate type name');
            nameInput.focus();
            return;
        }

        // Disable form during submission
        const saveBtn = addRateTypeForm.querySelector('button[onclick="saveNewRateType()"]');
        const originalBtnText = saveBtn ? saveBtn.innerHTML : '';
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i>Saving...';
        }

        // Prepare form data
        const formData = new FormData();
        formData.append('action', 'addBillingRateType');
        formData.append('billingRateTypeName', rateTypeName);
        formData.append('billingRateTypeDescription', rateTypeDescription);

        // Get site URL - calculate from current location
        let baseUrl = '';
        const pathArray = window.location.pathname.split('/');
        const htmlIndex = pathArray.indexOf('html');
        if (htmlIndex > 0) {
            const basePath = pathArray.slice(0, htmlIndex).join('/') + '/';
            baseUrl = window.location.origin + basePath;
        } else {
            baseUrl = window.location.origin + '/';
        }
        if (!baseUrl.endsWith('/')) {
            baseUrl += '/';
        }

        // Send AJAX request
        fetch(baseUrl + 'php/scripts/projects/add_billing_rate_type.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Add new option to dropdown
                const newOption = document.createElement('option');
                newOption.value = data.billingRateTypeID;
                newOption.textContent = rateTypeName;
                newOption.setAttribute('selected', 'selected');

                // Insert before "Add New Rate Type" option
                const addOption = rateTypeSelect.querySelector('option[value="addRateType"]');
                if (addOption) {
                    rateTypeSelect.insertBefore(newOption, addOption);
                } else {
                    rateTypeSelect.appendChild(newOption);
                }

                // Hide inline form
                addRateTypeForm.style.display = 'none';

                // Reset form
                nameInput.value = '';
                if (descInput) {
                    descInput.value = '';
                }

                // Restore required attribute
                rateTypeSelect.setAttribute('required', 'required');

                // Show success message
                if (typeof showToast === 'function') {
                    showToast('Rate type added successfully!', 'success');
                } else {
                    alert('Rate type added successfully!');
                }
            } else {
                // Show error message
                const errorMsg = data.message || 'Failed to add rate type. Please try again.';
                if (typeof showToast === 'function') {
                    showToast(errorMsg, 'error');
                } else {
                    alert(errorMsg);
                }
            }
        })
        .catch(error => {
            console.error('Error adding rate type:', error);
            if (typeof showToast === 'function') {
                showToast('An error occurred. Please try again.', 'error');
            } else {
                alert('An error occurred. Please try again.');
            }
        })
        .finally(() => {
            // Restore button
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalBtnText;
            }
        });
    };

    // Cancel add rate type function
    window.cancelAddRateType = function() {
        const addRateTypeForm = document.getElementById('addRateTypeForm');
        const rateTypeSelect = document.getElementById('billingRateTypeID');
        const nameInput = document.getElementById('newBillingRateTypeName');
        const descInput = document.getElementById('newBillingRateTypeDescription');

        if (addRateTypeForm) {
            addRateTypeForm.style.display = 'none';
        }

        if (rateTypeSelect) {
            rateTypeSelect.value = '';
            rateTypeSelect.setAttribute('required', 'required');
        }

        // Reset form fields
        if (nameInput) {
            nameInput.value = '';
        }
        if (descInput) {
            descInput.value = '';
        }
    };
})();
</script>