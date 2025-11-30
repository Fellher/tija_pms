<?php
/**
 * View Template - User
 *
 * View operational task template details
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

// Include help component
include __DIR__ . '/../../../includes/components/operational_help.php';

global $DBConn, $userID;

$templateID = $_GET['id'] ?? null;

if (!$templateID) {
    Alert::error("Template ID is required", true);
    header("Location: ?s=user&ss=operational&p=templates");
    exit;
}

// Get template
$template = OperationalTaskTemplate::getTemplate($templateID, $DBConn);

if (!$template) {
    Alert::error("Template not found", true);
    header("Location: ?s=user&ss=operational&p=templates");
    exit;
}

// Get process details
$process = null;
if ($template['processID']) {
    $process = BAUTaxonomy::getProcessByID($template['processID'], $DBConn);
}

// Get SOP if linked
$sop = null;
if ($template['sopID']) {
    $sop = SOPManagement::getSOP($template['sopID'], $DBConn);
}

// Get checklist items
$checklistItems = $DBConn->retrieve_db_table_rows('tija_operational_task_checklists',
    ['checklistItemID', 'itemOrder', 'itemDescription', 'isMandatory'],
    ['templateID' => $templateID],
    false,
    'ORDER BY itemOrder ASC');

// Get workflow if linked
$workflow = null;
if ($template['workflowID']) {
    $workflow = WorkflowDefinition::getWorkflow($template['workflowID'], $DBConn);
}

$pageTitle = "Template: " . htmlspecialchars($template['templateName'] ?? 'Unknown');
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
                        <li class="breadcrumb-item"><a href="?s=user&ss=operational&p=templates">Templates</a></li>
                        <li class="breadcrumb-item active">View</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Template Details -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Template Information</h4>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5><?php echo htmlspecialchars($template['templateName'] ?? 'Unknown'); ?></h5>
                        <p class="text-muted"><?php echo htmlspecialchars($template['templateDescription'] ?? ''); ?></p>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Template Code:</strong><br>
                            <span class="badge bg-primary"><?php echo htmlspecialchars($template['templateCode'] ?? ''); ?></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Functional Area:</strong><br>
                            <span class="badge bg-info"><?php echo htmlspecialchars($template['functionalArea'] ?? 'N/A'); ?></span>
                        </div>
                    </div>

                    <?php if ($process): ?>
                        <div class="mb-3">
                            <strong>APQC Process:</strong><br>
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($process['processID'] ?? ''); ?></span>
                            <?php echo htmlspecialchars($process['processName'] ?? ''); ?>
                        </div>
                    <?php endif; ?>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Frequency:</strong><br>
                            <span class="badge bg-secondary">
                                <?php
                                    $freq = $template['frequencyType'] ?? 'custom';
                                    echo ucfirst($freq);
                                    if (!empty($template['frequencyInterval']) && $template['frequencyInterval'] > 1) {
                                        echo ' (every ' . $template['frequencyInterval'] . ')';
                                    }
                                ?>
                            </span>
                        </div>
                        <div class="col-md-4">
                            <strong>Estimated Duration:</strong><br>
                            <?php echo !empty($template['estimatedDuration']) ? number_format($template['estimatedDuration'], 2) . ' hours' : 'N/A'; ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Processing Mode:</strong><br>
                            <span class="badge bg-<?php
                                $mode = $template['processingMode'] ?? 'cron';
                                echo $mode === 'cron' ? 'success' : ($mode === 'manual' ? 'warning' : 'info');
                            ?>">
                                <?php echo ucfirst($mode); ?>
                            </span>
                        </div>
                    </div>

                    <?php if ($sop): ?>
                        <div class="mb-3">
                            <strong>Standard Operating Procedure:</strong><br>
                            <a href="<?php echo htmlspecialchars($sop['sopDocumentURL'] ?? '#'); ?>" target="_blank" class="btn btn-sm btn-info">
                                <i class="ri-file-text-line me-1"></i>View SOP: <?php echo htmlspecialchars($sop['sopTitle'] ?? ''); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($checklistItems)): ?>
                        <div class="mb-3">
                            <h6>Checklist Items</h6>
                            <ul class="list-group">
                                <?php foreach ($checklistItems as $item): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo htmlspecialchars($item['itemDescription'] ?? ''); ?>
                                        <?php if (($item['isMandatory'] ?? 'N') === 'Y'): ?>
                                            <span class="badge bg-danger">Required</span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($workflow): ?>
                        <div class="mb-3">
                            <h6>Workflow</h6>
                            <p class="text-muted"><?php echo htmlspecialchars($workflow['workflowName'] ?? ''); ?></p>
                            <?php if (!empty($workflow['steps'])): ?>
                                <small class="text-muted"><?php echo count($workflow['steps']); ?> workflow steps</small>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Template Status</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Status:</strong><br>
                        <span class="badge bg-<?php echo ($template['isActive'] ?? 'N') === 'Y' ? 'success' : 'secondary'; ?>">
                            <?php echo ($template['isActive'] ?? 'N') === 'Y' ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>
                    <div class="mb-3">
                        <strong>Requires Approval:</strong><br>
                        <?php echo ($template['requiresApproval'] ?? 'N') === 'Y' ? 'Yes' : 'No'; ?>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Actions</h6>
                </div>
                <div class="card-body">
                    <button class="btn btn-primary w-100 mb-2" onclick="createTaskInstance()">
                        <i class="ri-play-line me-1"></i>Create Task Instance
                    </button>
                    <a href="?s=user&ss=operational&p=templates" class="btn btn-secondary w-100">
                        <i class="ri-arrow-left-line me-1"></i>Back to Templates
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function createTaskInstance() {
    if (confirm('Create a new task instance from this template?')) {
        const formData = new FormData();
        formData.append('action', 'create');
        formData.append('templateID', <?php echo $templateID; ?>);
        formData.append('dueDate', new Date().toISOString().split('T')[0]);

        fetch('<?php echo $base; ?>php/scripts/operational/tasks/manage_task.php?action=create', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Task instance created successfully');
                window.location.href = '?s=user&ss=operational&p=tasks';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while creating the task instance');
        });
    }
}
</script>

