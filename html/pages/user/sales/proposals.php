<?php
if(!$isValidUser) {
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}


// //var_dump($userDetails);
$employeeID=(isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) : $userDetails->ID;
$employeeDetails = Data::users(array('ID'=>$employeeID), true, $DBConn);
// //var_dump($employeeDetails);

$allEmployees = Data::users([], false, $DBConn);

$orgDataID= isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : $employeeDetails->orgDataID;
$entityID= isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : $employeeDetails->entityID;
$clients = Client::clients(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
$businessUnits = Data::business_units(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
$allOrgs = Admin::organisation_data_mini([], false, $DBConn);
$salesCases = Sales::sales_cases(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);

$proposals = Sales::proposal_full(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
// var_dump($proposals);

// Ensure $proposals is an array to prevent warnings
if (!is_array($proposals)) {
    $proposals = [];
}

$last30DaysProposals = array_filter($proposals, function($proposal) {
    return strtotime($proposal->DateAdded) >= strtotime('-30 days');
});

$proposalsValue = array_reduce($last30DaysProposals, function($carry, $proposal) {
    return $carry + $proposal->proposalValue;
}, 0);
//  var_dump($proposalsValue);
// Calculate the estimated value for this month
// Assuming the value is evenly distributed over the month
$estimateThisMonth = $proposalsValue / 30; // Assuming the value is evenly distributed over the month



// var_dump($last30DaysProposals);


//var_dump($salesCases);
?>
<script>
   document.addEventListener("DOMContentLoaded", function(event) {
      let clients = <?= json_encode($clients) ?>;
      let salesCases = <?= json_encode($salesCases) ?>;
      let proposals = <?= json_encode($proposals) ?>;


   });
</script>
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb border-bottom">
   <h1 class="page-title fw-medium fs-24 mb-0">Proposals</h1>
   <div class="ms-md-1 ms-0">
      <nav>
         <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $s ?></a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $ss ?></a></li>
            <li class="breadcrumb-item active d-inline-flex" aria-current="page">proposals</li>
         </ol>
      </nav>
   </div>
</div>

<div class="container-fluid ">
   <div class="card card-body col-md-12 my-4">
      <div class="card   alert  alert-dismissible fade show border-0" role="alert">
         <div class="row">
            <div  class="col-md">
               <h4 class="mb-0 t300 font-22">Proposals This Month <small class="fs-14 text-primary"> (Last 30 Days)</small> </h4>
               <div class="col-md border-end">
                  <div class="font-22">
                  <!-- <span class="font-14">KES</span> -->
                     <?php echo  isset($last30DaysProposals) ? number_format(count($last30DaysProposals), 2, '.', ' ')  : 0 ?>
                     <span class="font-14"> Proposals</span>

                  </div>

               </div>
            </div>

            <div class="col-md">
               <h4 class="mb-0 t300 font-22">Estimated  Value </h4>
               <div class="col-md border-end">
                  <div class="font-22">
                     <span class="font-14">KES</span>
                     <?php echo (isset($proposalsValue) && $proposalsValue !== '') ? number_format($proposalsValue, 2, '.', ' ') :'0.00'; ?>
                  </div>
               </div>
            </div>

            <div class="col-md">
               <h4 class="mb-0 t300 font-22">Need Attention</h4>
               <div class="col-md border-end">
                  <div class="font-22 clientsVal "><?php echo (isset($clientsWithProjects) && !empty($clientsWithProjects)) ? count($clientsWithProjects) : 0; ?> Cases</div>
               </div>
            </div>

            <div class="col-md">
               <h4 class="mb-0 t300 font-22">Hitrate This month</h4>
               <div class="col-md border-end">
                  <div class="font-22 clientsVal "><?php echo (isset($clientsWithProjects) && !empty($clientsWithProjects)) ? count($clientsWithProjects) : 0; ?> %</div>
               </div>
            </div>
         </div>

         <button type="button"  class="btn-close nobg" >
            <a tabindex="0"  role="button" data-bs-toggle="popover" data-trigger="focus" title="Organize your work with projects" data-bg-content="Before you start tracking your work, create projects, assign clients, and add tasks. You can choose from advanced billing rates, set up approval workflow, add custom budgets, and set task estimates. Assign tasks to your team and monitor allocated time."><i class="icon-cog"></i></a>
         </button>

      </div>
   </div>
</div>
<div class="container-fluid">



   <div class="card custom-card">
      <div class="card-header justify-content-between">
         <h3 class="card-title">Proposals</h3>
         <div class="card-options">
            <button type="button" class="btn btn-success btn-sm me-2" data-bs-toggle="modal" data-bs-target="#proposalCreationWizardModal">
               <i class="ri-magic-line me-1"></i>Create Proposal (Wizard)
            </button>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manage_proposal_modal">Add Proposal</button>
            <button type="button" class="btn btn-secondary btn-sm" id="filterBtn">Filter</button>
         </div>
      </div>
      <div class="card-body">
         <div class="table-responsive">
            <table class="table table-bordered table-striped table-vcenter js-dataTable-full table-sm" id="proposalsTable">
               <thead>
                  <tr>
                     <th class="text-center">#</th>
                     <th>Proposal Title</th>
                     <th>Client</th>
                     <th>Sales Case</th>
                     <th>Proposal deadline</th>
                     <th> Owner</th>
                     <th>Value</th>
                     <th>Status</th>


                     <th>Actions</th>
                  </tr>
               </thead>
               <tbody id="proposalsTableBody">
                  <?php

                  if (isset($proposals) && !empty($proposals)) {
                     foreach ($proposals as $key => $proposal) {
                        ?>
                        <tr id="<?php echo $proposal->proposalID; ?>">
                           <td class="text-center"><?php echo $key + 1; ?></td>
                           <td>
                              <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=proposal_details&prID={$proposal->proposalID}" ?>"
                                 class="text-primary"
                                 data-id="<?php echo $proposal->proposalID; ?>"
                                 data-title="<?php echo $proposal->proposalTitle; ?>"
                                 data-client="<?php echo $proposal->clientID; ?>"
                                 data-salescase="<?php echo $proposal->salesCaseID; ?>"
                                 data-deadline="<?php echo $proposal->proposalDeadline; ?>"
                                 data-value="<?php echo $proposal->proposalValue; ?>"
                                 data-status="<?php echo $proposal->proposalStatusID; ?>"
                                 data-description="<?php echo $proposal->proposalDescription; ?>"
                                 data-comments="<?php echo $proposal->proposalComments; ?>"
                              >
                              <?= $proposal->proposalTitle ?>
                              </a>
                           </td>
                           <td><?php echo $proposal->clientName; ?></td>
                           <td><?php echo $proposal->salesCaseName; ?></td>
                           <td><?php echo Utility::date_format($proposal->proposalDeadline); ?></td>
                           <td><?php echo $proposal->employeeName; ?></td>
                           <td><?php echo number_format($proposal->proposalValue, 2, '.', ' '); ?></td>
                           <td><?php echo $proposal->proposalStatusName; ?></td>
                           <td class='text-end'>
                              <button
                                 type="button"
                                 class="btn btn-primary btn-sm"
                                 data-bs-toggle="modal"
                                 data-bs-target="#manage_proposal_modal"
                                 data-id="<?php echo $proposal->proposalID; ?>"
                                 data-title="<?php echo $proposal->proposalTitle; ?>"
                                 data-client="<?php echo $proposal->clientID; ?>"
                                 data-salescase="<?php echo $proposal->salesCaseID; ?>"
                                 data-deadline="<?php echo $proposal->proposalDeadline; ?>"
                                 data-value="<?php echo $proposal->proposalValue; ?>"
                                 data-status="<?php echo $proposal->proposalStatusID; ?>"
                                 data-description="<?php echo $proposal->proposalDescription; ?>"
                                 data-comments="<?php echo $proposal->proposalComments; ?>">
                                 Edit
                              </button>
                              <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#delete_proposal_modal" data-id="<?php echo $proposal->proposalID; ?>">Delete</button>
                           </td>


                        </tr>
                        <?php
                     }
                  } else {
                     ?>
                     <tr><td colspan="11" class="text-center">No proposals found.</td></tr>
                  <?php } ?>
               </tbody>
            </table>
         </div>

      </div>
   </div>
</div>
<?php
   echo Utility::form_modal_header("manage_proposal_modal", "sales/manage_proposal.php", "Proposal", array('modal-lg', 'modal-dialog-centered'), $base);
   include "includes/scripts/sales/modals/manage_proposal_modal.php";
   echo Utility::form_modal_footer('Save Proposal', 'saveProposal',  ' btn btn-success btn-sm', true);

   // Proposal Creation Wizard Modal
   echo Utility::form_modal_header("proposalCreationWizardModal", "", "Create Proposal - Wizard", array('modal-xl', 'modal-dialog-centered'), $base);
   include "includes/scripts/sales/modals/proposal_creation_wizard.php";
   echo Utility::form_modal_footer_no_buttons();

   // Initialize all proposal date pickers with Flatpickr
   include "includes/scripts/sales/proposal_date_pickers.php";
?>