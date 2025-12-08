<?php
if(!$isValidUser) {
   Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
   include "includes/core/log_in_script.php";
   return;
}
// var_dump($userDetails);
$employeeID=(isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) : $userDetails->ID;
$employeeDetails = Data::users(array('ID'=>$employeeID), true, $DBConn);
$allEmployees = Employee::employees([], false, $DBConn);
$employeesCategorised = Employee::categorise_employee($allEmployees, 'jobTitle');

$clientID= isset($_GET['client_id']) ? Utility::clean_string($_GET['client_id']) : '';
$clients= Client::clients(array('Suspended'=>'N'), false, $DBConn);
$clientDetails = Client::clients_full(array( 'clientID'=>$clientID), true, $DBConn);
// var_dump($clientDetails);
$orgDataID= isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : $clientDetails->orgDataID;
$entityID= isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : $clientDetails->entityID;
$orgDataID = $orgDataID;
$industries = data::tija_industry(array(), false, $DBConn);
$industrySectors = data::tija_sectors(array(), false, $DBConn);
// var_dump($industrySectors);
$clientLevels = Client::client_levels(array(), false, $DBConn);

$clientContacts = Client::client_contacts(array('clientID'=>$clientID), false, $DBConn);
$sales = Sales::sales_case_mid(array('clientID'=>$clientID), false, $DBConn);
$projects = Projects::projects_full(array('clientID'=>$clientID), false, $DBConn);

// Calculate key metrics for dashboard
$totalSalesValue = 0;
$activeSalesCount = 0;
$openOpportunities = 0;
if($sales && is_array($sales)) {
   foreach($sales as $sale) {
      if($sale->Suspended !== 'Y') {
         $activeSalesCount++;
         $totalSalesValue += floatval($sale->salesCaseEstimate ?? 0);
         if(in_array($sale->saleStage, ['Lead', 'Opportunity'])) {
            $openOpportunities++;
         }
      }
   }
}

$activeProjectsCount = 0;
$totalProjectValue = 0;
if($projects && is_array($projects)) {
   foreach($projects as $project) {
      if($project->Suspended !== 'Y' && $project->projectStatus !== 'Closed') {
         $activeProjectsCount++;
         $totalProjectValue += floatval($project->projectValue ?? 0);
      }
   }
}

$recentActivities = Schedule::tija_activities(array('clientID'=>$clientID), false, $DBConn);
$activityCount = $recentActivities ? count($recentActivities) : 0;
$lastActivityDate = null;
if($recentActivities && count($recentActivities) > 0) {
   usort($recentActivities, function($a, $b) {
      return strtotime($b->activityDate) - strtotime($a->activityDate);
   });
   $lastActivityDate = $recentActivities[0]->activityDate;
}

// Get client documents
$clientDocuments = Client::client_documents(array('clientID'=>$clientID), false, $DBConn);
$documentCount = $clientDocuments ? count($clientDocuments) : 0;

// Get client addresses
$addresses = Client::client_address(array('clientID'=>$clientDetails->clientID), false, $DBConn);

if(isset($clientDetails->accountOwnerID) && !empty($clientDetails->accountOwnerID)) {
   $accountOwnerName = Core::user_name($clientDetails->accountOwnerID, $DBConn);
   $accountOwner = Core::user(array('ID'=>$clientDetails->accountOwnerID), true, $DBConn);
}
$documentTypes = Data::document_types([], false, $DBConn);
$positionRoles = Admin::tija_job_titles(['Suspended'=>'N'], false, $DBConn);
$contactTypes = Client::contact_types([], false, $DBConn);
$prefixes = Data::prefixes([], false, $DBConn);

// Additional variables for included modals and scripts
$countries = Data::countries(array(), false, $DBConn) ?: array();
$projectTypes = Projects::project_types(array(), false, $DBConn) ?: array();
$billingRates = array(); // Initialize if billing rates feature exists
$businessUnits = Data::business_units(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn) ?: array();
$clientContactTypes = $contactTypes ?: array(); // Alias for compatibility
$clientAddresses = $addresses ?: array(); // Alias for compatibility
$employeeCategorised = $employeesCategorised ?: array(); // Alias for compatibility

$getString .= "&client_id={$clientID}";?>
<!-- External CSS -->
<link rel="stylesheet" href="<?= $base ?>html/assets/css/client_details.css">

<!-- SweetAlert2 Library -->
<link rel="stylesheet" href="<?= $base ?>assets/libs/sweetalert2/sweetalert2.min.css">
<script src="<?= $base ?>assets/libs/sweetalert2/sweetalert2.all.min.js"></script>

<script>
   let allEmployees = <?= json_encode($allEmployees) ?>;
</script>

<!-- ============================================================================
     ENTERPRISE PAGE HEADER
     ============================================================================ -->
