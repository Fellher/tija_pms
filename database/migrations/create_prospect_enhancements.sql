-- ============================================================================
-- Prospect Management Enhancement Migration
-- Created: 2025-12-11
-- Description: Adds enhanced prospecting capabilities including lead scoring,
--              team management, territory tracking, and interaction history
-- ============================================================================

-- ============================================================================
-- 1. Extend tija_sales_prospects table with new columns
-- ============================================================================

ALTER TABLE `tija_sales_prospects`
ADD COLUMN `leadScore` INT DEFAULT 0 COMMENT 'Calculated lead score (0-100)' AFTER `salesProspectStatus`,
ADD COLUMN `leadQualificationStatus` ENUM('unqualified','qualified','hot','warm','cold') DEFAULT 'unqualified' COMMENT 'Lead qualification level' AFTER `leadScore`,
ADD COLUMN `assignedTeamID` INT DEFAULT NULL COMMENT 'FK to tija_prospect_teams' AFTER `leadQualificationStatus`,
ADD COLUMN `territoryID` INT DEFAULT NULL COMMENT 'FK to tija_prospect_territories' AFTER `assignedTeamID`,
ADD COLUMN `industryID` INT DEFAULT NULL COMMENT 'FK to tija_prospect_industries' AFTER `territoryID`,
ADD COLUMN `companySize` ENUM('small','medium','large','enterprise') DEFAULT NULL COMMENT 'Company size classification' AFTER `industryID`,
ADD COLUMN `expectedCloseDate` DATE DEFAULT NULL COMMENT 'Projected conversion date' AFTER `companySize`,
ADD COLUMN `lastContactDate` DATE DEFAULT NULL COMMENT 'Last interaction timestamp' AFTER `expectedCloseDate`,
ADD COLUMN `nextFollowUpDate` DATE DEFAULT NULL COMMENT 'Scheduled next contact' AFTER `lastContactDate`,
ADD COLUMN `sourceDetails` TEXT DEFAULT NULL COMMENT 'Additional lead source information' AFTER `nextFollowUpDate`,
ADD COLUMN `tags` JSON DEFAULT NULL COMMENT 'Flexible tagging system' AFTER `sourceDetails`,
ADD COLUMN `prospectPhone` VARCHAR(20) DEFAULT NULL COMMENT 'Primary phone number' AFTER `prospectEmail`,
ADD COLUMN `prospectWebsite` VARCHAR(255) DEFAULT NULL COMMENT 'Company website' AFTER `prospectPhone`,
ADD COLUMN `budgetConfirmed` ENUM('Y','N') DEFAULT 'N' COMMENT 'Budget confirmed flag' AFTER `probability`,
ADD COLUMN `decisionMakerIdentified` ENUM('Y','N') DEFAULT 'N' COMMENT 'Decision maker identified flag' AFTER `budgetConfirmed`,
ADD COLUMN `timelineDefined` ENUM('Y','N') DEFAULT 'N' COMMENT 'Timeline defined flag' AFTER `decisionMakerIdentified`,
ADD COLUMN `needIdentified` ENUM('Y','N') DEFAULT 'N' COMMENT 'Need identified flag' AFTER `timelineDefined`;

-- Add indexes for performance
ALTER TABLE `tija_sales_prospects`
ADD INDEX `idx_lead_score` (`leadScore`),
ADD INDEX `idx_qualification_status` (`leadQualificationStatus`),
ADD INDEX `idx_assigned_team` (`assignedTeamID`),
ADD INDEX `idx_territory` (`territoryID`),
ADD INDEX `idx_industry` (`industryID`),
ADD INDEX `idx_expected_close` (`expectedCloseDate`),
ADD INDEX `idx_last_contact` (`lastContactDate`),
ADD INDEX `idx_next_followup` (`nextFollowUpDate`),
ADD INDEX `idx_org_entity_status` (`orgDataID`, `entityID`, `salesProspectStatus`);

