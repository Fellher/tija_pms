<div class="card  customized-card my-4">
   <div class="card-header d-flex justify-content-between align-items-center border-bottom border-primary border-bottom-2">
      <h4 class="mb-0 t400 font-18">Proposal Checklist Items</h4>            						
      <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manageChecklistItemModal">
         Add Checklist item
      </button>
   </div>
   <div class="card-body">
      <?php 
 
      if($checklistItems){
         foreach ($checklistItems as $key => $item) {?>
            <div class="row border-bottom py-2 d-flex-xl align-items-center">
               <div class="col-8 ">
                  <span class="t400 font-14  d-block">
                     <?php echo $item->proposalChecklistItemName; ?>
                     <span class="badge rounded-pill bg-primary-transparent float-end my-1" > 
                        <?= $item->proposalChecklistItemCategoryName ?> 
                     </span> 
                  </span>
                  <?= $item->proposalChecklistItemDescription ? "<span class='fst-italic text-muted font-14'>".$item->proposalChecklistItemDescription."</span>" : ""; ?>
               </div>
               <div class="col-4 text-end">
                  <button 
                     type="button" 
                     class="btn btn-primary btn-sm editChecklistItem" 
                     data-bs-toggle="modal" 
                     data-bs-target="#manageChecklistItemModal" 
                     data-proposal-checklist-item-id="<?php echo $item->proposalChecklistItemID; ?>" 
                     data-proposal-checklist-item-name="<?php echo $item->proposalChecklistItemName; ?>" 
                     data-proposal-checklist-item-description="<?php echo $item->proposalChecklistItemDescription; ?>"                   
                     data-proposal-checklist-item-category-id="<?php echo $item->proposalChecklistItemCategoryID; ?>"
                  >
                     Edit
                  </button>
               </div>
            </div>
            <?php
         }
      } else {
         Alert::error("No checklist items found", true, array('fst-italic', 'text-center', 'font-18'));
      }?>
   </div>
</div>
<?php
echo Utility::form_modal_header("manageChecklistItemModal", "sales/proposal_checklist/manage_proposal_checklist_item.php", "Manage Checklist Item", array('modal-md', 'modal-dialog-centered'), $base);
   include "includes/scripts/sales/proposal_checklist/modals/manage_proposal_checklist_items.php";
echo Utility::form_modal_footer("Save Checklist Item", "manageChecklistItem", 'btn btn-primary btn-sm');
?>