<div class="page-header-enterprise mb-4">
   <div class="d-md-flex d-block align-items-start justify-content-between">
      <!-- Left Section: Title & Breadcrumb -->
      <div class="flex-grow-1 mb-3 mb-md-0">
         <nav aria-label="breadcrumb" class="mb-2">
            <ol class="breadcrumb mb-0">
               <li class="breadcrumb-item"><a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=home" ?>">Home</a></li>
               <li class="breadcrumb-item"><a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=clients" ?>">Clients</a></li>
               <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($clientDetails->clientName) ?></li>
            </ol>
         </nav>
         <h1 class="page-title mb-2">
            <span class="fw-semibold fs-28"><?= htmlspecialchars($clientDetails->clientName) ?></span>
            <button type="button" class="btn btn-sm btn-link text-muted p-0 ms-2" data-bs-toggle="collapse" data-bs-target="#editClientDetails" aria-expanded="false">
               <i class="ri-pencil-line"></i>
            </button>
         </h1>
         <p class="text-muted mb-0 fs-16">
            <i class="ri-building-line me-1"></i>
            <span class="fst-italic"><?= htmlspecialchars($clientDetails->clientLevelName ?? 'Client') ?></span>
            <?php if($clientDetails->vatNumber): ?>
               <span class="ms-3"><i class="ri-file-text-line me-1"></i>VAT: <?= htmlspecialchars($clientDetails->vatNumber) ?></span>
            <?php endif; ?>
         </p>
      </div>

      <!-- Right Section: Account Owner & Actions -->
      <div class="d-flex flex-column flex-md-row gap-3">
         <!-- Account Owner Card -->
         <div class="d-flex align-items-center p-3 bg-light rounded-3 shadow-sm">
            <div class="avatar avatar-md rounded-circle d-flex justify-content-center align-items-center me-3" style="background-color: #007bff;">
               <span class="text-white fw-semibold">
                  <?php
                  if(isset($accountOwner) && !empty($accountOwner)) {
                     echo Core::user_name_initials($accountOwner);
                  } else {
                     echo Utility::generate_initials($clientDetails->clientName);
                  }?>
               </span>
            </div>
            <div>
               <div class="fw-semibold text-dark">Account Owner</div>
               <div class="text-muted small"><?= isset($accountOwnerName) ? htmlspecialchars($accountOwnerName) : "Unassigned" ?></div>
            </div>
         </div>

         <!-- Quick Actions Dropdown -->
         <div class="dropdown">
            <button class="btn btn-primary" type="button" id="quickActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
               <i class="ri-add-line me-1"></i>Quick Actions
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="quickActionsDropdown">
               <li><a class="dropdown-item" href="<?= "{$base}html/?s={$s}&ss={$ss}&p=add_sale&client_id={$clientID}" ?>"><i class="ri-store-line me-2"></i>Create Sale</a></li>
               <li><a class="dropdown-item" href="<?= "{$base}html/?s={$s}&ss={$ss}&p=add_project&client_id={$clientID}" ?>"><i class="ri-briefcase-line me-2"></i>Create Project</a></li>
               <li><a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#manageClientContactModal"><i class="ri-user-add-line me-2"></i>Add Contact</a></li>
               <li><a class="dropdown-item" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#manageClientDocumentModal"><i class="ri-file-add-line me-2"></i>Upload Document</a></li>
               <li><hr class="dropdown-divider"></li>
               <li><a class="dropdown-item text-primary" href="#editClientDetails" data-bs-toggle="collapse"><i class="ri-edit-line me-2"></i>Edit Client Details</a></li>
            </ul>
         </div>
      </div>
   </div>
</div>

<!-- ============================================================================
     KEY METRICS DASHBOARD
     ============================================================================ -->
<div class="row g-3 mb-4">
   <div class="col-md-3 col-sm-6">
      <div class="card border-0 shadow-sm h-100">
         <div class="card-body">
            <div class="d-flex align-items-center">
               <div class="avatar avatar-lg rounded-circle bg-success-transparent me-3">
                  <i class="ri-money-dollar-circle-line text-success fs-24"></i>
               </div>
               <div class="flex-grow-1">
                  <div class="text-muted small mb-1">Total Sales Value</div>
                  <div class="h4 mb-0 fw-bold">KES <?= number_format($totalSalesValue, 2) ?></div>
                  <div class="text-muted small mt-1"><?= $activeSalesCount ?> active case(s)</div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="col-md-3 col-sm-6">
      <div class="card border-0 shadow-sm h-100">
         <div class="card-body">
            <div class="d-flex align-items-center">
               <div class="avatar avatar-lg rounded-circle bg-primary-transparent me-3">
                  <i class="ri-briefcase-line text-primary fs-24"></i>
               </div>
               <div class="flex-grow-1">
                  <div class="text-muted small mb-1">Active Projects</div>
                  <div class="h4 mb-0 fw-bold"><?= $activeProjectsCount ?></div>
                  <div class="text-muted small mt-1">KES <?= number_format($totalProjectValue, 2) ?></div>
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
                  <i class="ri-line-chart-line text-warning fs-24"></i>
               </div>
               <div class="flex-grow-1">
                  <div class="text-muted small mb-1">Open Opportunities</div>
                  <div class="h4 mb-0 fw-bold"><?= $openOpportunities ?></div>
                  <div class="text-muted small mt-1">In pipeline</div>
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
                  <i class="ri-calendar-event-line text-info fs-24"></i>
               </div>
               <div class="flex-grow-1">
                  <div class="text-muted small mb-1">Last Activity</div>
                  <div class="h4 mb-0 fw-bold">
                     <?php if($lastActivityDate): ?>
                        <?= Utility::date_format($lastActivityDate) ?>
                     <?php else: ?>
                        <span class="text-muted small">No activity</span>
                     <?php endif; ?>
                  </div>
                  <div class="text-muted small mt-1"><?= $activityCount ?> total</div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php
