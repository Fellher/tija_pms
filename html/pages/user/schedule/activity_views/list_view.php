<?php
/**
 * Activity List View
 * 
 * Provides a traditional table/list format for viewing activities
 * Features:
 * - Sortable columns
 * - Bulk selection
 * - Quick actions
 * - Status indicators
 * - Priority levels
 * - Due date highlighting
 */

if (!isset($activities) || empty($activities)) {
    echo '<div class="text-center py-5">
            <div class="empty-state-icon mb-4">
                <i class="ri-inbox-line fs-1 text-muted"></i>
            </div>
            <h4 class="text-muted mb-3">No Activities Found</h4>
            <p class="text-muted mb-4">Get started by creating your first activity.</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manage_activity">
                <i class="ri-add-line me-2"></i>Create Activity
            </button>
          </div>';
    return;
}

// Sort activities by date (newest first)
usort($activities, function($a, $b) {
    return strtotime($b->activityDate) - strtotime($a->activityDate);
});
?>

<!-- List View Container -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom-0">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="ri-list-check me-2 text-primary"></i>
                Activity List
                <span class="badge bg-primary ms-2"><?= count($activities) ?></span>
            </h5>
            
            <!-- List View Controls -->
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="ri-sort-desc me-1"></i>Sort
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="sortActivities('date')">
                            <i class="ri-calendar-line me-2"></i>By Date
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="sortActivities('priority')">
                            <i class="ri-flag-line me-2"></i>By Priority
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="sortActivities('status')">
                            <i class="ri-checkbox-circle-line me-2"></i>By Status
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="sortActivities('name')">
                            <i class="ri-text me-2"></i>By Name
                        </a></li>
                    </ul>
                </div>
                
                <button class="btn btn-outline-secondary btn-sm" onclick="toggleCompactView()">
                    <i class="ri-layout-line" id="compactToggleIcon"></i>
                </button>
            </div>
        </div>
    </div>
    
    <div class="card-body p-0">
        <!-- Activities Table -->
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="activitiesTable">
                <thead class="table-light">
                    <tr>
                        <th width="40">
                            <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleAllSelection()">
                        </th>
                        <th width="60">Status</th>
                        <th>Activity</th>
                        <th width="120">Priority</th>
                        <th width="120">Due Date</th>
                        <th width="100">Progress</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activities as $activity): ?>
                        <?php
                        // Determine activity status and styling
                        $statusClass = '';
                        $statusText = '';
                        $statusIcon = '';
                        
                        switch($activity->activityStatusID) {
                            case 1:
                                $statusClass = 'status-todo';
                                $statusText = 'To Do';
                                $statusIcon = 'ri-checkbox-blank-circle-line';
                                break;
                            case 2:
                                $statusClass = 'status-in-progress';
                                $statusText = 'In Progress';
                                $statusIcon = 'ri-play-circle-line';
                                break;
                            case 3:
                                $statusClass = 'status-review';
                                $statusText = 'Review';
                                $statusIcon = 'ri-eye-line';
                                break;
                            case 4:
                                $statusClass = 'status-completed';
                                $statusText = 'Completed';
                                $statusIcon = 'ri-checkbox-circle-line';
                                break;
                            case 5:
                                $statusClass = 'status-cancelled';
                                $statusText = 'Cancelled';
                                $statusIcon = 'ri-close-circle-line';
                                break;
                        }
                        
                        // Determine priority styling
                        $priorityClass = '';
                        $priorityText = '';
                        $priorityIcon = '';
                        
                        if (isset($activity->priority)) {
                            switch(strtolower($activity->priority)) {
                                case 'high':
                                    $priorityClass = 'text-danger';
                                    $priorityText = 'High';
                                    $priorityIcon = 'ri-arrow-up-line';
                                    break;
                                case 'medium':
                                    $priorityClass = 'text-warning';
                                    $priorityText = 'Medium';
                                    $priorityIcon = 'ri-subtract-line';
                                    break;
                                case 'low':
                                    $priorityClass = 'text-success';
                                    $priorityText = 'Low';
                                    $priorityIcon = 'ri-arrow-down-line';
                                    break;
                            }
                        } else {
                            $priorityClass = 'text-secondary';
                            $priorityText = 'Normal';
                            $priorityIcon = 'ri-subtract-line';
                        }
                        
                        // Check if overdue
                        $isOverdue = strtotime($activity->activityDate) < time() && $activity->activityStatusID != 4;
                        $dueDateClass = $isOverdue ? 'text-danger' : '';
                        
                        // Calculate progress (simplified - you might want to implement actual progress tracking)
                        $progress = $activity->activityStatusID == 4 ? 100 : 
                                   ($activity->activityStatusID == 2 ? 50 : 
                                   ($activity->activityStatusID == 3 ? 75 : 0));
                        ?>
                        
                        <tr class="activity-row <?= $isOverdue ? 'table-danger' : '' ?>" data-activity-id="<?= $activity->activityID ?>">
                            <td>
                                <input type="checkbox" class="form-check-input activity-checkbox" 
                                       value="<?= $activity->activityID ?>" onchange="updateSelection()">
                            </td>
                            
                            <!-- Status Column -->
                            <td>
                                <span class="status-badge <?= $statusClass ?>" title="<?= $statusText ?>">
                                    <i class="<?= $statusIcon ?> me-1"></i>
                                    <?= $statusText ?>
                                </span>
                            </td>
                            
                            <!-- Activity Details Column -->
                            <td>
                                <div class="activity-details">
                                    <div class="activity-title fw-semibold mb-1">
                                        <?= htmlspecialchars($activity->activityName) ?>
                                    </div>
                                    
                                    <?php if (!empty($activity->activityDescription)): ?>
                                        <div class="activity-description text-muted small mb-2">
                                            <?= htmlspecialchars(substr($activity->activityDescription, 0, 100)) ?>
                                            <?= strlen($activity->activityDescription) > 100 ? '...' : '' ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Activity Meta -->
                                    <div class="activity-meta small text-muted">
                                        <?php if (isset($activity->activityCategoryName)): ?>
                                            <span class="badge bg-light text-dark me-1">
                                                <i class="ri-folder-line me-1"></i>
                                                <?= htmlspecialchars($activity->activityCategoryName) ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($activity->projectName)): ?>
                                            <span class="badge bg-light text-dark me-1">
                                                <i class="ri-projector-line me-1"></i>
                                                <?= htmlspecialchars($activity->projectName) ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($activity->clientName)): ?>
                                            <span class="badge bg-light text-dark">
                                                <i class="ri-user-line me-1"></i>
                                                <?= htmlspecialchars($activity->clientName) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Priority Column -->
                            <td>
                                <span class="<?= $priorityClass ?> fw-medium">
                                    <i class="<?= $priorityIcon ?> me-1"></i>
                                    <?= $priorityText ?>
                                </span>
                            </td>
                            
                            <!-- Due Date Column -->
                            <td>
                                <div class="<?= $dueDateClass ?>">
                                    <?= Utility::date_format($activity->activityDate) ?>
                                    <?php if ($isOverdue): ?>
                                        <br><small class="text-danger">
                                            <i class="ri-time-line me-1"></i>Overdue
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            
                            <!-- Progress Column -->
                            <td>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar <?= $progress == 100 ? 'bg-success' : ($progress >= 50 ? 'bg-primary' : 'bg-secondary') ?>" 
                                         role="progressbar" 
                                         style="width: <?= $progress ?>%"
                                         aria-valuenow="<?= $progress ?>" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                                <small class="text-muted"><?= $progress ?>%</small>
                            </td>
                            
                            <!-- Actions Column -->
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary" 
                                            onclick="editActivity(<?= $activity->activityID ?>)"
                                            title="Edit Activity">
                                        <i class="ri-edit-line"></i>
                                    </button>
                                    
                                    <button type="button" class="btn btn-outline-success" 
                                            onclick="quickUpdateStatus(<?= $activity->activityID ?>, '<?= $activity->activityStatusID == 4 ? 'reopen' : 'complete' ?>')"
                                            title="<?= $activity->activityStatusID == 4 ? 'Reopen' : 'Complete' ?> Activity">
                                        <i class="ri-<?= $activity->activityStatusID == 4 ? 'refresh-line' : 'check-line' ?>"></i>
                                    </button>
                                    
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" 
                                                data-bs-toggle="dropdown" title="More Actions">
                                            <i class="ri-more-line"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="viewActivityDetails(<?= $activity->activityID ?>)">
                                                <i class="ri-eye-line me-2"></i>View Details
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="duplicateActivity(<?= $activity->activityID ?>)">
                                                <i class="ri-file-copy-line me-2"></i>Duplicate
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteActivity(<?= $activity->activityID ?>)">
                                                <i class="ri-delete-bin-line me-2"></i>Delete
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Pagination (if needed) -->
    <?php if (count($activities) > 20): ?>
        <div class="card-footer bg-white">
            <nav aria-label="Activity pagination">
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <li class="page-item disabled">
                        <span class="page-link">Previous</span>
                    </li>
                    <li class="page-item active">
                        <span class="page-link">1</span>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">2</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">3</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<!-- List View JavaScript -->
