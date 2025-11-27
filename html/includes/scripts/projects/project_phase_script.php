<?php 
  
  if ($phases) { 
      $allTaskTeam = array();
      // *Loop through each phase and include the project phase script
      foreach ($phases as $key => $phase) { 
         include "includes/scripts/projects/project_phase.php";      
      } 
   } else { 
      Alert::info("No Phases set up for this project");
   }?>
 
   <script>
      document.addEventListener('DOMContentLoaded', function () {
         // check for click on managePhaseCollapse
         document.querySelectorAll('.managePhaseCollapse').forEach(phaseCollapse => {
            phaseCollapse.addEventListener('click', function(e) {
               e.preventDefault();
               const data = this.dataset;
              
               // get the phase collapse form
               const managePhaseForm = phaseCollapse.parentElement.parentElement.parentElement.parentElement.querySelector('.managePhaseCollapseForm');
       
               // phaseCollapse.closest('.managePhaseCollapseForm');
               if (!managePhaseForm) {                 
                  return;
               } 
               const phaseStartDateInput = managePhaseForm.querySelector('#phaseStartDate');
               const phaseEndDateInput = managePhaseForm.querySelector('#phaseEndDate');
               const phaseDates = managePhaseForm.querySelector('.phaseDates');
               // console.log(phaseDates);

               // Check if all required elements exist
               if (!phaseStartDateInput || !phaseEndDateInput || !phaseDates) {
                  console.error('Required phase date elements not found');
                  return;
               }

               phaseStartDateInput.addEventListener('change', () => checkPhaseDates());
               phaseEndDateInput.addEventListener('change', () => checkPhaseDates());

               const checkPhaseDates = () => {
                  // Additional safety check
                  if (!phaseStartDateInput || !phaseEndDateInput || !phaseDates) {
                     return;
                  }
                  
                  const phaseStartDate = new Date(phaseStartDateInput.value);
                  const phaseEndDate = new Date(phaseEndDateInput.value);
                  console.log(phaseDates);

                  if (phaseEndDate < phaseStartDate) {
                     phaseEndDateInput.value = phaseStartDateInput.value;
                     const errorMessage = document.createElement('div');
                     errorMessage.textContent = 'Error: End date cannot be before start date.';
                     errorMessage.classList.add('error-message');
                     errorMessage.classList.add('text-danger');
                     errorMessage.classList.add('fst-italic');
                     errorMessage.classList.add('font-12');
                     errorMessage.classList.add('text-center');
                     errorMessage.classList.add('mb-2');
                     errorMessage.classList.add('border-bottom');
                     errorMessage.classList.add('border-danger');

                     phaseDates.appendChild(errorMessage);
                     phaseEndDateInput.classList.remove('is-valid');
                     phaseEndDateInput.classList.add('is-invalid');
                     phaseEndDateInput.classList.add('border-danger');
                     phaseEndDateInput.classList.remove("form-control-plaintext");
                     phaseEndDateInput.classList.add("form-control");

                  } else {

                     phaseEndDateInput.classList.remove('is-invalid');
                     phaseEndDateInput.classList.add('is-valid');
                     phaseEndDateInput.classList.remove('border-danger');
                     phaseEndDateInput.classList.remove("form-control");
                     phaseEndDateInput.classList.add("form-control-plaintext");
                     
                     // Remove error message if it exists
                     const existingErrorMessage = phaseDates.querySelector('.error-message');
                     if (existingErrorMessage) {
                        existingErrorMessage.remove();
                     }

                  }
               }

            });
         });
        
      });
   </script>
   <!-- Bottom Add new Phase Button -->
<div class="col-12 clearfix">
   <a class="font-14 float-end newPhase btn btn-sm btn-outline-primary rounded-pill"  
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
/* * * 
*Modal for adding a new task step/activity
*/
echo Utility::form_modal_header("add_task_step", "projects/manage_sub_task.php", "Add Task Step ", array("modal-lg", "modal-dialog-centered"), $base);
   include 'includes/scripts/projects/modals/manage_task_step.php'; 
echo Utility::form_modal_footer("Add SubTask", "addSubtask", "btn btn-primary submit");

