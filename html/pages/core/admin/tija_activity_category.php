<?php 
if(!$isValidAdmin) {
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}

?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb border-bottom">
    <h1 class="page-title fw-medium fs-24 mb-0">Tija  Activities</h1>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                <li class="breadcrumb-item active d-inline-flex" aria-current="page">Company Details</li>
            </ol>
        </nav>
    </div>
</div>
<?php 
// $activities = Sales::tija_activities(array('Suspended'=>'N'), false, $DBConn);
$activityCategories = Sales::tija_activity_categories(array('Suspended'=>'N'), false, $DBConn);
$activityTypes = Sales::tija_activity_types(array('Suspended'=>'N'), false, $DBConn);
?>
<!-- Start::row-1 -->
 <div class="container-fluid">
   <div class="card custom-card">
      <div class="card-header justify-content-between border-bottom">
         <h4 class="card-title">Tija Activity Categories</h4>
         <button type="button" class="btn btn-primary-light shadow btn-sm px-4" data-bs-toggle="modal" data-bs-target="#manageActivityCategoriesModal">
         <i class="ri-add-line"></i>
         Add Activity Category</button>
      </div>
      <div class="card-body">
         <div class="table-responsive">
            <table class="table table-hover table-borderless table-striped table-vcenter text-nowrap mb-0">
               <thead>
                  <tr>
                     <th class="">Activity Category Name</th>
                     <th class="">Activity Category Description</th>
                     <th class="text-end">Actions</th>
                  </tr>
               </thead>
               <tbody>
                  <?php
                  if($activityCategories) {
                     foreach ($activityCategories as $activityCategory) {?>
                        <tr>
                           <td class="">
                              <?php echo $activityCategory->activityCategoryName; ?>
                           </td>
                           <td class="">
                              <?php echo $activityCategory->activityCategoryDescription; ?>
                           </td>
                           <td class="text-end">
                              <div class="d-flex justify-content-end">
                                 <button 
                                    type="button" 
                                    class="btn btn-primary-light btn-sm shadow edit-activity-category" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#manageActivityCategoriesModal"
                                    data-activityCategoryName="<?php echo $activityCategory->activityCategoryName; ?>"
                                    data-activityCategoryDescription="<?php echo $activityCategory->activityCategoryDescription; ?>"
                                    data-activityCategoryID="<?php echo $activityCategory->activityCategoryID; ?>"
                                    >
                                    <i class="ri-pencil-line"></i>
                                 </button>
                                 <button type="button" class="btn btn-danger-light btn-sm shadow ms-2" data-bs-toggle="modal" data-bs-target="#deleteActivityCategoriesModal">
                                    <i class="ri-delete-bin-line"></i>
                                 </button>
                              </div>
                           </td>
                        </tr>
                        <?php
                     }
                  } else {
                     ?>
                     <tr>
                        <td colspan="3" class="text-center">No Activity Categories found</td>
                     </tr>
                     <?php
                  }?>
               </tbody>
            </table>
         </div>
      </div>
      <?php 
      echo Utility::form_modal_header("manageActivityCategoriesModal", "sales/manage_activity_category.php", "Manage Activity Category", array('modal-md', 'modal-dialog-centered'), $base);
      include "includes/scripts/sales/modals/manage_activity_category.php";
      echo Utility::form_modal_footer('Save Activity Category', 'saveActivityCategory',  ' btn btn-success btn-sm', true);
      ?>
   </div>
</div>
<script>
   document.addEventListener('DOMContentLoaded', function(){
      var editButtons = document.querySelectorAll('.edit-activity-category');
      editButtons.forEach(function(button){
         button.addEventListener('click', function(){
            var activityCategoryName = this.getAttribute('data-activityCategoryName');
            var activityCategoryDescription = this.getAttribute('data-activityCategoryDescription');
            var activityCategoryID = this.getAttribute('data-activityCategoryID');

            console.log(activityCategoryDescription);

            var modal = document.querySelector('#manageActivityCategoriesModal');
            var activityCategoryNameInput = modal.querySelector('input[name="activityCategoryName"]');
            var activityCategoryDescriptionInput = modal.querySelector('textarea[name="activityCategoryDescription"]');
            var activityCategoryIDInput = modal.querySelector('input[name="activityCategoryID"]');

            // console.log(activityCategoryDescriptionInput);
            tinymce.get('activityCategoryDescription').setContent(activityCategoryDescription);

            activityCategoryNameInput.value = activityCategoryName;
            activityCategoryDescriptionInput.value = activityCategoryDescription;
            activityCategoryIDInput.value = activityCategoryID;
         });
      });
   });
