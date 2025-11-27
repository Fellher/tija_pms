<?php 
if(!$isAdmin) {
  Alert::info("You do not have permission to view this page", true, array('fst-italic', 'text-center', 'font-18'));
  exit();
}
$organisations = Admin::organisation_data_mini([], false, $DBConn);
// var_dump($organisations);

$allEmployees = Data::users([], false, $DBConn);

$orgDataID = isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : "";
$entityID= isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : "";?>
<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb border-bottom">
    <h1 class="page-title fw-medium fs-24 mb-0">Sales Status Levels</h1>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                <li class="breadcrumb-item active d-inline-flex" aria-current="page">Company Details</li>
            </ol>
        </nav>
    </div>
</div>
<div class="container">
   <div class="card card-body shadow-lg">
      <h3 class="border-bottom">Sales Statuses</h3>
      <p> 
      Sales statuses are the key milestones in your sales process, and they define the sales pipeline. They differ by company but typically you want to report them, for example as the amount of new leads or the value of offers sent. When a sales case progresses through different statuses, the probability to close the deal should increase accordingly. When the deal is closed and sales status set as won, the sales case becomes a project.
      </p>

   </div>
</div>


<?php 
if($orgDataID && $entityID) {
   $getString .= "&orgDataID={$orgDataID}&entityID={$entityID}";
   $orgDetails = Admin::org_data(array("orgDataID"=>$orgDataID), true, $DBConn);
   $entityDetails = Data::entities(array("entityID"=>$entityID), true, $DBConn);?>
   <div class="card custom-card">
      <div class="card-header justify-content-between ">
         <h3 class="card-title"><?= $entityDetails->entityName ?> status Levels</h3>
         <div class="ms-md-1 ms-0">
            <button type="button" class="btn btn-primary-light shadow btn-sm px-4" data-bs-toggle="modal" data-bs-target="#manageStatus">
            <i class="ri-add-line"></i>
            Add Status</button>
         </div>
      </div>
      <?php 
       $statusLevels = Data::sales_status_levels(['orgDataID'=>$orgDataID, 'entityID'=>$entityID], false, $DBConn);
      echo Utility::form_modal_header("manageStatus", "sales/manage_status_level.php", "Manage Status", array('modal-md', 'modal-dialog-centered'), $base); 
         include "includes/scripts/sales/modals/manage_status_levels.php";
      echo Utility::form_modal_footer('Save Status', 'saveStatus',  ' btn btn-success btn-sm', true);
      ?>
      <div class="card-body">
         <div class="table-responsive">
            <table class="table table-hover table-borderless table-striped table-sm table-vcenter text-nowrap mb-0">
                  <thead>
                     <tr>
                        <th class="">Status Name</th>
                        <th class="">Status Description</th>
                        <th class="">Status %</th>
                        <th class="">Status Order</th>
                        <th class="">Actions</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php
                    
 
                     // var_dump($statusLevels);
                     if($statusLevels) {
                        foreach ($statusLevels as $statusLevel) {   
                           // var_dump($statusLevel);         
                              ?>
                              <tr>
                                 <td >
                                    <?= $statusLevel->statusLevel ?>
                                 </td>
                                 <td style="word-wrap: break-all; max-width: 200px;">
                                    
                                    <p class="text-wrap fst-italic">
                                       <?= Utility::clean_string($statusLevel->StatusLevelDescription) ?>
                                    </p>
                                  
                                 </td>
                                 <!-- <td class="text-center">
                                    <span class="badge bg-<?= $statusLevel->statusColor ?>"><?= $statusLevel->statusColor ?></span>
                                 </td> -->

                                 <td> 
                                 <?= $statusLevel->levelPercentage ?>%
                                    <div class="progress">
                                       <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $statusLevel->levelPercentage ?>%" aria-valuenow="<?= (int)$statusLevel->levelPercentage ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                       
                                    </div>
                                 <td class="text-center">
                                    <?= $statusLevel->statusOrder ?>
                                 </td>
                                 <td class="text-center">
                                    <a href="javascript:void(0);" class="btn btn-sm btn-primary-light shadow-sm editStatus"  data-bs-toggle="modal" data-bs-target="#manageStatus" 
                                    data-sales-status-level-id="<?= $statusLevel->saleStatusLevelID ?>"
                                    data-status-level="<?= $statusLevel->statusLevel ?>" 
                                    data-status-level-description="<?= $statusLevel->StatusLevelDescription ?>" 
                                    data-level-percentage= "<?= $statusLevel->levelPercentage ?>"
                                    data-status-order="<?= $statusLevel->statusOrder ?>"
                                    data-previous-level="<?= $statusLevel->previousLevelID ?>"
                                    >
                                          <i class="ri-pencil-line"></i>
                                    </a>
                                 </td>
                              </tr>
                              <?php
                        }
                     } else {
                           ?>
                           <tr>
                                 <td colspan="5" class="text-center">No status levels found</td>
                           </tr>
                           <?php
                     }
                     ?>
                  </tbody>
            </table>
         </div>
      </div>
      <script>
      document.addEventListener('DOMContentLoaded', function() {
          // Listen for modal show event
          document.querySelectorAll('.editStatus').forEach(button => {
              button.addEventListener('click', function() {
                  // Get the data attributes from the button
                  const saleStatusLevelID = this.getAttribute('data-sales-status-level-id');
                  const statusLevel = this.getAttribute('data-status-level');
                  const StatusLevelDescription = this.getAttribute('data-status-level-description');
                  const levelPercentage = this.getAttribute('data-level-percentage');
                  const statusOrder = this.getAttribute('data-status-order');
                  const previousLevel = this.getAttribute('data-previous-level');

                  // Find the modal form elements and set their values
                  const form = document.getElementById('statusLevelForm');
                  if (!form) return;

                  const saleStatusLevelIDInput = form.querySelector('input[name="saleStatusLevelID"]');
                  const statusLevelInput = form.querySelector('input[name="statusLevel"]');
                  const StatusLevelDescriptionInput = form.querySelector('textarea[name="StatusLevelDescription"]');
                  const levelPercentageInput = form.querySelector('input[name="levelPercentage"]');
                  const statusOrderInput = form.querySelector('input[name="statusOrder"]');
                  const previousLevelSelect = form.querySelector('select[name="previousLevel"]');

                  if (saleStatusLevelIDInput) {
                      saleStatusLevelIDInput.value = saleStatusLevelID;
                  }
                  if (statusLevelInput) {
                      statusLevelInput.value = statusLevel;
                  }
                  if (StatusLevelDescriptionInput) {
                      StatusLevelDescriptionInput.value = StatusLevelDescription;
                  }
                  if (levelPercentageInput) {
                      levelPercentageInput.value = levelPercentage;
                  }
                  if (statusOrderInput) {
                      statusOrderInput.value = statusOrder;
                  }
                  if (previousLevelSelect) {
                      previousLevelSelect.value = previousLevel;
                  }
              });
          });
      });
      </script>
   </div>
<?php

} else {
   if($organisations) {?>

      <div class="card custom-card">
         <div class="card-header justify-content-between ">
            <h3 class="card-title">Organisation</h3>
         </div>
         <div class="card-body">
            <div class="list-group list-group-flush">            
               <?php
               foreach ($organisations as $organisation) {?>
                  <div class=" card card-body shadow-lg ">                    
                     <div class="ms-2">
                        <h5 class="mb-0"><?= $organisation->orgName ?></h5>
                        <?php
                        $entities = Data::entities(array('orgDataID'=>$organisation->orgDataID), false, $DBConn);
                        if($entities) {
                           foreach ($entities as $entity) {
                              ?>
                              <div class="list-group-item list-group-item-action">
                                 <div class=" d-flex justify-content-between">
                                    <div class="ms-2">
                                       <h5 class=" font-18 mb-0"><?= $entity->entityName ?></h5>
                                    </div>
                                    <div class="ms-2">
                                       <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=manage_status&orgDataID={$organisation->orgDataID}&entityID={$entity->entityID}" ?>" class="btn btn-primary-light shadow-sm btn-sm">
                                          <i class="ri-pencil-line"></i>
                                          Manage Status
                                          </a>
                                    </div>
                                 </div>
                              </div>
                              <?php
                           }
                        } else {
                           Alert::info("No entity details found", true, array('fst-italic', 'text-center', 'font-18'));
                        }   ?>
                     </div>
                      
                     </div>
                     <?php
                  
               }?>
            </div>
         </div>
      </div>
   <?php
   } else {
      Alert::info("No organisation details found", true, array('fst-italic', 'text-center', 'font-18'));
   }

}?>




