<?php
$clientRelationships = Client::client_relationships(array('clientID'=>$clientDetails->clientID), false, $DBConn);
$clientRelationshipTypes = $config['clientRelationshipTypes'];
 ?>
<div class="card custom-card">
   <div class="card-header d-flex justify-content-between">
      <h3 class="card-title t300 font-20 d-flex align-items-center">
         Client Relationships
         <button type="button"
                 class="btn btn-sm btn-link text-primary p-0 ms-2"
                 data-bs-toggle="modal"
                 data-bs-target="#clientRelationshipsDocModal"
                 title="View Client Relationships documentation">
            <i class="ri-information-line fs-18"></i>
         </button>
      </h3>
      <a href="#manageClientRelationship"
         class="btn btn-primary btn-sm rounded-circle add-client-relationship fs-22"
         data-bs-toggle="modal"
         role="button"
         aria-expanded="false"
         aria-controls="manageClientRelationship"
         data-client-id="<?= $clientDetails->clientID ?>"
         data-client-name="<?= $clientDetails->clientName ?>"
         data-client-relationship-id=""
         data-employee-id=""
         data-client-relationship-type=""
         data-action="add"
         >
         <i class="ti ti-user-plus"></i>
      </a>
   </div>
   <div class="card-body">
      <div class="row">
         <div class="col-md-12 row">
         <?php
         $relationshipType=array();
         if($clientRelationships){
            foreach($clientRelationships as $relationship){
               // var_dump($relationship);
               // var_dump($relationship);
               $relationshipType = array_filter($config['clientRelationshipTypes'], function($type) use ($relationship) {
                   return $type->key == $relationship->clientRelationshipType;
               });
               // var_dump($relationshipType);
               $relationshipType = reset($relationshipType);
               // var_dump($relationshipType);
               // remove the $relationshipType from $clientRelationshipTypes
               $clientRelationshipTypes = array_filter($clientRelationshipTypes, function($type) use ($relationshipType) {
                  return $type->key != $relationshipType->key;
               });
               if($relationship->employeeID) {
                  $initialsArr = Core::get_user_name_initials($relationship->employeeID, $DBConn);
               } ?>
               <div class="col-md-3">
                  <div class="ms-md-1 ms-0 row">
                     <div class="d-flex align-items-center justify-content-between border rounded-3 p-2 border-primary">
                        <div class="d-flex align-items-center">
                           <div class="rounded-circle d-flex justify-content-center align-items-center " style="width: 30px; height: 30px; background-color: #007bff;">
                              <span class="text-white">
                                 <?php
                                 if($initialsArr) {
                                    echo $initialsArr['initials'];
                                 } else {
                                    $initials = Utility::generate_initials($relationship->employeeName);
                                    echo $initials;
                                 }?>
                              </span>
                           </div>
                           <div class="ms-2 me-4 " >
                              <span class="text-primary d-block">
                                 <?= $relationship->clientRelationshipType ? $relationshipType->value : $relationship->clientRelationshipType ?>
                              </span>
                              <?php
                              if($initialsArr) {
                                 echo $initialsArr['name'];
                              } else {
                                 echo $relationship->employeeName;
                              }?>
                           </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-end d-block">
                           <a href="#manageClientRelationship"
                              class="float-end edit-client-relationship fs-22"
                              data-bs-toggle="modal"
                              role="button"
                              aria-expanded="false"
                              aria-controls="manageClientRelationship"
                              data-client-relationship-id="<?= $relationship->clientRelationshipID ?>"
                              data-client-id="<?= $relationship->clientID ?>"
                              data-employee-id="<?= $relationship->employeeID ?>"
                              data-client-relationship-type="<?= $relationship->clientRelationshipType ?>"
                              >
                              <i class="ri-pencil-line"></i>
                           </a>
                        </div>
                     </div>
                  </div>
               </div>
            <?php
            }
         }?>
         </div>
      </div>
   </div>
