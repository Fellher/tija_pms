<div id="view_project_file_modal" class="view_project_file_modal">
    <div class="row">
        <div class="col-md-12">
            <!-- File Preview Section -->
            <div class="text-center mb-3" id="filePreviewSection">
                <div id="filePreviewContent">
                    <!-- File preview will be loaded here -->
                </div>
            </div>

            <!-- File Information -->
            <div class="card border-0 bg-light mb-3">
                <div class="card-body">
                    <h6 class="text-primary mb-3"><i class="ri-information-line me-1"></i>File Information</h6>

                    <div class="row mb-2">
                        <div class="col-4"><strong>File Name:</strong></div>
                        <div class="col-8" id="fileInfoName">-</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4"><strong>File Size:</strong></div>
                        <div class="col-8" id="fileInfoSize">-</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4"><strong>File Type:</strong></div>
                        <div class="col-8" id="fileInfoType">-</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4"><strong>Category:</strong></div>
                        <div class="col-8" id="fileInfoCategory">-</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4"><strong>Version:</strong></div>
                        <div class="col-8" id="fileInfoVersion">-</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4"><strong>Uploaded By:</strong></div>
                        <div class="col-8" id="fileInfoUploader">-</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4"><strong>Upload Date:</strong></div>
                        <div class="col-8" id="fileInfoDate">-</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4"><strong>Downloads:</strong></div>
                        <div class="col-8" id="fileInfoDownloads">-</div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-4"><strong>Public Access:</strong></div>
                        <div class="col-8" id="fileInfoPublic">-</div>
                    </div>

                    <div class="row" id="fileInfoTaskRow" style="display: none;">
                        <div class="col-4"><strong>Linked Task:</strong></div>
                        <div class="col-8" id="fileInfoTask">-</div>
                    </div>
                </div>
            </div>

            <!-- File Description -->
            <div class="mb-3" id="fileDescriptionSection" style="display: none;">
                <h6 class="text-primary mb-2"><i class="ri-file-text-line me-1"></i>Description</h6>
                <p class="text-muted" id="fileDescription">-</p>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-2 justify-content-center">
                <a href="#" id="downloadFileBtn" class="btn btn-primary btn-sm" target="_blank">
                    <i class="ri-download-line me-1"></i>Download
                </a>
                <button type="button" class="btn btn-info btn-sm" id="viewFileBtn" target="_blank">
                    <i class="ri-eye-line me-1"></i>View File
                </button>
                <button type="button" class="btn btn-warning btn-sm" id="editFileBtn">
                    <i class="ri-edit-line me-1"></i>Edit Details
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="deleteFileBtn">
                    <i class="ri-delete-bin-line me-1"></i>Delete
                </button>
            </div>
        </div>
    </div>

    <script>
    // This will be populated when modal is opened
    function populateFileDetails(fileData) {
        if (!fileData) return;

        // File preview
        const previewContent = document.getElementById('filePreviewContent');
        const fileType = (fileData.fileType || '').toLowerCase();
        const fileURL = fileData.fileURL || '#';

        let previewHTML = '';
        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(fileType)) {
            previewHTML = `<img src="${fileURL}" class="img-fluid rounded" style="max-height: 300px;" alt="File preview">`;
        } else {
            const iconClass = getFileIcon(fileType);
            previewHTML = `<i class="${iconClass} fs-1 text-primary"></i>`;
        }
        previewContent.innerHTML = previewHTML;

        // File information
        document.getElementById('fileInfoName').textContent = fileData.fileOriginalName || fileData.fileName || 'Unknown';
        document.getElementById('fileInfoSize').textContent = formatFileSize(fileData.fileSize || 0);
        document.getElementById('fileInfoType').textContent = fileData.fileType || 'Unknown';
        document.getElementById('fileInfoCategory').textContent = fileData.category ? fileData.category.charAt(0).toUpperCase() + fileData.category.slice(1) : 'Not specified';
        document.getElementById('fileInfoVersion').textContent = fileData.version || '1.0';
        document.getElementById('fileInfoUploader').textContent = fileData.uploaderName || 'Unknown';
        document.getElementById('fileInfoDate').textContent = fileData.DateAdded ? new Date(fileData.DateAdded).toLocaleDateString() : '-';
        document.getElementById('fileInfoDownloads').textContent = fileData.downloadCount || 0;
        document.getElementById('fileInfoPublic').innerHTML = (fileData.isPublic === 'Y')
            ? '<span class="badge bg-success">Yes</span>'
            : '<span class="badge bg-secondary">No</span>';

        // Task linkage
        if (fileData.taskID && fileData.taskName) {
            document.getElementById('fileInfoTaskRow').style.display = 'flex';
            document.getElementById('fileInfoTask').textContent = fileData.taskName;
        } else {
            document.getElementById('fileInfoTaskRow').style.display = 'none';
        }

        // Description
        if (fileData.description) {
            document.getElementById('fileDescriptionSection').style.display = 'block';
            document.getElementById('fileDescription').textContent = fileData.description;
        } else {
            document.getElementById('fileDescriptionSection').style.display = 'none';
        }

        // Download button
        document.getElementById('downloadFileBtn').href = fileURL;

        // View button
        document.getElementById('viewFileBtn').onclick = function() {
            window.open(fileURL, '_blank');
        };

        // Edit button
        document.getElementById('editFileBtn').onclick = function() {
            // TODO: Open edit modal
            console.log('Edit file:', fileData.fileID);
        };

        // Delete button
        document.getElementById('deleteFileBtn').onclick = function() {
            if (confirm('Are you sure you want to delete this file? This action cannot be undone.')) {
                // Call the global deleteFile function
                if (typeof deleteFile === 'function') {
                    deleteFile(fileData.fileID);
                    // Close modal after deletion
                    const modal = bootstrap.Modal.getInstance(document.getElementById('view_file_details'));
                    if (modal) {
                        modal.hide();
                    }
                } else {
                    console.error('Delete function not available');
                    if (typeof showFileToast === 'function') {
                        showFileToast('Delete function is not available. Please refresh the page.', 'error');
                    }
                }
            }
        };
    }

    function getFileIcon(fileType) {
        const icons = {
            'pdf': 'ri-file-pdf-line text-danger',
            'doc': 'ri-file-word-line text-primary',
            'docx': 'ri-file-word-line text-primary',
            'xls': 'ri-file-excel-line text-success',
            'xlsx': 'ri-file-excel-line text-success',
            'ppt': 'ri-file-ppt-line text-warning',
            'pptx': 'ri-file-ppt-line text-warning',
            'jpg': 'ri-image-line text-info',
            'jpeg': 'ri-image-line text-info',
            'png': 'ri-image-line text-info',
            'gif': 'ri-image-line text-info',
            'zip': 'ri-file-zip-line text-secondary',
            'rar': 'ri-file-zip-line text-secondary'
        };
        return icons[fileType] || 'ri-file-line text-muted';
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
    </script>
</div>

