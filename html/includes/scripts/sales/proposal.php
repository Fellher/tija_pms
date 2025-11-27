<?php
// check if proposal exists if not create a new one
$proposals = Sales::proposal_full(['salesCaseID'=>$salesCaseID, 'entityID'=>$entityID], false, $DBConn);
$proposalStatuses = Sales::proposal_statuses(array('orgDataID'=>$salesCaseDetails->orgDataID, 'entityID'=>$salesCaseDetails->entityID), false, $DBConn);
$checklistStatuses = Proposal::proposal_checklist_status(['proposalChecklistStatusType'=>'checkListItem'], false, $DBConn);
$checklistItems = Proposal::proposal_checklist_items([], false, $DBConn);
$checklistItemCategories = Proposal::proposal_checklist_items_categories([], false, $DBConn);
$employeeList = Employee::employees(array('orgDataID'=>$orgDataID, 'entityID'=>$entityID), false, $DBConn);
$employeesCategorised = Employee::categorise_employee($employeeList, 'jobTitle');
$checkListStatus = Proposal::proposal_checklist_status([], false, $DBConn);
if($proposals){
    foreach($proposals as $key => $proposalDetails ){
        $proposalID  = $proposalDetails->proposalID;?>
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
                                <a href="<?= "{$base}html/?s={$s}&ss={$ss}&p=proposal_details&proposalID={$proposalID}" ?>" class="btn  btn-icon rounded-pill btn-secondary-light">
                                    <i class="ri-eye-line"></i>
                                </a>
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
                                }
                                 // proposal Attachments modal
                                    echo Utility::form_modal_header("manageProposalAttachmentModal", "sales/proposal_attachments/manage_proposal_attachment.php", "Proposal Attachment", array('modal-lg', 'modal-dialog-centered'), $base);
                                    include_once("html/includes/scripts/sales/proposal_attachments/manage_proposal_attachment.php");
                                    echo Utility::form_modal_footer('Save Proposal Attachment', 'saveProposalAttachment',  ' btn btn-success btn-sm', true);

                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="">
                <div class="card card-body col-md-12 my-4 border-0 shadow-lg">
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
                            // var_dump($proposalDetails);
                            // get proposal checklist
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
                                            <p class="mb-0 fst-italic text-muted font-14"><?php echo $checklist->proposalChecklistDescription; ?> </p>
                                            <p class="mb-0 fst-italic text-muted font-14">Deadline: <?php echo Utility::date_format($checklist->proposalChecklistDeadlineDate); ?> </p>
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
                                            data-proposal-checklist-deadline-date="<?php echo $checklist->proposalChecklistDeadlineDate; ?>"
                                            >
                                            Add Checklist Item
                                        </button>

                                        </div>
                                    </div>
                                    <div class="row ps-4 mt-3">
                                        <h4 class="mb-0 t500 font-16 "><?php echo $checklist->proposalChecklistName; ?> Checklist Items</h4>
                                        <?php

                                        // var_dump($checklist);
                                        // get checklist items assignment
                                        $checklistItemsAssignment = Proposal::proposal_checklist_item_assignment(array('proposalChecklistID'=>$checklist->proposalChecklistID), false, $DBConn);
                                        if($checklistItemsAssignment){?>
                                        <div class="list-group list-group-flush">
                                            <?php
                                            foreach ($checklistItemsAssignment as $key => $checklistItem) {
                                                // var_dump($checklistItem);
                                                ?>
                                                <div class="list-group-item border-bottom">
                                                    <div class="d-flex justify-content-between align-items-center py-0">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0">
                                                                <i class="bi bi-check-circle-fill"></i>
                                                            </div>
                                                            <div class="flex-grow-1 ms-3">

                                                                <p class="mb-0 fst-italic text-muted font-14"><?php echo $checklistItem->proposalChecklistItemAssignmentDescription; ?></p>
                                                                <span class="fst-italic text-primary font-14 mt-2 fw-bold d-block ">
                                                                    <?= $checklistItem->AssignedEmployeeName ?>

                                                                </span>
                                                            </div>
                                                        </div>
                                                        <span class="float-end">
                                                                        <span class="badge rounded-pill bg-secondary ms-2">
                                                                            <?php echo Utility::date_format($checklistItem->proposalChecklistItemAssignmentDueDate); ?>
                                                                        </span>
                                                                        <span class="badge rounded-pill bg-primary-transparent ms-2">
                                                                            <?php echo $checkListStatus[$checklistItem->proposalChecklistItemAssignmentStatusID]->proposalChecklistStatusName; ?>
                                                                        </span>
                                                                    </span>
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
        </div>
    <?php
    //    var_dump($proposalDetails);
    }

} else {
    Alert::info("No Proposals Set for the Sale" , true, array('fst-italic', 'text-center', 'font-18'));  ?>

<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manageProposalModal">
    Create Proposal
</button>


<?php

// var_dump($salesCaseDetails);
echo "<div class='alert alert-info'>No proposals found for this sales case. Please create a proposal to proceed.</div>";
    // echo "<button type='button' class='btn btn-primary' data-bs-toggle='modal' data-bs-target='#createProposalModal'>Create Proposal</button>";
    // echo "<script>document.querySelector('#createProposalModal').classList.add('show');</script>";
    echo Utility::form_modal_header("manageProposalModal", "sales/manage_proposal.php", "Manage Proposal", array('modal-md', 'modal-dialog-centered'), $base);
    include "includes/scripts/sales/modals/manage_proposal.php";
    echo Utility::form_modal_footer('Save Proposal', 'saveProposal',  ' btn btn-success btn-sm', true);
}



// //var_dump($proposalDetails);
// manage checklist item modal
 echo Utility::form_modal_header("addChecklistItemAssignment", "sales/proposal_checklist/manage_proposal_checklist_item_assignment.php", "Proposal Checklist Item", array('modal-lg', 'modal-dialog-centered'), $base);
 include_once("html/includes/scripts/sales/proposal_checklist/modals/manage_proposal_checklist_item_assignment.php");
 echo Utility::form_modal_footer('Save Proposal Checklist', 'saveProposalChecklistAssignment',  ' btn btn-success btn-sm', true);

//  manage checklist modal
echo Utility::form_modal_header("manageChecklistModal", "sales/proposal_checklist/manage_proposal_checklist.php", "Proposal Checklist", array('modal-lg', 'modal-dialog-centered'), $base);
include_once("html/includes/scripts/sales/proposal_checklist/modals/manage_proposal_checklist_modal.php");
echo Utility::form_modal_footer('Save Proposal Checklist', 'saveProposalChecklist',  ' btn btn-success btn-sm', true);

// Initialize all proposal date pickers with Flatpickr
include "includes/scripts/sales/proposal_date_pickers.php";

// //var_dump($proposalDetails);?>

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
        //  console.log(clickedButton);
         // stop propergation
         const form = document.getElementById('proposalChecklistItemAssignmentForm');
         if (!form) return;
        //  console.log(form);


         // Get all data attributes from the button
         const data = this.dataset;
         console.log(data);

           // Map form fields to their corresponding data attributes
         const fieldMappings = {
            'proposalChecklistID': 'proposalChecklistId',
            'orgDataID': 'orgDataId',
            'entityID': 'entityId',
            'proposalID': 'proposalId',
            'proposalChecklistDeadlineDate': 'proposalChecklistDeadlineDate',
         };

         const proposalChecklistItemAssignmentDueDate = form.querySelector('#proposalChecklistItemAssignmentDueDate');
         console.log(proposalChecklistItemAssignmentDueDate);
         if (proposalChecklistItemAssignmentDueDate) {
            let parentDiv = proposalChecklistItemAssignmentDueDate.parentElement;
            console.log(`parentDiv : ${parentDiv}`);
            const label = parentDiv.querySelector('label');
            label.classList.add('d-block', 'text-primary');
            const dueDateSpan = document.createElement('span');
            dueDateSpan.innerHTML = ` ChecklistDeadline:  ${data.proposalChecklistDeadlineDate} `;
            dueDateSpan.classList.add('float-end', 'text-danger', 'fs-12');
            console.log(`label is set as`);
            console.log(label);
            label.appendChild(dueDateSpan);
         }

         proposalChecklistItemAssignmentDueDate.addEventListener('change', function() {
            const dueDate = new Date(this.value);
            const checklistDeadlineDate = new Date(data.proposalChecklistDeadlineDate);
            if (dueDate > checklistDeadlineDate) {
            //    alert('Checklist Item Assignment Due Date cannot be after the Checklist Deadline Date');
               const errorDiv = document.createElement('div');
               errorDiv.textContent = 'Error: Checklist Item Assignment Due Date cannot be after the Checklist Deadline Date';
               errorDiv.style.color = 'red';
               proposalChecklistItemAssignmentDueDate.parentElement.insertBefore(errorDiv, proposalChecklistItemAssignmentDueDate.nextSibling);
               this.value = '';
            } else {
               const errorDiv = proposalChecklistItemAssignmentDueDate.parentElement.querySelector('div');
               if (errorDiv) {
                  errorDiv.remove();
               }
            }
         });

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
