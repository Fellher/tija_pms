<div class="primaryContactForm" id="primaryContactForm">	
	<input type="text" name="clientAddressID" id="clientAddressID" value="<?php echo isset($clientAddressID) ? $clientAddressID : "" ?>" <?= $isAdmin ? "" : "hidden" ?> class="form-control form-control-sm form-control-plaintext border-bottom bg-light-blue px-2" placeholder="Client Address ID" >

   <div class="form-group my-2">
   	<label for="address"> Address</label>
		<textarea name="address" id="address"  class="borderless-mini address" ></textarea>
	</div>

	<div class="form-group">
		<label for="postalCode" class="text-primary"> Postal Code</label>
		<input type="text" name="postalCode" id="postalCode" class="form-control-sm form-control-plaintext border-bottom bg-light-blue" placeholder="Postal Code">
	</div>

	<div class="form-group my-2">
		<label for="city" class="text-primary"> City</label>
		<input type="text" name="city" id="city" class="form-control-sm form-control-plaintext border-bottom bg-light-blue" placeholder="City">
	</div>

	<?php $countryList = Data::countries([], false, $DBConn); ?>

	<div class="form-group my-2"> 
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

		<div class="form-check mb-2">
			<input class="form-check-input  form-checked-secondary" type="checkbox" value="BillingAddress" id="billingAddress" name="billingAddress" >
			<label class="form-check-label" for="billingAddress">
				Billing Address
			</label>
		</div>
		
		<div class="form-check mb-2">
			<input class="form-check-input  form-checked-danger" type="checkbox" value="headquarters" id="headquarters" name="headquarters" >
			<label class="form-check-label" for="headquarters">
				Main Office(Headquarters)
			</label>
		</div>
	</div>


	<div class="form-group my-2 d-none ">
		<label for="primaryContact" class="text-primary"> Primary Contact Client ID </label>
		<input type="text" class="form-control form-control-plaintext bg-light-blue" name="clientID" id="clientID" value="<?php echo $clientID ?>" <?= $isAdmin ? "" : "hidden" ?>>
		<label for="primaryContact" class="text-primary"> Primary Contact Org Data  ID </label>
		<input type="text" class="form-control form-control-plaintext bg-light-blue" name="orgDataID" id="orgDataID" value="<?php echo $orgDataID ?>">
		<label for="primaryContact" class="text-primary"> Primary Contact Entity ID </label>
		<input type="text" class="form-control form-control-plaintext bg-light-blue" name="entityID" id="entityID" value="<?php echo $entityID ?>">
	</div>
		<!-- <button type="submit" class="btn btn-primary btn-sm float-end" id="saveClient">Save</button> -->
	</div>
