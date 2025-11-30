# Operational Work Menu Structure

## Overview
This document outlines the menu structure for the Operational Work Management module in TIJA.

## User Menu Section

**Location**: Main sidebar, after "Time & Attendance"

**Menu Item**: "Operational Work" (icon: `ri-repeat-line`)

### Sub-menu Items:

1. **Dashboard**
   - URL: `?s=user&ss=operational&p=dashboard`
   - Icon: `ri-dashboard-line`
   - Page: `html/pages/user/operational/dashboard.php` ✅ (Created)

2. **My Tasks**
   - URL: `?s=user&ss=operational&p=tasks`
   - Icon: `ri-task-line`
   - Page: `html/pages/user/operational/tasks/list.php` ⏳ (To be created)

3. **Templates**
   - URL: `?s=user&ss=operational&p=templates`
   - Icon: `ri-file-copy-line`
   - Page: `html/pages/user/operational/templates/list.php` ⏳ (To be created)

4. **Operational Projects**
   - URL: `?s=user&ss=operational&p=projects`
   - Icon: `ri-folder-line`
   - Page: `html/pages/user/operational/projects/list.php` ⏳ (To be created)

5. **Capacity Planning**
   - URL: `?s=user&ss=operational&p=capacity`
   - Icon: `ri-bar-chart-box-line`
   - Page: `html/pages/user/operational/capacity/dashboard.php` ⏳ (To be created)

6. **Reports Section**:
   - **Operational Health**
     - URL: `?s=user&ss=operational&p=reports_health`
     - Icon: `ri-heart-pulse-line`
     - Page: `html/pages/user/operational/reports/health.php` ⏳ (To be created)

   - **Executive Dashboard**
     - URL: `?s=user&ss=operational&p=reports_executive`
     - Icon: `ri-line-chart-line`
     - Page: `html/pages/user/operational/reports/executive.php` ⏳ (To be created)

## Admin Menu Section

**Location**: Main sidebar, after "Leave Administration"

**Menu Item**: "Operational Work Admin" (icon: `ri-settings-4-line`)

**Access**: Requires `$isAdmin || $isValidAdmin`

### Sub-menu Items:

#### Overview
1. **Dashboard**
   - URL: `?s=admin&ss=operational&p=dashboard`
   - Icon: `ri-dashboard-2-line`
   - Page: `html/pages/admin/operational/dashboard.php` ✅ (Created)

#### Process Management
2. **Processes & Activities** (Sub-menu)
   - **Processes**
     - URL: `?s=admin&ss=operational&p=processes`
     - Icon: `ri-flow-chart-line`
     - Page: `html/pages/admin/operational/processes/list.php` ⏳ (To be created)

   - **Activities**
     - URL: `?s=admin&ss=operational&p=activities`
     - Icon: `ri-list-check`
     - Page: `html/pages/admin/operational/activities/list.php` ⏳ (To be created)

   - **Tasks**
     - URL: `?s=admin&ss=operational&p=tasks`
     - Icon: `ri-task-line`
     - Page: `html/pages/admin/operational/tasks/list.php` ⏳ (To be created)

#### Workflow Management
3. **Workflows**
   - URL: `?s=admin&ss=operational&p=workflows`
   - Icon: `ri-flow-chart`
   - Page: `html/pages/admin/operational/workflows/list.php` ⏳ (To be created)

#### SOP Management
4. **Standard Operating Procedures**
   - URL: `?s=admin&ss=operational&p=sops`
   - Icon: `ri-file-text-line`
   - Page: `html/pages/admin/operational/sops/list.php` ⏳ (To be created)

#### Template Management
5. **Task Templates**
   - URL: `?s=admin&ss=operational&p=templates`
   - Icon: `ri-file-copy-line`
   - Page: `html/pages/admin/operational/templates/list.php` ⏳ (To be created)

#### Process Optimization
6. **Process Modeling** (Sub-menu)
   - **Process Modeler**
     - URL: `?s=admin&ss=operational&p=processes_model`
     - Icon: `ri-node-tree`
     - Page: `html/pages/admin/operational/processes/model.php` ⏳ (To be created)

   - **Simulation**
     - URL: `?s=admin&ss=operational&p=processes_simulate`
     - Icon: `ri-play-circle-line`
     - Page: `html/pages/admin/operational/processes/simulate.php` ⏳ (To be created)

   - **Optimization**
     - URL: `?s=admin&ss=operational&p=processes_optimize`
     - Icon: `ri-lightbulb-line`
     - Page: `html/pages/admin/operational/processes/optimize.php` ⏳ (To be created)

#### Configuration
7. **Task Assignments**
   - URL: `?s=admin&ss=operational&p=assignments`
   - Icon: `ri-user-settings-line`
   - Page: `html/pages/admin/operational/assignments/manage.php` ⏳ (To be created)

8. **Function Heads**
   - URL: `?s=admin&ss=operational&p=function_heads`
   - Icon: `ri-team-line`
   - Page: `html/pages/admin/operational/function_heads/list.php` ⏳ (To be created)

## URL Structure

All operational work pages follow this pattern:
- **User pages**: `?s=user&ss=operational&p={page_name}`
- **Admin pages**: `?s=admin&ss=operational&p={page_name}`

## File Structure

### User Pages
```
html/pages/user/operational/
├── dashboard.php ✅
├── tasks/
│   ├── list.php ⏳
│   └── execute.php ⏳
├── templates/
│   ├── list.php ⏳
│   └── view.php ⏳
├── projects/
│   ├── list.php ⏳
│   └── view.php ⏳
├── capacity/
│   └── dashboard.php ⏳
└── reports/
    ├── health.php ⏳
    └── executive.php ⏳
```

### Admin Pages
```
html/pages/admin/operational/
├── dashboard.php ✅
├── processes/
│   ├── list.php ⏳
│   ├── create.php ⏳
│   ├── view.php ⏳
│   ├── model.php ⏳
│   ├── simulate.php ⏳
│   └── optimize.php ⏳
├── activities/
│   ├── list.php ⏳
│   └── create.php ⏳
├── tasks/
│   ├── list.php ⏳
│   └── create.php ⏳
├── workflows/
│   ├── list.php ⏳
│   ├── create.php ⏳
│   └── view.php ⏳
├── sops/
│   ├── list.php ⏳
│   ├── create.php ⏳
│   └── view.php ⏳
├── templates/
│   ├── list.php ⏳
│   └── create.php ⏳
├── assignments/
│   └── manage.php ⏳
└── function_heads/
    └── list.php ⏳
```

## Status Legend
- ✅ Created
- ⏳ To be created

## Next Steps

1. Create placeholder pages for all menu items
2. Implement routing logic to handle page requests
3. Build out each page with full functionality
4. Add breadcrumb navigation
5. Implement permission checks for each page

