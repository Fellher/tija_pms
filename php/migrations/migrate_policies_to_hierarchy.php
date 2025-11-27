<?php
/**
 * Database Migration: Hierarchical Leave Policy System
 *
 * Adds hierarchical policy support to leave accumulation policies and entitlements:
 * - Adds policyScope (Global, Entity, Cadre)
 * - Adds jobCategoryID and jobBandID for cadre-level policies
 * - Adds parentEntityID for global policies
 * - Migrates existing policies to Entity scope
 */

session_start();

// Determine base path - script is in php/migrations/, so go up 2 levels to root
// __DIR__ = php/migrations/, dirname(__DIR__) = php/, dirname(dirname(__DIR__)) = root
$base = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;
set_include_path($base);

// Include php/includes.php using absolute path
$includesPath = $base . 'php' . DIRECTORY_SEPARATOR . 'includes.php';
if (file_exists($includesPath)) {
    include $includesPath;
} else {
    // Fallback: try relative path
    $base = '../../';
    set_include_path($base);
    include 'php/includes.php';
}

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

// Helper function to get count value from result (handles both object and array)
function getCountValue($result) {
    if (is_object($result)) {
        return isset($result->count) ? $result->count : 0;
    } elseif (is_array($result)) {
        return isset($result['count']) ? $result['count'] : 0;
    }
    return 0;
}

