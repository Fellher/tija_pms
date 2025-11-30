-- IT Operational Task Templates

-- Server Patch Management (10.0)
INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfWeek, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'IT-PATCH-001', 'Patch Assessment and Planning',
    'Assess available patches, review security bulletins, and plan patch deployment schedule.',
    '10.0', 'IT', 'weekly', 1, 1, 2.0, 'both', 'Y',
    '{"type": "role", "roleID": "IT_ADMIN"}'
);

INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfWeek, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'IT-PATCH-002', 'Staging Environment Patch Deployment',
    'Deploy patches to staging environment and perform testing.',
    '10.0', 'IT', 'weekly', 1, 2, 4.0, 'both', 'Y',
    '{"type": "role", "roleID": "IT_ADMIN"}'
);

INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfWeek, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'IT-PATCH-003', 'Production Patch Rollout',
    'Deploy patches to production environment during maintenance window.',
    '10.0', 'IT', 'weekly', 1, 5, 6.0, 'both', 'Y',
    '{"type": "role", "roleID": "IT_ADMIN"}'
);

-- Backup Verification (10.0)
INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfWeek, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'IT-BACKUP-001', 'Verify Backup Integrity',
    'Verify backup integrity by performing test restores and checking backup logs.',
    '10.0', 'IT', 'daily', 1, NULL, 1.0, 'cron', 'Y',
    '{"type": "role", "roleID": "IT_ADMIN"}'
);

-- Security Audits (11.0)
INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfMonth, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'IT-SEC-001', 'Monthly Security Audit',
    'Conduct monthly security audit including access reviews, vulnerability scans, and compliance checks.',
    '11.0', 'IT', 'monthly', 1, 15, 8.0, 'cron', 'Y',
    '{"type": "function_head", "functionalArea": "IT"}'
);

-- Help Desk Operations (Bucket tracking)
INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, estimatedDuration, processingMode, isActive, assignmentRule
) VALUES (
    'IT-HELPDESK-001', 'Help Desk Ticket Resolution',
    'Resolve help desk tickets and provide technical support to users. Track time against operational project bucket.',
    '10.0', 'IT', 'custom', 0.0, 'manual', 'Y',
    '{"type": "role", "roleID": "HELPDESK_TECH"}'
);

