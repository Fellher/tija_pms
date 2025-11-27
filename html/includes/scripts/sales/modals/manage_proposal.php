<div id="manageProposalForm" class="manageProposalForm row">
   <input    type="hidden" name="proposalID" value="">
   <input    type="hidden" name="orgDataID" value="<?= $salesCaseDetails->orgDataID ?>">
   <input    type="hidden" name="entityID" value="<?= $salesCaseDetails->entityID ?>">
   <input    type="hidden" name="employeeID" value="<?= $salesCaseDetails->salesPersonID ? $salesCaseDetails->salesPersonID : $userDetails->ID; ?>">
   <input    type="hidden" name="salesCaseID" value="<?= $salesCaseDetails->salesCaseID ?>">
   <input    type="hidden" name="clientID" value="<?= $salesCaseDetails->clientID ?>">
   <div class="form-group col-md-12">
      <label for="proposalTitle" class="text-primary"> Proposal title</label>
      <input type="text" class=" form-control-sm form-control-plaintext border-bottom bg-light-blue" id="proposalTitle" name="proposalTitle" value="" >
   </div>

   <div class="form-group col-md-12 my-1">
      <label for="proposalDeadline" class="text-primary"> Proposal Deadline</label>
      <input type="date" class=" form-control-sm bg-light-blue form-control-plaintext border-bottom date" id="proposalDeadline" name="proposalDeadline" value="" >
   </div>

   <div class="form-group col-md-12 my-1">
      <label for="proposalValue" class="text-primary"> Proposal Value</label>
      <input type="text" class=" form-control-sm bg-light-blue form-control-plaintext border-bottom" id="proposalValue" name="proposalValue" value="" >
   </div>

   <div class="form-group col-md-12 my-1">
      <label for="proposalStatusID" class="text-primary"> Proposal Status</label>
      <select class="form-control-sm form-control-plaintext bg-light-blue border-bottom" id="proposalStatusID" name="proposalStatusID" >
         <option value="" selected disabled>Select Proposal Status</option>
      <?php
            if($proposalStatuses) {
               foreach ($proposalStatuses as $status) {?>
                  <option value="<?php echo $status->proposalStatusID; ?>"><?php echo $status->proposalStatusName; ?></option>
               <?php
               }
            }?>
      </select>
   </div>

   <div class="form-group col-md-12 my-1">
      <label for="proposalDescription" class="text-primary"> Proposal Description</label>
      <textarea class="form-control-sm form-control-plaintext bg-light-blue border-bottom" id="proposalDescription" name="proposalDescription" rows="3" ></textarea>
   </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
   // get the current global div
   const manageProposalForm = document.querySelector('#manageProposalForm');
   if (!manageProposalForm) {
      console.error('Manage Proposal Form not found.');
      return;
   }

   // Initialize Tom Select for the proposal status select element
   const proposalStatusSelect = manageProposalForm.querySelector('#proposalStatusID');

   // Set expected close date as data attribute for Flatpickr validation
   const proposalDeadlineInput = document.querySelector('#proposalDeadline');
   if (proposalDeadlineInput) {
      const expectedCloseDate = '<?= isset($salesCaseDetails->expectedCloseDate) ? $salesCaseDetails->expectedCloseDate : '' ?>';
      if (expectedCloseDate) {
         proposalDeadlineInput.setAttribute('data-expected-close-date', expectedCloseDate);
      }

      // Fallback validation if Flatpickr not available
      if (typeof flatpickr === 'undefined') {
         proposalDeadlineInput.addEventListener('change', function() {
            const proposalDeadline = new Date(this.value);
            const expectedCloseDate = new Date('<?= isset($salesCaseDetails->expectedCloseDate) ? $salesCaseDetails->expectedCloseDate : '' ?>');
            if (expectedCloseDate && proposalDeadline > expectedCloseDate) {
               this.classList.add('border-danger', 'is-invalid');
               const errorMessage = document.createElement('div');
               errorMessage.className = 'text-danger small';
               errorMessage.textContent = `Proposal deadline cannot be after the expected close date. Please select a valid date before ${expectedCloseDate.toLocaleDateString()} or adjust the expected close date.`;
               this.parentNode.appendChild(errorMessage);
               this.value = '';
            }
         });
      }
   }

});
</script>