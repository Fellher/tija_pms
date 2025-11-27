-- Organizational Role Types Management Table
-- Allows root admins to manage organizational role types dynamically instead of hardcoded ENUM values
-- Note: This is different from tija_role_types which is used for permission role profiles

CREATE TABLE IF NOT EXISTS `tija_org_role_types` (
  `roleTypeID` INT(11) NOT NULL AUTO_INCREMENT,
  `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `roleTypeName` VARCHAR(100) NOT NULL COMMENT 'Display name (e.g., Executive, Management)',
  `roleTypeCode` VARCHAR(20) NOT NULL COMMENT 'Short code (e.g., EXEC, MGT)',
  `roleTypeDescription` TEXT DEFAULT NULL COMMENT 'Description of the role type',
  `displayOrder` INT(11) DEFAULT 0 COMMENT 'Order for display in dropdowns',
  `colorCode` VARCHAR(7) DEFAULT '#667eea' COMMENT 'Hex color code for badges',
  `iconClass` VARCHAR(50) DEFAULT 'fa-user-tie' COMMENT 'FontAwesome icon class',
  `isDefault` ENUM('Y','N') DEFAULT 'N' COMMENT 'Is this a default/system role type',
  `isActive` ENUM('Y','N') DEFAULT 'Y' COMMENT 'Is this role type active',
  `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` INT(11) DEFAULT NULL,
  `Lapsed` ENUM('Y','N') DEFAULT 'N',
  `Suspended` ENUM('Y','N') DEFAULT 'N',
  PRIMARY KEY (`roleTypeID`),
  UNIQUE KEY `unique_roleTypeCode` (`roleTypeCode`),
  KEY `idx_isActive` (`isActive`),
  KEY `idx_Suspended` (`Suspended`),
  KEY `idx_displayOrder` (`displayOrder`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Role types for organizational roles';

-- Insert default role types
INSERT INTO `tija_org_role_types` (`roleTypeName`, `roleTypeCode`, `roleTypeDescription`, `displayOrder`, `colorCode`, `iconClass`, `isDefault`, `isActive`) VALUES
('Executive', 'EXEC', 'C-Level, Top Leadership', 1, '#dc3545', 'fa-crown', 'Y', 'Y'),
('Management', 'MGT', 'Directors, Managers', 2, '#ffc107', 'fa-user-tie', 'Y', 'Y'),
('Supervisory', 'SUPV', 'Team Leads, Supervisors', 3, '#17a2b8', 'fa-user-shield', 'Y', 'Y'),
('Operational', 'OPR', 'Officers, Staff (Default)', 4, '#28a745', 'fa-user', 'Y', 'Y'),
('Support', 'SUPP', 'Administrative, Assistants', 5, '#6c757d', 'fa-user-cog', 'Y', 'Y');

