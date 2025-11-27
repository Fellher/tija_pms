# Accumulation Policy Historical Reporting Guide

## Overview

When an accumulation policy changes over time, the system needs to accurately report on:
1. **Historical accruals** - Leave accrued under old policy rules
2. **Current accruals** - Leave accrued under new policy rules
3. **Policy change impact** - When and how policies changed

## Current System Architecture

### How Historical Data is Stored

The system uses the `tija_leave_accumulation_history` table to store **immutable snapshots** of each accrual calculation:

```sql
tija_leave_accumulation_history
├── historyID (unique identifier)
├── employeeID (who received the accrual)
├── policyID (which policy was used)
├── accrualDate (when accrual was calculated)
├── baseAccrualRate (ACTUAL rate used at that time) ⭐ KEY FIELD
├── appliedMultiplier (multiplier from rules)
├── finalAccrualAmount (final amount accrued)
├── carryoverAmount
├── totalBalance
└── calculationNotes
```

### Critical Design Feature

**The `baseAccrualRate` field stores the actual rate that was used at the time of calculation**, not the current policy rate. This means:

- ✅ Historical accruals are preserved accurately
- ✅ You can report on old policy rules even after policy changes
- ✅ The history table is the source of truth for historical reporting

## How to Report on Policy Changes

### 1. Report Accruals by Date Range (Before/After Policy Change)

```php
// Get accruals before policy change (e.g., before 2024-06-01)
$oldAccruals = AccumulationPolicy::get_employee_history(
    $employeeID,
    $policyID,
    '2024-01-01',  // start date
    '2024-05-31',  // end date (before change)
    $DBConn
);

// Get accruals after policy change (e.g., after 2024-06-01)
$newAccruals = AccumulationPolicy::get_employee_history(
    $employeeID,
    $policyID,
    '2024-06-01',  // start date (after change)
    '2024-12-31',  // end date
    $DBConn
);
```

### 2. Detect Policy Changes by Comparing Rates

The history table stores the actual rates used. You can detect policy changes by:

```sql
-- Find when policy rate changed by looking at historical rates
SELECT
    DATE_FORMAT(accrualDate, '%Y-%m') as month,
    AVG(baseAccrualRate) as avgRate,
    MIN(baseAccrualRate) as minRate,
    MAX(baseAccrualRate) as maxRate,
    COUNT(*) as accrualCount
FROM tija_leave_accumulation_history
WHERE policyID = ?
GROUP BY DATE_FORMAT(accrualDate, '%Y-%m')
ORDER BY month;
```

If the rate changes between months, the policy was likely updated.

### 3. Compare Historical vs Current Policy

```sql
SELECT
    h.*,
    h.baseAccrualRate as historicalRate,  -- Rate used at time of accrual
    p.accrualRate as currentRate,         -- Current policy rate
    CASE
        WHEN ABS(h.baseAccrualRate - p.accrualRate) > 0.01 THEN 'Y'
        ELSE 'N'
    END as policyChanged
FROM tija_leave_accumulation_history h
LEFT JOIN tija_leave_accumulation_policies p ON h.policyID = p.policyID
WHERE h.employeeID = ?
ORDER BY h.accrualDate DESC;
```

## Reporting Scenarios

### Scenario 1: Report Leave Accrued Under Old Policy

**Question**: "How much leave did employees accrue under the old policy (before June 2024)?"

```php
$oldPolicyAccruals = AccumulationPolicy::get_employee_history(
    $employeeID,
    $policyID,
    '2024-01-01',  // Start of period
    '2024-05-31',  // End before policy change
    $DBConn
);

$totalOldAccrual = array_sum(array_column($oldPolicyAccruals, 'finalAccrualAmount'));
```

### Scenario 2: Report Leave Accrued Under New Policy

**Question**: "How much leave did employees accrue under the new policy (after June 2024)?"

```php
$newPolicyAccruals = AccumulationPolicy::get_employee_history(
    $employeeID,
    $policyID,
    '2024-06-01',  // Start after policy change
    '2024-12-31',  // End of period
    $DBConn
);

$totalNewAccrual = array_sum(array_column($newPolicyAccruals, 'finalAccrualAmount'));
```

### Scenario 3: Compare Policy Impact

**Question**: "What was the difference in accrual rates before and after the policy change?"

```sql
SELECT
    CASE
        WHEN accrualDate < '2024-06-01' THEN 'Old Policy'
        ELSE 'New Policy'
    END as policyPeriod,
    AVG(baseAccrualRate) as avgRate,
    SUM(finalAccrualAmount) as totalAccrued,
    COUNT(*) as accrualCount
FROM tija_leave_accumulation_history
WHERE policyID = ?
AND accrualDate BETWEEN '2024-01-01' AND '2024-12-31'
GROUP BY policyPeriod;
```

## Important Notes

### ⚠️ Policy Updates Don't Affect Historical Data

When you update a policy:
- ✅ Historical accruals remain unchanged (they're immutable)
- ✅ New accruals use the new policy rate
- ✅ The history table stores the rate that was actually used

### ⚠️ Policy Name Changes

If you change a policy name:
- Historical records still reference the same `policyID`
- The policy name in reports will show the **current** name
- Use `policyID` for accurate historical tracking, not policy name

### ⚠️ Policy Deletion

If a policy is deleted (soft delete - `Lapsed = 'Y'`):
- Historical accruals are still accessible
- Reports can still show historical data
- The policy won't appear in active policy lists

## Best Practices

1. **Always use the history table for historical reporting** - Don't rely on current policy data
2. **Use date ranges** to separate old vs new policy periods
3. **Compare `baseAccrualRate` in history** to detect policy changes
4. **Store policy change dates** in a separate audit log if you need exact change timestamps
5. **Use `policyID` for tracking**, not policy name (names can change)

## Future Enhancements

Consider implementing:
1. **Policy versioning table** - Store snapshots of policy changes
2. **Policy audit log** - Track exactly when policies changed
3. **Policy effective dates** - Explicitly define when policies take effect
4. **Policy comparison reports** - Side-by-side comparison of policy versions

## Example: Complete Reporting Query

```sql
-- Get complete accrual history with policy change indicators
SELECT
    h.accrualDate,
    h.accrualPeriod,
    h.baseAccrualRate as rateUsed,
    h.finalAccrualAmount,
    p.accrualRate as currentRate,
    CASE
        WHEN ABS(h.baseAccrualRate - p.accrualRate) > 0.01 THEN 'Policy Changed'
        ELSE 'Policy Unchanged'
    END as status,
    e.employeeName,
    lt.leaveTypeName
FROM tija_leave_accumulation_history h
LEFT JOIN tija_leave_accumulation_policies p ON h.policyID = p.policyID
LEFT JOIN tija_employees e ON h.employeeID = e.employeeID
LEFT JOIN tija_leave_types lt ON h.leaveTypeID = lt.leaveTypeID
WHERE h.policyID = ?
AND h.accrualDate BETWEEN ? AND ?
ORDER BY h.accrualDate DESC;
```

This query shows:
- What rate was actually used (`rateUsed`)
- What the current rate is (`currentRate`)
- Whether the policy has changed since that accrual (`status`)


