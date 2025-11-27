<div class="col-12">
   <h3 class='text-start border-bottom text-dark-blue'><?= "{$titlePage}" ?>
      <div class="float-end">
         <span class="badge bg-primary-light text-primary ms-2"><?php echo ucfirst($state); ?></span>
         <span class="badge bg-secondary-light text-secondary ms-2"><?php echo $salesCases  ? count($salesCases) : 0; ?> Cases</span>
         
      </div>
      
   </h3>
   <?php 
   $salesCases = Sales::sales_case_mid(['entityID' => $entityID, 'closeStatus' => $state ], false, $DBConn);
   // var_dump($salesCases);

   if($salesCases){
      echo "<p class='text-muted text-end text-primary'>You have " . count($salesCases) . " cases in {$titlePage}.</p>";?>

      <div class='row'> 
         
         <?php
         // Loop through each sales case and display the alert                                
         foreach ($salesCases as $case) {
            // var_dump($case);
               // Include the corresponding script for the case?>
               <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                  <div class="alert alert-secondary alert-dismissible fade show custom-alert-icon shadow-sm " role="alert">
                     <div class="d-flex align-items-top">
                           <div class="flex-shrink-0 me-3">
                              <i class="ri-file-text-line fs-2 text-primary"></i>
                           </div>
                           <div class="flex-grow-1">
                              <h4 class="alert-heading fs-18 mb-0 pb-0"><?php echo $case->salesCaseName; ?></h4>
                              <p class="mb-0"><a href="<?= "{$base}html/?s=user&ss=clients&p=client_details&client_id={$case->clientID}" ?>" class="text-body fw-bold"><?php echo $case->clientName; ?></a></p>
                              <p class="mb-0">Business Unit: <?php echo $case->businessUnitName; ?></p>
                           </div>
                           <div class="flex-shrink-0 ms-auto">                                                     
                              <a href="javascript:void(0);" 
                                 class="btn  btn-icon rounded-pill btn-primary-light businessDevProgressBtn" 
                                 data-bs-toggle="modal" 
                                 data-bs-target="#progressBusinessDevelopment"
                                 data-sales-case-id="<?php echo $case->salesCaseID; ?>"
                                 data-client-id="<?php echo $case->clientID; ?>"                                                                
                                 data-business-unit-id="<?php echo $case->businessUnitID; ?>"
                                 data-org-data-id = "<?php echo $orgDataID; ?>"
                                 data-entity-id = "<?php echo $entityID; ?>"
                                 >

                                 <i class="ti ti-corner-up-right-double"></i>
                              </a>  
                              <a href="javascript:void(0);" 
                                 class="btn btn-icon rounded-pill btn-primary-light editProspectBtn" 
                                 data-bs-toggle="collapse" 
                                 data-bs-target="#manageBusinessDevelopment" 
                                 aria-expanded="false" 
                                 aria-controls="manageBusinessDevelopment" 
                                 data-sales-case-id="<?php echo $case->salesCaseID; ?>"
                                 data-client-id="<?php echo $case->clientID; ?>"
                                 data-client-name="<?php echo $case->clientName; ?>"
                                 data-sales-case-name="<?php echo $case->salesCaseName; ?>"
                                 data-business-unit-id="<?php echo $case->businessUnitID; ?>"                           
                                 data-sales-person-id="<?php echo $case->salesPersonID; ?>"
                                 title="Edit Sale Case">
                                 <i class="ti ti-edit"></i>
                              </a>
                           </div>
                     </div>                      
                  </div>
               </div>
            
               <?php
         }?>
      </div>
      <?php
   } else {
      Alert::info("You have no cases in {$titlePage}.", true, array('fst-italic', 'text-center', 'font-18'));

      // echo "<p class='text-muted'>You have no cases in {$titlePage}.</p>";
   }
   ?>


</div>