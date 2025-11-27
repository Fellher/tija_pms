<?php 
$expenses = Projects::project_expenses(array("userID"=> $userID, "expenseDate"=> $DOF), false, $DBConn);
if ($expenses) {
	foreach ($expenses as $key => $expense) {	?>
		<div class="col-12 border shadow my-3 expenseBrief "  id="">
			<div class="row" >
				<div class="col-1 d-flex justify-content-center align-items-center">
				<span class="avatar bd-blue-800 avatar-lg me-2 avatar-rounded">
    				<i class="fa-solid fa-calculator"></i>
				</span>
				</div>
			
				
				<div class=" col-md row my-2">
						<div class="col-md-5 text-start">
						<?php echo "{$expense->clientName}: {$expense->projectName} <br/>
						{$expense->expenseTypeName}"?>

						</div>
						<div class="col-md-2 font-22 mt-3">
							KES <?php echo number_format($expense->expenseAmount, 2, ".", "," ) ?>
						</div>
						<div class=" col-md">
							<?php echo $expense->expenseDescription ?>
							<span class="float-end"> <a href="" class="userExpense" data-id="<?php echo $expense->expenseID ?>"><i class="fa-solid fa-file-edit font-26"></i></a> </span>
						</div>
				</div>
			</div>
		</div>
		<?php							
	}							
} ?>