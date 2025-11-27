<?php
if($isAdmin || $isHRManager) {
    // Ensure userDetails exists before accessing properties
    if (!isset($userDetails) || !is_object($userDetails) || !isset($userDetails->ID)) {
        Alert::danger("Unable to load user details. Please log in again.", true, array('fst-italic', 'text-center', 'font-18'));
        include "includes/core/log_in_script.php";
        return;
    }

    //get the admin level for the current user
    $adminLevel = Core::app_administrators(array('userID' => $userDetails->ID), false, $DBConn);
    // var_dump($adminLevel);
    //if user is an admin  with several organisations then show buttons to select the organisation
    if($isAdmin && $adminLevel && count($adminLevel) > 1 ){ ?>
        <!-- Organization Selection Header -->
        <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
            <div>
                <h1 class="page-title fw-semibold fs-24 mb-2">Select Organization</h1>
                <p class="text-muted mb-0">Choose an organization to manage its users and settings</p>
            </div>
            <div class="ms-md-1 ms-0">
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Core</a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Admin</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Users</li>
                    </ol>
                </nav>
            </div>
        </div>

        <!-- Organization Selection Display -->
        <?php if($orgDataID):
            // Compact button switcher when an organization is selected
            $colors = ['primary', 'secondary', 'success', 'info', 'warning', 'purple', 'teal', 'orange'];
            $iconClasses = ['fa-building', 'fa-briefcase', 'fa-sitemap', 'fa-city', 'fa-industry', 'fa-landmark', 'fa-store', 'fa-hospital'];

            // Get current organization details
            $currentOrg = null;
            foreach($adminLevel as $admin){
                if($admin->orgDataID == $orgDataID){
                    $currentOrg = $admin;
                    break;
                }
            }
        ?>
            <!-- Compact Organization Switcher -->
            <div class="card custom-card mb-4">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center">
                            <span class="avatar avatar-md bg-primary-transparent text-primary me-3">
                                <i class="fas fa-building fs-18"></i>
                            </span>
                            <div>
                                <small class="text-muted d-block mb-1">Current Organization</small>
                                <h6 class="fw-semibold mb-0"><?= htmlspecialchars($currentOrg->orgName ?? 'Unknown') ?></h6>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <small class="text-muted me-2">Switch to:</small>
                            <?php foreach($adminLevel as $index => $admin):
                                $color = $colors[$index % count($colors)];
                                $icon = $iconClasses[$index % count($iconClasses)];
                                $isActive = ($admin->orgDataID == $orgDataID);
                            ?>
                                <a href="<?= $base ?>html/?s=core&ss=admin&p=users&orgDataID=<?= $admin->orgDataID ?>"
                                   class="btn btn-<?= $isActive ? $color : 'outline-' . $color ?> btn-sm btn-wave <?= $isActive ? 'active' : '' ?>"
                                   title="<?= htmlspecialchars($admin->orgName) ?>"
                                   <?= $isActive ? 'aria-current="true"' : '' ?>>
                                    <i class="fas <?= $icon ?> me-1"></i>
                                    <span class="d-none d-sm-inline"><?= htmlspecialchars($admin->orgName) ?></span>
                                    <span class="d-inline d-sm-none"><?= substr(htmlspecialchars($admin->orgName), 0, 15) . (strlen($admin->orgName) > 15 ? '...' : '') ?></span>
                                    <?php if($isActive): ?>
                                        <i class="ri-check-line ms-1"></i>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

        <?php else:
            // Full card grid when no organization is selected
        ?>
            <div class="row">
                <?php foreach($adminLevel as $index => $admin):
                    $colors = ['primary', 'secondary', 'success', 'info', 'warning', 'purple', 'teal', 'orange'];
                    $color = $colors[$index % count($colors)];
                    $iconClasses = ['fa-building', 'fa-briefcase', 'fa-sitemap', 'fa-city', 'fa-industry', 'fa-landmark', 'fa-store', 'fa-hospital'];
                    $icon = $iconClasses[$index % count($iconClasses)];
                ?>
                    <div class="col-xxl-3 col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-4">
                        <a href="<?= $base ?>html/?s=core&ss=admin&p=users&orgDataID=<?= $admin->orgDataID ?>" class="text-decoration-none">
                            <div class="card custom-card organization-card border-0 shadow-sm h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-start justify-content-between mb-3">
                                        <div class="flex-fill">
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="avatar avatar-lg bg-<?= $color ?>-transparent text-<?= $color ?> me-3">
                                                    <i class="fas <?= $icon ?> fs-24"></i>
                                                </span>
                                                <div>
                                                    <h5 class="fw-semibold mb-1 text-dark"><?= htmlspecialchars($admin->orgName) ?></h5>
                                                    <span class="badge bg-<?= $color ?>-transparent text-<?= $color ?>">
                                                        <i class="ri-shield-check-line me-1"></i>Administrator
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <i class="ri-arrow-right-circle-line fs-20 text-<?= $color ?>"></i>
                                        </div>
                                    </div>

                                    <div class="mt-3 pt-3 border-top border-block-start-dashed">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="text-muted fs-12">
                                                <i class="ri-user-line me-1"></i>Manage Users & Settings
                                            </span>
                                            <span class="text-<?= $color ?> fs-11 fw-semibold">
                                                Click to access <i class="ri-arrow-right-s-line"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-<?= $color ?>-transparent border-top-0 p-0">
                                    <div class="btn btn-<?= $color ?> btn-wave w-100 rounded-0 rounded-bottom">
                                        <i class="ri-login-circle-line me-2"></i>Access Organization
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- Helpful Information Card - Only show when no org is selected -->
            <?php if(!$orgDataID): ?>
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card custom-card border-primary border-opacity-25">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-md bg-primary-transparent text-primary me-3">
                                        <i class="ri-information-line fs-20"></i>
                                    </span>
                                    <div>
                                        <h6 class="mb-1 fw-semibold">Multiple Organization Access</h6>
                                        <p class="text-muted mb-0 fs-13">You have administrator privileges for <?= count($adminLevel) ?> organizations. Select an organization above to manage its users, structure, and settings.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <style>
            .organization-card {
                transition: all 0.3s ease;
                cursor: pointer;
            }

            .organization-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
            }

            .organization-card:hover .card-footer .btn {
                transform: scale(1.02);
            }

            .organization-card .card-footer .btn {
                transition: all 0.2s ease;
            }

            .organization-card:active {
                transform: translateY(-2px);
            }
        </style>
        <?php
        // Continue to show the users page content below
        if(!$orgDataID){
            return;
        }
    }

    // If single organization, set orgDataID automatically
    if($isAdmin && $adminLevel && count($adminLevel) == 1){
        $orgDataID = $adminLevel[0]->orgDataID;
    }

    if($isHRManager ){
        $employee = Employee::employees(['ID' => $userDetails->ID], true, $DBConn);
        $orgDataID = $employee->orgDataID;
    }