$activityID= isset($_GET['actID']) ? Utility::clean_string($_GET['actID']) : '';
if($activityID){?>
   <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <strong>Activity ID: <?= $activityID ?></strong>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
   </div>
   <div class="container">
      <div class="card custom-card">
         <div class="card-header">
            <div class="card-title">
               <div class="d-flex justify-content-between">
                  <h3 class="t400 mb-0 fs-20 ">Edit Activity</h3>
               </div>
            </div>
         </div>
         <div class="card-body">
         <?php   include "includes\scripts\schedule\modals\manage_activity.php";      ?>
         </div>
      </div>
   </div>
   <?php
   exit;
}?>
<div class="collapse" id="editClientDetails">
   <div class="card card-body">
      <form action="<?= "{$base}php/scripts/clients/manage_clients.php"?>" method="post">
         <div class="row">
            <div class="col-lg-6 col-md-12">
               <div class="row">
                  <div class="form-group col-lg-6 d-none">
                     <label for="clientID" class="text-primary"> Client ID</label>
                     <input type="text" name="clientID" id="clientID" class="form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Client ID" value="<?= $clientDetails->clientID ?>" readonly>
                  </div>
                  <div class="form-group col-lg-2">
                     <label for="clientCode" class="text-primary"> Client Code</label>
                     <input type="text" name="clientCode" id="clientCode" class="form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Client Code" value="<?= $clientDetails->clientCode ?>">
                  </div>

                  <div class="form-group col-lg-10">
                     <label for="clientName" class="text-primary "> Client Name</label>
                     <input type="text" name="clientName" id="clientName" class="form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Client Name" value="<?= $clientDetails->clientName ?>">
                  </div>

                  <div class="form-group col-lg-6">
                     <label for="vatNumber" class="text-primary"> VAT Number</label>
                     <input type="text" name="vatNumber" id="vatNumber" class="form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="VAT Number" value="<?= $clientDetails->vatNumber ?>">
                  </div>
                  <div class="form-group col-lg-6">
                     <label for="clientType" class="text-primary "> Client Type</label>
                     <select name="clientLevelID" id="clientLevelID" class="form-control-sm form-control-plaintext bg-light-blue px-2">
                        <?php echo Form::populate_select_element_from_object($clientLevels, 'clientLevelID', 'clientLevelName', $clientDetails->clientLevelID, '' , 'Select Client Type') ?>
                     </select>
                  </div>
                  <div class="form-group col-lg-6">
                     <label for="clientIndustry" class="text-primary "> Client Industry</label>
                     <input type="text" name="clientIndustryID" id="clientIndustryID" class="form-control-sm form-control-plaintext bg-light-blue px-2 d-none" placeholder="Client Industry" value="<?= $clientDetails->clientIndustryID ?>">
                     <button type="button" class="rounded btn btn-sm btn-info-light  bg-light-blue dropdown-toggle d-flex align-items-center w-100" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="text-primary d-block selectedName"> <?= $clientDetails->clientIndustryID ? $clientDetails->industryName." - (". $clientDetails->sectorName .")" : 'Select  Industry'; ?> </span>

                     </button>
                     <ul class="dropdown-menu dropdown-menu-end">
                        <?php
                        if($industrySectors){
                           foreach ($industrySectors as $key => $sector) {
                              $active= $clientDetails->clientIndustryID == $sector->sectorID ? ' activeDay ' : '';
                              $industries = Data::tija_industry(array('sectorID'=>$sector->sectorID), false, $DBConn);
                              ?>
                              <li>
                                 <h5 class="dropdown-header <?= $active ?>"  data-id="<?= $sector->sectorID ?>" data-name="<?= $sector->sectorName ?>" data-type="sector" data-clientid="<?= $clientDetails->clientID ?>">
                                    <?= $sector->sectorName ?>
                                 </h5>
                                 <?php
                                 if($industries){
                                    foreach ($industries as $key => $industry) {
                                       $active= $clientDetails->clientIndustryID == $industry->industryID ? ' activeDay ' : '';
                                       ?>
                                       <a class="dropdown-item industryID ms-3 <?= $active ?>" data-id="<?= $industry->industryID ?>" data-name="<?= $industry->industryName ?>" data-type="industry" data-clientid="<?= $clientDetails->clientID ?>">
                                          <?= $industry->industryName ?>
                                       </a>
                                       <?php
                                    }
                                 }?>
                              </li>
                              <?php
                           }
                        }?>
                        <script>
                           document.querySelectorAll('.industryID').forEach(item => {
                              item.addEventListener('click', event => {
                                 // get all data attributes
                                 const data = item.dataset;
                                 console.log(data);
                                 const selectedName = item.getAttribute('data-name');
                                 const selectedID = item.getAttribute('data-id');
                                 const clientID = item.getAttribute('data-clientid');
                                 const type = item.getAttribute('data-type');
                                 const clientIndustryID = document.querySelector('#clientIndustryID');
                                 const selectedNameElement = document.querySelector('.selectedName');

                                 if(type == 'sector') {
                                    clientIndustryID.value = selectedID;
                                    selectedNameElement.innerHTML = selectedName;
                                 } else {
                                    clientIndustryID.value = selectedID;
                                    selectedNameElement.innerHTML = selectedName;
                                 }
                              })
                           });
                        </script>
                     </ul>

                  </div>

                  <div class="form-group col-lg-6">
                     <label for="" class="text-primary "> Client Owner Name</label>
                     <select name="accountOwnerID" id="accountOwnerID" class="form-control-sm form-control-plaintext bg-light-blue px-2">
                        <?php echo Form::populate_select_element_from_object($allEmployees, 'ID', 'employeeName', $clientDetails->accountOwnerID, '' , 'Select Case Owner') ?>
                     </select>
                  </div>
               </div>

            </div>
            <div class="col-lg-6 col-md-12">
               <div class="form-group my-2">
                  <label for="client_description"> Client Description</label>
                  <textarea name="clientDescription" id="clientDescription" class="borderless-mini" ><?= $clientDetails->clientDescription ?></textarea>
               </div>
            </div>
            <div class="col-12">
               <button type="submit" class="btn btn-primary btn-sm float-end">Save Changes</button>
            </div>
         </div>
      </form>
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
            <button class="nav-link" id="contacts-tab" data-bs-toggle="tab" data-bs-target="#contacts" type="button" role="tab">
               <i class="ri-contacts-line me-1"></i>Contacts & Addresses
               <?php
               $contactCount = (is_array($clientContacts) ? count($clientContacts) : 0);
               $addressCount = (is_array($addresses) ? count($addresses) : 0);
               if($contactCount > 0 || $addressCount > 0):
               ?>
                  <span class="badge bg-primary ms-1"><?= ($contactCount + $addressCount) ?></span>
               <?php endif; ?>
            </button>
         </li>
         <li class="nav-item" role="presentation">
            <button class="nav-link" id="sales-projects-tab" data-bs-toggle="tab" data-bs-target="#sales-projects" type="button" role="tab">
               <i class="ri-line-chart-line me-1"></i>Sales & Projects
               <?php
               $salesCount = (is_array($sales) ? count($sales) : 0);
               $projectCount = (is_array($projects) ? count($projects) : 0);
               if($salesCount > 0 || $projectCount > 0):
               ?>
                  <span class="badge bg-primary ms-1"><?= ($salesCount + $projectCount) ?></span>
               <?php endif; ?>
            </button>
         </li>
         <li class="nav-item" role="presentation">
            <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">
               <i class="ri-folder-line me-1"></i>Documents
               <?php if($documentCount > 0): ?>
                  <span class="badge bg-primary ms-1"><?= $documentCount ?></span>
               <?php endif; ?>
            </button>
         </li>
         <li class="nav-item" role="presentation">
            <button class="nav-link" id="activities-tab" data-bs-toggle="tab" data-bs-target="#activities" type="button" role="tab">
               <i class="ri-calendar-check-line me-1"></i>Activities
               <?php if($activityCount > 0): ?>
                  <span class="badge bg-primary ms-1"><?= $activityCount ?></span>
               <?php endif; ?>
            </button>
         </li>
         <li class="nav-item" role="presentation">
            <button class="nav-link" id="relationships-tab" data-bs-toggle="tab" data-bs-target="#relationships" type="button" role="tab">
               <i class="ri-team-line me-1"></i>Relationships
               <?php
               $relationshipCount = 0;
               $clientRelationships = Client::client_relationships(array('clientID'=>$clientID), false, $DBConn);
               if($clientRelationships && is_array($clientRelationships)) {
                  $relationshipCount = count($clientRelationships);
               }
               if($relationshipCount > 0):
               ?>
                  <span class="badge bg-primary ms-1"><?= $relationshipCount ?></span>
               <?php endif; ?>
            </button>
         </li>
         <li class="nav-item" role="presentation">
            <button class="nav-link" id="financials-tab" data-bs-toggle="tab" data-bs-target="#financials" type="button" role="tab">
               <i class="ri-bar-chart-box-line me-1"></i>Financials
            </button>
         </li>
      </ul>
   </div>
   <div class="card-body p-0">
      <div class="tab-content p-4">
         <!-- Overview Tab -->
         <div class="tab-pane fade show active" id="overview" role="tabpanel">
            <div class="row g-4">
               <!-- Left Column: Client Information -->
               <div class="col-lg-8">
                  <!-- Quick Information -->
                  <h6 class="fw-semibold mb-3">Client Information</h6>
                  <div class="row g-3 mb-4">
                     <div class="col-md-6">
                        <div class="info-card">
                           <label class="text-muted small mb-1">Client Type</label>
                           <div class="fw-semibold"><?= htmlspecialchars($clientDetails->clientLevelName ?? 'Standard Client') ?></div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="info-card">
                           <label class="text-muted small mb-1">Industry</label>
                           <div class="fw-semibold"><?= htmlspecialchars($clientDetails->industryName ?? 'Not Set') ?></div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="info-card">
                           <label class="text-muted small mb-1">Sector</label>
                           <div class="fw-semibold"><?= htmlspecialchars($clientDetails->sectorName ?? 'Not Set') ?></div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="info-card">
                           <label class="text-muted small mb-1">Client Since</label>
                           <div class="fw-semibold"><?= $clientDetails->DateAdded ? Utility::date_format($clientDetails->DateAdded) : 'N/A' ?></div>
                        </div>
                     </div>
                  </div>

                  <!-- Recent Activity Feed -->
                  <h6 class="fw-semibold mb-3">Recent Activity</h6>
                  <?php if($recentActivities && count($recentActivities) > 0): ?>
                     <div class="activity-feed mb-4">
                        <?php
                        $displayLimit = 5;
                        $displayActivities = array_slice($recentActivities, 0, $displayLimit);
                        foreach($displayActivities as $activity):
                           $activityType = strtolower($activity->activityTypeName ?? 'activity');
                           $icon = 'ri-record-circle-line';
                           $color = 'primary';

                           if(stripos($activityType, 'meeting') !== false) {
                              $icon = 'ri-team-line';
                              $color = 'info';
                           } elseif(stripos($activityType, 'call') !== false) {
                              $icon = 'ri-phone-line';
                              $color = 'success';
                           } elseif(stripos($activityType, 'email') !== false) {
                              $icon = 'ri-mail-line';
                              $color = 'warning';
                           }
                        ?>
                           <div class="activity-item d-flex align-items-start mb-3">
                              <div class="avatar avatar-sm rounded-circle bg-<?= $color ?>-transparent me-3">
                                 <i class="<?= $icon ?> text-<?= $color ?>"></i>
                              </div>
                              <div class="flex-grow-1">
                                 <div class="d-flex justify-content-between">
                                    <h6 class="mb-1 fw-semibold small"><?= htmlspecialchars($activity->activityName) ?></h6>
                                    <span class="text-muted small"><?= Utility::date_format($activity->activityDate) ?></span>
                                 </div>
                                 <?php if($activity->activityDescription): ?>
                                    <p class="text-muted small mb-0"><?= nl2br(htmlspecialchars(substr($activity->activityDescription, 0, 100))) ?><?= strlen($activity->activityDescription) > 100 ? '...' : '' ?></p>
                                 <?php endif; ?>
                              </div>
                           </div>
                        <?php endforeach; ?>
                        <?php if(count($recentActivities) > $displayLimit): ?>
                           <a href="javascript:void(0);" class="btn btn-sm btn-outline-primary w-100" onclick="document.querySelector('#activities-tab').click()">
                              View All <?= count($recentActivities) ?> Activities
                           </a>
                        <?php endif; ?>
                     </div>
                  <?php else: ?>
                     <div class="text-center py-4 bg-light rounded-3">
                        <i class="ri-calendar-line fs-32 text-muted mb-2 d-block"></i>
                        <p class="text-muted mb-0">No activities yet</p>
                     </div>
                  <?php endif; ?>

                  <!-- Client Description -->
                  <?php if($clientDetails->clientDescription): ?>
                     <h6 class="fw-semibold mb-3 mt-4">About Client</h6>
                     <div class="card bg-light border-0">
                        <div class="card-body">
                           <p class="mb-0"><?= nl2br(htmlspecialchars($clientDetails->clientDescription)) ?></p>
                        </div>
                     </div>
                  <?php endif; ?>
               </div>

               <!-- Right Column: Quick Stats & Actions -->
               <div class="col-lg-4">
                  <!-- Quick Stats -->
                  <h6 class="fw-semibold mb-3">Quick Stats</h6>
                  <div class="list-group mb-4">
                     <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="ri-contacts-line me-2 text-primary"></i>Contacts</span>
                        <span class="badge bg-primary-transparent text-primary"><?= is_array($clientContacts) ? count($clientContacts) : 0 ?></span>
                     </div>
                     <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="ri-map-pin-line me-2 text-success"></i>Addresses</span>
                        <span class="badge bg-success-transparent text-success"><?= is_array($addresses) ? count($addresses) : 0 ?></span>
                     </div>
                     <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="ri-store-line me-2 text-warning"></i>Sales Cases</span>
                        <span class="badge bg-warning-transparent text-warning"><?= is_array($sales) ? count($sales) : 0 ?></span>
                     </div>
                     <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="ri-briefcase-line me-2 text-info"></i>Projects</span>
                        <span class="badge bg-info-transparent text-info"><?= is_array($projects) ? count($projects) : 0 ?></span>
                     </div>
                     <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="ri-folder-line me-2 text-secondary"></i>Documents</span>
                        <span class="badge bg-secondary-transparent text-secondary"><?= $documentCount ?></span>
                     </div>
                  </div>

                  <!-- Quick Actions -->
                  <h6 class="fw-semibold mb-3">Quick Actions</h6>
                  <div class="d-grid gap-2 mb-4">
                     <button type="button" class="btn btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#editClientDetails">
                        <i class="ri-pencil-line me-1"></i>Edit Client Details
                     </button>
                     <button type="button" class="btn btn-outline-secondary" onclick="document.querySelector('#contacts-tab').click()">
                        <i class="ri-contacts-line me-1"></i>Manage Contacts
                     </button>
                     <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=add_sale&client_id={$clientID}" ?>" class="btn btn-outline-success">
                        <i class="ri-add-line me-1"></i>Create Sale
                     </a>
                     <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=add_project&client_id={$clientID}" ?>" class="btn btn-outline-info">
                        <i class="ri-add-line me-1"></i>Create Project
                     </a>
                  </div>

                  <!-- Relationship Summary -->
                  <?php
                  if($clientRelationships && is_array($clientRelationships) && count($clientRelationships) > 0):
                  ?>
                     <h6 class="fw-semibold mb-3">Team Members</h6>
                     <div class="card border-0 bg-light">
                        <div class="card-body p-3">
                           <?php
                           $displayLimit = 3;
                           $displayRelationships = array_slice($clientRelationships, 0, $displayLimit);
                           foreach($displayRelationships as $relationship):
                           ?>
                              <div class="d-flex align-items-center mb-2">
                                 <div class="avatar avatar-sm rounded-circle bg-primary text-white me-2">
                                    <?= Utility::generate_initials($relationship->employeeName ?? 'NA') ?>
                                 </div>
                                 <div class="flex-grow-1">
                                    <div class="small fw-semibold"><?= htmlspecialchars($relationship->employeeName ?? 'N/A') ?></div>
                                    <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($relationship->relationshipTypeName ?? '') ?></div>
                                 </div>
                              </div>
                           <?php endforeach; ?>
                           <?php if(count($clientRelationships) > $displayLimit): ?>
                              <button type="button" class="btn btn-sm btn-outline-primary w-100 mt-2" onclick="document.querySelector('#relationships-tab').click()">
                                 View All <?= count($clientRelationships) ?> Team Members
                              </button>
                           <?php endif; ?>
                        </div>
                     </div>
                  <?php else: ?>
                     <h6 class="fw-semibold mb-3">Team Members</h6>
                     <div class="text-center py-3 bg-light rounded-3">
                        <i class="ri-team-line fs-24 text-muted mb-2 d-block"></i>
                        <p class="text-muted small mb-0">No team assigned</p>
                     </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>

         <!-- Contacts & Addresses Tab -->
         <div class="tab-pane fade" id="contacts" role="tabpanel">
            <?php include "includes/scripts/clients/client_addresses_contacts_script.php"; ?>
         </div>

         <!-- Sales & Projects Tab -->
         <div class="tab-pane fade" id="sales-projects" role="tabpanel">
            <?php include "includes/scripts/clients/sales_projects.php"; ?>
         </div>

         <!-- Documents Tab -->
         <div class="tab-pane fade" id="documents" role="tabpanel">
            <?php include "includes/scripts/clients/client_document_script.php"; ?>
         </div>

         <!-- Activities Tab -->
         <div class="tab-pane fade" id="activities" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-3">
               <h6 class="fw-semibold mb-0">
                  <i class="ri-calendar-check-line me-2 text-primary"></i>Client Activities
               </h6>
            </div>
            <?php if($recentActivities && count($recentActivities) > 0): ?>
               <div class="row g-3">
                  <?php foreach($recentActivities as $activity): ?>
                     <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100 activity-card">
                           <div class="card-body">
                              <div class="d-flex justify-content-between align-items-start mb-2">
                                 <span class="badge bg-primary-transparent text-primary"><?= htmlspecialchars($activity->activityTypeName ?? 'Activity') ?></span>
                                 <small class="text-muted"><?= Utility::date_format($activity->activityDate) ?></small>
                              </div>
                              <h6 class="mb-2 fw-semibold"><?= htmlspecialchars($activity->activityName) ?></h6>
                              <?php if($activity->activityDescription): ?>
                                 <p class="text-muted small mb-2"><?= nl2br(htmlspecialchars(substr($activity->activityDescription, 0, 100))) ?><?= strlen($activity->activityDescription) > 100 ? '...' : '' ?></p>
                              <?php endif; ?>
                              <div class="border-top pt-2 mt-2">
                                 <small class="text-muted">
                                    <i class="ri-user-line me-1"></i><?= htmlspecialchars($activity->activityOwnerName ?? 'N/A') ?>
                                 </small>
                              </div>
                           </div>
                        </div>
                     </div>
                  <?php endforeach; ?>
               </div>
            <?php else: ?>
               <div class="text-center py-5">
                  <i class="ri-calendar-line fs-48 text-muted mb-3 d-block"></i>
                  <p class="text-muted">No activities yet</p>
               </div>
            <?php endif; ?>
         </div>

         <!-- Relationships Tab -->
         <div class="tab-pane fade" id="relationships" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-4">
               <div>
                  <h5 class="mb-1 fw-semibold">Client Relationship Matrix</h5>
                  <p class="text-muted small mb-0">Manage escalation hierarchy and team assignments</p>
               </div>
               <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manageClientRelationshipModal">
                  <i class="ri-user-add-line me-1"></i>Add Relationship
               </button>
            </div>

            <?php include "includes/scripts/clients/client_relationship_management_script.php"; ?>
         </div>

         <!-- Financials Tab -->
         <div class="tab-pane fade" id="financials" role="tabpanel">
            <?php include "includes/scripts/clients/financials.php"; ?>
         </div>
      </div>
   </div>
