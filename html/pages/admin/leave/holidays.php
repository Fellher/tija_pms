<?php
/**
 * Enhanced Holidays Management - Admin Page
 * Multi-jurisdiction holiday management with employee applicability
 */

if(!$isValidUser) {
    Alert::info("You need to be logged in to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

// Check admin permissions
if (!$isAdmin && !$isValidAdmin && !$isHRManager) {
    Alert::error("Access denied. Leave Administrator privileges required.", true, array('text-center'));
    return;
}

$entityID = $_SESSION['entityID'] ?? 1;
$pageTitle = 'Multi-Jurisdiction Holidays Management';
$title = $pageTitle . ' - Leave Management System';

// Get holidays with jurisdiction information
$holidaysList = Data::holidays([], false, $DBConn) ?: [];
$countries = Data::countries([], false, $DBConn) ?: [];
$entities = Data::entities([], false, $DBConn) ?: [];
?>

<style>
/* Enhanced Holidays Styles */
.jurisdiction-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
}

.holiday-card {
    transition: all 0.3s ease;
    border-left: 4px solid;
}

.holiday-card.country-level {
    border-left-color: #0052CC;
}

.holiday-card.region-level {
    border-left-color: #00875A;
}

.holiday-card.entity-level {
    border-left-color: #FF8B00;
}

.holiday-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
}

.applicability-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #F4F5F7;
    border-radius: 8px;
    font-size: 0.875rem;
}
</style>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-24 mb-0">
            <i class="ri-calendar-event-line me-2 text-warning"></i>
            <?= $pageTitle ?>
        </h1>
        <p class="text-muted mb-0 mt-2">Configure public holidays across multiple jurisdictions and manage employee applicability</p>
    </div>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= $base ?>html/?s=admin&ss=leave&p=dashboard">Leave Admin</a></li>
                <li class="breadcrumb-item active" aria-current="page">Holidays</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Dashboard Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="mb-2">
                    <i class="ri-calendar-event-line text-primary" style="font-size: 2.5rem;"></i>
                </div>
                <h3 class="mb-0"><?= count($holidaysList ?? []) ?></h3>
                <p class="text-muted mb-0 small">Total Holidays</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="mb-2">
                    <i class="ri-global-line text-info" style="font-size: 2.5rem;"></i>
                </div>
                <h3 class="mb-0">
                    <?php
                    $uniqueCountries = [];
                    if (is_array($holidaysList)) {
                        foreach ($holidaysList as $h) {
                            if (isset($h->countryID) && $h->countryID !== 'all' && !in_array($h->countryID, $uniqueCountries)) {
                                $uniqueCountries[] = $h->countryID;
                            }
                        }
                    }
                    echo count($uniqueCountries);
                    ?>
                </h3>
                <p class="text-muted mb-0 small">Countries</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="mb-2">
                    <i class="ri-refresh-line text-success" style="font-size: 2.5rem;"></i>
                </div>
                <h3 class="mb-0">
                    <?= count(array_filter($holidaysList ?? [], function($h) { return $h->repeatsAnnually === 'Y'; })) ?>
                </h3>
                <p class="text-muted mb-0 small">Annual Recurring</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="mb-2">
                    <i class="ri-calendar-check-line text-warning" style="font-size: 2.5rem;"></i>
                </div>
                <h3 class="mb-0">
                    <?php
                    $upcoming = array_filter($holidaysList ?? [], function($h) {
                        return strtotime($h->holidayDate) >= strtotime(date('Y-m-d'));
                    });
                    echo count($upcoming);
                    ?>
                </h3>
                <p class="text-muted mb-0 small">Upcoming</p>
            </div>
        </div>
    </div>
</div>

<!-- Recurring Holidays Management Banner -->
<?php
$recurringCount = count(array_filter($holidaysList ?? [], function($h) { return $h->repeatsAnnually === 'Y'; }));
if ($recurringCount > 0):
?>
<div class="alert alert-info d-flex align-items-center justify-content-between mb-4">
    <div>
        <i class="ri-repeat-line me-2 fs-5"></i>
        <strong><?= $recurringCount ?> Annual Recurring Holidays</strong> configured
        <p class="mb-0 small">These holidays automatically repeat every year on the same date</p>
    </div>
    <div>
        <button class="btn btn-sm btn-outline-primary me-2" onclick="showRecurringHolidays()">
            <i class="ri-eye-line me-1"></i>View Recurring
        </button>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#generateAnnualModal">
            <i class="ri-calendar-check-line me-1"></i>Generate for Year
        </button>
    </div>
</div>
<?php endif; ?>

