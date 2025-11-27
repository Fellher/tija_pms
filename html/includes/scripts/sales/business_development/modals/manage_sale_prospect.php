<div id="propspectModalForm">
   <div class="row">
            
         <input type="text" class="form-control-sm form-control-plaintext border-bottom" id="orgDataID" name="orgDataID" value="<?= $orgDataID ?>" placeholder="Organisation ID" required  hidden>
      
         <input type="text" class="form-control-sm form-control-plaintext border-bottom" id="entityID" name="entityID" value="<?= $entityID ?>" placeholder="Entity ID" required hidden>
         <input type="text" class="form-control-sm form-control-plaintext border-bottom" id="salesProspectID" name="salesProspectID" value="" placeholder="Sales Prospect ID" required hidden>
         <fieldset class="card card-body ">
            
            <div class="d-flex align-items-center justify-content-evenly">
            <span class=" fs-20 ">Is This prospect A client?</span>
               <div class="form-check form-switch">            
                  <input class="form-check-input" name="isClient" type="radio" role="switch"  id="clientNo" value="N">
                  <label class="form-check-label" for="clientNo">No</label>
               </div>
               <div class="form-check form-check-md form-switch">
                  <input class="form-check-input" name="isClient" type="radio" role="switch" id="clientYes" value="Y">
                  <label class="form-check-label" for="clientYes">Yes</label>
               </div>
            </div>
            
            <div class="new prospectNameDiv d-none">
               <div class="form-group my-2 ">
                  <label for="salesProspectName" class="text-primary"> Sales Prospect Name</label>
                  <input type="text" class="form-control-sm form-control-plaintext border-bottom" id="salesProspectName" name="salesProspectName" value="" placeholder="Sales Prospect Name" required>
               </div>
              
               <div class="form-group my-2">
                  <label for="address" class="text-primary"> Address</label>
                  <textarea class="form-control borderless-mini" id="address" name="address" rows="3"></textarea>
               </div>
            </div>

            <div class="form-group col-xl-6 d-none clientIDDiv" id="clientIDDiv">
               <label for="clientID"> <span class="text-primary"> Client ID</span>   <span class="text-muted fst-italic"> (if client)</span> </label>
            
                  <select class="form-control-sm form-control-plaintext border-bottom" id="clientID" name="clientID" aria-label="Default select example">
                     <?= Form::populate_select_element_from_object($clients, 'clientID',  'clientName', '', '', 'Select Client'); ?>
                  </select>
            </div>
         
      </fieldset> 
      <script>
         document.addEventListener('DOMContentLoaded', function() {
            const clientNo = document.getElementById('clientNo');
            const clientYes = document.getElementById('clientYes');
            const prospectNameDiv = document.querySelector('.prospectNameDiv');
            const clientIDDiv = document.getElementById('clientIDDiv');

            clientNo.addEventListener('change', function() {
               if (this.checked) {
                  prospectNameDiv.classList.remove('d-none');
                  clientIDDiv.classList.add('d-none');
               }
            });

            clientYes.addEventListener('change', function() {
               if (this.checked) {
                  prospectNameDiv.classList.add('d-none');
                  clientIDDiv.classList.remove('d-none');
               }
            });
         });
      </script>

     
<div class="form-group my-2">
                     <label for="prospectEmail" class="text-primary"> Email Address</label>
                     <input type="email" class="form-control-sm form-control-plaintext border-bottom" id="prospectEmail" name="prospectEmail" value="" placeholder="Email Address" required>
                  </div>
     
      
      <div class="form-group my-2">
         <label for="prospectCaseName" class="text-primary"> Case Name</label>
         <input type="text" class="form-control-sm form-control-plaintext border-bottom" id="prospectCaseName" name="prospectCaseName" value="" placeholder="prospect case name" required>
      </div>
      <div class="form-group col-md-6 my-2">
         <label for="estimatedValue" class="text-primary"> Estimated Value</label>
         <input type="text" class="form-control-sm form-control-plaintext border-bottom" id="estimatedValue" name="estimatedValue" value="" placeholder="Phone Number" required>
      </div>
      <div class="form-group col-md-6 my-2">
         <label for="probability" class="text-primary"> Sales Prospect Probability</label>
        <input type="text" class="form-control-sm form-control-plaintext border-bottom" id="probability" name="probability" value="" placeholder="Sales Prospect Status" required>
      </div>
      <div class="form-group col-md-6 my-2">
         <label for="LeadSourceID" class="text-primary"> Lead Source</label>
         <select class="form-control-sm form-control-plaintext border-bottom" id="leadSourceID" name="leadSourceID" aria-label="Default select example">
            <?= Form::populate_select_element_from_object($leadSources, 'leadSourceID',  'leadSourceName', '', '', 'Select Lead Source'); ?>
         </select>
      </div>
      <div class="form-group col-md-6 my-2">
         <label for="businessUnitID" class="text-primary"> Business Unit ID</label>
         <select class="form-control-sm form-control-plaintext border-bottom" id="businessUnitID" name="businessUnitID" aria-label="Default select example">
            <?= Form::populate_select_element_from_object($businessUnits, 'businessUnitID',  'businessUnitName', '', '', 'Select Business Unit'); ?>
         </select>      
      </div>
      
   </div>

</div>