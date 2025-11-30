-- Sales & Marketing Operational Task Templates

-- SDR Daily Routine (3.0)
INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfWeek, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'SALES-SDR-001', 'Daily Prospecting Block',
    'Daily prospecting block: research prospects, identify decision makers, and build target lists.',
    '3.0', 'Sales', 'daily', 1, NULL, 2.0, 'both', 'Y',
    '{"type": "role", "roleID": "SDR"}'
);

INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfWeek, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'SALES-SDR-002', 'Daily Outreach Block',
    'Daily outreach block: send personalized emails, make calls, and follow up on previous outreach.',
    '3.0', 'Sales', 'daily', 1, NULL, 3.0, 'both', 'Y',
    '{"type": "role", "roleID": "SDR"}'
);

INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfWeek, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'SALES-SDR-003', 'CRM Hygiene and Data Entry',
    'Update CRM with call notes, meeting outcomes, and prospect information. Maintain data quality.',
    '3.0', 'Sales', 'daily', 1, NULL, 1.0, 'both', 'Y',
    '{"type": "role", "roleID": "SDR"}'
);

-- Marketing Content Operations (3.0)
INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfWeek, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'MKT-CONTENT-001', 'Social Media Calendar Preparation',
    'Prepare and schedule social media content calendar for the upcoming week.',
    '3.0', 'Marketing', 'weekly', 1, 1, 3.0, 'both', 'Y',
    '{"type": "role", "roleID": "MARKETING_COORD"}'
);

INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfWeek, estimatedDuration,
    processingMode, isActive, assignmentRule, requiresApproval
) VALUES (
    'MKT-CONTENT-002', 'Content Approval Cycle',
    'Review and approve marketing content before publication. Ensure brand compliance and quality standards.',
    '3.0', 'Marketing', 'weekly', 1, 2, 2.0, 'both', 'Y',
    '{"type": "role", "roleID": "MARKETING_MANAGER"}', 'Y'
);

INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfMonth, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'MKT-CONTENT-003', 'Content Performance Review',
    'Review content performance metrics, analyze engagement data, and identify optimization opportunities.',
    '3.0', 'Marketing', 'monthly', 1, 1, 4.0, 'cron', 'Y',
    '{"type": "function_head", "functionalArea": "Marketing"}'
);

