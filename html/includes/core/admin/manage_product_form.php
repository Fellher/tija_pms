<div class="row">
    <div class="form-group col-xl-4 mb-2">
        <label for="productName" class="nott mb-0 t400 text-primary"> Product Name</label>
        <input type="text" name="productName" id="productName" class="form-control-xs form-control-plaintext border-bottom " value="<?php echo (isset($product->productName) && !empty($product->productName) ) ? $product->productName : "" ?>" placeholder="Add Product Name" required>
    </div>
    <div class="form-group col-xl-4 mb-2">
        <label for="productDescription" class="nott mb-0 t400 text-primary"> Product Description</label>
        <textarea name="productDescription" id="productDescription" class="form-control-xs form-control-plaintext border-bottom " placeholder="Add Product Description" required>
        <?php echo (isset($product->productDescription) && !empty($product->productDescription) ) ? $product->productDescription : "" ?>
        </textarea>
    </div>
    <div class="text-center col-xl-4 mt-3 saveProductDiv">
        <button type="submit" class="btn btn-primary-light btn-sm rounded-pill" id="saveProduct">Save Product</button>
    </div>
</div>

