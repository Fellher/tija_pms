<?php
// This is a new sub-menu reporting file for 'projects_cases.php'
// Add your content here.

$projects = Projects::projects_full([], false, $DBConn);
// var_dump($projects);

if($projects){
   // var_dump($projects[0]);
   ?>

   <div class="table-responsive">
      <table class="table table-bordered table-xs table-striped" id="dataTable">
         <thead >
            <tr class="table-th-info">
               <th>&nbsp;</th>
               <th>Progress</th>
               <th>Project Name</th>
               <th>client</th>
               <th>Project value</th>
               <th>Work hours worked</th>
               <th>Estimated work hours</th>
               <th>Deadline</th>             
               <th>Billed value</th>
               <th>Gross profit</th>
               <th>Gross profit percentage</th>
               <th>Project Status</th>
               <th>Project Duration</th>
               <th>Project Owner</th>
            </tr>
         </thead>
         <tbody>
            <?php
            $k=0;
            foreach ($projects as $project) {
               $k++;
               // var_dump($project);
               $projectWorkHoursWorked  = 0;
               $projectAllocatedWorkHours = 0;
               $projectBilled = 0;
               $projectBilledValue = 0;
               $projectGrossProfit = 0;
               $projectGrossProfitPercentage = 0;
               $projectStatus = $project->projectStatus;
               $projectDuration = 0;
               $projectOwner = $project->projectOwnerName;
               $projectProgress = rand(20, 54);
               $projectStatus = $project->projectStatus ? $project->projectStatus : ((int)$projectWorkHoursWorked > 0 ? 'In Progress' : 'Not Started');
               $projectDeadline = $project->projectClose ? Utility::date_format($project->projectClose,'british') : 'N/A';
               // var_dump($projectStatus);
               echo "<tr>";
               echo "<td>{$k}</td>";
               echo "<td>
                  <div class='progress ' role='progressbar'  aria-valuenow='{$projectProgress}' aria-valuemin='0' aria-valuemax='100'>
        <div class='progress-bar bg-secondary' style='width: {$projectProgress}%;'>{$projectProgress}%</div>
    </div>
               </td>";
               echo "<td>{$project->projectCode} - {$project->projectName}</td>";
               echo "<td>{$project->clientName}</td>";
               echo "<td class='text-end'>". number_format($project->projectValue, 0, '.', ',') ."</td>";
               echo "<td class='text-center text-muted'>{$projectWorkHoursWorked}</td>";
               echo "<td class='text-center text-muted'>{$projectAllocatedWorkHours}</td>";               
               echo "<td class='text-center text-muted'>{$projectDeadline}</td>";
               echo "<td class='text-end'>{$projectBilledValue}</td>";
               echo "<td class='text-end text-muted'>{$projectGrossProfit}</td>";
               echo "<td>{$projectGrossProfitPercentage}</td>";
               echo "<td>{$projectStatus}</td>";
               echo "<td>{$projectDuration}</td>";
               echo "<td>{$project->projectOwnerName}</td>";
               echo "</tr>";
            }?>
         </tbody>
      </table>
   </div>
   <?php
}
?>
