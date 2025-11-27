<div class=" card-header mb-3 pb-0 d-none">
   <!-- Header moved to contact_info.php to include info icon -->
</div>
   <?php
   echo Utility::form_modal_header("manageClientDocuments", "clients/manage_client_documents.php", "Manage Client Documents", array('modal-lg', 'modal-dialog-centered'), $base);
   include "includes/scripts/clients/modals/manage_client_documents.php";
   echo Utility::form_modal_footer('Save Client Documents', 'saveClientDocuments',  ' btn btn-success btn-sm', true);
   $clientDocuments = Client::client_documents(array('clientID'=>$clientDetails->clientID), false, $DBConn);?>
   <div class="row">
      <?php
      if($clientDocuments){
         foreach ($clientDocuments as $key => $clientDocument) {?>
            <div class="col-lg-4 col-md-4 col-12">
               <div class="alert alert-secondary fade show custom-alert-icon shadow-sm" role="alert">
                  <div class="d-flex justify-content-between align-items-center">
                     <div class="d-flex align-items-center">
                        <i class="ri-file-text-line ri-2x me-2"></i>
                        <div>
                           <h5 class="alert-heading mb-0"><?= $clientDocument->clientDocumentName ?></h5>
                           <p class="mb-0 fst-italic text-muted"><?= $clientDocument->clientDocumentDescription ?></p>
                           <span class="badge rounded-pill bg-purple-transparent py-1"><?= $clientDocument->documentTypeName ?></span>
                        </div>
                     </div>
                     <div>
                        <?php
                        // var_dump($clientDocument);?>
                        <a href="<?= "{$config['DataDir']}{$clientDocument->clientDocumentFile}" ?>" class="btn btn-primary btn-sm rounded-circle" title="Download Document">
                           <i class="ri-download-2-line"></i>
                        </a>
                        <a href="#manageClientDocuments"
                           class="btn btn-info btn-sm rounded-circle edit-client-document fs-22"
                           data-bs-toggle="modal"
                           role="button"
                           aria-expanded="false"
                           aria-controls="manageClientDocuments"
                           data-client-document-id="<?= $clientDocument->clientDocumentID ?>"
                           data-client-id="<?= $clientDocument->clientID ?>"
                           data-client-document-name="<?= $clientDocument->clientDocumentName ?>"
                           data-client-document-description="<?= $clientDocument->clientDocumentDescription ?>"
                           data-document-type-id="<?= $clientDocument->documentTypeID ?>"
                           data-document-file-name ="<?=   $clientDocument->documentFileName ?>"
                           data-document-file-path="<?= $clientDocument->documentFilePath ?>"
                           data-client-document-file ="<?= $clientDocument->clientDocumentFile ?>"
                           title="Edit Document"
                        >
                           <i class="ri-pencil-line"></i>
                        </a>
                        <a href="<?= "{$base}php/scripts/clients/manage_client_documents.php?clientDocumentID={$clientDocument->clientDocumentID}&action=delete" ?>" class="btn btn-danger btn-sm rounded-circle" title="Delete Document" onclick="return confirm('Are you sure you want to delete this document?');">
                           <i class="ri-delete-bin-5-line"></i>
                        </a>
                     </div>
                  </div>
               </div>
            </div>
            <?php
         }
      } else {
         Alert::info("No client documents found. Click on the edit icon to add new documents.", true, array('fst-italic', 'text-center', 'font-18'));
      }?>
   </div>

<script>
   document.addEventListener('DOMContentLoaded', function() {
      document.querySelectorAll('.edit-client-document').forEach(button => {
         button.addEventListener('click', function() {
            // Get the form element
            console.log('Edit client document button clicked');
            // console.log(button);
            const form = document.getElementById('clientDocumentsModalForm');
            if (!form) return;

            // console.log(form);
               // Get all data attributes from the button
               const data = this.dataset;
               console.log(data);
            const fieldMappings= {
                  'clientDocumentID': 'clientDocumentId',
                  'clientID': 'clientId',
                  'clientDocumentName': 'clientDocumentName',
                  'clientDocumentDescription': 'clientDocumentDescription',
                  'documentTypeID': 'documentTypeId',
                  'clientDocumentFile': 'clientDocumentFile',
               // This will be handled separately for file inputs
               };

            // Loop through the field mappings and set the form values
            for (const [field, dataAttr] of Object.entries(fieldMappings)) {
               const input = form.querySelector(`[name="${field}"]`);
               // if (input) {
               //    input.value = data[dataAttr] || '';
               // }
               // console.log(input, data[dataAttr]);
               // check that input is a file input
               if (input && input.type === 'file') {
                  input.value = ''; // Clear the file input
                  const documentNameElement = document.createElement('span');
                  documentNameElement.className = 'document-name d-block mt-2 badge rounded-pill bg-secondary-transparent  w-100';
                  documentNameElement.innerHTML = `<i class="ri-file-text-line ri-2x me-4"></i>${data.clientDocumentName}`;
                  input.parentNode.insertBefore(documentNameElement, input.nextSibling);
                  input.placeholder = 'Select new file to change the document';
               } else {
                  input.value = data[dataAttr] || '';
                  console.log(`object ${field} set to ${data[dataAttr]}`);
               }
            }
         });
      });
   });
</script>