</div>

<!-- ============================================================================
     MODALS - Client Management
     ============================================================================ -->
<?php
// Add Client Relationship Modal
echo Utility::form_modal_header("manageClientRelationshipModal", "clients/manage_client_relationship.php", "Manage Client Relationship", array('modal-md', 'modal-dialog-centered'), $base);
include "includes/scripts/clients/modals/manage_client_relationship.php";
echo Utility::form_modal_footer('Save Relationship', 'saveClientRelationship',  ' btn btn-success btn-sm', true);

// Add Client Contact Modal (if not already included)
if(!isset($clientContactModalIncluded)) {
   echo Utility::form_modal_header("manageClientContactModal", "clients/manage_client_contact.php", "Manage Client Contact", array('modal-lg', 'modal-dialog-centered'), $base);
   include "includes/scripts/clients/modals/manage_client_contact.php";
   echo Utility::form_modal_footer('Save Contact', 'saveClientContact',  ' btn btn-success btn-sm', true);
   $clientContactModalIncluded = true;
}

// Add Client Address Modal (if not already included)
if(!isset($clientAddressModalIncluded)) {
   echo Utility::form_modal_header("manageClientAddressModal", "clients/manage_client_address.php", "Manage Client Address", array('modal-lg', 'modal-dialog-centered'), $base);
   // Check if manage_client_address.php exists, otherwise use primary contact modal
   if(file_exists("includes/scripts/clients/modals/manage_client_address.php")) {
      include "includes/scripts/clients/modals/manage_client_address.php";
   } else {
      include "includes/scripts/clients/modals/manage_client_primary_contact.php";
   }
   echo Utility::form_modal_footer('Save Address', 'saveClientAddress',  ' btn btn-success btn-sm', true);
   $clientAddressModalIncluded = true;
}

