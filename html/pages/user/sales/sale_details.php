<?php
/**
 * Enterprise Sales Case Details Page
 * Comprehensive CRM view for managing sales opportunities
 *
 * Features:
 * - Tab-based navigation for different CRM sections
 * - Timeline/activity feed
 * - Notes and comments
 * - Documents management
 * - Metrics dashboard
 * - Quick actions
 * - Enterprise-level UI/UX
 *
 * @package    Tija CRM
 * @subpackage Sales Management
 * @version    2.0.0
 */

// ============================================================================
// AUTHENTICATION & VALIDATION
// ============================================================================


if(!$isValidUser) {
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}

// ============================================================================
// DATA INITIALIZATION
// ============================================================================
$employeeID = (isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) : $userDetails->ID;
$employeeDetails = Data::users(array('ID'=>$employeeID), true, $DBConn);
$allEmployees = Employee::employees([], false, $DBConn);
$orgDataID = isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : $employeeDetails->orgDataID;
$entityID = isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : $employeeDetails->entityID;
$salesCaseID = isset($_GET['saleid']) ? Utility::clean_string($_GET['saleid']) : "";

// Validate sales case ID
if(!$salesCaseID) {
   Alert::info("No sales case found", true, array('fst-italic', 'text-center', 'font-18'));
   return;
}

// Fetch sales case details
$salesCaseDetails = Sales::sales_case_mid(array('salesCaseID'=>$salesCaseID, 'Suspended'=>'N'), true, $DBConn);
if(!$salesCaseDetails) {
   Alert::error("Sales case not found or has been suspended", true, array('fst-italic', 'text-center', 'font-18'));
   return;
}

// Set entity ID from sales case if not provided
if(!$entityID) {
   $entityID = $salesCaseDetails->entityID;
}

$getString .= "&saleid={$salesCaseID}";

