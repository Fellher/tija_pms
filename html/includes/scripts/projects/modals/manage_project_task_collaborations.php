<fieldset class="row">
   <?php
   $projectPhases = Projects::project_phases(array('projectID' => $projectID), false, $DBConn);
   // Ensure $projectPhases is always an array (even if empty)
   if (!is_array($projectPhases)) {
       $projectPhases = array();
   }
   $workTypes = Data::work_types(array(), false, $DBConn);
   $projectTaskStatus = Projects::task_status(array('Suspended' => 'N'), false, $DBConn);
  ?>
   <legend class="col-form-label col-sm-12 col-md-12">Project Task Details</legend>
   <input type="hidden" name="projectID" value="<?= $projectID ?>" class="form-control form-control-sm projectID">
   <input type="hidden" name="projectTaskID" value="" class="form-control form-control-sm projectTaskID">


   <div class="col-sm-12 col-md-6">
      <label for="projectTaskName" class="form-label">Task Name</label>
      <input type="text" class="form-control-sm form-control-plaintext border-bottom bg-light-blue" id="projectTaskName" name="projectTaskName" required>
   </div>
   <div class="col-sm-12 col-md-6">
      <label for="projectPhase" class="form-label">Project Phase</label>
      <select class="form-select form-control-sm form-control-plaintext bg-light-blue border-bottom" id="projectPhaseID" name="projectPhaseID" required>
         <option value="">Select Project Phase</option>
         <?php if (!empty($projectPhases) && is_array($projectPhases)):
            foreach ($projectPhases as $phase) : ?>
            <option value="<?= $phase->projectPhaseID ?>"><?= $phase->projectPhaseName ?></option>
         <?php endforeach;
         else: ?>
            <option value="" disabled>No phases available</option>
         <?php endif; ?>
      </select>
   </div>
   <div class="col-sm-12 col-md-6">
      <label for="projectStartDate" class="form-label">Project Start Date</label>
      <input type="date" class="form-control-sm form-control-plaintext bg-light-blue border-bottom date" id="taskStart" name="taskStart" required>
   </div>

   <div class="col-sm-12 col-md-6">
      <label for="projectEndDate" class="form-label">Task Deadline</label>
      <input type="date" class="form-control-sm form-control-plaintext bg-light-blue border-bottom date" id="taskDeadline" name="taskDeadline" required>
   </div>

   <div class="col-sm-12 col-md-6">
      <label for="taskStatusPipe" class="form-label">Task Status</label>
      <select class="form-select form-control-sm form-control-plaintext bg-light-blue border-bottom px-2" id="taskStatusID" name="taskStatusID" required>
         <?= Form::populate_select_element_from_object($projectTaskStatus, 'taskStatusID', 'taskStatusName', '', '', 'Select Task Status') ?>
      </select>
   </div>

   <div class="col-sm-12 col-md-6">
      <label for="hoursAllocated" class="form-label">Hours Allocated</label>
      <input type="number" class="form-control-sm form-control-plaintext bg-light-blue border-bottom" id="hoursAllocated" name="hoursAllocated" required>
   </div>

   <div class="col-12 taskParticipants"></div>



</fieldset>

