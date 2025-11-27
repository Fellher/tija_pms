<!-- History View -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <h5 class="mb-0">
                        <i class="ri-history-line text-primary me-2"></i>
                        Approval History
                    </h5>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" id="filterStatus">
                            <option value="">All Statuses</option>
                            <option value="6">Approved</option>
                            <option value="4">Rejected</option>
                        </select>
                        <select class="form-select form-select-sm" id="filterPeriod">
                            <option value="7">Last 7 days</option>
                            <option value="30" selected>Last 30 days</option>
                            <option value="90">Last 90 days</option>
                            <option value="365">Last year</option>
                            <option value="all">All time</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php
                $allHistory = array_merge($approvedApplications, $rejectedApplications);
                usort($allHistory, function($a, $b) {
                    return strtotime($b->DateAdded ?? '0') - strtotime($a->DateAdded ?? '0');
                });

                if (!empty($allHistory)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="historyTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Date Decided</th>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Leave Period</th>
                                    <th>Days</th>
                                    <th>Decision</th>
                                    <th>Comments</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allHistory as $item):
                                    $initials = Core::get_user_name_initials($item->employeeID, $DBConn);
                                    $isApproved = $item->leaveStatusID == 6;
                                ?>
                                <tr data-status="<?= $item->leaveStatusID ?>"
                                    data-date="<?= $item->DateAdded ?>">
                                    <td>
                                        <small class="text-muted">
                                            <?= date('M d, Y', strtotime($item->DateAdded)) ?>
                                            <br>
                                            <?= date('h:i A', strtotime($item->DateAdded)) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-primary-transparent rounded-circle me-2">
                                                <span class="avatar-initials small"><?= $initials['initials'] ?></span>
                                            </div>
                                            <div>
                                                <strong><?= htmlspecialchars($item->employeeName) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($item->jobTitle ?? 'N/A') ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: <?= $item->leaveColor ?? '#6c757d' ?>;">
                                            <?= htmlspecialchars($item->leaveTypeName) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small>
                                            <?= date('M d', strtotime($item->startDate)) ?> -
                                            <?= date('M d, Y', strtotime($item->endDate)) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong><?= $item->noOfDays ?: Leave::countWeekdays($item->startDate, $item->endDate) ?></strong> days
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $isApproved ? 'success' : 'danger' ?>">
                                            <i class="ri-<?= $isApproved ? 'checkbox' : 'close' ?>-circle-line me-1"></i>
                                            <?= $isApproved ? 'Approved' : 'Rejected' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($item->approverComments)): ?>
                                            <button class="btn btn-sm btn-light"
                                                    data-bs-toggle="tooltip"
                                                    title="<?= htmlspecialchars($item->approverComments) ?>">
                                                <i class="ri-message-3-line"></i>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted small">No comments</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="?s=<?= $s ?>&ss=<?= $ss ?>&p=<?= $p ?>&view=detail&applicationID=<?= $item->leaveApplicationID ?>"
                                           class="btn btn-sm btn-light">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="ri-inbox-line text-muted" style="font-size: 4rem;"></i>
                        <h4 class="mt-3">No History Yet</h4>
                        <p class="text-muted">Your approval history will appear here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Summary Statistics for History -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="ri-bar-chart-line text-primary me-2"></i>
                    Approval Breakdown
                </h6>
            </div>
            <div class="card-body">
                <canvas id="approvalBreakdownChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h6 class="mb-0">
                    <i class="ri-line-chart-line text-primary me-2"></i>
                    Monthly Trend
                </h6>
            </div>
            <div class="card-body">
                <canvas id="monthlyTrendChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter functionality
    const statusFilter = document.getElementById('filterStatus');
    const periodFilter = document.getElementById('filterPeriod');
    const tableRows = document.querySelectorAll('#historyTable tbody tr');

    function applyFilters() {
        const selectedStatus = statusFilter.value;
        const selectedPeriod = parseInt(periodFilter.value);
        const now = new Date();

        tableRows.forEach(row => {
            let show = true;

            // Status filter
            if (selectedStatus && row.dataset.status !== selectedStatus) {
                show = false;
            }

            // Period filter
            if (selectedPeriod !== 'all') {
                const rowDate = new Date(row.dataset.date);
                const daysDiff = (now - rowDate) / (1000 * 60 * 60 * 24);
                if (daysDiff > selectedPeriod) {
                    show = false;
                }
            }

            row.style.display = show ? '' : 'none';
        });
    }

    statusFilter.addEventListener('change', applyFilters);
    periodFilter.addEventListener('change', applyFilters);

    // Approval Breakdown Chart
    const approvalCtx = document.getElementById('approvalBreakdownChart');
    if (approvalCtx) {
        const approved = <?= count(array_filter($allHistory, function($item) { return $item->leaveStatusID == 6; })) ?>;
        const rejected = <?= count(array_filter($allHistory, function($item) { return $item->leaveStatusID == 4; })) ?>;

        new Chart(approvalCtx, {
            type: 'doughnut',
            data: {
                labels: ['Approved', 'Rejected'],
                datasets: [{
                    data: [approved, rejected],
                    backgroundColor: ['#28a745', '#dc3545'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Monthly Trend Chart
    const trendCtx = document.getElementById('monthlyTrendChart');
    if (trendCtx) {
        // Group by month
        const monthlyData = {};
        const allHistory = <?= json_encode($allHistory) ?>;

        allHistory.forEach(item => {
            const month = new Date(item.DateAdded).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            if (!monthlyData[month]) {
                monthlyData[month] = { approved: 0, rejected: 0 };
            }
            if (item.leaveStatusID == 6) {
                monthlyData[month].approved++;
            } else if (item.leaveStatusID == 4) {
                monthlyData[month].rejected++;
            }
        });

        const months = Object.keys(monthlyData).slice(-6); // Last 6 months
        const approvedData = months.map(m => monthlyData[m].approved);
        const rejectedData = months.map(m => monthlyData[m].rejected);

        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Approved',
                        data: approvedData,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Rejected',
                        data: rejectedData,
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    // Initialize tooltips
    const tooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltips.map(el => new bootstrap.Tooltip(el));
});
</script>

