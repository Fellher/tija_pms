-- Default Operational Projects (BAU Buckets)
-- Create FY operational projects for each functional area

SET @currentYear = YEAR(CURDATE());
SET @nextYear = @currentYear + 1;

-- Finance Operations
INSERT INTO tija_operational_projects (
    projectCode, projectName, functionalArea, fiscalYear, allocatedHours, fteRequirement
) VALUES (
    CONCAT('FY', @currentYear, '-FIN-OPS'),
    CONCAT('FY', @currentYear, ' Finance Operations'),
    'Finance', @currentYear, 2080, 1.0
)
ON DUPLICATE KEY UPDATE projectName = VALUES(projectName);

-- HR Operations
INSERT INTO tija_operational_projects (
    projectCode, projectName, functionalArea, fiscalYear, allocatedHours, fteRequirement
) VALUES (
    CONCAT('FY', @currentYear, '-HR-OPS'),
    CONCAT('FY', @currentYear, ' HR Operations'),
    'HR', @currentYear, 2080, 1.0
)
ON DUPLICATE KEY UPDATE projectName = VALUES(projectName);

-- IT Operations
INSERT INTO tija_operational_projects (
    projectCode, projectName, functionalArea, fiscalYear, allocatedHours, fteRequirement
) VALUES (
    CONCAT('FY', @currentYear, '-IT-OPS'),
    CONCAT('FY', @currentYear, ' IT Operations'),
    'IT', @currentYear, 4160, 2.0
)
ON DUPLICATE KEY UPDATE projectName = VALUES(projectName);

-- Sales Operations
INSERT INTO tija_operational_projects (
    projectCode, projectName, functionalArea, fiscalYear, allocatedHours, fteRequirement
) VALUES (
    CONCAT('FY', @currentYear, '-SALES-OPS'),
    CONCAT('FY', @currentYear, ' Sales Operations'),
    'Sales', @currentYear, 2080, 1.0
)
ON DUPLICATE KEY UPDATE projectName = VALUES(projectName);

-- Marketing Operations
INSERT INTO tija_operational_projects (
    projectCode, projectName, functionalArea, fiscalYear, allocatedHours, fteRequirement
) VALUES (
    CONCAT('FY', @currentYear, '-MKT-OPS'),
    CONCAT('FY', @currentYear, ' Marketing Operations'),
    'Marketing', @currentYear, 2080, 1.0
)
ON DUPLICATE KEY UPDATE projectName = VALUES(projectName);

-- Legal Operations
INSERT INTO tija_operational_projects (
    projectCode, projectName, functionalArea, fiscalYear, allocatedHours, fteRequirement
) VALUES (
    CONCAT('FY', @currentYear, '-LEGAL-OPS'),
    CONCAT('FY', @currentYear, ' Legal Operations'),
    'Legal', @currentYear, 1040, 0.5
)
ON DUPLICATE KEY UPDATE projectName = VALUES(projectName);

-- Facilities Operations
INSERT INTO tija_operational_projects (
    projectCode, projectName, functionalArea, fiscalYear, allocatedHours, fteRequirement
) VALUES (
    CONCAT('FY', @currentYear, '-FAC-OPS'),
    CONCAT('FY', @currentYear, ' Facilities Operations'),
    'Facilities', @currentYear, 2080, 1.0
)
ON DUPLICATE KEY UPDATE projectName = VALUES(projectName);

