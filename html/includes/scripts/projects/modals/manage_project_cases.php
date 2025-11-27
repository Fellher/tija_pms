<div class="manageProjectsForm">
	<div class="form-group">
		<div class="form-group">
			<label class="nott font-14 mb-0 t400 ">Project Type</label>
			<select name="projectTypeID" id="projectTypeID" class="form-control form-control-xs form-control-plaintext bg-light-blue projectID  ">
			<?php echo Form::populate_select_element_from_object($projectTypes, 'projectTypeID', 'projectTypeName', "", '', 'Select Project Type') ?>
			</select>
		</div>
	</div>
	<div class="form-group clientInput">
		<div class="form-group">
			<label class="nott font-14 mb-0 t400 ">Client</label>
			<select name="clientID" class="form-control form-control-xs form-control-plaintext bg-light-blue clientID" id="clientID" >


			<?php echo Form::populate_select_element_from_object($clients, 'clientID', 'clientName', (isset($clientID) && $clientID) ? $clientID :"" , '', 'Select Client') ?>
			<!-- <option value="new">New Client</option> -->
			</select>
		</div>
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
					const form = document.querySelector('.manageProjectsForm');
					if (!form) return; // Exit if form not found

					console.log(form);
					// Initialize Tom Select for the client select element
					const clientSectorID = form.querySelector('#clientSectorID');
					if (!clientSectorID) return; // Exit if element not found

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




	<input type="hidden" name="orgDataID" value="<?php echo $orgDataID ?>" >
   <input type="hidden" name="entityID" value="<?php echo $entityID ?>" >
   <input type="hidden" name="caseID" class="caseID" value="" >
   <input type="hidden" name="projectID" class="projectID" value="" >
	<div class="form-group projectIDDiv"></div>

	<fieldset class="bg-light row">
		<legend class="border-bottom fs-18 bg-light mt-2 border-bottom border-darg bs-gray-300">Case Details</legend>
		<div class="form-group col-sm-2 my-1 d-none">
			<label class="nott font-14 mb-0 t400 text-primary fst-italic ">Code</label>
			<input type="text" id="projectCode" name="projectCode" class="form-control form-control-sm form-control-plaintext border-bottom bg-light-blue projectCode">
		</div>
		<div class="form-group col-sm my-1">
			<label  class="nott font-14 mb-0 t400 text-primary fst-italic  ">Case Name/Project name</label>
			<input type="text" name="projectName" id="projectName"  class="form-control form-control-xs form-control-plaintext border-bottom bg-light-blue projectName">
		</div>
      <div class="form-group my-1">
         <label class="nott font-14 mb-0 t400 text-primary fst-italic ">Project Owner</label>
         <select name="projectOwnerID" id="projectOwnerID" class="form-control form-control-xs form-control-plaintext bg-light-blue projectOwnerID">
         <?php echo Form::populate_select_element_from_grouped_object($employeeCategorised, 'ID', 'employeeNameWithInitials',   $userDetails->ID, '', 'Select Project Owner') ?>
         </select>
      </div>


      <div class="form-group my-1 projectTimeline ">
         <label class="nott font-16 mb-0 t400 text-dark ">Project Timeline</label>
            <div class=" row">
					<div class="col-md">
						<label class="nott font-12 mb-0 t400 text-primary fst-italic ">Project Start Date</label>
						<input type="text" class="form-control text-start form-control-sm text-left bg-light-blue form-control-plaintext date " id="projectStart" placeholder="Project start Date"  name="projectStart" value="" />
					</div>
					<div class="col-md">
						<label class="nott font-12 mb-0 t400 text-primary fst-italic ">Project End Date</label>
						<input type="text" class="form-control text-start form-control-sm text-left bg-light-blue form-control-plaintext date " id="projectClose" placeholder="Project end Date"  name="projectClose" value="" />
					</div>
            </div>
      </div>
		<script>
			document.addEventListener("DOMContentLoaded", function() {
				// INSERT_YOUR_CODE
				let manageProjectsForm = document.querySelector(".manageProjectsForm");
				if (!manageProjectsForm) return; // Exit if form not found

				let projectStart = manageProjectsForm.querySelector("#projectStart");
				let projectEnd = manageProjectsForm.querySelector("#projectClose");
				let projectTimeline = document.querySelector(".projectTimeline");

				if (!projectStart || !projectEnd) return; // Exit if elements not found

					projectStart.addEventListener("change", function() {
						console.log("Project start date changed to: " + projectStart.value);
						console.log("Project end date: " + projectEnd.value);
						validateProjectDates();
					});
					projectEnd.addEventListener("change", function() {
						validateProjectDates();
					});
				// document.getElementById("projectStart").addEventListener("blur", function() {
				// 	validateProjectDates();
				// });

				// document.getElementById("projectEnd").addEventListener("blur", function() {
				// 	validateProjectDates();
				// });

				function validateProjectDates() {
					const projectStart = manageProjectsForm.querySelector("#projectStart").value;
					const projectEnd = manageProjectsForm.querySelector("#projectEnd").value;

					if (projectStart && projectEnd) {
						const startDate = new Date(projectStart);
						const endDate = new Date(projectEnd);

						if (endDate < startDate) {
							// If the end date is before the start date, show an alert and focus on the end date input
							// create alert div and append it to the projectTimeline div
							let alertDiv = document.createElement("div");
							alertDiv.className = "alert alert-danger alert-dismissible fade show";
							alertDiv.innerHTML = `<strong>Error!</strong> Project end date cannot be before the start date. Please correct it. <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
							projectTimeline.appendChild(alertDiv);
							// focus on the end date input
							document.getElementById("projectEnd").focus();
							projectEnd.classList.add("is-invalid");
						} else {
							// If the dates are valid, remove any existing alert and invalid class
							let existingAlert = projectTimeline.querySelector(".alert");
							if (existingAlert) {
								existingAlert.remove();
							}
							projectEnd.classList.remove("is-invalid");
						}
					}
				}
			});

		</script>

		<div class="form-group my-1">
			<label class="nott font-14 mb-0 t400 text-primary fst-italic ">Billing Rate</label>
			<select name="billingRateID" id="billingRateID" class="form-control form-control-xs form-control-plaintext bg-light-blue billingrate  ">
			<?php echo Form::populate_select_element_from_object($billingRates, 'billingRateID', 'billingRate', '', '', 'Select Billing Rate') ?>
			</select>
		</div>


		<div class="form-group-col col-md-12">
			<div class="form-group  col-12 my-1">
				<label class="nott font-14 mb-0 t400 text-primary fst-italic "> Rounding Of Time</label><br>
				<span class="fs-12 text-danger fst-italic mb-2" >Automatically round log's duration for billing purposes(rounded duration). Actual Duration is preserved</span>
				<div class="row">
					<div class="col-sm-6">
						<select id="roundingoff" class="form-control form-control-xs rounding  form-control-plaintext bg-light-blue border-bottom roundingoff" name="roundingoff"  >
							<?php echo Form::populate_select_element_from_object($config['roundingOptions'],"key", "value", "", "", "Select rounding"); ?>
						</select>
					</div>
					<div class="roundingInterval col-sm-6 d-none" >
						<select id="roundingInterval" name="roundingInterval" class="form-control form-control-xs  form-control-plaintext border-bottom bg-light-blue roundingIntervalOption ">
							<?php echo Form::populate_select_element_from_object($config['roundingOffParams'], "key", "value", "", "", "Select rounding of value") ?>
						</select>
					</div>
				</div>
			</div>
		</div>

		<script>
			let roundingOff = document.getElementById("roundingoff");
			if (roundingOff) {
				roundingOff.addEventListener("change", (e)=>{
				let selection = e.target.value;

				if (selection === "no_rounding" || selection === "") {
					document.querySelector(".roundingInterval").classList.add("d-none");
				} else {
					document.querySelector(".roundingInterval").classList.remove("d-none");
				}
			});
			}
		</script>
		<div class="form-group col-md-6">
			<div class="row">

				<div class="col-12">
					<label for="businessUnit" class="nott font-12 mb-0 t400 text-primary fst-italic ">Business Unit</label>
					<select name="businessUnitID" id="businessUnitProj" class="form-control form-control-xs form-control-plaintext border-bottom bg-light-blue businessUnitID" onchange="newunit(this)">
						<?php echo Form::populate_select_element_from_object($businessUnits, 'businessUnitID', 'businessUnitName', '', '', 'Select Business Unit'); ?>
						<option value="newbusinessUnit"> Add Unit</option>
					</select>
				</div>

				<div class="newUnitProj col-12 d-none" >
					<label class="nott font-12 mb-0 t400 ">Input new business Unit</label>
					<input type="text" name="newbusinessUnit" class="form-control form-control-xs form-control-plaintext border-bottom bg-light-blue newbusinessUnit "  >
				</div>

			</div>

			<script>
				let businessUnitProj = document.getElementById("businessUnitProj");
				if (businessUnitProj) {
					businessUnitProj.addEventListener("change",(e)=> {
					let selection = e.target.value;
					if (selection === "newbusinessUnit") {
						document.querySelector(".newUnitProj").classList.remove("d-none");
					} else {
						document.querySelector(".newUnitProj").classList.add("d-none");
						document.querySelector(".newbusinessUnit").value="";
					}
				});
				}
			</script>
		</div>

		<div class="form-group col-md-6 ">
         <label class="nott font-12 text-primary mb-0 t400 ">Project Value</label>
         <input type="text" name="projectValue" class="form-control form-control-xs form-control-plaintext border-bottom bg-light-blue projectValue" value="" placeholder="Project Value">
      </div>

		<div class="btn-group" role="group" aria-label="Basic radio toggle button group my-2">
		  <input type="radio" class="btn-check" name="SaleStatus" id="saleLost" value="NoSale" autocomplete="off" >
		  <label class="btn btn-outline-danger" for="saleLost"><i class="icon-line-cross-octagon mx-2"></i>No Sale </label>
		  <input type="radio" class="btn-check SaleWon" name="SaleStatus" id="SaleWon" value="SaleWon" autocomplete="off">
		  <label class="btn btn-outline-info" for="SaleWon"><i class="icon-ok-sign mx-2"></i>Sale Won</label>
		</div>

		<div class="salewonDiv border rounded shadow p-2 mt-3 col-sm-10 mx-auto d-none">
			<div class="form-group">
				<label class="nott font-16 mb-0 t400 " for="">Order Date</label>
				<input type="text" value="<?php echo $dt->format('Y-m-d') ?>" id='date'  name="orderDate"  class="form-control  form-control-xs text-left component-datepicker past-enabled bg-light-blue form-control-plaintext border-bottom border-primary date"  placeholder="YYYY-MM-DD">
			</div>
		</div>

		<script>
			let saleLost = document.querySelector("#saleLost"), SaleWon = document.querySelector('#SaleWon');

			if (saleLost) {
				saleLost.addEventListener("click", (e)=> {
					document.querySelector(".salewonDiv").classList.add("d-none");
				});
			}

			if (SaleWon) {
				SaleWon.addEventListener("click", (e)=> {
					document.querySelector(".salewonDiv").classList.remove("d-none");
				});
			}
		</script>

	</fieldset>
</div>

<script>
		// Initialize TomSelect when modal is shown to avoid double initialization
		function initializeLegacyFormSelects() {
			// Initialize Tom Select for the client selection
			const clientSelect = document.querySelector('#manageProjectCase .clientID');
			if (clientSelect && !clientSelect.tomselect) {
				new TomSelect(clientSelect, {
					create: true,
					maxItems: 1,
					placeholder: 'Select or create a client',
					onChange: function(value) {
						// get the form for current client select
						const form = clientSelect.closest('form');
						console.log(form);
						console.log(`Client changed to: ${value}`);
						// Trigger the change event to update contact person options
						const event = new Event('change');
						clientSelect.dispatchEvent(event);
						// Check if the value is 'new' and handle it accordingly
						console.log(`New client added: ${value}`);
						// Show the new client input if 'new' is selected
						const newClientDiv = form.querySelector('.newClientDiv');
						// if (value === 'new') {
						// 	newClientDiv.classList.remove('d-none');
						// 	const newClientInput = form.querySelector('#clientName');
						// 	newClientInput.value = ''; // Clear the input for new client name
						// } else {
						// 	newClientDiv.classList.add('d-none');
						// }

						// if (value === 'new') {
						// 	clientSelect.value = '';
						// }
					},
					onOptionAdd: function(value, item) {
						const form = clientSelect.closest('form');
						console.log(form);
						// log the current value of the select element
						console.log(`New client added: ${value} at on option add`);
						// Show the new client input if 'new' is selected
						const newClientDiv = form.querySelector('.newClientDiv');

							newClientDiv.classList.remove('d-none');
							const newClientNote = document.createElement('input');
							newClientNote.type = 'text';
							newClientNote.name = 'newClientNote';
							newClientNote.classList.add('form-control', 'form-control-sm', 'form-control-plaintext', 'bg-white', 'px-2', 'mb-3');
							newClientNote.placeholder = 'Input new client name';
							newClientNote.value = 'newClient';
							newClientDiv.appendChild(newClientNote);
						// get the input for new client name


							const newClientInput = form.querySelector('#clientName');
							 // check if clientName exists
						if (newClientInput) {
								newClientInput.value = value; // Set the value of the input to the new client name
                	}

					}
				});
			}
			// initialize Tom Select for project managers
			const projectManagersSelect = document.querySelector('#manageProjectCase select[name="projectManagersIDs[]"]');
			if (projectManagersSelect && !projectManagersSelect.tomselect) {
				new TomSelect(projectManagersSelect, {
					create: true,
					maxItems: null, // Allow multiple selections
					placeholder: 'Select or create project managers',
					onChange: function(value) {
						if (value === 'new') {
							projectManagersSelect.value = '';
						}
					}
				});
			}

			// Handle project type change to show/hide client selection
			const projectTypeID = document.querySelector('#manageProjectCase select[name="projectTypeID"]');
			const clientInput = document.querySelector('#manageProjectCase .clientInput');
			const clients = <?= json_encode($clients) ?>; // Assuming $clients is an array of client objects

			if (projectTypeID && clientInput) {
				// Remove existing listener if any
				const newProjectTypeID = projectTypeID.cloneNode(true);
				projectTypeID.parentNode.replaceChild(newProjectTypeID, projectTypeID);

				newProjectTypeID.addEventListener('change', function() {
				console.log(this.value);
				const modalClientSelect = document.querySelector('#manageProjectCase .clientID');
				if (!modalClientSelect) return;

				if (this.value == '2') { // Assuming '2' is the ID for 'Internal'
					clientInput.classList.add('d-none'); // Hide the client selection
					modalClientSelect.value = ""; // Clear the client selection
					// modalClientSelect.setAttribute('disabled', 'disabled'); // Disable the client selection
					modalClientSelect.innerHTML = '';
					clients.forEach(client => {
						const option = document.createElement('option');
						option.text = client.clientName;
						option.value = client.clientID;
						if (client.clientID == '1') {
							option.selected = true;
						}
						modalClientSelect.appendChild(option);
					});
					modalClientSelect.value = "1";

				} else {
					clientInput.classList.remove('d-none'); // Show the client selection
					modalClientSelect.removeAttribute('disabled'); // Enable the client selection

				}
			});
			}
		}

		// Initialize on DOMContentLoaded
		document.addEventListener('DOMContentLoaded', function() {
			// Initialize modal event listener
			const legacyModal = document.getElementById('manageProjectCase');
			if (legacyModal) {
				legacyModal.addEventListener('shown.bs.modal', function() {
					initializeLegacyFormSelects();
				});
			}
		});
	</script>