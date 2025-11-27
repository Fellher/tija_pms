<div class="container-fluid">
	<div class="card card-body mt-4 headerSummary" >
		<div class="row">
			<div class="col-md">
				<span class=" t400mb-2 d-block">
					Expected Hours
				</span>
				<div  class=" font-26">
					<?php echo Utility::format_time($config['stdWeekHours40'], ":", false) ?>
					<span class="font-14">hours</span>
				</div>
			</div>

			<div class="col-md">
				<span class=" t400mb-2 d-block">
					Work Hours(Week)
				</span>
				<div  class=" font-26 ">
					<span class="wkTime">00:00</span> <span class="font-14">hours</span>
				</div>
			</div>

			<div class="col-md">
				<span class=" t400mb-2 d-block">
					Absence
				</span>
				<div  class=" font-26">
					<span class="wkAbsTime">00:00</span><span class="font-14">hours</span>
				</div>
			</div>

			<div class="col-md">
				<span class=" t400mb-2 d-block">
					Billable
				</span>
				<div  class=" font-26">
					<span class="wkBillable">00:00</span>
				</div>
			</div>

			<div class="col-md">
				<span class=" t400mb-2 d-block">
					Productive
				</span>
				<div  class=" font-26">
					<span class="wkProd">00:00</span><span class="font-22">%</span>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="container-fluid">
	<?php
	include "includes/scripts/time_attendance/week_day_band.php";
	include "includes/scripts/time_attendance/work_log_entries.php";
	?>
</div>
<?php
// get the list of tasks Assigned to the employee
include "includes/scripts/time_attendance/my_tasks.php";
// get the list of activities assigned to the employee
$activities = Schedule::tija_activities(array('activityOwnerID'=>$employeeID), false, $DBConn);
$addActivity= false;
// include "includes/scripts/work/activity_display_script.php"; ?>


