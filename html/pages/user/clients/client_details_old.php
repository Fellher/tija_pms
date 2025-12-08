<?php
if(!$isValidUser) {
   Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
   include "includes/core/log_in_script.php";
   return;
}
// var_dump($userDetails);
$employeeID=(isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) : $userDetails->ID;
$employeeDetails = Data::users(array('ID'=>$employeeID), true, $DBConn);
$allEmployees = Employee::employees([], false, $DBConn);
$employeesCategorised = Employee::categorise_employee($allEmployees, 'jobTitle');

$clientID= isset($_GET['client_id']) ? Utility::clean_string($_GET['client_id']) : '';
$clients= Client::clients(array('Suspended'=>'N'), false, $DBConn);
$clientDetails = Client::clients_full(array( 'clientID'=>$clientID), true, $DBConn);
// var_dump($clientDetails);
$orgDataID= isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : $clientDetails->orgDataID;
$entityID= isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : $clientDetails->entityID;
$orgDataID = $orgDataID;
$industries = data::tija_industry(array(), false, $DBConn);
$industrySectors = data::tija_sectors(array(), false, $DBConn);
// var_dump($industrySectors);
$clientLevels = Client::client_levels(array(), false, $DBConn);

$clientContacts = Client::client_contacts(array('clientID'=>$clientID), false, $DBConn);
$sales = Sales::sales_case_mid(array('clientID'=>$clientID), false, $DBConn);
$projects = Projects::projects_full(array('clientID'=>$clientID), false, $DBConn);
// var_dump($clientDetails);
if(isset($clientDetails->accountOwnerID) && !empty($clientDetails->accountOwnerID)) {
   $accountOwnerName = Core::user_name($clientDetails->accountOwnerID, $DBConn);
   $accountOwner = Core::user(array('ID'=>$clientDetails->accountOwnerID), true, $DBConn);
}
$documentTypes = Data::document_types([], false, $DBConn);
$positionRoles = Admin::tija_job_titles(['Suspended'=>'N'], false, $DBConn);
$contactTypes = Client::contact_types([], false, $DBConn);
$prefixes = Data::prefixes([], false, $DBConn);
$getString .= "&client_id={$clientID}";?>
<script>
   let allEmployees = <?= json_encode($allEmployees) ?>;
</script>
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb">
   <h1 class="page-title fw-medium fs-24 mb-0">
      <?= $clientDetails->clientName ?>
  <a class="text-primary" href="#editClientDetails" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="editClientDetails">
    <i class="ri-pencil-line"></i>
  </a>
  </h1>
   <div class="ms-md-1 ms-0 row">
      <div class="d-flex align-items-center border rounded-3 p-2 border-primary">
         <div class="rounded-circle d-flex justify-content-center align-items-center " style="width: 30px; height: 30px; background-color: #007bff;">
            <span class="text-white">
               <?php
               if(isset($accountOwner) && !empty($accountOwner)) {
                  $initials = Core::user_name_initials($accountOwner);
                  echo $initials;
               } else {
                  $initials = Utility::generate_initials($clientDetails->clientName);
                  echo $initials;
               }?>
            </span>
         </div>
         <div class="ms-2 me-4 " >
            <span class="text-primary"> Account Owner: </span><br/>
            <?=  isset($accountOwnerName) ? $accountOwnerName : "Account Owner" ?>
         </div>
      </div>
   </div>