<div class=" border rounded p-3 my-3 bg-light shadow-sm">
   <h4 class="t300 fs-14 border-bottom border-dark" >Add Work hours</h4>
   <div class="project_client_phase_task"></div>
   <div class="row">

      <div class="form-group col-lg-4 col-sm-12 ">
         <label for="workHours" class="form-label">Work Hours</label>
         <!-- <input type="text" class="form-control-xs form-control-plaintext bg-light-blue border-bottom border-secondary border-top-0" id="workHours" value="02:00" name="workHours" required> -->
         <input type="text" class="form-control-xs form-control-plaintext bg-light-blue border-bottom border-secondary border-top-0 workHours" name="taskDuration" value="" placeholder="HH:MM" >
							<span class="workHoursError text-danger text-center fs-6 fst-italic"></span>
      </div>

      <div class="form-group col-lg-4 col-sm-12">
         <label for="workDate" class="form-label">Work Date</label>
         <input type="date" class="form-control-xs form-control-plaintext bg-light-blue border-bottom border-secondary border-top-0 date" value="<?= date('Y-m-d') ?>" id="taskDate" name="taskDate" required>
      </div>
      <div class="form-group col-lg-4 col-sm-12">
         <label for="workType" class="form-label">Work Type</label>
         <select class=" form-control-xs form-control-plaintext bg-light-blue border-bottom border-secondary border-top-0" id="workTypeID" name="workTypeID" required>

            <?php echo Form::populate_select_element_from_object($workTypes, 'workTypeID', 'workTypeName', '', '', 'Select Work Type') ?>
         </select>
      </div>

      <div class="form-group col-12">
         <label for="workDescription" class="form-label">Work Description</label>
         <textarea class="form-control-sm form-control-plaintext bg-light-blue border-botom rounded-2 border-secondary p-2" id="taskNarrative" name="taskNarrative" rows="3" required></textarea>
      </div>

   </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

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

		function validateTimeInput(element) {
			let value = element.value;
			if (value.includes('.')) {
				let decimalHours = parseFloat(value);
				let hours = Math.floor(decimalHours);
				let minutes = Math.round((decimalHours - hours) * 60);
				let formattedTime = `${hours < 10 ? '0' : ''}${hours}:${minutes < 10 ? '0' : ''}${minutes}`;
				element.value = formattedTime;
			}
			else {
				let timeParts = value.split(':');
				if (timeParts.length === 2) {
					let hours = parseInt(timeParts[0]);
					let minutes = parseInt(timeParts[1]);
					if (minutes > 59) {
						hours += Math.floor(minutes / 60);
						minutes = minutes % 60;
					element.nextElementSibling.textContent = "Invalid time format. Please enter time in HH:MM format. The extra minutes will be converted to hours";
					}
					if (isNaN(hours) || isNaN(minutes)) {
						element.value = '';
					} else {
						element.value = `${hours < 10 ? '0' : ''}${hours}:${minutes < 10 ? '0' : ''}${minutes}`;
					}
				} else {
					element.value = '';
				}
			}
		}
   document.querySelectorAll('.taskAlert').forEach(alert => {
      alert.addEventListener('click', function() {
         const data = this.dataset;

         const decodedTaskDetails = decodeURIComponent(data.taskDetails);
         const taskDetails = JSON.parse(decodedTaskDetails);
         console.log(taskDetails);
         const taskAssignees = JSON.parse(decodeURIComponent(data.taskAssignees) || '[]');

         console.log(taskAssignees);

         console.log(data);

          // Map form fields to their corresponding data attributes

          const fieldMappings = {
            'projectID': 'projectID',
            'projectTaskID': 'projectTaskID',
            'clientID': 'clientID',
            'projectPhaseID': 'projectPhaseID',
            'taskStatusID': 'taskStatusID',
            'projectTaskName': 'projectTaskName',
            'taskStart': 'taskStart',
            'taskDeadline': 'taskDeadline',
            'hoursAllocated': 'hoursAllocated',
          }

            // Populate form fields with data attributes
            for( const [field, dataAttr] of Object.entries(fieldMappings)) {
               // console.log(`Setting field ${field} with data attribute ${dataAttr}`);
               const input = document.querySelector(`input[name="${field}"]`);
               // console.log(input);
               // const input = document.querySelector(`input[name="${field}"]`);
               if (input) {
                  // console.log(taskDetails[dataAttr]);
                  // Use taskDetails if available, otherwise use data attribute

                  input.value = taskDetails[dataAttr] || data[dataAttr] || '';
               }
            }
           // If you have select elements that need special handling
            // (like setting selected options), handle them here
            const selects = ['projectPhaseID', 'taskStatusID'];
            selects.forEach(selectName => {
               const select = document.querySelector(`select[name="${selectName}"]`);
               if (select && (data[selectName] || taskDetails[selectName])) {
                  console.log(`Setting select ${selectName} with value ${taskDetails[selectName]}`);
                  // select.value = data[selectName];
                  select.value = taskDetails[selectName] || data[selectName] || '';
               }
            });

         // reverse htmlspecialchars(data.taskDetails);



      });
   });
});
</script>