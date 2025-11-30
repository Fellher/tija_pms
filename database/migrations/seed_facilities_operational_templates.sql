-- Facilities Operational Task Templates

-- HVAC Maintenance (9.0)
INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfMonth, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'FAC-HVAC-001', 'HVAC Filter Replacement',
    'Replace HVAC filters and inspect system components. Document maintenance activities.',
    '9.0', 'Facilities', 'monthly', 1, 1, 2.0, 'cron', 'Y',
    '{"type": "role", "roleID": "FACILITIES_TECH"}'
);

INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyMonthOfYear, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'FAC-HVAC-002', 'Chiller System Inspection',
    'Inspect chiller system, check refrigerant levels, and test system performance.',
    '9.0', 'Facilities', 'quarterly', 1, 1, 4.0, 'cron', 'Y',
    '{"type": "role", "roleID": "FACILITIES_TECH"}'
);

-- Life Safety (9.0)
INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfMonth, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'FAC-SAFETY-001', 'Fire Extinguisher Inspection',
    'Inspect all fire extinguishers, check pressure gauges, and verify inspection tags are current.',
    '9.0', 'Facilities', 'monthly', 1, 1, 2.0, 'cron', 'Y',
    '{"type": "role", "roleID": "FACILITIES_TECH"}'
);

INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfMonth, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'FAC-SAFETY-002', 'Emergency Lighting Test',
    'Test emergency lighting systems and backup power. Document test results and address any issues.',
    '9.0', 'Facilities', 'monthly', 1, 15, 1.5, 'cron', 'Y',
    '{"type": "role", "roleID": "FACILITIES_TECH"}'
);

-- General Maintenance (9.0)
INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyDayOfMonth, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'FAC-MAINT-001', 'Deep Clean Common Areas',
    'Perform deep cleaning of common areas including lobbies, restrooms, and break rooms.',
    '9.0', 'Facilities', 'monthly', 1, 1, 6.0, 'cron', 'Y',
    '{"type": "role", "roleID": "CUSTODIAL"}'
);

INSERT INTO tija_operational_task_templates (
    templateCode, templateName, templateDescription, processID, functionalArea,
    frequencyType, frequencyInterval, frequencyMonthOfYear, estimatedDuration,
    processingMode, isActive, assignmentRule
) VALUES (
    'FAC-MAINT-002', 'Generator Load Test',
    'Perform generator load test to ensure backup power system is operational.',
    '9.0', 'Facilities', 'quarterly', 1, 1, 3.0, 'cron', 'Y',
    '{"type": "role", "roleID": "FACILITIES_TECH"}'
);

