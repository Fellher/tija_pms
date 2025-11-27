<div class="row" id="manageAbsenceForm">
	<div class="col-md-6 row">
		<input type="hidden"  class="form-control form-control-sm form-control-plaintext bg-light-blue" name= 'absenceDate' value="<?php echo $dt->format('Y-m-d'); ?>">
		 <input type="hidden" name="userID" class="form-control form-control-sm" value="<?php echo $userID ?>">
		<div class="form-group mb-3 col-md-6 mb-0">			
			<label for="floatingInput"  class="mb-0 nott t500">Absence Name</label>
		  	<input type="text" class="form-control form-control-xs form-control-plaintext bg-light-blue px-2" id="floatingInput" name="absenceName"  placeholder="absenceName" required >		
			  <div class="invalid-feedback">Required Field</div>	 							  
		</div>
		
		<div class="form-group col-md-6 mb-0">
			<label for="absenceTypeID"  class="mb-0 nott t500"> Absence Type</label>							
			<select name="absenceTypeID" id="absenceTypeID" class="form-control  form-control-xs form-control-plaintext bg-light-blue px-2" required>
				<?php echo Form::populate_select_element_from_object($absenceTypes, 'absenceTypeID', 'absenceTypeName', '', '', 'select absence Type') ?>
			</select>	
			<div class="invalid-feedback">Required field</div>									
		</div>

		<div class="form-group col-md-12 mb-0" >
			<label id="target_abs" for="grp_option_abs" class="mb-0 nott t500"> Absence - Select Project case</label>
			<select name="projectID[]" id="grp_option_abs" class="form-control form-control-xs form-control-plaintext bg-light-blue choices-multiple-default" multiple required >
         <?php echo Form:: populate_select_element_from_grouped_object($projectClientArray, 'projectID', 'projectName',  '', '', $blankText='Select:') ?>
			</select>
			<div class="invalid-feedback">Absence project case is required</div>	
		</div>

      <!-- <div class="form-group">
      <p class="fw-semibold mb-2">Absence - Select Affected Project</p>
            <select class="form-control" data-trigger name="projectID[]" id="choices-multiple-default" multiple>
            <?php //echo Form:: populate_select_element_from_grouped_object($projectArray, 'projectID', 'projectName',  '', '', $blankText='Select:') ?>
            </select>
      </div> -->

		<script>
			// const grp_option_abs= document.getElementById('grp_option_abs');
			// grp_option_abs.addEventListener('change', getLabel_abs);
			// function getLabel_abs() {
			// 	let target= document.getElementById('target_abs');	
			// 	let selectVal2 = grp_option.options[grp_option_abs.selectedIndex].parentElement.label;
			// 	console.log(target);
			// 	target.innerHTML=  ` Select case(Project/Sale) <span class="text-danger float-end nott t500 ms-4"> Client: <em> ${selectVal2} </em></span>`;
			// }
		</script>

		<fieldset class="row timeSet mb-0">
			<div class="form-group col-sm-6">
				<label for="" class="nott mb-0 t400 ">Start Time</label>
				<div class="input-group text-start" >
					<input type="text" id="startTime" name="startTime" class="form-control form-control-xs form-control-plaintext bg-light-blue px-2"  placeholder="00:00 hrs " required/>
					<div class="invalid-feedback">start Time must be in the format 00:00</div>									
				</div>
			</div>

			<div class="form-group col-sm-6 mb-0">
				<label for="" class="nott mb-0 t400 text-primary"> End Time</label>
				<div class="input-group text-start" >
					<input type="text" id="endTime" name="endTime" class="form-control  form-control-xs form-control-plaintext bg-light-blue  px-2" placeholder="00:00 hrs " required/>		
					<div class="invalid-feedback">End Time must be in the format 00:00</div>								
				</div>
			</div>
			
		</fieldset>	
		<div class="form-check form-switch mb-0  ps-3" >
			<input class="form-check-input ms-5" name= "allday" id="allDay" value="Y" type="checkbox" id="flexSwitchCheckDefault">
			<label class="form-check-label ml-3" for="flexSwitchCheckDefault">All Day</label>
		</div>
	</div>

	<div class="col-md-6">
		<div class="form-group col-md-12-mb-0 py-2">
			<label for="description" class="col-md-12 nott mb-0 t500 text-dark mb-2  "> Add Desription</label>
			<textarea class="form-control borderless-mini" name="absenceDescription"   rows="3" placeholder="Edit absence  description" required></textarea>
		</div>
	</div>
</div>
<script>
	// check that page is loaded
	document.addEventListener('DOMContentLoaded', function() {
		//initialize grp_option_abs as tomselect element with multiple selection
		new TomSelect('#grp_option_abs', {
			plugins: ['remove_button'],
			maxItems: null,
			placeholder: 'Select project case',
			allowEmptyOption: false,
			create: true,
			sortField: {
				field: "text",
				direction: "asc"
			}
		});

		let startTime=document.getElementById('startTime');
		let endTime=document.getElementById('endTime');
		let allDay= document.getElementById('allDay');

			startTime.addEventListener('blur', validateStart);
			endTime.addEventListener('blur', validateEnd);
			allDay.addEventListener('change', setAllDay);

			document.getElementById('manageAbsenceForm').addEventListener('submit', function(event) {
				let isValid = true;
				this.querySelectorAll('input, textarea').forEach(function(element) {

					console.log(element);	
					if (element.required && element.value.trim() === '') {
						element.classList.add('is-invalid');
						isValid = false;
					} else {
						element.classList.remove('is-invalid');
					}
				});
				if (!isValid) {
					event.preventDefault();
				}
				event.preventDefault();
				// prevent form submission if validation fails
				if (!isValid) {
					
					return false;
				}
			});

			function validateStart() {				
				const re= /^[0-2]{1}[0-9]{1}\:[0-5][0-9]$/;
				if (!re.test(startTime.value)) {
					startTime.classList.add('is-invalid');
				} else {
					startTime.classList.remove('is-invalid');
				}
			}
			function validateEnd() {				
				const re= /^[0-2]{1}[0-9]{1}\:[0-5][0-9]$/;
				if (!re.test(endTime.value)) {
					endTime.classList.add('is-invalid');
				} else {
					endTime.classList.remove('is-invalid');
				}
			}


			function setAllDay() {								

				if (allDay.checked==true) {
					startTime.value= '08:00';
					endTime.value= '17:00';
					startTime.setAttribute('readonly', true);
					endTime.setAttribute('readonly', true);


				}
			}

		});
	</script>