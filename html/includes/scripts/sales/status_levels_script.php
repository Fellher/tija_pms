<form action="<?php echo $base ."php/scripts/sales/manage_sale.php" ?>" method="POST" id="statusSubmit" class="my-3" >
   <input type="hidden" name="salesCaseID" value="<?php echo $salesCaseDetails->salesCaseID ?>">
   <input type="hidden" name="saleStatusLevelID" id="saleStatusLevelID" value="<?php echo $salesCaseDetails->saleStatusLevelID ?>">
   <div class="btn-group col-12" role="group" aria-label="Basic radio toggle button group">
      <div class="row col-12 g-0">
         <?php
         if($statusLevels) {
            $statusLevelCount = 0;
            foreach ($statusLevels as $statusLevel) {
               $statusLevelCount++;
               $btnState = ($salesCaseDetails->closeStatus == 'won' || $salesCaseDetails->closeStatus == 'lost') ?  'btn-danger' :'btn-outline-primary';
               // Check if closeLevel property exists and equals 'Y'
               $isCloseLevel = isset($statusLevel->closeLevel) && ($statusLevel->closeLevel == 'Y' || $statusLevel->closeLevel === '1');
               if($isCloseLevel) {
                  $btnState = ($salesCaseDetails->closeStatus == 'won' || $salesCaseDetails->closeStatus == 'lost') ?  'btn-danger' :'btn-secondary';?>
                  <div class="col-sm d-grid gap-2 dropdown closeInput">
                     <input
                        type="checkbox"
                        class="btn-check w-100 rounded-0 dropdown-toggle"
                        name="saleStatus" id="btnradio<?php echo $statusLevel->saleStatusLevelID ?>"
                        value="<?php echo $statusLevel->saleStatusLevelID ?>"
                        autocomplete="off"
                        <?php echo $salesCaseDetails->saleStatusLevelID == $statusLevel->saleStatusLevelID ? 'checked' : '' ?>
                        data-bs-toggle="dropdown" />

                        <input type="hidden" class="closeStatus" name="closeStatus" id="closeStatus" value="">
                     <label class="btn <?php echo $btnState; ?> btn-lg rounded-0" for="btnradio<?php echo $statusLevel->saleStatusLevelID ?>"><?php echo $statusLevel->statusLevel ?></label>
                     <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1"style="width:300px">
                        <li><a id="order" class="dropdown-item orderStateOption " data-close-state ="won" href="">Order</a></li>
                        <li><a id="reject" class="dropdown-item orderStateOption" href="" data-close-state="lost" >rejected Proposal</a></li>
                     </ul>
                  </div>
                  <script>
                     document.addEventListener('DOMContentLoaded', function() {
                        document.querySelectorAll('.closeInput input[type="checkbox"]').forEach(function(element) {
                           element.addEventListener('click', function(event) {
                              event.stopPropagation();
                              event.preventDefault();
                              console.log('This Has been clicked' + element.value);
                              document.getElementById('saleStatusLevelID').value = element.value;
                           });
                        });

                        document.querySelectorAll('.closeInput .dropdown-item').forEach(function(element) {
                           element.addEventListener('click', function(event) {
                              // console.log('This Has been clicked' + element.value);
                              // console.log('This Has been clicked' + element.dataset.closeState);
                              document.getElementById('closeStatus').value = element.dataset.closeState;
                              const closeStatusName = element.dataset.closeState == 'won' ? 'order' : 'loss';

                              // create input for sales stage change
                              const input = document.createElement('input');
                              input.type = 'hidden';
                              input.name = 'saleStage';
                              input.value = closeStatusName;
                              input.className = 'form-control form-control-sm form-control-plaintext bg-light';
                              document.getElementById('statusSubmit').appendChild(input);

                              event.stopPropagation();
                              event.preventDefault();
                              document.getElementById('statusSubmit').submit();
                           });
                        });
                     });
                  </script>
                  <?php
                  continue;
               }?>
               <div class="col-sm d-grid gap-2">
                  <input
                     type="checkbox"
                     class="btn-check w-100 rounded-0 status"
                     name="saleStatus"
                     id="btnradio<?php echo $statusLevel->saleStatusLevelID ?>"
                     value="<?php echo $statusLevel->saleStatusLevelID ?>"
                     autocomplete="off" <?php echo $salesCaseDetails->saleStatusLevelID == $statusLevel->saleStatusLevelID ? 'checked' : '' ?>
                     data-closeLevel ="<?php echo isset($statusLevel->closeLevel) ? $statusLevel->closeLevel : 'N' ?>"
                     data-statusLevelID ="<?php echo $statusLevel->saleStatusLevelID ?>"
                     data-statusLevel ="<?php echo $statusLevel->statusLevel ?>" />
                  <label class="btn <?php echo $btnState; ?> btn-lg rounded-0 " for="btnradio<?php echo $statusLevel->saleStatusLevelID ?>">
                     <a tabindex="0"
                        class="text-dark"
                        role="button"
                        data-bs-toggle="popover"
                        data-bs-trigger="hover"
                        data-bs-placement="top"
                        title="<?php echo $statusLevel->statusLevel ?> "
                        data-bs-content="<?php echo $statusLevel->StatusLevelDescription ?>">
                        <?php echo $statusLevel->statusLevel ?>
                        <i class="ti ti-info-circle"></i>
                     </a>
                  </label>
               </div>
               <?php
            }
         } else {?>
            <div class="col-sm d-grid gap-2">
               <input type="checkbox" class="btn-check w-100 rounded-0"   name="saleStatus" id="btnradio1" value="lead" autocomplete="off" <?php echo $leadActive ?>  >
               <label class="btn <?php echo $btnState; ?> btn-lg rounded-0" for="btnradio1">Lead</label>
            </div>
            <div class="col-sm d-grid gap-2">
               <input type="checkbox" class="btn-check w-100 " name="saleStatus" id="btnradio2" value="opportunity" autocomplete="off" <?php echo $oppotunityActive ?> >
               <label class="btn <?php echo $btnState; ?> btn-lg rounded-0" for="btnradio2">Opportunity</label>
            </div>
            <div class="col-sm d-grid gap-2">
               <input type="checkbox" class="btn-check w-100 rounded-0" name="saleStatus" id="btnradio3" value="proposal" autocomplete="off" <?php echo $proposalActive ?>>
               <label class="btn <?php echo $btnState; ?> btn-lg rounded-0 " for="btnradio3">Proposal</label>
            </div>

            <div class="col-sm d-grid gap-2 dropdown">
               <input type="checkbox" class="btn-check w-100 rounded-0 dropdown-toggle" name="saleStatus"  id="btnradio4" value="closed" autocomplete="off" <?php echo $closedActive ?> data-bs-toggle="dropdown" aria-expanded="false">
               <label class="btn <?php echo $btnState; ?> btn-lg rounded-0" for="btnradio4">Closed<?php echo isset($saleDetails->closeStatus) ?  "<small class='nott font-12'>({$saleDetails->closeStatus})</small>" : '' ?></label>

               <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1"style="width:300px">
                  <li><a id="order" class="dropdown-item order" href="">Order</a></li>
                  <li><a id="reject" class="dropdown-item reject" href="" >Rejected Proposal</a></li>
               </ul>
            </div>
            <?php
         } ?>
      </div>
   </div>
