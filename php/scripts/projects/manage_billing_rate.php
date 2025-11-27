<?php
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes=array();

if ( $isValidUser) {
   var_dump($_POST);
   
   $entityID =( isset($_POST['entityID']) && !empty($_POST['entityID']) )? Utility::clean_string($_POST['entityID']) : '';
   $billingRateID =( isset($_POST['billingRateID']) && !empty($_POST['billingRateID']) ? Utility::clean_string($_POST['billingRateID']) : '');
   $projectID =( isset($_POST['projectID']) && !empty($_POST['projectID']) ? Utility::clean_string($_POST['projectID']) : '');
   $rateName =( isset($_POST['rateName']) && !empty($_POST['rateName']) ? Utility::clean_string($_POST['rateName']) : '');
   $billingRateTypeID =( isset($_POST['billingRateTypeID']) && !empty($_POST['billingRateTypeID']) ? Utility::clean_string($_POST['billingRateTypeID']) : '');
   $workTypeID =( isset($_POST['workTypeID']) && !empty($_POST['workTypeID']) ? Utility::clean_string($_POST['workTypeID']) : '');
   $hourlyRate =( isset($_POST['hourlyRate']) && !empty($_POST['hourlyRate']) ? Utility::clean_string($_POST['hourlyRate']) : '');
   $doneByID =( isset($_POST['doneByID']) && !empty($_POST['doneByID']) ? Utility::clean_string($_POST['doneByID']) : '');
   $suspended =( isset($_POST['suspended']) && !empty($_POST['suspended']) ? Utility::clean_string($_POST['suspended']) : 'N');

   if(!$billingRateID){
      $entityID  ? $details['entityID'] = $entityID : $errors[] = "Entity ID is required";
      $projectID ? $details['projectID'] = $projectID : $errors[] = "Project ID is required";
      $rateName  ? $details['billingRateName'] = $rateName : $errors[] = "Rate Name is required";
      $billingRateTypeID ? $details['billingRateTypeID'] = $billingRateTypeID : $errors[] = "Billing Rate Type ID is required";
      $workTypeID ? $details['workTypeID'] = $workTypeID : $errors[] = "Work Type ID is required";
      $hourlyRate ? $details['hourlyRate'] = $hourlyRate : $errors[] = "Hourly Rate is required";
      $doneByID ? $details['doneByID'] = $doneByID : $errors[] = "Done By ID is required";

      if(!$errors){
         if($details){
            $details['LastUpdateByID']= $userDetails->ID;
            $details['LastUpdate']= $config['currentDateTimeFormated'];
            if(!$DBConn->insert_data('tija_billing_rates', $details)){
               $errors[] = "Failed to insert billing rate details";
            } else {
               $billingRateID = $DBConn->lastInsertId();
            }            
         }
      }
      
      var_dump($details);
   } else {
      $billingRateDetails = Projects::billing_rates(array('billingRateID' => $billingRateID), false, $DBConn);
      var_dump($billingRateDetails);
   }



} else {
	Alert::warning("You need to be logged in as a valid ");
}
$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');

var_dump($errors);
var_dump($returnURL);

 if (count($errors) == 0) {
	 $DBConn->commit();
	 $messages = array(array('Text'=>'Your time log was successfully updated', 'Type'=>'success'));
 } else {
	 $DBConn->rollback();
	 $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
 }
 $_SESSION['FlashMessages'] = serialize($messages);
 header("location:{$base}html/{$returnURL}");