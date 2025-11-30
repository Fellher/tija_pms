-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Operational Work Starter Pack
-- Purpose: Seed default categories, process groups, processes, activities,
--          workflows, SOPs, and task templates for organizations to start using
-- Date: 2025-11-29
-- ────────────────────────────────────────────────────────────────────────────

-- ============================================================================
-- PART 1: APQC TAXONOMY (Categories, Process Groups, Processes, Activities)
-- ============================================================================

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
ON DUPLICATE KEY UPDATE categoryName = VALUES(categoryName), categoryDescription = VALUES(categoryDescription);

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
SELECT categoryID, '6.3', 'Reward and Retain Employees', 'Reward and retain employees to maintain workforce capability', 3, 'Y'
FROM tija_bau_categories WHERE categoryCode = '6.0'
ON DUPLICATE KEY UPDATE processGroupName = VALUES(processGroupName);

INSERT INTO tija_bau_process_groups (categoryID, processGroupCode, processGroupName, processGroupDescription, displayOrder, isActive)
SELECT categoryID, '6.4', 'Develop and Deploy People', 'Develop and deploy people to build workforce capability', 4, 'Y'
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

-- Processes for 6.3 (Reward and Retain Employees)
INSERT INTO tija_bau_processes (processGroupID, processCode, processName, processDescription, functionalArea, isActive, isCustom)
SELECT pg.processGroupID, '6.3.1', 'Manage Payroll', 'Process payroll accurately and on time for all employees', 'HR', 'Y', 'N'
FROM tija_bau_process_groups pg
JOIN tija_bau_categories c ON pg.categoryID = c.categoryID
WHERE pg.processGroupCode = '6.3' AND c.categoryCode = '6.0'
ON DUPLICATE KEY UPDATE processName = VALUES(processName);

INSERT INTO tija_bau_processes (processGroupID, processCode, processName, processDescription, functionalArea, isActive, isCustom)
SELECT pg.processGroupID, '6.3.2', 'Manage Employee Benefits', 'Administer employee benefits programs including health, retirement, and other benefits', 'HR', 'Y', 'N'
FROM tija_bau_process_groups pg
JOIN tija_bau_categories c ON pg.categoryID = c.categoryID
WHERE pg.processGroupCode = '6.3' AND c.categoryCode = '6.0'
ON DUPLICATE KEY UPDATE processName = VALUES(processName);

INSERT INTO tija_bau_processes (processGroupID, processCode, processName, processDescription, functionalArea, isActive, isCustom)
SELECT pg.processGroupID, '6.3.3', 'Manage Employee Relations', 'Manage employee relations and workplace policies', 'HR', 'Y', 'N'
FROM tija_bau_process_groups pg
JOIN tija_bau_categories c ON pg.categoryID = c.categoryID
WHERE pg.processGroupCode = '6.3' AND c.categoryCode = '6.0'
ON DUPLICATE KEY UPDATE processName = VALUES(processName);

-- Processes for 6.4 (Develop and Deploy People)
INSERT INTO tija_bau_processes (processGroupID, processCode, processName, processDescription, functionalArea, isActive, isCustom)
SELECT pg.processGroupID, '6.4.1', 'Conduct Performance Reviews', 'Conduct regular performance reviews and evaluations', 'HR', 'Y', 'N'
FROM tija_bau_process_groups pg
JOIN tija_bau_categories c ON pg.categoryID = c.categoryID
WHERE pg.processGroupCode = '6.4' AND c.categoryCode = '6.0'
ON DUPLICATE KEY UPDATE processName = VALUES(processName);

INSERT INTO tija_bau_processes (processGroupID, processCode, processName, processDescription, functionalArea, isActive, isCustom)
SELECT pg.processGroupID, '6.4.2', 'Manage Training and Development', 'Manage employee training and development programs', 'HR', 'Y', 'N'
FROM tija_bau_process_groups pg
JOIN tija_bau_categories c ON pg.categoryID = c.categoryID
WHERE pg.processGroupCode = '6.4' AND c.categoryCode = '6.0'
ON DUPLICATE KEY UPDATE processName = VALUES(processName);

-- Processes for 8.3 (Process Financial Transactions)
INSERT INTO tija_bau_processes (processGroupID, processCode, processName, processDescription, functionalArea, isActive, isCustom)
SELECT pg.processGroupID, '8.3.1', 'Process Accounts Payable', 'Process accounts payable transactions including vendor invoices and payments', 'Finance', 'Y', 'N'
FROM tija_bau_process_groups pg
JOIN tija_bau_categories c ON pg.categoryID = c.categoryID
WHERE pg.processGroupCode = '8.3' AND c.categoryCode = '8.0'
ON DUPLICATE KEY UPDATE processName = VALUES(processName);

INSERT INTO tija_bau_processes (processGroupID, processCode, processName, processDescription, functionalArea, isActive, isCustom)
SELECT pg.processGroupID, '8.3.2', 'Process Accounts Receivable', 'Process accounts receivable transactions including customer invoices and collections', 'Finance', 'Y', 'N'
FROM tija_bau_process_groups pg
JOIN tija_bau_categories c ON pg.categoryID = c.categoryID
WHERE pg.processGroupCode = '8.3' AND c.categoryCode = '8.0'
ON DUPLICATE KEY UPDATE processName = VALUES(processName);

