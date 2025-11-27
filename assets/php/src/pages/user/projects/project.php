<script>
// This script is for the project details page, handling task management and project editing functionalities.
// Add this JavaScript code at the bottom of your file
document.addEventListener('DOMContentLoaded', function () {
	// Multiple Select - Choices.js removed
	// const multipleSelect = document.querySelectorAll('.choices-multiple-default');
    // if (multipleSelect) {
    //     multipleSelect.forEach(select => {
    //         new Choices(select, {
    //             removeItemButton: true,
    //         });
    //     });
    // }

	/*var currentURL = window.location;
	let taskAnchor = document.querySelectorAll('.projectTask');
	for (const task of taskAnchor) {
		task.addEventListener('click', (e) => {
			e.preventDefault();
			let taskID = task.dataset.id;	
			console.log(projectTasks);
			let taskArr = projectTasks.filter((taskObj) => {
				return parseInt(taskObj.projectTaskID) === parseInt(taskID);
			}); 
			const taskDetails = taskArr[0];
			console.log(taskDetails);
		
			const manageTask = document.querySelector('#manageTask');
			taskModal = new bootstrap.Modal(manageTask);
			taskModal.show();
			let modalBody = manageTask.querySelector('.modal-body');	

			modalBody.querySelector('.projectTaskID').value =taskDetails.projectTaskID;
			modalBody.querySelector('.clientID').value = projectDetails.clientID;
			modalBody.querySelector('.projectID').value = projectDetails.projectID;
			modalBody.querySelector('.projectPhaseID').value =	taskDetails.projectPhaseID;
			modalBody.querySelector('.taskCode').value = taskDetails.projectTaskCode;
			modalBody.querySelector('.projectTaskName').value =taskDetails.projectTaskName;
			modalBody.querySelector('.hoursAllocated').value = taskDetails.hoursAllocated;
			modalBody.querySelector('.taskStatus').value = taskDetails.status;
			modalBody.querySelector('.taskWeighting').value =	taskDetails.taskWeighting;
			modalBody.querySelector('.projectTaskTypeID').value = taskDetails.projectTaskTypeID;
			modalBody.querySelector('.taskStartDate').value = taskDetails.taskStart;
			modalBody.querySelector('.taskDeadline').value = taskDetails.taskDeadline;
			// let dateRange = taskDetails.taskStart + ' to ' + taskDetails.taskDeadline;
			// let daterangeinput = modalBody.querySelector('.taskDateRange');
			// daterangeinput.value = dateRange;
			if (taskDetails.taskDescription === null) {
				taskDetails.taskDescription = '';
			}
			// initialize the tinyMCE editor			

			tinymce.init({
				selector: '.taskDescription',
				height: 150,
				menubar: false,
				setup: (editor) => {
					editor.on('init', (e) => {
						console.log('The Editor has initialized.');
						editor.setContent(`${taskDetails.taskDescription}`);
					});
				},
			});
		});
	}

	let assigneeAnchor = document.querySelectorAll('.editAssignee');
	for (let editAssignee of assigneeAnchor) {
		editAssignee.addEventListener('click', (e) => {
			e.preventDefault();
			let taskID = editAssignee.dataset.id;
			let assigneeArr = projectUserAssignments.filter((taskAss) => {
				return parseInt(taskAss.projectTaskID) === parseInt(taskID);
			});
			console.log(assigneeArr);
			const taskAssignees = document.querySelector('#taskAssignees');
			taskModal = new bootstrap.Modal(taskAssignees);
			taskModal.show();
			let modalBody = taskAssignees.querySelector('.modal-body'),
				closebutton = taskAssignees.querySelector('.btn-close'),
				submitAll = taskAssignees.querySelector('.submit');
			modalBody.innerHTML = '';
			const div = document.createElement('div');
			div.classList.add('col-12');
			div.classList.add('p-2');
			let assignees = '';
			for (let taskData of assigneeArr) {
				assignees += `<div class="list-group-item py-1"> 
								${taskData.assigneeName}
								<div class="dropdown float-end">
								  <a class=" nobg dropdown-toggle float-end" href="#"  id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
								    <i class="fa-solid fa-user-times"></i>
								  </a>
								  <ul class="dropdown-menu bg-danger py-0" aria-labelledby="dropdownMenuLink">
								    <li class="py-1">
								    	<a data-task="${taskData.assignmentTaskID}" id="delete_${taskData.assignmentTaskID}" class="dropdown-item delete bg-danger text-white" href="#">Delete User</a>
								    </li>						    
								  </ul>
								</div> 
							</div>`;
			}

			let contList = `<div class="list-group list-group-flush"> 					
							${assignees}
						</div>`;
			div.innerHTML = contList;
			modalBody.appendChild(div);

			deleteformDiv = document.createElement('div');
			deleteformDiv.innerHTML = `<form id="formDelete" action="${siteUrl}php/scripts/projects/manage_task_assignee.php" method= "post">
							
							<input type="hidden" class="form-control form-control-sm" name="projectTaskID" value="${taskID}">

						</form>`;

			modalBody.appendChild(deleteformDiv);
			let form = modalBody.querySelector('#formDelete');

			const showSubmit = () => {
				submitAll.classList.remove('d-none');
				submitAll.classList.add('float-end');
			};

			let assigneesList = modalBody.querySelectorAll('.delete');
			for (let userDiv of assigneesList) {
				userDiv.addEventListener('click', (e) => {
					e.preventDefault();
					let userRemove = e.target;
					let assignmentTaskID = userDiv.dataset.task;
					let user =
						userRemove.parentElement.parentElement.parentElement.parentElement;
					user.remove();
					if (closebutton) {
						closebutton.remove();
					}

					taskAssignees.querySelector('.footer-close').classList.add('d-none');

					showSubmit();
					deleteDiv = document.createElement('div');
					deleteDiv.innerHTML = `
						<input type="hidden" class="form-control form-control-sm" name="Suspended" value="Y">
						<input type="hidden" class="form-control form-control-sm" name="suspendTaskAssignmentID[]" value="${assignmentTaskID}">`;
					form.appendChild(deleteDiv);
				});
			}

			submitAll.addEventListener('click', (e) => {
				form.submit();
			});

			let addUserdiv = document.createElement('div');
			addUserdiv.classList.add('float-start');
			addUserdiv.classList.add('border-box');
			addUserdiv.innerHTML = `<button type='button' class="btn btn-primary addNewUser user "  > Assign New User</button>`;

			console.log(addUserdiv);
			let modalfooter = taskAssignees.querySelector('.modal-footer');
			modalfooter.appendChild(addUserdiv);
			let addNewUserDiv = modalfooter.querySelector('.addNewUser');
			addNewUserDiv.addEventListener('click', (e) => {
				const userDiv = document.createElement('div');
				userDiv.classList.add('form-group');
				userDiv.classList.add('col-12');

				let userDivContent = `
								<label> Select User</label>
							<select class='form-control' data-trigger  id='choices-multiple-default' name='newMemberUserID[]' >   
							
									<option value=''>Select user</option>`;
									projectTeamMembers.forEach((employee, idx) => {
					userDivContent += `<option value="${employee.userID}"> ${employee.teamMemberName}</php>`;
				});

				userDivContent += `	</select>
								`;

				userDiv.innerHTML = userDivContent;
				form.appendChild(userDiv);

			
				addNewUserDiv.classList.add('d-none');
				showSubmit();
			});
		});
	}

	// let editProjectAnch = document.querySelector('#editProjectAnch');
	// editProjectAnch.addEventListener('click', (e) => {
	// 	e.preventDefault();
	// 	const projectModDiv = document.querySelector('#editProject');
	// 	projectModal = new bootstrap.Modal(projectModDiv);
	// 	projectModal.show();

	// 	let modalBody = projectModDiv.querySelector('.modal-body');

	// 	modalBody.querySelector('.clientID').value = projectDetails.clientID;
	// 	modalBody.querySelector('.projectCode').value = projectDetails.projectCode;
	// 	modalBody.querySelector('.projectName').value = projectDetails.projectName;
	// 	modalBody.querySelector('.billingrate').value =
	// 		projectDetails.billingRateID;
	// 	modalBody.querySelector('.roundingInterval').classList.remove('d-none');
	// 	modalBody.querySelector('.roundingIntervalOption').value =
	// 		projectDetails.roundingInterval;
	// 	modalBody.querySelector('.businessUnitID').value =
	// 		projectDetails.businessUnitID;
	// 	modalBody.querySelector('.projectValue').value =
	// 		projectDetails.projectValue;
	// 	modalBody.querySelector('.salewonDiv').classList.remove('d-none');
	// 	modalBody.querySelector('.SaleWon').checked = true;
	// 	modalBody.querySelector('.orderDate').value = projectDetails.orderDate;

	// 	jQuery('.orderDate').datepicker('update', projectDetails.orderDate);
	// 	jQuery('.projectStart').datepicker('update', '2022-08-05');
	// 	jQuery('.projectClose').datepicker('update', '2022-08-10');
	// 	modalBody.querySelector(
	// 		'.projectIDDiv'
	// 	).innerHTML = `<input type="hidden" class="form-control form-control-sm form-control-plaintext" name="projectID" value="${projectDetails.projectID}" >`;
	// });*/
}); 

</script>

