<div id="eaveEntitlementForm">
   <div class="form-group">
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
   <div class="form-group">
      <label for="entitlement">Entitlement (Days)</label>
      <input type="number" class="form-control" id="entitlement" name="entitlement" placeholder="Enter total entitlement days" min="0" step="0.5" required>
   </div>
   <div class="form-group">
      <label for="maxDaysPerApplication">Max Days Per Application</label>
      <input type="number" class="form-control" id="maxDaysPerApplication" name="maxDaysPerApplication" placeholder="Enter Max Days Per Application (leave empty for unlimited)" min="1">
      <small class="form-text text-muted">Maximum days that can be applied for in a single application. Leave empty for unlimited.</small>
   </div>
   <div class="form-group">
      <label for="minNoticeDays">Minimum Notice Period (Days)</label>
      <input type="number" class="form-control" id="minNoticeDays" name="minNoticeDays" placeholder="Enter minimum notice period in days (leave empty if not applicable)" min="0">
      <small class="form-text text-muted">Minimum number of days notice required before the leave start date. Leave empty if no minimum notice is required.</small>
   </div>
   <div class="form-group">
      <label for="entityID"> Entity </label>
      <select class="form-control" id="entityID" name="entityID" required>

         <?= Form::populate_select_element_from_object($entities, 'entityID', 'entityName', $entityID, '', 'Select Entity') ?>
         ?>
      </select>
   </div>

   <input type="hidden" id="leaveEntitlementID" name="leaveEntitlementID" value="">

</div>