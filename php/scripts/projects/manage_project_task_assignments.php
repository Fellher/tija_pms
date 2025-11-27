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

   $assignmentTaskID = (isset($_POST['assigmentTaskID']) && !empty($_POST['assigmentTaskID'])) ? Utility::clean_string($_POST['assigmentTaskID']) : "";
   $projectID = (isset($_POST['projectID']) && !empty($_POST['projectID'])) ? Utility::clean_string($_POST['projectID']) : "";
   $projectTaskID = (isset($_POST['projectTaskID']) && !empty($_POST['projectTaskID'])) ? Utility::clean_string($_POST['projectTaskID']) : "";
   $userID = (isset($_POST['assigneeID']) && !empty($_POST['assigneeID'])) ? Utility::clean_string($_POST['assigneeID']) : "";
   $assignmentStatus = (isset($_POST['assignmentStatus']) && !empty($_POST['assignmentStatus'])) ? Utility::clean_string($_POST['assignmentStatus']) : "";
   $suspended = (isset($_POST['suspended']) && !empty($_POST['suspended'])) ? Utility::clean_string($_POST['suspended']) : "N";
   

   if($assignmentTaskID){
      $assignmentTaskDetails = Projects::task_user_assignment(array("assignmentTaskID"=>$assignmentTaskID), true, $DBConn);

      if(!$assignmentTaskDetails){
         $errors[] = "Assignment task details not found";
      }

      var_dump($assignmentTaskDetails);
      $projectID && $projectID !==$assignmentTaskDetails->projectID ? $changes['projectID'] = $projectID : null;
      $projectTaskID && $projectTaskID !==$assignmentTaskDetails->projectTaskID ? $changes['projectTaskID'] = $projectTaskID : null;
      $userID && $userID !==$assignmentTaskDetails->userID ? $changes['userID'] = $userID : null;
      $assignmentStatus && $assignmentStatus !==$assignmentTaskDetails->assignmentStatus ? $changes['assignmentStatus'] = $assignmentStatus : null;
      $suspended && $suspended !==$assignmentTaskDetails->suspended ? $changes['suspended'] = $suspended : null;

      if(!$errors){
         if($changes){
         
            if(!$DBConn->update_table('tija_assigned_project_tasks', $changes, array('assignmentTaskID'=>$assignmentTaskID))){
               $errors[] = "Error updating assignment task details";
            }
         }
      }

   } else {

      var_dump($projectID);
      var_dump($projectTaskID);
      var_dump($userID);
      var_dump($assignmentStatus);
      var_dump($suspended);
      $projectID ? $details['projectID'] = $projectID : $error[] = "Project ID is required";
      $projectTaskID ? $details['projectTaskID'] = $projectTaskID : $error[] = "Project Task ID is required";
      $userID ? $details['userID'] = $userID : $error[] = "Assignee ID is required";
      $assignmentStatus ? $details['assignmentStatus'] = $assignmentStatus : "assigned";
      $suspended ? $details['suspended'] = $suspended : "N";
      var_dump($errors);
      if(!$errors){
         if($details){         
            
            var_dump($details);
            if(!$DBConn->insert_data('tija_assigned_project_tasks', $details)){
               $errors[] = "Error updating assignment task details";
            }
         }
      }
   }
   

   
} else {
	$errors[] = 'You need to log in as a valid user to add new sales case.';
}

var_dump($errors);
$returnURL= Utility::returnURL($_SESSION['returnURL'], "s=user&ss=work&p=project");
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