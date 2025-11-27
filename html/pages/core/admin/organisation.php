<?php 
if($isAdmin || $isValidAdmin) {
    $state = isset($_GET['state']) ? Utility::clean_string($_GET['state']) : 'home'; 
    $orgDataID = isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : '';
    $entityID = isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : ''; 
    $organisations = Admin::organisation_data_mini([], false, $DBConn);
    $entities = Data::entities([], false, $DBConn);
    $employeeDetails = Employee::employees(array('ID'=>$userDetails->ID), true, $DBConn);
    $orgDataID= isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : 
                (isset($employeeDetails->orgDataID) && !empty($employeeDetails->orgDataID) ? $employeeDetails->orgDataID : 
                    ((isset($_SESSION['orgDataID']) && !empty($_SESSION['orgDataID'])) ? $_SESSION['orgDataID'] : "")
         );

    $entityID= isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : 
            (isset($employeeDetails->entityID) && !empty($employeeDetails->entityID) ? $employeeDetails->entityID :
            ( isset($_SESSION['entityID']) ? $_SESSION['entityID'] : ''));

   if(!$orgDataID ) {
      Alert::info("You need to select an organisation and entity to view clients", true, array('fst-italic', 'text-center', 'font-18'));?>
      <div class="col-6 mx-auto">         
         <div class="card custom-card">
            <div class="card-header jsustify-content-between">
               <h4 class="card-title">Select Organisation and Entity</h4>
            </div>
            <div class="card-body">                        
               <div class="list-group list-group-flush"> 
                  <?php foreach ($organisations as $org) { ?>
                     <div class="list-group-item list-group-item-action">
                        <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p={$p}&orgDataID={$org->orgDataID}" ?>">
                        <?php echo $org->orgName; ?>
                        </a>
                     </div>
                  <?php } ?>
               </div> 
            </div>              
         </div>
      </div>
      <?php
      return;
   } else if(!$entityID) {
      $entities = Data::entities(array('orgDataID'=>$orgDataID), false, $DBConn); 
      Alert::info("You need to select an entity to view clients", true, array('fst-italic', 'text-center', 'font-18'));?>
         <div class="col-6 mx-auto">
            <div class="card custom-card">
               <div class="card-header justify-content-between">
                  <h4 class="card-title">Select Entity</h4>
               </div>
               <div class="card-body">
                  <div class="list-group list-group-flush">
                     <?php foreach ($entities as $entity) { ?>
                        <div class="list-group-item list-group-item-action">
                           <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p={$p}&orgDataID={$orgDataID}&entityID={$entity->entityID}" ?>">
                           <?php echo $entity->entityName; ?>
                           </a>
                        
                        </div>
                     <?php } ?>
                  </div>
               </div>
            </div>
         </div>            
      <?php
      return;
   }
    $getString .= "&orgdataID={$orgDataID}&entityID={$entityID}"; ?>

   <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb border-bottom">
      <h1 class="page-title fw-medium fs-24 mb-0">Tija Organisation</h1>
      <div class="ms-md-1 ms-0">
         <nav>
            <ol class="breadcrumb mb-0">
               <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $s ?></a></li>
               <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $ss ?></a></li>
               <li class="breadcrumb-item active d-inline-flex" aria-current="page">Organisation Profile</li>
            </ol>
         </nav>
      </div>
   </div>
   <?php 
   $menuItems = [
      ['title' => 'Organisation Details','state' => 'home', 'link' => 'company_details.php'],
      ['title' => 'Organisation Structure',  'state' => 'structure', 'link' => 'organisation_structure.php'],     
      ['title' => 'Chart(Reporting Hierarchy)', 'state' => 'chart', 'link' => 'organisation_chart.php'],
      ['title' => 'Users',  'state' => 'users', 'link' => 'users.php'],
      ['title' => 'Business Units', 'state' => 'business_units', 'link' => 'business_units.php']
   ];
      
   ?>
   <div class="col-12 bg-light-blue py-2 text-end ">    
      <?php foreach($menuItems as $menuItem): ?>
            <a href="<?= "{$base}html/{$getString}&state={$menuItem['state']}" ?>" class="btn <?php echo $state === $menuItem['state'] ? "btn-primary" : "btn-outline-dark"; ?> btn-sm rounded-pill btn-wave px-4 py-0">
               <?php echo $menuItem['title'] ?>
            </a>
      <?php endforeach; ?>
   </div>
   <?php 
   $pages = [
      'home' => 'company_details.php',
      'structure' => 'organisation_structure.php',      
      'chart' => 'organisation_chart.php',
      'users' => 'users.php',
      'business_units' => 'business_units.php',
   ];
   $page = isset($pages[$state]) ? $pages[$state] : $pages['home'];
   
   include_once("includes/core/admin/organisation/" . $page);
        
	$getString = str_replace("&state={$state}", "", $getString); 
   $getString .="&state={$state}";  
} else {
    Alert::info("You need to be logged in as a valid administrator to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
}?>  