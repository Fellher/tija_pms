# Goals Module - API Reference

## Overview

This document provides API reference for the Goals Module endpoints.

## Base URL

All API endpoints are located in: `php/scripts/goals/`

## Authentication

All endpoints require:
- Valid user session
- `$isValidUser` must be true

## Endpoints

### 1. Manage Goal

**File**: `manage_goal.php`

**Actions**:
- `create` - Create a new goal
- `update` - Update existing goal
- `delete` - Delete goal (soft delete)
- `get` - Get goal details
- `list` - List goals by owner
- `validate_weights` - Validate goal weights sum to 100%

**Request Format**:
```json
{
  "action": "create",
  "goalType": "Strategic",
  "goalTitle": "Goal Title",
  "goalDescription": "Description",
  "startDate": "2025-01-01",
  "endDate": "2025-12-31",
  "weight": 0.25,
  "ownerUserID": 123,
  "status": "Draft"
}
```

**Response Format**:
```json
{
  "success": true,
  "goalUUID": "uuid-here",
  "message": "Goal created successfully"
}
```

### 2. Cascade Goal

**File**: `cascade_goal.php`

**Actions**:
- `cascade` - Execute goal cascade
- `get_path` - Get cascade path for a goal

**Request Format**:
```json
{
  "action": "cascade",
  "parentGoalUUID": "uuid-here",
  "cascadeMode": "Strict",
  "targets": [
    {"type": "Entity", "id": 1},
    {"type": "User", "id": 123}
  ]
}
```

**Response Format**:
```json
{
  "success": true,
  "results": [
    {
      "targetID": 1,
      "targetType": "Entity",
      "goalUUID": "child-uuid",
      "status": "Created"
    }
  ]
}
```

### 3. Submit Evaluation

**File**: `submit_evaluation.php`

**Actions**:
- `submit` - Submit evaluation
- `get` - Get evaluations for goal
- `get_360` - Get 360-degree feedback

**Request Format**:
```json
{
  "action": "submit",
  "goalUUID": "uuid-here",
  "score": 85.5,
  "comments": "Good progress"
}
```

**Response Format**:
```json
{
  "success": true,
  "evaluationID": 456,
  "message": "Evaluation submitted successfully"
}
```

### 4. Suggest Templates

**File**: `suggest_templates.php`

**Request**: GET or POST (no parameters required, uses session user)

**Response Format**:
```json
{
  "success": true,
  "templates": [
    {
      "libraryID": 1,
      "templateCode": "SALE-001",
      "templateName": "Achieve [Target]% Growth",
      "goalType": "Strategic",
      "functionalDomain": "Sales"
    }
  ]
}
```

### 5. Calculate Scores

**File**: `calculate_scores.php`

**Request Format**:
```json
{
  "goalUUID": "uuid-here"
}
```

Or for entity:
```json
{
  "entityID": 1
}
```

**Response Format**:
```json
{
  "success": true,
  "goalUUID": "uuid-here",
  "score": 87.5
}
```

## Error Responses

All endpoints return errors in this format:

```json
{
  "success": false,
  "message": "Error description"
}
```

## Permission Requirements

- `create`: Any authenticated user
- `update`: Goal owner, manager, or admin
- `delete`: Goal owner (non-critical) or L+2 manager (critical)
- `cascade`: Admin or entity manager
- `evaluate`: Assigned evaluator (manager, peer, self, matrix manager)
- `view_global`: Admin only

## Rate Limiting

No rate limiting currently implemented. Consider adding for production.

## Versioning

Current API version: 1.0.0

