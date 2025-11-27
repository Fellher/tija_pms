<div id="statusLevelForm" class="needs-validation" novalidate>
   <input type="hidden" name="entityID" value="<?php echo $entityID; ?>">
   <input type="hidden" name="orgDataID" value="<?php echo $orgDataID; ?>">
   <input type="hidden" name="userID" value="<?php echo $userID; ?>">
   <input type="hidden" id="saleStatusLevelID" name="saleStatusLevelID" value="">

   <div class="form-group">
      <label for="statusLevel"> Status Level</label>
      <input type="text" id="statusLevel" name="statusLevel" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Status Level" >
   </div>
   <div class="form-group">
      <label for="StatusLevelDescription"> Status Level Description</label>
      <textarea id="StatusLevelDescription" name="StatusLevelDescription" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Status Level Description" ></textarea>
   </div>
   <div class="form-group">
      <label for="levelPercentage"> Level Percentage</label>
      <input type="number" id="levelPercentage" min="0.1" max='100' name="levelPercentage" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Level Percentage" >
   </div>
   <div class="form-group">
      <label for="previousLevel">previous Level</label>
      <select id="previousLevel" name="previousLevel" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Previous Level" >
   
         <?php echo  Form::populate_select_element_from_object($statusLevels, 'saleStatusLevelID', 'statusLevel', '', '', 'Select Previous Level'); ?>
      </select>
   </div>
</div>
