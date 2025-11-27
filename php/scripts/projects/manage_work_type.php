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

$workTypeID = (isset($_POST['workTypeID']) && !empty($_POST['workTypeID'])) ? Utility::clean_string($_POST['workTypeID']) : null;
$workTypeName = (isset($_POST['workTypeName']) && !empty($_POST['workTypeName'])) ? Utility::clean_string($_POST['workTypeName']) : null;
$workTypeDescription = (isset($_POST['workTypeDescription']) && !empty($_POST['workTypeDescription'])) ? $_POST['workTypeDescription'] : null;
$Suspended = (isset($_POST['Suspended']) && !empty($_POST['Suspended'])) ? Utility::clean_string($_POST['Suspended']) : 'N';

if(!$workTypeID){
   $workTypeName ? $details['workTypeName'] = $workTypeName : $errors[] = "Please submit valid work type name";
   $workTypeDescription ? $details['workTypeDescription'] = $workTypeDescription : $errors[] = "Please submit valid work type description";

   if(!$errors){
     
      if($details){
         $details['LastUpdate'] = $config['currentDateTimeFormated'];
         if(!$DBConn->insert_data('tija_work_types', $details)){
            $errors[] = "Unable to add work type details";
         } else {
            $workTypeID = $DBConn->lastInsertId();
         }
      }
   }

} else {
   $workTypeDetails = Data::work_types(array('workTypeID'=>$workTypeID), true, $DBConn);
   var_dump($workTypeDetails);
   $workTypeName && $workTypeDetails->workTypeName != $workTypeName ? $changes['workTypeName'] = $workTypeName : null;
   $workTypeDescription && $workTypeDetails->workTypeDescription != $workTypeDescription ? $changes['workTypeDescription'] = $workTypeDescription : null;
   $Suspended && $workTypeDetails->Suspended != $Suspended ? $changes['Suspended'] = $Suspended : null;

   if(!$errors){
    
      if($changes){

         var_dump($changes);
         $changes['LastUpdate'] = $config['currentDateTimeFormated'];
         if(!$DBConn->update_table('tija_work_types', $changes, array('workTypeID'=>$workTypeID))){
            $errors[] = "Unable to update work type details";
         }
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
 header("location:{$base}html/{$returnURL}");
?>