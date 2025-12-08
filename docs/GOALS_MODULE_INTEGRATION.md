# Goals Module Integration Guide

## Overview

This document describes how the Goals Module integrates with other TIJA system components, specifically:
- Job Roles (`job_titles`, `job_bands`, `job_categories`)
- Organization Charts (`tija_org_charts`, `tija_org_chart_position_assignments`)
- Organization Structure (`tija_entities`, `tija_organisation_data`)

## Integration Points

### 1. Job Roles Integration

#### Template Suggestions
The Goals Module uses job roles to suggest relevant goal templates:

- **Functional Domain Mapping**: Job categories and titles are mapped to functional domains (Sales, IT, HR, Finance, etc.)
- **Competency Level Mapping**: Job bands are mapped to competency levels (Junior, Senior, Principal, Executive)
- **Location**: `php/classes/goalintegration.php` - `getFunctionalDomainFromJobRole()` and `getCompetencyLevelFromJobRole()`

#### Usage
```php
require_once 'goalintegration.php';

// Get functional domain from job role
$functionalDomain = GoalIntegration::getFunctionalDomainFromJobRole($jobTitleID, $DBConn);

// Get competency level from job band
$competencyLevel = GoalIntegration::getCompetencyLevelFromJobRole($jobBandID, $DBConn);

// Enhanced template suggestions
$templates = GoalIntegration::suggestTemplatesWithJobRole($userID, $DBConn);
```

### 2. Organization Chart Integration

#### Cascade Target Selection
The Goals Module uses org charts to determine cascade targets:

- **Position-Based Cascading**: Goals can be cascaded to employees in specific org chart positions
- **Hierarchical Cascading**: Uses org chart parent-child relationships for hierarchical goal distribution
- **Location**: `php/classes/goalintegration.php` - `getOrgChartForCascading()` and `getCascadeTargetsFromOrgChart()`

#### Usage
```php
require_once 'goalintegration.php';

// Get org chart for entity
$positions = GoalIntegration::getOrgChartForCascading($entityID, $DBConn);

// Get cascade targets from org chart
$targets = GoalIntegration::getCascadeTargetsFromOrgChart($entityID, $positionID, $DBConn);
```

### 3. Organization Structure Integration

#### Hierarchy Management
The Goals Module uses the organization structure for:

- **Closure Table**: Uses `tija_entities` and `tija_org_charts` to build hierarchy closure table
- **Entity-Level Goals**: Goals can be assigned to entities (`ownerEntityID`)
- **Cascade Execution**: Cascades goals across entity hierarchy
- **Location**: `php/classes/goalhierarchy.php` - `buildClosureTable()`, `getDescendants()`, `getAncestors()`

#### Usage
```php
require_once 'goalhierarchy.php';

// Build closure table from org structure
GoalHierarchy::buildClosureTable($entityID, 'Administrative', $DBConn);

// Get descendants for cascading
$descendants = GoalHierarchy::getDescendants($entityID, null, 'Administrative', $DBConn);
```

## Menu Integration

### User Menu
The Goals module is accessible via:
- **URL Pattern**: `?s=user&ss=goals&p={page}`
- **Menu Location**: After "Leave Management" section
- **Menu Items**:
  - Dashboard (`p=dashboard`)
  - My Goals (`p=goal_detail`)
  - Evaluations (`p=evaluations`)
  - Matrix Team (`p=matrix_team`)
  - Automation Settings (`p=settings`)

### Admin Menu
The Goals administration is accessible via:
- **URL Pattern**: `?s=admin&ss=goals&p={page}`
- **Menu Location**: After "Operational Work Admin" section
- **Menu Items**:
  - Dashboard (`p=dashboard`)
  - Goal Library (`p=library`)
  - Cascade Management (`p=cascade`)
  - Evaluation Config (`p=evaluation_config`)
  - Reports & Analytics (`p=reports`)
  - Strategy Map (`p=strategy_map`)
  - AHP Interface (`p=ahp_interface`)

## Database Integration

### Tables Used
1. **Job Roles**:
   - `job_titles` - Job title information
   - `job_bands` - Job band/level information
   - `job_categories` - Job category information

2. **Organization Charts**:
   - `tija_org_charts` - Organization chart definitions
   - `tija_org_chart_position_assignments` - Position assignments

3. **Organization Structure**:
   - `tija_entities` - Entity definitions
   - `tija_organisation_data` - Organization data
   - `tija_org_hierarchy_closure` - Hierarchy closure table (Goals module)

### Foreign Key Relationships
- `tija_goals.ownerEntityID` → `tija_entities.entityID`
- `tija_goals.ownerUserID` → `people.ID`
- `tija_goal_matrix_assignments.employeeUserID` → `people.ID`
- `tija_org_hierarchy_closure` references both entities and users

## API Integration

### Template Suggestions API
**Endpoint**: `php/scripts/goals/suggest_templates.php`

**Integration**: Uses job roles to filter templates
```php
// Automatically uses jobTitleID and jobBandID from user context
$templates = GoalIntegration::suggestTemplatesWithJobRole($userID, $DBConn);
```

### Cascade API
**Endpoint**: `php/scripts/goals/cascade_goal.php`

**Integration**: Uses org chart for target selection
```php
// Can use org chart positions for cascade targets
$targets = GoalIntegration::getCascadeTargetsFromOrgChart($entityID, $positionID, $DBConn);
```

## Configuration

### Functional Domain Mapping
The functional domain mapping can be customized in `GoalIntegration::getFunctionalDomainFromJobRole()`:
- Sales → Sales
- IT/Tech → IT
- HR/Human Resources → HR
- Finance/Accounting → Finance
- Manager/Director → Management

### Competency Level Mapping
The competency level mapping can be customized in `GoalIntegration::getCompetencyLevelFromJobRole()`:
- Junior/Entry → Junior
- Senior/Lead → Senior
- Principal/Expert → Principal
- Executive/Director/VP → Executive

## Best Practices

1. **Template Suggestions**: Always use `GoalIntegration::suggestTemplatesWithJobRole()` for better relevance
2. **Cascade Targets**: Use org chart positions when cascading to ensure proper organizational alignment
3. **Hierarchy Building**: Rebuild closure table after org structure changes
4. **Job Role Updates**: Update goal template suggestions when job roles change

## Future Enhancements

1. **Mapping Table**: Create dedicated mapping table for job roles → functional domains
2. **Org Chart Sync**: Automatic sync between org chart changes and goal assignments
3. **Role-Based Templates**: Pre-assign templates to specific job roles
4. **Position-Based Goals**: Auto-create goals for new positions in org chart

