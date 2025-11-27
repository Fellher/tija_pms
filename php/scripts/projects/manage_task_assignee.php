
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

	if(isset($_POST['suspendTaskAssignmentID']) && !empty($_POST['suspendTaskAssignmentID']) && is_array($_POST['suspendTaskAssignmentID'])){
		$suspendTaskAssignmentID = $_POST['suspendTaskAssignmentID'];
		foreach ($suspendTaskAssignmentID as $key => $assignmentTaskID) {
			if (Projects::assigned_task(array("assignmentTaskID"=>$assignmentTaskID), true, $DBConn)) {
				if (!$DBConn->delete_row("tija_assigned_project_tasks", array("assignmentTaskID"=>$assignmentTaskID))) {
					$errors[]="Error deleting user {$assignmentTaskID}";
				} else{
					echo "<p> successfuly deleted user assignment {$assignmentTaskID}</p>";
				}
			}
		}
	}

	$projectTaskID = (isset($_POST['projectTaskID']) && !empty($_POST['projectTaskID'])) ? Utility::clean_string($_POST['projectTaskID']) : "";


	if ($projectTaskID) {
		$taskDetails = Projects::projects_tasks(array("projectTaskID"=> $projectTaskID), true, $DBConn);
		var_dump($taskDetails);

		$newMemberArray = (isset($_POST['newMemberUserID']) && is_array($_POST['newMemberUserID']) && !empty($_POST['newMemberUserID']) ) ? $_POST['newMemberUserID'] : "";
		var_dump($newMemberArray);
		if ($newMemberArray) {
			foreach ($newMemberArray as $key => $userID) {
				if($userID) {
					var_dump($newMemberArray);
				// Check if user already Assigned to task


					$assignment = Projects::assigned_task(array("userID"=> $userID, "projectTaskID"=> $projectTaskID), false, $DBConn);
					if (!$assignment) {
						$userName = Core::user_name($userID, $DBConn);
						if (!$DBConn->insert_data("tija_assigned_project_tasks", array("userID"=> $userID, 'projectTaskID'=> $projectTaskID, "projectID"=> $taskDetails->projectID))) {
							$errors[] = "Error saving {$userName} to the database";
						} else {
							$success .= "<p class='pb-0 mb-1'> successfuly saved {$userName} to the database </p>";
						}
						// code...
					}

				}
				
				// code...
			}
			// code...
		}
		// code...
	}








} else {
	$errors[] = 'You need to log in as a valid administrator to do that.';
}

$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');


var_dump($returnURL);
var_dump($errors);
 if (count($errors) == 0) {
	 $DBConn->commit();
	 $messages = array(array('Text'=>$success, 'Type'=>'success'));
 } else {
	 $DBConn->rollback();
	 $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
 }
 $_SESSION['FlashMessages'] = serialize($messages);
 var_dump($_SESSION['FlashMessages']);
 header("location:{$base}html/{$returnURL}");
?>

