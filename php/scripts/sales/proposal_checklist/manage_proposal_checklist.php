
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
   $proposalChecklistID = (isset($_POST['proposalChecklistID']) && !empty($_POST['proposalChecklistID'])) ?  Utility::clean_string($_POST['proposalChecklistID']): "";
   $proposalChecklistName = (isset($_POST['proposalChecklistName']) && !empty($_POST['proposalChecklistName'])) ?  Utility::clean_string($_POST['proposalChecklistName']): "";
   $proposalChecklistDescription = (isset($_POST['proposalChecklistDescription']) && !empty($_POST['proposalChecklistDescription'])) ?  Utility::clean_string($_POST['proposalChecklistDescription']): "";
   $proposalChecklistStatusID = (isset($_POST['proposalChecklistStatusID']) && !empty($_POST['proposalChecklistStatusID'])) ?  Utility::clean_string($_POST['proposalChecklistStatusID']): "";
   $proposalID = (isset($_POST['proposalID']) && !empty($_POST['proposalID'])) ?  Utility::clean_string($_POST['proposalID']): "";
   $assignedEmployeeID = (isset($_POST['assignedEmployeeID']) && !empty($_POST['assignedEmployeeID'])) ?  Utility::clean_string($_POST['assignedEmployeeID']): "";
   $orgDataID = (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ?  Utility::clean_string($_POST['orgDataID']): "";
   $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ?  Utility::clean_string($_POST['entityID']): "";
   $assigneeID = (isset($_POST['assigneeID']) && !empty($_POST['assigneeID'])) ?  Utility::clean_string($_POST['assigneeID']): "";
   $proposalChecklistDeadlineDate = (isset($_POST['proposalChecklistDeadlineDate']) && !empty($_POST['proposalChecklistDeadlineDate'])) ?  Utility::clean_string($_POST['proposalChecklistDeadlineDate']): "";
   

   if($proposalChecklistID){
      $proposalChecklistDetails = Proposal::proposal_checklist(array('proposalChecklistID'=>$proposalChecklistID), true, $DBConn);
      var_dump($proposalChecklistDetails);
      $proposalChecklistName && $proposalChecklistName != $proposalChecklistDetails->proposalChecklistName ? $changes['proposalChecklistName'] = $proposalChecklistName : "";
      $proposalChecklistDescription && $proposalChecklistDescription != $proposalChecklistDetails->proposalChecklistDescription ? $changes['ProposalChecklistDescription'] = $proposalChecklistDescription : "";
      $assignedEmployeeID && $assignedEmployeeID != $proposalChecklistDetails->assignedEmployeeID ? $changes['assignedEmployeeID'] = $assignedEmployeeID : "";
      $proposalChecklistStatusID && $proposalChecklistStatusID != $proposalChecklistDetails->proposalChecklistStatusID ? $changes['proposalChecklistStatusID'] = $proposalChecklistStatusID : "";
      $orgDataID && $orgDataID != $proposalChecklistDetails->orgDataID ? $changes['orgDataID'] = $orgDataID : "";
      $entityID && $entityID != $proposalChecklistDetails->entityID ? $changes['entityID'] = $entityID : "";
      $proposalID && $proposalID != $proposalChecklistDetails->proposalID ? $changes['proposalID'] = $proposalID : "";
      $proposalChecklistDeadlineDate && $proposalChecklistDeadlineDate != $proposalChecklistDetails->proposalChecklistDeadlineDate ? $changes['proposalChecklistDeadlineDate'] = $proposalChecklistDeadlineDate : "";

      if(!$errors){
         if($changes) {        
            $changes['LastUpdate']=$config['currentDateTimeFormated'];
            if($proposalChecklistDetails->LastUpdateByID !== $userDetails->ID) {
               $changes['LastUpdateByID'] = $userDetails->ID;
            }           
            if(!$DBConn->update_table('tija_proposal_checklists', $changes, array('ProposalChecklistID'=>$proposalChecklistID))) {
               $errors[] = 'Error updating Proposal Checklist';
            }
         }
      }
   } else {
          // Add new checklist
          if($orgDataID && $entityID) {
             // Check if the proposal checklist already exists for the proposal
            //  if(Proposal::check_proposal_checklist_exists($orgDataID, $entityID, $proposalID, $DBConn)) {
            //     $errors[] = 'Proposal Checklist already exists for this proposal';
            //  } else {
            $proposalChecklistName ? $details['ProposalChecklistName'] = $proposalChecklistName : $errors[] = 'Proposal Checklist Name is required';
            $proposalChecklistDescription ? $details['ProposalChecklistDescription'] = $proposalChecklistDescription : $errors[] = 'Proposal Checklist Description is required';
            $proposalChecklistStatusID ? $details['ProposalChecklistStatusID'] = $proposalChecklistStatusID : $errors[] = 'Proposal Checklist Status is required';
            $assignedEmployeeID ? $details['AssignedEmployeeID'] = $assignedEmployeeID :"";
            $orgDataID ? $details['OrgDataID'] = $orgDataID : $errors[] = 'Organisation ID is required';
            $entityID ? $details['EntityID'] = $entityID : $errors[] = 'Entity ID is required';
            $proposalID ? $details['ProposalID'] = $proposalID : $errors[] = 'Proposal ID is required';
            $assignedEmployeeID ? $details['AssignedEmployeeID'] = $assignedEmployeeID : $errors[] = 'Assigned Employee is required';
            $details['AssigneeID'] = $assigneeID ?  $assignedEmployeeID :  $userDetails->ID;
            $proposalChecklistDeadlineDate ? $details['ProposalChecklistDeadlineDate'] = $proposalChecklistDeadlineDate : $errors[] = 'Proposal Checklist Deadline Date is required';

            var_dump($details);
            if(!$errors){
               if($details) {
                  if(!$DBConn->insert_data('tija_proposal_checklists', $details)) {
                     $errors[] = 'Error adding new Proposal Checklist';
                  } else {
                     $success = "New Proposal Checklist added successfully";
                  }
               }
            }
               
                $details['LastUpdateByID']=$userDetails->ID;
                $details['LastUpdate']=$config['currentDateTimeFormated'];
            //  }
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
 ?>