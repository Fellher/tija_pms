<?php
/**
 * Process Modeler - Admin
 *
 * Visual process modeling and design tool
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

$pageTitle = "Process Modeler";
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
                        <li class="breadcrumb-item active">Process Modeler</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Process Modeler Interface -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Process Modeling Canvas</h4>
                        <div class="btn-group">
                            <button class="btn btn-sm btn-primary" data-action="save-model">
                                <i class="ri-save-line me-1"></i>Save Model
                            </button>
                            <button class="btn btn-sm btn-success" data-action="export-model">
                                <i class="ri-download-line me-1"></i>Export
                            </button>
                            <button class="btn btn-sm btn-info" data-action="clear-canvas">
                                <i class="ri-delete-bin-line me-1"></i>Clear
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Toolbox -->
                        <div class="col-md-3">
                            <div class="card border">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Process Elements</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-outline-primary btn-sm" data-action="add-element" data-element-type="start">
                                            <i class="ri-play-circle-line me-1"></i>Start Event
                                        </button>
                                        <button class="btn btn-outline-primary btn-sm" data-action="add-element" data-element-type="task">
                                            <i class="ri-task-line me-1"></i>Task
                                        </button>
                                        <button class="btn btn-outline-primary btn-sm" data-action="add-element" data-element-type="gateway">
                                            <i class="ri-shape-line me-1"></i>Gateway
                                        </button>
                                        <button class="btn btn-outline-primary btn-sm" data-action="add-element" data-element-type="subprocess">
                                            <i class="ri-stack-line me-1"></i>Sub-Process
                                        </button>
                                        <button class="btn btn-outline-primary btn-sm" data-action="add-element" data-element-type="end">
                                            <i class="ri-stop-circle-line me-1"></i>End Event
                                        </button>
                                    </div>
                                    <hr>
                                    <h6 class="mb-2">Properties</h6>
                                    <div id="elementProperties" class="mt-3">
                                        <p class="text-muted small">Select an element to edit properties</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Canvas -->
                        <div class="col-md-9">
                            <div class="card border" style="height: 600px; position: relative; overflow: auto;">
                                <div id="processCanvas" style="width: 100%; height: 100%; min-height: 500px; background: #f8f9fa; position: relative;">
                                    <div class="text-center p-5 text-muted">
                                        <i class="ri-node-tree fs-1 mb-3 d-block"></i>
                                        <p>Drag elements from the toolbox to start modeling your process</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Process Models List -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Saved Process Models</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="modelsTable">
                            <thead>
                                <tr>
                                    <th>Model Name</th>
                                    <th>Process</th>
                                    <th>Version</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th width="150" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No process models saved yet
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Process modeling functionality
let selectedElement = null;
let elements = [];

function addElement(type) {
    // TODO: Implement drag-and-drop process modeling
    alert('Process modeling functionality will be implemented with a visual editor library');
}

function saveModel() {
    // TODO: Implement save functionality
    alert('Save functionality will be implemented');
}

function exportModel() {
    // TODO: Implement export functionality
    alert('Export functionality will be implemented');
}

function clearCanvas() {
    if (confirm('Clear the canvas? All unsaved changes will be lost.')) {
        document.getElementById('processCanvas').innerHTML = '';
        elements = [];
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('modelsTable')) {
        $('#modelsTable').DataTable({
            responsive: true,
            pageLength: 10,
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ models per page"
            }
        });
    }

    // Event delegation for process modeling
    document.addEventListener('click', function(e) {
        const action = e.target.closest('[data-action]')?.getAttribute('data-action');
        if (!action) return;

        const element = e.target.closest('[data-action]');

        switch(action) {
            case 'save-model':
                saveModel();
                break;

            case 'export-model':
                exportModel();
                break;

            case 'clear-canvas':
                clearCanvas();
                break;

            case 'add-element':
                const elementType = element.getAttribute('data-element-type');
                if (elementType) {
                    addElement(elementType);
                }
                break;
        }
    });
});
</script>

