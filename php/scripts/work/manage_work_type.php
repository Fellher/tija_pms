<?php
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes=array();
$success = "";

if ( $isValidUser) {

   $posts= $_POST;
   var_dump($posts);
   $workTypeID = (isset($_POST['workTypeID']) && !empty($_POST['workTypeID'])) ? Utility::clean_string($_POST['workTypeID']) : null;
   $workTypeName = (isset($_POST['workTypeName']) && !empty($_POST['workTypeName'])) ? Utility::clean_string($_POST['workTypeName']) : null;
   $workTypeDescription = (isset($_POST['workTypeDescription']) && !empty($_POST['workTypeDescription'])) ? $_POST['workTypeDescription'] : null;
   $workTypeCode = (isset($_POST['workTypeCode']) && !empty($_POST['workTypeCode'])) ? Utility::clean_string($_POST['workTypeCode']) : null;
   $workCategoryID = (isset($_POST['workCategoryID']) && !empty($_POST['workCategoryID'])) ? Utility::clean_string($_POST['workCategoryID']) : null;
   $Suspended = (isset($_POST['Suspended']) && !empty($_POST['Suspended'])) ? Utility::clean_string($_POST['Suspended']) : 'N';

   if(!$workTypeID){
      $workTypeName ? $details['workTypeName'] = $workTypeName : $errors[] = "Please submit valid work type name";
      $workTypeDescription ? $details['workTypeDescription'] = $workTypeDescription : $errors[] = "Please submit valid work type description";
      $workCategoryID ? $details['workCategoryID'] = $workCategoryID : $errors[] = "Please submit valid work category ID";
      $workTypeCode ? $details['workTypeCode'] = $workTypeCode : $errors[] = "Please submit valid work type code";

      if(!$errors){
      
         if($details){
            $details['LastUpdate'] = $config['currentDateTimeFormated'];
            if(!$DBConn->insert_data('tija_work_types', $details)){
               $errors[] = "Unable to add work type details";
            } else {
               $workTypeID = $DBConn->lastInsertId();
            }
         }
      }

   } else {
      $workTypeDetails = Work::work_types(array('workTypeID'=>$workTypeID), true, $DBConn);
      var_dump($workTypeDetails);
      $workTypeName && $workTypeDetails->workTypeName != $workTypeName ? $changes['workTypeName'] = $workTypeName : null;
      $workTypeDescription && $workTypeDetails->workTypeDescription != $workTypeDescription ? $changes['workTypeDescription'] = $workTypeDescription : null;
      $workCategoryID && $workTypeDetails->workCategoryID != $workCategoryID ? $changes['workCategoryID'] = $workCategoryID : null;
      $workTypeCode && $workTypeDetails->workTypeCode != $workTypeCode ? $changes['workTypeCode'] = $workTypeCode : null;
      //  var_dump($changes);
      //  die();
      if(!$errors){
      
         if($changes){

            var_dump($changes);
            $changes['LastUpdate'] = $config['currentDateTimeFormated'];
            $changes['LastUpdateByID'] = isset($_SESSION['userID']) ? $_SESSION['userID'] : $userDetails->ID;   
            //  var_dump($changes);
            //  die();
            if(!$DBConn->update_table('tija_work_types', array_merge($changes, array('LastUpdate'=>$config['currentDateTimeFormated'])), array('workTypeID'=>$workTypeID))){
               $errors[] = "Unable to update work type details";
            } else {
               $success = "Work type details updated successfully";
            }
         }
      }
   }

} else {
	$errors[] = 'You need to log in as a valid user to add new sales case.';
}
var_dump($_SESSION);
var_dump($errors);
$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performance=home');

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