# Sales Case Notes & Next Steps UI Implementation Plan

## Overview
Comprehensive implementation of UI components, notifications, and reporting for sales case notes and next steps.

## Phase 1: UI Components for sale_details.php ✅ (Priority 1)

### 1.1 Notes Tab Section
**Location:** Add to existing tabs in sale_details.php
**Components:**
- Notes list display (with privacy indicators)
- Add note form (general/private toggle)
- Recipient selector (for private notes)
- Delete note functionality

### 1.2 Activities/Next Steps Tab Section
**Location:** Integrate into Activities tab
**Components:**
- Next steps kanban board (pending, in progress, completed)
- Add next step form
- Priority and due date indicators
- Assignment functionality
- Status update controls

## Phase 2: Notification System ✅ (Priority 2)

### 2.1 Database Tables
**File:** `database/migrations/create_sales_notes_notifications.sql`
**Tables:**
- Add notification event types to existing notification system
- Link to sales case notes and next steps

### 2.2 Notification Events
**Events to create:**
1. `sales_case_note_created` - When note is added
2. `sales_case_note_private_shared` - When private note is shared with user
3. `sales_case_next_step_assigned` - When next step is assigned
4. `sales_case_next_step_due_soon` - 24 hours before due date
5. `sales_case_next_step_overdue` - When past due date
6. `sales_case_next_step_completed` - When marked complete

### 2.3 Notification Templates
**File:** Create email/in-app templates for each event

## Phase 3: Backend API ✅ (Priority 3)

### 3.1 API Endpoint
**File:** `php/scripts/sales/manage_sales_case_notes.php`
**Actions:**
- `addNote` - Create note with recipients
- `getNotes` - Fetch notes (privacy filtered)
- `deleteNote` - Soft delete note
- `addNextStep` - Create next step
- `getNextSteps` - Fetch next steps
- `updateNextStepStatus` - Change status
- `deleteNextStep` - Soft delete next step

## Phase 4: Reporting Dashboard ✅ (Priority 4)

### 4.1 Dashboard Page
**File:** `html/pages/user/sales/notes_next_steps_dashboard.php`
**Widgets:**
- My assigned next steps (pending/overdue)
- Team next steps completion rate
- Recent notes activity
- Overdue next steps by team member
- Next steps by priority distribution

### 4.2 Charts & Visualizations
- Next steps completion trend (line chart)
- Priority distribution (pie chart)
- Team member workload (bar chart)
- Notes activity timeline

## Implementation Order

### Step 1: Create API Endpoint (30 min)
- Create `manage_sales_case_notes.php`
- Implement all CRUD operations
- Add validation and security

### Step 2: Add Notes UI to sale_details.php (45 min)
- Add Notes tab
- Create add note form
- Display notes list
- Implement delete functionality

### Step 3: Add Next Steps UI to Activities Tab (60 min)
- Create kanban-style layout
- Add next step form
- Implement status updates
- Add assignment controls

### Step 4: Notification System (45 min)
- Create notification events table entries
- Create notification templates
- Implement trigger logic in API

### Step 5: Reporting Dashboard (60 min)
- Create dashboard page
- Implement data queries
- Add charts and visualizations
- Create export functionality

## Files to Create/Modify

### Create:
1. `php/scripts/sales/manage_sales_case_notes.php` - API endpoint
2. `html/includes/scripts/sales/modals/add_note_modal.php` - Add note modal
3. `html/includes/scripts/sales/modals/add_next_step_modal.php` - Add next step modal
4. `html/pages/user/sales/notes_next_steps_dashboard.php` - Dashboard
5. `database/migrations/add_sales_notes_notification_events.sql` - Notification events
6. `html/includes/notification_templates/sales_case_note_created.php` - Template
7. `html/includes/notification_templates/sales_case_next_step_assigned.php` - Template

### Modify:
1. `html/pages/user/sales/sale_details.php` - Add tabs and UI components

## Technical Requirements

### Frontend:
- Bootstrap 5 tabs
- SweetAlert2 for confirmations
- Flatpickr for date selection
- Select2 for recipient selection
- Chart.js for dashboard visualizations

### Backend:
- PHP 7.4+
- MySQL 5.7+
- Existing Sales class methods
- Notification system integration

### Security:
- Privacy filtering for notes
- User permission checks
- CSRF protection
- Input sanitization

## Success Criteria

✅ Users can add general and private notes
✅ Private notes only visible to authorized users
✅ Next steps can be created and assigned
✅ Status updates work smoothly
✅ Notifications sent for assignments
✅ Dashboard shows real-time data
✅ All operations are secure and validated

## Estimated Total Time: 4-5 hours

Let's begin implementation!
