<div id="proposalChecklistModalForm" class="proposalCheckupModal">
   <div class="row">
      <div class="form-control my-2">
         <div class="row">
            <div class="col-12 form-group d-none">
               <label for="proposalID" >proposalID</label>
               <input type="text" class="form-control-sm form-control-plaintext border-bottom" id="proposalID" name="proposalID" value="<?= $proposalID ?>" placeholder="Proposal ID" required hidden>
            </div>

            <div class="col-12 form-group my-2">
               <label for="proposalChecklistName" class="form-label text-primary">Checklist Name</label>
               <input type="text" class="form-control-sm form-control-plaintext border-bottom" id="proposalChecklistName" name="proposalChecklistName" value="<?= (isset($proposalDetails) && $proposalDetails->proposalTitle) ? "{$proposalDetails->proposalTitle} Checklist" : "" ?>" placeholder="Checklist Name" required>
            </div>
            <?php   $checklistStatuses = Proposal::proposal_checklist_status(['proposalChecklistStatusType'=>'checkList'], false, $DBConn);   ?>
            <div class="col-12 form-group">
               <label for="proposalChecklistStatusID">Checklist Status </label>
               <select class="form-control-sm form-control-plaintext border-bottom" id="proposalChecklistStatusID" name="proposalChecklistStatusID" aria-label="Default select example">
                  <?= Form::populate_select_element_from_object($checklistStatuses, 'proposalChecklistStatusID',  'proposalChecklistStatusName', '', '', 'Select Checklist Status'); ?>
               </select>
            </div>

            <div class="col-12 form-group my-2">
               <label for="assignedEmployeeID" class="form-label text-primary">Assigned Employee</label>
               <select class="form-control-sm form-control-plaintext border-bottom" id="assignedEmployeeID" name="assignedEmployeeID" aria-label="Default select example">
                  <?= Form::populate_select_element_from_grouped_object($employeesCategorised, 'ID',  'employeeName', '', '', 'Select Assigned Employee');  ?>
               </select>
            </div>
            <div class="col-12 form-group my-2">
               <label for="proposalChecklistDeadlineDate" class="form-label text-primary d-block">Checklist Deadline Date <span class="float-end text-danger fs-12 "> Proposal Deadline: <?= isset($proposalDetails) && $proposalDetails->proposalDeadline ? json_encode($proposalDetails->proposalDeadline) : '""' ?> </span></label>
               <input type="date" class="form-control-sm form-control-plaintext border-bottom date" id="proposalChecklistDeadlineDate" name="proposalChecklistDeadlineDate" placeholder="Checklist Deadline Date" required>
            </div>

            <div class="col-12 from-group my-2 d-none">
               <label for="orgDataID" class="form-label text-primary">Organisation </label>
               <input type="text" class="form-control-sm form-control-plaintext border-bottom" id="orgDataID" name="orgDataID" value="<?= $orgDataID ?>" placeholder="Organisation ID" required hidden>
               <label for="entityID" class="form-label text-primary">Entity</label>
               <input type="text" class="form-control-sm form-control-plaintext border-bottom" id="entityID" name="entityID" value="<?= $entityID ?>" placeholder="Entity ID" required hidden>
            </div>

            <div class="col-12 form-group my-2">
               <label for="proposalProposalChecklistDescription" class="form-label text-primary d-block">Checklist Description   </label>
               <textarea class="form-control borderless-mini" id="proposalChecklistDescription" name="proposalChecklistDescription" rows="3"></textarea>
            </div>

            <input type="hidden" id="proposalChecklistID" name="proposalChecklistID" value="">
         </div>
      </div>
   </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function(event) {
  // Set proposal deadline as data attribute for Flatpickr validation
  const proposalDeadlineDate = <?= isset($proposalDetails) && $proposalDetails->proposalDeadline ? json_encode($proposalDetails->proposalDeadline) : '""' ?>;
   const proposalChecklistDeadlineDate = document.getElementById('proposalChecklistDeadlineDate');

   if (proposalChecklistDeadlineDate && proposalDeadlineDate) {
      proposalChecklistDeadlineDate.setAttribute('data-proposal-deadline', proposalDeadlineDate);
   }

   // Fallback validation if Flatpickr not available
   if (proposalChecklistDeadlineDate && typeof flatpickr === 'undefined') {
      proposalChecklistDeadlineDate.addEventListener('change', function() {
         if (proposalDeadlineDate && new Date(this.value) > new Date(proposalDeadlineDate)) {
            alert('Checklist Deadline Date cannot be after Proposal Deadline Date');
            const errorDiv = document.createElement('div');
            errorDiv.textContent = 'Error: Checklist Deadline Date cannot be after Proposal Deadline Date';
            errorDiv.style.color = 'red';
            proposalChecklistDeadlineDate.parentElement.insertBefore(errorDiv, proposalChecklistDeadlineDate.nextSibling);
            this.value = '';
         } else {
            const errorDiv = proposalChecklistDeadlineDate.parentElement.querySelector('div');
            if (errorDiv) {
               errorDiv.remove();
            }
         }
      });
   }
});

</script>