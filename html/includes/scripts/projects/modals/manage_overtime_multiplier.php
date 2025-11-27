<div id="manage_overtime_multiplier_form">
   <!-- Hidden Fields -->
   <input type="hidden" name="overtimeMultiplierID" id="overtimeMultiplierID" value="">
   <input type="hidden" name="projectID" id="projectID" value="<?php echo htmlspecialchars($projectID ?? ''); ?>">
   <input type="hidden" name="entityID" id="entityID" value="<?php echo htmlspecialchars($entityID ?? ''); ?>">
   <input type="hidden" name="doneByID" id="doneByID" value="<?= $userDetails->ID ?? '' ?>">

   <!-- Overtime Multiplier Name -->
   <div class="form-group mb-3">
      <label for="overtimeMultiplierName" class="form-label">
         Overtime Multiplier Name <span class="text-danger">*</span>
      </label>
      <input type="text"
             name="overtimeMultiplierName"
             id="overtimeMultiplierName"
             class="form-control form-control-sm"
             placeholder="e.g., Standard Overtime, Weekend Overtime"
             required>
      <small class="form-text text-muted">Enter a descriptive name for this overtime multiplier</small>
   </div>

   <!-- Multiplier Rate -->
   <div class="form-group mb-3">
      <label for="multiplierRate" class="form-label">
         Multiplier Rate <span class="text-danger">*</span>
      </label>
      <div class="input-group">
         <input type="number"
                name="multiplierRate"
                id="multiplierRate"
                class="form-control form-control-sm"
                placeholder="1.5"
                step="0.01"
                min="0.01"
                required>
         <span class="input-group-text bg-light">
            <i class="ri-percent-line me-1"></i>
            <small class="text-muted">Multiplier</small>
         </span>
      </div>
      <small class="form-text text-muted">
         <i class="ri-information-line me-1"></i>
         Enter the multiplier rate (e.g., 1.5 for 1.5x, 2.0 for 2x). This will be applied to the base hourly rate.
      </small>
   </div>

   <!-- Applicable Work Types -->
   <div class="form-group mb-3">
      <label for="workTypeID" class="form-label d-flex align-items-center justify-content-between">
         <span>
            Applicable Work Types <span class="text-danger">*</span>
         </span>
         <button type="button"
                 class="btn btn-sm btn-link p-0 text-decoration-none"
                 data-bs-toggle="tooltip"
                 data-bs-placement="top"
                 title="Select one or more work types that this overtime multiplier applies to">
            <i class="ri-question-line text-muted"></i>
         </button>
      </label>

      <select name="workTypeID[]"
              id="workTypeID"
              class="form-control form-control-sm"
              multiple
              required
              data-placeholder="Select work types..."
              style="min-height: 120px;">
         <?php
         if (isset($workType) && $workType && is_array($workType) && count($workType) > 0) {
            foreach ($workType as $type) {
               $workTypeID = htmlspecialchars($type->workTypeID ?? '');
               $workTypeName = htmlspecialchars($type->workTypeName ?? 'Unnamed Type');
               echo "<option value='{$workTypeID}'>{$workTypeName}</option>";
            }
         } else {
            echo "<option value='' disabled>No work types available</option>";
         }
         ?>
      </select>

      <div class="mt-2">
         <small class="form-text text-muted d-flex align-items-start">
            <i class="ri-information-line me-1 mt-1"></i>
            <span>Select one or more work types that this overtime multiplier will apply to. You can select multiple work types by holding Ctrl (Windows) or Cmd (Mac) while clicking, or use the search feature.</span>
         </small>
      </div>

      <!-- Selected Work Types Display -->
      <div id="selectedWorkTypesDisplay" class="mt-2 d-none">
         <div class="d-flex flex-wrap gap-2" id="selectedWorkTypesBadges"></div>
      </div>
   </div>

   <!-- Help Card -->
   <div class="alert alert-info d-flex align-items-start mb-0" role="alert">
      <i class="ri-lightbulb-line me-2 mt-1"></i>
      <div class="flex-grow-1">
         <strong>How Overtime Multipliers Work:</strong>
         <ul class="mb-0 small">
            <li>The multiplier rate is applied to the base hourly rate for selected work types</li>
            <li>Example: If base rate is KES 1,000/hour and multiplier is 1.5, overtime rate = KES 1,500/hour</li>
            <li>You can apply the same multiplier to multiple work types</li>
            <li>Each work type can have different overtime multipliers</li>
         </ul>
      </div>
   </div>
