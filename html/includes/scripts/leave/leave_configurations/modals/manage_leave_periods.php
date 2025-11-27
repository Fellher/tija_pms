<div id="manage_leave_period_form">
   <div class="row g-3">
      <div class="form-group d-none">
         <label for="leave_period_id" class="text-primary"> Leave Period ID</label>
         <input type="text" id="leave_period_id" name="leavePeriodID" class="form-control form-control-sm form-control-plaintext bg-light-blue" placeholder="Leave Period ID">
      </div>

      <div class="form-group col-12">
         <label for="leave_period_name" class="text-primary"> Leave Period Name</label>
         <input type="text" id="leave_period_name" name="leavePeriodName" class="form-control form-control-sm form-control-plaintext bg-light-blue" placeholder="Leave Period Name">
      </div>
      <div class="form-group col-md-6">
         <label for="leave_period_entity" class="text-primary"> Organisation / Entity</label>
         <select id="leave_period_entity"
                 name="entityID"
                 class="form-select form-select-sm bg-light-blue"
                 data-default-value="<?php echo htmlspecialchars($currentEntityID ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                 required>
            <?php echo Form::populate_select_element_from_object($allEntities ?? [], 'entityID', 'entityName', $currentEntityID ?? '', '', 'Select Entity'); ?>
         </select>
      </div>
      <div class="form-group col-md-6">
         <label for="leave_period_start_date" class="text-primary">
            Leave Period Start Date <span class="text-danger">*</span>
         </label>
         <input type="date"
                id="leave_period_start_date"
                name="leavePeriodStartDate"
                class="form-control form-control-sm form-control-plaintext bg-light-blue"
                placeholder="YYYY-MM-DD"
                required
                onchange="calculatePeriodEndDate()">
         <small class="text-muted">Select any date to start the 12-month period</small>
      </div>
      <div class="form-group col-md-6">
         <label for="leave_period_end_date" class="text-primary">
            Leave Period End Date <span class="text-danger">*</span>
         </label>
         <div class="input-group">
            <input type="date"
                   id="leave_period_end_date"
                   name="leavePeriodEndDate"
                   class="form-control form-control-sm form-control-plaintext bg-light-blue"
                   placeholder="YYYY-MM-DD"
                   required
                   onchange="validatePeriodDuration()">
            <button type="button"
                    class="btn btn-sm btn-outline-secondary"
                    onclick="calculatePeriodEndDate()"
                    title="Auto-calculate end date (12 months from start)">
               <i class="ri-calculator-line"></i> Auto
            </button>
         </div>
         <div id="periodDurationFeedback" class="mt-1"></div>
         <small class="text-muted">Must be exactly 12 months from start date</small>
      </div>
      <div class="col-12">
         <div class="alert alert-info mb-0" id="periodInfoAlert" style="display: none;">
            <i class="ri-information-line me-2"></i>
            <strong>Period Duration:</strong> <span id="periodDurationText">-</span>
         </div>
      </div>
   </div>
</div>