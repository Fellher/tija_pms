<div class="salesCaseForm">	
	<input type="hidden" name="orgDataID" value="<?php echo $orgDataID; ?>">
	<input type="hidden" name="salesPersonID" value="<?php echo $userID; ?>">	
   <input type="hidden" name="entityID" value="<?php echo $entityID; ?>">
	<input type="hidden" name="salesCaseID" id="salesCaseID" value="">	
	<input type="hidden" name="salesStage" value="opportunities">
	<div class="form-group col-12 my-2">
		<label for="clientID" class="form-label mb-0">Client</label>
		<select id="clientID" name="clientID" class=" form-control form-control-sm form-control-plaintext bg-light-blue px-2 client clientID" style="z-index: 1051;">
			<?php echo Form::populate_select_element_from_object($clients, 'clientID', 'clientName', (isset($clientID) && !empty($clientID)) ? $clientID : "", '', 'Select/Add Client');  ?>			
		</select>						
	</div>
	 
	<div class="newClientDiv d-none   card card-body shadow-lg my-2 ">
		<div class="row">
			<div class="form-group col-sm-12 col-lg-12 col-md-12 ">
				<label for="clientName" class="form-label mb-0">New Client Name</label>
				<input type="text" id="clientName" name="clientName" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="New Client Name" >
			</div>
			<div class="form-group col-sm-12 col-lg-12 col-md-12 ">
				<label for="clientSectorID" class="form-label mb-0">Client Sector</label>
				<select id="clientSectorID" name="clientSectorID" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2 clientSectorID" >
					<?php echo Form::populate_select_element_from_object($industrySectors, 'sectorID', 'sectorName', (isset($clientSectorID) && !empty($clientSectorID)) ? $clientSectorID : "", '', 'Select Client Sector'); ?>
				</select>
			</div>
			<div class="form-group col-sm-12 col-lg-12 col-md-12 ">
				<label for="clientIndustryID" class="form-label mb-0">Client Industry</label>
				<select id="clientIndustryID" name="clientIndustryID" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2 clientTypeID" >
					<?php echo Form::populate_select_element_from_object($industries, 'industryID', 'industryName', (isset($industryID) && !empty($industryID)) ? $industryID : "", '', 'Select Client Type'); ?>
				</select>
			</div>
			<div class="form-group col-sm-12 col-lg-12 col-md-12 ">
				<label for="address" class="form-label mb-0">Client Address</label>
				<textarea id="address" name="address" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Client Address"></textarea>
			</div>
			<div class="form-group col-sm-12 col-lg-12 col-md-12 ">
				<label for="city" class="form-label mb-0">City</label>
				<input type="text" id="city" name="city" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="City" >
			</div>
			<div class="form-group col-sm-12 col-lg-12 col-md-12 ">
				<label for="postalCode" class="form-label mb-0">Postal Code</label>
				<input type="text" id="postalCode" name="postalCode" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Postal Code" >
			</div>
			<div class="form-group col-sm-12 col-lg-12 col-md-12 ">
				<label for="countryID" class="form-label mb-0">Country</label>
				<select id="countryID" name="countryID" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2">
					<?php echo Form::populate_select_element_from_object($countries, 'countryID', 'countryName', (isset($countryID) && !empty($countryID)) ? $countryID : "", '', 'Select Country'); ?>
				</select>
			</div>
			
			<div class="fieldset card card-body shadow">
				<label class="form-label mb-0">Address Type</label>
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
			</div>


			<script>
				document.addEventListener("DOMContentLoaded", function() {
					// current form with the clientID class
					const form = document.querySelector('.salesCaseForm');
					console.log(form);
					// Initialize Tom Select for the client select element
					const clientSectorID = form.querySelector('#clientSectorID');
				clientSectorID.addEventListener('change', function() {
					const selectedValue = this.value;
					const allIndustries = <?php echo json_encode($industries); ?>;
					console.log(allIndustries);
					const filteredIndustries = allIndustries.filter(industry => industry.sectorID == selectedValue);
					console.log(filteredIndustries);
					console.log(`Client Sector changed to: ${this.value}`);
					const industrySelect = form.querySelector('#clientIndustryID');
					// populate the industry select element with the filtered industries
					industrySelect.innerHTML = `<option value="">Select Client Industry</option>`;
					// check that filteredIndustries is not empty
					if (filteredIndustries.length === 0) {
						industrySelect.innerHTML += `<option value="">No Industries Available</option>`;
						return;
					}
					filteredIndustries.forEach(industry => {
						const option = document.createElement('option');
						option.value = industry.industryID;
						option.textContent = industry.industryName;
						industrySelect.appendChild(option);
					});
					// Additional logic can be added here as needed
				});
					
				});
			</script>

		</div> 
	</div>
	
	
	<!-- <div class="form-group my-2">
		<label for="salesCaseContactID" class="form-label mb-0">Sales Case Contact Person</label>
		<select id="salesCaseContactID" name="salesCaseContactID" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2 salesCaseContactID">
			<option value="">Select Contact Person</option>
			<?php 
			if($clientContacts) {
				foreach ($clientContacts as $key => $clientContact) { ?>
					<option value="<?php echo $clientContact->clientContactID; ?>"><?php echo $clientContact->contactName; ?> (<?php echo $clientContact->contactEmail; ?> - <?php echo $clientContact->title; ?>)</option>
				<?php 
				} 
			}?>
		</select>
	</div> -->
	<div class="newContactPersonDiv d-none card-body shadow-lg my-2">
		
		<div class="row gx-1">
			<div class="form-group col-md-3 my-2">
				<label for="contactTitle" class="form-label mb-0">Contact Title</label>
				<input type="text" id="contactTitle" name="contactTitle" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Contact Title" >
			</div>
			<div class="form-group col-md-9 my-2">
				<label for="contactName" class="form-label mb-0">Contact Name</label>
				<input type="text" id="contactName" name="contactName" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Contact Name" >
			</div>

		</div>
		
		<div class="form-group my-2">
			<label for="contactEmail" class="form-label mb-0">Contact Email</label>
			<input type="email" id="contactEmail" name="contactEmail" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Contact Email" >
		</div>
		<div class="form-group my-2">
			<label for="contactPhone" class="form-label mb-0">Contact Phone</label>
			<input type="text" id="contactPhone" name="contactPhone" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Contact Phone" >
		</div>

		<div class="form-group my-2">
			<label for="contactTypeID" class="form-label mb-0">Contact Type</label>
			<select id="contactTypeID" name="contactTypeID" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2">
				<option value="">Select Contact Type</option>
				<?php 
				if($clientContactTypes) {
					foreach ($clientContactTypes as $key => $contactType) { ?>
						<option value="<?php echo $contactType->contactTypeID; ?>"><?php echo $contactType->contactType; ?></option>
					<?php 
					} 
				}?>
			</select>
		</div>
		
		<div class="form-group my-2">
			<label for="clientAddressID" class="form-label mb-0"> Client Address </label>
			<select id="clientAddressID" name="clientAddressID" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2">
				<option value="">Select Client Address</option>
				<?php 
				if($clientAddresses) {
					foreach ($clientAddresses as $key => $clientAddress) { ?>
						<option value="<?php echo $clientAddress->clientAddressID; ?>"><?php echo $clientAddress->address; ?>, <?php echo $clientAddress->city; ?>, <?php echo $clientAddress->postalCode; ?></option>
					<?php 
					} 
				}?>
			</select>
		</div>
	</div>

	<div class="form-group my-2">
		<label for="">Sales Case Name</label>
		<input type="text" id="salesCaseName" name="salesCaseName" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Case Name" >
	</div>

	<div class="form-group my-2 contactPersonDiv"></div>
	<div class="form-group my-2"> 
		<label for="">Business Unit</label>
		<select id="businessUnitID" name="businessUnitID" class="form-control form-control-sm form-control-plaintext bg-light-blue">
			<?php echo Form::populate_select_element_from_object($businessUnits, 'businessUnitID', 'businessUnitName', '','', 'Select Business Unit'); ?>
			<option value="newUnit">Add New BusinessUnit</option>
		</select>
		<div id="newBusinessUnit" class="d-none">
			<input type="text" name="newBusinessUnit" class="form-control form-control-sm form-control-plaintext bg-light-orange px-2" placeholder="add new business unit">
		</div>
	</div>
	<div class="form-group">
		<label for="statusLevel" class="form-label mb-0">Status Level</label>	
		<?php
		$statusLevels = Data::sales_status_levels(['orgDataID'=>$orgDataID, 'entityID'=>$entityID], false, $DBConn);	
		$leadSource = Data::lead_sources([], false, $DBConn);
		if($statusLevels) {?>
			<div class="btn-group col-12" role="group" aria-label="Basic radio toggle button group ">
				<?php
				foreach ($statusLevels as $key => $statusLevel) {
					$levelNode = "level_{$key}_{$statusLevel->saleStatusLevelID}";
					if($key !==count($statusLevels)-1){?>
						<input type="radio" class="btn-check" name="saleStatusLevelID" id="<?= $levelNode ?>" autocomplete="off" value="<?= $statusLevel->saleStatusLevelID ?>" >
						<label class="btn btn-outline-primary btn-group-xs" for="<?= $levelNode ?>"><?= $statusLevel->statusLevel ?></label>
					<?php 
					} 
					$statusLevels[$key]->statusLevel = $statusLevel->statusLevel;					
				}?>
			</div>
			<!-- <div class="form-group my-2">
				<label for="statusLevel" class="form-label mb-0">Status Level</label>
				<select id="saleStatusLevelID" name="saleStatusLevelID" class="form-control form-control-sm form-control-plaintext bg-light-blue">
					<?php echo Form::populate_select_element_from_object($statusLevels, 'saleStatusLevelID', 'statusLevel', '', '', 'Select Status Level') ?>
				</select>
			</div> -->
			<?php
		}?>	
	</div>
	<div class="form-group my-2">
		<label for="">Sales Case Estimate Value</label>
		<input type="text" id="salesCaseEstimate"  name="salesCaseEstimate" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Sale Estimate value " >
	</div>

	<div class="form-group my-2">
		<label for="">Sales Case Probability</label>
		<input type="text" id="probability" name="probability" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Probability of project">
	</div>

	<div class="form-group my-2">
		<label for="">Expected Order Date</label>
		<input type="text" id="date" name="expectedCloseDate" value="" class="form-control form-control-sm text-start component-datepicker form-control-plaintext bg-light-blue today px-2" placeholder="Expected Order Date">
	</div>	
	

	<div class="form-group my-2">
		<label for="">Lead Source</label>
		<select id="leadSourceID" name="leadSourceID" class="form-control form-control-sm form-control-plaintext bg-light-blue" >
			<?php echo Form::populate_select_element_from_object($leadSource, 'leadSourceID', 'leadSourceName', '', '', 'Select Lead Source') ?>
			<option value="newSource">Add new source lead</option>
		</select>		
	</div>

