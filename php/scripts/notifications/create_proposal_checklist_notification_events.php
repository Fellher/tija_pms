<?php
/**
 * Create Proposal Checklist Notification Events
 *
 * This script creates the required notification events for the proposal
 * checklist module including:
 * - Checklist item assignment notifications
 * - Checklist item submission notifications
 * - Checklist item status update notifications
 * - Deadline reminders
 *
 * Run this script once to set up the notification events.
 *
 * @version 1.0
 * @date 2025-12-12
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
    // =========================================================================
    // STEP 1: Create or get the Sales/Proposals module
    // =========================================================================
    $salesModule = $DBConn->fetch_all_rows(
        "SELECT * FROM tija_notification_modules WHERE moduleSlug = ?",
        array(array('sales', 's'))
    );

    $moduleID = null;
    if (!$salesModule || count($salesModule) === 0) {
        // Create sales module
        $moduleData = array(
            'moduleName' => 'Sales & Proposals',
            'moduleSlug' => 'sales',
            'moduleDescription' => 'Sales opportunities, proposals, and checklist notifications',
            'moduleIcon' => 'ri-file-list-3-line',
            'isActive' => 'Y',
            'sortOrder' => 20,
            'DateAdded' => date('Y-m-d H:i:s'),
            'Suspended' => 'N'
        );

        error_log("Creating sales notification module: " . print_r($moduleData, true));

        $moduleResult = $DBConn->insert_data('tija_notification_modules', $moduleData);
        if ($moduleResult) {
            $moduleID = $DBConn->lastInsertId();
        } else {
            throw new Exception('Failed to create sales notification module');
        }
    } else {
        $module = is_object($salesModule[0]) ? (array)$salesModule[0] : $salesModule[0];
        $moduleID = isset($module['moduleID']) ? (int)$module['moduleID'] : null;
    }

    if (!$moduleID) {
        throw new Exception('Could not determine module ID');
    }

    // =========================================================================
    // STEP 2: Ensure notification channels exist (in_app and email)
    // =========================================================================

    // Check/create in_app channel
    $inAppChannel = $DBConn->fetch_all_rows(
        "SELECT * FROM tija_notification_channels WHERE channelSlug = ?",
        array(array('in_app', 's'))
    );

    if (!$inAppChannel || count($inAppChannel) === 0) {
        $channelData = array(
            'channelName' => 'In-App Notification',
            'channelSlug' => 'in_app',
            'channelDescription' => 'In-application notifications',
            'isActive' => 'Y',
            'sortOrder' => 1,
            'DateAdded' => date('Y-m-d H:i:s'),
            'Suspended' => 'N'
        );

        if (!$DBConn->insert_data('tija_notification_channels', $channelData)) {
            throw new Exception('Failed to create in_app notification channel');
        }
        $inAppChannelID = $DBConn->lastInsertId();
    } else {
        $channel = is_object($inAppChannel[0]) ? (array)$inAppChannel[0] : $inAppChannel[0];
        $inAppChannelID = isset($channel['channelID']) ? (int)$channel['channelID'] : null;
    }

    // Check/create email channel
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

    // =========================================================================
    // STEP 3: Define proposal checklist notification events
    // =========================================================================
    $events = array(
        // Notification to assignee when they are assigned a checklist item
        array(
            'eventSlug' => 'checklist_item_assigned',
            'eventName' => 'Checklist Item Assigned',
            'eventDescription' => 'Notification sent to team member when they are assigned a proposal checklist requirement',
            'moduleID' => $moduleID,
            'priorityLevel' => 'high',
            'isActive' => 'Y',
            'defaultEnabled' => 'Y',
            'sortOrder' => 1,
            'DateAdded' => date('Y-m-d H:i:s'),
            'Suspended' => 'N'
        ),
        // Notification to checklist owner when an item is submitted
        array(
            'eventSlug' => 'checklist_item_submitted',
            'eventName' => 'Checklist Item Submitted',
            'eventDescription' => 'Notification sent to checklist owner when an assigned team member submits their work',
            'moduleID' => $moduleID,
            'priorityLevel' => 'medium',
            'isActive' => 'Y',
            'defaultEnabled' => 'Y',
            'sortOrder' => 2,
            'DateAdded' => date('Y-m-d H:i:s'),
            'Suspended' => 'N'
        ),
        // Notification to assignee when their submission is approved
        array(
            'eventSlug' => 'checklist_item_approved',
            'eventName' => 'Checklist Item Approved',
            'eventDescription' => 'Notification sent to assignee when their checklist item submission is approved',
            'moduleID' => $moduleID,
            'priorityLevel' => 'medium',
            'isActive' => 'Y',
            'defaultEnabled' => 'Y',
            'sortOrder' => 3,
            'DateAdded' => date('Y-m-d H:i:s'),
            'Suspended' => 'N'
        ),
        // Notification to assignee when their submission needs revision
        array(
            'eventSlug' => 'checklist_item_revision_required',
            'eventName' => 'Checklist Item Revision Required',
            'eventDescription' => 'Notification sent to assignee when their checklist item submission requires revision',
            'moduleID' => $moduleID,
            'priorityLevel' => 'high',
            'isActive' => 'Y',
            'defaultEnabled' => 'Y',
            'sortOrder' => 4,
            'DateAdded' => date('Y-m-d H:i:s'),
            'Suspended' => 'N'
        ),
        // Reminder notification for upcoming deadline
        array(
            'eventSlug' => 'checklist_item_deadline_reminder',
            'eventName' => 'Checklist Item Deadline Reminder',
            'eventDescription' => 'Reminder notification sent to assignee when checklist item deadline is approaching',
            'moduleID' => $moduleID,
            'priorityLevel' => 'high',
            'isActive' => 'Y',
            'defaultEnabled' => 'Y',
            'sortOrder' => 5,
            'DateAdded' => date('Y-m-d H:i:s'),
            'Suspended' => 'N'
        ),
        // Notification for overdue items
        array(
            'eventSlug' => 'checklist_item_overdue',
            'eventName' => 'Checklist Item Overdue',
            'eventDescription' => 'Notification sent when a checklist item has passed its due date without completion',
            'moduleID' => $moduleID,
            'priorityLevel' => 'critical',
            'isActive' => 'Y',
            'defaultEnabled' => 'Y',
            'sortOrder' => 6,
            'DateAdded' => date('Y-m-d H:i:s'),
            'Suspended' => 'N'
        ),
        // Notification to proposal owner when all checklist items are completed
        array(
            'eventSlug' => 'proposal_checklist_completed',
            'eventName' => 'Proposal Checklist Completed',
            'eventDescription' => 'Notification sent to proposal owner when all checklist items have been completed',
            'moduleID' => $moduleID,
            'priorityLevel' => 'medium',
            'isActive' => 'Y',
            'defaultEnabled' => 'Y',
            'sortOrder' => 7,
            'DateAdded' => date('Y-m-d H:i:s'),
            'Suspended' => 'N'
        )
    );

    $createdEvents = array();
    $skippedEvents = array();

    // =========================================================================
    // STEP 4: Create events and their templates
    // =========================================================================
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

        // Create in-app template
        ensureChannelTemplate(
            $eventID,
            $inAppChannelID,
            getDefaultSubject($eventData['eventSlug']),
            getDefaultBody($eventData['eventSlug']),
            $DBConn
        );

        // Create HTML email template
        ensureEmailTemplate(
            $eventID,
            $emailChannelID,
            $eventData['eventSlug'],
            $DBConn
        );
    }

    echo json_encode(array(
        'success' => true,
        'message' => 'Proposal checklist notification events setup completed',
        'moduleID' => $moduleID,
        'inAppChannelID' => $inAppChannelID,
        'emailChannelID' => $emailChannelID,
        'createdEvents' => $createdEvents,
        'skippedEvents' => $skippedEvents
    ));

} catch (Exception $e) {
    error_log("Create proposal checklist notification events error: " . $e->getMessage());
    echo json_encode(array(
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ));
}

// =============================================================================
// HELPER FUNCTIONS
// =============================================================================

/**
 * Get default in-app notification subject template for event
 */
