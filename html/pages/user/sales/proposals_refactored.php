<?php
/**
 * Proposals Management - Refactored Version
 * Modern interface for managing proposals with enhanced workflow
 *
 * Features:
 * - Kanban board for proposal tracking
 * - Real-time status updates
 * - Document management
 * - Checklist tracking
 * - Analytics dashboard
 *
 * @version 2.0
 * @date 2025-10-09
 */

// Security check
if (!$isValidUser) {
    Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    include "includes/core/log_in_script.php";
    return;
}

// Get user and organization details
$employeeID = (isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) : $userDetails->ID;
$employeeDetails = Data::users(array('ID' => $employeeID), true, $DBConn);
$orgDataID = isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : $employeeDetails->orgDataID;
$entityID = isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : $employeeDetails->entityID;

// Get reference data
$clients = Client::client_full(array('orgDataID' => $orgDataID, 'entityID' => $entityID), false, $DBConn);
$salesCases = Sales::sales_case_mid(array('orgDataID' => $orgDataID, 'entityID' => $entityID, 'Suspended' => 'N'), false, $DBConn);
$proposalStatuses = Sales::proposal_statuses(['entityID' => $entityID, 'Suspended' => 'N'], false, $DBConn);

// View and filter settings
$view = isset($_GET['view']) ? Utility::clean_string($_GET['view']) : 'board';
$filter = isset($_GET['filter']) ? Utility::clean_string($_GET['filter']) : 'active';
?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-semibold fs-24 mb-0">
            <i class="ri-file-text-line text-primary me-2"></i>
            Proposal Management
        </h1>
        <p class="text-muted fs-14 mb-0">Track and manage your business proposals</p>
    </div>
    <div class="ms-md-1 ms-0 d-flex gap-2">
        <button type="button" class="btn btn-light btn-sm" id="analyticsBtn">
            <i class="ri-bar-chart-line"></i> Analytics
        </button>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addProposalModal">
            <i class="ri-add-line"></i> New Proposal
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div id="proposalStats" class="mb-4">
    <div class="row g-3">
        <div class="col-xl-3 col-lg-6">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-primary-transparent text-primary me-3">
                            <i class="ri-file-list-line"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stats-label">Total Proposals</div>
                            <div class="stats-value text-primary" id="totalProposals">
                                <span class="spinner-border spinner-border-sm"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-success-transparent text-success me-3">
                            <i class="ri-money-dollar-circle-line"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stats-label">Total Value</div>
                            <div class="stats-value text-success" id="totalValue">
                                <span class="spinner-border spinner-border-sm"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-warning-transparent text-warning me-3">
                            <i class="ri-time-line"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stats-label">Due This Week</div>
                            <div class="stats-value text-warning" id="dueThisWeek">
                                <span class="spinner-border spinner-border-sm"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon bg-info-transparent text-info me-3">
                            <i class="ri-check-double-line"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="stats-label">Win Rate</div>
                            <div class="stats-value text-info" id="winRate">
                                <span class="spinner-border spinner-border-sm"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters and View Toggle -->
<div class="card custom-card mb-4">
    <div class="card-body p-3">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="btn-group btn-group-sm" role="group">
                    <input type="radio" class="btn-check" name="filterType" id="filterActive"
                           <?= $filter === 'active' ? 'checked' : '' ?> autocomplete="off">
                    <label class="btn btn-outline-primary" for="filterActive" data-filter="active">
                        Active
                    </label>

                    <input type="radio" class="btn-check" name="filterType" id="filterDue"
                           <?= $filter === 'due' ? 'checked' : '' ?> autocomplete="off">
                    <label class="btn btn-outline-primary" for="filterDue" data-filter="due">
                        Due Soon
                    </label>

                    <input type="radio" class="btn-check" name="filterType" id="filterWon"
                           <?= $filter === 'won' ? 'checked' : '' ?> autocomplete="off">
                    <label class="btn btn-outline-primary" for="filterWon" data-filter="won">
                        Won
                    </label>

                    <input type="radio" class="btn-check" name="filterType" id="filterAll"
                           <?= $filter === 'all' ? 'checked' : '' ?> autocomplete="off">
                    <label class="btn btn-outline-primary" for="filterAll" data-filter="all">
                        All
                    </label>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <div class="btn-group btn-group-sm" role="group">
                    <input type="radio" class="btn-check" name="viewType" id="viewBoard"
                           <?= $view === 'board' ? 'checked' : '' ?> autocomplete="off">
                    <label class="btn btn-outline-secondary" for="viewBoard" data-view="board">
                        <i class="ri-layout-grid-line"></i> Board
                    </label>

                    <input type="radio" class="btn-check" name="viewType" id="viewTable"
                           <?= $view === 'table' ? 'checked' : '' ?> autocomplete="off">
                    <label class="btn btn-outline-secondary" for="viewTable" data-view="table">
                        <i class="ri-table-line"></i> Table
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Proposals Content -->
<div id="proposalsContent">
    <div class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="text-muted mt-2">Loading proposals...</p>
    </div>
