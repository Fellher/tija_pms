<!-- Section Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
   <div>
      <h5 class="mb-1 fw-semibold">Contacts & Addresses</h5>
      <p class="text-muted small mb-0">Contacts are grouped by their associated addresses</p>
   </div>
   <div class="d-flex gap-2">
      <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manageClientAddress">
         <i class="ri-map-pin-add-line me-1"></i>Add Address
      </button>
      <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#manageContacts">
         <i class="ri-user-add-line me-1"></i>Add Contact
      </button>
   </div>
</div>

<?php
echo Utility::form_modal_header("manageClientAddress", "clients/manage_client_primary_contact.php", "Manage Primary Contact", array('modal-lg', 'modal-dialog-centered'), $base);
include "includes/scripts/clients/modals/manage_client_primary_contact.php";
echo Utility::form_modal_footer('Save Primary Contact', 'savePrimaryContact',  ' btn btn-success btn-sm', true);

echo Utility::form_modal_header("manageContacts", "clients/manage_client_contact.php", "Manage Contact", array('modal-lg', 'modal-dialog-centered'), $base);
include "includes/scripts/clients/modals/manage_client_contact.php";
echo Utility::form_modal_footer('Save Client Contact', 'saveClientContact',  ' btn btn-success btn-sm', true);
?>

