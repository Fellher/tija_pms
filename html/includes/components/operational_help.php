<?php
/**
 * Operational Work Help Component
 *
 * Provides contextual help, tooltips, and documentation for operational work features
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

/**
 * Render help icon with tooltip
 */
function renderHelpIcon($helpText, $placement = 'top', $size = 'sm') {
    $id = 'help-' . uniqid();
    return '<i class="ri-question-line text-info help-icon"
                data-bs-toggle="tooltip"
                data-bs-placement="' . htmlspecialchars($placement) . '"
                data-bs-title="' . htmlspecialchars($helpText) . '"
                id="' . $id . '"
                style="cursor: help; font-size: 0.9rem;"></i>';
}

/**
 * Render help icon with popover (for longer content)
 */
function renderHelpPopover($title, $content, $placement = 'top', $trigger = 'hover') {
    $id = 'help-popover-' . uniqid();
    return '<i class="ri-question-line text-info help-icon"
                data-bs-toggle="popover"
                data-bs-placement="' . htmlspecialchars($placement) . '"
                data-bs-trigger="' . htmlspecialchars($trigger) . '"
                data-bs-title="' . htmlspecialchars($title) . '"
                data-bs-content="' . htmlspecialchars($content) . '"
                id="' . $id . '"
                style="cursor: help; font-size: 0.9rem;"></i>';
}

/**
 * Render contextual hotspot with help modal trigger
 */
function renderHelpHotspot($helpKey, $position = 'top-right') {
    $id = 'hotspot-' . $helpKey;
    return '<span class="help-hotspot position-absolute"
                data-help-key="' . htmlspecialchars($helpKey) . '"
                data-bs-toggle="modal"
                data-bs-target="#helpModal"
                id="' . $id . '"
                style="cursor: pointer; z-index: 10;">
                <i class="ri-question-answer-line text-primary"></i>
            </span>';
}

/**
 * Get help content by key
 */
