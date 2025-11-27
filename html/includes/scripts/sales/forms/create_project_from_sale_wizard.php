<?php
/**
 * Project Creation Wizard from Sales Case
 *
 * This wizard allows users to create a project from a sales case with
 * pre-filled information from the sales case.
 *
 * Required Variables:
 * - $saleDetails: Sales case details object
 * - $billingRates: Array of billing rates
 * - $businessUnits: Array of business units
 * - $allEmployees: Array of all employees
 * - $employeeCategorised: Categorized employees by job title
 * - $userDetails: Current user details object
 * - $config: Application configuration array
 * - $base: Base URL path
 * - $s, $ss, $p: URL parameters for navigation
 */

// Validate required variables
if (!isset($saleDetails) || !$saleDetails) {
   Alert::error("Sales case not found", true, array('fst-italic', 'text-center', 'font-18'));
   return;
}

// Ensure required data is available
if (!isset($billingRates)) {
   $billingRates = Projects::project_billing_rates(array('Suspended'=>'N'), false, $DBConn);
}
if (!isset($businessUnits)) {
   $businessUnits = Data::business_units(array('orgDataID'=>$saleDetails->orgDataID, 'entityID'=>$saleDetails->entityID), false, $DBConn);
}
if (!isset($allEmployees)) {
   $allEmployees = Employee::employees([], false, $DBConn);
}
if (!isset($employeeCategorised)) {
   $employeeCategorised = Employee::categorise_employee($allEmployees, 'jobTitle');
}
?>

<!-- ============================================================================
     PROJECT FROM SALES CASE WIZARD
     ============================================================================ -->
