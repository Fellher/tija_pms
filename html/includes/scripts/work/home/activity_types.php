<?php 
$activityCategories =  Schedule::activity_categories([], false, $DBConn);    ?>

<div class="container-fluid mt-3">
   <div class="card custom-card my-4 shadow-lg">
      <div class="card-header border-0 justify-content-between d-between">
         <h4 class="card-title">Activity Categories</h4>
         <div class="card-options">
            <a href="javascript:void(0);" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manageActivityCategory">Add Activity Category</a>
         </div>
      </div>
      <div class="card-body"> 
         <?php 
         if($activityCategories) {
            
            // var_dump($activityCategories[1])?>
            <div class="table-responsive">
               <table class="table table-sm table-hover table-striped table-vcenter ">
                  <thead>
                        <tr>
                           <th class="text-center">#</th>
                           <th>Activity Category</th>
                           <th>activity Description</th>
                           <th>icon Link</th>
                           <th class="text-center">Actions</th>
                        </tr>
                     </thead>
                  <?php
                  foreach($activityCategories as $key => $category) {?>
                     <tbody>
                        <tr>
                           <td> <?= $key+1 ?> </td>
                           <td> <?= $category->activityCategoryName; ?> </td>
                           <td> <?= $category->activityCategoryDescription; ?> </td>
                           <td> <?= $category->iconlink; ?> </td>
                           <td class="text-end">
                              <a 
                                 href="javascript:void(0);" 
                                 class="btn btn-sm btn-primary editActivityCategory" 
                                 data-bs-toggle="modal" 
                                 data-bs-target="#manageActivityCategory" 
                                 data-activity-category-id="<?= $category->activityCategoryID; ?>" 
                                 data-activity-category-name="<?= $category->activityCategoryName; ?>" 
                                 data-activity-category-description="<?= $category->activityCategoryDescription; ?>" 
                                 data-icon-link="<?= $category->iconlink; ?>"
                              > 
                                 Edit
                              </a>
                              <a href="javascript:void(0);" 
                                 class="btn btn-sm btn-danger" 
                                 data-bs-toggle="modal" 
                                 data-bs-target="#deleteActivityCategoryModal" 
                                 data-id="<?= $category->activityCategoryID; ?>" 
                                 data-name="<?= $category->activityCategoryName; ?>"
                                 >
                                 Delete
                              </a>
                           </td>
                        </tr>
                     </tbody>
                     <?php
                  }?> 
             
               </table>
            </div>
            <?php 
         } else {
            Alert::info("No work categories set for this instance", true, array('fst-italic', 'text-center', 'font-18'));
         }?>
      </div>
   </div>


   <div class="card custom-card my-4 shadow-lg">
      <div class="card-header  justify-content-between d-between">
         <h4 class="card-title">Activity Types</h4>
         <div class="card-options">
            <a href="javascript:void(0);" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manageActivityType">Add Activity Types</a>
         </div>
      </div>
      <div class="card-body"> 
         <?php 
         $activityTypes =  Schedule::tija_activity_types([], false, $DBConn); 
         // var_dump($activityTypes);
         // var_dump($activityTypes[1]);
         if($activityTypes){?>
         <div class="table-responsive">
            <table class="table table-sm table-hover table-striped table-vcenter ">
               <thead>
                     <tr>
                        <th class="text-center">#</th>
                        <th>Activity Type</th>
                       
                        <th>activity Description</th>
                        <th>Activity Category</th>
                        <th>icon Link</th>
                        <th class="text-end">Actions</th>
                     </tr>
                  </thead>
               <?php
               foreach($activityTypes as $key => $type) {
                  $categoryDetails = Schedule::activity_categories(array('activityCategoryID'=>$type->activityCategoryID), true, $DBConn);
                  $type->activityCategoryName = isset($categoryDetails->activityCategoryName) ? $categoryDetails->activityCategoryName : "No Category";
                  ?>
                  <tbody>
                     <tr>
                        <td> <?= $key+1 ?> </td>
                        <td> <?= $type->activityTypeName; ?> </td>
                      
                        <td> <?= $type->activityTypeDescription; ?> </td>
                        <td> <?= $type->activityCategoryName; ?> </td>
                        <td> <?= $type->iconlink; ?> </td>
                        <td class="text-end">
                           <a 
                              href="javascript:void(0);" 
                              class="btn btn-sm btn-primary editActivityType" 
                              data-bs-toggle="modal" 
                              data-bs-target="#manageActivityType" 
                              data-activity-type-id="<?= $type->activityTypeID; ?>" 
                              data-activity-type-name="<?= $type->activityTypeName; ?>" 
                              data-activity-category-id="<?= $type->activityCategoryID; ?>"
                              data-activity-type-description="<?= $type->activityTypeDescription; ?>" 
                              data-iconlink="<?= $type->iconlink; ?>"
                              >Edit</a>
                           <a href="javascript:void(0);" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteActivityTypeModal" data-id="<?= $type->activityTypeID; ?>" data-name="<?= $type->activityTypeName; ?>">Delete</a>
                        </td>
                     </tr>
                  </tbody>
                  <?php
               }?>
               </table>
         </div>

            <?php
         } else {
            Alert::info("No work categories set for this instance", true, array('fst-italic', 'text-center', 'font-18'));
         }?>
      </div>
   </div>