function getHelpContent($key) {
    $helpContent = [
        // Admin Dashboard
        'admin_dashboard_overview' => [
            'title' => 'Operational Work Dashboard',
            'content' => 'This dashboard provides an overview of all operational work in your functional area. Use the quick actions to create new processes, workflows, SOPs, or templates. Monitor active processes, workflows, and templates, and address overdue tasks promptly.'
        ],
        'admin_dashboard_processes' => [
            'title' => 'Active Processes',
            'content' => 'Shows the number of active APQC processes defined for your functional area. Processes are the building blocks of operational work and are used to categorize tasks.'
        ],
        'admin_dashboard_workflows' => [
            'title' => 'Active Workflows',
            'content' => 'Displays the number of active workflows. Workflows define the step-by-step process for completing operational tasks, including approvals and dependencies.'
        ],
        'admin_dashboard_templates' => [
            'title' => 'Active Templates',
            'content' => 'Shows the number of active task templates. Templates define recurring operational tasks with schedules, checklists, and assignment rules.'
        ],
        'admin_dashboard_overdue' => [
            'title' => 'Overdue Tasks',
            'content' => 'Displays tasks that are past their due date. Click to view and take action on overdue tasks.'
        ],

        // User Dashboard
        'user_dashboard_overview' => [
            'title' => 'My Operational Work Dashboard',
            'content' => 'This dashboard shows your assigned operational tasks and capacity. Monitor upcoming tasks, track your progress, and view your available capacity for taking on additional work.'
        ],
        'user_dashboard_upcoming' => [
            'title' => 'Upcoming Tasks',
            'content' => 'Tasks due in the next 7 days. Click on a task to execute it and complete the required checklist items.'
        ],
        'user_dashboard_capacity' => [
            'title' => 'Capacity Waterline',
            'content' => 'Shows how your time is allocated: Non-working time (PTO, holidays), BAU (operational tasks), Projects, and Available capacity. This helps you understand your workload distribution.'
        ],

        // Processes
        'process_apqc' => [
            'title' => 'APQC Process Classification',
            'content' => 'APQC (American Productivity & Quality Center) provides a standard taxonomy for classifying business processes. Each process has a unique ID (e.g., 8.6.1 for Cash Management). You can use standard APQC processes or create custom ones for your organization.'
        ],
        'process_functional_area' => [
            'title' => 'Functional Area',
            'content' => 'The department or business unit responsible for this process. This helps organize processes and assign function heads.'
        ],
        'process_custom' => [
            'title' => 'Custom vs Standard Processes',
            'content' => 'Standard processes follow the APQC taxonomy. Custom processes are organization-specific processes that don\'t fit the standard taxonomy. Both can be used to create operational task templates.'
        ],

        // Activities
        'activity_process' => [
            'title' => 'Activities within Processes',
            'content' => 'Activities are the actionable units of work within a process. For example, the "Manage Payroll" process might have activities like "Review Time and Attendance" and "Calculate Gross Pay".'
        ],

        // Workflows
        'workflow_steps' => [
            'title' => 'Workflow Steps',
            'content' => 'Define the sequential steps required to complete a task. Each step can have assigned roles, required approvals, and specific actions. Workflows ensure consistency and compliance.'
        ],
        'workflow_transitions' => [
            'title' => 'Workflow Transitions',
            'content' => 'Transitions define how tasks move from one step to another. They can be automatic (based on conditions) or require manual approval. Transitions help automate task progression.'
        ],

        // SOPs
        'sop_versioning' => [
            'title' => 'SOP Versioning',
            'content' => 'Standard Operating Procedures are versioned to track changes over time. When you update an SOP, create a new version and submit it for approval. Only approved versions are active.'
        ],
        'sop_approval' => [
            'title' => 'SOP Approval Process',
            'content' => 'SOPs require approval from function heads before they become active. Pending SOPs are shown in the dashboard and can be approved or rejected with comments.'
        ],

        // Templates
        'template_frequency' => [
            'title' => 'Task Frequency',
            'content' => 'Define how often a task should be created: Daily, Weekly, Monthly, Quarterly, Annually, or Custom. The system automatically creates task instances based on the schedule.'
        ],
        'template_processing_mode' => [
            'title' => 'Processing Mode',
            'content' => 'Cron: Tasks are automatically created by scheduled jobs. Manual: Tasks are created when users manually trigger them. Both: Tasks can be created automatically or manually.'
        ],
        'template_assignment' => [
            'title' => 'Assignment Rules',
            'content' => 'Define how tasks are assigned: by role (e.g., all AP clerks), by function head, or to specific employees. Assignment rules ensure tasks go to the right people automatically.'
        ],
        'template_checklist' => [
            'title' => 'Task Checklists',
            'content' => 'Checklists ensure all required steps are completed. Mandatory items must be checked before a task can be completed. Checklists help maintain quality and compliance.'
        ],

        // Tasks
        'task_status' => [
            'title' => 'Task Status',
            'content' => 'Pending: Task created but not started. In Progress: Task is being worked on. Completed: Task finished with all checklist items done. Overdue: Task past due date.'
        ],
        'task_execution' => [
            'title' => 'Executing Tasks',
            'content' => 'Click "Execute" to start working on a task. You\'ll see the task details, checklist items, SOP link, and time logging. Complete all mandatory checklist items before marking as complete.'
        ],
        'task_dependencies' => [
            'title' => 'Task Dependencies',
            'content' => 'Some tasks depend on others being completed first. The system will show you if a task is blocked by dependencies. Complete prerequisite tasks first.'
        ],

        // Capacity Planning
        'capacity_waterline' => [
            'title' => 'Capacity Waterline',
            'content' => 'A visual representation of how your time is allocated: Layer 1 (Non-working: PTO, holidays), Layer 2 (BAU: operational tasks), Layer 3 (Projects), and Available capacity. This helps you understand your workload and plan accordingly.'
        ],
        'capacity_fte' => [
            'title' => 'Full-Time Equivalent (FTE)',
            'content' => 'FTE represents the number of full-time employees needed. Calculated as: Annual Hours / 2080 (standard work hours per year). This helps with resource planning and budgeting.'
        ],
        'capacity_operational_tax' => [
            'title' => 'Operational Tax',
            'content' => 'The time spent on Business-As-Usual (BAU) tasks. This "tax" on your capacity is necessary operational work that must be done to keep the business running. Understanding this helps balance BAU vs project work.'
        ],

        // Projects (BAU Buckets)
        'project_operational' => [
            'title' => 'Operational Projects',
            'content' => 'Operational projects (BAU buckets) are used to track time spent on operational work by functional area. They help with capacity planning and FTE calculations. Time logged against operational tasks is automatically allocated to these projects.'
        ],
        'project_utilization' => [
            'title' => 'Project Utilization',
            'content' => 'Shows how much of the allocated hours have been used. Over 100% indicates more time was spent than planned. This helps identify capacity issues and plan for future periods.'
        ],

        // Reports
        'report_health' => [
            'title' => 'Operational Health Metrics',
            'content' => 'Track key metrics: Task volume (tasks completed), Cycle time (average completion time), Backlog (overdue tasks), Quality/Error rate, and SLA compliance. These metrics help identify areas for improvement.'
        ],
        'report_executive' => [
            'title' => 'Executive Dashboard',
            'content' => 'High-level view of operational work investment: Investment mix (Run vs Grow vs Transform), Capacity waterline across teams, FTE by functional area, and efficiency trends. Helps leadership make strategic decisions.'
        ],

        // Function Heads
        'function_head_assignment' => [
            'title' => 'Function Head Assignment',
            'content' => 'Function heads are responsible for managing operational work in their functional area. They can define processes, approve SOPs, and oversee templates. Assign function heads to ensure proper ownership and accountability.'
        ],

        // Notifications
        'notification_pending' => [
            'title' => 'Pending Task Notifications',
            'content' => 'You receive notifications for tasks that require manual processing (processing mode: manual or both). Click "Process" to create the task instance and start working on it. Notifications appear in the bell icon and as dashboard alerts.'
        ]
    ];

    return $helpContent[$key] ?? ['title' => 'Help', 'content' => 'Help content not available.'];
}
?>

