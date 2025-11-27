<div class="salesCaseFormuPLOAD">	

	
   <?php 
   $orgs = Admin::organisation_data_mini([], false, $DBConn);
   $clients = Client::clients([], false, $DBConn);
   $entities = Data::entities([], false, $DBConn);
   

   ?>

   <div class="form-group my-2">
      <label for="orgSelect" class="form-label">Select Organisation</label>
      <select id="orgSelect" name="orgSelect" class="form-control form-control-sm">
         <option value="">Select Organisation</option>
         <?php foreach ($orgs as $org) { ?>
            <option value="<?php echo $org->orgDataID; ?>"><?php echo $org->orgName; ?></option>
         <?php } ?>
      </select>
   </div>
   <div class="form-group my-2">
      <label for="entityID"> Entity</label>
      <select id="entityID" name="entityID" class="form-control form-control-sm">
         <option value="">Select Entity</option>
         <?php foreach ($entities as $entity) { ?>
            <option value="<?php echo $entity->entityID; ?>"><?php echo $entity->entityName; ?></option>
         <?php } ?>
      </select>
   </div>



   <div class="mb-3">
      <label for="formFileSm" class="form-label">Small file input example</label>
      <input class="form-control form-control-sm" id="formFileSm" name="salesUpload" type="file">
   </div>

</div>