<!-- Holiday Type Tabs -->
<ul class="nav nav-tabs nav-tabs-header mb-4" role="tablist" id="holidayTabs">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="global-tab" data-bs-toggle="tab" data-bs-target="#global-holidays" type="button" role="tab">
            <i class="ri-global-line me-2"></i>Global Holidays
            <span class="badge bg-purple ms-2" id="global-count">0</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="jurisdictional-tab" data-bs-toggle="tab" data-bs-target="#jurisdictional-holidays" type="button" role="tab">
            <i class="ri-map-pin-line me-2"></i>Jurisdictional
            <span class="badge bg-info ms-2" id="jurisdictional-count">0</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="entity-tab" data-bs-toggle="tab" data-bs-target="#entity-holidays" type="button" role="tab">
            <i class="ri-building-line me-2"></i>Entity-Level
            <span class="badge bg-warning ms-2" id="entity-count">0</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="adhoc-tab" data-bs-toggle="tab" data-bs-target="#adhoc-holidays" type="button" role="tab">
            <i class="ri-calendar-event-line me-2"></i>Ad-Hoc Holidays
            <span class="badge bg-secondary ms-2" id="adhoc-count">0</span>
        </button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content" id="holidayTabsContent">
    <!-- Global Holidays Tab -->
    <div class="tab-pane fade show active" id="global-holidays" role="tabpanel">
        <div class="alert alert-info mb-3">
            <i class="ri-information-line me-2"></i>
            <strong>Global Holidays:</strong> Apply to all employees across all entities and jurisdictions. Use for company-wide holidays like New Year's Day.
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" class="form-control" id="globalSearch" placeholder="Search global holidays...">
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manageHolidays" onclick="setHolidayType('global')">
                    <i class="ri-add-line me-1"></i>Add Global Holiday
                </button>
            </div>
        </div>
        <div id="globalHolidaysTable"></div>
    </div>

    <!-- Jurisdictional Holidays Tab -->
    <div class="tab-pane fade" id="jurisdictional-holidays" role="tabpanel">
        <div class="alert alert-info mb-3">
            <i class="ri-information-line me-2"></i>
            <strong>Jurisdictional Holidays:</strong> Apply to specific countries, regions, or cities. Use for national holidays, regional observances, or local public holidays.
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <select id="jurisdictionCountryFilter" class="form-select">
                    <option value="">All Countries</option>
                    <?php if (is_array($countries)): ?>
                        <?php foreach ($countries as $country): ?>
                            <?php if (is_object($country) && isset($country->countryID) && isset($country->countryName)): ?>
                            <option value="<?= $country->countryID ?>"><?= htmlspecialchars($country->countryName) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-4">
                <select id="jurisdictionLevelFilter" class="form-select">
                    <option value="">All Levels</option>
                    <option value="country">Country</option>
                    <option value="region">Region</option>
                    <option value="city">City</option>
                </select>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manageHolidays" onclick="setHolidayType('jurisdictional')">
                    <i class="ri-add-line me-1"></i>Add Jurisdictional Holiday
                </button>
            </div>
        </div>
        <div id="jurisdictionalHolidaysTable"></div>
    </div>

    <!-- Entity-Level Holidays Tab -->
    <div class="tab-pane fade" id="entity-holidays" role="tabpanel">
        <div class="alert alert-warning mb-3">
            <i class="ri-information-line me-2"></i>
            <strong>Entity-Level Holidays:</strong> Apply only to specific entities within your organization. Use for entity-specific observances or local office closures.
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <select id="entityFilter" class="form-select">
                    <option value="">All Entities</option>
                    <?php if (is_array($entities)): ?>
                        <?php foreach ($entities as $entity): ?>
                            <?php if (is_object($entity) && isset($entity->entityID) && isset($entity->entityName)): ?>
                            <option value="<?= $entity->entityID ?>"><?= htmlspecialchars($entity->entityName) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manageHolidays" onclick="setHolidayType('entity')">
                    <i class="ri-add-line me-1"></i>Add Entity Holiday
                </button>
            </div>
        </div>
        <div id="entityHolidaysTable"></div>
    </div>

    <!-- Ad-Hoc Holidays Tab -->
    <div class="tab-pane fade" id="adhoc-holidays" role="tabpanel">
        <div class="alert alert-secondary mb-3">
            <i class="ri-information-line me-2"></i>
            <strong>Ad-Hoc Holidays:</strong> One-time holidays that don't repeat annually. Use for special occasions, company events, or unexpected closures.
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" class="form-control" id="adhocSearch" placeholder="Search ad-hoc holidays...">
            </div>
            <div class="col-md-6 text-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manageHolidays" onclick="setHolidayType('adhoc')">
                    <i class="ri-add-line me-1"></i>Add Ad-Hoc Holiday
                </button>
            </div>
        </div>
        <div id="adhocHolidaysTable"></div>
    </div>
</div>

<!-- Filters and Actions (Legacy - kept for compatibility) -->
<div class="row mb-3 d-none">
    <div class="col-md-3">
        <select id="countryFilter" class="form-select">
            <option value="">All Countries</option>
            <?php if (is_array($countries)): ?>
                <?php foreach ($countries as $country): ?>
                    <?php if (is_object($country) && isset($country->countryID) && isset($country->countryName)): ?>
                    <option value="<?= $country->countryID ?>"><?= htmlspecialchars($country->countryName) ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
    <div class="col-md-2">
        <select id="typeFilter" class="form-select">
            <option value="">All Types</option>
            <option value="full_day">Full Day</option>
            <option value="half_day">Half Day</option>
        </select>
    </div>
    <div class="col-md-2">
        <select id="jurisdictionFilter" class="form-select">
            <option value="">All Jurisdictions</option>
            <option value="global">Global</option>
            <option value="country">Country</option>
            <option value="region">Region</option>
            <option value="entity">Entity</option>
        </select>
    </div>
    <div class="col-md-2">
        <select id="recurringFilter" class="form-select">
            <option value="">All Holidays</option>
            <option value="recurring">Recurring Only</option>
            <option value="onetime">One-Time Only</option>
        </select>
    </div>
    <div class="col-md-3">
        <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#manageHolidays">
            <i class="ri-add-line me-1"></i>Add Holiday
        </button>
    </div>
</div>

