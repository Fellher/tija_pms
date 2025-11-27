<?php
/**
 * Checklist Item Submission UI Component
 * Allows users to submit their assigned checklist items
 *
 * @package    Tija CRM
 * @subpackage Sales Management
 */

// Get assignments for current user
$userAssignments = Proposal::proposal_checklist_item_assignment_full(
   array('checklistItemAssignedEmployeeID' => $userDetails->ID, 'proposalID' => $proposalID, 'Suspended' => 'N'),
   false,
   $DBConn
);

// Get submissions for these assignments
$userSubmissions = array();
if ($userAssignments) {
   foreach ($userAssignments as $assignment) {
      $submissions = Proposal::proposal_checklist_submissions(
         array('proposalChecklistItemAssignmentID' => $assignment->proposalChecklistItemAssignmentID, 'Suspended' => 'N'),
         false,
         $DBConn
      );
      if ($submissions) {
         $userSubmissions[$assignment->proposalChecklistItemAssignmentID] = $submissions;
      }
   }
}

// Status colors for submissions
$submissionStatusColors = array(
   'draft' => 'secondary',
   'submitted' => 'primary',
   'approved' => 'success',
   'rejected' => 'danger',
   'revision_requested' => 'warning'
);
?>

<div class="checklist-submissions-container">
   <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0 fw-semibold">
         <i class="ri-file-upload-line me-2 text-primary"></i>
         My Checklist Item Submissions
      </h5>
   </div>

   <?php if($userAssignments && count($userAssignments) > 0): ?>
      <div class="row g-3">
         <?php foreach($userAssignments as $assignment): ?>
            <?php
            $latestSubmission = null;
            if (isset($userSubmissions[$assignment->proposalChecklistItemAssignmentID]) &&
                !empty($userSubmissions[$assignment->proposalChecklistItemAssignmentID])) {
               $latestSubmission = $userSubmissions[$assignment->proposalChecklistItemAssignmentID][0];
            }

            $isOverdue = false;
            if ($assignment->proposalChecklistItemAssignmentDueDate) {
               $dueDate = strtotime($assignment->proposalChecklistItemAssignmentDueDate);
               $isOverdue = ($dueDate < time() && (!$latestSubmission || $latestSubmission->submissionStatus !== 'approved'));
            }
            ?>
            <div class="col-md-6 col-lg-4">
               <div class="card border shadow-sm submission-card h-100 <?= $isOverdue ? 'border-danger' : '' ?>">
                  <div class="card-body">
                     <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0 fw-semibold flex-grow-1">
                           <?= htmlspecialchars($assignment->proposalChecklistItemName ?? 'Checklist Item') ?>
                           <?php if($assignment->isMandatory === 'Y'): ?>
                              <span class="badge bg-danger-transparent text-danger ms-1">
                                 <i class="ri-alert-line"></i> Mandatory
                              </span>
                           <?php endif; ?>
                        </h6>
                        <?php if($latestSubmission): ?>
                           <span class="badge bg-<?= $submissionStatusColors[$latestSubmission->submissionStatus ?? 'submitted'] ?? 'primary' ?>-transparent">
                              <?= ucfirst(str_replace('_', ' ', $latestSubmission->submissionStatus ?? 'submitted')) ?>
                           </span>
                        <?php else: ?>
                           <span class="badge bg-secondary-transparent">Not Submitted</span>
                        <?php endif; ?>
                     </div>

                     <?php if($assignment->proposalChecklistItemAssignmentDescription): ?>
                        <p class="text-muted small mb-2">
                           <?= nl2br(htmlspecialchars(substr($assignment->proposalChecklistItemAssignmentDescription, 0, 150))) ?>
                           <?= strlen($assignment->proposalChecklistItemAssignmentDescription) > 150 ? '...' : '' ?>
                        </p>
                     <?php endif; ?>

                     <div class="mb-3">
                        <small class="text-muted d-block">
                           <i class="ri-calendar-line me-1"></i>
                           Due: <?= Utility::date_format($assignment->proposalChecklistItemAssignmentDueDate) ?>
                           <?php if($isOverdue): ?>
                              <span class="badge bg-danger-transparent text-danger ms-1">Overdue</span>
                           <?php endif; ?>
                        </small>
                        <?php if($latestSubmission): ?>
                           <small class="text-muted d-block mt-1">
                              <i class="ri-time-line me-1"></i>
                              Submitted: <?= Utility::date_format($latestSubmission->submissionDate) ?>
                           </small>
                           <?php if($latestSubmission->reviewedDate): ?>
                              <small class="text-muted d-block mt-1">
                                 <i class="ri-user-check-line me-1"></i>
                                 Reviewed by: <?= htmlspecialchars($latestSubmission->reviewedByName ?? 'N/A') ?>
                              </small>
                           <?php endif; ?>
                        <?php endif; ?>
                     </div>

                     <?php if($latestSubmission && $latestSubmission->reviewNotes): ?>
                        <div class="alert alert-info small mb-2">
                           <strong>Review Notes:</strong><br>
                           <?= nl2br(htmlspecialchars($latestSubmission->reviewNotes)) ?>
                        </div>
                     <?php endif; ?>

                     <div class="d-flex gap-2">
                        <?php if(!$latestSubmission || $latestSubmission->submissionStatus === 'revision_requested'): ?>
                           <button type="button"
                                   class="btn btn-sm btn-primary flex-fill submitChecklistItemBtn"
                                   data-assignment-id="<?= $assignment->proposalChecklistItemAssignmentID ?>"
                                   data-item-name="<?= htmlspecialchars($assignment->proposalChecklistItemName ?? '') ?>">
                              <i class="ri-upload-line me-1"></i>
                              <?= $latestSubmission ? 'Resubmit' : 'Submit' ?>
                           </button>
                        <?php endif; ?>

                        <?php if($latestSubmission): ?>
                           <button type="button"
                                   class="btn btn-sm btn-outline-info viewSubmissionBtn"
                                   data-submission-id="<?= $latestSubmission->submissionID ?>">
                              <i class="ri-eye-line"></i>
                           </button>
                        <?php endif; ?>
                     </div>
                  </div>
               </div>
            </div>
         <?php endforeach; ?>
      </div>
   <?php else: ?>
      <div class="text-center py-5">
         <i class="ri-task-line fs-48 text-muted mb-3 d-block"></i>
         <p class="text-muted">No checklist items assigned to you.</p>
      </div>
   <?php endif; ?>
