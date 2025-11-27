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

   $travelRateTypeID = (isset($_POST['travelRateTypeID']) && !empty($_POST['travelRateTypeID'])) ? Utility::clean_string($_POST['travelRateTypeID']) : "";
   $travelRateTypeName = (isset($_POST['travelRateTypeName']) && !empty($_POST['travelRateTypeName'])) ? Utility::clean_string($_POST['travelRateTypeName']) : "";
   $travelRateTypeDescription = (isset($_POST['travelRateTypeDescription']) && !empty($_POST['travelRateTypeDescription'])) ? Utility::clean_string($_POST['travelRateTypeDescription']) : "";
   $suspended = (isset($_POST['suspended']) && !empty($_POST['suspended'])) ? Utility::clean_string($_POST['suspended']) : "N";

   if(!$travelRateTypeID) {
      $travelRateTypeName ? $details['travelRateTypeName'] = Utility::clean_string($travelRateTypeName) : $errors[] = "Please submit valid travel rate type name";
      $travelRateTypeDescription ? $details['travelRateTypeDescription'] = Utility::clean_string($travelRateTypeDescription) : $errors[] = "Please submit valid travel rate type description";
      $suspended ? $details['suspended'] = Utility::clean_string($suspended) : $details['suspended'] = "N";
      if (count($errors) === 0) {
         if ($details) {
            $details['LastUpdate'] = $config['currentDateTimeFormated'];
            $details['LastUpdateByID']= $userDetails->ID;
            if (!$DBConn->insert_data("tija_travel_rate_types", $details)) {
               $errors[]= "ERROR inserting travel rate type details in the database";
            } else {
               $success = "Travel rate type details added successfully.";
            }
         }
      }


   } else {
      $travelRateTypeDetails = Projects::billing_rate_type(array('travelRateTypeID' => $travelRateTypeID), true, $DBConn);
      if ($travelRateTypeDetails) {
         $travelRateTypeName && ($travelRateTypeName !== $travelRateTypeDetails->travelRateTypeName) ? $changes['travelRateTypeName'] = $travelRateTypeName : "";
         $travelRateTypeDescription && ($travelRateTypeDescription !== $travelRateTypeDetails->travelRateTypeDescription) ? $changes['travelRateTypeDescription'] = $travelRateTypeDescription : "";
         $suspended && ($suspended !== $travelRateTypeDetails->suspended) ? $changes['suspended'] = $suspended : "";
         $changes['LastUpdate'] = $config['currentDateTimeFormated'];
         $changes['suspended'] = $suspended;
         if (!$DBConn->update_table("tija_travel_rate_types", $changes, array("travelRateTypeID"=>$travelRateTypeID))) {
            $errors[]= "ERROR updating travel rate type details in the database";
         }
      } else {
         $errors[] = "Travel rate type not found.";
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