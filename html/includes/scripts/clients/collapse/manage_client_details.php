<form action="<?= "{$base}php/scripts/clients/manage_clients.php"?>" method="post" id="manageClientDetailsForm" class="form-horizontal">          
   <div class="row">
      <div class="col-lg-6 col-md-12">
         <div class="row">
            <div class="form-group col-lg-6 d-none">
               <label for="clientID" class="text-primary"> Client ID</label>
               <input type="text" name="clientID" id="clientID" class="form-control-sm form-control-plaintext border-bottom px-2" placeholder="Client ID" value="<?= $clientDetails->clientID ?>" readonly>
            </div>

            <div class="form-group col-lg-2">
               <label for="clientCode" class="text-primary"> Client Code</label>
               <input type="text" name="clientCode" id="clientCode" class="form-control-sm form-control-plaintext border-bottom px-2" placeholder="Client Code" value="<?= $clientDetails->clientCode ?>" readonly>
            </div>

            <div class="form-group col-lg-10">
               <label for="clientName" class="text-primary "> Client Name</label>
               <input type="text" name="clientName" id="clientName" class="form-control-sm form-control-plaintext border-bottom px-2" placeholder="Client Name" value="<?= $clientDetails->clientName ?>" readonly> 
            </div>                

            <div class="form-group col-lg-6">
               <label for="vatNumber" class="text-primary"> VAT Number</label>
               <input type="text" name="vatNumber" id="vatNumber" class="form-control-sm form-control-plaintext border-bottom px-2" placeholder="VAT Number" value="<?= $clientDetails->vatNumber ?>" readonly>
            </div>

            <div class="form-group col-lg-6">
               <label for="clientType" class="text-primary "> Client Type</label>
               <select name="clientLevelID" id="clientLevelID" class="form-control-sm form-control-plaintext border-bottom px-2" readonly disabled >				
                  <?php echo Form::populate_select_element_from_object($clientLevels, 'clientLevelID', 'clientLevelName', $clientDetails->clientLevelID, '' , 'Select Client Type') ?>
               </select>
            </div>

            <div class="form-group col-lg-6">
               <label for="clientIndustry" class="text-primary "> Client Industry</label>
               <input type="text" name="clientIndustryID" id="clientIndustryID" class="form-control-sm form-control-plaintext border-bottom px-2 d-none " placeholder="Client Industry" value="<?= $clientDetails->clientIndustryID ?>">
               <button type="button" class="rounded btn btn-sm btn-info-light   dropdown-toggle d-flex align-items-center w-100" data-bs-toggle="dropdown" aria-expanded="false" disabled>
                  <span class="text-primary d-block selectedName"> <?= $clientDetails->clientIndustryID ? $clientDetails->industryName." - (". $clientDetails->sectorName .")" : 'Select  Industry'; ?> </span>
               </button>
               <ul class="dropdown-menu dropdown-menu-end">
                  <?php
                  if($industrySectors){
                     foreach ($industrySectors as $key => $sector) {						
                        $active= $clientDetails->clientIndustryID == $sector->sectorID ? ' activeDay ' : '';
                        $industries = Data::tija_industry(array('sectorID'=>$sector->sectorID), false, $DBConn);
                        ?>
                        <li>
                           <h5 class="dropdown-header <?= $active ?>"  data-id="<?= $sector->sectorID ?>" data-name="<?= $sector->sectorName ?>" data-type="sector" data-clientid="<?= $clientDetails->clientID ?>">
                              <?= $sector->sectorName ?>
                           </h5>
                           <?php
                           if($industries){
                              foreach ($industries as $key => $industry) {						
                                 $active= $clientDetails->clientIndustryID == $industry->industryID ? ' activeDay ' : '';
                                 ?>
                                 <a class="dropdown-item industryID ms-3 <?= $active ?>" data-industry-id="<?= $industry->industryID ?>" data-industry-name="<?= $industry->industryName ?>" data-type="industry" data-clientid="<?= $clientDetails->clientID ?>">
                                    <?= $industry->industryName ?>
                                 </a>
                                 <?php
                              }
                           }?>
                        </li>
                        <?php
                     }
                  }?>
                  <script>
                     document.querySelectorAll('.industryID').forEach(item => {
                        item.addEventListener('click', event => {
                           // get the form 
                           const form = document.getElementById('manageClientDetailsForm');

                           console.log(item);
                           // get all data attributes
                           const data = item.dataset;
                           console.log(data);
                           const selectedName = data.industryName || ""; // Use industryName if available, otherwise use name
                           const selectedID = data.industryId || data.id; // Use industryId if available, otherwise use id
                           const clientID = data.clientid || document.querySelector('#clientID').value; // Use clientid if available, otherwise use clientID from the input field
                           const type = item.getAttribute('data-type');
                           const clientIndustryID = form.querySelector('#clientIndustryID');
                           const selectedNameElement = form.querySelector('.selectedName');

                           if(type == 'sector') {
                              clientIndustryID.value = selectedID;
                              selectedNameElement.innerHTML = selectedName;
                           } else {
                              clientIndustryID.value = selectedID;
                              selectedNameElement.innerHTML = selectedName;
                           }
                        })
                     });
                  </script> 
               </ul>                    

            </div>

            <div class="form-group col-lg-6">
               <label for="" class="text-primary "> Client Owner Name</label>
               <select name="accountOwnerID" id="accountOwnerID" class="form-control-sm form-control-plaintext  px-2" disabled>				
                  <?php echo Form::populate_select_element_from_grouped_object($employeesCategorised, 'ID', 'employeeName', $clientDetails->accountOwnerID, '' , 'Select Case Owner') ?>
               </select>
            </div>
         </div>

      </div>
      <div class="col-lg-6 col-md-12">               
         <div class="form-group my-2">
            <label for="client_description"> Client Description</label>
            <!-- This textarea is hidden and will be used for editing -->
            <div class="d-none clientDescriptionDiv">
               <textarea name="clientDescription" id="clientDescription" class=" borderless-mini"  ><?= $clientDetails->clientDescription ?></textarea>
            </div>
            <div class=" bg-light-blue px-2 py-1" id="clientDescriptionDisplay">
               <?= $clientDetails->clientDescription ? $clientDetails->clientDescription : "Please insert Company Description"  ?>
            </div>
         </div>
      </div>
      <div class="col-12 d-none footerSubmit">
         <button type="submit" class="btn btn-primary btn-sm float-end">Save Changes</button>
      </div>
   </div>
