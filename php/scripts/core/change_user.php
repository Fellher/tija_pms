<?php
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();
if ( $isValidUser) {
	var_dump($_GET);

	$userID= $userDetails->ID;

	if (isset($_GET['user']) && !empty($_GET['user'])) {
		$newUser= Utility::clean_string($_GET['user']);

		var_dump($newUser);

		if ($newUser=== 'superAdmin' && $isSuperAdmin) {
			$_SESSION['logedinUser']="superAdmin";
			$success= "Your role has been changed to Admin";
		} else {
			unset($_SESSION['logedinUser']);
		}
	}
var_dump($_SESSION);
if ($newUser == 'superAdmin') {
	$returnURL= 's=admin&ss=user&p=home';
}


} else {
	$errors[]="You need to log in as a valid administrator to process this request";
}


if (count($errors) == 0) {
	 $DBConn->commit();
	 $messages = array(array('Text'=>"{$success}", 'Type'=>'success'));
	 
 } else {
	 $DBConn->rollback();
	 $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
 }

 var_dump($returnURL);

 header("location:{$base}html/?{$returnURL}");
?>