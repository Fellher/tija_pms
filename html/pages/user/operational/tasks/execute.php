<?php
/**
 * Task Execution Interface - User
 *
 * Execute operational task with checklist, time logging, and approval
 *
 * @package    TIJA_PMS
 * @subpackage Operational_Work
 * @version    1.0.0
 */

if(!$isValidUser) {
    Alert::info("You need to be logged in to access this page", true);
    include "includes/core/log_in_script.php";
    return;
}

global $DBConn, $userID;

$taskID = $_GET['id'] ?? null;

if (!$taskID) {
    Alert::error("Task ID is required", true);
    header("Location: ?s=user&ss=operational&p=tasks");
    exit;
}

// Get task instance
$task = OperationalTask::getInstance($taskID, $DBConn);

if (!$task) {
    Alert::error("Task not found", true);
    header("Location: ?s=user&ss=operational&p=tasks");
    exit;
}

// Get template details
$template = OperationalTaskTemplate::getTemplate($task['templateID'], $DBConn);

// Get checklist items
$checklistItems = $DBConn->retrieve_db_table_rows('tija_operational_task_checklists',
    ['checklistItemID', 'itemOrder', 'itemDescription', 'isMandatory', 'isCompleted'],
    ['operationalTaskID' => $taskID],
    false,
    'ORDER BY itemOrder ASC');

// Get SOP if linked
$sop = null;
if ($template['sopID']) {
    $sop = SOPManagement::getSOP($template['sopID'], $DBConn);
}

// Get dependencies
$dependencies = $DBConn->retrieve_db_table_rows('tija_operational_task_dependencies',
    ['dependencyID', 'predecessorTaskID', 'dependencyType', 'lagDays'],
    ['successorTaskID' => $taskID]);

$pageTitle = "Execute Task: " . htmlspecialchars($template['templateName'] ?? 'Unknown');
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
                        <li class="breadcrumb-item"><a href="?s=user&ss=operational&p=tasks">My Tasks</a></li>
                        <li class="breadcrumb-item active">Execute</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Task Details -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Task Details</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h5><?php echo htmlspecialchars($template['templateName'] ?? 'Unknown'); ?></h5>
                        <p class="text-muted"><?php echo htmlspecialchars($template['templateDescription'] ?? ''); ?></p>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Due Date:</strong>
                            <span class="<?php echo !empty($task['dueDate']) && $task['dueDate'] < date('Y-m-d') ? 'text-danger fw-bold' : ''; ?>">
                                <?php echo !empty($task['dueDate']) ? date('M d, Y', strtotime($task['dueDate'])) : 'N/A'; ?>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <strong>Status:</strong>
                            <span class="badge bg-<?php
                                $status = $task['status'] ?? 'pending';
                                echo $status === 'completed' ? 'success' :
                                    ($status === 'in_progress' ? 'info' :
                                    ($status === 'overdue' ? 'danger' : 'warning'));
                            ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                            </span>
                        </div>
                    </div>

                    <?php if ($sop): ?>
                        <div class="mb-3">
                            <strong>SOP:</strong>
                            <a href="<?php echo htmlspecialchars($sop['sopDocumentURL'] ?? '#'); ?>" target="_blank" class="btn btn-sm btn-info">
                                <i class="ri-file-text-line me-1"></i>View SOP
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Checklist -->
                    <?php if (!empty($checklistItems)): ?>
                        <div class="mb-3">
                            <h6>Checklist</h6>
                            <div id="checklistContainer">
                                <?php foreach ($checklistItems as $item): ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input checklist-item"
                                               type="checkbox"
                                               id="checklist_<?php echo $item['checklistItemID']; ?>"
                                               data-item-id="<?php echo $item['checklistItemID']; ?>"
                                               <?php echo ($item['isCompleted'] ?? 'N') === 'Y' ? 'checked' : ''; ?>
                                               <?php echo ($item['isMandatory'] ?? 'N') === 'Y' ? 'required' : ''; ?>>
                                        <label class="form-check-label" for="checklist_<?php echo $item['checklistItemID']; ?>">
                                            <?php echo htmlspecialchars($item['itemDescription'] ?? ''); ?>
                                            <?php if (($item['isMandatory'] ?? 'N') === 'Y'): ?>
                                                <span class="text-danger">*</span>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Time Logging -->
                    <div class="mb-3">
                        <h6>Log Time</h6>
                        <form id="timeLogForm">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="date" class="form-control" id="taskDate" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <input type="time" class="form-control" id="startTime" required>
                                </div>
                                <div class="col-md-4">
                                    <input type="time" class="form-control" id="endTime" required>
                                </div>
                            </div>
                            <div class="mt-2">
                                <textarea class="form-control" id="taskNarrative" placeholder="Task description/narrative..." rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm mt-2">
                                <i class="ri-time-line me-1"></i>Log Time
                            </button>
                        </form>
                    </div>

                    <!-- Comments -->
                    <div class="mb-3">
                        <h6>Comments</h6>
                        <div id="commentsContainer">
                            <!-- Comments will be loaded here -->
                        </div>
                        <div class="mt-2">
                            <textarea class="form-control" id="newComment" placeholder="Add a comment..." rows="2"></textarea>
                            <button type="button" class="btn btn-sm btn-primary mt-2" onclick="addComment()">
                                <i class="ri-chat-1-line me-1"></i>Add Comment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Task Actions -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Actions</h6>
                </div>
                <div class="card-body">
                    <?php if (($task['status'] ?? '') === 'pending'): ?>
                        <button class="btn btn-primary w-100 mb-2" onclick="startTask()">
                            <i class="ri-play-line me-1"></i>Start Task
                        </button>
                    <?php elseif (($task['status'] ?? '') === 'in_progress'): ?>
                        <button class="btn btn-success w-100 mb-2" onclick="completeTask()">
                            <i class="ri-check-line me-1"></i>Complete Task
                        </button>
                    <?php endif; ?>
                    <button class="btn btn-secondary w-100" onclick="window.location.href='?s=user&ss=operational&p=tasks'">
                        <i class="ri-arrow-left-line me-1"></i>Back to Tasks
                    </button>
                </div>
            </div>

            <!-- Dependencies -->
            <?php if (!empty($dependencies)): ?>
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">Dependencies</h6>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">This task depends on:</small>
                        <ul class="list-unstyled mt-2">
                            <?php foreach ($dependencies as $dep): ?>
                                <li>
                                    <i class="ri-link-line me-1"></i>
                                    Task #<?php echo $dep['predecessorTaskID']; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Task Info -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Task Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Template:</strong><br>
                        <small><?php echo htmlspecialchars($template['templateCode'] ?? ''); ?></small>
                    </div>
                    <div class="mb-2">
                        <strong>Instance:</strong><br>
                        <small>#<?php echo $task['instanceNumber'] ?? '1'; ?></small>
                    </div>
                    <div class="mb-2">
                        <strong>Functional Area:</strong><br>
                        <span class="badge bg-info"><?php echo htmlspecialchars($template['functionalArea'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="mb-2">
                        <strong>Estimated Duration:</strong><br>
                        <small><?php echo !empty($template['estimatedDuration']) ? number_format($template['estimatedDuration'], 2) . ' hrs' : 'N/A'; ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const taskID = <?php echo $taskID; ?>;

function startTask() {
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('taskID', taskID);
    formData.append('status', 'in_progress');

    fetch('<?php echo $base; ?>php/scripts/operational/tasks/manage_task.php?action=update_status', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Task started successfully');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while starting the task');
    });
}

