<?php
$hrAssignmentsEnabled = false;
$entityHrAssignments = array();
$hrAssignmentSummary = array('primary' => null, 'substitute' => null);
$tableCheck = $DBConn->fetch_all_rows("SHOW TABLES LIKE 'tija_entity_hr_assignments'", array());
$hrAssignmentsEnabled = $tableCheck && count($tableCheck) > 0;

if ($hrAssignmentsEnabled) {
    $assignmentRows = $DBConn->fetch_all_rows(
        "SELECT a.userID, a.roleType,
                CONCAT(COALESCE(p.FirstName, ''), ' ', COALESCE(p.Surname, '')) AS hrEmployeeName,
                p.Email
         FROM tija_entity_hr_assignments a
         LEFT JOIN people p ON a.userID = p.ID
         LEFT JOIN user_details ud ON a.userID = ud.ID
         WHERE a.entityID = ?
           AND a.Lapsed = 'N' AND a.Suspended = 'N'
           AND ud.Lapsed = 'N' AND ud.Suspended = 'N'",
        array(array($entityID, 'i'))
    );

    if ($assignmentRows) {
        foreach ($assignmentRows as $assignment) {
            $roleType = strtolower($assignment->roleType ?? '');
            $userId = (int)$assignment->userID;
            $entityHrAssignments[$userId] = $roleType;
            if ($roleType === 'primary' || $roleType === 'substitute') {
                $hrAssignmentSummary[$roleType] = $assignment;
            }
        }
    }
}