</div>
<?php
$activityID= isset($_GET['actID']) ? Utility::clean_string($_GET['actID']) : '';
if($activityID){?>
   <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <strong>Activity ID: <?= $activityID ?></strong>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
   </div>
   <div class="container">
      <div class="card custom-card">
         <div class="card-header">
            <div class="card-title">
               <div class="d-flex justify-content-between">
                  <h3 class="t400 mb-0 fs-20 ">Edit Activity</h3>
               </div>
            </div>
         </div>
         <div class="card-body">
         <?php   include "includes\scripts\schedule\modals\manage_activity.php";      ?>
         </div>
      </div>
   </div>
   <?php
   exit;
}?>
<div class="collapse" id="editClientDetails">
   <div class="card card-body">
      <form action="<?= "{$base}php/scripts/clients/manage_clients.php"?>" method="post">
         <div class="row">
            <div class="col-lg-6 col-md-12">
               <div class="row">
                  <div class="form-group col-lg-6 d-none">
                     <label for="clientID" class="text-primary"> Client ID</label>
                     <input type="text" name="clientID" id="clientID" class="form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Client ID" value="<?= $clientDetails->clientID ?>" readonly>
                  </div>
                  <div class="form-group col-lg-2">
                     <label for="clientCode" class="text-primary"> Client Code</label>
                     <input type="text" name="clientCode" id="clientCode" class="form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Client Code" value="<?= $clientDetails->clientCode ?>">
                  </div>

                  <div class="form-group col-lg-10">
                     <label for="clientName" class="text-primary "> Client Name</label>
                     <input type="text" name="clientName" id="clientName" class="form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Client Name" value="<?= $clientDetails->clientName ?>">
                  </div>

                  <div class="form-group col-lg-6">
                     <label for="vatNumber" class="text-primary"> VAT Number</label>
                     <input type="text" name="vatNumber" id="vatNumber" class="form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="VAT Number" value="<?= $clientDetails->vatNumber ?>">
                  </div>
                  <div class="form-group col-lg-6">
                     <label for="clientType" class="text-primary "> Client Type</label>
                     <select name="clientLevelID" id="clientLevelID" class="form-control-sm form-control-plaintext bg-light-blue px-2">
                        <?php echo Form::populate_select_element_from_object($clientLevels, 'clientLevelID', 'clientLevelName', $clientDetails->clientLevelID, '' , 'Select Client Type') ?>
                     </select>
                  </div>
                  <div class="form-group col-lg-6">
                     <label for="clientIndustry" class="text-primary "> Client Industry</label>
                     <input type="text" name="clientIndustryID" id="clientIndustryID" class="form-control-sm form-control-plaintext bg-light-blue px-2 d-none" placeholder="Client Industry" value="<?= $clientDetails->clientIndustryID ?>">
                     <button type="button" class="rounded btn btn-sm btn-info-light  bg-light-blue dropdown-toggle d-flex align-items-center w-100" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="text-primary d-block selectedName"> <?= $clientDetails->clientIndustryID ? $clientDetails->industryName." - (". $clientDetails->sectorName .")" : 'Select  Industry'; ?> </span>

                     </button>
                     <ul class="dropdown-menu dropdown-menu-end">
                        <?php
                        if($industrySectors){
                           foreach ($industrySectors as $key => $sector) {
                              $active= $clientDetails->clientIndustryID == $sector->sectorID ? ' activeDay ' : '';
                              $industries = Data::tija_industry(array('sectorID'=>$sector->sectorID), false, $DBConn);
                              ?>
                              <li>
                                 <h5 class="dropdown-header <?= $active ?>"  data-id="<?= $sector->sectorID ?>" data-name="<?= $sector->sectorName ?>" data-type="sector" data-clientid="<?= $clientDetails->clientID ?>">
                                    <?= $sector->sectorName ?>
                                 </h5>
                                 <?php
                                 if($industries){
                                    foreach ($industries as $key => $industry) {
                                       $active= $clientDetails->clientIndustryID == $industry->industryID ? ' activeDay ' : '';
                                       ?>
                                       <a class="dropdown-item industryID ms-3 <?= $active ?>" data-id="<?= $industry->industryID ?>" data-name="<?= $industry->industryName ?>" data-type="industry" data-clientid="<?= $clientDetails->clientID ?>">
                                          <?= $industry->industryName ?>
                                       </a>
                                       <?php
                                    }
                                 }?>
                              </li>
                              <?php
                           }
                        }?>
                        <script>
                           document.querySelectorAll('.industryID').forEach(item => {
                              item.addEventListener('click', event => {
                                 // get all data attributes
                                 const data = item.dataset;
                                 console.log(data);
                                 const selectedName = item.getAttribute('data-name');
                                 const selectedID = item.getAttribute('data-id');
                                 const clientID = item.getAttribute('data-clientid');
                                 const type = item.getAttribute('data-type');
                                 const clientIndustryID = document.querySelector('#clientIndustryID');
                                 const selectedNameElement = document.querySelector('.selectedName');

                                 if(type == 'sector') {
                                    clientIndustryID.value = selectedID;
                                    selectedNameElement.innerHTML = selectedName;
                                 } else {
                                    clientIndustryID.value = selectedID;
                                    selectedNameElement.innerHTML = selectedName;
                                 }
                              })
                           });
                        </script>
                     </ul>

                  </div>

                  <div class="form-group col-lg-6">
                     <label for="" class="text-primary "> Client Owner Name</label>
                     <select name="accountOwnerID" id="accountOwnerID" class="form-control-sm form-control-plaintext bg-light-blue px-2">
                        <?php echo Form::populate_select_element_from_object($allEmployees, 'ID', 'employeeName', $clientDetails->accountOwnerID, '' , 'Select Case Owner') ?>
                     </select>
                  </div>
               </div>

            </div>
            <div class="col-lg-6 col-md-12">
               <div class="form-group my-2">
                  <label for="client_description"> Client Description</label>
                  <textarea name="clientDescription" id="clientDescription" class="borderless-mini" ><?= $clientDetails->clientDescription ?></textarea>
               </div>
            </div>
            <div class="col-12">
               <button type="submit" class="btn btn-primary btn-sm float-end">Save Changes</button>
            </div>
         </div>
      </form>
   </div>
