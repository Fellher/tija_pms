<!-- License Types Management Page -->
<?php
// Check admin access
if (!$isValidAdmin && !$isAdmin) {
    Alert::info("You need to be logged in as a valid administrator to access this page", true,
        array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    exit;
}

// Fetch license types
$licenseTypes = Admin::license_types(array('Suspended' => 'N'), false, $DBConn);
?>

<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <h1 class="page-title fw-semibold fs-24 mb-0">License Types Management</h1>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= $base ?>html/?s=core&ss=admin&p=home">Admin</a></li>
                <li class="breadcrumb-item active" aria-current="page">License Types</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-lg-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Total License Types</p>
                        <h3 class="mb-0 fw-semibold"><?= $licenseTypes ? count($licenseTypes) : 0 ?></h3>
                        <small class="text-muted fs-11">Active types</small>
                    </div>
                    <div class="ms-2">
                        <span class="avatar avatar-md bg-primary-transparent">
                            <i class="fas fa-list fs-20"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Popular Plans</p>
                        <h3 class="mb-0 fw-semibold">
                            <?php
                            $popularCount = 0;
                            if ($licenseTypes) {
                                foreach ($licenseTypes as $type) {
                                    if ($type->isPopular == 'Y') $popularCount++;
                                }
                            }
                            echo $popularCount;
                            ?>
                        </h3>
                        <small class="text-muted fs-11">Recommended plans</small>
                    </div>
                    <div class="ms-2">
                        <span class="avatar avatar-md bg-warning-transparent">
                            <i class="fas fa-star fs-20"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Free Plans</p>
                        <h3 class="mb-0 fw-semibold">
                            <?php
                            $freeCount = 0;
                            if ($licenseTypes) {
                                foreach ($licenseTypes as $type) {
                                    if ($type->monthlyPrice == 0 || $type->monthlyPrice === null) $freeCount++;
                                }
                            }
                            echo $freeCount;
                            ?>
                        </h3>
                        <small class="text-muted fs-11">No-cost options</small>
                    </div>
                    <div class="ms-2">
                        <span class="avatar avatar-md bg-success-transparent">
                            <i class="fas fa-gift fs-20"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top justify-content-between">
                    <div class="flex-fill">
                        <p class="mb-1 text-muted">Enterprise Plans</p>
                        <h3 class="mb-0 fw-semibold">
                            <?php
                            $enterpriseCount = 0;
                            if ($licenseTypes) {
                                foreach ($licenseTypes as $type) {
                                    if (stripos($type->licenseTypeName, 'enterprise') !== false) $enterpriseCount++;
                                }
                            }
                            echo $enterpriseCount;
                            ?>
                        </h3>
                        <small class="text-muted fs-11">Custom pricing</small>
                    </div>
                    <div class="ms-2">
                        <span class="avatar avatar-md bg-danger-transparent">
                            <i class="fas fa-crown fs-20"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- License Types Management -->
<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">
                    <h5 class="mb-0">License Types</h5>
                </div>
                <button type="button" class="btn btn-primary btn-wave" data-bs-toggle="modal" data-bs-target="#manageLicenseTypeModal" onclick="resetLicenseTypeForm()">
                    <i class="fas fa-plus me-2"></i>Add New License Type
                </button>
            </div>
            <div class="card-body">
                <?php if (!$licenseTypes): ?>
                    <div class="text-center py-5">
                        <div class="avatar avatar-xl bg-primary-transparent mx-auto mb-4">
                            <i class="fas fa-certificate fs-32"></i>
                        </div>
                        <h5 class="mb-3">No License Types Found</h5>
                        <p class="text-muted mb-4">Create your first license type to get started</p>
                        <button class="btn btn-primary btn-wave" data-bs-toggle="modal" data-bs-target="#manageLicenseTypeModal" onclick="resetLicenseTypeForm()">
                            <i class="fas fa-plus me-2"></i>Add License Type
                        </button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-primary">
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 15%;">License Type</th>
                                    <th style="width: 25%;">Description</th>
                                    <th style="width: 10%;">User Limit</th>
                                    <th style="width: 12%;">Pricing</th>
                                    <th style="width: 10%;">Duration</th>
                                    <th style="width: 8%;">Popular</th>
                                    <th style="width: 15%;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="sortable-license-types">
                                <?php foreach ($licenseTypes as $index => $type): ?>
                                    <tr data-id="<?= $type->licenseTypeID ?>" data-order="<?= $type->displayOrder ?>">
                                        <td class="text-center">
                                            <i class="fas fa-grip-vertical text-muted drag-handle" style="cursor: move;"></i>
                                            <?= $index + 1 ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($type->colorCode): ?>
                                                    <span class="avatar avatar-sm me-2" style="background-color: <?= htmlspecialchars($type->colorCode) ?>;">
                                                        <i class="fas <?= htmlspecialchars($type->iconClass ?? 'fa-certificate') ?> text-white"></i>
                                                    </span>
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?= htmlspecialchars($type->licenseTypeName) ?></strong>
                                                    <br><small class="text-muted"><?= htmlspecialchars($type->licenseTypeCode) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <small><?= htmlspecialchars(substr($type->licenseTypeDescription ?? '', 0, 100)) ?><?= strlen($type->licenseTypeDescription ?? '') > 100 ? '...' : '' ?></small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary-transparent">
                                                <?= $type->defaultUserLimit == 999999 ? 'Unlimited' : number_format($type->defaultUserLimit) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($type->monthlyPrice === null || $type->monthlyPrice == 0): ?>
                                                <span class="badge bg-success-transparent">Free</span>
                                            <?php else: ?>
                                                <strong>$<?= number_format($type->monthlyPrice, 2) ?></strong>/mo
                                                <?php if ($type->yearlyPrice): ?>
                                                    <br><small class="text-muted">$<?= number_format($type->yearlyPrice, 2) ?>/yr</small>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?= $type->defaultDuration ?> days
                                        </td>
                                        <td class="text-center">
                                            <?php if ($type->isPopular == 'Y'): ?>
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-star me-1"></i>Popular
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <button type="button"
                                                    class="btn btn-sm btn-info-light btn-wave"
                                                    onclick="viewLicenseType(<?= $type->licenseTypeID ?>)"
                                                    title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button"
                                                    class="btn btn-sm btn-primary-light btn-wave"
                                                    onclick="editLicenseType(<?= $type->licenseTypeID ?>)"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#manageLicenseTypeModal"
                                                    title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button"
                                                    class="btn btn-sm btn-danger-light btn-wave"
                                                    onclick="deleteLicenseType(<?= $type->licenseTypeID ?>, '<?= htmlspecialchars($type->licenseTypeName) ?>')"
                                                    title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
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

<?php
// Include the license type management modal
include "includes/core/admin/modals/manage_license_type.php";

// Initialize date pickers
include "includes/core/admin/init_date_pickers.php";

// View Details Modal - Custom structure for better UI
?>
<style>
/* View License Type Modal Scrolling */
#viewLicenseTypeModal .modal-dialog {
    max-height: 90vh;
    margin: 1.75rem auto;
}

