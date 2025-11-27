# Leave Management Modals Documentation

## Overview

This package provides three comprehensive modals for leave management functionality:

1. **Apply Leave Modal** - Multi-step leave application form
2. **Leave Calendar Modal** - Interactive calendar view for leave management
3. **Approval Workflow Modal** - Multi-level approval interface

## Files Structure

```
html/pages/user/leave/modals/
├── apply_leave_modal.php              # Main apply leave modal
├── apply_leave_modal_steps.php       # Modal steps (steps 2-4)
├── apply_leave_modal_scripts.php     # CSS and JavaScript for apply modal
├── leave_calendar_modal.php          # Calendar modal with full functionality
├── approval_workflow_modal.php        # Approval workflow modal
├── leave_modals_include.php           # Main include file with all modals
└── README.md                          # This documentation
```

## Quick Start

### 1. Include the Modals

Add this line to your leave management page:

```php
<?php include 'modals/leave_modals_include.php'; ?>
```

### 2. Add Trigger Buttons (No Inline Handlers)

```html
<!-- Apply Leave Button -->
<button
	type="button"
	class="btn btn-primary"
	data-action="open-apply-leave-modal"
>
	<i class="ri-calendar-add-line me-1"></i>Apply Leave
</button>

<!-- Leave Calendar Button -->
<button
	type="button"
	class="btn btn-info"
	data-action="open-leave-calendar-modal"
>
	<i class="ri-calendar-line me-1"></i>View Calendar
</button>

<!-- Approval Workflow Button -->
<button
	type="button"
	class="btn btn-warning"
	data-action="open-approval-workflow-modal"
>
	<i class="ri-user-settings-line me-1"></i>Approvals
</button>
```

Attach the handlers once during page load:

```javascript
document.addEventListener('DOMContentLoaded', () => {
	document.querySelectorAll('[data-action="open-apply-leave-modal"]').forEach(button => {
		button.addEventListener('click', openApplyLeaveModal);
	});

	document.querySelectorAll('[data-action="open-leave-calendar-modal"]').forEach(button => {
		button.addEventListener('click', openLeaveCalendarModal);
	});

	document.querySelectorAll('[data-action="open-approval-workflow-modal"]').forEach(button => {
		button.addEventListener('click', openApprovalWorkflowModal);
	});
});
```

### 3. Required Variables

Ensure these variables are available in your page:

```php
$employeeDetails = Employee::employees(array('ID'=>$userDetails->ID), true, $DBConn);
$orgDataID = $employeeDetails->orgDataID;
$entityID = $employeeDetails->entityID;
$leaveTypes = Leave::leave_types(array('Lapsed'=>'N'), false, $DBConn);
$leaveEntitlements = Leave::leave_entitlements(array('Suspended'=>'N', 'entityID'=>$entityID), false, $DBConn);
$myLeaveApplications = Leave::leave_applications_full(array('Suspended'=>'N', 'employeeID'=>$userDetails->ID), false, $DBConn);
```

## Modal Details

### 1. Apply Leave Modal

**Features:**

- 4-step application process
- Real-time leave balance checking
- Date validation and calculation
- Half-day leave support
- File upload for supporting documents
- Approval workflow preview

**Usage:**

```javascript
document.addEventListener('DOMContentLoaded', () => {
	window.leaveUI?.bindWizardNavigation?.({
		onNext: step => nextStep(step),
		onPrev: step => prevStep(step),
		onSubmit: submitLeaveApplication
	});
});
```

**Form Steps:**

1. **Leave Type Selection** - Choose from available leave types
2. **Date Selection** - Pick start/end dates with validation
3. **Additional Details** - Reason, emergency contact, handover notes
4. **Review & Submit** - Review all details and submit

### 2. Leave Calendar Modal

**Features:**

- Month/Week/Day view modes
- Team leave visibility
- Holiday integration
- Interactive event clicking
- Leave details modal integration
- Filtering by team members

**Usage:**

```javascript
document.addEventListener('DOMContentLoaded', () => {
	const prevButtons = document.querySelectorAll('[data-action="calendar-prev-month"]');
	prevButtons.forEach(button => button.addEventListener('click', previousMonth));

	const nextButtons = document.querySelectorAll('[data-action="calendar-next-month"]');
	nextButtons.forEach(button => button.addEventListener('click', nextMonth));

	document.querySelectorAll('[data-action="calendar-go-today"]').forEach(button => {
		button.addEventListener('click', goToToday);
	});
});
```

**Calendar Views:**

- **Month View** - Full month calendar with events
- **Week View** - Detailed week view with time slots
- **Day View** - Single day with detailed event list

