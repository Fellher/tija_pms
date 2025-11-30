<?php
/**
 * Pending Tasks Notification Widget
 *
 * Displays pending scheduled task notifications for manual processing
 * Can be included in dashboard or header
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

// Check authentication
if(!$isValidUser) {
    return;
}

require_once __DIR__ . '/../../../../php/classes/operationaltaskscheduler.php';

global $DBConn, $userID;

// Get pending notifications
$notifications = OperationalTaskScheduler::getPendingTaskNotifications($userID, $DBConn);
$notificationCount = is_array($notifications) ? count($notifications) : 0;

// Only show if there are notifications
if ($notificationCount == 0) {
    return;
}
?>

<!-- Pending Operational Tasks Notification -->
<div class="alert alert-info alert-dismissible fade show mb-3" role="alert" id="operationalTasksNotification">
    <div class="d-flex align-items-center">
        <i class="ri-notification-3-line me-2 fs-4"></i>
        <div class="flex-grow-1">
            <h5 class="alert-heading mb-1">Pending Scheduled Tasks</h5>
            <p class="mb-2">
                You have <strong><?php echo $notificationCount; ?></strong> scheduled task(s) ready to be activated.
            </p>
            <div id="pendingTasksList">
                <?php if ($notifications): ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach (array_slice($notifications, 0, 5) as $notification): ?>
                            <li class="mb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo htmlspecialchars($notification['templateName']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            Due: <?php echo date('M d, Y', strtotime($notification['dueDate'])); ?>
                                        </small>
                                    </div>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-primary process-task-btn"
                                        data-notification-id="<?php echo $notification['notificationID']; ?>"
                                        data-template-name="<?php echo htmlspecialchars($notification['templateName']); ?>">
                                        <i class="ri-play-line me-1"></i>Process Now
                                    </button>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if ($notificationCount > 5): ?>
                        <p class="mb-0 mt-2">
                            <a href="?s=user&ss=operational&p=tasks&filter=pending" class="alert-link">
                                View all <?php echo $notificationCount; ?> pending tasks
                            </a>
                        </p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle process task button clicks
    document.querySelectorAll('.process-task-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const notificationID = this.getAttribute('data-notification-id');
            const templateName = this.getAttribute('data-template-name');
            const btnElement = this;

            // Disable button
            btnElement.disabled = true;
            btnElement.innerHTML = '<i class="ri-loader-4-line me-1"></i>Processing...';

            // Process task
            const formData = new URLSearchParams();
            formData.append('notificationID', notificationID);
            formData.append('action', 'process');

            fetch('<?php echo $base; ?>php/scripts/operational/tasks/process_pending_task.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData.toString()
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    btnElement.innerHTML = '<i class="ri-check-line me-1"></i>Processed';
                    btnElement.classList.remove('btn-primary');
                    btnElement.classList.add('btn-success');

                    // Remove notification from list after 2 seconds
                    setTimeout(function() {
                        const listItem = btnElement.closest('li');
                        if (listItem) {
                            listItem.style.transition = 'opacity 0.3s';
                            listItem.style.opacity = '0';
                            setTimeout(function() {
                                listItem.remove();

                                // Check if list is empty
                                const list = document.getElementById('pendingTasksList');
                                if (list && list.querySelectorAll('li').length === 0) {
                                    document.getElementById('operationalTasksNotification').remove();
                                }
                            }, 300);
                        }
                    }, 2000);

                    // Show toast notification
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Task Created',
                            text: 'Task "' + templateName + '" has been created successfully.',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }
                } else {
                    // Show error
                    btnElement.disabled = false;
                    btnElement.innerHTML = '<i class="ri-play-line me-1"></i>Process Now';
                    alert('Error: ' + (data.message || 'Failed to process task'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                btnElement.disabled = false;
                btnElement.innerHTML = '<i class="ri-play-line me-1"></i>Process Now';
                alert('Error processing task. Please try again.');
            });
        });
    });
});
</script>

