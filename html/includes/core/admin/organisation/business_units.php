<?php 
$getString .="&state={$state}";
if(!$isAdmin && !$isValidAdmin) {
    Alert::info("You need to be logged in as a valid administrator to access this page", true, array('fst-italic', 'text-center', 'font-18'));
    return;
}
$unitType = Data::unit_types([], false, $DBConn);
?>

<div class="row d-flex align-items-stretch ">
   <div class="col-12 ">
      <div class="card custom-card shadow-sm m-3">
         <div class="card-header border-bottom-2 justify-content-between d-flex align-items-center">
            <div class="card-title d-flex align-items-center">
               <i class="bi bi-building fs-3 me-2"></i>
               <h4 class="mb-0">Business Units</h4>
            </div>
            <div class="card-options">
               <a href="javascript:void(0)" class="btn btn-sm btn-primary" id="addBusinessUnit" data-bs-toggle="modal" data-bs-target="#manageBusinessUnit">Add Business Unit</a>
            </div>
         </div>
         <div class="card-body">
            <?php
            $businessUnits = Data::business_units([], false, $DBConn);

            if($businessUnits){
               foreach($businessUnits as $unit){
                  $unitTypeDetails = Data::unit_types(array('unitTypeID'=>$unit->unitTypeID), true, $DBConn);
                  $unitTypeName = $unitTypeDetails ? $unitTypeDetails->unitTypeName : "N/A";
                  ?>
                  <div class="card card-body bs-gray-300 my-3 p-2 shadow-sm">
                     <div class="d-flex justify-content-between align-items-center col-12">
                        <div class="col-md-11">
                           <h5 class="mb-0 fs-16"><?php echo $unit->businessUnitName; ?>  <span class="mb-0 fst-italic text-primary fs-12 float-end fst-bold">Business Unit Type: <span class="fst-bold  text-dark"> <?php echo $unitTypeName; ?></span></span></h5>
                           <p class="mb-0 fst-italic"><?php echo $unit->businessUnitDescription; ?></p>
                         
                        </div>
                        <div>
                           <a href="javascript:void(0)" 
                              class="btn btn-sm btn-outline-primary editBusinessUnit" 
                              
                              data-bs-toggle="modal" 
                              data-bs-target="#manageBusinessUnit"
                              data-business-unit-id="<?php echo $unit->businessUnitID; ?>"
                              data-business-unit-name="<?php echo $unit->businessUnitName; ?>"
                              data-business-unit-description="<?php echo $unit->businessUnitDescription; ?>"
                              data-business-unit-type-id="<?php echo $unit->unitTypeID; ?>"
                              

                           >
                              Edit
                           </a>
                        </div>
                     </div>
                  </div>
                  <?php
               }
              
            } else {
               Alert::info("No business units found", true, array('fst-italic', 'text-center', 'font-18'));
            }
          
            ?>

         </div>
      </div>
   </div>
</div>
 
<?php
echo Utility::form_modal_header("manageBusinessUnit", "organisation/manage_business_unit.php", "Manage Business Unit", array("modal-lg", "modal-dialog-centered"), $base);
include "includes/core/admin/organisation/modals/manage_business_units.php";
echo Utility::form_modal_footer("Save", "submit_business_unit", "btn btn-success btn-sm");
?>

<script>
   document.addEventListener('DOMContentLoaded', function() {
      document.querySelectorAll(".editBusinessUnit").forEach((btn) => {
         btn.addEventListener("click", (e) => {
            console.log(e.target);

            const businessUnitID = e.target.getAttribute("data-business-unit-id");
            const businessUnitName = e.target.getAttribute("data-business-unit-name");
            const businessUnitDescription = e.target.getAttribute("data-business-unit-description");
            const unitTypeID = e.target.getAttribute("data-business-unit-type-id");

            document.querySelector("#businessUnitID").value = businessUnitID;
            document.querySelector("#businessUnitName").value = businessUnitName;
            // initialize tinyMCE editor
            tinymce.init({
               selector: '#businessUnitDescription',
               plugins: 'lists link image code',
               toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | code',
               menubar: false,
               setup: function (editor) {
                  editor.on('init', function () {
                     editor.setContent(businessUnitDescription);
                  });
               }
            });
            // Handle tinyMCE editor
            const editor = tinymce.get('businessUnitDescription'); 

            if (editor) {
                // Wait for a brief moment to ensure tinyMCE is fully initialized
                setTimeout(() => {
                    editor.setContent(businessUnitDescription || '');
                }, 100);
            }

            // document.querySelector("#businessUnitDescription").value = businessUnitDescription;
            document.querySelector("#unitTypeID").value = unitTypeID;
         });
      });


   });

</script>


