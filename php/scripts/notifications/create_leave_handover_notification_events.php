<?php
/**
 * Create Leave Handover Notification Events
 *
 * Seeds notification events + templates for handover workflow (applicant + nominee emails/in-app).
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
    $moduleID = ensureLeaveModule($DBConn);
    $inAppChannelID = ensureChannel('in_app', 'In-App Notification', 'In-application notifications', 1, $DBConn);
    $emailChannelID = ensureChannel('email', 'Email Notification', 'Outbound email notifications', 2, $DBConn);

    $events = array(
        array(
            'eventSlug' => 'leave_handover_assignment',
            'eventName' => 'New Handover Assignment',
            'eventDescription' => 'Sent to nominees when they are allocated handover tasks',
            'priorityLevel' => 'high',
            'sortOrder' => 1
        ),
        array(
            'eventSlug' => 'leave_handover_submitted',
            'eventName' => 'Handover Plan Submitted',
            'eventDescription' => 'Sent to applicants confirming their handover plan and nominee',
            'priorityLevel' => 'medium',
            'sortOrder' => 2
        ),
        array(
            'eventSlug' => 'leave_handover_revision_requested',
            'eventName' => 'Handover Revision Requested',
            'eventDescription' => 'Sent to applicants when the nominee needs additional information',
            'priorityLevel' => 'high',
            'sortOrder' => 3
        ),
        array(
            'eventSlug' => 'leave_handover_accepted',
            'eventName' => 'Handover Accepted',
            'eventDescription' => 'Sent to applicants once the nominee accepts the handover',
            'priorityLevel' => 'medium',
            'sortOrder' => 4
        ),
        array(
            'eventSlug' => 'leave_handover_completed',
            'eventName' => 'Handover Completed',
            'eventDescription' => 'Sent when all handover tasks are confirmed',
            'priorityLevel' => 'medium',
            'sortOrder' => 5
        ),
        array(
            'eventSlug' => 'leave_handover_timer_expired',
            'eventName' => 'Handover Response Overdue',
            'eventDescription' => 'Sent when the nominee has not acknowledged the handover in time',
            'priorityLevel' => 'high',
            'sortOrder' => 6
        )
    );

    $created = array();
    $skipped = array();

    foreach ($events as $eventData) {
        $eventPayload = array_merge($eventData, array(
            'moduleID' => $moduleID,
            'isActive' => 'Y',
            'defaultEnabled' => 'Y',
            'DateAdded' => date('Y-m-d H:i:s'),
            'Suspended' => 'N'
        ));

        $eventRecord = $DBConn->fetch_all_rows(
            "SELECT * FROM tija_notification_events WHERE eventSlug = ?",
            array(array($eventData['eventSlug'], 's'))
        );

        if ($eventRecord && count($eventRecord) > 0) {
            $event = is_object($eventRecord[0]) ? (array)$eventRecord[0] : $eventRecord[0];
            $eventID = (int)$event['eventID'];
            $skipped[] = $eventData['eventSlug'];
        } else {
            $result = $DBConn->insert_data('tija_notification_events', $eventPayload);
            if (!$result) {
                throw new Exception('Failed to create notification event: ' . $eventData['eventSlug']);
            }
            $eventID = (int)$DBConn->lastInsertId();
            $created[] = $eventData['eventSlug'];
        }

        upsertTemplate($eventID, $inAppChannelID, getInAppSubject($eventData['eventSlug']), getInAppBody($eventData['eventSlug']), $DBConn);
        upsertTemplate($eventID, $emailChannelID, getEmailSubject($eventData['eventSlug']), buildEmailBody($eventData['eventSlug']), $DBConn);
    }

    echo json_encode(array(
        'success' => true,
        'message' => 'Handover notification events ready',
        'createdEvents' => $created,
        'skippedEvents' => $skipped
    ));
} catch (Exception $e) {
    error_log('create_leave_handover_notification_events error: ' . $e->getMessage());
    echo json_encode(array('success' => false, 'message' => $e->getMessage()));
}

function ensureLeaveModule($DBConn) {
    $rows = $DBConn->fetch_all_rows(
        "SELECT * FROM tija_notification_modules WHERE moduleSlug = ?",
        array(array('leave', 's'))
    );

    if ($rows && count($rows) > 0) {
        $module = is_object($rows[0]) ? (array)$rows[0] : $rows[0];
        return (int)$module['moduleID'];
    }

    $result = $DBConn->insert_data('tija_notification_modules', array(
        'moduleName' => 'Leave Management',
        'moduleSlug' => 'leave',
        'moduleDescription' => 'Leave application and handover notifications',
        'moduleIcon' => 'ri-calendar-2-line',
        'isActive' => 'Y',
        'sortOrder' => 10,
        'DateAdded' => date('Y-m-d H:i:s'),
        'Suspended' => 'N'
    ));

    if (!$result) {
        throw new Exception('Unable to create leave notification module');
    }

    return (int)$DBConn->lastInsertId();
}

function ensureChannel($slug, $name, $description, $sortOrder, $DBConn) {
    $rows = $DBConn->fetch_all_rows(
        "SELECT * FROM tija_notification_channels WHERE channelSlug = ?",
        array(array($slug, 's'))
    );

    if ($rows && count($rows) > 0) {
        $channel = is_object($rows[0]) ? (array)$rows[0] : $rows[0];
        return (int)$channel['channelID'];
    }

    $result = $DBConn->insert_data('tija_notification_channels', array(
        'channelName' => $name,
        'channelSlug' => $slug,
        'channelDescription' => $description,
        'isActive' => 'Y',
        'sortOrder' => $sortOrder,
        'DateAdded' => date('Y-m-d H:i:s'),
        'Suspended' => 'N'
    ));

    if (!$result) {
        throw new Exception('Failed to create channel: ' . $slug);
    }

    return (int)$DBConn->lastInsertId();
}

function upsertTemplate($eventID, $channelID, $subject, $body, $DBConn) {
    if (!$eventID || !$channelID) {
        return;
    }

    $existing = $DBConn->fetch_all_rows(
        "SELECT * FROM tija_notification_templates WHERE eventID = ? AND channelID = ? LIMIT 1",
        array(array($eventID, 'i'), array($channelID, 'i'))
    );

    $payload = array(
        'templateSubject' => $subject,
        'templateBody' => $body,
        'isActive' => 'Y',
        'isDefault' => 'Y',
        'isSystem' => 'Y',
        'Suspended' => 'N',
        'LastUpdate' => date('Y-m-d H:i:s')
    );

    if ($existing && count($existing) > 0) {
        $template = is_object($existing[0]) ? (array)$existing[0] : $existing[0];
        $DBConn->update_table('tija_notification_templates', $payload, array('templateID' => $template['templateID']));
    } else {
        $payload['eventID'] = $eventID;
        $payload['channelID'] = $channelID;
        $payload['DateAdded'] = date('Y-m-d H:i:s');
        $DBConn->insert_data('tija_notification_templates', $payload);
    }
}

function getInAppSubject($slug) {
    $subjects = array(
        'leave_handover_assignment' => 'New handover assignment from {{employee_name}}',
        'leave_handover_submitted' => 'Handover plan logged for {{leave_type}}',
        'leave_handover_revision_requested' => 'Revision requested by {{nominee_name}}',
        'leave_handover_accepted' => '{{nominee_name}} accepted the handover',
        'leave_handover_completed' => 'Handover confirmed for {{leave_type}}',
        'leave_handover_timer_expired' => 'Handover response overdue'
    );
    return $subjects[$slug] ?? 'Leave handover update';
}

function getInAppBody($slug) {
    $bodies = array(
        'leave_handover_assignment' => '{{employee_name}} assigned their {{leave_type}} handover to you for {{start_date}} – {{end_date}}. Review the tasks and acknowledge the handover.',
        'leave_handover_submitted' => 'Your {{leave_type}} handover has been saved. {{nominee_name}} has been notified and can now acknowledge the plan.',
        'leave_handover_revision_requested' => '{{nominee_name}} requested revisions to your {{leave_type}} handover. Details: {{requested_changes}}.',
        'leave_handover_accepted' => '{{nominee_name}} confirmed readiness to cover your {{leave_type}} handover.',
        'leave_handover_completed' => 'All tasks for {{leave_type}} ({{start_date}} – {{end_date}}) are confirmed. Your handover is complete.',
        'leave_handover_timer_expired' => 'The nominee has not acknowledged the {{leave_type}} handover for {{start_date}} – {{end_date}}. Please follow up.'
    );
    return $bodies[$slug] ?? 'You have a new leave handover update.';
}

function getEmailSubject($slug) {
    return getInAppSubject($slug);
}

function buildEmailBody($slug) {
    $copy = array(
        'leave_handover_assignment' => array(
            'title' => 'You were nominated to cover for {{employee_name}}',
            'detail_heading' => 'Handover Summary',
            'cta_label' => 'Review Handover',
            'intro' => 'Please review the assigned responsibilities while {{employee_name}} is away.',
            'extra_html' => '<p style="margin:0;font-size:14px;color:#0f172a;"><strong>Next steps:</strong> Sign in to acknowledge the handover, confirm access to credentials, and raise any blockers.</p>'
        ),
        'leave_handover_submitted' => array(
            'title' => 'Handover plan recorded for {{leave_type}}',
            'detail_heading' => 'Plan Overview',
            'cta_label' => 'View Handover',
            'intro' => '{{nominee_name}} has been notified. Track confirmations and update tasks as needed.',
            'extra_html' => ''
        ),
        'leave_handover_revision_requested' => array(
            'title' => '{{nominee_name}} requested updates',
            'detail_heading' => 'Revision Details',
            'cta_label' => 'Update Handover',
            'intro' => 'Your nominee needs more information before accepting the handover.',
            'extra_html' => '<p style="margin:0;font-size:14px;color:#0f172a;"><strong>Requested changes:</strong><br>{{requested_changes}}</p>'
        ),
        'leave_handover_accepted' => array(
            'title' => '{{nominee_name}} accepted your handover',
            'detail_heading' => 'Handover Snapshot',
            'cta_label' => 'Open Handover',
            'intro' => 'All required tasks have been acknowledged. Managers can now proceed with review.',
            'extra_html' => ''
        ),
        'leave_handover_completed' => array(
            'title' => 'Handover complete for {{leave_type}}',
            'detail_heading' => 'What happens next',
            'cta_label' => 'View Application',
            'intro' => 'Every task has been confirmed. The leave application is ready for final processing.',
            'extra_html' => ''
        ),
        'leave_handover_timer_expired' => array(
            'title' => 'Handover acknowledgement overdue',
            'detail_heading' => 'Pending Handover',
            'cta_label' => 'Follow Up Now',
            'intro' => 'The nominee has not responded within the required timeframe.',
            'extra_html' => '<p style="margin:0;font-size:14px;color:#0f172a;">Please reach out to {{nominee_name}} or reassign the handover so that coverage is confirmed.</p>'
        )
    );

    $content = $copy[$slug] ?? $copy['leave_handover_assignment'];
    $title = htmlspecialchars($content['title'], ENT_QUOTES, 'UTF-8');
    $detailHeading = htmlspecialchars($content['detail_heading'], ENT_QUOTES, 'UTF-8');
    $ctaLabel = htmlspecialchars($content['cta_label'], ENT_QUOTES, 'UTF-8');
    $intro = $content['intro'];
    $extraHtml = $content['extra_html'] ?? '';

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
                                <p style="margin:0 0 8px;font-size:14px;color:#0f172a;"><strong>Nominee:</strong> {{nominee_name}}</p>
                                <p style="margin:0 0 8px;font-size:14px;color:#0f172a;"><strong>Leave type:</strong> {{leave_type}}</p>
                                <p style="margin:0 0 8px;font-size:14px;color:#0f172a;"><strong>Dates:</strong> {{start_date}} – {{end_date}}</p>
                                {$extraHtml}
                            </div>
                            <div style="margin-top:32px;">
                                <a href="{{absolute_link}}" style="display:inline-block;padding:14px 28px;border-radius:999px;background-color:#2563eb;color:#ffffff;text-decoration:none;font-weight:600;">{$ctaLabel}</a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 40px;border-top:1px solid #e2e8f0;">
                            <p style="margin:0;font-size:13px;color:#94a3b8;">You’re receiving this message because you’re part of a leave handover workflow.</p>
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

