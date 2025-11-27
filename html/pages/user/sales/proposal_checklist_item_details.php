<?php 
if(!$isValidUser) {
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}

$checklistItemAssignmentID = (isset($_GET['checkListItemAssignmentID']) && !empty($_GET['checkListItemAssignmentID'])) ? Utility::clean_string($_GET['checkListItemAssignmentID']) : null;

if(!$checklistItemAssignmentID) {
  Alert::error("Checklist Item Assignment ID is required", true, array('fst-italic', 'text-center', 'font-18'));
  return;
}

// Get the checklist item assignment details
$checklistItem = Proposal::proposal_checklist_item_assignment_full(array('proposalChecklistItemAssignmentID'=>$checklistItemAssignmentID), true, $DBConn);

if(!$checklistItem) {
  Alert::error("Checklist Item Assignment not found", true, array('fst-italic', 'text-center', 'font-18'));
  return;
}

// Get submission details for this assignment
$submissions = Proposal::proposal_checklist_item_assignment_submissions(array('proposalChecklistItemAssignmentID'=>$checklistItemAssignmentID), false, $DBConn);

// Get proposal details
$proposalDetails = Sales::proposal_full(array('proposalID'=>$checklistItem->proposalID), true, $DBConn);

$getString.= "&checkListItemAssignmentID={$checklistItemAssignmentID}";
?>

<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb border-bottom">
   <h1 class="page-title fw-medium fs-24 mb-0"><?= "{$checklistItem->proposalChecklistItemName} - Assignment Details" ?> </h1>
   <div class="ms-md-1 ms-0">     
      <div class="ms-md-1 ms-0 row">
         <div class="d-flex align-items-center border rounded-3 p-2 border-primary">         
            <div class="rounded-circle d-flex justify-content-center align-items-center " style="width: 30px; height: 30px; background-color: #007bff;">
               <span class="text-white">
                  <?= isset($checklistItem->checklistItemAssignedEmployeeName) ? substr($checklistItem->checklistItemAssignedEmployeeName, 0, 2) : "NA" ?>
               </span>
            </div>
            <div class="ms-2 me-4 " >
               <span class="text-primary"> Assigned to: </span><br/> 
               <?=  isset($checklistItem->checklistItemAssignedEmployeeName) ? $checklistItem->checklistItemAssignedEmployeeName : "Not Assigned" ?>
            </div>
         </div>
      </div>
   </div> 
</div>

