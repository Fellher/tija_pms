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

   $activityTypeID = isset($_POST['activityTypeID']) ? Utility::clean_string($_POST['activityTypeID']) : '';
   $activityTypeName = isset($_POST['activityTypeName']) ? Utility::clean_string($_POST['activityTypeName']) : '';
   $activityCategoryID = isset($_POST['activityCategoryID']) ? Utility::clean_string($_POST['activityCategoryID']) : '';
   $activityTypeDescription = isset($_POST['activityTypeDescription']) ? Utility::clean_string($_POST['activityTypeDescription']) : '';
   $iconlink = isset($_POST['iconlink']) ? Utility::clean_string($_POST['iconlink']) : '';

   if(!$activityTypeID) {

      $activityTypeName && $activityTypeName != '' ? $details['activityTypeName'] = $activityTypeName : $errors[] = "Activity Type Name is required";
      $activityCategoryID && $activityCategoryID != '' ? $details['activityCategoryID'] = $activityCategoryID : $errors[] = "Activity Category is required";
      $activityTypeDescription && $activityTypeDescription != '' ? $details['activityTypeDescription'] = $activityTypeDescription : $errors[] = "Activity Type Description is required";
      $iconlink && $iconlink != '' ? $details['iconlink'] = $iconlink : $errors[] = "Icon Link is required";
      

      if(!$errors) {
         if($details){
            $details['LastUpdate'] = $config['currentDateTimeFormated'];
            $details['LastUpdatedByID'] = $userDetails->ID;
            var_dump($details);

            if(!$DBConn->insert_data('tija_activity_types', $details)){
               $errors[] = "Failed to add activity type";
            } else {
               $success = "Activity Type added successfully";
            }
         }
         
      }

   } else {
      $activityTypeDetails = Schedule::tija_activity_types(array('activityTypeID'=>$activityTypeID), true, $DBConn);
      var_dump($activityTypeDetails);

      $activityTypeName && $activityTypeName!= $activityTypeDetails->activityTypeName ? $changes['activityTypeName'] = $activityTypeName : '';
      $activityCategoryID && $activityCategoryID !=$activityTypeDetails->activityCategoryID ? $changes['activityCategoryID'] = $activityCategoryID : '';
      $activityTypeDescription && $activityTypeDescription !=$activityTypeDetails->activityTypeDescription ? $changes['activityTypeDescription'] = $activityTypeDescription : '';
      $iconlink && $iconlink !=$activityTypeDetails->iconlink ? $changes['iconlink'] = $iconlink : '';

      if(!$errors) {
         if($changes){
            var_dump($changes);
            $changes['LastUpdate'] = $config['currentDateTimeFormated'];
            $changes['LastUpdatedByID'] = $userDetails->ID;
            if(!$DBConn->update_table('tija_activity_types', $changes, array('activityTypeID'=>$activityTypeID))){
               $errors[] = "Failed to update activity type";
            } else {
               $success = "Activity Type updated successfully";
            }

         }
      }
   }

   var_dump($errors);
   
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