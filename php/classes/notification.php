<?php
/**
 * Notification Class
 * Comprehensive notification system for all modules
 *
 * @version 1.0
 * @date 2025-10-21
 */

// Use PHPMailer namespace if available
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Notification {

    /**
     * Cache flags for entity preference table existence checks.
     */
    private static $entityPreferenceTableChecked = false;
    private static $entityPreferenceTableExists = false;

    /**
     * Create and dispatch a notification
     *
     * @param array $params Notification parameters
     *   - eventSlug (required): Event identifier (e.g., 'leave_application_submitted')
     *   - userId (required): Recipient user ID
     *   - originatorId (optional): User who triggered the event
     *   - data (required): Array of template variables
     *   - link (optional): Deep link URL (relative)
     *   - priority (optional): low, medium, high, critical (default: from event)
     *   - entityID (optional): Entity ID for context
     *   - orgDataID (optional): Organization data ID
     *   - segmentType (optional): Type of related record (e.g., 'leave_application')
     *   - segmentID (optional): ID of related record
     *   - channels (optional): Array of channels to use, or null for user preferences
     *   - scheduleFor (optional): DateTime to schedule notification (default: immediate)
     * @param object $DBConn Database connection
     * @return array Success status and notification ID(s)
     */
    public static function create($params, $DBConn) {
        try {
            // Debug: Log notification creation attempt
            error_log("Notification::create - Starting. EventSlug: " . ($params['eventSlug'] ?? 'NOT SET') . ", UserID: " . ($params['userId'] ?? 'NOT SET'));

            // Validate required parameters
            if (empty($params['eventSlug']) || empty($params['userId']) || empty($params['data'])) {
                $missing = array();
                if (empty($params['eventSlug'])) $missing[] = 'eventSlug';
                if (empty($params['userId'])) $missing[] = 'userId';
                if (empty($params['data'])) $missing[] = 'data';

                error_log("Notification::create - ERROR: Missing required parameters: " . implode(', ', $missing));
                return array(
                    'success' => false,
                    'message' => 'Missing required parameters: ' . implode(', ', $missing)
                );
            }

            // Get event details
            $event = self::getEventBySlug($params['eventSlug'], $DBConn);
            if (!$event) {
                // Log the error for debugging
                error_log("Notification::create - ERROR: Event not found: {$params['eventSlug']}. User: {$params['userId']}");
                error_log("Notification::create - Checking if tija_notification_events table exists...");
                $tableCheck = $DBConn->fetch_all_rows("SHOW TABLES LIKE 'tija_notification_events'", array());
                if (!$tableCheck || count($tableCheck) === 0) {
                    error_log("Notification::create - ERROR: Table 'tija_notification_events' does not exist!");
                } else {
                    error_log("Notification::create - Table exists. Checking for event slug...");
                    $allEvents = $DBConn->fetch_all_rows("SELECT eventSlug FROM tija_notification_events", array());
                    error_log("Notification::create - Available events: " . print_r($allEvents, true));
                }
                return array(
                    'success' => false,
                    'message' => 'Event not found: ' . $params['eventSlug']
                );
            }

            error_log("Notification::create - Event found. EventID: " . ($event['eventID'] ?? 'N/A') . ", isActive: " . ($event['isActive'] ?? 'N/A'));

            // Check if event is active
            if ($event['isActive'] !== 'Y' || $event['Suspended'] === 'Y') {
                // Log the error for debugging
                error_log("Notification::create - ERROR: Event not active: {$params['eventSlug']}. isActive: {$event['isActive']}, Suspended: {$event['Suspended']}. User: {$params['userId']}");
                return array(
                    'success' => false,
                    'message' => 'Event is not active'
                );
            }

            error_log("Notification::create - Event is active. Proceeding...");

            // Enrich template variables with useful defaults (site URL, absolute links, etc.)
            $payloadData = isset($params['data']) ? $params['data'] : array();
            $params['data'] = self::enrichTemplateData(
                is_array($payloadData) ? $payloadData : (array)$payloadData,
                isset($params['link']) ? $params['link'] : null
            );

            // Get channels to use (explicit override or user preference)
            $channels = isset($params['channels']) && is_array($params['channels'])
                ? $params['channels']
                : self::getUserChannelsForEvent($params['userId'], $event['eventID'], $DBConn);

            error_log("Notification::create - Initial channels from user preferences: " . json_encode($channels));

            // Merge entity-level preferences
            $entityChannelPreference = self::getEntityChannelsForEvent(
                isset($params['entityID']) ? (int)$params['entityID'] : null,
                $event['eventID'],
                $DBConn
            );

            error_log("Notification::create - Entity channel preferences: " . json_encode($entityChannelPreference));

            // First, merge enforced channels (these override user preferences)
            if (!empty($entityChannelPreference['enforced'])) {
                $channels = array_values(array_unique(array_merge(
                    is_array($channels) ? $channels : array(),
                    $entityChannelPreference['enforced']
                )));
                error_log("Notification::create - Channels after merging enforced entity preferences: " . json_encode($channels));
            }

            // Then, merge allowed channels (add them to user preferences if not already included)
            // This ensures both in-app and email are sent when entity allows email
            if (!empty($entityChannelPreference['allowed'])) {
                $currentChannels = is_array($channels) ? $channels : array();
                $mergedChannels = array_values(array_unique(array_merge(
                    $currentChannels,
                    $entityChannelPreference['allowed']
                )));

                // Only update if we actually added new channels
                if (count($mergedChannels) > count($currentChannels)) {
                    $channels = $mergedChannels;
                    error_log("Notification::create - Channels after merging entity allowed channels: " . json_encode($channels));
                } else {
                    error_log("Notification::create - Entity allowed channels already included in user preferences");
                }
            }

            // Fallback: if no channels at all, use entity allowed channels
            if ((empty($channels) || count($channels) === 0) && !empty($entityChannelPreference['allowed'])) {
                $channels = $entityChannelPreference['allowed'];
                error_log("Notification::create - Using entity allowed channels as fallback: " . json_encode($channels));
            }

            if (empty($channels)) {
                // Default to in-app only
                $inAppChannel = self::getChannelBySlug('in_app', $DBConn);
                if ($inAppChannel) {
                    $channels = array($inAppChannel['channelID']);
                    error_log("Notification::create - Using default in-app channel: " . json_encode($channels));
                }
            }

            // Log final channel selection with channel names for debugging
            $channelDetails = array();
            foreach ($channels as $chID) {
                $ch = self::getChannelById($chID, $DBConn);
                if ($ch) {
                    $channelDetails[] = $ch['channelSlug'] . ' (ID: ' . $chID . ')';
                }
            }
            error_log("Notification::create - Final channels selected: " . implode(', ', $channelDetails) . " for event '{$params['eventSlug']}', userID: {$params['userId']}, entityID: " . (isset($params['entityID']) ? $params['entityID'] : 'N/A'));

            $notifications = array();
            $success = true;

            // Create notification for each channel
            foreach ($channels as $channelID) {
                // Get or use channel ID
                if (is_array($channelID)) {
                    $channelID = $channelID['channelID'];
                }

                // Get channel details first to know the channel slug
                $channel = self::getChannelById($channelID, $DBConn);
                $channelSlug = $channel ? $channel['channelSlug'] : 'unknown';

                error_log("Notification::create - Processing channel: {$channelSlug} (ID: {$channelID})");

                // Get template for this event/channel combination
                $template = self::getTemplate($event['eventID'], $channelID, $params, $DBConn);

                if (!$template) {
                    error_log("Notification::create - WARNING: No template found for eventID: {$event['eventID']}, channelID: {$channelID} (channelSlug: {$channelSlug}), eventSlug: {$params['eventSlug']}.");

                    // For email channel, use in-app template as fallback
                    if ($channel && $channel['channelSlug'] === 'email') {
                        error_log("Notification::create - INFO: Email template missing, using in-app template as fallback");
                        $inAppChannel = self::getChannelBySlug('in_app', $DBConn);
                        if ($inAppChannel) {
                            $template = self::getTemplate($event['eventID'], $inAppChannel['channelID'], $params, $DBConn);
                            if ($template) {
                                error_log("Notification::create - Using in-app template (ID: {$template['templateID']}) as fallback for email");
                            }
                        }

                        // If still no template, create a basic one
                        if (!$template) {
                            $template = array(
                                'templateSubject' => $event['eventName'] ?? 'Notification',
                                'templateBody' => '<p>You have a new notification: {{employee_name}} - {{leave_type}}</p>'
                            );
                            error_log("Notification::create - Created basic email template as fallback");
                        }
                    } else {
                        error_log("Notification::create - Skipping channel {$channelSlug} due to missing template");
                        continue; // Skip this channel if no template
                    }
                }

                if ($template) {
                    error_log("Notification::create - Template found/created. TemplateID: " . ($template['templateID'] ?? 'FALLBACK'));
                    // Render template with variables
                    $rendered = self::renderTemplate($template, $params['data']);
                } else {
                    error_log("Notification::create - ERROR: Could not create template for channel");
                    continue;
                }

                // Determine priority
                $priority = isset($params['priority']) ? $params['priority'] : $event['priorityLevel'];

                // Build notification link
                $link = isset($params['link']) ? $params['link'] : null;

                // Channel details already retrieved above

                // Create notification record
                if ($channel && $channel['channelSlug'] === 'in_app') {
                    // In-app notification - store in main table
                    $notifID = self::createInAppNotification(array(
                        'eventID' => $event['eventID'],
                        'userID' => $params['userId'],
                        'originatorUserID' => isset($params['originatorId']) ? $params['originatorId'] : null,
                        'entityID' => isset($params['entityID']) ? $params['entityID'] : null,
                        'orgDataID' => isset($params['orgDataID']) ? $params['orgDataID'] : null,
                        'segmentType' => isset($params['segmentType']) ? $params['segmentType'] : null,
                        'segmentID' => isset($params['segmentID']) ? $params['segmentID'] : null,
                        'notificationTitle' => $rendered['subject'],
                        'notificationBody' => $rendered['body'],
                        'notificationData' => json_encode($params['data']),
                        'notificationLink' => $link,
                        'notificationIcon' => $event['moduleIcon'] ?? 'ri-notification-line',
                        'priority' => $priority
                    ), $DBConn);

                    if ($notifID) {
                        error_log("Notification::create - SUCCESS: In-app notification created with ID: {$notifID}");
                        $notifications[] = array('channelSlug' => 'in_app', 'notificationID' => $notifID);

                        // Log the creation
                        try {
                            self::logAction(array(
                                'notificationID' => $notifID,
                                'eventID' => $event['eventID'],
                                'channelID' => $channelID,
                                'userID' => $params['userId'],
                                'action' => 'created',
                                'actionDetails' => 'In-app notification created'
                            ), $DBConn);
                        } catch (Exception $logError) {
                            error_log("Notification::create - WARNING: Failed to log action: " . $logError->getMessage());
                        }
                    } else {
                        error_log("Notification::create - ERROR: Failed to create in-app notification. createInAppNotification returned false.");
                        $success = false;
                    }
                } else {
                    // Email/SMS/Push - create notification record first
                    $notifID = self::createInAppNotification(array(
                        'eventID' => $event['eventID'],
                        'userID' => $params['userId'],
                        'originatorUserID' => isset($params['originatorId']) ? $params['originatorId'] : null,
                        'entityID' => isset($params['entityID']) ? $params['entityID'] : null,
                        'orgDataID' => isset($params['orgDataID']) ? $params['orgDataID'] : null,
                        'segmentType' => isset($params['segmentType']) ? $params['segmentType'] : null,
                        'segmentID' => isset($params['segmentID']) ? $params['segmentID'] : null,
                        'notificationTitle' => $rendered['subject'],
                        'notificationBody' => $rendered['body'],
                        'notificationData' => json_encode($params['data']),
                        'notificationLink' => $link,
                        'notificationIcon' => $event['moduleIcon'] ?? 'ri-notification-line',
                        'priority' => $priority
                    ), $DBConn);

                    if ($notifID) {
                        // Get user contact info from people table
                        error_log("Notification::create - ===== RETRIEVING APPROVER/USER EMAIL FROM PEOPLE TABLE =====");
                        error_log("Notification::create - Getting user contact info for UserID={$params['userId']}, ChannelID={$channelID}, ChannelSlug={$channel['channelSlug']}");
                        error_log("Notification::create - Event: {$params['eventSlug']}, Recipient UserID: {$params['userId']}");
                        $userContact = self::getUserContactInfo($params['userId'], $channelID, $DBConn);
                        error_log("Notification::create - User contact info retrieved: " . json_encode($userContact));
                        error_log("Notification::create - ===== EMAIL RETRIEVAL COMPLETE =====");

                        // For email channel, send immediately using PHPMailer
                        if ($channel['channelSlug'] === 'email') {
                            $emailSent = self::sendEmailImmediately($notifID, $rendered['subject'], $rendered['body'], $userContact, $params, $DBConn);

                            if ($emailSent['success']) {
                                // Email sent successfully - mark as sent in queue for tracking
                                $queueID = self::addToQueue(array(
                                    'notificationID' => $notifID,
                                    'channelID' => $channelID,
                                    'recipientEmail' => isset($userContact['email']) ? $userContact['email'] : null,
                                    'recipientPhone' => null,
                                    'scheduledFor' => date('Y-m-d H:i:s'),
                                    'status' => 'sent',
                                    'sentAt' => date('Y-m-d H:i:s')
                                ), $DBConn);

                                $notifications[] = array(
                                    'channelSlug' => $channel['channelSlug'],
                                    'notificationID' => $notifID,
                                    'queueID' => $queueID,
                                    'emailSent' => true,
                                    'sentAt' => date('Y-m-d H:i:s')
                                );

                                // Log successful email send
                                self::logAction(array(
                                    'notificationID' => $notifID,
                                    'queueID' => $queueID,
                                    'eventID' => $event['eventID'],
                                    'channelID' => $channelID,
                                    'userID' => $params['userId'],
                                    'action' => 'sent',
                                    'actionDetails' => 'Email sent immediately via PHPMailer to ' . (isset($userContact['email']) ? $userContact['email'] : 'unknown')
                                ), $DBConn);

                                error_log("Notification::create - ✓ EMAIL SENT IMMEDIATELY: NotificationID={$notifID}, RecipientEmail=" . (isset($userContact['email']) ? $userContact['email'] : 'NOT SET') . ", EventSlug={$params['eventSlug']}, UserID={$params['userId']}, Subject={$rendered['subject']}");
                            } else {
                                // Email failed - add to queue for retry
                                error_log("Notification::create - ✗ EMAIL SEND FAILED: NotificationID={$notifID}, Error: " . $emailSent['message'] . ", RecipientEmail=" . (isset($userContact['email']) ? $userContact['email'] : 'NOT SET') . ", EventSlug={$params['eventSlug']}, UserID={$params['userId']}");

                                $queueID = self::addToQueue(array(
                                    'notificationID' => $notifID,
                                    'channelID' => $channelID,
                                    'recipientEmail' => isset($userContact['email']) ? $userContact['email'] : null,
                                    'recipientPhone' => null,
                                    'scheduledFor' => date('Y-m-d H:i:s'),
                                    'status' => 'pending',
                                    'errorMessage' => $emailSent['message']
                                ), $DBConn);

                                $notifications[] = array(
                                    'channelSlug' => $channel['channelSlug'],
                                    'notificationID' => $notifID,
                                    'queueID' => $queueID,
                                    'emailSent' => false,
                                    'error' => $emailSent['message']
                                );

                                self::logAction(array(
                                    'notificationID' => $notifID,
                                    'queueID' => $queueID,
                                    'eventID' => $event['eventID'],
                                    'channelID' => $channelID,
                                    'userID' => $params['userId'],
                                    'action' => 'queued',
                                    'actionDetails' => 'Email send failed, added to queue for retry. Error: ' . $emailSent['message']
                                ), $DBConn);

                                // Don't mark as failure - it's queued for retry
                            }
                        } else {
                            // SMS/Push - add to queue for processing
                            $queueID = self::addToQueue(array(
                                'notificationID' => $notifID,
                                'channelID' => $channelID,
                                'recipientEmail' => isset($userContact['email']) ? $userContact['email'] : null,
                                'recipientPhone' => isset($userContact['phone']) ? $userContact['phone'] : null,
                                'scheduledFor' => isset($params['scheduleFor']) ? $params['scheduleFor'] : date('Y-m-d H:i:s')
                            ), $DBConn);

                            if ($queueID) {
                                $notifications[] = array(
                                    'channelSlug' => $channel['channelSlug'],
                                    'notificationID' => $notifID,
                                    'queueID' => $queueID
                                );

                                self::logAction(array(
                                    'notificationID' => $notifID,
                                    'queueID' => $queueID,
                                    'eventID' => $event['eventID'],
                                    'channelID' => $channelID,
                                    'userID' => $params['userId'],
                                    'action' => 'queued',
                                    'actionDetails' => 'Added to ' . $channel['channelSlug'] . ' delivery queue'
                                ), $DBConn);
                            } else {
                                error_log("Notification::create - ERROR: Failed to add notification to queue. Channel: {$channel['channelSlug']}, NotificationID: {$notifID}");
                                $success = false;
                            }
                        }
                    } else {
                        error_log("Notification::create - ERROR: Failed to create notification record. Channel: {$channel['channelSlug']}");
                        $success = false;
                    }
                }
            }

            return array(
                'success' => $success,
                'notifications' => $notifications,
                'message' => $success ? 'Notification(s) created successfully' : 'Some notifications failed to create'
            );

        } catch (Exception $e) {
            error_log("Notification creation error: " . $e->getMessage());
            return array(
                'success' => false,
                'message' => 'Error creating notification: ' . $e->getMessage()
            );
        }
    }

    /**
     * Get event details by slug
     */
    private static function getEventBySlug($slug, $DBConn) {
        $sql = "SELECT e.*, m.moduleIcon
                FROM tija_notification_events e
                LEFT JOIN tija_notification_modules m ON e.moduleID = m.moduleID
                WHERE e.eventSlug = ? AND e.Suspended = 'N'";
        $result = $DBConn->fetch_all_rows($sql, array(array($slug, 's')));
        if (is_array($result) && count($result) > 0) {
            return (array) $result[0];
        }
        return false;
    }

    /**
     * Get channel details by slug
     */
    private static function getChannelBySlug($slug, $DBConn) {
        $sql = "SELECT * FROM tija_notification_channels WHERE channelSlug = ? AND Suspended = 'N'";
        $result = $DBConn->fetch_all_rows($sql, array(array($slug, 's')));
        if (is_array($result) && count($result) > 0) {
            return (array) $result[0];
        }
        return false;
    }

    /**
     * Get channel details by ID
     */
    private static function getChannelById($channelID, $DBConn) {
        $sql = "SELECT * FROM tija_notification_channels WHERE channelID = ? AND Suspended = 'N'";
        $result = $DBConn->fetch_all_rows($sql, array(array($channelID, 'i')));
        if (is_array($result) && count($result) > 0) {
            return (array) $result[0];
        }
        return false;
    }

    /**
     * Get user's preferred channels for an event
     */
    private static function getUserChannelsForEvent($userID, $eventID, $DBConn) {
        $sql = "SELECT channelID
                FROM tija_notification_preferences
                WHERE userID = ? AND eventID = ? AND isEnabled = 'Y' AND Suspended = 'N'";
        $result = $DBConn->fetch_all_rows($sql, array(array($userID, 'i'), array($eventID, 'i')));

        if (is_array($result) && count($result) > 0) {
            $normalized = array_map(function($row) {
                return is_array($row) ? $row : (array) $row;
            }, $result);
            return array_column($normalized, 'channelID');
        }

        // If no preferences set, use event defaults
        $event = self::getEventById($eventID, $DBConn);
        if ($event && $event['defaultEnabled'] === 'Y') {
            // Return in-app channel by default
            $inApp = self::getChannelBySlug('in_app', $DBConn);
            return $inApp ? array($inApp['channelID']) : array();
        }

        return array();
    }

    /**
     * Get entity-level channels for an event
     */
    private static function getEntityChannelsForEvent($entityID, $eventID, $DBConn) {
        if (empty($entityID) || empty($eventID)) {
            return array('allowed' => array(), 'enforced' => array());
        }

        if (!self::$entityPreferenceTableChecked) {
            $tableCheck = $DBConn->fetch_all_rows("SHOW TABLES LIKE 'tija_notification_entity_preferences'", array());
            self::$entityPreferenceTableExists = $tableCheck && count($tableCheck) > 0;
            self::$entityPreferenceTableChecked = true;
        }

        if (!self::$entityPreferenceTableExists) {
            return array('allowed' => array(), 'enforced' => array());
        }

        $sql = "SELECT channelID, enforceForAllUsers
                FROM tija_notification_entity_preferences
                WHERE entityID = ? AND eventID = ?
                  AND isEnabled = 'Y' AND Suspended = 'N'";

        $rows = $DBConn->fetch_all_rows($sql, array(
            array($entityID, 'i'),
            array($eventID, 'i')
        ));

        $allowed = array();
        $enforced = array();

        if ($rows) {
            foreach ($rows as $row) {
                $data = is_array($row) ? $row : (array)$row;
                if (!isset($data['channelID'])) {
                    continue;
                }

                $channelID = (int)$data['channelID'];
                $allowed[] = $channelID;

                $enforceFlag = isset($data['enforceForAllUsers']) ? $data['enforceForAllUsers'] : 'N';
                if ($enforceFlag === 'Y') {
                    $enforced[] = $channelID;
                }
            }
        }

        return array(
            'allowed' => array_values(array_unique($allowed)),
            'enforced' => array_values(array_unique($enforced))
        );
    }

    /**
     * Get event by ID
     */
    private static function getEventById($eventID, $DBConn) {
        $sql = "SELECT * FROM tija_notification_events WHERE eventID = ? AND Suspended = 'N'";
        $result = $DBConn->fetch_all_rows($sql, array(array($eventID, 'i')));
        if (is_array($result) && count($result) > 0) {
            return (array) $result[0];
        }
        return false;
    }

    /**
     * Get template for event/channel combination
     */
    private static function getTemplate($eventID, $channelID, $params, $DBConn) {
        // Try to get entity-specific template first
        if (isset($params['entityID'])) {
            $sql = "SELECT * FROM tija_notification_templates
                    WHERE eventID = ? AND channelID = ? AND entityID = ?
                    AND isActive = 'Y' AND Suspended = 'N'
                    LIMIT 1";
            $result = $DBConn->fetch_all_rows($sql, array(
                array($eventID, 'i'),
                array($channelID, 'i'),
                array($params['entityID'], 'i')
            ));

            if (is_array($result) && count($result) > 0) {
                return (array) $result[0];
            }
        }

        // Fall back to default template
        $sql = "SELECT * FROM tija_notification_templates
                WHERE eventID = ? AND channelID = ? AND entityID IS NULL
                AND isActive = 'Y' AND Suspended = 'N'
                ORDER BY isDefault DESC, isSystem DESC
                LIMIT 1";
        $result = $DBConn->fetch_all_rows($sql, array(
            array($eventID, 'i'),
            array($channelID, 'i')
        ));

        if (is_array($result) && count($result) > 0) {
            return (array) $result[0];
        }
        return false;
    }

    /**
     * Render template with variables
     */
    private static function renderTemplate($template, $data) {
        $subject = $template['templateSubject'] ?? '';
        $body = $template['templateBody'] ?? '';

        // Replace variables in both subject and body
        foreach ($data as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $subject = str_replace($placeholder, $value, $subject);
            $body = str_replace($placeholder, $value, $body);
        }

        return array(
            'subject' => $subject,
            'body' => $body
        );
    }

    /**
     * Create in-app notification
     */
    private static function createInAppNotification($data, $DBConn) {
        try {
            // Debug: Log the data being inserted
            error_log("Notification::createInAppNotification - Starting. Data keys: " . implode(', ', array_keys($data)));

            // Check if table exists
            $tableCheck = $DBConn->fetch_all_rows("SHOW TABLES LIKE 'tija_notifications_enhanced'", array());
            if (!$tableCheck || count($tableCheck) === 0) {
                error_log("Notification::createInAppNotification - ERROR: Table 'tija_notifications_enhanced' does not exist!");
                return false;
            }

            $columns = array();
            $placeholders = array();
            $params = array();

            foreach ($data as $key => $value) {
                $columns[] = "`{$key}`";
                if ($value === null) {
                    $placeholders[] = 'NULL';
                } else {
                    $placeholders[] = '?';
                    $params[] = $value;
                }
            }

            $columns[] = '`status`';
            $placeholders[] = '?';
            $params[] = 'unread';

            $columns[] = '`Suspended`';
            $placeholders[] = '?';
            $params[] = 'N';

            $columns[] = '`DateAdded`';
            $placeholders[] = '?';
            $params[] = date('Y-m-d H:i:s');

            $sql = "INSERT INTO tija_notifications_enhanced (" . implode(', ', $columns) . ")
                    VALUES (" . implode(', ', $placeholders) . ")";

            error_log("Notification::createInAppNotification - SQL: " . $sql);
            error_log("Notification::createInAppNotification - Params count: " . count($params));

            $DBConn->query($sql);

            $position = 0;
            foreach ($params as $param) {
                $DBConn->bind(++$position, $param);
            }

            $executeResult = $DBConn->execute();

            if ($executeResult) {
                $insertId = $DBConn->lastInsertId();
                error_log("Notification::createInAppNotification - SUCCESS: Notification created with ID: " . $insertId);
                return $insertId;
            } else {
                error_log("Notification::createInAppNotification - ERROR: Execute failed. No error details available.");
                // Try to get last error
                $errorInfo = $DBConn->errorInfo ?? null;
                if ($errorInfo) {
                    error_log("Notification::createInAppNotification - Error info: " . print_r($errorInfo, true));
                }
                return false;
            }
        } catch (Exception $e) {
            error_log("Notification::createInAppNotification - EXCEPTION: " . $e->getMessage());
            error_log("Notification::createInAppNotification - Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Send email immediately using PHPMailer
     */
    private static function sendEmailImmediately($notificationID, $subject, $body, $userContact, $params, $DBConn) {
        global $config, $base;

        error_log("Notification::sendEmailImmediately - START: NotificationID={$notificationID}, Attempting to send email...");

        if (empty($userContact['email'])) {
            $errorMsg = 'No recipient email address';
            error_log("Notification::sendEmailImmediately - ERROR: {$errorMsg} for NotificationID={$notificationID}, UserID={$params['userId']}");
            return array('success' => false, 'message' => $errorMsg);
        }

        $recipientEmail = $userContact['email'];
        // Use the name from getUserContactInfo if available, otherwise try data array
        $recipientName = isset($userContact['name']) && !empty($userContact['name'])
            ? $userContact['name']
            : (isset($params['data']['employee_name']) ? $params['data']['employee_name'] : 'User');

        error_log("Notification::sendEmailImmediately - Recipient: {$recipientEmail}, Name: {$recipientName}");

        // Check if email helper function exists
        if (function_exists('send_email')) {
            error_log("Notification::sendEmailImmediately - Using send_email() helper function");
            try {
                $result = send_email(
                    $recipientEmail,
                    $recipientName,
                    $subject,
                    $body,
                    strip_tags($body),
                    $config,
                    false // No debug output to console, but we log
                );

                if ($result['success']) {
                    error_log("Notification::sendEmailImmediately - ✓ SUCCESS: Email sent successfully to {$recipientEmail} for NotificationID={$notificationID}");
                    return array('success' => true, 'message' => 'Email sent successfully');
                } else {
                    error_log("Notification::sendEmailImmediately - ✗ FAILED: " . $result['message'] . " for NotificationID={$notificationID}, Recipient={$recipientEmail}");
                    return array('success' => false, 'message' => $result['message']);
                }
            } catch (Exception $e) {
                $errorMsg = 'Exception in send_email(): ' . $e->getMessage();
                error_log("Notification::sendEmailImmediately - ✗ EXCEPTION: {$errorMsg} for NotificationID={$notificationID}");
                return array('success' => false, 'message' => $errorMsg);
            }
        } else {
            // Fallback: Use PHPMailer directly from php/classes/ folder
            error_log("Notification::sendEmailImmediately - send_email() helper not found, using PHPMailer directly");

            try {
                // Check PHPMailer library paths - PHPMailer is in php/classes/ folder (lowercase filename)
                $phpmailerPaths = array(
                    __DIR__ . '/phpmailer.php',  // Same directory as notification.php (lowercase)
                    $base . 'php/classes/phpmailer.php',  // Lowercase filename
                    dirname(__DIR__) . '/classes/phpmailer.php'  // Lowercase filename
                );

                $phpmailerFile = null;
                $smtpFile = null;
                $exceptionFile = null;

                foreach ($phpmailerPaths as $path) {
                    // Check with lowercase filename (case-sensitive check)
                    if (file_exists($path)) {
                        $phpmailerFile = $path;
                        $phpmailerDir = dirname($path);
                        // Also use lowercase for related files
                        $smtpFile = $phpmailerDir . '/smtp.php';
                        $exceptionFile = $phpmailerDir . '/Exception.php';
                        error_log("Notification::sendEmailImmediately - Found PHPMailer at: {$phpmailerFile}");
                        break;
                    }
                }

                if (!$phpmailerFile || !file_exists($phpmailerFile)) {
                    $errorMsg = 'PHPMailer library not found in php/classes/ folder';
                    error_log("Notification::sendEmailImmediately - ✗ ERROR: {$errorMsg}");
                    return array('success' => false, 'message' => $errorMsg);
                }

                // Load PHPMailer files
                if (file_exists($exceptionFile)) {
                    require_once $exceptionFile;
                }
                require_once $phpmailerFile;
                if (file_exists($smtpFile)) {
                    require_once $smtpFile;
                }

                error_log("Notification::sendEmailImmediately - Loading PHPMailer from: {$phpmailerFile} (lowercase filename: phpmailer.php)");

                // Try to instantiate PHPMailer - check if it's namespaced or not
                // Note: PHPMailer file is phpmailer.php (lowercase) in php/classes/ folder
                try {
                    // After require_once, check which class name works
                    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                        // Namespaced version (most likely)
                        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                        error_log("Notification::sendEmailImmediately - Using fully qualified namespaced PHPMailer class");
                    } elseif (class_exists('PHPMailer')) {
                        // Non-namespaced or use statement worked
                        $mail = new PHPMailer(true);
                        error_log("Notification::sendEmailImmediately - Using PHPMailer class");
                    } else {
                        $errorMsg = 'PHPMailer class not found after loading phpmailer.php';
                        error_log("Notification::sendEmailImmediately - ✗ ERROR: {$errorMsg}");
                        error_log("Notification::sendEmailImmediately - Available classes: " . implode(', ', get_declared_classes()));
                        return array('success' => false, 'message' => $errorMsg);
                    }
                } catch (\Exception $e) {
                    $errorMsg = 'Failed to instantiate PHPMailer: ' . $e->getMessage();
                    error_log("Notification::sendEmailImmediately - ✗ ERROR: {$errorMsg}");
                    return array('success' => false, 'message' => $errorMsg);
                }

                // Configure SMTP settings from config
                error_log("Notification::sendEmailImmediately - Configuring SMTP: Host=" . ($config['emailHost'] ?? 'NOT SET') . ", Port=" . ($config['emailPort'] ?? 'NOT SET'));

                $mail->isSMTP();
                $mail->Host = $config['emailHost'] ?? 'localhost';
                $mail->SMTPAuth = true;
                $mail->Username = $config['userName'] ?? $config['siteEmail'] ?? '';
                $mail->Password = $config['emailPWS'] ?? '';

                // Set encryption based on port
                $port = $config['emailPort'] ?? 587;
                if ($port == 465) {
                    // Use the correct constant - try namespaced first, then fallback
                    if (defined('PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS')) {
                        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                    } elseif (defined('PHPMailer::ENCRYPTION_SMTPS')) {
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    } else {
                        $mail->SMTPSecure = 'ssl';
                    }
                } elseif ($port == 587) {
                    if (defined('PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS')) {
                        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    } elseif (defined('PHPMailer::ENCRYPTION_STARTTLS')) {
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    } else {
                        $mail->SMTPSecure = 'tls';
                    }
                }

                $mail->Port = $port;
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );
                $mail->Timeout = 30;

                // Set From address
                $fromEmail = $config['siteEmail'] ?? 'noreply@example.com';
                $fromName = $config['siteName'] ?? 'PMS Notification System';
                $mail->setFrom($fromEmail, $fromName);
                error_log("Notification::sendEmailImmediately - From: {$fromEmail} ({$fromName})");

                // Add recipient
                $mail->addAddress($recipientEmail, $recipientName);
                $mail->addReplyTo($fromEmail, $fromName);
                error_log("Notification::sendEmailImmediately - To: {$recipientEmail} ({$recipientName})");

                // Content
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = $body;
                $mail->AltBody = strip_tags($body);
                error_log("Notification::sendEmailImmediately - Subject: {$subject}");

                // Send email
                error_log("Notification::sendEmailImmediately - Attempting to send email via SMTP...");
                $mail->send();

                error_log("Notification::sendEmailImmediately - ✓ SUCCESS: Email sent successfully to {$recipientEmail} for NotificationID={$notificationID}");
                return array('success' => true, 'message' => 'Email sent successfully');

            } catch (Exception $e) {
                $errorMsg = 'PHPMailer Exception: ' . $e->getMessage();
                error_log("Notification::sendEmailImmediately - ✗ EXCEPTION: {$errorMsg} for NotificationID={$notificationID}, Recipient={$recipientEmail}");
                if (isset($mail)) {
                    error_log("Notification::sendEmailImmediately - PHPMailer ErrorInfo: " . $mail->ErrorInfo);
                }
                return array('success' => false, 'message' => $errorMsg);
            }
        }
    }

    /**
     * Add notification to delivery queue
     */
    private static function addToQueue($data, $DBConn) {
        $payload = array(
            'status' => isset($data['status']) ? $data['status'] : 'pending',
            'DateAdded' => date('Y-m-d H:i:s')
        );

        foreach ($data as $key => $value) {
            if ($value !== null && $key !== 'status') {
                $payload[$key] = $value;
            }
        }

        // Handle sentAt if status is 'sent'
        if (isset($payload['status']) && $payload['status'] === 'sent' && isset($data['sentAt'])) {
            $payload['sentAt'] = $data['sentAt'];
        }

        $result = $DBConn->insert_data('tija_notification_queue', $payload);
        return $result ? $DBConn->lastInsertId() : false;
    }

    /**
     * Get user contact information for a channel
     */
    private static function getUserContactInfo($userID, $channelID, $DBConn) {
        error_log("Notification::getUserContactInfo - START: UserID={$userID}, ChannelID={$channelID}");

        // Get channel type
        $channel = self::getChannelById($channelID, $DBConn);

        if (!$channel) {
            error_log("Notification::getUserContactInfo - ERROR: Channel not found for ChannelID={$channelID}");
            return array();
        }

        error_log("Notification::getUserContactInfo - Channel: {$channel['channelSlug']}");

        $info = array();

        // Get user details - email is in people table, phone number is in user_details table
        $sql = "SELECT p.ID, p.Email, p.FirstName, p.Surname, p.OtherNames, d.phoneNo
                FROM people p
                LEFT JOIN user_details d ON p.ID = d.ID
                WHERE p.ID = ?";

        error_log("Notification::getUserContactInfo - Querying user contact info from people table for UserID={$userID}");
        error_log("Notification::getUserContactInfo - SQL: SELECT p.ID, p.Email, p.FirstName, p.Surname, p.OtherNames, d.phoneNo FROM people p LEFT JOIN user_details d ON p.ID = d.ID WHERE p.ID = {$userID}");

        $result = $DBConn->fetch_all_rows($sql, array(array($userID, 'i')));

        if (is_array($result) && count($result) > 0) {
            // Convert result to array if it's an object
            $user = $result[0];
            if (is_object($user)) {
                $user = (array) $user;
            }

            $userID_val = isset($user['ID']) ? $user['ID'] : (isset($user->ID) ? $user->ID : $userID);
            $userName = trim((isset($user['FirstName']) ? $user['FirstName'] : '') . ' ' . (isset($user['Surname']) ? $user['Surname'] : ''));
            $userEmail = isset($user['Email']) ? $user['Email'] : null;
            $userPhone = isset($user['phoneNo']) ? $user['phoneNo'] : null;

            error_log("Notification::getUserContactInfo - ✓ User found in people table:");
            error_log("Notification::getUserContactInfo -   - ID: {$userID_val}");
            error_log("Notification::getUserContactInfo -   - Name: {$userName}");
            error_log("Notification::getUserContactInfo -   - Email: " . ($userEmail ?: 'NOT SET'));
            error_log("Notification::getUserContactInfo -   - Phone: " . ($userPhone ?: 'NOT SET'));

            if ($channel['channelSlug'] === 'email') {
                $info['email'] = $userEmail;
                $info['name'] = $userName;
                if (empty($info['email'])) {
                    error_log("Notification::getUserContactInfo - ✗ WARNING: User {$userID} ({$userName}) has no email address in people table!");
                } else {
                    error_log("Notification::getUserContactInfo - ✓✓✓ EMAIL RETRIEVED FROM PEOPLE TABLE: {$info['email']} for UserID={$userID} ({$userName})");
                }
            } elseif ($channel['channelSlug'] === 'sms') {
                $info['phone'] = $userPhone;
                if (empty($info['phone'])) {
                    error_log("Notification::getUserContactInfo - ✗ WARNING: User {$userID} ({$userName}) has no phone number in user_details table!");
                } else {
                    error_log("Notification::getUserContactInfo - ✓ Phone retrieved: {$info['phone']} for UserID={$userID}");
                }
            }
        } else {
            error_log("Notification::getUserContactInfo - ✗ ERROR: User ID {$userID} not found in people table!");
            error_log("Notification::getUserContactInfo - SQL query returned no results for UserID={$userID}");
        }

        error_log("Notification::getUserContactInfo - END: Returning info: " . json_encode($info));
        return $info;
    }

    /**
     * Log notification action
     */
    private static function logAction($data, $DBConn) {
        $payload = array();

        foreach ($data as $key => $value) {
            if ($value !== null) {
                $payload[$key] = $value;
            }
        }

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $payload['ipAddress'] = $_SERVER['REMOTE_ADDR'];
        }

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $payload['userAgent'] = substr($_SERVER['HTTP_USER_AGENT'], 0, 250);
        }

        $payload['DateAdded'] = date('Y-m-d H:i:s');

        return $DBConn->insert_data('tija_notification_logs', $payload);
    }

    /**
     * Get user notifications
     *
     * @param int $userID User ID
     * @param array $filters Optional filters (status, priority, limit, offset, eventID, etc.)
     * @param object $DBConn Database connection
     * @return array|false Array of notifications or false
     */
    public static function getUserNotifications($userID, $filters = array(), $DBConn) {
        $where = array('n.userID = ?');
        $params = array(array($userID, 'i'));

        // Apply filters
        if (isset($filters['status'])) {
            $where[] = 'n.status = ?';
            $params[] = array($filters['status'], 's');
        }

        if (isset($filters['priority'])) {
            $where[] = 'n.priority = ?';
            $params[] = array($filters['priority'], 's');
        }

        if (isset($filters['eventID'])) {
            $where[] = 'n.eventID = ?';
            $params[] = array($filters['eventID'], 'i');
        }

        if (isset($filters['segmentType'])) {
            $where[] = 'n.segmentType = ?';
            $params[] = array($filters['segmentType'], 's');
        }

        if (isset($filters['unreadOnly']) && $filters['unreadOnly']) {
            $where[] = "n.status = 'unread'";
        }

        // Exclude deleted
        $where[] = "n.status != 'deleted'";
        $where[] = "n.Suspended = 'N'";

        $whereClause = implode(' AND ', $where);

        $limit = isset($filters['limit']) ? (int)$filters['limit'] : 50;
        $offset = isset($filters['offset']) ? (int)$filters['offset'] : 0;
        $orderBy = isset($filters['orderBy']) ? $filters['orderBy'] : 'n.DateAdded DESC';

        $sql = "SELECT n.*,
                       e.eventName, e.eventSlug, e.eventCategory,
                       m.moduleName, m.moduleSlug,
                       CONCAT(p.FirstName, ' ', p.Surname) as originatorName
                FROM tija_notifications_enhanced n
                LEFT JOIN tija_notification_events e ON n.eventID = e.eventID
                LEFT JOIN tija_notification_modules m ON e.moduleID = m.moduleID
                LEFT JOIN people p ON n.originatorUserID = p.ID
                WHERE {$whereClause}
                ORDER BY {$orderBy}
                LIMIT {$limit} OFFSET {$offset}";

        return $DBConn->fetch_all_rows($sql, $params);
    }

    /**
     * Get unread notification count for user
     */
    public static function getUnreadCount($userID, $DBConn) {
        $sql = "SELECT COUNT(*) as count
                FROM tija_notifications_enhanced
                WHERE userID = ? AND status = 'unread' AND Suspended = 'N'";
        $result = $DBConn->fetch_all_rows($sql, array(array($userID, 'i')));

        if (is_array($result) && count($result) > 0) {
            // Handle both array and object results
            $firstResult = $result[0];
            if (is_object($firstResult)) {
                return (int)$firstResult->count;
            } else {
                return (int)$firstResult['count'];
            }
        }

        return 0;
    }

    /**
     * Mark notification as read
     */
    public static function markAsRead($notificationID, $userID, $DBConn) {
        $now = date('Y-m-d H:i:s');
        $sql = "UPDATE tija_notifications_enhanced
                SET status = ?, readAt = ?
                WHERE notificationID = ? AND userID = ?";

        $DBConn->query($sql);
        $DBConn->bind(1, 'read');
        $DBConn->bind(2, $now);
        $DBConn->bind(3, $notificationID);
        $DBConn->bind(4, $userID);

        $result = $DBConn->execute();
        $updated = $result && $DBConn->rowCount() > 0;

        if ($updated) {
            // Log the action
            self::logAction(array(
                'notificationID' => $notificationID,
                'userID' => $userID,
                'action' => 'read',
                'actionDetails' => 'Notification marked as read'
            ), $DBConn);
        }

        return $updated;
    }

    /**
     * Mark all notifications as read for a user
     */
    public static function markAllAsRead($userID, $DBConn) {
        $now = date('Y-m-d H:i:s');
        $sql = "UPDATE tija_notifications_enhanced
                SET status = ?, readAt = ?
                WHERE userID = ? AND status = ?";

        $DBConn->query($sql);
        $DBConn->bind(1, 'read');
        $DBConn->bind(2, $now);
        $DBConn->bind(3, $userID);
        $DBConn->bind(4, 'unread');

        $result = $DBConn->execute();
        $updated = $result && $DBConn->rowCount() > 0;

        if ($updated) {
            self::logAction(array(
                'userID' => $userID,
                'action' => 'mark_all_read',
                'actionDetails' => 'All notifications marked as read'
            ), $DBConn);
        }

        return $updated;
    }

    /**
     * Archive notification
     */
    public static function archive($notificationID, $userID, $DBConn) {
        $now = date('Y-m-d H:i:s');
        $sql = "UPDATE tija_notifications_enhanced
                SET status = ?, archivedAt = ?
                WHERE notificationID = ? AND userID = ?";

        $DBConn->query($sql);
        $DBConn->bind(1, 'archived');
        $DBConn->bind(2, $now);
        $DBConn->bind(3, $notificationID);
        $DBConn->bind(4, $userID);

        $result = $DBConn->execute();
        $updated = $result && $DBConn->rowCount() > 0;

        if ($updated) {
            self::logAction(array(
                'notificationID' => $notificationID,
                'userID' => $userID,
                'action' => 'archived',
                'actionDetails' => 'Notification archived'
            ), $DBConn);
        }

        return $updated;
    }

    /**
     * Delete notification (soft delete)
     */
    public static function delete($notificationID, $userID, $DBConn) {
        $sql = "UPDATE tija_notifications_enhanced
                SET status = ?
                WHERE notificationID = ? AND userID = ?";

        $DBConn->query($sql);
        $DBConn->bind(1, 'deleted');
        $DBConn->bind(2, $notificationID);
        $DBConn->bind(3, $userID);

        $result = $DBConn->execute();
        $updated = $result && $DBConn->rowCount() > 0;

        if ($updated) {
            self::logAction(array(
                'notificationID' => $notificationID,
                'userID' => $userID,
                'action' => 'deleted',
                'actionDetails' => 'Notification deleted'
            ), $DBConn);
        }

        return $updated;
    }

    /**
     * Get notification modules
     */
    public static function getModules($whereArr = array(), $single = false, $DBConn) {
        $params = array();
        $where = '';

        if (count($whereArr) > 0) {
            $conditions = array();
            foreach ($whereArr as $col => $val) {
                $conditions[] = "{$col} = ?";
                $params[] = array($val, is_numeric($val) ? 'i' : 's');
            }
            $where = 'WHERE ' . implode(' AND ', $conditions);
        }

        $sql = "SELECT * FROM tija_notification_modules {$where} ORDER BY sortOrder, moduleName";
        $rows = $DBConn->fetch_all_rows($sql, $params);

        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) :
               ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get notification events
     */
    public static function getEvents($whereArr = array(), $single = false, $DBConn) {
        $params = array();
        $where = '';

        if (count($whereArr) > 0) {
            $conditions = array();
            foreach ($whereArr as $col => $val) {
                $conditions[] = "e.{$col} = ?";
                $params[] = array($val, is_numeric($val) ? 'i' : 's');
            }
            $where = 'WHERE ' . implode(' AND ', $conditions);
        }

        $sql = "SELECT e.*, m.moduleName, m.moduleSlug
                FROM tija_notification_events e
                LEFT JOIN tija_notification_modules m ON e.moduleID = m.moduleID
                {$where}
                ORDER BY e.sortOrder, e.eventName";
        $rows = $DBConn->fetch_all_rows($sql, $params);

        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) :
               ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get notification channels
     */
    public static function getChannels($whereArr = array(), $single = false, $DBConn) {
        $params = array();
        $where = '';

        if (count($whereArr) > 0) {
            $conditions = array();
            foreach ($whereArr as $col => $val) {
                $conditions[] = "{$col} = ?";
                $params[] = array($val, is_numeric($val) ? 'i' : 's');
            }
            $where = 'WHERE ' . implode(' AND ', $conditions);
        }

        $sql = "SELECT * FROM tija_notification_channels {$where} ORDER BY sortOrder, channelName";
        $rows = $DBConn->fetch_all_rows($sql, $params);

        return ($single === true) ? ((is_array($rows) && count($rows) === 1) ? $rows[0] : false) :
               ((is_array($rows) && count($rows) > 0) ? $rows : false);
    }

    /**
     * Get user preferences
     */
    public static function getUserPreferences($userID, $DBConn) {
        $sql = "SELECT p.*, e.eventName, e.eventSlug, e.defaultEnabled,
                       c.channelName, c.channelSlug,
                       m.moduleName, m.moduleSlug
                FROM tija_notification_preferences p
                LEFT JOIN tija_notification_events e ON p.eventID = e.eventID
                LEFT JOIN tija_notification_channels c ON p.channelID = c.channelID
                LEFT JOIN tija_notification_modules m ON e.moduleID = m.moduleID
                WHERE p.userID = ? AND p.Suspended = 'N'
                ORDER BY m.sortOrder, e.sortOrder, c.sortOrder";

        return $DBConn->fetch_all_rows($sql, array(array($userID, 'i')));
    }

    /**
     * Process notification queue immediately (for critical notifications)
     * This can be called after creating important notifications to send emails immediately
     *
     * @param int $limit Maximum number of queued items to process (default: 10)
     * @param object $DBConn Database connection
     * @return array Success status and processing results
     */
    public static function processQueueImmediately($limit = 10, $DBConn) {
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
                    ORDER BY q.DateAdded ASC
                    LIMIT ?";

            $pending = $DBConn->fetch_all_rows($sql, array(array($limit, 'i')));

            if (!$pending || count($pending) === 0) {
                return array('success' => true, 'processed' => 0, 'message' => 'No pending notifications to process');
            }

            $successCount = 0;
            $failCount = 0;
            global $config, $base;

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
                        $success = self::sendEmailImmediate($item, $errorMessage, $config, $base);
                    } elseif ($item['channelSlug'] === 'sms') {
                        // SMS not implemented for immediate processing
                        $errorMessage = 'SMS gateway not configured for immediate processing';
                        $success = false;
                    } elseif ($item['channelSlug'] === 'push') {
                        // Push not implemented
                        $errorMessage = 'Push notifications not implemented';
                        $success = false;
                    }

                    if ($success) {
                        // Mark as sent
                        $updateSql = "UPDATE tija_notification_queue
                                      SET status = 'sent',
                                          sentAt = NOW(),
                                          errorMessage = NULL
                                      WHERE queueID = ?";
                        $DBConn->update_db_table($updateSql, array(array($item['queueID'], 'i')));
                        $successCount++;
                        error_log("Notification::processQueueImmediately - Sent email notification (Queue ID: {$item['queueID']})");
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
                        $failCount++;
                        error_log("Notification::processQueueImmediately - Failed to send notification (Queue ID: {$item['queueID']}): {$errorMessage}");
                    }
                } catch (Exception $e) {
                    $errorMessage = $e->getMessage();
                    $updateSql = "UPDATE tija_notification_queue
                                  SET status = 'pending',
                                      errorMessage = ?
                                  WHERE queueID = ?";
                    $DBConn->update_db_table($updateSql, array(
                        array($errorMessage, 's'),
                        array($item['queueID'], 'i')
                    ));
                    $failCount++;
                    error_log("Notification::processQueueImmediately - Exception for Queue ID {$item['queueID']}: {$errorMessage}");
                }
            }

            return array(
                'success' => true,
                'processed' => $successCount + $failCount,
                'sent' => $successCount,
                'failed' => $failCount
            );

        } catch (Exception $e) {
            error_log("Notification::processQueueImmediately - ERROR: " . $e->getMessage());
            return array(
                'success' => false,
                'message' => 'Error processing queue: ' . $e->getMessage()
            );
        }
    }

    /**
     * Send email immediately (helper for processQueueImmediately)
     */
    private static function sendEmailImmediate($item, &$errorMessage, $config, $base) {
        if (empty($item['recipientEmail'])) {
            $errorMessage = 'No recipient email address';
            error_log("Notification::sendEmailImmediate - ERROR: {$errorMessage}");
            return false;
        }

        try {
            // PHPMailer is in php/classes/ folder (lowercase filename: phpmailer.php)
            $phpmailerFile = __DIR__ . '/phpmailer.php';  // Lowercase filename
            $smtpFile = __DIR__ . '/smtp.php';  // Lowercase filename
            $exceptionFile = __DIR__ . '/Exception.php';

            // Also check alternative paths (all lowercase)
            if (!file_exists($phpmailerFile)) {
                $phpmailerFile = $base . 'php/classes/phpmailer.php';  // Lowercase
                $smtpFile = $base . 'php/classes/smtp.php';  // Lowercase
                $exceptionFile = $base . 'php/classes/Exception.php';
            }

            if (file_exists($phpmailerFile)) {
                error_log("Notification::sendEmailImmediate - Loading PHPMailer from: {$phpmailerFile} (lowercase filename)");

                if (file_exists($exceptionFile)) {
                    require_once $exceptionFile;
                }
                require_once $phpmailerFile;  // This will load phpmailer.php (lowercase)
                if (file_exists($smtpFile)) {
                    require_once $smtpFile;  // This will load smtp.php (lowercase)
                }

                // Try to instantiate PHPMailer - check if it's namespaced or not
                try {
                    // First try with namespace (if use statement worked)
                    if (class_exists('PHPMailer')) {
                        $mail = new PHPMailer(true);
                        error_log("Notification::sendEmailImmediate - Using PHPMailer class (via use statement)");
                    } elseif (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                        error_log("Notification::sendEmailImmediate - Using fully qualified namespaced PHPMailer class");
                    } else {
                        // Last resort - try without namespace
                        $mail = new \PHPMailer(true);
                        error_log("Notification::sendEmailImmediate - Using PHPMailer without namespace");
                    }
                } catch (\Exception $e) {
                    $errorMsg = 'Failed to instantiate PHPMailer: ' . $e->getMessage();
                    error_log("Notification::sendEmailImmediate - ✗ ERROR: {$errorMsg}");
                    $errorMessage = $errorMsg;
                    return false;
                }

                // Server settings - use config keys from email_helper.php
                $mail->isSMTP();
                $mail->Host = $config['emailHost'] ?? 'localhost';
                $mail->SMTPAuth = true;
                $mail->Username = $config['userName'] ?? $config['siteEmail'] ?? '';
                $mail->Password = $config['emailPWS'] ?? '';

                // Set encryption based on port
                $port = $config['emailPort'] ?? 587;
                if ($port == 465) {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } elseif ($port == 587) {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                }
                $mail->Port = $port;

                // SSL options
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );
                $mail->Timeout = 30;

                // Recipients
                $fromEmail = $config['siteEmail'] ?? 'noreply@example.com';
                $fromName = $config['siteName'] ?? 'PMS Notification System';
                $mail->setFrom($fromEmail, $fromName);
                $mail->addAddress($item['recipientEmail']);
                $mail->addReplyTo($fromEmail, $fromName);

                error_log("Notification::sendEmailImmediate - Sending email from {$fromEmail} to {$item['recipientEmail']}");

                // Content
                $mail->isHTML(true);
                $mail->Subject = $item['notificationTitle'];
                $mail->Body = $item['notificationBody'];
                $mail->AltBody = strip_tags($item['notificationBody']);

                $mail->send();
                error_log("Notification::sendEmailImmediate - ✓ SUCCESS: Email sent to {$item['recipientEmail']}");
                return true;
            } else {
                // PHPMailer not found
                $errorMessage = 'PHPMailer library not found in php/classes/ folder';
                error_log("Notification::sendEmailImmediate - ✗ ERROR: {$errorMessage}");

                // Fallback to PHP mail()
                $fromEmail = $config['siteEmail'] ?? 'noreply@example.com';
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-type: text/html; charset=UTF-8\r\n";
                $headers .= "From: {$fromEmail}\r\n";

                error_log("Notification::sendEmailImmediate - Attempting fallback to PHP mail() function");
                $sent = mail(
                    $item['recipientEmail'],
                    $item['notificationTitle'],
                    $item['notificationBody'],
                    $headers
                );

                if (!$sent) {
                    $errorMessage = 'mail() function returned false';
                    error_log("Notification::sendEmailImmediate - ✗ FAILED: {$errorMessage}");
                } else {
                    error_log("Notification::sendEmailImmediate - ✓ SUCCESS: Email sent via mail() to {$item['recipientEmail']}");
                }

                return $sent;
            }
        } catch (Exception $e) {
            $errorMessage = 'PHPMailer Exception: ' . $e->getMessage();
            error_log("Notification::sendEmailImmediate - ✗ EXCEPTION: {$errorMessage}");
            if (isset($mail)) {
                error_log("Notification::sendEmailImmediate - PHPMailer ErrorInfo: " . $mail->ErrorInfo);
            }
            return false;
        }
    }

    /**
     * Update user preference
     */
    public static function updatePreference($userID, $eventID, $channelID, $isEnabled, $DBConn) {
        // Check if preference exists
        $sql = "SELECT preferenceID FROM tija_notification_preferences
                WHERE userID = ? AND eventID = ? AND channelID = ?";
        $existing = $DBConn->fetch_all_rows($sql, array(
            array($userID, 'i'),
            array($eventID, 'i'),
            array($channelID, 'i')
        ));

        if (is_array($existing) && count($existing) > 0) {
            $updateData = array(
                'isEnabled' => $isEnabled,
                'LastUpdate' => date('Y-m-d H:i:s')
            );
            $where = array(
                'userID' => $userID,
                'eventID' => $eventID,
                'channelID' => $channelID
            );

            return $DBConn->update_table('tija_notification_preferences', $updateData, $where);
        } else {
            $insertData = array(
                'userID' => $userID,
                'eventID' => $eventID,
                'channelID' => $channelID,
                'isEnabled' => $isEnabled,
                'DateAdded' => date('Y-m-d H:i:s')
            );

            return $DBConn->insert_data('tija_notification_preferences', $insertData);
        }
    }

    /**
     * Populate common variables used by templates (site information, absolute links, etc.)
     */
    private static function enrichTemplateData(array $data, $fallbackLink = null) {
        global $config;

        $siteURL = isset($config['siteURL']) ? rtrim($config['siteURL'], '/') : '';
        $siteName = $config['siteName'] ?? 'Tija Practice Management System';

        if (!isset($data['site_url'])) {
            $data['site_url'] = $siteURL;
        }

        if (!isset($data['site_name'])) {
            $data['site_name'] = $siteName;
        }

        if (!isset($data['application_link']) && !empty($fallbackLink)) {
            $data['application_link'] = $fallbackLink;
        }

        if (!isset($data['application_link_full']) && !empty($data['application_link'])) {
            $data['application_link_full'] = self::buildAbsoluteLink($data['application_link']);
        } elseif (!isset($data['application_link_full']) && !empty($fallbackLink)) {
            $data['application_link_full'] = self::buildAbsoluteLink($fallbackLink);
        }

        if (!isset($data['cta_link'])) {
            $data['cta_link'] = $data['application_link_full'] ?? $siteURL;
        }

        return $data;
    }

    /**
     * Convert relative application links to absolute URLs for email templates.
     */
    private static function buildAbsoluteLink($path) {
        global $config;

        if (empty($path)) {
            return '';
        }

        $path = trim($path);

        if ($path === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $baseURL = isset($config['siteURL']) ? rtrim($config['siteURL'], '/') : '';

        if ($path[0] === '?') {
            $path = 'html/' . $path;
        } elseif (stripos($path, 'html/') !== 0) {
            $path = 'html/' . ltrim($path, '/');
        }

        if ($baseURL === '') {
            return ltrim($path, '/');
        }

        return $baseURL . '/' . ltrim($path, '/');
    }
}

