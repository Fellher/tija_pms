<?php 
if(!$isValidUser) {
  Alert::info("You need to be logged in as a valid user to access this page", true, array('fst-italic', 'text-center', 'font-18'));
  include "includes/core/log_in_script.php";
  return;
}

$employeeID=(isset($_GET['uid']) && !empty($_GET['uid'])) ? Utility::clean_string($_GET['uid']) : $userDetails->ID;
$employeeDetails = Data::users(array('ID'=>$employeeID), true, $DBConn);
$allEmployees = Employee::employees([], false, $DBConn);
$orgDataID= isset($_GET['orgDataID']) ? Utility::clean_string($_GET['orgDataID']) : $employeeDetails->orgDataID;
$entityID= isset($_GET['entityID']) ? Utility::clean_string($_GET['entityID']) : $employeeDetails->entityID;

$checklistStatuses = Proposal::proposal_checklist_status(['proposalChecklistStatusType'=>'checkListItem'], false, $DBConn);
$proposalChecklistItemAssignmentID = isset($_GET['pciad']) && !empty($_GET['pciad']) ? Utility::clean_string($_GET['pciad']) : "";

if($proposalChecklistItemAssignmentID) {
    $checklistItemAssignmentDetails = Proposal::proposal_checklist_item_assignment_full(array('proposalChecklistItemAssignmentID'=>$proposalChecklistItemAssignmentID), true, $DBConn);    
    if($checklistItemAssignmentDetails) {?>
        <div class="card card-body shadow-lg">
            <h4 class="border-bottom"  > <?= $checklistItemAssignmentDetails->proposalTitle ?> 
                <span class="float-end fs-16" > 
                    <span class="badge rounded-pill bg-primary-transparent mx-4">
                        <?= $checklistItemAssignmentDetails->proposalChecklistStatusItemName; ?>
                    </span>
                    <i class="ti ti-user-circle fs-20"></i> 
                    <?= $checklistItemAssignmentDetails->proposalChecklistAssignorName  ?>
                </span> 
            </h4>
            <div class="d-flex">
                <div class="flex-grow-1 me-3">     
                    <h5 class="fs-16"> <?= "{$checklistItemAssignmentDetails->proposalChecklistItemCategoryName} - {$checklistItemAssignmentDetails->proposalChecklistItemName} " ?> </h5>         
                    <p> <?= $checklistItemAssignmentDetails->proposalChecklistItemAssignmentDescription ?> </p>
                    <div class="row">
                        <div class="col-md-6">
                            <span class="d-block"><strong>Assigned Employee:</strong> <?= $checklistItemAssignmentDetails->checklistItemAssignedEmployeeName; ?></span>
                            <span class="d-block"><strong>Assigned By:</strong> <?= $checklistItemAssignmentDetails->proposalChecklistAssignorName; ?></span>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Due Date:</strong> <?= Utility::date_format($checklistItemAssignmentDetails->proposalChecklistItemAssignmentDueDate); ?></p>
                        </div>
                        <?php 
                        if($checklistItemAssignmentDetails->proposalChecklistTemplate): ?>
                            <div class="col-md-6"><strong>Template:</strong> 
                                <a href="<?= $checklistItemAssignmentDetails->proposalChecklistTemplate ?>" class="badge rounded-pill bg-primary-transparent mx-4">
                                    Download Template  <i class="bi bi-download mx-2"></i>
                                </a>
                            </div>
                            <?php 
                        endif;
                        if($checklistItemAssignmentDetails->proposalChecklistAssignmentDocument): ?>
                            <div class="col-md-6"><strong>Supporting Document Instruction:</strong> 
                                <a href="<?= $checklistItemAssignmentDetails->proposalChecklistAssignmentDocument ?>" class="badge rounded-pill bg-primary-transparent mx-4">
                                    Download Document  <i class="bi bi-download mx-2"></i>
                                </a>
                            </div>
                            <?php 
                        endif; ?>
                    </div>

                </div>
                <div class="flex-shrink-0">
                    <span class="d-block text-center badge rounded-pill bg-primary-transparent"> Deadline: <?= Utility::date_format($checklistItemAssignmentDetails->proposalChecklistItemAssignmentDueDate); ?></span>                    
                    <?php 
                    if($checklistItemAssignmentDetails->proposalChecklistTemplate): ?>
                        <div class="col-12">
                            <strong class="d-block">Template</strong> 
                            <a href="<?= $checklistItemAssignmentDetails->proposalChecklistTemplate ?>" class="badge rounded-pill bg-primary-transparent mx-auto d-block">
                                Download Template  <i class="bi bi-download mx-2"></i>
                            </a>
                        </div>
                        <?php 
                    endif;
                    if($checklistItemAssignmentDetails->proposalChecklistAssignmentDocument): ?>
                        <div class="col-12">
                            <strong class="d-block">Supporting Document Instruction</strong> 
                            <a href="<?= $checklistItemAssignmentDetails->proposalChecklistAssignmentDocument ?>" class="badge rounded-pill bg-primary-transparent mx-auto d-block">
                                Download Document  <i class="bi bi-download mx-2"></i>
                            </a>
                        </div>
                        <?php 
                    endif; ?>                   
                </div>
            </div>
        </div>

        <div class="card shadow-lg mt-3">
            <div class="card-body">
                <h5 class="card-title">Checklist Item Assignment Submission</h5>
                <form action="<?= "{$base}php/scripts/sales/proposal_checklist/manage_checklist_assignments_submission.php" ?>" id="checklistitemAssignment" method="POST" enctype="multipart/form-data">
                
                    <div class="row">
                        <div class="form-group">
                            <!-- This is where you can add the form fields for submitting the checklist item assignment -->
                            <input type="hidden" name="proposalChecklistItemAssignmentID" value="<?= $checklistItemAssignmentDetails->proposalChecklistItemAssignmentID; ?>">
                            <input type="hidden" name="proposalChecklistItemID" value="<?= $checklistItemAssignmentDetails->proposalChecklistItemID; ?>">
                            <input type="hidden" name="checklistItemAssignedEmployeeID" value="<?= $checklistItemAssignmentDetails->checklistItemAssignedEmployeeID; ?>">
                            <input type="hidden" name="proposalChecklistItemAssignmentStatusID" value="<?= $checklistItemAssignmentDetails->proposalChecklistItemAssignmentStatusID; ?>">
                        </div>
                        <div class="col-12 form-group my-2">
                            <label for="proposalChecklistItemUploadfile" class="form-label text-primary">Checklist Item Assignment Submission Documents</label>
                            <input type="file" class="form-control-sm form-control border-bottom " id="proposalChecklistItemUploadfile" name="proposalChecklistItemUploadfile[]"  multiple data-allow-reorder="true"  placeholder="Checklist Item Assignment Submission Document" required>
                        </div>
                        <div class="col-12 form-group my-2">
                            <label for="proposalChecklistItemAssignmentSubmissionDescription" class="form-label text-primary">Submission Description</label>
                            <textarea class="form-control borderless-mini" id="proposalChecklistItemAssignmentSubmissionDescription" name="proposalChecklistItemAssignmentSubmissionDescription" rows="3"></textarea>
                        </div>
                        <div class="col-12 form-group my-2">
                            <label for="proposalChecklistItemAssignmentSubmissionStatusID">Submission Status</label>
                            <select class="form-control-sm form-control-plaintext border-bottom" id="proposalChecklistItemAssignmentSubmissionStatusID" name="proposalChecklistItemAssignmentSubmissionStatusID" aria-label="Default select example">
                                <?= Form::populate_select_element_from_object($checklistStatuses, 'proposalChecklistStatusID',  'proposalChecklistStatusName', '', '', 'Select Submission Status'); ?>
                            </select>
                        </div>                        
                    </div>
                    <div class="col-12 text-right">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    } else {
        Alert::info("The checklist assignment does not exist. Please select a valid Checklist Item assignment ", true, array('fst-italic', 'text-center', 'font-18') );
    }
    // var_dump($checklistItemAssignmentDetails);

} else{
    $checklistItemsAssignment = Proposal::proposal_checklist_item_assignment(array('checklistItemAssignedEmployeeID'=> $employeeID), false, $DBConn);

    $checklistItemAssignmentFull = Proposal::proposal_checklist_item_assignment_full(array('checklistItemAssignedEmployeeID'=> $employeeID), false, $DBConn);
    // var_dump($checklistItemAssignmentFull);

    // var_dump($checklistItemsAssignment);?>
    <div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between ">
                        <h3> Proposal Requests</h3>                                                
                    </div>
                    <?php 
                    if($checklistItemAssignmentFull) {?>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <h4 class="text-center">Assigned Proposal Checklist Items</h4>
                            </div>
                            <?php
                            foreach ($checklistItemAssignmentFull as $assignment) {
                                // var_dump($assignment);
                                // $submited = Proposal::proposal_checklist_item_assignment_submissions(
                                //     ['proposalChecklistItemAssignmentID'=>$assignment->proposalChecklistItemAssignmentID
                                // ], true, $DBConn);
                                ?>
                                <div class="col-md-4 col-sm-6 col-12 mb-3">
                                    <div class="alert alert-info fst-italic text-left font-12 ">
                                        <div class="d-flex">                                        
                                            <div class="flex-grow-1 me-3">                                            
                                                <span class="d-block mb-1 border-bottom border-info">
                                                    Proposal Name: 
                                                    <strong class="text-primary"><?php echo $assignment->proposalTitle; ?></strong>
                                                </span>
            
                                                <span  class="fs-12 t500"><?php echo $assignment->proposalChecklistItemName; ?> </span>
                                                <span class="d-block" ><?php echo $assignment->proposalChecklistItemAssignmentDescription; ?></span>
                                        
                                                <span class="t600" > Assigned By: </span>
                                                <strong class="fst-italic text-primary"><?php echo $assignment->proposalChecklistAssignorName; ?></strong>
                                                <span class="mx-3" >|</span>
            
                                                <span class="t600" >Due Date: </span> <?php echo $assignment->proposalChecklistItemAssignmentDueDate; ?>
                                            </div>
                                            <div class="flex-shrink-0 d-flex align-items-center flex-column gap-2 ">
                                                <!-- <button type="button" class="btn  btn-icon rounded-pill btn-primary-light btn-sm">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                                <button type="button" class="btn  btn-icon rounded-pill btn-danger-light btn-sm">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                                <button type="button" class="btn  btn-icon rounded-pill btn-warning-light btn-sm">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button> -->
                                                <a href="<?= "{$base}html/{$getString}&pciad={$assignment->proposalChecklistItemAssignmentID}"  ?>" type="button" class="btn  btn-icon rounded-pill  btn-warning-light btn-sm">
                                                    <i class="bi bi-eye"></i>
                                                </a> 
                                                <!-- <button type="button" class="btn  btn-icon rounded-pill btn-info-light btn-sm">
                                                    <i class="bi bi-archive"></i>
                                                </button>
                                                -->
                                                <?php 
                                                if($assignment->proposalChecklistTemplate): ?>
                                                    <a href="<?php echo $assignment->proposalChecklistTemplate; ?>" class="btn btn-icon rounded-pill btn-primary-light btn-sm">
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                    <?php 
                                                endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php //var_dump($assignment); 
                            }?>
                        </div>

                        <?php
                    } else {
                        Alert::info("No proposal checklist items assigned to you.", true, array('fst-italic', 'text-center', 'font-18'));
                    } ?>

                

                </div>
            </div>
        </div>
    </div>
    </div>
<?php

}?>



