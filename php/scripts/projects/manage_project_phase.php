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
   $projectPhaseID = isset($_POST['projectPhaseID']) ? Utility::sanitize_input($_POST['projectPhaseID']) : null;
   $projectID = isset($_POST['projectID']) && $_POST['projectID'] !== '' ? Utility::sanitize_input($_POST['projectID']) : null;
   $projectPhaseName = isset($_POST['projectPhaseName']) && $_POST['projectPhaseName'] !== '' ? Utility::sanitize_input($_POST['projectPhaseName']) : null;
   $phaseStartDate = isset($_POST['phaseStartDate']) && $_POST['phaseStartDate'] !== '' && preg_match($config['ISODateFormat'],$_POST['phaseStartDate']) ? Utility::sanitize_input($_POST['phaseStartDate']) : null;
   $phaseEndDate = isset($_POST['phaseEndDate']) && $_POST['phaseEndDate'] !== '' && preg_match($config['ISODateFormat'],$_POST['phaseEndDate']) ? Utility::sanitize_input($_POST['phaseEndDate']) : null;
   $phaseWorkHrs = isset($_POST['phaseWorkHrs']) && $_POST['phaseWorkHrs'] !== '' ? Utility::sanitize_input($_POST['phaseWorkHrs']) : null;
   $phaseWeighting = isset($_POST['phaseWeighting']) && $_POST['phaseWeighting'] !== '' ? Utility::sanitize_input($_POST['phaseWeighting']) : null;
   $billingMilestone = isset($_POST['billingMilestone']) ? Utility::sanitize_input($_POST['billingMilestone']) : "N";
   $billingCycleID = isset($_POST['billingCycleID']) && $_POST['billingCycleID'] !== '' ? Utility::sanitize_input($_POST['billingCycleID']) : null;

   $returnURL = Utility::returnURL($_SESSION['returnURL'], "?s=user&ss=projects&p=project&pid={$projectID}");

   if(!$projectPhaseID){
      $projectID ? $details['projectID'] = $projectID : $errors[] = "Project ID is required";
      $projectPhaseName ? $details['projectPhaseName'] = $projectPhaseName : $errors[] = "Project phase name is required";
      $phaseStartDate ? $details['phaseStartDate'] = $phaseStartDate : $errors[] = "Phase start date is required";
      $phaseEndDate ? $details['phaseEndDate'] = $phaseEndDate : $errors[] = "Phase end date is required";
      $phaseWorkHrs ? $details['phaseWorkHrs'] = $phaseWorkHrs : $errors[] = "Phase work hours is required";
      $phaseWeighting ? $details['phaseWeighting'] = $phaseWeighting : $errors[] = "Phase weighting is required";
      $billingMilestone ? $details['billingMilestone'] = $billingMilestone : $errors[] = "Billing milestone is required";
      // billingCycleID is optional - only add if provided
      if (isset($billingCycleID) && $billingCycleID !== null && $billingCycleID !== '') {
          $details['billingCycleID'] = $billingCycleID;
      }

      if(!$errors){
         $details['LastUpdate'] = Utility::generateDateTime($DBConn);
         $details['LastUpdatedByID'] = $userDetails->ID;
         var_dump($details);
         if($details){
            if(!$DBConn->insert_data('tija_project_phases', $details)){
               $errors[] = "Error saving phase details to the database";
            } else {
               $success = "Successfully updated the phase details to the database";
               $projectPhaseID = $DBConn->lastInsertId();
               $returnURL .= "&projectPhaseID={$projectPhaseID}";
            }
         }
      }

   } else {
      $phaseDetails = Projects::project_phases_mini(array('projectPhaseID' => $projectPhaseID), true, $DBConn);
      var_dump($phaseDetails);
      if($phaseDetails){
         $projectID  && $projectID !==$phaseDetails->projectID ? $changes['projectID'] = $projectID : null;
         $projectPhaseName && $projectPhaseName !==$phaseDetails->projectPhaseName ? $changes['projectPhaseName'] = $projectPhaseName : null;
         $phaseStartDate && $phaseStartDate !==$phaseDetails->phaseStartDate ? $changes['phaseStartDate'] = $phaseStartDate : null;
         $phaseEndDate && $phaseEndDate !==$phaseDetails->phaseEndDate ? $changes['phaseEndDate'] = $phaseEndDate : null;
         $phaseWorkHrs && $phaseWorkHrs !==$phaseDetails->phaseWorkHrs ? $changes['phaseWorkHrs'] = $phaseWorkHrs : null;
         $phaseWeighting && $phaseWeighting !==$phaseDetails->phaseWeighting ? $changes['phaseWeighting'] = $phaseWeighting : null;
         $billingMilestone && $billingMilestone !==$phaseDetails->billingMilestone ? $changes['billingMilestone'] = $billingMilestone : null;
         // billingCycleID is optional - only update if provided
         if (isset($billingCycleID) && $billingCycleID !== null && $billingCycleID !== '') {
             $changes['billingCycleID'] = $billingCycleID;
         }
         if(!$errors){
            if($changes){
               $changes['LastUpdate'] = Utility::generateDateTime($DBConn);
               $changes['LastUpdatedByID'] = $userDetails->ID;
               if(!$DBConn->update_table('tija_project_phases', $changes, array('projectPhaseID' => $projectPhaseID))){
                  $errors[] = "Error updating phase details to the database";
               } else {
                  $success = "Successfully updated the phase details to the database";
                  $returnURL .= "&projectPhaseID={$projectPhaseID}";
               }
            }
         }

      } else {
         $errors[] = "Phase not found";
      }

   }

} else {
	$errors[] = 'You need to log in as a valid user to add new sales case.';
}

var_dump($errors);

var_dump($returnURL);
 if (count($errors) == 0) {
	 $DBConn->commit();
	 $messages = array(array('Text'=>$success, 'Type'=>'success'));
 } else {
	 $DBConn->rollback();
	 $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
 }
 $_SESSION['FlashMessages'] = serialize($messages);
 header("location:{$base}html/{$returnURL}");
?>