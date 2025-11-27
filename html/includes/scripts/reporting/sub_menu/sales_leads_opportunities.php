<?php
// This is a new sub-menu reporting file for 'sales_leads_opportunities.php'
// Add your content here.
$sales = Sales::sales_case_full([], false, $DBConn);
// var_dump($sales[0]);

//filter the sales by saleStage
$businessDevelopmentLeads = array_filter($sales, function($sale) {
   return $sale->saleStage == 'business_development';
});
// var_dump($businessDevelopmentLeads);
//filter the sales by saleStage for oppotunities   
$opportunities = array_filter($sales, function($sale) {
   return $sale->saleStage == 'opportunities';
});
// var_dump($opportunities);
//filter the sales by saleStage for won
$won = array_filter($sales, function($sale) {
   return $sale->saleStage == 'won';
});
// var_dump($sales);
//filter the sales by saleStage for lost
$lost = array_filter($sales, function($sale) {
   return $sale->saleStage == 'lost';
});
// var_dump($lost);
//filter the sales by saleStage for closed_won
$closed_won = array_filter($sales, function($sale) {
   return $sale->saleStage == 'closed_won';
});
// var_dump($closed_won);
//filter the sales by saleStage for closed_lost
$closed_lost = array_filter($sales, function($sale) {
   return $sale->saleStage == 'closed_lost';
});
// var_dump($closed_lost);


?>
<?php
// Calculate metrics for Business Development Leads
$bdLeadsCount = count($businessDevelopmentLeads);
$bdLeadsValue = array_reduce($businessDevelopmentLeads, function($sum, $item) {
    return $sum + ($item->salesCaseEstimate ?: 0);
}, 0);

// Calculate metrics for Opportunities
$opportunitiesCount = count($opportunities);
$opportunitiesValue = array_reduce($opportunities, function($sum, $item) {
    return $sum + ($item->salesCaseEstimate ?: 0);
}, 0);

// Calculate metrics for Won
$wonCount = count($won);
$wonValue = array_reduce($won, function($sum, $item) {
    return $sum + ($item->salesCaseEstimate ?: 0);
}, 0);

// Calculate metrics for Lost
$lostCount = count($lost);
$lostValue = array_reduce($lost, function($sum, $item) {
    return $sum + ($item->salesCaseEstimate ?: 0);
}, 0);

// Calculate metrics for Closed Won
$closedWonCount = count($closed_won);
$closedWonValue = array_reduce($closed_won, function($sum, $item) {
    return $sum + ($item->salesCaseEstimate ?: 0);
}, 0);

