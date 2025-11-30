<!-- 34ec81f7-c8ff-4dd3-abff-38b856ccf890 7e3abd67-156d-485a-862e-b0486e5c4aa2 -->
# Non-Project Work Integration Plan for TIJA Platform

## Overview

This plan integrates Business-As-Usual (BAU) work management into TIJA, transforming operational tasks from "invisible work" into a managed portfolio. The implementation follows the APQC taxonomy framework and adopts a hybrid architecture with separate BAU module but unified reporting.

## Architecture Principles

### 1. Hybrid Work Model

- **Separate BAU Module**: Dedicated section for operational work management
- **Unified Data Model**: BAU tasks inherit from base task structure (similar to ServiceNow inheritance model)
- **Unified Reporting**: Combined analytics showing Project + BAU capacity utilization

### 2. Data Model Strategy

- Extend existing `tija_tasks_time_logs` with BAU-specific fields
- Create new tables for BAU taxonomy, templates, and operational projects
- Leverage existing `taskType` enum (add 'operational' type)

## Implementation Phases

### Phase 1: Core Infrastructure & Database Schema

#### 1.1 APQC Taxonomy Tables

**File**: `database/migrations/create_bau_taxonomy_tables.sql`

Create tables to support APQC Process Classification Framework:

- `tija_bau_categories` - Top-level domains (e.g., 7.0 Develop and Manage Human Capital)
- `tija_bau_process_groups` - Functional areas within categories
- `tija_bau_processes` - Specific workflows (e.g., 7.3.1 Manage Payroll)
- `tija_bau_activities` - Actionable units of work

**Key Fields**:

- `processID` (APQC format: 7.3.1)
- `processName`, `processDescription`
- `categoryID`, `processGroupID`
- Hierarchy relationships
- `createdByID`, `functionalAreaOwnerID` - Function head assignment
- `isCustom` ENUM('Y','N') - Custom vs standard APQC process

#### 1.2 Operational Task Templates

**File**: `database/migrations/create_operational_task_templates.sql`

Create `tija_operational_task_templates`:

- `templateID`, `templateCode`, `templateName`
- `processID` (FK to APQC taxonomy)
- `functionalArea` ENUM('Finance','HR','IT','Sales','Marketing','Legal','Facilities')
- `frequencyType` ENUM('daily','weekly','monthly','quarterly','annually','custom')
- `frequencyInterval` INT
- `frequencyDayOfWeek` INT (1-7)
- `frequencyDayOfMonth` INT (1-31)
- `frequencyMonthOfYear` INT (1-12)
- `estimatedDuration` DECIMAL(10,2) - hours
- `sopDocumentURL` TEXT - Link to SOP/knowledge base
- `assignmentRule` JSON - Auto-assignment logic
- `requiresApproval` ENUM('Y','N')
- `approverRoleID` INT
- `isActive` ENUM('Y','N')

#### 1.3 Operational Task Instances

**File**: `database/migrations/create_operational_tasks_table.sql`

Create `tija_operational_tasks`:

- `operationalTaskID`, `templateID` (FK)
- `instanceNumber` INT - Cycle number
- `dueDate` DATE, `startDate` DATE, `completedDate` DATETIME
- `status` ENUM('pending','in_progress','completed','overdue','cancelled')
- `assigneeID` INT (FK to people)
- `processID` (FK to APQC)
- `actualDuration` DECIMAL(10,2)
- `nextInstanceDueDate` DATE - For regeneration
- `parentInstanceID` INT - Links to previous cycle

#### 1.4 Operational Task Checklists

**File**: `database/migrations/create_operational_task_checklists.sql`

Create `tija_operational_task_checklists`:

- `checklistItemID`
- `templateID` or `operationalTaskID` (template-level or instance-level)
- `itemOrder` INT
- `itemDescription` TEXT
- `isMandatory` ENUM('Y','N')
- `isCompleted` ENUM('Y','N')
- `completedByID` INT
- `completedDate` DATETIME

#### 1.5 Operational Projects (BAU Buckets)

**File**: `database/migrations/create_operational_projects_table.sql`

Create `tija_operational_projects`:

- `operationalProjectID`
- `projectCode`, `projectName` (e.g., "FY25 HR Operations")
- `functionalArea` ENUM
- `fiscalYear` INT
- `allocatedHours` DECIMAL(10,2) - Planned BAU hours
- `actualHours` DECIMAL(10,2) - Logged hours
- `fteRequirement` DECIMAL(5,2) - Calculated FTE
- Links to existing `tija_projects` via `projectID` (soft booking)