#viewLicenseTypeModal .modal-content {
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

#viewLicenseTypeModal .modal-body {
    overflow-y: auto;
    max-height: calc(90vh - 140px);
    flex: 1 1 auto;
    padding: 0;
}

#viewLicenseTypeModal .modal-header,
#viewLicenseTypeModal .modal-footer {
    flex-shrink: 0;
}

/* Enhanced UI Styles for View Modal */
.license-detail-card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    margin-bottom: 1rem;
}

.license-detail-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    text-align: center;
}

.license-icon-lg {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.detail-section {
    padding: 1.5rem;
}

.detail-section-title {
    font-size: 1rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e9ecef;
    display: flex;
    align-items: center;
}

.detail-section-title i {
    margin-right: 0.5rem;
    color: #667eea;
}

.info-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    margin: 0.25rem;
}

.info-badge i {
    margin-right: 0.5rem;
    color: #667eea;
}

.feature-item {
    padding: 0.5rem;
    border-left: 3px solid #28a745;
    background: #f8f9fa;
    margin-bottom: 0.5rem;
    border-radius: 0 4px 4px 0;
}

.benefit-item {
    padding: 0.5rem 0.5rem 0.5rem 2rem;
    position: relative;
}

.benefit-item:before {
    content: "✓";
    position: absolute;
    left: 0.5rem;
    color: #28a745;
    font-weight: bold;
}

.restriction-item {
    padding: 0.5rem 0.5rem 0.5rem 2rem;
    position: relative;
    color: #6c757d;
}

.restriction-item:before {
    content: "•";
    position: absolute;
    left: 0.5rem;
    color: #dc3545;
    font-weight: bold;
    font-size: 1.5rem;
    line-height: 1;
}

.price-card {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    padding: 1.5rem;
    border-radius: 10px;
    text-align: center;
}

.price-amount {
    font-size: 2rem;
    font-weight: 700;
    color: #667eea;
}

.price-period {
    color: #6c757d;
    font-size: 0.875rem;
}
</style>