// Calculate metrics for Closed Lost
$closedLostCount = count($closed_lost);
$closedLostValue = array_reduce($closed_lost, function($sum, $item) {
    return $sum + ($item->salesCaseEstimate ?: 0);
}, 0);
?>
<div class="container-fluid bg-light">
    <div class="row my-4">
        <!-- Business Development Leads Widget -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-white shadow">
                <div class="card-header bg-info-subtle">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-info">
                            <i class="ri-lightbulb-line me-2"></i>Business Development Leads
                        </h6>
                        <button class="btn btn-sm btn-outline-info" type="button" data-bs-toggle="collapse" data-bs-target="#bdLeadsDetails" aria-expanded="false">
                            <i class="ri-eye-line"></i> Details
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar-sm bg-info-subtle rounded">
                                <span class="avatar-title bg-info-subtle text-info rounded">
                                    <i class="ri-lightbulb-line fs-20"></i>
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h4 class="mb-0">KES <?= number_format($bdLeadsValue, 0) ?></h4>
                            <small class="text-muted"><?= $bdLeadsCount ?> cases</small>
                        </div>
                    </div>
                    
                    <!-- Collapsible Details Table -->
                    <div class="collapse mt-3" id="bdLeadsDetails">
                        <div class="card card-body bg-light">
                            <h6 class="mb-3">Business Development Leads Details</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Case Name</th>
                                            <th>Client</th>
                                            <th>Sales Person</th>
                                            <th>Value</th>
                                            <th>Probability</th>
                                            <th>Expected Close</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($businessDevelopmentLeads as $case): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($case->salesCaseName) ?></td>
                                            <td><?= htmlspecialchars($case->clientName) ?></td>
                                            <td><?= htmlspecialchars($case->salesPersonName) ?></td>
                                            <td>KES <?= number_format($case->salesCaseEstimate, 0) ?></td>
                                            <td>
                                                <span class="badge bg-info-subtle text-info"><?= $case->probability ?>%</span>
                                            </td>
                                            <td><?= $case->expectedCloseDate && $case->expectedCloseDate != '0000-00-00' ? $case->expectedCloseDate : '-' ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Opportunities Widget -->
         <div class="col-xl-3 col-md-6 mb-3">
               <div class="card bg-white shadow">
                  <div class="card-header bg-primary-subtle">
                     <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-primary">
                           <i class="ri-briefcase-line me-2"></i>Opportunities
                        </h6>
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#oppDetails" aria-expanded="false">
                           <i class="ri-eye-line"></i> Details
                        </button>
                     </div>
                  </div>
                  <div class="card-body">
                     <div class="d-flex align-items-center">
                           <div class="flex-shrink-0">
                              <div class="avatar-sm bg-primary-subtle rounded">
                                 <span class="avatar-title bg-primary-subtle text-primary rounded">
                                       <i class="ri-briefcase-line fs-20"></i>
                                 </span>
                              </div>
                           </div>
                           <div class="flex-grow-1 ms-3">
                              <h4 class="mb-0">KES <?= number_format($opportunitiesValue, 0) ?></h4>
                              <small class="text-muted"><?= $opportunitiesCount ?> cases</small>
                           </div>
                     </div>
                     
                     <!-- Collapsible Details Table -->
                     <div class="collapse mt-3" id="oppDetails">
                        <div class="card card-body bg-light">
                           <h6 class="mb-3">Opportunities Details</h6>
                           <div class="table-responsive">
                              <table class="table table-sm table-hover">
                                 <thead>
                                    <tr>
                                       <th>Case Name</th>
                                       <th>Client</th>
                                       <th>Sales Person</th>
                                       <th>Value</th>
                                       <th>Probability</th>
                                       <th>Expected Close</th>
                                    </tr>
                                 </thead>
                                 <tbody>
                                    <?php foreach ($opportunities as $case): ?>
                                    <tr>
                                       <td><?= htmlspecialchars($case->salesCaseName) ?></td>
                                       <td><?= htmlspecialchars($case->clientName) ?></td>
                                       <td><?= htmlspecialchars($case->salesPersonName) ?></td>
                                       <td>KES <?= number_format($case->salesCaseEstimate, 0) ?></td>
                                       <td>
                                          <span class="badge bg-primary-subtle text-primary"><?= $case->probability ?>%</span>
                                       </td>
                                       <td><?= $case->expectedCloseDate && $case->expectedCloseDate != '0000-00-00' ? $case->expectedCloseDate : '-' ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                 </tbody>
                              </table>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
         </div>

         <!-- Won Widget -->
         <div class="col-xl-3 col-md-6 mb-3">
               <div class="card bg-white">
                  <div class="card-header bg-success-subtle">
                     <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-success">
                           <i class="ri-trophy-line me-2"></i>Won Cases
                        </h6>
                        <button class="btn btn-sm btn-outline-success" type="button" data-bs-toggle="collapse" data-bs-target="#wonDetails" aria-expanded="false">
                           <i class="ri-eye-line"></i> Details
                        </button>
                     </div>
                  </div>
                  <div class="card-body">
                     <div class="d-flex align-items-center">
                           <div class="flex-shrink-0">
                              <div class="avatar-sm bg-success-subtle rounded">
                                 <span class="avatar-title bg-success-subtle text-success rounded">
                                       <i class="ri-trophy-line fs-20"></i>
                                 </span>
                              </div>
                           </div>
                           <div class="flex-grow-1 ms-3">
                              <h4 class="mb-0">KES <?= number_format($wonValue, 0) ?></h4>
                              <small class="text-muted"><?= $wonCount ?> cases</small>
                           </div>
                     </div>
                     
                     <!-- Collapsible Details Table -->
                     <div class="collapse mt-3" id="wonDetails">
                        <div class="card card-body bg-light">
                           <h6 class="mb-3">Won Cases Details</h6>
                           <div class="table-responsive">
                              <table class="table table-sm table-hover">
                                 <thead>
                                    <tr>
                                       <th>Case Name</th>
                                       <th>Client</th>
                                       <th>Sales Person</th>
                                       <th>Value</th>
                                       <th>Probability</th>
                                       <th>Date Closed</th>
                                    </tr>
                                 </thead>
                                 <tbody>
                                    <?php foreach ($won as $case): ?>
                                    <tr>
                                       <td><?= htmlspecialchars($case->salesCaseName) ?></td>
                                       <td><?= htmlspecialchars($case->clientName) ?></td>
                                       <td><?= htmlspecialchars($case->salesPersonName) ?></td>
                                       <td>KES <?= number_format($case->salesCaseEstimate, 0) ?></td>
                                       <td>
                                          <span class="badge bg-success-subtle text-success"><?= $case->probability ?>%</span>
                                       </td>
                                       <td><?= $case->dateClosed && $case->dateClosed != '0000-00-00' ? $case->dateClosed : '-' ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                 </tbody>
                              </table>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
         </div>

         <!-- Lost Widget -->
         <div class="col-xl-3 col-md-6 mb-3">
               <div class="card bg-white">
                  <div class="card-header bg-danger-subtle">
                     <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 text-danger">
                           <i class="ri-emotion-sad-line me-2"></i>Lost Cases
                        </h6>
                        <button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="collapse" data-bs-target="#lostDetails" aria-expanded="false">
                           <i class="ri-eye-line"></i> Details
                        </button>
                     </div>
                  </div>
                  <div class="card-body">
                     <div class="d-flex align-items-center">
                           <div class="flex-shrink-0">
                              <div class="avatar-sm bg-danger-subtle rounded">
                                 <span class="avatar-title bg-danger-subtle text-danger rounded">
                                       <i class="ri-emotion-sad-line fs-20"></i>
                                 </span>
                              </div>
                           </div>
                           <div class="flex-grow-1 ms-3">
                              <h4 class="mb-0">KES <?= number_format($lostValue, 0) ?></h4>
                              <small class="text-muted"><?= $lostCount ?> cases</small>
                           </div>
                     </div>
                     
                     <!-- Collapsible Details Table -->
                     <div class="collapse mt-3" id="lostDetails">
                        <div class="card card-body bg-light">
                           <h6 class="mb-3">Lost Cases Details</h6>
                           <div class="table-responsive">
                              <table class="table table-sm table-hover">
                                 <thead>
                                    <tr>
                                       <th>Case Name</th>
                                       <th>Client</th>
                                       <th>Sales Person</th>
                                       <th>Value</th>
                                       <th>Probability</th>
                                       <th>Date Closed</th>
                                    </tr>
                                 </thead>
                                 <tbody>
                                    <?php foreach ($lost as $case): ?>
                                    <tr>
                                       <td><?= htmlspecialchars($case->salesCaseName) ?></td>
                                       <td><?= htmlspecialchars($case->clientName) ?></td>
                                       <td><?= htmlspecialchars($case->salesPersonName) ?></td>
                                       <td>KES <?= number_format($case->salesCaseEstimate, 0) ?></td>
                                       <td>
                                          <span class="badge bg-danger-subtle text-danger"><?= $case->probability ?>%</span>
                                       </td>
                                       <td><?= $case->dateClosed && $case->dateClosed != '0000-00-00' ? $case->dateClosed : '-' ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                 </tbody>
                              </table>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
         </div>
      </div>

</div>
