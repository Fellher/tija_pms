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
   $fiscalYear = (isset($_POST['fiscalYear']) && !empty($_POST['fiscalYear'])) ? Utility::clean_string($_POST['fiscalYear']):"";
   $withholdingTax = (isset($_POST['withholdingTax']) && !empty($_POST['withholdingTax'])) ? Utility::clean_string($_POST['withholdingTax']):"";

   $withholdingTaxID = (isset($_POST['withholdingTaxID']) && !empty($_POST['withholdingTaxID'])) ? Utility::clean_string($_POST['withholdingTaxID']):"";

   if($withholdingTaxID) {

   }else {
      $entityID ? $details['entityID'] = $entityID : $errors[] = "Please submit valid instance ID";
      $fiscalYear ? $details['fiscalYear'] = $fiscalYear : $errors[] = "Please submit valid fiscal year";
      $withholdingTax ? $details['withholdingTax'] = $withholdingTax : $errors[] = "Please submit valid withholding tax";

      if(count($errors) ===0) {
         if(!$DBConn->insert_data('tija_withholding_tax', $details)) {
            $errors[] = "<span class't600'> ERROR!</span> Failed to insert withholding tax to the database";
         } else {
            $success = "Withholding tax inserted successfully";
         }
      }
   }

} else { 
   $errors[] = 'You need to log in as a valid administrator to do that.';
}
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