</script>
<!-- End::row-1 -->


<div class="container-fluid">
   <div class="card custom-card">
      <div class="card-header justify-content-between border-bottom">
         <h4 class="card-title">Tija Activity Types</h4>
         <button type="button" class="btn btn-primary-light shadow btn-sm px-4" data-bs-toggle="modal" data-bs-target="#manageActivityTypesModal">
         <i class="ri-add-line"></i>
         Add Activity Types</button>
      </div>
      <div class="card-body">
         <div class="table-responsive">
            <table class="table table-hover table-borderless table-striped table-vcenter text-nowrap mb-0">
               <thead>
                  <tr>
                     <th class="">Activity Type Name</th>
                     <th class="">Activity Type Description </th>
                     <th class="text-end">Actions</th>
                  </tr>
               </thead>
               <tbody>
                  <?php
                  if($activityTypes) {
                     foreach ($activityTypes as $activityType) {?>
                        <tr>
                           <td class="">
                              <?php echo $activityType->activityTypeName; ?>
                           </td>
                           <td class="">
                              <?php echo $activityType->activityTypeDescription; ?>
                           </td>
                           <td class="text-end">
                              <div class="d-flex justify-content-end">
                                 <button 
                                    type="button" 
                                    class="btn btn-primary-light btn-sm shadow edit-activity-type" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#manageActivityTypesModal"
                                    data-activityTypeName="<?php echo $activityType->activityTypeName; ?>"
                                    data-activityTypeDescription="<?php echo $activityType->activityTypeDescription; ?>"
                                    data-activityTypeID="<?php echo $activityType->activityTypeID; ?>"
                                    >
                                    <i class="ri-pencil-line"></i>
                                 </button>
                                 <button type="button" class="btn btn-danger-light btn-sm shadow ms-2" data-bs-toggle="modal" data-bs-target="#deleteActivityTypesModal">
                                    <i class="ri-delete-bin-line"></i>
                                 </button>
                              </div>
                           </td>
                        </tr>
                        <?php
                     }
                  } else {
                     ?>
                     <tr>
                        <td colspan="3" class="text-center">No Activity Types found</td>
                     </tr>
                     <?php
                  }?>
               </tbody>
            </table>
         </div>
      </div>
      <?php
         echo Utility::form_modal_header("manageActivityTypesModal", "sales/manage_activity_type.php", "Manage Activity Type", array('modal-md', 'modal-dialog-centered'), $base);
            include "includes/scripts/sales/modals/manage_activity_type.php";
         echo Utility::form_modal_footer('Save Activity Type', 'saveActivityType',  ' btn btn-success btn-sm', true);
      ?>
   </div>
</div>

<script>
   document.addEventListener('DOMContentLoaded', function(){
      var editButtons = document.querySelectorAll('.edit-activity-type');
      editButtons.forEach(function(button){
         button.addEventListener('click', function(){

            var activityTypeName = this.getAttribute('data-activityTypeName');
            var activityTypeDescription = this.getAttribute('data-activityTypeDescription');
            var activityTypeID = this.getAttribute('data-activityTypeID');
            console.log(activityTypeID);

            var modal = document.querySelector('#manageActivityTypesModal');
            var activityTypeNameInput = modal.querySelector('input[name="activityTypeName"]');
            var activityTypeDescriptionInput = modal.querySelector('textarea[name="activityTypeDescription"]');
            var activityTypeIDInput = modal.querySelector('input[name="activityTypeID"]');

            tinymce.get('activityTypeDescription').setContent(activityTypeDescription);

            activityTypeNameInput.value = activityTypeName;
            // activityTypeDescriptionInput.value = activityTypeDescription;
            activityTypeIDInput.value = activityTypeID;
         });
      });
   });
   </script>