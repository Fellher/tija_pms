<?php
$getString .= "&state={$state}";

// Check admin access
if(!$isAdmin && !$isValidAdmin) {
    Alert::info("You need to be logged in as a valid administrator to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    return;
}

// Get organization chart ID from URL
$orgChartID = (isset($_GET['orgChartID']) && !empty($_GET['orgChartID'])) ? Utility::clean_string($_GET['orgChartID']) : null;

// Fetch organisation details
$orgFilter = array("suspended" => 'N');
if(isset($orgDataID) && !empty($orgDataID)) {
    $orgFilter['orgDataID'] = $orgDataID;
}
$OrganisationDetails = Admin::org_data($orgFilter, true, $DBConn);
$orgDetails = $OrganisationDetails;

// Validate organisation details
if (!$orgDetails || !is_object($orgDetails) || !isset($orgDetails->orgDataID)) {
    Alert::danger("Unable to load organisation details. Please ensure your organisation is properly configured.", true, array('fst-italic', 'text-center', 'font-18'));
    return;
}

// Fetch organisation entities
$organisationEntities = Data::entities_full(['orgDataID'=> $orgDetails->orgDataID, 'Suspended'=> 'N'], false, $DBConn);

// Fetch job titles for position assignments
$jobTitles = Admin::tija_job_titles(array('Suspended' => 'N'), false, $DBConn);

// Fetch all org charts for this organization
$orgCharts = Data::org_charts(['orgDataID' => $orgDetails->orgDataID, 'Suspended' => 'N'], false, $DBConn);
$totalCharts = $orgCharts ? count($orgCharts) : 0;

// Get total positions across all charts
$totalPositions = 0;
$totalEntitiesWithCharts = 0;
if($orgCharts) {
    foreach($orgCharts as $chart) {
        $positions = Data::org_chart_position_assignments(['orgChartID' => $chart->orgChartID, 'Suspended' => 'N'], false, $DBConn);
        $totalPositions += $positions ? count($positions) : 0;
    }
    // Count unique entities with charts
    $entitiesWithCharts = array_unique(array_column($orgCharts, 'entityID'));
    $totalEntitiesWithCharts = count($entitiesWithCharts);
}

?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-24 mb-2">
            <i class="fas fa-sitemap me-2"></i>Organization Charts
        </h1>
        <p class="text-muted mb-0"><?= htmlspecialchars($orgDetails->orgName) ?> - Reporting Hierarchy Visualization</p>
    </div>
    <div class="d-flex gap-2">
        <?php if($orgCharts): ?>
            <button class="btn btn-secondary btn-sm" onclick="refreshCharts()">
                <i class="ri-refresh-line me-1"></i>Refresh
            </button>
        <?php endif; ?>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manageOrgChartModal">
            <i class="ri-add-line me-1"></i>Create New Chart
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md bg-primary-transparent me-3">
                        <i class="fas fa-sitemap fs-20"></i>
                    </div>
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Total Charts</p>
                        <h4 class="mb-0"><?= $totalCharts ?></h4>
                        <small class="text-muted">Organization charts</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md bg-success-transparent me-3">
                        <i class="fas fa-layer-group fs-20"></i>
                    </div>
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Total Positions</p>
                        <h4 class="mb-0"><?= $totalPositions ?></h4>
                        <small class="text-muted">Across all charts</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md bg-info-transparent me-3">
                        <i class="fas fa-building fs-20"></i>
                    </div>
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Entities</p>
                        <h4 class="mb-0"><?= $totalEntitiesWithCharts ?>/<?= count($organisationEntities ?? []) ?></h4>
                        <small class="text-muted">With charts</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-md bg-warning-transparent me-3">
                        <i class="fas fa-user-tie fs-20"></i>
                    </div>
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Job Titles</p>
                        <h4 class="mb-0"><?= count($jobTitles ?? []) ?></h4>
                        <small class="text-muted">Available titles</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Scenario 1: Empty State - No Charts Exist
if(!$orgCharts || count($orgCharts) == 0) {
?>
    <!-- Empty State -->
        	<div class="row">
        <div class="col-12">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="text-center py-5">
                        <div class="avatar avatar-xl bg-primary-transparent mx-auto mb-4">
                            <i class="fas fa-sitemap fs-40"></i>
                        </div>
                        <h4 class="mb-3">No Organization Charts Found</h4>
                        <p class="text-muted mb-4 mx-auto" style="max-width: 600px;">
                            Create your first organizational chart to visualize your company's reporting structure.
                            Charts help you define positions, hierarchies, and reporting relationships across your organization.
                        </p>

                        <div class="row justify-content-center mb-4">
                            <div class="col-md-8">
                                <div class="alert alert-info text-start">
                                    <h6 class="alert-heading"><i class="fas fa-lightbulb me-2"></i>What can you do with Org Charts?</h6>
                                    <ul class="mb-0">
                                        <li>Visualize organizational hierarchies across multiple entities</li>
                                        <li>Define positions and reporting relationships</li>
                                        <li>Support for enterprise groups and subsidiaries</li>
                                        <li>Export charts for presentations and documentation</li>
                                        <li>Track historical organizational changes</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#manageOrgChartModal">
                            <i class="ri-add-circle-line me-2"></i>Create Your First Chart
                        </button>
            	</div>
          	</div>
        	</div>
      </div>
   </div>

   <?php
// Scenario 2: Charts Exist but None Selected - Show Chart List
} elseif(!$orgChartID) {
?>
    <!-- Charts Grid View -->
    <div class="row">
        <div class="col-12">
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="card-title mb-0">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Your Organization Charts</h5>
                    </div>
                    <div class="d-flex gap-2">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-primary active" id="viewGrid">
                                <i class="ri-grid-line"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="viewList">
                                <i class="ri-list-check"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        Select a chart below to view and manage its positions. You can create multiple charts for different time periods or organizational structures.
                    </div>

                    <!-- Grid View -->
                    <div id="chartsGridView" class="row">
			<?php
                        $colors = ['primary', 'success', 'info', 'warning', 'purple', 'teal', 'orange', 'pink'];
                        foreach($orgCharts as $index => $chart):
                            $color = $colors[$index % count($colors)];

                            // Get positions for this chart
                            $chartPositions = Data::org_chart_position_assignments(['orgChartID' => $chart->orgChartID, 'Suspended' => 'N'], false, $DBConn);
                            $positionCount = $chartPositions ? count($chartPositions) : 0;

                            // Get entity details
                            $chartEntity = null;
                            if($chart->entityID) {
                                $chartEntity = Data::entities_full(['entityID' => $chart->entityID], true, $DBConn);
                            }
                        ?>
                        <div class="col-xxl-4 col-xl-6 col-lg-6 col-md-12 mb-4">
                            <div class="card custom-card org-chart-card border-0 shadow-sm h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-start justify-content-between mb-3">
                                        <div class="flex-fill">
                                            <div class="d-flex align-items-center mb-3">
                                                <span class="avatar avatar-lg bg-<?= $color ?>-transparent text-<?= $color ?> me-3">
                                                    <i class="fas fa-sitemap fs-24"></i>
                                                </span>
                                                <div>
                                                    <h5 class="fw-semibold mb-1"><?= htmlspecialchars($chart->orgChartName) ?></h5>
                                                    <?php if($chartEntity): ?>
                                                        <span class="badge bg-<?= $color ?>-transparent">
                                                            <i class="ri-building-line me-1"></i><?= htmlspecialchars($chartEntity->entityName) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary-transparent">
                                                            <i class="ri-global-line me-1"></i>Organization-Wide
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="d-flex gap-3 mb-3">
                                                <div>
                                                    <small class="text-muted d-block">Positions</small>
                                                    <h6 class="mb-0"><?= $positionCount ?></h6>
                                                </div>
                                                <div class="border-end"></div>
                                                <div>
                                                    <small class="text-muted d-block">Created</small>
                                                    <h6 class="mb-0"><?= date('M Y', strtotime($chart->DateAdded)) ?></h6>
                                                </div>
                                            </div>

                                            <?php if(isset($chart->orgChartDescription) && !empty($chart->orgChartDescription)): ?>
                                                <p class="text-muted fs-12 mb-0">
                                                    <?= htmlspecialchars(substr($chart->orgChartDescription ?? '', 0, 100)) . (strlen($chart->orgChartDescription ?? '') > 100 ? '...' : '') ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2 mt-3">
                                        <a href="<?= $base ?>html/<?= $getString ?>&orgChartID=<?= $chart->orgChartID ?>"
                                           class="btn btn-<?= $color ?> btn-sm flex-fill">
                                            <i class="ri-eye-line me-1"></i>View Chart
                                        </a>
                                        <button class="btn btn-outline-<?= $color ?> btn-sm editOrgChart"
                                                data-bs-toggle="modal"
                                                data-bs-target="#manageOrgChartModal"
                                                data-chart-id="<?= $chart->orgChartID ?>"
                                                data-chart-name="<?= htmlspecialchars($chart->orgChartName) ?>"
                                                data-entity-id="<?= $chart->entityID ?? '' ?>"
                                                data-chart-description="<?= htmlspecialchars($chart->orgChartDescription ?? '') ?>">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                        <button class="btn btn-outline-secondary btn-sm" onclick="exportChart(<?= $chart->orgChartID ?>)">
                                            <i class="ri-download-line"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- List View (Hidden by default) -->
                    <div id="chartsListView" class="table-responsive" style="display: none;">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 30%;">Chart Name</th>
                                    <th style="width: 20%;">Entity</th>
                                    <th style="width: 10%;">Positions</th>
                                    <th style="width: 15%;">Created</th>
                                    <th style="width: 20%;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($orgCharts as $index => $chart):
                                    $chartPositions = Data::org_chart_position_assignments(['orgChartID' => $chart->orgChartID, 'Suspended' => 'N'], false, $DBConn);
                                    $positionCount = $chartPositions ? count($chartPositions) : 0;
                                    $chartEntity = null;
                                    if($chart->entityID) {
                                        $chartEntity = Data::entities_full(['entityID' => $chart->entityID], true, $DBConn);
                                    }
                                ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($chart->orgChartName) ?></strong>
                                        <?php if($chart->orgChartDescription): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars(substr($chart->orgChartDescription, 0, 60)) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($chartEntity): ?>
                                            <span class="badge bg-primary-transparent">
                                                <?= htmlspecialchars($chartEntity->entityName) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-transparent">Organization-Wide</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-transparent">
                                            <i class="ri-user-line me-1"></i><?= $positionCount ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($chart->DateAdded)) ?></td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="<?= $base ?>html/<?= $getString ?>&orgChartID=<?= $chart->orgChartID ?>"
                                               class="btn btn-sm btn-primary-light"
                                               title="View Chart">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            <button class="btn btn-sm btn-success-light editOrgChart"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#manageOrgChartModal"
                                                    data-chart-id="<?= $chart->orgChartID ?>"
                                                    data-chart-name="<?= htmlspecialchars($chart->orgChartName) ?>"
                                                    data-entity-id="<?= $chart->entityID ?? '' ?>"
                                                    data-chart-description="<?= htmlspecialchars($chart->orgChartDescription ?? '') ?>"
                                                    title="Edit Chart">
                                                <i class="ri-edit-line"></i>
                                            </button>
                                            <button class="btn btn-sm btn-secondary-light"
                                                    onclick="exportChart(<?= $chart->orgChartID ?>)"
                                                    title="Export">
                                                <i class="ri-download-line"></i>
						</button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
					</div>

					<?php
// Scenario 3: Specific Chart Selected - Show Chart Details and Positions
} else {
    $getString .= "&orgChartID={$orgChartID}";
    $orgChartDetails = Data::org_charts(array('orgChartID' => $orgChartID), true, $DBConn);

    if(!$orgChartDetails) {
        Alert::danger("Organization chart not found", true, array('text-center'));
        return;
    }

    // Get chart entity
    $chartEntity = null;
    if($orgChartDetails->entityID) {
        $chartEntity = Data::entities_full(['entityID' => $orgChartDetails->entityID], true, $DBConn);
    }

    // Get positions for this chart
    $positions = Data::org_chart_position_assignments(['orgChartID' => $orgChartID, 'Suspended' => 'N'], false, $DBConn);
    $positionCount = $positions ? count($positions) : 0;

    // Build positions hierarchy
						$positionsByParent = [];
						if($positions) {
							foreach ($positions as $position) {
            $parentId = $position->positionParentID ?: 0;
								if (!isset($positionsByParent[$parentId])) {
                $positionsByParent[$parentId] = [];
								}
								$positionsByParent[$parentId][] = $position;
							}
						}
?>

    <!-- Chart Details Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card custom-card border-primary">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center flex-grow-1">
                            <span class="avatar avatar-xl bg-primary-transparent text-primary me-3">
                                <i class="fas fa-sitemap fs-30"></i>
                            </span>
                            <div>
                                <h4 class="mb-1 fw-semibold"><?= htmlspecialchars($orgChartDetails->orgChartName) ?></h4>
                                <div class="d-flex gap-2 flex-wrap mb-2">
                                    <?php if($chartEntity): ?>
                                        <span class="badge bg-primary-transparent">
                                            <i class="ri-building-line me-1"></i><?= htmlspecialchars($chartEntity->entityName) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-transparent">
                                            <i class="ri-global-line me-1"></i>Organization-Wide
                                        </span>
                                    <?php endif; ?>
                                    <span class="badge bg-info-transparent">
                                        <i class="ri-user-line me-1"></i><?= $positionCount ?> Positions
                                    </span>
                                    <span class="badge bg-success-transparent">
                                        <i class="ri-calendar-line me-1"></i><?= date('M d, Y', strtotime($orgChartDetails->DateAdded)) ?>
                                    </span>
                                </div>
                                <?php if($orgChartDetails->orgChartDescription ?? false): ?>
                                    <p class="text-muted mb-0 fs-13"><?= htmlspecialchars($orgChartDetails->orgChartDescription) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="<?= $base ?>html/<?= str_replace("&orgChartID={$orgChartID}", "", $getString) ?>"
                               class="btn btn-sm btn-secondary">
                                <i class="ri-arrow-left-line me-1"></i>Back to Charts
                            </a>
                            <button class="btn btn-sm btn-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#manageOrgChartPosition">
                                <i class="ri-add-line me-1"></i>Add Position
                            </button>
                            <button class="btn btn-sm btn-success editOrgChart"
                                    data-bs-toggle="modal"
                                    data-bs-target="#manageOrgChartModal"
                                    data-chart-id="<?= $orgChartDetails->orgChartID ?>"
                                    data-chart-name="<?= htmlspecialchars($orgChartDetails->orgChartName) ?>"
                                    data-entity-id="<?= $orgChartDetails->entityID ?? '' ?>"
                                    data-chart-description="<?= htmlspecialchars($orgChartDetails->orgChartDescription ?? '') ?>">
                                <i class="ri-edit-line me-1"></i>Edit Chart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if($positions && count($positions) > 0): ?>
        <!-- Positions Hierarchy Display -->
        <div class="row">
            <div class="col-12">
                <div class="card custom-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="card-title mb-0">
                            <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>Organizational Positions</h5>
                        </div>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-primary active" onclick="toggleChartView('hierarchy')">
                                <i class="ri-node-tree me-1"></i>Hierarchy
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleChartView('visual')">
                                <i class="ri-organization-chart me-1"></i>Visual
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Hierarchy View -->
                        <div id="hierarchyView">
                            <?php
                            // Function to display positions hierarchically
                            function displayPositionsHierarchy($positionID, $positionsByParent, $DBConn, $orgChartDetails, $level = 0) {
							if (!isset($positionsByParent[$positionID])) {
								return;
							}

							foreach ($positionsByParent[$positionID] as $position) {
                                    $indent = $level * 30;

                                    // Get parent position details
                                    $positionParent = null;
                                    if($position->positionParentID && $position->positionParentID > 0) {
                                        $positionParent = Data::org_chart_position_assignments(['positionAssignmentID' => $position->positionParentID], true, $DBConn);
                                    }

                                    // Get job title details
                                    $jobTitle = null;
                                    if($position->positionID) {
                                        $jobTitle = Admin::tija_job_titles(['jobTitleID' => $position->positionID], true, $DBConn);
                                    }

                                    // Get entity if specific
                                    $posEntity = null;
                                    if($position->entityID) {
                                        $posEntity = Data::entities_full(['entityID' => $position->positionID], true, $DBConn);
                                    }

                                    $levelColor = $level == 0 ? 'primary' : ($level == 1 ? 'success' : ($level == 2 ? 'info' : 'secondary'));
                                    ?>
                                    <div class="position-item mb-2" style="margin-left: <?= $indent ?>px;">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body p-3">
                                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                                    <div class="d-flex align-items-center flex-grow-1">
                                                        <?php if($level > 0): ?>
                                                            <i class="fas fa-level-up-alt fa-rotate-90 me-3 text-muted"></i>
                                                        <?php endif; ?>
                                                        <div class="me-3">
                                                            <span class="avatar avatar-md bg-<?= $levelColor ?>-transparent text-<?= $levelColor ?>">
                                                                <i class="fas fa-user-tie"></i>
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1 fw-semibold"><?= htmlspecialchars($position->positionTitle) ?></h6>
                                                            <div class="d-flex gap-2 flex-wrap">
                                                                <span class="badge bg-<?= $levelColor ?>-transparent">
                                                                    <i class="ri-shield-star-line me-1"></i>Level <?= $level ?>
                                                                </span>
                                                                <?php if($positionParent): ?>
                                                                    <span class="badge bg-info-transparent">
                                                                        <i class="ri-arrow-up-line me-1"></i>Reports to: <?= htmlspecialchars($positionParent->positionTitle) ?>
                                                                    </span>
                                                                <?php endif; ?>
                                                                <?php if($jobTitle): ?>
                                                                    <span class="badge bg-success-transparent">
                                                                        <i class="ri-briefcase-line me-1"></i><?= htmlspecialchars($jobTitle->jobTitle) ?>
                                                                    </span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="d-flex gap-2">
                                                        <button class="btn btn-sm btn-primary-light editPosition"
															data-bs-toggle="modal"
															data-bs-target="#manageOrgChartPosition"
															data-org-chart-id="<?= $orgChartDetails->orgChartID ?>"
                                                                data-position-assignment-id="<?= $position->positionAssignmentID ?>"
															data-position-id="<?= $position->positionID ?>"
															data-position-parent-id="<?= $position->positionParentID ?>"
                                                                data-entity-id="<?= $position->entityID ?? '' ?>"
                                                                data-position-title="<?= htmlspecialchars($position->positionTitle) ?>"
                                                                title="Edit Position">
                                                            <i class="ri-edit-line"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger-light deletePosition"
                                                                data-position-assignment-id="<?= $position->positionAssignmentID ?>"
                                                                data-position-title="<?= htmlspecialchars($position->positionTitle) ?>"
                                                                title="Delete Position">
                                                            <i class="ri-delete-bin-line"></i>
                                                        </button>
                                                    </div>
													</div>
												</div>
										</div>
									</div>
									<?php
									// Recursively display child positions
                                    displayPositionsHierarchy($position->positionAssignmentID, $positionsByParent, $DBConn, $orgChartDetails, $level + 1);
							}
						}

                            // Display positions starting from top-level (no parent)
                            displayPositionsHierarchy(0, $positionsByParent, $DBConn, $orgChartDetails);
					?>
				</div>

                        <!-- Visual View (Canvas) -->
                        <div id="visualView" style="display: none;">
                            <div id="orgChartCanvas" class="border rounded p-4 bg-light" style="min-height: 500px; overflow-x: auto;">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary mb-3" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="text-muted">Rendering organizational chart...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- Empty State: No Positions -->
        <div class="row">
            <div class="col-12">
                <div class="card custom-card">
					<div class="card-body">
                        <div class="text-center py-5">
                            <div class="avatar avatar-xl bg-warning-transparent mx-auto mb-3">
                                <i class="fas fa-user-plus fs-40"></i>
						</div>
                            <h5 class="mb-2">No Positions Defined</h5>
                            <p class="text-muted mb-4">Start building your organizational chart by adding positions.</p>
                            <button class="btn btn-primary"
													data-bs-toggle="modal"
                                    data-bs-target="#manageOrgChartPosition">
                                <i class="ri-add-circle-line me-2"></i>Add First Position
                            </button>
                        </div>
                    </div>
											</div>
										</div>
									</div>
    <?php endif; ?>

<?php } ?>