// ============================================================================
// RELATED DATA FETCHING
// ============================================================================
$salesPersonDetails = Core::get_user_name_initials($salesCaseDetails->salesPersonID, $DBConn);
$clientContacts = Client::client_contact_full(array('clientID'=>$salesCaseDetails->clientID), false, $DBConn);
$clients = Client::clients(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
$businessUnits = Data::business_units(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
$leadSources = Sales::lead_sources(array('Suspended'=>'N', 'orgDataID'=>$orgDataID), false, $DBConn);
$statusLevels = Sales::sales_status_levels(array('Suspended'=>'N'), false, $DBConn);
$workTypes = Work::work_types(array('Suspended'=>'N'), false, $DBConn);
$activityCategories = Schedule::activity_categories([], false, $DBConn);
$activityTypes = Schedule::tija_activity_types([], false, $DBConn);
$salesCases = Sales::sales_case_mid(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID, 'Suspended'=>'N'), false, $DBConn);
$sectors = Data::tija_sectors(array(), false, $DBConn);
$industrySectors = $sectors; // Alias for compatibility
$industries = Data::tija_industry(array(), false, $DBConn);
$countries = Data::countries(array(), false, $DBConn);
$employeesCategorised = Employee::categorise_employee($allEmployees, 'jobTitle');

// Additional data for modals and includes
$clientContactTypes = Client::contact_types(array(), false, $DBConn);
$clientAddresses = Client::client_address(array('clientID'=>$salesCaseDetails->clientID), false, $DBConn);
$projects = array(); // Initialize empty if not in project context

// Fetch contact details if available
if($salesCaseDetails->salesCaseContactID) {
   $clientContact = Client::client_contact_full(array('clientContactID'=>$salesCaseDetails->salesCaseContactID), true, $DBConn);
   if($clientContact) {
      $salesCaseDetails->contactName = $clientContact->contactName;
      $salesCaseDetails->contactEmail = $clientContact->contactEmail;
   } else {
      $salesCaseDetails->contactName = "";
      $salesCaseDetails->contactEmail = "";
   }
}

// Fetch activities for timeline
$activities = Schedule::tija_activities(array('salesCaseID'=>$salesCaseID), false, $DBConn);
$activityCount = $activities ? count($activities) : 0;

// Fetch proposals if status is Proposal
$proposals = array();
if($salesCaseDetails->statusLevel === "Proposal") {
   $proposals = Sales::proposal_full(['salesCaseID'=>$salesCaseID, 'entityID'=>$entityID], false, $DBConn);
}

// Calculate metrics
$daysUntilClose = null;
if(!empty($salesCaseDetails->expectedCloseDate) && $salesCaseDetails->expectedCloseDate != '0000-00-00') {
   $daysUntilClose = ceil((strtotime($salesCaseDetails->expectedCloseDate) - time()) / (60 * 60 * 24));
}
// Weighted value will be calculated after probability is set from status level

// Status levels
$statusFilter = array('Suspended'=>'N', 'orgDataID'=> $salesCaseDetails->orgDataID, 'entityID'=>$entityID);
$statusLevels = Sales::sales_status_levels($statusFilter, false, $DBConn);

// Set probability from status level if not available or zero
if (empty($salesCaseDetails->probability) || $salesCaseDetails->probability == 0) {
    if ($statusLevels && is_array($statusLevels) && !empty($salesCaseDetails->saleStatusLevelID)) {
        foreach ($statusLevels as $statusLevel) {
            if ($statusLevel->saleStatusLevelID == $salesCaseDetails->saleStatusLevelID) {
                $salesCaseDetails->probability = $statusLevel->levelPercentage ?? 0;
                break;
            }
        }
    }
}

// Recalculate weighted value with updated probability
$weightedValue = floatval($salesCaseDetails->salesCaseEstimate ?? 0) * (floatval($salesCaseDetails->probability ?? 0) / 100);

// Set variables for included files
$clientID = $salesCaseDetails->clientID;
$salesCaseID = $salesCaseDetails->salesCaseID;
$activityTitle = "Sales Activities ( <span class='text-primary fst-italic'>{$salesCaseDetails->salesCaseName}</span> )";
$addActivity = true;
$activitySegment = 'sales';

// ============================================================================
// MODALS
// ============================================================================
echo Utility::form_modal_header("manageSale", "sales/manage_sale.php", "Manage Sale", array('modal-lg', 'modal-dialog-centered'), $base);
include "includes/scripts/sales/modals/manage_sale.php";
echo Utility::form_modal_footer('Save Sale', 'saveSale',  ' btn btn-success btn-sm', true);

echo Utility::form_modal_header("manageActivityModal", "sales/manage_activity_wizard.php", "Manage Activity", array('modal-xl', 'modal-dialog-centered', 'modal-dialog-scrollable'), $base);
include "includes/scripts/sales/modals/manage_sale_activity.php";
// Note: Modal footer is handled within the wizard navigation
echo '</div></form></div></div></div>'; // Close modal-body, form, modal-content, modal-dialog, modal

echo Utility::form_modal_header("manageSalesDocumentModal", "sales/manage_sales_document.php", "Upload Sales Document", array('modal-lg', 'modal-dialog-centered'), $base);
include "includes/scripts/sales/modals/manage_sales_document.php";
echo Utility::form_modal_footer('Upload Document', 'submitSalesDocument',  ' btn btn-success btn-sm', true);

?>

<!-- ============================================================================
     PAGE HEADER - Enterprise Design
     ============================================================================ -->
<div class="page-header-enterprise mb-4">
   <div class="d-md-flex d-block align-items-start justify-content-between">
      <!-- Left Section: Title & Breadcrumb -->
      <div class="flex-grow-1 mb-3 mb-md-0">
         <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb mb-0">
               <li class="breadcrumb-item"><a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=home" ?>">Sales</a></li>
               <li class="breadcrumb-item"><a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=home&state={$salesCaseDetails->saleStage}" ?>"><?= ucfirst($salesCaseDetails->saleStage) ?></a></li>
               <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($salesCaseDetails->salesCaseName) ?></li>
            </ol>
         </nav>
         <div class="d-flex align-items-center gap-3 mb-2">
            <h1 class="page-title mb-0">
               <span class="fw-semibold fs-28"><?= htmlspecialchars($salesCaseDetails->clientName) ?></span>
            </h1>
            <button type="button" class="btn btn-sm btn-outline-primary border-0 d-flex align-items-center gap-2"
                    data-bs-toggle="collapse" data-bs-target="#manageSale" aria-expanded="false"
                    title="Edit sales case details">
               <i class="ri-pencil-line"></i>
               <span class="d-none d-md-inline">Edit Details</span>
            </button>
         </div>
         <p class="text-muted mb-0 fs-16">
            <i class="ri-briefcase-line me-1"></i>
            <span class="fst-italic"><?= htmlspecialchars($salesCaseDetails->salesCaseName) ?></span>
         </p>
      </div>

      <!-- Right Section: Key People & Actions -->
      <div class="d-flex flex-column flex-md-row gap-3">
         <!-- Sales Case Contact -->
         <div class="dropdown">
            <a class="d-flex align-items-center text-decoration-none p-3 bg-light rounded-3 shadow-sm" href="javascript:void(0);" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
               <div class="avatar avatar-md rounded-circle d-flex justify-content-center align-items-center me-3" style="background-color: <?= isset($salesCaseDetails->contactName) ? "#007bff" : "#dc3545"; ?>">
                  <span class="text-white fw-semibold">
                     <?= isset($salesCaseDetails->contactName) ? Utility::generate_initials($salesCaseDetails->contactName) : "NA"; ?>
                  </span>
               </div>
               <div>
                  <div class="fw-semibold text-dark"><?= isset($salesCaseDetails->contactName) ? htmlspecialchars($salesCaseDetails->contactName) : "No Contact" ?></div>
                  <div class="text-muted small"><?= isset($salesCaseDetails->contactEmail) ? htmlspecialchars($salesCaseDetails->contactEmail) : "Assign Contact" ?></div>
               </div>
               <i class="ri-arrow-down-s-line ms-2 text-muted"></i>
            </a>
            <form class="dropdown-menu dropdown-menu-end p-3 shadow-lg" action="<?= "{$base}php/scripts/sales/manage_sale.php"?>" method="post" style="min-width: 320px;">
               <h6 class="dropdown-header fw-semibold">Select Contact</h6>
               <input type="hidden" name="salesCaseID" value="<?= $salesCaseDetails->salesCaseID ?>">
               <input type="hidden" name="entityID" value="<?= $entityID; ?>">
               <input type="hidden" name="salesCaseContactID" value="">
               <?php if($clientContacts): ?>
                  <?php foreach ($clientContacts as $clientContact): ?>
                     <?php $clientContactInitials = strtoupper(Utility::generate_initials($clientContact->contactName)); ?>
                     <a href="javascript:void(0);" class="dropdown-item d-flex align-items-center py-2 contact-select-item" data-contact-id="<?= $clientContact->clientContactID ?>">
                        <div class="avatar avatar-sm rounded-circle d-flex justify-content-center align-items-center me-3" style="background-color: #007bff;">
                           <span class="text-white small fw-semibold"><?= $clientContactInitials ?: 'NA' ?></span>
                        </div>
                        <div class="flex-grow-1">
                           <div class="fw-semibold"><?= htmlspecialchars($clientContact->contactName) ?></div>
                           <div class="text-muted small"><?= htmlspecialchars($clientContact->contactType) ?></div>
                        </div>
                     </a>
                  <?php endforeach; ?>
               <?php else: ?>
                  <div class="dropdown-item-text text-muted">No contacts available</div>
               <?php endif; ?>
            </form>
         </div>

         <!-- Sales Person -->
         <div class="d-flex align-items-center p-3 bg-light rounded-3 shadow-sm">
            <div class="avatar avatar-md rounded-circle d-flex justify-content-center align-items-center me-3" style="background-color: #007bff;">
               <span class="text-white fw-semibold">
                  <?= $salesPersonDetails['initials'] ?? Utility::generate_initials($salesCaseDetails->salesPersonName ?? 'NA'); ?>
               </span>
            </div>
            <div>
               <div class="fw-semibold text-dark">Sales Person</div>
               <div class="text-muted small"><?= htmlspecialchars($salesCaseDetails->salesPersonName ?? 'Unassigned') ?></div>
            </div>
         </div>
      </div>
   </div>
</div>
            <!-- ============================================================================
                 INLINE EDIT FORM (Collapsible)
                 ============================================================================ -->
            <div id="manageSale" class="collapse">
               <div class="card border-0 shadow-sm mb-4">
                  <div class="card-header bg-transparent border-bottom">
                     <h5 class="mb-0">
                        <i class="ri-edit-line me-2"></i>Edit Sales Case Details
                     </h5>
                  </div>
                  <div class="card-body">
                     <form id="editSaleForm" action="<?= "{$base}php/scripts/sales/manage_sale.php"?>" method="post" class="needs-validation" novalidate>
                        <input type="hidden" name="salesCaseID" value="<?= $salesCaseDetails->salesCaseID ?>">
                        <input type="hidden" name="entityID" value="<?= $entityID ?>">
                        <input type="hidden" name="orgDataID" value="<?= $orgDataID ?>">
                        <input type="hidden" name="action" value="update">

                        <!-- Section 1: Basic Information -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">
                               <i class="ri-information-line me-2"></i>Basic Information
                            </h6>
                            <div class="row g-3">
                               <div class="col-md-4">
                                  <label for="editSalesCaseName" class="form-label">
                                     Opportunity Name <span class="text-danger">*</span>
                                  </label>
                                  <input type="text" class="form-control" id="editSalesCaseName" name="salesCaseName"
                                         value="<?= htmlspecialchars($salesCaseDetails->salesCaseName) ?>" required>
                                  <div class="invalid-feedback">Please provide an opportunity name.</div>
                               </div>

                               <div class="col-md-4">
                                  <label for="editBusinessUnitID" class="form-label">
                                     Business Unit <span class="text-danger">*</span>
                                  </label>
                                  <select class="form-select" id="editBusinessUnitID" name="businessUnitID" required>
                                     <option value="">Select business unit...</option>
                                     <?php
                                     if ($businessUnits) {
                                        foreach ($businessUnits as $unit) {
                                           $selected = ($unit->businessUnitID == $salesCaseDetails->businessUnitID) ? 'selected' : '';
                                           echo "<option value='{$unit->businessUnitID}' {$selected}>{$unit->businessUnitName}</option>";
                                        }
                                     }
                                     ?>
                                  </select>
                                  <div class="invalid-feedback">Please select a business unit.</div>
                               </div>

                               <div class="col-md-4">
                                  <label for="editSaleStage" class="form-label">
                                     Sale Stage / Pipeline <span class="text-danger">*</span>
                                  </label>
                                  <select class="form-select" id="editSaleStage" name="saleStage" required>
                                     <option value="">Select stage...</option>
                                     <?php
                                     $saleStages = [
                                        'business_development' => [
                                           'label' => 'Business Development',
                                           'icon' => 'ri-lightbulb-line',
                                           'color' => '#ffc107'
                                        ],
                                        'opportunities' => [
                                           'label' => 'Opportunities',
                                           'icon' => 'ri-star-line',
                                           'color' => '#17a2b8'
                                        ],
                                        'order' => [
                                           'label' => 'Order / Won',
                                           'icon' => 'ri-check-line',
                                           'color' => '#28a745'
                                        ],
                                        'loss' => [
                                           'label' => 'Lost',
                                           'icon' => 'ri-close-line',
                                           'color' => '#dc3545'
                                        ]
                                     ];

                                     foreach ($saleStages as $stageValue => $stageInfo) {
                                        $selected = ($salesCaseDetails->saleStage == $stageValue) ? 'selected' : '';
                                        echo "<option value='{$stageValue}' {$selected}>{$stageInfo['label']}</option>";
                                     }
                                     ?>
                                  </select>
                                  <div class="invalid-feedback">Please select a sale stage.</div>
                                  <small class="form-text text-muted">
                                     <i class="ri-information-line"></i> Moving to Order/Won or Lost will close the opportunity
                                  </small>
                               </div>

                               <div class="col-md-4">
                                  <label for="editSalesPersonID" class="form-label">
                                     Sales Person / Owner <span class="text-danger">*</span>
                                  </label>
                                  <select class="form-select" id="editSalesPersonID" name="salesPersonID" required>
                                     <option value="">Select owner...</option>
                                     <?php if ($allEmployees): ?>
                                        <?php foreach ($allEmployees as $employee): ?>
                                           <?php $selected = ($employee->ID == $salesCaseDetails->salesPersonID) ? 'selected' : ''; ?>
                                           <option value="<?= $employee->ID ?>" <?= $selected ?>>
                                              <?= htmlspecialchars($employee->FirstName . ' ' . $employee->Surname) ?>
                                           </option>
                                        <?php endforeach; ?>
                                     <?php endif; ?>
                                  </select>
                                  <div class="invalid-feedback">Please select a sales person.</div>
                               </div>
                            </div>
                        </div>

                        <!-- Section 2: Client & Contact -->
                        <div class="mb-4">
                           <h6 class="text-primary mb-3">
                              <i class="ri-user-line me-2"></i>Client & Contact Information
                           </h6>
                           <div class="row g-3">
                              <div class="col-md-6">
                                 <label for="editClientID" class="form-label">
                                    Client <span class="text-danger">*</span>
                                 </label>
                                 <select class="form-select" id="editClientID" name="clientID" required>
                                    <option value="">Select a client...</option>
                                    <?php
                                    $clients = Client::client_full(array('orgDataID' => $orgDataID, 'entityID' => $entityID, 'Suspended' => 'N'), false, $DBConn);
                                    if ($clients) {
                                       foreach ($clients as $client) {
                                          $selected = ($client->clientID == $salesCaseDetails->clientID) ? 'selected' : '';
                                          echo "<option value='{$client->clientID}' {$selected}>{$client->clientName} ({$client->clientCode})</option>";
                                       }
                                    }
                                    ?>
                                 </select>
                                 <div class="invalid-feedback">Please select a client.</div>
                                 <small class="form-text text-muted">Changing the client will reset the contact</small>
                              </div>

                              <div class="col-md-6">
                                 <label for="editContactPersonID" class="form-label">Contact Person</label>
                                 <select class="form-select" id="editContactPersonID" name="salesCaseContactID">
                                    <option value="">Select contact person...</option>
                                    <?php if($clientContacts): ?>
                                       <?php foreach ($clientContacts as $clientContact): ?>
                                          <?php $selected = ($clientContact->clientContactID == $salesCaseDetails->salesCaseContactID) ? 'selected' : ''; ?>
                                          <option value="<?= $clientContact->clientContactID ?>" <?= $selected ?>>
                                             <?= htmlspecialchars($clientContact->contactName) ?>
                                          </option>
                                       <?php endforeach; ?>
                                    <?php endif; ?>
                                 </select>
                                 <small class="form-text text-muted">Will be updated based on selected client</small>
                              </div>
                           </div>
                        </div>



                         <!-- Section 4: Notes -->
                        <div class="mb-4">
                           <label for="editSalesCaseNotes" class="form-label">
                              <i class="ri-file-text-line me-2"></i>Notes & Description
                           </label>
                           <textarea class="form-control" id="editSalesCaseNotes" name="salesCaseNotes" rows="4"><?= htmlspecialchars($salesCaseDetails->salesCaseNotes ?? '') ?></textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                           <button type="button" class="btn btn-light" data-bs-toggle="collapse" data-bs-target="#manageSale">
                              <i class="ri-close-line me-1"></i>Cancel
                           </button>
                           <button type="submit" class="btn btn-primary" id="updateSaleBtn">
                              <i class="ri-save-line me-1"></i>Save Changes
                           </button>
                        </div>
                     </form>
                  </div>
               </div>
            </div>

<!-- ============================================================================
     QUICK METRICS DASHBOARD
     ============================================================================ -->
<div class="row g-3 mb-4">
   <div class="col-md-3 col-sm-6">
      <div class="card border-0 shadow-sm h-100">
         <div class="card-body">
            <div class="d-flex align-items-center">
               <div class="avatar avatar-lg rounded-circle bg-primary-transparent me-3">
                  <i class="ri-money-dollar-circle-line text-primary fs-24"></i>
               </div>
               <div class="flex-grow-1">
                  <div class="text-muted small mb-1">Deal Value <span class="text-muted text-danger t600 small float-end">(Based on Probability)</span></div>
                  <div class="h4 mb-0 fw-bold">KES <?= number_format($weightedValue, 2) ?></div>
                  <div class="text-muted small mt-1">
                     <small>(<?= number_format($salesCaseDetails->salesCaseEstimate ?? 0, 2) ?> × <?= $salesCaseDetails->probability ?? 0 ?>%)</small>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="col-md-3 col-sm-6">
      <div class="card border-0 shadow-sm h-100">
         <div class="card-body">
            <div class="d-flex align-items-center">
               <div class="avatar avatar-lg rounded-circle bg-success-transparent me-3">
                  <i class="ri-percent-line text-success fs-24"></i>
               </div>
               <div class="flex-grow-1">
                  <div class="text-muted small mb-1">Probability</div>
                  <div class="h4 mb-0 fw-bold"><?= $salesCaseDetails->probability ?? 0 ?>%</div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="col-md-3 col-sm-6">
      <div class="card border-0 shadow-sm h-100">
         <div class="card-body">
            <div class="d-flex align-items-center">
               <div class="avatar avatar-lg rounded-circle bg-info-transparent me-3">
                  <i class="ri-calculator-line text-info fs-24"></i>
               </div>
               <div class="flex-grow-1">
                  <div class="text-muted small mb-1">Weighted Value</div>
                  <div class="h4 mb-0 fw-bold">KES <?= number_format($weightedValue, 2) ?></div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="col-md-3 col-sm-6">
      <div class="card border-0 shadow-sm h-100">
         <div class="card-body">
            <div class="d-flex align-items-center">
               <div class="avatar avatar-lg rounded-circle bg-warning-transparent me-3">
                  <i class="ri-calendar-line text-warning fs-24"></i>
               </div>
               <div class="flex-grow-1">
                  <div class="text-muted small mb-1">Days Until Close</div>
                  <div class="h4 mb-0 fw-bold">
                     <?php if($daysUntilClose !== null): ?>
                        <?= $daysUntilClose > 0 ? $daysUntilClose : '<span class="text-danger">Overdue</span>' ?>
                     <?php else: ?>
                        <span class="text-muted">Not Set</span>
                     <?php endif; ?>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<!-- ============================================================================
     STATUS & KEY INFORMATION SECTION
     ============================================================================ -->
<div class="card border-0 shadow-sm mb-4">
   <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
      <h5 class="mb-0 fw-semibold">
         <i class="ri-bar-chart-line me-2 text-primary"></i>
         Status & Pipeline
      </h5>
      <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=home&state={$salesCaseDetails->saleStage}"?>" class="btn btn-sm btn-outline-primary">
         <i class="ri-eye-line me-1"></i>All Opportunities
      </a>
   </div>
   <div class="card-body">
      <?php
      $disableState='disabled';
      $btnState = 'btn-outline-primary';
      $leadActive = $oppotunityActive = $proposalActive = $closedActive = '';
      if($salesCaseID) {
         $salesCaseDetails->saleStatusLevelID == 1 ? $leadActive = 'checked' : '';
         $salesCaseDetails->saleStatusLevelID == 2 ? $oppotunityActive = 'checked' : '';
         $salesCaseDetails->saleStatusLevelID == 3 ? $proposalActive = 'checked' : '';
         $salesCaseDetails->saleStatusLevelID == 4 ? $closedActive = 'checked' : '';
      }
      // include "includes/scripts/sales/status_levels_script.php";
      ?>


<form action="<?php echo $base ."php/scripts/sales/manage_sale.php" ?>" method="POST" id="statusSubmit" class="my-3" >
   <input type="hidden" name="salesCaseID" value="<?php echo $salesCaseDetails->salesCaseID ?>">
   <input type="hidden" name="saleStatusLevelID" id="saleStatusLevelID" value="<?php echo $salesCaseDetails->saleStatusLevelID ?>">
   <div class="btn-group col-12" role="group" aria-label="Basic radio toggle button group">
      <div class="row col-12 g-0">
         <?php
         if($statusLevels) {
            $statusLevelCount = 0;
            foreach ($statusLevels as $statusLevel) {
               $statusLevelCount++;
               $btnState = ($salesCaseDetails->closeStatus == 'won' || $salesCaseDetails->closeStatus == 'lost') ?  'btn-danger' :'btn-outline-primary';
               // Check if closeLevel property exists and equals 'Y'
               $isCloseLevel = isset($statusLevel->closeLevel) && ($statusLevel->closeLevel == 'Y' || $statusLevel->closeLevel === '1');
               if($isCloseLevel) {
                  $btnState = ($salesCaseDetails->closeStatus == 'won' || $salesCaseDetails->closeStatus == 'lost') ?  'btn-danger' :'btn-secondary';?>
                  <div class="col-sm d-grid gap-2 dropdown closeInput">
                     <input
                        type="checkbox"
                        class="btn-check w-100 rounded-0 dropdown-toggle"
                        name="saleStatus" id="btnradio<?php echo $statusLevel->saleStatusLevelID ?>"
                        value="<?php echo $statusLevel->saleStatusLevelID ?>"
                        autocomplete="off"
                        <?php echo $salesCaseDetails->saleStatusLevelID == $statusLevel->saleStatusLevelID ? 'checked' : '' ?>
                        data-bs-toggle="dropdown" />

                        <input type="hidden" class="closeStatus" name="closeStatus" id="closeStatus" value="">
                     <label class="btn <?php echo $btnState; ?> btn-lg rounded-0" for="btnradio<?php echo $statusLevel->saleStatusLevelID ?>"><?php echo $statusLevel->statusLevel ?></label>
                     <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1"style="width:300px">
                        <li><a id="order" class="dropdown-item orderStateOption " data-close-state ="won" href="">Order</a></li>
                        <li><a id="reject" class="dropdown-item orderStateOption" href="" data-close-state="lost" >rejected Proposal</a></li>
                     </ul>
                  </div>
                  <script>
                     document.addEventListener('DOMContentLoaded', function() {
                        document.querySelectorAll('.closeInput input[type="checkbox"]').forEach(function(element) {
                           element.addEventListener('click', function(event) {
                              event.stopPropagation();
                              event.preventDefault();
                              console.log('This Has been clicked' + element.value);
                              document.getElementById('saleStatusLevelID').value = element.value;
                           });
                        });

                        document.querySelectorAll('.closeInput .dropdown-item').forEach(function(element) {
                           element.addEventListener('click', function(event) {
                              // console.log('This Has been clicked' + element.value);
                              // console.log('This Has been clicked' + element.dataset.closeState);
                              document.getElementById('closeStatus').value = element.dataset.closeState;
                              const closeStatusName = element.dataset.closeState == 'won' ? 'order' : 'loss';

                              // create input for sales stage change
                              const input = document.createElement('input');
                              input.type = 'hidden';
                              input.name = 'saleStage';
                              input.value = closeStatusName;
                              input.className = 'form-control form-control-sm form-control-plaintext bg-light';
                              document.getElementById('statusSubmit').appendChild(input);

                              event.stopPropagation();
                              event.preventDefault();
                              document.getElementById('statusSubmit').submit();
                           });
                        });
                     });
                  </script>
                  <?php
                  continue;
               }?>
               <div class="col-sm d-grid gap-2">
                  <input
                     type="checkbox"
                     class="btn-check w-100 rounded-0 status"
                     name="saleStatus"
                     id="btnradio<?php echo $statusLevel->saleStatusLevelID ?>"
                     value="<?php echo $statusLevel->saleStatusLevelID ?>"
                     autocomplete="off" <?php echo $salesCaseDetails->saleStatusLevelID == $statusLevel->saleStatusLevelID ? 'checked' : '' ?>
                     data-closeLevel ="<?php echo isset($statusLevel->closeLevel) ? $statusLevel->closeLevel : 'N' ?>"
                     data-statusLevelID ="<?php echo $statusLevel->saleStatusLevelID ?>"
                     data-statusLevel ="<?php echo $statusLevel->statusLevel ?>" />
                  <label class="btn <?php echo $btnState; ?> btn-lg rounded-0 " for="btnradio<?php echo $statusLevel->saleStatusLevelID ?>">
                     <a tabindex="0"
                        class="text-dark"
                        role="button"
                        data-bs-toggle="popover"
                        data-bs-trigger="hover"
                        data-bs-placement="top"
                        title="<?php echo $statusLevel->statusLevel ?> "
                        data-bs-content="<?php echo $statusLevel->StatusLevelDescription ?>">
                        <?php echo $statusLevel->statusLevel ?>
                        <i class="ti ti-info-circle"></i>
                     </a>
                  </label>
               </div>
               <?php
            }
         } else {?>
            <div class="col-sm d-grid gap-2">
               <input type="checkbox" class="btn-check w-100 rounded-0"   name="saleStatus" id="btnradio1" value="lead" autocomplete="off" <?php echo $leadActive ?>  >
               <label class="btn <?php echo $btnState; ?> btn-lg rounded-0" for="btnradio1">Lead</label>
            </div>
            <div class="col-sm d-grid gap-2">
               <input type="checkbox" class="btn-check w-100 " name="saleStatus" id="btnradio2" value="opportunity" autocomplete="off" <?php echo $oppotunityActive ?> >
               <label class="btn <?php echo $btnState; ?> btn-lg rounded-0" for="btnradio2">Opportunity</label>
            </div>
            <div class="col-sm d-grid gap-2">
               <input type="checkbox" class="btn-check w-100 rounded-0" name="saleStatus" id="btnradio3" value="proposal" autocomplete="off" <?php echo $proposalActive ?>>
               <label class="btn <?php echo $btnState; ?> btn-lg rounded-0 " for="btnradio3">Proposal</label>
            </div>

            <div class="col-sm d-grid gap-2 dropdown">
               <input type="checkbox" class="btn-check w-100 rounded-0 dropdown-toggle" name="saleStatus"  id="btnradio4" value="closed" autocomplete="off" <?php echo $closedActive ?> data-bs-toggle="dropdown" aria-expanded="false">
               <label class="btn <?php echo $btnState; ?> btn-lg rounded-0" for="btnradio4">Closed<?php echo isset($saleDetails->closeStatus) ?  "<small class='nott font-12'>({$saleDetails->closeStatus})</small>" : '' ?></label>

               <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1"style="width:300px">
                  <li><a id="order" class="dropdown-item order" href="">Order</a></li>
                  <li><a id="reject" class="dropdown-item reject" href="" >Rejected Proposal</a></li>
               </ul>
            </div>
            <?php
         } ?>
      </div>
   </div>
</form>

<script>
   document.addEventListener('DOMContentLoaded', function() {
      const statusButtons = document.querySelectorAll('input[name="saleStatus"]');
      const statusForm = document.getElementById('statusSubmit');

      // Ensure SweetAlert is available (lazy-load if missing)
      let swalReadyPromise;
      function ensureSwal() {
         if (window.Swal) return Promise.resolve();
         if (!swalReadyPromise) {
            swalReadyPromise = new Promise((resolve) => {
               const script = document.createElement('script');
               script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
               script.onload = () => resolve();
               script.onerror = () => resolve(); // fallback gracefully
               document.head.appendChild(script);
            });
         }
         return swalReadyPromise;
      }

      async function confirmStatusChange(label) {
         await ensureSwal();
         if (window.Swal && typeof Swal.fire === 'function') {
            const result = await Swal.fire({
               title: 'Change status?',
               text: `Move this sales case to "${label}"?`,
               icon: 'question',
               showCancelButton: true,
               confirmButtonColor: '#0d6efd',
               cancelButtonColor: '#6c757d',
               confirmButtonText: 'Yes, change',
               cancelButtonText: 'No, keep current'
            });
            return result.isConfirmed;
         }
         return window.confirm(`Move this sales case to "${label}"?`);
      }

      statusButtons.forEach(button => {
         button.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();

            const data = button.dataset;
            const statusLabel = data.statuslevel || 'this status';

            const isCloseLevel = data.closelevel == 'Y' || data.closelevel === '1';
            const statusLevel = data.statuslevel ? data.statuslevel.toLowerCase() : '';
            const isClosedStatus = isCloseLevel || statusLevel.includes('closed') || statusLevel.includes('close');

            const confirmed = await confirmStatusChange(statusLabel);
            if (!confirmed) return;

            // Closed status path -> open close-status modal
            if (isClosedStatus && !button.closest('.closeInput')) {
               const modal = document.getElementById('closeStatusModal');
               if (modal) {
                  const modalStatusLevelInput = document.getElementById('modalSaleStatusLevelID');
                  if (modalStatusLevelInput) {
                     modalStatusLevelInput.value = button.value;
                  }
                  document.getElementById('saleStatusLevelID').value = button.value;
                  const bsModal = new bootstrap.Modal(modal);
                  bsModal.show();
               }
               return;
            }

            // Regular status path -> submit immediately
            document.getElementById('saleStatusLevelID').value = button.value;
            statusForm.submit();
         });
      });
   });