<!-- Help Modal -->
<div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="helpModalLabel">Help & Documentation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="helpModalBody">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // Initialize popovers
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));

    // Handle help hotspot clicks
    document.querySelectorAll('.help-hotspot').forEach(hotspot => {
        hotspot.addEventListener('click', function() {
            const helpKey = this.getAttribute('data-help-key');
            loadHelpContent(helpKey);
        });
    });

    // Handle help modal triggers
    document.querySelectorAll('[data-help-key]').forEach(element => {
        element.addEventListener('click', function() {
            const helpKey = this.getAttribute('data-help-key');
            if (helpKey) {
                loadHelpContent(helpKey);
            }
        });
    });
});

function loadHelpContent(helpKey) {
    const helpContent = <?php
        $allHelp = [];
        foreach (['admin_dashboard_overview', 'admin_dashboard_processes', 'admin_dashboard_workflows', 'admin_dashboard_templates', 'admin_dashboard_overdue', 'user_dashboard_overview', 'user_dashboard_upcoming', 'user_dashboard_capacity', 'process_apqc', 'process_functional_area', 'process_custom', 'activity_process', 'workflow_steps', 'workflow_transitions', 'sop_versioning', 'sop_approval', 'template_frequency', 'template_processing_mode', 'template_assignment', 'template_checklist', 'task_status', 'task_execution', 'task_dependencies', 'capacity_waterline', 'capacity_fte', 'capacity_operational_tax', 'project_operational', 'project_utilization', 'report_health', 'report_executive', 'function_head_assignment', 'notification_pending'] as $key) {
            $allHelp[$key] = getHelpContent($key);
        }
        echo json_encode($allHelp);
    ?>;

    const content = helpContent[helpKey] || {title: 'Help', content: 'Help content not available.'};

    document.getElementById('helpModalLabel').textContent = content.title;
    document.getElementById('helpModalBody').innerHTML = '<p>' + content.content + '</p>';

    const modal = new bootstrap.Modal(document.getElementById('helpModal'));
    modal.show();
}
</script>

<style>
.help-icon {
    margin-left: 4px;
    vertical-align: middle;
    opacity: 0.7;
    transition: opacity 0.2s;
}

.help-icon:hover {
    opacity: 1;
}

.help-hotspot {
    top: 5px;
    right: 5px;
    padding: 5px;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.help-hotspot:hover {
    background: rgba(255, 255, 255, 1);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.help-text {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 0.25rem;
}
</style>

