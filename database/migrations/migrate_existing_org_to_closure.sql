-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Populate Organizational Hierarchy Closure Table
-- Purpose: Build closure table from existing organizational structure
-- Date: 2025-01-XX
-- ────────────────────────────────────────────────────────────────────────────

-- This script calls the stored procedure to build the closure table
-- It should be run after create_org_hierarchy_closure_table.sql

-- Build Administrative Hierarchy Closure Table
CALL sp_build_administrative_closure();

-- Verify closure table population
SELECT
    hierarchy_type,
    ancestor_type,
    descendant_type,
    COUNT(*) as path_count,
    MAX(depth) as max_depth
FROM tija_org_hierarchy_closure
GROUP BY hierarchy_type, ancestor_type, descendant_type;

-- Sample query to test: Get all descendants of entity ID 1
-- SELECT * FROM tija_org_hierarchy_closure WHERE ancestor_id = 1 AND hierarchy_type = 'Administrative';

