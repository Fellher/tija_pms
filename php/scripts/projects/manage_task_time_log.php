<?php
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();

if ( $isValidUser) {

$posts= $_POST;
var_dump($posts);

if (isset($posts['projectTaskID']) && !empty($posts['projectTaskID'])) {
	$projectTaskID = Utility::clean_string($posts['projectTaskID']);
	$taskDetails= Work::project_tasks(array('projectTaskID'=>$projectTaskID), true, $DBConn);
		var_dump($taskDetails);


	if (isset($posts['projectTaskID']) && !empty($posts['projectTaskID'])) {
		$details['projectTaskID'] = Utility::clean_string($posts['projectTaskID']);
	} else {
		$errors[]= "Please submit valid project task";
	}

	if (isset($posts['projectID']) && !empty($posts['projectID'])) {
		$details['projectID'] = Utility::clean_string($posts['projectID']);

	} else {
		$details['projectID'] =$taskDetails->projectID;
	}

	$projectDetails = Work::projects (array('projectID'=>$details['projectID']), true, $DBConn);
		$details['clientID'] = $projectDetails->clientID;

	// Check if this is a recurring project and associate with billing cycle
	if (isset($projectDetails->isRecurring) && $projectDetails->isRecurring === 'Y') {
		// Get billing cycle for the task date
		if (isset($details['taskDate']) && !empty($details['taskDate'])) {
			$taskDate = $details['taskDate'];

			// Check if billingCycleID is explicitly provided
			if (isset($posts['billingCycleID']) && !empty($posts['billingCycleID'])) {
				$billingCycleID = intval($posts['billingCycleID']);
				// Validate that the cycle belongs to this project
				$cycle = Projects::get_billing_cycles(['billingCycleID' => $billingCycleID, 'projectID' => $details['projectID']], true, $DBConn);
				if ($cycle) {
					$details['billingCycleID'] = $billingCycleID;
				}
			} else {
				// Auto-associate with active billing cycle for the task date
				$activeCycles = Projects::get_billing_cycles(
					[
						'projectID' => $details['projectID'],
						'Suspended' => 'N'
					],
					false,
					$DBConn
				);

				if ($activeCycles && is_array($activeCycles)) {
					foreach ($activeCycles as $cycle) {
						if ($taskDate >= $cycle->cycleStartDate && $taskDate <= $cycle->cycleEndDate) {
							$details['billingCycleID'] = $cycle->billingCycleID;
							break;
						}
					}
				}
			}
		}
	}

	if (isset($posts['workTypeID']) && !empty($posts['workTypeID'])) {
		$details['workTypeID'] = utility::clean_string($posts['workTypeID']);
	} else {
		$errors[]= "Please submit valid work Type";
	}

	if (isset($posts['phaseID']) && !empty($posts['phaseID'])) {
		$details['projectPhaseID'] = utility::clean_string($posts['phaseID']);
	} else {
		$details['projectPhaseID']  = $taskDetails->projectPhaseID;
	}



	if (isset($posts['taskDate']) && !empty($posts['taskDate'])) {
		if (preg_match($config['ISODateFormat'],$posts['taskDate'])) {
			$details['taskDate'] =$posts['taskDate'];
		} else {
			$errors[]= "Wrong date format";
		}
	} else {
		$errors[]="Please submit Valid task Date";
	}

	if (isset($posts['hours']) && !empty($posts['hours'])) {
		$hours = Utility::clean_string($posts['hours']);
	} else {
		$hours = '0';
	}

	if (isset($posts['minutes']) && !empty($posts['minutes'])) {
		$minutes = Utility::clean_string($posts['minutes']);

		$minutesDigits= ($minutes <10) ? "{$minutes}0" : $minutes;
		$minutes = $minutesDigits;
	} else {
		$minutes='0';
	}

	if ($hours == '0' && $minutes== '0') {
		$errors[]= "please submit valid time";
	} else {
		$details['taskDuration'] = "{$hours}:{$minutes}";
	}

	if (isset($_POST['taskStatusID']) && !empty($_POST['taskStatusID'])) {
		$taskStatusID = Utility::clean_string($_POST['taskStatusID']);

		if ($taskDetails->status != $taskStatusID) {
			if (count($errors)=== 0) {
				if (!$DBConn->update_table('sbsl_project_tasks', array('status'=>$taskStatusID), array('projectTaskID'=>$details['projectTaskID']))) {
					$errors[] = "Unable to update task status";
				}
			}
		}

	}

	if (isset($_POST['userID']) && !empty($_POST['userID'])) {
		$details['userID'] = Utility::clean_string($_POST['userID']);
	} else {
		$details['userID'] = $userDetails->ID;
	}

	if (isset($posts['taskNarrative']) && !empty($posts['taskNarrative'])) {
		$details['taskNarrative']= $posts['taskNarrative'];
	} else{
		$errors[]="Please submit Valid task Narative";
	}

var_dump($details);

if (count($errors) ===0) {
	if ($details) {
		// Use correct table name (check if it's tija_tasks_time_logs or sbsl_tasks_time_logs)
		$timeLogTable = 'tija_tasks_time_logs'; // Update based on your actual table name

		if (!$DBConn->insert_data($timeLogTable, $details)) {
			$errors[]="unable to save the timelog to the database";
		} else {
			$timelogID= $DBConn->lastInsertID();

			// Update billing cycle hours logged if billingCycleID is set
			if (isset($details['billingCycleID']) && !empty($details['billingCycleID'])) {
				$cycle = Projects::get_billing_cycles(['billingCycleID' => $details['billingCycleID']], true, $DBConn);
				if ($cycle) {
					// Calculate hours from duration
					$hours = 0;
					if (isset($details['taskDuration'])) {
						$durationParts = explode(':', $details['taskDuration']);
						$hours = floatval($durationParts[0]) + (floatval($durationParts[1]) / 60);
					}

					$newHoursLogged = floatval($cycle->hoursLogged) + $hours;
					Projects::update_billing_cycle_status(
						$details['billingCycleID'],
						$cycle->status,
						['hoursLogged' => round($newHoursLogged, 2)],
						$DBConn
					);
				}
			}
		}
	}
}


if (isset($posts['expenseTypeID']) && !empty($posts['expenseTypeID'])) {
	$expenseTypeID= Utility::clean_string($posts['expenseTypeID']);
	if (isset($posts['amount']) && !empty($posts['amount'])) {
		$amount=Utility::clean_string($posts['amount']);
		$details['expenses'] = $amount;


		$expenseArray=array('expenseTypeID'=> $expenseTypeID,  'expenseAmount'=> $amount, 'expenseStatus'=> 'pending', 'timelogID'=> $timelogID, "projectTaskID"=>$details['projectTaskID'] );
		if (count($errors) === 0) {
			if ($expenseArray) {
				if (!$DBConn->insert_data('sbsl_task_expenses', $expenseArray)) {
					$errors[]="Unable to save expense";
				}
			}
		}
	} else {
		$errors[]= "please submit valid amount for the expense";
	}
}



	$fileAttachments= $_FILES['fileAttachments'];
	// var_dump($fileAttachments);

	$n= count($fileAttachments['name']);
	echo "<h1> nymber of  files = {$n}</h1>";
	for ($i=0; $i <$n ; $i++) {
		$filesArray[]= array( 'name'=> $fileAttachments['name'][$i], 'type'=> $fileAttachments['type'][$i], 'tmp_name'=> $fileAttachments['tmp_name'][$i], 'error'=>$fileAttachments['error'][$i], 'size'=>$fileAttachments['error'][$i]);
	}

	// var_dump($filesArray);

	if ($filesArray) {
		foreach ($filesArray as $key => $file) {
			var_dump($file);

			$uploadedFile = new UploadedFile($file['name'], $file['tmp_name'], $file['type'], $file['size'], $file['error']);
			$uploadError = $uploadedFile->upload_error($config['MaxUploadedFileSize'], $config['DataDir'], $config['ValidFileTypes']);
			if (!$uploadError) {
				var_dump($uploadedFile);

				$fileFullUrl= $uploadedFile->newFileName;
				$pathParts= Utility::file_path_split($fileFullUrl);
				echo "<h3> File Parts </h3>";
				var_dump($pathParts);
				$fileDetails= array('fileURL'=> $pathParts[count($pathParts)-1], 'timelogID'=> $timelogID, 'userID'=> $details['userID'], 'fileType'=>  $uploadedFile->fileType );
				if (count($errors) === 0) {
					if ($fileDetails) {
						if (!$DBConn->insert_data('sbsl_task_files', $fileDetails)) {
							$errors[]= "unable to save file {$file['name'] }";
						}
					}
				}
			} else {
				var_dump($uploadError);
			}
		}
	}
	var_dump($details);
}




} else {
	Alert::warning("You need to be logged in as a valid ");
}
if (isset($_SESSION['returnURL']) && $_SESSION['returnURL'] !== '') {
	$returnURL =Utility::clean_string($_SESSION['returnURL']);
	// unset($_SESSION['returnURL']);
} else {
	$returnURL= 's=user&p=work_hour_entry&PID='.$projectID;

}

var_dump($errors);

 if (count($errors) == 0) {
	 $DBConn->commit();
	 $messages = array(array('Text'=>'Your time log was successfully updated', 'Type'=>'success'));
 } else {
	 $DBConn->rollback();
	 $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
 }
 $_SESSION['FlashMessages'] = serialize($messages);
//  header("location:{$base}html/?{$returnURL}");
?>