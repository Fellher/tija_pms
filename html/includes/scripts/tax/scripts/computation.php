<?php 
$deductionsTotalSum = 0;
if(isset($investmentStatementData) && isset($statementData) && $status=="compute") {
   $incomeStatementDetails = Tax::financial_statements(array("financialStatementID"=>$financialStatementID, 'entityID'=>$entityID, "Lapsed"=>'N', "Suspended"=>'N'), true, $DBConn);?>
   <div class="col-12">
      <div class="card card-body">
         <h3 class="text-center"> Tax Computation </h3>
         <h4 class="border-bottom border-primary mb-0 pb-0"> Company: <?php echo $entityDetails->entityName ?> </h4>
         <h4 class="border-bottom border-primary pb-0 mb-0"> Tax Year: <?php echo  $incomeStatementDetails->fiscalYear;   ?> </h4>
         <!-- <h4 class="border-bottom border-primary"> Tax Period: (<?php echo date('m') . " ) ". date('F') ?> </h4> -->
         <div class="list-group list-group-flush bg">
            <div class="list-group-item active py-0">
               <div class="row text-white">
                  <div class="col-6">
                     <h3 class="my-0 py-0 text-white ">Particulars</h3>
                  </div>
                  <div class="col-6">
                     <h3 class="my-0 py-0 text-white">Amount</h3>
                  </div>
               </div>
            </div>
            <div class="list-group-item">
               <div class="row">
                  <div class="col-6">
                     <h5>Profit (Loss) before Tax</h5>
                  </div>
                  <div class="col-6">
                     <h5 class="text-end">
                        <?php   
                        $profit_loss_before_tax = $creditTotal- $debitTotal;
                        echo Utility::formatToCurrency($profit_loss_before_tax, "KES");
                        ?>
                     </h5>                                    
                  </div>
               </div>
            </div>
            <?php 
            $adjustmentTypes = Tax::tax_adjustment_types(array("Lapsed"=>'N', "Suspended"=>'N'), false, $DBConn);     
            if($adjustmentTypes) {
               $deductionsTotal = 0;
               $adbacksTotal = 0;
               foreach ($adjustmentTypes as $key => $adjustmentType) {  
                  // var_dump($adjustmentType);                                             
                  $adjustmentAccounts = Tax::adjustment_accounts(array("adjustmentTypeID"=>$adjustmentType->adjustmentTypeID,  "Lapsed"=>'N', "Suspended"=>'N'), false, $DBConn);      
                  // var_dump($adjustmentAccounts);      
                  if($adjustmentAccounts) {?>
                     <div class="list-group-item list-group-item-action list-group-item-primary">
                        <div class="row">
                           <div class="col-6">
                              <span class="fw-semibold fs-20"><?php echo $adjustmentType->adjustmentType ?></span>
                           </div>
                           <div class="col-6 text-end">
                              <span class="fw-semibold fs-20">Amounts</span>
                           </div>
                        </div>
                     </div>
                     <?php                        
                     $trialBalanceAdjustments = 0;
                     $investmentBuilding = 0;
                     $adjustmentAccountsTotal = 0;
                     $adjustmentAccounts = Tax::adjustment_accounts(array("adjustmentTypeID"=>$adjustmentType->adjustmentTypeID,  "Lapsed"=>'N', "Suspended"=>'N'), false, $DBConn);	
                     // echo "<h4> adjustment Accounts {$adjustmentType->adjustmentType} </h4>";
                     // var_dump($adjustmentAccounts);										
                     if($adjustmentAccounts) {?>
                        <div class="list-group list-group-flush">
                           <?php
                           $display = false;
                           foreach ($adjustmentAccounts as $key => $adjustmentAccount) { 														
                              $lineAmount = 0;                                               
                              if($adjustmentAccount->financialStatementTypeID == 7) {	
                                 $trialBalanceAdjustments++;	
                                 $financialStatementTypeDetails = Tax::financial_statements_types(array("financialStatementTypeID"=>$adjustmentAccount->financialStatementTypeID, "Lapsed"=>'N', "Suspended"=>'N'), true, $DBConn);
                                 $mappingFilter = array("financialStatementAccountID"=>$adjustmentAccount->financialStatementAccountID, "financialStatementID"=> $financialStatementID, 'entityID'=>$entityID, "Lapsed"=>'N', "Suspended"=>'N');
                                 $mappings = Tax::trial_balance_mapped_accounts($mappingFilter, false, $DBConn);
                                 $mappingTotalcr = 0;
                                 $mappingTotaldr = 0;
                                 $ledgerName = "";
                                 if($mappings){ 
                                    $display= true;                                          
                                    foreach ($mappings as $key => $mapping) {
                                       $accountName = $mapping->accountName;
                                       if($mapping->accountType === 'debit') {
                                                $mappingTotaldr += $mapping->debitValue;  
                                       } else {
                                                $mappingTotalcr += $mapping->creditValue;                                                               
                                       }
                                    }
                                    $ledgerName .= "{$mapping->accountName} ";
                                 }
                                 $lineAmount = ($mappingTotaldr - $mappingTotalcr) * $adjustmentAccount->accountRate;
                                 $finacialStatementAccounts = Tax::financial_statement_accounts(array("financialStatementAccountID"=>$adjustmentAccount->financialStatementAccountID, "Lapsed"=>'N', "Suspended"=>'N'), true, $DBConn);
                                 $accountName = "{$finacialStatementAccounts->accountName} - ({$financialStatementTypeDetails->financialStatementTypeName}) - ( {$ledgerName} )";
                              } elseif($adjustmentAccount->financialStatementTypeID == 5) {                                                 
                                 $statementOfInvestment = Tax::statement_of_investment_allowance_accounts(array("investmentAllowanceAccountID"=>$adjustmentAccount->financialStatementAccountID, "Lapsed"=>'N', "Suspended"=>'N'), true, $DBConn);   
                                 
                                 
                                 $mapedAccounts = Tax::investment_accounts_mapping(array("investmentAllowanceAccountID"=>$adjustmentAccount->financialStatementAccountID, 'entityID'=>$entityID, "Lapsed"=>'N', "Suspended"=>'N'), true, $DBConn);																
                                 if($mapedAccounts) { 
                                    $display= true;
                                    // var_dump($mapedAccounts);                                                   
                                    $accountDetails =Tax::statement_of_investment_data(array("InvestmentAllowanceID" => $mapedAccounts->InvestmentAllowanceID, "Lapsed"=>'N', "Suspended"=>'N'), true, $DBConn);
                                    // var_dump($accountDetails);
                                    if($accountDetails){
                                       $accountName = $accountDetails->investmentName;
                                       $lineAmount = $accountDetails->wearAndTearAllowance;
                                       if($accountDetails->InvestmentAllowanceID == 15){
                                          $investmentBuilding = $accountDetails->wearAndTearAllowance;
                                       }
                                    }
                                 }                                                
                              }
                              
                              if($lineAmount !=0) {
                                 $adjustmentAccountsTotal += abs($lineAmount); 
                                 // var_dump($lineAmount);                        
                                 // $adjustmentAccountsTotal += $adjustmentAccount->adjustmentAmount; ?>                                
                                 <div class="list-group-item py-1">
                                    <div class="row">
                                       <div class="col-6">
                                          <span  class="text-capitalize pb-0 my-0 fw-normal fs-14"><?php echo  str_replace('_', ' ',  $accountName) ?></span>
                                       </div>
                                       <div class="col-6 text-end">
                                          <span class="text-capitalize pb-0 my-0 text-end fw-semibold fs-14"><?php echo  Utility::formatToCurrency($lineAmount) ?></span>
                                       </div>
                                    </div>
                                 </div>
                                 <?php
                                 if($adjustmentType->adjustmentTypeID ==1) {
                                    $adbacksTotal += $lineAmount;
                                 } else {
                                    $deductionsTotal += $lineAmount;
                                 }                                 
                              }	
                           }
                           //  var_dump($adjustmentType);
                           if($adjustmentType->adjustmentTypeID ==2) {?>
                              <div class="list-group-item py-0">
                                 <div class="row">
                                    <div class="col-6">
                                       <span  class="text-capitalize pb-0 my-0 fw-normal fs-14">Investment allowance</span>
                                    </div>
                                    <div class="col-6 text-end">
                                       <span class="text-capitalize pb-0 my-0 text-end fw-normal fs-14"><?php $investmentAllowance = $wearTearAllowanceTotal - $investmentBuilding; 
                                       echo Utility::formatToCurrency($investmentAllowance) ?></span>
                                    </div>
                                 </div>
                              </div>
                              <div class="list-group-item py-1 list-group-item-danger">
                                 <div class="row">
                                    <div class="col-6">
                                       <h5  class="text-capitalize pb-0 my-0 fw-18">Deductions Total</h5>
                                    </div>
                                    <div class="col-6">
                                       <h5 class="text-capitalize pb-0 my-0 text-end fw-18">
                                          <?php $deductionsTotalSum = $deductionsTotal + $investmentAllowance;
                                          echo Utility::formatToCurrency($deductionsTotalSum) ?>
                                       </h5>                                    
                                    </div>
                                 </div>
                              </div>
                              <?php                           
                           } elseif($adjustmentType->adjustmentTypeID ==1) {?>
                              <div class="list-group-item py-1 list-group-item-danger">
                                 <div class="row">
                                    <div class="col-6">
                                       <h5  class="text-capitalize pb-0 my-0 fw-18">Add back Total</h5>
                                    </div>
                                    <div class="col-6">
                                       <h5 class="text-capitalize pb-0 my-0 text-end"><?php echo $adbacksTotalSum =  Utility::formatToCurrency($adbacksTotal) ?></h5>
                                    </div>
                                 </div>
                              </div>
                              <?php
                           }	?>														
                        </div>
                        <?php
                     }
                  }
               }?>
               <div class="list-group-item  list-group-item-action list-group-item-primary">
                  <div class="row">
                     <div class="col-6">
                        <span  class="text-capitalize pb-0 my-0 fs-16 fw-semibold">Adjusted taxable Profit (Loss)</span>
                     </div>
                     <div class="col-6">
                        <h5 class="text-capitalize pb-0 my-0 text-end fs-16 fw-semibold">
                           <?php                           
                           // echo "<p> Adbacks Total: {$adbacksTotal} </p>";
                           // echo "<p> Deductions Total: {$deductionsTotalSum} </p>";
                           // echo "<p> Investment Allowance: {$investmentAllowance} </p>";
                           // echo "<p> Investment Building: {$investmentBuilding} </p>";
                           $adjustedTaxableProfitLoss = $adbacksTotal + $deductionsTotalSum + $profit_loss_before_tax;
                           echo Utility::formatToCurrency($adjustedTaxableProfitLoss) ?></h5>
                        </h5>
                     </div>
                  </div>
               </div>
               <?php
               $previousYear = $incomeStatementDetails->fiscalYear - 1;
               $previousYearTaxableProfit = 0;
               // var_dump($previousYear);
               ?>
               <div class="list-group-item   list-group-item-success">
                  <div class="row">
                     <div class="col-6">
                           <span   class="text-capitalize pb-0 my-0 fs-16 fw-semibold text-dark">Previous Year Taxable Profit(<?php echo $previousYear; ?>)</span>
                     </div>
                     <div class="col-6 text-end">
                        <span class="text-capitalize pb-0 my-0 text-end fs-16 fw-semibold text-dark">
                           <?php 
                           $previousYearTaxableProfit = Tax::year_taxable_profit(array( 'entityID'=>$entityID, "Lapsed"=>'N', "Suspended"=>'N'), true, $DBConn);
                           // var_dump($previousYearTaxableProfit);
                           echo $previousYearTaxableProfitSum = $previousYearTaxableProfit ? Utility::formatToCurrency($previousYearTaxableProfit->taxableProfit) : "No Previous Year Taxable Profit";
                           ?>
                           <!-- Modal To add previous year taxable balance -->
                           <a href="#previousYearTaxableProfit" data-bs-toggle="modal" data-bs-target="#previousYearTaxableProfit" class="btn btn-primary btn-sm">Edit</a>
                        </span>                           
                     </div>
                     <?php
                     echo Utility::form_modal_header("previousYearTaxableProfit", "tax/admin/manage_year_taxable_profit.php", "Add Previous Year Taxable Profit", array('modal-md', 'modal-dialog-centered', "modal-xl"), $base);
                     include "includes/scripts/tax/modals/manage_year_taxable_profit.php";
                     echo Utility::form_modal_footer( "Add Previous Year Taxable Profit","add_previous_year_taxable_profit", 'btn  btn-secondary btn-lg', false);
                     ?>
                  </div>
               </div>

               <div class="list-group-item   ">
                  <div class="row">
                     <div class="col-6">
                           <span  class="text-capitalize pb-0 my-0 fs-16 fw-semibold text-dark">Taxable Profit(Loss) before Tax</span>
                     </div>
                     <div class="col-6 text-end">
                        <span class="text-capitalize pb-0 my-0 text-end fs-16 fw-semibold text-dark"  >
                           <?php 
                           if(isset($previousYearTaxableProfitSum) && isset($previousYearTaxableProfit->taxableProfit) ) {
                                 $currentProfitBeforeTax = $adjustedTaxableProfitLoss + $previousYearTaxableProfit->taxableProfit;
                                 echo Utility::formatToCurrency($currentProfitBeforeTax);
                              } ?>
                        </span>
                     </div>
                  </div>
               </div>

               <div class="list-group-item ">
                  <div class="row">
                     <div class="col-6">
                        <h5  class="text-capitalize pb-0 my-0">Tax</h5>
                     </div>
                     <div class="col-6">
                        <h5 class="text-capitalize pb-0 my-0 text-end">
                           <?php 
                         $tax = (isset($currentProfitBeforeTax) && $currentProfitBeforeTax >0) ? $currentProfitBeforeTax * 0.3 : 0;
                           echo Utility::formatToCurrency($tax);
                           ?>
                        </h5>
                     </div>
                  </div>
               </div>

               <div class=" list-group-item list-group-item-primary py-1 border-bottom border-primary">
                 <span  class="text-dark fw-bold fs-20  "> Tax Credits </span>

               </div>
               <div class="list-group-item   ">
                  <div class="row">
                     <div class="col-6">
                     <span  class="text-capitalize pb-0 my-0 h6 fw-normal">Withholding Tax</span>
                     </div>
                     <div class="col-6">
                        <h5 class="text-capitalize pb-0 my-0 text-end fs-16 fw-normal">
                           <?php 
                           $withholdingTax = Tax::year_withholding_tax(array( 'entityID'=>$entityID, "Lapsed"=>'N', "Suspended"=>'N'), true, $DBConn);
                           // var_dump($withholdingTax);
                           echo $withholdingTaxSum= $withholdingTax ? Utility::formatToCurrency($withholdingTax->withholdingTax) : "No Withholding Tax";
                           ?>
                           <a href="#withholdingTax" data-bs-toggle="modal" data-bs-target="#withholdingTax" class="btn btn-primary btn-sm">Edit</a>                           
                        </h5>
                        <?php 
                        echo Utility::form_modal_header("withholdingTax", "tax/admin/manage_withholding_tax.php", "Add Withholding Tax", array('modal-md', 'modal-dialog-centered', "modal-xl"), $base);
                        include "includes/scripts/tax/modals/manage_withholding_tax.php";
                        echo Utility::form_modal_footer( "Add Withholding Tax","add_withholding_tax", 'btn  btn-secondary btn-lg', false);
                        ?>
                     </div>
                  </div>
               </div>
               <div class="list-group-item  py-0 ">
                  <div class="row">
                     <div class="col-6">
                           <span  class="text-capitalize pb-0 my-0 h6 fw-normal">Advance Tax</span>
                     </div>
                     <div class="col-6">
                        <span class="text-capitalize pb-0 my-0 float-end h6 ">
                           <?php 
                           $advanceTax = Tax::year_advance_tax(array("fiscalYear"=>$incomeStatementDetails->fiscalYear, 'entityID'=>$entityID, "Lapsed"=>'N', "Suspended"=>'N'), true, $DBConn);
                           // var_dump($advanceTax);
                           echo $advancedTaxPaid = $advanceTax ? Utility::formatToCurrency($advanceTax->advanceTax) : "No Advance Tax";
                           ?>
                           <a href="#advanceTax" data-bs-toggle="modal" data-bs-target="#advanceTax" class="btn btn-primary btn-sm">Edit</a>
                        </span>
                        <?php 
                        echo Utility::form_modal_header("advanceTax", "tax/admin/manage_advance_tax.php", "Add Advance Tax", array('modal-md', 'modal-dialog-centered', "modal-xl"), $base);
                           include "includes/scripts/tax/modals/manage_advance_tax.php";
                        echo Utility::form_modal_footer( "Add Advance Tax","add_advance_tax", 'btn  btn-secondary btn-lg', false);?>
                     </div>
                  </div>
               </div>
               <div class=" list-group-item  py-1">                  
                  <div class="row">
                     <div class="col-6">
                        <span class ="text-dark fw-semibold fs-20 "> Tax Credits total </span>
                     </div>
                     <div class="col-6 text-end">
                        <span class="text-end text-dark fw-semibold fs-18 ">
                           <?php 
                           $withholdingTaxPaidAll = isset($withholdingTax->withholdingTax) ? $withholdingTax->withholdingTax : 0;
                           $advanceTaxPaid = isset($advanceTax->advanceTax) ? $advanceTax->advanceTax : 0;
                           // var_dump($withholdingTax);
                           $taxCreditsTotal = $withholdingTaxPaidAll + $advanceTaxPaid;
                           echo Utility::formatToCurrency($taxCreditsTotal);
                           ?>
                        </span>
                     </div>
                  </div>
               </div>
               <div class=" list-group-item list-group-item-danger py-1"> 
                  <div class="row">
                     <div class="col-6">
                        <h4> Tax Payable </h4>
                     </div>
                     <div class="col-6">
                        <h4 class="text-end">
                           <?php 
                           $taxPayable = $tax + $taxCreditsTotal;
                           echo Utility::formatToCurrency($taxPayable);
                           ?>
                        </h4>
                     </div>
                  </div>
               </div>
               <?php
            }?>                       
         </div>                    
      </div>
   </div>
   <?php
} else {?>
<div class="col-lg-6 mx-auto">
<a href="<?= "{$base}html/{$getString}&status=compute" ?>" class="btn btn-primary w-50">Compute Tax</a>
</div>
   
<?php
}

?>