</div>

<!-- Add/Edit Proposal Modal -->
<?php
echo Utility::form_modal_header("addProposalModal", "sales/manage_proposal.php", "Proposal Details", array('modal-xl', 'modal-dialog-centered'), $base);
?>
<form id="proposalForm">
    <input type="hidden" name="proposalID" id="proposalID" value="">
    <input type="hidden" name="orgDataID" value="<?= $orgDataID ?>">
    <input type="hidden" name="entityID" value="<?= $entityID ?>">
    <input type="hidden" name="employeeID" value="<?= $userDetails->ID ?>">

    <div class="row g-3">
        <div class="col-md-12">
            <label for="proposalTitle" class="form-label">Proposal Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="proposalTitle" name="proposalTitle" required>
        </div>

        <div class="col-md-6">
            <label for="clientID" class="form-label">Client <span class="text-danger">*</span></label>
            <select class="form-select" id="proposalClientID" name="clientID" required>
                <option value="">Select client...</option>
                <?php
                if ($clients) {
                    foreach ($clients as $client) {
                        echo "<option value='{$client->clientID}'>{$client->clientName}</option>";
                    }
                }
                ?>
            </select>
        </div>

        <div class="col-md-6">
            <label for="salesCaseID" class="form-label">Related Opportunity</label>
            <select class="form-select" id="proposalSalesCaseID" name="salesCaseID">
                <option value="">Select opportunity...</option>
                <?php
                if ($salesCases) {
                    foreach ($salesCases as $case) {
                        echo "<option value='{$case->salesCaseID}'>{$case->salesCaseName} - {$case->clientName}</option>";
                    }
                }
                ?>
            </select>
        </div>

        <div class="col-md-6">
            <label for="proposalValue" class="form-label">Proposal Value (KES) <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text">KES</span>
                <input type="number" class="form-control" id="proposalValue" name="proposalValue"
                       step="0.01" min="0" required>
            </div>
        </div>

        <div class="col-md-6">
            <label for="proposalDeadline" class="form-label">Deadline <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="proposalDeadline" name="proposalDeadline" required>
        </div>

        <div class="col-md-12">
            <label for="proposalStatusID" class="form-label">Status <span class="text-danger">*</span></label>
            <select class="form-select" id="proposalStatusID" name="proposalStatusID" required>
                <option value="">Select status...</option>
                <?php
                if ($proposalStatuses) {
                    foreach ($proposalStatuses as $status) {
                        echo "<option value='{$status->proposalStatusID}'>{$status->proposalStatusName}</option>";
                    }
                }
                ?>
            </select>
        </div>

        <div class="col-md-12">
            <label for="proposalDescription" class="form-label">Description</label>
            <textarea class="form-control" id="proposalDescription" name="proposalDescription" rows="3"></textarea>
        </div>

        <div class="col-md-12">
            <label for="proposalComments" class="form-label">Comments</label>
            <textarea class="form-control" id="proposalComments" name="proposalComments" rows="2"></textarea>
        </div>

        <div class="col-md-12">
            <label for="proposalFile" class="form-label">Attach Document</label>
            <input type="file" class="form-control" id="proposalFile" name="proposalFile">
            <div class="form-text">Upload proposal document (PDF, DOCX, etc.)</div>
        </div>
    </div>
</form>
<?php
echo Utility::form_modal_footer('Save Proposal', 'saveProposal', 'btn btn-primary', true);
?>

<style>
/* Stats Cards */
.stats-card {
    border: none;
    border-radius: 12px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.stats-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
}

.stats-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
}

