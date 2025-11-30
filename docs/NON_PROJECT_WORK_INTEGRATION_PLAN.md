# Non-Project Work Integration Plan for TIJA Platform

## Overview
This plan integrates Business-As-Usual (BAU) work management into TIJA, transforming operational tasks from "invisible work" into a managed portfolio. The implementation follows the APQC taxonomy framework, adopts a hybrid architecture with separate BAU module but unified reporting, and includes comprehensive admin tools for function heads to define processes, workflows, SOPs, and enable process optimization through intelligent automation.

## Architecture Principles

### 1. Hybrid Work Model
- **Separate BAU Module**: Dedicated section for operational work management
- **Unified Data Model**: BAU tasks inherit from base task structure (similar to ServiceNow inheritance model)
- **Unified Reporting**: Combined analytics showing Project + BAU capacity utilization

### 2. Data Model Strategy
- Extend existing `tija_tasks_time_logs` with BAU-specific fields
- Create new tables for BAU taxonomy, templates, workflows, SOPs, and operational projects
- Leverage existing `taskType` enum (add 'operational' type)

### 3. Admin-Driven Configuration
- Function heads can define functions, tasks, activities, and subtasks
- Role-based and employee-specific assignments
- Workflow definition and management
- SOP creation and management
- Process modeling and simulation capabilities

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
- `functionalArea` ENUM('Finance','HR','IT','Sales','Marketing','Legal','Facilities','Custom')
- `isActive` ENUM('Y','N')

#### 1.2 Workflow Definition Tables
**File**: `database/migrations/create_workflow_definition_tables.sql`

Create workflow management tables:
- `tija_workflows` - Master workflow definitions
  - `workflowID`, `workflowCode`, `workflowName`, `workflowDescription`
  - `processID` (FK to APQC processes)
  - `functionalArea` ENUM
  - `workflowType` ENUM('sequential','parallel','conditional','state_machine')
  - `version` INT - Version control
  - `isActive` ENUM('Y','N')
  - `createdByID`, `functionalAreaOwnerID`
  - `workflowDefinition` JSON - Workflow structure (nodes, edges, conditions)

- `tija_workflow_steps` - Individual steps in workflow
  - `workflowStepID`, `workflowID` (FK)
  - `stepOrder` INT
  - `stepName`, `stepDescription`
  - `stepType` ENUM('task','approval','decision','notification','automation','subprocess')
  - `assigneeType` ENUM('role','employee','function_head','auto')
  - `assigneeRoleID` INT (FK to roles)
  - `assigneeEmployeeID` INT (FK to people)
  - `estimatedDuration` DECIMAL(10,2)
  - `isMandatory` ENUM('Y','N')
  - `stepConfig` JSON - Step-specific configuration

- `tija_workflow_transitions` - Transitions between steps
  - `transitionID`, `workflowID`
  - `fromStepID`, `toStepID` (FKs to workflow_steps)
  - `conditionType` ENUM('always','conditional','time_based','event_based')
  - `conditionExpression` JSON - Condition logic
  - `transitionLabel`

- `tija_workflow_instances` - Active workflow executions
  - `instanceID`, `workflowID` (FK)
  - `operationalTaskID` (FK) - Links to operational task
  - `currentStepID` (FK to workflow_steps)
  - `status` ENUM('pending','in_progress','completed','cancelled','error')
  - `startedDate`, `completedDate`
  - `instanceData` JSON - Runtime data

#### 1.3 SOP Management Tables
**File**: `database/migrations/create_sop_management_tables.sql`

Create SOP (Standard Operating Procedure) tables:
- `tija_sops` - SOP master records
  - `sopID`, `sopCode`, `sopTitle`, `sopDescription`
  - `processID` (FK to APQC processes)
  - `functionalArea` ENUM
  - `sopVersion` VARCHAR(20) - Version number
  - `sopDocumentURL` TEXT - Link to document/knowledge base
  - `sopContent` LONGTEXT - Rich text content (HTML/Markdown)
  - `effectiveDate`, `expiryDate`
  - `approvalStatus` ENUM('draft','pending_approval','approved','archived')
  - `approvedByID` INT, `approvedDate` DATETIME
  - `createdByID`, `functionalAreaOwnerID`
  - `isActive` ENUM('Y','N')

- `tija_sop_sections` - SOP structured sections
  - `sectionID`, `sopID` (FK)
  - `sectionOrder` INT
  - `sectionTitle`, `sectionContent` TEXT
  - `sectionType` ENUM('overview','procedure','checklist','troubleshooting','references')

- `tija_sop_attachments` - SOP file attachments
  - `attachmentID`, `sopID` (FK)
  - `fileName`, `fileURL`, `fileType`, `fileSize`
  - `uploadedByID`, `uploadedDate`

- `tija_sop_links` - Links SOPs to tasks/templates
  - `linkID`, `sopID` (FK)
  - `linkType` ENUM('template','task','workflow_step','process')
  - `linkedEntityID` INT - ID of linked entity
  - `isRequired` ENUM('Y','N') - Must review before completion

#### 1.4 Process Modeling & Simulation Tables
**File**: `database/migrations/create_process_modeling_tables.sql`

Create process optimization tables:
- `tija_process_models` - Process model definitions
  - `modelID`, `modelName`, `modelDescription`
  - `processID` (FK)
  - `modelType` ENUM('as_is','to_be','simulation','optimized')
  - `modelDefinition` JSON - Process model (BPMN-like structure)
  - `createdByID`, `createdDate`
  - `isBaseline` ENUM('Y','N') - Baseline for comparison