INSERT INTO tija_bau_processes (processGroupID, processCode, processName, processDescription, functionalArea, isActive, isCustom)
SELECT pg.processGroupID, '8.3.3', 'Process General Ledger', 'Process general ledger transactions and maintain chart of accounts', 'Finance', 'Y', 'N'
FROM tija_bau_process_groups pg
JOIN tija_bau_categories c ON pg.categoryID = c.categoryID
WHERE pg.processGroupCode = '8.3' AND c.categoryCode = '8.0'
ON DUPLICATE KEY UPDATE processName = VALUES(processName);

-- Processes for 8.6 (Manage Treasury Operations)
INSERT INTO tija_bau_processes (processGroupID, processCode, processName, processDescription, functionalArea, isActive, isCustom)
SELECT pg.processGroupID, '8.6.1', 'Manage Cash', 'Manage cash to ensure adequate liquidity and optimize cash position', 'Finance', 'Y', 'N'
FROM tija_bau_process_groups pg
JOIN tija_bau_categories c ON pg.categoryID = c.categoryID
WHERE pg.processGroupCode = '8.6' AND c.categoryCode = '8.0'
ON DUPLICATE KEY UPDATE processName = VALUES(processName);

INSERT INTO tija_bau_processes (processGroupID, processCode, processName, processDescription, functionalArea, isActive, isCustom)
SELECT pg.processGroupID, '8.6.2', 'Reconcile Bank Accounts', 'Reconcile bank accounts to ensure accuracy and identify discrepancies', 'Finance', 'Y', 'N'
FROM tija_bau_process_groups pg
JOIN tija_bau_categories c ON pg.categoryID = c.categoryID
WHERE pg.processGroupCode = '8.6' AND c.categoryCode = '8.0'
ON DUPLICATE KEY UPDATE processName = VALUES(processName);

-- Activities for 6.3.1 (Manage Payroll)
INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '6.3.1.1', 'Collect Time and Attendance', 'Collect and validate employee time and attendance records', 2.0, 1, 'Y'
FROM tija_bau_processes p
JOIN tija_bau_process_groups pg ON p.processGroupID = pg.processGroupID
WHERE p.processCode = '6.3.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '6.3.1.2', 'Calculate Gross Pay', 'Calculate gross pay based on hours worked, rates, and overtime', 1.5, 2, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '6.3.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '6.3.1.3', 'Calculate Deductions', 'Calculate payroll deductions including taxes, benefits, and other deductions', 2.0, 3, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '6.3.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '6.3.1.4', 'Calculate Net Pay', 'Calculate net pay after all deductions', 0.5, 4, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '6.3.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '6.3.1.5', 'Process Payroll Payments', 'Process and distribute payroll payments via direct deposit or checks', 1.0, 5, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '6.3.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '6.3.1.6', 'Generate Payroll Reports', 'Generate payroll reports and remit taxes and deductions', 1.0, 6, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '6.3.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

-- Activities for 8.3.1 (Process Accounts Payable)
INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '8.3.1.1', 'Receive and Verify Invoices', 'Receive vendor invoices and verify accuracy and authorization', 1.0, 1, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '8.3.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '8.3.1.2', 'Match Invoices to Purchase Orders', 'Match invoices to purchase orders and receiving documents', 1.5, 2, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '8.3.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '8.3.1.3', 'Obtain Approval', 'Obtain required approvals for invoice payment', 0.5, 3, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '8.3.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '8.3.1.4', 'Process Payment', 'Process payment to vendor via check, ACH, or wire transfer', 1.0, 4, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '8.3.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '8.3.1.5', 'Record in General Ledger', 'Record accounts payable transactions in general ledger', 0.5, 5, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '8.3.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

-- Activities for 8.6.2 (Reconcile Bank Accounts)
INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '8.6.2.1', 'Retrieve Bank Statements', 'Retrieve bank statements and transaction records', 0.5, 1, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '8.6.2'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '8.6.2.2', 'Compare Bank Records to General Ledger', 'Compare bank records to general ledger cash account', 2.0, 2, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '8.6.2'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '8.6.2.3', 'Identify and Resolve Discrepancies', 'Identify discrepancies and resolve outstanding items', 1.5, 3, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '8.6.2'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '8.6.2.4', 'Document Reconciliation', 'Document reconciliation results and file supporting documents', 0.5, 4, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '8.6.2'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

-- Activities for 6.3.2 (Manage Employee Benefits)
INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '6.3.2.1', 'Enroll Employees in Benefits', 'Process new employee benefit enrollments', 1.0, 1, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '6.3.2'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '6.3.2.2', 'Process Benefit Changes', 'Process employee benefit changes and updates', 0.5, 2, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '6.3.2'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '6.3.2.3', 'Reconcile Benefit Deductions', 'Reconcile benefit deductions with provider invoices', 1.0, 3, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '6.3.2'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '6.3.2.4', 'Process Benefit Claims', 'Process and coordinate employee benefit claims', 1.5, 4, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '6.3.2'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

