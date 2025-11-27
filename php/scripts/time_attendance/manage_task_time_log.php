<?php
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


$DBConn->begin();
$errors = array();
$details=array();
$changes=array();

/**
 * Sync time log file to tija_project_files table
 * This ensures files uploaded with time logs are also available in project file management
 */
function syncTimeLogFileToProjectFiles($fileDetails, $originalFile, $uploadedFile, $projectID, $projectTaskID, $userID, $DBConn, $config) {
	try {
		// Get timelog details to ensure we have projectID
		$timelogDetails = TimeAttendance::project_tasks_time_logs(array('timelogID' => $fileDetails['timelogID']), true, $DBConn);

		if (!$timelogDetails || !$timelogDetails->projectID) {
			// No project associated, skip sync
			return false;
		}

		$projectID = $timelogDetails->projectID;
		$projectTaskID = $timelogDetails->projectTaskID ?? $projectTaskID;

		// Get file information - ensure all details are captured
		$fileURL = $fileDetails['fileURL'];

		// Get file extension from uploaded file or original file
		$fileExtension = !empty($uploadedFile->fileType) ? strtolower($uploadedFile->fileType) : strtolower(pathinfo($originalFile['name'], PATHINFO_EXTENSION));

		// Get original filename (preserve user's original filename)
		$fileOriginalName = !empty($originalFile['name']) ? $originalFile['name'] : basename($fileURL);

		// Get file size - try multiple sources
		$fileSize = 0;
		if (isset($uploadedFile->fileSize) && $uploadedFile->fileSize > 0) {
			$fileSize = $uploadedFile->fileSize;
		} elseif (isset($originalFile['size']) && $originalFile['size'] > 0) {
			$fileSize = $originalFile['size'];
		} else {
			// Try to get from actual file on disk
			$fullPath = $config['DataDir'] . $fileURL;
			if (file_exists($fullPath)) {
				$fileSize = filesize($fullPath);
			}
		}

		// Get MIME type - try multiple sources
		$fileMimeType = '';
		if (!empty($originalFile['type'])) {
			$fileMimeType = $originalFile['type'];
		} else {
			// Determine MIME type based on extension if not available
			$mimeTypes = array(
				'pdf' => 'application/pdf',
				'doc' => 'application/msword',
				'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				'xls' => 'application/vnd.ms-excel',
				'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
				'jpg' => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'png' => 'image/png',
				'gif' => 'image/gif',
				'webp' => 'image/webp',
				'csv' => 'text/csv',
				'txt' => 'text/plain',
				'zip' => 'application/zip',
				'rar' => 'application/x-rar-compressed'
			);
			$fileMimeType = isset($mimeTypes[$fileExtension]) ? $mimeTypes[$fileExtension] : 'application/octet-stream';
		}

		// Extract filename from fileURL (stored filename, not original)
		$fileName = basename($fileURL);

		// Ensure fileURL is properly formatted (relative path from DataDir)
		if (strpos($fileURL, $config['DataDir']) === 0) {
			$fileURL = str_replace($config['DataDir'], '', $fileURL);
		}

		// Check if file already exists in project_files (avoid duplicates)
		$existingFile = $DBConn->retrieve_db_table_rows(
			'tija_project_files',
			array('fileID'),
			array('fileURL' => $fileURL, 'projectID' => $projectID, 'Suspended' => 'N')
		);

		if ($existingFile && !empty($existingFile)) {
			// File already synced, skip
			return true;
		}

		// Validate required fields before inserting
		if (empty($fileName) || empty($fileOriginalName) || empty($fileURL) || empty($projectID) || empty($userID)) {
			error_log("Warning: Missing required fields for project file sync. File: {$fileOriginalName}, Project: {$projectID}");
			return false;
		}

		// Prepare data for tija_project_files - ensure all fields are properly set
		$projectFileData = array(
			'projectID' => intval($projectID), // Required: NOT NULL
			'taskID' => !empty($projectTaskID) ? intval($projectTaskID) : null, // Optional: NULL allowed
			'fileName' => $fileName, // Required: NOT NULL - stored filename
			'fileOriginalName' => $fileOriginalName, // Required: NOT NULL - user's original filename
			'fileURL' => $fileURL, // Required: NOT NULL - relative path from DataDir
			'fileType' => !empty($fileExtension) ? strtolower($fileExtension) : '', // Optional but recommended
			'fileSize' => intval($fileSize), // Optional but recommended - size in bytes
			'fileMimeType' => !empty($fileMimeType) ? $fileMimeType : 'application/octet-stream', // Optional but recommended
			'category' => 'time_log', // Optional - Category to identify files from time logs
			'version' => '1.0', // Optional - Default version
			'uploadedBy' => intval($userID), // Required: NOT NULL - user who uploaded
			'description' => 'File uploaded with time log entry', // Optional - description
			'isPublic' => 'N', // Optional - Default 'N'
			'downloadCount' => 0, // Optional - Default 0
			'DateAdded' => 'NOW()', // Optional - Auto timestamp
			'Suspended' => 'N' // Optional - Default 'N'
		);

		// Insert into tija_project_files
		if ($DBConn->insert_data('tija_project_files', $projectFileData)) {
			error_log("Synced time log file to project_files: {$fileOriginalName} (Project: {$projectID})");
			return true;
		} else {
			error_log("Warning: Failed to sync time log file to project_files: {$fileOriginalName}");
			return false;
		}
	} catch (Exception $e) {
		error_log("Error syncing time log file to project_files: " . $e->getMessage());
		return false;
	}
}