</div>


<?php
$linkList = array(
    (object)[
        "title" => "Client Information (KYC)",
        "link" => "contact_info.php",
        "id" => "contact_info",
        "adminlevel" => 1
    ],
    (object)[
        "title" => "Sales & Projects",
        "link" => "sales_projects.php",
        "id" => "sales_projects",
        "adminlevel" => 2
    ],
    (object)[
        "title" => "Collaborations",
        "link" => "collaborations.php",
        "id" => "collaborations",
        "adminlevel" => 3
    ],
    (object)[
        "title" => "Financials",
        "link" => "financials.php",
        "id" => "financials",
        "adminlevel" => 4
    ]
);
$page = (isset($_GET['page']) && !empty($_GET['page'])) ? Utility::clean_string($_GET['page']) : 'contact_info';

	$getString = str_replace("&page={$page}", "", $getString);
?>
<div class="container-fluid bg-white border-0 rounded-3">
   <div class="col-md-12 my-4">
      <div class="bg-white" >
         <div class="row">
            <?php
            if ($linkList){
               $textColor= '';
               foreach ($linkList as $key => $link) {
                  $active= $page == $link->id ? ' activeDay ' : '';
                  $adminLevel= $link->adminlevel;?>
                  <a class="font-primary  border-end col-md kpi-item " href="<?php echo $base ."html/{$getString}&page=".$link->id; ?>" >
                     <div class="col-md-12 sales-barner py-4 d-flex align-items-center justify-content-between">
                        <h4 class="mb-0 t300 font-22"> <?= $link->title ?> </h4>
                     </div>
                  </a>
                  <?php
               }
            }?>
            <div class="col-md float-end center">
               <div class="d-flex justify-content-center align-items-center pt-3">
                  <a href="">
                     <i class="ti ti-3d-cube-sphere font-lg text-secondary"></i>
                  </a>
               </div>
            </div>

         </div>
      </div>
   </div>
</div>

<?php
// define array of valid pages
$validPages = array(
   'contact_info',
   'sales_projects',
   'collaborations',
   'financials'
);
// check if page is valid
if (in_array($page, $validPages)) {
   // include the page
   include "includes/scripts/clients/{$page}.php";
} else {
   // include default page
    include "includes/scripts/clients/contact_info.php";
}

 $getString .= "&page={$page}";
 ?>

<!-- ============================================================================
     CLIENT DETAILS DOCUMENTATION MODAL
     ============================================================================ -->
