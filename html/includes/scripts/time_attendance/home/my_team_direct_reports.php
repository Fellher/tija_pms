<?php
$entityID = (isset($_GET['entityID']) && !empty($_GET['entityID'])) ? Utility::clean_string($_GET['entityID']) : $employeeDetails->entityID;
$orgChart = Data::org_charts(['entityID'=>$entityID], false, $DBConn);
$employeesDirectReports = Employee::employees(['supervisorID'=>$userDetails->ID], false, $DBConn);
// var_dump($getString);
// var_dump($orgChart);
// echo "<h5>Employee Details</h5>";
// var_dump($employeeDetails);
if(!$isAdmin){
   if(!isset($employeeDetails->jobTitleID) || empty($employeeDetails->jobTitleID)) {
      Alert::info("You need to have a job title assigned to access this page", true, array('fst-italic', 'text-center', 'font-18'));
      return;
   }
}
// $myPosition = Data::org_chart_position_assignments(['positionID'=>$employeeDetails->jobTitleID], true, $DBConn);
// var_dump($myPosition);

$myTeam = array();
$nodeID .= "my_team";

$myTeamPositions= Array();
// get direct reporting By position/Job Role
if ($orgChart) {
   $userArr = array();
   foreach ($orgChart as $key => $org) {
      $positions = Data::org_chart_position_assignments(['orgChartID'=>$org->orgChartID], false, $DBConn);
      // echo "<h4> Org Chart ID: {$org->orgChartID} Positions</h4>";
      // var_dump($positions);
      if ($positions) {
         foreach ($positions as $key => $position) {
            if(isset($myPosition->positionAssignmentID) && $position->positionParentID == $myPosition->positionAssignmentID ) {
               $myTeamPositions[] = $position;
            }
         }
      }
      // var_dump($myTeamPositions);
      if($myTeamPositions) {
         // var_dump($myTeamPositions[3]);
         foreach ($myTeamPositions as $key => $team) {
            // $us[] = Data::users(['ID'=>$team->userID], true, $DBConn);
              // var_dump($position);
              $teamUserDetails = Data::users(['jobTitleID'=>$team->positionID], false, $DBConn);

              // var_dump($teamUserDetails);
              if($teamUserDetails){
                 foreach ($teamUserDetails as $key => $user) {
                    $userArr[] = $user;
                    // var_dump($user);
                    // $myTeamPositions[] = $position;
                 }
              }
         }
      }
      // var_dump($userArr);
   }
   $myTeam = $userArr;

   // var_dump($myTeam);


   // get directreporting By direct reporting users

   // var_dump($employeesDirectReports);
   if($employeesDirectReports){
      foreach ($employeesDirectReports as $employee) {
         $existingIDs = array_column($myTeam, 'ID');
         if (!in_array($employee->ID, $existingIDs)) {
            $myTeam[] = $employee;
         }
      }

   }
   // var_dump($myTeam);
   $team=array();
   if($myTeam) {
      foreach ($myTeam as $key => $team) {
         $nodeID .= $team->ID . '_';
         $assignedTasks =Projects::task_user_assignment(['userID'=>$team->ID, 'Suspended'=>'N'], false, $DBConn);
         // var_dump($assignedTasks);
         $taskCompletedArray = array();
         $subtaskCompletedArray = array();
         if($assignedTasks) {
            foreach ($assignedTasks as $key => $task) {
               $taskDetails = Projects::project_tasks(['projectTaskID'=>$task->projectTaskID], true, $DBConn);
               // check if tasks are completed for today
               $today = $DOF;
               // echo "<h5> today is {$DOF}</h5>";
               // check if task is completed
               $taskCompletedArr = TimeAttendance::project_tasks_time_logs(['projectTaskID'=>$task->projectTaskID, 'taskDate'=>$today, 'employeeID'=>$team->ID, 'dailyComplete'=>'Y' ], false, $DBConn);
               // var_dump($taskCompletedArr);
               $taskCompleted = false;
               if($taskCompletedArr) {
                  foreach ($taskCompletedArr as $key => $completed) {
                     if($completed->subtaskID === ""){
                        $taskCompleted = true;
                        $taskCompletedArray = $completed;
                     } else {
                        $taskCompleted = false;
                     }
                  }
               } else {
                  if ($taskDetails && is_object($taskDetails)) {
                     $taskDetails->completed = 'N';
                  }
               }

               $assignedActivities = Projects::project_subtasks(['projectTaskID'=>$task->projectTaskID, 'assignee'=>$team->ID, 'Suspended'=>'N'], false, $DBConn);
               if($assignedActivities){
                  foreach ($assignedActivities as $key => $activity) {
                     // check if task is completed
                     $subtaskCompletedArr = TimeAttendance::project_tasks_time_logs(
                        ['projectTaskID'=>$activity->projectTaskID, 'taskDate'=>$today, 'employeeID'=>$team->ID, 'subtaskID'=> $activity->subtaskID, 'dailyComplete'=>'Y' ], false, $DBConn);
                     // var_dump($subtaskCompletedArr);
                     if($subtaskCompletedArr) {
                        foreach ($subtaskCompletedArr as $key => $completed) {
                           $subtaskCompletedArray[] = $completed;
                           if($completed->subtaskID !== ""){
                              $subtaskCompleted = true;
                           } else {
                              $subtaskCompleted = false;
                           }
                        }
                     } else {
                        $subtaskCompleted = false;
                     }
                  }
               }
               // $subtaskCompleted = TimeAttendance::project_tasks_time_logs_full([ 'taskDate'=>$today, 'userID'=>$team->ID], false, $DBConn);
            }
         }
         // var_dump($nodeID);
         // var_dump($subtaskCompletedArray);
         $assignedActivities = Projects::project_subtasks([ 'assignee'=>$team->ID, 'Suspended'=>'N'], false, $DBConn);
         // var_dump($taskDetails);
         // var_dump($assignedActivities);


         // var_dump($team);

         // var_dump($userDetails);
         ?>
       <div class="alert alert-primary alert-dismissible fade show custom-alert-icon shadow-sm" role="alert">
         <div class="alert-icon float-start mb-2">
            <span class="avatar bd-blue-800 avatar-xs me-2 avatar-rounded float-start">
               <AC>
                  <?= Utility::generate_initials($team->employeeName); ?>
                  </AC>
            </span>
         </div>
         <div class="alert-text">
            <div class="d-flex  justify-content-between">
               <div class="">
                  <strong class=" "><?php echo $team->employeeName; ?></strong>
                  <p class="mb-0 fst-italic"><?php echo $team->jobTitle; ?></p>
               </div>

               <div class="task-count">
                  <span class="fs-20 text-primary">
                     <i class="bi bi-check-circle"></i>
                  </span>
                  <span class="fs-18"><?= (isset($assignedTasks) && !empty($assignedTasks)) ? count($assignedTasks) : 0; ?> tasks and
                  <?= (isset($assignedActivities) && !empty($assignedActivities)) ? count($assignedActivities) : 0; ?> activities assigned</span>
               </div>
               <div class="completed-assignments">
                  <span class="fs-20 text-secondary">
                     <i class="bi bi-check-circle"></i>
                  </span>
                  <span class="fs-18 completed-tasks<?= $nodeID ?> ">
                     <?= ($taskCompletedArray && is_array($taskCompletedArray)) ?  count($taskCompletedArray) : "0" ?>  tasks and
                     <?=  ($subtaskCompletedArray && is_array($subtaskCompletedArray)) ?  count($subtaskCompletedArray) : "0" ?> activities completed

                  </span>
               </div>
               <div class="flex-shrink-0">
                  <a class="btn btn-primary btn-sm collapsed " data-bs-toggle="collapse"
                     href="#<?= "{$nodeID}_team_collapse" ?>" role="button" aria-expanded="false"
                     aria-controls="<?= "{$nodeID}_team_collapse" ?>">
                    view details
                  </a>

                  <!-- <a href="<?php echo $base .'html/?s='.$s.'&ss='.$ss.'&p='.$p.'&uid='.$team->ID;?>" class="btn btn-primary btn-sm">View</a> -->
               </div>
            </div>
         </div>

         <div class="collapse" id="<?= "{$nodeID}_team_collapse" ?>">
            <div class="card card-body mb-0">
               <div class="list-group list-group-flush">

               <?php
               // var_dump($assignedTasks[1]);
               $completedTasksCount = 0;
               $totalCompletedSubtasks = 0;
               if($assignedTasks) {
                  $completedTasksArray = array();
                  foreach ($assignedTasks as $key => $task) {  ?>
                     <div class= "list-group-item list-group-item-action flex-column align-items-start">
                        <div class="row w-100">
                           <div class="col-12 col-md-4 col-lg-4">

                              <h6 class=" text-dark mb-0"><?php echo $task->projectTaskName; ?></h6>
                           </div>

                           <div class="col-4 col-md-4 col-lg-4">
                              <small class=" text-dark mb-0"><?php echo $task->projectName; ?></small>

                              <small class="text-muted">(<?php echo $task->clientName; ?>)</small>
                           </div>
                           <div class=" col   text-end">
                              <?php
                                $today = $DOF;
                                // echo "<h5> today is {$DOF}</h5>";
                                // check if task is completed
                                $taskCompletedFromLog = false;
                                $filterTaskLog = ['projectTaskID'=>$task->projectTaskID, 'taskDate'=>$today, 'employeeID'=>$team->ID, 'dailyComplete'=>'Y' ];
                                $taskCompletedArr = TimeAttendance::project_tasks_time_logs($filterTaskLog, false, $DBConn);
                              //   var_dump($taskCompletedArr);
                              //   $taskCompletedArr = TimeAttendance::project_tasks_time_logs(, false, $DBConn);
                                if($taskCompletedArr) {
                                 //   var_dump($taskCompleted);

                                 $lastCompletedTaskLog = array_reduce($taskCompletedArr, function($carry, $item) {
                                    return $carry ? $carry->DateAdded > $item->DateAdded ? $carry : $item : $item;
                                 }, null);
                                 if($lastCompletedTaskLog) {
                                    $taskCompletedFromLog = true;
                                    $taskCompletedArray = $lastCompletedTaskLog;
                                 }
                                 // var_dump($lastCompletedTaskLog);
                                 //   foreach ($taskCompletedArr as $key => $completed) {
                                 //    var_dump($completed);
                                 //      if($completed->subtaskID === ""){
                                 //         $taskCompletedFromLog = true;
                                 //         $taskCompletedArray = $completed;
                                 //      }
                                 //   }
                                }?>
                              <span class="badge bg-<?= ($taskCompletedFromLog) ? 'success' : 'warning' ?>"><?= ($taskCompletedFromLog) ? 'Completed' : 'Pending' ?></span>
                           </div>
                        </div>
                        <?php
                           $subTasks = Projects::project_subtasks(['projectTaskID'=>$task->projectTaskID, 'assignee'=>$team->ID, 'Suspended'=>'N'], false, $DBConn);

                           $completedSubTasksArray = array();
                           if($subTasks) {
                              foreach ($subTasks as $key => $subTask) {
                                 // var_dump($subTask);
                                 $logFilter = array('subtaskID'=>$subTask->subtaskID, 'taskDate'=>$DOF, 'employeeID'=>$team->ID,  'Suspended'=>'N');

                                 // var_dump($logFilter);
                                 $subTaskChangelogs = TimeAttendance::daily_task_status_change_log_full($logFilter, false, $DBConn);
                                 // var_dump($subTaskChangelogs);
                                 $latestStateChangelog = null;
                                 if($subTaskChangelogs) {
                                    $latestStateChangelog = array_reduce($subTaskChangelogs, function($carry, $item) {
                                       return $carry ? $carry->changeDateTime > $item->changeDateTime ? $carry : $item : $item;
                                    }, null);
                                 }
                                 // var_dump($latestStateChangelog);
                                 ?>
                                 <div class="row w-100 py-1 border-bottom">
                                    <div class="col-12 col-md-4 col-lg-4">
                                       <span class=" text-dark mb-0"><?php echo $subTask->subTaskName; ?></span>
                                    </div>
                                    <div class="col-4 col-md-4 col-lg-4">
                                       <small class=" text-dark mb-0"><?php echo $subTask->subTaskDescription; ?></small>
                                    </div>
                                    <?php
                                    // get timelogs
                                    $subtaskCompletedArr = TimeAttendance::project_tasks_time_logs(['projectTaskID'=>$subTask->projectTaskID, 'taskDate'=>$today, 'employeeID'=>$team->ID, 'subtaskID'=> $subTask->subtaskID, 'dailyComplete'=>'Y' ], false, $DBConn);
                                    // var_dump($subtaskCompletedArr);
                                    $subtaskCompleted = false;
                                    if($subtaskCompletedArr) {
                                       foreach ($subtaskCompletedArr as $key => $completed) {
                                          if($completed->subtaskID !== ""){
                                             $subtaskCompleted = true;
                                          }
                                       }
                                    } else {
                                       $subtaskCompleted = false;
                                    }
                                    // var_dump($taskStatusList);

                                    $lastTaskStatus = end($taskStatusList);
                                    // var_dump($lastTaskStatus);

                                    $subtaskCompletedStatus = (isset($lastStatus) && $lastStatus->taskStatusName) ? $lastStatus->taskStatusName : "Not Started";
                                    // echo "<h4> {$count}</h4>";
                                    (isset($latestStateChangelog)  && $latestStateChangelog->taskStatusID == 5 ) ? $completedSubTasksArray[] = $subTask->subtaskID : "";

                                    ?>

                                    <div class="col-4 col-md-4 col-lg-4">
                                       <span class="badge py-0 bg-<?= (isset($latestStateChangelog)  && $latestStateChangelog->taskStatusID == 5 ) ? 'success' : 'danger' ?> text-capitalize">
                                          <?= (isset($latestStateChangelog) && !empty($latestStateChangelog->taskStatusName)) ? $latestStateChangelog->taskStatusName : "Not Started"  ?>
                                       </span>
                                    </div>
                                 </div>
                                 <?php

                              }

                              // var_dump($completedSubTasksArray);


                              if($completedSubTasksArray) {

                                 foreach ($completedSubTasksArray as $key => $completedSubTask) {
                                    $subtaskCompleted = true;
                                    // var_dump($completedSubTask);
                                 }
                              } else {
                                 $subtaskCompleted = false;
                              }
                              $subtaskCompletedCount = count($completedSubTasksArray);
                           }
                           // var_dump($subTasks);
                           if(isset($subtasks) && count($subTasks) == $subtaskCompletedCount) {
                              $completedTasksCount++;
                              $totalCompletedSubtasks += $subtaskCompletedCount;
                           }

                        ?>
                     <script>
                        document.addEventListener('DOMContentLoaded', function() {

                           console.log(<?= $completedTasksCount ?>)

                           document.querySelector('.completed-tasks<?= $nodeID ?>').innerHTML = `Completed Tasks: <?= $completedTasksCount ?> | Completed Subtasks: <?= $totalCompletedSubtasks ?>`;
                        });
                     </script>
                     </div>
                     <?php

                  }
               } else {
                  Alert::info("No Tasks Assigned", true, array('fst-italic', 'text-center', 'font-18'));
               }

               /*echo "<h5> Completed Tasks: {$completedTasksCount}</h5>";
               echo "<h5> Completed Subtasks: {$totalCompletedSubtasks}</h5>"; */?>
               </div>
            </div>
         </div>
         <!-- <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"><i class="bi bi-x"></i></button> -->
         </div>
         <?php
      }
   } else {
      Alert::info("No Team Members Found", true,    array('fst-italic', 'text-center', 'font-18'));
   }
   $minGetString=  str_replace("&d={$DOF}", "", $getString);

   // var_dump($minGetString);
   ?>

   <div class="card custom-card">
      <div class="card-body">
         <div class="d-flex justify-content-between align-items-center">
            <h2 class="t300 font-20">Week Report</h2>
            <div class=" m-0">
               <a  class="btn btn-link m-0 py-0" href="<?php echo $base ."html/{$minGetString}&week=".($week-1).'&year='.$year.'&uid='.$userID; ?>">
                  <i class="fa-solid fa-circle-chevron-left"></i></a> <!--Previous week-->
               <span>Week <?php echo $dt->format('W') ?></span>
               <a class="btn btn-link m-0 py-0" href="<?php echo $base ."html/?{$minGetString}&week=".($week+1).'&year='.$year.'&uid='.$userID;?>"><i class="fa-solid fa-circle-chevron-right"></i></a> <!--Next week-->
               <a href="<?php echo $base ."html/?{$minGetString}&week=".($week+1).'&year='.$year.'&uid='.$userID;?>" class="btn btn-white border"> Today</a>
            </div>
            <div class="dropdown">
               <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                  <?php echo $dt->format('F Y') ?>
               </button>
               <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                  <?php
                  for ($i=0; $i<12; $i++) {
                     $monthDate = date('F Y', strtotime("+$i month", strtotime($dt->format('Y-m-01'))));
                     $yearli = date('Y', strtotime($monthDate));
                     $monthli = date('m', strtotime($monthDate));
                     echo "<li><a class='dropdown-item' href='{$base}html/?{$minGetString}&month={$monthli}&year={$yearli}&uid={$userID}'>{$monthDate}</a></li>";
                  }?>
               </ul>
            </div>
         </div>
         <div class="row">
            <?php
            if($team){?>
               <div class="table-responsive">
                  <table class="table table-striped table-bordered table-sm">
                     <thead>
                        <tr>
                           <th>Employee Name</th>
                           <th>Job Title</th>
                           <?php
                           for ($i=0; $i<7; $i++) {
                              $dayDate = date('d M', strtotime("+$i day", strtotime($dt->format('Y-m-d'))));
                              echo "<th class='text-center'>{$dayDate} <br><span class='fs-18 t300'>".date('D', strtotime("+$i day", strtotime($dt->format('Y-m-d'))))."</span> </th>";
                           }?>
                           <th>Totals</th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php
                        foreach ($myTeam as $key => $team) {
                           echo "<tr>";
                           echo "<td>{$team->employeeName} </td>";
                           echo "<td>{$team->jobTitle}</td>";
                           $userTotal = 0;

                           for ($i=0; $i<7; $i++) {
                              $nodeID = $team->ID . "_";



                              $dayDate = date('Y-m-d', strtotime("+$i day", strtotime($dt->format('Y-m-d'))));
                              $timelogs = TimeAttendance::project_tasks_time_logs(['taskDate'=>$dayDate, 'employeeID'=>$team->ID], false, $DBConn);
                              $totalTime = 0;
                              if($timelogs){
                                 foreach ($timelogs as $key => $log) {
                                    // var_dump($log);
                                   $totalTime += Utility::time_to_sec($log->taskDuration);
                                 }
                              } else {
                                 $timelogs = null;
                              }
                              $userTotal +=$totalTime;
                              $totalTimeConverted = Utility::secToTime($totalTime,"hr_min");

                              // Add data for each day here
                              echo "<td>{$totalTimeConverted}</td>";
                           }
                           $userTotalConverted = Utility::secToTime($userTotal,"hr_min");
                           echo "<td>{$userTotalConverted}</td>"; // Totals
                           echo "</tr>";
                        }?>
                     </tbody>
                  </table>
               </div>
               <?php
            }?>
         </div>
      </div>
   </div>

   <div class="card custom-card">
      <div class="card-body">
         <div class="d-flex justify-content-between align-items-center">
            <h2 class="t300 font-20">Leave Approvals</h2>
            <div class=" m-0">
               <a  class="btn btn-link m-0 py-0" href="<?php echo $base ."html/{$minGetString}&week=".($week-1).'&year='.$year.'&uid='.$userID; ?>">
                  <i class="fa-solid fa-circle-chevron-left"></i></a> <!--Previous week-->
               <span>Week <?php echo $dt->format('W') ?></span>
               <a class="btn btn-link m-0 py-0" href="<?php echo $base ."html/?{$minGetString}&week=".($week+1).'&year='.$year.'&uid='.$userID;?>"><i class="fa-solid fa-circle-chevron-right"></i></a> <!--Next week-->
               <a href="<?php echo $base ."html/?{$minGetString}&week=".($week+1).'&year='.$year.'&uid='.$userID;?>" class="btn btn-white border"> Today</a>
            </div>
            <div class="dropdown">
               <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                  <?php echo $dt->format('F Y') ?>
               </button>
               <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                  <?php
                  for ($i=0; $i<12; $i++) {
                     $monthDate = date('F Y', strtotime("+$i month", strtotime($dt->format('Y-m-01'))));
                     $yearli = date('Y', strtotime($monthDate));
                     $monthli = date('m', strtotime($monthDate));
                     echo "<li><a class='dropdown-item' href='{$base}html/?{$minGetString}&month={$monthli}&year={$yearli}&uid={$userID}'>{$monthDate}</a></li>";
                  }?>
               </ul>
            </div>
         </div>
         <div class="row">
            <?php
            if($team){?>
               <div class="table-responsive">
                  <table class="table table-striped table-bordered table-sm">
                     <thead>
                        <tr>
                           <th>Employee Name</th>
                           <th>Job Title</th>
                           <?php
                           for ($i=0; $i<7; $i++) {
                              $dayDate = date('d M', strtotime("+$i day", strtotime($dt->format('Y-m-d'))));
                              echo "<th class='text-center'>{$dayDate} <br><span class='fs-18 t300'>".date('D', strtotime("+$i day", strtotime($dt->format('Y-m-d'))))."</span> </th>";
                           }?>
                           <th>Totals</th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php
                        foreach ($myTeam as $key => $team) {
                           echo "<tr>";
                           echo "<td>{$team->employeeName} </td>";
                           echo "<td>{$team->jobTitle}</td>";
                           $userTotal = 0;

                           for ($i=0; $i<7; $i++) {
                              $nodeID = $team->ID . "_";



                              $dayDate = date('Y-m-d', strtotime("+$i day", strtotime($dt->format('Y-m-d'))));
                             $leaveRequests=Leave::leave_applications_full(['employeeID'=>$team->ID, 'startDate'=>$dayDate], false, $DBConn);
                              // var_dump($leaveRequests);
                              $totalTime = 0;
                              if($leaveRequests){
                                 foreach ($leaveRequests as $key => $log) {
                                    // var_dump($log);
                                 //   $totalTime += Utility::time_to_sec($log->taskDuration);
                                 }
                              } else {
                                 $leaveRequests = null;
                              }
                              echo "<td></td>";
                           }


                              // Add data for each day here
                              echo "<td></td>";
                           echo "<td></td>"; // Totals
                           echo "</tr>";
                        }?>
                     </tbody>
                  </table>
               </div>
               <?php
            }  ?>
            </div>

         </div>
      </div>
   </div>

   <?php


} else {
   Alert::error("No Org Chart Found", true, array('fst-italic', 'text-center', 'font-18'));
   $orgChartDetails = false;
}?>