</form>

<script>
   document.addEventListener('DOMContentLoaded', function() {
      const statusButtons = document.querySelectorAll('input[name="saleStatus"]');
      const statusForm = document.getElementById('statusSubmit');

      async function confirmStatusChange(label) {
         if (window.Swal && typeof Swal.fire === 'function') {
            const result = await Swal.fire({
               title: 'Change status?',
               text: `Move this sales case to "${label}"?`,
               icon: 'question',
               showCancelButton: true,
               confirmButtonColor: '#0d6efd',
               cancelButtonColor: '#6c757d',
               confirmButtonText: 'Yes, change',
               cancelButtonText: 'No, keep current'
            });
            return result.isConfirmed;
         }
         return window.confirm(`Move this sales case to "${label}"?`);
      }

      statusButtons.forEach(button => {
         button.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();

            const data = button.dataset;
            const statusLabel = data.statuslevel || 'this status';

            const isCloseLevel = data.closelevel == 'Y' || data.closelevel === '1';
            const statusLevel = data.statuslevel ? data.statuslevel.toLowerCase() : '';
            const isClosedStatus = isCloseLevel || statusLevel.includes('closed') || statusLevel.includes('close');

            const confirmed = await confirmStatusChange(statusLabel);
            if (!confirmed) return;

            // Closed status path -> open close-status modal
            if (isClosedStatus && !button.closest('.closeInput')) {
               const modal = document.getElementById('closeStatusModal');
               if (modal) {
                  const modalStatusLevelInput = document.getElementById('modalSaleStatusLevelID');
                  if (modalStatusLevelInput) {
                     modalStatusLevelInput.value = button.value;
                  }
                  document.getElementById('saleStatusLevelID').value = button.value;
                  const bsModal = new bootstrap.Modal(modal);
                  bsModal.show();
               }
               return;
            }

            // Regular status path -> submit immediately
            document.getElementById('saleStatusLevelID').value = button.value;
            statusForm.submit();
         });
      });
   });
