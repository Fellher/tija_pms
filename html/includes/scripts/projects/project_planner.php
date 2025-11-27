
   <div class="countainer">
      <?php 
      $phases= Projects::project_phases(array('projectID' => $projectID), false, $DBConn); 
      if($phases) {
         foreach ($phases as $phaseKey => $phase) {
            $phaseTasks = Projects::project_tasks(array('projectPhaseID' => $phase->projectPhaseID), false, $DBConn);
            
            ?>
         <div class="card card-body my-3">            
            <div class="card-header d-flex justify-content-between">
               <span class="card-title mb-0 fs-18 ">Phase: <?php echo $phase->projectPhaseName; ?></span>
               <div class="phaseHours">
                  <span class="font-16 t600">PhaseHours: </span><?php echo $phase->phaseWorkHrs; ?>
               </div>
               <div class="card-tools">                 
                  <button type="button" class="btn  btn-icon rounded-pill btn-primary-light btn-sm newTask"
                     data-bs-toggle="modal" 
                     data-bs-target="#manageProjectPhaseTask" 
                     role="button" 
                     aria-expanded="false" 
                     data-projectPhaseName="<?php echo $phase->projectPhaseName; ?>"  
                     data-projectPhaseID="<?php echo $phase->projectPhaseID; ?>"  
                     aria-controls="manageProjectPhaseTask">
                     <i class="ti ti-plus" 
                        data-bs-toggle="tooltip" 
                        data-bs-custom-class="tooltip-primary"
                        data-bs-placement="top" 
                        title=" Add  New project Phase Task">
                     </i>                     
                  </button>

                  <button type="button" class="btn btn-icon rounded-pill btn-secondary-light btn-sm newPhase"  
                     data-bs-toggle="modal" 
                     href="#managePhase" 
                     role="button" 
                     aria-expanded="false" 
                     data-projectPhaseName="<?php echo $phase->projectPhaseName; ?>"  
                     data-projectPhaseID="<?php echo $phase->projectPhaseID; ?>"  
                     aria-controls="managePhase">
                     <i class="ti ti-plus" 
                        data-bs-toggle="tooltip" 
                        data-bs-custom-class="tooltip-primary"
                        data-bs-placement="top" 
                        title=" Add  New project Phase"
                        >
                     </i>                
                  </button>

                  <button 
                     type="button" 
                     class="btn  btn-icon rounded-pill  btn-secondary-light btn-sm editPhase" 
                     data-bs-toggle="modal" 
                     data-bs-target="#managePhase" 
                     data-project-phase-id="<?php echo $phase->projectPhaseID; ?>" 
                     data-project-id="<?php echo $projectID; ?>"
                     data-project-phase-name="<?php echo $phase->projectPhaseName; ?>"
                     data-phase-work-Hrs="<?php echo $phase->phaseWorkHrs; ?>"
                     data-phase-weighting="<?php echo $phase->phaseWeighting; ?>"
                     data-phase-start-date="<?php echo $phase->phaseStartDate; ?>"
                     data-phase-end-date="<?php echo $phase->phaseEndDate; ?>"
                     data-billing-milestone="<?php echo $phase->billingMilestone; ?>"
                  >
                     <i class="uil-edit" ></i>
                  </button>

                  <button 
                     type="button" 
                     class="btn  btn-icon rounded-pill  btn-danger-light" 
                     data-bs-toggle="modal" 
                     data-bs-target="#delete_project_phase" 
                     data-projectPhaseID="<?php echo $phase->projectPhaseID; ?>" 
                     data-projectID="<?php echo $projectID; ?>"
                  >
                     <i class="uil-trash-alt"></i>
                  </button>
               </div>
            </div>
            <div class="card-body">
               <div class="col-12">
                  <div class="row bg-light py-2 px-3">
                     <div class="col-5">
                        <span class="font-16 t600">Phase Tasks</span>
                     </div>
                     <div class="col">
                        <span> Done/allocated </span>
                     </div>
                     <div class="col">
                        <span> Task Period</span>
                     </div>
                     <div class="col">
                        <span> finish</span>
                     </div>
                     <div class="col">
                        <span> Assigned to</span>
                     </div>
                
                     <div class="col">
                        <span> Action</span>
                     </div>
                  </div>
                  <?php
                  if($phaseTasks) {
                     foreach ($phaseTasks as $taskKey => $task) {
                        // var_dump($task);
                        $subTasks = Projects::project_subtasks_full(['projectTaskID'=>$task->projectTaskID], false, $DBConn);                        
                        $subtaskCount = $subTasks ? count($subTasks) : ""; ?>
                     <div class="row py-1 px-3 border-bottom border-secondary align-items-center">
                        <div class="col-5">
                           <span class="font-14 t300"><?= $task->projectTaskName ?></span>
                        </div>
                        <div class="col">
                           <span> <?= $task->hoursAllocated ? $task->hoursAllocated : "-:-"  ?></span>
                        </div>
                        <div class="col">
                           <span> <?= Utility::date_format($task->taskStart, 'miniNoYear') ." - ".  Utility::date_format($task->taskDeadline, 'mini')  ?></span>
                        </div>
                        <div class="col">
                           <span> <?= Utility::date_format($task->taskDeadline, 'short') ?></span>
                        </div>
                        <div class="col">
                           <?php
                           $assignments= Projects::task_user_assignment( ['projectTaskID'=> $task->projectTaskID], false,$DBConn);
                           $teamUsers = array();																										
                           if ($assignments) {                                                      
                              for ($i=0; $i <count($assignments) ; $i++) { 
                                 $value= $assignments[$i];	
                                 // var_dump($value);	
                                 $teamUsers[] = (object)[ 'userName' =>$value->taskUser, 'ID' => $value->userID, 'projectTaskID' => $value->projectTaskID, 'taskUser' => $value->taskUser, 'jobTitle'=> $value->jobTitle ];														
                                 $allTaskTeam[]=(object)[ 'userName' =>$value->taskUser, 'ID' => $value->userID, 'projectTaskID' => $value->projectTaskID, 'taskUser' => $value->taskUser, 'jobTitle'=> $value->jobTitle ];
                                 if ($value->taskUser) {
                                    $FnameArray= str_split(explode(" ", $value->taskUser)[0]);
                                          $sNameArr= str_split(explode(" ", $value->taskUser)[1]);
                                 }?>
                                 <a href="#" class="py-0 " data-bs-toggle="tooltip" data-bs-html="true" title="<em><u><?php echo $value->taskUser ?></u></em> ">
                                    <span class="border border-warning rounded-circle p-1 font-12 mr-3 text-uppercase"><?php echo isset($value->taskUser) && !empty($value->taskUser)  ? Utility::generate_initials($value->taskUser) :''  ?></span>
                                 </a>
                                 <?php
                              }														
                           }?>
                            <script>
                              document.addEventListener('DOMContentLoaded', function() {
                                 const teamUsersJSON = JSON.stringify(<?php echo json_encode($teamUsers); ?>);
                                 console.log(teamUsersJSON);
                                 document.querySelector('.newTaskStep').dataset.teamUsers = teamUsersJSON;
                              });
                           </script>
                           <span class="float-end ms-3">
                              <a href="" data-project-task-id="<?php echo $task->projectTaskID ?>"  class="editAssignee">	
                                 <i class="fa-solid fa-user-edit"></i>
                              </a>															
                           </span>
                        </div>
                        <!-- <div class="col">
                           <span> <?=  $subtaskCount; ?></span>
                        </div> -->
                        <div class="col">
                           <span class="float-end">
                              <a href="#" class="newTaskStep" 
                                 data-bs-toggle="modal" 
                                 data-bs-target="#add_task_step" 
                                 data-project-task-id="<?php echo $task->projectTaskID; ?>"
                                 data-project-task-name="<?php echo $task->projectTaskName; ?>"
                                 data-project-task-duration="<?php echo $task->hoursAllocated; ?>"
                                 data-project-task-deadline="<?php echo $task->taskDeadline; ?>"
                              >
                                 <i class="fa-solid fa-plus"></i>
                              </a>
                           </span>
                           
                        </div>
                     </div>

                     <?php
                        
                     }
                  } else {
                     Alert::info("No Tasks set up for this project phase", true, array('fst-italic', 'text-center', 'font-18', 'my-3'));
                  }
                  ?>
                  <div class="col-12 my-3">
                     <button type="button" class="btn btn-primary rounded-pill newTask float-end" 
                        data-bs-toggle="modal" 
                        data-bs-target="#manageProjectPhaseTask" 
                        data-project-phase-id="<?php echo $phase->projectPhaseID; ?>"
                        data-project-id="<?php echo $projectID; ?>"
                        data-client-id ="<?php echo $phase->clientID; ?>"
                     >
                        Add phase Task to <?php echo $phase->projectPhaseName; ?>
                     </button>
                  </div>
                   
               <?php //var_dump($phaseTasks);   ?>
            </div>
         </div>
         
         <?php
         }
      } else {
         Alert::info("No Phases or tasks et up for this project ", true, array('fst-italic', 'text-center', 'font-18'));?>
         
         <?php         
      }?>
   </div>
   <div class="col-12 text-center">
      <a class="btn btn-primary rounded-pill" data-bs-toggle="modal" href="#managePhase">
         Add New Phase
      </a>
   </div>
      <?php      
      echo Utility::form_modal_header("managePhase", "projects/manage_project_phase.php", "Manage Project Phase", array('modal-xl', 'modal-dialog-centered'), $base);
      include "includes/scripts/projects/modals/manage_project_phase.php";
      echo Utility::form_modal_footer("Save Phase", "managePhase", 'btn btn-primary btn-sm'); 

      echo Utility::form_modal_header("manageProjectPhaseTask", "projects/manage_project_task_planner.php", "Manage Project Task", array('modal-lg', 'modal-dialog-centered'), $base);
      include "includes/scripts/projects/modals/manage_project_task.php";
      echo Utility::form_modal_footer("Save Phase", "manageProjectPhaseTaskSave", 'btn btn-primary btn-sm'); 

    

      echo Utility::form_modal_header("add_task_step", "projects/manage_sub_task.php", "Add Task Step ", array("modal-lg", "modal-dialog-centered"), $base);
      include 'includes/scripts/projects/modals/manage_task_step.php';
      echo Utility::form_modal_footer("Add SubTask", "addSubtask", "btn btn-primary submit");	
      ?>

   <script>
      document.addEventListener('DOMContentLoaded', function () {
         document.querySelectorAll('.newTask').forEach(newTaskLink => {
            newTaskLink.addEventListener('click', function(e) {
               e.preventDefault();
               const data = this.dataset;
               console.log(data);
               const manageTaskForm = document.getElementById('projectPlannerTaskForm');
               if (manageTaskForm) {
                  manageTaskForm.querySelector('input[name="projectPhaseID"]').value = data.projectPhaseId;
                  manageTaskForm.querySelector('input[name="projectID"]').value = data.projectId;
                  manageTaskForm.querySelector('input[name="clientID"]').value = data.clientId;
                  // manageTaskForm.querySelector('input[name="projectPhaseName"]').value = data.projectPhaseName;
               }
            });
         });

         document.querySelectorAll('.newTaskStep').forEach(taskStepLink => {
            taskStepLink.addEventListener('click', function(e) {
               e.preventDefault();
               const data= this.dataset;
               console.log(data);
               const taskDuration = document.querySelector('.taskDuration');
               console.log(taskDuration);
               taskDuration.innerHTML = `( ${data.projectTaskDuration} )`;
               taskDuration.classList.remove("d-none");
               document.querySelector('.subTaskDueDate').addEventListener('change', (e)=>{
                  const subtaskDueDateValue = e.target.value;
                  console.log(`Subtask Due Date: ${subtaskDueDateValue}`);
                  if (new Date(subtaskDueDateValue) > new Date(data.projectTaskDeadline)) {
                     alert("Subtask due date cannot be after the project task deadline.");
                     document.querySelector('.dateerror').innerHTML=`Subtask due date cannot be after the project task deadline. Subtask deadline has been reset to the TaskDeadline `;
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

         document.querySelectorAll('.editPhase').forEach(editPhaseLink => {
            editPhaseLink.addEventListener('click', function(e) {
               e.preventDefault();
               const data = this.dataset;
               console.log(data);
               const managePhaseForm = document.getElementById('managePhaseForm');
               if (!managePhaseForm) return;

               console.log(managePhaseForm);
               const fieldMapping = {
                  'projectPhaseID': 'projectPhaseId',
                  'projectID': 'projectId',
                  'projectPhaseName': 'projectPhaseName',
                  'phaseWorkHrs': 'phaseWorkHrs',
                  'phaseWeighting': 'phaseWeighting',
                  'phaseStartDate': 'phaseStartDate',
                  'phaseEndDate': 'phaseEndDate',
                  'billingMilestone': 'billingMilestone'
               }
               // fill regular fields
               for (const [field, dataKey] of Object.entries(fieldMapping)) {
                  const input = managePhaseForm.querySelector(`input[name="${field}"]`);
                  if (input) {
                     if(field ==='phaseWorkHrs') {
                        input.value = data[dataKey] || '';
                        console.log(dataKey, data[dataKey], input.value); // Debugging line
                        console.log(`Setting phaseWorkHrs to: ${data[dataKey]}`); // Debugging line                      
                     } 
                     if( input.type === 'checkbox') {
                        console.log(input, data[dataKey], dataKey);
                        input.checked = data[dataKey] === 'Y' || data[dataKey] === 'Yes' || data[dataKey] === true || data[dataKey] === 'true';
                     } else if (input.type === 'radio') {
                        managePhaseForm.querySelector(`input[name="${field}"][value="${data[dataKey]}"]`).checked = true;
                     } else {
                        input.value = data[dataKey] || '';
                     }
                  }
               }
            });
         });
      });

   </script>