<div class="container-fluid my-3">
   <div class="card custom-card">
      <div class="card-header justify-content-between">
         <h4 class="card-title">Project Tasks</h4>

      </div>
      <div class="card-body">
            <?php
            // Check if filtering by billing cycle
            $taskFilters = array('projectID' => $projectID, 'Suspended' => 'N');
            if (isset($_GET['billingCycleID']) && !empty($_GET['billingCycleID'])) {
                $taskFilters['billingCycleID'] = intval($_GET['billingCycleID']);
            }

            $tasks = Projects::project_tasks($taskFilters, false, $DBConn);
            $subtasks=Projects::project_subtasks_full(array('projectID'=>$projectID), false, $DBConn);
            $projectAssignedTasks = Projects::task_user_assignment(array("projectID" => $projectID), false, $DBConn);
            $projectTeamRoles = Projects::project_team_roles(array(), false, $DBConn);
            $taskStatus = Projects::task_status(array('Suspended' => "N"), false, $DBConn);
            // var_dump($taskStatus);
            $taskStatus = array_column($taskStatus, 'taskStatusName', 'taskStatusID');
            // var_dump($taskStatus);
            $taskStatus = array_map(function($status) {
               return htmlspecialchars($status);
            }, $taskStatus);

            if($tasks) {?>
               <table class="table table-striped table-sm">
                  <thead>
                     <tr>
                        <th>Task</th>
                        <th>Assignee</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php
                     foreach ($tasks as $task) {
                        $assigneeArr = Projects::task_user_assignment(['projectTaskID'=>$task->projectTaskID], false, $DBConn);
                        $bgColor = "";

                        if($assigneeArr) {
                          foreach ($assigneeArr as $assignee) {
                             if($assignee->userID) {

                             }
                          }
                        } else {
                           $task->assignee = 0;
                        }
                        $task->taskDeadline;
                        if($task->taskDeadline <= date('Y-m-d') && $task->taskStatusID != 6) {
                           $bgColor = "bg-danger";
                        }

                        // var_dump($bgColor);

                        // var_dump($task);
                        // $userName = Core::user_name($task->assignee, $DBConn);
                        // $taskStatusName = $taskStatus[$task->taskStatusID]; ?>
                        <tr class="task-row <?= $bgColor ?>" >
                           <td>
                               <?php echo htmlspecialchars($task->projectTaskName); ?>
                               <?php if (isset($task->billingCycleID) && $task->billingCycleID): ?>
                                   <?php
                                   $taskCycle = Projects::get_billing_cycles(['billingCycleID' => $task->billingCycleID], true, $DBConn);
                                   if ($taskCycle):
                                   ?>
                                       <span class="badge bg-info ms-2" title="Billing Cycle #<?= $taskCycle->cycleNumber ?>">
                                           <i class="ri-repeat-line"></i> Cycle #<?= $taskCycle->cycleNumber ?>
                                       </span>
                                   <?php endif; ?>
                               <?php endif; ?>
                           </td>
                           <td><?php //echo htmlspecialchars($userName);
                              // var_dump($assigneeArr);
                              if ($assigneeArr) {
                                 for ($i=0; $i <count($assigneeArr) ; $i++) {
                                    $value= $assigneeArr[$i];
                                    if ($value->taskUser) {
                                       $FnameArray= str_split(explode(" ", $value->taskUser)[0]);
                                             $sNameArr= str_split(explode(" ", $value->taskUser)[1]);
                                    }?>
                                    <a href="#" class="py-0 " data-bs-toggle="tooltip" data-bs-html="true" title="<em><u><?php echo $value->taskUser ?></u></em> ">
                                       <span class="border border-warning rounded-circle p-1 font-12 mr-3 text-uppercase"><?php echo $FnameArray[0].  $sNameArr[0]  ?></span>
                                    </a>
                                    <?php
                                 }
                              }
                              ?>
                           </td>
                           <td><?php echo htmlspecialchars($task->taskStart); ?></td>
                           <td><?php echo htmlspecialchars($task->taskDeadline); ?></td>
                           <td><?php echo htmlspecialchars($task->taskStatusName); ?></td>
                           <td><button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#manageTask" data-id="<?php echo $task->projectTaskID; ?>">Edit</button></td>
                        </tr>
                     <?php } ?>
                  </tbody>
               </table>
               <?php
            }


            ?>
      </div>
   </div>
</div>