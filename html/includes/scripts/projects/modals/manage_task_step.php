<fieldset class="manageTaskStepForm" id="manageTaskStepForm">
	<div class="d-none">
	<label for="projectTaskID"> Project Task ID</label>
	<input type="text" name="projectTaskID" class="form-control-sm form-control" value="">
	<label for="subtaskID"> Subtask ID</label>
	<input type="text" name="subtaskID" class="form-control-sm form-control" value="">
	</div>
	
	<div class="form-group">
		<label for="Subtask" class="nott font-sm t400"> Subtask Name </label>
		<input type="text" name="subTaskName" class="form-control form-control-sm form-control-plaintext bg-light-blue" placeholder="please add subtask" required >
	</div>
	<div class="row">
		<!-- <div class="form-group col-sm ">
			<label for="Subtask" class="nott font-sm t400"> Subtask Allocated Work Hours </label>
			<input type="text"  name="subTaskAllocatedWorkHours" class="form-control form-control-sm form-control-plaintext bg-light-blue timepicker24" id="timepickr1" placeholder="0:00hrs" required >
		</div> -->

		<div class="form-group mb-0 col-12">
			<label for="Subtask" class="nott font-sm t400"> Subtask Allocated Work Hours </label>
				<input type="text"  name="subTaskAllocatedWorkHours"   class="form-control form-control-sm form-control-plaintext bg-light-blue  " id="subTaskAllocatedWorkHours" placeholder="Choose time (00:00hrs)">
		</div> 
		<script>
			document.addEventListener('DOMContentLoaded', function () {
				const timeInput = document.getElementById('subTaskAllocatedWorkHours');
				timeInput.value = '00:00';
				timeInput.addEventListener('focus', function () {
					this.value = '';
				});

				timeInput.addEventListener('blur', (e) => {
						validateTimeInput(e.target);
					});

				// let timeInput = document.querySelectorAll('.workHours');
				// timeInput.forEach(input => {
				// 	input.addEventListener('blur', (e) => {
				// 		validateTimeInput(e.target);
				// 	});
				// });
				// timeInput.addEventListener('blur', function (e) {
				// 	console.log(e);
				// 	const value = this.value;
				// 	validateTimeInput(value);
				// 	// if (value.includes('.') && value.split('.')[1].length <= 2) {
				// 	// 	const hours = parseInt(value.split('.')[0]);
				// 	// 	const minutes = parseInt(value.split('.')[1]);
				// 	// 	const formattedTime = `${hours}:${minutes < 10 ? '0' : ''}${minutes}`;
				// 	// 	this.value = formattedTime;
				// 	// }
				// });
				// timeInput.addEventListener('input', function () {
				// 	const regex = /^[0-9]{1,2}:[0-9]{2}$/;
				// 	if (!regex.test(this.value)) {
				// 		this.value = '00:00';
				// 	}
				// });
			});
		</script>
		
		<div class="form-group col-sm my-2">
			<label class="nott font-sm t400 d-block"> Subtask Due Date <span class="float-end d-none text-danger taskDuration"> </span></label>
			<input type="text" name="subtaskDueDate"  value="" class="form-control-sm text-left form-control-plaintext bg-light-blue date subTaskDueDate" placeholder="format <?php echo isset($dt) ? $dt->format('Y-m-d') : date('Y-m-d');?>" autocomplete="off">	
			<span class='d-block text-danger dateerror'></span>				
		</div>						
	</div>				
	<!-- <div class="form-group col-md-12">
		<label for="assignedTo" class="nott font-16 text-primary t400">Select Task Members</label>
		<select class=" form-select selectAssignee  "  name="assignee"    style="width:100%;" required>								
			<?php // echo Form::populate_select_element_from_object($allEmployees, 'ID', 'employeeName', '' , '' , 'Select team Member') ?>
		</select>
	</div> -->
	
	<div class="form-group col-md-12 my-1">
				<label for="assignedTo" class="nott font-16 text-primary t400 d-block">Select subtask Members <span class="float-end"> <i class="ti ti-plus"></i> All Employees </span></label> 
				
				<select class="form-control form-control-sm   taskAssignee " data-trigger   name="assignee" > 
				
					<?=  Form::populate_select_element_from_grouped_object($employees, 'ID', 'employeeName', '' , '' , 'Select team Member') ?>
					
				</select>
			</div>
	<div class="form-group col-md-12">
		<label for="subtaskNarrative">Subtask Description/Notes </label>
		<textarea class="form-control basic borderless-mini" id="subtaskNarrativestep" name="subTaskDescription"   rows="3" placeholder="Add Subtask Description/Notes"></textarea>						
	</div>			

</fieldset>

<script>
	// Function to fill task step form with data
	function fillTaskStepForm(formElement, data) {
		// Map form fields to their corresponding data attributes
		const fieldMappings = {
			'projectTaskID': 'projectTaskId',
			'subtaskID': 'subtaskId',
			'subTaskName': 'subTaskName',
			'subTaskAllocatedWorkHours': 'subTaskAllocatedWorkHours',
			'subtaskDueDate': 'subtaskDueDate',
			'assignee': 'assignee',	
			'subTaskStatusID': 'subTaskStatusId',
			'subTaskDescription': 'subTaskDescription',
		
		};

		// Fill regular form inputs
		for (const [fieldName, dataAttribute] of Object.entries(fieldMappings)) {
			const input = formElement.querySelector(`[name="${fieldName}"]`);
			if (input) {
				input.value = data[dataAttribute] || '';
			}
		}

		 // Fill the textarea with tinyMCE
		 tinymce.init({
			selector: '#subtaskNarrativestep'
		 });
		 const editor = tinymce.get('subtaskNarrativestep');
		 if (editor) {
			editor.setContent(data.subTaskDescription || '');
		 }
	}

	document.addEventListener('DOMContentLoaded', function () {
		const manageTaskStepForm = document.querySelector('#manageTaskStepForm');
		const manageSubtaskBtn = document.querySelectorAll('.manageSubtaskBtn');
		manageSubtaskBtn.forEach(btn => {
			btn.addEventListener('click', function (e) {
				e.preventDefault();
				console.log(this.value);
				//get data from the clicked button
				const data = this.dataset;
				console.log(data);

				// Fill form with data
				fillTaskStepForm(manageTaskStepForm, data);
			});

			
		});

		const editSubtaskBtn = document.querySelectorAll('.editSubtaskBtn');
			editSubtaskBtn.forEach(btn => {
				btn.addEventListener('click', function (e) {
					e.preventDefault();
					e.stopPropagation();
					console.log(this.value);
					const data = this.dataset;
					console.log(data);
					fillTaskStepForm(manageTaskStepForm, data);
				});
			});


		// manageTaskStepForm.addEventListener('submit', function (e) {
		// 	e.preventDefault();
		// 	console.log(this.value);
		// 	const formData = new FormData(this);
		// 	console.log(formData);
		// 	const formDataObject = Object.fromEntries(formData);
		// 	console.log(formDataObject);
		// 	const formDataJson = JSON.stringify(formDataObject);
		// 	console.log(formDataJson);
		// });
	});
</script>