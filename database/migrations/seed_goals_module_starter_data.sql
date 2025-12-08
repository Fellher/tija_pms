-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Goals Module Starter Data
-- Purpose: Seed default goal library templates, evaluation weights, currency rates,
--          and sample data for organizations to start using the Goals module
-- Date: 2025-12-01
-- ────────────────────────────────────────────────────────────────────────────
--
-- PREREQUISITES:
-- Before running this script, ensure all Goals module tables are created:
-- 1. create_org_hierarchy_closure_table.sql
-- 2. create_goals_core_tables.sql
-- 3. create_goal_library_tables.sql
-- 4. create_goal_evaluation_tables.sql
-- 5. create_goal_matrix_tables.sql
-- 6. create_goal_reporting_tables.sql
-- 7. create_goal_automation_settings.sql (required for PART 7)
--
-- IMPORTANT: Replace entityID = 1 and userID = 1 with actual IDs from your database
-- ────────────────────────────────────────────────────────────────────────────

-- ============================================================================
-- PART 1: GOAL LIBRARY TEMPLATES
-- ============================================================================

-- Strategic Goal Templates (5-Year Horizon)
-- Using INSERT IGNORE to skip if templates already exist
INSERT IGNORE INTO `tija_goal_library` (
    `templateCode`, `templateName`, `templateDescription`, `goalType`,
    `variables`, `defaultKPIs`, `suggestedWeight`, `functionalDomain`,
    `competencyLevel`, `strategicPillar`, `timeHorizon`, `jurisdictionScope`,
    `isActive`, `DateAdded`
) VALUES
-- Revenue Growth Strategic Goals
('STRAT-001', 'Achieve [Target]% Revenue Growth',
    'Strategic goal to drive significant revenue growth over the strategic planning period. Focuses on expanding market share, entering new markets, or launching new products.',
    'Strategic',
    JSON_ARRAY('Target', 'Timeframe', 'Market'),
    JSON_ARRAY(
        JSON_OBJECT('name', 'Revenue Growth Rate', 'target', 25, 'unit', 'percentage'),
        JSON_OBJECT('name', 'Market Share', 'target', 15, 'unit', 'percentage'),
        JSON_OBJECT('name', 'New Customer Acquisition', 'target', 1000, 'unit', 'count')
    ),
    0.3000, 'Sales', 'Executive', 'Revenue', '5-Year', 'Global', 'Y', NOW()),

('STRAT-002', 'Expand into [Region/Market]',
    'Strategic expansion goal to enter new geographical markets or customer segments. Includes market research, regulatory compliance, and infrastructure setup.',
    'Strategic',
    JSON_ARRAY('Region', 'Market Segment', 'Investment Budget'),
    JSON_ARRAY(
        JSON_OBJECT('name', 'Market Entry Success', 'target', 100, 'unit', 'percentage'),
        JSON_OBJECT('name', 'Revenue from New Market', 'target', 5000000, 'unit', 'USD'),
        JSON_OBJECT('name', 'Regulatory Approvals', 'target', 5, 'unit', 'count')
    ),
    0.2500, 'Business Development', 'Executive', 'Revenue', '5-Year', 'Global', 'Y', NOW()),

-- Innovation Strategic Goals
('STRAT-003', 'Launch [Number] Innovative Products/Services',
    'Strategic innovation goal to develop and launch new products or services that create competitive advantage.',
    'Strategic',
    JSON_ARRAY('Number', 'Product Category', 'Innovation Type'),
    JSON_ARRAY(
        JSON_OBJECT('name', 'Products Launched', 'target', 5, 'unit', 'count'),
        JSON_OBJECT('name', 'R&D Investment', 'target', 2000000, 'unit', 'USD'),
        JSON_OBJECT('name', 'Time to Market', 'target', 18, 'unit', 'months')
    ),
    0.2000, 'Product Development', 'Executive', 'Innovation', '5-Year', 'Global', 'Y', NOW()),

('STRAT-004', 'Achieve [Target]% Digital Transformation',
    'Strategic goal to transform business operations through digital technologies, automation, and data-driven decision making.',
    'Strategic',
    JSON_ARRAY('Target', 'Transformation Area', 'Technology Stack'),
    JSON_ARRAY(
        JSON_OBJECT('name', 'Digital Maturity Score', 'target', 80, 'unit', 'percentage'),
        JSON_OBJECT('name', 'Processes Automated', 'target', 50, 'unit', 'count'),
        JSON_OBJECT('name', 'Data Analytics Adoption', 'target', 90, 'unit', 'percentage')
    ),
    0.2500, 'IT', 'Executive', 'Innovation', '5-Year', 'Global', 'Y', NOW()),

-- ESG Strategic Goals
('STRAT-005', 'Achieve Carbon Neutrality by [Year]',
    'Strategic environmental goal to achieve carbon neutrality through renewable energy, efficiency improvements, and carbon offset programs.',
    'Strategic',
    JSON_ARRAY('Year', 'Baseline Emissions', 'Reduction Strategy'),
    JSON_ARRAY(
        JSON_OBJECT('name', 'Carbon Emissions Reduction', 'target', 100, 'unit', 'percentage'),
        JSON_OBJECT('name', 'Renewable Energy Usage', 'target', 100, 'unit', 'percentage'),
        JSON_OBJECT('name', 'ESG Rating Score', 'target', 85, 'unit', 'score')
    ),
    0.1500, 'Operations', 'Executive', 'ESG Impact', '5-Year', 'Global', 'Y', NOW()),

