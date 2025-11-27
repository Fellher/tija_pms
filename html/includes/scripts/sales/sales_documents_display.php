<?php
/**
 * Sales Documents Display Component
 * Displays and manages sales documents with category filtering
 *
 * @package    Tija CRM
 * @subpackage Sales Management
 */

// Fetch documents for this sales case
$salesDocuments = Sales::sales_documents_by_case($salesCaseID, $DBConn);

// Check user permissions
$isManagement = false;
$isFinance = false;
$canViewAllDocuments = false;

// Check if user has management or finance role
if (isset($userDetails->permissionProfileID)) {
    // This should be enhanced with actual role checking logic
    // For now, we'll check common role names or IDs
    $userRole = isset($userDetails->permissionProfileName) ? strtolower($userDetails->permissionProfileName) : '';
    $isManagement = (strpos($userRole, 'management') !== false || strpos($userRole, 'manager') !== false || strpos($userRole, 'admin') !== false);
    $isFinance = (strpos($userRole, 'finance') !== false || strpos($userRole, 'accountant') !== false);
    $canViewAllDocuments = $isManagement || $isFinance;
}

// Filter documents based on permissions
if ($salesDocuments && !$canViewAllDocuments) {
    // Regular users can only see non-confidential documents or documents they uploaded
    $filteredDocuments = array();
    foreach ($salesDocuments as $doc) {
        $isOwner = ($doc->uploadedBy == $userDetails->ID);
        $isNotConfidential = ($doc->isConfidential !== 'Y');

        if ($isOwner || $isNotConfidential) {
            $filteredDocuments[] = $doc;
        }
    }
    $salesDocuments = $filteredDocuments;
}

// Group documents by category
$documentsByCategory = array();
if ($salesDocuments) {
    foreach ($salesDocuments as $doc) {
        $category = $doc->documentCategory ?? 'other';
        if (!isset($documentsByCategory[$category])) {
            $documentsByCategory[$category] = array();
        }
        $documentsByCategory[$category][] = $doc;
    }
}

// Document category labels
$categoryLabels = array(
    'sales_agreement' => 'Sales Agreements',
    'tor' => 'Terms of Reference',
    'proposal' => 'Proposals',
    'engagement_letter' => 'Engagement Letters',
    'confidentiality_agreement' => 'Confidentiality Agreements',
    'expense_document' => 'Expense Documents',
    'correspondence' => 'Correspondence',
    'meeting_notes' => 'Meeting Notes',
    'other' => 'Other Documents'
);

// Get file icon based on extension
function getFileIcon($fileType) {
    $icons = array(
        'pdf' => 'ri-file-pdf-line text-danger',
        'doc' => 'ri-file-word-line text-primary',
        'docx' => 'ri-file-word-line text-primary',
        'xls' => 'ri-file-excel-line text-success',
        'xlsx' => 'ri-file-excel-line text-success',
        'ppt' => 'ri-file-ppt-line text-warning',
        'pptx' => 'ri-file-ppt-line text-warning',
        'jpg' => 'ri-image-line text-info',
        'jpeg' => 'ri-image-line text-info',
        'png' => 'ri-image-line text-info',
        'gif' => 'ri-image-line text-info',
        'txt' => 'ri-file-text-line text-secondary'
    );
    return $icons[strtolower($fileType)] ?? 'ri-file-line text-muted';
}

// Format file size
function formatFileSize($bytes) {
    if ($bytes == 0) return '0 Bytes';
    $k = 1024;
    $sizes = array('Bytes', 'KB', 'MB', 'GB');
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}
?>

