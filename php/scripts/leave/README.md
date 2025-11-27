# Leave Module Backend Scripts

**Reorganized**: November 6, 2025
**Total Scripts**: 47 organized into 5 functional categories

---

## 📂 Directory Structure

```
php/scripts/leave/
├── config/              (8 scripts) - System configuration & settings
├── applications/        (8 scripts) - Leave application processing
├── workflows/          (17 scripts) - Approval workflows & templates
├── holidays/            (6 scripts) - Holiday management
├── utilities/           (4 scripts) - Helper & utility functions
└── README.md            (this file)
```

---

## 🎯 Quick Reference

### Need to...

**Configure leave policies?**
→ `config/manage_leave_policy.php`

**Submit leave application?**
→ `applications/submit_leave_application.php`

**Approve/reject leave?**
→ `applications/process_leave_approval_action.php`

**Manage holidays?**
→ `holidays/manage_holidays.php`

**Setup approval workflows?**
→ `workflows/save_template.php`

**Get team calendar data?**
→ `utilities/team_calendar_data.php`

**Export reports?**
→ `utilities/export_statistics.php`

---

## 📋 Script Categories

### ⚙️ CONFIG (8 scripts)
Configuration and system settings management

1. `manage_leave_policy.php` - Comprehensive policy management
2. `manage_leave_type.php` - Leave type CRUD (consolidated)
3. `manage_leave_status.php` - Status management
4. `manage_Leave_entitlement.php` - Entitlement tiers
5. `manage_leave_periods.php` - Fiscal periods
6. `manage_accumulation_policy.php` - Accrual rules
7. `manage_working_weekend.php` - Working weekends
8. `bradford_factor_threshold.php` - Bradford factor config

### 📝 APPLICATIONS (8 scripts)
Leave application submission and processing

1. `submit_leave_application.php` - Submit application
2. `process_leave_approval_action.php` - Process approval
3. `cancel_leave_application.php` - Cancel application
4. `get_leave_details.php` - Get application details
5. `download_leave_application.php` - Download as PDF
6. `download_document.php` - Download supporting docs
7. `apply_leave.php` ⚠️ (legacy - review)
8. `approve_leave.php` ⚠️ (legacy - review)

### 🔄 WORKFLOWS (17 scripts)
Approval workflow and template management

**Workflow Instances (9):**
1. `get_approval_workflow.php` - Get workflow details
2. `get_approvals.php` - Get approval list
3. `get_approval_details.php` - Approval details
4. `submit_approval_decision.php` - Submit decision
5. `create_workflow_from_template.php` - Create from template
6. `clone_workflow.php` - Clone workflow
7. `delete_workflow.php` - Delete workflow
8. `toggle_workflow_status.php` - Enable/disable
9. `set_default_workflow.php` - Set default

**Templates (8):**
1. `save_template.php` - Save template
2. `get_all_templates.php` - List templates
3. `get_template_details.php` - Template summary
4. `get_template_full.php` - Full template
5. `get_template_steps.php` - Template steps
6. `clone_template.php` - Clone template
7. `delete_template.php` - Delete template
8. `toggle_template_visibility.php` - Show/hide

### 🌍 HOLIDAYS (6 scripts)
Holiday and calendar management

1. `manage_holidays.php` - Holiday CRUD
2. `get_holidays.php` - Get holiday list
3. `get_holiday_applicability.php` - Check applicability
4. `check_employee_holidays.php` - Employee holidays
5. `delete_holiday.php` - Delete holiday
6. `generate_annual_holidays.php` - Generate recurring

### 🛠️ UTILITIES (4 scripts)
Helper functions and utilities

1. `team_calendar_data.php` - Calendar data
2. `export_statistics.php` - Export stats
3. `leave_calendar_config.php` - Calendar config
4. `get_entity_workflow_status.php` - Workflow status

---

## 🔗 Common Integration Patterns

### Employee Leave Application
```php
// Submit application
POST php/scripts/leave/applications/submit_leave_application.php

// Get application details
GET php/scripts/leave/applications/get_leave_details.php?applicationID=123

// Cancel application
POST php/scripts/leave/applications/cancel_leave_application.php
```

### Manager Approval
```php
// Get pending approvals
GET php/scripts/leave/workflows/get_approvals.php?approverID=456&status=pending

// Get approval workflow
GET php/scripts/leave/workflows/get_approval_workflow.php?applicationID=123

// Submit decision
POST php/scripts/leave/workflows/submit_approval_decision.php
```

### HR Configuration
```php
// Create leave type
POST php/scripts/leave/config/manage_leave_type.php
  action=create&leaveTypeName=Annual Leave&...

// Create policy
POST php/scripts/leave/config/manage_leave_policy.php
  action=create&policyName=Annual Leave Policy&...

// Manage holidays
POST php/scripts/leave/holidays/manage_holidays.php
  action=create&holidayName=New Year&...
```

---

## ⚠️ Important Notes

### Path Changes
All scripts have been reorganized. Update references:

```
OLD: php/scripts/leave/manage_holidays.php
NEW: php/scripts/leave/holidays/manage_holidays.php
```

### Legacy Scripts
Review and consolidate:
- `applications/apply_leave.php` - May be duplicate of `submit_leave_application.php`
- `applications/approve_leave.php` - May be duplicate of `process_leave_approval_action.php`

### Security
- All scripts require valid user session
- Admin operations require admin privileges
- Transactions used for data modifications
- Input sanitization applied

---

## 📚 Full Documentation

For complete API documentation, see:
`LEAVE_BACKEND_SCRIPTS_REFERENCE.md`

For reorganization details, see:
`LEAVE_MODULE_REORGANIZATION.md`

---

_Organized for better maintainability and developer experience_