- `tija_process_simulations` - Simulation runs
  - `simulationID`, `modelID` (FK)
  - `simulationName`, `simulationDescription`
  - `simulationParameters` JSON - Input parameters
  - `simulationResults` JSON - Output metrics
  - `runDate`, `runByID`
  - `status` ENUM('pending','running','completed','failed')

- `tija_process_metrics` - Process performance metrics
  - `metricID`, `processID` (FK)
  - `metricName` VARCHAR(100) - e.g., 'cycle_time', 'cost_per_unit', 'error_rate'
  - `metricValue` DECIMAL(15,4)
  - `metricUnit` VARCHAR(20) - e.g., 'hours', 'dollars', 'percentage'
  - `measurementDate`
  - `source` ENUM('actual','simulated','target')

- `tija_process_optimization_recommendations` - AI/ML recommendations
  - `recommendationID`, `processID` (FK)
  - `recommendationType` ENUM('automation','reengineering','resource_allocation','elimination')
  - `recommendationTitle`, `recommendationDescription`
  - `estimatedImpact` JSON - Expected improvements
  - `implementationEffort` ENUM('low','medium','high')
  - `priority` ENUM('low','medium','high','critical')
  - `status` ENUM('pending','approved','implemented','rejected')
  - `createdDate`, `createdByID`

#### 1.5 Operational Task Templates (Enhanced)
**File**: `database/migrations/create_operational_task_templates.sql`

Create `tija_operational_task_templates`:
- `templateID`, `templateCode`, `templateName`
- `processID` (FK to APQC taxonomy)
- `workflowID` (FK to workflows) - Optional workflow
- `sopID` (FK to SOPs) - Linked SOP
- `functionalArea` ENUM('Finance','HR','IT','Sales','Marketing','Legal','Facilities')
- `frequencyType` ENUM('daily','weekly','monthly','quarterly','annually','custom','event_driven')
- `frequencyInterval` INT
- `frequencyDayOfWeek` INT (1-7)
- `frequencyDayOfMonth` INT (1-31)
- `frequencyMonthOfYear` INT (1-12)
- `triggerEvent` VARCHAR(100) - Event name for event-driven tasks
- `estimatedDuration` DECIMAL(10,2) - hours
- `assignmentRule` JSON - Auto-assignment logic (role-based, employee-specific, round-robin, etc.)
- `requiresApproval` ENUM('Y','N')
- `approverRoleID` INT
- `requiresSOPReview` ENUM('Y','N') - Must review SOP before starting
- `isActive` ENUM('Y','N')
- `createdByID`, `functionalAreaOwnerID`

#### 1.6 Operational Task Instances
**File**: `database/migrations/create_operational_tasks_table.sql`

Create `tija_operational_tasks`:
- `operationalTaskID`, `templateID` (FK)
- `workflowInstanceID` (FK to workflow_instances) - If workflow-enabled
- `instanceNumber` INT - Cycle number
- `dueDate` DATE, `startDate` DATE, `completedDate` DATETIME
- `status` ENUM('pending','in_progress','completed','overdue','cancelled','blocked')
- `assigneeID` INT (FK to people)
- `processID` (FK to APQC)
- `actualDuration` DECIMAL(10,2)
- `nextInstanceDueDate` DATE - For regeneration
- `parentInstanceID` INT - Links to previous cycle
- `blockedByTaskID` INT - Dependency blocker
- `sopReviewed` ENUM('Y','N') - SOP review status

#### 1.7 Operational Task Checklists
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
- `validationRule` JSON - Optional validation logic

#### 1.8 Operational Projects (BAU Buckets)
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
- `functionalAreaOwnerID` - Function head responsible

#### 1.9 Task Dependencies
**File**: `database/migrations/create_operational_task_dependencies.sql`

Create `tija_operational_task_dependencies`:
- `dependencyID`
- `predecessorTaskID` (template or instance)
- `successorTaskID`
- `dependencyType` ENUM('finish_to_start','start_to_start','finish_to_finish')
- `lagDays` INT - Delay in days

#### 1.10 Function Head Assignments
**File**: `database/migrations/create_function_head_assignments.sql`

Create `tija_function_head_assignments`:
- `assignmentID`
- `employeeID` (FK to people) - Function head
- `functionalArea` ENUM('Finance','HR','IT','Sales','Marketing','Legal','Facilities')
- `effectiveDate`, `expiryDate`
- `permissions` JSON - Specific permissions (define_processes, define_workflows, approve_sops, etc.)
- `isActive` ENUM('Y','N')

#### 1.11 Extend Time Logs Table
**File**: `database/migrations/extend_time_logs_for_bau.sql`

Alter `tija_tasks_time_logs`:
- Add `operationalTaskID` INT (FK to `tija_operational_tasks`)
- Add `operationalProjectID` INT (FK to `tija_operational_projects`)
- Extend `taskType` enum to include 'operational'
- Add `processID` VARCHAR(20) - APQC process identifier
- Add `workflowStepID` INT (FK) - If part of workflow

### Phase 2: Backend Classes & Services

#### 2.1 BAU Taxonomy Class
**File**: `php/classes/bautaxonomy.php`

Methods:
- `getCategories()`, `getProcessGroups($categoryID)`, `getProcesses($groupID)`
- `getProcessByID($processID)` - Returns full APQC hierarchy
- `searchProcesses($query)` - Search across taxonomy
- `createCustomProcess($data)` - Function head creates custom process
- `updateProcess($processID, $data)`
- `assignProcessOwner($processID, $employeeID)` - Assign function head

