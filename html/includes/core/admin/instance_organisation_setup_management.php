<?php
   $organisations = Admin::org_data(array(), false, $DBConn);
   $countries =  Data::countries(array(), false, $DBConn);
   // var_dump($countries);  
   if(!$organisations){
      Alert::info("No Organisations set up for this tax computation instance", true, array('fst-italic', 'text-center', 'font-18'));?>
      <button class="btn btn-sm btn-primary-light shadow-sm mx-auto" data-bs-toggle="modal" data-bs-target="#manageOrganisationModal">
         Add Organisation
      </button>   
      <?php        
   } else {
      $entityTypes = Data::entity_types(array(), false, $DBConn);
      $african_countries = Data::countries([], false, $DBConn);
      $industrySectors = Data::industry_sectors(["Suspended"=> 'N'], false, $DBConn);
      foreach ($organisations as $key => $organisation) {?>
         <div class="card custom-card">
               <div class="card-header justify-content-between">
                  <div class="card-title"> 
                     <h4 class="t400" ><?= $organisation->orgName ?> Entities</h4>
                  </div>
                  <button type="button"class="btn btn-sm btn-primary-light shadow-sm manageEntityOrganisation" data-bs-toggle="modal" data-organisationId="<?= $organisation->orgDataID ?>"  data-bs-target="#manageEntity">
                     <i class="fas fa-plus"></i>
                     Add New Entity
                  </button>
               </div>          
               <div class="card-body">         
                  <?php $entities = Data::entities_full(['orgDataID'=> $organisation->orgDataID, 'Suspended'=> 'N'], false, $DBConn); ?>
                  <div class="list-group list-group-flush border-0  ">
                     <?php
                     if($entities) {
                           // Sort entities by entityParentID
                           usort($entities, function($a, $b) {
                              return $a->entityParentID <=> $b->entityParentID;
                           });                     
                           function buildTree($entities, $parent = 0) {
                              $tree = "";
                              foreach ($entities as $entity) {
                                 if ($entity->entityParentID == $parent) {
                                       $tree .= "<div class='list-group-item  border-0 py-1'>
                                                      <div class='row border-bottom py-0'>
                                                               <div class='col-md-3'>
                                                                  {$entity->entityName}
                                                               </div>
                                                               <div class='col-md'>
                                                                  {$entity->entityTypeTitle}
                                                               </div>
                                                               <div class='col-md'>
                                                                  {$entity->registrationNumber}/ {$entity->entityPIN}
                                                               </div>
                                                               <div class='col-md'>
                                                                  {$entity->countryName} / {$entity->entityCity}  
                                                               </div>
                                                               <div class='col-md'>
                                                                  {$entity->industryTitle}
                                                               </div>
                                                               <div class='col-md text-end'>
                                                                  <button
                                                                     class='btn btn-sm btn-primary-light shadow-sm editEntity'
                                                                     data-bs-toggle='modal'
                                                                     data-bs-target='#manageEntity'
                                                                     data-id='{$entity->entityID}'
                                                                     data-entity-name='{$entity->entityName}'
                                                                     data-entity-description='{$entity->entityDescription}'
                                                                     data-entity-type-id='{$entity->entityTypeID}'                                                        
                                                                     data-org-data-id='{$entity->orgDataID}'
                                                                     data-entity-parent-id='{$entity->entityParentID}'
                                                                     data-industry-sector-id='{$entity->industrySectorID}'
                                                                     data-registration-number='{$entity->registrationNumber}'
                                                                     data-entity-pin='{$entity->entityPIN}'
                                                                     data-entity-city='{$entity->entityCity}'
                                                                     data-entity-country='{$entity->entityCountry}'
                                                                     data-entity-phone-number='{$entity->entityPhoneNumber}'
                                                                     data-entity-email='{$entity->entityEmail}'
                                                                     >
                                                                     Edit
                                                                  </button>
                                                                  <button
                                                                     class='btn btn-sm btn-danger-light shadow-sm deleteEntity'
                                                                     data-bs-toggle='modal'
                                                                     data-id='{$entity->entityID}'
                                                                     data-entity-name='{$entity->entityName}'
                                                                     data-bs-target='#deleteEntityModal'  >
                                                                     <i class='fas fa-trash'></i>
                                                                     Delete
                                                                  </button>
                                                               </div>                                                    
                                                      </div>                                            
                                                      ";                                    
                                       $tree .= buildTree($entities, $entity->entityID);
                                       $tree .= "</div>";
                                 }
                              }                       
                              return $tree;
                           }
                     }
                     if($entities) {
                           echo buildTree($entities);
                     } else {
                           Alert::info("There are no Entities set up for {$organisation->orgName}", array('fst-italic', 'text-center', 'font-18'));
                     }?>
                  </div>              
               </div>
         </div>   
         <?php
      }
   }
   // Modal for adding Organisation
   echo Utility::form_modal_header("manageOrganisationModal", "global/manage_organisation_details.php", "Add Organisation", array('modal-md', 'modal-dialog-centered', "modal-xl"), $base);
   include "includes/core/admin/organisation/modals/manage_organisation.php";
   echo Utility::form_modal_footer('Save Organisation');
   // Modal for adding Entity
   echo Utility::form_modal_header("manageEntity", "tax/admin/manage_entity.php", "Add Organization Entity", array('modal-md', 'modal-dialog-centered', "modal-xl"), $base);
        include "includes/scripts/global/modals/manage_entity.php";
    echo Utility::form_modal_footer('Save Entity');

    // Delete Confirmation Modal
    echo Utility::form_modal_header(
        "deleteEntityModal",
        "tax/admin/manage_entity.php",
        "Delete Organisation Entity",
        array('modal-lg', 'modal-dialog-centered'),
        $base
    );
    ?>
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="entityID" id="entityID">
            <p class="font-18"> Are you sure you want to delete the entity "<span id="entityNameToDelete" class="fw-bold text-danger"></span>"?
            This action cannot be undone.  </p>
    <?= Utility::form_modal_footer('Yes, Delete Entity', 'deleteConfirmed',  ' btn btn-danger', true);  ?>