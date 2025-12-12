<?php
if(!$isValidUser) {
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}

// Get prospect ID
$prospectID = isset($_GET['prospectID']) ? (int)$_GET['prospectID'] : 0;

if (!$prospectID) {
    Alert::danger("Invalid prospect ID", true);
    return;
}

// Get prospect details
$prospect = Sales::sales_prospect_full($prospectID, $DBConn);

if (!$prospect) {
    Alert::danger("Prospect not found", true);
    return;
}

// Get related data
$interactions = Sales::prospect_interactions($prospectID, array(), $DBConn);
// Note: Activities are tracked via prospect_interactions, not tija_sales_activities
$activities = array(); // Initialize as empty array for tab count

// Get user context
$employeeID = $userDetails->ID;
$orgDataID = $prospect->orgDataID;
$entityID = $prospect->entityID;

// Get dropdown data for editing
$businessUnits = Data::business_units(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
$leadSources = Data::lead_sources([], false, $DBConn);
$teams = Sales::prospect_teams(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID, 'isActive'=>'Y'), false, $DBConn);
$territories = Sales::prospect_territories(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID, 'isActive'=>'Y'), false, $DBConn);
$industries = Sales::prospect_industries(array('isActive'=>'Y'), false, $DBConn);
$allEmployees = Employee::employees([], false, $DBConn);

// Calculate BANT score
$bantScore = 0;
if ($prospect->budgetConfirmed == 'Y') $bantScore += 25;
if ($prospect->decisionMakerIdentified == 'Y') $bantScore += 25;
if ($prospect->needIdentified == 'Y') $bantScore += 25;
if ($prospect->timelineDefined == 'Y') $bantScore += 25;

?>

<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <div>
        <h1 class="page-title fw-medium fs-24 mb-0">Prospect Details
            <span class="badge bg-<?= $prospect->salesProspectStatus == 'open' ? 'success' : 'secondary' ?>-transparent ms-2">
                <?= ucfirst($prospect->salesProspectStatus) ?>
            </span>
        </h1>
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= "{$base}html/?s=user&ss=sales&p=home" ?>">Sales</a></li>
                <li class="breadcrumb-item"><a href="<?= "{$base}html/?s=user&ss=sales&p=prospects" ?>">Prospects</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($prospect->salesProspectName) ?></li>
            </ol>
        </nav>
    </div>
    <div class="ms-md-1 ms-0 mt-md-0 mt-2">
        <button type="button" class="btn btn-primary btn-wave" id="editProspectBtn">
            <i class="ri-edit-line me-1"></i> Edit Prospect
        </button>
        <?php if ($prospect->leadQualificationStatus === 'qualified' && ($prospect->convertedToSale ?? 'N') !== 'Y' && ($prospect->Suspended ?? 'N') !== 'Y'): ?>
        <button type="button" class="btn btn-success btn-wave" id="convertToSaleBtn" title="Convert this qualified prospect to a sales opportunity">
            <i class="fas fa-exchange-alt me-1"></i> Convert to Sale
        </button>
        <?php endif; ?>
        <button type="button" class="btn btn-outline-primary btn-wave ms-2" data-bs-toggle="modal" data-bs-target="#prospectHelpModal" title="Help & Documentation">
            <i class="ri-question-line me-1"></i> Help
        </button>
        <button type="button" class="btn btn-success btn-wave" data-bs-toggle="modal" data-bs-target="#logInteractionModal">
            <i class="ri-chat-3-line me-1"></i> Log Interaction
        </button>
        <button type="button" class="btn btn-danger btn-wave" id="deleteProspectBtn">
            <i class="ri-delete-bin-line me-1"></i> Delete
        </button>
    </div>
</div>