<div class="container-fluid">
   <!-- Assignment Overview Card -->
   <div class="card card-body col-md-12 my-4 shadow-lg">
      <div class="card alert fade show border-0" role="alert">
         <div class="row">
            <div class="d-flex align-items-center justify-content-between mb-4 border-bottom">
               <h4 class="mb-0 t300 font-22">Assignment Overview</h4>
               <div class="border-end">							
                  <div class="font-22">
                     <span class="badge rounded-pill <?php 
                        switch($checklistItem->proposalChecklistItemAssignmentStatusID) {
                           case 1: echo 'bg-info'; break;
                           case 2: echo 'bg-danger'; break;
                           case 3: echo 'bg-warning'; break;
                           case 4: echo 'bg-success'; break;
                           default: echo 'bg-secondary';
                        }
                     ?>">
                        <?= $checklistItem->proposalChecklistStatusItemName ?>
                     </span>
                     <span class="badge rounded-pill bg-primary-transparent ms-2">
                        Due: <?= Utility::date_format($checklistItem->proposalChecklistItemAssignmentDueDate) ?>
                     </span>
                  </div>                  
               </div>
            </div>
            
            <div class="col-md-12">
               <div class="row">
                  <div class="col-md-6">
                     <div class="card bg-light">
                        <div class="card-body">
                           <h6 class="card-title text-primary">Assignment Details</h6>
                           <p class="card-text">
                              <strong>Item Name:</strong> <?= $checklistItem->proposalChecklistItemName ?><br/>
                              <strong>Category:</strong> <?= $checklistItem->proposalChecklistItemCategoryName ?><br/>
                              <strong>Description:</strong> <?= $checklistItem->proposalChecklistItemAssignmentDescription ?><br/>
                              <strong>Assigned Date:</strong> <?= Utility::date_format($checklistItem->DateAdded) ?><br/>
                              <strong>Due Date:</strong> <?= Utility::date_format($checklistItem->proposalChecklistItemAssignmentDueDate) ?>
                           </p>
                        </div>
                     </div>
                  </div>
                  <div class="col-md-6">
                     <div class="card bg-light">
                        <div class="card-body">
                           <h6 class="card-title text-primary">Related Information</h6>
                           <p class="card-text">
                              <strong>Proposal:</strong> <?= $proposalDetails->proposalTitle ?><br/>
                              <strong>Checklist:</strong> <?= $checklistItem->proposalChecklistName ?><br/>
                              <strong>Assigned By:</strong> <?= $checklistItem->proposalChecklistAssignorName ?><br/>
                              <strong>Last Updated:</strong> <?= Utility::date_format($checklistItem->LastUpdate) ?><br/>
                              <strong>Updated By:</strong> <?= $checklistItem->LastUpdatedByName ?>
                           </p>
                        </div>
                     </div>
                  </div>
               </div>

               <!-- Template Document Section -->
               <?php if(!empty($checklistItem->proposalChecklistAssignmentDocument) || !empty($checklistItem->proposalChecklistTemplate)): ?>
               <div class="row mt-3">
                  <div class="col-md-12">
                     <div class="card bg-light">
                        <div class="card-body">
                           <h6 class="card-title text-primary">Template Documents</h6>
                           <div class="row">
                              <?php if(!empty($checklistItem->proposalChecklistAssignmentDocument)): ?>
                              <div class="col-md-6">
                                 <a href="<?= $config['DataDir'] ?><?= $checklistItem->proposalChecklistAssignmentDocument ?>" 
                                    target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="ri-file-download-line"></i> Assignment Document
                                 </a>
                              </div>
                              <?php endif; ?>
                              <?php if(!empty($checklistItem->proposalChecklistTemplate)): ?>
                              <div class="col-md-6">
                                 <a href="<?= $config['DataDir'] ?><?= $checklistItem->proposalChecklistTemplate ?>" 
                                    target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="ri-file-download-line"></i> Template Document
                                 </a>
                              </div>
                              <?php endif; ?>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <?php endif; ?>
            </div>
         </div>
      </div>
   </div>

   <!-- Submissions Section -->
   <div class="card card-body col-md-12 my-4 shadow-lg">
      <div class="card alert fade show border-0" role="alert">
         <div class="row">
            <div class="d-flex align-items-center justify-content-between mb-4 border-bottom">
               <h4 class="mb-0 t300 font-22">Submissions</h4>
               <div class="border-end">							
                  <div class="font-22">
                     <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#submitAssignmentModal">
                        <i class="ri-add-line"></i> Submit Assignment
                     </button>
                  </div>                  
               </div>
            </div>
            
            <div class="col-md-12">
               <?php if($submissions && count($submissions) > 0): ?>
                  <?php foreach($submissions as $index => $submission): ?>
                  <div class="submission-card card border-0 shadow-sm mb-4" data-submission-id="<?= $submission->proposalChecklistItemAssignmentSubmissionID ?>">
                     <div class="card-header bg-gradient-primary text-white border-0">
                        <div class="d-flex justify-content-between align-items-center">
                           <div class="d-flex align-items-center">
                              <div class="submission-icon me-3">
                                 <i class="ri-file-text-line fs-4"></i>
                              </div>
                              <div>
                                 <h5 class="mb-0 fw-semibold">Submission #<?= ($index + 1) ?></h5>
                                 <small class="opacity-75">ID: <?= $submission->proposalChecklistItemAssignmentSubmissionID ?></small>
                              </div>
                           </div>
                           <div class="submission-status">
                              <span class="badge rounded-pill fs-6 px-3 py-2 <?php 
                                 switch($submission->proposalChecklistItemAssignmentSubmissionStatusID) {
                                    case 1: echo 'bg-light text-info border border-info'; break;
                                    case 2: echo 'bg-light text-success border border-success'; break;
                                    case 3: echo 'bg-light text-warning border border-warning'; break;
                                    case 4: echo 'bg-light text-danger border border-danger'; break;
                                    default: echo 'bg-light text-secondary border border-secondary';
                                 }
                              ?>">
                                 <i class="ri-<?php 
                                    switch($submission->proposalChecklistItemAssignmentSubmissionStatusID) {
                                       case 1: echo 'draft-line'; break;
                                       case 2: echo 'check-line'; break;
                                       case 3: echo 'eye-line'; break;
                                       case 4: echo 'close-line'; break;
                                       default: echo 'question-line';
                                    }
                                 ?> me-1"></i>
                                 <?= $submission->proposalChecklistItemAssignmentStatusName ?>
                              </span>
                           </div>
                        </div>
                     </div>
                     
                     <div class="card-body p-4">
                        <div class="row">
                           <!-- Left Column - Main Content -->
                           <div class="col-lg-8">
                              <!-- Submission Description -->
                              <?php if(!empty($submission->proposalChecklistItemAssignmentSubmissionDescription)): ?>
                              <div class="submission-description mb-4">
                                 <h6 class="text-primary mb-2">
                                    <i class="ri-file-text-line me-2"></i>Description
                                 </h6>
                                 <div class="description-content p-3 bg-light rounded border-start border-4 border-primary">
                                    <?= nl2br(htmlspecialchars($submission->proposalChecklistItemAssignmentSubmissionDescription)) ?>
                                 </div>
                              </div>
                              <?php endif; ?>
                              
                              <!-- File Attachments -->
                              <?php if(!empty($submission->proposalChecklistItemUploadfiles)): ?>
                              <div class="file-attachments">
                                 <h6 class="text-primary mb-3">
                                    <i class="ri-attachment-line me-2"></i>Attached Files
                                 </h6>
                                 <div class="file-list">
                                    <?php 
                                    $files = explode(',', $submission->proposalChecklistItemUploadfiles);
                                    foreach($files as $file): 
                                       $fileName = basename($file);
                                       $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                    ?>
                                    <div class="file-item d-flex align-items-center p-3 mb-2 bg-light rounded border">
                                       <div class="file-icon me-3">
                                          <?php
                                          switch($fileExtension) {
                                             case 'pdf': echo '<i class="ri-file-pdf-line fs-3 text-danger"></i>'; break;
                                             case 'doc':
                                             case 'docx': echo '<i class="ri-file-word-line fs-3 text-primary"></i>'; break;
                                             case 'xls':
                                             case 'xlsx': echo '<i class="ri-file-excel-line fs-3 text-success"></i>'; break;
                                             case 'jpg':
                                             case 'jpeg':
                                             case 'png': echo '<i class="ri-image-line fs-3 text-info"></i>'; break;
                                             default: echo '<i class="ri-file-line fs-3 text-secondary"></i>';
                                          }
                                          ?>
                                       </div>
                                       <div class="file-info flex-grow-1">
                                          <div class="file-name fw-medium"><?= htmlspecialchars($fileName) ?></div>
                                          <small class="text-muted"><?= strtoupper($fileExtension) ?> File</small>
                                       </div>
                                       <div class="file-actions">
                                          <a href="<?= $config['DataDir'] ?><?= trim($file) ?>" 
                                             target="_blank" 
                                             class="btn btn-outline-primary btn-sm">
                                             <i class="ri-download-line me-1"></i>Download
                                          </a>
                                       </div>
                                    </div>
                                    <?php endforeach; ?>
                                 </div>
                              </div>
                              <?php endif; ?>
                           </div>
                           
                           <!-- Right Column - Metadata -->
                           <div class="col-lg-4">
                              <div class="submission-metadata">
                                 <h6 class="text-primary mb-3">
                                    <i class="ri-information-line me-2"></i>Submission Details
                                 </h6>
                                 
                                 <div class="metadata-item mb-3">
                                    <div class="metadata-label">
                                       <i class="ri-calendar-line me-2 text-muted"></i>
                                       <strong>Submitted Date</strong>
                                    </div>
                                    <div class="metadata-value text-dark">
                                       <?= Utility::date_format($submission->proposalChecklistItemAssignmentSubmissionDate) ?>
                                    </div>
                                 </div>
                                 
                                 <div class="metadata-item mb-3">
                                    <div class="metadata-label">
                                       <i class="ri-time-line me-2 text-muted"></i>
                                       <strong>Last Updated</strong>
                                    </div>
                                    <div class="metadata-value text-dark">
                                       <?= Utility::date_format($submission->LastUpdate) ?>
                                    </div>
                                 </div>
                                 
                                 <div class="metadata-item mb-3">
                                    <div class="metadata-label">
                                       <i class="ri-user-line me-2 text-muted"></i>
                                       <strong>Updated By</strong>
                                    </div>
                                    <div class="metadata-value text-dark">
                                       <?= $submission->LastUpdatedByName ?>
                                    </div>
                                 </div>
                                 
                                 <!-- Quick Actions -->
                                 <div class="quick-actions mt-4">
                                    <h6 class="text-primary mb-3">
                                       <i class="ri-flashlight-line me-2"></i>Quick Actions
                                    </h6>
                                    <div class="d-grid gap-2">
                                       <button class="btn btn-outline-primary btn-sm" 
                                               onclick="copySubmissionDetails(<?= $submission->proposalChecklistItemAssignmentSubmissionID ?>)">
                                          <i class="ri-copy-line me-1"></i>Copy Details
                                       </button>
                                       <button class="btn btn-outline-secondary btn-sm" 
                                               onclick="printSubmission(<?= $submission->proposalChecklistItemAssignmentSubmissionID ?>)">
                                          <i class="ri-printer-line me-1"></i>Print
                                       </button>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <?php endforeach; ?>
               <?php else: ?>
                  <div class="empty-state text-center py-5">
                     <div class="empty-state-icon mb-4">
                        <i class="ri-inbox-line fs-1 text-muted"></i>
                     </div>
                     <h4 class="text-muted mb-3">No Submissions Yet</h4>
                     <p class="text-muted mb-4">This assignment hasn't been submitted yet. Click the button below to make your first submission.</p>
                     <div class="d-flex justify-content-center gap-3">
                        <button type="button" class="btn btn-primary btn-lg px-4" data-bs-toggle="modal" data-bs-target="#submitAssignmentModal">
                           <i class="ri-add-line me-2"></i>Submit Assignment
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-lg px-4" onclick="showToast('info', 'Help', 'Use the Submit Assignment button to upload your work and provide a description.')">
                           <i class="ri-question-line me-2"></i>Need Help?
                        </button>
                     </div>
                  </div>
               <?php endif; ?>
            </div>
         </div>
      </div>
   </div>

   <!-- Progress Timeline -->
   <div class="card card-body col-md-12 my-4 shadow-lg">
      <div class="card alert fade show border-0" role="alert">
         <div class="row">
            <div class="d-flex align-items-center justify-content-between mb-4 border-bottom">
               <h4 class="mb-0 t300 font-22">Progress Timeline</h4>
            </div>
            
            <div class="col-md-12">
               <div class="timeline">
                  <!-- Assignment Created -->
                  <div class="timeline-item">
                     <div class="timeline-marker bg-primary"></div>
                     <div class="timeline-content">
                        <h6 class="timeline-title">Assignment Created</h6>
                        <p class="timeline-text">
                           Assignment was created and assigned to <?= $checklistItem->checklistItemAssignedEmployeeName ?>
                        </p>
                        <small class="text-muted"><?= Utility::date_format($checklistItem->DateAdded) ?></small>
                     </div>
                  </div>

                  <!-- Submissions -->
                  <?php if($submissions && count($submissions) > 0): ?>
                     <?php foreach($submissions as $index => $submission): ?>
                     <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                           <h6 class="timeline-title">Submission #<?= ($index + 1) ?></h6>
                           <p class="timeline-text">
                              Assignment was submitted with status: <?= $submission->proposalChecklistItemAssignmentStatusName ?>
                              <?php if(!empty($submission->proposalChecklistItemAssignmentSubmissionDescription)): ?>
                              <br/><small><?= $submission->proposalChecklistItemAssignmentSubmissionDescription ?></small>
                              <?php endif; ?>
                           </p>
                           <small class="text-muted"><?= Utility::date_format($submission->proposalChecklistItemAssignmentSubmissionDate) ?></small>
                        </div>
                     </div>
                     <?php endforeach; ?>
                  <?php endif; ?>

                  <!-- Due Date -->
                  <div class="timeline-item <?php 
                     $dueDate = new DateTime($checklistItem->proposalChecklistItemAssignmentDueDate);
                     $now = new DateTime();
                     echo ($dueDate < $now) ? 'timeline-item-overdue' : '';
                  ?>">
                     <div class="timeline-marker <?php 
                        $dueDate = new DateTime($checklistItem->proposalChecklistItemAssignmentDueDate);
                        $now = new DateTime();
                        echo ($dueDate < $now) ? 'bg-danger' : 'bg-warning';
                     ?>"></div>
                     <div class="timeline-content">
                        <h6 class="timeline-title">Due Date</h6>
                        <p class="timeline-text">
                           Assignment is due on <?= Utility::date_format($checklistItem->proposalChecklistItemAssignmentDueDate) ?>
                           <?php 
                              $dueDate = new DateTime($checklistItem->proposalChecklistItemAssignmentDueDate);
                              $now = new DateTime();
                              if($dueDate < $now): ?>
                              <br/><span class="text-danger"><i class="ri-error-warning-line"></i> Overdue</span>
                           <?php endif; ?>
                        </p>
                        <small class="text-muted"><?= Utility::date_format($checklistItem->proposalChecklistItemAssignmentDueDate) ?></small>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<!-- Toast Notification Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
   <div id="notificationToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header">
         <i id="toastIcon" class="ri-check-line me-2"></i>
         <strong id="toastTitle" class="me-auto">Notification</strong>
         <small class="text-muted">just now</small>
         <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div id="toastBody" class="toast-body">
         This is a notification message.
      </div>
   </div>