<div class="modal fade" id="viewLicenseTypeModal" tabindex="-1" aria-labelledby="viewLicenseTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="licenseTypeDetailsContent">
                    <!-- Content loaded dynamically -->
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.drag-handle {
    cursor: move !important;
}

.sortable-license-types tr {
    cursor: move;
}

.sortable-license-types tr:hover {
    background-color: #f8f9fa;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
// Initialize sortable for drag and drop reordering
document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.querySelector('.sortable-license-types');
    if (tbody) {
        new Sortable(tbody, {
            handle: '.drag-handle',
            animation: 150,
            onEnd: function(evt) {
                updateDisplayOrder();
            }
        });
    }
});

function updateDisplayOrder() {
    const rows = document.querySelectorAll('.sortable-license-types tr');
    const orderData = [];

    rows.forEach((row, index) => {
        const id = row.getAttribute('data-id');
        orderData.push({
            id: id,
            order: index + 1
        });
    });

    // Send AJAX request to update order
    fetch('<?= $base ?>php/scripts/global/admin/manage_license_types.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=update_order&orderData=' + encodeURIComponent(JSON.stringify(orderData))
    })
    .then(response => {
        // Check if response is OK
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        // Check content type
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                console.error('Response is not JSON:', text);
                throw new Error('Server returned HTML instead of JSON. Check console for details.');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            console.log('Order updated successfully');
        } else {
            console.error('Failed to update order:', data.message || 'Unknown error');
            alert('Error updating order: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error updating display order:', error);
        // Don't alert for every drag, just log it
        // alert('Error updating order: ' + error.message);
    });
}

function resetLicenseTypeForm() {
    document.getElementById('manageLicenseTypeForm').reset();
    document.getElementById('licenseTypeID').value = '';
    document.querySelector('#manageLicenseTypeModal .modal-title').textContent = 'Add New License Type';
}

function editLicenseType(id) {
    // Fetch license type data and populate form
    fetch('<?= $base ?>php/scripts/global/admin/manage_license_types.php?action=get&id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const type = data.licenseType;

                // Populate form fields
                document.getElementById('licenseTypeID').value = type.licenseTypeID;
                document.getElementById('licenseTypeName').value = type.licenseTypeName;
                document.getElementById('licenseTypeCode').value = type.licenseTypeCode;
                document.getElementById('licenseTypeDescription').value = type.licenseTypeDescription || '';
                document.getElementById('defaultUserLimit').value = type.defaultUserLimit;
                document.getElementById('monthlyPrice').value = type.monthlyPrice || '';
                document.getElementById('yearlyPrice').value = type.yearlyPrice || '';
                document.getElementById('defaultDuration').value = type.defaultDuration;
                document.getElementById('colorCode').value = type.colorCode || '';
                document.getElementById('iconClass').value = type.iconClass || '';
                document.getElementById('isPopular').value = type.isPopular;

                // Parse and populate features, restrictions, benefits
                if (type.features) {
                    const features = JSON.parse(type.features);
                    document.querySelectorAll('input[name="features[]"]').forEach(checkbox => {
                        checkbox.checked = features.includes(checkbox.value);
                    });
                }

                document.getElementById('restrictions').value = type.restrictions ? JSON.parse(type.restrictions).join('\n') : '';
                document.getElementById('benefits').value = type.benefits ? JSON.parse(type.benefits).join('\n') : '';

                document.querySelector('#manageLicenseTypeModal .modal-title').textContent = 'Edit License Type';
            }
        });
}