-- ============================================================================
-- 2. Create tija_prospect_teams table
-- ============================================================================

DROP TABLE IF EXISTS `tija_prospect_teams`;
CREATE TABLE IF NOT EXISTS `tija_prospect_teams` (
  `teamID` INT NOT NULL AUTO_INCREMENT,
  `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `teamName` VARCHAR(255) NOT NULL COMMENT 'Team name',
  `teamCode` VARCHAR(50) DEFAULT NULL COMMENT 'Short team code',
  `teamDescription` TEXT DEFAULT NULL COMMENT 'Team description',
  `teamManagerID` INT DEFAULT NULL COMMENT 'FK to tija_users - team manager',
  `orgDataID` INT NOT NULL COMMENT 'FK to tija_organisation_data',
  `entityID` INT NOT NULL COMMENT 'FK to tija_entities',
  `territoryID` INT DEFAULT NULL COMMENT 'Default territory for team',
  `isActive` ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Active status',
  `LastUpdate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` INT NOT NULL,
  `Lapsed` ENUM('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` ENUM('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`teamID`),
  INDEX `idx_org_entity` (`orgDataID`, `entityID`),
  INDEX `idx_manager` (`teamManagerID`),
  INDEX `idx_territory` (`territoryID`),
  INDEX `idx_active` (`isActive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sales teams for prospect management';

-- ============================================================================
-- 3. Create tija_prospect_team_members table
-- ============================================================================

DROP TABLE IF EXISTS `tija_prospect_team_members`;
CREATE TABLE IF NOT EXISTS `tija_prospect_team_members` (
  `teamMemberID` INT NOT NULL AUTO_INCREMENT,
  `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `teamID` INT NOT NULL COMMENT 'FK to tija_prospect_teams',
  `userID` INT NOT NULL COMMENT 'FK to tija_users',
  `roleInTeam` VARCHAR(100) DEFAULT NULL COMMENT 'Role within team',
  `isTeamLead` ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Team lead flag',
  `joinDate` DATE NOT NULL COMMENT 'Date joined team',
  `leaveDate` DATE DEFAULT NULL COMMENT 'Date left team',
  `isActive` ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Active member status',
  `LastUpdate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` INT NOT NULL,
  `Lapsed` ENUM('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` ENUM('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`teamMemberID`),
  UNIQUE KEY `unique_team_user` (`teamID`, `userID`, `isActive`),
  INDEX `idx_team` (`teamID`),
  INDEX `idx_user` (`userID`),
  INDEX `idx_active` (`isActive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Team membership tracking';

-- ============================================================================
-- 4. Create tija_prospect_territories table
-- ============================================================================

DROP TABLE IF EXISTS `tija_prospect_territories`;
CREATE TABLE IF NOT EXISTS `tija_prospect_territories` (
  `territoryID` INT NOT NULL AUTO_INCREMENT,
  `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `territoryName` VARCHAR(255) NOT NULL COMMENT 'Territory name',
  `territoryCode` VARCHAR(50) DEFAULT NULL COMMENT 'Short territory code',
  `territoryDescription` TEXT DEFAULT NULL COMMENT 'Territory description',
  `territoryType` ENUM('geographic','industry','account_based','hybrid') NOT NULL DEFAULT 'geographic' COMMENT 'Territory type',
  `parentTerritoryID` INT DEFAULT NULL COMMENT 'Parent territory for hierarchy',
  `orgDataID` INT NOT NULL COMMENT 'FK to tija_organisation_data',
  `entityID` INT NOT NULL COMMENT 'FK to tija_entities',
  `countryCode` VARCHAR(3) DEFAULT NULL COMMENT 'ISO country code',
  `regionName` VARCHAR(100) DEFAULT NULL COMMENT 'Region/state name',
  `cityName` VARCHAR(100) DEFAULT NULL COMMENT 'City name',
  `isActive` ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Active status',
  `LastUpdate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` INT NOT NULL,
  `Lapsed` ENUM('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` ENUM('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`territoryID`),
  INDEX `idx_org_entity` (`orgDataID`, `entityID`),
  INDEX `idx_parent` (`parentTerritoryID`),
  INDEX `idx_type` (`territoryType`),
  INDEX `idx_country` (`countryCode`),
  INDEX `idx_active` (`isActive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Territory definitions for prospect management';

-- ============================================================================
-- 5. Create tija_prospect_industries table
-- ============================================================================

DROP TABLE IF EXISTS `tija_prospect_industries`;
CREATE TABLE IF NOT EXISTS `tija_prospect_industries` (
  `industryID` INT NOT NULL AUTO_INCREMENT,
  `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `industryName` VARCHAR(255) NOT NULL COMMENT 'Industry name',
  `industryCode` VARCHAR(50) DEFAULT NULL COMMENT 'Industry code (NAICS/SIC)',
  `industryDescription` TEXT DEFAULT NULL COMMENT 'Industry description',
  `parentIndustryID` INT DEFAULT NULL COMMENT 'Parent industry for hierarchy',
  `industryLevel` INT DEFAULT 1 COMMENT 'Hierarchy level',
  `isActive` ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Active status',
  `LastUpdate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` INT NOT NULL,
  `Lapsed` ENUM('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` ENUM('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`industryID`),
  INDEX `idx_parent` (`parentIndustryID`),
  INDEX `idx_code` (`industryCode`),
  INDEX `idx_active` (`isActive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Industry classifications';

-- ============================================================================
-- 6. Create tija_prospect_interactions table
-- ============================================================================

DROP TABLE IF EXISTS `tija_prospect_interactions`;
CREATE TABLE IF NOT EXISTS `tija_prospect_interactions` (
  `interactionID` INT NOT NULL AUTO_INCREMENT,
  `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `salesProspectID` INT NOT NULL COMMENT 'FK to tija_sales_prospects',
  `interactionType` ENUM('call','email','meeting','note','task','other') NOT NULL COMMENT 'Interaction type',
  `interactionDate` DATETIME NOT NULL COMMENT 'When interaction occurred',
  `interactionSubject` VARCHAR(255) DEFAULT NULL COMMENT 'Subject/title',
  `interactionDescription` TEXT DEFAULT NULL COMMENT 'Detailed description',
  `interactionOutcome` ENUM('positive','neutral','negative','no_response') DEFAULT NULL COMMENT 'Outcome',
  `nextSteps` TEXT DEFAULT NULL COMMENT 'Agreed next steps',
  `userID` INT NOT NULL COMMENT 'FK to tija_users - who logged interaction',
  `duration` INT DEFAULT NULL COMMENT 'Duration in minutes',
  `attachmentURL` VARCHAR(500) DEFAULT NULL COMMENT 'Attached file URL',
  `relatedActivityID` INT DEFAULT NULL COMMENT 'FK to tija_activities',
  `LastUpdate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` INT NOT NULL,
  `Lapsed` ENUM('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` ENUM('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`interactionID`),
  INDEX `idx_prospect` (`salesProspectID`),
  INDEX `idx_type` (`interactionType`),
  INDEX `idx_date` (`interactionDate`),
  INDEX `idx_user` (`userID`),
  INDEX `idx_outcome` (`interactionOutcome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Prospect interaction history';

-- ============================================================================
-- 7. Create tija_lead_scoring_rules table
-- ============================================================================

DROP TABLE IF EXISTS `tija_lead_scoring_rules`;
CREATE TABLE IF NOT EXISTS `tija_lead_scoring_rules` (
  `ruleID` INT NOT NULL AUTO_INCREMENT,
  `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ruleName` VARCHAR(255) NOT NULL COMMENT 'Rule name',
  `ruleDescription` TEXT DEFAULT NULL COMMENT 'Rule description',
  `ruleCategory` ENUM('value','source','engagement','timeline','qualification','demographic') NOT NULL COMMENT 'Rule category',
  `ruleField` VARCHAR(100) NOT NULL COMMENT 'Field to evaluate',
  `ruleCondition` VARCHAR(50) NOT NULL COMMENT 'Condition (equals, greater_than, etc)',
  `ruleValue` VARCHAR(255) DEFAULT NULL COMMENT 'Value to compare against',
  `scorePoints` INT NOT NULL DEFAULT 0 COMMENT 'Points to add/subtract',
  `scoreWeight` DECIMAL(5,2) NOT NULL DEFAULT 1.00 COMMENT 'Weight multiplier (0.00-1.00)',
  `orgDataID` INT NOT NULL COMMENT 'FK to tija_organisation_data',
  `entityID` INT NOT NULL COMMENT 'FK to tija_entities',
  `isActive` ENUM('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Active status',
  `LastUpdate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` INT NOT NULL,
  `Lapsed` ENUM('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` ENUM('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ruleID`),
  INDEX `idx_org_entity` (`orgDataID`, `entityID`),
  INDEX `idx_category` (`ruleCategory`),
  INDEX `idx_active` (`isActive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lead scoring rule definitions';

-- ============================================================================
-- 8. Create tija_prospect_status_history table
-- ============================================================================

DROP TABLE IF EXISTS `tija_prospect_status_history`;
CREATE TABLE IF NOT EXISTS `tija_prospect_status_history` (
  `historyID` INT NOT NULL AUTO_INCREMENT,
  `DateAdded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `salesProspectID` INT NOT NULL COMMENT 'FK to tija_sales_prospects',
  `oldStatus` VARCHAR(50) DEFAULT NULL COMMENT 'Previous status',
  `newStatus` VARCHAR(50) NOT NULL COMMENT 'New status',
  `oldQualification` VARCHAR(50) DEFAULT NULL COMMENT 'Previous qualification',
  `newQualification` VARCHAR(50) DEFAULT NULL COMMENT 'New qualification',
  `oldLeadScore` INT DEFAULT NULL COMMENT 'Previous lead score',
  `newLeadScore` INT DEFAULT NULL COMMENT 'New lead score',
  `changeReason` TEXT DEFAULT NULL COMMENT 'Reason for change',
  `changedByID` INT NOT NULL COMMENT 'FK to tija_users',
  PRIMARY KEY (`historyID`),
  INDEX `idx_prospect` (`salesProspectID`),
  INDEX `idx_date` (`DateAdded`),
  INDEX `idx_status` (`newStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Prospect status change audit trail';

-- ============================================================================
-- 9. Insert default data
-- ============================================================================

-- Default industries (common categories)
INSERT INTO `tija_prospect_industries`
(`industryName`, `industryCode`, `industryDescription`, `parentIndustryID`, `industryLevel`, `LastUpdatedByID`)
VALUES
('Technology', 'TECH', 'Technology and IT services', NULL, 1, 1),
('Financial Services', 'FIN', 'Banking, insurance, and financial services', NULL, 1, 1),
('Healthcare', 'HEALTH', 'Healthcare and medical services', NULL, 1, 1),
('Manufacturing', 'MFG', 'Manufacturing and production', NULL, 1, 1),
('Retail', 'RETAIL', 'Retail and e-commerce', NULL, 1, 1),
('Professional Services', 'PROF', 'Consulting and professional services', NULL, 1, 1),
('Real Estate', 'RE', 'Real estate and property management', NULL, 1, 1),
('Education', 'EDU', 'Education and training', NULL, 1, 1),
('Government', 'GOV', 'Government and public sector', NULL, 1, 1),
('Non-Profit', 'NPO', 'Non-profit organizations', NULL, 1, 1);

-- Default lead scoring rules (example rules)
INSERT INTO `tija_lead_scoring_rules`
(`ruleName`, `ruleDescription`, `ruleCategory`, `ruleField`, `ruleCondition`, `ruleValue`, `scorePoints`, `scoreWeight`, `orgDataID`, `entityID`, `LastUpdatedByID`)
VALUES
('High Value Prospect', 'Prospects with estimated value > 100000', 'value', 'estimatedValue', 'greater_than', '100000', 30, 0.30, 1, 1, 1),
('Medium Value Prospect', 'Prospects with estimated value 50000-100000', 'value', 'estimatedValue', 'between', '50000,100000', 20, 0.30, 1, 1, 1),
('Low Value Prospect', 'Prospects with estimated value < 50000', 'value', 'estimatedValue', 'less_than', '50000', 10, 0.30, 1, 1, 1),
('Premium Lead Source', 'Referral or customer recommendation', 'source', 'leadSourceID', 'in', '1', 25, 0.25, 1, 1, 1),
('Recent Contact', 'Contacted within last 7 days', 'engagement', 'lastContactDate', 'within_days', '7', 25, 0.25, 1, 1, 1),
('Budget Confirmed', 'Budget has been confirmed', 'qualification', 'budgetConfirmed', 'equals', 'Y', 20, 0.20, 1, 1, 1),
('Decision Maker Identified', 'Decision maker has been identified', 'qualification', 'decisionMakerIdentified', 'equals', 'Y', 20, 0.20, 1, 1, 1);

-- ============================================================================
-- 10. Create views for reporting
-- ============================================================================

-- View for prospect summary with all related data
CREATE OR REPLACE VIEW `vw_prospects_full` AS
SELECT
    p.salesProspectID,
    p.DateAdded,
    p.salesProspectName,
    p.isClient,
    p.clientID,
    p.prospectCaseName,
    p.address,
    p.prospectEmail,
    p.prospectPhone,
    p.prospectWebsite,
    p.estimatedValue,
    p.probability,
    p.salesProspectStatus,
    p.leadScore,
    p.leadQualificationStatus,
    p.companySize,
    p.expectedCloseDate,
    p.lastContactDate,
    p.nextFollowUpDate,
    p.budgetConfirmed,
    p.decisionMakerIdentified,
    p.timelineDefined,
    p.needIdentified,
    p.tags,
    p.orgDataID,
    p.entityID,
    c.clientName,
    c.clientCode,
    bu.businessUnitName,
    ls.leadSourceName,
    t.teamName,
    t.teamCode,
    ter.territoryName,
    ind.industryName,
    CONCAT(u.FirstName, ' ', u.Surname) AS ownerName,
    CONCAT(lup.FirstName, ' ', lup.Surname) AS lastUpdatedByName,
    DATEDIFF(CURRENT_DATE, p.DateAdded) AS daysInPipeline,
    CASE
        WHEN p.nextFollowUpDate < CURRENT_DATE THEN 'Overdue'
        WHEN p.nextFollowUpDate = CURRENT_DATE THEN 'Due Today'
        WHEN p.nextFollowUpDate IS NULL THEN 'Not Scheduled'
        ELSE 'Scheduled'
    END AS followUpStatus
FROM tija_sales_prospects p
LEFT JOIN tija_clients c ON p.clientID = c.clientID
LEFT JOIN tija_business_units bu ON p.businessUnitID = bu.businessUnitID
LEFT JOIN tija_lead_sources ls ON p.leadSourceID = ls.leadSourceID
LEFT JOIN tija_prospect_teams t ON p.assignedTeamID = t.teamID
LEFT JOIN tija_prospect_territories ter ON p.territoryID = ter.territoryID
LEFT JOIN tija_prospect_industries ind ON p.industryID = ind.industryID
LEFT JOIN people u ON p.ownerID = u.ID
LEFT JOIN people lup ON p.LastUpdateByID = lup.ID
WHERE p.Suspended = 'N' AND p.Lapsed = 'N';

-- ============================================================================
-- Migration Complete
-- ============================================================================
