<?php
/**
 * Bulk Reset Emails Script
 *
 * Sends password reset emails to multiple employees at once
 * Accessible by admins and HR managers only
 */

session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';
include 'php/class_autoload.php';
include 'php/scripts/db_connect.php';

// Set JSON header
header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require $base . 'php/classes/Exception.php';
require $base . 'php/classes/phpmailer.php';
require $base . 'php/classes/smtp.php';

$response = [
    'success' => false,
    'message' => '',
    'sentCount' => 0,
    'failedCount' => 0,
    'failedEmails' => []
];

// Check authorization
if (!$isValidAdmin && !$isHRManager && !$isAdmin && !$isSuperAdmin && !$isTenantAdmin) {
    $response['success'] = false;
    $response['message'] = 'You do not have permission to perform this action.';
    echo json_encode($response);
    exit();
}

// Get entity ID
$entityID = isset($_POST['entityID']) ? intval($_POST['entityID']) : 0;
if ($entityID <= 0) {
    $response['message'] = 'Invalid entity ID.';
    echo json_encode($response);
    exit;
}

// Get employee IDs
$employeeIDs = isset($_POST['employeeIDs']) && is_array($_POST['employeeIDs'])
    ? array_map('intval', $_POST['employeeIDs'])
    : [];

if (empty($employeeIDs)) {
    $response['message'] = 'No employees selected.';
    echo json_encode($response);
    exit;
}

// If sending to all employees, fetch all employee IDs for the entity
$isAllEmployees = isset($_POST['isAllEmployees']) && $_POST['isAllEmployees'] == '1';
if ($isAllEmployees) {
    $allEntityEmployees = Employee::employees(['entityID' => $entityID, 'Suspended' => 'N'], false, $DBConn);
    if ($allEntityEmployees && is_array($allEntityEmployees)) {
        $employeeIDs = array_map(function($emp) {
            return intval($emp->ID);
        }, $allEntityEmployees);
    }
}

// Remove duplicates and invalid IDs
$employeeIDs = array_unique(array_filter($employeeIDs, function($id) {
    return $id > 0;
}));

if (empty($employeeIDs)) {
    $response['message'] = 'No valid employees found.';
    echo json_encode($response);
    exit;
}

$DBConn->begin();

$sentCount = 0;
$failedCount = 0;
$failedEmails = [];

