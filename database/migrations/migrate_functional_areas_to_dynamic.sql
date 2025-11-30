-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Migrate Functional Areas from ENUM to Dynamic Table
-- Purpose: Convert hardcoded ENUM columns to foreign keys to functional areas table
-- Date: 2025-11-29
-- ────────────────────────────────────────────────────────────────────────────
-- WARNING: This migration requires the functional areas table to exist first
-- Run create_functional_areas_tables.sql before this migration

-- Step 1: Add new functionalAreaID columns to all tables that use functionalArea ENUM
-- We'll keep the old column temporarily for data migration

-- tija_bau_processes
ALTER TABLE `tija_bau_processes`
ADD COLUMN `functionalAreaID` INT UNSIGNED NULL COMMENT 'FK to tija_functional_areas' AFTER `functionalArea`,
ADD INDEX `idx_functionalAreaID` (`functionalAreaID`),
ADD FOREIGN KEY (`functionalAreaID`) REFERENCES `tija_functional_areas`(`functionalAreaID`) ON DELETE RESTRICT;

-- tija_workflows
ALTER TABLE `tija_workflows`
ADD COLUMN `functionalAreaID` INT UNSIGNED NULL COMMENT 'FK to tija_functional_areas' AFTER `functionalArea`,
ADD INDEX `idx_functionalAreaID` (`functionalAreaID`),
ADD FOREIGN KEY (`functionalAreaID`) REFERENCES `tija_functional_areas`(`functionalAreaID`) ON DELETE RESTRICT;

-- tija_sops
ALTER TABLE `tija_sops`
ADD COLUMN `functionalAreaID` INT UNSIGNED NULL COMMENT 'FK to tija_functional_areas' AFTER `functionalArea`,
ADD INDEX `idx_functionalAreaID` (`functionalAreaID`),
ADD FOREIGN KEY (`functionalAreaID`) REFERENCES `tija_functional_areas`(`functionalAreaID`) ON DELETE RESTRICT;

-- tija_operational_task_templates
ALTER TABLE `tija_operational_task_templates`
ADD COLUMN `functionalAreaID` INT UNSIGNED NULL COMMENT 'FK to tija_functional_areas' AFTER `functionalArea`,
ADD INDEX `idx_functionalAreaID` (`functionalAreaID`),
ADD FOREIGN KEY (`functionalAreaID`) REFERENCES `tija_functional_areas`(`functionalAreaID`) ON DELETE RESTRICT;

-- tija_operational_projects
ALTER TABLE `tija_operational_projects`
ADD COLUMN `functionalAreaID` INT UNSIGNED NULL COMMENT 'FK to tija_functional_areas' AFTER `functionalArea`,
ADD INDEX `idx_functionalAreaID` (`functionalAreaID`),
ADD FOREIGN KEY (`functionalAreaID`) REFERENCES `tija_functional_areas`(`functionalAreaID`) ON DELETE RESTRICT;

-- tija_function_head_assignments
ALTER TABLE `tija_function_head_assignments`
ADD COLUMN `functionalAreaID` INT UNSIGNED NULL COMMENT 'FK to tija_functional_areas' AFTER `functionalArea`,
ADD INDEX `idx_functionalAreaID` (`functionalAreaID`),
ADD FOREIGN KEY (`functionalAreaID`) REFERENCES `tija_functional_areas`(`functionalAreaID`) ON DELETE RESTRICT;

-- Step 2: Migrate existing data from ENUM to functionalAreaID
UPDATE `tija_bau_processes` p
INNER JOIN `tija_functional_areas` fa ON
    CASE p.functionalArea
        WHEN 'Finance' THEN fa.functionalAreaCode = 'FIN'
        WHEN 'HR' THEN fa.functionalAreaCode = 'HR'
        WHEN 'IT' THEN fa.functionalAreaCode = 'IT'
        WHEN 'Sales' THEN fa.functionalAreaCode = 'SALES'
        WHEN 'Marketing' THEN fa.functionalAreaCode = 'MKTG'
        WHEN 'Legal' THEN fa.functionalAreaCode = 'LEGAL'
        WHEN 'Facilities' THEN fa.functionalAreaCode = 'FAC'
        WHEN 'Custom' THEN fa.functionalAreaCode = 'CUSTOM'
    END
SET p.functionalAreaID = fa.functionalAreaID;

UPDATE `tija_workflows` w
INNER JOIN `tija_functional_areas` fa ON
    CASE w.functionalArea
        WHEN 'Finance' THEN fa.functionalAreaCode = 'FIN'
        WHEN 'HR' THEN fa.functionalAreaCode = 'HR'
        WHEN 'IT' THEN fa.functionalAreaCode = 'IT'
        WHEN 'Sales' THEN fa.functionalAreaCode = 'SALES'
        WHEN 'Marketing' THEN fa.functionalAreaCode = 'MKTG'
        WHEN 'Legal' THEN fa.functionalAreaCode = 'LEGAL'
        WHEN 'Facilities' THEN fa.functionalAreaCode = 'FAC'
        WHEN 'Custom' THEN fa.functionalAreaCode = 'CUSTOM'
    END
