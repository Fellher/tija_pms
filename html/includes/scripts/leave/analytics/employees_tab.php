<?php
/**
 * Employee Drill-Down Tab - Leave Analytics Dashboard
 * Individual employee leave analysis with search and filtering
 */

// Get all employees in scope
$employeeWhere = array('ud.Lapsed = ?', 'ud.Suspended = ?');
$employeeParams = array(array('N', 's'), array('N', 's'));

if ($selectedOrgDataID) {
    $employeeWhere[] = 'ud.orgDataID = ?';
    $employeeParams[] = array($selectedOrgDataID, 'i');
}

if ($selectedEntityID) {
    $employeeWhere[] = 'ud.entityID = ?';
    $employeeParams[] = array($selectedEntityID, 'i');
}

$employeeWhereClause = implode(' AND ', $employeeWhere);

$employeeSql = "SELECT
                    p.ID as employeeID,
                    CONCAT(p.FirstName, ' ', p.Surname) as employeeName,
                    p.Email,
                    jt.jobTitle,
                    bu.businessUnitName as department,
                    (SELECT COUNT(*) FROM tija_leave_applications la
                     WHERE la.employeeID = p.ID
                     AND la.startDate >= ?
                     AND la.startDate <= ?
                     AND la.Lapsed = 'N' AND la.Suspended = 'N') as totalApplications,
                    (SELECT SUM(la.noOfDays) FROM tija_leave_applications la
                     WHERE la.employeeID = p.ID
                     AND la.leaveStatusID = 6
                     AND la.startDate >= ?
                     AND la.startDate <= ?
                     AND la.Lapsed = 'N' AND la.Suspended = 'N') as approvedDays,
                    (SELECT COUNT(*) FROM tija_leave_applications la
                     WHERE la.employeeID = p.ID
                     AND la.leaveStatusID = 3
                     AND la.Lapsed = 'N' AND la.Suspended = 'N') as pendingApplications
                FROM people p
                INNER JOIN user_details ud ON p.ID = ud.ID
                LEFT JOIN tija_job_titles jt ON ud.jobTitleID = jt.jobTitleID
                LEFT JOIN tija_business_units bu ON ud.businessUnitID = bu.businessUnitID
                WHERE {$employeeWhereClause}
                ORDER BY approvedDays DESC, employeeName";

$employeeParams = array_merge(
    $employeeParams,
    array(
        array($startDate, 's'),
        array($endDate, 's'),
        array($startDate, 's'),
        array($endDate, 's')
    )
);

$employees = $DBConn->fetch_all_rows($employeeSql, $employeeParams);
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="ri-user-search-line me-2 text-primary"></i>Employee Leave Summary</h5>
                    <div class="input-group" style="width: 300px;">
                        <span class="input-group-text"><i class="ri-search-line"></i></span>
                        <input type="text" class="form-control" id="employeeSearch" placeholder="Search employees...">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="employeesTable">
                        <thead class="table-light">
                            <tr>
                                <th>Employee</th>
                                <th>Department</th>
                                <th class="text-end">Applications</th>
                                <th class="text-end">Days Approved</th>
                                <th class="text-end">Pending</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($employees): ?>
                                <?php foreach ($employees as $emp): ?>
                                <?php $empData = is_object($emp) ? (array)$emp : $emp; ?>
                                <tr class="employee-row"
                                    data-employee-id="<?php echo $empData['employeeID']; ?>"
                                    data-employee-name="<?php echo htmlspecialchars($empData['employeeName']); ?>">
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold"><?php echo htmlspecialchars($empData['employeeName']); ?></span>
                                            <small class="text-muted"><?php echo htmlspecialchars($empData['Email'] ?? ''); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?php echo htmlspecialchars($empData['department'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
                                    <td class="text-end"><?php echo $empData['totalApplications']; ?></td>
                                    <td class="text-end fw-semibold"><?php echo number_format($empData['approvedDays'] ?? 0, 1); ?></td>
                                    <td class="text-end">
                                        <?php if ($empData['pendingApplications'] > 0): ?>
                                            <span class="badge bg-warning text-dark"><?php echo $empData['pendingApplications']; ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary view-employee-details"
                                                data-employee-id="<?php echo $empData['employeeID']; ?>"
                                                data-employee-name="<?php echo htmlspecialchars($empData['employeeName']); ?>">
                                            <i class="ri-eye-line me-1"></i> View Details
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No employee data available</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Employee Details Modal -->
<div class="modal fade" id="employeeDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="employeeDetailsModalLabel">Employee Leave History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="employeeDetailsContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Loading employee details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="exportEmployeeReport">
                    <i class="ri-download-line me-1"></i> Export Report
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Employee search
document.getElementById('employeeSearch')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('.employee-row');

    rows.forEach(row => {
        const name = row.dataset.employeeName.toLowerCase();
        row.style.display = name.includes(searchTerm) ? '' : 'none';
    });
});

