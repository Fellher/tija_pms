<?php
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';


$DBConn->begin();
$errors = array();
$details=array();

var_dump($_POST);

$token1 = $_POST['t1'];
$token2 = $_POST['t2'];

if (!(preg_match('/^[0-9a-f]{64}$/', $token1) && preg_match('/^[0-9a-f]{64}$/', $token2))) {
  $errors[] = 'We were unable to retrieve the your account. Please contact customer care.';
}

$email = (isSet($_POST['Email']) && !empty($_POST['Email'])) ? Form::validate_email(Utility::clean_string($_POST['Email'])) : null ;

if (Form::validate_email($email)) {
	$password = $_POST['Password'];
	$passwordConfirm = $_POST['PasswordConfirm'];
	if (strlen($password) < 4) {
  		$errors[] = 'The password you selected is too short. Please submit a stronger password. Passwords should be at least five characters long, and should contain a mixture of upper case letters, lower case letters, numbers, and special characters.';
	}

	if ($password != $passwordConfirm) {
	  $errors[] = 'The password and password confirmation do not match. Please correct this.';
	}

	if (count($errors) == 0) {
	 	 $registrationDetails = Core::registration_token_details($email, $token1, $token2,  $DBConn);
	    var_dump($registrationDetails);
	  	if ($registrationDetails) {
	    	if ($registrationDetails->PasswordSet == 'n') {
				if (Core::complete_registration($registrationDetails, $password, $DBConn)){
					$time = time();
					$check = md5($_SERVER['HTTP_USER_AGENT'] . $time); /* FIXME : Change this to something more secure!!! */
					$endActiveSessions = Core::end_active_sessions($registrationDetails->PersonID, $DBConn);
					if ($endActiveSessions) {
						$sessID = Core::create_new_session($registrationDetails->PersonID, $check, $time, $DBConn);
						if (!$sessID) {
							$errors[] = 'Attempted login failed. Please contact customer support.';
						}
					} else {
						$errors[] = 'Attempted login failed. Please contact customer support.';
					}
				} else {
	        		$errors[] = 'There was an error completing the registration. Please try again. If the problem persists, contact support.';
	      	}
	    	} else {
	      	$errors[] = "Registration has already been completed. You can log in <a href='{$base}html/?s=user&p=home'>here</a>.";
	    	}
	  	} else {
	    	$errors[] = 'We were unable to retrieve the administrator account. Please contact customer care.';
	  	}
	}
} else {
	$errors[] = "Please submit valid Email";
}

var_dump($errors);

if (count($errors) == 0) { 
  	$DBConn->commit();
  	$_SESSION['SessionID'] = $sessID;
  	var_dump($_SESSION['SessionID']);
  	$messages[] = array('Text'=>'You were successfully logged in. Welcome!', 'Type'=>'success');
  	$toURL= Core::login_redirect($registrationDetails->PersonID, $DBConn);
} else {
  	$DBConn->rollback();
  	$messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
  	$toURL = 's=user&p=complete_registration&';
}

var_dump($toURL);
$_SESSION['FlashMessages'] = serialize($messages);
header("location:{$base}html/?{$toURL}");
?>