<div class="row g-4">
   <?php
   if($addresses && is_array($addresses) && count($addresses) > 0) {
      foreach ($addresses as $addressIndex => $address) {
         $clientAddressID = $address->clientAddressID;
         $country = Data::countries(array('countryID'=>$address->countryID), true, $DBConn);
         $contacts = Client::client_contact_full(['clientID'=>$clientDetails->clientID, 'clientAddressID'=>$address->clientAddressID], false, $DBConn);

         // Determine address type badges
         $addressBadges = [];
         if($address->headquarters === 'Y') $addressBadges[] = '<span class="badge bg-danger-transparent text-danger small">HQ</span>';
         if($address->billingAddress === 'Y') $addressBadges[] = '<span class="badge bg-success-transparent text-success small">Billing</span>';
      ?>
         <!-- Address-Contact Row (Side by Side) -->
         <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
               <div class="card-body p-0">
                  <div class="row g-0">
                     <!-- LEFT: Address Panel -->
                     <div class="col-md-4 border-end">
                        <div class="p-4 bg-light-subtle h-100">
                           <div class="d-flex justify-content-between align-items-start mb-3">
                              <div class="d-flex align-items-start">
                                 <div class="avatar avatar-md rounded bg-primary-transparent text-primary me-2">
                                    <i class="ri-map-pin-line fs-20"></i>
                                 </div>
                                 <div>
                                    <h6 class="mb-1 fw-semibold">
                                       <?= $address->headquarters === 'Y' ? "Headquarters" : "Branch Office" ?>
                                    </h6>
                                    <?php if($addressBadges): ?>
                                       <div class="mb-2"><?= implode(' ', $addressBadges) ?></div>
                                    <?php endif; ?>
                                 </div>
                              </div>
                              <button type="button"
                                      class="btn btn-sm btn-link p-0 text-primary editAddress"
                                      data-bs-toggle="modal"
                                      data-bs-target="#manageClientAddress"
                                      data-address-id="<?= $address->clientAddressID ?>"
                                      data-address="<?= htmlspecialchars($address->address) ?>"
                                      data-postal-code="<?= htmlspecialchars($address->postalCode) ?>"
                                      data-city="<?= htmlspecialchars($address->city) ?>"
                                      data-country-id="<?= $address->countryID ?>"
                                      data-address-type="<?= htmlspecialchars($address->addressType) ?>"
                                      data-billing-address="<?= $address->billingAddress ?>"
                                      data-headquarters="<?= $address->headquarters ?>"
                                      data-client-address-id="<?= $address->clientAddressID ?>"
                                      title="Edit Address">
                                 <i class="ri-pencil-line"></i>
                              </button>
                           </div>

                           <div class="mb-2">
                              <small class="text-muted d-block"><i class="ri-home-line me-1"></i><?= nl2br(htmlspecialchars($address->address)) ?></small>
                           </div>
                           <div class="d-flex gap-3">
                              <small class="text-muted"><i class="ri-mail-line me-1"></i><?= htmlspecialchars($address->postalCode) ?></small>
                              <small class="text-muted"><i class="ri-building-line me-1"></i><?= htmlspecialchars($address->city) ?></small>
                           </div>
                           <?php if(isset($country->countryName)): ?>
                              <small class="text-muted d-block mt-1"><i class="ri-global-line me-1"></i><?= htmlspecialchars($country->countryName) ?></small>
                           <?php endif; ?>
                        </div>
                     </div>

                     <!-- RIGHT: Contacts Panel -->
                     <div class="col-md-8">
                        <div class="p-4">
                           <div class="d-flex justify-content-between align-items-center mb-3">
                              <h6 class="mb-0 fw-semibold text-muted">
                                 <i class="ri-user-line me-2"></i>Contacts
                                 <?php if($contacts && is_array($contacts)): ?>
                                    <span class="badge bg-primary-transparent text-primary ms-2"><?= count($contacts) ?></span>
                                 <?php endif; ?>
                              </h6>
                              <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#manageContacts">
                                 <i class="ri-user-add-line me-1"></i>Add
                              </button>
                           </div>

                           <?php if($contacts && is_array($contacts) && count($contacts) > 0): ?>
                              <!-- Expandable Contact List -->
                              <div class="contact-list">
                                 <?php foreach ($contacts as $contactIndex => $contact):
                                    $nameArr = explode(' ', $contact->contactName);
                                    $contact->firstName = $nameArr[0];
                                    $contact->lastName = isset($nameArr[1]) ? $nameArr[1] : "";
                                    $collapseId = "contact-{$addressIndex}-{$contactIndex}";
                                 ?>
                                    <div class="contact-item mb-2">
                                       <!-- Horizontal Compact Contact Card (All Details in One Line) -->
                                       <div class="contact-horizontal border rounded-3 p-2 bg-white">
                                          <div class="d-flex align-items-center gap-3">
                                             <!-- Avatar -->
                                             <div class="avatar avatar-sm rounded-circle bg-primary text-white flex-shrink-0">
                                                <?= Utility::generate_initials($contact->contactName) ?>
                                             </div>

                                             <!-- Name & Type -->
                                             <div class="contact-name-section flex-shrink-0" style="min-width: 140px;">
                                                <div class="fw-semibold small"><?= htmlspecialchars($contact->contactName) ?></div>
                                                <span class="badge bg-primary-transparent text-primary" style="font-size: 0.65rem;"><?= htmlspecialchars($contact->contactType) ?></span>
                                             </div>

                                             <!-- Vertical Separator -->
                                             <div class="vr" style="height: 30px;"></div>

                                             <!-- Title/Position -->
                                             <?php if(isset($contact->title) && $contact->title): ?>
                                                <div class="contact-title-section flex-shrink-0" style="min-width: 100px;">
                                                   <small class="text-muted d-block" style="font-size: 0.7rem;">Position</small>
                                                   <small class="fw-medium"><?= htmlspecialchars($contact->title) ?></small>
                                                </div>
                                                <div class="vr" style="height: 30px;"></div>
                                             <?php endif; ?>

                                             <!-- Email -->
                                             <?php if(isset($contact->contactEmail) && $contact->contactEmail): ?>
                                                <div class="contact-email-section flex-grow-1">
                                                   <small class="text-muted d-block" style="font-size: 0.7rem;">Email</small>
                                                   <a href="mailto:<?= $contact->contactEmail ?>" class="text-decoration-none small" onclick="event.stopPropagation();">
                                                      <i class="ri-mail-line me-1 text-primary"></i><?= htmlspecialchars($contact->contactEmail) ?>
                                                   </a>
                                                </div>
                                                <div class="vr" style="height: 30px;"></div>
                                             <?php endif; ?>

                                             <!-- Phone -->
                                             <?php if(isset($contact->contactPhone) && $contact->contactPhone): ?>
                                                <div class="contact-phone-section flex-shrink-0" style="min-width: 110px;">
                                                   <small class="text-muted d-block" style="font-size: 0.7rem;">Phone</small>
                                                   <a href="tel:<?= $contact->contactPhone ?>" class="text-decoration-none small" onclick="event.stopPropagation();">
                                                      <i class="ri-phone-line me-1 text-success"></i><?= htmlspecialchars($contact->contactPhone) ?>
                                                   </a>
                                                </div>
                                                <div class="vr" style="height: 30px;"></div>
                                             <?php endif; ?>

                                             <!-- Edit Button -->
                                             <button type="button"
                                                     class="btn btn-sm btn-outline-primary editContact flex-shrink-0"
                                                     data-bs-toggle="modal"
                                                     data-bs-target="#manageContacts"
                                                     data-contactID="<?= $contact->clientContactID ?>"
                                                     data-clientid="<?= $contact->clientID ?>"
                                                     data-clientcontactid="<?= $contact->clientContactID ?>"
                                                     data-userid="<?= $contact->userID ?>"
                                                     data-firstname="<?= htmlspecialchars($contact->firstName) ?>"
                                                     data-lastname="<?= htmlspecialchars($contact->lastName) ?>"
                                                     data-title="<?= htmlspecialchars($contact->title) ?>"
                                                     data-salutationid="<?= $contact->salutationID ?>"
                                                     data-email="<?= htmlspecialchars($contact->contactEmail) ?>"
                                                     data-telephone="<?= htmlspecialchars($contact->contactPhone) ?>"
                                                     data-clientaddressid="<?= $contact->clientAddressID ?>"
                                                     data-contacttypeid="<?= $contact->contactTypeID ?>"
                                                     title="Edit Contact"
                                                     onclick="event.stopPropagation();">
                                                <i class="ri-pencil-line"></i>
                                             </button>
                                          </div>
                                       </div>
                                    </div>
                                 <?php endforeach; ?>
                              </div>
                           <?php else: ?>
                              <div class="text-center py-3 bg-light rounded-3">
                                 <i class="ri-user-line fs-32 text-muted mb-2 d-block"></i>
                                 <p class="text-muted small mb-2">No contacts at this address</p>
                                 <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#manageContacts">
                                    <i class="ri-user-add-line me-1"></i>Add Contact
                                 </button>
                              </div>
                           <?php endif; ?>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      <?php
      }
   } else {
      // No addresses at all
      ?>
      <div class="col-12">
         <div class="text-center py-5">
            <div class="empty-state-icon mb-3">
               <i class="ri-map-pin-line fs-48 text-muted"></i>
            </div>
            <h5 class="mb-2">No Addresses Found</h5>
            <p class="text-muted mb-4">Add an address to start organizing contacts by location.</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#manageClientAddress">
               <i class="ri-map-pin-add-line me-1"></i>Add First Address
            </button>
         </div>
      </div>
      <?php
   }

   // Contacts Without Address Section
   $otherContacts = Client::client_contact_full(array('clientID'=>$clientDetails->clientID), false, $DBConn);
   $otherContactsFiltered = array();

   if($otherContacts && is_array($otherContacts) && count($otherContacts) > 0) {
      // Filter contacts that don't have an address
      $otherContactsFiltered = array_filter($otherContacts, function($contact) {
          return $contact->clientAddressID === null || $contact->clientAddressID === '';
      });
   }

   if($otherContactsFiltered && count($otherContactsFiltered) > 0): ?>
      <div class="col-12 mb-4">
         <div class="card border-0 shadow-sm border-start border-warning border-4">
            <div class="card-body p-4">
               <div class="d-flex justify-content-between align-items-center mb-3">
                  <div class="d-flex align-items-center">
                     <div class="avatar avatar-md rounded bg-warning-transparent text-warning me-3">
                        <i class="ri-user-line fs-20"></i>
                     </div>
                     <div>
                        <h6 class="mb-0 fw-semibold">
                           Contacts Without Address
                           <span class="badge bg-warning-transparent text-warning ms-2"><?= count($otherContactsFiltered) ?></span>
                        </h6>
                        <small class="text-muted">Not associated with any location</small>
                     </div>
                  </div>
                  <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#manageContacts">
                     <i class="ri-user-add-line me-1"></i>Add
                  </button>
               </div>

               <!-- Horizontal Contact List -->
               <div class="contact-list">
                  <?php foreach ($otherContactsFiltered as $otherIndex => $contact):
                     $nameArr = explode(' ', $contact->contactName);
                     $contact->firstName = $nameArr[0];
                     $contact->lastName = isset($nameArr[1]) ? $nameArr[1] : "";
                  ?>
                     <div class="contact-item mb-2">
                        <!-- Horizontal Compact Contact Card -->
                        <div class="contact-horizontal border rounded-3 p-2 bg-white border-warning">
                           <div class="d-flex align-items-center gap-3">
                              <!-- Avatar (Warning color for unassigned) -->
                              <div class="avatar avatar-sm rounded-circle bg-warning text-white flex-shrink-0">
                                 <?= Utility::generate_initials($contact->contactName) ?>
                              </div>

                              <!-- Name & Type -->
                              <div class="contact-name-section flex-shrink-0" style="min-width: 140px;">
                                 <div class="fw-semibold small"><?= htmlspecialchars($contact->contactName) ?></div>
                                 <span class="badge bg-warning-transparent text-warning" style="font-size: 0.65rem;"><?= htmlspecialchars($contact->contactType) ?></span>
                              </div>

                              <!-- Vertical Separator -->
                              <div class="vr" style="height: 30px;"></div>

                              <!-- Title/Position -->
                              <?php if(isset($contact->title) && $contact->title): ?>
                                 <div class="contact-title-section flex-shrink-0" style="min-width: 100px;">
                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Position</small>
                                    <small class="fw-medium"><?= htmlspecialchars($contact->title) ?></small>
                                 </div>
                                 <div class="vr" style="height: 30px;"></div>
                              <?php endif; ?>

                              <!-- Email -->
                              <?php if(isset($contact->contactEmail) && $contact->contactEmail): ?>
                                 <div class="contact-email-section flex-grow-1">
                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Email</small>
                                    <a href="mailto:<?= $contact->contactEmail ?>" class="text-decoration-none small" onclick="event.stopPropagation();">
                                       <i class="ri-mail-line me-1 text-primary"></i><?= htmlspecialchars($contact->contactEmail) ?>
                                    </a>
                                 </div>
                                 <div class="vr" style="height: 30px;"></div>
                              <?php endif; ?>

                              <!-- Phone -->
                              <?php if(isset($contact->contactPhone) && $contact->contactPhone): ?>
                                 <div class="contact-phone-section flex-shrink-0" style="min-width: 110px;">
                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Phone</small>
                                    <a href="tel:<?= $contact->contactPhone ?>" class="text-decoration-none small" onclick="event.stopPropagation();">
                                       <i class="ri-phone-line me-1 text-success"></i><?= htmlspecialchars($contact->contactPhone) ?>
                                    </a>
                                 </div>
                                 <div class="vr" style="height: 30px;"></div>
                              <?php endif; ?>

                              <!-- Edit Button -->
                              <button type="button"
                                      class="btn btn-sm btn-outline-warning editContact flex-shrink-0"
                                      data-bs-toggle="modal"
                                      data-bs-target="#manageContacts"
                                      data-contactID="<?= $contact->clientContactID ?>"
                                      data-clientid="<?= $contact->clientID ?>"
                                      data-clientcontactid="<?= $contact->clientContactID ?>"
                                      data-userid="<?= $contact->userID ?>"
                                      data-firstname="<?= htmlspecialchars($contact->firstName) ?>"
                                      data-lastname="<?= htmlspecialchars($contact->lastName) ?>"
                                      data-title="<?= htmlspecialchars($contact->title) ?>"
                                      data-salutationid="<?= $contact->salutationID ?>"
                                      data-email="<?= htmlspecialchars($contact->contactEmail) ?>"
                                      data-telephone="<?= htmlspecialchars($contact->contactPhone) ?>"
                                      data-clientaddressid="<?= $contact->clientAddressID ?>"
                                      data-contacttypeid="<?= $contact->contactTypeID ?>"
                                      title="Edit Contact"
                                      onclick="event.stopPropagation();">
                                 <i class="ri-pencil-line"></i>
                              </button>
                           </div>
                        </div>
                     </div>
                  <?php endforeach; ?>
               </div>
            </div>
         </div>
      </div>
   <?php endif; ?>
