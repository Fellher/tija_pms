
<?php
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
$success="";
if ($isValidUser) {
	var_dump($_POST);
   $action = (isset($_POST['action']) && !empty($_POST['action'])) ?  Utility::clean_string($_POST['action']): "";
   $userID = (isset($_POST['userID']) && !empty($_POST['userID'])) ?  Utility::clean_string($_POST['userID']): "";
   $entityID = (isset($_POST['entityID']) && !empty($_POST['entityID'])) ?  Utility::clean_string($_POST['entityID']): "";
   $orgDataID = (isset($_POST['orgDataID']) && !empty($_POST['orgDataID'])) ?  Utility::clean_string($_POST['orgDataID']): "";
   $proposalStatusCategoryID = (isset($_POST['proposalStatusCategoryID']) && !empty($_POST['proposalStatusCategoryID'])) ?  Utility::clean_string($_POST['proposalStatusCategoryID']): "";
   $proposalStatusCategoryDescription = (isset($_POST['proposalStatusCategoryDescription']) && !empty($_POST['proposalStatusCategoryDescription'])) ?  Utility::clean_string($_POST['proposalStatusCategoryDescription']): "";
   $proposalStatusCategoryName = (isset($_POST['proposalStatusCategoryName']) && !empty($_POST['proposalStatusCategoryName'])) ?  Utility::clean_string($_POST['proposalStatusCategoryName']): "";



   if($proposalStatusCategoryID){
      $propoSalCategoryDetails = Sales::tija_proposal_status_categories(array('proposalStatusCategoryID'=>$proposalStatusCategoryID), true, $DBConn);
      var_dump($propoSalCategoryDetails);

   } else {
      $proposalStatusCategoryName ? $details['proposalStatusCategoryName'] = $proposalStatusCategoryName : $errors[] = 'Proposal Status Category Name is required';
      $proposalStatusCategoryDescription ? $details['proposalStatusCategoryDescription'] = $proposalStatusCategoryDescription : $errors[] = 'Proposal Status Category Description is required';
      $entityID ? $details['entityID'] = $entityID : $errors[] = 'Entity ID is required';
      $orgDataID ? $details['orgDataID'] = $orgDataID : $errors[] = 'Org Data ID is required';
      

      if(!$errors){
         if($details) {
            $details['LastUpdateByID']=$userDetails->ID;
            $details['LastUpdate']=$config['currentDateTimeFormated'];
            if(!$DBConn->insert_data('tija_proposal_status_categories', $details)) {
               $errors[] = 'Error adding new Proposal Status Category';
            } else {
               $proposalStatusCategoryID = $DBConn->lastInsertID();
            }
         }
      }
   }




} else {
	$errors[] = 'You need to log in as a valid administrator to do that.';
}
$returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
var_dump($errors);

if (count($errors) == 0) {
	$DBConn->commit();
	$messages = array(array('Text'=>"{$success}", 'Type'=>'success'));
} else {
 	$DBConn->rollback();
 	$messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
}
 $_SESSION['FlashMessages'] = serialize($messages);
 header("location:{$base}html/{$returnURL}");
?>