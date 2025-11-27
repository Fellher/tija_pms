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
   var_dump($_FILES);
   $proposalChecklistTemplateFile = null;
   $checklistAssignmentDocumentFile = null;

   // Get the checklist details from the POST request
   $proposalChecklistItemAssignmentID = (isset($_POST['proposalChecklistItemAssignmentID']) && $_POST['proposalChecklistItemAssignmentID'] != '') ? Utility::clean_string($_POST['proposalChecklistItemAssignmentID']) : null; 
   $proposalID = (isset($_POST['proposalID']) && $_POST['proposalID'] != '') ? Utility::clean_string($_POST['proposalID']) : null;
   $orgDataID = (isset($_POST['orgDataID']) && $_POST['orgDataID'] != '') ? Utility::clean_string($_POST['orgDataID']) : null;
   $entityID = (isset($_POST['entityID']) && $_POST['entityID'] != '') ? Utility::clean_string($_POST['entityID']) : null;
   $proposalChecklistID = (isset($_POST['proposalChecklistID']) && $_POST['proposalChecklistID'] != '') ? Utility::clean_string($_POST['proposalChecklistID']) : null;
   $proposalChecklistItemCategoryID = (isset($_POST['proposalChecklistItemCategoryID']) && $_POST['proposalChecklistItemCategoryID'] != '') ? Utility::clean_string($_POST['proposalChecklistItemCategoryID']) : null;
   $proposalChecklistItemID = (isset($_POST['proposalChecklistItemID']) && $_POST['proposalChecklistItemID'] != '') ? Utility::clean_string($_POST['proposalChecklistItemID']) : null;
   $proposalChecklistItemAssignmentDueDate = (isset($_POST['proposalChecklistItemAssignmentDueDate']) && $_POST['proposalChecklistItemAssignmentDueDate'] != '') ? Utility::clean_string($_POST['proposalChecklistItemAssignmentDueDate']) : null;
   $proposalChecklistItemAssignmentDescription = (isset($_POST['proposalChecklistItemAssignmentDescription']) && $_POST['proposalChecklistItemAssignmentDescription'] != '') ? Utility::clean_string($_POST['proposalChecklistItemAssignmentDescription']) : null;
   $proposalChecklistTemplate = (isset($_FILES['proposalChecklistTemplate']) && $_FILES['proposalChecklistTemplate'] != '') ? $_FILES['proposalChecklistTemplate'] : null;
   $checklistAssignmentDocument = (isset($_FILES['checklistAssignmentDocument']) && $_FILES['checklistAssignmentDocument'] != '') ? $_FILES['checklistAssignmentDocument'] : null;
   $proposalChecklistItemAssignmentStatusID = (isset($_POST['proposalChecklistItemAssignmentStatusID']) && $_POST['proposalChecklistItemAssignmentStatusID'] != '') ? Utility::clean_string($_POST['proposalChecklistItemAssignmentStatusID']) : null;
   $checklistItemAssignedEmployeeID = (isset($_POST['checklistItemAssignedEmployeeID']) && $_POST['checklistItemAssignedEmployeeID'] != '') ? Utility::clean_string($_POST['checklistItemAssignedEmployeeID']) : null;
   $proposalChecklistAssignorID = (isset($_POST['proposalChecklistAssignorID']) && $_POST['proposalChecklistAssignorID'] != '') ? Utility::clean_string($_POST['proposalChecklistAssignorID']) : $userDetails->ID;
   

  

