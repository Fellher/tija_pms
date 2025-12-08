# Goals Module - Test Suite

## Overview

This directory contains unit tests for the Goals Module classes.

## Test Files

- `GoalTest.php` - Tests for Goal class (CRUD operations, validation)
- `GoalEvaluationTest.php` - Tests for GoalEvaluation class (evaluations, scoring)
- `GoalHierarchyTest.php` - Tests for GoalHierarchy class (closure table, traversal)

## Running Tests

### Individual Test File

```bash
php php/tests/goals/GoalTest.php
php php/tests/goals/GoalEvaluationTest.php
php php/tests/goals/GoalHierarchyTest.php
```

### All Tests

```bash
php php/tests/goals/run_all_tests.php
```

## Test Coverage

### Goal Class
- ✓ Goal creation
- ✓ Goal retrieval
- ✓ Goal update
- ✓ Weight validation
- ⚠ Cascade execution (integration test)
- ⚠ Score calculation (integration test)

### GoalEvaluation Class
- ✓ Evaluation submission
- ✓ Weighted score calculation
- ✓ 360 feedback retrieval
- ⚠ Missing evaluation normalization (integration test)

### GoalHierarchy Class
- ✓ Closure table building
- ✓ Descendant retrieval
- ✓ Ancestor retrieval
- ⚠ Cascade modes (integration test)

## Integration Tests

Integration tests should be created for:
- End-to-end cascade workflows
- Matrix assignment workflows
- Multi-rater evaluation completion
- Reporting aggregation
- Currency normalization

## Performance Tests

Performance tests should verify:
- Cascade execution for 1000+ entities < 30 seconds
- Score calculation for entity with 100 goals < 1 second
- Global report generation < 5 seconds

## Notes

- Tests require database connection
- Tests may create test data - cleanup should be implemented
- Some tests require existing data (entities, users)
- Integration tests require full system setup