#### 2.2 Workflow Definition Class
**File**: `php/classes/workflowdefinition.php`

Methods:
- `createWorkflow($data)` - Create workflow definition
- `updateWorkflow($workflowID, $data)`
- `getWorkflow($workflowID)` - Get workflow with steps and transitions
- `addWorkflowStep($workflowID, $stepData)`
- `updateWorkflowStep($stepID, $stepData)`
- `addWorkflowTransition($workflowID, $transitionData)`
- `validateWorkflow($workflowID)` - Validate workflow structure
- `activateWorkflow($workflowID)`, `deactivateWorkflow($workflowID)`
- `getWorkflowsByProcess($processID)`
- `getWorkflowsByFunctionalArea($functionalArea)`

#### 2.3 Workflow Engine Class
**File**: `php/classes/workflowengine.php`

Methods:
- `startWorkflow($workflowID, $contextData)` - Initialize workflow instance
- `executeStep($instanceID, $stepID, $stepData)` - Execute workflow step
- `transitionToNext($instanceID, $transitionID)` - Move to next step
- `getWorkflowInstance($instanceID)` - Get instance status
- `handleWorkflowError($instanceID, $error)` - Error handling
- `completeWorkflow($instanceID)` - Mark workflow complete
- `evaluateConditions($instanceID, $conditionExpression)` - Evaluate transition conditions

#### 2.4 SOP Management Class
**File**: `php/classes/sopmanagement.php`

Methods:
- `createSOP($data)` - Create new SOP
- `updateSOP($sopID, $data)`
- `getSOP($sopID)` - Get SOP with sections and attachments
- `addSOPSection($sopID, $sectionData)`
- `updateSOPSection($sectionID, $data)`
- `attachFileToSOP($sopID, $fileData)`
- `linkSOPToTask($sopID, $taskID, $isRequired)`
- `linkSOPToTemplate($sopID, $templateID, $isRequired)`
- `approveSOP($sopID, $approverID)`
- `getSOPsByProcess($processID)`
- `getSOPsByFunctionalArea($functionalArea)`
- `trackSOPReview($sopID, $employeeID)` - Track who reviewed SOP

#### 2.5 Process Modeling & Simulation Class
**File**: `php/classes/processmodeling.php`

Methods:
- `createProcessModel($data)` - Create process model (as-is or to-be)
- `getProcessModel($modelID)`
- `compareModels($baselineModelID, $comparisonModelID)` - Compare as-is vs to-be
- `runSimulation($modelID, $parameters)` - Run process simulation
- `getSimulationResults($simulationID)`
- `calculateProcessMetrics($processID, $dateRange)` - Calculate actual metrics
- `getOptimizationRecommendations($processID)` - Get AI/ML recommendations
- `applyOptimization($processID, $recommendationID)` - Apply optimization
- `trackProcessPerformance($processID)` - Continuous performance tracking

#### 2.6 Operational Task Template Class
**File**: `php/classes/operationaltasktemplate.php`

Methods:
- `createTemplate($data)` - Create new template with SOP, checklist, schedule, workflow
- `updateTemplate($templateID, $data)`
- `getTemplate($templateID)`
- `listTemplates($filters)` - Filter by functional area, frequency, etc.
- `activateTemplate($templateID)`, `deactivateTemplate($templateID)`
- `assignTemplateToRole($templateID, $roleID)` - Role-based assignment
- `assignTemplateToEmployee($templateID, $employeeID)` - Employee-specific assignment

#### 2.7 Operational Task Instance Class
**File**: `php/classes/operationaltask.php`

Methods:
- `instantiateFromTemplate($templateID, $dueDate)` - Create new instance
- `getInstance($operationalTaskID)`
- `updateStatus($operationalTaskID, $status)`
- `completeTask($operationalTaskID, $actualDuration, $checklistData)`
- `regenerateNextInstance($operationalTaskID)` - Calculate and create next cycle
- `getOverdueTasks($filters)`
- `getUpcomingTasks($daysAhead)`
- `startWorkflow($operationalTaskID)` - Initialize workflow if template has workflow

#### 2.8 Recurring Task Scheduler Service
**File**: `php/classes/operationaltaskscheduler.php`

Methods:
- `processScheduledTasks()` - Cron job entry point
- `evaluateTemplates()` - Check which templates need instantiation
- `createInstances($templateID, $dueDate)` - Batch create
- `handleDependencies($operationalTaskID)` - Check and update dependent tasks
- `sendNotifications($operationalTaskID, $eventType)`
- `processEventTriggers($eventName, $eventData)` - Handle event-driven tasks

**Cron Job**: `php/scripts/cron/process_operational_tasks.php` (runs hourly)

#### 2.9 Capacity Planning Class
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

#### 2.10 Intelligent Automation Engine
**File**: `php/classes/intelligentautomation.php`

Methods:
- `analyzeProcessEfficiency($processID)` - Analyze process for optimization opportunities
- `identifyAutomationCandidates($processID)` - Identify tasks suitable for automation
- `suggestProcessReengineering($processID)` - Suggest process improvements
- `generateWorkflowFromProcess($processID)` - Auto-generate workflow from process definition
- `optimizeResourceAllocation($processID)` - Optimize resource assignments
- `predictProcessBottlenecks($processID)` - ML-based bottleneck prediction
- `recommendProcessElimination($processID)` - Identify redundant/obsolete processes

