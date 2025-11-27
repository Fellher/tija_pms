<?php
/**
 * Database Migration: Accrual Type Enum Update
 *
 * Updates accrual type enum from (Monthly, Quarterly, Annual, Continuous)
 * to (Front-Loaded, Periodic, Proration)
 *
 * Maps existing values:
 * - Monthly/Quarterly -> Periodic
 * - Annual -> Front-Loaded
 * - Continuous -> Periodic
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
    // Check current enum values
    $checkEnumSql = "SELECT COLUMN_TYPE
                     FROM INFORMATION_SCHEMA.COLUMNS
                     WHERE TABLE_SCHEMA = DATABASE()
                     AND TABLE_NAME = 'tija_leave_accumulation_policies'
                     AND COLUMN_NAME = 'accrualType'";

    $enumResult = $DBConn->fetch_all_rows($checkEnumSql, array());

    if (!$enumResult || count($enumResult) == 0) {
        throw new Exception('Table tija_leave_accumulation_policies or column accrualType not found');
    }

    $currentEnum = $enumResult[0]['COLUMN_TYPE'];

    // Check if migration is needed
    if (strpos($currentEnum, 'Front-Loaded') !== false &&
        strpos($currentEnum, 'Periodic') !== false &&
        strpos($currentEnum, 'Proration') !== false) {
        $results[] = "⏭️ Accrual type enum already migrated. Current values: {$currentEnum}";
    } else {
        // Step 1: Map existing data to new values
        $mappingSql = "UPDATE `tija_leave_accumulation_policies`
                       SET `accrualType` = CASE
                           WHEN `accrualType` = 'Annual' THEN 'Front-Loaded'
                           WHEN `accrualType` IN ('Monthly', 'Quarterly', 'Continuous') THEN 'Periodic'
                           ELSE 'Periodic'
                       END
                       WHERE `accrualType` IN ('Monthly', 'Quarterly', 'Annual', 'Continuous')";

        try {
            $DBConn->query($mappingSql);
            if ($DBConn->execute()) {
                $affectedRows = $DBConn->rowCount();
                $results[] = "✅ Mapped {$affectedRows} existing accrual policies to new enum values";
            } else {
                $errors[] = "❌ Failed to map existing data: Query executed but returned false";
            }
        } catch (Exception $e) {
            $errors[] = "❌ Failed to map existing data: " . $e->getMessage();
        }

        // Step 2: Modify the enum column
        // MySQL requires dropping and recreating the column to change enum values
        $modifyEnumSql = "ALTER TABLE `tija_leave_accumulation_policies`
                          MODIFY COLUMN `accrualType`
                          ENUM('Front-Loaded', 'Periodic', 'Proration')
                          CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
                          NOT NULL DEFAULT 'Periodic'
                          COMMENT 'Accrual method: Front-Loaded (full amount upfront), Periodic (regular intervals), Proration (proportional)'";

        try {
            $DBConn->query($modifyEnumSql);
            if ($DBConn->execute()) {
                $results[] = "✅ Updated accrualType enum to (Front-Loaded, Periodic, Proration)";
            } else {
                $errors[] = "❌ Failed to modify enum: Query executed but returned false";
            }
        } catch (Exception $e) {
            $errors[] = "❌ Failed to modify enum: " . $e->getMessage();
        }
    }

    // Step 3: Add accrual period field for Periodic type
    $checkPeriodField = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS
                         WHERE TABLE_SCHEMA = DATABASE()
                         AND TABLE_NAME = 'tija_leave_accumulation_policies'
                         AND COLUMN_NAME = 'accrualPeriod'";

    $periodCheck = $DBConn->fetch_all_rows($checkPeriodField, array());

    if ($periodCheck && isset($periodCheck[0]) && $periodCheck[0]['count'] == 0) {
        $addPeriodSql = "ALTER TABLE `tija_leave_accumulation_policies`
                         ADD COLUMN `accrualPeriod` ENUM('Daily', 'Weekly', 'Bi-Weekly', 'Monthly', 'Quarterly', 'Bi-Annually', 'Annually')
                         CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Monthly'
                         COMMENT 'Accrual period for Periodic type'
                         AFTER `accrualType`";

        try {
            $DBConn->query($addPeriodSql);
            if ($DBConn->execute()) {
                $results[] = "✅ Added accrualPeriod field for Periodic accrual type";
            } else {
                $errors[] = "❌ Failed to add accrualPeriod field: Query executed but returned false";
            }
        } catch (Exception $e) {
            $errors[] = "❌ Failed to add accrualPeriod field: " . $e->getMessage();
        }
    } else {
        $results[] = "⏭️ accrualPeriod field already exists";
    }

    // Step 4: Add front-load date field for Front-Loaded type
    $checkFrontLoadField = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE TABLE_SCHEMA = DATABASE()
                            AND TABLE_NAME = 'tija_leave_accumulation_policies'
                            AND COLUMN_NAME = 'frontLoadDate'";

    $frontLoadCheck = $DBConn->fetch_all_rows($checkFrontLoadField, array());

    if ($frontLoadCheck && isset($frontLoadCheck[0]) && $frontLoadCheck[0]['count'] == 0) {
        $addFrontLoadSql = "ALTER TABLE `tija_leave_accumulation_policies`
                            ADD COLUMN `frontLoadDate` DATE DEFAULT NULL
                            COMMENT 'Date when front-loaded leave is granted (for Front-Loaded type)'
                            AFTER `accrualPeriod`";

        try {
            $DBConn->query($addFrontLoadSql);
            if ($DBConn->execute()) {
                $results[] = "✅ Added frontLoadDate field for Front-Loaded accrual type";
            } else {
                $errors[] = "❌ Failed to add frontLoadDate field: Query executed but returned false";
            }
        } catch (Exception $e) {
            $errors[] = "❌ Failed to add frontLoadDate field: " . $e->getMessage();
        }
    } else {
        $results[] = "⏭️ frontLoadDate field already exists";
    }

    // Step 5: Add proration basis field for Proration type
    $checkProrationField = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS
                            WHERE TABLE_SCHEMA = DATABASE()
                            AND TABLE_NAME = 'tija_leave_accumulation_policies'
                            AND COLUMN_NAME = 'prorationBasis'";

    $prorationCheck = $DBConn->fetch_all_rows($checkProrationField, array());

    if ($prorationCheck && isset($prorationCheck[0]) && $prorationCheck[0]['count'] == 0) {
        $addProrationSql = "ALTER TABLE `tija_leave_accumulation_policies`
                            ADD COLUMN `prorationBasis` ENUM('Days Worked', 'Months Worked', 'Service Period', 'Custom')
                            CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Days Worked'
                            COMMENT 'Basis for proration calculation'
                            AFTER `frontLoadDate`";

        try {
            $DBConn->query($addProrationSql);
            if ($DBConn->execute()) {
                $results[] = "✅ Added prorationBasis field for Proration accrual type";
            } else {
                $errors[] = "❌ Failed to add prorationBasis field: Query executed but returned false";
            }
        } catch (Exception $e) {
            $errors[] = "❌ Failed to add prorationBasis field: " . $e->getMessage();
        }
    } else {
        $results[] = "⏭️ prorationBasis field already exists";
    }

    $success = empty($errors);

    echo json_encode([
        'success' => $success,
        'message' => $success
            ? 'Accrual type migration completed successfully.'
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

