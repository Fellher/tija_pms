<div class="card custom-card">
   <div class="card-header justify-content-between">
      <h4 class="card-title "> Leave Types</h4>
      <div class="card-options">
         <button class="btn btn-primary btn-sm rounded-pill btn-wave px-4 py-0" data-bs-toggle="modal" data-bs-target="#manage_leave_types">Add Leave Types</button>
      </div>
   </div>
   <?php
   $leaveTypesList = Leave::leave_types(["Suspended"=>"N"], false, $DBConn);  ?>
   <div class="card-body">
      <div class="table-responsive">
         <table id="leave_types_table" class="table table-bordered table-striped table-vcenter js-dataTable-full table-sm" style="width: 100%;">
            <thead>
               <tr>
                  <th class="text-center">#</th>
                  <th>Leave Type Code</th>
                  <th>Leave Type Name</th>

                  <th>Leave Type Description</th>
                  <th>Action</th>
               </tr>
            </thead>
            <tbody>
               <?php
               if($leaveTypesList){
                  $i = 1;
                  foreach($leaveTypesList as $leaveType){
                     // var_dump($leaveType);
                     ?>
                     <tr>
                        <td class="text-center"><?php echo $i ?></td>
                        <td><?php echo $leaveType->leaveTypeCode ?></td>
                        <td><?php echo $leaveType->leaveTypeName ?></td>

                        <td><?php echo $leaveType->leaveTypeDescription ?></td>
                        <td class="text-end">
                           <button
                              type="button"
                              class="btn btn-sm btn-primary editLeaveType"
                              data-bs-toggle="modal"
                              data-bs-target="#manage_leave_types"
                              data-leavetypeid="<?php echo $leaveType->leaveTypeID ?>"
                              data-leavetypename="<?php echo $leaveType->leaveTypeName ?>"
                              data-leavetypecode="<?php echo $leaveType->leaveTypeCode ?>"
                              data-leavetypedescription="<?php echo $leaveType->leaveTypeDescription ?>"
                              data-leavetypesuspended="<?php echo $leaveType->Suspended ?>" >
                              Edit
                           </button>

                           <button type="button"
                              class="btn btn-sm btn-danger"
                              data-bs-toggle="modal"
                              data-bs-target="#manage_leave_types"
                              data-id="<?php echo $leaveType->leaveTypeID ?>">
                              Delete
                           </button>

                        </td>
                     </tr>
                     <?php $i++;
                  }
               } else { ?>
                  <tr>
                     <td colspan="5" class="text-center">No Leave Types Found</td>
                  </tr>
               <?php
               }
               ?>
            </tbody>
         </table>
      </div>
   </div>
</div>

<div class="card custom-card">
   <div class="card-header justify-content-between">
      <h4 class="card-title "> Leave Status</h4>
      <div class="card-options">
         <button class="btn btn-primary btn-sm rounded-pill btn-wave px-4 py-0" data-bs-toggle="modal" data-bs-target="#manage_leave_status">Add Leave Status</button>
      </div>
   </div>
   <div class="card-body">
      <?php
      $leaveStatusList = Leave::leave_status([], false, $DBConn); ?>
      <div class="table-responsive">
         <table id="leave_status_table" class="table table-bordered table-striped table-vcenter js-dataTable-full table-sm" style="width: 100%;">
            <thead>
               <tr>
                  <th class="text-center">#</th>
                  <th>Leave Status Name</th>
                  <th>Leave Status Description</th>
                  <th>Action</th>
               </tr>
            </thead>
            <tbody>
               <?php
               if($leaveStatusList){
                  $i = 1;
                  foreach($leaveStatusList as $leaveStatus){
                     // var_dump($leaveType);
                     ?>
                     <tr>
                        <td class="text-center"><?php echo $i ?></td>
                        <td><?php echo $leaveStatus->leaveStatusName ?></td>
                        <td><?php echo $leaveStatus->leaveStatusDescription ?></td>
                        <td class="text-end">
                           <button
                              type="button"
                              class="btn btn-sm btn-primary editLeaveStatus"
                              data-bs-toggle="modal"
                              data-bs-target="#manage_leave_status"
                              data-leaveStatusId="<?php echo $leaveStatus->leaveStatusID ?>"
                              data-leaveStatusName="<?php echo $leaveStatus->leaveStatusName ?>"

                              data-leaveStatusDescription="<?php echo $leaveStatus->leaveStatusDescription ?>"
                              data-suspended="<?php echo $leaveStatus->Suspended ?>" >
                              Edit
                           </button>

                           <button type="button"
                              class="btn btn-sm btn-danger"
                              data-bs-toggle="modal"
                              data-bs-target="#manage_leave_status"
                              data-id="<?php echo $leaveStatus->leaveStatusID ?>">
                              Delete
                           </button>

                        </td>
                     </tr>
                     <?php $i++;
                  }
               } else { ?>
                  <tr>
                     <td colspan="5" class="text-center">No Leave Types Found</td>
                  </tr>
               <?php
               }?>
            </tbody>
         </table>
   </div>


