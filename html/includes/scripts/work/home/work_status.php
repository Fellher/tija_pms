 <?php  $taskStatus = TimeAttendance::task_statuses(['Suspended' => 'N'], false, $DBConn); ?>
<div class="container-fluid mt-3">
   <div class="card custom-card my-4 shadow-lg">
      <div class="card-header border-0 justify-content-between d-between">
         <h4 class="card-title">Task Statuses</h4>
         <div class="card-options">
            <a href="javascript:void(0);" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manageTaskStatuses">Add Task Status</a>
         </div>
      </div>
      <div class="card-body"> 
         <div class="table-responsive">
            <table class="table table-sm table-bordered table-striped table-hover">
               <thead>
                  <tr>
                     <th>#</th>
                     <th>Status Name</th>
                     <th>Status Description</th>
                     <!-- <th>Color</th> -->
                     <th>Actions</th>
                  </tr>
               </thead>
               <tbody>
                  <?php 
                  if ($taskStatus) {
                     $st = 0;
                    
                     foreach ($taskStatus as $status):
                     $st++;
                     ?>
                     <tr>
                        <td><?php echo $st; ?></td>

                        <td><?php echo $status->taskStatusName; ?></td>
                        <td><?php echo $status->taskStatusDescription; ?></td>
                        <!-- <td><span class="badge" style="background-color: <?php echo $status->colorVariableID; ?>;"><?php echo $status->colorVariableID; ?></span></td> -->
                        <td>
                           <a href="#" data-bs-toggle="modal" data-bs-target="#manageTaskStatuses"  class="btn btn-sm btn-primary">Edit</a>
                           <a href="#" data-bs-toggle="modal" data-bs-target="#delete" class="btn btn-sm btn-danger">Delete</a>
                        </td>
                     </tr>
                     <?php endforeach; 
                  } else {
                     echo "<tr><td colspan='4' class='text-center'>No task statuses found.</td></tr>";
                  
                  }
                  ?>
               </tbody>
            </table>
         </div>
      </div>
   </div>
</div>
<?php
echo Utility::form_modal_header("manageTaskStatuses", "time_attendance/manage_task_statuses.php", "Manage Task Statuses", array('modal-md', 'modal-dialog-centered'), $base);
include "includes/scripts/time_attendance/modals/manage_task_statuses.php";
echo Utility::form_modal_footer("Save Status", "manageTskStatus", 'btn btn-primary btn-sm');

echo Utility::form_modal_header("delete", "time_attendance/delete_task_status.php", "Delete Task Status", array('modal-sm', 'modal-dialog-centered'), $base);
echo Utility::form_modal_footer("Delete Status", "deleteTskStatus", 'btn btn-danger btn-sm');
