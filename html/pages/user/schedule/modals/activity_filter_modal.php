<?php
/**
 * Activity Filter Modal
 * 
 * Provides advanced filtering options for activities
 * Features:
 * - Date range filtering
 * - Status filtering
 * - Category filtering
 * - Priority filtering
 * - Assignee filtering
 * - Project filtering
 * - Saved filter presets
 */

?>

<!-- Activity Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="filterModalLabel">
                    <i class="ri-filter-line me-2"></i>
                    Filter Activities
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="filterForm">
                <div class="modal-body">
                    <!-- Quick Filter Presets -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="ri-bookmark-line me-2"></i>
                                Quick Filters
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="applyQuickFilter('today')">
                                            <i class="ri-calendar-line me-2"></i>Today's Activities
                                        </button>
                                        <button type="button" class="btn btn-outline-warning btn-sm" onclick="applyQuickFilter('overdue')">
                                            <i class="ri-time-line me-2"></i>Overdue
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-sm" onclick="applyQuickFilter('completed')">
                                            <i class="ri-check-double-line me-2"></i>Completed
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-outline-info btn-sm" onclick="applyQuickFilter('my_activities')">
                                            <i class="ri-user-line me-2"></i>My Activities
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="applyQuickFilter('high_priority')">
                                            <i class="ri-flag-line me-2"></i>High Priority
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="applyQuickFilter('this_week')">
                                            <i class="ri-calendar-week-line me-2"></i>This Week
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Left Column - Basic Filters -->
                        <div class="col-md-6">
                            <!-- Date Range -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="ri-calendar-2-line me-2"></i>
                                        Date Range
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="filterStartDate" class="form-label">From Date</label>
                                                <input type="date" class="form-control" id="filterStartDate" name="startDate">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="filterEndDate" class="form-label">To Date</label>
                                                <input type="date" class="form-control" id="filterEndDate" name="endDate">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="filterIncludeOverdue" name="includeOverdue" checked>
                                        <label class="form-check-label" for="filterIncludeOverdue">
                                            Include overdue activities
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Status Filter -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="ri-checkbox-circle-line me-2"></i>
                                        Status
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <?php foreach ($activityStatuses as $status): ?>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input status-filter" 
                                                   type="checkbox" 
                                                   id="status_<?= $status->activityStatusID ?>" 
                                                   name="statuses[]" 
                                                   value="<?= $status->activityStatusID ?>"
                                                   checked>
                                            <label class="form-check-label" for="status_<?= $status->activityStatusID ?>">
                                                <?= htmlspecialchars($status->activityStatusName) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllStatuses(true)">Select All</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllStatuses(false)">Deselect All</button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Priority Filter -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="ri-flag-line me-2"></i>
                                        Priority
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="priority_high" name="priorities[]" value="high">
                                        <label class="form-check-label text-danger" for="priority_high">
                                            <i class="ri-arrow-up-line me-1"></i>High Priority
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="priority_medium" name="priorities[]" value="medium" checked>
                                        <label class="form-check-label text-warning" for="priority_medium">
                                            <i class="ri-subtract-line me-1"></i>Medium Priority
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="priority_low" name="priorities[]" value="low">
                                        <label class="form-check-label text-success" for="priority_low">
                                            <i class="ri-arrow-down-line me-1"></i>Low Priority
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column - Advanced Filters -->
                        <div class="col-md-6">
                            <!-- Category Filter -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="ri-folder-line me-2"></i>
                                        Category
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <input type="text" class="form-control form-control-sm" id="categorySearch" placeholder="Search categories...">
                                    </div>
                                    <div class="category-list" style="max-height: 200px; overflow-y: auto;">
                                        <?php foreach ($activityCategories as $category): ?>
                                            <div class="form-check mb-2 category-item">
                                                <input class="form-check-input category-filter" 
                                                       type="checkbox" 
                                                       id="category_<?= $category->activityCategoryID ?>" 
                                                       name="categories[]" 
                                                       value="<?= $category->activityCategoryID ?>">
                                                <label class="form-check-label" for="category_<?= $category->activityCategoryID ?>">
                                                    <?= htmlspecialchars($category->activityCategoryName) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Assignee Filter -->
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="ri-user-line me-2"></i>
                                        Assignee
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <input type="text" class="form-control form-control-sm" id="assigneeSearch" placeholder="Search assignees...">
                                    </div>
                                    <div class="assignee-list" style="max-height: 150px; overflow-y: auto;">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="assignee_unassigned" name="assignees[]" value="">
                                            <label class="form-check-label" for="assignee_unassigned">
                                                <i class="ri-user-line me-1"></i>Unassigned
                                            </label>
                                        </div>
                                        <?php foreach ($employees as $employee): ?>
                                            <div class="form-check mb-2 assignee-item">
                                                <input class="form-check-input assignee-filter" 
                                                       type="checkbox" 
                                                       id="assignee_<?= $employee->ID ?>" 
                                                       name="assignees[]" 
                                                       value="<?= $employee->ID ?>">
                                                <label class="form-check-label" for="assignee_<?= $employee->ID ?>">
                                                    <?= htmlspecialchars($employee->firstName . ' ' . $employee->lastName) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Project Filter -->
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="ri-projector-line me-2"></i>
                                        Project
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <input type="text" class="form-control form-control-sm" id="projectSearch" placeholder="Search projects...">
                                    </div>
                                    <div class="project-list" style="max-height: 150px; overflow-y: auto;">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="project_none" name="projects[]" value="">
                                            <label class="form-check-label" for="project_none">
                                                <i class="ri-folder-line me-1"></i>No Project
                                            </label>
                                        </div>
                                        <?php foreach ($projects as $project): ?>
                                            <div class="form-check mb-2 project-item">
                                                <input class="form-check-input project-filter" 
                                                       type="checkbox" 
                                                       id="project_<?= $project->projectID ?>" 
                                                       name="projects[]" 
                                                       value="<?= $project->projectID ?>">
                                                <label class="form-check-label" for="project_<?= $project->projectID ?>">
                                                    <?= htmlspecialchars($project->projectName) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Saved Filters -->
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="ri-bookmark-3-line me-2"></i>
                                Saved Filter Presets
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="loadSavedFilter('weekly_review')">
                                            <i class="ri-calendar-week-line me-1"></i>Weekly Review
                                        </button>
                                        <button type="button" class="btn btn-outline-success btn-sm" onclick="loadSavedFilter('completed_this_month')">
                                            <i class="ri-check-double-line me-1"></i>Completed This Month
                                        </button>
                                        <button type="button" class="btn btn-outline-warning btn-sm" onclick="loadSavedFilter('my_overdue')">
                                            <i class="ri-time-line me-1"></i>My Overdue
                                        </button>
                                        <button type="button" class="btn btn-outline-info btn-sm" onclick="loadSavedFilter('high_priority_tasks')">
                                            <i class="ri-flag-line me-1"></i>High Priority Tasks
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4 text-end">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="saveCurrentFilter()">
                                        <i class="ri-save-line me-1"></i>Save Current Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" onclick="clearAllFilters()">
                        <i class="ri-refresh-line me-1"></i>Clear All
                    </button>
                    
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i>Cancel
                    </button>
                    
                    <button type="button" class="btn btn-primary" onclick="applyFilters()">
                        <i class="ri-filter-line me-1"></i>Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Filter Modal JavaScript -->
