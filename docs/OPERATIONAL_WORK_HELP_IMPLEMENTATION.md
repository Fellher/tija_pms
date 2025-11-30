# Operational Work Help Documentation Implementation

## Overview

Comprehensive help documentation has been added to all operational work pages using multiple UI patterns:

1. **Help Icons** (ℹ️) - Quick tooltips on hover
2. **Help Popovers** - Detailed explanations on hover/click
3. **Help Hotspots** - Contextual help modals triggered by icons
4. **Inline Help Text** - Descriptive text below page titles
5. **Help Modal** - Full documentation modal for complex topics

## Implementation Details

### Help Component

**File**: `html/includes/components/operational_help.php`

This component provides:
- Helper functions for rendering help elements
- Help content dictionary with all help topics
- Bootstrap tooltip and popover initialization
- Help modal for detailed documentation

### Help Functions

1. **`renderHelpIcon($helpText, $placement, $size)`**
   - Renders a question mark icon with tooltip
   - Used for quick explanations

2. **`renderHelpPopover($title, $content, $placement, $trigger)`**
   - Renders a question mark icon with popover
   - Used for detailed explanations

3. **`renderHelpHotspot($helpKey, $position)`**
   - Renders a contextual hotspot icon
   - Opens help modal with detailed content

4. **`getHelpContent($key)`**
   - Returns help content by key
   - Used by help modal

## Pages with Help Documentation

### Admin Pages

#### 1. Admin Dashboard (`html/pages/admin/operational/dashboard.php`)
- Help on dashboard overview
- Help on each statistic card (Processes, Workflows, Templates, Overdue Tasks)
- Help hotspot on Quick Actions card
- Inline help text explaining dashboard purpose

#### 2. Processes Management (`html/pages/admin/operational/processes.php`)
- Help on APQC taxonomy
- Help on functional area filter
- Help on custom vs standard processes
- Inline help text explaining process management

#### 3. Templates Management (`html/pages/admin/operational/templates.php`)
- Help on template overview
- Help on processing mode filter (Cron, Manual, Both)
- Help on processing mode badges in table
- Inline help text explaining templates

#### 4. Workflows Management (`html/pages/admin/operational/workflows.php`)
- Help on workflow overview
- Help on workflow steps and transitions
- Inline help text explaining workflows

#### 5. SOPs Management (`html/pages/admin/operational/sops.php`)
- Help on SOP versioning
- Help on SOP approval process
- Inline help text explaining SOPs

### User Pages

#### 1. User Dashboard (`html/pages/user/operational/dashboard.php`)
- Help on dashboard overview
- Help on BAU hours metric
- Help on available capacity metric
- Help on upcoming tasks section
- Inline help text explaining user dashboard

#### 2. My Tasks (`html/pages/user/operational/tasks.php`)
- Help on task status filter
- Help on task statuses (Pending, In Progress, Completed, Overdue)
- Help on task execution

#### 3. Capacity Planning (`html/pages/user/operational/capacity.php`)
- Help on capacity waterline concept
- Help on BAU hours (operational tax)
- Help on FTE calculation
- Inline help text explaining capacity planning

#### 4. Template View (`html/pages/user/operational/templates/view.php`)
- Help component included (ready for help elements)

#### 5. Project View (`html/pages/user/operational/projects/view.php`)
- Help component included (ready for help elements)

## Help Content Topics

The help component includes help content for:

1. **Admin Dashboard**
   - Overview, Processes, Workflows, Templates, Overdue Tasks

2. **User Dashboard**
   - Overview, Upcoming Tasks, Capacity

3. **Processes**
   - APQC Taxonomy, Functional Area, Custom vs Standard

4. **Activities**
   - Activities within Processes

5. **Workflows**
   - Workflow Steps, Workflow Transitions

6. **SOPs**
   - SOP Versioning, SOP Approval Process

7. **Templates**
   - Task Frequency, Processing Mode, Assignment Rules, Checklists

8. **Tasks**
   - Task Status, Task Execution, Task Dependencies

9. **Capacity Planning**
   - Capacity Waterline, FTE, Operational Tax

10. **Projects**
    - Operational Projects, Project Utilization

11. **Reports**
    - Operational Health Metrics, Executive Dashboard

12. **Function Heads**
    - Function Head Assignment

13. **Notifications**
    - Pending Task Notifications

## Usage Examples

### Adding Help Icon
```php
<?php echo renderHelpIcon('This field is required for all templates.', 'top'); ?>
```

### Adding Help Popover
```php
<?php echo renderHelpPopover('Processing Mode', 'Cron: Tasks automatically created. Manual: Tasks created when users trigger them. Both: Flexible option.', 'top'); ?>
```

### Adding Help Hotspot
```php
<?php echo renderHelpHotspot('admin_dashboard_overview', 'top-right'); ?>
```

### Adding Inline Help Text
```php
<p class="text-muted mb-0 help-text">
    Overview of operational work in your functional area.
    <?php echo renderHelpPopover('Dashboard', 'Detailed explanation...', 'right'); ?>
</p>
```

## User Documentation

A comprehensive user documentation file has been created:

**File**: `docs/OPERATIONAL_WORK_USER_DOCUMENTATION.md`

This document includes:
- Overview and key benefits
- User interface guide
- Admin features documentation
- User features documentation
- Key concepts explanation
- Getting started guide
- Frequently asked questions

## Best Practices

1. **Use Help Icons** for quick, one-line explanations
2. **Use Help Popovers** for detailed explanations (2-3 sentences)
3. **Use Help Hotspots** for complex topics that need full modal explanation
4. **Use Inline Help Text** for page-level context
5. **Keep Help Content Concise** - Users should be able to understand quickly
6. **Place Help Elements Near Relevant Content** - Don't make users search for help
7. **Use Consistent Placement** - Help icons after labels, hotspots in top-right of cards

## Future Enhancements

1. **Help Search** - Allow users to search help content
2. **Help Videos** - Embed video tutorials for complex features
3. **Contextual Help Based on User Role** - Show role-specific help
4. **Help Analytics** - Track which help topics are accessed most
5. **Interactive Tutorials** - Step-by-step guided tours for new users

## Testing

To test help documentation:

1. **Tooltips**: Hover over help icons (ℹ️) - should show tooltip
2. **Popovers**: Hover/click on help icons with popovers - should show detailed content
3. **Hotspots**: Click on hotspot icons - should open help modal
4. **Help Modal**: Click on elements with `data-help-key` - should open modal with content
5. **Responsive**: Test on mobile devices - help should be accessible

## Maintenance

When adding new features:

1. **Add Help Content** to `getHelpContent()` function in `operational_help.php`
2. **Add Help Elements** to relevant pages using helper functions
3. **Update User Documentation** in `OPERATIONAL_WORK_USER_DOCUMENTATION.md`
4. **Test Help Elements** to ensure they work correctly

---

*Last Updated: <?php echo date('F Y'); ?>*

