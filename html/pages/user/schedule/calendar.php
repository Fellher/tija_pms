<?php 
if(!$isValidUser) {
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}

if (isset($_GET['d']) && !empty($_GET['d']) && preg_match($config['ISODateFormat'], $_GET['d'])) {
   $DOF= Utility::clean_string($_GET['d']);
   $dt=date_create($DOF);
} else {	
   $year = (isset($_GET['year']) && !empty($_GET['year'])) ? Utility::clean_string($_GET['year']) : $dt->format('o');
   $week = (isset($_GET['week']) && !empty($_GET['week'])) ? Utility::clean_string($_GET['week']) : $dt->format('W');
   $month = (isset($_GET['month']) && !empty($_GET['month'])) ? Utility::clean_string($_GET['month']) : $dt->format('m');	

   if($year && $week){
      $dt = new DateTime();
      $dt->setISODate($year, $week);
   } elseif($year && $month) {

      $dt = new DateTime();
   //  $dt->setISODate($year, $week);
      $dt->setDate($year, $month, 1);
   }    
}

$DOF= $dt->format('Y-m-d');

$getString .="&d={$DOF}";
$year = (isset($year) && !empty($year))? $year : $dt->format('o');
$week = (isset($week) && !empty($week)) ? $week : $dt->format('W');
$month = (isset($month) && !empty($month) ) ?$month : $dt->format('m') ;


// var_dump($getString);
// var_dump($userDetails);
$employeeID=(isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) : $userDetails->ID;
$employeeDetails = Data::users(array('ID'=>$employeeID), true, $DBConn);

$allEmployees = Data::users([], false, $DBConn);

