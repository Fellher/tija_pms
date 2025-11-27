<div class="card custom-card">
   <div class="card-header">
      <h4 class="card-title">Leave Periods</h4>
   </div>
   <?php
   $leavePeriodsFilter = $leavePeriodsFilter ?? ['Suspended' => 'N'];
   $leavePeriods = Leave::leave_Periods($leavePeriodsFilter, false, $DBConn);
   $entityLookup = $entityLookup ?? [];
   if (empty($entityLookup) && isset($allEntities) && is_array($allEntities)) {
      foreach ($allEntities as $entityRec) {
         $entityLookup[$entityRec->entityID] = $entityRec->entityName;
      }
   }
   $currentEntityID = $currentEntityID ?? ($leavePeriodsFilter['entityID'] ?? '');
   ?>
   <div class="card-body">
      <?php if (!empty($allEntities)): ?>
      <div class="row align-items-end mb-3">
         <div class="col-md-6">
            <form method="get" class="row g-2 align-items-end">
               <input type="hidden" name="s" value="<?php echo htmlspecialchars($s ?? 'admin'); ?>">
               <input type="hidden" name="ss" value="<?php echo htmlspecialchars($ss ?? 'leave'); ?>">
               <input type="hidden" name="p" value="<?php echo htmlspecialchars($p ?? 'leave_periods'); ?>">
               <div class="col-md-8">
                  <label for="entity_filter" class="form-label text-primary fw-semibold">Organisation / Entity</label>
                  <select id="entity_filter" name="entity_filter" class="form-select form-select-sm">
                     <option value="">All Organisations</option>
                     <?php foreach ($allEntities as $entity): ?>
                        <option value="<?php echo $entity->entityID; ?>" <?php echo ($currentEntityID == $entity->entityID) ? 'selected' : ''; ?>>
                           <?php echo htmlspecialchars($entity->entityName); ?>
                        </option>
                     <?php endforeach; ?>
                  </select>
               </div>
               <div class="col-md-4">
                  <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                     <i class="ri-filter-2-line me-1"></i>Filter
                  </button>
               </div>
            </form>
         </div>
         <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <button class="btn btn-primary btn-sm rounded-pill btn-wave px-4 py-0"
                    data-bs-toggle="modal"
                    data-bs-target="#manage_leave_periods">
               Add Leave Period
            </button>
         </div>
      </div>
      <?php else: ?>
      <div class="text-end mb-3">
         <button class="btn btn-primary btn-sm rounded-pill btn-wave px-4 py-0"
                 data-bs-toggle="modal"
                 data-bs-target="#manage_leave_periods">
            Add Leave Period
         </button>
      </div>
      <?php endif; ?>
      <div class="table-responsive">
         <table id="leave_periods_table" class="table table-bordered table-striped table-vcenter js-dataTable-full table-sm" style="width: 100%;">
            <thead>
               <tr>
                  <th class="text-center">#</th>
                  <th>Leave Period Name</th>
                  <th>Organisation</th>
                  <th>Start Date</th>
                  <th>End Date</th>
                  <th>Action</th>
               </tr>
            </thead>
            <tbody>
               <?php
               if($leavePeriods){
                  $i = 1;
                  foreach($leavePeriods as $period){
                     // var_dump($period);
                     ?>
                     <tr>
                        <td class="text-center"><?php echo $i ?></td>
                        <td><?php echo $period->leavePeriodName ?></td>
                        <td><?php echo htmlspecialchars($entityLookup[$period->entityID] ?? 'Not set'); ?></td>
                        <td><?php echo Utility::date_format($period->leavePeriodStartDate) ?></td>
                        <td><?php echo Utility::date_format($period->leavePeriodEndDate) ?></td>
                        <td class="text-end">
                           <button
                              type="button"
                              class="btn btn-sm btn-primary editLeavePeriod"
                              data-bs-toggle="modal"
                              data-bs-target="#manage_leave_periods"
                              data-leavePeriodID="<?php echo $period->leavePeriodID ?>"
                              data-leaveperiodname="<?php echo $period->leavePeriodName ?>"
                              data-leaveperiodstartdate="<?php echo $period->leavePeriodStartDate ?>"
                              data-leaveperiodenddate="<?php echo $period->leavePeriodEndDate ?>"
                              data-entity-id="<?php echo $period->entityID ?>"
                              data-leaveperiodsuspended="<?php echo $period->Suspended ?>" >
                              Edit
                           </button>

                           <button type="button"
                              class="btn btn-sm btn-danger"
                              data-bs-toggle="modal"
                              data-bs-target="#manage_leave_periods"
                              data-id="<?php echo $period->leavePeriodID ?>">
                              Delete
                           </button>

                        </td>
                     </tr>
                     <?php $i++;
                  }
               } else {?>
                     <tr>
                        <td colspan="6" class="text-center">No Leave Periods Found</td>
                     </tr>
                  <?php
               }?>
            </tbody>
         </table>
      </div>
   </div>
</div>

