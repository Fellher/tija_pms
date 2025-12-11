<?php
/**
 * Sales Stage Analysis Widgets
 * Comprehensive analysis widgets for different sales stages
 */

// Get sales data
$sales = Sales::sales_case_full([], false, $DBConn);

// Initialize stage data arrays
$stageData = [
    'business_development' => ['count' => 0, 'value' => 0, 'probability' => 0, 'cases' => []],
    'opportunities' => ['count' => 0, 'value' => 0, 'probability' => 0, 'cases' => []],
    'won' => ['count' => 0, 'value' => 0, 'probability' => 0, 'cases' => []],
    'lost' => ['count' => 0, 'value' => 0, 'probability' => 0, 'cases' => []]
];

// Process sales data by stage
if ($sales) {
    foreach ($sales as $sale) {
        $stage = $sale->saleStage;
        if (isset($stageData[$stage])) {
            $stageData[$stage]['count']++;
            $stageData[$stage]['value'] += $sale->salesCaseEstimate ?: 0;
            $stageData[$stage]['probability'] += $sale->probability ?: 0;
            $stageData[$stage]['cases'][] = $sale;
        }
    }
}

// Calculate averages
foreach ($stageData as $stage => &$data) {
    if ($data['count'] > 0) {
        $data['avgProbability'] = round($data['probability'] / $data['count'], 1);
        $data['avgValue'] = round($data['value'] / $data['count'], 2);
    } else {
        $data['avgProbability'] = 0;
        $data['avgValue'] = 0;
    }
}

// Calculate total pipeline value
$totalPipelineValue = array_sum(array_column($stageData, 'value'));
$totalCases = array_sum(array_column($stageData, 'count'));

// Calculate conversion rates
$conversionRates = [];
if ($stageData['business_development']['count'] > 0) {
    $conversionRates['bd_to_opportunities'] = round(($stageData['opportunities']['count'] / $stageData['business_development']['count']) * 100, 1);
}
if ($stageData['opportunities']['count'] > 0) {
    $conversionRates['opportunities_to_won'] = round(($stageData['won']['count'] / $stageData['opportunities']['count']) * 100, 1);
}
?>