<div class="modal fade" id="clientDetailsDocModal" tabindex="-1" aria-labelledby="clientDetailsDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="clientDetailsDocModalLabel">
                    <i class="ri-edit-line me-2"></i>
                    Editing Client Details Guide
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <p class="text-muted">
                        Learn how to edit and update client information including basic details, industry classification,
                        and account ownership.
                    </p>
                </div>

                <div class="card border-primary-transparent mb-3">
                    <div class="card-body">
                        <h6 class="text-primary mb-3">
                            <i class="ri-pencil-line me-2"></i>
                            How to Edit Client Information
                        </h6>
                        <ol class="mb-0">
                            <li class="mb-2">
                                <strong>Access Edit Mode:</strong> Click the pencil icon (<i class="ri-pencil-line"></i>)
                                next to the client name at the top of the page, or click the edit button in the "Client Details" card.
                            </li>
                            <li class="mb-2">
                                <strong>Update Information:</strong> Modify any of the following fields:
                                <ul class="mt-2">
                                    <li><strong>Client Code:</strong> Unique identifier for the client</li>
                                    <li><strong>Client Name:</strong> Official name of the client organization</li>
                                    <li><strong>VAT Number:</strong> Tax identification number</li>
                                    <li><strong>Client Type:</strong> Classification level (e.g., Premium, Standard)</li>
                                    <li><strong>Client Industry:</strong> Select from industry sectors and specific industries</li>
                                    <li><strong>Account Owner:</strong> Assign the employee responsible for managing this client</li>
                                    <li><strong>Client Description:</strong> Additional notes and information about the client</li>
                                </ul>
                            </li>
                            <li class="mb-2">
                                <strong>Select Industry:</strong> Click the industry dropdown to browse by sector, then select
                                the specific industry. The selection will update automatically.
                            </li>
                            <li class="mb-2">
                                <strong>Save Changes:</strong> Click the "Save Changes" button at the bottom of the form to
                                apply your updates.
                            </li>
                        </ol>
                    </div>
                </div>

                <div class="alert alert-info mb-0">
                    <h6 class="alert-heading">
                        <i class="ri-lightbulb-line me-2"></i>
                        Tips
                    </h6>
                    <ul class="mb-0 small">
                        <li>Keep client information up to date for accurate reporting</li>
                        <li>Use the description field to add important notes about the client</li>
                        <li>Ensure the Account Owner is correctly assigned for proper accountability</li>
                        <li>Select the appropriate industry for better categorization and reporting</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================================
     CLIENT DOCUMENTS DOCUMENTATION MODAL
     ============================================================================ -->