function getDefaultSubject($eventSlug) {
    $subjects = array(
        'checklist_item_assigned' => 'New Requirement Assigned: {{requirement_name}}',
        'checklist_item_submitted' => 'Requirement Submitted: {{requirement_name}}',
        'checklist_item_approved' => 'Requirement Approved: {{requirement_name}}',
        'checklist_item_revision_required' => 'Revision Required: {{requirement_name}}',
        'checklist_item_deadline_reminder' => 'Deadline Approaching: {{requirement_name}}',
        'checklist_item_overdue' => 'OVERDUE: {{requirement_name}}',
        'proposal_checklist_completed' => 'Checklist Completed: {{proposal_title}}'
    );
    return isset($subjects[$eventSlug]) ? $subjects[$eventSlug] : 'Proposal Notification';
}

/**
 * Get default in-app notification body template for event
 */
function getDefaultBody($eventSlug) {
    $bodies = array(
        'checklist_item_assigned' => 'You have been assigned a new requirement "{{requirement_name}}" for proposal "{{proposal_title}}". Due date: {{due_date}}. Assigned by: {{assignor_name}}.',
        'checklist_item_submitted' => '{{assignee_name}} has submitted "{{requirement_name}}" for proposal "{{proposal_title}}". Please review the submission.',
        'checklist_item_approved' => 'Your submission for "{{requirement_name}}" on proposal "{{proposal_title}}" has been approved by {{reviewer_name}}.',
        'checklist_item_revision_required' => 'Your submission for "{{requirement_name}}" on proposal "{{proposal_title}}" requires revision. Feedback: {{feedback}}',
        'checklist_item_deadline_reminder' => 'Reminder: The requirement "{{requirement_name}}" for proposal "{{proposal_title}}" is due on {{due_date}}. {{days_remaining}} day(s) remaining.',
        'checklist_item_overdue' => 'URGENT: The requirement "{{requirement_name}}" for proposal "{{proposal_title}}" was due on {{due_date}} and is now {{days_overdue}} day(s) overdue.',
        'proposal_checklist_completed' => 'All checklist items for proposal "{{proposal_title}}" have been completed. The proposal is ready for final review.'
    );
    return isset($bodies[$eventSlug]) ? $bodies[$eventSlug] : 'You have a new proposal notification.';
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
 * Build the responsive HTML email template for proposal checklist events.
 */
function getEmailTemplateBody($eventSlug) {
    $copy = getEmailTemplateCopy($eventSlug);

    $title = htmlspecialchars($copy['title'], ENT_QUOTES, 'UTF-8');
    $detailHeading = htmlspecialchars($copy['detail_heading'], ENT_QUOTES, 'UTF-8');
    $ctaLabel = htmlspecialchars($copy['cta_label'], ENT_QUOTES, 'UTF-8');
    $intro = $copy['intro'];
    $extraHtml = $copy['extra_html'] ?? '';
    $headerColor = $copy['header_color'] ?? '#2563eb';
    $headerBg = $copy['header_bg'] ?? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';

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
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width:640px;margin:0 auto;background-color:#ffffff;border-radius:16px;box-shadow:0 10px 30px rgba(15,23,42,0.08);overflow:hidden;">
                    <!-- Header Banner -->
                    <tr>
                        <td style="background:{$headerBg};padding:24px 40px;">
                            <h1 style="margin:0;font-size:20px;color:#ffffff;font-weight:600;">
                                <span style="display:inline-block;vertical-align:middle;margin-right:10px;">📋</span>
                                Proposal Requirement Update
                            </h1>
                        </td>
                    </tr>
                    <!-- Main Content -->
                    <tr>
                        <td style="padding:40px 40px 32px;">
                            <h2 style="margin:0 0 12px;font-size:24px;color:#0f172a;">{$title}</h2>
                            <p style="margin:0 0 24px;font-size:15px;line-height:1.6;color:#334155;">{$intro}</p>

                            <!-- Details Card -->
                            <div style="padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;">
                                <p style="margin:0 0 12px;font-size:13px;letter-spacing:0.08em;color:#94a3b8;text-transform:uppercase;">{$detailHeading}</p>
                                <p style="margin:0 0 8px;font-size:14px;color:#0f172a;"><strong>Proposal:</strong> {{proposal_title}}</p>
                                <p style="margin:0 0 8px;font-size:14px;color:#0f172a;"><strong>Requirement:</strong> {{requirement_name}}</p>
                                <p style="margin:0 0 8px;font-size:14px;color:#0f172a;"><strong>Category:</strong> {{checklist_name}}</p>
                                <p style="margin:0 0 8px;font-size:14px;color:#0f172a;"><strong>Assigned To:</strong> {{assignee_name}}</p>
                                <p style="margin:0;font-size:14px;color:#0f172a;"><strong>Due Date:</strong> {{due_date}}</p>
                            </div>

                            {$extraHtml}

                            <!-- CTA Button -->
                            <div style="text-align:center;margin:32px 0 16px;">
                                <a href="{{action_link_full}}" style="display:inline-block;padding:14px 28px;background-color:{$headerColor};color:#ffffff;text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;">{$ctaLabel}</a>
                            </div>

                            <!-- Link Fallback -->
                            <p style="font-size:13px;color:#6b7280;text-align:center;margin:0 0 32px;">
                                Or copy and paste this link into your browser:<br>
                                <a href="{{action_link_full}}" style="color:#2563eb;text-decoration:none;">{{action_link_full}}</a>
                            </p>

                            <!-- Footer -->
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
 * Copy variations per proposal checklist notification event.
 */
function getEmailTemplateCopy($eventSlug) {
    switch ($eventSlug) {
        case 'checklist_item_assigned':
            return array(
                'title' => 'New Requirement Assigned to You',
                'intro' => '{{assignor_name}} has assigned you a new requirement for the proposal "{{proposal_title}}". Please review the details and complete the task by the due date.',
                'detail_heading' => 'Assignment Details',
                'cta_label' => 'View Requirement',
                'header_color' => '#2563eb',
                'header_bg' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                'extra_html' => '
                    <div style="margin:24px 0;padding:16px;border-radius:12px;background-color:#eff6ff;border-left:4px solid #2563eb;">
                        <p style="margin:0 0 8px;font-size:14px;color:#1e40af;font-weight:600;">📝 Instructions from Assignor:</p>
                        <p style="margin:0;font-size:14px;color:#1e40af;">{{instructions}}</p>
                    </div>'
            );

        case 'checklist_item_submitted':
            return array(
                'title' => 'Requirement Submission Received',
                'intro' => '{{assignee_name}} has submitted their work for the requirement "{{requirement_name}}". Please review the submission and provide feedback.',
                'detail_heading' => 'Submission Details',
                'cta_label' => 'Review Submission',
                'header_color' => '#059669',
                'header_bg' => 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
                'extra_html' => '
                    <div style="margin:24px 0;padding:16px;border-radius:12px;background-color:#ecfdf5;">
                        <p style="margin:0;font-size:14px;color:#065f46;">
                            <strong>Submitted on:</strong> {{submission_date}}<br>
                            <strong>Files attached:</strong> {{attachment_count}} file(s)
                        </p>
                    </div>'
            );

        case 'checklist_item_approved':
            return array(
                'title' => 'Your Submission Has Been Approved! ✓',
                'intro' => 'Great news! Your submission for "{{requirement_name}}" has been reviewed and approved by {{reviewer_name}}.',
                'detail_heading' => 'Approved Requirement',
                'cta_label' => 'View Details',
                'header_color' => '#059669',
                'header_bg' => 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
                'extra_html' => '
                    <div style="margin:24px 0;padding:16px;border-radius:12px;background-color:#ecfdf5;">
                        <p style="margin:0;font-size:14px;color:#065f46;"><strong>Reviewer Comments:</strong> {{reviewer_comments}}</p>
                    </div>'
            );

        case 'checklist_item_revision_required':
            return array(
                'title' => 'Revision Required for Your Submission',
                'intro' => 'Your submission for "{{requirement_name}}" has been reviewed by {{reviewer_name}} and requires some revisions before it can be approved.',
                'detail_heading' => 'Requirement Details',
                'cta_label' => 'View Feedback & Revise',
                'header_color' => '#d97706',
                'header_bg' => 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)',
                'extra_html' => '
                    <div style="margin:24px 0;padding:16px;border-radius:12px;background-color:#fffbeb;border-left:4px solid #f59e0b;">
                        <p style="margin:0 0 8px;font-size:14px;color:#92400e;font-weight:600;">⚠️ Revision Feedback:</p>
                        <p style="margin:0;font-size:14px;color:#92400e;">{{feedback}}</p>
                    </div>'
            );

        case 'checklist_item_deadline_reminder':
            return array(
                'title' => 'Deadline Reminder: {{days_remaining}} Day(s) Remaining',
                'intro' => 'This is a reminder that the requirement "{{requirement_name}}" is due on {{due_date}}. Please ensure your submission is completed before the deadline.',
                'detail_heading' => 'Requirement Details',
                'cta_label' => 'Complete Requirement',
                'header_color' => '#d97706',
                'header_bg' => 'linear-gradient(135deg, #fbbf24 0%, #d97706 100%)',
                'extra_html' => '
                    <div style="margin:24px 0;padding:16px;border-radius:12px;background-color:#fffbeb;text-align:center;">
                        <p style="margin:0;font-size:32px;color:#92400e;font-weight:700;">{{days_remaining}}</p>
                        <p style="margin:4px 0 0;font-size:14px;color:#92400e;text-transform:uppercase;letter-spacing:0.1em;">Days Remaining</p>
                    </div>'
            );

        case 'checklist_item_overdue':
            return array(
                'title' => '⚠️ OVERDUE: Immediate Attention Required',
                'intro' => 'URGENT: The requirement "{{requirement_name}}" was due on {{due_date}} and is now {{days_overdue}} day(s) overdue. Please complete this item immediately.',
                'detail_heading' => 'Overdue Requirement',
                'cta_label' => 'Complete Now',
                'header_color' => '#dc2626',
                'header_bg' => 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)',
                'extra_html' => '
                    <div style="margin:24px 0;padding:16px;border-radius:12px;background-color:#fef2f2;text-align:center;border:2px solid #fecaca;">
                        <p style="margin:0;font-size:32px;color:#991b1b;font-weight:700;">{{days_overdue}}</p>
                        <p style="margin:4px 0 0;font-size:14px;color:#991b1b;text-transform:uppercase;letter-spacing:0.1em;">Days Overdue</p>
                    </div>'
            );

        case 'proposal_checklist_completed':
            return array(
                'title' => '🎉 All Requirements Completed!',
                'intro' => 'Congratulations! All checklist items for the proposal "{{proposal_title}}" have been completed. The proposal is now ready for final review and submission.',
                'detail_heading' => 'Completion Summary',
                'cta_label' => 'Review Proposal',
                'header_color' => '#059669',
                'header_bg' => 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
                'extra_html' => '
                    <div style="margin:24px 0;padding:16px;border-radius:12px;background-color:#ecfdf5;text-align:center;">
                        <p style="margin:0;font-size:48px;">🎉</p>
                        <p style="margin:8px 0 0;font-size:16px;color:#065f46;font-weight:600;">All {{total_items}} requirements completed!</p>
                        <p style="margin:4px 0 0;font-size:14px;color:#065f46;">Completed by {{completion_date}}</p>
                    </div>'
            );

        default:
            return array(
                'title' => 'Proposal Checklist Update',
                'intro' => 'There is an update regarding a proposal checklist requirement.',
                'detail_heading' => 'Requirement Details',
                'cta_label' => 'View Details',
                'header_color' => '#2563eb',
                'header_bg' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                'extra_html' => ''
            );
    }
}
