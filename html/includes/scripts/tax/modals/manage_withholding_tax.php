<div class="form-group d-none">
   <label for="entityID"> entity ID</label>
   <input type="text" name="entityID" id="entityID" class="form-control form-control-sm" value="<?php echo (isset($entityID) && $entityID)  ? $entityID : "" ?>" placeholder="Entity ID"/>
</div>
<div class="form-group">
   <label for=fiscalYear" class="mb-0"> Fiscal Year</label>
   <input type="text" name="fiscalYear" id="fiscalYear" class="form-control form-control-sm" value="<?php echo (isset($stmtData->fiscalYear) && $stmtData->fiscalYear)  ? $stmtData->fiscalYear : "" ?>" placeholder="Fiscal Year"/>
</div>
<div class="form-group">
   <label for="withholdingTax"> Withholding Tax</label>
   <input type="text" name="withholdingTax" id="withholdingTax" class="form-control form-control-sm" value="<?php echo (isset($stmtData->withholdingTax) && $stmtData->withholdingTax)  ? $stmtData->withholdingTax : "" ?>" placeholder="Withholding Tax"/>
   
</div>