<?php
/**
 * Operational Projects - User
 *
 * View operational projects (BAU buckets)
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

global $DBConn, $userID;

// Get operational projects
$projects = $DBConn->retrieve_db_table_rows('tija_operational_projects',
    ['operationalProjectID', 'projectCode', 'projectName', 'functionalArea', 'fiscalYear', 'allocatedHours', 'actualHours', 'fteRequirement', 'DateAdded'],
    ['Suspended' => 'N']);

$pageTitle = "Operational Projects";
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
                        <li class="breadcrumb-item active">Projects</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Total Projects</p>
                            <h4 class="mb-2"><?php echo is_array($projects) ? count($projects) : 0; ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-primary rounded-3">
                                <i class="ri-folder-line font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Total Allocated</p>
                            <h4 class="mb-2 text-info"><?php
                                $totalAllocated = is_array($projects) ? array_sum(array_column($projects, 'allocatedHours')) : 0;
                                echo number_format($totalAllocated, 0); ?> hrs
                            </h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-info rounded-3">
                                <i class="ri-time-line font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Total Actual</p>
                            <h4 class="mb-2 text-success"><?php
                                $totalActual = is_array($projects) ? array_sum(array_column($projects, 'actualHours')) : 0;
                                echo number_format($totalActual, 0); ?> hrs
                            </h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-success rounded-3">
                                <i class="ri-check-line font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <p class="text-truncate font-size-14 mb-2">Total FTE</p>
                            <h4 class="mb-2 text-warning"><?php
                                $totalFTE = is_array($projects) ? array_sum(array_column($projects, 'fteRequirement')) : 0;
                                echo number_format($totalFTE, 2);
                            ?></h4>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-light text-warning rounded-3">
                                <i class="ri-user-line font-size-18"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Operational Projects</h4>
                </div>
                <div class="card-body">
                    <?php if (empty($projects)): ?>
                        <div class="text-center py-5">
                            <i class="ri-folder-line fs-1 text-muted mb-3"></i>
                            <h5 class="text-muted">No Operational Projects Found</h5>
                            <p class="text-muted">Operational projects (BAU buckets) will appear here.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="projectsTable">
                                <thead>
                                    <tr>
                                        <th>Project Code</th>
                                        <th>Project Name</th>
                                        <th>Functional Area</th>
                                        <th>Fiscal Year</th>
                                        <th>Allocated Hours</th>
                                        <th>Actual Hours</th>
                                        <th>Utilization</th>
                                        <th>FTE</th>
                                        <th width="100" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($projects as $project):
                                        $utilization = $project['allocatedHours'] > 0
                                            ? ($project['actualHours'] / $project['allocatedHours']) * 100
                                            : 0;
                                    ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($project['projectCode'] ?? 'N/A'); ?></span>
                                            </td>
                                            <td>
                                                <div class="fw-semibold"><?php echo htmlspecialchars($project['projectName'] ?? 'Unknown'); ?></div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($project['functionalArea'] ?? 'N/A'); ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($project['fiscalYear'] ?? 'N/A'); ?></td>
                                            <td><?php echo number_format($project['allocatedHours'] ?? 0, 0); ?> hrs</td>
                                            <td><?php echo number_format($project['actualHours'] ?? 0, 0); ?> hrs</td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-<?php echo $utilization > 100 ? 'danger' : ($utilization > 80 ? 'warning' : 'success'); ?>"
                                                         role="progressbar"
                                                         style="width: <?php echo min($utilization, 100); ?>%">
                                                        <?php echo number_format($utilization, 1); ?>%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo number_format($project['fteRequirement'] ?? 0, 2); ?></span>
                                            </td>
                                            <td class="text-end">
                                                <a href="?s=user&ss=operational&p=projects&action=view&id=<?php echo $project['operationalProjectID']; ?>"
                                                   class="btn btn-sm btn-info" title="View">
                                                    <i class="ri-eye-line"></i>
                                                </a>
                                            </td>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('projectsTable')) {
        $('#projectsTable').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[3, 'desc']],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ projects per page"
            }
        });
    }
});
</script>