-- Activities for 6.4.1 (Conduct Performance Reviews)
INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '6.4.1.1', 'Schedule Performance Reviews', 'Schedule performance review meetings with employees and managers', 0.5, 1, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '6.4.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '6.4.1.2', 'Collect Performance Data', 'Collect performance data, goals, and feedback', 1.0, 2, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '6.4.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '6.4.1.3', 'Conduct Review Meeting', 'Conduct performance review meeting with employee', 1.0, 3, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '6.4.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '6.4.1.4', 'Document Review Results', 'Document performance review results and action items', 0.5, 4, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '6.4.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '6.4.1.5', 'Set Development Goals', 'Set development goals and create improvement plans', 0.5, 5, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '6.4.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

-- Activities for 6.4.2 (Manage Training and Development)
INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '6.4.2.1', 'Assess Training Needs', 'Assess organizational and individual training needs', 2.0, 1, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '6.4.2'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '6.4.2.2', 'Develop Training Programs', 'Develop or select training programs to meet identified needs', 3.0, 2, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '6.4.2'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '6.4.2.3', 'Schedule Training Sessions', 'Schedule training sessions and coordinate logistics', 1.0, 3, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '6.4.2'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '6.4.2.4', 'Deliver Training', 'Deliver training sessions or coordinate external training', 4.0, 4, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '6.4.2'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '6.4.2.5', 'Evaluate Training Effectiveness', 'Evaluate training effectiveness and gather feedback', 1.0, 5, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '6.4.2'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

-- Activities for 8.3.2 (Process Accounts Receivable)
INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '8.3.2.1', 'Generate Customer Invoices', 'Generate invoices for products or services delivered', 1.0, 1, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '8.3.2'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '8.3.2.2', 'Send Invoices to Customers', 'Send invoices to customers via email or mail', 0.5, 2, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '8.3.2'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '8.3.2.3', 'Record Receivables', 'Record accounts receivable in general ledger', 0.5, 3, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '8.3.2'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '8.3.2.4', 'Monitor Collections', 'Monitor accounts receivable aging and follow up on overdue accounts', 2.0, 4, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '8.3.2'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '8.3.2.5', 'Process Customer Payments', 'Process customer payments and apply to invoices', 1.0, 5, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '8.3.2'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '8.3.2.6', 'Reconcile Receivables', 'Reconcile accounts receivable and resolve discrepancies', 1.0, 6, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '8.3.2'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

-- Activities for 8.3.3 (Process General Ledger)
INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '8.3.3.1', 'Post Journal Entries', 'Post journal entries to general ledger', 1.0, 1, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '8.3.3'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '8.3.3.2', 'Reconcile General Ledger Accounts', 'Reconcile general ledger accounts monthly', 2.0, 2, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '8.3.3'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '8.3.3.3', 'Maintain Chart of Accounts', 'Maintain and update chart of accounts structure', 1.0, 3, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '8.3.3'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '8.3.3.4', 'Close Accounting Periods', 'Close accounting periods and prepare for next period', 2.0, 4, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '8.3.3'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

-- Activities for 8.6.1 (Manage Cash)
INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '8.6.1.1', 'Monitor Daily Cash Position', 'Monitor daily cash balances across all accounts', 0.5, 1, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '8.6.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '8.6.1.2', 'Forecast Cash Flow', 'Forecast short-term and long-term cash flow requirements', 2.0, 2, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '8.6.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '8.6.1.3', 'Optimize Cash Position', 'Optimize cash position through investments or borrowing', 1.0, 3, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '8.6.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

INSERT INTO tija_bau_activities (processID, activityCode, activityName, activityDescription, estimatedDuration, displayOrder, isActive)
SELECT p.processID, '8.6.1.4', 'Manage Bank Relationships', 'Manage relationships with banks and financial institutions', 0.5, 4, 'Y'
FROM tija_bau_processes p
WHERE p.processCode = '8.6.1'
ON DUPLICATE KEY UPDATE activityName = VALUES(activityName);

-- ============================================================================
-- PART 2: WORKFLOWS
-- ============================================================================

-- Workflow: Monthly Payroll Processing
INSERT INTO tija_workflows (workflowCode, workflowName, workflowDescription, processID, functionalArea, workflowType, version, isActive, workflowDefinition)
SELECT 'WF-PAYROLL-001', 'Monthly Payroll Processing', 'Sequential workflow for processing monthly payroll', p.processID, 'HR', 'sequential', 1, 'Y',
JSON_OBJECT('type', 'sequential', 'steps', JSON_ARRAY(
    JSON_OBJECT('step', 1, 'name', 'Collect Time Records', 'type', 'task'),
    JSON_OBJECT('step', 2, 'name', 'Calculate Payroll', 'type', 'task'),
    JSON_OBJECT('step', 3, 'name', 'Review and Approve', 'type', 'approval'),
    JSON_OBJECT('step', 4, 'name', 'Process Payments', 'type', 'task'),
    JSON_OBJECT('step', 5, 'name', 'Generate Reports', 'type', 'task')
))
FROM tija_bau_processes p WHERE p.processCode = '6.3.1'
ON DUPLICATE KEY UPDATE workflowName = VALUES(workflowName);

