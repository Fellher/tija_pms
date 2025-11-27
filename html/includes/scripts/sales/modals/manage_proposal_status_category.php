<div class="proposalStatusCategoryForm">
   <div class="form-group my-2">
      <label for="proposalStatusCategoryName">Proposal Status Category Name</label>
      <input type="text" class="form-control" id="proposalStatusCategoryName" name="proposalStatusCategoryName" placeholder="Enter Proposal Status Category Name" required>
      <div class="invalid-feedback">
         Please enter a valid Proposal Status Category Name.
      </div>
   </div>
   <div class="form-group my-2">
      <label for="proposalStatusCategoryDescription">Proposal Status Category Description</label>
      <textarea class="form-control" id="proposalStatusCategoryDescription" name="proposalStatusCategoryDescription" rows="3" placeholder="Enter Proposal Status Category Description"></textarea>
      <div class="invalid-feedback">
         Please enter a valid Proposal Status Category Description.
      </div>
   </div>
   <div class="form-group">
      <input type="text" name="proposalStatusCategoryID" id="proposalStatusCategoryID" class="form-control"  value="" hidden>
      <input type="text" name="orgDataID" id="orgDataID" value="<?php echo $_SESSION['orgDataID']; ?>" class="form-control" hidden> 
      <input type="text" name="entityID" id="entityID" value="<?php echo $_SESSION['entityID']; ?>" class="form-control" hidden>
      <input type="text" name="userID" id="userID" value="<?php echo $userDetails->ID; ?>" class="form-control" hidden>
      <input type="text" name="action" id="action" value="" hidden>
     
   </div>

</div>