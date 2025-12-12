<?php
/**
 * Log Prospect Interaction Modal with BANT Qualification
 * Allows logging interactions and updating BANT criteria
 */
?>

<!-- Log Interaction Modal -->
<div class="modal fade" id="logInteractionModal" tabindex="-1" aria-labelledby="logInteractionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logInteractionModalLabel">
                    <i class="ri-chat-3-line me-2"></i>Log Prospect Interaction
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="logInteractionForm" method="POST" action="<?= "{$base}php/scripts/sales/manage_prospect_advanced.php" ?>">
                <input type="hidden" name="action" value="logInteraction">
                <input type="hidden" name="salesProspectID" value="<?= $prospectID ?>">

                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <!-- Interaction Details Section -->
                    <div class="mb-4">
                        <h6 class="fw-semibold mb-3 text-primary">
                            <i class="ri-information-line me-1"></i>Interaction Details
                        </h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="interactionType" class="form-label">Interaction Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="interactionType" name="interactionType" required>
                                    <option value="">Select type...</option>
                                    <option value="call">Phone Call</option>
                                    <option value="email">Email</option>
                                    <option value="meeting">Meeting</option>
                                    <option value="note">Note</option>
                                    <option value="task">Task</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="interactionDate" class="form-label">Interaction Date/Time</label>
                                <input type="datetime-local" class="form-control" id="interactionDate" name="interactionDate"
                                       value="<?= date('Y-m-d\TH:i') ?>">
                            </div>

                            <div class="col-md-8">
                                <label for="interactionSubject" class="form-label">Subject</label>
                                <input type="text" class="form-control" id="interactionSubject" name="interactionSubject"
                                       placeholder="Brief subject or title">
                            </div>


                            <div class="col-md-4">
                                <label for="duration" class="form-label">Duration (minutes)</label>
                                <input type="number" class="form-control" id="duration" name="duration" placeholder="e.g., 30">
                            </div>

                            <div class="col-12">
                                <label for="interactionDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="interactionDescription" name="interactionDescription"
                                          rows="4" placeholder="Detailed description of the interaction..."></textarea>
                            </div>

                            <div class="col-md-6">
                                <label for="interactionOutcome" class="form-label">Outcome</label>
                                <select class="form-select" id="interactionOutcome" name="interactionOutcome">
                                    <option value="">Select Outcome</option>
                                    <option value="positive">Positive</option>
                                    <option value="neutral">Neutral</option>
                                    <option value="negative">Negative</option>
                                    <option value="no_response">No Response</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="leadQualificationStatus" class="form-label">
                                    Update Qualification Status
                                    <i class="ri-information-line text-muted" data-bs-toggle="tooltip" title="Optionally update the prospect's qualification status based on this interaction"></i>
                                </label>
                                <select class="form-select" id="leadQualificationStatus" name="leadQualificationStatus">
                                    <option value="">Keep Current (<?= ucfirst($prospect->leadQualificationStatus) ?>)</option>
                                    <option value="unqualified">Unqualified</option>
                                    <option value="cold">Cold</option>
                                    <option value="warm">Warm</option>
                                    <option value="hot">Hot</option>
                                    <option value="qualified">Qualified</option>
                                </select>
                                <small class="text-muted">Change qualification based on this interaction</small>
                            </div>

                            <div class="col-md-12">
                                <label for="nextSteps" class="form-label">Next Steps</label>
                                <textarea class="form-control" id="nextSteps" name="nextSteps"
                                          rows="2" placeholder="Planned follow-up actions"></textarea>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- BANT Qualification Section -->
                    <div class="mb-3">
                        <h6 class="fw-semibold mb-3 text-success">
                            <i class="ri-checkbox-multiple-line me-1"></i>BANT Qualification
                            <small class="text-muted fw-normal">(Budget, Authority, Need, Timeline)</small>
                        </h6>

                        <!-- Budget -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="budgetConfirmed" name="budgetConfirmed" value="Y">
                                    <label class="form-check-label fw-semibold" for="budgetConfirmed">
                                        <i class="ri-money-dollar-circle-line text-success me-1"></i>Budget Confirmed
                                    </label>
                                </div>
                                <div id="budgetFields" style="display: none;">
                                    <label for="confirmedBudget" class="form-label">Budget Amount <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">KES</span>
                                        <input type="number" class="form-control" id="confirmedBudget" name="confirmedBudget"
                                               step="0.01" min="0" placeholder="0.00">
                                    </div>
                                    <small class="text-muted">Enter the confirmed budget amount</small>
                                </div>
                            </div>
                        </div>

                        <!-- Authority (Decision Maker) -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="decisionMakerConfirmed" name="decisionMakerConfirmed" value="Y">
                                    <label class="form-check-label fw-semibold" for="decisionMakerConfirmed">
                                        <i class="ri-user-star-line text-info me-1"></i>Decision Maker Confirmed
                                    </label>
                                </div>
                                <div id="decisionMakerFields" style="display: none;">
                                    <?php
                                    // Get client contacts
                                    $hasContacts = false;
                                    if ($prospect->clientID) {
                                        $contacts = Client::client_contact_full(array('clientID' => $prospect->clientID), false, $DBConn);
                                        $hasContacts = $contacts && count($contacts) > 0;
                                    }
                                    ?>

                                    <?php if ($hasContacts): ?>
                                        <label for="decisionMakerContactID" class="form-label">Select Decision Maker <span class="text-danger">*</span></label>
                                        <select class="form-select mb-2" id="decisionMakerContactID" name="decisionMakerContactID">
                                            <option value="">Select contact...</option>
                                            <option value="new">+ Create New Contact</option>
                                            <?php
                                            foreach ($contacts as $contact) {
                                                echo "<option value='{$contact->clientContactID}'>" .
                                                     htmlspecialchars($contact->contactName) .
                                                     " ({$contact->contactType})</option>";
                                            }
                                            ?>
                                        </select>
                                        <small class="text-muted">This contact will be marked as the decision maker</small>
                                    <?php else: ?>
                                        <input type="hidden" id="decisionMakerContactID" name="decisionMakerContactID" value="new">
                                        <div class="alert alert-info mb-2">
                                            <i class="ri-information-line me-1"></i>
                                            No contacts found. Please create a new contact below.
                                        </div>
                                    <?php endif; ?>

                                    <!-- New Contact Form (hidden by default if contacts exist) -->
                                    <div id="newContactFields" style="display: <?= $hasContacts ? 'none' : 'block' ?>;">
                                        <hr class="my-3">
                                        <h6 class="fw-semibold mb-3">New Contact Details</h6>

                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <label for="newContactName" class="form-label">Contact Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="newContactName" name="newContactName"
                                                       placeholder="Full name">
                                            </div>

                                            <div class="col-md-6">
                                                <label for="newContactEmail" class="form-label">Email <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control" id="newContactEmail" name="newContactEmail"
                                                       placeholder="email@example.com">
                                            </div>

                                            <div class="col-md-6">
                                                <label for="newContactPhone" class="form-label">Phone <span class="text-danger">*</span></label>
                                                <input type="tel" class="form-control" id="newContactPhone" name="newContactPhone"
                                                       placeholder="+254 700 000 000">
                                            </div>

                                            <div class="col-md-6">
                                                <label for="newContactType" class="form-label">Contact Type</label>
                                                <select class="form-select" id="newContactType" name="newContactType">
                                                    <?php
                                                    // Get contact types from database
                                                    $contactTypes = Client::contact_types(array('Suspended' => 'N'), false, $DBConn);
                                                    if ($contactTypes) {
                                                        foreach ($contactTypes as $type) {
                                                            $selected = $type->contactType === 'Primary' ? 'selected' : '';
                                                            echo "<option value='{$type->contactTypeID}' {$selected}>" .
                                                                 htmlspecialchars($type->contactType) .
                                                                 "</option>";
                                                        }
                                                    } else {
                                                        // Fallback if no types found
                                                        echo "<option value=''>No contact types available</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>

                                            <div class="col-12">
                                                <label for="newContactAddress" class="form-label">Address</label>
                                                <textarea class="form-control" id="newContactAddress" name="newContactAddress"
                                                          rows="2" placeholder="Street address, city, postal code"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Need -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="needIdentified" name="needIdentified" value="Y">
                                    <label class="form-check-label fw-semibold" for="needIdentified">
                                        <i class="ri-lightbulb-line text-warning me-1"></i>Need Identified
                                    </label>
                                </div>
                                <div id="needFields" style="display: none;">
                                    <label for="identifiedNeed" class="form-label">Describe the Need <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="identifiedNeed" name="identifiedNeed"
                                              rows="3" maxlength="1000" placeholder="What specific problem or need does the prospect have?"></textarea>
                                    <small class="text-muted"><span id="needCharCount">0</span>/1000 characters</small>
                                </div>
                            </div>
                        </div>

                        <!-- Timeline -->
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="timelineDefined" name="timelineDefined" value="Y">
                                    <label class="form-check-label fw-semibold" for="timelineDefined">
                                        <i class="ri-calendar-check-line text-danger me-1"></i>Timeline Defined
                                    </label>
                                </div>
                                <div id="timelineFields" style="display: none;">
                                    <label for="expectedTimeline" class="form-label">Expected Decision Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="expectedTimeline" name="expectedTimeline"
                                           min="<?= date('Y-m-d') ?>">
                                    <small class="text-muted">When does the prospect expect to make a decision?</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i>Save Interaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle Budget fields
    document.getElementById('budgetConfirmed').addEventListener('change', function() {
        const fields = document.getElementById('budgetFields');
        const input = document.getElementById('confirmedBudget');
        if (this.checked) {
            fields.style.display = 'block';
            input.required = true;
        } else {
            fields.style.display = 'none';
            input.required = false;
            input.value = '';
        }
    });

    // Toggle Decision Maker fields
    document.getElementById('decisionMakerConfirmed').addEventListener('change', function() {
        const fields = document.getElementById('decisionMakerFields');
        const select = document.getElementById('decisionMakerContactID');
        if (this.checked) {
            fields.style.display = 'block';
            select.required = true;
        } else {
            fields.style.display = 'none';
            select.required = false;
            select.value = '';
            // Hide new contact fields
            document.getElementById('newContactFields').style.display = 'none';
        }
    });

    // Toggle new contact form when "Create New Contact" is selected
    const contactSelect = document.getElementById('decisionMakerContactID');
    if (contactSelect && contactSelect.tagName === 'SELECT') {
        contactSelect.addEventListener('change', function() {
            const newContactFields = document.getElementById('newContactFields');
            if (this.value === 'new') {
                newContactFields.style.display = 'block';
                // Make new contact fields required
                document.getElementById('newContactName').required = true;
                document.getElementById('newContactEmail').required = true;
                document.getElementById('newContactPhone').required = true;
            } else {
                newContactFields.style.display = 'none';
                // Remove required from new contact fields
                document.getElementById('newContactName').required = false;
                document.getElementById('newContactEmail').required = false;
                document.getElementById('newContactPhone').required = false;
            }
        });
    }

    // Toggle Need fields
    document.getElementById('needIdentified').addEventListener('change', function() {
        const fields = document.getElementById('needFields');
        const textarea = document.getElementById('identifiedNeed');
        if (this.checked) {
            fields.style.display = 'block';
            textarea.required = true;
        } else {
            fields.style.display = 'none';
            textarea.required = false;
            textarea.value = '';
        }
    });

    // Toggle Timeline fields
    document.getElementById('timelineDefined').addEventListener('change', function() {
        const fields = document.getElementById('timelineFields');
        const input = document.getElementById('expectedTimeline');
        if (this.checked) {
            fields.style.display = 'block';
            input.required = true;
        } else {
            fields.style.display = 'none';
            input.required = false;
            input.value = '';
        }
    });

    // Character counter for need description
    document.getElementById('identifiedNeed').addEventListener('input', function() {
        document.getElementById('needCharCount').textContent = this.value.length;
    });

    // Initialize Flatpickr for expected timeline date
    if (typeof flatpickr !== 'undefined') {
        flatpickr('#expectedTimeline', {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'F j, Y',
            minDate: 'today',
            allowInput: true
        });
    }

    // Form submission
    document.getElementById('logInteractionForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('<?= "{$base}php/scripts/sales/manage_prospect_advanced.php" ?>', {
            method: 'POST',
            body: new URLSearchParams(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Interaction Logged!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false,
                    didClose: () => {
                        if (typeof hideSpinner === 'function') {
                            hideSpinner();
                        }
                    }
                });

                // Close modal and reload page
                bootstrap.Modal.getInstance(document.getElementById('logInteractionModal')).hide();
                setTimeout(() => location.reload(), 2000);
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
                text: 'Failed to log interaction'
            });
        })
        .finally(() => {
            if (typeof hideSpinner === 'function') {
                hideSpinner();
            }
        });
    });
});
</script>
