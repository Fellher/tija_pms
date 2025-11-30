-- APQC Process Classification Framework Taxonomy Seeding
-- This script seeds the standard APQC taxonomy structure

-- Categories (Top-level domains)
INSERT INTO tija_bau_categories (categoryCode, categoryName, categoryDescription, displayOrder, isActive) VALUES
('1.0', 'Develop Vision and Strategy', 'Develop vision and strategy to guide the direction of the enterprise', 1, 'Y'),
('2.0', 'Develop and Manage Products and Services', 'Develop and manage products and services to meet market needs', 2, 'Y'),
('3.0', 'Market and Sell Products and Services', 'Market and sell products and services to customers', 3, 'Y'),
('4.0', 'Deliver Products and Services', 'Deliver products and services to customers', 4, 'Y'),
('5.0', 'Manage Customer Service', 'Manage customer service to ensure customer satisfaction', 5, 'Y'),
('6.0', 'Develop and Manage Human Capital', 'Develop and manage human capital to enable individual and organizational success', 6, 'Y'),
('7.0', 'Develop and Manage Human Capital', 'Develop and manage human capital to enable individual and organizational success', 7, 'Y'),
('8.0', 'Manage Financial Resources', 'Manage financial resources to ensure financial viability', 8, 'Y'),
('9.0', 'Acquire, Construct, and Manage Property', 'Acquire, construct, and manage property to support operations', 9, 'Y'),
('10.0', 'Manage Information Technology', 'Manage information technology to support business processes', 10, 'Y'),
('11.0', 'Manage Enterprise Risk, Compliance, Remediation, and Resiliency', 'Manage enterprise risk, compliance, remediation, and resiliency', 11, 'Y'),
('12.0', 'Manage External Relationships', 'Manage external relationships to support business objectives', 12, 'Y')
ON DUPLICATE KEY UPDATE categoryName = VALUES(categoryName);

-- Process Groups for Category 6.0 (Develop and Manage Human Capital)
INSERT INTO tija_bau_process_groups (categoryID, processGroupCode, processGroupName, processGroupDescription, displayOrder, isActive)
SELECT categoryID, '6.1', 'Develop Human Capital Strategy', 'Develop human capital strategy aligned with business objectives', 1, 'Y'
FROM tija_bau_categories WHERE categoryCode = '6.0'
ON DUPLICATE KEY UPDATE processGroupName = VALUES(processGroupName);

INSERT INTO tija_bau_process_groups (categoryID, processGroupCode, processGroupName, processGroupDescription, displayOrder, isActive)
SELECT categoryID, '6.2', 'Attract, Source, and Select Talent', 'Attract, source, and select talent to meet workforce needs', 2, 'Y'
FROM tija_bau_categories WHERE categoryCode = '6.0'
ON DUPLICATE KEY UPDATE processGroupName = VALUES(processGroupName);

INSERT INTO tija_bau_process_groups (categoryID, processGroupCode, processGroupName, processGroupDescription, displayOrder, isActive)
SELECT categoryID, '6.3', 'Develop and Deploy People', 'Develop and deploy people to build workforce capability', 3, 'Y'
FROM tija_bau_categories WHERE categoryCode = '6.0'
ON DUPLICATE KEY UPDATE processGroupName = VALUES(processGroupName);

-- Process Groups for Category 8.0 (Manage Financial Resources)
INSERT INTO tija_bau_process_groups (categoryID, processGroupCode, processGroupName, processGroupDescription, displayOrder, isActive)
SELECT categoryID, '8.1', 'Develop Financial Strategy and Plans', 'Develop financial strategy and plans to guide financial decisions', 1, 'Y'
FROM tija_bau_categories WHERE categoryCode = '8.0'
ON DUPLICATE KEY UPDATE processGroupName = VALUES(processGroupName);

INSERT INTO tija_bau_process_groups (categoryID, processGroupCode, processGroupName, processGroupDescription, displayOrder, isActive)
SELECT categoryID, '8.2', 'Manage Financial Resources', 'Manage financial resources to ensure financial viability', 2, 'Y'
FROM tija_bau_categories WHERE categoryCode = '8.0'
ON DUPLICATE KEY UPDATE processGroupName = VALUES(processGroupName);

