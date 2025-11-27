<div class="row">
    <div class="form-group">
        <label for="permissionType" class="form-label mb-0 ">Permission Profile Name</label>
        <input type="text" class="form-control-xs form-control-plaintext border-bottom bg-light" id="permissionProfileTitle" name="permissionProfileTitle" placeholder="Enter Permission Profile Name">
    </div>

    <div class="form-group">
        <?php $permissionScope = Admin::permission_scope(array(), false, $DBConn);
        // var_dump($permissionScope); ?>
        <label for="permissionProfileScope" class="form-label">Permission profile Scope</label>
       <select class="form-control form-control-sm form-control-plaintext border-bottom" id="permissionProfileScopeID" name="permissionProfileScopeID">
          <?php  echo Form::populate_select_element_from_object($permissionScope, 'permissionScopeID', 'permissionScopeTitle', '', '', 'Select Permission Profile Scope'); ?>
        </select>
    </div>

    <div class="form-group">
        <label for="permissionProfileDescription" class="form-label mb-0">Permission Profile Description</label>
        <textarea class="form-control-xs form-control-plaintext border-bottom bg-light" id="permissionProfileDescription" name="permissionProfileDescription" placeholder="Enter Permission Profile Description"></textarea>
    </div>
  
</div>  GHT