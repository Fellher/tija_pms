
jQuery(document).ready( function(){
	const grp_option= document.getElementById('grp_option');						
	grp_option.addEventListener('change', getLabel);
	function getLabel(){
		let selectVal = grp_option.options[grp_option.selectedIndex].text;
		let selectVal2 = grp_option.options[grp_option.selectedIndex].parentElement.label;
		let target= document.getElementById('target');		

		target.innerHTML=  ` Select Project <span class="text-danger float-end nott t500 ms-4"> (<em> ${selectVal2} </em>)</span>`;

		let projectID = grp_option.value;
		let phaseList="";

		phaseList+=`<div class="form-group">
						<label for="phases" class="mb-0 nott t500"> Project Phase</label>
						<select name="phaseID" id="phases" class="form-control form-control-sm form-control-plaintext bg-light-blue phases">
							<option value="" > Select Task </option>`;
					phaseArray.forEach((element, index, array) => {
					// console.log(element);
				    if (element.projectID == projectID) {
				      	// console.log(array);
				      	// console.log(element);
				      	phaseList +=`<option value="${element.projectPhaseID}" >${element.projectPhaseName} </option> `;								      
				    }
			});	

			phaseList+=`</select>
					</div>`;

					console.log(phaseList);

			document.querySelector('.projectPhase').innerHTML = phaseList;

			let phases = document.getElementById('phases');

			phases.addEventListener('change', getTask);

			function getTask() {
				let phaseID= phases.value;
				console.log(phaseID);

				let taskList="";
				taskList+= `<div class="form-group col-sm">
				 				<label for="project" class="nott mb-0 t500 text-dark "> Task</label>
				 				<select class="form-control form-control-sm form-control-plaintext  bg-light-blue tasks"  name="projectTaskID" onchange="ShowDiv(this)">
				 					<option value="" > Select Task </option>`;

				taskArray.forEach((element, index, array) => {
					// console.log(element);
				    if (element.projectPhaseID == phaseID) {
				      	console.log(array);
				      	console.log(element);
				      	taskList +=`<option value="${element.projectTaskID}" >${element.projectTaskName} </option> `;								      
				    }
				});	
				taskList +=`</select>
					 		</div>`;					
				document.querySelector('.projectTasks').innerHTML = taskList;	
			}
			
	}



	if (userExpenses) {

		let expenses = document.querySelectorAll(".userExpense");

		console.log(expenses);

		for(const expense of expenses) {
				console.log(expense);
				expense.addEventListener("click", (e)=>{
				e.preventDefault();
				e.stopPropagation();
				expenseID = expense.dataset.id;
				console.log(expenseID);
			
				let expenseArr = userExpenses.filter(expenseObj=> {
					return parseInt(expenseObj.expenseID) === parseInt(expenseID);
				});
				expenseDtails = expenseArr[0];
				console.log(expenseDtails);

				const manageExpense = document.querySelector("#addExpense");
				exportModal = new bootstrap.Modal(manageExpense);
				exportModal.show();
				let modalBody = manageExpense.querySelector(".modal-body");
				console.log(modalBody);
				let div = document.createElement("div");
				// div.classList.add("form-group");

				div.innerHTML = `<input type="hidden" name="expenseID" id="expenseID" class="form-control form-control-xs" value="${expenseID}">`;
				modalBody.appendChild(div);
				modalBody.querySelector(".projectID").value= expenseDtails.projectID;
				modalBody.querySelector(".expenseTypeID").value= expenseDtails.expenseTypeID;
				modalBody.querySelector(".expenseDate").value= expenseDtails.expenseDate;
				modalBody.querySelector(".expenseAmount").value= expenseDtails.expenseAmount;
				

				tinymce.init({
					selector: '.expenseDescription',
					height:150,			 
					menubar: false,
					  setup: (editor) => {
					    editor.on('init', (e) => {
					      console.log('The Editor has initialized.');
					      editor.setContent(`${expenseDtails.expenseDescription}`);
					    });
					}
				});

			});
		}

	}
});



