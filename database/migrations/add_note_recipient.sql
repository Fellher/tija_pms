-- Migration: Add recipient support for private notes
-- Date: 2025-12-11
-- Description: Adds recipientID field to support private notes directed to specific team members

ALTER TABLE tija_prospect_notes
ADD COLUMN recipientID INT NULL AFTER isPrivate,
ADD INDEX idx_recipient (recipientID),
ADD FOREIGN KEY (recipientID) REFERENCES people(ID);
