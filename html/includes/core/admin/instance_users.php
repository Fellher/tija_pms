<div class="card custom-card">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom">                  
            <h4 class=" border-none t400 "> User Management </h4>
            <a aria-label="anchor" href="javascript:void(0);" class="btn btn-sm btn-primary-light shadow-sm" data-bs-toggle="modal" data-bs-target="#manageAdmin">  add admin  </a>
        </div>
        <div class="table-responsive text-start">
            <?php $admins= Core::organisation_admins(['Suspended'=>"N"], false, $DBConn);
            $adminTypes = Admin::admin_types(array(), false, $DBConn);
            $users= Core::user([], false, $DBConn);

            // var_dump($users);
            
            if(!$admins){
                Alert::info("No Admins set up for this tax computation instance", true,
                array('fst-italic', 'text-center', 'font-18'));
            } else {?>
                <table class="table table-sm table-hover text-nowrap mb-0">
                    <thead>
                        <tr>
                            <th class="col">Admin Name</th>
                            <th class="col">Admin Type</th>
                            <th class="col">Organisation </th>
                            <th class="col">Entity</th>
                            <th class="col"> Unit </th>
                            <th class="col text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if($admins){
                            foreach ($admins as $key => $admin) {
                                // var_dump($admin);
                                ?>
                                <tr class="text-left">
                                    <td class="text-start"><?= "{$admin->AdminName} ({$admin->Email})"  ?></td>
                                    <td class="text-start"><?= $admin->adminTypeName ?></td>
                                    <td class="text-start"><?= $admin->orgName ?></td>
                                    <td class="text-start"><?= $admin->entityName ?></td>
                                    <td class="text-start"><?= $admin->unitName ?></td>
                                    <td class="text-end">
                                        <button 
                                        class="btn btn-sm btn-primary-light shadow-sm edit_admin"  
                                        data-bs-toggle="modal"
                                        data-bs-target="#manageAdmin" 
                                        data-admin-id="<?= $admin->adminID ?>" 
                                     
                                        data-user-id= "<?= $admin->userID ?>"
                                        data-admin-type-id="<?= $admin->adminTypeID ?>" 
                                        data-org-data-id="<?= $admin->orgDataID ?>" 
                                        data-entity-id="<?= $admin->entityID ?>" 
                                        data-unit-id="<?= $admin->unitID ?>"
                                        data-unit-type-id = "<?= $admin->unitTypeID ?>"
                                        data-unit-id = "<?= $admin->unitID ?>"
                                        data-is-employee= "<?= $admin->isEmployee ?>"
                                        >
                                            Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger-light shadow-sm">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                <?php
                            }
                        }?>
                    </tbody>
                </table>
                <?php
            } ?>
    </div>
</div>

<?php
// Modal for adding Organisation Admin
echo Utility::form_modal_header("manageAdmin", "global/admin/manage_admin.php", "Manage Organisation Admin", array('modal-md', 'modal-dialog-centered', "modal-xl"), $base);
    include "includes/scripts/global/modals/manage_org_admin.php";
echo Utility::form_modal_footer('Save Entity');
?>
<script>
    // check that the document is ready
    document.addEventListener("DOMContentLoaded", function() {
        // Get all edit buttons
        const editButtons = document.querySelectorAll('.edit_admin');
        
        // Add click event listener to each button
        editButtons.forEach(button => {
            button.addEventListener('click', function() {

                // check that the modal is open
                const modal = document.querySelector('#manageAdmin');
                if (modal) {
                    console.log(`Modal with ID ${modal.id} is open.`);
                    // Clear any previous error messages
                    const errorMessage = modal.querySelector('.error-message');
                    if (errorMessage) {
                        errorMessage.textContent = '';
                    }
                    // Reset the form fields in the modal
                    // modal.querySelector('form').reset();
                       // Get all data attributes from the button
                    const data = this.dataset;

                    console.log(data);

                    // console.log(modal);
                    // Map form fields to their corresponding data attributes
                    const fieldMappings = {
                        'adminID': 'adminId',
                        'userID': 'userId',
                        'adminTypeID': 'adminTypeId',
                        'orgDataID': 'orgDataId',
                        'entityID': 'entityId',
                        'unitID': 'unitId',
                        'unitTypeID': 'unitTypeId',
                        'isEmployee': 'isEmployee'
                    };

                    // console.log(fieldMappings);
                    // Fill in the form fields using the data attributes

                    // fill in regular form fields
                    for (const [field, dataAttr] of Object.entries(fieldMappings)) {
                        // console.log(`Filling field: ${field} with data attribute: ${data[dataAttr]}`);
                        // Find the input or select element in the modal
                        const input = modal.querySelector(`input[name="${field}"], select[name="${field}"]`);
                        if (input) {
                            console.log(input.value);
                            input.value = data[dataAttr];
                            console.log(`Setting ${field} to ${data[dataAttr]}`); // Debugging log
                        }
                    }
                    // If you have select elements that need special handling
                    // (like setting selected options), handle them here
                    const selects = ['adminTypeID', 'userID'];
                    selects.forEach(selectName => {
                        const select = modal.querySelector(`[name="${selectName}"]`);
                        if (select && data[selectName]) {
                            console.log(data[selectName]);

                            select.value = data[selectName];
                        }
                    });
                }


                // // Get data attributes from the button
                // const adminID = this.getAttribute('data-admin-id');
                // const adminName = this.getAttribute('data-admin-name');
                // const adminTypeID = this.getAttribute('data-admin-type-id');
                // const orgDataID = this.getAttribute('data-org-id');
                // const entityID = this.getAttribute('data-entity-id');
                // const unitID = this.getAttribute('data-unit-id');

                // // Set the values in the modal form
                // document.querySelector('#manageAdmin input[name="adminID"]').value = adminID;
                // document.querySelector('#manageAdmin input[name="adminName"]').value = adminName;
                // document.querySelector('#manageAdmin select[name="adminType"]').value = adminTypeID;
                // document.querySelector('#manageAdmin select[name="orgDataID"]').value = orgDataID;
                // document.querySelector('#manageAdmin select[name="entityID"]').value = entityID;
                // document.querySelector('#manageAdmin select[name="unitID"]').value = unitID;
            });
        });
    });
</script>