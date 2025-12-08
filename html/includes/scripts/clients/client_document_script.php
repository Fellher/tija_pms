<!-- Documents Section Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
   <div>
      <h5 class="mb-1 fw-semibold">Client Documents</h5>
      <p class="text-muted small mb-0">Manage all documents related to this client</p>
   </div>
   <button type="button" class="btn btn-primary add-client-document" data-bs-toggle="modal" data-bs-target="#manageClientDocuments">
      <i class="ri-file-add-line me-1"></i>Add Document
   </button>
</div>

<?php
echo Utility::form_modal_header("manageClientDocuments", "clients/manage_client_documents.php", "Manage Client Documents", array('modal-lg', 'modal-dialog-centered'), $base);
include "includes/scripts/clients/modals/manage_client_documents.php";
echo Utility::form_modal_footer('Save Document', 'saveClientDocuments',  ' btn btn-success btn-sm', true);
$clientDocuments = Client::client_documents(array('clientID'=>$clientDetails->clientID), false, $DBConn);
?>

<?php if($clientDocuments && count($clientDocuments) > 0): ?>
   <div class="row g-3">
      <?php foreach ($clientDocuments as $key => $clientDocument):
         // Get file extension for icon
         $fileExt = strtolower(pathinfo($clientDocument->documentFileName, PATHINFO_EXTENSION));
         $iconClass = 'ri-file-text-line';
         $iconColor = 'primary';

         switch($fileExt) {
            case 'pdf':
               $iconClass = 'ri-file-pdf-line';
               $iconColor = 'danger';
               break;
            case 'doc':
            case 'docx':
               $iconClass = 'ri-file-word-line';
               $iconColor = 'info';
               break;
            case 'xls':
            case 'xlsx':
               $iconClass = 'ri-file-excel-line';
               $iconColor = 'success';
               break;
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
               $iconClass = 'ri-file-image-line';
               $iconColor = 'warning';
               break;
         }
      ?>
         <div class="col-lg-4 col-md-6 col-12">
            <div class="card border-0 shadow-sm h-100 document-card">
               <div class="card-body">
                  <div class="d-flex align-items-start mb-3">
                     <div class="avatar avatar-lg rounded bg-<?= $iconColor ?>-transparent text-<?= $iconColor ?> me-3">
                        <i class="<?= $iconClass ?> fs-24"></i>
                     </div>
                     <div class="flex-grow-1">
                        <h6 class="mb-1 fw-semibold"><?= htmlspecialchars($clientDocument->clientDocumentName) ?></h6>
                        <span class="badge bg-<?= $iconColor ?>-transparent text-<?= $iconColor ?> small">
                           <?= htmlspecialchars($clientDocument->documentTypeName) ?>
                        </span>
                     </div>
                  </div>

                  <?php if($clientDocument->clientDocumentDescription): ?>
                     <p class="text-muted small mb-3"><?= htmlspecialchars(substr($clientDocument->clientDocumentDescription, 0, 100)) ?><?= strlen($clientDocument->clientDocumentDescription) > 100 ? '...' : '' ?></p>
                  <?php endif; ?>

                  <div class="d-flex justify-content-between align-items-center border-top pt-3 mt-auto">
                     <small class="text-muted">
                        <i class="ri-file-line me-1"></i><?= strtoupper($fileExt) ?>
                        <?php if(isset($clientDocument->documentFileSize) && $clientDocument->documentFileSize > 0):
                           $size = $clientDocument->documentFileSize;
                           $units = ['B', 'KB', 'MB', 'GB'];
                           $i = 0;
                           while ($size >= 1024 && $i < 3) {
                              $size /= 1024;
                              $i++;
                           }
                        ?>
                           · <?= round($size, 1) . ' ' . $units[$i] ?>
                        <?php endif; ?>
                     </small>
                     <div class="btn-group">
                        <a href="<?= "{$config['DataDir']}{$clientDocument->clientDocumentFile}" ?>"
                           class="btn btn-sm btn-outline-primary"
                           title="Download Document"
                           download>
                           <i class="ri-download-2-line"></i>
                        </a>
                        <button type="button"
                           class="btn btn-sm btn-outline-info edit-client-document"
                           data-bs-toggle="modal"
                           data-bs-target="#manageClientDocuments"
                           data-client-document-id="<?= $clientDocument->clientDocumentID ?>"
                           data-client-id="<?= $clientDocument->clientID ?>"
                           data-client-document-name="<?= htmlspecialchars($clientDocument->clientDocumentName) ?>"
                           data-client-document-description="<?= htmlspecialchars($clientDocument->clientDocumentDescription) ?>"
                           data-document-type-id="<?= $clientDocument->documentTypeID ?>"
                           data-document-file-name="<?= htmlspecialchars($clientDocument->documentFileName) ?>"
                           data-document-file-path="<?= htmlspecialchars($clientDocument->documentFilePath) ?>"
                           data-client-document-file="<?= htmlspecialchars($clientDocument->clientDocumentFile) ?>"
                           title="Edit Document">
                           <i class="ri-pencil-line"></i>
                        </button>
                        <button type="button"
                           class="btn btn-sm btn-outline-danger delete-client-document"
                           data-document-id="<?= $clientDocument->clientDocumentID ?>"
                           data-document-name="<?= htmlspecialchars($clientDocument->clientDocumentName) ?>"
                           data-delete-url="<?= "{$base}php/scripts/clients/manage_client_documents.php?clientDocumentID={$clientDocument->clientDocumentID}&action=delete" ?>"
                           title="Delete Document">
                           <i class="ri-delete-bin-line"></i>
                        </button>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      <?php endforeach; ?>
   </div>
