<?php
/**
 * Proposal Details - Requirements/Checklists Tab
 * Displays and manages proposal checklists and requirement items
 */

$checklists = Proposal::proposal_checklist(array(
   'orgDataID' => $proposalDetails->orgDataID,
   'entityID' => $proposalDetails->entityID,
   'proposalID' => $proposalID
), false, $DBConn);

// Calculate statistics
$totalItems = 0;
$pendingItems = 0;
$inProgressItems = 0;
$completedItems = 0;

if ($checklists) {
   foreach ($checklists as $checklist) {
      $items = Proposal::proposal_checklist_item_assignment_full(
         array('proposalChecklistID' => $checklist->proposalChecklistID),
         false,
         $DBConn
      );
      if ($items) {
         $totalItems += count($items);
         foreach ($items as $item) {
            if (isset($item->proposalChecklistItemAssignmentStatusID)) {
               switch ($item->proposalChecklistItemAssignmentStatusID) {
                  case 1: $pendingItems++; break;
                  case 2: $inProgressItems++; break;
                  case 3: $completedItems++; break;
                  default: $pendingItems++; break;
               }
            }
         }
      }
   }
}
?>

<!-- Stats Summary -->
<div class="proposal-quick-stats">
   <div class="proposal-stat-card">
      <div class="proposal-stat-icon primary">
         <i class="ri-list-check-2"></i>
      </div>
      <div>
         <div class="proposal-stat-value"><?= $totalItems ?></div>
         <div class="proposal-stat-label">Total Items</div>
      </div>
   </div>
   <div class="proposal-stat-card">
      <div class="proposal-stat-icon warning">
         <i class="ri-time-line"></i>
      </div>
      <div>
         <div class="proposal-stat-value"><?= $pendingItems ?></div>
         <div class="proposal-stat-label">Pending</div>
      </div>
   </div>
   <div class="proposal-stat-card">
      <div class="proposal-stat-icon primary">
         <i class="ri-loader-4-line"></i>
      </div>
      <div>
         <div class="proposal-stat-value"><?= $inProgressItems ?></div>
         <div class="proposal-stat-label">In Progress</div>
      </div>
   </div>
   <div class="proposal-stat-card">
      <div class="proposal-stat-icon success">
         <i class="ri-checkbox-circle-line"></i>
      </div>
      <div>
         <div class="proposal-stat-value"><?= $completedItems ?></div>
         <div class="proposal-stat-label">Completed</div>
      </div>
   </div>
</div>

