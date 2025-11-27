<?php
/**
 * Manage Organization Admin
 * Handles creating/updating organization administrators
 *
 * @package Tija Practice Management System
 * @subpackage Admin
 */

// Prevent direct output of errors
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
$base = '../../../../';
set_include_path($base);

include_once 'php/includes.php';
include_once 'php/class_autoload.php';

include_once 'php/config/config.inc.php';

include_once 'php/scripts/db_connect.php';



use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


require $base .'php/classes/Exception.php';
require $base .'php/classes/phpmailer.php';
require $base .'php/classes/smtp.php';



$DBConn->begin();
$errors = array();
$details=array();
$changes= array();
$success="";
$userDetails =array();
if ($isValidAdmin || ($isAdmin && $isSuperAdmin)) {
	var_dump($_POST);

   // Check if this is a restricted edit (only role, entity, unit, options)
   $action = (isset($_POST['action']) && $_POST['action'] != '') ? Utility::clean_string($_POST['action']) : '';
   $adminID = (isset($_POST['adminID']) && $_POST['adminID'] != '') ? Utility::clean_string($_POST['adminID']) : '';

   $orgDataID = (isset($_POST['orgDataID']) && $_POST['orgDataID'] != '') ? Utility::clean_string($_POST['orgDataID']) : '';
   $userID = (isset($_POST['userID']) && $_POST['userID'] != '') ? Utility::clean_string($_POST['userID']) : '';
   $adminSelect = (isset($_POST['adminSelect']) && $_POST['adminSelect'] != '') ? Utility::clean_string($_POST['adminSelect']) : '';
   $adminTypeID = (isset($_POST['adminTypeID']) && $_POST['adminTypeID'] != '') ? Utility::clean_string($_POST['adminTypeID']) : '';
   $isEmployee = (isset($_POST['isEmployee']) && $_POST['isEmployee'] != '') ? Utility::clean_string($_POST['isEmployee']) : 'N';
   $entityID = (isset($_POST['entityID']) && $_POST['entityID'] != '') ? Utility::clean_string($_POST['entityID']) : '';
   $unitTypeID = (isset($_POST['unitTypeID']) && $_POST['unitTypeID'] != '') ? Utility::clean_string($_POST['unitTypeID']) : '';
   $unitID = (isset($_POST['unitID']) && $_POST['unitID'] != '') ? Utility::clean_string($_POST['unitID']) : '';

   // Handle restricted edit (update_restricted action)
   if ($action == 'update_restricted' && $adminID) {
      // Verify admin exists and get current userID (cannot be changed)
      $currentAdmin = Core::app_administrators(array('adminID' => $adminID), true, $DBConn);

      if (!$currentAdmin) {
         $errors[] = "Administrator not found.";
      } else {
         // Ensure userID cannot be changed - use existing one
         $userID = $currentAdmin->userID;
         $orgDataID = $currentAdmin->orgDataID; // Also preserve organization

         // Prepare update data (only allowed fields)
         $updateData = array(
            'adminTypeID' => $adminTypeID ?: null,
            'entityID' => $entityID ?: null,
            'unitTypeID' => $unitTypeID ?: null,
            'unitID' => $unitID ?: null,
            'isEmployee' => $isEmployee,
            'LastUpdate' => date('Y-m-d H:i:s')
         );

         // Update administrator record
         $whereArr = array('adminID' => $adminID);
         if (!$DBConn->update_table('tija_administrators', $updateData, $whereArr)) {
            $errors[] = "Error updating administrator role and assignment";
         } else {
            $success = "Administrator role and assignment updated successfully";
         }
      }
   } else {


   if(!$userID) {
      $FirstName = (isset($_POST['FirstName']) && $_POST['FirstName'] != '') ? $userDetails['FirstName'] =Utility::clean_string($_POST['FirstName']) : $errors[]="First Name is required";
      $Surname = (isset($_POST['Surname']) && $_POST['Surname'] != '') ? $userDetails['Surname'] =Utility::clean_string($_POST['Surname']) : $errors[]="Last Name is required";
      $OtherNames = (isset($_POST['OtherNames']) && $_POST['OtherNames'] != '') ? $userDetails['OtherNames'] =Utility::clean_string($_POST['OtherNames']) :"";
      $Email = (isset($_POST['Email']) && $_POST['Email'] != '') ? $userDetails['Email'] =Utility::clean_string($_POST['Email']) : $errors[]="Email is required";


      if(!$errors) {
         if($userDetails){
            if(!$DBConn->insert_data('people', $userDetails)){
               $errors[] = "Error creating user account";
            } else {
               $userID = $DBConn->lastInsertId();
               $success = "User account created successfully";
               $tokens = Core::add_registration_tokens($userID, $DBConn);
               if($isEmployee == 'Y'){
                 $employeeDetails = array('ID'=>$userID, $UID=bin2hex(openssl_cipher_iv_length(32)), 'orgDataID'=>$orgDataID);
                  if(!$DBConn->insert_data('user_details', $employeeDetails)){
                     $errors[] = "Error creating employee account";
                  } else {
                     $success .= " and Employee account created successfully";
                  }
               }

               if ($tokens) {
                  var_dump($tokens);
                  $tokensArr =array($tokens[0], $tokens[1]);
               } else {
                  $regtokens = Core::add_registration_tokens($userID, $DBConn);
                  $tokensArr=array($regtokens[0], $regtokens[1]);
                  $tokens = Core::tokens(array('ID'=>$regtokens[2]), true, $DBConn);
               }

               if($tokensArr){
                  $base_url = ( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ? 'https' : 'http' ) . '://' .  $_SERVER['HTTP_HOST'];

                  var_dump($base_url);
                 if ((preg_match('/localhost/', $_SERVER['HTTP_HOST'])) || (preg_match('/liveprojects/', $_SERVER['HTTP_HOST'])) ){
                    $base_url="{$config['siteURL']}";
                 }
                 $email = $Email;
                 $name= Core::user_name($userID, $DBConn);
                 var_dump($base_url);

                 $recoveryUrl = "{$base_url}/html/?p=password_reset&t1={$tokensArr[0]}&t2={$tokensArr[1]}&ID={$userID}";

                 $emailBody = "<h3> Hi {$name} </h3>
                          <p>Welcome to {$config['siteName']}. You have been added as an admin to the {$config['siteName']} Portal. Please click on the link below to proceed and set a new password for your account </p>
                          <div style='margin-top:20px; text-align:center; '>
                             <a href='{$recoveryUrl}' style='display:block; text-decoration:none; padding:20px 30px; border:solid 1px gray;  line-height:30px;width: 500px;border-radius:10px; -webkit-font-smoothing: antialiased; color:grey;font-weight: 600;  text-align:center;  margin:50px auto'> Reset Password</a>
                          </div>
                          <div style='text-align:center; margin-bottom-30px'>
                              <p style='margin-bottom:20px'> If the above link does not work, copy and paste this URL into your browser </p>
                              {$recoveryUrl}
                              <p style='font-size:14px'>
                                 If you experience any issues with the reset, please
                                 <a href='{$config['siteURL']}html/?p=contact_us&ID={$userID}' >contact us</a>.
                              </p>
                           </div>
                           <div style='text-align:center; margin-bottom-30px'>
                               <p style='font-size=22px'> <em> Regards</p>
                               <p style'font-size:16px> {$config['siteName']}</em> </p>
                            </div>";

                       $emailNoHtml = "Hello {$name}" . PHP_EOL .
                                   "Welcome to {$config['siteName']}. You have been added as an admin to the {$config['siteName']} Portal. Please click on the link below to proceed and set a new password for your account". PHP_EOL .
                                   "Copy and paste this URL into your browser and start the pasword set/reset process ". PHP_EOL .
                                   "{$recoveryUrl}".PHP_EOL  .PHP_EOL  .

                                   'Regards' .PHP_EOL  .PHP_EOL  .
                                   "{$config['siteName']}";
                 echo $emailBody;
                 $send= true;

                 if ($send) {
                  $mail = new PHPMailer(true);
                  $subject = "{$config['siteName']} Account Password Set/Re-set";

                  function send_email($mail, $config, $email, $name, $subject, $emailBody, $emailNoHtml) {
                     $errors = [];
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
                          $success = true; // Email sent successfully
                     } catch (Exception $e) {
                        $errors[] = "Unable to send reset Email. Error. {$mail->ErrorInfo}";
                        $success = false; // Email sending failed
                     }
                     return  array('success' => $success, 'errors' => $errors);
                  }

                  $sendState = send_email($mail, $config, $email, $name, $subject, $emailBody, $emailNoHtml);

                  var_dump($sendState);
                  if ($sendState['success']) {
                     $success .= " and Password set/reset email sent successfully";
                  } else {
                     $errors[] = "Error sending password set/reset email";
                     var_dump($sendState['errors']);
                  }
                  $detailsEmail = array('Email'=>$email, 'Name'=>$name, 'Subject'=>"{$config['siteName']} Account Password Set/Re-set", 'Body'=>$emailBody, 'BodyNoHtml'=>$emailNoHtml);
                  var_dump($detailsEmail);
                     echo "<h5> Send Password Set/Re-set Email</h5>";

                     // $emailSent=Core::send_email_php_mailer($detailsEmail, $config, $DBConn);
                     // if($core::send_email_php_mailer($detailsEmail, $config, $DBConn)){
                     //    $success .= " and Password set/reset email sent successfully";
                     // } else {
                     //    $errors[] = "Error sending password set/reset email";
                     // }
                 }

               }
            }
         }
      }
      if(!$errors){
         $adminDetails= array('adminTypeID'=>$adminTypeID, 'userID'=>$userID, 'orgDataID'=>$orgDataID, 'isEmployee'=>$isEmployee, 'entityID'=>$entityID, 'unitTypeID'=>$unitTypeID, 'unitID'=>$unitID);
         if(!$DBConn->insert_data('tija_administrators', $adminDetails)){
            $errors[] = "Error creating admin account";
         } else {
            $success .= " and Admin account created successfully";
         }
      }
   } else {
      $userDetails = array('adminTypeID'=>$adminTypeID, 'isEmployee'=>$isEmployee);

      var_dump($userDetails);
      if($adminSelect == 'existing'){
         $userDetails['adminTypeID'] = $adminTypeID;
         $userDetails['isEmployee'] = 'Y';
         $userDetails['orgDataID'] = $orgDataID;
         $userDetails['entityID'] = $entityID;
         $userDetails['unitTypeID'] = $unitTypeID;
         $userDetails['unitID'] = $unitID;
         $userDetails['userID'] = $userID;
      } else {
         $userDetails['adminTypeID'] = null;
         $userDetails['isEmployee'] = 'N';
         $userDetails['orgDataID'] = null;
         $userDetails['entityID'] = null;
         $userDetails['unitTypeID'] = null;
         $userDetails['unitID'] = null;
      }

      var_dump($userDetails);
      if(!$DBConn->insert_data('tija_administrators', $userDetails)){
         $errors[] = "Error updating admin account";
      } else {
         $success = "admin account updated successfully";

      }
   }
   } // End else block for non-restricted edits

} else {
   $errors[] = "You are not authorized to perform this action.";
}
$returnURL= Utility::returnURL($_SESSION['returnURL'], "s=core&ss=admin&p=tenant_details&orgDataID={$orgDataID}");
// $returnURL = "?s=core&ss=admin&p=tenant_details&orgDataID={$orgDataID}";

if (count($errors) == 0) {
  $DBConn->commit();
  $messages = array(array('Text'=>"{$success}", 'Type'=>'success'));
} else {
   $DBConn->rollback();
   $messages = array_map(function($error){ return array('Text'=>$error, 'Type'=>'danger'); }, $errors);
}
$_SESSION['FlashMessages'] = serialize($messages);
header("location:{$base}html/{$returnURL}");?>
