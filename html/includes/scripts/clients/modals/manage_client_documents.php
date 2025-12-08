<div class="client-documents" id="clientDocumentsModalForm">
   <input type="hidden" name="clientID" id="clientID" value="<?= isset($clientID) ? $clientID : (isset($clientDetails) ? $clientDetails->clientID : '') ?>">
   <input type="hidden" name="clientDocumentID" id="clientDocumentID" value="">

   <div class="form-group">
      <label for="documentName" class="form-label">Document Name</label>
      <input type="text" id="clientDocumentName" name="clientDocumentName" class="form-control-sm px-2 form-control-plaintext bg-light border-bottom" placeholder="Enter document name" required>
   </div>
   <div class="form-group">
      <label for="documentFile" class="form-label">Upload Document</label>
      <input type="file" id="clientDocumentFile" name="clientDocumentFile" class="form-control " accept=".pdf,.doc,.docx,.txt" required>
   </div>
   <div class="form-group">
      <label for="document_description" class="form-label">Description</label>
      <textarea id="clientDocumentDescription" name="clientDocumentDescription" class="form-control-sm p-2 form-control-plaintext bg-light border-bottom" placeholder="Enter document description" rows="3"></textarea>
   </div>
   <div class="form-group">
      <label for="document_type" class="form-label">Document Type</label>
      <select id="document_type" name="documentTypeID" class="form-control-sm px-2 form-control-plaintext bg-light border-bottom" required>
         <!-- Insert options here -->
          <?= Form::populate_select_element_from_object(
            $documentTypes,
            'documentTypeID',
            'documentTypeName',
            "",
            '',
            'Select Document Type')

            ?>
             <option value="other">Add New Document Type</option>
      </select>
   </div>
   <div class="card card-body bg-light shadow-lg my-3 newDocumentTypeDiv d-none">
      <h5 class="t300 border-bottom border-bottom-2" >Add new Document Type</h5>
      <div class="form-group">
         <label for="documentTypeName"> Document Type NAme</label>
         <input type="text" id="documentTypeName" name="documentTypeName" class="form-control-sm px-2 form-control-plaintext bg-light-blue border-bottom" placeholder="Enter document type name">

      </div>
      <div class="form-group">
         <label for="documentTypeDescription">Document Type Description</label>
         <textarea id="documentTypeDescription" name="documentTypeDescription" class="form-control-sm p-2 form-control-plaintext bg-light-blue border-bottom" placeholder="Enter document type description" rows="3"></textarea>
      </div>
   </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const documentTypeSelect = document.getElementById('document_type');
    const newDocumentTypeDiv = document.querySelector('.newDocumentTypeDiv');

    documentTypeSelect.addEventListener('change', function() {
        if (this.value === 'other') {
            newDocumentTypeDiv.classList.remove('d-none');
        } else {
            newDocumentTypeDiv.classList.add('d-none');
        }
    });
});
</script>