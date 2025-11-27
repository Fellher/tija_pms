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

$posts= $_POST;
var_dump($posts);
$billingRateTypeID = (isset($_POST['billingRateTypeID']) && !empty($_POST['billingRateTypeID'])) ? Utility::clean_string($_POST['billingRateTypeID']) : null;
$billingRateTypeName = (isset($_POST['billingRateTypeName']) && !empty($_POST['billingRateTypeName'])) ? Utility::clean_string($_POST['billingRateTypeName']) : null;
$billingRateTypeDescription = (isset($_POST['billingRateTypeDescription']) && !empty($_POST['billingRateTypeDescription'])) ? $_POST['billingRateTypeDescription'] : null;
$Suspended = (isset($_POST['Suspended']) && !empty($_POST['Suspended'])) ? Utility::clean_string($_POST['Suspended']) : 'N';


if(!$billingRateTypeID) {
   $billingRateTypeName ? $details['billingRateTypeName'] = $billingRateTypeName : $errors[] = "Please submit valid billing rate type name";
   $billingRateTypeDescription ? $details['billingRateTypeDescription'] = $billingRateTypeDescription : $errors[] = "Please submit valid billing rate type description";

   if(!$errors){

      if($details){
         $details['LastUpdate'] = $config['currentDateTimeFormated'];
         if(!$DBConn->insert_data('tija_billing_rate_types', $details)){
            $errors[] = "Unable to add billing rate type details";
         } else {
            $billingRateTypeID = $DBConn->lastInsertId();
         }
      }
   }

} else {
   $billingRateTypeDetails = Projects::billing_rate_type(array('billingRateTypeID'=>$billingRateTypeID), true, $DBConn);
   var_dump($billingRateTypeDetails);
   $billingRateTypeName && $billingRateTypeDetails->billingRateTypeName != $billingRateTypeName ? $changes['billingRateTypeName'] = $billingRateTypeName : null;
   $billingRateTypeDescription && $billingRateTypeDetails->billingRateTypeDescription != $billingRateTypeDescription ? $changes['billingRateTypeDescription'] = $billingRateTypeDescription : null;
   $Suspended && $billingRateTypeDetails->Suspended != $Suspended ? $changes['Suspended'] = $Suspended : null;
   if(!$errors){
      if($changes){
         var_dump($changes);
         $changes['LastUpdate'] = $config['currentDateTimeFormated'];
         if(!$DBConn->update_table('tija_billing_rate_types', $changes, array('billingRateTypeID'=>$billingRateTypeID))){
            $errors[] = "Unable to update billing rate type details";
         }
      }
   }
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
?>