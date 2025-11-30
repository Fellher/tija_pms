-- HR Operational Task Templates

-- Payroll Administration: Time & Attendance Review (6.3.1)
INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfWeek, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'HR-PAY-001', 'Review Time and Attendance',
    'Review employee time and attendance records for accuracy. Verify hours worked, overtime, and leave balances.',
    '6.3.1', 'HR', 'weekly', 1, 1, 4.0, 'both', 'Y',
    '{"type": "role", "roleID": "PAYROLL_ADMIN"}'
);

-- Payroll Administration: Gross-to-Net Calculation (6.3.1)
INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfWeek, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'HR-PAY-002', 'Calculate Gross-to-Net Payroll',
    'Calculate gross pay, apply deductions (taxes, benefits, etc.), and compute net pay for all employees.',
    '6.3.1', 'HR', 'weekly', 1, 2, 6.0, 'cron', 'Y',
    '{"type": "role", "roleID": "PAYROLL_ADMIN"}'
);

-- Employee Onboarding (6.2) - Event-driven
INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, estimatedDuration, processingMode, isActive, assignmentRule
) VALUES (
    'HR-ONB-001', 'New Employee Onboarding',
    'Complete 90-day onboarding process for new employees including paperwork, system access, and orientation.',
    '6.2', 'HR', 'custom', 0.0, 'manual', 'Y',
    '{"type": "role", "roleID": "HR_ADMIN"}'
);

-- Benefits Administration (6.3.2)
INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfMonth, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'HR-BEN-001', 'Process Benefits Enrollment',
    'Process employee benefits enrollment and changes. Update benefits records and coordinate with providers.',
    '6.3.2', 'HR', 'monthly', 1, 1, 3.0, 'both', 'Y',
    '{"type": "role", "roleID": "BENEFITS_ADMIN"}'
);

-- Compliance Reporting (6.0)
INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyMonthOfYear, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'HR-COMP-001', 'Prepare HR Compliance Reports',
    'Prepare and submit required HR compliance reports to regulatory bodies.',
    '6.0', 'HR', 'quarterly', 1, 3, 4.0, 'cron', 'Y',
    '{"type": "function_head", "functionalArea": "HR"}'
);

-- Add checklist items for Payroll Review
INSERT INTO tija_operational_task_checklists (templateID, itemOrder, itemDescription, isMandatory)
SELECT templateID, 1, 'Review all time entries for the period', 'Y'
FROM tija_operational_task_templates WHERE templateCode = 'HR-PAY-001';

INSERT INTO tija_operational_task_checklists (templateID, itemOrder, itemDescription, isMandatory)
SELECT templateID, 2, 'Verify overtime calculations', 'Y'
FROM tija_operational_task_templates WHERE templateCode = 'HR-PAY-001';

INSERT INTO tija_operational_task_checklists (templateID, itemOrder, itemDescription, isMandatory)
SELECT templateID, 3, 'Check leave balance adjustments', 'Y'
FROM tija_operational_task_templates WHERE templateCode = 'HR-PAY-001';

INSERT INTO tija_operational_task_checklists (templateID, itemOrder, itemDescription, isMandatory)
SELECT templateID, 4, 'Resolve any discrepancies with employees', 'Y'
FROM tija_operational_task_templates WHERE templateCode = 'HR-PAY-001';

