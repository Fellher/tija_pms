<?php
/**
 * Notification Queue Processor
 * Processes pending email/SMS notifications from the queue
 *
 * @usage Run via cron job every 5 minutes:
 *        */5 * * * * php /path/to/process_notification_queue.php
 *
 * @version 1.0
 * @date 2025-10-21
 */

// Set up environment
session_start();
$base = '../../../';
set_include_path($base);

include 'php/includes.php';

// Configuration
$batchSize = 50; // Process up to 50 notifications per run
$maxAttempts = 3; // Maximum retry attempts
$retryDelay = 300; // Wait 5 minutes before retry (in seconds)

// Log start
$logMessage = "[" . date('Y-m-d H:i:s') . "] Starting notification queue processing...\n";
echo $logMessage;

try {
    // Get pending notifications from queue
    $sql = "SELECT q.*,
                   n.notificationTitle, n.notificationBody,
                   c.channelSlug, c.channelName
            FROM tija_notification_queue q
            INNER JOIN tija_notifications_enhanced n ON q.notificationID = n.notificationID
            INNER JOIN tija_notification_channels c ON q.channelID = c.channelID
            WHERE q.status = 'pending'
            AND (q.scheduledFor IS NULL OR q.scheduledFor <= NOW())
            AND (q.lastAttemptAt IS NULL OR TIMESTAMPDIFF(SECOND, q.lastAttemptAt, NOW()) >= ?)
            AND q.attempts < q.maxAttempts
            ORDER BY q.scheduledFor ASC, q.DateAdded ASC
            LIMIT ?";

    $pending = $DBConn->fetch_all_rows($sql, array(
        array($retryDelay, 'i'),
        array($batchSize, 'i')
    ));

    if (!$pending || count($pending) === 0) {
        echo "[" . date('Y-m-d H:i:s') . "] No pending notifications to process.\n";
        exit(0);
    }

    echo "[" . date('Y-m-d H:i:s') . "] Found " . count($pending) . " notification(s) to process.\n";

    $successCount = 0;
    $failCount = 0;

    foreach ($pending as $item) {
        // Update status to processing
        $updateSql = "UPDATE tija_notification_queue
                      SET status = 'processing',
                          attempts = attempts + 1,
                          lastAttemptAt = NOW()
                      WHERE queueID = ?";
        $DBConn->update_db_table($updateSql, array(array($item['queueID'], 'i')));

        $success = false;
        $errorMessage = null;

        try {
            // Process based on channel
            if ($item['channelSlug'] === 'email') {
                $success = sendEmail($item, $errorMessage);
            } elseif ($item['channelSlug'] === 'sms') {
                $success = sendSMS($item, $errorMessage);
            } elseif ($item['channelSlug'] === 'push') {
                $success = sendPushNotification($item, $errorMessage);
            }

            if ($success) {
                // Mark as sent
                $updateSql = "UPDATE tija_notification_queue
                              SET status = 'sent',
                                  sentAt = NOW(),
                                  errorMessage = NULL
                              WHERE queueID = ?";
                $DBConn->update_db_table($updateSql, array(array($item['queueID'], 'i')));

                // Log success
                $logSql = "INSERT INTO tija_notification_logs
                           (notificationID, queueID, eventID, channelID, userID, action, actionDetails)
                           SELECT notificationID, ?, eventID, channelID,
                                  (SELECT userID FROM tija_notifications_enhanced WHERE notificationID = ?),
                                  'sent',
                                  CONCAT('Successfully sent via ', ?)
                           FROM tija_notification_queue WHERE queueID = ?";
                $DBConn->insert_db_table($logSql, array(
                    array($item['queueID'], 'i'),
                    array($item['notificationID'], 'i'),
                    array($item['channelName'], 's'),
                    array($item['queueID'], 'i')
                ));

                $successCount++;
                echo "[" . date('Y-m-d H:i:s') . "] ✓ Sent {$item['channelSlug']} notification (Queue ID: {$item['queueID']})\n";
            } else {
                // Check if max attempts reached
                if ($item['attempts'] + 1 >= $item['maxAttempts']) {
                    // Mark as failed
                    $updateSql = "UPDATE tija_notification_queue
                                  SET status = 'failed',
                                      errorMessage = ?
                                  WHERE queueID = ?";
                    $DBConn->update_db_table($updateSql, array(
                        array($errorMessage, 's'),
                        array($item['queueID'], 'i')
                    ));

                    echo "[" . date('Y-m-d H:i:s') . "] ✗ Failed {$item['channelSlug']} notification after {$item['maxAttempts']} attempts (Queue ID: {$item['queueID']})\n";
                } else {
                    // Return to pending for retry
                    $updateSql = "UPDATE tija_notification_queue
                                  SET status = 'pending',
                                      errorMessage = ?
                                  WHERE queueID = ?";
                    $DBConn->update_db_table($updateSql, array(
                        array($errorMessage, 's'),
                        array($item['queueID'], 'i')
                    ));

                    echo "[" . date('Y-m-d H:i:s') . "] ⟳ Will retry {$item['channelSlug']} notification (Queue ID: {$item['queueID']})\n";
                }

                $failCount++;
            }

        } catch (Exception $e) {
            // Handle exception
            $errorMessage = $e->getMessage();

            $updateSql = "UPDATE tija_notification_queue
                          SET status = 'pending',
                              errorMessage = ?
                          WHERE queueID = ?";
            $DBConn->update_db_table($updateSql, array(
                array($errorMessage, 's'),
                array($item['queueID'], 'i')
            ));

            echo "[" . date('Y-m-d H:i:s') . "] ✗ Exception for Queue ID {$item['queueID']}: {$errorMessage}\n";
            $failCount++;
        }
    }

    echo "[" . date('Y-m-d H:i:s') . "] Processing complete. Success: {$successCount}, Failed: {$failCount}\n";

} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