</script>

<div class="col-12">
   <form action="<?php echo $base ."php/scripts/sales/manage_sale.php" ?>" method="POST" id="submitSale" class="mb-0" >
      <input type="hidden" name="salesCaseID" value="<?php echo $salesCaseDetails->salesCaseID ?>">
      <input type="hidden" name="entityID" value="<?php echo $entityID; ?>">
      <div class="row col-12">
         <div class="d-flex align-items-center flex-fill col-lg-3">
            <span class="avatar bd-blue-800 avatar-lg me-2 avatar-rounded">
               <i class="fa-solid fa-calculator"></i>
            </span>
            <div class="col-sm-10">
               <div class="form-group">
                  <label for="salesValue" class="nott"> Sale/Project Value</label>
                  <input type="text" name="salesCaseEstimate" id="salesCaseEstimate" class="form-control form-control-sm form-control-plaintext  <?= !$salesCaseDetails->salesCaseEstimate ? "bg-danger" : "bg-light" ?>  " value="<?php echo $salesCaseDetails->salesCaseEstimate ?>">
               </div>
            </div>
         </div>

         <div class="d-flex align-items-center flex-fill col-lg-3">
            <span class="avatar bd-blue-800 avatar-lg me-2 avatar-rounded">
               <i class="fa-solid fa-percent"></i>
            </span>
            <div class="col-10">
               <div class="form-group">
                  <label for="probability" class="nott"> Probability</label>
                  <input type="text" name="probability" id="probability" class="form-control form-control-sm form-control-plaintext  <?= !$salesCaseDetails->salesCaseEstimate ? "bg-danger" : "bg-light" ?> " value="<?php echo $salesCaseDetails->probability ?>">
               </div>
            </div>
         </div>

         <div class="d-flex align-items-center flex-fill col-lg-3">
            <span class="avatar bd-blue-800 avatar-lg me-2 avatar-rounded">
               <i class="fa-solid fa-calendar"></i>
            </span>
            <div class="col-10">
               <div class="form-group">
                  <label for="probability" class="nott"> Expected Order Date  </label>
                  <input type="text"
                  id="date"
                  name="expectedCloseDate"
                  class="form-control form-control-sm form-control-plaintext px-2
                  <?= !$salesCaseDetails->expectedCloseDate || $salesCaseDetails->expectedCloseDate == '0000-00-00' ? " bg-light-orange border-danger " : "bg-light" ?> "
                  value="<?=  $salesCaseDetails->expectedCloseDate && $salesCaseDetails->expectedCloseDate != "0000-00-00" ? $salesCaseDetails->expectedCloseDate : "";  ?>" placeholder="Expected Order Date">
               </div>
            </div>
         </div>

         <?php
         // var_dump($salesCaseDetails->expectedCloseDate); ?>
         <script>
            // check if the date input exists and set the datepicker start date to today
            document.addEventListener('DOMContentLoaded', function() {
               const dateInput = document.getElementById('date');
               if (dateInput) {
                  if(dateInput.value === "0000-00-00") {
                     // If the date input is set to "0000-00-00", make the inpit to invalid and error message to please input valid expected order date
                     dateInput.classList.add('is-invalid');
                     dateInput.setCustomValidity('Please input a valid expected order date.');
                     // If the date input is empty, set it to today's date
                     // dateInput.value = new Date().toISOString().split('T')[0]; // Set today's date in YYYY-MM-DD format
                  }
                  dateInput.addEventListener('change', function() {
                     console.log('Date input changed');
                     console.log(dateInput.value);
                  });
               } else {
                  console.warn('No date input found.');
               }
            });
         </script>

         <div class="d-flex align-items-center flex-fill col-lg-3">
            <span class="avatar bd-blue-800 avatar-lg me-2 avatar-rounded">
               <i class="fa-solid fa-user"></i>
            </span>
            <div class="col-10">
               <div class="row">
                  <div class="form-group col-md-10">
                     <label for="probability" class="nott"> Lead Source</label>
                     <select id="sourceLead" name="leadSourceID" class="form-control form-control-sm form-control-plaintext bg-light ps-2" onchange="addNewLead(this);" >
                        <?php echo
                        Form::populate_select_element_from_object($leadSource, 'leadSourceID', 'leadSourceName', $salesCaseDetails->leadSourceID, '', 'Select Lead Source') ?>
                        <option value="newSource">Add new source lead</option>
                     </select>
                     <div id="SourceLeadAdd" class="col-12 d-none">
                        <small>Add Lead Source <span id="return-btn" value="return" class="float-end btn-link"><i class="icon-select"></i></span></small>
                        <input type="text" class="form-control form-control-sm bg-light-orange form-control-plaintext px-2" name="newLeadSource" placeholder="Add new Lead source" >
                     </div>
                  </div>
                  <div class="col-md-2 px-0">
                     <label for="probability" class="nott">&nbsp;</label>
                     <button type="Submit" class="btn btn-primary  btn-sm w-100">Save</button>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </form>
