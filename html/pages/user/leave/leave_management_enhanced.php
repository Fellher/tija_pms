<?php
/**
 * Enhanced Leave Management System
 *
 * Comprehensive leave management for global organizations
 * Features:
 * - Multi-jurisdiction holiday support
 * - Flexible leave accumulation policies
 * - Multi-level approval workflow
 * - Project clearance requirements
 * - Leave balance tracking and analytics
 * - Mobile-responsive design
 *
 * @author System Administrator
 * @version 3.0
 * @since 2024
 */

if(!$isValidUser) {
    Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

// ============================================================================
// DATA INITIALIZATION AND VALIDATION
// ============================================================================

/**
 * Initialize user and organizational context
 */
$employeeID = (isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) : $userDetails->ID;
$employeeDetails = Employee::employees(array('ID'=>$employeeID), true, $DBConn);
$orgDataID = isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : $employeeDetails->orgDataID;
$entityID = isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : $employeeDetails->entityID;
// var_dump($employeeDetails);
/**
 * Fetch comprehensive leave data
 */
$leaveTypes = Leave::leave_types(array('Lapsed'=>'N'), false, $DBConn);
$leavePeriods = Leave::leave_periods(array('Lapsed'=>'N'), false, $DBConn);
$leaveEntitlements = Leave::leave_entitlements(array('Suspended'=>'N', 'entityID'=>$entityID), false, $DBConn);
$myLeaveApplications = Leave::leave_applications_full(array('Suspended'=>'N', 'employeeID'=>$userDetails->ID), false, $DBConn);

// Ensure we have an array to work with
if (!is_array($myLeaveApplications)) {
    $myLeaveApplications = [];
}

/**
 * Get employee organizational hierarchy for approvals
 */
$directReport = Employee::get_direct_report($employeeDetails->ID, $DBConn);
$departmentHead = Employee::get_department_head($employeeDetails->departmentID ?? null, $DBConn);
$hrManager = Employee::get_hr_manager($orgDataID, $entityID, $DBConn);

/**
 * Get employee's active projects for clearance requirements
 */
$employeeProjects = Employee::get_employee_active_projects($employeeDetails->ID, $DBConn);

/**
 * Calculate leave balances and analytics
 */
$leaveBalances = Leave::calculate_leave_balances($employeeDetails->ID, $entityID, $DBConn);

// var_dump($leaveBalances);

// var_dump($leaveBalances);
$leaveAnalytics = Leave::get_leave_analytics($employeeDetails->ID, $DBConn);
$hrManagerScope = Employee::get_hr_manager_scope($userDetails->ID, $DBConn);

/**
 * Get global holidays for employee's jurisdiction
 */
$globalHolidays = Leave::get_global_holidays($employeeDetails->country ?? 'Kenya', $employeeDetails->state ?? null, $DBConn);

$holidayDates = [];
if (!empty($globalHolidays)) {
    foreach ($globalHolidays as $holidayItem) {
        $holidayDate = null;
        if (is_object($holidayItem) && isset($holidayItem->holidayDate)) {
            $holidayDate = $holidayItem->holidayDate;
        } elseif (is_array($holidayItem) && isset($holidayItem['holidayDate'])) {
            $holidayDate = $holidayItem['holidayDate'];
        }
        if ($holidayDate) {
            $holidayDates[] = date('Y-m-d', strtotime($holidayDate));
        }
    }
    $holidayDates = array_values(array_unique($holidayDates));
}

/**
 * Get current view mode
 */
$currentView = isset($_GET['view']) ? Utility::clean_string($_GET['view']) : 'dashboard';
$validViews = ['dashboard', 'apply', 'history', 'team', 'analytics'];
if (!in_array($currentView, $validViews)) {
    $currentView = 'dashboard';
}
?>

<!-- ============================================================================
     ENHANCED LEAVE MANAGEMENT INTERFACE
     ============================================================================ -->

<!-- Page Header with Navigation -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div class="d-flex align-items-center">
        <h1 class="page-title fw-medium fs-24 mb-0 me-3">Leave Management</h1>

        <!-- View Navigation -->
        <nav class="nav nav-pills">
            <a class="nav-link <?= $currentView == 'dashboard' ? 'active' : '' ?>"
               href="?s=user&ss=leave&p=leave_management_enhanced&view=dashboard">
                <i class="ri-dashboard-line me-1"></i>Dashboard
            </a>
            <a class="nav-link <?= $currentView == 'apply' ? 'active' : '' ?>"
               href="?s=user&ss=leave&p=leave_management_enhanced&view=apply">
                <i class="ri-calendar-add-line me-1"></i>Apply Leave
            </a>
            <a class="nav-link <?= $currentView == 'history' ? 'active' : '' ?>"
               href="?s=user&ss=leave&p=leave_management_enhanced&view=history">
                <i class="ri-history-line me-1"></i>History
            </a>
            <a class="nav-link <?= $currentView == 'team' ? 'active' : '' ?>"
               href="?s=user&ss=leave&p=leave_management_enhanced&view=team">
                <i class="ri-team-line me-1"></i>Team
            </a>
            <a class="nav-link <?= $currentView == 'analytics' ? 'active' : '' ?>"
               href="?s=user&ss=leave&p=leave_management_enhanced&view=analytics">
                <i class="ri-bar-chart-line me-1"></i>Analytics
            </a>
        </nav>
    </div>

    <div class="ms-md-1 ms-0">
        <!-- Quick Actions -->
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#applyLeaveModal">
                <i class="ri-add-line me-1"></i>Apply Leave
            </button>
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#leaveCalendarModal">
                <i class="ri-calendar-line me-1"></i>Calendar
            </button>
        </div>
    </div>
</div>

<!-- Leave Balance Overview -->
<div class="container-fluid mb-4">
    <div class="row">
        <!-- Annual Leave Balance -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="metric-icon bg-primary bg-opacity-10 text-primary">
                                <i class="ri-calendar-check-line fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Annual Leave</h6>
                            <h4 class="mb-0 fw-bold"><?= $leaveBalances['annual_leave']['available'] ?? 0 ?></h4>
                            <small class="text-primary">
                                <i class="ri-information-line"></i>
                                <?= $leaveBalances['annual_leave']['used'] ?? 0 ?> used of <?= $leaveBalances['annual_leave']['total'] ?? 0 ?>
                            </small>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar bg-primary"
                                style="width: <?= $leaveBalances['annual_leave']['percentage'] ?? 0 ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sick Leave Balance -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="metric-icon bg-success bg-opacity-10 text-success">
                                <i class="ri-heart-pulse-line fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Sick Leave</h6>
                            <h4 class="mb-0 fw-bold"><?= $leaveBalances['sick_leave']['available'] ?? 0 ?></h4>
                            <small class="text-success">
                                <i class="ri-information-line"></i>
                                <?= $leaveBalances['sick_leave']['used'] ?? 0 ?> used of <?= $leaveBalances['sick_leave']['total'] ?? 0 ?>
                            </small>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar bg-success"
                             style="width: <?= $leaveBalances['sick_leave']['percentage'] ?? 0 ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Applications -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="metric-icon bg-warning bg-opacity-10 text-warning">
                                <i class="ri-time-line fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Pending Applications</h6>
                            <h4 class="mb-0 fw-bold"><?= count(array_filter($myLeaveApplications, function($app) {
                                return in_array((int)$app->leaveStatusID, [2, 3], true);
                            })) ?></h4>
                            <small class="text-warning">
                                <i class="ri-eye-line"></i> Awaiting approval
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Leave -->
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="metric-icon bg-info bg-opacity-10 text-info">
                                <i class="ri-calendar-todo-line fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Next Leave</h6>
                            <h4 class="mb-0 fw-bold">
                                <?php
                                $upcomingLeaves = array_filter($myLeaveApplications, function($app) {
                                    return (int)$app->leaveStatusID === 6 && strtotime($app->startDate) > time();
                                });
                                if (!empty($upcomingLeaves)) {
                                    echo date('M d', strtotime(reset($upcomingLeaves)->startDate));
                                } else {
                                    echo 'None';
                                }
                                ?>
                            </h4>
                            <small class="text-info">
                                <i class="ri-calendar-line"></i>
                                <?= !empty($upcomingLeaves) ? 'Approved leave' : 'No scheduled leave' ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Area -->
<div class="container-fluid">
    <div class="row">
        <!-- Dashboard View -->
        <div id="dashboard-view" class="view-container <?= $currentView == 'dashboard' ? 'active' : '' ?>">
            <?php include 'leave_views/dashboard_view.php'; ?>
        </div>

        <!-- Apply Leave View -->
        <div id="apply-view" class="view-container <?= $currentView == 'apply' ? 'active' : '' ?>">
            <?php include 'leave_views/apply_leave_view.php'; ?>
        </div>

        <!-- History View -->
        <div id="history-view" class="view-container <?= $currentView == 'history' ? 'active' : '' ?>">
            <?php include 'leave_views/history_view.php'; ?>
        </div>

        <!-- Team View -->
        <div id="team-view" class="view-container <?= $currentView == 'team' ? 'active' : '' ?>">
            <?php include 'leave_views/team_view.php'; ?>
        </div>

        <!-- Analytics View -->
        <div id="analytics-view" class="view-container <?= $currentView == 'analytics' ? 'active' : '' ?>">
            <?php include 'leave_views/analytics_view.php'; ?>
        </div>
    </div>
</div>
<!-- Enhanced JavaScript moved to assets/php/src/pages/user/leave/leave_management_enhanced.php -->

<!-- Include Leave Management Modals -->
<?php include 'modals/leave_modals_include.php'; ?>
