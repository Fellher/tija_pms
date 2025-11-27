
<?php
session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
$success="";
if ($isValidUser) {
	var_dump($_POST);
   $proposalChecklistStatusID = (isset($_POST['proposalChecklistStatusID']) && !empty($_POST['proposalChecklistStatusID'])) ?  Utility::clean_string($_POST['proposalChecklistStatusID']): "";
   $proposalChecklistStatusName = (isset($_POST['proposalChecklistStatusName']) && !empty($_POST['proposalChecklistStatusName'])) ?  Utility::clean_string($_POST['proposalChecklistStatusName']): "";
   $proposalChecklistStatusDescription = (isset($_POST['proposalChecklistStatusDescription']) && !empty($_POST['proposalChecklistStatusDescription'])) ?  Utility::clean_string($_POST['proposalChecklistStatusDescription']): "";
   $proposalChecklistStatusType = (isset($_POST['proposalChecklistStatusType']) && !empty($_POST['proposalChecklistStatusType'])) ?  Utility::clean_string($_POST['proposalChecklistStatusType']): "";
   $orgDataID = (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ?  Utility::clean_string($_POST['orgDataID']): "";
   $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ?  Utility::clean_string($_POST['entityID']): "";

   if(!$proposalChecklistStatusID) {
      $proposalChecklistStatusName ? $details['proposalChecklistStatusName'] = $proposalChecklistStatusName : $errors[] = 'Proposal Checklist & Item Status Name is required';
      $proposalChecklistStatusDescription ? $details['proposalChecklistStatusDescription'] = $proposalChecklistStatusDescription : $errors[] = 'Proposal Checklist & Item Status Description is required';
      $proposalChecklistStatusType ? $details['proposalChecklistStatusType'] = $proposalChecklistStatusType : $errors[] = 'Proposal Checklist & Item Status Type is required';
      $orgDataID ? $details['orgDataID'] = $orgDataID : $errors[] = 'Organisation ID is required';
      $entityID ? $details['entityID'] = $entityID : $errors[] = 'Entity ID is required';

      if(!$errors){
         if($details) {
            $details['LastUpdateByID']=$userDetails->ID;
            $details['LastUpdate']=$config['currentDateTimeFormated'];
            if(!$DBConn->insert_data('tija_proposal_checklist_status', $details)) {
               $errors[] = 'Error adding new Proposal Checklist & Item Status';
            } else {
               $proposalChecklistStatusID = $DBConn->lastInsertID();
            }
         }
      }
   } else {
      $activivityCategoryDetails = Sales::tija_proposal_checklist_status(array('proposalChecklistStatusID'=>$proposalChecklistStatusID), true, $DBConn);
      $proposalChecklistStatusName && $proposalChecklistStatusName != $activivityCategoryDetails->proposalChecklistStatusName ? $changes['proposalChecklistStatusName'] = $proposalChecklistStatusName : "";
      $proposalChecklistStatusDescription && $proposalChecklistStatusDescription != $activivityCategoryDetails->proposalChecklistStatusDescription ? $changes['proposalChecklistStatusDescription'] = $proposalChecklistStatusDescription : "";
      $proposalChecklistStatusType && $proposalChecklistStatusType != $activivityCategoryDetails->proposalChecklistStatusType ? $changes['proposalChecklistStatusType'] = $proposalChecklistStatusType : "";
      $orgDataID && $orgDataID != $activivityCategoryDetails->orgDataID ? $changes['orgDataID'] = $orgDataID : "";
      $entityID && $entityID != $activivityCategoryDetails->entityID ? $changes['entityID'] = $entityID : "";
      if(!$errors){
         if($changes) {
            $changes['LastUpdatedByID']=$userDetails->ID;
            $changes['LastUpdate']=$config['currentDateTimeFormated'];
            if(!$DBConn->update_table('tija_proposal_checklist_status', $changes, array('proposalChecklistStatusID'=>$proposalChecklistStatusID))) {
               $errors[] = 'Error updating Proposal Checklist & Item Status';
            }
         }
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