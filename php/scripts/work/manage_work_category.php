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
   $workCategoryName = (isset($_POST['workCategoryName']) && !empty($_POST['workCategoryName'])) ? Utility::clean_string($_POST['workCategoryName']) : "";
   $workCategoryCode = (isset($_POST['workCategoryCode']) && !empty($_POST['workCategoryCode'])) ? Utility::clean_string($_POST['workCategoryCode']) : Utility::unit_code($workCategoryName, 5, $DBConn);
   $workCategoryDescription = (isset($_POST['workCategoryDescription']) && !empty($_POST['workCategoryDescription'])) ? $_POST['workCategoryDescription'] : "";
   $workCategoryID = (isset($_POST['workCategoryID']) && !empty($_POST['workCategoryID'])) ? Utility::clean_string($_POST['workCategoryID']) : null;


   if(!$workCategoryID){
      $workCategoryName ? $details['workCategoryName'] = $workCategoryName : $errors[] = "Please submit valid work category name";
      $workCategoryCode ? $details['workCategoryCode'] = $workCategoryCode : $errors[] = "Please submit valid work category code";
      $workCategoryDescription ? $details['workCategoryDescription'] = $workCategoryDescription : $errors[] = "Please submit valid work category description";

      var_dump($details);

      if(!$errors){
         if($details){
            $details['DateAdded'] = $config['currentDateTimeFormated'];
            if(!$DBConn->insert_data('tija_work_categories', $details)){
               $errors[] = "Unable to add work category details";
            } else {
               $workCategoryID = $DBConn->lastInsertId();
            }
         }
      }

   } else {

      $workCategoryDetails = Data::work_categories(array('workCategoryID'=>$workCategoryID), true, $DBConn);
      var_dump($workCategoryDetails);

      $workCategoryName && $workCategoryDetails->workCategoryName != $workCategoryName ? $changes['workCategoryName'] = $workCategoryName : null;
      $workCategoryCode && $workCategoryDetails->workCategoryCode != $workCategoryCode ? $changes['workCategoryCode'] = $workCategoryCode : null;
      $Lapsed && $workCategoryDetails->Lapsed != $Lapsed ? $changes['Lapsed'] = $Lapsed : null;
      if(!$errors){
         if($changes){
            var_dump($changes);
            if(!$DBConn->update_table('tija_work_categories', array_merge($changes, array('LastUpdate'=>$config['currentDateTimeFormated'])), array('workCategoryID'=>$workCategoryID))){
               $errors[] = "Unable to update work category details";
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