</div>

<!-- Submission Modal -->
<?php
echo Utility::form_modal_header("submitChecklistItemModal", "", "Submit Checklist Item", array('modal-lg', 'modal-dialog-centered'), $base);
include "includes/scripts/sales/modals/submit_checklist_item.php";
echo Utility::form_modal_footer('Submit', 'submitChecklistItem',  ' btn btn-success btn-sm', true);
?>

<script>
(function() {
   'use strict';

   // Handle submission button clicks
   document.querySelectorAll('.submitChecklistItemBtn').forEach(btn => {
      btn.addEventListener('click', function() {
         const assignmentID = this.dataset.assignmentId;
         const itemName = this.dataset.itemName;

         // Set assignment ID in modal form
         document.getElementById('submissionAssignmentID').value = assignmentID;
         document.getElementById('submissionItemName').textContent = itemName || 'Checklist Item';

         // Show modal
         const modal = new bootstrap.Modal(document.getElementById('submitChecklistItemModal'));
         modal.show();
      });
   });

   // Handle view submission button clicks
   document.querySelectorAll('.viewSubmissionBtn').forEach(btn => {
      btn.addEventListener('click', function() {
         const submissionID = this.dataset.submissionId;

         // Fetch and display submission details
         const formData = new FormData();
         formData.append('action', 'get');
         formData.append('submissionID', submissionID);

         fetch('<?= $base ?>php/scripts/sales/manage_proposal_checklist_submission.php', {
            method: 'POST',
            body: formData
         })
         .then(response => response.json())
         .then(data => {
            if (data.success && data.data) {
               // Display submission details in a modal or alert
               const submission = data.data;
               let filesHtml = '';
               if (submission.submissionFiles) {
                  const files = JSON.parse(submission.submissionFiles);
                  filesHtml = '<div class="mt-2"><strong>Files:</strong><ul>';
                  files.forEach(file => {
                     filesHtml += `<li><a href="<?= $base ?>${file}" target="_blank">${file}</a></li>`;
                  });
                  filesHtml += '</ul></div>';
               }

               const detailsHtml = `
                  <div class="submission-details">
                     <p><strong>Status:</strong> <span class="badge bg-${getStatusColor(submission.submissionStatus)}">${submission.submissionStatus}</span></p>
                     <p><strong>Submitted:</strong> ${submission.submissionDate}</p>
                     ${submission.submissionNotes ? `<p><strong>Notes:</strong><br>${submission.submissionNotes}</p>` : ''}
                     ${submission.reviewNotes ? `<p><strong>Review Notes:</strong><br>${submission.reviewNotes}</p>` : ''}
                     ${filesHtml}
                  </div>
               `;

               // Show in modal or alert
               alert(detailsHtml.replace(/<[^>]*>/g, '')); // Simple alert for now
            }
         })
         .catch(error => {
            console.error('Error:', error);
         });
      });
   });

   function getStatusColor(status) {
      const colors = {
         'draft': 'secondary',
         'submitted': 'primary',
         'approved': 'success',
         'rejected': 'danger',
         'revision_requested': 'warning'
      };
      return colors[status] || 'secondary';
   }
})();
</script>

<style>
.submission-card {
   transition: transform 0.2s, box-shadow 0.2s;
}

.submission-card:hover {
   transform: translateY(-2px);
   box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
}

.submission-card.border-danger {
   border-width: 2px !important;
}
</style>