('STRAT-006', 'Achieve [Target]% Employee Engagement Score',
    'Strategic human capital goal to improve employee satisfaction, retention, and productivity through engagement initiatives.',
    'Strategic',
    JSON_ARRAY('Target', 'Engagement Driver', 'Measurement Method'),
    JSON_ARRAY(
        JSON_OBJECT('name', 'Employee Engagement Score', 'target', 85, 'unit', 'percentage'),
        JSON_OBJECT('name', 'Employee Retention Rate', 'target', 90, 'unit', 'percentage'),
        JSON_OBJECT('name', 'Training Hours per Employee', 'target', 40, 'unit', 'hours')
    ),
    0.2000, 'HR', 'Executive', 'Employee Engagement', '5-Year', 'Global', 'Y', NOW()),

-- OKR Templates (Annual/Quarterly)
('OKR-001', 'Increase [Product] Sales by [Target]%',
    'OKR template for sales teams to achieve specific product sales targets through focused execution.',
    'OKR',
    JSON_ARRAY('Product', 'Target', 'Sales Channel'),
    JSON_ARRAY(
        JSON_OBJECT('name', 'Sales Revenue', 'target', 1000000, 'unit', 'USD'),
        JSON_OBJECT('name', 'Units Sold', 'target', 5000, 'unit', 'count'),
        JSON_OBJECT('name', 'Conversion Rate', 'target', 25, 'unit', 'percentage')
    ),
    0.3000, 'Sales', 'All', 'Revenue', 'Annual', 'Global', 'Y', NOW()),

('OKR-002', 'Improve Customer Satisfaction to [Target]%',
    'OKR template focused on improving customer satisfaction scores through service quality improvements.',
    'OKR',
    JSON_ARRAY('Target', 'Customer Segment', 'Satisfaction Metric'),
    JSON_ARRAY(
        JSON_OBJECT('name', 'NPS Score', 'target', 70, 'unit', 'score'),
        JSON_OBJECT('name', 'Customer Satisfaction', 'target', 90, 'unit', 'percentage'),
        JSON_OBJECT('name', 'Customer Complaints', 'target', 5, 'unit', 'count')
    ),
    0.2500, 'Customer Service', 'All', 'Customer Intimacy', 'Quarterly', 'Global', 'Y', NOW()),

('OKR-003', 'Reduce [Process] Cycle Time by [Target]%',
    'OKR template for operational efficiency improvements through process optimization.',
    'OKR',
    JSON_ARRAY('Process', 'Target', 'Current Baseline'),
    JSON_ARRAY(
        JSON_OBJECT('name', 'Cycle Time Reduction', 'target', 30, 'unit', 'percentage'),
        JSON_OBJECT('name', 'Process Efficiency', 'target', 85, 'unit', 'percentage'),
        JSON_OBJECT('name', 'Cost Savings', 'target', 100000, 'unit', 'USD')
    ),
    0.2000, 'Operations', 'All', 'Operational Excellence', 'Quarterly', 'Global', 'Y', NOW()),

('OKR-004', 'Launch [Product/Feature] by [Date]',
    'OKR template for product development teams to deliver new products or features on schedule.',
    'OKR',
    JSON_ARRAY('Product', 'Date', 'Quality Standard'),
    JSON_ARRAY(
        JSON_OBJECT('name', 'On-Time Delivery', 'target', 100, 'unit', 'percentage'),
        JSON_OBJECT('name', 'Quality Score', 'target', 95, 'unit', 'percentage'),
        JSON_OBJECT('name', 'User Adoption', 'target', 80, 'unit', 'percentage')
    ),
    0.3000, 'Product Development', 'All', 'Innovation', 'Quarterly', 'Global', 'Y', NOW()),

-- KPI Templates (Monthly/Quarterly)
('KPI-001', 'Maintain [Target]% Uptime for [System]',
    'KPI template for IT operations to ensure system availability and reliability.',
    'KPI',
    JSON_ARRAY('Target', 'System', 'Measurement Period'),
    JSON_ARRAY(
        JSON_OBJECT('name', 'System Uptime', 'target', 99.9, 'unit', 'percentage'),
        JSON_OBJECT('name', 'Mean Time to Recovery', 'target', 4, 'unit', 'hours'),
        JSON_OBJECT('name', 'Incident Count', 'target', 2, 'unit', 'count')
    ),
    0.2000, 'IT', 'All', 'Operational Excellence', 'Monthly', 'Global', 'Y', NOW()),

('KPI-002', 'Achieve [Target]% Employee Retention Rate',
    'KPI template for HR to track and improve employee retention.',
    'KPI',
    JSON_ARRAY('Target', 'Employee Segment', 'Retention Period'),
    JSON_ARRAY(
        JSON_OBJECT('name', 'Retention Rate', 'target', 90, 'unit', 'percentage'),
        JSON_OBJECT('name', 'Voluntary Turnover', 'target', 5, 'unit', 'percentage'),
        JSON_OBJECT('name', 'Average Tenure', 'target', 36, 'unit', 'months')
    ),
    0.1500, 'HR', 'All', 'Employee Engagement', 'Quarterly', 'Global', 'Y', NOW()),

