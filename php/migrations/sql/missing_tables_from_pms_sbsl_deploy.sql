-- Tables from pms_sbsl_deploy (7).sql that are not in sbsl_pms (3).sql
-- Generated automatically

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS=0;

-- --------------------------------------------------------
-- Table: tija_activity_attachments
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_activity_attachments`;
CREATE TABLE IF NOT EXISTS `tija_activity_attachments` (
  `attachmentID` int NOT NULL AUTO_INCREMENT,
  `activityID` int NOT NULL,
  `fileName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `filePath` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fileType` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fileSize` int DEFAULT NULL COMMENT 'Size in bytes',
  `uploadedBy` int NOT NULL,
  `uploadedOn` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `description` text COLLATE utf8mb4_unicode_ci,
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`attachmentID`),
  KEY `idx_activity_attachments` (`activityID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table: tija_activity_comments
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_activity_comments`;
CREATE TABLE IF NOT EXISTS `tija_activity_comments` (
  `commentID` int NOT NULL AUTO_INCREMENT,
  `activityID` int NOT NULL,
  `commentText` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `commentBy` int NOT NULL,
  `commentOn` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `parentCommentID` int DEFAULT NULL COMMENT 'For threaded comments',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`commentID`),
  KEY `idx_activity_comments` (`activityID`),
  KEY `idx_comment_parent` (`parentCommentID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table: tija_activity_expenses
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_activity_expenses`;
CREATE TABLE IF NOT EXISTS `tija_activity_expenses` (
  `expenseID` int NOT NULL AUTO_INCREMENT,
  `activityID` int NOT NULL,
  `expenseDate` date NOT NULL,
  `expenseCategory` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expenseAmount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `expenseDescription` text COLLATE utf8mb4_unicode_ci,
  `expenseCurrency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'KES',
  `receiptNumber` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receiptAttached` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `receiptPath` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paymentMethod` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Cash, Card, Mpesa, etc.',
  `reimbursable` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y',
  `reimbursementStatus` enum('pending','approved','rejected','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `approvedBy` int DEFAULT NULL,
  `approvedOn` datetime DEFAULT NULL,
  `paidOn` datetime DEFAULT NULL,
  `addedBy` int NOT NULL,
  `addedOn` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int NOT NULL,
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`expenseID`),
  KEY `idx_activity_expenses` (`activityID`),
  KEY `idx_expense_date` (`expenseDate`),
  KEY `idx_expense_category` (`expenseCategory`),
  KEY `idx_reimbursement_status` (`reimbursementStatus`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table: tija_activity_history
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_activity_history`;
CREATE TABLE IF NOT EXISTS `tija_activity_history` (
  `historyID` int NOT NULL AUTO_INCREMENT,
  `activityID` int NOT NULL,
  `fieldChanged` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `oldValue` text COLLATE utf8mb4_unicode_ci,
  `newValue` text COLLATE utf8mb4_unicode_ci,
  `changedBy` int NOT NULL,
  `changedOn` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `changeNote` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`historyID`),
  KEY `idx_activity_history` (`activityID`),
  KEY `idx_changed_on` (`changedOn`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table: tija_activity_reminders
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_activity_reminders`;
CREATE TABLE IF NOT EXISTS `tija_activity_reminders` (
  `reminderID` int NOT NULL AUTO_INCREMENT,
  `activityID` int NOT NULL,
  `reminderTime` datetime NOT NULL,
  `reminderType` enum('email','sms','notification','all') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'notification',
  `recipientID` int NOT NULL,
  `reminderSent` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `sentOn` datetime DEFAULT NULL,
  `reminderNote` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`reminderID`),
  KEY `idx_activity_reminders` (`activityID`),
  KEY `idx_reminder_time` (`reminderTime`),
  KEY `idx_reminder_sent` (`reminderSent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table: tija_bau_activities
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_bau_activities`;
CREATE TABLE IF NOT EXISTS `tija_bau_activities` (
  `activityID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `processID` int UNSIGNED NOT NULL COMMENT 'FK to tija_bau_processes',
  `activityCode` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Optional activity code',
  `activityName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activityDescription` text COLLATE utf8mb4_unicode_ci,
  `estimatedDuration` decimal(10,2) DEFAULT NULL COMMENT 'Estimated hours',
  `displayOrder` int DEFAULT '0',
  `isActive` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`activityID`),
  KEY `idx_process` (`processID`),
  KEY `idx_isActive` (`isActive`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='APQC Activities - Actionable units of work';


-- --------------------------------------------------------
-- Table: tija_bau_categories
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_bau_categories`;
CREATE TABLE IF NOT EXISTS `tija_bau_categories` (
  `categoryID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `categoryCode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APQC code (e.g., 7.0)',
  `categoryName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoryDescription` text COLLATE utf8mb4_unicode_ci,
  `displayOrder` int DEFAULT '0',
  `isActive` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`categoryID`),
  UNIQUE KEY `unique_categoryCode` (`categoryCode`),
  KEY `idx_isActive` (`isActive`),
  KEY `idx_displayOrder` (`displayOrder`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='APQC Categories - Top-level domains';


-- --------------------------------------------------------
-- Table: tija_bau_process_groups
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_bau_process_groups`;
CREATE TABLE IF NOT EXISTS `tija_bau_process_groups` (
  `processGroupID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `categoryID` int UNSIGNED NOT NULL COMMENT 'FK to tija_bau_categories',
  `processGroupCode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APQC code (e.g., 7.3)',
  `processGroupName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `processGroupDescription` text COLLATE utf8mb4_unicode_ci,
  `displayOrder` int DEFAULT '0',
  `isActive` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`processGroupID`),
  UNIQUE KEY `unique_processGroupCode` (`processGroupCode`),
  KEY `idx_category` (`categoryID`),
  KEY `idx_isActive` (`isActive`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='APQC Process Groups - Functional areas within categories';


-- --------------------------------------------------------
-- Table: tija_bau_processes
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_bau_processes`;
CREATE TABLE IF NOT EXISTS `tija_bau_processes` (
  `processID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `processGroupID` int UNSIGNED NOT NULL COMMENT 'FK to tija_bau_process_groups',
  `categoryID` int UNSIGNED NOT NULL COMMENT 'FK to tija_bau_categories (denormalized from processGroup)',
  `processCode` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'APQC code (e.g., 7.3.1)',
  `processName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `processDescription` text COLLATE utf8mb4_unicode_ci,
  `functionalArea` enum('Finance','HR','IT','Sales','Marketing','Legal','Facilities','Custom') COLLATE utf8mb4_unicode_ci NOT NULL,
  `functionalAreaID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_functional_areas',
  `functionalAreaOwnerID` int DEFAULT NULL COMMENT 'FK to people - Function head',
  `isCustom` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Custom vs standard APQC process',
  `isActive` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `createdByID` int DEFAULT NULL COMMENT 'FK to people',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`processID`),
  UNIQUE KEY `unique_processCode` (`processCode`),
  KEY `idx_processGroup` (`processGroupID`),
  KEY `idx_functionalArea` (`functionalArea`),
  KEY `idx_functionalAreaOwner` (`functionalAreaOwnerID`),
  KEY `idx_isActive` (`isActive`),
  KEY `idx_functionalAreaID` (`functionalAreaID`),
  KEY `idx_categoryID` (`categoryID`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='APQC Processes - Specific workflows';


-- --------------------------------------------------------
-- Table: tija_function_head_assignments
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_function_head_assignments`;
CREATE TABLE IF NOT EXISTS `tija_function_head_assignments` (
  `assignmentID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `employeeID` int NOT NULL COMMENT 'FK to people - Function head',
  `functionalArea` enum('Finance','HR','IT','Sales','Marketing','Legal','Facilities','Custom') COLLATE utf8mb4_unicode_ci NOT NULL,
  `functionalAreaID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_functional_areas',
  `effectiveDate` date NOT NULL,
  `expiryDate` date DEFAULT NULL,
  `permissions` json DEFAULT NULL COMMENT 'Specific permissions (define_processes, define_workflows, approve_sops, etc.)',
  `isActive` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  PRIMARY KEY (`assignmentID`),
  KEY `idx_employee` (`employeeID`),
  KEY `idx_functionalArea` (`functionalArea`),
  KEY `idx_isActive` (`isActive`),
  KEY `idx_effectiveDate` (`effectiveDate`),
  KEY `idx_functionalAreaID` (`functionalAreaID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Function head assignments to functional areas';


-- --------------------------------------------------------
-- Table: tija_functional_areas
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_functional_areas`;
CREATE TABLE IF NOT EXISTS `tija_functional_areas` (
  `functionalAreaID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `functionalAreaCode` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Unique code (e.g., FIN, HR, IT)',
  `functionalAreaName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Display name (e.g., Finance, Human Resources)',
  `functionalAreaDescription` text COLLATE utf8mb4_unicode_ci COMMENT 'Description of the functional area',
  `isShared` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'Y' COMMENT 'Can be shared across organizations',
  `isActive` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `displayOrder` int DEFAULT '0',
  `createdByID` int DEFAULT NULL COMMENT 'FK to people',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`functionalAreaID`),
  UNIQUE KEY `unique_functionalAreaCode` (`functionalAreaCode`),
  KEY `idx_isActive` (`isActive`),
  KEY `idx_isShared` (`isShared`),
  KEY `idx_displayOrder` (`displayOrder`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Master functional areas that can be shared across organizations';


-- --------------------------------------------------------
-- Table: tija_goal_cascade_log
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_goal_cascade_log`;
CREATE TABLE IF NOT EXISTS `tija_goal_cascade_log` (
  `logID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `parentGoalUUID` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK to tija_goals.goalUUID - parent goal',
  `childGoalUUID` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK to tija_goals.goalUUID - child goal created',
  `cascadeMode` enum('Strict','Aligned','Hybrid') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mode used for cascade',
  `targetEntityID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_entities.entityID - where cascaded to',
  `targetUserID` int UNSIGNED DEFAULT NULL COMMENT 'FK to people.ID - individual target if applicable',
  `cascadeDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When cascade was executed',
  `cascadedByUserID` int UNSIGNED NOT NULL COMMENT 'FK to people.ID - who executed cascade',
  `status` enum('Pending','Accepted','Rejected','Modified','AutoCreated') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending' COMMENT 'Cascade status',
  `modificationNotes` text COLLATE utf8mb4_unicode_ci COMMENT 'Notes if status is Modified',
  `responseDate` datetime DEFAULT NULL COMMENT 'When target responded (accepted/rejected)',
  `respondedByUserID` int UNSIGNED DEFAULT NULL COMMENT 'FK to people.ID - who responded',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`logID`),
  KEY `idx_parentGoal` (`parentGoalUUID`),
  KEY `idx_childGoal` (`childGoalUUID`),
  KEY `idx_cascadeMode` (`cascadeMode`),
  KEY `idx_targetEntity` (`targetEntityID`),
  KEY `idx_targetUser` (`targetUserID`),
  KEY `idx_status` (`status`),
  KEY `idx_cascadeDate` (`cascadeDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Goal Cascade Log - Audit trail for goal cascading operations';


-- --------------------------------------------------------
-- Table: tija_goal_currency_rates
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_goal_currency_rates`;
CREATE TABLE IF NOT EXISTS `tija_goal_currency_rates` (
  `rateID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `fromCurrency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ISO currency code (e.g., USD, EUR, JPY)',
  `toCurrency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ISO currency code (target currency)',
  `budgetRate` decimal(15,6) NOT NULL COMMENT 'Fixed budget rate (set at fiscal year start)',
  `spotRate` decimal(15,6) NOT NULL COMMENT 'Current spot rate',
  `effectiveDate` date NOT NULL COMMENT 'Date rate becomes effective',
  `expiryDate` date DEFAULT NULL COMMENT 'Date rate expires (NULL = current)',
  `fiscalYear` year NOT NULL COMMENT 'Fiscal year this rate applies to',
  `rateType` enum('Budget','Spot','Average') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Spot' COMMENT 'Type of rate',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`rateID`),
  UNIQUE KEY `unique_currency_date` (`fromCurrency`,`toCurrency`,`effectiveDate`,`rateType`),
  KEY `idx_fromCurrency` (`fromCurrency`),
  KEY `idx_toCurrency` (`toCurrency`),
  KEY `idx_effectiveDate` (`effectiveDate`),
  KEY `idx_fiscalYear` (`fiscalYear`),
  KEY `idx_rateType` (`rateType`)
) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Goal Currency Rates - Exchange rates for multi-currency performance normalization';


-- --------------------------------------------------------
-- Table: tija_goal_evaluation_weights
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_goal_evaluation_weights`;
CREATE TABLE IF NOT EXISTS `tija_goal_evaluation_weights` (
  `weightID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `goalUUID` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK to tija_goals.goalUUID',
  `evaluatorRole` enum('Manager','Self','Peer','Subordinate','Matrix','External') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Role of evaluator',
  `weight` decimal(5,4) NOT NULL COMMENT 'Weight percentage (0.0000-1.0000)',
  `isDefault` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'Is this a default weight (can be overridden)',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`weightID`),
  UNIQUE KEY `unique_goal_role` (`goalUUID`,`evaluatorRole`),
  KEY `idx_goalUUID` (`goalUUID`),
  KEY `idx_evaluatorRole` (`evaluatorRole`)
) ;


-- --------------------------------------------------------
-- Table: tija_goal_evaluations
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_goal_evaluations`;
CREATE TABLE IF NOT EXISTS `tija_goal_evaluations` (
  `evaluationID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `goalUUID` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK to tija_goals.goalUUID',
  `evaluatorUserID` int UNSIGNED NOT NULL COMMENT 'FK to people.ID - who is evaluating',
  `evaluatorRole` enum('Manager','Self','Peer','Subordinate','Matrix','External') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Role of evaluator',
  `score` decimal(5,2) NOT NULL COMMENT 'Score given (0.00-100.00)',
  `comments` text COLLATE utf8mb4_unicode_ci COMMENT 'Evaluation comments/feedback',
  `isAnonymous` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'Is this evaluation anonymous',
  `evaluationDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When evaluation was submitted',
  `status` enum('Draft','Submitted','Approved','Rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Draft' COMMENT 'Evaluation status',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`evaluationID`),
  KEY `idx_goalUUID` (`goalUUID`),
  KEY `idx_evaluator` (`evaluatorUserID`),
  KEY `idx_evaluatorRole` (`evaluatorRole`),
  KEY `idx_status` (`status`),
  KEY `idx_evaluationDate` (`evaluationDate`)
) ;


-- --------------------------------------------------------
-- Table: tija_goal_kpis
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_goal_kpis`;
CREATE TABLE IF NOT EXISTS `tija_goal_kpis` (
  `kpiID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `goalUUID` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK to tija_goals.goalUUID',
  `kpiName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'KPI name',
  `kpiDescription` text COLLATE utf8mb4_unicode_ci COMMENT 'KPI description',
  `measurementFrequency` enum('Daily','Weekly','Monthly','Quarterly','Annual','Continuous') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Monthly' COMMENT 'How often this KPI is measured',
  `baselineValue` decimal(15,2) DEFAULT NULL COMMENT 'Baseline value at start',
  `targetValue` decimal(15,2) NOT NULL COMMENT 'Target value to achieve',
  `currentValue` decimal(15,2) DEFAULT NULL COMMENT 'Current value',
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Unit of measurement (e.g., USD, %, hours)',
  `currencyCode` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ISO currency code if monetary KPI',
  `reportingRate` decimal(15,6) DEFAULT NULL COMMENT 'Exchange rate for multi-currency normalization',
  `isPerpetual` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'Is this a perpetual/continuous KPI',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`kpiID`),
  UNIQUE KEY `unique_goalUUID` (`goalUUID`),
  KEY `idx_currency` (`currencyCode`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='KPI-specific data - Key Performance Indicators';


-- --------------------------------------------------------
-- Table: tija_goal_library
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_goal_library`;
CREATE TABLE IF NOT EXISTS `tija_goal_library` (
  `libraryID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `templateCode` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Unique template code (e.g., SALE-001)',
  `templateName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Template name',
  `templateDescription` text COLLATE utf8mb4_unicode_ci COMMENT 'Template description',
  `goalType` enum('Strategic','OKR','KPI') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Type of goal this template creates',
  `variables` json DEFAULT NULL COMMENT 'Parameterized fields: ["Product", "Target", "Timeframe"]',
  `defaultKPIs` json DEFAULT NULL COMMENT 'Suggested metrics: [{"name": "Revenue Growth", "target": 20}]',
  `jurisdictionDeny` json DEFAULT NULL COMMENT 'Array of jurisdiction codes where invalid: ["DE", "FR"]',
  `suggestedWeight` decimal(5,4) DEFAULT '0.2500' COMMENT 'Suggested weight (0.0000-1.0000)',
  `functionalDomain` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Department/job family: Sales, IT, HR, Legal, Operations',
  `competencyLevel` enum('Junior','Senior','Principal','Executive','All') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'All' COMMENT 'Required seniority level',
  `strategicPillar` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'L0 objective it supports: Innovation, Revenue, ESG, Customer Intimacy',
  `timeHorizon` enum('5-Year','Annual','Quarterly','Sprint','Monthly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Annual' COMMENT 'Intended duration',
  `jurisdictionScope` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Where valid: Global, EU-Only, Excludes-California',
  `broaderConceptID` int UNSIGNED DEFAULT NULL COMMENT 'SKOS: FK to parent concept in taxonomy',
  `narrowerConceptIDs` json DEFAULT NULL COMMENT 'SKOS: Array of child concept IDs',
  `relatedConceptIDs` json DEFAULT NULL COMMENT 'SKOS: Array of related concept IDs',
  `isActive` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y',
  `usageCount` int UNSIGNED DEFAULT '0' COMMENT 'Number of times this template has been used',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int UNSIGNED DEFAULT NULL COMMENT 'FK to people.ID',
  `Lapsed` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`libraryID`),
  UNIQUE KEY `unique_templateCode` (`templateCode`),
  KEY `idx_goalType` (`goalType`),
  KEY `idx_functionalDomain` (`functionalDomain`),
  KEY `idx_competencyLevel` (`competencyLevel`),
  KEY `idx_strategicPillar` (`strategicPillar`),
  KEY `idx_timeHorizon` (`timeHorizon`),
  KEY `idx_isActive` (`isActive`),
  KEY `idx_broaderConcept` (`broaderConceptID`),
  KEY `LastUpdatedByID` (`LastUpdatedByID`)
) ;


-- --------------------------------------------------------
-- Table: tija_goal_library_versions
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_goal_library_versions`;
CREATE TABLE IF NOT EXISTS `tija_goal_library_versions` (
  `versionID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `libraryID` int UNSIGNED NOT NULL COMMENT 'FK to tija_goal_library.libraryID',
  `versionNumber` int UNSIGNED NOT NULL COMMENT 'Version number (1, 2, 3, ...)',
  `templateData` json NOT NULL COMMENT 'Complete snapshot of template at this version',
  `changeDescription` text COLLATE utf8mb4_unicode_ci COMMENT 'Description of changes in this version',
  `effectiveDate` date NOT NULL COMMENT 'Date this version became effective',
  `deprecatedDate` date DEFAULT NULL COMMENT 'Date this version was deprecated (NULL = current)',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdatedByID` int UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`versionID`),
  UNIQUE KEY `unique_library_version` (`libraryID`,`versionNumber`),
  KEY `idx_libraryID` (`libraryID`),
  KEY `idx_effectiveDate` (`effectiveDate`),
  KEY `LastUpdatedByID` (`LastUpdatedByID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Goal Library Versions - Template versioning and change tracking';


-- --------------------------------------------------------
-- Table: tija_goal_matrix_assignments
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_goal_matrix_assignments`;
CREATE TABLE IF NOT EXISTS `tija_goal_matrix_assignments` (
  `assignmentID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `goalUUID` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK to tija_goals.goalUUID',
  `employeeUserID` int UNSIGNED NOT NULL COMMENT 'FK to people.ID - employee receiving goal',
  `matrixManagerID` int UNSIGNED NOT NULL COMMENT 'FK to people.ID - functional/matrix manager',
  `administrativeManagerID` int UNSIGNED DEFAULT NULL COMMENT 'FK to people.ID - legal entity manager',
  `assignmentType` enum('Functional','Project','Matrix','Temporary') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Matrix' COMMENT 'Type of assignment',
  `allocationPercent` decimal(5,2) DEFAULT '100.00' COMMENT 'Percentage allocation if partial (0.00-100.00)',
  `projectID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_projects.projectID if project-based',
  `startDate` date NOT NULL COMMENT 'Assignment start date',
  `endDate` date DEFAULT NULL COMMENT 'Assignment end date (NULL = ongoing)',
  `status` enum('Active','Completed','Cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`assignmentID`),
  KEY `idx_goalUUID` (`goalUUID`),
  KEY `idx_employee` (`employeeUserID`),
  KEY `idx_matrixManager` (`matrixManagerID`),
  KEY `idx_adminManager` (`administrativeManagerID`),
  KEY `idx_assignmentType` (`assignmentType`),
  KEY `idx_projectID` (`projectID`),
  KEY `idx_status` (`status`),
  KEY `idx_dates` (`startDate`,`endDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Goal Matrix Assignments - Cross-border and matrix goal assignments';


-- --------------------------------------------------------
-- Table: tija_goal_okrs
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_goal_okrs`;
CREATE TABLE IF NOT EXISTS `tija_goal_okrs` (
  `okrID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `goalUUID` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK to tija_goals.goalUUID',
  `objective` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Qualitative Objective (the O in OKR)',
  `keyResults` json NOT NULL COMMENT 'Array of Key Results: [{"kr": "Reduce carbon by 20%", "target": 20, "current": 15, "unit": "percent"}, ...]',
  `alignmentDirection` enum('TopDown','BottomUp','Bidirectional') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'TopDown' COMMENT 'How this OKR aligns',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`okrID`),
  UNIQUE KEY `unique_goalUUID` (`goalUUID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='OKR-specific data - Objectives and Key Results';


-- --------------------------------------------------------
-- Table: tija_goal_performance_snapshots
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_goal_performance_snapshots`;
CREATE TABLE IF NOT EXISTS `tija_goal_performance_snapshots` (
  `snapshotID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `goalUUID` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK to tija_goals.goalUUID',
  `snapshotDate` date NOT NULL COMMENT 'Date of snapshot (typically weekly)',
  `currentScore` decimal(5,2) DEFAULT NULL COMMENT 'Current calculated score (0.00-100.00)',
  `targetValue` decimal(15,2) DEFAULT NULL COMMENT 'Target value at snapshot time',
  `actualValue` decimal(15,2) DEFAULT NULL COMMENT 'Actual value at snapshot time',
  `completionPercentage` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'Completion percentage (0.00-100.00)',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active' COMMENT 'Status: OnTrack, AtRisk, Behind, Completed',
  `trend` enum('Improving','Stable','Declining') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Trend compared to previous snapshot',
  `ownerEntityID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_entities.entityID - for aggregation',
  `ownerUserID` int UNSIGNED DEFAULT NULL COMMENT 'FK to people.ID - for individual goals',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`snapshotID`),
  UNIQUE KEY `unique_goal_snapshot` (`goalUUID`,`snapshotDate`),
  KEY `idx_goalUUID` (`goalUUID`),
  KEY `idx_snapshotDate` (`snapshotDate`),
  KEY `idx_ownerEntity` (`ownerEntityID`),
  KEY `idx_ownerUser` (`ownerUserID`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Goal Performance Snapshots - Weekly snapshots for data warehouse and reporting';


-- --------------------------------------------------------
-- Table: tija_goal_scores
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_goal_scores`;
CREATE TABLE IF NOT EXISTS `tija_goal_scores` (
  `scoreID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `goalUUID` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK to tija_goals.goalUUID',
  `calculatedScore` decimal(5,2) NOT NULL COMMENT 'Weighted average score (0.00-100.00)',
  `weightedScore` decimal(5,2) NOT NULL COMMENT 'Score × weight (0.00-100.00)',
  `calculationDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When score was calculated',
  `calculationMethod` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'WeightedAverage' COMMENT 'Method used: WeightedAverage, AHP, etc.',
  `evaluatorCount` int UNSIGNED DEFAULT '0' COMMENT 'Number of evaluators included',
  `missingEvaluators` json DEFAULT NULL COMMENT 'Array of evaluator roles that did not submit',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`scoreID`),
  UNIQUE KEY `unique_goal_latest` (`goalUUID`,`calculationDate`),
  KEY `idx_goalUUID` (`goalUUID`),
  KEY `idx_calculationDate` (`calculationDate`)
) ;


-- --------------------------------------------------------
-- Table: tija_goals
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_goals`;
CREATE TABLE IF NOT EXISTS `tija_goals` (
  `goalUUID` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'UUID v4 for global uniqueness and sharding support',
  `parentGoalUUID` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Self-referencing FK for cascading goals',
  `ownerEntityID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_entities.entityID for entity-level goals',
  `ownerUserID` int UNSIGNED DEFAULT NULL COMMENT 'FK to people.ID for individual-level goals',
  `libraryRefID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_goal_library.libraryID if created from template',
  `goalType` enum('Strategic','OKR','KPI') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Type of goal',
  `goalTitle` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Goal title/name',
  `goalDescription` text COLLATE utf8mb4_unicode_ci COMMENT 'Detailed description',
  `propriety` enum('Low','Medium','High','Critical') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Medium' COMMENT 'Criticality level',
  `weight` decimal(5,4) NOT NULL DEFAULT '0.0000' COMMENT 'Weight percentage (0.0000-1.0000)',
  `progressMetric` json DEFAULT NULL COMMENT 'Progress tracking: {"current": 80, "target": 100, "unit": "USD", "currency": "USD"}',
  `evaluatorConfig` json DEFAULT NULL COMMENT 'Multi-rater configuration: {"manager_weight": 0.5, "peer_weight": 0.3, "self_weight": 0.2}',
  `jurisdictionID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_entities.entityID for L3 compliance rules',
  `visibility` enum('Global','Public','Private') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Private' COMMENT 'Visibility scope',
  `cascadeMode` enum('Strict','Aligned','Hybrid','None') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'None' COMMENT 'Cascade mode if this is a parent goal',
  `startDate` date NOT NULL COMMENT 'Goal start date',
  `endDate` date NOT NULL COMMENT 'Goal end date',
  `status` enum('Draft','Active','Completed','Cancelled','OnHold') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Draft' COMMENT 'Goal status',
  `completionPercentage` decimal(5,2) DEFAULT '0.00' COMMENT 'Calculated completion percentage',
  `sysStartTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Temporal versioning start',
  `sysEndTime` datetime DEFAULT NULL COMMENT 'Temporal versioning end (NULL = current version)',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int UNSIGNED DEFAULT NULL COMMENT 'FK to people.ID',
  `Lapsed` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`goalUUID`),
  KEY `idx_parentGoal` (`parentGoalUUID`),
  KEY `idx_ownerEntity` (`ownerEntityID`),
  KEY `idx_ownerUser` (`ownerUserID`),
  KEY `idx_libraryRef` (`libraryRefID`),
  KEY `idx_goalType` (`goalType`),
  KEY `idx_status` (`status`),
  KEY `idx_propriety` (`propriety`),
  KEY `idx_dates` (`startDate`,`endDate`),
  KEY `idx_jurisdiction` (`jurisdictionID`),
  KEY `idx_temporal` (`sysStartTime`,`sysEndTime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Main goals table - supports Strategic Goals, OKRs, and KPIs';


-- --------------------------------------------------------
-- Table: tija_leave_handover_artifacts
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_leave_handover_artifacts`;
CREATE TABLE IF NOT EXISTS `tija_leave_handover_artifacts` (
  `artifactID` int NOT NULL AUTO_INCREMENT,
  `handoverID` int NOT NULL,
  `handoverItemID` int DEFAULT NULL,
  `assignmentID` int DEFAULT NULL,
  `artifactType` enum('document','credential','training','other') NOT NULL DEFAULT 'document',
  `filePath` varchar(255) NOT NULL,
  `fileLabel` varchar(255) DEFAULT NULL,
  `description` text,
  `accessInstructions` text,
  `uploadedByID` int NOT NULL,
  `uploadedAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`artifactID`),
  KEY `idx_artifact_handover` (`handoverID`),
  KEY `idx_artifact_item` (`handoverItemID`),
  KEY `idx_artifact_assignment` (`assignmentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- --------------------------------------------------------
-- Table: tija_leave_handover_assignments
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_leave_handover_assignments`;
CREATE TABLE IF NOT EXISTS `tija_leave_handover_assignments` (
  `assignmentID` int NOT NULL AUTO_INCREMENT,
  `handoverID` int NOT NULL,
  `handoverItemID` int DEFAULT NULL,
  `assignedToID` int NOT NULL,
  `assignedByID` int NOT NULL,
  `assignmentDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `confirmationStatus` enum('pending','acknowledged','confirmed','rejected') NOT NULL DEFAULT 'pending',
  `confirmedDate` datetime DEFAULT NULL,
  `confirmationComments` text,
  `negotiationID` int DEFAULT NULL COMMENT 'FK to tija_leave_handover_peer_negotiations',
  `revisionRequested` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Whether revision was requested for this assignment',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`assignmentID`),
  KEY `idx_assignment_handover` (`handoverID`),
  KEY `idx_assignment_item` (`handoverItemID`),
  KEY `idx_assignment_assignee` (`assignedToID`),
  KEY `idx_assignment_status` (`confirmationStatus`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- --------------------------------------------------------
-- Table: tija_leave_handover_confirmations
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_leave_handover_confirmations`;
CREATE TABLE IF NOT EXISTS `tija_leave_handover_confirmations` (
  `confirmationID` int NOT NULL AUTO_INCREMENT,
  `assignmentID` int NOT NULL,
  `handoverItemID` int DEFAULT NULL,
  `briefed` enum('Y','N','not_required') NOT NULL DEFAULT 'Y',
  `briefedDate` datetime DEFAULT NULL,
  `trained` enum('Y','N','not_required') NOT NULL DEFAULT 'not_required',
  `trainedDate` datetime DEFAULT NULL,
  `hasCredentials` enum('Y','N','not_required') NOT NULL DEFAULT 'not_required',
  `credentialsDetails` text,
  `hasTools` enum('Y','N','not_required') NOT NULL DEFAULT 'not_required',
  `toolsDetails` text,
  `hasDocuments` enum('Y','N','not_required') NOT NULL DEFAULT 'not_required',
  `documentsDetails` text,
  `readyToTakeOver` enum('Y','N') NOT NULL DEFAULT 'N',
  `additionalNotes` text,
  `confirmedByID` int NOT NULL,
  `confirmedDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`confirmationID`),
  KEY `idx_confirmation_assignment` (`assignmentID`),
  KEY `idx_confirmation_item` (`handoverItemID`),
  KEY `idx_confirmation_ready` (`readyToTakeOver`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- --------------------------------------------------------
-- Table: tija_leave_handover_fsm_states
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_leave_handover_fsm_states`;
CREATE TABLE IF NOT EXISTS `tija_leave_handover_fsm_states` (
  `stateID` int NOT NULL AUTO_INCREMENT,
  `leaveApplicationID` int NOT NULL,
  `handoverID` int DEFAULT NULL,
  `currentState` enum('ST_00','ST_01','ST_02','ST_03','ST_04','ST_05','ST_06','ST_07') NOT NULL,
  `previousState` enum('ST_00','ST_01','ST_02','ST_03','ST_04','ST_05','ST_06','ST_07') DEFAULT NULL,
  `stateOwnerID` int DEFAULT NULL COMMENT 'Employee ID who owns current state',
  `nomineeID` int DEFAULT NULL COMMENT 'Peer/nominee assigned for handover',
  `stateEnteredAt` datetime NOT NULL,
  `stateCompletedAt` datetime DEFAULT NULL,
  `timerStartedAt` datetime DEFAULT NULL COMMENT 'For peer response deadlines',
  `timerExpiresAt` datetime DEFAULT NULL,
  `revisionCount` int NOT NULL DEFAULT '0',
  `chainOfCustodyLog` text COMMENT 'JSON log of state transitions',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`stateID`),
  KEY `idx_application` (`leaveApplicationID`),
  KEY `idx_handover` (`handoverID`),
  KEY `idx_current_state` (`currentState`),
  KEY `idx_nominee` (`nomineeID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- --------------------------------------------------------
-- Table: tija_leave_handover_items
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_leave_handover_items`;
CREATE TABLE IF NOT EXISTS `tija_leave_handover_items` (
  `handoverItemID` int NOT NULL AUTO_INCREMENT,
  `handoverID` int NOT NULL,
  `itemType` enum('project_task','function','duty','other') NOT NULL DEFAULT 'other',
  `itemTitle` varchar(255) NOT NULL,
  `itemDescription` text,
  `projectID` int DEFAULT NULL,
  `taskID` int DEFAULT NULL,
  `priority` enum('low','medium','high','critical') NOT NULL DEFAULT 'medium',
  `dueDate` date DEFAULT NULL,
  `instructions` text,
  `isMandatory` enum('Y','N') NOT NULL DEFAULT 'Y',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`handoverItemID`),
  KEY `idx_item_handover` (`handoverID`),
  KEY `idx_item_type` (`itemType`),
  KEY `idx_item_priority` (`priority`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- --------------------------------------------------------
-- Table: tija_leave_handover_packages
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_leave_handover_packages`;
CREATE TABLE IF NOT EXISTS `tija_leave_handover_packages` (
  `packageID` int NOT NULL AUTO_INCREMENT,
  `handoverID` int NOT NULL,
  `preparedByID` int NOT NULL,
  `lineManagerID` int DEFAULT NULL,
  `packageStatus` enum('draft','submitted','approved','returned') NOT NULL DEFAULT 'draft',
  `handoverOverview` text,
  `taskChecklistJson` longtext,
  `knowledgeTransferPlan` text,
  `credentialStatus` enum('complete','partial','missing','not_required') NOT NULL DEFAULT 'not_required',
  `documentStatus` enum('complete','partial','missing','not_required') NOT NULL DEFAULT 'not_required',
  `trainingStatus` enum('complete','partial','missing','not_required') NOT NULL DEFAULT 'not_required',
  `riskAssessment` text,
  `submittedAt` datetime DEFAULT NULL,
  `returnedReason` text,
  `managerNotes` text,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`packageID`),
  UNIQUE KEY `uniq_package_handover` (`handoverID`),
  KEY `idx_package_status` (`packageStatus`),
  KEY `idx_package_manager` (`lineManagerID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- --------------------------------------------------------
-- Table: tija_leave_handover_peer_negotiations
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_leave_handover_peer_negotiations`;
CREATE TABLE IF NOT EXISTS `tija_leave_handover_peer_negotiations` (
  `negotiationID` int NOT NULL AUTO_INCREMENT,
  `handoverID` int NOT NULL,
  `assignmentID` int DEFAULT NULL,
  `nomineeID` int NOT NULL,
  `requesterID` int NOT NULL,
  `negotiationType` enum('request_change','reject','accept') NOT NULL,
  `requestedChanges` text COMMENT 'Details of what needs to be changed',
  `negotiationStatus` enum('pending','resolved','escalated') NOT NULL DEFAULT 'pending',
  `responseDate` datetime DEFAULT NULL,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`negotiationID`),
  KEY `idx_handover` (`handoverID`),
  KEY `idx_nominee` (`nomineeID`),
  KEY `idx_assignment` (`assignmentID`),
  KEY `idx_status` (`negotiationStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- --------------------------------------------------------
-- Table: tija_leave_handover_policies
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_leave_handover_policies`;
CREATE TABLE IF NOT EXISTS `tija_leave_handover_policies` (
  `policyID` int NOT NULL AUTO_INCREMENT,
  `entityID` int NOT NULL,
  `orgDataID` int DEFAULT NULL,
  `leaveTypeID` int DEFAULT NULL,
  `policyScope` enum('entity_wide','role_based','job_group','job_level','job_title') NOT NULL DEFAULT 'entity_wide' COMMENT 'Scope of policy targeting',
  `targetRoleID` int DEFAULT NULL COMMENT 'Target role ID for role-based policies',
  `targetJobCategoryID` int DEFAULT NULL COMMENT 'Target job category ID for job group policies',
  `targetJobBandID` int DEFAULT NULL COMMENT 'Target job band ID for job group policies',
  `targetJobLevelID` int DEFAULT NULL COMMENT 'Target job level ID (FK to tija_role_levels)',
  `targetJobTitleID` int DEFAULT NULL COMMENT 'Target job title ID (FK to tija_job_titles)',
  `requireNomineeAcceptance` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Whether nominee acceptance is required',
  `nomineeResponseDeadlineHours` int NOT NULL DEFAULT '48' COMMENT 'Hours for nominee to respond',
  `allowPeerRevision` enum('Y','N') NOT NULL DEFAULT 'Y' COMMENT 'Whether peer can request revisions',
  `maxRevisionAttempts` int NOT NULL DEFAULT '3' COMMENT 'Maximum number of revision attempts allowed',
  `isMandatory` enum('Y','N') NOT NULL DEFAULT 'N',
  `minHandoverDays` int NOT NULL DEFAULT '0',
  `requireConfirmation` enum('Y','N') NOT NULL DEFAULT 'Y',
  `requireTraining` enum('Y','N') NOT NULL DEFAULT 'N',
  `requireCredentials` enum('Y','N') NOT NULL DEFAULT 'N',
  `requireTools` enum('Y','N') NOT NULL DEFAULT 'N',
  `requireDocuments` enum('Y','N') NOT NULL DEFAULT 'N',
  `allowProjectIntegration` enum('Y','N') NOT NULL DEFAULT 'N',
  `effectiveDate` date NOT NULL DEFAULT '1970-01-01',
  `expiryDate` date DEFAULT NULL,
  `policyName` varchar(255) DEFAULT NULL,
  `policyDescription` text,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`policyID`),
  KEY `idx_policy_entity` (`entityID`),
  KEY `idx_policy_leave_type` (`leaveTypeID`),
  KEY `idx_policy_effective` (`effectiveDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- --------------------------------------------------------
-- Table: tija_leave_handover_signoffs
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_leave_handover_signoffs`;
CREATE TABLE IF NOT EXISTS `tija_leave_handover_signoffs` (
  `signoffID` int NOT NULL AUTO_INCREMENT,
  `handoverID` int NOT NULL,
  `relatedAssignmentID` int DEFAULT NULL,
  `signoffType` enum('delegate','manager','hr') NOT NULL DEFAULT 'delegate',
  `status` enum('pending','approved','returned','rejected') NOT NULL DEFAULT 'pending',
  `signedByID` int DEFAULT NULL,
  `requiresActionByID` int DEFAULT NULL,
  `comments` text,
  `signedAt` datetime DEFAULT NULL,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`signoffID`),
  KEY `idx_signoff_handover` (`handoverID`),
  KEY `idx_signoff_assignment` (`relatedAssignmentID`),
  KEY `idx_signoff_type` (`signoffType`),
  KEY `idx_signoff_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- --------------------------------------------------------
-- Table: tija_leave_handovers
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_leave_handovers`;
CREATE TABLE IF NOT EXISTS `tija_leave_handovers` (
  `handoverID` int NOT NULL AUTO_INCREMENT,
  `leaveApplicationID` int NOT NULL,
  `employeeID` int NOT NULL,
  `entityID` int NOT NULL,
  `orgDataID` int NOT NULL,
  `policyID` int DEFAULT NULL,
  `nomineeID` int DEFAULT NULL COMMENT 'Peer/nominee assigned for handover',
  `fsmStateID` int DEFAULT NULL COMMENT 'FK to tija_leave_handover_fsm_states',
  `revisionCount` int NOT NULL DEFAULT '0' COMMENT 'Number of revision attempts',
  `handoverStatus` enum('pending','in_progress','partial','completed','rejected') NOT NULL DEFAULT 'pending',
  `packageStatus` enum('draft','submitted','approved','returned') NOT NULL DEFAULT 'draft',
  `managerReviewStatus` enum('pending','verified','returned','waived') NOT NULL DEFAULT 'pending',
  `managerReviewerID` int DEFAULT NULL,
  `managerReviewDate` datetime DEFAULT NULL,
  `managerComments` text,
  `hrOverrideStatus` enum('none','pending','approved','rejected') NOT NULL DEFAULT 'none',
  `hrOverrideByID` int DEFAULT NULL,
  `hrOverrideDate` datetime DEFAULT NULL,
  `hrOverrideComments` text,
  `handoverDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completionDate` datetime DEFAULT NULL,
  `notes` text,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`handoverID`),
  KEY `idx_handover_application` (`leaveApplicationID`),
  KEY `idx_handover_employee` (`employeeID`),
  KEY `idx_handover_status` (`handoverStatus`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- --------------------------------------------------------
-- Table: tija_leave_manual_balances
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_leave_manual_balances`;
CREATE TABLE IF NOT EXISTS `tija_leave_manual_balances` (
  `manualBalanceID` int NOT NULL AUTO_INCREMENT,
  `employeeID` int NOT NULL,
  `entityID` int NOT NULL,
  `leaveTypeID` int NOT NULL,
  `payrollNumber` varchar(120) DEFAULT NULL,
  `openingBalanceDays` decimal(8,2) NOT NULL DEFAULT '0.00',
  `asOfDate` date DEFAULT NULL,
  `uploadBatch` varchar(64) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `createdBy` int DEFAULT NULL,
  `updatedBy` int DEFAULT NULL,
  `createdDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `updatedDate` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` char(1) NOT NULL DEFAULT 'N',
  `Suspended` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`manualBalanceID`),
  UNIQUE KEY `uniq_manual_balance_employee_leave` (`employeeID`,`leaveTypeID`),
  KEY `idx_manual_balance_entity` (`entityID`),
  KEY `idx_manual_balance_leave_type` (`leaveTypeID`),
  KEY `idx_manual_balance_payroll` (`payrollNumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- --------------------------------------------------------
-- Table: tija_operational_projects
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_operational_projects`;
CREATE TABLE IF NOT EXISTS `tija_operational_projects` (
  `operationalProjectID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `projectCode` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `projectName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g., "FY25 HR Operations"',
  `functionalArea` enum('Finance','HR','IT','Sales','Marketing','Legal','Facilities','Custom') COLLATE utf8mb4_unicode_ci NOT NULL,
  `functionalAreaID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_functional_areas',
  `fiscalYear` int NOT NULL,
  `projectID` int DEFAULT NULL COMMENT 'FK to tija_projects - Soft booking link',
  `allocatedHours` decimal(10,2) DEFAULT '0.00' COMMENT 'Planned BAU hours',
  `actualHours` decimal(10,2) DEFAULT '0.00' COMMENT 'Logged hours',
  `fteRequirement` decimal(5,2) DEFAULT '0.00' COMMENT 'Calculated FTE',
  `functionalAreaOwnerID` int DEFAULT NULL COMMENT 'FK to people - Function head responsible',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `isActive` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `Lapsed` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`operationalProjectID`),
  UNIQUE KEY `unique_projectCode` (`projectCode`),
  KEY `idx_functionalArea` (`functionalArea`),
  KEY `idx_fiscalYear` (`fiscalYear`),
  KEY `idx_project` (`projectID`),
  KEY `idx_isActive` (`isActive`),
  KEY `idx_functionalAreaID` (`functionalAreaID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Operational projects (BAU buckets) for capacity planning';


-- --------------------------------------------------------
-- Table: tija_operational_task_checklists
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_operational_task_checklists`;
CREATE TABLE IF NOT EXISTS `tija_operational_task_checklists` (
  `checklistItemID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `templateID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_operational_task_templates - Template-level',
  `operationalTaskID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_operational_tasks - Instance-level',
  `itemOrder` int NOT NULL,
  `itemDescription` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `isMandatory` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `isCompleted` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `completedByID` int DEFAULT NULL COMMENT 'FK to people',
  `completedDate` datetime DEFAULT NULL,
  `validationRule` json DEFAULT NULL COMMENT 'Optional validation logic',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`checklistItemID`),
  KEY `idx_template` (`templateID`),
  KEY `idx_operationalTask` (`operationalTaskID`),
  KEY `idx_itemOrder` (`itemOrder`)
) ;


-- --------------------------------------------------------
-- Table: tija_operational_task_dependencies
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_operational_task_dependencies`;
CREATE TABLE IF NOT EXISTS `tija_operational_task_dependencies` (
  `dependencyID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `predecessorTaskID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_operational_tasks or templateID',
  `predecessorTemplateID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_operational_task_templates',
  `successorTaskID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_operational_tasks or templateID',
  `successorTemplateID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_operational_task_templates',
  `dependencyType` enum('finish_to_start','start_to_start','finish_to_finish') COLLATE utf8mb4_unicode_ci DEFAULT 'finish_to_start',
  `lagDays` int DEFAULT '0' COMMENT 'Delay in days',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`dependencyID`),
  KEY `idx_predecessorTask` (`predecessorTaskID`),
  KEY `idx_predecessorTemplate` (`predecessorTemplateID`),
  KEY `idx_successorTask` (`successorTaskID`),
  KEY `idx_successorTemplate` (`successorTemplateID`)
) ;


-- --------------------------------------------------------
-- Table: tija_operational_task_notifications
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_operational_task_notifications`;
CREATE TABLE IF NOT EXISTS `tija_operational_task_notifications` (
  `notificationID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `templateID` int UNSIGNED NOT NULL COMMENT 'FK to tija_operational_task_templates',
  `employeeID` int NOT NULL COMMENT 'FK to people - User to notify',
  `dueDate` date NOT NULL COMMENT 'Task due date',
  `notificationType` enum('scheduled_task_ready','task_overdue','task_due_soon') COLLATE utf8mb4_unicode_ci DEFAULT 'scheduled_task_ready',
  `status` enum('pending','sent','acknowledged','processed','dismissed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `sentDate` datetime DEFAULT NULL,
  `acknowledgedDate` datetime DEFAULT NULL,
  `processedDate` datetime DEFAULT NULL,
  `taskInstanceID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_operational_tasks - Created when processed',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`notificationID`),
  KEY `idx_template` (`templateID`),
  KEY `idx_employee` (`employeeID`),
  KEY `idx_status` (`status`),
  KEY `idx_dueDate` (`dueDate`),
  KEY `taskInstanceID` (`taskInstanceID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Notifications for manual task processing';


-- --------------------------------------------------------
-- Table: tija_operational_task_templates
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_operational_task_templates`;
CREATE TABLE IF NOT EXISTS `tija_operational_task_templates` (
  `templateID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `templateCode` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `templateName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `templateDescription` text COLLATE utf8mb4_unicode_ci,
  `processID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_bau_processes',
  `workflowID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_workflows - Optional workflow',
  `sopID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_sops - Linked SOP',
  `functionalArea` enum('Finance','HR','IT','Sales','Marketing','Legal','Facilities','Custom') COLLATE utf8mb4_unicode_ci NOT NULL,
  `functionalAreaID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_functional_areas',
  `frequencyType` enum('daily','weekly','monthly','quarterly','annually','custom','event_driven') COLLATE utf8mb4_unicode_ci NOT NULL,
  `frequencyInterval` int DEFAULT '1' COMMENT 'e.g., every 2 weeks',
  `frequencyDayOfWeek` int DEFAULT NULL COMMENT '1-7 for weekly',
  `frequencyDayOfMonth` int DEFAULT NULL COMMENT '1-31 for monthly/quarterly',
  `frequencyMonthOfYear` int DEFAULT NULL COMMENT '1-12 for annually',
  `triggerEvent` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Event name for event-driven tasks',
  `estimatedDuration` decimal(10,2) DEFAULT NULL COMMENT 'Estimated hours',
  `assignmentRule` json DEFAULT NULL COMMENT 'Auto-assignment logic (role-based, employee-specific, round-robin, etc.)',
  `requiresApproval` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `approverRoleID` int DEFAULT NULL COMMENT 'FK to permission roles',
  `requiresSOPReview` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Must review SOP before starting',
  `isActive` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `processingMode` enum('cron','manual','both') COLLATE utf8mb4_unicode_ci DEFAULT 'cron' COMMENT 'cron=automatic via cron, manual=user notification on login, both=both methods',
  `lastNotificationSent` datetime DEFAULT NULL COMMENT 'Last time notification was sent for manual processing',
  `createdByID` int DEFAULT NULL COMMENT 'FK to people',
  `functionalAreaOwnerID` int DEFAULT NULL COMMENT 'FK to people - Function head',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`templateID`),
  UNIQUE KEY `unique_templateCode` (`templateCode`),
  KEY `idx_process` (`processID`),
  KEY `idx_workflow` (`workflowID`),
  KEY `idx_sop` (`sopID`),
  KEY `idx_functionalArea` (`functionalArea`),
  KEY `idx_frequencyType` (`frequencyType`),
  KEY `idx_isActive` (`isActive`),
  KEY `idx_functionalAreaID` (`functionalAreaID`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Operational task templates for recurring tasks';


-- --------------------------------------------------------
-- Table: tija_operational_tasks
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_operational_tasks`;
CREATE TABLE IF NOT EXISTS `tija_operational_tasks` (
  `operationalTaskID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `templateID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_operational_task_templates',
  `workflowInstanceID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_workflow_instances - If workflow-enabled',
  `instanceNumber` int DEFAULT '1' COMMENT 'Cycle number',
  `dueDate` date NOT NULL,
  `startDate` date DEFAULT NULL,
  `completedDate` datetime DEFAULT NULL,
  `status` enum('pending','in_progress','completed','overdue','cancelled','blocked') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `assigneeID` int NOT NULL COMMENT 'FK to people',
  `processID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_bau_processes',
  `actualDuration` decimal(10,2) DEFAULT NULL COMMENT 'Actual hours spent',
  `nextInstanceDueDate` date DEFAULT NULL COMMENT 'For regeneration',
  `parentInstanceID` int UNSIGNED DEFAULT NULL COMMENT 'Links to previous cycle',
  `blockedByTaskID` int UNSIGNED DEFAULT NULL COMMENT 'Dependency blocker',
  `sopReviewed` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'SOP review status',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`operationalTaskID`),
  KEY `idx_template` (`templateID`),
  KEY `idx_workflowInstance` (`workflowInstanceID`),
  KEY `idx_assignee` (`assigneeID`),
  KEY `idx_process` (`processID`),
  KEY `idx_status` (`status`),
  KEY `idx_dueDate` (`dueDate`),
  KEY `idx_parentInstance` (`parentInstanceID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Operational task instances';


-- --------------------------------------------------------
-- Table: tija_org_hierarchy_closure
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_org_hierarchy_closure`;
CREATE TABLE IF NOT EXISTS `tija_org_hierarchy_closure` (
  `ancestor_id` int UNSIGNED NOT NULL COMMENT 'FK to tija_entities.entityID or people.ID',
  `descendant_id` int UNSIGNED NOT NULL COMMENT 'FK to tija_entities.entityID or people.ID',
  `depth` int NOT NULL DEFAULT '0' COMMENT 'Number of levels between ancestor and descendant',
  `hierarchy_type` enum('Administrative','Functional') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Administrative' COMMENT 'Type of hierarchy relationship',
  `ancestor_type` enum('Entity','Individual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Entity' COMMENT 'Type of ancestor node',
  `descendant_type` enum('Entity','Individual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Entity' COMMENT 'Type of descendant node',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ancestor_id`,`descendant_id`,`hierarchy_type`),
  KEY `idx_ancestor` (`ancestor_id`,`hierarchy_type`),
  KEY `idx_descendant` (`descendant_id`,`hierarchy_type`),
  KEY `idx_depth` (`depth`,`hierarchy_type`),
  KEY `idx_hierarchy_type` (`hierarchy_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Closure Table for organizational hierarchy - stores all ancestor-descendant paths';


-- --------------------------------------------------------
-- Table: tija_organization_functional_areas
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_organization_functional_areas`;
CREATE TABLE IF NOT EXISTS `tija_organization_functional_areas` (
  `linkID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `orgDataID` int NOT NULL COMMENT 'FK to tija_organisation_data',
  `functionalAreaID` int UNSIGNED NOT NULL COMMENT 'FK to tija_functional_areas',
  `isActive` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  PRIMARY KEY (`linkID`),
  UNIQUE KEY `unique_org_functional_area` (`orgDataID`,`functionalAreaID`),
  KEY `idx_organization` (`orgDataID`),
  KEY `idx_functionalArea` (`functionalAreaID`),
  KEY `idx_isActive` (`isActive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Junction table linking organizations to functional areas';


-- --------------------------------------------------------
-- Table: tija_process_metrics
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_process_metrics`;
CREATE TABLE IF NOT EXISTS `tija_process_metrics` (
  `metricID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `processID` int UNSIGNED NOT NULL COMMENT 'FK to tija_bau_processes',
  `metricName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g., cycle_time, cost_per_unit, error_rate',
  `metricValue` decimal(15,4) NOT NULL,
  `metricUnit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'e.g., hours, dollars, percentage',
  `measurementDate` date NOT NULL,
  `source` enum('actual','simulated','target') COLLATE utf8mb4_unicode_ci DEFAULT 'actual',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`metricID`),
  KEY `idx_process` (`processID`),
  KEY `idx_metricName` (`metricName`),
  KEY `idx_measurementDate` (`measurementDate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Process performance metrics';


-- --------------------------------------------------------
-- Table: tija_process_models
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_process_models`;
CREATE TABLE IF NOT EXISTS `tija_process_models` (
  `modelID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `modelName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modelDescription` text COLLATE utf8mb4_unicode_ci,
  `processID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_bau_processes',
  `modelType` enum('as_is','to_be','simulation','optimized') COLLATE utf8mb4_unicode_ci DEFAULT 'as_is',
  `modelDefinition` json DEFAULT NULL COMMENT 'Process model (BPMN-like structure)',
  `createdByID` int DEFAULT NULL COMMENT 'FK to people',
  `createdDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `isBaseline` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Baseline for comparison',
  PRIMARY KEY (`modelID`),
  KEY `idx_process` (`processID`),
  KEY `idx_modelType` (`modelType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Process model definitions';


-- --------------------------------------------------------
-- Table: tija_process_optimization_recommendations
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_process_optimization_recommendations`;
CREATE TABLE IF NOT EXISTS `tija_process_optimization_recommendations` (
  `recommendationID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `processID` int UNSIGNED NOT NULL COMMENT 'FK to tija_bau_processes',
  `recommendationType` enum('automation','reengineering','resource_allocation','elimination') COLLATE utf8mb4_unicode_ci NOT NULL,
  `recommendationTitle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recommendationDescription` text COLLATE utf8mb4_unicode_ci,
  `estimatedImpact` json DEFAULT NULL COMMENT 'Expected improvements',
  `implementationEffort` enum('low','medium','high') COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `priority` enum('low','medium','high','critical') COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `status` enum('pending','approved','implemented','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `createdDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `createdByID` int DEFAULT NULL COMMENT 'FK to people (system or user)',
  `approvedByID` int DEFAULT NULL COMMENT 'FK to people',
  `approvedDate` datetime DEFAULT NULL,
  PRIMARY KEY (`recommendationID`),
  KEY `idx_process` (`processID`),
  KEY `idx_status` (`status`),
  KEY `idx_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Process optimization recommendations';


-- --------------------------------------------------------
-- Table: tija_process_simulations
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_process_simulations`;
CREATE TABLE IF NOT EXISTS `tija_process_simulations` (
  `simulationID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `modelID` int UNSIGNED NOT NULL COMMENT 'FK to tija_process_models',
  `simulationName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `simulationDescription` text COLLATE utf8mb4_unicode_ci,
  `simulationParameters` json DEFAULT NULL COMMENT 'Input parameters',
  `simulationResults` json DEFAULT NULL COMMENT 'Output metrics',
  `runDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `runByID` int DEFAULT NULL COMMENT 'FK to people',
  `status` enum('pending','running','completed','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  PRIMARY KEY (`simulationID`),
  KEY `idx_model` (`modelID`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Process simulation runs';


-- --------------------------------------------------------
-- Table: tija_sales_document_access_log
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_sales_document_access_log`;
CREATE TABLE IF NOT EXISTS `tija_sales_document_access_log` (
  `accessID` int NOT NULL AUTO_INCREMENT,
  `documentID` int NOT NULL,
  `accessedBy` int NOT NULL,
  `accessType` enum('view','download','share','edit') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'view',
  `accessDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ipAddress` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `userAgent` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`accessID`),
  KEY `idx_document_access` (`documentID`),
  KEY `idx_accessed_by` (`accessedBy`),
  KEY `idx_access_date` (`accessDate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table: tija_sales_document_shares
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_sales_document_shares`;
CREATE TABLE IF NOT EXISTS `tija_sales_document_shares` (
  `shareID` int NOT NULL AUTO_INCREMENT,
  `documentID` int NOT NULL,
  `sharedWith` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sharedBy` int NOT NULL,
  `sharedDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `shareMethod` enum('email','link','portal') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'email',
  `accessLink` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accessExpiry` datetime DEFAULT NULL,
  `accessCount` int NOT NULL DEFAULT '0',
  `lastAccessedDate` datetime DEFAULT NULL,
  PRIMARY KEY (`shareID`),
  KEY `idx_document_shares` (`documentID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table: tija_sales_document_versions
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_sales_document_versions`;
CREATE TABLE IF NOT EXISTS `tija_sales_document_versions` (
  `versionID` int NOT NULL AUTO_INCREMENT,
  `documentID` int NOT NULL,
  `versionNumber` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fileName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fileURL` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fileSize` bigint DEFAULT NULL,
  `versionNotes` text COLLATE utf8mb4_unicode_ci,
  `uploadedBy` int NOT NULL,
  `uploadedOn` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `isCurrent` enum('Y','N') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`versionID`),
  KEY `idx_document_versions` (`documentID`),
  KEY `idx_version_current` (`isCurrent`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table: tija_sop_attachments
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_sop_attachments`;
CREATE TABLE IF NOT EXISTS `tija_sop_attachments` (
  `attachmentID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `sopID` int UNSIGNED NOT NULL COMMENT 'FK to tija_sops',
  `fileName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fileURL` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fileType` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fileSize` bigint DEFAULT NULL COMMENT 'File size in bytes',
  `uploadedByID` int DEFAULT NULL COMMENT 'FK to people',
  `uploadedDate` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`attachmentID`),
  KEY `idx_sop` (`sopID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='SOP file attachments';


-- --------------------------------------------------------
-- Table: tija_sop_links
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_sop_links`;
CREATE TABLE IF NOT EXISTS `tija_sop_links` (
  `linkID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `sopID` int UNSIGNED NOT NULL COMMENT 'FK to tija_sops',
  `linkType` enum('template','task','workflow_step','process') COLLATE utf8mb4_unicode_ci NOT NULL,
  `linkedEntityID` int UNSIGNED NOT NULL COMMENT 'ID of linked entity',
  `isRequired` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Must review before completion',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`linkID`),
  KEY `idx_sop` (`sopID`),
  KEY `idx_linkType_entity` (`linkType`,`linkedEntityID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Links SOPs to tasks/templates/workflows';


-- --------------------------------------------------------
-- Table: tija_sop_sections
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_sop_sections`;
CREATE TABLE IF NOT EXISTS `tija_sop_sections` (
  `sectionID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `sopID` int UNSIGNED NOT NULL COMMENT 'FK to tija_sops',
  `sectionOrder` int NOT NULL,
  `sectionTitle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sectionContent` text COLLATE utf8mb4_unicode_ci,
  `sectionType` enum('overview','procedure','checklist','troubleshooting','references') COLLATE utf8mb4_unicode_ci DEFAULT 'procedure',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`sectionID`),
  KEY `idx_sop` (`sopID`),
  KEY `idx_sectionOrder` (`sectionOrder`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='SOP structured sections';


-- --------------------------------------------------------
-- Table: tija_sops
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_sops`;
CREATE TABLE IF NOT EXISTS `tija_sops` (
  `sopID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `sopCode` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sopTitle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sopDescription` text COLLATE utf8mb4_unicode_ci,
  `processID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_bau_processes',
  `functionalArea` enum('Finance','HR','IT','Sales','Marketing','Legal','Facilities','Custom') COLLATE utf8mb4_unicode_ci NOT NULL,
  `functionalAreaID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_functional_areas',
  `sopVersion` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT '1.0' COMMENT 'Version number',
  `sopDocumentURL` text COLLATE utf8mb4_unicode_ci COMMENT 'Link to document/knowledge base',
  `sopContent` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Rich text content (HTML/Markdown)',
  `effectiveDate` date DEFAULT NULL,
  `expiryDate` date DEFAULT NULL,
  `approvalStatus` enum('draft','pending_approval','approved','archived') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `approvedByID` int DEFAULT NULL COMMENT 'FK to people',
  `approvedDate` datetime DEFAULT NULL,
  `createdByID` int DEFAULT NULL COMMENT 'FK to people',
  `functionalAreaOwnerID` int DEFAULT NULL COMMENT 'FK to people - Function head',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `isActive` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `Lapsed` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`sopID`),
  UNIQUE KEY `unique_sopCode_version` (`sopCode`,`sopVersion`),
  KEY `idx_process` (`processID`),
  KEY `idx_functionalArea` (`functionalArea`),
  KEY `idx_approvalStatus` (`approvalStatus`),
  KEY `idx_isActive` (`isActive`),
  KEY `idx_functionalAreaID` (`functionalAreaID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='SOP master records';


-- --------------------------------------------------------
-- Table: tija_workflow_instances
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_workflow_instances`;
CREATE TABLE IF NOT EXISTS `tija_workflow_instances` (
  `instanceID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `workflowID` int UNSIGNED NOT NULL COMMENT 'FK to tija_workflows',
  `operationalTaskID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_operational_tasks',
  `currentStepID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_workflow_steps',
  `status` enum('pending','in_progress','completed','cancelled','error') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `startedDate` datetime DEFAULT NULL,
  `completedDate` datetime DEFAULT NULL,
  `instanceData` json DEFAULT NULL COMMENT 'Runtime data',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`instanceID`),
  KEY `idx_workflow` (`workflowID`),
  KEY `idx_operationalTask` (`operationalTaskID`),
  KEY `idx_currentStep` (`currentStepID`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Active workflow executions';


-- --------------------------------------------------------
-- Table: tija_workflow_steps
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_workflow_steps`;
CREATE TABLE IF NOT EXISTS `tija_workflow_steps` (
  `workflowStepID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `workflowID` int UNSIGNED NOT NULL COMMENT 'FK to tija_workflows',
  `stepOrder` int NOT NULL,
  `stepName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stepDescription` text COLLATE utf8mb4_unicode_ci,
  `stepType` enum('task','approval','decision','notification','automation','subprocess') COLLATE utf8mb4_unicode_ci DEFAULT 'task',
  `assigneeType` enum('role','employee','function_head','auto') COLLATE utf8mb4_unicode_ci DEFAULT 'auto',
  `assigneeRoleID` int DEFAULT NULL COMMENT 'FK to permission roles',
  `assigneeEmployeeID` int DEFAULT NULL COMMENT 'FK to people',
  `estimatedDuration` decimal(10,2) DEFAULT NULL COMMENT 'Estimated hours',
  `isMandatory` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `stepConfig` json DEFAULT NULL COMMENT 'Step-specific configuration',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`workflowStepID`),
  KEY `idx_workflow` (`workflowID`),
  KEY `idx_stepOrder` (`stepOrder`),
  KEY `idx_assigneeRole` (`assigneeRoleID`),
  KEY `idx_assigneeEmployee` (`assigneeEmployeeID`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Individual steps in workflow';


-- --------------------------------------------------------
-- Table: tija_workflow_transitions
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_workflow_transitions`;
CREATE TABLE IF NOT EXISTS `tija_workflow_transitions` (
  `transitionID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `workflowID` int UNSIGNED NOT NULL COMMENT 'FK to tija_workflows',
  `fromStepID` int UNSIGNED NOT NULL COMMENT 'FK to tija_workflow_steps',
  `toStepID` int UNSIGNED NOT NULL COMMENT 'FK to tija_workflow_steps',
  `conditionType` enum('always','conditional','time_based','event_based') COLLATE utf8mb4_unicode_ci DEFAULT 'always',
  `conditionExpression` json DEFAULT NULL COMMENT 'Condition logic',
  `transitionLabel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`transitionID`),
  KEY `idx_workflow` (`workflowID`),
  KEY `idx_fromStep` (`fromStepID`),
  KEY `idx_toStep` (`toStepID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Transitions between workflow steps';


-- --------------------------------------------------------
-- Table: tija_workflows
-- --------------------------------------------------------

DROP TABLE IF EXISTS `tija_workflows`;
CREATE TABLE IF NOT EXISTS `tija_workflows` (
  `workflowID` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `workflowCode` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `workflowName` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `workflowDescription` text COLLATE utf8mb4_unicode_ci,
  `processID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_bau_processes',
  `functionalArea` enum('Finance','HR','IT','Sales','Marketing','Legal','Facilities','Custom') COLLATE utf8mb4_unicode_ci NOT NULL,
  `functionalAreaID` int UNSIGNED DEFAULT NULL COMMENT 'FK to tija_functional_areas',
  `workflowType` enum('sequential','parallel','conditional','state_machine') COLLATE utf8mb4_unicode_ci DEFAULT 'sequential',
  `version` int DEFAULT '1' COMMENT 'Version control',
  `isActive` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `workflowDefinition` json DEFAULT NULL COMMENT 'Workflow structure (nodes, edges, conditions)',
  `createdByID` int DEFAULT NULL COMMENT 'FK to people',
  `functionalAreaOwnerID` int DEFAULT NULL COMMENT 'FK to people - Function head',
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdatedByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('Y','N') COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`workflowID`),
  UNIQUE KEY `unique_workflowCode` (`workflowCode`),
  KEY `idx_process` (`processID`),
  KEY `idx_functionalArea` (`functionalArea`),
  KEY `idx_isActive` (`isActive`),
  KEY `idx_functionalAreaID` (`functionalAreaID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Master workflow definitions';


-- --------------------------------------------------------
-- Table: time_entry_templates
-- --------------------------------------------------------

DROP TABLE IF EXISTS `time_entry_templates`;
CREATE TABLE IF NOT EXISTS `time_entry_templates` (
  `templateID` int NOT NULL AUTO_INCREMENT COMMENT 'Primary key for template',
  `userID` int NOT NULL COMMENT 'User who created the template',
  `templateName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Name/description of the template',
  `templateData` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'JSON data containing template fields',
  `createdDate` datetime NOT NULL COMMENT 'When the template was created',
  `modifiedDate` datetime DEFAULT NULL COMMENT 'Last modification date',
  `Suspended` char(1) COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'Y/N - Is template active?',
  PRIMARY KEY (`templateID`),
  KEY `idx_userID` (`userID`),
  KEY `idx_suspended` (`Suspended`),
  KEY `idx_user_suspended` (`userID`,`Suspended`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores reusable time entry templates for quick data entry';


-- --------------------------------------------------------
-- Table: view_activity_expense_totals
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `view_activity_expense_totals` (
`activityID` int
,`approvedReimbursement` decimal(37,2)
,`expenseCount` bigint
,`paidReimbursement` decimal(37,2)
,`pendingReimbursement` decimal(37,2)
,`totalExpenses` decimal(37,2)
,`totalNonReimbursable` decimal(37,2)
,`totalReimbursable` decimal(37,2)
);
DROP TABLE IF EXISTS `view_activity_expense_totals`;

DROP VIEW IF EXISTS `view_activity_expense_totals`;


SET FOREIGN_KEY_CHECKS=1;
