
<?php
session_start();
$base = '../../../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
$success="";
var_dump($_POST);
	var_dump($_FILES);
if ($isAdmin || $isValidAdmin) {
	echo "<h4> Job Title Details Posted</h4>";
	var_dump($_POST);
	var_dump($_FILES);
	$jobTitleID = (isset($_POST['jobTitleID']) && !empty($_POST['jobTitleID'])) ? Utility::clean_string($_POST['jobTitleID']) : '';
	$jobTitle = (isset($_POST['jobTitle']) && !empty($_POST['jobTitle'])) ? Utility::clean_string($_POST['jobTitle']) : '';
	$jobCategoryID = (isset($_POST['jobCategoryID']) && !empty($_POST['jobCategoryID'])) ? Utility::clean_string($_POST['jobCategoryID']) : '';
	$jobDescription = (isset($_POST['jobDescription']) && !empty($_POST['jobDescription'])) ? Utility::clean_string($_POST['jobDescription']) : '';
	$jobDescriptionDoc = (isset($_FILES['jobDescriptionDoc']) && !empty($_FILES['jobDescriptionDoc'])) ? $_FILES['jobDescriptionDoc'] : '';

	if($jobDescriptionDoc && $jobDescriptionDoc['error'] == 0) {
		$jobDescriptionDocUpload = File::uploadFile($jobDescriptionDoc, array('docx', 'doc', 'pdf', 'xls', 'xlsx'), 2097152, $config,  $DBConn);
		echo "<h4> Job Description Doc Upload</h4>";
		var_dump($jobDescriptionDocUpload);
	} else {
		$jobDescriptionDocUpload = '';
	}

	if($jobTitleID) {
		$jobTitleDetails = Admin::tija_job_titles(array('jobTitleID'=>$jobTitleID), true, $DBConn);
		var_dump($jobTitleDetails);
		$jobTitle && $jobTitleDetails->jobTitle != $jobTitle ? $changes['jobTitle'] = $jobTitle : '';
		$jobCategoryID && $jobTitleDetails->jobCategoryID != $jobCategoryID ? $changes['jobCategoryID'] = $jobCategoryID : '';
		$jobDescription && $jobTitleDetails->jobDescription != $jobDescription ? $changes['jobDescription'] = $jobDescription : '';
		$jobDescriptionDocUpload && $jobTitleDetails->jobDescriptionDoc != $jobDescriptionDocUpload ? $changes['jobDescriptionDoc'] = $jobDescriptionDocUpload : '';
		var_dump($changes);
		IF(count($changes) > 0 && COUNT($errors) == 0) {
			$changes['LastUpdatedByID'] = $userDetails->ID;
			$changes['LastUpdate'] = Utility::generateDateTime($DBConn);
			if($DBConn->update_table('tija_job_titles', $changes, array('jobTitleID'=>$jobTitleID))) {
				$success = "Job Title Details updated successfully";
			} else {
				$errors[] = "Job Title could not be updated";
			}
		} else {
			$errors[] = "No changes were made";
		}

	} else {
		($jobTitle) ? $details['jobTitle'] = $jobTitle : $errors[] = "Job Title is required";
		($jobCategoryID) ? $details['jobCategoryID'] = $jobCategoryID : $errors[] = "Job Category is required";
		($jobDescription) ? $details['jobDescription'] = $jobDescription : $errors[] = "Job Description is required";
		($jobDescriptionDocUpload) ? $details['jobDescriptionDoc'] = $jobDescriptionDocUpload :  "";

		if(count($errors) == 0) {
			if($details) {
			// $details['jobTitleID'] = Utility::generateUniqueID('tija_job_titles', 'jobTitleID', $DBConn);
				$details['DateAdded'] = Utility::generateDateTime($DBConn);
				$details['LastUpdatedByID'] = $userDetails->ID;
				$details['jobDescriptionDoc'] = $jobDescriptionDocUpload;
				// $details['jobDescriptionDoc'] = Utility::uploadFile('jobSpesification', 'jobDescriptionDoc', 'jobTitles', $details['jobTitleID'], $DBConn);
				var_dump($details);
				if($DBConn->insert_data('tija_job_titles', $details)) {
					$success = "Job Title added successfully";
				} else {
					$errors[] = "Job Title could not be added";
				}
				
				$success = "Job Title added successfully";
			}
		}
	}

	var_dump($errors);

    $returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
	var_dump($returnURL);
} else {
    $errors[] = "You are not authorized to perform this action.";
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