<!-- Prospect Overview Cards -->
<div class="row">
    <!-- Lead Score Card -->
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top">
                    <div class="me-3">
                        <span class="avatar avatar-md avatar-rounded bg-primary">
                            <i class="ri-line-chart-line fs-18"></i>
                        </span>
                    </div>
                    <div class="flex-fill">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="d-block text-muted">Lead Score</span>
                            <span class="badge bg-<?= $prospect->leadScore >= 70 ? 'success' : ($prospect->leadScore >= 40 ? 'warning' : 'danger') ?>-transparent">
                                <?= $prospect->leadScore ?>/100
                            </span>
                        </div>
                        <div class="progress progress-sm mb-2">
                            <div class="progress-bar bg-<?= $prospect->leadScore >= 70 ? 'success' : ($prospect->leadScore >= 40 ? 'warning' : 'danger') ?>"
                                 style="width: <?= $prospect->leadScore ?>%"></div>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary-light" id="recalculateScoreBtn">
                            <i class="ri-refresh-line me-1"></i> Recalculate
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estimated Value Card -->
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top">
                    <div class="me-3">
                        <span class="avatar avatar-md avatar-rounded bg-success">
                            <i class="ri-money-dollar-circle-line fs-18"></i>
                        </span>
                    </div>
                    <div class="flex-fill">
                        <span class="d-block text-muted mb-1">Estimated Value</span>
                        <h4 class="fw-semibold mb-1"><?= number_format($prospect->estimatedValue ?? 0, 2) ?></h4>
                        <span class="text-muted fs-12">Probability: <?= $prospect->probability ?? 0 ?>%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Days in Pipeline Card -->
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top">
                    <div class="me-3">
                        <span class="avatar avatar-md avatar-rounded bg-warning">
                            <i class="ri-time-line fs-18"></i>
                        </span>
                    </div>
                    <div class="flex-fill">
                        <span class="d-block text-muted mb-1">Days in Pipeline</span>
                        <h4 class="fw-semibold mb-1"><?= $prospect->daysInPipeline ?></h4>
                        <span class="text-muted fs-12">Added: <?= date('M d, Y', strtotime($prospect->DateAdded)) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BANT Score Card -->
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-top">
                    <div class="me-3">
                        <span class="avatar avatar-md avatar-rounded bg-info">
                            <i class="ri-checkbox-multiple-line fs-18"></i>
                        </span>
                    </div>
                    <div class="flex-fill">
                        <span class="d-block text-muted mb-1">BANT Score</span>
                        <h4 class="fw-semibold mb-1"><?= $bantScore ?>%</h4>
                        <div class="d-flex gap-1 mt-2">
                            <span class="badge bg-<?= $prospect->budgetConfirmed == 'Y' ? 'success' : 'secondary' ?>-transparent" title="Budget">B</span>
                            <span class="badge bg-<?= $prospect->decisionMakerIdentified == 'Y' ? 'success' : 'secondary' ?>-transparent" title="Authority">A</span>
                            <span class="badge bg-<?= $prospect->needIdentified == 'Y' ? 'success' : 'secondary' ?>-transparent" title="Need">N</span>
                            <span class="badge bg-<?= $prospect->timelineDefined == 'Y' ? 'success' : 'secondary' ?>-transparent" title="Timeline">T</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Tabs -->
