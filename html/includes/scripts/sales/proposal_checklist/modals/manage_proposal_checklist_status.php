<div id="checklistStatusForm">
   <div class="row">
      <div class="form-group my-2">
         <label for="proposalChecklistStatusName" class="text-primary"> Proposal Checklist & Item Status Name</label>
         <input type="text" class="form-control-plaintext border-bottom form-control-sm" id="proposalChecklistStatusName" name="proposalChecklistStatusName" placeholder="Enter Proposal Checklist & Item Status Name">
      </div>

      <div class="form-group my-2">
         <label for="proposalChecklistStatusDescription" class="text-primary">Proposal Checklist & Item Status Description</label>
         <textarea class="form-control form-control-sm borderless-mini" id="proposalChecklistStatusDescription" name="proposalChecklistStatusDescription" placeholder="Enter Proposal Checklist & Item Status Description"></textarea>
      </div>
      <div class="form-group my-2">
         <label for="proposalChecklistStatusColor" class="text-primary">Proposal Checklist category</label>
         
         <select class="form-control-plaintext border-bottom form-control-sm " id="proposalChecklistStatusType" name="proposalChecklistStatusType">
            <option value="">Select Proposal Checklist category</option>
            <?php
            if($config['checklistStatusType']) {
               foreach ($config['checklistStatusType']  as $checklistStatusType) {?>
                  <option value="<?php echo $checklistStatusType->key; ?>"><?php echo $checklistStatusType->value; ?></option>
               <?php
               }
            }?>
      </div>
      <input type="text" class="form-control-plaintext border-bottom form-control-sm" id="orgDataID" name="orgDataID" value="<?php echo $orgDataID; ?>" hidden>
      <input type="text" class="form-control-plaintext border-bottom form-control-sm" id="entityID" name="entityID" value="<?php echo $entityID; ?>" hidden>
      <input type="text" class="form-control-plaintext border-bottom form-control-sm" id="proposalChecklistStatusID" name="proposalChecklistStatusID" value="" hidden>
   </div>   
</div>