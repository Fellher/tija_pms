<?php
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$changes=array();
if ($isValidUser) {
	var_dump($_POST);

	(isset($_POST['saleID']) && !empty($_POST['saleID'])) ? $details['saleID'] = Utility::clean_string($_POST['saleID']): "";
	(isset($_POST['activityName']) && !empty($_POST['activityName'])) ? $details['activityName'] = Utility::clean_string($_POST['activityName']): "";
	(isset($_POST['activityTypeID']) && !empty($_POST['activityTypeID'])) ? $details['activityTypeID'] = Utility::clean_string($_POST['activityTypeID']): "";
	$details['activityCategory'] = (isset($_POST['duration']) && !empty($_POST['duration'])) ?  Utility::clean_string($_POST['duration']): "deadline";

	if ($details['activityCategory'] === "deadline") {		
		(isset($_POST['DeadlineDate']) && !empty($_POST['DeadlineDate']) && preg_match($config['ISODateFormat'], $_POST['DeadlineDate'])) ? $details['deadlineDate'] = Utility::clean_string($_POST['DeadlineDate']): "";
	} else {
		(isset($_POST['enddate']) && !empty($_POST['enddate']) && preg_match($config['ISODateFormat'], $_POST['enddate'])) ? $details['deadlineDate'] = Utility::clean_string($_POST['enddate']): "";
		(isset($_POST['startDate']) && !empty($_POST['startDate']) && preg_match($config['ISODateFormat'], $_POST['startDate'])) ? $details['startDate'] = Utility::clean_string($_POST['startDate']): "";
	}
	
	(isset($_POST['activityOwner']) && !empty($_POST['activityOwner'])) ? $details['activityOwnerID'] = Utility::clean_string($_POST['activityOwner']): "";
	(isset($_POST['description']) && !empty($_POST['description'])) ? $details['description'] = $_POST['description'] : "";

	$saleActivityID = (isset($_POST['saleActivityID']) && !empty($_POST['saleActivityID'])) ? Utility::clean_string($_POST['saleActivityID']): "";

	$suspended = (isset($_POST['Suspended']) && !empty($_POST['Suspended'])) ? Utility::clean_string($_POST['Suspended']) : "";
	$activityStatus = (isset($_POST['activityStatus']) && !empty($_POST['activityStatus'])) ? Utility::clean_string($_POST['activityStatus']) : "";


	var_dump($suspended);

	var_dump($details);

	if ($saleActivityID) {

		$saleActivityDetails= Work::sale_activities(array("saleActivityID"=>$saleActivityID), true, $DBConn);

		if ($saleActivityDetails) {

			var_dump($saleActivityDetails);
			(isset($details['saleID']) && ( (int)$details['saleID'] !==  $saleActivityDetails->saleID)) ? $changes['saleID'] = $details['saleID'] : "";
			(isset($details['activityName']) && ( $details['activityName'] !==  $saleActivityDetails->activityName)) ? $changes['activityName'] = $details['activityName'] : "";
			(isset($details['activityTypeID']) && ( (int)$details['activityTypeID'] !==  $saleActivityDetails->activityTypeID)) ? $changes['activityTypeID'] = $details['activityTypeID'] : "";
			(isset($details['deadlineDate']) && ( $details['deadlineDate'] !==  $saleActivityDetails->deadlineDate)) ? $changes['deadlineDate'] = $details['deadlineDate'] : "";
			(isset($details['startDate']) && ($details['startDate'] !==  $saleActivityDetails->startDate)) ? $changes['startDate'] = $details['startDate'] : "";
			(isset($details['activityCategory']) && ($details['activityCategory'] !==  $saleActivityDetails->activityCategory)) ? $changes['activityCategory'] = $details['activityCategory'] : "";
			(isset($details['activityOwnerID']) && ( (int)$details['activityOwnerID'] !==  $saleActivityDetails->activityOwnerID)) ? $changes['activityOwnerID'] = $details['activityOwnerID'] : "";
			(isset($details['description']) && ($details['description'] !==  $saleActivityDetails->description)) ? $changes['description'] = $details['description'] : "";
			(isset($details['activityStatus']) && ($details['activityStatus'] !==  $saleActivityDetails->activityStatus)) ? $changes['activityStatus'] = $details['activityStatus'] : "";
			(isset($details['closeDate']) && ($details['closeDate'] !==  $saleActivityDetails->closeDate)) ? $changes['closeDate'] = $details['closeDate'] : "";
			($suspended && ($suspended !==  $saleActivityDetails->Suspended)) ? $changes['Suspended'] = $suspended : "";
			($activityStatus && $activityStatus !== $saleActivityDetails->activityStatus) ? $changes['activityStatus'] = $activityStatus : "";

		}

		var_dump($changes);
		if (count($errors)=== 0) {
			if ($changes) {
				if (!$DBConn->update_table("sbsl_sale_activities", $changes, array("saleActivityID"=> $saleActivityID))) {
					$errors[]="<span class't600'> ERROR! </span> Unable to update activity details to the Database";
				}				
			}			
		}
		var_dump($saleActivityDetails);		
	} else {

		(!isset($details['saleID']) || empty($details['saleID'])) ?  $errors[] = "Please submit valid sale for this saleActivity": "";
		(!isset($details['activityName']) ||  empty($details['activityName'])) ?  $errors[] = "please submit valid Activity ": "";
		(!isset($details['activityTypeID']) ||  empty($details['activityTypeID'])) ?  $errors[] = "please submit valid  activity type": "";
		(!isset($details['deadlineDate']) ||  empty($details['deadlineDate'])) ?  $errors[] = "please submit valid sale activity Deadline": "";
		(!isset($details['activityCategory']) ||  empty($details['activityCategory'])) ?  $errors[] = "please submit valid sale activity Category": "";

		if ($details['activityCategory'] === 'duration') {
			(!isset($details['startDate']) ||  empty($details['startDate'])) ?  $errors[] = "please submit valid start date for the activity details": "";
		}
		
		(!isset($details['activityOwnerID']) ||  empty($details['activityOwnerID'])) ?  $errors[] = "Please submit valid activity owner": "";
		(!isset($details['description']) ||  empty($details['description'])) ?  $errors[] = "please submit valid sale  Activity Description": "";
		// (!isset($details['activityStatus']) ||  empty($details['activityStatus'])) ?  $errors[] = "please submit valid sale  activity Status": "";
		
 		if (count($errors) === 0) {
			if ($details) {
				if (!$DBConn->insert_data("sbsl_sale_activities", $details)) {
					$errors[]="<span class't600'> ERROR! </span> Unable to save activity details to the Database";
				} else {
					$saleActivityID = $DBConn->lastInsertID();
				}
			}
		}
	}
} else {
	$errors[] = 'You need to log in as a valid user to add new sales case.';
} 
	
if (isset($_SESSION['returnURL']) && $_SESSION['returnURL'] !== '') {
	$returnURL =Utility::clean_string($_SESSION['returnURL']);
} else {
	$returnURL= 's=user&ss=timesheets&p=project_details&PID='.$projectID;
}

var_dump($errors);
 if (count($errors) == 0) {
	 $DBConn->commit();
	 $messages = array(array('Text'=>'The Sale Activity was successfully updated.', 'Type'=>'success'));
 } else {
	 $DBConn->rollback();
	 $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
 }
 $_SESSION['FlashMessages'] = serialize($messages);
 header("location:{$base}html/?{$returnURL}");
?>
