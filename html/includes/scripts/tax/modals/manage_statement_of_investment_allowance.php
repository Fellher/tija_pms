<div class="row">
    <div class="form-group col-md-8">
        <label for="investmentName<?php echo $node ?>" class="mb-0"> Investment Name</label>
        <input type="text" name="investmentName" id="investmentName<?php echo $node ?>" class="form-control form-control-sm" value="<?php echo (isset($stmtData->investmentName) && $stmtData->investmentName)  ? $stmtData->investmentName : "" ?>" placeholder="Investment Name"/>
    </div>
    <div class="form-group col-md-4">
        <label for="rate<?php echo $node ?>" class="mb-0"> Rate %</label>
        <input type="number" name="rate<?php echo $node ?>" id="rate" min="0" max="100" step="0.1"  class="form-control form-control-sm" value="<?php echo (isset($stmtData->rate) && $stmtData->rate)  ? $stmtData->rate*100 : "" ?>" placeholder="Rate"/>
    </div>
    <div class="form-group col-md-4">
        <label for="initialWriteDownValue<?php echo $node ?>" class="mb-0"> Initial Write Down Value</label>
        <input type="text" name="initialWriteDownValue" id="initialWriteDownValue<?php echo $node ?>" class="form-control form-control-sm" value="<?php echo (isset($stmtData->initialWriteDownValue) && $stmtData->initialWriteDownValue)  ? $stmtData->initialWriteDownValue : "" ?>" placeholder="Initial Write Down Value"/>
    </div>
    <div class="form-group col-md-4">
        <label for="beginDate<?php echo $node ?>" class="mb-0"> Begin Date</label>
        <input type="text" name="beginDate" id="beginDate<?php echo $node ?>" class="form-control form-control-sm component-datepicker past-enabled" value="<?php echo (isset($stmtData->beginDate) && $stmtData->beginDate)  ? $stmtData->beginDate : "" ?>" placeholder="Begin Date"/>
    </div>
    <div class="form-group col-md-4">
        <label for="additions<?php echo $node ?>" class="mb-0"> Additions</label>
        <input type="text" name="additions" id="additions<?php echo $node ?>" class="form-control form-control-sm" value="<?php echo (isset($stmtData->additions) && $stmtData->additions)  ? $stmtData->additions : "" ?>" placeholder="Additions"/>
    </div>
    <div class="form-group col-md-4">
        <label for="disposals<?php echo $node ?>" class="mb-0"> Disposals</label>
        <input type="text" name="disposals" id="disposals<?php echo $node ?>" class="form-control form-control-sm" value="<?php echo (isset($stmtData->disposals) && $stmtData->disposals)  ? $stmtData->disposals : "" ?>" placeholder="Disposals"/>
    </div>
    <div class="form-group col-md-4">
        <label for="wearAndTearAllowance<?php echo $node ?>" class="mb-0"> Wear & Tear Allowance</label>
        <input type="text" name="wearAndTearAllowance" id="wearAndTearAllowance<?php echo $node ?>" class="form-control form-control-sm" value="<?php echo (isset($stmtData->wearAndTearAllowance) && $stmtData->wearAndTearAllowance)  ? $stmtData->wearAndTearAllowance : "" ?>" placeholder="Wear & Tear Allowance"/>
    </div>
    <div class="form-group col-md-4">
        <label for="endWriteDownValue<?php echo $node ?>" class="mb-0"> End Write Down Value</label>
        <input type="text" name="endWriteDownValue" id="endWriteDownValue<?php echo $node ?>" class="form-control form-control-sm " value="<?php echo (isset($stmtData->endWriteDownValue) && $stmtData->endWriteDownValue)  ? $stmtData->endWriteDownValue : "" ?>" placeholder="End Write Down Value"/>
    </div>
    <div class="form-group col-md-4">
        <label for="endDate<?php echo $node ?>" class="mb-0"> End Date</label>
        <input type="text" name="endDate" id="endDate<?php echo $node ?>" class="form-control form-control-sm component-datepicker past-enabled" value="<?php echo (isset($stmtData->endDate) && $stmtData->endDate)  ? $stmtData->endDate : "" ?>" placeholder="End Date"/>
    </div>
    <div class="form-group col-md-4 d-none">
        <label for="investmentAllowanceID<?php echo $node ?>" class="mb-0"> Investment Allowance ID</label>
        <input type="text" name="investmentAllowanceID" id="investmentAllowanceID<?php echo $node ?>" class="form-control form-control-sm" value="<?php echo (isset($stmtData->InvestmentAllowanceID) && $stmtData->InvestmentAllowanceID)  ? $stmtData->InvestmentAllowanceID : "" ?>" placeholder="Investment Allowance ID"/>
    </div>
    <div class="form-group d-none ">
        <!-- <label for="instanceID"> Instanceid</label> -->
       
        <!-- <label for=""> financialStatementID</label> -->
        <input type="hidden" name="financialStatementID" id="financialStatementID<?php echo $node ?>" class="form-control" Value="<?php echo $stmtData->financialStatementID; ?>">
       
    </div>
    <!-- Inputc a check button to suspend account -->
    <div class="form-group col-md">
        <label class="form-check-label" for="suspend<?php echo $node ?>">Suspend Account</label>
        <div class="form-check form-switch  col-md-4">       
            <input class="form-check-input" name="Suspend" type="checkbox" role="switch" id="suspend<?php echo $node ?>" value="Y" <?php echo $stmtData->Suspended == 'Y' ? "checked" : "" ?>>
            <label class="form-check-label" for="suspend<?php echo $node ?>">Suspend Account</label>
        </div>
    </div>
    <div class="form-group col-md">
        <label class="form-check-label" for="allowInTotal<?php echo $node ?>">Allow in Calculation</label>
        <div class="form-check form-switch  col-md-4">       
            <input class="form-check-input" name="allowInTotal" type="checkbox" role="switch" id="allowInTotal<?php echo $node ?>" value="Y" <?php echo $stmtData->allowInTotal == 'Y' ? "checked" : "" ?>>
            <label class="form-check-label" for="allowInTotal<?php echo $node ?>">Allow in total Investment Allowance Calculation</label>
        </div>
    </div>
</div>
