<script>

    let editOrganisation = document.querySelector('.editOrganisation');
    let organisationForm = document.getElementById('organisationDetailsForm');
    editOrganisation.addEventListener('click', (e) => {
        e.preventDefault();
		if(editOrganisation.classList.contains('active')) {
            // console.log('Edit organisations contains class active');
			editOrganisation.classList.remove('active');
			organisationForm.querySelectorAll('input[type=text] , select').forEach(input => {
				// console.log(input);
				input.setAttribute('readonly', true);
				input.classList.remove("form-control-sm");
				input.classList.remove("form-control");
				input.classList.add("form-control-plaintext");
			});
			document.querySelector('.updateDetails').classList.add('d-none');
		} else {
			editOrganisation.classList.add('active');
            // console.log('Edit Organisation does not contain active');
            let inputElements = organisationForm.querySelectorAll('input[type=text] , select');
            // console.log(inputElements)
			inputElements.forEach(input => {
                
				// console.log(input);
				input.removeAttribute('readonly');
				input.classList.remove("form-control-plaintext");
				input.classList.add("form-control-sm");
				input.classList.add("form-control");
			});
			document.querySelector('.updateDetails').classList.remove('d-none');
		}
        
    });
    // let form = document.getElementById('organisationForm');
    
    // form.querySelectorAll('input').forEach(input => {
    //     console.log(input);
    //     // input.addEventListener('input', () => {
    //     //     form.querySelector('.btn').disabled = false;
    //     // });
    // });

</script>