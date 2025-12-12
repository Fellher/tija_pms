# Sales Case Notes & Next Steps - Integration Guide

## Files Created ✅

### 1. API Endpoint
**File:** `php/scripts/sales/manage_sales_case_notes.php`
**Purpose:** Backend API for all CRUD operations
**Actions:**
- `addNote` - Create note
- `getNotes` - Fetch notes (privacy filtered)
- `deleteNote` - Delete note
- `addNextStep` - Create next step
- `getNextSteps` - Fetch next steps
- `updateNextStepStatus` - Update status
- `deleteNextStep` - Delete next step

### 2. Add Note Modal
**File:** `html/includes/scripts/sales/modals/add_note_modal.php`
**Features:**
- General/Private note toggle
- Recipient selection for private notes
- Form validation
- Success/error handling

### 3. Add Next Step Modal
**File:** `html/includes/scripts/sales/modals/add_next_step_modal.php`
**Features:**
- Description field
- Due date picker (Flatpickr)
- Priority selection
- User assignment
- Form validation

## Integration Steps for sale_details.php

### Step 1: Include Modals (Add after existing modals, around line 146)

```php
// Add after existing modal includes
include "includes/scripts/sales/modals/add_note_modal.php";
include "includes/scripts/sales/modals/add_next_step_modal.php";
```

### Step 2: Add Notes Tab (Find the tabs section, add new tab)

```html
<!-- Add to tab navigation (around line 300-400) -->
<li class="nav-item">
    <a class="nav-link" data-bs-toggle="tab" href="#notes-tab" role="tab">
        <i class="ri-sticky-note-line me-1"></i>Notes
    </a>
</li>
```

### Step 3: Add Notes Tab Content (Add to tab content section)

```html
<!-- Notes Tab -->
<div class="tab-pane" id="notes-tab" role="tabpanel">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0">
                <i class="ri-sticky-note-line me-2"></i>Sales Case Notes
            </h6>
            <button type="button" class="btn btn-primary btn-sm"
                    onclick="openAddNoteModal(<?= $salesCaseID ?>, <?= $salesCaseDetails->saleStatusLevelID ?>)">
                <i class="ri-add-line me-1"></i>Add Note
            </button>
        </div>
        <div class="card-body">
            <div id="notesList">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

### Step 4: Add JavaScript for Notes (Add to bottom of page, before closing </script>)

```javascript
// Load notes function
function loadNotes() {
    const notesList = document.getElementById('notesList');
    if (!notesList) return;

    notesList.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';

    fetch('<?= "{$base}php/scripts/sales/manage_sales_case_notes.php" ?>?action=getNotes&salesCaseID=<?= $salesCaseID ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.notes && data.notes.length > 0) {
                let notesHTML = '';
                data.notes.forEach(note => {
                    const noteDate = new Date(note.DateAdded).toLocaleString();
                    const isPrivate = note.isPrivate === 'Y';

                    notesHTML += `
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">${note.addedByName || 'Unknown User'}</h6>
                                        <small class="text-muted">${noteDate}</small>
                                        ${note.stageName ? `<span class="badge bg-info ms-2">${note.stageName}</span>` : ''}
                                    </div>
                                    <div>
                                        ${isPrivate ? '<span class="badge bg-warning"><i class="ri-lock-line me-1"></i>Private</span>' : '<span class="badge bg-success"><i class="ri-team-line me-1"></i>Team</span>'}
                                        ${note.createdByID == <?= $userID ?> ? `<button class="btn btn-sm btn-danger ms-2" onclick="deleteNote(${note.salesCaseNoteID})"><i class="ri-delete-bin-line"></i></button>` : ''}
                                    </div>
                                </div>
                                <p class="mb-0">${note.noteText || ''}</p>
                            </div>
                        </div>
                    `;
                });
                notesList.innerHTML = notesHTML;
            } else {
                notesList.innerHTML = '<div class="alert alert-info"><i class="ri-information-line me-2"></i>No notes yet. Add your first note!</div>';
            }
        })
        .catch(error => {
            console.error('Error loading notes:', error);
            notesList.innerHTML = '<div class="alert alert-danger">Error loading notes</div>';
        });
}

