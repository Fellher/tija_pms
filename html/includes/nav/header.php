<?php include "includes/scripts/home/style_switcher.php" ?>
<div class="page">
         <!-- app-header -->
         <header class="app-header">

            <!-- Start::main-header-container -->
            <div class="main-header-container container-fluid">

                <!-- Start::header-content-left -->
                <div class="header-content-left">

                    <!-- Start::header-element -->
                    <div class="header-element">
                        <div class="horizontal-logo">
                            <a href="index.html" class="header-logo">
                                <img src="<?php echo $base ?>assets/img/brand-logos/desktop-logo.png" alt="logo" class="desktop-logo">
                                <img src="<?php echo $base ?>assets/img/brand-logos/toggle-logo.png" alt="logo" class="toggle-logo">
                                <img src="<?php echo $base ?>assets/img/brand-logos/desktop-dark.png" alt="logo" class="desktop-dark">
                                <img src="<?php echo $base ?>assets/img/brand-logos/toggle-dark.png" alt="logo" class="toggle-dark">
                                <img src="<?php echo $base ?>assets/img/brand-logos/desktop-white.png" alt="logo" class="desktop-white">
                                <img src="<?php echo $base ?>assets/img/brand-logos/toggle-white.png" alt="logo" class="toggle-white">
                            </a>
                        </div>
                    </div>
                    <!-- End::header-element -->
                    <!-- Start::header-element -->
                    <div class="header-element">
                        <!-- Start::header-link -->
                        <div class="">
                            <a class="sidebar-toggle sidemenu-toggle header-link" data-bs-toggle="sidebar" href="javascript:void(0);">
                              <span class="sr-only">Toggle Navigation</span>
                              <i class="ri-arrow-right-circle-line header-icon"></i>
                            </a>
                          </div>
                        <a aria-label="Hide Sidebar" class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle d-none" data-bs-toggle="sidebar" href="javascript:void(0);"><span></span></a>
                        <!-- End::header-link -->
                    </div>
                    <!-- End::header-element -->


                </div>
                <!-- End::header-content-left -->

                <!-- Start::header-content-right -->
                <div class="header-content-right">


                    <!-- Start::header-element -->
                    <div class="header-element country-selector d-none">
                        <!-- Start::header-link|dropdown-toggle -->
                        <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-auto-close="outside" data-bs-toggle="dropdown">
                            <img src="<?php echo "{$base}assets/" ?>img/flags/united-kingdom.png" alt="img" class="rounded-circle header-link-icon">
                        </a>
                        <!-- End::header-link|dropdown-toggle -->
                        <ul class="main-header-dropdown dropdown-menu dropdown-menu-end" data-popper-placement="none">
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="javascript:void(0);">
                                    <span class=" lh-1 me-2">
                                        <img src="<?php echo "{$base}assets/" ?>img/flags/10.png" alt="img">
                                    </span>
                                    English
                                </a>
                            </li>
                             <li>
                                <a class="dropdown-item d-flex align-items-center" href="javascript:void(0);">
                                    <span class=" lh-1 me-2">
                                        <img src="<?php echo "{$base}assets/" ?>img/flags/1.png" alt="img" >
                                    </span>
                                    Spanish
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="javascript:void(0);">
                                    <span class=" lh-1 me-2">
                                        <img src="<?php echo "{$base}assets/" ?>img/flags/2.png" alt="img" >
                                    </span>
                                    French
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="javascript:void(0);">
                                    <span class=" lh-1 me-2">
                                        <img src="<?php echo "{$base}assets/" ?>img/flags/3.png" alt="img" >
                                    </span>
                                    German
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="javascript:void(0);">
                                    <span class=" lh-1 me-2">
                                        <img src="<?php echo "{$base}assets/" ?>img/flags/4.png" alt="img" >
                                    </span>
                                    Italian
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="javascript:void(0);">
                                    <span class=" lh-1 me-2">
                                        <img src="<?php echo "{$base}assets/" ?>img/flags/5.png" alt="img" >
                                    </span>
                                    Russian
                                </a>
                            </li>
                        </ul>
                    </div>
                    <!-- End::header-element -->

                    <!-- Start::header-element -->
                    <div class="header-element header-search d-none">
                        <!-- Start::header-link -->
                        <a href="javascript:void(0);" class="header-link" data-bs-toggle="modal" data-bs-target="#searchModal">
                            <i class="ri-search-2-line header-link-icon"></i>
                        </a>
                        <!-- End::header-link -->
                    </div>
                    <!-- End::header-element -->

                    <!-- Start::header-element -->
                    <div class="header-element header-theme-mode d-none">
                        <!-- Start::header-link|layout-setting -->
                        <a href="javascript:void(0);" class="header-link layout-setting">
                            <span class="light-layout">
                                <!-- Start::header-link-icon -->
                                <i class="ri-moon-line header-link-icon"></i>
                                <!-- End::header-link-icon -->
                            </span>
                            <span class="dark-layout">
                                <!-- Start::header-link-icon -->
                                <i class="ri-sun-line header-link-icon"></i>
                                <!-- End::header-link-icon -->
                            </span>
                        </a>
                        <!-- End::header-link|layout-setting -->
                    </div>
                    <!-- End::header-element -->

                    <!-- Start::header-element -->
                    <div class="header-element header-fullscreen">
                        <!-- Start::header-link -->
                        <a onclick="openFullscreen();" href="javascript:void(0);" class="header-link">
                            <i class="ri-fullscreen-line full-screen-open header-link-icon"></i>
                            <i class="ri-fullscreen-line full-screen-close header-link-icon d-none"></i>
                        </a>
                        <!-- End::header-link -->
                    </div>
                    <!-- End::header-element -->

                    <!-- Start::header-element -->
                    <div class="header-element cart-dropdown d-none">
                        <!-- Start::header-link|dropdown-toggle -->
                        <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-auto-close="outside" data-bs-toggle="dropdown">
                            <i class="ri-shopping-basket-line  header-link-icon"></i>
                            <span class="badge bg-danger rounded-pill header-icon-badge" id="cart-icon-badge">4</span>
                        </a>

                        <!-- End::header-link|dropdown-toggle -->
                        <!-- Start::main-header-dropdown -->
                        <div class="main-header-dropdown dropdown-menu dropdown-menu-end d-none" data-popper-placement="none">
                            <div class="header-dropdown bg-primary text-fixed-white rounded-top">
                                <div class="d-flex align-items-center justify-content-between">
                                    <p class="mb-0 fs-15 fw-semibold">Shopping Cart</p>
                                    <span class="badge badge-light-2" id="cart-data">4 Items</span>
                                </div>
                            </div>
                            <div><hr class="dropdown-divider d-none"></div>
                                <ul class="list-unstyled mb-0" id="header-cart-items-scroll">
                                    <li class="dropdown-item border-bottom-0">
                                        <div class="d-flex align-items-start cart-dropdown-item">
                                            <img src="<?php echo "{$base}assets/" ?>img/ecommerce/products/1.png" alt="img" class="avatar avatar  br-5 me-3 p-2 bg-gray-100">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-start justify-content-between mb-0">
                                                    <div class="mb-0 fs-14 text-default fw-medium">
                                                        <a href="cart.html">Black Heals For Women</a>
                                                    </div>
                                                    <div>
                                                        <a href="javascript:void(0);" class="header-cart-remove float-end dropdown-item-close"><i class="ri-close-circle-line"></i></a>
                                                    </div>
                                                </div>
                                                <div class="min-w-fit-content d-flex align-items-start justify-content-between">
                                                    <ul class="header-product-item d-flex">
                                                        <li>$699</li>
                                                        <li class="text-decoration-line-through text-muted">$999</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="dropdown-item border-bottom-0">
                                        <div class="d-flex align-items-start cart-dropdown-item">
                                            <img src="<?php echo "{$base}assets/" ?>img/ecommerce/products/2.png" alt="img" class="avatar avatar  br-5 me-3 p-2 bg-gray-100">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-start justify-content-between mb-0">
                                                    <div class="mb-0 fs-14 text-default fw-medium">
                                                        <a href="cart.html">Tshirt For Men</a>
                                                    </div>
                                                    <div>
                                                        <a href="javascript:void(0);" class="header-cart-remove float-end dropdown-item-close"><i class="ri-close-circle-line"></i></a>
                                                    </div>
                                                </div>
                                                <div class="min-w-fit-content d-flex align-items-start justify-content-between">
                                                    <ul class="header-product-item">
                                                        <li>$245</li>
                                                        <li><span class="text-decoration-line-through text-muted">$599</span></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="dropdown-item border-bottom-0">
                                        <div class="d-flex align-items-start cart-dropdown-item">
                                            <img src="<?php echo "{$base}"?>assets/img/ecommerce/products/9.png" alt="img" class="avatar avatar  br-5 me-3 p-2 bg-gray-100">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-start justify-content-between mb-0">
                                                    <div class="mb-0 fs-14 text-default fw-medium">
                                                        <a href="cart.html">Travel Bag For Womens</a>
                                                    </div>
                                                    <div>
                                                        <a href="javascript:void(0);" class="header-cart-remove float-end dropdown-item-close"><i class="ri-close-circle-line"></i></a>
                                                    </div>
                                                </div>
                                                <div class="min-w-fit-content d-flex align-items-start justify-content-between">
                                                    <ul class="header-product-item d-flex">
                                                        <li>$299</li>
                                                        <li class="text-decoration-line-through text-muted">$399</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="dropdown-item border-bottom-0">
                                        <div class="d-flex align-items-start cart-dropdown-item">
                                            <img src="<?php echo "{$base}"?>assets/img/ecommerce/products/10.png" alt="img" class="avatar avatar  br-5 me-3 p-2 bg-gray-100">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-start justify-content-between mb-0">
                                                    <div class="mb-0 fs-14 text-default fw-medium">
                                                        <a href="cart.html">Leather Wallet For Grils</a>
                                                    </div>
                                                    <div>
                                                        <a href="javascript:void(0);" class="header-cart-remove float-end dropdown-item-close"><i class="ri-close-circle-line"></i></a>
                                                    </div>
                                                </div>
                                                <div class="min-w-fit-content d-flex align-items-start justify-content-between">
                                                    <ul class="header-product-item d-flex">
                                                        <li>$100</li>
                                                        <li class="text-decoration-line-through text-muted">$150</li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                                <div class="p-2 empty-footer-item border-top">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <span class="fs-13 fw-semibold op-9">Total</span>
                                        </div>
                                        <div class="text-end font-medium">
                                            <span class="fs-13 fw-semibold op-9">$40,020</span>
                                        </div>
                                    </div>
                                    </div>
                                <div class="p-3 empty-header-item border-top">
                                    <div class="d-grid">
                                        <a href="checkout.html" class="btn btn-primary">Proceed to checkout</a>
                                    </div>
                                </div>
                                <div class="p-5 empty-item d-none">
                                    <div class="text-center">
                                        <span class="avatar avatar-xxl avatar-rounded bg-primary-transparent">
                                            <i class="ri-shopping-cart-2-line fs-2"></i>
                                        </span>
                                        <h6 class="fw-bold mb-1 mt-3">No Items In Cart</h6>
                                        <span class="mb-3 fw-normal fs-13 d-block">When you have Items added here , they will appear here.</span>
                                        <a href="products.html" class="btn btn-primary btn-wave btn-sm m-1" data-abc="true"><i class="bi bi-arrow-right me-1"></i>continue shopping </a>
                                    </div>
                                </div>
                            </div>
                        <!-- End::main-header-dropdown -->
                    </div>
                    <!-- End::header-element -->

                    <!-- Start::header-element -->
                    <div class="header-element notifications-dropdown">
                        <?php include_once 'html/includes/components/notification_dropdown.php'; ?>
                    </div>
                    <!-- End::header-element -->

                    <!-- Start::header-element -->
                    <div class="header-element header-shortcuts-dropdown">
                        <!-- Start::header-link|dropdown-toggle -->
                        <!-- <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" id="notificationDropdown" aria-expanded="false">
                            <i class="ri-bookmark-line header-link-icon"></i>
                        </a> -->
                        <!-- End::header-link|dropdown-toggle -->
                        <!-- Start::main-header-dropdown -->
                        <div class="main-header-dropdown header-shortcuts-dropdown dropdown-menu pb-0 dropdown-menu-end" aria-labelledby="notificationDropdown">
                            <div class="header-dropdown bg-primary text-fixed-white rounded-top">
                                <div class="d-flex align-items-center justify-content-between">
                                    <p class="mb-0 fs-15 fw-semibold">Related Apps</p>
                                </div>
                            </div>
                            <div class="dropdown-divider mb-0"></div>
                            <div class="main-header-shortcuts p-2" id="header-shortcut-scroll">
                                <div class="row drop-icon-wrap my-2  p-2">
                                    <div class="col-4">
                                        <a href="mail-inbox.html" class=" d-grid justify-content-center    rounded p-1 ">
                                            <div class="main-grid">
                                                <span class="avatar p-2  bg-primary-transparent rounded-2 mx-auto">
                                                    <i class="ri ri-mail-line fs-24"></i>
                                                 </span>
                                                <span class="mt-2 fs-12  fw-semibold text-center">Mail Box</span>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-4">
                                        <a href="chat.html" class=" d-grid justify-content-center mb-0   rounded p-1 ">
                                            <div class="main-grid">
                                                <span class="avatar p-2  bg-secondary-transparent rounded-2 mx-auto">
                                                    <i class="ri ri-chat-2-line fs-24"></i>
                                                 </span>
                                                <span class="mt-2 fs-12  fw-semibold text-center">Chat</span>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-4">
                                        <a href="tasks.html" class=" d-grid justify-content-center   rounded p-1 ">
                                            <div class="main-grid">
                                                <span class="avatar p-2  bg-warning-transparent rounded-2 mx-auto">
                                                    <i class="ri ri-task-line  fs-24"></i>
                                                </span>
                                                <span class="mt-2 fs-12  fw-semibold text-center">Tasks</span>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-4 mt-3">
                                        <a href="calendar.html" class=" d-grid justify-content-center    rounded p-1 ">
                                            <div class="main-grid">
                                                <span class="avatar p-2  bg-danger-transparent rounded-2 mx-auto">
                                                    <i class="ri ri-calendar-event-line  fs-24"></i>
                                                </span>
                                                <span class="mt-2 fs-12  fw-semibold text-center">calendar</span>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-4 mt-3">
                                        <a href="filemanager.html" class=" d-grid justify-content-center  rounded p-1 ">
                                            <div class="main-grid">
                                                <span class="avatar p-2  bg-info-transparent rounded-2 mx-auto">
                                                    <i class="ri ri-file-copy-2-line   fs-24"></i>
                                                </span>
                                                <span class="mt-2 fs-12  fw-semibold text-center">FileManager</span>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-4 mt-3">
                                        <a href="contacts.html" class=" d-grid justify-content-center rounded p-1 ">
                                            <div class="main-grid">
                                                <span class="avatar p-2  bg-success-transparent rounded-2 mx-auto">
                                                    <i class="ri ri-group-line   fs-24"></i>
                                                </span>
                                                <span class="mt-2 fs-12  fw-semibold text-center">Contacts</span>
                                            </div>
                                        </a>
                                    </div>
                                 </div>
                            </div>
                            <div class="p-3 border-top">
                                <div class="d-grid">
                                    <a href="javascript:void(0);" class="btn btn-primary">View All</a>
                                </div>
                            </div>
                        </div>
                        <!-- End::main-header-dropdown -->
                    </div>
                    <!-- End::header-element -->

                    <?php
                    if($isValidAdmin) {?>
                         <div class="header-element">
                            <!-- Start::header-link|switcher-icon -->
                            <a href="<?php echo "{$base}html/?s=core&ss=admin&p=home" ?>" class="header-link switcher-icon">
                                <i class="ri-tools-fill header-link-icon"></i>
                            </a>
                            <!-- End::header-link|switcher-icon -->
                        </div>
                        <?php
                    }?>


                    <!-- Start::header-element -->
                    <div class="header-element">
                        <!-- Start::header-link|dropdown-toggle -->
                        <a href="javascript:void(0);" class="header-link dropdown-toggle" id="mainHeaderProfile" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <div class="">
                                <?php
                                        if($userDetails && $userDetails->profile_image) {?>
                                            <img src="<?php echo "{$config['DataDir']}{$userDetails->profile_image}" ?>" alt="img" width="30" height="30" class="rounded-circle">

                                            <?php
                                        } else {?>
                                            <img src="<?php echo $base ?>assets/img/users/8.jpg" alt="img" width="30" height="30" class="rounded-circle">
                                            <?php
                                        }?>
                                    <!-- <img src="<?php echo $base ?>assets/img/users/1.jpg" alt="img" width="30" height="30" class="rounded-circle"> -->
                                </div>
                            </div>
                        </a>
                        <!-- End::header-link|dropdown-toggle -->
                        <div class="main-header-dropdown dropdown-menu pt-0 overflow-hidden header-profile-dropdown dropdown-menu-end" aria-labelledby="mainHeaderProfile">
                            <div class="header-dropdown bg-primary text-fixed-white rounded-top">
                                <div class="d-flex align-items-center">
                                    <div class="me-sm-2 me-0 avatar">
                                        <?php
                                        if($userDetails && $userDetails->profile_image) {?>
                                            <img src="<?php echo "{$config['DataDir']}{$userDetails->profile_image}" ?>" alt="img" class="rounded-circle">
                                            <?php
                                        } else {?>
                                            <img src="<?php echo $base ?>assets/img/users/8.jpg" alt="img" class="rounded-circle">
                                            <?php
                                        }

                                        ?>

                                    </div>
                                    <div class="d-sm-block d-none">
                                        <p class="fw-semibold mb-0 lh-1"><?php echo Core::user_name_object($userDetails); ?> </p>
                                        <span class="op-7 fw-medium d-block fs-12">
                                        <span class="d-block fst-italic mb-0"> <?= $userDetails->Email ? $userDetails->Email : ''; ?></span>
                                        <?= isset($employeeDetails)  && isset($employeeDetails->jobTitle) ? $employeeDetails->jobTitle : ''; ?>

                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-divider mb-0"></div>

                            <ul class="list-unstyled">
                                <li><a class="dropdown-item d-flex" href="<?php echo "{$base}html/?s=user&p=profile&uid={$userDetails->ID}"?>"><i class="ti ti-user-circle fs-18 me-2"></i>Profile</a></li>
                                <!-- <li><a class="dropdown-item d-flex" href="mail-inbox.html"><i class="ti ti-inbox fs-18 me-2"></i>Inbox</a></li> -->
                                <li><a class="dropdown-item d-flex border-block-end" href="<?= "{$base}html/?s=user&ss=schedule&p=to_do_list"?>"><i class="ti ti-clipboard-check fs-18 me-2"></i>Task Manager</a></li>
                                <!-- <li><a class="dropdown-item d-flex" href="<?= "{$base}html/?s=core&ss=admin&p=settings";  ?>"><i class="ti ti-adjustments-horizontal fs-18 me-2"></i>Settings</a></li> -->
                                <!-- <li><a class="dropdown-item d-flex border-block-end" href="index3.html"><i class="ti ti-wallet fs-18 me-2"></i>Bal: $7,12,950</a></li> -->
                                <li><a class="dropdown-item d-flex" href="<?= "{$base}html/?s=user&p=support" ?>"><i class="ti ti-headset fs-18 me-2"></i>Support</a></li>
                                <li><a class="dropdown-item d-flex" href="<?php echo "{$base}php/scripts/core/sign_out.php" ?>"><i class="ti ti-logout fs-18 me-2"></i>Log Out</a></li>
                            </ul>
                            </div>
                    </div>
                    <!-- End::header-element -->

                    <!-- Start::header-element -->
                    <div class="header-element">
                        <!-- Start::header-link|switcher-icon -->
                        <!-- <a href="javascript:void(0);" class="header-link switcher-icon" data-bs-toggle="offcanvas" data-bs-target="#switcher-canvas">
                            <i class="ri-settings-5-line animate-spin header-link-icon"></i>
                        </a> -->
                        <!-- End::header-link|switcher-icon -->
                    </div>
                    <!-- End::header-element -->

                </div>
                <!-- End::header-content-right -->

            </div>
            <!-- End::main-header-container -->

        </header>