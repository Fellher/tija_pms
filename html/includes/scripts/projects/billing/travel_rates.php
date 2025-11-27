<?php   $travelRateTypes = Projects::travel_rate_types(array(), false, $DBConn); ?>
<div class="row my-4">
   <div class="col-md-7">
      <div class="row nogutters ">         
         <div class=" d-flex  justify-content-between ">
            <h1 class="page-title fs-20 bg-light"> Travel Rates </h1>
            <div class="card-options">
               <a href="#manage_billing_rate" class="btn btn-primary btn-sm float-end" data-bs-toggle="modal"   data-bs-target="#manage_billing_rate">Add Billing Rate</a>
            </div>
         </div>
         <?php 
         if($travelRateTypes) {
            foreach ($travelRateTypes as $rate){ ?>
               <div class="col-md-12">
                  <div class="card card-body border-0 shadow-sm mb-3">
                     <h5 class="card-title bg-light py-1 px-2"><?php echo htmlspecialchars($rate->travelRateTypeName); ?></h5>
                  </div>
               </div>
            <?php
            }
         } else {
            echo "<div class='col-md-12'>
                     <div class='card card-body border-0 shadow-sm mb-3'>
                        <h5 class='card-title'>No travel rates found</h5>
                        <p class='card-text'>You can add travel rates by clicking the button below.</p>
                        <a href='javascript:void(0)' class='btn btn-primary btn-sm' data-bs-toggle='modal' data-bs-target='#manage_billing_rate'>Add Travel Rate</a>
                     </div>
                  </div>";
         }?>

      </div>
   </div>

   <div class="col-md-5">
      <div class="row nogutters ">
         <div class=" d-flex  justify-content-between ">
            <h1 class="page-title fs-20 bg-light"> Travel Rate Types </h1>
            <div class="card-options">
               <a href="#manage_travel_rate_types" class="btn btn-primary btn-sm float-end" data-bs-toggle="modal"   data-bs-target="#manage_travel_rate_types">Add Travel Rate Types</a>
            </div>
         </div>
         <div class="table-responsive">
            <table class="table table-hover table-borderless table-striped table-vcenter text-nowrap table-sm mb-0">
               <thead>
                  <tr>
                     <th>Rate type Name</th>
                     <th>Rate Description</th>
                    
                     <th>Action</th>
                  </tr>
               </thead>
               <tbody>
                  <?php 
                  
                  // var_dump($travelRates);
                  if($travelRateTypes) {
                     foreach ($travelRateTypes as $rate){ ?>
                        <tr>
                           <td><?php echo htmlspecialchars($rate->travelRateTypeName); ?></td>
                           <td><?php echo htmlspecialchars($rate->travelRateTypeDescription); ?></td>
                          
                           <td><a href="javascript:void(0)" class="btn btn-sm btn-outline-secondary">Edit</a></td>
                        </tr>
                        <?php 
                     }
                  } else {
                     echo "<tr>
                              <td colspan='4' class='text-center'>";
                              Alert::info('No travel rates found', true, array('fst-italic', 'text-center', 'font-18'));
                              echo "<a href='javascript:void(0)' class='btn btn-primary btn-sm' data-bs-toggle='modal' data-bs-target='#manage_travel_rate_types'>Add Travel Rate Type</a>";
                              echo "<br><br>";
                  }
                  ?>
               </tbody>
            </table>         
         </div>
      </div>
   </div>
</div>
<?php 
echo Utility::form_modal_header("manage_travel_rate_types", "projects/manage_travel_rate_types.php", "Manage Billing  Details", array('modal-md', 'modal-dialog-centered'), $base);
include 'includes/scripts/projects/modals/manage_travel_rate_types.php';
echo Utility::form_modal_footer("Save Billing Rate", "manageTsk", 'btn btn-primary btn-sm');



?>