</div>


<!-- Add Leave Types Modal -->
<?php


      echo Utility::form_modal_header('manage_leave_types', 'leave/config/manage_leave_type.php', 'Manage Leave Types', array("modal-dialog-centered", "modal-lg"), $base, true );
      include 'includes/scripts/leave/leave_configurations/modals/manage_leave_types.php';
      echo Utility::form_modal_footer("Save Leave Type", "manage_leave_types_details", 'btn btn-primary btn-sm');

      // leave Status modal
      echo Utility::form_modal_header('manage_leave_status', 'leave/config/manage_leave_status.php', 'Manage Leave Types', array("modal-dialog-centered", "modal-md"), $base, true );
      include 'includes/scripts/leave/leave_configurations/modals/manage_leave_status.php';
      echo Utility::form_modal_footer("Save leave Status", "manage_leave_status_details", 'btn btn-primary btn-sm');


   ?>

<script>
      // Prepopulate the leaveTypeForm when the editLeaveType button is clicked
      document.addEventListener('DOMContentLoaded', function() {
         const statusLeaveButton = document.querySelectorAll('.editLeaveStatus');
         statusLeaveButton.forEach(button => {
            button.addEventListener('click', function() {
               const form = document.getElementById('leaveStatusForm');
               if (!form) return;
               // clear form data
               form.reset();

               // Get all data attributes from the button
               const data = this.dataset;

               console.log(data);

               //  map form fields to their corresponding data attributes
               const fieldMappings = {
                  'leaveStatusID': 'leavestatusid',
                  'leaveStatusName': 'leavestatusname',
                  'leaveStatusDescription': 'leavestatusdescription',
                  'Suspended': 'leavestatussuspended'
               };

               // Loop through the field mappings and set the values in the form
               for (const [field, dataAttr] of Object.entries(fieldMappings)) {
                  const value = button.getAttribute(`data-${dataAttr}`);
                  const formField = form.querySelector(`[name="${field}"]`);
                  if (formField) {
                     formField.value = value;
                  }
               }
               // If you have a checkbox for "Suspended", set its checked state
               const suspendedCheckbox = form.querySelector('[name="Suspended"]');
               if (suspendedCheckbox) {
                  suspendedCheckbox.checked = data['leavestatussuspended'] === 'Y';
               }

               // If you have a hidden input for the leaveTypeID, set its value
               const leaveTypeIDInput = form.querySelector('[name="leaveStatusID"]');
               if (leaveTypeIDInput) {
                  leaveTypeIDInput.value = data['leavestatusid'];
               }
            });
         });
         const editButtons = document.querySelectorAll('.editLeaveType');
         editButtons.forEach(button => {
            button.addEventListener('click', function() {


               const form = document.getElementById('leaveTypeForm');
               if (!form) return;

               // Get all data attributes from the button
               const data = this.dataset;

               console.log(data);

               //  map form fields to their corresponding data attributes
               const fieldMappings = {
                  'leaveTypeID': 'leavetypeid',
                  'leaveTypeName': 'leavetypename',
                  'leaveTypeCode': 'leavetypecode',
                  'leaveTypeDescription': 'leavetypedescription',
                  'Suspended': 'leavetypesuspended'
               };

               // Loop through the field mappings and set the values in the form
               for (const [field, dataAttr] of Object.entries(fieldMappings)) {
                  const value = button.getAttribute(`data-${dataAttr}`);
                  const formField = form.querySelector(`[name="${field}"]`);
                  if (formField) {
                     formField.value = value;
                  }
               }
               // If you have a checkbox for "Suspended", set its checked state
               const suspendedCheckbox = form.querySelector('[name="Suspended"]');
               if (suspendedCheckbox) {
                  suspendedCheckbox.checked = data['leavetypesuspended'] === 'Y';
               }

               // If you have a hidden input for the leaveTypeID, set its value
               const leaveTypeIDInput = form.querySelector('[name="leaveTypeID"]');
               if (leaveTypeIDInput) {
                  leaveTypeIDInput.value = data['leavetypeid'];
               }
                  // initialize tynymce editor
               tinymce.init({
                  selector: '#leaveTypeDescription'
               });

                // Handle tinyMCE editor
               const editor = tinymce.get('leaveTypeDescription'); // Make sure 'entityDescription' matches your textarea's ID
               console.log(editor);
               if (editor) {
                  // Wait for a brief moment to ensure tinyMCE is fully initialized
                  setTimeout(() => {
                     console.log(data['leavetypedescription']);
                     // set the content of the editor
                     // editor.setContent(data['leaveTypeDescription'] || '');
                     editor.setContent(data['leavetypedescription'] || '');
                  }, 100);
               }

            });
         });
      });



</script>