#### 2.11 Extend TimeAttendance Class
**File**: `php/classes/timeattendance.php` (extend existing)

Add methods:
- `logOperationalTime($data)` - Log time against operational task
- `getOperationalTimeLogs($filters)` - Query BAU time logs
- `getBAUHoursByEmployee($employeeID, $dateRange)`
- `getBAUHoursByProcess($processID, $dateRange)`

### Phase 3: Admin Views for Function Heads

#### 3.1 Function Head Dashboard
**File**: `html/pages/admin/operational/dashboard.php`

Features:
- Overview of functional area (assigned area)
- Active processes count
- Active workflows count
- SOPs pending approval
- Process optimization recommendations
- Team capacity utilization
- Quick actions: Define Process, Create Workflow, Create SOP

**Access Control**: Only function heads for their assigned functional areas

#### 3.2 Process Definition Interface
**File**: `html/pages/admin/operational/processes/list.php` - List all processes
**File**: `html/pages/admin/operational/processes/create.php` - Create/edit process
**File**: `html/pages/admin/operational/processes/view.php` - Process details

Process creation form includes:
- APQC Category/Group selection (or create custom)
- Process name, description
- Functional area assignment
- Process owner assignment (function head)
- Link to parent process (hierarchy)
- Process metrics definition (target cycle time, cost, etc.)

#### 3.3 Activity & Task Definition Interface
**File**: `html/pages/admin/operational/activities/list.php` - List activities for a process
**File**: `html/pages/admin/operational/activities/create.php` - Create activity
**File**: `html/pages/admin/operational/tasks/list.php` - List tasks for an activity
**File**: `html/pages/admin/operational/tasks/create.php` - Create task

Activity/Task creation includes:
- Activity/Task name, description
- Parent process/activity selection
- Estimated duration
- Assignment options:
  - Role-based (select role)
  - Employee-specific (select employee)
  - Function head (auto-assign to function head)
  - Round-robin (distribute among team)
- Dependencies (predecessor tasks)
- Checklist items
- Linked SOP (if exists)

#### 3.4 Workflow Definition Interface
**File**: `html/pages/admin/operational/workflows/list.php` - List workflows
**File**: `html/pages/admin/operational/workflows/create.php` - Visual workflow builder
**File**: `html/pages/admin/operational/workflows/view.php` - Workflow details

Workflow builder features:
- **Visual Designer**: Drag-and-drop workflow builder (using library like jsPlumb or React Flow)
- **Step Types**:
  - Task step (links to operational task template)
  - Approval step (assign approver role/employee)
  - Decision step (conditional branching)
  - Notification step (send notifications)
  - Automation step (trigger automation)
  - Subprocess step (call another workflow)
- **Transitions**: Define conditions between steps
- **Assignment Rules**: Per-step assignment (role, employee, function head)
- **Validation**: Validate workflow (no orphaned steps, valid transitions)
- **Version Control**: Save workflow versions
- **Test Mode**: Test workflow before activation

#### 3.5 SOP Management Interface
**File**: `html/pages/admin/operational/sops/list.php` - List SOPs
**File**: `html/pages/admin/operational/sops/create.php` - Create/edit SOP
**File**: `html/pages/admin/operational/sops/view.php` - SOP viewer with version history

SOP creation form includes:
- SOP title, description
- Linked process (APQC)
- Functional area
- Rich text editor for SOP content (TinyMCE or similar)
- Structured sections:
  - Overview
  - Procedure steps
  - Checklist
  - Troubleshooting
  - References
- File attachments
- Version control (version number, effective date, expiry date)
- Approval workflow (submit for approval)
- Link to tasks/templates (mark as required review)

#### 3.6 Process Modeling & Simulation Interface
**File**: `html/pages/admin/operational/processes/model.php` - Process modeler
**File**: `html/pages/admin/operational/processes/simulate.php` - Simulation interface
**File**: `html/pages/admin/operational/processes/optimize.php` - Optimization recommendations

Process modeler features:
- **Visual Process Designer**: BPMN-like process modeling
- **Model Types**:
  - As-Is (current state)
  - To-Be (proposed state)
  - Simulation model
- **Process Elements**:
  - Start/End events
  - Tasks/Activities
  - Decision gateways
  - Parallel/sequential flows
  - Resources (roles, employees)
  - Duration estimates
  - Cost estimates
- **Simulation Parameters**:
  - Volume (number of instances)
  - Resource availability
  - Processing times (with variability)
  - Cost per resource
- **Simulation Results**:
  - Cycle time analysis
  - Resource utilization
  - Cost analysis
  - Bottleneck identification
  - Comparison (as-is vs to-be)

#### 3.7 Process Optimization Dashboard
**File**: `html/pages/admin/operational/processes/optimization.php`

Features:
- **Process Performance Metrics**:
  - Cycle time trends
  - Cost per unit
  - Error rates
  - Resource utilization
- **Optimization Recommendations**:
  - Automation opportunities
  - Process reengineering suggestions
  - Resource allocation improvements
  - Process elimination candidates
- **Impact Analysis**: Estimated improvements (time savings, cost reduction)
- **Implementation Tracking**: Track approved/implemented optimizations

#### 3.8 Assignment Management Interface
**File**: `html/pages/admin/operational/assignments/manage.php`

Features:
- View all process/task assignments
- Filter by:
  - Functional area
  - Process
  - Assignment type (role vs employee)
  - Assignee
- Bulk assignment operations:
  - Assign process to role
  - Assign process to multiple employees
  - Reassign tasks