<div class="container-fluid py-4">
   <div class="row justify-content-center">
      <div class="col-12 col-xl-10">
         <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
               <div class="d-flex align-items-center justify-content-between">
                  <div class="flex-grow-1">
                     <div class="d-flex align-items-center gap-2">
                        <h4 class="mb-0 fw-semibold">
                           <i class="ri-folder-add-line me-2"></i>
                           Create Project from Sales Case
                        </h4>
                        <button type="button" class="btn btn-sm btn-link text-white p-0" data-bs-toggle="modal" data-bs-target="#wizardDocumentationModal" title="View wizard documentation">
                           <i class="ri-information-line fs-18"></i>
                        </button>
                     </div>
                     <p class="mb-0 mt-1 opacity-75 small">
                        Sales Case: <strong><?= htmlspecialchars($saleDetails->salesCaseName) ?></strong> |
                        Client: <strong><?= htmlspecialchars($saleDetails->clientName) ?></strong>
                     </p>
                  </div>
                  <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p={$p}" ?>" class="btn btn-light btn-sm">
                     <i class="ri-close-line"></i>
                  </a>
               </div>
            </div>

            <div class="card-body p-4">
               <!-- Wizard Progress Steps -->
               <div class="wizard-steps mb-4">
                  <div class="row">
                     <div class="col-12">
                        <div class="d-flex justify-content-between position-relative">
                           <!-- Progress Line -->
                           <div class="wizard-progress-line"></div>

                           <!-- Step 1: Project Details -->
                           <div class="wizard-step active" data-step="1">
                              <div class="step-circle">
                                 <i class="ri-information-line"></i>
                                 <span class="step-number">1</span>
                              </div>
                              <div class="step-label">Project Details</div>
                           </div>

                           <!-- Step 2: Dates & Owner -->
                           <div class="wizard-step" data-step="2">
                              <div class="step-circle">
                                 <i class="ri-calendar-line"></i>
                                 <span class="step-number">2</span>
                              </div>
                              <div class="step-label">Dates & Owner</div>
                           </div>

                           <!-- Step 3: Billing & Settings -->
                           <div class="wizard-step" data-step="3">
                              <div class="step-circle">
                                 <i class="ri-money-dollar-circle-line"></i>
                                 <span class="step-number">3</span>
                              </div>
                              <div class="step-label">Billing & Settings</div>
                           </div>

                           <!-- Step 4: Review -->
                           <div class="wizard-step" data-step="4">
                              <div class="step-circle">
                                 <i class="ri-checkbox-circle-line"></i>
                                 <span class="step-number">4</span>
                              </div>
                              <div class="step-label">Review</div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>

               <!-- Wizard Form -->
               <form action="<?= "{$base}php/scripts/projects/manage_project_case.php" ?>" method="POST" id="saleProjectWizardForm" class="sale-project-wizard-form">
                  <!-- Hidden Fields -->
                  <input type="hidden" name="orgDataID" value="<?= $saleDetails->orgDataID ?>">
                  <input type="hidden" name="entityID" value="<?= $saleDetails->entityID ?>">
                  <input type="hidden" name="clientID" value="<?= $saleDetails->clientID ?>">
                  <input type="hidden" name="salesCaseID" value="<?= $saleDetails->salesCaseID ?>">
                  <input type="hidden" name="projectTypeID" value="1">
                  <input type="hidden" name="action" value="createFromSale">

                  <!-- Step 1: Project Details -->
                  <div class="wizard-step-content" data-step="1">
                     <div class="row g-4">
                        <div class="col-12">
                           <div class="alert alert-info d-flex align-items-start">
                              <i class="ri-information-line fs-20 me-3 mt-1"></i>
                              <div class="flex-grow-1">
                                 <strong>Step 1: Project Details</strong>
                                 <p class="mb-2 small">The following information has been pre-filled from the sales case. You can modify the project name if needed.</p>
                                 <div class="small">
                                    <strong>Quick Guide:</strong>
                                    <ul class="mb-0 ps-3">
                                       <li>Project name is required and can be customized</li>
                                       <li>Project code will be auto-generated when you create the project</li>
                                       <li>Select the appropriate business unit for this project</li>
                                       <li>Project value is pre-filled from the sales case estimate</li>
                                    </ul>
                                 </div>
                              </div>
                           </div>
                        </div>

                        <div class="col-md-6">
                           <label class="form-label fw-semibold">
                              <i class="ri-building-line me-1 text-primary"></i>Client Name
                           </label>
                           <input type="text" class="form-control" value="<?= htmlspecialchars($saleDetails->clientName) ?>" readonly>
                           <small class="text-muted">Pre-filled from sales case</small>
                        </div>

                        <div class="col-md-6">
                           <label class="form-label fw-semibold">
                              <i class="ri-briefcase-line me-1 text-primary"></i>Sales Case Name
                           </label>
                           <input type="text" class="form-control" value="<?= htmlspecialchars($saleDetails->salesCaseName) ?>" readonly>
                           <small class="text-muted">Pre-filled from sales case</small>
                        </div>

                        <div class="col-md-4">
                           <label for="projectCode" class="form-label fw-semibold">
                              <i class="ri-code-line me-1 text-primary"></i>Project Code
                           </label>
                           <input type="text" id="projectCode" name="projectCode" class="form-control" placeholder="Auto-generated" readonly>
                           <small class="text-muted">Will be auto-generated upon creation</small>
                        </div>

                        <div class="col-md-8">
                           <label for="projectName" class="form-label fw-semibold">
                              <i class="ri-folder-line me-1 text-primary"></i>Project Name <span class="text-danger">*</span>
                           </label>
                           <input type="text" id="projectName" name="projectName" class="form-control" value="<?= htmlspecialchars($saleDetails->salesCaseName) ?>" placeholder="Enter project name" required>
                           <small class="text-muted">You can modify this name. Default is the sales case name.</small>
                        </div>

                        <div class="col-md-6">
                           <label for="businessUnitID" class="form-label fw-semibold">
                              <i class="ri-organization-chart me-1 text-primary"></i>Business Unit
                           </label>
                           <select name="businessUnitID" id="businessUnitID" class="form-select">
                              <?php echo Form::populate_select_element_from_object($businessUnits, 'businessUnitID', 'businessUnitName', '', '', 'Select Business Unit'); ?>
                           </select>
                        </div>

                        <div class="col-md-6">
                           <label for="projectValue" class="form-label fw-semibold">
                              <i class="ri-money-dollar-circle-line me-1 text-primary"></i>Project Value (KES)
                           </label>
                           <input type="text" name="projectValue" id="projectValue" class="form-control" placeholder="0.00" value="<?= $saleDetails->salesCaseEstimate ?? '' ?>">
                           <small class="text-muted">Pre-filled from sales case estimate</small>
                        </div>
                     </div>
                  </div>

                  <!-- Step 2: Dates & Owner -->
                  <div class="wizard-step-content d-none" data-step="2">
                     <div class="row g-4">
                        <div class="col-12">
                           <div class="alert alert-info d-flex align-items-start mb-3">
                              <i class="ri-information-line fs-20 me-3 mt-1"></i>
                              <div class="flex-grow-1">
                                 <strong>Step 2: Dates & Owner</strong>
                                 <p class="mb-2 small">Assign a project owner and set the project timeline. All fields marked with <span class="text-danger">*</span> are required.</p>
                                 <div class="small">
                                    <strong>Quick Guide:</strong>
                                    <ul class="mb-0 ps-3">
                                       <li>Select the project owner who will be responsible for managing this project</li>
                                       <li>Start date defaults to today but can be changed</li>
                                       <li>End date must be after the start date</li>
                                       <li>You can use the Previous button to go back and edit earlier steps</li>
                                    </ul>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="col-12">
                           <h6 class="fw-semibold mb-3">
                              <i class="ri-user-line me-2 text-primary"></i>Project Owner
                           </h6>
                        </div>

                        <div class="col-md-12">
                           <label for="projectOwnerID" class="form-label fw-semibold">
                              Project Owner <span class="text-danger">*</span>
                           </label>
                           <select name="projectOwnerID" id="projectOwnerID" class="form-select" required>
                              <?php echo Form::populate_select_element_from_grouped_object($employeeCategorised, 'ID', 'employeeNameWithInitials', $userDetails->ID, '', 'Select Project Owner') ?>
                           </select>
                           <small class="text-muted">Default: Current user</small>
                        </div>

                        <div class="col-12 mt-4">
                           <h6 class="fw-semibold mb-3">
                              <i class="ri-calendar-line me-2 text-primary"></i>Project Timeline
                           </h6>
                        </div>

                        <div class="col-md-6">
                           <label for="projectStart" class="form-label fw-semibold">
                              Project Start Date <span class="text-danger">*</span>
                           </label>
                           <input type="text" name="projectStart" id="projectStart" class="form-control date" value="<?= date('Y-m-d') ?>" required>
                           <small class="text-muted">Default: Today's date</small>
                        </div>

                        <div class="col-md-6">
                           <label for="projectClose" class="form-label fw-semibold">
                              Project End Date <span class="text-danger">*</span>
                           </label>
                           <input type="text" name="projectClose" id="projectClose" class="form-control date" required>
                           <small class="text-muted">Select expected project completion date</small>
                           <div class="invalid-feedback" id="endDateError"></div>
                        </div>
                     </div>
                  </div>

                  <!-- Step 3: Billing & Settings -->
                  <div class="wizard-step-content d-none" data-step="3">
                     <div class="row g-4">
                        <div class="col-12">
                           <div class="alert alert-info d-flex align-items-start mb-3">
                              <i class="ri-information-line fs-20 me-3 mt-1"></i>
                              <div class="flex-grow-1">
                                 <strong>Step 3: Billing & Settings</strong>
                                 <p class="mb-2 small">Configure billing rates and time rounding settings for this project. These settings are optional but recommended.</p>
                                 <div class="small">
                                    <strong>Quick Guide:</strong>
                                    <ul class="mb-0 ps-3">
                                       <li>Select a billing rate if you want to track billable hours</li>
                                       <li>Time rounding helps standardize time entries for billing</li>
                                       <li>If you select a rounding option, you can also set the rounding interval</li>
                                       <li>These settings can be changed later in project settings</li>
                                    </ul>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="col-12">
                           <h6 class="fw-semibold mb-3">
                              <i class="ri-money-dollar-circle-line me-2 text-primary"></i>Billing Configuration
                           </h6>
                        </div>

                        <div class="col-md-6">
                           <label for="billingRateID" class="form-label fw-semibold">
                              Billing Rate
                           </label>
                           <select name="billingRateID" id="billingRateID" class="form-select">
                              <?php echo Form::populate_select_element_from_object($billingRates, 'billingRateID', 'billingRate', '', '', 'Select Billing Rate') ?>
                           </select>
                        </div>

                        <div class="col-md-6">
                           <label class="form-label fw-semibold">
                              <i class="ri-time-line me-1 text-primary"></i>Time Rounding
                           </label>
                           <div class="alert alert-light border">
                              <small class="text-muted d-block mb-2">Automatically round log's duration for billing purposes</small>
                              <div class="row g-2">
                                 <div class="col-6">
                                    <select id="roundingOff" name="roundingoff" class="form-select form-select-sm">
                                       <?php echo Form::populate_select_element_from_object($config['roundingOptions'], "key", "value", "", "", "Select rounding"); ?>
                                    </select>
                                 </div>
                                 <div class="col-6 roundingInterval d-none">
                                    <select id="roundingInterval" name="roundingInterval" class="form-select form-select-sm">
                                       <?php echo Form::populate_select_element_from_object($config['roundingOffParams'], "key", "value", "", "", "Select interval") ?>
                                    </select>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>

                  <!-- Step 4: Review -->
                  <div class="wizard-step-content d-none" data-step="4">
                     <div class="row g-4">
                        <div class="col-12">
                           <div class="alert alert-success d-flex align-items-start">
                              <i class="ri-checkbox-circle-line fs-20 me-3 mt-1"></i>
                              <div class="flex-grow-1">
                                 <strong>Step 4: Review & Create</strong>
                                 <p class="mb-2 small">Please review all the information below before creating the project. Use the Previous button to go back and make changes if needed.</p>
                                 <div class="small">
                                    <strong>Before Creating:</strong>
                                    <ul class="mb-0 ps-3">
                                       <li>Verify all project details are correct</li>
                                       <li>Check that dates and owner assignments are accurate</li>
                                       <li>Ensure billing settings match your requirements</li>
                                       <li>Once created, you can edit project details from the project management page</li>
                                    </ul>
                                 </div>
                              </div>
                           </div>
                        </div>

                        <div class="col-md-6">
                           <div class="card border">
                              <div class="card-header bg-light">
                                 <h6 class="mb-0 fw-semibold">Project Information</h6>
                              </div>
                              <div class="card-body">
                                 <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                       <td class="text-muted" style="width: 40%;">Project Name:</td>
                                       <td class="fw-semibold" id="reviewProjectName">-</td>
                                    </tr>
                                    <tr>
                                       <td class="text-muted">Client:</td>
                                       <td class="fw-semibold"><?= htmlspecialchars($saleDetails->clientName) ?></td>
                                    </tr>
                                    <tr>
                                       <td class="text-muted">Sales Case:</td>
                                       <td class="fw-semibold"><?= htmlspecialchars($saleDetails->salesCaseName) ?></td>
                                    </tr>
                                    <tr>
                                       <td class="text-muted">Business Unit:</td>
                                       <td class="fw-semibold" id="reviewBusinessUnit">-</td>
                                    </tr>
                                    <tr>
                                       <td class="text-muted">Project Value:</td>
                                       <td class="fw-semibold" id="reviewProjectValue">-</td>
                                    </tr>
                                 </table>
                              </div>
                           </div>
                        </div>

                        <div class="col-md-6">
                           <div class="card border">
                              <div class="card-header bg-light">
                                 <h6 class="mb-0 fw-semibold">Project Details</h6>
                              </div>
                              <div class="card-body">
                                 <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                       <td class="text-muted" style="width: 40%;">Project Owner:</td>
                                       <td class="fw-semibold" id="reviewProjectOwner">-</td>
                                    </tr>
                                    <tr>
                                       <td class="text-muted">Start Date:</td>
                                       <td class="fw-semibold" id="reviewStartDate">-</td>
                                    </tr>
                                    <tr>
                                       <td class="text-muted">End Date:</td>
                                       <td class="fw-semibold" id="reviewEndDate">-</td>
                                    </tr>
                                    <tr>
                                       <td class="text-muted">Billing Rate:</td>
                                       <td class="fw-semibold" id="reviewBillingRate">-</td>
                                    </tr>
                                    <tr>
                                       <td class="text-muted">Time Rounding:</td>
                                       <td class="fw-semibold" id="reviewRounding">-</td>
                                    </tr>
                                 </table>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </form>

               <!-- Wizard Navigation -->
               <div class="wizard-footer mt-4 pt-3 border-top">
                  <div class="d-flex justify-content-between align-items-center">
                     <div style="min-width: 120px;">
                        <button type="button" class="btn btn-secondary" id="wizardPrevBtn" style="display: none;">
                           <i class="ri-arrow-left-line me-1"></i>Previous
                        </button>
                     </div>

                     <div class="flex-fill text-center">
                        <small class="text-muted">Step <span id="currentStepNumber">1</span> of 4</small>
                     </div>

                     <div style="min-width: 120px; text-align: right;">
                        <button type="button" class="btn btn-primary" id="wizardNextBtn">
                           Next <i class="ri-arrow-right-line ms-1"></i>
                        </button>

                        <button type="submit" form="saleProjectWizardForm" class="btn btn-success" id="wizardSubmitBtn" style="display: none;">
                           <i class="ri-check-line me-1"></i>Create Project
                        </button>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<!-- Wizard Documentation Modal -->
