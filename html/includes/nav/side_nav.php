   <!-- Start::app-sidebar -->
 <aside class="app-sidebar" id="sidebar">

<!-- Start::main-sidebar-header -->
<div class="main-sidebar-header">
    <a href="<?php echo "{$base}html/?s=$s&p=home" ?>" class="header-logo">
        <img src="../assets/img/brand-logos/desktop-logo.png" alt="logo" class="main-logo desktop-logo">
        <img src="../assets/img/brand-logos/toggle-logo.png" alt="logo" class="main-logo toggle-logo">
        <img src="../assets/img/brand-logos/desktop-dark.png" alt="logo" class="main-logo desktop-dark">
        <img src="../assets/img/brand-logos/toggle-dark.png" alt="logo" class="main-logo toggle-dark">
         <img src="../assets/img/brand-logos/desktop-white.png" alt="logo" class="desktop-white">
        <img src="../assets/img/brand-logos/toggle-white.png" alt="logo" class="toggle-white">
    </a>
</div>
<!-- End::main-sidebar-header -->

<!-- Start::main-sidebar -->
<div class="main-sidebar " id="sidebar-scroll">
    <!-- Start::nav -->
    <nav class="main-menu-container nav nav-pills flex-column sub-open">
        <div class="slide-left" id="slide-left">
            <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
            </svg>
        </div>
        <ul class="main-menu">
            <!-- Start::slide__category -->
            <li class="slide__category"><span class="category-name">TIJA <sup>&copy;</sup> Main </span></li>
            <!-- End::slide__category -->
            <!-- Start::slide -->

            <?php
            if($isAdmin || $isValidAdmin ) { ?>
                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item administration">
                        <i class="ri-settings-3-line side-menu__icon"></i>
                        <span class="side-menu__label">TIJA Administration</span>
                        <i class="ri ri-arrow-right-s-line side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide side-menu__label1"><a href="javascript:void(0)">Dashboards</a></li>
                        <?php
                        if($isValidAdmin) {
                            echo "<li class='slide'><a href='{$base}html/?s=core&ss=admin&p=home' class='side-menu__item'><i class='ri-building-line me-2'></i>Organisation</a></li>";?>
                             <li class="slide"><a href="<?php echo "{$base}html/?s=core&ss=admin&p=user_roles" ?>" class="side-menu__item <?php echo $p=="user_roles" ? "active" : ""; ?>"><i class="ri-user-settings-line me-2"></i>Manage User Roles</a></li>
                             <li class="slide"><a href="<?php echo "{$base}html/?s=core&ss=admin&p=license_types" ?>" class="side-menu__item <?php echo $p=="license_types" ? "active" : ""; ?>"><i class="fas fa-certificate me-2"></i>License Types</a></li>
                             <li class="slide"><a href="<?php echo "{$base}html/?s=core&ss=admin&p=role_types" ?>" class="side-menu__item <?php echo $p=="role_types" ? "active" : ""; ?>"><i class="fas fa-layer-group me-2"></i>Role Types</a></li>
                             <li class="slide"><a href="<?php echo "{$base}html/?s=core&ss=admin&p=role_levels" ?>" class="side-menu__item <?php echo $p=="role_levels" ? "active" : ""; ?>"><i class="fas fa-sitemap me-2"></i>Role Levels</a></li>
                             <?php
                        } ?>
                        <li class="slide"><a href="<?php echo "{$base}html/?s=core&ss=admin&p=jobs" ?>" class="side-menu__item <?php echo $p=="jobs" ? "active" : ""; ?>"><i class="ri-briefcase-line me-2"></i>Jobs</a></li>
                        <li class="slide"><a href="<?php echo "{$base}html/?s=core&ss=admin&p=home" ?>" class="side-menu__item <?php echo $p=="organisation" ? "active" : ""; ?>"><i class="ri-building-line me-2"></i>Organisation</a></li>
                        <li class="slide"><a href="<?php echo "{$base}html/?s=core&ss=admin&p=work_settings" ?>" class="side-menu__item <?php echo $p=="users" ? "active" : ""; ?>"><i class="ri-time-zone-line me-2"></i>Work Time, travel, & Products</a></li>
                        <!-- <li class="slide"><a href="<?php echo "{$base}html/?s=core&ss=admin&p=tax" ?>" class="side-menu__item <?php echo $p=="tax" ? "active" : ""; ?>">Tax</a></li> -->

                    </ul>
                </li>
                <?php
                if($isValidAdmin || $isSuperAdmin ) {?>
                   <!-- Start::slide -->
                   <li class="slide has-sub">
                       <a href="javascript:void(0);" class="side-menu__item">
                           <i class="ri-tools-line side-menu__icon"></i>
                           <span class="side-menu__label">Set Up</span>
                           <i class="ri ri-arrow-right-s-line side-menu__angle"></i>
                       </a>
                       <ul class="slide-menu child1">
                           <li class="slide side-menu__label1"><a href="javascript:void(0)">System Set Up</a></li>

                           <li class="slide"><a href="<?php echo "{$base}html/?s=core&ss=admin&p=home" ?>" class="side-menu__item"><i class="ri-settings-2-line me-2"></i>Organisation Setup</a></li>

                           <!-- <li class="slide"><a href="<?php echo "{$base}html/?s=core&ss=admin&&sss=setup&p=user_upload" ?>" class="side-menu__item">Users</a></li>
                           <li class="slide"><a href="<?php echo "{$base}html/?s=core&ss=admin&&sss=setup&p=admin_setup" ?>" class="side-menu__item">Admins</a></li> -->
                       </ul>
                   </li>
                   <!-- End::slide -->
                <?php } ?>


                <!-- End::slide -->
                <?php
            } ?>
            <!-- Start::slide -->
            <?php if($isHRManager): ?>
                <li class="slide  has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                    <i class="ri-team-line side-menu__icon"></i>
                        <span class="side-menu__label">Employee Management</span>
                        <i class="ri ri-arrow-right-s-line side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide side-menu__label1"><a href="javascript:void(0)">Employee Management </a></li>
                        <li class="slide"><a href="<?php echo "{$base}html/?s=core&ss=admin&p=users"?>" class="side-menu__item"><i class="ri-user-list me-2"></i>Employee List</a></li>
                        <li class="slide"><a href="<?php echo "{$base}html/?s=core&ss=admin&p=home" ?>" class="side-menu__item"><i class="ri-settings-2-line me-2"></i>Organisation Setup</a></li>

                    </ul>
                </li>
                <?php endif; ?>
                    <!-- Start::slide -->
            <li class="slide  has-sub">
                <a href="javascript:void(0);" class="side-menu__item">
                    <i class="ri-user-heart-line side-menu__icon"></i>
                    <span class="side-menu__label">Customer Management</span>
                    <i class="ri ri-arrow-right-s-line side-menu__angle"></i>
                </a>
                <ul class="slide-menu child1">
                    <li class="slide side-menu__label1"><a href="javascript:void(0)"> Client Management </a></li>
                    <li class="slide"><a href="<?php echo "{$base}html/?s=user&ss=clients&p=home"?>" class="side-menu__item"><i class="ri-eye-line me-2"></i>Client overview</a></li>

                </ul>
            </li>
            <!-- End::slide -->

            <li class="slide has-sub">
                <a href="javascript:void(0);" class="side-menu__item">
                    <i class="ri-line-chart-line side-menu__icon"></i>
                    <span class="side-menu__label">Sales </span>
                    <i class="ri ri-arrow-right-s-line side-menu__angle"></i>
                </a>
                <ul class="slide-menu child1">
                    <li class="slide side-menu__label1"><a href="javascript:void(0)">Sales</a></li>
                    <li class="slide"><a href="<?php echo "{$base}html/?s=user&ss=sales&p=home"?>" class="side-menu__item"><i class="ri-dashboard-line me-2"></i>Overview</a></li>
                    <li  class="slide"><a href="<?php echo "{$base}html/?s=user&ss=sales&p=prospects"?>" class="side-menu__item"><i class="ri-user-search-line me-2"></i>Prospecting</a></li>
                    <li class="slide"><a href="<?php echo "{$base}html/?s=user&ss=sales&p=prospect_analytics_dashboard"?>" class="side-menu__item"><i class="ri-pie-chart-line me-2"></i>Prospect Analytics</a></li>
                    <li class="slide"><a href="<?php echo "{$base}html/?s=user&ss=sales&p=proposals"?>" class="side-menu__item"><i class="ri-file-paper-line me-2"></i>Proposals</a></li>
                    <li class="slide"><a href="<?php echo "{$base}html/?s=user&ss=sales&p=sales_analytics_dashboard"?>" class="side-menu__item"><i class="ri-bar-chart-line me-2"></i>Sales dashboard</a></li>
                    <?php
                    if($isAdmin || $isSalesAdmin || $isValidAdmin) {?>
                        <!-- <li class="slide"><a href="<?php echo "{$base}html/?s=user&ss=sales&p=business_development"?>" class="side-menu__item">Business Development</a></li> -->
                        <li class="slide"><a href="<?php echo "{$base}html/?s=user&ss=sales&p=proposals_config"?>" class="side-menu__item"><i class="ri-settings-3-line me-2"></i>Proposals Config</a></li>
                        <!-- <?= "<li class='slide '><a href='{$base}html/?s=core&ss=sales&p=manage_status' class='side-menu__item'>Manage Sales Status</a></li>";?> -->
                        <li class="slide"><a href="<?php echo "{$base}html/?s=user&ss=sales&p=sales_config" ?>" class="side-menu__item"><i class="ri-settings-4-line me-2"></i>Sales Configuration</a></li>
                        <?php
                    }?>
                </ul>
            </li>

            <!-- Start::slide -->
            <li class="slide has-sub">
                <a href="javascript:void(0);" class="side-menu__item">
                    <i class="ri-folder-open-line side-menu__icon"></i>
                    <span class="side-menu__label">Projects</span>
                    <i class="ri ri-arrow-right-s-line side-menu__angle"></i>
                </a>
                <ul class="slide-menu child1">
                    <li class="slide side-menu__label1"><a href="javascript:void(0)">Projects</a></li>
                    <li class="slide"><a href="<?= "{$base}html/?s=user&ss=projects&p=home" ?>" class="side-menu__item"><i class="ri-dashboard-line me-2"></i>Overview</a></li>
                    <?php
                    if($isAdmin ||  $isValidAdmin) {?>
                        <li class="slide"><a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=project_variables" ?>" class="side-menu__item"><i class="ri-code-s-slash-line me-2"></i>Variables</a></li>
                        <?php
                    }?>

                </ul>
            </li>
            <!-- End::slide -->

            <!-- Start::slide -->
            <li class="slide has-sub">
                <a href="javascript:void(0);" class="side-menu__item">
                    <i class="ri-time-line side-menu__icon"></i>
                    <span class="side-menu__label">Time & Attendance</span>
                    <i class="ri ri-arrow-right-s-line side-menu__angle"></i>
                </a>
                <ul class="slide-menu child1">
                    <li class="slide side-menu__label1"><a href="javascript:void(0)">Time & Attendance</a></li>
                    <li class="slide"><a href="<?= "{$base}html/?s=user&ss=time_attendance&p=home" ?>" class="side-menu__item"><i class="ri-dashboard-line me-2"></i>Overview</a></li>
                    <?php
                    if($isAdmin || $isValidAdmin) {?>
                        <!-- <li class="slide"><a href="#" class="side-menu__item">Reports</a></li> -->
                        <?php
                    }?>
                </ul>
            </li>
            <!-- End::slide -->

            <!-- Start::slide - Operational Work (User Section) -->
            <li class="slide has-sub">
                <a href="javascript:void(0);" class="side-menu__item">
                    <i class="ri-repeat-line side-menu__icon"></i>
                    <span class="side-menu__label">Operational Work</span>
                    <i class="ri ri-arrow-right-s-line side-menu__angle"></i>
                </a>
                <ul class="slide-menu child1">
                    <li class="slide side-menu__label1"><a href="javascript:void(0)">Operational Work</a></li>
                    <li class="slide"><a href="<?= "{$base}html/?s=user&ss=operational&p=dashboard" ?>" class="side-menu__item <?= (isset($p) && $p == 'dashboard' && isset($ss) && $ss == 'operational') ? 'active' : '' ?>">
                        <i class="ri-dashboard-line me-2"></i>Dashboard
                    </a></li>
                    <li class="slide"><a href="<?= "{$base}html/?s=user&ss=operational&p=tasks" ?>" class="side-menu__item <?= (isset($p) && $p == 'tasks' && isset($ss) && $ss == 'operational') ? 'active' : '' ?>">
                        <i class="ri-task-line me-2"></i>My Tasks
                    </a></li>
                    <li class="slide"><a href="<?= "{$base}html/?s=user&ss=operational&p=templates" ?>" class="side-menu__item <?= (isset($p) && $p == 'templates' && isset($ss) && $ss == 'operational') ? 'active' : '' ?>">
                        <i class="ri-file-copy-line me-2"></i>Templates
                    </a></li>
                    <li class="slide"><a href="<?= "{$base}html/?s=user&ss=operational&p=projects" ?>" class="side-menu__item <?= (isset($p) && $p == 'projects' && isset($ss) && $ss == 'operational') ? 'active' : '' ?>">
                        <i class="ri-folder-line me-2"></i>Operational Projects
                    </a></li>
                    <li class="slide"><a href="<?= "{$base}html/?s=user&ss=operational&p=capacity" ?>" class="side-menu__item <?= (isset($p) && $p == 'capacity' && isset($ss) && $ss == 'operational') ? 'active' : '' ?>">
                        <i class="ri-bar-chart-box-line me-2"></i>Capacity Planning
                    </a></li>
                    <li class="slide side-menu__label1"><a href="javascript:void(0)">Reports</a></li>
                    <li class="slide"><a href="<?= "{$base}html/?s=user&ss=operational&p=reports_health" ?>" class="side-menu__item <?= (isset($p) && $p == 'reports_health' && isset($ss) && $ss == 'operational') ? 'active' : '' ?>">
                        <i class="ri-heart-pulse-line me-2"></i>Operational Health
                    </a></li>
                    <li class="slide"><a href="<?= "{$base}html/?s=user&ss=operational&p=reports_executive" ?>" class="side-menu__item <?= (isset($p) && $p == 'reports_executive' && isset($ss) && $ss == 'operational') ? 'active' : '' ?>">
                        <i class="ri-line-chart-line me-2"></i>Executive Dashboard
                    </a></li>
                </ul>
            </li>
            <!-- End::slide -->
            <li class="slide has-sub">
                <a href="javascript:void(0);" class="side-menu__item">
                    <i class="ri-calendar-line side-menu__icon"></i>
                    <span class="side-menu__label">Schedule</span>
                    <i class="ri ri-arrow-right-s-line side-menu__angle"></i>
                </a>
                <ul class="slide-menu child1">
                    <li class="slide side-menu__label1"><a href="javascript:void(0)">Schedule </a></li>
                    <li class="slide"><a href="<?= "{$base}html/?s={$s}&ss=schedule&p=task" ?>" class="side-menu__item"><i class="ri-checkbox-line me-2"></i>To-do</a></li>
                    <!-- <li class="slide"><a href="<?= "{$base}html/?s={$s}&ss=schedule&p=to_do_list" ?>" class="side-menu__item">To-do List</a></li> -->
                    <li class="slide"><a href="<?= "{$base}html/?s={$s}&ss=schedule&p=calendar" ?>" class="side-menu__item"><i class="ri-calendar-2-line me-2"></i>Calendar</a></li>
                    <li class="slide"><a href="<?= "{$base}html/?s={$s}&ss=schedule&p=activities_enhanced" ?>" class="side-menu__item"><i class="ri-list-check me-2"></i>Activities Enhanced</a></li>
                    <!-- <li class="slide"><a href="<?= "{$base}html/?s={$s}&ss=schedule&p=task_kanban" ?>" class="side-menu__item">Task Kanban</a></li> -->


                </ul>
            </li>
             <!-- Start::slide - Leave Management (User Section) -->
             <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="ri-calendar-check-line side-menu__icon"></i>
                        <span class="side-menu__label">Leave Management</span>
                        <i class="ri ri-arrow-right-s-line side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide side-menu__label1"><a href="javascript:void(0)">My Leave</a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=user&ss=leave&p=leave_management_enhanced" ?>" class="side-menu__item">
                            <i class="ri-dashboard-line me-2"></i>Dashboard
                        </a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=user&ss=leave&p=apply_leave_workflow" ?>" class="side-menu__item">
                            <i class="ri-calendar-add-line me-2"></i>Apply Leave
                        </a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=user&ss=leave&p=my_leave_usage" ?>" class="side-menu__item">
                            <i class="ri-pie-chart-line me-2"></i>My Leave Usage
                        </a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=user&ss=leave&p=leave_approval" ?>" class="side-menu__item">
                            <i class="ri-checkbox-circle-line me-2"></i>Leave Approvals
                        </a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=user&ss=leave&p=team_calendar" ?>" class="side-menu__item">
                            <i class="ri-team-line me-2"></i>Team Calendar
                        </a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=user&ss=leave&p=leave_calendar_interactive" ?>" class="side-menu__item">
                            <i class="ri-calendar-2-line me-2"></i>Interactive Calendar
                        </a></li>
                        <?php if($isHRManager || $isAdmin || $isValidAdmin): ?>
                        <li class="slide side-menu__label1"><a href="javascript:void(0)">Analytics & Reports</a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=user&ss=leave&p=leave_analytics" ?>" class="side-menu__item">
                            <i class="ri-bar-chart-box-line me-2"></i>Leave Analytics
                        </a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <!-- End::slide -->

                <!-- Start::slide - Leave Administration (Admin Section) -->
                <?php if($isAdmin || $isValidAdmin || $isHRManager): ?>
                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="ri-admin-line side-menu__icon"></i>
                        <span class="side-menu__label">Leave Administration</span>
                        <i class="ri ri-arrow-right-s-line side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <!-- Dashboard -->
                        <li class="slide side-menu__label1"><a href="javascript:void(0)">Overview</a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=leave&p=dashboard" ?>" class="side-menu__item <?= (isset($p) && $p == 'dashboard' && isset($ss) && $ss == 'leave') ? 'active' : '' ?>">
                            <i class="ri-dashboard-2-line me-2"></i>Dashboard

                        </a></li>

                        <!-- Leave Types -->
                        <li class="slide side-menu__label1"><a href="javascript:void(0)">Leave Types & Policies</a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=leave&p=leave_types" ?>" class="side-menu__item <?= (isset($p) && $p == 'leave_types' && isset($ss) && $ss == 'leave') ? 'active' : '' ?>">
                            <i class="ri-calendar-2-line me-2"></i>Leave Types

                        </a></li>

                        <!-- Leave Policies -->
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item <?= (isset($p) && $p == 'leave_policies' && isset($ss) && $ss == 'leave') ? 'active' : '' ?>">
                                <i class="ri-file-list-3-line me-2"></i>Leave Policies
                                <i class="ri ri-arrow-right-s-line side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child2">
                                <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=leave&p=leave_policies&action=list" ?>" class="side-menu__item">
                                    <i class="ri-list-check me-2"></i>Policy List

                                </a></li>
                                <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=leave&p=leave_policies&action=create" ?>" class="side-menu__item">
                                    <i class="ri-add-circle-line me-2"></i>Create/Edit Policy

                                </a></li>
                            </ul>
                        </li>

                        <!-- Accrual Policies -->
                        <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=leave&p=accumulation_policies" ?>" class="side-menu__item <?= (isset($p) && $p == 'accumulation_policies' && isset($ss) && $ss == 'leave') ? 'active' : '' ?>">
                            <i class="ri-refresh-line me-2"></i>Accrual Policies

                        </a></li>

                        <!-- Holidays & Calendar -->
                        <li class="slide side-menu__label1"><a href="javascript:void(0)">Holidays & Calendar</a></li>
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item <?= (isset($p) && in_array($p, ['holidays', 'working_weekends', 'leave_periods']) && isset($ss) && $ss == 'leave') ? 'active' : '' ?>">
                                <i class="ri-calendar-line me-2"></i>Holidays & Calendar
                                <i class="ri ri-arrow-right-s-line side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child2">
                                <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=leave&p=holidays" ?>" class="side-menu__item <?= (isset($p) && $p == 'holidays' && isset($ss) && $ss == 'leave') ? 'active' : '' ?>">
                                    <i class="ri-sun-line me-2"></i>Holidays

                                </a></li>
                                <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=leave&p=working_weekends" ?>" class="side-menu__item <?= (isset($p) && $p == 'working_weekends' && isset($ss) && $ss == 'leave') ? 'active' : '' ?>">
                                    <i class="ri-calendar-check-line me-2"></i>Working Weekends
                                </a></li>
                                <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=leave&p=leave_periods" ?>" class="side-menu__item <?= (isset($p) && $p == 'leave_periods' && isset($ss) && $ss == 'leave') ? 'active' : '' ?>">
                                    <i class="ri-calendar-event-line me-2"></i>Leave Periods

                                </a></li>
                            </ul>
                        </li>

                        <!-- Workflows -->
                        <li class="slide side-menu__label1"><a href="javascript:void(0)">Workflows</a></li>
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item <?= (isset($p) && in_array($p, ['approval_workflows']) && isset($ss) && $ss == 'leave') ? 'active' : '' ?>">
                                <i class="ri-flow-chart me-2"></i>Workflows
                                <i class="ri ri-arrow-right-s-line side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child2">
                                <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=leave&p=approval_workflows" ?>" class="side-menu__item <?= (isset($p) && $p == 'approval_workflows' && isset($ss) && $ss == 'leave') ? 'active' : '' ?>">
                                    <i class="ri-checkbox-circle-line me-2"></i>Approval Workflows
                                </a></li>
                                <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=leave&p=approval_workflows&action=delegation" ?>" class="side-menu__item <?= (isset($p) && $p == 'approval_workflows' && isset($ss) && $ss == 'leave' && isset($_GET['action']) && $_GET['action'] == 'delegation') ? 'active' : '' ?>">
                                    <i class="ri-user-settings-line me-2"></i>Delegation Management
                                </a></li>
                            </ul>
                        </li>

                        <!-- Reports & Analytics -->
                        <li class="slide side-menu__label1"><a href="javascript:void(0)">Reports & Analytics</a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=user&ss=leave&p=leave_analytics" ?>" class="side-menu__item <?= (isset($p) && $p == 'leave_analytics' && isset($ss) && $ss == 'leave') ? 'active' : '' ?>">
                            <i class="ri-bar-chart-box-line me-2"></i>Leave Analytics Dashboard
                        </a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=leave&p=reports" ?>" class="side-menu__item <?= (isset($p) && $p == 'reports' && isset($ss) && $ss == 'leave') ? 'active' : '' ?>">
                            <i class="ri-bar-chart-line me-2"></i>Reports & Analytics
                        </a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=leave&p=leave_balances" ?>" class="side-menu__item <?= (isset($p) && $p == 'leave_balances' && isset($ss) && $ss == 'leave') ? 'active' : '' ?>">
                            <i class="ri-wallet-line me-2"></i>Leave Balances
                        </a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=leave&p=audit_log" ?>" class="side-menu__item <?= (isset($p) && $p == 'audit_log' && isset($ss) && $ss == 'leave') ? 'active' : '' ?>">
                            <i class="ri-history-line me-2"></i>Audit Log
                        </a></li>
                    </ul>
                </li>
                <?php endif; ?>
                <!-- End::slide -->

                <!-- Start::slide - Operational Work Administration (Admin Section) -->
                <?php if($isAdmin || $isValidAdmin): ?>
                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="ri-settings-4-line side-menu__icon"></i>
                        <span class="side-menu__label">Operational Work Admin</span>
                        <i class="ri ri-arrow-right-s-line side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <!-- Dashboard -->
                        <li class="slide side-menu__label1"><a href="javascript:void(0)">Overview</a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=operational&p=dashboard" ?>" class="side-menu__item <?= (isset($p) && $p == 'dashboard' && isset($ss) && $ss == 'operational') ? 'active' : '' ?>">
                            <i class="ri-dashboard-2-line me-2"></i>Dashboard
                        </a></li>

                        <!-- Process Management -->
                        <li class="slide side-menu__label1"><a href="javascript:void(0)">Process Management</a></li>
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item <?= (isset($p) && in_array($p, ['processes', 'activities', 'tasks']) && isset($ss) && $ss == 'operational') ? 'active' : '' ?>">
                                <i class="ri-file-list-3-line me-2"></i>Processes & Activities
                                <i class="ri ri-arrow-right-s-line side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child2">
                                <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=operational&p=processes" ?>" class="side-menu__item <?= (isset($p) && $p == 'processes' && isset($ss) && $ss == 'operational') ? 'active' : '' ?>">
                                    <i class="ri-flow-chart-line me-2"></i>Processes
                                </a></li>
                                <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=operational&p=activities" ?>" class="side-menu__item <?= (isset($p) && $p == 'activities' && isset($ss) && $ss == 'operational') ? 'active' : '' ?>">
                                    <i class="ri-list-check me-2"></i>Activities
                                </a></li>
                                <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=operational&p=tasks" ?>" class="side-menu__item <?= (isset($p) && $p == 'tasks' && isset($ss) && $ss == 'operational') ? 'active' : '' ?>">
                                    <i class="ri-task-line me-2"></i>Tasks
                                </a></li>
                            </ul>
                        </li>

                        <!-- Workflow Management -->
                        <li class="slide side-menu__label1"><a href="javascript:void(0)">Workflow Management</a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=operational&p=workflows" ?>" class="side-menu__item <?= (isset($p) && $p == 'workflows' && isset($ss) && $ss == 'operational') ? 'active' : '' ?>">
                            <i class="ri-flow-chart me-2"></i>Workflows
                        </a></li>

                        <!-- SOP Management -->
                        <li class="slide side-menu__label1"><a href="javascript:void(0)">SOP Management</a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=operational&p=sops" ?>" class="side-menu__item <?= (isset($p) && $p == 'sops' && isset($ss) && $ss == 'operational') ? 'active' : '' ?>">
                            <i class="ri-file-text-line me-2"></i>Standard Operating Procedures
                        </a></li>

                        <!-- Template Management -->
                        <li class="slide side-menu__label1"><a href="javascript:void(0)">Template Management</a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=operational&p=templates" ?>" class="side-menu__item <?= (isset($p) && $p == 'templates' && isset($ss) && $ss == 'operational') ? 'active' : '' ?>">
                            <i class="ri-file-copy-line me-2"></i>Task Templates
                        </a></li>

                        <!-- Process Optimization -->
                        <li class="slide side-menu__label1"><a href="javascript:void(0)">Process Optimization</a></li>
                        <li class="slide has-sub">
                            <a href="javascript:void(0);" class="side-menu__item <?= (isset($p) && in_array($p, ['processes_model', 'processes_simulate', 'processes_optimize']) && isset($ss) && $ss == 'operational') ? 'active' : '' ?>">
                                <i class="ri-line-chart-line me-2"></i>Process Modeling
                                <i class="ri ri-arrow-right-s-line side-menu__angle"></i>
                            </a>
                            <ul class="slide-menu child2">
                                <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=operational&p=processes_model" ?>" class="side-menu__item <?= (isset($p) && $p == 'processes_model' && isset($ss) && $ss == 'operational') ? 'active' : '' ?>">
                                    <i class="ri-node-tree me-2"></i>Process Modeler
                                </a></li>
                                <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=operational&p=processes_simulate" ?>" class="side-menu__item <?= (isset($p) && $p == 'processes_simulate' && isset($ss) && $ss == 'operational') ? 'active' : '' ?>">
                                    <i class="ri-play-circle-line me-2"></i>Simulation
                                </a></li>
                                <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=operational&p=processes_optimize" ?>" class="side-menu__item <?= (isset($p) && $p == 'processes_optimize' && isset($ss) && $ss == 'operational') ? 'active' : '' ?>">
                                    <i class="ri-lightbulb-line me-2"></i>Optimization
                                </a></li>
                            </ul>
                        </li>

                        <!-- Assignments -->
                        <li class="slide side-menu__label1"><a href="javascript:void(0)">Configuration</a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=operational&p=assignments" ?>" class="side-menu__item <?= (isset($p) && $p == 'assignments' && isset($ss) && $ss == 'operational') ? 'active' : '' ?>">
                            <i class="ri-user-settings-line me-2"></i>Task Assignments
                        </a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=operational&p=function_heads" ?>" class="side-menu__item <?= (isset($p) && $p == 'function_heads' && isset($ss) && $ss == 'operational') ? 'active' : '' ?>">
                            <i class="ri-team-line me-2"></i>Function Heads
                        </a></li>
                    </ul>
                </li>
                <?php endif; ?>
                <!-- End::slide -->

                <!-- Start::slide - Goals & Performance Administration (Admin Section) -->
                <?php if($isAdmin || $isValidAdmin): ?>
                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                    <i class="ri-settings-4-line side-menu__icon"></i>
                        <span class="side-menu__label">Goals & Performance Admin</span>
                        <i class="ri ri-arrow-right-s-line side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <!-- Dashboard -->
                        <li class="slide side-menu__label1"><a href="javascript:void(0)">Overview</a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=goals&p=dashboard" ?>" class="side-menu__item <?= (isset($p) && $p == 'dashboard' && isset($ss) && $ss == 'goals') ? 'active' : '' ?>">
                            <i class="ri-dashboard-2-line me-2"></i>Dashboard
                        </a></li>

                        <!-- Goal Management -->
                        <li class="slide side-menu__label1"><a href="javascript:void(0)">Goal Management</a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=goals&p=library" ?>" class="side-menu__item <?= (isset($p) && $p == 'library' && isset($ss) && $ss == 'goals') ? 'active' : '' ?>">
                            <i class="ri-book-open-line me-2"></i>Goal Library
                        </a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=goals&p=cascade" ?>" class="side-menu__item <?= (isset($p) && $p == 'cascade' && isset($ss) && $ss == 'goals') ? 'active' : '' ?>">
                            <i class="ri-flow-chart-line me-2"></i>Cascade Management
                        </a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=goals&p=evaluation_config" ?>" class="side-menu__item <?= (isset($p) && $p == 'evaluation_config' && isset($ss) && $ss == 'goals') ? 'active' : '' ?>">
                            <i class="ri-settings-4-line me-2"></i>Evaluation Config
                        </a></li>

                        <!-- Analytics & Reporting -->
                        <li class="slide side-menu__label1"><a href="javascript:void(0)">Analytics & Reporting</a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=goals&p=reports" ?>" class="side-menu__item <?= (isset($p) && $p == 'reports' && isset($ss) && $ss == 'goals') ? 'active' : '' ?>">
                            <i class="ri-bar-chart-box-line me-2"></i>Reports & Analytics
                        </a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=goals&p=strategy_map" ?>" class="side-menu__item <?= (isset($p) && $p == 'strategy_map' && isset($ss) && $ss == 'goals') ? 'active' : '' ?>">
                            <i class="ri-map-2-line me-2"></i>Strategy Map
                        </a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=admin&ss=goals&p=ahp_interface" ?>" class="side-menu__item <?= (isset($p) && $p == 'ahp_interface' && isset($ss) && $ss == 'goals') ? 'active' : '' ?>">
                            <i class="ri-node-tree me-2"></i>AHP Interface
                        </a></li>
                    </ul>
                </li>
                <?php endif; ?>
                <!-- End::slide -->

                <!-- Start::slide - Goals & Performance (User Section) -->
                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                    <i class="ri-settings-4-line side-menu__icon"></i>
                        <span class="side-menu__label">Goals & Performance</span>
                        <i class="ri ri-arrow-right-s-line side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide side-menu__label1"><a href="javascript:void(0)">My Goals</a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=user&ss=goals&p=dashboard" ?>" class="side-menu__item <?= (isset($p) && $p == 'dashboard' && isset($ss) && $ss == 'goals') ? 'active' : '' ?>">
                            <i class="ri-dashboard-line me-2"></i>Dashboard
                        </a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=user&ss=goals&p=goal_detail" ?>" class="side-menu__item <?= (isset($p) && $p == 'goal_detail' && isset($ss) && $ss == 'goals') ? 'active' : '' ?>">
                            <i class="ri-file-list-3-line me-2"></i>My Goals
                        </a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=user&ss=goals&p=evaluations" ?>" class="side-menu__item <?= (isset($p) && $p == 'evaluations' && isset($ss) && $ss == 'goals') ? 'active' : '' ?>">
                            <i class="ri-star-line me-2"></i>Evaluations
                        </a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=user&ss=goals&p=matrix_team" ?>" class="side-menu__item <?= (isset($p) && $p == 'matrix_team' && isset($ss) && $ss == 'goals') ? 'active' : '' ?>">
                            <i class="ri-team-line me-2"></i>Matrix Team
                        </a></li>
                        <li class="slide side-menu__label1"><a href="javascript:void(0)">Settings</a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=user&ss=goals&p=settings" ?>" class="side-menu__item <?= (isset($p) && $p == 'settings' && isset($ss) && $ss == 'goals') ? 'active' : '' ?>">
                            <i class="ri-settings-3-line me-2"></i>Automation Settings
                        </a></li>
                    </ul>
                </li>
                <!-- End::slide -->

                <!-- Start::slide -->
                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="ri-bar-chart-line side-menu__icon"></i>
                        <span class="side-menu__label">Reports</span>
                        <i class="ri ri-arrow-right-s-line side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide side-menu__label1"><a href="javascript:void(0)">Reports</a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=user&ss=reporting&p=home" ?>" class="side-menu__item"><i class="ri-dashboard-line me-2"></i>Overview</a></li>
                        <li class="slide"><a href="leaflet-maps.html" class="side-menu__item"><i class="ri-gallery-line me-2"></i>Reports Gallery</a></li>
                    </ul>
                </li>
                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="ri-money-dollar-circle-line side-menu__icon"></i>
                        <span class="side-menu__label">Invoicing</span>
                        <i class="ri ri-arrow-right-s-line side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide side-menu__label1"><a href="javascript:void(0)">Invoicing </a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=user&ss=invoices&p=list" ?>" class="side-menu__item <?php echo ($p=="list" && $ss=="invoices") ? "active" : ""; ?>"><i class="ri-file-list-3-line me-2"></i>All Invoices</a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=user&ss=invoices&p=create" ?>" class="side-menu__item <?php echo ($p=="create" && $ss=="invoices") ? "active" : ""; ?>"><i class="ri-add-circle-line me-2"></i>Create Invoice</a></li>
                        <?php if($isAdmin || $isValidAdmin): ?>
                        <li class="slide"><a href="<?= "{$base}html/?s=user&ss=invoices&p=templates" ?>" class="side-menu__item <?php echo ($p=="templates" && $ss=="invoices") ? "active" : ""; ?>"><i class="ri-file-copy-line me-2"></i>Invoice Templates</a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=user&ss=invoices&p=reports" ?>" class="side-menu__item <?php echo ($p=="reports" && $ss=="invoices") ? "active" : ""; ?>"><i class="ri-bar-chart-line me-2"></i>Reports</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
                <!-- End::slide -->
             <?php
             if($isValidAdmin) { ?>


            <!-- Start::slide -->


                <!-- Start::slide -->
                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="ri-bar-chart-line side-menu__icon"></i>
                        <span class="side-menu__label">Reports</span>
                        <i class="ri ri-arrow-right-s-line side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide side-menu__label1"><a href="javascript:void(0)">Reports</a></li>
                        <li class="slide"><a href="google-maps.html" class="side-menu__item"><i class="ri-dashboard-line me-2"></i>Overview</a></li>
                        <!-- <li class="slide"><a href="leaflet-maps.html" class="side-menu__item">Reports Gallery</a></li>                         -->
                    </ul>
                </li>
                <!-- End::slide -->

                <!-- Start::slide -->
                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="ri-customer-service-2-line side-menu__icon"></i>
                        <span class="side-menu__label">Support Desk</span>
                        <i class="ri ri-arrow-right-s-line side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide side-menu__label1"><a href="javascript:void(0)">Support Desk</a></li>
                        <li class="slide"><a href="<?php echo "{$base}html/?s=tax&ss=admin&p=data_upload" ?>" class="side-menu__item <?php echo $p=="user_roles" ? "active" : ""; ?>"><i class="ri-dashboard-line me-2"></i>Overview</a></li>
                        <li class="slide"><a href="<?php echo "{$base}html/?s=tax&ss=admin&p=adjustment_config" ?>" class="side-menu__item <?php echo $p=="jobs" ? "active" : ""; ?>"><i class="ri-ticket-line me-2"></i>My Tickets</a></li>
                        <li class="slide"><a href="<?php echo "{$base}html/?s=tax&ss=admin&p=computation" ?>" class="side-menu__item <?php echo $p=="organisation" ? "active" : ""; ?>"><i class="ri-bar-chart-line me-2"></i>Ticket Reports</a></li>
                    </ul>
                </li>
                <?php
            } ?>
                <!-- End::slide -->
            </ul>
            <div class="slide-right" id="slide-right">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
                </svg>
            </div>
        </nav>
        <!-- End::nav -->
</div>
<!-- End::main-sidebar -->

</aside>
<!-- End::app-sidebar -->