-- Workflow Steps for Payroll
INSERT INTO tija_workflow_steps (workflowID, stepOrder, stepName, stepDescription, stepType, assigneeType, estimatedDuration, isMandatory)
SELECT w.workflowID, 1, 'Collect Time Records', 'Collect and validate employee time and attendance records', 'task', 'role', 2.0, 'Y'
FROM tija_workflows w WHERE w.workflowCode = 'WF-PAYROLL-001'
ON DUPLICATE KEY UPDATE stepName = VALUES(stepName);

INSERT INTO tija_workflow_steps (workflowID, stepOrder, stepName, stepDescription, stepType, assigneeType, estimatedDuration, isMandatory)
SELECT w.workflowID, 2, 'Calculate Payroll', 'Calculate gross pay, deductions, and net pay', 'task', 'role', 3.0, 'Y'
FROM tija_workflows w WHERE w.workflowCode = 'WF-PAYROLL-001'
ON DUPLICATE KEY UPDATE stepName = VALUES(stepName);

INSERT INTO tija_workflow_steps (workflowID, stepOrder, stepName, stepDescription, stepType, assigneeType, estimatedDuration, isMandatory)
SELECT w.workflowID, 3, 'Review and Approve', 'Review payroll calculations and obtain approval', 'approval', 'function_head', 1.0, 'Y'
FROM tija_workflows w WHERE w.workflowCode = 'WF-PAYROLL-001'
ON DUPLICATE KEY UPDATE stepName = VALUES(stepName);

INSERT INTO tija_workflow_steps (workflowID, stepOrder, stepName, stepDescription, stepType, assigneeType, estimatedDuration, isMandatory)
SELECT w.workflowID, 4, 'Process Payments', 'Process and distribute payroll payments', 'task', 'role', 1.0, 'Y'
FROM tija_workflows w WHERE w.workflowCode = 'WF-PAYROLL-001'
ON DUPLICATE KEY UPDATE stepName = VALUES(stepName);

INSERT INTO tija_workflow_steps (workflowID, stepOrder, stepName, stepDescription, stepType, assigneeType, estimatedDuration, isMandatory)
SELECT w.workflowID, 5, 'Generate Reports', 'Generate payroll reports and remit taxes', 'task', 'role', 1.0, 'Y'
FROM tija_workflows w WHERE w.workflowCode = 'WF-PAYROLL-001'
ON DUPLICATE KEY UPDATE stepName = VALUES(stepName);

-- Workflow Transitions for Payroll
INSERT INTO tija_workflow_transitions (workflowID, fromStepID, toStepID, conditionType, transitionLabel)
SELECT w.workflowID, s1.workflowStepID, s2.workflowStepID, 'always', 'Next'
FROM tija_workflows w
JOIN tija_workflow_steps s1 ON w.workflowID = s1.workflowID AND s1.stepOrder = 1
JOIN tija_workflow_steps s2 ON w.workflowID = s2.workflowID AND s2.stepOrder = 2
WHERE w.workflowCode = 'WF-PAYROLL-001'
ON DUPLICATE KEY UPDATE transitionLabel = VALUES(transitionLabel);

INSERT INTO tija_workflow_transitions (workflowID, fromStepID, toStepID, conditionType, transitionLabel)
SELECT w.workflowID, s1.workflowStepID, s2.workflowStepID, 'always', 'Next'
FROM tija_workflows w
JOIN tija_workflow_steps s1 ON w.workflowID = s1.workflowID AND s1.stepOrder = 2
JOIN tija_workflow_steps s2 ON w.workflowID = s2.workflowID AND s2.stepOrder = 3
WHERE w.workflowCode = 'WF-PAYROLL-001'
ON DUPLICATE KEY UPDATE transitionLabel = VALUES(transitionLabel);

INSERT INTO tija_workflow_transitions (workflowID, fromStepID, toStepID, conditionType, transitionLabel)
SELECT w.workflowID, s1.workflowStepID, s2.workflowStepID, 'always', 'Approved'
FROM tija_workflows w
JOIN tija_workflow_steps s1 ON w.workflowID = s1.workflowID AND s1.stepOrder = 3
JOIN tija_workflow_steps s2 ON w.workflowID = s2.workflowID AND s2.stepOrder = 4
WHERE w.workflowCode = 'WF-PAYROLL-001'
ON DUPLICATE KEY UPDATE transitionLabel = VALUES(transitionLabel);

INSERT INTO tija_workflow_transitions (workflowID, fromStepID, toStepID, conditionType, transitionLabel)
SELECT w.workflowID, s1.workflowStepID, s2.workflowStepID, 'always', 'Next'
FROM tija_workflows w
JOIN tija_workflow_steps s1 ON w.workflowID = s1.workflowID AND s1.stepOrder = 4
JOIN tija_workflow_steps s2 ON w.workflowID = s2.workflowID AND s2.stepOrder = 5
WHERE w.workflowCode = 'WF-PAYROLL-001'
ON DUPLICATE KEY UPDATE transitionLabel = VALUES(transitionLabel);