</div>

<!-- ============================================================================
     CLOSE STATUS MODAL - Select Won or Lost
     ============================================================================ -->
<div class="modal fade" id="closeStatusModal" tabindex="-1" aria-labelledby="closeStatusModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="closeStatusModalLabel">
               <i class="ri-checkbox-circle-line me-2"></i>Close Sales Case
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form action="<?php echo $base ."php/scripts/sales/manage_sale.php" ?>" method="POST" id="closeStatusForm">
            <input type="hidden" name="salesCaseID" value="<?php echo $salesCaseDetails->salesCaseID ?>">
            <input type="hidden" name="saleStatusLevelID" id="modalSaleStatusLevelID" value="">
            <input type="hidden" name="closeStatus" id="modalCloseStatus" value="">
            <input type="hidden" name="entityID" value="<?php echo $entityID; ?>">

            <div class="modal-body">
               <p class="mb-4">Please select the outcome for this sales case:</p>

               <div class="d-grid gap-3">
                  <button type="button" class="btn btn-lg btn-outline-success close-status-option" data-close-status="won">
                     <i class="ri-checkbox-circle-line me-2 fs-20"></i>
                     <div class="text-start">
                        <div class="fw-semibold">Won / Order</div>
                        <small class="text-muted">This sale was successfully closed and won</small>
                     </div>
                  </button>

                  <button type="button" class="btn btn-lg btn-outline-danger close-status-option" data-close-status="lost">
                     <i class="ri-close-circle-line me-2 fs-20"></i>
                     <div class="text-start">
                        <div class="fw-semibold">Lost / Rejected</div>
                        <small class="text-muted">This sale was closed but not won</small>
                     </div>
                  </button>
               </div>
            </div>

            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
         </form>
      </div>
   </div>
