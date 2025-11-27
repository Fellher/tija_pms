-- ============================================================================
-- ENTERPRISE INVOICING SYSTEM - DATABASE SCHEMA
-- ============================================================================
-- This migration creates a comprehensive invoicing system integrated with
-- projects, tasks, work hours, and expenses.
--
-- IMPORTANT: Backup your database before running this script!
-- Created: 2025-01-XX
-- ============================================================================

-- ============================================================================
-- SECTION 1: Invoice Line Items Table
-- ============================================================================
-- Stores individual line items on invoices (projects, tasks, hours, expenses)

CREATE TABLE IF NOT EXISTS `tija_invoice_items` (
    `invoiceItemID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `invoiceID` INT NOT NULL COMMENT 'FK to tija_invoices',
    `itemType` ENUM('project', 'task', 'work_hours', 'expense', 'fee_expense', 'license', 'custom') NOT NULL COMMENT 'Type of invoice item',
    `itemReferenceID` INT NULL COMMENT 'ID of referenced item (projectID, taskID, expenseID, etc.)',
    `itemCode` VARCHAR(100) NULL COMMENT 'Item code/reference',
    `itemDescription` TEXT NOT NULL COMMENT 'Description of the item',
    `quantity` DECIMAL(10,2) DEFAULT 1.00 COMMENT 'Quantity (hours, units, etc.)',
    `unitPrice` DECIMAL(15,2) NOT NULL COMMENT 'Price per unit',
    `discountPercent` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Discount percentage',
    `discountAmount` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Discount amount',
    `taxPercent` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Tax percentage',
    `taxAmount` DECIMAL(15,2) DEFAULT 0.00 COMMENT 'Tax amount',
    `lineTotal` DECIMAL(15,2) NOT NULL COMMENT 'Total for this line item',
    `sortOrder` INT DEFAULT 0 COMMENT 'Display order',
    `metadata` JSON NULL COMMENT 'Additional item metadata (dates, employee info, etc.)',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `Suspended` ENUM('Y','N') DEFAULT 'N',
    INDEX `idx_invoice` (`invoiceID`),
    INDEX `idx_item_type` (`itemType`),
    INDEX `idx_reference` (`itemReferenceID`),
    INDEX `idx_suspended` (`Suspended`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Invoice line items linking to projects, tasks, hours, expenses';

-- ============================================================================
-- SECTION 2: Invoice Templates Table
-- ============================================================================
-- Stores reusable invoice templates for different invoice types

CREATE TABLE IF NOT EXISTS `tija_invoice_templates` (
    `templateID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `templateName` VARCHAR(255) NOT NULL COMMENT 'Template name',
    `templateCode` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Unique template code',
    `templateDescription` TEXT NULL COMMENT 'Template description',
    `templateType` ENUM('standard', 'hourly', 'expense', 'milestone', 'recurring', 'custom') DEFAULT 'standard',
    `headerHTML` TEXT NULL COMMENT 'Invoice header HTML',
    `footerHTML` TEXT NULL COMMENT 'Invoice footer HTML',
    `bodyHTML` TEXT NULL COMMENT 'Invoice body HTML template',
    `cssStyles` TEXT NULL COMMENT 'Custom CSS styles',
    `logoURL` VARCHAR(500) NULL COMMENT 'Company logo URL',
    `companyName` VARCHAR(255) NULL COMMENT 'Company name',
    `companyAddress` TEXT NULL COMMENT 'Company address',
    `companyPhone` VARCHAR(50) NULL COMMENT 'Company phone',
    `companyEmail` VARCHAR(255) NULL COMMENT 'Company email',
    `companyWebsite` VARCHAR(255) NULL COMMENT 'Company website',
    `companyTaxID` VARCHAR(100) NULL COMMENT 'Company tax ID/VAT number',
    `defaultTerms` TEXT NULL COMMENT 'Default payment terms',
    `defaultNotes` TEXT NULL COMMENT 'Default invoice notes',
    `currency` VARCHAR(3) DEFAULT 'KES' COMMENT 'Default currency',
    `taxEnabled` ENUM('Y','N') DEFAULT 'Y' COMMENT 'Enable tax calculation',
    `defaultTaxPercent` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Default tax percentage',
    `isDefault` ENUM('Y','N') DEFAULT 'N' COMMENT 'Is this the default template',
    `isActive` ENUM('Y','N') DEFAULT 'Y',
    `orgDataID` INT NOT NULL DEFAULT 1,
    `entityID` INT NOT NULL DEFAULT 1,
    `createdBy` INT NULL COMMENT 'User who created the template',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT NULL,
    `Suspended` ENUM('Y','N') DEFAULT 'N',
    INDEX `idx_template_code` (`templateCode`),
    INDEX `idx_template_type` (`templateType`),
    INDEX `idx_is_default` (`isDefault`),
    INDEX `idx_org_entity` (`orgDataID`, `entityID`),
    INDEX `idx_suspended` (`Suspended`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Invoice templates for different invoice types';

-- ============================================================================
-- SECTION 3: Invoice Payments Table
-- ============================================================================
-- Tracks payments received against invoices

CREATE TABLE IF NOT EXISTS `tija_invoice_payments` (
    `paymentID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `invoiceID` INT NOT NULL COMMENT 'FK to tija_invoices',
    `paymentNumber` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Payment reference number',
    `paymentDate` DATE NOT NULL COMMENT 'Date payment was received',
    `paymentAmount` DECIMAL(15,2) NOT NULL COMMENT 'Amount paid',
    `paymentMethod` ENUM('cash', 'bank_transfer', 'cheque', 'credit_card', 'mobile_money', 'other') DEFAULT 'bank_transfer',
    `paymentReference` VARCHAR(255) NULL COMMENT 'Payment reference (transaction ID, cheque number, etc.)',
    `bankAccountID` INT NULL COMMENT 'FK to bank account if applicable',
    `currency` VARCHAR(3) DEFAULT 'KES' COMMENT 'Payment currency',
    `exchangeRate` DECIMAL(10,4) DEFAULT 1.0000 COMMENT 'Exchange rate if different currency',
    `notes` TEXT NULL COMMENT 'Payment notes',
    `receivedBy` INT NULL COMMENT 'User who recorded the payment',
    `verifiedBy` INT NULL COMMENT 'User who verified the payment',
    `verificationDate` DATETIME NULL COMMENT 'When payment was verified',
    `status` ENUM('pending', 'verified', 'reversed', 'cancelled') DEFAULT 'pending',
    `orgDataID` INT NOT NULL DEFAULT 1,
    `entityID` INT NOT NULL DEFAULT 1,
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT NULL,
    `Suspended` ENUM('Y','N') DEFAULT 'N',
    INDEX `idx_invoice` (`invoiceID`),
    INDEX `idx_payment_number` (`paymentNumber`),
    INDEX `idx_payment_date` (`paymentDate`),
    INDEX `idx_status` (`status`),
    INDEX `idx_org_entity` (`orgDataID`, `entityID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Payments received against invoices';

-- ============================================================================
-- SECTION 4: Invoice Work Hours Mapping Table
-- ============================================================================
-- Links specific work hours/time logs to invoice items

CREATE TABLE IF NOT EXISTS `tija_invoice_work_hours` (
    `mappingID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `invoiceItemID` INT NOT NULL COMMENT 'FK to tija_invoice_items',
    `timelogID` INT NOT NULL COMMENT 'FK to tija_tasks_time_logs',
    `hoursBilled` DECIMAL(10,2) NOT NULL COMMENT 'Hours billed for this time log',
    `billingRate` DECIMAL(15,2) NOT NULL COMMENT 'Rate used for billing',
    `amount` DECIMAL(15,2) NOT NULL COMMENT 'Amount billed for this time log',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_invoice_item` (`invoiceItemID`),
    INDEX `idx_timelog` (`timelogID`),
    UNIQUE KEY `unique_item_timelog` (`invoiceItemID`, `timelogID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Maps work hours/time logs to invoice items';

-- ============================================================================
-- SECTION 5: Invoice Expense Mapping Table
-- ============================================================================
-- Links specific expenses to invoice items

CREATE TABLE IF NOT EXISTS `tija_invoice_expenses` (
    `mappingID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `invoiceItemID` INT NOT NULL COMMENT 'FK to tija_invoice_items',
    `expenseID` INT NULL COMMENT 'FK to tija_project_expenses',
    `feeExpenseID` INT NULL COMMENT 'FK to tija_project_fee_expenses',
    `amount` DECIMAL(15,2) NOT NULL COMMENT 'Amount billed for this expense',
    `markupPercent` DECIMAL(5,2) DEFAULT 0.00 COMMENT 'Markup percentage applied',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_invoice_item` (`invoiceItemID`),
    INDEX `idx_expense` (`expenseID`),
    INDEX `idx_fee_expense` (`feeExpenseID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Maps expenses to invoice items';

-- ============================================================================
-- SECTION 6: Invoice Licenses/Subscriptions Table
-- ============================================================================
-- Tracks licenses and subscriptions that can be billed

CREATE TABLE IF NOT EXISTS `tija_invoice_licenses` (
    `licenseID` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `licenseName` VARCHAR(255) NOT NULL COMMENT 'License/subscription name',
    `licenseCode` VARCHAR(100) NULL COMMENT 'License code/reference',
    `licenseType` ENUM('software', 'subscription', 'service', 'maintenance', 'other') DEFAULT 'software',
    `clientID` INT NULL COMMENT 'FK to tija_clients if client-specific',
    `projectID` INT NULL COMMENT 'FK to tija_projects if project-specific',
    `monthlyCost` DECIMAL(15,2) NULL COMMENT 'Monthly cost',
    `annualCost` DECIMAL(15,2) NULL COMMENT 'Annual cost',
    `startDate` DATE NULL COMMENT 'License start date',
    `endDate` DATE NULL COMMENT 'License end date',
    `renewalDate` DATE NULL COMMENT 'Next renewal date',
    `autoRenew` ENUM('Y','N') DEFAULT 'N' COMMENT 'Auto-renew license',
    `billingFrequency` ENUM('monthly', 'quarterly', 'annually', 'one_time') DEFAULT 'monthly',
    `isActive` ENUM('Y','N') DEFAULT 'Y',
    `description` TEXT NULL,
    `orgDataID` INT NOT NULL DEFAULT 1,
    `entityID` INT NOT NULL DEFAULT 1,
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `LastUpdatedByID` INT NULL,
    `Suspended` ENUM('Y','N') DEFAULT 'N',
    INDEX `idx_client` (`clientID`),
    INDEX `idx_project` (`projectID`),
    INDEX `idx_renewal` (`renewalDate`),
    INDEX `idx_active` (`isActive`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Licenses and subscriptions that can be billed';

-- ============================================================================
-- SECTION 7: Update tija_invoices Table (if needed)
-- ============================================================================
-- Add template reference and other fields if they don't exist
-- Note: MySQL doesn't support IF NOT EXISTS for ALTER TABLE ADD COLUMN
-- If columns already exist, you can skip this section or comment out individual lines

-- Check and add templateID column
SET @dbname = DATABASE();
SET @tablename = 'tija_invoices';
SET @columnname = 'templateID';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @dbname
        AND TABLE_NAME = @tablename
        AND COLUMN_NAME = @columnname
    ) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` INT NULL COMMENT ''FK to tija_invoice_templates'' AFTER `invoiceStatusID`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add subtotal column
SET @columnname = 'subtotal';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @dbname
        AND TABLE_NAME = @tablename
        AND COLUMN_NAME = @columnname
    ) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` DECIMAL(15,2) DEFAULT 0.00 COMMENT ''Subtotal before tax and discount'' AFTER `invoiceAmount`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add discountPercent column
SET @columnname = 'discountPercent';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @dbname
        AND TABLE_NAME = @tablename
        AND COLUMN_NAME = @columnname
    ) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` DECIMAL(5,2) DEFAULT 0.00 COMMENT ''Overall discount percentage'' AFTER `subtotal`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add discountAmount column
SET @columnname = 'discountAmount';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @dbname
        AND TABLE_NAME = @tablename
        AND COLUMN_NAME = @columnname
    ) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` DECIMAL(15,2) DEFAULT 0.00 COMMENT ''Overall discount amount'' AFTER `discountPercent`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add notes column
SET @columnname = 'notes';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @dbname
        AND TABLE_NAME = @tablename
        AND COLUMN_NAME = @columnname
    ) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` TEXT NULL COMMENT ''Invoice notes'' AFTER `totalAmount`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add terms column
SET @columnname = 'terms';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @dbname
        AND TABLE_NAME = @tablename
        AND COLUMN_NAME = @columnname
    ) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` TEXT NULL COMMENT ''Payment terms'' AFTER `notes`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add pdfURL column
SET @columnname = 'pdfURL';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @dbname
        AND TABLE_NAME = @tablename
        AND COLUMN_NAME = @columnname
    ) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` VARCHAR(500) NULL COMMENT ''Generated PDF URL'' AFTER `terms`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add sentDate column
SET @columnname = 'sentDate';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @dbname
        AND TABLE_NAME = @tablename
        AND COLUMN_NAME = @columnname
    ) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` DATETIME NULL COMMENT ''When invoice was sent'' AFTER `pdfURL`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add paidDate column
SET @columnname = 'paidDate';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @dbname
        AND TABLE_NAME = @tablename
        AND COLUMN_NAME = @columnname
    ) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` DATETIME NULL COMMENT ''When invoice was fully paid'' AFTER `sentDate`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add paidAmount column
SET @columnname = 'paidAmount';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @dbname
        AND TABLE_NAME = @tablename
        AND COLUMN_NAME = @columnname
    ) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` DECIMAL(15,2) DEFAULT 0.00 COMMENT ''Total amount paid'' AFTER `paidDate`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add outstandingAmount column
SET @columnname = 'outstandingAmount';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = @dbname
        AND TABLE_NAME = @tablename
        AND COLUMN_NAME = @columnname
    ) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tablename, '` ADD COLUMN `', @columnname, '` DECIMAL(15,2) DEFAULT 0.00 COMMENT ''Outstanding amount'' AFTER `paidAmount`')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add indexes (check if they exist first)
