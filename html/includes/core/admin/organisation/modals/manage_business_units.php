<div id="business_unit_form" class="managebusinessUnits">
      <div class="row">
         <input type="hidden" id="businessUnitID" name="businessUnitID" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Business Unit ID" >
         <input type="hidden" id="orgDataID" name="orgDataID" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Organisation ID" value="<?php echo $orgDataID; ?>" >
         <input type="hidden" id="entityID" name="entityID" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Entity ID" value="<?php echo $entityID; ?>" >
         <div class="form-group">
            <label for="businessUnitName" class="form-label mb-0">Business Unit Name</label>
            <input type="text" id="businessUnitName" name="businessUnitName" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Business Unit Name" >

         </div>
         <div class="form-group">
            <label for="businessUnitDescription" class="form-label mb-0">Business Unit Description</label>
           <textarea id="businessUnitDescription" name="businessUnitDescription" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2 borderless-md" placeholder="Business Unit Description" ></textarea>
         </div>
         <div class="form-group">
            <label for="businessUnitType" class="form-label mb-0">Business Unit Type</label>
            <select id="unitTypeID" name="unitTypeID" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2">
               <?php echo Form::populate_select_element_from_object($unitType, 'unitTypeID', 'unitTypeName', '', '', 'Select Business Unit Type') ?>
            </select>
      </div>
</div>