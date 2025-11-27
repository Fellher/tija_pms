<?php $workCategories = Work::work_categories([], false, $DBConn); ?>
<div class="container-fluid mt-3">
   <div class="card custom-card border-top border-primary border-4 border-bottom-0 border-start-0 border-end-0">
      <div class="card-header border-0 justify-content-between d-between">
         <h4 class="card-title">Work Categories</h4>
         <div class="card-options">
            <a href="javascript:void(0);" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addWorkCategoryModal">Add Work Category</a>
         </div>
      </div>
      <div class="card-body">         
         <?php 
         if ($workCategories) {?>
            <div class="table table-sm">
               <table class="table table-sm table-bordered table-striped table-vcenter js-dataTable-full">
                  <thead>
                     <tr>
                        <th class="text-center">#</th>
                        <th>Work Category</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th class="text-center">Actions</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php 
                     foreach ($workCategories as $key => $category) { ?>
                        <tr>
                           <td class="text-center"><?php echo $key + 1; ?></td>
                           <td><?php echo $category->workCategoryName; ?></td>
                           <td><?php echo $category->workCategoryCode; ?></td>
                           <td><?php echo $category->workCategoryDescription; ?></td>
                           <td class="text-center">
                              <!-- Add your action buttons here -->
                              <a href="#" 
                                 class="btn  btn-icon btn-sm rounded-pill btn-secondary-light edit-work-category" 
                                 data-bs-toggle="modal" 
                                 title="Edit" 
                                 data-bs-target="#addWorkCategoryModal"
                                 data-work-category-id = "<?php echo $category->workCategoryID; ?>"
                                 data-work-category-name = "<?php echo $category->workCategoryName; ?>"
                                 data-work-category-code = "<?php echo $category->workCategoryCode; ?>"
                                 data-work-category-description = "<?php echo $category->workCategoryDescription; ?>"
                                 >
                                 <i class="ri-edit-line"></i>
                              </a>

                              <a href="#" class="btn btn-sm  btn-icon rounded-pill  btn-danger-light"> 
                                 <i class="ti ti-trash"></i> 
                              </a>

                           </td>
                        </tr>                     
                        <?php 
                     }?>
                  </tbody>
               </table>
            </div>        
            <?php
         } else{
            Alert::info("No work categories found", true, array('fst-italic', 'text-center', 'font-18'));
         } ?>

      </div>

   </div>

   <div class="card custom-card">
      <div class="card-header border-0 justify-content-between d-between  ">
         <h4 class="card-title">Work Types</h4>
         <div class="card-options">
            <a href="javascript:void(0);" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addWorkTypeModal">Add Work Type</a>
         </div>
      </div>
      <div class="card-body">
         <?php 
         $workTypes = Work::work_types([], false, $DBConn);
         if ($workTypes) {?>
            <div class="table table-sm">
               <table class="table table-sm table-bordered table-striped table-vcenter js-dataTable-full">
                  <thead>
                     <tr>
                        <th class="text-center">#</th>
                        <th>Work Type</th>
                        <th>Work Category</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th class="text-center">Actions</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php 
                     foreach ($workTypes as $key => $workType) { ?>
                        <tr>
                           <td class="text-center"><?php echo $key + 1; ?></td>
                           <td><?php echo $workType->workTypeName; ?></td>
                           <td><?php echo $workType->workCategoryName; ?></td>
                           <td><?php echo $workType->workTypeCode; ?></td>
                           <td><?php echo $workType->workTypeDescription; ?></td>
                           <td class="text-center">
                              <!-- Add your action buttons here -->
                              <a href="#" 
                                 class="btn  btn-icon btn-sm rounded-pill btn-secondary-light edit-work-type" 
                                 data-bs-toggle="modal" 
                                 title="Edit" 
                                 data-bs-target="#addWorkTypeModal"
                                 data-work-type-id = "<?php echo $workType->workTypeID; ?>"
                                 data-work-type-name = "<?php echo $workType->workTypeName; ?>"
                                 data-work-type-code = "<?php echo $workType->workTypeCode; ?>"
                                 data-work-type-description = "<?php echo $workType->workTypeDescription; ?>"
                                 data-work-category-id = "<?php echo $workType->workCategoryID; ?>"                                 
                                 >
                                 <i class="ri-edit-line"></i>
                              </a>

                              <a href="#" class="btn btn-sm  btn-icon rounded-pill  btn-danger-light"> 
                                 <i class="ti ti-trash"></i> 
                              </a>

                           </td>
                        </tr>                     
                        <?php 
                     }?>
                  </tbody>
               </table>
            </div>
            <?php            
         } else{
            Alert::info("No work types found", true, array('fst-italic', 'text-center', 'font-18'));
         } ?>
      </div>
   </div>

   <div class="card custom-card">
      <div class="card-header border-0 justify-content-between d-between  ">
         <h4 class="card-title">Activity Statuses</h4>
         <div class="card-options">
            <a href="javascript:void(0);" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manageActivityStatusModal">Add Activity Ststus</a>
         </div>
      </div>
      <div class="card-body">
         <?php 
         $activityStatuses = Schedule::activity_status([], false, $DBConn);
         if ($activityStatuses) {
            
            // var_dump($activityStatuses);?>
            <div class="table table-sm">
               <table class="table table-sm table-bordered table-striped table-vcenter js-dataTable-full">
                  <thead>
                     <tr>
                        <th class="text-center">#</th>
                        <th>Activity Status</th>
                       
                        <th>Description</th>
                        <th class="text-center">Actions</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php 
                     foreach ($activityStatuses as $key => $status) { ?>
                        <tr>
                           <td class="text-center"><?php echo $key + 1; ?></td>
                           <td><?php echo $status->activityStatusName; ?></td>
                          
                           <td><?php echo $status->activityStatusDescription; ?></td>
                           <td class="text-center">
                              <!-- Add your action buttons here -->
                              <a href="#" 
                                 class="btn  btn-icon btn-sm rounded-pill btn-secondary-light edit-work-type" 
                                 data-bs-toggle="modal" 
                                 title="Edit" 
                                 data-bs-target="#manageActivityStatusModal"
                                 data-activity-status-id = "<?php echo $status->activityStatusID; ?>"
                                 data-activity-status-name = "<?php echo $status->activityStatusName; ?>"
                            
                                 data-activity-status-description = "<?php echo $status->activityStatusDescription; ?>"
                                 >
                                 <i class="ri-edit-line"></i>
                              </a>

                              <a href="#" class="btn btn-sm  btn-icon rounded-pill  btn-danger-light"> 
                                 <i class="ti ti-trash"></i> 
                              </a>

                           </td>
                        </tr>                     
                        <?php 
                     }?>
                  </tbody>
               </table>
            </div>        
            <?php
         } else{
            Alert::info("No activity statuses found", true, array('fst-italic', 'text-center', 'font-18'));
         } ?>
      </div>
   </div>
   <div class="card custom-card">
      <div class="card-header border-0 justify-content-between d-between  ">
         <h4 class="card-title">Work Segment</h4>
         <div class="card-options">
            <a href="javascript:void(0);" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manageWorkSegmentModal">Add Work Segment</a>
         </div>
      </div>
      <div class="card-body">
         <?php 
         $workSegments = Work::work_segments([], false, $DBConn);
         if ($workSegments) {?>
            <div class="table table-sm">
               <table class="table table-sm table-bordered table-striped table-vcenter js-dataTable-full">
                  <thead>
                     <tr>
                        <th class="text-center">#</th>
                        <th>Work Segment</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th class="text-center">Actions</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php 
                     foreach ($workSegments as $key => $segment) { ?>
                        <tr>
                           <td class="text-center"><?php echo $key + 1; ?></td>
                           <td><?php echo $segment->workSegmentName; ?></td>
                           <td><?php echo $segment->workSegmentCode; ?></td>
                           <td><?php echo $segment->workSegmentDescription; ?></td>
                           <td class="text-center">
                              <!-- Add your action buttons here -->
                              <a href="#" 
                                 class="btn  btn-icon btn-sm rounded-pill btn-secondary-light edit-work-type" 
                                 data-bs-toggle="modal" 
                                 title="Edit" 
                                 data-bs-target="#manageActivityStatusModal"
                                 data-work-segment-id = "<?php echo $segment->workSegmentID; ?>"
                                 data-work-segment-name = "<?php echo $segment->workSegmentName; ?>"
                            
                                 data-work-segment-description = "<?php echo $segment->workSegmentDescription; ?>"
                                 >
                                 <i class="ri-edit-line"></i>
                              </a>

                              <a href="#" class="btn btn-sm  btn-icon rounded-pill  btn-danger-light"> 
                                 <i class="ti ti-trash"></i> 
                              </a>

                           </td>
                        </tr>                     
                        <?php 
                     }?>
                  </tbody>
               </table>
            </div>        
            <?php
         } else{
            Alert::info("No work segments found", true, array('fst-italic', 'text-center', 'font-18'));
         } ?>
      
   </div>
