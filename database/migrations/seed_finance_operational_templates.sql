-- Finance & Accounting Operational Task Templates
-- Based on research document and APQC taxonomy

-- Month-End Close: Cash Reconciliation (8.6.1)
INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfMonth, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'FIN-MEC-001', 'Month-End Cash Reconciliation',
    'Reconcile all cash accounts and bank statements at month-end. Verify all transactions, identify discrepancies, and prepare reconciliation reports.',
    '8.6.1', 'Finance', 'monthly', 1, 1, 4.0, 'cron', 'Y',
    '{"type": "function_head", "functionalArea": "Finance"}'
);

-- Month-End Close: AP Ledger Review (8.3.1)
INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfMonth, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'FIN-MEC-002', 'Month-End AP Ledger Review',
    'Review accounts payable ledger for accuracy. Verify all invoices are properly recorded, check for duplicates, and ensure proper coding.',
    '8.3.1', 'Finance', 'monthly', 1, 2, 3.0, 'cron', 'Y',
    '{"type": "function_head", "functionalArea": "Finance"}'
);

-- Month-End Close: Fixed Asset Depreciation (8.3.3)
INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfMonth, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'FIN-MEC-003', 'Calculate Fixed Asset Depreciation',
    'Calculate and record monthly depreciation for all fixed assets. Update asset register and general ledger.',
    '8.3.3', 'Finance', 'monthly', 1, 3, 2.0, 'cron', 'Y',
    '{"type": "function_head", "functionalArea": "Finance"}'
);

-- Month-End Close: Accruals & Prepayments (8.3.3)
INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfMonth, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'FIN-MEC-004', 'Process Accruals and Prepayments',
    'Review and process month-end accruals and prepayments. Ensure all expenses and revenues are recorded in the correct period.',
    '8.3.3', 'Finance', 'monthly', 1, 4, 3.0, 'cron', 'Y',
    '{"type": "function_head", "functionalArea": "Finance"}'
);

-- Accounts Payable Processing (8.3.1)
INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfWeek, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'FIN-AP-001', 'Process Accounts Payable',
    'Review, verify, and process vendor invoices. Match invoices to purchase orders, obtain approvals, and schedule payments.',
    '8.3.1', 'Finance', 'weekly', 1, 1, 8.0, 'both', 'Y',
    '{"type": "role", "roleID": "AP_CLERK"}'
);

-- Financial Reporting (8.4)
INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfMonth, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'FIN-REP-001', 'Prepare Monthly Financial Reports',
    'Compile and prepare monthly financial statements including P&L, Balance Sheet, and Cash Flow statements.',
    '8.4', 'Finance', 'monthly', 1, 5, 6.0, 'cron', 'Y',
    '{"type": "function_head", "functionalArea": "Finance"}'
);

-- Add checklist items for Cash Reconciliation
INSERT INTO tija_operational_task_checklists (templateID, itemOrder, itemDescription, isMandatory)
SELECT templateID, 1, 'Obtain bank statements for all accounts', 'Y'
FROM tija_operational_task_templates WHERE templateCode = 'FIN-MEC-001';

INSERT INTO tija_operational_task_checklists (templateID, itemOrder, itemDescription, isMandatory)
SELECT templateID, 2, 'Compare bank balances to general ledger', 'Y'
FROM tija_operational_task_templates WHERE templateCode = 'FIN-MEC-001';

INSERT INTO tija_operational_task_checklists (templateID, itemOrder, itemDescription, isMandatory)
SELECT templateID, 3, 'Identify and document all discrepancies', 'Y'
FROM tija_operational_task_templates WHERE templateCode = 'FIN-MEC-001';

INSERT INTO tija_operational_task_checklists (templateID, itemOrder, itemDescription, isMandatory)
SELECT templateID, 4, 'Prepare reconciliation report', 'Y'
FROM tija_operational_task_templates WHERE templateCode = 'FIN-MEC-001';

INSERT INTO tija_operational_task_checklists (templateID, itemOrder, itemDescription, isMandatory)
SELECT templateID, 5, 'Obtain manager approval', 'Y'
FROM tija_operational_task_templates WHERE templateCode = 'FIN-MEC-001';