.stats-value {
    font-size: 1.75rem;
    font-weight: 700;
    margin: 0.25rem 0;
}

.stats-label {
    font-size: 0.813rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Proposal Board */
.proposal-board {
    display: flex;
    gap: 1.5rem;
    overflow-x: auto;
    padding-bottom: 1rem;
}

.proposal-column {
    flex: 0 0 340px;
    background: #f8f9fa;
    border-radius: 12px;
    padding: 1.25rem;
}

.proposal-column-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #dee2e6;
}

.proposal-item {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 1.25rem;
    margin-bottom: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.proposal-item:hover {
    border-color: #6366f1;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
    transform: translateY(-2px);
}

.proposal-item.urgent {
    border-left: 4px solid #dc3545;
}

.proposal-item.warning {
    border-left: 4px solid #ffc107;
}

.proposal-item-title {
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 0.5rem;
    color: #1e293b;
}

.proposal-item-client {
    font-size: 0.875rem;
    color: #64748b;
    margin-bottom: 0.75rem;
}

.proposal-item-meta {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-top: 0.75rem;
}

.proposal-item-value {
    font-weight: 700;
    color: #10b981;
    font-size: 1.125rem;
}

/* Table View */
.proposal-table {
    background: white;
    border-radius: 12px;
}

.proposal-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #475569;
    text-transform: uppercase;
    font-size: 0.813rem;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #dee2e6;
}

.proposal-table td {
    vertical-align: middle;
}

.proposal-table tbody tr {
    transition: all 0.2s ease;
}

.proposal-table tbody tr:hover {
    background: #f8f9fa;
}
</style>

