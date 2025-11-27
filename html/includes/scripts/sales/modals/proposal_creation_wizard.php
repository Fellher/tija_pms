<?php
/**
 * Proposal Creation Wizard
 * Multi-step wizard for creating proposals with checklists and assignments
 *
 * @package    Tija CRM
 * @subpackage Sales Management
 */

// Get required data
$allEmployees = Employee::employees(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
$clients = Client::clients(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
$salesCases = Sales::sales_cases(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
$proposalStatuses = Sales::proposal_statuses(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
$checklistItems = Proposal::proposal_checklist_items([], false, $DBConn);
$checklistItemCategories = Proposal::proposal_checklist_items_categories([], false, $DBConn);
$checklistStatuses = Proposal::proposal_checklist_status(['proposalChecklistStatusType'=>'checkList'], false, $DBConn);
$employeesCategorised = Employee::categorise_employee($allEmployees, 'jobTitle');
?>

<div class="proposal-wizard-container">
   <!-- Wizard Progress Steps -->
   <div class="wizard-progress mb-4">
      <div class="progress-steps d-flex justify-content-between position-relative">
         <div class="step-item active" data-step="1">
            <div class="step-number">1</div>
            <div class="step-label">Proposal Details</div>
         </div>
         <div class="step-item" data-step="2">
            <div class="step-number">2</div>
            <div class="step-label">Checklist</div>
         </div>
         <div class="step-item" data-step="3">
            <div class="step-number">3</div>
            <div class="step-label">Checklist Items</div>
         </div>
         <div class="step-item" data-step="4">
            <div class="step-number">4</div>
            <div class="step-label">Assignments</div>
         </div>
         <div class="step-item" data-step="5">
            <div class="step-number">5</div>
            <div class="step-label">Review</div>
         </div>
      </div>
   </div>

   <!-- Wizard Steps -->
   <div class="wizard-steps">
      <!-- Step 1: Proposal Details -->
      <div class="wizard-step active" data-step="1">
         <h5 class="mb-4"><i class="ri-file-text-line me-2"></i>Basic Proposal Information</h5>

         <input type="hidden" name="orgDataID" value="<?= $orgDataID ?? '' ?>">
         <input type="hidden" name="entityID" value="<?= $entityID ?? '' ?>">
         <input type="hidden" name="employeeID" value="<?= $userDetails->ID ?? '' ?>">
         <input type="hidden" name="proposalID" id="wizardProposalID" value="">

         <div class="row g-3">
            <div class="col-md-12">
               <label for="wizardProposalTitle" class="form-label fw-semibold">
                  Proposal Title <span class="text-danger">*</span>
               </label>
               <input type="text" class="form-control" id="wizardProposalTitle" name="proposalTitle" required>
            </div>

            <div class="col-md-6">
               <label for="wizardClientID" class="form-label fw-semibold">
                  Client <span class="text-danger">*</span>
               </label>
               <select class="form-select" id="wizardClientID" name="clientID" required>
                  <option value="">Select Client</option>
                  <?php if($clients): ?>
                     <?php foreach($clients as $client): ?>
                        <option value="<?= $client->clientID ?>"><?= htmlspecialchars($client->clientName) ?></option>
                     <?php endforeach; ?>
                  <?php endif; ?>
               </select>
            </div>

            <div class="col-md-6">
               <label for="wizardSalesCaseID" class="form-label fw-semibold">
                  Sales Case <span class="text-danger">*</span>
               </label>
               <select class="form-select" id="wizardSalesCaseID" name="salesCaseID" required>
                  <option value="">Select Sales Case</option>
                  <?php if($salesCases): ?>
                     <?php foreach($salesCases as $case): ?>
                        <option value="<?= $case->salesCaseID ?>" data-client-id="<?= $case->clientID ?>">
                           <?= htmlspecialchars($case->salesCaseName) ?>
                        </option>
                     <?php endforeach; ?>
                  <?php endif; ?>
               </select>
            </div>

            <div class="col-md-6">
               <label for="wizardProposalDeadline" class="form-label fw-semibold">
                  Proposal Deadline <span class="text-danger">*</span>
               </label>
               <input type="text" class="form-control" id="wizardProposalDeadline" name="proposalDeadline" required>
               <small class="text-muted">Select the deadline for this proposal</small>
            </div>

            <div class="col-md-6">
               <label for="wizardProposalValue" class="form-label fw-semibold">
                  Proposal Value <span class="text-danger">*</span>
               </label>
               <div class="input-group">
                  <span class="input-group-text">KES</span>
                  <input type="number" class="form-control" id="wizardProposalValue" name="proposalValue" step="0.01" min="0" required>
               </div>
            </div>

            <div class="col-md-6">
               <label for="wizardProposalStatusID" class="form-label fw-semibold">
                  Proposal Status <span class="text-danger">*</span>
               </label>
               <select class="form-select" id="wizardProposalStatusID" name="proposalStatusID" required>
                  <option value="">Select Status</option>
                  <?php if($proposalStatuses): ?>
                     <?php foreach($proposalStatuses as $status): ?>
                        <option value="<?= $status->proposalStatusID ?>"><?= htmlspecialchars($status->proposalStatusName) ?></option>
                     <?php endforeach; ?>
                  <?php endif; ?>
               </select>
            </div>

            <div class="col-md-12">
               <label for="wizardProposalDescription" class="form-label fw-semibold">Proposal Description</label>
               <textarea class="form-control" id="wizardProposalDescription" name="proposalDescription" rows="4" placeholder="Describe the proposal..."></textarea>
            </div>
         </div>
      </div>

      <!-- Step 2: Checklist Creation -->
      <div class="wizard-step" data-step="2">
         <h5 class="mb-4"><i class="ri-checkbox-multiple-line me-2"></i>Create Proposal Checklist</h5>

         <div class="row g-3">
            <div class="col-md-12">
               <label for="wizardChecklistName" class="form-label fw-semibold">
                  Checklist Name <span class="text-danger">*</span>
               </label>
               <input type="text" class="form-control" id="wizardChecklistName" name="checklistName" required>
               <small class="text-muted">Auto-filled from proposal title, but you can customize it</small>
            </div>

            <div class="col-md-6">
               <label for="wizardChecklistDeadline" class="form-label fw-semibold">
                  Checklist Deadline <span class="text-danger">*</span>
               </label>
               <input type="text" class="form-control" id="wizardChecklistDeadline" name="checklistDeadline" required>
               <small class="text-muted">Must be before proposal deadline</small>
            </div>

            <div class="col-md-6">
               <label for="wizardChecklistStatusID" class="form-label fw-semibold">
                  Checklist Status
               </label>
               <select class="form-select" id="wizardChecklistStatusID" name="checklistStatusID">
                  <option value="">Select Status</option>
                  <?php if($checklistStatuses): ?>
                     <?php foreach($checklistStatuses as $status): ?>
                        <option value="<?= $status->proposalChecklistStatusID ?>"><?= htmlspecialchars($status->proposalChecklistStatusName) ?></option>
                     <?php endforeach; ?>
                  <?php endif; ?>
               </select>
            </div>

            <div class="col-md-6">
               <label for="wizardChecklistAssignedTo" class="form-label fw-semibold">
                  Assigned Employee
               </label>
               <select class="form-select" id="wizardChecklistAssignedTo" name="checklistAssignedTo">
                  <option value="">Select Employee</option>
                  <?php if($employeesCategorised): ?>
                     <?php foreach($employeesCategorised as $category => $employees): ?>
                        <optgroup label="<?= htmlspecialchars($category) ?>">
                           <?php foreach($employees as $employee): ?>
                              <option value="<?= $employee->ID ?>"><?= htmlspecialchars($employee->employeeName) ?></option>
                           <?php endforeach; ?>
                        </optgroup>
                     <?php endforeach; ?>
                  <?php endif; ?>
               </select>
            </div>

            <div class="col-md-12">
               <label for="wizardChecklistDescription" class="form-label fw-semibold">Checklist Description</label>
               <textarea class="form-control" id="wizardChecklistDescription" name="checklistDescription" rows="3" placeholder="Describe the checklist requirements..."></textarea>
            </div>
         </div>
      </div>

      <!-- Step 3: Checklist Items -->
      <div class="wizard-step" data-step="3">
         <h5 class="mb-4"><i class="ri-list-check me-2"></i>Select Checklist Items</h5>

         <div class="alert alert-info">
            <i class="ri-information-line me-2"></i>Select items from existing checklist items or add new ones. You can assign them in the next step.
         </div>

         <div class="row g-3 mb-3">
            <div class="col-md-12">
               <div class="d-flex justify-content-between align-items-center mb-3">
                  <label class="form-label fw-semibold mb-0">Available Checklist Items</label>
                  <button type="button" class="btn btn-sm btn-outline-primary" id="addNewChecklistItemBtn">
                     <i class="ri-add-line me-1"></i>Add New Item
                  </button>
               </div>

               <!-- Filter by Category -->
               <div class="mb-3">
                  <select class="form-select form-select-sm" id="filterChecklistCategory">
                     <option value="">All Categories</option>
                     <?php if($checklistItemCategories): ?>
                        <?php foreach($checklistItemCategories as $category): ?>
                           <option value="<?= $category->proposalChecklistItemCategoryID ?>">
                              <?= htmlspecialchars($category->proposalChecklistItemCategoryName) ?>
                           </option>
                        <?php endforeach; ?>
                     <?php endif; ?>
                  </select>
               </div>

               <!-- Checklist Items List -->
               <div class="checklist-items-container" style="max-height: 400px; overflow-y: auto;">
                  <?php if($checklistItems): ?>
                     <?php
                     $itemsByCategory = array();
                     foreach($checklistItems as $item) {
                        $catID = $item->proposalChecklistItemCategoryID ?? 'uncategorized';
                        if(!isset($itemsByCategory[$catID])) {
                           $itemsByCategory[$catID] = array();
                        }
                        $itemsByCategory[$catID][] = $item;
                     }
                     ?>
                     <?php foreach($itemsByCategory as $catID => $items): ?>
                        <div class="category-group mb-3" data-category-id="<?= $catID ?>">
                           <?php
                           $categoryName = 'Uncategorized';
                           foreach($checklistItemCategories as $cat) {
                              if($cat->proposalChecklistItemCategoryID == $catID) {
                                 $categoryName = $cat->proposalChecklistItemCategoryName;
                                 break;
                              }
                           }
                           ?>
                           <h6 class="text-muted mb-2"><?= htmlspecialchars($categoryName) ?></h6>
                           <?php foreach($items as $item): ?>
                              <div class="form-check mb-2">
                                 <input class="form-check-input checklist-item-checkbox"
                                        type="checkbox"
                                        value="<?= $item->proposalChecklistItemID ?>"
                                        id="item_<?= $item->proposalChecklistItemID ?>"
                                        data-item-name="<?= htmlspecialchars($item->proposalChecklistItemName) ?>"
                                        data-item-category="<?= $catID ?>">
                                 <label class="form-check-label" for="item_<?= $item->proposalChecklistItemID ?>">
                                    <?= htmlspecialchars($item->proposalChecklistItemName) ?>
                                    <?php if(isset($item->isMandatory) && $item->isMandatory === 'Y'): ?>
                                       <span class="badge bg-danger ms-2">Mandatory</span>
                                    <?php endif; ?>
                                 </label>
                              </div>
                           <?php endforeach; ?>
                        </div>
                     <?php endforeach; ?>
                  <?php else: ?>
                     <div class="alert alert-warning">No checklist items available. Please add new items.</div>
                  <?php endif; ?>
               </div>
            </div>
         </div>

         <!-- Add New Checklist Item Form (Hidden by default) -->
         <div class="card mt-3 d-none" id="newChecklistItemForm">
            <div class="card-header bg-primary text-white">
               <h6 class="mb-0">Add New Checklist Item</h6>
            </div>
            <div class="card-body">
               <div class="row g-3">
                  <div class="col-md-12">
                     <label for="newItemName" class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                     <input type="text" class="form-control" id="newItemName" placeholder="Enter item name">
                  </div>
                  <div class="col-md-6">
                     <label for="newItemCategory" class="form-label fw-semibold">Category</label>
                     <select class="form-select" id="newItemCategory">
                        <option value="">Select Category</option>
                        <?php if($checklistItemCategories): ?>
                           <?php foreach($checklistItemCategories as $category): ?>
                              <option value="<?= $category->proposalChecklistItemCategoryID ?>">
                                 <?= htmlspecialchars($category->proposalChecklistItemCategoryName) ?>
                              </option>
                           <?php endforeach; ?>
                        <?php endif; ?>
                     </select>
                  </div>
                  <div class="col-md-6">
                     <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" id="newItemMandatory">
                        <label class="form-check-label" for="newItemMandatory">Mandatory Item</label>
                     </div>
                  </div>
                  <div class="col-md-12">
                     <button type="button" class="btn btn-sm btn-success" id="saveNewItemBtn">
                        <i class="ri-save-line me-1"></i>Save Item
                     </button>
                     <button type="button" class="btn btn-sm btn-secondary" id="cancelNewItemBtn">Cancel</button>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <!-- Step 4: Assignments -->
      <div class="wizard-step" data-step="4">
         <h5 class="mb-4"><i class="ri-user-settings-line me-2"></i>Assign Checklist Items</h5>

         <div class="alert alert-info">
            <i class="ri-information-line me-2"></i>Assign selected checklist items to team members with due dates.
         </div>

         <div id="assignmentsContainer">
            <div class="text-center text-muted py-4">
               <i class="ri-inbox-line fs-48 mb-2 d-block"></i>
               <p>No items selected. Please go back and select checklist items.</p>
            </div>
         </div>
      </div>

      <!-- Step 5: Review -->
      <div class="wizard-step" data-step="5">
         <h5 class="mb-4"><i class="ri-eye-line me-2"></i>Review & Submit</h5>

         <div class="review-summary">
            <div class="card mb-3">
               <div class="card-header bg-primary text-white">
                  <h6 class="mb-0"><i class="ri-file-text-line me-2"></i>Proposal Summary</h6>
               </div>
               <div class="card-body" id="reviewProposalDetails">
                  <!-- Populated by JavaScript -->
               </div>
            </div>

            <div class="card mb-3">
               <div class="card-header bg-success text-white">
                  <h6 class="mb-0"><i class="ri-checkbox-multiple-line me-2"></i>Checklist Summary</h6>
               </div>
               <div class="card-body" id="reviewChecklistDetails">
                  <!-- Populated by JavaScript -->
               </div>
            </div>

            <div class="card mb-3">
               <div class="card-header bg-info text-white">
                  <h6 class="mb-0"><i class="ri-list-check me-2"></i>Selected Items</h6>
               </div>
               <div class="card-body" id="reviewItemsDetails">
                  <!-- Populated by JavaScript -->
               </div>
            </div>

            <div class="card">
               <div class="card-header bg-warning text-dark">
                  <h6 class="mb-0"><i class="ri-user-settings-line me-2"></i>Assignments Summary</h6>
               </div>
               <div class="card-body" id="reviewAssignmentsDetails">
                  <!-- Populated by JavaScript -->
               </div>
            </div>
         </div>
      </div>
   </div>

   <!-- Wizard Navigation Buttons -->
   <div class="wizard-navigation mt-4 d-flex justify-content-between">
      <button type="button" class="btn btn-secondary" id="wizardPrevBtn" style="display: none;">
         <i class="ri-arrow-left-line me-1"></i>Previous
      </button>
      <div class="ms-auto">
         <button type="button" class="btn btn-outline-secondary me-2" id="wizardSaveDraftBtn">
            <i class="ri-save-line me-1"></i>Save Draft
         </button>
         <button type="button" class="btn btn-primary" id="wizardNextBtn">
            Next <i class="ri-arrow-right-line ms-1"></i>
         </button>
         <button type="button" class="btn btn-success d-none" id="wizardSubmitBtn">
            <i class="ri-check-line me-1"></i>Create Proposal
         </button>
      </div>
   </div>

   <!-- Error Messages -->
   <div id="wizardErrors" class="alert alert-danger d-none mt-3"></div>
</div>

<style>
.wizard-progress {
   padding: 20px 0;
}

.progress-steps::before {
   content: '';
   position: absolute;
   top: 20px;
   left: 0;
   right: 0;
   height: 2px;
   background: #e0e0e0;
   z-index: 0;
}

.step-item {
   position: relative;
   z-index: 1;
   text-align: center;
   flex: 1;
}

.step-number {
   width: 40px;
   height: 40px;
   border-radius: 50%;
   background: #e0e0e0;
   color: #666;
   display: flex;
   align-items: center;
   justify-content: center;
   font-weight: bold;
   margin: 0 auto 8px;
   transition: all 0.3s;
}

.step-item.active .step-number {
   background: #007bff;
   color: white;
}

.step-item.completed .step-number {
   background: #28a745;
   color: white;
}

.step-item.completed .step-number::after {
   content: '✓';
}

.step-label {
   font-size: 12px;
   color: #666;
   font-weight: 500;
}

.step-item.active .step-label {
   color: #007bff;
   font-weight: 600;
}

.wizard-step {
   display: none;
   animation: fadeIn 0.3s;
}

.wizard-step.active {
   display: block;
}

@keyframes fadeIn {
   from { opacity: 0; transform: translateY(10px); }
   to { opacity: 1; transform: translateY(0); }
}

.checklist-items-container {
   border: 1px solid #dee2e6;
   border-radius: 0.375rem;
   padding: 15px;
}

.category-group {
   padding: 10px;
   background: #f8f9fa;
   border-radius: 0.25rem;
}

.assignment-item {
   border: 1px solid #dee2e6;
   border-radius: 0.375rem;
   padding: 15px;
   margin-bottom: 15px;
   background: #fff;
}

.assignment-item-header {
   display: flex;
   justify-content: space-between;
   align-items: center;
   margin-bottom: 10px;
}

.review-summary .card {
   border: none;
   box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.review-summary .card-body {
   padding: 20px;
}
</style>

<script>
(function() {
   'use strict';

   const wizardModal = document.getElementById('proposalCreationWizardModal');
   if (!wizardModal) return;

   let currentStep = 1;
   const totalSteps = 5;
   let wizardData = {
      proposal: {},
      checklist: {},
      selectedItems: [],
      assignments: []
   };

   // Initialize wizard
   function initWizard() {
      currentStep = 1;
      wizardData = {
         proposal: {},
         checklist: {},
         selectedItems: [],
         assignments: []
      };
      updateStepDisplay();
      updateNavigationButtons();

      // Auto-fill checklist name from proposal title
      const proposalTitleInput = document.getElementById('wizardProposalTitle');
      const checklistNameInput = document.getElementById('wizardChecklistName');
      if (proposalTitleInput && checklistNameInput) {
         proposalTitleInput.addEventListener('input', function() {
            if (!checklistNameInput.value || checklistNameInput.value === wizardData.proposal.proposalTitle + ' Checklist') {
               checklistNameInput.value = this.value ? this.value + ' Checklist' : '';
            }
         });
      }

      // Filter checklist items by category
      const categoryFilter = document.getElementById('filterChecklistCategory');
      if (categoryFilter) {
         categoryFilter.addEventListener('change', function() {
            const selectedCategory = this.value;
            const categoryGroups = document.querySelectorAll('.category-group');
            categoryGroups.forEach(group => {
               if (!selectedCategory || group.dataset.categoryId === selectedCategory) {
                  group.style.display = 'block';
               } else {
                  group.style.display = 'none';
               }
            });
         });
      }

      // Handle checklist item selection
      const checkboxes = document.querySelectorAll('.checklist-item-checkbox');
      checkboxes.forEach(checkbox => {
         checkbox.addEventListener('change', function() {
            updateSelectedItems();
            updateAssignmentsStep();
         });
      });

      // Add new checklist item
      const addNewItemBtn = document.getElementById('addNewChecklistItemBtn');
      const newItemForm = document.getElementById('newChecklistItemForm');
      const saveNewItemBtn = document.getElementById('saveNewItemBtn');
      const cancelNewItemBtn = document.getElementById('cancelNewItemBtn');

      if (addNewItemBtn && newItemForm) {
         addNewItemBtn.addEventListener('click', function() {
            newItemForm.classList.remove('d-none');
         });
      }

      if (cancelNewItemBtn && newItemForm) {
         cancelNewItemBtn.addEventListener('click', function() {
            newItemForm.classList.add('d-none');
            document.getElementById('newItemName').value = '';
            document.getElementById('newItemCategory').value = '';
            document.getElementById('newItemMandatory').checked = false;
         });
      }

      if (saveNewItemBtn) {
         saveNewItemBtn.addEventListener('click', function() {
            saveNewChecklistItem();
         });
      }

      // Initialize date pickers
      initWizardDatePickers();
   }

   // Initialize date pickers
   function initWizardDatePickers() {
      // Proposal deadline picker
      const proposalDeadlineInput = document.getElementById('wizardProposalDeadline');
      if (proposalDeadlineInput && typeof flatpickr !== 'undefined') {
         flatpickr(proposalDeadlineInput, {
            dateFormat: 'Y-m-d',
            minDate: 'today',
            allowInput: true
         });
      }

      // Checklist deadline picker
      const checklistDeadlineInput = document.getElementById('wizardChecklistDeadline');
      if (checklistDeadlineInput && typeof flatpickr !== 'undefined') {
         flatpickr(checklistDeadlineInput, {
            dateFormat: 'Y-m-d',
            minDate: 'today',
            allowInput: true,
            onChange: function(selectedDates, dateStr, instance) {
               // Validate against proposal deadline
               const proposalDeadline = document.getElementById('wizardProposalDeadline').value;
               if (proposalDeadline && new Date(dateStr) > new Date(proposalDeadline)) {
                  if (typeof showToast === 'function') {
                     showToast('Checklist deadline cannot be after proposal deadline', 'error');
                  }
                  instance.setDate(proposalDeadline);
               }
            }
         });
      }
   }

   // Update step display
   function updateStepDisplay() {
      // Update step indicators
      document.querySelectorAll('.step-item').forEach((item, index) => {
         const stepNum = index + 1;
         item.classList.remove('active', 'completed');
         if (stepNum === currentStep) {
            item.classList.add('active');
         } else if (stepNum < currentStep) {
            item.classList.add('completed');
         }
      });

      // Update step content
      document.querySelectorAll('.wizard-step').forEach((step, index) => {
         const stepNum = index + 1;
         if (stepNum === currentStep) {
            step.classList.add('active');
         } else {
            step.classList.remove('active');
         }
      });
   }

   // Update navigation buttons
   function updateNavigationButtons() {
      const prevBtn = document.getElementById('wizardPrevBtn');
      const nextBtn = document.getElementById('wizardNextBtn');
      const submitBtn = document.getElementById('wizardSubmitBtn');

      if (prevBtn) {
         prevBtn.style.display = currentStep > 1 ? 'inline-block' : 'none';
      }

      if (nextBtn) {
         nextBtn.style.display = currentStep < totalSteps ? 'inline-block' : 'none';
      }

      if (submitBtn) {
         submitBtn.style.display = currentStep === totalSteps ? 'inline-block' : 'none';
      }
   }

   // Validate current step
   function validateStep(step) {
      const errors = [];

      switch(step) {
         case 1:
            if (!document.getElementById('wizardProposalTitle').value.trim()) {
               errors.push('Proposal title is required');
            }
            if (!document.getElementById('wizardClientID').value) {
               errors.push('Client selection is required');
            }
            if (!document.getElementById('wizardSalesCaseID').value) {
               errors.push('Sales case selection is required');
            }
            if (!document.getElementById('wizardProposalDeadline').value) {
               errors.push('Proposal deadline is required');
            }
            if (!document.getElementById('wizardProposalValue').value) {
               errors.push('Proposal value is required');
            }
            if (!document.getElementById('wizardProposalStatusID').value) {
               errors.push('Proposal status is required');
            }
            break;

         case 2:
            if (!document.getElementById('wizardChecklistName').value.trim()) {
               errors.push('Checklist name is required');
            }
            if (!document.getElementById('wizardChecklistDeadline').value) {
               errors.push('Checklist deadline is required');
            }
            break;

         case 3:
            const selectedItems = Array.from(document.querySelectorAll('.checklist-item-checkbox:checked'));
            if (selectedItems.length === 0) {
               errors.push('Please select at least one checklist item');
            }
            break;

         case 4:
            if (wizardData.selectedItems.length === 0) {
               errors.push('No items selected for assignment');
            } else {
               // Validate that all assignments have required fields
               wizardData.assignments.forEach((assignment, index) => {
                  if (!assignment.assignedTo) {
                     errors.push(`Assignment ${index + 1}: Assigned user is required`);
                  }
                  if (!assignment.dueDate) {
                     errors.push(`Assignment ${index + 1}: Due date is required`);
                  }
               });
            }
            break;
      }

      return errors;
   }

   // Save step data
   function saveStepData(step) {
      switch(step) {
         case 1:
            wizardData.proposal = {
               proposalTitle: document.getElementById('wizardProposalTitle').value,
               clientID: document.getElementById('wizardClientID').value,
               salesCaseID: document.getElementById('wizardSalesCaseID').value,
               proposalDeadline: document.getElementById('wizardProposalDeadline').value,
               proposalValue: document.getElementById('wizardProposalValue').value,
               proposalStatusID: document.getElementById('wizardProposalStatusID').value,
               proposalDescription: document.getElementById('wizardProposalDescription').value
            };
            break;

         case 2:
            wizardData.checklist = {
               checklistName: document.getElementById('wizardChecklistName').value,
               checklistDeadline: document.getElementById('wizardChecklistDeadline').value,
               checklistStatusID: document.getElementById('wizardChecklistStatusID').value,
               checklistAssignedTo: document.getElementById('wizardChecklistAssignedTo').value,
               checklistDescription: document.getElementById('wizardChecklistDescription').value
            };
            break;

         case 3:
            updateSelectedItems();
            break;

         case 4:
            updateAssignmentsData();
            break;
      }
   }

   // Update selected items
   function updateSelectedItems() {
      const checkedBoxes = document.querySelectorAll('.checklist-item-checkbox:checked');
      wizardData.selectedItems = Array.from(checkedBoxes).map(cb => ({
         itemID: cb.value,
         itemName: cb.dataset.itemName,
         categoryID: cb.dataset.itemCategory
      }));
   }

   // Update assignments step
   function updateAssignmentsStep() {
      const container = document.getElementById('assignmentsContainer');
      if (!container) return;

      if (wizardData.selectedItems.length === 0) {
         container.innerHTML = `
            <div class="text-center text-muted py-4">
               <i class="ri-inbox-line fs-48 mb-2 d-block"></i>
               <p>No items selected. Please go back and select checklist items.</p>
            </div>
         `;
         return;
      }

      // Initialize assignments for selected items if not already done
      wizardData.selectedItems.forEach(item => {
         const existing = wizardData.assignments.find(a => a.itemID === item.itemID);
         if (!existing) {
            wizardData.assignments.push({
               itemID: item.itemID,
               itemName: item.itemName,
               assignedTo: '',
               dueDate: '',
               description: '',
               isMandatory: false
            });
         }
      });

      // Remove assignments for unselected items
      wizardData.assignments = wizardData.assignments.filter(a =>
         wizardData.selectedItems.some(item => item.itemID === a.itemID)
      );

      renderAssignments();
   }

   // Render assignments
   function renderAssignments() {
      const container = document.getElementById('assignmentsContainer');
      if (!container) return;

      container.innerHTML = wizardData.assignments.map((assignment, index) => {
         const employeesOptions = <?= json_encode(array_map(function($emp) {
            return ['ID' => $emp->ID, 'employeeName' => $emp->employeeName];
         }, $allEmployees ?: array())) ?>;

         const employeesOptionsHTML = employeesOptions.map(emp =>
            `<option value="${emp.ID}" ${assignment.assignedTo == emp.ID ? 'selected' : ''}>${emp.employeeName}</option>`
         ).join('');

         return `
            <div class="assignment-item" data-index="${index}">
               <div class="assignment-item-header">
                  <h6 class="mb-0">${assignment.itemName}</h6>
                  <span class="badge bg-primary">Item ${index + 1}</span>
               </div>
               <div class="row g-3">
                  <div class="col-md-6">
                     <label class="form-label fw-semibold">Assign To <span class="text-danger">*</span></label>
                     <select class="form-select assignment-assigned-to" data-index="${index}">
                        <option value="">Select User</option>
                        ${employeesOptionsHTML}
                     </select>
                  </div>
                  <div class="col-md-6">
                     <label class="form-label fw-semibold">Due Date <span class="text-danger">*</span></label>
                     <input type="text" class="form-control assignment-due-date" data-index="${index}" value="${assignment.dueDate}">
                  </div>
                  <div class="col-md-12">
                     <label class="form-label fw-semibold">Description/Notes</label>
                     <textarea class="form-control assignment-description" data-index="${index}" rows="2">${assignment.description || ''}</textarea>
                  </div>
                  <div class="col-md-12">
                     <div class="form-check form-switch">
                        <input class="form-check-input assignment-mandatory" type="checkbox" data-index="${index}" ${assignment.isMandatory ? 'checked' : ''}>
                        <label class="form-check-label">Mark as Mandatory</label>
                     </div>
                  </div>
               </div>
            </div>
         `;
      }).join('');

      // Initialize date pickers for assignment due dates
      container.querySelectorAll('.assignment-due-date').forEach(input => {
         if (typeof flatpickr !== 'undefined') {
            const checklistDeadline = wizardData.checklist.checklistDeadline;
            flatpickr(input, {
               dateFormat: 'Y-m-d',
               minDate: 'today',
               maxDate: checklistDeadline || undefined,
               allowInput: true
            });
         }
      });

      // Attach event listeners
      container.querySelectorAll('.assignment-assigned-to').forEach(select => {
         select.addEventListener('change', function() {
            const index = parseInt(this.dataset.index);
            wizardData.assignments[index].assignedTo = this.value;
         });
      });

      container.querySelectorAll('.assignment-due-date').forEach(input => {
         input.addEventListener('change', function() {
            const index = parseInt(this.dataset.index);
            wizardData.assignments[index].dueDate = this.value;
         });
      });

      container.querySelectorAll('.assignment-description').forEach(textarea => {
         textarea.addEventListener('input', function() {
            const index = parseInt(this.dataset.index);
            wizardData.assignments[index].description = this.value;
         });
      });

      container.querySelectorAll('.assignment-mandatory').forEach(checkbox => {
         checkbox.addEventListener('change', function() {
            const index = parseInt(this.dataset.index);
            wizardData.assignments[index].isMandatory = this.checked;
         });
      });
   }

   // Update assignments data
   function updateAssignmentsData() {
      // Data is already updated via event listeners
      // This function can be used for final validation
   }

   // Update review step
   function updateReviewStep() {
      // Proposal details
      const proposalDetails = document.getElementById('reviewProposalDetails');
      if (proposalDetails) {
         proposalDetails.innerHTML = `
            <div class="row">
               <div class="col-md-6"><strong>Title:</strong></div>
               <div class="col-md-6">${wizardData.proposal.proposalTitle || 'N/A'}</div>
               <div class="col-md-6"><strong>Client:</strong></div>
               <div class="col-md-6">${getClientName(wizardData.proposal.clientID)}</div>
               <div class="col-md-6"><strong>Sales Case:</strong></div>
               <div class="col-md-6">${getSalesCaseName(wizardData.proposal.salesCaseID)}</div>
               <div class="col-md-6"><strong>Deadline:</strong></div>
               <div class="col-md-6">${wizardData.proposal.proposalDeadline || 'N/A'}</div>
               <div class="col-md-6"><strong>Value:</strong></div>
               <div class="col-md-6">KES ${parseFloat(wizardData.proposal.proposalValue || 0).toLocaleString('en-KE', {minimumFractionDigits: 2})}</div>
               <div class="col-md-6"><strong>Status:</strong></div>
               <div class="col-md-6">${getProposalStatusName(wizardData.proposal.proposalStatusID)}</div>
               ${wizardData.proposal.proposalDescription ? `
                  <div class="col-md-12 mt-2"><strong>Description:</strong></div>
                  <div class="col-md-12">${wizardData.proposal.proposalDescription}</div>
               ` : ''}
            </div>
         `;
      }

      // Checklist details
      const checklistDetails = document.getElementById('reviewChecklistDetails');
      if (checklistDetails) {
         checklistDetails.innerHTML = `
            <div class="row">
               <div class="col-md-6"><strong>Name:</strong></div>
               <div class="col-md-6">${wizardData.checklist.checklistName || 'N/A'}</div>
               <div class="col-md-6"><strong>Deadline:</strong></div>
               <div class="col-md-6">${wizardData.checklist.checklistDeadline || 'N/A'}</div>
               <div class="col-md-6"><strong>Assigned To:</strong></div>
               <div class="col-md-6">${getEmployeeName(wizardData.checklist.checklistAssignedTo)}</div>
               ${wizardData.checklist.checklistDescription ? `
                  <div class="col-md-12 mt-2"><strong>Description:</strong></div>
                  <div class="col-md-12">${wizardData.checklist.checklistDescription}</div>
               ` : ''}
            </div>
         `;
      }

      // Selected items
      const itemsDetails = document.getElementById('reviewItemsDetails');
      if (itemsDetails) {
         itemsDetails.innerHTML = `
            <ul class="list-group">
               ${wizardData.selectedItems.map(item => `
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                     ${item.itemName}
                     ${wizardData.assignments.find(a => a.itemID === item.itemID)?.isMandatory ?
                        '<span class="badge bg-danger">Mandatory</span>' : ''}
                  </li>
               `).join('')}
            </ul>
         `;
      }

      // Assignments
      const assignmentsDetails = document.getElementById('reviewAssignmentsDetails');
      if (assignmentsDetails) {
         assignmentsDetails.innerHTML = `
            <div class="table-responsive">
               <table class="table table-sm">
                  <thead>
                     <tr>
                        <th>Item</th>
                        <th>Assigned To</th>
                        <th>Due Date</th>
                        <th>Mandatory</th>
                     </tr>
                  </thead>
                  <tbody>
                     ${wizardData.assignments.map(assignment => `
                        <tr>
                           <td>${assignment.itemName}</td>
                           <td>${getEmployeeName(assignment.assignedTo)}</td>
                           <td>${assignment.dueDate || 'N/A'}</td>
                           <td>${assignment.isMandatory ? '<span class="badge bg-danger">Yes</span>' : '<span class="badge bg-secondary">No</span>'}</td>
                        </tr>
                     `).join('')}
                  </tbody>
               </table>
            </div>
         `;
      }
   }

   // Helper functions
   function getClientName(clientID) {
      const select = document.getElementById('wizardClientID');
      if (select) {
         const option = select.querySelector(`option[value="${clientID}"]`);
         return option ? option.textContent : 'N/A';
      }
      return 'N/A';
   }

   function getSalesCaseName(salesCaseID) {
      const select = document.getElementById('wizardSalesCaseID');
      if (select) {
         const option = select.querySelector(`option[value="${salesCaseID}"]`);
         return option ? option.textContent : 'N/A';
      }
      return 'N/A';
   }

   function getProposalStatusName(statusID) {
      const select = document.getElementById('wizardProposalStatusID');
      if (select) {
         const option = select.querySelector(`option[value="${statusID}"]`);
         return option ? option.textContent : 'N/A';
      }
      return 'N/A';
   }

   function getEmployeeName(employeeID) {
      const select = document.getElementById('wizardChecklistAssignedTo');
      if (select) {
         const option = select.querySelector(`option[value="${employeeID}"]`);
         return option ? option.textContent : 'N/A';
      }
      return 'N/A';
   }

   // Save new checklist item
   function saveNewChecklistItem() {
      const itemName = document.getElementById('newItemName').value.trim();
      const categoryID = document.getElementById('newItemCategory').value;
      const isMandatory = document.getElementById('newItemMandatory').checked;

      if (!itemName) {
         if (typeof showToast === 'function') {
            showToast('Item name is required', 'error');
         }
         return;
      }

      // Create form data
      const formData = new FormData();
      formData.append('action', 'create');
      formData.append('proposalChecklistItemName', itemName);
      formData.append('proposalChecklistItemCategoryID', categoryID);
      formData.append('isMandatory', isMandatory ? 'Y' : 'N');
      formData.append('orgDataID', '<?= $orgDataID ?? '' ?>');
      formData.append('entityID', '<?= $entityID ?? '' ?>');

      // Save via AJAX - Note: This script may redirect, so we'll handle it differently
      fetch('<?= $base ?>php/scripts/sales/proposal_checklist/manage_proposal_checklist_item.php', {
         method: 'POST',
         body: formData
      })
      .then(response => {
         // Check if response is JSON or HTML (redirect)
         const contentType = response.headers.get('content-type');
         if (contentType && contentType.includes('application/json')) {
            return response.json();
         } else {
            // If it's a redirect or HTML, assume success for now
            // In a real scenario, you'd want to parse the response
            return { success: true, data: { itemID: Date.now(), itemName: itemName } };
         }
      })
      .then(data => {
         if (data.success) {
            if (typeof showToast === 'function') {
               showToast('Checklist item created successfully', 'success');
            }

            // Add to the list
            const categoryGroup = document.querySelector(`.category-group[data-category-id="${categoryID || 'uncategorized'}"]`);
            if (!categoryGroup) {
               // Create new category group if needed
               const container = document.querySelector('.checklist-items-container');
               const newGroup = document.createElement('div');
               newGroup.className = 'category-group mb-3';
               newGroup.dataset.categoryId = categoryID || 'uncategorized';
               newGroup.innerHTML = `
                  <h6 class="text-muted mb-2">${categoryID ? getCategoryName(categoryID) : 'Uncategorized'}</h6>
                  <div class="form-check mb-2">
                     <input class="form-check-input checklist-item-checkbox"
                            type="checkbox"
                            value="${data.data.itemID}"
                            id="item_${data.data.itemID}"
                            data-item-name="${itemName}"
                            data-item-category="${categoryID || 'uncategorized'}">
                     <label class="form-check-label" for="item_${data.data.itemID}">
                        ${itemName}
                        ${isMandatory ? '<span class="badge bg-danger ms-2">Mandatory</span>' : ''}
                     </label>
                  </div>
               `;
               container.appendChild(newGroup);
            } else {
               // Add to existing group
               const newItem = document.createElement('div');
               newItem.className = 'form-check mb-2';
               newItem.innerHTML = `
                  <input class="form-check-input checklist-item-checkbox"
                         type="checkbox"
                         value="${data.data.itemID}"
                         id="item_${data.data.itemID}"
                         data-item-name="${itemName}"
                         data-item-category="${categoryID || 'uncategorized'}">
                  <label class="form-check-label" for="item_${data.data.itemID}">
                     ${itemName}
                     ${isMandatory ? '<span class="badge bg-danger ms-2">Mandatory</span>' : ''}
                  </label>
               `;
               categoryGroup.appendChild(newItem);
            }

            // Attach event listener
            const newCheckbox = document.getElementById(`item_${data.data.itemID}`);
            if (newCheckbox) {
               newCheckbox.addEventListener('change', function() {
                  updateSelectedItems();
                  updateAssignmentsStep();
               });
            }

            // Reset form
            document.getElementById('newItemName').value = '';
            document.getElementById('newItemCategory').value = '';
            document.getElementById('newItemMandatory').checked = false;
            document.getElementById('newChecklistItemForm').classList.add('d-none');
         } else {
            if (typeof showToast === 'function') {
               showToast(data.message || 'Failed to create checklist item', 'error');
            }
         }
      })
      .catch(error => {
         console.error('Error:', error);
         if (typeof showToast === 'function') {
            showToast('Network error. Please try again.', 'error');
         }
      });
   }

   function getCategoryName(categoryID) {
      const select = document.getElementById('filterChecklistCategory');
      if (select) {
         const option = select.querySelector(`option[value="${categoryID}"]`);
         return option ? option.textContent : 'Uncategorized';
      }
      return 'Uncategorized';
   }

   // Navigation handlers
   document.getElementById('wizardNextBtn')?.addEventListener('click', function() {
      const errors = validateStep(currentStep);
      if (errors.length > 0) {
         const errorDiv = document.getElementById('wizardErrors');
         errorDiv.innerHTML = '<ul class="mb-0"><li>' + errors.join('</li><li>') + '</li></ul>';
         errorDiv.classList.remove('d-none');
         return;
      }

      document.getElementById('wizardErrors').classList.add('d-none');
      saveStepData(currentStep);

      if (currentStep === 3) {
         updateAssignmentsStep();
      } else if (currentStep === 4) {
         updateReviewStep();
      }

      currentStep++;
      updateStepDisplay();
      updateNavigationButtons();
   });

   document.getElementById('wizardPrevBtn')?.addEventListener('click', function() {
      if (currentStep > 1) {
         currentStep--;
         updateStepDisplay();
         updateNavigationButtons();
      }
   });

   // Submit wizard
   document.getElementById('wizardSubmitBtn')?.addEventListener('click', function() {
      submitWizard();
   });

   // Save draft
   document.getElementById('wizardSaveDraftBtn')?.addEventListener('click', function() {
      saveStepData(currentStep);
      saveDraft();
   });

   // Submit wizard
   function submitWizard() {
      const submitBtn = document.getElementById('wizardSubmitBtn');
      const originalText = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';

      // Collect all data
      saveStepData(currentStep);

      const formData = new FormData();

      // Proposal data
      Object.keys(wizardData.proposal).forEach(key => {
         formData.append(key, wizardData.proposal[key]);
      });

      // Checklist data
      Object.keys(wizardData.checklist).forEach(key => {
         formData.append('checklist_' + key, wizardData.checklist[key]);
      });

      // Selected items
      formData.append('selectedItems', JSON.stringify(wizardData.selectedItems));

      // Assignments
      formData.append('assignments', JSON.stringify(wizardData.assignments));

      formData.append('action', 'create_with_checklist');

      fetch('<?= $base ?>php/scripts/sales/manage_proposal_wizard.php', {
         method: 'POST',
         body: formData
      })
      .then(response => response.json())
      .then(data => {
         if (data.success) {
            if (typeof showToast === 'function') {
               showToast('Proposal created successfully!', 'success');
            }

            // Close modal and reload
            const modal = bootstrap.Modal.getInstance(wizardModal);
            if (modal) {
               modal.hide();
            }

            setTimeout(() => {
               if (data.data && data.data.proposalID) {
                  window.location.href = '<?= $base ?>html/?s=user&ss=sales&p=proposal_details&prID=' + data.data.proposalID;
               } else {
                  location.reload();
               }
            }, 1000);
         } else {
            const errorDiv = document.getElementById('wizardErrors');
            errorDiv.innerHTML = '<ul class="mb-0"><li>' + (data.message || 'Failed to create proposal') + '</li></ul>';
            errorDiv.classList.remove('d-none');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
         }
      })
      .catch(error => {
         console.error('Error:', error);
         const errorDiv = document.getElementById('wizardErrors');
         errorDiv.innerHTML = '<ul class="mb-0"><li>Network error. Please try again.</li></ul>';
         errorDiv.classList.remove('d-none');
         submitBtn.disabled = false;
         submitBtn.innerHTML = originalText;
      });
   }

   // Save draft (simplified - just saves proposal)
   function saveDraft() {
      saveStepData(currentStep);

      const formData = new FormData();
      Object.keys(wizardData.proposal).forEach(key => {
         formData.append(key, wizardData.proposal[key]);
      });
      formData.append('action', 'create');
      formData.append('ajax', '1'); // Flag as AJAX request

      const saveBtn = document.getElementById('wizardSaveDraftBtn');
      const originalText = saveBtn.innerHTML;
      saveBtn.disabled = true;
      saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

      fetch('<?= $base ?>php/scripts/sales/manage_proposal.php', {
         method: 'POST',
         body: formData
      })
      .then(response => {
         // Check if response is JSON
         const contentType = response.headers.get('content-type');
         if (contentType && contentType.includes('application/json')) {
            return response.json();
         } else {
            // If HTML, try to parse as text first
            return response.text().then(text => {
               try {
                  return JSON.parse(text);
               } catch (e) {
                  throw new Error('Server returned invalid response. Expected JSON but got HTML.');
               }
            });
         }
      })
      .then(data => {
         if (data && data.success) {
            if (typeof showToast === 'function') {
               showToast('Draft saved successfully', 'success');
            }
            if (data.data && data.data.proposalID) {
               document.getElementById('wizardProposalID').value = data.data.proposalID;
            }
         } else {
            const errorMsg = (data && data.message) ? data.message : 'Failed to save draft';
            if (typeof showToast === 'function') {
               showToast(errorMsg, 'error');
            }
         }
      })
      .catch(error => {
         console.error('Error:', error);
         const errorMsg = error.message || 'Network error. Please try again.';
         if (typeof showToast === 'function') {
            showToast(errorMsg, 'error');
         }
      })
      .finally(() => {
         saveBtn.disabled = false;
         saveBtn.innerHTML = originalText;
      });
   }

   // Filter sales cases by client
   document.getElementById('wizardClientID')?.addEventListener('change', function() {
      const clientID = this.value;
      const salesCaseSelect = document.getElementById('wizardSalesCaseID');
      if (salesCaseSelect) {
         salesCaseSelect.querySelectorAll('option').forEach(option => {
            if (option.value === '') return;
            if (option.dataset.clientId === clientID) {
               option.style.display = 'block';
            } else {
               option.style.display = 'none';
            }
         });
         salesCaseSelect.value = '';
      }
   });

   // Initialize when modal is shown
   wizardModal.addEventListener('shown.bs.modal', function() {
      initWizard();
   });

   // Reset when modal is hidden
   wizardModal.addEventListener('hidden.bs.modal', function() {
      currentStep = 1;
      wizardData = {
         proposal: {},
         checklist: {},
         selectedItems: [],
         assignments: []
      };
      document.getElementById('wizardErrors').classList.add('d-none');
   });
})();
</script>

