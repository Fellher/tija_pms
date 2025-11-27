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
	$productTypeID = (isset($_POST['productTypeID']) && !empty($_POST['productTypeID'])) ? Utility::clean_string($_POST['productTypeID']) : "";
	$productTypeName = (isset($_POST['productTypeName']) && !empty($_POST['productTypeName'])) ? Utility::clean_string($_POST['productTypeName']) : "";
	$productTypeDescription = (isset($_POST['productTypeDescription']) && !empty($_POST['productTypeDescription'])) ? Utility::clean_string($_POST['productTypeDescription']) : "";
	$Suspended = (isset($_POST['Suspended']) && !empty($_POST['Suspended'])) ? Utility::clean_string($_POST['Suspended']) : "N";

   if(!$productTypeID) {
		$productTypeName ? $details['productTypeName'] = Utility::clean_string($productTypeName) : $errors[] = "Please submit valid product type name";
		$productTypeDescription ? $details['productTypeDescription'] = Utility::clean_string($productTypeDescription) : $errors[] = "Please submit valid product type description";
		if(!$errors) {
			if($details) {
				var_dump($details);
				if(!$DBConn->insert_data("tija_product_types", $details)) {
					$errors[]= "ERROR inserting product type details in the database";
				} else {
					$success = "Product type details added successfully";
				}
			}
		}
	} else {
		$productTypeDetails = Projects::product_types(array("productTypeID"=> $productTypeID), true, $DBConn);
		var_dump($productTypeDetails);
		$productTypeName && ($productTypeName !== $productTypeDetails->productTypeName) ? $changes['productTypeName'] = $productTypeName : "";
		$productTypeDescription && ($productTypeDescription !== $productTypeDetails->productTypeDescription) ? $changes['productTypeDescription'] = $productTypeDescription : "";
		$Suspended && ($Suspended !== $productTypeDetails->Suspended) ? $changes['Suspended'] = $Suspended : "";
		if (count($errors) === 0) {
			if ($changes) {
				var_dump($changes);
				$changes['LastUpdate'] = $config['currentDateTimeFormated'];
				$changes['LastUpdateByID'] = $userDetails->ID;
				if (!$DBConn->update_table("tija_product_types", $changes, array("productTypeID"=>$productTypeID))) {
					$errors[]= "ERROR updating product type details in the database";
				} else {
					$success = "Product type details updated successfully";
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