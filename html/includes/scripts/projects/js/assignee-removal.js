/**
 * Assignee Removal Functionality
 *
 * Handles the removal of assignees from tasks with confirmation modal.
 * This file should be included in pages that use the project plan presentation.
 *
 * @package    PMS
 * @subpackage Projects
 * @author     SKM Development Team
 * @version    3.0.0
 * @since      1.0.0
 */

console.log('Assignee removal script loaded successfully');

// Global variables to store current removal context
let currentRemoveButton = null;
let currentRemoveContainer = null;
let currentRemoveData = null;

/**
 * Create Assignee Removal Modal
 *
 * Creates a Bootstrap modal for confirming assignee removal.
 *
 * @return {HTMLElement} Modal element
 */
function createAssigneeRemovalModal() {
	const modalDiv = document.createElement('div');
	modalDiv.className = 'modal fade';
	modalDiv.id = 'confirmRemoveAssigneeModal';
	modalDiv.setAttribute('tabindex', '-1');
	modalDiv.setAttribute('aria-labelledby', 'confirmRemoveAssigneeModalLabel');
	modalDiv.setAttribute('aria-hidden', 'true');
	modalDiv.style.display = 'none'; // Initially hidden

	modalDiv.innerHTML = `
        <form class="modal-dialog modal-dialog-centered" id="confirmRemoveAssigneeForm" method="post" action="${siteUrl}php/scripts/projects/remove_assignee_data.php">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmRemoveAssigneeModalLabel">
                        <i class="uil uil-exclamation-triangle text-warning me-2"></i>
                        Confirm Removal
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Are you sure you want to remove <strong id="assigneeNameToRemove"></strong> from this task?</p>
						  <input type="hidden" name="assignmentId" id="assignmentId">
						  <input type="hidden" name="assigneeId" id="assigneeId">
						  <input type="hidden" name="taskId" id="taskId">
						  <input type="hidden" name="projectId" id="projectId">
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <i class="uil uil-info-circle me-2"></i>
                        <div>This action cannot be undone. The assignee will be removed from the task immediately.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="uil uil-times me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-danger" id="confirmRemoveBtn">
                        <i class="uil uil-trash-alt me-1"></i>Remove Assignee
                    </button>
                </div>
            </div>
        </form>
    `;

	return modalDiv;
}

/**
 * Remove Assignee From Task
 *
 * Initiates the removal process for an assignee from a task.
 * Shows confirmation modal and handles the removal flow.
 *
 * @param {HTMLElement} button - The delete button that was clicked
 */
function removeAssigneeFromTask(button) {
	console.log('removeAssigneeFromTask called with button:', button);

	const container = button.closest('.assignee-container');
	const assigneeId = container.dataset.assigneeId;
	const assigneeName = container.dataset.assigneeName;
	const assignmentId = button.dataset.assignmentId;

	console.log('Container data:', {
		assigneeId,
		assigneeName,
		assignmentId,
	});

	// Find the task container
	const taskCard = container.closest('.task-card');
	const taskId = taskCard.dataset.taskId;
	const projectId = taskCard.dataset.projectId;

	console.log('Task data:', {
		taskId,
		projectId,
	});

	if (!taskId || !projectId || !assigneeId) {
		console.error('Missing required data for removing assignee');
		showNotification('Missing required data for removing assignee', 'error');
		return;
	}

	// Store current removal context
	currentRemoveButton = button;
	currentRemoveContainer = container;
	currentRemoveData = {
		assigneeId: assigneeId,
		assigneeName: assigneeName,
		assignmentId: assignmentId,
		taskId: taskId,
		projectId: projectId,
	};

	// Check if modal exists, if not create it dynamically
	let modalElement = document.getElementById('confirmRemoveAssigneeModal');
	if (!modalElement) {
		// Create modal dynamically
		modalElement = createAssigneeRemovalModal();
		document.body.appendChild(modalElement);

		// Wait for the modal to be added to DOM
		setTimeout(() => {
			showModal(modalElement, assigneeName, assignmentId);
		}, 100);
	} else {
		showModal(modalElement, assigneeName, assignmentId);
	}
}