- Assignment history

### Phase 4: Functional Area Catalog Implementation

#### 4.1 Finance & Accounting Templates
**File**: `database/migrations/seed_finance_operational_templates.sql`

Seed templates based on research document with workflows and SOPs:
- Month-End Close tasks (Cash Reconciliation, AP Ledger Review, etc.)
- Accounts Payable processing workflow
- Fixed Asset Depreciation
- Accruals & Prepayments
- Payroll Reconciliation workflow
- Financial Reporting workflow

Each template includes:
- APQC Process ID (e.g., 8.6.1 for Cash Reconciliation)
- Frequency (monthly)
- Estimated duration
- Checklist items
- Dependencies
- Workflow definition (if applicable)
- Linked SOP

#### 4.2 HR Templates
**File**: `database/migrations/seed_hr_operational_templates.sql`

Seed templates with workflows:
- Payroll Administration (Time & Attendance Review, Gross-to-Net Calculation, etc.)
- Employee Onboarding (90-day workflow with multiple steps)
- Benefits Administration
- Compliance Reporting

#### 4.3 IT Templates
**File**: `database/migrations/seed_it_operational_templates.sql`

Seed templates:
- Server Patch Management (multi-step workflow: Assessment → Staging → Approval → Production)
- Backup Verification
- Security Audits
- Help Desk Operations (bucket tracking)

#### 4.4 Sales & Marketing Templates
**File**: `database/migrations/seed_sales_marketing_templates.sql`

Seed templates:
- SDR Daily Routine (Prospecting, Outreach Blocks, CRM Hygiene)
- Marketing Content Operations (Content Prep → Approval → Scheduling → Review workflow)

#### 4.5 Legal & Compliance Templates
**File**: `database/migrations/seed_legal_operational_templates.sql`

Seed templates:
- Annual Report Filing (workflow with legal review steps)
- Board Meeting Preparation (multi-step workflow)
- Contract Reviews (approval workflow)
- Regulatory Compliance (GDPR/CCPA Audits, License Renewals)

#### 4.6 Facilities Templates
**File**: `database/migrations/seed_facilities_operational_templates.sql`

Seed templates:
- HVAC Maintenance (Filter Replacement, Chiller Inspection)
- Life Safety (Fire Extinguisher Inspection, Emergency Lighting Test)
- General Maintenance (Deep Clean, Generator Load Test)

### Phase 5: Frontend Pages & UI (User Views)

#### 5.1 BAU Dashboard
**File**: `html/pages/user/operational/dashboard.php`

Features:
- Operational Health Metrics (Volume, Cycle Time, Backlog, SLA Compliance)
- Upcoming Tasks Widget
- Overdue Tasks Alert
- Capacity Utilization Chart (Waterline visualization)
- Quick Actions: Create Template, Log Time, View Reports

#### 5.2 Template Management (User View)
**File**: `html/pages/user/operational/templates/list.php` - List all templates (read-only for non-admins)
**File**: `html/pages/user/operational/templates/view.php` - Template details with SOP, checklist, schedule

#### 5.3 Operational Tasks View
**File**: `html/pages/user/operational/tasks/list.php`

Features:
- Kanban board (Pending, In Progress, Completed, Overdue)
- Calendar view
- List view with filters (Functional Area, Process, Status, Assignee, Date Range)
- Bulk actions (assign, complete, reschedule)

#### 5.4 Task Execution Interface
**File**: `html/pages/user/operational/tasks/execute.php`

Features:
- Task details with SOP link (required review if marked)
- Embedded checklist (mandatory items must be completed)
- Time logging widget
- Dependency status (shows blockers)
- Workflow progress (if workflow-enabled)
- Approval workflow (if required)
- Comments/notes
- Attachments

#### 5.5 Operational Projects (BAU Buckets)
**File**: `html/pages/user/operational/projects/list.php` - List operational projects
**File**: `html/pages/user/operational/projects/view.php` - Project details with:
- Allocated vs Actual hours
- FTE calculation
- Resource allocations
- Time log summary

#### 5.6 Capacity Planning Dashboard
**File**: `html/pages/user/operational/capacity/dashboard.php`

Features:
- Individual employee capacity waterline chart
- Team capacity heatmap
- FTE calculations by functional area
- Operational tax visualization
- Available capacity for projects

### Phase 6: Backend Scripts & APIs

#### 6.1 Process Management APIs
**File**: `php/scripts/admin/operational/processes/manage_process.php`
- Create, update, delete processes
- Assign process owners
- Link processes to functional areas

**File**: `php/scripts/admin/operational/processes/get_processes.php`
- List processes with filters
- Get process hierarchy

#### 6.2 Activity & Task Management APIs
**File**: `php/scripts/admin/operational/activities/manage_activity.php`
- Create, update, delete activities

**File**: `php/scripts/admin/operational/tasks/manage_task.php`
- Create, update, delete tasks
- Assign tasks to roles/employees

#### 6.3 Workflow Management APIs
**File**: `php/scripts/admin/operational/workflows/manage_workflow.php`
- Create, update, delete workflows
- Save workflow definition (JSON)

**File**: `php/scripts/admin/operational/workflows/get_workflow.php`
- Get workflow with steps and transitions

**File**: `php/scripts/admin/operational/workflows/validate_workflow.php`
- Validate workflow structure

**File**: `php/scripts/admin/operational/workflows/test_workflow.php`
- Test workflow execution

