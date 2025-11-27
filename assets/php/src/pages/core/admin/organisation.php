<?php 
if(isset($state) && $state=="home") {?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Edit Organisation Handler
            let editOrganisation = document.querySelector('.editOrganisation');
            let organisationForm = document.getElementById('organisationDetailsForm');
            if (editOrganisation) {
                editOrganisation.addEventListener('click', (e) => {
                    e.preventDefault();
                    if(editOrganisation.classList.contains('active')) {
                        editOrganisation.classList.remove('active');
                        organisationForm.querySelectorAll('input[type=text] , select').forEach(input => {
                            input.setAttribute('readonly', true);
                            input.classList.remove("form-control-sm");
                            input.classList.remove("form-control");
                            input.classList.add("form-control-plaintext");
                        });
                        document.querySelector('.updateDetails').classList.add('d-none');
                    } else {
                        editOrganisation.classList.add('active');
                        let inputElements = organisationForm.querySelectorAll('input[type=text] , select');
                        inputElements.forEach(input => {
                            input.removeAttribute('readonly');
                            input.classList.remove("form-control-plaintext");
                            input.classList.add("form-control-sm");
                            input.classList.add("form-control");
                        });
                        document.querySelector('.updateDetails').classList.remove('d-none');
                    }            
                });
            }

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

            // Handle edit entity buttons
            document.querySelectorAll('.editEntity').forEach(button => {
                button.addEventListener('click', function() {
                    const form = document.getElementById('entityForm');
                    if (!form) return;

                    const data = this.dataset;
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

                    // Fill form inputs
                    for (const [fieldName, dataAttribute] of Object.entries(fieldMappings)) {
                        const input = form.querySelector(`[name="${fieldName}"]`);
                        if (input) {
                            input.value = data[dataAttribute] || '';
                        }
                    }

                    // Handle tinyMCE editor
                    const editor = tinymce.get('entityDescription');
                    if (editor) {
                        setTimeout(() => {
                            editor.setContent(data.entityDescription || '');
                        }, 100);
                    }

                    // Handle select elements
                    const selects = ['entityTypeID', 'entityParentID', 'industrySectorID', 'entityCountry'];
                    selects.forEach(selectName => {
                        const select = form.querySelector(`[name="${selectName}"]`);
                        if (select && data[fieldMappings[selectName]]) {
                            select.value = data[fieldMappings[selectName]];
                        }
                    });
                });
            });

            // Handle delete entity buttons
            document.querySelectorAll('.deleteEntity').forEach(button => {
                button.addEventListener('click', function() {
                    const entityId = this.getAttribute('data-id');
                    const entityName = this.getAttribute('data-entity-name');
                    
                    const entityNameSpan = document.getElementById('entityNameToDelete');
                    const entityIdInput = document.querySelector('#deleteEntityModal input[name="entityID"]');
                    
                    if (entityNameSpan) entityNameSpan.textContent = entityName;
                    if (entityIdInput) entityIdInput.value = entityId;
                });
            });
        });
    </script>
    <?php
    if(isset($entityDetails) && $entityDetails) {?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize tinyMCE
                tinymce.init({
                        selector: 'textarea[name="unitDescription"]',
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
                });
        </script>
        <?php 

    }
} elseif($state=='users') {?>
  <!-- Date & Time Picker JS -->
   <h4>Date load</h4>
 
    <!-- Date & Time Picker JS -->
    <script src="<?= "{$base}assets/libs/flatpickr/flatpickr.min.js"?>"></script>
    <script src="<?= "{$base}assets/js/date&time_pickers.js"?>"></script>


<?php
} 
 
?>