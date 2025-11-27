<div class="col-12">
   <h3 class='text-start border-bottom text-dark-blue fs-18'><?php echo "{$titlePage}" ?> 
      <a class="btn  btn-icon btn-sm rounded-pill btn-primary-light mx-3" href="javascript:void(0);" data-bs-toggle="collapse" data-bs-target="#manageBusinessDevelopment" aria-expanded="false" aria-controls="manageBusinessDevelopment" title="Manage <?= "{$titlePage}" ?>">
         <i class="ri-add-line"></i>
      </a>
      <div class="float-end">
         <span class="badge bg-primary-light text-primary ms-2"><?php echo ucfirst($state); ?></span>
         <span class="badge bg-secondary-light text-secondary ms-2"><?php echo $salesCases  ? count($salesCases) : 0; ?> Cases</span>
         <button type="button" class="btn btn-primary-light btn-sm addNewProspect" data-bs-toggle="collapse" data-bs-target="#manageBusinessDevelopment"  aria-expanded="false" aria-controls="manageBusinessDevelopment" data-action="add" title="Add New <?= "{$titlePage}" ?>">
               <i class="ri-add-line"></i> Add <?= "{$titlePage}" ?> 
         </button>
      </div>      
   </h3>    
   <div class="collapse col-4" id="manageBusinessDevelopment"> 
      <div class="card card-body mb-0">
         <?php include "includes/scripts/sales/forms/manage_business_development.php"; ?>
      </div> 
   </div>
  
   <?php
   // Check if there are any sales cases
   if($salesCases){
      echo "<p class='text-muted text-end text-primary'>You have " . count($salesCases) . " cases in {$titlePage}.</p>";?>
      <div class='row'>          
         <?php
         // Loop through each sales case and display the alert                                
         foreach ($salesCases as $case) {         
            $addresses = Client::client_address(['clientID'=>$case->clientID], false, $DBConn);               
            $contactTypes = Client::contact_types([], false, $DBConn);
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
   }
   $opportunityStatusLevels = Sales::sales_status_levels(["Suspended"=> "N", 'orgDataID'=>$orgDataID, 'entityID'=>$entityID], false, $DBConn);
   // var_dump($opportunityStatusLevels);
   echo Utility::form_modal_header(
         "progressBusinessDevelopment",
         "sales/progress_sale.php",
         "Progress {$titlePage}",
         array('modal-lg', 'modal-dialog-centered'), 
         $base
      );

    include "includes/scripts/sales/modals/progress_business_development.php";
   echo Utility::form_modal_footer(
      "Save Progress",
      "progressBusinessDevelopmentID",     
      ' btn btn-success btn-sm',
      true
   );
   // Include the corresponding script for the state
   // include "includes/scripts/sales/{$state}.php";
   ?>
</div>
<script>
   document.addEventListener("DOMContentLoaded", function() {
      const progressFormBtn = document.querySelectorAll('.businessDevProgressBtn');
      // Add event listener to the progressFormBtn
      progressFormBtn.forEach(function(button) {
         button.addEventListener('click', function() {
            // Log the button click
            console.log("Progress Form Button clicked");
            // Get the form
            const form = document.querySelector('.businessDevProgressForm');
            if (!form) return;
            // Get all data attributes from the button
            const data = this.dataset;
            console.log(data);
            // Map form fields to their corresponding data attributes         
            form.querySelector('#salesCaseID').value = data.salesCaseId || '';
            form.querySelector('#clientID').value = data.clientId || '';
            form.querySelector('#businessUnitID').value = data.businessUnitId || '';
            form.querySelector('#salesPersonID').value = data.salesPersonId || '<?php echo $userDetails->ID; ?>';
            form.querySelector('#orgDataID').value = data.orgDataId || '';
            form.querySelector('#entityID').value = data.entityId || '';
         });
      });

      const currentUserID = <?php echo json_encode($userDetails->ID); ?>;
      // add event listener to the addNewSale button
      document.querySelector('.addNewProspect').addEventListener('click', function() {
         // Log the button click
         console.log("Add New Sale button clicked");
         // Check if the businessDevForm exists
         // Reset the form
         const form = document.querySelector('.businessDevForm');
         if (form) {
            // Log the form
            console.log("Resetting form:", form);
            // Reset the form fields except thje hidden ones
            const inputs = form.querySelectorAll('input:not([type="hidden"])');
            inputs.forEach(input => {
               input.value = '';
            });
            // form.reset();
            // Clear any Tom Select values if applicable
            const selects = ['clientID', 'businessUnitID', 'salesPersonID'];
            selects.forEach(selectName => {
               const select = form.querySelector(`[name="${selectName}"]`);

               if (select && select.tomselect) {
                  console.log(`Clearing Tom Select: ${selectName}`);
                  select.tomselect.clear();
                  if (selectName === 'salesPersonID') {
                     select.value = currentUserID;
                  }
               } else if (select) {
                  select.value = '';
               }
            });

            // const tomSelects = document.querySelectorAll('.tom-select');
            // console.log(tomSelects);
            // tomSelects.forEach(select => {
            //    console.log(`Clearing Tom Select: ${select.name}`);
            //    if (select.tomselect) {
            //       select.tomselect.clear();
            //    }
            // });
         }
         // Show the businessDevForm
         // document.querySelector('.collapse').classList.add('show');
      });
      // Add event listener to the editProspectBtn
      document.querySelectorAll('.editProspectBtn').forEach(function(button) {
         button.addEventListener('click', function() {
            console.log(button);
            // get the form 
            const form = document.querySelector('.businessDevForm');

            if( !form) return;
   
            
            // Reset the form
            // form.reset();
            // log the form 

             // Get all data attributes from the button
             const data = this.dataset;

             console.log(data);

               // Map form fields to their corresponding data attributes
            const fieldMappings = {
               'salesCaseID': 'salesCaseId',
               'clientID': 'clientId',
               'salesCaseName': 'salesCaseName',
               'businessUnitID': 'businessUnitId',
               'salesPersonID': 'salesPersonId'
            };
            // Loop through the field mappings and set the form values
            for (const [field, dataAttr] of Object.entries(fieldMappings)) {
               const input = form.querySelector(`[name="${field}"]`);
               if (input) {
                  input.value = data[dataAttr] || '';
               }
            }
            // If you have select elements that need special handling
            // (like setting selected options), handle them here
            const selects = ['clientID', 'businessUnitID', 'salesPersonID'];
            selects.forEach(selectName => {
               const select = form.querySelector(`[name="${selectName}"]`);
               console.log(select);
               if (select && data[fieldMappings[selectName]]) {
                  // If the select element is a Tom Select, set its value
                  if (select.tomselect) {
                     console.log(`Setting value for Tom Select: ${selectName} of ${data[fieldMappings[selectName]]}`);
                     // Use Tom Select's setValue method if available
                     select.tomselect.setValue(data[fieldMappings[selectName]]);
                  } else {
                     // Otherwise, set the value directly
                     select.value = data[fieldMappings[selectName]];
                  }
               }
            });

            
         });
      });
   });
</script>