// Delete note function
function deleteNote(noteID) {
    Swal.fire({
        title: 'Delete Note?',
        text: 'This action cannot be undone',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'deleteNote');
            formData.append('noteID', noteID);

            fetch('<?= "{$base}php/scripts/sales/manage_sales_case_notes.php" ?>', {
                method: 'POST',
                body: new URLSearchParams(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', data.message, 'success');
                    loadNotes();
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        }
    });
}

// Load notes when Notes tab is shown
document.querySelector('.nav-link[href="#notes-tab"]')?.addEventListener('shown.bs.tab', function() {
    loadNotes();
});

// Load notes if Notes tab is active on page load
if (window.location.hash === '#notes-tab') {
    loadNotes();
}
```

### Step 5: Add Next Steps to Activities Tab

Find the Activities tab content and add:

```html
<!-- Add to Activities tab content -->
<div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="card-title mb-0">
            <i class="ri-task-line me-2"></i>Next Steps
        </h6>
        <button type="button" class="btn btn-success btn-sm"
                onclick="openAddNextStepModal(<?= $salesCaseID ?>, <?= $salesCaseDetails->saleStatusLevelID ?>)">
            <i class="ri-add-line me-1"></i>Add Next Step
        </button>
    </div>
    <div class="card-body">
        <div id="nextStepsList">
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>
</div>
```

### Step 6: Add JavaScript for Next Steps

```javascript
// Load next steps function
function loadNextSteps() {
    const stepsList = document.getElementById('nextStepsList');
    if (!stepsList) return;

    stepsList.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>';

    fetch('<?= "{$base}php/scripts/sales/manage_sales_case_notes.php" ?>?action=getNextSteps&salesCaseID=<?= $salesCaseID ?>')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.steps && data.steps.length > 0) {
                let stepsHTML = '<div class="row">';

                // Group by status
                const statuses = ['pending', 'in_progress', 'completed'];
                const statusLabels = {
                    'pending': 'Pending',
                    'in_progress': 'In Progress',
                    'completed': 'Completed'
                };

                statuses.forEach(status => {
                    const statusSteps = data.steps.filter(s => s.status === status);

                    stepsHTML += `
                        <div class="col-md-4">
                            <h6 class="text-muted mb-3">${statusLabels[status]} (${statusSteps.length})</h6>
                    `;

                    if (statusSteps.length > 0) {
                        statusSteps.forEach(step => {
                            const priorityColors = {
                                'urgent': 'danger',
                                'high': 'warning',
                                'medium': 'info',
                                'low': 'secondary'
                            };

                            stepsHTML += `
                                <div class="card mb-2">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="badge bg-${priorityColors[step.priority]}">${step.priority}</span>
                                            ${step.dueDate ? `<small class="text-muted">${new Date(step.dueDate).toLocaleDateString()}</small>` : ''}
                                        </div>
                                        <p class="mb-2">${step.nextStepDescription}</p>
                                        <small class="text-muted">
                                            ${step.assignedToName ? `Assigned to: ${step.assignedToName}` : 'Unassigned'}
                                        </small>
                                        <div class="mt-2">
                                            ${status !== 'completed' ? `
                                                <button class="btn btn-sm btn-success" onclick="updateStepStatus(${step.salesCaseNextStepID}, '${status === 'pending' ? 'in_progress' : 'completed'}')">
                                                    ${status === 'pending' ? 'Start' : 'Complete'}
                                                </button>
                                            ` : ''}
                                            <button class="btn btn-sm btn-danger" onclick="deleteNextStep(${step.salesCaseNextStepID})">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                    } else {
                        stepsHTML += '<p class="text-muted">No steps</p>';
                    }

                    stepsHTML += '</div>';
                });

                stepsHTML += '</div>';
                stepsList.innerHTML = stepsHTML;
            } else {
                stepsList.innerHTML = '<div class="alert alert-info">No next steps yet. Add your first action item!</div>';
            }
        })
        .catch(error => {
            console.error('Error loading next steps:', error);
            stepsList.innerHTML = '<div class="alert alert-danger">Error loading next steps</div>';
        });
}

// Update step status
function updateStepStatus(stepID, status) {
    const formData = new FormData();
    formData.append('action', 'updateNextStepStatus');
    formData.append('stepID', stepID);
    formData.append('status', status);

    fetch('<?= "{$base}php/scripts/sales/manage_sales_case_notes.php" ?>', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadNextSteps();
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    });
}

// Delete next step
function deleteNextStep(stepID) {
    Swal.fire({
        title: 'Delete Next Step?',
        text: 'This action cannot be undone',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('action', 'deleteNextStep');
            formData.append('stepID', stepID);

            fetch('<?= "{$base}php/scripts/sales/manage_sales_case_notes.php" ?>', {
                method: 'POST',
                body: new URLSearchParams(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', data.message, 'success');
                    loadNextSteps();
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        }
    });
}

// Load next steps when page loads or Activities tab is shown
loadNextSteps();
```

## Quick Start Checklist

- [ ] 1. Run database migration: `create_sales_case_notes_and_next_steps.sql`
- [ ] 2. Verify API endpoint is accessible
- [ ] 3. Include modal files in sale_details.php
- [ ] 4. Add Notes tab to navigation
- [ ] 5. Add Notes tab content
- [ ] 6. Add JavaScript for notes
- [ ] 7. Add Next Steps section to Activities tab
- [ ] 8. Add JavaScript for next steps
- [ ] 9. Test adding notes (general and private)
- [ ] 10. Test adding next steps
- [ ] 11. Test status updates
- [ ] 12. Test delete functionality

## Testing

### Test Notes:
1. Add a general note - should appear for all users
2. Add a private note with recipients - should only appear for selected users
3. Delete your own note - should work
4. Try to delete someone else's note - should fail

### Test Next Steps:
1. Add a next step with assignment
2. Move from Pending → In Progress → Completed
3. Test priority levels display correctly
4. Test due date display
5. Delete a next step

## Features Included

✅ Add/view/delete notes
✅ General and private notes
✅ Recipient selection for private notes
✅ Add/view/delete next steps
✅ Kanban-style status board
✅ Priority levels (urgent, high, medium, low)
✅ User assignment
✅ Due date tracking
✅ Status updates (pending → in progress → completed)
✅ Privacy filtering (users only see notes they have access to)
✅ Soft delete (data preserved)

## Next Phase (Optional)

- Notifications for assignments
- Email alerts for overdue steps
- Reporting dashboard
- Read receipts for private notes
- Bulk operations
- Export functionality
