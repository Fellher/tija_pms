<?php
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
$success = "";
$recurrenceDateArray = array();
if ($isValidUser) {
	var_dump($_POST);
   
   $activityID = (isset($_POST['activityID']) && !empty($_POST['activityID'])) ? Utility::clean_string($_POST['activityID']) : "";
   $orgDataID =(isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ? Utility::clean_string($_POST['orgDataID']) : "";
   $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ? Utility::clean_string($_POST['entityID']) : "";  
   $clientID = (isset($_POST['clientID']) && !empty($_POST['clientID'])) ? Utility::clean_string($_POST['clientID']) : "";

   $activityCategoryID = (isset($_POST['activityCategoryID']) && !empty($_POST['activityCategoryID'])) ? Utility::clean_string($_POST['activityCategoryID']) : "";

   $activityTypeID = (isset($_POST['activityTypeID']) && !empty($_POST['activityTypeID'])) ? Utility::clean_string($_POST['activityTypeID']) : ""; 
   $activityName = (isset($_POST['activityName']) && !empty($_POST['activityName'])) ? Utility::clean_string($_POST['activityName']) : "";
   $durationType = (isset($_POST['durationType']) && !empty($_POST['durationType'])) ? Utility::clean_string($_POST['durationType']) : "";

   $activityDate = (isset($_POST['activityDate']) && !empty($_POST['activityDate']) && (preg_match($config['ISODateFormat'], Utility::clean_string($_POST['activityDate'])))) ? Utility::clean_string($_POST['activityDate']) : "";
   
   $activityStartTime = (isset($_POST['activityStartTime']) && !empty($_POST['activityStartTime'])) ? Utility::clean_string($_POST['activityStartTime']) : ""; 
   $activityDurationEndDate= (isset($_POST['activityDurationEndDate']) && !empty($_POST['activityDurationEndDate']) && (preg_match($config['ISODateFormat'], Utility::clean_string($_POST['activityDurationEndDate'])))) ? Utility::clean_string($_POST['activityDurationEndDate']) : "";
   $activityDurationEndTime = (isset($_POST['activityDurationEndTime']) && !empty($_POST['activityDurationEndTime'])) ? Utility::clean_string($_POST['activityDurationEndTime']) : "";

   $recurring = (isset($_POST['recurring']) && !empty($_POST['recurring'])) ? Utility::clean_string($_POST['recurring']) : "";
   $recurrenceType = (isset($_POST['recurrenceType']) && !empty($_POST['recurrenceType'])) ? Utility::clean_string($_POST['recurrenceType']) : "";
   $recurringInterval = (isset($_POST['recurringInterval']) && !empty($_POST['recurringInterval'])) ? Utility::clean_string($_POST['recurringInterval']) : "";
   $recurringIntervalUnit = (isset($_POST['recurringIntervalUnit']) && !empty($_POST['recurringIntervalUnit'])) ? Utility::clean_string($_POST['recurringIntervalUnit']) : "";

   // monthly variables
   $monthRepeatOnDays = (isset($_POST['monthRepeatOnDays']) && !empty($_POST['monthRepeatOnDays'])) ? Utility::clean_string($_POST['monthRepeatOnDays']) : ""; // month repeat on days

   $monthlyRepeatingDay = (isset($_POST['monthlyRepeatingDay']) && !empty($_POST['monthlyRepeatingDay'])) ? Utility::clean_string($_POST['monthlyRepeatingDay']) : "";
   $customFrequencyOrdinal = (isset($_POST['customFrequencyOrdinal']) && !empty($_POST['customFrequencyOrdinal'])) ? Utility::clean_string($_POST['customFrequencyOrdinal']) : "";
   $customFrequencyDayValue = (isset($_POST['customFrequencyDayValue']) && !empty($_POST['customFrequencyDayValue'])) ? Utility::clean_string($_POST['customFrequencyDayValue']) : "";

   // $numberOfOccurrencesToEnd = (isset($_POST['numberOfOccurrencesToEnd']) && !empty($_POST['numberOfOccurrencesToEnd'])) ? Utility::clean_string($_POST['numberOfOccurrencesToEnd']) : ""; // number of occurrences
   // $recurringDays = (isset($_POST['recurringDays']) && !empty($_POST['recurringDays'])) ? $_POST['recurringDays'] : ""; // recurring days

   $repeatDays = (isset($_POST['repeatDays']) && !empty($_POST['repeatDays'])) ? $_POST['repeatDays'] : ""; // repeat days

   // weekly variables
   $weekRecurringDays = (isset($_POST['weekRecurringDays']) && !empty($_POST['weekRecurringDays']) && is_array($_POST['weekRecurringDays'])) ? $_POST['weekRecurringDays'] : ""; // week recurring days


   // end date variables
   $recurrenceEndType = (isset($_POST['recurrenceEndType']) && !empty($_POST['recurrenceEndType'])) ? Utility::clean_string($_POST['recurrenceEndType']) : "";
   $numberOfOccurrencesToEnd = (isset($_POST['numberOfOccurrencesToEnd']) && !empty($_POST['numberOfOccurrencesToEnd'])) ? Utility::clean_string($_POST['numberOfOccurrencesToEnd']) : "";
   $recurringEndDate = (isset($_POST['recurringEndDate']) && !empty($_POST['recurringEndDate']) && (preg_match($config['ISODateFormat'], Utility::clean_string($_POST['recurringEndDate'])))) ? Utility::clean_string($_POST['recurringEndDate']) : "";

  
   // Activity Details
   $activityDescription = (isset($_POST['activityDescription']) && !empty($_POST['activityDescription'])) ? $_POST['activityDescription']: "";
   $activitySegment = (isset($_POST['activitySegment']) && !empty($_POST['activitySegment'])) ? Utility::clean_string($_POST['activitySegment']) : "";
   $salesCaseID = (isset($_POST['salesCaseID']) && !empty($_POST['salesCaseID'])) ? Utility::clean_string($_POST['salesCaseID']) : "";
   $projectID = (isset($_POST['projectID']) && !empty($_POST['projectID'])) ? Utility::clean_string($_POST['projectID']) : "";
  
   $activityStatusID = (isset($_POST['activityStatusID']) && !empty($_POST['activityStatusID'])) ? Utility::clean_string($_POST['activityStatusID']) : "";
   $activityOwnerID = (isset($_POST['activityOwnerID']) && !empty($_POST['activityOwnerID'])) ? Utility::clean_string($_POST['activityOwnerID']) : "";
  
   $subTaskID = (isset($_POST['subTaskID']) && !empty($_POST['subTaskID'])) ? Utility::clean_string($_POST['subTaskID']) : "";


   $activityTime = (isset($_POST['activityTime']) && !empty($_POST['activityTime'])) ? Utility::clean_string($_POST['activityTime']) : "";
  
   $activityParticipants = (isset($_POST['activityParticipants']) && !empty($_POST['activityParticipants']) && is_array($_POST['activityParticipants'])) ? implode(',', $_POST['activityParticipants']) : "";
   $activityNotes = (isset($_POST['activityNotes']) && !empty($_POST['activityNotes'])) ? $_POST['activityNotes'] : "";  
   $assignedByID = (isset($_POST['assignedByID']) && !empty($_POST['assignedByID'])) ? Utility::clean_string($_POST['assignedByID']) : $userDetails->ID; 

   $collaborationID = (isset($_POST['collaborationID']) && !empty($_POST['collaborationID'])) ? Utility::clean_string($_POST['collaborationID']) : "";
   $taskID = (isset($_POST['taskID']) && !empty($_POST['taskID'])) ? Utility::clean_string($_POST['taskID']) : ""; 
   $activityPriority= (isset($_POST['activityPriority']) && !empty($_POST['activityPriority'])) ? Utility::clean_string($_POST['activityPriority']) : "";   
   $activityLocation =(isset($_POST['activityLocation']) && !empty($_POST['activityLocation'])) ? Utility::clean_string($_POST['activityLocation']) : "";
  
  var_dump($activityID);

   if(!$activityID) {
      echo "<h5>  Adding New Activity</h5>";
      // mandatory fields
      $orgDataID ? $details['orgDataID'] = $orgDataID : $errors[] = 'Organisation ID is required';
      $entityID ? $details['entityID'] = $entityID : $errors[] = 'Entity ID is required';
      $activityName ? $details['activityName'] = $activityName : $errors[] = 'Activity Name is required';     
      $activityDate ? $details['activityDate'] = $activityDate : $errors[] = 'Activity Date is required';
      $activityCategoryID ? $details['activityCategoryID'] = $activityCategoryID : $errors[] = 'Activity Category ID is required';
      $clientID ? $details['clientID'] = $clientID : "";
      $activityTypeID ? $details['activityTypeID'] = $activityTypeID : "";
      $durationType ? $details['durationType'] = $durationType : $errors[] = 'Duration Type is required';
      $activityStartTime ? $details['activityStartTime'] = $activityStartTime : "";

      if($durationType === "duration") {
         $activityStartTime ? $details['activityStartTime'] = $activityStartTime : $errors[] = 'Activity Start Time is required';
         $activityDurationEndTime ? $details['activityDurationEndTime'] = $activityDurationEndTime : $errors[] = 'Activity End Time is required';
         $activityDurationEndDate ? $details['activityDurationEndDate'] = $activityDurationEndDate : $errors[] = 'Activity End Date is required';
      } elseif($durationType === "single"){
         $activityStartTime ? $details['activityStartTime'] = $activityStartTime : $errors[] = 'Activity Start Time is required';
      }

      if($recurring === 'recurring' || $recurring === 'Y'){
         $recurrenceDateArray = array();
         $recurring ? $details['recurring'] = $recurring : "";
         $recurrenceType ? $details['recurrenceType'] = $recurrenceType : $errors[] = 'Recurrence Frequency is required';
         $recurringInterval ? $details['recurringInterval'] = $recurringInterval : $errors[] = 'Recurring Interval is required';        
         $recurringIntervalUnit ? $details['recurringIntervalUnit'] = $recurringIntervalUnit : $errors[] = 'Recurrence Frequency Unit is required';
         if($recurrenceType == 'weekly'){
            $weekRecurringDays && is_array($weekRecurringDays) ? $details['weekRecurringDays'] =  implode( ",", $weekRecurringDays) : $errors[] = 'Recurring Days are required';
         }  
         if($recurrenceType == 'monthly'){
            if($monthRepeatOnDays && $monthRepeatOnDays== "customDays" ){
               $customFrequencyOrdinal ? $details['customFrequencyOrdinal'] = $customFrequencyOrdinal : "";
               $customFrequencyDayValue ? $details['customFrequencyDayValue'] = $customFrequencyDayValue : "";
            } else {
               $monthlyRepeatingDay ? $details['monthlyRepeatingDay'] = $monthlyRepeatingDay : $errors[] = 'Monthly Repeating Day is required';
            }        
           
         } elseif($recurrenceType == 'yearly'){
            // yearly
         }
         $recurrenceEndType ? $details['recurrenceEndType'] = $recurrenceEndType : $errors[] = 'Recurrence End Type is required';
         if($recurrenceEndType == 'occurrences'){
            $numberOfOccurrencesToEnd ? $details['numberOfOccurrencesToEnd'] = $numberOfOccurrencesToEnd : $errors[] = 'Number of Occurrences is required';           
         } elseif($recurrenceEndType == 'endDate'){
            $recurringEndDate ? $details['recurringEndDate'] = $recurringEndDate : $errors[] = 'Recurrence End Date is required';
         }

         if($recurrenceEndType == 'noEndDate') {
            $recurringEndDate = date('Y-12-31');
         }

         if($recurringEndDate){
            echo "<h5>  Recurrence End Date or no end</h5>";
            if(strtotime($recurringEndDate) < strtotime($activityDate)){
               $errors[] = 'Recurrence End Date should be greater than Activity Start Date';
            } else {
               if($recurrenceType === "weekly"){
                  $recurrenceIntervalInDays = $recurringInterval * 7;
                  while (strtotime($currentDate) < strtotime($recurringEndDate)) {
                     $currentDate= date('Y-m-d', strtotime($currentDate . ' + ' . $recurrenceIntervalInDays . ' days'));
                       $recurrenceDateArr[] = $currentDate;
                 }
               }
               if($recurrenceType === "monthly"){
                  $recurrenceIntervalInDays = 0;                 
                  while (strtotime($activityDate) < strtotime($recurringEndDate)) {
                     $recurrenceIntervalInDays = $recurringInterval * 30;
                     $activityDate= date('Y-m-d', strtotime($activityDate . ' + ' . $recurrenceIntervalInDays . ' days'));
                     if(strtotime($activityDate) < strtotime($recurringEndDate)){
                        $recurrenceDateArr[] = $activityDate;
                     }                      
                 }
                 if($recurrenceDateArr){
                     foreach($recurrenceDateArr as $date){
                        // check if date falls on a saturday or sunday and change to next working day
                        $dayOfWeek = date('N', strtotime($date));
                        // echo "<h5>  Recurrence Date {$date} and Day of Week {$dayOfWeek}</h5>";
                        $monthNumber = date('n', strtotime($date));
                        $day = date('j', strtotime($date));
                        if($dayOfWeek == 6){
                           $date = date('Y-m-d', strtotime($date . ' + 2 days'));
                        } elseif($dayOfWeek == 7){
                           $date = date('Y-m-d', strtotime($date . ' + 1 days'));
                        }
                        echo "<h5>  Recurrence Date {$date} and Day of Week {$dayOfWeek}</h5>";
                        $recurrenceDateArray[] = $date;
                        var_dump($recurrenceDateArray);
                        // echo "<h5>  Recurrence Month Number {$monthNumber} and Day {$day}</h5>";
                     }
                 }
                 var_dump($recurrenceDateArray);
               }
            }

         } 
      
         if($recurrenceEndType == 'occurrences') {
            echo "<h5>  Recurrence Occurrences {$numberOfOccurrencesToEnd}</h5>";;
            if($recurrenceType === "weekly"){
               $recurrenceIntervalInDays = 0;
               for($i=0; $i < $numberOfOccurrencesToEnd; $i++){
                  $recurrenceIntervalInDays += $recurringInterval * 7;
                  $recurringDateCalculation = date('Y-m-d', strtotime($activityDate . ' + ' . $recurrenceIntervalInDays . ' days'));
                  $recurrenceDateArr[] = $recurringDateCalculation;
               }
               $weekNumbers = [];
               foreach ($recurrenceDateArr as $date) {
                  $weekNumber = date('W', strtotime($date));
                  $weekNumbers[] = $weekNumber;
                  $year= date('o', strtotime($date));
                  if($weekRecurringDays){
                     foreach($weekRecurringDays as $day){
                        // echo "<h5>  Recurrence Week Number {$weekNumber} and Day {$day}</h5>";
                        $dateOfWeek = date('Y-m-d', strtotime($date . ' ' . $day));
                        echo "<h5>  Recurrence Week Number {$weekNumber} and Day {$day} ad date is {$dateOfWeek}</h5>";
                        $recurrenceDateArray[] = $dateOfWeek;
                     }
                  }  
               }         
            }
            if($recurrenceType === "monthly"){
               $recurrenceIntervalInDays = 0;
               for($i=0; $i < $numberOfOccurrencesToEnd; $i++){
                  $recurrenceIntervalInDays += $recurringInterval * 30;
                  $recurringDateCalculation = date('Y-m-d', strtotime($activityDate . ' + ' . $recurrenceIntervalInDays . ' days'));
                  $recurrenceDateArr[] = $recurringDateCalculation;
               }
               var_dump($recurrenceDateArr);
               if($recurrenceDateArr){
                  foreach($recurrenceDateArr as $date){
                     // check if date falls on a saturday or sunday and change to next working day
                     $dayOfWeek = date('N', strtotime($date));
                     // echo "<h5>  Recurrence Date {$date} and Day of Week {$dayOfWeek}</h5>";
                     $monthNumber = date('n', strtotime($date));
                     $day = date('j', strtotime($date));
                     if($dayOfWeek == 6){
                        $date = date('Y-m-d', strtotime($date . ' + 2 days'));
                     } elseif($dayOfWeek == 7){
                        $date = date('Y-m-d', strtotime($date . ' + 1 days'));
                     }
                     echo "<h5>  Recurrence Date {$date} and Day of Week {$dayOfWeek}</h5>";
                     $recurrenceDateArray[] = $date;
                     var_dump($recurrenceDateArray);
                     // echo "<h5>  Recurrence Month Number {$monthNumber} and Day {$day}</h5>";
                  }
              }
            }
            
         }
         echo "<h5>  Recurrence Dates</h5>";
         var_dump($recurrenceDateArray);

         // if($recurrenceEndType == 'endDate' && $recurringEndDate) {
         //    $recurringEndDate ? $details['recurringEndDate'] = $recurringEndDate : $errors[] = 'Recurrence End Date is required';
         //    if($recurringEndDate && $activityDate){
         //       if(strtotime($recurringEndDate) < strtotime($activityDate)){
         //          $errors[] = 'Recurrence End Date should be greater than Activity Date';
         //       } else {
         //          if($recurrenceType === "weekly"){
         //             $recurrenceIntervalInDays = $recurringInterval * 7;
         //             // for weekly recurrence check if the activity date is in the week of the recurrence end date
         //             $currentDate = $activityDate;
         //             $recurrenceDateArr = array();
         //             $recurrenceDateArr[] = $activityDate;
         //             while (strtotime($currentDate) < strtotime($recurringEndDate)) {
         //                 $currentDate= date('Y-m-d', strtotime($currentDate . ' + ' . $recurrenceIntervalInDays . ' days'));
         //                   $recurrenceDateArr[] = $currentDate;
         //             }

         //             var_dump($recurrenceDateArr);
         //             if (strtotime($currentDate) > strtotime($recurringEndDate)) {
         //                 $errors[] = 'Activity Date exceeds Recurrence End Date';
         //             }
         //             $recurringDateCalculation = date('Y-m-d', strtotime($activityDate . ' + ' . $recurrenceIntervalInDays . ' days'));
         //          }
         //       }
         //    }
         // } elseif($recurrenceEndType == 'occurrences' && $numberOfOccurrencesToEnd) {
         //    $numberOfOccurrencesToEnd ? $details['numberOfOccurrencesToEnd'] = $numberOfOccurrencesToEnd : $errors[] = 'Number of Occurrences is required';
         // }

         

      }

      $activityDescription ? $details['activityDescription'] = $activityDescription : "";
      $activitySegment ? $details['activitySegment'] = $activitySegment : "";

      if($activitySegment === "project") {
         $projectID ? $details['projectID'] = $projectID : $errors[] = 'Project ID is required';
        
         $activityStatusID ? $details['activityStatusID'] = $activityStatusID : $errors[] = 'Activity Status is required';
      } elseif($activitySegment === "sales") {
         $salesCaseID ? $details['salesCaseID'] = $salesCaseID : "";
      } elseif($activitySegment === "collaboration") {
         $collaborationID ? $details['collaborationID'] = $collaborationID : "";
         $activityStatusID ? $details['activityStatusID'] = $activityStatusID : $errors[] = 'Activity Status is required';
      } elseif($activitySegment === "task") {
         $taskID ? $details['taskID'] = $taskID : "";
      }

      $activityPriority ? $details['activityPriority'] = $activityPriority : $errors[] = 'Activity Priority is required';
    
      $activityOwnerID ? $details['activityOwnerID'] = $activityOwnerID : $errors[] = 'Activity Owner ID is required';
      $activityParticipants ? $details['activityParticipants'] = $activityParticipants : "";
      // ! create activityParticipants detabase table toallocate participants to activity
 


     
      echo "<h5>  Details</h5>";
      var_dump($details);

      
      
      if(!$errors){
         $details['assignedByID'] = $userDetails->ID;
         $details['LastUpdate'] = date('Y-m-d H:i:s');
         $details['LastUpdateByID'] = $userDetails->ID;
         if(!$DBConn->insert_data('tija_activities', $details)){
            $errors[] = 'Failed to add new activity';
         } else {
            $success = 'New activity added successfully';
            $activityID = $DBConn->lastInsertId();
            $activityDetails = Schedule::tija_activities(array("activityID"=> $activityID), true, $DBConn);
            var_dump($activityDetails);
            echo "<h5>  Activity Participant Details ID</h5>";
            var_dump($details['activityParticipants']);
            var_dump($activityParticipants);
            $activityParticipantsArray = explode(',', $activityParticipants);
            if(is_array($activityParticipantsArray)){
               foreach($activityParticipantsArray as $participant){
                  $activityParticipantDetails = array(
                     'activityID' => $activityID,
                     'participantUserID' => $participant,
                     'activityOwnerID' => $activityOwnerID,
                     'recurring'=> $recurring == 'recurring' ? 'Y' : "N",
                     'recurringInterval'=> $recurringInterval? $recurringInterval : "",
                     'recurringIntervalUnit'=> $recurringIntervalUnit? $recurringIntervalUnit : "",
                     'activityStartDate'=> $activityDate,
                     'activityEndDate'=> $activityDurationEndDate,
                     'LastUpdateByID'=> $userDetails->ID,
                     'createdByID' => $userDetails->ID,
                     'LastUpdate' => date('Y-m-d H:i:s'),
                  );
                  var_dump($activityParticipantDetails);
                  if(!$DBConn->insert_data('tija_activity_participant_assignment', $activityParticipantDetails)){
                     $errors[] = 'Failed to add activity participants';
                  } else {
                     $success = 'New activity participants added successfully';
                     $employeeDetails = Employee::employees(array('ID'=>$participant), true, $DBConn);
                     $activityOwnerDetails = Employee::employees(array('ID'=>$activityOwnerID), true, $DBConn);
                     $assigneeDetails = Employee::employees(array('ID'=>$userDetails->ID), true, $DBConn);
                     $notificationArr = array(
                        'employeeID' => $participant,
                        'approverID' => $userDetails->ID,
                        'segmentType'=> "activity",
                        "segmentID" => $activityID,
                        "notificationNotes" => "<p>New activity <strong>{$activityName}</strong> has been assigned to you by {$assigneeDetails->employeeNameWithInitials}. The activity Owner is {$assigneeDetails->employeeNameWithInitials}. Please contact {$assigneeDetails->employeeNameWithInitials} for guidance </p>
                        <p> The activity Date is {$activityDate} and Start Time is {$activityStartTime} </p> 

                                                <p><a href='{$base}html/?s=user&ss=schedule&p=activity_details&activityID={$activityID}'>View Activity</a></p>
                                                <p> You have been assigned to this activity.</p>",
                        'notificationType' => "{$durationType}_activities_assigned_{$activityDetails->activityTypeName}",
                        'notificationText' => "New activity <strong>{$activityName}</strong> has been assigned to you by {$employeeDetails->employeeNameWithInitials}
                                                <p> You have been assigned to this activity.</p>
                                                <a href='{$config['siteURL']}html/?s=user&ss=schedule&p=activity_details&activityID={$activityID}'>View Activity</a>",
                        'notificationStatus' => 'unread',
                        'originatorUserID' => $userDetails->ID,
                        'targetUserID' => $participant,
                        
                     );
                     if($notificationArr) {
                        echo "<h5>  Notification Details</h5>";
                        var_dump($notificationArr);
                        if(!$DBConn->insert_data('tija_notifications', $notificationArr)) {
                           $errors[] = 'Failed to create notification for activity assignment';
                        } else {
                           $success .= ' and notification created successfully';
                        }
                     }
                  }
               }
            }
            if($recurrenceDateArray){
               foreach ($recurrenceDateArray as $Datekey => $recurrenceDate) {
                  $instanceCount = $Datekey + 1;
                  $recurrenceDetailsArr = array(
                     'activityID' => $activityID,
                     'activityInstanceDate'=> $recurrenceDate,
                     'activityinstanceStartTime'=> $activityStartTime,
                  
                     'activityInstanceDurationEndTime'=> $activityDurationEndTime,
                     'instanceCount'=> $instanceCount,
                     'orgDataID'=>$orgDataID,
                     'entityID'=>$entityID,
                     'activityInstanceOwnerID'=> $activityOwnerID
                  );
                  var_dump($recurrenceDetailsArr);
                  if(!$DBConn->insert_data('tija_recurring_activity_instances', $recurrenceDetailsArr)){
                     $errors[] = 'Failed to add activity instance';
                  } else {
                     $success = 'New activity instance added successfully';
                  }
               }
            }
         }
      } 

   } else {
      $activityDetails = Schedule::activity_mini(array("activityID"=> $activityID), true, $DBConn);

      var_dump($activityDetails);
      $orgDataID && $activityDetails->orgDataID != $orgDataID ? $changes['orgDataID'] = $orgDataID : "";
      $entityID && $activityDetails->entityID != $entityID ? $changes['entityID'] = $entityID : "";
      $clientID && $activityDetails->clientID != $clientID ? $changes['clientID'] = $clientID : "";
      $activityName && $activityDetails->activityName != $activityName ? $changes['activityName'] = $activityName : "";
      $activityDate && $activityDetails->activityDate != $activityDate ? $changes['activityDate'] = $activityDate : "";
      $activityCategoryID && $activityDetails->activityCategoryID != $activityCategoryID ? $changes['activityCategoryID'] = $activityCategoryID : "";
      $activityTypeID && $activityDetails->activityTypeID != $activityTypeID ? $changes['activityTypeID'] = $activityTypeID : "";
      $activitySegment && $activityDetails->activitySegment != $activitySegment ? $changes['activitySegment'] = $activitySegment : "";
      $salesCaseID && $activityDetails->salesCaseID != $salesCaseID ? $changes['salesCaseID'] = $salesCaseID : "";
      $projectID && $activityDetails->projectID != $projectID ? $changes['projectID'] = $projectID : "";
      $projectPhaseID && $activityDetails->projectPhaseID != $projectPhaseID ? $changes['projectPhaseID'] = $projectPhaseID : "";
      $projectTaskID && $activityDetails->projectTaskID != $projectTaskID ? $changes['projectTaskID'] = $projectTaskID : "";
      $activityStatusID && $activityDetails->activityStatusID != $activityStatusID ? $changes['activityStatusID'] = $activityStatusID : "";
      $activityOwnerID && $activityDetails->activityOwnerID != $activityOwnerID ? $changes['activityOwnerID'] = $activityOwnerID : "";
      $activityDescription && $activityDetails->activityDescription != $activityDescription ? $changes['activityDescription'] = $activityDescription : "";
      $activityTime && $activityDetails->activityTime != $activityTime ? $changes['activityTime'] = $activityTime : "";
      $durationType && $activityDetails->durationType != $durationType ? $changes['durationType'] = $durationType : "";
      $activityParticipants && $activityDetails->activityParticipants != $activityParticipants ? $changes['activityParticipants'] = $activityParticipants : "";
      $activityNotes && $activityDetails->activityNotes != $activityNotes ? $changes['activityNotes'] = $activityNotes : "";
      $assignedByID && $activityDetails->assignedByID != $assignedByID ? $changes['assignedByID'] = $assignedByID : "";
   }


   
} else {
	$errors[] = 'You need to log in as a valid user to add new sales case.';
}

var_dump($errors);
$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
var_dump($returnURL);
 if (count($errors) == 0) {
	 $DBConn->commit();
	 $messages = array(array('Text'=>$success, 'Type'=>'success'));
 } else {
	 $DBConn->rollback();
	 $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
 }
 $_SESSION['FlashMessages'] = serialize($messages);
 header("location:{$base}html/{$returnURL}");
?>