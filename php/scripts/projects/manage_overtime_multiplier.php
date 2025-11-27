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

   $overtimeMultiplierName = (isset($_POST['overtimeMultiplierName']) && !empty($_POST['overtimeMultiplierName'])) ? Utility::clean_string($_POST['overtimeMultiplierName']) : "";
   $overtimeMultiplierID = (isset($_POST['overtimeMultiplierID']) && !empty($_POST['overtimeMultiplierID'])) ? Utility::clean_string($_POST['overtimeMultiplierID']) : "";
   $multiplierRate = (isset($_POST['multiplierRate']) && !empty($_POST['multiplierRate'])) ? Utility::clean_string($_POST['multiplierRate']) : "";
   $projectID = (isset($_POST['projectID']) && !empty($_POST['projectID'])) ? Utility::clean_string($_POST['projectID']) : "";
   $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ? Utility::clean_string($_POST['entityID']) : "";
   $workTypeID = (isset($_POST['workTypeID']) && !empty($_POST['workTypeID']) &&  is_array($_POST['workTypeID']) ) ? $_POST['workTypeID'] : "";

   if(!$overtimeMultiplierID) {
      $overtimeMultiplierName ? $details['overtimeMultiplierName'] = $overtimeMultiplierName : $errors[] = "Overtime Multiplier is required";
      $multiplierRate ? $details['multiplierRate'] = $multiplierRate : $errors[] = "Multiplier Rate is required";
      $projectID ? $details['projectID'] = $projectID : $errors[] = "Project ID is required";
      $entityID ? $details['entityID'] = $entityID : $errors[] = "Entity ID is required";
      $workTypeID ? $details['workTypeID'] = implode(',', $workTypeID) : $errors[] = "Work Type ID is required";
      var_dump($details);

      if(!$errors) {
         if($details) {
            $details['LastUpdateByID']= $userDetails->ID;
            $details['LastUpdate']= $config['currentDateTimeFormated'];
            if(!$DBConn->insert_data('tija_overtime_multiplier', $details)){
               $errors[] = "Failed to insert overtime multiplier details";
            } else {
               $overtimeMultiplierID = $DBConn->lastInsertId();
            }            
         }
      }

   } else {
      $overtimeMultiplierDetails = Projects::overtime_multiplier(array('overtimeMultiplierID' => $overtimeMultiplierID), true , $DBConn);
      var_dump($overtimeMultiplierDetails);

      $overtimeMultiplierName && ($overtimeMultiplierName !== $overtimeMultiplierDetails->overtimeMultiplierName) ? $changes['overtimeMultiplierName'] = $overtimeMultiplierName : "";
      $multiplierRate && ($multiplierRate !== $overtimeMultiplierDetails->multiplierRate) ? $changes['multiplierRate'] = $multiplierRate : "";
      $projectID && ($projectID !== $overtimeMultiplierDetails->projectID) ? $changes['projectID'] = $projectID : "";
      $entityID && ($entityID !== $overtimeMultiplierDetails->entityID) ? $changes['entityID'] = $entityID : "";
      $workTypeIDStr = implode(',', $workTypeID) ;
      $workTypeID && ($workTypeIDStr !== $overtimeMultiplierDetails->workTypeID) ? $changes['workTypeID'] = implode(',', $workTypeID) : "";
      var_dump($changes);
      if (count($errors) === 0) {
         if ($changes) {
            $changes['LastUpdate'] = $config['currentDateTimeFormated'];
            $changes['LastUpdateByID'] = $userDetails->ID;
            if (!$DBConn->update_table("tija_overtime_multiplier", $changes, array("overtimeMultiplierID"=>$overtimeMultiplierID))) {
               $errors[]= "ERROR updating overtime multiplier details in the database";
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