</div>

<script>
   document.addEventListener('DOMContentLoaded', function() {
      // Handle close status option clicks
      const closeStatusOptions = document.querySelectorAll('.close-status-option');
      const closeStatusForm = document.getElementById('closeStatusForm');
      const modalCloseStatusInput = document.getElementById('modalCloseStatus');
      const modal = document.getElementById('closeStatusModal');

      closeStatusOptions.forEach(option => {
         option.addEventListener('click', function() {
            const closeStatus = this.dataset.closeStatus;
            const closeStatusName = closeStatus == 'won' ? 'order' : 'loss';

            // Set the close status
            if(modalCloseStatusInput) {
               modalCloseStatusInput.value = closeStatus;
            }

            // Add saleStage input
            let saleStageInput = closeStatusForm.querySelector('input[name="saleStage"]');
            if(!saleStageInput) {
               saleStageInput = document.createElement('input');
               saleStageInput.type = 'hidden';
               saleStageInput.name = 'saleStage';
               saleStageInput.value = closeStatusName;
               closeStatusForm.appendChild(saleStageInput);
            } else {
               saleStageInput.value = closeStatusName;
            }

            // Submit the form
            closeStatusForm.submit();
         });
      });

      // Update modal status level ID when modal is shown
      if(modal) {
         modal.addEventListener('show.bs.modal', function(event) {
            // The status level ID should already be set by the click handler
            console.log('Modal shown, status level ID:', document.getElementById('modalSaleStatusLevelID').value);
         });
      }
   });
</script>


   </div>
</div>

<!-- ============================================================================
     TAB NAVIGATION - Enterprise CRM Sections
     ============================================================================ -->
