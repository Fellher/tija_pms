<div class="col-12 d-flex align-items-stretch">
        <div class="card custom-card border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 "><i class="ri ri-shield-user-line me-2"></i>Manage Salary Components </h5>
                    <a class="btn btn-sm btn-icon rounded-pill btn-primary-light float-end permissionRoleProfileModal"  href="#manageSalaryComponent" data-bs-toggle="modal"  > 
                        <i class="ti ti-plus"></i> 
                    </a> 
                  
                </div>
                <script>
                    let salaryComponentCategories, salaryComponentCategory, addSalaryComponentCategoryArr, salaryCategoryArr;
                    </script> 
        
                <?php 
                $componentCategories = Admin::tija_salary_component_category(array('Suspended'=> "N"), false, $DBConn);
            echo Utility::form_modal_header("manageSalaryComponent", "global/admin/jobs/manage_employee_salary_components.php", "Add  New Job category", array("modal-lg", "modal-dialog-centered"), $base);
                include "includes/core/admin/jobs/modals/manage_salary_components.php";
            echo Utility::form_modal_footer("Update Salary Component", "submit_salary_component", "btn btn-success btn-sm");
            $salaryComponents = Admin::tija_salary_components(array('Suspended'=> "N"), false, $DBConn);
            if($salaryComponents){
                foreach($salaryComponents as $salaryComponent){
                   $nodeID .="salary_{$salaryComponent->salaryComponentID}";
                    
                    ?>
                    
                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                        <div class="d-flex align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="me-2">
                                    <i class="ri ri-shield-user-line fs-24"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0"><?php echo $salaryComponent->salaryComponentTitle; ?></h6>
                                    <p class="mb-0"><?php echo $salaryComponent->salaryComponentDescription; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <a class="btn btn-sm btn-icon rounded-pill btn-primary-light salaryModalClass" href="#manageSalaryComponent" data-bs-toggle="modal"  data-id="<?php  echo $salaryComponent->salaryComponentID ?>" > 
                                <i class="ti ti-pencil"></i>
                            </a>
                            <a class="btn btn-sm btn-icon rounded-pill btn-danger-light " href="#manageSalaryComponent" data-bs-toggle="modal"  > 
                                <i class="ti ti-trash"></i>
                            </a>
                        </div>
                    </div>
                    <?php
                    //  echo Utility::form_modal_header("manageSalaryComponent{$nodeID}", "global/admin/jobs/manage_employee_salary_components.php", "Add  New Job category", array("modal-lg", "modal-dialog-centered"), $base);
                    //  include "includes/core/admin/jobs/modals/manage_salary_components.php";
                    // echo Utility::form_modal_footer("Update Salary Component", "submit_salary_component", "btn btn-success btn-sm");
                }
            } else {
                Alert::info("No Salary Components found", true, array('fst-italic', 'text-center', 'font-18'));
            }?>
            

            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function(){
        let salaryComponentArr = <?php echo json_encode($salaryComponents); ?>;
        console.log(salaryComponentArr);

        let salaryModalClassArr = document.querySelectorAll(".salaryModalClass");
        for(let editSalary of salaryModalClassArr) {
            console.log(editSalary);
            editSalary.addEventListener('click', function(){
                console.log("Edit Salary Component");
                let salaryComponentID = editSalary.getAttribute('data-id');
                console.log(salaryComponentID);
                let salaryComponent = salaryComponentArr.find(salaryComponent => salaryComponent.salaryComponentID == salaryComponentID);
                console.log(salaryComponent);
                if(salaryComponent){
                    let salaryComponentID = document.querySelector('#salaryComponentID');
                    let salaryComponentTitle = document.querySelector('#salaryComponentTitle');
                    let salaryComponentDescription = document.querySelector('#salaryComponentDescription');
                    let salaryComponentType = document.querySelector('#salaryComponentType');
                    let salaryComponentCategoryID = document.querySelector('#salaryComponentCategoryID');
                    let salaryComponentValueType = document.querySelector('#salaryComponentValueType');
                    let applyTo = document.querySelector('#applyTo');
                    salaryComponentID.value = salaryComponent.salaryComponentID;
                    salaryComponentTitle.value = salaryComponent.salaryComponentTitle;
                    salaryComponentDescription.value = salaryComponent.salaryComponentDescription;
                    salaryComponentType.value = salaryComponent.salaryComponentType;
                    salaryComponentCategoryID.value = salaryComponent.salaryComponentCategoryID;
                    salaryComponentValueType.value = salaryComponent.salaryComponentValueType;
                    applyTo.value = salaryComponent.applyTo;
                }
            
            });
        }

        // const manageSalaryComponent = document.querySelector('#manageSalaryComponent');
		// 	salaryModal = new bootstrap.Modal(manageSalaryComponent);
		// 	salaryModal.show();

    });
        salaryComponentCategories = <?php echo json_encode($componentCategories); ?>;
        salaryComponentCategory = document.getElementById('salaryComponentCategoryID');
        addSalaryComponentCategoryArr = document.querySelectorAll('.addSalaryComponentCategory');
        console.log(addSalaryComponentCategoryArr) ;
        addSalaryComponentCategoryArr.forEach(function(addSalaryComponentCategory){
            addSalaryComponentCategory.addEventListener('click', function(){
               let div= document.createElement('div');
                div.classList.add('card', 'p-3', 'bg-light', 'border-1', 'rounded', 'mt-3',  'mb-3', 'addSalaryComponentCategoryDiv');
                div.innerHTML = `
                    <div class="col-12">
                        <div class="form-group">
                            <label for="categoryTitle">Category Title</label>
                            <input type="text" name="salaryComponentCategoryTitle" id="salaryComponentCategoryTitle" class="form-control-xs form-control-plaintext border-bottom bg-white px-2" placeholder="Please insert category title">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label for="categoryDescription">Category Description</label>
                            <textarea name="salaryComponentCategoryDescription" id="salaryComponentCategoryDescription" class="form-control form-control-xs form-control-plaintext border-bottom bg-whiet px-2" placeholder="Please insert category description"></textarea>
                        </div>

                    </div>`;
                    addSalaryComponentCategory.parentElement.appendChild(div);
            });
        });
        salaryCategoryArr = document.querySelectorAll('.salaryCategory');
        salaryCategoryArr.forEach(function(salaryCategory){
            salaryCategory.addEventListener('change', function(){
                if(salaryCategory.value === "addNew"){
                    let div= document.createElement('div');
                    div.classList.add('card', 'p-3', 'bg-light', 'border-1', 'rounded', 'mt-3',  'mb-3', 'addSalaryComponentCategoryDiv');
                    div.innerHTML = `
                        <div class="col-12">
                            <div class="form-group">
                                <label for="categoryTitle">Category Title</label>
                                <input type="text" name="salaryComponentCategoryTitle" id="salaryComponentCategoryTitle" class="form-control-xs form-control-plaintext border-bottom bg-white px-2" placeholder="Please insert category title">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="categoryDescription">Category Description</label>
                                <textarea name="salaryComponentCategoryDescription" id="salaryComponentCategoryDescription" class="form-control form-control-xs form-control-plaintext border-bottom bg-whiet px-2" placeholder="Please insert category description"></textarea>
                            </div>
                        </div>`;
                        salaryCategory.parentElement.appendChild(div);
                }
            });
        });
</script>