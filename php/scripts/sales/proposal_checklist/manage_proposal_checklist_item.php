
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
   $proposalChecklistItemName = (isset($_POST['proposalChecklistItemName']) && !empty($_POST['proposalChecklistItemName'])) ?  Utility::clean_string($_POST['proposalChecklistItemName']): "";
   $proposalChecklistItemDescription = (isset($_POST['proposalChecklistItemDescription']) && !empty($_POST['proposalChecklistItemDescription'])) ?  Utility::clean_string($_POST['proposalChecklistItemDescription']): "";
   $proposalChecklistItemCategoryID = (isset($_POST['proposalChecklistItemCategoryID']) && !empty($_POST['proposalChecklistItemCategoryID'])) ?  Utility::clean_string($_POST['proposalChecklistItemCategoryID']): "";
   $proposalChecklistItemID = (isset($_POST['proposalChecklistItemID']) && !empty($_POST['proposalChecklistItemID'])) ?  Utility::clean_string($_POST['proposalChecklistItemID']): "";


   if($proposalChecklistItemID) {
      $proposalChecklistItemDetails = Proposal::proposal_checklist_items(array('proposalChecklistItemID'=>$proposalChecklistItemID), true, $DBConn);
      $proposalChecklistItemName && $proposalChecklistItemName != $proposalChecklistItemDetails->proposalChecklistItemName ? $changes['proposalChecklistItemName'] = $proposalChecklistItemName : "";
      $proposalChecklistItemDescription && $proposalChecklistItemDescription != $proposalChecklistItemDetails->proposalChecklistItemDescription ? $changes['proposalChecklistItemDescription'] = $proposalChecklistItemDescription : "";
      $proposalChecklistItemCategoryID && $proposalChecklistItemCategoryID != $proposalChecklistItemDetails->ProposalChecklistItemCategoryID ? $changes['ProposalChecklistItemCategoryID'] = $proposalChecklistItemCategoryID : "";
      if(!$errors){
         if($changes) {        
            $changes['LastUpdated']=$config['currentDateTimeFormated'];
            if($proposalChecklistItemDetails->LastUpdateByID !== $userDetails->ID) {
               $changes['LastUpdateByID'] = $userDetails->ID;
            }           
            if(!$DBConn->update_table('tija_proposal_checklist_items', $changes, array('ProposalChecklistItemID'=>$proposalChecklistItemID))) {
               $errors[] = 'Error updating Proposal Checklist Item';
            }
         }
      }
   } else {
    
         $proposalChecklistItemName ? $details['ProposalChecklistItemName'] = $proposalChecklistItemName : $errors[] = 'Proposal Checklist Item Name is required';
         $proposalChecklistItemDescription ? $details['ProposalChecklistItemDescription'] = $proposalChecklistItemDescription : $errors[] = 'Proposal Checklist Item Description is required';
         $proposalChecklistItemCategoryID ? $details['ProposalChecklistItemCategoryID'] = $proposalChecklistItemCategoryID : $errors[] = 'Proposal Checklist Item Category is required';
         $details['LastUpdateByID']=$userDetails->ID;
         $details['LastUpdate']=$config['currentDateTimeFormated'];
         var_dump($details);
      if(!$errors){
         if($details) {
            if(!$DBConn->insert_data('tija_proposal_checklist_items', $details)) {
               $errors[] = 'Error adding new Proposal Checklist Item';
            } else {
               $success = "New Proposal Checklist Item added successfully";
            }
         }
      }
     
   }

   var_dump($errors);

   var_dump($success);

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