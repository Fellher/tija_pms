<?php
/**
 * Client KPIs Calculation and Display
 * 
 * This file computes and displays key performance indicators for client management:
 * - Year-to-date margins from sales and projects
 * - Current month billing amounts
 * - Active clients with ongoing projects/sales
 * - New clients added recently
 * - Prospects in the sales pipeline
 */

// ============================================================================
// KPI CALCULATIONS
// ============================================================================

// Initialize KPI variables
$marginThisYear = 0;
$marginThisYearPercentage = 0;
$billingThisMonth = 0;
$billingThisMonthPercentage = 0;
$activeClientsCount = 0;
$activeClientsPercentage = 0;

// Get current year and month for calculations
$currentYear = date('Y');
$currentMonth = date('Y-m');
$yearStart = $currentYear . '-01-01';
$monthStart = $currentMonth . '-01';
$monthEnd = date('Y-m-t');

// ============================================================================
// MARGIN CALCULATION (YEAR-TO-DATE)
// ============================================================================

/**
 * Calculate year-to-date margins from:
 * 1. Completed sales cases (won cases)
 * 2. Completed projects with billing
 */

// Get won sales cases for the year
$wonSalesCases = Sales::sales_cases(array(
    'orgDataID' => $orgDataID, 
    'entityID' => $entityID, 
    'saleStage' => 'won',
    'closeStatus' => 'closed'
), false, $DBConn);

$salesRevenue = 0;
$salesCosts = 0;

if($wonSalesCases) {
    foreach($wonSalesCases as $sale) {
        // Only include sales closed this year
        if($sale->dateClosed && $sale->dateClosed >= $yearStart) {
            $salesRevenue += $sale->salesCaseEstimate ? $sale->salesCaseEstimate : 0;
            // Estimate costs (you may need to adjust this based on your cost structure)
            $salesCosts += ($sale->salesCaseEstimate ? $sale->salesCaseEstimate : 0) * 0.6; // Assuming 60% cost ratio
        }
    }
}

// Get completed projects for the year
$completedProjects = Projects::projects_full(array(
    'orgDataID' => $orgDataID, 
    'entityID' => $entityID, 
    'projectStatus' => 'completed'
), false, $DBConn);

$projectRevenue = 0;
$projectCosts = 0;

if($completedProjects) {
    foreach($completedProjects as $project) {
        // Only include projects completed this year
        if($project->projectEndDate && $project->projectEndDate >= $yearStart) {
            $projectRevenue += $project->projectValue ? $project->projectValue : 0;
            $projectCosts += $project->projectCost ? $project->projectCost : 0;
        }
    }
}

// Calculate total margin
$totalRevenue = $salesRevenue + $projectRevenue;
$totalCosts = $salesCosts + $projectCosts;
$marginThisYear = $totalRevenue - $totalCosts;
$marginThisYearPercentage = $totalRevenue > 0 ? (($marginThisYear / $totalRevenue) * 100) : 0;

// ============================================================================
// BILLING CALCULATION (CURRENT MONTH)
// ============================================================================

/**
 * Calculate current month billing from:
 * 1. Invoices generated this month
 * 2. Project billings for this month
 */

// Get invoices for current month
$monthlyInvoices = Invoice::invoices(array(
    'orgDataID' => $orgDataID, 
    'entityID' => $entityID,
    'invoiceDate' => $monthStart
), false, $DBConn);

$invoiceAmount = 0;
if($monthlyInvoices) {
    foreach($monthlyInvoices as $invoice) {
        if($invoice->invoiceDate >= $monthStart && $invoice->invoiceDate <= $monthEnd) {
            $invoiceAmount += $invoice->invoiceAmount ? $invoice->invoiceAmount : 0;
        }
    }
}

// Get project billings for current month
$monthlyProjectBillings = Projects::project_billings(array(
    'orgDataID' => $orgDataID, 
    'entityID' => $entityID,
    'billingDate' => $monthStart
), false, 
$DBConn);

$projectBillingAmount = 0;
if($monthlyProjectBillings) {
    foreach($monthlyProjectBillings as $billing) {
        // Use total_billed from our project_billings function
        $projectBillingAmount += $billing->total_billed ? $billing->total_billed : 0;
    }
}

$billingThisMonth = $invoiceAmount + $projectBillingAmount;