<?php
      echo Utility::form_modal_header('manage_leave_periods', 'leave/config/manage_leave_periods.php', 'Manage Leave Period', array("modal-dialog-centered", "modal-lg"), $base, true );
      include 'includes/scripts/leave/leave_configurations/modals/manage_leave_periods.php';
      echo Utility::form_modal_footer("Save Leave Period", "manage_leave_periods_details", 'btn btn-primary btn-sm');
   ?>

   <script>
      // Calculate end date (12 months from start date)
      function calculatePeriodEndDate() {
         const startDateInput = document.getElementById('leave_period_start_date');
         const endDateInput = document.getElementById('leave_period_end_date');

         if (!startDateInput || !endDateInput) return;

         const startDate = startDateInput.value;
         if (!startDate) {
            endDateInput.value = '';
            return;
         }

         // Calculate 12 months from start date
         const start = new Date(startDate);
         const end = new Date(start);
         end.setMonth(end.getMonth() + 12);
         end.setDate(end.getDate() - 1); // Subtract 1 day to get the last day of the 12th month

         // Format as YYYY-MM-DD
         const endDateStr = end.toISOString().split('T')[0];
         endDateInput.value = endDateStr;

         validatePeriodDuration();
      }

      // Validate that period is exactly 12 months
      function validatePeriodDuration() {
         const startDateInput = document.getElementById('leave_period_start_date');
         const endDateInput = document.getElementById('leave_period_end_date');
         const feedbackDiv = document.getElementById('periodDurationFeedback');
         const infoAlert = document.getElementById('periodInfoAlert');
         const durationText = document.getElementById('periodDurationText');

         if (!startDateInput || !endDateInput || !feedbackDiv) return;

         const startDate = startDateInput.value;
         const endDate = endDateInput.value;

         if (!startDate || !endDate) {
            feedbackDiv.innerHTML = '';
            if (infoAlert) infoAlert.style.display = 'none';
            return;
         }

         const start = new Date(startDate);
         const end = new Date(endDate);

         if (start >= end) {
            feedbackDiv.innerHTML = '<small class="text-danger"><i class="ri-error-warning-line me-1"></i>End date must be after start date</small>';
            if (infoAlert) infoAlert.style.display = 'none';
            return;
         }

         // Calculate difference in months
         const monthsDiff = (end.getFullYear() - start.getFullYear()) * 12 + (end.getMonth() - start.getMonth());
         const daysDiff = Math.floor((end - start) / (1000 * 60 * 60 * 24));

         // Check if it's approximately 12 months (365-366 days, accounting for leap years)
         const is12Months = daysDiff >= 364 && daysDiff <= 366;

         if (is12Months) {
            feedbackDiv.innerHTML = '<small class="text-success"><i class="ri-checkbox-circle-line me-1"></i>Valid 12-month period</small>';
            if (infoAlert) {
               infoAlert.className = 'alert alert-success mb-0';
               infoAlert.style.display = 'block';
               durationText.textContent = `${daysDiff} days (${monthsDiff} months)`;
            }
         } else {
            const daysOff = Math.abs(daysDiff - 365);
            feedbackDiv.innerHTML = `<small class="text-warning"><i class="ri-alert-line me-1"></i>Period is ${daysDiff} days (should be 365-366 days for 12 months). ${daysOff > 0 ? `Off by ${daysOff} day(s).` : ''}</small>`;
            if (infoAlert) {
               infoAlert.className = 'alert alert-warning mb-0';
               infoAlert.style.display = 'block';
               durationText.textContent = `${daysDiff} days (${monthsDiff} months) - Please adjust to exactly 12 months`;
            }
         }
      }

      document.addEventListener('DOMContentLoaded', function() {
         const editLeavePeriodButtons = document.querySelectorAll('.editLeavePeriod');

         editLeavePeriodButtons.forEach(function(button) {
            button.addEventListener('click', function() {
               const form = document.getElementById('manage_leave_periods');
               if(!form) {
                  console.error('Form not found');
                  return;
               }

               // Get all data attributes from the button
               const data = this.dataset;

               // Map form fields to their corresponding data attributes
               const fieldMappings = {
                  'leavePeriodID': 'leaveperiodid',
                  'leavePeriodName': 'leaveperiodname',
                  'leavePeriodStartDate': 'leaveperiodstartdate',
                  'leavePeriodEndDate': 'leaveperiodenddate',
                  'leavePeriodSuspended': 'leaveperiodsuspended',
                  'entityID': 'entityId'
               };

                // Fill regular form inputs
               for (const [field, dataAttr] of Object.entries(fieldMappings)) {
                  const input = form.querySelector(`[name="${field}"]`);
                  if (input) {
                     input.value = data[dataAttr];
                  } else {
                     console.error(`Input field ${field} not found in the form`);
                  }
               }


            });
         });

         const addLeavePeriodButtons = document.querySelectorAll('[data-bs-target="#manage_leave_periods"]:not(.editLeavePeriod)');
         addLeavePeriodButtons.forEach(function(button) {
            button.addEventListener('click', function() {
               const form = document.getElementById('manage_leave_periods');
               if (!form) { return; }
               form.reset();
               const hiddenId = form.querySelector('[name="leavePeriodID"]');
               if (hiddenId) { hiddenId.value = ''; }
               const entitySelect = form.querySelector('[name="entityID"]');
               if (entitySelect) {
                  const defaultValue = entitySelect.dataset.defaultValue || '';
                  entitySelect.value = defaultValue;
               }
            });
         });
      });
   </script>