<!-- Manage Org Chart Modal -->
									<?php
echo Utility::form_modal_header("manageOrgChartModal", "global/admin/organisation/manage_org_chart.php", "Manage Organization Chart", array("modal-lg", "modal-dialog-centered"), $base);
?>
    <div class="row g-3">
        <input type="hidden" name="orgChartID" id="orgChartID">
        <input type="hidden" name="orgDataID" value="<?= $orgDetails->orgDataID ?>">

        <div class="col-md-12">
            <label for="orgChartName" class="form-label">
                Chart Name <span class="text-danger">*</span>
            </label>
            <input type="text"
                   id="orgChartName"
                   name="orgChartName"
                   class="form-control form-control-sm"
                   placeholder="e.g., 2025 Organizational Structure"
                   required>
        </div>

        <div class="col-md-12">
            <label for="chartEntityID" class="form-label">Entity Scope</label>
            <select id="chartEntityID" name="entityID" class="form-select form-control-sm">
                <option value="">Organization-Wide (All Entities)</option>
                <?php if($organisationEntities):
                    foreach($organisationEntities as $entity): ?>
                        <option value="<?= $entity->entityID ?>">
                            <?= htmlspecialchars($entity->entityName) ?>
                        </option>
                    <?php endforeach;
                endif; ?>
            </select>
            <small class="text-muted">Choose if this chart is for a specific entity or the entire organization</small>
        </div>

        <div class="col-md-12">
            <label for="orgChartDescription" class="form-label">Description</label>
            <textarea id="orgChartDescription"
                      name="orgChartDescription"
                      class="form-control form-control-sm"
                      rows="3"
                      placeholder="Brief description of this organizational chart"></textarea>
        </div>

        <div class="col-md-6">
            <label for="chartEffectiveDate" class="form-label">Effective Date</label>
            <input type="date"
                   id="chartEffectiveDate"
                   name="effectiveDate"
                   class="form-control form-control-sm"
                   value="<?= date('Y-m-d') ?>">
        </div>

        <div class="col-md-6">
            <div class="form-check mt-4">
                <input class="form-check-input"
                       type="checkbox"
                       id="isCurrentChart"
                       name="isCurrent"
                       value="Y"
                       checked>
                <label class="form-check-label" for="isCurrentChart">
                    <strong>Set as Current Chart</strong>
                    <small class="text-muted d-block">Mark this as the active organizational chart</small>
                </label>
						</div>
					</div>
				</div>
				<?php
