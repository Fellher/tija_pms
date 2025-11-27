<div class="form-group">
	<!-- <label for=""> Project ID</label> -->
	<input type="hidden" name="projectID" value="" class=" form-control form-control-xs projectID " >
	<!-- <label for="">ProjectTaskID</label> -->
	<input type="hidden" name="projectTaskID" value="" class=" form-control form-control-xs projectTaskID">
	<!-- <label for="">Project Owner ID</label> -->
	<input type="hidden" name="projectManagerID" value="<?php echo $userDetails->ID ?>" class=" form-control form-control-xs projectManagerID ">
	<!-- <label for=""> Client ID</label> -->
	<input type="hidden" name="clientID" value="" class="form-control form-control-xs clientID" >
	<!-- <label for=""> Project Phase Id</label> -->
	<input type="hidden" name="projectPhaseID" value="" class=" form-control form-control-xs projectPhaseID ">
	
</div>
	
<div class="form-group col-md-12 "> 
	<div class="row">
		<div class="col-md-4">
			<input type="text"  name="projectTaskCode" class="border-bottom  form-control-plaintext taskCode" placeholder="Input task Code" value="" required> 
		</div>
		<div class="col-md-8">
			<input type="text"  name="projectTaskName" class="border-bottom form-control-plaintext projectTaskName" placeholder="Input Task Name" value="" required> 
		</div>
	</div>						
</div>
<div class="mb-3 row"> 
	
	<div class="form-group col-md-6">
		<label for="" class="nott mb-0 t400"> Task Hours Estimate</label>
		<input type="text" name="hoursAllocated" class="form-control form-control-sm form-control-plaintext bg-light-blue hoursAllocated" value="">
	</div>
	<div class="form-group col-md-6">
		<label for="" class="nott mb-0 t400 "> Task Weighting (percentage ratio)</label>
		<input type="text" min="0" max="100" step=".01" name="taskWeighting" id="taskWeighting" class="form-control form-control-sm form-control-plaintext bg-light-blue taskWeighting" value="">		
	</div>
	<div class="form-group col-md-6">
		<label class="nott  t400  mb-0">Task Start & Deadline: here</label>
		<input type="text" name="taskDateRange" class="form-control form-control-sm form-control-plaintext daterange col-md-8  bg-light-blue px-2 taskDateRange" value="" placeholder="select Task Start and end"  required />
	</div>
	<div class="form-group col-md-6">
		<label for="" class=" nott mb-0 t400"> Task Status</label>
		<select name="status" class="form-control form-control-sm form-control-plaintext bg-light-blue taskStatus" >
			<?php echo Form::populate_select_element_from_object($taskStatus, 'taskStatusID', 'taskStatusName',  '', '', 'Select task Status') ?>
		</select>
	</div> 

	<label for="" class="nott mb-0 t400 "> Task Notes/Description</label>

	<div class="form-group col-md-12">
		<textarea class="form-control taskDescription" name="taskDescription"   rows="3" placeholder="Edit  task Notes/description"> </textarea>
	</div>

    
</div>