<?php
/**
 * Proposal Tasks Display Component
 * Shows and manages proposal tasks with assignment and completion tracking
 *
 * @package    Tija CRM
 * @subpackage Sales Management
 */

// Fetch tasks for this proposal
$proposalTasks = Proposal::proposal_tasks(array('proposalID' => $proposalID, 'Suspended' => 'N'), false, $DBConn);

// Ensure $proposalTasks is always an array
if (!is_array($proposalTasks)) {
    $proposalTasks = array();
}

// Group tasks by status
$tasksByStatus = array(
    'pending' => array(),
    'in_progress' => array(),
    'completed' => array(),
    'cancelled' => array()
);

if (!empty($proposalTasks)) {
    foreach ($proposalTasks as $task) {
        $status = $task->status ?? 'pending';
        if (isset($tasksByStatus[$status])) {
            $tasksByStatus[$status][] = $task;
        }
    }
}

// Priority colors
$priorityColors = array(
    'low' => 'secondary',
    'medium' => 'info',
    'high' => 'warning',
    'urgent' => 'danger'
);

// Status colors
$statusColors = array(
    'pending' => 'secondary',
    'in_progress' => 'primary',
    'completed' => 'success',
    'cancelled' => 'danger'
);
?>

<div class="proposal-tasks-container">
   <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0 fw-semibold">
         <i class="ri-task-line me-2 text-primary"></i>
         Proposal Tasks
      </h5>
      <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#manageProposalTaskModal">
         <i class="ri-add-line me-1"></i>Add Task
      </button>
   </div>

   <!-- Task Statistics -->
   <div class="row g-3 mb-4">
      <div class="col-md-3">
         <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
               <div class="h4 mb-0 text-muted"><?= count($proposalTasks) ?></div>
               <small class="text-muted">Total Tasks</small>
            </div>
         </div>
      </div>
      <div class="col-md-3">
         <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
               <div class="h4 mb-0 text-primary"><?= count($tasksByStatus['pending'] ?? array()) ?></div>
               <small class="text-muted">Pending</small>
            </div>
         </div>
      </div>
      <div class="col-md-3">
         <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
               <div class="h4 mb-0 text-info"><?= count($tasksByStatus['in_progress'] ?? array()) ?></div>
               <small class="text-muted">In Progress</small>
            </div>
         </div>
      </div>
      <div class="col-md-3">
         <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
               <div class="h4 mb-0 text-success"><?= count($tasksByStatus['completed'] ?? array()) ?></div>
               <small class="text-muted">Completed</small>
            </div>
         </div>
      </div>
   </div>

   <!-- Tasks List -->
   <?php if(!empty($proposalTasks)): ?>
      <div class="task-list">
         <?php foreach($tasksByStatus as $status => $tasks): ?>
            <?php if(!empty($tasks)): ?>
               <div class="task-status-group mb-4">
                  <h6 class="fw-semibold mb-3 text-uppercase text-muted">
                     <span class="badge bg-<?= $statusColors[$status] ?? 'secondary' ?>-transparent me-2">
                        <?= count($tasks) ?>
                     </span>
                     <?= ucfirst(str_replace('_', ' ', $status)) ?>
                  </h6>

                  <div class="row g-3">
                     <?php foreach($tasks as $task): ?>
                        <div class="col-md-6 col-lg-4">
                           <div class="card border shadow-sm task-card h-100">
                              <div class="card-body">
                                 <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0 fw-semibold flex-grow-1">
                                       <?= htmlspecialchars($task->taskName) ?>
                                       <?php if($task->isMandatory === 'Y'): ?>
                                          <span class="badge bg-danger-transparent text-danger ms-1" title="Mandatory">
                                             <i class="ri-alert-line"></i>
                                          </span>
                                       <?php endif; ?>
                                    </h6>
                                    <span class="badge bg-<?= $priorityColors[$task->priority ?? 'medium'] ?? 'info' ?>-transparent">
                                       <?= ucfirst($task->priority ?? 'medium') ?>
                                    </span>
                                 </div>

                                 <?php if($task->taskDescription): ?>
                                    <p class="text-muted small mb-2"><?= nl2br(htmlspecialchars(substr($task->taskDescription, 0, 100))) ?><?= strlen($task->taskDescription) > 100 ? '...' : '' ?></p>
                                 <?php endif; ?>

                                 <div class="mb-2">
                                    <small class="text-muted d-block">
                                       <i class="ri-user-line me-1"></i>Assigned to: <?= htmlspecialchars($task->assignedToName ?? 'Unassigned') ?>
                                    </small>
                                    <small class="text-muted d-block">
                                       <i class="ri-calendar-line me-1"></i>Due: <?= Utility::date_format($task->dueDate) ?>
                                       <?php
                                       $dueDate = strtotime($task->dueDate);
                                       $now = time();
                                       if ($dueDate < $now && $task->status !== 'completed'): ?>
                                          <span class="badge bg-danger-transparent text-danger ms-1">Overdue</span>
                                       <?php endif; ?>
                                    </small>
                                 </div>

                                 <!-- Completion Progress -->
                                 <?php if($task->status === 'in_progress'): ?>
                                    <div class="mb-2">
                                       <div class="d-flex justify-content-between small mb-1">
                                          <span>Progress</span>
                                          <span><?= number_format($task->completionPercentage ?? 0, 0) ?>%</span>
                                       </div>
                                       <div class="progress" style="height: 6px;">
                                          <div class="progress-bar bg-primary"
                                               style="width: <?= $task->completionPercentage ?? 0 ?>%"></div>
                                       </div>
                                    </div>
                                 <?php endif; ?>

                                 <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                    <?php if($task->assignedTo == $userDetails->ID && $task->status !== 'completed'): ?>
                                       <button type="button"
                                               class="btn btn-sm btn-outline-primary updateTaskProgressBtn"
                                               data-task-id="<?= $task->proposalTaskID ?>"
                                               data-current-progress="<?= $task->completionPercentage ?? 0 ?>">
                                          <i class="ri-edit-line me-1"></i>Update Progress
                                       </button>
                                    <?php endif; ?>

                                    <?php if($task->assignedBy == $userDetails->ID || ($isAdmin ?? false)): ?>
                                       <div class="btn-group btn-group-sm">
                                          <button type="button"
                                                  class="btn btn-outline-secondary editTaskBtn"
                                                  onclick="editProposalTask(<?= $task->proposalTaskID ?>)">
                                             <i class="ri-pencil-line"></i>
                                          </button>
                                          <button type="button"
                                                  class="btn btn-outline-danger deleteTaskBtn"
                                                  data-task-id="<?= $task->proposalTaskID ?>">
                                             <i class="ri-delete-bin-line"></i>
                                          </button>
                                       </div>
                                    <?php endif; ?>
                                 </div>
                              </div>
                           </div>
                        </div>
                     <?php endforeach; ?>
                  </div>
               </div>
            <?php endif; ?>
         <?php endforeach; ?>
      </div>
   <?php else: ?>
      <div class="text-center py-5">
         <i class="ri-task-line fs-48 text-muted mb-3 d-block"></i>
         <p class="text-muted mb-3">No tasks created yet.</p>
         <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manageProposalTaskModal">
            <i class="ri-add-line me-1"></i>Create First Task
         </button>
      </div>
   <?php endif; ?>
