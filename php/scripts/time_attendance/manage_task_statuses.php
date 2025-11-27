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

   $_POST= $_POST;
   var_dump($_POST);

   $taskStatusID = (isset($_POST['taskStatusID']) && !empty($_POST['taskStatusID'])) ? Utility::clean_string($_POST['taskStatusID']) : '';
   $taskStatusName = (isset($_POST['taskStatusName']) && !empty($_POST['taskStatusName'])) ? Utility::clean_string($_POST['taskStatusName']) : '';
   $taskStatusDescription = (isset($_POST['taskStatusDescription']) && !empty($_POST['taskStatusDescription'])) ? Utility::clean_string($_POST['taskStatusDescription']) : '';

   if ($taskStatusID ) {
      $taskStatusDetails = TimeAttendance::task_statuses(array('ID' => $taskStatusID), true, $DBConn);
      var_dump($taskStatusDetails);
      if ($taskStatusDetails) {
         $taskStatusName && $taskStatusDetails->taskStatusName != $taskStatusName ? $changes['taskStatusName'] = $taskStatusName : '';
         $taskStatusDescription && $taskStatusDetails->taskStatusDescription != $taskStatusDescription ? $changes['taskStatusDescription'] = $taskStatusDescription : '';
         // $colorVariableID && $taskStatusDetails->colorVariableID != $colorVariableID ? $changes['colorVariableID'] = $colorVariableID : '';

         if(!$errors){
            if($changes){
               $changes['LastUpdateByID'] = $userDetails->ID;
               $changes['LastUpdateDate'] = date('Y-m-d H:i:s');
               if(!$DBConn->update_table('tija_task_status', $changes, array('taskStatusID' => $taskStatusID))){
                  $errors[] = "Failed to update task status.";
               } else {                  
                  Alert::success("Task status updated successfully.");                 
               }
            }
         }
      } else {
         $errors[] = "Task status not found.";
        
      }
      
   } else {
      $taskStatusName ? $details['taskStatusName'] = $taskStatusName : '';
      $taskStatusDescription ? $details['taskStatusDescription'] = $taskStatusDescription : '';
      // $colorVariableID ? $details['colorVariableID'] = $colorVariableID : '';
      if(!$errors){
         $details['LastUpdateByID'] = $userDetails->ID;
         $details['LastUpdate'] = date('Y-m-d H:i:s');

         var_dump($details);
         if(!$DBConn->insert_data('tija_task_status', $details)){
            $errors[] = "Failed to create task status.";
         } else {
            Alert::success("Task status created successfully.");
         }
      }
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
// var_dump($errors);

var_dump($returnURL);

if (count($errors) == 0) {
      $DBConn->commit();
      $messages = array(array('Text'=>'Your time log was successfully updated', 'Type'=>'success'));
} else {
      $DBConn->rollback();
      $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
}
$_SESSION['FlashMessages'] = serialize($messages);
header("location:{$base}html/{$returnURL}");?>