<?php
/**
 * Task Assignments - Admin
 *
 * Manage task assignments and assignment rules
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

if (!$isAdmin && !$isValidAdmin) {
    Alert::error("Access denied. Administrator privileges required.", true);
    return;
}

global $DBConn, $userID;

// Get templates for assignment management
$templates = OperationalTaskTemplate::listTemplates(['Suspended' => 'N'], $DBConn);

$pageTitle = "Task Assignments";
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0"><?php echo htmlspecialchars($pageTitle); ?></h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="?s=admin">Admin</a></li>
                        <li class="breadcrumb-item"><a href="?s=admin&ss=operational">Operational Work</a></li>
                        <li class="breadcrumb-item active">Assignments</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignment Rules -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Assignment Rules</h4>
                        <button class="btn btn-primary btn-sm" data-action="show-create-rule-modal">
                            <i class="ri-add-line me-1"></i>Create Rule
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="assignmentsTable">
                            <thead>
                                <tr>
                                    <th>Template</th>
                                    <th>Assignment Type</th>
                                    <th>Assigned To</th>
                                    <th>Functional Area</th>
                                    <th>Status</th>
                                    <th width="150" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (is_array($templates)): ?>
                                    <?php foreach ($templates as $template):
                                        // Handle both object and array access
                                        $templateID = is_object($template) ? ($template->templateID ?? null) : ($template['templateID'] ?? null);
                                        $templateName = is_object($template) ? ($template->templateName ?? 'Unknown') : ($template['templateName'] ?? 'Unknown');
                                        $templateCode = is_object($template) ? ($template->templateCode ?? '') : ($template['templateCode'] ?? '');
                                        $functionalArea = is_object($template) ? ($template->functionalArea ?? 'N/A') : ($template['functionalArea'] ?? 'N/A');
                                        $isActive = is_object($template) ? ($template->isActive ?? 'N') : ($template['isActive'] ?? 'N');
                                        $assignmentRuleStr = is_object($template) ? ($template->assignmentRule ?? '{}') : ($template['assignmentRule'] ?? '{}');

                                        $assignmentRule = json_decode($assignmentRuleStr, true);
                                        $ruleType = is_array($assignmentRule) ? ($assignmentRule['type'] ?? 'none') : 'none';
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($templateName); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($templateCode); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php
                                                    echo $ruleType === 'employee' ? 'primary' :
                                                        ($ruleType === 'role' ? 'info' :
                                                        ($ruleType === 'function_head' ? 'success' : 'secondary'));
                                                ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $ruleType)); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($ruleType === 'employee' && !empty($assignmentRule['employeeID'])): ?>
                                                    <?php
                                                        $employee = Data::users(['ID' => $assignmentRule['employeeID']], true, $DBConn);
                                                        echo $employee ? htmlspecialchars($employee->FirstName . ' ' . $employee->Surname) : 'Unknown';
                                                    ?>
                                                <?php elseif ($ruleType === 'role'): ?>
                                                    Role: <?php echo htmlspecialchars($assignmentRule['roleID'] ?? 'N/A'); ?>
                                                <?php elseif ($ruleType === 'function_head'): ?>
                                                    Function Head
                                                <?php else: ?>
                                                    <span class="text-muted">Not assigned</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($functionalArea); ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $isActive === 'Y' ? 'success' : 'secondary'; ?>">
                                                    <?php echo $isActive === 'Y' ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-primary" data-action="edit-assignment" data-template-id="<?php echo $templateID; ?>" title="Edit">
                                                        <i class="ri-edit-line"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" data-action="remove-assignment" data-template-id="<?php echo $templateID; ?>" title="Remove">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            No templates found. Create templates first to manage assignments.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('assignmentsTable')) {
        $('#assignmentsTable').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[0, 'asc']],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ assignments per page"
            }
        });
    }
});

function showCreateRuleModal() {
    // Show modal for creating assignment rule
    // This would typically open a Bootstrap modal with template, assignment type, and assignee selection
    alert('Please use the "Create Rule" button to open the assignment rule form');
}

function editAssignment(templateID) {
    // Redirect to template edit page where assignment rules can be managed
    window.location.href = '?s=admin&ss=operational&p=templates&action=edit&id=' + templateID;
}

function removeAssignment(templateID) {
    if (confirm('Remove assignment rule for this template? This will clear the assignment rule but not delete the template.')) {
        const formData = new FormData();
        formData.append('action', 'update');
        formData.append('templateID', templateID);
        formData.append('assignmentRule', '{}');

        fetch('<?php echo $base; ?>php/scripts/operational/templates/manage_template.php?action=update', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Assignment rule removed successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while removing the assignment rule');
        });
    }
}

// Event delegation for assignments
document.addEventListener('click', function(e) {
    const action = e.target.closest('[data-action]')?.getAttribute('data-action');
    if (!action) return;

    const element = e.target.closest('[data-action]');

    switch(action) {
        case 'show-create-rule-modal':
            showCreateRuleModal();
            break;

        case 'edit-assignment':
            const templateID = element.getAttribute('data-template-id');
            if (templateID) {
                editAssignment(parseInt(templateID));
            }
            break;

        case 'remove-assignment':
            const removeTemplateID = element.getAttribute('data-template-id');
            if (removeTemplateID && confirm('Are you sure you want to remove this assignment rule?')) {
                removeAssignment(parseInt(removeTemplateID));
            }
            break;
    }
});
</script>