</div>

<!-- Submit Assignment Modal -->
<div class="modal fade" id="submitAssignmentModal" tabindex="-1" aria-labelledby="submitAssignmentModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="submitAssignmentModalLabel">Submit Assignment</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body">
            <!-- Inline Alert Container -->
            <div id="formAlertContainer" class="mb-3" style="display: none;">
               <div id="formAlert" class="alert alert-dismissible fade show" role="alert">
                  <i id="formAlertIcon" class="ri-information-line me-2"></i>
                  <span id="formAlertMessage">Message</span>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
               </div>
            </div>
            
            <form id="submitAssignmentForm" action="<?= $base ?>php/scripts/sales/manage_proposal_checklist_submission.php" method="POST" enctype="multipart/form-data">
               <input type="hidden" name="proposalChecklistItemAssignmentID" value="<?= $checklistItem->proposalChecklistItemAssignmentID ?>">
               <input type="hidden" name="orgDataID" value="<?= $checklistItem->orgDataID ?>">
               <input type="hidden" name="entityID" value="<?= $checklistItem->entityID ?>">
               
               <div class="mb-3">
                  <label for="submissionDescription" class="form-label">Submission Description</label>
                  <textarea class="form-control" id="submissionDescription" name="proposalChecklistItemAssignmentSubmissionDescription" rows="4" placeholder="Describe your submission..."></textarea>
               </div>
               
               <div class="mb-3">
                  <label for="submissionFiles" class="form-label">Upload Files</label>
                  <input type="file" class="form-control" id="submissionFiles" name="proposalChecklistItemUploadfiles[]" multiple>
                  <div class="form-text">You can upload multiple files. Supported formats: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG</div>
               </div>
               
               <div class="mb-3">
                  <label for="submissionStatus" class="form-label">Submission Status</label>
                  <select class="form-control" id="submissionStatus" name="proposalChecklistItemAssignmentSubmissionStatusID" required>
                     <option value="">Select Status</option>
                     <option value="1">Draft</option>
                     <option value="2">Submitted</option>
                     <option value="3">Under Review</option>
                     <option value="4">Rejected</option>
                  </select>
               </div>
            </form>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" form="submitAssignmentForm" class="btn btn-primary">Submit Assignment</button>
         </div>
      </div>
   </div>
