<?php 
if(!$isValidUser) {
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}

// var_dump($userDetails);
$employeeID=(isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) : $userDetails->ID;
$employeeDetails = Data::users(array('ID'=>$employeeID), true, $DBConn);

$allEmployees = Data::users([], false, $DBConn);

$orgDataID= isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : $employeeDetails->orgDataID;
$entityID= isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : $employeeDetails->entityID;
$clients = Client::clients(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
$businessUnits = Data::business_units(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
$allOrgs = Admin::organisation_data_mini([], false, $DBConn); 
$projects = Projects::projects_full([], false, $DBConn);
$salesCases = Sales::sales_case_full([], false, $DBConn);
$activity_categories = Schedule::activity_categories([], false, $DBConn);

$allActivities = Schedule::tija_activities(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <h1 class="page-title fw-medium fs-24 mb-0">Task Kanban </h1>
    <div class="ms-md-1 ms-0">
         <button type="button" class="btn btn-primary-light shadow btn-sm px-4 addNewActivity" data-bs-toggle="modal" data-bs-target="#manage_activity">
            <i class="ri-add-line"></i>
            Add Task
        </button>
       
    </div>
</div>