// View employee details
document.querySelectorAll('.view-employee-details').forEach(btn => {
    btn.addEventListener('click', function() {
        const employeeID = this.dataset.employeeId;
        const employeeName = this.dataset.employeeName;

        const modal = new bootstrap.Modal(document.getElementById('employeeDetailsModal'));
        document.getElementById('employeeDetailsModalLabel').textContent = `Leave History - ${employeeName}`;

        loadEmployeeDetails(employeeID);
        modal.show();
    });
});

function loadEmployeeDetails(employeeID) {
    const content = document.getElementById('employeeDetailsContent');
    content.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2 text-muted">Loading employee details...</p>
        </div>
    `;

    fetch(`${analyticsConfig.baseUrl}php/scripts/leave/analytics/get_employee_details.php?employeeID=${employeeID}&year=${new Date().getFullYear()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderEmployeeDetails(data);
            } else {
                content.innerHTML = `<div class="alert alert-danger">${data.message || 'Failed to load details'}</div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = `<div class="alert alert-danger">Error loading employee details</div>`;
        });
}

function renderEmployeeDetails(data) {
    const content = document.getElementById('employeeDetailsContent');
    const summary = data.summary || {};
    const applications = data.applications || [];

    let html = `
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="text-muted small">Total Applications</h6>
                        <h3 class="mb-0">${summary.totalApplications || 0}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success bg-opacity-10">
                    <div class="card-body text-center">
                        <h6 class="text-muted small">Approved</h6>
                        <h3 class="mb-0 text-success">${summary.approvedApplications || 0}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger bg-opacity-10">
                    <div class="card-body text-center">
                        <h6 class="text-muted small">Rejected</h6>
                        <h3 class="mb-0 text-danger">${summary.rejectedApplications || 0}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary bg-opacity-10">
                    <div class="card-body text-center">
                        <h6 class="text-muted small">Days Taken</h6>
                        <h3 class="mb-0 text-primary">${summary.totalDaysTaken || 0}</h3>
                    </div>
                </div>
            </div>
        </div>

        <h6 class="mb-3">Leave Applications</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Leave Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Days</th>
                        <th>Status</th>
                        <th>Applied On</th>
                    </tr>
                </thead>
                <tbody>
    `;

    if (applications.length > 0) {
        applications.forEach(app => {
            const statusBadge = app.leaveStatusID == 6 ? 'bg-success' :
                              (app.leaveStatusID == 4 ? 'bg-danger' : 'bg-warning text-dark');
            html += `
                <tr>
                    <td>${app.leaveTypeName || 'N/A'}</td>
                    <td>${app.startDate || 'N/A'}</td>
                    <td>${app.endDate || 'N/A'}</td>
                    <td>${app.noOfDays || 0}</td>
                    <td><span class="badge ${statusBadge}">${app.leaveStatusName || 'Unknown'}</span></td>
                    <td>${app.dateApplied ? new Date(app.dateApplied).toLocaleDateString() : 'N/A'}</td>
                </tr>
            `;
        });
    } else {
        html += '<tr><td colspan="6" class="text-center text-muted">No leave applications found</td></tr>';
    }

    html += `
                </tbody>
            </table>
        </div>
    `;

    content.innerHTML = html;
}
</script>

