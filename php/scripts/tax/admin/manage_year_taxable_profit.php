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
   $taxableProfit = (isset($_POST['taxableProfit']) && !empty($_POST['taxableProfit'])) ? Utility::clean_string($_POST['taxableProfit']):"";
   $fiscalYear = (isset($_POST['year']) && !empty($_POST['year'])) ? Utility::clean_string($_POST['year']):"";

   $taxableProfitID = (isset($_POST['taxableProfitID']) && !empty($_POST['taxableProfitID'])) ? Utility::clean_string($_POST['taxableProfitID']):"";

   if($taxableProfitID) {

   } else {
      $entityID ? $details['entityID'] = $entityID : $errors[] = "Please submit valid instance ID";
      $taxableProfit ? $details['taxableProfit'] = $taxableProfit : $errors[] = "Please submit valid taxable profit";
      $fiscalYear ? $details['fiscalYear'] = $fiscalYear : $errors[] = "Please submit valid fiscal year";

      if(count($errors) ===0) {
         if(!$DBConn->insert_data('tija_taxable_profit', $details)) {
            $errors[] = "<span class't600'> ERROR!</span> Failed to insert taxable profit to the database";
         } else {
            $success = "Taxable profit inserted successfully";
         }
      }
   }

   
   var_dump($errors);


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