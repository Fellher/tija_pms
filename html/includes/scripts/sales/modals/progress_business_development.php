
<div class="col-12 businessDevProgressForm">
   <div class="col-12 mb-3 d-none">
      <input type="hidden" name="s" id="s" value="<?= $s ?>" class="form-control-plaintext border-bottom bg-light-blue">
      <input type="hidden" name="ss" id="ss" value="<?= $ss ?>" class="form-control-plaintext border-bottom bg-light-blue">
      <input type="hidden" name="p" id="p" value="<?= $p ?>" class="form-control-plaintext border-bottom bg-light-blue">
      <label for="">salesCaseID</label>
      <input type="text" name="salesCaseID" id="salesCaseID" value="" class="form-control-plaintext border-bottom bg-light-blue">
      <label for="">clientID</label>
      <input type="text" name="clientID" id="clientID" value="" class="form-control-plaintext border-bottom bg-light-blue">
      <label for="">businessUnitID</label>
      <input type="text" name="businessUnitID" id="businessUnitID" value="" class="form-control-plaintext border-bottom bg-light-blue">
      <label for="">salesPersonID</label>
      <input type="text" name="salesPersonID" id="salesPersonID" value="<?php echo $userDetails->ID; ?>" class="form-control-plaintext border-bottom bg-light-blue">
      <label for="">salesProgressID</label>
      <input type="text" name="salesProgressID" id="salesProgressID " value="" class="form-control-plaintext border-bottom bg-light-blue">      
      <label for="">entityID</label>
      <input type="text" name="entityID" id="entityID" value="" class="form-control-plaintext border-bottom bg-light-blue">
      <label for="">orgDataID</label>
      <input type="text" name="orgDataID" id="orgDataID" value="" class="form-control-plaintext border-bottom bg-light-blue">         
   </div>
   <div class="row d-lg-flex justify-content-lg-between align-items-lg-stretch " >

      <?php 
      if($opportunityStatusLevels){
         $i= 0;
         // Loop through each status level and display the radio buttons
         foreach($opportunityStatusLevels as $opKey => $statusLevel){         
            $i++;
            if($i >2 ) continue;?>
            <div class="col-sm-12 col-lg-6 mb-3 d-flex justify-content-center align-items-center align-items-stretch">         
               <div class="rounded-3 py-2 d-flex justify-content-center align-items-center align-items-stretch">
                  <input class="btn-check" type="radio" name="saleStatusLevelID" id="statusLevel<?php echo $opKey; ?>" value="<?php echo $statusLevel->saleStatusLevelID; ?>" >
                  <label class="form-check-label btn border-secondary-subtle btn-outline-info" for="statusLevel<?php echo $opKey; ?>">
                     <h5 class="text-center"><?php echo $statusLevel->statusLevel; ?></h5>
                     <p><?php echo $statusLevel->StatusLevelDescription; ?></p>               
                  </label>
               </div>
            </div>
            
            <?php
         }    
      }?>
   </div>
   <hr class="my-3">
   <div class='col-12 my-2'>
      <div class='form-group'>
         <label for='progressNotes'>Progress Notes</label>
         <textarea class='form-control bg-light-blue' name='progressNotes' id='progressNotes' rows='3' placeholder='Enter progress notes here...'></textarea>
      </div>
   </div>
   <div class="row">      
      <div class="col-sm-12 col-lg-4 my-2">
         <div class="form-group">
            <label for="salesCaseEstimate">Sales Case Estimate Value</label>
            <input type="text" class="form-control-sm form-control-plaintext border-bottom bg-light-blue px-2" name="salesCaseEstimate" id="salesCaseEstimate" value="" placeholder="Enter sales case estimate value">
         </div>
      </div>
   
      <div class="col-sm-12 col-lg-4 my-2">
         <div class="form-group">
            <label for="leadSourceID">Lead Source</label>
         <select class="form-select form-select-xs bg-light-blue" name="leadSourceID" id="leadSourceID">
               <?php 
               $leadSources = Data::lead_sources([], false, $DBConn);
               if($leadSources){
                  echo "<option value=''>Select Lead Source</option>";
                  foreach($leadSources as $source){
                     echo "<option value='{$source->leadSourceID}'>{$source->leadSourceName}</option>";
                  }
               } else {
                  echo "<option value=''>No lead sources available</option>";
               }?>
            </select>
         </div>
      </div>
      <?php $clientContacts = Client::client_contacts(['clientID'=>$case->clientID], true, $DBConn);?>
      <div class="col-sm-12 col-lg-4 my-2">
         <div class="form-group">
            <label for="salesCaseContactID">Sales Case Contact</label>
            <select class="form-select form-select-xs bg-light-blue" name="salesCaseContactID" id="salesCaseContactID">            
               <?php 
               if($clientContacts){?>
                  <option value="">Select Contact</option>
                  <?php
                  foreach($clientContacts as $contact){
                     echo "<option value='{$contact->clientContactID}'>{$contact->contactName} ({$contact->contactEmail})</option>";
                  }
               } else {
                  echo "<option value=''>No contacts available</option>";
               }?>
               <option value="addNew">Add New Contact</option>
            </select>         
         </div>
      </div>      
   </div>

   <!-- This div will be dynamically created when the user selects "Add New Contact" -->
   <div id="newContactDiv" class="col-12 my-2 d-none" >
      <fieldset class="col-md-12 ">
         <div class=" pt-0">
            <legend class="fs-18 bg-light-blue px-2 my-2">Personal Information</legend>
            <div class="row">
               <div class="form-group col-md-6">
                  <label class="nott mb-0 t500 text-primary">First Name</label>
                  <input type="text" name="firstName" class="form-control form-control-xs form-control-plaintext  bg-light border ps-2" placeholder="First Name" value="">
               </div>

               <div class="form-group col-md-6">
                  <label class="nott mb-0 t500 text-primary">Last Name</label>
                  <input type="text" name="lastName" class="form-control form-control-xs form-control-plaintext  border bg-light ps-2 py-0" placeholder="Last Name" value="">
               </div>

               <div class="form-group col-md-6">
                  <label class="nott mb-0 t500 text-primary">Title</label>
                  <input type="text" name="title" class="form-control form-control-xs form-control-plaintext  border bg-light ps-2" placeholder="title" value="">
               </div>

               <div class="form-group col-md-6">
                  <label class="nott mb-0 t500 text-primary">Email</label>
                  <input type="email" name="email" class="form-control form-control-xs form-control-plaintext bg-light border-bottom ps-2" placeholder="email@domain.com" value=" ">
               </div>

				<div class="form-group col-md-6">
					<label class="nott mb-0 t500 text-primary">Telephone</label>
					<input type="text" name="telephone" class="form-control form-control-xs form-control-plaintext bg-light border-bottom ps-2" placeholder="+254 00 000 0000" value="">
				</div>				
           
            <div class="form-group col-md-6">
               <label class="nott mb-0 t500 text-primary">Contact Role</label>				  			
               <select class="form-control form-control-xs form-control-plaintext border-bottom bg-light pl-2" name="contactTypeID">
                  <?php echo Form::populate_select_element_from_object($contactTypes, 'contactTypeID', 'contactType',  '', '', 'Select contact Role')  ?>
               </select>               
            </div>
          
				<div class="form-group col-md-6">
					<label class="nott mb-0 t500 text-primary">Address</label>				  			
					<select class="form-control form-control-xs form-control-plaintext border-bottom bg-light pl-2 clientAddressID" name="clientAddressID" id="clientAddressID">  
						<?= Form::populate_select_element_from_object($addresses, 'clientAddressID', 'address', '', '', 'Select Address')  ?>
                  <option value="addNew">Add New Address</option>
					</select>
				</div>
            <div class="newAddressDiv d-none row" id="newAddressDiv">
               <div class="form-group my-2 col-sm-12 col-lg-12">
                  <label for="address"> Address</label>
                  <textarea name="address" id="address"  class="borderless-mini address" ></textarea>
               </div>
               <div class="row">

                  <div class="form-group my-2 col-sm-12 col-lg-4">
                     <label for="postalCode" class="text-primary"> Postal Code</label>
                     <input type="text" name="postalCode" id="postalCode" class="form-control-sm form-control-plaintext border-bottom bg-light-blue" placeholder="Postal Code">
                  </div>
   
                  <div class="form-group my-2 col-sm-12 col-lg-4">
                     <label for="city" class="text-primary"> City</label>
                     <input type="text" name="city" id="city" class="form-control-sm form-control-plaintext border-bottom bg-light-blue" placeholder="City">
                  </div>
   
                  <?php $countryList = Data::countries([], false, $DBConn); ?>
   
                  <div class="form-group my-2 my-2 col-sm-12 col-lg-4"> 
                     <label for="country"> Country</label>
                     <select name="countryID" id="countryID" class="form-control-sm form-control-plaintext border-bottom bg-light-blue">
                        <?php echo Form::populate_select_element_from_object($countryList, 'countryID', 'countryName', '', '',  'Select Country') ?>
                     </select>
                  </div>
                  
                  <div class="fieldset card card-body shadow">
                     <div class="form-check mb-2">
                        <input class="form-check-input " type="radio" value="postalAddress" id="postalAddress" name="addressType" >
                        <label class="form-check-label" for="postalAddress">
                           Postal Address
                        </label>
                     </div>
   
                     <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" value="officeAddress" id="officeAddress" name="addressType" >
                        <label class="form-check-label" for="officeAddress">
                           Office Address
                        </label>
                     </div> 
               </div>
            </div>
         </div>
      </div>
   </fieldset>
   </div>