if ( $isValidUser) {

$_POST= $_POST;
var_dump($_POST);

$timelogID = isset($_POST['timelogID']) && $_POST['timelogID'] ? Utility::clean_string($_POST['timelogID']) : false;
$taskDate = (isset($_POST['taskDate']) && (preg_match($config['ISODateFormat'],$_POST['taskDate'])) )
	? Utility::clean_string($_POST['taskDate']) : date('Y-m-d');
$employeeID = (isset($_POST['employeeID']) && $_POST['employeeID']) ? Utility::clean_string($_POST['employeeID']) : $userDetails->ID;
$projectID = (isset($_POST['projectID']) && $_POST['projectID']) ? Utility::clean_string($_POST['projectID']) : false;

$projectDetails = Projects::projects_mini (array('projectID'=>$projectID), true, $DBConn);
var_dump($projectDetails);
$clientID = (isset($_POST['clientID']) && $_POST['clientID'])  ? Utility::clean_string($_POST['clientID']) :(isset($projectDetails->clientID) ? $projectDetails->clientID : false);
$projectPhaseID = (isset($_POST['projectPhaseID']) && $_POST['projectPhaseID']) ? Utility::clean_string($_POST['projectPhaseID']) : false;
$projectTaskID = (isset($_POST['projectTaskID']) && $_POST['projectTaskID']) ? Utility::clean_string($_POST['projectTaskID']) : false;
$projectTaskName = (isset($_POST['taskName']) && $_POST['taskName']) ? Utility::clean_string($_POST['taskName']) : false;
$subtaskID = (isset($_POST['subtaskID']) && !empty($_POST['subtaskID'])) ? Utility::clean_string($_POST['subtaskID']) : false;
$workTypeID = (isset($_POST['workTypeID']) && $_POST['workTypeID']) ? Utility::clean_string($_POST['workTypeID']) : false;
$taskNarrative = (isset($_POST['taskNarrative']) && $_POST['taskNarrative']) ? Utility::clean_string($_POST['taskNarrative']) : false;
$startTime = (isset($_POST['startTime']) && $_POST['startTime']) ? Utility::clean_string($_POST['startTime']) : false;
$endTime = (isset($_POST['endTime']) && $_POST['endTime']) ? Utility::clean_string($_POST['endTime']) : false;
$taskDuration = (isset($_POST['taskDuration']) && $_POST['taskDuration']) ? Utility::clean_string($_POST['taskDuration']) : false;
$dailyComplete = (isset($_POST['dailyComplete']) && $_POST['dailyComplete']) ? Utility::clean_string($_POST['dailyComplete']) : false;
$taskStatusID = (isset($_POST['taskStatusID']) && $_POST['taskStatusID']) ? Utility::clean_string($_POST['taskStatusID']) : false;
$taskType = (isset($_POST['taskType']) && $_POST['taskType']) ? Utility::clean_string($_POST['taskType']) : false;
$taskActivityID = (isset($_POST['taskActivityID']) && $_POST['taskActivityID']) ? Utility::clean_string($_POST['taskActivityID']) : false;
$workSegmentID = (isset($_POST['workSegmentID']) && $_POST['workSegmentID']) ? Utility::clean_string($_POST['workSegmentID']) : false;
$recurringInstanceID = (isset($_POST['recurringInstanceID']) && $_POST['recurringInstanceID']) ? Utility::clean_string($_POST['recurringInstanceID']) : false;

IF(!$timelogID){
	$taskDate ? $details['taskDate'] = $taskDate : $errors[]="Please submit valid task date";
	$employeeID ? $details['employeeID'] = $employeeID : $errors[]="Please submit valid employee ID";
	$projectID ? $details['projectID'] = $projectID : "";
	$clientID ? $details['clientID'] = $clientID : $errors[]="Please submit valid client ID";
	$projectPhaseID ? $details['projectPhaseID'] = $projectPhaseID : "";
	$projectTaskID ? $details['projectTaskID'] = $projectTaskID : "";
	if(!$projectTaskID && !$subtaskID) {
		if($projectTaskName){
			$taskDetails= array(
					'projectTaskName'=>$projectTaskName,
					'projectID'=>$projectID,
					'projectPhaseID'=>$projectPhaseID,
					 'taskStart'=> $taskDate,

					'taskStatusID'=> $taskStatusID,
					'projectTaskCode' => Utility::clientCode($projectTaskName)
			);

			var_dump($taskDetails);
			if (count($errors) === 0) {
				if ($taskDetails) {
					if (!$DBConn->insert_data('tija_project_tasks', $taskDetails)) {
						$errors[]="unable to save the project task to the database";
					} else {
						$projectTaskID= $DBConn->lastInsertID();
						$details['projectTaskID'] = $projectTaskID;
					}
				}
			}
		}
	} else {
		if ($projectTaskID) {
			$details['projectTaskID'] = $projectTaskID;
		}
	}
	echo "<h4> Task Duration = {$taskDuration} </h4>";
	$subtaskID ? $details['subtaskID'] = Utility::clean_string($subtaskID) : "";
	$workTypeID ? $details['workTypeID'] = Utility::clean_string($workTypeID) : "";
	$taskNarrative ? $details['taskNarrative'] = $taskNarrative : "";
	$startTime ? $details['startTime'] = $startTime : "";
	$endTime ? $details['endTime'] = $endTime : "";
	$taskDuration ? $details['taskDuration'] = $taskDuration : "";
	$dailyComplete ? $details['dailyComplete'] = $dailyComplete : "";
	$taskStatusID ? $details['taskStatusID'] = $taskStatusID : "";
	$taskType ? $details['taskType'] = $taskType : "";
	$taskActivityID ? $details['taskActivityID'] = $taskActivityID : "";
	$workSegmentID ? $details['workSegmentID'] = $workSegmentID : "";
	$recurringInstanceID ? $details['recurringInstanceID'] = $recurringInstanceID : "";
	echo "<h3>Details</h3>";
	var_dump($details);
	if (count($errors) === 0) {
		if ($details) {
			if (!$DBConn->insert_data('tija_tasks_time_logs', $details)) {
				$errors[]="unable to save the timelog to the database";
			} else {
				$timelogID= $DBConn->lastInsertID();
				echo "<h3> Timelog ID = {$timelogID} </h3>";
				// if the task status is 4 or 5, update the task status
				echo "<h4> Updating task status >>>> </h4>";
				if($taskStatusID == 4 || $taskStatusID == 5) {
					// update the task status
					if (!$DBConn->update_table('tija_project_tasks', array('taskStatusID'=>$taskStatusID, 'status'=>$taskStatusID), array('projectTaskID'=>$details['projectTaskID']))) {
						$errors[] = "Unable to update task status";
					} else {

						$taskDetails = Projects::project_tasks(array('projectTaskID'=>$details['projectTaskID']), true, $DBConn);

						echo "<h4> Successfully updated task status </h4>";
						$employeeDetails = Employee::employees(array('ID'=>$employeeID), true, $DBConn);
						echo "<h5>Employee Details</h5>";
						var_dump($employeeDetails);
						// get the project Details
						$projectDetails = Projects::projects_mini(array('projectID'=>$projectID), true, $DBConn);
						$approverID = $employeeDetails->supervisorID;
						$superviorDetails = Employee::employees(array('ID'=>$approverID), true, $DBConn);
						$statusDetails = TimeAttendance::task_statuses(array('taskStatusID'=>$taskStatusID), true, $DBConn);
						// create notification and input to notification table
						$notificationDetails = array(
							'employeeID' => $employeeID,
							'approverID' =>$approverID ? $approverID : $projectDetails->projectOwnerID,
							'employeeID' => $projectID,
							'segmentType' => 'task',
							'notificationNotes' => $taskNarrative ? $taskNarrative : "No task narrative provided",
							'notificationType' => 'task_status_update',
							'notificationText' => "Your task has been marked as {$statusDetails->taskStatusName} by {$employeeDetails->employeeName} in project {$projectDetails->projectName}",
							'timestamp' => $config['currentDateTimeFormatted'],
							'emailed' =>'Y'
						);
						echo "<h5>Notification Details</h5>";
						var_dump($notificationDetails);
						// insert notification details to the database
						if (!$DBConn->insert_data('tija_notifications', $notificationDetails)) {
							$errors[] = "Unable to create notification for task status update";
						} else {
							require $base .'php/classes/Exception.php';
							require $base .'php/classes/phpmailer.php';
							require $base .'php/classes/smtp.php';
							// send email notification
							$notificationID = $DBConn->lastInsertID();

							$emailBody = "Your task {$taskDetails->projectTaskName} has been marked as {$statusDetails->taskStatusName} by {$employeeDetails->employeeName} in project {$projectDetails->projectName}.";
							// $emailNohtmlBody = "Your task has been marked as {$statusDetails->taskStatusName} by {$employeeDetails->employeeName} in project {$projectDetails->projectName}.";


							$send= true;
							if ($send) {
								$mail = new PHPMailer(true);
								$subject = "Task Status Update: {$statusDetails->taskStatusName} in Project {$projectDetails->projectName}";
								try {
									$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
									$mail->isSMTP();                                            // Send using SMTP
									$mail->Host       = $config['emailHost'];                   // Set the SMTP server to send through
									$mail->SMTPAuth   = true;                                   // Enable SMTP authentication
									$mail->Username   = $config['userName'];                    // SMTP username
									$mail->Password   = $config['emailPWS'];                    // SMTP password
							// Set encryption based on port
							if ($config['emailPort'] == 465) {
								$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;        // SSL encryption for port 465
							} elseif ($config['emailPort'] == 587) {
								$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;     // TLS encryption for port 587
							}
							$mail->Port       = $config['emailPort'];                   // TCP port

									// SSL options for Microsoft 365
									$mail->SMTPOptions = array(
										'ssl' => array(
											'verify_peer' => true,
											'verify_peer_name' => true,
											'allow_self_signed' => true
										)
									);
									$mail->Timeout = 30;                                        // Increase timeout

									$mail->setFrom($config['siteEmail'], $config['siteName']);
									$mail->addAddress($superviorDetails->Email, $superviorDetails->employeeName);     								// Add a recipient
									$mail->addReplyTo($config['siteEmail'], $config['siteName']);
									$mail->addBCC($config['secondaryEmail'], $config['siteName']);
									// Attachments
									//$mail->addAttachment('/var/tmp/file.tar.gz');         		// Add attachments
									//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    		// Optional name

									$mail->isHTML(true);                                 			// Set email format to HTML
									$mail->Subject = $subject;
									$mail->Body    = $emailBody;
									// $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
									//   $mail->AltBody    = $emailNoHtml; 									// optional, comment out and test

									  $mail->send();
								} catch (Exception $e) {
									var_dump($e);
									$errors[]= "Unable to send reset Email. Error. {$mail->ErrorInfo}";

								}
							}


						}
					}
				}
			}
		}
	}
} else {

	$timelogDetails= TimeAttendance::project_tasks_time_logs(array('timelogID'=>$timelogID), true,  $DBConn);
	var_dump($timelogDetails);
	$taskDate && $timelogDetails->taskDate != $taskDate ? $changes['taskDate'] = $taskDate : "";
	$employeeID && $timelogDetails->employeeID != $employeeID ? $changes['employeeID'] = $employeeID : "";
	$clientID && $timelogDetails->clientID != $clientID ? $changes['clientID'] = $clientID : "";
	$projectID && $timelogDetails->projectID != $projectID ? $changes['projectID'] = $projectID : "";
	$projectPhaseID && $timelogDetails->projectPhaseID != $projectPhaseID ? $changes['projectPhaseID'] = $projectPhaseID : "";
	$projectTaskID && $timelogDetails->projectTaskID != $projectTaskID ? $changes['projectTaskID'] = $projectTaskID : "";
	$subtaskID && $timelogDetails->subtaskID != $subtaskID ? $changes['subtaskID'] = $subtaskID : "";
	$workTypeID && $timelogDetails->workTypeID != $workTypeID ? $changes['workTypeID'] = $workTypeID : "";
	$taskNarrative && $timelogDetails->taskNarrative != $taskNarrative ? $changes['taskNarrative'] = $taskNarrative : "";
	$startTime && $timelogDetails->startTime != $startTime ? $changes['startTime'] = $startTime : "";
	$endTime && $timelogDetails->endTime != $endTime ? $changes['endTime'] = $endTime : "";
	$taskDuration && $timelogDetails->taskDuration != $taskDuration ? $changes['taskDuration'] = $taskDuration : "";
	$dailyComplete && $timelogDetails->dailyComplete != $dailyComplete ? $changes['dailyComplete'] = $dailyComplete : "";
	$taskStatusID && $timelogDetails->taskStatusID != $taskStatusID ? $changes['taskStatusID'] = $taskStatusID : "";
	$taskType && $timelogDetails->taskType != $taskType ? $changes['taskType'] = $taskType : "";
	$taskActivityID && $timelogDetails->taskActivityID != $taskActivityID ? $changes['taskActivityID'] = $taskActivityID : "";
	$workSegmentID && $timelogDetails->workSegmentID != $workSegmentID ? $changes['workSegmentID'] = $workSegmentID : "";
	$recurringInstanceID && $timelogDetails->recurringInstanceID != $recurringInstanceID ? $changes['recurringInstanceID'] = $recurringInstanceID : "";


	$changes['LastUpdate'] = $config['currentDateTimeFormatted'];
	var_dump($changes);
	if (count($errors) === 0) {
		if ($changes) {

			if (!$DBConn->update_table('tija_tasks_time_logs', $changes, array('timelogID'=>$timelogID))) {
				$errors[]="unable to save the edited timelog changes";
			} else {
				echo "<h3> Timelog ID = {$timelogID} </h3>";
				// if the task status is 4 or 5, update the task status
				echo "<h4> Updating task status >>>> </h4>";
				if($taskStatusID == 4 || $taskStatusID == 5) {
					// update the task status
					if (!$DBConn->update_table('tija_project_tasks', array('taskStatusID'=>$taskStatusID, 'status'=>$taskStatusID), array('projectTaskID'=>$details['projectTaskID']))) {
						$errors[] = "Unable to update task status";
					} else {

						$taskDetails = Projects::project_tasks(array('projectTaskID'=>$details['projectTaskID']), true, $DBConn);

						echo "<h4> Successfully updated task status </h4>";
						$employeeDetails = Employee::employees(array('ID'=>$employeeID), true, $DBConn);
						echo "<h5>Employee Details</h5>";
						var_dump($employeeDetails);
						// get the project Details
						$projectDetails = Projects::projects_mini(array('projectID'=>$projectID), true, $DBConn);
						$approverID = $employeeDetails->supervisorID;
						$superviorDetails = Employee::employees(array('ID'=>$approverID), true, $DBConn);
						$statusDetails = TimeAttendance::task_statuses(array('taskStatusID'=>$taskStatusID), true, $DBConn);
						// create notification and input to notification table
						$notificationDetails = array(
							'employeeID' => $employeeID,
							'approverID' =>$approverID ? $approverID : $projectDetails->projectOwnerID,
							'employeeID' => $projectID,
							'segmentType' => 'task',
							'notificationNotes' => $taskNarrative ? $taskNarrative : "No task narrative provided",
							'notificationType' => 'task_status_update',
							'notificationText' => "Your task has been marked as {$statusDetails->taskStatusName} by {$employeeDetails->employeeName} in project {$projectDetails->projectName}",
							'timestamp' => $config['currentDateTimeFormatted'],
							'emailed' =>'Y'
						);
						echo "<h5>Notification Details</h5>";
						var_dump($notificationDetails);
						// insert notification details to the database
						if (!$DBConn->insert_data('tija_notifications', $notificationDetails)) {
							$errors[] = "Unable to create notification for task status update";
						} else {
							require $base .'php/classes/Exception.php';
							require $base .'php/classes/phpmailer.php';
							require $base .'php/classes/smtp.php';
							// send email notification
							$notificationID = $DBConn->lastInsertID();

							$emailBody = "Your task {$taskDetails->projectTaskName} has been marked as {$statusDetails->taskStatusName} by {$employeeDetails->employeeName} in project {$projectDetails->projectName}.";
							// $emailNohtmlBody = "Your task has been marked as {$statusDetails->taskStatusName} by {$employeeDetails->employeeName} in project {$projectDetails->projectName}.";


							$send= true;
							if ($send) {
								$mail = new PHPMailer(true);
								$subject = "Task Status Update: {$statusDetails->taskStatusName} in Project {$projectDetails->projectName}";
								try {
									$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
									$mail->isSMTP();                                            // Send using SMTP
									$mail->Host       = $config['emailHost'];                   // Set the SMTP server to send through
									$mail->SMTPAuth   = true;                                   // Enable SMTP authentication
									$mail->Username   = $config['userName'];                    // SMTP username
									$mail->Password   = $config['emailPWS'];                    // SMTP password
							// Set encryption based on port
							if ($config['emailPort'] == 465) {
								$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;        // SSL encryption for port 465
							} elseif ($config['emailPort'] == 587) {
								$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;     // TLS encryption for port 587
							}
							$mail->Port       = $config['emailPort'];                   // TCP port

									// SSL options for Microsoft 365
									$mail->SMTPOptions = array(
										'ssl' => array(
											'verify_peer' => true,
											'verify_peer_name' => true,
											'allow_self_signed' => true
										)
									);
									$mail->Timeout = 30;                                        // Increase timeout

									$mail->setFrom($config['siteEmail'], $config['siteName']);
									$mail->addAddress($superviorDetails->Email, $superviorDetails->employeeName);     								// Add a recipient
									$mail->addReplyTo($config['siteEmail'], $config['siteName']);
									$mail->addBCC($config['secondaryEmail'], $config['siteName']);
									// Attachments
									//$mail->addAttachment('/var/tmp/file.tar.gz');         		// Add attachments
									//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    		// Optional name

									$mail->isHTML(true);                                 			// Set email format to HTML
									$mail->Subject = $subject;
									$mail->Body    = $emailBody;
									// $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
									//   $mail->AltBody    = $emailNoHtml; 									// optional, comment out and test

									  $mail->send();
								} catch (Exception $e) {
									var_dump($e);
									$errors[]= "Unable to send reset Email. Error. {$mail->ErrorInfo}";

								}
							}


						}
					}
				}
			}
		}
	}
}
		$fileAttachments= isset($_FILES['fileAttachments']) ? $_FILES['fileAttachments'] : false;
		// var_dump($fileAttachments);
		$filesArray= array();
		$n= $fileAttachments ? count($fileAttachments['name']): 0;
		echo "<h1> nymber of  files = {$n}</h1>";
		for ($i=0; $i <$n ; $i++) {
			$filesArray[]= array( 'name'=> $fileAttachments['name'][$i], 'type'=> $fileAttachments['type'][$i], 'tmp_name'=> $fileAttachments['tmp_name'][$i], 'error'=>$fileAttachments['error'][$i], 'size'=>$fileAttachments['error'][$i]);
		}

		var_dump($filesArray);

		if ($filesArray) {
			// Define allowed file extensions for time log attachments (File class expects extensions, not MIME types)
			$allowedExtensions = [
				'pdf',      // PDF files
				'jpg',      // JPEG images
				'jpeg',     // JPEG images
				'png',      // PNG images
				'gif',      // GIF images
				'webp',     // WebP images
				'doc',      // Word documents (old)
				'docx',     // Word documents (new)
				'xls',      // Excel spreadsheets (old)
				'xlsx',     // Excel spreadsheets (new)
				'csv',      // CSV files
				'txt'       // Text files
			];

			foreach ($filesArray as $key => $file) {
				var_dump($file);
				if($file['name'] == '') {
					continue;
				}

				$uploadedFile = new UploadedFile($file['name'], $file['tmp_name'], $file['type'], $file['size'], $file['error']);
				$uploadError = $uploadedFile->upload_error($config['MaxUploadedFileSize'], $config['DataDir'], $allowedExtensions);
				if (!$uploadError) {
					var_dump($uploadedFile);

					$fileFullUrl= $uploadedFile->newFileName;
					$pathParts= Utility::file_path_split($fileFullUrl);
					echo "<h3> File Parts </h3>";
					var_dump($pathParts);
					$fileDetails= array(
						'fileURL'=> $pathParts[count($pathParts)-1],
						'timelogID'=> $timelogID,
						'userID'=> $details['employeeID'],
						'fileType'=>  $uploadedFile->fileType
					);
					if (count($errors) === 0) {
						if ($fileDetails) {
							if (!$DBConn->insert_data('tija_task_files', $fileDetails)) {
								$errors[]= "unable to save file {$file['name'] }";
							} else {
								// Sync file to tija_project_files if projectID exists
								if ($projectID && $timelogID) {
									syncTimeLogFileToProjectFiles($fileDetails, $file, $uploadedFile, $projectID, $projectTaskID, $details['employeeID'], $DBConn, $config);
								}
							}
						}
					}
				} else {
					var_dump($uploadError);
				}
			}
		}
		var_dump($errors);
} else {
	Alert::warning("You need to be logged in as a valid ");
}
if (isset($_SESSION['returnURL']) && $_SESSION['returnURL'] !== '') {
	$returnURL =Utility::clean_string($_SESSION['returnURL']);
	// unset($_SESSION['returnURL']);
} else {
	$returnURL= 's=user&p=work_hour_entry&PID='.$projectID;
}
// var_dump($errors);

 if (count($errors) == 0) {
	 $DBConn->commit();
	 $messages = array(array('Text'=>'Your time log was successfully updated', 'Type'=>'success'));
 } else {
	 $DBConn->rollback();
	 $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
 }
 $_SESSION['FlashMessages'] = serialize($messages);
 header("location:{$base}html/?{$returnURL}");
?>