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
if ($isValidAdmin || $isAdmin) {
	var_dump($_POST);
   $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ? Utility::clean_string($_POST['entityID']):"";
   $advanceTax = (isset($_POST['advanceTax']) && !empty($_POST['advanceTax'])) ? Utility::clean_string($_POST['advanceTax']):"";
   $fiscalYear = (isset($_POST['fiscalYear']) && !empty($_POST['fiscalYear'])) ? Utility::clean_string($_POST['fiscalYear']):"";

   $advanceTaxID = (isset($_POST['advanceTaxID']) && !empty($_POST['advanceTaxID'])) ? Utility::clean_string($_POST['advanceTaxID']):"";
   if($advanceTaxID) {

   } else {
      $entityID ? $details['entityID'] = $entityID : $errors[] = "Please submit valid instance ID";
      $advanceTax ? $details['advanceTax'] = $advanceTax : $errors[] = "Please submit valid advance tax";
      $fiscalYear ? $details['fiscalYear'] = $fiscalYear : $errors[] = "Please submit valid fiscal year";

      if(count($errors) ===0) {
         if(!$DBConn->insert_data('tija_advance_tax', $details)) {
            $errors[] = "<span class't600'> ERROR!</span> Failed to insert advance tax to the database";
         } else {
            $success = "Advance tax inserted successfully";
         }
      }
   }

} else { 
   $errors[] = 'You need to log in as a valid administrator to do that.';
}

var_dump($errors);
$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
  var_dump($returnURL);
if (count($errors) == 0) {
  $DBConn->commit();
  $messages = array(array('Text'=>"{$success}", 'Type'=>'success'));
} else {
   $DBConn->rollback();
   $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
}
$_SESSION['FlashMessages'] = serialize($messages);
header("location:{$base}html/{$returnURL}");?>