<?php else: ?>
   <!-- Empty State -->
   <div class="text-center py-5">
      <div class="empty-state-icon mb-3">
         <i class="ri-folder-open-line fs-48 text-muted"></i>
      </div>
      <h5 class="mb-2">No Documents Yet</h5>
      <p class="text-muted mb-4">Start by uploading important client documents like contracts, agreements, or reports.</p>
      <button type="button"
              class="btn btn-primary add-client-document"
              data-bs-toggle="modal"
              data-bs-target="#manageClientDocuments">
         <i class="ri-file-add-line me-1"></i>Add First Document
      </button>
   </div>
<?php endif; ?>

<!-- Document Management JavaScript -->
<script>
(function() {
   'use strict';

   // Debug: Log clientID availability
   const clientID = '<?= isset($clientDetails) ? $clientDetails->clientID : (isset($clientID) ? $clientID : "") ?>';
   console.log('Client Document Script - clientID:', clientID);

   // Initialize: Set clientID in hidden input on page load
   document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('clientDocumentsModalForm');
      if (form && clientID) {
         const clientIDInput = form.querySelector('#clientID') || form.querySelector('[name="clientID"]');
         if (clientIDInput && !clientIDInput.value) {
            clientIDInput.value = clientID;
            console.log('ClientID set in hidden input:', clientID);
         }
      }
   });

   // Handle Add Document Button (clear form)
   document.addEventListener('click', function(e) {
      const addBtn = e.target.closest('.add-client-document');
      if (addBtn) {
         const form = document.getElementById('clientDocumentsModalForm');
         if (!form) return;

         // Ensure clientID is set
         const clientIDInput = form.querySelector('#clientID') || form.querySelector('[name="clientID"]');
         if (clientIDInput && !clientIDInput.value) {
            // Try to get clientID from page context
            const clientID = '<?= isset($clientDetails) ? $clientDetails->clientID : (isset($clientID) ? $clientID : "") ?>';
            if (clientID) {
               clientIDInput.value = clientID;
            }
         }

         // Clear all form fields except clientID
         form.querySelectorAll('input, textarea, select').forEach(input => {
            if (input.name === 'clientID') {
               // Keep clientID, don't clear it
               return;
            } else if (input.type === 'file') {
               input.value = '';
               // Remove any existing document name badge
               const docBadge = input.parentNode.querySelector('.document-name');
               if (docBadge) docBadge.remove();
               input.placeholder = 'Select file';
               input.required = true;
            } else if (input.name === 'clientDocumentID') {
               input.value = ''; // Clear ID for new document
            } else if (input.type === 'checkbox' || input.type === 'radio') {
               input.checked = false;
            } else {
               input.value = '';
            }
         });

         // Hide new document type div
         const newDocTypeDiv = document.querySelector('.newDocumentTypeDiv');
         if (newDocTypeDiv) newDocTypeDiv.classList.add('d-none');
      }

      // Handle Edit Document Button
      const editBtn = e.target.closest('.edit-client-document');
      if (editBtn) {
         const form = document.getElementById('clientDocumentsModalForm');
         if (!form) return;

         const data = editBtn.dataset;
         console.log('Edit document:', data);

         // Ensure clientID is set from data or keep existing
         const clientIDInput = form.querySelector('#clientID') || form.querySelector('[name="clientID"]');
         if (clientIDInput) {
            if (data.clientId) {
               clientIDInput.value = data.clientId;
            } else if (!clientIDInput.value) {
               // Fallback to page context
               const clientID = '<?= isset($clientDetails) ? $clientDetails->clientID : (isset($clientID) ? $clientID : "") ?>';
               if (clientID) clientIDInput.value = clientID;
            }
         }

         const fieldMappings = {
            'clientDocumentID': 'clientDocumentId',
            'clientID': 'clientId',
            'clientDocumentName': 'clientDocumentName',
            'clientDocumentDescription': 'clientDocumentDescription',
            'documentTypeID': 'documentTypeId',
            'clientDocumentFile': 'clientDocumentFile',
         };

         // Populate form fields
         for (const [field, dataAttr] of Object.entries(fieldMappings)) {
            const input = form.querySelector(`[name="${field}"]`);

            if (input && input.type === 'file') {
               input.value = ''; // Clear file input
               input.required = false; // File not required for edit

               // Remove existing badge
               const existingBadge = input.parentNode.querySelector('.document-name');
               if (existingBadge) existingBadge.remove();

               // Add current file name badge
               const documentNameElement = document.createElement('span');
               documentNameElement.className = 'document-name d-block mt-2 badge rounded-pill bg-secondary-transparent w-100';
               documentNameElement.innerHTML = `<i class="ri-file-text-line me-2"></i>${data.documentFileName || data.clientDocumentName}`;
               input.parentNode.insertBefore(documentNameElement, input.nextSibling);
               input.placeholder = 'Select new file to replace current document';
            } else if (input) {
               input.value = data[dataAttr] || '';
            }
         }
      }

      // Handle Delete Document Button with SweetAlert
      const deleteBtn = e.target.closest('.delete-client-document');
      if (deleteBtn) {
         e.preventDefault();
         const documentName = deleteBtn.dataset.documentName;
         const deleteUrl = deleteBtn.dataset.deleteUrl;

         if (typeof Swal !== 'undefined') {
            Swal.fire({
               title: 'Delete Document?',
               html: `Are you sure you want to delete <strong>"${documentName}"</strong>?<br><small class="text-muted">This action cannot be undone.</small>`,
               icon: 'warning',
               showCancelButton: true,
               confirmButtonText: 'Yes, delete it',
               cancelButtonText: 'Cancel',
               reverseButtons: true,
               buttonsStyling: false,
               customClass: {
                  confirmButton: 'btn btn-danger me-2',
                  cancelButton: 'btn btn-outline-secondary'
               }
            }).then((result) => {
               if (result.isConfirmed) {
                  window.location.href = deleteUrl;
               }
            });
         } else {
            // Fallback to native confirm
            if (window.confirm(`Are you sure you want to delete "${documentName}"?`)) {
               window.location.href = deleteUrl;
            }
         }
      }
   });
})();
</script>

<style>
.document-card {
   transition: all 0.3s ease;
}

.document-card:hover {
   transform: translateY(-4px);
   box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15) !important;
}

.empty-state-icon {
   width: 80px;
   height: 80px;
   margin: 0 auto;
   background: #f8f9fa;
   border-radius: 50%;
   display: flex;
   align-items: center;
   justify-content: center;
}
</style>