// Add Client Document Modal (if not already included)
if(!isset($clientDocumentModalIncluded)) {
   echo Utility::form_modal_header("manageClientDocumentModal", "clients/manage_client_documents.php", "Manage Client Documents", array('modal-lg', 'modal-dialog-centered'), $base);
   include "includes/scripts/clients/modals/manage_client_documents.php";
   echo Utility::form_modal_footer('Save Document', 'saveClientDocument',  ' btn btn-success btn-sm', true);
   $clientDocumentModalIncluded = true;
}
?>

<!-- External JavaScript -->
<script src="<?= $base ?>html/assets/js/client_details.js"></script>

<!-- ============================================================================
     INLINE CONTEXTUAL HELP - Replacing Modal Documentation
     ============================================================================ -->
<div class="d-none">
<!-- Help modals removed - replaced with inline contextual help throughout the interface -->
</div>

<!-- Keep modal structure but hide documentation modals -->
<div class="modal fade d-none" id="clientDetailsDocModal" tabindex="-1" aria-labelledby="clientDetailsDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="clientDetailsDocModalLabel">
                    <i class="ri-edit-line me-2"></i>
                    Editing Client Details Guide
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <p class="text-muted">
                        Learn how to edit and update client information including basic details, industry classification,
                        and account ownership.
                    </p>
                </div>

                <div class="card border-primary-transparent mb-3">
                    <div class="card-body">
                        <h6 class="text-primary mb-3">
                            <i class="ri-pencil-line me-2"></i>
                            How to Edit Client Information
                        </h6>
                        <ol class="mb-0">
                            <li class="mb-2">
                                <strong>Access Edit Mode:</strong> Click the pencil icon (<i class="ri-pencil-line"></i>)
                                next to the client name at the top of the page, or click the edit button in the "Client Details" card.
                            </li>
                            <li class="mb-2">
                                <strong>Update Information:</strong> Modify any of the following fields:
                                <ul class="mt-2">
                                    <li><strong>Client Code:</strong> Unique identifier for the client</li>
                                    <li><strong>Client Name:</strong> Official name of the client organization</li>
                                    <li><strong>VAT Number:</strong> Tax identification number</li>
                                    <li><strong>Client Type:</strong> Classification level (e.g., Premium, Standard)</li>
                                    <li><strong>Client Industry:</strong> Select from industry sectors and specific industries</li>
                                    <li><strong>Account Owner:</strong> Assign the employee responsible for managing this client</li>
                                    <li><strong>Client Description:</strong> Additional notes and information about the client</li>
                                </ul>
                            </li>
                            <li class="mb-2">
                                <strong>Select Industry:</strong> Click the industry dropdown to browse by sector, then select
                                the specific industry. The selection will update automatically.
                            </li>
                            <li class="mb-2">
                                <strong>Save Changes:</strong> Click the "Save Changes" button at the bottom of the form to
                                apply your updates.
                            </li>
                        </ol>
                    </div>
                </div>

                <div class="alert alert-info mb-0">
                    <h6 class="alert-heading">
                        <i class="ri-lightbulb-line me-2"></i>
                        Tips
                    </h6>
                    <ul class="mb-0 small">
                        <li>Keep client information up to date for accurate reporting</li>
                        <li>Use the description field to add important notes about the client</li>
                        <li>Ensure the Account Owner is correctly assigned for proper accountability</li>
                        <li>Select the appropriate industry for better categorization and reporting</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================================
     CLIENT DOCUMENTS DOCUMENTATION MODAL
     ============================================================================ -->
