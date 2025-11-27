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
   $activityStatusID = (isset($_POST['activityStatusID']) && !empty($_POST['activityStatusID'])) ? Utility::clean_string($_POST['activityStatusID']) : '';
   $activityStatusName = (isset($_POST['activityStatusName']) && !empty($_POST['activityStatusName'])) ? Utility::clean_string($_POST['activityStatusName']) : '';
   $activityStatusDescription = (isset($_POST['activityStatusDescription']) && !empty($_POST['activityStatusDescription'])) ? Utility::clean_string($_POST['activityStatusDescription']) : '';
  

   if(!$activityStatusID) {
      $activityStatusName ? $details['activityStatusName'] = $activityStatusName : $errors[] = 'Activity status name is required.';
      $activityStatusDescription ? $details['activityStatusDescription'] = $activityStatusDescription : $errors[] = 'Activity status description is required.';
    
      if($details){
         $details['LastUpdate'] = date('Y-m-d H:i:s');
         $details['LastUpdateByID'] = $userDetails->ID;
         if(!$DBConn->insert_data('tija_activity_status', $details)) {
            $errors[]="Failed to save the activity status details.";
         } else {
            $activityStatusID = $DBConn->lastInsertId();
         }
      }
      
   } else{
      $activityStatusDetails = Schedule::activity_status(['activityStatusID'=>$activityStatusID], true, $DBConn);

      $activityStatusName && $activityStatusName != $activityStatusDetails->activityStatusName ? $changes['activityStatusName'] = $activityStatusName : '';
      $activityStatusDescription && $activityStatusDescription != $activityStatusDetails->activityStatusDescription ? $changes['activityStatusDescription'] = $activityStatusDescription : '';
     
      if($changes){
         $changes['LastUpdated'] = date('Y-m-d H:i:s');
         $changes['LastUpdateByID'] = $userDetails->ID;
         if(!$DBConn->update_table('tija_activity_status', $changes, array('activityStatusID'=>$activityStatusID))) {
            $errors[]="Failed to update the activity status details.";
         } else {
            $success = "Activity status updated successfully.";
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