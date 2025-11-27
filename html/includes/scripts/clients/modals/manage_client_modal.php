<div class="col-12 clientForm" id="clientForm">
   <div class="form-group">
		<input type="hidden" name="clientCode" id="clientCode" class="form-control-sm form-control-plaintext border-bottom bg-light-blue" placeholder="Client Code" hidden>
		<label for="clientName" class="text-primary "> Client Name</label>
		<input type="text" name="clientName" id="clientName" class="form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Client Name">
	</div>
	
	<div class="form-group">
		<label for="vatNumber" class="text-primary"> PIN Number</label>
		<input type="text" name="vatNumber" id="vatNumber" class="form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="PIN Number">
	</div>
   
	<div class="form-group">
		<label for="" class="text-primary "> Client Owner Name</label>
		
		<select name="accountOwnerID" id="accountOwnerID" class="form-control-sm form-control-plaintext bg-light-blue px-2">				
		<?php echo Form::populate_select_element_from_grouped_object($employeesCategorised, 'ID', 'employeeNameWithInitials', $userDetails->ID, '' , 'Select Case Owner') ?>
		</select>
	</div>
	<!-- 
	<div class="form-group my-2">
		<label for="" class="nott mb-0 t400 font-12 "> Client Contact Name</label>
		<input type="text" name="clientContactName" id="ContactName" class=" form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Client Contact Name">
	</div>
	<div class="form-group my-2">
		<label for="" class="nott mb-0 t400 font-12 "> Client Contact Email</label>
		<input type="email" name="clientContactEmail" id="contactEmail" class="form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Contact Email">
	</div>	 
	-->
   <div class="form-group my-2">
   	<label for="adress"> Address</label>
		<textarea name="address" id="address" class="borderless-mini" ></textarea>
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
		<select name="country" id="country" class="form-control-sm form-control-plaintext border-bottom bg-light-blue">
			<?php echo Form::populate_select_element_from_object($countryList, 'countryID', 'countryName', '25', '',  'Select Country') ?>
		</select>
	</div>
	
	<div class="fieldset card card-body shadow">
		<div class="form-check mb-2">
			<input class="form-check-input " type="radio" value="PostalAddress" id="primaryoutlineChecked" name="addressType" >
			<label class="form-check-label" for="primaryoutlineChecked">
				Postal Address
			</label>
		</div>
		<div class="form-check mb-2">
			<input class="form-check-input" type="radio" value="OfficeAddress" id="warningoutlineChecked" name="addressType" >
			<label class="form-check-label" for="warningoutlineChecked">
				Office Address
			</label>
		</div>

		<div class="form-check mb-2">
			<input class="form-check-input  form-checked-secondary" type="checkbox" value="BillingAddress" id="secondaryoutline" name="BillingAddress" >
			<label class="form-check-label" for="secondaryoutline">
				Billing Address
			</label>
		</div>
		
		<div class="form-check mb-2">
			<input class="form-check-input  form-checked-danger" type="checkbox" value="Headquarters" id="dangeroutline" name="Headquarters" >
			<label class="form-check-label" for="dangeroutline">
				Main Office(Headquarters)
			</label>
		</div>		

	</div>


	<div class="form-group my-2">
		<input type="hidden" name="clientID" id="clientID" value="">
		<input type="hidden" class="form-control" name="orgDataID" id="orgDataID" value="<?php echo $orgDataID ?>">
		<input type="hidden" class="form-control" name="entityID" id="entityID" value="<?php echo $entityID ?>">
		<!-- <button type="submit" class="btn btn-primary btn-sm float-end" id="saveClient">Save</button> -->
	</div>

</div>