<script>
/**
 * List View Specific Functionality
 */

// Sort activities by different criteria
function sortActivities(criteria) {
    const table = document.getElementById('activitiesTable');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        switch(criteria) {
            case 'date':
                const dateA = a.querySelector('.activity-row').dataset.activityId;
                const dateB = b.querySelector('.activity-row').dataset.activityId;
                return dateA.localeCompare(dateB);
            case 'priority':
                const priorityA = a.querySelector('[class*="text-"]').className;
                const priorityB = b.querySelector('[class*="text-"]').className;
                return priorityA.localeCompare(priorityB);
            case 'status':
                const statusA = a.querySelector('.status-badge').textContent.trim();
                const statusB = b.querySelector('.status-badge').textContent.trim();
                return statusA.localeCompare(statusB);
            case 'name':
                const nameA = a.querySelector('.activity-title').textContent.trim();
                const nameB = b.querySelector('.activity-title').textContent.trim();
                return nameA.localeCompare(nameB);
        }
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

// Toggle compact view
let isCompactView = false;
function toggleCompactView() {
    isCompactView = !isCompactView;
    const table = document.getElementById('activitiesTable');
    const icon = document.getElementById('compactToggleIcon');
    
    if (isCompactView) {
        table.classList.add('table-sm');
        icon.className = 'ri-layout-2-line';
    } else {
        table.classList.remove('table-sm');
        icon.className = 'ri-layout-line';
    }
}

// Toggle all selection
function toggleAllSelection() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.activity-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateSelection();
}

