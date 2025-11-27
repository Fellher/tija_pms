<?php
/**
 * Entity Notification Preferences Seeder
 *
 * Creates or updates notification channel preferences for the current entity
 * across all leave-related events. By default it enables in-app + email
 * channels and allows enforcing specific channels for every employee.
 */

session_start();
$base = '../../../';
set_include_path($base);
include 'php/includes.php';

header('Content-Type: application/json');

if (!$isValidUser || (!$isValidAdmin && !$isAdmin && !$isSuperAdmin && !$isHRManager)) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized'
    ]);
    exit;
}

try {
    $entityID = null;
    if (isset($_POST['entityID'])) {
        $entityID = (int) Utility::clean_string($_POST['entityID']);
    } elseif (isset($_SESSION['entityID'])) {
        $entityID = (int) $_SESSION['entityID'];
    }

    if (!$entityID) {
        throw new Exception('Missing entity ID. Provide entityID or ensure it exists in the session.');
    }

/**
 * Normalize a list of channel slugs.
 */
function normalize_channel_slugs($slugs) {
    if (!is_array($slugs)) {
        return array();
    }
    $normalized = array();
    foreach ($slugs as $slug) {
        $clean = strtolower(Utility::clean_string($slug));
        if ($clean !== '') {
            $normalized[] = $clean;
        }
    }
    return array_values(array_unique($normalized));
}

$globalEnabledChannels = normalize_channel_slugs(isset($_POST['enabledChannels']) ? $_POST['enabledChannels'] : array('in_app', 'email'));
if (empty($globalEnabledChannels)) {
    $globalEnabledChannels = array('in_app');
}

$globalEnforceChannels = normalize_channel_slugs(isset($_POST['enforceChannels']) ? $_POST['enforceChannels'] : array());

$eventPreferencesPayload = array();
if (!empty($_POST['eventPreferences'])) {
    $decoded = json_decode($_POST['eventPreferences'], true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $eventPreferencesPayload = $decoded;
    }
}

    // Identify leave module
    $moduleRow = $DBConn->fetch_all_rows(
        "SELECT moduleID FROM tija_notification_modules WHERE moduleSlug = ? AND Suspended = 'N' LIMIT 1",
        array(array('leave', 's'))
    );

    if (!$moduleRow || count($moduleRow) === 0) {
        throw new Exception('Leave notification module not found. Run create_leave_notification_events.php first.');
    }

    $module = is_object($moduleRow[0]) ? (array)$moduleRow[0] : $moduleRow[0];
    $moduleID = isset($module['moduleID']) ? (int)$module['moduleID'] : null;

    if (!$moduleID) {
        throw new Exception('Unable to determine leave module ID.');
    }

    // Fetch leave events
    $events = $DBConn->fetch_all_rows(
        "SELECT eventID, eventSlug, eventName
         FROM tija_notification_events
         WHERE moduleID = ? AND Suspended = 'N' AND isActive = 'Y'",
        array(array($moduleID, 'i'))
    );

    if (!$events || count($events) === 0) {
        throw new Exception('No active leave notification events were found.');
    }

    // Fetch active channels
    $channels = $DBConn->fetch_all_rows(
        "SELECT channelID, channelSlug
         FROM tija_notification_channels
         WHERE Suspended = 'N' AND isActive = 'Y'",
        array()
    );

    if (!$channels || count($channels) === 0) {
        throw new Exception('No active notification channels are available.');
    }

    $insertSql = "
        INSERT INTO `tija_notification_entity_preferences`
            (`entityID`, `eventID`, `channelID`, `isEnabled`, `enforceForAllUsers`,
             `notifyImmediately`, `notifyDigest`, `digestFrequency`, `Suspended`, `Lapsed`)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'none', 'N', 'N')
        ON DUPLICATE KEY UPDATE
            `isEnabled` = VALUES(`isEnabled`),
            `enforceForAllUsers` = VALUES(`enforceForAllUsers`),
            `notifyImmediately` = VALUES(`notifyImmediately`),
            `notifyDigest` = VALUES(`notifyDigest`),
            `digestFrequency` = VALUES(`digestFrequency`),
            `Suspended` = 'N',
            `Lapsed` = 'N',
            `LastUpdate` = CURRENT_TIMESTAMP
    ";

    $created = 0;
    $updated = 0;

    foreach ($events as $eventItem) {
        $eventData = is_object($eventItem) ? (array)$eventItem : $eventItem;
        $eventID = (int) ($eventData['eventID'] ?? 0);
        if (!$eventID) {
            continue;
        }

        $eventPref = array(
            'enabledChannels' => $globalEnabledChannels,
            'enforceChannels' => $globalEnforceChannels
        );

        if (isset($eventPreferencesPayload[$eventID]) && is_array($eventPreferencesPayload[$eventID])) {
            $eventPref['enabledChannels'] = normalize_channel_slugs($eventPreferencesPayload[$eventID]['enabledChannels'] ?? array());
            $eventPref['enforceChannels'] = normalize_channel_slugs($eventPreferencesPayload[$eventID]['enforceChannels'] ?? array());

            if (empty($eventPref['enabledChannels'])) {
                $eventPref['enabledChannels'] = $globalEnabledChannels;
            }
        }

        foreach ($channels as $channelItem) {
            $channelData = is_object($channelItem) ? (array)$channelItem : $channelItem;
            $channelID = (int) ($channelData['channelID'] ?? 0);
            $channelSlug = strtolower($channelData['channelSlug'] ?? '');

            if (!$channelID || !$channelSlug) {
                continue;
            }

            $isEnabled = in_array($channelSlug, $eventPref['enabledChannels'], true) ? 'Y' : 'N';
            $enforce = in_array($channelSlug, $eventPref['enforceChannels'], true) ? 'Y' : 'N';
            $notifyImmediately = $isEnabled === 'Y' ? 'Y' : 'N';
            $notifyDigest = 'N';

            $DBConn->query($insertSql);
            $DBConn->bind(1, $entityID, PDO::PARAM_INT);
            $DBConn->bind(2, $eventID, PDO::PARAM_INT);
            $DBConn->bind(3, $channelID, PDO::PARAM_INT);
            $DBConn->bind(4, $isEnabled);
            $DBConn->bind(5, $enforce);
            $DBConn->bind(6, $notifyImmediately);
            $DBConn->bind(7, $notifyDigest);

            $DBConn->execute();
            $affected = $DBConn->rowCount();

            if ($affected === 1) {
                $created++;
            } elseif ($affected === 2) {
                $updated++;
            }
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Entity notification preferences synchronized successfully.',
        'entityID' => $entityID,
        'eventsProcessed' => count($events),
        'channelsProcessed' => count($channels),
        'recordsInserted' => $created,
        'recordsUpdated' => $updated
    ]);
} catch (Exception $e) {
    error_log('Failed to apply entity notification preferences: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

