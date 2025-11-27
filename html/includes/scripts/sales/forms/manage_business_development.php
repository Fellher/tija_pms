<form class="businessDevForm row" action="<?= "{$base}php/scripts/sales/manage_sale_prospect.php" ?>" method="post">	
   <input type="hidden" name="orgDataID" value="<?php echo $orgDataID; ?>"class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" hidden>
   <input type="hidden" name="entityID" value="<?php echo $entityID; ?>" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" hidden>
   <input type="hidden" name="salesCaseID" id="salesCaseID" value=""  class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" hidden>
   <div class="row">
      <div class="form-group col-sm-12 col-lg-12 col-md-12 my-2"> 
         <label for="">Prospect/Opportunity Name</label>
         <input type="text" id="salesCaseName" name="salesCaseName" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Opportunity/Prospect Case Name" required>
      </div>
      <div class="form-group col-sm-12 my-2">
         <label for="clientID" class="form-label mb-0 d-block">
            Client/potential Client/Company
         <!-- link with icon to add new prospect/potential client -->
         <a href="javascript:void(0);" class="text-primary float-end addNewClient" title="Add New Client"><i class="ri-add-line"></i> New Prospect</a>
         </label>
         <select id="clientID" name="clientID" class=" form-control form-control-sm form-control-plaintext bg-light-blue px-2 client clientID" required>
            <?php echo Form::populate_select_element_from_object($clients, 'clientID', 'clientName', (isset($clientID) && !empty($clientID)) ? $clientID : "", '', 'Select/Add Client');  ?>
            <option value="newClient">New Client</option>
         </select>						
      </div>
      <div class="newClientDiv d-none  col-lg-12 my-2 ">
         <div class="row">               
            <div class="form-group col-sm-12 my-2  ">
               <label for="countryID" class="form-label mb-0">Country</label>
               <select id="countryID" name="countryID" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2 countryID" >
                  <?php echo Form::populate_select_element_from_object($countries, 'countryID', 'countryName', (isset($countryID) && !empty($countryID)) ? $countryID : "", '', 'Select Country'); ?>
               </select>
            </div>
            <div class="form-group col-sm-12 my-2 ">
               <label for="city" class="form-label mb-0">City</label>
               <input type="text" id="city" name="city" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="City" >
            </div>
         </div> 
      </div>
      <div class="form-group col-sm-12 mt-2">
         <label for="clientContactID" class="form-label mb-0">Contact Person</label>
         <select id="clientContactID" name="clientContactID" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2 clientContactID" required>
            <option value="">Select Contact</option>
            <option value="newContact">New Contact</option>
         </select>
      </div>
      <div class="new_contact col-12 d-none my-2 bg-light p-2 rounded">
         <span class="text-danger d-block mb-2">Please fill in the contact details below:</span>
         <div class="form-group">
            <label for="contactName" class="form-label mb-0">Contact Name</label>
            <input type="text" id="contactName" name="contactName" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Contact Name" >
         </div>
         <div class="form-group">
            <label for="contactEmail"> Contact Person Email</label>
            <input type="email" id="contactEmail" name="contactEmail" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Contact Email" >
         </div>
         <div class="form-group">
            <label for="contactPhone"> Contact Person Phone</label>
            <input type="text" id="contactPhone" name="contactPhone" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Contact Phone" >
         </div>
         <div class="form-group">
            <label for="contactPersonTitle"> Contact Person Position</label>
            <input type="text" id="contactPersonTitle" name="contactPersonTitle" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Contact Position" >
            </div>

      </div>

      <div class="form-group col-sm-12  my-2">
         <label for="">Business Unit</label>
         <select id="businessUnitID" name="businessUnitID" class="form-control form-control-sm form-control-plaintext bg-light-blue businessUnitID" required>
            <?php echo Form::populate_select_element_from_object($businessUnits, 'businessUnitID', 'businessUnitName', '','', 'Select Business Unit'); ?>
            <option value="newUnit">Add New BusinessUnit</option>
         </select>
         <div id="newBusinessUnit" class="d-none">
            <input type="text" name="newBusinessUnit" class="form-control form-control-sm form-control-plaintext bg-light-orange px-2" placeholder="add new business unit" >
         </div>
      </div>
      <div class="form-group col-sm-12  my-2">
         <label for="salesPersonID" class="form-label mb-0">Prospect Owner</label>
         <select id="salesPersonID" name="salesPersonID" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" required>
            <?php echo Form::populate_select_element_from_grouped_object($employeeCategorize, 'ID', 'employeeName', $userDetails->ID, '', 'Select Owner'); ?>
         </select>
      </div>
      <div class="col-sm-12" >
         <label for="expectedRevenue" class="form-label mb-0">Expected Revenue</label>
         <input type="text" id="expectedRevenue" name="expectedRevenue" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Expected Revenue" >
      </div>
   </div>
      
   <div class="form-group col-sm-12 col-lg-12 col-md-12 my-2 text-end">
      <button type="submit" class="btn btn-primary">Submit</button>
   </div>
</form>