INSERT INTO tija_bau_process_groups (categoryID, processGroupCode, processGroupName, processGroupDescription, displayOrder, isActive)
SELECT categoryID, '8.3', 'Process Financial Transactions', 'Process financial transactions accurately and efficiently', 3, 'Y'
FROM tija_bau_categories WHERE categoryCode = '8.0'
ON DUPLICATE KEY UPDATE processGroupName = VALUES(processGroupName);

INSERT INTO tija_bau_process_groups (categoryID, processGroupCode, processGroupName, processGroupDescription, displayOrder, isActive)
SELECT categoryID, '8.4', 'Report Financial Information', 'Report financial information to stakeholders', 4, 'Y'
FROM tija_bau_categories WHERE categoryCode = '8.0'
ON DUPLICATE KEY UPDATE processGroupName = VALUES(processGroupName);

INSERT INTO tija_bau_process_groups (categoryID, processGroupCode, processGroupName, processGroupDescription, displayOrder, isActive)
SELECT categoryID, '8.5', 'Manage Financial Risk', 'Manage financial risk to protect financial resources', 5, 'Y'
FROM tija_bau_categories WHERE categoryCode = '8.0'
ON DUPLICATE KEY UPDATE processGroupName = VALUES(processGroupName);

INSERT INTO tija_bau_process_groups (categoryID, processGroupCode, processGroupName, processGroupDescription, displayOrder, isActive)
SELECT categoryID, '8.6', 'Manage Treasury Operations', 'Manage treasury operations to optimize cash and liquidity', 6, 'Y'
FROM tija_bau_categories WHERE categoryCode = '8.0'
ON DUPLICATE KEY UPDATE processGroupName = VALUES(processGroupName);

-- Processes for 6.3 (Develop and Deploy People)
INSERT INTO tija_bau_processes (processGroupID, processCode, processID, processName, processDescription, functionalArea, isActive, isCustom)
SELECT pg.processGroupID, '6.3.1', '6.3.1', 'Manage Payroll', 'Process payroll accurately and on time for all employees', 'HR', 'Y', 'N'
FROM tija_bau_process_groups pg
JOIN tija_bau_categories c ON pg.categoryID = c.categoryID
WHERE pg.processGroupCode = '6.3' AND c.categoryCode = '6.0'
ON DUPLICATE KEY UPDATE processName = VALUES(processName);

INSERT INTO tija_bau_processes (processGroupID, processCode, processID, processName, processDescription, functionalArea, isActive, isCustom)
SELECT pg.processGroupID, '6.3.2', '6.3.2', 'Manage Employee Benefits', 'Administer employee benefits programs', 'HR', 'Y', 'N'
FROM tija_bau_process_groups pg
JOIN tija_bau_categories c ON pg.categoryID = c.categoryID
WHERE pg.processGroupCode = '6.3' AND c.categoryCode = '6.0'
ON DUPLICATE KEY UPDATE processName = VALUES(processName);

-- Processes for 8.6 (Manage Treasury Operations)
INSERT INTO tija_bau_processes (processGroupID, processCode, processID, processName, processDescription, functionalArea, isActive, isCustom)
SELECT pg.processGroupID, '8.6.1', '8.6.1', 'Manage Cash', 'Manage cash to ensure adequate liquidity', 'Finance', 'Y', 'N'
FROM tija_bau_process_groups pg
JOIN tija_bau_categories c ON pg.categoryID = c.categoryID
WHERE pg.processGroupCode = '8.6' AND c.categoryCode = '8.0'
ON DUPLICATE KEY UPDATE processName = VALUES(processName);

INSERT INTO tija_bau_processes (processGroupID, processCode, processID, processName, processDescription, functionalArea, isActive, isCustom)
SELECT pg.processGroupID, '8.6.2', '8.6.2', 'Reconcile Bank Accounts', 'Reconcile bank accounts to ensure accuracy', 'Finance', 'Y', 'N'
FROM tija_bau_process_groups pg
JOIN tija_bau_categories c ON pg.categoryID = c.categoryID
WHERE pg.processGroupCode = '8.6' AND c.categoryCode = '8.0'
ON DUPLICATE KEY UPDATE processName = VALUES(processName);

