<div id="upload_project_file_form" class="upload_project_file_form">
    <!-- Hidden Fields -->
    <div class="form-group d-none">
        <input type="hidden" name="projectID" id="projectID" value="<?php echo $projectID; ?>">
        <input type="hidden" name="uploadedBy" value="<?php echo $userDetails->ID ?? ''; ?>">
    </div>

    <!-- File Upload Area -->
    <div class="form-group mb-3">
        <label for="projectFile" class="form-label fw-semibold text-primary">
            <i class="ri-upload-cloud-line me-1"></i>Select File to Upload
        </label>
        <div class="file-upload-area border-2 border-dashed rounded p-4 text-center bg-light" style="cursor: pointer;" onclick="document.getElementById('projectFile').click();">
            <input type="file"
                   id="projectFile"
                   name="projectFile"
                   class="d-none"
                   accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.gif,.webp,.zip,.rar"
                   required>
            <i class="ri-file-upload-line fs-1 text-primary mb-2 d-block"></i>
            <p class="mb-0">
                <span class="text-primary fw-semibold">Click to upload</span> or drag and drop
            </p>
            <small class="text-muted">PDF, DOC, XLS, Images, ZIP (Max 50MB)</small>
        </div>
        <div id="filePreview" class="mt-2"></div>
    </div>

    <!-- File Information -->
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="fileOriginalName" class="form-label text-primary">File Name</label>
                <input type="text"
                       id="fileOriginalName"
                       name="fileOriginalName"
                       class="form-control form-control-sm"
                       placeholder="Enter file name (optional)">
                <small class="form-text text-muted">Leave blank to use original filename</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="fileCategory" class="form-label text-primary">Category</label>
                <select id="fileCategory" name="category" class="form-control form-control-sm">
                    <option value="">Select Category</option>
                    <option value="contract">Contract</option>
                    <option value="design">Design</option>
                    <option value="report">Report</option>
                    <option value="invoice">Invoice</option>
                    <option value="proposal">Proposal</option>
                    <option value="documentation">Documentation</option>
                    <option value="meeting">Meeting Notes</option>
                    <option value="other">Other</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Optional Task Linkage -->
    <div class="form-group">
        <label for="taskID" class="form-label text-primary">Link to Task (Optional)</label>
        <select id="taskID" name="taskID" class="form-control form-control-sm">
            <option value="">No Task Link</option>
            <?php
            // Get project tasks for linking
            $projectTasks = Projects::projects_tasks(array('projectID' => $projectID, 'Suspended' => 'N'), false, $DBConn);
            if ($projectTasks && is_array($projectTasks)):
                foreach ($projectTasks as $task):
            ?>
                <option value="<?= $task->projectTaskID ?>">
                    <?= htmlspecialchars($task->projectTaskCode . ' - ' . $task->projectTaskName) ?>
                </option>
            <?php
                endforeach;
            endif;
            ?>
        </select>
    </div>

    <!-- File Description -->
    <div class="form-group">
        <label for="fileDescription" class="form-label text-primary">Description</label>
        <textarea id="fileDescription"
                  name="description"
                  class="form-control form-control-sm"
                  rows="3"
                  placeholder="Add a description for this file (optional)"></textarea>
    </div>

    <!-- Public Access Toggle -->
    <div class="form-group">
        <div class="form-check form-switch">
            <input class="form-check-input"
                   type="checkbox"
                   id="isPublic"
                   name="isPublic"
                   value="Y">
            <label class="form-check-label" for="isPublic">
                <i class="ri-global-line me-1"></i>Make file accessible to client
            </label>
        </div>
        <small class="form-text text-muted">Public files can be viewed by the client</small>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('projectFile');
        const fileNameInput = document.getElementById('fileOriginalName');
        const filePreview = document.getElementById('filePreview');

        // Update filename input when file is selected
        fileInput.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const fileName = file.name;

                // Auto-fill filename if empty
                if (!fileNameInput.value) {
                    fileNameInput.value = fileName.replace(/\.[^/.]+$/, ""); // Remove extension
                }

                // Show file preview
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                const fileType = file.type || 'Unknown';

                filePreview.innerHTML = `
                    <div class="alert alert-info d-flex align-items-center">
                        <i class="ri-file-line fs-4 me-2"></i>
                        <div class="flex-grow-1">
                            <strong>${fileName}</strong><br>
                            <small>Size: ${fileSize} MB | Type: ${fileType}</small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearFile()">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                `;
            }
        });

        // Drag and drop functionality
        const uploadArea = document.querySelector('.file-upload-area');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
            }, false);
        });

        uploadArea.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length > 0) {
                fileInput.files = files;
                fileInput.dispatchEvent(new Event('change'));
            }
        }, false);
    });

    function clearFile() {
        document.getElementById('projectFile').value = '';
        document.getElementById('filePreview').innerHTML = '';
        document.getElementById('fileOriginalName').value = '';
    }
    </script>
</div>

