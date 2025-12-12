<?php
if(!$isValidUser) {
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}

$proposalID= (isset($_GET['prID']) && !empty($_GET['prID'])) ? Utility::clean_string($_GET['prID']) : ((isset($_GET['proposalID']) && !empty($_GET['proposalID'])) ? Utility::clean_string($_GET['proposalID']) : 0);
$getString.= "&prID={$proposalID}";

$proposalDetails = Sales::proposal_full(array('proposalID'=>$proposalID), true, $DBConn);
if(!$proposalDetails) {
  Alert::info("Proposal not found", true, array('fst-italic', 'text-center', 'font-18'));
  return;
}
$salesCases = Sales::sales_cases(array('orgDataID'=>$proposalDetails->orgDataID, 'entityID'=>$proposalDetails->entityID), false, $DBConn);
$clients = Client::clients(array('orgDataID'=>$proposalDetails->orgDataID, 'entityID'=>$proposalDetails->entityID), false, $DBConn);
$proposalStatuses = Sales::proposal_statuses(array('orgDataID'=>$proposalDetails->orgDataID, 'entityID'=>$proposalDetails->entityID), false, $DBConn);
$employeeID=(isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) : $userDetails->ID;
$entityID= $_SESSION['entityID'] ? $_SESSION['entityID'] :null;
$orgDataID = $_SESSION['orgDataID'] ? $_SESSION['orgDataID']  : null;
$employeeList = Employee::employees(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
$checklistStatuses = Proposal::proposal_checklist_status(['proposalChecklistStatusType'=>'checkListItem'], false, $DBConn);
$checklistItems = Proposal::proposal_checklist_items([], false, $DBConn);
$checklistItemCategories = Proposal::proposal_checklist_items_categories([], false, $DBConn);

$employeesCategorised = Employee::categorise_employee($employeeList, 'jobTitle');
$checkListStatus = Proposal::proposal_checklist_status([], false, $DBConn);

// Get active tab from URL or default to 'overview'
$activeTab = isset($_GET['tab']) ? Utility::clean_string($_GET['tab']) : 'overview';
?>

<!-- ============================================== -->
<!-- PROPOSAL DETAILS - TABBED INTERFACE STYLES    -->
<!-- ============================================== -->
<style>
   /* Main Container */
   .proposal-details-container {
      --pd-primary: #0d6efd;
      --pd-primary-light: rgba(13, 110, 253, 0.1);
      --pd-gradient-start: #667eea;
      --pd-gradient-end: #764ba2;
      --pd-success: #198754;
      --pd-warning: #ffc107;
      --pd-danger: #dc3545;
      --pd-border: #e9ecef;
      --pd-muted: #6c757d;
   }

   /* Header Section */
   .proposal-header {
      background: linear-gradient(135deg, var(--pd-gradient-start) 0%, var(--pd-gradient-end) 100%);
      border-radius: 16px 16px 0 0;
      padding: 1.5rem 2rem;
      color: white;
      margin-bottom: 0;
   }
   .proposal-header-title {
      font-size: 1.75rem;
      font-weight: 600;
      margin: 0;
      display: flex;
      align-items: center;
      gap: 0.75rem;
   }
   .proposal-header-meta {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-top: 0.75rem;
      flex-wrap: wrap;
   }
   .proposal-header-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      background: rgba(255,255,255,0.2);
      padding: 0.35rem 0.85rem;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 500;
   }
   .proposal-value-display {
      background: rgba(255,255,255,0.95);
      color: var(--pd-gradient-start);
      padding: 0.5rem 1rem;
      border-radius: 8px;
      font-weight: 700;
      font-size: 1.1rem;
   }
   .proposal-owner-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: rgba(255,255,255,0.9);
      color: var(--pd-gradient-start);
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      font-size: 0.875rem;
   }

   /* Tabbed Navigation */
   .proposal-tabs-container {
      background: #fff;
      border-radius: 0 0 16px 16px;
      box-shadow: 0 4px 24px rgba(0,0,0,0.08);
      overflow: hidden;
   }
   .proposal-nav-tabs {
      background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
      border-bottom: 2px solid var(--pd-border);
      padding: 0 1rem;
      display: flex;
      gap: 0.25rem;
      overflow-x: auto;
      scrollbar-width: none;
   }
   .proposal-nav-tabs::-webkit-scrollbar {
      display: none;
   }
   .proposal-nav-tab {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      padding: 1rem 1.5rem;
      font-weight: 500;
      color: var(--pd-muted);
      text-decoration: none;
      border-bottom: 3px solid transparent;
      transition: all 0.2s ease;
      white-space: nowrap;
      position: relative;
   }
   .proposal-nav-tab:hover {
      color: var(--pd-primary);
      background: var(--pd-primary-light);
   }
   .proposal-nav-tab.active {
      color: var(--pd-primary);
      border-bottom-color: var(--pd-primary);
      background: rgba(13, 110, 253, 0.05);
   }
   .proposal-nav-tab i {
      font-size: 1.1rem;
   }
   .proposal-tab-badge {
      background: var(--pd-primary);
      color: white;
      font-size: 0.7rem;
      padding: 0.15rem 0.5rem;
      border-radius: 10px;
      font-weight: 600;
   }
   .proposal-nav-tab.active .proposal-tab-badge {
      background: var(--pd-primary);
   }

   /* Tab Content */
   .proposal-tab-content {
      display: none;
      padding: 1.5rem;
      animation: fadeIn 0.3s ease;
   }
   .proposal-tab-content.active {
      display: block;
   }
   @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
   }

   /* Section Cards inside tabs */
   .proposal-section-card {
      background: #fff;
      border: 1px solid var(--pd-border);
      border-radius: 12px;
      margin-bottom: 1.5rem;
      overflow: hidden;
   }
   .proposal-section-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 1rem 1.25rem;
      background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
      border-bottom: 1px solid var(--pd-border);
   }
   .proposal-section-title {
      font-size: 1.1rem;
      font-weight: 600;
      color: #212529;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin: 0;
   }
   .proposal-section-title i {
      color: var(--pd-primary);
   }
   .proposal-section-body {
      padding: 1.25rem;
   }

   /* Quick Stats */
   .proposal-quick-stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-bottom: 1.5rem;
   }
   .proposal-stat-card {
      background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
      border: 1px solid var(--pd-border);
      border-radius: 12px;
      padding: 1.25rem;
      display: flex;
      align-items: center;
      gap: 1rem;
   }
   .proposal-stat-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.25rem;
   }
   .proposal-stat-icon.primary { background: var(--pd-primary-light); color: var(--pd-primary); }
   .proposal-stat-icon.success { background: rgba(25, 135, 84, 0.1); color: var(--pd-success); }
   .proposal-stat-icon.warning { background: rgba(255, 193, 7, 0.15); color: #b86e00; }
   .proposal-stat-icon.danger { background: rgba(220, 53, 69, 0.1); color: var(--pd-danger); }
   .proposal-stat-value {
      font-size: 1.5rem;
      font-weight: 700;
      color: #212529;
      line-height: 1;
   }
   .proposal-stat-label {
      font-size: 0.8rem;
      color: var(--pd-muted);
      margin-top: 0.25rem;
   }

   /* Mobile Responsive */
   @media (max-width: 768px) {
      .proposal-header {
         padding: 1rem 1.25rem;
         border-radius: 12px 12px 0 0;
      }
      .proposal-header-title {
         font-size: 1.25rem;
      }
      .proposal-nav-tab {
         padding: 0.75rem 1rem;
         font-size: 0.9rem;
      }
      .proposal-tab-content {
         padding: 1rem;
      }
   }
</style>

<!-- Page Header Breadcrumb -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb border-bottom">
   <h1 class="page-title fw-medium fs-24 mb-0"><?= "{$proposalDetails->proposalTitle}" ?></h1>
   <div class="ms-md-1 ms-0">
      <div class="d-flex align-items-center gap-2">
         <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=proposals" ?>" class="btn btn-outline-secondary btn-sm">
            <i class="ri-arrow-left-line me-1"></i> Back to Proposals
         </a>
      </div>
   </div>
</div>

<!-- Proposal Status Tracker & Completion -->
<?php include "includes/scripts/sales/proposal_status_tracker.php"; ?>

<div class="container-fluid proposal-details-container">
   <!-- ============================================== -->
   <!-- PROPOSAL HEADER                               -->
   <!-- ============================================== -->
   <div class="proposal-header">
      <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
         <div>
            <h1 class="proposal-header-title">
               <i class="ri-file-list-3-line"></i>
               <?= htmlspecialchars($proposalDetails->proposalTitle) ?>
            </h1>
            <div class="proposal-header-meta">
               <span class="proposal-header-badge">
                  <i class="ri-user-3-line"></i>
                  <?= htmlspecialchars($proposalDetails->clientName ?? 'No Client') ?>
               </span>
               <span class="proposal-header-badge">
                  <i class="ri-calendar-line"></i>
                  Due: <?= Utility::date_format($proposalDetails->proposalDeadline) ?>
               </span>
               <span class="proposal-header-badge">
                  <?php
                  switch($proposalDetails->proposalStatusID) {
                     case 1: $statusColor = 'bg-info'; break;
                     case 2: $statusColor = 'bg-danger'; break;
                     case 3: $statusColor = 'bg-warning'; break;
                     case 4: $statusColor = 'bg-success'; break;
                     default: $statusColor = 'bg-secondary'; break;
                  }
                  ?>
                  <span class="badge <?= $statusColor ?>"><?= $proposalDetails->proposalStatusName ?></span>
               </span>
            </div>
         </div>
         <div class="d-flex align-items-center gap-3">
            <div class="proposal-value-display">
               <span style="font-size: 0.75rem; font-weight: 400;">KES</span>
               <?= number_format($proposalDetails->proposalValue ?? 0, 2, '.', ',') ?>
            </div>
            <div class="d-flex align-items-center gap-2">
               <div class="proposal-owner-avatar">
                  <?php
                  if(isset($proposalDetails->userInitials) && !empty($proposalDetails->userInitials)) {
                     echo $proposalDetails->userInitials;
                  } else {
                     echo $userDetails->userInitials ?? "NA";
                  }
                  ?>
               </div>
               <div style="line-height: 1.2;">
                  <small style="opacity: 0.8;">Owner</small><br>
                  <span style="font-weight: 500;"><?= htmlspecialchars($employeeDetails->employeeName ?? 'Unassigned') ?></span>
               </div>
            </div>
         </div>
      </div>
   </div>

   <!-- ============================================== -->
   <!-- TABBED NAVIGATION                             -->
   <!-- ============================================== -->
   <div class="proposal-tabs-container">
      <nav class="proposal-nav-tabs" role="tablist">
         <a href="#overview" class="proposal-nav-tab <?= $activeTab === 'overview' ? 'active' : '' ?>" data-tab="overview" role="tab">
            <i class="ri-dashboard-line"></i>
            <span>Overview</span>
         </a>
         <a href="#attachments" class="proposal-nav-tab <?= $activeTab === 'attachments' ? 'active' : '' ?>" data-tab="attachments" role="tab">
            <i class="ri-attachment-2"></i>
            <span>Attachments</span>
            <?php
            $attachmentCount = Proposal::proposal_attachments(array('proposalID'=>$proposalID), false, $DBConn);
            $attachmentCount = $attachmentCount ? count($attachmentCount) : 0;
            ?>
            <?php if($attachmentCount > 0): ?>
               <span class="proposal-tab-badge"><?= $attachmentCount ?></span>
            <?php endif; ?>
         </a>
         <a href="#tasks" class="proposal-nav-tab <?= $activeTab === 'tasks' ? 'active' : '' ?>" data-tab="tasks" role="tab">
            <i class="ri-task-line"></i>
            <span>Tasks</span>
         </a>
         <a href="#submissions" class="proposal-nav-tab <?= $activeTab === 'submissions' ? 'active' : '' ?>" data-tab="submissions" role="tab">
            <i class="ri-upload-cloud-2-line"></i>
            <span>Submissions</span>
         </a>
         <a href="#requirements" class="proposal-nav-tab <?= $activeTab === 'requirements' ? 'active' : '' ?>" data-tab="requirements" role="tab">
            <i class="ri-checkbox-multiple-line"></i>
            <span>Requirements</span>
            <?php
            $checklistCount = Proposal::proposal_checklist(array('orgDataID'=>$proposalDetails->orgDataID, 'entityID'=>$proposalDetails->entityID, 'proposalID'=>$proposalID), false, $DBConn);
            $checklistCount = $checklistCount ? count($checklistCount) : 0;
            ?>
            <?php if($checklistCount > 0): ?>
               <span class="proposal-tab-badge"><?= $checklistCount ?></span>
            <?php endif; ?>
         </a>
      </nav>

      <!-- ============================================== -->
      <!-- TAB 1: OVERVIEW                               -->
      <!-- ============================================== -->
      <div id="overview" class="proposal-tab-content <?= $activeTab === 'overview' ? 'active' : '' ?>" role="tabpanel">
         <?php include "includes/scripts/sales/proposal_tabs/overview_tab.php"; ?>
      </div>

      <!-- ============================================== -->
      <!-- TAB 2: ATTACHMENTS                            -->
      <!-- ============================================== -->
      <div id="attachments" class="proposal-tab-content <?= $activeTab === 'attachments' ? 'active' : '' ?>" role="tabpanel">
         <?php include "includes/scripts/sales/proposal_tabs/attachments_tab.php"; ?>
      </div>

      <!-- ============================================== -->
      <!-- TAB 3: TASKS                                  -->
      <!-- ============================================== -->
      <div id="tasks" class="proposal-tab-content <?= $activeTab === 'tasks' ? 'active' : '' ?>" role="tabpanel">
         <?php include "includes/scripts/sales/proposal_tasks_display.php"; ?>
      </div>

      <!-- ============================================== -->
      <!-- TAB 4: SUBMISSIONS                            -->
      <!-- ============================================== -->
      <div id="submissions" class="proposal-tab-content <?= $activeTab === 'submissions' ? 'active' : '' ?>" role="tabpanel">
         <?php include "includes/scripts/sales/checklist_item_submission_ui.php"; ?>
      </div>

      <!-- ============================================== -->
      <!-- TAB 5: REQUIREMENTS / CHECKLISTS              -->
      <!-- ============================================== -->
      <div id="requirements" class="proposal-tab-content <?= $activeTab === 'requirements' ? 'active' : '' ?>" role="tabpanel">
         <?php include "includes/scripts/sales/proposal_tabs/requirements_tab.php"; ?>
      </div>
   </div>
</div>

<?php
// ==============================================
// MODALS
// ==============================================

// manage checklist item modal
echo Utility::form_modal_header("addChecklistItemAssignment", "sales/proposal_checklist/manage_proposal_checklist_item_assignment.php", "Proposal Checklist Item", array('modal-lg', 'modal-dialog-centered'), $base);
include_once("html/includes/scripts/sales/proposal_checklist/modals/manage_proposal_checklist_item_assignment.php");
echo Utility::form_modal_footer('Save Proposal Checklist', 'saveProposalChecklistAssignment',  ' btn btn-success btn-sm', true);

// manage checklist modal
echo Utility::form_modal_header("manageChecklistModal", "sales/proposal_checklist/manage_proposal_checklist.php", "Proposal Checklist", array('modal-lg', 'modal-dialog-centered'), $base);
include_once("html/includes/scripts/sales/proposal_checklist/modals/manage_proposal_checklist_modal.php");
echo Utility::form_modal_footer('Save Proposal Checklist', 'saveProposalChecklist',  ' btn btn-success btn-sm', true);

// Proposal Task Management Modal
echo Utility::form_modal_header("manageProposalTaskModal", "", "Manage Proposal Task", array('modal-lg', 'modal-dialog-centered'), $base);
include "includes/scripts/sales/modals/manage_proposal_task.php";
echo Utility::form_modal_footer('Save Task', 'submitProposalTask',  ' btn btn-success btn-sm', true);

// Proposal Attachment Modal
echo Utility::form_modal_header("manageProposalAttachmentModal", "sales/proposal_attachments/manage_proposal_attachment.php", "Proposal Attachment", array('modal-lg', 'modal-dialog-centered'), $base);
include_once("html/includes/scripts/sales/proposal_attachments/manage_proposal_attachment.php");
echo Utility::form_modal_footer('Save Proposal Attachment', 'saveProposalAttachment',  ' btn btn-success btn-sm', true);

// Initialize all proposal date pickers with Flatpickr
include "includes/scripts/sales/proposal_date_pickers.php";
?>

<!-- ============================================== -->
<!-- TAB SWITCHING & MODAL SCRIPTS                  -->
<!-- ============================================== -->
<script>
document.addEventListener("DOMContentLoaded", function() {
   // ===== TAB NAVIGATION =====
   const tabs = document.querySelectorAll('.proposal-nav-tab');
   const tabContents = document.querySelectorAll('.proposal-tab-content');

   tabs.forEach(tab => {
      tab.addEventListener('click', function(e) {
         e.preventDefault();
         const targetTab = this.getAttribute('data-tab');

         // Update URL without page reload
         const url = new URL(window.location);
         url.searchParams.set('tab', targetTab);
         window.history.pushState({}, '', url);

         // Update active states
         tabs.forEach(t => t.classList.remove('active'));
         tabContents.forEach(c => c.classList.remove('active'));

         this.classList.add('active');
         document.getElementById(targetTab).classList.add('active');
      });
   });

   // ===== CHECKLIST CATEGORY EDIT MODAL =====
   document.querySelectorAll('.editChecklistItemCategory').forEach(button => {
      button.addEventListener('click', function() {
         const form = document.getElementById('proposalChecklistModalForm');
         if (!form) return;

         const data = this.dataset;
         console.log(data);

         const fieldMappings = {
            'proposalChecklistID': 'proposalChecklistId',
            'proposalChecklistName': 'proposalChecklistName',
            'proposalChecklistDescription': 'proposalChecklistDescription',
            'assignedEmployeeID': 'assignedEmployeeId',
            'orgDataID': 'orgDataId',
            'entityID': 'entityId',
            'proposalChecklistStatusID': 'proposalChecklistStatusId'
         };

         for (const [field, dataAttr] of Object.entries(fieldMappings)) {
            const input = form.querySelector(`input[name="${field}"]`);
            if (input) {
               input.value = data[dataAttr] || '';
            }
         }

         // Handle tinyMCE editor
         if (typeof tinymce !== 'undefined') {
            tinymce.init({ selector: '#proposalChecklistDescription' });
            const editor = tinymce.get('proposalChecklistDescription');
            if (editor) {
               setTimeout(() => {
                  editor.setContent(data.proposalChecklistDescription || '');
               }, 100);
            }
         }

         const selects = ['proposalChecklistStatusID', 'assignedEmployeeID'];
         selects.forEach(selectName => {
            const select = form.querySelector(`[name="${selectName}"]`);
            if (select && data[fieldMappings[selectName]]) {
               select.value = data[fieldMappings[selectName]];
            }
         });
      });
   });

   // ===== ADD CHECKLIST ITEM ASSIGNMENT =====
   document.querySelectorAll('.addChecklistItemAssignmentBtn').forEach(button => {
      button.addEventListener('click', function() {
         const form = document.getElementById('proposalChecklistItemAssignmentForm');
         if (!form) return;

         const data = this.dataset;

         const fieldMappings = {
            'proposalChecklistID': 'proposalChecklistId',
            'orgDataID': 'orgDataId',
            'entityID': 'entityId',
            'proposalID': 'proposalId',
            'proposalChecklistDeadlineDate': 'proposalChecklistDeadlineDate',
         };

         const proposalChecklistItemAssignmentDueDate = form.querySelector('#proposalChecklistItemAssignmentDueDate');
         if (proposalChecklistItemAssignmentDueDate) {
            let parentDiv = proposalChecklistItemAssignmentDueDate.parentElement;
            const label = parentDiv.querySelector('label');
            if (label) {
               label.classList.add('d-block', 'text-primary');
               // Remove existing deadline span if present
               const existingSpan = label.querySelector('.deadline-info');
               if (existingSpan) existingSpan.remove();

               const dueDateSpan = document.createElement('span');
               dueDateSpan.innerHTML = ` Checklist Deadline: ${data.proposalChecklistDeadlineDate} `;
               dueDateSpan.classList.add('float-end', 'text-danger', 'fs-12', 'deadline-info');
               label.appendChild(dueDateSpan);
            }
         }

         // Set checklist deadline for Flatpickr validation
         if (proposalChecklistItemAssignmentDueDate && data.proposalChecklistDeadlineDate) {
            proposalChecklistItemAssignmentDueDate.setAttribute('data-checklist-deadline', data.proposalChecklistDeadlineDate);

            if (typeof window.initProposalDatePickers !== 'undefined' && window.initProposalDatePickers.checklistItemDueDate) {
               if (proposalChecklistItemAssignmentDueDate._flatpickr) {
                  proposalChecklistItemAssignmentDueDate._flatpickr.destroy();
               }
               setTimeout(() => {
                  window.initProposalDatePickers.checklistItemDueDate();
               }, 100);
            }
         }

         for (const [field, dataAttr] of Object.entries(fieldMappings)) {
            const input = form.querySelector(`input[name="${field}"]`);
            if (input) {
               input.value = data[dataAttr] || '';
               input.readOnly = true;
            }
         }
      });
   });

   // ===== EDIT CHECKLIST ITEM =====
   document.querySelectorAll('.editChecklistItemBtn').forEach(button => {
      button.addEventListener('click', function() {
         let form = document.querySelector('#proposalChecklistItemAssignmentForm');
         if(!form) return;

         const data = this.dataset;

         const fieldMappings = {
            'proposalChecklistItemAssignmentID': 'proposalChecklistItemAssignmentId',
            'proposalChecklistID': 'proposalChecklistId',
            'proposalChecklistItemCategoryID': 'proposalChecklistItemCategoryId',
            'proposalChecklistItemID': 'proposalChecklistItemId',
            'proposalChecklistItemAssignmentDueDate': 'proposalChecklistItemAssignmentDueDate',
            'proposalChecklistItemAssignmentDescription': 'proposalChecklistItemAssignmentDescription',
            'proposalChecklistItemAssignmentStatusID': 'proposalChecklistItemAssignmentStatusId',
            'checklistItemAssignedEmployeeID': 'checklistItemAssignedEmployeeId',
            'proposalChecklistAssignorID': 'proposalChecklistAssignorId',
            'orgDataID': 'orgDataId',
            'entityID': 'entityId',
            'proposalID': 'proposalId',
            'proposalChecklistAssignmentDocument': 'proposalChecklistAssignmentDocument',
            'proposalChecklistTemplate': 'proposalChecklistTemplate'
         };

         for (const [field, dataAttr] of Object.entries(fieldMappings)) {
            const input = form.querySelector(`input[name="${field}"]`);
            if(input) {
               input.value = data[dataAttr] || '';
               if(input.type === 'file') {
                  input.parentElement.disabled = true;
                  input.parentElement.classList.add('bg-light-blue', 'border', 'border-primary-subtle', 'px-2', 'd-none');
               }
            }
         }

         if (typeof tinymce !== 'undefined') {
            tinymce.init({ selector: '#proposalChecklistItemAssignmentDescription' });
            const editor = tinymce.get('proposalChecklistItemAssignmentDescription');
            if (editor) {
               setTimeout(() => {
                  editor.setContent(data.proposalChecklistItemAssignmentDescription || '');
               }, 100);
            }
         }

         const selects = ['checklistItemAssignedEmployeeID', 'proposalChecklistItemID','proposalChecklistItemCategoryID', 'proposalChecklistItemAssignmentStatusID'];
         selects.forEach(selectName => {
            const select = form.querySelector(`[name="${selectName}"]`);
            if (select && data[fieldMappings[selectName]]) {
               select.value = data[fieldMappings[selectName]];
            }
         });
      });
   });
});
</script>