<div class="sales-documents-container">
   <!-- Filter Tabs -->
   <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="btn-group" role="group">
         <button type="button" class="btn btn-sm btn-outline-primary active" data-filter="all">
            <i class="ri-folder-line me-1"></i>All Documents
         </button>
         <?php foreach($categoryLabels as $key => $label): ?>
            <?php if(isset($documentsByCategory[$key])): ?>
            <button type="button" class="btn btn-sm btn-outline-primary" data-filter="<?= $key ?>">
               <?= htmlspecialchars($label) ?> (<?= count($documentsByCategory[$key]) ?>)
            </button>
            <?php endif; ?>
         <?php endforeach; ?>
      </div>
      <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#manageSalesDocumentModal">
         <i class="ri-upload-line me-1"></i>Upload Document
      </button>
   </div>

   <!-- Documents List -->
   <?php if($salesDocuments && count($salesDocuments) > 0): ?>
      <?php foreach($categoryLabels as $categoryKey => $categoryLabel): ?>
         <?php if(isset($documentsByCategory[$categoryKey])): ?>
            <div class="document-category-section mb-4" data-category="<?= $categoryKey ?>">
               <h6 class="fw-semibold mb-3 text-primary border-bottom pb-2">
                  <i class="ri-folder-2-line me-2"></i><?= htmlspecialchars($categoryLabel) ?>
                  <span class="badge bg-primary-transparent ms-2"><?= count($documentsByCategory[$categoryKey]) ?></span>
               </h6>
               <div class="row g-3">
                  <?php foreach($documentsByCategory[$categoryKey] as $doc): ?>
                     <div class="col-md-6 col-lg-4">
                        <div class="card border shadow-sm h-100 document-card">
                           <div class="card-body">
                              <div class="d-flex align-items-start mb-2">
                                 <div class="flex-shrink-0">
                                    <i class="<?= getFileIcon($doc->fileType) ?> fs-24"></i>
                                 </div>
                                 <div class="flex-grow-1 ms-2">
                                    <h6 class="mb-1 fw-semibold text-truncate" title="<?= htmlspecialchars($doc->documentName) ?>">
                                       <?= htmlspecialchars($doc->documentName) ?>
                                    </h6>
                                    <small class="text-muted d-block">
                                       <?= formatFileSize($doc->fileSize ?? 0) ?>
                                       <?php if($doc->fileType): ?>
                                          · <?= strtoupper($doc->fileType) ?>
                                       <?php endif; ?>
                                    </small>
                                 </div>
                                 <?php if($doc->isConfidential === 'Y'): ?>
                                    <span class="badge bg-warning-transparent text-warning" title="Confidential">
                                       <i class="ri-lock-line"></i>
                                    </span>
                                 <?php endif; ?>
                              </div>

                              <?php if($doc->description): ?>
                                 <p class="text-muted small mb-2"><?= nl2br(htmlspecialchars(substr($doc->description, 0, 100))) ?><?= strlen($doc->description) > 100 ? '...' : '' ?></p>
                              <?php endif; ?>

                              <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                 <small class="text-muted">
                                    <i class="ri-user-line me-1"></i><?= htmlspecialchars($doc->uploadedByName ?? 'Unknown') ?><br>
                                    <i class="ri-calendar-line me-1"></i><?= Utility::date_format($doc->DateAdded) ?>
                                 </small>
                                 <div class="btn-group btn-group-sm">
                                    <a href="<?= $base . $config['DataDir'] . $doc->fileURL ?>"
                                       target="_blank"
                                       class="btn btn-outline-primary"
                                       title="Download"
                                       onclick="trackDocumentDownload(<?= $doc->documentID ?>)">
                                       <i class="ri-download-line"></i>
                                    </a>
                                    <?php if($doc->requiresApproval === 'Y'): ?>
                                       <?php if($doc->approvalStatus === 'pending'): ?>
                                          <span class="badge bg-warning-transparent text-warning" title="Pending Approval">
                                             <i class="ri-time-line"></i>
                                          </span>
                                       <?php elseif($doc->approvalStatus === 'approved'): ?>
                                          <span class="badge bg-success-transparent text-success" title="Approved">
                                             <i class="ri-checkbox-circle-line"></i>
                                          </span>
                                       <?php elseif($doc->approvalStatus === 'rejected'): ?>
                                          <span class="badge bg-danger-transparent text-danger" title="Rejected">
                                             <i class="ri-close-circle-line"></i>
                                          </span>
                                       <?php endif; ?>
                                    <?php endif; ?>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  <?php endforeach; ?>
               </div>
            </div>
         <?php endif; ?>
      <?php endforeach; ?>
   <?php else: ?>
      <div class="text-center py-5">
         <i class="ri-folder-open-line fs-48 text-muted mb-3 d-block"></i>
         <p class="text-muted mb-3">No documents uploaded yet.</p>
         <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manageSalesDocumentModal">
            <i class="ri-upload-line me-1"></i>Upload First Document
         </button>
      </div>
   <?php endif; ?>
</div>

<script>
(function() {
   'use strict';

   // Category filtering
   const filterButtons = document.querySelectorAll('[data-filter]');
   const categorySections = document.querySelectorAll('.document-category-section');

   filterButtons.forEach(button => {
      button.addEventListener('click', function() {
         const filter = this.dataset.filter;

         // Update active button
         filterButtons.forEach(btn => {
            btn.classList.remove('active');
         });
         this.classList.add('active');

         // Show/hide sections
         categorySections.forEach(section => {
            if (filter === 'all' || section.dataset.category === filter) {
               section.style.display = 'block';
            } else {
               section.style.display = 'none';
            }
         });
      });
   });

   // Track document download
   window.trackDocumentDownload = function(documentID) {
      // Optional: Track download count
      fetch('<?= $base ?>php/scripts/sales/manage_sales_document.php', {
         method: 'POST',
         headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
         },
         body: 'action=get&documentID=' + documentID
      }).catch(err => console.error('Download tracking error:', err));
   };

   // Reload documents function
   window.loadSalesDocuments = function() {
      // Reload the documents section via AJAX or page reload
      location.reload();
   };
})();
</script>

<style>
.document-card {
   transition: transform 0.2s, box-shadow 0.2s;
}

.document-card:hover {
   transform: translateY(-2px);
   box-shadow: 0 4px 8px rgba(0,0,0,0.1) !important;
}

.document-category-section {
   display: block;
}

.sales-documents-container .btn-group .btn {
   font-size: 0.875rem;
}
</style>

