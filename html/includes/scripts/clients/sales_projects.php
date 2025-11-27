<?php
/**
 * Sales & Projects Overview
 * Enterprise-level view of client sales cases, ongoing projects, and closed projects
 */

// Calculate statistics
$salesCount = $sales && is_array($sales) ? count($sales) : 0;
$projectsCount = $projects && is_array($projects) ? count($projects) : 0;
$totalSalesValue = 0;
$totalProjectsValue = 0;

if ($sales && is_array($sales)) {
    foreach ($sales as $sale) {
        $totalSalesValue += $sale->salesCaseEstimate;
    }
}

if ($projects && is_array($projects)) {
    foreach ($projects as $proj) {
        $totalProjectsValue += $proj->projectValue;
    }
}

$completedProjects = Projects::projects_full(array('clientID'=>$clientID, 'projectStatus'=>'Completed'), false, $DBConn);
$lostSales = Sales::sales_case_mid(array('clientID'=>$clientID, 'closeStatus'=>'lost'), false, $DBConn);
?>

<!-- Page Header with Documentation -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-2">
        <h2 class="page-title fw-semibold fs-22 mb-0">
            <i class="ri-briefcase-line text-primary me-2"></i>
            Sales & Projects Overview
        </h2>
        <button type="button"
                class="btn btn-sm btn-link text-primary p-0"
                data-bs-toggle="modal"
                data-bs-target="#salesProjectsDocModal"
                title="View Sales & Projects documentation">
            <i class="ri-information-line fs-20"></i>
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card border-start border-primary border-4 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted text-uppercase small mb-1">Active Sales Cases</h6>
                        <h3 class="mb-0 fw-bold"><?= $salesCount ?></h3>
                    </div>
                    <div class="text-primary">
                        <i class="ri-money-dollar-circle-line fs-32"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-start border-success border-4 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted text-uppercase small mb-1">Total Sales Value</h6>
                        <h3 class="mb-0 fw-bold">KES <?= number_format($totalSalesValue, 2) ?></h3>
                    </div>
                    <div class="text-success">
                        <i class="ri-line-chart-line fs-32"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-start border-info border-4 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted text-uppercase small mb-1">Ongoing Projects</h6>
                        <h3 class="mb-0 fw-bold"><?= $projectsCount ?></h3>
                    </div>
                    <div class="text-info">
                        <i class="ri-projector-line fs-32"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-start border-warning border-4 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted text-uppercase small mb-1">Total Projects Value</h6>
                        <h3 class="mb-0 fw-bold">KES <?= number_format($totalProjectsValue, 2) ?></h3>
                    </div>
                    <div class="text-warning">
                        <i class="ri-folder-chart-line fs-32"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Active Sales Cases Section -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <div class="d-flex align-items-center gap-2">
            <h3 class="card-title mb-0 fw-semibold fs-18">
                <i class="ri-money-dollar-circle-line text-primary me-2"></i>
                Active Sales Cases
            </h3>
            <button type="button"
                    class="btn btn-sm btn-link text-primary p-0"
                    data-bs-toggle="modal"
                    data-bs-target="#salesCasesDocModal"
                    title="View Sales Cases documentation">
                <i class="ri-information-line fs-16"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <?php if ($sales && is_array($sales) && count($sales) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="fw-semibold">Case Name</th>
                            <th class="fw-semibold">Sales Person</th>
                            <th class="fw-semibold text-end">Sales Estimate</th>
                            <th class="fw-semibold">Status</th>
                            <th class="fw-semibold text-center">Probability</th>
                            <th class="fw-semibold">Expected Close Date</th>
                            <th class="text-center" style="width: 80px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sales as $k => $sale):
                            $OrderDate = date_create($sale->expectedCloseDate);
                            $salesPerson = Core::user_name($sale->salesPersonID, $DBConn);
                            $probability = (int)$sale->levelPercentage;
                            $statusBadgeClass = '';
                            if ($probability >= 80) {
                                $statusBadgeClass = 'bg-success';
                            } elseif ($probability >= 50) {
                                $statusBadgeClass = 'bg-warning';
                            } else {
                                $statusBadgeClass = 'bg-danger';
                            }
                        ?>
                            <tr>
                                <td>
                                    <a href="<?= $base ?>html/?s=<?= $s ?>&ss=sales&p=sale_details&saleid=<?= $sale->salesCaseID ?>"
                                       class="text-decoration-none fw-medium">
                                        <i class="ri-eye-line text-primary me-2"></i>
                                        <?= htmlspecialchars($sale->salesCaseName) ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="ri-user-line text-muted me-2"></i>
                                        <span><?= htmlspecialchars($salesPerson) ?></span>
                                    </div>
                                </td>
                                <td class="text-end fw-semibold text-primary">
                                    KES <?= number_format($sale->salesCaseEstimate, 2, '.', ',') ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark text-capitalize">
                                        <?= htmlspecialchars($sale->statusLevel) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $statusBadgeClass ?>">
                                        <?= $probability ?>%
                                    </span>
                                </td>
                                <td>
                                    <i class="ri-calendar-line text-muted me-1"></i>
                                    <?= $OrderDate->format('d M Y') ?>
                                </td>
                                <td class="text-center">
                                    <a href=""
                                       class="editSales text-primary"
                                       data-id="<?= $sale->salesCaseID ?>"
                                       title="Edit Sale">
                                        <i class="ri-edit-line fs-18"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <?php Alert::warning("There are no active sales cases for {$clientDetails->clientName} at the moment"); ?>
        <?php endif; ?>
    </div>
</div>

<!-- Ongoing Projects Section -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <div class="d-flex align-items-center gap-2">
            <h3 class="card-title mb-0 fw-semibold fs-18">
                <i class="ri-projector-line text-info me-2"></i>
                Ongoing Projects
            </h3>
            <button type="button"
                    class="btn btn-sm btn-link text-primary p-0"
                    data-bs-toggle="modal"
                    data-bs-target="#ongoingProjectsDocModal"
                    title="View Ongoing Projects documentation">
                <i class="ri-information-line fs-16"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <?php if ($projects && is_array($projects) && count($projects) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="fw-semibold">Project Code</th>
                            <th class="fw-semibold">Project Name</th>
                            <th class="fw-semibold">Duration</th>
                            <th class="fw-semibold">Project Owner</th>
                            <th class="fw-semibold text-end">Project Value</th>
                            <th class="fw-semibold text-end">Work Hours</th>
                            <th class="text-center" style="width: 80px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $proj => $projVal):
                            $phases = Projects::project_phases(array('projectID'=>$projVal->projectID, 'Suspended'=>"N"), false, $DBConn);
                            $workHours = 0;
                            if ($phases && is_array($phases)) {
                                foreach ($phases as $key => $phase) {
                                    $workHours += $phase->phaseWorkHrs;
                                }
                            }
                            $projectOwner = Core::user_name($projVal->projectOwnerID, $DBConn);
                            $projectValue = number_format($projVal->projectValue, 2, '.', ',');
                            $startDate = date_create($projVal->projectStart);
                            $closeDate = date_create($projVal->projectClose);
                        ?>
                            <tr>
                                <td>
                                    <a href="<?= $base ?>html/?s=<?= $s ?>&ss=projects&p=project&pid=<?= $projVal->projectID ?>"
                                       class="text-decoration-none fw-medium">
                                        <i class="ri-eye-line text-info me-2"></i>
                                        <?= htmlspecialchars($projVal->projectCode) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($projVal->projectName) ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="ri-calendar-line text-muted me-1"></i>
                                        <small>
                                            <?= $startDate->format('d M Y') ?> - <?= $closeDate->format('d M Y') ?>
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="ri-user-line text-muted me-2"></i>
                                        <span><?= htmlspecialchars($projectOwner) ?></span>
                                    </div>
                                </td>
                                <td class="text-end fw-semibold text-info">
                                    KES <?= $projectValue ?>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-light text-dark">
                                        <i class="ri-time-line me-1"></i>
                                        <?= number_format($workHours, 0) ?> hrs
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href=""
                                       class="editProject text-info"
                                       data-id="<?= $projVal->projectID ?>"
                                       title="Edit Project">
                                        <i class="ri-edit-line fs-18"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <?php Alert::warning("There are no ongoing projects for {$clientDetails->clientName} at the moment"); ?>
        <?php endif; ?>
    </div>
</div>

<!-- Closed Projects & Lost Sales Section -->
<div class="card shadow-sm">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <h3 class="card-title mb-0 fw-semibold fs-18">
                <i class="ri-archive-line text-secondary me-2"></i>
                Closed Projects & Lost Sales
            </h3>
            <button type="button"
                    class="btn btn-sm btn-link text-primary p-0"
                    data-bs-toggle="modal"
                    data-bs-target="#closedProjectsDocModal"
                    title="View Closed Projects documentation">
                <i class="ri-information-line fs-16"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Completed Projects -->
        <div class="mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h4 class="fw-semibold fs-16 mb-0">
                    <i class="ri-checkbox-circle-line text-success me-2"></i>
                    Completed Projects
                </h4>
                <?php if ($completedProjects && is_array($completedProjects)): ?>
                    <span class="badge bg-success"><?= count($completedProjects) ?> Completed</span>
                <?php endif; ?>
            </div>
            <?php if ($completedProjects && is_array($completedProjects) && count($completedProjects) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="fw-semibold">Project Code</th>
                                <th class="fw-semibold">Project Name</th>
                                <th class="fw-semibold">Completion Date</th>
                                <th class="fw-semibold text-end">Project Value</th>
                                <th class="text-center" style="width: 80px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($completedProjects as $completed):
                                $completedValue = number_format($completed->projectValue, 2, '.', ',');
                                $completionDate = !empty($completed->projectClose) ? date_create($completed->projectClose) : null;
                            ?>
                                <tr>
                                    <td>
                                        <a href="<?= $base ?>html/?s=<?= $s ?>&ss=projects&p=project&pid=<?= $completed->projectID ?>"
                                           class="text-decoration-none">
                                            <i class="ri-eye-line text-success me-2"></i>
                                            <?= htmlspecialchars($completed->projectCode) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($completed->projectName) ?></td>
                                    <td>
                                        <?php if ($completionDate): ?>
                                            <i class="ri-calendar-check-line text-muted me-1"></i>
                                            <?= $completionDate->format('d M Y') ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end fw-semibold text-success">
                                        KES <?= $completedValue ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= $base ?>html/?s=<?= $s ?>&ss=projects&p=project&pid=<?= $completed->projectID ?>"
                                           class="text-success"
                                           title="View Project">
                                            <i class="ri-eye-line fs-18"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <?php Alert::info("There are no completed projects for {$clientDetails->clientName} at the moment"); ?>
            <?php endif; ?>
        </div>

        <hr class="my-4">

        <!-- Lost Sales -->
        <div>
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h4 class="fw-semibold fs-16 mb-0">
                    <i class="ri-close-circle-line text-danger me-2"></i>
                    Lost Sales
                </h4>
                <?php if ($lostSales && is_array($lostSales)): ?>
                    <span class="badge bg-danger"><?= count($lostSales) ?> Lost</span>
                <?php endif; ?>
            </div>
            <?php if ($lostSales && is_array($lostSales) && count($lostSales) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="fw-semibold">Case Name</th>
                                <th class="fw-semibold">Sales Person</th>
                                <th class="fw-semibold text-end">Estimated Value</th>
                                <th class="fw-semibold">Lost Date</th>
                                <th class="text-center" style="width: 80px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lostSales as $lost):
                                $lostSalesPerson = Core::user_name($lost->salesPersonID, $DBConn);
                                $lostValue = number_format($lost->salesCaseEstimate, 2, '.', ',');
                                $lostDate = !empty($lost->expectedCloseDate) ? date_create($lost->expectedCloseDate) : null;
                            ?>
                                <tr>
                                    <td>
                                        <a href="<?= $base ?>html/?s=<?= $s ?>&ss=sales&p=sale_details&saleid=<?= $lost->salesCaseID ?>"
                                           class="text-decoration-none">
                                            <i class="ri-eye-line text-danger me-2"></i>
                                            <?= htmlspecialchars($lost->salesCaseName) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="ri-user-line text-muted me-2"></i>
                                            <span><?= htmlspecialchars($lostSalesPerson) ?></span>
                                        </div>
                                    </td>
                                    <td class="text-end fw-semibold text-danger">
                                        KES <?= $lostValue ?>
                                    </td>
                                    <td>
                                        <?php if ($lostDate): ?>
                                            <i class="ri-calendar-close-line text-muted me-1"></i>
                                            <?= $lostDate->format('d M Y') ?>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= $base ?>html/?s=<?= $s ?>&ss=sales&p=sale_details&saleid=<?= $lost->salesCaseID ?>"
                                           class="text-danger"
                                           title="View Sale">
                                            <i class="ri-eye-line fs-18"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <?php Alert::info("There are no lost sales for {$clientDetails->clientName} at the moment"); ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modals -->
<?php
echo Utility::form_modal_header("manageProjectCase", "projects/manage_project_case.php", "Add New Project Case", array("modal-dialog-centered", "modal-lg"), $base, true );
include 'includes/scripts/projects/modals/manage_project_cases.php';
echo Utility::form_modal_footer();
echo Utility::form_modal_header("manageSale", "sales/manage_sale.php", "Manage Sale", array('modal-md', 'modal-dialog-centered'), $base);
include "includes/scripts/sales/modals/manage_sale.php";
echo Utility::form_modal_footer('Save Sale', 'saveSale',  ' btn btn-success btn-sm', true);
?>

<!-- ============================================================================
     SALES & PROJECTS DOCUMENTATION MODAL
     ============================================================================ -->
<div class="modal fade" id="salesProjectsDocModal" tabindex="-1" aria-labelledby="salesProjectsDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="salesProjectsDocModalLabel">
                    <i class="ri-briefcase-line me-2"></i>
                    Sales & Projects Overview Guide
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4">
                    <h6 class="text-primary mb-3">
                        <i class="ri-eye-line me-2"></i>
                        Overview
                    </h6>
                    <p class="text-muted">
                        The Sales & Projects Overview provides a comprehensive view of all sales cases, ongoing projects,
                        and closed activities for this client. This page helps you track the complete business relationship
                        lifecycle, from active opportunities to completed work and lost sales.
                    </p>
                </div>

                <div class="mb-4">
                    <h6 class="text-primary mb-3">
                        <i class="ri-navigation-line me-2"></i>
                        Page Navigation
                    </h6>
                    <div class="card border-primary-transparent mb-3">
                        <div class="card-body">
                            <p class="mb-2"><strong>Accessing This Page:</strong></p>
                            <ol class="mb-0">
                                <li class="mb-2">Navigate to the <strong>Clients</strong> section from the main menu</li>
                                <li class="mb-2">Select a client from the client directory</li>
                                <li class="mb-2">Click on the <strong>"Sales & Projects"</strong> tab in the client details page</li>
                                <li class="mb-2">You'll see an overview dashboard with statistics and detailed tables</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="text-primary mb-3">
                        <i class="ri-dashboard-line me-2"></i>
                        Understanding the Dashboard
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-start border-primary border-3">
                                <div class="card-body">
                                    <h6 class="fw-semibold mb-2">
                                        <i class="ri-money-dollar-circle-line text-primary me-2"></i>
                                        Active Sales Cases
                                    </h6>
                                    <p class="small text-muted mb-0">
                                        Shows the total number of active sales opportunities currently in progress for this client.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-start border-success border-3">
                                <div class="card-body">
                                    <h6 class="fw-semibold mb-2">
                                        <i class="ri-line-chart-line text-success me-2"></i>
                                        Total Sales Value
                                    </h6>
                                    <p class="small text-muted mb-0">
                                        Displays the combined estimated value of all active sales cases in Kenyan Shillings (KES).
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-start border-info border-3">
                                <div class="card-body">
                                    <h6 class="fw-semibold mb-2">
                                        <i class="ri-projector-line text-info me-2"></i>
                                        Ongoing Projects
                                    </h6>
                                    <p class="small text-muted mb-0">
                                        Count of all active projects currently being executed for this client.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-start border-warning border-3">
                                <div class="card-body">
                                    <h6 class="fw-semibold mb-2">
                                        <i class="ri-folder-chart-line text-warning me-2"></i>
                                        Total Projects Value
                                    </h6>
                                    <p class="small text-muted mb-0">
                                        Sum of all ongoing project values, representing the total revenue from active projects.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="text-primary mb-3">
                        <i class="ri-table-line me-2"></i>
                        Section Overview
                    </h6>
                    <div class="list-group">
                        <div class="list-group-item">
                            <h6 class="fw-semibold mb-2">
                                <i class="ri-money-dollar-circle-line text-primary me-2"></i>
                                Active Sales Cases
                            </h6>
                            <p class="small text-muted mb-2">
                                View all active sales opportunities with details including case name, sales person,
                                estimated value, status, probability percentage, and expected close date. Click on
                                a case name to view full details or use the edit icon to modify information.
                            </p>
                            <ul class="small text-muted mb-0">
                                <li><strong>Probability Badges:</strong> Green (80%+), Yellow (50-79%), Red (&lt;50%)</li>
                                <li><strong>View Details:</strong> Click on a case name to view full details</li>
                                <li><strong>Edit Sale:</strong> Click the edit icon in the Actions column</li>
                            </ul>
                        </div>
                        <div class="list-group-item">
                            <h6 class="fw-semibold mb-2">
                                <i class="ri-projector-line text-info me-2"></i>
                                Ongoing Projects
                            </h6>
                            <p class="small text-muted mb-2">
                                Monitor all active projects including project code, name, duration, owner, value,
                                and estimated work hours. Access project details by clicking the project code.
                            </p>
                            <ul class="small text-muted mb-0">
                                <li><strong>Project Duration:</strong> Shows start and end dates</li>
                                <li><strong>Work Hours:</strong> Calculated from project phases</li>
                                <li><strong>View Details:</strong> Click on a project code to view full project details</li>
                            </ul>
                        </div>
                        <div class="list-group-item">
                            <h6 class="fw-semibold mb-2">
                                <i class="ri-archive-line text-secondary me-2"></i>
                                Closed Projects & Lost Sales
                            </h6>
                            <p class="small text-muted mb-2">
                                Review historical data including completed projects and lost sales opportunities.
                                This helps analyze past performance and identify patterns.
                            </p>
                            <ul class="small text-muted mb-0">
                                <li><strong>Completed Projects:</strong> Successfully finished projects with completion dates</li>
                                <li><strong>Lost Sales:</strong> Sales opportunities that did not convert</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mb-0">
                    <h6 class="alert-heading">
                        <i class="ri-lightbulb-line me-2"></i>
                        Pro Tips
                    </h6>
                    <ul class="mb-0 small">
                        <li>Use the statistics cards at the top to quickly assess the client's business value</li>
                        <li>Monitor probability percentages to prioritize follow-up activities</li>
                        <li>Review closed projects to understand project delivery patterns</li>
                        <li>Analyze lost sales to identify areas for improvement</li>
                        <li>Click on any case or project name to view comprehensive details</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================================
     SALES CASES DOCUMENTATION MODAL
     ============================================================================ -->
<div class="modal fade" id="salesCasesDocModal" tabindex="-1" aria-labelledby="salesCasesDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="salesCasesDocModalLabel">
                    <i class="ri-money-dollar-circle-line me-2"></i>
                    Active Sales Cases Guide
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <p class="text-muted">
                        Learn how to manage and track active sales opportunities for this client. Sales cases represent
                        potential business opportunities that are currently in progress.
                    </p>
                </div>

                <div class="card border-primary-transparent mb-3">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">Understanding Sales Cases</h6>
                        <ul class="mb-0">
                            <li class="mb-2">
                                <strong>Case Name:</strong> The name or title of the sales opportunity. Click to view full details.
                            </li>
                            <li class="mb-2">
                                <strong>Sales Person:</strong> The employee responsible for managing this sales opportunity.
                            </li>
                            <li class="mb-2">
                                <strong>Sales Estimate:</strong> The estimated value of the opportunity in Kenyan Shillings (KES).
                            </li>
                            <li class="mb-2">
                                <strong>Status:</strong> Current stage of the sales process (e.g., Lead, Proposal, Negotiation).
                            </li>
                            <li class="mb-2">
                                <strong>Probability %:</strong> Likelihood of closing the sale, indicated by color-coded badges:
                                <ul class="mt-1">
                                    <li><span class="badge bg-success">Green (80%+)</span> - High probability</li>
                                    <li><span class="badge bg-warning">Yellow (50-79%)</span> - Medium probability</li>
                                    <li><span class="badge bg-danger">Red (&lt;50%)</span> - Low probability</li>
                                </ul>
                            </li>
                            <li class="mb-2">
                                <strong>Expected Close Date:</strong> Anticipated date when the sale will be finalized.
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card border-info-transparent mb-3">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">Managing Sales Cases</h6>
                        <ol class="mb-0">
                            <li class="mb-2">
                                <strong>View Details:</strong> Click on any case name to open the detailed sales case page with full information.
                            </li>
                            <li class="mb-2">
                                <strong>Edit Sale:</strong> Click the edit icon (<i class="ri-edit-line"></i>) in the Actions column to modify sale information.
                            </li>
                            <li class="mb-2">
                                <strong>Update Status:</strong> Edit the sale to change its status and probability as it progresses through the sales pipeline.
                            </li>
                            <li class="mb-2">
                                <strong>Note:</strong> To add new sales cases, please navigate to the Sales module from the main menu.
                            </li>
                        </ol>
                    </div>
                </div>

                <div class="alert alert-info mb-0">
                    <h6 class="alert-heading">
                        <i class="ri-lightbulb-line me-2"></i>
                        Best Practices
                    </h6>
                    <ul class="mb-0 small">
                        <li>Regularly update probability percentages based on client feedback and progress</li>
                        <li>Keep expected close dates realistic and update them as needed</li>
                        <li>Assign sales cases to appropriate team members</li>
                        <li>Review high-probability cases frequently to ensure timely follow-up</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================================
     ONGOING PROJECTS DOCUMENTATION MODAL
     ============================================================================ -->
<div class="modal fade" id="ongoingProjectsDocModal" tabindex="-1" aria-labelledby="ongoingProjectsDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="ongoingProjectsDocModalLabel">
                    <i class="ri-projector-line me-2"></i>
                    Ongoing Projects Guide
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <p class="text-muted">
                        Learn how to manage and monitor active projects for this client. Projects represent work
                        currently being executed and delivered to the client.
                    </p>
                </div>

                <div class="card border-info-transparent mb-3">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">Understanding Project Information</h6>
                        <ul class="mb-0">
                            <li class="mb-2">
                                <strong>Project Code:</strong> Unique identifier for the project. Click to view full project details.
                            </li>
                            <li class="mb-2">
                                <strong>Project Name:</strong> Descriptive name or title of the project.
                            </li>
                            <li class="mb-2">
                                <strong>Duration:</strong> Project start and end dates showing the timeline.
                            </li>
                            <li class="mb-2">
                                <strong>Project Owner:</strong> The employee responsible for managing and delivering the project.
                            </li>
                            <li class="mb-2">
                                <strong>Project Value:</strong> Total contract value in Kenyan Shillings (KES).
                            </li>
                            <li class="mb-2">
                                <strong>Work Hours:</strong> Total estimated work hours calculated from all project phases.
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card border-primary-transparent mb-3">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">Managing Projects</h6>
                        <ol class="mb-0">
                            <li class="mb-2">
                                <strong>View Project Details:</strong> Click on any project code to access the comprehensive project management page.
                            </li>
                            <li class="mb-2">
                                <strong>Edit Project:</strong> Click the edit icon (<i class="ri-edit-line"></i>) in the Actions column to modify project information.
                            </li>
                            <li class="mb-2">
                                <strong>Monitor Progress:</strong> Use the project details page to track phases, tasks, and deliverables.
                            </li>
                            <li class="mb-2">
                                <strong>Note:</strong> To add new projects, please navigate to the Projects module from the main menu.
                            </li>
                        </ol>
                    </div>
                </div>

                <div class="alert alert-info mb-0">
                    <h6 class="alert-heading">
                        <i class="ri-lightbulb-line me-2"></i>
                        Best Practices
                    </h6>
                    <ul class="mb-0 small">
                        <li>Ensure project owners are correctly assigned for accountability</li>
                        <li>Keep project durations and timelines updated</li>
                        <li>Monitor work hours to track resource allocation</li>
                        <li>Review project values regularly for accurate financial reporting</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================================
     CLOSED PROJECTS DOCUMENTATION MODAL
     ============================================================================ -->
<div class="modal fade" id="closedProjectsDocModal" tabindex="-1" aria-labelledby="closedProjectsDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="closedProjectsDocModalLabel">
                    <i class="ri-archive-line me-2"></i>
                    Closed Projects & Lost Sales Guide
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <p class="text-muted">
                        Learn how to review and analyze historical data including completed projects and lost sales
                        opportunities. This information helps understand past performance and identify improvement areas.
                    </p>
                </div>

                <div class="card border-success-transparent mb-3">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">
                            <i class="ri-checkbox-circle-line text-success me-2"></i>
                            Completed Projects
                        </h6>
                        <p class="mb-2">Projects that have been successfully finished and delivered to the client.</p>
                        <ul class="mb-0">
                            <li class="mb-2">
                                <strong>Project Code & Name:</strong> Click on the project code to view full project history and details.
                            </li>
                            <li class="mb-2">
                                <strong>Completion Date:</strong> Date when the project was marked as completed.
                            </li>
                            <li class="mb-2">
                                <strong>Project Value:</strong> Final contract value that was delivered.
                            </li>
                            <li class="mb-2">
                                <strong>Use Cases:</strong> Review completed projects to understand delivery patterns,
                                identify successful project types, and reference past work for similar future projects.
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card border-danger-transparent mb-3">
                    <div class="card-body">
                        <h6 class="fw-semibold mb-3">
                            <i class="ri-close-circle-line text-danger me-2"></i>
                            Lost Sales
                        </h6>
                        <p class="mb-2">Sales opportunities that did not convert into actual business.</p>
                        <ul class="mb-0">
                            <li class="mb-2">
                                <strong>Case Name:</strong> Click to review the full sales case details and understand why it was lost.
                            </li>
                            <li class="mb-2">
                                <strong>Sales Person:</strong> The team member who was managing the opportunity.
                            </li>
                            <li class="mb-2">
                                <strong>Estimated Value:</strong> The potential value that was not realized.
                            </li>
                            <li class="mb-2">
                                <strong>Lost Date:</strong> When the opportunity was marked as lost.
                            </li>
                            <li class="mb-2">
                                <strong>Analysis:</strong> Review lost sales to identify common reasons, competitive factors,
                                pricing issues, or areas where the sales process can be improved.
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="alert alert-info mb-0">
                    <h6 class="alert-heading">
                        <i class="ri-lightbulb-line me-2"></i>
                        Analytical Insights
                    </h6>
                    <ul class="mb-0 small">
                        <li>Compare completed project values to identify high-value client relationships</li>
                        <li>Analyze lost sales patterns to improve future sales strategies</li>
                        <li>Use historical data to forecast future project opportunities</li>
                        <li>Review project completion timelines to improve project planning</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Enterprise-level styling enhancements */
.card {
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08) !important;
}

.card-header {
    border-bottom: 2px solid #f0f0f0;
    padding: 1rem 1.25rem;
}

.table th {
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #495057;
    border-bottom: 2px solid #dee2e6;
}

.table td {
    vertical-align: middle;
    padding: 0.875rem 0.75rem;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
    transition: background-color 0.2s ease;
}

.badge {
    font-weight: 500;
    padding: 0.35em 0.65em;
}

.border-primary-transparent {
    border-color: rgba(13, 110, 253, 0.2) !important;
}

.border-info-transparent {
    border-color: rgba(13, 202, 240, 0.2) !important;
}

.border-success-transparent {
    border-color: rgba(25, 135, 84, 0.2) !important;
}

.border-danger-transparent {
    border-color: rgba(220, 53, 69, 0.2) !important;
}

.list-group-item {
    border-left: 3px solid transparent;
    transition: all 0.2s ease;
}

.list-group-item:hover {
    border-left-color: #0d6efd;
    background-color: #f8f9fa;
}

.text-decoration-none:hover {
    text-decoration: underline !important;
}
</style>
