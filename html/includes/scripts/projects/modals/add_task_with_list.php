<!-- <form  id="EditDetailsForm<?php  echo isset($phase->projectPhaseID) ? $phase->projectPhaseID :''  ?>" class="mb-0 editform" action="<?php echo $base ?>php/scripts/projects/manage_project_task.php" method="post" enctype="multipart/form-data"> -->
<div class="add-task-form" id="addTaskForm">
	<div class="form-group">
		<label class="form-label">Project ID</label>
	<input type="text" class="form-control form-control-sm" name="projectID" value="<?php echo $projectID ?>">
	<label for="projectManagerID">Project Manager ID</label>
	<input type="text" class="form-control form-control-sm" name="projectManagerID" value="<?php echo $userDetails->ID; ?>">
	<label for="category">Category</label>
	<input type="text" class="form-control form-control-sm" name="category" value="assignment">
	<label for="clientID">Client ID</label>
	<input type="text" class="form-control form-control-sm" name="clientID" value="<?php 
		// Use planData if available, otherwise fall back to direct variable
		$clientID = isset($planData['projectDetails']->clientID) ? $planData['projectDetails']->clientID : ($projectDetails->clientID ?? '');
		echo $clientID;
	?>">
	<div class="row">
		<div class="col-md-6">
			<div class="form-group col-12 my-2">
				<input type="Text" name="projectPhaseID"  placeholder="Please submit Task List Name" value="" class="form-control form-control-sm form-control-plaintext projectPhaseID bg-light-blue" >
				<input type="text" name="projectPhaseName"  placeholder="Please submit project Phase Name " class="border-bottom form-control-plaintext bg-light edit-phase-name "  value="" >
				
			</div>
			<div class="form-group col-12 my-2">
				<input type="text" name="phaseWorkHrs"  placeholder="submit allocated hrs" class="border-bottom form-control-plaintext bg-light   phaseWorkHrs " value="" >
			</div>
				<?php			
				$codePrase = Utility::generateRandomString(12);
				$taskCode = Utility::clientCode($codePrase, 10);	?>
			<div class="form-group col-md-12 my-`">
				<div class="row">
					<div class="col-md-3">
						<input type="text"  name="projectTaskCode" class="border-bottom form-control-plaintext bg-light-blue px-2" placeholder="Input task Code" value="<?php echo $taskCode; ?>" required> 
					</div>
					<div class="col-md-9">
						<input type="text"  name="projectTaskName" class="border-bottom form-control-plaintext bg-light-blue px-2" placeholder="Input Task Name" required> 
					</div>
				</div>				
			</div>	

			<div class="form-group col-md-12 my-1">
				<label for="assignedTo" class="nott font-16 text-primary t400">Select Task Members</label>          
				<select class="form-control choices-multiple-default " data-trigger  name="teamMemberIDs[]" id="teamMemberIDs" multiple>
					<?php 
					// Use planData if available, otherwise fall back to direct variable
					$employees = isset($planData['employeesByJobTitle']) ? $planData['employeesByJobTitle'] : ($employeesByJobTitle ?? []);
					echo Form::populate_select_element_from_grouped_object($employees, 'ID', 'employeeName', '' , '' , 'Select team Member') 
					?>
				</select>
			</div>

			<div class=" form-group col-md-12 my-1 taskPeriod">				
				<label class="nott font-16 t400 col-md-12 text-primary ">Task Start & Deadline:</label>
				<div class="row">
					<div class="col-md">
						<input type="text" name="taskStart" id="taskStart" class="form-control-sm form-control-plaintext bg-light-blue col-md-8 px-3  date" value="" placeholder="Task Start Date "  required />
					</div>
					<div class="col-md">
					<input type="text" name="taskDeadline" id="taskDeadline" class="form-control-sm form-control-plaintext bg-light-blue col-md-8 px-3  date" value="" placeholder="Task end/Deadline Date "  required />
					</div>
				</div>
			</div>
			
		</div>
		<div class="col-md-6 my-1">
			<div class="form-group col-md-12">
				<textarea class="form-control  borderless-md" name="taskDescription"    rows="3" placeholder="Task Notes & description"></textarea>
			</div>			
		</div>
	</div>
		
</div>
<?php

?>
<!-- </form> -->
 <script>
	// document.addEventListener("DOMContentLoaded", function() {

	// 	const addTaskInPhase = document.querySelectorAll('.addTaskInPhase');
	// 	addTaskInPhase.forEach((task) => {
	// 		task.addEventListener('click', function(e) {
	// 			e.preventDefault();
	// 			const phaseID = task.getAttribute('data-projectPhaseId');
	// 			const phaseName = task.getAttribute('data-projectPhaseName');
	// 			console.log('=== ADD TASK BUTTON CLICKED ===');
	// 			console.log('Button element:', task);
	// 			console.log('Button attributes:', task.attributes);
	// 			const phaseID = task.getAttribute('data-projectPhaseId');
	// 			const phaseName = task.getAttribute('data-projectPhaseName');
	// 			const phaseWorkHrs = task.getAttribute('data-phaseWorkHrs');
	// 			const phaseWeighting = task.getAttribute('data-phaseWeighting');
	// 			const phaseStartDate = task.getAttribute('data-phaseStartDate');
	// 			const phaseEndDate = task.getAttribute('data-phaseEndDate');
	// 			console.log(phaseID, phaseName, phaseWorkHrs, phaseWeighting, phaseStartDate, phaseEndDate);
	// 		});
			
	// 	});

	// });

 </script>
		
	

