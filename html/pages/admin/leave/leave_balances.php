<?php
/**
 * Leave Balances Administration
 * Upload and review employee leave balances by entity
 */

if(!$isValidUser) {
    Alert::info("You need to be logged in to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

if (!$isAdmin && !$isValidAdmin && !$isHRManager) {
    Alert::error("Access denied. Leave Administrator privileges required.", true, array('text-center'));
    return;
}

$defaultEntityID = (int)($userDetails->entityID ?? ($_SESSION['entityID'] ?? 1));
$selectedEntityID = isset($_GET['entity_filter']) && $_GET['entity_filter'] !== ''
    ? (int)Utility::clean_string($_GET['entity_filter'])
    : $defaultEntityID;

if ($isHRManager && !$isAdmin && !$isValidAdmin) {
    $selectedEntityID = $defaultEntityID;
}

$allEntities = Data::entities_full(array('Suspended' => 'N'), false, $DBConn) ?: array();
$entityMap = array();
foreach ($allEntities as $entity) {
    $entityMap[$entity->entityID] = $entity->entityName;
}

$currentEntityName = $entityMap[$selectedEntityID] ?? 'Current Entity';
$leaveTypes = Leave::leave_types(array('Lapsed' => 'N', 'Suspended' => 'N'), false, $DBConn) ?: array();

$manualTableExists = Leave::ensure_manual_balances_table($DBConn);
$manualStats = array('totalEntries' => 0, 'lastUpdated' => null);
if ($manualTableExists && $selectedEntityID) {
    $statsSql = "SELECT COUNT(*) AS totalEntries, MAX(updatedDate) AS lastUpdated
                 FROM tija_leave_manual_balances
                 WHERE entityID = ?
                   AND Lapsed = 'N'
                   AND Suspended = 'N'";
    $statsRows = $DBConn->fetch_all_rows($statsSql, array(array($selectedEntityID, 'i')));
    if ($statsRows && count($statsRows) > 0) {
        $manualStats['totalEntries'] = (int)($statsRows[0]->totalEntries ?? 0);
        $manualStats['lastUpdated'] = $statsRows[0]->lastUpdated ?? null;
    }
}

$employeesSql = "
    SELECT
        ud.ID AS employeeID,
        ud.payrollNo,
        ud.entityID,
        p.FirstName,
        p.Surname,
        p.Email
    FROM user_details ud
    LEFT JOIN people p ON ud.ID = p.ID
    WHERE ud.entityID = ?
      AND ud.Lapsed = 'N'
      AND ud.Suspended = 'N'
    ORDER BY p.FirstName, p.Surname ASC
";
$employees = $DBConn->fetch_all_rows($employeesSql, array(array($selectedEntityID, 'i')));

$employeeRows = array();
if ($employees) {
    foreach ($employees as $employee) {
        $employeeObj = is_object($employee) ? $employee : (object)$employee;
        $fullNameParts = array_filter(array($employeeObj->FirstName ?? '', $employeeObj->Surname ?? ''));
        $employeeName = trim(implode(' ', $fullNameParts));
        $calculatedBalances = Leave::calculate_leave_balances(
            (int)$employeeObj->employeeID,
            (int)($employeeObj->entityID ?? $selectedEntityID),
            $DBConn
        );

        $employeeRows[] = array(
            'id' => (int)$employeeObj->employeeID,
            'name' => $employeeName,
            'payroll' => $employeeObj->payrollNo ?? '',
            'email' => $employeeObj->Email ?? '',
            'balances' => $calculatedBalances
        );
    }
}

$pageTitle = 'Leave Balances';
?>

<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-24 mb-0">
            <i class="ri-wallet-3-line me-2 text-primary"></i>
            <?= htmlspecialchars($pageTitle) ?>
        </h1>
        <p class="text-muted mb-0 mt-2">Upload, review, and audit employee leave balances for <?= htmlspecialchars($currentEntityName) ?></p>
    </div>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= $base ?>html/?s=admin&ss=leave&p=dashboard">Leave Admin</a></li>
                <li class="breadcrumb-item active" aria-current="page">Leave Balances</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card custom-card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="entityFilter" class="form-label text-muted small mb-1">Entity</label>
                <form method="get" class="d-flex">
                    <input type="hidden" name="s" value="admin" />
                    <input type="hidden" name="ss" value="leave" />
                    <input type="hidden" name="p" value="leave_balances" />
                    <select class="form-select" id="entityFilter" name="entity_filter" onchange="this.form.submit()" <?= ($isHRManager && !$isAdmin && !$isValidAdmin) ? 'disabled' : '' ?>>
                        <?php foreach ($entityMap as $entityID => $entityName): ?>
                            <option value="<?= (int)$entityID ?>" <?= ($entityID == $selectedEntityID) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($entityName) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ($isHRManager && !$isAdmin && !$isValidAdmin): ?>
                        <input type="hidden" name="entity_filter" value="<?= (int)$selectedEntityID ?>">
                    <?php endif; ?>
                </form>
            </div>
            <div class="col-md-8">
                <div class="row g-3">
                    <div class="col-sm-4">
                        <div class="p-3 border rounded-3">
                            <p class="text-muted mb-1 small">Manual Entries</p>
                            <h4 class="mb-0"><?= number_format($manualStats['totalEntries']) ?></h4>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-3 border rounded-3">
                            <p class="text-muted mb-1 small">Last Upload</p>
                            <h6 class="mb-0">
                                <?= $manualStats['lastUpdated'] ? Utility::date_format($manualStats['lastUpdated'], 'miniDateTime') : 'Not available' ?>
                            </h6>
                        </div>
                    </div>
                    <div class="col-sm-4 d-flex align-items-center">
                        <div class="btn-group w-100" role="group">
                            <a href="<?= $base ?>php/scripts/leave/utilities/download_leave_balance_template.php?entityID=<?= (int)$selectedEntityID ?>"
                               class="btn btn-outline-primary" title="Download Leave Balance Template">
                                <i class="ri-download-2-line me-1"></i>
                                Balance Template
                            </a>
                            <a href="<?= $base ?>php/scripts/leave/utilities/download_leave_days_template.php?entityID=<?= (int)$selectedEntityID ?>&includeExisting=0"
                               class="btn btn-outline-secondary" title="Download Leave Days Taken Template">
                                <i class="ri-calendar-check-line me-1"></i>
                                Days Template
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Section Tabs -->
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="balances-tab" data-bs-toggle="tab" data-bs-target="#balances-pane" type="button" role="tab">
            <i class="ri-wallet-3-line me-1"></i>Leave Balances
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="days-taken-tab" data-bs-toggle="tab" data-bs-target="#days-taken-pane" type="button" role="tab">
            <i class="ri-calendar-check-line me-1"></i>Leave Days Taken
        </button>
    </li>
</ul>

<div class="tab-content">
    <!-- Leave Balances Tab -->
    <div class="tab-pane fade show active" id="balances-pane" role="tabpanel">
        <div class="row">
            <div class="col-xl-4">
                <div class="card custom-card h-100 mb-4 mb-xl-0">
                    <div class="card-header">
                        <div class="card-title">Upload Leave Balances</div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">
                            Upload the populated CSV template to update opening leave balances. Only payroll numbers that match active employees within <?= htmlspecialchars($currentEntityName) ?> will be processed.
                        </p>
                        <form id="leaveBalanceUploadForm" autocomplete="off" enctype="multipart/form-data" method="post" action="<?= $base ?>php/scripts/leave/utilities/upload_leave_balances.php">
                            <input type="hidden" name="entityID" value="<?= (int)$selectedEntityID ?>">
                            <div class="mb-3">
                                <label for="balanceDate" class="form-label">Balance As Of</label>
                                <input type="date" class="form-control" id="balanceDate" name="balanceDate" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="leaveBalanceFile" class="form-label">CSV File</label>
                                <input type="file" class="form-control" id="leaveBalanceFile" name="leaveBalanceFile" accept=".csv" required>
                                <div class="form-text">Use the downloaded template and keep header names unchanged.</div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <span class="upload-label">
                                    <i class="ri-upload-2-line me-1"></i> Upload Balances
                                </span>
                                <span class="upload-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Uploading...
                                </span>
                            </button>
                            <div id="uploadFeedback" class="mt-3"></div>
                        </form>
                        <hr>
                        <h6>Template Guidelines</h6>
                        <ul class="text-muted small ps-3">
                            <li>Do not remove or rename the first four columns (payroll number, employee ID, name, email).</li>
                            <li>Enter numeric balances (in days) for each leave type column.</li>
                            <li>Leave a value blank to skip updating that leave type for the employee.</li>
                            <li>Only employees in the selected entity will be matched.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-xl-8">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">Employee Leave Balances</div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($employeeRows)): ?>
                            <div class="alert alert-info mb-0">
                                No active employees found for <?= htmlspecialchars($currentEntityName) ?>.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered text-nowrap align-middle" id="leaveBalanceTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Payroll No</th>
                                            <th>Employee</th>
                                            <th>Email</th>
                                            <?php foreach ($leaveTypes as $leaveType): ?>
                                                <th><?= htmlspecialchars($leaveType->leaveTypeName ?? 'Leave Type') ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($employeeRows as $row): ?>
                                            <tr>
                                                <td><span class="fw-semibold"><?= htmlspecialchars($row['payroll'] ?: '—') ?></span></td>
                                                <td>
                                                    <div class="fw-semibold"><?= htmlspecialchars($row['name']) ?></div>
                                                    <small class="text-muted">#<?= (int)$row['id'] ?></small>
                                                </td>
                                                <td><?= htmlspecialchars($row['email'] ?: '—') ?></td>
                                                <?php foreach ($leaveTypes as $leaveType):
                                                    $key = strtolower(str_replace(' ', '_', $leaveType->leaveTypeName ?? 'leave'));
                                                    $balanceData = $row['balances'][$key] ?? null;
                                                    $availableDays = $balanceData['available'] ?? 0;
                                                    $sourceLabel = ($balanceData['source'] ?? 'policy') === 'manual_upload' ? 'Manual' : 'Policy';
                                                    $tooltip = ($balanceData['source'] ?? '') === 'manual_upload'
                                                        ? 'Manual upload reference' . ($balanceData['as_of'] ? ' (' . htmlspecialchars($balanceData['as_of']) . ')' : '')
                                                        : 'Derived from policy entitlement';
                                                ?>
                                                    <td>
                                                        <div class="fw-semibold"><?= number_format((float)$availableDays, 2) ?> days</div>
                                                        <small class="text-muted" title="<?= htmlspecialchars($tooltip) ?>">
                                                            <?= htmlspecialchars($sourceLabel) ?>
                                                        </small>
                                                    </td>
                                                <?php endforeach; ?>
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

    <!-- Leave Days Taken Tab -->
    <div class="tab-pane fade" id="days-taken-pane" role="tabpanel">
        <div class="row">
            <div class="col-xl-4">
                <div class="card custom-card h-100 mb-4 mb-xl-0">
                    <div class="card-header">
                        <div class="card-title">Upload Leave Days Taken</div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">
                            Upload leave days taken to synchronize with leave applications. The system will create or update leave application records based on the CSV data.
                        </p>
                        <div class="mb-3">
                            <a href="<?= $base ?>php/scripts/leave/utilities/download_leave_days_template.php?entityID=<?= (int)$selectedEntityID ?>&includeExisting=0"
                               class="btn btn-outline-primary w-100 mb-2">
                                <i class="ri-download-2-line me-1"></i>
                                Download Empty Template
                            </a>
                            <a href="<?= $base ?>php/scripts/leave/utilities/download_leave_days_template.php?entityID=<?= (int)$selectedEntityID ?>&includeExisting=1"
                               class="btn btn-outline-secondary w-100">
                                <i class="ri-file-download-line me-1"></i>
                                Download with Existing Data
                            </a>
                        </div>
                        <form id="leaveDaysUploadForm" autocomplete="off" enctype="multipart/form-data" method="post" action="<?= $base ?>php/scripts/leave/utilities/upload_leave_days_taken.php">
                            <input type="hidden" name="entityID" value="<?= (int)$selectedEntityID ?>">
                            <div class="mb-3">
                                <label for="leaveDaysFile" class="form-label">CSV File</label>
                                <input type="file" class="form-control" id="leaveDaysFile" name="leaveDaysFile" accept=".csv" required>
                                <div class="form-text">Use the downloaded template and keep header names unchanged.</div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <span class="upload-days-label">
                                    <i class="ri-upload-2-line me-1"></i> Upload Leave Days
                                </span>
                                <span class="upload-days-spinner d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Uploading...
                                </span>
                            </button>
                            <div id="uploadDaysFeedback" class="mt-3"></div>
                        </form>
                        <hr>
                        <h6>Template Guidelines</h6>
                        <ul class="text-muted small ps-3">
                            <li>Payroll Number, Leave Type, Start Date, and End Date are required.</li>
                            <li>Days Taken will be auto-calculated if left blank (based on working days).</li>
                            <li>Status: 1=New, 3=Pending, 4=Approved (defaults to 4 if not specified).</li>
                            <li>Duplicate entries (same employee, leave type, dates) will be updated instead of creating new records.</li>
                            <li>Date format must be YYYY-MM-DD (e.g., 2025-01-15).</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-xl-8">
                <div class="card custom-card">
                    <div class="card-header">
                        <div class="card-title">Leave Applications Overview</div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            After uploading leave days taken, the records will appear in the leave applications system.
                            You can view detailed leave applications from the <a href="<?= $base ?>html/?s=admin&ss=leave&p=reports">Reports</a> page.
                        </p>
                        <div class="alert alert-info mb-0">
                            <i class="ri-information-line me-2"></i>
                            <strong>Note:</strong> Uploaded leave applications will be automatically approved (status 4) unless you specify a different status in the CSV file.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Leave Balances Upload Form
    const balanceForm = document.getElementById('leaveBalanceUploadForm');
    const balanceFeedback = document.getElementById('uploadFeedback');

    if (balanceForm) {
        const balanceSpinner = balanceForm.querySelector('.upload-spinner');
        const balanceLabel = balanceForm.querySelector('.upload-label');

        balanceForm.addEventListener('submit', function (event) {
            event.preventDefault();
            balanceFeedback.innerHTML = '';

            balanceSpinner.classList.remove('d-none');
            balanceLabel.classList.add('d-none');

            const formData = new FormData(balanceForm);

            fetch(balanceForm.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    balanceFeedback.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                    if (data.stats) {
                        balanceFeedback.innerHTML += `<div class="mt-2 small text-muted">
                            Processed: ${data.stats.rowsProcessed} rows |
                            Matched: ${data.stats.employeesMatched} employees |
                            Updated: ${data.stats.balancesUpdated} balances
                        </div>`;
                    }
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    balanceFeedback.innerHTML = `<div class="alert alert-danger">${data.message || 'Upload failed.'}</div>`;
                }
            })
            .catch(() => {
                balanceFeedback.innerHTML = '<div class="alert alert-danger">An unexpected error occurred while uploading balances.</div>';
            })
            .finally(() => {
                balanceSpinner.classList.add('d-none');
                balanceLabel.classList.remove('d-none');
            });
        });
    }

    // Leave Days Taken Upload Form
    const daysForm = document.getElementById('leaveDaysUploadForm');
    const daysFeedback = document.getElementById('uploadDaysFeedback');

    if (daysForm) {
        const daysSpinner = daysForm.querySelector('.upload-days-spinner');
        const daysLabel = daysForm.querySelector('.upload-days-label');

        daysForm.addEventListener('submit', function (event) {
            event.preventDefault();
            daysFeedback.innerHTML = '';

            daysSpinner.classList.remove('d-none');
            daysLabel.classList.add('d-none');

            const formData = new FormData(daysForm);

            fetch(daysForm.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let message = `<div class="alert alert-success">${data.message}</div>`;
                    if (data.stats) {
                        message += `<div class="mt-2 small text-muted">
                            <strong>Statistics:</strong><br>
                            Rows Processed: ${data.stats.rowsProcessed}<br>
                            Employees Matched: ${data.stats.employeesMatched}<br>
                            Applications Created: ${data.stats.applicationsCreated}<br>
                            Applications Updated: ${data.stats.applicationsUpdated}<br>
                            Skipped Rows: ${data.stats.skippedRows}
                        </div>`;
                    }
                    if (data.errors && data.errors.length > 0) {
                        message += `<div class="mt-2 alert alert-warning">
                            <strong>Warnings:</strong>
                            <ul class="mb-0 small">`;
                        data.errors.slice(0, 10).forEach(error => {
                            message += `<li>${error}</li>`;
                        });
                        if (data.errors.length > 10) {
                            message += `<li>... and ${data.errors.length - 10} more errors</li>`;
                        }
                        message += `</ul></div>`;
                    }
                    daysFeedback.innerHTML = message;
                    setTimeout(() => window.location.reload(), 3000);
                } else {
                    daysFeedback.innerHTML = `<div class="alert alert-danger">${data.message || 'Upload failed.'}</div>`;
                }
            })
            .catch(() => {
                daysFeedback.innerHTML = '<div class="alert alert-danger">An unexpected error occurred while uploading leave days.</div>';
            })
            .finally(() => {
                daysSpinner.classList.add('d-none');
                daysLabel.classList.remove('d-none');
            });
        });
    }
});
</script>

