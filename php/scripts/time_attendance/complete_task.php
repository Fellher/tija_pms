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

   $subtaskID = (isset($_POST['subtaskID']) && !empty($_POST['subtaskID'])) ? Utility::clean_string($_POST['subtaskID']) : "";
   $todaysdate= (isset($_POST['todaysdate']) && !empty($_POST['todaysdate'])) ? Utility::clean_string($_POST['todaysdate']) : "";

   $timelogDetails = TimeAttendance::project_tasks_time_logs(array("subtaskID"=> $subtaskID,'taskDate'=>$todaysdate), false, $DBConn);

   var_dump($timelogDetails);

   if($timelogDetails){
      foreach ($timelogDetails as $timelogDetail) {
         $timelogID = $timelogDetail->timelogID;
        var_dump($timelogID);
         if (!$DBConn->update_table("tija_tasks_time_logs", array("dailyComplete"=>'Y'), array("timelogID"=>$timelogID))) {
            $errors[]= "ERROR updating task time log details in the database";
         } else {
            $success = "Task time log updated successfully";
         }
      }
   } else {
      $errors[] = "ERROR! You have not uploaded any time for the task, You can not complete a task without time Logs!";
   }


} else {
	$errors[] = 'You need to log in as a valid user to add new sales case.';
}
var_dump($errors);
$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
var_dump($returnURL);
 if (count($errors) == 0) {
	 $DBConn->commit();
	 $messages = array(array('Text'=>$success, 'Type'=>'success'));
 } else {
	 $DBConn->rollback();
	 $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
 }
 $_SESSION['FlashMessages'] = serialize($messages);
 header("location:{$base}html/?{$returnURL}");
?>