</div>

<style>
.timeline {
   position: relative;
   padding-left: 30px;
}

.timeline::before {
   content: '';
   position: absolute;
   left: 15px;
   top: 0;
   bottom: 0;
   width: 2px;
   background: #dee2e6;
}

.timeline-item {
   position: relative;
   margin-bottom: 30px;
}

.timeline-marker {
   position: absolute;
   left: -22px;
   top: 5px;
   width: 12px;
   height: 12px;
   border-radius: 50%;
   border: 3px solid #fff;
   box-shadow: 0 0 0 3px #dee2e6;
}

.timeline-content {
   background: #f8f9fa;
   padding: 15px;
   border-radius: 8px;
   border-left: 4px solid #007bff;
}

.timeline-title {
   margin-bottom: 5px;
   font-weight: 600;
}

.timeline-text {
   margin-bottom: 5px;
   color: #6c757d;
}

.timeline-item-overdue .timeline-content {
   border-left-color: #dc3545;
   background: #fff5f5;
}

/* Ensure modal form inputs are properly styled and accessible */
#submitAssignmentModal .form-control {
   pointer-events: auto !important;
   user-select: text !important;
   -webkit-user-select: text !important;
   -moz-user-select: text !important;
   -ms-user-select: text !important;
}

#submitAssignmentModal .form-control:focus {
   border-color: #007bff;
   box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

