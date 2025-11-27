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
   $timelogID = (isset($_POST['timelogID']) && !empty($_POST['timelogID'])) ? Utility::clean_string($_POST['timelogID']) : "";
   $activityID = (isset($_POST['activityID']) && !empty($_POST['activityID'])) ? Utility::clean_string($_POST['activityID']) : "";
   $orgDataID = (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ? Utility::clean_string($_POST['orgDataID']) : "";
   $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ? Utility::clean_string($_POST['entityID']) : "";
   $activityCategoryID = (isset($_POST['activityCategoryID']) && !empty($_POST['activityCategoryID'])) ? Utility::clean_string($_POST['activityCategoryID']) : "";
   $activityTypeID = (isset($_POST['activityTypeID']) && !empty($_POST['activityTypeID'])) ? Utility::clean_string($_POST['activityTypeID']) : "";
   $activityName = (isset($_POST['activityName']) && !empty($_POST['activityName'])) ? Utility::clean_string($_POST['activityName']) : "";
   $activityDate = (isset($_POST['activityDate']) && !empty($_POST['activityDate']) && (preg_match($config['ISODateFormat'], Utility::clean_string($_POST['activityDate'])))) ? Utility::clean_string($_POST['activityDate']) : "";
   $taskDuration = (isset($_POST['taskDuration']) && !empty($_POST['taskDuration']) && (preg_match($config['TimeFormatMini'], Utility::clean_string($_POST['taskDuration'])))) ? Utility::clean_string($_POST['taskDuration']) : "";
   $durationType = (isset($_POST['durationType']) && !empty($_POST['durationType'])) ? Utility::clean_string($_POST['durationType']) : "";
   $activityDescription = (isset($_POST['activityDescription']) && !empty($_POST['activityDescription'])) ? $_POST['activityDescription'] : "";
   $workTypeID = (isset($_POST['workTypeID']) && !empty($_POST['workTypeID'])) ? Utility::clean_string($_POST['workTypeID']) : "";
   $activityOwnerID = (isset($_POST['activityOwnerID']) && !empty($_POST['activityOwnerID'])) ? Utility::clean_string($_POST['activityOwnerID']) : $userDetails->ID;
   $businessUnitID = (isset($_POST['businessUnitID']) && !empty($_POST['businessUnitID'])) ? Utility::clean_string($_POST['businessUnitID']) : "";
   $activitySegment = (isset($_POST['activitySegment']) && !empty($_POST['activitySegment'])) ? Utility::clean_string($_POST['activitySegment']) : "";
   $clientID = (isset($_POST['clientID']) && !empty($_POST['clientID'])) ? Utility::clean_string($_POST['clientID']) : "";
   $projectID = (isset($_POST['projectID']) && !empty($_POST['projectID'])) ? Utility::clean_string($_POST['projectID']) : "";
   $salesCaseID = (isset($_POST['salesCaseID']) && !empty($_POST['salesCaseID'])) ? Utility::clean_string($_POST['salesCaseID']) : "";

   if(!$activityID) {
      $orgDataID ? $details['orgDataID'] = Utility::clean_string($orgDataID) : $errors[] = "Please submit valid orgDataID";
      $entityID ? $details['entityID'] = Utility::clean_string($entityID) : $errors[] = "Please submit valid entityID";
      $activityCategoryID ? $details['activityCategoryID'] = Utility::clean_string($activityCategoryID) : $errors[] = "Please submit valid activity Category";
      $activityTypeID ? $details['activityTypeID'] = Utility::clean_string($activityTypeID) : "";
      $activitySegment ? $details['activitySegment'] = Utility::clean_string($activitySegment) : "";
      $activityName ? $details['activityName'] = Utility::clean_string($activityName) : $errors[] = "Please submit valid activity Name";
      $activityDate ? $details['activityDate'] = Utility::clean_string($activityDate) : $errors[] = "Please submit valid activity Date";
      $durationType ? $details['durationType'] = Utility::clean_string($durationType) : $errors[] = "Please submit valid duration Type";
      $taskDuration ? $details['taskDuration'] = Utility::clean_string($taskDuration) : $errors[] = "Please submit valid task Duration";
      $activityDescription ? $details['activityDescription'] = Utility::clean_string($activityDescription) : $errors[] = "Please submit valid activity Description";
      $workTypeID ? $details['workTypeID'] = Utility::clean_string($workTypeID) : "";
      $activityOwnerID ? $details['activityOwnerID'] = Utility::clean_string($activityOwnerID) : $errors[] = "Please submit valid activity Owner";
      $businessUnitID ? $details['businessUnitID'] = Utility::clean_string($businessUnitID) : "";
      $clientID ? $details['clientID'] = Utility::clean_string($clientID) : "";
      $projectID ? $details['projectID'] = Utility::clean_string($projectID) : "";
      $salesCaseID ? $details['salesCaseID'] = Utility::clean_string($salesCaseID) : "";

      if(!$errors){
         if($details){
            $activityArray = array(
               "orgDataID" => $details['orgDataID'],
               "entityID" => $details['entityID'],
               "activityCategoryID" => $details['activityCategoryID'],
               "activityTypeID" => isset($details['activityTypeID']) ? $details['activityTypeID'] : null,
               "activitySegment" => isset($details['activitySegment']) ? $details['activitySegment'] : null,
               "activityName" => $details['activityName'],
               "activityDate" => $details['activityDate'],
               "durationType" => $details['durationType'],
               "assignedByID"=> $userDetails->ID,
               "activityDescription" => $details['activityDescription'],
               "activityOwnerID" => $details['activityOwnerID'],
               "clientID" => isset($details['clientID']) ? $details['clientID'] : null,
               "projectID" => $projectID ? $details['projectID']  : null,
               "salesCaseID" => $salesCaseID ? $details['salesCaseID'] : null,
               'activityCompleted'=>'Y',
               'LastUpdate' => $config['currentDateTimeFormated'],
               'LastUpdateByID'=> $userDetails->ID
            );

            if(!$DBConn->insert_data("tija_activities", $activityArray)) {
               $errors[] = "Error saving activity details to the database";
            } else {
               $activityID = $DBConn->lastInsertId();
               $success = "Successfully added new activity to the database";
               // add activity hours to work log table
               $workLogArray = array(
                  'taskDate'=> $details['activityDate'],
                  'taskDuration'=> $details['taskDuration'],
                  "employeeID" => $details['activityOwnerID'],
                  "taskActivityID" => $activityID,
                  "workTypeID" => isset($details['workTypeID']) ? $details['workTypeID'] : null,
                  "dailyComplete" => 'Y',
                  "taskType"=> 'activity',
                  "taskNarrative" => $details['activityDescription'],
                  "workSegmentID" => "3",
                  'projectID' => $projectID ? $details['projectID'] : null,
                  'clientID'=> isset($details['clientID']) ? $details['clientID'] : null,


               );
               if($workLogArray) {
                  if(!$DBConn->insert_data("tija_tasks_time_logs", $workLogArray)) {
                     $errors[] = "Error saving activity hours to the database";
                  } else {
                     $success .= " and activity hours added to work log";
                  }
               } else {
                  $errors[] = "Error preparing work log details";
               }


            }
         }

      }



   } else {
      $activityDetails = Schedule::tija_activity(array("activityID"=> $activityID), true, $DBConn);
      var_dump($activityDetails);
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