function completeTask() {
    // Check mandatory checklist items
    const mandatoryItems = document.querySelectorAll('.checklist-item[required]');
    let allMandatoryChecked = true;
    mandatoryItems.forEach(item => {
        if (!item.checked) {
            allMandatoryChecked = false;
        }
    });

    if (!allMandatoryChecked) {
        alert('Please complete all mandatory checklist items before completing the task');
        return;
    }

    if (confirm('Complete this task?')) {
        const formData = new FormData();
        formData.append('action', 'complete');
        formData.append('taskID', taskID);

        // Get checklist data
        const checklistData = [];
        document.querySelectorAll('.checklist-item').forEach(item => {
            checklistData.push({
                itemID: item.dataset.itemId,
                isCompleted: item.checked ? 'Y' : 'N'
            });
        });
        formData.append('checklistData', JSON.stringify(checklistData));

        // Get actual duration (would calculate from time logs)
        formData.append('actualDuration', 0);

        fetch('<?php echo $base; ?>php/scripts/operational/tasks/manage_task.php?action=complete', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Task completed successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while completing the task');
        });
    }
}

// Time logging
document.getElementById('timeLogForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData();
    formData.append('operationalTaskID', taskID);
    formData.append('taskDate', document.getElementById('taskDate').value);
    formData.append('startTime', document.getElementById('startTime').value);
    formData.append('endTime', document.getElementById('endTime').value);
    formData.append('taskNarrative', document.getElementById('taskNarrative').value);

    fetch('<?php echo $base; ?>php/scripts/operational/tasks/log_time.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Time logged successfully');
            document.getElementById('timeLogForm').reset();
            document.getElementById('taskDate').value = '<?php echo date('Y-m-d'); ?>';
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while logging time');
    });
});

function addComment() {
    const comment = document.getElementById('newComment').value;
    if (!comment.trim()) {
        alert('Please enter a comment');
        return;
    }

    // TODO: Implement comment API
    alert('Comment functionality will be implemented');
    document.getElementById('newComment').value = '';
}
</script>

