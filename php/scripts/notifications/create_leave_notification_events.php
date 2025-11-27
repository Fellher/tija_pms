<?php
/**
 * Create Leave Notification Events
 *
 * This script creates the required notification events for the leave module
 * if they don't already exist.
 *
 * Run this script once to set up the notification events.
 */

session_start();
$base = '../../../';
set_include_path($base);
include 'php/includes.php';

header('Content-Type: application/json');

if (!$isValidUser) {
    echo json_encode(array('success' => false, 'message' => 'Unauthorized'));
    exit;
}

try {
    // Check if leave module exists, create if not
    $leaveModule = $DBConn->fetch_all_rows(
        "SELECT * FROM tija_notification_modules WHERE moduleSlug = ?",
        array(array('leave', 's'))
    );

    $moduleID = null;
    if (!$leaveModule || count($leaveModule) === 0) {
        // Create leave module
        $moduleData = array(
            'moduleName' => 'Leave Management',
            'moduleSlug' => 'leave',
            'moduleDescription' => 'Leave application and approval notifications',
            'moduleIcon' => 'ri-calendar-line',
            'isActive' => 'Y',
            'sortOrder' => 10,
            'DateAdded' => date('Y-m-d H:i:s'),
            'Suspended' => 'N'
        );
        //output the moduleData on php_error_log
        error_log(print_r($moduleData, true));

        $moduleResult = $DBConn->insert_data('tija_notification_modules', $moduleData);
        if ($moduleResult) {
            $moduleID = $DBConn->lastInsertId();
        } else {
            throw new Exception('Failed to create leave notification module');
        }
    } else {
        $module = is_object($leaveModule[0]) ? (array)$leaveModule[0] : $leaveModule[0];
        $moduleID = isset($module['moduleID']) ? (int)$module['moduleID'] : null;
    }

    if (!$moduleID) {
        throw new Exception('Could not determine module ID');
    }

    // Check if in_app channel exists
    $inAppChannel = $DBConn->fetch_all_rows(
        "SELECT * FROM tija_notification_channels WHERE channelSlug = ?",
        array(array('in_app', 's'))
    );

    if (!$inAppChannel || count($inAppChannel) === 0) {
        // Create in_app channel
        $channelData = array(
            'channelName' => 'In-App Notification',
            'channelSlug' => 'in_app',
            'channelDescription' => 'In-application notifications',
            'isActive' => 'Y',
            'sortOrder' => 1,
            'DateAdded' => date('Y-m-d H:i:s'),
            'Suspended' => 'N'
        );
        //output the channelData on php_error_log
        error_log(print_r($channelData, true));
        $channelResult = $DBConn->insert_data('tija_notification_channels', $channelData);
        if (!$channelResult) {
            throw new Exception('Failed to create in_app notification channel');
        }
        $channelID = $DBConn->lastInsertId();
    } else {
        $channel = is_object($inAppChannel[0]) ? (array)$inAppChannel[0] : $inAppChannel[0];
        $channelID = isset($channel['channelID']) ? (int)$channel['channelID'] : null;
    }

    $inAppChannelID = $channelID;

    // Ensure email channel exists
    $emailChannel = $DBConn->fetch_all_rows(
        "SELECT * FROM tija_notification_channels WHERE channelSlug = ?",
        array(array('email', 's'))
    );

    if (!$emailChannel || count($emailChannel) === 0) {
        $emailChannelData = array(
            'channelName' => 'Email Notification',
            'channelSlug' => 'email',
            'channelDescription' => 'Outbound email notifications',
            'isActive' => 'Y',
            'sortOrder' => 2,
            'DateAdded' => date('Y-m-d H:i:s'),
            'Suspended' => 'N'
        );

        if (!$DBConn->insert_data('tija_notification_channels', $emailChannelData)) {
            throw new Exception('Failed to create email notification channel');
        }
        $emailChannelID = $DBConn->lastInsertId();
    } else {
        $emailRow = is_object($emailChannel[0]) ? (array)$emailChannel[0] : $emailChannel[0];
        $emailChannelID = isset($emailRow['channelID']) ? (int)$emailRow['channelID'] : null;
    }

    // Events to create
    $events = array(
        array(
            'eventSlug' => 'leave_pending_approval',
            'eventName' => 'Leave Pending Approval',
            'eventDescription' => 'Notification sent to approvers when a leave application is pending their approval',
            'moduleID' => $moduleID,
            'priorityLevel' => 'high',
            'isActive' => 'Y',
            'defaultEnabled' => 'Y',
            'sortOrder' => 1,
            'DateAdded' => date('Y-m-d H:i:s'),
            'Suspended' => 'N'
        ),
        array(
            'eventSlug' => 'leave_application_submitted',
            'eventName' => 'Leave Application Submitted',
            'eventDescription' => 'Notification sent to employee when their leave application is submitted',
            'moduleID' => $moduleID,
            'priorityLevel' => 'medium',
            'isActive' => 'Y',
            'defaultEnabled' => 'Y',
            'sortOrder' => 2,
            'DateAdded' => date('Y-m-d H:i:s'),
            'Suspended' => 'N'
        ),
        array(
            'eventSlug' => 'leave_approved',
            'eventName' => 'Leave Approved',
            'eventDescription' => 'Notification sent to employee when their leave application is approved',
            'moduleID' => $moduleID,
            'priorityLevel' => 'medium',
            'isActive' => 'Y',
            'defaultEnabled' => 'Y',
            'sortOrder' => 3,
            'DateAdded' => date('Y-m-d H:i:s'),
            'Suspended' => 'N'
        ),
        array(
            'eventSlug' => 'leave_rejected',
            'eventName' => 'Leave Rejected',
            'eventDescription' => 'Notification sent to employee when their leave application is rejected',
            'moduleID' => $moduleID,
            'priorityLevel' => 'high',
            'isActive' => 'Y',
            'defaultEnabled' => 'Y',
            'sortOrder' => 4,
            'DateAdded' => date('Y-m-d H:i:s'),
            'Suspended' => 'N'
        )
    );

    $createdEvents = array();
    $skippedEvents = array();

    foreach ($events as $eventData) {
        $eventID = null;

        // Check if event already exists
        $existing = $DBConn->fetch_all_rows(
            "SELECT * FROM tija_notification_events WHERE eventSlug = ?",
            array(array($eventData['eventSlug'], 's'))
        );

        if ($existing && count($existing) > 0) {
            $existingEvent = is_object($existing[0]) ? (array)$existing[0] : $existing[0];
            $eventID = isset($existingEvent['eventID']) ? (int)$existingEvent['eventID'] : null;
            $skippedEvents[] = $eventData['eventSlug'];
        } else {
            $eventResult = $DBConn->insert_data('tija_notification_events', $eventData);
            if ($eventResult) {
                $eventID = $DBConn->lastInsertId();
                $createdEvents[] = $eventData['eventSlug'];
            }
        }

        if (!$eventID) {
            continue;
        }

        // Ensure in-app template exists
        ensureChannelTemplate(
            $eventID,
            $inAppChannelID,
            getDefaultSubject($eventData['eventSlug']),
            getDefaultBody($eventData['eventSlug']),
            $DBConn
        );

        // Ensure HTML email template exists/updated
        ensureEmailTemplate(
            $eventID,
            $emailChannelID,
            $eventData['eventSlug'],
            $DBConn
        );
    }

    echo json_encode(array(
        'success' => true,
        'message' => 'Notification events setup completed',
        'moduleID' => $moduleID,
        'channelID' => $channelID,
        'createdEvents' => $createdEvents,
        'skippedEvents' => $skippedEvents
    ));

} catch (Exception $e) {
    error_log("Create leave notification events error: " . $e->getMessage());
    echo json_encode(array(
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ));
}

