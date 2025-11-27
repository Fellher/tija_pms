<div class="col-12 d-flex align-items-stretch">
        <div class="card custom-card border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center border-bottom border-3 border-danger mb-2">
                    <h5 class="card-title mb-0 "><i class="ri ri-shield-user-line me-2"></i>Manage Job Categories </h5>
                    <a class="btn btn-sm btn-icon rounded-pill btn-primary-light float-end permissionRoleProfileModal"  href="#manageJobCategories" data-bs-toggle="modal"  > 
                        <i class="ti ti-plus"></i> 
                    </a>                    
                  
                </div>
                <?php 
                    echo Utility::form_modal_header("manageJobCategories", "global/admin/jobs/manage_job_categories.php", "Add  New Job category", array("modal-lg", "modal-dialog-centered"), $base);
                        include "includes/core/admin/jobs/modals/manage_job_categories.php";
                    echo Utility::form_modal_footer("Update job Title", "submit_{jobTitleNodeID}", "btn btn-success btn-sm");
                    $jobCategories = Admin::tija_job_categories(array(), false, $DBConn);
                    if($jobCategories){
                        foreach($jobCategories as $jobCategory){
                            $nodeID = "manageJobCategories{$jobCategory->jobCategoryID}";
                            ?>
                        
                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                <div class="d-flex align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="me-2">
                                            <i class="ri ri-shield-user-line fs-24"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?php echo $jobCategory->jobCategoryTitle; ?></h6>
                                            <p class="mb-0"><?php echo $jobCategory->jobCategoryDescription; ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <a class="btn btn-sm btn-icon rounded-pill btn-primary-light ManageJobCategories" href="#manageJobCategories<?php echo $nodeID ?>" data-bs-toggle="modal"  > 
                                        <i class="ti ti-pencil"></i>
                                    </a>
                                    <a class="btn btn-sm btn-icon rounded-pill btn-danger-light permissionRoleProfileModal" href="<?php echo "#manageJobCategoriesDelete{$nodeID}"?>" data-bs-toggle="modal"  > 
                                        <i class="ti ti-trash"></i>
                                    </a>
                                </div>
                            </div>
                        <?php 
                          echo Utility::form_modal_header("manageJobCategories{$nodeID}", "global/admin/jobs/manage_job_categories.php", "Manage {$jobCategory->jobCategoryTitle}", array("modal-lg", "modal-dialog-centered"), $base);
                            include "includes/core/admin/jobs/modals/manage_job_categories.php";
                         echo Utility::form_modal_footer("Update job Title", "submit_{jobTitleNodeID}", "btn btn-success btn-sm");
                        }
                    } else {
                        Alert::info("No job categories found", true, array('fst-italic', 'text-center', 'font-18'));
                    }?>	 

            </div>
        </div>
    </div>
</div>
