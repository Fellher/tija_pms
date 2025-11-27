<?php
$firstName = Utility::clean_string($_POST['FirstName']);
		if ($firstName == '') {
		 	$errors[] = 'Please submit a valid first name.';
		}

		if (!$surname = Utility::clean_string($_POST['Surname'])) {
		 	$errors[] = 'Please submit a valid last name.';
		}

		// if (!$instanceID = Utility::clean_string($_POST['instanceID'])) {
		//  	$errors[] = 'Please submit a valid InstanceID.';
		// }

		$otherNames = null;
		if (Utility::clean_string($_POST['OtherNames'])){
		 	$otherNames = Utility::clean_string($_POST['OtherNames']);
		}

		$email = null;
		if (!$email = Form::validate_email($_POST['Email'])) {
		 	$errors[] = 'Please sumbit a valid email address for the recruiter.';
		}else {
			$userExists=Person::check_user ( $email, $DBConn);
			if ($userExists) {
				$errors[]=" A user with that email address already exists in the database.";
			}
		}

		$personDetails = array('FirstName'=>$firstName, 'Surname'=>$surname, 'OtherNames'=>$otherNames, 'Email'=>$email);
		var_dump($personDetails);
		$personID = Person::add_new($personDetails, $DBConn);

		print $personID;
		var_dump($errors);
		if ($personID) {
			$adminDetails = array('ID'=>$personID, 'UID'=>bin2hex(openssl_random_pseudo_bytes(32)), 'instanceID' => $instanceID );
			if ($DBConn->insert_data('sbsl_users', $adminDetails)) {
			   $tokens = Person::add_registration_tokens($personID, $DBConn);
				if ($tokens) {
					$name = $firstName . ($otherNames ? " {$otherNames}" : '') . ($surname ? " {$surname}" : '');

				$link = "http://{$s}/html/?s=user&p=complete_registration&t1={$tokens[0]}&t2={$tokens[1]}&ID={$personID}";

				$messageBody="<p> Hello {$name} </p>
									<p> You have been successfuly added to the SBSL HRM  Tool as a user  </p>
									<p> An {$instanceName} instance has been created. Please complete your Registration</p>

									<p>To Complete your registration and set Your password. Click on the link below. </p>
									<a href='".$link."'> Complete registration  </a>

									<p> Regards</p>
									<p> SBSL  Admin</p>
									";

				$bodyNohtml = 'Hello ' . PHP_EOL .
								' Please  click on the link below/ copy paste it to your browser to set Up/Complete registration of  your  Account' . PHP_EOL .

								"{$link}".PHP_EOL  .PHP_EOL  .
								'Regards';

				$send=true;

				}
			}
		}

		if ($send) {
		$toEmail= $email;

		$subject = "SBSL HRM Portal - account Registration"  ;
		$toName= $name;
		$mail = new PHPMailer(true);
		try {
			$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
			$mail->isSMTP();                                            // Send using SMTP

		$mail->Host       = $config['emailHost'];                    // Set the SMTP server to send through
		$mail->SMTPAuth   = true;                                   // Enable SMTP authentication
		$mail->Username   = $config['userName'];                     // SMTP username
		$mail->Password   = $config['emailPWS'];                    // SMTP password
		// Set encryption based on port
		if ($config['emailPort'] == 465) {
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;        // SSL encryption for port 465
		} elseif ($config['emailPort'] == 587) {
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;     // TLS encryption for port 587
		}
		$mail->Port       = $config['emailPort'];                   // TCP port

			$mail->setFrom('support@sbsl.co.ke', 'Strategic Business Solutions Limited');
			$mail->addAddress($toEmail, $name); 						// Add a recipient
			$mail->addReplyTo('info@sbsl.co.ke', 'Strategic Business Solutions Limited');


			$mail->addBCC('felix.mauncho@sbsl.co.ke');

			// Content
			$mail->isHTML(true);                                     // Set email format to HTML
			$mail->Subject = $subject;
			$mail->Body    = $messageBody;
			$mail->AltBody = $bodyNohtml;
		 // $mail->AltBody    = $bodyNohtml; // optional, comment out and test
			// $mail->addAttachment('images/phpmailer_mini.png')


			$mail->send();
			// echo 'Message has been sent';
		} catch (Exception $e) {
			//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
			  $errors[]= 'Unable to send reset email';
		}


	}?>