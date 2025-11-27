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
$leadSource = Sales::lead_sources(array('Suspended'=>'N', 'orgDataID'=>$orgDataID), false, $DBConn);
$workTypes = Work::work_types(array('Suspended'=>'N'), false, $DBConn);
$activityCategories = Schedule::activity_categories([], false, $DBConn);
$activityTypes = Schedule::tija_activity_types([], false, $DBConn);
$salesCases = Sales::sales_case_mid(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID, 'Suspended'=>'N'), false, $DBConn);
$industrySectors = Data::tija_sectors(array(), false, $DBConn);
$industries = Data::tija_industry(array(), false, $DBConn);
$countries = Data::countries(array(), false, $DBConn);
$employeesCategorised = Employee::categorise_employee($allEmployees, 'jobTitle');

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

echo Utility::form_modal_header("manageActivityModal", "sales/manage_sales_activity.php", "Manage Activity", array('modal-md', 'modal-dialog-centered'), $base);
include "includes/scripts/sales/modals/manage_sale_activity.php";
echo Utility::form_modal_footer('Save Activity', 'saveActivity',  ' btn btn-success btn-sm', true);

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
         <h1 class="page-title mb-2">
            <span class="fw-semibold fs-28"><?= htmlspecialchars($salesCaseDetails->clientName) ?></span>
            <button type="button" class="btn btn-sm btn-link text-muted p-0 ms-2" data-bs-toggle="collapse" data-bs-target="#manageSale" aria-expanded="false">
               <i class="ri-pencil-line"></i>
            </button>
         </h1>
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
      include "includes/scripts/sales/status_levels_script.php";
      ?>
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
            <div class="timeline-container">
               <?php if($activities && count($activities) > 0): ?>
                  <div class="timeline">
                     <?php foreach(array_reverse($activities) as $activity): ?>
                        <div class="timeline-item">
                           <div class="timeline-marker"></div>
                           <div class="timeline-content">
                              <div class="d-flex justify-content-between align-items-start mb-2">
                                 <div>
                                    <h6 class="mb-1 fw-semibold"><?= htmlspecialchars($activity->activityName ?? 'Activity') ?></h6>
                                    <p class="text-muted small mb-0">
                                       <i class="ri-calendar-line me-1"></i>
                                       <?= Utility::date_format($activity->salesActivityDate ?? $activity->activityDate ?? date('Y-m-d')) ?>
                                       <?php if(isset($activity->activityTime)): ?>
                                          <i class="ri-time-line ms-2 me-1"></i><?= $activity->activityTime ?>
                                       <?php endif; ?>
                                    </p>
                                 </div>
                                 <span class="badge bg-primary-transparent"><?= htmlspecialchars($activity->activityTypeName ?? 'Activity') ?></span>
                              </div>
                              <?php if(!empty($activity->activityDescription)): ?>
                                 <p class="mb-0"><?= nl2br(htmlspecialchars($activity->activityDescription)) ?></p>
                              <?php endif; ?>
                           </div>
                        </div>
                     <?php endforeach; ?>
                  </div>
               <?php else: ?>
                  <div class="text-center py-5">
                     <i class="ri-time-line fs-48 text-muted mb-3 d-block"></i>
                     <p class="text-muted">No activities yet. Start by adding your first activity.</p>
                     <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manageActivityModal">
                        <i class="ri-add-line me-1"></i>Add Activity
                     </button>
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

<!-- ============================================================================
     INLINE EDIT FORM (Collapsible)
     ============================================================================ -->
<div id="manageSale" class="collapse">
   <div class="card border-0 shadow-sm mb-4">
      <div class="card-header bg-transparent border-bottom">
         <h5 class="mb-0">Edit Sales Case</h5>
      </div>
      <div class="card-body">
         <form action="<?= "{$base}php/scripts/sales/manage_sale.php"?>" method="post" class="row g-3">
            <input type="hidden" name="salesCaseID" value="<?= $salesCaseDetails->salesCaseID ?>">
            <div class="col-md-4">
               <label for="salesCaseName" class="form-label">Sales Case Name</label>
               <input type="text" class="form-control" id="salesCaseName" name="salesCaseName" value="<?= htmlspecialchars($salesCaseDetails->salesCaseName) ?>">
            </div>
            <div class="col-md-4">
               <label for="clientName" class="form-label">Client Name</label>
               <input type="text" class="form-control" id="clientName" name="clientName" value="<?= htmlspecialchars($salesCaseDetails->clientName) ?>" readonly>
            </div>
            <div class="col-md-4">
               <label for="salesPersonID" class="form-label">Sales Person</label>
               <select class="form-select" id="salesPersonID" name="salesPersonID">
                  <?php foreach ($allEmployees as $employee): ?>
                     <option value="<?= $employee->ID ?>" <?= $employee->ID == $salesCaseDetails->salesPersonID ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($employee->FirstName . ' ' . $employee->Surname) ?>
                     </option>
                  <?php endforeach; ?>
               </select>
            </div>
            <div class="col-12">
               <button type="submit" class="btn btn-primary">Update Sales Case</button>
               <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#manageSale">Cancel</button>
            </div>
         </form>
      </div>
   </div>
</div>

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
   padding-left: 2rem;
}

.timeline-item:not(:last-child)::before {
   content: '';
   position: absolute;
   left: 0.5rem;
   top: 1.5rem;
   bottom: -1rem;
   width: 2px;
   background: #e9ecef;
}

.timeline-marker {
   position: absolute;
   left: -1.75rem;
   top: 0.25rem;
   width: 0.75rem;
   height: 0.75rem;
   border-radius: 50%;
   background: #007bff;
   border: 2px solid #fff;
   box-shadow: 0 0 0 2px #007bff;
}

.timeline-content {
   background: #f8f9fa;
   padding: 1rem;
   border-radius: 0.5rem;
   border-left: 3px solid #007bff;
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
