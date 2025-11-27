<div class="proposalChecklistItemAssignmentForm" id="proposalChecklistItemAssignmentForm">
   <div class="from-group">
      <div class="row">
         <div class="col-12 form-group my-2 d-none " >

            <label for="proposalID"> Proposal ID</label>
            <input type="text" class="form-control-sm form-control-plaintext border-bottom" id="proposalID" name="proposalID" value="" placeholder="Proposal ID" required  >
            <label for="orgDataID"> Organisation ID</label>
            <input type="text" class="form-control-sm form-control-plaintext border-bottom" id="orgDataID" name="orgDataID" value="<?= $orgDataID ?>" placeholder="Organisation ID" required >
            <label for="entityID"> Entity ID</label>
            <input type="text" class="form-control-sm form-control-plaintext border-bottom" id="entityID" name="entityID" value="<?= $entityID ?>" placeholder="Entity ID" required >
            <label for="proposalChecklistID"> Proposal Checklist ID ID</label>
            <input type="text" class="form-control-sm form-control-plaintext border-bottom" id="proposalChecklistID" name="proposalChecklistID" value="" placeholder="Checklist ID" required >



         </div>
         <div class="col-12 form-group my-2">
            <label for="checklistItemCategory" class="form-label text-primary">Checklist Item Category</label>
            <select class="form-control-sm form-control-plaintext border-bottom" id="proposalChecklistItemCategoryID" name="proposalChecklistItemCategoryID" aria-label="Default select example">
               <?= Form::populate_select_element_from_object($checklistItemCategories, 'proposalChecklistItemCategoryID',  'proposalChecklistItemCategoryName', '', '', 'Select Checklist Item Category'); ?>
            </select>
         </div>
         <?php
         // var_dump($checklistItems);?>
         <div class="col-12 form-group my-2">
            <label for="proposalCheckListItemID" class="form-label text-primary">Checklist Item</label>
            <select class="form-control-sm form-control-plaintext border-bottom" id="proposalChecklistItemID" name="proposalChecklistItemID" aria-label="Default select example">
               <?= Form::populate_select_element_from_object($checklistItems, 'proposalChecklistItemID',  'proposalChecklistItemName', '', '', 'Select Checklist Item'); ?>
            </select>
         </div>

         <script>
            document.addEventListener('DOMContentLoaded', function() {
               let form= document.querySelector('#proposalChecklistItemAssignmentForm');
               let checkListItems = <?= json_encode($checklistItems); ?>;
               let checklistItemCategorySelect = form.querySelector('#proposalChecklistItemCategoryID');
               // Initialize the select2 plugin for the checklist item dropdown
               checklistItemCategorySelect.addEventListener("change", function() {
                  let selectedCategoryID = this.value;
                  let checklistItemSelect = form.querySelector('#proposalChecklistItemID');
                  checklistItemSelect.innerHTML = ''; // Clear existing options

                  // Filter checklist items based on the selected category
                  let filteredItems = checkListItems.filter(item => item.proposalChecklistItemCategoryID == selectedCategoryID);

                  // Populate the checklist item dropdown with filtered items
                  filteredItems.forEach(item => {
                     let option = document.createElement('option');
                     option.value = item.proposalChecklistItemID;
                     option.textContent = item.proposalChecklistItemName;
                     checklistItemSelect.appendChild(option);
                  });

               });
            });
         </script>
         <div class="col-12 form-group my-2">
            <label for="proposalChecklistItemAssignmentDescription" class="form-label text-primary">Checklist Item Assignment Description</label>
            <textarea class="form-control borderless-mini" id="proposalChecklistItemAssignmentDescription" name="proposalChecklistItemAssignmentDescription" rows="3"></textarea>
         </div>
         <div class="col-12 form-group my-2">
            <label for="proposalChecklistItemAssignmentStatusID">Checklist Item Assignment Status</label>
            <select class="form-control-sm form-control-plaintext border-bottom" id="proposalChecklistItemAssignmentStatusID" name="proposalChecklistItemAssignmentStatusID" aria-label="Default select example">
               <?= Form::populate_select_element_from_object($checklistStatuses, 'proposalChecklistStatusID',  'proposalChecklistStatusName', '', '', 'Select Checklist Item Assignment Status'); ?>
            </select>
         </div>
         <div class="col-12 form-group my-2">
            <label for="assignedEmployeeID" class="form-label text-primary">Assignee Employee</label>
            <select class="form-control-sm form-control-plaintext border-bottom" id="checklistItemAssignedEmployeeID" name="checklistItemAssignedEmployeeID" aria-label="Default select example">
               <?= Form::populate_select_element_from_grouped_object($employeesCategorised, 'ID',  'employeeName', '', '', 'Select Assigned Employee');  ?>
            </select>
         </div>
         <div class="col-12 form-group my-2">
            <label for="proposalChecklistItemAssignmentDueDate" class="form-label text-primary d-block">Checklist Item Assignment Due Date <span class="float-end" >  </span> </label>
            <input type="date"
                   class="form-control-sm form-control-plaintext border-bottom date"
                   id="proposalChecklistItemAssignmentDueDate"
                   name="proposalChecklistItemAssignmentDueDate"
                   placeholder="Checklist Item Assignment Due Date"
                   data-checklist-deadline=""
                   required>
         </div>
         <script>
            // Set checklist deadline for validation when modal is shown
            document.addEventListener('DOMContentLoaded', function() {
               const itemDueDateInput = document.getElementById('proposalChecklistItemAssignmentDueDate');
               if (itemDueDateInput) {
                  // Get checklist deadline from the form or parent context
                  const checklistDeadline = itemDueDateInput.closest('form')?.querySelector('[name="proposalChecklistDeadlineDate"]')?.value ||
                                          itemDueDateInput.closest('.modal')?.querySelector('#proposalChecklistDeadlineDate')?.value ||
                                          null;
                  if (checklistDeadline) {
                     itemDueDateInput.setAttribute('data-checklist-deadline', checklistDeadline);
                  }
               }
            });
         </script>
         <div class="col-12 form-group my-2">
            <label for="proposalChecklistTemplate" class="form-label text-primary">Checklist Item Template</label>
            <input type="file" class="form-control-sm form-control border-bottom" id="proposalChecklistTemplate" name="proposalChecklistTemplate" placeholder="Checklist Item Template" required>
         </div>
         <div class="col-12 form-group my-2">
            <label for="checklistAssignmentDocument" class="form-label text-primary">Checklist Item Assignment Document</label>
            <input type="file" class="form-control-sm form-control border-bottom" id="proposalChecklistAssignmentDocument" name="proposalChecklistAssignmentDocument" placeholder="Checklist Item Assignment Document" required>
         </div>

         <input type="hidden" id="proposalChecklistItemAssignmentID" name="proposalChecklistItemAssignmentID" value="">
      </div>
   </div>
</div>