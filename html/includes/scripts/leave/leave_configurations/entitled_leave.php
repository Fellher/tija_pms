<div class="card custom-card my-3 shadow-lg">
   <div class="card-header justify-content-between">
      <h4 class="card-title "> Leave Entitlement</h4>
      <div class="card-options">
         <button class="btn btn-primary btn-sm rounded-pill btn-wave px-4 py-0" data-bs-toggle="modal" data-bs-target="#manage_Leave_entitlement">Add Leeave Entitlement</button>        
      </div>
   </div>

   <?php
   $entitledLeaveList =Leave::leave_entitlements(['Suspended'=>'N'],false, $DBConn);
   $entitledLeaveListJson = json_encode($entitledLeaveList);

   $leaveTypes = Leave::leave_types(array('Lapsed'=>'N'), false, $DBConn);
   ?>
   

   <div class="card-body">
      <div class="table-responsive">
         <table id="entitled_leave_table" class="table table-bordered table-striped table-vcenter js-dataTable-full" style="width: 100%;">
            <thead>
               <tr>
                  <th class="text-center">#</th>
                  <th>Leave Type</th>
                  <th>Entitlement</th>
                  <th>Max Days Per Application</th>
                  <th>Action</th>
               </tr>
            </thead>
            <tbody>
               <?php 
               if($entitledLeaveList) {
                  foreach ($entitledLeaveList as $key  => $leave) {
                     
                     // var_dump($leave);?>
                     <tr>
                        <td class="text-center"><?php echo $key + 1; ?></td>
                        <td><?php echo $leave->leaveTypeName; ?></td>
                        <td><?php echo $leave->entitlement; ?></td>
                        <td><?php echo $leave->maxDaysPerApplication ?? 'Unlimited'; ?></td>
                        <td class="text-center">
                           <button 
                           type="button" 
                           class="btn btn-sm btn-primary editEntitledLeaveBtn" 
                           data-bs-toggle="modal" 
                           data-bs-target="#manage_Leave_entitlement" 
                           data-leave-entitlement-id="<?php echo $leave->leaveEntitlementID; ?>" 
                           data-leave-type-id="<?php echo $leave->leaveTypeID; ?>" 
                           data-entitlement="<?php echo $leave->entitlement; ?>"
                           data-max-days-per-application="<?php echo $leave->maxDaysPerApplication ?? ''; ?>"                   
                           >
                           <i class="fas fa-edit"></i>
                           Edit
                           </button>
                           <button type="button" class="btn btn-sm btn-danger delete-entitled-leave"  data-leave-entitlement-id="<?php echo $leave->leaveEntitlementID; ?>" >Delete</button>
                        </td>
                     </tr>
                     <?php
                     # code...
                  }
               } else {
                  ?>
                  <tr><td colspan="6" class="text-center fst-italic">No entitled leave found</td></tr>
                  <?php
               }?>           
            </tbody>
         </table>
      </div>
   </div>
</div>

<?php
 echo Utility::form_modal_header('manage_Leave_entitlement', 'leave/manage_Leave_entitlement.php', 'Manage Holidays', array("modal-dialog-centered", "modal-lg"), $base, true );
 include 'includes/scripts/leave/leave_configurations/modals/manage_Leave_entitlement.php';
 echo Utility::form_modal_footer("Save Work Type", "manage_rate_type_details", 'btn btn-primary btn-sm'); ?> 

<script>
$(document).ready(function() {
    // Handle edit button click
    $('.editEntitledLeaveBtn').on('click', function() {
        var leaveEntitlementId = $(this).data('leave-entitlement-id');
        var leaveTypeId = $(this).data('leave-type-id');
        var entitlement = $(this).data('entitlement');
        var maxDaysPerApplication = $(this).data('max-days-per-application');
        
        // Populate the form
        $('#leaveEntitlementID').val(leaveEntitlementId);
        $('#leaveTypeID').val(leaveTypeId);
        $('#entitlement').val(entitlement);
        $('#maxDaysPerApplication').val(maxDaysPerApplication);
    });
    
    // Handle delete button click
    $('.delete-entitled-leave').on('click', function() {
        var leaveEntitlementId = $(this).data('leave-entitlement-id');
        
        if (confirm('Are you sure you want to delete this leave entitlement?')) {
            // You can implement delete functionality here
            console.log('Delete leave entitlement ID:', leaveEntitlementId);
        }
    });
});
</script> 