</div>

<script>
(function() {
   'use strict';

   // Update task progress
   document.querySelectorAll('.updateTaskProgressBtn').forEach(btn => {
      btn.addEventListener('click', function() {
         const taskID = this.dataset.taskId;
         const currentProgress = parseFloat(this.dataset.currentProgress) || 0;

         // Simple prompt for now - can be enhanced with a modal
         const newProgress = prompt('Enter completion percentage (0-100):', currentProgress);
         if (newProgress !== null) {
            const progress = parseFloat(newProgress);
            if (isNaN(progress) || progress < 0 || progress > 100) {
               if (typeof showToast === 'function') {
                  showToast('Please enter a valid percentage between 0 and 100', 'error');
               } else {
                  alert('Please enter a valid percentage between 0 and 100');
               }
               return;
            }

            // Update via AJAX
            const formData = new FormData();
            formData.append('action', 'complete');
            formData.append('proposalTaskID', taskID);
            formData.append('completionPercentage', progress);

            fetch('<?= $base ?>php/scripts/sales/manage_proposal_task.php', {
               method: 'POST',
               body: formData
            })
            .then(response => response.json())
            .then(data => {
               if (data.success) {
                  if (typeof showToast === 'function') {
                     showToast(data.message || 'Progress updated successfully', 'success');
                  }
                  location.reload();
               } else {
                  if (typeof showToast === 'function') {
                     showToast(data.message || 'Failed to update progress', 'error');
                  } else {
                     alert(data.message || 'Failed to update progress');
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
      });
   });

   // Delete task
   document.querySelectorAll('.deleteTaskBtn').forEach(btn => {
      btn.addEventListener('click', function() {
         if (!confirm('Are you sure you want to delete this task?')) {
            return;
         }

         const taskID = this.dataset.taskId;
         const formData = new FormData();
         formData.append('action', 'delete');
         formData.append('proposalTaskID', taskID);

         fetch('<?= $base ?>php/scripts/sales/manage_proposal_task.php', {
            method: 'POST',
            body: formData
         })
         .then(response => response.json())
         .then(data => {
            if (data.success) {
               if (typeof showToast === 'function') {
                  showToast(data.message || 'Task deleted successfully', 'success');
               }
               location.reload();
            } else {
               if (typeof showToast === 'function') {
                  showToast(data.message || 'Failed to delete task', 'error');
               }
            }
         })
         .catch(error => {
            console.error('Error:', error);
         });
      });
   });
})();
</script>

<style>
.task-card {
   transition: transform 0.2s, box-shadow 0.2s;
}

.task-card:hover {
   transform: translateY(-2px);
   box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
}

.task-status-group {
   border-left: 3px solid #e9ecef;
   padding-left: 1rem;
}
</style>

