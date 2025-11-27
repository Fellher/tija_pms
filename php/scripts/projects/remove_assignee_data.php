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

   $assignmentTaskID = isset($posts['assignmentId']) && !empty($posts['assignmentId']) ? $posts['assignmentId'] : '';
   $userID = isset($posts['assigneeId']) && !empty($posts['assigneeId']) ? $posts['assigneeId'] : '';
   $projectTaskID = isset($posts['taskId']) && !empty($posts['taskId']) ? $posts['taskId'] : '';
   $projectID = isset($posts['projectId']) && !empty($posts['projectId']) ? $posts['projectId'] : '';

   if($assignmentTaskID && $userID && $projectTaskID && $projectID){
      $assignmentTaskDetails = Projects::task_user_assignment(array("assignmentTaskID"=>$assignmentTaskID), true, $DBConn);

      var_dump($assignmentTaskDetails);

      if($assignmentTaskDetails){
         $changes['Suspended'] = 'Y';
         $changes['assignmentStatus'] = 'suspended';
         // $changes['LastUpdate'] = $config['currentDateTimeFormated'];
         if(!$DBConn->update_table('tija_assigned_project_tasks', $changes, array('assignmentTaskID'=>$assignmentTaskID))){
            $errors[] = "Unable to update assignment task details";
         }
      }
   }


} else {
	Alert::warning("You need to be logged in as a valid ");
}
$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');

var_dump($errors);
var_dump($returnURL);

 if (count($errors) == 0) {
	 $DBConn->commit();
	 $messages = array(array('Text'=>'Your time log was successfully updated', 'Type'=>'success'));
 } else {
	 $DBConn->rollback();
	 $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
 }
 $_SESSION['FlashMessages'] = serialize($messages);
//  header("location:{$base}html/{$returnURL}");
?>