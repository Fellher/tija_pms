<?php 
if(!$isAdmin) {
  Alert::info("You do not have permission to view this page", true, array('fst-italic', 'text-center', 'font-18'));
  exit();
}
$organisations = Admin::organisation_data_mini([], false, $DBConn);
// var_dump($organisations);

$allEmployees = Data::users([], false, $DBConn);

$orgDataID = isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : ($_SESSION['orgDataID'] ? $_SESSION['orgDataID'] : null);
$entityID = isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : ($_SESSION['entityID'] ? $_SESSION['entityID'] : null);"";
?>
<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb border-bottom">
    <h1 class="page-title fw-medium fs-24 mb-0">Proposal Status Levels</h1>
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
      <!-- <h3 class="border-bottom">Proposal Statuses</h3> -->
      <p>    Proposal statuses define the process of the proposal document handling and help in automating the sales pipeline. Sales in progress category should include steps from creating the proposal till the final step before the customer accepts or rejects the proposal. When proposal is accepted or rejected it should be set into a won or lost status. This will cause a prompt for the salesperson to update the sales case value and sales status accordingly.
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
       $proposalStatusLevels = Sales::proposal_statuses(['orgDataID'=>$orgDataID, 'entityID'=>$entityID], false, $DBConn);
      echo Utility::form_modal_header("manageStatus", "sales/manage_proposal_status_level.php", "Manage Status", array('modal-md', 'modal-dialog-centered'), $base); 
         include "includes/scripts/sales/modals/manage_proposal_status_level.php";
      echo Utility::form_modal_footer('Save Status', 'saveProposalStatus',  ' btn btn-success btn-sm', true);
      ?>
      <div class="card-body">
         <div class="list-group list-group-flush">
            <?php 
            if($proposalStatusLevels){
               foreach ($proposalStatusLevels as $key => $proposalStatusLevel) { ?>
                  <div class="list-group-item">
                     <div class="d-flex justify-content-between">
                        <div class="ms-2">
                           <h5 class="mb-0 d-block">
                              <?= $proposalStatusLevel->proposalStatusName ?>
                              <span class="badge bg-primary ms-5"><?= $proposalStatusLevel->proposalStatusCategoryName ?></span>
                           </h5>
                           <p class="text-wrap fst-italic mb-0">
                              <?= Utility::clean_string($proposalStatusLevel->proposalStatusDescription) ?>
                           </p>
                        </div>
                        <div class="ms-2">
                           <a href="javascript:void(0);" class="btn btn-primary-light shadow-sm editStatus"  data-bs-toggle="modal" data-bs-target="#manageStatus" 
                           data-proposal-status-id="<?= $proposalStatusLevel->proposalStatusID ?>"
                           data-proposal-status-name="<?= $proposalStatusLevel->proposalStatusName ?>" 
                           data-proposal-status-description="<?= $proposalStatusLevel->proposalStatusDescription ?>" 
                           data-org-data-id="<?= $orgDataID ?>"
                           data-entity-id="<?= $entityID ?>"
                           
                           >
                              <i class="ri-pencil-line"></i>
                           </a>
                        </div>
                     </div>

                  </div>
                  <?php
                  # code...
               }
               
               // var_dump($proposalStatusLevels);

            } else {
               Alert::info("No proposal status levels found", true, array('fst-italic', 'text-center', 'font-18'));
            }?>
        
         </div>
      </div>
     
   </div>
   <!-- Proposal Status category -->
   <div class="container-fluid">
   <div class="card card-body shadow-lg">
      <div class="d-md-flex justify-content-between text-align-center border-bottom ">
         <h3 class="">Proposal Status Categories</h3>
         <div class="ms-md-1 ms-0">
            <button type="button" class="btn btn-primary-light shadow btn-sm px-4" data-bs-toggle="modal" data-bs-target="#manageStatusCategory" data-action="add">
            <i class="ri-add-line"></i>
            Add Status</button>
         </div>
      </div>
      
      <p>    
         Proposal statuses define the process of the proposal document handling and help in automating the sales pipeline. Sales in progress category should include steps from creating the proposal till the final step before the customer accepts or rejects the proposal. When proposal is accepted or rejected it should be set into a won or lost status. This will cause a prompt for the salesperson to update the sales case value and sales status accordingly.
      </p>

      <?php 
      $statusCategories = Sales::proposal_status_categories(['orgDataID'=>$orgDataID, 'entityID'=>$entityID], false, $DBConn);
      // var_dump($statusCategories);
      if($statusCategories) {?>
         <div class="list-group  list-group-flush">
            <?php
            foreach ($statusCategories as $statusCategory) { ?>
               <div class="list-group-item">
                  <div class="d-flex justify-content-between">
                     <div class="ms-2">
                        <h5 class="mb-0"><?= $statusCategory->proposalStatusCategoryName ?></h5>
                        <p class="text-wrap fst-italic mb-0">
                           <?= Utility::clean_string($statusCategory->proposalStatusCategoryDescription) ?>
                        </p>
                     </div>
                     <div class="ms-2">
                        <a href="javascript:void(0);" class="btn btn-primary-light shadow-sm editStatus"  data-bs-toggle="modal" data-bs-target="#manageStatusCategory" 
                        data-proposal-status-category-id="<?= $statusCategory->proposalStatusCategoryID ?>"
                        data-proposal-status-category-name="<?= $statusCategory->proposalStatusCategoryName ?>" 
                        data-proposal-status-category-description="<?= $statusCategory->proposalStatusCategoryDescription ?>" 
                        data-org-data-id="<?= $statusCategory->orgDataID ?>"
                        data-entity-id="<?= $statusCategory->entityID ?>"
                       
                        >
                              <i class="ri-pencil-line"></i>
                        </a>
                     </div>
                  </div>
               </div>
               <?php
            }?>

         </div>
         <?php
      } else {
         Alert::info("No status categories found", true, array('fst-italic', 'text-center', 'font-18'));
      }?>
   </div>
</div>
   <?php
   echo Utility::form_modal_header("manageStatusCategory", "sales/manage_proposal_status_category.php", "Manage Status Category", array('modal-md', 'modal-dialog-centered'), $base); 
      include "includes/scripts/sales/modals/manage_proposal_status_category.php";
   echo Utility::form_modal_footer('Save Status', 'saveProposalStatusCategory',  ' btn btn-success btn-sm', true);
   
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





