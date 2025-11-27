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

   $businessUnitID = (isset($_POST['businessUnitID']) && !empty($_POST['businessUnitID'])) ? Utility::clean_string($_POST['businessUnitID']) : "";
   $businessUnitName = (isset($_POST['businessUnitName']) && !empty($_POST['businessUnitName'])) ? Utility::clean_string($_POST['businessUnitName']) : "";
   $businessUnitDescription = (isset($_POST['businessUnitDescription']) && !empty($_POST['businessUnitDescription'])) ? Utility::clean_string($_POST['businessUnitDescription']) : "";
   $unitTypeID = (isset($_POST['unitTypeID']) && !empty($_POST['unitTypeID'])) ? Utility::clean_string($_POST['unitTypeID']) : "";
   $categoryID = (isset($_POST['categoryID']) && !empty($_POST['categoryID'])) ? Utility::clean_string($_POST['categoryID']) : "";
   $orgDataID = (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ? Utility::clean_string($_POST['orgDataID']) : "";
   $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ? Utility::clean_string($_POST['entityID']) : "";

   if(!$businessUnitID) {
      $details= [];
      $businessUnitName ? $details['businessUnitName'] = Utility::clean_string($businessUnitName) : $errors[] = "Please submit valid business unit Name";
      $businessUnitDescription ? $details['businessUnitDescription'] = Utility::clean_string($businessUnitDescription) : $errors[] = "Please submit valid business unit Description";
      $unitTypeID ? $details['unitTypeID'] = Utility::clean_string($unitTypeID) : "";
      if ($categoryID) $details['categoryID'] = Utility::clean_string($categoryID);
      $orgDataID ? $details['orgDataID'] = Utility::clean_string($orgDataID) : $errors[] = "Please submit valid organisation ID";
      $entityID ? $details['entityID'] = Utility::clean_string($entityID) : $errors[] = "Please submit valid entity ID";
      if (count($errors) === 0) {
         if ($details) {
            if (!$DBConn->insert_data("tija_business_units", $details)) {
               $errors[]= "ERROR inserting business unit details in the database";
            } else {
               $success = "Business unit details added successfully";
            }
         }
      }
   } else {
      $businessUnitDetails = Data::business_units_full(array("businessUnitID"=> $businessUnitID), true, $DBConn);

      $changes=[];
      $businessUnitName && ($businessUnitName !== $businessUnitDetails->businessUnitName) ? $changes['businessUnitName'] = $businessUnitName : "";
      $businessUnitDescription && ($businessUnitDescription !== $businessUnitDetails->businessUnitDescription) ? $changes['businessUnitDescription'] = $businessUnitDescription : "";
      $unitTypeID && ($unitTypeID !== $businessUnitDetails->unitTypeID) ? $changes['unitTypeID'] = $unitTypeID : "";
      $categoryID && ($categoryID !== ($businessUnitDetails->categoryID ?? '')) ? $changes['categoryID'] = $categoryID : "";
      $orgDataID && ($orgDataID !== $businessUnitDetails->orgDataID) ? $changes['orgDataID'] = $orgDataID : "";


      if(count($errors) === 0) {
         if ($changes) {
            $changes['LastUpdate'] = $config['currentDateTimeFormated'];
            $changes['LastUpdateByID'] = $userDetails->ID;
            if (!$DBConn->update_table("tija_business_units", $changes, array("businessUnitID"=>$businessUnitID))) {
               $errors[]= "ERROR updating business unit details in the database";
            } else {
               $success = "Business unit details updated successfully";
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