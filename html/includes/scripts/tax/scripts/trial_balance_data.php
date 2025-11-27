<?php
$financialStatementData = Tax::financial_statementData(array("financialStatementID"=>$financialStatementID, "Suspended"=>'N'), false, $DBConn);
// var_dump($financialStatementData[8]);			
if($financialStatementData){
	$debitTotal = 0;
	$creditTotal = 0;?>												
	<div class='table'>
		<table class='table table-striped  table-hover table-xs trial_balance_table'>
			<thead>
				<tr>						
					<th>Account Code</th> 
					<th>Account Name</th>									                                                  
					<th>Account Category</th>									
					<th> Debit </th>
					<th> Credit </th>
					<th>&nbsp;&nbsp;</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$I=0;
				foreach ($financialStatementData as $key => $stmtData) {
					$I++;
					$checked = '';
					$mapped =Tax::trial_balance_mapped_accounts(array("financialStatementDataID"=>$stmtData->financialStatementDataID, 'entityID'=>$entityDetails->entityID, 'Suspended'=>'N'), true, $DBConn);
					if(!$mapped){												
						$debitTotal += $stmtData->debitValue;
						$creditTotal += $stmtData->creditValue;                                    
						$accountvalue= ($stmtData->accountType === 'credit') ? $stmtData->creditValue : $stmtData->debitValue; ?>                                   
						<tr>	
							<td><?php echo " {$stmtData->accountName}"; ?></td>	
							<td><?php echo " {$stmtData->accountCode}" ?></td>
																
							<td><?php echo $stmtData->accountCategory ?></td>												
							<td><?php echo ($stmtData->accountType === 'debit') ? $stmtData->debitValue : '-' ?></td>
							<td><?php echo ($stmtData->accountType === 'credit') ?$stmtData->creditValue: '-' ?></td>
							<td>
								<?php 
								/*if($mapped){
									echo "<span class='badge bg-success'>Mapped</span>";
								}?>
								<a href="#<?php echo "map_account_{$stmtData->financialStatementDataID}" ?>" data-bs-toggle="modal" data-financialStatementDataID="<?php echo $stmtData->financialStatementDataID; ?> data-accountName="<?php echo $stmtData->accountName ?>" class="btn btn-primary btn-sm">Map</a>
								<!-- <a href="#<?php echo "manage_account_{$stmtData->financialStatementDataID}" ?>" data-bs-toggle="modal" data-financialStatementDataID="<?php echo $stmtData->financialStatementDataID; ?>" class="btn btn-primary btn-sm">Edit</a> -->
								<a href="<?php echo "?{$getString}&action=manage&fsdID={$stmtData->financialStatementDataID}" ?>" class="text-success float-end"> <i class="uil-edit-alt"></i> edit</a>
								<?php 
								echo Utility::form_modal_header("manage_account_ {$stmtData->financialStatementDataID}", "tax/admin/map_financial_statement_account.php", "Map Statement Account", array('modal-md', 'modal-dialog-centered', "modal-xl"), $base);
								include "includes/scripts/modals/tax/map_financial_statement_accounts.php";
								echo Utility::form_modal_footer( "Map Account","map_account", 'btn  btn-secondary btn-lg', false);*/
								?>	
							</td>
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
} else {
	Alert::inkfo("No data found", true, array('fst-italic', 'text-center', 'font-18'));
}