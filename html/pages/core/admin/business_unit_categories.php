<!-- Business Unit Categories Management Page -->
<?php
// Check admin access
if (!$isValidAdmin && !$isAdmin) {
    Alert::info("You need to be logged in as a valid administrator to access this page", true,
        array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    exit;
}

// Fetch all business unit categories
$categories = Data::business_unit_categories(array(), false, $DBConn);
?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-24 mb-0">
            <i class="fas fa-tags text-primary me-2"></i>
            Business Unit Categories
        </h1>
        <p class="text-muted mb-0 mt-1">Manage business unit category classifications</p>
    </div>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="<?= $base ?>html/?s=core&ss=admin&p=home">Admin</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    Business Unit Categories
                </li>
            </ol>
        </nav>
    </div>
</div>

<!-- Quick Actions Bar -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card custom-card border-0 shadow-sm">
            <div class="card-body py-2">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <button type="button" class="btn btn-primary btn-sm btn-wave"
                        data-bs-toggle="modal"
                        data-bs-target="#manageCategoryModal"
                        onclick="addNewCategory()">
                        <i class="fas fa-plus me-2"></i>Add Category
                    </button>
                    <a href="<?= $base ?>html/?s=core&ss=admin&p=home" class="btn btn-light btn-sm btn-wave ms-auto">
                        <i class="fas fa-arrow-left me-2"></i>Back to Admin
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Categories Table -->
<div class="row">
    <div class="col-12">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Business Unit Categories</h5>
                </div>
            </div>
            <div class="card-body">
                <?php if ($categories): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 5%;"></th>
                                    <th style="width: 20%;">Category Name</th>
                                    <th style="width: 15%;">Code</th>
                                    <th style="width: 35%;">Description</th>
                                    <th style="width: 10%;" class="text-center">Status</th>
                                    <th style="width: 10%;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $index => $cat): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td class="text-center">
                                            <?php if ($cat->iconClass): ?>
                                                <i class="fas <?= htmlspecialchars($cat->iconClass) ?> fa-lg"
                                                   style="color: <?= htmlspecialchars($cat->colorCode ?? '#6c757d') ?>;"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($cat->categoryName) ?></strong>
                                        </td>
                                        <td>
                                            <code><?= htmlspecialchars($cat->categoryCode) ?></code>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($cat->categoryDescription ?? 'N/A') ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= $cat->isActive == 'Y' ? 'success' : 'danger' ?>-transparent">
                                                <?= $cat->isActive == 'Y' ? 'Active' : 'Inactive' ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-primary-light editCategory"
                                                data-bs-toggle="modal"
                                                data-bs-target="#manageCategoryModal"
                                                data-category-id="<?= $cat->categoryID ?>"
                                                title="Edit Category">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-<?= $cat->isActive == 'Y' ? 'warning' : 'success' ?>-light toggleCategoryStatus"
                                                data-category-id="<?= $cat->categoryID ?>"
                                                data-current-status="<?= $cat->isActive ?>"
                                                title="<?= $cat->isActive == 'Y' ? 'Deactivate' : 'Activate' ?>">
                                                <i class="fas fa-<?= $cat->isActive == 'Y' ? 'ban' : 'check' ?>"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="avatar avatar-lg bg-secondary-transparent mx-auto mb-3">
                            <i class="fas fa-tags fs-24"></i>
                        </div>
                        <h6 class="mb-2">No Categories Found</h6>
                        <p class="text-muted mb-3">Add business unit categories to classify your business units.</p>
                        <button type="button" class="btn btn-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#manageCategoryModal"
                            onclick="addNewCategory()">
                            <i class="fas fa-plus me-2"></i>Add First Category
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Modal for managing Categories
echo Utility::form_modal_header("manageCategoryModal", "global/admin/manage_business_unit_category.php", "Manage Business Unit Category", array('modal-md', 'modal-dialog-centered', "modal-lg"), $base);
?>
<div id="category_form" class="manageCategory">
    <div class="row g-3">
        <input type="hidden" id="categoryID" name="categoryID">

        <div class="col-md-6">
            <label for="categoryName" class="form-label mb-0">Category Name <span class="text-danger">*</span></label>
            <input type="text" id="categoryName" name="categoryName" class="form-control form-control-sm" placeholder="e.g., Cost Center" required>
        </div>

        <div class="col-md-6">
            <label for="categoryCode" class="form-label mb-0">Category Code <span class="text-danger">*</span></label>
            <input type="text" id="categoryCode" name="categoryCode" class="form-control form-control-sm" placeholder="e.g., cost_center" required>
            <small class="text-muted">Lowercase, use underscores</small>
        </div>

        <div class="col-md-12">
            <label for="categoryDescription" class="form-label mb-0">Description</label>
            <textarea id="categoryDescription" name="categoryDescription" class="form-control form-control-sm" rows="2" placeholder="Describe this category"></textarea>
        </div>

        <div class="col-md-4">
            <label for="categoryOrder" class="form-label mb-0">Display Order</label>
            <input type="number" id="categoryOrder" name="categoryOrder" class="form-control form-control-sm" min="1" value="1">
        </div>

        <div class="col-md-4">
            <label for="iconClass" class="form-label mb-0">Icon Class</label>
            <input type="text" id="iconClass" name="iconClass" class="form-control form-control-sm" placeholder="fa-chart-line">
            <small class="text-muted">Font Awesome icon</small>
        </div>

        <div class="col-md-4">
            <label for="colorCode" class="form-label mb-0">Color Code</label>
            <input type="color" id="colorCode" name="colorCode" class="form-control form-control-color form-control-sm" value="#007bff">
        </div>

        <div class="col-md-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="isActive" name="isActive" value="Y" checked>
                <label class="form-check-label" for="isActive">
                    Active
                </label>
            </div>
        </div>
    </div>
</div>
<?php
echo Utility::form_modal_footer('Save Category');
?>

<style>
.table tbody tr:hover {
    background-color: #f8f9fa;
}
</style>

<script>
function addNewCategory() {
    const modal = document.querySelector('#manageCategoryModal');
    if (modal) {
        // Reset form
        modal.querySelector('form')?.reset();

        // Set default values
        document.getElementById('categoryOrder').value = 1;
        document.getElementById('colorCode').value = '#007bff';
        document.getElementById('isActive').checked = true;

        // Update modal title
        const modalTitle = modal.querySelector('.modal-title');
        if (modalTitle) {
            modalTitle.textContent = 'Add Business Unit Category';
        }
    }
}

function editCategory(categoryID) {
    const modal = document.querySelector('#manageCategoryModal');

    if (!modal) {
        console.error('Category modal not found');
        return;
    }

    // Update modal title
    const modalTitle = modal.querySelector('.modal-title');
    if (modalTitle) {
        modalTitle.textContent = 'Edit Business Unit Category';
    }

    // Build the URL
    const url = '<?= $base ?>php/scripts/global/admin/get_business_unit_category.php?categoryID=' + categoryID;
    console.log('Fetching category data from:', url);

    // Fetch category data via AJAX
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.category) {
                const cat = data.category;
                console.log('Category data loaded:', cat);

                // Populate form fields
                document.getElementById('categoryID').value = cat.categoryID || '';
                document.getElementById('categoryName').value = cat.categoryName || '';
                document.getElementById('categoryCode').value = cat.categoryCode || '';
                document.getElementById('categoryDescription').value = cat.categoryDescription || '';
                document.getElementById('categoryOrder').value = cat.categoryOrder || 1;
                document.getElementById('iconClass').value = cat.iconClass || '';
                document.getElementById('colorCode').value = cat.colorCode || '#007bff';
                document.getElementById('isActive').checked = cat.isActive === 'Y';

            } else {
                alert('Error: ' + (data.message || 'Failed to load category data'));
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Error loading category data: ' + error.message);
        });
}

// Add event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Edit category buttons
    document.querySelectorAll('.editCategory').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const categoryID = this.getAttribute('data-category-id');
            if (categoryID) {
                editCategory(categoryID);
            }
        });
    });

    // Toggle status buttons
    document.querySelectorAll('.toggleCategoryStatus').forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const categoryID = this.getAttribute('data-category-id');
            const currentStatus = this.getAttribute('data-current-status');
            const newStatus = currentStatus === 'Y' ? 'N' : 'Y';
            const action = newStatus === 'Y' ? 'activate' : 'deactivate';

            if (confirm(`Are you sure you want to ${action} this category?`)) {
                // TODO: Implement AJAX call to toggle status
                window.location.href = '<?= $base ?>php/scripts/global/admin/toggle_category_status.php?categoryID=' + categoryID + '&newStatus=' + newStatus + '&returnURL=' + encodeURIComponent(window.location.href);
            }
        });
    });
});
</script>

