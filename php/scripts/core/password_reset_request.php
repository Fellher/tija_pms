<?php
/**
 *
 *
 */
session_start();
$base = '../../../';
set_include_path($base);

include 'php/class_autoload.php';

include 'php/config/config.inc.php';

include 'php/scripts/db_connect.php';

Utility::print_array($_POST);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


require $base .'php/classes/Exception.php';
require $base .'php/classes/phpmailer.php';
require $base .'php/classes/smtp.php';


$toURL = '';
$errors = array();
$DBConn->begin();
$changes= array();


$email = trim(Form::validate_email ($_POST['Email']));

  $captcha=$_POST['g-recaptcha-response'];
	$secretKey = $config['secretKey'];
	$ip = $_SERVER['REMOTE_ADDR'];
	// post request to server
	$url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secretKey) .  '&response=' . urlencode($captcha);
	$response = file_get_contents($url);
	$responseKeys = json_decode($response,true);
	var_dump($response);
	var_dump($responseKeys);
	// if($responseKeys['success'] === true){
		if (isset($email) && !empty($email)) {

			var_dump($email);
			$userDetails= Core::user(array('Email'=>$email), true, $DBConn);
			var_dump($userDetails);

			if ($userDetails) {

				if ($userDetails->NeedsToChangePassword != 'y') {
					$changes['needsToChangePassword'] ='y';
				}

				if ($userDetails->Valid != 'n') {
					$changes['Valid'] = 'n';
				}
				/*Update changes to person table*/
				if ($changes) {
					if (!$DBConn->update_table('people', $changes, array('ID'=>$userDetails->ID))) {
						$errors[]= "<span class='fst-italic text-center'> Failed to update changes to the database </span>";
					}
				}

				if (count($errors) ===0) {
					/*Retrieve tokens*/
					$tokens = Core::tokens(array('PersonID'=>$userDetails->ID), true, $DBConn);
					if ($tokens) {
						$tokensArr =array($tokens->Token1, $tokens->Token2);
					} else {
						$regtokens = Core::add_registration_tokens($userDetails->ID, $DBConn);
						$tokensArr=array($regtokens[0], $regtokens[1]);
						$tokens = Core::tokens(array('ID'=>$regtokens[2]), true, $DBConn);
					}
					if ($tokensArr) {
						$changeSet = $DBConn->update_table('registration_tokens', array('PasswordSet'=>'n'), array('ID'=>$tokens->ID));
						 $base_url = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https' : 'http' ) . '://' .  $_SERVER['HTTP_HOST'];

						 var_dump($base_url);
						if ((preg_match('/localhost/', $_SERVER['HTTP_HOST'])) || (preg_match('/liveprojects/', $_SERVER['HTTP_HOST'])) ){
							$base_url="{$config['siteURL']}";
						}

						var_dump($base_url);

						$recoveryUrl = "{$base_url}/html/?p=password_reset&t1={$tokensArr[0]}&t2={$tokensArr[1]}&ID={$userDetails->ID}";
						$name= Core::user_name($userDetails->ID, $DBConn);
						$emailBody = "<h3> Hi {$name} </h3>
									<p>we have received a request to reset your password on your {$config['siteName']} Account. Please click on the link below to proceed and set a new password for your account </p>
									<div style='margin-top:20px; text-align:center; '>
										<a href='{$recoveryUrl}' style='display:block; text-decoration:none; padding:20px 30px; border:solid 1px gray;  line-height:30px;width: 500px;border-radius:10px; -webkit-font-smoothing: antialiased; color:grey;font-weight: 600;  text-align:center;  margin:50px auto'> Reset Password</a>
									</div>
									<div style='text-align:center; margin-bottom-30px'>
									 	<p style='margin-bottom:20px'> If the above link does not work, copy and paste this URL into your browser </p>
									 	{$recoveryUrl}
									 	<p style='font-size:14px'>
									 		If you experience any issues with the reset, please
									 		<a href='{$config['siteURL']}html/?p=contact_us&ID={$userDetails->ID}' >contact us</a>.
									 	</p>
									 </div>
									 <div style='text-align:center; margin-bottom-30px'>
								  		<p style='font-size=22px'> <em> Regards</p>
								  		<p style'font-size:16px> {$config['siteName']}</em> </p>
								  	</div>";

								$emailNoHtml = "Hello {$name}" . PHP_EOL .
												"we have received a request to reset your password on your {$config['siteName']} Account. Please click on the link below to proceed and set a new password for your account". PHP_EOL .
												"Copy and paste this URL into your browser and start the reset process ". PHP_EOL .
												"{$recoveryUrl}".PHP_EOL  .PHP_EOL  .

												'Regards' .PHP_EOL  .PHP_EOL  .
												"{$config['siteName']}";
						echo $emailBody;
						$send= true;
						if ($send) {
							$mail = new PHPMailer(true);
							$subject = "{$config['siteName']} Account Password Reset";
							try {
								$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
								$mail->isSMTP();                                            // Send using SMTP
								$mail->Host       = $config['emailHost'];                   // Microsoft 365: smtp.office365.com
								$mail->SMTPAuth   = true;                                   // Enable SMTP authentication
								$mail->Username   = $config['userName'];                    // SMTP username
								$mail->Password   = $config['emailPWS'];                    // SMTP password

							// Set encryption based on port
							if ($config['emailPort'] == 465) {
								$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;        // SSL encryption for port 465
							} elseif ($config['emailPort'] == 587) {
								$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;     // TLS encryption for port 587
							}
							$mail->Port       = $config['emailPort'];                   // TCP port

								// SSL options for Microsoft 365
								$mail->SMTPOptions = array(
									'ssl' => array(
										'verify_peer' => true,
										'verify_peer_name' => true,
										'allow_self_signed' => true
									)
								);
								$mail->Timeout = 30;                                        // Increase timeout

								$mail->setFrom($config['siteEmail'], $config['siteName']);
								$mail->addAddress($email, $name);     								// Add a recipient
								$mail->addReplyTo($config['siteEmail'], $config['siteName']);
								$mail->addBCC($config['secondaryEmail'], $config['siteName']);
								// Attachments
								//$mail->addAttachment('/var/tmp/file.tar.gz');         		// Add attachments
								//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    		// Optional name

								$mail->isHTML(true);                                 			// Set email format to HTML
								$mail->Subject = $subject;
								$mail->Body    = $emailBody;
								// $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
							  	$mail->AltBody    = $emailNoHtml; 									// optional, comment out and test

							  	$mail->send();
							} catch (Exception $e) {
								var_dump($e);
								$errors[]= "Unable to send reset Email. Error. {$mail->ErrorInfo}";

							}
						}
					}
				}
			} else {
				$errors[]="<span class='d-block text-center fst-italic'>  A user with the above email ID does not exist in the database. Please Submit valid Email ID</span>";
			}
		}
	// } else {
	// 	$errors[]="Please submit valid recaptcha response";
	// }

if (count($errors) == 0) {
	$DBConn->commit();
	// $_SESSION['SessionID'] = $sessID;
	// $messages[] = array('Text'=>"Your reset details are in your mailbox. Welcome! {$toLink} ", 'Type'=>'success');
	$messages[] = array('Text'=>"<span class=' d-block text-center fst-italic font-16'>Thank you for your password reset request. Your password reset details have been sent to your mailbox.</span>  ", 'Type'=>'success');
	$toURL="p=password_reset&valid=true&email={$email}";

} else {

	var_dump($errors);
	$DBConn->rollback();
	$messages = array_map(function ($error) { return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
	$toURL = "p=reset_password";

}
$_SESSION['FlashMessages'] = serialize($messages);
header("location:{$base}html/?{$toURL}");
?>