/**
 * Get default subject template for event
 */
function getDefaultSubject($eventSlug) {
    $subjects = array(
        'leave_pending_approval' => 'Leave Application Pending Approval - {{employee_name}}',
        'leave_application_submitted' => 'Leave Application Submitted',
        'leave_approved' => 'Leave Application Approved',
        'leave_rejected' => 'Leave Application Rejected'
    );
    return isset($subjects[$eventSlug]) ? $subjects[$eventSlug] : 'Notification';
}

/**
 * Get default body template for event
 */
function getDefaultBody($eventSlug) {
    $bodies = array(
        'leave_pending_approval' => '{{employee_name}} has submitted a leave application for {{leave_type}} from {{start_date}} to {{end_date}} ({{total_days}} day(s)). Please review and approve.',
        'leave_application_submitted' => 'Your leave application for {{leave_type}} from {{start_date}} to {{end_date}} ({{total_days}} day(s)) has been submitted successfully and is pending approval.',
        'leave_approved' => 'Your leave application for {{leave_type}} from {{start_date}} to {{end_date}} ({{total_days}} day(s)) has been approved.',
        'leave_rejected' => 'Your leave application for {{leave_type}} from {{start_date}} to {{end_date}} ({{total_days}} day(s)) has been rejected.'
    );
    return isset($bodies[$eventSlug]) ? $bodies[$eventSlug] : 'You have a new notification.';
}

