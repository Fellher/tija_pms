<?php
/**
 * Proposal Details - Attachments Tab
 * Displays and manages proposal attachments/documents
 */

$proposalAttachments = Proposal::proposal_attachments(array('proposalID'=>$proposalID), false, $DBConn);
?>

<div class="proposal-section-card">
   <div class="proposal-section-header">
      <h5 class="proposal-section-title">
         <i class="ri-attachment-2"></i>
         Proposal Attachments
         <?php if($proposalAttachments): ?>
            <span class="badge bg-primary ms-2"><?= count($proposalAttachments) ?></span>
         <?php endif; ?>
      </h5>
      <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#manageProposalAttachmentModal">
         <i class="ri-add-line me-1"></i> Add Attachment
      </button>
   </div>
   <div class="proposal-section-body">
      <?php if($proposalAttachments && count($proposalAttachments) > 0): ?>
         <div class="row g-3">
            <?php foreach($proposalAttachments as $attachment):
               // Determine file icon based on extension
               $fileExtension = strtolower(pathinfo($attachment->proposalAttachmentFile, PATHINFO_EXTENSION));
               switch($fileExtension) {
                  case 'pdf': $iconClass = 'ri-file-pdf-line text-danger'; break;
                  case 'doc': case 'docx': $iconClass = 'ri-file-word-line text-primary'; break;
                  case 'xls': case 'xlsx': case 'csv': $iconClass = 'ri-file-excel-line text-success'; break;
                  case 'ppt': case 'pptx': $iconClass = 'ri-file-ppt-line text-warning'; break;
                  case 'png': case 'jpg': case 'jpeg': case 'gif': case 'webp': $iconClass = 'ri-image-line text-info'; break;
                  case 'zip': case 'rar': case '7z': $iconClass = 'ri-file-zip-line text-secondary'; break;
                  default: $iconClass = 'ri-file-line text-muted'; break;
               }

               $proposalAttachmentFile = str_replace(" ", "%20", $attachment->proposalAttachmentFile);
               $proposalAttachmentFile = ltrim($proposalAttachmentFile, '/');
               $downloadUrl = $config['DataDir'] . $proposalAttachmentFile;
            ?>
            <div class="col-md-6 col-lg-4">
               <div class="card h-100 border shadow-sm">
                  <div class="card-body d-flex align-items-center gap-3">
                     <div class="flex-shrink-0">
                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                           <i class="<?= $iconClass ?> fs-4"></i>
                        </div>
                     </div>
                     <div class="flex-grow-1 min-width-0">
                        <h6 class="mb-1 text-truncate" title="<?= htmlspecialchars($attachment->proposalAttachmentName) ?>">
                           <?= htmlspecialchars($attachment->proposalAttachmentName) ?>
                        </h6>
                        <small class="text-muted d-block text-truncate">
                           <?= strtoupper($fileExtension) ?> file
                        </small>
                        <?php if(isset($attachment->uploadedByName)): ?>
                           <small class="text-muted">
                              <i class="ri-user-line"></i> <?= htmlspecialchars($attachment->uploadedByName) ?>
                           </small>
                        <?php endif; ?>
                     </div>
                     <div class="flex-shrink-0">
                        <a href="<?= $downloadUrl ?>"
                           target="_blank"
                           class="btn btn-sm btn-outline-primary rounded-circle"
                           title="Download / View">
                           <i class="ri-download-2-line"></i>
                        </a>
                     </div>
                  </div>
               </div>
            </div>
            <?php endforeach; ?>
         </div>
      <?php else: ?>
         <div class="text-center py-5">
            <div class="mb-3">
               <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                  <i class="ri-folder-add-line text-muted" style="font-size: 2.5rem;"></i>
               </div>
            </div>
            <h5 class="text-muted mb-2">No Attachments Yet</h5>
            <p class="text-muted mb-3">
               Upload proposal documents, supporting materials, or reference files.
            </p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manageProposalAttachmentModal">
               <i class="ri-add-line me-1"></i> Upload First Attachment
            </button>
         </div>
      <?php endif; ?>
   </div>
</div>

<!-- Attachment Types Guide -->
<div class="proposal-section-card">
   <div class="proposal-section-header">
      <h5 class="proposal-section-title">
         <i class="ri-information-line"></i>
         Supported File Types
      </h5>
   </div>
   <div class="proposal-section-body">
      <div class="row g-3">
         <div class="col-6 col-md-3">
            <div class="d-flex align-items-center gap-2">
               <i class="ri-file-pdf-line text-danger fs-5"></i>
               <span class="text-muted">PDF Documents</span>
            </div>
         </div>
         <div class="col-6 col-md-3">
            <div class="d-flex align-items-center gap-2">
               <i class="ri-file-word-line text-primary fs-5"></i>
               <span class="text-muted">Word Documents</span>
            </div>
         </div>
         <div class="col-6 col-md-3">
            <div class="d-flex align-items-center gap-2">
               <i class="ri-file-excel-line text-success fs-5"></i>
               <span class="text-muted">Excel Spreadsheets</span>
            </div>
         </div>
         <div class="col-6 col-md-3">
            <div class="d-flex align-items-center gap-2">
               <i class="ri-image-line text-info fs-5"></i>
               <span class="text-muted">Images (PNG, JPG)</span>
            </div>
         </div>
      </div>
   </div>
</div>
