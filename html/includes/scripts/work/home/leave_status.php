<?php 
$leaveStatus = Leave::leave_status([], false, $DBConn);?>

<script type="text/javascript">
   let leaveStatus = <?php echo json_encode($leaveStatus); ?>;
</script>
<div class="container-fluid mt-3">
   <div class="card custom-card border-top border-primary border-4 border-bottom-0 border-start-0 border-end-0">
      <div class="card-header border-0 justify-content-between d-between">
         <h4 class="card-title">Leave Status</h4>
         <div class="card-options">
            <a href="javascript:void(0);" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manageLeaveStatus">Add Leave Category</a>
         </div>
      </div>
      <div class="card-body">
         <?php 
         if(isset($leaveStatus) && is_array($leaveStatus) && count($leaveStatus) > 0) {?>
            <div class="table-responsive">
               <table class="table table-bordered table-sm table-striped table-vcenter text-nowrap mb-0" id="leaveStatusTable">
                  <thead>
                     <tr>
                        <th class="text-center">#</th>
                        <th>Leave Status Name</th>
                        <th>Description</th>
                        <th class="text-center">Actions</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php 
                     foreach ($leaveStatus as $key => $status) {?>
                        <tr id="<?php echo $status->leaveStatusID; ?>">
                           <td class="text-center"><?php echo $key + 1; ?></td>
                           <td><?php echo $status->leaveStatusName; ?></td>
                           <td><?php echo $status->leaveStatusDescription; ?></td>
                           <td class="text-center">
                           <button
                              type="button" 
                              class="btn btn-sm btn-primary editLeaveStatus" 
                              data-bs-toggle="modal" 
                              data-bs-target="#manageLeaveStatus" 
                              data-leaveStatusId="<?php echo $status->leaveStatusID ?>"
                              data-leaveStatusName="<?php echo $status->leaveStatusName ?>"
                             
                              data-leaveStatusDescription="<?php echo $status->leaveStatusDescription ?>"
                              data-suspended="<?php echo $status->Suspended ?>" >
                              Edit
                           </button>

                           <button type="button" 
                              class="btn btn-sm btn-danger" 
                              data-bs-toggle="modal" 
                              data-bs-target="#manageLeaveStatus" 
                              data-id="<?php echo $status->leaveStatusID ?>">
                              Delete
                           </button>
                           </td>
                        </tr>
                     <?php
                     } ?>
                  </tbody>
               </table>
            </div>
         <?php
         } else {?>
            <div class="alert alert-warning" role="alert">
               No leave status found.
            </div>
         <?php
         } ?>
      </div>
   </div>
</div>
<?php 

// leave status modal
echo Utility::form_modal_header("manageLeaveStatus", "leave/manageLeaveStatus.php", "Manage Leave Status", array('modal-md', 'modal-dialog-centered'), $base);  
include "includes/scripts/leave/leave_configurations/modals/manage_leave_status.php";
echo Utility::form_modal_footer('Save Leave Status', 'leaveStatus',  ' btn btn-success btn-sm', true);?>


<script>
      // Prepopulate the leaveTypeForm when the editLeaveType button is clicked
      document.addEventListener('DOMContentLoaded', function() {
         const statusLeaveButton = document.querySelectorAll('.editLeaveStatus');
         statusLeaveButton.forEach(button => {
            button.addEventListener('click', function() {
               const form = document.getElementById('leaveStatusForm');
               if (!form) return;
               console.log(form);
               // clear form data   
               // form.reset();

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

               // Initialize tinyMCE editor for leaveStatusDescription
               tinymce.init({
                  selector: '#leaveStatusDescription',
                 
               })
               // Handle tinyMCE editor
               const editor = tinymce.get('leaveStatusDescription'); // Make sure 'leaveStatusDescription' matches your textarea's ID
               console.log(editor);
               // Check if the editor is initialized
               // and set the content
               // Wait for a brief moment to ensure tinyMCE is fully initialized
               if (editor) {
                  // Wait for a brief moment to ensure tinyMCE is fully initialized
                  setTimeout(() => {
                     editor.setContent(data['leavestatusdescription'] || '');
                  }, 100);
               }
            });
         });
         // Handle delete button clicks
         document.querySelectorAll('.deleteLeaveStatus').forEach(button => {
            button.addEventListener('click', function() {
               // Get entity details from data attributes
               const leaveStatusID = this.getAttribute('data-id');
               const leaveStatusName = this.getAttribute('data-leavestatusname');

               // Find the delete modal elements
               const leaveStatusNameSpan = document.getElementById('leaveStatusNameToDelete');
               const leaveStatusIDInput = document.querySelector('#deleteLeaveStatusModal input[name="leaveStatusID"]');

               // Set the values in the modal
               if (leaveStatusNameSpan) {
                  leaveStatusNameSpan.textContent = leaveStatusName;
               }

               if (leaveStatusIDInput) {
                  leaveStatusIDInput.value = leaveStatusID;
               }
            });
         });
         // Initialize DataTable
         // $('#leaveStatusTable').DataTable({
         //    "order": [[0, "asc"]],
         //    "pageLength": 10,
         //    "lengthMenu": [5, 10, 25, 50, 100],
         //    "language": {
         //       "lengthMenu": "_MENU_",
         //       "search": "_INPUT_",
         //       "searchPlaceholder": "Search Leave Status"
         //    }
         // });
      });

</script>