-- Workflow: Accounts Payable Processing
INSERT INTO tija_workflows (workflowCode, workflowName, workflowDescription, processID, functionalArea, workflowType, version, isActive, workflowDefinition)
SELECT 'WF-AP-001', 'Accounts Payable Processing', 'Workflow for processing vendor invoices and payments', p.processID, 'Finance', 'sequential', 1, 'Y',
JSON_OBJECT('type', 'sequential', 'steps', JSON_ARRAY(
    JSON_OBJECT('step', 1, 'name', 'Receive Invoice', 'type', 'task'),
    JSON_OBJECT('step', 2, 'name', 'Match to PO', 'type', 'task'),
    JSON_OBJECT('step', 3, 'name', 'Obtain Approval', 'type', 'approval'),
    JSON_OBJECT('step', 4, 'name', 'Process Payment', 'type', 'task'),
    JSON_OBJECT('step', 5, 'name', 'Record in GL', 'type', 'task')
))
FROM tija_bau_processes p WHERE p.processCode = '8.3.1'
ON DUPLICATE KEY UPDATE workflowName = VALUES(workflowName);

-- Workflow Steps for AP
INSERT INTO tija_workflow_steps (workflowID, stepOrder, stepName, stepDescription, stepType, assigneeType, estimatedDuration, isMandatory)
SELECT w.workflowID, 1, 'Receive Invoice', 'Receive and verify vendor invoice', 'task', 'role', 1.0, 'Y'
FROM tija_workflows w WHERE w.workflowCode = 'WF-AP-001'
ON DUPLICATE KEY UPDATE stepName = VALUES(stepName);

INSERT INTO tija_workflow_steps (workflowID, stepOrder, stepName, stepDescription, stepType, assigneeType, estimatedDuration, isMandatory)
SELECT w.workflowID, 2, 'Match to PO', 'Match invoice to purchase order and receiving documents', 'task', 'role', 1.5, 'Y'
FROM tija_workflows w WHERE w.workflowCode = 'WF-AP-001'
ON DUPLICATE KEY UPDATE stepName = VALUES(stepName);

INSERT INTO tija_workflow_steps (workflowID, stepOrder, stepName, stepDescription, stepType, assigneeType, estimatedDuration, isMandatory)
SELECT w.workflowID, 3, 'Obtain Approval', 'Obtain required approval for payment', 'approval', 'function_head', 0.5, 'Y'
FROM tija_workflows w WHERE w.workflowCode = 'WF-AP-001'
ON DUPLICATE KEY UPDATE stepName = VALUES(stepName);

INSERT INTO tija_workflow_steps (workflowID, stepOrder, stepName, stepDescription, stepType, assigneeType, estimatedDuration, isMandatory)
SELECT w.workflowID, 4, 'Process Payment', 'Process payment to vendor', 'task', 'role', 1.0, 'Y'
FROM tija_workflows w WHERE w.workflowCode = 'WF-AP-001'
ON DUPLICATE KEY UPDATE stepName = VALUES(stepName);

INSERT INTO tija_workflow_steps (workflowID, stepOrder, stepName, stepDescription, stepType, assigneeType, estimatedDuration, isMandatory)
SELECT w.workflowID, 5, 'Record in GL', 'Record transaction in general ledger', 'task', 'role', 0.5, 'Y'
FROM tija_workflows w WHERE w.workflowCode = 'WF-AP-001'
ON DUPLICATE KEY UPDATE stepName = VALUES(stepName);

-- Workflow Transitions for AP
INSERT INTO tija_workflow_transitions (workflowID, fromStepID, toStepID, conditionType, transitionLabel)
SELECT w.workflowID, s1.workflowStepID, s2.workflowStepID, 'always', 'Next'
FROM tija_workflows w
JOIN tija_workflow_steps s1 ON w.workflowID = s1.workflowID AND s1.stepOrder = 1
JOIN tija_workflow_steps s2 ON w.workflowID = s2.workflowID AND s2.stepOrder = 2
WHERE w.workflowCode = 'WF-AP-001'
ON DUPLICATE KEY UPDATE transitionLabel = VALUES(transitionLabel);

INSERT INTO tija_workflow_transitions (workflowID, fromStepID, toStepID, conditionType, transitionLabel)
SELECT w.workflowID, s1.workflowStepID, s2.workflowStepID, 'always', 'Next'
FROM tija_workflows w
JOIN tija_workflow_steps s1 ON w.workflowID = s1.workflowID AND s1.stepOrder = 2
JOIN tija_workflow_steps s2 ON w.workflowID = s2.workflowID AND s2.stepOrder = 3
WHERE w.workflowCode = 'WF-AP-001'
ON DUPLICATE KEY UPDATE transitionLabel = VALUES(transitionLabel);

