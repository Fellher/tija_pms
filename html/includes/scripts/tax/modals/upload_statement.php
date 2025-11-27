<div class="row"> 
    <div class="form-group col-md-6">
        <label for="FiscalYear"> Fiscal Year </label>
        <select name="fiscalYear" id="fiscalYear" class="form-control">
            <option value="">Select Year</option>
            <?php 
            $currentYear = date('Y'); 
            for ($i=$currentYear-6; $i <= $currentYear; $i++) {
            ?>
            <option value="<?php echo $i ?>"><?php echo $i ?></option>
            <?php } ?>        
        </select>
    </div>
    <div class="form-group col-md-6">
        <label for="daterange"> Select Statement Period</label>
        <div class="input-group">
        <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
        <input type="text" class="form-control" id="daterange" name="period" placeholder="Date range picker">
        </div>
    </div>
    
    <div class="form-group">
      <label for="statementupload" class="font-16" >Upload:</label><br>
      <input id="input-5" name="<?php echo "upload_{$stmtType->statementTypeNode}"?>" type="file" class="file-loading data_upload form-control form-control-sm w100 " data-show-preview="false">  
    </div>

    <input type="hidden" name="orgDataID" value="<?php echo $entityDetails->orgDataID ?>">
    <input type="hidden" name="entityID" value="<?php echo $entityDetails->entityID ?>">
    <input type="hidden" name="userID" value="<?php echo $userID ?>">
    <input type="hidden" name="statementType" value="<?php echo $stmtType->financialStatementTypeName ?>">
    <input type="hidden" name="statementTypeNode" value="<?php echo $stmtType->statementTypeNode ?>">
    <input type="hidden" name="statementTypeID" value="<?php echo $stmtType->financialStatementTypeID ?>" >
</div>