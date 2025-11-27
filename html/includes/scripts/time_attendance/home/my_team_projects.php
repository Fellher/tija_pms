<?php
// check for projects that I am a project owner
$MyProjects = Projects::projects_full(array('projectOwnerID'=>$userDetails->ID, 'Suspended'=>'N'), false, $DBConn);

// var_dump($MyProjects);
// check for all taksks that I have been assigned to
$MyTasks= Projects::task_user_assignment(array('userID'=>$userDetails->ID, 'Suspended'=>'N'), false, $DBConn);

// var_dump($MyTasks);

$subTaskAssignments = Projects::project_subtasks_full(array('assignee'=>$userDetails->ID, 'Suspended'=>'N'), false, $DBConn);

$uniqueProjectsArr = array();

// var_dump($subTaskAssignments);


$uniqueTaskProjects = $MyTasks ?  array_unique(array_column($MyTasks, 'projectID')) : array();
// var_dump($uniqueTaskProjects);

$uniqueSubTasksProjects = $subTaskAssignments ? array_unique(array_column($subTaskAssignments, 'projectID')) : array();
// var_dump($uniqueSubTasksProjects);


$uniqueProjectsArr = array_merge($uniqueTaskProjects, $uniqueSubTasksProjects);
$uniqueProjectsArr = array_unique($uniqueProjectsArr);

// var_dump($uniqueProjectsArr);

if(count($uniqueProjectsArr) > 0) {
   foreach ($uniqueProjectsArr as $key => $value) {
      if($value == '') {
         unset($uniqueProjectsArr[$key]);
      }
      $projects[] = Projects::projects_full(array('projectID'=>$value, 'Suspended'=>'N'), true, $DBConn);

      // var_dump($projects);


   }

} else {
   $projects = array();
}

// var_dump($projects);
$teamMembers = array();

if($projects) {
   foreach ($projects as $project) {?>
   <div class="card card-body">
      <h3> <?php echo $project->projectName; ?></h3>
      <?php
      // Project team members here
      $allProjectTasks = Projects::projects_tasks(array('projectID' => $project->projectID), false, $DBConn);
      // var_dump($projectTasks);

      $projectAssignedTasks = Projects::task_user_assignment(array("projectID" => $project->projectID), false, $DBConn);


      if($projectAssignedTasks) {
         foreach ($projectAssignedTasks as $task) {
            if (!in_array($task->userID, $teamMembers)) {
               $teamMembers[] = $task->userID;
            }
         }
      }
      // var_dump($teamMembers);
      $subtasks=Projects::project_subtasks_full(array('projectID'=>$project->projectID), false, $DBConn);
      // var_dump($subtasks);
      $subTaskMembers = array();
      if($subtasks) {
         foreach ($subtasks as $task) {
            if (!in_array($task->assignee, $teamMembers)) {
               $teamMembers[] = (int)$task->assignee;
            }
         }
      }
      // var_dump($subTaskMembers);
      array_merge($teamMembers, $subTaskMembers);
      $teamMembers = array_unique($teamMembers);
      // var_dump($teamMembers);

      // Team Tasks
      if($teamMembers) {
         foreach ($teamMembers as $key => $team) {
            $team = Employee::employees(array('ID'=>$team, 'Suspended'=>'N'), true, $DBConn);
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
                        $subtaskCompletedArr = TimeAttendance::project_tasks_time_logs(['projectTaskID'=>$activity->projectTaskID, 'taskDate'=>$today, 'employeeID'=>$team->ID, 'subtaskID'=> $activity->subtaskID, 'dailyComplete'=>'Y' ], false, $DBConn);
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
                                       <span class="avatar bd-blue-800 avatar-xs me-2 avatar-rounded float-start">
                                          <AC>
                                             <?= Utility::generate_initials($task->projectTaskName); ?>
                                          </AC>
                                       </span>
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
                                    if(isset($subtasks) && !empty($subtasks)) {
                                       // var_dump($subtasks);
                                       $subtaskCompletedCount = 0;
                                       foreach ($subtasks as $key => $subtask) {
                                          $subtaskCompletedCount++;
                                       }
                                       // var_dump($subtaskCompletedCount);
                                       // var_dump($completedSubTasksArray);
                                       // var_dump($subtaskCompletedCount);
                                       // var_dump($completedSubTasksArray);
                                       // var_dump($subtasks);
                                       // $subtaskCompletedCount = count($completedSubTasksArray);
                                       // $completedSubTasksArray = array();
                                       // $completedSubTasksArray = array_unique($completedSubTasksArray);
                                    //   $subCount = is_array($subtasks) ? count($subTasks) : 0;
                                    //    // $subtaskCompletedCount = count($completedSubTasksArray);
                                    //    if( $subCount == $subtaskCompletedCount) {
                                    //       $completedTasksCount++;
                                    //       $totalCompletedSubtasks += $subtaskCompletedCount;
                                    //    }
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

      }  ?>
   </div>
   <?php


   }
} else{
   Alert::info("No Team Members Found", true,    array('fst-italic', 'text-center', 'font-18', 'mt-5'));
}