</div>
<?php
echo Utility::form_modal_header("manageClientRelationship", "clients/manage_client_relationship.php", "Manage Client Relationship", array('modal-lg', 'modal-dialog-centered'), $base);
include "includes/scripts/clients/modals/manage_client_relationship.php";
echo Utility::form_modal_footer('Save Client Relationship', 'saveClientRelationship',  ' btn btn-success btn-sm', true);
?>
<script>
   // Event delegation for client relationship management
   document.addEventListener('DOMContentLoaded', function() {
      const clientRelationshipTypesArr = <?= json_encode($config['clientRelationshipTypes']) ?>;
      console.log(clientRelationshipTypesArr);
      console.log('Page loaded successfully');

      // Use event delegation for add relationship buttons
      if (typeof EventDelegation !== 'undefined') {
         EventDelegation.on('.relationship', 'click', function(e, target) {
            const form = document.getElementById('manage_client_relationship_form');
            if (!form) return;
            // clear form inputs
            form.querySelectorAll('input, select').forEach(input => {
               input.value = '';
               if (input.type === 'checkbox' || input.type === 'radio') {
                  input.checked = false;
               }
            });
            // Set the action for the form
            form.querySelector('#action').value = 'add';
            // Set the client ID and name in the form
            const clientId = target.dataset.clientId;
            const clientName = target.dataset.clientName;
            form.querySelector('#clientID').value = clientId;
         }, {}, document);

         // Event delegation for edit relationship buttons
         EventDelegation.on('.edit-client-relationship', 'click', function(e, target) {
            const form = document.getElementById('manage_client_relationship_form');
            if (!form) return;
            const data = target.dataset;
            // clear form inputs
            form.querySelectorAll('input, select').forEach(input => {
               input.value = '';
               if (input.type === 'checkbox' || input.type === 'radio') {
                  input.checked = false;
               }
            });
            console.log(data);
            // Set the action for the form
            form.querySelector('#action').value = 'edit';

            const fieldMappings = {
                  'clientRelationshipID': 'clientRelationshipId',
                  'clientID': 'clientId',
                  'employeeID': 'employeeId',
                  'clientRelationshipType': 'clientRelationshipType',
            };


            // Fill regular form inputs
            for (const [fieldName, dataAttribute] of Object.entries(fieldMappings)) {
               console.log(`fieldName: ${fieldName}, dataAttribute: ${dataAttribute}` );
                  const input = form.querySelector(`[name="${fieldName}"]`);
                console.log(input);
                console.log(`data[dataAttribute]: ${data[dataAttribute]}`);
               if (fieldName === 'clientRelationshipType') {
                     // Populate the clientRelationshipType select with all options
                     const relationshipType = clientRelationshipTypesArr.find(type => type.key === data[dataAttribute]);
                  console.log(`object found: ${relationshipType}`);
                  const select = form.querySelector(`[name="${fieldName}"]`);
                  select.options.length = 0; // Clear existing options
                  select.appendChild(new Option('Select Client Relationship Type', ''));
                  clientRelationshipTypesArr.forEach(type => {
                     let option = document.createElement('option');
                     option.text = type.value;
                     option.value = type.key;
                     if (type.key === data[dataAttribute]) {
                        option.selected = true;
                     }
                     select.appendChild(option);
                  });
                  console.log(relationshipType);
                  // Populate the clientRelationshipType select options based on the selected type's level
                  const selectedType = clientRelationshipTypesArr.find(item => item.key === data[dataAttribute]);
                  console.log(selectedType);
                  if (selectedType) {
                     const level = selectedType.level;
                     console.log(`Selected type level: ${level}`);
                     let filteredEmployees = [];
                     if (level == 1 || level == 2) {
                        filteredEmployees = allEmployees.filter(employee => employee.jobTitle === 'Partner' || employee.jobTitle === 'Director');
                     } else if (level == 3) {
                        filteredEmployees = allEmployees.filter(employee => employee.jobTitle === 'Manager' || employee.jobTitle === 'Senior Manager' || employee.jobTitle === 'Director');
                     } else if (level == 4) {
                        filteredEmployees = allEmployees.filter(employee => employee.jobTitle === 'Associate' || employee.jobTitle === 'Senior Associate');
                     } else {
                        filteredEmployees = allEmployees; // Default to all employees if no specific level
                     }
                     console.log(filteredEmployees);
                     // Populate the employeeID select with filtered employees
                     const employeeSelect = form.querySelector('[name="employeeID"]');
                     employeeSelect.options.length = 0; // Clear existing options
                     employeeSelect.appendChild(new Option('Select Employee', ''));
                     filteredEmployees.forEach(employee => {
                        let option = document.createElement('option');
                        option.text = employee.employeeNameWithInitials;
                        option.value = employee.ID;
                        employeeSelect.appendChild(option);
                     });
                  }
               }
               if (input) {
                  input.value = data[dataAttribute] || '';
               }
            }
         }, {}, document);
      } else {
         // Fallback: Use document-level event delegation if EventDelegation is not available
         document.addEventListener('click', function(e) {
            const addBtn = e.target.closest('.relationship');
            if (addBtn) {
               const form = document.getElementById('manage_client_relationship_form');
               if (!form) return;
               form.querySelectorAll('input, select').forEach(input => {
                  input.value = '';
                  if (input.type === 'checkbox' || input.type === 'radio') {
                     input.checked = false;
                  }
               });
               form.querySelector('#action').value = 'add';
               const clientId = addBtn.dataset.clientId;
               form.querySelector('#clientID').value = clientId;
            }

            const editBtn = e.target.closest('.edit-client-relationship');
            if (editBtn) {
               const form = document.getElementById('manage_client_relationship_form');
               if (!form) return;
               const data = editBtn.dataset;
               form.querySelectorAll('input, select').forEach(input => {
                  input.value = '';
                  if (input.type === 'checkbox' || input.type === 'radio') {
                     input.checked = false;
                  }
               });
               form.querySelector('#action').value = 'edit';
               // ... rest of edit logic would go here
            }
         });
      }
   });
</script>