<?php
/**
 * Proposal Status Tracker Component
 * Displays proposal status stages with visual progress indicator
 *
 * @package    Tija CRM
 * @subpackage Sales Management
 */

// Get proposal status stages
$statusStages = Proposal::proposal_status_stages(array('isActive' => 'Y'), false, $DBConn);

// Determine current stage using proposalStatusID if available
$currentStatusID = isset($proposalDetails->proposalStatusID) ? $proposalDetails->proposalStatusID : null;
$currentStage    = isset($proposalDetails->statusStage) ? $proposalDetails->statusStage : 'draft';
$currentStageOrder = isset($proposalDetails->statusStageOrder) ? $proposalDetails->statusStageOrder : 1;

if ($statusStages && $currentStatusID) {
   foreach ($statusStages as $stage) {
      if (isset($stage->stageID) && intval($stage->stageID) === intval($currentStatusID)) {
         $currentStage = $stage->stageCode ?? $currentStage;
         $currentStageOrder = $stage->stageOrder ?? $currentStageOrder;
         break;
      }
   }
}

// Calculate completion percentages
$completionData = Proposal::calculate_proposal_completion($proposalID, $DBConn);
$totalCompletion = $completionData['total'] ?? 0;
$mandatoryCompletion = $completionData['mandatory'] ?? 0;
?>

<!-- Proposal Status Tracker -->
<div class="card border-0 shadow-sm mb-4">
   <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
      <h5 class="mb-0 fw-semibold">
         <i class="ri-bar-chart-line me-2 text-primary"></i>
         Proposal Status & Progress
      </h5>
      <div class="d-flex align-items-center gap-3">
         <!-- Completion Percentages -->
         <div class="text-end">
            <div class="small text-muted">Total Completion</div>
            <div class="h5 mb-0 fw-bold text-primary"><?= number_format($totalCompletion, 1) ?>%</div>
         </div>
         <div class="text-end">
            <div class="small text-muted">Mandatory</div>
            <div class="h5 mb-0 fw-bold <?= $mandatoryCompletion < 100 ? 'text-warning' : 'text-success' ?>">
               <?= number_format($mandatoryCompletion, 1) ?>%
            </div>
         </div>
      </div>
   </div>
   <div class="card-body">
      <!-- Status Stages Progress Bar -->
      <?php if($statusStages && count($statusStages) > 0):

         ?>
         <div class="status-stages-tracker">
            <div class="d-flex align-items-center justify-content-between mb-3">
               <?php foreach($statusStages as $stage): ?>
                  <?php
                  $matchesID = $currentStatusID && isset($stage->stageID) && intval($stage->stageID) === intval($currentStatusID);
                  $isActive = $matchesID || ($stage->stageCode == $currentStage);
                  $isCompleted = ($stage->stageOrder < $currentStageOrder);
                  $isPending = ($stage->stageOrder > $currentStageOrder);
                  ?>
                  <div class="stage-item flex-fill text-center position-relative">
                     <div class="stage-indicator mb-2">
                        <div class="stage-icon-wrapper <?= $isActive ? 'active' : ($isCompleted ? 'completed' : 'pending') ?>"
                             style="background-color: <?= $isActive ? $stage->colorCode : '#e9ecef' ?>; color: <?= $isActive ? '#fff' : '#6c757d' ?>">
                           <i class="<?= $stage->iconClass ?>"></i>
                        </div>
                        <?php if($isCompleted): ?>
                           <div class="stage-check">
                              <i class="ri-check-line"></i>
                           </div>
                        <?php endif; ?>
                     </div>
                     <div class="stage-label">
                        <div class="fw-semibold <?= $isActive ? 'text-primary' : ($isCompleted ? 'text-success' : 'text-muted') ?>">
                           <?= htmlspecialchars($stage->stageName) ?>
                        </div>
                        <?php if($isActive): ?>
                           <small class="badge bg-primary-transparent text-primary">Current</small>
                        <?php endif; ?>
                     </div>
                     <?php if($stage->stageOrder < count($statusStages)): ?>
                        <div class="stage-connector <?= $isCompleted ? 'completed' : '' ?>"></div>
                     <?php endif; ?>
                  </div>
               <?php endforeach; ?>
            </div>

            <!-- Progress Bar -->
            <div class="progress mb-3" style="height: 8px;">
               <div class="progress-bar bg-primary"
                    role="progressbar"
                    style="width: <?= ($currentStageOrder / count($statusStages)) * 100 ?>%"
                    aria-valuenow="<?= $currentStageOrder ?>"
                    aria-valuemin="0"
                    aria-valuemax="<?= count($statusStages) ?>">
               </div>
            </div>

            <!-- Stage Change Controls -->
            <?php if(isset($isValidUser) && $isValidUser && (isset($userDetails->ID) && $userDetails->ID == $proposalDetails->employeeID || (isset($isAdmin) && $isAdmin))): ?>
               <div class="d-flex justify-content-center gap-2">
                  <button type="button"
                          class="btn btn-sm btn-outline-primary"
                          data-bs-toggle="modal"
                          data-bs-target="#changeProposalStageModal">
                     <i class="ri-arrow-right-line me-1"></i>Change Status
                  </button>
               </div>
            <?php endif; ?>
         </div>
      <?php endif; ?>

      <!-- Completion Details -->
      <div class="row g-3 mt-3 pt-3 border-top">
         <div class="col-md-6">
            <div class="d-flex align-items-center">
               <div class="flex-shrink-0">
                  <div class="avatar avatar-lg rounded-circle bg-primary-transparent">
                     <i class="ri-checkbox-circle-line text-primary fs-24"></i>
                  </div>
               </div>
               <div class="flex-grow-1 ms-3">
                  <div class="fw-semibold">Total Items</div>
                  <div class="text-muted small">
                     <?= $completionData['completedItems'] ?? 0 ?> of <?= $completionData['totalItems'] ?? 0 ?> completed
                  </div>
               </div>
            </div>
         </div>
         <div class="col-md-6">
            <div class="d-flex align-items-center">
               <div class="flex-shrink-0">
                  <div class="avatar avatar-lg rounded-circle bg-warning-transparent">
                     <i class="ri-alert-line text-warning fs-24"></i>
                  </div>
               </div>
               <div class="flex-grow-1 ms-3">
                  <div class="fw-semibold">Mandatory Items</div>
                  <div class="text-muted small">
                     <?= $completionData['completedMandatory'] ?? 0 ?> of <?= $completionData['mandatoryItems'] ?? 0 ?> completed
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<!-- Change Proposal Stage Modal -->
<div class="modal fade" id="changeProposalStageModal" tabindex="-1" aria-labelledby="changeProposalStageModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="changeProposalStageModalLabel">
               <i class="ri-arrow-right-line me-2"></i>Change Proposal Status
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form id="changeProposalStageForm">
            <div class="modal-body">
               <div class="mb-3">
                  <label class="form-label">Select Status Stage</label>
                  <select class="form-select" name="proposalStatusID" id="proposalStatusSelect" required>
                     <option value="">Choose status...</option>
                     <?php if($statusStages): ?>
                        <?php foreach($statusStages as $stage): ?>
                           <?php
                              $selected = ($currentStatusID && isset($stage->stageID) && intval($stage->stageID) === intval($currentStatusID))
                                          ? 'selected'
                                          : (($stage->stageCode ?? '') === $currentStage ? 'selected' : '');
                           ?>
                           <option value="<?= $stage->stageID ?>" <?= $selected ?>>
                              <?= htmlspecialchars($stage->stageName) ?>
                           </option>
                        <?php endforeach; ?>
                     <?php endif; ?>
                  </select>
               </div>
               <input type="hidden" name="proposalID" value="<?= $proposalDetails->proposalID ?? '' ?>">
               <input type="hidden" name="entityID" value="<?= $proposalDetails->entityID ?? ($entityID ?? '') ?>">
               <input type="hidden" name="orgDataID" value="<?= $proposalDetails->orgDataID ?? ($orgDataID ?? '') ?>">
               <input type="hidden" name="employeeID" value="<?= $proposalDetails->employeeID ?? ($userDetails->ID ?? '') ?>">
               <input type="hidden" name="ajax" value="1">
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
               <button type="submit" class="btn btn-primary" id="changeStageSubmit">
                  <i class="ri-check-line me-1"></i>Update Status
               </button>
            </div>
         </form>
      </div>
   </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
   const form = document.getElementById('changeProposalStageForm');
   const submitBtn = document.getElementById('changeStageSubmit');
   const modalEl = document.getElementById('changeProposalStageModal');

   function notifyError(message) {
      if (window.Swal && typeof Swal.fire === 'function') {
         Swal.fire({
            icon: 'error',
            title: 'Update failed',
            text: message || 'Something went wrong while updating the status'
         });
      } else {
         alert(message || 'Something went wrong while updating the status');
      }
   }

   form?.addEventListener('submit', function(e) {
      e.preventDefault();
      if (!form.checkValidity()) return;

      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';

      const fd = new FormData(form);

      fetch('<?= $base ?>php/scripts/sales/manage_proposal.php', {
         method: 'POST',
         body: fd
      })
      .then(res => res.json())
      .then(res => {
         if (res.success) {
            if (window.bootstrap) {
               const bsModal = bootstrap.Modal.getInstance(modalEl);
               bsModal && bsModal.hide();
            }
            setTimeout(() => location.reload(), 300);
         } else {
            notifyError(res.message || 'Failed to update proposal status');
         }
      })
      .catch(() => notifyError('Network error updating proposal status'))
      .finally(() => {
         submitBtn.disabled = false;
         submitBtn.innerHTML = '<i class="ri-check-line me-1"></i>Update Status';
      });
   });
});
</script>