echo Utility::form_modal_footer("Save Organization Chart", "submitChart", "btn btn-success btn-sm");

// Manage Position Modal
if($orgChartID) {
    echo Utility::form_modal_header("manageOrgChartPosition", "global/admin/organisation/manage_org_chart_position.php", "Manage Position", array("modal-lg", "modal-dialog-centered"), $base);
    include "includes/core/admin/organisation/modals/manage_org_chart_positions.php";
    echo Utility::form_modal_footer("Save Position", "submitPosition", "btn btn-success btn-sm");
}
?>

<!-- JavaScript for Organization Charts -->
  <script>
// Chart data from PHP
const orgChartsData = <?php echo json_encode($orgCharts ?? []); ?>;
const jobTitlesData = <?php echo json_encode($jobTitles ?? []); ?>;
const currentOrgChartID = <?php echo json_encode($orgChartID ?? null); ?>;

    document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    initializeTooltips();

    // View toggle buttons
    setupViewToggles();

    // Edit org chart handlers
    setupEditChartHandlers();

    // Edit position handlers
    setupEditPositionHandlers();

    // Delete position handlers
    setupDeletePositionHandlers();

    // Render visual chart if on chart details page
    if(currentOrgChartID) {
        setTimeout(() => {
            renderVisualChart(currentOrgChartID);
        }, 500);
    }
});

