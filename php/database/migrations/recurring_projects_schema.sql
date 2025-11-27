-- ============================================================================
-- Recurring Projects Database Schema Migration
-- ============================================================================
-- This migration adds support for recurring projects with periodic billing cycles
--
-- IMPORTANT: Backup your database before running this script!
-- Run Date: 2025-01-XX
-- ============================================================================

-- ============================================================================
-- SECTION 1: Add Recurring Fields to tija_projects Table
-- ============================================================================

ALTER TABLE `tija_projects`
ADD COLUMN `isRecurring` ENUM('Y','N') DEFAULT 'N' AFTER `projectStatus`,
ADD COLUMN `recurrenceType` ENUM('weekly','monthly','quarterly','annually','custom') NULL AFTER `isRecurring`,
ADD COLUMN `recurrenceInterval` INT DEFAULT 1 COMMENT 'e.g., every 2 weeks' AFTER `recurrenceType`,
ADD COLUMN `recurrenceDayOfWeek` INT NULL COMMENT '1-7 for weekly, NULL for others' AFTER `recurrenceInterval`,
ADD COLUMN `recurrenceDayOfMonth` INT NULL COMMENT '1-31 for monthly/quarterly' AFTER `recurrenceDayOfWeek`,
ADD COLUMN `recurrenceMonthOfYear` INT NULL COMMENT '1-12 for annually' AFTER `recurrenceDayOfMonth`,
ADD COLUMN `recurrenceStartDate` DATE NULL AFTER `recurrenceMonthOfYear`,
ADD COLUMN `recurrenceEndDate` DATE NULL COMMENT 'NULL for indefinite' AFTER `recurrenceStartDate`,
ADD COLUMN `recurrenceCount` INT NULL COMMENT 'number of cycles, NULL for indefinite' AFTER `recurrenceEndDate`,
ADD COLUMN `planReuseMode` ENUM('same','customizable') DEFAULT 'same' AFTER `recurrenceCount`,
ADD COLUMN `teamAssignmentMode` ENUM('template','instance','both') DEFAULT 'template' AFTER `planReuseMode`,
ADD COLUMN `billingCycleAmount` DECIMAL(15,2) NULL COMMENT 'amount per billing cycle' AFTER `teamAssignmentMode`,
ADD COLUMN `autoGenerateInvoices` ENUM('Y','N') DEFAULT 'N' AFTER `billingCycleAmount`,
ADD COLUMN `invoiceDaysBeforeDue` INT DEFAULT 7 COMMENT 'days before cycle end to generate draft' AFTER `autoGenerateInvoices`;

-- Add index for recurring projects
ALTER TABLE `tija_projects` ADD INDEX `idx_recurring` (`isRecurring`, `recurrenceType`);

