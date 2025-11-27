<div class="form-group col-md-12">
	<label for="expense" class="col-md-12 nott mb-0 t500 text-dark  "> Expenses</label>
	<input type="hidden" name="userID" id="expenseUserID" class="form-control-xs" value="<?php echo $userID ?>">
	<div class="row">
		<div class="col-md-6  col-sm-12">
			<div class="row">
				<div class="col-sm-12">
					<div class="form-group">
						<label id="target" for="project" class="mb-0 nott t500"> Select case(Project/Sale)</label>
						<select name="projectID" id="expprojectID" class="form-control form-control-sm form-control-plaintext bg-light-blue projectID" >
							<?php echo Form:: populate_select_element_from_grouped_object($projectArray, 'projectID', 'projectName',  '', '', $blankText='Select:') ?>
						</select>
					</div>												
				</div>
				<div class="form-group col-12 ">
					<label for="expenseTyoe"  class="nott mb-0 t500 text-dark "> Expense Type</label>
					<select name="expenseTypeID" id="expenseTypeID" class="form-control-sm  form-control-plaintext bg-light-blue expenseTypeID" > 
						<?php echo Form::populate_select_element_from_object($expenseTypes, 'expenseTypeID', 'typeName', '', '', 'select Expense Type') ?>
					</select>
				</div>
				<div class="form-group col-md-6 col-sm-12">
					<label for="taskDate" class="nott mb-0 t500 text-dark ">Expense Date</label>
					<input type="text" id="date" value="<?php  echo date_format($dt,'Y-m-d') ?>"  name="expenseDate"  class="form-control  form-control-sm form-control-plaintext bg-light-blue text-left component-datepicker past-enabled expenseDate" placeholder="YYYY-MM-DD">
				</div>	
				
				<div class=" form-group col-md-6 col-sm-12 ">
					<label for="expenseAmount" class="nott mb-0 t500 text-dark "> Expense Value</label>
					<input type="text" class="col-md-6 form-control form-control-sm form-control-plaintext bg-light-blue px-2 expenseAmount" name="expenseAmount" placeholder="amount in KES" value="">
				</div>
				<div class="form-group col-md-12">
					<label for="expenseDocument" class="nott mb-0 t500 text-dark">Expense Document</label>
					<input type="file" id="expenseDocuments" name="expenseDocuments[]" class="form-control form-control-sm form-control-plaintext bg-light-blue" multiple>
				</div>
			</div>
		</div>
		<div class="col-md-6 col-sm-12">
			<div class="form-group col-md-12 py-2">
				<label for="expenseDescription" class="col-md-12 nott mb-0 t500 text-dark mb-2  "> Expense Notes(description)</label>
				<textarea class="form-control basic borderless-mini" name="expenseDescription"  id="expenseDescription"  rows="3" placeholder="Edit time Expense description"></textarea>
			</div>									
		</div>
	</div>												
</div>				