<div class="card border-0 shadow-sm">
   <div class="card-header bg-transparent border-bottom p-0">
      <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
         <li class="nav-item" role="presentation">
            <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
               <i class="ri-dashboard-line me-1"></i>Overview
            </button>
         </li>
         <li class="nav-item" role="presentation">
            <button class="nav-link" id="timeline-tab" data-bs-toggle="tab" data-bs-target="#timeline" type="button" role="tab">
               <i class="ri-time-line me-1"></i>Timeline
               <?php if($activityCount > 0): ?>
                  <span class="badge bg-primary ms-1"><?= $activityCount ?></span>
               <?php endif; ?>
            </button>
         </li>
         <li class="nav-item" role="presentation">
            <button class="nav-link" id="activities-tab" data-bs-toggle="tab" data-bs-target="#activities" type="button" role="tab">
               <i class="ri-calendar-check-line me-1"></i>Activities
            </button>
         </li>
         <?php if($salesCaseDetails->statusLevel === "Proposal"): ?>
         <li class="nav-item" role="presentation">
            <button class="nav-link" id="proposal-tab" data-bs-toggle="tab" data-bs-target="#proposal" type="button" role="tab">
               <i class="ri-file-paper-line me-1"></i>Proposal
            </button>
         </li>
         <?php endif; ?>
         <li class="nav-item" role="presentation">
            <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">
               <i class="ri-folder-line me-1"></i>Documents
            </button>
         </li>
      </ul>
   </div>
   <div class="card-body p-0">
      <div class="tab-content p-4">
         <!-- Overview Tab -->
         <div class="tab-pane fade show active" id="overview" role="tabpanel">
            <div class="row g-4">
               <!-- Key Information -->
               <div class="col-lg-8">
                  <h6 class="fw-semibold mb-3">Key Information</h6>
                  <div class="row g-3">
                     <div class="col-md-6">
                        <div class="info-item">
                           <label class="text-muted small mb-1">Client</label>
                           <div class="fw-semibold"><?= htmlspecialchars($salesCaseDetails->clientName) ?></div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="info-item">
                           <label class="text-muted small mb-1">Sales Person</label>
                           <div class="fw-semibold"><?= htmlspecialchars($salesCaseDetails->salesPersonName ?? 'Unassigned') ?></div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="info-item">
                           <label class="text-muted small mb-1">Expected Close Date</label>
                           <div class="fw-semibold">
                              <?php if($salesCaseDetails->expectedCloseDate && $salesCaseDetails->expectedCloseDate != '0000-00-00'): ?>
                                 <?= Utility::date_format($salesCaseDetails->expectedCloseDate) ?>
                                 <?php if($daysUntilClose !== null && $daysUntilClose < 0): ?>
                                    <span class="badge bg-danger-transparent text-danger ms-2">Overdue</span>
                                 <?php endif; ?>
                              <?php else: ?>
                                 <span class="text-muted">Not Set</span>
                              <?php endif; ?>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="info-item">
                           <label class="text-muted small mb-1">Lead Source</label>
                           <div class="fw-semibold">
                              <?php
                              $leadSourceName = 'Not Set';
                              if($salesCaseDetails->leadSourceID && $leadSource) {
                                 foreach($leadSource as $source) {
                                    if($source->leadSourceID == $salesCaseDetails->leadSourceID) {
                                       $leadSourceName = $source->leadSourceName;
                                       break;
                                    }
                                 }
                              }
                              echo htmlspecialchars($leadSourceName);
                              ?>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <!-- Quick Actions -->
               <div class="col-lg-4">
                  <h6 class="fw-semibold mb-3">Quick Actions</h6>
                  <div class="d-grid gap-2">
                     <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#manageActivityModal">
                        <i class="ri-add-line me-1"></i>Add Activity
                     </button>
                     <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#manageSale">
                        <i class="ri-pencil-line me-1"></i>Edit Details
                     </button>
                     <?php if($salesCaseDetails->statusLevel === "Proposal" && !empty($proposals)): ?>
                     <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=proposal_details&proposalID=" . $proposals[0]->proposalID ?>" class="btn btn-outline-info">
                        <i class="ri-file-paper-line me-1"></i>View Proposal
                     </a>
                     <?php endif; ?>
                  </div>
               </div>
            </div>
         </div>

         <!-- Timeline Tab -->
         <div class="tab-pane fade" id="timeline" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-4">
               <div>
                  <h5 class="mb-1 fw-semibold">Sales Journey Timeline</h5>
                  <p class="text-muted small mb-0">Track all activities from inception to close</p>
               </div>
               <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manageActivityModal">
                  <i class="ri-add-line me-1"></i>Add Activity
               </button>
            </div>

            <div class="timeline-container">
               <?php if($activities && count($activities) > 0): ?>
                  <!-- Timeline Start Marker -->
                  <div class="timeline-milestone">
                     <div class="timeline-milestone-marker bg-success">
                        <i class="ri-flag-line text-white"></i>
                     </div>
                     <div class="timeline-milestone-content">
                        <h6 class="mb-0 fw-semibold">Sales Case Started</h6>
                        <p class="text-muted small mb-0">
                           <i class="ri-calendar-line me-1"></i>
                           <?= Utility::date_format($salesCaseDetails->createdOn ?? date('Y-m-d')) ?>
                        </p>
                     </div>
                  </div>

                  <div class="timeline">
                     <?php
                     // Sort activities by date (most recent first)
                     $sortedActivities = $activities;
                     usort($sortedActivities, function($a, $b) {
                        $dateA = $a->salesActivityDate ?? $a->activityDate ?? date('Y-m-d');
                        $dateB = $b->salesActivityDate ?? $b->activityDate ?? date('Y-m-d');
                        return strtotime($dateB) - strtotime($dateA);
                     });

                     foreach($sortedActivities as $activity):
                        // Determine activity type and styling
                        $activityType = strtolower($activity->activityTypeName ?? 'other');
                        $activityCategory = strtolower($activity->activityCategoryName ?? '');

                        // Icon and color based on activity type
                        $icon = 'ri-record-circle-line';
                        $markerColor = 'primary';
                        $borderColor = '#007bff';

                        if(stripos($activityType, 'meeting') !== false || stripos($activityCategory, 'meeting') !== false) {
                           $icon = 'ri-team-line';
                           $markerColor = 'info';
                           $borderColor = '#17a2b8';
                        } elseif(stripos($activityType, 'call') !== false || stripos($activityCategory, 'call') !== false) {
                           $icon = 'ri-phone-line';
                           $markerColor = 'success';
                           $borderColor = '#28a745';
                        } elseif(stripos($activityType, 'email') !== false || stripos($activityCategory, 'email') !== false) {
                           $icon = 'ri-mail-line';
                           $markerColor = 'warning';
                           $borderColor = '#ffc107';
                        } elseif(stripos($activityType, 'proposal') !== false || stripos($activityCategory, 'proposal') !== false) {
                           $icon = 'ri-file-paper-line';
                           $markerColor = 'purple';
                           $borderColor = '#6f42c1';
                        } elseif(stripos($activityType, 'expense') !== false || stripos($activityCategory, 'expense') !== false) {
                           $icon = 'ri-money-dollar-circle-line';
                           $markerColor = 'danger';
                           $borderColor = '#dc3545';
                        } elseif(stripos($activityType, 'document') !== false || stripos($activityCategory, 'document') !== false || stripos($activityType, 'collateral') !== false) {
                           $icon = 'ri-file-text-line';
                           $markerColor = 'secondary';
                           $borderColor = '#6c757d';
                        } elseif(stripos($activityType, 'task') !== false || stripos($activityCategory, 'task') !== false) {
                           $icon = 'ri-task-line';
                           $markerColor = 'primary';
                           $borderColor = '#007bff';
                        }

                        // Status badge color
                        $statusBadge = 'bg-secondary-transparent';
                        if(isset($activity->activityStatus)) {
                           if(strtolower($activity->activityStatus) == 'completed') {
                              $statusBadge = 'bg-success-transparent text-success';
                           } elseif(strtolower($activity->activityStatus) == 'pending' || strtolower($activity->activityStatus) == 'scheduled') {
                              $statusBadge = 'bg-warning-transparent text-warning';
                           } elseif(strtolower($activity->activityStatus) == 'cancelled') {
                              $statusBadge = 'bg-danger-transparent text-danger';
                           }
                        }
                     ?>
                        <div class="timeline-item" data-activity-id="<?= $activity->activityID ?? '' ?>">
                           <div class="timeline-marker bg-<?= $markerColor ?>">
                              <i class="<?= $icon ?> text-white"></i>
                           </div>
                           <div class="timeline-content" style="border-left-color: <?= $borderColor ?>;">
                              <div class="d-flex justify-content-between align-items-start mb-2">
                                 <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                       <h6 class="mb-0 fw-semibold"><?= htmlspecialchars($activity->activityName ?? 'Activity') ?></h6>
                                       <span class="badge <?= $statusBadge ?> small"><?= htmlspecialchars($activity->activityStatus ?? 'Completed') ?></span>
                                    </div>
                                    <p class="text-muted small mb-0">
                                       <i class="ri-calendar-line me-1"></i>
                                       <?= Utility::date_format($activity->salesActivityDate ?? $activity->activityDate ?? date('Y-m-d')) ?>
                                       <?php if(isset($activity->activityTime)): ?>
                                          <i class="ri-time-line ms-2 me-1"></i><?= $activity->activityTime ?>
                                       <?php endif; ?>
                                       <?php if(isset($activity->activityDuration) && $activity->activityDuration): ?>
                                          <i class="ri-hourglass-line ms-2 me-1"></i><?= $activity->activityDuration ?> mins
                                       <?php endif; ?>
                                    </p>
                                 </div>
                                 <span class="badge bg-<?= $markerColor ?>-transparent text-<?= $markerColor ?>"><?= htmlspecialchars($activity->activityTypeName ?? 'Activity') ?></span>
                              </div>

                              <?php if(!empty($activity->activityDescription)): ?>
                                 <p class="mb-2 text-secondary"><?= nl2br(htmlspecialchars($activity->activityDescription)) ?></p>
                              <?php endif; ?>

                              <!-- Activity Metadata -->
                              <div class="d-flex flex-wrap gap-3 mt-2 pt-2 border-top">
                                 <?php if(isset($activity->assignedToName)): ?>
                                    <div class="text-muted small">
                                       <i class="ri-user-line me-1"></i>
                                       <strong>Assigned:</strong> <?= htmlspecialchars($activity->assignedToName) ?>
                                    </div>
                                 <?php endif; ?>

                                 <?php if(isset($activity->activityLocation) && $activity->activityLocation): ?>
                                    <div class="text-muted small">
                                       <i class="ri-map-pin-line me-1"></i>
                                       <strong>Location:</strong> <?= htmlspecialchars($activity->activityLocation) ?>
                                    </div>
                                 <?php endif; ?>

                                 <?php if(isset($activity->activityOutcome) && $activity->activityOutcome): ?>
                                    <div class="text-muted small">
                                       <i class="ri-feedback-line me-1"></i>
                                       <strong>Outcome:</strong> <?= htmlspecialchars($activity->activityOutcome) ?>
                                    </div>
                                 <?php endif; ?>

                                 <?php if(isset($activity->activityCost) && floatval($activity->activityCost) > 0): ?>
                                    <div class="text-muted small">
                                       <i class="ri-money-dollar-circle-line me-1"></i>
                                       <strong>Cost:</strong> KES <?= number_format($activity->activityCost, 2) ?>
                                    </div>
                                 <?php endif; ?>
                              </div>

                              <!-- Activity Actions -->
                              <div class="mt-2">
                                 <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editActivity('<?= $activity->activityID ?? '' ?>')">
                                    <i class="ri-edit-line"></i> Edit
                                 </button>
                              </div>
                           </div>
                        </div>
                     <?php endforeach; ?>
                  </div>

                  <!-- Timeline End Marker (Expected Close) -->
                  <?php if($salesCaseDetails->expectedCloseDate && $salesCaseDetails->expectedCloseDate != '0000-00-00'): ?>
                  <div class="timeline-milestone">
                     <div class="timeline-milestone-marker <?= $daysUntilClose !== null && $daysUntilClose < 0 ? 'bg-danger' : 'bg-warning' ?>">
                        <i class="ri-flag-2-line text-white"></i>
                     </div>
                     <div class="timeline-milestone-content">
                        <h6 class="mb-0 fw-semibold">Expected Close Date</h6>
                        <p class="text-muted small mb-0">
                           <i class="ri-calendar-line me-1"></i>
                           <?= Utility::date_format($salesCaseDetails->expectedCloseDate) ?>
                           <?php if($daysUntilClose !== null): ?>
                              <?php if($daysUntilClose > 0): ?>
                                 <span class="badge bg-info-transparent text-info ms-2"><?= $daysUntilClose ?> days remaining</span>
                              <?php else: ?>
                                 <span class="badge bg-danger-transparent text-danger ms-2">Overdue by <?= abs($daysUntilClose) ?> days</span>
                              <?php endif; ?>
                           <?php endif; ?>
                        </p>
                     </div>
                  </div>
                  <?php endif; ?>

               <?php else: ?>
                  <!-- Empty State -->
                  <div class="text-center py-5">
                     <div class="empty-state-icon mb-3">
                        <i class="ri-time-line fs-48 text-muted"></i>
                     </div>
                     <h5 class="mb-2">No Activities Yet</h5>
                     <p class="text-muted mb-4">Start tracking your sales journey by adding activities such as meetings, calls, proposals, and more.</p>
                     <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manageActivityModal">
                        <i class="ri-add-line me-1"></i>Add Your First Activity
                     </button>

                     <!-- Activity Type Suggestions -->
                     <div class="row g-3 mt-4 mx-auto" style="max-width: 800px;">
                        <div class="col-md-4 col-6">
                           <div class="card border-0 bg-light h-100">
                              <div class="card-body text-center">
                                 <i class="ri-phone-line text-success fs-32 mb-2"></i>
                                 <p class="small mb-0 fw-semibold">Calls</p>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-4 col-6">
                           <div class="card border-0 bg-light h-100">
                              <div class="card-body text-center">
                                 <i class="ri-team-line text-info fs-32 mb-2"></i>
                                 <p class="small mb-0 fw-semibold">Meetings</p>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-4 col-6">
                           <div class="card border-0 bg-light h-100">
                              <div class="card-body text-center">
                                 <i class="ri-mail-line text-warning fs-32 mb-2"></i>
                                 <p class="small mb-0 fw-semibold">Emails</p>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-4 col-6">
                           <div class="card border-0 bg-light h-100">
                              <div class="card-body text-center">
                                 <i class="ri-file-paper-line text-purple fs-32 mb-2"></i>
                                 <p class="small mb-0 fw-semibold">Proposals</p>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-4 col-6">
                           <div class="card border-0 bg-light h-100">
                              <div class="card-body text-center">
                                 <i class="ri-file-text-line text-secondary fs-32 mb-2"></i>
                                 <p class="small mb-0 fw-semibold">Collaterals</p>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-4 col-6">
                           <div class="card border-0 bg-light h-100">
                              <div class="card-body text-center">
                                 <i class="ri-money-dollar-circle-line text-danger fs-32 mb-2"></i>
                                 <p class="small mb-0 fw-semibold">Expenses</p>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               <?php endif; ?>
            </div>
         </div>

         <!-- Activities Tab -->
         <div class="tab-pane fade" id="activities" role="tabpanel">
            <?php include "includes/scripts/work/activity_display_script.php"; ?>
         </div>

         <!-- Proposal Tab -->
         <?php if($salesCaseDetails->statusLevel === "Proposal"): ?>
         <div class="tab-pane fade" id="proposal" role="tabpanel">
            <?php include "includes/scripts/sales/proposal.php"; ?>
         </div>
         <?php endif; ?>

         <!-- Documents Tab -->
         <div class="tab-pane fade" id="documents" role="tabpanel">
            <?php include "includes/scripts/sales/sales_documents_display.php"; ?>
         </div>
      </div>
   </div>
