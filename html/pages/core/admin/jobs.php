<?php
if($isAdmin || $isValidAdmin) {
    $state = isset($_GET['state']) ? Utility::clean_string($_GET['state']) : 'job_titles';
    $jobCategories = Admin::tija_job_categories(array(), false, $DBConn); ?>

    <!-- Page Header -->
    <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb border-bottom">
        <h1 class="page-title fw-medium fs-24 mb-0">Tija Jobs and Roles  Profiles</h1>
        <div class="ms-md-1 ms-0">
            <nav>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $s ?></a></li>
                    <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $ss ?></a></li>
                    <li class="breadcrumb-item active d-inline-flex" aria-current="page">jobs</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="col-12 bg-light-blue py-2 text-end ">
        <a href="<?php echo "{$base}html/{$getString}&state=job_titles" ?>" class="btn  <?php echo  $state === "job_titles" ? "btn-primary" : "btn-outline-dark"; ?> btn-sm rounded-pill btn-wave px-4 py-0"  > Job Titles</a>
        <a href="<?php echo "{$base}html/{$getString}&state=job_categories" ?>" class="btn <?php echo  $state === "job_categories" ? "btn-primary" : "btn-outline-dark"; ?> btn-sm rounded-pill btn-wave px-4 py-0" > Manage Job Categories</a>
        <a href="<?php echo "{$base}html/{$getString}&state=status" ?>" class="btn <?php echo  $state === "status" ? "btn-primary" : "btn-outline-dark"; ?> btn-sm rounded-pill btn-wave px-4 py-0" > Employment Status</a>
        <a href="<?php echo "{$base}html/{$getString}&state=salaryComponents" ?>" class="btn <?php echo  $state === "salaryComponents" ? "btn-primary" : "btn-outline-dark"; ?> btn-sm rounded-pill btn-wave px-4 py-0" > Salary Components</a>
        <a href="<?php echo "{$base}html/{$getString}&state=payGrades" ?>" class="btn <?php echo  $state === "payGrades" ? "btn-primary" : "btn-outline-dark"; ?> btn-sm rounded-pill btn-wave px-4 py-0" > Pay Grades</a>

    </div>

    <?php
    $scriptMap = [
        'job_titles' => 'manage_job_titles.php',
        'job_categories' => 'manage_job_categories.php',
        'status' => 'manage_employment_status.php',
        'salaryComponents' => 'manage_salary_components_new.php',
        'payGrades' => 'manage_pay_grades.php',
        'employmentStatus' => 'manage_employment_status.php',
    ];
    $defaultScript = 'manage_job_titles.php';
    $scriptToInclude = isset($scriptMap[$state]) ? $scriptMap[$state] : $defaultScript;
    include_once("includes/core/admin/jobs/{$scriptToInclude}");
    $getString .="&state={$state}";
} else {
    Alert::info("You need to be logged in as a valid administrator to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
}?>