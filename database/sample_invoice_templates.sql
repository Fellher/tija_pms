-- ============================================================================
-- Sample Invoice Templates
-- ============================================================================
-- This script creates sample invoice templates for first-time users
-- These templates can be customized by administrators
-- ============================================================================

-- Standard Invoice Template
INSERT INTO `tija_invoice_templates` (
    `templateName`, `templateCode`, `templateDescription`, `templateType`,
    `currency`, `taxEnabled`, `defaultTaxPercent`,
    `companyName`, `companyAddress`, `companyPhone`, `companyEmail`, `companyWebsite`, `companyTaxID`,
    `defaultTerms`, `defaultNotes`,
    `isDefault`, `isActive`, `orgDataID`, `entityID`, `createdBy`, `LastUpdatedByID`, `Suspended`
) VALUES (
    'Standard Invoice Template',
    'STD',
    'A professional standard invoice template suitable for most businesses',
    'standard',
    'KES',
    'Y',
    16.00,
    'Your Company Name',
    'Company Address\nCity, Country',
    '+254 XXX XXX XXX',
    'info@company.com',
    'www.company.com',
    'VAT-XXXXX',
    'Payment is due within 30 days of invoice date. Late payments may incur interest charges.',
    'Thank you for your business!',
    'Y',
    'Y',
    1,
    1,
    1,
    1,
    'N'
);

-- Hourly Billing Template
INSERT INTO `tija_invoice_templates` (
    `templateName`, `templateCode`, `templateDescription`, `templateType`,
    `currency`, `taxEnabled`, `defaultTaxPercent`,
    `companyName`, `companyAddress`, `companyPhone`, `companyEmail`, `companyWebsite`, `companyTaxID`,
    `defaultTerms`, `defaultNotes`,
    `isDefault`, `isActive`, `orgDataID`, `entityID`, `createdBy`, `LastUpdatedByID`, `Suspended`
) VALUES (
    'Hourly Billing Template',
    'HOURLY',
    'Template optimized for hourly billing and time-based services',
    'hourly',
    'KES',
    'Y',
    16.00,
    'Your Company Name',
    'Company Address\nCity, Country',
    '+254 XXX XXX XXX',
    'info@company.com',
    'www.company.com',
    'VAT-XXXXX',
    'Payment is due within 15 days of invoice date. Hourly rates are based on agreed terms.',
    'Hours are billed based on actual time worked and logged in the system.',
    'N',
    'Y',
    1,
    1,
    1,
    1,
    'N'
);

-- Expense-Based Template
INSERT INTO `tija_invoice_templates` (
    `templateName`, `templateCode`, `templateDescription`, `templateType`,
    `currency`, `taxEnabled`, `defaultTaxPercent`,
    `companyName`, `companyAddress`, `companyPhone`, `companyEmail`, `companyWebsite`, `companyTaxID`,
    `defaultTerms`, `defaultNotes`,
    `isDefault`, `isActive`, `orgDataID`, `entityID`, `createdBy`, `LastUpdatedByID`, `Suspended`
) VALUES (
    'Expense-Based Template',
    'EXPENSE',
    'Template designed for expense reimbursement and project expenses',
    'expense',
    'KES',
    'Y',
    16.00,
    'Your Company Name',
    'Company Address\nCity, Country',
    '+254 XXX XXX XXX',
    'info@company.com',
    'www.company.com',
    'VAT-XXXXX',
    'Payment is due within 30 days. All expenses must be supported by receipts.',
    'This invoice includes project-related expenses as per agreement.',
    'N',
    'Y',
    1,
    1,
    1,
    1,
    'N'
);

-- Milestone Payment Template
INSERT INTO `tija_invoice_templates` (
    `templateName`, `templateCode`, `templateDescription`, `templateType`,
    `currency`, `taxEnabled`, `defaultTaxPercent`,
    `companyName`, `companyAddress`, `companyPhone`, `companyEmail`, `companyWebsite`, `companyTaxID`,
    `defaultTerms`, `defaultNotes`,
    `isDefault`, `isActive`, `orgDataID`, `entityID`, `createdBy`, `LastUpdatedByID`, `Suspended`
) VALUES (
    'Milestone Payment Template',
    'MILESTONE',
    'Template for milestone-based project payments',
    'milestone',
    'KES',
    'Y',
    16.00,
    'Your Company Name',
    'Company Address\nCity, Country',
    '+254 XXX XXX XXX',
    'info@company.com',
    'www.company.com',
    'VAT-XXXXX',
    'Payment is due upon milestone completion and approval.',
    'This invoice represents payment for completed project milestone.',
    'N',
    'Y',
    1,
    1,
    1,
    1,
    'N'
);

-- Recurring Billing Template
INSERT INTO `tija_invoice_templates` (
    `templateName`, `templateCode`, `templateDescription`, `templateType`,
    `currency`, `taxEnabled`, `defaultTaxPercent`,
    `companyName`, `companyAddress`, `companyPhone`, `companyEmail`, `companyWebsite`, `companyTaxID`,
    `defaultTerms`, `defaultNotes`,
    `isDefault`, `isActive`, `orgDataID`, `entityID`, `createdBy`, `LastUpdatedByID`, `Suspended`
) VALUES (
    'Recurring Billing Template',
    'RECURRING',
    'Template for recurring subscription or retainer billing',
    'recurring',
    'KES',
    'Y',
    16.00,
    'Your Company Name',
    'Company Address\nCity, Country',
    '+254 XXX XXX XXX',
    'info@company.com',
    'www.company.com',
    'VAT-XXXXX',
    'This is a recurring invoice. Payment is due within 7 days of invoice date.',
    'This invoice is part of your recurring billing cycle.',
    'N',
    'Y',
    1,
    1,
    1,
    1,
    'N'
);

-- ============================================================================
-- Notes:
-- ============================================================================
-- 1. These templates are created with orgDataID=1 and entityID=1
--    Administrators should update these values or create organization-specific templates
-- 2. The 'Standard Invoice Template' is set as default (isDefault='Y')
-- 3. All templates are active by default (isActive='Y')
-- 4. Administrators can edit these templates or create new ones via the UI
-- 5. Company information should be updated to match your organization
-- ============================================================================

