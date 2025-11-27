<?php
/**
 * Comprehensive Salary Components Management
 * Manages salary components, categories, and employee assignments
 * Integrated with payroll system
 *
 * URL-based tab management for persistent state on refresh
 */

// Get current tab from URL or default to 'components'
$currentTab = (isset($_GET['view']) && !empty($_GET['view'])) ? Utility::clean_string($_GET['view']) : 'components';

// Define tabs array (similar to profile_new.php)
$salaryTabsArray = [
    (object)[
        "title" => "Salary Components",
        "icon" => "ri-list-check",
        "slug" => "components",
        "active" => $currentTab == "components",
        "description" => "Manage salary components (earnings and deductions)"
    ],
    (object)[
        "title" => "Categories",
        "icon" => "ri-folder-line",
        "slug" => "categories",
        "active" => $currentTab == "categories",
        "description" => "Organize components into categories"
    ],
    (object)[
        "title" => "Statutory Rules",
        "icon" => "ri-government-line",
        "slug" => "statutory",
        "active" => $currentTab == "statutory",
        "description" => "Configure statutory deduction rules"
    ]
];

// Get all categories and components for current organization
// Use the user's organization/entity context
$userEntityID = isset($userDetails->entityID) ? $userDetails->entityID : null;
$userOrgDataID = isset($userDetails->orgDataID) ? $userDetails->orgDataID : null;

$categories = Data::salary_component_categories([
    'Suspended' => 'N'
], false, $DBConn);

// Filter by user's organization if available
if ($categories && $userOrgDataID) {
    $categories = array_filter($categories, function($cat) use ($userOrgDataID) {
        return !isset($cat->orgDataID) || $cat->orgDataID == $userOrgDataID;
    });
}

$components = Data::salary_components_with_category([
    'Suspended' => 'N'
], false, $DBConn);

// Filter by user's organization if available
if ($components && $userOrgDataID) {
    $components = array_filter($components, function($comp) use ($userOrgDataID) {
        return !isset($comp->orgDataID) || $comp->orgDataID == $userOrgDataID;
    });
}

