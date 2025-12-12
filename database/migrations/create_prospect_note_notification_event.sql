-- Migration: Create notification event for prospect notes
-- Date: 2025-12-11
-- Description: Creates a notification event for when users receive prospect notes

-- First, check if a sales module exists, if not create it
INSERT IGNORE INTO tija_notification_modules (moduleID, moduleName, moduleSlug, moduleDescription, isActive, Suspended)
VALUES (100, 'Sales & Prospects', 'sales_prospects', 'Notifications related to sales prospects and opportunities', 'Y', 'N');

-- Create the notification event for prospect notes
INSERT INTO tija_notification_events
(eventID, moduleID, eventName, eventSlug, eventDescription, eventCategory, isUserConfigurable, isActive, defaultEnabled, priorityLevel, sortOrder, Suspended)
VALUES
(1000, 100, 'Prospect Note Received', 'prospect_note_received', 'Triggered when a user receives a private note on a prospect', 'prospect', 'Y', 'Y', 'Y', 'normal', 100, 'N');

-- Verify the insert
SELECT eventID, eventName, eventSlug FROM tija_notification_events WHERE eventSlug = 'prospect_note_received';
