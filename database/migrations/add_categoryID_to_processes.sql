

-- Populate categoryID from processGroupID for existing records
UPDATE `tija_bau_processes` p
INNER JOIN `tija_bau_process_groups` pg ON p.`processGroupID` = pg.`processGroupID`
SET p.`categoryID` = pg.`categoryID`
WHERE p.`categoryID` IS NULL;

-- Make categoryID NOT NULL after populating
ALTER TABLE `tija_bau_processes`
MODIFY COLUMN `categoryID` INT UNSIGNED NOT NULL COMMENT 'FK to tija_bau_categories (denormalized from processGroup)';