// Initialize tooltips
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Setup view toggle buttons
function setupViewToggles() {
    const viewGrid = document.getElementById('viewGrid');
    const viewList = document.getElementById('viewList');
    const gridView = document.getElementById('chartsGridView');
    const listView = document.getElementById('chartsListView');

    if(viewGrid && viewList && gridView && listView) {
        viewGrid.addEventListener('click', function() {
            gridView.style.display = 'block';
            listView.style.display = 'none';
            viewGrid.classList.add('active');
            viewList.classList.remove('active');
        });

        viewList.addEventListener('click', function() {
            gridView.style.display = 'none';
            listView.style.display = 'block';
            viewList.classList.add('active');
            viewGrid.classList.remove('active');
        });
    }
}

// Toggle between hierarchy and visual chart views
function toggleChartView(viewType) {
    const hierarchyView = document.getElementById('hierarchyView');
    const visualView = document.getElementById('visualView');

    if(viewType === 'hierarchy') {
        hierarchyView.style.display = 'block';
        visualView.style.display = 'none';
    } else {
        hierarchyView.style.display = 'none';
        visualView.style.display = 'block';
        renderVisualChart(currentOrgChartID);
    }
}

// Setup edit chart button handlers
function setupEditChartHandlers() {
    document.querySelectorAll('.editOrgChart').forEach(button => {
        button.addEventListener('click', function() {
            const chartId = this.getAttribute('data-chart-id');
            const chartName = this.getAttribute('data-chart-name');
            const entityId = this.getAttribute('data-entity-id');
            const chartDescription = this.getAttribute('data-chart-description');

            // Populate form
            document.getElementById('orgChartID').value = chartId || '';
            document.getElementById('orgChartName').value = chartName || '';
            document.getElementById('chartEntityID').value = entityId || '';
            document.getElementById('orgChartDescription').value = chartDescription || '';

            // Update modal title
            const modalTitle = document.querySelector('#manageOrgChartModal .modal-title');
            if(modalTitle) {
                modalTitle.innerHTML = chartId ?
                    '<i class="fas fa-edit me-2"></i>Edit Organization Chart' :
                    '<i class="fas fa-plus me-2"></i>Create Organization Chart';
            }
        });
    });
}

