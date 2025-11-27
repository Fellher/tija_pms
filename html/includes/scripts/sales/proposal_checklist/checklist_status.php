<div class="card  customized-card my-4">
   <div class="card-header d-flex justify-content-between align-items-center border-bottom border-primary border-bottom-2">
      <h4 class="mb-0 t400 font-18">Proposal Checklist Statuses</h4>            						
      <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manageChecklistStatusModal">
         Add Checklist Status
      </button>
   </div>
   <div class="card-body">
      <?php
   
      if($checklistStatuses){
         foreach ($checklistStatuses as $key => $status) {?>
            <div class="row border-bottom py-2 d-flex-xl align-items-center">
               <div class="col-8 ">
                  <span class="t400 font-14  d-block">
                     <?php echo $status->proposalChecklistStatusName; ?>
                     <span class="badge rounded-pill bg-primary-transparent float-end my-1" > 
                        <?= $status->proposalChecklistStatusType ?> 
                     </span> 
                  </span>
                  <?= $status->proposalChecklistStatusDescription ? "<span class='fst-italic text-muted font-14'>".$status->proposalChecklistStatusDescription."</span>" : ""; ?>
               </div>
               <div class="col-4 text-end">
                  <button 
                     type="button" 
                     class="btn btn-primary btn-sm" 
                     data-bs-toggle="modal" 
                     data-bs-target="#manageChecklistStatusModal" 
                     data-proposal-checklist-status-id="<?php echo $status->proposalChecklistStatusID; ?>" 
                     data-proposal-checklist-status-name="<?php echo $status->proposalChecklistStatusName; ?>" 
                     data-proposal-checklist-status-description="<?php echo $status->proposalChecklistStatusDescription; ?>"
                     data-proposal-checklist-status-type="<?php echo $status->proposalChecklistStatusType; ?>"
                     data-org-data-id= "<?php echo $status->orgDataID; ?>"
                     data-entity-id= "<?php echo $status->entityID; ?>"
                  >
                     Edit
                  </button>
               </div>
            </div>
            <?php     
         }
      } else {
         Alert::error("No checklist statuses found", true, array('fst-italic', 'text-center', 'font-18'));
      }?>

   </div>
   
</div>
<?php
echo Utility::form_modal_header("manageChecklistStatusModal", "sales/proposal_checklist/manage_proposal_checklist_status.php", "Manage Proposal Checklist", array('modal-md', 'modal-dialog-centered'), $base);
   include "includes/scripts/sales/proposal_checklist/modals/manage_proposal_checklist_status.php";
echo Utility::form_modal_footer("Save Checklist", "manageChecklist", 'btn btn-primary btn-sm');
?>