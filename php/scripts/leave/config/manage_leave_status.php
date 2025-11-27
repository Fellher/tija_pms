<?php
session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
$success = "";
if ($isValidUser) {
	var_dump($_POST);

   $leaveStatusID = (isset($_POST['leaveStatusID']) && !empty($_POST['leaveStatusID'])) ? Utility::clean_string($_POST['leaveStatusID']) : "";
   $leaveStatusName = (isset($_POST['leaveStatusName']) && !empty($_POST['leaveStatusName'])) ? Utility::clean_string($_POST['leaveStatusName']) : "";
   $leaveStatusDescription = (isset($_POST['leaveStatusDescription']) && !empty($_POST['leaveStatusDescription'])) ? Utility::sanitize_rich_text_input($_POST['leaveStatusDescription']) : "";

   if(!$leaveStatusID) {
      $leaveStatusName ? $details['leaveStatusName'] = Utility::clean_string($leaveStatusName) : $errors[] = "Please submit valid leave status name";
      $leaveStatusDescription ? $details['leaveStatusDescription'] = Utility::clean_string($leaveStatusDescription) : $errors[] = "Please submit valid leave status description";
      if (count($errors) === 0) {
         if ($details) {
            if (!$DBConn->insert_data("tija_leave_status", $details)) {
               $errors[]= "ERROR adding new leave status to the database";
            } else {
               $success = "Leave Status added successfully";
            }
         }
      }

   } else {
      $leaveStatusDetails = Leave::leave_Status(array("leaveStatusID"=> $leaveStatusID), true, $DBConn);
      $leaveStatusName && ($leaveStatusName !== $leaveStatusDetails->leaveStatusName) ? $changes['leaveStatusName'] = $leaveStatusName : "";
      $leaveStatusDescription && ($leaveStatusDescription !== $leaveStatusDetails->leaveStatusDescription) ? $changes['leaveStatusDescription'] = $leaveStatusDescription : "";
      if (count($errors) === 0) {
         if ($changes) {
            $changes['LastUpdate'] = $config['currentDateTimeFormated'];
            if (!$DBConn->update_table("tija_leave_status", $changes, array("leaveStatusID"=>$leaveStatusID))) {
               $errors[]= "ERROR updating leave status details in the database";
            } else {
               $success = "Leave Status updated successfully";
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