</div>

<!-- Work Type Selection Enhancement Script -->
<script>
(function() {
    'use strict';

    // Initialize Choices.js for work type selection when modal opens
    let workTypeChoices = null;

    const overtimeModal = document.getElementById('manage_overtime_multiplier');
    if (overtimeModal) {
        overtimeModal.addEventListener('shown.bs.modal', function() {
            const workTypeSelect = document.getElementById('workTypeID');

            if (workTypeSelect && !workTypeChoices) {
                // Initialize Choices.js with better configuration
                workTypeChoices = new Choices(workTypeSelect, {
                    removeItemButton: true,
                    searchEnabled: true,
                    searchChoices: true,
                    searchFields: ['label', 'value'],
                    placeholder: true,
                    placeholderValue: 'Select work types...',
                    searchPlaceholderValue: 'Search work types...',
                    allowHTML: true,
                    duplicateItemsAllowed: false,
                    itemSelectText: 'Click to select',
                    noResultsText: 'No work types found',
                    noChoicesText: 'No work types available',
                    maxItemText: 'Maximum {count} work types can be selected',
                    addItemText: 'Press Enter to add <b>"{value}"</b>',
                    uniqueItemText: 'Only unique work types can be added',
                    classNames: {
                        containerOuter: 'choices',
                        containerInner: 'choices__inner',
                        input: 'choices__input',
                        inputCloned: 'choices__input--cloned',
                        list: 'choices__list',
                        listItems: 'choices__list--multiple',
                        listSingle: 'choices__list--single',
                        listDropdown: 'choices__list--dropdown',
                        item: 'choices__item',
                        itemSelectable: 'choices__item--selectable',
                        itemDisabled: 'choices__item--disabled',
                        itemChoice: 'choices__item--choice',
                        placeholder: 'choices__placeholder',
                        group: 'choices__group',
                        groupHeading: 'choices__heading',
                        button: 'choices__button',
                        activeState: 'is-active',
                        focusState: 'is-focused',
                        openState: 'is-open',
                        disabledState: 'is-disabled',
                        highlightedState: 'is-highlighted',
                        selectedState: 'is-selected',
                        flippedState: 'is-flipped',
                        loadingState: 'is-loading',
                        noResults: 'has-no-results',
                        noChoices: 'has-no-choices'
                    }
                });

                // Update selected work types display
                workTypeSelect.addEventListener('change', function() {
                    updateSelectedWorkTypesDisplay();
                });

                // Initial display update
                updateSelectedWorkTypesDisplay();
            }
        });

        // Clean up when modal is hidden
        overtimeModal.addEventListener('hidden.bs.modal', function() {
            if (workTypeChoices) {
                workTypeChoices.destroy();
                workTypeChoices = null;
            }

            // Reset form
            const form = document.getElementById('manage_overtime_multiplier_form');
            if (form) {
                form.reset();
                document.getElementById('selectedWorkTypesDisplay').classList.add('d-none');
            }
        });
    }

    // Function to update selected work types display
    function updateSelectedWorkTypesDisplay() {
        const workTypeSelect = document.getElementById('workTypeID');
        const displayDiv = document.getElementById('selectedWorkTypesDisplay');
        const badgesDiv = document.getElementById('selectedWorkTypesBadges');

        if (!workTypeSelect || !displayDiv || !badgesDiv) return;

        const selectedOptions = Array.from(workTypeSelect.selectedOptions);

        if (selectedOptions.length > 0) {
            badgesDiv.innerHTML = '';
            selectedOptions.forEach(option => {
                if (option.value) {
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-primary';
                    badge.innerHTML = `
                        ${option.textContent}
                        <button type="button"
                                class="btn-close btn-close-white ms-1"
                                style="font-size: 0.7em;"
                                onclick="removeWorkType('${option.value}')"
                                aria-label="Remove ${option.textContent}"></button>
                    `;
                    badgesDiv.appendChild(badge);
                }
            });
            displayDiv.classList.remove('d-none');
        } else {
            displayDiv.classList.add('d-none');
        }
    }

    // Function to remove work type
    window.removeWorkType = function(workTypeID) {
        const workTypeSelect = document.getElementById('workTypeID');
        if (workTypeSelect && workTypeChoices) {
            workTypeChoices.removeActiveItemsByValue(workTypeID);
            updateSelectedWorkTypesDisplay();
        }
    };

    // Load existing data when editing
    window.loadOvertimeMultiplierData = function(data) {
        if (!data) return;

        // Set form values
        if (data.overtimeMultiplierID) {
            document.getElementById('overtimeMultiplierID').value = data.overtimeMultiplierID;
        }
        if (data.overtimeMultiplierName) {
            document.getElementById('overtimeMultiplierName').value = data.overtimeMultiplierName;
        }
        if (data.multiplierRate) {
            document.getElementById('multiplierRate').value = data.multiplierRate;
        }

        // Set work types
        if (data.workTypeID && workTypeChoices) {
            const workTypeIDs = data.workTypeID.split(',').map(id => id.trim()).filter(id => id);
            workTypeChoices.setValue(workTypeIDs);
            updateSelectedWorkTypesDisplay();
        }
    };
})();
</script>

