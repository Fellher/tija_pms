
<?php
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
$success="";
if ($isValidUser) {
	var_dump($_POST);

   $proposalStatusID = (isset($_POST['proposalStatusID']) && !empty($_POST['proposalStatusID'])) ?  Utility::clean_string($_POST['proposalStatusID']): "";
   $proposalStatusName = (isset($_POST['proposalStatusName']) && !empty($_POST['proposalStatusName'])) ?  Utility::clean_string($_POST['proposalStatusName']): "";
   $proposalStatusDescription = (isset($_POST['proposalStatusDescription']) && !empty($_POST['proposalStatusDescription'])) ?  Utility::clean_string($_POST['proposalStatusDescription']): "";
   $orgDataID = (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ?  Utility::clean_string($_POST['orgDataID']): "";
   $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ?  Utility::clean_string($_POST['entityID']): "";
   $proposalStatusCategoryID = (isset($_POST['proposalStatusCategoryID']) && !empty($_POST['proposalStatusCategoryID'])) ?  Utility::clean_string($_POST['proposalStatusCategoryID']): "";

   if(!$proposalStatusID){
      $proposalStatusName ? $details['proposalStatusName'] = $proposalStatusName : $errors[] = 'Proposal Status Name is required';
      $proposalStatusDescription ? $details['proposalStatusDescription'] = $proposalStatusDescription : $errors[] = 'Proposal Status Description is required';
      $proposalStatusCategoryID ? $details['proposalStatusCategoryID'] = $proposalStatusCategoryID : $errors[] = 'Proposal Status Category is required';
      $orgDataID ? $details['orgDataID'] = $orgDataID : $errors[] = 'Organization ID is required';
      $entityID ? $details['entityID'] = $entityID : "";
      if(!$errors){
         if($details) {
            $details['LastUpdateByID']=$userDetails->ID;
            $details['LastUpdate']=$config['currentDateTimeFormated'];
            if(!$DBConn->insert_data('tija_proposal_statuses', $details)) {
               $errors[] = 'Error adding new Proposal Status';
            } else {
               $proposalStatusID = $DBConn->lastInsertID();
            }
         }
      }
   } else {
      $proposalStatusDetails = Sales::proposal_statuses(array('proposalStatusID'=>$proposalStatusID), true, $DBConn);
      var_dump($proposalStatusDetails);
      if($proposalStatusDetails){
         $proposalStatusName && $proposalStatusName != $proposalStatusDetails->proposalStatusName ? $changes['proposalStatusName'] = $proposalStatusName : "";
         $proposalStatusDescription && $proposalStatusDescription != $proposalStatusDetails->proposalStatusDescription ? $changes['proposalStatusDescription'] = $proposalStatusDescription : "";
         $orgDataID && $orgDataID != $proposalStatusDetails->orgDataID ? $changes['orgDataID'] = $orgDataID : "";
         $entityID && $entityID != $proposalStatusDetails->entityID ? $changes['entityID'] = $entityID : "";
         $proposalStatusCategoryID && $proposalStatusCategoryID != $proposalStatusDetails->proposalStatusCategoryID ? $changes['proposalStatusCategoryID'] = $proposalStatusCategoryID : "";
         if($changes) {
            $changes['LastUpdateByID']=$userDetails->ID;
            $changes['LastUpdate']=$config['currentDateTimeFormated'];
            if(!$DBConn->update_table('tija_proposal_statuses', $changes, array('proposalStatusID'=>$proposalStatusID))) {
               $errors[] = 'Error updating Proposal Status';
            }
         }
      } else {
         $errors[] = 'Proposal Status not found';
      }
   }
   var_dump($errors);
   $returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
	var_dump($returnURL);
} else {
	$errors[] = 'You need to log in as a valid administrator to do that.';
}

if (count($errors) == 0) {
	$DBConn->commit();
	$messages = array(array('Text'=>"{$success}", 'Type'=>'success'));
} else {
 	$DBConn->rollback();
 	$messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
}
 $_SESSION['FlashMessages'] = serialize($messages);
 header("location:{$base}html/{$returnURL}");