// Get statistics
$totalComponents = $components ? count($components) : 0;
$earningsCount = $components ? count(array_filter($components, function($c) { return $c->salaryComponentType == 'earning'; })) : 0;
$deductionsCount = $components ? count(array_filter($components, function($c) { return $c->salaryComponentType == 'deduction'; })) : 0;
$statutoryCount = $components ? count(array_filter($components, function($c) { return $c->isStatutory == 'Y'; })) : 0;
?>
<div class="row">
    <div class="col-12">
        <!-- Header Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1"><i class="ri-money-dollar-circle-line me-2"></i>Salary Components Management</h4>
                        <p class="text-muted mb-0">Manage salary components, categories, and payroll configurations</p>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-primary" onclick="openCategoryModal()">
                            <i class="ri-folder-add-line me-1"></i> Add Category
                        </button>
                        <button class="btn btn-success" onclick="openComponentModal()">
                            <i class="ri-add-line me-1"></i> Add Component
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Total Components</p>
                                <h3 class="mb-0"><?= $totalComponents ?></h3>
                            </div>
                            <div class="avatar avatar-lg bg-primary-transparent">
                                <i class="ri-list-check fs-24"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Earnings</p>
                                <h3 class="mb-0 text-success"><?= $earningsCount ?></h3>
                            </div>
                            <div class="avatar avatar-lg bg-success-transparent">
                                <i class="ri-arrow-up-circle-line fs-24"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Deductions</p>
                                <h3 class="mb-0 text-danger"><?= $deductionsCount ?></h3>
                            </div>
                            <div class="avatar avatar-lg bg-danger-transparent">
                                <i class="ri-arrow-down-circle-line fs-24"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1">Statutory</p>
                                <h3 class="mb-0 text-warning"><?= $statutoryCount ?></h3>
                            </div>
                            <div class="avatar avatar-lg bg-warning-transparent">
                                <i class="ri-government-line fs-24"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Tabs Navigation - URL-based State Management -->
        <ul class="nav nav-tabs nav-tabs-custom nav-justified mb-3" role="tablist">
            <?php foreach ($salaryTabsArray as $tab):
                $tabUrl = "{$getString}&state=salaryComponents&view={$tab->slug}";
            ?>
            <li class="nav-item" role="presentation">
                <a href="<?= $tabUrl ?>"
                   class="nav-link <?= $tab->active ? 'active' : '' ?>"
                   id="<?= $tab->slug ?>-tab"
                   data-tab-slug="<?= $tab->slug ?>"
                   title="<?= $tab->description ?>">
                    <i class="<?= $tab->icon ?> me-2"></i><?= $tab->title ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>

        <!-- Tab Content - State-based Rendering -->
        <div class="tab-content-wrapper">
            <?php if ($currentTab == 'components'): ?>
            <!-- Components Tab -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Salary Components</h5>
                            <div class="input-group" style="max-width: 300px;">
                                <span class="input-group-text"><i class="ri-search-line"></i></span>
                                <input type="text" class="form-control" id="componentSearch" placeholder="Search components...">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($components && count($components) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover" id="componentsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Code</th>
                                        <th>Component Name</th>
                                        <th>Category</th>
                                        <th>Type</th>
                                        <th>Value Type</th>
                                        <th>Default Value</th>
                                        <th>Flags</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($components as $component): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($component->componentCode) ?></code></td>
                                        <td>
                                            <strong><?= htmlspecialchars($component->salaryComponentTitle) ?></strong>
                                            <?php if ($component->salaryComponentDescription): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($component->salaryComponentDescription) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info-transparent"><?= htmlspecialchars($component->salaryComponentCategoryTitle) ?></span>
                                        </td>
                                        <td>
                                            <?php if ($component->salaryComponentType == 'earning'): ?>
                                            <span class="badge bg-success">Earning</span>
                                            <?php else: ?>
                                            <span class="badge bg-danger">Deduction</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $valueTypeLabel = ucfirst($component->salaryComponentValueType);
                                            $valueTypeClass = $component->salaryComponentValueType == 'percentage' ? 'bg-warning-transparent' : 'bg-primary-transparent';
                                            ?>
                                            <span class="badge <?= $valueTypeClass ?>"><?= $valueTypeLabel ?></span>
                                        </td>
                                        <td>
                                            <?php if ($component->salaryComponentValueType == 'percentage'): ?>
                                                <?= number_format($component->defaultValue, 2) ?>%
                                            <?php else: ?>
                                                KES <?= number_format($component->defaultValue, 2) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php if ($component->isStatutory == 'Y'): ?>
                                                <span class="badge bg-warning" title="Statutory"><i class="ri-government-line"></i></span>
                                                <?php endif; ?>
                                                <?php if ($component->isMandatory == 'Y'): ?>
                                                <span class="badge bg-info" title="Mandatory"><i class="ri-check-line"></i></span>
                                                <?php endif; ?>
                                                <?php if ($component->isTaxable == 'Y'): ?>
                                                <span class="badge bg-secondary" title="Taxable"><i class="ri-money-dollar-line"></i></span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-sm btn-primary" onclick="editComponent(<?= $component->salaryComponentID ?>)" title="Edit">
                                                    <i class="ri-edit-line"></i>
                                                </button>
                                                <button class="btn btn-sm btn-info" onclick="viewComponentDetails(<?= $component->salaryComponentID ?>)" title="View Details">
                                                    <i class="ri-eye-line"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteComponent(<?= $component->salaryComponentID ?>)" title="Delete">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="ri-inbox-line fs-48 text-muted mb-3"></i>
                            <p class="text-muted">No salary components found. Click "Add Component" to create your first salary component.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($currentTab == 'categories'): ?>
            <!-- Categories Tab -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Salary Component Categories</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($categories && count($categories) > 0): ?>
                        <div class="row">
                            <?php foreach ($categories as $category): ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card h-100 border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-1"><?= htmlspecialchars($category->salaryComponentCategoryTitle) ?></h6>
                                                <code class="small"><?= htmlspecialchars($category->categoryCode) ?></code>
                                            </div>
                                            <span class="badge bg-<?= $category->categoryType == 'earning' ? 'success' : ($category->categoryType == 'statutory' ? 'warning' : 'danger') ?>-transparent">
                                                <?= ucfirst($category->categoryType) ?>
                                            </span>
                                        </div>
                                        <p class="text-muted small mb-3"><?= htmlspecialchars($category->salaryComponentCategoryDescription) ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <?php if ($category->isSystemCategory == 'Y'): ?>
                                            <span class="badge bg-info-transparent">System Category</span>
                                            <?php else: ?>
                                            <span></span>
                                            <?php endif; ?>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-sm btn-outline-primary" onclick="editCategory(<?= $category->salaryComponentCategoryID ?>)">
                                                    <i class="ri-edit-line"></i>
                                                </button>
                                                <?php if ($category->isSystemCategory != 'Y'): ?>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteCategory(<?= $category->salaryComponentCategoryID ?>)">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="ri-folder-2-line fs-48 text-muted mb-3"></i>
                            <p class="text-muted">No categories found. Click "Add Category" to create your first category.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($currentTab == 'statutory'): ?>
            <!-- Statutory Rules Tab -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Statutory Computation Rules</h5>
                            <button class="btn btn-sm btn-primary" onclick="openRuleModal()">
                                <i class="ri-add-line me-1"></i> Add Rule
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="ri-information-line me-2"></i>
                            <strong>Coming Soon:</strong> Configure PAYE tax brackets, NHIF rates, NSSF tiers, and other statutory deduction rules.
                        </div>
                        <p class="text-muted">This section will allow you to configure complex payroll computation rules including:</p>
                        <ul class="text-muted">
                            <li>PAYE Tax Calculation (Kenya Tax Brackets)</li>
                            <li>NHIF Contribution Rates</li>
                            <li>NSSF Tier System</li>
                            <li>Housing Levy Rules</li>
                            <li>Custom Statutory Deductions</li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Component Modal -->
<?php include 'modals/salary_component_modal.php'; ?>

<!-- Category Modal -->
<?php include 'modals/salary_category_modal.php'; ?>

<!-- Component Details Modal -->
<?php include 'modals/salary_component_details_modal.php'; ?>

<script>
// Store data for JavaScript
const categoriesData = <?= json_encode($categories ?: []) ?>;
const componentsData = <?= json_encode($components ?: []) ?>;

// Search functionality
document.getElementById('componentSearch')?.addEventListener('keyup', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const table = document.getElementById('componentsTable');
    const rows = table.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Open component modal
function openComponentModal(componentID = null) {
    if (componentID) {
        // Edit mode - fetch and populate
        fetch('<?= $base ?>php/scripts/global/admin/salary_components_api.php?action=get&id=' + componentID)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateComponentForm(data.data);
                    document.getElementById('componentModalTitle').textContent = 'Edit Salary Component';
                } else {
                    showToast('Failed to load component: ' + (data.message || 'Unknown error'), 'danger');
                }
            });
    } else {
        // Add mode - reset form
        document.getElementById('componentForm').reset();
        document.getElementById('salaryComponentID').value = '';
        document.getElementById('componentModalTitle').textContent = 'Add Salary Component';

        // Reset auto-generation flags for new component
        const codeInput = document.getElementById('componentCode');
        if (codeInput) {
            codeInput.dataset.manuallyEdited = 'false';
            codeInput.dataset.autoGenerated = 'false';
        }
    }

    const modal = new bootstrap.Modal(document.getElementById('componentModal'));
    modal.show();
}