('KPI-003', 'Process [Target] Invoices per Month',
    'KPI template for finance/accounting teams to measure processing efficiency.',
    'KPI',
    JSON_ARRAY('Target', 'Invoice Type', 'Processing Standard'),
    JSON_ARRAY(
        JSON_OBJECT('name', 'Invoices Processed', 'target', 1000, 'unit', 'count'),
        JSON_OBJECT('name', 'Processing Accuracy', 'target', 99.5, 'unit', 'percentage'),
        JSON_OBJECT('name', 'Average Processing Time', 'target', 2, 'unit', 'days')
    ),
    0.1500, 'Finance', 'All', 'Operational Excellence', 'Monthly', 'Global', 'Y', NOW()),

('KPI-004', 'Generate [Target] Qualified Leads per Month',
    'KPI template for marketing teams to track lead generation performance.',
    'KPI',
    JSON_ARRAY('Target', 'Lead Source', 'Qualification Criteria'),
    JSON_ARRAY(
        JSON_OBJECT('name', 'Qualified Leads', 'target', 500, 'unit', 'count'),
        JSON_OBJECT('name', 'Lead Conversion Rate', 'target', 20, 'unit', 'percentage'),
        JSON_OBJECT('name', 'Cost per Lead', 'target', 50, 'unit', 'USD')
    ),
    0.2000, 'Marketing', 'All', 'Revenue', 'Monthly', 'Global', 'Y', NOW()),

('KPI-005', 'Maintain [Target]% Gross Profit Margin',
    'KPI template for finance/operations to track profitability.',
    'KPI',
    JSON_ARRAY('Target', 'Product Line', 'Cost Category'),
    JSON_ARRAY(
        JSON_OBJECT('name', 'Gross Profit Margin', 'target', 35, 'unit', 'percentage'),
        JSON_OBJECT('name', 'Operating Margin', 'target', 20, 'unit', 'percentage'),
        JSON_OBJECT('name', 'Cost Reduction', 'target', 10, 'unit', 'percentage')
    ),
    0.2500, 'Finance', 'All', 'Revenue', 'Quarterly', 'Global', 'Y', NOW()),

-- Department-Specific Templates
('KPI-006', 'Resolve [Target]% of Support Tickets within SLA',
    'KPI template for customer support teams.',
    'KPI',
    JSON_ARRAY('Target', 'Ticket Priority', 'SLA Standard'),
    JSON_ARRAY(
        JSON_OBJECT('name', 'SLA Compliance', 'target', 95, 'unit', 'percentage'),
        JSON_OBJECT('name', 'Average Resolution Time', 'target', 24, 'unit', 'hours'),
        JSON_OBJECT('name', 'First Contact Resolution', 'target', 80, 'unit', 'percentage')
    ),
    0.2000, 'Customer Service', 'All', 'Customer Intimacy', 'Monthly', 'Global', 'Y', NOW()),

('OKR-005', 'Complete [Number] Training Programs',
    'OKR template for learning and development initiatives.',
    'OKR',
    JSON_ARRAY('Number', 'Training Type', 'Target Audience'),
    JSON_ARRAY(
        JSON_OBJECT('name', 'Programs Completed', 'target', 10, 'unit', 'count'),
        JSON_OBJECT('name', 'Participant Satisfaction', 'target', 85, 'unit', 'percentage'),
        JSON_OBJECT('name', 'Skills Improvement', 'target', 30, 'unit', 'percentage')
    ),
    0.2000, 'HR', 'All', 'Employee Engagement', 'Annual', 'Global', 'Y', NOW());

-- ============================================================================
-- PART 2: DEFAULT EVALUATION WEIGHTS
-- ============================================================================

-- Note: Default weights are handled in application code (see GoalEvaluation::getDefaultWeights())
-- We cannot store NULL goalUUID due to foreign key constraints.
-- Instead, we'll apply default weights to the sample goals created below.
-- Default weights are:
--   Manager: 50%, Self: 20%, Peer: 20%, Subordinate: 10%, Matrix: 0%

-- ============================================================================
-- PART 3: CURRENCY RATES
-- ============================================================================

