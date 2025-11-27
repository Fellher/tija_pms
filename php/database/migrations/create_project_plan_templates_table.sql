-- ============================================================================
-- PROJECT PLAN TEMPLATES TABLE
-- ============================================================================
--
-- This table stores reusable project plan templates that can be applied
-- to new projects. Templates are organization-wide and can be shared or
-- kept private by individual users.
--
-- Created: November 4, 2025
-- Version: 1.0.0
-- ============================================================================

-- ============================================================================
-- DROP EXISTING TABLES (Clean Installation)
-- ============================================================================

-- Drop tables in reverse order due to foreign key constraints
DROP TABLE IF EXISTS `tija_project_plan_template_phases`;
DROP TABLE IF EXISTS `tija_project_plan_templates`;

-- ============================================================================
-- CREATE TABLES
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tija_project_plan_templates` (
    `templateID` INT(11) NOT NULL AUTO_INCREMENT,
    `templateName` VARCHAR(200) NOT NULL,
    `templateDescription` TEXT NULL,
    `templateCategory` VARCHAR(100) NULL COMMENT 'e.g., software, construction, marketing',
    `isPublic` ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Y=Organization-wide, N=Personal',
    `isSystemTemplate` ENUM('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Y=Built-in, cannot be deleted',
    `createdByID` INT(11) NOT NULL,
    `orgDataID` INT(11) NOT NULL,
    `entityID` INT(11) NULL,
    `usageCount` INT(11) DEFAULT 0 COMMENT 'Track how many times template is used',
    `lastUsedDate` DATETIME NULL,
    `isActive` ENUM('Y','N') NOT NULL DEFAULT 'Y',
    `DateAdded` DATETIME NOT NULL,
    `LastUpdate` DATETIME NULL,
    `LastUpdateByID` INT(11) NULL,
    PRIMARY KEY (`templateID`),
    KEY `idx_org_entity` (`orgDataID`, `entityID`),
    KEY `idx_creator` (`createdByID`),
    KEY `idx_public` (`isPublic`, `isActive`),
    KEY `idx_category` (`templateCategory`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Stores reusable project plan templates';

-- ============================================================================
-- PROJECT PLAN TEMPLATE PHASES TABLE
-- ============================================================================
--
-- Stores the individual phases for each template
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tija_project_plan_template_phases` (
    `templatePhaseID` INT(11) NOT NULL AUTO_INCREMENT,
    `templateID` INT(11) NOT NULL,
    `phaseName` VARCHAR(200) NOT NULL,
    `phaseDescription` TEXT NULL,
    `phaseOrder` INT(11) NOT NULL DEFAULT 0,
    `phaseColor` VARCHAR(20) NULL COMMENT 'Hex color code for visual representation',
    `estimatedDuration` INT(11) NULL COMMENT 'Estimated duration in days',
    `durationPercent` DECIMAL(5,2) NULL COMMENT 'Percentage of total project duration',
    `DateAdded` DATETIME NOT NULL,
    `LastUpdate` DATETIME NULL,
    PRIMARY KEY (`templatePhaseID`),
    KEY `idx_template` (`templateID`),
    KEY `idx_order` (`templateID`, `phaseOrder`),
    CONSTRAINT `fk_template_phases_template`
        FOREIGN KEY (`templateID`)
        REFERENCES `tija_project_plan_templates` (`templateID`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Stores phases for project plan templates';

-- ============================================================================
-- INSERT DEFAULT SYSTEM TEMPLATES
-- ============================================================================

-- Note: You'll need to update the orgDataID and createdByID values
-- based on your system's admin organization and user

INSERT INTO `tija_project_plan_templates`
(`templateName`, `templateDescription`, `templateCategory`, `isPublic`, `isSystemTemplate`, `createdByID`, `orgDataID`, `isActive`, `DateAdded`)
VALUES
('Standard Software Project', 'A general-purpose template for software development projects', 'software', 'Y', 'Y', 1, 1, 'Y', NOW()),
('Agile Sprint', 'Template for agile/scrum sprint-based projects', 'software', 'Y', 'Y', 1, 1, 'Y', NOW()),
('Waterfall Project', 'Traditional waterfall methodology project template', 'software', 'Y', 'Y', 1, 1, 'Y', NOW()),
('Research Project', 'Academic or business research project template', 'research', 'Y', 'Y', 1, 1, 'Y', NOW()),
('Construction Project', 'Building and construction project template', 'construction', 'Y', 'Y', 1, 1, 'Y', NOW()),
('Marketing Campaign', 'Marketing campaign project template', 'marketing', 'Y', 'Y', 1, 1, 'Y', NOW());

-- Get the template IDs for inserting phases
SET @standard_id = (SELECT templateID FROM tija_project_plan_templates WHERE templateName = 'Standard Software Project' LIMIT 1);
SET @agile_id = (SELECT templateID FROM tija_project_plan_templates WHERE templateName = 'Agile Sprint' LIMIT 1);
SET @waterfall_id = (SELECT templateID FROM tija_project_plan_templates WHERE templateName = 'Waterfall Project' LIMIT 1);
SET @research_id = (SELECT templateID FROM tija_project_plan_templates WHERE templateName = 'Research Project' LIMIT 1);
SET @construction_id = (SELECT templateID FROM tija_project_plan_templates WHERE templateName = 'Construction Project' LIMIT 1);
SET @marketing_id = (SELECT templateID FROM tija_project_plan_templates WHERE templateName = 'Marketing Campaign' LIMIT 1);

-- Standard Software Project Phases
INSERT INTO `tija_project_plan_template_phases`
(`templateID`, `phaseName`, `phaseDescription`, `phaseOrder`, `durationPercent`, `DateAdded`)
VALUES
(@standard_id, 'Planning', 'Project planning and resource allocation', 1, 20.00, NOW()),
(@standard_id, 'Development', 'Core development and implementation', 2, 50.00, NOW()),
(@standard_id, 'Testing', 'Quality assurance and testing', 3, 20.00, NOW()),
(@standard_id, 'Deployment', 'Launch and deployment to production', 4, 10.00, NOW());

-- Agile Sprint Phases
INSERT INTO `tija_project_plan_template_phases`
(`templateID`, `phaseName`, `phaseDescription`, `phaseOrder`, `durationPercent`, `DateAdded`)
VALUES
(@agile_id, 'Sprint Planning', 'Sprint goal and backlog refinement', 1, 10.00, NOW()),
(@agile_id, 'Development', 'Sprint development and daily standups', 2, 70.00, NOW()),
(@agile_id, 'Review', 'Sprint review with stakeholders', 3, 10.00, NOW()),
(@agile_id, 'Retrospective', 'Team retrospective and improvements', 4, 10.00, NOW());

-- Waterfall Project Phases
INSERT INTO `tija_project_plan_template_phases`
(`templateID`, `phaseName`, `phaseDescription`, `phaseOrder`, `durationPercent`, `DateAdded`)
VALUES
(@waterfall_id, 'Requirements', 'Requirements gathering and analysis', 1, 15.00, NOW()),
(@waterfall_id, 'Design', 'System and UI/UX design', 2, 20.00, NOW()),
(@waterfall_id, 'Implementation', 'Development and coding', 3, 40.00, NOW()),
(@waterfall_id, 'Testing', 'System testing and QA', 4, 15.00, NOW()),
(@waterfall_id, 'Maintenance', 'Deployment and ongoing maintenance', 5, 10.00, NOW());

-- Research Project Phases
INSERT INTO `tija_project_plan_template_phases`
(`templateID`, `phaseName`, `phaseDescription`, `phaseOrder`, `durationPercent`, `DateAdded`)
VALUES
(@research_id, 'Literature Review', 'Research existing materials and studies', 1, 25.00, NOW()),
(@research_id, 'Data Collection', 'Gather data and conduct experiments', 2, 35.00, NOW()),
(@research_id, 'Analysis', 'Analyze results and findings', 3, 25.00, NOW()),
(@research_id, 'Reporting', 'Document and present findings', 4, 15.00, NOW());

-- Construction Project Phases
INSERT INTO `tija_project_plan_template_phases`
(`templateID`, `phaseName`, `phaseDescription`, `phaseOrder`, `durationPercent`, `DateAdded`)
VALUES
(@construction_id, 'Planning & Permits', 'Project planning and permit acquisition', 1, 15.00, NOW()),
(@construction_id, 'Foundation', 'Site preparation and foundation work', 2, 20.00, NOW()),
(@construction_id, 'Structure', 'Main structure construction', 3, 35.00, NOW()),
(@construction_id, 'Finishing', 'Interior and exterior finishing', 4, 20.00, NOW()),
(@construction_id, 'Handover', 'Final inspection and client handover', 5, 10.00, NOW());

-- Marketing Campaign Phases
INSERT INTO `tija_project_plan_template_phases`
(`templateID`, `phaseName`, `phaseDescription`, `phaseOrder`, `durationPercent`, `DateAdded`)
VALUES
(@marketing_id, 'Market Research', 'Research target audience and competitors', 1, 20.00, NOW()),
(@marketing_id, 'Strategy Development', 'Develop campaign strategy and messaging', 2, 20.00, NOW()),
(@marketing_id, 'Content Creation', 'Create campaign materials and content', 3, 30.00, NOW()),
(@marketing_id, 'Campaign Launch', 'Execute and launch campaign', 4, 20.00, NOW()),
(@marketing_id, 'Analysis & Reporting', 'Measure results and optimize', 5, 10.00, NOW());

-- ============================================================================
-- INDEXES FOR PERFORMANCE
-- ============================================================================

-- Create additional indexes for common queries
CREATE INDEX idx_template_search ON tija_project_plan_templates (templateName, orgDataID, isActive);
CREATE INDEX idx_template_usage ON tija_project_plan_templates (usageCount DESC, lastUsedDate DESC);

-- ============================================================================
-- COMMENTS AND DOCUMENTATION
-- ============================================================================

-- Add table comments for better documentation
ALTER TABLE `tija_project_plan_templates`
    COMMENT = 'Stores reusable project plan templates for organization-wide use';

ALTER TABLE `tija_project_plan_template_phases`
    COMMENT = 'Stores individual phases for each project plan template with duration percentages';

-- ============================================================================
-- SUCCESS MESSAGE
-- ============================================================================

SELECT 'Project Plan Templates tables created successfully!' AS Message;
SELECT COUNT(*) AS 'System Templates Created' FROM tija_project_plan_templates WHERE isSystemTemplate = 'Y';
SELECT COUNT(*) AS 'Total Template Phases Created' FROM tija_project_plan_template_phases;

