<div class="row">
   <div class="form-group">
      <label for="orgDataID"> Organisation</label>
      <?php 
      $organisation = Admin::organisation_data_mini([], false, $DBConn); ?>
      <select class="form-select" id="orgDataID" name="orgDataID">
         <option value="">Select Organisation</option>
         <?php foreach ($organisation as $org) : ?>
         <option value="<?php echo $org->orgDataID ?>"><?php echo $org->orgName ?></option>
         <?php endforeach; ?>
      </select>
   </div>
   <div class="form-group">
      <label for="productID"> Product</label>
      <?php 
      $products = Admin::tija_products([], false, $DBConn); ?>
      <select class="form-select" id="productID" name="productID">
         <option value="">Select Product</option>
         <?php foreach ($products as $product) : ?>
         <option value="<?php echo $product->productID ?>"><?php echo $product->productName ?></option>
         <?php endforeach; ?>
      </select>
   </div>

   <!-- Add Enttity -->
   <div class="form-group">
      <label for="entityID"> Entity</label>
      <?php 
      $entities = Data::Entities([], false, $DBConn); ?>
      <select class="form-select" id="entityID" name="entityID">
         <option value="">Select Entity</option>
         <?php foreach ($entities as $entity) : ?>
         <option value="<?php echo $entity->entityID ?>"><?php echo $entity->entityName ?></option>
         <?php endforeach; ?>
      </select>
   </div>

   <div class="mb-3 form-group">
      <label for="usersFile" class="form-label">Upload Users CSV File</label>
      <input class="form-control form-control-sm" id="usersFile" name="users file" type="file">
   </div>
</div>