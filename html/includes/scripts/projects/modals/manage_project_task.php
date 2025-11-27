
<div class="projectPlannerTaskForm" id="projectPlannerTaskForm" >
	<div class="form-group d-none ">
		<label for=""> Project ID</label>
		<input type="text" name="projectID" value="" class=" form-control form-control-xs projectID " >
		<label for="">ProjectTaskID</label>
		<input type="text" name="projectTaskID" value="" class=" form-control form-control-xs projectTaskID">
		
		<label for=""> Client ID</label>
		<input type="text" name="clientID" value="" class="form-control form-control-xs clientID" >
		<label for=""> Project Phase Id</label>
		<input type="text" name="projectPhaseID" value="" class=" form-control form-control-xs projectPhaseID ">	
	</div>
		
	<div class="form-group col-md-12 ">
		<div class="row">
			<div class="col-md-4  ">
				<input type="hidden"  name="projectTaskCode" class="border-bottom  form-control-plaintext taskCode " placeholder="Input task Code" value="<?= 	$taskCode = Utility::clientCode(Utility::generateRandomString(12), 10); ?>" required > 
			</div>
			<div class="col-md-12">
				<label for="projectTaskName" class="nott mb-0 t400"> Task Name <span class="text-danger"> *</span> </label>
				<input type="text"  name="projectTaskName" class="border-bottom form-control-plaintext projectTaskName bg-light-blue" placeholder="Input Task Name" value="" required> 
			</div>
		</div>						
	</div>
	
	<div class="mb-3 row gx-1"> 


		<div class="col-md-8 row taskDates gx-1 my-2" id="taskDates">	
			<div class="col-md-6 form-group">
				<label for="phaseStartDate" class="nott mb-0 t400 text-primary"> Phase Start Date <span class="text-danger"> *</span> </label>
				<input type="text" id="phaseStartDate" name="phaseStartDate" class="form-control-xs form-control-plaintext border-bottom phaseStartDate date ps-2" value="" placeholder="Phase start date" required readonly>
			</div>
			<div class="col-md-6 form-group">
				<label for="phaseEndDate" class="nott mb-0 t400 text-primary"> Phase End Date <span class="text-danger"> *</span> </label>
				<input type="text" id="phaseEndDate" name="phaseEndDate" class="form-control-xs form-control-plaintext border-bottom  phaseEndDate date ps-2" value="" placeholder="Phase end date" required readonly>
			</div>
			<div class="form-group col-md-6">
				<label for="taskStartDate" class="nott mb-0 t400 text-primary"> Task Start Date <span class="text-danger"> *</span> </label>
				<input type="text" id="taskStartDate" name="taskStart" class="form-control form-control-sm form-control-plaintext bg-light-blue taskStartDate date ps-2" value="" placeholder="MM/DD/YYYY" required>
			</div>

			<div class="form-group col-md-6">
				<label for="taskDueDate" class="nott mb-0 t400 text-primary"> Task Due Date <span class="text-danger"> *</span> </label>
				<input type="text" id="taskDeadline" name="taskDeadline" class="form-control form-control-sm form-control-plaintext bg-light-blue taskDeadline date ps-2" value="" placeholder="MM/DD/YYYY">
			</div>
		</div>
		<div class="col-md-4 my-2">
			
			<div class="form-group  ">
				<label for="" class="nott mb-0 t400 text-primary"> Task Hours Estimate <span class="text-danger"> *</span> </label>
				<input type="text" name="hoursAllocated" class="form-control form-control-sm form-control-plaintext bg-light-blue hoursAllocated" value="">
			</div>
		
			<div class="form-group  ">
				<label for="" class="nott mb-0 t400 text-primary"> Task Weighting (percentage ratio)</label>
				<input type="text" min="0" max="100" step=".01" name="taskWeighting" id="taskWeighting" class="form-control form-control-sm form-control-plaintext bg-light-blue taskWeighting ps-2" value="">		
			</div>
		</div>
		<?php $projectTeamMembers = isset($planData['teamMembers']) ? $planData['teamMembers'] : [];
		// var_dump($projectTeamMembers);
		?>
		<div class="form-group col-md-12 my-2">
				<label for="assignedTo" class="nott mb-0 t400 text-primary">Select Task Members</label>          
				<select class="form-control  " data-trigger  name="teamMemberIDs[]" id="teamMemberIDs" multiple>
					<?php 
					// Use planData if available, otherwise fall back to direct variable

					echo Form::populate_select_element_from_array($projectTeamMembers, 'userID', 'employeeName', '' , '' , 'Select team Member') 
					?>
				</select>
			</div>
		<!-- <div class="form-group col-md-4 my-2">
			<label for="" class="nott mb-0 t400">Task Type</label>
			<select name="projectTaskTypeID" class="form-control form-control-sm form-control-plaintext bg-light-blue projectTaskTypeID ps-2" >
				<?php 
				// Use planData if available, otherwise fall back to direct variable
				$taskTypes = isset($planData['projectTaskTypes']) ? $planData['projectTaskTypes'] : ($projectTaskTypes ?? []);
				echo Form::populate_select_element_from_object($taskTypes, 'projectTaskTypeID', 'projectTaskTypeName',  '', '', 'Select task Type') 
				?>
			</select>
		</div>
	 -->
		<!-- <div class="form-group col-md-4 my-2">
			<label for="" class=" nott mb-0 t400"> Task Status</label>
			<select name="status" class="form-control form-control-sm form-control-plaintext bg-light-blue taskStatus ps-2" >
				<?php echo Form::populate_select_element_from_object($taskStatus, 'taskStatusID', 'taskStatusName',  $taskStatus ? $taskStatus[0]->taskStatusID :'', '', 'Select task Status') ?>
			</select>
		</div>  -->
	</div>
	
	
	
	<div class="form-group col-md-12">
		<label for="" class="nott mb-0 t400 text-primary"> Task Notes/Description</label>
		<div class="form-group col-md-12">
			<textarea class="form-control taskDescription bg-light-blue " name="taskDescription"   rows="3" placeholder="Edit  task Notes/description"> </textarea>
		</div>    
	</div>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
		// Function to check task dates and update the due date if necessary

		//initialize tomSelect for multiple select for teamMemberIDs
		new TomSelect("#teamMemberIDs", {
			create: false,
			sortField: {
				field: "text",
				direction: "asc"
			}
		});



			let checkTaskDatesPlanner = window.checkTaskDatesPlanner || (() => {
				const taskStartDate = new Date(taskStartDateInput.value);
				const taskDueDate = new Date(taskDueDateInput.value);
				const phaseStartDate = new Date(phaseStartDateInput.value);
				const phaseEndDate = new Date(phaseEndDateInput.value);
			
				let errorMessages = [];
				
				// Clear any existing error messages
				const existingErrors = taskDates.querySelectorAll('.error-message');
				existingErrors.forEach(error => error.remove());
				
				// Helper function to format dates for display
				const formatDate = (date) => {
					return date.toLocaleDateString('en-US', { 
						year: 'numeric', 
						month: 'short', 
						day: 'numeric' 
					});
				};
				
				// Check if due date is before start date
				if (taskDueDate < taskStartDate) {
					errorMessages.push('Due date (' + formatDate(taskDueDate) + ') cannot be before start date (' + formatDate(taskStartDate) + ')');
					taskDueDateInput.classList.remove('is-valid');
					taskDueDateInput.classList.add('is-invalid', 'border-danger');
					taskDueDateInput.classList.remove("form-control-plaintext");
					taskDueDateInput.classList.add("form-control");
				} else {
					taskDueDateInput.classList.remove('is-invalid', 'border-danger');
					taskDueDateInput.classList.add('is-valid');
					taskDueDateInput.classList.remove("form-control");
					taskDueDateInput.classList.add("form-control-plaintext");
				}

				// Check if task start date is before phase start date
				if (taskStartDate < phaseStartDate) {
					errorMessages.push('Task start date (' + formatDate(taskStartDate) + ') cannot be before phase start date (' + formatDate(phaseStartDate) + ')');
					taskStartDateInput.classList.remove('is-valid');
					taskStartDateInput.classList.add('is-invalid', 'border-danger');
				} else {
					taskStartDateInput.classList.remove('is-invalid', 'border-danger');
					taskStartDateInput.classList.add('is-valid');
				}

				// Check if task start date is after phase end date
				if (taskStartDate > phaseEndDate) {
					errorMessages.push('Task start date (' + formatDate(taskStartDate) + ') cannot be after phase end date (' + formatDate(phaseEndDate) + ')');
					taskStartDateInput.classList.remove('is-valid');
					taskStartDateInput.classList.add('is-invalid', 'border-danger');
				}

				// Check if task due date is before phase start date
				if (taskDueDate < phaseStartDate) {
					errorMessages.push('Task due date (' + formatDate(taskDueDate) + ') cannot be before phase start date (' + formatDate(phaseStartDate) + ')');
					taskDueDateInput.classList.remove('is-valid');
					taskDueDateInput.classList.add('is-invalid', 'border-danger');
				}

				// Check if task due date is after phase end date
				if (taskDueDate > phaseEndDate) {
					errorMessages.push('Task due date (' + formatDate(taskDueDate) + ') cannot be after phase end date (' + formatDate(phaseEndDate) + ')');
					taskDueDateInput.classList.remove('is-valid');
					taskDueDateInput.classList.add('is-invalid', 'border-danger');
				}

				// Display all error messages
				if (errorMessages.length > 0) {
					const errorContainer = document.createElement('div');
					errorContainer.classList.add('error-message', 'text-danger', 'fst-italic', 'font-12', 'text-center', 'mb-2', 'border-bottom', 'border-danger');
					
					// Create individual error messages
					errorMessages.forEach((message, index) => {
						const errorDiv = document.createElement('div');
						errorDiv.textContent = '• ' + message;
						errorDiv.classList.add('mb-1');
						if (index > 0) {
							errorDiv.classList.add('mt-1');
						}
						errorContainer.appendChild(errorDiv);
					});
					
					taskDates.appendChild(errorContainer);
					
					// Auto-remove error messages after 60 seconds
					setTimeout(() => {
						const errorToRemove = taskDates.querySelector('.error-message');
						if (errorToRemove) {
							errorToRemove.remove();
						}
					}, 60000);
				}
			});
			
			// Store the function globally for reuse
			window.checkTaskDatesPlanner = checkTaskDatesPlanner;
			
			//   form in focus
			const projectPlannerTaskForm = document.getElementById('projectPlannerTaskForm');
			const taskStartDateInput = projectPlannerTaskForm.querySelector('#taskStartDate');
			const taskDueDateInput = projectPlannerTaskForm.querySelector('#taskDeadline');
			const taskDates = projectPlannerTaskForm.querySelector('.taskDates');
			const phaseStartDateInput = projectPlannerTaskForm.querySelector('#phaseStartDate');
			const phaseEndDateInput = projectPlannerTaskForm.querySelector('#phaseEndDate');
			console.log(taskDueDateInput);
			
			taskStartDateInput.addEventListener('change', checkTaskDatesPlanner);
			taskDueDateInput.addEventListener('change', checkTaskDatesPlanner);
			// Initialize the date inputs with today's date
			taskStartDateInput.value = new Date().toISOString().split('T')[0];
			taskDueDateInput.value = new Date().toISOString().split('T')[0];	
			phaseStartDateInput.value = new Date().toISOString().split('T')[0];

			// script to prepolulate form fields from the button dataset
			const editTask = document.querySelectorAll('.editTaskBtn');
			editTask.forEach(task => {
				task.addEventListener('click', () => {
					const taskID = task.dataset.projectTaskID;

					const taskForm = document.querySelector('.projectPlannerTaskForm');
					if(!taskForm) {
						log('Task form not found');
						return;
					} 

					const data = task.dataset;
					console.log(data);
					
            // Map form fields to their corresponding data attributes
					const fieldMapping = {
						'projectTaskID': 'projectTaskId',
						'projectID': 'projectId',
						'projectPhaseID': 'projectPhaseId',
						'clientID': 'clientId',
						'projectTaskName': 'projectTaskName',
						'taskStart': 'taskStart',
						'taskDeadline': 'taskDeadline',
						'hoursAllocated': 'taskHoursAllocated',
						'taskWeighting': 'taskWeighting',
						'projectTaskTypeID': 'projectTaskTypeId',
						'taskStatusID': 'taskStatusId',
						'taskDescription': 'taskDescription',
						'taskStatusID': 'taskStatusID',
						'projectTaskTypeID': 'projectTaskTypeId',
						'projectTaskCode': 'projectTaskCode',
						'assignees': 'assignees',
					
					
					};

					let teamMemberIDs = JSON.parse(data['assignees']);
					console.log(teamMemberIDs);
					const teamMemberIDsSelect = taskForm.querySelector('#teamMemberIDs');
					let teamMemberIDsSelectParentNode = teamMemberIDsSelect.parentNode;
					console.log(teamMemberIDsSelectParentNode);
					
					teamMemberIDsSelectParentNode.remove();
					for(const [field, dataKey] of Object.entries(fieldMapping)) {
						const input = taskForm.querySelector(`input[name="${field}"]`);
						if(input) {
							input.value = data[dataKey] || '';
						}
					}

					//text area with tinyMCE
					const textarea = taskForm.querySelector('textarea[name="taskDescription"]');
					if(textarea) {
						textarea.value = data['taskDescription'] || '';
						tinymce.init({
							selector: '.taskDescription',
							height: 100,
							plugins: 'advlist autolink link image lists charmap preview',
							menubar: false,
							resize: true,
							toolbar: false,
						});
						const editor = tinymce.get('taskDescription');
						if(editor) {
							editor.value = data['taskDescription'] || '';
						}
					}

				});
			});

			const addTask = document.querySelectorAll('.addTaskBtn');
			addTask.forEach(task => {
				task.addEventListener('click', () => {
					const taskID = task.dataset.projectTaskID;
					//get the form using query
					const taskForm = document.querySelector('.projectPlannerTaskForm');
					if(!taskForm) return;
					
					//get data from the button dataset
					const data = task.dataset;
					console.log(data);

					//map form fields to their corresponding data attributes
					const fieldMapping = {
						'projectTaskID': 'projectTaskId',
						'projectID': 'projectId',
						'projectPhaseID': 'projectPhaseId',
						'clientID': 'clientId',
						'phaseStartDate': 'phaseStartDate',
						'phaseEndDate': 'phaseEndDate',
						'taskStart': 'taskStart',
						'taskDeadline': 'taskDeadline',
					};
					for(const [field, dataKey] of Object.entries(fieldMapping)) {
						const input = taskForm.querySelector(`input[name="${field}"]`);
						if(input) {
							input.value = data[dataKey] || '';
						}
					}
				});
			});
			
			
			let checkTaskDatesPhase = window.checkTaskDatesPhase || (() => {
				const phaseStartDate = new Date(phaseStartDateInput.value);
				const phaseEndDate = new Date(phaseEndDateInput.value);
				
				
				
			});
		});
	
	
	</script>
</div>