#### 1.6 Extend Time Logs Table

**File**: `database/migrations/extend_time_logs_for_bau.sql`

Alter `tija_tasks_time_logs`:

- Add `operationalTaskID` INT (FK to `tija_operational_tasks`)
- Add `operationalProjectID` INT (FK to `tija_operational_projects`)
- Extend `taskType` enum to include 'operational'
- Add `processID` VARCHAR(20) - APQC process identifier

#### 1.7 Task Dependencies

**File**: `database/migrations/create_operational_task_dependencies.sql`

Create `tija_operational_task_dependencies`:

- `dependencyID`
- `predecessorTaskID` (template or instance)
- `successorTaskID`
- `dependencyType` ENUM('finish_to_start','start_to_start','finish_to_finish')
- `lagDays` INT - Delay in days

### Phase 2: Backend Classes & Services

#### 2.1 BAU Taxonomy Class

**File**: `php/classes/bautaxonomy.php`

Methods:

- `getCategories()`, `getProcessGroups($categoryID)`, `getProcesses($groupID)`
- `getProcessByID($processID)` - Returns full APQC hierarchy
- `searchProcesses($query)` - Search across taxonomy

#### 2.2 Operational Task Template Class

**File**: `php/classes/operationaltasktemplate.php`

Methods:

- `createTemplate($data)` - Create new template with SOP, checklist, schedule
- `updateTemplate($templateID, $data)`
- `getTemplate($templateID)`
- `listTemplates($filters)` - Filter by functional area, frequency, etc.
- `activateTemplate($templateID)`, `deactivateTemplate($templateID)`

#### 2.3 Operational Task Instance Class

**File**: `php/classes/operationaltask.php`

Methods:

- `instantiateFromTemplate($templateID, $dueDate)` - Create new instance
- `getInstance($operationalTaskID)`
- `updateStatus($operationalTaskID, $status)`
- `completeTask($operationalTaskID, $actualDuration, $checklistData)`
- `regenerateNextInstance($operationalTaskID)` - Calculate and create next cycle
- `getOverdueTasks($filters)`
- `getUpcomingTasks($daysAhead)`

#### 2.4 Recurring Task Scheduler Service

**File**: `php/classes/operationaltaskscheduler.php`

Methods:

- `processScheduledTasks()` - Cron job entry point
- `evaluateTemplates()` - Check which templates need instantiation
- `createInstances($templateID, $dueDate)` - Batch create
- `handleDependencies($operationalTaskID)` - Check and update dependent tasks
- `sendNotifications($operationalTaskID, $eventType)`

**Cron Job**: `php/scripts/cron/process_operational_tasks.php` (runs hourly)

#### 2.5 Capacity Planning Class

**File**: `php/classes/capacityplanning.php`

Methods:

- `calculateFTE($annualHours)` - FTE = annualHours / 2080
- `calculateOperationalTax($employeeID, $dateRange)` - Total BAU hours
- `getAvailableCapacity($employeeID, $dateRange)` - 2080 - PTO - BAU - Projects
- `createOperationalProject($data)` - Create BAU bucket
- `allocateToOperationalProject($operationalProjectID, $employeeID, $hours)`
- `getCapacityWaterline($employeeID, $dateRange)` - Returns layered breakdown:
- Layer 1: Non-Working Time (PTO, Holidays)
- Layer 2: BAU (Operational tasks)
- Layer 3: Projects
- Available capacity

#### 2.6 Extend TimeAttendance Class

**File**: `php/classes/timeattendance.php` (extend existing)

Add methods:

- `logOperationalTime($data)` - Log time against operational task
- `getOperationalTimeLogs($filters)` - Query BAU time logs
- `getBAUHoursByEmployee($employeeID, $dateRange)`
- `getBAUHoursByProcess($processID, $dateRange)`

### Phase 3: Functional Area Catalog Implementation

#### 3.1 Finance & Accounting Templates

**File**: `database/migrations/seed_finance_operational_templates.sql`

Seed templates based on research document:

- Month-End Close tasks (Cash Reconciliation, AP Ledger Review, etc.)
- Accounts Payable processing
- Fixed Asset Depreciation
- Accruals & Prepayments
- Payroll Reconciliation
- Financial Reporting

Each template includes:

