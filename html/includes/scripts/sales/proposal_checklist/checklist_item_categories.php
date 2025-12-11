<div class="card  customized-card my-4">
   <div class="card-header d-flex justify-content-between align-items-center border-bottom border-primary border-bottom-2">
      <h4 class="mb-0 t400 font-18">Proposal Checklist Items Categories</h4>
      <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manageChecklistItemCategoriesModal">
         Add Checklist item Category
      </button>
   </div>
   <div class="card-body">
      <?php
      $checklistItemCategories = Proposal::proposal_checklist_items_categories([], false, $DBConn);
      if($checklistItemCategories){
         foreach ($checklistItemCategories as $key => $itemCategories) {?>
               <div class="row border-bottom py-2 d-flex-xl align-items-center">
                  <div class="col-8 ">
                     <span class="t400 font-14  d-block">
                        <?php echo $itemCategories->proposalChecklistItemCategoryName; ?>

                     </span>
                     <?php echo $itemCategories->proposalChecklistItemCategoryDescription ? "<span class='fst-italic text-muted font-14'>".$itemCategories->proposalChecklistItemCategoryDescription."</span>" : ""; ?>
                  </div>
                  <div class="col-4 text-end">
                     <button
                        type="button"
                        class="btn btn-primary btn-sm editChecklistItemCategory"
                        data-bs-toggle="modal"
                        data-bs-target="#manageChecklistItemCategoriesModal"
                        data-proposal-checklist-item-category-id="<?php echo $itemCategories->proposalChecklistItemCategoryID; ?>"
                        data-proposal-checklist-item-category-name="<?php echo $itemCategories->proposalChecklistItemCategoryName; ?>"
                        data-proposal-checklist-item-category-description="<?php echo $itemCategories->proposalChecklistItemCategoryDescription; ?>"

                     >
                        Edit
                     </button>
                  </div>
               </div>

            <?php
         }
      } else {
         Alert::error("No checklist item categories found", true, array('fst-italic', 'text-center', 'font-18'));
      }?>
   </div>
</div>
<?php
echo Utility::form_modal_header("manageChecklistItemCategoriesModal", "sales/proposal_checklist/manage_proposal_checklist_item_categories.php", "Manage Checklist Item", array('modal-md', 'modal-dialog-centered'), $base);
   include "includes/scripts/sales/proposal_checklist/modals/manage_proposal_checklist_items_categories.php";
echo Utility::form_modal_footer("Save Checklist Item Category", "manageChecklistItemCAtegory", 'btn btn-primary btn-sm', true);
?>

<script>
document.addEventListener("DOMContentLoaded", function(event) {
   document.querySelectorAll('.editChecklistItemCategory').forEach(button => {
      button.addEventListener('click', function() {
         const form = document.getElementById('manageChecklistItemCategoriesForm');
         if (!form) return;

         // Get all data attributes from the button
         const data = this.dataset;
         console.log(data);

         // Map form fields to their corresponding data attributes
         const fieldMappings = {
            'proposalChecklistItemCategoryName': 'proposalChecklistItemCategoryName',
            'proposalChecklistItemCategoryDescription': 'proposalChecklistItemCategoryDescription',
            'proposalChecklistItemCategoryID': 'id'
         };

         // Set the values in the form
         for (const [field, dataAttr] of Object.entries(fieldMappings)) {
            const input = form.querySelector(`input[name="${field}"]`);
            if (input) {
               input.value = data[dataAttr];
            }
         }
           // Fill the textarea with tinyMCE
         tinymce.init({
            selector: '#proposalChecklistItemCategoryDescription'
         });

         // Handle tinyMCE editor
         const editor = tinymce.get('proposalChecklistItemCategoryDescription'); // Make sure 'entityDescription' matches your textarea's ID
         if (editor) {
            // Wait for a brief moment to ensure tinyMCE is fully initialized
            setTimeout(() => {
               editor.setContent(data['proposalChecklistItemCategoryDescription'] || '');
            }, 100);
         }

      });
   });

  console.log("Page is loaded");
});
</script>