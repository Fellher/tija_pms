<?php
session_start();
$base = "../../../";
set_include_path($base);

include 'php/includes.php';

var_dump($_POST);

$errors = array();
$DBConn->begin();
$details=array();

if ($isValidUser) {
   $productRateTypeID = (isset($_POST['productRateTypeID']) && !empty($_POST['productRateTypeID'])) ? Utility::clean_string($_POST['productRateTypeID']) : "";
   $productRateTypeName = (isset($_POST['productRateTypeName']) && !empty($_POST['productRateTypeName'])) ? Utility::clean_string($_POST['productRateTypeName']) : "";
   $productRateTypeDescription = (isset($_POST['productRateTypeDescription']) && !empty($_POST['productRateTypeDescription'])) ? Utility::clean_string($_POST['productRateTypeDescription']) : "";
   $suspended = (isset($_POST['suspended']) && !empty($_POST['suspended'])) ? Utility::clean_string($_POST['suspended']) : "N";

   if(!$productRateTypeID) {
      $productRateTypeName ? $details['productRateTypeName'] = Utility::clean_string($productRateTypeName) : $errors[] = "Please submit valid product rate type name";
      $productRateTypeDescription ? $details['productRateTypeDescription'] = Utility::clean_string($productRateTypeDescription) : $errors[] = "Please submit valid product rate type description";
      $suspended ? $details['suspended'] = Utility::clean_string($suspended) : $details['suspended'] = "N";
      if (count($errors) === 0) {
         if ($details) {
            $details['LastUpdate'] = $config['currentDateTimeFormated'];
            $details['LastUpdateByID']= $userDetails->ID;
            if (!$DBConn->insert_data("tija_product_rate_types", $details)) {
               $errors[]= "ERROR inserting product rate type details in the database";
            } else {
               $success = "Product rate type details added successfully.";
            }
         }
      }
   } else {
      $productRateTypeDetails = Projects::billing_rate_type(array('productRateTypeID' => $productRateTypeID), true, $DBConn);
      if ($productRateTypeDetails) {
         $productRateTypeName && ($productRateTypeName !== $productRateTypeDetails->productRateTypeName) ? $changes['productRateTypeName'] = $productRateTypeName : "";
         $productRateTypeDescription && ($productRateTypeDescription !== $productRateTypeDetails->productRateTypeDescription) ? $changes['productRateTypeDescription'] = $productRateTypeDescription : "";
         $suspended && ($suspended !== $productRateTypeDetails->suspended) ? $changes['suspended'] = $suspended : "";
         $changes['LastUpdate'] = $config['currentDateTimeFormated'];
         if (!$DBConn->update_table("tija_product_rate_types", $changes, array("productRateTypeID"=>$productRateTypeID))) {
            $errors[]= "ERROR updating product rate type details in the database";
         }
      } else {
         $errors[] = "Product rate type not found.";
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