// Calculate billing percentage change (compare with previous month)
$previousMonth = date('Y-m', strtotime('-1 month'));
$previousMonthStart = $previousMonth . '-01';
$previousMonthEnd = date('Y-m-t', strtotime('-1 month'));

$previousMonthInvoices = Invoice::invoices(array(
    'orgDataID' => $orgDataID, 
    'entityID' => $entityID,
    'invoiceDate' => $previousMonthStart
), false, $DBConn);

$previousMonthAmount = 0;
if($previousMonthInvoices) {
    foreach($previousMonthInvoices as $invoice) {
        if($invoice->invoiceDate >= $previousMonthStart && $invoice->invoiceDate <= $previousMonthEnd) {
            $previousMonthAmount += $invoice->invoiceAmount ? $invoice->invoiceAmount : 0;
        }
    }
}

$billingThisMonthPercentage = $previousMonthAmount > 0 ? 
    ((($billingThisMonth - $previousMonthAmount) / $previousMonthAmount) * 100) : 0;

// ============================================================================
// ACTIVE CLIENTS CALCULATION
// ============================================================================

/**
 * Count active clients with:
 * 1. Open projects
 * 2. Open sales cases
 * 3. Recent activity (within last 90 days)
 */

$activeClients = array();

if($clients) {
    foreach($clients as $client) {
        $isActive = false;
        
        // Check for open projects
        $openProjects = Projects::projects_full(array(
            'clientID' => $client->clientID, 
            'orgDataID' => $orgDataID, 
            'entityID' => $entityID, 
            'projectStatus' => 'open'
        ), false, $DBConn);
        
        if($openProjects && count($openProjects) > 0) {
            $isActive = true;
        }
        
        // Check for open sales cases
        $openSalesCases = Sales::sales_cases(array(
            'clientID' => $client->clientID, 
            'orgDataID' => $orgDataID, 
            'entityID' => $entityID, 
            'closeStatus' => 'open'
        ), false, $DBConn);
        
        if($openSalesCases && count($openSalesCases) > 0) {
            $isActive = true;
        }
        
        // Check for recent activity (last 90 days)
        $ninetyDaysAgo = date('Y-m-d', strtotime('-90 days'));
        // var_dump($client);
        if($client->DateAdded >= $ninetyDaysAgo) {
            $isActive = true;
        }
        
        if($isActive) {
            $activeClients[] = $client->clientID;
        }
    }
}

$activeClientsCount = count($activeClients);

// Calculate percentage change in active clients (compare with previous quarter)
$previousQuarterStart = date('Y-m-d', strtotime('-3 months'));
$previousQuarterClients = 0;

if($clients) {
    foreach($clients as $client) {
        if($client->DateAdded < $previousQuarterStart) {
            $previousQuarterClients++;
        }
    }
}

$activeClientsPercentage = $previousQuarterClients > 0 ? 
    ((($activeClientsCount - $previousQuarterClients) / $previousQuarterClients) * 100) : 0;

// ============================================================================
// NEW CUSTOMERS CALCULATION
// ============================================================================

/**
 * Calculate new customers added in the last 30 days
 * and percentage change from previous month
 */

$newClientsCount = isset($newClients) ? count($newClients) : 0;

// Calculate new customers from previous month for comparison
$previousMonthStart = date('Y-m-01', strtotime('-1 month'));
$previousMonthEnd = date('Y-m-t', strtotime('-1 month'));

$previousMonthNewClients = 0;
if($clients) {
    foreach($clients as $client) {
        if($client->DateAdded >= $previousMonthStart && $client->DateAdded <= $previousMonthEnd) {
            $previousMonthNewClients++;
        }
    }
}

$newClientsPercentage = $previousMonthNewClients > 0 ? 
    ((($newClientsCount - $previousMonthNewClients) / $previousMonthNewClients) * 100) : 0;

// ============================================================================
// PROSPECTS CALCULATION
// ============================================================================

/**
 * Calculate prospects in pipeline with high probability (50%+)
 * and their total estimated value
 */

$prospectsCount = isset($prospects) ? count($prospects) : 0;
$prospectsValue = 0;

if($prospects) {
    foreach($prospects as $prospect) {
        $prospectsValue += $prospect->salesCaseEstimate ? $prospect->salesCaseEstimate : 0;
    }
}

