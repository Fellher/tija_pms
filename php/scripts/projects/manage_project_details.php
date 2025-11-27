
<?php
session_start();
$base = "../../../";
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$changes=array();
if ( $isValidUser) {

	var_dump($_POST);

	$projectID = (isset($_POST['projectID']) && !empty($_POST['projectID']))? Utility::clean_string($_POST['projectID']) : "";

	(isset($_POST['clientID']) && !empty($_POST['clientID'])) ? $details['clientID'] = Utility::clean_string($_POST['clientID']) : "";
	(isset($_POST['instanceID']) && !empty($_POST['instanceID'])) ? $details['instanceID'] = Utility::clean_string($_POST['instanceID']) : "";
	(isset($_POST['projectCode']) && !empty($_POST['projectCode'])) ? $details['projectCode'] = Utility::clean_string($_POST['projectCode']) : "";
	(isset($_POST['projectName']) && !empty($_POST['projectName'])) ? $details['projectName'] = Utility::clean_string($_POST['projectName']) : "";
	(isset($_POST['projectStart']) && !empty($_POST['projectStart']) && preg_match($config['ISODateFormat'], Utility::clean_string($_POST['projectStart'])))? $details['projectStart'] = Utility::clean_string($_POST['projectStart']) : "";
	(isset($_POST['projectClose']) && !empty($_POST['projectClose']) && preg_match($config['ISODateFormat'], Utility::clean_string($_POST['projectClose'])))? $details['projectClose'] = Utility::clean_string($_POST['projectClose']) : "";
	 $businessUnitID =(isset($_POST['businessUnitID']) && !empty($_POST['businessUnitID'])) ? Utility::clean_string($_POST['businessUnitID']) : "";
	if ($businessUnitID && $businessUnitID=== "newbusinessUnit") {
		if (isset($_POST['newbusinessUnit']) && !empty($_POST['newbusinessUnit'])) {
			$newbusinessUnit= Utility::clean_string($_POST['newbusinessUnit']);
			if (count($errors) === 0) {
				if ($newbusinessUnit) {
					if (!$DBConn->insert_data("sbsl_business_units", array("businessUnitName"=> $newbusinessUnit, "instanceID"=> $details['instanceID']))) {
						$errors[]="<span class't600'> ERROR!</span> Failed to update business Unit to the database"; 
						
					} else {
						$details['businessUnitID'] = $DBConn->lastInsertID();
					}
				}
			}
		}
		
	} else {
		$details['businessUnitID'] = $businessUnitID;
	}

	var_dump($details);

	(isset($_POST['projectValue']) && !empty($_POST['projectValue'])) ? $details['projectValue'] = Utility::clean_string($_POST['projectValue']) : "";
	(isset($_POST['orderStatus']) && !empty($_POST['orderStatus'])) ? $details['orderStatus'] = Utility::clean_string($_POST['orderStatus']) : "";
	(isset($_POST['orderDate']) && !empty($_POST['orderDate']) && preg_match($config['ISODateFormat'], Utility::clean_string($_POST['orderDate'])))? $details['orderDate'] = Utility::clean_string($_POST['orderDate']) : "";


	if ($projectID) {
		$projectDetails = Work::projects(array('projectID'=> $projectID), true, $DBConn);

		(isset($details['projectCode']) && ($details['projectCode'] !== $projectDetails->projectCode)) ? $changes['projectCode'] = $details['projectCode'] : "";
		(isset($details['projectName']) && ($details['projectName'] !== $projectDetails->projectName)) ? $changes['projectName'] = $details['projectName'] : "";
		(isset($details['clientID']) && ((int)$details['clientID'] !== (int)$projectDetails->clientID)) ? $changes['clientID'] = $details['clientID'] : "";
		(isset($details['projectStart']) && ($details['projectStart'] !== $projectDetails->projectStart)) ? $changes['projectStart'] = $details['projectStart'] : "";
		(isset($details['projectClose']) && ($details['projectClose'] !== $projectDetails->projectClose)) ? $changes['projectClose'] = $details['projectClose'] : "";
		(isset($details['projectDeadline']) && ($details['projectDeadline'] !== $projectDetails->projectDeadline)) ? $changes['projectDeadline'] = $details['projectDeadline'] : "";
		(isset($details['projectOwnerID']) && ($details['projectOwnerID'] !== $projectDetails->projectOwnerID)) ? $changes['projectOwnerID'] = $details['projectOwnerID'] : "";
		(isset($details['billable']) && ($details['billable'] !== $projectDetails->billable)) ? $changes['billable'] = $details['billable'] : "";
		(isset($details['projectBillableRate']) && ($details['projectBillableRate'] !== $projectDetails->projectBillableRate)) ? $changes['projectBillableRate'] = $details['projectBillableRate'] : "";
		(isset($details['billableRateValue']) && ($details['billableRateValue'] !== $projectDetails->billableRateValue)) ? $changes['billableRateValue'] = $details['billableRateValue'] : "";
		(isset($details['roundingoff']) && ($details['roundingoff'] !== $projectDetails->roundingoff)) ? $changes['roundingoff'] = $details['roundingoff'] : "";
		(isset($details['roundingInterval']) && ($details['roundingInterval'] !== $projectDetails->roundingInterval)) ? $changes['roundingInterval'] = $details['roundingInterval'] : "";
		(!empty($details['businessUnitID']) &&  ((int)$details['businessUnitID'] !== (int)$projectDetails->businessUnitID)) ? $changes['businessUnitID'] = $details['businessUnitID'] : "";
		(isset($details['projectValue']) && ($details['projectValue'] !== $projectDetails->projectValue)) ? $changes['projectValue'] = $details['projectValue'] : "";
		(isset($details['approval']) && ($details['approval'] !== $projectDetails->approval)) ? $changes['approval'] = $details['approval'] : "";
		(isset($details['status']) && ($details['status'] !== $projectDetails->status)) ? $changes['status'] = $details['status'] : "";
		(isset($details['allocatedWorkHours']) && ($details['allocatedWorkHours'] !== $projectDetails->allocatedWorkHours)) ? $changes['allocatedWorkHours'] = $details['allocatedWorkHours'] : "";
		(isset($details['orderDate']) && ($details['orderDate'] !== $projectDetails->orderDate)) ? $changes['orderDate'] = $details['orderDate'] : "";
		
		var_dump($changes);
		if (count($errors)===0) {
			if ($changes) {
		
				$changes['LastUpdate']= $config['currentDateTimeFormated'];
				$changes['DateLastUpdated']= $config['currentDateTimeFormated'];
				if (!$DBConn->update_table("sbsl_projects", $changes, array("projectID"=> $projectID))) {
					$errors[]="<span class't600'> ERROR!</span> Failed to update project to the database";
				}			
			}			
		}
	} else{
		if (count($errors) === 0) {
			if($details){
				if (!$DBConn->insert_data("sbsl_projects", $details)) {
					$errors[]="<span class't600'> ERROR!</span> Failed to update project to the database";
				} else {
					$projectID= $DBConn->lastInsertID();				
				}
			}
		}
	}


} else {
	$errors[] = 'You need to log in as a valid administrator to do that.';
}

var_dump($errors);


	$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&p=home');



if (count($errors) == 0) {
	$DBConn->commit();
	$messages = array(array('Text'=>'The role Factor was successfully Added.', 'Type'=>'success'));
	$returnURL= "s=user&ss=work&p=project&id={$projectID}";
} else {
 	$DBConn->rollback();
 	$messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
}
 $_SESSION['FlashMessages'] = serialize($messages);
 header("location:{$base}html/?{$returnURL}");
?>