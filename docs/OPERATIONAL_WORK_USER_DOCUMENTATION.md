# Operational Work Management - User Documentation

## Table of Contents

1. [Overview](#overview)
2. [User Interface](#user-interface)
3. [Admin Features](#admin-features)
4. [User Features](#user-features)
5. [Key Concepts](#key-concepts)
6. [Getting Started](#getting-started)
7. [Frequently Asked Questions](#frequently-asked-questions)

## Overview

The Operational Work Management module integrates Business-As-Usual (BAU) work into the TIJA platform, transforming operational tasks from "invisible work" into a managed portfolio. This system helps organizations track, manage, and optimize their operational processes.

### Key Benefits

- **Visibility**: Track all operational work, not just projects
- **Automation**: Automatically create recurring tasks based on schedules
- **Compliance**: Ensure tasks are completed with required checklists
- **Capacity Planning**: Understand how time is allocated across BAU and projects
- **Standardization**: Use APQC taxonomy for consistent process classification

## User Interface

### Help Features

The system includes comprehensive help documentation accessible through:

1. **Help Icons** (ℹ️): Click on question mark icons next to fields or labels for quick tooltips
2. **Help Popovers**: Hover over help icons for detailed explanations
3. **Help Hotspots**: Click on hotspot icons in the top-right of cards for contextual help modals
4. **Inline Help Text**: Descriptive text below page titles explaining the page purpose

### Navigation

- **Admin Section**: `Admin > Operational Work` - For function heads and administrators
- **User Section**: `User > Operational Work` - For all users to manage their tasks

## Admin Features

### Dashboard

The admin dashboard provides an overview of operational work in your functional area:

- **Active Processes**: Number of APQC processes defined
- **Active Workflows**: Number of workflows configured
- **Active Templates**: Number of task templates
- **Overdue Tasks**: Tasks that need attention

**Quick Actions**:
- Define Process
- Create Workflow
- Create SOP
- Create Template

### Process Management

**What are Processes?**
Processes are the building blocks of operational work, following the APQC (American Productivity & Quality Center) taxonomy. Each process has a unique ID (e.g., 8.6.1 for Cash Management).

**Key Features**:
- View all processes in your functional area
- Filter by category, functional area, or search
- Create custom processes for organization-specific workflows
- Link processes to task templates

**Help Tips**:
- Standard processes follow APQC taxonomy
- Custom processes are organization-specific
- Functional area helps organize processes and assign ownership

### Activity Management

**What are Activities?**
Activities are actionable units of work within a process. For example, the "Manage Payroll" process might have activities like "Review Time and Attendance" and "Calculate Gross Pay".

**Key Features**:
- Define activities within processes
- Set estimated duration
- Assign to functional areas

### Workflow Management

**What are Workflows?**
Workflows define the step-by-step process for completing operational tasks, including approvals and dependencies.

**Key Features**:
- Define sequential steps
- Set up transitions between steps
- Configure automatic or manual progression
- Assign roles to steps

**Help Tips**:
- Workflows ensure consistency and compliance
- Transitions can be automatic (based on conditions) or require approval
- Each step can have assigned roles

### SOP Management

**What are SOPs?**
Standard Operating Procedures document how tasks should be performed. SOPs are versioned and require approval.

**Key Features**:
- Create and version SOPs
- Link SOPs to task templates
- Approve/reject SOPs (function heads)
- Track SOP versions

**Help Tips**:
- SOPs are versioned to track changes
- Only approved versions are active
- Function heads approve SOPs before they become active

### Template Management

**What are Templates?**
Templates define recurring operational tasks with schedules, checklists, and assignment rules.

**Key Features**:
- Define task frequency (daily, weekly, monthly, etc.)
- Set processing mode (cron, manual, or both)
- Create checklists with mandatory items
- Configure assignment rules
- Link to processes, workflows, and SOPs

**Processing Modes**:
- **Cron**: Tasks automatically created by scheduled jobs
- **Manual**: Tasks created when users manually trigger them
- **Both**: Tasks can be created automatically or manually

**Assignment Rules**:
- By role (e.g., all AP clerks)
- By function head
- To specific employees

**Help Tips**:
- Frequency determines when tasks are created
- Processing mode controls automation
- Checklists ensure all required steps are completed
- Assignment rules ensure tasks go to the right people

### Function Head Assignment

**What are Function Heads?**
Function heads are responsible for managing operational work in their functional area.

**Key Features**:
- Assign function heads to processes
- Function heads can define processes, approve SOPs, and oversee templates
- Ensures proper ownership and accountability

## User Features

### Dashboard

The user dashboard shows your assigned operational tasks and capacity:

- **Upcoming Tasks**: Tasks due in the next 7 days
- **In Progress**: Tasks currently being worked on
- **BAU Hours**: Time spent on operational tasks (30 days)
- **Available Capacity**: Hours available for additional work

**Help Tips**:
- Click "Execute" on a task to start working on it
- Monitor your capacity to understand workload
- Address overdue tasks promptly

### My Tasks

View and manage your assigned operational tasks in multiple views:

**Views**:
- **List**: Table view with filters
- **Kanban**: Board view organized by status
- **Calendar**: Calendar view (coming soon)

**Filters**:
- Status (Pending, In Progress, Completed, Overdue)
- Date range
- Functional area

**Task Status**:
- **Pending**: Task created but not started
- **In Progress**: Task is being worked on
- **Completed**: Task finished with all checklist items done
- **Overdue**: Task past due date

### Task Execution

When executing a task, you'll see:

1. **Task Details**: Name, description, due date
2. **SOP Link**: Link to Standard Operating Procedure
3. **Checklist**: Required items that must be completed
4. **Time Logging**: Log time spent on the task
5. **Dependencies**: Shows if task is blocked by dependencies
6. **Comments**: Add notes or questions

**Help Tips**:
- Complete all mandatory checklist items before marking as complete
- Log time accurately for capacity planning
- Check dependencies if task seems blocked

### Capacity Planning

View your capacity utilization and planning:

**Capacity Waterline**:
- **Layer 1**: Non-Working Time (PTO, holidays)
- **Layer 2**: BAU (operational tasks)
- **Layer 3**: Projects
- **Available**: Remaining capacity

**Metrics**:
- Total Capacity: 2,080 hours (annual FTE)
- BAU Hours: Time spent on operational tasks
- Project Hours: Time spent on projects
- Available Capacity: Hours available for additional work

**Help Tips**:
- Waterline visualization shows how time is allocated
- Understanding capacity helps balance workload
- Operational tax is necessary work to keep business running

### Operational Projects

View operational projects (BAU buckets) that track time by functional area:

**Features**:
- View allocated vs actual hours
- See utilization percentage
- View resource allocations
- Review time log summary

**Help Tips**:
- Operational projects help with capacity planning
- Time logged against tasks is automatically allocated
- Over 100% utilization indicates capacity issues

### Templates & Projects (View Only)

Users can view available templates and operational projects to understand:
- What tasks are available
- How tasks are structured
- How operational work is organized

## Key Concepts

### APQC Taxonomy

The APQC Process Classification Framework provides a standard taxonomy for classifying business processes. Processes are organized hierarchically:

- **Categories** (e.g., 8.0 Manage Financial Resources)
- **Process Groups** (e.g., 8.6 Manage Treasury Operations)
- **Processes** (e.g., 8.6.1 Manage Cash)
- **Activities** (e.g., 8.6.1.1 Reconcile Cash Accounts)

### Operational Tax

The time spent on Business-As-Usual (BAU) tasks. This "tax" on capacity is necessary operational work that must be done to keep the business running. Understanding this helps balance BAU vs project work.

### FTE (Full-Time Equivalent)

FTE represents the number of full-time employees needed. Calculated as: Annual Hours / 2,080 (standard work hours per year). This helps with resource planning and budgeting.

### Capacity Waterline

A visual representation showing how time is allocated across:
1. Non-working time (PTO, holidays)
2. BAU (operational tasks)
3. Projects
4. Available capacity

This helps understand workload distribution and plan accordingly.

### Processing Modes

Tasks can be processed in three ways:

1. **Cron**: Automatically created by scheduled jobs (recommended for recurring tasks)
2. **Manual**: Created when users manually trigger them (for ad-hoc or event-driven tasks)
3. **Both**: Can be created automatically or manually (flexible option)

### Task Dependencies

Some tasks depend on others being completed first. The system shows if a task is blocked by dependencies. Complete prerequisite tasks first.

## Getting Started

### For Administrators

1. **Define Processes**: Start by defining APQC processes for your functional area
2. **Create Workflows**: Define workflows for complex processes
3. **Create SOPs**: Document Standard Operating Procedures
4. **Create Templates**: Set up task templates with schedules and checklists
5. **Assign Function Heads**: Assign ownership of processes

### For Users

1. **View Dashboard**: Check your dashboard for upcoming tasks
2. **Process Notifications**: Click "Process" on pending task notifications
3. **Execute Tasks**: Click "Execute" to start working on tasks
4. **Complete Checklists**: Complete all mandatory checklist items
5. **Log Time**: Log time accurately for capacity planning

## Frequently Asked Questions

### Q: What's the difference between a process and a template?

**A**: A process is a classification (e.g., "Manage Payroll") following APQC taxonomy. A template is a specific recurring task (e.g., "Monthly Payroll Processing") that uses a process and defines the schedule, checklist, and assignment rules.

### Q: How do I know if a task is overdue?

**A**: Overdue tasks are highlighted in red and shown in the "Overdue Tasks" section. The system also sends notifications for overdue tasks.

### Q: Can I create tasks manually?

**A**: Yes, if the template's processing mode is "manual" or "both". Click "Process" on pending task notifications or use the template view page to create instances.

### Q: What happens if I don't complete mandatory checklist items?

**A**: You cannot mark a task as complete until all mandatory checklist items are checked. This ensures compliance and quality.

### Q: How is capacity calculated?

**A**: Capacity is calculated as: Total Capacity (2,080 hrs) - Non-Working Time - BAU Hours - Project Hours = Available Capacity

### Q: What's the difference between BAU and projects?

**A**: BAU (Business-As-Usual) are recurring operational tasks that keep the business running. Projects are temporary initiatives with specific goals and end dates.

### Q: How do I assign tasks to specific people?

**A**: In the template, configure the assignment rule to assign by specific employee ID, or use role-based assignment for flexibility.

### Q: Can I change a task's due date?

**A**: Due dates are typically set by the template's schedule. Contact your function head or administrator if you need to adjust a due date.

### Q: What if I need help with a task?

**A**: Check the SOP link in the task details, review the checklist, or contact your function head. You can also add comments to the task.

### Q: How do I view my capacity over time?

**A**: Use the Capacity Planning page and adjust the date range to see capacity over different periods.

## Additional Resources

- **Help Icons**: Look for question mark icons (ℹ️) throughout the interface for contextual help
- **Help Modals**: Click on hotspot icons for detailed explanations
- **SOPs**: Always check the linked SOP for detailed procedures
- **Function Heads**: Contact your function head for questions about processes and templates

---

*Last Updated: <?php echo date('F Y'); ?>*

