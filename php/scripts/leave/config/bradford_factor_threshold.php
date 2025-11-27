<?php
session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
$success = "";
if ($isValidUser) {
	var_dump($_POST);
   $bradfordFactorID = (isset($_POST['bradfordFactorID']) && !empty($_POST['bradfordFactorID'])) ? Utility::clean_string($_POST['bradfordFactorID']) : "";
   $bradfordFactorName = (isset($_POST['bradfordFactorName']) && !empty($_POST['bradfordFactorName'])) ? Utility::clean_string($_POST['bradfordFactorName']) : "";
   $bradfordFactorValue = (isset($_POST['bradfordFactorValue']) && !empty($_POST['bradfordFactorValue'])) ? Utility::clean_string($_POST['bradfordFactorValue']) : "";
 

   if(!$bradfordFactorID) {
      $bradfordFactorName ? $details['bradfordFactorName'] = Utility::clean_string($bradfordFactorName) : $errors[] = "Please submit valid bradford factor name";
      $bradfordFactorValue ? $details['bradfordFactorValue'] = Utility::clean_string($bradfordFactorValue) : $errors[] = "Please submit valid bradford factor value";
      if (count($errors) === 0) {
         $details['LastUpdate'] = $config['currentDateTimeFormated'];
         $details['LastUpdateByID'] = $userDetails->ID;
         $details['DateAdded'] = $config['currentDateTimeFormated'];
         if ($details) {
            if (!$DBConn->insert_data("tija_bradford_factor", $details)) {
               $errors[]= "ERROR adding new bradford factor to the database";
            } else {
               $success = "bradford factor added successfully";
            }
         }
      }

   } else {
      $bradfordFactorDetails = Leave::bradford_threshold(array("bradfordFactorID"=> $bradfordFactorID), true, $DBConn);
      $bradfordFactorName && ($bradfordFactorName !== $bradfordFactorDetails->bradfordFactorName) ? $changes['bradfordFactorName'] = $bradfordFactorName : "";
      $bradfordFactorValue && ($bradfordFactorValue !== $bradfordFactorDetails->bradfordFactorValue) ? $changes['bradfordFactorValue'] = $bradfordFactorValue : "";
      if (count($errors) === 0) {
         if ($changes) {
            $changes['LastUpdate'] = $config['currentDateTimeFormated'];
            $changes['LastUpdateByID'] = $userDetails->ID;
            if (!$DBConn->update_table("tija_bradford_factor", $changes, array("bradfordFactorID"=>$bradfordFactorID))) {
               $errors[]= "ERROR updating bradford factor details in the database";
            } else {
               $success = "bradford factor updated successfully";
            }
         }
      }
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
 header("location:{$base}html/{$returnURL}");
?>