</div>

<!-- Address-Contact Horizontal Layout Styles -->
<style>
/* Horizontal contact card */
.contact-horizontal {
   transition: all 0.2s ease;
   background: white;
}

.contact-horizontal:hover {
   background: #f8f9fa;
   box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
   transform: translateX(2px);
}

/* Contact list spacing */
.contact-list {
   max-height: 600px;
   overflow-y: auto;
}

.contact-list::-webkit-scrollbar {
   width: 6px;
}

.contact-list::-webkit-scrollbar-track {
   background: #f1f1f1;
   border-radius: 10px;
}

.contact-list::-webkit-scrollbar-thumb {
   background: #888;
   border-radius: 10px;
}

.contact-list::-webkit-scrollbar-thumb:hover {
   background: #555;
}

/* Avatar sizing */
.avatar-sm {
   width: 36px;
   height: 36px;
   font-size: 0.75rem;
}

/* Vertical separators */
.vr {
   opacity: 0.2;
}

/* Empty state */
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

/* Responsive: Stack horizontally on smaller screens */
@media (max-width: 991.98px) {
   .contact-horizontal .d-flex.gap-3 {
      gap: 0.5rem !important;
   }

   .contact-name-section,
   .contact-title-section,
   .contact-email-section,
   .contact-phone-section {
      min-width: auto !important;
   }

   .contact-email-section {
      max-width: 150px;
      overflow: hidden;
      text-overflow: ellipsis;
   }
}

