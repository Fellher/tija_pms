-- Migration: Rename prospect team columns for clarity
-- Date: 2025-12-11
-- Description: Renames columns in tija_prospect_teams to use prospect-specific naming

-- Rename columns to prospect-specific names
ALTER TABLE tija_prospect_teams
CHANGE COLUMN teamName prospectTeamName VARCHAR(255),
CHANGE COLUMN teamCode prospectTeamCode VARCHAR(50),
CHANGE COLUMN teamManagerID prospectTeamManagerID INT;

-- Update any indexes if they exist
-- ALTER TABLE tija_prospect_teams DROP INDEX idx_teamName;
-- ALTER TABLE tija_prospect_teams ADD INDEX idx_prospectTeamName (prospectTeamName);