<script>
	document.addEventListener('DOMContentLoaded', function() {
		document.addEventListener('keydown', function(event) {
			if (event.key === 'Enter') {
				event.preventDefault();
			}
		});
		console.log('DOM is ready');
		// Get DOM elements
		const fileUploadInput = document.getElementById('fileUpload');
		const fileNameDisplay = document.getElementById('fileNameDisplay');

		// --- File Upload Logic ---
		fileUploadInput.addEventListener('change', function(event) {
				if (event.target.files && event.target.files.length > 0) {
					const fileName = event.target.files[0].name;
					fileNameDisplay.textContent = fileName; // Display the selected file name
					console.log("File selected:", fileName);
					// Here you would typically handle the file upload, e.g., via FormData and fetch
				} else {
					fileNameDisplay.textContent = 'No file selected'; // Clear if no file is selected or selection is cancelled
				}
		});

		// --- Work Hours Calculation Logic ---

		let timeInput = document.querySelectorAll('.workHours');
		timeInput.forEach(input => {
			input.addEventListener('blur', (e) => {
				validateTimeInput(e.target);
			});
		});
		// timeInput.addEventListener('blur', (e)=>{
		// 	validateTimeInput(e.target);
		// });



		// --- Dropdown Logic ---
		// Get all the leafProject elements
		const leafProjects = document.querySelectorAll('.leafProject');
      const leafPhases = document.querySelectorAll('.leafPhase');
      const leafTasks = document.querySelectorAll('.leafTask');

      // Get the selectedTaskValues element
      // const selectedTaskValues = document.querySelector('.selectedTaskValues');
      // Get the selectedText element
      const selectedText = document.querySelector('.selectedText');
      // Get the addWorkHourBtn element
      const addWorkHourBtn = document.querySelector('.addWorkHourBtn');
      // get the selection button


      // create new Task input
      let newTask= document.createElement('div');
      newTask.classList.add('form-group', 'col-md-12', 'py-2');
      newTask.innerHTML= `<label for="taskName" class="col-md-12 nott mb-0 t500 text-dark mb-2  "> Add New Task</label>
      <input type="text" class="form-control form-control-sm bg-light-blue border-2 border-info " name="taskName"  value="" placeholder="Task Name">`;

      // Add click event listeners to each element
      leafProjects.forEach(leafProject => {
         leafProject.addEventListener('click', function() {
            const currentForm = this.closest('form');
            console.log(currentForm);
            let selectedTaskValues = currentForm.querySelector('.selectedTaskValues');
            let projectID= this.getAttribute('data-project-id');
            let projectName= this.getAttribute('data-project-name');
            let clientID= this.getAttribute('data-client-id');
            let clientName= this.getAttribute('data-client-name');
            console.log(projectID, projectName, clientID, clientName);

            // selectedTaskValues.innerHTML= `<h4 class="text-capitalize t400 fs-16 selectedText ">Add Work Hours for ${clientName} : ${projectName}</h4>`;
            const projectInput = document.createElement('input');
            projectInput.type = 'hidden';
            projectInput.name = 'projectID';
            projectInput.value = projectID;
            projectInput.classList.add('form-control-sm', 'form-control-plaintext', 'border-bottom', 'bg-light');
            console.log(projectInput);

            selectedTaskValues.appendChild(projectInput);


            selectedTaskValues.appendChild(newTask);
            const selection = currentForm.querySelector('.selection');
            // selection
            selection.innerText= ` ${clientName} : ${projectName}`;
         });
      });

      leafPhases.forEach(leafPhase => {
         leafPhase.addEventListener('click', function() {
            const currentForm = this.closest('form');
            console.log(currentForm);
            let selectedTaskValues = currentForm.querySelector('.selectedTaskValues');
            let projectID= this.getAttribute('data-project-id');
            let projectName= this.getAttribute('data-project-name');
            let clientID= this.getAttribute('data-client-id');
            let clientName= this.getAttribute('data-client-name');
            let projectPhaseID= this.getAttribute('data-project-phase-id');
            let projectPhaseName= this.getAttribute('data-project-phase-name');
            console.log(projectID, projectName, clientID, clientName);
            // selectedTaskValues.innerHTML= `<h4 class="text-capitalize t300 fs-16 selectedText ">Add Work Hours for ${clientName} : ${projectName} : ${projectPhaseName} </h4>`;
            const projectInput = document.createElement('input');
            projectInput.type = 'hidden';
            projectInput.name = 'projectID';
            projectInput.value = projectID;
            projectInput.classList.add('form-control-sm', 'form-control-plaintext', 'border-bottom', 'bg-light');
            console.log(projectInput);
            selectedTaskValues.appendChild(projectInput);
            // create input for projectPhaseID
            const projectPhaseInput = document.createElement('input');
            projectPhaseInput.type = 'hidden';
            projectPhaseInput.name = 'projectPhaseID';
            projectPhaseInput.value = projectPhaseID;
            projectPhaseInput.classList.add('form-control-sm', 'form-control-plaintext', 'border-bottom', 'bg-light');
            console.log(projectPhaseInput);
            selectedTaskValues.appendChild(projectPhaseInput);
            selectedTaskValues.appendChild(newTask);
            // selection
            const selection = currentForm.querySelector('.selection');
            selection.innerText= ` ${clientName} : ${projectName} : ${projectPhaseName}`;
            // create input for projectTaskID
         });
      });

      leafTasks.forEach(leafTask => {
         leafTask.addEventListener('click', function() {
            const currentForm = this.closest('form');
            console.log(currentForm);
            let selectedTaskValues = currentForm.querySelector('.selectedTaskValues');
            let projectID= this.getAttribute('data-project-id');
            let projectName= this.getAttribute('data-project-name');
            let clientID= this.getAttribute('data-client-id');
            let clientName= this.getAttribute('data-client-name');
            let projectPhaseID= this.getAttribute('data-project-phase-id');
            let projectPhaseName= this.getAttribute('data-project-phase-name');
            let projectTaskID= this.getAttribute('data-project-task-id');
            let projectTaskName= this.getAttribute('data-project-task-name');
            console.log(projectID, projectName, clientID, clientName);
            // selectedTaskValues.innerHTML= `<h4 class="text-capitalize t300 fs-16 selectedText ">Add Work Hours for ${clientName} : ${projectName} : ${projectPhaseName} </h4>`;

            const projectInput = document.createElement('input');
            projectInput.type = 'hidden';
            projectInput.name = 'projectID';
            projectInput.value = projectID;
            projectInput.classList.add('form-control-sm', 'form-control-plaintext', 'border-bottom', 'bg-light');
            console.log(projectInput);

            // create input for projectPhaseID
            const projectPhaseInput = document.createElement('input');
            projectPhaseInput.type = 'hidden';
            projectPhaseInput.name = 'projectPhaseID';
            projectPhaseInput.value = projectPhaseID;
            projectPhaseInput.classList.add('form-control-sm', 'form-control-plaintext', 'border-bottom', 'bg-light');
            console.log(projectPhaseInput);

            // create input for projectTaskID
            const projectTaskInput = document.createElement('input');
            projectTaskInput.type = 'hidden';
            projectTaskInput.name = 'projectTaskID';
            projectTaskInput.value = projectTaskID;
            projectTaskInput.classList.add('form-control-sm', 'form-control-plaintext', 'border-bottom', 'bg-light');
            console.log(projectTaskInput);

            // create input for projectTaskName
            const projectTaskNameInput = document.createElement('input');
            projectTaskNameInput.type = 'hidden';
            projectTaskNameInput.name = 'projectTaskName';
            projectTaskNameInput.value = projectTaskName;
            projectTaskNameInput.classList.add('form-control-sm', 'form-control-plaintext', 'border-bottom', 'bg-light');
            console.log(projectTaskNameInput);

            let taskName =document.createElement('div');
            taskName.classList.add('font-16', 't600', 'text-primary', 'd-block', 'fst-italic');
            taskName.innerHTML= `<span class="text-dark me-2"> Task Name: </span> (${projectTaskName}) `;
            selectedTaskValues.innerHTML= taskName.outerHTML;
            selectedTaskValues.appendChild(projectInput);
            selectedTaskValues.appendChild(projectPhaseInput);
            selectedTaskValues.appendChild(projectTaskInput);
            selectedTaskValues.appendChild(projectTaskNameInput);
            const selection = currentForm.querySelector('.selection');
            // create input for projectTaskName
            selection .innerText= ` ${clientName} : ${projectName} : ${projectPhaseName} `;
         });
      });



      let dropdownClose = document.querySelectorAll('.dropdownClose');
      // Add click event listener to the dropdownClose elements
      dropdownClose.forEach(dropdownClose => {
         // close the dropdown on click using the id
         dropdownClose.addEventListener('click', function() {
            document.getElementById('dropdownMenuClickableInside').click();
         });
      });
      // dropdownClose.addEventListener('click', function() {
      //    // close the dropdown on click using the id
      //    document.getElementById('dropdownMenuClickableInside').click();
      // });
});

</script>
