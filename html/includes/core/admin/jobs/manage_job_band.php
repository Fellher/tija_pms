<div class="col-12 d-flex align-items-stretch">
        <div class="card custom-card border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 "><i class="ri ri-shield-user-line me-2"></i>Manage Pay Grades</h5>
                    <a class="btn btn-sm btn-icon rounded-pill btn-primary-light float-end permissionRoleProfileModal"  href="#paybandModal" data-bs-toggle="modal"  > 
                        <i class="ti ti-plus"></i> 
                    </a>                   
                </div>
                <?php 
                echo Utility::form_modal_header("paybandModal", "global/admin/jobs/manage_job_band.php", "Add  New Pay Band", array("modal-lg", "modal-dialog-centered"), $base);
                    include "includes/core/admin/jobs/modals/manage_job_band.php";
                echo Utility::form_modal_footer("Update pay  band", "submit_pay_band", "btn btn-success btn-sm");
                $jobBands = Admin::tija_job_bands(array('Suspended'=> "N"), false, $DBConn);
                if($jobBands){                  
                    foreach($jobBands as $jobBand){
                        $nodeID .="jobBand_{$jobBand->jobBandID}";?>
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div class="d-flex align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="ri ri-shield-user-line fs-24"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0"><?php echo $jobBand->jobBandTitle; ?></h6>
                                        <p class="mb-0"><?php echo $jobBand->jobBandDescription; ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <a class="btn btn-sm btn-icon rounded-pill btn-primary-light permissionRoleProfileModal" href="#manageJobBand<?php echo $nodeID ?>" data-bs-toggle="modal"  > 
                                    <i class="ti ti-pencil"></i>
                                </a>
                                <a class="btn btn-sm btn-icon rounded-pill btn-danger-light permissionRoleProfileModal" href="#paybandModalDeete<?php echo $nodeID ?>" data-bs-toggle="modal"  > 
                                    <i class="ti ti-trash"></i>
                                </a>
                            </div>
                        </div>
                        <?php
                        echo Utility::form_modal_header("manageJobBand{$nodeID}", "global/admin/jobs/manage_job_band.php", "Add  New Pay Band", array("modal-lg", "modal-dialog-centered"), $base);
                            include "includes/core/admin/jobs/modals/manage_job_band.php";
                        echo Utility::form_modal_footer("Update pay  band", "submit_pay_band", "btn btn-success btn-sm");
                    }
                } else {
                    Alert::info("No Salary Components found", true, array('fst-italic', 'text-center', 'font-18'));
                }?>            
            </div>
        </div>
    </div>
</div>