<div class="modal fade" id="clientDocumentsDocModal" tabindex="-1" aria-labelledby="clientDocumentsDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="clientDocumentsDocModalLabel">
                    <i class="ri-file-text-line me-2"></i>
                    Document Management Guide
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <p class="text-muted">
                        Learn how to upload, manage, and organize client documents with information about supported file types
                        and formats.
                    </p>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="card border-info-transparent h-100">
                            <div class="card-body">
                                <h6 class="text-info">
                                    <i class="ri-upload-cloud-line me-2"></i>
                                    Adding Documents
                                </h6>
                                <ol class="small mb-0">
                                    <li>Click the <i class="ri-add-line"></i> button in the "Client Documents" section</li>
                                    <li>Fill in the document name and description</li>
                                    <li>Select the document type from the dropdown</li>
                                    <li>Choose the file to upload</li>
                                    <li>Click "Save Client Documents"</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-success-transparent h-100">
                            <div class="card-body">
                                <h6 class="text-success">
                                    <i class="ri-edit-box-line me-2"></i>
                                    Editing Documents
                                </h6>
                                <ol class="small mb-0">
                                    <li>Click the edit icon (<i class="ri-pencil-line"></i>) on any document card</li>
                                    <li>Update the document name, description, or type</li>
                                    <li>Optionally upload a new file to replace the existing one</li>
                                    <li>Save your changes</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <h6 class="alert-heading">
                        <i class="ri-file-settings-line me-2"></i>
                        Allowed Document Types and Formats
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Document Formats:</strong>
                            <ul class="mb-0 small">
                                <li><strong>PDF:</strong> .pdf (Portable Document Format)</li>
                                <li><strong>Word:</strong> .doc, .docx (Microsoft Word)</li>
                                <li><strong>Excel:</strong> .xls, .xlsx (Microsoft Excel)</li>
                                <li><strong>PowerPoint:</strong> .ppt, .pptx (Microsoft PowerPoint)</li>
                                <li><strong>Text:</strong> .txt, .csv (Plain text and CSV files)</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <strong>Image Formats:</strong>
                            <ul class="mb-0 small">
                                <li><strong>Images:</strong> .jpg, .jpeg, .png, .gif</li>
                                <li><strong>Archives:</strong> .zip, .rar, .gz, .tgz</li>
                            </ul>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <strong class="text-warning">
                                <i class="ri-alert-line me-1"></i>
                                File Size Limit:
                            </strong>
                            Maximum file size is <strong>10 MB</strong> per document.
                            For larger files, consider compressing them or splitting into multiple documents.
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <strong>Common Document Types:</strong>
                            <ul class="mb-0 small">
                                <li>Registration Certificates</li>
                                <li>Tax Identification Documents</li>
                                <li>Business Licenses</li>
                                <li>Identity Documents (for authorized signatories)</li>
                                <li>Bank Statements</li>
                                <li>Contracts and Agreements</li>
                                <li>Compliance Certificates</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="alert alert-success mb-0">
                    <h6 class="alert-heading">
                        <i class="ri-lightbulb-line me-2"></i>
                        Best Practices
                    </h6>
                    <ul class="mb-0 small">
                        <li>Upload important documents immediately after receiving them</li>
                        <li>Use descriptive names for documents to make them easy to find</li>
                        <li>Ensure document file names are clear and descriptive before uploading</li>
                        <li>Select the appropriate document type for better organization</li>
                        <li>Add descriptions to provide context about the document</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================================
     CONTACTS & ADDRESSES DOCUMENTATION MODAL
     ============================================================================ -->
