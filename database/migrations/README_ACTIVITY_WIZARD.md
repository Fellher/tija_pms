# Activity Wizard Implementation

## Overview
This document details the comprehensive activity management wizard implementation with a 5-step process covering all aspects of activity tracking from inception to completion.

## Database Changes

### Migration File
**File:** `add_activity_wizard_fields.sql`

### New Fields Added to `tija_activities`
1. `meetingLink` - VARCHAR(500) - Virtual meeting URL (Zoom, Teams, etc.)
2. `activityNotes` - TEXT - Additional notes and agenda
3. `activityOutcome` - VARCHAR(100) - Result of the activity
4. `activityResult` - TEXT - Detailed results and key takeaways
5. `activityCost` - DECIMAL(15,2) - Expense tracking
6. `costCategory` - VARCHAR(100) - Category of expense
7. `costNotes` - TEXT - Expense notes
8. `followUpNotes` - TEXT - Follow-up action items
9. `requiresFollowUp` - ENUM('Y','N') - Follow-up flag
10. `sendReminder` - ENUM('Y','N') - Reminder flag
11. `reminderTime` - INT - Minutes before activity
12. `allDayEvent` - ENUM('Y','N') - All-day event flag
13. `duration` - INT - Duration in minutes (calculated)

### New Tables Created
1. **`tija_activity_history`** - Audit trail for activity changes
   - Tracks field changes, who changed them, and when

2. **`tija_activity_attachments`** - File attachments
   - Store documents, proposals, and other files

3. **`tija_activity_reminders`** - Automated reminders
   - Email, SMS, and notification reminders

4. **`tija_activity_comments`** - Threaded comments
   - Discussion and collaboration on activities

### Indexes Added
- `idx_activities_outcome` - Performance for filtering by outcome
- `idx_activities_status` - Performance for status queries
- `idx_activities_date` - Performance for date range queries
- `idx_activities_owner` - Performance for owner lookups
- `idx_activities_sales` - Performance for sales case queries

## Implementation Details

### Frontend Files

#### 1. Modal Template
**File:** `html/includes/scripts/sales/modals/manage_sale_activity.php`

**Features:**
- 5-step wizard interface
- Tom Select multi-select for participants
- Flatpickr date/time pickers
- Dynamic category-type filtering
- Comprehensive validation
- Real-time summary updates
- Responsive design

#### 2. Page Integration
**File:** `html/pages/user/sales/sale_details.php`

**Changes:**
- Updated modal size to `modal-xl`
- Added scrollable dialog class
- Updated form action to new backend

### Backend Files

#### 1. Activity Processing
**File:** `php/scripts/sales/manage_activity_wizard.php`

**Features:**
- Comprehensive field handling
- Create and update support
- Reminder creation
- Transaction support
- Error handling
- Return URL management

#### 2. Activity Fetching
**File:** `php/scripts/sales/get_activity.php`

**Features:**
- JSON API endpoint
- Fetches complete activity data
- Support for edit mode
- Security validation

## Wizard Steps

### Step 1: Activity Details
- Activity Name (required)
- Category & Type (required, filtered)
- Client Selection
- Priority Level
- Status
- Description
- Sales Context Display

### Step 2: Schedule & Timeline
- Activity Date (required)
- Duration Type (One-time, Duration, Recurring)
- Start & End Time
- Duration Calculation
- Recurrence Settings
  - Pattern (Daily, Weekly, Monthly, Custom)
  - Interval
  - End Conditions
- All-day Event Option

### Step 3: Additional Details
- Activity Owner (required)
- Participants (Tom Select multi-select)
- Location
- Meeting Link
- Notes & Agenda
- Reminder Settings

### Step 4: Outcomes & Expenses
- Activity Outcome
- Results & Takeaways
- Cost/Expense Tracking
- Cost Category
- Expense Notes
- Follow-up Requirements

### Step 5: Review Summary ✨ NEW
- Complete activity overview
- All fields displayed in organized sections
- Edit capability from summary
- Print functionality
- Visual badges for status/priority

## Features

### Category-Type Filtering
- Dynamic filtering of activity types based on selected category
- Auto-selection for single options
- Visual indicators (Step 1 blue, Step 2 green)
- Smart placeholder updates

