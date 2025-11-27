<input type="text" name="adjustmentTypeID" id="adjustmentTypeID_<?php echo $adjustmentType->adjustmentTypeID ?>" value="<?php echo $adjustmentType->adjustmentTypeID ?>" />
<input type="text" name="instanceID" id="addBackDeductionsInstanceID" value="<?php echo $instanceID ?>" />
<input type="text" class="form-control" name="financialStatementTypeID" value="<?php  echo $financialStatementTypes->financialStatementTypeID ?>" >


<?php
$multiSelect = true;


$statementAccounts = Tax::financial_statement_accounts(array('Suspended'=>'N', 'parentAccountID'=>'0' ), false, $DBConn); 
if(isset($statementAccounts) && !empty($statementAccounts)) {?>
    <div class=" p-3 shadow">
        <?php
        foreach ($statementAccounts as $key => $account) { ?>
            <div  class="list-group-item list-group-item-action font-20 text-capitalize">
                <h3 class="border-bottom border-primary border-2"><?php echo $account->accountName; ?></h3>
                <?php 
                $childAccounts = Tax::financial_statement_accounts(array('Suspended'=>'N', 'parentAccountID'=>$account->financialStatementAccountID ), false, $DBConn);
                if(isset($childAccounts) && !empty($childAccounts)) {?>
                    <div class="list-group list-group-flush ml-6">
                        <?php                                              
                        foreach ($childAccounts as $key => $childAccount) { ?>
                            <div  class="list-group-item list-group-item-action font-18 text-capitalize">
                                <?php 
                                $childAccounts2 = Tax::financial_statement_accounts(array('Suspended'=>'N', 'parentAccountID'=>$childAccount->financialStatementAccountID ), false, $DBConn);
                                if(isset($childAccounts2) && empty($childAccounts2)) {?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="<?php echo  $multiSelect ? 'checkbox' :'radio' ?>" name="<?php echo  $multiSelect ? 'financialStatementAccountID[]' : 'financialStatementAccountID' ?>" id="<?php echo "selectedAccount_{$childAccount->financialStatementAccountID}_{$node}" ?>" value="<?php echo $childAccount->financialStatementAccountID ?>>
                                            <label class="form-check-label stretched-link " for="<?php echo "selectedAccount_{$childAccount->financialStatementAccountID}_{$node}" ?>">
                                            <?php echo str_replace('_', ' ', $childAccount->accountName);  ?> 
                                        </label>
                                    </div>
                                    <?php                                    
                                } else {	?>
                                    <a class="d-block text-dark" data-bs-toggle="collapse" href="#<?php echo "collapse_{$childAccount->financialStatementAccountID}" ?>" role="button" aria-expanded="false" aria-controls="<?php echo "collapse_{$childAccount->financialStatementAccountID}" ?>">
                                    <i class="bi-plus-circle fw-bold text-primary font-18"></i>
                                    <?php	echo str_replace('_', ' ',$childAccount->accountName); ?>
                                    </a>
                                    <?php
                                }?>
                                <div class="collapse" id="<?php echo "collapse_{$childAccount->financialStatementAccountID}" ?>">
                                    <div class="list-group list-group-flush ml-8 font-16">
                                        <?php
                                        if(isset($childAccounts2) && !empty($childAccounts2)) {																						
                                            foreach ($childAccounts2 as $key => $childAccount2) {	?>
                                                <div  class="list-group-item list-group-item-action font-14 text-capitalize">
                                                    <?php 
                                                    $childAccounts3 = Tax::financial_statement_accounts(array('Suspended'=>'N', 'parentAccountID'=>$childAccount2->financialStatementAccountID ), false, $DBConn);
                                                    if(isset($childAccounts3) && empty($childAccounts3)) {
                                                        // echo "<h4 class='float-end'> {$childAccount2->financialStatementAccountID} </h4>";
                                                        $mappedChild2 =Tax::mapped_accounts(array("financialStatementAccountID"=>$childAccount2->financialStatementAccountID, 'instanceID'=>$instanceID, 'Suspended'=>'N'), false, $DBConn);												
                                                    
                                                        ?>
                                                            <div class="form-check">
                                                                <input class="form-check-input"  type="<?php echo  $multiSelect ? 'checkbox' :'radio' ?>" name="<?php echo  $multiSelect ? 'financialStatementAccountID[]' : 'financialStatementAccountID' ?>" id="<?php echo "selectedAccount_{$childAccount2->financialStatementAccountID}_{$node}" ?>" Value="<?php echo $childAccount2->financialStatementAccountID?>" >
                                                                    <label class="form-check-label stretched-link " for="<?php echo "selectedAccount_{$childAccount2->financialStatementAccountID}_{$node}" ?>">
                                                                    <?php echo str_replace('_', ' ', $childAccount2->accountName);  ?> 
                                                                </label>
                                                            </div>
                                                        <?php
                                                    } else {?>
                                                        <a class="d-block text-dark" data-bs-toggle="collapse" href="#<?php echo "collapse_{$childAccount2->financialStatementAccountID}" ?>" role="button" aria-expanded="false" aria-controls="<?php echo "collapse_{$childAccount2->financialStatementAccountID}" ?>">
                                                            <i class="bi-plus-circle fw-bold text-primary font-18"></i>	<?php echo str_replace('_', ' ', $childAccount2->accountName);  ?>
                                                        </a>
                                                        <?php                                                        
                                                    }?>
                                                    <div class="collapse" id="<?php echo "collapse_{$childAccount2->financialStatementAccountID}" ?>">
                                                    
                                                        <div class="list-group list-group-flush ml-6 font-14">
                                                            <?php
                                                            if(isset($childAccounts3) && !empty($childAccounts3)) {
                                                                foreach ($childAccounts3 as $key => $childAccount3) { 
                                                                    $mappedChild3 =Tax::mapped_accounts(array("financialStatementAccountID"=>$childAccount3->financialStatementAccountID, 'instanceID'=>$instanceID, 'Suspended'=>'N'), false, $DBConn);
                                                                    $active = "";
                                                                    if($mappedChild3) { 
                                                                        $active= "<span class='badge bg-success float-end'>Mapped</span>";
                                                                    }
                                                                    ?>
                                                                    <div  class="list-group-item list-group-item-action font-14 text-capitalize">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input"  type="<?php echo  $multiSelect ? 'checkbox' :'radio' ?>" name="<?php echo  $multiSelect ? 'financialStatementAccountID[]' : 'financialStatementAccountID' ?>" id="<?php echo "selectedAccount_{$childAccount3->financialStatementAccountID}_{$node}" ?>" value="<?php echo $childAccount3->financialStatementAccountID ?>" >
                                                                                <label class="form-check-label stretched-link " for="<?php echo "selectedAccount_{$childAccount3->financialStatementAccountID}_{$node}" ?>">
                                                                                <?php echo str_replace('_', ' ', $childAccount3->accountName);
                                                                                    ?> 
                                                                            </label>
                                                                            <?php echo $active ?>
                                                                        </div>																													
                                                                    </div>
                                                                    <?php                                                                    
                                                                }
                                                            } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                        } ?>
                                    </div>
                                </div>
                            </div>
                            <?php                                                       
                        } ?>
                    </div>
                    <?php
                }?>
            </div>
            <?php
        } ?>
    </div>										
    <?php 
} else { 
    Alert::info("No accounts found", true, array('fst-italic', 'text-center', 'font-18'));
}?>

