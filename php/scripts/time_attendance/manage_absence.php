<?php
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
$success = "";
if ($isValidUser) {
	var_dump($_POST);
$userID = (isset($_POST['userID']) && !empty($_POST['userID'])) ? Utility::clean_string($_POST['userID']) : "";
$absenceID = (isset($_POST['absenceID']) && !empty($_POST['absenceID'])) ? Utility::clean_string($_POST['absenceID']) : "";
$absenceDate = (isset($_POST['absenceDate']) && !empty($_POST['absenceDate']) && (preg_match($config['ISODateFormat'], Utility::clean_string($_POST['absenceDate'])))) ? Utility::clean_string($_POST['absenceDate']) : "";
$absenceName = (isset($_POST['absenceName']) && !empty($_POST['absenceName'])) ? Utility::clean_string($_POST['absenceName']) : "";
$absenceTypeID = (isset($_POST['absenceTypeID']) && !empty($_POST['absenceTypeID'])) ? Utility::clean_string($_POST['absenceTypeID']) : "";
$projectID = (isset($_POST['projectID']) && !empty($_POST['projectID'])) && is_array($_POST['projectID']) ? $_POST['projectID'] : "";
$startTime = (isset($_POST['startTime']) && !empty($_POST['startTime']) && (preg_match($config['TimeFormatMini'], Utility::clean_string($_POST['startTime'])))) ? Utility::clean_string($_POST['startTime']) : "";
$endTime = (isset($_POST['endTime']) && !empty($_POST['endTime']) && (preg_match($config['TimeFormatMini'], Utility::clean_string($_POST['endTime'])))) ? Utility::clean_string($_POST['endTime']) : "";
$allDay = (isset($_POST['allDay']) && !empty($_POST['allDay'])) ? Utility::clean_string($_POST['allDay']) : "";
$absenceDescription = (isset($_POST['absenceDescription']) && !empty($_POST['absenceDescription'])) ? $_POST['absenceDescription']: "";
var_dump((preg_match($config['TimeFormatMini'], $_POST['startTime'])));
if( $startTime && $endTime) {
	$start = DateTime::createFromFormat('H:i', $startTime);
	$end = DateTime::createFromFormat('H:i', $endTime);
	if ($end <= $start) {
		$errors[] = "End Time must be later than Start Time";
	}
	// get time difference between start and end time
	$interval = $start->diff($end);
	$hours = $interval->h;
	$minutes = $interval->i;
	if ($hours == 0 && $minutes == 0) {
		$errors[] = "Start Time and End Time cannot be the same";
	} else {
		var_dump($interval);

		echo "<br> Time difference: {$hours} hours and {$minutes} minutes<br>";
		$absenceHrsMins = "{$hours}:{$minutes}:00";
		// $absenceHrs = 
	}
}
if($projectID && is_array($projectID)) {
	$projectID = implode(',', $projectID);
}

if ($absenceID) {
	$absenceDetails = Work::absence(array("absenceID"=> $absenceID), true, $DBConn);
	$absenceDate && ((int)$absenceDate !== $absenceDetails->absenceDate) ? $changes['absenceDate'] = $absenceDate : "";
	$projectID && ((int)$projectID !== $absenceDetails->projectID) ? $changes['projectID'] = $projectID : "";
	$absenceName && ($absenceName !== $absenceDetails->absenceName) ? $changes['absenceName'] = $absenceName : "";
	$absenceTypeID && ($absenceTypeID !== $absenceDetails->absenceTypeID) ? $changes['absenceTypeID'] = $absenceTypeID : "";
	$startTime && ($startTime !== $absenceDetails->startTime) ? $changes['startTime'] = $startTime : "";
	$endTime && ($endTime !== $absenceDetails->endTime) ? $changes['endTime'] = $endTime : "";
	$allDay && ($allDay !== $absenceDetails->allDay) ? $changes['allDay'] = $allDay : "";
	$absenceDescription && ($absenceDescription !== $absenceDetails->absenceDescription) ? $changes['absenceDescription'] = $absenceDescription : "";
	$absenceHrsMins && ($absenceHrsMins !== $absenceDetails->absenceHrs) ? $changes['absenceHrs'] = $absenceHrsMins : "";
	if (count($errors) === 0) {
		if ($changes) {
			$changes['LastUpdate'] = $config['currentDateTimeFormated'];
			if (!$DBConn->update_table("tija_absence_data", $changes, array("absenceID"=>$absenceID))) {
				$errors[]= "ERROR updating absence details in the database";
			}			
		}		
	}	
} else {
	$absenceDate ? $details['absenceDate'] = Utility::clean_string($absenceDate) : $errors[] = "Please submit valid absence Date";
	$absenceName ? $details['absenceName'] = Utility::clean_string($absenceName) : $errors[] = "Please submit valid absence Neme";
	$absenceTypeID ? $details['absenceTypeID'] = Utility::clean_string($absenceTypeID) : $errors[] = "Please submit valid absence Type";
	$projectID ? $details['projectID'] = Utility::clean_string($projectID) : $errors[] = "Please submit valid project the absence will affect";
	$startTime ? $details['startTime'] = Utility::clean_string($startTime) : $errors[] = "Please submit valid absence startTime";
	$endTime ? $details['endTime'] = Utility::clean_string($endTime) : $errors[] = "Please submit valid absence endTime";
	$allDay ? $details['allday'] = Utility::clean_string($allday) : 'N';
	$userID ? $details['userID'] = Utility::clean_string($userID) : $errors[] = "Please submit valid user";
	$absenceDescription ? $details['absenceDescription'] = $absenceDescription : $errors[] = "Please submit valid absence notes";
	$absenceHrsMins ? $details['absenceHrs'] = $absenceHrsMins : $errors[] = "Please submit valid absence hours";
	var_dump($details);

	if (count($errors) === 0) {
		if ($details) {
			if (!$DBConn->insert_data("tija_absence_data", $details)) {
				$errors[] = "Error saving absence details to the database";
			} else {
				$success = "Successfully updated the absence details to the database";
			}			
		}		
	}
}

} else {
	$errors[] = 'You need to log in as a valid user to add new sales case.';
}

var_dump($errors);
$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
var_dump($returnURL);
 if (count($errors) == 0) {
	 $DBConn->commit();
	 $messages = array(array('Text'=>$success, 'Type'=>'success'));
 } else {
	 $DBConn->rollback();
	 $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
 }
 $_SESSION['FlashMessages'] = serialize($messages);
 header("location:{$base}html/?{$returnURL}");
?>