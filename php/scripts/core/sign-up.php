<?php
session_start();
$base = '../../../';
set_include_path($base);

include 'php/class_autoload.php';

include 'php/config/config.inc.php';

include 'php/scripts/db_connect.php';

Utility::print_array($_POST);
require $base .'php/classes/Exception.php';
require $base .'php/classes/phpmailer.php';
require $base .'php/classes/smtp.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$toURL = '';
$errors = array();
$DBConn->begin();

$firstName = Utility::clean_string($_POST['FirstName']);
if ($firstName == '') {
 	$errors[] = 'Please submit a valid first name.';
}

if (!$surname = Utility::clean_string($_POST['Surname'])) {
 	$errors[] = 'Please submit a valid last name.';
}

$otherNames = null;
if (Utility::clean_string($_POST['OtherNames'])){
 	$otherNames = Utility::clean_string($_POST['OtherNames']);
}
if (isset($_POST['jobID']) && $_POST['jobID'] !== '') {
	$jobID = Utility::clean_string($_POST['jobID']);
} else {
	$jobID= '';
}

$email = null;
if (!$email = Form::validate_email($_POST['Email'])) {
 	$errors[] = 'Please sumbit a valid email address for the alumni.';
}else {
	$userExists=Core::check_user ( $email, $DBConn);
	if ($userExists) {
		$errors[]=" An applicant with that email address already exists in the database.";
		# code...
	}
}

if (isset($_POST['instanceID']) && !empty($_POST['instanceID'])) {
	$instanceID= Utility::clean_string($_POST['instanceID']);
}

$personDetails = array('FirstName'=>$firstName, 'Surname'=>$surname, 'OtherNames'=>$otherNames, 'Email'=>$email);
$personID = Core::add_new($personDetails, $DBConn);
var_dump($personDetails);
print '<br> Person Id '.$personID;

	if ($personID) {
		$applicantsDetails = array('ID'=>$personID, 'UID'=>bin2hex(openssl_random_pseudo_bytes(32)), 'instanceID'=> $instanceID);

		$userAdded= $DBConn->insert_data('user_details', $applicantsDetails);
		echo '<h3> User Added </h3>';
var_dump($userAdded);
		if ($userAdded) {

			var_dump($userAdded);
			$tokens = Core::add_registration_tokens($personID, $DBConn);

			var_dump($tokens);
			if ($tokens) {

				var_dump($tokens);

				// $sendEmail=Applicants::send_registration_email($personDetails, $personID, $tokens, $DBConn);
				// $s='recruitment.racg.co.ke';

				$link = "http://{$config['siteRoot']}/html/?s=user&p=complete_registration&t1={$tokens[0]}&t2={$tokens[1]}&ID={$personID}&jobID={$jobID}";
				// print "<p> The link is <a target='_blank' href=". $link ."> Link</a>";
				$plink="<a target='_blank' href=". $link ."> complete registration</a>";

				$name = $firstName . ($otherNames ? " {$otherNames}" : '') . ($surname ? " {$surname}" : '');
				$messageBody="<p> Hello {$name} </p>
								<p>You have been successfully added to the {$config['siteName']} Portal<p>
								<p> Please click in the link below to complete your registration  </p>
								<a style='display: inline-block;font-weight: 400;line-height: 1.5;color: #fff;text-align: center; text-decoration: none;vertical-align: middle;cursor: pointer;
									-webkit-user-select: none;
									-moz-user-select: none;
									user-select: none;
									background-color: blue;
									border: 1px solid transparent;
									padding: 0.375rem 0.75rem;
									font-size: 1rem;
									border-radius: 0.25rem;'
									href='".$link."'> Complete registration
								</a>

								<p> Regards</p>
								<p> {$config['siteName']}</p>
								";



				$toEmail= $email;

				$subject = $config['siteName']  ;
				$toName= $name;
				$bodyNohtml = 'Hello ' . PHP_EOL .
							' Please  click on the link below/ copy paste it to your browser to set Up your Account' . PHP_EOL .

						"{$link}".PHP_EOL  .PHP_EOL  .
						'Regards';

			   $send = true;
			    if ($send) {
			    	$mail = new PHPMailer(true);

			    	try {
						//Server settings
						$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
						$mail->isSMTP();                                            // Send using SMTP

					$mail->Host       = $config['emailHost'];                   // SMTP host
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
						$mail->addAddress($toEmail, $toName); 						// Add a recipient
						$mail->addReplyTo($config['siteEmail'], $config['siteName']);
						// $mail->addCC('gmathu@aar.co.ke');
						$mail->addBCC($config['secondaryEmail']);

						// Attachments
						//$mail->addAttachment('/var/tmp/file.tar.gz');         		// Add attachments
						//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    		// Optional name

						// Content
						$mail->isHTML(true);                                 		 // Set email format to HTML
						$mail->Subject = $subject;
						$mail->Body    = $messageBody;
						// $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
						  $mail->AltBody    = $bodyNohtml; // optional, comment out and test

						$mail->send();
						echo 'Message has been sent';
					} catch (Exception $e) {
						//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
							$errors[]= 'Unable to send reset email';
					}

			    }
			}

		}
	}
echo $messageBody;
	var_dump($errors);

 if (count($errors) == 0) {
	$DBConn->commit();
	$messages = array(array('Text'=>'Your registration has been successfully received. An email has been sent to your address with instruction on how to complete the registration.', 'Type'=>'success'));
	 // FOR ONLINE
		// header("location:{$base}html/?p=registration_received&id={$personID}");
 } else {
	$DBConn->rollback();
	$messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
	// FOR ONLINE
		// header("location:{$base}html/?p=registration_not_received");
 }
 if (isset($_SESSION['returnURL']) && $_SESSION['returnURL'] !== '') {
 	$returnURL=$_SESSION['returnURL'];
 	unset($_SESSION['returnURL']);
 } else {
 	$returnURL="s=user&p=user_details&id={$personID}";
 }
 var_dump($messages);
 $_SESSION['FlashMessages'] = serialize($messages);

 // FOR ONLINE
header("location:{$base}html/?{$returnURL}");

 // FOR OFFLINE
//  header("location:{$link}");

?>