#### 6.4 SOP Management APIs
**File**: `php/scripts/admin/operational/sops/manage_sop.php`
- Create, update, delete SOPs
- Add sections, attach files

**File**: `php/scripts/admin/operational/sops/approve_sop.php`
- Approve/reject SOP

**File**: `php/scripts/admin/operational/sops/get_sops.php`
- List SOPs with filters

#### 6.5 Process Modeling APIs
**File**: `php/scripts/admin/operational/processes/save_model.php`
- Save process model

**File**: `php/scripts/admin/operational/processes/run_simulation.php`
- Run process simulation

**File**: `php/scripts/admin/operational/processes/get_simulation_results.php`
- Get simulation results

**File**: `php/scripts/admin/operational/processes/get_optimization_recommendations.php`
- Get AI/ML optimization recommendations

#### 6.6 Template Management APIs
**File**: `php/scripts/operational/templates/manage_template.php`
- Create, update, delete, activate/deactivate templates

**File**: `php/scripts/operational/templates/get_templates.php`
- List templates with filters

#### 6.7 Task Instance APIs
**File**: `php/scripts/operational/tasks/manage_task.php`
- Create instance, update status, complete task

**File**: `php/scripts/operational/tasks/get_tasks.php`
- List tasks with filters, get task details

**File**: `php/scripts/operational/tasks/regenerate_instance.php`
- Manually trigger next instance creation

#### 6.8 Time Logging API
**File**: `php/scripts/operational/tasks/log_time.php`
- Log time against operational task (extends existing time logging)

#### 6.9 Capacity Planning APIs
**File**: `php/scripts/operational/capacity/get_capacity.php`
- Get employee/team capacity data

**File**: `php/scripts/operational/capacity/calculate_fte.php`
- Calculate FTE for operational work

#### 6.10 Workflow Engine APIs
**File**: `php/scripts/operational/workflows/start_workflow.php`
- Start workflow instance

**File**: `php/scripts/operational/workflows/execute_step.php`
- Execute workflow step

**File**: `php/scripts/operational/workflows/get_workflow_instance.php`
- Get workflow instance status

### Phase 7: Reporting & Analytics

#### 7.1 Operational Health Dashboard
**File**: `html/pages/user/operational/reports/health.php`

Metrics:
- Task Volume (tasks completed per period)
- Cycle Time (average time to complete recurring cycle)
- Backlog (overdue tasks count)
- Quality/Error Rate (tasks reopened)
- SLA Compliance (% completed on time)
- Process Efficiency Trends

#### 7.2 Executive Single Pane of Glass
**File**: `html/pages/user/operational/reports/executive.php`

Visualizations:
- Investment Mix Chart (Run vs Grow vs Transform)
- Capacity Waterline (stacked: PTO, BAU, Projects, Available)
- FTE by Functional Area
- Operational Efficiency Trends
- Process Performance Comparison

#### 7.3 Process Analytics
**File**: `html/pages/user/operational/reports/process_analytics.php`

Features:
- Total labor cost by APQC process
- Process efficiency benchmarking
- Time spent by functional area
- Automation opportunity identification
- Process optimization impact tracking

#### 7.4 Workflow Analytics
**File**: `html/pages/user/operational/reports/workflow_analytics.php`

Features:
- Workflow execution times
- Step-level performance
- Bottleneck identification
- Workflow success rates
- Average time per step

#### 7.5 SOP Analytics
**File**: `html/pages/user/operational/reports/sop_analytics.php`

Features:
- SOP review compliance
- SOP usage by process
- SOP version adoption
- SOP effectiveness (correlation with error rates)

### Phase 8: Integration Points

#### 8.1 Extend Existing Project Views
**Files**:
- `html/pages/user/projects/home.php` (add BAU filter)
- `html/includes/projects/tasks_kanban.php` (show operational tasks option)

Add toggle: "Show Operational Tasks" to existing project views for unified visibility.

#### 8.2 Unified Time Logging
**File**: `html/pages/user/timesheet/log_time.php` (extend existing)

Add:
- Task type selector (Project, Operational, Adhoc, etc.)
- Operational task dropdown (when type = operational)
- Process ID display
- Workflow step (if part of workflow)

#### 8.3 Resource Planning Integration
**File**: `html/includes/scripts/projects/logic/project_plan_logic.php` (extend)

Update `calculateResourceAllocation()` to:
- Subtract operational capacity from available hours
- Show BAU allocation in resource view
- Warn when over-allocating (BAU + Projects > Capacity)

### Phase 9: Automation & Workflows

#### 9.1 Scheduled Task Processor
**File**: `php/scripts/cron/process_operational_tasks.php`

Logic:
1. Query active templates
2. Evaluate recurrence rules (calculate next due date)
3. Create task instances for due templates
4. Handle dependencies (check if predecessors completed)
5. Start workflows (if template has workflow)
6. Send notifications (new task assigned, overdue reminders)
7. Regenerate completed tasks (calculate next cycle)

**Cron Schedule**: Run every hour

#### 9.2 Event-Based Triggers
**File**: `php/classes/operationaltriggers.php`

Methods:
- `onEmployeeTerminated($employeeID)` - Trigger access revocation tasks
- `onInvoiceReceived()` - Trigger AP processing tasks
- `onLeaveApplicationSubmitted($leaveID)` - Trigger handover tasks (integrate with existing leave system)
- `onProcessCompleted($processID)` - Trigger dependent processes
- `onThresholdReached($metricName, $threshold)` - Threshold-based triggers

#### 9.3 Workflow Automation
**File**: `php/classes/workflowautomation.php`

