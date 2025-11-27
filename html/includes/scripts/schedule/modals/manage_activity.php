<?php 
$activityCategories = Schedule::activity_categories([], false, $DBConn);       
$activityTypes = Schedule::tija_activity_types([], false, $DBConn);?>
<div id="manage_activity_form" class="bg-light">
   <div class=" shadow bg-white border-0 rounded-3  p-3 my-2 activityNameDiv">
      <div class="row">
         <div class="form-group my-2  d-none ">
            <label for="orgDataID"> Org Data ID</label>]
            <input type="text" id="orgDataID" name="orgDataID" class="form-control form-control-sm form-control-plaintext " placeholder="Org Data ID" value="<?php echo $orgDataID; ?>" readonly>
            <label for="entityID"> EntityID</label>
            <input type="text" id="entityID" name="entityID" class="form-control form-control-sm form-control-plaintext " placeholder="Entity ID" value="<?php echo $entityID; ?>" readonly>
            <label for="activityID"> activityID</label>
            <input type="text" id="activityID" name="activityID" class="form-control form-control-sm form-control-plaintext " placeholder="Activity ID" value="" readonly>
         </div>
      
         <div class="form-group my-2 col-md-6 ">
            <label for="activityCategoryID"> Activity Category</label>
            <select name="activityCategoryID" id="activityCategoryID" class="form-control form-control-sm form-control-plaintext  ps-2">
               <option value="">Select Activity Category</option>
               <?php foreach($activityCategories as $category): ?>
                  <option value="<?php echo $category->activityCategoryID; ?>"><?php echo $category->activityCategoryName; ?></option>
               <?php endforeach; ?>
            </select>
         </div>
         <div class="form-group my-2 col-md-6 d-none activityTypeDiv ">
            <label for="activityTime" class="col-md-12 nott mb-0 t500 text-dark">Activity Type</label>
            <?php        ?>
            <select name="activityTypeID" id="activityType" class="form-control form-control-sm form-control-plaintext  ps-2">
               <option value="">Select Activity Type</option>
               <?php 
               foreach($activityTypes as $type): ?>
                  <option value="<?php echo $type->activityTypeID; ?>"><?php echo $type->activityTypeName; ?></option>
               <?php endforeach; ?>
            </select>
         </div>   

         <div class="form-group my-2">
            <label for="activityName" class="col-md-12 nott mb-0 t500 text-dark">Activity Name</label>
            <input type="text" id="activityName" name="activityName" class="form-control form-control-sm form-control-plaintext " placeholder="Enter Activity Name">
         </div>
      </div>
   </div>


   <div class=" shadow border-0 bg-white rounded-3  p-3 my-2">
      <div class="row">
         <div class="form-group my-2 col-md-12     py-1 activityPeriodDiv">
            <label for="activityDuration" class="col-md-12 nott mb-0 t500 text-dark">Duration/Period</label>
            <div class="row">
               <div class="col-md">
                  <div class="form-check form-switch">
                     <input class="form-check-input Single durationSelect" type="radio" name="durationType" id="single" value="single" >
                     <label class="form-check-label" for="single">   Single</label>
                  </div>
               </div>
               <div class="col-md">                  
                  <div class="form-check form-switch">
                     <input class="form-check-input duration durationSelect  "  type="radio" name="durationType" id="duration" value="duration" >
                     <label class="form-check-label" for="duration">
                        Duration
                     </label>
                  </div>
               </div>

               <div class="col-md">
                  <div class="form-check form-switch">
                     <input class="form-check-input recurring " type="checkbox" name="recurring" id="recurring" value="recurring">
                     <label class="form-check-label" for="recurring">
                      Recurring
                     </label>
                  </div>
               </div>               
            </div>
         </div>

         <div class="col-md">
            <div class="form-group my-2 activityStartDateDiv d-none">
               <label for="activityDate" class="col-md-12 nott mb-0 t500 text-dark">Activity  Date</label>
               <input type="date" id="date" name="activityDate" class=" form-control-sm form-control-plaintext border-bottom  pb-0  border-top-0 activityStartDate" placeholder="YYYY-MM-DD" value="<?php echo date_format($dt,'Y-m-d') ?>">
            </div>
      
            <div class="form-group mb-0   activityStartTimeDiv d-none">  
               <label for="activityStartTime" class="col-md-12 nott mb-0 t500 text-dark">Activity Time(24hr )</label>
               <input type="text" name="activityStartTime"  class="form-control-plaintext form-control-sm pb-0 border-bottom  activityStartTime" id="inlinetime" placeholder="Choose time in 24hr format" value= "<?php echo date_format($dt,'H:i') ?>">
            </div>
         </div>
         

         

         <div class="col-md-6 activityEndDateDiv d-none">
            <div class="form-group my-2  d-none ">
               <label for="activityEndDate" class="col-md-12 nott mb-0 t500 text-dark">Activity end Date</label>
               <input type="date" id="date" name="activityDurationEndDate" class="form-control-sm form-control-plaintext border-bottom border-top-0  activityDurationEndDate" placeholder="YYYY-MM-DD" value="<?php echo date_format($dt,'Y-m-d') ?>">
            </div>      
            <div class="form-group mb-0   activityEndTimeDiv  ">
               <label for="activityEndTime" class="col-md-12 nott mb-0 t500 text-dark">Activity end Time</label>
               <input type="text" name="activityDurationEndTime"  class=" form-control-sm form-control-plaintext border-bottom activityDurationEndTime" id="inlinetime" placeholder="Choose time" value= "<?php echo date_format($dt->modify('+30 minutes'),'H:i') ?>">
            </div>
         </div>

         <div class="col-12 recurringDiv d-none bg-light border-0 rounded-3  py-1">
            <div class="row">
               <legend class= "fs-18 border-bottom border-2 border-dark bg-white text-dark"> Recurrence rule </legend>               
               <div class ="col-md-6">
                  <label for="recurrenceType" class="col-md-12 nott mb-0 t500 text-dark">Recurrence</label>
                  <select name="recurrenceType" id="recurrenceType" class="form-control form-control-sm form-control-plaintext bg-light-blue ps-2">
                     <option value="">Select Recurrence Frequency</option>
                     <option value="daily">Daily</option>
                     <option value="weekly">Weekly</option>
                     <option value="monthly">Monthly</option>
                     <option value="yearly">Yearly</option>
                  </select>
               </div>
               <div class="col-md-6 form-group">
                  <label for="repeatEvery" class="col-md-12 nott mb-0 t500 text-dark">Repeat Every</label>
                  <div class="row" >
                     <div class="col-6">
                        <input type="number" step='1' min='1' max="7" id="repeatEvery" name="recurringInterval" class="form-control form-control-sm form-control-plaintext  px-2" placeholder="">
                     </div>
                     <div class="col-6">
                        <input type="text" name="recurringIntervalUnit" id="recurrenceTypeUnit" class="form-control form-control-sm form-control-plaintext bg-light  px-2" value="" checked> 
                     </div>
                  </div>
               </div>

               <div class="col-12 weeklyRecurrence d-none my-2 border-bottom border-2 border-dark py-3">
                  <label class="fs-16" > Weekly Recurring Days</label>
                  <label for="weekRecurringDays" class="col-md-12 nott mb-0 t500 text-dark">Recurring Days</label>
                  <div class="row">
                     <div class="col-md-3">
                        <input type="checkbox" id="monday" name="weekRecurringDays[]" value="monday">
                        <label for="monday">Monday</label>
                     </div>
                     <div class="col-md-3">
                        <input type="checkbox" id="tuesday" name="weekRecurringDays[]" value="tuesday">
                        <label for="tuesday">Tuesday</label>
                     </div>
                     <div class="col-md-3">
                        <input type="checkbox" id="wednesday" name="weekRecurringDays[]" value="wednesday"> 
                        <label for="wednesday">Wednesday</label>
                     </div>
                     <div class="col-md-3">
                        <input type="checkbox" id="thursday" name="weekRecurringDays[]" value="thursday">
                        <label for="thursday">Thursday</label>
                     </div>
                     <div class="col-md-3">
                        <input type="checkbox" id="friday" name="weekRecurringDays[]" value="friday">
                        <label for="friday">Friday</label>
                     </div>
                     <div class="col-md-3">
                        <input type="checkbox" id="saturday" name="weekRecurringDays[]" value="saturday">
                        <label for="saturday">Saturday</label>
                     </div>
                     <div class="col-md-3">
                        <input type="checkbox" id="sunday" name="weekRecurringDays[]" value="sunday">
                        <label for="sunday">Sunday</label>
                     </div>
                  </div>
               </div>

               <div class="col-12 monthlyRecurrence d-none  my-2 border-bottom border-2 border-dark py-3">
                  <label class="fs-16 border-bottom" > Monthly Recurring Days</label>
                  <span for="" class="col-md-12 nott mb-0 t500 text-dark d-block fs-14">Repeats on</span> 
                  <div class="form-check form-check-lg  col-12 form-switch mb-2">
                     <input class="form-check-input" type="radio" role="switch" name="monthRepeatOnDays"  id="repeatDays" value="repeatonDay"  >
                     <label class="form-check-label col-10 " for="repeatDays">
                        <div class="row">
                           <div class="col-4">
                              Day
                           </div>
                           <div class="col-6">
                              <input type="number" step='1' min='1' max="31" id="repeatDays" name="monthlyRepeatingDay" class="form-control form-control-sm form-control-plaintext border-bottom  px-2" placeholder="15">
                           </div>
                        </div>                  
                     </label>
                  </div>
                  <!-- <div class="form-check form-check-lg col-12 form-switch">
                     <input class="form-check-input" type="radio" role="switch" name="monthRepeatOnDays"  id="customDays" value="customDays" >
                     <label class="form-check-label col-10 bg-light-blue" for="customDays" >
                     <div class="row">                        
                        <div class="col-6">
                           <select name="customFrequencyOrdinal" id="customFrequencyOrdinal" class="form-control form-control-sm form-control-plaintext bg-light-blue ps-2">
                              <option value="">Search</option>
                              <option value="1">First</option>
                              <option value="2">second</option>
                              <option value="3">Third</option>
                              <option value="4">Last</option>
                           </select>
                        </div>
                        <div class="col-6">
                           <select name="customFrequencyDayValue" id="customFrequencyDayVal" class="form-control form-control-sm form-control-plaintext bg-light-blue ps-2">
                              <option value=""> select Day</option>
                              <option value="day">Day</option>
                              <option value="wekday">Weekday</option>
                              <option value="weekend">Weekend</option>
                              <option value="monday">Monday</option>
                              <option value="tuesday">Tuesday</option>
                              <option value="wednesday">Wednesday</option>
                              <option value="thursday">Thursday</option>
                              <option value="friday">Friday</option>
                              <option value="saturday">Saturday</option>
                              <option value="sunday">Sunday</option>
                           </select>
                        </div>
                     </div>
                  </div>  -->
               </div>
            </div>
               
            <div class="recurrenceEnd my-4 border-bottom border-2 border-dark py-3">
               <div class="form-check form-check-lg  col-12 form-switch mb-2">
                  <input class="form-check-input" type="radio" name="recurrenceEndType" id="noEndDateNo" value="noEndDate">
                  <label class="form-check-label fs-18 col-10" for="noEndDateNo">
                     <div class="col-12 fs-16">No End Date </div> 
                  </label>
               </div>
               <div class="form-check form-check-lg  col-12 form-switch">
                  <input class="form-check-input" type="radio" name="recurrenceEndType" id="endDateOccurrence" value="occurrences" checked>
                  <label class="form-check-label col-10" for="endDateOccurrence">
                     <div class="row">
                        <div class="col-4 fs-16">
                           After 
                        </div>
                        <div class="col-4">
                           <input type="number" step='1' min='1' max="31" id="endDateOccurrenceValue" name="numberOfOccurrencesToEnd" class="form-control form-control-sm form-control-plaintext border-bottom  px-2" placeholder="15">
                        </div>
                        <div class="col-4 fs-16">
                           Occurrence
                        </div>
                     </div>                     
                  </label>
               </div>
               <div class="form-check form-check-lg  col-12 form-switch">
                  <input class="form-check-input" type="radio" name="recurrenceEndType" id="endDateDate" value="endDate" checked>
                  <label class="form-check-label col-10" for="endDateDate">
                     <div class="row">
                        <div class="col-4 fs-16">
                           End Date 
                        </div>
                        <div class="col-6">
                        <input type="date" id="recurringEndDate" name="recurringEndDate" class="form-control-sm form-control-plaintext  date" placeholder="YYYY-MM-DD">
                        </div>                        
                     </div>                     
                  </label>
               </div>

            </div>
                             
         </div>
           
      </div>
   </div>

   <div class=" bg-light border-0 rounded-3  p-3 my-2">
      <div class="row">
         <div class="form-group col-12 my-2">
            <label for="activityDescription" class="col-md-12 nott mb-0 t500 text-dark">Activity Description</label>
            <textarea id="activityDescription" name="activityDescription" class="form-control borderless-mini" rows="3" placeholder="Enter Activity Description"></textarea>
         </div>

         <div class="form-group my-2 col-md-6">
            <label for="activitySegment"> Activity Segment</label>
            <select name="activitySegment" id="activitySegment" class="form-control form-control-sm form-control-plaintext bg-light-blue ps-2">
               <option value="">Select Activity Segment</option>
               <?php foreach($config['activitySegment'] as $segment): ?>
                  <option value="<?php echo $segment->key; ?>" <?= isset($activitySegment) && $activitySegment === $segment->key ? "selected" : "" ?> ><?php echo $segment->value; ?></option>
               <?php endforeach; ?>
            </select>
         </div>

        

         <div class="form-group my-2 col-md-6">
            <label for="clientID">Client</label>
            <select name="clientID" id="clientID" class="form-control form-control-sm form-control-plaintext bg-light-blue ps-2">     
               <?= Form::populate_select_element_from_object($clients, 'clientID', 'clientName', (isset($clientID) && !empty($clientID) )? $clientID : "", '' , 'Select Client') ?>
            </select>
         </div>
         
         <div class=" d-none projectCaseDiv bg-light border-0 rounded-3  py-1 <?= !$projects ? "d-none" : "";  ?>">
            <div class="row">                     
               <div class="col-md-6">
                  <label for="projectID" class="col-md-12 nott mb-0 t500 text-dark">Project</label>
                  <select name="projectID" id="projectID" class="form-control form-control-sm form-control-plaintext bg-light-blue ps-2">
                     <option value="">Select Project</option>
                     <?php foreach($projects as $project): ?>
                        <option value="<?php echo $project->projectID; ?>" <?= (isset($projectID) && $projectID === $project->projectID) ? 'selected': "";?> ><?php echo $project->projectName; ?></option>
                     <?php endforeach; ?>
                  </select>
               </div>   

              
               <?php $activityStatus = Schedule::activity_status([], false, $DBConn);?>
               <div class="form-group my-2 col-md-6">
                  <label for="activityStatus" class="col-md-12 nott mb-0 t500 text-dark">Activity Status</label>
                  <select name="activityStatusID" id="activityStatus" class="form-control form-control-sm form-control-plaintext bg-light-blue ps-2">
                     <option value="">Select Activity Status</option>
                     <?= Form::populate_select_element_from_object($activityStatus, 'activityStatusID', 'activityStatusName',  "", '' , 'Select Activity Status') ?>
                  </select>
               </div>               
            </div>
         </div>
      
        

         <div class="form-group my-2 col-md-6 <?= isset($activitySegment) && $activitySegment === "sales" ? "" : 'd-none' ?> salesCaseDiv">
            <label for="salesCase" class="col-md-12 nott mb-0 t500 text-dark">Sales Case</label>
            <select name="salesCaseID" id="salesCase" class="form-control form-control-sm form-control-plaintext bg-light-blue ps-2">
               <option value="">Select Sales Case</option>
                        <?= Form::populate_select_element_from_object($salesCases, 'salesCaseID', 'salesCaseName', (isset($salesCaseID) && !empty($salesCaseID) )? $salesCaseID : "", '' , 'Select Sales Case') ?>
            </select>
         </div>
      </div>
   </div>
  
   <div class=" bg-light border-0 rounded-3  p-3 my-2">
      <div class="row">
         <div class="form-group my-2 col-md-6">
            <label for="activityStatus" class="col-md-12 nott mb-0 t500 text-dark">Activity Priority</label>
            <select name="activityPriority" id="activityPriority" class="form-control form-control-sm form-control-plaintext bg-light-blue ps-2" required>
               <option value="">Select Activity Priority</option>
               <?php foreach($config['activityPriority'] as $priority): ?>
                  <option value="<?php echo $priority->key; ?>"><?php echo $priority->value; ?></option>
               <?php endforeach; ?>
            </select>
         </div>
              

         <div class="form-group my-2 col-md-6">
            <label for="activityOwner" class="col-md-12 nott mb-0 t500 text-dark">Activity Owner</label>
            <select name="activityOwnerID" id="activityOwnerID" class="form-control form-control-sm form-control-plaintext bg-light-blue ps-2 ">
               <option value="">Select Activity Owner</option>
               <?= Form::populate_select_element_from_grouped_object($employeesCategorised, 'ID', 'employeeName', (isset($employeeID) && !empty($employeeID) )? $employeeID : "", '' , 'Select Activity Owner') ?>
            </select>
         </div>
   
         <div class=" col-md-6 my-2 d-flex align-items-center">
            <div class="form-check form-switch">
               <input class="form-check-input" type="checkbox" id="markAsCompleted" name="markAsCompleted" value="completed">
               <label class="form-check-label" for="markAsCompleted">Mark as Completed</label>
            </div>
         </div>

         <div class="form-group my-2 participantsDiv  d-none">
            <label for="participants" class="col-md-12 nott mb-0 t500 text-dark">Participants</label>
            <select data-trigger name="activityParticipants[]" id="participants" class="form-control form-control-sm form-control-plaintext bg-light-blue ps-2 choices-multiple-default" multiple>
               <option value="">Select Participants</option>
               <?php foreach($allEmployees as $user): ?>
                  <option value="<?php echo $user->ID; ?>"><?php echo $user->employeeName ?></option>
               <?php endforeach; ?>
            </select>
         </div>

         <div class="form-group participantsDivCategorised">
            <div class="card-body">
               <div class="row">
                  <div class="col-md-12 dropup">
                     <span class="h4">Participants </span>                  
                     <button type="button" class="rounded-pill btn btn-outline-light dropdown-toggle d-flex align-items-center float-end" data-bs-toggle="dropdown" aria-expanded="false">
                           <span class="avatar bd-blue-200 avatar-xs2 me-2 avatar-rounded  me-1">
                              <AC>  <i class="ti ti-user-plus"></i>        </AC>
                           </span>
                           Add Participants
                     </button>
                     <ul class="dropdown-menu" style="width: 300px;">
                        <?php 
                        foreach($employeesCategorised  as $role => $user):   ?>
                           <li class = "dropdown-header bg-light border-bottom">
                              <h6 class="text-dark fst-italic text-capitalize"><?php echo $role ?></h6>
                           </li>
                           <?php 
                           foreach($user as $user): ?>
                              <li><a class="dropdown-item participantUserID" href="javascript:void(0);" data-user-id=<?= $user->ID ?> data-participant-name = "<?= $user->employeeName?>" data-participant-initials = "<?= $user->employeeInitials ?>" ><?php echo $user->employeeName ?></a></li>
                           <?php endforeach;  
                        endforeach; ?>
                     </ul>
                  </div>
                  <div class="participantDiv"> </div>
               </div>
            </div>         
         </div>
      </div>
   </div>