<div class="modal fade" id="wizardDocumentationModal" tabindex="-1" aria-labelledby="wizardDocumentationModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
         <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="wizardDocumentationModalLabel">
               <i class="ri-book-open-line me-2"></i>Project Creation Wizard Guide
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body">
            <div class="mb-4">
               <h6 class="text-primary mb-3">
                  <i class="ri-information-line me-2"></i>Overview
               </h6>
               <p>This wizard helps you create a new project from an existing sales case. The wizard guides you through four simple steps to set up your project with all necessary information.</p>
               <p class="mb-0">All information from the sales case is pre-filled, making it quick and easy to convert a won sale into an active project.</p>
            </div>

            <div class="mb-4">
               <h6 class="text-primary mb-3">
                  <i class="ri-list-check me-2"></i>Step-by-Step Guide
               </h6>

               <div class="card border mb-3">
                  <div class="card-header bg-light">
                     <h6 class="mb-0">
                        <span class="badge bg-primary me-2">Step 1</span>Project Details
                     </h6>
                  </div>
                  <div class="card-body">
                     <p><strong>What to do:</strong></p>
                     <ul>
                        <li><strong>Project Name:</strong> Review and modify if needed. This name will be used throughout the system.</li>
                        <li><strong>Project Code:</strong> Automatically generated - no action needed.</li>
                        <li><strong>Business Unit:</strong> Select the business unit responsible for this project (optional).</li>
                        <li><strong>Project Value:</strong> Pre-filled from sales case estimate. You can adjust if needed.</li>
                     </ul>
                     <p class="mb-0 small text-muted"><i class="ri-lightbulb-line me-1"></i><strong>Tip:</strong> Use a descriptive project name that clearly identifies the work being performed.</p>
                  </div>
               </div>

               <div class="card border mb-3">
                  <div class="card-header bg-light">
                     <h6 class="mb-0">
                        <span class="badge bg-primary me-2">Step 2</span>Dates & Owner
                     </h6>
                  </div>
                  <div class="card-body">
                     <p><strong>What to do:</strong></p>
                     <ul>
                        <li><strong>Project Owner:</strong> Select the person who will manage this project. Defaults to you.</li>
                        <li><strong>Start Date:</strong> When the project will begin. Defaults to today.</li>
                        <li><strong>End Date:</strong> Expected project completion date. Must be after the start date.</li>
                     </ul>
                     <p class="mb-0 small text-muted"><i class="ri-lightbulb-line me-1"></i><strong>Tip:</strong> Set realistic dates. You can always extend the project timeline later if needed.</p>
                  </div>
               </div>

               <div class="card border mb-3">
                  <div class="card-header bg-light">
                     <h6 class="mb-0">
                        <span class="badge bg-primary me-2">Step 3</span>Billing & Settings
                     </h6>
                  </div>
                  <div class="card-body">
                     <p><strong>What to do:</strong></p>
                     <ul>
                        <li><strong>Billing Rate:</strong> Select a billing rate if you need to track billable hours (optional).</li>
                        <li><strong>Time Rounding:</strong> Choose how to round time entries for billing purposes (optional).</li>
                        <li><strong>Rounding Interval:</strong> If rounding is enabled, set the interval (e.g., 15 minutes, 30 minutes).</li>
                     </ul>
                     <p class="mb-0 small text-muted"><i class="ri-lightbulb-line me-1"></i><strong>Tip:</strong> These settings help ensure consistent billing. You can skip this step if billing isn't required.</p>
                  </div>
               </div>

               <div class="card border mb-3">
                  <div class="card-header bg-light">
                     <h6 class="mb-0">
                        <span class="badge bg-primary me-2">Step 4</span>Review & Create
                     </h6>
                  </div>
                  <div class="card-body">
                     <p><strong>What to do:</strong></p>
                     <ul>
                        <li>Review all project information displayed in the summary cards.</li>
                        <li>Use the <strong>Previous</strong> button to go back and make changes if needed.</li>
                        <li>Click <strong>Create Project</strong> when everything looks correct.</li>
                     </ul>
                     <p class="mb-0 small text-muted"><i class="ri-lightbulb-line me-1"></i><strong>Tip:</strong> Don't worry - you can edit project details after creation from the project management page.</p>
                  </div>
               </div>
            </div>

            <div class="mb-4">
               <h6 class="text-primary mb-3">
                  <i class="ri-question-line me-2"></i>Navigation Tips
               </h6>
               <div class="row g-3">
                  <div class="col-md-6">
                     <div class="d-flex align-items-start">
                        <i class="ri-arrow-right-line text-success me-2 mt-1 fs-18"></i>
                        <div>
                           <strong>Next Button</strong>
                           <p class="small mb-0">Click to proceed to the next step. The form validates required fields before advancing.</p>
                        </div>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="d-flex align-items-start">
                        <i class="ri-arrow-left-line text-info me-2 mt-1 fs-18"></i>
                        <div>
                           <strong>Previous Button</strong>
                           <p class="small mb-0">Available from step 2 onwards. Go back to edit information without losing your data.</p>
                        </div>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="d-flex align-items-start">
                        <i class="ri-check-line text-success me-2 mt-1 fs-18"></i>
                        <div>
                           <strong>Create Project</strong>
                           <p class="small mb-0">Only appears on the final step. Click to create the project with all entered information.</p>
                        </div>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="d-flex align-items-start">
                        <i class="ri-close-line text-danger me-2 mt-1 fs-18"></i>
                        <div>
                           <strong>Cancel</strong>
                           <p class="small mb-0">Click the X button in the header to exit the wizard without creating a project.</p>
                        </div>
                     </div>
                  </div>
               </div>
            </div>

            <div class="mb-4">
               <h6 class="text-primary mb-3">
                  <i class="ri-error-warning-line me-2"></i>Common Questions
               </h6>
               <div class="accordion" id="wizardFAQ">
                  <div class="accordion-item">
                     <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                           Can I change project details after creation?
                        </button>
                     </h2>
                     <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#wizardFAQ">
                        <div class="accordion-body">
                           Yes! All project details can be edited after creation from the project management page. Only the project code cannot be changed once created.
                        </div>
                     </div>
                  </div>
                  <div class="accordion-item">
                     <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                           What if I make a mistake?
                        </button>
                     </h2>
                     <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#wizardFAQ">
                        <div class="accordion-body">
                           Use the <strong>Previous</strong> button to go back to any step and make changes. Your entered data is preserved as you navigate between steps.
                        </div>
                     </div>
                  </div>
                  <div class="accordion-item">
                     <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                           Are all fields required?
                        </button>
                     </h2>
                     <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#wizardFAQ">
                        <div class="accordion-body">
                           Only fields marked with a red asterisk (<span class="text-danger">*</span>) are required. Optional fields can be filled in later or skipped entirely.
                        </div>
                     </div>
                  </div>
                  <div class="accordion-item">
                     <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                           What happens to the sales case after creating the project?
                        </button>
                     </h2>
                     <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#wizardFAQ">
                        <div class="accordion-body">
                           The sales case remains unchanged. Creating a project from a sales case links them together but doesn't modify the sales case status. You can still manage the sales case independently.
                        </div>
                     </div>
                  </div>
               </div>
            </div>

            <div class="alert alert-light border">
               <h6 class="mb-2">
                  <i class="ri-customer-service-line me-2 text-primary"></i>Need Help?
               </h6>
               <p class="mb-0 small">If you encounter any issues or have questions about creating projects, please contact your system administrator or refer to the project management documentation.</p>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
         </div>
      </div>
   </div>
