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

$allActivities = Schedule::tija_activities(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);

?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <h1 class="page-title fw-medium fs-24 mb-0">Schedule To Do Activities </h1>
    <div class="ms-md-1 ms-0">
         <button type="button" class="btn btn-primary-light shadow btn-sm px-4 addNewActivity" data-bs-toggle="modal" data-bs-target="#manage_activity">
            <i class="ri-add-line"></i>
           Assign/Schedule Activity
        </button>
       
    </div>
</div>




<div class="container-fluid bg-white p-4 my-4">
   <div class="d-flex justify-content-around align-items-stretch">

      <div class="flex-fill pe-5">
         <h2 class="t300 font-16">Activities Completed this month</h2>
         <div class="d-flex justify-content-between align-items-center">
            <h2 class="t300 font-24"> 0 <span class="fs-12"> Activities </span> </h2>
            <span class="badge bg-success">+0%</span>
         </div>
      </div>
      
      <div class="flex-fill pe-5">
         <h2 class ="t300 font-16">Deadlines Upcoming this month</h2>
         <div class="d-flex justify-content-between align-items-center">
            <h2 class="t300 font-24"> 0 <span class="fs-12"> Deadlines </span></h2>
            <span class="badge bg-success">+0%</span>
         </div>
      </div>

      <div class="flex-fill pe-5">
         <a href="#active_customers" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#active_customers">
            <h2 class="t300 font-16">Activities Late</h2>
            </a>      
         <div class="d-flex justify-content-between align-items-center">
            <h2 class="t300 font-24 text-danger text-shadow t600">0<span class="fs-12 text-danger"> Activities Late </span></h2>
            <span class="badge bg-danger">+30%</span>         
         </div>
      </div>
      <div class="flex-fill pe-5">
         <h2 class="t300 font-16">Activities Open today</h2>
         <div class="d-flex justify-content-between align-items-center">
            <h2 class="t300 font-24"> 0<span class="fs-12"> Activities </span></h2>
            <span class="badge bg-success">+0%</span>
         </div>
      </div>

   </div>
</div>
<div class="container-fluid bg-white border-0 rounded-3">
      <div class="card custom-card">
         <div class="card-header justify-content-between">
            <h4 class="card-title">Activities</h4>
            <div class="card-options">
               <!-- <a href="#" class="btn btn-primary-light shadow btn-sm px-4 addNewSale" data-bs-toggle="modal" data-bs-target="#manageSale">
                  <i class="ri-add-line"></i>
                  assign & Schedule Activity
               </a> -->
           
            </div>
         </div>
         <div class="card-body">
            <div class="row">
               <div class="col-md">
                  <label for="filterBy" class="form-label">Filter By</label>
                  <select id="filterBy" class="form-select form-control-sm">
                     <option value="">All</option>
                     <option value="completed">Completed</option>
                     <option value="pending">Pending</option>
                  </select>
               </div>
               <div class="col-md">
                  <label for="assignedTo" class="form-label">Assigned To</label>
                  <select id="assignedTo" class="form-select form-control-sm">
                     <option value="">All</option>
                     <?php foreach($allEmployees as $employee): ?>
                        <option value="<?php echo $employee->ID; ?>"><?php echo $employee->employeeName; ?></option>
                     <?php endforeach; ?>
                  </select>
               </div>
               <div class="col-md">
                  <label for="dueDate" class="form-label">Due Date</label>
                  <input type="date" id="dueDate" class="form-control form-control-sm">
               </div>
               <div class="col-md-4">
                  <label for="search" class="form-label">Search</label>
                  <input type="text" id="search" class="form-control form-control-sm" placeholder="Search To Do Activities">
               </div>
            </div>
            
         </div>
         <div class="col-12">
               <div class="table-responsive">
                  <table id="toDoListTable" class="table table-striped table-bordered table-hover w-100">
                     <thead>
                           <tr>
                              <th>To Do</th>
                              <th>Assigned To</th>
                              <th>Due Date</th>
                              <th>Status</th>
                              <th>Action</th>
                           </tr>
                     </thead>
                     <tbody>
                           <?php 
                           $toDoList = Schedule::tija_activities(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
                           if($toDoList) {
                              foreach($toDoList as $item) {
                                 // var_dump($item);
                                 $assignedTo = Data::users(array('ID'=>$item->activityOwnerID), true, $DBConn);
                                 $status = ($item->activityStatus == "completed") ? '<span class="badge bg-success">Completed</span>' : "<span class='badge bg-danger'>{$item->activityStatus }</span>";
                                 echo "<tr>";
                                 echo "<td>{$item->activityName}</td>";
                                 echo "<td>{$item->activityOwnerName}</td>";
                                 echo "<td>{$item->activityDate}</td>";
                                 echo "<td>{$status}</td>";
                                 echo "<td><button type='button' class='btn btn-primary-light shadow btn-sm px-4 editToDo' data-id='{$item->activityID}' data-bs-toggle='modal' data-bs-target='#manageSale'><i class='ri-edit-line'></i></button></td>";
                                 echo "</tr>";
                              }
                           } else {
                              echo "<tr><td colspan='5' class='text-center'>No To Do Activities Found</td></tr>";
                           }?>
                     </tbody>
                  </table>
               </div>
         </div>
      </div>

</div>
<?php 
   /* Code to add and edit tasks */
   echo Utility::form_modal_header("manage_activity", "schedule/manage_activity.php", "Manage Activity", array('modal-lg', 'modal-dialog-centered'), $base);
   include 'includes/scripts/schedule/modals/manage_activity.php';
   echo Utility::form_modal_footer("save activity", "manage_activity_submit", 'btn btn-primary btn-sm');?>
