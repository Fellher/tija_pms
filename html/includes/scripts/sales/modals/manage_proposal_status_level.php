<div id="proposalStatuslevelForm">
   <div class="form-group">
      <label for="proposalStatusName"> Proposal status Name </label>
      <input type="text" name="proposalStatusName" id="proposalStatusName" class="form-control form-control-sm" >
   </div>
   <div class="form-group">
      <label for="proposalStatusDescription"> Proposal status Description </label>
      <textarea name="proposalStatusDescription" id="proposalStatusDescription" class="form-control form-control-sm" rows="3"></textarea>
   </div>
   <div class="form-group">
      <input type="text" class="form-control" name="proposalStatusID" id="proposalStatusID" hidden>
      <input type="text" class="form-control" name="orgDataID" id="orgDataID" value="<?= $orgDataID ?>" hidden>
      <input type="text" class="form-control" name="entityID" id="entityID" value="<?= $entityID ?>" hidden>
      
   </div>
   <?php 
   $proposalStatusCategories= Sales::proposal_status_categories(['orgDataID'=>$orgDataID, 'entityID'=>$entityID], false, $DBConn);
   // var_dump($proposalStatusCategories);
   // array(
   //    '1' => 'Pending',
   //    '2' => 'Approved',
   //    '3' => 'Rejected',
   //    '4' => 'In Progress',
   //    '5' => 'Completed'
   // );?>
   <div class="form-group">
      <label for="proposalStatusCategoryID"> Proposal status Category </label>
      <select name="proposalStatusCategoryID" id="proposalStatusCategoryID" class="form-control form-control-sm">
        
         <?= Form::populate_select_element_from_object($proposalStatusCategories, 'proposalStatusCategoryID', 'proposalStatusCategoryName','', '', "Select Category"); ?>
            

      </select>
   </div>
</div>