<div class="modal fade" id="clientDocumentsDocModal" tabindex="-1" aria-labelledby="clientDocumentsDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="clientDocumentsDocModalLabel">
                    <i class="ri-file-text-line me-2"></i>
                    Document Management Guide
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <p class="text-muted">
                        Learn how to upload, manage, and organize client documents with information about supported file types
                        and formats.
                    </p>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="card border-info-transparent h-100">
                            <div class="card-body">
                                <h6 class="text-info">
                                    <i class="ri-upload-cloud-line me-2"></i>
                                    Adding Documents
                                </h6>
                                <ol class="small mb-0">
                                    <li>Click the <i class="ri-add-line"></i> button in the "Client Documents" section</li>
                                    <li>Fill in the document name and description</li>
                                    <li>Select the document type from the dropdown</li>
                                    <li>Choose the file to upload</li>
                                    <li>Click "Save Client Documents"</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-success-transparent h-100">
                            <div class="card-body">
                                <h6 class="text-success">
                                    <i class="ri-edit-box-line me-2"></i>
                                    Editing Documents
                                </h6>
                                <ol class="small mb-0">
                                    <li>Click the edit icon (<i class="ri-pencil-line"></i>) on any document card</li>
                                    <li>Update the document name, description, or type</li>
                                    <li>Optionally upload a new file to replace the existing one</li>
                                    <li>Save your changes</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <h6 class="alert-heading">
                        <i class="ri-file-settings-line me-2"></i>
                        Allowed Document Types and Formats
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Document Formats:</strong>
                            <ul class="mb-0 small">
                                <li><strong>PDF:</strong> .pdf (Portable Document Format)</li>
                                <li><strong>Word:</strong> .doc, .docx (Microsoft Word)</li>
                                <li><strong>Excel:</strong> .xls, .xlsx (Microsoft Excel)</li>
                                <li><strong>PowerPoint:</strong> .ppt, .pptx (Microsoft PowerPoint)</li>
                                <li><strong>Text:</strong> .txt, .csv (Plain text and CSV files)</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <strong>Image Formats:</strong>
                            <ul class="mb-0 small">
                                <li><strong>Images:</strong> .jpg, .jpeg, .png, .gif</li>
                                <li><strong>Archives:</strong> .zip, .rar, .gz, .tgz</li>
                            </ul>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <strong class="text-warning">
                                <i class="ri-alert-line me-1"></i>
                                File Size Limit:
                            </strong>
                            Maximum file size is <strong>10 MB</strong> per document.
                            For larger files, consider compressing them or splitting into multiple documents.
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <strong>Common Document Types:</strong>
                            <ul class="mb-0 small">
                                <li>Registration Certificates</li>
                                <li>Tax Identification Documents</li>
                                <li>Business Licenses</li>
                                <li>Identity Documents (for authorized signatories)</li>
                                <li>Bank Statements</li>
                                <li>Contracts and Agreements</li>
                                <li>Compliance Certificates</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="alert alert-success mb-0">
                    <h6 class="alert-heading">
                        <i class="ri-lightbulb-line me-2"></i>
                        Best Practices
                    </h6>
                    <ul class="mb-0 small">
                        <li>Upload important documents immediately after receiving them</li>
                        <li>Use descriptive names for documents to make them easy to find</li>
                        <li>Ensure document file names are clear and descriptive before uploading</li>
                        <li>Select the appropriate document type for better organization</li>
                        <li>Add descriptions to provide context about the document</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================================
     CONTACTS & ADDRESSES DOCUMENTATION MODAL
     ============================================================================ -->
