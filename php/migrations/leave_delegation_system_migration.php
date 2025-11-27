<?php
/**
 * Database Migration: Leave Delegation System
 *
 * Adds delegation-related fields to leave approval workflow tables
 * Safe to run multiple times (checks for column existence before adding)
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

$results = [];
$errors = [];

try {
    // 1. Add delegation fields to tija_leave_approval_policies
    $delegationPolicyFields = [
        'autoDelegationOnLeave' => "ALTER TABLE `tija_leave_approval_policies`
            ADD COLUMN IF NOT EXISTS `autoDelegationOnLeave` ENUM('Y','N') NOT NULL DEFAULT 'N'
            COMMENT 'Auto-delegate when approver is on leave' AFTER `allowDelegation`",

        'delegationMethod' => "ALTER TABLE `tija_leave_approval_policies`
            ADD COLUMN IF NOT EXISTS `delegationMethod` VARCHAR(50) DEFAULT 'predefined'
            COMMENT 'Method: predefined, skip_level, same_level, hr_manager' AFTER `autoDelegationOnLeave`",

        'delegationPrompt' => "ALTER TABLE `tija_leave_approval_policies`
            ADD COLUMN IF NOT EXISTS `delegationPrompt` ENUM('Y','N') NOT NULL DEFAULT 'Y'
            COMMENT 'Prompt approver to delegate before action' AFTER `delegationMethod`",

        'allowSkipLevel' => "ALTER TABLE `tija_leave_approval_policies`
            ADD COLUMN IF NOT EXISTS `allowSkipLevel` ENUM('Y','N') NOT NULL DEFAULT 'N'
            COMMENT 'Allow skip-level delegation' AFTER `delegationPrompt`"
    ];

    foreach ($delegationPolicyFields as $fieldName => $sql) {
        // Check if column exists
        $checkSql = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS
                     WHERE TABLE_SCHEMA = DATABASE()
                     AND TABLE_NAME = 'tija_leave_approval_policies'
                     AND COLUMN_NAME = ?";

        $checkParams = array(array($fieldName, 's'));
        $checkResult = $DBConn->fetch_all_rows($checkSql, $checkParams);

        if ($checkResult && isset($checkResult[0]) && $checkResult[0]['count'] == 0) {
            // Column doesn't exist, add it
            // MySQL doesn't support IF NOT EXISTS in ALTER TABLE, so we'll use a workaround
            $alterSql = str_replace(' IF NOT EXISTS', '', $sql);
            try {
                $DBConn->query($alterSql);
                if ($DBConn->execute()) {
                    $results[] = "✅ Added column `{$fieldName}` to tija_leave_approval_policies";
                } else {
                    $errors[] = "❌ Failed to add `{$fieldName}`: Query executed but returned false";
                }
            } catch (Exception $e) {
                $errors[] = "❌ Failed to add `{$fieldName}`: " . $e->getMessage();
            }
        } else {
            $results[] = "⏭️ Column `{$fieldName}` already exists in tija_leave_approval_policies";
        }
    }

    // 2. Add delegation fields to tija_leave_approval_steps
    $delegationStepFields = [
        'delegateApproverID' => "ALTER TABLE `tija_leave_approval_steps`
            ADD COLUMN IF NOT EXISTS `delegateApproverID` INT(11) DEFAULT NULL
            COMMENT 'Predefined delegate approver user ID' AFTER `escalateToStepID`",

        'delegationPriority' => "ALTER TABLE `tija_leave_approval_steps`
            ADD COLUMN IF NOT EXISTS `delegationPriority` INT(11) DEFAULT 1
            COMMENT 'Priority order for delegation (1 = highest)' AFTER `delegateApproverID`",

        'allowDelegation' => "ALTER TABLE `tija_leave_approval_steps`
            ADD COLUMN IF NOT EXISTS `allowDelegation` ENUM('Y','N') NOT NULL DEFAULT 'Y'
            COMMENT 'Allow delegation for this step' AFTER `delegationPriority`"
    ];

    foreach ($delegationStepFields as $fieldName => $sql) {
        $checkSql = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS
                     WHERE TABLE_SCHEMA = DATABASE()
                     AND TABLE_NAME = 'tija_leave_approval_steps'
                     AND COLUMN_NAME = ?";

        $checkParams = array(array($fieldName, 's'));
        $checkResult = $DBConn->fetch_all_rows($checkSql, $checkParams);

        if ($checkResult && isset($checkResult[0]) && $checkResult[0]['count'] == 0) {
            $alterSql = str_replace(' IF NOT EXISTS', '', $sql);
            try {
                $DBConn->query($alterSql);
                if ($DBConn->execute()) {
                    $results[] = "✅ Added column `{$fieldName}` to tija_leave_approval_steps";
                } else {
                    $errors[] = "❌ Failed to add `{$fieldName}`: Query executed but returned false";
                }
            } catch (Exception $e) {
                $errors[] = "❌ Failed to add `{$fieldName}`: " . $e->getMessage();
            }
        } else {
            $results[] = "⏭️ Column `{$fieldName}` already exists in tija_leave_approval_steps";
        }
    }

    // 3. Add delegation tracking to tija_leave_approval_step_approvers
    $delegationApproverFields = [
        'delegationReason' => "ALTER TABLE `tija_leave_approval_step_approvers`
            ADD COLUMN IF NOT EXISTS `delegationReason` VARCHAR(255) DEFAULT NULL
            COMMENT 'Reason for delegation (on_leave, unavailable, etc.)' AFTER `delegatedToUserID`",

        'delegatedAt' => "ALTER TABLE `tija_leave_approval_step_approvers`
            ADD COLUMN IF NOT EXISTS `delegatedAt` DATETIME DEFAULT NULL
            COMMENT 'When delegation occurred' AFTER `delegationReason`"
    ];

    foreach ($delegationApproverFields as $fieldName => $sql) {
        $checkSql = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS
                     WHERE TABLE_SCHEMA = DATABASE()
                     AND TABLE_NAME = 'tija_leave_approval_step_approvers'
                     AND COLUMN_NAME = ?";

        $checkParams = array(array($fieldName, 's'));
        $checkResult = $DBConn->fetch_all_rows($checkSql, $checkParams);

        if ($checkResult && isset($checkResult[0]) && $checkResult[0]['count'] == 0) {
            $alterSql = str_replace(' IF NOT EXISTS', '', $sql);
            try {
                $DBConn->query($alterSql);
                if ($DBConn->execute()) {
                    $results[] = "✅ Added column `{$fieldName}` to tija_leave_approval_step_approvers";
                } else {
                    $errors[] = "❌ Failed to add `{$fieldName}`: Query executed but returned false";
                }
            } catch (Exception $e) {
                $errors[] = "❌ Failed to add `{$fieldName}`: " . $e->getMessage();
            }
        } else {
            $results[] = "⏭️ Column `{$fieldName}` already exists in tija_leave_approval_step_approvers";
        }
    }

    // 4. Add index for delegation queries
    $indexes = [
        'idx_delegation_policy' => "CREATE INDEX IF NOT EXISTS `idx_delegation_policy`
            ON `tija_leave_approval_policies` (`allowDelegation`, `autoDelegationOnLeave`)",

        'idx_delegation_step' => "CREATE INDEX IF NOT EXISTS `idx_delegation_step`
            ON `tija_leave_approval_steps` (`delegateApproverID`, `allowDelegation`)",

        'idx_delegation_approver' => "CREATE INDEX IF NOT EXISTS `idx_delegation_approver`
            ON `tija_leave_approval_step_approvers` (`delegatedToUserID`, `delegatedAt`)"
    ];

    foreach ($indexes as $indexName => $sql) {
        try {
            // Check if index exists
            $checkIndexSql = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.STATISTICS
                              WHERE TABLE_SCHEMA = DATABASE()
                              AND TABLE_NAME = ?
                              AND INDEX_NAME = ?";

            $tableName = '';
            if (strpos($sql, 'tija_leave_approval_policies') !== false) {
                $tableName = 'tija_leave_approval_policies';
            } elseif (strpos($sql, 'tija_leave_approval_steps') !== false) {
                $tableName = 'tija_leave_approval_steps';
            } elseif (strpos($sql, 'tija_leave_approval_step_approvers') !== false) {
                $tableName = 'tija_leave_approval_step_approvers';
            }

            if ($tableName) {
                $checkParams = array(
                    array($tableName, 's'),
                    array($indexName, 's')
                );
                $checkResult = $DBConn->fetch_all_rows($checkIndexSql, $checkParams);

                if ($checkResult && isset($checkResult[0]) && $checkResult[0]['count'] == 0) {
                    $createSql = str_replace(' IF NOT EXISTS', '', $sql);
                    $DBConn->query($createSql);
                    $results[] = "✅ Created index `{$indexName}`";
                } else {
                    $results[] = "⏭️ Index `{$indexName}` already exists";
                }
            }
        } catch (Exception $e) {
            $errors[] = "❌ Failed to create index `{$indexName}`: " . $e->getMessage();
        }
    }

    $success = empty($errors);

    echo json_encode([
        'success' => $success,
        'message' => $success
            ? 'Delegation system migration completed successfully.'
            : 'Migration completed with some errors. See details.',
        'details' => array_merge($results, $errors)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Migration failed: ' . $e->getMessage(),
        'details' => array_merge($results, $errors)
    ]);
}
?>

