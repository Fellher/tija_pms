<div class="row my-4">
   <div class="col-md-7">
      <div class="row nogutters ">         
         <div class=" d-flex  justify-content-between ">
            <h1 class="page-title fs-20 bg-light"> Product Rates </h1>
            <div class="card-options">
               <a href="#manage_billing_rate" class="btn btn-primary btn-sm float-end" data-bs-toggle="modal"   data-bs-target="#manage_billing_rate">Add Product Rate</a>
            </div>
         </div>
         <?php
         $productRates = Projects::product_rates(['projectID'=>$projectID], false, $DBConn);
         // var_dump($productRates);
         if($productRates) {?>
            <table class="table table-striped">
               <thead>
                  <tr>
                     <th>Product Rate Name</th>
                     <th>Price Rate</th>
                     <th>Actions</th>
                  </tr>
               </thead>
               <tbody>
                  <?php foreach ($productRates as $rate) { ?>
                     <tr>
                        <td><?php echo htmlspecialchars($rate->productRateName); ?></td>
                        <td><?php echo Utility::formatToCurrency(htmlspecialchars($rate->priceRate)); ?></td>
                        <td class="text-end">
                           <a 
                              href="javascript:void(0)" 
                              class="btn btn-sm btn-primary editProductRate" 
                              data-bs-toggle="modal" 
                              data-bs-target="#manage_product_rate" 
                              data-id="<?php echo $rate->productRateID; ?>"
                              data-name="<?php echo $rate->productRateName; ?>"
                              data-productRateTypeID="<?php echo $rate->productRateTypeID; ?>"
                              data-priceRate="<?php echo $rate->priceRate; ?>"
                              data-suspended="<?php echo $rate->Suspended; ?>"
                           >
                           Edit
                           </a>
                           <a href="javascript:void(0)" 
                              class="btn btn-sm btn-danger" 
                              data-bs-toggle="modal" 
                              data-bs-target="#deleteProductRateModal" 
                              data-id="<?php echo  $rate->productRateID; ?>">
                           Delete
                           </a>
                        </td>
                     </tr>
                  <?php } ?>
               </tbody>
            </table>
            <?php
         } else {
            echo "<div class='col-md-12'>
                     <div class='card card-body border-0 shadow-sm mb-3'>
                        <h5 class='card-title'>No product rates found</h5>
                        <p class='card-text'>You can add product rates by clicking the button below.</p> 
                        <a href='javascript:void(0)' class='btn btn-primary btn-sm' data-bs-toggle='modal' data-bs-target='#manage_billing_rate'>Add Product Rate</a>
                     </div>
                  </div>";
         }?>
      </div>
   </div>
   
   <div class="col-md-5">
      <div class="row nogutters ">
         <div class=" d-flex  justify-content-between ">
            <h1 class="page-title fs-20 bg-light"> Product Rate Types </h1>
            <div class="card-options">
               <a href="#manage_product_rate_types" class="btn btn-primary btn-sm float-end" data-bs-toggle="modal"   data-bs-target="#manage_product_rate_types">Add Product Rate Types</a>
            </div>
         </div>
         <?php
         
         // var_dump($productRateTypes);
         if($productRateTypes) {?>
            <div class="list-group list-group-flush">
              
               <?php
               foreach ($productRateTypes as $rate){ ?>
                  <div class="list-group-item d-flex justify-content-between align-items-center">
                     <div>
                        <h5 class="mb-0 fs-16"><?php echo htmlspecialchars($rate->productRateTypeName); ?></h5>
                        <p class="mb-0 fs-12"><?php echo htmlspecialchars($rate->productRateTypeName); ?></p>
                     </div>
                     <div class="">
                        <a 
                           href="javascript:void(0)" 
                           class="btn btn-sm btn-primary editProductRateType" 
                           data-bs-toggle="modal" 
                           data-bs-target="#manage_product_rate_types" 
                           data-id="<?php echo $rate->productRateTypeID; ?>"
                           data-name="<?php echo $rate->productRateTypeName; ?>"
                           data-description="<?php echo $rate->productRateTypeDescription; ?>"
                           data-suspended="<?php echo $rate->Suspended; ?>"
                        >
                        Edit
                        </a>
                        <a href="javascript:void(0)" 
                           class="btn btn-sm btn-danger" 
                           data-bs-toggle="modal" 
                           data-bs-target="#deleteProductRateTypeModal" 
                           data-id="<?php echo  $rate->productRateTypeID; ?>">
                        Delete
                        </a>
                     </div>
                  </div>
               <?php
               }?>
            </div>
               <?php

         } else {
            echo "<div class='col-md-12'>
                     <div class='card card-body border-0 shadow-sm mb-3'>
                        <h5 class='card-title'>No product rates found</h5>
                        <p class='card-text'>You can add product rates by clicking the button below.</p>
                        <a href='javascript:void(0)' class='btn btn-primary btn-sm' data-bs-toggle='modal' data-bs-target='#manage_billing_rate'>Add Product Rate</a>
                     </div>
                  </div>";
         }?>         
      </div>
   
   </div>
</div>
<?php 
echo Utility::form_modal_header("manage_product_rate_types", "projects/manage_product_rate_types.php", "Manage Product Rate Type", array('modal-md', 'modal-dialog-centered'), $base);
include_once("includes/scripts/projects/billing/modals/manage_product_rate_types.php");
echo Utility::form_modal_footer("Save Product Rate Type", "manage_product_rate_types_submit", 'btn btn-primary btn-sm');

echo Utility::form_modal_header("manage_billing_rate", "projects/manage_product_rate.php", "Manage Product Rate", array('modal-lg', 'modal-dialog-centered'), $base);
include_once("includes/scripts/projects/billing/modals/manage_product_rate.php");
echo Utility::form_modal_footer("Save Product Rate", "manage_product_rate_submit", 'btn btn-primary btn-sm');
?>