?>


   <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb border-bottom">
      <h1 class="page-title fw-medium fs-24 mb-0">Tija Users</h1>
      <div class="ms-md-1 ms-0">
         <nav>
               <ol class="breadcrumb mb-0">
                  <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $s ?></a></li>
                  <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $ss ?></a></li>
                  <li class="breadcrumb-item active d-inline-flex" aria-current="page">Users</li>
               </ol>
         </nav>
      </div>
    </div>
    <?php
    $linkArray = array(
        (object)[
            'id' => 'home',
            'name' => 'Organisation Users',
            'include' => 'users.php'
        ],
        (object)[
            'id' => 'structure',
            'name' => 'Organisation Structure',
            'include' => 'organisation_structure.php'
        ],
        // (object)[
        //     'id' => 'cost_center',
        //     'name' => 'Cost Center',
        //     'include' => 'cost_center.php'
        // ],
        (object)[
            'id' => 'chart',
            'name' => 'Organisation Chart(Reporting Hierarchy)',
            'include' => 'organisation_chart.php'
        ],
        // (object)[
        //     'id' => 'job_titles',
        //     'name' => 'Assigned Job Titles',
        //     'include' => 'assigned_job_titles.php'
        // ],
        // (object)[
        //     'id' => 'roles',
        //     'name' => 'Assigned Roles',
        //     'include' => 'assigned_roles.php'
        // ],
    );
    $state = isset($_GET['state']) ? Utility::clean_string($_GET['state']) : 'home';
    $getString = str_replace("&orgDataID={$orgDataID}", "", $getString);
$getString .=$orgDataID ? "&orgDataID={$orgDataID}" : "";
    ?>


    <div class="col-12 bg-light-blue py-2 text-end ">
      <?php foreach($linkArray as $link): ?>
        <a href="<?php echo "{$base}html/{$getString}&state=" . $link->id ?>" class="btn <?php echo  $state === $link->id ? "btn-primary" : "btn-outline-dark"; ?> btn-sm rounded-pill btn-wave px-4 py-0"  >
            <?php echo $link->name ?>
        </a>
      <?php endforeach; ?>
    </div>
    <?php
    foreach($linkArray as $link){
        if($state == $link->id){
            include_once("includes/core/admin/organisation/" . $link->include);
            break;
        }
    }
    $getString = str_replace("&state={$state}", "", $getString);
$getString .="&state={$state}";

} else {
    Alert::info("You need to be logged in as a valid administrator to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
}?>