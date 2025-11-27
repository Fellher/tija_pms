<?php
/**
 * Setup Leave Approval Workflow Tables
 *
 * Creates the core tables required for the leave approval workflow.
 * Safe to run multiple times (uses CREATE TABLE IF NOT EXISTS).
 */

session_start();
$base = '../../../../';
set_include_path($base);

include 'php/includes.php';

header('Content-Type: application/json');

// Require admin privileges
if (!$isValidAdmin && !$isAdmin && !$isSuperAdmin) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be an administrator to run this migration.'
    ]);
    exit;
}

$migrationStatements = [
    'tija_leave_approval_policies' => "
        CREATE TABLE IF NOT EXISTS `tija_leave_approval_policies` (
            `policyID` INT(11) NOT NULL AUTO_INCREMENT,
            `policyName` VARCHAR(150) NOT NULL,
            `policyDescription` TEXT DEFAULT NULL,
            `orgDataID` INT(11) DEFAULT NULL,
            `entityID` INT(11) DEFAULT NULL,
            `isDefault` CHAR(1) NOT NULL DEFAULT 'N',
            `isActive` CHAR(1) NOT NULL DEFAULT 'Y',
            `approvalType` VARCHAR(20) NOT NULL DEFAULT 'parallel',
            `requireAllApprovals` ENUM('Y','N') DEFAULT 'N',
            `allowDelegation` ENUM('Y','N') DEFAULT 'Y',
            `autoDelegationOnLeave` ENUM('Y','N') DEFAULT 'N',
            `delegationMethod` VARCHAR(50) DEFAULT 'predefined',
            `delegationPrompt` ENUM('Y','N') DEFAULT 'Y',
            `allowSkipLevel` ENUM('Y','N') DEFAULT 'N',
            `autoApproveThreshold` INT DEFAULT NULL,
            `createdBy` INT(11) DEFAULT NULL,
            `createdAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updatedBy` INT(11) DEFAULT NULL,
            `updatedAt` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            `Lapsed` CHAR(1) NOT NULL DEFAULT 'N',
            `Suspended` CHAR(1) NOT NULL DEFAULT 'N',
            PRIMARY KEY (`policyID`),
            KEY `idx_policy_org` (`orgDataID`),
            KEY `idx_policy_entity` (`entityID`),
            KEY `idx_policy_active` (`isActive`),
            KEY `idx_policy_default` (`isDefault`),
            KEY `idx_delegation_policy` (`allowDelegation`, `autoDelegationOnLeave`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ",
    'tija_leave_approval_steps' => "
        CREATE TABLE IF NOT EXISTS `tija_leave_approval_steps` (
            `stepID` INT(11) NOT NULL AUTO_INCREMENT,
            `policyID` INT(11) NOT NULL,
            `stepOrder` INT(11) NOT NULL DEFAULT 1,
            `stepName` VARCHAR(150) DEFAULT NULL,
            `stepDescription` VARCHAR(255) DEFAULT NULL,
            `isRequired` CHAR(1) NOT NULL DEFAULT 'Y',
            `approvalRequired` VARCHAR(10) NOT NULL DEFAULT 'all',
            `escalationDays` INT DEFAULT NULL,
            `escalateToStepID` INT DEFAULT NULL,
            `delegateApproverID` INT(11) DEFAULT NULL,
            `delegationPriority` INT(11) DEFAULT 1,
            `allowDelegation` ENUM('Y','N') DEFAULT 'Y',
            `createdAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updatedAt` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            `Lapsed` CHAR(1) NOT NULL DEFAULT 'N',
            `Suspended` CHAR(1) NOT NULL DEFAULT 'N',
            PRIMARY KEY (`stepID`),
            KEY `idx_steps_policy` (`policyID`),
            KEY `idx_steps_order` (`stepOrder`),
            KEY `idx_delegation_step` (`delegateApproverID`, `allowDelegation`),
            CONSTRAINT `fk_leave_steps_policy`
                FOREIGN KEY (`policyID`) REFERENCES `tija_leave_approval_policies` (`policyID`)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ",
    'tija_leave_approval_step_approvers' => "
        CREATE TABLE IF NOT EXISTS `tija_leave_approval_step_approvers` (
            `stepApproverID` INT(11) NOT NULL AUTO_INCREMENT,
            `stepID` INT(11) NOT NULL,
            `approverID` INT(11) DEFAULT NULL,
            `approverUserID` INT(11) DEFAULT NULL,
            `notificationOrder` INT(11) DEFAULT 1,
            `delegatedToUserID` INT(11) DEFAULT NULL,
            `delegationReason` VARCHAR(255) DEFAULT NULL,
            `delegatedAt` DATETIME DEFAULT NULL,
            `createdAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updatedAt` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            `Lapsed` CHAR(1) NOT NULL DEFAULT 'N',
            `Suspended` CHAR(1) NOT NULL DEFAULT 'N',
            PRIMARY KEY (`stepApproverID`),
            KEY `idx_step_approver_step` (`stepID`),
            KEY `idx_step_approver_user` (`approverUserID`),
            KEY `idx_delegation_approver` (`delegatedToUserID`, `delegatedAt`),
            CONSTRAINT `fk_leave_step_approvers_step`
                FOREIGN KEY (`stepID`) REFERENCES `tija_leave_approval_steps` (`stepID`)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ",
    'tija_leave_approval_instances' => "
        CREATE TABLE IF NOT EXISTS `tija_leave_approval_instances` (
            `instanceID` INT(11) NOT NULL AUTO_INCREMENT,
            `leaveApplicationID` INT(11) NOT NULL,
            `policyID` INT(11) NOT NULL,
            `currentStepOrder` INT(11) DEFAULT 1,
            `status` VARCHAR(30) DEFAULT 'pending',
            `startedAt` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `completedAt` DATETIME DEFAULT NULL,
            `Lapsed` CHAR(1) NOT NULL DEFAULT 'N',
            `Suspended` CHAR(1) NOT NULL DEFAULT 'N',
            PRIMARY KEY (`instanceID`),
            UNIQUE KEY `uniq_leave_application` (`leaveApplicationID`),
            KEY `idx_instance_policy` (`policyID`),
            KEY `idx_instance_status` (`status`),
            CONSTRAINT `fk_leave_instance_policy`
                FOREIGN KEY (`policyID`) REFERENCES `tija_leave_approval_policies` (`policyID`)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ",
    'tija_leave_approval_actions' => "
        CREATE TABLE IF NOT EXISTS `tija_leave_approval_actions` (
            `actionID` INT(11) NOT NULL AUTO_INCREMENT,
            `instanceID` INT(11) NOT NULL,
            `stepID` INT(11) NOT NULL,
            `approverID` INT(11) DEFAULT NULL,
            `approverUserID` INT(11) DEFAULT NULL,
            `action` VARCHAR(50) NOT NULL,
            `comments` TEXT,
            `decisionDate` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`actionID`),
            KEY `idx_actions_instance` (`instanceID`),
            KEY `idx_actions_step` (`stepID`),
            KEY `idx_actions_approver` (`approverUserID`),
            KEY `idx_actions_instance_step_approver` (`instanceID`, `stepID`, `approverUserID`),
            CONSTRAINT `fk_leave_actions_instance`
                FOREIGN KEY (`instanceID`) REFERENCES `tija_leave_approval_instances` (`instanceID`)
                ON DELETE CASCADE,
            CONSTRAINT `fk_leave_actions_step`
                FOREIGN KEY (`stepID`) REFERENCES `tija_leave_approval_steps` (`stepID`)
                ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ",
    'tija_leave_approval_comments' => "
        CREATE TABLE IF NOT EXISTS `tija_leave_approval_comments` (
            `commentID` INT(11) NOT NULL AUTO_INCREMENT,
            `leaveApplicationID` INT(11) NOT NULL,
            `approverID` INT(11) DEFAULT NULL,
            `approverUserID` INT(11) DEFAULT NULL,
            `approvalLevel` VARCHAR(50) DEFAULT NULL,
            `comment` TEXT,
            `commentType` VARCHAR(30) DEFAULT NULL,
            `commentDate` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `Lapsed` CHAR(1) NOT NULL DEFAULT 'N',
            `Suspended` CHAR(1) NOT NULL DEFAULT 'N',
            PRIMARY KEY (`commentID`),
            KEY `idx_comments_application` (`leaveApplicationID`),
            KEY `idx_comments_approver` (`approverUserID`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    "
];

$results = [];
$errors = [];

foreach ($migrationStatements as $tableName => $sql) {
    try {
        $execution = $DBConn->query($sql);
        if ($execution) {
            $results[] = "✅ {$tableName} ready";
        } else {
            $errors[] = "⚠️ {$tableName} could not be created (no error thrown)";
        }
    } catch (Exception $e) {
        $errors[] = "❌ {$tableName} failed: " . $e->getMessage();
    }
}

$success = empty($errors);

echo json_encode([
    'success' => $success,
    'message' => $success
        ? 'Leave approval workflow tables are ready.'
        : 'Some tables could not be created. See details.',
    'details' => array_merge($results, $errors)
]);

