<div class="col-12 d-flex align-items-stretch">
        <div class="card custom-card border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 "><i class="ri ri-shield-user-line me-2"></i>Manage Job Titles</h5>
                    <a class="btn btn-sm btn-icon rounded-pill btn-primary-light float-end permissionRoleProfileModal"  href="#manageJobTitle" data-bs-toggle="modal"  > 
                        <i class="ti ti-plus"></i> 
                    </a>
                    <?php 
                    echo Utility::form_modal_header("manageJobTitle", "global/admin/jobs/manage_job_titles.php", "Add  New Job Role", array("modal-lg", "modal-dialog-centered"), $base);
                        include "includes/core/admin/jobs/modals/manage_job_titles.php";
                    echo Utility::form_modal_footer("Update job Title", "submit_{jobTitleNodeID}", "btn btn-success btn-sm");
                    ?>
                </div>
                <div class="table-responsive mt-4">
                    <table class="table table-center table-padding mb-0" id="jobTitlesTable">
                        <thead>
                            <tr>
                                <th class="py-3">Job Title</th>
                                <th class="py-3">Job Category</th>
                                <th class="py-3">Job Description</th>
                                <th class="py-3">Actions</th> 
                            </tr> 
                        </thead>
                        <tbody>
                            <?php                       
                            $jobTitles = Admin::tija_job_titles(array(), false, $DBConn);
                            if($jobTitles){
                                // var_dump($jobTitles);
                                foreach($jobTitles as $jobTitle){                                   
                                    $nodeID = $jobTitle->jobTitleID;
                                    $jobCategory = Admin::tija_job_categories(array("jobCategoryID"=>$jobTitle->jobCategoryID), true, $DBConn);?>
                                    <tr>
                                        <td><?php echo $jobTitle->jobTitle ?></td>
                                        <td><?php echo $jobCategory->jobCategoryTitle ?></td>
                                        <td class="col-lg-8" style="max-width: 400px;" >
                                            <?=  $jobTitle->jobDescription; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <a class="btn btn-sm btn-icon rounded-pill btn-primary-light jobTitleEditModal"                                                
                                                href="<?php echo "#manageJobTitle"?>" 
                                                data-bs-toggle="modal" 
                                                data-job-title-id="<?php echo $jobTitle->jobTitleID; ?>"
                                                data-job-title="<?php echo $jobTitle->jobTitle; ?>"
                                                data-job-description="<?php echo $jobTitle->jobDescription; ?>"
                                                data-jobCategory-id="<?php echo $jobTitle->jobCategoryID; ?>"                                             
                                                data-jobDescription-doc="<?php echo $jobTitle->jobDescriptionDoc; ?>"
                                                > 
                                                    <i class="ti ti-pencil"></i>
                                                </a>
                                                <a class="btn btn-sm btn-icon rounded-pill btn-danger-light permissionRoleProfileModal" href="#manageJobTitles" data-bs-toggle="modal"  > 
                                                    <i class="ti ti-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                   

                                }
                            } else { ?>
                                <tr>
                                    <td colspan="4" class="text-center"><?php
                                        Alert::info("No job titles found", false, array('fst-italic', 'font-18'));
                                    
                                    ?></td>
                                </tr>
                                <?php
                            }?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    
 <script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('.jobTitleEditModal').forEach(function(element) {
            element.addEventListener('click', function() {
                const form = document.querySelector('#manageJobTitleFormModal');
                
                const data = this.dataset;
                const fieldMappings = {
                    'jobTitle': 'jobTitle',
                    'jobCategoryID': 'jobcategoryId',
                    'jobDescription': 'jobDescription',
                    'jobTitleID': 'jobTitleId',
                    'jobDescriptionDoc': 'jobdescriptionDoc'
                };
               
                for (const [field, dataAttr] of Object.entries(fieldMappings)) {
                    const input = form.querySelector(`[name="${field}"]`);
                    if (input) {
                        console.log(`Assigning value to ${field}:`, data[dataAttr]);
                        input.value = data[dataAttr] || '';
                    } else {
                        console.warn(`Input not found for field: ${field}`);
                    }
                }
                 // Fill the textarea with tinyMCE
                tinymce.init({
                selector: '#jobDescription'
                });
                const editor = tinymce.get('jobDescription'); // Make sure 'entityDescription' matches your textarea's ID
                if (editor) {
                    // Wait for a brief moment to ensure tinyMCE is fully initialized
                    setTimeout(() => {
                        editor.setContent(data.jobDescription || '');
                    }, 100);
                }
            });
        });
    });
 </script>