-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Create Organizational Hierarchy Closure Table
-- Purpose: Efficient hierarchy traversal using Closure Table pattern
-- Supports dual hierarchy (Administrative vs Functional) for matrix organizations
-- Date: 2025-01-XX
-- ────────────────────────────────────────────────────────────────────────────

-- Table: tija_org_hierarchy_closure
-- Stores all paths from every ancestor to every descendant for efficient queries
-- This eliminates the need for recursive CTEs and provides O(1) lookup performance
CREATE TABLE IF NOT EXISTS `tija_org_hierarchy_closure` (
    `ancestor_id` INT UNSIGNED NOT NULL COMMENT 'FK to tija_entities.entityID or people.ID',
    `descendant_id` INT UNSIGNED NOT NULL COMMENT 'FK to tija_entities.entityID or people.ID',
    `depth` INT NOT NULL DEFAULT 0 COMMENT 'Number of levels between ancestor and descendant',
    `hierarchy_type` ENUM('Administrative', 'Functional') NOT NULL DEFAULT 'Administrative' COMMENT 'Type of hierarchy relationship',
    `ancestor_type` ENUM('Entity', 'Individual') NOT NULL DEFAULT 'Entity' COMMENT 'Type of ancestor node',
    `descendant_type` ENUM('Entity', 'Individual') NOT NULL DEFAULT 'Entity' COMMENT 'Type of descendant node',
    `DateAdded` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `LastUpdate` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`ancestor_id`, `descendant_id`, `hierarchy_type`),
    INDEX `idx_ancestor` (`ancestor_id`, `hierarchy_type`),
    INDEX `idx_descendant` (`descendant_id`, `hierarchy_type`),
    INDEX `idx_depth` (`depth`, `hierarchy_type`),
    INDEX `idx_hierarchy_type` (`hierarchy_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Closure Table for organizational hierarchy - stores all ancestor-descendant paths';

-- Stored Procedure: Build Closure Table for Administrative Hierarchy
-- Populates closure table from existing organizational structure
DROP PROCEDURE IF EXISTS `sp_build_administrative_closure`;

DELIMITER $$

CREATE PROCEDURE `sp_build_administrative_closure`()
BEGIN
    -- Clear existing administrative closure
    DELETE FROM tija_org_hierarchy_closure WHERE hierarchy_type = 'Administrative';

    -- Insert self-references (each node is its own ancestor at depth 0)
    INSERT INTO tija_org_hierarchy_closure (ancestor_id, descendant_id, depth, hierarchy_type, ancestor_type, descendant_type)
    SELECT entityID, entityID, 0, 'Administrative', 'Entity', 'Entity'
    FROM tija_entities
    WHERE Lapsed = 'N' AND Suspended = 'N';

    -- Insert direct parent-child relationships (depth 1) from entity hierarchy
    INSERT INTO tija_org_hierarchy_closure (ancestor_id, descendant_id, depth, hierarchy_type, ancestor_type, descendant_type)
    SELECT
        e.entityParentID,
        e.entityID,
        1,
        'Administrative',
        'Entity',
        'Entity'
    FROM tija_entities e
    WHERE e.entityParentID IS NOT NULL
    AND e.Lapsed = 'N'
    AND e.Suspended = 'N'
    AND NOT EXISTS (
        SELECT 1 FROM tija_org_hierarchy_closure c
        WHERE c.ancestor_id = e.entityParentID
        AND c.descendant_id = e.entityID
        AND c.hierarchy_type = 'Administrative'
    );

    -- Build transitive closure paths iteratively
    closure_loop: WHILE TRUE DO
        INSERT INTO tija_org_hierarchy_closure (ancestor_id, descendant_id, depth, hierarchy_type, ancestor_type, descendant_type)
        SELECT DISTINCT
            c1.ancestor_id,
            c2.descendant_id,
            c1.depth + c2.depth,
            'Administrative',
            c1.ancestor_type,
            c2.descendant_type
        FROM tija_org_hierarchy_closure c1
        INNER JOIN tija_org_hierarchy_closure c2 ON c1.descendant_id = c2.ancestor_id
        WHERE c1.hierarchy_type = 'Administrative'
        AND c2.hierarchy_type = 'Administrative'
        AND c1.ancestor_type = 'Entity'
        AND c1.descendant_type = 'Entity'
        AND c2.ancestor_type = 'Entity'
        AND c2.descendant_type = 'Entity'
        AND c1.depth > 0
        AND c2.depth = 1
        AND NOT EXISTS (
            SELECT 1 FROM tija_org_hierarchy_closure c3
            WHERE c3.ancestor_id = c1.ancestor_id
            AND c3.descendant_id = c2.descendant_id
            AND c3.hierarchy_type = 'Administrative'
        );

        IF ROW_COUNT() = 0 THEN
            LEAVE closure_loop;
        END IF;
    END WHILE closure_loop;

    -- Add user-to-entity relationships (individuals to their entity)
    INSERT INTO tija_org_hierarchy_closure (ancestor_id, descendant_id, depth, hierarchy_type, ancestor_type, descendant_type)
    SELECT
        ud.entityID,
        ud.ID,
        0,
        'Administrative',
        'Entity',
        'Individual'
    FROM user_details ud
    WHERE ud.entityID IS NOT NULL
    AND ud.Lapsed = 'N'
    AND ud.Suspended = 'N'
    AND NOT EXISTS (
        SELECT 1 FROM tija_org_hierarchy_closure c
        WHERE c.ancestor_id = ud.entityID
        AND c.descendant_id = ud.ID
        AND c.hierarchy_type = 'Administrative'
    );

    -- Insert self-references for individuals (each individual is its own ancestor at depth 0)
    INSERT INTO tija_org_hierarchy_closure (ancestor_id, descendant_id, depth, hierarchy_type, ancestor_type, descendant_type)
    SELECT ID, ID, 0, 'Administrative', 'Individual', 'Individual'
    FROM user_details
    WHERE Lapsed = 'N' AND Suspended = 'N';

    -- Add supervisor relationships (individual to individual) - direct relationships are depth 1
    INSERT INTO tija_org_hierarchy_closure (ancestor_id, descendant_id, depth, hierarchy_type, ancestor_type, descendant_type)
    SELECT
        ud1.supervisorID,
        ud1.ID,
        1,
        'Administrative',
        'Individual',
        'Individual'
    FROM user_details ud1
    INNER JOIN user_details ud2 ON ud1.supervisorID = ud2.ID
    WHERE ud1.supervisorID IS NOT NULL
    AND ud1.Lapsed = 'N'
    AND ud1.Suspended = 'N'
    AND ud2.Lapsed = 'N'
    AND ud2.Suspended = 'N'
    AND NOT EXISTS (
        SELECT 1 FROM tija_org_hierarchy_closure c
        WHERE c.ancestor_id = ud1.supervisorID
        AND c.descendant_id = ud1.ID
        AND c.hierarchy_type = 'Administrative'
    );

    -- Build transitive closure for supervisor relationships
    supervisor_loop: WHILE TRUE DO
        INSERT INTO tija_org_hierarchy_closure (ancestor_id, descendant_id, depth, hierarchy_type, ancestor_type, descendant_type)
        SELECT DISTINCT
            c1.ancestor_id,
            c2.descendant_id,
            c1.depth + c2.depth,
            'Administrative',
            'Individual',
            'Individual'
        FROM tija_org_hierarchy_closure c1
        INNER JOIN tija_org_hierarchy_closure c2 ON c1.descendant_id = c2.ancestor_id
        WHERE c1.hierarchy_type = 'Administrative'
        AND c2.hierarchy_type = 'Administrative'
        AND c1.ancestor_type = 'Individual'
        AND c1.descendant_type = 'Individual'
        AND c2.ancestor_type = 'Individual'
        AND c2.descendant_type = 'Individual'
        AND c1.depth > 0
        AND c2.depth = 1
        AND NOT EXISTS (
            SELECT 1 FROM tija_org_hierarchy_closure c3
            WHERE c3.ancestor_id = c1.ancestor_id
            AND c3.descendant_id = c2.descendant_id
            AND c3.hierarchy_type = 'Administrative'
        );

        IF ROW_COUNT() = 0 THEN
            LEAVE supervisor_loop;
        END IF;
    END WHILE supervisor_loop;
END$$

DELIMITER ;

-- Stored Procedure: Get All Descendants
-- Efficiently retrieves all descendants of a given ancestor
DROP PROCEDURE IF EXISTS `sp_get_descendants`;

DELIMITER $$

CREATE PROCEDURE `sp_get_descendants`(
    IN p_ancestor_id INT,
    IN p_hierarchy_type VARCHAR(20),
    IN p_max_depth INT
)
BEGIN
    SELECT
        c.descendant_id,
        c.depth,
        c.descendant_type,
        CASE
            WHEN c.descendant_type = 'Entity' THEN e.entityName
            WHEN c.descendant_type = 'Individual' THEN CONCAT(p.FirstName, ' ', p.Surname)
        END AS descendant_name
    FROM tija_org_hierarchy_closure c
    LEFT JOIN tija_entities e ON c.descendant_type = 'Entity' AND c.descendant_id = e.entityID
    LEFT JOIN people p ON c.descendant_type = 'Individual' AND c.descendant_id = p.ID
    WHERE c.ancestor_id = p_ancestor_id
    AND c.hierarchy_type = p_hierarchy_type
    AND (p_max_depth IS NULL OR c.depth <= p_max_depth)
    AND c.depth > 0 -- Exclude self
    ORDER BY c.depth, descendant_name;
END$$

DELIMITER ;

-- Stored Procedure: Get All Ancestors
-- Efficiently retrieves all ancestors of a given descendant
DROP PROCEDURE IF EXISTS `sp_get_ancestors`;

DELIMITER $$

CREATE PROCEDURE `sp_get_ancestors`(
    IN p_descendant_id INT,
    IN p_hierarchy_type VARCHAR(20),
    IN p_max_depth INT
)
BEGIN
    SELECT
        c.ancestor_id,
        c.depth,
        c.ancestor_type,
        CASE
            WHEN c.ancestor_type = 'Entity' THEN e.entityName
            WHEN c.ancestor_type = 'Individual' THEN CONCAT(p.FirstName, ' ', p.Surname)
        END AS ancestor_name
    FROM tija_org_hierarchy_closure c
    LEFT JOIN tija_entities e ON c.ancestor_type = 'Entity' AND c.ancestor_id = e.entityID
    LEFT JOIN people p ON c.ancestor_type = 'Individual' AND c.ancestor_id = p.ID
    WHERE c.descendant_id = p_descendant_id
    AND c.hierarchy_type = p_hierarchy_type
    AND (p_max_depth IS NULL OR c.depth <= p_max_depth)
    AND c.depth > 0 -- Exclude self
    ORDER BY c.depth, ancestor_name;
END$$

DELIMITER ;

