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
   $activityID = (isset($_POST['activityID']) && $_POST['activityID'] != '') ? Utility::clean_string($_POST['activityID']): '';
   $activityName = (isset($_POST['activityName']) && $_POST['activityName'] != '') ? Utility::clean_string($_POST['activityName']): '';
   $activityDate = (isset($_POST['activityDate']) && $_POST['activityDate'] != '') ? Utility::clean_string($_POST['activityDate']): '';
   $activityStartTime = (isset($_POST['activityStartTime']) && $_POST['activityStartTime'] != '') ? Utility::clean_string($_POST['activityStartTime']): '';
   $activityDurationEndDate = (isset($_POST['activityDurationEndDate']) && $_POST['activityDurationEndDate'] != '') ? Utility::clean_string($_POST['activityDurationEndDate']): '';
   $activityDurationEndTime = (isset($_POST['activityDurationEndTime']) && $_POST['activityDurationEndTime'] != '') ? Utility::clean_string($_POST['activityDurationEndTime']): '';

   if($activityID ) {
     $activityDetails = Schedule::activity_mini(array('activityID'=>$activityID), true, $DBConn);
     var_dump($activityDetails);
       if($activityDetails) {
         $activityDate && $activityDetails->activityDate !== $activityDate ? $changes['activityDate'] = $activityDate : '';
         $activityStartTime && $activityDetails->activityStartTime !== $activityStartTime ? $changes['activityStartTime'] = $activityStartTime : '';
         if($activityDetails->recurring === 'recurrent') {
            $recurrentDetails = array();
            $activityDurationEndDate && $activityDetails->activityDurationEndDate !== $activityDurationEndDate ? $changes['activityDurationEndDate'] = $activityDurationEndDate : '';
            $activityDurationEndTime && $activityDetails->activityDurationEndTime !== $activityDurationEndTime ? $changes['activityDurationEndTime'] = $activityDurationEndTime : '';

            $recurrentDetails['activityID'] = $activityID;        
            $recurrentDetails['orgDataID'] = $orgDataID;
            $recurrentDetails['entityID'] = $entityID;
            $recurrentDetails['activityInstanceDate']= $activityDate;
            $recurrentDetails['activityinstanceStartTime'] = $activityStartTime;
            $recurrentDetails['activityInstanceDurationEndDate'] = $activityDurationEndDate;
            $recurrentDetails['activityInstanceDurationEndTime'] = $activityDurationEndTime;

            var_dump($recurrentDetails);
            if($recurrentDetails){
               if(!$DBConn->insert_data('tija_recurring_activity_instances', $recurrentDetails, true)) {
                  $errors[] = "Error in updating activity";
               } else {
                  $success = "Activity postponed successfully";
                // log activity
                  Activity::log_activity(array('userID'=>$userDetails->ID, 'action'=>'postponed', 'objectType'=>'Activity', 'objectID'=>$activityID, 'objectName'=>$activityName), true, $DBConn);
               }
            }
            
         }
         var_dump($changes);
         if($changes){
            $changes['LastUpdateByID'] = $userDetails->ID;
            $changes['LastUpdate'] = date('Y-m-d H:i:s');
            if(!$DBConn->update_table('tija_activities', $changes, array('activityID'=>$activityID))) {
               $errors[] = "Error in updating activity";
            } else {
               $success = "Activity postponed successfully";
               // log activity
               Activity::log_activity(array('userID'=>$userDetails->ID, 'action'=>'postponed', 'objectType'=>'Activity', 'objectID'=>$activityID, 'objectName'=>$activityName), true, $DBConn);
            }
         }

        



         // $activityDateTime = $activityDate . ' ' . $activityStartTime;
         // $activityDurationEndDateTime = $activityDurationEndDate . ' ' . $activityDurationEndTime;
         // $details['activityID'] = $activityID;
         // $details['activityName'] = $activityName;
         // $details['activityDate'] = $activityDateTime;
         // $details['activityDurationEndDate'] = $activityDurationEndDateTime;
         // var_dump($details);
         // // update activity
         // if(Schedule::update_activity($details, true, $DBConn)) {
         //     $success = "Activity postponed successfully";
         //     // log activity
         //     Activity::log_activity(array('userID'=>$userDetails->ID, 'action'=>'postponed', 'objectType'=>'Activity', 'objectID'=>$activityID, 'objectName'=>$activityName), true, $DBConn);
         // } else {
         //     $errors[] = "Error in postponing activity";
         // }
       } else {
          $errors[] = "Error in fetching activity details";
       }
   }
      
} else {
	$errors[] = 'You need to log in as a valid user to add new sales case.';
}

var_dump($errors);
$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performance&p=home');
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