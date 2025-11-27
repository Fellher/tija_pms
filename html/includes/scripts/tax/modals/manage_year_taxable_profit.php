<div class="form-group ">
      <label for="year" class="mb-0"> Year</label>
      <input type="text" name="year" id="year" class="form-control form-control-sm" value="<?php echo (isset($stmtData->year) && $stmtData->year)  ? $stmtData->year : "" ?>" placeholder="Year"/>

</div>
<div class="form-group">
   <label for="taxableProfit" class="mb-0"> Taxable Profit</label>
   <input type="text" name="taxableProfit" id="taxableProfit" class="form-control form-control-sm" value="<?php echo (isset($stmtData->taxableProfit) && $stmtData->taxableProfit)  ? $stmtData->taxableProfit : "" ?>" placeholder="Taxable Profit"/>
</div>
<div class="form-group d-none">
   <label for="entityID"> Entity ID</label>
   <input type="text" name="entityID" id="entityID" class="form-control form-control-sm" value="<?php echo (isset($entityID) && $entityID)  ? $entityID : "" ?>" placeholder="Entity ID"/>
</div>