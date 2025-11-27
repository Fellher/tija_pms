<?php
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';

$DBConn->begin();
$errors = array();
$details=array();
$assignTableDetails=array();
$taskStart="";
$changes= array();
if ($isValidUser) {

	var_dump($_POST);
   echo "<h5>POST</h5>";
   $projectTeamMemberID = (isset($_POST['projectTeamMemberID'])) ?  Utility::sanitize_input($_POST['projectTeamMemberID']) : "";

   $projectID = (isset($_POST['projectID'])) ?  Utility::sanitize_input($_POST['projectID']) : "";
   $userID = (isset($_POST['userID'])) ?  Utility::sanitize_input($_POST['userID']) : "";
   $projectTeamRoleID = (isset($_POST['projectTeamRoleID'])) ?  Utility::sanitize_input($_POST['projectTeamRoleID']) : "";

   if($projectTeamRoleID === 'addRole') {
      $projectTeamRoleName = (isset($_POST['projectTeamRoleName'])) ?  Utility::sanitize_input($_POST['projectTeamRoleName']) : "";
      $projectTeamRoleDescription = (isset($_POST['projectTeamRoleDescription'])) ?  Utility::sanitize_rich_text_input($_POST['projectTeamRoleDescription']) : "";

      if($projectTeamRoleName === '') $errors[] = "Please enter a valid project team role name";
      if($projectTeamRoleDescription === '') $errors[] = "Please enter a valid project team role description";
      
      if(count($errors) === 0) {
         $projectTeamRoleDetails = array(
            'projectTeamRoleName' => $projectTeamRoleName,
            'projectTeamRoleDescription' => $projectTeamRoleDescription
         );

         var_dump($projectTeamRoleDetails);
         if($projectTeamRoleDetails){
            if(!$DBConn->insert_data("tija_project_team_roles", $projectTeamRoleDetails)) {
               $errors[] = "Failed to add project team role";
            } else {
               $projectTeamRoleID = $DBConn->lastInsertID();
            }
         }
      }
   }
// var_dump
   if(!$projectTeamMemberID) {
      
      $projectID ? $details['projectID'] = $projectID : $errors[] = "Please select a valid project";
      $userID ? $details['userID'] = $userID : $errors[] = "Please select a valid user";
      $projectTeamRoleID ? $details['projectTeamRoleID'] = $projectTeamRoleID : $errors[] = "Please select a valid project team role";
   var_dump($details);
      if(!$errors) {
         if(!$DBConn->insert_data("tija_project_team", $details)) {
            $errors[] = "Failed to add project team member";
         } else {
            $projectTeamMemberID = $DBConn->lastInsertID();
            $success = "Project team member added successfully";
            $projectTeamMemberDetails = Projects::project_team_full(array('projectTeamMemberID'=>$projectTeamMemberID), true, $DBConn);
            var_dump($projectTeamMemberDetails);
            $employeeDetails = Employee::employees(array('ID'=>$projectTeamMemberDetails->userID), true, $DBConn);
            $assignorDetails = Employee::employees(array('ID'=>$userDetails->ID), true, $DBConn);

            $notificationArr = array(
               'employeeID' => $projectTeamMemberDetails->userID,
               'approverID' => $userDetails->ID,
               'segmentType'=> "projects",
               "segmentID" => $projectTeamMemberDetails->projectID,
               "notificationNotes" => "<p>You have been added to the project <strong>{$projectTeamMemberDetails->projectName}</strong> as a <strong>{$projectTeamMemberDetails->projectTeamRoleName}</strong> by {$assignorDetails->employeeNameWithInitials}.</p>
                                       <p><a href='{$base}html/?s=user&ss=projects&p=project&pid={$projectTeamMemberDetails->projectID}'>View Project</a></p>",
               'notificationType' => "project_team_add",
               'notificationText' => "You have been added to the project <strong>{$projectTeamMemberDetails->projectName}</strong> as a <strong>{$projectTeamMemberDetails->projectTeamRoleName}</strong> by {$assignorDetails->employeeNameWithInitials}.",
               'notificationStatus' => 'unread',
               'originatorUserID' => $userDetails->ID,
               'targetUserID' => $projectTeamMemberDetails->userID,

            );
            if($notificationArr) {
               if(!$DBConn->insert_data('tija_notifications', $notificationArr)) {
                  $errors[] = 'Failed to send notification to the new project team member';
               } else {
                  $success .= ' and notification sent successfully';
               }
               
            }
         }
      }
   } else {
      $projectTeamMemberDetails = Projects::project_team(array('projectTeamMemberID'=>$projectTeamMemberID), true, $DBConn);
      var_dump($projectTeamMemberDetails);
      if($projectTeamMemberDetails) {
         $projectID && $projectTeamMemberDetails->projectID != $projectID ? $changes['projectID'] = $projectID : '';
         $userID && $projectTeamMemberDetails->userID != $userID ? $changes['userID'] = $userID : '';
         $projectTeamRoleID && $projectTeamMemberDetails->projectTeamRoleID != $projectTeamRoleID ? $changes['projectTeamRoleID'] = $projectTeamRoleID : '';
         if(count($changes) > 0) {
            $changes['LastUpdateByID'] = $userDetails->ID;
            $changes['LastUpdate'] = date('Y-m-d H:i:s');

            if(!$DBConn->update_table("tija_project_team", $changes, array('projectTeamMemberID'=>$projectTeamMemberID))) {
               $errors[] = "Failed to update project team member";
            } else {
               $success = "Project team member updated successfully";
               $projectTeamMemberDetails = Projects::project_team_full(array('projectTeamMemberID'=>$projectTeamMemberID), true, $DBConn);
               $employeeDetails = Employee::employees(array('ID'=>$projectTeamMemberDetails->userID), true, $DBConn);
               $assignorDetails = Employee::employees(array('ID'=>$userDetails->ID), true, $DBConn);
               $notificationArr = array(
                  'employeeID' => $projectTeamMemberDetails->userID,
                  'approverID' => $userDetails->ID,
                  'segmentType'=> "projects",
                  "segmentID" => $projectTeamMemberDetails->projectID,
                  "notificationNotes" => "<p>Your project team details for the project <strong>{$projectTeamMemberDetails->projectName}</strong> have been updated by {$assignorDetails->employeeNameWithInitials}.</p>
                                          <p><a href='{$base}html/?s=user&ss=projects&p=project&pid={$projectTeamMemberDetails->projectID}'>View Project</a></p>",
                  'notificationType' => "project_team_update",
                  'notificationText' => "Your project team details for the project <strong>{$projectTeamMemberDetails->projectName}</strong> have been updated by {$assignorDetails->employeeNameWithInitials}.",
                  'notificationStatus' => 'unread',
                  'originatorUserID' => $userDetails->ID,
                  'targetUserID' => $projectTeamMemberDetails->userID,

               );
               if($notificationArr) {
                  if(!$DBConn->insert_data('tija_notifications', $notificationArr)) {
                     $errors[] = 'Failed to send notification to the project team member';
                  } else {
                     $success .= ' and notification sent successfully';
                  }
                  
               }
            }
         }
      }
   }
   
	echo "<h5> Errors </h5>";
	var_dump($errors);


} else {
	Alert::danger('You need to be logged in as a valid user to edit the User personal infomation');
}

 $returnURL= Utility::returnURL($_SESSION['returnURL'], "s=user&ss=work&p=project");
if (count($errors) === 0) {
   $DBConn->commit();
   $messages = array(array('Text'=>'The updates were successfully Saved.', 'Type'=>'success'));
   
  
} else {
   $DBConn->rollback();
   $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
   $_SESSION['posts'] = serialize($_POST);  
   // $returnURL= Utility::returnURL($_SESSION['returnURL'], "s={$s}&ss={$ss}&p={$p}");
	
}

var_dump($returnURL);
$_SESSION['FlashMessages'] = serialize($messages);
header("location:{$base}html/{$returnURL}");
var_dump($errors); ?>