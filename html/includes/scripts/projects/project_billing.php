<?php
/**
 * Project Billing & Invoicing Module
 * Comprehensive billing setup and management with wizard workflow
 *
 * @package Tija Practice Management System
 * @subpackage Projects - Billing
 * @version 3.0.0
 *
 * Features:
 * - Wizard-based initial setup
 * - Billing dashboard with metrics
 * - Organized rate management
 * - Fee & expense tracking
 * - Product type management
 * - Billing period configuration
 */

// Load billing data
$productBillingPeriodLevels = Projects::product_billing_period_levels([], false, $DBConn);
$productTypes = Projects::product_types([], false, $DBConn);
$productRateTypes = Projects::product_rate_types([], false, $DBConn);

// Check if billing is already configured
$workHourRates = Projects::billing_rate_full(['projectID' => $projectID], false, $DBConn);
$projectFeesExpenses = Projects::project_fee_expenses(['projectID' => $projectID], false, $DBConn);
$isBillingConfigured = ($workHourRates && count($workHourRates) > 0) || ($projectFeesExpenses && count($projectFeesExpenses) > 0);

// Get project details for billing context
$projectDetails = Projects::projects_mini(['projectID' => $projectID], true, $DBConn);
?>

<div class="container-fluid my-3" id="projectBillingContainer">
    <!-- Help Button -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0">
                <i class="ri-money-dollar-circle-line me-2 text-primary"></i>Project Billing & Invoicing
            </h3>
            <p class="text-muted mb-0">Manage billing rates, fees, expenses, and invoicing for this project</p>
        </div>
        <div>
            <button type="button" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#billingHelpGuideModal" title="View Help Guide">
                <i class="ri-question-line me-1"></i>Help Guide
            </button>
        </div>
    </div>

    <!-- Billing Setup Wizard (shown if not configured) -->
    <?php if (!$isBillingConfigured): ?>
    <div class="card custom-card mb-4" id="billingSetupWizard">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">
                <i class="ri-settings-3-line me-2"></i>Billing & Invoicing Setup Wizard
            </h4>
            <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#billingHelpGuideModal" title="View Setup Guide">
                <i class="ri-question-line me-1"></i>Need Help?
            </button>
        </div>
        <div class="card-body">
            <div class="wizard-container">
                <!-- Wizard Progress -->
                <div class="wizard-progress mb-4">
                    <div class="wizard-steps">
                        <div class="wizard-step active" data-step="1">
                            <div class="step-number">1</div>
                            <div class="step-label">Billing Rates</div>
                        </div>
                        <div class="wizard-step" data-step="2">
                            <div class="step-number">2</div>
                            <div class="step-label">Product Types</div>
                        </div>
                        <div class="wizard-step" data-step="3">
                            <div class="step-number">3</div>
                            <div class="step-label">Billing Periods</div>
                        </div>
                        <div class="wizard-step" data-step="4">
                            <div class="step-number">4</div>
                            <div class="step-label">Review & Complete</div>
                        </div>
                    </div>
                </div>

                <!-- Wizard Steps -->
                <div class="wizard-content">
                    <!-- Step 1: Billing Rates -->
                    <div class="wizard-panel active" data-panel="1">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <h5 class="mb-2">
                                    <i class="ri-money-dollar-circle-line me-2 text-primary"></i>Step 1: Configure Billing Rates
                                </h5>
                                <p class="text-muted mb-0">Set up billing rates for work hours, travel, and products. You can add more rates later.</p>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" data-bs-placement="left" title="Click for detailed help on billing rates" onclick="showHelpSection('billing-rates')">
                                <i class="ri-question-line"></i>
                            </button>
                        </div>

                        <!-- Inline Help Card -->
                        <div class="alert alert-info d-flex align-items-start mb-4" role="alert">
                            <i class="ri-information-line me-2 mt-1"></i>
                            <div class="flex-grow-1">
                                <strong>What are Billing Rates?</strong>
                                <p class="mb-2 small">Billing rates determine how much you charge for different types of work. Work hour rates are hourly charges for different roles, travel rates cover travel expenses, and product rates are for products or services.</p>
                                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#billingHelpGuideModal" onclick="setHelpSection('billing-rates')">
                                    <i class="ri-book-open-line me-1"></i>Learn More
                                </button>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <i class="ri-time-line text-primary" style="font-size: 2rem;"></i>
                                        <h6 class="mt-2">Work Hour Rates</h6>
                                        <p class="text-muted small">Hourly rates for different roles</p>
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#manage_billing_rate">
                                            <i class="ri-add-line me-1"></i>Add Rate
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <i class="ri-road-map-line text-success" style="font-size: 2rem;"></i>
                                        <h6 class="mt-2">Travel Rates</h6>
                                        <p class="text-muted small">Rates for travel expenses</p>
                                        <button type="button" class="btn btn-sm btn-success" onclick="showTravelRatesSetup()">
                                            <i class="ri-add-line me-1"></i>Add Rate
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-info">
                                    <div class="card-body text-center">
                                        <i class="ri-shopping-bag-line text-info" style="font-size: 2rem;"></i>
                                        <h6 class="mt-2">Product Rates</h6>
                                        <p class="text-muted small">Rates for products/services</p>
                                        <button type="button" class="btn btn-sm btn-info" onclick="showProductRatesSetup()">
                                            <i class="ri-add-line me-1"></i>Add Rate
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="alert alert-info">
                                <i class="ri-information-line me-2"></i>
                                <strong>Tip:</strong> You can skip this step and configure rates later, but at least one work hour rate is recommended.
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Product Types -->
                    <div class="wizard-panel" data-panel="2">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <h5 class="mb-2">
                                    <i class="ri-folder-line me-2 text-primary"></i>Step 2: Set Up Product Types/Categories
                                </h5>
                                <p class="text-muted mb-0">Create categories to organize your billing items (e.g., Consulting, Development, Support).</p>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" data-bs-placement="left" title="Click for detailed help on product types" onclick="showHelpSection('product-types')">
                                <i class="ri-question-line"></i>
                            </button>
                        </div>

                        <!-- Inline Help Card -->
                        <div class="alert alert-info d-flex align-items-start mb-4" role="alert">
                            <i class="ri-information-line me-2 mt-1"></i>
                            <div class="flex-grow-1">
                                <strong>What are Product Types?</strong>
                                <p class="mb-2 small">Product types help you categorize your billing items. For example, you might have "Consulting Services", "Software Development", or "Training" as product types. This helps organize fees and expenses.</p>
                                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#billingHelpGuideModal" onclick="setHelpSection('product-types')">
                                    <i class="ri-book-open-line me-1"></i>Learn More
                                </button>
                            </div>
                        </div>

                        <?php if ($productTypes && count($productTypes) > 0): ?>
                            <div class="list-group mb-3">
                                <?php foreach ($productTypes as $type): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($type->productTypeName) ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars($type->productTypeDescription) ?></small>
                                            </div>
                                            <span class="badge bg-success">Configured</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="ri-alert-line me-2"></i>
                                No product types found. Click below to add your first product type.
                            </div>
                        <?php endif; ?>

                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manage_product_Types">
                            <i class="ri-add-line me-1"></i>Add Product Type
                        </button>
                    </div>

                    <!-- Step 3: Billing Periods -->
                    <div class="wizard-panel" data-panel="3">
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div>
                                <h5 class="mb-2">
                                    <i class="ri-calendar-line me-2 text-primary"></i>Step 3: Configure Billing Period Levels
                                </h5>
                                <p class="text-muted mb-0">Set up billing period levels to categorize when items are billable (e.g., Billable Now, Billable Later, Billed).</p>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" data-bs-placement="left" title="Click for detailed help on billing periods" onclick="showHelpSection('billing-periods')">
                                <i class="ri-question-line"></i>
                            </button>
                        </div>

                        <!-- Inline Help Card -->
                        <div class="alert alert-info d-flex align-items-start mb-4" role="alert">
                            <i class="ri-information-line me-2 mt-1"></i>
                            <div class="flex-grow-1">
                                <strong>What are Billing Period Levels?</strong>
                                <p class="mb-2 small">Billing period levels help you track when items should be billed. Common levels include "Billable Now" (ready to invoice), "Billable Later" (scheduled for future billing), and "Billed" (already invoiced).</p>
                                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#billingHelpGuideModal" onclick="setHelpSection('billing-periods')">
                                    <i class="ri-book-open-line me-1"></i>Learn More
                                </button>
                            </div>
                        </div>

                        <?php if ($productBillingPeriodLevels && count($productBillingPeriodLevels) > 0): ?>
                            <div class="list-group mb-3">
                                <?php foreach ($productBillingPeriodLevels as $level): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($level->productBillingPeriodLevelName) ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars($level->productBillingPeriodLevelDescription) ?></small>
                                            </div>
                                            <span class="badge bg-success">Active</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="ri-alert-line me-2"></i>
                                No billing period levels found. Click below to add billing period levels.
                            </div>
                        <?php endif; ?>

                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manage_product_billing_period_level">
                            <i class="ri-add-line me-1"></i>Add Billing Period Level
                        </button>
                    </div>

                    <!-- Step 4: Review & Complete -->
                    <div class="wizard-panel" data-panel="4">
                        <h5 class="mb-4">
                            <i class="ri-checkbox-circle-line me-2 text-success"></i>Step 4: Review & Complete Setup
                        </h5>
                        <p class="text-muted mb-4">Review your billing configuration and complete the setup.</p>

                        <div class="card border-success">
                            <div class="card-body">
                                <h6 class="mb-3">Setup Summary</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="ri-check-line text-success me-2"></i>
                                            <span>Billing Rates: <?= $workHourRates ? count($workHourRates) : 0 ?> configured</span>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="ri-check-line text-success me-2"></i>
                                            <span>Product Types: <?= $productTypes ? count($productTypes) : 0 ?> configured</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="ri-check-line text-success me-2"></i>
                                            <span>Billing Periods: <?= $productBillingPeriodLevels ? count($productBillingPeriodLevels) : 0 ?> configured</span>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="ri-check-line text-success me-2"></i>
                                            <span>Project: <?= htmlspecialchars($projectDetails->projectName ?? 'N/A') ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-success mt-4">
                            <i class="ri-checkbox-circle-line me-2"></i>
                            <strong>Ready to go!</strong> Your billing setup is complete. You can now manage rates, fees, and expenses.
                        </div>
                    </div>
                </div>

                <!-- Wizard Navigation -->
                <div class="wizard-footer mt-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" id="wizardPrevBtn" style="display: none;">
                        <i class="ri-arrow-left-line me-1"></i>Previous
                    </button>
                    <div class="ms-auto">
                        <button type="button" class="btn btn-outline-secondary me-2" id="wizardSkipBtn" onclick="skipWizard()">
                            Skip Setup
                        </button>
                        <button type="button" class="btn btn-primary" id="wizardNextBtn">
                            Next
                            <i class="ri-arrow-right-line ms-1"></i>
                        </button>
                        <button type="button" class="btn btn-success" id="wizardCompleteBtn" style="display: none;" onclick="completeWizard()">
                            <i class="ri-check-line me-1"></i>Complete Setup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Billing Dashboard -->
    <div class="row mb-4" id="billingDashboard">
        <div class="col-md-3">
            <div class="card custom-card border-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="ri-money-dollar-circle-line text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 text-muted">Total Billed</h6>
                            <h4 class="mb-0" id="totalBilled">KES 0.00</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card custom-card border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="ri-checkbox-circle-line text-success" style="font-size: 2rem;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 text-muted">Paid</h6>
                            <h4 class="mb-0" id="totalPaid">KES 0.00</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card custom-card border-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="ri-time-line text-warning" style="font-size: 2rem;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 text-muted">Pending</h6>
                            <h4 class="mb-0" id="totalPending">KES 0.00</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card custom-card border-danger">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="ri-alert-line text-danger" style="font-size: 2rem;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 text-muted">Overdue</h6>
                            <h4 class="mb-0" id="totalOverdue">KES 0.00</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Billing Tabs -->
    <div class="card custom-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#billingRatesTab" role="tab">
                        <i class="ri-money-dollar-circle-line me-1"></i>Billing Rates
                        <span class="badge bg-primary-transparent ms-1" data-bs-toggle="tooltip" title="Configure hourly rates, travel rates, and product rates">?</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#feesExpensesTab" role="tab">
                        <i class="ri-file-list-3-line me-1"></i>Fees & Expenses
                        <span class="badge bg-primary-transparent ms-1" data-bs-toggle="tooltip" title="Track project fees and expenses by category">?</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#productTypesTab" role="tab">
                        <i class="ri-folder-line me-1"></i>Product Types
                        <span class="badge bg-primary-transparent ms-1" data-bs-toggle="tooltip" title="Organize billing items into categories">?</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#billingPeriodsTab" role="tab">
                        <i class="ri-calendar-line me-1"></i>Billing Periods
                        <span class="badge bg-primary-transparent ms-1" data-bs-toggle="tooltip" title="Define when items are billable">?</span>
                    </a>
                </li>
            </ul>
            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#billingHelpGuideModal" title="View Help Guide">
                <i class="ri-question-line me-1"></i>Help
            </button>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <!-- Billing Rates Tab -->
                <div class="tab-pane fade show active" id="billingRatesTab" role="tabpanel">
                    <div class="mb-3">
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="rateOptionVal" id="work_hour_rates" autocomplete="off" value="work_hour_rates" checked>
                            <label class="btn btn-outline-primary" for="work_hour_rates">
                                <i class="ri-time-line me-1"></i>Work Hour Rates
                            </label>
                            <input type="radio" class="btn-check" name="rateOptionVal" id="travel_rates" autocomplete="off" value="travel_rates">
                            <label class="btn btn-outline-primary" for="travel_rates">
                                <i class="ri-road-map-line me-1"></i>Travel Rates
                            </label>
                            <input type="radio" class="btn-check" name="rateOptionVal" id="product_rates" autocomplete="off" value="product_rates">
                            <label class="btn btn-outline-primary" for="product_rates">
                                <i class="ri-shopping-bag-line me-1"></i>Product Rates
                            </label>
                        </div>
                    </div>
                    <div id="rateContentContainer">
                        <?php
                        $rateOption = (isset($_GET['rateOption']) && !empty($_GET['rateOption'])) ? Utility::clean_string($_GET['rateOption']) : 'work_hour_rates';
                        $pages = [
                            'work_hour_rates' => 'work_hour_rates.php',
                            'travel_rates' => 'travel_rates.php',
                            'product_rates' => 'product_rates.php'
                        ];
                        $page = isset($pages[$rateOption]) ? $pages[$rateOption] : $pages['work_hour_rates'];
                        include_once("includes/scripts/projects/billing/" . $page);
                        ?>
                    </div>
                </div>

                <!-- Fees & Expenses Tab -->
                <div class="tab-pane fade" id="feesExpensesTab" role="tabpanel">
                    <div class="mb-3">
                        <?php if ($productTypes && count($productTypes) > 0): ?>
                            <div class="btn-group flex-wrap" role="group">
                                <?php
                                $productTypeID = isset($_GET['productType']) ? $_GET['productType'] : $productTypes[0]->productTypeID;
                                foreach ($productTypes as $type):
                                ?>
                                    <input type="radio" class="btn-check" name="productTypeVal" id="pt_<?= $type->productTypeID ?>" autocomplete="off" value="<?= $type->productTypeID ?>" <?= ($productTypeID == $type->productTypeID) ? 'checked' : '' ?>>
                                    <label class="btn btn-outline-secondary" for="pt_<?= $type->productTypeID ?>">
                                        <?= htmlspecialchars($type->productTypeName) ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="ri-alert-line me-2"></i>No product types found. Please add product types first.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manage_fee_cost">
                            <i class="ri-add-line me-1"></i>Add Fee & Expense
                        </button>
                    </div>

                    <?php
                    if ($productTypes && count($productTypes) > 0):
                        $productTypeID = isset($_GET['productType']) ? $_GET['productType'] : $productTypes[0]->productTypeID;
                        $feesExpenses = Projects::project_fee_expenses(['projectID' => $projectID], false, $DBConn);

                        if ($feesExpenses) {
                            $feesExpenses = array_filter($feesExpenses, function($feeExpense) use ($productTypeID) {
                                return ($feeExpense->productTypeID == $productTypeID);
                            });
                        } else {
                            $feesExpenses = array();
                        }

                        $billableNowFeesExpenses = array_filter($feesExpenses, function($feeExpense) {
                            return ($feeExpense->billed == "N") && ($feeExpense->billable == "immediately" || $feeExpense->billingDate <= date('Y-m-d'));
                        });

                        $billableLatterFeesExpenses = array_filter($feesExpenses, function($feeExpense) {
                            return ($feeExpense->billed == "N") && ($feeExpense->billingDate >= date('Y-m-d')) && ($feeExpense->billable != "none_billable");
                        });

                        $billedFeeExpense = array_filter($feesExpenses, function($feeExpense) {
                            return ($feeExpense->billed == "Y");
                        });

                        if ($productBillingPeriodLevels):
                    ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($productBillingPeriodLevels as $level): ?>
                                <div class="list-group-item">
                                    <h5 class="mb-3">
                                        <i class="ri-folder-line me-2"></i><?= htmlspecialchars($level->productBillingPeriodLevelName) ?>
                                    </h5>
                                    <?php
                                    switch ($level->productBillingPeriodLevelID) {
                                        case 1:
                                            $bilableExpense = $billableNowFeesExpenses;
                                            break;
                                        case 2:
                                            $bilableExpense = $billableLatterFeesExpenses;
                                            break;
                                        case 3:
                                            $bilableExpense = $billedFeeExpense;
                                            break;
                                        default:
                                            $bilableExpense = array();
                                            break;
                                    }
                                    ?>
                                    <?php if (count($bilableExpense) > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Description</th>
                                                        <th>Amount</th>
                                                        <th>Billing Date</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($bilableExpense as $expense): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($expense->feeExpenseDescription ?? 'N/A') ?></td>
                                                            <td>KES <?= number_format($expense->amount ?? 0, 2) ?></td>
                                                            <td><?= date('d M Y', strtotime($expense->billingDate ?? 'now')) ?></td>
                                                            <td>
                                                                <?php if ($expense->billed == 'Y'): ?>
                                                                    <span class="badge bg-success">Billed</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-warning">Pending</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <button class="btn btn-sm btn-outline-primary">Edit</button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted mb-0">No items in this category.</p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php
                        endif;
                    endif;
                    ?>
                </div>

                <!-- Product Types Tab -->
                <div class="tab-pane fade" id="productTypesTab" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Product Types & Categories</h6>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manage_product_Types">
                            <i class="ri-add-line me-1"></i>Add Product Type
                        </button>
                    </div>
                    <?php if ($productTypes && count($productTypes) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($productTypes as $type): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0"><?= htmlspecialchars($type->productTypeName) ?></h6>
                                        <p class="mb-0 text-muted small"><?= htmlspecialchars($type->productTypeDescription) ?></p>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-primary editProductType" data-bs-toggle="modal" data-bs-target="#manage_product_Types" data-id="<?= $type->productTypeID ?>" data-name="<?= htmlspecialchars($type->productTypeName) ?>" data-description="<?= htmlspecialchars($type->productTypeDescription) ?>" data-suspended="<?= $type->Suspended ?>">
                                            <i class="ri-edit-line me-1"></i>Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteProductTypeModal" data-id="<?= $type->productTypeID ?>">
                                            <i class="ri-delete-bin-line me-1"></i>Delete
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>No product types found. Add your first product type to get started.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Billing Periods Tab -->
                <div class="tab-pane fade" id="billingPeriodsTab" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Billing Period Levels</h6>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manage_product_billing_period_level">
                            <i class="ri-add-line me-1"></i>Add Billing Period Level
                        </button>
                    </div>
                    <?php if ($productBillingPeriodLevels && count($productBillingPeriodLevels) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($productBillingPeriodLevels as $level): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0"><?= htmlspecialchars($level->productBillingPeriodLevelName) ?></h6>
                                        <p class="mb-0 text-muted small"><?= htmlspecialchars($level->productBillingPeriodLevelDescription) ?></p>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-primary editProductBillingPeriodLevel" data-bs-toggle="modal" data-bs-target="#manage_product_billing_period_level" data-id="<?= $level->productBillingPeriodLevelID ?>" data-name="<?= htmlspecialchars($level->productBillingPeriodLevelName) ?>" data-description="<?= htmlspecialchars($level->productBillingPeriodLevelDescription) ?>" data-suspended="<?= $level->Suspended ?>">
                                            <i class="ri-edit-line me-1"></i>Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteProductBillingPeriodLevelModal" data-id="<?= $level->productBillingPeriodLevelID ?>">
                                            <i class="ri-delete-bin-line me-1"></i>Delete
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>No billing period levels found. Add billing period levels to organize your billing items.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Modals -->
<?php
echo Utility::form_modal_header("manage_product_Types", "projects/manage_product_types.php", "Manage Product Types", array('modal-md', 'modal-dialog-centered'), $base);
include 'includes/scripts/projects/billing/modals/manage_product_types.php';
echo Utility::form_modal_footer("Save Product Type", "manageProductType", 'btn btn-primary btn-sm');

echo Utility::form_modal_header("manage_fee_cost", "projects/manage_fee_cost.php", "Manage Project Fee & Expenses", array('modal-xl', 'modal-dialog-centered'), $base);
include 'includes/scripts/projects/billing/modals/manage_fee_cost.php';
echo Utility::form_modal_footer("Save Fee & Expense", "manageFeeCost", 'btn btn-primary btn-sm');

echo Utility::form_modal_header("manage_product_billing_period_level", "projects/manage_product_billing_period_level.php", "Manage Product Billing Period Level", array('modal-md', 'modal-dialog-centered'), $base);
include 'includes/scripts/projects/billing/modals/manage_product_billing_period_level.php';
echo Utility::form_modal_footer("Save Billing Period Level", "manageBillingPeriodLevel", 'btn btn-primary btn-sm');
?>

<!-- Billing Module Styles -->
<style>
    /* Wizard Styles */
    .wizard-container {
        position: relative;
    }

    .wizard-progress {
        position: relative;
        padding: 2rem 0;
    }

    .wizard-steps {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
    }

    .wizard-steps::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 0;
        right: 0;
        height: 2px;
        background: #e9ecef;
        z-index: 0;
    }

    .wizard-step {
        position: relative;
        z-index: 1;
        text-align: center;
        flex: 1;
    }

    .wizard-step .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e9ecef;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        margin: 0 auto 0.5rem;
        transition: all 0.3s ease;
    }

    .wizard-step.active .step-number {
        background: #0d6efd;
        color: #fff;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.2);
    }

    .wizard-step.completed .step-number {
        background: #198754;
        color: #fff;
    }

    .wizard-step .step-label {
        font-size: 0.875rem;
        color: #6c757d;
        font-weight: 500;
    }

    .wizard-step.active .step-label {
        color: #0d6efd;
        font-weight: 600;
    }

    .wizard-panel {
        display: none;
        min-height: 400px;
        padding: 1.5rem 0;
    }

    .wizard-panel.active {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .wizard-footer {
        padding-top: 1.5rem;
        border-top: 1px solid #e9ecef;
    }

    /* Dashboard Cards */
    .card.border-primary,
    .card.border-success,
    .card.border-warning,
    .card.border-danger {
        border-width: 3px !important;
    }

    /* Tab Styles */
    .nav-tabs .nav-link {
        border: none;
        border-bottom: 2px solid transparent;
        color: #6c757d;
        padding: 0.75rem 1.5rem;
    }

    .nav-tabs .nav-link:hover {
        border-bottom-color: #e9ecef;
        color: #0d6efd;
    }

    .nav-tabs .nav-link.active {
        border-bottom-color: #0d6efd;
        color: #0d6efd;
        background: transparent;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .wizard-steps {
            flex-direction: column;
            gap: 1rem;
        }

        .wizard-steps::before {
            display: none;
        }

        .wizard-step {
            width: 100%;
        }
    }
</style>

<!-- Billing Module JavaScript -->
<script>
(function() {
    'use strict';

    // Wizard functionality
    let currentWizardStep = 1;
    const totalWizardSteps = 4;

    // Initialize wizard
    function initWizard() {
        updateWizardUI();
        setupWizardNavigation();
    }

    // Update wizard UI
    function updateWizardUI() {
        // Update step indicators
        document.querySelectorAll('.wizard-step').forEach((step, index) => {
            const stepNum = index + 1;
            step.classList.remove('active', 'completed');

            if (stepNum < currentWizardStep) {
                step.classList.add('completed');
            } else if (stepNum === currentWizardStep) {
                step.classList.add('active');
            }
        });

        // Update panels
        document.querySelectorAll('.wizard-panel').forEach((panel, index) => {
            const panelNum = index + 1;
            panel.classList.remove('active');
            if (panelNum === currentWizardStep) {
                panel.classList.add('active');
            }
        });

        // Update navigation buttons
        const prevBtn = document.getElementById('wizardPrevBtn');
        const nextBtn = document.getElementById('wizardNextBtn');
        const completeBtn = document.getElementById('wizardCompleteBtn');
        const skipBtn = document.getElementById('wizardSkipBtn');

        if (prevBtn) {
            prevBtn.style.display = currentWizardStep > 1 ? 'block' : 'none';
        }

        if (nextBtn) {
            nextBtn.style.display = currentWizardStep < totalWizardSteps ? 'block' : 'none';
        }

        if (completeBtn) {
            completeBtn.style.display = currentWizardStep === totalWizardSteps ? 'block' : 'none';
        }

        if (skipBtn) {
            skipBtn.style.display = currentWizardStep < totalWizardSteps ? 'block' : 'none';
        }
    }

    // Setup wizard navigation
    function setupWizardNavigation() {
        const nextBtn = document.getElementById('wizardNextBtn');
        const prevBtn = document.getElementById('wizardPrevBtn');

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                if (currentWizardStep < totalWizardSteps) {
                    currentWizardStep++;
                    updateWizardUI();
                }
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                if (currentWizardStep > 1) {
                    currentWizardStep--;
                    updateWizardUI();
                }
            });
        }
    }

    // Skip wizard
    window.skipWizard = function() {
        if (confirm('Are you sure you want to skip the billing setup? You can configure it later.')) {
            document.getElementById('billingSetupWizard').style.display = 'none';
            localStorage.setItem('billingWizardSkipped_' + <?= $projectID ?>, 'true');
        }
    };

    // Complete wizard
    window.completeWizard = function() {
        document.getElementById('billingSetupWizard').style.display = 'none';
        localStorage.setItem('billingWizardCompleted_' + <?= $projectID ?>, 'true');

        // Show success message
        if (typeof showToast === 'function') {
            showToast('Billing setup completed successfully!', 'success');
        } else {
            alert('Billing setup completed successfully!');
        }
    };

    // Rate option switching
    document.querySelectorAll('input[name="rateOptionVal"]').forEach((radio) => {
        radio.addEventListener('change', function() {
            const rateOption = this.value;
            loadRateContent(rateOption);
        });
    });

    // Load rate content dynamically
    function loadRateContent(rateOption) {
        const container = document.getElementById('rateContentContainer');
        if (!container) return;

        container.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>';

        const url = window.location.href.split('&rateOption=')[0] + '&rateOption=' + rateOption;

        fetch(url)
            .then(response => response.text())
            .then(html => {
                // Extract only the rate content
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const rateContent = doc.querySelector('#rateContentContainer') || doc.querySelector('.row.my-4');
                if (rateContent) {
                    container.innerHTML = rateContent.innerHTML;
                } else {
                    container.innerHTML = '<div class="alert alert-warning">Content not found</div>';
                }
            })
            .catch(error => {
                console.error('Error loading rate content:', error);
                container.innerHTML = '<div class="alert alert-danger">Error loading content. Please refresh the page.</div>';
            });
    }

    // Product type switching
    document.querySelectorAll('input[name="productTypeVal"]').forEach((radio) => {
        radio.addEventListener('change', function() {
            const productTypeID = this.value;
            // Reload page with new product type
            const url = new URL(window.location.href);
            url.searchParams.set('productType', productTypeID);
            window.location.href = url.toString();
        });
    });

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        initWizard();

        // Check if wizard was previously skipped or completed
        const wizardSkipped = localStorage.getItem('billingWizardSkipped_' + <?= $projectID ?>);
        const wizardCompleted = localStorage.getItem('billingWizardCompleted_' + <?= $projectID ?>);

        if (wizardSkipped === 'true' || wizardCompleted === 'true') {
            const wizard = document.getElementById('billingSetupWizard');
            if (wizard) {
                wizard.style.display = 'none';
            }
        }
    });

    // Helper functions
    window.showTravelRatesSetup = function() {
        document.querySelector('#travel_rates').checked = true;
        document.querySelector('#travel_rates').dispatchEvent(new Event('change'));
        document.querySelector('a[href="#billingRatesTab"]').click();
    };

    window.showProductRatesSetup = function() {
        document.querySelector('#product_rates').checked = true;
        document.querySelector('#product_rates').dispatchEvent(new Event('change'));
        document.querySelector('a[href="#billingRatesTab"]').click();
    };

    // Help guide functions
    window.setHelpSection = function(section) {
        const helpModal = document.getElementById('billingHelpGuideModal');
        if (helpModal) {
            const helpModalInstance = bootstrap.Modal.getInstance(helpModal) || new bootstrap.Modal(helpModal);
            helpModalInstance.show();

            // Scroll to section after modal opens
            setTimeout(() => {
                const targetSection = document.querySelector(`[data-help-section="${section}"]`);
                if (targetSection) {
                    targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    targetSection.classList.add('highlight-section');
                    setTimeout(() => {
                        targetSection.classList.remove('highlight-section');
                    }, 2000);
                }
            }, 500);
        }
    };

    window.showHelpSection = function(section) {
        setHelpSection(section);
    };
})();
</script>