// Open category modal
function openCategoryModal(categoryID = null) {
    if (categoryID) {
        // Edit mode
        const category = categoriesData.find(c => c.salaryComponentCategoryID == categoryID);
        if (category) {
            populateCategoryForm(category);
            document.getElementById('categoryModalTitle').textContent = 'Edit Category';
        }
    } else {
        // Add mode
        document.getElementById('categoryForm').reset();
        document.getElementById('salaryComponentCategoryID').value = '';
        document.getElementById('categoryModalTitle').textContent = 'Add Category';

        // Reset auto-generation flags for new category
        const codeInput = document.getElementById('categoryCode');
        if (codeInput) {
            codeInput.dataset.manuallyEdited = 'false';
            codeInput.dataset.autoGenerated = 'false';
        }
    }

    const modal = new bootstrap.Modal(document.getElementById('categoryModal'));
    modal.show();
}

// Edit component
function editComponent(componentID) {
    openComponentModal(componentID);
}

// Edit category
function editCategory(categoryID) {
    openCategoryModal(categoryID);
}

// View component details
function viewComponentDetails(componentID) {
    const component = componentsData.find(c => c.salaryComponentID == componentID);
    if (component) {
        // Populate details modal
        document.getElementById('detailComponentCode').textContent = component.componentCode;
        document.getElementById('detailComponentTitle').textContent = component.salaryComponentTitle;
        document.getElementById('detailComponentDescription').textContent = component.salaryComponentDescription || 'N/A';
        document.getElementById('detailComponentCategory').textContent = component.salaryComponentCategoryTitle;
        document.getElementById('detailComponentType').textContent = component.salaryComponentType;
        document.getElementById('detailComponentValueType').textContent = component.salaryComponentValueType;
        document.getElementById('detailDefaultValue').textContent = component.defaultValue;
        document.getElementById('detailApplyTo').textContent = component.applyTo;

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('componentDetailsModal'));
        modal.show();
    }
}