<div class="modal fade" id="contactsAddressesDocModal" tabindex="-1" aria-labelledby="contactsAddressesDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="contactsAddressesDocModalLabel">
                    <i class="ri-contacts-line me-2"></i>
                    Contacts & Addresses Management Guide
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <p class="text-muted">
                        Learn how to manage client addresses and contacts, including adding, editing, and organizing
                        multiple addresses and contact persons.
                    </p>
                </div>

                <!-- Address Management -->
                <div class="mb-4">
                    <h6 class="text-primary mb-3">
                        <i class="ri-map-pin-line me-2"></i>
                        Managing Client Addresses
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-primary-transparent h-100">
                                <div class="card-body">
                                    <h6 class="text-primary">
                                        <i class="ri-add-circle-line me-2"></i>
                                        Adding a New Address
                                    </h6>
                                    <ol class="small mb-0">
                                        <li>Click the <i class="ri-add-line"></i> button in the "Address" section</li>
                                        <li>Fill in the address details:
                                            <ul>
                                                <li>Complete street address</li>
                                                <li>City</li>
                                                <li>Postal/ZIP code</li>
                                                <li>Country</li>
                                            </ul>
                                        </li>
                                        <li>Select the address type:
                                            <ul>
                                                <li><strong>Office Address:</strong> Business location</li>
                                                <li><strong>Postal Address:</strong> Mailing address</li>
                                            </ul>
                                        </li>
                                        <li>Set address flags:
                                            <ul>
                                                <li><strong>Billing Address:</strong> For invoicing purposes</li>
                                                <li><strong>Headquarters:</strong> Main business location</li>
                                            </ul>
                                        </li>
                                        <li>Click "Save Primary Contact" to save</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-success-transparent h-100">
                                <div class="card-body">
                                    <h6 class="text-success">
                                        <i class="ri-edit-box-line me-2"></i>
                                        Editing Addresses
                                    </h6>
                                    <ol class="small mb-0">
                                        <li>Click the edit icon (<i class="ri-pencil-line"></i>) on the address card</li>
                                        <li>Update any address fields as needed</li>
                                        <li>Modify address type or flags if required</li>
                                        <li>Save your changes</li>
                                    </ol>
                                    <div class="mt-3 p-2 bg-light rounded">
                                        <strong class="text-muted small">Note:</strong>
                                        <p class="mb-0 small text-muted">
                                            You can have multiple addresses per client. Only one address can be marked as
                                            "Headquarters" and "Billing Address" at a time.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Management -->
                <div class="mb-4">
                    <h6 class="text-primary mb-3">
                        <i class="ri-user-line me-2"></i>
                        Managing Client Contacts
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-info-transparent h-100">
                                <div class="card-body">
                                    <h6 class="text-info">
                                        <i class="ri-user-add-line me-2"></i>
                                        Adding a New Contact
                                    </h6>
                                    <ol class="small mb-0">
                                        <li>Click the <i class="ti ti-user-plus"></i> button in the "Contacts" section</li>
                                        <li>Fill in contact information:
                                            <ul>
                                                <li>First Name and Last Name</li>
                                                <li>Title/Position</li>
                                                <li>Email address</li>
                                                <li>Phone number</li>
                                                <li>Salutation (Mr., Mrs., Dr., etc.)</li>
                                            </ul>
                                        </li>
                                        <li>Select the contact type/role:
                                            <ul>
                                                <li>Primary Contact</li>
                                                <li>Billing Contact</li>
                                                <li>Technical Contact</li>
                                                <li>Decision Maker</li>
                                                <li>Other roles as defined</li>
                                            </ul>
                                        </li>
                                        <li>Associate with an address (optional)</li>
                                        <li>Click "Save Client Contact"</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-warning-transparent h-100">
                                <div class="card-body">
                                    <h6 class="text-warning">
                                        <i class="ri-user-settings-line me-2"></i>
                                        Editing Contacts
                                    </h6>
                                    <ol class="small mb-0">
                                        <li>Click the edit icon on any contact card</li>
                                        <li>Update contact details as needed</li>
                                        <li>Change contact type or role if required</li>
                                        <li>Update associated address if needed</li>
                                        <li>Save your changes</li>
                                    </ol>
                                    <div class="mt-3 p-2 bg-light rounded">
                                        <strong class="text-muted small">Best Practices:</strong>
                                        <ul class="mb-0 small text-muted">
                                            <li>Keep contact information up to date</li>
                                            <li>Assign appropriate contact types for easy filtering</li>
                                            <li>Link contacts to relevant addresses when applicable</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-success mb-0">
                    <h6 class="alert-heading">
                        <i class="ri-lightbulb-line me-2"></i>
                        Tips
                    </h6>
                    <ul class="mb-0 small">
                        <li>Mark the correct address as "Headquarters" for accurate location data</li>
                        <li>Assign appropriate contact types to enable filtering and targeted communication</li>
                        <li>Keep all contact information current for effective communication</li>
                        <li>Link contacts to addresses when they work at specific locations</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================================
     CLIENT RELATIONSHIPS DOCUMENTATION MODAL
     ============================================================================ -->