</div>


<script>
// Edit Sale Form Enhancement
(function() {
   const editForm = document.getElementById('editSaleForm');
   const editClientSelect = document.getElementById('editClientID');
   const editContactSelect = document.getElementById('editContactPersonID');
   const editSaleStageSelect = document.getElementById('editSaleStage');

   // Handle sale stage changes with confirmation for closing stages
   if (editSaleStageSelect) {
      const originalStage = editSaleStageSelect.value;

      editSaleStageSelect.addEventListener('change', function() {
         const newStage = this.value;

         // Warn when moving to closing stages
         if ((newStage === 'order' || newStage === 'loss') && newStage !== originalStage) {
            const stageName = newStage === 'order' ? 'Order/Won' : 'Lost';
            const message = `Are you sure you want to move this opportunity to "${stageName}"?\n\n` +
                          `This will close the opportunity and it will no longer appear in your active pipeline.`;

            if (!confirm(message)) {
               // Revert to original value if user cancels
               this.value = originalStage;
               return;
            }
         }

         // Show appropriate message based on stage
         if (newStage === 'order') {
            alert('Congratulations! 🎉 This opportunity will be marked as Won. You can convert it to a project after saving.');
         } else if (newStage === 'loss') {
            alert('This opportunity will be marked as Lost. You can still reopen it later if needed.');
         }
      });
   }

   // Load contacts when client changes
   if (editClientSelect && editContactSelect) {
      editClientSelect.addEventListener('change', function() {
         const clientID = this.value;

         if (!clientID) {
            editContactSelect.innerHTML = '<option value="">Select contact person...</option>';
            return;
         }

         // Show loading state
         editContactSelect.innerHTML = '<option value="">Loading contacts...</option>';
         editContactSelect.disabled = true;

         // Get base path
         const basePath = '<?= $base ?>';

         // Fetch contacts
         fetch(`${basePath}php/scripts/clients/get_client_contacts.php?clientID=${clientID}`)
            .then(response => response.json())
            .then(data => {
               editContactSelect.innerHTML = '<option value="">Select contact person...</option>';
               editContactSelect.disabled = false;

               if (data.success && data.contacts && data.contacts.length > 0) {
                  data.contacts.forEach(contact => {
                     const option = document.createElement('option');
                     option.value = contact.clientContactID;
                     option.textContent = contact.contactName + (contact.contactEmail ? ' (' + contact.contactEmail + ')' : '');
                     editContactSelect.appendChild(option);
                  });
               } else {
                  editContactSelect.innerHTML += '<option value="" disabled>No contacts found</option>';
               }
            })
            .catch(error => {
               console.error('Error loading contacts:', error);
               editContactSelect.innerHTML = '<option value="">Select contact person...</option>';
               editContactSelect.innerHTML += '<option value="" disabled>Error loading contacts</option>';
               editContactSelect.disabled = false;
            });
      });
   }

   // Form validation
   if (editForm) {
      editForm.addEventListener('submit', function(e) {
         if (!editForm.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
         }
         editForm.classList.add('was-validated');
      });
   }
})();
</script>

<!-- ============================================================================
     JAVASCRIPT - Enhanced Functionality
     ============================================================================ -->
<script>
document.addEventListener('DOMContentLoaded', function() {
   'use strict';

   // ========================================================================
   // Contact Selection Handler
   // ========================================================================
   const contactSelectItems = document.querySelectorAll('.contact-select-item');
   contactSelectItems.forEach(item => {
      item.addEventListener('click', function(e) {
         e.preventDefault();
         const contactID = this.dataset.contactId;
         const form = this.closest('form');
         const hiddenInput = form.querySelector('input[name="salesCaseContactID"]');

         if(hiddenInput) {
            hiddenInput.value = contactID;
            form.submit();
         }
      });
   });

   // ========================================================================
   // Tab Persistence (Optional - save active tab to sessionStorage)
   // ========================================================================
   const tabButtons = document.querySelectorAll('[data-bs-toggle="tab"]');
   tabButtons.forEach(button => {
      button.addEventListener('shown.bs.tab', function(e) {
         sessionStorage.setItem('salesCaseActiveTab', e.target.getAttribute('data-bs-target'));
      });
   });

   // Restore active tab on page load
   const activeTab = sessionStorage.getItem('salesCaseActiveTab');
   if(activeTab) {
      const tabButton = document.querySelector(`[data-bs-target="${activeTab}"]`);
      if(tabButton) {
         const tab = new bootstrap.Tab(tabButton);
         tab.show();
      }
   }

   // ========================================================================
   // Form Validation & UX Enhancements
   // ========================================================================
   const editForm = document.querySelector('#manageSale form');
   if(editForm) {
      editForm.addEventListener('submit', function(e) {
         // Add loading state
         const submitBtn = this.querySelector('button[type="submit"]');
         if(submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
         }
      });
   }

   // ========================================================================
   // Timeline Activity Management
   // ========================================================================
   window.editActivity = function(activityID) {
      if(!activityID) {
         console.error('Activity ID is required');
         return;
      }

      // Open the activity modal
      const modalElement = document.getElementById('manageActivityModal');
      if (!modalElement) {
         console.error('Activity modal not found');
         return;
      }

      const modal = new bootstrap.Modal(modalElement);
      modal.show();

      // Load activity data via AJAX
      const baseUrl = '<?= $base ?>';
      fetch(`${baseUrl}php/scripts/sales/get_activity.php?activityID=${activityID}`)
         .then(response => {
            if (!response.ok) {
               throw new Error('Failed to fetch activity data');
            }
            return response.json();
         })
         .then(data => {
            if (data && data.success && data.activity) {
               // Wait for wizard to initialize
               setTimeout(() => {
                  if (typeof window.loadActivityForEdit === 'function') {
                     window.loadActivityForEdit(data.activity);
                  } else {
                     console.warn('loadActivityForEdit function not available yet');
                     // Fallback: try again after delay
                     setTimeout(() => {
                        if (typeof window.loadActivityForEdit === 'function') {
                           window.loadActivityForEdit(data.activity);
                        }
                     }, 500);
                  }
               }, 200);
            } else {
               console.error('Invalid activity data received');
               if (typeof showToast === 'function') {
                  showToast('Failed to load activity details', 'error');
               }
            }
         })
         .catch(error => {
            console.error('Error loading activity:', error);
            if (typeof showToast === 'function') {
               showToast('Error loading activity. Please try again.', 'error');
            }
         });
   };

   // Add New Activity (opens modal with empty form)
   window.addNewActivity = function() {
      const modalElement = document.getElementById('manageActivityModal');
      if (!modalElement) {
         console.error('Activity modal not found');
         return;
      }

      // Clear the activity ID to indicate new activity
      const activityIDField = document.getElementById('activityID');
      if (activityIDField) {
         activityIDField.value = '';
      }

      const modal = new bootstrap.Modal(modalElement);
      modal.show();
   };

   // ========================================================================
   // Auto-refresh Timeline (Optional - for real-time updates)
   // ========================================================================
   // Uncomment if you want auto-refresh functionality
   /*
   setInterval(function() {
      const timelineTab = document.getElementById('timeline');
      if(timelineTab && timelineTab.classList.contains('active')) {
         // Refresh timeline content via AJAX
         // Implementation depends on your AJAX setup
      }
   }, 30000); // Refresh every 30 seconds
   */
});
</script>