// Delete component
function deleteComponent(componentID) {
    if (confirm('Are you sure you want to delete this salary component? This action cannot be undone.')) {
        fetch('<?= $base ?>php/scripts/global/admin/salary_components_api.php?action=delete&id=' + componentID, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Component deleted successfully', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Failed to delete component', 'danger');
            }
        });
    }
}

// Delete category
function deleteCategory(categoryID) {
    if (confirm('Are you sure you want to delete this category? Components in this category will need to be reassigned.')) {
        fetch('<?= $base ?>php/scripts/global/admin/salary_components_api.php?action=delete_category&id=' + categoryID, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Category deleted successfully', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Failed to delete category', 'danger');
            }
        });
    }
}

// Populate component form
function populateComponentForm(component) {
    document.getElementById('salaryComponentID').value = component.salaryComponentID;

    const codeInput = document.getElementById('componentCode');
    codeInput.value = component.componentCode;
    // Mark as manually edited to prevent auto-generation during edit
    codeInput.dataset.manuallyEdited = 'true';
    codeInput.dataset.autoGenerated = 'false';

    document.getElementById('salaryComponentTitle').value = component.salaryComponentTitle;
    document.getElementById('salaryComponentDescription').value = component.salaryComponentDescription || '';
    document.getElementById('salaryComponentCategoryID').value = component.salaryComponentCategoryID;
    document.getElementById('salaryComponentType').value = component.salaryComponentType;
    document.getElementById('salaryComponentValueType').value = component.salaryComponentValueType;
    document.getElementById('defaultValue').value = component.defaultValue;
    document.getElementById('applyTo').value = component.applyTo;
    document.getElementById('isStatutory').checked = (component.isStatutory === 'Y');
    document.getElementById('isMandatory').checked = (component.isMandatory === 'Y');
    document.getElementById('isTaxable').checked = (component.isTaxable === 'Y');
    document.getElementById('isVisible').checked = (component.isVisible === 'Y');
    document.getElementById('sortOrder').value = component.sortOrder || 0;
}

// Auto-generate category code from title
document.getElementById('salaryComponentCategoryTitle')?.addEventListener('input', function(e) {
    const title = e.target.value;
    const codeInput = document.getElementById('categoryCode');

    // Only auto-generate if code field is empty or was auto-generated
    if (!codeInput.dataset.manuallyEdited) {
        const autoCode = generateCodeFromTitle(title);
        codeInput.value = autoCode;
        codeInput.dataset.autoGenerated = 'true';
    }
});

// Track manual edits to category code
document.getElementById('categoryCode')?.addEventListener('input', function(e) {
    if (!e.target.dataset.autoGenerated || e.target.dataset.autoGenerated === 'false') {
        e.target.dataset.manuallyEdited = 'true';
    }
});

// Populate category form
function populateCategoryForm(category) {
    document.getElementById('salaryComponentCategoryID').value = category.salaryComponentCategoryID;

    const codeInput = document.getElementById('categoryCode');
    codeInput.value = category.categoryCode;
    // Mark as manually edited to prevent auto-generation during edit
    codeInput.dataset.manuallyEdited = 'true';
    codeInput.dataset.autoGenerated = 'false';

    document.getElementById('salaryComponentCategoryTitle').value = category.salaryComponentCategoryTitle;
    document.getElementById('salaryComponentCategoryDescription').value = category.salaryComponentCategoryDescription || '';
    document.getElementById('categoryType').value = category.categoryType;
    document.getElementById('categorySortOrder').value = category.sortOrder || 0;
}

// Auto-generate component code from title
document.getElementById('salaryComponentTitle')?.addEventListener('input', function(e) {
    const title = e.target.value;
    const codeInput = document.getElementById('componentCode');

    // Only auto-generate if code field is empty or was auto-generated
    // Don't overwrite manual edits
    if (!codeInput.dataset.manuallyEdited) {
        const autoCode = generateCodeFromTitle(title);
        codeInput.value = autoCode;
        codeInput.dataset.autoGenerated = 'true';
    }
});

// Track manual edits to component code
document.getElementById('componentCode')?.addEventListener('input', function(e) {
    // If user manually types in code field, mark it as manually edited
    if (!e.target.dataset.autoGenerated || e.target.dataset.autoGenerated === 'false') {
        e.target.dataset.manuallyEdited = 'true';
    }
});

