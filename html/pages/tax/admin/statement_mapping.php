<?php
// var_dump($isValidAdmin);
if (!$isValidAdmin) {
   Alert::info("You need to be logged in as a valid administrator to access this page", true, 
       array('fst-italic', 'text-center', 'font-18'));
   exit;
}

// Initialize variables
$userID = Utility::clean_string($_GET['uid'] ?? $userDetails->ID);
$entityID = Utility::clean_string($_GET['eid'] ?? '');
$entityDetails = Data::entities_full(array("entityID"=>$entityID), true, $DBConn);

$financialStatementID = Utility::clean_string($_GET['finstmtID'] ?? '');
$investmentFinancialStatementID = Utility::clean_string($_GET['invID'] ?? '');



// Build query string
$getString .= "&uid={$userID}&eid={$entityID}&finstmtID={$financialStatementID}";


if ($investmentFinancialStatementID) $getString .= "&invID={$investmentFinancialStatementID}";
if ($financialStatementID) $getString .= "&stmtID={$financialStatementID}";

// Get income statement type
$incomeStatementType = Tax::financial_statements_types([
    "Lapsed" => 'N',
    "Suspended" => 'N',
    "statementTypeNode" => 'TrialBalance'
], true, $DBConn);

function renderMappedAccountRow($mappedValue, $index) {
    if (floatval($mappedValue->debitValue) <= 0 && floatval($mappedValue->creditValue) <= 0) {
        return;
    }
    $num = $index + 1;
    $amount = floatval($mappedValue->debitValue) > 0 ? $mappedValue->debitValue : $mappedValue->creditValue;
    ?>
    <div class="ms-3 row border-bottom">
        <div class='col'><?php echo "{$num}. {$mappedValue->accountName}" ?></div>
        <div class='col-2'><?php echo $mappedValue->accountCode ?></div>
        <div class='col-2'><?php echo $mappedValue->accountCategory ?></div>
        <div class='col-2'><?php echo $amount ?></div>
    </div>
    <?php
}

function renderAccountRadio($account, $mappedAccounts = null) {
    $accountId = $account->financialStatementAccountID;
    ?>
    <div class="form-check">
        <input class="form-check-input" 
               type="radio" 
               name="financialStatementAccountID" 
               id="selectedAccount_<?php echo $accountId ?>" 
               value="<?php echo $accountId ?>">
        <label class="form-check-label stretched-link" 
               for="selectedAccount_<?php echo $accountId ?>">
            <?php echo htmlspecialchars(str_replace('_', ' ', $account->accountName)) ?>
        </label>
    </div>
    <?php
    if ($mappedAccounts) {
        foreach ($mappedAccounts as $key => $mappedAccount) {
            renderMappedAccountRow($mappedAccount, $key);
        }
    }
}

function renderNestedAccounts($account, $level = 0, $entityID, $financialStatementID="", $DBConn) {
    $childAccounts = Tax::financial_statement_accounts([
        'Suspended' => 'N',
        'parentAccountID' => $account->financialStatementAccountID
    ], false, $DBConn);
    // var_dump($childAccounts);
    if (empty($childAccounts)) {
        $mappedAccounts = Tax::trial_balance_mapped_accounts([
            "financialStatementAccountID" => $account->financialStatementAccountID,
            'entityID' => $entityID,
            'Suspended' => 'N',
			"financialStatementID" => $financialStatementID,
        ], false, $DBConn);
        renderAccountRadio($account, $mappedAccounts);
		// var_dump($account);
        return;
    }
    ?>
    <a class="d-block text-dark" 
       data-bs-toggle="collapse" 
       href="#collapse_<?php echo $account->financialStatementAccountID ?>" 
       role="button" 
       aria-expanded="false">
        <i class="bi-plus-circle fw-bold text-primary font-18"></i>
        <?php echo htmlspecialchars(str_replace('_', ' ', $account->accountName)) ?>
    </a>
    <div class="collapse" id="collapse_<?php echo $account->financialStatementAccountID ?>">
        <div class="list-group list-group-flush ml-<?php echo ($level + 1) * 2 ?> font-<?php echo 18 - ($level * 2) ?>">
            <?php foreach ($childAccounts as $childAccount): ?>
                <div class="list-group-item list-group-item-action text-capitalize">
                    <?php renderNestedAccounts($childAccount, $level + 1, $entityID, $financialStatementID , $DBConn); ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}?>

