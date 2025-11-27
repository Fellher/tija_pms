<!-- Leave Types List View -->
<div class="leave-types-management">
    <!-- Quick Stats Cards -->
    <div class="row mb-4">
        <?php
        $totalTypes = count($leaveTypes);
        $activeTypes = count(array_filter($leaveTypes, function($type) { return $type->Suspended === 'N'; }));
        $suspendedTypes = count(array_filter($leaveTypes, function($type) { return $type->Suspended === 'Y'; }));
        $withCodes = count(array_filter($leaveTypes, function($type) { return !empty($type->leaveTypeCode); }));
        ?>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
            <div class="card custom-card overflow-hidden stats-card-modern">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md avatar-rounded bg-primary-transparent me-3">
                            <i class="ri-calendar-check-line fs-20 text-primary"></i>
                        </div>
                        <div class="flex-fill">
                            <p class="text-muted mb-0 fs-12">Total Leave Types</p>
                            <h4 class="fw-semibold mb-0"><?= $totalTypes ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
            <div class="card custom-card overflow-hidden stats-card-modern">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md avatar-rounded bg-success-transparent me-3">
                            <i class="ri-check-line fs-20 text-success"></i>
                        </div>
                        <div class="flex-fill">
                            <p class="text-muted mb-0 fs-12">Active Types</p>
                            <h4 class="fw-semibold mb-0"><?= $activeTypes ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
            <div class="card custom-card overflow-hidden stats-card-modern">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md avatar-rounded bg-warning-transparent me-3">
                            <i class="ri-pause-line fs-20 text-warning"></i>
                        </div>
                        <div class="flex-fill">
                            <p class="text-muted mb-0 fs-12">Suspended Types</p>
                            <h4 class="fw-semibold mb-0"><?= $suspendedTypes ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-3">
            <div class="card custom-card overflow-hidden stats-card-modern">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md avatar-rounded bg-info-transparent me-3">
                            <i class="ri-code-line fs-20 text-info"></i>
                        </div>
                        <div class="flex-fill">
                            <p class="text-muted mb-0 fs-12">With Codes</p>
                            <h4 class="fw-semibold mb-0"><?= $withCodes ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="card custom-card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="ri-search-line text-muted"></i>
                        </span>
                        <input type="text"
                               id="searchInput"
                               class="form-control border-start-0 ps-0"
                               placeholder="Search by name, code, or description...">
                        <button class="btn btn-outline-secondary border-start-0" type="button" id="clearSearch" style="display: none;">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <select id="statusFilter" class="form-select form-select-lg">
                        <option value="">All Status</option>
                        <option value="active">Active Only</option>
                        <option value="suspended">Suspended Only</option>
                    </select>
                </div>
                <div class="col-md-3 text-md-end">
                    <a href="<?= $base ?>html/?s=admin&ss=leave&p=leave_types&action=create"
                       class="btn btn-primary btn-lg w-100 w-md-auto">
                        <i class="ri-add-line me-1"></i>
                        Create New Leave Type
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Types Grid -->
    <div id="leaveTypesContainer">
        <?php if (!empty($leaveTypes)): ?>
            <div class="row" id="leaveTypesGrid">
                <?php foreach ($leaveTypes as $index => $type):
                    $isActive = $type->Suspended === 'N';
                    // Get usage stats
                    $applications = Leave::leave_applications(array('leaveTypeID' => $type->leaveTypeID), false, $DBConn);
                    $totalApps = $applications ? count($applications) : 0;
                ?>
                <div class="col-xl-4 col-lg-6 col-md-6 mb-4 leave-type-item"
                     data-name="<?= strtolower(htmlspecialchars($type->leaveTypeName)) ?>"
                     data-code="<?= strtolower(htmlspecialchars($type->leaveTypeCode ?? '')) ?>"
                     data-description="<?= strtolower(htmlspecialchars($type->leaveTypeDescription ?? '')) ?>"
                     data-status="<?= $isActive ? 'active' : 'suspended' ?>">
                    <div class="card custom-card leave-type-card-modern h-100">
                        <div class="card-header border-bottom-0 pb-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-fill">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar avatar-sm avatar-rounded bg-primary-transparent me-2">
                                            <i class="ri-calendar-2-line text-primary"></i>
                                        </div>
                                        <h5 class="mb-0 fw-semibold leave-type-title"><?= htmlspecialchars($type->leaveTypeName) ?></h5>
                                    </div>
                                    <?php if (!empty($type->leaveTypeCode)): ?>
                                    <span class="badge bg-primary-transparent text-primary leave-type-code-badge-modern">
                                        <i class="ri-code-line me-1"></i><?= htmlspecialchars($type->leaveTypeCode) ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-icon btn-light" type="button" data-bs-toggle="dropdown">
                                        <i class="ri-more-2-line"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="<?= $base ?>html/?s=admin&ss=leave&p=leave_types&action=view&leaveTypeID=<?= $type->leaveTypeID ?>">
                                                <i class="ri-eye-line me-2"></i>View Details
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="<?= $base ?>html/?s=admin&ss=leave&p=leave_types&action=edit&leaveTypeID=<?= $type->leaveTypeID ?>">
                                                <i class="ri-edit-line me-2"></i>Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="<?= $base ?>html/?s=admin&ss=leave&p=leave_policies&action=create&leaveTypeID=<?= $type->leaveTypeID ?>">
                                                <i class="ri-settings-3-line me-2"></i>Configure Policy
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-<?= $isActive ? 'warning' : 'success' ?>"
                                               href="javascript:void(0)"
                                               onclick="toggleLeaveTypeStatus(<?= $type->leaveTypeID ?>, '<?= $type->Suspended ?>')">
                                                <i class="ri-<?= $isActive ? 'pause' : 'play' ?>-line me-2"></i>
                                                <?= $isActive ? 'Suspend' : 'Activate' ?>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-danger delete-leave-type"
                                               href="<?= $base ?>html/?s=admin&ss=leave&p=leave_types&action=delete&leaveTypeID=<?= $type->leaveTypeID ?>"
                                               data-name="<?= htmlspecialchars($type->leaveTypeName) ?>">
                                                <i class="ri-delete-bin-line me-2"></i>Delete
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($type->leaveTypeDescription)): ?>
                            <p class="text-muted mb-3 leave-type-description" style="font-size: 0.875rem; line-height: 1.5;">
                                <?= htmlspecialchars(mb_substr($type->leaveTypeDescription, 0, 120)) ?><?= mb_strlen($type->leaveTypeDescription) > 120 ? '...' : '' ?>
                            </p>
                            <?php endif; ?>

                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <span class="badge bg-<?= $isActive ? 'success' : 'warning' ?>-transparent text-<?= $isActive ? 'success' : 'warning' ?> status-badge-modern">
                                        <i class="ri-<?= $isActive ? 'check' : 'pause' ?>-circle-line me-1"></i>
                                        <?= $isActive ? 'Active' : 'Suspended' ?>
                                    </span>
                                </div>
                                <div class="text-muted small">
                                    <i class="ri-file-list-3-line me-1"></i>
                                    <?= $totalApps ?> application<?= $totalApps !== 1 ? 's' : '' ?>
                                </div>
                            </div>

                            <div class="border-top pt-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="ri-time-line me-1"></i>
                                        Updated <?= date('M d, Y', strtotime($type->LastUpdate)) ?>
                                    </small>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= $base ?>html/?s=admin&ss=leave&p=leave_types&action=view&leaveTypeID=<?= $type->leaveTypeID ?>"
                                           class="btn btn-outline-primary btn-sm"
                                           data-bs-toggle="tooltip" title="View Details">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        <a href="<?= $base ?>html/?s=admin&ss=leave&p=leave_types&action=edit&leaveTypeID=<?= $type->leaveTypeID ?>"
                                           class="btn btn-outline-success btn-sm"
                                           data-bs-toggle="tooltip" title="Edit">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        <a href="<?= $base ?>html/?s=admin&ss=leave&p=leave_policies&action=create&leaveTypeID=<?= $type->leaveTypeID ?>"
                                           class="btn btn-outline-info btn-sm"
                                           data-bs-toggle="tooltip" title="Configure Policy">
                                            <i class="ri-settings-3-line"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- No Results Message -->
            <div id="noResults" class="text-center py-5" style="display: none;">
                <div class="avatar avatar-xl bg-light mx-auto mb-3">
                    <i class="ri-search-line text-muted fs-32"></i>
                </div>
                <h5 class="text-muted">No leave types found</h5>
                <p class="text-muted">Try adjusting your search or filter criteria</p>
            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div class="card custom-card">
                <div class="card-body text-center py-5">
                    <div class="avatar avatar-xl bg-primary-transparent mx-auto mb-4">
                        <i class="ri-calendar-check-line text-primary fs-48"></i>
                    </div>
                    <h4 class="mb-2">No Leave Types Found</h4>
                    <p class="text-muted mb-4">
                        Get started by creating your first leave type. Leave types define the different categories of leave available in your organization.
                    </p>
                    <a href="<?= $base ?>html/?s=admin&ss=leave&p=leave_types&action=create" class="btn btn-primary btn-lg">
                        <i class="ri-add-line me-2"></i>
                        Create Your First Leave Type
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.leave-types-management {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.stats-card-modern {
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.08);
}

