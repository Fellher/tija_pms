<div class="card custom-card">
         <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
               <h2 class="t300 font-20">Planned Activities</h2>
               
               <button 
                  type="button" 
                  class="btn btn-primary-light shadow btn-sm px-4 addNewActivity" 
                  data-bs-toggle="modal" 
                  data-bs-target="#manage_activity"
                  >
                  <i class="ri-add-line"></i>
                     Manage My Daily Tasks
               </button>
              
            </div>
            <div class="activities">
               <?php
              
               // var_dump($activities);
               // echo "count: ".count($activities);
               $myActivityParticipating = Schedule::activity_participants(array('participantUserID'=>$employeeID), false, $DBConn);
               // var_dump($myActivityParticipating);
               if($myActivityParticipating) {
                  foreach ($myActivityParticipating as $key => $activity) {
                     $activityID= $activity->activityID;
                     $activityDetails = Schedule::tija_activities(array('activityID'=>$activityID), true, $DBConn);
                     // var_dump($activityDetails);
                     if($activityDetails) {
                        $activities[] = $activityDetails;
                     }
                  }
               }
               echo "count: ".count($activities);
               if($activities){
                  foreach ($activities as $key => $activity) {
                     // var_dump($activity);?>

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
                                    <?= "<span class='mx-2' > " .Utility::date_format($activity->activityDate, 'british') . "</span>"; ?> 
                                    <?= $activity->activityStartTime ? "|<span class='mx-2'> {$activity->activityStartTime} </span>"  : "" ?> 

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
                                 console.log(participantDetails);
                              participantDetailsString = JSON.stringify(participantDetails);
                              console.log(participantDetailsString);
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
                                 <div class="dropdown">
                                       <a aria-label="anchor" href="javascript:void(0);" class="btn btn-primary-light btn-sm rounded-circle shadow-sm" data-bs-toggle="dropdown" aria-expanded="false">
                                          <i class="fe fe-more-vertical"></i>
                                       </a>
                                       <ul class="dropdown-menu" style="position: absolute; inset: 0px auto auto 0px; margin: 0px; transform: translate(0px, 30px);" data-popper-placement="bottom-start">
                                          <li><a class="dropdown-item" href="javascript:void(0);">
                                          <a 
                                             href="#manage_activity" 
                                             data-bs-toggle="modal" 
                                             role="button" 
                                             aria-expanded="false" 
                                             aria-controls="manage_activity"
                                             class="dropdown-item  editActivityBtn" 
                                             data-activity-id="<?= $activityMini->activityID ?>"
                                             data-orgData-id ="<?= $orgDataID ?>"
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
                                             Edit Activity
                                          </a>
                                          </li>
                                          <li>
                                             <a 
                                                href="#postponeActivity" 
                                                data-bs-toggle="modal" 
                                                role="button" 
                                                aria-expanded="false" 
                                                aria-controls="postponeActivity"
                                                class="dropdown-item postponeActivityBtn"
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
                                                Postpone Activity

                                             </a></li>
                                          <li><a
                                                href="#deleteActivity" 
                                                data-bs-toggle="modal" 
                                                role="button" 
                                                aria-expanded="false" 
                                                aria-controls="deleteActivity"
                                                class="dropdown-item"
                                                data-activity-id="<?= $activity->activityID ?>"
                                                data-activity-name="<?= $activity->activityName ?>"
                                                >
                                                <i class="ri-delete-bin-line" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete Activity"> </i>
                                                Delete Activity
                                             </a>
                                          </li>
                                          <li>
                                             <a 
                                                href="#completeActivity" 
                                                data-bs-toggle="modal" 
                                                role="button" 
                                                aria-expanded="false" 
                                                aria-controls="completeActivity"
                                                class="dropdown-item completeActivityBtn"
                                                data-activity-id="<?= $activity->activityID ?>"
                                                data-activity-name="<?= $activity->activityName ?>"
                                             >
                                                <i class="ri-checkbox-circle-line" data-bs-toggle="tooltip" data-bs-placement="top" title="Mark Activity As complete"></i>
                                          
                                                Mark  As complete
                                             </a>
                                          </li>
                                       </ul>
                                 </div>
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
                     <?php
                  }
               } else {
                  Alert::info("No activities found for you", true, array('fst-italic', 'text-center', 'font-18'));
               }?>
            </div>
         </div>
      </div>