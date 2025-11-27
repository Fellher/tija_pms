<?php
// This is a new sub-menu reporting file for 'projects_tasks_activities.php'
// Add your content here.
$projects = Projects::projects_full([], false, $DBConn);
//active projects are projects whoose deadline is yet to be reached or status is active
$activeProjects = [];
foreach ($projects as $project) {
   if ($project->projectDeadline > date('Y-m-d') || $project->projectStatus != 'closed') {
      $activeProjects[] = $project;
   }
}

// var_dump($activeProjects);
$tasks=[];
if($activeProjects){
   foreach ($activeProjects as $project) {
      // var_dump($project);
      $projectTasks = Projects::project_tasks_full(['projectID'=>$project->projectID], false, $DBConn);
      // var_dump($projectTasks);
      if($projectTasks){
         $tasks = array_merge($tasks, $projectTasks);
      }
   }
}

// var_dump($tasks);

if($tasks){?>

   <table class="table table-striped table-sm">
      <thead>
         <tr>
            <th>Task</th>
            <th>Phase</th>
            <th>Project</th>
            <th>Client</th>
            <th>Assignees</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>hours worked</th>
            <th>hours allocated</th>
            <th>progress</th>
            <th>status</th>
            <th>Value of hrs worked</th>
            <th>Value of hrs allocated</th>
            <th>Value of hrs billed</th>
            <th>Value of hrs unbilled</th>
            <th>Actions</th>
         </tr>
      </thead>
      <tbody>
   <?php
   //var_dump($tasks[0]);
   foreach ($tasks as $task) {
      // var_dump($task);
      $taskAssignees = Projects::task_user_assignment(['projectTaskID'=>$task->projectTaskID], false, $DBConn);
      // var_dump($taskAssignees);
      $hoursWorked = 0;
      $hoursAllocated = 0;
      $progress = 0;
      $status = $task->status;
      $valueOfHrsWorked = 0;
      $valueOfHrsAllocated = 0;
      $valueOfHrsBilled = 0;
      $valueOfHrsUnbilled = 0;
      $actions = '';
      $assigneesNames = '';
      if($taskAssignees){
         foreach($taskAssignees as $assignee){
          $assigneesNames.= "<button class='btn btn-sm btn-icon rounded-pill btn-primary-light' data-bs-toggle='tooltip' data-bs-html='true' title='<em><u>{$assignee->assigneeName}</u></em> '>{$assignee->userInitials[0]}</button> ";
         }
      }?>
      <tr>
         <td> <?= $task->projectTaskName ?></td>
         <td><?= $task->projectPhaseName ?></td>
         <td><?= $task->projectName ?></td>
         <td><?= $task->clientName ?></td>
         <td><?= $assigneesNames ?></td>
         <td><?= $task->status ?></td>
         <td><?= $task->taskStart ?></td>
         <td><?= $task->taskDeadline ?></td>
         <td><?= $hoursWorked ?></td>
         <td><?= $hoursAllocated ?></td>
         <td><?= $progress ?></td>
         <td><?= $status ?></td>
         <td><?= $valueOfHrsWorked ?></td>
         <td><?= $valueOfHrsAllocated ?></td>
         <td><?= $valueOfHrsBilled ?></td>
         <td><?= $valueOfHrsUnbilled ?></td>
         <td><?= $actions ?></td>
      </tr>
   <?php
      // echo "<tr>";
      // echo "<td>{$task->projectTaskCode}</td>";
      // echo "<td>{$task->projectTaskName}</td>";
      // echo "<td>{$task->projectTaskDescription}</td>";
      // echo "<td>{$task->projectTaskStatus}</td>";
      // echo "<td>{$task->taskStart}</td>";
      // echo "<td>{$task->taskDeadline}</td>";
      // echo "<td>{$task->projectTaskStartDate}</td>";
      // echo "<td>{$task->projectTaskEndDate}</td>";
      // echo "<td>{$task->projectTaskAssignee}</td>";
      // echo "<td>{$task->projectTaskAssigneeEmail}</td>";
      // echo "<td>{$task->projectTaskAssigneePhone}</td>";
      // echo "<td>{$task->projectTaskAssigneeAddress}</td>";
      // echo "</tr>";
   }?>
   </tbody>
   </table>
<?php
}
?>