-- ============================================================================
-- SECTION 2: Create tija_recurring_project_billing_cycles Table
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tija_recurring_project_billing_cycles` (
    `billingCycleID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `projectID` INT NOT NULL,
    `cycleNumber` INT NOT NULL COMMENT '1, 2, 3...',
    `cycleStartDate` DATE NOT NULL,
    `cycleEndDate` DATE NOT NULL,
    `billingDate` DATE NOT NULL COMMENT 'when invoice should be generated',
    `dueDate` DATE NOT NULL COMMENT 'payment due date',
    `status` ENUM('upcoming','active','billing_due','invoiced','paid','overdue','cancelled') DEFAULT 'upcoming',
    `invoiceDraftID` INT NULL COMMENT 'FK to tija_invoices when draft created',
    `invoiceID` INT NULL COMMENT 'FK to tija_invoices when finalized',
    `amount` DECIMAL(15,2) NOT NULL,
    `hoursLogged` DECIMAL(10,2) DEFAULT 0,
    `notes` TEXT NULL,
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `Suspended` ENUM('Y','N') DEFAULT 'N',
    INDEX `idx_project` (`projectID`),
    INDEX `idx_status` (`status`),
    INDEX `idx_billing_date` (`billingDate`),
    INDEX `idx_due_date` (`dueDate`),
    INDEX `idx_cycle_dates` (`cycleStartDate`, `cycleEndDate`)
    -- Foreign key constraint commented out - uncomment after verifying tija_projects table exists
    -- FOREIGN KEY (`projectID`) REFERENCES `tija_projects`(`projectID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Billing cycles for recurring projects';

-- ============================================================================
-- SECTION 3: Create tija_recurring_project_plan_instances Table
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tija_recurring_project_plan_instances` (
    `planInstanceID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `billingCycleID` INT UNSIGNED NOT NULL,
    `projectID` INT NOT NULL,
    `phaseJSON` TEXT NULL COMMENT 'customized phases/tasks for this cycle',
    `isCustomized` ENUM('Y','N') DEFAULT 'N',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_cycle` (`billingCycleID`),
    INDEX `idx_project` (`projectID`)
    -- Foreign key constraints commented out - uncomment after verifying tables exist
    -- FOREIGN KEY (`billingCycleID`) REFERENCES `tija_recurring_project_billing_cycles`(`billingCycleID`) ON DELETE CASCADE,
    -- FOREIGN KEY (`projectID`) REFERENCES `tija_projects`(`projectID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Customized plan instances for recurring project billing cycles';

-- ============================================================================
-- SECTION 4: Create tija_recurring_project_team_assignments Table
-- ============================================================================

CREATE TABLE IF NOT EXISTS `tija_recurring_project_team_assignments` (
    `teamAssignmentID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `billingCycleID` INT UNSIGNED NOT NULL,
    `projectID` INT NOT NULL,
    `employeeID` INT NOT NULL,
    `role` VARCHAR(50) NULL COMMENT 'owner, manager, member',
    `hoursAllocated` DECIMAL(10,2) NULL,
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `Suspended` ENUM('Y','N') DEFAULT 'N',
    INDEX `idx_cycle` (`billingCycleID`),
    INDEX `idx_project` (`projectID`),
    INDEX `idx_employee` (`employeeID`),
    INDEX `idx_role` (`role`)
    -- Foreign key constraints commented out - uncomment after verifying tables exist
    -- FOREIGN KEY (`billingCycleID`) REFERENCES `tija_recurring_project_billing_cycles`(`billingCycleID`) ON DELETE CASCADE,
    -- FOREIGN KEY (`projectID`) REFERENCES `tija_projects`(`projectID`) ON DELETE CASCADE,
    -- FOREIGN KEY (`employeeID`) REFERENCES `people`(`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Team assignments for recurring project billing cycles';

-- ============================================================================
-- SECTION 5: Modify tija_tasks_time_logs Table
-- ============================================================================

-- Add billing cycle linkage to time logs
ALTER TABLE `tija_tasks_time_logs`
ADD COLUMN `billingCycleID` INT UNSIGNED NULL COMMENT 'FK to tija_recurring_project_billing_cycles' AFTER `recurringInstanceID`;

-- Add index for billing cycle
ALTER TABLE `tija_tasks_time_logs` ADD INDEX `idx_billing_cycle` (`billingCycleID`);

-- Add foreign key constraint if the column exists
-- Note: Foreign key will be added after verifying the column exists

-- ============================================================================
-- SECTION 6: Add Foreign Key for billingCycleID in time logs
-- ============================================================================

-- Add foreign key constraint for billingCycleID in time logs
-- This may fail if there are existing records with invalid billingCycleID values
-- In that case, clean up the data first, then run this:
-- ALTER TABLE `tija_tasks_time_logs`
-- ADD CONSTRAINT `fk_time_logs_billing_cycle`
-- FOREIGN KEY (`billingCycleID`) REFERENCES `tija_recurring_project_billing_cycles`(`billingCycleID`) ON DELETE SET NULL;

-- Note: The foreign key constraint for time logs is commented out to avoid errors
-- if the table structure differs. Uncomment and run separately after verifying
-- the column was added successfully and data is clean.

-- ============================================================================
-- SECTION 7: Add Foreign Key Constraints (Optional - Run after verifying tables exist)
-- ============================================================================
-- Uncomment and run these statements separately after verifying all tables exist
-- and the referenced tables (tija_projects, people) are available in your database

-- Add foreign key for billing cycles -> projects
-- ALTER TABLE `tija_recurring_project_billing_cycles`
-- ADD CONSTRAINT `fk_billing_cycles_project`
-- FOREIGN KEY (`projectID`) REFERENCES `tija_projects`(`projectID`) ON DELETE CASCADE;

-- Add foreign key for plan instances -> billing cycles
-- ALTER TABLE `tija_recurring_project_plan_instances`
-- ADD CONSTRAINT `fk_plan_instances_cycle`
-- FOREIGN KEY (`billingCycleID`) REFERENCES `tija_recurring_project_billing_cycles`(`billingCycleID`) ON DELETE CASCADE;

-- Add foreign key for plan instances -> projects
-- ALTER TABLE `tija_recurring_project_plan_instances`
-- ADD CONSTRAINT `fk_plan_instances_project`
-- FOREIGN KEY (`projectID`) REFERENCES `tija_projects`(`projectID`) ON DELETE CASCADE;

-- Add foreign key for team assignments -> billing cycles
-- ALTER TABLE `tija_recurring_project_team_assignments`
-- ADD CONSTRAINT `fk_team_assignments_cycle`
-- FOREIGN KEY (`billingCycleID`) REFERENCES `tija_recurring_project_billing_cycles`(`billingCycleID`) ON DELETE CASCADE;

-- Add foreign key for team assignments -> projects
-- ALTER TABLE `tija_recurring_project_team_assignments`
-- ADD CONSTRAINT `fk_team_assignments_project`
-- FOREIGN KEY (`projectID`) REFERENCES `tija_projects`(`projectID`) ON DELETE CASCADE;

-- Add foreign key for team assignments -> people
-- ALTER TABLE `tija_recurring_project_team_assignments`
-- ADD CONSTRAINT `fk_team_assignments_employee`
-- FOREIGN KEY (`employeeID`) REFERENCES `people`(`ID`) ON DELETE CASCADE;

-- ============================================================================
-- Migration Complete
-- ============================================================================
--
-- IMPORTANT: After running this migration successfully, verify that:
-- 1. All tables were created correctly
-- 2. The tija_projects table exists in your database
-- 3. The people table exists in your database
-- 4. Then uncomment and run the foreign key constraints in SECTION 7 above
-- ============================================================================

