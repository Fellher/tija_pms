<div class="col-12 d-flex align-items-stretch">
    <div class="card custom-card border-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 "><i class="ri ri-shield-user-line me-2"></i>Manage Employement Status </h5>
                <a class="btn btn-sm btn-icon rounded-pill btn-primary-light float-end permissionRoleProfileModal"  href="#manageEmploymentStatus" data-bs-toggle="modal"  > 
                    <i class="ti ti-plus"></i> 
                </a>                   
            </div>
            <?php 
            echo Utility::form_modal_header("manageEmploymentStatus", "global/admin/jobs/manage_employment_status.php", "Add  New Job category", array("modal-lg", "modal-dialog-centered"), $base);
                include "includes/core/admin/jobs/modals/manage_emploment_status.php";
            echo Utility::form_modal_footer("Update Employment status", "submit_employment_status", "btn btn-success btn-sm");
            $employmentStatus = Admin::tija_employment_status(array(), false, $DBConn);

            if($employmentStatus){
                foreach($employmentStatus as $employmentStatus){
                    $nodeID .="status_{$employmentStatus->employmentStatusID}";
                    ?>
                    
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div class="d-flex align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="me-2">
                                    <i class="ri ri-shield-user-line fs-24"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0"><?php echo $employmentStatus->employmentStatusTitle; ?></h6>
                                    <p class="mb-0"><?php echo $employmentStatus->employmentStatusDescription; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <a class="btn btn-sm btn-icon rounded-pill btn-primary-light permissionRoleProfileModal" href="#manageEmploymentStatus<?php echo $nodeID ?>" data-bs-toggle="modal"  > 
                                <i class="ti ti-pencil"></i>
                            </a>
                            <a class="btn btn-sm btn-icon rounded-pill btn-danger-light permissionRoleProfileModal" href="#manageEmploymentStatus" data-bs-toggle="modal"  > 
                                <i class="ti ti-trash"></i>
                            </a>
                        </div>
                        <?php 
                            echo Utility::form_modal_header("manageEmploymentStatus{$nodeID}", "global/admin/jobs/manage_employment_status.php", "Add  New Job category", array("modal-lg", "modal-dialog-centered"), $base);
                               include "includes/core/admin/jobs/modals/manage_emploment_status.php";
                           echo Utility::form_modal_footer("Update Employment status", "submit_employment_status", "btn btn-success btn-sm");
                        ?>
                    </div>
                    <?php
                }
            } else {
                Alert::info("No Employment Status found", true, array('fst-italic', 'text-center', 'font-18'));
            } ?>
        </div>
    </div>
</div>