/**
 * Show Modal Helper Function
 *
 * Helper function to show the modal with proper error handling.
 *
 * @param {HTMLElement} modalElement - The modal element to show
 * @param {string} assigneeName - The assignee name to display
 */
function showModal(modalElement, assigneeName, assignmentId) {
	// Update modal content
	const assigneeNameElement = document.getElementById('assigneeNameToRemove');
	if (assigneeNameElement) {
		assigneeNameElement.textContent = assigneeName;
	}

	// Get the data from current removal context
	const assigneeId = currentRemoveData.assigneeId;
	const taskId = currentRemoveData.taskId;
	const projectId = currentRemoveData.projectId;

	const assignmentIdElement = document.getElementById('assignmentId');
	if (assignmentIdElement) {
		assignmentIdElement.value = assignmentId;
	}
	const assigneeIdElement = document.getElementById('assigneeId');
	if (assigneeIdElement) {
		assigneeIdElement.value = assigneeId;
	}
	const taskIdElement = document.getElementById('taskId');
	if (taskIdElement) {
		taskIdElement.value = taskId;
	}
	const projectIdElement = document.getElementById('projectId');
	if (projectIdElement) {
		projectIdElement.value = projectId;
	}

	// Ensure modal is visible in DOM
	if (!modalElement.offsetParent && modalElement.style.display !== 'none') {
		modalElement.style.display = 'block';
	}

	// Show confirmation modal
	try {
		// Check if Bootstrap is available
		if (typeof bootstrap === 'undefined') {
			throw new Error('Bootstrap is not loaded');
		}

		// Ensure the modal element is properly attached to the DOM
		if (!document.body.contains(modalElement)) {
			document.body.appendChild(modalElement);
		}

		// Create and show the modal
		const modal = new bootstrap.Modal(modalElement, {
			backdrop: 'static',
			keyboard: false,
		});

		// Show the modal
		modal.show();

		console.log('Modal shown successfully');
	} catch (error) {
		console.error('Error showing modal:', error);
		// Fallback to simple confirm dialog
		if (
			confirm(`Are you sure you want to remove ${assigneeName} from this task?`)
		) {
			// Submit the form directly
			const form = document.getElementById('confirmRemoveAssigneeForm');
			if (form) {
				form.submit();
			}
		}
	}
}

/**
 * Confirm Remove Assignee
 *
 * This function is no longer needed since we're using form submission.
 * The form will submit to the server and handle the response there.
 */

/**
 * Show Notification
 *
 * Displays a notification message to the user.
 *
 * @param {string} message - The message to display
 * @param {string} type - The type of notification (success, error, info)
 */
function showNotification(message, type = 'info') {
	// Create notification element
	const notification = document.createElement('div');
	notification.className = `alert alert-${
		type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'
	} alert-dismissible fade show position-fixed`;
	notification.style.cssText =
		'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
	notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

	document.body.appendChild(notification);

	// Auto remove after 3 seconds
	setTimeout(() => {
		if (notification.parentNode) {
			notification.remove();
		}
	}, 3000);
}

// Initialize tooltips and modal event listeners
document.addEventListener('DOMContentLoaded', function () {
	// Re-initialize tooltips when new content is added
	const tooltipTriggerList = [].slice.call(
		document.querySelectorAll('[data-bs-toggle="tooltip"]')
	);
	tooltipTriggerList.map(function (tooltipTriggerEl) {
		return new bootstrap.Tooltip(tooltipTriggerEl);
	});

	// Add event listener for assignee delete buttons using event delegation
	document.addEventListener('click', function (e) {
		// Check if clicked element is an assignee delete button
		if (e.target && e.target.classList.contains('removeAssigneeFromTask')) {
			removeAssigneeFromTask(e.target);
		}

		// Check if clicked element is inside an assignee delete button
		if (e.target && e.target.closest('.removeAssigneeFromTask')) {
			removeAssigneeFromTask(e.target.closest('.removeAssigneeFromTask'));
		}

		// Form submission is now handled by the browser automatically
		// No need for manual event handling
	});
});