<!-- Holidays Table -->
<div class="card custom-card shadow-sm">
   <div class="card-header bg-white border-bottom">
      <h4 class="card-title mb-0">
         <i class="ri-calendar-event-line me-2"></i>
         Holidays Calendar
      </h4>
   </div>
   <div class="card-body">
      <div class="table-responsive">
         <table id="holidays_table" class="table table-hover table-bordered align-middle" style="width: 100%;">
            <thead class="table-light">
               <tr>
                  <th class="text-center" style="width: 50px;">#</th>
                  <th>Holiday Name</th>
                  <th style="width: 150px;">Date</th>
                  <th style="width: 100px;">Type</th>
                  <th>Jurisdiction</th>
                  <th style="width: 120px;" class="text-center">Applicability</th>
                  <th style="width: 100px;" class="text-center">Recurring</th>
                  <th style="width: 150px;" class="text-center">Actions</th>
               </tr>
            </thead>
            <tbody>
               <?php
               if($holidaysList) {
                  foreach ($holidaysList as $key => $holiday) {
                     $holidayDate = date_format(date_create($holiday->holidayDate), 'D, d M Y');
                     $isPast = strtotime($holiday->holidayDate) < strtotime(date('Y-m-d'));

                     // Determine jurisdiction level
                     $jurisdictionLevel = 'Country';
                     $jurisdictionBadge = 'primary';

                     if (isset($holiday->jurisdictionLevel)) {
                        switch ($holiday->jurisdictionLevel) {
                           case 'global':
                              $jurisdictionLevel = 'Global';
                              $jurisdictionBadge = 'purple';
                              break;
                           case 'region':
                              $jurisdictionLevel = 'Region';
                              $jurisdictionBadge = 'success';
                              break;
                           case 'city':
                              $jurisdictionLevel = 'City';
                              $jurisdictionBadge = 'info';
                              break;
                           case 'entity':
                              $jurisdictionLevel = 'Entity';
                              $jurisdictionBadge = 'warning';
                              break;
                           default:
                              $jurisdictionLevel = 'Country';
                              $jurisdictionBadge = 'primary';
                        }
                     }
                     ?>
                     <tr class="<?= $isPast ? 'table-secondary' : '' ?>">
                        <td class="text-center fw-bold"><?php echo $key + 1; ?></td>
                        <td>
                           <div class="d-flex align-items-center">
                              <i class="ri-calendar-2-line me-2 text-warning"></i>
                              <strong><?php echo htmlspecialchars($holiday->holidayName); ?></strong>
                           </div>
                        </td>
                        <td>
                           <?php if ($isPast): ?>
                              <span class="text-muted"><?php echo $holidayDate; ?></span>
                           <?php else: ?>
                              <strong><?php echo $holidayDate; ?></strong>
                           <?php endif; ?>
                        </td>
                        <td>
                           <span class="badge bg-<?= $holiday->holidayType === 'full_day' ? 'primary' : 'info' ?>">
                              <?= ucwords(str_replace('_', ' ', $holiday->holidayType)); ?>
                           </span>
                        </td>
                        <td>
                           <div>
                              <span class="jurisdiction-badge bg-<?= $jurisdictionBadge ?> bg-opacity-10 text-<?= $jurisdictionBadge ?>">
                                 <i class="ri-map-pin-line"></i>
                                 <?= $jurisdictionLevel ?>
                              </span>
                           </div>
                           <small class="text-muted">
                              <i class="ri-global-line me-1"></i><?= htmlspecialchars($holiday->countryName ?? 'Not set') ?>
                           </small>
                        </td>
                        <td class="text-center">
                           <?php
                           // Get employee count for this jurisdiction
                           $applicableCount = '?';
                           if (isset($holiday->countryID)) {
                              if ($holiday->countryID === 'all' || (isset($holiday->jurisdictionLevel) && $holiday->jurisdictionLevel === 'global')) {
                                 $allEmployees = Employee::employees(['Suspended' => 'N'], false, $DBConn);
                                 $applicableCount = $allEmployees ? count($allEmployees) : 0;
                              } else {
                                 // For specific jurisdictions, show as unknown for now
                                 // Full calculation would require checking employee locations
                                 $applicableCount = '?';
                              }
                           }
                           ?>
                           <div class="applicability-indicator d-inline-flex">
                              <i class="ri-group-line text-primary"></i>
                              <span class="fw-bold"><?= $applicableCount ?></span>
                           </div>
                        </td>
                        <td class="text-center">
                           <?php if ($holiday->repeatsAnnually === 'Y'): ?>
                              <span class="badge bg-success">
                                 <i class="ri-repeat-line me-1"></i>Yes
                              </span>
                           <?php else: ?>
                              <span class="badge bg-secondary">Once</span>
                           <?php endif; ?>
                        </td>
                        <td class="text-center">
                           <div class="btn-group btn-group-sm" role="group">
                              <button
                                 type="button"
                                 class="btn btn-outline-primary editHolidayBtn"
                                 data-bs-toggle="modal"
                                 data-bs-target="#manageHolidays"
                                 data-holiday-id="<?php echo $holiday->holidayID; ?>"
                                 data-holiday-name="<?php echo htmlspecialchars($holiday->holidayName); ?>"
                                 data-holiday-date="<?php echo $holiday->holidayDate; ?>"
                                 data-holiday-type="<?php echo $holiday->holidayType; ?>"
                                 data-country-id="<?php echo $holiday->countryID; ?>"
                                 data-repeats-annually="<?php echo $holiday->repeatsAnnually; ?>"
                                 data-bs-toggle="tooltip" title="Edit">
                                 <i class="ri-edit-line"></i>
                              </button>
                              <button type="button"
                                 class="btn btn-outline-info view-applicability"
                                 data-holiday-id="<?php echo $holiday->holidayID; ?>"
                                 data-bs-toggle="tooltip" title="View Applicability">
                                 <i class="ri-group-line"></i>
                              </button>
                              <button type="button"
                                 class="btn btn-outline-danger delete-holiday"
                                 data-holiday-id="<?php echo $holiday->holidayID; ?>"
                                 data-bs-toggle="tooltip" title="Delete">
                                 <i class="ri-delete-bin-line"></i>
                              </button>
                           </div>
                        </td>
                     </tr>
                     <?php
                  }
               } else {
                  echo '<tr><td colspan="8" class="text-center py-5">
                     <i class="ri-calendar-line display-1 text-muted"></i>
                     <p class="mt-3 mb-0">No Holidays Configured</p>
                     <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#manageHolidays">
                        <i class="ri-add-line me-2"></i>Add First Holiday
                     </button>
                  </td></tr>';
               }?>
            </tbody>
         </table>
      </div>
   </div>