Methods:
- `autoAssignTasks()` - Auto-assign based on rules
- `autoApprove()` - Auto-approve based on conditions
- `autoNotify()` - Send notifications based on workflow state
- `autoEscalate()` - Escalate overdue workflow steps
- `autoComplete()` - Auto-complete steps based on conditions

#### 9.4 Intelligent Process Optimization
**File**: `php/scripts/cron/process_optimization_analyzer.php`

Cron job that:
1. Analyzes process performance metrics
2. Identifies optimization opportunities
3. Generates recommendations
4. Sends alerts to function heads

**Cron Schedule**: Run daily

#### 9.5 Notification System
Extend existing notification system:
- New operational task assigned
- Task due soon (configurable days ahead)
- Task overdue
- Dependency blocked
- Approval required
- Task completed (notify approver)
- Workflow step completed
- SOP updated (notify users)
- Process optimization recommendation available

### Phase 10: Data Migration & Seeding

#### 10.1 APQC Taxonomy Data
**File**: `database/migrations/seed_apqc_taxonomy.sql`

Import APQC Process Classification Framework data:
- Categories (12 main categories)
- Process Groups
- Processes (with IDs like 7.3.1, 8.6.1, etc.)

#### 10.2 Default Operational Projects
**File**: `database/migrations/seed_default_operational_projects.sql`

Create FY operational projects for each functional area:
- "FY25 Finance Operations"
- "FY25 HR Operations"
- "FY25 IT Operations"
- etc.

#### 10.3 Default Function Head Roles
**File**: `database/migrations/seed_function_head_roles.sql`

Create permission roles for function heads:
- Finance Head
- HR Head
- IT Head
- Sales Head
- Marketing Head
- Legal Head
- Facilities Head

### Phase 11: Testing & Validation

#### 11.1 Unit Tests
- Template creation and scheduling logic
- FTE calculations
- Capacity waterline calculations
- Dependency resolution
- Workflow execution
- Process simulation
- SOP linking and review tracking

#### 11.2 Integration Tests
- Time logging integration
- Project capacity integration
- Notification delivery
- Cron job execution
- Workflow automation
- Process optimization recommendations

#### 11.3 User Acceptance Testing
- Template creation workflow
- Workflow definition and execution
- SOP creation and review
- Task execution with checklists
- Process modeling and simulation
- Capacity planning accuracy
- Reporting accuracy
- Function head admin interfaces

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
- Store SOP URLs and rich content in database
- Embed checklists in task instances
- Require SOP review before task execution (configurable)
- Link to knowledge base (future: integrate with Confluence/SharePoint)

### 5. Workflow Intelligence
- Visual workflow builder for function heads
- Support for complex workflows (sequential, parallel, conditional)
- Workflow versioning
- Workflow testing before activation
- Automated workflow execution

### 6. Process Optimization
- Visual process modeling (as-is and to-be)
- Process simulation with metrics
- AI/ML-based optimization recommendations
- Continuous performance tracking
- Impact analysis for optimizations

### 7. Function Head Permissions
- Function heads can only manage their assigned functional areas
- Role-based access control for admin features
- Audit trail for all configuration changes

## Files to Create/Modify

### New Database Tables (20+ tables)
1. `tija_bau_categories`
2. `tija_bau_process_groups`
3. `tija_bau_processes`
4. `tija_bau_activities`
5. `tija_workflows`
6. `tija_workflow_steps`
7. `tija_workflow_transitions`
8. `tija_workflow_instances`
9. `tija_sops`
10. `tija_sop_sections`
11. `tija_sop_attachments`
12. `tija_sop_links`
13. `tija_process_models`
14. `tija_process_simulations`
15. `tija_process_metrics`
16. `tija_process_optimization_recommendations`
17. `tija_operational_task_templates`
18. `tija_operational_tasks`
19. `tija_operational_task_checklists`
20. `tija_operational_projects`
21. `tija_operational_task_dependencies`
22. `tija_function_head_assignments`
23. Extend `tija_tasks_time_logs`

### New PHP Classes (12+ classes)
1. `php/classes/bautaxonomy.php`
2. `php/classes/workflowdefinition.php`
3. `php/classes/workflowengine.php`
4. `php/classes/sopmanagement.php`
5. `php/classes/processmodeling.php`
6. `php/classes/operationaltasktemplate.php`
7. `php/classes/operationaltask.php`
8. `php/classes/operationaltaskscheduler.php`
9. `php/classes/capacityplanning.php`
10. `php/classes/intelligentautomation.php`
11. `php/classes/operationaltriggers.php`
12. `php/classes/workflowautomation.php`

### New Admin Frontend Pages (15+ pages)
1. `html/pages/admin/operational/dashboard.php`
2. `html/pages/admin/operational/processes/list.php`
3. `html/pages/admin/operational/processes/create.php`
4. `html/pages/admin/operational/processes/view.php`
5. `html/pages/admin/operational/processes/model.php`
6. `html/pages/admin/operational/processes/simulate.php`
7. `html/pages/admin/operational/processes/optimize.php`
8. `html/pages/admin/operational/activities/list.php`
9. `html/pages/admin/operational/activities/create.php`
10. `html/pages/admin/operational/tasks/list.php`
11. `html/pages/admin/operational/tasks/create.php`
12. `html/pages/admin/operational/workflows/list.php`
13. `html/pages/admin/operational/workflows/create.php`
14. `html/pages/admin/operational/workflows/view.php`
15. `html/pages/admin/operational/sops/list.php`
16. `html/pages/admin/operational/sops/create.php`
17. `html/pages/admin/operational/sops/view.php`
18. `html/pages/admin/operational/assignments/manage.php`