#submitAssignmentModal textarea {
   resize: vertical;
   min-height: 80px;
}

/* Toast Notification Styles */
.toast {
   background-color: #fff;
   border: 1px solid rgba(0,0,0,.1);
   box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15);
}

.toast.success {
   border-left: 4px solid #28a745;
}

.toast.error {
   border-left: 4px solid #dc3545;
}

.toast.warning {
   border-left: 4px solid #ffc107;
}

.toast.info {
   border-left: 4px solid #17a2b8;
}

.toast-header .ri-check-line {
   color: #28a745;
}

.toast-header .ri-error-warning-line {
   color: #dc3545;
}

.toast-header .ri-alert-line {
   color: #ffc107;
}

.toast-header .ri-information-line {
   color: #17a2b8;
}

/* Loading spinner animation */
@keyframes spin {
   0% { transform: rotate(0deg); }
   100% { transform: rotate(360deg); }
}

.spin {
   animation: spin 1s linear infinite;
}

/* Enhanced Submission Cards */
.submission-card {
   transition: all 0.3s ease;
   border-radius: 12px;
   overflow: hidden;
}

.submission-card:hover {
   transform: translateY(-2px);
   box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.submission-card .card-header {
   background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
   padding: 1.5rem;
}

.submission-icon {
   width: 50px;
   height: 50px;
   background: rgba(255,255,255,0.2);
   border-radius: 50%;
   display: flex;
   align-items: center;
   justify-content: center;
   backdrop-filter: blur(10px);
}

.submission-status .badge {
   font-size: 0.9rem;
   font-weight: 500;
   backdrop-filter: blur(10px);
}

/* File Attachments Styling */
.file-item {
   transition: all 0.2s ease;
   border-radius: 8px;
}

.file-item:hover {
   background-color: #f8f9fa !important;
   border-color: #007bff !important;
   transform: translateX(4px);
}

.file-icon {
   min-width: 40px;
   display: flex;
   align-items: center;
   justify-content: center;
}

.file-name {
   color: #333;
   margin-bottom: 2px;
}

/* Metadata Styling */
.metadata-item {
   padding: 0.75rem;
   background: #f8f9fa;
   border-radius: 8px;
   border-left: 3px solid #007bff;
}

.metadata-label {
   color: #6c757d;
   font-size: 0.85rem;
   margin-bottom: 0.25rem;
}

.metadata-value {
   font-size: 0.95rem;
   font-weight: 500;
}

/* Description Content */
.description-content {
   line-height: 1.6;
   font-size: 0.95rem;
   max-height: 200px;
   overflow-y: auto;
}

/* Quick Actions */
.quick-actions .btn {
   font-size: 0.85rem;
   padding: 0.5rem 1rem;
   border-radius: 6px;
   transition: all 0.2s ease;
}

.quick-actions .btn:hover {
   transform: translateY(-1px);
   box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Status Badge Enhancements */
.badge {
   position: relative;
   overflow: hidden;
}

.badge::before {
   content: '';
   position: absolute;
   top: 0;
   left: -100%;
   width: 100%;
   height: 100%;
   background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
   transition: left 0.5s;
}

.badge:hover::before {
   left: 100%;
}

/* Responsive Design */
@media (max-width: 768px) {
   .submission-card .card-header {
      padding: 1rem;
   }
   
   .submission-icon {
      width: 40px;
      height: 40px;
   }
   
   .file-item {
      flex-direction: column;
      text-align: center;
   }
   
   .file-actions {
      margin-top: 0.5rem;
   }
   
   .metadata-item {
      margin-bottom: 1rem;
   }
}

/* Empty State Styling */
.empty-state {
   background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
   border-radius: 12px;
   border: 2px dashed #dee2e6;
   margin: 2rem 0;
}

.empty-state-icon {
   width: 80px;
   height: 80px;
   background: rgba(108, 117, 125, 0.1);
   border-radius: 50%;
   display: flex;
   align-items: center;
   justify-content: center;
   margin: 0 auto;
}

.empty-state .btn {
   border-radius: 8px;
   font-weight: 500;
   transition: all 0.3s ease;
}

.empty-state .btn:hover {
   transform: translateY(-2px);
   box-shadow: 0 6px 20px rgba(0,0,0,0.15);
}
</style>

<script>
// Toast Notification Function
function showToast(type, title, message) {
   const toast = document.getElementById('notificationToast');
   const toastIcon = document.getElementById('toastIcon');
   const toastTitle = document.getElementById('toastTitle');
   const toastBody = document.getElementById('toastBody');
   
   // Remove existing classes
   toast.className = 'toast';
   
   // Set type-specific styling and content
   switch(type) {
      case 'success':
         toast.classList.add('success');
         toastIcon.className = 'ri-check-line me-2';
         toastIcon.style.color = '#28a745';
         break;
      case 'error':
         toast.classList.add('error');
         toastIcon.className = 'ri-error-warning-line me-2';
         toastIcon.style.color = '#dc3545';
         break;
      case 'warning':
         toast.classList.add('warning');
         toastIcon.className = 'ri-alert-line me-2';
         toastIcon.style.color = '#ffc107';
         break;
      case 'info':
         toast.classList.add('info');
         toastIcon.className = 'ri-information-line me-2';
         toastIcon.style.color = '#17a2b8';
         break;
   }
   
   // Set content
   toastTitle.textContent = title;
   toastBody.textContent = message;
   
   // Show toast
   const bsToast = new bootstrap.Toast(toast, {
      autohide: true,
      delay: type === 'error' ? 8000 : 5000 // Show errors longer
   });
   
   bsToast.show();
}

// Inline Form Alert Function
function showFormAlert(type, message) {
   const alertContainer = document.getElementById('formAlertContainer');
   const alert = document.getElementById('formAlert');
   const alertIcon = document.getElementById('formAlertIcon');
   const alertMessage = document.getElementById('formAlertMessage');
   
   // Remove existing classes
   alert.className = 'alert alert-dismissible fade show';
   
   // Set type-specific styling
   switch(type) {
      case 'success':
         alert.classList.add('alert-success');
         alertIcon.className = 'ri-check-line me-2';
         break;
      case 'error':
         alert.classList.add('alert-danger');
         alertIcon.className = 'ri-error-warning-line me-2';
         break;
      case 'warning':
         alert.classList.add('alert-warning');
         alertIcon.className = 'ri-alert-line me-2';
         break;
      case 'info':
         alert.classList.add('alert-info');
         alertIcon.className = 'ri-information-line me-2';
         break;
   }
   
   alertMessage.textContent = message;
   alertContainer.style.display = 'block';
   
   // Auto-hide after 5 seconds
   setTimeout(() => {
      alertContainer.style.display = 'none';
   }, 5000);
}

// Form Validation Function
function validateForm(formData) {
   const errors = [];
   
   // Check required fields
   if (!formData.get('proposalChecklistItemAssignmentSubmissionStatusID')) {
      errors.push('Please select a submission status');
   }
   
   if (!formData.get('proposalChecklistItemAssignmentSubmissionDescription')) {
      errors.push('Please provide a submission description');
   }
   
   return errors;
}

// Quick Actions Functions
function copySubmissionDetails(submissionId) {
   const submissionCard = document.querySelector(`[data-submission-id="${submissionId}"]`);
   if (!submissionCard) return;
   
   const title = submissionCard.querySelector('h5').textContent;
   const status = submissionCard.querySelector('.badge').textContent.trim();
   const description = submissionCard.querySelector('.description-content')?.textContent.trim() || 'No description provided';
   const submittedDate = submissionCard.querySelector('.metadata-item .metadata-value').textContent.trim();
   
   const copyText = `Submission Details:
${title}
Status: ${status}
Submitted: ${submittedDate}
Description: ${description}`;
   
   navigator.clipboard.writeText(copyText).then(() => {
      showToast('success', 'Copied to Clipboard', 'Submission details have been copied to your clipboard.');
   }).catch(() => {
      // Fallback for older browsers
      const textArea = document.createElement('textarea');
      textArea.value = copyText;
      document.body.appendChild(textArea);
      textArea.select();
      document.execCommand('copy');
      document.body.removeChild(textArea);
      showToast('success', 'Copied to Clipboard', 'Submission details have been copied to your clipboard.');
   });
}

function printSubmission(submissionId) {
   const submissionCard = document.querySelector(`[data-submission-id="${submissionId}"]`);
   if (!submissionCard) return;
   
   // Create a new window for printing
   const printWindow = window.open('', '_blank');
   const title = submissionCard.querySelector('h5').textContent;
   const status = submissionCard.querySelector('.badge').textContent.trim();
   const description = submissionCard.querySelector('.description-content')?.innerHTML || 'No description provided';
   const submittedDate = submissionCard.querySelector('.metadata-item .metadata-value').textContent.trim();
   
   printWindow.document.write(`
      <!DOCTYPE html>
      <html>
      <head>
         <title>${title} - Print</title>
         <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { border-bottom: 2px solid #007bff; padding-bottom: 10px; margin-bottom: 20px; }
            .status { display: inline-block; background: #007bff; color: white; padding: 5px 10px; border-radius: 15px; margin: 10px 0; }
            .description { background: #f8f9fa; padding: 15px; border-left: 4px solid #007bff; margin: 15px 0; }
            .metadata { margin: 10px 0; }
            .label { font-weight: bold; color: #666; }
         </style>
      </head>
      <body>
         <div class="header">
            <h1>${title}</h1>
            <div class="status">${status}</div>
         </div>
         <div class="metadata">
            <p><span class="label">Submitted Date:</span> ${submittedDate}</p>
         </div>
         <div class="description">
            <h3>Description:</h3>
            ${description}
         </div>
         <p><small>Printed on ${new Date().toLocaleString()}</small></p>
      </body>
      </html>
   `);
   
   printWindow.document.close();
   printWindow.focus();
   printWindow.print();
   printWindow.close();
   
   showToast('info', 'Print Dialog Opened', 'The print dialog has been opened for this submission.');
}

document.addEventListener("DOMContentLoaded", function() {
   // Fix modal input focus issues
   const submitModal = document.getElementById('submitAssignmentModal');
   if (submitModal) {
      submitModal.addEventListener('shown.bs.modal', function () {
         // Focus on the first input field when modal is fully shown
         setTimeout(function() {
            const firstInput = submitModal.querySelector('textarea, input[type="text"], input[type="file"], select');
            if (firstInput) {
               firstInput.focus();
               firstInput.click(); // Ensure it's clickable
            }
         }, 100);
      });
      
      // Ensure inputs are enabled when modal opens
      submitModal.addEventListener('show.bs.modal', function () {
         // Clear any existing alerts
         const alertContainer = document.getElementById('formAlertContainer');
         if (alertContainer) {
            alertContainer.style.display = 'none';
         }
         
         const inputs = submitModal.querySelectorAll('input, textarea, select');
         inputs.forEach(input => {
            input.disabled = false;
            input.readOnly = false;
            
            // Clone the input to remove any event listeners that might be interfering
            const newInput = input.cloneNode(true);
            input.parentNode.replaceChild(newInput, input);
            
            // Ensure the new input is properly enabled
            newInput.disabled = false;
            newInput.readOnly = false;
         });
      });
   }
   
   // Handle form submission
   const submitForm = document.getElementById('submitAssignmentForm');
   if (submitForm) {
      submitForm.addEventListener('submit', function(e) {
         e.preventDefault();
         
         // Get form data
         const formData = new FormData(this);

         console.log('Form action:', this.action);
         console.log('Form data:', formData);
         
         // Validate form
         const validationErrors = validateForm(formData);
         if (validationErrors.length > 0) {
            showFormAlert('error', validationErrors.join('. ') + '.');
            return;
         }
         
         // Debug: Log form data entries
         for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
         }
         
         // Show loading state
         const submitBtn = document.querySelector('button[type="submit"]');
         const originalText = submitBtn.innerHTML;
         submitBtn.innerHTML = '<i class="ri-loader-4-line spin"></i> Submitting...';
         submitBtn.disabled = true;
         
         // Show loading notification
         showToast('info', 'Submitting Assignment', 'Please wait while we process your submission...');
         
         // Submit via fetch
         fetch(this.action, {
            method: 'POST',
            body: formData
         })
         .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            
            if (!response.ok) {
               throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return response.text().then(text => {
               console.log('Raw response:', text);
               try {
                  return JSON.parse(text);
               } catch (e) {
                  console.error('JSON parse error:', e);
                  console.error('Response text:', text);
                  throw new Error('Invalid JSON response from server');
               }
            });
         })
         .then(data => {
            console.log('Parsed response:', data);
            if (data.success) {
               // Show success message
               showToast('success', 'Assignment submitted successfully!', 'Your assignment has been submitted and saved.');
               // Reload page to show new submission
               setTimeout(() => {
                  window.location.reload();
               }, 2000);
            } else {
               showToast('error', 'Submission Failed', data.message || 'Unknown error occurred while submitting your assignment.');
            }
         })
         .catch(error => {
            console.error('Fetch error:', error);
            showToast('error', 'Network Error', 'Unable to submit assignment. Please check your connection and try again.');
         })
         .finally(() => {
            // Reset button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
         });
      });
   }
   
   // Auto-hide actual alert messages after 5 seconds
   setTimeout(function() {
      const alerts = document.querySelectorAll('.alert-dismissible');
      alerts.forEach(alert => {
         const bsAlert = new bootstrap.Alert(alert);
         bsAlert.close();
      });
   }, 5000);
});
</script>
