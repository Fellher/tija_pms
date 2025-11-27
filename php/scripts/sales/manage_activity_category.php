
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
   $activityCategoryName = (isset($_POST['activityCategoryName']) && !empty($_POST['activityCategoryName'])) ?  Utility::clean_string($_POST['activityCategoryName']): "";
   $activityCategoryDescription = (isset($_POST['activityCategoryDescription']) && !empty($_POST['activityCategoryDescription'])) ?  Utility::clean_string($_POST['activityCategoryDescription']): "";
   $activityCategoryID = (isset($_POST['activityCategoryID']) && !empty($_POST['activityCategoryID'])) ?  Utility::clean_string($_POST['activityCategoryID']): "";

   if(!$activityCategoryID) {
      $activityCategoryName ? $details['activityCategoryName'] = $activityCategoryName : $errors[] = 'Activity Category Name is required';
      $activityCategoryDescription ? $details['activityCategoryDescription'] = $activityCategoryDescription : $errors[] = 'Activity Category Description is required';
      if(!$errors){
         if($details) {
            $details['LastUpdatedByID']=$userDetails->ID;
            $details['LastUpdate']=$config['currentDateTimeFormated'];
            if(!$DBConn->insert_data('tija_activity_categories', $details)) {
               $errors[] = 'Error adding new Activity Category';
            } else {
               $activityCategoryID = $DBConn->lastInsertID();
            }
         }
      }
   } else {
      $activivityCategoryDetails = Sales::tija_activity_categories(array('activityCategoryID'=>$activityCategoryID), true, $DBConn);
      $activityCategoryName && $activityCategoryName != $activivityCategoryDetails->activityCategoryName ? $changes['activityCategoryName'] = $activityCategoryName : "";
      $activityCategoryDescription && $activityCategoryDescription != $activivityCategoryDetails->activityCategoryDescription ? $changes['activityCategoryDescription'] = $activityCategoryDescription : "";
      if(!$errors){
         if($changes) {
            $changes['LastUpdatedByID']=$userDetails->ID;
            $changes['LastUpdate']=$config['currentDateTimeFormated'];
            if(!$DBConn->update_table('tija_activity_categories', $changes, array('activityCategoryID'=>$activityCategoryID))) {
               $errors[] = 'Error updating Activity Category';
            }
         }
      }
   }
   if(!$errors) {
      $success = "Activity Category saved successfully";
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