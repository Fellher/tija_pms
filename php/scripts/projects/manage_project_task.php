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

	$projectTaskID = (isset($_POST['projectTaskID']) && !empty($_POST['projectTaskID'])) ? Utility::clean_string($_POST['projectTaskID']) : "";	
	$projectID = (isset($_POST['projectID']) && !empty($_POST['projectID'])) ? Utility::clean_string($_POST['projectID']) : "";
	$projectManagerID =(isset($_POST['projectManagerID']) && !empty($_POST['projectManagerID'])) ?  Utility::clean_string($_POST['projectManagerID']) : "";
	$category = (isset($_POST['category']) && !empty($_POST['category']))  ? Utility::clean_string($_POST['category']) : "";
	$clientID =(isset($_POST['clientID']) && !empty($_POST['clientID'])) ?  Utility::clean_string($_POST['clientID']) : "";
	// Phase details
	$projectPhaseName = (isset($_POST['projectPhaseName']) && !empty($_POST['projectPhaseName'])) ?  Utility::clean_string($_POST['projectPhaseName']) : "";
	$projectPhaseID = (isset($_POST['projectPhaseID']) && !empty($_POST['projectPhaseID'])) ? utility::clean_string($_POST['projectPhaseID']): "";
	$phaseWorkHrs = (isset($_POST['phaseWorkHrs']) && !empty($_POST['phaseWorkHrs'])) ? Utility::clean_string($_POST["phaseWorkHrs"]) : "";
	$phaseWeighting = (isset($_POST['phaseWeighting']) && !empty($_POST['phaseWeighting'])) ? utility::clean_string($_POST['phaseWeighting']) : "";

	$projectTaskTypeID = (isset($_POST['projectTaskTypeID']) && !empty($_POST['projectTaskTypeID'])) ? utility::clean_string($_POST['projectTaskTypeID']): "";

	$taskStart = (isset($_POST['taskStart']) && !empty($_POST['taskStart'])) ? Utility::clean_string($_POST['taskStart']) : "";
	$taskDeadline = (isset($_POST['taskDeadline']) && !empty($_POST['taskDeadline'])) ? Utility::clean_string($_POST['taskDeadline']) : "";
	

	$projectTaskCode = (isset($_POST['projectTaskCode']) && !empty($_POST['projectTaskCode'])) ? utility::clean_string($_POST['projectTaskCode']): "";
	$projectTaskName = (isset($_POST['projectTaskName']) && !empty($_POST['projectTaskName'])) ? utility::clean_string($_POST['projectTaskName']): "";
	$taskDateRange = (isset($_POST['taskDateRange']) && !empty($_POST['taskDateRange'])) ? Utility::clean_string($_POST['taskDateRange']) : "";
	$taskDescription = (isSet($_POST['taskDescription']) && !empty($_POST['taskDescription'])) ? $_POST['taskDescription'] : "";
	$taskEmployeeAssignmentArr = (isset($_POST['teamMemberIDs']) && !empty($_POST['teamMemberIDs'])  && is_array($_POST['teamMemberIDs'])) ? $_POST['teamMemberIDs'] : "";
	$progress = (isset($_POST['progress']) && !empty($_POST['progress'])) ? Utility::clean_string($_POST['progress']): "";
	$status = (isset($_POST['status']) && !empty($_POST['status'])) ? Utility::clean_string($_POST['status']) : "";
 	$hoursAllocated =(isset($_POST['hoursAllocated']) && !empty($_POST['hoursAllocated'])) ? Utility::clean_string($_POST['hoursAllocated']) : "";
 	$taskWeighting = (isset($_POST['taskWeighting']) && !empty($_POST['taskWeighting'])) ? Utility::clean_string($_POST['taskWeighting']) :"";
	// add phase if Phase Id is not available

	$taskStatusID = (isset($_POST['taskStatusID']) && !empty($_POST['taskStatusID'])) ? Utility::clean_string($_POST['taskStatusID']) : "";
	$taskDuration = (isset($_POST['taskDuration']) && !empty($_POST['taskDuration'])) ? Utility::clean_string($_POST['taskDuration']) : "";
	$workTypeID = (isset($_POST['workTypeID']) && !empty($_POST['workTypeID'])) ? Utility::clean_string($_POST['workTypeID']) : "";
	$taskNarrative = (isset($_POST['taskNarrative']) && !empty($_POST['taskNarrative'])) ? Utility::clean_string($_POST['taskNarrative']) : "";
	$taskDate = (isset($_POST['taskDate']) && !empty($_POST['taskDate'])) ? Utility::clean_string($_POST['taskDate']) : "";



	var_dump($projectTaskCode);
	if (!$projectTaskID) {
		if (!isset($projectPhaseID) || empty($projectPhaseID)) {
			$phaseDetails = array();
			(isset($projectPhaseName) && !empty($projectPhaseName)) ? $phaseDetails['projectPhaseName'] = $projectPhaseName  : $errors[] = "Please submit valid phase name";
			(isset($phaseWorkHrs) && !empty($phaseWorkHrs)) ? $phaseDetails['phaseWorkHrs'] = $phaseWorkHrs : $errors[] = "Please submit valid phase work hours";
			(isset($projectID) &&  !empty($projectID)) ? $phaseDetails['projectID']  = $projectID : $errors[] = "Please submit valid project";
			(isset($phaseWeighting) && !empty($phaseWeighting)) ? $phaseDetails['phaseWeighting'] = $phaseWeighting : "";
			(isset($billingMilestone) && !empty($billingMilestone)) ? $phaseDetails['billingMilestone'] = $billingMilestone : "";
			(isset($taskStart) && !empty($taskStart)) ? $phaseDetails['phaseStartDate'] = $taskStart : "";

         var_dump($phaseDetails);

			if (count($errors) === 0) {
				if ($phaseDetails) {
					if (!$DBConn->insert_data("tija_project_phases", $phaseDetails)) {
						$errors[] = "<span class't600'> ERROR !</span> Failed to save pahse details to the database";
					} else {
						$projectPhaseID = $DBConn->lastInsertID();
					}				
				}			
			}
		}
	}
	

	if ($taskDateRange) {
		$dateRangeArr = explode(" to ", $taskDateRange);
		echo preg_match($config['ISODateFormat'], Utility::clean_string($dateRangeArr[0]));

		echo $taskStart = ($dateRangeArr[0] && preg_match($config['ISODateFormat'], Utility::clean_string($dateRangeArr[0]))) ?   Utility::clean_string($dateRangeArr[0]) : $errors[] = "Please submit valid task Start Date";
		echo "<br />";
		echo $taskDeadline =($dateRangeArr[1] && preg_match($config['ISODateFormat'], Utility::clean_string($dateRangeArr[1]))) ?   Utility::clean_string($dateRangeArr[1]) : $errors[] = "Please submit valid task end Date"; 

	} elseif (isset($_POST['taskDeadline']) && !empty($_POST['taskDeadline'])) {
		if(preg_match($config['ISODateFormat'], Utility::clean_string($_POST['taskDeadline']))) {
			$taskDeadline = Utility::clean_string($_POST['taskDeadline']);
		} else {
			$errors[]="Wrong date format for Task Deadline! Please submit date in valid forat (YYYY-MM-DD)";
		}
	}

	$taskDetails = array();

	if ($projectTaskID) {
		$projectDetails = Projects::projects_tasks(array("projectTaskID"=> $projectTaskID), true, $DBConn);
		var_dump($projectDetails);
		$projectTaskDetails = $projectDetails;
		($projectTaskCode && ($projectDetails->projectTaskCode !== $projectTaskCode)) ? $changes['projectTaskCode'] =  $projectTaskCode : "";
		($projectTaskName && ($projectDetails->projectTaskName !== $projectTaskName)) ? $changes['projectTaskName'] =  $projectTaskName : "";
		($projectID && ($projectDetails->projectID !== (int)$projectID)) ? $changes['projectID'] =  $projectID : "";
		($taskStart && ($projectDetails->taskStart !== $taskStart)) ? $changes['taskStart'] =  $taskStart : "";
		($taskDeadline && ($projectDetails->taskDeadline !== $taskDeadline)) ? $changes['taskDeadline'] =  $taskDeadline : "";
		($projectPhaseID && ($projectDetails->projectPhaseID !== (int)$projectPhaseID)) ? $changes['projectPhaseID'] =  $projectPhaseID : "";
		($progress && ($projectDetails->progress !== $progress)) ? $changes['progress'] =  $progress : "";
		($status && ($projectDetails->status !== $status)) ? $changes['status'] =  $status : "";
		($taskDescription && ($projectDetails->taskDescription !== $taskDescription)) ? $changes['taskDescription'] =  $taskDescription : "";
		($hoursAllocated && ($projectDetails->hoursAllocated !== floatval($hoursAllocated))) ? $changes['hoursAllocated'] =  $hoursAllocated : "";
		($taskWeighting && ($projectDetails->taskWeighting !== $taskWeighting)) ? $changes['taskWeighting'] =  $taskWeighting : "";
		($projectTaskTypeID && ($projectDetails->projectTaskTypeID !== (int)$projectTaskTypeID)) ? $changes['projectTaskTypeID'] =  $projectTaskTypeID : "";
		($taskStatusID && ($projectDetails->taskStatusID !== (int)$taskStatusID)) ? $changes['taskStatusID'] =  $taskStatusID : "";
		

			var_dump($changes);
			if (count($errors) === 0) {
				if ($changes) {
					$changes['DateLastUpdated'] = $config['currentDateTimeFormated'];
					// $DBConn->update_table("sbsl_project_tasks", $changes, array("projectTaskID"=> $projectTaskID));
					if (!$DBConn->update_table("tija_project_tasks", $changes, array("projectTaskID"=> $projectTaskID))) {
						$errors[]= "<span class=''> ERROR!</span> Failed to update project task";
					}
				}
			}

		// add work log hours

		var_dump($taskDuration);
		if($taskDuration) {
			// Default to today's date if not provided
			$workLog = array(
				"projectTaskID"=> $projectTaskID,
				"projectID"=> $projectID,
				"projectPhaseID"=> $projectPhaseID,				
				"employeeID"=> $userDetails->ID,				
				"taskNarrative"=> $taskNarrative,
			);

			var_dump($taskDuration);
			$taskDate ? $workLog['taskDate'] = $taskDate : $workLog['taskDate'] = today('Y-m-d'); 
			$workTypeID ? $workLog['workTypeID'] = $workTypeID : $errors[] = "Please submit valid work type ID";
			$taskDuration ? $workLog['taskDuration'] = $taskDuration : $errors[] = "Please submit valid work hours in format HH:MM";
			if (!$DBConn->insert_data("tija_tasks_time_logs", $workLog)) {
				$errors[] = "<span class='t600'>ERROR!</span> Failed to save work log hours to the database";
			}
		} 
	} else {

		var_dump($taskEmployeeAssignmentArr);
		$projectTaskCode ? $taskDetails['projectTaskCode'] = $projectTaskCode : $errors[] = "Please submit valid project task code";
		$projectTaskName ?  $taskDetails['projectTaskName'] = $projectTaskName : $errors[] = "Please submit valid project task Name";
		$projectID ? $taskDetails['projectID'] = $projectID : $errors[] = "Please submit valid project ID";
		$taskStart ? $taskDetails['taskStart'] = $taskStart : $errors[] = "Please submit valid task start date";
		$taskDeadline ? $taskDetails['taskDeadline'] = $taskDeadline : $errors[] = "Please submit task Deadline";
		$projectPhaseID ? $taskDetails['projectPhaseID'] = $projectPhaseID : $errors[] = "Please submit valid project the task belongs to";
		$progress ?  $taskDetails['progress'] = $progress : "";
		$status ? $taskDetails['status'] = $status: "";
		$taskDescription ? $taskDetails['taskDescription'] = $taskDescription: "";
		$hoursAllocated ? $taskDetails['hoursAllocated'] = $hoursAllocated : "";
		$taskWeighting ? $taskDetails['taskWeighting'] = $taskWeighting: "";

		var_dump($taskDetails);
		if (count($errors) === 0) {
			if ($taskDetails) {
				if (!$DBConn->insert_data("tija_project_tasks", $taskDetails)) {
					$errors[] = "<span class='t600'> ERROR!</span> Unable to save project task details";					
				} else { 
					$projectTaskID = $DBConn->lastInsertID();
					$projectTaskDetails = Projects::projects_tasks(array("projectTaskID"=> $projectTaskID), true, $DBConn);
					echo "<h3> Inserted Project Task Details </h3>";
					var_dump($projectTaskDetails);
					if ($taskEmployeeAssignmentArr) {
						foreach ($taskEmployeeAssignmentArr as $key => $employeeID) {
							$assignment = array("userID"=> $employeeID, 
												"projectID"=> $projectID,
												"projectTaskID"=> $projectTaskID,
											);

							var_dump($assignment);
							if ($status) {
								$assignment['taskStatus'] = $status;								
							}
							if (count($errors) === 0) {
								if (!$DBConn->insert_data("tija_assigned_project_tasks", $assignment)) {
									$errors[] = "<span class='t600'>ERROR!</span> Failed to save user assgnment to the database";									
								} else {
									$assignmentID = $DBConn->lastInsertID();
									$taskAssignmentDetails = Projects::task_user_assignment(array("assignmentTaskID"=> $assignmentID), true, $DBConn);
									var_dump($taskAssignmentDetails);
									$success = "User assigned to the task successfully";
									// create and add notification to user for assigned tasks
									$employeeDetails = Employee::employees(array("ID"=> $taskAssignmentDetails->userID), true, $DBConn);
									$assignorDetails = Employee::employees(array("ID"=> $userDetails->ID), true, $DBConn);
									$notificationArr = array(
										"employeeID" => $taskAssignmentDetails->userID,
										"approverID" => $userDetails->ID,
										"segmentType"=> "projects",
										"segmentID" => $taskAssignmentDetails->projectID,
										"notificationNotes" => "<p>You have been assigned to the task <strong>{$taskAssignmentDetails->projectTaskName}</strong> in the project <strong>{$taskAssignmentDetails->projectName}</strong> by {$assignorDetails->employeeNameWithInitials}.</p>
																<p><a href='{$base}html/?s=user&ss=work&p=project&projectID={$taskAssignmentDetails->projectID}&projectTaskID={$taskAssignmentDetails->projectTaskID}'>View Project Task</a></p>",
										"notificationType" => "project_task_assignment",
										"notificationText" => "You have been assigned to the task <strong>{$taskAssignmentDetails->projectTaskName}</strong> in the project <strong>{$taskAssignmentDetails->projectName}</strong> by {$assignorDetails->employeeNameWithInitials}.",
										"notificationStatus" => "unread",
										"originatorUserID" => $userDetails->ID,
										"targetUserID" => $taskAssignmentDetails->userID,

									);
									if($notificationArr) {
										if(!$DBConn->insert_data('tija_notifications', $notificationArr)) {
											$errors[] = 'Failed to send notification to the new project task assignment';
										} else {
											$success .= ' and notification sent successfully';
										}

									}

								}								
							}
							// Check if the user is already in the project Team
							// $checkUser = Projects::task_user_assignment(array("projectID"=> $projectID, "userID"=> $employeeID, "projectTaskID"=> $projectTaskID), true, $DBConn);
							$isInTeam = Projects::project_team_full(array('projectID' => $projectID, 'userID'=> $employeeID), true, $DBConn);

							if(!$isInTeam) {
								$teamMember = array(
													"userID"=> $employeeID, 
													"projectID"=> $projectID,
													"projectTeamRoleID"=> 1,
													
												);
								if (!$DBConn->insert_data("tija_project_team", $teamMember)) {
									$errors[] = "<span class='t600'>ERROR!</span> Failed to save user to the project team";									
								} 
							} 
														
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