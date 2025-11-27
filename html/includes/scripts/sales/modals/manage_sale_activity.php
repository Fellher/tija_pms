<div class="row salesActivityForm">
   <input type="hidden" name="salesCaseID" id="salesCaseID" value="<?= $salesCaseDetails->salesCaseID; ?>">
   <input type="hidden" name="orgDataID" value="<?php echo $orgDataID; ?>">
   <input type="hidden" name="entityID" value="<?php echo $entityID; ?>">
   <input type="hidden" name="salesPersonID" value="<?php echo $userID; ?>">
   <input type="hidden" name="salesActivityID">

   <div class="form-group t400 my-1">
      <label for="activityName" class="form-label mb-0 text-primary fst-italic fs-12">Activity Name</label>
      <input type="text" id="activityName" name="activityName" class="form-control form-control-sm" placeholder="Activity Name">
   </div>

   <div class="form-group col-md-12 my-1 ">
      <label for="clientID" class="form-label mb-0 text-primary fst-italic fs-12">Client</label>
      <select name="clientID" id="clientID" class="form-control form-control-sm">
         <option value="">Select Client</option>
         <?php
         $clients = Client::clients(array('orgDataID'=>$orgDataID), false, $DBConn);

         if($clients) {
            foreach ($clients as $client) {
               ?>
               <option value="<?php echo $client->clientID; ?>" <?= $salesCaseDetails->clientID== $client->clientID ? "selected" : ""?>> <?php echo $client->clientName; ?></option>
               <?php
            }
         }?>
         <option value="newClient">Add New Client</option>
      </select>
   </div>


   <?php $activityTypes = Sales::tija_activity_types(array('Suspended'=>'N'), false, $DBConn);   ?>
   <div class="form-group my-1">
      <label for="activityType" class="form-label mb-0 text-primary fst-italic fs-12">Activity Type</label>
      <select id="activityType" name="activityTypeID" class="form-control form-control-sm">
         <option value="">Select Activity Type</option>
         <?php
         if($activityTypes) {
            foreach ($activityTypes as $activityType) {
               ?>
               <option value="<?php echo $activityType->activityTypeID; ?>"><?php echo $activityType->activityTypeName; ?></option>
               <?php
            }
         }?>
      </select>
   </div>
   <div class="form-group my-1">
      <label for="activityDate" class="form-label mb-0 text-primary fst-italic fs-12">Activity Date</label>
      <input type="text" id="salesActivityDate" name="salesActivityDate" class="form-control form-control-sm" placeholder="Select Activity Date">
      <small class="text-muted">Click to select date</small>
   </div>

   <div class="row g-2">
      <div class="col-md-6">
         <div class="form-group my-1">
            <label for="activityStartTime" class="form-label mb-0 text-primary fst-italic fs-12">Start Time</label>
            <input type="text" id="activityStartTime" name="activityStartTime" class="form-control form-control-sm" placeholder="Select start time">
            <small class="text-muted">24-hour format</small>
         </div>
      </div>
      <div class="col-md-6">
         <div class="form-group my-1">
            <label for="activityEndTime" class="form-label mb-0 text-primary fst-italic fs-12">End Time</label>
            <input type="text" id="activityEndTime" name="activityEndTime" class="form-control form-control-sm" placeholder="Select end time">
            <small class="text-muted">24-hour format</small>
            <div id="endTimeError" class="text-danger small mt-1" style="display: none;">
               <i class="ri-error-warning-line me-1"></i>End time cannot be before start time
            </div>
         </div>
      </div>
   </div>

   <!-- Legacy field for backward compatibility -->
   <input type="hidden" id="timepickr1" name="activityTime" value="">


   <div class="form-group my-1">
      <label for="activityDescription" class="form-label mb-0 text-primary fst-italic fs-12">Activity Description</label>
      <textarea id="activityDescription" name="activityDescription" class="form-control form-control-sm borderless-mini" placeholder="Activity Description"></textarea>
   </div>

   <div class="form-group">
      <small>Case Name(Project/sale</small>
      <input type="text"  class="form-control form-control-sm form-control-plaintext bg-light-orange px-3 " value="<?php echo $salesCaseDetails->salesCaseName ?>" readonly>
   </div>

   <div class="form-group">
      <small>Customer</small>
      <input type="text" class="form-control form-control-sm form-control-plaintext bg-light-orange px-3 " value="<?php echo $salesCaseDetails->clientName ?> " readonly>
   </div>
   <div class="form-floating">
      <select class="form-select" name="activityOwner" id="ActivityOwner" aria-label="Floating label select example">
         <?php echo Form::populate_select_element_from_object($allEmployees, 'ID', 'employeeName', $salesCaseDetails->salesPersonID, '', 'Select Activity') ?>
      </select>
      <label for="ActivityOwner">Activity Owner </label>
   </div>
</div>

<!-- Flatpickr Initialization Script -->
<script>
(function() {
   'use strict';

   // Wait for DOM and Flatpickr to be available
   function initializeActivityPickers() {
      if (typeof flatpickr === 'undefined') {
         console.warn('Flatpickr is not loaded. Retrying in 100ms...');
         setTimeout(initializeActivityPickers, 100);
         return;
      }

      const activityDateInput = document.getElementById('salesActivityDate');
      const startTimeInput = document.getElementById('activityStartTime');
      const endTimeInput = document.getElementById('activityEndTime');
      const legacyTimeInput = document.getElementById('timepickr1');
      const endTimeError = document.getElementById('endTimeError');

      if (!activityDateInput || !startTimeInput || !endTimeInput) {
         console.warn('Activity picker inputs not found');
         return;
      }

      // Initialize Date Picker
      const datePicker = flatpickr(activityDateInput, {
         dateFormat: 'Y-m-d',
         altInput: true,
         altFormat: 'F j, Y',
         allowInput: true,
         defaultDate: 'today',
         minDate: 'today',
         onChange: function(selectedDates, dateStr) {
            // Update time validation when date changes
            validateTimeRange();
         }
      });

      // Initialize End Time Picker first (needed for start time picker reference)
      let endTimePicker = flatpickr(endTimeInput, {
         enableTime: true,
         noCalendar: true,
         dateFormat: 'H:i',
         time_24hr: true,
         allowInput: true,
         defaultDate: '10:00',
         minuteIncrement: 15,
         minTime: '09:00',
         onChange: function(selectedDates, dateStr) {
            validateTimeRange();
         }
      });

      // Initialize Start Time Picker
      const startTimePicker = flatpickr(startTimeInput, {
         enableTime: true,
         noCalendar: true,
         dateFormat: 'H:i',
         time_24hr: true,
         allowInput: true,
         defaultDate: '09:00',
         minuteIncrement: 15,
         onChange: function(selectedDates, dateStr) {
            // Update end time minimum when start time changes
            if (dateStr && endTimePicker) {
               endTimePicker.set('minTime', dateStr);
               // If end time is now invalid, clear it
               if (endTimeInput.value) {
                  const [startH, startM] = dateStr.split(':').map(Number);
                  const [endH, endM] = endTimeInput.value.split(':').map(Number);
                  const startTotal = startH * 60 + startM;
                  const endTotal = endH * 60 + endM;
                  if (endTotal <= startTotal) {
                     endTimePicker.clear();
                  }
               }
               validateTimeRange();
            }
            // Update legacy field for backward compatibility
            if (legacyTimeInput) {
               legacyTimeInput.value = dateStr;
            }
         }
      });

      /**
       * Validate that end time is not before start time
       */
      function validateTimeRange() {
         const startTime = startTimeInput.value;
         const endTime = endTimeInput.value;

         if (!startTime || !endTime) {
            // Clear error if either field is empty
            if (endTimeError) {
               endTimeError.style.display = 'none';
            }
            if (endTimeInput) {
               endTimeInput.classList.remove('is-invalid');
            }
            return true;
         }

         // Parse times (HH:MM format)
         const [startHours, startMinutes] = startTime.split(':').map(Number);
         const [endHours, endMinutes] = endTime.split(':').map(Number);

         const startTotalMinutes = startHours * 60 + startMinutes;
         const endTotalMinutes = endHours * 60 + endMinutes;

         if (endTotalMinutes <= startTotalMinutes) {
            // Show error
            if (endTimeError) {
               endTimeError.style.display = 'block';
            }
            if (endTimeInput) {
               endTimeInput.classList.add('is-invalid');
            }
            return false;
         } else {
            // Clear error
            if (endTimeError) {
               endTimeError.style.display = 'none';
            }
            if (endTimeInput) {
               endTimeInput.classList.remove('is-invalid');
            }
            return true;
         }
      }

      // Store picker instances for external access if needed
      window.activityDatePicker = datePicker;
      window.activityStartTimePicker = startTimePicker;
      window.activityEndTimePicker = endTimePicker;
      window.validateActivityTimeRange = validateTimeRange;

      // Validate on form submit
      const form = activityDateInput.closest('form');
      if (form) {
         form.addEventListener('submit', function(e) {
            if (!validateTimeRange()) {
               e.preventDefault();
               e.stopPropagation();

               // Show toast notification if available
               if (typeof showToast === 'function') {
                  showToast('Please ensure end time is after start time', 'error');
               } else {
                  alert('Error: End time cannot be before or equal to start time. Please adjust the times.');
               }

               // Focus on end time input
               endTimeInput.focus();
               return false;
            }
         });
      }

      // Real-time validation as user types
      startTimeInput.addEventListener('blur', validateTimeRange);
      endTimeInput.addEventListener('blur', validateTimeRange);
   }

   // Initialize when modal is shown or immediately if already visible
   const modal = document.getElementById('manageActivityModal');
   if (modal) {
      // Check if modal is already visible
      if (modal.classList.contains('show')) {
         initializeActivityPickers();
      } else {
         // Wait for modal to be shown
         modal.addEventListener('shown.bs.modal', function() {
            setTimeout(initializeActivityPickers, 100);
         });
      }
   } else {
      // If modal doesn't exist yet, try on DOMContentLoaded
      if (document.readyState === 'loading') {
         document.addEventListener('DOMContentLoaded', initializeActivityPickers);
      } else {
         initializeActivityPickers();
      }
   }
})();
</script>

<style>
/* Custom styling for Flatpickr in activity modal */
#manageActivityModal .flatpickr-input {
   cursor: pointer;
   background-color: #fff;
}

#manageActivityModal .flatpickr-input:focus {
   border-color: #007bff;
   box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

#manageActivityModal .is-invalid {
   border-color: #dc3545;
}

#manageActivityModal .is-invalid:focus {
   border-color: #dc3545;
   box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

#endTimeError {
   animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
   from {
      opacity: 0;
      transform: translateY(-5px);
   }
   to {
      opacity: 1;
      transform: translateY(0);
   }
}
</style>