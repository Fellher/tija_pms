<div class="col-12">
   <h3 class='text-start border-bottom text-dark-blue'><?= "{$titlePage}" ?>
      <div class="float-end">
         <span class="badge bg-primary-light text-primary ms-2"><?php echo ucfirst($state); ?></span>
         <span class="badge bg-secondary-light text-secondary ms-2"><?php echo $salesCases  ? count($salesCases) : 0; ?> Cases</span>
         <button type="button" class="btn btn-primary-light btn-sm addNewSale" data-bs-toggle="modal" data-bs-target="#manageSale">
            <i class="ri-add-line"></i> Add <?= "{$titlePage}" ?> 
         </button>
         <?php  $view = (isset($_GET['view']) && $_GET['view'] == 'list') ? 'list' : 'kanban';?>
         <div class="btn-group">
            <?php 
            if($view == 'kanban'){?>
               <a href="<?= "{$base}html/{$getString}&view=list" ?>" class="btn btn-primary-light btn-sm">
                  <i class="ri-list-view"></i> List
               </a>
               <?php 
            } elseif($view == 'list'){?>
               <a href="<?= "{$base}html/{$getString}&view=kanban" ?>" class="btn btn-primary-light btn-sm">
                  <i class="ri-kanban-view"></i> Kanban
               </a>
               <?php 
            } else {?>
               <button type="button" class="btn btn-primary-light btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                  View
               </button>
               <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="<?= "{$base}html/{$getString}&view=list" ?>">List</a></li>
                  <li><a class="dropdown-item" href="<?= "{$base}html/{$getString}&view=kanban" ?>">Kanban</a></li>
               </ul>
               <?php 
            }?>
         </div>
      </div>      
   </h3>
   <?php

   // var_dump($getString);
   $salesStatusLevels = Sales::sales_status_levels(['entityID'=>$entityID], false, $DBConn);
   
   // var_dump($salesStatusLevels);
   // create kanban of sales cases based on sales status levels
   // var_dump($salesCases[0]);
   // Check if view is list or kanban
   $view = (isset($_GET['view']) && $_GET['view'] == 'list') ? 'list' : 'kanban';
   // var_dump($view);
   if($view == 'kanban'){
   
      if($salesStatusLevels){?>
      <div class="row  d-flex align-items-stretch">
         <?php

         foreach($salesStatusLevels as $key => $statusLevel){?>
            <div class="col-lg col-sm-12 col-md-6 mb-4">
               <div class="card shadow-sm border-0 h-100">
                  <div class="card-header bg-light-blue text-white">
                     <h5 class="mb-0"><?php echo $statusLevel->statusLevel; ?></h5>
                  </div>
                  <div class="card-body">
                     <?php
                     // var_dump($statusLevel);
                     // Get sales cases for the current status level
                     if($saleFilter == 'mySales') {
                       
                        $salesCaseFilterOp = array(
                           'orgDataID'=>$orgDataID, 
                           'entityID'=>$entityID,  
                           'saleStatusLevelID'=>$statusLevel->saleStatusLevelID,
                           'salesPersonID'=>$employeeID, 
                           'Suspended'=>'N'
                        );
                        
                     } else {
                        $salesCaseFilterOp = array(
                           'orgDataID'=>$orgDataID, 
                           'entityID'=>$entityID,  
                           'saleStatusLevelID'=>$statusLevel->saleStatusLevelID,
                           
                           'Suspended'=>'N'
                        );
                      
                       
                     }
                     $salesCasesForStatus = Sales::sales_case_mid($salesCaseFilterOp, false, $DBConn);
                     // var_dump($salesCasesForStatus);
                     if($salesCasesForStatus){
                        foreach($salesCasesForStatus as $case){
                           // Include the corresponding script for the case
                           // include "includes/scripts/sales/{$state}.php";
                           // var_dump($case);?>
                           
                        <div class="alert alert-secondary  fade show custom-alert-icon shadow-sm " role="alert">
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
                                    <div class="d-flex align-items-stretch flex-column mb-2">
                                       <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=sale_details&saleid={$case->salesCaseID}" ?>" class="btn  btn-icon rounded-pill btn-primary-light my-1 <?= $case->salesPersonID !== $employeeID ? "disabled" : "" ?> " title="View Sale Details" data-bs-toggle="tooltip" data-bs-placement="top"  data-bs-custom-class="custom-tooltip"    data-bs-title="View Sales details"  >
                                          <i class="ti ti-eye" ></i>
                                       </a>      
                                       
                                       <?php
                                       if($case->closeStatus == 'won') { ?>
                                          <span class="badge bg-success  m-1">Won</span>
                                       <?php } elseif($case->closeStatus == 'lost') { ?>
                                          <span class="badge bg-danger  my-1">Lost</span>
                                          <?php 
                                       }?>  
                                    </div>                                        
                                 </div>
                              </div> 
                           </div>

                           <?php
                        }
                     } else {
                        Alert::info("No sales cases found for {$statusLevel->statusLevel}.", true, array('fst-italic', 'text-center', 'font-18'));
                        // echo "<p class='text-muted'>No sales cases found for {$statusLevel->statusLevel}.</p>";
                     }?>
                        
                  </div>
               </div>
            </div>
            <?php 
         }?>
      </div>
      <?php
      } else {
         Alert::info("No sales status levels found for {$titlePage}.", true, array('fst-italic', 'text-center', 'font-18'));
         // echo "<p class='text-muted'>No sales status levels found for {$titlePage}.</p>";
      }
   }

   if($view == 'list'){   
      if($salesCases){
         echo "<p class='text-muted text-end text-primary'>You have " . count($salesCases) . " cases in {$titlePage}.</p>";?>

         <div class='row'>          
            <?php
            // Loop through each sales case and display the alert                                
            foreach ($salesCases as $case) {
         
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
                                 <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=sale_details&saleid={$case->salesCaseID}" ?>" class="btn  btn-icon rounded-pill btn-primary-light">
                                    <i class="ti ti-eye"></i>
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
   }
   // Include the corresponding script for the state
   // include "includes/scripts/sales/{$state}.php";
   ?>
</div>

<!-- <div class="countainer-fluid">
   <div class="row">
      <div class="col-md-12">
         <div class="card">
            <div class="card-body">
               <div class="table-responsive">
                  <table class="table table-hover table-borderless table-centered table-nowrap table-responsive table-compact">
               <thead>
                  <tr>
                     <th scope="col">Case Name</th>
                     <th scope="col">Client</th>
                     <th scope="col">Contact Person</th>
                     <th scope="col">Business Unit</th>
                     <th scope="col">Status Level</th>
                     <th scope="col">Estimate</th>
                     <th scope="col">Probability</th>
                     <th scope="col">Expected Close Date</th>
                     <th scope="col">Lead Source</th>
                     <th scope="col">Actions</th>
                  </tr>
               </thead>
               <tbody>
                  <?php                    
                     if($salesCases) {
                        foreach($salesCases as $case) {
                           // var_dump($case);
                            // //var_dump($case);
                            $client = Client::clients(array('clientID'=>$case->clientID), true, $DBConn);
                            $statusLevel = Sales::sales_status_levels(array('saleStatusLevelID'=>$case->saleStatusLevelID), true, $DBConn);
                            $leadSource = Sales::lead_sources(array('leadSourceID'=>$case->leadSourceID), true, $DBConn);
                            $businessUnit = Data::business_units(array('businessUnitID'=>$case->businessUnitID), true, $DBConn);
                            ?>
                            <tr>
                                <td>
                                    <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=sale_details&saleid={$case->salesCaseID}"?>" class="text-body text-primary fw-bold">
                                    <i class="ri-search-line me-2"></i>
                                    <?php echo $case->salesCaseName; ?>
                                    </a>
                                </td>
                                <td><a href="<?= "{$base}html/?s=user&ss=clients&p=client_details&client_id={$case->clientID}" ?>"> <?php echo $client->clientName; ?></a></td>
                                <td><?php echo isset($case->contactName) ? $case->contactName : "not set"; ?></td>
                                <td><?php echo $businessUnit->businessUnitName; ?></td>
                                <td><?php echo $statusLevel->statusLevel; ?></td>
                                <td><?php echo $case->salesCaseEstimate; ?></td>
                                <td><?php echo $case->probability; ?></td>
                                <td><?php echo $case->expectedCloseDate; ?></td>
                                <td><?php echo isset($leadSource->leadSourceName) ?  $leadSource->leadSourceName: ""; ?></td>
                                <td>
                                    <div class="d-flex">
                                        <button 
                                        type="button" 
                                        class="btn btn-primary-light btn-sm editSalesButton" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#manageSale" 
                                        data-salesCaseID="<?php echo $case->salesCaseID; ?>"
                                        data-salesCaseName="<?php echo $case->salesCaseName; ?>"
                                        data-clientID="<?php echo $case->clientID; ?>"
                                        data-salesCaseContactID="<?php echo $case->salesCaseContactID; ?>"
                                        data-businessUnitID="<?php echo $case->businessUnitID; ?>"
                                        data-saleStatusLevelID="<?php echo $case->saleStatusLevelID; ?>"
                                        data-salesCaseEstimate="<?php echo $case->salesCaseEstimate; ?>"
                                        data-probability="<?php echo $case->probability; ?>"
                                        data-expectedCloseDate="<?php echo $case->expectedCloseDate; ?>"
                                        data-leadSourceID="<?php echo $case->leadSourceID; ?>"                                      
                                        >
                                        <i class="ri-pencil-line"></i>
                                        </button>
                                          <button type="button" class="btn btn-danger-light btn-sm ms-2 deleteSalesCaseButton" data-bs-toggle="modal" data-bs-target="#deleteSalesCaseModal" data-salesCaseID="<?php echo $case->salesCaseID; ?>">
                                        <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </td>
                           </tr>
                           <?php
                        }
                     }
                  ?>
               </tbody>
            </table>
         </div>
      </div>
   </div>
</div>  -->