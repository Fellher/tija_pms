-- ============================================================================
-- Create Leave Notification Events
-- ============================================================================
-- This script creates the required notification events for the leave module
-- Run this script in phpMyAdmin to set up notification events
-- ============================================================================

-- Step 1: Create Leave Module (if it doesn't exist)
INSERT INTO `tija_notification_modules`
(`moduleName`, `moduleSlug`, `moduleDescription`, `moduleIcon`, `isActive`, `sortOrder`, `DateAdded`, `Suspended`)
SELECT
    'Leave Management',
    'leave',
    'Leave application and approval notifications',
    'ri-calendar-line',
    'Y',
    10,
    NOW(),
    'N'
WHERE NOT EXISTS (
    SELECT 1 FROM `tija_notification_modules` WHERE `moduleSlug` = 'leave'
);

-- Step 2: Create In-App Channel (if it doesn't exist)
INSERT INTO `tija_notification_channels`
(`channelName`, `channelSlug`, `channelDescription`, `isActive`, `sortOrder`, `DateAdded`, `Suspended`)
SELECT
    'In-App Notification',
    'in_app',
    'In-application notifications',
    'Y',
    1,
    NOW(),
    'N'
WHERE NOT EXISTS (
    SELECT 1 FROM `tija_notification_channels` WHERE `channelSlug` = 'in_app'
);

-- Step 3: Get module and channel IDs (for reference)
SET @moduleID = (SELECT moduleID FROM tija_notification_modules WHERE moduleSlug = 'leave' LIMIT 1);
SET @channelID = (SELECT channelID FROM tija_notification_channels WHERE channelSlug = 'in_app' LIMIT 1);

-- Step 4: Create Leave Pending Approval Event
INSERT INTO `tija_notification_events`
(`eventSlug`, `eventName`, `eventDescription`, `moduleID`, `priorityLevel`, `isActive`, `defaultEnabled`, `sortOrder`, `DateAdded`, `Suspended`)
SELECT
    'leave_pending_approval',
    'Leave Pending Approval',
    'Notification sent to approvers when a leave application is pending their approval',
    @moduleID,
    'high',
    'Y',
    'Y',
    1,
    NOW(),
    'N'
WHERE NOT EXISTS (
    SELECT 1 FROM `tija_notification_events` WHERE `eventSlug` = 'leave_pending_approval'
);

-- Step 5: Create Leave Application Submitted Event
INSERT INTO `tija_notification_events`
(`eventSlug`, `eventName`, `eventDescription`, `moduleID`, `priorityLevel`, `isActive`, `defaultEnabled`, `sortOrder`, `DateAdded`, `Suspended`)
SELECT
    'leave_application_submitted',
    'Leave Application Submitted',
    'Notification sent to employee when their leave application is submitted',
    @moduleID,
    'medium',
    'Y',
    'Y',
    2,
    NOW(),
    'N'
WHERE NOT EXISTS (
    SELECT 1 FROM `tija_notification_events` WHERE `eventSlug` = 'leave_application_submitted'
);

-- Step 6: Create Leave Approved Event
INSERT INTO `tija_notification_events`
(`eventSlug`, `eventName`, `eventDescription`, `moduleID`, `priorityLevel`, `isActive`, `defaultEnabled`, `sortOrder`, `DateAdded`, `Suspended`)
SELECT
    'leave_approved',
    'Leave Approved',
    'Notification sent to employee when their leave application is approved',
    @moduleID,
    'medium',
    'Y',
    'Y',
    3,
    NOW(),
    'N'
WHERE NOT EXISTS (
    SELECT 1 FROM `tija_notification_events` WHERE `eventSlug` = 'leave_approved'
);

-- Step 7: Create Leave Rejected Event
INSERT INTO `tija_notification_events`
(`eventSlug`, `eventName`, `eventDescription`, `moduleID`, `priorityLevel`, `isActive`, `defaultEnabled`, `sortOrder`, `DateAdded`, `Suspended`)
SELECT
    'leave_rejected',
    'Leave Rejected',
    'Notification sent to employee when their leave application is rejected',
    @moduleID,
    'high',
    'Y',
    'Y',
    4,
    NOW(),
    'N'
WHERE NOT EXISTS (
    SELECT 1 FROM `tija_notification_events` WHERE `eventSlug` = 'leave_rejected'
);

-- Step 8: Create Templates for Leave Pending Approval Event
INSERT INTO `tija_notification_templates`
(`eventID`, `channelID`, `templateSubject`, `templateBody`, `isActive`, `isDefault`, `isSystem`, `DateAdded`, `Suspended`)
SELECT
    (SELECT eventID FROM tija_notification_events WHERE eventSlug = 'leave_pending_approval' LIMIT 1),
    @channelID,
    'Leave Application Pending Approval - {{employee_name}}',
    '{{employee_name}} has submitted a leave application for {{leave_type}} from {{start_date}} to {{end_date}} ({{total_days}} day(s)). Please review and approve.',
    'Y',
    'Y',
    'Y',
    NOW(),
    'N'
WHERE NOT EXISTS (
    SELECT 1 FROM `tija_notification_templates`
    WHERE `eventID` = (SELECT eventID FROM tija_notification_events WHERE eventSlug = 'leave_pending_approval' LIMIT 1)
    AND `channelID` = @channelID
);

-- Step 9: Create Templates for Leave Application Submitted Event
INSERT INTO `tija_notification_templates`
(`eventID`, `channelID`, `templateSubject`, `templateBody`, `isActive`, `isDefault`, `isSystem`, `DateAdded`, `Suspended`)
SELECT
    (SELECT eventID FROM tija_notification_events WHERE eventSlug = 'leave_application_submitted' LIMIT 1),
    @channelID,
    'Leave Application Submitted',
    'Your leave application for {{leave_type}} from {{start_date}} to {{end_date}} ({{total_days}} day(s)) has been submitted successfully and is pending approval.',
    'Y',
    'Y',
    'Y',
    NOW(),
    'N'
WHERE NOT EXISTS (
    SELECT 1 FROM `tija_notification_templates`
    WHERE `eventID` = (SELECT eventID FROM tija_notification_events WHERE eventSlug = 'leave_application_submitted' LIMIT 1)
    AND `channelID` = @channelID
);

-- Step 10: Create Templates for Leave Approved Event
INSERT INTO `tija_notification_templates`
(`eventID`, `channelID`, `templateSubject`, `templateBody`, `isActive`, `isDefault`, `isSystem`, `DateAdded`, `Suspended`)
SELECT
    (SELECT eventID FROM tija_notification_events WHERE eventSlug = 'leave_approved' LIMIT 1),
    @channelID,
    'Leave Application Approved',
    'Your leave application for {{leave_type}} from {{start_date}} to {{end_date}} ({{total_days}} day(s)) has been approved.',
    'Y',
    'Y',
    'Y',
    NOW(),
    'N'
WHERE NOT EXISTS (
    SELECT 1 FROM `tija_notification_templates`
    WHERE `eventID` = (SELECT eventID FROM tija_notification_events WHERE eventSlug = 'leave_approved' LIMIT 1)
    AND `channelID` = @channelID
);

-- Step 11: Create Templates for Leave Rejected Event
INSERT INTO `tija_notification_templates`
(`eventID`, `channelID`, `templateSubject`, `templateBody`, `isActive`, `isDefault`, `isSystem`, `DateAdded`, `Suspended`)
SELECT
    (SELECT eventID FROM tija_notification_events WHERE eventSlug = 'leave_rejected' LIMIT 1),
    @channelID,
    'Leave Application Rejected',
    'Your leave application for {{leave_type}} from {{start_date}} to {{end_date}} ({{total_days}} day(s)) has been rejected.',
    'Y',
    'Y',
    'Y',
    NOW(),
    'N'
WHERE NOT EXISTS (
    SELECT 1 FROM `tija_notification_templates`
    WHERE `eventID` = (SELECT eventID FROM tija_notification_events WHERE eventSlug = 'leave_rejected' LIMIT 1)
    AND `channelID` = @channelID
);

-- Verification Query
SELECT
    e.eventSlug,
    e.eventName,
    e.isActive,
    m.moduleName,
    COUNT(t.templateID) as templateCount
FROM tija_notification_events e
LEFT JOIN tija_notification_modules m ON e.moduleID = m.moduleID
LEFT JOIN tija_notification_templates t ON e.eventID = t.eventID
WHERE e.eventSlug IN ('leave_pending_approval', 'leave_application_submitted', 'leave_approved', 'leave_rejected')
GROUP BY e.eventID, e.eventSlug, e.eventName, e.isActive, m.moduleName;



