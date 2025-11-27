<div id="leaveApplicationForm">
   <div class="row">
      <input type="hidden" id="leaveApplicationID" name="leaveApplicationID" value="">
      <input type="hidden" id="employeeID" name="employeeID" value="<?php echo $userDetails->ID; ?>">
      <input type="hidden" id="leaveStatusID" name="leaveStatusID" value="1">
      <input type="hidden" id="leaveEntitlementID" name="leaveEntitlementID" value="">
      <input type="hidden" id="entityID" name="entityID" value="<?php echo $entityID; ?>">
      <input type="hidden" id="orgDataID" name="orgDataID" value="<?php echo $orgDataID; ?>">
      <div class="form-group col-md-6">
         <label for="leaveTypeID">Leave Type</label>
         <select class="form-control" id="leaveTypeID" name="leaveTypeID" required>
            <option value="">Select Leave Type</option>
            <?php
            if($leaveTypes) {
               foreach ($leaveTypes as $leaveType) {?>
                  <option value="<?php echo $leaveType->leaveTypeID; ?>"><?php echo $leaveType->leaveTypeName; ?></option>
               <?php
               }
            }?>
         </select>
      </div>
      <div class="form-group col-md-6">
         <label for="leavePeriodID">Leave Period</label>
         <select class="form-control" id="leavePeriodID" name="leavePeriodID" required>
            <option value="">Select Leave Period</option>
            <?php
            if($leavePeriods) {
               foreach ($leavePeriods as $leavePeriod) {?>
                  <option value="<?php echo $leavePeriod->leavePeriodID; ?>"><?php echo $leavePeriod->leavePeriodName; ?></option>
               <?php
               }
            }?>
         </select>
      </div>

   </div>
   
   <div class="row my-2 bg-light p-2 rounded">
      <div class="form-group col-md-6">

         <label for="startDate">Start Date</label>
         <input type="date" class="form-control form-control-sm date" id="startDate" name="startDate" required>
      </div>
      <div class="form-group col-md-6">
         <label for="endDate">End Date</label>
         <input type="date" class=" form-control form-control-sm date" id="endDate" name="endDate" required>
      </div>

   </div>
   

   <div class="form-group">
      <label for="leaveComments">Comments</label>
      <textarea class="form-control borderless-md" id="leaveComments" name="leaveComments" rows="3"></textarea>
   </div>
   <div class="form-group my-2">
      <label for="uploadFiles" class="text-primary">Upload Files</label>
      <input type="file" class="form-control" id="uploadFiles" name="uploadFiles[]" multiple>
   </div>

</div>

<script>
      document.addEventListener('DOMContentLoaded', function() {
         const startDateInput = document.getElementById('startDate');
         const endDateInput = document.getElementById('endDate');

         startDateInput.addEventListener('change', checkDates);
         endDateInput.addEventListener('change', checkDates);

         function checkDates() {
            const startDate = new Date(startDateInput.value);
            const startDateUnix = startDate.getTime() / 1000;

            const endDate = new Date(endDateInput.value);
            const endDateUnix = endDate.getTime() / 1000;
            console.log(startDateUnix, endDateUnix);

            console.log(startDate, endDate);
            // if (startDateInput.value && endDateInput.value && startDate > endDate) {
            //    console.log('End date must be after start date.');
            //    endDateInput.value = '';
            // }

            if (endDateUnix < startDateUnix) {
               console.log('End date must be after start date.');
               endDateInput.value = '';
            }
         }

         document.querySelectorAll('.editLeaveApplicationBtn').forEach(function(button) {
            button.addEventListener('click', function() {

               const form = document.getElementById('leaveApplicationForm');
               if (!form) return;

               console.log(form);

              

                  // Get all data attributes from the button
            const data = this.dataset;
            console.log(data);

               // Map form fields to their corresponding data attributes
               const fieldMappings = {
                  'leaveTypeID': 'leaveTypeId',
                  'leavePeriodID': 'leavePeriodId',
                  'startDate': 'startDate',
                  'endDate': 'endDate',
                  'leaveStatusID': 'leaveStatusID',
                  'employeeID': 'employeeId',
                  'leaveComments': 'leaveComments',
                  'leaveFiles': 'leaveFiles',
                
                 
                  'leaveEntitlementID': 'leaveEntitlementId',
                  'leaveApplicationID': 'leaveApplicationId',
                  'leaveStatusID': 'leaveStatusId',
                  'entitlement': 'entitlement',
               };

                // Fill regular form inputs
               for (const [fieldName, dataAttribute] of Object.entries(fieldMappings)) {
                  const input = form.querySelector(`[name="${fieldName}"]`);
                  if (input) {
                     input.value = data[dataAttribute] || '';
                  }
               }
               // Handle tinyMCE editor
               tinymce.init({
                  selector: '#leaveComments'
               });
               const editor = tinymce.get('leaveComments'); // Make sure 'leaveComments' matches your textarea's ID
               if (editor) {
                  // Wait for a brief moment to ensure tinyMCE is fully initialized
                  setTimeout(() => {
                     editor.setContent(data.leaveComments || '');
                  }, 100);
               }

                // If you have select elements that need special handling
            // (like setting selected options), handle them here
               const selects = ['leaveTypeID', 'leavePeriodID'];
               selects.forEach(selectName => {
                  const select = form.querySelector(`[name="${selectName}"]`);
                  if (select && data[fieldMappings[selectName]]) {
                     select.value = data[fieldMappings[selectName]];
                  }
               });
               // Hide the file input
               const fileInput = form.querySelector('input[type="file"]');
               if (fileInput) {
                  fileInput.parentElement.classList.add('d-none');
               }




               // const leaveTypeID = this.getAttribute('data-leave-type-id');
               // const leavePeriodID = this.getAttribute('data-leave-period-id');
               // const startDate = this.getAttribute('data-start-date');
               // const endDate = this.getAttribute('data-end-date');
               // const leaveStatusID = this.getAttribute('data-leave-status-id');
               // const employeeID = this.getAttribute('data-employee-id');
               // const leaveComments = this.getAttribute('data-leave-comments');
               // const leaveFiles = this.getAttribute('data-leave-files');

               // document.getElementById('leaveTypeID').value = leaveTypeID;
               // document.getElementById('leavePeriodID').value = leavePeriodID;
               // document.getElementById('startDate').value = startDate;
               // document.getElementById('endDate').value = endDate;
               // document.getElementById('leaveStatusID').value = leaveStatusID;
               // document.getElementById('employeeID').value = employeeID;
               // document.getElementById('leaveComments').value = leaveComments;

            });
         });

      });
   </script>