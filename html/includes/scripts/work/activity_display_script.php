<div class="">
   <div class="card custom-card">
      <div class="card-header d-flex justify-content-between border-bottom-0 pb-0">
         <h3 class="card-title t300 font-20"><?= (isset($activityTitle) ? $activityTitle : "Activities") ?></h3>
         <?php

         if(isset($addActivity) && $addActivity) { ?>
            <a
               href="#manage_activity"
               data-bs-toggle="modal"
               role="button"
               aria-expanded="false"
               aria-controls="manage_activity"
               class= "btn btn-primary btn-sm rounded"
            >
               <i class="ri-add-line"></i> Add Activity
            </a>
            <?php
         } ?>

      </div>
      <div class="card-body">
         <div class="row">
            <div class="col-md-12">
               <?php
               $workTypes = Work::work_types([], false, $DBConn);
               $activityStatuses = Schedule::activity_status([], false, $DBConn);

               if(!$activities){
                  Alert::info("No activities found", true, array('fst-italic', 'text-center', 'font-18'));
               } else {
                  // var_dump($activities[0]);
                  // Check if the activities are recurring and include recurring activities
                  $recurringActivities = array_filter($activities, function($activity) {
                     return $activity->recurring == 'Y' || $activity->recurring == 'recurring';
                  });
                  $newActivity = array();
                  // var_dump($recurringActivities);
                  if($recurringActivities){
                     foreach ($recurringActivities as $rekey => $activity) {
                        // echo "<h4 class='bg-light pt-2 pb-1 px-2 mb-3 border-bottom border-dark fs-16'> original Activity</h4>";
                        // var_dump($activity);
                        $recurrencies = Schedule::recurring_activity_instances(['activityID' =>$activity->activityID], false, $DBConn);
                        // echo "<h4 class='bg-light pt-2 pb-1 px-2 mb-3 border-bottom border-dark fs-16'> Recurring Activities for {$activity->activityName} </h4>";
                        // var_dump($recurrencies);

                        if($recurrencies){
                           foreach ($recurrencies as $key => $recurrency) {
                              $newActivity[] = $activity;
                              unset($activities[$rekey]);
                              // var_dump($recurrency);
                              $activity->activityDate = $recurrency->activityInstanceDate;
                              $activity->activityStartTime = $recurrency->activityinstanceStartTime;
                              $activity->activityDurationEndTime = $recurrency->activityInstanceDurationEndTime;
                              $activity->activityStatusID = $recurrency->activityStatusID;
                              $activity->instance = 'Y';
                              $activity->recurringInstanceID = $recurrency->recurringInstanceID;
                              // include "includes/scripts/schedule/activity_listing.php";


                           }
                        }
                     }
                  }
                  // $activityCount = count($newActivity);
                  //   echo "<h4 class='bg-light pt-2 pb-1 px-2 mb-3 border-bottom border-dark fs-16'> Recurring Activities {$activityCount} </h4>";

                  // merge the new activities with the original activities
                  $activities = array_merge($activities, $newActivity);


                  //?activities completed this month
                  $completedThisMonth = array_filter($activities, function($activity) {
                     return $activity->activityStatusID == 4 && date('Y-m') == date('Y-m', strtotime($activity->activityDate));
                  });

                  // var_dump($activities);

                  /**
                   * *filter $activities for activityStatusID of 4(*Completed Activity)
                   * * filter for all completed activities
                   */
                  $completedActivities = array_filter($activities, function($activity) {
                     return $activity->activityStatusID == 4;
                  });


                  // Filter for overdue activities
                  $overdueActivities = array_filter($activities, function($activity) use ($dt){
                     return $activity->activityStatusID != 4 && $activity->activityDate < date('Y-m-d');
                  });

                  /*
                     *filter $activities for future Activities
                   * * activityStatusID of not 4(* NOt Completed Activity) and activityDate > today
                   * * filter for future activities
                  */



                  $plannedActivities = array_filter($activities, function($activity) use ($dt){
                     return $activity->activityStatusID != 4 && $activity->activityDate >= date('Y-m-d', strtotime('+1 day')) && $activity->activityDate <= date('Y-m-d', strtotime('+14 days'));
                  });

                  // filter $activities for activityStatusID of not 4(*Not Completed Activity) and activityDate is today
                  $activitiesDueToday = array_filter($activities, function($activity) use ($dt){
                     return $activity->activityStatusID != 4 && $activity->activityDate == date('Y-m-d');
                  });
                  // filter $activities for activityStatusID of 4(*Completed Activity) and activityDate < today
                  // filter for past completed activities
                  $pastCompletedActivities = array_filter($activities, function($activity) {
                     return $activity->activityStatusID == 4 && $activity->activityDate < date('Y-m-d');
                  });
                  // ?Overdue Activities
                  if($overdueActivities){?>
                    <h4 class="bg-light pt-2 pb-1 px-2 mb-3 border-bottom border-dark fs-16"> Overdue Activities </h4>
                  <?php
                     foreach ($overdueActivities as $key => $activity) {
                        include "includes/scripts/schedule/activity_listing.php";
                     }
                  } ?>
                  <!-- Activities Due Today -->
                  <h4 class="bg-light pt-2 pb-1 px-2 mb-3 border-bottom border-dark fs-16"> Activities Due Today </h4>
                  <?php
                  if($activitiesDueToday){
                     foreach ($activitiesDueToday as $key => $activity) {
                        include "includes/scripts/schedule/activity_listing.php";
                     }
                  } else {
                     Alert::info("No Activities due today", true, array('fst-italic', 'text-center', 'font-18'));
                  }
                  /**
                   * * planned activities for the next 30 days
                   * * filter $activities for activityStatusID of not 4(*Not Completed Activity) and activityDate > today
                   */
                  if($plannedActivities){
                     echo ' <h4 class="bg-light pt-2 pb-1 px-2 mb-3 border-bottom border-dark fs-16">  Due in the next 14 days </h4>';
                     foreach ($plannedActivities as $key => $activity) {
                        include "includes/scripts/schedule/activity_listing.php";
                     }
                  }
                  /**
                   * * Past activities
                   * * filter $activities for activityStatusID of 4(*Completed Activity) and activityDate < today
                   * * filter for past completed activities
                  ?*/
                  if($pastCompletedActivities){
                     echo ' <h4 class="bg-light pt-2 pb-1 px-2 mb-3 border-bottom border-dark fs-16">  Past Completed Activities </h4>';
                     foreach ($pastCompletedActivities as $key => $activity) {
                        include "includes/scripts/schedule/activity_listing.php";
                     }
                  }
               }?>
            </div>
         </div>
      </div>
   </div>
