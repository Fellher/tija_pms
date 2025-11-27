<?php 
if($isAdmin || $isValidAdmin) {
   $employeeID = isset($_GET['uid']) ? Utility::clean_string($_GET['uid']) : '';
   $employeeDetails = Employee::employees(array('ID'=>$employeeID), true, $DBConn);
   // var_dump($employeeDetails);?>

   <div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb border-bottom">
      <h1 class="page-title fw-medium fs-24 mb-0"><?= $employeeDetails->employeeName?></h1>
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
   <div class="card">
      <div class="card-body">                
         <div class="table-responsive">
            <?php
            $userLogs =  TimeAttendance::project_tasks_time_logs_full(array('employeeID' => $employeeDetails->ID), false, $DBConn);
            if($userLogs) { 
               // var_dump($userLogs);
               ?>
               <table class="table table-striped table-bordered">
                  <thead>
                     <tr>
                        <th>Date</th>
                        <th>project</th>
                        <th>Task</th>
                        <th> Deadline </th>
                        <th>time</th>
                        <th>Details</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php foreach ($userLogs as $log) {
                        
                        // $timeSec = Utility::timestring_to_sec($log->taskDuration);
                        // var_dump($timeSec);
                        ?>
                        <tr>
                           <td><?php echo Utility::date_format($log->taskDate); ?></td>
                           <td><?php echo $log->projectName; ?></td>
                           <td><?php  echo $log->projectTaskName?></td>
                           <td><?php echo $log->taskDeadline; ?></td>
                           <td><?php echo $log->taskDuration ; ?></td>
                           <td><?php //echo $log->details; ?></td>
                        </tr>
                     <?php } ?>
                  </tbody>
               </table>
            <?php } else {
               Alert::info("No user logs found for this employee", true, array('fst-italic', 'text-center', 'font-18'));
            } ?>
         </div>
      </div>
   </div>
<?php
} else {
    Alert::error("You do not have permission to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    return;
}?>