- APQC Process ID (e.g., 8.6.1 for Cash Reconciliation)
- Frequency (monthly)
- Estimated duration
- Checklist items
- Dependencies

#### 3.2 HR Templates

**File**: `database/migrations/seed_hr_operational_templates.sql`

Seed templates:

- Payroll Administration (Time & Attendance Review, Gross-to-Net Calculation, etc.)
- Employee Onboarding (event-driven, 90-day process)
- Benefits Administration
- Compliance Reporting

#### 3.3 IT Templates

**File**: `database/migrations/seed_it_operational_templates.sql`

Seed templates:

- Server Patch Management (Patch Assessment, Staging Deployment, Production Rollout)
- Backup Verification
- Security Audits
- Help Desk Operations (bucket tracking for high-volume tasks)

#### 3.4 Sales & Marketing Templates

**File**: `database/migrations/seed_sales_marketing_templates.sql`

Seed templates:

- SDR Daily Routine (Prospecting, Outreach Blocks, CRM Hygiene)
- Marketing Content Operations (Social Calendar Prep, Approval Cycle, Performance Review)

#### 3.5 Legal & Compliance Templates

**File**: `database/migrations/seed_legal_operational_templates.sql`

Seed templates:

- Annual Report Filing
- Board Meeting Preparation
- Contract Reviews
- Regulatory Compliance (GDPR/CCPA Audits, License Renewals)

#### 3.6 Facilities Templates

**File**: `database/migrations/seed_facilities_operational_templates.sql`

Seed templates:

- HVAC Maintenance (Filter Replacement, Chiller Inspection)
- Life Safety (Fire Extinguisher Inspection, Emergency Lighting Test)
- General Maintenance (Deep Clean, Generator Load Test)

### Phase 4: Frontend Pages & UI

#### 4.1 BAU Dashboard

**File**: `html/pages/user/operational/dashboard.php`

Features:

- Operational Health Metrics (Volume, Cycle Time, Backlog, SLA Compliance)
- Upcoming Tasks Widget
- Overdue Tasks Alert
- Capacity Utilization Chart (Waterline visualization)
- Quick Actions: Create Template, Log Time, View Reports

#### 4.2 Template Management

**File**: `html/pages/user/operational/templates/list.php` - List all templates
**File**: `html/pages/user/operational/templates/create.php` - Create/edit template
**File**: `html/pages/user/operational/templates/view.php` - Template details with SOP, checklist, schedule

Template creation form includes:

- APQC Process selection (dropdown with hierarchy)
- Functional area
- Frequency configuration (recurrence rules)
- Estimated duration
- SOP document upload/link
- Checklist builder
- Assignment rules
- Approval workflow

#### 4.3 Operational Tasks View

**File**: `html/pages/user/operational/tasks/list.php`

Features:

- Kanban board (Pending, In Progress, Completed, Overdue)
- Calendar view
- List view with filters (Functional Area, Process, Status, Assignee, Date Range)
- Bulk actions (assign, complete, reschedule)

#### 4.4 Task Execution Interface

**File**: `html/pages/user/operational/tasks/execute.php`

Features:

- Task details with SOP link
- Embedded checklist (mandatory items must be completed)
- Time logging widget
- Dependency status (shows blockers)
- Approval workflow (if required)
- Comments/notes
- Attachments

#### 4.5 Operational Projects (BAU Buckets)

**File**: `html/pages/user/operational/projects/list.php` - List operational projects
**File**: `html/pages/user/operational/projects/create.php` - Create FY operational project
**File**: `html/pages/user/operational/projects/view.php` - Project details with:

- Allocated vs Actual hours
- FTE calculation
- Resource allocations
- Time log summary

#### 4.6 Capacity Planning Dashboard

**File**: `html/pages/user/operational/capacity/dashboard.php`

Features:

- Individual employee capacity waterline chart
- Team capacity heatmap
- FTE calculations by functional area
- Operational tax visualization
- Available capacity for projects

### Phase 5: Backend Scripts & APIs

#### 5.1 Template Management APIs

**File**: `php/scripts/operational/templates/manage_template.php`

- Create, update, delete, activate/deactivate templates

**File**: `php/scripts/operational/templates/get_templates.php`

- List templates with filters

#### 5.2 Task Instance APIs

**File**: `php/scripts/operational/tasks/manage_task.php`

- Create instance, update status, complete task

**File**: `php/scripts/operational/tasks/get_tasks.php`

- List tasks with filters, get task details

**File**: `php/scripts/operational/tasks/regenerate_instance.php`

