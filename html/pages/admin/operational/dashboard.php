<?php
/**
 * Function Head Dashboard - Operational Work Management
 *
 * Dashboard for function heads to manage their functional area's operational work
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

// Check authentication
if(!$isValidUser) {
    Alert::info("You need to be logged in to access this page", true);
    include "includes/core/log_in_script.php";
    return;
}

// Check function head permissions
// TODO: Implement function head permission check
// For now, check if admin
if (!$isAdmin && !$isValidAdmin) {
    Alert::error("Access denied. Function Head privileges required.", true);
    return;
}

// Include help component
include __DIR__ . '/../../../includes/components/operational_help.php';

// Classes are automatically loaded via class_autoload.php (included in php/includes.php)
// No need for explicit require_once statements

global $DBConn, $userID;

// Get functional area (would be determined from function head assignment)
$functionalArea = $_GET['functionalArea'] ?? 'Finance'; // Placeholder

// Get statistics
$activeProcesses = BAUTaxonomy::getProcesses(null, ['functionalArea' => $functionalArea, 'isActive' => 'Y'], false, $DBConn);
$activeWorkflows = WorkflowDefinition::getWorkflowsByFunctionalArea($functionalArea, $DBConn);
$pendingSOPs = $DBConn->retrieve_db_table_rows('tija_sops',
    ['sopID', 'sopCode', 'sopTitle', 'functionalArea', 'approvalStatus'],
    ['functionalArea' => $functionalArea, 'approvalStatus' => 'pending_approval', 'Suspended' => 'N']);
$activeTemplates = OperationalTaskTemplate::listTemplates(['functionalArea' => $functionalArea, 'isActive' => 'Y'], $DBConn);
$overdueTasks = OperationalTask::getOverdueTasks(['functionalArea' => $functionalArea], $DBConn);

$pageTitle = "Operational Work Management Dashboard";
?>

<style>
.hover-shadow {
    transition: box-shadow 0.3s ease, transform 0.3s ease;
}
.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    transform: translateY(-2px);
}
.transition {
    transition: all 0.3s ease;
}
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-sm-0"><?php echo htmlspecialchars($pageTitle); ?></h4>
                    <p class="text-muted mb-0 help-text">
                        Central hub for managing all operational work components and BAUTaxonomy structure.
                        <?php echo renderHelpPopover('Operational Work Dashboard', 'This dashboard provides access to all operational work management tools. Navigate to different sections to manage functional areas, workflows, SOPs, templates, process modeling, and task assignments.', 'right'); ?>
                    </p>
                </div>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="?s=admin">Admin</a></li>
                        <li class="breadcrumb-item active">Operational Work</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">
                                Active Processes
                                <?php echo renderHelpIcon('Shows the number of active APQC processes defined for your functional area. Processes are the building blocks of operational work.', 'top'); ?>
                            </p>
                            <h4 class="mb-2"><?php echo is_array($activeProcesses) ? count($activeProcesses) : 0; ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-primary rounded-3">
                                <i class="ri-file-list-3-line font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">
                                Active Workflows
                                <?php echo renderHelpIcon('Displays the number of active workflows. Workflows define the step-by-step process for completing operational tasks.', 'top'); ?>
                            </p>
                            <h4 class="mb-2"><?php echo count($activeWorkflows); ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-success rounded-3">
                                <i class="ri-flow-chart-line font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">
                                Active Templates
                                <?php echo renderHelpIcon('Shows the number of active task templates. Templates define recurring operational tasks with schedules, checklists, and assignment rules.', 'top'); ?>
                            </p>
                            <h4 class="mb-2"><?php echo is_array($activeTemplates) ? count($activeTemplates) : 0; ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-warning rounded-3">
                                <i class="ri-file-copy-line font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">
                                Overdue Tasks
                                <?php echo renderHelpIcon('Displays tasks that are past their due date. Click to view and take action on overdue tasks.', 'top'); ?>
                            </p>
                            <h4 class="mb-2 text-danger"><?php echo is_array($overdueTasks) ? count($overdueTasks) : 0; ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-danger rounded-3">
                                <i class="ri-alarm-warning-line font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Operational Work Navigation Cards -->
    <div class="row">
        <div class="col-12 mb-4">
            <h5 class="mb-3">Operational Work Management</h5>
            <p class="text-muted">Navigate to different sections to manage your operational work structure and BAUTaxonomy hierarchy.</p>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Functional Areas Card -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 shadow-sm hover-shadow transition">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <div class="avatar-lg me-3">
                            <span class="avatar-title bg-primary bg-soft rounded-3">
                                <i class="ri-organization-chart font-size-24"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-1">Functional Areas</h5>
                            <p class="text-muted mb-0 small">BAUTaxonomy Foundation</p>
                        </div>
                    </div>
                    <p class="card-text text-muted mb-3">
                        Manage the organizational structure including <strong>Categories</strong> (APQC top-level domains),
                        <strong>Process Groups</strong> (functional areas within categories), and <strong>Process Types</strong>
                        (specific workflows). This forms the foundation of the BAUTaxonomy hierarchy.
                    </p>
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1"><strong>Hierarchy:</strong></small>
                        <ul class="small text-muted mb-0 ps-3">
                            <li>Categories → Process Groups → Processes → Activities</li>
                        </ul>
                    </div>
                    <a href="?s=admin&ss=operational&p=functional_areas" class="btn btn-primary btn-sm w-100">
                        <i class="ri-arrow-right-line me-1"></i> Manage Functional Areas
                    </a>
                </div>
            </div>
        </div>

        <!-- Workflows Card -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 shadow-sm hover-shadow transition">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <div class="avatar-lg me-3">
                            <span class="avatar-title bg-success bg-soft rounded-3">
                                <i class="ri-flow-chart font-size-24"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-1">Workflows</h5>
                            <p class="text-muted mb-0 small">Process Orchestration</p>
                        </div>
                    </div>
                    <p class="card-text text-muted mb-3">
                        Define step-by-step workflows that specify how operational tasks should be completed.
                        Configure workflow steps, transitions, approvals, and automation rules. Workflows ensure
                        consistency and compliance in operational processes.
                    </p>
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1"><strong>Function:</strong></small>
                        <ul class="small text-muted mb-0 ps-3">
                            <li>Defines sequential steps for task completion</li>
                            <li>Manages approvals and decision points</li>
                            <li>Ensures process compliance</li>
                        </ul>
                    </div>
                    <a href="?s=admin&ss=operational&p=workflows" class="btn btn-success btn-sm w-100">
                        <i class="ri-arrow-right-line me-1"></i> Manage Workflows
                    </a>
                </div>
            </div>
        </div>

        <!-- Standard Operating Procedures Card -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 shadow-sm hover-shadow transition">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <div class="avatar-lg me-3">
                            <span class="avatar-title bg-info bg-soft rounded-3">
                                <i class="ri-file-text font-size-24"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-1">Standard Operating Procedures</h5>
                            <p class="text-muted mb-0 small">Documentation & Guidelines</p>
                        </div>
                    </div>
                    <p class="card-text text-muted mb-3">
                        Create and manage Standard Operating Procedures (SOPs) that document how operational
                        tasks should be performed. SOPs provide detailed instructions, best practices, and
                        compliance guidelines for team members.
                    </p>
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1"><strong>Function:</strong></small>
                        <ul class="small text-muted mb-0 ps-3">
                            <li>Documents detailed task procedures</li>
                            <li>Ensures standardization across teams</li>
                            <li>Supports training and compliance</li>
                        </ul>
                    </div>
                    <a href="?s=admin&ss=operational&p=sops" class="btn btn-info btn-sm w-100">
                        <i class="ri-arrow-right-line me-1"></i> Manage SOPs
                    </a>
                </div>
            </div>
        </div>

        <!-- Task Templates Card -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 shadow-sm hover-shadow transition">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <div class="avatar-lg me-3">
                            <span class="avatar-title bg-warning bg-soft rounded-3">
                                <i class="ri-file-copy font-size-24"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-1">Task Templates</h5>
                            <p class="text-muted mb-0 small">Reusable Task Definitions</p>
                        </div>
                    </div>
                    <p class="card-text text-muted mb-3">
                        Create reusable task templates that define recurring operational tasks with schedules,
                        checklists, assignment rules, and dependencies. Templates are linked to processes in the
                        BAUTaxonomy and automatically generate task instances.
                    </p>
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1"><strong>Function:</strong></small>
                        <ul class="small text-muted mb-0 ps-3">
                            <li>Defines recurring task patterns</li>
                            <li>Automates task creation and scheduling</li>
                            <li>Links to BAUTaxonomy processes</li>
                        </ul>
                    </div>
                    <a href="?s=admin&ss=operational&p=templates" class="btn btn-warning btn-sm w-100">
                        <i class="ri-arrow-right-line me-1"></i> Manage Templates
                    </a>
                </div>
            </div>
        </div>

        <!-- Process Modeling Card -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 shadow-sm hover-shadow transition">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <div class="avatar-lg me-3">
                            <span class="avatar-title bg-danger bg-soft rounded-3">
                                <i class="ri-node-tree font-size-24"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-1">Process Modeling</h5>
                            <p class="text-muted mb-0 small">Visualize & Optimize</p>
                        </div>
                    </div>
                    <p class="card-text text-muted mb-3">
                        Model, visualize, simulate, and optimize operational processes. Create process flow diagrams,
                        analyze process performance, identify bottlenecks, and optimize workflows for efficiency and effectiveness.
                    </p>
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1"><strong>Function:</strong></small>
                        <ul class="small text-muted mb-0 ps-3">
                            <li>Visual process flow modeling</li>
                            <li>Process simulation and analysis</li>
                            <li>Performance optimization tools</li>
                        </ul>
                    </div>
                    <a href="?s=admin&ss=operational&p=processes_model" class="btn btn-danger btn-sm w-100">
                        <i class="ri-arrow-right-line me-1"></i> Process Modeling
                    </a>
                </div>
            </div>
        </div>

        <!-- Task Assignments Card -->
        <div class="col-xl-4 col-md-6">
            <div class="card h-100 shadow-sm hover-shadow transition">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <div class="avatar-lg me-3">
                            <span class="avatar-title bg-secondary bg-soft rounded-3">
                                <i class="ri-user-settings font-size-24"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-1">Task Assignments</h5>
                            <p class="text-muted mb-0 small">Resource Management</p>
                        </div>
                    </div>
                    <p class="card-text text-muted mb-3">
                        Manage task assignments, reassign tasks, track task status, and monitor resource allocation
                        across functional areas. View task distributions, capacity planning, and assignment history.
                    </p>
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1"><strong>Function:</strong></small>
                        <ul class="small text-muted mb-0 ps-3">
                            <li>Task assignment and reassignment</li>
                            <li>Resource capacity tracking</li>
                            <li>Assignment history and analytics</li>
                        </ul>
                    </div>
                    <a href="?s=admin&ss=operational&p=assignments" class="btn btn-secondary btn-sm w-100">
                        <i class="ri-arrow-right-line me-1"></i> Manage Assignments
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Quick Links -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-links-line me-2"></i>Additional Sections
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="?s=admin&ss=operational&p=processes" class="text-decoration-none">
                                <div class="border rounded p-3 text-center h-100 hover-shadow transition">
                                    <i class="ri-file-list-3-line fs-2 text-primary d-block mb-2"></i>
                                    <h6 class="mb-1">Processes</h6>
                                    <small class="text-muted">Manage APQC processes</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="?s=admin&ss=operational&p=activities" class="text-decoration-none">
                                <div class="border rounded p-3 text-center h-100 hover-shadow transition">
                                    <i class="ri-list-check fs-2 text-success d-block mb-2"></i>
                                    <h6 class="mb-1">Activities</h6>
                                    <small class="text-muted">Manage BAU activities</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="?s=admin&ss=operational&p=tasks" class="text-decoration-none">
                                <div class="border rounded p-3 text-center h-100 hover-shadow transition">
                                    <i class="ri-task-line fs-2 text-info d-block mb-2"></i>
                                    <h6 class="mb-1">Tasks</h6>
                                    <small class="text-muted">View all operational tasks</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="?s=admin&ss=operational&p=function_heads" class="text-decoration-none">
                                <div class="border rounded p-3 text-center h-100 hover-shadow transition">
                                    <i class="ri-team-line fs-2 text-warning d-block mb-2"></i>
                                    <h6 class="mb-1">Function Heads</h6>
                                    <small class="text-muted">Manage function head assignments</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Recent Activity</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Recent operational work activity will be displayed here.</p>
                    <!-- TODO: Implement recent activity feed -->
                </div>
            </div>
        </div>
    </div>
</div>