/**
 * Ensure that a template exists (or is updated) for the provided channel.
 */
function ensureChannelTemplate($eventID, $channelID, $subject, $body, $DBConn) {
    if (empty($eventID) || empty($channelID)) {
        return;
    }

    $existing = $DBConn->fetch_all_rows(
        "SELECT * FROM tija_notification_templates WHERE eventID = ? AND channelID = ? LIMIT 1",
        array(array($eventID, 'i'), array($channelID, 'i'))
    );

    if ($existing && count($existing) > 0) {
        $template = is_object($existing[0]) ? (array)$existing[0] : $existing[0];
        $DBConn->update_table(
            'tija_notification_templates',
            array(
                'templateSubject' => $subject,
                'templateBody' => $body,
                'isActive' => 'Y',
                'isDefault' => 'Y',
                'isSystem' => 'Y',
                'Suspended' => 'N'
            ),
            array('templateID' => $template['templateID'])
        );
    } else {
        $DBConn->insert_data('tija_notification_templates', array(
            'eventID' => $eventID,
            'channelID' => $channelID,
            'templateSubject' => $subject,
            'templateBody' => $body,
            'isActive' => 'Y',
            'isDefault' => 'Y',
            'isSystem' => 'Y',
            'DateAdded' => date('Y-m-d H:i:s'),
            'Suspended' => 'N'
        ));
    }
}

/**
 * Create/refresh the HTML email template for the supplied event.
 */
function ensureEmailTemplate($eventID, $emailChannelID, $eventSlug, $DBConn) {
    if (empty($eventID) || empty($emailChannelID)) {
        return;
    }

    $subject = getDefaultSubject($eventSlug);
    $body = getEmailTemplateBody($eventSlug);

    ensureChannelTemplate($eventID, $emailChannelID, $subject, $body, $DBConn);
}

/**
 * Build the responsive HTML email template with CTA button.
 */
