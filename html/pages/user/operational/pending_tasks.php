<?php
/**
 * Pending Operational Tasks Page
 *
 * Dedicated page for managing pending scheduled operational tasks
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

// Check authentication
if(!$isValidUser) {
    Alert::info("You need to be logged in to access this page", true);
    include "includes/core/log_in_script.php";
    return;
}

require_once __DIR__ . '/../../../../php/classes/operationaltaskscheduler.php';
require_once __DIR__ . '/../../../../php/classes/operationaltasktemplate.php';

global $DBConn, $userID;

// Get filter from URL
$filter = isset($_GET['filter']) ? Utility::clean_string($_GET['filter']) : 'all';
$statusFilter = isset($_GET['status']) ? Utility::clean_string($_GET['status']) : 'pending';

// Get pending notifications
$notifications = OperationalTaskScheduler::getPendingTaskNotifications($userID, $DBConn);
$notificationCount = is_array($notifications) ? count($notifications) : 0;

// Filter notifications
$filteredNotifications = $notifications;
if ($statusFilter === 'pending') {
    $filteredNotifications = array_filter($notifications, function($n) {
        return in_array($n['status'], ['pending', 'sent']);
    });
}

$pageTitle = "Pending Operational Tasks";
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0"><?php echo htmlspecialchars($pageTitle); ?></h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="?s=user">User</a></li>
                        <li class="breadcrumb-item"><a href="?s=user&ss=operational">Operational Work</a></li>
                        <li class="breadcrumb-item active">Pending Tasks</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-md bg-primary text-white rounded">
                                <i class="ri-task-line fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Pending Tasks</h6>
                            <p class="mb-0 text-muted">
                                <span class="fs-4 fw-bold text-primary"><?php echo $notificationCount; ?></span>
                                <span class="text-muted">ready to process</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-md bg-warning text-white rounded">
                                <i class="ri-time-line fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Due Today</h6>
                            <p class="mb-0 text-muted">
                                <span class="fs-4 fw-bold text-warning">
                                    <?php
                                    $dueToday = array_filter($notifications, function($n) {
                                        return $n['dueDate'] == date('Y-m-d');
                                    });
                                    echo count($dueToday);
                                    ?>
                                </span>
                                <span class="text-muted">tasks</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-md bg-danger text-white rounded">
                                <i class="ri-alarm-warning-line fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Overdue</h6>
                            <p class="mb-0 text-muted">
                                <span class="fs-4 fw-bold text-danger">
                                    <?php
                                    $overdue = array_filter($notifications, function($n) {
                                        return $n['dueDate'] < date('Y-m-d');
                                    });
                                    echo count($overdue);
                                    ?>
                                </span>
                                <span class="text-muted">tasks</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Actions -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary filter-btn <?php echo $statusFilter === 'pending' ? 'active' : ''; ?>"
                                    data-filter="pending">
                                <i class="ri-time-line me-1"></i>Pending
                            </button>
                            <button class="btn btn-sm btn-outline-secondary filter-btn <?php echo $statusFilter === 'all' ? 'active' : ''; ?>"
                                    data-filter="all">
                                <i class="ri-list-check me-1"></i>All
                            </button>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-primary" id="processSelectedBtn" disabled>
                                <i class="ri-play-line me-1"></i>Process Selected
                            </button>
                            <button class="btn btn-sm btn-success" id="processAllBtn" <?php echo $notificationCount == 0 ? 'disabled' : ''; ?>>
                                <i class="ri-play-fill me-1"></i>Process All
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <?php if (empty($filteredNotifications)): ?>
                        <div class="text-center py-5">
                            <i class="ri-task-line fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">No Pending Tasks</h5>
                            <p class="text-muted">You're all caught up! No scheduled tasks are ready to be processed.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" class="form-check-input" id="selectAll">
                                        </th>
                                        <th>Task Template</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Date Added</th>
                                        <th width="150" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($filteredNotifications as $notification):
                                        $isOverdue = $notification['dueDate'] < date('Y-m-d');
                                        $isDueToday = $notification['dueDate'] == date('Y-m-d');
                                        $template = OperationalTaskTemplate::getTemplate($notification['templateID'], $DBConn);
                                    ?>
                                        <tr class="<?php echo $isOverdue ? 'table-danger' : ($isDueToday ? 'table-warning' : ''); ?>">
                                            <td>
                                                <input type="checkbox" class="form-check-input task-checkbox"
                                                       value="<?php echo $notification['notificationID']; ?>">
                                            </td>
                                            <td>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($notification['templateName']); ?></div>
                                                <?php if ($template && isset($template['templateDescription'])): ?>
                                                    <small class="text-muted"><?php echo htmlspecialchars(substr($template['templateDescription'], 0, 100)); ?>...</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="ri-calendar-line me-2"></i>
                                                    <span class="<?php echo $isOverdue ? 'text-danger fw-bold' : ($isDueToday ? 'text-warning fw-semibold' : ''); ?>">
                                                        <?php echo date('M d, Y', strtotime($notification['dueDate'])); ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $isOverdue ? 'danger' : ($isDueToday ? 'warning' : 'info'); ?>">
                                                    <?php echo ucfirst($notification['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('M d, Y H:i', strtotime($notification['DateAdded'])); ?>
                                                </small>
                                            </td>
                                            <td class="text-end">
                                                <button class="btn btn-sm btn-primary process-single-btn"
                                                        data-notification-id="<?php echo $notification['notificationID']; ?>"
                                                        data-template-name="<?php echo htmlspecialchars($notification['templateName']); ?>">
                                                    <i class="ri-play-line me-1"></i>Process
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            window.location.href = '?s=user&ss=operational&p=pending_tasks&status=' + filter;
        });
    });

    // Select all checkbox
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.task-checkbox');

    selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateProcessButton();
    });

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateProcessButton);
    });

    function updateProcessButton() {
        const selected = document.querySelectorAll('.task-checkbox:checked');
        document.getElementById('processSelectedBtn').disabled = selected.length === 0;
    }

    // Process single task
    document.querySelectorAll('.process-single-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const notificationID = this.getAttribute('data-notification-id');
            const templateName = this.getAttribute('data-template-name');
            processTask(notificationID, templateName, this);
        });
    });

    // Process selected
    document.getElementById('processSelectedBtn').addEventListener('click', function() {
        const selected = Array.from(document.querySelectorAll('.task-checkbox:checked'))
            .map(cb => cb.value);

        if (selected.length === 0) return;

        if (confirm(`Process ${selected.length} task(s)?`)) {
            processMultipleTasks(selected);
        }
    });

    // Process all
    document.getElementById('processAllBtn').addEventListener('click', function() {
        const all = Array.from(checkboxes).map(cb => cb.value);
        if (all.length === 0) return;

        if (confirm(`Process all ${all.length} task(s)?`)) {
            processMultipleTasks(all);
        }
    });

    function processTask(notificationID, templateName, buttonElement) {
        buttonElement.disabled = true;
        buttonElement.innerHTML = '<i class="ri-loader-4-line me-1"></i>Processing...';

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
                // Remove row
                buttonElement.closest('tr').style.transition = 'opacity 0.3s';
                buttonElement.closest('tr').style.opacity = '0';
                setTimeout(() => {
                    buttonElement.closest('tr').remove();
                    // Reload page if no more tasks
                    if (document.querySelectorAll('.task-checkbox').length === 0) {
                        location.reload();
                    }
                }, 300);
            } else {
                alert('Error: ' + (data.message || 'Failed to process task'));
                buttonElement.disabled = false;
                buttonElement.innerHTML = '<i class="ri-play-line me-1"></i>Process';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error processing task. Please try again.');
            buttonElement.disabled = false;
            buttonElement.innerHTML = '<i class="ri-play-line me-1"></i>Process';
        });
    }

    function processMultipleTasks(notificationIDs) {
        // Process sequentially to avoid overwhelming server
        let index = 0;
        const processNext = () => {
            if (index >= notificationIDs.length) {
                location.reload();
                return;
            }

            const formData = new URLSearchParams();
            formData.append('notificationID', notificationIDs[index]);
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
                index++;
                processNext();
            })
            .catch(error => {
                console.error('Error:', error);
                index++;
                processNext();
            });
        };

        processNext();
    }
});
</script>