</div>

<!-- Back Button -->
<div class="row mt-4">
    <div class="col-12">
        <a href="<?= $base ?>html/?s=admin&ss=leave&p=dashboard" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i>
            Back to Dashboard
        </a>
    </div>
</div>

<!-- Enhanced Holidays Modal -->
<?php
   echo Utility::form_modal_header('manageHolidays', 'leave/manage_holidays.php', 'Manage Holiday', array("modal-dialog-centered", "modal-xl"), $base, true);
?>

<div class="modal-body">
   <div id="holidayForm">
      <input type="hidden" id="holidayID" name="holidayID" value="" />

      <!-- Basic Information Section -->
      <div class="mb-4">
         <h6 class="border-bottom pb-2 mb-3">
            <i class="ri-information-line me-2 text-primary"></i>
            Basic Information
         </h6>

         <div class="row">
            <div class="col-md-6">
               <div class="mb-3">
                  <label for="holidayName" class="form-label fw-semibold">
                     Holiday Name <span class="text-danger">*</span>
                  </label>
                  <input type="text" id="holidayName" name="holidayName"
                         class="form-control"
                         placeholder="e.g., New Year's Day, Independence Day"
                         required>
                  <div class="form-text">Enter a descriptive name for the holiday</div>
               </div>
            </div>

            <div class="col-md-6">
               <div class="mb-3">
                  <label for="holidayDate" class="form-label fw-semibold">
                     Holiday Date <span class="text-danger">*</span>
                  </label>
                  <input type="date" id="holidayDate" name="holidayDate"
                         class="form-control"
                         required>
                  <div class="form-text">Select the date of the holiday</div>
               </div>
            </div>
         </div>

         <div class="row">
            <div class="col-md-6">
               <div class="mb-3">
                  <label for="holidayType" class="form-label fw-semibold">
                     Holiday Type <span class="text-danger">*</span>
                  </label>
                  <select id="holidayType" name="holidayType" class="form-select" required>
                     <option value="">Select Type</option>
                     <option value="full_day">Full Day Holiday</option>
                     <option value="half_day">Half Day Holiday</option>
                  </select>
                  <div class="form-text">Full day or half day observance</div>
               </div>
            </div>

            <div class="col-md-6">
               <div class="mb-3">
                  <div class="form-check form-switch mt-4">
                     <input class="form-check-input" type="checkbox" id="repeatsAnnually" name="repeatsAnnually" value="Y">
                     <label class="form-check-label fw-semibold" for="repeatsAnnually">
                        <i class="ri-repeat-line me-1"></i>
                        Repeats Annually on Same Date
                     </label>
                  </div>
                  <div class="form-text">Holiday recurs every year (e.g., Christmas, New Year)</div>

                  <!-- Recurring Info Box -->
                  <div id="recurringInfo" class="alert alert-success mt-2 d-none">
                     <small>
                        <i class="ri-magic-line me-1"></i>
                        <strong>Auto-Generation Enabled:</strong> This holiday can be automatically generated for future years using the "Generate for Year" feature.
                     </small>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <!-- Jurisdiction Configuration Section -->
      <div class="mb-4">
         <h6 class="border-bottom pb-2 mb-3">
            <i class="ri-map-pin-line me-2 text-success"></i>
            Jurisdiction & Applicability
         </h6>

         <div class="alert alert-info mb-3">
            <i class="ri-information-line me-2"></i>
            <strong>Important:</strong> Define where this holiday applies. Holidays will only affect employees within the specified jurisdiction.
         </div>

         <div class="row">
            <div class="col-md-6">
               <div class="mb-3">
                  <label for="jurisdictionLevel" class="form-label fw-semibold">
                     Jurisdiction Level <span class="text-danger">*</span>
                  </label>
                  <select id="jurisdictionLevel" name="jurisdictionLevel" class="form-select" required>
                     <option value="">Select Level</option>
                     <option value="global">Global (All Countries)</option>
                     <option value="country">Country</option>
                     <option value="region">Region/State</option>
                     <option value="city">City</option>
                     <option value="entity">Specific Entity/Branch</option>
                  </select>
                  <div class="form-text">Defines the scope of this holiday</div>
               </div>
            </div>

            <div class="col-md-6">
               <div class="mb-3" id="countrySelection">
                  <label for="countryID" class="form-label fw-semibold">
                     Country <span class="text-danger">*</span>
                  </label>
                  <select id="countryID" name="countryID" class="form-select" required>
                     <option value="">Select Country</option>
                     <?php if (is_array($countries)): ?>
                        <?php foreach ($countries as $country): ?>
                           <?php if (is_object($country) && isset($country->countryID) && isset($country->countryName)): ?>
                           <option value="<?= $country->countryID ?>"><?= htmlspecialchars($country->countryName) ?></option>
                           <?php endif; ?>
                        <?php endforeach; ?>
                     <?php endif; ?>
                  </select>
                  <div class="form-text">Primary country for this holiday</div>
               </div>
            </div>
         </div>

         <div class="row">
            <div class="col-md-6 d-none" id="regionSelection">
               <div class="mb-3">
                  <label for="regionID" class="form-label fw-semibold">Region/State</label>
                  <input type="text" id="regionID" name="regionID" class="form-control"
                         placeholder="e.g., Nairobi, California, Lagos">
                  <div class="form-text">Specific region or state within the country</div>
               </div>
            </div>

            <div class="col-md-6 d-none" id="citySelection">
               <div class="mb-3">
                  <label for="cityID" class="form-label fw-semibold">City</label>
                  <input type="text" id="cityID" name="cityID" class="form-control"
                         placeholder="e.g., Nairobi, Los Angeles, Lagos">
                  <div class="form-text">Specific city</div>
               </div>
            </div>

            <div class="col-md-6 d-none" id="entitySelection">
               <div class="mb-3">
                  <label for="entitySpecific" class="form-label fw-semibold">Specific Entity/Branch</label>
                  <select id="entitySpecific" name="entitySpecific" class="form-select" multiple>
                     <option value="all">All Entities</option>
                     <?php if ($entities): ?>
                        <?php foreach ($entities as $entity): ?>
                        <option value="<?= $entity->entityID ?>">
                           <?= htmlspecialchars($entity->entityName) ?>
                        </option>
                        <?php endforeach; ?>
                     <?php endif; ?>
                  </select>
                  <div class="form-text">Select specific branches/entities (Ctrl+Click for multiple)</div>
               </div>
            </div>
         </div>
      </div>

      <!-- Employee Applicability Section -->
      <div class="mb-4">
         <h6 class="border-bottom pb-2 mb-3">
            <i class="ri-group-line me-2 text-warning"></i>
            Employee Applicability Rules
         </h6>

         <div class="row">
            <div class="col-md-6">
               <div class="mb-3">
                  <label for="applyToEmploymentTypes" class="form-label fw-semibold">Apply To Employment Types</label>
                  <select id="applyToEmploymentTypes" name="applyToEmploymentTypes[]" class="form-select" multiple>
                     <option value="all" selected>All Employment Types</option>
                     <option value="permanent">Permanent</option>
                     <option value="contract">Contract</option>
                     <option value="temporary">Temporary</option>
                     <option value="parttime">Part-Time</option>
                     <option value="intern">Intern</option>
                  </select>
                  <div class="form-text">Which employee types get this holiday</div>
               </div>
            </div>

            <div class="col-md-6">
               <div class="mb-3">
                  <label for="excludeBusinessUnits" class="form-label fw-semibold">Exclude Business Units (Optional)</label>
                  <select id="excludeBusinessUnits" name="excludeBusinessUnits[]" class="form-select" multiple>
                     <option value="">No Exclusions</option>
                     <!-- Business units would be loaded dynamically -->
                  </select>
                  <div class="form-text">Business units to exclude from this holiday</div>
               </div>
            </div>
         </div>

         <div class="row">
            <div class="col-md-12">
               <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" id="affectsLeaveBalance" name="affectsLeaveBalance" value="Y" checked>
                  <label class="form-check-label fw-semibold" for="affectsLeaveBalance">
                     Affects Leave Balance Calculations
                  </label>
               </div>
               <div class="form-text">When enabled, holidays within leave periods won't count against leave days</div>
            </div>
         </div>
      </div>

      <!-- Additional Notes -->
      <div class="mb-3">
         <label for="holidayNotes" class="form-label fw-semibold">
            <i class="ri-file-text-line me-1"></i>
            Additional Notes (Optional)
         </label>
         <textarea id="holidayNotes" name="holidayNotes" class="form-control" rows="2"
                   placeholder="Any special notes or observance details..."></textarea>
      </div>
   </div>
