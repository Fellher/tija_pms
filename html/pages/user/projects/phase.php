<?php 
if ($isValidUser) {
	$phaseID = (isset($_GET['phID']) && !empty($_GET['phID'])) ? Utility::clean_string($_GET['phID']) : null;
	$phaseDetails = Projects::project_phases(array('projectPhaseID'=>$phaseID), true, $DBConn);
	$projectID= $phaseDetails->projectID;
	$projectDetails=Projects::projects_full(array("projectID"=>$projectID), true, $DBConn);
	$clients = Client::clients(array("Suspended"=>"N"), false, $DBConn);
	$businessUnits= Data::business_units (array('Suspended'=>'N'), false, $DBConn);
	$billingRates= Projects::project_billing_rates(array("Suspended"=> "N"), false, $DBConn);		
	// $projectAssignments = Admin::project_assigments(array('projectID'=>$projectID, 'Suspended'=> 'N'), false, $DBConn );
	// $permissionProfiles = Admin::permision_profiles(array('Suspended'=>'N'), false, $DBConn);
	$allUsers= Data::users(array(), false, $DBConn);
	$projectTasks = Projects:: projects_tasks (array('projectID'=> $projectID), false, $DBConn);	
	$phases=Projects::project_phases (array('projectID'=> $projectID), false, $DBConn);
	$projectUserAssignments = Projects::task_user_assignment(array("projectID"=> $projectID), false, $DBConn);
	$taskStatus = Projects::task_status(array('Suspended' =>"N"), false, $DBConn);
	$projectTaskTypes = Projects::project_task_types(array(), false, $DBConn);
	$allEmployees = Data::users([], false, $DBConn);
	$getString .= "&pid={$projectID}&phID={$phaseID}";
	?>
	<script>
		let projectTasks= <?php echo json_encode($projectTasks); ?>,
		projectDetails =<?php echo json_encode($projectDetails); ?>,
		projectUserAssignments = <?php echo json_encode($projectUserAssignments) ?>,
		allEmployees =<?php echo json_encode($allUsers) ?>  ;
	</script>

	
	<?php
	echo Utility::form_modal_header("manageTask", "projects/manage_project_task.php", "Manage Task Details" , array('modal-lg', 'modal-dialog-centered'), $base);
		include 'includes/scripts/projects/modals/manage_project_task.php';
	echo Utility::form_modal_footer("Save Task", "manageTsk", 'btn btn-primary btn-sm'); 
	echo Utility::form_modal_header("collapseTaskList", "projects/manage_project_task.php", "Manage Phase and Task Details", array('modal-xl', 'modal-dialog-centered'), $base);
		include "includes/scripts/projects/add_task_with_list.php";
	echo Utility::form_modal_footer("Save Task", "manageTsk", 'btn btn-primary btn-sm');  ?>
	<div class="bg-white col-12 border-top border-bottom  mt-3 py-2">
		<div class="container-fluid">
			<div class="row nogutters ">
				<div class="col-md-7"> 
					<div class="row">
						<div class="col-sm-1">	
							<span class="avatar avatar-xs me-2 avatar-rounded text-dark">								
								<i class="uil-folder-open fs-22"></i>		
							</span>	
						</div>												
						<div class="col-sm">
							<h3 class="mb-0 t500"><?php echo $phaseDetails->projectPhaseName ?>
                        <span class="ms-5 font-16">
                           (Project : 
                           <a href="<?php echo "{$base}html/?s={$s}&ss={$ss}&p=project&id={$phaseDetails->projectID}" ?>">
                           <?php echo $phaseDetails->projectName; ?></a>)
                        </span>  
                     </h3>
						</div>
					</div>						
				</div>
				<div class="col-md">
					<span class="float-end font-22 font-secondary px-5" ><?php  echo date_format($dt,'l, d F Y ') ?></span>
				</div>
			</div>
		</div>
	</div>
	<?php
	if ($phaseDetails) {
		$phase = $phaseDetails;
		include "includes/scripts/projects/project_phase.php";
	}?>
	<div class="col-12 clearfix">
		<a class="font-14 p-4 float-end newPhase"  
			data-bs-toggle="modal" 
			href="#collapseTaskList" 
			role="button" 
			aria-expanded="false" 
			data-projectPhaseName="nonane"  
			aria-controls="collapseTaskList">
			<span class="float-end">
				<i class="icon-plus mx-3"></i>
				Add  New project Phase
			</span>
		</a>
	</div>
	<?php 
	echo Utility::form_modal_header("add_tasK_step", "projects/manage_sub_task.php", "Add Task Step ", array("modal-lg", "modal-dialog-centered"), $base);
			include 'includes/scripts/projects/modals/manage_task_step.php'; 
	echo Utility::form_modal_footer("Add SubTask", "addSubtask", "btn btn-primary submit");	?>	

	<script>
		document.addEventListener('DOMContentLoaded', function () {
			document.querySelectorAll('.newTaskStep').forEach(taskStepLink => {
					taskStepLink.addEventListener('click', function(e) {
						e.preventDefault();
						const projectTaskID = this.getAttribute('data-projecttaskid');
						const manageTaskStepForm = document.getElementById('manageTaskStepForm');
						if (manageTaskStepForm) {
							manageTaskStepForm.querySelector('input[name="projectTaskID"]').value = projectTaskID;
						}
					});
			});

			let newTaskInPhase = document.querySelectorAll('.newTaskInPhase');

			
			newTaskInPhase.forEach((task) => {
				console.log(task);
				task.addEventListener('click', function (e) {
					e.preventDefault();
					let projectPhaseID = task.dataset.projectphaseid;
					let projectPhaseName = task.dataset.projectphasename;
					let phaseWorkHrs = task.dataset.phaseworkhrs;
					let phaseWeighting = task.dataset.phaseweighting;

					console.log(projectPhaseID);
					const data = this.dataset;
					console.log(data);

					console.log(projectPhaseID, projectPhaseName, phaseWorkHrs, phaseWeighting);
					let addTaskForm = document.getElementById('addTaskForm');

					addTaskForm.querySelector('.projectPhaseID').value = projectPhaseID;
					addTaskForm.querySelector('.projectPhaseID').readOnly = true;
					addTaskForm.querySelector('.edit-phase-name').value = projectPhaseName;
					addTaskForm.querySelector('.edit-phase-name').readOnly = true;
					addTaskForm.querySelector('.phaseWorkHrs').value = phaseWorkHrs;
					addTaskForm.querySelector('.phaseWorkHrs').readOnly = true;
					addTaskForm.querySelector('.taskWeighting').value = phaseWeighting;
					addTaskForm.querySelector('.taskWeighting').readOnly = true;
				});
			});           
			// let addTaskForm = document.getElementById('addTaskForm');            
	});
	</script>
<?php

} else {
	Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
}