@media (max-width: 767.98px) {
   /* Stack vertically on mobile */
   .contact-horizontal .d-flex {
      flex-wrap: wrap;
   }

   .vr {
      display: none;
   }

   .contact-list {
      max-height: 400px;
   }

   .col-md-4,
   .col-md-8 {
      border: none !important;
   }
}
</style>

<!-- Contact & Address Management JavaScript -->
<script>
(function() {
   'use strict';

   // Get clientID from PHP context
   const clientID = '<?= isset($clientDetails) ? $clientDetails->clientID : (isset($clientID) ? $clientID : "") ?>';
   const orgDataID = '<?= isset($clientDetails) ? $clientDetails->orgDataID : (isset($orgDataID) ? $orgDataID : "") ?>';
   const entityID = '<?= isset($clientDetails) ? $clientDetails->entityID : (isset($entityID) ? $entityID : "") ?>';

   console.log('Contacts & Addresses Script - clientID:', clientID, 'orgDataID:', orgDataID, 'entityID:', entityID);

   document.addEventListener('DOMContentLoaded', function() {

      // Initialize clientID in both forms on page load
      const addressForm = document.getElementById('primaryContactForm');
      const contactForm = document.getElementById('editContactPersonForm');

      if (addressForm && clientID) {
         const clientIDInput = addressForm.querySelector('#clientID') || addressForm.querySelector('[name="clientID"]');
         const orgDataIDInput = addressForm.querySelector('#orgDataID') || addressForm.querySelector('[name="orgDataID"]');
         const entityIDInput = addressForm.querySelector('#entityID') || addressForm.querySelector('[name="entityID"]');

         if (clientIDInput && !clientIDInput.value) {
            clientIDInput.value = clientID;
            console.log('Address Form - clientID set:', clientID);
         }
         if (orgDataIDInput && !orgDataIDInput.value && orgDataID) {
            orgDataIDInput.value = orgDataID;
         }
         if (entityIDInput && !entityIDInput.value && entityID) {
            entityIDInput.value = entityID;
         }
      }

      if (contactForm && clientID) {
         const clientIDInput = contactForm.querySelector('#clientID') || contactForm.querySelector('[name="clientID"]');
         if (clientIDInput && !clientIDInput.value) {
            clientIDInput.value = clientID;
            console.log('Contact Form - clientID set:', clientID);
         }
      }
      // Handle modal open events to ensure clientID is set
      const addressModal = document.getElementById('manageClientAddress');
      const contactModal = document.getElementById('manageContacts');

      if (addressModal) {
         addressModal.addEventListener('show.bs.modal', function() {
            const form = document.getElementById('primaryContactForm');
            if (form && clientID) {
               const clientIDInput = form.querySelector('#clientID') || form.querySelector('[name="clientID"]');
               if (clientIDInput && !clientIDInput.value) {
                  clientIDInput.value = clientID;
                  console.log('Address Modal opened - clientID ensured:', clientID);
               }
            }
         });
      }

      if (contactModal) {
         contactModal.addEventListener('show.bs.modal', function() {
            const form = document.getElementById('editContactPersonForm');
            if (form && clientID) {
               const clientIDInput = form.querySelector('#clientID') || form.querySelector('[name="clientID"]');
               if (clientIDInput && !clientIDInput.value) {
                  clientIDInput.value = clientID;
                  console.log('Contact Modal opened - clientID ensured:', clientID);
               }
            }
         });
      }

      // Handle Edit Address Button
      document.querySelectorAll('.editAddress').forEach(button => {
         button.addEventListener('click', function() {
            const form = document.getElementById('primaryContactForm');
            if (!form) return;

            // Ensure clientID is set
            const clientIDInput = form.querySelector('#clientID') || form.querySelector('[name="clientID"]');
            if (clientIDInput && clientID) {
               clientIDInput.value = clientID;
            }

            // Get all data attributes from the button
            const data = this.dataset;
            // Map form fields to their corresponding data attributes
            const fieldMappings = {
                  'address': 'address',
                  'addressID': 'addressId',
                  'postalCode': 'postalCode',
                  'city': 'city',
                  'countryID': 'countryId',
                  'addressType': 'addressType',
                  'billingAddress': 'billingAddress',
                  'headquarters': 'headquarters',
                  'address': 'address',

                  'clientAddressID': 'clientAddressId',

                  'headquarters': 'headquarters'
            };

            console.log(data);

            // Fill regular form inputs
            for (const [fieldName, dataAttribute] of Object.entries(fieldMappings)) {
                  const input = form.querySelector(`[name="${fieldName}"]`);
                console.log(input);
                  if (input) {
                     input.value = data[dataAttribute] || '';
                  }
            }

            tinymce.init({
               selector: '#address'
            });

            // Handle tinyMCE editor
            const editor = tinymce.get('address'); // Make sure 'entityDescription' matches your textarea's ID
            console.log(data.address);
            if (editor) {
                  // Wait for a brief moment to ensure tinyMCE is fully initialized
                  setTimeout(() => {
                  // console.log(editor);
                     editor.setContent(data.address || '');
                  }, 100);
            }

            // If you have select elements that need special handling
            // (like setting selected options), handle them here
            const selects = ['countryID'];
            selects.forEach(selectName => {
                  const select = form.querySelector(`[name="${selectName}"]`);
                  if (select && data[fieldMappings[selectName]]) {
                     select.value = data[fieldMappings[selectName]];
                  }
            });

            // If you have Checkboxes and radio elements that need special handling
            // (like setting checked checkboxes and radio ), handle them here
            const checkboxes = ['billingAddress', 'headquarters', 'addressType'];
            checkboxes.forEach(checkboxName => {
                  const checkbox = form.querySelector(`[name="${checkboxName}"]`);

                  if (checkbox ) {
                     console.log(checkbox.value);

                     if(data[checkboxName] === 'Y' ) {
                        checkbox.checked = true;
                     }

                     if( data[checkboxName] == checkbox.value) {
                        checkbox.checked = true;
                     }
                  }
                  else {
                     console.warn(`Checkbox with name ${checkboxName} not found in form.`);
                  }
            });
         });


      });

      document.querySelectorAll('.editContact').forEach(button => {
         button.addEventListener('click', function() {
            const form = document.getElementById('editContactPersonForm');
            if (!form) return;

            // Ensure clientID is set
            const clientIDInput = form.querySelector('#clientID') || form.querySelector('[name="clientID"]');
            if (clientIDInput) {
               if (this.dataset.clientid) {
                  clientIDInput.value = this.dataset.clientid;
               } else if (clientID) {
                  clientIDInput.value = clientID;
               }
            }

            // Get all data attributes from the button
            const data = this.dataset;
            // Map form fields to their corresponding data attributes
            const fieldMappings = {
                  'clientContactID': 'clientcontactid',
                  'userID': 'userid',
                  'clientID': 'clientid',
                  'firstName': 'firstname',
                  'lastName': 'lastname',
                  'title': 'title',
                  'email': 'email',
                  'telephone': 'telephone',
                  'clientAddressID': 'clientaddressid',
                  'contactTypeID': 'contacttypeid',
                  'salutationID': 'salutationid'
            };

            console.log(`Data from button: ${JSON.stringify(data)}`);
            console.log(data);
            // Fill regular form inputs
            for (const [fieldName, dataAttribute] of Object.entries(fieldMappings)) {
                  const input = form.querySelector(`[name="${fieldName}"]`);
               //  console.log(input);
                  if (input) {
                     input.value = data[dataAttribute] || '';
                  }
            }
         });
      });

      // Handle Add Address button (clear form but keep clientID)
      document.addEventListener('click', function(e) {
         const addAddressBtn = e.target.closest('[data-bs-target="#manageClientAddress"]');
         if (addAddressBtn && !addAddressBtn.classList.contains('editAddress')) {
            // This is an Add button, not Edit
            setTimeout(function() {
               const form = document.getElementById('primaryContactForm');
               if (form) {
                  // Clear all fields except clientID, orgDataID, entityID
                  form.querySelectorAll('input, textarea, select').forEach(input => {
                     if (['clientID', 'orgDataID', 'entityID'].includes(input.name)) {
                        // Keep these values
                        if (input.name === 'clientID' && !input.value && clientID) {
                           input.value = clientID;
                        }
                        if (input.name === 'orgDataID' && !input.value && orgDataID) {
                           input.value = orgDataID;
                        }
                        if (input.name === 'entityID' && !input.value && entityID) {
                           input.value = entityID;
                        }
                     } else if (input.name === 'clientAddressID') {
                        input.value = ''; // Clear for new address
                     } else if (input.type === 'checkbox' || input.type === 'radio') {
                        input.checked = false;
                     } else {
                        input.value = '';
                     }
                  });
                  console.log('Add Address - Form cleared, clientID preserved:', clientID);
               }
            }, 100);
         }

         // Handle Add Contact button (clear form but keep clientID)
         const addContactBtn = e.target.closest('[data-bs-target="#manageContacts"]');
         if (addContactBtn && !addContactBtn.classList.contains('editContact')) {
            // This is an Add button, not Edit
            setTimeout(function() {
               const form = document.getElementById('editContactPersonForm');
               if (form) {
                  // Clear all fields except clientID
                  form.querySelectorAll('input, select').forEach(input => {
                     if (input.name === 'clientID') {
                        // Keep clientID
                        if (!input.value && clientID) {
                           input.value = clientID;
                        }
                     } else if (input.name === 'userID') {
                        // Keep userID
                        return;
                     } else if (input.name === 'clientContactID') {
                        input.value = ''; // Clear for new contact
                     } else if (input.type === 'checkbox' || input.type === 'radio') {
                        input.checked = false;
                     } else {
                        input.value = '';
                     }
                  });
                  console.log('Add Contact - Form cleared, clientID preserved:', clientID);
               }
            }, 100);
         }
      });

      // Handle contact expand/collapse with accordion behavior
      document.addEventListener('click', function(e) {
         const collapseBtn = e.target.closest('.contact-collapsed');
         if (collapseBtn) {
            // Get the target collapse element
            const target = collapseBtn.getAttribute('data-bs-target');
            const targetElement = document.querySelector(target);

            if (targetElement) {
               const isExpanded = collapseBtn.getAttribute('aria-expanded') === 'true';

               // Close all other expanded contacts in the same address group (accordion behavior)
               const parentCard = collapseBtn.closest('.card-body');
               if (parentCard && !isExpanded) {
                  parentCard.querySelectorAll('.contact-collapsed[aria-expanded="true"]').forEach(expanded => {
                     if (expanded !== collapseBtn) {
                        const expandedTarget = expanded.getAttribute('data-bs-target');
                        const expandedElement = document.querySelector(expandedTarget);
                        if (expandedElement) {
                           const bsCollapse = bootstrap.Collapse.getInstance(expandedElement);
                           if (bsCollapse) {
                              bsCollapse.hide();
                           }
                        }
                     }
                  });
               }
            }
         }
      });

      // Add tooltips
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
      tooltipTriggerList.map(function (tooltipTriggerEl) {
         return new bootstrap.Tooltip(tooltipTriggerEl);
      });

      console.log('Contacts & Addresses - Side-by-side expandable view initialized');
   });
})();
</script>