</div>

<!-- Wizard Styles -->
<style>
.wizard-steps {
   position: relative;
   padding: 1rem 0;
}

.wizard-progress-line {
   position: absolute;
   top: 25px;
   left: 12.5%;
   right: 12.5%;
   height: 3px;
   background: #e9ecef;
   z-index: 0;
   --progress: 0%;
}

.wizard-progress-line::before {
   content: '';
   position: absolute;
   top: 0;
   left: 0;
   height: 100%;
   background: linear-gradient(90deg, #0d6efd, #198754);
   width: var(--progress, 0%);
   transition: width 0.5s ease;
   border-radius: 3px;
}

.wizard-step {
   flex: 1;
   text-align: center;
   position: relative;
   z-index: 1;
}

.step-circle {
   width: 50px;
   height: 50px;
   border-radius: 50%;
   background: #fff;
   border: 3px solid #e9ecef;
   display: flex;
   align-items: center;
   justify-content: center;
   margin: 0 auto 10px;
   position: relative;
   transition: all 0.3s ease;
}

.step-circle i {
   font-size: 22px;
   color: #6c757d;
   display: block;
}

.step-circle .step-number {
   font-size: 16px;
   font-weight: 600;
   color: #6c757d;
   display: none;
}

.wizard-step.active .step-circle {
   border-color: #0d6efd;
   background: #0d6efd;
   box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.2);
}