SET w.functionalAreaID = fa.functionalAreaID;

UPDATE `tija_sops` s
INNER JOIN `tija_functional_areas` fa ON
    CASE s.functionalArea
        WHEN 'Finance' THEN fa.functionalAreaCode = 'FIN'
        WHEN 'HR' THEN fa.functionalAreaCode = 'HR'
        WHEN 'IT' THEN fa.functionalAreaCode = 'IT'
        WHEN 'Sales' THEN fa.functionalAreaCode = 'SALES'
        WHEN 'Marketing' THEN fa.functionalAreaCode = 'MKTG'
        WHEN 'Legal' THEN fa.functionalAreaCode = 'LEGAL'
        WHEN 'Facilities' THEN fa.functionalAreaCode = 'FAC'
        WHEN 'Custom' THEN fa.functionalAreaCode = 'CUSTOM'
    END
SET s.functionalAreaID = fa.functionalAreaID;

UPDATE `tija_operational_task_templates` t
INNER JOIN `tija_functional_areas` fa ON
    CASE t.functionalArea
        WHEN 'Finance' THEN fa.functionalAreaCode = 'FIN'
        WHEN 'HR' THEN fa.functionalAreaCode = 'HR'
        WHEN 'IT' THEN fa.functionalAreaCode = 'IT'
        WHEN 'Sales' THEN fa.functionalAreaCode = 'SALES'
        WHEN 'Marketing' THEN fa.functionalAreaCode = 'MKTG'
        WHEN 'Legal' THEN fa.functionalAreaCode = 'LEGAL'
        WHEN 'Facilities' THEN fa.functionalAreaCode = 'FAC'
        WHEN 'Custom' THEN fa.functionalAreaCode = 'CUSTOM'
    END
SET t.functionalAreaID = fa.functionalAreaID;

UPDATE `tija_operational_projects` op
INNER JOIN `tija_functional_areas` fa ON
    CASE op.functionalArea
        WHEN 'Finance' THEN fa.functionalAreaCode = 'FIN'
        WHEN 'HR' THEN fa.functionalAreaCode = 'HR'
        WHEN 'IT' THEN fa.functionalAreaCode = 'IT'
        WHEN 'Sales' THEN fa.functionalAreaCode = 'SALES'
        WHEN 'Marketing' THEN fa.functionalAreaCode = 'MKTG'
        WHEN 'Legal' THEN fa.functionalAreaCode = 'LEGAL'
        WHEN 'Facilities' THEN fa.functionalAreaCode = 'FAC'
        WHEN 'Custom' THEN fa.functionalAreaCode = 'CUSTOM'
    END
SET op.functionalAreaID = fa.functionalAreaID;

UPDATE `tija_function_head_assignments` fha
INNER JOIN `tija_functional_areas` fa ON
    CASE fha.functionalArea
        WHEN 'Finance' THEN fa.functionalAreaCode = 'FIN'
        WHEN 'HR' THEN fa.functionalAreaCode = 'HR'
        WHEN 'IT' THEN fa.functionalAreaCode = 'IT'
        WHEN 'Sales' THEN fa.functionalAreaCode = 'SALES'
        WHEN 'Marketing' THEN fa.functionalAreaCode = 'MKTG'
        WHEN 'Legal' THEN fa.functionalAreaCode = 'LEGAL'
        WHEN 'Facilities' THEN fa.functionalAreaCode = 'FAC'
        WHEN 'Custom' THEN fa.functionalAreaCode = 'CUSTOM'
    END
SET fha.functionalAreaID = fa.functionalAreaID;

-- Step 3: Make functionalAreaID NOT NULL after migration (optional - can be done later)
-- ALTER TABLE `tija_bau_processes` MODIFY `functionalAreaID` INT UNSIGNED NOT NULL;
-- ALTER TABLE `tija_workflows` MODIFY `functionalAreaID` INT UNSIGNED NOT NULL;
-- ALTER TABLE `tija_sops` MODIFY `functionalAreaID` INT UNSIGNED NOT NULL;
-- ALTER TABLE `tija_operational_task_templates` MODIFY `functionalAreaID` INT UNSIGNED NOT NULL;
-- ALTER TABLE `tija_operational_projects` MODIFY `functionalAreaID` INT UNSIGNED NOT NULL;
-- ALTER TABLE `tija_function_head_assignments` MODIFY `functionalAreaID` INT UNSIGNED NOT NULL;

-- Note: The old ENUM columns are kept for backward compatibility during transition
-- They can be removed in a future migration after all code is updated to use functionalAreaID