// Upload checklist template and assignment document
   if ($proposalChecklistTemplate && $proposalChecklistTemplate['error'] === 0) {
      $maxFileSize = $config['MaxUploadedFileSize'];
      $fileUploadResults= File::upload_file($proposalChecklistTemplate, 'checklist', array('pdf', 'docx', 'doc', 'xlsx', 'csv', 'xls', 'txt'),$maxFileSize , $config, $DBConn);

      if ($fileUploadResults['errors']) {
         $errors = array_merge($errors, $fileUploadResults['errors']);
      } else {
         $proposalChecklistTemplateFile = $fileUploadResults['uploadedFilePaths'];
         $success = "Checklist template uploaded successfully.";
         echo "<h4> File  </h4>";
         var_dump($proposalChecklistTemplateFile);
      }
   } 
   if ($checklistAssignmentDocument && $checklistAssignmentDocument['error'] === 0) {
      $maxFileSize = $config['MaxUploadedFileSize'];
      $fileUploadResults= File::upload_file($checklistAssignmentDocument, 'checklist', array('pdf', 'docx', 'doc', 'xlsx', 'csv', 'xls', 'txt'),$maxFileSize , $config, $DBConn);

      if ($fileUploadResults['errors']) {
         $errors = array_merge($errors, $fileUploadResults['errors']);
      } else {
         $checklistAssignmentDocumentFile = $fileUploadResults['uploadedFilePaths'];
         $success = "Checklist assignment document uploaded successfully.";
         echo "<h4> File  </h4>";
         var_dump($checklistAssignmentDocumentFile);
      }
   }
  


   if($proposalChecklistItemAssignmentID && $proposalChecklistItemAssignmentID != '') {
      
      $checklistAssignmentDetails = Proposal::proposal_checklist_item_assignment(['proposalChecklistItemAssignmentID'=> $proposalChecklistItemAssignmentID], true,  $DBConn);

      $proposalID && $checklistAssignmentDetails->proposalID != $proposalID ?$changes['proposalID'] = $proposalID : '';
      $orgDataID && $checklistAssignmentDetails->orgDataID != $orgDataID ?$changes['orgDataID'] = $orgDataID : '';
      $entityID && $checklistAssignmentDetails->entityID != $entityID ?$changes['entityID'] = $entityID : '';
      $proposalChecklistID && $checklistAssignmentDetails->proposalChecklistID != $proposalChecklistID ?$changes['proposalChecklistID'] = $proposalChecklistID : '';
      $proposalChecklistItemID && $checklistAssignmentDetails->proposalChecklistItemID != $proposalChecklistItemID ?$changes['proposalChecklistItemID'] = $proposalChecklistItemID : '';
      $proposalChecklistItemAssignmentDescription && $checklistAssignmentDetails->proposalChecklistItemAssignmentDescription != $proposalChecklistItemAssignmentDescription ?$changes['proposalChecklistItemAssignmentDescription'] = $proposalChecklistItemAssignmentDescription : '';
      $proposalChecklistItemAssignmentStatusID && $checklistAssignmentDetails->proposalChecklistItemAssignmentStatusID != $proposalChecklistItemAssignmentStatusID ?$changes['proposalChecklistItemAssignmentStatusID'] = $proposalChecklistItemAssignmentStatusID : '';
      $checklistItemAssignedEmployeeID && $checklistAssignmentDetails->checklistItemAssignedEmployeeID != $checklistItemAssignedEmployeeID ?$changes['checklistItemAssignedEmployeeID'] = $checklistItemAssignedEmployeeID : '';
      $proposalChecklistItemAssignmentDueDate && $checklistAssignmentDetails->proposalChecklistItemAssignmentDueDate != $proposalChecklistItemAssignmentDueDate ?$changes['proposalChecklistItemAssignmentDueDate'] = $proposalChecklistItemAssignmentDueDate : '';
      $proposalChecklistTemplateFile && $checklistAssignmentDetails->proposalProposalChecklistTemplate != $proposalChecklistTemplateFile ?$changes['proposalProposalChecklistTemplate'] = $proposalChecklistTemplateFile : '';
      $checklistAssignmentDocumentFile && $checklistAssignmentDetails->proposalChecklistAssignmentDocument != $checklistAssignmentDocumentFile ?$changes['proposalChecklistAssignmentDocument'] = $checklistAssignmentDocumentFile : '';
      $proposalChecklistAssigneeID && $checklistAssignmentDetails->proposalChecklistAssigneeID != $proposalChecklistAssigneeID ?$changes['proposalChecklistAssigneeID'] = $proposalChecklistAssigneeID : '';
      $changes['lastUpdate']=$config['currentDateTimeFormated'];
      $changes['lastUpdateByID']=$userDetails->ID;

      if($changes){
         if(!$DBConn->update_table('tija_proposal_checklist_item_assignment', $changes, array('proposalChecklistItemAssignmentID'=>$proposalChecklistItemAssignmentID))){
            $errors[] = 'Failed to update checklist item assignment.';
         } else {
            $success = "Checklist item assignment updated successfully.";
         }
      }

      
   } else {
      $proposalID ? $details['proposalID'] = $proposalID : $errors[] = 'Proposal ID is required.';
      $orgDataID ? $details['orgDataID'] = $orgDataID : $errors[] = 'Organisation ID is required.';
      $entityID ? $details['entityID'] = $entityID : $errors[] = 'Entity ID is required.';
      $proposalChecklistID ? $details['proposalChecklistID'] = $proposalChecklistID : $errors[] = 'Proposal Checklist ID is required.';
      $proposalChecklistItemCategoryID ? $details['proposalChecklistItemCategoryID'] = $proposalChecklistItemCategoryID : $errors[] = 'Proposal Checklist Item Category ID is required.';
      $proposalChecklistItemID ? $details['proposalChecklistItemID'] = $proposalChecklistItemID : $errors[] = 'Proposal Checklist Item ID is required.';
      $proposalChecklistItemAssignmentDueDate ? $details['proposalChecklistItemAssignmentDueDate'] = $proposalChecklistItemAssignmentDueDate : $errors[] = 'Proposal Checklist Item Assignment Due Date is required.';
      $proposalChecklistItemAssignmentDescription ? $details['proposalChecklistItemAssignmentDescription'] = $proposalChecklistItemAssignmentDescription : $errors[] = 'Proposal Checklist Item Assignment Description is required.';
      $proposalChecklistItemAssignmentStatusID ? $details['proposalChecklistItemAssignmentStatusID'] = $proposalChecklistItemAssignmentStatusID : $errors[] = 'Proposal Checklist Item Assignment Status ID is required.';
      $checklistItemAssignedEmployeeID ? $details['checklistItemAssignedEmployeeID'] = $checklistItemAssignedEmployeeID : $errors[] = 'Checklist Item Assigned Employee ID is required.';
      $proposalChecklistAssignorID ? $details['proposalChecklistAssignorID'] = $proposalChecklistAssignorID : $errors[] = 'Proposal Checklist Assignor ID is required.';
      $checklistAssignmentDocumentFile ? $details['proposalChecklistAssignmentDocument'] = $checklistAssignmentDocumentFile : "";
      $proposalChecklistTemplateFile ? $details['proposalChecklistTemplate'] = $proposalChecklistTemplateFile : "";
     
      var_dump($errors);

      var_dump($details);

      if(!$errors){
         if($details){
            $details['lastUpdate']=$config['currentDateTimeFormated'];
            $details['lastUpdateByID']=$userDetails->ID;
            if(!$DBConn->insert_data('tija_proposal_checklist_item_assignment', $details)){
               $errors[] = 'Failed to insert checklist item assignment.';
            } else {
               $success = "Checklist item assignment created successfully.";
            }
         }
      }
      



   } 



   var_dump($_FILES);

   
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
 var_dump($_SESSION['FlashMessages']);
 header("location:{$base}html/{$returnURL}");
 ?>