<!-- Requirements List -->
<div class="proposal-section-card">
   <div class="proposal-section-header">
      <h5 class="proposal-section-title">
         <i class="ri-checkbox-multiple-line"></i>
         Requirement Categories
         <?php if($checklists): ?>
            <span class="badge bg-primary ms-2"><?= count($checklists) ?></span>
         <?php endif; ?>
      </h5>
      <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#manageChecklistModal">
         <i class="ri-add-line me-1"></i> Add Category
      </button>
   </div>
   <div class="proposal-section-body p-0">
      <?php if($checklists && count($checklists) > 0): ?>
         <div class="accordion accordion-flush" id="checklistAccordion">
            <?php foreach ($checklists as $key => $checklist):
               $checklistItemsAssignment = Proposal::proposal_checklist_item_assignment_full(
                  array('proposalChecklistID' => $checklist->proposalChecklistID),
                  false,
                  $DBConn
               );
               $itemCount = $checklistItemsAssignment ? count($checklistItemsAssignment) : 0;

               // Calculate completion
               $completedCount = 0;
               if ($checklistItemsAssignment) {
                  foreach ($checklistItemsAssignment as $item) {
                     if (isset($item->proposalChecklistItemAssignmentStatusID) && $item->proposalChecklistItemAssignmentStatusID == 3) {
                        $completedCount++;
                     }
                  }
               }
               $completionPercent = $itemCount > 0 ? round(($completedCount / $itemCount) * 100) : 0;

               // Status badge class
               switch($checklist->proposalChecklistStatusID ?? 1) {
                  case 1: $statusBadgeClass = 'bg-warning text-dark'; break;
                  case 2: $statusBadgeClass = 'bg-info'; break;
                  case 3: $statusBadgeClass = 'bg-success'; break;
                  case 4: $statusBadgeClass = 'bg-primary'; break;
                  default: $statusBadgeClass = 'bg-secondary'; break;
               }

               // Deadline status
               $deadlineDate = strtotime($checklist->proposalChecklistDeadlineDate);
               $today = strtotime(date('Y-m-d'));
               $daysUntilDeadline = ($deadlineDate - $today) / (60 * 60 * 24);
               $deadlineClass = '';
               if ($daysUntilDeadline < 0) $deadlineClass = 'text-danger';
               elseif ($daysUntilDeadline <= 3) $deadlineClass = 'text-warning';
            ?>
            <div class="accordion-item border-0 border-bottom">
               <h2 class="accordion-header">
                  <button class="accordion-button <?= $key > 0 ? 'collapsed' : '' ?>"
                          type="button"
                          data-bs-toggle="collapse"
                          data-bs-target="#checklist_<?= $checklist->proposalChecklistID ?>"
                          aria-expanded="<?= $key === 0 ? 'true' : 'false' ?>">
                     <div class="d-flex align-items-center gap-3 w-100 me-3">
                        <div class="flex-shrink-0">
                           <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                              <i class="ri-folder-check-line text-primary"></i>
                           </div>
                        </div>
                        <div class="flex-grow-1">
                           <div class="d-flex align-items-center gap-2 mb-1">
                              <strong><?= htmlspecialchars($checklist->proposalChecklistName) ?></strong>
                              <span class="badge <?= $statusBadgeClass ?> fw-normal"><?= htmlspecialchars($checklist->proposalChecklistStatusName) ?></span>
                           </div>
                           <div class="d-flex align-items-center gap-3 text-muted small">
                              <span class="<?= $deadlineClass ?>">
                                 <i class="ri-calendar-line"></i> <?= Utility::date_format($checklist->proposalChecklistDeadlineDate) ?>
                              </span>
                              <span>
                                 <i class="ri-user-line"></i> <?= htmlspecialchars($checklist->AssignedEmployeeName ?? 'Unassigned') ?>
                              </span>
                              <span>
                                 <i class="ri-file-list-3-line"></i> <?= $itemCount ?> item<?= $itemCount != 1 ? 's' : '' ?>
                              </span>
                           </div>
                        </div>
                        <div class="flex-shrink-0 text-end me-3" style="min-width: 80px;">
                           <div class="progress" style="height: 6px; width: 80px;">
                              <div class="progress-bar bg-success" style="width: <?= $completionPercent ?>%"></div>
                           </div>
                           <small class="text-muted"><?= $completionPercent ?>%</small>
                        </div>
                     </div>
                  </button>
               </h2>
               <div id="checklist_<?= $checklist->proposalChecklistID ?>"
                    class="accordion-collapse collapse <?= $key === 0 ? 'show' : '' ?>"
                    data-bs-parent="#checklistAccordion">
                  <div class="accordion-body bg-light">
                     <!-- Category Actions -->
                     <div class="d-flex justify-content-between align-items-center mb-3">
                        <p class="text-muted mb-0 small"><?= htmlspecialchars($checklist->proposalChecklistDescription) ?></p>
                        <div class="d-flex gap-2">
                           <button type="button"
                                   class="btn btn-sm btn-outline-primary addChecklistItemAssignmentBtn"
                                   data-bs-toggle="modal"
                                   data-bs-target="#addChecklistItemAssignment"
                                   data-proposal-checklist-id="<?= $checklist->proposalChecklistID ?>"
                                   data-proposal-checklist-name="<?= htmlspecialchars($checklist->proposalChecklistName) ?>"
                                   data-proposal-id="<?= $checklist->proposalID ?>"
                                   data-org-data-id="<?= $checklist->orgDataID ?>"
                                   data-entity-id="<?= $checklist->entityID ?>"
                                   data-proposal-checklist-deadline-date="<?= $checklist->proposalChecklistDeadlineDate ?>">
                              <i class="ri-add-line me-1"></i> Add Item
                           </button>
                           <button type="button"
                                   class="btn btn-sm btn-outline-secondary editChecklistItemCategory"
                                   data-bs-toggle="modal"
                                   data-bs-target="#manageChecklistModal"
                                   data-proposal-checklist-id="<?= $checklist->proposalChecklistID ?>"
                                   data-proposal-checklist-name="<?= htmlspecialchars($checklist->proposalChecklistName) ?>"
                                   data-proposal-checklist-description="<?= htmlspecialchars($checklist->proposalChecklistDescription) ?>"
                                   data-proposal-checklist-status-id="<?= $checklist->proposalChecklistStatusID ?>"
                                   data-assigned-employee-id="<?= $checklist->assignedEmployeeID ?>"
                                   data-assignee-id="<?= $checklist->assigneeID ?>"
                                   data-proposal-id="<?= $checklist->proposalID ?>"
                                   data-org-data-id="<?= $checklist->orgDataID ?>"
                                   data-entity-id="<?= $checklist->entityID ?>">
                              <i class="ri-edit-line"></i>
                           </button>
                        </div>
                     </div>

                     <!-- Checklist Items -->
                     <?php if($checklistItemsAssignment && count($checklistItemsAssignment) > 0): ?>
                        <div class="list-group">
                           <?php foreach ($checklistItemsAssignment as $checklistItem):
                              // Status class
                              switch($checklistItem->proposalChecklistItemAssignmentStatusID ?? 1) {
                                 case 1: $itemStatusClass = 'border-start border-4 border-warning'; break;
                                 case 2: $itemStatusClass = 'border-start border-4 border-info'; break;
                                 case 3: $itemStatusClass = 'border-start border-4 border-success'; break;
                                 default: $itemStatusClass = 'border-start border-4 border-secondary'; break;
                              }

                              // Due date check
                              $itemDueDate = strtotime($checklistItem->proposalChecklistItemAssignmentDueDate);
                              $itemDaysUntil = ($itemDueDate - $today) / (60 * 60 * 24);
                              $itemDueDateClass = '';
                              if ($itemDaysUntil < 0 && $checklistItem->proposalChecklistItemAssignmentStatusID != 3) {
                                 $itemDueDateClass = 'text-danger fw-bold';
                              } elseif ($itemDaysUntil <= 3 && $checklistItem->proposalChecklistItemAssignmentStatusID != 3) {
                                 $itemDueDateClass = 'text-warning';
                              }

                              // Assignee initials
                              $assigneeInitials = 'NA';
                              if (!empty($checklistItem->checklistItemAssignedEmployeeName)) {
                                 $parts = explode(' ', $checklistItem->checklistItemAssignedEmployeeName);
                                 $assigneeInitials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                              }
                           ?>
                           <div class="list-group-item bg-white <?= $itemStatusClass ?>">
                              <div class="d-flex justify-content-between align-items-center">
                                 <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                         style="width: 32px; height: 32px; font-size: 0.75rem;"
                                         title="<?= htmlspecialchars($checklistItem->checklistItemAssignedEmployeeName ?? 'Unassigned') ?>">
                                       <?= $assigneeInitials ?>
                                    </div>
                                    <div>
                                       <p class="mb-0"><?= htmlspecialchars($checklistItem->proposalChecklistItemAssignmentDescription) ?></p>
                                       <small class="text-muted">
                                          Assigned to: <?= htmlspecialchars($checklistItem->checklistItemAssignedEmployeeName ?? 'Unassigned') ?>
                                       </small>
                                    </div>
                                 </div>
                                 <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-secondary <?= $itemDueDateClass ?>">
                                       <?= Utility::date_format($checklistItem->proposalChecklistItemAssignmentDueDate) ?>
                                       <?php if ($itemDaysUntil < 0 && $checklistItem->proposalChecklistItemAssignmentStatusID != 3): ?>
                                          <small>(Overdue)</small>
                                       <?php endif; ?>
                                    </span>
                                    <span class="badge bg-primary-subtle text-primary">
                                       <?= $checklistItem->proposalChecklistStatusItemName ?? 'Pending' ?>
                                    </span>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary editChecklistItemBtn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#addChecklistItemAssignment"
                                            data-proposal-checklist-item-assignment-id="<?= $checklistItem->proposalChecklistItemAssignmentID ?>"
                                            data-proposal-id="<?= $checklistItem->proposalID ?>"
                                            data-proposal-checklist-id="<?= $checklistItem->proposalChecklistID ?>"
                                            data-proposal-checklist-item-category-id="<?= $checklistItem->proposalChecklistItemCategoryID ?>"
                                            data-proposal-checklist-item-id="<?= $checklistItem->proposalChecklistItemID ?>"
                                            data-proposal-checklist-item-assignment-due-date="<?= $checklistItem->proposalChecklistItemAssignmentDueDate ?>"
                                            data-proposal-checklist-item-assignment-description="<?= htmlspecialchars($checklistItem->proposalChecklistItemAssignmentDescription) ?>"
                                            data-proposal-checklist-item-assignment-status-id="<?= $checklistItem->proposalChecklistItemAssignmentStatusID ?>"
                                            data-checklist-item-assigned-employee-id="<?= $checklistItem->checklistItemAssignedEmployeeID ?>"
                                            data-proposal-checklist-assignor-id="<?= $checklistItem->proposalChecklistAssignorID ?>"
                                            data-org-data-id="<?= $checklistItem->orgDataID ?>"
                                            data-entity-id="<?= $checklistItem->entityID ?>">
                                       <i class="ri-edit-line"></i>
                                    </button>
                                    <a href="<?= "{$base}html/?s={$s}&ss={$ss}&sss={$sss}&p=proposal_checklist_item_details&checkListItemAssignmentID={$checklistItem->proposalChecklistItemAssignmentID}" ?>"
                                       class="btn btn-sm btn-primary">
                                       <i class="ri-eye-line"></i>
                                    </a>
                                 </div>
                              </div>
                           </div>
                           <?php endforeach; ?>
                        </div>
                     <?php else: ?>
                        <div class="text-center py-4 text-muted">
                           <i class="ri-inbox-line fs-2 d-block mb-2"></i>
                           <p class="mb-2">No requirements added yet</p>
                           <button type="button"
                                   class="btn btn-sm btn-outline-primary addChecklistItemAssignmentBtn"
                                   data-bs-toggle="modal"
                                   data-bs-target="#addChecklistItemAssignment"
                                   data-proposal-checklist-id="<?= $checklist->proposalChecklistID ?>"
                                   data-proposal-checklist-name="<?= htmlspecialchars($checklist->proposalChecklistName) ?>"
                                   data-proposal-id="<?= $checklist->proposalID ?>"
                                   data-org-data-id="<?= $checklist->orgDataID ?>"
                                   data-entity-id="<?= $checklist->entityID ?>"
                                   data-proposal-checklist-deadline-date="<?= $checklist->proposalChecklistDeadlineDate ?>">
                              <i class="ri-add-line"></i> Add First Requirement
                           </button>
                        </div>
                     <?php endif; ?>
                  </div>
               </div>
            </div>
            <?php endforeach; ?>
         </div>
      <?php else: ?>
         <div class="text-center py-5">
            <div class="mb-3">
               <div class="rounded-circle bg-primary-subtle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                  <i class="ri-folder-add-line text-primary" style="font-size: 2.5rem;"></i>
               </div>
            </div>
            <h5 class="mb-2">No Requirement Categories Yet</h5>
            <p class="text-muted mb-3">
               Start organizing your proposal requirements by creating categories such as<br>
               "Mandatory Documents", "Technical Proposal", or "Financial Documents".
            </p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manageChecklistModal">
               <i class="ri-add-line me-1"></i> Create First Category
            </button>
         </div>
      <?php endif; ?>
   </div>
</div>