- Manually trigger next instance creation

#### 5.3 Time Logging API

**File**: `php/scripts/operational/tasks/log_time.php`

- Log time against operational task (extends existing time logging)

#### 5.4 Capacity Planning APIs

**File**: `php/scripts/operational/capacity/get_capacity.php`

- Get employee/team capacity data

**File**: `php/scripts/operational/capacity/calculate_fte.php`

- Calculate FTE for operational work

### Phase 6: Reporting & Analytics

#### 6.1 Operational Health Dashboard

**File**: `html/pages/user/operational/reports/health.php`

Metrics:

- Task Volume (tasks completed per period)
- Cycle Time (average time to complete recurring cycle)
- Backlog (overdue tasks count)
- Quality/Error Rate (tasks reopened)
- SLA Compliance (% completed on time)

#### 6.2 Executive Single Pane of Glass

**File**: `html/pages/user/operational/reports/executive.php`

Visualizations:

- Investment Mix Chart (Run vs Grow vs Transform)
- Capacity Waterline (stacked: PTO, BAU, Projects, Available)
- FTE by Functional Area
- Operational Efficiency Trends

#### 6.3 Process Analytics

**File**: `html/pages/user/operational/reports/process_analytics.php`

Features:

- Total labor cost by APQC process
- Process efficiency benchmarking
- Time spent by functional area
- Automation opportunity identification

### Phase 7: Integration Points

#### 7.1 Extend Existing Project Views

**Files**:

- `html/pages/user/projects/home.php` (add BAU filter)
- `html/includes/projects/tasks_kanban.php` (show operational tasks option)

Add toggle: "Show Operational Tasks" to existing project views for unified visibility.

#### 7.2 Unified Time Logging

**File**: `html/pages/user/timesheet/log_time.php` (extend existing)

Add:

- Task type selector (Project, Operational, Adhoc, etc.)
- Operational task dropdown (when type = operational)
- Process ID display

#### 7.3 Resource Planning Integration

**File**: `html/includes/scripts/projects/logic/project_plan_logic.php` (extend)

Update `calculateResourceAllocation()` to:

- Subtract operational capacity from available hours
- Show BAU allocation in resource view
- Warn when over-allocating (BAU + Projects > Capacity)

### Phase 8: Automation & Workflows

#### 8.1 Scheduled Task Processor

**File**: `php/scripts/cron/process_operational_tasks.php`

Logic:

1. Query active templates
2. Evaluate recurrence rules (calculate next due date)
3. Create task instances for due templates
4. Handle dependencies (check if predecessors completed)
5. Send notifications (new task assigned, overdue reminders)
6. Regenerate completed tasks (calculate next cycle)

**Cron Schedule**: Run every hour

#### 8.2 Event-Based Triggers

**File**: `php/classes/operationaltriggers.php`

Methods:

- `onEmployeeTerminated($employeeID)` - Trigger access revocation tasks
- `onInvoiceReceived()` - Trigger AP processing tasks
- `onLeaveApplicationSubmitted($leaveID)` - Trigger handover tasks (integrate with existing leave system)

#### 8.3 Notification System

Extend existing notification system:

- New operational task assigned
- Task due soon (configurable days ahead)
- Task overdue
- Dependency blocked
- Approval required
- Task completed (notify approver)

### Phase 9: Data Migration & Seeding

#### 9.1 APQC Taxonomy Data

**File**: `database/migrations/seed_apqc_taxonomy.sql`

Import APQC Process Classification Framework data:

- Categories (12 main categories)
- Process Groups
- Processes (with IDs like 7.3.1, 8.6.1, etc.)

#### 9.2 Default Operational Projects

**File**: `database/migrations/seed_default_operational_projects.sql`

Create FY operational projects for each functional area:

- "FY25 Finance Operations"
- "FY25 HR Operations"
- "FY25 IT Operations"
- etc.

### Phase 10: Testing & Validation

#### 10.1 Unit Tests

- Template creation and scheduling logic
- FTE calculations
- Capacity waterline calculations
- Dependency resolution

#### 10.2 Integration Tests

- Time logging integration
- Project capacity integration
- Notification delivery
- Cron job execution

#### 10.3 User Acceptance Testing

- Template creation workflow
- Task execution with checklists
- Capacity planning accuracy
- Reporting accuracy

## Key Design Decisions

### 1. Inheritance Model