<div class="modal fade" id="contactsAddressesDocModal" tabindex="-1" aria-labelledby="contactsAddressesDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="contactsAddressesDocModalLabel">
                    <i class="ri-contacts-line me-2"></i>
                    Contacts & Addresses Management Guide
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <p class="text-muted">
                        Learn how to manage client addresses and contacts, including adding, editing, and organizing
                        multiple addresses and contact persons.
                    </p>
                </div>

                <!-- Address Management -->
                <div class="mb-4">
                    <h6 class="text-primary mb-3">
                        <i class="ri-map-pin-line me-2"></i>
                        Managing Client Addresses
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-primary-transparent h-100">
                                <div class="card-body">
                                    <h6 class="text-primary">
                                        <i class="ri-add-circle-line me-2"></i>
                                        Adding a New Address
                                    </h6>
                                    <ol class="small mb-0">
                                        <li>Click the <i class="ri-add-line"></i> button in the "Address" section</li>
                                        <li>Fill in the address details:
                                            <ul>
                                                <li>Complete street address</li>
                                                <li>City</li>
                                                <li>Postal/ZIP code</li>
                                                <li>Country</li>
                                            </ul>
                                        </li>
                                        <li>Select the address type:
                                            <ul>
                                                <li><strong>Office Address:</strong> Business location</li>
                                                <li><strong>Postal Address:</strong> Mailing address</li>
                                            </ul>
                                        </li>
                                        <li>Set address flags:
                                            <ul>
                                                <li><strong>Billing Address:</strong> For invoicing purposes</li>
                                                <li><strong>Headquarters:</strong> Main business location</li>
                                            </ul>
                                        </li>
                                        <li>Click "Save Primary Contact" to save</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-success-transparent h-100">
                                <div class="card-body">
                                    <h6 class="text-success">
                                        <i class="ri-edit-box-line me-2"></i>
                                        Editing Addresses
                                    </h6>
                                    <ol class="small mb-0">
                                        <li>Click the edit icon (<i class="ri-pencil-line"></i>) on the address card</li>
                                        <li>Update any address fields as needed</li>
                                        <li>Modify address type or flags if required</li>
                                        <li>Save your changes</li>
                                    </ol>
                                    <div class="mt-3 p-2 bg-light rounded">
                                        <strong class="text-muted small">Note:</strong>
                                        <p class="mb-0 small text-muted">
                                            You can have multiple addresses per client. Only one address can be marked as
                                            "Headquarters" and "Billing Address" at a time.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Management -->
                <div class="mb-4">
                    <h6 class="text-primary mb-3">
                        <i class="ri-user-line me-2"></i>
                        Managing Client Contacts
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-info-transparent h-100">
                                <div class="card-body">
                                    <h6 class="text-info">
                                        <i class="ri-user-add-line me-2"></i>
                                        Adding a New Contact
                                    </h6>
                                    <ol class="small mb-0">
                                        <li>Click the <i class="ti ti-user-plus"></i> button in the "Contacts" section</li>
                                        <li>Fill in contact information:
                                            <ul>
                                                <li>First Name and Last Name</li>
                                                <li>Title/Position</li>
                                                <li>Email address</li>
                                                <li>Phone number</li>
                                                <li>Salutation (Mr., Mrs., Dr., etc.)</li>
                                            </ul>
                                        </li>
                                        <li>Select the contact type/role:
                                            <ul>
                                                <li>Primary Contact</li>
                                                <li>Billing Contact</li>
                                                <li>Technical Contact</li>
                                                <li>Decision Maker</li>
                                                <li>Other roles as defined</li>
                                            </ul>
                                        </li>
                                        <li>Associate with an address (optional)</li>
                                        <li>Click "Save Client Contact"</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-warning-transparent h-100">
                                <div class="card-body">
                                    <h6 class="text-warning">
                                        <i class="ri-user-settings-line me-2"></i>
                                        Editing Contacts
                                    </h6>
                                    <ol class="small mb-0">
                                        <li>Click the edit icon on any contact card</li>
                                        <li>Update contact details as needed</li>
                                        <li>Change contact type or role if required</li>
                                        <li>Update associated address if needed</li>
                                        <li>Save your changes</li>
                                    </ol>
                                    <div class="mt-3 p-2 bg-light rounded">
                                        <strong class="text-muted small">Best Practices:</strong>
                                        <ul class="mb-0 small text-muted">
                                            <li>Keep contact information up to date</li>
                                            <li>Assign appropriate contact types for easy filtering</li>
                                            <li>Link contacts to relevant addresses when applicable</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-success mb-0">
                    <h6 class="alert-heading">
                        <i class="ri-lightbulb-line me-2"></i>
                        Tips
                    </h6>
                    <ul class="mb-0 small">
                        <li>Mark the correct address as "Headquarters" for accurate location data</li>
                        <li>Assign appropriate contact types to enable filtering and targeted communication</li>
                        <li>Keep all contact information current for effective communication</li>
                        <li>Link contacts to addresses when they work at specific locations</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================================
     CLIENT RELATIONSHIPS DOCUMENTATION MODAL
     ============================================================================ -->