foreach ($employeeIDs as $employeeID) {
    try {
        // Get user details
        $userDetails = Core::user(['ID' => $employeeID], true, $DBConn);

        if (!$userDetails || !$userDetails->Email) {
            $failedCount++;
            $failedEmails[] = ['id' => $employeeID, 'email' => 'N/A', 'reason' => 'Employee not found or no email'];
            continue;
        }

        $email = trim($userDetails->Email);
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $failedCount++;
            $failedEmails[] = ['id' => $employeeID, 'email' => $email, 'reason' => 'Invalid email address'];
            continue;
        }

        // Update user to require password change
        $changes = [];
        if ($userDetails->NeedsToChangePassword != 'y') {
            $changes['NeedsToChangePassword'] = 'y';
        }
        if ($userDetails->Valid != 'n') {
            $changes['Valid'] = 'n';
        }

        if (!empty($changes)) {
            if (!$DBConn->update_table('people', $changes, ['ID' => $userDetails->ID])) {
                $failedCount++;
                $failedEmails[] = ['id' => $employeeID, 'email' => $email, 'reason' => 'Failed to update user record'];
                continue;
            }
        }

        // Get or create tokens
        $tokens = Core::tokens(['PersonID' => $userDetails->ID], true, $DBConn);
        if ($tokens) {
            $tokensArr = [$tokens->Token1, $tokens->Token2];
        } else {
            $regtokens = Core::add_registration_tokens($userDetails->ID, $DBConn);
            $tokensArr = [$regtokens[0], $regtokens[1]];
            $tokens = Core::tokens(['ID' => $regtokens[2]], true, $DBConn);
        }

        if (!$tokensArr || !$tokens) {
            $failedCount++;
            $failedEmails[] = ['id' => $employeeID, 'email' => $email, 'reason' => 'Failed to generate reset tokens'];
            continue;
        }

        // Update token to require password set
        $DBConn->update_table('registration_tokens', ['PasswordSet' => 'n'], ['ID' => $tokens->ID]);

        // Build reset URL
        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        if (preg_match('/localhost/', $_SERVER['HTTP_HOST']) || preg_match('/liveprojects/', $_SERVER['HTTP_HOST'])) {
            $base_url = $config['siteURL'];
        }

        $recoveryUrl = "{$base_url}/html/?p=password_reset&t1={$tokensArr[0]}&t2={$tokensArr[1]}&ID={$userDetails->ID}";
        $name = Core::user_name($userDetails->ID, $DBConn);

        // Build email content
        $emailBody = "<h3>Hi {$name}</h3>
                     <p>Welcome to the {$config['siteName']}. Please click on the link below to proceed and set a new password for your account.</p>
                     <div style='margin-top:20px; text-align:center;'>
                        <a href='{$recoveryUrl}' style='display:block; text-decoration:none; padding:20px 30px; border:solid 1px gray; line-height:30px;width: 500px;border-radius:10px; -webkit-font-smoothing: antialiased; color:grey;font-weight: 600; text-align:center; margin:50px auto'>Reset Password</a>
                     </div>
                     <div style='text-align:center; margin-bottom:30px'>
                         <p style='margin-bottom:20px'>If the above link does not work, copy and paste this URL into your browser:</p>
                         {$recoveryUrl}
                         <p style='font-size:14px'>
                            If you experience any issues with the reset, please
                            <a href='{$config['siteURL']}html/?p=contact_us&ID={$userDetails->ID}'>contact us</a>.
                         </p>
                      </div>
                      <div style='text-align:center; margin-bottom:30px'>
                          <p style='font-size:22px'><em>Regards</p>
                          <p style='font-size:16px'>{$config['siteName']}</em></p>
                       </div>";

        $emailNoHtml = "Hello {$name}" . PHP_EOL .
                      "Welcome to the {$config['siteName']}. Please click on the link below to proceed and set a new password for your account." . PHP_EOL .
                      "Copy and paste this URL into your browser and start the reset process: " . PHP_EOL .
                      "{$recoveryUrl}" . PHP_EOL . PHP_EOL .
                      "Regards" . PHP_EOL . PHP_EOL .
                      "{$config['siteName']}";

        // Send email
        $mail = new PHPMailer(true);
        $subject = "{$config['siteName']} Account Password Reset";

        try {
            $mail->isSMTP();
            $mail->Host = $config['emailHost'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['userName'];
            $mail->Password = $config['emailPWS'];

            // Set encryption based on port
            if ($config['emailPort'] == 465) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($config['emailPort'] == 587) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            $mail->Port = $config['emailPort'];

            // SSL options
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'allow_self_signed' => true
                ]
            ];
            $mail->Timeout = 30;

            $mail->setFrom($config['siteEmail'], $config['siteName']);
            $mail->addAddress($email, $name);
            $mail->addReplyTo($config['siteEmail'], $config['siteName']);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $emailBody;
            $mail->AltBody = $emailNoHtml;

            $mail->send();
            $sentCount++;

        } catch (Exception $e) {
            $failedCount++;
            $failedEmails[] = [
                'id' => $employeeID,
                'email' => $email,
                'reason' => 'Email sending failed: ' . $mail->ErrorInfo
            ];
        }

    } catch (Exception $e) {
        $failedCount++;
        $failedEmails[] = [
            'id' => $employeeID,
            'email' => 'N/A',
            'reason' => 'Error: ' . $e->getMessage()
        ];
    }
}

// Commit transaction
if ($sentCount > 0) {
    $DBConn->commit();
} else {
    $DBConn->rollback();
}

$response['success'] = $sentCount > 0;
$response['sentCount'] = $sentCount;
$response['failedCount'] = $failedCount;
$response['failedEmails'] = $failedEmails;
$response['message'] = $sentCount > 0
    ? "Successfully sent {$sentCount} reset email(s)." . ($failedCount > 0 ? " {$failedCount} failed." : '')
    : "Failed to send reset emails.";

echo json_encode($response);

