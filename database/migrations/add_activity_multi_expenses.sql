-- ============================================================================
-- Migration: Add Activity Multi-Expense Support
-- Description: Allows tracking multiple expense line items per activity
--              (e.g., travel, meals, materials separately)
-- Date: 2025-12-02
-- ============================================================================

USE pms_sbsl;

-- ============================================================================
-- Create Activity Expenses Table for Multiple Line Items
-- ============================================================================
CREATE TABLE IF NOT EXISTS `tija_activity_expenses` (
   `expenseID` INT NOT NULL AUTO_INCREMENT,
   `activityID` INT NOT NULL,
   `expenseDate` DATE NOT NULL,
   `expenseCategory` VARCHAR(100) NOT NULL,
   `expenseAmount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
   `expenseDescription` TEXT NULL,
   `expenseCurrency` VARCHAR(10) NOT NULL DEFAULT 'KES',
   `receiptNumber` VARCHAR(100) NULL,
   `receiptAttached` ENUM('Y','N') NOT NULL DEFAULT 'N',
   `receiptPath` VARCHAR(500) NULL,
   `paymentMethod` VARCHAR(50) NULL COMMENT 'Cash, Card, Mpesa, etc.',
   `reimbursable` ENUM('Y','N') NOT NULL DEFAULT 'Y',
   `reimbursementStatus` ENUM('pending','approved','rejected','paid') NOT NULL DEFAULT 'pending',
   `approvedBy` INT NULL,
   `approvedOn` DATETIME NULL,
   `paidOn` DATETIME NULL,
   `addedBy` INT NOT NULL,
   `addedOn` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
   `LastUpdate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
   `LastUpdatedByID` INT NOT NULL,
   `Suspended` ENUM('Y','N') NOT NULL DEFAULT 'N',
   PRIMARY KEY (`expenseID`),
   INDEX idx_activity_expenses (`activityID`),
   INDEX idx_expense_date (`expenseDate`),
   INDEX idx_expense_category (`expenseCategory`),
   INDEX idx_reimbursement_status (`reimbursementStatus`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Create Expense Categories Reference Table (Optional - for consistency)
-- ============================================================================
CREATE TABLE IF NOT EXISTS `tija_expense_categories` (
   `expenseCategoryID` INT NOT NULL AUTO_INCREMENT,
   `categoryName` VARCHAR(100) NOT NULL,
   `categoryDescription` TEXT NULL,
   `categoryIcon` VARCHAR(100) NULL,
   `categoryColor` VARCHAR(20) NULL,
   `requiresReceipt` ENUM('Y','N') NOT NULL DEFAULT 'Y',
   `maxAmount` DECIMAL(15,2) NULL COMMENT 'Maximum allowed per transaction',
   `orgDataID` INT NULL,
   `entityID` INT NULL,
   `Suspended` ENUM('Y','N') NOT NULL DEFAULT 'N',
   PRIMARY KEY (`expenseCategoryID`),
   UNIQUE KEY unique_category (`categoryName`, `orgDataID`, `entityID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default expense categories
INSERT INTO `tija_expense_categories` (`categoryName`, `categoryDescription`, `categoryIcon`, `categoryColor`, `requiresReceipt`, `maxAmount`) VALUES
('Travel', 'Transportation and mileage', 'ri-taxi-line', '#007bff', 'Y', NULL),
('Meals', 'Client entertainment and meals', 'ri-restaurant-line', '#28a745', 'Y', 5000.00),
('Materials', 'Sales collateral and materials', 'ri-file-copy-line', '#6c757d', 'Y', NULL),
('Accommodation', 'Hotel and lodging', 'ri-hotel-line', '#17a2b8', 'Y', NULL),
('Technology', 'Software, tools, subscriptions', 'ri-macbook-line', '#6f42c1', 'N', NULL),
('Communication', 'Phone, internet, data', 'ri-phone-line', '#fd7e14', 'Y', 2000.00),
('Parking', 'Parking fees', 'ri-parking-box-line', '#20c997', 'Y', 500.00),
('Fuel', 'Vehicle fuel', 'ri-gas-station-line', '#ffc107', 'Y', NULL),
('Gifts', 'Client gifts and giveaways', 'ri-gift-line', '#e83e8c', 'Y', 10000.00),
('Other', 'Other miscellaneous expenses', 'ri-more-line', '#6c757d', 'N', NULL)
ON DUPLICATE KEY UPDATE categoryDescription = VALUES(categoryDescription);

-- ============================================================================
-- Migrate Existing Single Expense Data to Multi-Expense Table
-- ============================================================================
-- Move existing cost data from tija_activities to tija_activity_expenses
INSERT INTO `tija_activity_expenses`
   (`activityID`, `expenseDate`, `expenseCategory`, `expenseAmount`, `expenseDescription`,
    `addedBy`, `addedOn`, `LastUpdatedByID`)
SELECT
   `activityID`,
   `activityDate` as expenseDate,
   COALESCE(`costCategory`, 'Other') as expenseCategory,
   COALESCE(`activityCost`, 0.00) as expenseAmount,
   `costNotes` as expenseDescription,
   COALESCE(`activityOwnerID`, 1) as addedBy,
   `DateAdded` as addedOn,
   COALESCE(`LastUpdateByID`, 1) as LastUpdatedByID
FROM `tija_activities`
WHERE `activityCost` > 0 AND `activityCost` IS NOT NULL
AND NOT EXISTS (
   SELECT 1 FROM `tija_activity_expenses`
   WHERE `tija_activity_expenses`.`activityID` = `tija_activities`.`activityID`
);

-- ============================================================================
-- Optional: Keep original fields for backward compatibility
-- Or deprecate them by adding a comment
-- ============================================================================
ALTER TABLE `tija_activities`
MODIFY COLUMN `activityCost` DECIMAL(15,2) NULL DEFAULT 0.00 COMMENT 'Deprecated: Use tija_activity_expenses table',
MODIFY COLUMN `costCategory` VARCHAR(100) NULL COMMENT 'Deprecated: Use tija_activity_expenses table',
MODIFY COLUMN `costNotes` TEXT NULL COMMENT 'Deprecated: Use tija_activity_expenses table';

-- ============================================================================
-- Create View for Easy Total Expense Calculation
-- ============================================================================
CREATE OR REPLACE VIEW `view_activity_expense_totals` AS
SELECT
   ae.activityID,
   COUNT(ae.expenseID) as expenseCount,
   SUM(ae.expenseAmount) as totalExpenses,
   SUM(CASE WHEN ae.reimbursable = 'Y' THEN ae.expenseAmount ELSE 0 END) as totalReimbursable,
   SUM(CASE WHEN ae.reimbursable = 'N' THEN ae.expenseAmount ELSE 0 END) as totalNonReimbursable,
   SUM(CASE WHEN ae.reimbursementStatus = 'pending' THEN ae.expenseAmount ELSE 0 END) as pendingReimbursement,
   SUM(CASE WHEN ae.reimbursementStatus = 'approved' THEN ae.expenseAmount ELSE 0 END) as approvedReimbursement,
   SUM(CASE WHEN ae.reimbursementStatus = 'paid' THEN ae.expenseAmount ELSE 0 END) as paidReimbursement
FROM `tija_activity_expenses` ae
WHERE ae.Suspended = 'N'
GROUP BY ae.activityID;

-- ============================================================================
-- SUCCESS MESSAGE
-- ============================================================================
SELECT 'Multi-expense system migration completed successfully!' AS Status,
       COUNT(*) AS MigratedExpenses
FROM `tija_activity_expenses`;


