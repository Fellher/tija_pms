
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

   $proposalChecklistItemAssignmentSubmissionID = isset($_POST['proposalChecklistItemAssignmentSubmissionID']) && !empty($_POST['proposalChecklistItemAssignmentSubmissionID']) ? $_POST['proposalChecklistItemAssignmentSubmissionID'] : '';
   $proposalChecklistItemAssignmentID = isset($_POST['proposalChecklistItemAssignmentID']) && !empty($_POST['proposalChecklistItemAssignmentID']) ? $_POST['proposalChecklistItemAssignmentID'] : '';
   $proposalChecklistItemID = isset($_POST['proposalChecklistItemID']) && !empty($_POST['proposalChecklistItemID']) ? $_POST['proposalChecklistItemID'] : '';
   $checklistItemAssignedEmployeeID = isset($_POST['checklistItemAssignedEmployeeID']) && !empty($_POST['checklistItemAssignedEmployeeID']) ? $_POST['checklistItemAssignedEmployeeID'] : '';
   $proposalChecklistItemAssignmentStatusID = isset($_POST['proposalChecklistItemAssignmentStatusID']) && !empty($_POST['proposalChecklistItemAssignmentStatusID']) ? $_POST['proposalChecklistItemAssignmentStatusID'] : '';
   $proposalChecklistItemUploadfile = isset($_FILES['proposalChecklistItemUploadfile']) && !empty($_FILES['proposalChecklistItemUploadfile']) ? $_FILES['proposalChecklistItemUploadfile'] : '';
   $proposalChecklistItemAssignmentSubmissionDescription = isset($_POST['proposalChecklistItemAssignmentSubmissionDescription']) && !empty($_POST['proposalChecklistItemAssignmentSubmissionDescription']) ? $_POST['proposalChecklistItemAssignmentSubmissionDescription'] : '';
   $proposalChecklistItemAssignmentSubmissionDate = isset($_POST['proposalChecklistItemAssignmentSubmissionDate']) && !empty($_POST['proposalChecklistItemAssignmentSubmissionDate']) ? $_POST['proposalChecklistItemAssignmentSubmissionDate'] : date('Y-m-d H:i:s');
   $proposalChecklistItemAssignmentSubmissionStatusID = isset($_POST['proposalChecklistItemAssignmentSubmissionStatusID']) && !empty($_POST['proposalChecklistItemAssignmentSubmissionStatusID']) ? $_POST['proposalChecklistItemAssignmentSubmissionStatusID'] : 1; // Default to 'Pending' status

   if (isset($proposalChecklistItemUploadfile) && !empty($proposalChecklistItemUploadfile)) {

      var_dump($proposalChecklistItemUploadfile);
       
      $fileUploaded = File::multiple_file_upload(
         $_FILES,
         'proposal_checklist_items', 
          array('jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'), 
          10 * 1024 * 1024,
         $config, $DBConn
      ); // 10 MB limit

         var_dump($fileUploaded);

      if ($fileUploaded['success']) {
         $fileNames = $fileUploaded['fileNames'];
         $filePaths = $fileUploaded['uploadedFilePaths'];
         
      } else {
         $errors[] = $fileUploaded['error'];
      }
      // if (!empty($fileNames)) {
      //    // Update the database with the file names
      //    $fileNamesString = implode(',', $fileNames);
      //    $updateQuery = "UPDATE tija_proposal_checklist_item_assignment_submissions SET proposalChecklistItemUploadfiles = ? WHERE proposalChecklistItemAssignmentSubmissionID = ?";
      //    $DBConn->execute_query($updateQuery, array($fileNamesString, $proposalChecklistItemAssignmentSubmissionID));
      // }
   }

   if(!$proposalChecklistItemAssignmentSubmissionID){
      //collect all file submission ddetails
      $proposalChecklistItemAssignmentID ? $details['proposalChecklistItemAssignmentID'] = Utility::clean_string($proposalChecklistItemAssignmentID) : $errors[] = 'Invalid Assignment ID.';
      $checklistItemAssignedEmployeeID ? $details['checklistItemAssignedEmployeeID'] = Utility::clean_string($checklistItemAssignedEmployeeID) : $errors[] = 'Invalid Employee ID.';
      $proposalChecklistItemID ? $details['proposalChecklistItemID'] = Utility::clean_string($proposalChecklistItemID) : $errors[] = 'Invalid Checklist Item ID.';
      $proposalChecklistItemAssignmentSubmissionDescription ? $details['proposalChecklistItemAssignmentSubmissionDescription'] = Utility::clean_string($proposalChecklistItemAssignmentSubmissionDescription) : null;
      $proposalChecklistItemAssignmentSubmissionDate ? $details['proposalChecklistItemAssignmentSubmissionDate'] = Utility::clean_string($proposalChecklistItemAssignmentSubmissionDate) : $details['proposalChecklistItemAssignmentSubmissionDate'] = date('Y-m-d H:i:s');
      $proposalChecklistItemAssignmentSubmissionStatusID ? $details['proposalChecklistItemAssignmentSubmissionStatusID'] = Utility::clean_string($proposalChecklistItemAssignmentSubmissionStatusID) : $details['proposalChecklistItemAssignmentSubmissionStatusID'] = 1; // Default to 'Pending' status
      if (isset($fileNames) && !empty($fileNames)) {
         $details['proposalChecklistItemUploadfiles'] = implode(',', $filePaths);
      } else {
         $details['proposalChecklistItemUploadfiles'] = null;
      }

      if(!$errors) {
         $details['createdByID'] = $userDetails->ID;
         $details['DateAdded'] = date('Y-m-d H:i:s');
         $details['LastUpdateByID'] = $userDetails->ID;
         $details['LastUpdate'] = date('Y-m-d H:i:s');
         var_dump($details);
         if($details){
            if(!$DBConn->insert_data('tija_proposal_checklist_item_assignment_submissions', $details)){
               $errors[] = 'Could not save submission details. Please try again.';
            } else {
               $success = 'Submission details saved successfully.';
               $proposalChecklistItemAssignmentSubmissionID = $DBConn->lastInsertId();
            }
         }
      }


   } else {
      $proposalChecklistItemAssignmentSubmissionID = Utility::clean_string($proposalChecklistItemAssignmentSubmissionID);
      $proposalChecklistItemAssignmentSubmissionDetails = Proposal::proposal_checklist_item_assignment_submissions(['proposalChecklistItemAssignmentSubmissionID' => $proposalChecklistItemAssignmentSubmissionID], true, $DBConn );
      var_dump($proposalChecklistItemAssignmentSubmissionDetails);
      $proposalChecklistItemAssignmentID && $proposalChecklistItemAssignmentID != $proposalChecklistItemAssignmentSubmissionDetails->proposalChecklistItemAssignmentID ? $changes['proposalChecklistItemAssignmentID'] = Utility::clean_string($proposalChecklistItemAssignmentID) : null;
      $checklistItemAssignedEmployeeID && $checklistItemAssignedEmployeeID != $proposalChecklistItemAssignmentSubmissionDetails->checklistItemAssignedEmployeeID ? $changes['checklistItemAssignedEmployeeID'] = Utility::clean_string($checklistItemAssignedEmployeeID) : null; 
      $proposalChecklistItemID && $proposalChecklistItemID != $proposalChecklistItemAssignmentSubmissionDetails->proposalChecklistItemID ? $changes['proposalChecklistItemID'] = Utility::clean_string($proposalChecklistItemID) : null;
      $proposalChecklistItemAssignmentSubmissionDescription && $proposalChecklistItemAssignmentSubmissionDescription != $proposalChecklistItemAssignmentSubmissionDetails->proposalChecklistItemAssignmentSubmissionDescription ? $changes['proposalChecklistItemAssignmentSubmissionDescription'] = Utility::clean_string($proposalChecklistItemAssignmentSubmissionDescription) : null;
      $proposalChecklistItemAssignmentSubmissionDate && $proposalChecklistItemAssignmentSubmissionDate != $proposalChecklistItemAssignmentSubmissionDetails->proposalChecklistItemAssignmentSubmissionDate ? $changes['proposalChecklistItemAssignmentSubmissionDate'] = Utility::clean_string($proposalChecklistItemAssignmentSubmissionDate) : null;
      $proposalChecklistItemAssignmentSubmissionStatusID && $proposalChecklistItemAssignmentSubmissionStatusID != $proposalChecklistItemAssignmentSubmissionDetails->proposalChecklistItemAssignmentSubmissionStatusID ? $changes['proposalChecklistItemAssignmentSubmissionStatusID'] = Utility::clean_string($proposalChecklistItemAssignmentSubmissionStatusID) : null;
      if (isset($fileNames) && !empty($fileNames)) {
         $changes['proposalChecklistItemUploadfiles'] = implode(',', $filePaths);
      }
      if($changes){
         $changes['LastUpdateBy'] = $userDetails->ID;
         $changes['LastUpdateDate'] = date('Y-m-d H:i:s');
         var_dump($changes);
         if(!$DBConn->update('tija_proposal_checklist_item_assignment_submissions', $changes, ['proposalChecklistItemAssignmentSubmissionID' => $proposalChecklistItemAssignmentSubmissionID])){
            $errors[] = 'Could not update submission details. Please try again.';
         } else {
            $success = 'Submission details updated successfully.';
         }
      } else {
         $errors[] = 'No changes detected.';
      }

   }



   var_dump($errors);

   $returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performance&p=home');
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