function getEmailTemplateBody($eventSlug) {
    $copy = getEmailTemplateCopy($eventSlug);

    $title = htmlspecialchars($copy['title'], ENT_QUOTES, 'UTF-8');
    $detailHeading = htmlspecialchars($copy['detail_heading'], ENT_QUOTES, 'UTF-8');
    $ctaLabel = htmlspecialchars($copy['cta_label'], ENT_QUOTES, 'UTF-8');
    $intro = $copy['intro'];
    $extraHtml = $copy['extra_html'] ?? '';

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f6fb;font-family:'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
        <tr>
            <td style="padding:32px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);">
                    <tr>
                        <td style="padding:40px 40px 32px;">
                            <h2 style="margin:0 0 12px;font-size:24px;color:#0f172a;">{$title}</h2>
                            <p style="margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;">{$intro}</p>
                            <div style="padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;">
                                <p style="margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;">{$detailHeading}</p>
                                <p style="margin:0 0 8px;font-size:14px;color:#0f172a;"><strong>Employee:</strong> {{employee_name}}</p>
                                <p style="margin:0 0 8px;font-size:14px;color:#0f172a;"><strong>Leave type:</strong> {{leave_type}}</p>
                                <p style="margin:0 0 8px;font-size:14px;color:#0f172a;"><strong>Dates:</strong> {{start_date}} – {{end_date}}</p>
                                <p style="margin:0;font-size:14px;color:#0f172a;"><strong>Total days:</strong> {{total_days}}</p>
                            </div>
                            {$extraHtml}
                            <div style="text-align:center;margin:32px 0 16px;">
                                <a href="{{application_link_full}}" style="display:inline-block;padding:14px 28px;background-color:#2563eb;color:#ffffff;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;">{$ctaLabel}</a>
                            </div>
                            <p style="font-size:13px;color:#6b7280;text-align:center;margin:0 0 32px;">
                                Or copy and paste this link into your browser:<br>
                                <a href="{{application_link_full}}" style="color:#2563eb;text-decoration:none;">{{application_link_full}}</a>
                            </p>
                            <p style="font-size:12px;color:#94a3b8;text-align:center;margin:0;">
                                Sent from {{site_name}} · {{site_url}}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
}

/**
 * Copy variations per leave notification event.
 */
function getEmailTemplateCopy($eventSlug) {
    switch ($eventSlug) {
        case 'leave_pending_approval':
            return array(
                'title' => 'Leave Request Pending Your Approval',
                'intro' => '{{employee_name}} has submitted a {{leave_type}} request that requires your review.',
                'detail_heading' => 'Request Summary',
                'cta_label' => 'Review Leave Request',
                'extra_html' => '<p style="margin:20px 0 0;font-size:14px;color:#1f2937;">Approval step: <strong>{{approval_level}}</strong></p>'
            );
        case 'leave_application_submitted':
            return array(
                'title' => 'Leave Application Submitted',
                'intro' => 'Your leave request has been received and routed to your approvers.',
                'detail_heading' => 'Request Summary',
                'cta_label' => 'View Application',
                'extra_html' => '<p style="margin:20px 0 0;font-size:14px;color:#1f2937;">Reason provided: {{leave_reason}}</p>'
            );
        case 'leave_approved':
            return array(
                'title' => 'Leave Application Approved',
                'intro' => 'Great news — {{approver_name}} approved your leave request.',
                'detail_heading' => 'Approved Leave Details',
                'cta_label' => 'View Application',
                'extra_html' => '<div style="margin:24px 0;padding:16px;border-radius:12px;background-color:#ecfdf5;"><p style="margin:0;font-size:14px;color:#065f46;"><strong>Approver comments:</strong> {{approver_comments}}</p></div>'
            );
        case 'leave_rejected':
            return array(
                'title' => 'Leave Application Update',
                'intro' => 'Your leave request was not approved by {{approver_name}}.',
                'detail_heading' => 'Request Summary',
                'cta_label' => 'Review Details',
                'extra_html' => '<div style="margin:24px 0;padding:16px;border-radius:12px;background-color:#fef2f2;"><p style="margin:0;font-size:14px;color:#991b1b;"><strong>Reason provided:</strong> {{approver_comments}}</p></div>'
            );
        default:
            return array(
                'title' => 'Leave Application Update',
                'intro' => 'There is an update to the leave request shown below.',
                'detail_heading' => 'Application Summary',
                'cta_label' => 'Open Leave Application',
                'extra_html' => ''
            );
    }
}

