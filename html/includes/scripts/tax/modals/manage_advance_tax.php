<div class="form-group d-none">
   <label for="entityID"> Entity ID</label>
   <input type="text" name="entityID" id="entityID" class="form-control form-control-sm" value="<?php echo (isset($entityID) && $entityID)  ? $entityID : "" ?>" placeholder="Entity ID"/>
</div>
<div class="form-group">
   <label for="advanceTax"> Advance Tax </label>
   <input type="text" name="advanceTax" id="advanceTax" class="form-control form-control-sm" value="<?php echo (isset($stmtData->advanceTax) && $stmtData->advanceTax)  ? $stmtData->advanceTax : "" ?>" placeholder="advance Tax"/>
</div>
<div class="form-group">
   <label for="fiscalYear"> Fiscal Year</label>
   <input type="text" name="fiscalYear" id="fiscalYear" class="form-control form-control-sm" value="<?php echo (isset($stmtData->fiscalYear) && $stmtData->fiscalYear)  ? $stmtData->fiscalYear : "" ?>" placeholder="Fiscal Year"/>
</div>