<div id="proposalAttachmentsForm">
   <div class="row">
      <input type="hidden" name="proposalID" value="<?= $proposalID ?>">
      <input type="hidden" name="proposalAttachmentID" value="">

      <div class="form-group">
         <label for="proposalAttachmentName" class="text-primary">Proposal Attachment Name</label>
         <input type="text" class="form-control-sm form-control border-bottom" id="proposalAttachmentName" name="proposalAttachmentName" value="">
      </div>

      <div class="form-group">
         <label for="proposalAttachmentFile" class="text-primary">Proposal Attachment File</label>
         <input type="file" class="form-control-sm form-control border-bottom" id="proposalAttachmentFile" name="proposalAttachmentFile" value="">
      </div>     
      <div class="form-group">
         <label for="proposalAttachmentType" class="text-primary">Proposal Attachment Type</label>
         <select class="form-control-sm form-control-plaintext border-bottom" id="proposalAttachmentType" name="proposalAttachmentType" value="">
         <?= Form::populate_select_element_from_object($checklistItems, 'proposalChecklistItemID', 'proposalChecklistItemName', '', '', 'Select Proposal Attachment Type'); ?>
         </select>
      </div>

   </div>   
</div>
