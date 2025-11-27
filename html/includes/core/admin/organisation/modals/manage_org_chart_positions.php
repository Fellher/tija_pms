<div class="row">
    <!-- Hidden Fields -->
    <input type="hidden" name="positionAssignmentID" id="modal_positionAssignmentID">
    <input type="hidden" name="orgDataID" id="modal_orgDataID" value="<?= isset($orgChartDetails) && is_object($orgChartDetails) ? $orgChartDetails->orgDataID : (isset($orgDetails) && is_object($orgDetails) ? $orgDetails->orgDataID : '') ?>">
    <input type="hidden" name="orgChartID" id="modal_orgChartID" value="<?= isset($orgChartDetails) && is_object($orgChartDetails) ? $orgChartDetails->orgChartID : '' ?>">

    <div class="col-12 mb-3">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Add or edit a position</strong> in the organizational chart. Positions represent roles or job titles in your hierarchy.
        </div>
    </div>

    <!-- Position Title (Job Title Selection) -->
    <div class="col-md-12 mb-3">
        <label for="modal_positionID" class="form-label">
            Job Title/Position <span class="text-danger">*</span>
        </label>
        <select class="form-select form-control-sm" id="modal_positionID" name="positionID" required>
            <option value="">-- Select Job Title --</option>
            <?php
            // Get all job titles
            $allJobTitles = Admin::tija_job_titles(array('Suspended' => 'N'), false, $DBConn);

            if($allJobTitles):
                foreach ($allJobTitles as $jobTitle): ?>
                    <option value="<?= $jobTitle->jobTitleID ?>"
                            data-title="<?= htmlspecialchars($jobTitle->jobTitle) ?>"
                            data-description="<?= htmlspecialchars($jobTitle->jobDescription ?? '') ?>">
                        <?= htmlspecialchars($jobTitle->jobTitle) ?>
                        <?php if($jobTitle->jobDescription): ?>
                            - <?= htmlspecialchars(substr($jobTitle->jobDescription, 0, 50)) ?>
                        <?php endif; ?>
                    </option>
                <?php endforeach;
            endif; ?>
        </select>
        <small class="text-muted">Select the job title for this position in the chart</small>
    </div>

    <!-- Parent Position -->
    <div class="col-md-12 mb-3">
        <label for="modal_positionParentID" class="form-label">
            Reports To (Parent Position)
        </label>
        <select class="form-select form-control-sm" id="modal_positionParentID" name="positionParentID">
            <option value="0">None (Top Level Position)</option>
            <?php
            // Get assigned positions for this chart
            if(isset($orgChartDetails) && is_object($orgChartDetails)) {
                $assignedPositions = Data::org_chart_position_assignments(
                    array('orgChartID' => $orgChartDetails->orgChartID, 'Suspended' => 'N'),
                    false,
                    $DBConn
                );

                if($assignedPositions):
                    foreach ($assignedPositions as $position): ?>
                        <option value="<?= $position->positionAssignmentID ?>"
                                data-title="<?= htmlspecialchars($position->positionTitle) ?>">
                            <?= htmlspecialchars($position->positionTitle) ?>
                        </option>
                    <?php endforeach;
                endif;
            }
            ?>
        </select>
        <small class="text-muted">Select the position this role reports to in the hierarchy</small>
    </div>

    <!-- Entity Assignment (for multi-entity organizations) -->
    <div class="col-md-12 mb-3">
        <label for="modal_positionEntityID" class="form-label">
            Entity Assignment (Optional)
        </label>
        <select class="form-select form-control-sm" id="modal_positionEntityID" name="entityID">
            <option value="">-- Organization-Wide Position --</option>
            <?php
            // Get entities for this organization
            if(isset($orgDetails) && is_object($orgDetails)) {
                $orgEntities = Data::entities_full(
                    array('orgDataID' => $orgDetails->orgDataID, 'Suspended' => 'N'),
                    false,
                    $DBConn
                );

                if($orgEntities):
                    foreach ($orgEntities as $entity): ?>
                        <option value="<?= $entity->entityID ?>">
                            <?= htmlspecialchars($entity->entityName) ?>
                            <?php if($entity->entityTypeTitle): ?>
                                (<?= htmlspecialchars($entity->entityTypeTitle) ?>)
                            <?php endif; ?>
                        </option>
                    <?php endforeach;
                endif;
            }
            ?>
        </select>
        <small class="text-muted">Assign this position to a specific entity, or leave blank for organization-wide</small>
    </div>

    <!-- Position Notes -->
    <div class="col-md-12 mb-3">
        <label for="modal_positionNotes" class="form-label">Notes/Description (Optional)</label>
        <textarea class="form-control form-control-sm"
                  id="modal_positionNotes"
                  name="positionNotes"
                  rows="2"
                  placeholder="Additional notes about this position's responsibilities or requirements"></textarea>
    </div>

    <!-- Quick Reference -->
    <div class="col-12">
        <div class="alert alert-secondary mb-0">
            <h6 class="alert-heading"><i class="fas fa-lightbulb me-2"></i>Quick Tips</h6>
            <ul class="mb-0 small">
                <li><strong>Top-level positions</strong> should have "None" as parent (e.g., CEO, Board)</li>
                <li><strong>Child positions</strong> should select their reporting position as parent</li>
                <li><strong>Entity-specific</strong> positions are for multi-entity organizations with different structures</li>
                <li><strong>Organization-wide</strong> positions appear across all entities in the group</li>
            </ul>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Tom Select for better dropdowns
    const positionSelect = document.getElementById('modal_positionID');
    if (positionSelect && !positionSelect.tomselect) {
        new TomSelect(positionSelect, {
            create: false,
            sortField: 'text',
            placeholder: 'Select Job Title',
            searchField: ['text'],
            onChange: function(value) {
                console.log('Selected position:', value);
                updatePositionPreview(value);
            }
        });
    }

    const positionParentSelect = document.getElementById('modal_positionParentID');
    if (positionParentSelect && !positionParentSelect.tomselect) {
        new TomSelect(positionParentSelect, {
            create: false,
            sortField: 'text',
            placeholder: 'Select Parent Position (or None for top-level)',
            searchField: ['text']
        });
    }

    const entitySelect = document.getElementById('modal_positionEntityID');
    if (entitySelect && !entitySelect.tomselect) {
        new TomSelect(entitySelect, {
            create: false,
            sortField: 'text',
            placeholder: 'Select Entity (optional)',
            searchField: ['text']
        });
    }
});

// Update position preview based on selected job title
function updatePositionPreview(jobTitleID) {
    if(!jobTitleID) return;

    const select = document.getElementById('modal_positionID');
    const option = select.querySelector(`option[value="${jobTitleID}"]`);

    if(option) {
        const title = option.getAttribute('data-title');
        const description = option.getAttribute('data-description');

        console.log('Position Preview:', {title, description});
    }
}

// Reset form when modal is hidden
document.getElementById('manageOrgChartPosition')?.addEventListener('hidden.bs.modal', function() {
    const form = this.querySelector('form');
    if(form) {
        form.reset();

        // Reset Tom Select instances
        const selects = form.querySelectorAll('select');
        selects.forEach(select => {
            if(select.tomselect) {
                select.tomselect.clear();
            }
        });
    }
});
</script>
