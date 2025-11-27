<div class="row workTypeForm">
   <div class="form-group d-none">
      <label for="workTypeID"> Work Type ID</label>
      <input type="text" class="form-control-sm form-control-plaintext  border-bottom py-0 bg-light " id="workTypeID" name="workTypeID" value="" readonly>
   </div>
   <div class="form-group">
      <label for="workTypeName"> Work Type Name</label>
      <input type="text" class="form-control-sm form-control-plaintext border-bottom py-0 bg-light " id="workTypeName" name="workTypeName" value="" required>
   </div>
   <div class="form-group">
      <label for="workTypeCode"> Work Type Code</label>
      <input type="text" class="form-control-sm form-control-plaintext border-bottom py-0 bg-light " id="workTypeCode" name="workTypeCode" value="" required>
   </div>
   <div class="form-group">
      <label for="workTypeDescription"> Work Type Description</label>
      <textarea class="form-control-sm form-control-plaintext  border-bottom borderless-mini" id="workTypeDescription" name="workTypeDescription" rows="3"></textarea>
   </div>
   <div class="form-group">
      <label for="workCategoryID"> Work Category</label>
      <select class="form-control form-control-sm form-control-plaintext  border-bottom" id="workCategoryID" name="workCategoryID" required>
         <option value="">Select Work Category</option>
         <?php 
         foreach ($workCategories as $key => $workCategory) { ?>
            <option value="<?php echo $workCategory->workCategoryID; ?>"><?php echo $workCategory->workCategoryName; ?></option>
            <?php 
         } ?>
      </select>
   </div>   
</div>