SET @indexname = 'idx_template';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = @dbname
        AND TABLE_NAME = @tablename
        AND INDEX_NAME = @indexname
    ) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tablename, '` ADD INDEX `', @indexname, '` (`templateID`)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @indexname = 'idx_sent_date';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = @dbname
        AND TABLE_NAME = @tablename
        AND INDEX_NAME = @indexname
    ) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tablename, '` ADD INDEX `', @indexname, '` (`sentDate`)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

SET @indexname = 'idx_paid_date';
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = @dbname
        AND TABLE_NAME = @tablename
        AND INDEX_NAME = @indexname
    ) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tablename, '` ADD INDEX `', @indexname, '` (`paidDate`)')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- ============================================================================
-- SECTION 8: Insert Default Invoice Template
-- ============================================================================

INSERT INTO `tija_invoice_templates` (
    `templateName`, `templateCode`, `templateDescription`, `templateType`,
    `isDefault`, `isActive`, `orgDataID`, `entityID`, `DateAdded`
) VALUES (
    'Standard Invoice Template',
    'STANDARD',
    'Default standard invoice template',
    'standard',
    'Y',
    'Y',
    1,
    1,
    NOW()
) ON DUPLICATE KEY UPDATE `templateName` = `templateName`;

-- ============================================================================
-- SECTION 9: Insert Default Invoice Statuses (if not exists)
-- ============================================================================

INSERT IGNORE INTO `tija_invoice_status` (`statusID`, `statusName`, `statusDescription`, `statusColor`, `isActive`, `sortOrder`) VALUES
(1, 'Draft', 'Invoice is in draft status', '#6c757d', 'Y', 1),
(2, 'Sent', 'Invoice has been sent to client', '#0d6efd', 'Y', 2),
(3, 'Paid', 'Invoice has been fully paid', '#198754', 'Y', 3),
(4, 'Partially Paid', 'Invoice has been partially paid', '#ffc107', 'Y', 4),
(5, 'Overdue', 'Invoice payment is overdue', '#dc3545', 'Y', 5),
(6, 'Cancelled', 'Invoice has been cancelled', '#6c757d', 'Y', 6),
(7, 'Pending Approval', 'Invoice pending approval', '#fd7e14', 'Y', 7),
(8, 'Refunded', 'Invoice has been refunded', '#6c757d', 'Y', 8);

-- ============================================================================
-- END OF SCHEMA
-- ============================================================================

