-- Migration Script: Convert tija_roles.roleType and roleLevel to Foreign Keys
-- This script migrates existing data and alters the table structure

-- Step 1: Add new columns for foreign keys
ALTER TABLE `tija_roles`
ADD COLUMN `roleTypeID` INT(11) NULL AFTER `roleType`,
ADD COLUMN `roleLevelID` INT(11) NULL AFTER `roleLevel`;

-- Step 2: Migrate existing roleType data to roleTypeID
-- Match by roleTypeCode first, then by roleTypeName
UPDATE `tija_roles` r
LEFT JOIN `tija_org_role_types` rt ON (
    r.roleType = rt.roleTypeCode
    OR r.roleType = rt.roleTypeName
)
SET r.roleTypeID = rt.roleTypeID
WHERE rt.roleTypeID IS NOT NULL;

-- Step 3: Migrate existing roleLevel data to roleLevelID
-- Match by levelNumber
UPDATE `tija_roles` r
LEFT JOIN `tija_role_levels` rl ON r.roleLevel = rl.levelNumber
SET r.roleLevelID = rl.roleLevelID
WHERE rl.roleLevelID IS NOT NULL;

-- Step 4: Set default values for NULL entries (if any remain)
-- Default to "Operational" role type (usually ID 4 or find by code)
UPDATE `tija_roles`
SET roleTypeID = (SELECT roleTypeID FROM tija_org_role_types WHERE roleTypeCode = 'OPR' LIMIT 1)
WHERE roleTypeID IS NULL;

-- Default to "Supervisor" role level (usually levelNumber 5, roleLevelID varies)
UPDATE `tija_roles`
SET roleLevelID = (SELECT roleLevelID FROM tija_role_levels WHERE levelNumber = 5 LIMIT 1)
WHERE roleLevelID IS NULL;

-- Step 5: Make the new columns NOT NULL (after data migration)
ALTER TABLE `tija_roles`
MODIFY COLUMN `roleTypeID` INT(11) NOT NULL,
MODIFY COLUMN `roleLevelID` INT(11) NOT NULL;

-- Step 6: Add foreign key constraints
ALTER TABLE `tija_roles`
ADD CONSTRAINT `fk_roles_roleType`
    FOREIGN KEY (`roleTypeID`)
    REFERENCES `tija_org_role_types` (`roleTypeID`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
ADD CONSTRAINT `fk_roles_roleLevel`
    FOREIGN KEY (`roleLevelID`)
    REFERENCES `tija_role_levels` (`roleLevelID`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE;

-- Step 7: Add indexes for better performance
ALTER TABLE `tija_roles`
ADD INDEX `idx_roleTypeID` (`roleTypeID`),
ADD INDEX `idx_roleLevelID` (`roleLevelID`);

-- Step 8: (Optional) Drop old columns after verification
-- Uncomment these lines after verifying the migration worked correctly
-- ALTER TABLE `tija_roles` DROP COLUMN `roleType`;
-- ALTER TABLE `tija_roles` DROP COLUMN `roleLevel`;

-- Verification queries (run these to check migration)
-- SELECT COUNT(*) as total_roles FROM tija_roles;
-- SELECT COUNT(*) as roles_with_type FROM tija_roles WHERE roleTypeID IS NOT NULL;
-- SELECT COUNT(*) as roles_with_level FROM tija_roles WHERE roleLevelID IS NOT NULL;
-- SELECT r.roleID, r.roleName, rt.roleTypeName, rl.levelName
-- FROM tija_roles r
-- LEFT JOIN tija_org_role_types rt ON r.roleTypeID = rt.roleTypeID
-- LEFT JOIN tija_role_levels rl ON r.roleLevelID = rl.roleLevelID
-- LIMIT 10;

