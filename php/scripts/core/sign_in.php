<?php
/**
 *
 *
 */
session_start();
$base = '../../../';
set_include_path($base);



require_once 'php/includes.php';

// Utility::print_array($_POST);

$email = trim($_POST['email']);
$password = $_POST['password'];

$toURL = '';

$messages = array();
$errors = array();
$info = array();
$DBConn->begin();
$toURL= "";

var_dump($_SESSION);

if ($email != '' && $password != '') {
	$loginDetails = Core::validate_login($email, $password, $DBConn);
	Utility::print_array($loginDetails);
	if ($loginDetails) {
		if ($loginDetails['NeedsToChangePassword'] == 'y') {
			$info[] = 'You need to change your password to proceed.';
			$messages[] = array('Text'=>'You need to change your password to proceed.', 'Type'=>'warning');
			$_SESSION['FlashMessages'] = serialize($messages);
			$errors[] = 'You need to change your password to proceed.';
			$toURL = "p=reset_password&email={$email}";
			// header("location:{$toURL}");
		} else {
			$time = new DateTime();
			$check = md5($_SERVER['HTTP_USER_AGENT'] . $time->format('Y-m-d H:i:s')); /* FIXME : Change this to something more secure!!! */
			$endActiveSessions = Core::end_active_sessions($loginDetails['PersonID'], $DBConn);
			if ($endActiveSessions) {
				$sessID = Core::create_new_session($loginDetails['PersonID'], $check, $time, $DBConn);
				if (!$sessID) {
					$errors[] = 'Attempted login failed. Please contact customer support.';
				}
			} else {
				$errors[] = 'Attempted login failed. Please contact customer support.';
			}
		}
	} else {
		$errors[] = 'Attempted login failed. Please submit a valid email and password.';
	}
} else {
	$errors[] = 'Attempted login failed. Please submit a valid email and password.';
}

Utility::print_array($errors);

if (count($errors) == 0) {
	if (isset($_SESSION['returnURL']) && $_SESSION['returnURL'] !== '') {
		$toURL = $_SESSION['returnURL'];
		// unset($_SESSION['returnURL']);
	} else {
		$toURL= Core::login_redirect($loginDetails['PersonID'], $DBConn);
	}
	var_dump($toURL);
	$DBConn->commit();
	$_SESSION['SessionID'] = $sessID;
} else {
	$DBConn->rollback();
	$messages = array_map(function ($error) { return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
	$toURL = !$toURL ?'p=sign_in' : $toURL;
}
var_dump($toURL);
//remove the ? from the beginning of the URL
$toURL = ltrim($toURL, '?');
var_dump($toURL);

var_dump($toURL);
$_SESSION['FlashMessages'] = serialize($messages);
header("location:{$base}html/?{$toURL}");
?>