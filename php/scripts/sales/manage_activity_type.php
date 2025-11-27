
<?php
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
$success="";
if ($isValidUser) {
	var_dump($_POST);

   $activityTypeID = (isset($_POST['activityTypeID']) && !empty($_POST['activityTypeID'])) ?  Utility::clean_string($_POST['activityTypeID']): "";
   $activityTypeName = (isset($_POST['activityTypeName']) && !empty($_POST['activityTypeName'])) ?  Utility::clean_string($_POST['activityTypeName']): "";
   $activityTypeDescription = (isset($_POST['activityTypeDescription']) && !empty($_POST['activityTypeDescription'])) ?  Utility::clean_string($_POST['activityTypeDescription']): "";

   if(!$activityTypeID) {
      $activityTypeName ? $details['activityTypeName'] = $activityTypeName : $errors[] = 'Activity Type Name is required';
      $activityTypeDescription ? $details['activityTypeDescription'] = $activityTypeDescription : $errors[] = 'Activity Type Description is required';
      if(!$errors){
         if($details) {
            $details['LastUpdatedByID']=$userDetails->ID;
            $details['LastUpdate']=$config['currentDateTimeFormated'];
            if(!$DBConn->insert_data('tija_activity_types', $details)) {
               $errors[] = 'Error adding new Activity Type';
            } else {
               $activityTypeID = $DBConn->lastInsertID();
            }
         }
      }
   } else {
      $activivityTypeDetails = Sales::tija_activity_types(array('activityTypeID'=>$activityTypeID), true, $DBConn);
      var_dump($activivityTypeDetails);
      $activityTypeName && $activityTypeName != $activivityTypeDetails->activityTypeName ? $changes['activityTypeName'] = $activityTypeName : "";
      $activityTypeDescription && $activityTypeDescription != $activivityTypeDetails->activityTypeDescription ? $changes['activityTypeDescription'] = $activityTypeDescription : "";
      if(!$errors){
         if($changes) {
            $changes['LastUpdatedByID']=$userDetails->ID;
            $changes['LastUpdate']=$config['currentDateTimeFormated'];
            if(!$DBConn->update_table('tija_activity_types', $changes, array('activityTypeID'=>$activityTypeID))) {
               $errors[] = 'Error updating Activity Type';
            }
         }
      }
   }

   var_dump($errors);

   $returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
	var_dump($returnURL);
} else {
	$errors[] = 'You need to log in as a valid administrator to do that.';
}

if (count($errors) == 0) {
	$DBConn->commit();
	$messages = array(array('Text'=>"{$success}", 'Type'=>'success'));
} else {
 	$DBConn->rollback();
 	$messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
}
 $_SESSION['FlashMessages'] = serialize($messages);
 header("location:{$base}html/{$returnURL}");