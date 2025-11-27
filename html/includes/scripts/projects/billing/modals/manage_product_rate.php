<div id="manage_product_rate_form">
   <input type="hidden" id="productRateID" name="productRateID" value="">
   <input type="hidden" id="entityID" name="entityID" value="<?= $entityID; ?>">
   <input type="hidden" id="projectID" name="projectID" value="<?= $projectID; ?>">

   <div class="row">
      <div class="form-group col-md-6 col-12"> 
         <label for="productRateName" class="form-label">Product Rate Name</label>
         <input type="text" class="form-control form-control-sm border-bottom" id="productRateName" name="productRateName" placeholder="Product Rate Type Name" required>
      </div>

     <div class="form-group col-md-6 col-12"> 
         <label for="productRateTypeID" class="form-label">Product Rate Type</label>
         <select class="form-select-sm form-control-plaintext border-bottom" id="productRateTypeID" name="productRateTypeID" required>
            <option value="">Select Product Rate Type</option>
            <?php
            if($productRateTypes) {
               foreach ($productRateTypes as $rate) { ?>
                  <option value="<?php echo $rate->productRateTypeID; ?>"><?php echo htmlspecialchars($rate->productRateTypeName); ?></option>
               <?php }
            } else { ?>
               <option value="">No Product Rate Types found.</option>
            <?php } ?>
         </select>
     </div>
     <div class="form-group">
         <label for="priceRate" class="form-label">Price Rate</label>
         <input type="text" class="form-control form-control-sm border-bottom" id="priceRate" name="priceRate" placeholder="Price Rate" required>
     </div>
     
   </div>
</div>