/* 
*Modal for managing task list
* *
*/
echo Utility::form_modal_header("collapseTaskList", "projects/manage_project_task.php", "Manage Phase and Task Details", array('modal-xl', 'modal-dialog-centered'), $base);
include "includes/scripts/projects/add_task_with_list.php";
echo Utility::form_modal_footer("Save Task", "manageTsk", 'btn btn-primary btn-sm');  
?>	
<script>
   document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('.newTaskStep').forEach(taskStepLink => {
         taskStepLink.addEventListener('click', function(e) {
            e.preventDefault();
            const data= this.dataset;
           
            const taskDuration = document.querySelector('.taskDuration');
           
            taskDuration.innerHTML = `( ${data.projectTaskDuration} )`;
            taskDuration.classList.remove("d-none");
            document.querySelector('.subTaskDueDate').addEventListener('change', (e)=>{
               const subtaskDueDateValue = e.target.value;
              
               if (new Date(subtaskDueDateValue) > new Date(data.projectTaskDeadline)) {
                  alert("Subtask due date cannot be after the project task deadline.");
                  document.querySelector('.dateerror').innerHTML=`Subtask due date cannot be after the project task deadline. Subtask deadline has been reset to the TaskDeadline `
                  e.target.value = data.projectTaskDeadline;
               }
            });
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

           
            const data = this.dataset;
         

          
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
      
      document.querySelectorAll(".dueDateChange").forEach(dueDateChange => {
         dueDateChange.addEventListener("click", (e) => {
            e.preventDefault();
            console.log(e.target);
            let projectTaskID = dueDateChange.dataset.projectTaskId;         
            let projectTaskDeadline = dueDateChange.dataset.projectTaskDeadline;
            let projectTaskChangeDiv = dueDateChange.parentElement;
            
            const data = dueDateChange.dataset;
            console.log(data);

            console.log(projectTaskChangeDiv);

            // form 
            let changeDueDateForm = projectTaskChangeDiv.querySelector('.manageTaskDeadlineForm');
            if(!changeDueDateForm) return;
            

            changeDueDateForm.querySelector('.projectTaskID').value = projectTaskID;

           
            changeDueDateForm.querySelector('.taskDeadlineChange').addEventListener('change', (e) => {
               const newDueDate = e.target.value;
               console.log(newDueDate);
               // if (new Date(newDueDate) < new Date(projectTaskDeadline)) {
               //    alert("New due date cannot be before the current due date.");
               //    e.target.value = projectTaskDeadline; // Reset to original due date
               // }

               console.log(`new Date ${newDueDate} > phaseEndDate ${data.phaseEndDate}`);
               const phaseEndDate = data.phaseEndDate;
               if (new Date(newDueDate) > new Date(phaseEndDate)) {
                  // alert("New task deadline  due date cannot be after the phase end date.");
                  document.querySelector('.invalid-feedback').innerHTML = `New task deadline due date cannot be after the phase end date. <br /> Task deadline has been reset to the Phase End Date`;
                  e.target.classList.add('is-invalid');               
                  e.target.value = projectTaskDeadline; // Reset to original due date

               } else {
                  document.querySelector('.invalid-feedback').innerHTML = '';
                  e.target.classList.remove('is-invalid');
                  e.target.classList.add('is-valid');
               }
            });

            
            /*{
               changeDueDateForm = document.createElement('form');
               changeDueDateForm.classList.add('changeDueDateForm');
               changeDueDateForm.setAttribute('method', 'post');
               changeDueDateForm.setAttribute('action', 'projects/change_task_due_date.php');
               changeDueDateForm.innerHTML = `
                  <input type="hidden" name="projectTaskID" value="${projectTaskID}">
                  <input type="date" name="projectTaskDeadline" class="form-control form-control-sm" value="${projectTaskDeadline}" required>
                  <button type="submit" class="btn btn-primary btn-sm">Change Due Date</button>
               `;
               projectTaskChangeDiv.appendChild(changeDueDateForm);
            }*/


            console.log(projectTaskID, projectTaskDeadline);
            // document.querySelector('.projectTaskID').value = projectTaskID;
            // document.querySelector('.projectTaskName').value = projectTaskName;
            // document.querySelector('.projectTaskDueDate').value = projectTaskDueDate;
         });
      });
   });
</script>

         