function viewLicenseType(id) {
    // Fetch and display license type details with enhanced UI
    fetch('<?= $base ?>php/scripts/global/admin/manage_license_types.php?action=get&id=' + id)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const type = data.licenseType;
                const features = type.features ? JSON.parse(type.features) : [];
                const restrictions = type.restrictions ? JSON.parse(type.restrictions) : [];
                const benefits = type.benefits ? JSON.parse(type.benefits) : [];

                // Format pricing
                const monthlyPrice = type.monthlyPrice && parseFloat(type.monthlyPrice) > 0
                    ? `$${parseFloat(type.monthlyPrice).toFixed(2)}`
                    : 'Free';
                const yearlyPrice = type.yearlyPrice && parseFloat(type.yearlyPrice) > 0
                    ? `$${parseFloat(type.yearlyPrice).toFixed(2)}`
                    : 'Free';

                // Popular badge
                const popularBadge = type.isPopular === 'Y'
                    ? '<span class="badge bg-warning ms-2"><i class="fas fa-star me-1"></i>Popular</span>'
                    : '';

                const html = `
                    <!-- Header Section -->
                    <div class="license-detail-header">
                        <div class="license-icon-lg" style="background-color: ${type.colorCode || '#667eea'};">
                            <i class="fas ${type.iconClass || 'fa-certificate'} fs-1 text-white"></i>
                        </div>
                        <h3 class="mb-2">${type.licenseTypeName}${popularBadge}</h3>
                        <p class="mb-0 opacity-75"><code>${type.licenseTypeCode}</code></p>
                    </div>

                    <!-- Description Section -->
                    <div class="detail-section">
                        <div class="detail-section-title">
                            <i class="fas fa-info-circle"></i>Description
                        </div>
                        <p class="text-muted mb-0">${type.licenseTypeDescription || 'No description provided'}</p>
                    </div>

                    <!-- Key Information -->
                    <div class="detail-section bg-light">
                        <div class="detail-section-title">
                            <i class="fas fa-chart-bar"></i>Key Information
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="info-badge w-100">
                                    <i class="fas fa-users"></i>
                                    <div>
                                        <strong>User Limit:</strong>
                                        <span class="ms-2">${type.defaultUserLimit == 999999 ? 'Unlimited' : number_format(type.defaultUserLimit) + ' users'}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-badge w-100">
                                    <i class="fas fa-clock"></i>
                                    <div>
                                        <strong>Duration:</strong>
                                        <span class="ms-2">${type.defaultDuration} days</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Section -->
                    <div class="detail-section">
                        <div class="detail-section-title">
                            <i class="fas fa-dollar-sign"></i>Pricing
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="price-card">
                                    <div class="price-amount">${monthlyPrice}</div>
                                    <div class="price-period">per month</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="price-card">
                                    <div class="price-amount">${yearlyPrice}</div>
                                    <div class="price-period">per year</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Features Section -->
                    ${features.length > 0 ? `
                    <div class="detail-section">
                        <div class="detail-section-title">
                            <i class="fas fa-check-circle"></i>Included Features (${features.length})
                        </div>
                        <div class="row g-2">
                            ${features.map(f => `
                                <div class="col-md-6">
                                    <div class="feature-item">
                                        <i class="fas fa-check text-success me-2"></i>${formatFeatureName(f)}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    ` : ''}

                    <!-- Benefits Section -->
                    ${benefits.length > 0 ? `
                    <div class="detail-section bg-light">
                        <div class="detail-section-title">
                            <i class="fas fa-gift"></i>Key Benefits (${benefits.length})
                        </div>
                        <div class="row">
                            ${benefits.map(b => `
                                <div class="col-md-6">
                                    <div class="benefit-item">${b}</div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    ` : ''}

                    <!-- Restrictions Section -->
                    ${restrictions.length > 0 ? `
                    <div class="detail-section">
                        <div class="detail-section-title">
                            <i class="fas fa-exclamation-triangle"></i>Restrictions & Limitations (${restrictions.length})
                        </div>
                        <div class="row">
                            ${restrictions.map(r => `
                                <div class="col-md-6">
                                    <div class="restriction-item">${r}</div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    ` : ''}
                `;

                document.getElementById('licenseTypeDetailsContent').innerHTML = html;
                const modal = new bootstrap.Modal(document.getElementById('viewLicenseTypeModal'));
                modal.show();
            }
        });
}

// Helper function to format feature names
function formatFeatureName(feature) {
    const featureNames = {
        'payroll': 'Payroll Management',
        'leave': 'Leave Management',
        'attendance': 'Attendance Tracking',
        'performance': 'Performance Management',
        'recruitment': 'Recruitment Module',
        'training': 'Training & Development',
        'employee_management': 'Employee Management',
        'reports': 'Basic Reports',
        'advanced_reports': 'Advanced Reports',
        'analytics': 'Analytics Dashboard',
        'custom_reports': 'Custom Report Builder',
        'api': 'API Access',
        'whitelabel': 'White-label Branding',
        'sso': 'Single Sign-On (SSO)',
        'custom_development': 'Custom Development',
        'integrations': 'Third-party Integrations',
        'mobile_app': 'Mobile App Access'
    };
    return featureNames[feature] || feature.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
}

// Helper function to format numbers
function number_format(number) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function deleteLicenseType(id, name) {
    if (confirm('Are you sure you want to delete the license type "' + name + '"?\n\nWarning: This may affect existing licenses using this type.')) {
        fetch('<?= $base ?>php/scripts/global/admin/manage_license_types.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=delete&licenseTypeID=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('License type deleted successfully');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to delete license type'));
            }
        });
    }
}
</script>