</script>

<div class="col-12">
   <form action="<?php echo $base ."php/scripts/sales/manage_sale.php" ?>" method="POST" id="submitSale" class="mb-0" >
      <input type="hidden" name="salesCaseID" value="<?php echo $salesCaseDetails->salesCaseID ?>">
      <input type="hidden" name="entityID" value="<?php echo $entityID; ?>">
      <div class="row col-12">
         <div class="d-flex align-items-center flex-fill col-lg-3">
            <span class="avatar bd-blue-800 avatar-lg me-2 avatar-rounded">
               <i class="fa-solid fa-calculator"></i>
            </span>
            <div class="col-sm-10">
               <div class="form-group">
                  <label for="salesValue" class="nott"> Sale/Project Value</label>
                  <input type="text" name="salesCaseEstimate" id="salesCaseEstimate" class="form-control form-control-sm form-control-plaintext  <?= !$salesCaseDetails->salesCaseEstimate ? "bg-danger" : "bg-light" ?>  " value="<?php echo $salesCaseDetails->salesCaseEstimate ?>">
               </div>
            </div>
         </div>

         <div class="d-flex align-items-center flex-fill col-lg-3">
            <span class="avatar bd-blue-800 avatar-lg me-2 avatar-rounded">
               <i class="fa-solid fa-percent"></i>
            </span>
            <div class="col-10">
               <div class="form-group">
                  <label for="probability" class="nott"> Probability</label>
                  <input type="text" name="probability" id="probability" class="form-control form-control-sm form-control-plaintext  <?= !$salesCaseDetails->salesCaseEstimate ? "bg-danger" : "bg-light" ?> " value="<?php echo $salesCaseDetails->probability ?>">
               </div>
            </div>
         </div>

         <div class="d-flex align-items-center flex-fill col-lg-3">
            <span class="avatar bd-blue-800 avatar-lg me-2 avatar-rounded">
               <i class="fa-solid fa-calendar"></i>
            </span>
            <div class="col-10">
               <div class="form-group">
                  <label for="probability" class="nott"> Expected Order Date  </label>
                  <input type="text"
                  id="date"
                  name="expectedCloseDate"
                  class="form-control form-control-sm form-control-plaintext px-2
                  <?= !$salesCaseDetails->expectedCloseDate || $salesCaseDetails->expectedCloseDate == '0000-00-00' ? " bg-light-orange border-danger " : "bg-light" ?> "
                  value="<?=  $salesCaseDetails->expectedCloseDate && $salesCaseDetails->expectedCloseDate != "0000-00-00" ? $salesCaseDetails->expectedCloseDate : "";  ?>" placeholder="Expected Order Date">
               </div>
            </div>
         </div>

         <?php
         // var_dump($salesCaseDetails->expectedCloseDate); ?>
         <script>
            // check if the date input exists and set the datepicker start date to today
            document.addEventListener('DOMContentLoaded', function() {
               const dateInput = document.getElementById('date');
               if (dateInput) {
                  if(dateInput.value === "0000-00-00") {
                     // If the date input is set to "0000-00-00", make the inpit to invalid and error message to please input valid expected order date
                     dateInput.classList.add('is-invalid');
                     dateInput.setCustomValidity('Please input a valid expected order date.');
                     // If the date input is empty, set it to today's date
                     // dateInput.value = new Date().toISOString().split('T')[0]; // Set today's date in YYYY-MM-DD format
                  }
                  dateInput.addEventListener('change', function() {
                     console.log('Date input changed');
                     console.log(dateInput.value);
                  });
               } else {
                  console.warn('No date input found.');
               }
            });
         </script>

         <div class="d-flex align-items-center flex-fill col-lg-3">
            <span class="avatar bd-blue-800 avatar-lg me-2 avatar-rounded">
               <i class="fa-solid fa-user"></i>
            </span>
            <div class="col-10">
               <div class="row">
                  <div class="form-group col-md-10">
                     <label for="probability" class="nott"> Lead Source</label>
                     <select id="sourceLead" name="leadSourceID" class="form-control form-control-sm form-control-plaintext bg-light ps-2" onchange="addNewLead(this);" >
                        <?php echo
                        Form::populate_select_element_from_object($leadSource, 'leadSourceID', 'leadSourceName', $salesCaseDetails->leadSourceID, '', 'Select Lead Source') ?>
                        <option value="newSource">Add new source lead</option>
                     </select>
                     <div id="SourceLeadAdd" class="col-12 d-none">
                        <small>Add Lead Source <span id="return-btn" value="return" class="float-end btn-link"><i class="icon-select"></i></span></small>
                        <input type="text" class="form-control form-control-sm bg-light-orange form-control-plaintext px-2" name="newLeadSource" placeholder="Add new Lead source" >
                     </div>
                  </div>
                  <div class="col-md-2 px-0">
                     <label for="probability" class="nott">&nbsp;</label>
                     <button type="Submit" class="btn btn-primary  btn-sm w-100">Save</button>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </form>
