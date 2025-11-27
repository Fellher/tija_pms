<?php
/**
 * Project Files View
 * File and document management for projects
 *
 * @package Tija Practice Management System
 * @subpackage Projects - Files View
 * @version 3.0.0
 */

// Get project details
$projectDetails = Projects::projects_mini(array('projectID' => $projectID), true, $DBConn);

// Get project files
$projectFiles = Projects::project_files(array('projectID' => $projectID, 'Suspended' => 'N'), false, $DBConn);
if (!$projectFiles) {
    $projectFiles = array();
}

// Get employees for file uploader information
$employees = Employee::employees(array('Suspended' => 'N'), false, $DBConn);
$employeeMap = array();
if ($employees && is_array($employees)) {
    foreach ($employees as $emp) {
        $employeeMap[$emp->ID] = $emp->employeeName;
    }
}
?>

<div class="container-fluid my-3" id="projectFilesContainer">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-0">
                <i class="ri-folder-line me-2 text-primary"></i>Project Files
            </h3>
            <p class="text-muted mb-0">Manage documents and files for this project</p>
        </div>
        <button type="button"
                class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#upload_project_file"
                data-project-id="<?= htmlspecialchars($projectID) ?>">
            <i class="ri-upload-line me-1"></i>Upload File
        </button>
    </div>

    <!-- Files Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-md bg-primary bg-opacity-10 text-primary rounded-circle">
                                <i class="ri-file-line fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-0">Total Files</h6>
                            <h4 class="mb-0"><?= count($projectFiles) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-md bg-success bg-opacity-10 text-success rounded-circle">
                                <i class="ri-file-pdf-line fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-0">Documents</h6>
                            <h4 class="mb-0">
                                <?= count(array_filter($projectFiles, function($f) { return in_array(strtolower($f->fileType ?? ''), ['pdf', 'doc', 'docx', 'xls', 'xlsx']); })) ?>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-md bg-info bg-opacity-10 text-info rounded-circle">
                                <i class="ri-image-line fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-0">Images</h6>
                            <h4 class="mb-0">
                                <?= count(array_filter($projectFiles, function($f) { return in_array(strtolower($f->fileType ?? ''), ['jpg', 'jpeg', 'png', 'gif', 'webp']); })) ?>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-md bg-warning bg-opacity-10 text-warning rounded-circle">
                                <i class="ri-download-line fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-0">Total Downloads</h6>
                            <h4 class="mb-0">
                                <?= array_sum(array_column(array_map(function($f) { return (array)$f; }, $projectFiles), 'downloadCount')) ?>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Files List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0">Project Files</h5>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-secondary active" data-view="grid">
                            <i class="ri-grid-line"></i> Grid
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-view="list">
                            <i class="ri-list-check"></i> List
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($projectFiles)): ?>
                <div class="text-center py-5">
                    <i class="ri-folder-open-line fs-1 text-muted mb-3 d-block"></i>
                    <h5 class="text-muted">No files uploaded yet</h5>
                    <p class="text-muted">Upload your first file to get started</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#upload_project_file">
                        <i class="ri-upload-line me-1"></i>Upload File
                    </button>
                </div>
            <?php else: ?>
                <div class="row" id="filesGrid">
                    <?php foreach ($projectFiles as $file): ?>
                        <div class="col-md-3 mb-3">
                            <div class="card border h-100 file-card">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <?php
                                        $fileIcon = 'ri-file-line';
                                        $fileType = strtolower($file->fileType ?? '');
                                        if (in_array($fileType, ['pdf'])) {
                                            $fileIcon = 'ri-file-pdf-line text-danger';
                                        } elseif (in_array($fileType, ['doc', 'docx'])) {
                                            $fileIcon = 'ri-file-word-line text-primary';
                                        } elseif (in_array($fileType, ['xls', 'xlsx'])) {
                                            $fileIcon = 'ri-file-excel-line text-success';
                                        } elseif (in_array($fileType, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                            $fileIcon = 'ri-image-line text-info';
                                        }
                                        ?>
                                        <i class="<?= $fileIcon ?> fs-1"></i>
                                    </div>
                                    <h6 class="mb-1 text-truncate" title="<?= htmlspecialchars($file->fileOriginalName ?? $file->fileName ?? 'Untitled') ?>">
                                        <?= htmlspecialchars($file->fileOriginalName ?? $file->fileName ?? 'Untitled') ?>
                                    </h6>
                                    <small class="text-muted d-block mb-2">
                                        <?php
                                        $fileSize = $file->fileSize ?? 0;
                                        if ($fileSize > 0) {
                                            echo $fileSize > 1048576 ? number_format($fileSize / 1048576, 2) . ' MB' : number_format($fileSize / 1024, 2) . ' KB';
                                        }
                                        ?>
                                    </small>
                                    <small class="text-muted d-block mb-2">
                                        <i class="ri-user-line"></i> <?= htmlspecialchars($employeeMap[$file->uploadedBy ?? 0] ?? 'Unknown') ?>
                                    </small>
                                    <small class="text-muted d-block">
                                        <i class="ri-calendar-line"></i> <?= date('M d, Y', strtotime($file->DateAdded ?? 'now')) ?>
                                    </small>
                                </div>
                                <div class="card-footer bg-white border-top">
                                    <div class="btn-group w-100" role="group">
                                        <a href="<?= htmlspecialchars($file->fileURL ?? '#') ?>"
                                           class="btn btn-sm btn-outline-primary"
                                           target="_blank"
                                           download>
                                            <i class="ri-download-line"></i>
                                        </a>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#view_file_details"
                                                data-file-id="<?= $file->fileID ?? '' ?>">
                                            <i class="ri-eye-line"></i>
                                        </button>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="deleteFile(<?= $file->fileID ?? 0 ?>)">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// File view toggle (grid/list)
document.querySelectorAll('[data-view]').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('[data-view]').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        // TODO: Implement grid/list view toggle
    });
});