</div>

	<script>		
		// check that the dom is loaded
		document.addEventListener("DOMContentLoaded", function() {
			let clientContacts = <?php echo json_encode($clientContacts); ?>;
			let client = document.querySelector("#clientID")
			client.addEventListener("change", (e) => {
				console.log(`clientID: ${e.target.value} has changed`);
				const clientID = e.target.value;
				console.log(clientID);
				let filteredClientContacts = clientContacts.filter(contact => contact.clientID == clientID);

				console.log(filteredClientContacts);
				let contactPerson = document.querySelector(".contactPersonDiv");
				console.log(contactPerson);

				contactPerson.innerHTML = "";
				if (filteredClientContacts.length > 0) {
					let contacts = document.createElement("select");
					contacts.classList.add("form-control");
					contacts.classList.add("form-control-sm");
					contacts.classList.add("form-control-plaintext");
					contacts.classList.add("bg-light-blue");
					contacts.classList.add("px-2");
					contacts.classList.add("contactPerson");
					contacts.setAttribute("name", "contactPersonID");
					contacts.setAttribute("id", "contactPersonID");
					
					contacts.innerHTML = `
					
					<option value="">Select Contact Person</option>`;
					filteredClientContacts.forEach(contact => {
						let option = document.createElement("option");
						option.value = contact.clientContactID;
						option.textContent = `${contact.contactName} (${contact.contactEmail} - ${contact.title})`;
						contacts.appendChild(option);
					});
					console.log(contacts);
					contactPerson.innerHTML = `<label for="contactPersonID" class="form-label mb-0">Contact Person</label>`;
					contactPerson.classList.add("my-2");
					
					contactPerson.appendChild(contacts);

					
				} else {
					contactPerson.classList.remove("d-none");
					contactPerson.value = "";
					contactPerson.innerHTML = `
					<div class="col-12 card card-body bs-gray-200 border-primary">
						<input type="hidden" name="newSalesContactName" value="newSalesContactName">
						<div class="form-label mb-0">Add New Contact Person</div>
						<div class="form-group my-2">
							<label for="contactName" class="form-label mb-0">Contact Person</label>
							<input type="text" id="contactName" name="contactName" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Contact Person Name">
						</div>
						<div class="form-group my-2">
							<label for="contactEmail" class="form-label mb-0">Contact Person Email</label>
							<input type="email" id="contactEmail" name="contactEmail" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Contact Person Email">
						</div>
						<div class="form-group my-2">
							<label for="contactPhone" class="form-label mb-0">Contact Person Phone</label>
							<input type="text" id="contactPhone" name="contactPhone" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Contact Person Phone">
						</div>
						<div class="form-group my-2">
							<label for="title" class="form-label mb-0">Contact Title</label>
							<input type="text" id="title" name="contactTitle" class="form-control form-control-sm form-control-plaintext bg-light-blue px-2" placeholder="Contact Title">
						</div>
						</div>
													`;
				

				}

				
			});

		let businessUnit = document.querySelector("#businessUnitID");
			businessUnit.addEventListener("change", (e)=>{
				const businessUnitID = e.target.value;
				if (businessUnitID === "newUnit") {
					businessUnit.classList.add("d-none");
					let unitDiv = document.createElement("div");
					unitDiv.innerHTML = `<div id="newBusinessUnit" class=" bs-gray-300 card card-body ">
											<label class="d-block nott mb-0 t400"> Add New Business Unit <span id="return-btn" value="return" class="float-end btn-xs btn btn-link"><i class="bi-x-circle "></i></span></label>
											<input type="text" name="newBusinessUnit" class="form-control form-control-sm form-control-plaintext bg-white px-2" placeholder="add new business unit">
										</div>`;
						businessUnit.parentElement.appendChild(unitDiv);
						document.querySelector("#return-btn").addEventListener("click", ()=> {
							unitDiv.remove();
							businessUnit.classList.remove("d-none");
							businessUnit.value="";
						});
				}
			});

		let leadSource = document.querySelector("#leadSourceID");
		// console.log(leadSource);
		leadSource.addEventListener("change", (e)=> {
			const leadSourceID = e.target.value;
			if (leadSourceID === "newSource") {

				leadSource.classList.add("d-none");
				let source = document.createElement("div");
				source.innerHTML= `<div id="leadSourceAdd" class="col-12 card card-body bs-gray-300 mt-2">
									<small>Add New Lead Source <span id="return-btn" value="return" class="float-end btn-xs btn btn-link"><i class="bi-x-circle "></i></span></small>
									<input type="text" class="form-control form-control-sm bg-white form-control-plaintext px-2" name="newLeadSource" placeholder="Add new Lead source" >
								</div>`;
				leadSource.parentElement.appendChild(source);

				document.querySelector("#return-btn").addEventListener("click", ()=> {
					source.remove();
					leadSource.classList.remove("d-none");
					leadSource.value="";
				});
			} 
		});


		
		});
		
		
	</script>