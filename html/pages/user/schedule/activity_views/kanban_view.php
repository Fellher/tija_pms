<?php
/**
 * Activity Kanban View
 * 
 * Provides a board-style organization by status
 * Features:
 * - Drag and drop between columns
 * - Visual status representation
 * - Card-based layout
 * - Real-time updates
 * - Column filtering
 */

if (!isset($activities) || empty($activities)) {
    echo '<div class="text-center py-5">
            <div class="empty-state-icon mb-4">
                <i class="ri-dashboard-line fs-1 text-muted"></i>
            </div>
            <h4 class="text-muted mb-3">No Activities Found</h4>
            <p class="text-muted mb-4">Get started by creating your first activity.</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manage_activity">
                <i class="ri-add-line me-2"></i>Create Activity
            </button>
          </div>';
    return;
}

// Group activities by status
$activitiesByStatus = [
    'todo' => [],
    'in-progress' => [],
    'review' => [],
    'completed' => [],
    'cancelled' => []
];

foreach ($activities as $activity) {
    switch($activity->activityStatusID) {
        case 1:
            $activitiesByStatus['todo'][] = $activity;
            break;
        case 2:
            $activitiesByStatus['in-progress'][] = $activity;
            break;
        case 3:
            $activitiesByStatus['review'][] = $activity;
            break;
        case 4:
            $activitiesByStatus['completed'][] = $activity;
            break;
        case 5:
            $activitiesByStatus['cancelled'][] = $activity;
            break;
    }
}

// Define column configurations
$columns = [
    'todo' => [
        'title' => 'To Do',
        'icon' => 'ri-checkbox-blank-circle-line',
        'color' => 'secondary',
        'status_id' => 1
    ],
    'in-progress' => [
        'title' => 'In Progress',
        'icon' => 'ri-play-circle-line',
        'color' => 'primary',
        'status_id' => 2
    ],
    'review' => [
        'title' => 'Review',
        'icon' => 'ri-eye-line',
        'color' => 'warning',
        'status_id' => 3
    ],
    'completed' => [
        'title' => 'Completed',
        'icon' => 'ri-checkbox-circle-line',
        'color' => 'success',
        'status_id' => 4
    ],
    'cancelled' => [
        'title' => 'Cancelled',
        'icon' => 'ri-close-circle-line',
        'color' => 'danger',
        'status_id' => 5
    ]
];
?>

