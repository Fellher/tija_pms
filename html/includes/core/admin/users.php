<div class="col-12 d-flex align-items-stretch">
    <div class="card custom-card border-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 "><i class="ri ri-shield-user-line me-2"></i>Manage Users </h5>
                <a class="btn btn-sm btn-icon rounded-pill btn-primary-light float-end permissionRoleProfileModal"  href="#manageEmploymentStatus" data-bs-toggle="modal"  > 
                    <i class="ti ti-plus"></i> 
                </a>                   
            </div>
            <?php 
            echo Utility::form_modal_header("manageEmploymentStatus", "global/admin/jobs/manage_employment_status.php", "Add  New Job category", array("modal-lg", "modal-dialog-centered"), $base);
                include "includes/core/admin/jobs/modals/manage_emploment_status.php";
            echo Utility::form_modal_footer("Update Employment status", "submit_employment_status", "btn btn-success btn-sm");
            $users = core::users(array(), false, $DBConn);

           ?>
        </div>
    </div>
</div>