</div>

<?= Utility::form_modal_footer("Save Holiday", "manage_holiday_details", 'btn btn-primary'); ?>

<!-- Applicability Modal -->
<div class="modal fade" id="applicabilityModal" tabindex="-1">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header bg-info text-white">
            <h5 class="modal-title">
               <i class="ri-group-line me-2"></i>
               Holiday Applicability
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
         </div>
         <div class="modal-body">
            <div id="applicabilityContent">
               <!-- Content loaded dynamically -->
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" onclick="exportApplicableEmployees()">
               <i class="ri-download-line me-1"></i>
               Export List
            </button>
         </div>
      </div>
   </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
   // Initialize tooltips
   var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
   tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
   });

   // Jurisdiction level change handler
   document.getElementById('jurisdictionLevel')?.addEventListener('change', function() {
      const level = this.value;

      // Hide all optional selections
      document.getElementById('countrySelection').classList.add('d-none');
      document.getElementById('regionSelection')?.classList.add('d-none');
      document.getElementById('citySelection')?.classList.add('d-none');
      document.getElementById('entitySelection')?.classList.add('d-none');

      // Show relevant selections based on level
      if (level === 'global') {
         // No additional selections needed
         document.getElementById('countryID').value = 'all';
      } else if (level === 'country') {
         document.getElementById('countrySelection').classList.remove('d-none');
         document.getElementById('countryID').required = true;
      } else if (level === 'region') {
         document.getElementById('countrySelection').classList.remove('d-none');
         document.getElementById('regionSelection')?.classList.remove('d-none');
         document.getElementById('countryID').required = true;
      } else if (level === 'city') {
         document.getElementById('countrySelection').classList.remove('d-none');
         document.getElementById('regionSelection')?.classList.remove('d-none');
         document.getElementById('citySelection')?.classList.remove('d-none');
         document.getElementById('countryID').required = true;
      } else if (level === 'entity') {
         document.getElementById('entitySelection')?.classList.remove('d-none');
      }
   });

   // Edit holiday handler
   const editHolidayBtns = document.querySelectorAll('.editHolidayBtn');
   editHolidayBtns.forEach(function(button) {
      button.addEventListener('click', function() {
         const holidayForm = document.getElementById('holidayForm');
         if (!holidayForm) {
            console.error('Holiday form not found!');
            return;
         }

         const data = this.dataset;

         // Fill form fields
         document.getElementById('holidayID').value = data.holidayId || '';
         document.getElementById('holidayName').value = data.holidayName || '';
         document.getElementById('holidayDate').value = data.holidayDate || '';
         document.getElementById('holidayType').value = data.holidayType || '';
         document.getElementById('countryID').value = data.countryId || '';

         // Handle checkbox
         const checkbox = document.getElementById('repeatsAnnually');
         if (checkbox) {
            checkbox.checked = data.repeatsAnnually === 'Y';
         }
      });
   });

   // View applicability
   document.querySelectorAll('.view-applicability').forEach(btn => {
      btn.addEventListener('click', function() {
         const holidayID = this.dataset.holidayId;
         loadApplicability(holidayID);
      });
   });

   // Delete holiday
   document.querySelectorAll('.delete-holiday').forEach(btn => {
      btn.addEventListener('click', function() {
         if (confirm('Are you sure you want to delete this holiday? This will remove it from all employee calendars.')) {
            deleteHoliday(this.dataset.holidayId);
         }
      });
   });

   // Filters
   document.getElementById('countryFilter')?.addEventListener('change', filterHolidays);
   document.getElementById('typeFilter')?.addEventListener('change', filterHolidays);
   document.getElementById('jurisdictionFilter')?.addEventListener('change', filterHolidays);
   document.getElementById('recurringFilter')?.addEventListener('change', filterHolidays);

   // Highlight recurring checkbox importance
   const recurringCheckbox = document.getElementById('repeatsAnnually');
   if (recurringCheckbox) {
      recurringCheckbox.addEventListener('change', function() {
         const infoBox = document.getElementById('recurringInfo');
         if (this.checked) {
            infoBox?.classList.remove('d-none');
         } else {
            infoBox?.classList.add('d-none');
         }
      });
   }
});