### Tom Select Integration
- Modern multi-select for participants
- Search functionality
- Avatar initials display
- Tag-based selection
- Auto-loads from CDN if not available

### Smart Scrolling
- Modal height limited to 90vh
- Custom scrollbar styling
- Sticky wizard progress
- Sticky navigation buttons
- Scroll-to-top button
- Shadow indicators

### Validation
- Required field checking
- Time range validation
- Real-time feedback
- Step-by-step validation

### Responsive Design
- Desktop, tablet, mobile optimized
- Touch-friendly controls
- Responsive wizard layout
- Adaptive button sizing

## Installation Steps

### 1. Run Database Migration
```bash
mysql -u your_username -p your_database < database/migrations/add_activity_wizard_fields.sql
```

### 2. Verify Tables
Check that all new tables and fields exist:
```sql
SHOW COLUMNS FROM tija_activities LIKE '%meetingLink%';
SHOW TABLES LIKE '%activity_%';
```

### 3. Test Frontend
1. Navigate to sales case details page
2. Click "Add Activity" button
3. Verify all 5 wizard steps load
4. Test form submission

### 4. Verify Backend
1. Create a test activity
2. Check database for new record in `tija_activities`
3. Verify all fields are populated
4. Test update functionality

## Dependencies

### Required Libraries
1. **Bootstrap 5** - Modal and form components
2. **Flatpickr** - Date/time pickers
3. **Tom Select** - Enhanced multi-select
4. **Remixicon** - Icon library

### CDN Fallbacks
Tom Select auto-loads from CDN if not available:
- CSS: `https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.css`
- JS: `https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js`

## API Endpoints

### Get Activity
**Endpoint:** `php/scripts/sales/get_activity.php`
**Method:** GET
**Parameters:**
- `activityID` (required)

**Response:**
```json
{
  "success": true,
  "message": "Activity loaded successfully",
  "activity": {
    "activityID": "123",
    "activityName": "Client Meeting",
    "activityCategoryID": "2",
    "activityTypeID": "5",
    ...
  }
}
```

### Save Activity
**Endpoint:** `php/scripts/sales/manage_activity_wizard.php`
**Method:** POST
**Parameters:** All form fields from wizard

## Timeline Integration

### Enhanced Timeline Display
**File:** `html/pages/user/sales/sale_details.php`

**Features:**
- Milestone markers (Start, End)
- Activity type icons
- Status badges
- Metadata display (owner, location, outcome, cost)
- Edit functionality
- Chronological sorting

## Security

### Input Sanitization
- All inputs cleaned with `Utility::clean_string()`
- JSON encoding for arrays
- Float validation for costs
- Enum validation for flags

### Authentication
- Valid user check
- Session management
- Transaction support (rollback on error)

### SQL Injection Prevention
- Prepared statements
- Parameter binding
- Database class methods

## Performance Optimizations

### Database
- Indexed key fields
- Efficient joins
- Transaction batching

### Frontend
- Lazy loading of Tom Select
- Debounced scroll detection
- CSS-only animations where possible
- Minimal JavaScript execution

## Future Enhancements

### Planned Features
1. Activity attachments upload
2. Inline comment threads
3. Activity templates
4. Bulk operations
5. Advanced recurrence patterns
6. Activity analytics dashboard
7. Email/SMS reminders automation
8. Activity export (PDF, Excel)

## Support

### Common Issues

**Issue:** Tom Select not loading
**Solution:** Check browser console, CDN may be blocked. Add Tom Select to local assets.

**Issue:** Date picker not working
**Solution:** Verify Flatpickr is loaded before modal initialization.

**Issue:** Form submission fails
**Solution:** Check PHP error logs, verify database fields exist.

**Issue:** Timeline not showing activities
**Solution:** Verify `tija_activities` table has data, check query conditions.

## Version History

### v2.0.0 (2025-12-02)
- Initial comprehensive wizard implementation
- 5-step wizard with summary
- Database schema updates
- Tom Select integration
- Enhanced timeline display
- Complete backend processing

## Contributors
- Tija CRM Development Team

## License
Proprietary - Tija CRM System


