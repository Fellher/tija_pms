<div class="form-group">
   <label for="adjustmentCategoryName">Adjustment Category Name</label>
   <input type="text" class="form-control form-control-sm" id="adjustmentCategoryName" name="adjustmentCategoryName" required>
</div>
<div class="form-group">
   <label for="adjustmentCategoryID">Adjustment Category ID</label>
<input type="text" name="adjustmentCategoryID" id="adjustmentCategoryID" value="" class="form-control form-control-sm">
</div>

<div class="form-group">
   <label for="adjustmentCategoryDescription">Description</label>
   <textarea class="form-control borderless" id="adjustmentCategoryDescription" name="categoryDescription" rows="3"></textarea>
</div>
<div class="form-group">
   
   <label for="adjustmentTypeID"> Adjustment Type</label>
   <select class="form-select form-select-sm" id="adjustmentTypeID" name="adjustmentTypeID" required>
      <option value="">Select Adjustment Type</option>
      <?php foreach ($adjustmentTypes as $type) {?>
      <option value="<?= $type->adjustmentTypeID ?>"><?= $type->adjustmentType ?></option>
      <?php }?>
   </select>
</div>