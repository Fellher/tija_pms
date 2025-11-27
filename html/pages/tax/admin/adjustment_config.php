<?php 

if (!$isValidAdmin ) {
   Alert::info("You need to be logged in as a valid administrator to access this page", true, 
       array('fst-italic', 'text-center', 'font-18'));
   exit;
}
$organisations = Admin::org_data(array(), false, $DBConn);
// var_dump($organisations);
?>
<div class="container-fluid">
   <div class="row pt-2 bg-light px-lg-3 mb-3">
      <h2 class="mb-2 t300 fs-3 border-bottom border-primary">
         Tax Computation config - Adjustments
         
      </h2>
   </div>
   <div class="row mb-3">
      <div class="col-12">
         <h3> 
            Adjustments
            <span class="float-end">
              
               <a href="#manageAdjustmentTypes" class="btn btn-primary" data-bs-toggle="modal">
                    <i class="fas fa-plus"></i> Add Adjustment Types
               </a>
               <a href="<?= "{$base}html/?{$getString}" ?>" class="btn btn-primary">
                  <i class="fas fa-arrow-left"></i> Back to Tax Computation config
               </a>
            </span>
         </h3>
         <?php
         $adjustmentTypes = Tax::tax_adjustment_types([    
            "Suspended" => 'N'], 
            false, 
            $DBConn
         );
         if($adjustmentTypes){?>
         
            <div class="card custom-card my-5">
               <div class="card-header card-header justify-content-between">
                  <div class="card-title">
                     AdjustmentTypes
                  </div>
               </div>
               <div class="card-body">
                  <div class="list-group list-group-flush">                    
                     <?php
                     foreach ($adjustmentTypes as $key => $adjustmentType) {?>
                        <div class="list-group-item list-group-item-action" aria-current="true">
                           <div class="d-sm-flex w-100 justify-content-between">
                              <h6 class="mb-1 fw-semibold">    
                                 <?= $adjustmentType->adjustmentType ?> 
                              </h6>                              
                              <small>
                                 <div class="btn-list">                                          
                                    <a href='#' class='editAdjustmentType btn  btn-icon rounded-pill btn-secondary-light btn-wave btn-sm' data-bs-toggle='modal' data-bs-target='#manageAdjustmentTypes' data-id='<?= $adjustmentType->adjustmentTypeID ?>'>
                                       <i class='fas fa-edit'></i> 
                                    </a>                                 
                                    <a aria-label="anchor" href="#" class="deleteAdjustmentType btn  btn-icon rounded-pill btn-danger-light btn-wave btn-sm" data-bs-toggle='modal' data-bs-target='#deleteAdjustmentModal' data-id='<?= $adjustmentType->adjustmentTypeID ?>'>
                                    <i class="ti ti-trash"></i> 
                                    </a>
                                 </div>
                              </small>
                           </div>                        
                           <p class="mb-1"><?= $adjustmentType->adjustmentTypeDescription ?></p>
                        </div>
                        <?php
                     } ?>                  
                  </div>
               </div>
            </div>
               <?php
         }  else { 
            echo "<p>No Adjustment Types found</p>";
         }           

         echo Utility::form_modal_header("manageAdjustments", "tax/admin/manage_adjustment_categories.php", "Add Tax Adjustment categories", array('modal-md', 'modal-dialog-centered', "modal-xl"), $base); 
            include "includes/scripts/tax/modals/manage_tax_adjustment_categories.php";
         echo Utility::form_modal_footer('Save Tax Adjustment Category');
         
         echo Utility::form_modal_header("manageAdjustmentTypes", "tax/admin/manage_adjustment_types.php", "Add Tax Adjustment categories", array('modal-md', 'modal-dialog-centered', "modal-xl"), $base); 
            // include "includes/scripts/tax/modals/manage_tax_adjustment_types.php";
            ?>
            <div class="row">                  
               <div class="form-group">
                  <input type="hidden" name="action" value="">
                  <input type="hidden" name="adjustmentTypeID" id="adjustmentTypeID" value="">
                  <label for="adjustmentType">Adjustment Type</label>
                  <input type="text" name="adjustmentType" id="adjustmentType" class="form-control form-control-sm" required>
               </div>
               <div class="form-group">
                  <label for="adjustmentTypeDescription">Adjustment Type Description</label>
                  <textarea name="adjustmentTypeDescription" id="adjustmentTypeDescription" class="form-control form-control-sm borderless" required></textarea>
               </div>
            </div>
            <?php
         echo Utility::form_modal_footer('Save Tax Adjustment Category'); 

        // Delete Confirmation Modal