<!-- Custom Styles for Work Type Selection -->
<style>
/* Choices.js Custom Styling */
#workTypeID + .choices {
    margin-top: 0.5rem;
}

#workTypeID + .choices .choices__inner {
    min-height: 120px;
    padding: 0.5rem;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    background-color: #fff;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

#workTypeID + .choices .choices__inner:focus-within {
    border-color: #86b7fe;
    outline: 0;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

#workTypeID + .choices .choices__list--multiple .choices__item {
    background-color: #0d6efd;
    border: 1px solid #0d6efd;
    border-radius: 0.25rem;
    padding: 0.375rem 0.75rem;
    margin: 0.25rem;
    font-size: 0.875rem;
}

#workTypeID + .choices .choices__list--multiple .choices__item.is-highlighted {
    background-color: #0a58ca;
    border-color: #0a58ca;
}

#workTypeID + .choices .choices__input {
    margin: 0.25rem;
    padding: 0.375rem;
    font-size: 0.875rem;
}

#workTypeID + .choices .choices__button {
    border-left: 1px solid rgba(255, 255, 255, 0.5);
    margin-left: 0.5rem;
    padding-left: 0.5rem;
}

#workTypeID + .choices .choices__button:hover {
    opacity: 0.8;
}

/* Selected Work Types Badges */
#selectedWorkTypesBadges .badge {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

#selectedWorkTypesBadges .badge .btn-close {
    opacity: 0.8;
}

#selectedWorkTypesBadges .badge .btn-close:hover {
    opacity: 1;
}

/* Form Group Spacing */
#manage_overtime_multiplier_form .form-group {
    margin-bottom: 1.5rem;
}

/* Input Group Enhancement */
#manage_overtime_multiplier_form .input-group-text {
    font-size: 0.875rem;
    color: #6c757d;
}

/* Alert Styling */
#manage_overtime_multiplier_form .alert {
    font-size: 0.875rem;
    border-left: 4px solid #0dcaf0;
}

#manage_overtime_multiplier_form .alert ul {
    margin-top: 0.5rem;
    padding-left: 1.25rem;
}

#manage_overtime_multiplier_form .alert li {
    margin-bottom: 0.25rem;
}

/* Responsive Adjustments */
@media (max-width: 576px) {
    #workTypeID + .choices .choices__inner {
        min-height: 100px;
    }

    #selectedWorkTypesBadges .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.5rem;
    }
}
</style>
