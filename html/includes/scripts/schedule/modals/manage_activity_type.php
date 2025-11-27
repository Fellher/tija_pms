<div id="activityTypeForm">
   <div class="row">
      <div class="form-group my-1">
         <label for="activityTypeName">Activity Type Name</label>
         <input type="text" class="form-control form-control-sm" id="activityTypeName" name="activityTypeName" placeholder="Enter Activity Type Name" required>
         <input type="hidden" class="form-control form-control-sm" id="activityTypeID" name="activityTypeID" placeholder="Enter Activity Type ID" hidden>
      </div>
      
      <div class="form-group my-1">
         <label for="activityTypeDescription">Activity Type Description</label>
         <textarea class="form-control form-control-sm borderless-mini" id="activityTypeDescription" name="activityTypeDescription" placeholder="Enter Activity Type Description" required></textarea>
      </div>
      
      <div class="form-group my-1">
         <label for="iconlink">Icon class</label>
         <input type="text" class="form-control form-control-sm" id="iconlink" name="iconlink" placeholder="Enter Icon Link" required>
      </div>
      <div class="form-group my-1">
         <label for="activityCategoryID">Activity Category</label>
         <select name="activityCategoryID" id="activityCategoryID" class="form-control form-control-sm" required>
            <?php echo Form::populate_select_element_from_object($activityCategories, 'activityCategoryID', 'activityCategoryName', '', '' , 'Select Activity Category') ?>
         </select>
      </div>
   </div>
</div>