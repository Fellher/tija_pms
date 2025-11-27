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
	$proposalID = (isset($_POST['proposalID']) && !empty($_POST['proposalID'])) ? Utility::clean_string($_POST['proposalID']) : "";
	$proposalAttachmentID = (isset($_POST['proposalAttachmentID']) && !empty($_POST['proposalAttachmentID'])) ? Utility::clean_string($_POST['proposalAttachmentID']) : "";
	$proposalAttachmentName = (isset($_POST['proposalAttachmentName']) && !empty($_POST['proposalAttachmentName'])) ? Utility::clean_string($_POST['proposalAttachmentName']) : "";
	$proposalAttachmentFile = (isset($_FILES['proposalAttachmentFile']) && !empty($_FILES['proposalAttachmentFile'])) ? $_FILES['proposalAttachmentFile'] : "";
	$proposalAttachmentType = (isset($_POST['proposalAttachmentType']) && !empty($_POST['proposalAttachmentType'])) ? Utility::clean_string($_POST['proposalAttachmentType']) : "";
	// upload file
	$uploadFile = File::upload_file($proposalAttachmentFile, 'proposal_attachments', array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'), 1024 * 1024 * 10, $config, $DBConn);

	var_dump($uploadFile);

	if(!$proposalAttachmentID){
		$proposalID ? $details['proposalID'] = $proposalID : $errors[] = "Proposal ID is required";
		$proposalAttachmentName ? $details['proposalAttachmentName'] = $proposalAttachmentName : $errors[] = "Proposal Attachment Name is required";
		$uploadFile ? $details['proposalAttachmentFile'] = $uploadFile['uploadedFilePaths'] : $errors[] = "Proposal Attachment File is required";
		$proposalAttachmentType ? $details['proposalAttachmentType'] = $proposalAttachmentType : $errors[] = "Proposal Attachment Type is required";

		var_dump($details);
		if(!$errors){
			if($details){
				$details['LastUpdateByID'] = $userDetails->ID;
				$details['LastUpdate'] = date('Y-m-d H:i:s');
				if(!$DBConn->insert_data('tija_proposal_attachments', $details)){
					$errors[] = "Failed to insert proposal attachment";
				}
			}
		}
	} else {
		$proposalAttachmentDetails = Proposal::proposal_attachments(['proposalAttachmentID' => $proposalAttachmentID], true, $DBConn);
		if($proposalAttachmentDetails){
			$proposalID && $proposalAttachmentDetails->proposalID != $proposalID ? $changes['proposalID'] = $proposalID : "";
			$proposalAttachmentName && $proposalAttachmentDetails->proposalAttachmentName != $proposalAttachmentName ? $changes['proposalAttachmentName'] = $proposalAttachmentName : "";
			$uploadFile && $proposalAttachmentDetails->proposalAttachmentFile != $uploadFile['uploadedFilePaths'] ? $changes['proposalAttachmentFile'] = $uploadFile['uploadedFilePaths'] : "";
			$proposalAttachmentType && $proposalAttachmentDetails->proposalAttachmentType != $proposalAttachmentType ? $changes['proposalAttachmentType'] = $proposalAttachmentType : "";
			
			if($changes){
				$changes['LastUpdateBy'] = $userDetails->ID;
				$changes['LastUpdate'] = date('Y-m-d H:i:s');
				if(!$DBConn->update_data('tija_proposal_attachments', $changes, ['proposalAttachmentID' => $proposalAttachmentID])){
					$errors[] = "Failed to update proposal attachment";
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
 header("location:{$base}html/{$returnURL}");?>