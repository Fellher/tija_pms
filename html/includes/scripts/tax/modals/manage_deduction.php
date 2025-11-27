<?php 
$multiSelect = true;
include 'includes/scripts/views/tax/standard_accounts_tree.php' ?>
<input type="hidden" class="form-control" name="financialStatementTypeID" value="<?php  echo $incomeStatementType->financialStatementTypeID ?>" >
<input type="hidden" name="node" id="addBackDeductionsNode" value="deduction" />
<imput type="hidden" name="instanceID" id="addBackDeductionsInstanceID" value="<?php echo $instanceID ?>" />