<?php
// Get client relationship types from config
$clientRelationshipTypes = isset($config['clientRelationshipTypes']) ? $config['clientRelationshipTypes'] : [];
?>
<div class="modal fade" id="clientRelationshipsDocModal" tabindex="-1" aria-labelledby="clientRelationshipsDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="clientRelationshipsDocModalLabel">
                    <i class="ri-team-line me-2"></i>
                    Client Relationships (Escalation Matrix) Guide
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <p class="text-muted">
                        Learn how to manage client relationships and set up the escalation matrix to ensure proper
                        communication channels and accountability within your organization.
                    </p>
                </div>

                <div class="card border-primary-transparent mb-3">
                    <div class="card-body">
                        <h6 class="text-primary mb-2">
                            <i class="ri-add-line me-2"></i>
                            Adding a Relationship
                        </h6>
                        <ol class="mb-0">
                            <li>Click the "Add Relationship" button (<i class="ti ti-user-plus"></i>) in the Relationships section</li>
                            <li>Select the employee/team member to assign</li>
                            <li>Choose the relationship type from the available options</li>
                            <li>Save the relationship</li>
                        </ol>
                    </div>
                </div>

                <div class="card border-success-transparent mb-3">
                    <div class="card-body">
                        <h6 class="text-success mb-2">
                            <i class="ri-edit-line me-2"></i>
                            Editing Relationships
                        </h6>
                        <ol class="mb-0">
                            <li>Click the edit icon (<i class="ri-pencil-line"></i>) on any relationship card</li>
                            <li>Update the relationship details</li>
                            <li>Modify the relationship type or employee assignment as needed</li>
                            <li>Save your changes</li>
                        </ol>
                    </div>
                </div>

                <!-- Available Relationship Types -->
                <div class="mb-3">
                    <h6 class="text-primary mb-3">
                        <i class="ri-list-check me-2"></i>
                        Available Relationship Types
                    </h6>
                    <?php if (!empty($clientRelationshipTypes)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th>Relationship Type</th>
                                    <th>Level</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Sort by level for better display
                                $sortedTypes = $clientRelationshipTypes;
                                usort($sortedTypes, function($a, $b) {
                                    return (int)$a->level - (int)$b->level;
                                });

                                foreach ($sortedTypes as $type):
                                    $levelDescriptions = [
                                        '1' => 'Highest level - Partner responsible for client liaison',
                                        '2' => 'Partner level - Manages engagement with client',
                                        '3' => 'Management level - Oversees client operations',
                                        '4' => 'Associate level - Handles day-to-day client work',
                                        '5' => 'Entry level - Intern or junior associate',
                                        '6' => 'General - All employees can be assigned'
                                    ];
                                    $description = isset($levelDescriptions[$type->level]) ? $levelDescriptions[$type->level] : 'Client relationship assignment';
                                ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($type->value) ?></strong></td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-transparent">Level <?= htmlspecialchars($type->level) ?></span>
                                    </td>
                                    <td class="small text-muted"><?= htmlspecialchars($description) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="ri-alert-line me-2"></i>
                        No relationship types configured. Please contact your administrator.
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Relationship Level Hierarchy -->
                <div class="mb-3">
                    <h6 class="text-primary mb-3">
                        <i class="ri-hierarchy me-2"></i>
                        Escalation Hierarchy
                    </h6>
                    <div class="card border-info-transparent">
                        <div class="card-body">
                            <p class="mb-2 small text-muted">
                                Relationship types are organized by hierarchy levels. When assigning relationships,
                                the system will filter available employees based on their job titles and the relationship level:
                            </p>
                            <ul class="mb-0 small">
                                <li><strong>Level 1-2:</strong> Partners and Directors</li>
                                <li><strong>Level 3:</strong> Managers, Senior Managers, and Directors</li>
                                <li><strong>Level 4:</strong> Associates and Senior Associates</li>
                                <li><strong>Level 5:</strong> Interns</li>
                                <li><strong>Level 6:</strong> All employees (no restriction)</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning mb-0">
                    <h6 class="alert-heading">
                        <i class="ri-information-line me-2"></i>
                        Escalation Matrix Best Practices
                    </h6>
                    <ul class="mb-0 small">
                        <li>Always assign a <strong>Client Liaison Partner</strong> (Level 1) for primary client contact</li>
                        <li>Assign an <strong>Engagement Partner</strong> (Level 2) to manage the engagement</li>
                        <li>Use <strong>Manager</strong> (Level 3) for operational oversight</li>
                        <li>Assign <strong>Associates</strong> (Level 4-5) for day-to-day client work</li>
                        <li>Follow the hierarchy levels for proper escalation paths</li>
                        <li>Review and update relationships regularly to ensure accuracy</li>
                        <li>Ensure appropriate employees are assigned based on their job titles and relationship levels</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>
                    Close
                </button>
            </div>
        </div>
    </div>
</div>