// Setup edit position button handlers
function setupEditPositionHandlers() {
		document.querySelectorAll('.editPosition').forEach(button => {
		  button.addEventListener('click', function() {
            const form = document.querySelector('#manageOrgChartPosition form');
			if(!form) {
                console.error('Position form not found');
				return;
			}

            const data = this.dataset;

            // Populate form with position data
            if(form.querySelector('input[name="positionAssignmentID"]')) {
                form.querySelector('input[name="positionAssignmentID"]').value = data.positionAssignmentId || '';
            }
            if(form.querySelector('input[name="orgChartID"]')) {
                form.querySelector('input[name="orgChartID"]').value = data.orgChartId || '';
            }
            if(form.querySelector('select[name="positionID"]')) {
                form.querySelector('select[name="positionID"]').value = data.positionId || '';
            }
            if(form.querySelector('select[name="positionParentID"]')) {
                form.querySelector('select[name="positionParentID"]').value = data.positionParentId || '0';
            }
            if(form.querySelector('select[name="entityID"]')) {
                form.querySelector('select[name="entityID"]').value = data.entityId || '';
            }
        });
    });
}

// Setup delete position handlers
function setupDeletePositionHandlers() {
    document.querySelectorAll('.deletePosition').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            const positionAssignmentId = this.getAttribute('data-position-assignment-id');
            const positionTitle = this.getAttribute('data-position-title');

            if(confirm(`Are you sure you want to delete the position "${positionTitle}"?\n\nThis action cannot be undone.`)) {
                // Create and submit form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = siteUrl + 'php/scripts/global/admin/organisation/delete_org_chart_position.php';

                const positionInput = document.createElement('input');
                positionInput.type = 'hidden';
                positionInput.name = 'positionAssignmentID';
                positionInput.value = positionAssignmentId;
                form.appendChild(positionInput);

                const suspendedInput = document.createElement('input');
                suspendedInput.type = 'hidden';
                suspendedInput.name = 'Suspended';
                suspendedInput.value = 'Y';
                form.appendChild(suspendedInput);

                document.body.appendChild(form);
                form.submit();
            }
        });
    });
}