</div>
<?php 
// work segment modal
   echo Utility::form_modal_header('manageWorkSegmentModal', "work/manage_work_segment.php", "Manage Work Segment",array('modal-lg', 'modal-dialog-centered'), $base);
   include "includes/scripts/work/modals/manage_work_segment.php";
   echo Utility::form_modal_footer('Save  Segment', 'workSegment',  ' btn btn-success btn-sm', true);

   // Activity Status modal
 echo Utility::form_modal_header('manageActivityStatusModal', "schedule/manage_activity_status.php", "Manage Activity Status",array('modal-lg', 'modal-dialog-centered'), $base);
   include "includes/scripts/schedule/modals/manage_activity_status.php";

 echo Utility::form_modal_footer('Save  Activity Status', 'activityStatus',  ' btn btn-success btn-sm', true);
   // Work Categories
   echo Utility::form_modal_header("addWorkCategoryModal", "work/manage_work_category.php", "Manage Work Category", array('modal-md', 'modal-dialog-centered'), $base);
      include "includes/scripts/work/modals/manage_work_category.php";
   echo Utility::form_modal_footer('Save  Category', 'workCategory',  ' btn btn-success btn-sm', true);


   //  work types
   echo Utility::form_modal_header("addWorkTypeModal", "work/manage_work_type.php", "Manage Work Type", array('modal-md', 'modal-dialog-centered'), $base);
      include "includes/scripts/work/modals/manage_work_type.php";
   echo Utility::form_modal_footer('Save  Type', 'workType',  ' btn btn-success btn-sm', true);

 ?>
 <script>
      document.addEventListener('DOMContentLoaded', function() {
         // Work Category Modal
         document.querySelectorAll('.edit-work-category').forEach((button) => {
            button.addEventListener('click', function() {
               // console.log(button);
               // Get the modal form

               const form = document.querySelector('.workCategoryForm');
               // console.log(form);
               if (!form) return;
               // Get all data attributes from the button
               const data = this.dataset;
               // Map form fields to their corresponding data attributes
               const fieldMappings = {
                  'workCategoryName': 'workCategoryName',
                  'workCategoryCode': 'workCategoryCode',
                  'workCategoryDescription': 'workCategoryDescription',
                  'workCategoryID': 'workCategoryId'
               };
               // console.log(data);
               // console.log(fieldMappings);

               // Set the values of the form fields based on the data attributes
               for (const [field, dataAttr] of Object.entries(fieldMappings)) {
                  const input = form.querySelector(`#${dataAttr}`);
                  if (input) {
                     input.value = data[field];
                  }
               }

               // innitialize the tinyMCE editor for workCategoryDescription
               tinymce.init({
                  selector: '#workCategoryDescription',
                  height: 200,
                  menubar: false,
                  plugins: [
                     'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                     'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                     'insertdatetime', 'media', 'table', 'help', 'wordcount'
                  ],
                  toolbar: 'undo redo | formatselect | ' +
                     'bold italic backcolor | alignleft aligncenter ' +
                     'alignright alignjustify | bullist numlist outdent indent | ' +
                     'removeformat | help',
               });
               // handle tinyMCE editor for workCategoryDescription
               const editor = tinymce.get('workCategoryDescription');
               if(editor) {
                  setTimeout(() => {
                     editor.setContent(data.workCategoryDescription);
                  }, 100);                 
               }               
            });
         });

         document.querySelectorAll('.edit-work-type').forEach((button) => {
            button.addEventListener('click', function() {
               // console.log(button);
               // Get the modal form
               const form = document.querySelector('.workTypeForm');
               // console.log(form);
               if (!form) return;
               // Get all data attributes from the button
               const data = this.dataset;
               console.log(data);

               // Map form fields to their corresponding data attributes
               const fieldMappings = {
                  'workTypeName': 'workTypeName',
                  'workTypeCode': 'workTypeCode',
                  'workTypeDescription': 'workTypeDescription',
                  'workCategoryID': 'workCategoryId',
                  'workTypeID': 'workTypeId'
               };
               // Function to generate a code
               function generateCode(length) {
                  let result = '';
                  const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                  const charactersLength = characters.length;
                  for (let i = 0; i < length; i++) {
                     result += characters.charAt(Math.floor(Math.random() * charactersLength));
                  }
                  return result;
               }
            
               // Set the values of the form fields based on the data attributes
               for (const [field, dataAttr] of Object.entries(fieldMappings)) {
         
                  const input = form.querySelector(`#${field}`);
                 
                
                  if (input) {
                     
                     // console.log(input);
                     input.value = data[dataAttr];
                     if (field === 'workTypeCode' && input.value === '') {
                        // Generate a code if the input is empty
                        console.log(input);
                        input.value = generateCode(6);
                     }
                  }
               }

               // innitialize the tinyMCE editor for workCategoryDescription
               tinymce.init({
                  selector: '#workTypeDescription',
                  height: 200,
                  menubar: false,
                  plugins: [
                     'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                     'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                     'insertdatetime', 'media', 'table', 'help', 'wordcount'
                  ],
                  toolbar: 'undo redo | formatselect | ' +
                     'bold italic backcolor | alignleft aligncenter ' +
                     'alignright alignjustify | bullist numlist outdent indent | ' +
                     'removeformat | help',
               });
               // handle tinyMCE editor for workCategoryDescription
               const editor = tinymce.get('workTypeDescription');
               if(editor) {
                  setTimeout(() => {
                     editor.setContent(data.workTypeDescription);
                  }, 100);                 
               }               
            });
         });

      });
 </script>

