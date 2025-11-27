<?php
/**
 * Migrate to Parallel Workflow
 *
 * Adds columns to support parallel/independent approval workflow
 * Safe to run multiple times (checks for column existence first)
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
    // Check and add approvalType column to tija_leave_approval_policies
    $checkColumn = $DBConn->fetch_all_rows(
        "SHOW COLUMNS FROM tija_leave_approval_policies LIKE 'approvalType'",
        array()
    );

    if (!$checkColumn || count($checkColumn) === 0) {
        $sql = "ALTER TABLE `tija_leave_approval_policies`
                ADD COLUMN `approvalType` VARCHAR(20) NOT NULL DEFAULT 'parallel'
                AFTER `isActive`";
        $execution = $DBConn->query($sql);
        if ($execution) {
            $results[] = "✅ Added approvalType column to tija_leave_approval_policies";
        } else {
            $errors[] = "⚠️ Failed to add approvalType column";
        }
    } else {
        $results[] = "✅ approvalType column already exists in tija_leave_approval_policies";
    }

    // Check and add approvalRequired column to tija_leave_approval_steps
    $checkColumn = $DBConn->fetch_all_rows(
        "SHOW COLUMNS FROM tija_leave_approval_steps LIKE 'approvalRequired'",
        array()
    );

    $approvalRequiredAdded = false;
    if (!$checkColumn || count($checkColumn) === 0) {
        try {
            $sql = "ALTER TABLE `tija_leave_approval_steps`
                    ADD COLUMN `approvalRequired` VARCHAR(10) NOT NULL DEFAULT 'all'
                    AFTER `isRequired`";
            $execution = $DBConn->query($sql);
            if ($execution) {
                $results[] = "✅ Added approvalRequired column to tija_leave_approval_steps";
                $approvalRequiredAdded = true;

                // Verify the column was actually added
                $verifyColumn = $DBConn->fetch_all_rows(
                    "SHOW COLUMNS FROM tija_leave_approval_steps LIKE 'approvalRequired'",
                    array()
                );
                if (!$verifyColumn || count($verifyColumn) === 0) {
                    $errors[] = "⚠️ approvalRequired column addition may have failed - column not found after addition";
                }
            } else {
                $errors[] = "⚠️ Failed to add approvalRequired column";
            }
        } catch (Exception $e) {
            $errors[] = "⚠️ Error adding approvalRequired column: " . $e->getMessage();
        }
    } else {
        $results[] = "✅ approvalRequired column already exists in tija_leave_approval_steps";
        $approvalRequiredAdded = true;
    }

    // Add stepName column if it doesn't exist
    $checkColumn = $DBConn->fetch_all_rows(
        "SHOW COLUMNS FROM tija_leave_approval_steps LIKE 'stepName'",
        array()
    );

    if (!$checkColumn || count($checkColumn) === 0) {
        $sql = "ALTER TABLE `tija_leave_approval_steps`
                ADD COLUMN `stepName` VARCHAR(150) DEFAULT NULL
                AFTER `stepOrder`";
        $execution = $DBConn->query($sql);
        if ($execution) {
            $results[] = "✅ Added stepName column to tija_leave_approval_steps";
        } else {
            $errors[] = "⚠️ Failed to add stepName column";
        }
    } else {
        $results[] = "✅ stepName column already exists in tija_leave_approval_steps";
    }

    // Check and add approverUserID column to tija_leave_approval_actions if it doesn't exist
    $checkApproverUserID = $DBConn->fetch_all_rows(
        "SHOW COLUMNS FROM tija_leave_approval_actions LIKE 'approverUserID'",
        array()
    );

    if (!$checkApproverUserID || count($checkApproverUserID) === 0) {
        $sql = "ALTER TABLE `tija_leave_approval_actions`
                ADD COLUMN `approverUserID` INT(11) DEFAULT NULL
                AFTER `approverID`";
        $execution = $DBConn->query($sql);
        if ($execution) {
            $results[] = "✅ Added approverUserID column to tija_leave_approval_actions";

            // Update existing records to populate approverUserID from approverID if possible
            $updateSQL = "UPDATE tija_leave_approval_actions
                         SET approverUserID = approverID
                         WHERE approverUserID IS NULL AND approverID IS NOT NULL";
            $DBConn->query($updateSQL);
        } else {
            $errors[] = "⚠️ Failed to add approverUserID column";
        }
    } else {
        $results[] = "✅ approverUserID column already exists in tija_leave_approval_actions";
    }

    // Add index on tija_leave_approval_actions for faster lookups
    $checkIndex = $DBConn->fetch_all_rows(
        "SHOW INDEX FROM tija_leave_approval_actions WHERE Key_name = 'idx_actions_instance_step_approver'",
        array()
    );

    if (!$checkIndex || count($checkIndex) === 0) {
        // Check if approverUserID exists before creating index
        $hasApproverUserID = $DBConn->fetch_all_rows(
            "SHOW COLUMNS FROM tija_leave_approval_actions LIKE 'approverUserID'",
            array()
        );

        if ($hasApproverUserID && count($hasApproverUserID) > 0) {
            $sql = "ALTER TABLE `tija_leave_approval_actions`
                    ADD INDEX `idx_actions_instance_step_approver` (`instanceID`, `stepID`, `approverUserID`)";
            $execution = $DBConn->query($sql);
            if ($execution) {
                $results[] = "✅ Added composite index to tija_leave_approval_actions";
            } else {
                $errors[] = "⚠️ Failed to add composite index";
            }
        } else {
            $results[] = "⚠️ Skipped composite index - approverUserID column not available";
        }
    } else {
        $results[] = "✅ Composite index already exists on tija_leave_approval_actions";
    }

    // Update existing records to have default values
    // Only update if column exists
    $checkApprovalType = $DBConn->fetch_all_rows(
        "SHOW COLUMNS FROM tija_leave_approval_policies LIKE 'approvalType'",
        array()
    );

    if ($checkApprovalType && count($checkApprovalType) > 0) {
        try {
            $updatePolicies = $DBConn->query(
                "UPDATE `tija_leave_approval_policies`
                 SET `approvalType` = 'parallel'
                 WHERE `approvalType` IS NULL OR `approvalType` = ''"
            );
            if ($updatePolicies) {
                $results[] = "✅ Updated existing policies to default to parallel";
            }
        } catch (Exception $e) {
            $errors[] = "⚠️ Failed to update policies: " . $e->getMessage();
        }
    }

    // Only update if column exists and was successfully added/verified
    if ($approvalRequiredAdded) {
        $checkApprovalRequired = $DBConn->fetch_all_rows(
            "SHOW COLUMNS FROM tija_leave_approval_steps LIKE 'approvalRequired'",
            array()
        );

        if ($checkApprovalRequired && count($checkApprovalRequired) > 0) {
            try {
                $updateSteps = $DBConn->query(
                    "UPDATE `tija_leave_approval_steps`
                     SET `approvalRequired` = 'all'
                     WHERE `approvalRequired` IS NULL OR `approvalRequired` = ''"
                );
                if ($updateSteps !== false) {
                    $results[] = "✅ Updated existing steps to default to 'all'";
                }
            } catch (Exception $e) {
                $errors[] = "⚠️ Failed to update steps: " . $e->getMessage();
            }
        } else {
            $errors[] = "⚠️ Cannot update steps - approvalRequired column not found";
        }
    }

    $success = empty($errors);

    echo json_encode([
        'success' => $success,
        'message' => $success
            ? 'Migration to parallel workflow completed successfully.'
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

