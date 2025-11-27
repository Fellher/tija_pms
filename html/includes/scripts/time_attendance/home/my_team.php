<?php
if(!$isValidUser){
   Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
	include "includes/core/log_in_script.php";
   return;
}

// var_dump($entityID);

$employeeDetails = Employee::employees(array('ID'=>$userDetails->ID), true, $DBConn);
// var_dump($employeeDetails);

$entityID = $employeeDetails->entityID ?? null;

if(!isset($entityID) || empty($entityID)){
   Alert::info("You need to select an entity to access this page", true, array('fst-italic', 'text-center', 'font-18'));
   $allEntities = Data::entities(array('Suspended'=>'N'), false, $DBConn);
   // var_dump($allEntities);
   ?>
   <div class="container-fluid my-3">
      <div class="card custom-card">
         <div class="card-header justify-content-between">
            <div class="card-title">Select Entity</div>
         </div>
         <div class="card-body">
            <?php
            if($allEntities){

               foreach ($allEntities as $key => $entity) {
                  ?>
                  <a href="<?php echo "{$base}html/?{$getString}&uid={$userID}&entityID={$entity->entityID}"; ?>" class="btn btn-primary btn-sm rounded-pill px-4 py-0"><?php echo $entity->entityName; ?></a>
                  <?php
               }
            } else {
               Alert::info("No entities found", true, array('fst-italic', 'text-center', 'font-18'));
            }
            ?>
         </div>
      </div>
   </div>



<?php
}


 ?>
   <div class="container-fluid my-3">
      <div class="card custom-card">
         <div class="card-header justify-content-between">
            <div class="card-title">My Team</div>
         </div>
         <div class="card-body">
            <?php
            $teamCategoryLinksArray = array(
               (object)[
                  "title" => "Direct Reports",
                  "link" => "direct_reports",
                  "id" => "direct_reports",
                  "adminlevel" => 2
               ],
               (object)[
                  "title" => "By Projects",
                  "link" => "projects",
                  "id" => "projects",
                  "adminlevel" => 3
               ],
            );
            $status = isset($_GET['status']) ? Utility::clean_string($_GET['status']) : 'direct_reports';
            $nodeID = "my_team";

            // echo "<h5 class='text-center'>{$getString}</h5>";
            ?>
             <div class="container-fluid">
               <div class="col-12 bg-light-blue py-2 text-end  border-primary border-bottom border-2 px-3">
                  <?php
                  foreach ($teamCategoryLinksArray as $key => $link) {

                     $active= $status == $link->link ?  "btn-primary" : "btn-outline-dark";
                     ?>
                     <a class="btn  <?php echo  $active; ?> btn-sm rounded-pill btn-wave px-4 py-0" href="<?php echo "{$base}html/?{$getString}&status=".$link->link.'&uid='.$userID; ?>" >
                        <?php echo $link->title; ?>
                     </a>
                  <?php } ?>
               </div>
            </div>
            <?php

            if($status == 'direct_reports') {
               // $myTeam = TimeAttendance::get_my_team(['employeeID'=>$userID, 'Suspended'=>'N'], false, $DBConn);
               include "includes/scripts/time_attendance/home/my_team_direct_reports.php";
            } elseif ($status == 'projects') {
               include "includes/scripts/time_attendance/home/my_team_projects.php";
               // $myTeam = TimeAttendance::get_my_team(['employeeID'=>$userID, 'Suspended'=>'N'], false, $DBConn);
            } else {
               Alert::info("Invalid Team Category", true, array('fst-italic', 'text-center', 'font-18'));
            }


           ?>
         </div>

      </div>

   </div>
   <?php


   // var_dump($myTeamPositions);



?>