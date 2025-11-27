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
   $productRateID = (isset($_POST['productRateID']) && !empty($_POST['productRateID'])) ? Utility::clean_string($_POST['productRateID']) : "";
   $productRateName = (isset($_POST['productRateName']) && !empty($_POST['productRateName'])) ? Utility::clean_string($_POST['productRateName']) : "";
   $productRateTypeID = (isset($_POST['productRateTypeID']) && !empty($_POST['productRateTypeID'])) ? Utility::clean_string($_POST['productRateTypeID']) : "";
   $priceRate =   (isset($_POST['priceRate']) && !empty($_POST['priceRate'])) ? Utility::clean_string($_POST['priceRate']) : ""; 
   $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ? Utility::clean_string($_POST['entityID']) : "";
   $projectID = (isset($_POST['projectID']) && !empty($_POST['projectID'])) ? Utility::clean_string($_POST['projectID']) : "";
   $Suspended = (isset($_POST['Suspended']) && !empty($_POST['Suspended'])) ? Utility::clean_string($_POST['Suspended']) : "";

   if(!$productRateID){
      $productRateName ? $details['productRateName'] = Utility::clean_string($productRateName) : $errors[] = "Please submit valid product rate name";
      $productRateTypeID ? $details['productRateTypeID'] = Utility::clean_string($productRateTypeID) : $errors[] = "Please submit valid product rate type";
      $priceRate ? $details['priceRate'] = Utility::clean_string($priceRate) : $errors[] = "Please submit valid product rate price";
      $entityID ? $details['entityID'] = Utility::clean_string($entityID) : $errors[] = "Please submit valid entity ID";
      $projectID ? $details['projectID'] = Utility::clean_string($projectID) : $errors[] = "Please submit valid project ID";

      if(!$errors){
         if($details){
            var_dump($details);
            if(!$DBConn->insert_data("tija_product_rates", $details)){
               $errors[]= "ERROR inserting product rate details in the database";
            } else {
               $success = "Product rate details added successfully";
            }
         }
      }
   } else {
      $productTypeDetails = Projects::product_rates(array("productRateID"=> $productRateID), true, $DBConn);
      $productRateName && ($productRateName !== $productTypeDetails->productRateName) ? $changes['productRateName'] = $productRateName : "";
      $productRateTypeID && ($productRateTypeID !== $productTypeDetails->productRateTypeID) ? $changes['productRateTypeID'] = $productRateTypeID : "";
      $priceRate && ($priceRate !== $productTypeDetails->priceRate) ? $changes['priceRate'] = $priceRate : "";
      $entityID && ($entityID !== $productTypeDetails->entityID) ? $changes['entityID'] = $entityID : "";
      $projectID && ($projectID !== $productTypeDetails->projectID) ? $changes['projectID'] = $projectID : "";
      $Suspended && ($Suspended !== $productTypeDetails->Suspended) ? $changes['Suspended'] = $Suspended : "";
      if (count($errors) === 0) {
         if ($changes) {
            $changes['LastUpdate'] = $config['currentDateTimeFormated'];
            $changes['LastUpdateByID'] = $userDetails->ID;
            if (!$DBConn->update_table("tija_product_rates", $changes, array("productRateID"=>$productRateID))) {
               $errors[]= "ERROR updating product rate details in the database";
            } else {
               $success = "Product rate details updated successfully";
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