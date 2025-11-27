<?php
echo Utility::form_modal_header(
    "assignHRManagerModal",
    "global/admin/manage_hr_manager.php",
    "Manage HR Manager Access",
    array('modal-md', 'modal-dialog-centered'),
    $base
);
?>
    <input type="hidden" name="userID" id="assignHrManagerUserID" value="">
    <input type="hidden" name="entityID" id="assignHrManagerEntityID" value="<?= isset($entityID) ? $entityID : '' ?>">
    <input type="hidden" name="returnURL" id="assignHrManagerReturnURL"
           value="?s=core&ss=admin&p=entity_details&entityID=<?= $entityID ?>&tab=employees">

    <div class="text-center mb-3">
        <div class="avatar avatar-lg bg-primary-transparent rounded-circle mx-auto mb-2">
            <i class="fas fa-user-shield fs-24 text-primary"></i>
        </div>
        <h5 class="fw-semibold mb-1" id="assignHrManagerEmployeeName">Employee Name</h5>
        <p class="text-muted mb-0">
            <i class="far fa-envelope me-1"></i>
            <span id="assignHrManagerEmployeeEmail">employee@example.com</span>
        </p>
    </div>

    <div class="alert alert-info d-flex align-items-start gap-2">
        <i class="fas fa-info-circle mt-1"></i>
        <div class="text-start small">
            Assign a <strong>Primary</strong> HR manager (default approver) and optionally a
            <strong>Substitute</strong> HR manager who can approve when the primary is unavailable.
            Removing access revokes this employee's HR permissions for the entity.
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-0">
        <div class="card-body">
            <div class="d-flex flex-column gap-3">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="hrRoleType" id="assignHrRolePrimary" value="primary">
                    <label class="form-check-label fw-semibold" for="assignHrRolePrimary">
                        Primary HR Manager
                    </label>
                    <p class="text-muted small mb-0">Receives priority on leave approvals and notifications.</p>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="hrRoleType" id="assignHrRoleSubstitute" value="substitute">
                    <label class="form-check-label fw-semibold" for="assignHrRoleSubstitute">
                        Substitute HR Manager
                    </label>
                    <p class="text-muted small mb-0">Acts when the primary HR manager is unavailable.</p>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="radio" name="hrRoleType" id="assignHrRoleNone" value="none" checked>
                    <label class="form-check-label fw-semibold text-danger" for="assignHrRoleNone">
                        Remove HR Manager Access
                    </label>
                    <p class="text-muted small mb-0">Revokes HR permissions for this employee on this entity.</p>
                </div>
            </div>
        </div>
    </div>
<?php
echo Utility::form_modal_footer('Save Changes', 'saveHrManager', 'btn btn-success btn-sm');
?>

