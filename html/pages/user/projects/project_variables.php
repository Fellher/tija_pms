<?php 
if(!$isAdmin) {
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}?>
<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <h1 class="page-title fw-medium fs-24 mb-0">Project Setup Dashboard </h1>
    <div class="ms-md-1 ms-0">
        
         <?php  echo date_format($dt,'l, d F Y ') ?>
    </div>
</div>

<!-- Page Header -->
<!-- Set up work types -->
<?php 
$workTypes = Data::work_types([], false, $DBConn);
// var_dump($workTypes);?>

<div class="row">
   <div class="col-md-6 col-12">
      <div class="card custom-card">
         <div class="card-header justify-content-between">
            <h4 class="card-title ">Project Work Types</h4>
            <div class="card-options">
               <a href="javascript:void(0)" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manage_work_type">Add Work Type</a>
            </div>
         </div>
         <div class="card-body">
            <div class="alert alert-info alert-dismissible fade show" role="alert">
               <strong>Note:</strong> Work types are used to categorize the work done on a project. You can add, edit, or delete work types as needed.
               <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <div class="list-group list-group-flush">
               <div class="list-group-item d-flex justify-content-between align-items-center">
                  <h5 class="mb-0">Work Type </h5>
                  <div class="form-check form-switch">
                     <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault" role="switch" checked>
                     <label class="form-check
                     label" for="flexSwitchCheckDefault">Active</label>
                  </div>
               </div>
               <?php
               if($workTypes): ?>
               <?php foreach($workTypes as $workType): ?>
               <div class="list-group-item d-flex justify-content-between align-items-center">
                  <p class="mb-0"><?php echo $workType->workTypeName; ?></p>
                  <div class="">

                     <a 
                        href="javascript:void(0)" 
                        class="btn btn-sm btn-primary editWorkType" 
                        data-bs-toggle="modal" 
                        data-bs-target="#manage_work_type" 
                        data-id="<?php echo $workType->workTypeID; ?>"
                        data-name="<?php echo $workType->workTypeName; ?>"
                        data-description="<?php echo $workType->workTypeDescription; ?>"
                        data-suspended="<?php echo $workType->Suspended; ?>"
                        >
                        Edit
                     </a>

                     <a href="javascript:void(0)" 
                        class="btn btn-sm btn-danger" 
                        data-bs-toggle="modal" 
                        data-bs-target="#deleteWorkTypeModal" 
                        data-id="<?php echo  $workType->workTypeID; ?>">
                        Delete
                     </a>

                  </div>
               </div>
               <?php endforeach; ?>
               <?php else: ?>
               <div class="list-group-item d-flex justify-content-between align-items-center">
                  <h5 class="mb-0">No work types found.</h5>
                  <div class="">
                     <a href="javascript:void(0)" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addWorkTypeModal">Add Work Type</a>
                  </div>
               </div>
               <?php endif; ?>
            </div>        
         </div>
      </div>
   </div>
   <div class="col-md-6 col-12">
      <div class="card custom-card">
         <div class="card-header justify-content-between">
            <h4 class="card-title ">Project Bulling Rate Types Details</h4>
            <div class="card-options">
               <a href="javascript:void(0)" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manage_rate_type">Add Work Type</a>
            </div>
         </div>
         <div class="card-body">
            <div class="alert alert-info alert-dismissible fade show" role="alert">
               <strong>Note:</strong> Billing rates types are used to categorise the billing rates for a project. You can add, edit, or delete billing rate types as needed.
               <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <div class="list-group list-group-flush">
               <div class="list-group-item d-flex justify-content-between align-items-center">
                  <h5 class="mb-0">Billing Rate types </h5>
                  <div class="form-check form-switch">
                     <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault" role="switch" checked>
                     <label class="form-check label" for="flexSwitchCheckDefault">Active</label>
                  </div>
               </div>
               <?php
               $billingRateTypes = Projects::billing_rate_type([], false, $DBConn);
               // var_dump($billingRateTypes);
               if($billingRateTypes): ?>
                  <?php foreach($billingRateTypes as $billingRateType): ?>
                     <div class="list-group-item d-flex justify-content-between align-items-center">
                        <p class="mb-0"><?php echo $billingRateType->billingRateTypeName; ?></p>
                        <div class="">
                           <a href="javascript:void(0)" 
                              class="btn btn-sm btn-primary editBillingRateType" 
                              data-bs-toggle="modal" 
                              data-bs-target="#manage_rate_type" 
                              data-id="<?php echo $billingRateType->billingRateTypeID; ?>"
                              data-name="<?php echo $billingRateType->billingRateTypeName; ?>"
                              data-description="<?php echo $billingRateType->billingRateTypeDescription; ?>"
                              data-suspended="<?php echo $billingRateType->Suspended; ?>"
                              >
                              Edit
                           </a>

                           <a href="javascript:void(0)" 
                              class="btn btn-sm btn-danger" 
                              data-bs-toggle="modal" 
                              data-bs-target="#deleteBillingRateModal" 
                              data-id="<?php echo  $billingRateType->billingRateTypeID; ?>">
                              Delete
                           </a>

                        </div>
                     </div>
                  <?php endforeach; ?>
               <?php else: ?>
              
                  <div class="list-group-item d-flex justify-content-between align-items-center">
                     <h5 class="mb-0">No billing rate types found.</h5>
                     <div class="">
                        <a href="javascript:void(0)" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addBillingRateTypeModal">Add Billing Rate Type</a>
                     </div>
                  </div>
               <?php endif; ?>
            </div>
         </div>
      </div>
   </div>

</div>
<?php 
echo Utility::form_modal_header("manage_rate_type", "projects/manage_rate_type.php", "Manage Work Type  Details", array('modal-md', 'modal-dialog-centered'), $base);
include 'includes/scripts/projects/modals/manage_rate_type.php';
echo Utility::form_modal_footer("Save Work Type", "manage_rate_type_details", 'btn btn-primary btn-sm');
?>
<?php 
echo Utility::form_modal_header("manage_work_type", "projects/manage_work_type.php", "Manage Work Type  Details", array('modal-md', 'modal-dialog-centered'), $base);
include 'includes/scripts/projects/modals/manage_work_type.php';
echo Utility::form_modal_footer("Save Work Type", "manage_work_type_details", 'btn btn-primary btn-sm');
?>
<script>
   document.addEventListener('DOMContentLoaded', function(){
      var editWorkTypeButtons = document.querySelectorAll('.editWorkType');

      editWorkTypeButtons.forEach(function(button){
         button.addEventListener('click', function(){
            var id = button.getAttribute('data-id');
            var name = button.getAttribute('data-name');
            var description = button.getAttribute('data-description');
            var suspended = button.getAttribute('data-suspended');

            var workTypeIDInput = document.getElementById('workTypeID');
            var workTypeNameInput = document.getElementById('workTypeName');
            var workTypeDescriptionInput = document.getElementById('workTypeDescription');

            workTypeIDInput.value = id;
            workTypeNameInput.value = name;

            tinymce.init({
                selector: '#workTypeDescription'
            });
            const editor = tinymce.get('workTypeDescription'); // Make sure 'entityDescription' matches your textarea's ID
     
            if (editor) {
                // Wait for a brief moment to ensure tinyMCE is fully initialized
                setTimeout(() => {
                    editor.setContent(description || '');
                }, 100);
            }
            // workTypeDescriptionInput.value = description;
         });
      });
   });
</script>