<!-- Kanban Board Container -->
<div class="kanban-board-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0">
            <i class="ri-dashboard-line me-2 text-primary"></i>
            Kanban Board
        </h5>
        
        <!-- Kanban Controls -->
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="toggleColumnVisibility()">
                <i class="ri-eye-line me-1"></i>Hide Columns
            </button>
            <button class="btn btn-outline-primary btn-sm" onclick="addNewColumn()">
                <i class="ri-add-line me-1"></i>Add Column
            </button>
        </div>
    </div>
    
    <div class="kanban-board" id="kanbanBoard">
        <?php foreach ($columns as $columnKey => $columnConfig): ?>
            <div class="kanban-column" data-status="<?= $columnKey ?>" data-status-id="<?= $columnConfig['status_id'] ?>">
                <!-- Column Header -->
                <div class="kanban-header">
                    <div class="d-flex align-items-center">
                        <i class="<?= $columnConfig['icon'] ?> me-2 text-<?= $columnConfig['color'] ?>"></i>
                        <h6 class="mb-0 fw-semibold"><?= $columnConfig['title'] ?></h6>
                    </div>
                    <div class="kanban-count"><?= count($activitiesByStatus[$columnKey]) ?></div>
                </div>
                
                <!-- Column Content -->
                <div class="kanban-content" id="kanban-<?= $columnKey ?>">
                    <?php if (empty($activitiesByStatus[$columnKey])): ?>
                        <div class="kanban-empty-state text-center py-4">
                            <i class="ri-inbox-line fs-2 text-muted mb-2"></i>
                            <p class="text-muted small mb-0">No activities</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($activitiesByStatus[$columnKey] as $activity): ?>
                            <?php
                            // Determine priority styling
                            $priorityClass = '';
                            $priorityIcon = '';
                            
                            if (isset($activity->priority)) {
                                switch(strtolower($activity->priority)) {
                                    case 'high':
                                        $priorityClass = 'border-danger';
                                        $priorityIcon = 'ri-arrow-up-line text-danger';
                                        break;
                                    case 'medium':
                                        $priorityClass = 'border-warning';
                                        $priorityIcon = 'ri-subtract-line text-warning';
                                        break;
                                    case 'low':
                                        $priorityClass = 'border-success';
                                        $priorityIcon = 'ri-arrow-down-line text-success';
                                        break;
                                }
                            } else {
                                $priorityClass = 'border-secondary';
                                $priorityIcon = 'ri-subtract-line text-secondary';
                            }
                            
                            // Check if overdue
                            $isOverdue = strtotime($activity->activityDate) < time() && $activity->activityStatusID != 4;
                            $overdueClass = $isOverdue ? 'border-danger bg-danger bg-opacity-10' : '';
                            
                            // Calculate progress
                            $progress = $activity->activityStatusID == 4 ? 100 : 
                                       ($activity->activityStatusID == 2 ? 50 : 
                                       ($activity->activityStatusID == 3 ? 75 : 0));
                            ?>
                            
                            <div class="activity-card draggable <?= $priorityClass ?> <?= $overdueClass ?>" 
                                 data-activity-id="<?= $activity->activityID ?>"
                                 draggable="true">
                                
                                <!-- Card Header -->
                                <div class="card-header bg-transparent border-0 p-3 pb-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="card-title mb-1 fw-semibold activity-title">
                                            <?= htmlspecialchars($activity->activityName) ?>
                                        </h6>
                                        <div class="dropdown">
                                            <button class="btn btn-link btn-sm p-0" type="button" data-bs-toggle="dropdown">
                                                <i class="ri-more-2-line text-muted"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="#" onclick="editActivity(<?= $activity->activityID ?>)">
                                                    <i class="ri-edit-line me-2"></i>Edit
                                                </a></li>
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
                                    
                                    <!-- Priority Indicator -->
                                    <div class="priority-indicator">
                                        <i class="<?= $priorityIcon ?> fs-6"></i>
                                    </div>
                                </div>
                                
                                <!-- Card Body -->
                                <div class="card-body p-3 pt-0">
                                    <!-- Description -->
                                    <?php if (!empty($activity->activityDescription)): ?>
                                        <p class="card-text small text-muted mb-3 activity-description">
                                            <?= htmlspecialchars(substr($activity->activityDescription, 0, 120)) ?>
                                            <?= strlen($activity->activityDescription) > 120 ? '...' : '' ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <!-- Progress Bar -->
                                    <?php if ($activity->activityStatusID != 4): ?>
                                        <div class="progress mb-3" style="height: 4px;">
                                            <div class="progress-bar bg-<?= $columnConfig['color'] ?>" 
                                                 style="width: <?= $progress ?>%"></div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Meta Information -->
                                    <div class="activity-meta mb-3">
                                        <!-- Due Date -->
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="ri-calendar-line me-2 text-muted small"></i>
                                            <small class="<?= $isOverdue ? 'text-danger' : 'text-muted' ?>">
                                                <?= Utility::date_format($activity->activityDate) ?>
                                                <?php if ($isOverdue): ?>
                                                    <span class="badge bg-danger ms-1">Overdue</span>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        
                                        <!-- Category -->
                                        <?php if (isset($activity->activityCategoryName)): ?>
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="ri-folder-line me-2 text-muted small"></i>
                                                <small class="text-muted"><?= htmlspecialchars($activity->activityCategoryName) ?></small>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Project -->
                                        <?php if (isset($activity->projectName)): ?>
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="ri-projector-line me-2 text-muted small"></i>
                                                <small class="text-muted"><?= htmlspecialchars($activity->projectName) ?></small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Tags -->
                                    <div class="activity-tags">
                                        <?php if (isset($activity->clientName)): ?>
                                            <span class="badge bg-light text-dark me-1 mb-1">
                                                <i class="ri-user-line me-1"></i>
                                                <?= htmlspecialchars($activity->clientName) ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($activity->activityTypeName)): ?>
                                            <span class="badge bg-light text-dark me-1 mb-1">
                                                <i class="ri-tag-line me-1"></i>
                                                <?= htmlspecialchars($activity->activityTypeName) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Card Footer -->
                                <div class="card-footer bg-transparent border-0 p-3 pt-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <!-- Assignee -->
                                        <div class="assignee-info">
                                            <small class="text-muted">
                                                <i class="ri-user-line me-1"></i>
                                                <?= isset($activity->activityOwnerName) ? htmlspecialchars($activity->activityOwnerName) : 'Unassigned' ?>
                                            </small>
                                        </div>
                                        
                                        <!-- Quick Actions -->
                                        <div class="quick-actions">
                                            <?php if ($activity->activityStatusID != 4): ?>
                                                <button class="btn btn-success btn-sm" 
                                                        onclick="quickUpdateStatus(<?= $activity->activityID ?>, 'complete')"
                                                        title="Mark Complete">
                                                    <i class="ri-check-line"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-outline-secondary btn-sm" 
                                                        onclick="quickUpdateStatus(<?= $activity->activityID ?>, 'reopen')"
                                                        title="Reopen">
                                                    <i class="ri-refresh-line"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Kanban View JavaScript -->
<script>
/**
 * Kanban View Specific Functionality
 */

// Initialize drag and drop
function initializeKanbanDragDrop() {
    const draggableElements = document.querySelectorAll('.draggable');
    const dropZones = document.querySelectorAll('.kanban-column .kanban-content');
    
    draggableElements.forEach(element => {
        element.addEventListener('dragstart', handleDragStart);
        element.addEventListener('dragend', handleDragEnd);
    });
    
    dropZones.forEach(zone => {
        zone.addEventListener('dragover', handleDragOver);
        zone.addEventListener('dragenter', handleDragEnter);
        zone.addEventListener('dragleave', handleDragLeave);
        zone.addEventListener('drop', handleDrop);
    });
}

