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
if ($isValidUser) {
	var_dump($_POST);
   var_dump($_FILES);
   $activityID = (isset($_POST['activityID']) && !empty($_POST['activityID'])) ? Utility::clean_string($_POST['activityID']) : '';
   $complete = (isset($_POST['complete']) && !empty($_POST['complete'])) ? Utility::clean_string($_POST['complete']) : '';
   $employeeID = (isset($_POST['employeeID']) && !empty($_POST['employeeID'])) ? Utility::clean_string($_POST['employeeID']) : $userDetails->ID;
   $workSegmentID = (isset($_POST['workSegmentID']) && !empty($_POST['workSegmentID'])) ? Utility::clean_string($_POST['workSegmentID']) : '';
   $taskType = (isset($_POST['taskType']) && !empty($_POST['taskType'])) ? Utility::clean_string($_POST['taskType']) : '';
   $instance = (isset($_POST['instance']) && !empty($_POST['instance'])) ? Utility::clean_string($_POST['instance']) : '';
   $recurringInstanceID = (isset($_POST['recurringInstanceID']) && !empty($_POST['recurringInstanceID'])) ? Utility::clean_string($_POST['recurringInstanceID']) : '';
   $activityDate = (isset($_POST['activityDate']) && !empty($_POST['activityDate'])) ? Utility::clean_string($_POST['activityDate']) : '';
   $workTypeID = (isset($_POST['workTypeID']) && !empty($_POST['workTypeID'])) ? Utility::clean_string($_POST['workTypeID']) : '';
   $activityDuration = (isset($_POST['activityDuration']) && !empty($_POST['activityDuration'])) ? Utility::clean_string($_POST['activityDuration']) : '';
   $taskNarrative = (isset($_POST['taskNarrative']) && !empty($_POST['taskNarrative'])) ? Utility::clean_string($_POST['taskNarrative']) : '';
   $activityStatusID = (isset($_POST['activityStatusID']) && !empty($_POST['activityStatusID'])) ? Utility::clean_string($_POST['activityStatusID']) : '';
   

   if($instance && $instance =='Y'){
      if($recurringInstanceID) {
         $recurringInstanceDetails = Schedule::recurring_activity_instances(['recurringInstanceID'=>$recurringInstanceID], true, $DBConn);
         echo "<h2> Recurring Instance Details</h2>";
         var_dump($recurringInstanceDetails);
         if((int)$recurringInstanceDetails->activityID === (int)$activityID) {
            $activityDetails = Schedule::tija_activities(['activityID'=>$activityID], true, $DBConn);
            echo "<h2> Activity Details</h2>";
            var_dump($activityDetails);
            // Get the details to update the task time log table
            $taskLogDetails = array();
            $taskLogDetails['taskActivityID'] = $activityID;
            $taskLogDetails['taskDate']= $activityDate;
            $taskLogDetails['employeeID'] = $employeeID;
            $taskLogDetails['projectID'] = $activityDetails->projectID ? $activityDetails->projectID : "";
            $taskLogDetails['workTypeID'] = $workTypeID;
            $taskLogDetails['workSegmentID'] = $workSegmentID;
            $taskLogDetails['taskNarrative'] = $taskNarrative;
            $taskLogDetails['taskType'] = $taskType;
            $taskLogDetails['recurringInstanceID'] = $recurringInstanceDetails->recurringInstanceID;
            $taskLogDetails['taskDuration'] = $activityDuration;
            $taskLogDetails['dailyComplete'] = $complete;
            $taskLogDetails['taskStatusID'] = $activityStatusID;

            var_dump($taskLogDetails);

            if($taskLogDetails){
               if(!$DBConn->insert_data('tija_tasks_time_logs', $taskLogDetails)){
                  $errors[]="Failed to insert the task time log details.";
               } else {
                  $success = "Task time log details inserted successfully.";
                  $recurringInstanceChanges = array('activityStatusID'=>4, 'LastUpdate'=>date('Y-m-d H:i:s'), 'LastUpdateByID'=>$userDetails->ID, 'completed'=>'Y', 'dateCompleted'=>$dt->format('Y-m-d H:i:s'));
                  if(!$DBConn->update_table('tija_recurring_activity_instances', $recurringInstanceChanges, array('recurringInstanceID'=>$recurringInstanceID))) {
                     $errors[] = "Failed to update the recurring activity status details.";
                  } else {
                     $success = "Recurring activity status updated successfully.";
                  }
               }
            } else {
               $errors[] = "Failed to get the task time log details.";
            }



            
           
            if(!$DBConn->update_table('tija_recurring_activity_instances', $recurringInstanceChanges, array('recurringInstanceID'=>$recurringInstanceID))) {
               $errors[] = "Failed to update the recurring activity status details.";
            } else {
               $success = "Recurring activity status updated successfully.";
            }
         } else {
            $errors[] = "Recurring instance ID does not match the activity ID.";

         }
         
      } else {
         $errors[] = "Recurring instance ID is required. You are trying to complete a recurring activity without the recurring instance details.";
      }
   } else {
      $activityDetails = Schedule::tija_activities(['activityID'=>$activityID], true, $DBConn);
      echo "<h2> Activity Details</h2>";
      var_dump($activityDetails);
      // Get the details to update the task time log table
      $taskLogDetails = array();
      $taskLogDetails['taskActivityID'] = $activityID;
      $taskLogDetails['taskDate']= $activityDate;
      $taskLogDetails['employeeID'] = $employeeID;
      $taskLogDetails['projectID'] = $activityDetails->projectID ? $activityDetails->projectID : "";
      $taskLogDetails['workTypeID'] = $workTypeID;
      $taskLogDetails['workSegmentID'] = $workSegmentID;
      $taskLogDetails['taskNarrative'] = $taskNarrative;
      $taskLogDetails['taskType'] = $taskType;
      $taskLogDetails['recurringInstanceID'] = null;
      $taskLogDetails['taskDuration'] = $activityDuration;
      $taskLogDetails['dailyComplete'] = 'Y';
      $taskLogDetails['clientID'] = $activityDetails->clientID;


      echo "<h5> Activity status id is ".$activityDetails->activityStatusID."</h5>";
      if($activityDetails) {
         if($activityDetails->activityStatusID == 4) {
            // activity is already completed
            // do not insert the task time log details
            // set the error message
            $errors[] = "Activity is already completed.";
         } else {
            var_dump($taskLogDetails);
            // insert the task time log details
            if($taskLogDetails){
               if(!$DBConn->insert_data('tija_tasks_time_logs', $taskLogDetails)){
                  $errors[]="Failed to insert the task time log details.";
               } else {
                  $success = "Task time log details inserted successfully.";
                  if(!$DBConn->update_table('tija_activities', array('activityStatusID'=>4, 'LastUpdate'=>date('Y-m-d H:i:s'), 'LastUpdateByID'=>$userDetails->ID), array('activityID'=>$activityID))) {
                     $errors[]="Failed to update the activity status details.";
                  } else {
                     $success = "Activity status updated successfully.";
                  }
               }
            } else {
               $errors[] = "Failed to get the task time log details.";
            }
         }
      }
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