</div>

<!-- ============================================================================
     CLOSE STATUS MODAL - Select Won or Lost
     ============================================================================ -->
<div class="modal fade" id="closeStatusModal" tabindex="-1" aria-labelledby="closeStatusModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="closeStatusModalLabel">
               <i class="ri-checkbox-circle-line me-2"></i>Close Sales Case
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form action="<?php echo $base ."php/scripts/sales/manage_sale.php" ?>" method="POST" id="closeStatusForm">
            <input type="hidden" name="salesCaseID" value="<?php echo $salesCaseDetails->salesCaseID ?>">
            <input type="hidden" name="saleStatusLevelID" id="modalSaleStatusLevelID" value="">
            <input type="hidden" name="closeStatus" id="modalCloseStatus" value="">
            <input type="hidden" name="entityID" value="<?php echo $entityID; ?>">

            <div class="modal-body">
               <p class="mb-4">Please select the outcome for this sales case:</p>

               <div class="d-grid gap-3">
                  <button type="button" class="btn btn-lg btn-outline-success close-status-option" data-close-status="won">
                     <i class="ri-checkbox-circle-line me-2 fs-20"></i>
                     <div class="text-start">
                        <div class="fw-semibold">Won / Order</div>
                        <small class="text-muted">This sale was successfully closed and won</small>
                     </div>
                  </button>

                  <button type="button" class="btn btn-lg btn-outline-danger close-status-option" data-close-status="lost">
                     <i class="ri-close-circle-line me-2 fs-20"></i>
                     <div class="text-start">
                        <div class="fw-semibold">Lost / Rejected</div>
                        <small class="text-muted">This sale was closed but not won</small>
                     </div>
                  </button>
               </div>
            </div>

            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
         </form>
      </div>
   </div>
</div>

<script>
   document.addEventListener('DOMContentLoaded', function() {
      // Handle close status option clicks
      const closeStatusOptions = document.querySelectorAll('.close-status-option');
      const closeStatusForm = document.getElementById('closeStatusForm');
      const modalCloseStatusInput = document.getElementById('modalCloseStatus');
      const modal = document.getElementById('closeStatusModal');

      closeStatusOptions.forEach(option => {
         option.addEventListener('click', function() {
            const closeStatus = this.dataset.closeStatus;
            const closeStatusName = closeStatus == 'won' ? 'order' : 'loss';

            // Set the close status
            if(modalCloseStatusInput) {
               modalCloseStatusInput.value = closeStatus;
            }

            // Add saleStage input
            let saleStageInput = closeStatusForm.querySelector('input[name="saleStage"]');
            if(!saleStageInput) {
               saleStageInput = document.createElement('input');
               saleStageInput.type = 'hidden';
               saleStageInput.name = 'saleStage';
               saleStageInput.value = closeStatusName;
               closeStatusForm.appendChild(saleStageInput);
            } else {
               saleStageInput.value = closeStatusName;
            }

            // Submit the form
            closeStatusForm.submit();
         });
      });

      // Update modal status level ID when modal is shown
      if(modal) {
         modal.addEventListener('show.bs.modal', function(event) {
            // The status level ID should already be set by the click handler
            console.log('Modal shown, status level ID:', document.getElementById('modalSaleStatusLevelID').value);
         });
      }
   });
</script>

