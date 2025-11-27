
<?php
session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';


include 'php/class_autoload.php';



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

$errors = array();
$details=array();
$changes= array();
$success="";
if ($isValidAdmin || $isValidUser) {
	var_dump($_POST);

   $email = (isset($_POST['userEmail']) && !empty($_POST['emuserEmailail'])) ?  trim(Form::validate_email ($_POST['userEmail'])) : "";

   $id = (isset($_POST['userID']) && !empty($_POST['userID'])) ?  Utility::clean_string($_POST['userID']): "";

   $userDetails= core::user(array('ID'=>$id), true, $DBConn);
   var_dump($userDetails);

   if ($userDetails) {

      if ($userDetails->NeedsToChangePassword != 'y') {
         $changes['NeedsToChangePassword'] ='y';
      }

      if ($userDetails->Valid != 'n') {
         $changes['Valid'] = 'n';
      }
      /*Update changes to person table*/
      if ($changes) {
         var_dump($changes);
         if (!$DBConn->update_table('people', $changes, array('ID'=>$userDetails->ID))) {
            $errors[]= "<span class='fst-italic text-center'> Failed to update changes to the database </span>";
         } else {
            echo $success= "Transaction successful. User {$userDetails->FirstName} has been set to change password on next login. A password reset email has been sent to {$userDetails->Email}";
         }
      }
      echo "<h4> Send Password Reset Email Errors</h4>";
      var_dump($errors);
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
            $email = $userDetails->Email;
            $name= Core::user_name($userDetails->ID, $DBConn);
            var_dump($base_url);

            $recoveryUrl = "{$base_url}/html/?p=password_reset&t1={$tokensArr[0]}&t2={$tokensArr[1]}&ID={$userDetails->ID}";
            $name= Core::user_name($userDetails->ID, $DBConn);
            $emailBody = "<h3> Hi {$name} </h3>
                     <p>Welcome to the {$config['siteName']}.  Please click on the link below to proceed and set a new password for your account </p>
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
                              "Welcome to the {$config['siteName']}. Please click on the link below to proceed and set a new password for your account". PHP_EOL .
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
               $mail->Host       = $config['emailHost'];                   // SMTP host
               $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
               $mail->Username   = $config['userName'];                    // SMTP username
               $mail->Password   = $config['emailPWS'];                    // SMTP password

               // Set encryption based on port
               if ($config['emailPort'] == 465) {
                  $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;         // SSL encryption for port 465
               } elseif ($config['emailPort'] == 587) {
                  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;      // TLS encryption for port 587
               }
               $mail->Port       = $config['emailPort'];                   // TCP port

                  // SSL options for Microsoft 365
                  // Note: Microsoft 365 uses valid SSL certificates, but allowing self-signed as requested
                  $mail->SMTPOptions = array(
                      'ssl' => array(
                          'verify_peer' => true,
                          'verify_peer_name' => true,
                          'allow_self_signed' => true                         // Allow self-signed certificates
                      )
                  );
                  $mail->Timeout = 30;                                        // Increase timeout to 30 seconds

                  $mail->setFrom($config['siteEmail'], $config['siteName']);
                  $mail->addAddress($email, $name);     								// Add a recipient
                  $mail->addReplyTo($config['siteEmail'], $config['siteName']);
                  // $mail->addBCC($config['secondaryEmail'], $config['siteName']);
                  // Attachments
                  //$mail->addAttachment('/var/tmp/file.tar.gz');         		// Add attachments
                  //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    		// Optional name

                  $mail->isHTML(true);                                 			// Set email format to HTML
                  $mail->Subject = $subject;
                  $mail->Body    = $emailBody;
                  // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
                    $mail->AltBody    = $emailNoHtml; 									// optional, comment out and test

                    $mail->send();
                    echo "<div class='alert alert-success'>Password reset email sent successfully to {$email}</div>";
               } catch (Exception $e) {
                  var_dump($e);
                  echo "<div class='alert alert-danger'>SMTP Error: {$mail->ErrorInfo}</div>";
                  echo "<div class='alert alert-warning'>Detailed error: " . $e->getMessage() . "</div>";
                  $errors[]= "Unable to send reset Email. Error. {$mail->ErrorInfo}";

               }
            }
         }
      }
   } else {
      $errors[]="<span class='d-block text-center fst-italic'>  A user with the above email ID does not exist in the database. Please Submit valid Email ID</span>";
   }

   echo "<h4> Send Password Reset Email Errors</h4>";
   var_dump($errors);

  $returnURL= Utility::returnURL($_SESSION['returnURL'], 's=admin&ss=performancep=home');
  var_dump($returnURL);


} else {
  $errors[] = 'You need to log in as a valid administrator to do that.';
}

if (count($errors) == 0) {
  $DBConn->commit();
  $messages = array(array('Text'=>"{$success}", 'Type'=>'success'));
} else {
   $DBConn->rollback();
   $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
}
$_SESSION['FlashMessages'] = serialize($messages);
header("location:{$base}html/{$returnURL}");