</div>
<?php

/*
* Code to add and edit tasks
*/
$modalTitle = isset($activityTitle) ? "Manage {$activityTitle}" : "Manage Activity";

echo Utility::form_modal_header("manage_activity", "schedule/manage_activity.php", $modalTitle, array('modal-lg', 'modal-dialog-centered'), $base);
include 'includes/scripts/schedule/modals/manage_activity.php';
echo Utility::form_modal_footer("save activity", "manage_activity_submit_btn", 'btn btn-primary btn-sm');

// Code to postpone activities
echo Utility::form_modal_header("postponeActivity", "schedule/manage_activity_postpone_instance.php", "Manage Activity", array('modal-lg', 'modal-dialog-centered'), $base);
include 'includes/scripts/schedule/modals/manage_activity_postpone.php';
echo Utility::form_modal_footer("postpone activity", "postpone_activity_submit_btn", 'btn btn-primary btn-sm');

// Complete activity modal
echo Utility::form_modal_header("complete_activity", "schedule/manage_activity_complete.php", "Complete Activity", array('modal-lg', 'modal-dialog-centered'), $base);
include 'includes/scripts/schedule/modals/manage_activity_complete.php';
echo Utility::form_modal_footer("Complete activity", "manage_activity_complete_submit_btn", 'btn btn-primary btn-sm');
?>
<script>
   document.addEventListener('DOMContentLoaded', function() {
      let workTypeArray = <?= json_encode($workTypes) ?>;
      let employeeID = <?= $employeeID ?>;
      /* * postpone activity
      * This script handles the postponing of activities.
      * It includes the modal for postponing activities and the necessary JavaScript to handle the interactions.
      * The script also includes the display of existing activities with options to postpone them.
      * @package    Tija CRM
      * @subpackage Activity Management
      */
      document.querySelectorAll('.postponeActivityBtn').forEach(button=>{
         button.addEventListener('click', function(){
            const form = document.getElementById('postponeActivityForm');
            if (!form) return;
            // console.log(button);
            // Get all data attributes from the button
            const data = this.dataset;
            console.log(data);

            // Map form fields to their corresponding data attributes
            const fieldMappings = {
               'activityID': 'activityId',
               'activityDate': 'activityDate',
               'activityStartTime': 'activityStartTime',
               'activityName': 'activityName',
               'activityDurationEndDate': 'activityDurationEndDate',
               'activityDurationEndTime': 'activityDurationEndTime',
            }

            // fill the form inputs
            // hidden inputs

            for (const [fieldName, dataAttribute] of Object.entries(fieldMappings)) {
               const input = form.querySelector(`[name="${fieldName}"]`);
               // console.log(input);
               if (input) {
                  input.value = data[dataAttribute] || '';
               }
            }

            const hiddens = [ 'activityDurationEndDate', 'activityDurationEndTime'];
            hiddens.forEach(hiddenName => {
               const hidden = form.querySelector(`[name="${hiddenName}"]`);
               console.log(hidden);
               if (hidden && data[hiddenName]) {
                  // console.log(hiddenName);
                  // console.log(data[hiddenName]);
                  // hidden.value = data[hiddenName] || '';
                  hidden.parentElement.parentElement.classList.remove('d-none');
                  // const parentElement = hidden.parentElement;
                  // console.log( parentElement)
               }
               else {
                  hidden.parentElement.parentElement.classList.add('d-none');
               }
            });
         })
      })

      /* *complete activity
      * This script handles the completion of activities.
      * It includes the modal for completing activities and the necessary JavaScript to handle the interactions.
      * The script also includes the display of existing activities with options to complete them.
      * @package    Tija CRM
      * @subpackage Activity Management
      */
      // Event listener for complete activity buttons
      document.querySelectorAll('.completeActivityBtn').forEach(button => {
         let completeSubmitBtn = document.querySelector('#manage_activity_complete_submit_btn');
         button.addEventListener('click', function(){
            // get the form
            const form = document.getElementById('completeActivityForm');
            if (!form) return;
            // Get all data attributes from the button
            const data = this.dataset;
            console.log(data);
            // Map form fields to their corresponding data attributes
            const fieldMappings = {
               'activityID': 'activityId',
               'activityDate': 'activityDate',
               'activityName': 'activityName',
               'workSegmentID': 'workSegmentId',
               'employeeID': 'employeeId',
               'taskActivityID': 'taskActivityId',
               'taskType': 'taskType',
               'instance': 'instance',
               'recurringInstanceID': 'recurringInstanceId',


            }

            // fill the form inputs
            // hidden inputs
            for (const [fieldName, dataAttribute] of Object.entries(fieldMappings)) {
               const input = form.querySelector(`[name="${fieldName}"]`);

               console.log(input);
               if (input) {
                  input.value = data[dataAttribute] || '';
               }
            }
            document.querySelector('.activityName').innerHTML = data['activityName'];
            // check if the workTypeID and activityDuration is not empty on click of complete submit button
            completeSubmitBtn.addEventListener('click', function(e) {
               const workTypeID = form.querySelector('[name="workTypeID"]');
               const activityDuration = form.querySelector('[name="activityDuration"]');
               e.preventDefault();
               e.stopPropagation();
               console.log(workTypeID.value);
               console.log(activityDuration.value);
               if (workTypeID.value === '' || activityDuration.value === '00:00') {
                  alert('Please select a work type and enter a time duration.');
                  // return false;
               } else {
                  // submit the form
                  const completeActivityForm = document.querySelector('.complete_activity');
                  console.log(completeActivityForm);
                  completeActivityForm.submit();
               }
            });

         });
      });
      // manage activity with spesific activity Category

      //listen to click event button in manage_activity_submit_btn
      const manage_activity_submit_btn = document.querySelector('#manage_activity_submit_btn');
      document.querySelector('#manage_activity_submit_btn').addEventListener('click', function() {
         const form = document.querySelector('#manage_activity_form');
         if (!form) return;
         form.submit();
      });
      //listen to click event button in postpone_activity_submit_btn
      document.querySelector('#postpone_activity_submit_btn').addEventListener('click', function(e) {
         console.log(e);
         const form = document.querySelector('#postponeActivityForm');
         if (!form) return;
         console.log(e);
         // form.submit();
         //prevent default
         e.preventDefault();
         e.stopPropagation();
      });
      //listen to click event button in manage_activity_complete_submit_btn
      document.querySelector('#manage_activity_complete_submit_btn').addEventListener('click', function() {
         const form = document.querySelector('#completeActivityForm');
         if (!form) return;
         form.submit();
      });

   });
</script>