$employeesByID = array();
if (!empty($entityEmployees)) {
    foreach ($entityEmployees as $employeeRecord) {
        $employeesByID[$employeeRecord->ID] = $employeeRecord;
    }
}
?>
<!-- Employees Tab -->
<div class="row">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i>Entity Employees (<?= $employeeCount ?>)</h5>
                </div>
                <div class="d-flex gap-2">

                    <?php

                    //check if user has permission to perform bulk actions
                    $canBulkActions = false;
                    if ($isValidAdmin || $isHRManager || $isAdmin || $isSuperAdmin || $isTenantAdmin) {
                        $canBulkActions = true;
                    }

                    if ($canBulkActions): ?>
                    <div class="btn-group" id="bulkActionsGroup" >
                        <button type="button" class="btn btn-warning btn-sm btn-wave" id="sendBulkResetEmailsBtn" title="Send reset emails to selected employees">
                            <i class="fas fa-envelope me-2"></i>Send Reset Emails (<span id="selectedCount">0</span>)
                        </button>
                        <button type="button" class="btn btn-warning btn-sm btn-wave" id="sendAllResetEmailsBtn" title="Send reset emails to all employees">
                            <i class="fas fa-envelope-open me-2"></i>Send to All
                        </button>
                    </div>
                    <?php endif; ?>
                <button type="button" class="btn btn-primary btn-sm btn-wave"
                    data-bs-toggle="modal"
                    data-bs-target="#addEmployeeModal"
                    onclick="addEmployee(<?= $entityID ?>)">
                    <i class="fas fa-user-plus me-2"></i>Add Employee
                </button>
                </div>
            </div>
            <div class="card-body">
                <?php if ($entityEmployees): ?>
                    <!-- Search and Pagination Controls -->
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2 flex-grow-1" style="min-width: 300px;">
                            <div class="input-group" style="max-width: 400px;">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text"
                                       class="form-control"
                                       id="employeeSearchInput"
                                       placeholder="Search by name, payroll number, or job title...">
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label class="mb-0 text-muted small">Show:</label>
                            <select class="form-select form-select-sm" id="employeesPerPageSelect" style="width: auto;">
                                <option value="30">30</option>
                                <option value="50" selected>50</option>
                                <option value="100">100</option>
                            </select>
                            <span class="text-muted small">per page</span>
                        </div>
                    </div>

                    <!-- Table Info Display -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="text-muted small" id="tableInfo">
                            Showing <span id="showingStart">0</span> to <span id="showingEnd">0</span> of <span id="totalCount"><?= $employeeCount ?></span> employees
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0" id="employeesTable">
                            <thead class="table-light">
                                <tr>
                                    <?php if ($canBulkActions): ?>
                                    <th style="width: 40px;">
                                        <input type="checkbox" id="selectAllEmployees" class="form-check-input" title="Select/Deselect All">
                                    </th>
                                    <?php endif; ?>
                                    <th class="sortable" data-sort="payrollNo" style="cursor: pointer;">
                                        Payroll Number
                                        <i class="fas fa-sort ms-1 text-muted"></i>
                                    </th>
                                    <th class="sortable" data-sort="employeeName" style="cursor: pointer;">
                                        Employee
                                        <i class="fas fa-sort ms-1 text-muted"></i>
                                    </th>
                                    <th class="sortable" data-sort="jobTitle" style="cursor: pointer;">
                                        Job Title
                                        <i class="fas fa-sort ms-1 text-muted"></i>
                                    </th>
                                    <th class="sortable" data-sort="department" style="cursor: pointer;">
                                        Department
                                        <i class="fas fa-sort ms-1 text-muted"></i>
                                    </th>
                                    <th class="sortable" data-sort="supervisor" style="cursor: pointer;">
                                        Supervisor
                                        <i class="fas fa-sort ms-1 text-muted"></i>
                                    </th>
                                    <th class="text-center">HR Role</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="employeesTableBody">
                                <?php foreach ($entityEmployees as $emp):
                                    // Get the department from unit assigned to the employee
                                    $unitsAssigned = Employee::user_unit_assignments(['userID' => $emp->ID], false, $DBConn);

                                    // Department unit id is 1 in the unitsAssigned array
                                    if($unitsAssigned) {
                                        foreach($unitsAssigned as $unit) {
                                            if($unit->unitTypeID == 1) {
                                                $emp->departmentName = $unit->unitName;
                                            }
                                        }
                                    } else {
                                        $emp->departmentName = 'Unknown';
                                    }
                                ?>
                                    <tr data-employee-id="<?= $emp->ID ?>" data-employee-email="<?= htmlspecialchars($emp->Email ?? '', ENT_QUOTES) ?>">
                                        <?php if ($canBulkActions): ?>
                                        <td>
                                            <input type="checkbox" class="form-check-input employee-checkbox"
                                                   value="<?= $emp->ID ?>"
                                                   data-email="<?= htmlspecialchars($emp->Email ?? '', ENT_QUOTES) ?>"
                                                   data-name="<?= htmlspecialchars($emp->fullName ?? $emp->employeeName ?? 'Employee', ENT_QUOTES) ?>">
                                        </td>
                                        <?php endif; ?>
                                        <td><?= htmlspecialchars($emp->payrollNo ?? 'N/A') ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (isset($emp->profile_image) && $emp->profile_image): ?>
                                                    <img src="<?= "{$base}data/uploaded_files/{$emp->profile_image}" ?>"
                                                        alt="Profile" class="avatar avatar-sm rounded-circle me-2">
                                                <?php else: ?>
                                                    <div class="avatar avatar-sm bg-primary-transparent rounded-circle me-2">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="d-block">
                                                    <h6 class="mb-0 fw-bold font-12"><?= htmlspecialchars($emp->fullName ?? $emp->employeeName ?? 'N/A') ?></h6>
                                                    <span class="text-muted small ms-2">
                                                        <?= htmlspecialchars($emp->Email ?? '') ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($emp->jobTitle ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($emp->departmentName ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($emp->supervisorName ?? 'N/A') ?></td>
                                        <?php
                                            $currentHrRole = $entityHrAssignments[$emp->ID] ?? null;
                                            $hasLegacyHrAccess = !$currentHrRole && strtoupper((string)($emp->isHRManager ?? 'N')) === 'Y';
                                            $hrRoleDataAttr = $currentHrRole ?? ($hasLegacyHrAccess ? 'general' : 'none');
                                        ?>
                                        <td class="text-center">
                                            <?php if ($currentHrRole === 'primary'): ?>
                                                <span class="badge bg-primary-transparent text-primary px-3 py-1">
                                                    <i class="fas fa-star me-1"></i> Primary
                                                </span>
                                            <?php elseif ($currentHrRole === 'substitute'): ?>
                                                <span class="badge bg-info-transparent text-info px-3 py-1">
                                                    <i class="fas fa-sync-alt me-1"></i> Substitute
                                                </span>
                                            <?php elseif ($hasLegacyHrAccess): ?>
                                                <span class="badge bg-warning-transparent text-warning px-3 py-1">
                                                    <i class="fas fa-user-shield me-1"></i> HR Manager
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-transparent text-muted px-3 py-1">
                                                    <i class="fas fa-ban me-1"></i> None
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= "{$base}html/?s=user&p=profile&uid={$emp->ID}" ?>"
                                                class="btn btn-sm btn-info-light"
                                                title="View Employee">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= "{$base}html/?s=user&p=profile&uid={$emp->ID}" ?>"
                                                class="btn btn-sm btn-primary-light"
                                                title="Edit Employee">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="#resetEmail"
                                               data-bs-toggle="modal"
                                               data-id="<?= $emp->ID ?>"
                                               data-email="<?= $emp->Email ?? '' ?>"
                                               class="btn btn-sm btn-warning-light resetemail"
                                               title="Send Reset Password Link">
                                                <i class="fas fa-key"></i>
                                            </a>
                                            <button type="button"
                                                    class="btn btn-sm btn-success-light assign-hr-manager"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#assignHRManagerModal"
                                                    data-user-id="<?= $emp->ID ?>"
                                                    data-entity-id="<?= $entityID ?>"
                                                    data-employee-name="<?= htmlspecialchars($emp->fullName ?? $emp->employeeName ?? 'Employee', ENT_QUOTES) ?>"
                                                    data-employee-email="<?= htmlspecialchars($emp->Email ?? '', ENT_QUOTES) ?>"
                                                    data-hr-role="<?= htmlspecialchars($hrRoleDataAttr, ENT_QUOTES) ?>"
                                                    data-hr-status="<?= ($emp->isHRManager ?? 'N') === 'Y' ? 'Y' : 'N' ?>"
                                                    title="<?= ($emp->isHRManager ?? 'N') === 'Y' ? 'Manage HR Manager Access' : 'Grant HR Manager Access' ?>">
                                                <i class="fas fa-user-shield"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Controls -->
                    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                        <div id="paginationContainer" class="pagination-container">
                            <!-- Pagination will be generated by JavaScript -->
                        </div>
                        <div class="text-muted small">
                            Page <span id="currentPage">1</span> of <span id="totalPages">1</span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="avatar avatar-lg bg-primary-transparent mx-auto mb-3">
                            <i class="fas fa-users fs-24"></i>
                        </div>
                        <h6 class="mb-2">No Employees Assigned</h6>
                        <p class="text-muted mb-3">Add employees to this entity.</p>
                        <button type="button" class="btn btn-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#addEmployeeModal"
                            onclick="addEmployee(<?= $entityID ?>)">
                            <i class="fas fa-user-plus me-2"></i>Add First Employee
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<link rel="stylesheet" href="<?= $base ?>assets/libs/sweetalert2/sweetalert2.min.css">
<script src="<?= $base ?>assets/libs/sweetalert2/sweetalert2.all.min.js"></script>

<!-- Employee Table Management Script -->
<script>
/**
 * Employee Table Management
 * Handles sorting, searching, and pagination for the employee table
 */
(function() {
    'use strict';

    // Store all table rows data
    const allEmployees = [];
    let filteredEmployees = [];
    let currentPage = 1;
    let itemsPerPage = 50;
    let sortColumn = 'payrollNo';
    let sortDirection = 'asc';
    let searchTimeout = null;

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeEmployeeTable();
    });

    /**
     * Initialize the employee table
     */
    function initializeEmployeeTable() {
        const tbody = document.getElementById('employeesTableBody');
        if (!tbody) return;

        // Extract employee data from table rows
        const rows = tbody.querySelectorAll('tr');
        const hasCheckbox = rows.length > 0 && rows[0].querySelector('.employee-checkbox');
        const checkboxOffset = hasCheckbox ? 1 : 0;

        rows.forEach((row, index) => {
            const cells = row.querySelectorAll('td');
            const minCells = hasCheckbox ? 7 : 6; // Checkbox + 6 data columns, or 6 data columns without checkbox
            if (cells.length >= minCells) {
                const employee = {
                    index: index,
                    employeeID: row.dataset.employeeId || '',
                    employeeEmail: row.dataset.employeeEmail || '',
                    payrollNo: cells[checkboxOffset].textContent.trim(),
                    employeeName: cells[checkboxOffset + 1].querySelector('h6') ? cells[checkboxOffset + 1].querySelector('h6').textContent.trim() : cells[checkboxOffset + 1].textContent.trim(),
                    email: cells[checkboxOffset + 1].querySelector('span') ? cells[checkboxOffset + 1].querySelector('span').textContent.trim() : '',
                    jobTitle: cells[checkboxOffset + 2].textContent.trim(),
                    department: cells[checkboxOffset + 3].textContent.trim(),
                    supervisor: cells[checkboxOffset + 4].textContent.trim(),
                    hrManager: cells[checkboxOffset + 5].textContent.trim(),
                    rowHtml: row.outerHTML
                };
                allEmployees.push(employee);
            }
        });

        // Initialize filtered list
        filteredEmployees = [...allEmployees];

        // Set up event listeners
        setupEventListeners();

        // Set up bulk actions
        setupBulkActions();

        // Initial render
        renderTable();
        updatePagination();
    }

    /**
     * Setup bulk action event listeners
     */
    function setupBulkActions() {
        // Select all checkbox
        const selectAllCheckbox = document.getElementById('selectAllEmployees');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.employee-checkbox');
                checkboxes.forEach(cb => {
                    cb.checked = this.checked;
                });
                updateBulkActionsVisibility();
            });
        }

        // Individual checkboxes
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('employee-checkbox')) {
                updateSelectAllState();
                updateBulkActionsVisibility();
            }
        });

        // Send bulk reset emails button
        const sendBulkBtn = document.getElementById('sendBulkResetEmailsBtn');
        if (sendBulkBtn) {
            sendBulkBtn.addEventListener('click', sendBulkResetEmails);
        }

        // Send all reset emails button
        const sendAllBtn = document.getElementById('sendAllResetEmailsBtn');
        if (sendAllBtn) {
            sendAllBtn.addEventListener('click', sendAllResetEmails);
        }
    }

    /**
     * Update select all checkbox state
     */
    function updateSelectAllState() {
        const selectAllCheckbox = document.getElementById('selectAllEmployees');
        if (!selectAllCheckbox) return;

        const checkboxes = document.querySelectorAll('.employee-checkbox');
        const checkedCount = document.querySelectorAll('.employee-checkbox:checked').length;

        if (checkedCount === 0) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
        } else if (checkedCount === checkboxes.length) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = true;
        } else {
            selectAllCheckbox.indeterminate = true;
            selectAllCheckbox.checked = false;
        }
    }

    /**
     * Update bulk actions visibility
     */
    function updateBulkActionsVisibility() {
        const checkedCount = document.querySelectorAll('.employee-checkbox:checked').length;
        const bulkActionsGroup = document.getElementById('bulkActionsGroup');
        const selectedCountSpan = document.getElementById('selectedCount');

        if (bulkActionsGroup) {
            if (checkedCount > 0) {
                bulkActionsGroup.style.display = 'flex';
            } else {
                bulkActionsGroup.style.display = 'none';
            }
        }

        if (selectedCountSpan) {
            selectedCountSpan.textContent = checkedCount;
        }
    }

    function escapeHtml(unsafe = '') {
        return unsafe
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function confirmWithSweetAlert(options = {}) {
        const {
            title = 'Are you sure?',
            text = '',
            html = '',
            icon = 'warning',
            confirmButtonText = 'Yes',
            cancelButtonText = 'Cancel'
        } = options;

        if (typeof Swal !== 'undefined') {
            return Swal.fire({
                title,
                text,
                html,
                icon,
                showCancelButton: true,
                confirmButtonText,
                cancelButtonText,
                reverseButtons: true,
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-primary me-2',
                    cancelButton: 'btn btn-outline-secondary'
                }
            }).then(result => result.isConfirmed);
        }

        let fallbackText = text || title;
        if (!fallbackText && html) {
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            fallbackText = tempDiv.textContent || tempDiv.innerText || html;
        }
        return Promise.resolve(window.confirm(fallbackText));
    }

    function showSweetAlertMessage({ title = 'Notice', text = '', html = '', icon = 'info', confirmButtonText = 'OK' } = {}) {
        if (typeof Swal !== 'undefined') {
            return Swal.fire({
                title,
                text,
                html,
                icon,
                confirmButtonText,
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-primary'
                }
            });
        }

        let fallbackText = text || title;
        if (!fallbackText && html) {
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;
            fallbackText = tempDiv.textContent || tempDiv.innerText || html;
        }
        alert(fallbackText);
        return Promise.resolve();
    }

    /**
     * Send reset emails to selected employees
     */
    function sendBulkResetEmails() {
        const selectedCheckboxes = document.querySelectorAll('.employee-checkbox:checked');
        if (selectedCheckboxes.length === 0) {
            showSweetAlertMessage({
                title: 'Select Employees',
                text: 'Please select at least one employee to send reset emails.',
                icon: 'warning'
            });
            return;
        }

        const employeeIDs = Array.from(selectedCheckboxes).map(cb => cb.value);
        const employeeNames = Array.from(selectedCheckboxes).map(cb => {
            const row = cb.closest('tr');
            return row ? row.querySelector('h6')?.textContent.trim() || 'Employee' : 'Employee';
        });

        const previewNames = employeeNames.slice(0, 5).map(name => `<li>${escapeHtml(name)}</li>`).join('');
        let htmlContent = `
            <p class="mb-2">Send password reset emails to ${employeeIDs.length} selected employee(s)?</p>
            <ul class="text-start ps-4 mb-0">
                ${previewNames}
            </ul>
        `;
        if (employeeNames.length > 5) {
            htmlContent += `<p class="mt-2 text-muted">...and ${employeeNames.length - 5} more</p>`;
        }

        confirmWithSweetAlert({
            title: 'Send Reset Emails?',
            html: htmlContent,
            icon: 'warning',
            confirmButtonText: 'Send Emails',
            cancelButtonText: 'Cancel'
        }).then(confirmed => {
            if (!confirmed) return;
            sendBulkResetEmailsRequest(employeeIDs, false);
        });
    }

    /**
     * Send reset emails to all employees
     */
    function sendAllResetEmails() {
        const totalEmployees = allEmployees.length;
        confirmWithSweetAlert({
            title: 'Send to All Employees?',
            text: `This will send password reset emails to all ${totalEmployees} employees in this entity.`,
            icon: 'warning',
            confirmButtonText: 'Send to All',
            cancelButtonText: 'Cancel'
        }).then(confirmed => {
            if (!confirmed) return;

            const allEmployeeIDs = allEmployees.map(emp => emp.employeeID).filter(id => id);
            sendBulkResetEmailsRequest(allEmployeeIDs, true);
        });
    }

    /**
     * Send bulk reset emails request
     */
    function sendBulkResetEmailsRequest(employeeIDs, isAllEmployees) {
        const sendBtn = isAllEmployees ? document.getElementById('sendAllResetEmailsBtn') : document.getElementById('sendBulkResetEmailsBtn');
        const originalText = sendBtn ? sendBtn.innerHTML : '';

        // Show loading state
        if (sendBtn) {
            sendBtn.disabled = true;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
        }

        // Prepare form data
        const formData = new FormData();
        formData.append('action', 'bulk_reset_emails');
        formData.append('entityID', <?= $entityID ?>);
        formData.append('isAllEmployees', isAllEmployees ? '1' : '0');
        employeeIDs.forEach(id => {
            formData.append('employeeIDs[]', id);
        });

        // Send request
        fetch('<?= $base ?>php/scripts/global/admin/bulk_reset_emails.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const htmlSummary = `
                    <p class="mb-2">Successfully sent password reset emails to <strong>${data.sentCount}</strong> employee(s).</p>
                    ${data.failedCount > 0 ? `<p class="mb-0 text-warning">Failed to send to ${data.failedCount} employee(s).</p>` : ''}
                `;
                showSweetAlertMessage({
                    title: 'Emails Sent',
                    html: htmlSummary,
                    icon: data.failedCount > 0 ? 'warning' : 'success'
                });

                // Clear selections
                document.querySelectorAll('.employee-checkbox:checked').forEach(cb => cb.checked = false);
                const selectAllCheckbox = document.getElementById('selectAllEmployees');
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = false;
                    selectAllCheckbox.indeterminate = false;
                }
                updateBulkActionsVisibility();
            } else {
                showSweetAlertMessage({
                    title: 'Error',
                    text: data.message || 'Failed to send reset emails. Please try again.',
                    icon: 'error'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showSweetAlertMessage({
                title: 'Error',
                text: 'An error occurred while sending reset emails. Please try again.',
                icon: 'error'
            });
        })
        .finally(() => {
            // Restore button state
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.innerHTML = originalText;
            }
        });
    }

    /**
     * Setup event listeners for search, sort, and pagination
     */
    function setupEventListeners() {
        // Search input with debounce
        const searchInput = document.getElementById('employeeSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    performSearch(searchInput.value);
                }, 500); // 500ms delay (half a second)
            });
        }

        // Items per page selector
        const perPageSelect = document.getElementById('employeesPerPageSelect');
        if (perPageSelect) {
            perPageSelect.addEventListener('change', function() {
                itemsPerPage = parseInt(this.value);
                currentPage = 1;
                renderTable();
                updatePagination();
            });
        }

        // Sortable column headers
        const sortableHeaders = document.querySelectorAll('#employeesTable .sortable');
        sortableHeaders.forEach(header => {
            header.addEventListener('click', function() {
                const column = this.getAttribute('data-sort');
                if (sortColumn === column) {
                    sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    sortColumn = column;
                    sortDirection = 'asc';
                }
                updateSortIndicators();
                renderTable();
            });
        });
    }

    /**
     * Perform search on employee data
     */
    function performSearch(searchTerm) {
        const term = searchTerm.toLowerCase().trim();

        if (!term) {
            filteredEmployees = [...allEmployees];
        } else {
            filteredEmployees = allEmployees.filter(emp => {
                return emp.payrollNo.toLowerCase().includes(term) ||
                       emp.employeeName.toLowerCase().includes(term) ||
                       emp.jobTitle.toLowerCase().includes(term);
            });
        }

        currentPage = 1;
        renderTable();
        updatePagination();
    }

    /**
     * Sort filtered employees
     */
    function sortEmployees() {
        filteredEmployees.sort((a, b) => {
            let aVal, bVal;

            switch(sortColumn) {
                case 'payrollNo':
                    aVal = a.payrollNo || '';
                    bVal = b.payrollNo || '';
                    break;
                case 'employeeName':
                    aVal = a.employeeName || '';
                    bVal = b.employeeName || '';
                    break;
                case 'jobTitle':
                    aVal = a.jobTitle || '';
                    bVal = b.jobTitle || '';
                    break;
                case 'department':
                    aVal = a.department || '';
                    bVal = b.department || '';
                    break;
                case 'supervisor':
                    aVal = a.supervisor || '';
                    bVal = b.supervisor || '';
                    break;
                default:
                    aVal = a.payrollNo || '';
                    bVal = b.payrollNo || '';
            }

            // Convert to string and compare
            aVal = String(aVal).toLowerCase();
            bVal = String(bVal).toLowerCase();

            if (sortDirection === 'asc') {
                return aVal.localeCompare(bVal);
            } else {
                return bVal.localeCompare(aVal);
            }
        });
    }

    /**
     * Update sort indicators in table headers
     */
    function updateSortIndicators() {
        const headers = document.querySelectorAll('#employeesTable .sortable');
        headers.forEach(header => {
            const icon = header.querySelector('i');
            const column = header.getAttribute('data-sort');

            if (icon) {
                if (column === sortColumn) {
                    icon.className = sortDirection === 'asc'
                        ? 'fas fa-sort-up ms-1 text-primary'
                        : 'fas fa-sort-down ms-1 text-primary';
                } else {
                    icon.className = 'fas fa-sort ms-1 text-muted';
                }
            }
        });
    }

    /**
     * Render the table with current filters and pagination
     */
    function renderTable() {
        // Sort employees
        sortEmployees();

        // Calculate pagination
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const pageEmployees = filteredEmployees.slice(startIndex, endIndex);

        // Update table body
        const tbody = document.getElementById('employeesTableBody');
        if (!tbody) return;

        tbody.innerHTML = '';
        pageEmployees.forEach(emp => {
            tbody.insertAdjacentHTML('beforeend', emp.rowHtml);
        });

        // Re-attach checkbox event listeners after rendering
        const checkboxes = tbody.querySelectorAll('.employee-checkbox');
        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                updateSelectAllState();
                updateBulkActionsVisibility();
            });
        });

        // Update table info
        updateTableInfo();
    }

    /**
     * Update table information display
     */
    function updateTableInfo() {
        const total = filteredEmployees.length;
        const start = total === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1;
        const end = Math.min(currentPage * itemsPerPage, total);

        const showingStart = document.getElementById('showingStart');
        const showingEnd = document.getElementById('showingEnd');
        const totalCount = document.getElementById('totalCount');

        if (showingStart) showingStart.textContent = start;
        if (showingEnd) showingEnd.textContent = end;
        if (totalCount) totalCount.textContent = total;
    }

    /**
     * Update pagination controls
     */
    function updatePagination() {
        const totalPages = Math.ceil(filteredEmployees.length / itemsPerPage);
        const paginationContainer = document.getElementById('paginationContainer');
        const currentPageSpan = document.getElementById('currentPage');
        const totalPagesSpan = document.getElementById('totalPages');

        if (currentPageSpan) currentPageSpan.textContent = currentPage;
        if (totalPagesSpan) totalPagesSpan.textContent = totalPages;

        if (!paginationContainer) return;

        if (totalPages <= 1) {
            paginationContainer.innerHTML = '';
            return;
        }

        let paginationHtml = '<nav><ul class="pagination pagination-sm mb-0">';

        // Previous button
        paginationHtml += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">`;
        paginationHtml += `<a class="page-link" href="#" data-page="${currentPage - 1}" ${currentPage === 1 ? 'tabindex="-1" aria-disabled="true"' : ''}>`;
        paginationHtml += '<i class="fas fa-chevron-left"></i></a></li>';

        // Page numbers
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

        if (endPage - startPage < maxVisiblePages - 1) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }

        if (startPage > 1) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
            if (startPage > 2) {
                paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            paginationHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}">`;
            paginationHtml += `<a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationHtml += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
        }

        // Next button
        paginationHtml += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">`;
        paginationHtml += `<a class="page-link" href="#" data-page="${currentPage + 1}" ${currentPage === totalPages ? 'tabindex="-1" aria-disabled="true"' : ''}>`;
        paginationHtml += '<i class="fas fa-chevron-right"></i></a></li>';

        paginationHtml += '</ul></nav>';

        paginationContainer.innerHTML = paginationHtml;

        // Add click handlers to pagination links
        paginationContainer.querySelectorAll('a[data-page]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.getAttribute('data-page'));
                if (page >= 1 && page <= totalPages && page !== currentPage) {
                    currentPage = page;
                    renderTable();
                    updatePagination();

                    // Scroll to top of table
                    const table = document.getElementById('employeesTable');
                    if (table) {
                        table.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            });
        });
    }
})();
</script>

<style>
.sortable {
    user-select: none;
    -webkit-user-select: none;
}

.sortable:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

#employeeSearchInput:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.25);
}

.pagination-container .pagination {
    margin-bottom: 0;
}

#tableInfo {
    font-size: 0.875rem;
}

/* Bulk Actions Styling */
#bulkActionsGroup {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.employee-checkbox {
    cursor: pointer;
}

.employee-checkbox:checked {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}

#selectAllEmployees {
    cursor: pointer;
}

#selectAllEmployees:indeterminate {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}

/* Table row hover effect for selected rows */
tr:has(.employee-checkbox:checked) {
    background-color: rgba(var(--bs-primary-rgb), 0.05);
}
</style>