INSERT INTO tija_workflow_transitions (workflowID, fromStepID, toStepID, conditionType, transitionLabel)
SELECT w.workflowID, s1.workflowStepID, s2.workflowStepID, 'always', 'Approved'
FROM tija_workflows w
JOIN tija_workflow_steps s1 ON w.workflowID = s1.workflowID AND s1.stepOrder = 3
JOIN tija_workflow_steps s2 ON w.workflowID = s2.workflowID AND s2.stepOrder = 4
WHERE w.workflowCode = 'WF-AP-001'
ON DUPLICATE KEY UPDATE transitionLabel = VALUES(transitionLabel);

INSERT INTO tija_workflow_transitions (workflowID, fromStepID, toStepID, conditionType, transitionLabel)
SELECT w.workflowID, s1.workflowStepID, s2.workflowStepID, 'always', 'Next'
FROM tija_workflows w
JOIN tija_workflow_steps s1 ON w.workflowID = s1.workflowID AND s1.stepOrder = 4
JOIN tija_workflow_steps s2 ON w.workflowID = s2.workflowID AND s2.stepOrder = 5
WHERE w.workflowCode = 'WF-AP-001'
ON DUPLICATE KEY UPDATE transitionLabel = VALUES(transitionLabel);

-- ============================================================================
-- PART 3: STANDARD OPERATING PROCEDURES (SOPs)
-- ============================================================================

-- SOP: Monthly Payroll Processing
INSERT INTO tija_sops (sopCode, sopTitle, sopDescription, processID, functionalArea, sopVersion, approvalStatus, isActive, sopContent)
SELECT 'SOP-PAYROLL-001', 'Monthly Payroll Processing Procedure',
'Standard operating procedure for processing monthly payroll including time collection, calculation, approval, and payment distribution.',
p.processID, 'HR', '1.0', 'approved', 'Y',
'<h2>Monthly Payroll Processing Procedure</h2>
<h3>1. Overview</h3>
<p>This procedure outlines the steps for processing monthly payroll accurately and on time.</p>
<h3>2. Procedure</h3>
<ol>
<li>Collect time and attendance records from all employees</li>
<li>Verify and validate time records for accuracy</li>
<li>Calculate gross pay based on hours worked and rates</li>
<li>Calculate all deductions (taxes, benefits, etc.)</li>
<li>Calculate net pay</li>
<li>Obtain approval from HR Manager</li>
<li>Process payments via direct deposit or checks</li>
<li>Generate payroll reports</li>
<li>Remit taxes and deductions to appropriate agencies</li>
</ol>
<h3>3. Responsibilities</h3>
<ul>
<li>Payroll Administrator: Collect time, calculate payroll</li>
<li>HR Manager: Review and approve payroll</li>
<li>Finance: Process payments and remit taxes</li>
</ul>'
FROM tija_bau_processes p WHERE p.processCode = '6.3.1'
ON DUPLICATE KEY UPDATE sopTitle = VALUES(sopTitle);

-- SOP Sections for Payroll
INSERT INTO tija_sop_sections (sopID, sectionOrder, sectionTitle, sectionContent, sectionType)
SELECT s.sopID, 1, 'Overview', 'This procedure outlines the steps for processing monthly payroll accurately and on time.', 'overview'
FROM tija_sops s WHERE s.sopCode = 'SOP-PAYROLL-001'
ON DUPLICATE KEY UPDATE sectionContent = VALUES(sectionContent);

INSERT INTO tija_sop_sections (sopID, sectionOrder, sectionTitle, sectionContent, sectionType)
SELECT s.sopID, 2, 'Procedure Steps',
'1. Collect time and attendance records\n2. Verify and validate time records\n3. Calculate gross pay\n4. Calculate deductions\n5. Calculate net pay\n6. Obtain approval\n7. Process payments\n8. Generate reports\n9. Remit taxes',
'procedure'
FROM tija_sops s WHERE s.sopCode = 'SOP-PAYROLL-001'
ON DUPLICATE KEY UPDATE sectionContent = VALUES(sectionContent);

INSERT INTO tija_sop_sections (sopID, sectionOrder, sectionTitle, sectionContent, sectionType)
SELECT s.sopID, 3, 'Checklist',
'□ Time records collected\n□ Time records verified\n□ Payroll calculated\n□ Approval obtained\n□ Payments processed\n□ Reports generated\n□ Taxes remitted',
'checklist'
FROM tija_sops s WHERE s.sopCode = 'SOP-PAYROLL-001'
ON DUPLICATE KEY UPDATE sectionContent = VALUES(sectionContent);

-- SOP: Accounts Payable Processing
INSERT INTO tija_sops (sopCode, sopTitle, sopDescription, processID, functionalArea, sopVersion, approvalStatus, isActive, sopContent)
SELECT 'SOP-AP-001', 'Accounts Payable Processing Procedure',
'Standard operating procedure for processing vendor invoices and payments including invoice verification, matching, approval, and payment.',
p.processID, 'Finance', '1.0', 'approved', 'Y',
'<h2>Accounts Payable Processing Procedure</h2>
<h3>1. Overview</h3>
<p>This procedure outlines the steps for processing vendor invoices and payments accurately and efficiently.</p>
<h3>2. Procedure</h3>
<ol>
<li>Receive vendor invoice</li>
<li>Verify invoice details (amount, date, vendor)</li>
<li>Match invoice to purchase order and receiving documents</li>
<li>Obtain required approval based on amount</li>
<li>Process payment via check, ACH, or wire transfer</li>
<li>Record transaction in general ledger</li>
<li>File supporting documents</li>
</ol>
<h3>3. Approval Limits</h3>
<ul>
<li>Under $1,000: Department Manager</li>
<li>$1,000 - $10,000: Finance Manager</li>
<li>Over $10,000: CFO</li>
</ul>'
FROM tija_bau_processes p WHERE p.processCode = '8.3.1'
ON DUPLICATE KEY UPDATE sopTitle = VALUES(sopTitle);

