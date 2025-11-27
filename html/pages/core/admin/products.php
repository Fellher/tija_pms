<!-- Page Header -->
<div class="d-md-flex d-block align-items-center justify-content-between my-4 page-header-breadcrumb border-bottom">
    <h1 class="page-title fw-medium fs-24 mb-0">Tija Products</h1>
    <div class="ms-md-1 ms-0">
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $s ?></a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0);"><?php echo $ss ?></a></li>
                <li class="breadcrumb-item active d-inline-flex" aria-current="page">Tija Products</li>
            </ol>
        </nav>
    </div>
</div>

<?php  $products = Admin::tija_products(array("suspended"=>'N'), false, $DBConn); ?>


<div class="row d-flex align-items-stretch ">
    <div class="col-12 ">
        <div class="card custom-card">
            <div class="card-body p-0">
                <div class="card-header">
                    <div class="card-title d-block col-12 ">
                        <i class="ri ri-shield-user-line me-2"></i>Product Details 
                        <a class="btn btn-sm btn-icon rounded-pill btn-primary-light float-end editOrganisation"  data-bs-toggle="collapse"href="#addProduct" role="button" aria-expanded="false"  aria-controls="addProduct" > <i class="ri-folder-add-line"></i> </a> </div>
                    </div>
                </div>
                <div class="collapse container p-2" id="addProduct">
                    <form action="<?php echo "{$base}php/scripts/global/manage_product_details.php" ?>" method="POST"   enctype='multipart/form-data' class="m-0 bg-light border-0 rounded shadow-lg p-3"   id="productForm">
                        <?php include "includes/core/admin/manage_product_form.php" ?>
                    </form>
                </div>
                <div class="container-fluid">

                
                    <div class="list-group list-group-flush">
                        <?php 
                        if($products) {
                            foreach ($products as $key => $product) {?>
                            <div class="list-group-item list-group-item-action" aria-current="true">
                                <form action="<?php echo "{$base}php/scripts/global/manage_product_details.php" ?>" method="POST"   enctype='multipart/form-data' class="<?php echo "form_{$product->productID}" ?>">                         
                                    <div class="d-sm-flex w-100 justify-content-between">
                                        <input type="hidden" name="productID" value="<?php echo $product->productID ?>">
                                        <h6 class="mb-1 fw-semibold">    
                                            <?php echo $product->productName ?> </h6>
                                        <small>
                                            <div class="btn-list">                                          
                                                <a aria-label="anchor" href="javascript:void(0);" data-bs-toggle="tooltip" data-id = "<?php echo $product->productID ?>" data-bs-placement="top" data-bs-title="Edit" class="btn  btn-icon rounded-pill btn-secondary-light btn-wave btn-sm productEdit "><i class="ti ti-pencil"></i></a>
                                                <a aria-label="anchor" href="javascript:void(0);" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Delete" class="btn  btn-icon rounded-pill  btn-danger-light btn-wave btn-sm productDelete "><i class="ti ti-trash"></i></a>
                                            </div>
                                        </small>
                                    </div>                                                             
                                    <p class="mb-1"><?php echo $product->productDescription; ?></p>
                                    <div class="bg-light border-0 rounded-2 shadow-lg card card-body editForm d-none">
                                        <?php include "includes/core/admin/manage_product_form.php" ?>
                                    </div>                                    
                                </form>
                            </div>
                            <?php 
                            }
                        } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