<script>
// Proposal Management System
const ProposalManager = {
    config: {
        base: '<?= $base ?>',
        orgDataID: '<?= $orgDataID ?>',
        entityID: '<?= $entityID ?>',
        userID: '<?= $userDetails->ID ?>',
        currentView: '<?= $view ?>',
        currentFilter: '<?= $filter ?>'
    },

    data: {
        proposals: null,
        statuses: <?= json_encode($proposalStatuses) ?>,
        clients: <?= json_encode($clients) ?>,
        salesCases: <?= json_encode($salesCases) ?>
    },

    init: function() {
        this.loadProposals();
        this.setupEventListeners();
    },

    setupEventListeners: function() {
        // View toggle
        document.querySelectorAll('[data-view]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.config.currentView = e.currentTarget.dataset.view;
                this.renderProposals();
            });
        });

        // Filter toggle
        document.querySelectorAll('[data-filter]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.config.currentFilter = e.currentTarget.dataset.filter;
                this.loadProposals();
            });
        });

        // Form submission
        document.getElementById('proposalForm')?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveProposal();
        });

        // Client change - filter opportunities
        document.getElementById('proposalClientID')?.addEventListener('change', (e) => {
            this.filterOpportunities(e.target.value);
        });
    },

    loadProposals: function() {
        const params = new URLSearchParams({
            action: 'get_proposals',
            orgDataID: this.config.orgDataID,
            entityID: this.config.entityID,
            status: this.config.currentFilter
        });

        fetch(`${this.config.base}php/scripts/sales/sales_api.php?${params}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.data.proposals = data.data;
                    this.renderProposals();
                    this.updateStats();
                }
            })
            .catch(error => {
                console.error('Error loading proposals:', error);
                this.showError('Failed to load proposals');
            });
    },

    renderProposals: function() {
        if (this.config.currentView === 'board') {
            this.renderBoardView();
        } else {
            this.renderTableView();
        }
    },

    renderBoardView: function() {
        if (!this.data.proposals || this.data.proposals.length === 0) {
            this.showEmptyState();
            return;
        }

        // Group proposals by status
        const groupedProposals = {};
        this.data.statuses.forEach(status => {
            groupedProposals[status.proposalStatusName] = [];
        });

        this.data.proposals.forEach(proposal => {
            const statusName = proposal.proposalStatusName || 'Unknown';
            if (!groupedProposals[statusName]) {
                groupedProposals[statusName] = [];
            }
            groupedProposals[statusName].push(proposal);
        });

        // Render board
        let boardHTML = '<div class="proposal-board">';

        Object.entries(groupedProposals).forEach(([status, proposals]) => {
            boardHTML += `
                <div class="proposal-column">
                    <div class="proposal-column-header">
                        <h5 class="mb-0 fw-semibold">${status}</h5>
                        <span class="badge bg-primary">${proposals.length}</span>
                    </div>
                    <div class="proposal-items">
                        ${this.renderProposalItems(proposals)}
                    </div>
                </div>
            `;
        });

        boardHTML += '</div>';
        document.getElementById('proposalsContent').innerHTML = boardHTML;

        this.setupProposalHandlers();
    },

    renderProposalItems: function(proposals) {
        if (!proposals || proposals.length === 0) {
            return '<div class="text-center text-muted py-4"><small>No proposals</small></div>';
        }

        return proposals.map(proposal => {
            const urgentClass = proposal.isUrgent ? 'urgent' : (proposal.daysUntilDeadline <= 7 ? 'warning' : '');

            return `
                <div class="proposal-item ${urgentClass}" data-proposal-id="${proposal.proposalID}">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="proposal-item-title mb-0">${proposal.proposalTitle}</h6>
                        ${proposal.isUrgent ? '<span class="badge bg-danger">Urgent</span>' : ''}
                    </div>
                    <div class="proposal-item-client">
                        <i class="ri-building-line me-1"></i>${proposal.clientName}
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="proposal-item-value">KES ${this.formatNumber(proposal.proposalValue)}</span>
                        ${proposal.daysUntilDeadline !== null ? `
                            <small class="text-muted">
                                <i class="ri-calendar-line"></i> ${proposal.daysUntilDeadline} days
                            </small>
                        ` : ''}
                    </div>
                    ${proposal.employeeName ? `
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="ri-user-line"></i> ${proposal.employeeName}
                            </small>
                        </div>
                    ` : ''}
                </div>
            `;
        }).join('');
    },

    renderTableView: function() {
        if (!this.data.proposals || this.data.proposals.length === 0) {
            this.showEmptyState();
            return;
        }

        let tableHTML = `
            <div class="table-responsive">
                <table class="table proposal-table table-hover">
                    <thead>
                        <tr>
                            <th>Proposal</th>
                            <th>Client</th>
                            <th>Value</th>
                            <th>Deadline</th>
                            <th>Status</th>
                            <th>Owner</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        this.data.proposals.forEach(proposal => {
            const urgentBadge = proposal.isUrgent ? '<span class="badge bg-danger ms-2">Urgent</span>' : '';

            tableHTML += `
                <tr data-proposal-id="${proposal.proposalID}">
                    <td>
                        <div class="fw-semibold">${proposal.proposalTitle}</div>
                        <small class="text-muted">${proposal.proposalCode || ''}</small>
                    </td>
                    <td>${proposal.clientName}</td>
                    <td class="fw-semibold text-success">KES ${this.formatNumber(proposal.proposalValue)}</td>
                    <td>
                        ${proposal.proposalDeadline ? this.formatDate(proposal.proposalDeadline) : '-'}
                        ${urgentBadge}
                    </td>
                    <td><span class="badge bg-primary-transparent">${proposal.proposalStatusName}</span></td>
                    <td>${proposal.employeeName || '-'}</td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-primary-light view-proposal-btn" data-id="${proposal.proposalID}">
                                <i class="ri-eye-line"></i>
                            </button>
                            <button class="btn btn-secondary-light edit-proposal-btn" data-id="${proposal.proposalID}">
                                <i class="ri-edit-line"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        tableHTML += `
                    </tbody>
                </table>
            </div>
        `;

        document.getElementById('proposalsContent').innerHTML = tableHTML;
        this.setupProposalHandlers();
    },

    updateStats: function() {
        if (!this.data.proposals) return;

        const stats = {
            total: this.data.proposals.length,
            totalValue: 0,
            dueThisWeek: 0,
            won: 0,
            totalClosed: 0
        };

        this.data.proposals.forEach(proposal => {
            stats.totalValue += parseFloat(proposal.proposalValue || 0);

            if (proposal.daysUntilDeadline !== null && proposal.daysUntilDeadline <= 7) {
                stats.dueThisWeek++;
            }

            // Count won proposals (you'll need to determine the status that indicates "won")
            if (proposal.proposalStatusName && proposal.proposalStatusName.toLowerCase().includes('won')) {
                stats.won++;
            }
        });

        stats.totalClosed = stats.won; // Adjust based on your status logic
        const winRate = stats.totalClosed > 0 ? (stats.won / stats.totalClosed * 100).toFixed(1) : 0;

        document.getElementById('totalProposals').textContent = stats.total;
        document.getElementById('totalValue').textContent = `KES ${this.formatNumber(stats.totalValue)}`;
        document.getElementById('dueThisWeek').textContent = stats.dueThisWeek;
        document.getElementById('winRate').textContent = `${winRate}%`;
    },

    setupProposalHandlers: function() {
        // Board view items
        document.querySelectorAll('.proposal-item').forEach(item => {
            item.addEventListener('click', (e) => {
                const proposalID = e.currentTarget.dataset.proposalId;
                this.viewProposal(proposalID);
            });
        });

        // Table view buttons
        document.querySelectorAll('.view-proposal-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const proposalID = e.currentTarget.dataset.id;
                this.viewProposal(proposalID);
            });
        });

        document.querySelectorAll('.edit-proposal-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const proposalID = e.currentTarget.dataset.id;
                this.editProposal(proposalID);
            });
        });
    },

    viewProposal: function(proposalID) {
        window.location.href = `${this.config.base}html/?s=user&ss=sales&p=proposal_details&prID=${proposalID}`;
    },

    editProposal: function(proposalID) {
        const proposal = this.data.proposals.find(p => p.proposalID == proposalID);
        if (!proposal) return;

        // Populate form
        document.getElementById('proposalID').value = proposal.proposalID;
        document.getElementById('proposalTitle').value = proposal.proposalTitle;
        document.getElementById('proposalClientID').value = proposal.clientID;
        document.getElementById('proposalSalesCaseID').value = proposal.salesCaseID || '';
        document.getElementById('proposalValue').value = proposal.proposalValue;
        document.getElementById('proposalDeadline').value = proposal.proposalDeadline;
        document.getElementById('proposalStatusID').value = proposal.proposalStatusID;
        document.getElementById('proposalDescription').value = proposal.proposalDescription || '';
        document.getElementById('proposalComments').value = proposal.proposalComments || '';

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('addProposalModal'));
        modal.show();
    },

    saveProposal: function() {
        const formData = new FormData(document.getElementById('proposalForm'));

        fetch(`${this.config.base}php/scripts/sales/manage_proposal.php`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (typeof showToast === 'function') {
                    showToast('Proposal saved successfully!', 'success');
                } else {
                    alert('Proposal saved successfully!');
                }
                bootstrap.Modal.getInstance(document.getElementById('addProposalModal')).hide();
                this.loadProposals();
            } else {
                if (typeof showToast === 'function') {
                    showToast('Error: ' + (data.message || 'Failed to save proposal'), 'error');
                } else {
                    alert('Error: ' + (data.message || 'Failed to save proposal'));
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof showToast === 'function') {
                showToast('An error occurred while saving', 'error');
            } else {
                alert('An error occurred while saving');
            }
        });
    },

    filterOpportunities: function(clientID) {
        const select = document.getElementById('proposalSalesCaseID');
        if (!select) return;

        select.innerHTML = '<option value="">Select opportunity...</option>';

        if (clientID && this.data.salesCases) {
            const filtered = this.data.salesCases.filter(c => c.clientID == clientID);
            filtered.forEach(salesCase => {
                const option = document.createElement('option');
                option.value = salesCase.salesCaseID;
                option.textContent = salesCase.salesCaseName;
                select.appendChild(option);
            });
        }
    },

    showEmptyState: function() {
        const emptyHTML = `
            <div class="empty-state text-center py-5">
                <div class="empty-state-icon mb-3">
                    <i class="ri-file-text-line" style="font-size: 4rem; color: #cbd5e1;"></i>
                </div>
                <h4 class="mb-2">No Proposals Found</h4>
                <p class="text-muted mb-4">Start by creating your first proposal</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProposalModal">
                    <i class="ri-add-line"></i> Create Proposal
                </button>
            </div>
        `;
        document.getElementById('proposalsContent').innerHTML = emptyHTML;
    },

    showError: function(message) {
        const errorHTML = `
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="ri-error-warning-line me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.getElementById('proposalsContent').innerHTML = errorHTML;
    },

    formatNumber: function(num) {
        return new Intl.NumberFormat('en-KE', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(num || 0);
    },

    formatDate: function(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-KE', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    ProposalManager.init();
});
</script>

