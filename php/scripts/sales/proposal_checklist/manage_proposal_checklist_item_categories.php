
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
   $proposalChecklistItemCategoryID = (isset($_POST['proposalChecklistItemCategoryID']) && !empty($_POST['proposalChecklistItemCategoryID'])) ?  Utility::clean_string($_POST['proposalChecklistItemCategoryID']): "";
   $proposalChecklistItemCategoryName = (isset($_POST['proposalChecklistItemCategoryName']) && !empty($_POST['proposalChecklistItemCategoryName'])) ?  Utility::clean_string($_POST['proposalChecklistItemCategoryName']): "";
   $proposalChecklistItemCategoryDescription = (isset($_POST['proposalChecklistItemCategoryDescription']) && !empty($_POST['proposalChecklistItemCategoryDescription'])) ?  Utility::clean_string($_POST['proposalChecklistItemCategoryDescription']): "";
   if(!$proposalChecklistItemCategoryID) {
      $proposalChecklistItemCategoryName ? $details['proposalChecklistItemCategoryName'] = $proposalChecklistItemCategoryName : $errors[] = 'Proposal Checklist Item Category Name is required';
      $proposalChecklistItemCategoryDescription ? $details['proposalChecklistItemCategoryDescription'] = $proposalChecklistItemCategoryDescription : $errors[] = 'Proposal Checklist Item Category Description is required';
      if(!$errors){
         if($details) {
            $details['LastUpdateByID']=$userDetails->ID;
            $details['LastUpdate']=$config['currentDateTimeFormated'];
            if(!$DBConn->insert_data('tija_proposal_checklist_item_categories', $details)) {
               $errors[] = 'Error adding new Proposal Checklist Item Category';
            } else {
               $proposalChecklistItemCategoryID = $DBConn->lastInsertID();
            }
         }
      }
   } else {
      $proposalChecklistItemCategoryDetails = Proposal::proposal_checklist_items_categories(array('proposalChecklistItemCategoryID'=>$proposalChecklistItemCategoryID), true, $DBConn);
      $proposalChecklistItemCategoryName && $proposalChecklistItemCategoryName != $proposalChecklistItemCategoryDetails->proposalChecklistItemCategoryName ? $changes['proposalChecklistItemCategoryName'] = $proposalChecklistItemCategoryName : "";
      $proposalChecklistItemCategoryDescription && $proposalChecklistItemCategoryDescription != $proposalChecklistItemCategoryDetails->proposalChecklistItemCategoryDescription ? $changes['proposalChecklistItemCategoryDescription'] = $proposalChecklistItemCategoryDescription : "";
      $proposalChecklistItemCategoryDetails->LastUpdateByID !== $userDetails->ID ? $changes['LastUpdateByID'] = $userDetails->ID : "";
      if(!$errors){
         if($changes) {
            $changes['LastUpdatedByID']=$userDetails->ID;            
            if(!$DBConn->update_table('tija_proposal_checklist_item_categories', $changes, array('proposalChecklistItemCategoryID'=>$proposalChecklistItemCategoryID))) {
               $errors[] = 'Error updating Proposal Checklist Item Category';
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