</div>
<script>
   document.addEventListener('DOMContentLoaded', function() { 

      // get the select element
      const recurrenceType = document.getElementById('recurrenceType');
      const repeatEvery = document.getElementById('repeatEvery');
      const recurrenceTypeUnit = document.getElementById('recurrenceTypeUnit');

      // add event listener to the select element
      recurrenceType.addEventListener('change', function() {
         // get the selected value
         const selectedValue = this.value;
         console.log(selectedValue);
         switch (selectedValue) {
            case 'daily':
               recurrenceTypeUnit.value = 'days';
               document.querySelector('.weeklyRecurrence').classList.add('d-none');
               break;
            case 'weekly':
               recurrenceTypeUnit.value = 'weeks';
               // Show the weeklyRecurrence div
               document.querySelector('.weeklyRecurrence').classList.remove('d-none');
               document.querySelector('.monthlyRecurrence').classList.add('d-none');
               break;
            case 'monthly':
               recurrenceTypeUnit.value = 'months';
               document.querySelector('.monthlyRecurrence').classList.remove('d-none');
               document.querySelector('.weeklyRecurrence').classList.add('d-none');
               break;
            case 'yearly':
               recurrenceTypeUnit.value = 'years';
            document.querySelector('.monthlyRecurrence').classList.add('d-none');
            document.querySelector('.weeklyRecurrence').classList.add('d-none');
            break;
            default:
            recurrenceTypeUnit.value = '';
         }         
      });     

      // get participantID
      const participantID = document.querySelectorAll('.participantUserID');
      const participantsDivCategorised = document.querySelector('.participantsDivCategorised');
      let participantArr= [];

      participantID.forEach(function(participant) {
         participant.addEventListener('click', (e)=> {
            e.preventDefault();
            e.stopPropagation();
         
            // this.preventDefault();
            // this.stopPropagation();
            // get all data attribute
            const data  = e.currentTarget.dataset;
            console.log(data);

            let existingParticipant = participantArr.find(participant => participant.userID === data.userId);
            if (!existingParticipant) {
            
               participantArr.push({
                  userID: data.userId,
                  userName: data.participantName,
                  initials: data.participantInitials
               });
            let participantDiv = document.querySelector('.participantDiv');
         
            participantDiv.innerHTML += `<span class='avatar bd-blue-800 avatar-xs mx-2 avatar-rounded' data-bs-toggle='tooltip' data-bs-placement='top' title='${data.participantName}'>
                              <AC>${data.participantInitials}</AC>
                           </span>
                           <input type='hidden' name='activityParticipants[]' value='${data.userId}' class='participantID'>
            
            `;
            // participantsDivCategorised.appendChild(participantDiv);
            }        

         });
      });

      let formContainer = document.querySelector('#manage_activity_form');



      let activityCategories = <?= json_encode($activityCategories) ?>,
      activityTypes = <?= json_encode($activityTypes) ?>;
      let activityCategoryID = formContainer.querySelector('#activityCategoryID');
      activityCategoryID.addEventListener('change', function() {
         let categoryID = this.value;
         console.log(`Selected Category ID: ${categoryID}`);
         console.log(activityTypes);
         let filteredTypes = activityTypes.filter(type => type.activityCategoryID == parseInt(categoryID));
         console.log(filteredTypes);
         if (filteredTypes.length > 0) {
            formContainer.querySelector('.activityTypeDiv').classList.remove('d-none');
         } else {
            formContainer.querySelector('.activityTypeDiv').classList.add('d-none');
         }
         let activityTypeSelect = formContainer.querySelector('#activityType');
         activityTypeSelect.innerHTML = '';
         activityTypeSelect.innerHTML += '<option value="">Select Activity Type</option>';
         filteredTypes.forEach(type => {
            activityTypeSelect.innerHTML += `<option value="${type.activityTypeID}">${type.activityTypeName}</option>`;
         });
      });

      // check that activity end date is not before activity start date
      const activityStartDate = document.querySelector('.activityStartDate');
      const activityEndDate = document.querySelector('.activityDurationEndDate');
      const activityStartTime = document.querySelector('.activityStartTime');
      const activityEndTime = document.querySelector('.activityDurationEndTime');

      activityStartDate.addEventListener('change', function() {
         if (activityEndDate.value !== '') {
            if (this.value > activityEndDate.value) {
               // alert('Activity Start Date cannot be greater than Activity End Date');
               activityEndDate.value = this.value;
            }
         }
      });

      activityEndDate.addEventListener('change', function() {
         if (this.value < activityStartDate.value) {
            // alert('Activity End Date cannot be less than Activity Start Date');
            this.value = activityStartDate.value;
         }
      });

      activityStartTime.addEventListener('change', function() {
         if (activityEndTime.value !== '') {
            let startDateTime = new Date(`${activityStartDate.value}T${this.value}`);
            let endDateTime = new Date(`${activityEndDate.value}T${activityEndTime.value}`);
            if (startDateTime > endDateTime) {
               // alert('Activity Start Time cannot be greater than Activity End Time');
               activityEndTime.value = this.value;
            }
         }
      });

      activityEndTime.addEventListener('change', function() {
         let startDateTime = new Date(`${activityStartDate.value}T${activityStartTime.value}`);
         let endDateTime = new Date(`${activityEndDate.value}T${this.value}`);
         if (endDateTime < startDateTime) {
            // alert('Activity End Time cannot be less than Activity Start Time');
            this.value = activityStartTime.value;
         }
      });

         

      // get the durattion select and range elements
      durationSelect = document.querySelectorAll('.durationSelect');
      activityStartDateDiv = document.querySelector('.activityStartDateDiv');
      activityStartTimeDiv = document.querySelector('.activityStartTimeDiv');
      activityEndDateDiv = document.querySelector('.activityEndDateDiv');
      activityEndTimeDiv = document.querySelector('.activityEndTimeDiv');
      recurringDiv = document.querySelector('.recurringDiv'); 

      durationSelect.forEach(function(select) {
         select.addEventListener('change', function() {
            if (this.checked) {
               console.log(`selected ${this.id}`);
               // Hide all elements first
               activityStartDateDiv.classList.add('d-none'); // Hide activityDateDiv
               activityStartTimeDiv.classList.add('d-none'); // Hide activityStartTimeDiv
               activityEndDateDiv.classList.add('d-none'); // Hide activityEndDateDiv
               activityEndTimeDiv.classList.add('d-none'); // Hide activityEndTimeDiv
               recurringDiv.classList.add('d-none'); // Hide recurringDiv
               // Show the selected element
               if (this.id === 'single') {
                  activityStartDateDiv.classList.remove('d-none'); // Show activityDateDiv
                  activityStartTimeDiv.classList.add('d-none'); // Show activityStartTimeDiv
                  activityEndDateDiv.classList.add('d-none'); // Hide activityEndDateDiv
                  activityStartTimeDiv.classList.add('d-none'); // Hide activityStartTimeDiv
                  activityEndDateDiv.classList.add('d-none'); // Hide activityDateDiv
               } else if (this.id === 'duration') {
                  // activityStartTimeDiv.classList.remove('d-none'); // Show activityStartTimeDiv
                  activityEndDateDiv.classList.remove('d-none'); // Show activityEndDateDiv
                  activityEndTimeDiv.classList.remove('d-none'); // Show activityEndTimeDiv
                  activityStartDateDiv.classList.remove('d-none'); // Show activityDateDiv
                  activityStartTimeDiv.classList.remove('d-none'); // Show activityStartTimeDiv
               }
               if(this.id === 'recurring') {                         
                  recurringDiv.classList.remove('d-none'); // Show recurringDiv
               }                   
            }
         });
      });
      recurring = document.querySelector('.recurring');
      recurring.addEventListener('change', function() {
         if (this.checked) {
            console.log(`selected ${this.id}`);
            recurringDiv.classList.remove('d-none'); // Show recurringDiv
         } else {
            recurringDiv.classList.add('d-none'); // Hide recurringDiv
         }
      });

      document.getElementById('activitySegment').addEventListener('change', function() {
         var selectedSegment = this.value;
         var salesCaseDiv = document.querySelector('.salesCaseDiv');
         var projectCaseDiv = document.querySelector('.projectCaseDiv');
         if(selectedSegment == 'sales') {
            salesCaseDiv.classList.remove('d-none');
            projectCaseDiv.classList.add('d-none');
         } else if(selectedSegment == 'project') {
            projectCaseDiv.classList.remove('d-none');
            salesCaseDiv.classList.add('d-none');
         } else {
            salesCaseDiv.classList.add('d-none');
            projectCaseDiv.classList.add('d-none');
         }
      });

      // Script to edit activity
      document.querySelectorAll('.editActivityBtn').forEach(function(button) {
         button.addEventListener('click', function() {
            
            // get form
            const form = document.querySelector('#manage_activity_form');
            if(!form){
               console.log('Form not found');
               return;
            }

                  // Get all data attributes from the button
            const data = this.dataset;
            console.log(data);

               // Map form fields to their corresponding data attributes
               const fieldMappings = {
               'activityID': 'activityId',
               'orgDataID': 'orgDataId',
               'entityID': 'entityId',
               'clientID': 'clientId',
               'activityName': 'activityName',
               'activityDescription': 'activityDescription',
               'activityCategoryID': 'activityCategoryId',
               'activityTypeID': 'activityTypeId',
               'activitySegment': 'activitySegment',
               'durationType': 'durationType',
               'activityDate': 'activityDate',
               'activityStartTime': 'activityStartTime',
               'activityDurationEndTime': 'activityDurationEndTime',
               'activityDurationEndDate': 'activityDurationEndDate',
               'recurring': 'recurring',
               'recurrenceType': 'recurrenceType',
               'recurringInterval': 'recurringInterval',
               'recurringIntervalUnit': 'recurringIntervalUnit',
               'weekRecurringDays': 'weekRecurringDays',
               'monthRepeatOnDays': 'monthRepeatOnDays',
               'monthlyRepeatingDay': 'monthlyRepeatingDay',
               'customFrequencyOrdinal': 'customFrequencyOrdinal',
               'customFrequencyDayValue': 'customFrequencyDayValue',
               'recurrenceEndType': 'recurrenceEndType',
               'numberOfOccurrencesToEnd': 'numberOfOccurrencesToEnd',
               'recurringEndDate': 'recurringEndDate',
               'salesCaseID': 'salesCaseId',
               'projectID': 'projectId',
               'projectPhaseID': 'projectPhaseId',
               'projectTaskID': 'projectTaskId',
               'activityStatusID': 'activityStatusId',
               'activityPriority': 'activityPriority',
               'activityOwnerID': 'activityOwnerId',
               'activityParticipants': 'activityParticipants'
               
               }

            // Fill regular form inputs
            for (const [fieldName, dataAttribute] of Object.entries(fieldMappings)) {
               
               
               const input = form.querySelector(`[name="${fieldName}"]`);
               
               if (input) {
                  // check for the value of categoryID
                  if(fieldName === 'activityCategoryID'){
                     var categoryID = data[dataAttribute];
                     console.log(`category id is ${categoryID}`);
                     let filteredTypes = activityTypes.filter(type => type.activityCategoryID === parseInt(categoryID));
                     if (filteredTypes.length > 0) {
                        document.querySelector('.activityTypeDiv').classList.remove('d-none');
                     } else {
                        document.querySelector('.activityTypeDiv').classList.add('d-none');
                     }
                     let activityTypeSelect = document.getElementById('activityType');
                     activityTypeSelect.innerHTML = '';
                     activityTypeSelect.innerHTML += '<option value="">Select Activity Type</option>';
                     filteredTypes.forEach(type => {
                        activityTypeSelect.innerHTML += `<option value="${type.activityTypeID}">${type.activityTypeName}</option>`;
                     });
                  }

                  // check for value of durationType

                  if(fieldName === 'durationType') {
                     // console.log(`duration type is ${data[dataAttribute]}`);
                     if(data[dataAttribute] === 'duration') {
                        activityEndDateDiv.classList.remove('d-none'); // Show activityEndDateDiv
                        activityEndTimeDiv.classList.remove('d-none'); // Show activityEndTimeDiv
                        activityStartDateDiv.classList.remove('d-none'); // Show activityDateDiv
                        activityStartTimeDiv.classList.remove('d-none'); // Show activityStartTimeDiv
                     } else {
                        activityStartDateDiv.classList.remove('d-none'); // Show activityDateDiv
                        activityStartTimeDiv.classList.add('d-none'); // Show activityStartTimeDiv
                        activityEndDateDiv.classList.add('d-none'); // Hide activityEndDateDiv
                        activityStartTimeDiv.classList.add('d-none'); // Hide activityStartTimeDiv
                        activityEndDateDiv.classList.add('d-none'); // Hide activityDateDiv // Hide recurringDiv
                     }
                  }

                  if(fieldName === 'recurring') {
                     // console.log(`recurring is ${data[dataAttribute]}`);
                     if(data[dataAttribute] === 'recurring') {
                        recurringDiv.classList.remove('d-none'); // Show recurringDiv
                     } else {
                        recurringDiv.classList.add('d-none'); // Hide recurringDiv
                     }
                  }

                     // Fill the textarea with tinyMCE
                  if(fieldName === 'activityDescription') {
                     // console.log(`activityDescription is ${data[dataAttribute]}`);
                     // initialize tinyMCE if not already initialized
                     tinymce.init({
                        selector: '#activityDescription',                        
                     });
                     // handle the content of the textarea
                     const editor = tinymce.get('activityDescription');// Make sure 'entityDescription' matches your textarea's ID
                     if (editor) {
                        // Set the content of the editor
                        editor.setContent(data[dataAttribute]);
                     } else {
                        // If tinyMCE is not initialized, set the value directly
                        input.value = data[dataAttribute];
                     }
                     // tinymce.get('activityDescription').setContent(data[dataAttribute]);
                  }
                  if (input) {
                     // radio buttons
                     if (input.type === 'radio') {
                        // console.log(`fieldName: ${fieldName}, dataAttribute: ${dataAttribute}`);
                        
                        input.checked = data[dataAttribute] === input.value;
                     } else if (input.tagName === 'SELECT') {
                        
                        input.value = data[dataAttribute] || '';
                     } 
                     if (input.type === 'checkbox' ) {
                     
                     input.checked = data[dataAttribute] === input.value;
                     } else {
                        input.value = data[dataAttribute] || '';
                     }
                  }
               }
            }

            // create an array of all radios using IDs
            const radiosInput = form.querySelectorAll('input[type="radio"]');
         
            let radios = [];
            radiosInput.forEach(function(radio) {
               // console.log(radio);
               
               radios.push(radio.id);

               if(radio && radio.id === data[fieldMappings[radio.name]]) {
                  radio.checked = true;
               }
            
            
            });
            // get all radio inputs with the IDs

            // get partcipants Array from data
            console.log(data.participantsArr);

            // convert comma separated string to array
            let participants = JSON.parse(data.participantsArr);
            console.log(participants);
            let participantsdiv = document.querySelector('.participantsDiv');
            // check if participants is not empty
            let participantContent ="";
            if (participants.length > 0) {
               participantsdiv.classList.remove('d-none');
               participantsdiv.classList.add('d-flex', 'flex-wrap', 'gap-2');
               participantsdiv.innerHTML = '<label for="participants" class="col-md-12 nott mb-0 t500 text-dark">Participants</label>';
               participants.forEach(function(participant) {
                  participantContent += `<div class="me-2 p-2">
                     <a href="javascript:void(0)" class="btn  btn-icon rounded-pill btn-primary ps-2"  data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="${participant.name}">${participant.initials}</a>
                     <input type="hidden" name="participants[]" value="${participant.ID}">
                     <input type="hidden" name="participantsInitials[]" value="${participant.initials}">                    

                  </div>`;
               });
               participantsdiv.innerHTML += participantContent;
            } else {
               participantsdiv.classList.add('d-none');
            }

               
            // check typeof partcicipants
            if (Array.isArray(participants)) {
               console.log('participants is an array');
               // loop through the array and check the checkboxes
               participants.forEach(function(participant) {
                  console.log(participant);
                  
               });
            } else {
               console.log('participants is not an array');
            }
         });
      });
   });
</script>