### 3. Approval Workflow Modal

**Features:**

- Pending/Approved/Rejected filter tabs
- Detailed application review
- Multi-level approval workflow visualization
- Approval comments and history
- Document viewing and download
- Approval decision submission

**Usage:**

```javascript
document.addEventListener('DOMContentLoaded', () => {
	document.querySelectorAll('[data-action="approval-approve"]').forEach(button => {
		button.addEventListener('click', approveLeave);
	});

	document.querySelectorAll('[data-action="approval-reject"]').forEach(button => {
		button.addEventListener('click', rejectLeave);
	});

	document.querySelectorAll('[data-action="approval-request-info"]').forEach(button => {
		button.addEventListener('click', requestMoreInfo);
	});

	document.querySelectorAll('[data-action="approval-download"]').forEach(button => {
		button.addEventListener('click', downloadApplication);
	});
});
```

**Approval Actions:**

- **Approve** - Approve the leave application
- **Reject** - Reject with required reason
- **Request More Info** - Request additional information

## Global Functions

The modals provide several global utility functions:

```javascript
// Modal opening functions
openApplyLeaveModal();
openLeaveCalendarModal();
openApprovalWorkflowModal();

// Utility functions
refreshLeaveData(); // Refresh leave data after operations
showToast('success', 'Title', 'Message'); // Show toast notifications
formatDate(date); // Format date for display
formatDateTime(date); // Format date and time
calculateDaysBetween(start, end); // Calculate days between dates
isWeekend(date); // Check if date is weekend
getLeaveStatusColor(status); // Get Bootstrap color for status
getLeaveStatusIcon(status); // Get icon for status
```

## Keyboard Shortcuts

- **Ctrl/Cmd + L** - Open leave calendar
- **Ctrl/Cmd + Shift + L** - Apply leave
- **Ctrl/Cmd + Shift + A** - Open approvals

## Required PHP Scripts

The modals expect these PHP scripts to exist:

```
php/scripts/leave/
├── submit_leave_application.php       # Submit new leave application
├── get_approval_workflow.php          # Get approval workflow data
├── get_approvals.php                  # Get approvals for user
├── get_approval_details.php           # Get detailed approval information
├── submit_approval_decision.php       # Submit approval decision
├── get_leave_details.php              # Get leave application details
├── cancel_leave_application.php       # Cancel leave application
├── download_leave_application.php      # Download application PDF
└── download_document.php              # Download supporting documents
```

## Styling

The modals include comprehensive CSS styling that:

- Uses Bootstrap 5 classes
- Provides responsive design
- Includes custom animations
- Supports dark/light themes
- Provides loading states

## Event Handling

The modals emit custom events for integration:

```javascript
// Listen for leave data updates
window.addEventListener('leaveDataUpdated', function () {
	// Refresh your page data
	location.reload();
});

// Access global functions
window.LeaveManagement.openApplyLeaveModal();
```

## Customization

### Customizing Colors

Override CSS variables:

```css
:root {
	--bs-primary: #your-color;
	--bs-success: #your-color;
	--bs-warning: #your-color;
	--bs-danger: #your-color;
}
```

### Customizing Icons

Replace Remix Icon classes with your preferred icon library:

```html
<i class="your-icon-class"></i>
```

### Adding Custom Fields

Extend the apply leave form by modifying `apply_leave_modal.php`:

```html
<!-- Add to step 3 -->
<div class="col-12">
	<label
		for="customField"
		class="form-label"
		>Custom Field</label
	>
	<input
		type="text"
		class="form-control"
		id="customField"
		name="customField"
	/>
</div>
```

## Error Handling

The modals include comprehensive error handling:

- Form validation with user-friendly messages
- Network error handling with retry options
- Server error display with actionable messages
- Loading states for all async operations

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Dependencies

- Bootstrap 5.0+
- Remix Icons
- PHP 7.4+
- MySQL 5.7+

## Troubleshooting

### Common Issues

1. **Modals not opening**

   - Check if Bootstrap JavaScript is loaded
   - Verify modal IDs match function calls

2. **Form submission errors**

   - Check PHP script paths
   - Verify required variables are set
   - Check database connections

3. **Calendar not loading**
   - Verify leave data is properly formatted
   - Check JavaScript console for errors

### Debug Mode

Enable debug mode by adding to your page:

```javascript
window.LeaveManagement.debug = true;
```

This will log additional information to the console.

## Support

For issues or questions:

1. Check the browser console for JavaScript errors
2. Verify all required PHP scripts exist
3. Ensure database tables are properly set up
4. Check file permissions

## License

This code is part of the PMS system and follows the same licensing terms.
