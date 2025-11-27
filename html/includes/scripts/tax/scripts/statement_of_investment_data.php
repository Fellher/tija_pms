<?php
$investmentAllowanceAccountsData = Tax::statement_of_investment_data(array("Lapsed"=>'N', "Suspended"=>'N', "financialStatementID"=> $financialStatementID), false, $DBConn);
if($investmentAllowanceAccountsData) {
    // var_dump($investmentAllowanceAccountsData);?>
    <div class='table'>
        <table class='table table-striped table-bordered table-sm'>
            <thead>
                <tr>
                    <th>Investment Name</th>
                    <th>Investment rate</th>
                    <th>Initial W.D.V </th>
                    <th> Start Date </th>
                    <th> Additions (value) </th>
                    <th> Disposals (Value) </th>
                    <th> Wear & Tear Allowance Value </th>
                    <th> End W.D.V </th>
                    <th> End Date </th>
                </tr>
            </thead>
            <tbody>
                <?php
                $wearTearAllowanceTotal = 0;
                $disposalTotal = 0;
                $innitialTotal = 0;
                $additionTotal = 0;
                $investmentTotal = 0;
                
                foreach ($investmentAllowanceAccountsData as $key => $stmtData) {
                    $node .= "edit_{$stmtData->InvestmentAllowanceID}";
                    $investmentTotal += $stmtData->endWriteDownValue; 
                    $wearTearAllowanceTotal += $stmtData->wearAndTearAllowance;
                    $disposalTotal += $stmtData->disposals;
                    $additionTotal += $stmtData->additions;
                    $innitialTotal += $stmtData->initialWriteDownValue;

                    ?>                                   
                    <tr>
                        <td><?php echo $stmtData->investmentName ?></td>
                        <td><?php echo $stmtData->rate *100 ?> %</td>
                        <td><?php echo Utility::formatToCurrency($stmtData->initialWriteDownValue, "") ?></td>
                        <td><?php echo $stmtData->beginDate ?></td>
                        <td><?php echo Utility::formatToCurrency($stmtData->additions, "") ?></td>
                        <td><?php echo Utility::formatToCurrency($stmtData->disposals, "") ?></td>
                        <td><?php echo Utility::formatToCurrency($stmtData->wearAndTearAllowance, "") ?></td>
                        <td><?php echo Utility::formatToCurrency($stmtData->endWriteDownValue, "") ?></td>
                        <td><?php echo $stmtData->endDate ?>
                        <!-- edit  -->
                            <a href="#<?php echo "edit_{$stmtData->InvestmentAllowanceID}" ?>" data-bs-toggle="modal" data-InvestmentAllowanceID="<?php echo $stmtData->InvestmentAllowanceID; ?>" class="btn btn-primary btn-sm">Edit</a>
                    </td>
                    </tr>
                    <?php
                     echo Utility::form_modal_header("edit_{$stmtData->InvestmentAllowanceID}", "tax/admin/manage_statement_of_investment_allowance.php", "Edit Investment Allowance", array('modal-md', 'modal-dialog-centered', "modal-xl"), $base);
                        // var_dump($stmtData);
                     include "includes/scripts/tax/modals/manage_statement_of_investment_allowance.php";
                     echo Utility::form_modal_footer( "Edit Investment Allowance","edit_investment_allowance", 'btn  btn-secondary btn-lg', false);
                }?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2">Total</td>
                    <td><?php echo Utility::formatToCurrency($innitialTotal) ?></td>
                    <td> </td>
                    <td><?php echo Utility::formatToCurrency($additionTotal, "") ?></td>
                    <td><?php echo Utility::formatToCurrency($disposalTotal, "") ?></td>

                    <td> <?php echo Utility::formatToCurrency($wearTearAllowanceTotal, "") ?></td>
                    <td><?php echo Utility::formatToCurrency($investmentTotal, "") ?></td>
                </tr>
            </tfoot>
        </table>                                                
    </div>
    <?php
   

} else {
    echo "<div class='alert alert-info'>No Investment Allowance Accounts Found</div>";
}?>