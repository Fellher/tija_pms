<script>
   console.log("thie javascript is loaded");
   // Add this JavaScript code at the bottom of your file
document.addEventListener('DOMContentLoaded', function() {
    // Listen for modal show event
    document.querySelectorAll('.manageEntityOrganisation').forEach(button => {
        button.addEventListener('click', function() {
            // Get the organisation ID from the button's data attribute
            const organisationId = this.getAttribute('data-organisationId');
            
            // Find the modal input and set its value
            const orgDataIDInput = document.querySelector('#manageEntity input[name="orgDataID"]');
            if (orgDataIDInput) {
                orgDataIDInput.value = organisationId;
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tinyMCE
    tinymce.init({
        selector: 'textarea[name="entityDescription"]',
        height: 200,
        menubar: false,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | formatselect | ' +
            'bold italic backcolor | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist outdent indent | ' +
            'removeformat | help',
    });

    // Handle edit button clicks
    document.querySelectorAll('.editEntity').forEach(button => {
        button.addEventListener('click', function() {
            const form = document.getElementById('entityForm');
            if (!form) return;

            // Get all data attributes from the button
            const data = this.dataset;

            // Map form fields to their corresponding data attributes
            const fieldMappings = {
                'entityName': 'entityName',
                'entityTypeID': 'entityTypeId',
                'orgDataID': 'orgDataId',
                'entityParentID': 'entityParentId',
                'industrySectorID': 'industrySectorId',
                'registrationNumber': 'registrationNumber',
                'entityPIN': 'entityPin',
                'entityCity': 'entityCity',
                'entityCountry': 'entityCountry',
                'entityPhoneNumber': 'entityPhoneNumber',
                'entityEmail': 'entityEmail',
                'entityID': 'id'
            };

            // Fill regular form inputs
            for (const [fieldName, dataAttribute] of Object.entries(fieldMappings)) {
                
                const input = form.querySelector(`[name="${fieldName}"]`);
                console.log(input);
                if (input) {
                    input.value = data[dataAttribute] || '';
                }
            }
            // Fill the textarea with tinyMCE
            tinymce.init({
               selector: '#entityDescription'
            });
            

            // Handle tinyMCE editor
            const editor = tinymce.get('entityDescription'); // Make sure 'entityDescription' matches your textarea's ID
            if (editor) {
                // Wait for a brief moment to ensure tinyMCE is fully initialized
                setTimeout(() => {
                    editor.setContent(data.entityDescription || '');
                }, 100);
            }

            // If you have select elements that need special handling
            // (like setting selected options), handle them here
            const selects = ['entityTypeID', 'entityParentID', 'industrySectorID', 'entityCountry'];
            selects.forEach(selectName => {
                const select = form.querySelector(`[name="${selectName}"]`);
                if (select && data[fieldMappings[selectName]]) {
                    select.value = data[fieldMappings[selectName]];
                }
            });
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // Handle delete button clicks
    document.querySelectorAll('.deleteEntity').forEach(button => {
        button.addEventListener('click', function() {
            // Get entity details from data attributes
            const entityId = this.getAttribute('data-id');
            const entityName = this.getAttribute('data-entity-name');
            
            // Find the delete modal elements
            const entityNameSpan = document.getElementById('entityNameToDelete');
            const entityIdInput = document.querySelector('#deleteEntityModal input[name="entityID"]');
            
            // Set the values in the modal
            if (entityNameSpan) {
                entityNameSpan.textContent = entityName;
            }
            
            if (entityIdInput) {
                entityIdInput.value = entityId;
            }
        });
    });
});
</script>