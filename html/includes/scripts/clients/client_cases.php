<div class="container-fluid py-7 my-4">						
   <div class="card clearfix shadow-lg my-7">
      <div class="card-header bg-light-blue">
         <h3 class="t400 mb-0 font-20">Sales Cases </h3>
      </div>
      <div class="card-body">						
         <?php										
         if ($sales) {?>	
            <div class="table-responsive">
               <table class="table table-sm table-hover table-stripped">
                  <thead class="table-dark">
                     <tr>
                        <th> Case Name</th>
                        <th> Sales Person</th>
                        <th>Sales estimate</th>
                        <th>Sales status</th>
                        <th>Probability %</th>
                        <th>Expected Order Date</th>								
                        <th>&nbsp;</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php 
                     foreach ($sales as $k => $sale) {
                        $OrderDate= date_create($sale->expectedCloseDate);
                        $salesPerson= Core::user_name($sale->salesPersonID, $DBConn); ?>
                        <tr>
                           <td><a href="<?php echo $base ."html/?s={$s}&ss=sales&p=home&saleid={$sale->salesCaseID}" ?>"><i class="uil-search-plus mx-2"></i></a><?php echo $sale->salesCaseName ?></td>
                           <td><?php echo $salesPerson ?></td>
                           <td><?php echo number_format($sale->salesCaseEstimate, '2', '.', ' '); ?></td>
                           <td class="text-capitalize"><?php echo $sale->statusLevel ?></td>
                           <td><?php echo $sale->levelPercentage .'%'; ?></td>
                           <td><?php echo $OrderDate->format(' d F Y ')  ?></td>											
                           <td><a href="" class="editSales" data-id="<?php echo $sale->salesCaseID; ?>"><i class="uil-edit" ></i></a></td>
                        </tr>												
                        <?php
                     }?>											
                  </tbody>
               </table>
            </div>        
            <?php
         } else{
            Alert::warning("There are no sales Cases for {$clientDetails->clientName} at the moment");
         }?>						
      </div>
   </div>
</div>

<div class="container-fluid mt-5 shadow-lg">
   <div class="card">
      <div class="card-header bg-light-blue">
         <h3 class="t400 mb-0 font-20">Projects</h3>
      </div>									
      <div class="card-body">
         <div class="col-12">
            <?php 	
            if ($projects) {?>
               <div class="table-responsive">
                  <table class="table table-sm table-hover table-stripped">
                     <thead class="table-dark">
                        <tr>
                           <th>Project Code</th>
                           <th> Case Name/project Name</th>
                           <th> Project duration</th>
                           <th>ProjectOwner</th>
                           <th class="text-end">Project Value</th>
                           <th class="text-end">Work Hr Estimate </th>										
                           <th style="min-width:100px">&nbsp;</th>
                        </tr>
                     </thead>
                     <tbody>
                        <?php
                        foreach ($projects as $proj => $projVal) {
                           // var_dump($projVal);
                           $phases= Projects::project_phases(array('projectID'=>$projVal->projectID, 'Suspended'=>"N"), false, $DBConn);
                           $workHours =0;
                           if ($phases) {
                              foreach ($phases as $key => $phase) {
                                 $workHours+=$phase->phaseWorkHrs;
                              }
                           }
                           $projectOwner= Core::user_name($projVal->projectOwnerID, $DBConn);
                           $allocatedWorkHours= !empty($projVal->allocatedWorkHours) ? $projVal->allocatedWorkHours : 'not Set';
                           $projectValue = number_format($projVal->projectValue, 2, '.', ' '); ?>
                           <tr>
                              <td ><a href='<?php echo "{$base}html/?s={$s}&ss=projects&p=project&pid={$projVal->projectID}" ?>'><i class='uil-search-plus me-3'></i></a><?php  echo "{$projVal->projectCode}" ?> </td>
                              <td><?php echo "{$projVal->projectName}"; ?></td>
                              <td><?php echo "{$projVal->projectStart} to  {$projVal->projectClose}" ?></td>
                              <td> <?php echo "{$projectOwner}" ?></td>
                              <td class='text-end'><?php echo "Kes {$projectValue}" ?></td>
                              <td class='text-end'><?php echo "{$workHours}" ?></td>											
                              <td class='text-end'><a href='' class="editProject" data-id='<?php echo $projVal->projectID ?>'><i class='uil-edit'></i></a>
                           </tr>											
                           <?php													
                        }?>
                     </tbody>
                  </table>
               </div>        
               <?php
            } else {
               Alert::warning("There are no projects commissioned for this client");
            } ?>														
         </div>							
      </div>						
   </div>						
</div>