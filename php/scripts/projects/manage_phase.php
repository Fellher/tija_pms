<?php
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
if ($isValidUser) {
	var_dump($_POST);
	$projectPhaseID = (isset($_POST['projectPhaseID'])  && !empty($_POST['projectPhaseID'])) ?  Utility::clean_string($_POST['projectPhaseID']) : "";
	$projectPhaseName = (isset($_POST['projectPhaseName'])  && !empty($_POST['projectPhaseName'])) ?  Utility::clean_string($_POST['projectPhaseName']) : "";
	$phaseWorkHrs = (isset($_POST['phaseWorkHrs'])  && !empty($_POST['phaseWorkHrs'])) ?  Utility::clean_string($_POST['phaseWorkHrs']) : "";
	$phaseWeighting = (isset($_POST['phaseWeighting'])  && !empty($_POST['phaseWeighting'])) ?  Utility::clean_string($_POST['phaseWeighting']) : "";
	$billingMilestone = (isset($_POST['billingMilestone'])  && !empty($_POST['billingMilestone'])) ?  Utility::clean_string($_POST['billingMilestone']) : "N";
	$projectID = (isset($_POST['projectID'])  && !empty($_POST['projectID'])) ?  Utility::clean_string($_POST['projectID']) : "";
	$phaseStartDate = (isset($_POST['phaseStartDate']) && !empty($_POST['phaseStartDate'])) ? Utility::clean_string($_POST['phaseStartDate']) : "";
	$phaseEndDate = (isset($_POST['phaseEndDate']) && !empty($_POST['phaseEndDate'])) ? Utility::clean_string($_POST['phaseEndDate']) : "";







	if ($projectPhaseID) {
		$phaseDetails = Projects::project_phases(array('projectPhaseID'=>$projectPhaseID, 'Suspended'=> 'N'), true, $DBConn);
		var_dump($phaseDetails);
		(isset($projectID) && $projectID  !== $phaseDetails->projectID) ? $changes['projectID'] = $projectID : '';
		(isset($projectPhaseName) && $projectPhaseName !== $phaseDetails->projectPhaseName) ? $changes['projectPhaseName'] = $projectPhaseName : '';
		(isset($phaseWorkHrs) && $phaseWorkHrs !== $phaseDetails->phaseWorkHrs) ? $changes['phaseWorkHrs'] = $phaseWorkHrs : '';
		(isset($phaseWeighting) && $phaseWeighting !== $phaseDetails->phaseWeighting) ? $changes['phaseWeighting'] = $phaseWeighting : '';
		(isset($billingMilestone) && $billingMilestone !== $phaseDetails->billingMilestone) ? $changes['billingMilestone'] = $billingMilestone : '';
		(isset($phaseStartDate) && $phaseStartDate !== $phaseDetails->phaseStartDate) ? $changes['phaseStartDate'] = $phaseStartDate : '';
		(isset($phaseEndDate) && $phaseEndDate !== $phaseDetails->phaseEndDate) ? $changes['phaseEndDate'] = $phaseEndDate : '';
		(isset($billingCycleID) && $billingCycleID !== null) ? $changes['billingCycleID'] = $billingCycleID : '';
		if (isset($changes['phaseStartDate']) && isset($changes['phaseEndDate']) && $changes['phaseEndDate'] < $changes['phaseStartDate']) {
			$errors[] = "Error: End date cannot be before start date.";
			$changes['phaseEndDate'] = $changes['phaseStartDate'];
		}

		var_dump($changes);
		if (count($errors)=== 0) {
			if ($changes) {
				if (!$DBConn->update_table('tija_project_phases', $changes, array('projectPhaseID'=> $projectPhaseID))) {
					$errors[]= "ERROR! there was an error saving the phase updates";
				}
			}
		}
	} else {
		$projectID ? $details['projectID'] = $projectID : $errors[] = 'Project ID is required.';
		$projectPhaseName ? $details['projectPhaseName'] = $projectPhaseName : $errors[] = 'Project Phase Name is required.';
		$phaseWorkHrs ? $details['phaseWorkHrs'] = $phaseWorkHrs : "";
		$phaseWeighting ? $details['phaseWeighting'] = $phaseWeighting : "";
		$billingMilestone ? $details['billingMilestone'] = $billingMilestone : $details['billingMilestone'] = 'N';
		$phaseStartDate ? $details['phaseStartDate'] = $phaseStartDate : $errors[] = 'Phase Start Date is required.';
		$phaseEndDate ? $details['phaseEndDate'] = $phaseEndDate : $errors[] = 'Phase End Date is required.';
		// billingCycleID is optional - only add if provided
		if (isset($billingCycleID) && $billingCycleID !== null && $billingCycleID !== '') {
			$details['billingCycleID'] = $billingCycleID;
		}
		if (isset($details['phaseStartDate']) && isset($details['phaseEndDate']) && $details['phaseEndDate'] < $details['phaseStartDate']) {
			$errors[] = "Error: End date cannot be before start date.";
			$details['phaseEndDate'] = $details['phaseStartDate'];
		}
		if (count($errors) === 0) {
			$details['projectPhaseID'] = Utility::generateUniqueID('tija_project_phases', 'projectPhaseID', $DBConn);
			if (!$DBConn->insert_data('tija_project_phases', $details)) {
				$errors[] = "ERROR! there was an error saving the new phase";
			} else {
				$projectPhaseID = $details['projectPhaseID'];
			}
		}
		if(!$errors) {

		}
	}

	var_dump($errors);

} else {
	$errors[] = 'You need to log in as a valid user to add new sales case.';
}

$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
var_dump($errors);
 if (count($errors) == 0) {
	 $DBConn->commit();
	 $messages = array(array('Text'=>'Project phase update successful!.', 'Type'=>'success'));
 } else {
	 $DBConn->rollback();
	 $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
 }
 $_SESSION['FlashMessages'] = serialize($messages);
 header("location:{$base}html/{$returnURL}");
?>