function filterHolidays() {
   // Implement filtering logic
   console.log('Filtering holidays...');
}

function loadApplicability(holidayID) {
   const modal = new bootstrap.Modal(document.getElementById('applicabilityModal'));
   const content = document.getElementById('applicabilityContent');

   content.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div></div>';
   modal.show();

   // Fetch applicability data
   fetch(`<?= $base ?>php/scripts/leave/holidays/get_holiday_applicability.php?holidayID=${holidayID}`)
      .then(response => response.json())
      .then(data => {
         if (data.success) {
            displayApplicability(data);
         } else {
            content.innerHTML = '<div class="alert alert-danger">Error loading data</div>';
         }
      })
      .catch(error => {
         content.innerHTML = '<div class="alert alert-danger">Error: ' + error.message + '</div>';
      });
}

function displayApplicability(data) {
   const content = document.getElementById('applicabilityContent');
   let html = `
      <div class="mb-3">
         <h6 class="text-primary">Holiday: ${data.holidayName}</h6>
         <p class="text-muted mb-0">Date: ${data.holidayDate}</p>
      </div>

      <div class="alert alert-info">
         <i class="ri-group-line me-2"></i>
         <strong>${data.applicableCount} employees</strong> will observe this holiday
      </div>

      <div class="table-responsive">
         <table class="table table-sm table-bordered">
            <thead>
               <tr>
                  <th>Employee</th>
                  <th>Entity</th>
                  <th>Location</th>
                  <th>Status</th>
               </tr>
            </thead>
            <tbody>
   `;

   if (data.employees && data.employees.length > 0) {
      data.employees.forEach(emp => {
         html += `
            <tr>
               <td>${emp.name}</td>
               <td>${emp.entity}</td>
               <td>${emp.location}</td>
               <td><span class="badge bg-success">Applicable</span></td>
            </tr>
         `;
      });
   } else {
      html += '<tr><td colspan="4" class="text-center">No applicable employees found</td></tr>';
   }

   html += `
            </tbody>
         </table>
      </div>
   `;

   content.innerHTML = html;
}

function deleteHoliday(holidayID) {
   window.location.href = `<?= $base ?>php/scripts/leave/holidays/delete_holiday.php?holidayID=${holidayID}`;
}

function exportApplicableEmployees() {
   if (typeof showToast === 'function') {
       showToast('Export functionality will be implemented', 'info');
   } else {
       alert('Export functionality will be implemented');
   }
}

// Show only recurring holidays
function showRecurringHolidays() {
   document.getElementById('recurringFilter').value = 'recurring';
   filterHolidays();
}

// Filter holidays based on all criteria
function filterHolidays() {
   const countryFilter = document.getElementById('countryFilter').value;
   const typeFilter = document.getElementById('typeFilter').value;
   const jurisdictionFilter = document.getElementById('jurisdictionFilter').value;
   const recurringFilter = document.getElementById('recurringFilter').value;

   const rows = document.querySelectorAll('#holidays_table tbody tr');

   rows.forEach(row => {
      if (row.cells.length === 1) return; // Skip empty row

      let show = true;
      const text = row.textContent.toLowerCase();

      // Recurring filter
      if (recurringFilter === 'recurring') {
         show = show && text.includes('yes');
      } else if (recurringFilter === 'onetime') {
         show = show && text.includes('once');
      }

      row.style.display = show ? '' : 'none';
   });
}
</script>