-- SOP Sections for AP
INSERT INTO tija_sop_sections (sopID, sectionOrder, sectionTitle, sectionContent, sectionType)
SELECT s.sopID, 1, 'Overview', 'This procedure outlines the steps for processing vendor invoices and payments.', 'overview'
FROM tija_sops s WHERE s.sopCode = 'SOP-AP-001'
ON DUPLICATE KEY UPDATE sectionContent = VALUES(sectionContent);

INSERT INTO tija_sop_sections (sopID, sectionOrder, sectionTitle, sectionContent, sectionType)
SELECT s.sopID, 2, 'Procedure Steps',
'1. Receive and verify invoice\n2. Match to purchase order\n3. Obtain approval\n4. Process payment\n5. Record in general ledger',
'procedure'
FROM tija_sops s WHERE s.sopCode = 'SOP-AP-001'
ON DUPLICATE KEY UPDATE sectionContent = VALUES(sectionContent);

-- SOP: Bank Reconciliation
INSERT INTO tija_sops (sopCode, sopTitle, sopDescription, processID, functionalArea, sopVersion, approvalStatus, isActive, sopContent)
SELECT 'SOP-BANK-RECON-001', 'Monthly Bank Reconciliation Procedure',
'Standard operating procedure for reconciling bank accounts monthly to ensure accuracy and identify discrepancies.',
p.processID, 'Finance', '1.0', 'approved', 'Y',
'<h2>Monthly Bank Reconciliation Procedure</h2>
<h3>1. Overview</h3>
<p>This procedure outlines the steps for reconciling bank accounts monthly.</p>
<h3>2. Procedure</h3>
<ol>
<li>Retrieve bank statements</li>
<li>Compare bank records to general ledger cash account</li>
<li>Identify outstanding checks and deposits</li>
<li>Identify and resolve discrepancies</li>
<li>Document reconciliation results</li>
<li>Obtain approval from Finance Manager</li>
<li>File reconciliation documents</li>
</ol>'
FROM tija_bau_processes p WHERE p.processCode = '8.6.2'
ON DUPLICATE KEY UPDATE sopTitle = VALUES(sopTitle);

-- ============================================================================
-- PART 4: TASK TEMPLATES
-- ============================================================================

-- Template: Monthly Payroll Processing
INSERT INTO tija_operational_task_templates (templateCode, templateName, templateDescription, processID, workflowID, sopID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfMonth, estimatedDuration, requiresApproval, requiresSOPReview, isActive, processingMode)
SELECT 'TMPL-PAYROLL-MONTHLY', 'Monthly Payroll Processing',
'Recurring monthly task to process payroll for all employees',
p.processID, w.workflowID, s.sopID, 'HR',
'monthly', 1, 25, 8.0, 'Y', 'Y', 'Y', 'cron'
FROM tija_bau_processes p
LEFT JOIN tija_workflows w ON w.workflowCode = 'WF-PAYROLL-001'
LEFT JOIN tija_sops s ON s.sopCode = 'SOP-PAYROLL-001'
WHERE p.processCode = '6.3.1'
ON DUPLICATE KEY UPDATE templateName = VALUES(templateName);

-- Template: Weekly Accounts Payable Processing
INSERT INTO tija_operational_task_templates (templateCode, templateName, templateDescription, processID, workflowID, sopID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfWeek, estimatedDuration, requiresApproval, requiresSOPReview, isActive, processingMode)
SELECT 'TMPL-AP-WEEKLY', 'Weekly Accounts Payable Processing',
'Recurring weekly task to process vendor invoices and payments',
p.processID, w.workflowID, s.sopID, 'Finance',
'weekly', 1, 5, 4.0, 'Y', 'N', 'Y', 'cron'
FROM tija_bau_processes p
LEFT JOIN tija_workflows w ON w.workflowCode = 'WF-AP-001'
LEFT JOIN tija_sops s ON s.sopCode = 'SOP-AP-001'
WHERE p.processCode = '8.3.1'
ON DUPLICATE KEY UPDATE templateName = VALUES(templateName);

