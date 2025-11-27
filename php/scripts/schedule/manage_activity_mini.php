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
   $activityID= (isset($_POST['activityID']) && !empty($_POST['activityID'])) ? Utility::clean_string($_POST['activityID']) : '';
   $activityCategoryID= (isset($_POST['activityCategoryID']) && !empty($_POST['activityCategoryID'])) ? Utility::clean_string($_POST['activityCategoryID']) : '';
   $activityTypeID= (isset($_POST['activityTypeID']) && !empty($_POST['activityTypeID'])) ? Utility::clean_string($_POST['activityTypeID']) : '';
   $activityDate= (isset($_POST['activityDate']) && !empty($_POST['activityDate'])) ? Utility::clean_string($_POST['activityDate']) : '';
   $activityName= (isset($_POST['activityName']) && !empty($_POST['activityName'])) ? Utility::clean_string($_POST['activityName']) : '';
   $userID= (isset($_POST['userID']) && !empty($_POST['userID'])) ? Utility::clean_string($_POST['userID']) : '';
   $orgDataID= (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ? Utility::clean_string($_POST['orgDataID']) : '';
   $entityID= (isset($_POST['entityID']) && !empty($_POST['entityID'])) ? Utility::clean_string($_POST['entityID']) : '';



   if(!$activityID){
      $activityCategoryID ? $details['activityCategoryID'] = $activityCategoryID : $errors[] = 'Please select activity category';
      $activityTypeID ? $details['activityTypeID'] = $activityTypeID : $errors[] = 'Please select activity type';
      $activityDate ? $details['activityDate'] = $activityDate : $errors[] = 'Please select activity date';
      $activityName ? $details['activityName'] = $activityName : $errors[] = 'Please enter activity name';
      $details['activityOwnerID'] = $userID ? $userID : $userDetails->ID;
      $orgDataID ? $details['orgDataID'] = $orgDataID : $errors[] = 'Please select organisation';
      $entityID ? $details['entityID'] = $entityID : $errors[] = 'Please select entity';
      var_dump($details);

      if(!$errors){
         $details['LastUpdate']= $config['currentDateTimeFormated'];
         $details['LastUpdateByID']= $userDetails->ID;
         $details['assignedByID']= $userDetails->ID;
         if($details){
            if(!$DBConn->insert_data('tija_activities', $details)){
               $errors[] = 'Failed to add new activity';
            } else {
               $success = 'New activity added successfully';
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