<div class="row">
    <div class="form-group d-none">
        <label for="instanceID"> Instanceid</label>
        <input type="text" name="instanceID" id="instanceID" class="form-control" Value="<?php echo $instanceID?>">
        <label for=""> financialStatementID</label>
        <input type="text" name="financialStatementID" id="financialStatementID" class="form-control" Value="<?php echo $financialStatementID; ?>">
    </div>
    <div class="form-group col-md-8">
        <label for="AccountName"> Account Name</label>
        <input type="text" name="accountName" id="accountName" class="form-control form-control-sm" value="" placeholder="Account Name"/>
    </div>

    <div class="form-group col-md-4">
        <label for="AccountCode"> Account Code</label>
        <input type="text" name="accountCode" id="accountCode" class="form-control form-control-sm" value="" placeholder="Account Code"/>
    </div>
    <div class="form-group col-md">
        <label for="accountCategory">Account Category</label>
        <input type="text" name="accountCategory" id="accountCategory" class="form-control form-control-sm" value="" placeholder="Account Description"/>
    </div>
    <div class="form-group col-md-4">
        <label for="accountType">Account Type</label>
        <select name="accountType" id="accountType" class="form-control form-control-sm">
            <option value="">Select Account Type</option>
            <option value="debit">Debit</option>
            <option value="credit">Credit</option>
        </select>
    </div>
    <div class="form-group col-md-4">
        <label for="accountValue">Account Value</label>
        <input type="text" name="accountValue" id="accountValue" class="form-control form-control-sm" value="" placeholder="Debit Value"/>
    </div>
    <div class="form-group">
        <label for="accountDescription">Account Description</label>
        <textarea name="accountDescription" id="accountDescription" class="form-control form-control-sm basic" placeholder="Account Description"></textarea>
    </div>
</div>