.wizard-step.active .step-circle i {
   color: white;
}

.wizard-step.completed .step-circle {
   border-color: #198754;
   background: #198754;
}

.wizard-step.completed .step-circle i {
   display: none;
}

.wizard-step.completed .step-circle .step-number {
   display: block;
   color: white;
}

.step-label {
   font-size: 13px;
   color: #6c757d;
   font-weight: 500;
}

.wizard-step.active .step-label {
   color: #0d6efd;
   font-weight: 600;
}

.wizard-step.completed .step-label {
   color: #198754;
}

.wizard-step-content {
   min-height: 400px;
}
</style>

<!-- Wizard JavaScript - Using Event Delegation -->
<script>
(function() {
   'use strict';

   // Get wizard form first - this is our base selector
   const wizardForm = document.getElementById('saleProjectWizardForm');
   if (!wizardForm) {
      console.warn('Wizard form not found');
      return;
   }

   // Get wizard container - parent card element
   const wizardContainer = wizardForm.closest('.card');
   if (!wizardContainer) {
      console.warn('Wizard container not found');
      return;
   }

   // Wizard state management
   const wizardState = {
      currentStep: 1,
      totalSteps: 4,
      initialized: false
   };

   // Get all form elements using wizardForm as base selector
   const getFormElement = function(id) {
      return wizardForm.querySelector('#' + id) || wizardContainer.querySelector('#' + id);
   };

   // Initialize wizard
   function initializeWizard() {
      if (wizardState.initialized) return;

      // Initialize Tom Select for project owner (only once)
      const projectOwnerSelect = getFormElement('projectOwnerID');
      if (projectOwnerSelect && typeof TomSelect !== 'undefined' && !projectOwnerSelect.tomselect) {
         try {
            projectOwnerSelect.tomselect = new TomSelect(projectOwnerSelect, {
               create: false,
               sortField: { field: "text", direction: "asc" }
            });
         } catch (e) {
            console.warn('TomSelect initialization failed:', e);
         }
      }

      // Initialize date pickers if they exist
      initializeDatePickers();

      // Show initial step
      showStep(wizardState.currentStep);
      wizardState.initialized = true;
   }

   // Initialize date pickers for step 2
   function initializeDatePickers() {
      const startDateInput = getFormElement('projectStart');
      const endDateInput = getFormElement('projectClose');

      if (startDateInput && endDateInput && typeof flatpickr !== 'undefined') {
         // Re-initialize if date pickers are shown
         if (startDateInput.offsetParent !== null) {
            if (!startDateInput._flatpickr) {
               flatpickr(startDateInput, {
                  dateFormat: 'Y-m-d',
                  altInput: true,
                  altFormat: 'F j, Y',
                  allowInput: true,
                  onChange: function() {
                     validateDates();
                  }
               });
            }
            if (!endDateInput._flatpickr) {
               flatpickr(endDateInput, {
                  dateFormat: 'Y-m-d',
                  altInput: true,
                  altFormat: 'F j, Y',
                  allowInput: true,
                  onChange: function() {
                     validateDates();
                  }
               });
            }
         }
      }
   }

   // Date validation helper
   function validateDates() {
      const startDateInput = getFormElement('projectStart');
      const endDateInput = getFormElement('projectClose');
      const endDateError = getFormElement('endDateError');

      if (!startDateInput || !endDateInput) return true;

      const startValue = startDateInput.value || startDateInput._flatpickr?.input?.value || '';
      const endValue = endDateInput.value || endDateInput._flatpickr?.input?.value || '';

      if (startValue && endValue) {
         const startDate = new Date(startValue);
         const endDate = new Date(endValue);

         if (endDate < startDate) {
            endDateInput.classList.add('is-invalid');
            if (endDateError) {
               endDateError.textContent = 'End date cannot be before start date.';
            }
            return false;
         } else {
            endDateInput.classList.remove('is-invalid');
            endDateInput.classList.add('is-valid');
            if (endDateError) {
               endDateError.textContent = '';
            }
            return true;
         }
      }
      return true;
   }

   // Update review step
   function updateReviewStep() {
      const projectName = getFormElement('projectName');
      const projectValue = getFormElement('projectValue');
      const businessUnitID = getFormElement('businessUnitID');
      const projectOwnerID = getFormElement('projectOwnerID');
      const projectStart = getFormElement('projectStart');
      const projectClose = getFormElement('projectClose');
      const billingRateID = getFormElement('billingRateID');
      const roundingOff = getFormElement('roundingOff');

      if (projectName) {
         const reviewName = getFormElement('reviewProjectName');
         if (reviewName) reviewName.textContent = projectName.value || '-';
      }

      if (projectValue) {
         const reviewValue = getFormElement('reviewProjectValue');
         if (reviewValue) {
            reviewValue.textContent = projectValue.value ? 'KES ' + projectValue.value : '-';
         }
      }

      if (businessUnitID) {
         const reviewUnit = getFormElement('reviewBusinessUnit');
         if (reviewUnit) {
            const selectedOption = businessUnitID.options[businessUnitID.selectedIndex];
            reviewUnit.textContent = selectedOption ? selectedOption.text : '-';
         }
      }

      if (projectOwnerID) {
         const reviewOwner = getFormElement('reviewProjectOwner');
         if (reviewOwner) {
            const selectedOption = projectOwnerID.options[projectOwnerID.selectedIndex];
            reviewOwner.textContent = selectedOption ? selectedOption.text : '-';
         }
      }

      if (projectStart) {
         const reviewStart = getFormElement('reviewStartDate');
         if (reviewStart) {
            const startValue = projectStart.value || projectStart._flatpickr?.input?.value || '';
            reviewStart.textContent = startValue || '-';
         }
      }

      if (projectClose) {
         const reviewEnd = getFormElement('reviewEndDate');
         if (reviewEnd) {
            const endValue = projectClose.value || projectClose._flatpickr?.input?.value || '';
            reviewEnd.textContent = endValue || '-';
         }
      }

      if (billingRateID) {
         const reviewBilling = getFormElement('reviewBillingRate');
         if (reviewBilling) {
            const selectedOption = billingRateID.options[billingRateID.selectedIndex];
            reviewBilling.textContent = selectedOption ? selectedOption.text : '-';
         }
      }

      if (roundingOff) {
         const reviewRounding = getFormElement('reviewRounding');
         if (reviewRounding) {
            const selectedOption = roundingOff.options[roundingOff.selectedIndex];
            reviewRounding.textContent = selectedOption ? selectedOption.text : '-';
         }
      }
   }

   // Step navigation
   function showStep(step) {
      if (step < 1 || step > wizardState.totalSteps) return;

      wizardState.currentStep = step;

      // Hide all steps - search within form first, then container
      const allSteps = wizardForm.querySelectorAll('.wizard-step-content') || wizardContainer.querySelectorAll('.wizard-step-content');
      allSteps.forEach(content => {
         content.classList.add('d-none');
      });

      // Show current step
      const currentStepContent = wizardForm.querySelector(`.wizard-step-content[data-step="${step}"]`) ||
                                 wizardContainer.querySelector(`.wizard-step-content[data-step="${step}"]`);
      if (currentStepContent) {
         currentStepContent.classList.remove('d-none');

         // Initialize date pickers if showing step 2
         if (step === 2) {
            setTimeout(initializeDatePickers, 100);
         }
      }

      // Update step indicators - search in container
      const wizardSteps = wizardContainer.querySelectorAll('.wizard-step');
      wizardSteps.forEach((stepEl, index) => {
         const stepNum = index + 1;
         stepEl.classList.remove('active', 'completed');

         if (stepNum < step) {
            stepEl.classList.add('completed');
         } else if (stepNum === step) {
            stepEl.classList.add('active');
         }
      });

      // Update progress line
      const progressLine = wizardContainer.querySelector('.wizard-progress-line');
      if (progressLine) {
         const progress = ((step - 1) / (wizardState.totalSteps - 1)) * 100;
         progressLine.style.setProperty('--progress', progress + '%');
      }

      // Update buttons - use getFormElement helper
      const prevBtn = getFormElement('wizardPrevBtn');
      const nextBtn = getFormElement('wizardNextBtn');
      const submitBtn = getFormElement('wizardSubmitBtn');
      const stepNumber = getFormElement('currentStepNumber');

      // Show/hide Previous button - visible from step 2 onwards
      if (prevBtn) {
         if (step > 1) {
            prevBtn.style.display = 'inline-block';
            prevBtn.disabled = false;
            prevBtn.removeAttribute('hidden');
         } else {
            prevBtn.style.display = 'none';
         }
      }

      // Show/hide Next button - hidden on last step
      if (nextBtn) {
         if (step < wizardState.totalSteps) {
            nextBtn.style.display = 'inline-block';
            nextBtn.disabled = false;
         } else {
            nextBtn.style.display = 'none';
         }
      }

      // Show/hide Submit button - only visible on last step
      if (submitBtn) {
         if (step === wizardState.totalSteps) {
            submitBtn.style.display = 'inline-block';
            submitBtn.disabled = false;
         } else {
            submitBtn.style.display = 'none';
         }
      }

      // Update step number display
      if (stepNumber) stepNumber.textContent = step;

      // Update review if on last step
      if (step === wizardState.totalSteps) {
         updateReviewStep();
      }
   }

   // Validate current step
   function validateStep(step) {
      const stepContent = wizardForm.querySelector(`.wizard-step-content[data-step="${step}"]`) ||
                         wizardContainer.querySelector(`.wizard-step-content[data-step="${step}"]`);
      if (!stepContent) {
         return false;
      }

      const requiredFields = stepContent.querySelectorAll('[required]');
      let isValid = true;

      for (let field of requiredFields) {
         let fieldValue = '';

         // Handle different input types
         if (field.type === 'checkbox' || field.type === 'radio') {
            fieldValue = field.checked ? 'checked' : '';
         } else if (field.tagName === 'SELECT') {
            fieldValue = field.value || '';
         } else {
            fieldValue = field.value ? field.value.trim() : '';
         }

         if (!fieldValue) {
            field.focus();
            field.classList.add('is-invalid');
            isValid = false;
         } else {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
         }
      }

      // Special validation for step 2 (dates)
      if (step === 2 && isValid) {
         isValid = validateDates();
      }

      if (!isValid) {
         // Show error message
         const errorMsg = document.createElement('div');
         errorMsg.className = 'alert alert-danger mt-3';
         errorMsg.setAttribute('role', 'alert');
         errorMsg.innerHTML = '<i class="ri-error-warning-line me-2"></i>Please fill in all required fields before proceeding.';

         // Remove existing error messages
         stepContent.querySelectorAll('.alert-danger').forEach(el => el.remove());

         // Insert error message at the beginning of step content
         const firstChild = stepContent.firstElementChild;
         if (firstChild) {
            stepContent.insertBefore(errorMsg, firstChild);
         } else {
            stepContent.appendChild(errorMsg);
         }

         // Scroll to top of step content
         stepContent.scrollIntoView({ behavior: 'smooth', block: 'start' });
      } else {
         // Remove any existing error messages if validation passes
         stepContent.querySelectorAll('.alert-danger').forEach(el => el.remove());
      }

      return isValid;
   }

   // Event delegation handler - handles all wizard interactions
   function handleWizardEvent(e) {
      const target = e.target;

      // Handle Next button click
      if (target.id === 'wizardNextBtn' || target.closest('#wizardNextBtn')) {
         e.preventDefault();
         e.stopPropagation();

         const isValid = validateStep(wizardState.currentStep);
         if (isValid) {
            showStep(wizardState.currentStep + 1);
         }
         return false;
      }

      // Handle Previous button click
      if (target.id === 'wizardPrevBtn' || target.closest('#wizardPrevBtn')) {
         e.preventDefault();
         e.stopPropagation();

         // Go back to previous step if not on first step
         if (wizardState.currentStep > 1) {
            const previousStep = wizardState.currentStep - 1;
            showStep(previousStep);

            // Clear any validation errors from the previous step
            const previousStepContent = wizardForm.querySelector(`.wizard-step-content[data-step="${previousStep}"]`) ||
                                       wizardContainer.querySelector(`.wizard-step-content[data-step="${previousStep}"]`);
            if (previousStepContent) {
               previousStepContent.querySelectorAll('.alert-danger').forEach(el => el.remove());
               previousStepContent.querySelectorAll('.is-invalid').forEach(el => {
                  el.classList.remove('is-invalid');
               });
            }
         }
         return false;
      }

      // Handle rounding off change
      if (target.id === 'roundingOff') {
         const roundingInterval = wizardForm.querySelector('.roundingInterval') || wizardContainer.querySelector('.roundingInterval');
         if (roundingInterval) {
            if (target.value === 'no_rounding' || target.value === '') {
               roundingInterval.classList.add('d-none');
            } else {
               roundingInterval.classList.remove('d-none');
            }
         }
      }

      // Handle date field changes for validation
      if (target.id === 'projectStart' || target.id === 'projectClose') {
         if (wizardState.currentStep === 2) {
            setTimeout(validateDates, 100);
         }
      }

      // Handle form submission
      if (target.id === 'wizardSubmitBtn' || target.closest('#wizardSubmitBtn') ||
          (target.type === 'submit' && target.closest('#saleProjectWizardForm'))) {
         e.preventDefault();
         e.stopPropagation();

         // Only allow submission from the final step
         if (wizardState.currentStep !== wizardState.totalSteps) {
            return false;
         }

         if (!validateStep(wizardState.currentStep)) {
            return false;
         }

         // Show loading state
         const submitBtn = getFormElement('wizardSubmitBtn');
         if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';
         }

         // Submit the form
         wizardForm.submit();
         return false;
      }
   }

   // Initialize when DOM is ready
   function init() {
      if (document.readyState === 'loading') {
         document.addEventListener('DOMContentLoaded', function() {
            initializeWizard();
         });
      } else {
         initializeWizard();
      }

      // Attach delegated event listeners
      wizardContainer.addEventListener('click', handleWizardEvent, true);
      wizardContainer.addEventListener('change', handleWizardEvent, true);

      // Handle form submission
      wizardForm.addEventListener('submit', function(e) {
         e.preventDefault();
         e.stopPropagation();

         if (wizardState.currentStep !== wizardState.totalSteps) {
            return false;
         }

         if (!validateStep(wizardState.currentStep)) {
            return false;
         }

         // Show loading state
         const submitBtn = getFormElement('wizardSubmitBtn');
         if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';
         }

         // Submit the form
         wizardForm.submit();
         return false;
      });
   }

   // Start initialization
   init();
})();
</script>

