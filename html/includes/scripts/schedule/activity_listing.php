<?php //var_dump($activity); ?>
<div class="alert alert-primary alert-dismissible fade show custom-alert-icon shadow-sm pe-1" role="alert">
   <div class="d-flex justify-content-between  border-bottom">
      <div class="col d-flex justify-content-start align-items-center">
      <span class="avatar avatar-sm bd-white-600 me-2 avatar-rounded pt-1 border border-secondary border-0">
         <AC>
            <i class="<?= $activity->activityTypeIcon ?> text-secondary fs-20 "></i>
         </AC>
      </span>
      <div class="w-100  row">
         <div class="col-md-5">
            <span class="d-block"> 
               <?= $activity->activityName ?> 
            </span>
            <span class="d-block">
               <?= "<span class='mx-2' > " .Utility::date_format($activity->activityDate, 'shortStr') . "</span>"; ?> 
               <?= $activity->activityStartTime ? "|<span class='mx-2'> " . date('g:i A', strtotime($activity->activityStartTime)) . "</span>"  : "" ?> 
            </span>
         
         </div> 
         <div class="col-md-7">
            <span class="d-block">
               <?php echo "<span class='mx-2'>".$activity->clientID ? $activity->clientName : "". "</span>" ?>
            </span>
            <span class="d-block">
            
               <?php
               $salesCaseName = $activity->activitySegment && $activity->activitySegment == "sales" && !empty($activity->salesCaseID) ? $activity->salesCaseName : "";
               $projectCaseName = $activity->activitySegment && $activity->activitySegment == "project" && !empty($activity->projectID) ? $activity->projectName : "";
               // echo $activity->activitySegment ? "<span class='mx-2'> {$activity->activitySegment} </span>" : "";
               // echo $projectCaseName ? "<span class='mx-2'> {$activity->activitySegment} </span>" : "";
               if($salesCaseName) {
                  echo "<span class='mx-2'> {$salesCaseName} </span>";
               } elseif($projectCaseName) {
                  echo "<span class='mx-2'> {$projectCaseName} </span>";
               } else {
                  echo "<span class='mx-2'> No Case </span>";
               }?> 
            </span>    
         </div>                  
      </div>
   </div>
      <?php
      $activityMini = Schedule::activity_mini(['activityID'=>$activity->activityID], true, $DBConn);
      $participants = $activity->activityParticipants ? explode(',', $activity->activityParticipants) : "";
      $participantDetails = [];
      if($participants ) {
         foreach($participants as $participant) {
            $participantDetails[] = Core::get_user_name_initials($participant, $DBConn);                                    
         }         
      }?>
      <script>
         document.addEventListener('DOMContentLoaded', function() {
            let participantDetails = <?= json_encode($participantDetails) ?>;             
            participantDetailsString = JSON.stringify(participantDetails);          
            document.querySelectorAll('.editActivityBtn').forEach(button => {
            button.dataset.participantDetails = participantDetailsString;
            });
         });
      </script>

      <div class="text-end d-flex justify-content-end ">                                  
         <span class="text-primary font-14 d-block px-2 ">
            <span class="avatar bd-blue-800 avatar-xs mx-2 avatar-rounded" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= $activity->activityOwnerName ?> - <?= $activity->activityOwnerEmail ?>">
               <AC><?= Utility::generate_initials($activity->activityOwnerName) ?> </AC>
            </span> 
         </span>  
        
            

         <div class="col text-end">

         <?php 
         if($userDetails->ID == $activity->activityOwnerID) { ?>
            <!-- Edit Activity -->
            <a 
               href="#manage_activity" 
               data-bs-toggle="modal" 
               role="button" 
               aria-expanded="false" 
               aria-controls="manage_activity"
               class="btn btn-primary-light btn-sm rounded-circle editActivityBtn" 
               data-activity-id="<?= $activityMini->activityID ?>"
               data-org-data-id ="<?= $orgDataID ?>"
               data-entity-id ="<?= $activity->entityID ?>"
               data-client-id ="<?= $activity->clientID ?>"
               data-activity-name="<?= $activity->activityName ?>"
               data-activity-description="<?= $activity->activityDescription ?>"
               data-activity-category-id ="<?= $activity->activityCategoryID ?>"
               data-activity-type-id ="<?= $activity->activityTypeID ?>"
               data-activity-segment="<?= $activity->activitySegment ?>"
               data-duration-type ="<?= $activity->durationType ?>"
               data-activity-date="<?= $activity->activityDate ?>"
               data-activity-start-time="<?= $activity->activityStartTime ?>"
               data-activity-duration-end-time="<?= $activity->activityDurationEndTime ?>"
               data-activity-duration-end-date ="<?= $activity->activityDurationEndDate ?>"
               data-recurring ="<?= $activity->recurring ?>"
               data-recurrence-type ="<?= $activity->recurrenceType ?>"
               data-recurring-interval ="<?= $activity->recurringInterval ?>"
               data-recurring-interval-unit = "<?= $activity->recurringIntervalUnit ?>"
               data-week-recurring-days = "<?= $activity->weekRecurringDays ?>"
               data-month-repeat-on-days = "<?= $activity->monthRepeatOnDays ?>"
               data-monthly-repeating-day = "<?= $activity->monthlyRepeatingDay ?>"
               data-custom-frequency-ordinal = "<?= $activity->customFrequencyOrdinal ?>"
               data-custom-frequency-day-value = "<?= $activity->customFrequencyDayValue ?>"
               data-recurrence-end-type = "<?= $activity->recurrenceEndType ?>"
               data-number-of-occurrences-to-end = "<?= $activity->numberOfOccurrencesToEnd ?>"
               data-recurring-end-date = "<?= $activity->recurringEndDate ?>"
               data-sales-case-id = "<?= $activity->salesCaseID ?>"
               data-project-id = "<?= $activity->projectID ?>"
               data-project-phase-id = "<?= $activity->projectPhaseID ?>"
               data-project-task-id = "<?= $activity->projectTaskID ?>"
               data-activity-status = "<?= $activity->activityStatus ?>"
               data-activity-status-id = "<?= $activity->activityStatusID ?>"
               data-activity-priority = "<?= $activity->activityPriority ?>"
               data-activity-owner-id = "<?= $activity->activityOwnerID ?>"
               data-activity-participants = "<?= $activity->activityParticipants ?>"             
               data-activity-notes-id = "<?= $activity->activityNotesID ?>"
               data-participants-arr = "<?= htmlspecialchars(json_encode($participantDetails))?>"
            >
               <i class="ri-edit-line" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Activity"></i>
            </a>
            <?php 
         }?>
          <?php if($activity->activityStatusID != 4) { ?>
            <!-- Postpone Activity -->
            <a 
               href="#postponeActivity" 
               data-bs-toggle="modal" 
               role="button" 
               aria-expanded="false" 
               aria-controls="postponeActivity"
               class="btn btn-warning-light btn-sm rounded-circle postponeActivityBtn"
               data-activity="<?= htmlspecialchars(json_encode($activity)) ?>"                                    
               data-activity-id="<?= $activity->activityID ?>"
               data-activity-date="<?= $activity->activityDate ?>"
               data-activity-start-time="<?= $activity->activityStartTime ?>"
               data-activity-name="<?= $activity->activityName ?>"
               data-activity-duration-end-date = "<?= $activity->activityDurationEndDate ?>"
               data-activity-duration-end-time = "<?= $activity->activityDurationEndTime ?>"
               title="Postpone Activity"
            >
               <i class="ri-time-line" data-bs-toggle="tooltip" data-bs-placement="top" title="Postpone Activity instance"></i>
            </a>
          <!-- Delete Activity -->
            <a 
               href="#deleteActivity" 
               data-bs-toggle="modal" 
               role="button" 
               aria-expanded="false" 
               aria-controls="deleteActivity"
               class="btn btn-danger-light btn-sm rounded-circle"
               data-activity-id="<?= $activity->activityID ?>"
               data-activity-name="<?= $activity->activityName ?>"
               >
               <i class="ri-delete-bin-line" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete Activity"> </i>
            </a>
            <!-- Complete Activity -->
            
            <a 
               href="#complete_activity"                
               role="button" 
               aria-expanded="false" 
               data-bs-toggle="modal" 
               aria-controls="completeActivity"
               class="btn btn-success-light btn-sm rounded-circle completeActivityBtn"
               data-activity-id="<?= $activity->activityID ?>"
               data-activity-name="<?= $activity->activityName ?>"
               data-work-segment-id ="<?= $activity->workSegmentID ?>"
               data-task-activity-id ="<?= $activity->activityID; ?>"
               data-employee-id = "<?= $employeeID ?>"
               data-activity-date ="<?= $dt->format('Y-m-d') ?>"
               data-work-segment-id="<?= $activity->workSegmentID ? $activity->workSegmentID : "3" ?>"
               data-task-type= "activity" 
               data-task-date = "<?= $dt->format('Y-m-d') ?>"
               data-instance = "<?= isset($activity->instance) ? $activity->instance : "" ?>"
               data-recurring-instance-id = "<?= isset($activity->recurringInstanceID) ? $activity->recurringInstanceID : ""  ?>"
               data-client-id="<?= $activity->clientID ?>"
               data-entity-id='<?= $activity->entityID ?>'
               data-activity-segment = "<?= $activity->activitySegment ?>"
            >
               <i class="ri-checkbox-circle-line" data-bs-toggle="tooltip" data-bs-placement="top" title="Mark Activity As complete"></i>
            </a>
            <?php } ?>
         </div>
      </div>
   </div>
   <div class="row">
      <div class="col-md d-flex align-items-center justify-content-start">
         <span class="d-block fst-italic "> 
            <?= $activity->activityDescription ?> 
         </span>
      </div>
      
      <div class="col-md text-end">
         <span class=" d-block fst-italic mt-2"> 
            <?php
               if($participants) {
                  foreach($participantDetails as $participant) {
                     echo "<span class='avatar bd-tija-blue avatar-xs mx-2 avatar-rounded' data-bs-toggle='tooltip' data-bs-placement='top' title='{$participant['name']}'>
                              <AC>{$participant['initials']}</AC>
                           </span>";
                  }
               } else {
                  echo "<span class='text-danger'>No Participants</span>";
               }?>
         </span>
      </div>
   </div>    
</div>