- Operational tasks extend base task structure (similar to `tija_project_tasks`)
- Unified time logging via `tija_tasks_time_logs` with `taskType='operational'`
- Shared resource model (employees work on both projects and BAU)

### 2. Granularity Strategy

- **Granular Tracking**: For compliance-critical or long-duration tasks (>1 hour)
- **Bucket Tracking**: For high-volume, low-duration tasks (help desk, email responses)
- Configurable per template

### 3. Capacity Modeling

- Adopt "Operational Bucket" (Bottom-Up) approach
- Create operational projects per fiscal year
- Allocate resources to buckets
- Track actuals vs forecast

### 4. SOP Integration

- Store SOP URLs in template
- Embed checklists in task instances
- Link to knowledge base (future: integrate with Confluence/SharePoint)

## Files to Create/Modify

### New Database Tables (10 tables)

1. `tija_bau_categories`
2. `tija_bau_process_groups`
3. `tija_bau_processes`
4. `tija_bau_activities`
5. `tija_operational_task_templates`
6. `tija_operational_tasks`
7. `tija_operational_task_checklists`
8. `tija_operational_projects`
9. `tija_operational_task_dependencies`
10. Extend `tija_tasks_time_logs`

### New PHP Classes (6 classes)

1. `php/classes/bautaxonomy.php`
2. `php/classes/operationaltasktemplate.php`
3. `php/classes/operationaltask.php`
4. `php/classes/operationaltaskscheduler.php`
5. `php/classes/capacityplanning.php`
6. `php/classes/operationaltriggers.php`

### New Frontend Pages (12+ pages)

1. `html/pages/user/operational/dashboard.php`
2. `html/pages/user/operational/templates/list.php`
3. `html/pages/user/operational/templates/create.php`
4. `html/pages/user/operational/templates/view.php`
5. `html/pages/user/operational/tasks/list.php`
6. `html/pages/user/operational/tasks/execute.php`
7. `html/pages/user/operational/projects/list.php`
8. `html/pages/user/operational/projects/create.php`
9. `html/pages/user/operational/projects/view.php`
10. `html/pages/user/operational/capacity/dashboard.php`
11. `html/pages/user/operational/reports/health.php`
12. `html/pages/user/operational/reports/executive.php`
13. `html/pages/user/operational/reports/process_analytics.php`

### New Backend Scripts (10+ scripts)

1. `php/scripts/operational/templates/manage_template.php`
2. `php/scripts/operational/templates/get_templates.php`
3. `php/scripts/operational/tasks/manage_task.php`
4. `php/scripts/operational/tasks/get_tasks.php`
5. `php/scripts/operational/tasks/log_time.php`
6. `php/scripts/operational/tasks/regenerate_instance.php`
7. `php/scripts/operational/capacity/get_capacity.php`
8. `php/scripts/operational/capacity/calculate_fte.php`
9. `php/scripts/cron/process_operational_tasks.php`
10. Additional API endpoints as needed

### Modified Files

1. `php/classes/timeattendance.php` - Add operational time logging methods
2. `html/pages/user/projects/home.php` - Add BAU filter option
3. `html/pages/user/timesheet/log_time.php` - Add operational task type
4. `html/includes/scripts/projects/logic/project_plan_logic.php` - Include BAU in capacity

## Implementation Timeline Estimate

- **Phase 1-2** (Core Infrastructure): 3-4 weeks
- **Phase 3** (Functional Catalogs): 2-3 weeks
- **Phase 4** (Frontend): 4-5 weeks
- **Phase 5** (APIs): 2 weeks
- **Phase 6** (Reporting): 2-3 weeks
- **Phase 7-8** (Integration & Automation): 2-3 weeks
- **Phase 9-10** (Data & Testing): 2 weeks

**Total Estimated Duration**: 17-22 weeks (4-5.5 months)

## Success Metrics

1. **Visibility**: 100% of identified BAU tasks tracked in system
2. **Accuracy**: Capacity planning within 5% of actual utilization
3. **Efficiency**: 80% of recurring tasks auto-instantiated
4. **Compliance**: 95% of mandatory checklists completed
5. **Adoption**: 90% of employees logging time against operational tasks

## Risk Mitigation

1. **Change Management**: Provide training on BAU vs Project distinction
2. **Data Quality**: Validate APQC taxonomy mapping during implementation
3. **Performance**: Index operational task queries, cache frequently accessed data
4. **User Adoption**: Start with "Big Rocks" (top 5 recurring tasks) before full rollout