-- Template: Monthly Bank Reconciliation
INSERT INTO tija_operational_task_templates (templateCode, templateName, templateDescription, processID, workflowID, sopID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfMonth, estimatedDuration, requiresApproval, requiresSOPReview, isActive, processingMode)
SELECT 'TMPL-BANK-RECON-MONTHLY', 'Monthly Bank Reconciliation',
'Recurring monthly task to reconcile all bank accounts',
p.processID, NULL, s.sopID, 'Finance',
'monthly', 1, 5, 3.0, 'Y', 'Y', 'Y', 'cron'
FROM tija_bau_processes p
LEFT JOIN tija_sops s ON s.sopCode = 'SOP-BANK-RECON-001'
WHERE p.processCode = '8.6.2'
ON DUPLICATE KEY UPDATE templateName = VALUES(templateName);

-- Template: Daily Cash Management
INSERT INTO tija_operational_task_templates (templateCode, templateName, templateDescription, processID, workflowID, sopID, functionalArea,
    frequencyType, frequencyInterval, estimatedDuration, requiresApproval, requiresSOPReview, isActive, processingMode)
SELECT 'TMPL-CASH-DAILY', 'Daily Cash Management',
'Recurring daily task to monitor cash position and liquidity',
p.processID, NULL, NULL, 'Finance',
'daily', 1, 1.0, 'N', 'N', 'Y', 'cron'
FROM tija_bau_processes p
WHERE p.processCode = '8.6.1'
ON DUPLICATE KEY UPDATE templateName = VALUES(templateName);

-- Template: Quarterly Performance Reviews
INSERT INTO tija_operational_task_templates (templateCode, templateName, templateDescription, processID, workflowID, sopID, functionalArea,
    frequencyType, frequencyInterval, frequencyMonthOfYear, estimatedDuration, requiresApproval, requiresSOPReview, isActive, processingMode)
SELECT 'TMPL-PERF-REVIEW-QTR', 'Quarterly Performance Reviews',
'Recurring quarterly task to conduct employee performance reviews',
p.processID, NULL, NULL, 'HR',
'quarterly', 1, 3, 2.0, 'Y', 'N', 'Y', 'manual'
FROM tija_bau_processes p
WHERE p.processCode = '6.4.1'
ON DUPLICATE KEY UPDATE templateName = VALUES(templateName);

-- ============================================================================
-- PART 5: SAMPLE OPERATIONAL TASKS (Optional - for demonstration)
-- ============================================================================
-- Note: These are sample task instances created from templates.
-- In production, tasks are typically created automatically by the scheduler
-- or manually by users. These samples demonstrate the system structure.

-- Sample Task: Monthly Payroll (Next Month)
-- This would normally be created automatically by the scheduler
-- INSERT INTO tija_operational_tasks (templateID, instanceNumber, dueDate, status, assigneeID, processID, sopReviewed)
-- SELECT t.templateID, 1, DATE_ADD(CURDATE(), INTERVAL 1 MONTH), 'pending', NULL, t.processID, 'N'
-- FROM tija_operational_task_templates t
-- WHERE t.templateCode = 'TMPL-PAYROLL-MONTHLY'
-- LIMIT 1;

-- ============================================================================
-- SUMMARY
-- ============================================================================
-- This comprehensive starter pack includes:
--
-- TAXONOMY:
-- - 12 APQC Categories (1.0-12.0)
-- - 10 Process Groups (6.1-6.4, 8.1-8.6)
-- - 10 Processes (6.3.1-6.3.3, 6.4.1-6.4.2, 8.3.1-8.3.3, 8.6.1-8.6.2)
-- - 40+ Activities across all key processes
--
-- WORKFLOWS:
-- - 2 Complete Workflows with Steps and Transitions:
--   * Monthly Payroll Processing (5 steps)
--   * Accounts Payable Processing (5 steps)
--
-- STANDARD OPERATING PROCEDURES:
-- - 3 Approved SOPs with Sections:
--   * Monthly Payroll Processing Procedure
--   * Accounts Payable Processing Procedure
--   * Monthly Bank Reconciliation Procedure
--
-- TASK TEMPLATES:
-- - 5 Recurring Task Templates:
--   * Monthly Payroll Processing (monthly, day 25)
--   * Weekly Accounts Payable Processing (weekly, Friday)
--   * Monthly Bank Reconciliation (monthly, day 5)
--   * Daily Cash Management (daily)
--   * Quarterly Performance Reviews (quarterly)
--
-- ACTIVITIES BREAKDOWN:
-- - 6.3.1 Manage Payroll: 6 activities
-- - 6.3.2 Manage Employee Benefits: 4 activities
-- - 6.4.1 Conduct Performance Reviews: 5 activities
-- - 6.4.2 Manage Training and Development: 5 activities
-- - 8.3.1 Process Accounts Payable: 5 activities
-- - 8.3.2 Process Accounts Receivable: 6 activities
-- - 8.3.3 Process General Ledger: 4 activities
-- - 8.6.1 Manage Cash: 4 activities
-- - 8.6.2 Reconcile Bank Accounts: 4 activities
--
-- USAGE:
-- 1. Run this script to populate your database with starter data
-- 2. Customize categories, processes, and activities for your organization
-- 3. Assign function heads to processes
-- 4. Configure task template assignment rules
-- 5. The scheduler will automatically create task instances from templates
-- 6. Users can start using workflows and following SOPs immediately
--
-- Organizations can customize these defaults and add more as needed.

