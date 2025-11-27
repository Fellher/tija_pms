<script>
console.log('THis page is loaded');

// Add this JavaScript code at the bottom of your file
document.addEventListener('DOMContentLoaded', function () {
	let editProjects = document.querySelectorAll('.editProject');
	console.log('this is loaded client Details');
	console.log(editProjects);
	for (let editPro of editProjects) {
		console.log(editPro);
		editPro.addEventListener('click', (e) => {
			e.preventDefault();
			e.stopPropagation();
			let projectID = editPro.dataset.id;
			console.log(editPro);
			console.log(projectID);

			let projectArray = projects.filter((project) => {
				return parseInt(project.projectID) === parseInt(projectID);
			});

			console.log(projectArray[0]);
			let projectDetails = projectArray[0];

			const manageProjectCase = document.querySelector('#manageProjectCase');
			projectModal = new bootstrap.Modal(manageProjectCase);
			projectModal.show();

			let modalBody = manageProjectCase.querySelector('.modal-body');
			console.log(modalBody);

			modalBody.querySelector(
				'.projectIDDiv'
			).innerHTML = `<label for="projectID"> Project ID (in case of edit)</label>
				<input type="text" name="projectID" id="projectID" class=" px-3 form-control-xs form-control-plaintext bg-light-blue projectID" value="${projectDetails.projectID}">`;

			modalBody.querySelector('.projectCode').value =
				projectDetails.projectCode;
			modalBody.querySelector('.projectName').value =
				projectDetails.projectName;
			modalBody.querySelector('.billingrate').value =
				projectDetails.billingRateID;
			modalBody.querySelector('.roundingOff').value =
				projectDetails.roundingoff;
			if (projectDetails.roundingoff !== 'no_rounding') {
				document.querySelector('.roundingInterval').classList.remove('d-none');
			} else {
				document.querySelector('.roundingInterval').classList.add('d-none');
			}
			console.log(projectDetails.roundingoff);
			modalBody.querySelector('.roundingIntervalOption').value =
				projectDetails.roundingInterval;
			modalBody.querySelector('.businessUnitID').value =
				projectDetails.businessUnitID;
			modalBody.querySelector('.projectValue').value =
				projectDetails.projectValue;

			$('#projectStart').datepicker('update', projectDetails.projectStart);

			$('#projectClose').datepicker('update', projectDetails.projectClose);

			if (projectDetails.orderDate) {
				modalBody.querySelector('#SaleWon').checked = true;
			}
		});
	}

	let editSales = document.querySelectorAll('.editSales');

	for (let edit of editSales) {
		edit.addEventListener('click', (e) => {
			e.preventDefault();
			e.stopPropagation();

			let saleID = edit.dataset.id;
			let saleArray = sales.filter((sale) => {
				return parseInt(sale.saleID) === parseInt(saleID);
			});
			let saleDetails = saleArray[0];

			const manageSalesCase = document.getElementById('manageSalesCase');
			const modal = new bootstrap.Modal(manageSalesCase);
			modal.show();

			let body = manageSalesCase.querySelector('.modal-body');

			body.querySelector(
				'.saleID'
			).innerHTML = `<input type="text" name="saleID" value="${saleDetails.saleID}" class="form-control form-control-sm form-control-plaintext bg-light-blue">`;

			manageSalesCase.querySelector('#caseName').value = saleDetails.caseName;
			manageSalesCase.querySelector('#clientID').value = saleDetails.clientID;
			manageSalesCase.querySelector('#contactPerson').value =
				saleDetails.contactPerson;
			manageSalesCase.querySelector('#businessUnit').value =
				saleDetails.businessUnitID;
			manageSalesCase.querySelector('#saleEstimate').value =
				saleDetails.saleEstimate;
			manageSalesCase.querySelector('#probability').value =
				saleDetails.probability;
			manageSalesCase.querySelector('#expectedCloseDate').value =
				saleDetails.expectedCloseDate;

			switch (saleDetails.saleStatus) {
				case 'lead':
					manageSalesCase.querySelector('#lead').checked = true;
					break;
				case 'opportunity':
					manageSalesCase.querySelector('#opportunity').checked = true;
					break;
				case 'proposal':
					manageSalesCase.querySelector('#proposal').checked = true;
					break;

				default:
					manageSalesCase.querySelector('#lead').checked;
					break;
			}

			manageSalesCase.querySelector('#leadSource').value =
				saleDetails.leadSourceID;
			manageSalesCase.querySelector('#caseName').value = saleDetails.caseName;
		});
	}

	// let editProjects = document.querySelectorAll(".editProject");

	// for(let editPro of editProjects) {
	// 	editPro.addEventListener("click", (e)=> {
	// 		e.preventDefault();
	// 		e.stopPropagation();
	// 		let projectID= editPro.dataset.id;
	// 		console.log(editPro);
	// 		console.log(projectID);

	// 		let projectArray = projects.filter(project=> {
	// 				return  parseInt(project.projectID) === parseInt(projectID);
	// 			});

	// 		console.log(projectArray[0]);
	// 		let projectDetails = projectArray[0];

	// 		const manageProjectCase = document.querySelector('#manageProjectCase');
	// 		projectModal = new bootstrap.Modal(manageProjectCase);
	// 		projectModal.show();

	// 		let modalBody = manageProjectCase.querySelector(".modal-body");
	// 		console.log(modalBody);

	// 		modalBody.querySelector(".projectIDDiv").innerHTML= `<label for="projectID"> Project ID (in case of edit)</label>
	// 			<input type="text" name="projectID" id="projectID" class=" px-3 form-control-xs form-control-plaintext bg-light-blue projectID" value="${projectDetails.projectID}">`;

	// 			modalBody.querySelector(".projectCode").value= projectDetails.projectCode;
	// 			modalBody.querySelector(".projectName").value= projectDetails.projectName;
	// 			modalBody.querySelector(".billingrate").value= projectDetails.projectBillableRate;
	// 			modalBody.querySelector(".roundingOff").value= projectDetails.roundingoff;
	// 			if (projectDetails.roundingoff !=="no_rounding" ) {
	// 				document.querySelector('.roundingInterval').classList.remove("d-none");
	// 			} else {
	// 				document.querySelector('.roundingInterval').classList.add("d-none");
	// 			}
	// 			console.log(projectDetails.roundingoff);
	// 			modalBody.querySelector(".roundingIntervalOPtion").value= projectDetails.roundingInterval;
	// 			modalBody.querySelector(".businessUnitID").value= projectDetails.businessUnitID;
	// 			modalBody.querySelector(".projectValue").value= projectDetails.projectValue;

	// 	});
	// }
});
</script>
