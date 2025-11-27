<?php
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes=array();

if ( $isValidUser) {

   $posts= $_POST;
   var_dump($posts);
   var_dump($_FILES);
   $s = (isset($posts['s']) && !empty($posts['s'])) ?  Utility::clean_string($posts['s']): "";
   $ss = (isset($posts['ss']) && !empty($posts['ss'])) ?  Utility::clean_string($posts['ss']): "";
   $filePath = $ss && $s ? "{$s}/{$ss}" : "{$s}";

   $subtaskID = (isset($posts['subtaskID']) && !empty($posts['subtaskID'])) ?  Utility::clean_string($posts['subtaskID']): "";
   $projectTaskID = (isset($posts['projectTaskID']) && !empty($posts['projectTaskID'])) ?  Utility::clean_string($posts['projectTaskID']): "";
   $projectPhaseID = (isset($posts['projectPhaseID']) && !empty($posts['projectPhaseID'])) ?  Utility::clean_string($posts['projectPhaseID']): "";
   $projectID = (isset($posts['projectID']) && !empty($posts['projectID'])) ?  Utility::clean_string($posts['projectID']): "";
   $employeeID = (isset($posts['employeeID']) && !empty($posts['employeeID'])) ?  Utility::clean_string($posts['employeeID']): "";
   $taskStatusID = (isset($posts['taskStatusID']) && !empty($posts['taskStatusID'])) ?  Utility::clean_string($posts['taskStatusID']): "";
   $taskChangeNotes = (isset($posts['taskChangeNotes']) && !empty($posts['taskChangeNotes'])) ?  Utility::sanitize_rich_text_input($posts['taskChangeNotes']): "";
   $changeDateTime = (isset($posts['changeDateTime']) && !empty($posts['changeDateTime']) && preg_match($config['ISODateTimeFormat'], $posts['changeDateTime'])) ?  Utility::clean_string($posts['changeDateTime']): "";
   $taskDate = (isset($posts['taskDate']) && !empty($posts['taskDate']) && preg_match($config['ISODateFormat'], $posts['taskDate'])) ?  Utility::clean_string($posts['taskDate']): "";


   $workTypeID = (isset($posts['workTypeID']) && !empty($posts['workTypeID'])) ?  Utility::clean_string($posts['workTypeID']): "";
   $hours = (isset($posts['hours']) && !empty($posts['hours'])) ?  Utility::clean_string($posts['hours']): "00";
   $minutes = (isset($posts['minutes']) && !empty($posts['minutes'])) ?  Utility::clean_string($posts['minutes']): "00";
   ($hours && $minutes) ? $taskTime = $hours.":".$minutes : $taskTime = "";
   

   
   $fileAttachments = (isset($_FILES['fileAttachments']) && !empty($_FILES['fileAttachments'])) ? $_FILES['fileAttachments']: "";
   $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'];
   $allowed_fileSize = $config['MaxUploadedFileSize'];
   // var_dump($fileAttachments) ;
   $fileUpload = array();
   if($fileAttachments) {
      $fileUpload = FIle:: multiple_file_upload($_FILES, $filePath, $allowedTypes, $allowed_fileSize , $config, $DBConn);
   }
  
   // echo "<h5> File Upload</h5>";
   // var_dump($fileUpload);
   if($fileUpload) {
     if(!$fileUpload['errors']) {
         $fileUpload = $fileUpload['uploadedFilePaths'];
      } else {
         $errors= $fileUpload['errors']; 
      }      
   } 

   $taskStatusID  ? $details['taskStatusID'] = $taskStatusID : $errors[]="<span class't600'> ERROR!</span> Task status ID is required";
   $subtaskID  ? $details['subtaskID'] = $subtaskID : "";
   $projectTaskID  ? $details['projectTaskID'] = $projectTaskID : "";
   $projectPhaseID  ? $details['projectPhaseID'] = $projectPhaseID : "";
   $projectID  ? $details['projectID'] = $projectID : "";
   $employeeID  ? $details['employeeID'] = $employeeID : "";
   $taskChangeNotes ? $details['taskChangeNotes'] = $taskChangeNotes : "";
   $changeDateTime ? $details['changeDateTime'] = $changeDateTime : $errors[]="<span class't600'> ERROR!</span> Change date time is required";
   $taskDate ? $details['taskDate'] = $taskDate : $errors[]="<span class't600'> ERROR!</span> Task date is required";

   echo "<h5> Task Status Change Details</h5>";
   var_dump($details);

   if(!$errors) {
     if($details){
         // $details['changeDateTime'] = $config['currentDateTimeFormated'];
         $details['LastUpdate'] = $config['currentDateTimeFormated'];
         $details['LastUpdateByID'] = $employeeID;
         echo "<h5> Task Status Change Details</h5>";
         var_dump($details);
         if (!$DBConn->insert_data("tija_task_status_change_log", $details)) {
            $errors[]="<span class't600'> ERROR!</span> Failed to save task status to the database"; 
         }  else {
            $taskStatusChangeID = $DBConn->lastInsertID();
            echo "<h5> Task Status Change Log Added Successfully</h5>";
            // check if subtask Status is changed or the project task status is changed and update accordingly
            if(($subtaskID || $projectTaskID) && $employeeID && $taskStatusID) {
               // if subtask change the status of the subtask
               if($subtaskID) {
                  // get subtask
                  $projectSubtask = Projects::project_subtasks_full(array("subtaskID"=>$subtaskID), true, $DBConn);
                  var_dump($projectSubtask); 
                  $projectTaskDetails = Projects::project_tasks(array("projectTaskID"=>$projectSubtask->projectTaskID), true, $DBConn);
                  echo "<h5> Project Task Details</h5>";
                  var_dump($projectTaskDetails);
                  if($projectTaskDetails && $projectTaskDetails->projectTaskTypeID != 2) {
                     echo "<h5> Process subtask for complete</h5>";
         
                     if($projectSubtask ) {
                        // get the changes
                     $taskStatusID && $projectSubtask->subTaskStatusID != $taskStatusID ?  $changes['subTaskStatusID'] = $taskStatusID : ""; 
                     $changes['LastUpdate'] = $config['currentDateTimeFormated'];
                     $taskStatusID == 6 ? $changes['subTaskStatus'] = "completed" : "";
                     if($changes){
                           $changes['LastUpdate'] = $config['currentDateTimeFormated'];
                           $changes['LastUpdateByID'] = $employeeID; 
                           echo "<h5> Subtask Changes</h5>";
                           var_dump($changes);
                           if (!$DBConn->update_table("tija_subtasks", $changes, array("subtaskID"=>$subtaskID))) {
                              $errors[]="<span class't600'> ERROR!</span> Failed to update task status to the database"; 
                           } else {
                              echo "<h5> Subtask Changes Updated Successfully {$taskStatusID} </h5>";
                              
                           } 
                        }
                    
                     }
                  }
                  echo "<h5> Task Status iDis {$taskStatusID}</h5>";
                  if($taskStatusID == 6){
                     echo "<h5> Add Timelog</h5>";
                     $changes['subTaskStatus'] = "completed";
                     $changes['subTaskStatusID'] = 6;
                     echo "<h5> Subtask Details</h5>"; 
                     var_dump($projectSubtask);
                     //  if subtask has been completed then add the task time log
                     // add task time logs 
                     $taskTimeLog = array(
                        'taskDate'=>$taskDate,
                        'userID'=>$employeeID,
                        'projectTaskID'=>$projectSubtask->projectTaskID,
                        'subtaskID'=>$subtaskID,                         
                        'projectPhaseID'=>$projectSubtask->projectPhaseID,
                        'projectID'=>$projectSubtask->projectID,
                        'clientID'=>$projectSubtask->clientID,
                        'workTypeID'=>$workTypeID,
                        'taskNarrative'=>$details['taskChangeNotes'],
                        'taskDuration'=>$taskTime,
                        'dailyComplete'=>'Y',
                        'LastUpdate'=>$config['currentDateTimeFormated']                             
                     );

                     echo "<h5> Task Time Log Adding</h5>";
                     var_dump($taskTimeLog);
                     // if($fileUpload) {
                     //    $taskTimeLog['fileAttachments'] = $fileUpload;
                     // }
                     if(!$errors){
                        if($taskTimeLog){
                           if(!$DBConn->insert_data("tija_tasks_time_logs", $taskTimeLog)) {
                              $errors[]="<span class't600'> ERROR!</span> Failed to save task time log to the database"; 
                           } else {
                              $taskTimeLogID = $DBConn->lastInsertID();    
                              echo "<h5> Task Time Log Added Successfully</h5>";      
                              
                              var_dump($fileUpload);
                              if($fileUpload){
                                 foreach($fileUpload as $filePath){
                                    $fileType = pathinfo($filePath, PATHINFO_EXTENSION);
                                    $taskTimeLogFile = array(
                                       'fileURL'=>$filePath,
                                       'userID'=>$taskTimeLog['userID'],
                                       'timelogID'=>$taskTimeLogID,
                                       'fileType'=> $fileType,
                                       'LastUpdate'=>$config['currentDateTimeFormated'],
                                       'LastUpdateByID'=>$employeeID
                                    );

                                    var_dump($taskTimeLogFile);
                                    if(!$DBConn->insert_data("tija_task_files", $taskTimeLogFile)) {
                                       $errors[]="<span class't600'> ERROR!</span> Failed to save task time log file to the database"; 
                                    } else {
                                       $taskTimeLogFileID = $DBConn->lastInsertID();                                       
                                    }
                                 }
                              }
                           }
                        }
                     }
   
                  } 
               
               } elseif($projectTaskID) {
                  $projectTask = Projects::project_tasks(array("projectTaskID"=>$projectTaskID), true, $DBConn);
                  var_dump($projectTask);        
                  $taskStatusID && $taskStatusID != $projectTask->taskStatusID ? $changes['taskStatusID'] = $taskStatusID : '';
                  $changes['LastUpdate'] = $config['currentDateTimeFormated'];
                  $changes['LastUpdateByID'] = $employeeID;
                  if($taskStatusID === 6){
                     $changes['taskStatus'] = "completed";  
                            
                  }

                  var_dump($changes);
                  if($changes) {
                     if($projectTask && $projectTask->projectTaskTypeID != 2) {
                        if (!$DBConn->update_table("tija_project_tasks", $changes, array("projectTaskID"=>$projectTaskID))) {
                           $errors[]="<span class't600'> ERROR!</span> Failed to update task status to the database"; 
                        } 
                     }
                     if($taskStatusID === 6){
                        $changes['subTaskStatus'] = "completed";
                    
                        //  if subtask has been completed then add the task time log
                        // add task time logs 
                        $taskTimeLog = array(
                           'taskDate'=>$taskDate,
                           'userID'=>$employeeID,
                           'projectTaskID'=>$projectSubtask->projectTaskID,
                                                   
                           'projectPhaseID'=>$projectSubtask->projectPhaseID,
                           'projectID'=>$projectSubtask->projectID,
                           'clientID'=>$projectSubtask->clientID,
                           'workTypeID'=>$workTypeID,
                           'taskNarrative'=>$details['taskChangeNotes'],
                           'taskDuration'=>$taskTime,
                           'dailyComplete'=>'Y',
                           'LastUpdate'=>$config['currentDateTimeFormated']                             
                        );
                        echo "<h5> Task Time Log Adding</h5>";
                        var_dump($taskTimeLog);
                        // if($fileUpload) {
                        //    $taskTimeLog['fileAttachments'] = $fileUpload;
                        // }
                        if(!$errors){
                           if($taskTimeLog){
                              if(!$DBConn->insert_data("tija_tasks_time_logs", $taskTimeLog)) {
                                 $errors[]="<span class't600'> ERROR!</span> Failed to save task time log to the database"; 
                              } else {
                                 $taskTimeLogID = $DBConn->lastInsertID();    
                                 echo "<h5> Task Time Log Added Successfully</h5>";      
                                 
                                 var_dump($fileUpload);
                                 if($fileUpload){
                                    foreach($fileUpload as $filePath){
                                       $fileType = pathinfo($filePath, PATHINFO_EXTENSION);
                                       $taskTimeLogFile = array(
                                          'fileURL'=>$filePath,
                                          'userID'=>$taskTimeLog['userID'],
                                          'timelogID'=>$taskTimeLogID,
                                          'fileType'=> $fileType,
                                          'LastUpdate'=>$config['currentDateTimeFormated'],
                                          'LastUpdateByID'=>$employeeID
                                       );

                                       var_dump($taskTimeLogFile);
                                       if(!$DBConn->insert_data("tija_task_files", $taskTimeLogFile)) {
                                          $errors[]="<span class't600'> ERROR!</span> Failed to save task time log file to the database"; 
                                       } else {
                                          $taskTimeLogFileID = $DBConn->lastInsertID();                                       
                                       }
                                    }
                                 }
                              }
                           }
                        }
      
                     } 
                  }
         
               } else {
                  $errors[]="<span class't600'> ERROR!</span> Missing required fields"; 
               }    
            } else {
               $errors[]="<span class't600'> ERROR!</span> Missing required fields"; 
            }
         }
     }
   }
} else {
	Alert::warning("You need to be logged in as a valid ");
}
$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
var_dump($returnURL);
echo "<h4> Errors</h4>";
var_dump($errors);

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