<?php
$activities = Schedule::tija_activities(array('clientID'=>$clientID), false, $DBConn);
$activityCategories = Schedule::activity_categories([], false, $DBConn);
$projects = Projects::projects_full(['clientID'=>$clientID], false, $DBConn);
$projectPhases = Projects::project_phases_mini([], false, $DBConn);
$projectTasks = Projects::projects_tasks([], false, $DBConn);
$subtasks = Projects::project_subtasks([], false, $DBConn);
$activityTypes =Schedule::tija_activity_types([], false, $DBConn);
$clients = Client::clients_full([], false, $DBConn);
$salesCases = Sales::sales_case_mid(['clientID'=>$clientID], false, $DBConn);
// var_dump($employeeID);
$addresses = Client::client_address(array('clientID'=>$clientDetails->clientID), false, $DBConn);
// var_dump($projects) ;
?>
<div class="my-4">
   <div class="card">
      <div class="card-body">
         <div class="card-header d-flex justify-content-between">
            <h3 class="card-title t300 font-20 clentDetailsTitle d-flex align-items-center">
               Client Details
               <button type="button"
                       class="btn btn-sm btn-link text-primary p-0 ms-2"
                       data-bs-toggle="modal"
                       data-bs-target="#clientDetailsDocModal"
                       title="View Client Details documentation">
                  <i class="ri-information-line fs-18"></i>
               </button>
            </h3>
            <div class="d-flex align-items-center">
               <a href="javascript:void(0);"
                  role="button"
                  aria-expanded="false"
                  aria-controls="manageClientDetails"
                  class="btn btn-primary btn-sm rounded-circle edit-client-details_main fs-22 me-2"
               >
                  <i class="ri-edit-line"></i>
               </a>
            </div>
         </div>
        <?php
        include "includes/scripts/clients/collapse/manage_client_details.php"; ?>
      </div>

   </div>
   <div class="  card card-body my-3">
     <div class="card-header mb-3 pb-0 d-flex justify-content-between align-items-center">
        <h3 class="t300 font-20 mb-0 d-flex align-items-center">
           <span class="text-dark">Client Documents</span>
           <button type="button"
                   class="btn btn-sm btn-link text-primary p-0 ms-2"
                   data-bs-toggle="modal"
                   data-bs-target="#clientDocumentsDocModal"
                   title="View Document Management documentation">
              <i class="ri-information-line fs-18"></i>
           </button>
        </h3>
        <a href="#manageClientDocuments"
           data-bs-toggle="modal"
           role="button"
           aria-expanded="false"
           aria-controls="manageClientDocuments"
           class="btn btn-primary btn-sm rounded-circle"
        >
           <i class="ri-add-line"></i>
        </a>
     </div>
     <?php
      /**
       * Client Document Management Scripts
        * This script handles the management of client documents, including adding, editing, and deleting documents.
        * It includes the modal for managing client documents and the necessary JavaScript to handle the interactions.
        * The script also includes the display of existing client documents with options to edit or delete them.
        * @package    Tija CRM
        * @subpackage Client Document Management
      */
     include_once "includes/scripts/clients/client_document_script.php"; ?>
   </div>

   <div class="card custom-card">
      <div class="card-header">
         <h3 class="card-title t300 font-20 d-flex align-items-center">
            Contacts & addresses
            <button type="button"
                    class="btn btn-sm btn-link text-primary p-0 ms-2"
                    data-bs-toggle="modal"
                    data-bs-target="#contactsAddressesDocModal"
                    title="View Contacts & Addresses documentation">
               <i class="ri-information-line fs-18"></i>
            </button>
         </h3>
      </div>
      <?php include "includes/scripts/clients/client_addresses_contacts_script.php"; ?>
   </div>
</div>
<?php
   include_once "includes/scripts/clients/client_relationship_management_script.php";
   // var_dump($clientRelationships);

   /* * Activity Display Script
      * This script handles the display of activities related to the client.
      * It includes the modal for adding new activities and the necessary JavaScript to handle the interactions.
      * The script also includes the display of existing activities with options to edit or delete them.
      * @package    Tija CRM
      * @subpackage Activity Management
      */
   $addActivity = true;
   include "includes/scripts/work/activity_display_script.php";
?>