### New User Frontend Pages (12+ pages)
1. `html/pages/user/operational/dashboard.php`
2. `html/pages/user/operational/templates/list.php`
3. `html/pages/user/operational/templates/view.php`
4. `html/pages/user/operational/tasks/list.php`
5. `html/pages/user/operational/tasks/execute.php`
6. `html/pages/user/operational/projects/list.php`
7. `html/pages/user/operational/projects/view.php`
8. `html/pages/user/operational/capacity/dashboard.php`
9. `html/pages/user/operational/reports/health.php`
10. `html/pages/user/operational/reports/executive.php`
11. `html/pages/user/operational/reports/process_analytics.php`
12. `html/pages/user/operational/reports/workflow_analytics.php`
13. `html/pages/user/operational/reports/sop_analytics.php`

### New Backend Scripts (25+ scripts)
1. `php/scripts/admin/operational/processes/manage_process.php`
2. `php/scripts/admin/operational/processes/get_processes.php`
3. `php/scripts/admin/operational/activities/manage_activity.php`
4. `php/scripts/admin/operational/tasks/manage_task.php`
5. `php/scripts/admin/operational/workflows/manage_workflow.php`
6. `php/scripts/admin/operational/workflows/get_workflow.php`
7. `php/scripts/admin/operational/workflows/validate_workflow.php`
8. `php/scripts/admin/operational/workflows/test_workflow.php`
9. `php/scripts/admin/operational/sops/manage_sop.php`
10. `php/scripts/admin/operational/sops/approve_sop.php`
11. `php/scripts/admin/operational/sops/get_sops.php`
12. `php/scripts/admin/operational/processes/save_model.php`
13. `php/scripts/admin/operational/processes/run_simulation.php`
14. `php/scripts/admin/operational/processes/get_simulation_results.php`
15. `php/scripts/admin/operational/processes/get_optimization_recommendations.php`
16. `php/scripts/operational/templates/manage_template.php`
17. `php/scripts/operational/templates/get_templates.php`
18. `php/scripts/operational/tasks/manage_task.php`
19. `php/scripts/operational/tasks/get_tasks.php`
20. `php/scripts/operational/tasks/log_time.php`
21. `php/scripts/operational/tasks/regenerate_instance.php`
22. `php/scripts/operational/capacity/get_capacity.php`
23. `php/scripts/operational/capacity/calculate_fte.php`
24. `php/scripts/operational/workflows/start_workflow.php`
25. `php/scripts/operational/workflows/execute_step.php`
26. `php/scripts/operational/workflows/get_workflow_instance.php`
27. `php/scripts/cron/process_operational_tasks.php`
28. `php/scripts/cron/process_optimization_analyzer.php`

### Modified Files
1. `php/classes/timeattendance.php` - Add operational time logging methods
2. `html/pages/user/projects/home.php` - Add BAU filter option
3. `html/pages/user/timesheet/log_time.php` - Add operational task type
4. `html/includes/scripts/projects/logic/project_plan_logic.php` - Include BAU in capacity

## Implementation Timeline Estimate

- **Phase 1** (Core Infrastructure): 4-5 weeks
- **Phase 2** (Backend Classes): 4-5 weeks
- **Phase 3** (Admin Views): 5-6 weeks
- **Phase 4** (Functional Catalogs): 2-3 weeks
- **Phase 5** (User Frontend): 4-5 weeks
- **Phase 6** (APIs): 3-4 weeks
- **Phase 7** (Reporting): 3-4 weeks
- **Phase 8-9** (Integration & Automation): 3-4 weeks
- **Phase 10-11** (Data & Testing): 3-4 weeks

**Total Estimated Duration**: 31-40 weeks (7.5-10 months)

## Success Metrics

1. **Visibility**: 100% of identified BAU tasks tracked in system
2. **Accuracy**: Capacity planning within 5% of actual utilization
3. **Efficiency**: 80% of recurring tasks auto-instantiated
4. **Compliance**: 95% of mandatory checklists completed
5. **Adoption**: 90% of employees logging time against operational tasks
6. **Process Optimization**: 20% improvement in process cycle times through optimization
7. **Automation**: 30% of repetitive tasks automated
8. **SOP Compliance**: 90% SOP review rate before task execution

## Risk Mitigation

1. **Change Management**: Provide training on BAU vs Project distinction, workflow definition, process modeling
2. **Data Quality**: Validate APQC taxonomy mapping during implementation
3. **Performance**: Index operational task queries, cache frequently accessed data, optimize workflow execution
4. **User Adoption**: Start with "Big Rocks" (top 5 recurring tasks) before full rollout
5. **Complexity**: Provide templates and wizards for common workflows and processes
6. **Workflow Errors**: Implement comprehensive validation and testing tools
7. **Process Modeling Learning Curve**: Provide training materials and example models

## Future Enhancements

1. **AI/ML Integration**: Advanced process optimization using machine learning
2. **Integration with External Systems**: Connect to ERP, HRIS, ITSM systems
3. **Mobile App**: Mobile interface for task execution and time logging
4. **Advanced Analytics**: Predictive analytics for process performance
5. **Collaboration Features**: Real-time collaboration on process design
6. **Process Mining**: Automatic process discovery from execution logs
7. **RPA Integration**: Robotic Process Automation for fully automated tasks

