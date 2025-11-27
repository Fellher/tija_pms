<div id="manage_proposal_modal_form">

   <div class="form-group d-none">
      <label for="employeID"> EmployeeID</label>
      <input type="text" class="form-control" id="employeID" name="employeID" placeholder="Enter Employee ID" value="<?php echo $employeeID; ?>" readonly>
      <label for="EntityID"> EntityID</label>
      <input type="text" class="form-control" id="entityID" name="entityID" placeholder="Enter Entity ID" value="<?php echo $entityID; ?>" readonly>
      <label for="orgDataID"> OrgDataID</label>
      <input type="text" class="form-control" id="orgDataID" name="orgDataID" placeholder="Enter Org ID" value="<?php echo $orgDataID; ?>" readonly>
      <label for="proposalID"> ProposalID</label>
      <input type="text" class="form-control" id="proposalID" name="proposalID" placeholder="Enter Proposal ID" value="" readonly>
   </div>
   <legend class="fs-18 t400 ">Basic Information</legend>
   <div class="row">
      <div class="col-md-12">
         <label for="proposalTitle" class="fs-14 text-primary">Proposal Title</label>
         <input type="text" class="form-control form-control-sm" id="proposalTitle" name="proposalTitle" placeholder="Enter Proposal Title">
      </div>

      <div class="col-md-6 form-group">
         <label for="clientID" class="fs-14 text-primary">Client Name</label>
         <select class="form-control form-control-sm" id="clientID" name="clientID">
            <option value="">Select Client</option>
            <?php
            if($clients) {
               foreach ($clients as $client) {?>
                  <option value="<?php echo $client->clientID; ?>"><?php echo $client->clientName; ?></option>
               <?php
               }
            }?>
         </select>
      </div>

      <div class="from-group col-md-6 mb-2">
         <label for="salesCaseID" class="fs-14 text-primary">Sales Case Name</label>
         <select class="form-control form-control-sm" id="salesCaseID" name="salesCaseID">
           <?= Form::populate_select_element_from_object($salesCases, 'salesCaseID', 'salesCaseName', '','','Select Sales Case') ?>
         </select>
      </div>
      <script>
         document.addEventListener('DOMContentLoaded', function() {
            let salesCases = <?= json_encode($salesCases) ?>;
            let clientID = document.getElementById('clientID');
            clientID.addEventListener('change', function() {
               let selectedClientID = clientID.value;
               let salesCaseSelect = document.getElementById('salesCaseID');
               salesCaseSelect.innerHTML = '<option value="">Select Sales Case</option>'; // Reset options
               salesCases.forEach(function(salesCase) {
                  if (salesCase.clientID == selectedClientID) {
                     let option = document.createElement('option');
                     option.value = salesCase.salesCaseID;
                     option.textContent = salesCase.salesCaseName;
                     salesCaseSelect.appendChild(option);
                  }
               });
            });
         });
      </script>
      <div class="form-group col-md-4">
         <label for="proposalDeadline" class="fs-14 text-primary">Proposal Deadline</label>
         <input type="date"
                class="form-control form-control-sm date"
                id="proposalDeadline"
                name="proposalDeadline"
                placeholder="Enter Proposal Deadline"
                data-expected-close-date="">
      </div>
      <script>
         // Set expected close date for validation if available
         document.addEventListener('DOMContentLoaded', function() {
            const proposalDeadlineInput = document.getElementById('proposalDeadline');
            if (proposalDeadlineInput) {
               // Try to get expected close date from sales case or form context
               const expectedCloseDate = proposalDeadlineInput.closest('form')?.querySelector('[name="expectedCloseDate"]')?.value ||
                                        document.querySelector('[data-expected-close-date]')?.dataset.expectedCloseDate ||
                                        null;
               if (expectedCloseDate) {
                  proposalDeadlineInput.setAttribute('data-expected-close-date', expectedCloseDate);
               }
            }
         });
      </script>
      <div class="form-group col-md-4">
         <label for="proposalValue" class="fs-14 text-primary">Proposal Value</label>
         <input type="text" class="form-control form-control-sm" id="proposalValue" name="proposalValue" placeholder="Enter Proposal Value">
      </div>
      <div class="form-group col-md-4">
         <label for="proposalStatusID" class="fs-14 text-primary">Proposal Status</label>
         <select class="form-control form-control-sm" id="proposalStatusID" name="proposalStatusID">
            <option value="">Select Proposal Status</option>
            <?php
            $proposalStatuses = Sales::proposal_statuses(['orgDataID'=> $orgDataID, 'entityID'=> $entityID], false, $DBConn);
            if($proposalStatuses) {
               foreach ($proposalStatuses as $proposalStatus) {?>
                  <option value="<?php echo $proposalStatus->proposalStatusID; ?>"><?php echo $proposalStatus->proposalStatusName; ?></option>
               <?php
               }
            }?>
            <!-- <option value="AddNew">Add Status</option>
            <option value="1">Pending</option>
            <option value="2">Approved</option>
            <option value="3">Rejected</option>
            <option value="4">In Progress</option>
            <option value="5">Completed</option>
            <option value="6">On Hold</option>
            <option value="7">Cancelled</option>
            <option value="8">Draft</option>
            <option value="9">Archived</option>
            <option value="10">Deleted</option>
            <option value="11">Finalized</option> -->
         </select>
      </div>
      <!-- <div class="form-group col-md-12 my-2">
         <label for="proposalFile" class="fs-14 text-primary">Upload Proposal Document</label>
         <input type="file" class="form-control form-control-sm" id="proposalFile" name="proposalFile" accept=".pdf, .doc, .docx">
      </div> -->
      <div class="form-group col-md-12 my-2">
         <label for="ProposalDescription" class="fs-14 text-primary">Proposal Description</label>
         <textarea class="form-control form-control-sm borderless-mini" id="proposalDescription" name="proposalDescription" rows="3" placeholder="Enter Proposal Description"></textarea>
      </div>
      <div class="form-group col-md-12 my-2">
         <label for="ProposalDescription" class="fs-14 text-primary">Proposal Comments</label>
         <textarea class="form-control form-control-sm borderless-mini" id="proposalComments" name="proposalComments" rows="3" placeholder="Enter Proposal Comments"></textarea>
      </div>
   </div>
</div>

<script>
   document.addEventListener("DOMContentLoaded", function(event) {
      console.log("Manage Proposal Modal Form Loaded");
      // Initialize any necessary JavaScript functionality here
      // For example, you can set up event listeners or form validation
      let form = document.getElementById('manage_proposal_modal_form');
      let clientID = document.querySelector('#clientID');
      let salesCaseID = form.querySelector('#salesCaseID');
      let proposalStatusID = form.querySelector('#proposalStatusID');

      // Example: Add an event listener to the clientID select element
      clientID.addEventListener('change', function() {
         console.log('Client ID changed:', clientID.value);
         // You can add logic here to update other fields based on the selected client
         let FilteredSalesCases = salesCases.filter(salesCase => salesCase.clientID === clientID.value);
         salesCaseID.innerHTML = '';
         salesCases.forEach(salesCase => {
            let option = document.createElement('option');
            option.value = salesCase.salesCaseID;
            option.text = salesCase.salesCaseName;
            salesCaseID.appendChild(option);
         });

         // output error if no sales cases found
         if (FilteredSalesCases.length === 0) {
            salesCaseID.innerHTML = '<option value="" class="bg-danger">No Sales Cases Found for this client</option>';
         }
      });



   });
</script>