</form>

<script>
   document.addEventListener('DOMContentLoaded', function() { 
      const clientDescriptionDisplay = document.getElementById('clientDescriptionDisplay');
      const clientDescription = document.getElementById('clientDescription');
      clientDescriptionDisplay.addEventListener('click', function() {
         clientDescription.classList.remove('d-none');
         clientDescriptionDisplay.classList.add('d-none');
         clientDescription.focus();
      });
      clientDescription.addEventListener('blur', function() {
         if(clientDescription.value.trim() === "") {
            clientDescriptionDisplay.innerHTML = "Please insert Company Description";
         } else {
            clientDescriptionDisplay.innerHTML = clientDescription.value;
         }
         clientDescription.classList.add('d-none');
         clientDescriptionDisplay.classList.remove('d-none');
      });

      // Initialize TinyMCE editor for client description
      tinymce.init({
         selector: 'textarea#clientDescription',
         height: 500,
         menubar: false,
         plugins: [
            'advlist autolink lists link image charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table paste code help wordcount'
         ],
         toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
         content_css: '//www.tiny.cloud/css/codepen.min.css'
      });
      const editClientDetailsButton = document.querySelector('.edit-client-details_main');
      editClientDetailsButton.addEventListener('click', function() {
         // Show the footer submit button
         document.querySelector('.footerSubmit').classList.remove('d-none');
         // Hide the edit button
         this.classList.add('d-none');
         // Show the form fields
         document.querySelectorAll('#manageClientDetailsForm input, #manageClientDetailsForm select, #manageClientDetailsForm button').forEach(input => {
            input.removeAttribute('readonly');
            input.disabled = false;
            input.classList.add('bg-light-blue');
            // input.classList.remove('form-control-plaintext');
            // input.classList.add('form-control');
         });

         // Show the client description textarea
         const clientDescriptionDiv = document.querySelector('.clientDescriptionDiv');
         clientDescriptionDiv.classList.remove('d-none');

         clientDescription.classList.remove('d-none');
         clientDescriptionDisplay.classList.add('d-none');
         const clentDetailsTitle = document.querySelector('.clentDetailsTitle');
         clentDetailsTitle.innerHTML = "Edit Client Details";
      });

   });
</script>