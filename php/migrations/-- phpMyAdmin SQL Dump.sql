-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 20, 2025 at 07:27 AM
-- Server version: 8.3.0
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `demo_pms_final`
--

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_accumulation_history`
--

DROP TABLE IF EXISTS `tija_leave_accumulation_history`;
CREATE TABLE IF NOT EXISTS `tija_leave_accumulation_history` (
  `historyID` int NOT NULL AUTO_INCREMENT,
  `employeeID` int NOT NULL COMMENT 'Employee who received the accrual',
  `policyID` int NOT NULL COMMENT 'Policy that generated this accrual',
  `ruleID` int DEFAULT NULL COMMENT 'Rule that applied (if any)',
  `leaveTypeID` int NOT NULL COMMENT 'Leave type accrued',
  `accrualPeriod` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Period this accrual covers (e.g., 2024-01, 2024-Q1)',
  `accrualDate` date NOT NULL COMMENT 'Date when accrual was calculated',
  `baseAccrualRate` decimal(5,2) NOT NULL COMMENT 'Base rate from policy',
  `appliedMultiplier` decimal(3,2) DEFAULT '1.00' COMMENT 'Multiplier applied from rules',
  `finalAccrualAmount` decimal(5,2) NOT NULL COMMENT 'Final amount accrued',
  `carryoverAmount` decimal(5,2) DEFAULT '0.00' COMMENT 'Amount carried over from previous period',
  `totalBalance` decimal(5,2) NOT NULL COMMENT 'Total balance after this accrual',
  `calculationNotes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Notes about how this was calculated',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime DEFAULT NULL,
  `LastUpdateByID` int DEFAULT NULL,
  PRIMARY KEY (`historyID`),
  KEY `idx_employee_period` (`employeeID`,`accrualPeriod`),
  KEY `idx_policy_history` (`policyID`,`accrualDate`),
  KEY `idx_leave_type_date` (`leaveTypeID`,`accrualDate`),
  KEY `idx_accrual_date` (`accrualDate`),
  KEY `ruleID` (`ruleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='History of leave accruals for employees';

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_accumulation_policies`
--

DROP TABLE IF EXISTS `tija_leave_accumulation_policies`;
CREATE TABLE IF NOT EXISTS `tija_leave_accumulation_policies` (
  `policyID` int NOT NULL AUTO_INCREMENT,
  `entityID` int NOT NULL COMMENT 'Entity this policy applies to',
  `policyName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Name of the accumulation policy',
  `leaveTypeID` int NOT NULL COMMENT 'Leave type this policy applies to',
  `accrualType` enum('Monthly','Quarterly','Annual','Continuous') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Monthly',
  `accrualRate` decimal(5,2) NOT NULL COMMENT 'Days accrued per period',
  `maxCarryover` int DEFAULT NULL COMMENT 'Maximum days that can be carried over (null = unlimited)',
  `carryoverExpiryMonths` int DEFAULT NULL COMMENT 'Months after which carryover expires (null = never)',
  `accrualStartDate` date DEFAULT NULL COMMENT 'Date when accrual starts (null = immediate)',
  `accrualEndDate` date DEFAULT NULL COMMENT 'Date when accrual ends (null = indefinite)',
  `proRated` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N' COMMENT 'Whether accrual is pro-rated for partial periods',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime DEFAULT NULL,
  `LastUpdateByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `priority` int DEFAULT '1' COMMENT 'Priority order when multiple policies apply',
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y' COMMENT 'Whether this policy is currently active',
  `policyDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Detailed description of the policy',
  PRIMARY KEY (`policyID`),
  KEY `idx_entity` (`entityID`),
  KEY `idx_leave_type` (`leaveTypeID`),
  KEY `idx_accrual_type` (`accrualType`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Policies for leave accumulation and accrual';

--
-- Dumping data for table `tija_leave_accumulation_policies`
--

INSERT INTO `tija_leave_accumulation_policies` (`policyID`, `entityID`, `policyName`, `leaveTypeID`, `accrualType`, `accrualRate`, `maxCarryover`, `carryoverExpiryMonths`, `accrualStartDate`, `accrualEndDate`, `proRated`, `DateAdded`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`, `priority`, `isActive`, `policyDescription`) VALUES
(1, 1, 'Annual Leave Monthly Accrual', 1, 'Monthly', 2.00, 10, 12, NULL, NULL, 'Y', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N', 1, 'Y', NULL),
(2, 1, 'Sick Leave Monthly Accrual', 2, 'Monthly', 1.50, 5, 6, NULL, NULL, 'Y', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N', 1, 'Y', NULL),
(3, 1, 'Maternity Leave Annual', 3, 'Annual', 90.00, NULL, NULL, NULL, NULL, 'N', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N', 1, 'Y', NULL),
(4, 1, 'Paternity Leave Annual', 4, 'Annual', 14.00, NULL, NULL, NULL, NULL, 'N', '2025-09-27 19:57:42', NULL, NULL, 'N', 'N', 1, 'Y', NULL),
(5, 1, 'Annual Leave Monthly Accrual', 1, 'Monthly', 2.00, 10, 12, NULL, NULL, 'Y', '2025-09-27 20:01:05', NULL, NULL, 'N', 'N', 1, 'Y', NULL),
(6, 1, 'Sick Leave Monthly Accrual', 2, 'Monthly', 1.50, 5, 6, NULL, NULL, 'Y', '2025-09-27 20:01:05', NULL, NULL, 'N', 'N', 1, 'Y', NULL),
(7, 1, 'Maternity Leave Annual', 3, 'Annual', 90.00, NULL, NULL, NULL, NULL, 'N', '2025-09-27 20:01:05', NULL, NULL, 'N', 'N', 1, 'Y', NULL),
(8, 1, 'Paternity Leave Annual', 4, 'Annual', 14.00, NULL, NULL, NULL, NULL, 'N', '2025-09-27 20:01:05', NULL, NULL, 'N', 'N', 1, 'Y', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_accumulation_rules`
--

DROP TABLE IF EXISTS `tija_leave_accumulation_rules`;
CREATE TABLE IF NOT EXISTS `tija_leave_accumulation_rules` (
  `ruleID` int NOT NULL AUTO_INCREMENT,
  `policyID` int NOT NULL COMMENT 'Parent policy this rule belongs to',
  `ruleName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Name of the rule',
  `ruleType` enum('Tenure','Performance','Department','Role','Custom') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Tenure',
  `conditionField` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Field to evaluate (e.g., yearsOfService, performanceRating)',
  `conditionOperator` enum('=','>','>=','<','<=','<>','IN','NOT IN') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '>=',
  `conditionValue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Value to compare against',
  `accrualMultiplier` decimal(3,2) DEFAULT '1.00' COMMENT 'Multiplier for base accrual rate',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime DEFAULT NULL,
  `LastUpdateByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ruleID`),
  KEY `idx_policy_rules` (`policyID`,`Lapsed`),
  KEY `idx_rule_type` (`ruleType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Rules for complex accumulation policies';

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_applications`
--

DROP TABLE IF EXISTS `tija_leave_applications`;
CREATE TABLE IF NOT EXISTS `tija_leave_applications` (
  `leaveApplicationID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `leaveTypeID` int NOT NULL,
  `leavePeriodID` int NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `leaveStatusID` int NOT NULL DEFAULT '1',
  `employeeID` int NOT NULL,
  `leaveFiles` text,
  `leaveComments` text,
  `leaveEntitlementID` int NOT NULL,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `LastUpdate` datetime NOT NULL,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  `noOfDays` decimal(3,2) DEFAULT NULL,
  `emergencyContact` text COMMENT 'Emergency contact information for the leave period',
  `handoverNotes` text COMMENT 'Notes about work handover during leave',
  `createdBy` int DEFAULT NULL COMMENT 'User ID who created the application',
  `createdDate` datetime DEFAULT NULL COMMENT 'Date and time when the application was created',
  `modifiedBy` int DEFAULT NULL COMMENT 'User ID who last modified the application',
  `modifiedDate` datetime DEFAULT NULL COMMENT 'Date and time when the application was last modified',
  `halfDayLeave` enum('Y','N') NOT NULL DEFAULT 'N' COMMENT 'Whether this is a half day leave',
  `halfDayPeriod` varchar(20) DEFAULT NULL COMMENT 'Period for half day leave (AM/PM)',
  `dateApplied` datetime DEFAULT NULL COMMENT 'Date when the application was submitted',
  `appliedByID` int DEFAULT NULL COMMENT 'ID of the person who applied for leave',
  PRIMARY KEY (`leaveApplicationID`),
  KEY `idx_employee_date` (`employeeID`,`startDate`),
  KEY `idx_status_date` (`leaveStatusID`,`startDate`),
  KEY `idx_leave_type` (`leaveTypeID`),
  KEY `idx_created_by` (`createdBy`),
  KEY `idx_created_date` (`createdDate`),
  KEY `idx_modified_by` (`modifiedBy`),
  KEY `idx_modified_date` (`modifiedDate`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tija_leave_applications`
--

INSERT INTO `tija_leave_applications` (`leaveApplicationID`, `DateAdded`, `leaveTypeID`, `leavePeriodID`, `startDate`, `endDate`, `leaveStatusID`, `employeeID`, `leaveFiles`, `leaveComments`, `leaveEntitlementID`, `orgDataID`, `entityID`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`, `noOfDays`, `emergencyContact`, `handoverNotes`, `createdBy`, `createdDate`, `modifiedBy`, `modifiedDate`, `halfDayLeave`, `halfDayPeriod`, `dateApplied`, `appliedByID`) VALUES
(1, '2025-11-19 14:02:30', 1, 1, '2025-11-20', '2025-11-21', 6, 4, NULL, 'dasfsd fasdf asdf asd', 1, 1, 1, '2025-11-19 14:04:24', 48, 'N', 'N', 2.00, 'sdafdsf asdf asd', 'asdf asdf asdf asdf asd', NULL, NULL, NULL, NULL, 'N', '', '2025-11-19 14:02:30', 4),
(2, '2025-11-19 14:11:45', 1, 1, '2025-11-20', '2025-11-24', 3, 4, NULL, 'dfsag fasdfg', 1, 1, 1, '0000-00-00 00:00:00', 0, 'N', 'N', 3.00, 'sdfag asdgf', 'asdf asdfa', NULL, NULL, NULL, NULL, 'N', '', '2025-11-19 14:11:45', 4),
(3, '2025-11-19 14:21:43', 1, 1, '2025-11-20', '2025-11-21', 3, 4, NULL, 'dsfadfgsasdfa dsfsadf sad', 1, 1, 1, '0000-00-00 00:00:00', 0, 'N', 'N', 2.00, 'asdfasdff asdf asdf asdf asd', 'asdfsdfasdasd fasdf asdf asdf', NULL, NULL, NULL, NULL, 'N', '', '2025-11-19 14:21:43', 4),
(4, '2025-11-19 14:22:54', 6, 1, '2025-11-26', '2025-11-28', 3, 4, NULL, 'dsf asd fgasdf dsaf asdf', 6, 1, 1, '0000-00-00 00:00:00', 0, 'N', 'N', 3.00, 'sdf asdf asdf asdf', 'asd fasdf asdf asdf asdf', NULL, NULL, NULL, NULL, 'N', '', '2025-11-19 14:22:54', 4),
(5, '2025-11-19 14:40:04', 1, 1, '2025-11-26', '2025-11-28', 3, 4, NULL, 'dsf sa as asd fasd', 1, 1, 1, '0000-00-00 00:00:00', 0, 'N', 'N', 3.00, 'sdaf asdf asdf', 'asdf asdf asdf asdf asdf asdf asd', NULL, NULL, NULL, NULL, 'N', '', '2025-11-19 14:40:04', 4),
(6, '2025-11-19 14:45:53', 1, 1, '2025-11-20', '2025-11-25', 3, 4, NULL, 'arwst artart', 1, 1, 1, '0000-00-00 00:00:00', 0, 'N', 'N', 4.00, 'rewtqrwetr', 'rwetq rwetqer', NULL, NULL, NULL, NULL, 'N', '', '2025-11-19 14:45:53', 4),
(7, '2025-11-19 18:24:37', 1, 1, '2025-12-08', '2025-12-15', 3, 48, NULL, 'sdfasdfweawgasfdgasfd', 1, 1, 1, '0000-00-00 00:00:00', 0, 'N', 'N', 5.00, 'sdaf dsf sadf sadf asdf', 'sdf asdf sdf asd fasg fg dg', NULL, NULL, NULL, NULL, 'N', '', '2025-11-19 18:24:37', 48),
(8, '2025-11-19 18:26:55', 6, 1, '2025-12-02', '2025-12-05', 6, 4, NULL, 're qer erer gerg er er', 6, 1, 1, '2025-11-19 18:27:35', 48, 'N', 'N', 4.00, 'er ter ert ert er', 'er terwt ewr tewr twer ewrtert', NULL, NULL, NULL, NULL, 'N', '', '2025-11-19 18:26:55', 4);

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_approvals`
--

DROP TABLE IF EXISTS `tija_leave_approvals`;
CREATE TABLE IF NOT EXISTS `tija_leave_approvals` (
  `leaveApprovalID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `leaveApplicationID` int NOT NULL,
  `employeeID` int NOT NULL,
  `leaveTypeID` int NOT NULL,
  `leavePeriodID` int NOT NULL,
  `leaveApproverID` int NOT NULL,
  `leaveDate` date NOT NULL,
  `leaveStatus` enum('approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `leaveStatusID` int NOT NULL,
  `approversComments` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `LastUpdateByID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`leaveApprovalID`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `tija_leave_approvals`
--

INSERT INTO `tija_leave_approvals` (`leaveApprovalID`, `DateAdded`, `leaveApplicationID`, `employeeID`, `leaveTypeID`, `leavePeriodID`, `leaveApproverID`, `leaveDate`, `leaveStatus`, `leaveStatusID`, `approversComments`, `LastUpdateByID`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-19 17:04:24', 1, 4, 1, 1, 48, '2025-11-19', 'approved', 6, '', 48, '2025-11-19 14:04:24', 'N', 'N'),
(2, '2025-11-19 17:54:19', 6, 4, 1, 1, 49, '2025-11-19', '', 3, 'the user can go on leave', 49, '2025-11-19 14:54:19', 'N', 'N'),
(3, '2025-11-19 17:55:12', 6, 4, 1, 1, 49, '2025-11-19', '', 3, 'adfasdfasd sdf sadf sdaf sad', 49, '2025-11-19 14:55:12', 'N', 'N'),
(4, '2025-11-19 20:42:13', 6, 4, 1, 1, 49, '2025-11-19', '', 3, 'dsafdsfasdf', 49, '2025-11-19 17:42:13', 'N', 'N'),
(5, '2025-11-19 20:47:50', 6, 4, 1, 1, 49, '2025-11-19', '', 3, 'this is my final approval', 49, '2025-11-19 17:47:50', 'N', 'N'),
(6, '2025-11-19 20:49:04', 6, 4, 1, 1, 48, '2025-11-19', '', 3, 'the user may go on leave.', 48, '2025-11-19 17:49:04', 'N', 'N'),
(7, '2025-11-19 21:27:17', 8, 4, 6, 1, 49, '2025-11-19', '', 3, 'sda gs asdf asdf asdf', 49, '2025-11-19 18:27:17', 'N', 'N'),
(8, '2025-11-19 21:27:35', 8, 4, 6, 1, 48, '2025-11-19', 'approved', 6, 'you may proceed', 48, '2025-11-19 18:27:35', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_approval_actions`
--

DROP TABLE IF EXISTS `tija_leave_approval_actions`;
CREATE TABLE IF NOT EXISTS `tija_leave_approval_actions` (
  `actionID` int NOT NULL AUTO_INCREMENT,
  `instanceID` int NOT NULL,
  `stepID` int NOT NULL,
  `stepOrder` int NOT NULL,
  `approverID` int NOT NULL COMMENT 'User who took action',
  `approverUserID` int DEFAULT NULL,
  `action` enum('pending','approved','rejected','delegated','escalated','cancelled','info_requested') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `comments` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `delegatedTo` int DEFAULT NULL,
  `actionDate` datetime NOT NULL,
  `responseTime` int DEFAULT NULL COMMENT 'Minutes from notification to action',
  `ipAddress` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `userAgent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`actionID`),
  KEY `idx_instance` (`instanceID`),
  KEY `idx_approver` (`approverID`),
  KEY `idx_action` (`action`),
  KEY `idx_date` (`actionDate`),
  KEY `idx_action_pending` (`instanceID`,`action`,`actionDate`),
  KEY `idx_actions_instance_step_approver` (`instanceID`,`stepID`,`approverUserID`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Log of all approval actions taken';

--
-- Dumping data for table `tija_leave_approval_actions`
--

INSERT INTO `tija_leave_approval_actions` (`actionID`, `instanceID`, `stepID`, `stepOrder`, `approverID`, `approverUserID`, `action`, `comments`, `delegatedTo`, `actionDate`, `responseTime`, `ipAddress`, `userAgent`) VALUES
(1, 6, 4, 0, 49, 49, 'approved', 'this is my final approval', NULL, '2025-11-19 17:47:50', NULL, NULL, NULL),
(2, 6, 5, 0, 48, 48, 'approved', 'the user may go on leave.', NULL, '2025-11-19 17:49:04', NULL, NULL, NULL),
(3, 8, 4, 0, 49, 49, 'approved', 'sda gs asdf asdf asdf', NULL, '2025-11-19 18:27:17', NULL, NULL, NULL),
(4, 8, 5, 0, 48, 48, 'approved', 'you may proceed', NULL, '2025-11-19 18:27:35', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_approval_comments`
--

DROP TABLE IF EXISTS `tija_leave_approval_comments`;
CREATE TABLE IF NOT EXISTS `tija_leave_approval_comments` (
  `commentID` int NOT NULL AUTO_INCREMENT,
  `leaveApplicationID` int NOT NULL,
  `approverID` int DEFAULT NULL,
  `approverUserID` int DEFAULT NULL,
  `approvalLevel` varchar(50) DEFAULT NULL,
  `comment` text,
  `commentType` varchar(30) DEFAULT NULL,
  `commentDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `DateAdded` datetime DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` char(1) NOT NULL DEFAULT 'N',
  `Suspended` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`commentID`),
  KEY `idx_comments_application` (`leaveApplicationID`),
  KEY `idx_comments_approver` (`approverUserID`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tija_leave_approval_comments`
--

INSERT INTO `tija_leave_approval_comments` (`commentID`, `leaveApplicationID`, `approverID`, `approverUserID`, `approvalLevel`, `comment`, `commentType`, `commentDate`, `DateAdded`, `Lapsed`, `Suspended`) VALUES
(1, 6, 49, 49, 'intermediate_approver', 'the user can go on leave', NULL, '2025-11-19 14:54:19', '2025-11-19 14:54:19', 'N', 'N'),
(2, 6, 49, 49, 'intermediate_approver', 'adfasdfasd sdf sadf sdaf sad', NULL, '2025-11-19 14:55:12', '2025-11-19 14:55:12', 'N', 'N'),
(3, 6, 49, 49, 'intermediate_approver', 'dsafdsfasdf', NULL, '2025-11-19 17:42:13', '2025-11-19 17:42:13', 'N', 'N'),
(4, 6, 49, 49, 'intermediate_approver', 'this is my final approval', NULL, '2025-11-19 17:47:50', '2025-11-19 17:47:50', 'N', 'N'),
(5, 6, 48, 48, 'hr_manager', 'the user may go on leave.', NULL, '2025-11-19 17:49:04', '2025-11-19 17:49:04', 'N', 'N'),
(6, 8, 49, 49, 'intermediate_approver', 'sda gs asdf asdf asdf', NULL, '2025-11-19 18:27:17', '2025-11-19 18:27:17', 'N', 'N'),
(7, 8, 48, 48, 'hr_manager', 'you may proceed', NULL, '2025-11-19 18:27:35', '2025-11-19 18:27:35', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_approval_instances`
--

DROP TABLE IF EXISTS `tija_leave_approval_instances`;
CREATE TABLE IF NOT EXISTS `tija_leave_approval_instances` (
  `instanceID` int NOT NULL AUTO_INCREMENT,
  `leaveApplicationID` int NOT NULL,
  `policyID` int NOT NULL,
  `currentStepID` int DEFAULT NULL,
  `currentStepOrder` int DEFAULT '1',
  `workflowStatus` enum('pending','in_progress','approved','rejected','cancelled','escalated') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `startedAt` datetime NOT NULL,
  `completedAt` datetime DEFAULT NULL,
  `lastActionAt` datetime DEFAULT NULL,
  `lastActionBy` int DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`instanceID`),
  KEY `idx_application` (`leaveApplicationID`),
  KEY `idx_policy` (`policyID`),
  KEY `idx_status` (`workflowStatus`),
  KEY `idx_instance_status` (`workflowStatus`,`currentStepOrder`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Workflow instances for leave applications';

--
-- Dumping data for table `tija_leave_approval_instances`
--

INSERT INTO `tija_leave_approval_instances` (`instanceID`, `leaveApplicationID`, `policyID`, `currentStepID`, `currentStepOrder`, `workflowStatus`, `startedAt`, `completedAt`, `lastActionAt`, `lastActionBy`, `createdAt`) VALUES
(1, 1, 1, 5, 2, 'in_progress', '2025-11-19 13:14:37', NULL, '2025-11-19 14:04:24', 48, '2025-11-19 16:14:37'),
(2, 2, 1, 4, 1, 'pending', '2025-11-19 14:11:45', NULL, NULL, NULL, '2025-11-19 17:11:45'),
(3, 3, 1, 4, 1, 'pending', '2025-11-19 14:21:43', NULL, NULL, NULL, '2025-11-19 17:21:43'),
(4, 4, 1, 4, 1, 'pending', '2025-11-19 14:22:54', NULL, NULL, NULL, '2025-11-19 17:22:54'),
(5, 5, 1, 4, 1, 'pending', '2025-11-19 14:40:04', NULL, NULL, NULL, '2025-11-19 17:40:04'),
(6, 6, 1, 4, 1, 'pending', '2025-11-19 14:45:53', NULL, NULL, NULL, '2025-11-19 17:45:53'),
(7, 7, 1, 4, 1, 'pending', '2025-11-19 18:24:37', NULL, NULL, NULL, '2025-11-19 21:24:37'),
(8, 8, 1, 4, 1, '', '2025-11-19 18:26:55', '2025-11-19 18:27:35', '2025-11-19 18:27:35', 48, '2025-11-19 21:26:55');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_approval_policies`
--

DROP TABLE IF EXISTS `tija_leave_approval_policies`;
CREATE TABLE IF NOT EXISTS `tija_leave_approval_policies` (
  `policyID` int NOT NULL AUTO_INCREMENT,
  `entityID` int NOT NULL,
  `orgDataID` int NOT NULL,
  `policyName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `policyDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `approvalType` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'parallel',
  `isDefault` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `requireAllApprovals` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'If Y, all approvers must approve. If N, sequential approval',
  `allowDelegation` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `autoApproveThreshold` int DEFAULT NULL COMMENT 'Auto-approve if leave days <= this value',
  `createdBy` int NOT NULL,
  `createdAt` datetime NOT NULL,
  `updatedBy` int DEFAULT NULL,
  `updatedAt` datetime DEFAULT NULL,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`policyID`),
  KEY `idx_entity` (`entityID`),
  KEY `idx_orgdata` (`orgDataID`),
  KEY `idx_active` (`isActive`,`Suspended`,`Lapsed`),
  KEY `idx_policy_entity` (`entityID`,`isActive`),
  KEY `idx_policy_default` (`entityID`,`isDefault`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Leave approval workflow policies per entity';

--
-- Dumping data for table `tija_leave_approval_policies`
--

INSERT INTO `tija_leave_approval_policies` (`policyID`, `entityID`, `orgDataID`, `policyName`, `policyDescription`, `isActive`, `approvalType`, `isDefault`, `requireAllApprovals`, `allowDelegation`, `autoApproveThreshold`, `createdBy`, `createdAt`, `updatedBy`, `updatedAt`, `Suspended`, `Lapsed`) VALUES
(1, 1, 1, 'Direct Line Manager approval', 'This template is used for employees who need approval from their direct supervisor, project manager and finally the HR Manager', 'Y', 'parallel', 'Y', 'N', 'Y', 4, 4, '2025-10-22 08:28:42', 4, '2025-11-19 17:00:11', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_approval_steps`
--

DROP TABLE IF EXISTS `tija_leave_approval_steps`;
CREATE TABLE IF NOT EXISTS `tija_leave_approval_steps` (
  `stepID` int NOT NULL AUTO_INCREMENT,
  `policyID` int NOT NULL,
  `stepOrder` int NOT NULL COMMENT 'Order of approval (1, 2, 3...)',
  `stepName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `stepType` enum('supervisor','project_manager','hr_manager','hr_representative','department_head','custom_role','specific_user') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `stepDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `isRequired` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `approvalRequired` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all',
  `isConditional` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `conditionType` enum('days_threshold','leave_type','user_role','department','custom') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `conditionValue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'JSON string for condition parameters',
  `escalationDays` int DEFAULT NULL COMMENT 'Days before escalation if no action',
  `escalateToStepID` int DEFAULT NULL COMMENT 'Which step to escalate to',
  `notifyOnPending` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `notifyOnApprove` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `notifyOnReject` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime DEFAULT NULL,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`stepID`),
  KEY `idx_policy` (`policyID`),
  KEY `idx_order` (`stepOrder`),
  KEY `idx_step_policy_order` (`policyID`,`stepOrder`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Individual steps in approval workflow';

--
-- Dumping data for table `tija_leave_approval_steps`
--

INSERT INTO `tija_leave_approval_steps` (`stepID`, `policyID`, `stepOrder`, `stepName`, `stepType`, `stepDescription`, `isRequired`, `approvalRequired`, `isConditional`, `conditionType`, `conditionValue`, `escalationDays`, `escalateToStepID`, `notifyOnPending`, `notifyOnApprove`, `notifyOnReject`, `createdAt`, `updatedAt`, `Suspended`) VALUES
(4, 1, 1, 'Direct Supervisor', 'supervisor', 'Direct Supervisor Approval', 'Y', 'all', 'N', NULL, NULL, 3, NULL, 'Y', 'Y', 'Y', '2025-11-19 17:00:11', NULL, 'N'),
(5, 1, 2, 'HR Manager', 'hr_manager', 'HR Manager Approval', 'Y', 'all', 'N', NULL, NULL, 3, NULL, 'Y', 'Y', 'Y', '2025-11-19 17:00:11', NULL, 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_approval_step_approvers`
--

DROP TABLE IF EXISTS `tija_leave_approval_step_approvers`;
CREATE TABLE IF NOT EXISTS `tija_leave_approval_step_approvers` (
  `approverID` int NOT NULL AUTO_INCREMENT,
  `stepID` int NOT NULL,
  `approverType` enum('user','role','department') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `approverUserID` int DEFAULT NULL COMMENT 'If approverType = user',
  `approverRole` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'If approverType = role',
  `approverDepartment` int DEFAULT NULL COMMENT 'If approverType = department',
  `isBackup` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `notificationOrder` int DEFAULT '1' COMMENT 'Order for parallel approvers',
  `createdAt` datetime NOT NULL,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`approverID`),
  KEY `idx_step` (`stepID`),
  KEY `idx_user` (`approverUserID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Specific approvers for custom workflow steps';

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_audit_log`
--

DROP TABLE IF EXISTS `tija_leave_audit_log`;
CREATE TABLE IF NOT EXISTS `tija_leave_audit_log` (
  `auditID` int NOT NULL AUTO_INCREMENT,
  `entityType` enum('application','approval','clearance','entitlement','policy') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `entityID` int NOT NULL COMMENT 'ID of the entity being audited',
  `action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Action performed (CREATE, UPDATE, DELETE, APPROVE, etc.)',
  `oldValues` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT 'Previous values (JSON format)',
  `newValues` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin COMMENT 'New values (JSON format)',
  `performedByID` int NOT NULL COMMENT 'User who performed the action',
  `performedDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ipAddress` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'IP address of user',
  `userAgent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'User agent string',
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Reason for the action',
  PRIMARY KEY (`auditID`),
  KEY `idx_entity` (`entityType`,`entityID`),
  KEY `idx_performed_by` (`performedByID`),
  KEY `idx_performed_date` (`performedDate`),
  KEY `idx_action` (`action`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `tija_leave_audit_log`
--

INSERT INTO `tija_leave_audit_log` (`auditID`, `entityType`, `entityID`, `action`, `oldValues`, `newValues`, `performedByID`, `performedDate`, `ipAddress`, `userAgent`, `reason`) VALUES
(1, 'approval', 4, 'approved', '{\"leaveStatusID\":3}', '{\"leaveStatusID\":3}', 48, '2025-11-18 13:36:38', NULL, NULL, 'dfgfdsg afdsg'),
(2, 'approval', 4, 'approved', '{\"leaveStatusID\":3}', '{\"leaveStatusID\":3}', 3, '2025-11-18 13:37:19', NULL, NULL, NULL),
(3, 'approval', 1, 'approved', '{\"leaveStatusID\":3}', '{\"leaveStatusID\":3}', 3, '2025-11-18 13:39:27', NULL, NULL, 'fdgs fgs df'),
(4, 'approval', 2, 'approved', '{\"leaveStatusID\":3}', '{\"leaveStatusID\":3}', 3, '2025-11-18 13:39:44', NULL, NULL, 'dgfhsdhs'),
(5, 'approval', 3, 'approved', '{\"leaveStatusID\":3}', '{\"leaveStatusID\":3}', 3, '2025-11-18 13:39:47', NULL, NULL, 'sdfghs dfghsd'),
(6, 'approval', 1, 'approved', '{\"leaveStatusID\":3}', '{\"leaveStatusID\":6}', 48, '2025-11-19 17:04:24', NULL, NULL, NULL),
(7, 'approval', 6, 'approved_step', '{\"leaveStatusID\":3}', '{\"leaveStatusID\":3}', 49, '2025-11-19 17:54:19', NULL, NULL, 'the user can go on leave'),
(8, 'approval', 6, 'approved_step', '{\"leaveStatusID\":3}', '{\"leaveStatusID\":3}', 49, '2025-11-19 17:55:12', NULL, NULL, 'adfasdfasd sdf sadf sdaf sad'),
(9, 'approval', 6, 'approved_step', '{\"leaveStatusID\":3}', '{\"leaveStatusID\":3}', 49, '2025-11-19 20:42:13', NULL, NULL, 'dsafdsfasdf'),
(10, 'approval', 6, 'approved_step', '{\"leaveStatusID\":3}', '{\"leaveStatusID\":3}', 49, '2025-11-19 20:47:50', NULL, NULL, 'this is my final approval'),
(11, 'approval', 6, 'approved', '{\"leaveStatusID\":3}', '{\"leaveStatusID\":3}', 48, '2025-11-19 20:49:04', NULL, NULL, 'the user may go on leave.'),
(12, 'approval', 8, 'approved_step', '{\"leaveStatusID\":3}', '{\"leaveStatusID\":3}', 49, '2025-11-19 21:27:17', NULL, NULL, 'sda gs asdf asdf asdf'),
(13, 'approval', 8, 'approved', '{\"leaveStatusID\":3}', '{\"leaveStatusID\":6}', 48, '2025-11-19 21:27:35', NULL, NULL, 'you may proceed');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_blackout_periods`
--

DROP TABLE IF EXISTS `tija_leave_blackout_periods`;
CREATE TABLE IF NOT EXISTS `tija_leave_blackout_periods` (
  `blackoutID` int NOT NULL AUTO_INCREMENT,
  `entityID` int NOT NULL COMMENT 'Entity this blackout applies to',
  `blackoutName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Name of the blackout period',
  `startDate` date NOT NULL COMMENT 'Start date of blackout period',
  `endDate` date NOT NULL COMMENT 'End date of blackout period',
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Reason for blackout period',
  `applicableLeaveTypes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'JSON array of leave type IDs this applies to (null = all types)',
  `severity` enum('Warning','Restriction','Prohibition') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Restriction',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime DEFAULT NULL,
  `LastUpdateByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`blackoutID`),
  KEY `idx_entity` (`entityID`),
  KEY `idx_date_range` (`startDate`,`endDate`),
  KEY `idx_severity` (`severity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Blackout periods when leave applications are restricted';

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_entitlement`
--

DROP TABLE IF EXISTS `tija_leave_entitlement`;
CREATE TABLE IF NOT EXISTS `tija_leave_entitlement` (
  `leaveEntitlementID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `leaveTypeID` int NOT NULL,
  `entitlement` decimal(4,0) NOT NULL,
  `maxDaysPerApplication` int DEFAULT NULL COMMENT 'Maximum days that can be applied for in a single application (NULL = unlimited)',
  `minNoticeDays` int NOT NULL,
  `entityID` int NOT NULL,
  `orgDataID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`leaveEntitlementID`),
  KEY `idx_entity_type` (`entityID`,`leaveTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tija_leave_entitlement`
--

INSERT INTO `tija_leave_entitlement` (`leaveEntitlementID`, `DateAdded`, `leaveTypeID`, `entitlement`, `maxDaysPerApplication`, `minNoticeDays`, `entityID`, `orgDataID`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-03-17 13:41:39', 1, 21, 10, 5, 1, 0, '2025-11-09 16:46:18', 4, 'N', 'N'),
(2, '2025-03-17 13:53:03', 2, 14, 14, 0, 1, 0, '2025-11-09 16:46:05', 4, 'N', 'N'),
(3, '2025-03-17 13:53:29', 3, 63, 63, 0, 1, 0, '2025-11-09 16:45:58', 4, 'N', 'N'),
(4, '2025-03-17 13:53:40', 4, 14, 14, 0, 1, 0, '2025-11-09 16:45:43', 4, 'N', 'N'),
(5, '2025-03-17 13:53:50', 5, 10, 5, 2, 1, 0, '2025-11-09 16:44:19', 4, 'N', 'N'),
(6, '2025-11-19 15:42:03', 6, 15, 10, 7, 1, 0, '2025-11-19 15:42:03', 4, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_periods`
--

DROP TABLE IF EXISTS `tija_leave_periods`;
CREATE TABLE IF NOT EXISTS `tija_leave_periods` (
  `leavePeriodID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `leavePeriodName` varchar(255) NOT NULL,
  `leavePeriodStartDate` date NOT NULL,
  `leavePeriodEndDate` date NOT NULL,
  `orgDataID` int NOT NULL,
  `entityID` int NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`leavePeriodID`),
  KEY `idx_entity_period` (`entityID`,`leavePeriodStartDate`,`leavePeriodEndDate`),
  KEY `idx_org_entity` (`orgDataID`,`entityID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tija_leave_periods`
--

INSERT INTO `tija_leave_periods` (`leavePeriodID`, `DateAdded`, `leavePeriodName`, `leavePeriodStartDate`, `leavePeriodEndDate`, `orgDataID`, `entityID`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-08 15:17:17', '2025 Leave Period', '2025-01-01', '2025-12-31', 0, 1, '2025-11-08 15:17:17', 4, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_project_clearances`
--

DROP TABLE IF EXISTS `tija_leave_project_clearances`;
CREATE TABLE IF NOT EXISTS `tija_leave_project_clearances` (
  `clearanceID` int NOT NULL AUTO_INCREMENT,
  `leaveApplicationID` int NOT NULL COMMENT 'Reference to leave application',
  `projectID` int NOT NULL COMMENT 'Project requiring clearance',
  `projectManagerID` int NOT NULL COMMENT 'Project manager who needs to approve',
  `clearanceStatus` enum('Pending','Approved','Rejected','Not Required') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `clearanceDate` datetime DEFAULT NULL COMMENT 'Date when clearance was given',
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Comments from project manager',
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdate` datetime DEFAULT NULL,
  `LastUpdateByID` int DEFAULT NULL,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  PRIMARY KEY (`clearanceID`),
  KEY `idx_leave_application` (`leaveApplicationID`),
  KEY `idx_project` (`projectID`),
  KEY `idx_project_manager` (`projectManagerID`),
  KEY `idx_clearance_status` (`clearanceStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Project manager clearances for leave applications';

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_status`
--

DROP TABLE IF EXISTS `tija_leave_status`;
CREATE TABLE IF NOT EXISTS `tija_leave_status` (
  `leaveStatusID` int NOT NULL AUTO_INCREMENT,
  `leaveStatusCode` varchar(80) NOT NULL,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `leaveStatusName` varchar(255) NOT NULL,
  `leaveStatusDescription` text NOT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`leaveStatusID`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tija_leave_status`
--

INSERT INTO `tija_leave_status` (`leaveStatusID`, `leaveStatusCode`, `DateAdded`, `leaveStatusName`, `leaveStatusDescription`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, 'scheduled', '2025-03-16 19:14:46', 'Scheduled', 'Scheduled Leave', '2025-03-16 19:14:46', 0, 'N', 'N'),
(2, 'taken', '2025-03-16 19:27:21', 'Taken', 'Leave already taken by employee', '2025-03-16 19:27:21', 0, 'N', 'N'),
(3, 'pending', '2025-03-16 19:40:10', 'Pending Approval', 'Leave requests pending approval', '2025-03-16 19:40:10', 0, 'N', 'N'),
(4, 'rejected', '2025-03-16 19:43:21', 'Rejected', 'leave requests rejected by supervisor', '2025-03-16 19:43:21', 0, 'N', 'N'),
(5, 'cancelled', '2025-03-16 19:44:23', 'Cancelled', 'Leave applications cancelled by employee', '2025-03-16 19:44:23', 0, 'N', 'N'),
(6, 'approved', '2025-05-28 17:40:00', 'approved', 'Approved Leave', '2025-05-28 17:40:00', 0, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_types`
--

DROP TABLE IF EXISTS `tija_leave_types`;
CREATE TABLE IF NOT EXISTS `tija_leave_types` (
  `leaveTypeID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `leaveTypeCode` varchar(255) NOT NULL,
  `leaveTypeName` varchar(255) NOT NULL,
  `leaveTypeDescription` text NOT NULL,
  `leaveSegment` enum('male','female','specialNeeds') DEFAULT NULL,
  `LastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastUpdateByID` int NOT NULL,
  `Lapsed` enum('Y','N') NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`leaveTypeID`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tija_leave_types`
--

INSERT INTO `tija_leave_types` (`leaveTypeID`, `DateAdded`, `leaveTypeCode`, `leaveTypeName`, `leaveTypeDescription`, `leaveSegment`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-03-15 23:02:11', 'ANN_R2HR5', 'Annual Leave', 'Annual Leave', NULL, '2025-03-15 23:02:11', 11, 'N', 'N'),
(2, '2025-03-17 13:05:48', 'COM_78QN5', 'Compassionate Leave', 'Compassionate Leave', NULL, '2025-03-17 13:05:48', 11, 'N', 'N'),
(3, '2025-03-17 13:08:01', 'MAT_6QPM5', 'Maternity Leave', 'Maternity Leave', 'female', '2025-03-17 13:08:01', 11, 'N', 'N'),
(4, '2025-03-17 13:08:19', 'PAT_0LD6W', 'Paternity Leave', 'Paternity Leave', 'male', '2025-03-17 13:08:19', 11, 'N', 'N'),
(5, '2025-03-17 13:08:35', 'SIC_D9NRA', 'Sick Leave', 'Sick Leave', NULL, '2025-03-17 13:08:35', 11, 'N', 'N'),
(6, '2025-11-19 15:21:58', 'STUDY_LV', 'Study Leave', 'Study Leave', NULL, '2025-11-19 15:21:58', 0, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_workflow_templates`
--

DROP TABLE IF EXISTS `tija_leave_workflow_templates`;
CREATE TABLE IF NOT EXISTS `tija_leave_workflow_templates` (
  `templateID` int NOT NULL AUTO_INCREMENT,
  `templateName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `templateDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sourcePolicyID` int DEFAULT NULL COMMENT 'Original policy this was created from',
  `isSystemTemplate` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `isPublic` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N' COMMENT 'If Y, visible to all entities',
  `createdBy` int NOT NULL,
  `createdForEntityID` int DEFAULT NULL,
  `usageCount` int DEFAULT '0',
  `createdAt` datetime NOT NULL,
  `updatedAt` datetime DEFAULT NULL,
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`templateID`),
  KEY `idx_public` (`isPublic`,`Suspended`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Reusable workflow templates';

--
-- Dumping data for table `tija_leave_workflow_templates`
--

INSERT INTO `tija_leave_workflow_templates` (`templateID`, `templateName`, `templateDescription`, `sourcePolicyID`, `isSystemTemplate`, `isPublic`, `createdBy`, `createdForEntityID`, `usageCount`, `createdAt`, `updatedAt`, `Suspended`) VALUES
(1, 'Standard 3-Level Approval', 'Direct Supervisor → Department Head → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:45:00', NULL, 'N'),
(2, 'Simple 2-Level Approval', 'Direct Supervisor → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:45:00', NULL, 'N'),
(3, 'Project-Based Approval', 'Direct Supervisor → Project Manager → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:45:00', NULL, 'N'),
(4, 'HR-Only Approval', 'HR Manager only (for HR department)', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:45:00', NULL, 'N'),
(5, 'Standard 3-Level Approval', 'Direct Supervisor → Department Head → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:48:14', NULL, 'N'),
(6, 'Simple 2-Level Approval', 'Direct Supervisor → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:48:14', NULL, 'N'),
(7, 'Project-Based Approval', 'Direct Supervisor → Project Manager → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:48:14', NULL, 'N'),
(8, 'Direct Line approval', 'Direct reporting line approval workflow', NULL, 'Y', 'Y', 1, NULL, 1, '2025-10-21 15:48:14', '2025-11-19 16:41:12', 'N'),
(9, 'Standard 3-Level Approval', 'Direct Supervisor → Department Head → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:49:33', NULL, 'N'),
(10, 'Simple 2-Level Approval', 'Direct Supervisor → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:49:33', NULL, 'N'),
(11, 'Project-Based Approval', 'Direct Supervisor → Project Manager → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:49:33', NULL, 'N'),
(12, 'HR-Only Approval', 'HR Manager only (for HR department)', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:49:33', NULL, 'N'),
(13, 'Standard 3-Level Approval', 'Direct Supervisor → Department Head → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:52:37', NULL, 'N'),
(14, 'Simple 2-Level Approval', 'Direct Supervisor → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:52:37', NULL, 'N'),
(15, 'Project-Based Approval', 'Direct Supervisor → Project Manager → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:52:37', NULL, 'N'),
(16, 'HR-Only Approval', 'HR Manager only (for HR department)', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:52:37', NULL, 'N'),
(17, 'Standard 3-Level Approval', 'Direct Supervisor → Department Head → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:52:56', NULL, 'N'),
(18, 'Simple 2-Level Approval', 'Direct Supervisor → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:52:56', NULL, 'N'),
(19, 'Project-Based Approval', 'Direct Supervisor → Project Manager → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:52:56', NULL, 'N'),
(20, 'HR-Only Approval', 'HR Manager only (for HR department)', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:52:56', NULL, 'N'),
(21, 'Standard 3-Level Approval', 'Direct Supervisor → Department Head → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:54:04', NULL, 'N'),
(22, 'Simple 2-Level Approval', 'Direct Supervisor → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:54:04', NULL, 'N'),
(23, 'Project-Based Approval', 'Direct Supervisor → Project Manager → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:54:04', NULL, 'N'),
(24, 'HR-Only Approval', 'HR Manager only (for HR department)', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:54:04', NULL, 'N'),
(25, 'Standard 3-Level Approval', 'Direct Supervisor → Department Head → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:56:07', NULL, 'N'),
(26, 'Simple 2-Level Approval', 'Direct Supervisor → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:56:07', NULL, 'N'),
(27, 'Project-Based Approval', 'Direct Supervisor → Project Manager → HR Manager', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:56:07', NULL, 'N'),
(28, 'HR-Only Approval', 'HR Manager only (for HR department)', NULL, 'Y', 'Y', 1, NULL, 0, '2025-10-21 15:56:07', NULL, 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_leave_workflow_template_steps`
--

DROP TABLE IF EXISTS `tija_leave_workflow_template_steps`;
CREATE TABLE IF NOT EXISTS `tija_leave_workflow_template_steps` (
  `templateStepID` int NOT NULL AUTO_INCREMENT,
  `templateID` int NOT NULL,
  `stepOrder` int NOT NULL,
  `stepName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `stepType` enum('supervisor','project_manager','hr_manager','hr_representative','department_head','custom_role','specific_user') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `stepDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `isRequired` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `isConditional` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `conditionType` enum('days_threshold','leave_type','user_role','department','custom') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `conditionValue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `escalationDays` int DEFAULT NULL,
  `notifySettings` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'JSON for notification settings',
  PRIMARY KEY (`templateStepID`),
  KEY `idx_template` (`templateID`)
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Steps in workflow templates';

--
-- Dumping data for table `tija_leave_workflow_template_steps`
--

INSERT INTO `tija_leave_workflow_template_steps` (`templateStepID`, `templateID`, `stepOrder`, `stepName`, `stepType`, `stepDescription`, `isRequired`, `isConditional`, `conditionType`, `conditionValue`, `escalationDays`, `notifySettings`) VALUES
(1, 1, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(2, 1, 2, 'Department Head Approval', 'department_head', 'Approval from department head', 'Y', 'N', NULL, NULL, 2, NULL),
(3, 1, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(4, 2, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(5, 2, 2, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(6, 3, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(7, 3, 2, 'Project Manager Approval', 'project_manager', 'Approval from project manager where user has active tasks', 'Y', 'N', NULL, NULL, 2, NULL),
(8, 3, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(9, 4, 1, 'HR Manager Approval', 'hr_manager', 'HR Manager approval only', 'Y', 'N', NULL, NULL, 2, NULL),
(10, 1, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(11, 1, 2, 'Department Head Approval', 'department_head', 'Approval from department head', 'Y', 'N', NULL, NULL, 2, NULL),
(12, 1, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(13, 2, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(14, 2, 2, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(15, 3, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(16, 3, 2, 'Project Manager Approval', 'project_manager', 'Approval from project manager where user has active tasks', 'Y', 'N', NULL, NULL, 2, NULL),
(17, 3, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(18, 4, 1, 'HR Manager Approval', 'hr_manager', 'HR Manager approval only', 'Y', 'N', NULL, NULL, 2, NULL),
(19, 1, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(20, 1, 2, 'Department Head Approval', 'department_head', 'Approval from department head', 'Y', 'N', NULL, NULL, 2, NULL),
(21, 1, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(22, 2, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(23, 2, 2, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(24, 3, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(25, 3, 2, 'Project Manager Approval', 'project_manager', 'Approval from project manager where user has active tasks', 'Y', 'N', NULL, NULL, 2, NULL),
(26, 3, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(27, 4, 1, 'HR Manager Approval', 'hr_manager', 'HR Manager approval only', 'Y', 'N', NULL, NULL, 2, NULL),
(28, 1, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(29, 1, 2, 'Department Head Approval', 'department_head', 'Approval from department head', 'Y', 'N', NULL, NULL, 2, NULL),
(30, 1, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(31, 2, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(32, 2, 2, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(33, 3, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(34, 3, 2, 'Project Manager Approval', 'project_manager', 'Approval from project manager where user has active tasks', 'Y', 'N', NULL, NULL, 2, NULL),
(35, 3, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(36, 4, 1, 'HR Manager Approval', 'hr_manager', 'HR Manager approval only', 'Y', 'N', NULL, NULL, 2, NULL),
(37, 1, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(38, 1, 2, 'Department Head Approval', 'department_head', 'Approval from department head', 'Y', 'N', NULL, NULL, 2, NULL),
(39, 1, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(40, 2, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(41, 2, 2, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(42, 3, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(43, 3, 2, 'Project Manager Approval', 'project_manager', 'Approval from project manager where user has active tasks', 'Y', 'N', NULL, NULL, 2, NULL),
(44, 3, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(45, 4, 1, 'HR Manager Approval', 'hr_manager', 'HR Manager approval only', 'Y', 'N', NULL, NULL, 2, NULL),
(46, 1, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(47, 1, 2, 'Department Head Approval', 'department_head', 'Approval from department head', 'Y', 'N', NULL, NULL, 2, NULL),
(48, 1, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(49, 2, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(50, 2, 2, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(51, 3, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(52, 3, 2, 'Project Manager Approval', 'project_manager', 'Approval from project manager where user has active tasks', 'Y', 'N', NULL, NULL, 2, NULL),
(53, 3, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(54, 4, 1, 'HR Manager Approval', 'hr_manager', 'HR Manager approval only', 'Y', 'N', NULL, NULL, 2, NULL),
(55, 1, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(56, 1, 2, 'Department Head Approval', 'department_head', 'Approval from department head', 'Y', 'N', NULL, NULL, 2, NULL),
(57, 1, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(58, 2, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(59, 2, 2, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(60, 3, 1, 'Direct Supervisor Approval', 'supervisor', 'Approval from employee\'s direct reporting supervisor', 'Y', 'N', NULL, NULL, 3, NULL),
(61, 3, 2, 'Project Manager Approval', 'project_manager', 'Approval from project manager where user has active tasks', 'Y', 'N', NULL, NULL, 2, NULL),
(62, 3, 3, 'HR Manager Approval', 'hr_manager', 'Final approval from HR Manager', 'Y', 'N', NULL, NULL, 2, NULL),
(63, 4, 1, 'HR Manager Approval', 'hr_manager', 'HR Manager approval only', 'Y', 'N', NULL, NULL, 2, NULL),
(71, 8, 1, 'Direct Supervisor', 'supervisor', 'Direct Supervisor Approval', 'Y', 'N', NULL, NULL, 3, NULL),
(72, 8, 2, 'HR Manager', 'hr_manager', 'HR Manager Approval', 'Y', 'N', NULL, NULL, 3, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tija_notifications`
--

DROP TABLE IF EXISTS `tija_notifications`;
CREATE TABLE IF NOT EXISTS `tija_notifications` (
  `notificationID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `employeeID` int NOT NULL,
  `approverID` int NOT NULL,
  `originatorUserID` int NOT NULL,
  `targetUserID` int NOT NULL,
  `segmentType` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'general',
  `segmentID` int DEFAULT NULL,
  `notificationNotes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `notificationType` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `emailed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `notificationText` text CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `notificationStatus` enum('read','unread') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'unread',
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Lapsed` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  `Suspended` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT 'N',
  PRIMARY KEY (`notificationID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `tija_notifications_enhanced`
--

DROP TABLE IF EXISTS `tija_notifications_enhanced`;
CREATE TABLE IF NOT EXISTS `tija_notifications_enhanced` (
  `notificationID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `eventID` int NOT NULL,
  `userID` int NOT NULL,
  `originatorUserID` int DEFAULT NULL,
  `entityID` int DEFAULT NULL,
  `orgDataID` int DEFAULT NULL,
  `segmentType` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `segmentID` int DEFAULT NULL,
  `notificationTitle` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notificationBody` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notificationData` json DEFAULT NULL,
  `notificationLink` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notificationIcon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ri-notification-line',
  `priority` enum('low','medium','high','critical') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `status` enum('unread','read','archived','deleted') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'unread',
  `readAt` datetime DEFAULT NULL,
  `archivedAt` datetime DEFAULT NULL,
  `expiresAt` datetime DEFAULT NULL,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`notificationID`),
  KEY `idx_user` (`userID`,`status`),
  KEY `idx_event` (`eventID`),
  KEY `idx_originator` (`originatorUserID`),
  KEY `idx_segment` (`segmentType`,`segmentID`),
  KEY `idx_date` (`DateAdded`),
  KEY `idx_entity` (`entityID`,`orgDataID`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tija_notifications_enhanced`
--

INSERT INTO `tija_notifications_enhanced` (`notificationID`, `DateAdded`, `eventID`, `userID`, `originatorUserID`, `entityID`, `orgDataID`, `segmentType`, `segmentID`, `notificationTitle`, `notificationBody`, `notificationData`, `notificationLink`, `notificationIcon`, `priority`, `status`, `readAt`, `archivedAt`, `expiresAt`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-11-19 14:04:24', 1, 4, 48, 1, 1, 'leave_application', 1, 'Leave Application Submitted', '<strong>Felix Mauncho</strong> has submitted a leave application for <strong>Annual Leave</strong> from <strong>Nov 20, 2025</strong> to <strong>Nov 21, 2025</strong> (2.00 days).', '{\"end_date\": \"Nov 21, 2025\", \"leave_type\": \"Annual Leave\", \"start_date\": \"Nov 20, 2025\", \"total_days\": \"2.00\", \"employee_id\": 4, \"employee_name\": \"Felix Mauncho\", \"application_id\": \"1\", \"application_link\": \"?s=user&ss=leave&p=my_applications&id=1\"}', '?s=user&ss=leave&p=my_applications&id=1', 'ri-calendar-event-line', 'medium', 'read', '2025-11-19 14:11:00', NULL, NULL, '2025-11-19 14:11:00', 'N', 'N'),
(3, '2025-11-19 14:45:53', 2, 49, 4, 1, 1, 'leave_application', 6, 'Leave Application Awaiting Your Approval', '<strong>Felix Mauncho</strong> has requested <strong>Annual Leave</strong> leave from <strong>Nov 20, 2025</strong> to <strong>Nov 25, 2025</strong>. This application requires your approval.', '{\"end_date\": \"Nov 25, 2025\", \"step_name\": \"Direct Supervisor\", \"leave_type\": \"Annual Leave\", \"start_date\": \"Nov 20, 2025\", \"total_days\": 4, \"employee_id\": \"4\", \"approver_name\": \"John Doe\", \"employee_name\": \"Felix Mauncho\", \"is_final_step\": false, \"application_id\": \"6\", \"approval_level\": 1}', '?s=user&ss=leave&p=pending_approvals&id=6', 'ri-calendar-event-line', 'high', 'read', '2025-11-19 14:46:34', NULL, NULL, '2025-11-19 14:46:34', 'N', 'N'),
(4, '2025-11-19 14:45:53', 2, 48, 4, 1, 1, 'leave_application', 6, 'Leave Application Awaiting Your Approval', '<strong>Felix Mauncho</strong> has requested <strong>Annual Leave</strong> leave from <strong>Nov 20, 2025</strong> to <strong>Nov 25, 2025</strong>. This application requires your approval.', '{\"end_date\": \"Nov 25, 2025\", \"step_name\": \"HR Manager\", \"leave_type\": \"Annual Leave\", \"start_date\": \"Nov 20, 2025\", \"total_days\": 4, \"employee_id\": \"4\", \"approver_name\": \"Julius Macharia\", \"employee_name\": \"Felix Mauncho\", \"is_final_step\": true, \"application_id\": \"6\", \"approval_level\": 2}', '?s=user&ss=leave&p=pending_approvals&id=6', 'ri-calendar-event-line', 'high', 'read', '2025-11-19 14:46:10', NULL, NULL, '2025-11-19 14:46:10', 'N', 'N'),
(5, '2025-11-19 14:45:53', 1, 4, 4, 1, 1, 'leave_application', 6, 'Leave Application Submitted', '<strong>Felix Mauncho</strong> has submitted a leave application for <strong>Annual Leave</strong> from <strong>Nov 20, 2025</strong> to <strong>Nov 25, 2025</strong> (4.00 days).', '{\"end_date\": \"Nov 25, 2025\", \"leave_type\": \"Annual Leave\", \"start_date\": \"Nov 20, 2025\", \"total_days\": \"4.00\", \"employee_id\": 4, \"leave_reason\": \"arwst artart\", \"employee_name\": \"Felix Mauncho\", \"application_id\": \"6\", \"application_link\": \"?s=user&ss=leave&p=my_applications&id=6\"}', '?s=user&ss=leave&p=my_applications&id=6', 'ri-calendar-event-line', 'medium', 'unread', NULL, NULL, NULL, '2025-11-19 14:45:53', 'N', 'N'),
(6, '2025-11-19 14:54:19', 1, 4, 49, 1, 1, 'leave_application', 6, 'Leave Application Submitted', '<strong>Felix Mauncho</strong> has submitted a leave application for <strong>Annual Leave</strong> from <strong>Nov 20, 2025</strong> to <strong>Nov 25, 2025</strong> (4.00 days).', '{\"end_date\": \"Nov 25, 2025\", \"leave_type\": \"Annual Leave\", \"start_date\": \"Nov 20, 2025\", \"total_days\": \"4.00\", \"employee_id\": 4, \"employee_name\": \"Felix Mauncho\", \"application_id\": 6, \"application_link\": \"?s=user&ss=leave&p=my_applications&id=6\"}', '?s=user&ss=leave&p=my_applications&id=6', 'ri-calendar-event-line', 'medium', 'unread', NULL, NULL, NULL, '2025-11-19 14:54:19', 'N', 'N'),
(7, '2025-11-19 14:55:12', 1, 4, 49, 1, 1, 'leave_application', 6, 'Leave Application Submitted', '<strong>Felix Mauncho</strong> has submitted a leave application for <strong>Annual Leave</strong> from <strong>Nov 20, 2025</strong> to <strong>Nov 25, 2025</strong> (4.00 days).', '{\"end_date\": \"Nov 25, 2025\", \"leave_type\": \"Annual Leave\", \"start_date\": \"Nov 20, 2025\", \"total_days\": \"4.00\", \"employee_id\": 4, \"employee_name\": \"Felix Mauncho\", \"application_id\": 6, \"application_link\": \"?s=user&ss=leave&p=my_applications&id=6\"}', '?s=user&ss=leave&p=my_applications&id=6', 'ri-calendar-event-line', 'medium', 'unread', NULL, NULL, NULL, '2025-11-19 14:55:12', 'N', 'N'),
(8, '2025-11-19 17:42:13', 1, 4, 49, 1, 1, 'leave_application', 6, 'Leave Application Submitted', '<strong>Felix Mauncho</strong> has submitted a leave application for <strong>Annual Leave</strong> from <strong>Nov 20, 2025</strong> to <strong>Nov 25, 2025</strong> (4.00 days).', '{\"end_date\": \"Nov 25, 2025\", \"leave_type\": \"Annual Leave\", \"start_date\": \"Nov 20, 2025\", \"total_days\": \"4.00\", \"employee_id\": 4, \"employee_name\": \"Felix Mauncho\", \"application_id\": 6, \"application_link\": \"?s=user&ss=leave&p=my_applications&id=6\"}', '?s=user&ss=leave&p=my_applications&id=6', 'ri-calendar-event-line', 'medium', 'unread', NULL, NULL, NULL, '2025-11-19 17:42:13', 'N', 'N'),
(9, '2025-11-19 17:47:50', 1, 4, 49, 1, 1, 'leave_application', 6, 'Leave Application Submitted', '<strong>Felix Mauncho</strong> has submitted a leave application for <strong>Annual Leave</strong> from <strong>Nov 20, 2025</strong> to <strong>Nov 25, 2025</strong> (4.00 days).', '{\"end_date\": \"Nov 25, 2025\", \"leave_type\": \"Annual Leave\", \"start_date\": \"Nov 20, 2025\", \"total_days\": \"4.00\", \"employee_id\": 4, \"employee_name\": \"Felix Mauncho\", \"application_id\": 6, \"application_link\": \"?s=user&ss=leave&p=my_applications&id=6\"}', '?s=user&ss=leave&p=my_applications&id=6', 'ri-calendar-event-line', 'medium', 'read', '2025-11-19 18:25:41', NULL, NULL, '2025-11-19 18:25:41', 'N', 'N'),
(10, '2025-11-19 17:49:04', 3, 4, 48, 1, 1, 'leave_application', 6, 'Your Leave Application Has Been Approved', 'Great news! Your <strong>Annual Leave</strong> leave application from <strong>Nov 20, 2025</strong> to <strong>Nov 25, 2025</strong> has been <span style=\"color: #28a745;\">approved</span> by <strong>Julius Macharia</strong>.', '{\"end_date\": \"Nov 25, 2025\", \"leave_type\": \"Annual Leave\", \"start_date\": \"Nov 20, 2025\", \"total_days\": \"4.00\", \"employee_id\": 4, \"approver_name\": \"Julius Macharia\", \"employee_name\": \"Felix Mauncho\", \"application_id\": 6, \"application_link\": \"?s=user&ss=leave&p=my_applications&id=6\", \"approver_comments\": \"the user may go on leave.\"}', '?s=user&ss=leave&p=my_applications&id=6', 'ri-calendar-event-line', 'high', 'read', '2025-11-19 18:25:30', NULL, NULL, '2025-11-19 18:25:30', 'N', 'N'),
(11, '2025-11-19 18:24:37', 2, 9, 48, 1, 1, 'leave_application', 7, 'Leave Application Awaiting Your Approval', '<strong>Julius Macharia</strong> has requested <strong>Annual Leave</strong> leave from <strong>Dec 8, 2025</strong> to <strong>Dec 15, 2025</strong>. This application requires your approval.', '{\"end_date\": \"Dec 15, 2025\", \"step_name\": \"Direct Supervisor\", \"leave_type\": \"Annual Leave\", \"start_date\": \"Dec 8, 2025\", \"total_days\": 5, \"employee_id\": \"48\", \"approver_name\": \"Gerhard Uduny\", \"employee_name\": \"Julius Macharia\", \"is_final_step\": false, \"application_id\": \"7\", \"approval_level\": 1}', '?s=user&ss=leave&p=pending_approvals&id=7', 'ri-calendar-event-line', 'high', 'unread', NULL, NULL, NULL, '2025-11-19 18:24:37', 'N', 'N'),
(12, '2025-11-19 18:24:37', 2, 48, 48, 1, 1, 'leave_application', 7, 'Leave Application Awaiting Your Approval', '<strong>Julius Macharia</strong> has requested <strong>Annual Leave</strong> leave from <strong>Dec 8, 2025</strong> to <strong>Dec 15, 2025</strong>. This application requires your approval.', '{\"end_date\": \"Dec 15, 2025\", \"step_name\": \"HR Manager\", \"leave_type\": \"Annual Leave\", \"start_date\": \"Dec 8, 2025\", \"total_days\": 5, \"employee_id\": \"48\", \"approver_name\": \"Julius Macharia\", \"employee_name\": \"Julius Macharia\", \"is_final_step\": true, \"application_id\": \"7\", \"approval_level\": 2}', '?s=user&ss=leave&p=pending_approvals&id=7', 'ri-calendar-event-line', 'high', 'unread', NULL, NULL, NULL, '2025-11-19 18:24:37', 'N', 'N'),
(13, '2025-11-19 18:24:37', 1, 48, 48, 1, 1, 'leave_application', 7, 'Leave Application Submitted', '<strong>Julius Macharia</strong> has submitted a leave application for <strong>Annual Leave</strong> from <strong>Dec 8, 2025</strong> to <strong>Dec 15, 2025</strong> (5.00 days).', '{\"end_date\": \"Dec 15, 2025\", \"leave_type\": \"Annual Leave\", \"start_date\": \"Dec 8, 2025\", \"total_days\": \"5.00\", \"employee_id\": 48, \"leave_reason\": \"sdfasdfweawgasfdgasfd\", \"employee_name\": \"Julius Macharia\", \"application_id\": \"7\", \"application_link\": \"?s=user&ss=leave&p=my_applications&id=7\"}', '?s=user&ss=leave&p=my_applications&id=7', 'ri-calendar-event-line', 'medium', 'unread', NULL, NULL, NULL, '2025-11-19 18:24:37', 'N', 'N'),
(14, '2025-11-19 18:26:55', 2, 49, 4, 1, 1, 'leave_application', 8, 'Leave Application Awaiting Your Approval', '<strong>Felix Mauncho</strong> has requested <strong>Study Leave</strong> leave from <strong>Dec 2, 2025</strong> to <strong>Dec 5, 2025</strong>. This application requires your approval.', '{\"end_date\": \"Dec 5, 2025\", \"step_name\": \"Direct Supervisor\", \"leave_type\": \"Study Leave\", \"start_date\": \"Dec 2, 2025\", \"total_days\": 4, \"employee_id\": \"4\", \"approver_name\": \"John Doe\", \"employee_name\": \"Felix Mauncho\", \"is_final_step\": false, \"application_id\": \"8\", \"approval_level\": 1}', '?s=user&ss=leave&p=pending_approvals&id=8', 'ri-calendar-event-line', 'high', 'read', '2025-11-19 18:27:10', NULL, NULL, '2025-11-19 18:27:10', 'N', 'N'),
(15, '2025-11-19 18:26:55', 2, 48, 4, 1, 1, 'leave_application', 8, 'Leave Application Awaiting Your Approval', '<strong>Felix Mauncho</strong> has requested <strong>Study Leave</strong> leave from <strong>Dec 2, 2025</strong> to <strong>Dec 5, 2025</strong>. This application requires your approval.', '{\"end_date\": \"Dec 5, 2025\", \"step_name\": \"HR Manager\", \"leave_type\": \"Study Leave\", \"start_date\": \"Dec 2, 2025\", \"total_days\": 4, \"employee_id\": \"4\", \"approver_name\": \"Julius Macharia\", \"employee_name\": \"Felix Mauncho\", \"is_final_step\": true, \"application_id\": \"8\", \"approval_level\": 2}', '?s=user&ss=leave&p=pending_approvals&id=8', 'ri-calendar-event-line', 'high', 'read', '2025-11-19 18:27:26', NULL, NULL, '2025-11-19 18:27:26', 'N', 'N'),
(16, '2025-11-19 18:26:55', 1, 4, 4, 1, 1, 'leave_application', 8, 'Leave Application Submitted', '<strong>Felix Mauncho</strong> has submitted a leave application for <strong>Study Leave</strong> from <strong>Dec 2, 2025</strong> to <strong>Dec 5, 2025</strong> (4.00 days).', '{\"end_date\": \"Dec 5, 2025\", \"leave_type\": \"Study Leave\", \"start_date\": \"Dec 2, 2025\", \"total_days\": \"4.00\", \"employee_id\": 4, \"leave_reason\": \"re qer erer gerg er er\", \"employee_name\": \"Felix Mauncho\", \"application_id\": \"8\", \"application_link\": \"?s=user&ss=leave&p=my_applications&id=8\"}', '?s=user&ss=leave&p=my_applications&id=8', 'ri-calendar-event-line', 'medium', 'read', '2025-11-19 18:28:15', NULL, NULL, '2025-11-19 18:28:15', 'N', 'N'),
(17, '2025-11-19 18:27:17', 1, 4, 49, 1, 1, 'leave_application', 8, 'Leave Application Submitted', '<strong>Felix Mauncho</strong> has submitted a leave application for <strong>Study Leave</strong> from <strong>Dec 2, 2025</strong> to <strong>Dec 5, 2025</strong> (4.00 days).', '{\"end_date\": \"Dec 5, 2025\", \"leave_type\": \"Study Leave\", \"start_date\": \"Dec 2, 2025\", \"total_days\": \"4.00\", \"employee_id\": 4, \"employee_name\": \"Felix Mauncho\", \"application_id\": 8, \"application_link\": \"?s=user&ss=leave&p=my_applications&id=8\"}', '?s=user&ss=leave&p=my_applications&id=8', 'ri-calendar-event-line', 'medium', 'read', '2025-11-19 18:28:12', NULL, NULL, '2025-11-19 18:28:12', 'N', 'N'),
(18, '2025-11-19 18:27:35', 3, 4, 48, 1, 1, 'leave_application', 8, 'Your Leave Application Has Been Approved', 'Great news! Your <strong>Study Leave</strong> leave application from <strong>Dec 2, 2025</strong> to <strong>Dec 5, 2025</strong> has been <span style=\"color: #28a745;\">approved</span> by <strong>Julius Macharia</strong>.', '{\"end_date\": \"Dec 5, 2025\", \"leave_type\": \"Study Leave\", \"start_date\": \"Dec 2, 2025\", \"total_days\": \"4.00\", \"employee_id\": 4, \"approver_name\": \"Julius Macharia\", \"employee_name\": \"Felix Mauncho\", \"application_id\": 8, \"application_link\": \"?s=user&ss=leave&p=my_applications&id=8\", \"approver_comments\": \"you may proceed\"}', '?s=user&ss=leave&p=my_applications&id=8', 'ri-calendar-event-line', 'high', 'read', '2025-11-19 18:27:53', NULL, NULL, '2025-11-19 18:27:53', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_notification_channels`
--

DROP TABLE IF EXISTS `tija_notification_channels`;
CREATE TABLE IF NOT EXISTS `tija_notification_channels` (
  `channelID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `channelName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `channelSlug` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `channelDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `channelIcon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ri-notification-line',
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `requiresConfiguration` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `configFields` json DEFAULT NULL,
  `sortOrder` int DEFAULT '0',
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`channelID`),
  UNIQUE KEY `channelSlug` (`channelSlug`),
  UNIQUE KEY `idx_channel_slug` (`channelSlug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tija_notification_channels`
--

INSERT INTO `tija_notification_channels` (`channelID`, `DateAdded`, `channelName`, `channelSlug`, `channelDescription`, `channelIcon`, `isActive`, `requiresConfiguration`, `configFields`, `sortOrder`, `LastUpdate`, `Lapsed`, `Suspended`) VALUES
(1, '2025-10-22 09:56:25', 'In-App Notification', 'in_app', 'Display notifications in the application interface', 'ri-notification-3-line', 'Y', 'N', NULL, 1, '2025-10-22 06:56:25', 'N', 'N'),
(2, '2025-10-22 09:56:25', 'Email', 'email', 'Send notifications via email', 'ri-mail-line', 'Y', 'Y', NULL, 2, '2025-10-22 06:56:25', 'N', 'N'),
(3, '2025-10-22 09:56:25', 'SMS', 'sms', 'Send notifications via SMS', 'ri-message-3-line', 'Y', 'Y', NULL, 3, '2025-10-22 06:56:25', 'N', 'N'),
(4, '2025-10-22 09:56:25', 'Push Notification', 'push', 'Browser push notifications', 'ri-notification-badge-line', 'Y', 'Y', NULL, 4, '2025-10-22 06:56:25', 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_notification_events`
--

DROP TABLE IF EXISTS `tija_notification_events`;
CREATE TABLE IF NOT EXISTS `tija_notification_events` (
  `eventID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `moduleID` int NOT NULL,
  `eventName` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `eventSlug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `eventDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `eventCategory` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'general',
  `isUserConfigurable` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `defaultEnabled` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `priorityLevel` enum('low','medium','high','critical') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'medium',
  `sortOrder` int DEFAULT '0',
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdateByID` int DEFAULT NULL,
  `Lapsed` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`eventID`),
  UNIQUE KEY `unique_event_slug` (`eventSlug`,`moduleID`),
  KEY `idx_module` (`moduleID`),
  KEY `idx_event_slug` (`eventSlug`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tija_notification_events`
--

INSERT INTO `tija_notification_events` (`eventID`, `DateAdded`, `moduleID`, `eventName`, `eventSlug`, `eventDescription`, `eventCategory`, `isUserConfigurable`, `isActive`, `defaultEnabled`, `priorityLevel`, `sortOrder`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-10-22 09:56:25', 1, 'Leave Application Submitted', 'leave_application_submitted', 'When an employee submits a leave application', 'application', 'Y', 'Y', 'Y', 'medium', 1, '2025-10-22 03:56:25', NULL, 'N', 'N'),
(2, '2025-10-22 09:56:25', 1, 'Leave Pending Approval', 'leave_pending_approval', 'Notify approver of pending leave request', 'approval', 'Y', 'Y', 'Y', 'high', 2, '2025-10-22 03:56:25', NULL, 'N', 'N'),
(3, '2025-10-22 09:56:25', 1, 'Leave Approved', 'leave_approved', 'When leave application is approved', 'approval', 'Y', 'Y', 'Y', 'high', 3, '2025-10-22 03:56:25', NULL, 'N', 'N'),
(4, '2025-10-22 09:56:25', 1, 'Leave Rejected', 'leave_rejected', 'When leave application is rejected', 'approval', 'Y', 'Y', 'Y', 'high', 4, '2025-10-22 03:56:25', NULL, 'N', 'N'),
(5, '2025-10-22 09:56:25', 1, 'Leave Cancelled', 'leave_cancelled', 'When leave application is cancelled', 'application', 'Y', 'Y', 'Y', 'medium', 5, '2025-10-22 03:56:25', NULL, 'N', 'N'),
(6, '2025-10-22 09:56:25', 1, 'Leave Approval Reminder', 'leave_approval_reminder', 'Reminder for pending approval', 'reminder', 'Y', 'Y', 'Y', 'medium', 6, '2025-10-22 03:56:25', NULL, 'N', 'N'),
(7, '2025-10-22 09:56:25', 1, 'Leave Starting Soon', 'leave_starting_soon', 'Reminder that leave is starting soon', 'reminder', 'Y', 'Y', 'Y', 'low', 7, '2025-10-22 03:56:25', NULL, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_notification_logs`
--

DROP TABLE IF EXISTS `tija_notification_logs`;
CREATE TABLE IF NOT EXISTS `tija_notification_logs` (
  `logID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notificationID` int DEFAULT NULL,
  `queueID` int DEFAULT NULL,
  `eventID` int NOT NULL,
  `channelID` int NOT NULL,
  `userID` int NOT NULL,
  `action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `actionDetails` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ipAddress` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `userAgent` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`logID`),
  KEY `idx_notification` (`notificationID`),
  KEY `idx_queue` (`queueID`),
  KEY `idx_user` (`userID`),
  KEY `idx_date` (`DateAdded`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tija_notification_logs`
--

INSERT INTO `tija_notification_logs` (`logID`, `DateAdded`, `notificationID`, `queueID`, `eventID`, `channelID`, `userID`, `action`, `actionDetails`, `ipAddress`, `userAgent`) VALUES
(2, '2025-11-19 14:11:00', 1, NULL, 0, 0, 4, 'read', 'Notification marked as read', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(3, '2025-11-19 14:19:38', 2, NULL, 2, 1, 4, 'created', 'In-app notification created', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(4, '2025-11-19 14:45:53', 3, NULL, 2, 1, 49, 'created', 'In-app notification created', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(5, '2025-11-19 14:45:53', 4, NULL, 2, 1, 48, 'created', 'In-app notification created', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(6, '2025-11-19 14:45:53', 5, NULL, 1, 1, 4, 'created', 'In-app notification created', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(7, '2025-11-19 14:46:10', 4, NULL, 0, 0, 48, 'read', 'Notification marked as read', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(8, '2025-11-19 14:46:34', 3, NULL, 0, 0, 49, 'read', 'Notification marked as read', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(9, '2025-11-19 14:54:19', 6, NULL, 1, 1, 4, 'created', 'In-app notification created', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(10, '2025-11-19 14:55:12', 7, NULL, 1, 1, 4, 'created', 'In-app notification created', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(11, '2025-11-19 17:42:13', 8, NULL, 1, 1, 4, 'created', 'In-app notification created', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(12, '2025-11-19 17:47:50', 9, NULL, 1, 1, 4, 'created', 'In-app notification created', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(13, '2025-11-19 17:49:04', 10, NULL, 3, 1, 4, 'created', 'In-app notification created', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(14, '2025-11-19 18:24:37', 11, NULL, 2, 1, 9, 'created', 'In-app notification created', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(15, '2025-11-19 18:24:37', 12, NULL, 2, 1, 48, 'created', 'In-app notification created', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(16, '2025-11-19 18:24:37', 13, NULL, 1, 1, 48, 'created', 'In-app notification created', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(17, '2025-11-19 18:25:30', 10, NULL, 0, 0, 4, 'read', 'Notification marked as read', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(18, '2025-11-19 18:25:41', 9, NULL, 0, 0, 4, 'read', 'Notification marked as read', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(19, '2025-11-19 18:26:55', 14, NULL, 2, 1, 49, 'created', 'In-app notification created', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(20, '2025-11-19 18:26:55', 15, NULL, 2, 1, 48, 'created', 'In-app notification created', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(21, '2025-11-19 18:26:55', 16, NULL, 1, 1, 4, 'created', 'In-app notification created', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(22, '2025-11-19 18:27:10', 14, NULL, 0, 0, 49, 'read', 'Notification marked as read', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(23, '2025-11-19 18:27:17', 17, NULL, 1, 1, 4, 'created', 'In-app notification created', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(24, '2025-11-19 18:27:26', 15, NULL, 0, 0, 48, 'read', 'Notification marked as read', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(25, '2025-11-19 18:27:35', 18, NULL, 3, 1, 4, 'created', 'In-app notification created', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(26, '2025-11-19 18:27:53', 18, NULL, 0, 0, 4, 'read', 'Notification marked as read', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(27, '2025-11-19 18:28:12', 17, NULL, 0, 0, 4, 'read', 'Notification marked as read', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36'),
(28, '2025-11-19 18:28:15', 16, NULL, 0, 0, 4, 'read', 'Notification marked as read', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36');

-- --------------------------------------------------------

--
-- Table structure for table `tija_notification_modules`
--

DROP TABLE IF EXISTS `tija_notification_modules`;
CREATE TABLE IF NOT EXISTS `tija_notification_modules` (
  `moduleID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `moduleName` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `moduleSlug` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `moduleDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `moduleIcon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ri-notification-line',
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `sortOrder` int DEFAULT '0',
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdateByID` int DEFAULT NULL,
  `Lapsed` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`moduleID`),
  UNIQUE KEY `moduleSlug` (`moduleSlug`),
  KEY `idx_module_slug` (`moduleSlug`),
  KEY `idx_active` (`isActive`,`Suspended`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tija_notification_modules`
--

INSERT INTO `tija_notification_modules` (`moduleID`, `DateAdded`, `moduleName`, `moduleSlug`, `moduleDescription`, `moduleIcon`, `isActive`, `sortOrder`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-10-22 09:56:25', 'Leave Management', 'leave', 'Leave applications, approvals, and status updates', 'ri-calendar-event-line', 'Y', 1, '2025-10-22 06:56:25', NULL, 'N', 'N'),
(2, '2025-10-22 09:56:25', 'Sales & CRM', 'sales', 'Lead assignments, opportunity updates, and sales activities', 'ri-money-dollar-circle-line', 'Y', 2, '2025-10-22 06:56:25', NULL, 'N', 'N'),
(3, '2025-10-22 09:56:25', 'Tasks & Projects', 'projects', 'Task assignments, project updates, and deadlines', 'ri-task-line', 'Y', 3, '2025-10-22 06:56:25', NULL, 'N', 'N'),
(4, '2025-10-22 09:56:25', 'Activities & Events', 'activities', 'Activity reminders and event notifications', 'ri-calendar-check-line', 'Y', 4, '2025-10-22 06:56:25', NULL, 'N', 'N'),
(5, '2025-10-22 09:56:25', 'System Alerts', 'system', 'System-wide announcements and important alerts', 'ri-error-warning-line', 'Y', 5, '2025-10-22 06:56:25', NULL, 'N', 'N'),
(6, '2025-10-22 09:56:25', 'HR & Employee', 'hr', 'Employee profile updates, documentation, and HR announcements', 'ri-user-line', 'Y', 6, '2025-10-22 06:56:25', NULL, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_notification_preferences`
--

DROP TABLE IF EXISTS `tija_notification_preferences`;
CREATE TABLE IF NOT EXISTS `tija_notification_preferences` (
  `preferenceID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `userID` int NOT NULL,
  `eventID` int NOT NULL,
  `channelID` int NOT NULL,
  `isEnabled` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `notifyImmediately` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `notifyDigest` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `digestFrequency` enum('none','daily','weekly') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'none',
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Lapsed` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`preferenceID`),
  UNIQUE KEY `unique_preference` (`userID`,`eventID`,`channelID`),
  KEY `idx_user` (`userID`),
  KEY `eventID` (`eventID`),
  KEY `channelID` (`channelID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_notification_queue`
--

DROP TABLE IF EXISTS `tija_notification_queue`;
CREATE TABLE IF NOT EXISTS `tija_notification_queue` (
  `queueID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notificationID` int NOT NULL,
  `channelID` int NOT NULL,
  `recipientEmail` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recipientPhone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scheduledFor` datetime DEFAULT NULL,
  `attempts` int DEFAULT '0',
  `maxAttempts` int DEFAULT '3',
  `lastAttemptAt` datetime DEFAULT NULL,
  `status` enum('pending','processing','sent','failed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `errorMessage` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sentAt` datetime DEFAULT NULL,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`queueID`),
  KEY `idx_notification` (`notificationID`),
  KEY `idx_status` (`status`,`scheduledFor`),
  KEY `idx_channel` (`channelID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tija_notification_templates`
--

DROP TABLE IF EXISTS `tija_notification_templates`;
CREATE TABLE IF NOT EXISTS `tija_notification_templates` (
  `templateID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `eventID` int NOT NULL,
  `channelID` int NOT NULL,
  `orgDataID` int DEFAULT NULL,
  `entityID` int DEFAULT NULL,
  `templateName` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `templateSubject` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `templateBody` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `templateVariables` json DEFAULT NULL,
  `isDefault` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `isSystem` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `isActive` enum('Y','N') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Y',
  `createdBy` int DEFAULT NULL,
  `LastUpdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `LastUpdateByID` int DEFAULT NULL,
  `Lapsed` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`templateID`),
  KEY `idx_event` (`eventID`),
  KEY `idx_channel` (`channelID`),
  KEY `idx_entity` (`entityID`),
  KEY `idx_active` (`isActive`,`Suspended`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tija_notification_templates`
--

INSERT INTO `tija_notification_templates` (`templateID`, `DateAdded`, `eventID`, `channelID`, `orgDataID`, `entityID`, `templateName`, `templateSubject`, `templateBody`, `templateVariables`, `isDefault`, `isSystem`, `isActive`, `createdBy`, `LastUpdate`, `LastUpdateByID`, `Lapsed`, `Suspended`) VALUES
(1, '2025-10-22 09:56:25', 1, 1, NULL, NULL, 'Leave Application Submitted - In-App', 'Leave Application Submitted', '<strong>{{employee_name}}</strong> has submitted a leave application for <strong>{{leave_type}}</strong> from <strong>{{start_date}}</strong> to <strong>{{end_date}}</strong> ({{total_days}} days).', '[\"employee_name\", \"employee_id\", \"leave_type\", \"start_date\", \"end_date\", \"total_days\", \"application_id\"]', 'Y', 'Y', 'Y', NULL, '2025-10-22 06:56:25', NULL, 'N', 'N'),
(2, '2025-10-22 09:56:25', 1, 2, NULL, NULL, 'Leave Application Submitted - Email', 'Leave Application Submitted - {{leave_type}}', '<h2>Leave Application Submitted</h2><p>Dear HR Team,</p><p><strong>{{employee_name}}</strong> has submitted a new leave application.</p><table border=\"1\" cellpadding=\"10\" style=\"border-collapse: collapse;\"><tr><td><strong>Employee:</strong></td><td>{{employee_name}}</td></tr><tr><td><strong>Leave Type:</strong></td><td>{{leave_type}}</td></tr><tr><td><strong>Start Date:</strong></td><td>{{start_date}}</td></tr><tr><td><strong>End Date:</strong></td><td>{{end_date}}</td></tr><tr><td><strong>Total Days:</strong></td><td>{{total_days}}</td></tr><tr><td><strong>Reason:</strong></td><td>{{leave_reason}}</td></tr></table><p>Please review the application in the system.</p><p><a href=\"{{application_link}}\" style=\"background: #6c5ce7; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;\">View Application</a></p>', '[\"employee_name\", \"employee_id\", \"leave_type\", \"start_date\", \"end_date\", \"total_days\", \"leave_reason\", \"application_id\", \"application_link\"]', 'Y', 'Y', 'Y', NULL, '2025-10-22 06:56:25', NULL, 'N', 'N'),
(3, '2025-10-22 09:56:25', 2, 1, NULL, NULL, 'Leave Pending Approval - In-App', 'Leave Application Awaiting Your Approval', '<strong>{{employee_name}}</strong> has requested <strong>{{leave_type}}</strong> leave from <strong>{{start_date}}</strong> to <strong>{{end_date}}</strong>. This application requires your approval.', '[\"employee_name\", \"employee_id\", \"leave_type\", \"start_date\", \"end_date\", \"total_days\", \"application_id\", \"approval_level\", \"approver_name\"]', 'Y', 'Y', 'Y', NULL, '2025-10-22 06:56:25', NULL, 'N', 'N'),
(4, '2025-10-22 09:56:25', 3, 1, NULL, NULL, 'Leave Approved - In-App', 'Your Leave Application Has Been Approved', 'Great news! Your <strong>{{leave_type}}</strong> leave application from <strong>{{start_date}}</strong> to <strong>{{end_date}}</strong> has been <span style=\"color: #28a745;\">approved</span> by <strong>{{approver_name}}</strong>.', '[\"employee_name\", \"employee_id\", \"leave_type\", \"start_date\", \"end_date\", \"total_days\", \"application_id\", \"approver_name\", \"approver_comments\"]', 'Y', 'Y', 'Y', NULL, '2025-10-22 06:56:25', NULL, 'N', 'N'),
(5, '2025-10-22 09:56:25', 4, 1, NULL, NULL, 'Leave Rejected - In-App', 'Your Leave Application Has Been Rejected', 'Your <strong>{{leave_type}}</strong> leave application from <strong>{{start_date}}</strong> to <strong>{{end_date}}</strong> has been <span style=\"color: #dc3545;\">rejected</span> by <strong>{{approver_name}}</strong>.<br><strong>Reason:</strong> {{rejection_reason}}', '[\"employee_name\", \"employee_id\", \"leave_type\", \"start_date\", \"end_date\", \"total_days\", \"application_id\", \"approver_name\", \"rejection_reason\"]', 'Y', 'Y', 'Y', NULL, '2025-10-22 06:56:25', NULL, 'N', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `tija_notification_template_variables`
--

DROP TABLE IF EXISTS `tija_notification_template_variables`;
CREATE TABLE IF NOT EXISTS `tija_notification_template_variables` (
  `variableID` int NOT NULL AUTO_INCREMENT,
  `DateAdded` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `moduleID` int NOT NULL,
  `variableName` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `variableSlug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `variableDescription` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `dataSource` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dataField` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exampleValue` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sortOrder` int DEFAULT '0',
  `Lapsed` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  `Suspended` enum('N','Y') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'N',
  PRIMARY KEY (`variableID`),
  UNIQUE KEY `unique_variable` (`moduleID`,`variableSlug`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tija_notification_template_variables`
--

INSERT INTO `tija_notification_template_variables` (`variableID`, `DateAdded`, `moduleID`, `variableName`, `variableSlug`, `variableDescription`, `dataSource`, `dataField`, `exampleValue`, `sortOrder`, `Lapsed`, `Suspended`) VALUES
(1, '2025-10-22 09:56:25', 1, 'Employee Name', 'employee_name', 'Full name of the employee', 'people', 'CONCAT(FirstName, \" \", Surname)', 'John Doe', 1, 'N', 'N'),
(2, '2025-10-22 09:56:25', 1, 'Employee ID', 'employee_id', 'ID of the employee', 'people', 'ID', '123', 2, 'N', 'N'),
(3, '2025-10-22 09:56:25', 1, 'Leave Type', 'leave_type', 'Type of leave', 'tija_leave_types', 'leaveTypeName', 'Annual Leave', 3, 'N', 'N'),
(4, '2025-10-22 09:56:25', 1, 'Start Date', 'start_date', 'Leave start date', 'tija_leave_applications', 'startDate', '2025-10-25', 4, 'N', 'N'),
(5, '2025-10-22 09:56:25', 1, 'End Date', 'end_date', 'Leave end date', 'tija_leave_applications', 'endDate', '2025-10-27', 5, 'N', 'N'),
(6, '2025-10-22 09:56:25', 1, 'Total Days', 'total_days', 'Total number of leave days', 'tija_leave_applications', 'noOfDays', '3', 6, 'N', 'N'),
(7, '2025-10-22 09:56:25', 1, 'Leave Reason', 'leave_reason', 'Reason for leave', 'tija_leave_applications', 'leaveComments', 'Family vacation', 7, 'N', 'N'),
(8, '2025-10-22 09:56:25', 1, 'Application ID', 'application_id', 'Leave application ID', 'tija_leave_applications', 'leaveApplicationID', '456', 8, 'N', 'N'),
(9, '2025-10-22 09:56:25', 1, 'Approver Name', 'approver_name', 'Name of the approver', 'people', 'CONCAT(FirstName, \" \", Surname)', 'Jane Smith', 9, 'N', 'N'),
(10, '2025-10-22 09:56:25', 1, 'Approval Level', 'approval_level', 'Current approval level', 'tija_leave_approval_steps', 'stepOrder', '1', 10, 'N', 'N'),
(11, '2025-10-22 09:56:25', 1, 'Application Link', 'application_link', 'Direct link to the application', 'generated', 'url', 'https://example.com/leave/123', 11, 'N', 'N');

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_leave_approval_policies`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `vw_leave_approval_policies`;
CREATE TABLE IF NOT EXISTS `vw_leave_approval_policies` (
`policyID` int
,`entityID` int
,`orgDataID` int
,`policyName` varchar(255)
,`policyDescription` text
,`isActive` enum('Y','N')
,`isDefault` enum('Y','N')
,`requireAllApprovals` enum('Y','N')
,`allowDelegation` enum('Y','N')
,`autoApproveThreshold` int
,`totalSteps` bigint
,`requiredSteps` bigint
,`createdBy` int
,`createdAt` datetime
,`createdByName` varchar(257)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_leave_approval_workflow`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `vw_leave_approval_workflow`;
CREATE TABLE IF NOT EXISTS `vw_leave_approval_workflow` (
`policyID` int
,`policyName` varchar(255)
,`entityID` int
,`stepID` int
,`stepOrder` int
,`stepName` varchar(255)
,`stepType` enum('supervisor','project_manager','hr_manager','hr_representative','department_head','custom_role','specific_user')
,`stepDescription` text
,`isRequired` enum('Y','N')
,`isConditional` enum('Y','N')
,`conditionType` enum('days_threshold','leave_type','user_role','department','custom')
,`escalationDays` int
,`customApproversCount` bigint
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_notification_events_with_templates`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `vw_notification_events_with_templates`;
CREATE TABLE IF NOT EXISTS `vw_notification_events_with_templates` (
`eventID` int
,`eventName` varchar(100)
,`eventSlug` varchar(100)
,`eventDescription` text
,`eventCategory` varchar(50)
,`priorityLevel` enum('low','medium','high','critical')
,`moduleID` int
,`moduleName` varchar(100)
,`moduleSlug` varchar(50)
,`templateCount` bigint
,`isActive` enum('Y','N')
,`isUserConfigurable` enum('Y','N')
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_pending_leave_approvals`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `vw_pending_leave_approvals`;
CREATE TABLE IF NOT EXISTS `vw_pending_leave_approvals` (
`instanceID` int
,`leaveApplicationID` int
,`employeeID` int
,`employeeName` varchar(257)
,`leaveTypeID` int
,`leaveTypeName` varchar(255)
,`startDate` date
,`endDate` date
,`totalDays` decimal(3,2)
,`policyID` int
,`policyName` varchar(255)
,`currentStepID` int
,`currentStepName` varchar(255)
,`currentStepType` enum('supervisor','project_manager','hr_manager','hr_representative','department_head','custom_role','specific_user')
,`currentStepOrder` int
,`workflowStatus` enum('pending','in_progress','approved','rejected','cancelled','escalated')
,`startedAt` datetime
,`lastActionAt` datetime
,`daysPending` int
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_user_notification_summary`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `vw_user_notification_summary`;
CREATE TABLE IF NOT EXISTS `vw_user_notification_summary` (
`userID` int
,`totalNotifications` bigint
,`unreadCount` decimal(23,0)
,`readCount` decimal(23,0)
,`criticalUnread` decimal(23,0)
,`lastNotificationDate` datetime
);

-- --------------------------------------------------------

--
-- Structure for view `vw_leave_approval_policies`
--
DROP TABLE IF EXISTS `vw_leave_approval_policies`;

DROP VIEW IF EXISTS `vw_leave_approval_policies`;
CREATE ALGORITHM=UNDEFINED DEFINER=CURRENT_USER SQL SECURITY DEFINER VIEW `vw_leave_approval_policies`  AS SELECT `p`.`policyID` AS `policyID`, `p`.`entityID` AS `entityID`, `p`.`orgDataID` AS `orgDataID`, `p`.`policyName` AS `policyName`, `p`.`policyDescription` AS `policyDescription`, `p`.`isActive` AS `isActive`, `p`.`isDefault` AS `isDefault`, `p`.`requireAllApprovals` AS `requireAllApprovals`, `p`.`allowDelegation` AS `allowDelegation`, `p`.`autoApproveThreshold` AS `autoApproveThreshold`, count(distinct `s`.`stepID`) AS `totalSteps`, count(distinct (case when (`s`.`isRequired` = 'Y') then `s`.`stepID` end)) AS `requiredSteps`, `p`.`createdBy` AS `createdBy`, `p`.`createdAt` AS `createdAt`, concat(`creator`.`FirstName`,' ',`creator`.`Surname`) AS `createdByName` FROM ((`tija_leave_approval_policies` `p` left join `tija_leave_approval_steps` `s` on(((`p`.`policyID` = `s`.`policyID`) and (`s`.`Suspended` = 'N')))) left join `people` `creator` on((`p`.`createdBy` = `creator`.`ID`))) WHERE (`p`.`Lapsed` = 'N') GROUP BY `p`.`policyID` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_leave_approval_workflow`
--
DROP TABLE IF EXISTS `vw_leave_approval_workflow`;

DROP VIEW IF EXISTS `vw_leave_approval_workflow`;
CREATE ALGORITHM=UNDEFINED DEFINER=CURRENT_USER SQL SECURITY DEFINER VIEW `vw_leave_approval_workflow`  AS SELECT `p`.`policyID` AS `policyID`, `p`.`policyName` AS `policyName`, `p`.`entityID` AS `entityID`, `s`.`stepID` AS `stepID`, `s`.`stepOrder` AS `stepOrder`, `s`.`stepName` AS `stepName`, `s`.`stepType` AS `stepType`, `s`.`stepDescription` AS `stepDescription`, `s`.`isRequired` AS `isRequired`, `s`.`isConditional` AS `isConditional`, `s`.`conditionType` AS `conditionType`, `s`.`escalationDays` AS `escalationDays`, count(`a`.`approverID`) AS `customApproversCount` FROM ((`tija_leave_approval_policies` `p` join `tija_leave_approval_steps` `s` on((`p`.`policyID` = `s`.`policyID`))) left join `tija_leave_approval_step_approvers` `a` on(((`s`.`stepID` = `a`.`stepID`) and (`a`.`Suspended` = 'N')))) WHERE ((`p`.`Lapsed` = 'N') AND (`p`.`Suspended` = 'N') AND (`s`.`Suspended` = 'N')) GROUP BY `s`.`stepID` ORDER BY `p`.`policyID` ASC, `s`.`stepOrder` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_notification_events_with_templates`
--
DROP TABLE IF EXISTS `vw_notification_events_with_templates`;

DROP VIEW IF EXISTS `vw_notification_events_with_templates`;
CREATE ALGORITHM=UNDEFINED DEFINER=CURRENT_USER SQL SECURITY DEFINER VIEW `vw_notification_events_with_templates`  AS SELECT `e`.`eventID` AS `eventID`, `e`.`eventName` AS `eventName`, `e`.`eventSlug` AS `eventSlug`, `e`.`eventDescription` AS `eventDescription`, `e`.`eventCategory` AS `eventCategory`, `e`.`priorityLevel` AS `priorityLevel`, `m`.`moduleID` AS `moduleID`, `m`.`moduleName` AS `moduleName`, `m`.`moduleSlug` AS `moduleSlug`, count(distinct `t`.`templateID`) AS `templateCount`, `e`.`isActive` AS `isActive`, `e`.`isUserConfigurable` AS `isUserConfigurable` FROM ((`tija_notification_events` `e` join `tija_notification_modules` `m` on((`e`.`moduleID` = `m`.`moduleID`))) left join `tija_notification_templates` `t` on(((`e`.`eventID` = `t`.`eventID`) and (`t`.`Suspended` = 'N')))) WHERE ((`e`.`Suspended` = 'N') AND (`m`.`Suspended` = 'N')) GROUP BY `e`.`eventID` ;

-- --------------------------------------------------------

--
-- Structure for view `vw_pending_leave_approvals`
--
DROP TABLE IF EXISTS `vw_pending_leave_approvals`;

DROP VIEW IF EXISTS `vw_pending_leave_approvals`;
CREATE ALGORITHM=UNDEFINED DEFINER=CURRENT_USER SQL SECURITY DEFINER VIEW `vw_pending_leave_approvals`  AS SELECT `i`.`instanceID` AS `instanceID`, `i`.`leaveApplicationID` AS `leaveApplicationID`, `la`.`employeeID` AS `employeeID`, concat(`emp`.`FirstName`,' ',`emp`.`Surname`) AS `employeeName`, `la`.`leaveTypeID` AS `leaveTypeID`, `lt`.`leaveTypeName` AS `leaveTypeName`, `la`.`startDate` AS `startDate`, `la`.`endDate` AS `endDate`, `la`.`noOfDays` AS `totalDays`, `i`.`policyID` AS `policyID`, `p`.`policyName` AS `policyName`, `i`.`currentStepID` AS `currentStepID`, `s`.`stepName` AS `currentStepName`, `s`.`stepType` AS `currentStepType`, `s`.`stepOrder` AS `currentStepOrder`, `i`.`workflowStatus` AS `workflowStatus`, `i`.`startedAt` AS `startedAt`, `i`.`lastActionAt` AS `lastActionAt`, (to_days(now()) - to_days(`i`.`lastActionAt`)) AS `daysPending` FROM (((((`tija_leave_approval_instances` `i` join `tija_leave_applications` `la` on((`i`.`leaveApplicationID` = `la`.`leaveApplicationID`))) join `people` `emp` on((`la`.`employeeID` = `emp`.`ID`))) join `tija_leave_types` `lt` on((`la`.`leaveTypeID` = `lt`.`leaveTypeID`))) join `tija_leave_approval_policies` `p` on((`i`.`policyID` = `p`.`policyID`))) left join `tija_leave_approval_steps` `s` on((`i`.`currentStepID` = `s`.`stepID`))) WHERE (`i`.`workflowStatus` in ('pending','in_progress')) ORDER BY `i`.`lastActionAt` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `vw_user_notification_summary`
--
DROP TABLE IF EXISTS `vw_user_notification_summary`;

DROP VIEW IF EXISTS `vw_user_notification_summary`;
CREATE ALGORITHM=UNDEFINED DEFINER=CURRENT_USER SQL SECURITY DEFINER VIEW `vw_user_notification_summary`  AS SELECT `tija_notifications_enhanced`.`userID` AS `userID`, count(0) AS `totalNotifications`, sum((case when (`tija_notifications_enhanced`.`status` = 'unread') then 1 else 0 end)) AS `unreadCount`, sum((case when (`tija_notifications_enhanced`.`status` = 'read') then 1 else 0 end)) AS `readCount`, sum((case when ((`tija_notifications_enhanced`.`priority` = 'critical') and (`tija_notifications_enhanced`.`status` = 'unread')) then 1 else 0 end)) AS `criticalUnread`, max(`tija_notifications_enhanced`.`DateAdded`) AS `lastNotificationDate` FROM `tija_notifications_enhanced` WHERE ((`tija_notifications_enhanced`.`Lapsed` = 'N') AND (`tija_notifications_enhanced`.`Suspended` = 'N')) GROUP BY `tija_notifications_enhanced`.`userID` ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tija_leave_accumulation_history`
--
ALTER TABLE `tija_leave_accumulation_history`
  ADD CONSTRAINT `tija_leave_accumulation_history_ibfk_1` FOREIGN KEY (`policyID`) REFERENCES `tija_leave_accumulation_policies` (`policyID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tija_leave_accumulation_history_ibfk_2` FOREIGN KEY (`ruleID`) REFERENCES `tija_leave_accumulation_rules` (`ruleID`) ON DELETE SET NULL;

--
-- Constraints for table `tija_leave_accumulation_rules`
--
ALTER TABLE `tija_leave_accumulation_rules`
  ADD CONSTRAINT `tija_leave_accumulation_rules_ibfk_1` FOREIGN KEY (`policyID`) REFERENCES `tija_leave_accumulation_policies` (`policyID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_leave_approval_actions`
--
ALTER TABLE `tija_leave_approval_actions`
  ADD CONSTRAINT `tija_leave_approval_actions_ibfk_1` FOREIGN KEY (`instanceID`) REFERENCES `tija_leave_approval_instances` (`instanceID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_leave_approval_instances`
--
ALTER TABLE `tija_leave_approval_instances`
  ADD CONSTRAINT `tija_leave_approval_instances_ibfk_1` FOREIGN KEY (`policyID`) REFERENCES `tija_leave_approval_policies` (`policyID`);

--
-- Constraints for table `tija_leave_approval_steps`
--
ALTER TABLE `tija_leave_approval_steps`
  ADD CONSTRAINT `tija_leave_approval_steps_ibfk_1` FOREIGN KEY (`policyID`) REFERENCES `tija_leave_approval_policies` (`policyID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_leave_approval_step_approvers`
--
ALTER TABLE `tija_leave_approval_step_approvers`
  ADD CONSTRAINT `tija_leave_approval_step_approvers_ibfk_1` FOREIGN KEY (`stepID`) REFERENCES `tija_leave_approval_steps` (`stepID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_leave_workflow_template_steps`
--
ALTER TABLE `tija_leave_workflow_template_steps`
  ADD CONSTRAINT `tija_leave_workflow_template_steps_ibfk_1` FOREIGN KEY (`templateID`) REFERENCES `tija_leave_workflow_templates` (`templateID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_notifications_enhanced`
--
ALTER TABLE `tija_notifications_enhanced`
  ADD CONSTRAINT `tija_notifications_enhanced_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `tija_notification_events` (`eventID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_notification_preferences`
--
ALTER TABLE `tija_notification_preferences`
  ADD CONSTRAINT `tija_notification_preferences_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `tija_notification_events` (`eventID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tija_notification_preferences_ibfk_2` FOREIGN KEY (`channelID`) REFERENCES `tija_notification_channels` (`channelID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_notification_queue`
--
ALTER TABLE `tija_notification_queue`
  ADD CONSTRAINT `tija_notification_queue_ibfk_1` FOREIGN KEY (`notificationID`) REFERENCES `tija_notifications_enhanced` (`notificationID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tija_notification_queue_ibfk_2` FOREIGN KEY (`channelID`) REFERENCES `tija_notification_channels` (`channelID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_notification_templates`
--
ALTER TABLE `tija_notification_templates`
  ADD CONSTRAINT `tija_notification_templates_ibfk_1` FOREIGN KEY (`eventID`) REFERENCES `tija_notification_events` (`eventID`) ON DELETE CASCADE,
  ADD CONSTRAINT `tija_notification_templates_ibfk_2` FOREIGN KEY (`channelID`) REFERENCES `tija_notification_channels` (`channelID`) ON DELETE CASCADE;

--
-- Constraints for table `tija_notification_template_variables`
--
ALTER TABLE `tija_notification_template_variables`
  ADD CONSTRAINT `tija_notification_template_variables_ibfk_1` FOREIGN KEY (`moduleID`) REFERENCES `tija_notification_modules` (`moduleID`) ON DELETE CASCADE;
COMMIT;
