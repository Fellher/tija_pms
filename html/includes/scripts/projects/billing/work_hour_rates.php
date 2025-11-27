<div class="row my-4">
   <div class="col-md-7">
      <div class="row nogutters ">         
         <div class=" d-flex  justify-content-between ">
            <h1 class="page-title fs-20 bg-light"> Hourly Rate</h1>
            <div class="card-options">
               <a href="#manage_billing_rate" class="btn btn-primary btn-sm float-end" data-bs-toggle="modal"   data-bs-target="#manage_billing_rate">Add Billing Rate</a>
            </div>
         </div>
         <div class="table-responsive">
            <table class="table table-hover table-borderless table-striped table-vcenter text-nowrap table-sm mb-0">
               <thead>
                  <tr>
                     <th>Rate Name</th>
                   
                     <th>Role</th>
                  
                     <th>Hourly Rate</th>
                     <th>Bill</th>
                     <th>Action</th>
                  </tr>
               </thead>
               <tbody>
                  <?php 
                  $workHourRates = Projects::billing_rate_full(['projectID' => $projectID], false, $DBConn);
                  // var_dump($workHourRates);
                  if($workHourRates) {
                     foreach ($workHourRates as $rate){ 
                        // var_dump($rate);
                        ?>
                        <tr>
                           <td><?php echo htmlspecialchars($rate->billingRateName); ?></td>
                         
                           <td>
                              <?= htmlspecialchars($rate->workTypeName); ?>
                           </td>
                           
                           <td><?php echo htmlspecialchars($rate->hourlyRate); ?></td>
                           <td>
                              <?php 
                              if($rate->bill == 'Y'){
                                 echo "<span class='badge bg-success'>Yes</span>";
                              } else {
                                 echo "<span class='badge bg-danger'>No</span>";
                              }
                              ?>
                           </td>
                          
                           <td><a href="javascript:void(0)" class="btn btn-sm btn-outline-secondary">Edit</a></td>
                        </tr>
                        <?php 
                     }
                  } else {
                     echo "<tr>
                              <td colspan='7' class='text-center'>";
                              Alert::info('No work hour rates found', true, array('fst-italic', 'text-center', 'font-18'));
                              echo "<a href='javascript:void(0)' class='btn btn-primary btn-sm' data-bs-toggle='modal' data-bs-target='#manage_billing_rate'>Add Billing Rate</a>";
                              echo "<br><br>";
                              echo "You can add work hour rates by clicking the button above.";
                              echo "<br><br>";
                              echo "
                              </td>
                           </tr>";
                                       
                  }  ?>
                  
               </tbody>
            </table>
         </div>

      </div>
      
   </div>
   
<?php 
echo Utility::form_modal_header("manage_billing_rate", "projects/manage_billing_rate.php", "Manage Billing  Details", array('modal-md', 'modal-dialog-centered'), $base);
include 'includes/scripts/projects/modals/manage_billing_rate.php';
echo Utility::form_modal_footer("Save Billing Rate", "manageTsk", 'btn btn-primary btn-sm');?>

   <div class="col-md-5">
      <div class="row nogutters ">
         <div class=" d-flex  justify-content-between ">
            <h1 class="page-title fs-20 bg-light">Overtime Multiplier</h1>
            <div class="card-options">
               <a href="javascript:void(0)" class="btn btn-primary btn-sm float-end" data-bs-toggle="modal"   data-bs-target="#manage_overtime_multiplier">Add Billing Rate</a>
               </div>
         </div>
         <div class="">
            <div class="table-responsive">
               <table class="table table-hover table-borderless table-striped table-vcenter text-nowrap table-sm mb-0">
                  <thead>
                     <tr>
                        <th>Rate Name</th>
                        <th>Multiplier</th>
                        <th>Work Type</th>
                        <th>Action</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php 
                     $overtimeMultiplier = Projects::overtime_multiplier(['projectID' => $projectID], false, $DBConn);
                     // var_dump($overtimeMultiplier);
                     if($overtimeMultiplier) {
                        foreach ($overtimeMultiplier as $multiplier){ 
                           // var_dump($multiplier);
                           $multiplierProject = Projects::projects_mini(['projectID' => $multiplier->projectID], true , $DBConn);
                           $workTypeIDs = explode(',', $multiplier->workTypeID);
                           // var_dump($workTypeIDs);
                           $workTypesArr = [];
                           if($workTypeIDs) {
                              foreach ($workTypeIDs as $key => $value) {
                                 $workTypeIDs[$key] = (int)$value;
                                 $workTypeDets = Data::work_types(['workTypeID' => $value], true, $DBConn);
                                 // var_dump($workType);
                                 $workTypesArr[$key] = $workTypeDets->workTypeName;
                              }
                           }
                           $workTypeNames = implode(',', $workTypesArr);
                           
                           ?>
                           <tr>
                              <td><?php echo htmlspecialchars($multiplier->overtimeMultiplierName); ?></td>
                              <td><?php echo htmlspecialchars($multiplier->multiplierRate); ?></td>
                              <td><?=  $workTypeNames ?></td>
                              <td><a href="javascript:void(0)" class="btn btn-sm btn-outline-secondary">Edit</a></td>
                           </tr>
                           <?php 
                        }
                     } else {
                        echo "<tr>
                                 <td colspan='7' class='text-center'>";
                                 Alert::info('No overtime rates found', true, array('fst-italic', 'text-center', 'font-18'));
                                 echo "<a href='javascript:void(0)' class='btn btn-primary btn-sm' data-bs-toggle='modal' data-bs-target='#manage_overtime_multiplier'>Add Overtime multiplier</a>";
                                 echo "<br><br>";
                                 echo "You can add overtime rate multiplier by clicking the button above.";
                                 echo "<br><br>";
                                 echo "
                                 </td>
                              </tr>";
                                         
                     }  ?>
                     
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </div>
</div>

<?php 
echo Utility::form_modal_header("manage_overtime_multiplier", "projects/manage_overtime_multiplier.php", "Manage Overtime Multiplier  Details", array('modal-md', 'modal-dialog-centered'), $base);
include 'includes/scripts/projects/modals/manage_overtime_multiplier.php';
echo Utility::form_modal_footer("Save Overtime Multiplier", "manageMutiplierTsk", 'btn btn-primary btn-sm');?>