echo Utility::form_modal_header(
   "deleteAdjustmentModal", 
   "tax/admin/manage_adjustment_categories.php", 
   "Delete Tax Adjustment Category", 
   array('modal-xl', 'modal-dialog-centered'), 
   $base
); 
?>
   <div class="modal-body">
       <input type="hidden" name="action" value="delete">
       <input type="hidden" name="adjustmentCategoryID" id="deleteCategoryId">
      
       <p class="text-center">Are you sure you want to delete this adjustment category?</p>
       <p class="text-center text-danger font-weight-bold" id="deleteCategoryName"></p>
   </div>
<?php 
echo Utility::form_modal_footer('Yes, Delete Category', 'btn-danger'); 

          // Delete Confirmation Modal
          echo Utility::form_modal_header(
            "deleteAdjustmentModal", 
            "tax/admin/manage_adjustment_type.php", 
            "Delete Tax Adjustment Type", 
            array('modal-xl', 'modal-dialog-centered'), 
            $base
         );
         include "includes/scripts/tax/modals/delete_adjustment_type.php"; 
         echo Utility::form_modal_footer('Yes, Delete Category', 'btn-danger'); 

         if($adjustmentTypes){?>
            <div class="card custom-card my-5">
               <div class="card-header card-header justify-content-between">
                  <div class="card-title">
                     Adjustment Categories                    
                  </div>
                  <a href="#manageAdjustments" class="btn btn-primary" data-bs-toggle="modal">
                        <i class="fas fa-plus"></i> Add Adjustment Category
                  </a>
               </div>
               <div class="card-body">
               <?php
               foreach($adjustmentTypes as $type) {
                  // var_dump($type);
                  $adjustmentCategories = Tax::tax_adjustment_categories([
                        "adjustmentTypeID" => $type->adjustmentTypeID
                  ], false, $DBConn); ?>
                  <div class="list-group list-group-flush">                    
                     <?php
                     if($adjustmentCategories) {?>                          
                        <div class=" ">
                           <div class="card-header">
                              <div class="card-title">
                                 <h5><?= $type->adjustmentType ?></h5> </div>
                           </div>
                           <div class="list-group list-group-flush">
                              <?php 
                              if($adjustmentCategories) {
                                 foreach($adjustmentCategories as $category) {
                                    // var_dump($category);?>
   
                                    <div class="list-group-item">
                                       <div class="d-flex w-100 justify-content-between">
                                          <h5 class="mb-1 fw-bold font-14"><?= $category->adjustmentCategoryName ?></h5>
                                          <small>
                                             <a href="#manageAdjustments" 
                                                class="btn btn-primary  btn-sm" 
                                                data-bs-toggle="modal"
                                                data-id="<?= $category->adjustmentCategoryID ?>"
                                                data-category-name="<?= htmlspecialchars($category->adjustmentCategoryName) ?>"
                                                data-category-description="<?= htmlspecialchars($category->adjustmentCategoryDescription) ?>"
                                                data-type-id="<?= $category->adjustmentTypeID ?>">
                                                <i class="fas fa-edit"></i> Edit
                                             </a>
                                          <!-- Update the delete button in the list-group-item -->
                                             <a href="#deleteAdjustmentModal" 
                                                class="btn btn-sm btn-danger"
                                                data-bs-toggle="modal"
                                                data-id="<?= $category->adjustmentCategoryID ?>"
                                                data-category-name="<?= htmlspecialchars($category->adjustmentCategoryName) ?>">
                                                <i class="fas fa-trash"></i> Delete
                                             </a>
                                          </small>
                                       </div>
                                       <p class="mb-1"><?= $category->adjustmentCategoryDescription ?></p>
                                    </div>
                                    <?php                                 
                                 }
                              }?>                               
                           </div> 
                        </div>
                        <?php
                     }?>

                  </div>
                  <?php
               }?>                  
            </div>
            <?php
         }?>   
      </div>
   </div>
</div>