<div class="container-fluid">
    <div class="row pt-2 bg-light px-lg-3 mb-3">
        <h2 class="mb-2 t300 fs-3 border-bottom border-primary">
            Tax Computation
            <span class="float-end t600 fs-4">
                (<?php echo htmlspecialchars($entityDetails->entityName) ?>)
            </span>
        </h2>
    </div>

    <div class="container-fluid">
        <h3 class="text-center">Select Statement to Calculate Tax

        <span class='float-end'>
            <a href="<?php echo (isset($_SESSION['mapURLReturn']) && !empty($_SESSION['mapURLReturn'])) ? "{$base}html/{$_SESSION['mapURLReturn']}" : "{$base}html/?s={$s}&ss={$ss}&p=data_upload&eid={$entityID}&finstmtID={$financialStatementID}" ?>" 
               class="btn btn-primary">back</a>
        </h3>

        
        <?php 
		if ($incomeStatementType): 
            $incomeStatement = Tax::financial_statements([
                "financialStatementTypeID" => $incomeStatementType->financialStatementTypeID,
                'entityID' => $entityID,
                "Lapsed" => 'N',
                "Suspended" => 'N'
            ], false, $DBConn);
            
            if ($incomeStatement): ?>
                <div class="list-group list-group-flush mb-4">
                    <?php foreach($incomeStatement as $stmt): 
                        $active = ($financialStatementID == $stmt->financialStatementID) ? "active" : "";
                        $label = "{$entityDetails->entityName} {$stmt->financialStatementTypeName} for full year {$stmt->fiscalYear}";
                        ?>
                        <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=data_upload&eid={$entityID}&finstmtID={$financialStatementID}" ?>" 
                           class="list-group-item list-group-item-action <?php echo $active ?>" 
                           aria-current="true">
                            <?php echo htmlspecialchars($label) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif;
        endif; ?>

        <form action="<?php echo "{$base}php/scripts/tax/admin/map_trial_balance.php" ?>" method="post">
            <div class="row">
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="text-center">Current Trial Balance Fields</h4>
                        </div>
                        <div class="card-body">
                            <?php
                            if($incomeStatementType) {
                                $statementData = Tax::financial_statementData(array("financialStatementID"=>$financialStatementID, 'entityID'=>$entityID, "Lapsed"=>'N', "Suspended"=>'N'), false, $DBConn);
                                // var_dump($statementData[3]);
                                ?>
                                <input type="hidden" name="financialStatementTypeID" value="<?php echo $incomeStatementType->financialStatementTypeID ?>">
                               
                                <input type="hidden" name="entityID" value="<?php echo $entityID ?>">
                                <input type="hidden" name="statementTypeNode" value="<?php echo $incomeStatementType->statementTypeNode ?>">
								<input type="hidden" name="financialStatementID" value="<?php echo $financialStatementID ?>">
                           
								<?php 
								if($statementData){
									$debitTotal = 0;
									$creditTotal = 0;?>                                                 
									<div class='table'>
										<table class='table table-striped  table-hover trial_balance_table'>
											<thead>
												<tr>
													<th>&nbsp;</th>
													<th>Account Name</th>
													<th>Account Code</th>                                                   
													<th>Account Category</th>
													<!-- <th>Account Value</th> -->
													<th> Debit </th>
													<th> Credit </th>
													<th>&nbsp;&nbsp;</th>
												</tr>
											</thead>
											<tbody>
												<?php
												foreach ($statementData as $key => $stmtData) {
													$checked = '';
													$mapped =Tax::trial_balance_mapped_accounts(array("financialStatementDataID"=>$stmtData->financialStatementDataID, 'entityID'=>$entityID, 'Suspended'=>'N'), true, $DBConn);
													if(!$mapped){                                                 
														$debitTotal += $stmtData->debitValue;
														$creditTotal += $stmtData->creditValue;                                    
														$accountvalue= ($stmtData->accountType === 'credit') ? $stmtData->creditValue : $stmtData->debitValue; ?>                                   
														<tr>
															<td>
															
																<div class="form-check form-switch">
																	<input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault" name="trial_balance_financialStatementDataIDs[]" value="<?php echo $stmtData->financialStatementDataID ?>" <?php echo $checked ?> >
																</div>
															
															</td>
															<td><?php echo $stmtData->accountName; 
															?></td>
															<td><?php echo $stmtData->accountCode ?></td>
															<td><?php echo $stmtData->accountCategory ?></td>
															<!-- <td><?php //echo Utility::formatToCurrency($accountvalue, "KES") ?></td> -->
															<td><?php echo ($stmtData->accountType === 'debit') ? Utility::formatToCurrency($stmtData->debitValue, "KES") : '-' ?></td>
															<td><?php echo ($stmtData->accountType === 'credit') ? Utility::formatToCurrency($stmtData->creditValue, "KES"): '-' ?></td>
															<td><?php if($mapped){
																echo "<span class='badge bg-success'>Mapped</span>";
															}  ?></td>
														</tr>
														<?php
													}
												}?>
											</tbody>
											<tfoot>
													<tr>
														<td colspan="3">Total</td>
														<td><?php echo Utility::formatToCurrency($debitTotal, "KES") ?></td>
														<td><?php echo Utility::formatToCurrency($creditTotal, "KES") ?></td>
													</tr>
											</tfoot>
										</table>                                                
									</div>
									<?php
								}
							}
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="text-center">Current statement Category fields</h4>
                        </div>
                        <div class="card-body">
                            <?php 
                            $statementAccounts = Tax::financial_statement_accounts(array('Suspended'=>'N', 'parentAccountID'=>'0' ), false, $DBConn); 
                            // var_dump($statementAccounts);
                            if(isset($statementAccounts) && !empty($statementAccounts)) {?>
                                <div class=" p-3 shadow">
                                    <?php
                                    foreach ($statementAccounts as $key => $account) { ?>
                                        <div  class="list-group-item list-group-item-action font-20 text-capitalize">
                                            <h3 class="border-bottom border-primary border-2"><?php echo $account->accountName; ?></h3>
                                            <?php 
                                            renderNestedAccounts($account, 0, $entityID, $financialStatementID, $DBConn);
                                            ?>
                                        </div>
                                        <?php
                                    } ?>
                                </div>
                                <?php
                            } else { 
                                Alert::info("No accounts found", true, array('fst-italic', 'text-center', 'font-18'));
                            } ?>
                            <button type="submit" class="btn btn-primary float-end my-6">Map Trial Balance</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>