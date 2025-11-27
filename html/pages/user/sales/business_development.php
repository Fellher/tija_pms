<?php  
if(!$isValidUser) {
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}
$salesProspectID = (isset($_GET['bdID']) && !empty($_GET['bdID'])) ? Utility::clean_string($_GET['bdID']) : null;
$prospectDetails = Sales::sales_prospects(array('salesProspectID'=>$salesProspectID), true, $DBConn);

$employeeID=(isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) : $userDetails->ID;
$employeeDetails = Data::users(array('ID'=>$employeeID), true, $DBConn);
$allEmployees = Employee::employees([], false, $DBConn);
$orgDataID= isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : $employeeDetails->orgDataID;
$entityID= isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : $employeeDetails->entityID;
$clients = Client::client_full(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
$businessUnits = Data::business_units(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
$allOrgs = Admin::organisation_data_mini([], false, $DBConn);
$leadSources = Data::lead_sources([], false, $DBConn);

// //var_dump($clients);
$clientContacts = Client::client_contacts(array('Suspended'=> 'N'), false, $DBConn);
// //var_dump($clientContacts);
$prospects = Sales::sales_prospects(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);

?> 
<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
    <h1 class="page-title fw-medium fs-24 mb-0">Business Development Dashboard<span class="t300 text-primary"> <?= $prospectDetails ? "<br> {$prospectDetails->salesProspectName}" : "(Leads/Potential Clients)" ?></span> </h1>
    <div class="ms-md-1 ms-0">
        <button type="button" class="btn btn-primary-light shadow btn-sm px-4 addNewSale" data-bs-toggle="modal" data-bs-target="#manageProspectModal">
            <i class="ri-add-line"></i>
            Add Prospect
        </button>
      
    </div>
</div>
<?php 
if($salesProspectID){

    if(!$prospectDetails) {
        Alert::info("No prospect found with the ID: {$salesProspectID}", true, array('fst-italic', 'text-center', 'font-18'));
        return;
    }
    // var_dump($prospectDetails);
    ?>
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Prospect Details  <span class="float-end">
                <button type="button" class="btn  btn-icon rounded-pill btn-primary-light editProspectDetailForm"> <i class="ri-edit-line"></i></button></span> </h4>
            <form id="prospectDetailsFormEdit" action="<?= "{$base}php/scripts/sales/business_development/manage_sale_prospect.php" ?>" method="post">
                <input type="hidden" name="action" id="action" value="updateSaleProspect">
                <input type="hidden" name="salesProspectID" id="salesProspectID" value="<?= $prospectDetails->salesProspectID ?>">
                <input type="hidden" name="orgDataID" id="orgDataID" value="<?= $prospectDetails->orgDataID ?>">
                <input type="hidden" name="entityID" id="entityID" value="<?= $prospectDetails->entityID ?>">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="row">
                            <div class="form-group col-lg-4 col-md-6 col-sm-12">
                                <label for="salesProspectName">Sales Prospect Name</label>
                                <input type="text" class="form-control-sm form-control-plaintext bg-light border-bottom px-2" id="salesProspectName" name="salesProspectName" value="<?= $prospectDetails->salesProspectName ?>" required disabled>
                            </div>
                            <div class="col-lg-4  ">
                                <label for="prospectEmail">
                                    Prospect Email Address
                                </label>
                                <input type="email" class="form-control-sm form-control-plaintext bg-light border-bottom px-2" id="prospectEmail" name="prospectEmail" value="<?= $prospectDetails->prospectEmail ?>" required disabled>
                            </div>
                            <div class="form-group col-lg-4 col-md-6 col-sm-12">
                                <label for="isClient">Is This Prospect a Client?</label>
                                <select class="form-control-sm form-control-plaintext bg-light border-bottom px-2" id="isClient" name="isClient" disabled>
                                    <option value="N" <?= $prospectDetails->isClient === 'N' ? 'selected' : '' ?>>No</option>
                                    <option value="Y" <?= $prospectDetails->isClient === 'Y' ? 'selected' : '' ?>>Yes</option>
                                </select>
                            </div>
                            <div class="form-group col-lg-4 col-md-6 col-sm-12 prospectNameDiv <?= $prospectDetails->isClient === 'Y' ? 'd-none' : '' ?>">
                                <label for="prospectCaseName">Prospect Case Name</label>
                                <input type="text" class="form-control-sm form-control-plaintext bg-light border-bottom px-2" id="prospectCaseName" name="prospectCaseName" value="<?= $prospectDetails->prospectCaseName ?>" required disabled>
                            </div>
                            <div class="form-group col-lg-4 col-md-6 col-sm-12 clientIDDiv <?= $prospectDetails->isClient === 'Y' ? '' : 'd-none' ?>" id="clientIDDiv">
                                <label for="clientID">Client ID</label>
                                <select class="form-control-sm form-control-plaintext bg-light border-bottom px-2" id="clientID" name="clientID" disabled>
                                    <?= Form::populate_select_element_from_object($clients, 'clientID', 'clientName', $prospectDetails->clientID, '', 'Select Client') ?>
                                </select>
                            </div>
                            <div class="form-group col-lg-4 col-md-6 col-sm-12 clientIDDiv ">
                                <label for="estimatedValue"> Prospect estimate Value</label>
                                <input type="number" class="form-control-sm form-control-plaintext bg-light border-bottom px-2" id="estimatedValue" name="estimatedValue" value="<?= $prospectDetails->estimatedValue ?>" required disabled>
                            </div>
                            <div class="form-group col-lg-4 col-md-6 col-sm-12">
                                <label for="probability">Probability</label>
                                <input type="number" class="form-control-sm form-control-plaintext bg-light border-bottom px-2" id="probability" name="probability" value="<?= $prospectDetails->probability ?>" required disabled>
                            </div>
                            <div class="form-group col-lg-4 col-md-6 col-sm-12">
                                <label for="leadSourceID">Lead Source</label>
                                <select class="form-control-sm form-control-plaintext bg-light border-bottom px-2" id="leadSourceID" name="leadSourceID" disabled>
                                    <?= Form::populate_select_element_from_object($leadSources, 'leadSourceID', 'leadSourceName', $prospectDetails->leadSourceID, '', 'Select Lead Source') ?>
                                </select>
                            </div>
                            <div class="form-group col-lg-4 col-md-6 col-sm-12">
                                <label for="businessUnitID">Business Unit</label>
                                <select class="form-control-sm form-control-plaintext bg-light border-bottom px-2" id="businessUnitID" name="businessUnitID" disabled="disabled">
                                    <?= Form::populate_select_element_from_object($businessUnits, 'businessUnitID', 'businessUnitName', $prospectDetails->businessUnitID, '', 'Select Business Unit') ?>
                                </select>
                            </div>
                            <div class="form-group col-lg-4 col-md-6 col-sm-12">
                                <label for="salesProspectStatus">Sales Prospect Status</label>
                                <select class="form-control-sm form-control-plaintext bg-light border-bottom px-2" id="salesProspectStatus" name="salesProspectStatus" disabled>
                                    <option value="Open" <?= $prospectDetails->salesProspectStatus === 'Open' ? 'selected' : '' ?>>Open</option>
                                    <option value="Closed" <?= $prospectDetails->salesProspectStatus === 'Closed' ? 'selected' : '' ?>>Closed</option>
                                    <option value="In Progress" <?= $prospectDetails->salesProspectStatus === 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                </select>
                            </div>

                        </div>
                        
                        
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12">

                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea class="form-control-sm form-control-plaintext bg-light border-bottom px-2 borderless-mini" id="address" name="address" rows="3" disabled><?= $prospectDetails->address ?></textarea>
                        </div>
                    </div>

                </div>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const editProspectDetailForm = document.querySelector('.editProspectDetailForm');
                    const prospectDetailForm = document.querySelector('#prospectDetailsFormEdit');

                    editProspectDetailForm.addEventListener('click', function() {
                        prospectDetailForm.querySelectorAll('input, select, textarea').forEach(function(element) {
                            element.disabled = false;
                            element.classList.remove('bg-light');
                            element.classList.add('bg-light-blue');
                            console.log(element);
                        });

                        // Add a submit button to the form
                        const submitButton = document.createElement('input');
                        submitButton.type = 'submit';
                        submitButton.value = 'Save Changes';
                        submitButton.className = 'btn btn-primary';
                        submitButton.classList.add('mx-3');
                        prospectDetailForm.appendChild(submitButton);

                        // Add a cancel button to the form
                        const cancelButton = document.createElement('button');
                        cancelButton.type = 'button';
                        cancelButton.textContent = 'Cancel Edit';
                        cancelButton.className = 'btn btn-secondary';
                        prospectDetailForm.insertBefore(cancelButton, submitButton);

                        cancelButton.addEventListener('click', function() {
                            prospectDetailForm.querySelectorAll('input, select, textarea').forEach(function(element) {
                                element.disabled = true;
                                element.classList.remove('bg-light-blue');
                                element.classList.add('bg-light');
                                element.classList.add('mx-3');
                            });
                            prospectDetailForm.removeChild(submitButton);
                            prospectDetailForm.removeChild(this);
                        });
                    });
                });
                </script>                
                <!-- Add more fields here -->
            </form>

        </div>
    </div>
    <?php
} else {?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="salesProspectsTable" class="table table-striped table-bordered table-hover table-sm" style="width:100%">
                    <thead>
                        <tr>
                            <th>Prospect Name</th>                        
                            <th>Address</th>
                            <th>Email Address</th>
                            <th>Case Name</th>
                            <th>Estimated Value</th>
                            <th>Probability</th>
                            <th>Lead Source</th>
                            <th>Business Unit</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="salesProspectsTableBody">
                        <?php
                        if(!empty($prospects)) {
                            foreach ($prospects as $prospect) {
                                // var_dump($prospect);
                                ?>
                            <tr>
                                <td> <a href="<?= "{$base}html/{$getString}&bdID={$prospect->salesProspectID}"?>"> <?=  $prospect->salesProspectName ?> </a></td>
                                <td> <?= $prospect->address ? $prospect->address : '' ?></td>
                                <td> <?= $prospect->prospectEmail ?></td>
                                <td> <?= $prospect->prospectCaseName ?></td>
                                <td> <?= $prospect->estimatedValue ?></td>
                                <td> <?= $prospect->probability ?></td>
                                <td> <?= $prospect->leadSourceName ?></td>
                                <td> <?= $prospect->businessUnitName ?></td>
                                <td> <?= $prospect->salesProspectStatus ?></td>
                                
                                <td>
                            
                                <button 
                                    class="btn btn-primary btn-sm editSaleProspect" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#manageProspectModal" 
                                    data-sales-prospect-id="<?= $prospect->salesProspectID ?>"
                                    data-is-client="<?= $prospect->isClient ?>"
                                    data-client-id="<?= $prospect->clientID ?>"
                                    data-prospect-case-name="<?= $prospect->prospectCaseName ?>"
                                    data-address="<?= $prospect->address ? htmlspecialchars($prospect->address) : "-" ?>"
                                    data-prospect-email="<?= $prospect->prospectEmail ?>"
                                    data-estimated-value="<?= $prospect->estimatedValue ?>"
                                    data-probability="<?= $prospect->probability ?>"
                                    data-lead-source-id="<?= $prospect->leadSourceID ?>"
                                    data-business-unit-id="<?= $prospect->businessUnitID ?>"
                                    data-sales-prospect-status="<?= $prospect->salesProspectStatus ?>"
                                    data-sales-prospect-name="<?= $prospect->salesProspectName ?>"
                                    data-entity-id="<?= $prospect->entityID ?>"
                                    data-org-data-id="<?= $prospect->orgDataID ?>"
                                
                                    
                                    >Edit</button>
                                <button 
                                    class="btn btn-danger btn-sm deleteSaleProspect" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#deleteProspectModal" 
                                    data-sales-prospect-id="<?= $prospect->salesProspectID ?>"
                                    >Delete</button>
                                </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='10'>No prospects found</td></tr>";
                        }
                        ?>
                        <!-- Data will be populated here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php    
}

   // activity 2
   $activities = Schedule::tija_activities(array(), false, $DBConn);
   $activityTitle = isset($salesCaseDetails) ? "Sales Activities ( <span class='text-primary fst-italic'>{$salesCaseDetails->salesCaseName}</span> )" : "";

   include "includes/scripts/work/activity_display_script.php";
// var_dump($prospects[0]);
?>

<?php
// //var_dump($prospects);
echo Utility::form_modal_header("manageProspectModal", "sales/business_development/manage_sale_prospect.php", "Manage Sale Prospect", array('modal-lg', 'modal-dialog-centered'), $base);
    include "includes/scripts/sales/business_development/modals/manage_sale_prospect.php";
echo Utility::form_modal_footer("Save Sale Prospect", "manageSaleProspect", 'btn btn-primary btn-sm', true);
?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    console.log('Page is loaded');
    // Initialize DataTable
    // const salesProspectsTable = $('#salesProspectsTable').DataTable({
    //     responsive: true,
    //     columnDefs: [
    //         { orderable: false, targets: -1 } // Disable sorting on the last column (Action)
    //     ]
    // });
    // Handle the click event for the "edit" button
  
    document.querySelectorAll('.editSaleProspect').forEach(button => {
        button.addEventListener('click', function() {

            // get the form
            const form = document.querySelector('#manageProspectModal');
            if (!form)    return;
            const clientIDDiv = form.querySelector('#clientIDDiv');

            // Get the data attributes from the button
            const data = this.dataset;
            console.log(data);

                // Map form fields to their corresponding data attributes
            const fieldMappings = {
                'salesProspectID': 'salesProspectId',
                'salesProspectName': 'salesProspectName',
                'address': 'address',
                'prospectEmail': 'prospectEmail',
                'prospectCaseName': 'prospectCaseName',
                'estimatedValue': 'estimatedValue',
                'probability': 'probability',
                'leadSourceID': 'leadSourceId',
                'businessUnitID': 'businessUnitId',
                'salesProspectStatus': 'salesProspectStatus',
                'isClient': 'isClient',
                'clientID': 'clientId',
                'entityID': 'entityId',
                'orgDataID': 'orgDataId'
                
            };
            // Populate the form fields with the data attributes
            for (const [field, dataAttr] of Object.entries(fieldMappings)) {
                const input = form.querySelector(`input[name="${field}"], select[name="${field}"]`);
                if (input) {
                    input.value = data[dataAttr] || '';
                    // if (input.type === 'checkbox') {
                    //     input.checked = (data[dataAttr] === '1' || data[dataAttr] === 'Y');
                    // }
                    if (input.type === 'radio') {
                        console.log(input);
                        const clientNo = form.querySelector('input[name="isClient"][value="N"]');
                        console.log(clientNo);
                        const clientYes = form.querySelector('input[name="isClient"][value="Y"]');
                        console.log(`clientYes`, clientYes);
                        console.log(`isClient value `, data.isClient);
                        console.log(`clientNo`, clientNo);
                        if (data.isClient === 'N') {
                            clientNo.checked = true;
                            console.log(`clientNo checked`, clientNo.checked);
                            console.log(clientNo);
                        } else if (data.isClient === 'Y') {
                            clientYes.checked= true;
                            console.log(`clientYes checked`, clientYes.checked);
                            console.log(clientYes);
                        }
                       
                    }
                    
                    // Handle the visibility of the prospectCaseName and clientIDDiv based on isClient
                    // show prospectCaseName if is client === N
                    const prospectCaseNameInput = form.querySelector('.prospectNameDiv');
                    if (data.isClient === 'N') {                   
                        if (prospectCaseNameInput) {
                            prospectCaseNameInput.classList.remove('d-none');
                            clientIDDiv.classList.add('d-none');
                        }
                    } else if (data.isClient === 'Y') {
                        clientIDDiv.classList.remove('d-none');
                        prospectCaseNameInput.classList.add('d-none');
                    }
                } else {
                    console.warn(`Input field ${field} not found in the form.`);
                }
            }
            // Handle the client ID field visibility based on isClient checkbox
            
            const isClientCheckbox = form.querySelector('input[name="isClient"]');
            if (isClientCheckbox) {
                isClientCheckbox.checked = (data.isClient === '1' || data.isClient === 'Y');
                clientIDDiv.classList.toggle('d-none_', !isClientCheckbox.checked);
            } else {
                console.warn('isClient checkbox not found in the form.');
            }
            // If you have a select element for clientID, set its value
            const clientIDSelect = form.querySelector('select[name="clientID"]');
            if (clientIDSelect && data.clientId) {
                clientIDSelect.value = data.clientId;
            } else {
                console.warn('Client ID select element not found in the form or no client ID provided.');
            }

            // Fill the textarea with tinyMCE
            tinymce.init({
                selector: '#address'
            });
             // Handle tinyMCE editor
            const editor = tinymce.get('address'); // Make sure 'address' matches your textarea's ID
            if (editor) {
                // Wait for a brief moment to ensure tinyMCE is fully initialized
                setTimeout(() => {
                    editor.setContent(data.address || '');
                }, 100);
            } else {
                console.warn('TinyMCE editor not found for address.');
            }



            // Populate the modal fields with the data attributes
        //     document.querySelector('#manageProspectModal input[name="salesProspectID"]').value = data.salesProspectId;
        //     document.querySelector('#manageProspectModal input[name="salesProspectName"]').value = data.salesProspectName;
        //     document.querySelector('#manageProspectModal input[name="address"]').value = data.address;
        //     document.querySelector('#manageProspectModal input[name="prospectEmail"]').value = data.prospectEmail;
        //     document.querySelector('#manageProspectModal input[name="prospectCaseName"]').value = data.prospectCaseName;
        //     document.querySelector('#manageProspectModal input[name="estimatedValue"]').value = data.estimatedValue;
        //     document.querySelector('#manageProspectModal input[name="probability"]').value = data.probability;
        //     document.querySelector('#manageProspectModal select[name="leadSourceID"]').value = data.leadSourceId;
        //     document.querySelector('#manageProspectModal select[name="businessUnitID"]').value = data.businessUnitId;
        //     document.querySelector('#manageProspectModal select[name="salesProspectStatus"]').value = data.salesProspectStatus;
        //     document.querySelector('#manageProspectModal input[name="isClient"]').checked = (data.isClient === '1');
        //     document.querySelector('#manageProspectModal input[name="clientID"]').value = data.clientId || '';
        });
    });
});
</script>