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
   $workSegmentID = (isset($_POST['workSegmentID']) && !empty($_POST['workSegmentID'])) ? Utility::clean_string($_POST['workSegmentID']) : null;
   $workSegmentName = (isset($_POST['workSegmentName']) && !empty($_POST['workSegmentName'])) ? Utility::clean_string($_POST['workSegmentName']) : "";
   $workSegmentCode = (isset($_POST['workSegmentCode']) && !empty($_POST['workSegmentCode'])) ? Utility::clean_string($_POST['workSegmentCode']) : Utility::unit_code($workSegmentName, 5, $DBConn);
   $workSegmentDescription = (isset($_POST['workSegmentDescription']) && !empty($_POST['workSegmentDescription'])) ? $_POST['workSegmentDescription'] : "";


   if(!$workSegmentID){
      $workSegmentName ? $details['workSegmentName'] = $workSegmentName : $errors[] = "Please submit valid work segment name";
      $workSegmentCode ? $details['workSegmentCode'] = $workSegmentCode : $errors[] = Utility::generate_name_code($workSegmentName,4) ;
      $workSegmentDescription ? $details['workSegmentDescription'] = $workSegmentDescription : $errors[] = "Please submit valid work segment description";

      var_dump($details);

      if(!$errors){
         if($details){
            $details['DateAdded'] = $config['currentDateTimeFormated'];
            $details['LastUpdate'] = $config['currentDateTimeFormated'];
            $details['LastUpdateByID'] = $userDetails->ID;
            if(!$DBConn->insert_data('tija_pms_work_segment', $details)){
               $errors[] = "Unable to add work segment details";
            } else {
               $workSegmentID = $DBConn->lastInsertId();
            }
         }
      }
   } else {
      $workSegmentDetails = Data::work_segments(array('workSegmentID'=>$workSegmentID), true, $DBConn);
      var_dump($workSegmentDetails);

      $workSegmentName && $workSegmentDetails->workSegmentName != $workSegmentName ? $changes['workSegmentName'] = $workSegmentName : null;
      $workSegmentCode && $workSegmentDetails->workSegmentCode != $workSegmentCode ? $changes['workSegmentCode'] = $workSegmentCode : null;
      if(!$errors){
         if($changes){
            var_dump($changes);
            $changes['LastUpdate'] = $config['currentDateTimeFormated'];
            $changes['LastUpdateByID'] = $userDetails->ID;

            if(!$DBConn->update_table('tija_pms_work_segment', array_merge($changes, array('LastUpdate'=>$config['currentDateTimeFormated'])), array('workSegmentID'=>$workSegmentID))){
               $errors[] = "Unable to update work segment details";
            }
         }
      }

   }

} else {
	$errors[] = 'You need to log in as a valid user to add new sales case.';
}

var_dump($errors);
$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performance=home');

var_dump($returnURL);
 if (count($errors) == 0) {
	 $DBConn->commit();
	 $messages = array(array('Text'=>$success, 'Type'=>'success'));
 } else {
	 $DBConn->rollback();
	 $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
 }
 $_SESSION['FlashMessages'] = serialize($messages);
 header("location:{$base}html/{$returnURL}");
?>