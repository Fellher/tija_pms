  <!-- Tom Select JS -->
  <script src="<?= $base ?>assets/libs/tom-select/js/tom-select.complete.min.js"></script>
     <!-- <script src="<?= $base ?>assets/js/tom-select.js"></script> -->

     <script>
   console.log(clientContacts);
        
document.addEventListener("DOMContentLoaded", function() {
      // Initialize Tom Select for the client select element
      const clientSelect = document.querySelectorAll('.clientID');
    //   check for the current form with the clientID class
      if (clientSelect.length === 0) {
          console.warn('No clientID select elements found.');
          return;
      }
      // Initialize Tom Select for each client select element
    

    clientSelect.forEach(select => {
        new TomSelect(select, {
            create: true,
            sortField: {
                field: "text",
                direction: "asc"
            },
            onChange: function(value) {
                console.log(clientContacts);
            // get the form for the current client select
                const form = select.closest('form');
                console.log(form);
                console.log(`Client changed to: ${value}`);
                // Trigger the change event to update contact person options
                const event = new Event('change');
                select.dispatchEvent(event);

                console.log(`the selected value is  ${value}`);
                // populate clientcontacts based on the selected client
                const clientContactID = form.querySelector('#clientContactID');
                if(clientContactID) {
                    console.log(clientContactID);
                    // Clear previous contacts
                    clientContactID.innerHTML = '';
                    // Reset to default option
                    clientContactID.innerHTML = '<option value="">Select Contact</option>';
                    const clientContactArr = [];
                    // If a client is selected, fetch contacts for that client
                    if (value !== 'newClient') {
                        clientContacts.forEach(contact => {
                            // Check if the contact belongs to the selected client
                            console.log(contact.clientID, value);

                            if (contact.clientID == value) {

                                clientContactID.innerHTML += `<option value="${contact.contactID}">${contact.contactName}</option>`;
                            }
                        });
                        // If no contacts found, show a message
                        if (clientContactID.options.length === 1) {
                            clientContactID.innerHTML += '<option value="" disabled>No contacts available for this client</option>';
                        }
                    // add the newContact option
                    clientContactID.innerHTML += '<option value="newContact">New Contact</option>';
                    } else {
                        // If 'newClient' is selected, show the new client input
                        const newClientDiv = form.querySelector('.newClientDiv');
                        if (newClientDiv) {
                            newClientDiv.classList.remove('d-none');
                        }
                        // Add a new option for 'newContact'
                        clientContactID.innerHTML += '<option value="newContact">New Contact</option>'; 
                    } 

                }
            },
            onOptionAdd : function(value, item) {
                const form = select.closest('form');
                console.log(form);
                // log the current value of the select element
                // Check if the value is 'newClient' and handle it accordingly
                    console.log(`New client added: ${value}`);
                    // Show the new client input if 'newClient' is selected
                    const newClient = form.querySelector('.newClientDiv');
                    newClient.classList.remove('d-none');
                    const newClientNote = document.createElement('input');
                    newClientNote.type = 'text';
                    newClientNote.name = 'newClientNote';
                    newClientNote.classList.add('form-control', 'form-control-sm', 'form-control-plaintext', 'bg-white', 'px-2', 'mb-3', 'd-none');
                    newClientNote.placeholder = 'Input new client name';
                    newClientNote.value = 'newClient'; 
                    newClient.appendChild(newClientNote);
                
                //   const newClient = document.querySelector('#newClient');
                const  clientName = form.querySelector('#clientName');
                // check if clientName exists
                if (clientName) {
                    clientName.value = value; // Set the value of the input to the new client name
                }

                  

                //   if (value === 'newClient') {
                //       newClient.classList.remove('d-none');
                //   } else {
                //       newClient.classList.add('d-none');
                //   }
            }
        });
    });

    const businessDevForm = document.querySelectorAll('.businessDevForm');
    console.log(businessDevForm);

    const clientContactID = document.querySelectorAll('select[name="clientContactID"]');
    console.log(clientContactID);
    clientContactID.forEach(select => {
        console.log(select);
        select.addEventListener('change', function() {
            console.log(`Client Contact Person changed to: ${this.value}`);
            // Show the new contact person input if 'newContact' is selected
            const newContactPersonDiv = document.querySelector('.new_contact');
            if (this.value === 'newContact') {
                newContactPersonDiv.classList.remove('d-none');
            } else {
                newContactPersonDiv.classList.add('d-none');
            }
        });
        // Initialize Tom Select for each client contact select element
        // new TomSelect(select, {
        //     create: true,
        //     sortField: {
        //         field: "text",
        //         direction: "asc"
        //     },
        //     onChange: function(value) {
        //         console.log(`Client Contact Person changed to: ${value}`);
        //         // Show the new contact person input if 'newContact' is selected
        //         const newContactPersonDiv = document.querySelector('.new_contact');
        //         if (value === 'newContact') {
        //             newContactPersonDiv.classList.remove('d-none');
        //         } else {
        //             newContactPersonDiv.classList.add('d-none');
        //         }
        //     }
        // });
    })

     
    // Initialize Tom Select for the business unit select element
    const businessUnitSelect = document.querySelectorAll('.businessUnitID');
    businessUnitSelect.forEach(select => {
        new TomSelect(select, {
            create: true,
            sortField: {
                field: "text",
                direction: "asc"
            },
            onChange: function(value) {
                console.log(`Business Unit changed to: ${value}`);
                // Show the new business unit input if 'newUnit' is selected
                const newBusinessUnit = document.querySelector('#newBusinessUnit');
                if (value === 'newUnit') {
                    newBusinessUnit.classList.remove('d-none');
                } else {
                    newBusinessUnit.classList.add('d-none');
                }
            }
        });
    });
     
    // Initialize Tom Select for the owner select element
    const salesPersonSelect = document.querySelector('#salesPersonID');
    if (salesPersonSelect) {
        new TomSelect(salesPersonSelect, {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });
    }
    // Initialize Tom Select for the country select element
    const countrySelect = document.querySelectorAll('.countryID');
    countrySelect.forEach(select => {
        new TomSelect(select, {
            create: true,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });
    });

    // Initialize Tom Select for the contact person select element
    const contactPersonSelect = document.querySelectorAll('.salesCaseContactID');
    contactPersonSelect.forEach(select => {
        new TomSelect(select, {
            create: true,
            sortField: {
                field: "text",
                direction: "asc"
            },
            onChange: function(value) {
                console.log(`Sales Contact Person changed to: ${value}`);
                // Trigger the change event to update contact person options
                const event = new Event('change');
                select.dispatchEvent(event);
            },
            onOptionAdd: function(value, item) {
                const form = select.closest('form');
                console.log(form);
                // log the current value of the select element
                console.log(`New contact person added: ${value}`);
                // Show the new contact person input if 'newContact' is selected
                const newContactPerson = form.querySelector('.newContactPersonDiv');
                newContactPerson.classList.remove('d-none');
                const newContactNote = document.createElement('input');
                newContactNote.type = 'text';
                newContactNote.name = 'newSalesContactName';
                newContactNote.classList.add('form-control', 'form-control-sm', 'form-control-plaintext', 'bg-white', 'px-2', 'mb-3', 'd-none');
                newContactNote.placeholder = 'Input new contact person name';
                newContactNote.value = 'newContact'; 
                newContactPerson.appendChild(newContactNote);

                // update the ClientContactName contactName
                const contactName = form.querySelector('#contactName');
                // check if contactName exists
                if (contactName) {
                    contactName.value = value; // Set the value of the input to the new contact person name
                }
            }
        });
    });

});
     </script>