<!-- Sales Stage Analysis Widgets Container -->
<div class="container-fluid">
    <div class="row">

        <!-- Pipeline Overview Widget -->
        <div class="col-xl-8 col-lg-7 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-bar-chart-line me-2"></i>Sales Pipeline Overview
                        <button class="btn btn-sm btn-link text-muted p-0 ms-1" data-bs-toggle="tooltip" title="Value and volume by stage, with a doughnut showing distribution.">
                            <i class="ri-question-line"></i>
                        </button>
                    </h5>
                    <div class="small text-muted">Shows total pipeline, total cases, and stage mix to spot where deals are concentrated.</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Pipeline Value -->
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-primary-subtle rounded">
                                        <span class="avatar-title bg-primary-subtle text-primary rounded">
                                            <i class="ri-money-dollar-circle-line fs-20"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Total Pipeline Value</h6>
                                    <h4 class="mb-0">KES <?= number_format($totalPipelineValue, 2) ?></h4>
                                </div>
                            </div>
                        </div>

                        <!-- Total Cases -->
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-success-subtle rounded">
                                        <span class="avatar-title bg-success-subtle text-success rounded">
                                            <i class="ri-file-list-3-line fs-20"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Total Cases</h6>
                                    <h4 class="mb-0"><?= $totalCases ?></h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stage Breakdown Chart -->
                    <div class="mt-4">
                        <canvas id="salesStageChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conversion Rates Widget -->
        <div class="col-xl-4 col-lg-5 col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-exchange-line me-2"></i>Conversion Rates
                        <button class="btn btn-sm btn-link text-muted p-0 ms-1" data-bs-toggle="tooltip" title="Stage-to-stage conversion percentages (BD→Opportunities, Opportunities→Won).">
                            <i class="ri-question-line"></i>
                        </button>
                    </h5>
                    <div class="small text-muted">Track funnel efficiency and where handoffs stall.</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">BD → Opportunities</span>
                                <span class="fw-semibold"><?= $conversionRates['bd_to_opportunities'] ?? 0 ?>%</span>
                            </div>
                            <div class="progress progress-sm mt-2">
                                <div class="progress-bar bg-info" style="width: <?= $conversionRates['bd_to_opportunities'] ?? 0 ?>%"></div>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Opportunities → Won</span>
                                <span class="fw-semibold"><?= $conversionRates['opportunities_to_won'] ?? 0 ?>%</span>
                            </div>
                            <div class="progress progress-sm mt-2">
                                <div class="progress-bar bg-success" style="width: <?= $conversionRates['opportunities_to_won'] ?? 0 ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <!-- Top Pipeline Cases Row -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                            <i class="ri-star-line me-2"></i>Top Pipeline Cases - Highest Value & Probability
                            <button class="btn btn-sm btn-link text-muted p-0 ms-1" data-bs-toggle="tooltip" title="Ranks active deals by weighted score (value × probability) to focus on high-impact opportunities.">
                                <i class="ri-question-line"></i>
                            </button>
                    </h5>
                        <div class="small text-muted">Use this list to prioritize follow-ups on the most material, likely-to-close deals.</div>
                </div>
                <div class="card-body">
                    <?php
                    // Get all active pipeline cases (excluding lost and closed stages)
                    $activePipelineCases = array_filter($sales, function($sale) {
                        return !in_array($sale->saleStage, ['lost', 'closed_lost', 'closed_won']) &&
                               $sale->salesCaseEstimate > 0 &&
                               $sale->probability > 0;
                    });

                    // Sort by value * probability (weighted score) to get top performers
                    usort($activePipelineCases, function($a, $b) {
                        $scoreA = $a->salesCaseEstimate * ($a->probability / 100);
                        $scoreB = $b->salesCaseEstimate * ($b->probability / 100);
                        return $scoreB <=> $scoreA; // Descending order
                    });

                    // Get top 4 cases
                    $topPipelineCases = array_slice($activePipelineCases, 0, 4);
                    ?>

                    <div class="row">
                        <?php foreach ($topPipelineCases as $index => $case):
                            $weightedScore = $case->salesCaseEstimate * ($case->probability / 100);
                            $stageColor = '';
                            $stageIcon = '';

                            switch($case->saleStage) {
                                case 'business_development':
                                    $stageColor = 'info';
                                    $stageIcon = 'ri-lightbulb-line';
                                    break;
                                case 'opportunities':
                                    $stageColor = 'warning';
                                    $stageIcon = 'ri-target-line';
                                    break;
                                case 'won':
                                    $stageColor = 'success';
                                    $stageIcon = 'ri-trophy-line';
                                    break;
                                default:
                                    $stageColor = 'primary';
                                    $stageIcon = 'ri-briefcase-line';
                            }
                        ?>
                        <div class="col-xl-3 col-lg-6 col-md-12 mb-3">
                            <div class="card border-<?= $stageColor ?>">
                                <div class="card-header bg-<?= $stageColor ?>-subtle">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 text-<?= $stageColor ?>">
                                            <i class="<?= $stageIcon ?> me-2"></i>
                                            <?= ucfirst(str_replace('_', ' ', $case->saleStage)) ?>
                                        </h6>
                                        <span class="badge bg-<?= $stageColor ?>-subtle text-<?= $stageColor ?>">
                                            #<?= $index + 1 ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <h5 class="card-title mb-1"><?= htmlspecialchars($case->salesCaseName) ?></h5>
                                        <p class="text-muted mb-2"><?= htmlspecialchars($case->clientName) ?></p>
                                    </div>

                                    <div class="row text-center mb-3">
                                        <div class="col-6">
                                            <div class="border-end">
                                                <h4 class="mb-1 text-<?= $stageColor ?>">KES <?= number_format($case->salesCaseEstimate, 0) ?></h4>
                                                <small class="text-muted">Value</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <h4 class="mb-1 text-<?= $stageColor ?>"><?= $case->probability ?>%</h4>
                                            <small class="text-muted">Probability</small>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="text-muted">Weighted Score</small>
                                            <small class="fw-semibold">KES <?= number_format($weightedScore, 0) ?></small>
                                        </div>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar bg-<?= $stageColor ?>" style="width: <?= min(($weightedScore / max(array_column($topPipelineCases, 'salesCaseEstimate'))) * 100, 100) ?>%"></div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12 mb-2">
                                            <small class="text-muted d-block">Sales Person</small>
                                            <span class="fw-semibold"><?= htmlspecialchars($case->salesPersonName) ?></span>
                                        </div>
                                        <div class="col-12 mb-2">
                                            <small class="text-muted d-block">Expected Close</small>
                                            <span class="fw-semibold">
                                                <?= $case->expectedCloseDate && $case->expectedCloseDate != '0000-00-00' ? $case->expectedCloseDate : 'Not Set' ?>
                                            </span>
                                        </div>
                                        <?php if ($case->businessUnitName): ?>
                                        <div class="col-12">
                                            <small class="text-muted d-block">Business Unit</small>
                                            <span class="fw-semibold"><?= htmlspecialchars($case->businessUnitName) ?></span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (count($topPipelineCases) == 0): ?>
                    <div class="text-center py-4">
                        <i class="ri-inbox-line fs-48 text-muted"></i>
                        <h5 class="text-muted mt-3">No Active Pipeline Cases</h5>
                        <p class="text-muted">There are currently no active sales cases with values and probabilities.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Stage Analysis Row -->
    <div class="row mt-4">

        <!-- Business Development Stage -->
        <div class="col-xl-6 col-lg-6 col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-info-subtle">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-info">
                            <i class="ri-lightbulb-line me-2"></i>Business Development
                        </h5>
                        <button class="btn btn-sm btn-outline-info" type="button" data-bs-toggle="collapse" data-bs-target="#bdDetails" aria-expanded="false">
                            <i class="ri-eye-line"></i> View Details
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h4 class="mb-1 text-info"><?= $stageData['business_development']['count'] ?></h4>
                            <p class="text-muted mb-0">Cases</p>
                        </div>
                        <div class="col-4">
                            <h4 class="mb-1 text-info">KES <?= number_format($stageData['business_development']['value'], 0) ?></h4>
                            <p class="text-muted mb-0">Value</p>
                        </div>
                        <div class="col-4">
                            <h4 class="mb-1 text-info"><?= $stageData['business_development']['avgProbability'] ?>%</h4>
                            <p class="text-muted mb-0">Avg Prob</p>
                        </div>
                    </div>

                    <!-- Recent Cases -->
                    <div class="mt-3">
                        <h6 class="mb-2">Recent Cases</h6>
                        <div class="list-group list-group-flush">
                            <?php
                            $recentBDCases = array_slice($stageData['business_development']['cases'], -3);
                            foreach ($recentBDCases as $case): ?>
                            <div class="list-group-item px-0 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($case->salesCaseName) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($case->clientName) ?></small>
                                    </div>
                                    <span class="badge bg-info-subtle text-info"><?= $case->probability ?>%</span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Collapsible Details Table -->
                    <div class="collapse mt-3" id="bdDetails">
                        <div class="card card-body bg-light">
                            <h6 class="mb-3">All Business Development Cases</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Case Name</th>
                                            <th>Client</th>
                                            <th>Sales Person</th>
                                            <th>Value</th>
                                            <th>Probability</th>
                                            <th>Expected Close</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($stageData['business_development']['cases'] as $case): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($case->salesCaseName) ?></td>
                                            <td><?= htmlspecialchars($case->clientName) ?></td>
                                            <td><?= htmlspecialchars($case->salesPersonName) ?></td>
                                            <td>KES <?= number_format($case->salesCaseEstimate, 0) ?></td>
                                            <td>
                                                <span class="badge bg-info-subtle text-info"><?= $case->probability ?>%</span>
                                            </td>
                                            <td><?= $case->expectedCloseDate && $case->expectedCloseDate != '0000-00-00' ? $case->expectedCloseDate : '-' ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Opportunities Stage -->
        <div class="col-xl-6 col-lg-6 col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-warning-subtle">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-warning">
                            <i class="ri-target-line me-2"></i>Opportunities
                        </h5>
                        <button class="btn btn-sm btn-outline-warning" type="button" data-bs-toggle="collapse" data-bs-target="#oppDetails" aria-expanded="false">
                            <i class="ri-eye-line"></i> View Details
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h4 class="mb-1 text-warning"><?= $stageData['opportunities']['count'] ?></h4>
                            <p class="text-muted mb-0">Cases</p>
                        </div>
                        <div class="col-4">
                            <h4 class="mb-1 text-warning">KES <?= number_format($stageData['opportunities']['value'], 0) ?></h4>
                            <p class="text-muted mb-0">Value</p>
                        </div>
                        <div class="col-4">
                            <h4 class="mb-1 text-warning"><?= $stageData['opportunities']['avgProbability'] ?>%</h4>
                            <p class="text-muted mb-0">Avg Prob</p>
                        </div>
                    </div>

                    <!-- Recent Cases -->
                    <div class="mt-3">
                        <h6 class="mb-2">Recent Cases</h6>
                        <div class="list-group list-group-flush">
                            <?php
                            $recentOppCases = array_slice($stageData['opportunities']['cases'], -3);
                            foreach ($recentOppCases as $case): ?>
                            <div class="list-group-item px-0 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($case->salesCaseName) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($case->clientName) ?></small>
                                    </div>
                                    <span class="badge bg-warning-subtle text-warning"><?= $case->probability ?>%</span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Collapsible Details Table -->
                    <div class="collapse mt-3" id="oppDetails">
                        <div class="card card-body bg-light">
                            <h6 class="mb-3">All Opportunities Cases</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Case Name</th>
                                            <th>Client</th>
                                            <th>Sales Person</th>
                                            <th>Value</th>
                                            <th>Probability</th>
                                            <th>Expected Close</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($stageData['opportunities']['cases'] as $case): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($case->salesCaseName) ?></td>
                                            <td><?= htmlspecialchars($case->clientName) ?></td>
                                            <td><?= htmlspecialchars($case->salesPersonName) ?></td>
                                            <td>KES <?= number_format($case->salesCaseEstimate, 0) ?></td>
                                            <td>
                                                <span class="badge bg-warning-subtle text-warning"><?= $case->probability ?>%</span>
                                            </td>
                                            <td><?= $case->expectedCloseDate && $case->expectedCloseDate != '0000-00-00' ? $case->expectedCloseDate : '-' ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Won Stage -->
        <div class="col-xl-6 col-lg-6 col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-success-subtle">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-success">
                            <i class="ri-trophy-line me-2"></i>Won Sales
                        </h5>
                        <button class="btn btn-sm btn-outline-success" type="button" data-bs-toggle="collapse" data-bs-target="#wonDetails" aria-expanded="false">
                            <i class="ri-eye-line"></i> View Details
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h4 class="mb-1 text-success"><?= $stageData['won']['count'] ?></h4>
                            <p class="text-muted mb-0">Cases</p>
                        </div>
                        <div class="col-4">
                            <h4 class="mb-1 text-success">KES <?= number_format($stageData['won']['value'], 0) ?></h4>
                            <p class="text-muted mb-0">Value</p>
                        </div>
                        <div class="col-4">
                            <h4 class="mb-1 text-success"><?= $stageData['won']['avgProbability'] ?>%</h4>
                            <p class="text-muted mb-0">Avg Prob</p>
                        </div>
                    </div>

                    <!-- Recent Cases -->
                    <div class="mt-3">
                        <h6 class="mb-2">Recent Cases</h6>
                        <div class="list-group list-group-flush">
                            <?php
                            $recentWonCases = array_slice($stageData['won']['cases'], -3);
                            foreach ($recentWonCases as $case): ?>
                            <div class="list-group-item px-0 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($case->salesCaseName) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($case->clientName) ?></small>
                                    </div>
                                    <span class="badge bg-success-subtle text-success">KES <?= number_format($case->salesCaseEstimate, 0) ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Collapsible Details Table -->
                    <div class="collapse mt-3" id="wonDetails">
                        <div class="card card-body bg-light">
                            <h6 class="mb-3">All Won Sales Cases</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Case Name</th>
                                            <th>Client</th>
                                            <th>Sales Person</th>
                                            <th>Value</th>
                                            <th>Probability</th>
                                            <th>Date Closed</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($stageData['won']['cases'] as $case): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($case->salesCaseName) ?></td>
                                            <td><?= htmlspecialchars($case->clientName) ?></td>
                                            <td><?= htmlspecialchars($case->salesPersonName) ?></td>
                                            <td>KES <?= number_format($case->salesCaseEstimate, 0) ?></td>
                                            <td>
                                                <span class="badge bg-success-subtle text-success"><?= $case->probability ?>%</span>
                                            </td>
                                            <td><?= $case->dateClosed && $case->dateClosed != '0000-00-00' ? $case->dateClosed : '-' ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lost Stage -->
        <div class="col-xl-6 col-lg-6 col-md-12 mb-4">
            <div class="card">
                <div class="card-header bg-danger-subtle">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-danger">
                            <i class="ri-close-circle-line me-2"></i>Lost Sales
                        </h5>
                        <button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="collapse" data-bs-target="#lostDetails" aria-expanded="false">
                            <i class="ri-eye-line"></i> View Details
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <h4 class="mb-1 text-danger"><?= $stageData['lost']['count'] ?></h4>
                            <p class="text-muted mb-0">Cases</p>
                        </div>
                        <div class="col-4">
                            <h4 class="mb-1 text-danger">KES <?= number_format($stageData['lost']['value'], 0) ?></h4>
                            <p class="text-muted mb-0">Value</p>
                        </div>
                        <div class="col-4">
                            <h4 class="mb-1 text-danger"><?= $stageData['lost']['avgProbability'] ?>%</h4>
                            <p class="text-muted mb-0">Avg Prob</p>
                        </div>
                    </div>

                    <!-- Recent Cases -->
                    <div class="mt-3">
                        <h6 class="mb-2">Recent Cases</h6>
                        <div class="list-group list-group-flush">
                            <?php
                            $recentLostCases = array_slice($stageData['lost']['cases'], -3);
                            foreach ($recentLostCases as $case): ?>
                            <div class="list-group-item px-0 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($case->salesCaseName) ?></h6>
                                        <small class="text-muted"><?= htmlspecialchars($case->clientName) ?></small>
                                    </div>
                                    <span class="badge bg-danger-subtle text-danger">Lost</span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Collapsible Details Table -->
                    <div class="collapse mt-3" id="lostDetails">
                        <div class="card card-body bg-light">
                            <h6 class="mb-3">All Lost Sales Cases</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Case Name</th>
                                            <th>Client</th>
                                            <th>Sales Person</th>
                                            <th>Value</th>
                                            <th>Probability</th>
                                            <th>Date Closed</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($stageData['lost']['cases'] as $case): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($case->salesCaseName) ?></td>
                                            <td><?= htmlspecialchars($case->clientName) ?></td>
                                            <td><?= htmlspecialchars($case->salesPersonName) ?></td>
                                            <td>KES <?= number_format($case->salesCaseEstimate, 0) ?></td>
                                            <td>
                                                <span class="badge bg-danger-subtle text-danger"><?= $case->probability ?>%</span>
                                            </td>
                                            <td><?= $case->dateClosed && $case->dateClosed != '0000-00-00' ? $case->dateClosed : '-' ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Performance by Sales Person -->
        <div class="col-xl-12 col-lg-12 col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-user-line me-2"></i>Sales Performance by Sales Person
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Sales Person</th>
                                    <th>Total Cases</th>
                                    <th>Pipeline Value</th>
                                    <th>Won Value</th>
                                    <th>Win Rate</th>
                                    <th>Avg Probability</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Group sales by sales person
                                $salesPersonData = [];
                                foreach ($sales as $sale) {
                                    $personName = $sale->salesPersonName ?: 'Unassigned';
                                    if (!isset($salesPersonData[$personName])) {
                                        $salesPersonData[$personName] = [
                                            'totalCases' => 0,
                                            'pipelineValue' => 0,
                                            'wonValue' => 0,
                                            'totalProbability' => 0
                                        ];
                                    }
                                    $salesPersonData[$personName]['totalCases']++;
                                    $salesPersonData[$personName]['pipelineValue'] += $sale->salesCaseEstimate ?: 0;
                                    $salesPersonData[$personName]['totalProbability'] += $sale->probability ?: 0;

                                    if ($sale->saleStage == 'won' || $sale->saleStage == 'closed_won') {
                                        $salesPersonData[$personName]['wonValue'] += $sale->salesCaseEstimate ?: 0;
                                    }
                                }

                                foreach ($salesPersonData as $personName => $data):
                                    $winRate        = ($data['pipelineValue'] > 0) ? round(($data['wonValue'] / $data['pipelineValue']) * 100, 1) : 0;
                                    $avgProbability = ($data['totalCases'] > 0)    ? round($data['totalProbability'] / $data['totalCases'], 1)    : 0;
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($personName) ?></td>
                                    <td><span class="badge bg-primary-subtle text-primary"><?= $data['totalCases'] ?></span></td>
                                    <td>KES <?= number_format($data['pipelineValue'], 0) ?></td>
                                    <td>KES <?= number_format($data['wonValue'], 0) ?></td>
                                    <td>
                                        <span class="badge <?= $winRate >= 50 ? 'bg-success-subtle text-success' : ($winRate >= 25 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger') ?>">
                                            <?= $winRate ?>%
                                        </span>
                                    </td>
                                    <td><?= $avgProbability ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

