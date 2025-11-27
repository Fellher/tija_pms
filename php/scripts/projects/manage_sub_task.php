<?php
session_start();
$base = "../../../";
set_include_path($base);

include 'php/includes.php';



$errors = array();
$DBConn->begin();
$details=array();
$changes= array();

if ($isValidUser) {
	$projectTaskID = (isset($_POST['projectTaskID']) && !empty($_POST['projectTaskID']) ) ? Utility::clean_string($_POST['projectTaskID']) : "";
	$subTaskName = (isset($_POST['subTaskName']) && !empty($_POST['subTaskName']) ) ? Utility::clean_string($_POST['subTaskName']) : "";
	$assignee = (isset($_POST['assignee']) && !empty($_POST['assignee']) ) ? $_POST['assignee'] : ((isset($_POST['userID']) && !empty($_POST['userID'])) ? Utility::clean_string($_POST['userID']) : "");
	$userID = (isset($_POST['userID']) && !empty($_POST['userID'])) ? Utility::clean_string($_POST['userID']) : "";
	$subtaskDueDate = (isset($_POST['subtaskDueDate']) && !empty($_POST['subtaskDueDate']) && preg_match($config['ISODateFormat'], Utility::clean_string($_POST['subtaskDueDate']))) ? Utility::clean_string($_POST['subtaskDueDate']) : "";
	$subTaskDescription = (isset($_POST['subTaskDescription']) && !empty($_POST['subTaskDescription']) ) ? Utility::clean_string($_POST['subTaskDescription']) : "";
	$subTaskAllocatedWorkHours = (isset($_POST['subTaskAllocatedWorkHours']) && !empty($_POST['subTaskAllocatedWorkHours']) ) ? Utility::time_to_sec(Utility::clean_string($_POST['subTaskAllocatedWorkHours'])) : "";
	$subTaskID = (isset($_POST['subtaskID']) && !empty($_POST['subtaskID']) ) ? Utility::clean_string($_POST['subtaskID']) : "";
var_dump($assignee);
	$assignedUserToTask = Projects::task_user_assignment(['userID'=> $assignee, 'projectTaskID'=> $projectTaskID, 'Suspended'=>'N'], true, $DBConn);
	echo "<h5> Assigned to roject task </h5>";
	var_dump($assignedUserToTask);

	if ($subTaskID) {
		$subTaskDetails = Projects::project_subtasks(array("subtaskID"=> $subTaskID), true, $DBConn);

		($projectTaskID && ($subTaskDetails->projectTaskID !== (int)$projectTaskID)) ? $changes['projectTaskID'] = $projectTaskID : "";
		($subTaskName && ($subTaskDetails->subTaskName !== (int)$subTaskName)) ? $changes['subTaskName'] = $subTaskName : "";
		($assignee && ($subTaskDetails->assignee !== (int)$assignee)) ? $changes['assignee'] = $assignee : "";

		($subtaskDueDate && ($subTaskDetails->subtaskDueDate !== (int)$subtaskDueDate)) ? $changes['subtaskDueDate'] = $subtaskDueDate : "";
		($subTaskDescription && ($subTaskDetails->subTaskDescription !== (int)$subTaskDescription)) ? $changes['subTaskDescription'] = $subTaskDescription : "";
		($subTaskAllocatedWorkHours && ($subTaskDetails->subTaskAllocatedWorkHours !== (int)$subTaskAllocatedWorkHours)) ? $changes['subTaskAllocatedWorkHours'] = $subTaskAllocatedWorkHours : "";

		var_dump($changes);
		if (count($errors) === 0) {
			if ($changes) {
				$changes['LastUpdate'] = $config['currentDateTimeFormated'];
				if (!$DBConn->update_table("tija_subtasks", $changes, array("subTaskID"=>$subTaskID))) {
					$errors[]= "<span class='t600'> ERROR!</span> Unable to update project subtask to the database";	
				}				
			}		
		}
		
	} else {
		$projectTaskID ? $details['projectTaskID'] = Utility::clean_string($projectTaskID) : $errors[]= "Please submit valid project task";
		$subTaskName ? $details['subTaskName'] = Utility::clean_string($subTaskName) : $errors[]= "Please submit valid project subtask name";
		$assignee ? $details['assignee'] = Utility::clean_string($assignee) : $errors[]= "Please submit valid project subtask assignee";
		$subtaskDueDate ? $details['subtaskDueDate'] = Utility::clean_string($subtaskDueDate) : $errors[]= "Please submit valid project subtask deadline date";
		$subTaskDescription ? $details['subTaskDescription'] = $subTaskDescription : $errors[]= "Please submit valid project subtask description";
		$subTaskAllocatedWorkHours ? $details['subTaskAllocatedWorkHours'] = Utility::clean_string($subTaskAllocatedWorkHours) : $errors[]= "Please submit allocated workhours";
		var_dump($details);

		if (count($errors) === 0) {
			if ($details) {
				if (!$DBConn->insert_data("tija_subtasks", $details)) {
					$errors[]= "<span class='t600'> ERROR!</span> Unable to save project subtask to the database";					
				} else {
					$subtaskID = $DBConn->lastInsertID();
					if(!$assignedUserToTask) {
						$projectTaskDetails= Projects::project_tasks(['projectTaskID'=>$projectTaskID], true, $DBConn);
						$userAssigmentArr= array('userID'=> $assignee, 'projectID'=> $projectTaskDetails->projectID, 'assignmentStatus'=>"assigned" );
						if(!$DBConn->insert_data('tija_assigned_project_tasks', $userAssigmentArr)){
							$errors[]="<span class='t600'> ERROR!</span> Unable to save assignment to the database";
						}
					}
				}				
			}			
		}
	}
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