<?php
// Get client relationship types from config
$clientRelationshipTypes = isset($config['clientRelationshipTypes']) ? $config['clientRelationshipTypes'] : [];
?>
<div class="modal fade" id="clientRelationshipsDocModal" tabindex="-1" aria-labelledby="clientRelationshipsDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="clientRelationshipsDocModalLabel">
                    <i class="ri-team-line me-2"></i>
                    Client Relationships (Escalation Matrix) Guide
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <p class="text-muted">
                        Learn how to manage client relationships and set up the escalation matrix to ensure proper
                        communication channels and accountability within your organization.
                    </p>
                </div>

                <div class="card border-primary-transparent mb-3">
                    <div class="card-body">
                        <h6 class="text-primary mb-2">
                            <i class="ri-add-line me-2"></i>
                            Adding a Relationship
                        </h6>
                        <ol class="mb-0">
                            <li>Click the "Add Relationship" button (<i class="ti ti-user-plus"></i>) in the Relationships section</li>
                            <li>Select the employee/team member to assign</li>
                            <li>Choose the relationship type from the available options</li>
                            <li>Save the relationship</li>
                        </ol>
                    </div>
                </div>

                <div class="card border-success-transparent mb-3">
                    <div class="card-body">
                        <h6 class="text-success mb-2">
                            <i class="ri-edit-line me-2"></i>
                            Editing Relationships
                        </h6>
                        <ol class="mb-0">
                            <li>Click the edit icon (<i class="ri-pencil-line"></i>) on any relationship card</li>
                            <li>Update the relationship details</li>
                            <li>Modify the relationship type or employee assignment as needed</li>
                            <li>Save your changes</li>
                        </ol>
                    </div>
                </div>

                <!-- Available Relationship Types -->
                <div class="mb-3">
                    <h6 class="text-primary mb-3">
                        <i class="ri-list-check me-2"></i>
                        Available Relationship Types
                    </h6>
                    <?php if (!empty($clientRelationshipTypes)): ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th>Relationship Type</th>
                                    <th>Level</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Sort by level for better display
                                $sortedTypes = $clientRelationshipTypes;
                                usort($sortedTypes, function($a, $b) {
                                    return (int)$a->level - (int)$b->level;
                                });

                                foreach ($sortedTypes as $type):
                                    $levelDescriptions = [
                                        '1' => 'Highest level - Partner responsible for client liaison',
                                        '2' => 'Partner level - Manages engagement with client',
                                        '3' => 'Management level - Oversees client operations',
                                        '4' => 'Associate level - Handles day-to-day client work',
                                        '5' => 'Entry level - Intern or junior associate',
                                        '6' => 'General - All employees can be assigned'
                                    ];
                                    $description = isset($levelDescriptions[$type->level]) ? $levelDescriptions[$type->level] : 'Client relationship assignment';
                                ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($type->value) ?></strong></td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-transparent">Level <?= htmlspecialchars($type->level) ?></span>
                                    </td>
                                    <td class="small text-muted"><?= htmlspecialchars($description) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="ri-alert-line me-2"></i>
                        No relationship types configured. Please contact your administrator.
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Relationship Level Hierarchy -->
                <div class="mb-3">
                    <h6 class="text-primary mb-3">
                        <i class="ri-hierarchy me-2"></i>
                        Escalation Hierarchy
                    </h6>
                    <div class="card border-info-transparent">
                        <div class="card-body">
                            <p class="mb-2 small text-muted">
                                Relationship types are organized by hierarchy levels. When assigning relationships,
                                the system will filter available employees based on their job titles and the relationship level:
                            </p>
                            <ul class="mb-0 small">
                                <li><strong>Level 1-2:</strong> Partners and Directors</li>
                                <li><strong>Level 3:</strong> Managers, Senior Managers, and Directors</li>
                                <li><strong>Level 4:</strong> Associates and Senior Associates</li>
                                <li><strong>Level 5:</strong> Interns</li>
                                <li><strong>Level 6:</strong> All employees (no restriction)</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning mb-0">
                    <h6 class="alert-heading">
                        <i class="ri-information-line me-2"></i>
                        Escalation Matrix Best Practices
                    </h6>
                    <ul class="mb-0 small">
                        <li>Always assign a <strong>Client Liaison Partner</strong> (Level 1) for primary client contact</li>
                        <li>Assign an <strong>Engagement Partner</strong> (Level 2) to manage the engagement</li>
                        <li>Use <strong>Manager</strong> (Level 3) for operational oversight</li>
                        <li>Assign <strong>Associates</strong> (Level 4-5) for day-to-day client work</li>
                        <li>Follow the hierarchy levels for proper escalation paths</li>
                        <li>Review and update relationships regularly to ensure accuracy</li>
                        <li>Ensure appropriate employees are assigned based on their job titles and relationship levels</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>
                    Close
                </button>
            </div>
        </div>
    </div>
</div>