// Delete file function
function deleteFile(fileID) {
    if (confirm('Are you sure you want to delete this file? This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('fileID', fileID);

        // Show loading indicator
        showFileToast('Deleting file...', 'info', 2000);

        fetch('<?= $base ?>php/scripts/projects/manage_project_file.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showFileToast(data.message || 'File deleted successfully', 'success');
                // Reload after a short delay to show the success message
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showFileToast(data.message || 'Unable to delete file. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('File Delete Error:', error);
            const errorMsg = formatErrorMessage(error, 'Unable to delete file. Please check your internet connection and try again.');
            showFileToast(errorMsg, 'error');
        });
    }
}

// Handle view file details modal
document.addEventListener('DOMContentLoaded', function() {
    const viewFileModal = document.getElementById('view_file_details');
    if (viewFileModal) {
        viewFileModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const fileID = button.getAttribute('data-file-id');

            // Fetch file details via AJAX and populate modal
            if (fileID) {
                const formData = new FormData();
                formData.append('action', 'get');
                formData.append('fileID', fileID);

                fetch('<?= $base ?>php/scripts/projects/manage_project_file.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.data) {
                        if (typeof populateFileDetails === 'function') {
                            populateFileDetails(data.data);
                        }
                    } else {
                        const errorMsg = data.message || 'Unable to load file details. Please try again.';
                        showFileToast(errorMsg, 'error');
                    }
                })
                .catch(error => {
                    console.error('File Details Load Error:', error);
                    const errorMsg = formatErrorMessage(error, 'Unable to load file details. Please check your connection and try again.');
                    showFileToast(errorMsg, 'error');
                });
            }
        });
    }

    // Handle upload modal project ID
    const uploadModal = document.getElementById('upload_project_file');
    if (uploadModal) {
        uploadModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const projectID = button.getAttribute('data-project-id') || '<?= $projectID ?>';
            document.getElementById('projectID').value = projectID;
        });
    }
});

// Helper function to show toast notifications with fallback
function showFileToast(message, type = 'info', duration = null) {
    if (typeof showToast === 'function') {
        showToast(message, type, duration);
    } else {
        // Fallback to alert if toast is not available
        alert(message);
    }
}

// Helper function to format error messages for better UX
function formatErrorMessage(error, defaultMessage) {
    if (!error) return defaultMessage;

    // Check if it's a network error
    if (error.message && error.message.includes('Network')) {
        return 'Unable to connect to the server. Please check your internet connection and try again.';
    }

    // Check if it's a JSON parse error
    if (error.message && error.message.includes('JSON')) {
        return 'Invalid response from server. Please refresh the page and try again.';
    }

    return defaultMessage;
}
</script>

<!-- Upload File Modal -->
<?php
echo Utility::form_modal_header(
    "upload_project_file",
    "projects/manage_project_file.php",
    "Upload Project File",
    array('modal-lg', 'modal-dialog-centered'),
    $base
);
include 'includes/scripts/projects/modals/upload_project_file.php';
echo Utility::form_modal_footer("Upload File", "upload_project_file_btn", 'btn btn-primary btn-sm', true);
?>

<!-- View File Details Modal -->
<?php
echo Utility::modal_general_top(
    "view_file_details",
    "File Details",
    array('modal-lg', 'modal-dialog-centered'),
    $base
);
include 'includes/scripts/projects/modals/view_project_file.php';
echo Utility::form_modal_general_footer("Close", "closeFileDetails", 'btn btn-secondary btn-sm');
?>

<script>
// Handle form submission for file upload
document.addEventListener('DOMContentLoaded', function() {
    const uploadForm = document.querySelector('.upload_project_file');
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'upload');

            const submitBtn = document.getElementById('upload_project_file_btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i>Uploading...';

            fetch('<?= $base ?>php/scripts/projects/manage_project_file.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const successMsg = data.message || 'File uploaded successfully!';
                    showFileToast(successMsg, 'success');
                    // Close modal and reload after a short delay
                    const modal = bootstrap.Modal.getInstance(document.getElementById('upload_project_file'));
                    if (modal) {
                        modal.hide();
                    }
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    const errorMsg = data.message || 'Unable to upload file. Please try again.';
                    showFileToast(errorMsg, 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('File Upload Error:', error);
                const errorMsg = formatErrorMessage(error, 'Unable to upload file. Please check your internet connection and try again.');
                showFileToast(errorMsg, 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
});
</script>