-- Common currency exchange rates (using USD as base)
-- Note: These are sample rates. In production, these should be updated via API or manual entry
-- Using INSERT IGNORE to skip if rates already exist
INSERT IGNORE INTO `tija_goal_currency_rates` (
    `fromCurrency`, `toCurrency`, `budgetRate`, `spotRate`,
    `effectiveDate`, `expiryDate`, `fiscalYear`, `rateType`, `DateAdded`
) VALUES
-- USD to other currencies (Budget Rate = Fiscal Year Start, Spot Rate = Current)
-- Using YEAR(CURDATE()) for fiscalYear
('USD', 'USD', 1.000000, 1.000000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Spot', NOW()),
('USD', 'EUR', 0.920000, 0.930000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Spot', NOW()),
('USD', 'GBP', 0.790000, 0.800000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Spot', NOW()),
('USD', 'KES', 130.000000, 132.000000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Spot', NOW()),
('USD', 'ZAR', 18.500000, 18.700000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Spot', NOW()),
('USD', 'NGN', 750.000000, 760.000000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Spot', NOW()),
('USD', 'GHS', 12.000000, 12.200000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Spot', NOW()),
('USD', 'JPY', 150.000000, 151.000000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Spot', NOW()),
('USD', 'CNY', 7.200000, 7.250000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Spot', NOW()),
('USD', 'INR', 83.000000, 83.500000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Spot', NOW()),

-- Budget rates (set at fiscal year start)
('USD', 'USD', 1.000000, 1.000000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Budget', NOW()),
('USD', 'EUR', 0.920000, 0.920000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Budget', NOW()),
('USD', 'GBP', 0.790000, 0.790000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Budget', NOW()),
('USD', 'KES', 130.000000, 130.000000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Budget', NOW()),
('USD', 'ZAR', 18.500000, 18.500000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Budget', NOW()),
('USD', 'NGN', 750.000000, 750.000000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Budget', NOW()),
('USD', 'GHS', 12.000000, 12.000000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Budget', NOW()),
('USD', 'JPY', 150.000000, 150.000000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Budget', NOW()),
('USD', 'CNY', 7.200000, 7.200000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Budget', NOW()),
('USD', 'INR', 83.000000, 83.000000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Budget', NOW()),

-- Reverse rates (other currencies to USD) - Spot rates
('EUR', 'USD', 1.086957, 1.075269, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Spot', NOW()),
('GBP', 'USD', 1.265823, 1.250000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Spot', NOW()),
('KES', 'USD', 0.007692, 0.007576, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Spot', NOW()),
('ZAR', 'USD', 0.054054, 0.053476, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Spot', NOW()),
('NGN', 'USD', 0.001333, 0.001316, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Spot', NOW()),
('GHS', 'USD', 0.083333, 0.081967, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Spot', NOW()),
('JPY', 'USD', 0.006667, 0.006623, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Spot', NOW()),
('CNY', 'USD', 0.138889, 0.137931, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Spot', NOW()),
('INR', 'USD', 0.012048, 0.011976, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 YEAR), YEAR(CURDATE()), 'Spot', NOW());

-- ============================================================================
-- PART 4: SAMPLE GOALS FOR ENTITIES
-- ============================================================================

-- Note: This section creates sample goals for the first entity (entityID = 1)
-- Adjust entityID, ownerUserID, and dates based on your actual data
-- These are example goals that organizations can use as templates

-- Sample Strategic Goal: Revenue Growth
-- Replace 'REPLACE_ENTITY_ID' and 'REPLACE_USER_ID' with actual IDs
-- Check if goal already exists before inserting
INSERT INTO `tija_goals` (
    `goalUUID`, `parentGoalUUID`, `ownerEntityID`, `ownerUserID`,
    `libraryRefID`, `goalType`, `goalTitle`, `goalDescription`,
    `propriety`, `weight`, `progressMetric`, `evaluatorConfig`,
    `visibility`, `cascadeMode`, `startDate`, `endDate`, `status`,
    `completionPercentage`, `DateAdded`
)
SELECT
    UUID() as goalUUID,
    NULL as parentGoalUUID,
    1 as ownerEntityID,  -- Replace with actual entityID
    NULL as ownerUserID,
    (SELECT libraryID FROM tija_goal_library WHERE templateCode = 'STRAT-001' LIMIT 1) as libraryRefID,
    'Strategic' as goalType,
    'Achieve 25% Revenue Growth Over 5 Years' as goalTitle,
    'Strategic goal to drive significant revenue growth through market expansion, new product launches, and customer acquisition. This goal aligns with our long-term vision of becoming a market leader in our industry.' as goalDescription,
    'Critical' as propriety,
    0.3000 as weight,
    JSON_OBJECT(
        'current', 0,
        'target', 25,
        'unit', 'percentage',
        'currency', 'USD',
        'baseline', 0
    ) as progressMetric,
    JSON_OBJECT(
        'manager_weight', 0.50,
        'self_weight', 0.20,
        'peer_weight', 0.20,
        'subordinate_weight', 0.10,
        'matrix_weight', 0.00
    ) as evaluatorConfig,
    'Global' as visibility,
    'Aligned' as cascadeMode,
    CURDATE() as startDate,
    DATE_ADD(CURDATE(), INTERVAL 5 YEAR) as endDate,
    'Active' as status,
    0.00 as completionPercentage,
    NOW() as DateAdded
WHERE EXISTS (SELECT 1 FROM tija_entities WHERE entityID = 1 LIMIT 1)
  AND NOT EXISTS (
      SELECT 1 FROM tija_goals
      WHERE goalTitle = 'Achieve 25% Revenue Growth Over 5 Years'
        AND ownerEntityID = 1
        AND sysEndTime IS NULL
        AND Lapsed = 'N'
  );

-- Sample OKR Goal: Customer Satisfaction
INSERT INTO `tija_goals` (
    `goalUUID`, `parentGoalUUID`, `ownerEntityID`, `ownerUserID`,
    `libraryRefID`, `goalType`, `goalTitle`, `goalDescription`,
    `propriety`, `weight`, `progressMetric`, `evaluatorConfig`,
    `visibility`, `cascadeMode`, `startDate`, `endDate`, `status`,
    `completionPercentage`, `DateAdded`
)
SELECT
    UUID() as goalUUID,
    NULL as parentGoalUUID,
    1 as ownerEntityID,
    NULL as ownerUserID,
    (SELECT libraryID FROM tija_goal_library WHERE templateCode = 'OKR-002' LIMIT 1) as libraryRefID,
    'OKR' as goalType,
    'Improve Customer Satisfaction to 90%' as goalTitle,
    'Annual OKR to improve customer satisfaction scores through enhanced service quality, faster response times, and proactive customer engagement.' as goalDescription,
    'High' as propriety,
    0.2500 as weight,
    JSON_OBJECT(
        'current', 75,
        'target', 90,
        'unit', 'percentage',
        'currency', NULL,
        'baseline', 70
    ) as progressMetric,
    JSON_OBJECT(
        'manager_weight', 0.40,
        'self_weight', 0.30,
        'peer_weight', 0.20,
        'subordinate_weight', 0.10,
        'matrix_weight', 0.00
    ) as evaluatorConfig,
    'Public' as visibility,
    'Strict' as cascadeMode,
    CURDATE() as startDate,
    DATE_ADD(CURDATE(), INTERVAL 1 YEAR) as endDate,
    'Active' as status,
    0.00 as completionPercentage,
    NOW() as DateAdded
WHERE EXISTS (SELECT 1 FROM tija_entities WHERE entityID = 1 LIMIT 1)
  AND NOT EXISTS (
      SELECT 1 FROM tija_goals
      WHERE goalTitle = 'Improve Customer Satisfaction to 90%'
        AND ownerEntityID = 1
        AND sysEndTime IS NULL
        AND Lapsed = 'N'
  );

-- Sample KPI Goal: System Uptime
INSERT INTO `tija_goals` (
    `goalUUID`, `parentGoalUUID`, `ownerEntityID`, `ownerUserID`,
    `libraryRefID`, `goalType`, `goalTitle`, `goalDescription`,
    `propriety`, `weight`, `progressMetric`, `evaluatorConfig`,
    `visibility`, `cascadeMode`, `startDate`, `endDate`, `status`,
    `completionPercentage`, `DateAdded`
)
SELECT
    UUID() as goalUUID,
    NULL as parentGoalUUID,
    1 as ownerEntityID,
    NULL as ownerUserID,
    (SELECT libraryID FROM tija_goal_library WHERE templateCode = 'KPI-001' LIMIT 1) as libraryRefID,
    'KPI' as goalType,
    'Maintain 99.9% Uptime for Core Systems' as goalTitle,
    'Monthly KPI to ensure high availability and reliability of critical IT systems and infrastructure.' as goalDescription,
    'Critical' as propriety,
    0.2000 as weight,
    JSON_OBJECT(
        'current', 99.5,
        'target', 99.9,
        'unit', 'percentage',
        'currency', NULL,
        'baseline', 99.0
    ) as progressMetric,
    JSON_OBJECT(
        'manager_weight', 0.60,
        'self_weight', 0.30,
        'peer_weight', 0.10,
        'subordinate_weight', 0.00,
        'matrix_weight', 0.00
    ) as evaluatorConfig,
    'Private' as visibility,
    'None' as cascadeMode,
    CURDATE() as startDate,
    DATE_ADD(CURDATE(), INTERVAL 1 MONTH) as endDate,
    'Active' as status,
    0.00 as completionPercentage,
    NOW() as DateAdded
WHERE EXISTS (SELECT 1 FROM tija_entities WHERE entityID = 1 LIMIT 1)
  AND NOT EXISTS (
      SELECT 1 FROM tija_goals
      WHERE goalTitle = 'Maintain 99.9% Uptime for Core Systems'
        AND ownerEntityID = 1
        AND sysEndTime IS NULL
        AND Lapsed = 'N'
  );

-- ============================================================================
-- PART 4B: APPLY DEFAULT EVALUATION WEIGHTS TO SAMPLE GOALS
-- ============================================================================

-- Apply default evaluation weights to the Strategic Goal (Revenue Growth)
-- Manager weight
INSERT INTO `tija_goal_evaluation_weights` (
    `goalUUID`, `evaluatorRole`, `weight`, `isDefault`, `DateAdded`
)
SELECT
    g.goalUUID,
    'Manager' as evaluatorRole,
    0.5000 as weight,
    'Y' as isDefault,
    NOW() as DateAdded
FROM tija_goals g
WHERE g.goalType = 'Strategic'
  AND g.goalTitle LIKE '%Revenue Growth%'
  AND g.sysEndTime IS NULL
  AND g.Lapsed = 'N'
  AND NOT EXISTS (
      SELECT 1 FROM tija_goal_evaluation_weights w
      WHERE w.goalUUID = g.goalUUID AND w.evaluatorRole = 'Manager'
  )
LIMIT 1;

-- Self weight
INSERT INTO `tija_goal_evaluation_weights` (
    `goalUUID`, `evaluatorRole`, `weight`, `isDefault`, `DateAdded`
)
SELECT
    g.goalUUID,
    'Self' as evaluatorRole,
    0.2000 as weight,
    'Y' as isDefault,
    NOW() as DateAdded
FROM tija_goals g
WHERE g.goalType = 'Strategic'
  AND g.goalTitle LIKE '%Revenue Growth%'
  AND g.sysEndTime IS NULL
  AND g.Lapsed = 'N'
  AND NOT EXISTS (
      SELECT 1 FROM tija_goal_evaluation_weights w
      WHERE w.goalUUID = g.goalUUID AND w.evaluatorRole = 'Self'
  )
LIMIT 1;

-- Peer weight
INSERT INTO `tija_goal_evaluation_weights` (
    `goalUUID`, `evaluatorRole`, `weight`, `isDefault`, `DateAdded`
)
SELECT
    g.goalUUID,
    'Peer' as evaluatorRole,
    0.2000 as weight,
    'Y' as isDefault,
    NOW() as DateAdded
FROM tija_goals g
WHERE g.goalType = 'Strategic'
  AND g.goalTitle LIKE '%Revenue Growth%'
  AND g.sysEndTime IS NULL
  AND g.Lapsed = 'N'
  AND NOT EXISTS (
      SELECT 1 FROM tija_goal_evaluation_weights w
      WHERE w.goalUUID = g.goalUUID AND w.evaluatorRole = 'Peer'
  )
LIMIT 1;

-- Subordinate weight
INSERT INTO `tija_goal_evaluation_weights` (
    `goalUUID`, `evaluatorRole`, `weight`, `isDefault`, `DateAdded`
)
SELECT
    g.goalUUID,
    'Subordinate' as evaluatorRole,
    0.1000 as weight,
    'Y' as isDefault,
    NOW() as DateAdded
FROM tija_goals g
WHERE g.goalType = 'Strategic'
  AND g.goalTitle LIKE '%Revenue Growth%'
  AND g.sysEndTime IS NULL
  AND g.Lapsed = 'N'
  AND NOT EXISTS (
      SELECT 1 FROM tija_goal_evaluation_weights w
      WHERE w.goalUUID = g.goalUUID AND w.evaluatorRole = 'Subordinate'
  )
LIMIT 1;

-- Apply default evaluation weights to the OKR Goal (Customer Satisfaction)
-- Manager weight
INSERT INTO `tija_goal_evaluation_weights` (
    `goalUUID`, `evaluatorRole`, `weight`, `isDefault`, `DateAdded`
)
SELECT
    g.goalUUID,
    'Manager' as evaluatorRole,
    0.4000 as weight,
    'Y' as isDefault,
    NOW() as DateAdded
FROM tija_goals g
WHERE g.goalType = 'OKR'
  AND g.goalTitle LIKE '%Customer Satisfaction%'
  AND g.sysEndTime IS NULL
  AND g.Lapsed = 'N'
  AND NOT EXISTS (
      SELECT 1 FROM tija_goal_evaluation_weights w
      WHERE w.goalUUID = g.goalUUID AND w.evaluatorRole = 'Manager'
  )
LIMIT 1;

-- Self weight
INSERT INTO `tija_goal_evaluation_weights` (
    `goalUUID`, `evaluatorRole`, `weight`, `isDefault`, `DateAdded`
)
SELECT
    g.goalUUID,
    'Self' as evaluatorRole,
    0.3000 as weight,
    'Y' as isDefault,
    NOW() as DateAdded
FROM tija_goals g
WHERE g.goalType = 'OKR'
  AND g.goalTitle LIKE '%Customer Satisfaction%'
  AND g.sysEndTime IS NULL
  AND g.Lapsed = 'N'
  AND NOT EXISTS (
      SELECT 1 FROM tija_goal_evaluation_weights w
      WHERE w.goalUUID = g.goalUUID AND w.evaluatorRole = 'Self'
  )
LIMIT 1;

-- Peer weight
INSERT INTO `tija_goal_evaluation_weights` (
    `goalUUID`, `evaluatorRole`, `weight`, `isDefault`, `DateAdded`
)
SELECT
    g.goalUUID,
    'Peer' as evaluatorRole,
    0.2000 as weight,
    'Y' as isDefault,
    NOW() as DateAdded
FROM tija_goals g
WHERE g.goalType = 'OKR'
  AND g.goalTitle LIKE '%Customer Satisfaction%'
  AND g.sysEndTime IS NULL
  AND g.Lapsed = 'N'
  AND NOT EXISTS (
      SELECT 1 FROM tija_goal_evaluation_weights w
      WHERE w.goalUUID = g.goalUUID AND w.evaluatorRole = 'Peer'
  )
LIMIT 1;

-- Subordinate weight
INSERT INTO `tija_goal_evaluation_weights` (
    `goalUUID`, `evaluatorRole`, `weight`, `isDefault`, `DateAdded`
)
SELECT
    g.goalUUID,
    'Subordinate' as evaluatorRole,
    0.1000 as weight,
    'Y' as isDefault,
    NOW() as DateAdded
FROM tija_goals g
WHERE g.goalType = 'OKR'
  AND g.goalTitle LIKE '%Customer Satisfaction%'
  AND g.sysEndTime IS NULL
  AND g.Lapsed = 'N'
  AND NOT EXISTS (
      SELECT 1 FROM tija_goal_evaluation_weights w
      WHERE w.goalUUID = g.goalUUID AND w.evaluatorRole = 'Subordinate'
  )
LIMIT 1;

-- Apply default evaluation weights to the KPI Goal (System Uptime)
-- Manager weight
INSERT INTO `tija_goal_evaluation_weights` (
    `goalUUID`, `evaluatorRole`, `weight`, `isDefault`, `DateAdded`
)
SELECT
    g.goalUUID,
    'Manager' as evaluatorRole,
    0.6000 as weight,
    'Y' as isDefault,
    NOW() as DateAdded
FROM tija_goals g
WHERE g.goalType = 'KPI'
  AND g.goalTitle LIKE '%Uptime%'
  AND g.sysEndTime IS NULL
  AND g.Lapsed = 'N'
  AND NOT EXISTS (
      SELECT 1 FROM tija_goal_evaluation_weights w
      WHERE w.goalUUID = g.goalUUID AND w.evaluatorRole = 'Manager'
  )
LIMIT 1;

-- Self weight
INSERT INTO `tija_goal_evaluation_weights` (
    `goalUUID`, `evaluatorRole`, `weight`, `isDefault`, `DateAdded`
)
SELECT
    g.goalUUID,
    'Self' as evaluatorRole,
    0.3000 as weight,
    'Y' as isDefault,
    NOW() as DateAdded
FROM tija_goals g
WHERE g.goalType = 'KPI'
  AND g.goalTitle LIKE '%Uptime%'
  AND g.sysEndTime IS NULL
  AND g.Lapsed = 'N'
  AND NOT EXISTS (
      SELECT 1 FROM tija_goal_evaluation_weights w
      WHERE w.goalUUID = g.goalUUID AND w.evaluatorRole = 'Self'
  )
LIMIT 1;

-- Peer weight
INSERT INTO `tija_goal_evaluation_weights` (
    `goalUUID`, `evaluatorRole`, `weight`, `isDefault`, `DateAdded`
)
SELECT
    g.goalUUID,
    'Peer' as evaluatorRole,
    0.1000 as weight,
    'Y' as isDefault,
    NOW() as DateAdded
FROM tija_goals g
WHERE g.goalType = 'KPI'
  AND g.goalTitle LIKE '%Uptime%'
  AND g.sysEndTime IS NULL
  AND g.Lapsed = 'N'
  AND NOT EXISTS (
      SELECT 1 FROM tija_goal_evaluation_weights w
      WHERE w.goalUUID = g.goalUUID AND w.evaluatorRole = 'Peer'
  )
LIMIT 1;

-- ============================================================================
-- PART 5: OKR-SPECIFIC DATA
-- ============================================================================

-- Sample OKR data for the customer satisfaction OKR created above
-- Using INSERT IGNORE to skip if OKR data already exists
INSERT IGNORE INTO `tija_goal_okrs` (
    `goalUUID`, `objective`, `keyResults`, `DateAdded`
)
SELECT
    g.goalUUID,
    'Deliver exceptional customer experiences that exceed expectations and drive loyalty' as objective,
    JSON_ARRAY(
        JSON_OBJECT(
            'keyResult', 'Achieve 90% customer satisfaction score',
            'current', 75,
            'target', 90,
            'unit', 'percentage',
            'weight', 0.40
        ),
        JSON_OBJECT(
            'keyResult', 'Maintain NPS score above 70',
            'current', 60,
            'target', 70,
            'unit', 'score',
            'weight', 0.35
        ),
        JSON_OBJECT(
            'keyResult', 'Reduce customer complaints to less than 5 per month',
            'current', 15,
            'target', 5,
            'unit', 'count',
            'weight', 0.25
        )
    ) as keyResults,
    NOW() as DateAdded
FROM tija_goals g
WHERE g.goalType = 'OKR'
  AND g.goalTitle LIKE '%Customer Satisfaction%'
  AND g.sysEndTime IS NULL
  AND g.Lapsed = 'N'
  AND NOT EXISTS (
      SELECT 1 FROM tija_goal_okrs okr
      WHERE okr.goalUUID = g.goalUUID
  )
LIMIT 1;

-- ============================================================================
-- PART 6: KPI-SPECIFIC DATA
-- ============================================================================

-- Sample KPI data for the system uptime KPI created above
-- Using INSERT IGNORE to skip if KPI data already exists
INSERT IGNORE INTO `tija_goal_kpis` (
    `goalUUID`, `kpiName`, `kpiDescription`, `measurementFrequency`,
    `baselineValue`, `targetValue`, `currentValue`, `currencyCode`,
    `reportingRate`, `DateAdded`
)
SELECT
    g.goalUUID,
    'System Uptime Percentage' as kpiName,
    'Percentage of time that core IT systems are operational and accessible to users' as kpiDescription,
    'Monthly' as measurementFrequency,
    99.0 as baselineValue,
    99.9 as targetValue,
    99.5 as currentValue,
    NULL as currencyCode,
    NULL as reportingRate,
    NOW() as DateAdded
FROM tija_goals g
WHERE g.goalType = 'KPI'
  AND g.goalTitle LIKE '%Uptime%'
  AND g.sysEndTime IS NULL
  AND g.Lapsed = 'N'
  AND NOT EXISTS (
      SELECT 1 FROM tija_goal_kpis kpi
      WHERE kpi.goalUUID = g.goalUUID
  )
LIMIT 1;

-- ============================================================================
-- PART 7: DEFAULT AUTOMATION SETTINGS (Optional - for system admin)
-- ============================================================================

-- NOTE: This section is commented out because it requires the
-- tija_goal_automation_settings table to exist.
--
-- TO ENABLE THIS SECTION:
-- 1. First run: create_goal_automation_settings.sql
-- 2. Then uncomment the INSERT statements below
-- 3. Replace '1' with actual admin user ID if different
--
-- Note: Automation settings are typically user-specific, but you can create
-- default settings for a system admin user (ID = 1) as an example

/*
-- Score calculation automation
INSERT IGNORE INTO `tija_goal_automation_settings` (
    `userID`, `automationType`, `isEnabled`, `executionMode`,
    `scheduleFrequency`, `notificationPreference`, `DateAdded`
)
SELECT
    1 as userID,  -- Replace with actual admin user ID
    'score_calculation' as automationType,
    'Y' as isEnabled,
    'automatic' as executionMode,
    'daily' as scheduleFrequency,
    'both' as notificationPreference,
    NOW() as DateAdded
WHERE EXISTS (SELECT 1 FROM people WHERE ID = 1 LIMIT 1);

-- Snapshot generation automation
INSERT IGNORE INTO `tija_goal_automation_settings` (
    `userID`, `automationType`, `isEnabled`, `executionMode`,
    `scheduleFrequency`, `notificationPreference`, `DateAdded`
)
SELECT
    1 as userID,
    'snapshot_generation' as automationType,
    'Y' as isEnabled,
    'automatic' as executionMode,
    'weekly' as scheduleFrequency,
    'both' as notificationPreference,
    NOW() as DateAdded
WHERE EXISTS (SELECT 1 FROM people WHERE ID = 1 LIMIT 1);

-- Evaluation reminders automation
INSERT IGNORE INTO `tija_goal_automation_settings` (
    `userID`, `automationType`, `isEnabled`, `executionMode`,
    `scheduleFrequency`, `notificationPreference`, `DateAdded`
)
SELECT
    1 as userID,
    'evaluation_reminders' as automationType,
    'Y' as isEnabled,
    'automatic' as executionMode,
    'daily' as scheduleFrequency,
    'both' as notificationPreference,
    NOW() as DateAdded
WHERE EXISTS (SELECT 1 FROM people WHERE ID = 1 LIMIT 1);

-- Deadline alerts automation
INSERT IGNORE INTO `tija_goal_automation_settings` (
    `userID`, `automationType`, `isEnabled`, `executionMode`,
    `scheduleFrequency`, `notificationPreference`, `DateAdded`
)
SELECT
    1 as userID,
    'deadline_alerts' as automationType,
    'Y' as isEnabled,
    'automatic' as executionMode,
    'daily' as scheduleFrequency,
    'both' as notificationPreference,
    NOW() as DateAdded
WHERE EXISTS (SELECT 1 FROM people WHERE ID = 1 LIMIT 1);
*/

-- ============================================================================
-- PART 8: SAMPLE PERFORMANCE SNAPSHOTS (Optional - for demonstration)
-- ============================================================================

-- Create a sample snapshot for the revenue growth goal
INSERT IGNORE INTO `tija_goal_performance_snapshots` (
    `goalUUID`, `snapshotDate`, `currentScore`, `targetValue`,
    `actualValue`, `completionPercentage`, `status`, `DateAdded`
)
SELECT
    g.goalUUID,
    CURDATE() as snapshotDate,
    0.00 as currentScore,
    25.0 as targetValue,
    0.0 as actualValue,
    0.00 as completionPercentage,
    'On Track' as status,
    NOW() as DateAdded
FROM tija_goals g
WHERE g.goalType = 'Strategic'
  AND g.goalTitle LIKE '%Revenue Growth%'
  AND g.sysEndTime IS NULL
  AND g.Lapsed = 'N'
LIMIT 1;

-- ============================================================================
-- SUMMARY
-- ============================================================================
-- This seed data includes:
-- 1. 15 Goal Library Templates (6 Strategic, 5 OKR, 4 KPI)
-- 2. Default Evaluation Weights (applied to sample goals - handled in app code for new goals)
-- 3. Currency Exchange Rates (10 currency pairs with Budget and Spot rates)
-- 4. Sample Goals (3 goals: 1 Strategic, 1 OKR, 1 KPI)
-- 5. Evaluation Weights (applied to the 3 sample goals)
-- 6. OKR-specific data (key results for customer satisfaction OKR)
-- 7. KPI-specific data (metrics for system uptime KPI)
-- 8. Default Automation Settings (4 automation types for admin user) - COMMENTED OUT
--    (Uncomment PART 7 after running create_goal_automation_settings.sql)
-- 9. Sample Performance Snapshot (1 snapshot for revenue growth goal)
--
-- NOTE: Before running this script:
-- 1. Ensure all Goals module tables are created
-- 2. Replace entityID = 1 with actual entity IDs from your database
-- 3. Replace userID = 1 with actual admin user ID
-- 4. Adjust dates and values as needed for your organization
-- 5. Review and customize templates to match your business needs
-- ============================================================================

