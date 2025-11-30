-- Legal & Compliance Operational Task Templates

-- Annual Report Filing (12.0)
INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyMonthOfYear, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'LEGAL-REP-001', 'Annual Report Filing',
    'Prepare and file annual reports with regulatory authorities. Compile required documentation and ensure compliance.',
    '12.0', 'Legal', 'annually', 1, 3, 40.0, 'cron', 'Y',
    '{"type": "function_head", "functionalArea": "Legal"}'
);

-- Board Meeting Preparation (12.0)
INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfMonth, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'LEGAL-BOARD-001', 'Board Meeting Preparation',
    'Prepare board meeting materials including agendas, reports, and supporting documents.',
    '12.0', 'Legal', 'monthly', 1, 1, 8.0, 'cron', 'Y',
    '{"type": "function_head", "functionalArea": "Legal"}'
);

-- Contract Reviews (12.0)
INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, estimatedDuration, processingMode, isActive, assignmentRule
) VALUES (
    'LEGAL-CONTRACT-001', 'Contract Review and Approval',
    'Review contracts for legal compliance, risk assessment, and approval. Track contract lifecycle.',
    '12.0', 'Legal', 'custom', 4.0, 'manual', 'Y',
    '{"type": "role", "roleID": "LEGAL_COUNSEL"}'
);

-- Regulatory Compliance (11.0)
INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyMonthOfYear, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'LEGAL-COMP-001', 'GDPR/CCPA Compliance Audit',
    'Conduct quarterly GDPR/CCPA compliance audit. Review data handling practices and privacy policies.',
    '11.0', 'Legal', 'quarterly', 1, 3, 12.0, 'cron', 'Y',
    '{"type": "function_head", "functionalArea": "Legal"}'
);

INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyMonthOfYear, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'LEGAL-COMP-002', 'License Renewal Review',
    'Review and renew business licenses and permits. Track expiration dates and renewal requirements.',
    '11.0', 'Legal', 'quarterly', 1, 1, 4.0, 'cron', 'Y',
    '{"type": "role", "roleID": "COMPLIANCE_OFFICER"}'
);