$orgDataID= isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : $employeeDetails->orgDataID;
$entityID= isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : $employeeDetails->entityID;
$clients = Client::clients(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
$businessUnits = Data::business_units(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
$allOrgs = Admin::organisation_data_mini([], false, $DBConn); 
$projects = Projects::projects_full([], false, $DBConn);
$salesCases = Sales::sales_case_full([], false, $DBConn);
$activityCategories = Schedule::activity_categories([], false, $DBConn);
$activityTypes = Schedule::activity_types([], false, $DBConn);
// var_dump($activityTypes);

$allActivities = Schedule::tija_activities(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <h1 class="page-title fw-medium fs-24 mb-0">Calendar</h1>
    <div class="ms-md-1 ms-0">
         <button type="button" class="btn btn-primary-light shadow btn-sm px-4 addNewActivity" data-bs-toggle="modal" data-bs-target="#manage_activity">
            <i class="ri-add-line"></i>
           Add Calendar Entry 
        </button>
       
    </div>
</div>
<?php 
// var_dump($dt);
/*===================================
DATES FOR THE WEEK FOR CURRENT WEEK(Week Starts on Monday)
==========================================================*/
$weekArray = Utility::week_array($year, $week);

// var_dump($weekArray);
?>
<div class="row">
   <div class="col-md-12 col-lg-8 col-xl-8">
      <div class="card custom-card mb-4">
         <div class="card-body">
            <div class="row">
               <div class="col-md-8"><h3 class="t300 my-0 text-dark"><?php echo $dt->format('l j\/n') ?></h3></div>
               <div class="col-md-4">
                  <div class=" m-0">
                     <a  class="btn btn-link m-0 py-0" href="<?php echo $base ."html/?s={$s}&ss={$ss}&p={$p}&week=".($week-1).'&year='.$year.'&uid='.$userID; ?>">
                        <i class="fa-solid fa-circle-chevron-left"></i></a> <!--Previous week-->
                     <span>Week <?php echo $dt->format('W') ?></span>
                     <a class="btn btn-link m-0 py-0" href="<?php echo $base ."html/?s={$s}&ss={$ss}&p={$p}&week=".($week+1).'&year='.$year.'&uid='.$userID;?>"><i class="fa-solid fa-circle-chevron-right"></i></a> <!--Next week-->
                     <a href="<?php echo $base ."html/?s={$s}&ss={$ss}&p={$p}&week=".($week+1).'&year='.$year.'&uid='.$userID;?>" class="btn btn-white border"> Today</a>
                  </div>
               </div>
            </div>             
            <div id="calendar"></div> 
         </div>
      </div>
   </div>
   <div class="col-md-12 col-lg-4 col-xl-4">
      <div class="card custom-card">
         <div class="card-body">
            <div class="row">
               <div class="col-md-12">
                  <h3 class="titular-title t300  font-primary font-26t300 my-0 text-dark ">Planned Activities  
                     <span class= 'float-end'> 
                        <!-- <button class="btn  btn-icon rounded-pill btn-primary-light">
                           <i class="ti ti-plus"></i>
                        </button>  -->
                        <div class="dropdown">
                           <a aria-label="anchor" href="javascript:void(0);" class="btn  btn-icon rounded-pill btn-primary-light" data-bs-toggle="dropdown" aria-expanded="false">
                           <i class="ti ti-plus" data-bs-toggle="tooltip" data-bs-placement="top" title="Add To Do"></i>
                           </a>
                           <form class="dropdown-menu p-3 shadow-md" action= "<?= "{$base}php/scripts/schedule/manage_activity_mini.php" ?>" method="POST" style="min-width:300px; position: absolute; inset: 0px auto auto 0px; margin: 0px; transform: translate(0px, 30px);" data-popper-placement="bottom-start">
                              <div class="form-group mb-2">
                                 <input type="hidden" name="orgDataID" id="orgDataID" value="<?php echo $orgDataID; ?>">
                                 <input type="hidden" name="entityID" id="entityID" value="<?php echo $entityID; ?>">
                                 <input type="hidden" name="userID" id="userID" value="<?php echo $employeeID; ?>">
                                 <input type="hidden" name='activityCategoryID' id="activityCategory" value="1">                              
                                 <label for="activityName" class="fs-12 text-primary"> Activity Name</label>
                                 <input type="text" class="form-control-xs form-control-plaintext border-bottom bg-light" id="activityName" name="activityName" placeholder="Activity Name">
                              </div>
                              <div class="form-group mb-2">
                                 <label for="activityType" class="fs-12 text-primary"> Activity Type</label>
                                 <select name="activityTypeID" id="activityTypeID" class="form-control-xs form-control-plaintext border-bottom" >
                                    <option value="">Select Activity Type</option>
                                    <?php foreach ($activityTypes as $key => $activityType) { ?>
                                       <option value="<?php echo $activityType->activityTypeID; ?>"><?php echo $activityType->activityTypeName; ?></option>
                                    <?php } ?>
                                 </select>
                              </div>
                              <div class="form-group mb-2">
                                 <label for="activityDate" class="fs-12 text-primary"> Activity Date</label>
                                 <input type="text" class="form-control-xs form-control-plaintext border-bottom bg-light date" id="activityDate" name="activityDate" value="<?php echo $dt->format('Y-m-d'); ?>" placeholder="Activity Date">
                                    
                              </div>
                              <div class="dropdown-footer bg-light border-top ">
                                 <button type="submit" class="btn  btn-success btn-xs float-end py-1 px-3 mt-2" id="addActivity">Add</button>
                              </div>
                           </form>
                     </div>
                     </span>
                  </h3>
               </div>
               <div class="col-12">
                  <?php
                  $myActivities = Schedule::tija_activities(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID, 'activityOwnerID'=>$employeeID), false, $DBConn);
                

                  if($myActivities){
                     foreach($myActivities as $activity){?>

                        <div class="alert alert-primary alert-dismissible fade show custom-alert-icon shadow-sm" role="alert">

                        <div class="d-block">
                           <div class="d-flex justify-content-between align-items-center">
                              <div class="">
                                 <i class="bi bi-calendar me-2"></i>
                                 <strong><?php echo $activity->activityName; ?></strong><br>
                                 <?php echo $activity->activityDate; ?> | <?= $activity->activityStartTime ?>  <?=  $activity->activityDurationEndTime ? "- {$activity->activityDurationEndTime}" : ""; ?>
                                 </div>
                              <div class="d-flex align-items-center justify-content-end">
                                
                                    <button type="button" class="btn btn-primary-light btn-sm rounded-circle me-2" data-bs-toggle="modal" data-bs-target="#postponeModal<?= $activity->activityID; ?>">
                                       <i class="ti ti-clock" data-bs-toggle="tooltip" data-bs-placement="top" title="Postpone "></i> 
                                    </button>
                                    <button type="button" class="btn btn-warning-light btn-sm btn-sm rounded-circle me-2" data-bs-toggle="modal" data-bs-target="#editModal<?= $activity->activityID; ?>">
                                       <i class="ti ti-edit" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-success-light btn-sm rounded-circle btn-sm" data-bs-toggle="modal" data-bs-target="#completeModal<?= $activity->activityID; ?>">
                                       <i class="ti ti-check" data-bs-toggle="tooltip" data-bs-placement="top" title="Complete"></i> 
                                    </button>
                                 
                              </div>

                           </div>
                          

                          
                           
                           
                        </div>

                        <?= $activity->projectID ? "<span class='d-block text-primary'> Project: {$activity->projectName} </span>" : ""; ?>
                        <?= $activity->salesCaseID ? "<span class='d-block text-primary'> Sales Case: {$activity->salesCaseName} </span>" : ""; ?>
                        <?= $activity->clientID ? "<span class='d-block text-primary'> Client: {$activity->clientName} </span>" : ""; ?>
                        <?php

                        // if($activity->salesCaseID){
                        //    $salesCase = Sales::sales_case_mid(array('salesCaseID'=>$activity->salesCaseID), true, $DBConn);
                        //    if($salesCase){
                        //       echo "Sales Case: {$salesCase->salesCaseName}";
                        //    }
                        // }
                        // if($activity->projectID){
                        //    $project = Projects::projects_full(array('projectID'=>$activity->projectID), true, $DBConn);
                        //    if($project){
                        //       echo "Project: {$project->projectName}";
                        //    }
                        // }
                        // if($activity->clientID){
                        //    $client = Client::clients_full(array('clientID'=>$activity->clientID), true, $DBConn);
                        //    if($client){
                        //       echo "Client: {$client->clientName}";
                        //    }
                        // }
                        ?>
                          
                          
                           
                        </div>

                        <?php
                     }
                     // var_dump($myActivities);
                  }

                  ?>
               </div>
            </div>
         </div>

      </div>
      
   </div>


</div>