<script>
/**
 * Activity Filter Modal JavaScript
 */

let currentFilters = {};

document.addEventListener('DOMContentLoaded', function() {
    initializeFilterModal();
});

function initializeFilterModal() {
    // Setup search functionality
    setupSearchFunctionality();
    
    // Load saved filters from localStorage
    loadFiltersFromStorage();
    
    // Setup form interactions
    setupFilterInteractions();
}

// Setup search functionality for filter lists
function setupSearchFunctionality() {
    // Category search
    const categorySearch = document.getElementById('categorySearch');
    if (categorySearch) {
        categorySearch.addEventListener('input', function() {
            filterListItems('.category-item', this.value);
        });
    }
    
    // Assignee search
    const assigneeSearch = document.getElementById('assigneeSearch');
    if (assigneeSearch) {
        assigneeSearch.addEventListener('input', function() {
            filterListItems('.assignee-item', this.value);
        });
    }
    
    // Project search
    const projectSearch = document.getElementById('projectSearch');
    if (projectSearch) {
        projectSearch.addEventListener('input', function() {
            filterListItems('.project-item', this.value);
        });
    }
}

// Filter list items based on search term
function filterListItems(selector, searchTerm) {
    const items = document.querySelectorAll(selector);
    const term = searchTerm.toLowerCase();
    
    items.forEach(item => {
        const label = item.querySelector('label').textContent.toLowerCase();
        if (label.includes(term)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// Setup filter interactions
function setupFilterInteractions() {
    // Auto-save filters on change
    const filterInputs = document.querySelectorAll('#filterForm input, #filterForm select');
    filterInputs.forEach(input => {
        input.addEventListener('change', saveFiltersToStorage);
    });
}

// Apply quick filter
function applyQuickFilter(filterType) {
    // Clear existing filters first
    clearAllFilters();
    
    const today = new Date();
    const startOfWeek = new Date(today);
    startOfWeek.setDate(today.getDate() - today.getDay() + 1); // Monday
    
    switch(filterType) {
        case 'today':
            document.getElementById('filterStartDate').value = formatDate(today);
            document.getElementById('filterEndDate').value = formatDate(today);
            break;
            
        case 'overdue':
            document.getElementById('filterEndDate').value = formatDate(new Date(today.getTime() - 24 * 60 * 60 * 1000));
            // Select only non-completed statuses
            document.querySelectorAll('.status-filter').forEach(checkbox => {
                if (!checkbox.id.includes('4')) { // Not completed
                    checkbox.checked = true;
                }
            });
            break;
            
        case 'completed':
            document.getElementById('status_4').checked = true;
            break;
            
        case 'my_activities':
            document.getElementById('assignee_<?= $userDetails->ID ?>').checked = true;
            break;
            
        case 'high_priority':
            document.getElementById('priority_high').checked = true;
            break;
            
        case 'this_week':
            document.getElementById('filterStartDate').value = formatDate(startOfWeek);
            document.getElementById('filterEndDate').value = formatDate(new Date(startOfWeek.getTime() + 6 * 24 * 60 * 60 * 1000));
            break;
    }
    
    showToast('info', 'Filter Applied', `${filterType.replace('_', ' ')} filter has been applied.`);
}

// Toggle all status checkboxes
function toggleAllStatuses(selectAll) {
    document.querySelectorAll('.status-filter').forEach(checkbox => {
        checkbox.checked = selectAll;
    });
}

// Apply filters
function applyFilters() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    
    // Convert FormData to object
    const filters = {};
    for (let [key, value] of formData.entries()) {
        if (filters[key]) {
            if (Array.isArray(filters[key])) {
                filters[key].push(value);
            } else {
                filters[key] = [filters[key], value];
            }
        } else {
            filters[key] = value;
        }
    }
    
    // Store filters globally
    currentFilters = filters;
    
    // Apply filters to current view
    applyFiltersToView(filters);
    
    // Save to localStorage
    saveFiltersToStorage();
    
    // Close modal
    bootstrap.Modal.getInstance(document.getElementById('filterModal')).hide();
    
    showToast('success', 'Filters Applied', 'Activities have been filtered according to your criteria.');
}

// Apply filters to current view
function applyFiltersToView(filters) {
    const currentView = getCurrentView();
    
    switch(currentView) {
        case 'list':
            applyFiltersToListView(filters);
            break;
        case 'kanban':
            applyFiltersToKanbanView(filters);
            break;
        case 'calendar':
            applyFiltersToCalendarView(filters);
            break;
        case 'analytics':
            applyFiltersToAnalyticsView(filters);
            break;
    }
}

// Apply filters to list view
function applyFiltersToListView(filters) {
    const rows = document.querySelectorAll('.activity-row');
    
    rows.forEach(row => {
        let showRow = true;
        
        // Check date range
        if (filters.startDate || filters.endDate) {
            const dueDate = new Date(row.querySelector('.activity-details').dataset.dueDate);
            if (filters.startDate && dueDate < new Date(filters.startDate)) showRow = false;
            if (filters.endDate && dueDate > new Date(filters.endDate)) showRow = false;
        }
        
        // Check status
        if (filters.statuses) {
            const statusId = row.dataset.statusId;
            if (!filters.statuses.includes(statusId)) showRow = false;
        }
        
        // Check priority
        if (filters.priorities) {
            const priority = row.dataset.priority;
            if (!filters.priorities.includes(priority)) showRow = false;
        }
        
        row.style.display = showRow ? 'table-row' : 'none';
    });
}

// Apply filters to kanban view
function applyFiltersToKanbanView(filters) {
    // Implementation for kanban view filtering
    console.log('Applying filters to kanban view:', filters);
}

// Apply filters to calendar view
function applyFiltersToCalendarView(filters) {
    // Implementation for calendar view filtering
    console.log('Applying filters to calendar view:', filters);
}

// Apply filters to analytics view
function applyFiltersToAnalyticsView(filters) {
    // Implementation for analytics view filtering
    console.log('Applying filters to analytics view:', filters);
}

// Clear all filters
function clearAllFilters() {
    // Clear all form inputs
    document.getElementById('filterForm').reset();
    
    // Clear stored filters
    currentFilters = {};
    localStorage.removeItem('activityFilters');
    
    // Show all activities
    document.querySelectorAll('.activity-row').forEach(row => {
        row.style.display = 'table-row';
    });
    
    showToast('info', 'Filters Cleared', 'All filters have been cleared.');
}

// Save filters to localStorage
function saveFiltersToStorage() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    
    const filters = {};
    for (let [key, value] of formData.entries()) {
        if (filters[key]) {
            if (Array.isArray(filters[key])) {
                filters[key].push(value);
            } else {
                filters[key] = [filters[key], value];
            }
        } else {
            filters[key] = value;
        }
    }
    
    localStorage.setItem('activityFilters', JSON.stringify(filters));
}

// Load filters from localStorage
function loadFiltersFromStorage() {
    const savedFilters = localStorage.getItem('activityFilters');
    if (savedFilters) {
        const filters = JSON.parse(savedFilters);
        applySavedFilters(filters);
    }
}

// Apply saved filters to form
function applySavedFilters(filters) {
    // Apply date filters
    if (filters.startDate) {
        document.getElementById('filterStartDate').value = filters.startDate;
    }
    if (filters.endDate) {
        document.getElementById('filterEndDate').value = filters.endDate;
    }
    
    // Apply status filters
    if (filters.statuses) {
        filters.statuses.forEach(statusId => {
            const checkbox = document.getElementById(`status_${statusId}`);
            if (checkbox) checkbox.checked = true;
        });
    }
    
    // Apply priority filters
    if (filters.priorities) {
        filters.priorities.forEach(priority => {
            const checkbox = document.getElementById(`priority_${priority}`);
            if (checkbox) checkbox.checked = true;
        });
    }
    
    // Apply category filters
    if (filters.categories) {
        filters.categories.forEach(categoryId => {
            const checkbox = document.getElementById(`category_${categoryId}`);
            if (checkbox) checkbox.checked = true;
        });
    }
    
    // Apply assignee filters
    if (filters.assignees) {
        filters.assignees.forEach(assigneeId => {
            const checkbox = document.getElementById(`assignee_${assigneeId}`);
            if (checkbox) checkbox.checked = true;
        });
    }
    
    // Apply project filters
    if (filters.projects) {
        filters.projects.forEach(projectId => {
            const checkbox = document.getElementById(`project_${projectId}`);
            if (checkbox) checkbox.checked = true;
        });
    }
}

// Load saved filter preset
function loadSavedFilter(presetName) {
    const presets = {
        weekly_review: {
            startDate: formatDate(new Date(Date.now() - 7 * 24 * 60 * 60 * 1000)),
            endDate: formatDate(new Date()),
            statuses: ['1', '2', '3'], // To Do, In Progress, Review
            priorities: ['high', 'medium']
        },
        completed_this_month: {
            startDate: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
            endDate: new Date().toISOString().split('T')[0],
            statuses: ['4'] // Completed
        },
        my_overdue: {
            endDate: formatDate(new Date(Date.now() - 24 * 60 * 60 * 1000)),
            statuses: ['1', '2', '3'], // Not completed
            assignees: ['<?= $userDetails->ID ?>']
        },
        high_priority_tasks: {
            priorities: ['high'],
            statuses: ['1', '2', '3'] // Not completed
        }
    };
    
    const preset = presets[presetName];
    if (preset) {
        clearAllFilters();
        applySavedFilters(preset);
        showToast('info', 'Filter Loaded', `${presetName.replace('_', ' ')} filter has been loaded.`);
    }
}

// Save current filter as preset
function saveCurrentFilter() {
    const filterName = prompt('Enter a name for this filter preset:');
    if (filterName) {
        const form = document.getElementById('filterForm');
        const formData = new FormData(form);
        
        const filters = {};
        for (let [key, value] of formData.entries()) {
            if (filters[key]) {
                if (Array.isArray(filters[key])) {
                    filters[key].push(value);
                } else {
                    filters[key] = [filters[key], value];
                }
            } else {
                filters[key] = value;
            }
        }
        
        // Save to localStorage with custom name
        const savedPresets = JSON.parse(localStorage.getItem('customFilterPresets') || '{}');
        savedPresets[filterName] = filters;
        localStorage.setItem('customFilterPresets', JSON.stringify(savedPresets));
        
        showToast('success', 'Filter Saved', `Filter preset "${filterName}" has been saved.`);
    }
}

// Get current view
function getCurrentView() {
    const activeView = document.querySelector('.view-container.active');
    return activeView ? activeView.id.replace('-view', '') : 'list';
}

// Format date for input fields
function formatDate(date) {
    return date.toISOString().split('T')[0];
}
</script>

<style>
/* Filter Modal Specific Styles */
.modal-lg {
    max-width: 800px;
}

.category-list,
.assignee-list,
.project-list {
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 0.5rem;
}

.form-check-label {
    font-size: 0.9rem;
    color: #495057;
}

.form-check-input:checked {
    background-color: #007bff;
    border-color: #007bff;
}

/* Quick filter buttons */
.btn-outline-primary:hover,
.btn-outline-success:hover,
.btn-outline-warning:hover,
.btn-outline-danger:hover,
.btn-outline-info:hover,
.btn-outline-secondary:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Search input styling */
.form-control-sm {
    font-size: 0.85rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .modal-lg {
        max-width: 95%;
    }
    
    .modal-body .row {
        flex-direction: column;
    }
    
    .modal-body .col-md-6 {
        max-width: 100%;
        flex: 0 0 100%;
    }
}

/* Filter list scrollbar */
.category-list::-webkit-scrollbar,
.assignee-list::-webkit-scrollbar,
.project-list::-webkit-scrollbar {
    width: 6px;
}

.category-list::-webkit-scrollbar-track,
.assignee-list::-webkit-scrollbar-track,
.project-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.category-list::-webkit-scrollbar-thumb,
.assignee-list::-webkit-scrollbar-thumb,
.project-list::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.category-list::-webkit-scrollbar-thumb:hover,
.assignee-list::-webkit-scrollbar-thumb:hover,
.project-list::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
