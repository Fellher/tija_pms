-- ────────────────────────────────────────────────────────────────────────────
-- Migration: Seed Goal Library Templates
-- Purpose: Pre-populate Goal Library with common templates
-- Date: 2025-01-XX
-- ────────────────────────────────────────────────────────────────────────────

-- Sales Goals
INSERT INTO `tija_goal_library` (
    `templateCode`, `templateName`, `templateDescription`, `goalType`,
    `variables`, `suggestedWeight`, `functionalDomain`, `competencyLevel`,
    `strategicPillar`, `timeHorizon`, `jurisdictionScope`, `isActive`
) VALUES
('SALE-001', 'Achieve [Target]% Growth in [Product] Sales', 'Increase sales revenue for specific product line', 'Strategic',
    '["Target", "Product"]', 0.3000, 'Sales', 'All', 'Revenue', 'Annual', 'Global', 'Y'),

('SALE-002', 'Acquire [Number] New Customers in [Region]', 'Customer acquisition goal', 'OKR',
    '["Number", "Region"]', 0.2500, 'Sales', 'All', 'Revenue', 'Quarterly', 'Global', 'Y'),

('SALE-003', 'Maintain Customer Retention Rate Above [Target]%', 'Customer retention KPI', 'KPI',
    '["Target"]', 0.2000, 'Sales', 'All', 'Customer Intimacy', 'Monthly', 'Global', 'Y'),

-- IT Goals
('IT-001', 'Maintain System Uptime Above [Target]%', 'System availability KPI', 'KPI',
    '["Target"]', 0.2500, 'IT', 'All', 'Innovation', 'Continuous', 'Global', 'Y'),

('IT-002', 'Reduce Technical Debt by [Target]%', 'Technical debt reduction', 'OKR',
    '["Target"]', 0.3000, 'IT', 'Senior', 'Innovation', 'Quarterly', 'Global', 'Y'),

('IT-003', 'Complete [Number] Security Audits', 'Security compliance goal', 'Strategic',
    '["Number"]', 0.2000, 'IT', 'All', 'Innovation', 'Annual', 'Global', 'Y'),

-- HR Goals
('HR-001', 'Achieve Employee Retention Rate of [Target]%', 'Retention KPI', 'KPI',
    '["Target"]', 0.2500, 'HR', 'All', 'ESG', 'Monthly', 'Global', 'Y'),

('HR-002', 'Complete [Number] Employee Development Plans', 'Development goal', 'OKR',
    '["Number"]', 0.3000, 'HR', 'All', 'ESG', 'Quarterly', 'Global', 'Y'),

('HR-003', 'Improve Employee Engagement Score to [Target]', 'Engagement improvement', 'Strategic',
    '["Target"]', 0.3500, 'HR', 'Executive', 'ESG', 'Annual', 'Global', 'Y'),

-- Executive Goals
('EXEC-001', 'Achieve Revenue Target of [Amount] [Currency]', 'Revenue target', 'Strategic',
    '["Amount", "Currency"]', 0.4000, 'Executive', 'Executive', 'Revenue', 'Annual', 'Global', 'Y'),

('EXEC-002', 'Reduce Operating Costs by [Target]%', 'Cost reduction goal', 'OKR',
    '["Target"]', 0.3000, 'Executive', 'Executive', 'Revenue', 'Quarterly', 'Global', 'Y'),

('EXEC-003', 'Achieve Carbon Neutrality by [Year]', 'ESG goal', 'Strategic',
    '["Year"]', 0.3500, 'Executive', 'Executive', 'ESG', '5-Year', 'Global', 'Y');

-- Default Evaluation Weights Configuration
-- These will be used as defaults when creating goals
-- Note: Actual weights are stored per-goal in tija_goal_evaluation_weights

-- Currency Rates (Sample - should be updated with actual rates)
INSERT INTO `tija_goal_currency_rates` (
    `fromCurrency`, `toCurrency`, `budgetRate`, `spotRate`,
    `effectiveDate`, `fiscalYear`, `rateType`
) VALUES
('USD', 'USD', 1.000000, 1.000000, CURDATE(), YEAR(CURDATE()), 'Budget'),
('EUR', 'USD', 1.100000, 1.080000, CURDATE(), YEAR(CURDATE()), 'Budget'),
('GBP', 'USD', 1.250000, 1.270000, CURDATE(), YEAR(CURDATE()), 'Budget'),
('JPY', 'USD', 0.006700, 0.006500, CURDATE(), YEAR(CURDATE()), 'Budget'),
('CNY', 'USD', 0.140000, 0.138000, CURDATE(), YEAR(CURDATE()), 'Budget'),
('KES', 'USD', 0.007000, 0.006800, CURDATE(), YEAR(CURDATE()), 'Budget');

-- Note: Update these rates with actual exchange rates for your fiscal year