<!-- ============================================================================
     CUSTOM STYLES - Enterprise UI Enhancements
     ============================================================================ -->
<style>
/* Page Header */
.page-header-enterprise {
   padding: 1.5rem 0;
}

.page-title {
   font-size: 1.75rem;
   font-weight: 600;
   color: #1a1a1a;
}

/* Edit Details Button */
.btn-outline-primary[data-bs-toggle="collapse"] {
   border-width: 1.5px;
   font-weight: 500;
   transition: all 0.3s ease;
   box-shadow: 0 2px 4px rgba(99, 102, 241, 0.1);
   position: relative;
   overflow: hidden;
}

.btn-outline-primary[data-bs-toggle="collapse"]::before {
   content: '';
   position: absolute;
   top: 0;
   left: -100%;
   width: 100%;
   height: 100%;
   background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
   transition: left 0.5s;
}

.btn-outline-primary[data-bs-toggle="collapse"]:hover::before {
   left: 100%;
}

.btn-outline-primary[data-bs-toggle="collapse"]:hover {
   transform: translateY(-1px);
   box-shadow: 0 4px 8px rgba(99, 102, 241, 0.2);
   background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
   border-color: #4f46e5;
}

.btn-outline-primary[data-bs-toggle="collapse"] i {
   font-size: 1.1rem;
   transition: transform 0.3s ease;
}

.btn-outline-primary[data-bs-toggle="collapse"]:hover i {
   transform: rotate(15deg);
}

.btn-outline-primary[data-bs-toggle="collapse"][aria-expanded="true"] {
   background: #6366f1;
   color: white;
   border-color: #6366f1;
}

.btn-outline-primary[data-bs-toggle="collapse"][aria-expanded="true"]:hover {
   background: #4f46e5;
   border-color: #4f46e5;
}

/* Subtle pulse animation on page load */
@keyframes pulse-subtle {
   0%, 100% {
      box-shadow: 0 2px 4px rgba(99, 102, 241, 0.1);
   }
   50% {
      box-shadow: 0 2px 12px rgba(99, 102, 241, 0.3);
   }
}

.btn-outline-primary[data-bs-toggle="collapse"] {
   animation: pulse-subtle 2s ease-in-out 3;
}

/* Avatar Styles */
.avatar {
   display: inline-flex;
   align-items: center;
   justify-content: center;
   width: 2.5rem;
   height: 2.5rem;
   font-size: 0.875rem;
   font-weight: 600;
}

.avatar-sm {
   width: 2rem;
   height: 2rem;
   font-size: 0.75rem;
}

.avatar-md {
   width: 2.5rem;
   height: 2.5rem;
   font-size: 0.875rem;
}

.avatar-lg {
   width: 3rem;
   height: 3rem;
   font-size: 1rem;
}

/* Custom Tabs */
.nav-tabs-custom {
   border-bottom: 2px solid #e9ecef;
}

.nav-tabs-custom .nav-link {
   border: none;
   border-bottom: 2px solid transparent;
   color: #6c757d;
   padding: 1rem 1.5rem;
   font-weight: 500;
   transition: all 0.3s ease;
}

.nav-tabs-custom .nav-link:hover {
   border-bottom-color: #dee2e6;
   color: #495057;
}

.nav-tabs-custom .nav-link.active {
   color: #007bff;
   border-bottom-color: #007bff;
   background-color: transparent;
}

/* Timeline Styles */
.timeline-container {
   position: relative;
   padding-left: 2rem;
}

.timeline {
   position: relative;
}

.timeline-item {
   position: relative;
   padding-bottom: 2rem;
   padding-left: 3rem;
}

.timeline-item:not(:last-child)::before {
   content: '';
   position: absolute;
   left: 1rem;
   top: 2.5rem;
   bottom: -1rem;
   width: 3px;
   background: linear-gradient(to bottom, #e9ecef 0%, #dee2e6 100%);
}

.timeline-marker {
   position: absolute;
   left: -2rem;
   top: 0.5rem;
   width: 2rem;
   height: 2rem;
   border-radius: 50%;
   background: #007bff;
   border: 3px solid #fff;
   box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.2);
   display: flex;
   align-items: center;
   justify-content: center;
   z-index: 2;
   transition: all 0.3s ease;
}

.timeline-marker i {
   font-size: 0.875rem;
}

.timeline-item:hover .timeline-marker {
   transform: scale(1.1);
   box-shadow: 0 0 0 5px rgba(0, 123, 255, 0.3);
}

.timeline-content {
   background: #fff;
   padding: 1.25rem;
   border-radius: 0.75rem;
   border-left: 4px solid #007bff;
   box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
   transition: all 0.3s ease;
}

.timeline-item:hover .timeline-content {
   box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
   transform: translateX(4px);
}

/* Timeline Milestone Styles */
.timeline-milestone {
   position: relative;
   padding-bottom: 2rem;
   padding-left: 3rem;
   display: flex;
   align-items: center;
}

.timeline-milestone:not(:last-child)::before {
   content: '';
   position: absolute;
   left: 1rem;
   top: 2.5rem;
   bottom: -1rem;
   width: 3px;
   background: linear-gradient(to bottom, #e9ecef 0%, #dee2e6 100%);
}

.timeline-milestone-marker {
   position: absolute;
   left: -2rem;
   top: 0.5rem;
   width: 2.5rem;
   height: 2.5rem;
   border-radius: 50%;
   border: 3px solid #fff;
   box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.2);
   display: flex;
   align-items: center;
   justify-content: center;
   z-index: 3;
}

.timeline-milestone-marker i {
   font-size: 1rem;
}

.timeline-milestone-content {
   background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
   padding: 1rem 1.25rem;
   border-radius: 0.75rem;
   border-left: 4px solid #28a745;
   flex-grow: 1;
}

/* Activity Type Colors */
.bg-info-transparent {
   background-color: rgba(23, 162, 184, 0.1);
}

.text-info {
   color: #17a2b8 !important;
}

.bg-success-transparent {
   background-color: rgba(40, 167, 69, 0.1);
}

.bg-warning-transparent {
   background-color: rgba(255, 193, 7, 0.1);
}

.text-warning {
   color: #ffc107 !important;
}

.bg-danger-transparent {
   background-color: rgba(220, 53, 69, 0.1);
}

.text-danger {
   color: #dc3545 !important;
}

.bg-secondary-transparent {
   background-color: rgba(108, 117, 125, 0.1);
}

.bg-purple-transparent {
   background-color: rgba(111, 66, 193, 0.1);
}

.text-purple {
   color: #6f42c1 !important;
}

.bg-purple {
   background-color: #6f42c1;
}

/* Marker colors for different activity types */
.timeline-marker.bg-info {
   background: #17a2b8;
   box-shadow: 0 0 0 3px rgba(23, 162, 184, 0.2);
}

.timeline-marker.bg-success {
   background: #28a745;
   box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.2);
}

.timeline-marker.bg-warning {
   background: #ffc107;
   box-shadow: 0 0 0 3px rgba(255, 193, 7, 0.2);
}

.timeline-marker.bg-danger {
   background: #dc3545;
   box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.2);
}

.timeline-marker.bg-secondary {
   background: #6c757d;
   box-shadow: 0 0 0 3px rgba(108, 117, 125, 0.2);
}

.timeline-marker.bg-purple {
   background: #6f42c1;
   box-shadow: 0 0 0 3px rgba(111, 66, 193, 0.2);
}

/* Empty State */
.empty-state-icon {
   width: 80px;
   height: 80px;
   margin: 0 auto;
   background: #f8f9fa;
   border-radius: 50%;
   display: flex;
   align-items: center;
   justify-content: center;
}

/* Info Items */
.info-item {
   padding: 0.75rem 0;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
   .page-header-enterprise .d-md-flex {
      flex-direction: column;
   }

   .nav-tabs-custom .nav-link {
      padding: 0.75rem 1rem;
      font-size: 0.875rem;
   }

   .timeline-container {
      padding-left: 1.5rem;
   }
}
</style>
