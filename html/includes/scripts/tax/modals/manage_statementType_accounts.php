<input type="hidden" name="financialStatementTypeID" value="<?php echo $statementTypeID ?>" class="form-control">
<input type="hidden" name="statementTypeNode" Value="<?php echo $statementTypeDetails->statementTypeNode?>" class="form-control">
<div class="form-group">
    <label for="accountName"> Account Name</label>
    <input type="text" name="accountName" id="accountName" class="form-control form-control-sm">
</div>
<?php
if($statementTypeDetails->statementTypeNode == 'StatementofInvestmentAllowance'){
    $StatementofInvestmentAllowanceAccounts = Tax::statement_of_investment_allowance_accounts(array("Suspended"=>"N"), false, $DBConn);
    
    var_dump($StatementofInvestmentAllowanceAccounts) ?>
        <div class="form-group">
            <label for="parentAccountID"> Parent Account</label>
            <select class="form-control form-control-sm" name="parentAccountID">
                <?php echo Form::populate_select_element_from_object($StatementofInvestmentAllowanceAccounts, 'investmentAllowanceAccountID', 'accountName', '', '', 'Select Parent Account' ); ?>
            </select>
        </div>
    <?php
}?>


   