/**
 * Send email notification
 */
function sendEmail($item, &$errorMessage) {
    global $config, $base;

    if (empty($item['recipientEmail'])) {
        $errorMessage = 'No recipient email address';
        return false;
    }

    try {
        // Check if PHPMailer is available
        if (file_exists($base . 'php/libraries/PHPMailer/PHPMailer.php')) {
            require_once $base . 'php/libraries/PHPMailer/PHPMailer.php';
            require_once $base . 'php/libraries/PHPMailer/SMTP.php';
            require_once $base . 'php/libraries/PHPMailer/Exception.php';

            $mail = new PHPMailer\PHPMailer\PHPMailer(true);

            // Server settings
            $mail->isSMTP();
            $mail->Host = $config['SMTPHost'] ?? 'localhost';
            $mail->SMTPAuth = isset($config['SMTPAuth']) ? $config['SMTPAuth'] : false;
            if ($mail->SMTPAuth) {
                $mail->Username = $config['SMTPUsername'] ?? '';
                $mail->Password = $config['SMTPPassword'] ?? '';
            }
            $mail->SMTPSecure = $config['SMTPSecure'] ?? 'tls';
            $mail->Port = $config['SMTPPort'] ?? 587;

            // Recipients
            $mail->setFrom($config['SMTPFrom'] ?? 'noreply@example.com', $config['SMTPFromName'] ?? 'PMS Notification System');
            $mail->addAddress($item['recipientEmail']);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $item['notificationTitle'];
            $mail->Body = $item['notificationBody'];
            $mail->AltBody = strip_tags($item['notificationBody']);

            $mail->send();
            return true;
        } else {
            // Fallback to PHP mail()
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . ($config['SMTPFrom'] ?? 'noreply@example.com') . "\r\n";

            $sent = mail(
                $item['recipientEmail'],
                $item['notificationTitle'],
                $item['notificationBody'],
                $headers
            );

            if (!$sent) {
                $errorMessage = 'mail() function returned false';
            }

            return $sent;
        }
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
        return false;
    }
}

/**
 * Send SMS notification
 */
function sendSMS($item, &$errorMessage) {
    global $config;

    if (empty($item['recipientPhone'])) {
        $errorMessage = 'No recipient phone number';
        return false;
    }

    // TODO: Implement SMS gateway integration
    // This is a placeholder - integrate with your SMS provider (Twilio, Africa's Talking, etc.)

    $errorMessage = 'SMS gateway not configured';
    return false;

    /* Example Twilio integration:
    try {
        $twilio = new \Twilio\Rest\Client($config['TwilioSID'], $config['TwilioToken']);
        $message = $twilio->messages->create(
            $item['recipientPhone'],
            array(
                'from' => $config['TwilioFrom'],
                'body' => strip_tags($item['notificationBody'])
            )
        );
        return true;
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
        return false;
    }
    */
}

/**
 * Send push notification
 */
function sendPushNotification($item, &$errorMessage) {
    // TODO: Implement push notification (Firebase Cloud Messaging, etc.)

    $errorMessage = 'Push notifications not implemented';
    return false;
}

