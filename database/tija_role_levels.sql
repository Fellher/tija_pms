-- Organizational Role Levels Management Table
-- Allows root admins to manage organizational role levels dynamically instead of hardcoded values

CREATE TABLE IF NOT EXISTS `tija_role_levels` (
  `roleLevelID` INT(11) NOT NULL AUTO_INCREMENT,
  `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `levelNumber` INT(11) NOT NULL COMMENT 'Numeric level (0-8, lower = higher authority)',
  `levelName` VARCHAR(100) NOT NULL COMMENT 'Display name (e.g., Board/External, CEO/Executive)',
  `levelCode` VARCHAR(20) DEFAULT NULL COMMENT 'Short code (e.g., BOARD, CEO, CSUITE)',
  `levelDescription` TEXT DEFAULT NULL COMMENT 'Description of the role level',
  `displayOrder` INT(11) DEFAULT 0 COMMENT 'Order for display in dropdowns',
  `isDefault` ENUM('Y','N') DEFAULT 'N' COMMENT 'Is this a default/system role level',
  `isActive` ENUM('Y','N') DEFAULT 'Y' COMMENT 'Is this role level active',
  `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` INT(11) DEFAULT NULL,
  `Lapsed` ENUM('Y','N') DEFAULT 'N',
  `Suspended` ENUM('Y','N') DEFAULT 'N',
  PRIMARY KEY (`roleLevelID`),
  UNIQUE KEY `unique_levelNumber` (`levelNumber`),
  UNIQUE KEY `unique_levelCode` (`levelCode`),
  KEY `idx_isActive` (`isActive`),
  KEY `idx_Suspended` (`Suspended`),
  KEY `idx_displayOrder` (`displayOrder`),
  KEY `idx_levelNumber` (`levelNumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Role levels for organizational hierarchy';

-- Insert default role levels
INSERT INTO `tija_role_levels` (`levelNumber`, `levelName`, `levelCode`, `levelDescription`, `displayOrder`, `isDefault`, `isActive`) VALUES
(0, 'Board/External', 'BOARD', 'Board Members, External Auditors', 0, 'Y', 'Y'),
(1, 'CEO/Executive', 'CEO', 'Chief Executive Officer', 1, 'Y', 'Y'),
(2, 'C-Suite', 'CSUITE', 'CFO, COO, CTO, CMO', 2, 'Y', 'Y'),
(3, 'Director', 'DIR', 'Director of Finance, IT Director', 3, 'Y', 'Y'),
(4, 'Manager', 'MGR', 'Department Manager, Project Manager', 4, 'Y', 'Y'),
(5, 'Supervisor', 'SUPV', 'Team Lead, Supervisor (Default)', 5, 'Y', 'Y'),
(6, 'Senior Staff', 'SRSTAFF', 'Senior Officer, Senior Consultant', 6, 'Y', 'Y'),
(7, 'Staff', 'STAFF', 'Officer, Consultant, Staff', 7, 'Y', 'Y'),
(8, 'Entry Level', 'ENTRY', 'Junior Officer, Trainee', 8, 'Y', 'Y');

