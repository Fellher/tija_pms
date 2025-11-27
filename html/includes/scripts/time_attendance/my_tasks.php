<?php

$userTasks = Projects::task_user_assignment(["userID"=> $userDetails->ID], false, $DBConn);
// var_dump($userTasks);
$userSubtasks = Projects::project_subtasks(array('assignee'=>$userDetails->ID, 'Suspended'=>'N'), false, $DBConn);
// var_dump($userSubtasks);
if ($userSubtasks) {
   foreach ($userSubtasks as $key => $subtask) {
      $subtaskFilter = array('projectTaskID'=>$subtask->projectTaskID, 'assignee'=>$userDetails->ID);
      $taskExists = false;
      foreach ($userTasks as $index => &$task) {
         if ($task->projectTaskID == $subtaskFilter['projectTaskID']) {
            $taskExists = true;
            if (!isset($task->subtasks)) {
               $task->subtasks = [];
            }
            $task->subtasks[] = $subtask;
            break;
         }
      }
      if (!$taskExists) {
         // Handle the case where the task is not found in $userTasks
         $projectTask = Projects::project_tasks_full(array('projectTaskID'=>$subtaskFilter['projectTaskID']), true, $DBConn);
         if ($projectTask) {
            $projectTask->subtasks = [$subtask];
            $userTasks[] = $projectTask;
         }

      }
      # code...
   }
}
// var_dump($userTasks);
?>
<div class="container-fluid">
   <div class="card card-body mt-4 headerSummary" >
      <div class="row">
         <div class="col-md">
            <h4 class="mb-0">My Project Tasks</h4>
         </div>
      </div>
      <?php
      $completedDayTasks = array();
      $completedDaySubtasksTasks = array();

      if($userTasks) {
         foreach ($userTasks as $task) {
            // var_dump($task);
            if($task->taskStatusID == 1 || $task->taskStatusID == 5 || $task->taskStatusID == 4 || $task->taskDeadline <= date('Y-m-d')){
               $bgColor = " alert-danger  ";
            } else {
               $bgColor = "alert-primary";
            }
            // echo "<h5> {$bgColor} </h5>";
            $subtaskFilter = array('projectTaskID'=>$task->projectTaskID, 'assignee'=>$userDetails->ID);
            // var_dump($subtaskFilter);
            $subtasks =Projects::project_subtasks_full($subtaskFilter, false, $DBConn);
            // var_dump($subtasks);
            if($task->taskStatusID != 6){
               // var_dump($task)?>
                  <div class="alert <?= $bgColor ?>   fade show custom-alert-icon shadow-sm my-2 " role="alert">
                     <div class="alert-text row">
                        <div class="col-md-4">
                           <h4 class="mb-0 fs-16"><?php echo $task->projectTaskName; ?></h4>
                           <p class="mb-0 fs-16">Project: <?php echo $task->projectName; ?></p>
                           <p class="mb-0 ">
                              <span class="me-2 text-dark "> <?= Utility::date_format($task->taskStart, 'midDate') ?> to <?= Utility::date_format($task->taskDeadline, 'midDate') ?>   </span>
                              |
                              <span class="mx-2 fst-italic text-primary"> <?php echo isset($task->taskUser) && !empty($task->taskUser) ? $task->taskUser : ""; ?> </span>
                           </p>
                        </div>
                        <div class="col-md-4">
                           <p class="mb-0 text-primary fs-16"><?php echo $task->clientName; ?></p>
                           <p class="mb-0"> <?php echo $task->taskDescription; ?></p>
                        </div>
                        <div class="col-md text-end ">

                           <span class="badge text-white <?= ($task->taskStatusID == 1 || $task->taskStatusID == 5 ||  $task->taskStatusID == 7) ? 'bg-warning' : 'bg-success'; //(($task->taskStatusID == 2 ||  $task->taskStatusID == 3) ? 'bg-info' : 'bg-success') ?> text-dark">
                              <?= $task->taskStatusName ? $task->taskStatusName : "Active" ?>
                           </span>
                           <?php
                           if(isset($task->taskUser) && !empty($task->taskUser)) {?>

                              <button
                                 class="btn btn-primary btn-sm submit_work_hour"
                                 data-bs-toggle="modal"
                                 data-bs-target="#addWorkHours"
                                 title="Add Work Hour"
                                 data-project-task-id="<?php echo $task->projectTaskID; ?>"
                                 data-project-task-name = "<?php echo $task->projectTaskName; ?>"
                                 data-project-id="<?php echo $task->projectID; ?>"
                                 data-project-name ="<?php echo $task->projectName; ?>"
                                 data-project-phase-id="<?php echo $task->projectPhaseID; ?>"
                                 data-project-phase-name ="<?php echo $task->projectPhaseName; ?>"
                                 data-task-status-id="<?php echo $task->taskStatusID ? $task->taskStatusID  : '2' ?>"
                                 data-client-id ="<?php echo $task->clientID; ?>"
                                 data-client-name="<?php echo $task->clientName; ?>"
                                 data-employee-id="<?php echo $userDetails->ID; ?>"
                              >
                              <i class="fa-solid fa-clock"  title="Add Work Hour"></i>
                              Add Work Hours
                           </button>
                           <?php
                           } else { ?>
                              <button class="btn btn-primary btn-sm" disabled>
                                 <i class="fa-solid fa-clock"  title="Add Work Hour"></i>
                                 Add Work Hours
                              </button>
                           <?php } ?>
                        </div>
                     </div>
                     <?php
                     if ($subtasks) {
                        $allDocuments = array();?>
                        <div class="col-md-11 ms-4">
                           <ul class="list-group list-group-flush bg-light border-0 rounded-3 p-3">
                              <?php
                              foreach ($subtasks as $subtask) {
                                 // var_dump($subtask);
                                 // var_dump($subtask->taskStatusID);
                                 // check if subtask timelog exists for the day and has been marked complete
                                 $subtaskTimelogs = TimeAttendance::project_tasks_time_logs(array('subtaskID'=>$subtask->subtaskID, 'taskDate'=>$DOF, 'employeeID'=>$userDetails->ID,  'Suspended'=>'N'), false, $DBConn);
                                 $dailycompleted = false;
                                 if ($subtaskTimelogs) {
                                    foreach ($subtaskTimelogs as $key => $timelog) {
                                       if ($timelog->subtaskID == $subtask->subtaskID && $timelog->taskDate == $DOF && $timelog->dailyComplete === 'Y') {
                                          $dailycompleted = true;
                                          $completedDaySubtasksTasks[] = $subtask;
                                          // echo "Subtask ID: {$subtask->subtaskID} - Daily Completed: {$timelog->dailyComplete} <br>";
                                       }
                                    }
                                 } else {
                                    $dailycompleted = false;
                                 }
                                 if(!$dailycompleted){
                                    if($subtask->subTaskStatus !== 'completed'){
                                       // check if subtask has been logged for the day
                                       $logFilter = array('subtaskID'=>$subtask->subtaskID, 'taskDate'=>$DOF, 'employeeID'=>$userDetails->ID,  'Suspended'=>'N');
                                       // var_dump($logFilter);
                                       $subTaskTimelogs = TimeAttendance::daily_task_status_change_log($logFilter, false, $DBConn);
                                       // $subTaskStatuses = TimeAttendance::project_tasks_time_logs(
                                       //    array('subtaskID'=>$subtask->subtaskID,
                                       //    'taskDate'=>$DOF, 'userID'=>$userDetails->ID,
                                       //    'Suspended'=>'N'),
                                       //    false,
                                       //    $DBConn);
                                       // var_dump($subTaskTimelogs);
                                       $latestTimelog = null;
                                       if($subTaskTimelogs) {
                                          $latestTimelog = array_reduce($subTaskTimelogs, function($carry, $item) {
                                             return $carry ? $carry->changeDateTime > $item->changeDateTime ? $carry : $item : $item;
                                          }, null);
                                          // if ($latestTimelog) {
                                          //    echo "Latest Timelog: " . $latestTimelog->changeDateTime;
                                          // } else {
                                          //    echo "No latest timelog found.";
                                          // }
                                       } else {
                                          $dailycompleted = false;
                                       }

                                       // var_dump($latestTimelog);


                                       $taskStatusLogs = TimeAttendance::daily_task_status_change_log(['subtaskID'=>$subtask->subtaskID, 'Suspended'=>'N'], false, $DBConn);

                                       // var_dump($taskStatusLogs);


                                       ?>
                                       <li class="list-group-item list-group-item-action d-flex justify-content-between align-items-start shadow-md pt-0 pb-1 nobg ">
                                          <div class=" col-md-4  row">
                                             <div class="col-md-2">
                                                <span class="avatar bd-blue-800 avatar-xs me-2 avatar-rounded">
                                                   <i class="fa-solid fa-clock fs-18"></i>
                                                </span>
                                             </div>
                                             <?php echo $subtask->subTaskName; ?> <br />
                                             <?php echo $subtask->subtaskDueDate; ?>
                                          </div>
                                          <div class="col-md-4"><?php echo $subtask->subTaskDescription; ?></div>
                                          <?php
                                          // var_dump($taskStatusList);


                                          // var_dump($taskStatusList);
                                             // $taskStatus = $taskStatusList[$subtask->taskStatusID];
                                             // var_dump($taskStatus);
                                             if($latestTimelog && $latestTimelog->taskStatusID) {
                                                $taskStatus = Projects::task_status(array('taskStatusID'=>$latestTimelog->taskStatusID), true, $DBConn);
                                             } else {
                                                $taskStatus = null;
                                             }
                                             // var_dump($taskStatus);

                                             ?>
                                             <form id="<?= "taskStatusForm{$subtask->subtaskID}" ?>" action="<?= "{$base}php/scripts/time_attendance/update_task_status.php" ?>" method="post" class="col-md">
                                                <input type="hidden" name="subtaskID" value="<?php echo $subtask->subtaskID; ?>">
                                                <input type="hidden" name="projectTaskID" value="<?php echo $subtask->projectTaskID; ?>">
                                                <input type="hidden" name="projectPhaseID" value="<?php echo $subtask->projectPhaseID; ?>">
                                                <input type="hidden" name="projectID" value="<?php echo $subtask->projectID; ?>">
                                                <input type="hidden" name="employeeID" value="<?php echo $userDetails->ID; ?>">
                                                <div class="col-12" >
                                                   <select class="form-select form-select-sm form-control-sm subTaskStatus" id="taskStatusSelect" name="taskStatusID" aria-label="Default select example">
                                                      <?php foreach ($taskStatusList as $status) { ?>
                                                         <option value="<?php echo $status->taskStatusID; ?>"
                                                            <?php if($latestTimelog && $latestTimelog->taskStatusID == $status->taskStatusID) echo 'selected'; ?>
                                                            >
                                                            <?php echo $status->taskStatusName; ?>
                                                         </option>
                                                      <?php } ?>
                                                   </select>
                                                </div>
                                             </form>

                                             <?php

                                             $activeSubmit = '';
                                             // Check if the subtask needs documents and if task status logs for the subtask with taskStatusID of 6 (Document received) exist
                                             $taskStatusLogs = TimeAttendance::daily_task_status_change_log(['subtaskID'=>$subtask->subtaskID, 'taskStatusID'=>6, 'Suspended'=>'N'], false, $DBConn);
                                             $allDocumentsReceived = !empty($taskStatusLogs);
                                             if($subtask->needsDocuments == 'Y' && $allDocumentsReceived) {
                                                $allDocuments[] = $subtask->subTaskName;
                                                $activeSubmit = true;
                                             } elseif($subtask->needsDocuments == 'Y' && !$allDocumentsReceived) {
                                                $allDocuments[] = $subtask->subTaskName;
                                                $activeSubmit = false;
                                             } elseif($subtask->needsDocuments == 'N') {
                                                $allDocuments[] = null;
                                                $activeSubmit = true;
                                             }
                                             // echo "<span class='text-success'>All documents received for {$subtask->subTaskName} and activesubmit is {$activeSubmit} </span>";
                                           if($activeSubmit) {?>



                                             <div class="col-md-1 text-end">
                                                   <button
                                                   class="btn  btn-icon rounded-pill btn-primary-light submit_work_hour"
                                                   data-bs-toggle="modal"
                                                   data-bs-target="#addWorkHours"
                                                   title="Add Work Hour"
                                                   data-subtaskID="<?php echo $subtask->subtaskID; ?>"
                                                   data-project-task-id="<?php echo $subtask->projectTaskID; ?>"
                                                   data-project-id="<?php echo $subtask->projectID; ?>"
                                                   data-project-phase-id="<?php echo $subtask->projectPhaseID; ?>"
                                                   data-project-phase-name ="<?php echo $subtask->projectPhaseName; ?>"
                                                   data-client-id ="<?php echo $subtask->clientID; ?>"
                                                   data-task-status-id="<?php echo $task->taskStatusID; ?>"
                                                   data-client-name="<?php echo $task->clientName; ?>"
                                                   data-employee-id="<?php echo $userDetails->ID; ?>"
                                                   data-subtask-name="<?php echo $subtask->subTaskName; ?>"
                                                   data-project-task-name ="<?php echo $task->projectTaskName; ?>"
                                                   data-subtask-name ="<?php echo $subtask->subTaskName; ?>"
                                                   >
                                                      <i class="fa-solid fa-clock"  title="Add Work Hour"></i>
                                                </button>
                                               <!-- <button class="btn  btn-icon rounded-pill btn-secondary-light" data-bs-toggle="modal" data-bs-target="#delegateTask">
                                                   <i class="fa-solid fa-user-plus"></i>
                                                </button>  -->
                                                <button
                                                class="btn  btn-icon rounded-pill btn-warning-light completetask"
                                                data-bs-toggle="modal"
                                                data-bs-target="#completeTask"
                                                data-subtask-id="<?php echo $subtask->subtaskID; ?>"
                                                title="Complete Task"
                                                data-todaysdate = "<?php echo $dt->format('Y-m-d'); ?>">
                                                   <i class="fa-solid fa-check"></i>
                                                </button>

                                                <script>
                                                   // Create the modal form
                                                   var modal = document.createElement('div');
                                                   // let todaysdate = <?php echo json_encode($dt->format('Y-m-d')); ?>;
                                                   // console.log(todaysdate);
                                                   modal.className = 'modal fade';
                                                   modal.id = 'completeTaskModal';
                                                   modal.innerHTML = `
                                                      <div class="modal-dialog modal-dialog-centered">
                                                         <div class="modal-content">
                                                            <div class="modal-header">
                                                               <h5 class="modal-title">Confirm Complete Task</h5>
                                                               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                               <form id="completeTaskForm" action="../php/scripts/time_attendance/complete_task.php" method="post">
                                                                  <input type="hidden" name="subtaskID" id="subtaskIDComplete" value="">
                                                                  <input type="hidden" name="todaysdate" id="todaysdate" value="<?php echo $dt->format('Y-m-d'); ?>">
                                                                  <p>Are you sure you want to complete this task?</p>
                                                                  <button type="submit" class="btn btn-primary">Complete</button>
                                                                  <button type="button" class="btn btn-secondary cancelConfirm" data-bs-dismiss="modal">Cancel</button>
                                                               </form>
                                                            </div>
                                                         </div>
                                                      </div>
                                                   `;
                                                   document.body.appendChild(modal);

                                                   // Add event listener to the complete task button
                                                   document.querySelectorAll('.completetask').forEach(button => {
                                                      button.addEventListener('click', function() {
                                                         let data = this.dataset;
                                                         // Get the subtask ID from the data attribute
                                                         // var subtaskID = this.getAttribute('data-subtask-id');
                                                         console.log(data);

                                                         var subtaskID = this.dataset.subtaskId;
                                                         console.log(subtaskID);
                                                         let subtskinput = document.getElementById('subtaskIDComplete');
                                                         console.log(subtskinput);
                                                         subtskinput.value = subtaskID;
                                                         // Set the value of the hidden input field in the modal form
                                                         // var subtaskIDInput = modal.querySelector('#subtaskID');
                                                         // subtaskIDInput.value = subtaskID;
                                                         // Show the modal
                                                         var modal = document.getElementById('completeTaskModal');
                                                         var modalInstance = new bootstrap.Modal(modal);
                                                         modalInstance.show();
                                                      });
                                                   });
                                                </script>
                                             </div>
                                          <?php
                                          }
                                             ?>

                                       </li>
                                       <?php
                                    }
                                 }
                              } ?>
                           </ul>
                        </div>
                        <?php
                     }
                     ?>
                     <div class="col-12">
                        <?php

                        /*  var_dump($task);
                        if($task->projectID == 4){

                           if( $allDocumentsreceived) { ?>
                              <button
                                 class="btn btn-primary btn-sm submit_work_hour float-end"
                                 data-bs-toggle="modal"
                                 data-bs-target="#addWorkHours"
                                 title="Add Work Hour"
                                 data-projectTaskID="<?php echo $task->projectTaskID; ?>"
                                 data-projectID="<?php echo $task->projectID; ?>"
                                 data-projectPhaseID="<?php echo $task->projectPhaseID; ?>"
                                 data-taskStatusID="6"
                                 data-workTypeID = "1">
                                 <i class="fa-solid fa-clock"  title="Add Work Hour"></i>
                                 Complete Task (<?php echo $task->projectTaskName; ?>)
                              </button>
                           <?php
                           } else {
                              Alert::danger("<i class='icon-business-time font-26 p-3 mx-5'></i>All documents have not been received for {$task->projectTaskName}");
                           }
                        } */?>
                        <!-- <button
                                 class="btn btn-primary btn-sm submit_work_hour float-end"
                                 data-bs-toggle="modal"
                                 data-bs-target="#addWorkHours"
                                 title="Add Work Hour"
                                 data-projectTaskID="<?php echo $task->projectTaskID; ?>"
                                 data-projectID="<?php echo $task->projectID; ?>"
                                 data-projectPhaseID="<?php echo $task->projectPhaseID; ?>"
                                 data-taskStatusID="6"
                                 data-workTypeID = "1">
                                 <i class="fa-solid fa-clock"  title="Add Work Hour"></i>
                                 Complete Task (<?php echo $task->projectTaskName; ?>)
                              </button> -->

                     </div>
                  </div>
            <?php
            }

         }
      }
 ?>
      <script>
         document.addEventListener("DOMContentLoaded", function() {
            // Add event listener to the submit work hour buttons
            document.querySelectorAll(".submit_work_hour").forEach(button => {
               button.addEventListener("click", function() {

                  // GET THE form
                   var form = document.querySelector('.workHourClean');
                   console.log(form);
                  // Get all data attributes from the button
                   const buttonData = this.dataset;
                   console.log(buttonData);

                    // Map form fields to their corresponding data attributes
                  const fieldMappings = {
                     'clientID': 'clientId',
                     'projectTaskID': 'projectTaskId',
                     'projectID': 'projectId',
                     'projectPhaseID': 'projectPhaseId',
                     // 'taskStatusID': 'taskStatusId',
                     'employeeID': 'employeeId'
                  }
                  const input = document.createElement('div');
                  input.className = 'form-group';

                  // Set the values in the form based on the button's data attributes
                  for (const [key, value] of Object.entries(fieldMappings)) {
                     // create text input with name as the key and value as the buttonData value
                     console.log(`Setting ${key} to ${buttonData[value]}`);
                     // create input

                      input.innerHTML +=`<input type="hidden" class="form-control" name="${key}" value="${buttonData[value]}">`;

                  }

                  console.log(input);
                  // Append the input to the form
                  form.appendChild(input);

                  const fieldMappingsNames ={
                     'clientName': 'clientName',
                     'projectName': 'projectName',
                     'projectPhaseName': 'projectPhaseName',
                     'projectTaskName': 'projectTaskName',
                     'subtaskName': 'subtaskName',
                  }
                  let selectedBtn = form.querySelector('.selection');
                  let selectedNameString= '';
                  // set values for selected projectName, projectPhaseName, projectTaskName
                  for (const [key, value] of Object.entries(fieldMappingsNames)) {
                     // create text input with name as the key and value as the buttonData value
                     console.log(`Setting ${key} to ${buttonData[value]}`);
                     // create input
                     selectedNameString += buttonData[value] ? ` : ${buttonData[value]}  ` : '';
                  }

                  console.log(selectedNameString);
                  selectedBtn.innerHTML = selectedNameString;
                  selectedBtn.classList.remove('dropdown-toggle');
                  selectedBtn.removeAttribute('data-bs-toggle');
                  selectedBtn.removeAttribute('data-bs-target');

               });
            });

            document.querySelectorAll(".subTaskStatus").forEach(button => {
               button.addEventListener("change", function() {

                  console.log(`item Selected: ${this.value}`);
                  // get the selected value and name
                  var selectedOption = this.options[this.selectedIndex];
                  var selectedValue = selectedOption.value;
                  var selectedName = selectedOption.text;
                  var selectedValue = this.value;

                  console.log(selectedName);

                  var modal = document.createElement('div');
                  modal.className = 'modal fade';
                  modal.id = 'taskStatusModal';
                  let modalHtml = ``;
                  // Create the modal form
                  modalHtml += `
                     <div class="modal-dialog modal-dialog-centered ${selectedValue == 6 ? 'modal-lg' : ' modal-md'}">
                        <div class="modal-content">
                           <div class="modal-header">
                              <h5 class="modal-title">Confirm Task Status Change</h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                           </div>
                           <div class="modal-body">
                              <form id="taskStatusForm" action="../php/scripts/time_attendance/update_task_status.php" method="post" enctype="multipart/form-data">
                                 <input type="hidden" name="subtaskID" id="subtaskID" value="">
                                 <input type="hidden" name="projectTaskID" id="projectTaskID" value="">
                                 <input type="hidden" name="projectPhaseID" id="projectPhaseID" value="">
                                 <input type="hidden" name="projectID" id="projectID" value="">
                                 <input type="hidden" name="employeeID" id="employeeID" value="">
                                 <input type="hidden" name="s" id="s" value="<?php echo $s; ?>">
                                 <input type="hidden" name="taskStatusID" id="taskStatusID" value="${selectedValue}">
                                 <input type="hidden" name="taskStatusName" id="taskStatusName" value="${selectedName}">
                                 <input type="hidden" name="changeDateTime" id="changeDateTime" value="<?php echo date('Y-m-d H:i:s'); ?>">
                                 <input type="hidden" name="taskDate" id="taskDate" value="<?php echo $dt->format('Y-m-d'); ?>">
                                 <p>Are you sure you want to change the task status to <span class="fw-bold fs-16">${selectedName}</span> ?</p>  `;
                                 // check if selected value is completed ie =6
                                 if (selectedValue == 6) {
                                    modalHtml += `

                                          <div class="row">
                                             <div class="form-group col-md-6">
                                                <label for="workType" class="nott mb-0 t500 text-dark "> Work Type</label>
                                                <select name="workTypeID" id="workTypeID" class="form-control form-control-sm form-control-plaintext bg-light-blue">
                                                      <?php echo Form:: populate_select_element_from_object($workType, 'workTypeID', 'workTypeName',  '', '', $blankText='Select:') ?>
                                                   <option value="addNew"> New Work Type</option>
                                                </select>
                                             </div>

                                             <div class=" form-group col-md-6 ">
                                                <label for="form1" class="col-md-12 nott mb-0 t500 text-dark  ">Task Time Duration </label>
                                                <div class="row mt-1">
                                                   <div class="col-md pl-0">
                                                      <input type="text" id="hours"  name="hours" step="1" min="0" max="24" class="form-control  form-control-sm form-control-plaintext bg-light-blue center" placeholder="hours">
                                                   </div>	:
                                                   <div class="col-md  ">
                                                      <input type="number" id="minutes" name='minutes' step="1" min="0" max="60"  class="form-control form-control-sm form-control-plaintext bg-light-blue center" placeholder="minutes">
                                                   </div>
                                                </div>
                                             </div>
                                             <div class="col-lg-12 bottommargin">
                                                <label class="col-md-12 nott mb-0 t500 text-dark  ">Attarch Supporting Files:</label><br>
                                                <input  id="formFileMultiple" type="file" class="form-control form-control-sm" name="fileAttachments[]" multiple data-show-preview="false">
                                             </div>
                                          </div>
                                          `;
                                 }

                  modalHtml += `
                                 <div class="form-group my-2">
                                    <label for="taskNotes">Please input Task Status ${selectedName} Notes</label>
                                    <textarea class="form-control borderless_mini bg-light-blue" id="taskNotes" name="taskChangeNotes" rows="3" placeholder="Add status change notes"></textarea>
                                 </div>
                                 <div class="col-12 text-end">

                                    <button type="button" class="btn btn-secondary cancelStatusChange" data-bs-dismiss="modal">Cancel</button>
                                     <button type="submit" class="btn btn-primary">Submit</button>
                                 </div>
                              </form>
                           </div>
                        </div>
                     </div>
                  `;

                  console.log(modalHtml);
                  modal.innerHTML = modalHtml;
                  // Append the modal to the body
                  document.body.appendChild(modal);
                  // Get the selected value
                  // var selectedValue = this.value;
                  // Get the subtask ID from the data attribute
                  var subtaskID = this.closest("form").querySelector("input[name='subtaskID']").value;
                  var projectTaskID = this.closest("form").querySelector("input[name='projectTaskID']").value;
                  var projectPhaseID = this.closest("form").querySelector("input[name='projectPhaseID']").value;
                  var projectID = this.closest("form").querySelector("input[name='projectID']").value;
                  var employeeID = this.closest("form").querySelector("input[name='employeeID']").value;


                  // Set the value of the hidden input field in the modal form
                  var subtaskIDInput = modal.querySelector('#subtaskID');
                  subtaskIDInput.value = subtaskID;
                  var projectTaskIDInput = modal.querySelector('#projectTaskID');
                  projectTaskIDInput.value = projectTaskID;
                  var projectPhaseIDInput = modal.querySelector('#projectPhaseID');
                  projectPhaseIDInput.value = projectPhaseID;
                  var projectIDInput = modal.querySelector('#projectID');
                  projectIDInput.value = projectID;
                  var employeeIDInput = modal.querySelector('#employeeID');
                  employeeIDInput.value = employeeID;

                  // Show the modal
                  var modalInstance = new bootstrap.Modal(modal);
                  modalInstance.show();
                  // Add event listener to the form submit button
                  var form = modal.querySelector("#taskStatusForm");

                  let cancelStatusChange = modal.querySelector(".cancelStatusChange");
                  cancelStatusChange.addEventListener("click", function() {
                     // Hide the modal
                     modalInstance.hide();
                     // Remove the modal from the DOM
                     document.body.removeChild(modal);
                     // reset page
                     location.reload();
                  });
               });
            });
         });
      </script>
   </div>
   <?php
   if($completedDaySubtasksTasks || $completedDayTasks) {?>

      <div class="card custom-card">
         <div class="card-body">
            <div class="row">
               <div class="col-md-4">
                  <h4 class="mb-0">Completed Tasks</h4>
               </div>

            </div>
            <?php
            if($completedDaySubtasksTasks) {
               foreach ($completedDaySubtasksTasks as $task) {?>
                  <div class="alert alert-success fade show custom-alert-icon shadow-sm my-2 " role="alert">
                     <div class="alert-text row">
                        <div class="col-md-4">
                           <h4 class="mb-0 fs-16"><?php echo $task->subTaskName; ?></h4>
                        </div>
                        <div class="col-md-4">
                           <p class="mb-0 fs-16">Project: <?php echo $task->projectName; ?></p>
                           </div>
                        <div class="col-md-4 text-end ">
                           <p class="mb-0 ">
                              <span class="me-2 text-dark "> <?= Utility::date_format($task->taskStart, 'midDate') ?> to <?= Utility::date_format($task->taskDeadline, 'midDate') ?>   </span>
                              |
                              <span class="mx-2 fst-italic text-primary"> <?php echo $task->taskUser; ?> </span>
                           </p>

                        </div>
                     </div>
                  </div>
                  <?php
               }
            } else {
               echo "<div class='font-24 center'>";
               Alert::info("<i class='icon-business-time font-26 p-3 mx-5'></i>There are no completed tasks for {$dt->format('l')}  {$dt->format('jS \o\f F')}");
               echo "</div>";
            }?>
         </div>
      </div>
      <?php

   }?>
</div>