<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header">
                <ul class="nav nav-tabs nav-tabs-header mb-0" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" role="tab" href="#overview-tab" aria-selected="true">
                            <i class="ri-user-line me-1"></i>Overview
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" role="tab" href="#interactions-tab" aria-selected="false">
                            <i class="ri-chat-3-line me-1"></i>Interactions
                            <span class="badge bg-primary ms-1"><?= $interactions ? count($interactions) : 0 ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" role="tab" href="#notes-tab" aria-selected="false">
                            <i class="ri-sticky-note-line me-1"></i>Notes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" role="tab" href="#history-tab" aria-selected="false">
                            <i class="ri-history-line me-1"></i>History
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Overview Tab -->
                    <div class="tab-pane show active text-muted" id="overview-tab" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-semibold mb-3">Contact Information</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="text-muted" width="40%">Prospect Name:</td>
                                        <td class="fw-semibold"><?= htmlspecialchars($prospect->salesProspectName) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Case Name:</td>
                                        <td class="fw-semibold"><?= htmlspecialchars($prospect->prospectCaseName) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Email:</td>
                                        <td><a href="mailto:<?= $prospect->prospectEmail ?>"><?= htmlspecialchars($prospect->prospectEmail ?? '-') ?></a></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Phone:</td>
                                        <td><?= htmlspecialchars($prospect->prospectPhone ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Website:</td>
                                        <td>
                                            <?php if ($prospect->prospectWebsite): ?>
                                                <a href="<?= htmlspecialchars($prospect->prospectWebsite) ?>" target="_blank"><?= htmlspecialchars($prospect->prospectWebsite) ?></a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Address:</td>
                                        <td><?= nl2br(htmlspecialchars($prospect->address ?? '-')) ?></td>
                                    </tr>
                                </table>
                            </div>

                            <div class="col-md-6">
                                <h6 class="fw-semibold mb-3">Classification & Assignment</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="text-muted" width="40%">Business Unit:</td>
                                        <td class="fw-semibold"><?= htmlspecialchars($prospect->businessUnitName ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Lead Source:</td>
                                        <td><?= htmlspecialchars($prospect->leadSourceName ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Industry:</td>
                                        <td><?= htmlspecialchars($prospect->industryName ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Company Size:</td>
                                        <td><?= ucfirst($prospect->companySize ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Assigned Team:</td>
                                        <td><?= htmlspecialchars($prospect->teamName ?? 'Unassigned') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Territory:</td>
                                        <td><?= htmlspecialchars($prospect->territoryName ?? '-') ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Owner:</td>
                                        <td><?= htmlspecialchars($prospect->ownerName ?? '-') ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-semibold mb-3">Sales Information</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="text-muted" width="40%">Qualification Status:</td>
                                        <td>
                                            <?php
                                            $qualBadge = array(
                                                'unqualified' => 'secondary',
                                                'cold' => 'info',
                                                'warm' => 'warning',
                                                'hot' => 'danger',
                                                'qualified' => 'success'
                                            );
                                            $badgeColor = $qualBadge[$prospect->leadQualificationStatus] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?= $badgeColor ?>-transparent">
                                                <?= ucfirst($prospect->leadQualificationStatus) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Expected Close Date:</td>
                                        <td><?= $prospect->expectedCloseDate ? date('M d, Y', strtotime($prospect->expectedCloseDate)) : '-' ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Last Contact:</td>
                                        <td><?= $prospect->lastContactDate ? date('M d, Y', strtotime($prospect->lastContactDate)) : 'Never' ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Next Follow-up:</td>
                                        <td>
                                            <?php if ($prospect->nextFollowUpDate): ?>
                                                <?php
                                                $followUpStatus = 'Scheduled';
                                                $badgeClass = 'info';
                                                if (strtotime($prospect->nextFollowUpDate) < time()) {
                                                    $followUpStatus = 'Overdue';
                                                    $badgeClass = 'danger';
                                                } elseif (date('Y-m-d', strtotime($prospect->nextFollowUpDate)) == date('Y-m-d')) {
                                                    $followUpStatus = 'Due Today';
                                                    $badgeClass = 'warning';
                                                }
                                                ?>
                                                <span class="badge bg-<?= $badgeClass ?>-transparent">
                                                    <?= date('M d, Y', strtotime($prospect->nextFollowUpDate)) ?> (<?= $followUpStatus ?>)
                                                </span>
                                            <?php else: ?>
                                                Not scheduled
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <div class="col-md-6">
                                <h6 class="fw-semibold mb-3">BANT Qualification</h6>
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="text-muted" width="40%">Budget Confirmed:</td>
                                        <td>
                                            <span class="badge bg-<?= $prospect->budgetConfirmed == 'Y' ? 'success' : 'secondary' ?>-transparent">
                                                <?= $prospect->budgetConfirmed == 'Y' ? 'Yes' : 'No' ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Decision Maker Identified:</td>
                                        <td>
                                            <span class="badge bg-<?= $prospect->decisionMakerIdentified == 'Y' ? 'success' : 'secondary' ?>-transparent">
                                                <?= $prospect->decisionMakerIdentified == 'Y' ? 'Yes' : 'No' ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Need Identified:</td>
                                        <td>
                                            <span class="badge bg-<?= $prospect->needIdentified == 'Y' ? 'success' : 'secondary' ?>-transparent">
                                                <?= $prospect->needIdentified == 'Y' ? 'Yes' : 'No' ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Timeline Defined:</td>
                                        <td>
                                            <span class="badge bg-<?= $prospect->timelineDefined == 'Y' ? 'success' : 'secondary' ?>-transparent">
                                                <?= $prospect->timelineDefined == 'Y' ? 'Yes' : 'No' ?>
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Interactions Tab -->
                    <div class="tab-pane text-muted" id="interactions-tab" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-semibold mb-0">Interaction Timeline</h6>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#logInteractionModal">
                                <i class="ri-add-line me-1"></i> Log Interaction
                            </button>
                        </div>

                        <?php if ($interactions && count($interactions) > 0): ?>
                            <div class="timeline">
                                <?php foreach ($interactions as $interaction): ?>
                                    <div class="timeline-item mb-4">
                                        <div class="d-flex">
                                            <div class="me-3">
                                                <span class="avatar avatar-sm avatar-rounded bg-<?=
                                                    $interaction->interactionType == 'call' ? 'primary' :
                                                    ($interaction->interactionType == 'email' ? 'info' :
                                                    ($interaction->interactionType == 'meeting' ? 'success' : 'secondary'))
                                                ?>">
                                                    <i class="ri-<?=
                                                        $interaction->interactionType == 'call' ? 'phone' :
                                                        ($interaction->interactionType == 'email' ? 'mail' :
                                                        ($interaction->interactionType == 'meeting' ? 'calendar' : 'chat-3'))
                                                    ?>-line"></i>
                                                </span>
                                            </div>
                                            <div class="flex-fill">
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <div>
                                                        <h6 class="fw-semibold mb-0"><?= htmlspecialchars($interaction->interactionSubject ?? ucfirst($interaction->interactionType)) ?></h6>
                                                        <span class="text-muted fs-12">
                                                            <?= htmlspecialchars($interaction->userName) ?> •
                                                            <?= date('M d, Y g:i A', strtotime($interaction->interactionDate)) ?>
                                                            <?php if ($interaction->duration): ?>
                                                                • <?= $interaction->duration ?> min
                                                            <?php endif; ?>
                                                        </span>
                                                    </div>
                                                    <?php if ($interaction->interactionOutcome): ?>
                                                        <span class="badge bg-<?=
                                                            $interaction->interactionOutcome == 'positive' ? 'success' :
                                                            ($interaction->interactionOutcome == 'negative' ? 'danger' :
                                                            ($interaction->interactionOutcome == 'no_response' ? 'warning' : 'secondary'))
                                                        ?>-transparent">
                                                            <?= ucfirst(str_replace('_', ' ', $interaction->interactionOutcome)) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if ($interaction->interactionDescription): ?>
                                                    <p class="mb-1"><?= nl2br(htmlspecialchars($interaction->interactionDescription)) ?></p>
                                                <?php endif; ?>
                                                <?php if ($interaction->nextSteps): ?>
                                                    <div class="alert alert-light mb-0 mt-2">
                                                        <strong>Next Steps:</strong> <?= nl2br(htmlspecialchars($interaction->nextSteps)) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="ri-chat-3-line fs-1 text-muted"></i>
                                <p class="text-muted mt-2">No interactions logged yet</p>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#logInteractionModal">
                                    Log First Interaction
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Activities Tab -->
                    <div class="tab-pane text-muted" id="activities-tab" role="tabpanel">
                        <p class="text-muted">Activities feature coming soon...</p>
                    </div>

                    <!-- Documents Tab -->
                    <div class="tab-pane text-muted" id="documents-tab" role="tabpanel">
                        <p class="text-muted">Documents feature coming soon...</p>
                    </div>

                    <!-- Notes Tab -->
                    <div class="tab-pane text-muted" id="notes-tab" role="tabpanel">
                        <!-- Add Note Form -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6 class="card-title mb-3">Add New Note</h6>
                                <form id="addNoteForm">
                                    <input type="hidden" name="action" value="addNote">
                                    <input type="hidden" name="salesProspectID" value="<?= $prospectID ?>">

                                    <div class="row">
                                        <div class="col-md-9 mb-3">
                                            <label class="form-label">Note Content <span class="text-danger">*</span></label>
                                            <textarea class="form-control" name="noteContent" rows="3" placeholder="Enter your note, guidance, or comment..." required></textarea>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Note Type</label>
                                            <select class="form-select" name="noteType">
                                                <option value="general">General</option>
                                                <option value="guidance">Guidance</option>
                                                <option value="warning">Warning</option>
                                                <option value="success">Success</option>
                                            </select>

                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" name="isPrivate" value="Y" id="isPrivateNote">
                                                <label class="form-check-label" for="isPrivateNote">
                                                    Private Note
                                                </label>
                                            </div>

                                            <div id="recipientSection" style="display: none;" class="mt-2">
                                                <label class="form-label">Send To</label>
                                                <select class="form-select form-select-sm" name="recipientID" id="recipientID">
                                                    <option value="">Select Recipient</option>
                                                    <?php
                                                    $employees = Employee::employees(array(), false, $DBConn);
                                                    if ($employees) {
                                                        foreach ($employees as $emp) {
                                                            if ($emp->ID != $userDetails->ID) { // Don't show current user
                                                                echo "<option value='{$emp->ID}'>{$emp->employeeName}</option>";
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                                <small class="text-muted">Only you and the recipient will see this note</small>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="ri-add-line me-1"></i>Add Note
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Notes List -->
                        <div id="notesList">
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- History Tab -->
                    <div class="tab-pane text-muted" id="history-tab" role="tabpanel">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Prospect Journey Timeline</h6>
                                <p class="text-muted mb-0 fs-12">Complete history of interactions and status changes</p>
                            </div>
                            <div class="card-body">
                                <div class="timeline-container">
                                    <?php
                                    // Get all interactions
                                    $timelineEvents = array();

                                    // Add interactions to timeline
                                    if ($interactions) {
                                        foreach ($interactions as $interaction) {
                                            $timelineEvents[] = array(
                                                'type' => 'interaction',
                                                'date' => $interaction->interactionDate,
                                                'title' => $interaction->interactionSubject ?? ucfirst($interaction->interactionType),
                                                'description' => $interaction->interactionDescription,
                                                'interactionType' => $interaction->interactionType,
                                                'outcome' => $interaction->interactionOutcome,
                                                'userName' => $interaction->userName,
                                                'duration' => $interaction->duration,
                                                'nextSteps' => $interaction->nextSteps
                                            );
                                        }
                                    }

                                    // Add prospect creation event
                                    $timelineEvents[] = array(
                                        'type' => 'created',
                                        'date' => $prospect->DateAdded,
                                        'title' => 'Prospect Created',
                                        'description' => 'Prospect was added to the system',
                                        'userName' => $prospect->createdByName ?? 'System'
                                    );

                                    // Add qualification status change if qualified
                                    if ($prospect->leadQualificationStatus === 'qualified') {
                                        $timelineEvents[] = array(
                                            'type' => 'qualified',
                                            'date' => $prospect->LastUpdate ?? $prospect->DateAdded,
                                            'title' => 'Prospect Qualified',
                                            'description' => 'Prospect reached qualified status',
                                            'userName' => $prospect->lastUpdatedByName ?? 'System'
                                        );
                                    }

                                    // Sort timeline events by date (newest first)
                                    usort($timelineEvents, function($a, $b) {
                                        return strtotime($b['date']) - strtotime($a['date']);
                                    });

                                    if (count($timelineEvents) > 0):
                                    ?>
                                        <div class="timeline">
                                            <?php foreach ($timelineEvents as $index => $event): ?>
                                                <div class="timeline-item">
                                                    <div class="timeline-marker
                                                        <?php
                                                        if ($event['type'] === 'qualified') echo 'bg-success';
                                                        elseif ($event['type'] === 'created') echo 'bg-primary';
                                                        elseif ($event['type'] === 'interaction') {
                                                            if ($event['outcome'] === 'positive') echo 'bg-success';
                                                            elseif ($event['outcome'] === 'negative') echo 'bg-danger';
                                                            else echo 'bg-info';
                                                        }
                                                        ?>">
                                                        <i class="ri-<?php
                                                            if ($event['type'] === 'qualified') echo 'check-double';
                                                            elseif ($event['type'] === 'created') echo 'add-circle';
                                                            elseif ($event['type'] === 'interaction') {
                                                                if ($event['interactionType'] === 'call') echo 'phone';
                                                                elseif ($event['interactionType'] === 'email') echo 'mail';
                                                                elseif ($event['interactionType'] === 'meeting') echo 'calendar';
                                                                else echo 'chat-3';
                                                            }
                                                        ?>-line"></i>
                                                    </div>
                                                    <div class="timeline-content">
                                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                                            <div>
                                                                <h6 class="mb-0"><?= htmlspecialchars($event['title']) ?></h6>
                                                                <small class="text-muted">
                                                                    <?= date('M d, Y g:i A', strtotime($event['date'])) ?>
                                                                    <?php if (isset($event['userName'])): ?>
                                                                        • by <?= htmlspecialchars($event['userName']) ?>
                                                                    <?php endif; ?>
                                                                    <?php if (isset($event['duration']) && $event['duration']): ?>
                                                                        • <?= $event['duration'] ?> min
                                                                    <?php endif; ?>
                                                                </small>
                                                            </div>
                                                            <?php if (isset($event['outcome']) && $event['outcome']): ?>
                                                                <span class="badge bg-<?php
                                                                    echo $event['outcome'] === 'positive' ? 'success' :
                                                                         ($event['outcome'] === 'negative' ? 'danger' :
                                                                         ($event['outcome'] === 'no_response' ? 'warning' : 'secondary'));
                                                                ?>-transparent">
                                                                    <?= ucfirst(str_replace('_', ' ', $event['outcome'])) ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <?php if (isset($event['description']) && $event['description']): ?>
                                                            <p class="mb-1 text-muted"><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                                                        <?php endif; ?>
                                                        <?php if (isset($event['nextSteps']) && $event['nextSteps']): ?>
                                                            <div class="alert alert-light mb-0 mt-2">
                                                                <strong>Next Steps:</strong> <?= nl2br(htmlspecialchars($event['nextSteps'])) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="ri-history-line fs-1 text-muted"></i>
                                            <p class="text-muted mt-2">No history available yet</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const prospectID = <?= $prospectID ?>;

    // ===== URL-BASED TAB STATE PERSISTENCE =====
    function activateTabFromUrl() {
        // Get hash from URL (e.g., #interactions-tab)
        let hash = window.location.hash;

        // If no hash or invalid hash, default to overview
        if (!hash || !document.querySelector(hash)) {
            hash = '#overview-tab';
        }

        // Remove active class from all tabs
        document.querySelectorAll('.nav-tabs .nav-link').forEach(tab => {
            tab.classList.remove('active');
            tab.setAttribute('aria-selected', 'false');
        });
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.classList.remove('show', 'active');
        });

        // Activate the tab from URL
        const tabLink = document.querySelector(`.nav-tabs .nav-link[href="${hash}"]`);
        const tabPane = document.querySelector(hash);

        if (tabLink && tabPane) {
            tabLink.classList.add('active');
            tabLink.setAttribute('aria-selected', 'true');
            tabPane.classList.add('show', 'active');
        }
    }

    // Activate tab on page load
    activateTabFromUrl();

    // Update URL hash when tab is clicked
    document.querySelectorAll('.nav-tabs .nav-link').forEach(tab => {
        tab.addEventListener('click', function(e) {
            const tabId = this.getAttribute('href');
            // Update URL without scrolling
            history.pushState(null, null, tabId);
        });
    });

    // Handle browser back/forward buttons
    window.addEventListener('hashchange', function() {
        activateTabFromUrl();
    });
    // ===== END URL-BASED TAB STATE PERSISTENCE =====

    // ===== NOTES LOADING =====
    function loadNotes() {
        const notesList = document.getElementById('notesList');
        notesList.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

        fetch('<?= "{$base}php/scripts/sales/manage_prospect_notes.php" ?>?action=getNotes&salesProspectID=' + prospectID)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.notes && data.notes.length > 0) {
                    let notesHTML = '';
                    data.notes.forEach(note => {
                        const noteDate = new Date(note.DateAdded).toLocaleString();
                        const isPrivate = note.isPrivate === 'Y';

                        notesHTML += `
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">${note.addedByName || 'Unknown User'}</h6>
                                            <small class="text-muted">${noteDate}</small>
                                        </div>
                                        <div>
                                            ${isPrivate ? '<span class="badge bg-warning"><i class="ri-lock-line me-1"></i>Private</span>' : '<span class="badge bg-success"><i class="ri-team-line me-1"></i>Team</span>'}
                                        </div>
                                    </div>
                                    <p class="mb-0">${note.noteText || ''}</p>
                                    ${note.recipients && note.recipients.length > 0 ? `
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="ri-user-line me-1"></i>Shared with: ${note.recipients.map(r => r.name).join(', ')}
                                            </small>
                                        </div>
                                    ` : ''}
                                </div>
                            </div>
                        `;
                    });
                    notesList.innerHTML = notesHTML;
                } else {
                    notesList.innerHTML = '<div class="alert alert-info"><i class="ri-information-line me-2"></i>No notes yet. Add your first note above!</div>';
                }
            })
            .catch(error => {
                console.error('Error loading notes:', error);
                notesList.innerHTML = '<div class="alert alert-danger"><i class="ri-error-warning-line me-2"></i>Error loading notes. Please try again.</div>';
            });
    }

    // Load notes when Notes tab is shown
    const notesTabLink = document.querySelector('.nav-link[href="#notes-tab"]');
    if (notesTabLink) {
        notesTabLink.addEventListener('shown.bs.tab', function() {
            loadNotes();
        });

        // Load notes if Notes tab is active on page load
        if (window.location.hash === '#notes-tab') {
            loadNotes();
        }
    }
    // ===== END NOTES LOADING =====

    // Recalculate score
    const recalculateScoreBtn = document.getElementById('recalculateScoreBtn');
    if (recalculateScoreBtn) {
        recalculateScoreBtn.addEventListener('click', function() {
            fetch('<?= "{$base}php/scripts/sales/manage_prospect_advanced.php" ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=calculateScore&salesProspectID=' + prospectID
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
        });
    }

    // Log interaction
    const saveInteractionBtn = document.getElementById('saveInteractionBtn');
    if (saveInteractionBtn) {
        saveInteractionBtn.addEventListener('click', function() {
        const form = document.getElementById('logInteractionForm');
        const formData = new FormData(form);

        // Add action field
        formData.append('action', 'logInteraction');
        formData.append('salesProspectID', prospectID);

        // DEBUG: Log what we're sending
        console.log('=== LOG INTERACTION CLIENT DEBUG ===');
        console.log('Prospect ID:', prospectID);
        console.log('Form Data:');
        for (let [key, value] of formData.entries()) {
            console.log(`  ${key}: ${value}`);
        }
        console.log('====================================');

        fetch('<?= "{$base}php/scripts/sales/manage_prospect_advanced.php" ?>', {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
        .then(response => response.json())
        .then(data => {
            console.log('Server response:', data);
            if (data.success) {
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while logging the interaction');
        });
        });
    }

    // Delete prospect
    const deleteProspectBtn = document.getElementById('deleteProspectBtn');
    if (deleteProspectBtn) {
        deleteProspectBtn.addEventListener('click', function() {
        if (confirm('Are you sure you want to delete this prospect?')) {
            fetch('<?= "{$base}php/scripts/sales/manage_prospect_advanced.php" ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=deleteProspect&salesProspectID=' + prospectID
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '<?= "{$base}html/?s=user&ss=sales&p=prospects" ?>';
                } else {
                    alert(data.message);
                }
            });
        }
        });
    }

    // Edit prospect button
    const editProspectBtn = document.getElementById('editProspectBtn');
    if (editProspectBtn) {
        editProspectBtn.addEventListener('click', function() {
        if (typeof loadProspectForEdit === 'function') {
            loadProspectForEdit(prospectID);
        }
        });
    }

    // Convert to Sale button
    const convertToSaleBtn = document.getElementById('convertToSaleBtn');
    if (convertToSaleBtn) {
        convertToSaleBtn.addEventListener('click', function() {
            // Prepare prospect data for modal
            const prospectData = {
                salesProspectID: prospectID,
                salesProspectName: '<?= addslashes($prospect->salesProspectName) ?>',
                prospectCaseName: '<?= addslashes($prospect->prospectCaseName) ?>',
                estimatedValue: '<?= $prospect->estimatedValue ?>',
                probability: '<?= $prospect->probability ?>',
                expectedCloseDate: '<?= $prospect->expectedCloseDate ?>',
                leadQualificationStatus: '<?= $prospect->leadQualificationStatus ?>',
                ownerID: '<?= $prospect->ownerID ?>',
                assignedTeamID: '<?= $prospect->assignedTeamID ?>',
                entityID: '<?= $prospect->entityID ?>',
                clientID: '<?= $prospect->clientID ?>'
            };

            if (typeof loadProspectForConversion === 'function') {
                loadProspectForConversion(prospectData);
            }
        });
    }

    // ===== TAB STATE PERSISTENCE =====

    // Restore active tab from localStorage
    const savedTab = localStorage.getItem('prospectDetailsActiveTab');
    if (savedTab) {
        const tabElement = document.querySelector(`a[href="${savedTab}"]`);
        if (tabElement) {
            const tab = new bootstrap.Tab(tabElement);
            tab.show();
        }
    }

    // Save active tab to localStorage when tab is clicked
    document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(tabLink => {
        tabLink.addEventListener('shown.bs.tab', function(e) {
            const activeTabHref = e.target.getAttribute('href');
            localStorage.setItem('prospectDetailsActiveTab', activeTabHref);
        });
    });

    // ===== NOTES FUNCTIONALITY =====

    // Load notes when notes tab is clicked
    document.querySelector('a[href="#notes-tab"]').addEventListener('click', function() {
        loadNotes();
    });

    // Load notes initially if notes tab is active
    if (document.querySelector('#notes-tab').classList.contains('active')) {
        loadNotes();
    }

    // Toggle recipient section when private checkbox changes
    document.getElementById('isPrivateNote').addEventListener('change', function() {
        const recipientSection = document.getElementById('recipientSection');
        if (this.checked) {
            recipientSection.style.display = 'block';
        } else {
            recipientSection.style.display = 'none';
            document.getElementById('recipientID').value = '';
        }
    });

    // Add note form submission
    document.getElementById('addNoteForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('<?= "{$base}php/scripts/sales/prospect_notes.php" ?>', {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Note Added!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false,
                    didClose: () => {
                        // Hide page spinner using global function
                        if (typeof hideSpinner === 'function') {
                            hideSpinner();
                        }
                    }
                });
                this.reset();
                loadNotes();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to add note'
            });
        })
        .finally(() => {
            // Hide page loader if it exists
            if (typeof hideLoader === 'function') {
                hideLoader();
            }
            // Also try to hide any global loaders
            const loader = document.querySelector('.page-loader');
            if (loader) {
                loader.style.display = 'none';
            }
        });
    });

    // Load notes function
    function loadNotes() {
        const notesList = document.getElementById('notesList');
        notesList.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';

        const formData = new FormData();
        formData.append('action', 'getNotes');
        formData.append('salesProspectID', prospectID);

        fetch('<?= "{$base}php/scripts/sales/prospect_notes.php" ?>', {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayNotes(data.data);
            } else {
                notesList.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            notesList.innerHTML = '<div class="alert alert-danger">Failed to load notes</div>';
        });
    }

    // Display notes function
    function displayNotes(notes) {
        const notesList = document.getElementById('notesList');

        if (!notes || notes.length === 0) {
            notesList.innerHTML = `
                <div class="text-center py-5">
                    <i class="ri-sticky-note-line fs-1 text-muted"></i>
                    <p class="text-muted mt-2">No notes yet. Add your first note above!</p>
                </div>
            `;
            return;
        }

        let html = '';
        notes.forEach(note => {
            const noteTypeColors = {
                'general': 'secondary',
                'guidance': 'info',
                'warning': 'warning',
                'success': 'success'
            };
            const badgeColor = noteTypeColors[note.noteType] || 'secondary';

            html += `
                <div class="card mb-3 note-item" data-note-id="${note.prospectNoteID}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-1">${note.createdByName}</h6>
                                <small class="text-muted">
                                    ${new Date(note.DateAdded).toLocaleString()}
                                    ${note.isPrivate === 'Y' ? '<span class="badge bg-dark ms-1">Private</span>' : ''}
                                    ${note.recipientName ? '<span class="badge bg-primary ms-1"><i class="ri-mail-line"></i> To: ' + note.recipientName + '</span>' : ''}
                                    <span class="badge bg-${badgeColor} ms-1">${note.noteType}</span>
                                </small>
                            </div>
                            ${note.createdByID == <?= $userDetails->ID ?> ? `
                            <div class="btn-group">
                                <button class="btn btn-sm btn-light edit-note-btn" data-note-id="${note.prospectNoteID}">
                                    <i class="ri-edit-line"></i>
                                </button>
                                <button class="btn btn-sm btn-light text-danger delete-note-btn" data-note-id="${note.prospectNoteID}">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                            ` : ''}
                        </div>
                        <p class="mb-0 note-content">${note.noteContent.replace(/\n/g, '<br>')}</p>
                        ${note.LastUpdatedByID ? `<small class="text-muted">Edited by ${note.lastUpdatedByName}</small>` : ''}
                    </div>
                </div>
            `;
        });

        notesList.innerHTML = html;

        // Attach event listeners to edit and delete buttons
        document.querySelectorAll('.edit-note-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                editNote(this.dataset.noteId);
            });
        });

        document.querySelectorAll('.delete-note-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                deleteNote(this.dataset.noteId);
            });
        });
    }

    // Edit note function
    function editNote(noteId) {
        const noteCard = document.querySelector(`.note-item[data-note-id="${noteId}"]`);
        const noteContent = noteCard.querySelector('.note-content').innerText;

        Swal.fire({
            title: 'Edit Note',
            input: 'textarea',
            inputValue: noteContent,
            inputAttributes: {
                rows: 4
            },
            showCancelButton: true,
            confirmButtonText: 'Save',
            showLoaderOnConfirm: true,
            preConfirm: (content) => {
                const formData = new FormData();
                formData.append('action', 'editNote');
                formData.append('prospectNoteID', noteId);
                formData.append('noteContent', content);

                return fetch('<?= "{$base}php/scripts/sales/prospect_notes.php" ?>', {
                    method: 'POST',
                    body: new URLSearchParams(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message);
                    }
                    return data;
                })
                .catch(error => {
                    Swal.showValidationMessage(`Request failed: ${error}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Updated!', 'Note has been updated.', 'success');
                loadNotes();
            }
        });
    }

    // Delete note function
    function deleteNote(noteId) {
        Swal.fire({
            title: 'Delete Note?',
            text: 'This action cannot be undone',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'deleteNote');
                formData.append('prospectNoteID', noteId);

                fetch('<?= "{$base}php/scripts/sales/prospect_notes.php" ?>', {
                    method: 'POST',
                    body: new URLSearchParams(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Deleted!', data.message, 'success');
                        loadNotes();
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Failed to delete note', 'error');
                });
            }
        });
    }
});
</script>

<?php
// Include modals
include "includes/scripts/sales/modals/edit_prospect.php";
include "includes/scripts/sales/modals/log_interaction_modal.php";
include "includes/scripts/sales/modals/convert_to_sale_modal.php";
include "includes/scripts/sales/modals/prospect_help.php";
?>

<!-- SweetAlert2 Library -->
<link rel="stylesheet" href="<?= $base ?>assets/libs/sweetalert2/sweetalert2.min.css">
<script src="<?= $base ?>assets/libs/sweetalert2/sweetalert2.all.min.js"></script>

<script>
// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize Flatpickr for interaction date/time input
    if (typeof flatpickr !== 'undefined') {
        const interactionDateInput = document.querySelector('input[name="interactionDate"]');

        if (interactionDateInput) {
            flatpickr(interactionDateInput, {
                enableTime: false,
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'F j, Y',
                time_24hr: false,
                allowInput: true,
                defaultDate: new Date()
            });
        }
    }
});
</script>