<!-- Generate Annual Holidays Modal -->
<div class="modal fade" id="generateAnnualModal" tabindex="-1">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header bg-success text-white">
            <h5 class="modal-title">
               <i class="ri-calendar-check-line me-2"></i>
               Generate Annual Recurring Holidays
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
         </div>
         <div class="modal-body">
            <div class="alert alert-primary">
               <i class="ri-information-line me-2"></i>
               <strong>How it works:</strong> This feature automatically creates holiday instances for all recurring holidays (marked as "Repeats Annually") for your selected year.
            </div>

            <div class="row mb-3">
               <div class="col-md-6">
                  <label for="targetYear" class="form-label fw-semibold">Select Year to Generate</label>
                  <select id="targetYear" class="form-select">
                     <?php
                     $currentYear = date('Y');
                     for ($year = $currentYear; $year <= $currentYear + 5; $year++) {
                        echo "<option value='$year'>$year</option>";
                     }
                     ?>
                  </select>
               </div>
               <div class="col-md-6 d-flex align-items-end">
                  <button type="button" class="btn btn-outline-primary w-100" onclick="previewAnnualGeneration()">
                     <i class="ri-eye-line me-1"></i>
                     Preview Holidays
                  </button>
               </div>
            </div>

            <div id="previewContainer" class="d-none">
               <hr>
               <h6 class="mb-3">
                  <i class="ri-file-list-line me-2"></i>
                  Preview: Holidays to Generate
               </h6>
               <div id="previewContent"></div>

               <div class="d-flex justify-content-end gap-2 mt-3">
                  <button type="button" class="btn btn-outline-secondary" onclick="cancelGeneration()">
                     Cancel
                  </button>
                  <button type="button" class="btn btn-success" onclick="confirmGeneration()">
                     <i class="ri-check-line me-1"></i>
                     Generate Holidays
                  </button>
               </div>
            </div>
         </div>
         <div class="modal-footer" id="generateFooter">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
         </div>
      </div>
   </div>
</div>

<script>
// Initialize holiday tabs and populate tables
document.addEventListener('DOMContentLoaded', function() {
    populateHolidayTabs();

    // Add event listeners for tab changes
    const tabs = document.querySelectorAll('#holidayTabs button[data-bs-toggle="tab"]');
    tabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function() {
            const targetId = this.getAttribute('data-bs-target');
            if (targetId === '#global-holidays') {
                filterAndDisplayHolidays('global');
            } else if (targetId === '#jurisdictional-holidays') {
                filterAndDisplayHolidays('jurisdictional');
            } else if (targetId === '#entity-holidays') {
                filterAndDisplayHolidays('entity');
            } else if (targetId === '#adhoc-holidays') {
                filterAndDisplayHolidays('adhoc');
            }
        });
    });

    // Initial load
    filterAndDisplayHolidays('global');
});

// Populate holiday counts in tabs
function populateHolidayTabs() {
    const holidays = <?= json_encode($holidaysList ?? []) ?>;

    let globalCount = 0;
    let jurisdictionalCount = 0;
    let entityCount = 0;
    let adhocCount = 0;

    holidays.forEach(holiday => {
        const jurisdictionLevel = holiday.jurisdictionLevel || 'country';
        const repeatsAnnually = holiday.repeatsAnnually === 'Y';

        if (jurisdictionLevel === 'global') {
            globalCount++;
        } else if (jurisdictionLevel === 'entity') {
            entityCount++;
        } else if (!repeatsAnnually) {
            adhocCount++;
        } else {
            jurisdictionalCount++;
        }
    });

    document.getElementById('global-count').textContent = globalCount;
    document.getElementById('jurisdictional-count').textContent = jurisdictionalCount;
    document.getElementById('entity-count').textContent = entityCount;
    document.getElementById('adhoc-count').textContent = adhocCount;
}

