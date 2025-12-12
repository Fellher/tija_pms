-- Migration: Add prospect to sale conversion tracking columns
-- Date: 2025-12-11
-- Description: Adds columns to track when prospects are converted to sales opportunities

ALTER TABLE tija_sales_prospects
ADD COLUMN convertedToSale ENUM('Y', 'N') DEFAULT 'N' COMMENT 'Whether prospect has been converted to a sale' AFTER salesProspectStatus,
ADD COLUMN salesCaseID INT NULL COMMENT 'ID of the created sale if converted' AFTER convertedToSale,
ADD COLUMN conversionDate DATETIME NULL COMMENT 'Date and time of conversion' AFTER salesCaseID,
ADD COLUMN convertedByID INT NULL COMMENT 'User ID who performed the conversion' AFTER conversionDate,
ADD INDEX idx_converted (convertedToSale, salesCaseID) COMMENT 'Index for querying converted prospects';

-- Add foreign key constraint if tija_sales_cases table exists
-- ALTER TABLE tija_sales_prospects
-- ADD CONSTRAINT fk_prospect_sale FOREIGN KEY (salesCaseID) REFERENCES tija_sales_cases(salesCaseID) ON DELETE SET NULL;