// Quick status update
function quickUpdateStatus(activityId, action) {
    const newStatus = action === 'complete' ? 4 : 1;
    updateActivityStatus(activityId, newStatus);
}

// Activity management functions
function editActivity(activityId) {
    // Open edit modal with activity data
    console.log('Edit activity:', activityId);
    // Implementation would open the activity management modal
}

function viewActivityDetails(activityId) {
    // Open activity details modal
    console.log('View activity details:', activityId);
    // Implementation would open a details modal
}

function duplicateActivity(activityId) {
    // Duplicate activity
    console.log('Duplicate activity:', activityId);
    // Implementation would create a copy of the activity
}

function deleteActivity(activityId) {
    if (confirm('Are you sure you want to delete this activity?')) {
        // Delete activity
        console.log('Delete activity:', activityId);
        // Implementation would delete the activity
    }
}
</script>

<style>
/* List View Specific Styles */
.activity-row:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.activity-details {
    min-width: 300px;
}

.activity-meta .badge {
    font-size: 0.7rem;
    margin-right: 0.25rem;
}

.table-sm .activity-description {
    display: none;
}

.table-sm .activity-meta {
    display: none;
}

.status-badge {
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
}

.progress {
    background-color: #e9ecef;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .activity-details {
        min-width: 200px;
    }
    
    .activity-meta {
        display: none !important;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}
</style>