// Drag start handler
function handleDragStart(e) {
    this.classList.add('dragging');
    e.dataTransfer.setData('text/plain', this.dataset.activityId);
    e.dataTransfer.effectAllowed = 'move';
}

// Drag end handler
function handleDragEnd(e) {
    this.classList.remove('dragging');
}

// Drag over handler
function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
}

// Drag enter handler
function handleDragEnter(e) {
    e.preventDefault();
    this.classList.add('drag-over');
}

// Drag leave handler
function handleDragLeave(e) {
    this.classList.remove('drag-over');
}

// Drop handler
function handleDrop(e) {
    e.preventDefault();
    this.classList.remove('drag-over');
    
    const activityId = e.dataTransfer.getData('text/plain');
    const newStatusId = this.closest('.kanban-column').dataset.statusId;
    const newStatus = this.closest('.kanban-column').dataset.status;
    
    // Update activity status
    updateActivityStatus(activityId, newStatusId);
    
    // Move the card visually
    const draggedElement = document.querySelector(`[data-activity-id="${activityId}"]`);
    if (draggedElement) {
        this.appendChild(draggedElement);
        updateColumnCounts();
    }
}

// Update column counts
function updateColumnCounts() {
    const columns = document.querySelectorAll('.kanban-column');
    columns.forEach(column => {
        const count = column.querySelectorAll('.activity-card').length;
        const countElement = column.querySelector('.kanban-count');
        if (countElement) {
            countElement.textContent = count;
        }
        
        // Show/hide empty state
        const content = column.querySelector('.kanban-content');
        const emptyState = content.querySelector('.kanban-empty-state');
        
        if (count === 0 && !emptyState) {
            content.innerHTML = '<div class="kanban-empty-state text-center py-4"><i class="ri-inbox-line fs-2 text-muted mb-2"></i><p class="text-muted small mb-0">No activities</p></div>';
        } else if (count > 0 && emptyState) {
            emptyState.remove();
        }
    });
}

// Toggle column visibility
function toggleColumnVisibility() {
    // Implementation for hiding/showing columns
    console.log('Toggle column visibility');
}

// Add new column
function addNewColumn() {
    // Implementation for adding custom columns
    console.log('Add new column');
}

// Initialize kanban when view is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('kanban-view').classList.contains('active')) {
        initializeKanbanDragDrop();
    }
});

// Re-initialize when switching to kanban view
document.addEventListener('viewChanged', function(e) {
    if (e.detail.view === 'kanban') {
        setTimeout(() => {
            initializeKanbanDragDrop();
        }, 100);
    }
});
</script>

<style>
/* Kanban View Specific Styles */
.kanban-board-container {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 1.5rem;
    min-height: 600px;
}

.kanban-board {
    display: flex;
    gap: 1.5rem;
    overflow-x: auto;
    padding-bottom: 1rem;
    min-height: 500px;
    width: 100%;
}

.kanban-column {
    min-width: 300px;
    flex: 1;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    max-height: 600px;
}

.kanban-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1rem 0.5rem 1rem;
    border-bottom: 2px solid #f8f9fa;
    margin-bottom: 1rem;
}

.kanban-count {
    background: #6c757d;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: bold;
}

.kanban-content {
    flex: 1;
    padding: 0 1rem 1rem 1rem;
    overflow-y: auto;
    min-height: 100px;
}

.kanban-content.drag-over {
    background-color: rgba(0, 123, 255, 0.05);
    border: 2px dashed #007bff;
    border-radius: 8px;
}

.activity-card {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 1rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: all 0.2s ease;
    cursor: move;
}

.activity-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}

.activity-card.dragging {
    opacity: 0.5;
    transform: rotate(5deg);
}

.activity-card.border-danger {
    border-left: 4px solid #dc3545;
}

.activity-card.border-warning {
    border-left: 4px solid #ffc107;
}

.activity-card.border-success {
    border-left: 4px solid #28a745;
}

.activity-card.border-secondary {
    border-left: 4px solid #6c757d;
}

.activity-card.bg-danger.bg-opacity-10 {
    background-color: rgba(220, 53, 69, 0.05);
}

.kanban-empty-state {
    color: #6c757d;
}

.activity-tags .badge {
    font-size: 0.7rem;
}

.priority-indicator {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .kanban-board {
        flex-direction: column;
        gap: 1rem;
        overflow-x: visible;
    }
    
    .kanban-column {
        min-width: 100%;
        flex: none;
        max-height: none;
    }
}

/* Scrollbar Styling */
.kanban-content::-webkit-scrollbar {
    width: 6px;
}

.kanban-content::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.kanban-content::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.kanban-content::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