<script>
      document.addEventListener("DOMContentLoaded", function() {
         // Handle the change event for the salesCaseContactID select element
         const salesContactID = document.getElementById("salesCaseContactID").addEventListener("change", function() {
            // Get the selected value
            console.log(this.value);

            if (this.value === "addNew") {
               // Open the div with adding new address and contact to the client and sales case
               const newContactDiv = document.getElementById("newContactDiv");
               newContactDiv.classList.remove("d-none");
               newContactDiv.classList.add("d-block");
            } else {
               // Hide the new contact div if "Add New Contact" is not selected
               const newContactDiv = document.getElementById("newContactDiv");
               newContactDiv.classList.remove("d-block");
               newContactDiv.classList.add("d-none");
               
            }
         });

         console.log("Handling new contact addition");
         // handle the option to add new address
         const clientAddressID = document.getElementById("clientAddressID");
         console.log(clientAddressID.Value);
         document.querySelectorAll(".clientAddressID").forEach(element => {
            console.log("Adding event listener to clientAddressID");
            element.addEventListener("change", function() {
               console.log("Client Address ID changed to: ", this.value);
               if (this.value === "addNew") {
                  // Open the div with adding new address and contact to the client and sales case
                  const newAddressDiv = document.getElementById("newAddressDiv");
                  newAddressDiv.classList.remove("d-none");
                  newAddressDiv.classList.add("d-block");
               } else {
                  // Hide the new address div if "Add New Address" is not selected
                  const newAddressDiv = document.getElementById("newAddressDiv");
                  newAddressDiv.classList.remove("d-block");
                  newAddressDiv.classList.add("d-none");
               }
            });
            
         });
         
      });
   </script>
