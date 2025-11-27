# Leave Administration Module

## Overview
This directory contains all leave administration pages and functionality. These pages are only accessible to users with admin or HR permissions.

## Structure

### Navigation
The leave system is now separated into two distinct sections in the navigation:

1. **Leave Management** (`s=user&ss=leave`) - User Section
   - Dashboard
   - Apply Leave
   - My Leave Usage
   - Leave Approvals (for managers)
   - Team Calendar

2. **Leave Administration** (`s=admin&ss=leave`) - Admin Section
   - Admin Dashboard
   - Leave Policy Types
   - Accumulation Policies
   - Leave Types
   - Leave Entitlements
   - Leave Periods
   - Holidays
   - Working Weekends
   - Approval Workflows
   - Reports & Analytics
   - Audit Log
   - System Settings

## Access Control

### User Section (Everyone)
All authenticated users can access:
- Apply for leave
- View their leave history
- Check leave balances
- View team calendar

### Manager Section (Managers/Supervisors)
Users with supervisory roles can access:
- Leave approval queue
- Team leave calendar
- Direct reports leave information

### Admin Section (HR/Admin Only)
Only users with `$isAdmin`, `$isValidAdmin`, or `$isHRManager` flags can access:
- All configuration pages
- System-wide reports
- Policy management
- Global settings

## Implementation Notes

### URL Structure
- User pages: `html/pages/user/leave/*.php`
- Admin pages: `html/pages/admin/leave/*.php`

### Access Check Pattern
All admin pages should include this check at the top:

```php
// Check if user is logged in
if(!$isValidUser) {
    Alert::info("You need to be logged in to access this page", true);
    include "includes/core/log_in_script.php";
    return;
}

// Check admin permissions
if (!$isAdmin && !$isValidAdmin && !$isHRManager) {
    Alert::error("Access denied. Leave Administrator privileges required.", true);
    return;
}
```

### Moving Existing Pages
The following pages have been reorganized:

**From:** `html/pages/user/leave/admin/`
**To:** `html/pages/admin/leave/`

Updated pages:
- `home.php` → `dashboard.php`
- `leave_policy_management.php`
- `accumulation_policies.php`

## Benefits of Separation

1. **Clearer Access Control**: Easy to implement role-based permissions
2. **Better Organization**: User and admin functionalities are logically separated
3. **Improved Security**: Admins can be given different access levels
4. **Easier Maintenance**: Clear separation of concerns
5. **Scalability**: Easy to add new admin features without cluttering user interface

## Future Enhancements

- [ ] Implement granular role-based permissions (e.g., HR Manager, HR Admin, Super Admin)
- [ ] Add audit logging for all admin actions
- [ ] Create admin-specific dashboards with KPIs
- [ ] Implement bulk operations for admin tasks
- [ ] Add export/import functionality for policies

## Related Files

### Navigation
- `html/includes/nav/side_nav.php` - Main navigation configuration

### Classes
- `php/classes/leave.php` - Leave management class
- `php/classes/data.php` - Data access class

### Scripts
- `php/scripts/leave/` - API endpoints for leave operations

## Contact
For questions or issues with the leave administration module, contact the development team.

