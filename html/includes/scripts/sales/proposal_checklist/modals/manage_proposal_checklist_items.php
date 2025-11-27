<div id="manageProposalChecklistItemsForm">
   <div class="row">
      <div class="form-control my-2">
         <div class="row">
            <div class="col-12 form-group my-2">
               <label for="proposalChecklistItemName" class="form-label text-primary">Checklist Item Name</label>
               <input type="text" class="form-control-sm form-control-plaintext border-bottom" id="proposalChecklistItemName" name="proposalChecklistItemName" placeholder="Checklist Item Name" required>
            </div>
            <div class="col-12 form-group my-2">
               <label for="proposalChecklistItemDescription" class="form-label text-primary">Checklist Item Description</label>
               <textarea class="form-control borderless-mini" id="proposalChecklistItemDescription" name="proposalChecklistItemDescription" rows="3"></textarea>
            </div>
            <div class="col-12 form-group my-2">
               <label for="proposalChecklistItemCategoryID" class="form-label text-primary"> Checklist Item Category</label>
              
               <select class="form-select form-select-sm" id="proposalChecklistItemCategoryID" name="proposalChecklistItemCategoryID" aria-label="Default select example">
                 
                  <?= Form::populate_select_element_from_object($checklistItemCategories, 'proposalChecklistItemCategoryID',  'proposalChecklistItemCategoryName', '', '', 'Select Checklist Item Category');                   ?>
               </select>
            </div>
            <input type="hidden" id="proposalChecklistItemID" name="proposalChecklistItemID" value="">
         </div>
      </div>
   </div>
</div>