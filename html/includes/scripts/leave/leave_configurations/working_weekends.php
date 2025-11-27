   <div class="card custom-card my-3 shadow-lg">
      <div class="card-header justify-content-between">
         <h4 class="card-title "> Working Weekends</h4>
         <div class="card-options">
            <button class="btn btn-primary btn-sm rounded-pill btn-wave px-4 py-0" data-bs-toggle="modal" data-bs-target="#manage_working_weekend">Add Working Weekend</button>        
         </div>
      </div>
      <?php
      $workingWeekendList = '';?>

      <div class="card-body">
         <div class="table-responsive">
            <table id="working_weekends_table" class="table table-bordered table-striped table-vcenter js-dataTable-full table-sm" style="width: 100%;">
               <thead>
                  <tr>
                     <th class="text-center">#</th>
                     <th> Name</th>
                     <th>Date</th>
                     <th> FullDay/HalfDay </th>
                     <th>Work Type</th>
                     <th>Work Type Description</th>
                     <th>Action</th>
                  </tr>
               </thead>
               <tbody>
                  <?php echo $workingWeekendList ?>
               </tbody>
            </table>
         </div>
      </div>
   </div>
   <!-- Add Holidays Modal -->
   <?php  
      echo Utility::form_modal_header('manage_working_weekend', 'leave/manage_working_weekend.php', 'Manage Working Weekend', array("modal-dialog-centered", "modal-lg"), $base, true );
      include 'includes/scripts/leave/leave_configurations/modals/manage_working_weekend.php';
      echo Utility::form_modal_footer("Save Work Type", "manage_working_weekend_details", 'btn btn-primary btn-sm'); 
   ?>
      
   <!-- Edit Holidays Modal -->
      