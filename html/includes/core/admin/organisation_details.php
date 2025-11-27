<form action="<?php echo "{$base}php/scripts/global/manage_organisation_details.php" ?>" method="POST" class="mb-0" id="organisationDetailsForm">
	<?php 
	if ($OrganisationDetails) {
		// var_dump($OrganisationDetails);?>
		<input type="hidden" name="orgDataID" class="form-control-xs form-control-plaintext border-bottom " value="<?php echo "$OrganisationDetails->orgDataID"; ?>">
		<?php					
	} ?>				
	<legend class="bg-light border-bottom fs-6 t300"> Company Details</legend>
	<div class="row">		
		<div class="col-md-3 form-group mb-2">
			<label for="" class="nott mb-0 t400 text-primary">Company Name</label>
			<input type="text" name="orgName" id="entityName" class="form-control-xs form-control-plaintext border-bottom " value="<?php echo (isset($OrganisationDetails) && !empty($OrganisationDetails->orgName)) ? $OrganisationDetails->orgName : '' ?>" readonly>
		</div>
		<div class="col-md form-group mb-2	">
			<label for="" class="nott mb-0 t400 text-primary"> Number of Empoyees</label>
			<input type="text" name="numberOfEmployees" id="numberOfEmployees" class="form-control-xs form-control-plaintext border-bottom " value="<?php echo (isset($OrganisationDetails) && !empty($OrganisationDetails->numberOfEmployees)) ? $OrganisationDetails->numberOfEmployees : '' ?>" readonly>
		</div>
		<div class="col-md form-group mb-2">
			<label for="registrationNumber" class="nott  t400 text-primary"> Registration Number </label>
			<input type="text" name="registrationNumber" id="registrationNumber" class="form-control-xs form-control-plaintext border-bottom" value="<?php echo (isset($OrganisationDetails) && !empty($OrganisationDetails->registrationNumber)) ? $OrganisationDetails->registrationNumber : '' ?>" readonly>
		</div>
		<div class="col-md">
			<label for="orgPIN" class="nott mb-0 t400 text-primary"> PIN </label>
			<input type="text" name="orgPIN" id="orgPIN" class="form-control-xs form-control-plaintext border-bottom " value="<?php echo (isset($OrganisationDetails) && !empty($OrganisationDetails->orgPIN)) ? $OrganisationDetails->orgPIN : '' ?>" readonly>
		</div>
		<div class="col-md form-switch pt-3 mb-2">
			<input class="form-check-input" type="checkbox" role="switch" name="costCenterEnabled" id="costCenterEnabled" value="Y" <?php echo (isset($OrganisationDetails) && !empty($OrganisationDetails->costCenterEnabled) && $OrganisationDetails->costCenterEnabled === "Y") ? "checked" : '' ?> readonly>
  			<label class="form-check-label nott t400 text-primary " for="costCenterEnabled">Cost Center enabled</label>
		</div>
	</div>

	<legend class="bg-light border-bottom fs-6">  Contact Details</legend>
	<div class="row">
		<div class="form-group col-xl-4 mb-2">
			<label for="orgAddress" class="nott mb-0 t400 text-primary"> Address</label>
			<input type="text" name="orgAddress" id="orgAddress" class="form-control-xs form-control-plaintext border-bottom " value="<?php echo (isset($OrganisationDetails) && !empty($OrganisationDetails->orgAddress)) ? $OrganisationDetails->orgAddress : '' ?>" placeholder="Add Address" readonly>
		</div>
		<div class="form-group col-xl-4 mb-2">
			<label for="orgPostalCode" class="nott mb-0 t400 text-primary"> Postal Code</label>
			<input type="text" name="orgPostalCode" id="orgPostalCode" class="form-control-xs form-control-plaintext border-bottom " value="<?php echo (isset($OrganisationDetails) && !empty($OrganisationDetails->orgPostalCode)) ? $OrganisationDetails->orgPostalCode : '' ?>" placeholder="Add Postal Code" c>
		</div>
		<div class="form-group col-xl-4 mb-2">
			<label for="city" class="nott mb-0 t400 text-primary"> City</label>
			<input type="text" name="orgCity" id="orgCity" class="form-control-xs form-control-plaintext border-bottom " value="<?php echo (isset($OrganisationDetails) && !empty($OrganisationDetails->orgCity)) ? $OrganisationDetails->orgCity : '' ?>" placeholder="Add City" readonly>
		</div>
		<div class="form-group col-xl-4 mb-2">
			<label for="orgCountry" class="nott mb-0 t400 text-primary"> Country</label>
			<input type="text" name="orgCountry" id="orgCountry" class="form-control-xs form-control-plaintext border-bottom " value="<?php echo (isset($OrganisationDetails) && !empty($OrganisationDetails->orgCountry)) ? $OrganisationDetails->orgCountry : '' ?>" placeholder="Add Country" readonly>
		</div> 
		<div class="form-group col-xl-4 mb-2">
			<label for="orgPhoneNumber1" class="nott mb-0 t400 text-primary"> Telephone 1 * </label>
			<input type="text" name="orgPhoneNumber1" id="orgPhoneNumber1" class="form-control-xs form-control-plaintext border-bottom " value="<?php echo (isset($OrganisationDetails) && !empty($OrganisationDetails->orgPhoneNumber1)) ? $OrganisationDetails->orgPhoneNumber1 : '' ?>" placeholder="Add phoneNumber " readonly>
		</div>
		<div class="form-group col-xl-4 mb-2">
			<label for="orgPhoneNUmber2" class="nott mb-0 t400 text-primary"> Telephone (Alternative) * </label>
			<input type="text" name="orgPhoneNUmber2" id="orgPhoneNUmber2" class="form-control-xs form-control-plaintext border-bottom " value="<?php echo (isset($OrganisationDetails) && !empty($OrganisationDetails->orgPhoneNUmber2)) ? $OrganisationDetails->orgPhoneNUmber2 : '' ?>" placeholder="Add phoneNumber " readonly>
		</div>
		<div class="form-group col-xl-4 mb-2">
			<label for="orgEmail" class="nott mb-0 t400 text-primary"> Company Email Address * </label>
			<input type="text" name="orgEmail" id="orgEmail" class="form-control-xs form-control-plaintext border-bottom " value="<?php echo (isset($OrganisationDetails) && !empty($OrganisationDetails->orgEmail)) ? $OrganisationDetails->orgEmail : '' ?>" placeholder="Add Organisation Email  " readonly>
		</div>
        <div class="form-group col-xl-4  mb-2">
            <label for="orgEmail" class="nott mb-0 t400 text-primary"> Industry Sector * </label>
            <select name="industrySectorID" id="industrySectorID" class="form-control-xs form-control-plaintext border-bottom " value="<?php echo (isset($OrganisationDetails) && !empty($OrganisationDetails->industrySectorID)) ? $OrganisationDetails->industrySectorID : '' ?>" placeholder="Add Organisation Email  " readonly>
                <option value="">Select Industry Sector</option>
                <?php
                if ($industrySectors) {                   
                    foreach ($industrySectors as $industrySector) {?>
                        <option value="<?php echo $industrySector->industrySectorID; ?>" <?php echo (isset($OrganisationDetails) && !empty($OrganisationDetails->industrySectorID) && $OrganisationDetails->industrySectorID === $industrySector->industrySectorID) ? "selected" : '' ?>><?php echo $industrySector->industryTitle; ?></option>
                        <?php
                    }
                }?>
            </select>
        </div>
	</div>
	<div class="col-md-12 border-top pt-2 mb-2 updateDetails d-none">
		<button type="submit" class="btn btn-primary float-end">Update Details</button>
	</div>				
</form>