.stats-card-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.leave-type-card-modern {
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,0.08);
}

.leave-type-card-modern:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    border-color: var(--bs-primary);
}

.leave-type-code-badge-modern {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
}

.status-badge-modern {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.35rem 0.75rem;
    border-radius: 6px;
}

.leave-type-title {
    font-size: 1.125rem;
    color: #172B4D;
    font-weight: 600;
}

.leave-type-description {
    color: #5E6C84;
    line-height: 1.6;
}

#searchInput:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.25);
}

.leave-type-item {
    animation: slideIn 0.3s ease-out;
    animation-fill-mode: both;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.leave-type-item:nth-child(1) { animation-delay: 0.05s; }
.leave-type-item:nth-child(2) { animation-delay: 0.1s; }
.leave-type-item:nth-child(3) { animation-delay: 0.15s; }
.leave-type-item:nth-child(4) { animation-delay: 0.2s; }
.leave-type-item:nth-child(5) { animation-delay: 0.25s; }
.leave-type-item:nth-child(6) { animation-delay: 0.3s; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const clearSearch = document.getElementById('clearSearch');
    const leaveTypeItems = document.querySelectorAll('.leave-type-item');
    const noResults = document.getElementById('noResults');

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    function filterLeaveTypes() {
        const searchTerm = (searchInput?.value || '').toLowerCase().trim();
        const statusValue = statusFilter?.value || '';
        let visibleCount = 0;

        leaveTypeItems.forEach(item => {
            const name = item.dataset.name || '';
            const code = item.dataset.code || '';
            const description = item.dataset.description || '';
            const status = item.dataset.status || '';

            const matchesSearch = !searchTerm ||
                name.includes(searchTerm) ||
                code.includes(searchTerm) ||
                description.includes(searchTerm);

            const matchesStatus = !statusValue || status === statusValue;

            if (matchesSearch && matchesStatus) {
                item.style.display = '';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        // Show/hide no results message
        if (noResults) {
            noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        }

        // Show/hide clear button
        if (clearSearch) {
            clearSearch.style.display = searchTerm ? 'block' : 'none';
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterLeaveTypes);
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                filterLeaveTypes();
            }
        });
    }

    if (statusFilter) {
        statusFilter.addEventListener('change', filterLeaveTypes);
    }

    if (clearSearch) {
        clearSearch.addEventListener('click', function() {
            searchInput.value = '';
            filterLeaveTypes();
            searchInput.focus();
        });
    }

    // Delete confirmation
    document.querySelectorAll('.delete-leave-type').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const name = this.dataset.name || 'this leave type';
            if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
                window.location.href = this.href;
            }
        });
    });
});
</script>