// Generate code from title (same logic as category codes)
function generateCodeFromTitle(title) {
    if (!title) return '';

    let code = title.trim();

    // Convert to uppercase
    code = code.toUpperCase();

    // Replace common words and symbols
    code = code.replace(/\s+&\s+/g, '_AND_');  // " & " → "_AND_"
    code = code.replace(/&/g, 'AND');           // "&" → "AND"
    code = code.replace(/\s+/g, '_');           // spaces → underscore
    code = code.replace(/-+/g, '_');            // hyphens → underscore
    code = code.replace(/'+/g, '');             // Remove apostrophes
    code = code.replace(/[^A-Z0-9_]/g, '');     // Remove special chars
    code = code.replace(/_+/g, '_');            // Multiple underscores → single
    code = code.replace(/^_|_$/g, '');          // Trim underscores

    // Limit length
    code = code.substring(0, 50);

    return code;
}

// Enable manual code editing
function enableManualCodeEdit(inputId) {
    const input = document.getElementById(inputId);
    if (input) {
        input.dataset.manuallyEdited = 'true';
        input.dataset.autoGenerated = 'false';
        input.focus();
        input.select();

        // Update hint text
        const hintId = inputId === 'componentCode' ? 'componentCodeHint' : 'categoryCodeHint';
        const hintElement = document.getElementById(hintId);
        if (hintElement) {
            hintElement.innerHTML = '<i class="ri-edit-line"></i> Manual editing enabled';
            hintElement.style.color = '#0d6efd';
        }
    }
}

// Component form submission
document.getElementById('componentForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append('action', 'save_component');

    fetch('<?= $base ?>php/scripts/global/admin/salary_components_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Component saved successfully', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Failed to save component', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while saving the component', 'danger');
    });
});

// Category form submission
document.getElementById('categoryForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append('action', 'save_category');

    fetch('<?= $base ?>php/scripts/global/admin/salary_components_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Category saved successfully', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Failed to save category', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while saving the category', 'danger');
    });
});

// Toast notification function
function showToast(message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }

    // Icon mapping
    const iconMap = {
        'danger': 'ri-error-warning-line',
        'warning': 'ri-alert-line',
        'success': 'ri-checkbox-circle-line',
        'info': 'ri-information-line'
    };

    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');

    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="${iconMap[type] || 'ri-information-line'} me-2"></i>${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    toastContainer.appendChild(toast);

    // Initialize and show toast
    if (typeof bootstrap !== 'undefined') {
        const bsToast = new bootstrap.Toast(toast, {
            autohide: true,
            delay: 5000
        });
        bsToast.show();
    } else if (typeof $ !== 'undefined' && $.fn.toast) {
        $(toast).toast({
            autohide: true,
            delay: 5000
        });
        $(toast).toast('show');
    } else {
        // Fallback: show as visible element
        toast.style.display = 'block';
        setTimeout(() => {
            toast.remove();
        }, 5000);
    }

    // Remove toast after it's hidden
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}
</script>

<style>
/* Avatar and Badge Styles */
.avatar {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.avatar-lg {
    width: 56px;
    height: 56px;
}

.bg-primary-transparent {
    background-color: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
}

.bg-success-transparent {
    background-color: rgba(25, 135, 84, 0.1);
    color: #198754;
}

.bg-danger-transparent {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.bg-warning-transparent {
    background-color: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

.bg-info-transparent {
    background-color: rgba(13, 202, 240, 0.1);
    color: #0dcaf0;
}

/* Tab Navigation Styles */
.nav-tabs-custom {
    border-bottom: 2px solid #e9ecef;
}

.nav-tabs-custom .nav-link {
    border: none;
    color: #6c757d;
    padding: 12px 20px;
    font-weight: 500;
    border-bottom: 3px solid transparent;
    transition: all 0.3s ease;
    text-decoration: none;
}

.nav-tabs-custom .nav-link:hover {
    color: #007bff;
    border-bottom-color: #007bff;
    background-color: #f8f9fa;
}

.nav-tabs-custom .nav-link.active {
    color: #007bff;
    border-bottom-color: #007bff;
    background-color: transparent;
    font-weight: 600;
}

/* Tab Content Animation */
.tab-content-wrapper {
    min-height: 400px;
    animation: fadeIn 0.4s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive Tabs */
@media (max-width: 768px) {
    .nav-tabs-custom .nav-link {
        padding: 10px 12px;
        font-size: 0.875rem;
    }

    .nav-tabs-custom .nav-link i {
        display: none;
    }
}
</style>