-- Processes for 8.3 (Process Financial Transactions)
INSERT INTO tija_bau_processes (processGroupID, processCode, processID, processName, processDescription, functionalArea, isActive, isCustom)
SELECT pg.processGroupID, '8.3.1', '8.3.1', 'Process Accounts Payable', 'Process accounts payable transactions', 'Finance', 'Y', 'N'
FROM tija_bau_process_groups pg
JOIN tija_bau_categories c ON pg.categoryID = c.categoryID
WHERE pg.processGroupCode = '8.3' AND c.categoryCode = '8.0'
ON DUPLICATE KEY UPDATE processName = VALUES(processName);

INSERT INTO tija_bau_processes (processGroupID, processCode, processID, processName, processDescription, functionalArea, isActive, isCustom)
SELECT pg.processGroupID, '8.3.2', '8.3.2', 'Process Accounts Receivable', 'Process accounts receivable transactions', 'Finance', 'Y', 'N'
FROM tija_bau_process_groups pg
JOIN tija_bau_categories c ON pg.categoryID = c.categoryID
WHERE pg.processGroupCode = '8.3' AND c.categoryCode = '8.0'
ON DUPLICATE KEY UPDATE processName = VALUES(processName);

INSERT INTO tija_bau_processes (processGroupID, processCode, processID, processName, processDescription, functionalArea, isActive, isCustom)
SELECT pg.processGroupID, '8.3.3', '8.3.3', 'Process General Ledger', 'Process general ledger transactions and maintain chart of accounts', 'Finance', 'Y', 'N'
FROM tija_bau_process_groups pg
JOIN tija_bau_categories c ON pg.categoryID = c.categoryID
WHERE pg.processGroupCode = '8.3' AND c.categoryCode = '8.0'
ON DUPLICATE KEY UPDATE processName = VALUES(processName);

-- Activities for 6.3.1 (Manage Payroll)
INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, functionalArea, isActive)
SELECT p.processID, '6.3.1.1', 'Review Time and Attendance', 'Review and validate employee time and attendance records', 'HR', 'Y'
FROM tija_bau_processes p WHERE p.processID = '6.3.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, functionalArea, isActive)
SELECT p.processID, '6.3.1.2', 'Calculate Gross Pay', 'Calculate gross pay based on hours worked and rates', 'HR', 'Y'
FROM tija_bau_processes p WHERE p.processID = '6.3.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, functionalArea, isActive)
SELECT p.processID, '6.3.1.3', 'Calculate Deductions', 'Calculate payroll deductions (taxes, benefits, etc.)', 'HR', 'Y'
FROM tija_bau_processes p WHERE p.processID = '6.3.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, functionalArea, isActive)
SELECT p.processID, '6.3.1.4', 'Calculate Net Pay', 'Calculate net pay after all deductions', 'HR', 'Y'
FROM tija_bau_processes p WHERE p.processID = '6.3.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, functionalArea, isActive)
SELECT p.processID, '6.3.1.5', 'Process Payroll Payments', 'Process and distribute payroll payments', 'HR', 'Y'
FROM tija_bau_processes p WHERE p.processID = '6.3.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

-- Activities for 8.6.1 (Manage Cash)
INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, functionalArea, isActive)
SELECT p.processID, '8.6.1.1', 'Reconcile Cash Accounts', 'Reconcile cash accounts and bank statements', 'Finance', 'Y'
FROM tija_bau_processes p WHERE p.processID = '8.6.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, functionalArea, isActive)
SELECT p.processID, '8.6.1.2', 'Monitor Cash Flow', 'Monitor cash flow and liquidity position', 'Finance', 'Y'
FROM tija_bau_processes p WHERE p.processID = '8.6.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, functionalArea, isActive)
SELECT p.processID, '8.6.1.3', 'Forecast Cash Requirements', 'Forecast future cash requirements', 'Finance', 'Y'
FROM tija_bau_processes p WHERE p.processID = '8.6.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

