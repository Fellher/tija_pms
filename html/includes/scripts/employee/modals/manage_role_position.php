<div id="manageJobRleAssignment" class=" job_role_assignment_form">
   <div class="row">
      <div class="col-12">
        <input type="text" class="form-control-sm" name="orgRoleID" id="orgRoleID" value="" />
        <input type="text" class="form-control-sm" name="orgDataID" id="orgDataID" value="<?php echo $orgDataID  ?>" />
        <input type="text" class="form-control-sm" name="entityID" id="entityID" value="<?php echo $entityID ?>" />
      </div>
      <div class="col-12">
         <div class="mb-3">
            <label for="jobTitleID" class="form-label">Job Title</label>
            <select name="jobTitleID" id="jobTitleID" class="form-select form-select-sm" required>
               <option value="">Select Job Title</option>
               <?php foreach($jobTitles as $jobTitle): ?>
                  <option value="<?php echo $jobTitle->jobTitleID ?>"><?php echo $jobTitle->jobTitle ?></option>
               <?php endforeach; ?>
            </select>
         </div>
      </div>


   </div>

</div>