</div>
<?php
echo Utility::form_modal_header("manageActivityType", "schedule/manage_activity_type.php", "Manage Activity Type ", array("modal-lg", "modal-dialog-centered"), $base);
   include 'includes/scripts/schedule/modals/manage_activity_type.php'; 
echo Utility::form_modal_footer("Add activity Type", "addActivityTypeBtnID", "btn btn-primary submit");	
// activity category mnodal\
echo Utility::form_modal_header("manageActivityCategory", "schedule/manage_activity_category.php", "Manage Activity Category", array("modal-lg", "modal-dialog-centered"), $base);
   include 'includes/scripts/schedule/modals/manage_activity_category.php';
echo Utility::form_modal_footer("Edit activity Category", "manageActivityCategoryBtnId", "btn btn-primary submit");
?>	
<script>
   document.addEventListener('DOMContentLoaded', function () {
      // Edit Activity Type
      const editActivityTypeButtons = document.querySelectorAll('.editActivityType');
      editActivityTypeButtons.forEach(button => {
         button.addEventListener('click', function () {
            const form = document.getElementById('manageActivityType');
            if(!form) return;

            // get all data attributes from button
            const data = this.dataset;
            console.log(data);

     


            // map data attributes to form inputs
            const fieldMappings = {
               'activityTypeID': 'activityTypeId',
               'activityTypeName': 'activityTypeName',
               'activityCategoryID': 'activityCategoryId',
               'activityTypeDescription': 'activityTypeDescription',
               'iconlink': 'iconlink'

            }
             // Fill regular form inputs
            for (const [fieldName, dataAttribute] of Object.entries(fieldMappings)) {
               const input = form.querySelector(`[name="${fieldName}"]`);
               if (input) {
                  input.value = data[dataAttribute] || '';
               }
            }
            // Fill select inputs
            const selects = ['activityCategoryID'];
            selects.forEach(selectName => {
               const select = form.querySelector(`[name="${selectName}"]`);
               if (select && data[fieldMappings[selectName]]) {
                  select.value = data[fieldMappings[selectName]];
               }
            });

            tinymce.init({
               selector: '#activityTypeDescription'
            });

            // Handle tinyMCE editor
            const editor = tinymce.get('activityTypeDescription'); // Make sure 'activityTypeDescription' matches your textarea's ID
            if (editor) {
               // Wait for a brief moment to ensure tinyMCE is fully initialized
               setTimeout(() => {
                  editor.setContent(data.activityTypeDescription || '');
               }, 100);
            }



          
         });
      });

      document.querySelectorAll('.editActivityCategory').forEach(button => {
         button.addEventListener('click', function() {
            console.log(`button clicked`);
            const form = document.getElementById('activityCategoryForm');
            if (!form) return;

            // Get all data attributes from the button
            const data = this.dataset;

            console.log(data);

            // Map form fields to their corresponding data attributes
            const fieldMappings = {
               'activityCategoryID': 'activityCategoryId',
               'activityCategoryName': 'activityCategoryName',
               'activityCategoryDescription': 'activityCategoryDescription',
               'iconlink': 'iconLink'
            };

            // Fill regular form inputs
            for (const [fieldName, dataAttribute] of Object.entries(fieldMappings)) {
               const input = form.querySelector(`[name="${fieldName}"]`);
               if (input) {
                  input.value = data[dataAttribute] || '';
               }
            }

            // initialize tinyMCE for the description field
            tinymce.init({
               selector: '#activityCategoryDescription'
            });
            // Handle tinyMCE editor
            const editor = tinymce.get('activityCategoryDescription'); // Make sure 'activityCategoryDescription' matches your textarea's ID
            if (editor) {
               // Wait for a brief moment to ensure tinyMCE is fully initialized
               setTimeout(() => {
                  editor.setContent(data.activityCategoryDescription || '');
               }, 100);
            }
         });
      });
      
   });
</script>