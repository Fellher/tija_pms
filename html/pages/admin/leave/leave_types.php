<?php
/**
 * Leave Types Management - Admin Page
 * Independent leave type creation and management
 * Separated from policy configuration for clarity
 */

if(!$isValidUser) {
    Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

// Check admin permissions
if (!$isAdmin && !$isValidAdmin && !$isHRManager) {
    Alert::error("Access denied. Admin privileges required.", true);
    return;
}

$entityID = $_SESSION['entityID'] ?? 1;
$currentUserID = $userDetails->ID;

// Get current page parameters
$action = $_GET['action'] ?? 'list';
$leaveTypeID = $_GET['leaveTypeID'] ?? null;

// Initialize data arrays
$leaveTypes = array();
$leaveType = null;
$errors = array();
$success = '';

try {
    // Get all leave types
    $leaveTypes = Leave::leave_types(array('Lapsed' => 'N'), false, $DBConn);

    // Handle different actions
    switch ($action) {
        case 'list':
            // List view - leave types already loaded above
            break;

        case 'view':
        case 'edit':
            if ($leaveTypeID) {
                $leaveType = Leave::leave_types(array('leaveTypeID' => $leaveTypeID), true, $DBConn);
                if (!$leaveType) {
                    $errors[] = 'Leave type not found';
                }
            } else {
                $errors[] = 'Leave type ID is required';
            }
            break;

        case 'create':
            // Create form - no data needed
            break;

        case 'delete':
            // Redirect to backend script for deletion
            if ($leaveTypeID) {
                $id = (int)$leaveTypeID;
                $siteURL = '';
                if (is_array($config) && isset($config['siteURL'])) {
                    $siteURL = $config['siteURL'];
                } elseif (is_object($config) && isset($config->siteURL)) {
                    $siteURL = $config->siteURL;
                }
                $backendScript = !empty($siteURL) ? rtrim((string)$siteURL, '/') . '/php/scripts/leave/config/delete_leave_type.php' : '/php/scripts/leave/config/delete_leave_type.php';
                header("Location: " . $backendScript . "?leaveTypeID=" . $id);
                exit;
            } else {
                $errors[] = 'Leave type ID is required';
            }
            break;

        case 'toggle_status':
            // Redirect to backend script for status toggle
            if ($leaveTypeID) {
                $id = (int)$leaveTypeID;
                $siteURL = '';
                if (is_array($config) && isset($config['siteURL'])) {
                    $siteURL = $config['siteURL'];
                } elseif (is_object($config) && isset($config->siteURL)) {
                    $siteURL = $config->siteURL;
                }
                $backendScript = !empty($siteURL) ? rtrim((string)$siteURL, '/') . '/php/scripts/leave/config/toggle_leave_type_status.php' : '/php/scripts/leave/config/toggle_leave_type_status.php';
                header("Location: " . $backendScript . "?leaveTypeID=" . $id);
                exit;
            } else {
                $errors[] = 'Leave type ID is required';
            }
            break;
    }

} catch (Exception $e) {
    $errors[] = $e->getMessage();
}

// Get page title
$pageTitle = 'Leave Types';
if ($action === 'view' || $action === 'edit') {
    $pageTitle = ($action === 'edit' ? 'Edit' : 'View') . ' Leave Type';
} elseif ($action === 'create') {
    $pageTitle = 'Create New Leave Type';
}

// Set page variables for header
$title = $pageTitle . ' - Leave Management System';
$keywords = array('leave management', 'leave types', 'admin');
?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-24 mb-0">
            <i class="ri-calendar-2-line me-2 text-primary"></i>
            <?= $pageTitle ?>
        </h1>
        <p class="text-muted mb-0 mt-2">
            <?php if ($action === 'list'): ?>
                Manage and configure all leave types available in your organization
            <?php elseif ($action === 'create'): ?>
                Define a new leave type with its basic information and display settings
            <?php else: ?>
                View and edit leave type details and configuration
            <?php endif; ?>
        </p>
    </div>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= $base ?>html/?s=admin&ss=leave&p=dashboard">Leave Admin</a></li>
                <li class="breadcrumb-item"><a href="<?= $base ?>html/?s=admin&ss=leave&p=leave_types">Leave Types</a></li>
                <?php if ($action !== 'list'): ?>
                <li class="breadcrumb-item active" aria-current="page"><?= ucfirst($action) ?></li>
                <?php endif; ?>
            </ol>
        </nav>
    </div>
</div>

<!-- Alerts -->
<?php if (!empty($errors)): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="ri-error-warning-line me-2"></i>
    <strong>Error!</strong>
    <ul class="mb-0 mt-2">
        <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="ri-check-line me-2"></i>
    <?= htmlspecialchars($success) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Content based on action -->
<?php if ($action === 'list'): ?>
    <?php include 'leave_policy_management/views/leave_types_list.php'; ?>
<?php elseif ($action === 'create'): ?>
    <?php include 'leave_policy_management/views/leave_type_form.php'; ?>
<?php elseif ($action === 'view' || $action === 'edit'): ?>
    <?php
    // Set leaveType variable for the detail view
    $leaveType = $leaveType;
    include 'leave_policy_management/views/leave_type_detail.php';
    ?>
<?php endif; ?>

<script>
// Helper functions for leave types
function toggleLeaveTypeStatus(leaveTypeID, currentStatus) {
    const action = currentStatus === 'Y' ? 'activate' : 'suspend';
    if (confirm(`Are you sure you want to ${action} this leave type?`)) {
        window.location.href = '<?= $base ?>html/?s=admin&ss=leave&p=leave_types&action=toggle_status&leaveTypeID=' + leaveTypeID;
    }
}

function generateLeaveTypeCode() {
    const nameInput = document.getElementById('leaveTypeName');
    const codeInput = document.getElementById('leaveTypeCode');

    if (nameInput && codeInput && nameInput.value) {
        const name = nameInput.value.toUpperCase();
        // Convert to code format: remove spaces, special chars, keep only alphanumeric
        let code = name.replace(/[^A-Z0-9]/g, '_').replace(/_+/g, '_').replace(/^_|_$/g, '');
        // Limit to 10 characters
        code = code.substring(0, 10);
        codeInput.value = code;

        // Trigger input event for validation
        codeInput.dispatchEvent(new Event('input'));
    }
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    // Bootstrap form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Auto-generate code when name changes
    const nameInput = document.getElementById('leaveTypeName');
    const codeInput = document.getElementById('leaveTypeCode');
    if (nameInput && codeInput && !codeInput.value) {
        nameInput.addEventListener('blur', function() {
            if (!codeInput.value) {
                generateLeaveTypeCode();
            }
        });
    }

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

