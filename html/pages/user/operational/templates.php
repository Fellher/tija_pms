<?php
/**
 * Templates - User
 *
 * View available operational task templates
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

// Get filters
$functionalArea = $_GET['functionalArea'] ?? '';
$frequency = $_GET['frequency'] ?? '';
$search = $_GET['search'] ?? '';

// Get templates
$filters = ['isActive' => 'Y', 'Suspended' => 'N'];
if ($functionalArea) $filters['functionalArea'] = $functionalArea;
if ($frequency) $filters['frequencyType'] = $frequency;

$templates = OperationalTaskTemplate::listTemplates($filters, $DBConn);

$pageTitle = "Operational Task Templates";
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
                        <li class="breadcrumb-item active">Templates</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex gap-2 flex-wrap">
                        <div class="input-group" style="width: 300px;">
                            <span class="input-group-text"><i class="ri-search-line"></i></span>
                            <input type="text" class="form-control" id="searchInput" placeholder="Search templates..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <select class="form-select" id="functionalAreaFilter" style="width: 200px;">
                            <option value="">All Functional Areas</option>
                            <option value="Finance" <?php echo $functionalArea == 'Finance' ? 'selected' : ''; ?>>Finance</option>
                            <option value="HR" <?php echo $functionalArea == 'HR' ? 'selected' : ''; ?>>HR</option>
                            <option value="IT" <?php echo $functionalArea == 'IT' ? 'selected' : ''; ?>>IT</option>
                            <option value="Sales" <?php echo $functionalArea == 'Sales' ? 'selected' : ''; ?>>Sales</option>
                            <option value="Marketing" <?php echo $functionalArea == 'Marketing' ? 'selected' : ''; ?>>Marketing</option>
                            <option value="Legal" <?php echo $functionalArea == 'Legal' ? 'selected' : ''; ?>>Legal</option>
                            <option value="Facilities" <?php echo $functionalArea == 'Facilities' ? 'selected' : ''; ?>>Facilities</option>
                        </select>
                        <select class="form-select" id="frequencyFilter" style="width: 180px;">
                            <option value="">All Frequencies</option>
                            <option value="daily" <?php echo $frequency == 'daily' ? 'selected' : ''; ?>>Daily</option>
                            <option value="weekly" <?php echo $frequency == 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                            <option value="monthly" <?php echo $frequency == 'monthly' ? 'selected' : ''; ?>>Monthly</option>
                            <option value="quarterly" <?php echo $frequency == 'quarterly' ? 'selected' : ''; ?>>Quarterly</option>
                            <option value="annually" <?php echo $frequency == 'annually' ? 'selected' : ''; ?>>Annually</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates Grid -->
    <div class="row">
        <?php if (empty($templates)): ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="ri-file-copy-line fs-1 text-muted mb-3"></i>
                        <h5 class="text-muted">No Templates Found</h5>
                        <p class="text-muted">No operational task templates match your filters.</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($templates as $template):
                // Helper to safely read either object or array
                $getVal = function($item, $field, $default = '') {
                    if (is_object($item) && isset($item->$field)) return $item->$field;
                    if (is_array($item) && isset($item[$field])) return $item[$field];
                    return $default;
                };
                $templateName = htmlspecialchars($getVal($template, 'templateName', 'Unknown'));
                $templateCode = htmlspecialchars($getVal($template, 'templateCode', ''));
                $functionalArea = htmlspecialchars($getVal($template, 'functionalArea', 'N/A'));
                $templateDesc = htmlspecialchars(substr($getVal($template, 'templateDescription', ''), 0, 150));
                $freq = $getVal($template, 'frequencyType', 'custom');
                $estimatedDuration = $getVal($template, 'estimatedDuration', '');
                $processingMode = $getVal($template, 'processingMode', 'cron');
                $templateID = $getVal($template, 'templateID', '');
            ?>
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title mb-1"><?php echo $templateName; ?></h5>
                                    <span class="badge bg-primary"><?php echo $templateCode; ?></span>
                                </div>
                                <span class="badge bg-info"><?php echo $functionalArea; ?></span>
                            </div>
                            <p class="card-text text-muted small">
                                <?php echo $templateDesc; ?>...
                            </p>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <small class="text-muted">Frequency:</small>
                                    <span class="badge bg-secondary">
                                        <?php
                                            echo ucfirst($freq);
                                        ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <small class="text-muted">Estimated Duration:</small>
                                    <strong><?php echo !empty($estimatedDuration) ? number_format($estimatedDuration, 2) . ' hrs' : 'N/A'; ?></strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Processing Mode:</small>
                                    <span class="badge bg-<?php
                                        $mode = $processingMode;
                                        echo $mode === 'cron' ? 'success' : ($mode === 'manual' ? 'warning' : 'info');
                                    ?>">
                                        <?php echo ucfirst($mode); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="?s=user&ss=operational&p=templates&action=view&id=<?php echo htmlspecialchars($templateID); ?>"
                               class="btn btn-sm btn-primary w-100">
                                <i class="ri-eye-line me-1"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('searchInput')?.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const cards = document.querySelectorAll('.col-xl-4');
        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            card.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    document.getElementById('functionalAreaFilter')?.addEventListener('change', applyFilters);
    document.getElementById('frequencyFilter')?.addEventListener('change', applyFilters);
});

function applyFilters() {
    const url = new URL(window.location);
    const functionalArea = document.getElementById('functionalAreaFilter').value;
    const frequency = document.getElementById('frequencyFilter').value;

    if (functionalArea) {
        url.searchParams.set('functionalArea', functionalArea);
    } else {
        url.searchParams.delete('functionalArea');
    }

    if (frequency) {
        url.searchParams.set('frequency', frequency);
    } else {
        url.searchParams.delete('frequency');
    }

    window.location.href = url.toString();
}
</script>