<style>
.status-stages-tracker {
   position: relative;
}

.stage-item {
   position: relative;
   z-index: 1;
}

.stage-indicator {
   position: relative;
   display: inline-block;
}

.stage-icon-wrapper {
   width: 48px;
   height: 48px;
   border-radius: 50%;
   display: flex;
   align-items: center;
   justify-content: center;
   font-size: 20px;
   transition: all 0.3s ease;
   border: 3px solid #fff;
   box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stage-icon-wrapper.active {
   transform: scale(1.1);
   box-shadow: 0 4px 8px rgba(0,123,255,0.3);
}

.stage-icon-wrapper.completed {
   background-color: #198754 !important;
   color: #fff !important;
}

.stage-check {
   position: absolute;
   top: -5px;
   right: -5px;
   width: 20px;
   height: 20px;
   background: #198754;
   border-radius: 50%;
   display: flex;
   align-items: center;
   justify-content: center;
   color: #fff;
   font-size: 12px;
   border: 2px solid #fff;
}

.stage-connector {
   position: absolute;
   top: 24px;
   left: calc(50% + 24px);
   width: calc(100% - 48px);
   height: 3px;
   background: #e9ecef;
   z-index: 0;
}

.stage-connector.completed {
   background: #198754;
}

.stage-item:last-child .stage-connector {
   display: none;
}
</style>

