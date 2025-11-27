<?php
// This is a new sub-menu reporting file for 'projects_phases_milestones.php'
// Add your content here.

$projects = Projects::projects_full([], false, $DBConn);

//active projects are projects whoose deadline is yet to be reached or status is active
$activeProjects = [];
foreach ($projects as $project) {
   if ($project->projectDeadline > date('Y-m-d') || $project->projectStatus == 'active') {
      $activeProjects[] = $project;
   }
}

var_dump($activeProjects);

if($activeProjects){
   foreach ($activeProjects as $project) {
      var_dump($project);
   }
}
?>