try {
    // ============================================================================
    // PART 1: Update tija_leave_accumulation_policies table
    // ============================================================================

    // Check if policyScope column already exists
    $checkScopeField = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE()
                        AND TABLE_NAME = 'tija_leave_accumulation_policies'
                        AND COLUMN_NAME = 'policyScope'";

    $scopeCheck = $DBConn->fetch_all_rows($checkScopeField, array());

    if ($scopeCheck && isset($scopeCheck[0]) && getCountValue($scopeCheck[0]) == 0) {
        // Step 1: Modify entityID to allow NULL
        $modifyEntitySql = "ALTER TABLE `tija_leave_accumulation_policies`
                           MODIFY COLUMN `entityID` INT NULL
                           COMMENT 'Entity this policy applies to (NULL for global policies)'";

        try {
            $DBConn->query($modifyEntitySql);
            if ($DBConn->execute()) {
                $results[] = "✅ Modified entityID to allow NULL in tija_leave_accumulation_policies";
            } else {
                $errors[] = "❌ Failed to modify entityID: Query executed but returned false";
            }
        } catch (Exception $e) {
            $errors[] = "❌ Failed to modify entityID: " . $e->getMessage();
        }

        // Step 2: Add policyScope column
        $addScopeSql = "ALTER TABLE `tija_leave_accumulation_policies`
                       ADD COLUMN `policyScope` ENUM('Global', 'Entity', 'Cadre')
                       CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
                       DEFAULT 'Entity'
                       COMMENT 'Policy scope: Global (parent entity), Entity (specific entity), Cadre (job category/band)'
                       AFTER `entityID`";

        try {
            $DBConn->query($addScopeSql);
            if ($DBConn->execute()) {
                $results[] = "✅ Added policyScope column to tija_leave_accumulation_policies";
            } else {
                $errors[] = "❌ Failed to add policyScope: Query executed but returned false";
            }
        } catch (Exception $e) {
            $errors[] = "❌ Failed to add policyScope: " . $e->getMessage();
        }

        // Step 3: Add parentEntityID column
        $addParentEntitySql = "ALTER TABLE `tija_leave_accumulation_policies`
                               ADD COLUMN `parentEntityID` INT NULL
                               COMMENT 'Parent entity ID for global policies (entityParentID = 0)'
                               AFTER `entityID`";

        try {
            $DBConn->query($addParentEntitySql);
            if ($DBConn->execute()) {
                $results[] = "✅ Added parentEntityID column to tija_leave_accumulation_policies";
            } else {
                $errors[] = "❌ Failed to add parentEntityID: Query executed but returned false";
            }
        } catch (Exception $e) {
            $errors[] = "❌ Failed to add parentEntityID: " . $e->getMessage();
        }

        // Step 4: Add jobCategoryID column
        $addJobCategorySql = "ALTER TABLE `tija_leave_accumulation_policies`
                              ADD COLUMN `jobCategoryID` INT NULL
                              COMMENT 'Job category ID for cadre-level policies'
                              AFTER `leaveTypeID`";

        try {
            $DBConn->query($addJobCategorySql);
            if ($DBConn->execute()) {
                $results[] = "✅ Added jobCategoryID column to tija_leave_accumulation_policies";
            } else {
                $errors[] = "❌ Failed to add jobCategoryID: Query executed but returned false";
            }
        } catch (Exception $e) {
            $errors[] = "❌ Failed to add jobCategoryID: " . $e->getMessage();
        }

        // Step 5: Add jobBandID column
        $addJobBandSql = "ALTER TABLE `tija_leave_accumulation_policies`
                         ADD COLUMN `jobBandID` INT NULL
                         COMMENT 'Job band ID for cadre-level policies'
                         AFTER `jobCategoryID`";

        try {
            $DBConn->query($addJobBandSql);
            if ($DBConn->execute()) {
                $results[] = "✅ Added jobBandID column to tija_leave_accumulation_policies";
            } else {
                $errors[] = "❌ Failed to add jobBandID: Query executed but returned false";
            }
        } catch (Exception $e) {
            $errors[] = "❌ Failed to add jobBandID: " . $e->getMessage();
        }

        // Step 6: Add index for policy scope queries
        $addIndexSql = "ALTER TABLE `tija_leave_accumulation_policies`
                       ADD INDEX `idx_policy_scope` (`policyScope`, `entityID`, `jobCategoryID`, `jobBandID`)";

        try {
            $DBConn->query($addIndexSql);
            if ($DBConn->execute()) {
                $results[] = "✅ Added idx_policy_scope index to tija_leave_accumulation_policies";
            } else {
                $errors[] = "❌ Failed to add index: Query executed but returned false";
            }
        } catch (Exception $e) {
            $errors[] = "❌ Failed to add index: " . $e->getMessage();
        }

        // Step 7: Migrate existing data - set all to Entity scope
        $migrateDataSql = "UPDATE `tija_leave_accumulation_policies`
                          SET `policyScope` = 'Entity',
                              `parentEntityID` = NULL,
                              `jobCategoryID` = NULL,
                              `jobBandID` = NULL
                          WHERE `policyScope` IS NULL OR `policyScope` = ''";

        try {
            $DBConn->query($migrateDataSql);
            if ($DBConn->execute()) {
                $affectedRows = $DBConn->rowCount();
                $results[] = "✅ Migrated {$affectedRows} existing accumulation policies to Entity scope";
            } else {
                $errors[] = "❌ Failed to migrate data: Query executed but returned false";
            }
        } catch (Exception $e) {
            $errors[] = "❌ Failed to migrate data: " . $e->getMessage();
        }
    } else {
        $results[] = "⏭️ policyScope column already exists in tija_leave_accumulation_policies";
    }

    // ============================================================================
    // PART 2: Update tija_leave_entitlement table
    // ============================================================================

    // Check if policyScope column already exists in entitlements
    $checkEntitlementScopeField = "SELECT COUNT(*) as count FROM INFORMATION_SCHEMA.COLUMNS
                                   WHERE TABLE_SCHEMA = DATABASE()
                                   AND TABLE_NAME = 'tija_leave_entitlement'
                                   AND COLUMN_NAME = 'policyScope'";

    $entitlementScopeCheck = $DBConn->fetch_all_rows($checkEntitlementScopeField, array());

    if ($entitlementScopeCheck && isset($entitlementScopeCheck[0]) && getCountValue($entitlementScopeCheck[0]) == 0) {
        // Step 1: Modify entityID to allow NULL
        $modifyEntitlementEntitySql = "ALTER TABLE `tija_leave_entitlement`
                                      MODIFY COLUMN `entityID` INT NULL
                                      COMMENT 'Entity this entitlement applies to (NULL for global entitlements)'";

        try {
            $DBConn->query($modifyEntitlementEntitySql);
            if ($DBConn->execute()) {
                $results[] = "✅ Modified entityID to allow NULL in tija_leave_entitlement";
            } else {
                $errors[] = "❌ Failed to modify entityID in entitlements: Query executed but returned false";
            }
        } catch (Exception $e) {
            $errors[] = "❌ Failed to modify entityID in entitlements: " . $e->getMessage();
        }

        // Step 2: Add policyScope column
        $addEntitlementScopeSql = "ALTER TABLE `tija_leave_entitlement`
                                  ADD COLUMN `policyScope` ENUM('Global', 'Entity', 'Cadre')
                                  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
                                  DEFAULT 'Entity'
                                  COMMENT 'Policy scope: Global (parent entity), Entity (specific entity), Cadre (job category/band)'
                                  AFTER `entityID`";

        try {
            $DBConn->query($addEntitlementScopeSql);
            if ($DBConn->execute()) {
                $results[] = "✅ Added policyScope column to tija_leave_entitlement";
            } else {
                $errors[] = "❌ Failed to add policyScope to entitlements: Query executed but returned false";
            }
        } catch (Exception $e) {
            $errors[] = "❌ Failed to add policyScope to entitlements: " . $e->getMessage();
        }

        // Step 3: Add parentEntityID column
        $addEntitlementParentEntitySql = "ALTER TABLE `tija_leave_entitlement`
                                         ADD COLUMN `parentEntityID` INT NULL
                                         COMMENT 'Parent entity ID for global entitlements (entityParentID = 0)'
                                         AFTER `entityID`";

        try {
            $DBConn->query($addEntitlementParentEntitySql);
            if ($DBConn->execute()) {
                $results[] = "✅ Added parentEntityID column to tija_leave_entitlement";
            } else {
                $errors[] = "❌ Failed to add parentEntityID to entitlements: Query executed but returned false";
            }
        } catch (Exception $e) {
            $errors[] = "❌ Failed to add parentEntityID to entitlements: " . $e->getMessage();
        }

        // Step 4: Add jobCategoryID column
        $addEntitlementJobCategorySql = "ALTER TABLE `tija_leave_entitlement`
                                        ADD COLUMN `jobCategoryID` INT NULL
                                        COMMENT 'Job category ID for cadre-level entitlements'
                                        AFTER `leaveTypeID`";

        try {
            $DBConn->query($addEntitlementJobCategorySql);
            if ($DBConn->execute()) {
                $results[] = "✅ Added jobCategoryID column to tija_leave_entitlement";
            } else {
                $errors[] = "❌ Failed to add jobCategoryID to entitlements: Query executed but returned false";
            }
        } catch (Exception $e) {
            $errors[] = "❌ Failed to add jobCategoryID to entitlements: " . $e->getMessage();
        }

        // Step 5: Add jobBandID column
        $addEntitlementJobBandSql = "ALTER TABLE `tija_leave_entitlement`
                                    ADD COLUMN `jobBandID` INT NULL
                                    COMMENT 'Job band ID for cadre-level entitlements'
                                    AFTER `jobCategoryID`";

        try {
            $DBConn->query($addEntitlementJobBandSql);
            if ($DBConn->execute()) {
                $results[] = "✅ Added jobBandID column to tija_leave_entitlement";
            } else {
                $errors[] = "❌ Failed to add jobBandID to entitlements: Query executed but returned false";
            }
        } catch (Exception $e) {
            $errors[] = "❌ Failed to add jobBandID to entitlements: " . $e->getMessage();
        }

        // Step 6: Add index for policy scope queries
        $addEntitlementIndexSql = "ALTER TABLE `tija_leave_entitlement`
                                  ADD INDEX `idx_policy_scope` (`policyScope`, `entityID`, `jobCategoryID`, `jobBandID`)";

        try {
            $DBConn->query($addEntitlementIndexSql);
            if ($DBConn->execute()) {
                $results[] = "✅ Added idx_policy_scope index to tija_leave_entitlement";
            } else {
                $errors[] = "❌ Failed to add index to entitlements: Query executed but returned false";
            }
        } catch (Exception $e) {
            $errors[] = "❌ Failed to add index to entitlements: " . $e->getMessage();
        }

        // Step 7: Migrate existing data - set all to Entity scope
        $migrateEntitlementDataSql = "UPDATE `tija_leave_entitlement`
                                     SET `policyScope` = 'Entity',
                                         `parentEntityID` = NULL,
                                         `jobCategoryID` = NULL,
                                         `jobBandID` = NULL
                                     WHERE `policyScope` IS NULL OR `policyScope` = ''";

        try {
            $DBConn->query($migrateEntitlementDataSql);
            if ($DBConn->execute()) {
                $affectedRows = $DBConn->rowCount();
                $results[] = "✅ Migrated {$affectedRows} existing entitlements to Entity scope";
            } else {
                $errors[] = "❌ Failed to migrate entitlement data: Query executed but returned false";
            }
        } catch (Exception $e) {
            $errors[] = "❌ Failed to migrate entitlement data: " . $e->getMessage();
        }
    } else {
        $results[] = "⏭️ policyScope column already exists in tija_leave_entitlement";
    }

    $success = empty($errors);

    echo json_encode([
        'success' => $success,
        'message' => $success
            ? 'Hierarchical policy migration completed successfully.'
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

