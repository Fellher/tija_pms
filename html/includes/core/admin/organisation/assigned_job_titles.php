<div class="col-12 ">
   <div class="card custom-card border-0">      
      <div class="card-header d-flex justify-content-between align-items-center">
         <h5 class="card-title mb-0 "><i class="ri ri-shield-user-line me-2"></i>Manage Assigned Role Positions </h5>
         <div class="d-flex flex-wrap gap-2">
            <a href="javascript:void(0);" class="btn btn-primary btn-sm btn-wave waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#manage_role_position">
               <i class="ri ri-add-line align-bottom me-1"></i> Add Role Position
            </a>
            <div class="me-3">
               <input class="form-control form-control-sm" type="text" placeholder="Search Here" aria-label=".form-control-sm example">
            </div>
            <div class="dropdown">
               <a href="javascript:void(0);" class="btn btn-primary btn-sm btn-wave waves-effect waves-light" data-bs-toggle="dropdown" aria-expanded="false">
                     Sort By<i class="ri-arrow-down-s-line align-middle ms-1 d-inline-block"></i>
               </a>
               <ul class="dropdown-menu" role="menu">
                     <li><a class="dropdown-item" href="javascript:void(0);">New</a></li>
                     <li><a class="dropdown-item" href="javascript:void(0);">Popular</a></li>
                     <li><a class="dropdown-item" href="javascript:void(0);">Relevant</a></li>
               </ul>
            </div>                   
         </div>                    
   </div>

   <?php      
   $orgDataID= isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : $employeeDetails->orgDataID;
   $entityID= isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : $employeeDetails->entityID;

   if(!$orgDataID || !$entityID) {
      Alert::error("No Organisation or Entity ID found. Please select an organisation and entity to view the assigned roles.", true, array('fst-italic', 'text-center', 'font-18'));
      ?>
      <div class="card card-body">
         <div class="row">
            <div class="col-12">
               <?php

               // var_dump($getString);
               if(!$orgDataID){

              
                  $orgs = Admin::organisation_data_mini([], false, $DBConn);
                  if($orgs) {
                     foreach ($orgs as $org) {?>                    
                        <a href="<?php echo "{$base}html/{$getString}&orgDataID={$org->orgDataID}" ?>" class="btn btn-sm btn-primary rounded px-3 mx-2">
                        <?php echo $org->orgName; ?>
                        </a>
                        <?php

                        # code...
                     }
                  }
               }  else {
                  if(!$entityID){
                     $entities = Data::entities(['orgDataID'=> $orgDataID], false, $DBConn);

                     var_dump($entities);
                  }

               }
               
               
               ?>
               
            </div>
         </div>
      <?php
   }

   $jobTitles = Admin::tija_job_titles([], false, $DBConn);         
   $assignedRoles = Employee::organisation_roles([], false, $DBConn);

   if($assignedRoles) {
      $assignedRoles = array_map(function($role) use ($DBConn) {
         $role->jobTitle = Employee::get_job_title($role->jobTitleID, $DBConn);
         return $role;
      }, $assignedRoles);
   } else {
      Alert::info("No Assigned Roles Found. Please add a role to the organisation to view it here.", true, array('fst-italic', 'text-center', 'font-18'));
    
   }

   echo Utility::form_modal_header("manage_role_position", "employees/manage_role_position.php", "Manage Job Role Assignment", array('modal-md', 'modal-dialog-centered'), $base); 
      include "includes/scripts/employee/modals/manage_role_position.php";
   echo Utility::form_modal_footer('Upload Role Assignment', 'manage_role_position_assignment',  ' btn btn-success btn-sm', true);

   ?>



</div>

         