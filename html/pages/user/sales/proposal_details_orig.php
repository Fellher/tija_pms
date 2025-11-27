<?php 
if(!$isValidUser) {
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}
 
$proposalID= (isset($_GET['prID']) && !empty($_GET['prID'])) ? Utility::clean_string($_GET['prID']) : ((isset($_GET['proposalID']) && !empty($_GET['proposalID'])) ? Utility::clean_string($_GET['proposalID']) : 0);
$getString.= "&prID={$proposalID}";

$proposalDetails = Sales::proposal_full(array('proposalID'=>$proposalID), true, $DBConn);
if(!$proposalDetails) {
  Alert::info("Proposal not found", true, array('fst-italic', 'text-center', 'font-18'));
  return;
}
$salesCases = Sales::sales_cases(array('orgDataID'=>$proposalDetails->orgDataID, 'entityID'=>$proposalDetails->entityID), false, $DBConn);
$clients = Client::clients(array('orgDataID'=>$proposalDetails->orgDataID, 'entityID'=>$proposalDetails->entityID), false, $DBConn);
$proposalStatuses = Sales::proposal_statuses(array('orgDataID'=>$proposalDetails->orgDataID, 'entityID'=>$proposalDetails->entityID), false, $DBConn);
$employeeID=(isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) : $userDetails->ID;
$entityID= $_SESSION['entityID'] ? $_SESSION['entityID'] :null;
$orgDataID = $_SESSION['orgDataID'] ? $_SESSION['orgDataID']  : null;
$employeeList = Employee::employees(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
$checklistStatuses = Proposal::proposal_checklist_status(['proposalChecklistStatusType'=>'checkListItem'], false, $DBConn);
$checklistItems = Proposal::proposal_checklist_items([], false, $DBConn);
$checklistItemCategories = Proposal::proposal_checklist_items_categories([], false, $DBConn);

$employeesCategorised = Employee::categorise_employee($employeeList, 'jobTitle');
$checkListStatus = Proposal::proposal_checklist_status([], false, $DBConn);
//var_dump($employeesCategorised);
// 
// var_dump($proposalDetails);
?>

<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb border-bottom">
   <h1 class="page-title fw-medium fs-24 mb-0"><?= "{$proposalDetails->proposalTitle} " ?> </h1>
   <div class="ms-md-1 ms-0">     
      <div class="ms-md-1 ms-0 row">
         <div class="d-flex align-items-center border rounded-3 p-2 border-primary">         
            <div class="rounded-circle d-flex justify-content-center align-items-center " style="width: 30px; height: 30px; background-color: #007bff;">
               <span class="text-white">
                  <?php
                  if(isset($proposalDetails->userInitials) && !empty($proposalDetails->userInitials)) {
                    
                     echo $proposalDetails->userInitials;
                  } else {
                     $userDetails->userInitials ? $userDetails->userInitials : "NA";
                  }
               ?>
               </span>
            </div>
            <div class="ms-2 me-4 " >
               <span class="text-primary"> Account Owner: </span><br/> 
               <?=  isset($employeeDetails->employeeName) ? $employeeDetails->employeeName : "Account Owner" ?>
            </div>
         </div>
      </div>
   </div> 
</div>

<div class="container-fluid">
   <div class="card card-body col-md-12 my-4 shadow-lg">
      <div class="card   alert  alert-dismissible fade show border-0" role="alert">
         <div class="row">
            <div  class="d-flex  align-items-center justify-content-between mb-4 border-bottom">
               <h4 class="mb-0 t300 font-22">Proposal Basic Information</h4>
               <div class=" border-end">							
                  <div class="font-22">
                     <span class="font-14">KES</span>
                     <span class=" me-3"> <?php echo  isset($proposalDetails->proposalValue) ? number_format($proposalDetails->proposalValue, 2, '.', ' ')  : 0 ?></span>                   
                     <?php
                        if($proposalDetails->proposalStatusID == 1) {
                           echo "<span class='badge bg-info'>{$proposalDetails->proposalStatusName}</span>";
                        } else if($proposalDetails->proposalStatusID == 2) {
                           echo "<span class='badge bg-danger'>{$proposalDetails->proposalStatusName}</span>";
                        } else if($proposalDetails->proposalStatusID == 3) {
                           echo "<span class='badge bg-warning'>{$proposalDetails->proposalStatusName}</span>";
                        } else if($proposalDetails->proposalStatusID == 4) {
                           echo "<span class='badge bg-success'>{$proposalDetails->proposalStatusName}</span>";
                        }
                     ?>
                     <button type="button" class="btn  btn-icon rounded-pill btn-primary-light" data-bs-toggle="modal" data-bs-target="#manageProposalModal">
                        <i class="ri-pencil-line"></i>
                     </button>
                  </div>                  
               </div>
            </div>

            <div class="col-md-12">
               <form class="card card-body col-md-12 my-4 bg-light" id="proposalDetailsForm" action="<?= $base ?>php/scripts/sales/manage_proposal.php" method="POST">
                  <div class="row">    
                     <input    type="hidden" name="proposalID" value="<?= $proposalDetails->proposalID ?>">
                     <input    type="hidden" name="orgDataID" value="<?= $proposalDetails->orgDataID ?>">
                     <input    type="hidden" name="entityID" value="<?= $proposalDetails->entityID ?>">
                     <input    type="hidden" name="employeID" value="<?= $proposalDetails->employeeID ?>">
                     <div class="form-group col-md-4">
                        <label for="proposalTitle" class="text-primary"> Proposal title</label>
                        <input type="text" class=" form-control-sm form-control-plaintext border-bottom" id="proposalTitle" name="proposalTitle" value="<?= $proposalDetails->proposalTitle ?>" readonly>
                     </div>
                     <div class="form-group col-md-4">
                        <label for="clientID" class="text-primary"> Client Name</label>
                        <select class="form-control-sm form-control-plaintext border-bottom" id="clientID" name="clientID" readonly>
                           <option value="<?= $proposalDetails->clientID ?>"><?= $proposalDetails->clientName ?></option>
                           <?php
                              if($clients) {
                                 foreach ($clients as $client) {?>
                                    <option value="<?php echo $client->clientID; ?>"><?php echo $client->clientName; ?></option>
                                 <?php
                                 }
                              }?>
                        </select>
                     </div>
                     <div class="form-group col-md-4">
                        <label for="salesCaseID" class="text-primary"> Sales Case Name</label>
                        <select class="form-control-sm form-control-plaintext border-bottom" id="salesCaseID" name="salesCaseID" readonly>                          
                           <?= Form::populate_select_element_from_object($salesCases, 'salesCaseID', 'salesCaseName', isset($proposalDetails->salesCaseID) ? $proposalDetails->salesCaseID :'','','Select Sales Case') ?>
                        </select>
                     </div>

                     <div class="form-group col-md-4 my-2">
                        <label for="proposalDeadline" class="text-primary"> Proposal Deadline</label>
                        <input type="date" class=" form-control-sm form-control-plaintext border-bottom date" id="proposalDeadline" name="proposalDeadline" value="<?= $proposalDetails->proposalDeadline ?>" readonly>
                     </div>

                     <div class="form-group col-md-4 my-2">
                        <label for="proposalValue" class="text-primary"> Proposal Value</label>
                        <input type="text" class=" form-control-sm form-control-plaintext border-bottom" id="proposalValue" name="proposalValue" value="<?= $proposalDetails->proposalValue ?>" readonly>
                     </div>

                     <div class="form-group col-md-4 my-2">
                        <label for="proposalStatusID" class="text-primary"> Proposal Status</label>
                        <select class="form-control-sm form-control-plaintext border-bottom" id="proposalStatusID" name="proposalStatusID" readonly>                
                           <?php
                              if($proposalStatuses) {
                                 foreach ($proposalStatuses as $status) {?>
                                    <option value="<?php echo $status->proposalStatusID; ?>"><?php echo $status->proposalStatusName; ?></option>
                                 <?php
                                 }
                              }?>
                        </select>
                     </div>

                     <div class="form-group col-md-6 my-2">
                        <label for="proposalDescription" class="text-primary"> Proposal Description</label>
                        <textarea class="form-control-sm form-control-plaintext border-bottom" id="proposalDescription" name="proposalDescription" rows="3" readonly><?= $proposalDetails->proposalDescription ?></textarea>
                     </div>

                     <div class="fom-group col-md-6 my-2" class="text-primary">
                        <label for="proposalComments" class="text-primary"> Proposal Comments</label>
                        <textarea class="form-control-sm form-control-plaintext border-bottom" id="proposalComments" name="proposalComments" rows="3" readonly><?= $proposalDetails->proposalComments ?></textarea>
                     </div>
                     
                     <!-- <div class="form-group col-md-6 my-2">
                        <label for="proposalFile" class="text-primary"> TOR/RFQ File</label>
                        <div class=" row d-flex align-items-center " >
                           <div class="col-md-2">
                              <?php
                                 if($proposalDetails->proposalFile) {
                                    echo "<a href='{$base}html/?s={$s}&ss={$ss}&p=download&file={$proposalDetails->proposalFile}' class='text-primary fs-22 ' download>
                                    <i class='fas fa-download'></i>

                                    </a>";
                                 } else {
                                    echo "No file uploaded";
                                 }
                              ?>
                           </div>
                           <div class="col-md-10">
                           <input type="file" class="form-control form-control-sm  border-bottom col-10" id="proposalFile" name="proposalFile" value="" >
                           </div>
                          
                        </div>
                     </div> -->
                     <!-- <div class="form-group col-md-6 my-2">
                        <label for="proposalFile" class="text-primary"> Proposal File</label>
                        <div class=" row d-flex align-items-center " >
                           <div class="col-md-2">
                              <?php
                                 if($proposalDetails->proposalFile) {
                                    echo "<a href='{$base}html/?s={$s}&ss={$ss}&p=download&file={$proposalDetails->proposalFile}' class='text-primary fs-22 ' download>
                                    <i class='fas fa-download'></i>
                                    </a>";
                                 } else {
                                    echo "No file uploaded";
                                 }
                              ?>
                           </div>
                           <div class="col-md-10">
                              <input type="file" class="form-control form-control-sm  border-bottom col-10" id="proposalFile" name="proposalFile" value="" >
                           </div>                          
                        </div>
                     </div> -->
                     <div class="form-group col-md-12 my-2">
                        <button type="submit" class="btn btn-primary btn-sm float-end">
                           Save
                        </button>
                     </div>

                  </input>
               </form>
            </div>
            <div class="col-12">
               <div class="col-md border rounded-3 p-2  bg-white shadow-sm">
                  <h4 class="text-primary d-flex align-items-center justify-content-between border-bottom pb-2 mb-2 " >
                     <span class="font-22 t300">Proposal Attachments</span>
                     <button type="button" class="btn btn-primary btn-sm float-end" data-bs-toggle="modal" data-bs-target="#manageProposalAttachmentModal">
                        <i class="ri-add-line"></i>
                     </button>
                  </h4>
                  <?php
                  $proposalAttachments = Proposal::proposal_attachments(array('proposalID'=>$proposalID), false, $DBConn);
                  if($proposalAttachments){
                     // var_dump($proposalAttachments);
                     foreach($proposalAttachments as $attachment){
                        // check file attachment extension
                        $fileExtension = pathinfo($attachment->proposalAttachmentFile, PATHINFO_EXTENSION);
                        if($fileExtension == 'pdf'){
                           $icon = '<i class="ri-file-pdf-line"></i>';                              
                        } elseif($fileExtension == 'docx'){
                           $icon = '<i class="ri-file-word-line"></i>';
                        } elseif($fileExtension == 'doc'){
                           $icon = '<i class="ri-file-word-line"></i>';
                        } elseif($fileExtension == 'xls'){
                           $icon = '<i class="ri-file-excel-line"></i>';
                        } elseif($fileExtension == 'png'){
                           $icon = '<i class="ri-image-line"></i>';
                        } elseif($fileExtension == 'jpg'){
                           $icon = '<i class="ri-image-line"></i>';
                        } elseif($fileExtension == 'jpeg'){
                           $icon = '<i class="ri-image-line"></i>';
                        } else{
                           $icon = '<i class="ri-file-line"></i>';
                        }
                        ?>

                        <div class="alert alert-img alert-outline-primary alert-dismissible fase show  flex-wrap" role="alert">
                           <div class="d-flex align-items-center justify-content-between w-100">
                              <div>
                                 <span class="avatar bd-blue-800 avatar-xs me-2 avatar-rounded">
                                    <AC>
                                       <?= $icon ?>
                                    </AC>
                                 </span>
                                 <?= $attachment->proposalAttachmentName ?>
                                 <?php
                                 $proposalAttachmentFile= str_replace(" ", "%20", $attachment->proposalAttachmentFile);
                                 $attachment->proposalAttachmentFile = ltrim($proposalAttachmentFile, '/');

                                 // echo "<br/> <small class='text-muted fst-italic'>Uploaded by: {$attachment->uploadedByName} on {$attachment->proposalAttachmentDate}</small>";
                                 $proposalAttachmentFile =$config['DataDir'].''.$attachment->proposalAttachmentFile; 
                                 ?>
                              </div>
                              <a type="button" target="_blank" href="<?= "{$config['DataDir']}{$attachment->proposalAttachmentFile}" ?>" class="btn  btn-icon rounded-pill btn-secondary-light float-end" >
                                 <i class="ti ti-cloud-download fs-20"></i>
                              </a>
                           </div>                                
                        </div>
                           <?php
                     }
                  } else {
                     echo "<div class='alert alert-info'>No proposal attachments found</div>";
                  }?>
               </div>
            </div>
         </div>
      </div>
   </div>
   <?php
   // proposal Attachments modal
   echo Utility::form_modal_header("manageProposalAttachmentModal", "sales/proposal_attachments/manage_proposal_attachment.php", "Proposal Attachment", array('modal-lg', 'modal-dialog-centered'), $base);
   include_once("html/includes/scripts/sales/proposal_attachments/manage_proposal_attachment.php");
   echo Utility::form_modal_footer('Save Proposal Attachment', 'saveProposalAttachment',  ' btn btn-success btn-sm', true);

  /* if($isAdmin || $isValidAdmin) {?>
      <div class="card card-body col-md-12 my-4 shadow-lg">
         <div class="card   alert  alert-dismissible fade show border-0" role="alert">
         <div class="row">      
            <?php
            $proposalChecklistPages = array(
               (object)[
                  "title" => "checklist Status",
                  "link" => "checklist_status.php",
                  "id" => "checklist_status",
                  "adminlevel" => 4
               ],
               (object)[
                  "title" => "checklist Items",
                  "link" => "checklist_items.php",
                  "id" => "checklist_items",
                  "adminlevel" => 4
               ],
               (object)[
                  "title" => "checklist Item Categories",
                  "link" => "checklist_item_categories.php",
                  "id" => "checklist_item_categories",
                  "adminlevel" => 4
               ],
            ); 
            // //var_dump($getString);
            $page = (isset($_GET['page']) && !empty($_GET['page'])) ? Utility::clean_string($_GET['page']) : 'checklist_status';
            $getString = str_replace("&page={$page}", "", $getString);?>
                  
            <div class="d-flex align-items-center justify-content-between mb-3 " >
               <h4 class="mb-0 t300 font-22">Proposal Checklist Settings</h4>
               <div class=" border-end">							
                  <div class="font-22">
                     <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manageChecklistItemModal">
                        Add Checklist Item
                     </button>
                  </div>                  
               </div>
            </div>
            <div class="col-12 bg-light-blue py-2 text-end ">    
               <?php foreach($proposalChecklistPages as $pageItem): ?>
                  <a href="<?= "{$base}html/{$getString}&page={$pageItem->id}" ?>" class="btn <?php echo $page === $pageItem->id ? "btn-primary" : "btn-outline-dark"; ?> btn-sm rounded-pill btn-wave px-4 py-0">
                    <?php echo $pageItem->title ?>
                  </a>
               <?php endforeach; ?>
            </div>
            <?php 
            $validPages= array(
               'checklist_status',
               'checklist_items',
               'checklist_item_categories'
            );

            if(in_array($page, $validPages)) {              
               $getString = str_replace("&page={$page}", "", $getString);
               include_once("html/includes/scripts/sales/proposal_checklist/{$page}.php");
               $getString.= "&page={$page}";
            } else {
               Alert::info("Invalid page", true, array('fst-italic', 'text-center', 'font-18'));
            }?>
         </div>
         </div>
      </div>
      <?php
   } */?>
</div>
  
   <div class="card card-body col-md-12 my-4 shadow-lg">
      <div class="card   alert  alert-dismissible fade show border-0" role="alert">
         <div class="row">
            <div class="d-flex align-items-center justify-content-between mb-4 border-bottom">
               <h4 class="mb-0 t300 font-22">Proposal Checklists </h4>
               <div class=" border-end">							
                  <div class="font-22">
                     <!-- <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manageChecklistItemModal">
                        Add Checklist Item
                     </button> -->
                     <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manageChecklistModal">
                        Add Checklist 
                     </button>
                  </div>                  
               </div>
            </div>
         </div>
         <div class="card-body">
            <?php 
            $checKlists = Proposal::proposal_checklist(array('orgDataID'=>$proposalDetails->orgDataID, 'entityID'=>$proposalDetails->entityID, "proposalID"=>$proposalID), false, $DBConn);
            // var_dump($checKlists);
            if($checKlists){
               foreach ($checKlists as $key => $checklist) {
                  // //var_dump($checklist)?>
                  <div class="alert alert-primary alert-dismissible fade show custom-alert-icon shadow-sm" role="alert">
                     <div class="d-flex justify-content-between align-items-center">                        
                        <div class="d-flex align-items-center">
                           <div class="flex-shrink-0">
                              <i class="bi bi-check-circle-fill"></i>
                           </div>
                           <div class="flex-grow-1 ms-3">
                              <h5 class="alert-heading mb-0 t400 font-18"><?php echo $checklist->proposalChecklistName; ?></h5>
                              <p class="mb-0 fst-italic text-muted font-14"><?php echo $checklist->proposalChecklistDescription; ?></p>
                           </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                           <div class="d-flex align-items-center">
                              <span class="badge rounded-pill bg-primary-transparent me-2" > 
                                 <?= $checklist->proposalChecklistStatusName ?> 
                              </span> 
                              <span class="badge rounded-pill bg-primary-transparent me-2" > 
                                 <?= $checklist->AssignedEmployeeName ?> 
                              </span> 
                           </div>
                           <button 
                              type="button" 
                              class="btn btn-primary btn-sm editChecklistItemCategory" 
                              data-bs-toggle="modal" 
                              data-bs-target="#manageChecklistModal" 
                              data-proposal-checklist-id="<?php echo $checklist->proposalChecklistID; ?>" 
                              data-proposal-checklist-name="<?php echo $checklist->proposalChecklistName; ?>" 
                              data-proposal-checklist-description="<?php echo $checklist->proposalChecklistDescription; ?>" 
                              data-proposal-checklist-status-id="<?php echo $checklist->proposalChecklistStatusID; ?>" 
                              data-assigned-employee-id="<?php echo $checklist->assignedEmployeeID; ?>" 
                              data-assignee-id="<?php echo $checklist->assigneeID; ?>"
                              data-proposal-id="<?php echo $checklist->proposalID; ?>"
                              data-org-data-id= "<?php echo $checklist->orgDataID; ?>" 
                              data-entity-id= "<?php echo $checklist->entityID; ?>"
                           >
                              Edit
                           </button>
                           <button 
                              type="button" 
                              class="btn btn-primary btn-sm mx-2 addChecklistItemAssignmentBtn " 
                              data-bs-toggle="modal" 
                              data-bs-target="#addChecklistItemAssignment" 
                              data-proposal-checklist-id="<?php echo $checklist->proposalChecklistID; ?>" 
                              data-proposal-checklist-name="<?php echo $checklist->proposalChecklistName; ?>" 
                              data-proposal-id="<?php echo $checklist->proposalID; ?>"
                              data-org-data-id= "<?php echo $checklist->orgDataID; ?>" 
                              data-entity-id= "<?php echo $checklist->entityID; ?>"
                              >
                              Add Checklist Item
                           </button>

                        </div>
                     </div> 
                     <div class="row ps-4 mt-3">
                        <h4 class="mb-0 t500 font-16 "><?php echo $checklist->proposalChecklistName; ?> Checklist Items</h4>
                        <?php 
                        $checklistItemsAssignment = Proposal::proposal_checklist_item_assignment(array('proposalChecklistID'=>$checklist->proposalChecklistID), false, $DBConn);
                        if($checklistItemsAssignment){?>
                           <div class="list-group list-group-flush">
                              <?php
                              foreach ($checklistItemsAssignment as $key => $checklistItem) {

                                 // //var_dump($checklistItem);
                                 ?>
                                 <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center py-0">
                                       <div class="d-flex align-items-center">
                                          <div class="flex-shrink-0">
                                             <i class="bi bi-check-circle-fill"></i>
                                          </div>
                                          <div class="flex-grow-1 ms-3">
                                          
                                             <p class="mb-0 fst-italic text-muted font-14"><?php echo $checklistItem->proposalChecklistItemAssignmentDescription; ?></p>
                                             <span class="fst-italic text-primary font-14 mt-2 fw-bold">
                                                <?= $checklistItem->AssignedEmployeeName ?>
                                             </span>
                                          </div>
                                       </div>
                                       <button 
                                          type="button" 
                                          class="btn btn-primary btn-sm editChecklistItem" 
                                          data-bs-toggle="modal" 
                                          data-bs-target="#addChecklistItemAssignment" 
                                          data-proposal-checklist-item-id="<?php echo $checklistItem->proposalChecklistItemID; ?>"                                          
                                          data-proposal-checklist-item-description="<?php echo $checklistItem->proposalChecklistItemAssignmentDescription; ?>" 
                                       >
                                          Edit
                                       </button>
                                    </div>
                                 </div>
                                 <?php
                              }?>
                           </div>
                           <?php                           
                           // //var_dump($checklistItems);
                        }
                        ?>
                     </div>                   
                  </div>
                  <?php
               }
            } else {
               Alert::error("No proposal checklist found", true, array('fst-italic', 'text-center', 'font-18'));
            }?>
         </div>
      </div>      
   </div>
</div>
<?php 
// //var_dump($proposalDetails);
// manage checklist item modal
 echo Utility::form_modal_header("addChecklistItemAssignment", "sales/proposal_checklist/manage_proposal_checklist_item_assignment.php", "Proposal Checklist Item", array('modal-lg', 'modal-dialog-centered'), $base); 
 include_once("html/includes/scripts/sales/proposal_checklist/modals/manage_proposal_checklist_item_assignment.php");
 echo Utility::form_modal_footer('Save Proposal Checklist', 'saveProposalChecklistAssignment',  ' btn btn-success btn-sm', true);

//  manage checklist modal
echo Utility::form_modal_header("manageChecklistModal", "sales/proposal_checklist/manage_proposal_checklist.php", "Proposal Checklist", array('modal-lg', 'modal-dialog-centered'), $base);
include_once("html/includes/scripts/sales/proposal_checklist/modals/manage_proposal_checklist_modal.php");
echo Utility::form_modal_footer('Save Proposal Checklist', 'saveProposalChecklist',  ' btn btn-success btn-sm', true);
?>

<script>
document.addEventListener("DOMContentLoaded", function(event) {
   document.querySelectorAll('.editChecklistItemCategory').forEach(button => {
      button.addEventListener('click', function() {
         const form = document.getElementById('proposalChecklistModalForm');
         if (!form) return;

         // Get all data attributes from the button
         const data = this.dataset;
         console.log(data);

           // Map form fields to their corresponding data attributes
         const fieldMappings = {
            'proposalChecklistID': 'proposalChecklistId',
            'proposalChecklistName': 'proposalChecklistName',
            'proposalChecklistDescription': 'proposalChecklistDescription',
            'assignedEmployeeID': 'assignedEmployeeId',
            'orgDataID': 'orgDataId',
            'entityID': 'entityId',
            'proposalChecklistStatusID': 'proposalChecklistStatusId'
         };
         
         // fill regular form inputs
         for (const [field, dataAttr] of Object.entries(fieldMappings)) {
            const input = form.querySelector(`input[name="${field}"]`);
            if (input) {
               input.value = data[dataAttr] || '';
            }
         }
         // Fill the textarea with tinyMCE
         tinymce.init({
            selector: '#proposalChecklistDescription'
         });
         // Handle tinyMCE editor
         const editor = tinymce.get('proposalChecklistDescription'); // Make sure 'entityDescription' matches your textarea's ID
         if (editor) {
            // Wait for a brief moment to ensure tinyMCE is fully initialized
            setTimeout(() => {
               editor.setContent(data.proposalChecklistDescription || '');
            }, 100);
         }
         // If you have select elements that need special handling
         // (like setting selected options), handle them here
         const selects = ['proposalChecklistStatusID', 'assignedEmployeeID'];
         selects.forEach(selectName => {
            const select = form.querySelector(`[name="${selectName}"]`);
            if (select && data[fieldMappings[selectName]]) {
               select.value = data[fieldMappings[selectName]];
            }
         });


      });
   });
   document.querySelectorAll('.addChecklistItemAssignmentBtn').forEach(button => {
      button.addEventListener('click', function() {
         clickedButton = this;
         console.log(clickedButton);
         // stop propergation
         const form = document.getElementById('proposalChecklistItemAssignmentForm');
         if (!form) return;
         console.log(form);
         

         // Get all data attributes from the button
         const data = this.dataset;
         console.log(data);

           // Map form fields to their corresponding data attributes
         const fieldMappings = {
            'proposalChecklistID': 'proposalChecklistId',           
            'orgDataID': 'orgDataId',
            'entityID': 'entityId',
            'proposalID': 'proposalId'
         };
         
         // fill regular form inputs
         for (const [field, dataAttr] of Object.entries(fieldMappings)) {
            const input = form.querySelector(`input[name="${field}"]`);
           
            input.addEventListener('click', function(event) {
                  event.stopPropagation();
                  event.preventDefault();
                  console.log(` input ${field} clicked`);

               });
   
            console.log(input);
            if (input) { 
               input.value = data[dataAttr] || '';
               input.readOnly = true;
            }
         }
      });
   });
});
</script>