<!-- Billing Help Guide Modal -->
<div class="modal fade" id="billingHelpGuideModal" tabindex="-1" aria-labelledby="billingHelpGuideModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="billingHelpGuideModalLabel">
                    <i class="ri-book-open-line me-2"></i>Project Billing & Invoicing - Complete User Guide
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Quick Navigation -->
                <div class="help-nav mb-4 p-3 bg-light rounded">
                    <h6 class="mb-3"><i class="ri-list-check me-2"></i>Quick Navigation</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="#help-intro" class="btn btn-sm btn-outline-primary" onclick="scrollToHelpSection('help-intro')">Introduction</a>
                        <a href="#help-billing-rates" class="btn btn-sm btn-outline-primary" onclick="scrollToHelpSection('help-billing-rates')">Billing Rates</a>
                        <a href="#help-product-types" class="btn btn-sm btn-outline-primary" onclick="scrollToHelpSection('help-product-types')">Product Types</a>
                        <a href="#help-billing-periods" class="btn btn-sm btn-outline-primary" onclick="scrollToHelpSection('help-billing-periods')">Billing Periods</a>
                        <a href="#help-fees-expenses" class="btn btn-sm btn-outline-primary" onclick="scrollToHelpSection('help-fees-expenses')">Fees & Expenses</a>
                        <a href="#help-best-practices" class="btn btn-sm btn-outline-primary" onclick="scrollToHelpSection('help-best-practices')">Best Practices</a>
                        <a href="#help-faq" class="btn btn-sm btn-outline-primary" onclick="scrollToHelpSection('help-faq')">FAQ</a>
                    </div>
                </div>

                <!-- Introduction -->
                <div class="help-section mb-5" id="help-intro" data-help-section="intro">
                    <div class="d-flex align-items-start mb-3">
                        <div class="help-icon me-3">
                            <i class="ri-information-line text-primary" style="font-size: 2rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="text-primary mb-3">Welcome to Project Billing & Invoicing</h4>
                            <p class="lead">This comprehensive guide will help you set up and manage billing and invoicing for your projects efficiently.</p>

                            <div class="alert alert-info mt-3">
                                <h6 class="alert-heading"><i class="ri-lightbulb-line me-2"></i>What You'll Learn</h6>
                                <ul class="mb-0">
                                    <li>How to configure billing rates for different types of work</li>
                                    <li>Setting up product types and categories</li>
                                    <li>Managing billing periods and timelines</li>
                                    <li>Tracking fees and expenses</li>
                                    <li>Best practices for accurate invoicing</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Billing Rates Section -->
                <div class="help-section mb-5" id="help-billing-rates" data-help-section="billing-rates">
                    <h4 class="text-primary mb-4">
                        <i class="ri-money-dollar-circle-line me-2"></i>1. Billing Rates Configuration
                    </h4>

                    <div class="card border-primary mb-3">
                        <div class="card-header bg-primary-transparent">
                            <h6 class="mb-0"><i class="ri-time-line me-2"></i>Work Hour Rates</h6>
                        </div>
                        <div class="card-body">
                            <p><strong>What are Work Hour Rates?</strong></p>
                            <p>Work hour rates define how much you charge per hour for different roles or work types. For example, you might charge KES 5,000/hour for a Senior Developer and KES 3,000/hour for a Junior Developer.</p>

                            <p class="mb-2"><strong>How to Add Work Hour Rates:</strong></p>
                            <ol>
                                <li>Click on the <strong>"Billing Rates"</strong> tab</li>
                                <li>Select <strong>"Work Hour Rates"</strong> from the toggle buttons</li>
                                <li>Click <strong>"Add Billing Rate"</strong> button</li>
                                <li>Fill in the form:
                                    <ul>
                                        <li><strong>Rate Name:</strong> A descriptive name (e.g., "Senior Developer Rate")</li>
                                        <li><strong>Rate Type:</strong> Select from existing types or add a new one inline</li>
                                        <li><strong>Work Type:</strong> The role or work category</li>
                                        <li><strong>Hourly Rate:</strong> The amount per hour</li>
                                    </ul>
                                </li>
                                <li>Click <strong>"Save Billing Rate"</strong></li>
                            </ol>

                            <div class="alert alert-success mt-3">
                                <i class="ri-checkbox-circle-line me-2"></i>
                                <strong>Tip:</strong> You can add multiple rates for the same role with different rate types (e.g., Standard Rate, Overtime Rate, Premium Rate).
                            </div>
                        </div>
                    </div>

                    <div class="card border-success mb-3">
                        <div class="card-header bg-success-transparent">
                            <h6 class="mb-0"><i class="ri-road-map-line me-2"></i>Travel Rates</h6>
                        </div>
                        <div class="card-body">
                            <p><strong>What are Travel Rates?</strong></p>
                            <p>Travel rates are used to bill clients for travel expenses. These can include mileage rates, per diem rates, or fixed travel charges.</p>

                            <p class="mb-2"><strong>How to Add Travel Rates:</strong></p>
                            <ol>
                                <li>Go to <strong>"Billing Rates"</strong> tab</li>
                                <li>Select <strong>"Travel Rates"</strong> toggle</li>
                                <li>Click <strong>"Add Travel Rate"</strong></li>
                                <li>Enter travel rate details and save</li>
                            </ol>
                        </div>
                    </div>

                    <div class="card border-info">
                        <div class="card-header bg-info-transparent">
                            <h6 class="mb-0"><i class="ri-shopping-bag-line me-2"></i>Product Rates</h6>
                        </div>
                        <div class="card-body">
                            <p><strong>What are Product Rates?</strong></p>
                            <p>Product rates are fixed prices for products or services. For example, a software license fee, a training package, or a consultation package.</p>

                            <p class="mb-2"><strong>How to Add Product Rates:</strong></p>
                            <ol>
                                <li>Go to <strong>"Billing Rates"</strong> tab</li>
                                <li>Select <strong>"Product Rates"</strong> toggle</li>
                                <li>Click <strong>"Add Product Rate"</strong></li>
                                <li>Enter product details and pricing</li>
                                <li>Save the product rate</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Product Types Section -->
                <div class="help-section mb-5" id="help-product-types" data-help-section="product-types">
                    <h4 class="text-primary mb-4">
                        <i class="ri-folder-line me-2"></i>2. Product Types & Categories
                    </h4>

                    <p><strong>What are Product Types?</strong></p>
                    <p>Product types are categories that help you organize your billing items. They act like folders for your fees and expenses, making it easier to track and report on different types of work.</p>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="text-success"><i class="ri-checkbox-circle-line me-1"></i>Good Examples</h6>
                                    <ul class="mb-0">
                                        <li>Consulting Services</li>
                                        <li>Software Development</li>
                                        <li>Training & Workshops</li>
                                        <li>Support & Maintenance</li>
                                        <li>Design Services</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="text-warning"><i class="ri-alert-line me-1"></i>Tips</h6>
                                    <ul class="mb-0">
                                        <li>Use clear, descriptive names</li>
                                        <li>Keep categories broad enough to be useful</li>
                                        <li>Add descriptions for clarity</li>
                                        <li>You can edit or delete later</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="mb-2"><strong>How to Add Product Types:</strong></p>
                    <ol>
                        <li>Go to the <strong>"Product Types"</strong> tab</li>
                        <li>Click <strong>"Add Product Categories"</strong> button</li>
                        <li>Enter:
                            <ul>
                                <li><strong>Product Type Name:</strong> The category name (e.g., "Consulting Services")</li>
                                <li><strong>Description:</strong> A brief description of what this category includes</li>
                            </ul>
                        </li>
                        <li>Click <strong>"Save Product Type"</strong></li>
                    </ol>

                    <div class="alert alert-info mt-3">
                        <i class="ri-information-line me-2"></i>
                        <strong>Note:</strong> Product types are used to organize fees and expenses. You'll assign fees/expenses to a product type when creating them.
                    </div>
                </div>

                <!-- Billing Periods Section -->
                <div class="help-section mb-5" id="help-billing-periods" data-help-section="billing-periods">
                    <h4 class="text-primary mb-4">
                        <i class="ri-calendar-line me-2"></i>3. Billing Period Levels
                    </h4>

                    <p><strong>What are Billing Period Levels?</strong></p>
                    <p>Billing period levels help you track the billing status of fees and expenses. They categorize items based on when they should be billed or have been billed.</p>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead class="table-primary">
                                <tr>
                                    <th>Level Name</th>
                                    <th>Description</th>
                                    <th>When to Use</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Billable Now</strong></td>
                                    <td>Items ready to be invoiced immediately</td>
                                    <td>Work completed, ready for billing</td>
                                </tr>
                                <tr>
                                    <td><strong>Billable Later</strong></td>
                                    <td>Items scheduled for future billing</td>
                                    <td>Scheduled work, milestone-based billing</td>
                                </tr>
                                <tr>
                                    <td><strong>Billed</strong></td>
                                    <td>Items already invoiced</td>
                                    <td>After invoice is generated</td>
                                </tr>
                                <tr>
                                    <td><strong>Non-Billable</strong></td>
                                    <td>Items that won't be billed</td>
                                    <td>Internal work, overhead costs</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p class="mb-2"><strong>How to Configure Billing Period Levels:</strong></p>
                    <ol>
                        <li>Go to the <strong>"Billing Periods"</strong> tab</li>
                        <li>Click <strong>"Add Product Rate Types"</strong> (this adds billing period levels)</li>
                        <li>Enter:
                            <ul>
                                <li><strong>Level Name:</strong> e.g., "Billable Now", "Billable Later"</li>
                                <li><strong>Description:</strong> Explain when this level is used</li>
                            </ul>
                        </li>
                        <li>Save the billing period level</li>
                    </ol>

                    <div class="alert alert-warning mt-3">
                        <i class="ri-alert-line me-2"></i>
                        <strong>Important:</strong> The system typically comes with default billing period levels. You may not need to add new ones unless you have specific billing requirements.
                    </div>
                </div>

                <!-- Fees & Expenses Section -->
                <div class="help-section mb-5" id="help-fees-expenses" data-help-section="fees-expenses">
                    <h4 class="text-primary mb-4">
                        <i class="ri-file-list-3-line me-2"></i>4. Fees & Expenses Management
                    </h4>

                    <p><strong>What are Fees & Expenses?</strong></p>
                    <p>Fees and expenses are billable items that you want to charge to your client. These can include:</p>
                    <ul>
                        <li><strong>Fees:</strong> Service charges, consultation fees, project fees</li>
                        <li><strong>Expenses:</strong> Travel costs, materials, third-party services, etc.</li>
                    </ul>

                    <p class="mb-2"><strong>How to Add Fees & Expenses:</strong></p>
                    <ol>
                        <li>Go to the <strong>"Fees & Expenses"</strong> tab</li>
                        <li>Select a <strong>Product Type</strong> from the toggle buttons (this filters by category)</li>
                        <li>Click <strong>"Add Fee & Cost"</strong> button</li>
                        <li>Fill in the form:
                            <ul>
                                <li><strong>Description:</strong> What is this fee/expense for?</li>
                                <li><strong>Amount:</strong> How much to charge</li>
                                <li><strong>Product Type:</strong> Which category does this belong to?</li>
                                <li><strong>Billing Date:</strong> When should this be billed?</li>
                                <li><strong>Billable Status:</strong> Immediately, Later, or Non-billable</li>
                            </ul>
                        </li>
                        <li>Click <strong>"Save Fee & Expense"</strong></li>
                    </ol>

                    <div class="card border-info mt-4">
                        <div class="card-header bg-info-transparent">
                            <h6 class="mb-0"><i class="ri-lightbulb-line me-2"></i>Understanding Billing Status</h6>
                        </div>
                        <div class="card-body">
                            <ul class="mb-0">
                                <li><strong>Immediately:</strong> Item appears in "Billable Now" section</li>
                                <li><strong>Later:</strong> Item appears in "Billable Later" section based on billing date</li>
                                <li><strong>Non-billable:</strong> Item is tracked but won't be invoiced</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Best Practices Section -->
                <div class="help-section mb-5" id="help-best-practices" data-help-section="best-practices">
                    <h4 class="text-primary mb-4">
                        <i class="ri-star-line me-2"></i>5. Best Practices & Tips
                    </h4>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-success h-100">
                                <div class="card-header bg-success-transparent">
                                    <h6 class="mb-0"><i class="ri-checkbox-circle-line me-2"></i>Setup Best Practices</h6>
                                </div>
                                <div class="card-body">
                                    <ul>
                                        <li>Set up billing rates before starting project work</li>
                                        <li>Create product types that match your service offerings</li>
                                        <li>Use consistent naming conventions</li>
                                        <li>Add descriptions for clarity</li>
                                        <li>Review and update rates periodically</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-info h-100">
                                <div class="card-header bg-info-transparent">
                                    <h6 class="mb-0"><i class="ri-lightbulb-line me-2"></i>Management Tips</h6>
                                </div>
                                <div class="card-body">
                                    <ul>
                                        <li>Track expenses as they occur</li>
                                        <li>Update billing status regularly</li>
                                        <li>Use billing periods to organize invoices</li>
                                        <li>Review dashboard metrics frequently</li>
                                        <li>Keep rate information up to date</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-success mt-4">
                        <h6 class="alert-heading"><i class="ri-trophy-line me-2"></i>Pro Tips</h6>
                        <ul class="mb-0">
                            <li><strong>Start Simple:</strong> Begin with basic rates and add complexity as needed</li>
                            <li><strong>Use Templates:</strong> Create rate templates for common project types</li>
                            <li><strong>Regular Reviews:</strong> Review billing setup quarterly to ensure accuracy</li>
                            <li><strong>Documentation:</strong> Add descriptions to help team members understand rates</li>
                            <li><strong>Test First:</strong> Test your billing setup with a small project before full rollout</li>
                        </ul>
                    </div>
                </div>

                <!-- FAQ Section -->
                <div class="help-section mb-5" id="help-faq" data-help-section="faq">
                    <h4 class="text-primary mb-4">
                        <i class="ri-question-line me-2"></i>6. Frequently Asked Questions
                    </h4>

                    <div class="accordion" id="billingFAQAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    <strong>Q: Do I need to set up all billing components before starting?</strong>
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#billingFAQAccordion">
                                <div class="accordion-body">
                                    <strong>A:</strong> No, you can start with basic setup and add more components as needed. However, we recommend setting up at least one work hour rate and one product type to get started.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    <strong>Q: Can I change billing rates after they're set up?</strong>
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#billingFAQAccordion">
                                <div class="accordion-body">
                                    <strong>A:</strong> Yes, you can edit billing rates at any time. Changes will apply to new time entries going forward. Existing logged time will retain the rate that was active when it was logged.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    <strong>Q: What's the difference between Product Types and Rate Types?</strong>
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#billingFAQAccordion">
                                <div class="accordion-body">
                                    <strong>A:</strong> Product Types are categories for organizing fees and expenses (e.g., "Consulting", "Development"). Rate Types are classifications for billing rates (e.g., "Standard Rate", "Overtime Rate", "Premium Rate").
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    <strong>Q: How do I add a new rate type inline?</strong>
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#billingFAQAccordion">
                                <div class="accordion-body">
                                    <strong>A:</strong> When adding a billing rate, select "+ Add New Rate Type" from the Rate Type dropdown. An inline form will appear. Enter the rate type name and description, then click "Save Rate Type". The new type will be automatically selected.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                    <strong>Q: Can I skip the wizard setup?</strong>
                                </button>
                            </h2>
                            <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#billingFAQAccordion">
                                <div class="accordion-body">
                                    <strong>A:</strong> Yes, you can skip the wizard and configure billing components later. However, completing the wizard ensures your billing is properly set up from the start.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                    <strong>Q: How do billing period levels work?</strong>
                                </button>
                            </h2>
                            <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#billingFAQAccordion">
                                <div class="accordion-body">
                                    <strong>A:</strong> Billing period levels automatically categorize your fees and expenses based on their billing date and status. Items ready to bill appear in "Billable Now", scheduled items in "Billable Later", and invoiced items in "Billed".
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Reference -->
                <div class="help-section mb-4" id="help-quick-reference">
                    <h4 class="text-primary mb-4">
                        <i class="ri-bookmark-line me-2"></i>Quick Reference Guide
                    </h4>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="ri-money-dollar-circle-line text-primary" style="font-size: 2rem;"></i>
                                    <h6 class="mt-2">Add Billing Rate</h6>
                                    <p class="small text-muted mb-2">Billing Rates Tab → Select Rate Type → Add Rate</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="ri-file-list-3-line text-success" style="font-size: 2rem;"></i>
                                    <h6 class="mt-2">Add Fee/Expense</h6>
                                    <p class="small text-muted mb-2">Fees & Expenses Tab → Select Product Type → Add Fee & Cost</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="ri-folder-line text-info" style="font-size: 2rem;"></i>
                                    <h6 class="mt-2">Add Product Type</h6>
                                    <p class="small text-muted mb-2">Product Types Tab → Add Product Categories</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Close
                </button>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="ri-printer-line me-1"></i>Print Guide
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Help Guide Styles -->
<style>
    .help-nav {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #f8f9fa;
    }

    .help-section {
        scroll-margin-top: 100px;
    }

    .help-section.highlight-section {
        background: #fff3cd;
        padding: 1rem;
        border-radius: 0.5rem;
        border: 2px solid #ffc107;
        animation: highlightPulse 2s ease;
    }

    @keyframes highlightPulse {
        0%, 100% { background: #fff3cd; }
        50% { background: #ffe69c; }
    }

    .help-icon {
        flex-shrink: 0;
    }

    .card.border-primary,
    .card.border-success,
    .card.border-info {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card.border-primary:hover,
    .card.border-success:hover,
    .card.border-info:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .accordion-button {
        font-weight: 500;
    }

    .accordion-button:not(.collapsed) {
        background-color: #e7f3ff;
        color: #0d6efd;
    }

    @media print {
        .help-nav,
        .modal-footer {
            display: none !important;
        }

        .help-section {
            page-break-inside: avoid;
        }
    }
</style>

<script>
// Scroll to help section function
function scrollToHelpSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        section.classList.add('highlight-section');
        setTimeout(() => {
            section.classList.remove('highlight-section');
        }, 2000);
    }
}

// Initialize tooltips when modal opens
document.addEventListener('DOMContentLoaded', function() {
    const helpModal = document.getElementById('billingHelpGuideModal');
    if (helpModal) {
        helpModal.addEventListener('shown.bs.modal', function() {
            // Initialize Bootstrap tooltips
            const tooltipTriggerList = [].slice.call(
                document.querySelectorAll('[data-bs-toggle="tooltip"]')
            );
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    }
});
</script>
