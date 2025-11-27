<?php
if(!$isValidUser) {
    Alert::info("You need to be logged in as a valid administrator to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}
$employeeID=(isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) : $userDetails->ID;
$employeeDetails = Employee::employees(array('ID'=>$employeeID), true, $DBConn);
$orgDataID= isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : $employeeDetails->orgDataID;
$entityID= isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : $employeeDetails->entityID;
?>

<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb border-bottom">
   <h1 class="page-title fw-medium fs-24 mb-0">Tija Leave Application</h1>
   <div class="ms-md-1 ms-0">
      <nav>
         <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $s ?></a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $ss ?></a></li>
            <li class="breadcrumb-item active d-inline-flex" aria-current="page">Leave Application</li>
         </ol>
      </nav>
   </div>
</div>

<div class="card custom-card my-3 shadow-lg">
   <div class="card-header justify-content-between">
      <h4 class="card-title "> Apply Leave</h4>
      <!-- <div class="card-options">
         <button class="btn btn-primary btn-sm rounded-pill btn-wave px-4 py-0" data-bs-toggle="modal" data-bs-target="#apply_leave">Add Leeave Entitlement</button>
      </div> -->
   </div>

</div>
<div class="row">
<?php
$leaveTypes = Leave::leave_types(array('Lapsed'=>'N'), false, $DBConn);
$leaveTypesJson = json_encode($leaveTypes);
$leavePeriods = Leave::leave_periods(array('Lapsed'=>'N'), false, $DBConn);
$leaveEntytlements = Leave::leave_entitlements(array('Suspended'=>'N', 'entityID'=>$entityID), false, $DBConn);
// var_dump($leaveEntytlements[2]);
// var_dump($employeeDetails);
if($leaveEntytlements) {
    foreach ($leaveEntytlements as $key  => $leave) {
        if($leave->leaveSegment && strtolower($leave->leaveSegment) != strtolower($employeeDetails->gender)) {
            continue;
        }?>
         <div class="col-xl-2">
            <div class="card custom-card border-top-card border-top-primary rounded-3">
                  <div class="card-body">
                     <div class="text-center">
                        <!-- <span class="avatar avatar-md bg-primary shadow-sm avatar-rounded mb-2">
                              <i class="ri-briefcase-2-line fs-16"></i>
                        </span> -->
                        <p class="fs-14 fw-semibold mb-2"><?= $leave->leaveTypeName ?></p>
                        <div class="d-flex align-items-center justify-content-center flex-wrap">
                           <div>
                              <h5 class="mb-0 fw-semibold d-block"><?= $leave->entitlement; ?> </h5>
                              <span class="badge bg-success-transparent rounded-pill ms-1">(Balance Days)</span>
                              </div>
                        </div>
                     </div>
                  </div>
            </div>
         </div>
      <?php
      }
   } else {
      ?>
      <div class="col-12">
            <div class="alert alert-info text-center fst-italic">No leave entitlement found</div>
      </div>
      <?php
   }

   $myLeaveApplications = Leave::leave_applications_full(array('Suspended'=>'N', 'employeeID'=>$userDetails->ID), false, $DBConn);
   $leaveApplicationsMini = Leave::leave_applications(array('Suspended'=>'N', 'employeeID'=>$userDetails->ID), false, $DBConn);
   // var_dump($leaveApplicationsMini);
   $myLeaveApplicationJson = json_encode($myLeaveApplications);

   // var_dump($myLeaveApplications);
      ?>

   <div class="col-12">
      <div class="card custom-card my-3 shadow-lg">
         <div class="card-header justify-content-between">
            <h4 class="card-title ">  My Leave Applications</h4>
            <div class="card-options">
               <button class="btn btn-primary btn-sm rounded-pill btn-wave px-4 py-0" data-bs-toggle="modal" data-bs-target="#apply_leave">Apply Leave</button>
            </div>
         </div>
         <div class="card-body">
            <div class="table-responsive">
               <table id="leave_application_table" class="table table-bordered table-striped table-vcenter js-dataTable-full" style="width: 100%;">
                  <thead>
                     <tr>
                        <th class="text-center">#</th>
                        <th>Leave Type</th>
                        <th>Leave Period</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Days</th>
                        <th>Status</th>
                        <th>Action</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php
                     if($myLeaveApplications) {
                        foreach ($myLeaveApplications as $key  => $application) {
                           // var_dump($application);

                           // var_dump($leave);?>
                           <tr>
                              <td class="text-center"><?php echo $key + 1; ?></td>
                              <td><?php echo $application->leaveTypeName; ?></td>
                              <td><?php echo  " (" . $application->leavePeriodStartDate . " to " . $application->leavePeriodEndDate . ")"; ?></td>
                              <td><?php echo $application->startDate; ?></td>
                              <td><?php echo $application->endDate; ?></td>
                              <td><?php echo $application->noOfDays ? $application->noOfDays : Leave::countWeekdays($application->startDate, $application->endDate); ?></td>
                              <td><?php echo $application->leaveStatusName; ?></td>
                              <td class="text-end">
                                 <button
                                 type="button"
                                 class="btn btn-sm btn-primary editLeaveApplicationBtn"
                                 data-bs-toggle="modal"
                                 data-bs-target="#apply_leave"
                                 data-leave-entitlement-id="<?php echo $application->leaveEntitlementID; ?>"
                                 data-leave-type-id="<?php echo $application->leaveTypeID; ?>"
                                 data-entitlement="<?php echo $application->entitlement; ?>"
                                 data-leave-period-id ="<?php echo $application->leavePeriodID; ?>"
                                 data-start-date="<?php echo $application->startDate; ?>"
                                 data-end-date="<?php echo $application->endDate; ?>"
                                 data-leave-status-id="<?php echo $application->leaveStatusID; ?>"
                                 data-employee-id="<?php echo $application->employeeID; ?>"
                                 data-leave-comments="<?php echo $application->leaveComments; ?>"
                                 data-leave-files="<?php echo $application->leaveFiles; ?>"
                                 data-entity-id="<?php echo $application->entityID; ?>"
                                 data-org-data-id="<?php echo $application->orgDataID; ?>"
                                 data-leave-application-id="<?php echo $application->leaveApplicationID; ?>"
                                 >
                                 <i class="fas fa-edit"></i>
                                 Edit
                                 </button>
                                 <button type="button" class="btn btn-sm btn-danger delete-entitled-leave"  data-leave-entitlement-id="<?php echo $application->leaveEntitlementID; ?>" >Delete</button>
                                 <?php
                                 if($application->leaveStatusID !== 2 ) {

                                    if(in_array((int)$application->leaveStatusID, [2, 3], true)) {
                                       ?>
                                       <button type="button" class="btn btn-sm btn-success-outline" disabled>Pending Approval</button>
                                       <?php
                                    } else {
                                       ?>
                                    <button
                                       type="button"
                                       class="btn btn-primary btn-sm requestApprovalBtn"
                                       data-bs-toggle="modal"
                                       data-bs-target="#request_approval_modal"
                                       data-leave-application-id="<?php echo $application->leaveApplicationID; ?>"
                                       data-employee-id="<?php echo $application->employeeID; ?>"
                                       data-leave-status-id="<?php echo $application->leaveStatusID; ?>"
                                       data-leave-type-id="<?php echo $application->leaveTypeID; ?>"
                                       data-start-date="<?php echo date('F j, Y', strtotime($application->startDate)); ?>"
                                       data-end-date="<?php echo date('F j, Y', strtotime($application->endDate)); ?>"
                                       data-leave-days = "<?php echo $application->noOfDays ? $application->noOfDays : Leave::countWeekdays($application->startDate, $application->endDate); ?>"
                                       data-leave-type-name="<?php echo $application->leaveTypeName; ?>"

                                       data-leave-comments="<?php echo $application->leaveComments; ?>"
                                       data-leave-files="<?php echo $application->leaveFiles; ?>"
                                       data-entity-id="<?php echo $application->entityID; ?>"
                                       data-org-data-id="<?php echo $application->orgDataID; ?>"
                                    >
                                    Request Approval
                                    </button>
                                    <?php

                                    }
                                 } ?>
                              </td>
                           </tr>
                           <?php
                           # code...
                        }
                     } else {
                        ?>
                        <tr><td colspan="6" class="text-center fst-italic">No entitled leave found</td></tr>
                        <?php
                     }
                     ?>
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </div>
   <?php
 echo Utility::form_modal_header('apply_leave', 'leave/apply_leave.php', 'Manage Holidays', array("modal-dialog-centered", "modal-lg"), $base, true );
 include 'includes/scripts/leave/modals/apply_leave.php';
 echo Utility::form_modal_footer("Apply Leave", "manage_rate_type_details", 'btn btn-primary btn-sm'); ?>
 <!-- Request Approval Modal -->
   <?php
   echo Utility::form_modal_header('request_approval_modal', 'leave/apply_leave.php', 'Request Approval', array("modal-dialog-centered", "modal-lg"), $base, true );
   // include 'includes/scripts/leave/modals/request_approval.php';
   echo Utility::form_modal_footer("Request Approval", "request_approval", 'btn btn-primary btn-sm'); ?>

 <script>
 document.addEventListener("DOMContentLoaded", function(event) {
   document.querySelectorAll('.requestApprovalBtn').forEach(button => {
      button.addEventListener('click', function() {
         // Get the data attributes from the button
         const data = this.dataset;
         console.log(data);

         // Populate the modal with the data
         const modal = document.getElementById('request_approval_modal');
         const modalBody = modal.querySelector('.modal-body');
         const modalFooter = modal.querySelector('.modal-footer');
         modalBody.innerHTML = `
            <p class="fs-18 fst-italic">Are you sure you want to request approval for <span class="t600">${data.leaveTypeName}</span> leave application for the period ${data.startDate} to ${data.endDate} ?</p>
            <p class="fs-14 fst-italic">Leave Days: <span class="t600">${data.leaveDays}</span></p>
            <input type="text" name="leaveApplicationID" class="form-control form-control-sm" id="leave_application_id" value="${data.leaveApplicationId}" hidden>
            <input type="text" name= "employeeID" class="form-control form-control-sm" id="employee_id" value="${data.employeeID}" hidden>
            <input type="text" name="leaveStatusID" class="form-control form-control-sm" id="leave_status_id" value="3" hidden>


         `;
         modalFooter.innerHTML = `
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary" id="confirm_request_approval">Confirm Request Approval</button>
         `;
         // const leaveApplicationID = this.dataset.leaveApplicationId;
         // const employeeID = this.dataset.employeeId;
         // const leaveStatusID = this.dataset.leaveStatusId;
         // const leaveTypeID = this.dataset.leaveTypeId;
         // const startDate = this.dataset.startDate;
         // const endDate = this.dataset.endDate;
         // const noOfDays = this.dataset.noOfDays;
         // const leaveComments = this.dataset.leaveComments;
         // const leaveFiles = this.dataset.leaveFiles;
         // const entityID = this.dataset.entityId;
         // const orgDataID = this.dataset.orgDataId;

         // const modal = document.getElementById('request_approval_modal');
         // const modalBody = modal.querySelector('.modal-body');
         // const modalFooter = modal.querySelector('.modal-footer');

         // modalBody.innerHTML = `
         //    <p>Are you sure you want to request approval for this leave application?</p>
         //    <p>Leave Application ID: ${leaveApplicationID}</p>
         //    <p>Employee ID: ${employeeID}</p>
         //    <p>Leave Type ID: ${leaveTypeID}</p>
         //    <p>Start Date: ${startDate}</p>
         //    <p>End Date: ${endDate}</p>
         //    <p>No. of Days: ${noOfDays}</p>
         //    <p>Leave Comments: ${leaveComments}</p>
         //    <p>Leave Files: ${leaveFiles}</p>
         // `;

         // modalFooter.innerHTML = `
         //    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
         //    <button type="button" class="btn btn-primary" id="confirm_request_approval">Confirm Request Approval</button>
         // `;

         // const confirmButton = modalFooter.querySelector('#confirm_request_approval');
         // confirmButton.addEventListener('click', function() {
         //    // Update the statusID to 3
         //    this.dataset.leaveStatusId = 3;
         //    // Add your logic to update the leave application status here
         //    console.log('Leave application status updated to 3');
         // });
      });
   });
 });
 </script>



</div>


