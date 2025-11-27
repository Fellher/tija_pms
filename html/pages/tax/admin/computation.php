<?php 
// var_dump($isValidAdmin);
if (!$isValidAdmin) {
   Alert::info("You need to be logged in as a valid administrator to access this page", true, 
       array('fst-italic', 'text-center', 'font-18'));
   exit;
}
$status= (isset($_GET['status']) && !empty($_GET['status'])) ? Utility::clean_string($_GET['status']) : '';
$financialStatementID= (isset($_GET['finstmtID']) && !empty($_GET['finstmtID'])) ? Utility::clean_string($_GET['finstmtID']) : '';
$financialStatementDataID = (isset($_GET['fsdID']) && !empty($_GET['fsdID'])) ? Utility::clean_string($_GET['fsdID']) : '';
$organisations= Admin::org_data(array(), false, $DBConn);
if(!$organisations){
   Alert::info("No Organisations set up for this tax computation entity", true, 
         array('fst-italic', 'text-center', 'font-18'));
         exit;
} else {
   $entityID = (isset($_GET['eid']) && !empty($_GET['eid'])) ? Utility::clean_string($_GET['eid']): "";
   $entityDetails = Data::entities_full(array("entityID"=>$entityID), true, $DBConn);
   if(!$entityDetails){
      foreach ($organisations as $key => $organisation) {?>
         <div class="card custom-card">
            <div class="card-header justify-content-between">
               <div class="card-title"> <?= $organisation->orgName ?> </div>               
						<button type="button"class="btn btn-sm btn-primary-light shadow-sm manageEntityOrganisation" data-bs-toggle="modal" data-organisationId="<?= $organisation->orgDataID ?>"  data-bs-target="#manageEntity">
							<i class="fas fa-plus"></i>
							Add New Entity
						</button>
               </div>
            </div>
            <div class="card-body">
               <div class="row">
                  <div class="col-12 ">
                     <?php 
                     $entities = Data::entities_full(['orgDataID'=> $organisation->orgDataID, 'Suspended'=> 'N'], false, $DBConn);
                     if($entities) {?>
                        <div class="list-group">
                           <?php 
                           foreach ($entities as $key => $entity) {?>
                              <div class="list-group-item list-group-item-action" aria-current="true">
                                 <div class="d-sm-flex w-100 justify-content-between">
                                    <h6 class="mb-1 fw-semibold">    
                                       <?php echo $entity->entityName ?> </h6>
                                    <small>
                                       <div class="btn-list">                                          
                                          <a aria-label="anchor" href="javascript:void(0);" data-bs-toggle="tooltip" data-id = "<?php echo $entity->entityID ?>" data-bs-placement="top" data-bs-title="Edit" class="btn  btn-icon rounded-pill btn-secondary-light btn-wave btn-sm entityEdit "><i class="ti ti-pencil"></i></a>
                                          <a aria-label="anchor" href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Delete" class="btn  btn-icon rounded-pill  btn-danger-light btn-wave btn-sm entityDelete "><i class="ti ti-trash"></i></a>
                                          <a aria-label="anchor" href="<?= "{$base}html/{$getString}&eid={$entity->entityID}"  ?>" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Select Entity" class="btn  btn-icon rounded-pill  btn-primary-light btn-wave btn-sm entityDelete "><i class="ti ti-eye"></i></a>
                                       </div>
                                    </small>
                                 </div>
                                 <p class="mb-1"><?php echo $entity->entityDescription; ?></p>
                              </div>
                              <?php
                           } ?>
                        </div> 
                        <?php
                     }?>
                  </div>
               </div>
            </div>
         </div>
         <?php
      }
   } else {
         $getString .= "&eid={$entityID}";
			if(!$financialStatementID){
				$financialStatementID = (isset($_GET['stmtID']) && !empty($_GET['stmtID'])) ? Utility::clean_string($_GET['stmtID']) : "";
			}
        
         $investmentFinancialStatementID = (isset($_GET['invID']) && !empty($_GET['invID'])) ? Utility::clean_string($_GET['invID']) : "";
         if($investmentFinancialStatementID) {
            $getString .= "&invID={$investmentFinancialStatementID}";
         }
         if($financialStatementID) {
            $getString .= "&stmtID={$financialStatementID}";
         }?>
         <div class="container-fluid">
            <div class="row pt-2 bg-light px-lg-3 mb-3">
               <h2 class="mb-2 t300 fs-3 border-bottom border-primary"> Tax Computation  <span class="float-end t600 fs-4">(<?php echo $entityDetails->entityName ?>)</span></h2>
            </div>
         </div>
         <div class="container-fluid">
        		<h3 class="text-center">Select Statement to Calculate Tax</h3>
				<div class="row">
					<div class="col-6">
						<div class="card">
							<div class="card-header">
								<h4 class="text-center">Income Statement/Trial Balance</h4>
							</div>
							<div class="card-body">
								<?php 
								$financialStatementType= Tax::financial_statements_types(array("Lapsed"=>'N', "Suspended"=>'N', "statementTypeNode"=>'TrialBalance'), true, $DBConn);
								// var_dump($financialStatementType);
								if($financialStatementType) {?>
									<div class="col-12">
										<?php echo $financialStatementType->financialStatementTypeDescription ?>
									</div>


									<?php
									
									$financialStatementDetails= Tax::financial_statements(array("financialStatementTypeID"=>$financialStatementType->financialStatementTypeID, 'entityID'=>$entityID, "Lapsed"=>'N', "Suspended"=>'N'), false, $DBConn);
									// var_dump($financialStatementDetails);
									?>
									<div class="list-group list-group-flush mb-4">
										<?php
										$active = "";
										if($financialStatementDetails) {
											foreach($financialStatementDetails as $key => $stmt) {
												$active = (isset($financialStatementID) && $financialStatementID == $stmt->financialStatementID) ? "active" : "";	?>
												<a href="<?php echo "{$getString}&stmtID={$stmt->financialStatementID}" ?>" class="list-group-item list-group-item-action <?php echo $active ?>" aria-current="true">   <?php echo "{$entityDetails->entityName} {$stmt->financialStatementTypeName} for full year {$stmt->fiscalYear}" ?> </a>
												<?php                                   
											}
										}?>
									</div>
									<?php
									$stmtType = $financialStatementType;
									if(!$financialStatementID) {?>
										<div class="col-12 my-3">
											<div class="col-lg-6 mx-auto text-center ">                                 
												<a class="btn btn-primary btn-lg" href="#<?php echo "upload_statement_{$stmtType->statementTypeNode}" ?>" data-bs-toggle="modal"> Upload <?php echo $stmtType->financialStatementTypeName ?></a>                                                            
											</div>	
											<?php 
												echo Utility::form_modal_header("upload_statement_{$stmtType->statementTypeNode}", "tax/admin/upload_statement.php", "Manage {$stmtType->financialStatementTypeName}", array('modal-md', 'modal-dialog-centered', "modal-xl"), $base);
													include "includes/scripts/tax/modals/upload_statement.php";
												echo Utility::form_modal_footer( "Upload {$stmtType->financialStatementTypeName}","upload_{$stmtType->statementTypeNode}", 'btn  btn-secondary btn-lg', false); ?>											
										</div>

										<?php
									}
								}
								if($financialStatementID) {?>
									<div class="co-12 text-center">
										<a class="btn btn-primary btn-lg" href="#trialBalance" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="trialBalance" > View Trial Balance</a>
									</div>																
									<div class="collapse" id="trialBalance">   
										<div class="card card-body">   
											<div class="col-12">
												<h4 class="text-center">Trial Balance
												
												<?php $mapURL = "?s=tax&ss=admin&p=statement_mapping&finstmtID={$financialStatementID}&eid={$entityID}"; 
												$_SESSION['mapURLReturn'] = $getString;
												//var_dump($_SESSION['mapURLReturn']);
												?>

													<a  href="<?= "{$base}html/{$mapURL}" ?>" class="btn btn-primary btn-sm float-end">Map </a>

												</h4>
											</div>                   
											<?php
											if($financialStatementDetails){                                    
												$statementData = Tax::financial_statementData(array("financialStatementID"=>$financialStatementID, 'entityID'=>$entityID, "Lapsed"=>'N', "Suspended"=>'N'), false, $DBConn);
												// var_dump($statementData[3]);
												if($statementData){
													$debitTotal = 0;
													$creditTotal = 0;?>
													<div class='table'>
														<table class='table table-striped table-bordered'>
															<thead>
																<tr>
																	<th>Account Name</th>
																	<th>Account Code</th>
																	<th>Account Type</th>
																	<th>Account Value</th>
																	<th> Debit </th>
																	<th> Credit </th>
																</tr>
															</thead>
															<tbody>
																<?php
																foreach ($statementData as $key => $stmtData) {
																	//   var_dump($stmtData);
																	$debitTotal += $stmtData->debitValue;
																	$creditTotal += $stmtData->creditValue;                                    
																	$accountvalue= ($stmtData->accountType === 'credit') ? $stmtData->creditValue : $stmtData->debitValue; ?>                                   
																	<tr>
																		<td><?php echo $stmtData->accountName ?></td>
																		<td><?php echo $stmtData->accountCode ?></td>
																		<td><?php echo $stmtData->accountType ?></td>
																		<td><?php echo Utility::formatToCurrency($accountvalue, "KES") ?></td>
																		<td><?php echo ($stmtData->accountType === 'debit') ? Utility::formatToCurrency($stmtData->debitValue, "KES") : '-' ?></td>
																		<td><?php echo ($stmtData->accountType === 'credit') ? Utility::formatToCurrency($stmtData->creditValue, "KES"): '-' ?></td>
																	</tr>
																	<?php
																}?>
															</tbody>
															<tfoot>
																<tr>
																	<td colspan="5">Profit (loss) before tax</td>
																	<?php  $profit_loss_before_tax_init = $creditTotal - $debitTotal; ?>																		
																	<td><?php echo Utility::formatToCurrency($profit_loss_before_tax_init, "KES") ?></td>
																</tr>
																<tr>
																	<td colspan="4">Total</td>
																	<td><?php echo Utility::formatToCurrency($debitTotal, "KES") ?></td>
																	<td><?php echo Utility::formatToCurrency($creditTotal, "KES") ?></td>
																</tr>
															</tfoot>
														</table>                                                
													</div>
													<?php
												}
											}?>   
										</div>                        
									</div>
									<?php
								}?>                        
							</div>
						</div>
					</div>
						<div class="col-6">
							<div class="card">
								<div class="card-header">
									<h4 class="text-center">  Statement of Investment Allowance </h4>
								</div>
								<div class="card-body">
									<?php
									$statementOfInvestment = Tax::financial_statements_types(array("statementTypeNode"=>'StatementofInvestmentAllowance',  "Lapsed"=>'N', "Suspended"=>'N'), true, $DBConn);                      
									$investmentStatement = Tax::financial_statements(array("financialStatementTypeID"=>$statementOfInvestment->financialStatementTypeID, 'statementTypeNode'=>'StatementofInvestmentAllowance', 'entityID'=>$entityID, "Lapsed"=>'N', "Suspended"=>'N'), false, $DBConn);
									if($investmentStatement) {?>
										<div class="list-group list-group-flush">
											<?php
											$invActive = "";
											foreach($investmentStatement as $key => $stmt) {
												$invActive = (isset($investmentFinancialStatementID) && $investmentFinancialStatementID == $stmt->financialStatementID) ? "active" : "";?>
												<a href="<?php echo "{$getString}&invID={$stmt->financialStatementID}" ?>" class="list-group-item list-group-item-action <?php echo $invActive ?>"  aria-current="true">   <?php echo "{$entityDetails->entityName} {$stmt->financialStatementTypeName} for full year {$stmt->fiscalYear}" ?> </a>
												<?php                                   
											}?>
										</div>
										<?php
									}
									$stmtType = $statementOfInvestment;
									if(!$investmentFinancialStatementID) {
									?>
									<div class="col-12 my-3">
										<div class="col-lg-6 mx-auto text-center ">                                 
											<a class="btn btn-primary btn-lg" href="#<?php echo "upload_statement_{$stmtType->statementTypeNode}" ?>" data-bs-toggle="modal"> Upload <?php echo $statementOfInvestment->financialStatementTypeName ?></a>                                                            
										</div>	
										<?php 
											echo Utility::form_modal_header("upload_statement_{$stmtType->statementTypeNode}", "tax/admin/upload_statement.php", "Manage {$stmtType->financialStatementTypeName}", array('modal-md', 'modal-dialog-centered', "modal-xl"), $base);
												include "includes/scripts/tax/modals/upload_statement.php";
											echo Utility::form_modal_footer( "Upload {$stmtType->financialStatementTypeName}","upload_{$stmtType->statementTypeNode}", 'btn  btn-secondary btn-lg', false); ?>											
									</div>

									<?php
									}
									if($investmentFinancialStatementID) {
										$investmentStatementData = Tax::statement_of_investment_data(array("financialStatementID"=>$investmentFinancialStatementID, 'entityID'=>$entityID, "Lapsed"=>'N', "Suspended"=>'N'), false, $DBConn);
										if($investmentStatementData){
											$investmentTotal = 0;
											$investmentTotal = 0;?>
											<div class="col-12 text-center my-4">
												<a class="btn btn-primary btn-lg" href="#statementOfInvestment" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="statementOfInvestment"  >  view statement of Investment Allowance</a>
											</div>
											<div class="collapse mt-5" id="statementOfInvestment">
												<div class="card card-body">
													<h4> Statement of Investment Allowance
														<?php 
														$invMapUrl= "s=tax&ss=admin&p=map_investment&invID={$investmentFinancialStatementID}&eid={$entityID}";
														$_SESSION['mapURLReturn'] = $getString;
														?>

													<a href="<?= "{$base}html/?{$invMapUrl}" ?>" class="btn btn-primary btn-sm float-end">Map </a>
													</h4>
												<div class="table">
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
																<!-- <th>&nbsp; </th> -->
															</tr>
														</thead>
														<tbody>
															<?php
															$wearTearAllowanceTotal = 0;
															$disposalTotal = 0;
															$innitialTotal = 0;
															$additionTotal = 0;
															$mappedInvestmentCount=  0;
															
															foreach ($investmentStatementData as $key => $stmtData) {
																// var_dump($stmtData);
																// check if data is mappped
																$mappedInvestment = Tax::investment_accounts_mapping(array("InvestmentAllowanceID"=>$stmtData->InvestmentAllowanceID, 'entityID'=>$entityID, "Lapsed"=>'N', "Suspended"=>'N'), true, $DBConn);

																$mappedInvestment   ? $mappedInvestmentCount++ : "";
																// var_dump($mappedInvestment );
																if($stmtData->allowInTotal == 'Y') {
																	$investmentTotal += $stmtData->endWriteDownValue; 
																	$wearTearAllowanceTotal += $stmtData->wearAndTearAllowance;
																	$disposalTotal += $stmtData->disposals;
																	$additionTotal += $stmtData->additions;
																	$innitialTotal += $stmtData->initialWriteDownValue;
																}?>                                   
																<tr class =" <?php  echo  $stmtData->allowInTotal == 'N' ? " table-danger ": "";  echo ($mappedInvestment  ) ? "table-primary" : "" ?>">
																	<td><?php echo $stmtData->investmentName ?></td>
																	<td><?php echo $stmtData->rate *100 ?> %</td>
																	<td><?php echo Utility::formatToCurrency($stmtData->initialWriteDownValue, "") ?></td>
																	<td><?php echo $stmtData->beginDate ?></td>
																	<td><?php echo Utility::formatToCurrency($stmtData->additions, "") ?></td>
																	<td><?php echo Utility::formatToCurrency($stmtData->disposals, "") ?></td>
																	<td><?php echo Utility::formatToCurrency($stmtData->wearAndTearAllowance, "") ?></td>
																	<td><?php echo Utility::formatToCurrency($stmtData->endWriteDownValue, "") ?></td>
																	<td><?php echo $stmtData->endDate ?></td>
																	 <td>
																		<?php echo  $stmtData->allowInTotal == 'N' ? "Not included in total" : ""; ?>
																		<a href="#<?php echo "edit_{$stmtData->InvestmentAllowanceID}" ?>" data-bs-toggle="modal" data-InvestmentAllowanceID="<?php echo $stmtData->InvestmentAllowanceID; ?>" class="btn btn-primary btn-sm">Edit</a>
																	</td>
																</tr>
																<?php
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

												</div>
												
											</div>
											<?php
										}
									}?> 
								</div>
							</div>
						</div>

						<?php
						include 'includes/scripts/tax/scripts/computation.php';?>
						
				</div>
			</div>  
         <?php
         
   }
}
?>
