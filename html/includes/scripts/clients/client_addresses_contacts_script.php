<?php
echo Utility::form_modal_header("manageClientAddress", "clients/manage_client_primary_contact.php", "Manage Primary Contact", array('modal-lg', 'modal-dialog-centered'), $base); 
include "includes/scripts/clients/modals/manage_client_primary_contact.php";
echo Utility::form_modal_footer('Save Primary Contact', 'savePrimaryContact',  ' btn btn-success btn-sm', true);

echo Utility::form_modal_header("manageContacts", "clients/manage_client_contact.php", "Manage Contact", array('modal-lg', 'modal-dialog-centered'), $base); 
include "includes/scripts/clients/modals/manage_client_contact.php";
echo Utility::form_modal_footer('Save Client Contact', 'saveClientContact',  ' btn btn-success btn-sm', true);
?>
<div class="card-body">
   <div class="row">
      <div class="col-lg-4"> 
         <h4 class="t300 font-16 mb-3  bg-light d-flex justify-content-between align-items-center">
            <span class="text-primary"> Address</span> 
            <a 
               href="#manageClientAddress" 
               data-bs-toggle="modal" 
               role="button" 
               aria-expanded="false" 
               aria-controls="addAddress"
               class= "btn btn-primary btn-sm rounded-circle float-end"
               >
                  <i class="ri-add-line"></i>
               </a>
         </h4>
      </div>
      <div class="col-lg-8">
         <h4 class="t300 font-16 mb-3  bg-light d-flex justify-content-between align-items-center">
            <span class="text-primary"> Contacts</span>
            <a 
               data-bs-toggle="modal" 
               data-bs-target="#manageContacts" 
               role="button" 
               aria-expanded="false" 
               aria-controls="manageContacts" 
               class="btn  btn-icon rounded-pill btn-primary-light "
            >
               <i class="ti ti-user-plus"></i>
            </a>
         </h4>
      </div>      
      <?php
      if($addresses) {
         foreach ($addresses as $address) {
            // var_dump($address);
            $clientAddressID = $address->clientAddressID;
            $country = Data::countries(array('countryID'=>$address->countryID), true, $DBConn);   
            $contacts = Client::client_contact_full(['clientID'=>$clientDetails->clientID, 'clientAddressID'=>$address->clientAddressID] , false, $DBConn);?>
            <div class="row mb-3 border-bottom pb-3 border-3">
               <div class="col-lg-4">
                  <div class="custom-border rounded p-3 mb-3 shadow">
                     <div class="row">
                        <div class="col-2">                                    
                           <i class="ri-map-pin-line ri-4x"></i>
                        </div>
                        <div class="col-10">
                           <div class="d-flex justify-content-between bg-light-blue pb-0 pt-2 px-2 mb-3"> 
                              <h4 class="t300 font-16 mb-0 ">
                              <?= $address->headquarters === 'Y'? "Headquarters" : "Branch Office" ?>   
                              </h4>
                              <a href="#manageClientAddress" 
                                 data-bs-toggle="modal" 
                                 role="button" 
                                 aria-expanded="false" 
                                 aria-controls="editPrimaryAddress"
                                 data-address-id="<?= $address->clientAddressID ?>"
                                 data-address="<?= $address->address ?>"
                                 data-postal-code="<?= $address->postalCode ?>"
                                 data-city="<?= $address->city ?>"
                                 data-country-id="<?= $address->countryID ?>"
                                 data-address-type="<?= $address->addressType ?>"
                                 data-billing-address="<?= $address->billingAddress ?>"
                                 data-headquarters="<?= $address->headquarters ?>" 
                                 data-client-address-id="<?= $address->clientAddressID ?>"
                                 class="editAddress"
                              >
                                 <i class="ri-pencil-line"></i>
                              </a>
                           </div>
                           <div class="row">
                              <span class="col-sm-12 t400">  <?= Utility::clean_string($address->address) ?></span>
                              <span class="col-sm-4">Post code: </span>
                              <span class="col-sm-8"> <?= $address->postalCode ?></span>
                              <span class="col-sm-4">City: </span>
                              <span class="col-sm-8"><?= $address->city ?></span>
                              <span class="col-sm-4">Country: </span> 
                              <span class="col-sm-8"><?= isset($country->countryName) && $country->countryName ? $country->countryName : "" ?></span>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="col-lg-8">
                  <div class="row">
                     <?php 
                     // var_dump($contacts);
                     // var_dump($accountOwner);
                     if($contacts){
                        foreach ($contacts as $key => $contact) {
                           $nameArr = explode(' ', $contact->contactName);
                           $contact->firstName = $nameArr[0];
                           $contact->lastName = isset($nameArr[1]) ? $nameArr[1] : "";   ?>
                           <div class="col-md-4">                      
                              <span class="text-primary font-14 d-block bg-light px-2 py-1"> <?= $contact->contactType ?>  
                              <a class='float-end editContact' 
                              href="#manageContacts" 
                              data-bs-toggle="modal" 
                              role="button" 
                              aria-expanded="false" 
                              aria-controls="manageContacts"
                              data-contactID="<?= $contact->clientContactID ?>"
                              data-clientid="<?= $contact->clientID ?>" 
                              data-clientcontactid="<?= $contact->clientContactID ?>"
                              data-userid="<?= $contact->userID ?>"
                              data-firstname="<?= $contact->firstName ?>"
                              data-lastname="<?= $contact->lastName ?>"
                              data-title="<?= $contact->title ?>"
                              data-salutationid="<?= $contact->salutationID ?>"
                              data-email="<?= $contact->contactEmail ?>"
                              data-telephone="<?= $contact->contactPhone ?>"                   
                              data-clientaddressid="<?= $contact->clientAddressID ?>"
                              data-contacttypeid="<?= $contact->contactTypeID ?>"
                              data-salutationid="<?= $contact->salutationID ?>"
                              >
                                 <i class="ri-pencil-line"></i>
                              </a>                  
                              </span>
                              <div class="d-flex align-items-start ">         
                                 <div class="rounded-circle d-flex justify-content-center align-items-center  " style="width: 30px; height: 30px; background-color: #007bff;">
                                    <span class="text-white">
                                       <?php                                      
                                       $initials =  (isset($contact->contactName) && $contact->contactName) ? Utility::generate_initials($contact->contactName) :  "CP";                                      
                                       echo $initials; ?>
                                    </span>
                                 </div>
                                 <div class="ms-2">                                 
                                    <?= $contact->contactName ?> <br />
                                    <span class="d-block" > <?= $contact->title ?> </span>
                                    <?= $contact->contactEmail ?> <br />
                                    <?= $contact->contactPhone ?><br />                                 
                                 </div>
                              </div>
                           </div>
                           
                           <?php
                        }
                     }?>
                  </div>
               </div>
            </div>                                             
            <?php
         }
      }
      $otherContacts = Client::client_contact_full(array('clientID'=>$clientDetails->clientID), false, $DBConn);
      // var_dump($otherContacts);
      $otherContactsFiltered = array();
      // Check if $otherContacts is not empty and is an array
      if($otherContacts && count($otherContacts) > 0) {
         // Filter out contacts that have a clientAddressID
         // This will leave only those contacts that do not have an address associated with them
         $otherContactsFiltered = array_filter($otherContacts, function($contact) {
             return $contact->clientAddressID === null;
         });
      }

      // var_dump($otherContacts);
      if($otherContactsFiltered){?>
         <div class="row mb-3 border-bottom pb-3 border-3">
            <div class="col-lg-4">
               <div class="custom-border rounded p-3 mb-3 shadow">
                  <div class="row">
                     <div class="col-2">                                    
                        <i class="ri-user-line ri-4x"></i>
                     </div>
                     <div class="col-10">
                        <div class="d-flex justify-content-between bg-light-blue pb-0 pt-2 px-2 mb-3"> 
                           <h4 class="t300 font-16 mb-0 ">Other Contacts</h4>
                        </div>
                        <?php
                        foreach ($otherContacts as $contact) {
                           $nameArr = explode(' ', $contact->contactName);
                           $contact->firstName = $nameArr[0];
                           $contact->lastName = isset($nameArr[1]) ? $nameArr[1] : "";   ?>
                           <span class="text-primary font-14 d-block bg-light px-2 py-1"> <?= $contact->contactType ?>  
                              <a class='float-end editContact' 
                                 href="#manageContacts" 
                                 data-bs-toggle="modal" 
                                 role="button" 
                                 aria-expanded="false" 
                                 aria-controls="manageContacts"
                                 data-contactID="<?= $contact->clientContactID ?>"
                                 data-clientid="<?= $contact->clientID ?>" 
                                 data-clientcontactid="<?= $contact->clientContactID ?>"
                                 data-userid="<?= $contact->userID ?>"
                                 data-firstname="<?= $contact->firstName ?>"
                                 data-lastname="<?= $contact->lastName ?>"
                                 data-title="<?= $contact->title ?>"
                                 data-salutationid="<?= $contact->salutationID ?>"
                                 data-email="<?= $contact->contactEmail ?>"
                                 data-telephone="<?= $contact->contactPhone ?>"                   
                                 data-clientaddressid="<?= $contact->clientAddressID ?>"
                                 data-contacttypeid="<?= $contact->contactTypeID ?>"
                                 data-salutationid="<?= $contact->salutationID ?>"
                              >
                                 <i class="ri-pencil-line"></i>
                              </a>                  
                           </span>
                           <div class="d-flex align-items-start ">         
                              <div class="rounded-circle d-flex justify-content-center align-items-center  " style="width: 30px; height: 30px; background-color: #007bff;">
                                 <span class="text-white">
                                    <?php                                      
                                    $initials =  (isset($contact->contactName) && $contact->contactName) ? Utility::generate_initials($contact->contactName) :  "CP";                                      
                                    echo $initials; ?>
                                 </span>
                              </div>
                              <div class="ms-2">                                 
                                 <?= $contact->contactName ?> <br />
                                 <span class="d-block" > <?= $contact->title ?> </span>
                                 <?= $contact->contactEmail ?> <br />
                                 <?= $contact->contactPhone ?><br />                                 
                              </div>
                           </div>
                          
                        <?php
                        }
                        ?>
                     </div>
                  </div>
               </div>
            </div>
         </div>



      <?php

      }

      ?>
   </div>
</div>

<script>
   // check that the dom is loaded
   document.addEventListener("DOMContentLoaded", function() {

      
    document.querySelectorAll('.editAddress').forEach(button => {
         button.addEventListener('click', function() {
            const form = document.getElementById('primaryContactForm');
            if (!form) return;
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
     
   });
</script>