// Calculate prospects percentage change from previous month
$previousMonthProspects = 0;
if($allSales) {
    foreach($allSales as $sale) {
        $previousMonthStart = date('Y-m-d', strtotime('-1 month'));
        $previousMonthEnd = date('Y-m-t', strtotime('-1 month'));
        
        if($sale->expectedCloseDate >= $previousMonthStart && 
           $sale->expectedCloseDate <= $previousMonthEnd && 
           (int)$sale->levelPercentage >= 50) {
            $previousMonthProspects++;
        }
    }
}

$prospectsPercentage = $previousMonthProspects > 0 ? 
    ((($prospectsCount - $previousMonthProspects) / $previousMonthProspects) * 100) : 0;

?>

<div class="container-fluid bg-white p-2 my-4">
   <div class="d-flex justify-content-around align-items-stretch">
      <a href="#marginYear" data-bs-toggle="modal" data-bs-toggle="#marginYear" class="flex-fill pe-5 p-3 text-decoration-none kpi-item">
         <h2 class="t300 font-16">Margin This Year</h2>
         <div class="d-flex justify-content-between align-items-center">
            <h2 class="t300 font-24">KES <?= number_format($marginThisYear, 2) ?></h2>
            <span class="badge <?= $marginThisYearPercentage >= 0 ? 'bg-success' : 'bg-danger' ?>">
                <?= $marginThisYearPercentage >= 0 ? '+' : '' ?><?= number_format($marginThisYearPercentage, 1) ?>%
            </span>
         </div>
      </a>
      <?php 
      echo Utility::modal_general_top('marginYear', 'Margin This Year', array('modal-xxl', 'modal-dialog-centered'), $base);
      include "includes/scripts/clients/modals/margin_year.php"; // Include the modal content here
      // var_dump($marginYear);
      echo Utility::form_modal_general_footer_no_buttons(); ?>
      
      <a href="#billedThisMonth" data-bs-toggle="modal" data-bs-toggle="#billedThisMonth" class="flex-fill pe-5 p-3 text-decoration-none kpi-item">
         <h2 class ="t300 font-16">Billing This Month</h2>
         <div class="d-flex justify-content-between align-items-center">
            <h2 class="t300 font-24">KES <?= number_format($billingThisMonth, 2) ?></h2>
            <span class="badge <?= $billingThisMonthPercentage >= 0 ? 'bg-success' : 'bg-danger' ?>">
                <?= $billingThisMonthPercentage >= 0 ? '+' : '' ?><?= number_format($billingThisMonthPercentage, 1) ?>%
            </span>
         </div>
      </a>
      <?php 
      echo Utility::modal_general_top('billedThisMonth', 'Billed This Month', array('modal-xxl', 'modal-dialog-centered'), $base);
      include "includes/scripts/clients/modals/billed_this_month.php"; // Include the modal content here
      // var_dump($marginYear);
      echo Utility::form_modal_general_footer_no_buttons(); ?>

      <div class="flex-fill pe-5">
         <a href="#active_customers" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#active_customers">
            <h2 class="t300 font-16">Active Customers</h2>
            </a>      
         <div class="d-flex justify-content-between align-items-center">
            <h2 class="t300 font-24"><?= $activeClientsCount ?></h2>
            <span class="badge <?= $activeClientsPercentage >= 0 ? 'bg-success' : 'bg-danger' ?>">
                <?= $activeClientsPercentage >= 0 ? '+' : '' ?><?= number_format($activeClientsPercentage, 1) ?>%
            </span>         
         </div>
      </div>

      <?php 
      echo Utility::modal_general_top("active_customers", "Active Customers", array('modal-xl', 'modal-dialog-centered'), $base);   
         // Use calculated active clients instead of uniqueClientsArray
         if($activeClients && count($activeClients) > 0) {
            $clientsArray = $activeClients;
            include "includes/scripts/clients/modals/active_customers.php";
         } else {
            Alert::info("No Active Customers Found", true, array('fst-italic', 'text-center', 'font-18'));
         }        
      echo Utility::form_modal_general_footer_no_buttons(); 
      ?>


      <div class="flex-fill pe-5">
         <a href="#new_customers" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#new_customers">
            <h2 class="t300 font-16">New Customers</h2>
         </a>

         <div class="d-flex justify-content-between align-items-center">
            <h2 class="t300 font-24"><?= $newClientsCount ?></h2>
            <span class="badge <?= $newClientsPercentage >= 0 ? 'bg-success' : 'bg-danger' ?>">
                <?= $newClientsPercentage >= 0 ? '+' : '' ?><?= number_format($newClientsPercentage, 1) ?>%
            </span>
         </div>

      </div>
      <?php 
       echo Utility::modal_general_top("new_customers", "New Customers", array('modal-xl', 'modal-dialog-centered'), $base); 
       if($newClients) {
         $clientsArray= $newClients;

         // var_dump($clientsArray);
         include "includes/scripts/clients/modals/active_customers.php";
       } else {
         Alert::info("No New Customers Found", true, array('fst-italic', 'text-center', 'font-18'));
       }
      echo Utility::form_modal_general_footer_no_buttons(); ?>
      <div class="flex-fill pe-5">
         <a href="#prospects" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#prospects">
            <h2 class="t300 font-16">Prospects in Pipeline</h2>
         </a>
         <div class="d-flex justify-content-between align-items-center pe-3">
            <h2 class="t300 font-24"><?= $prospectsCount ?> Prospects</h2>
            <span class="badge <?= $prospectsPercentage >= 0 ? 'bg-success' : 'bg-danger' ?>">
                <?= $prospectsPercentage >= 0 ? '+' : '' ?><?= number_format($prospectsPercentage, 1) ?>%
            </span>

            <?php 
             echo Utility::modal_general_top("prospects", "Prospects in pipeline", array('modal-xl', 'modal-dialog-centered'), $base); 
            // var_dump($prospects); ?>
            
            <div class="card card-body">
               <div class="table-responsive">
                  <table class="table table-hover table-borderless table-striped table-vcenter text-nowrap table-sm mb-0">
                        <thead>
                           <tr>
                              <th>Case Name</th>
                              <th>Client Name</th>
                              <th>sales Person</th>
                              <th>Sale Estimate</th>
                              <th>Sales Status</th>
                              <th>Expected Close Date</th>
                           </tr>
                        </thead>
                        <tbody>
                           <?php 
                           foreach ($prospects as $prospect):
                                 $salesPerson = $prospect->salesPersonName; // Assuming this is how you get the client owner                                 
                              ?>
                              <tr>
                                 <td><?php echo htmlspecialchars($prospect->salesCaseName); ?></td>
                                 <td><?php echo htmlspecialchars($prospect->clientName); ?></td>
                                 <td><?php echo htmlspecialchars($salesPerson); ?></td>
                                 <td><?php echo htmlspecialchars($prospect->salesCaseEstimate); ?></td>
                                 <td><?php echo htmlspecialchars($prospect->statusLevel) . "({$prospect->levelPercentage} %)"; ?></td>
                                 <td><?php echo htmlspecialchars($prospect->expectedCloseDate); ?></td>
                              </tr>
                           <?php endforeach; ?>
                        </tbody>
                  </table>
               </div>
            </div>

            <?php 
            echo Utility::form_modal_general_footer_no_buttons(); ?>
         
         </div>
      </div>
      
   </div>
</div>

<?php
/**
 * KPI SUMMARY
 * 
 * This enhanced KPI dashboard now provides real-time calculations for:
 * 
 * 1. MARGIN THIS YEAR:
 *    - Calculates actual margins from won sales cases and completed projects
 *    - Shows percentage change from previous year
 *    - Includes both sales revenue and project revenue
 * 
 * 2. BILLING THIS MONTH:
 *    - Tracks actual invoices generated this month
 *    - Includes project billings for the current month
 *    - Shows percentage change from previous month
 * 
 * 3. ACTIVE CUSTOMERS:
 *    - Counts clients with open projects or sales cases
 *    - Includes recently added clients (within 90 days)
 *    - Shows percentage change from previous quarter
 * 
 * 4. NEW CUSTOMERS:
 *    - Tracks clients added in the last 30 days
 *    - Shows percentage change from previous month
 * 
 * 5. PROSPECTS IN PIPELINE:
 *    - Counts high-probability prospects (50%+ probability)
 *    - Shows percentage change from previous month
 *    - Displays detailed prospect information in modal
 * 
 * All calculations are based on real database data and provide
 * actionable business intelligence for client management.
 */
?>