// Render visual organizational chart
function renderVisualChart(orgChartID) {
    const canvas = document.getElementById('orgChartCanvas');
    if(!canvas) return;

    canvas.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted">Loading organizational chart...</p>
        </div>
    `;

    // Fetch chart positions
    fetch(siteUrl + 'php/scripts/global/admin/get_org_chart.php?orgChartID=' + orgChartID)
        .then(response => response.json())
        .then(data => {
            if(data.success && data.positions) {
                renderChartVisual(data.positions);
            } else {
                canvas.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${data.message || 'No positions found in this chart'}
                    </div>
                `;
            }
        })
        .catch(error => {
            canvas.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Error loading chart: ${error.message}
                </div>
            `;
            console.error('Error loading chart:', error);
        });
}

// Render chart visual representation
function renderChartVisual(positions) {
    if(!positions || positions.length === 0) {
        document.getElementById('orgChartCanvas').innerHTML = `
            <div class="text-center py-4">
                <p class="text-muted">No positions to display</p>
            </div>
        `;
        return;
    }

    // Build hierarchy
    const hierarchy = buildChartHierarchy(positions);

    // Render
    let html = '<div class="org-chart-visual-container">';
    html += renderChartLevel(hierarchy, 0);
    html += '</div>';

    document.getElementById('orgChartCanvas').innerHTML = html;
}

// Build chart hierarchy from positions
function buildChartHierarchy(positions) {
    const positionMap = {};
    const roots = [];

    // Create position map
    positions.forEach(pos => {
        positionMap[pos.positionAssignmentID] = {...pos, children: []};
    });

    // Build tree
    positions.forEach(pos => {
        if (pos.positionParentID && positionMap[pos.positionParentID]) {
            positionMap[pos.positionParentID].children.push(positionMap[pos.positionAssignmentID]);
        } else {
            roots.push(positionMap[pos.positionAssignmentID]);
        }
    });

    return roots;
}

// Render chart level
function renderChartLevel(positions, level) {
    let html = '';
    const levelColors = ['danger', 'warning', 'success', 'info', 'primary', 'secondary'];
    const color = levelColors[level % levelColors.length];

    if(level > 0) {
        html += '<div class="hierarchy-connector"></div>';
    }

    html += '<div class="chart-level mb-4">';
    html += `<div class="level-indicator mb-2 text-center"><span class="badge bg-${color}-transparent">Level ${level}</span></div>`;
    html += '<div class="d-flex flex-wrap gap-3 justify-content-center">';

    positions.forEach(pos => {
        const childCount = pos.children ? pos.children.length : 0;

        html += `
            <div class="position-card-visual">
                <div class="card border-${color}" style="min-width: 220px; max-width: 250px;">
                    <div class="card-body p-3 text-center">
                        <div class="avatar avatar-md bg-${color}-transparent mx-auto mb-2">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h6 class="mb-1 fw-semibold">${escapeHtml(pos.positionTitle)}</h6>
                        ${pos.jobTitle ? `<small class="text-muted d-block mb-2">${escapeHtml(pos.jobTitle)}</small>` : ''}
                        ${childCount > 0 ? `<span class="badge bg-${color}-transparent"><i class="ri-team-line me-1"></i>${childCount} Reports</span>` : ''}
                    </div>
                </div>
            </div>
        `;
    });

    html += '</div>';

    // Render children
    positions.forEach(pos => {
        if(pos.children && pos.children.length > 0) {
            html += renderChartLevel(pos.children, level + 1);
        }
    });

    html += '</div>';
    return html;
}

// HTML escape utility
function escapeHtml(text) {
    if(!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Export chart function
function exportChart(orgChartID) {
    window.open(siteUrl + 'php/scripts/global/admin/export_org_chart.php?orgChartID=' + orgChartID, '_blank');
}

// Refresh charts
function refreshCharts() {
    window.location.reload();
}
	 </script>

<!-- Styles for Organization Charts -->
<style>
.org-chart-card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.org-chart-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
}

.position-item .card {
    transition: all 0.2s ease;
}

.position-item .card:hover {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1) !important;
}

/* Visual Chart Styles */
.org-chart-visual-container {
    padding: 20px;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 8px;
    min-height: 400px;
}

.chart-level {
    position: relative;
}

.level-indicator {
    font-weight: 600;
    margin-bottom: 15px;
}

.hierarchy-connector {
    width: 2px;
    height: 30px;
    background: linear-gradient(to bottom, #dee2e6, transparent);
    margin: 0 auto;
}

.position-card-visual {
    position: relative;
}

.position-card-visual .card {
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.position-card-visual .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.15) !important;
}

/* Grid/List view toggle */
.btn-group .btn.active {
    background-color: var(--primary-color);
    color: white;
}
</style>
