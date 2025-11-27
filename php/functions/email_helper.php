<?php
/**
 * Email Helper Functions
 * Provides reusable email configuration for PHPMailer
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * Configure PHPMailer instance with settings from config
 *
 * @param PHPMailer $mail PHPMailer instance to configure
 * @param array $config Configuration array
 * @param bool $debug Enable debug output (default: false)
 * @return PHPMailer Configured PHPMailer instance
 */
function configure_phpmailer($mail, $config, $debug = false) {
    // Server settings
    $mail->SMTPDebug = $debug ? SMTP::DEBUG_SERVER : SMTP::DEBUG_OFF;
    $mail->isSMTP();
    $mail->Host = $config['emailHost'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['userName'];
    $mail->Password = $config['emailPWS'];

    // Set encryption based on port
    if ($config['emailPort'] == 465) {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL encryption for port 465
    } elseif ($config['emailPort'] == 587) {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS encryption for port 587
    }
    // No encryption for port 25 (or other ports)

    $mail->Port = $config['emailPort'];

    // SSL options - allow self-signed certificates
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    $mail->Timeout = 30; // Increase timeout to 30 seconds

    // Set default From address
    $mail->setFrom($config['siteEmail'], $config['siteName']);

    // Add BCC to secondary email if configured
    if (isset($config['secondaryEmail']) && !empty($config['secondaryEmail'])) {
        $mail->addBCC($config['secondaryEmail']);
    }

    return $mail;
}

/**
 * Send a simple email using PHPMailer
 *
 * @param string $to Recipient email address
 * @param string $toName Recipient name
 * @param string $subject Email subject
 * @param string $bodyHtml HTML body content
 * @param string $bodyText Plain text body content
 * @param array $config Configuration array
 * @param bool $debug Enable debug output
 * @return array ['success' => bool, 'message' => string]
 */
function send_email($to, $toName, $subject, $bodyHtml, $bodyText = '', $config = null, $debug = false) {
    global $config as $globalConfig;

    if ($config === null) {
        $config = $globalConfig;
    }

    require_once __DIR__ . '/../classes/Exception.php';
    require_once __DIR__ . '/../classes/phpmailer.php';
    require_once __DIR__ . '/../classes/smtp.php';

    $mail = new PHPMailer(true);

    try {
        // Configure PHPMailer
        configure_phpmailer($mail, $config, $debug);

        // Recipients
        $mail->addAddress($to, $toName);
        $mail->addReplyTo($config['siteEmail'], $config['siteName']);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $bodyHtml;

        if (!empty($bodyText)) {
            $mail->AltBody = $bodyText;
        }

        $mail->send();

        return [
            'success' => true,
            'message' => 'Email sent successfully'
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => "Email could not be sent. Error: {$mail->ErrorInfo}"
        ];
    }
}
?>