// Filter and display holidays by type
function filterAndDisplayHolidays(type) {
    const holidays = <?= json_encode($holidaysList ?? []) ?>;
    let filtered = [];

    holidays.forEach(holiday => {
        const jurisdictionLevel = holiday.jurisdictionLevel || 'country';
        const repeatsAnnually = holiday.repeatsAnnually === 'Y';

        if (type === 'global' && jurisdictionLevel === 'global') {
            filtered.push(holiday);
        } else if (type === 'jurisdictional' && jurisdictionLevel !== 'global' && jurisdictionLevel !== 'entity' && repeatsAnnually) {
            filtered.push(holiday);
        } else if (type === 'entity' && jurisdictionLevel === 'entity') {
            filtered.push(holiday);
        } else if (type === 'adhoc' && !repeatsAnnually) {
            filtered.push(holiday);
        }
    });

    // Display in appropriate table
    const tableId = type + 'HolidaysTable';
    const container = document.getElementById(tableId);
    if (container) {
        if (filtered.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="ri-calendar-line display-1 text-muted"></i>
                    <p class="mt-3 mb-0">No ${type} holidays configured</p>
                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#manageHolidays" onclick="setHolidayType('${type}')">
                        <i class="ri-add-line me-2"></i>Add ${type.charAt(0).toUpperCase() + type.slice(1)} Holiday
                    </button>
                </div>
            `;
        } else {
            // Render table (simplified - you can enhance this)
            let html = '<div class="table-responsive"><table class="table table-hover"><thead><tr>';
            html += '<th>Holiday Name</th><th>Date</th><th>Type</th><th>Jurisdiction</th><th>Recurring</th><th>Actions</th>';
            html += '</tr></thead><tbody>';

            filtered.forEach(holiday => {
                const holidayDate = new Date(holiday.holidayDate).toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
                html += `<tr>
                    <td><strong>${holiday.holidayName}</strong></td>
                    <td>${holidayDate}</td>
                    <td><span class="badge bg-${holiday.holidayType === 'full_day' ? 'primary' : 'info'}">${holiday.holidayType.replace('_', ' ')}</span></td>
                    <td>${holiday.jurisdictionLevel || 'country'}</td>
                    <td>${holiday.repeatsAnnually === 'Y' ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary editHolidayBtn" data-bs-toggle="modal" data-bs-target="#manageHolidays" data-holiday-id="${holiday.holidayID}">
                            <i class="ri-edit-line"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-holiday" data-holiday-id="${holiday.holidayID}">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </td>
                </tr>`;
            });

            html += '</tbody></table></div>';
            container.innerHTML = html;
        }
    }
}

// Set holiday type when creating new holiday
function setHolidayType(type) {
    // Store the type in a hidden field or data attribute for the modal
    const modal = document.getElementById('manageHolidays');
    if (modal) {
        modal.setAttribute('data-holiday-type', type);
    }
}

// Preview annual holiday generation
function previewAnnualGeneration() {
   const year = document.getElementById('targetYear').value;
   const previewContainer = document.getElementById('previewContainer');
   const previewContent = document.getElementById('previewContent');

   previewContent.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"></div></div>';
   previewContainer.classList.remove('d-none');
   document.getElementById('generateFooter').classList.add('d-none');

   fetch(`<?= $base ?>php/scripts/leave/holidays/generate_annual_holidays.php?action=preview&year=${year}`)
      .then(response => response.json())
      .then(data => {
         if (data.success) {
            displayPreview(data);
         } else {
            previewContent.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
         }
      })
      .catch(error => {
         previewContent.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
      });
}

function displayPreview(data) {
   const previewContent = document.getElementById('previewContent');

   if (data.holidays.length === 0) {
      previewContent.innerHTML = `
         <div class="alert alert-warning">
            <i class="ri-alert-line me-2"></i>
            No recurring holidays found to generate for year ${data.year}
         </div>
      `;
      return;
   }

   let html = `
      <div class="alert alert-success">
         <i class="ri-check-line me-2"></i>
         Found <strong>${data.count} recurring holidays</strong> to generate for year <strong>${data.year}</strong>
      </div>

      <div class="table-responsive">
         <table class="table table-sm table-bordered">
            <thead class="table-light">
               <tr>
                  <th>Holiday Name</th>
                  <th>Original Date</th>
                  <th>New Date (${data.year})</th>
                  <th>Jurisdiction</th>
                  <th>Status</th>
               </tr>
            </thead>
            <tbody>
   `;

   data.holidays.forEach(holiday => {
      const statusBadge = holiday.exists
         ? '<span class="badge bg-warning"><i class="ri-alert-line me-1"></i>Already Exists</span>'
         : '<span class="badge bg-success"><i class="ri-check-line me-1"></i>Will Create</span>';

      html += `
         <tr class="${holiday.exists ? 'table-warning' : ''}">
            <td>${holiday.name}</td>
            <td>${new Date(holiday.originalDate).toLocaleDateString()}</td>
            <td><strong>${new Date(holiday.newDate).toLocaleDateString()}</strong></td>
            <td><span class="badge bg-primary">${holiday.jurisdiction}</span></td>
            <td>${statusBadge}</td>
         </tr>
      `;
   });

   html += `
            </tbody>
         </table>
      </div>
   `;

   const willCreate = data.holidays.filter(h => !h.exists).length;
   if (willCreate > 0) {
      html += `
         <div class="alert alert-info mt-3">
            <i class="ri-information-line me-2"></i>
            <strong>${willCreate} new holiday(s)</strong> will be created. Existing holidays will be skipped.
         </div>
      `;
   }

   previewContent.innerHTML = html;
}

function confirmGeneration() {
   const year = document.getElementById('targetYear').value;

   if (!confirm(`Generate annual holidays for year ${year}? This will create new holiday entries based on your recurring holidays.`)) {
      return;
   }

   const previewContent = document.getElementById('previewContent');
   previewContent.innerHTML = '<div class="text-center"><div class="spinner-border text-success" role="status"></div><p class="mt-2">Generating holidays...</p></div>';

   fetch(`<?= $base ?>php/scripts/leave/holidays/generate_annual_holidays.php`, {
      method: 'POST',
      headers: {
         'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: `action=generate&year=${year}`
   })
      .then(response => response.json())
      .then(data => {
         if (data.success) {
            previewContent.innerHTML = `
               <div class="alert alert-success">
                  <i class="ri-check-double-line me-2"></i>
                  <strong>Success!</strong> ${data.message}
                  <ul class="mb-0 mt-2">
                     <li>Created: ${data.created} holidays</li>
                     <li>Skipped: ${data.skipped} (already exist)</li>
                  </ul>
               </div>
            `;

            // Reload page after 2 seconds
            setTimeout(() => {
               location.reload();
            }, 2000);
         } else {
            previewContent.innerHTML = `
               <div class="alert alert-danger">
                  <i class="ri-error-warning-line me-2"></i>
                  Error: ${data.message}
               </div>
            `;
         }
      })
      .catch(error => {
         previewContent.innerHTML = `
            <div class="alert alert-danger">
               <i class="ri-error-warning-line me-2"></i>
               Error: ${error.message}
            </div>
         `;
      });
}

function cancelGeneration() {
   document.getElementById('previewContainer').classList.add('d-none');
   document.getElementById('generateFooter').classList.remove('d-none');
}
</script>
