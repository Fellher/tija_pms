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
   $productBillingPeriodLevelName = (isset($_POST['productBillingPeriodLevelName']) && !empty($_POST['productBillingPeriodLevelName'])) ? Utility::clean_string($_POST['productBillingPeriodLevelName']) : "";
   $productBillingPeriodLevelDescription = (isset($_POST['productBillingPeriodLevelDescription']) && !empty($_POST['productBillingPeriodLevelDescription'])) ? Utility::clean_string($_POST['productBillingPeriodLevelDescription']) : "";
   $billingPeriodLevelID = (isset($_POST['billingPeriodLevelID']) && !empty($_POST['billingPeriodLevelID'])) ? Utility::clean_string($_POST['billingPeriodLevelID']) : "";
   $Suspended = (isset($_POST['Suspended']) && !empty($_POST['Suspended'])) ? Utility::clean_string($_POST['Suspended']) : "";

   if(!$billingPeriodLevelID){
      $productBillingPeriodLevelName ? $details['productBillingPeriodLevelName'] = Utility::clean_string($productBillingPeriodLevelName) : $errors[] = "Please submit valid billing period level name";
      $productBillingPeriodLevelDescription ? $details['productBillingPeriodLevelDescription'] = Utility::clean_string($productBillingPeriodLevelDescription) : $errors[] = "Please submit valid billing period level description";
      if(!$errors){
         if($details){
            var_dump($details);
            if(!$DBConn->insert_data("tija_product_billing_period_levels", $details)){
               $errors[]= "ERROR inserting billing period level details in the database";
            } else {
               $success = "Billing period level details added successfully";
            }
         }
      }
   } else {
      $billingPeriodLevelDetails = Projects::billing_period_level(array("billingPeriodLevelID"=> $billingPeriodLevelID), true, $DBConn);
      $productBillingPeriodLevelName && ($productBillingPeriodLevelName !== $billingPeriodLevelDetails->productBillingPeriodLevelName) ? $changes['productBillingPeriodLevelName'] = $productBillingPeriodLevelName : "";
      $productBillingPeriodLevelDescription && ($productBillingPeriodLevelDescription !== $billingPeriodLevelDetails->productBillingPeriodLevelDescription) ? $changes['productBillingPeriodLevelDescription'] = $productBillingPeriodLevelDescription : "";
      $Suspended && ($Suspended !== $billingPeriodLevelDetails->Suspended) ? $changes['Suspended'] = $Suspended : "";
      if (count($errors) === 0) {
         if ($changes) {
            $changes['LastUpdate'] = $config['currentDateTimeFormated'];
            $changes['LastUpdateByID'] = $userDetails->ID;
            if (!$DBConn->update_table("tija_product_billing_period_levels", $changes, array("billingPeriodLevelID"=>$billingPeriodLevelID))) {
               $errors[]= "ERROR updating billing period level details in the database";
            } else {
               $success = "Billing period level details updated successfully";
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