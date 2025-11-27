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
                            echo "<li class='slide'><a href='{$base}html/?s=core&ss=admin&p=home' class='side-menu__item'>Organisation</a></li>";?>
                             <li class="slide"><a href="<?php echo "{$base}html/?s=core&ss=admin&p=user_roles" ?>" class="side-menu__item <?php echo $p=="user_roles" ? "active" : ""; ?>">Manage User Roles</a></li>
                             <li class="slide"><a href="<?php echo "{$base}html/?s=core&ss=admin&p=license_types" ?>" class="side-menu__item <?php echo $p=="license_types" ? "active" : ""; ?>"><i class="fas fa-certificate me-2"></i>License Types</a></li>
                             <li class="slide"><a href="<?php echo "{$base}html/?s=core&ss=admin&p=role_types" ?>" class="side-menu__item <?php echo $p=="role_types" ? "active" : ""; ?>"><i class="fas fa-layer-group me-2"></i>Role Types</a></li>
                             <li class="slide"><a href="<?php echo "{$base}html/?s=core&ss=admin&p=role_levels" ?>" class="side-menu__item <?php echo $p=="role_levels" ? "active" : ""; ?>"><i class="fas fa-sitemap me-2"></i>Role Levels</a></li>
                             <?php
                        } ?>
                        <li class="slide"><a href="<?php echo "{$base}html/?s=core&ss=admin&p=jobs" ?>" class="side-menu__item <?php echo $p=="jobs" ? "active" : ""; ?>">Jobs</a></li>
                        <li class="slide"><a href="<?php echo "{$base}html/?s=core&ss=admin&p=home" ?>" class="side-menu__item <?php echo $p=="organisation" ? "active" : ""; ?>">Organisation</a></li>
                        <li class="slide"><a href="<?php echo "{$base}html/?s=core&ss=admin&p=work_settings" ?>" class="side-menu__item <?php echo $p=="users" ? "active" : ""; ?>">Work Time, travel, & Products</a></li>
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

                           <li class="slide"><a href="<?php echo "{$base}html/?s=core&ss=admin&p=home" ?>" class="side-menu__item">Organisation Setup</a></li>

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
                        <li class="slide"><a href="<?php echo "{$base}html/?s=core&ss=admin&p=users"?>" class="side-menu__item">Employee List</a></li>
                        <li class="slide"><a href="<?php echo "{$base}html/?s=core&ss=admin&p=home" ?>" class="side-menu__item">Organisation Setup</a></li>

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
                    <li class="slide"><a href="<?php echo "{$base}html/?s=user&ss=clients&p=home"?>" class="side-menu__item">Client overview</a></li>

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
                    <li class="slide"><a href="<?php echo "{$base}html/?s=user&ss=sales&p=home"?>" class="side-menu__item">Overview</a></li>
                    <li class="slide"><a href="<?php echo "{$base}html/?s=user&ss=sales&p=proposals"?>" class="side-menu__item">Proposals</a></li>
                    <li class="slide"><a href="<?php echo "{$base}html/?s=user&ss=sales&p=sales_analytics_dashboard"?>" class="side-menu__item">Sales dashboard</a></li>
                    <?php
                    if($isAdmin || $isSalesAdmin || $isValidAdmin) {?>
                        <!-- <li class="slide"><a href="<?php echo "{$base}html/?s=user&ss=sales&p=business_development"?>" class="side-menu__item">Business Development</a></li> -->
                        <li class="slide"><a href="<?php echo "{$base}html/?s=user&ss=sales&p=proposals_config"?>" class="side-menu__item">Proposals Config</a></li>
                        <!-- <?= "<li class='slide '><a href='{$base}html/?s=core&ss=sales&p=manage_status' class='side-menu__item'>Manage Sales Status</a></li>";?> -->
                        <li class="slide"><a href="<?php echo "{$base}html/?s=user&ss=sales&p=sales_config" ?>" class="side-menu__item">Sales Configuration</a></li>
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
                    <li class="slide"><a href="<?= "{$base}html/?s=user&ss=projects&p=home" ?>" class="side-menu__item">Overview</a></li>
                    <?php
                    if($isAdmin ||  $isValidAdmin) {?>
                        <li class="slide"><a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=project_variables" ?>" class="side-menu__item">Variables</a></li>
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
                    <li class="slide"><a href="<?= "{$base}html/?s=user&ss=time_attendance&p=home" ?>" class="side-menu__item">Overview</a></li>
                    <?php
                    if($isAdmin || $isValidAdmin) {?>
                        <!-- <li class="slide"><a href="#" class="side-menu__item">Reports</a></li> -->
                        <?php
                    }?>
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
                    <li class="slide"><a href="<?= "{$base}html/?s={$s}&ss=schedule&p=task" ?>" class="side-menu__item">To-do</a></li>
                    <!-- <li class="slide"><a href="<?= "{$base}html/?s={$s}&ss=schedule&p=to_do_list" ?>" class="side-menu__item">To-do List</a></li> -->
                    <li class="slide"><a href="<?= "{$base}html/?s={$s}&ss=schedule&p=calendar" ?>" class="side-menu__item">Calendar</a></li>
                    <li class="slide"><a href="<?= "{$base}html/?s={$s}&ss=schedule&p=activities_enhanced" ?>" class="side-menu__item">Activities Enhanced</a></li>
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

                <!-- Start::slide -->
                <li class="slide has-sub">
                    <a href="javascript:void(0);" class="side-menu__item">
                        <i class="ri-bar-chart-line side-menu__icon"></i>
                        <span class="side-menu__label">Reports</span>
                        <i class="ri ri-arrow-right-s-line side-menu__angle"></i>
                    </a>
                    <ul class="slide-menu child1">
                        <li class="slide side-menu__label1"><a href="javascript:void(0)">Reports</a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=user&ss=reporting&p=home" ?>" class="side-menu__item">Overview</a></li>
                        <li class="slide"><a href="leaflet-maps.html" class="side-menu__item">Reports Gallery</a></li>
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
                        <li class="slide"><a href="<?= "{$base}html/?s=user&ss=invoices&p=list" ?>" class="side-menu__item <?php echo ($p=="list" && $ss=="invoices") ? "active" : ""; ?>">All Invoices</a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=user&ss=invoices&p=create" ?>" class="side-menu__item <?php echo ($p=="create" && $ss=="invoices") ? "active" : ""; ?>">Create Invoice</a></li>
                        <?php if($isAdmin || $isValidAdmin): ?>
                        <li class="slide"><a href="<?= "{$base}html/?s=user&ss=invoices&p=templates" ?>" class="side-menu__item <?php echo ($p=="templates" && $ss=="invoices") ? "active" : ""; ?>">Invoice Templates</a></li>
                        <li class="slide"><a href="<?= "{$base}html/?s=user&ss=invoices&p=reports" ?>" class="side-menu__item <?php echo ($p=="reports" && $ss=="invoices") ? "active" : ""; ?>">Reports</a></li>
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
                        <li class="slide"><a href="google-maps.html" class="side-menu__item">Overview</a></li>
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
                        <li class="slide"><a href="<?php echo "{$base}html/?s=tax&ss=admin&p=data_upload" ?>" class="side-menu__item <?php echo $p=="user_roles" ? "active" : ""; ?>">Overview</a></li>
                        <li class="slide"><a href="<?php echo "{$base}html/?s=tax&ss=admin&p=adjustment_config" ?>" class="side-menu__item <?php echo $p=="jobs" ? "active" : ""; ?>">My Tickets</a></li>
                        <li class="slide"><a href="<?php echo "{$base}html/?s=tax&ss=admin&p=computation" ?>" class="side-menu__item <?php echo $p=="organisation" ? "active" : ""; ?>">Ticket Reports</a></li>
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