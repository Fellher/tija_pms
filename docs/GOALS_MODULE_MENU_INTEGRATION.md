# Goals Module - Menu Integration Summary

## Overview

The Goals & Performance module has been successfully integrated into the TIJA platform navigation menu for both users and administrators.

## Menu Items Added

### User Menu Section

**Location**: After "Leave Management" section
**Icon**: `ri-target-line`
**Menu Label**: "Goals & Performance"

#### Sub-menu Items:

1. **Dashboard**
   - URL: `?s=user&ss=goals&p=dashboard`
   - Icon: `ri-dashboard-line`
   - Page: `html/pages/user/goals/dashboard.php`

2. **My Goals**
   - URL: `?s=user&ss=goals&p=goal_detail`
   - Icon: `ri-file-list-3-line`
   - Page: `html/pages/user/goals/goal_detail.php`

3. **Evaluations**
   - URL: `?s=user&ss=goals&p=evaluations`
   - Icon: `ri-star-line`
   - Page: `html/pages/user/goals/evaluations.php`

4. **Matrix Team**
   - URL: `?s=user&ss=goals&p=matrix_team`
   - Icon: `ri-team-line`
   - Page: `html/pages/user/goals/matrix_team.php`

5. **Automation Settings**
   - URL: `?s=user&ss=goals&p=settings`
   - Icon: `ri-settings-3-line`
   - Page: `html/pages/user/goals/settings.php`

### Admin Menu Section

**Location**: After "Operational Work Admin" section
**Icon**: `ri-target-2-line`
**Menu Label**: "Goals & Performance Admin"
**Access**: Requires `$isAdmin || $isValidAdmin`

#### Sub-menu Items:

1. **Dashboard**
   - URL: `?s=admin&ss=goals&p=dashboard`
   - Icon: `ri-dashboard-2-line`
   - Page: `html/pages/admin/goals/dashboard.php`

2. **Goal Library**
   - URL: `?s=admin&ss=goals&p=library`
   - Icon: `ri-book-open-line`
   - Page: `html/pages/admin/goals/library.php`

3. **Cascade Management**
   - URL: `?s=admin&ss=goals&p=cascade`
   - Icon: `ri-flow-chart-line`
   - Page: `html/pages/admin/goals/cascade.php`

4. **Evaluation Config**
   - URL: `?s=admin&ss=goals&p=evaluation_config`
   - Icon: `ri-settings-4-line`
   - Page: `html/pages/admin/goals/evaluation_config.php`

5. **Reports & Analytics**
   - URL: `?s=admin&ss=goals&p=reports`
   - Icon: `ri-bar-chart-box-line`
   - Page: `html/pages/admin/goals/reports.php`

6. **Strategy Map**
   - URL: `?s=admin&ss=goals&p=strategy_map`
   - Icon: `ri-map-2-line`
   - Page: `html/pages/admin/goals/strategy_map.php`

7. **AHP Interface**
   - URL: `?s=admin&ss=goals&p=ahp_interface`
   - Icon: `ri-node-tree`
   - Page: `html/pages/admin/goals/ahp_interface.php`

## Integration with Job Roles & Organization Structure

### Integration Class Created

**File**: `php/classes/goalintegration.php`

This class provides integration between Goals module and:
- **Job Roles**: Maps job titles/categories to functional domains, job bands to competency levels
- **Organization Charts**: Uses org chart positions for cascade target selection
- **Organization Structure**: Integrates with entity hierarchy for cascading

### Key Integration Methods

1. **`getFunctionalDomainFromJobRole($jobTitleID, $DBConn)`**
   - Maps job titles/categories to functional domains (Sales, IT, HR, Finance, etc.)
   - Used for template suggestions

2. **`getCompetencyLevelFromJobRole($jobBandID, $DBConn)`**
   - Maps job bands to competency levels (Junior, Senior, Principal, Executive)
   - Used for template filtering

3. **`getOrgChartForCascading($entityID, $DBConn)`**
   - Retrieves org chart positions for cascade target selection
   - Used in cascade management interface

4. **`getCascadeTargetsFromOrgChart($entityID, $positionID, $DBConn)`**
   - Gets employees by org chart position
   - Used for position-based goal cascading

5. **`suggestTemplatesWithJobRole($userID, $DBConn)`**
   - Enhanced template suggestions using job role information
   - Automatically filters by functional domain and competency level

### Enhanced Template Suggestions

The `GoalLibrary::suggestTemplates()` method has been enhanced to use job role integration:

```php
// Now automatically uses job roles for better suggestions
$templates = GoalLibrary::suggestTemplates($userID, $context, $DBConn);
```

This enhancement:
- Filters templates by functional domain based on job category
- Filters templates by competency level based on job band
- Sorts by relevance (usage count + match score)

## Files Modified

1. **`html/includes/nav/side_nav.php`**
   - Added user menu section for Goals & Performance
   - Added admin menu section for Goals & Performance Admin
   - All menu items include active state detection

2. **`php/classes/goallibrary.php`**
   - Enhanced `suggestTemplates()` to use job role integration
   - Now calls `GoalIntegration` methods for better filtering

3. **`php/classes/goalintegration.php`** (NEW)
   - Complete integration class for job roles and org structure
   - Provides all integration methods

4. **`docs/GOALS_MODULE_INTEGRATION.md`** (NEW)
   - Comprehensive integration documentation
   - Usage examples and best practices

## Routing

All Goals module pages follow the standard TIJA routing pattern:

- **User Pages**: `pages/user/goals/{page}.php`
- **Admin Pages**: `pages/admin/goals/{page}.php`

**URL Format**: `?s={section}&ss=goals&p={page}`

Where:
- `s` = `user` or `admin`
- `ss` = `goals`
- `p` = page name (dashboard, library, cascade, etc.)

## Active State Detection

All menu items include active state detection:

```php
<?= (isset($p) && $p == 'dashboard' && isset($ss) && $ss == 'goals') ? 'active' : '' ?>
```

This automatically highlights the current page in the menu.

## Access Control

- **User Menu**: Available to all logged-in users
- **Admin Menu**: Requires `$isAdmin || $isValidAdmin`

## Testing

To test the menu integration:

1. **User Menu**:
   - Log in as a regular user
   - Navigate to "Goals & Performance" in the sidebar
   - Verify all menu items are visible and functional

2. **Admin Menu**:
   - Log in as an administrator
   - Navigate to "Goals & Performance Admin" in the sidebar
   - Verify all menu items are visible and functional

3. **Integration**:
   - Test template suggestions with different job roles
   - Test cascade target selection using org chart positions
   - Verify job role mappings work correctly

## Next Steps

1. **Customize Mappings**: Adjust functional domain and competency level mappings in `GoalIntegration` class
2. **Add More Integrations**: Extend integration with other TIJA modules as needed
3. **Performance**: Optimize queries for large organizations
4. **UI Enhancements**: Add visual indicators for job role-based suggestions

