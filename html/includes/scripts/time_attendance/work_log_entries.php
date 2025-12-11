
<?php
/*ADD EXPENSES*/
echo Utility::form_modal_header("addExpense", "time_attendance/manage_expense.php", "Add Expense", array("modal-xl", "modal-dialog-centered"), $base);
   include "includes/scripts/time_attendance/modals/manage_expense_improved.php";
echo Utility::form_modal_footer("Save Expense", "manageExpense", 'btn btn-primary btn-sm', true);
/*Manage User Absence*/
echo Utility::form_modal_header ("manageAbsence", "time_attendance/manage_absence.php", "Add Absence {$dt->format('d/m/Y')}", array('modal-xl', 'modal-dialog-centered'), $base);
   $absenceTypes= TimeAttendance::absence_types ( array("Suspended"=>"N"), false,$DBConn);
   include "includes/scripts/time_attendance/modals/manage_absence_improved.php";
echo Utility::form_modal_footer();?>
<div class="card mt-4 shadow-lg hourEntries">
   <div class="card-body ">
      <div class="row bs-gray-100  mb-3 border-bottom border-dark border-2">
         <div class="col-md-6 ">
            <h4 class="text-capitalize t400">Your Work Log Entries
               <a class="small" href="<?php echo $base ."html/?{$getString}" ?>" title="Refresh page">
                  <i class="ti ti-refresh-alert"></i>
               </a>
            </h4>
         </div>
         <div class="col-md-6 text-end workHourBtns">
            <span class="dropdown">
                <button class="btn btn-success addWork btn-sm mt-2 addWorkHourBtn dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                    Add Work Hours
                </button>
                <ul class="dropdown-menu dropmenu-light-success" aria-labelledby="dropdownMenuButton1" style="min-width: 300px;">
                    <li><a class="dropdown-item  " href="#" data-bs-toggle="collapse" data-bs-target="#add_work_hours" aria-expanded="false" aria-controls="add_work_hours">Project Task work hour</a></li>
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#add_activity_hours" aria-expanded="false" aria-controls="add_activity_hours">Activity Work Hour</a></li>
                </ul>
            </span>
            <a class="btn btn-success w-15 btn-sm mt-2" href="#" data-bs-toggle="modal" data-bs-target="#manageAbsence">Add Absence</a>
            <a class="btn btn-success w-15 btn-sm mt-2" href="#" data-bs-toggle="modal" data-bs-target="#addExpense">Add Expense</a>
         </div>
      </div>
      <?php

      // var_dump($allSalesCases);
      echo Utility::form_modal_header("add_activity_hours", "time_attendance/manage_activity_hours.php", "Add Activity Work Hour", array('modal-xl', 'modal-dialog-centered'), $base);
      include "includes/scripts/time_attendance/modals/manage_activity_hours.php";
      echo Utility::form_modal_footer("Save Activity", "manageActivity", 'btn btn-primary btn-sm');
      ?>
      <div class="collapse" id="add_work_hours">
         <div class="col-12 bg-light my-4 shadow ">
            <button class="btn  btn-icon rounded-pill  btn-danger float-end" data-bs-toggle="collapse" data-bs-target="#add_work_hours" aria-expanded="false" aria-controls="add_work_hours">
                <i class="fa-solid fa-close"></i>
            </button>
            <?php include "includes/scripts/time_attendance/collapse/manage_work_hour_improved.php"; ?>
         </div>
      </div>
      <script>
         // check that page is loaded
         // document.addEventListener('DOMContentLoaded', function() {
         //    console.log('Page loaded successfully');
         //    const myCollapsible = document.getElementById('add_work_hours')
         //    myCollapsible.addEventListener('shown.bs.collapse', event => {
         //       document.querySelector(".addWorkHourBtn").innerHTML= "Cancel";
         //    });
         //    myCollapsible.addEventListener('hidden.bs.collapse', event => {
         //       document.querySelector(".addWorkHourBtn").innerHTML= "Add Work Hour";
         //    });
         // });
      </script>
		<?php
      if ($activeTask) {?>
         <script>
            let addWork = document.querySelector('.addWork');
         </script>
         <?php
      }
		$absence= TimeAttendance:: absence_full (array('userID'=>$userDetails->ID, 'absenceDate'=>$dt->format('Y-m-d'), 'Suspended'=>"N"), false, $DBConn);
      $timeLogArrFilter =		array('taskDate'=> $dt->format('Y-m-d'), 'employeeID'=>$userID,  'Suspended'=> 'N');
      // var_dump($timeLogArrFilter);
		$todaysTimelogs = TimeAttendance::project_tasks_time_logs_full($timeLogArrFilter, false, $DBConn);
		$durationArray= array();
      /**
       * Timelogs list
      * ***********************/
      $kay=0;
      if ($todaysTimelogs) {
         foreach ($todaysTimelogs as $key => $timelog) {

            // var_dusmp($timelog);
            $bgTaskColor = "";
            $timeConsumption = 0;
            $totalTaskTimeLogs= 0;
            $taskTotalTimelog = 0;
            $timelogFiles = TimeAttendance::timelog_files(array('timelogID'=>$timelog->timelogID), false, $DBConn);

            if($timelogFiles) {
               $timelog->files = $timelogFiles;
               // var_dump($timelogFiles);
            }

            // var_dump($timelog);

            $timelogID= $timelog->timelogID;
            // var_dump($timelog);
            if($timelog->projectID && $timelog->projectPhaseID && $timelog->projectTaskID) {
               $timelog->projectPhaseName= $timelog->projectPhaseName ? $timelog->projectPhaseName : "";
               $timelog->workTypeName= $timelog->workTypeName ? $timelog->workTypeName : "";
               $projectManagers = $timelog->projectManagersIDs ? explode(",", $timelog->projectManagersIDs) : array();
               // add project owner to project managers
               $projectManagers[] = $timelog->projectOwnerID;
               $projectManagers = array_unique($projectManagers);
               $isProjectmanager = in_array($userDetails->ID, $projectManagers) ? true : false;

               // Project Phase Time Logs
               $projectPhaseDetails= Projects::project_phases (array('projectPhaseID'=>$timelog->projectPhaseID), true, $DBConn);
               $allPhaseLogs = TimeAttendance::project_tasks_time_logs(array('projectPhaseID'=>$timelog->projectPhaseID), false, $DBConn);
               if ($allPhaseLogs) {
                  $phaseTime = 0;
                  foreach ($allPhaseLogs as $key => $phaseLog) {
                     $decimalTime = (isSet($phaseLog->taskDuration) && !empty($phaseLog->taskDuration)) ?Utility::Time_to_decimal($phaseLog->taskDuration): 0;
                     $phaseTime += $decimalTime ;
                  }
               }
               $phaseEstimate = (isset($projectPhaseDetails->phaseWorkHrs) && !empty($projectPhaseDetails->phaseWorkHrs)) ? Utility::Time_to_decimal($projectPhaseDetails->phaseWorkHrs,".") : 0;
               if ($phaseEstimate > 0) {
                  $percentage = ($phaseTime / $phaseEstimate) * 100;
                  $timeLeft = $phaseEstimate - $phaseTime;
               } else {
                  $percentage = 0;
                  $timeLeft = 0;
               }
            } else {
               $timelog->projectPhaseName= "";
               $timelog->workTypeName= "";
            }

            /*project edit*/
            $projectTasks=Projects::project_tasks (array('projectID'=> $timelog->projectID, 'Suspended'=> 'N'), false, $DBConn);

            $durationArray[]=$timelog->taskDuration;

            $totalTaskTimeLogs= TimeAttendance::project_tasks_time_logs(array("projectTaskID"=>$timelog->projectTaskID, "Suspended"=>"N"), false, $DBConn);
            $taskTotalTimelog = TimeAttendance::total_task_timelogs($timelog->projectTaskID, $DBConn);

            $allocatedTime =$timelog->hoursAllocated*3600;


            if ($taskTotalTimelog > $allocatedTime) {
               $bgTaskColor = "  bg-danger-subtle ";
            }

            if ($allocatedTime > 0) {
               $timeConsumption = round(($taskTotalTimelog/$allocatedTime)*100, 2);
            } else {
               $timeConsumption ="-";
            }?>

            <!-- Display time log  -->
            <div class="col-12 border shadow my-3 timelogBrief <?php echo $bgTaskColor; ?>"  id="<?php echo "timelogDiv{$timelog->timelogID}" ?>">
               <div class="" >

                  <button class="col-12 p-2 btn btn-white" id="editTimelogBtn<?php echo $timelogID  ?>">
                     <div class="row d-flex align-items-center">
                        <div class="col-md-5 text-start d-flex justify-content-start align-items-center ">
                           <div class=" d-flex justify-content-center align-items-center">
                              <span class="avatar bd-blue-800 avatar-md me-2 avatar-rounded">
                                 <i class="fa-solid fa-clock fs-26 "></i>
                              </span>
                           </div>
                           <div>
                              <h6 class="mb-0"><?php echo $timelog->clientName  ?> :
                                 <small><?php echo $timelog->projectName ? $timelog->projectName : "";  ?></small>
                                 <span class= "text-primary font-14 t300 "><?php  echo !empty($timelog->projectPhaseName) ? "({$timelog->projectPhaseName})" : ''  ?></span>
                              </h6>
                              <p class=" mb-0 ">
                                 <?php echo  $timelog->projectTaskName ? $timelog->projectTaskName :($timelog->activityName ?$timelog->activityName : "" ) ."<span class= 'text-primary'>{$timelog->workTypeName}</span>"  ?>
                              </p>
                           </div>
                        </div>

                        <div class="col-md-1">
                           <!-- <span class="d-block">Hrs Logged</span> -->
                           <span class="font-20"><?php echo "{$timelog->taskDuration} h"; ?></span>
                        </div>
                        <div class="col-md">
                           <span class="d-block">Task Description</span>
                           <span class="font-15"><?php echo $timelog->taskNarrative ? $timelog->taskNarrative : ""; ?></span>
                        </div>

                        <?php
                        if($timelog->projectID && $timelog->projectPhaseID && $timelog->projectTaskID) {?>
                           <div class="col-md-2 text-center">
                              <span class="d-block px-2">Task Time Cunsumption <i class="bi-info-circle f"></i></span>
                              <span class="font-22">
                              <?php

                                 echo "<span class='font-16'>".number_format(round($taskTotalTimelog/3660,  2), 2, ".", "")." hrs of ". number_format(round($allocatedTime/3600, 2), 2, ".", "")." hrs </span> ";
                                 echo $isProjectmanager ?"<br> {$timeConsumption} %": "";
                                 ?>
                              </span>
                           </div>
                           <?php
                        }
                        if (isset($isProjectmanager) && $isProjectmanager) {?>
                           <div class="col-md-2 text-center">
                              <span class="d-block">Phase Time Logged <i class="bi-info-circle float-end"></i></span>
                              <span class="font-22"><?php echo "{$phaseTime}  of  ". number_format($phaseEstimate, 2, ".", "") ." <span class='font-18 d-block'>hrs done</span> "; ?></span>
                           </div>

                           <div class="col-sm nomargins_p text-limit-2 text-start ">
                              <span class="font-18 t600"><?php echo $timeLeft  ?> hours</span> Left on Phase
                              <div class="col-md-11">
                                 <div class="progress ">
                                    <div class="progress-bar progress-bar-striped" role="progressbar" aria-label="Example with label" style="width: <?php echo round($percentage,2) ?>%;" aria-valuenow="<?php echo round($percentage,2) ?>" aria-valuemin="0" aria-valuemax="100">
                                       <?php echo $phaseTime; ?> %
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <?php
                        } ?>
                     </div>
                  </button>
               </div>
            </div>
               <?php include 'includes/scripts/time_attendance/manage_work_hour.php'; ?>
            <script>
               document.addEventListener('DOMContentLoaded', function() {
                  // console.log('DOM loaded successfully work');
                  let timelogBtn<?php echo $timelogID  ?> = document.getElementById('editTimelogBtn<?php echo $timelogID  ?>');
                  // console.log(timelogBtn<?php echo $timelogID  ?>);
                  timelogBtn<?php echo $timelogID  ?>.addEventListener('click', displayEdit );
                  // console.log(timelogBtn<?php echo $timelogID  ?>);
                  function displayEdit() {
                     let timelogDiv= document.getElementById('timelogDiv<?php echo $timelogID  ?>'),
                        editTimelogDiv= document.getElementById('editTimelogDiv<?php echo $timelogID;  ?>'),
                        editTimeLogClass = document.querySelectorAll(".editTimeLogClass"),
                        timelogBrief= document.querySelectorAll(".timelogBrief");
                        for (let i = editTimeLogClass.length - 1; i >= 0; i--) {
                           if(editTimeLogClass[i].classList.contains("d-none") !== true){
                              editTimeLogClass[i].classList.add("d-none");
                           }
                           if(timelogBrief[i].classList.contains("d-none") == true){
                              timelogBrief[i].classList.remove("d-none");
                           }
                        }
                     timelogDiv.classList.add("d-none");
                     editTimelogDiv.classList.remove("d-none");
                     editTimelogDiv.querySelector(".fa-close").addEventListener("click", (evt)=> {
                        evt.preventDefault();
                        evt.stopPropagation();
                        editTimelogDiv.classList.add("d-none");
                        timelogDiv.classList.remove("d-none");
                     });
                  }
               });

            </script>
				<?php
         }
         $allTime=0;
         if ($durationArray) {
            foreach ($durationArray as $ts => $duration) {
               $totalTime[$ts]=explode(':',$duration);
               $hours =0;
               $mins=0;
               $totalTimeSec[$ts]= ($totalTime[$ts][0] * 3600) + ($totalTime[$ts][1] * 60);
               $allTime +=$totalTimeSec[$ts];
            }
         }
      } else {
         echo "<div class='font-24 center'>";
         Alert::info("<i class='icon-business-time font-26 p-3 mx-5'></i>There are no work hours entries Saved for {$dt->format('l')}  {$dt->format('jS \o\f F')}");
         echo "</div>";
      }

      /*Manage Absence
      ======================*/
      include "includes/scripts/time_attendance/work_hour_absence.php";

      /*Manage Expense
      ================*/
      include "includes/scripts/time_attendance/project_expense.php";

      if ($todaysTimelogs) {
         $time_output= sprintf("%02s:%'02s\n", intval($allTime/60/60), abs(intval(($allTime%3600) / 60)), abs($allTime%60));?>
         <div class="col-12 " >
            <div class="row" >
               <div class="fbox-icon fbox-outline "></div>
               <div class="col-md " >
                  <div class="row">
                     <div class="col-sm-5 text-start">
                        <h4 class="mb-0 float-end font-26">Total Time</h4>
                     </div>
                     <div class="col-sm-2 center">
                        <span id="totaltime" class="font-26 text-primary"><?php echo "{$time_output} h"; ?></span>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <?php
      } ?>
   </div>
   <script>
      userExpenses = <?php echo json_encode($expenses); ?>;
   </script>
</div>
