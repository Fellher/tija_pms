<?php
$clientRelationships = Client::client_relationships(array('clientID'=>$clientDetails->clientID), false, $DBConn);
$clientRelationshipTypes = $config['clientRelationshipTypes'];
?>

<!-- Relationship Matrix Display -->
<?php if($clientRelationships && is_array($clientRelationships) && count($clientRelationships) > 0): ?>
   <div class="alert alert-info mb-4">
      <div class="d-flex align-items-center">
         <i class="ri-information-line fs-20 me-3"></i>
         <div>
            <strong>Escalation Matrix</strong>
            <p class="mb-0 small">Team members are organized by relationship level and role. Edit any relationship by clicking the pencil icon.</p>
         </div>
      </div>
   </div>

   <div class="row g-3">
      <?php
      $relationshipType = array();
      foreach($clientRelationships as $relationship){
         $relationshipType = array_filter($config['clientRelationshipTypes'], function($type) use ($relationship) {
             return $type->key == $relationship->clientRelationshipType;
         });
         $relationshipType = reset($relationshipType);

         if($relationship->employeeID) {
            $initialsArr = Core::get_user_name_initials($relationship->employeeID, $DBConn);
         }

         // Determine level badge color
         $levelColor = 'primary';
         if(isset($relationshipType->level)) {
            if($relationshipType->level <= 2) $levelColor = 'danger';
            elseif($relationshipType->level == 3) $levelColor = 'warning';
            elseif($relationshipType->level == 4) $levelColor = 'info';
            else $levelColor = 'secondary';
         }
      ?>
         <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100 relationship-card">
               <div class="card-body">
                  <div class="d-flex align-items-center mb-3">
                     <div class="avatar avatar-lg rounded-circle bg-<?= $levelColor ?> text-white me-3">
                        <?php
                        if(isset($initialsArr) && $initialsArr) {
                           echo $initialsArr['initials'];
                        } else {
                           echo Utility::generate_initials($relationship->employeeName ?? 'NA');
                        }?>
                     </div>
                     <div class="flex-grow-1">
                        <h6 class="mb-0 fw-semibold">
                           <?php
                           if(isset($initialsArr) && $initialsArr) {
                              echo htmlspecialchars($initialsArr['name']);
                           } else {
                              echo htmlspecialchars($relationship->employeeName ?? 'N/A');
                           }?>
                        </h6>
                        <span class="badge bg-<?= $levelColor ?>-transparent text-<?= $levelColor ?> small">
                           <?= htmlspecialchars($relationshipType->value ?? $relationship->clientRelationshipType) ?>
                        </span>
                     </div>
                  </div>

                  <?php if(isset($relationshipType->level)): ?>
                     <div class="d-flex align-items-center justify-content-between mb-2">
                        <small class="text-muted">
                           <i class="ri-bar-chart-line me-1"></i>Level <?= $relationshipType->level ?>
                        </small>
                        <?php if(isset($relationship->employeeEmail) && $relationship->employeeEmail): ?>
                           <a href="mailto:<?= $relationship->employeeEmail ?>" class="text-decoration-none small">
                              <i class="ri-mail-line"></i>
                           </a>
                        <?php endif; ?>
                     </div>
                  <?php endif; ?>

                  <div class="border-top pt-3 mt-3 d-flex gap-2">
                     <button type="button"
                             class="btn btn-sm btn-outline-primary flex-grow-1 edit-client-relationship"
                             data-bs-toggle="modal"
                             data-bs-target="#manageClientRelationshipModal"
                             data-client-relationship-id="<?= $relationship->clientRelationshipID ?>"
                             data-client-id="<?= $relationship->clientID ?>"
                             data-employee-id="<?= $relationship->employeeID ?>"
                             data-client-relationship-type="<?= $relationship->clientRelationshipType ?>">
                        <i class="ri-pencil-line me-1"></i>Edit
                     </button>
                     <a href="<?= "{$base}php/scripts/clients/manage_client_relationship.php?clientRelationshipID={$relationship->clientRelationshipID}&action=delete" ?>"
                        class="btn btn-sm btn-outline-danger delete-client-relationship"
                        title="Delete">
                        <i class="ri-delete-bin-line"></i>
                     </a>
                  </div>
               </div>
            </div>
         </div>
      <?php
      }
      ?>
   </div>
<?php else: ?>
   <!-- Empty State -->
   <div class="text-center py-5">
      <div class="empty-state-icon mb-3">
         <i class="ri-team-line fs-48 text-muted"></i>
      </div>
      <h5 class="mb-2">No Relationships Assigned</h5>
      <p class="text-muted mb-4">Set up your escalation matrix by assigning team members to this client.</p>
      <button type="button"
              class="btn btn-primary add-client-relationship"
              data-bs-toggle="modal"
              data-bs-target="#manageClientRelationshipModal"
              data-client-id="<?= $clientDetails->clientID ?>"
              data-client-name="<?= $clientDetails->clientName ?>">
         <i class="ri-user-add-line me-1"></i>Add First Relationship
      </button>
   </div>
<?php endif; ?>

<!-- Relationship Management JavaScript -->
<script>
(function() {
   'use strict';

   const clientRelationshipTypesArr = <?= json_encode($config['clientRelationshipTypes']) ?>;
   const allEmployees = <?= json_encode($allEmployees) ?>;

   // Debug: Check if SweetAlert is loaded
   console.log('SweetAlert2 loaded:', typeof Swal !== 'undefined');

   // Handle Add Relationship Button
   document.addEventListener('click', function(e) {
      const addBtn = e.target.closest('.add-client-relationship');
      if (addBtn) {
         const form = document.getElementById('manage_client_relationship_form');
         if (!form) return;

         // Clear form
         form.querySelectorAll('input, select').forEach(input => {
            input.value = '';
            if (input.type === 'checkbox' || input.type === 'radio') {
               input.checked = false;
            }
         });

         // Set action and client ID
         const actionInput = form.querySelector('#action') || form.querySelector('[name="action"]');
         if(actionInput) actionInput.value = 'add';

         const clientIDInput = form.querySelector('#clientID') || form.querySelector('[name="clientID"]');
         if(clientIDInput) clientIDInput.value = addBtn.dataset.clientId;
      }

      // Handle Edit Relationship Button
      const editBtn = e.target.closest('.edit-client-relationship');
      if (editBtn) {
         const form = document.getElementById('manage_client_relationship_form');
         if (!form) return;

         const data = editBtn.dataset;

         // Clear form
         form.querySelectorAll('input, select').forEach(input => {
            input.value = '';
            if (input.type === 'checkbox' || input.type === 'radio') {
               input.checked = false;
            }
         });

         // Set action
         const actionInput = form.querySelector('#action') || form.querySelector('[name="action"]');
         if(actionInput) actionInput.value = 'edit';

         // Field mappings
         const fieldMappings = {
            'clientRelationshipID': 'clientRelationshipId',
            'clientID': 'clientId',
            'employeeID': 'employeeId',
            'clientRelationshipType': 'clientRelationshipType',
         };

         // Populate fields
         for (const [fieldName, dataAttribute] of Object.entries(fieldMappings)) {
            const input = form.querySelector(`[name="${fieldName}"]`);

            if (fieldName === 'clientRelationshipType' && input) {
               // Populate relationship type select
               input.innerHTML = '<option value="">Select Relationship Type</option>';
               clientRelationshipTypesArr.forEach(type => {
                  const option = document.createElement('option');
                  option.value = type.key;
                  option.textContent = type.value;
                  if (type.key === data[dataAttribute]) {
                     option.selected = true;
                  }
                  input.appendChild(option);
               });

               // Trigger change to filter employees
               if(data[dataAttribute]) {
                  setTimeout(() => {
                     input.dispatchEvent(new Event('change'));
                     // Set employee after filtering
                     const employeeSelect = form.querySelector('[name="employeeID"]');
                     if(employeeSelect && data.employeeId) {
                        employeeSelect.value = data.employeeId;
                     }
                  }, 100);
               }
            } else if (input) {
               input.value = data[dataAttribute] || '';
            }
         }
      }

      // Handle Delete Relationship Button with SweetAlert confirmation
      const deleteBtn = e.target.closest('.delete-client-relationship');
      if (deleteBtn) {
         e.preventDefault();
         const href = deleteBtn.getAttribute('href');

         // Use SweetAlert2 if available
         if (typeof Swal !== 'undefined') {
            Swal.fire({
               title: 'Remove Relationship?',
               text: 'This will unlink the selected employee from this client. You can reassign them later if needed.',
               icon: 'warning',
               showCancelButton: true,
               confirmButtonText: 'Yes, remove',
               cancelButtonText: 'Cancel',
               reverseButtons: true,
               buttonsStyling: false,
               customClass: {
                  confirmButton: 'btn btn-danger me-2',
                  cancelButton: 'btn btn-outline-secondary'
               }
            }).then((result) => {
               if (result.isConfirmed) {
                  window.location.href = href;
               }
            });
         } else {
            // Fallback to native confirm if SweetAlert is not loaded
            if (window.confirm('Are you sure you want to remove this relationship?')) {
               window.location.href = href;
            }
         }
      }
   });

   // Filter employees based on relationship type level
   const relationshipTypeSelect = document.querySelector('[name="clientRelationshipType"]');
   if(relationshipTypeSelect) {
      relationshipTypeSelect.addEventListener('change', function() {
         const selectedKey = this.value;
         const selectedType = clientRelationshipTypesArr.find(type => type.key === selectedKey);
         const employeeSelect = document.querySelector('[name="employeeID"]');

         if(!employeeSelect || !selectedType) return;

         const level = parseInt(selectedType.level);
         let filteredEmployees = [];

         // Filter employees based on level
         if (level === 1 || level === 2) {
            filteredEmployees = allEmployees.filter(emp =>
               emp.jobTitle === 'Partner' || emp.jobTitle === 'Director'
            );
         } else if (level === 3) {
            filteredEmployees = allEmployees.filter(emp =>
               emp.jobTitle === 'Manager' || emp.jobTitle === 'Senior Manager' || emp.jobTitle === 'Director'
            );
         } else if (level === 4) {
            filteredEmployees = allEmployees.filter(emp =>
               emp.jobTitle === 'Associate' || emp.jobTitle === 'Senior Associate'
            );
         } else if (level === 5) {
            filteredEmployees = allEmployees.filter(emp =>
               emp.jobTitle === 'Intern' || emp.jobTitle === 'Junior Associate'
            );
         } else {
            filteredEmployees = allEmployees; // Level 6 - all employees
         }

         console.log('Filtered employees for level ' + level + ':', filteredEmployees.length);

         // FALLBACK: If no employees match the filter, show all employees
         if(filteredEmployees.length === 0) {
            console.warn('No employees found for level ' + level + '. Showing all employees as fallback.');
            filteredEmployees = allEmployees;

            // Show warning message to user
            const parentDiv = employeeSelect.parentElement;
            if(parentDiv) {
               // Remove existing warning
               const existingWarning = parentDiv.querySelector('.filter-warning');
               if(existingWarning) existingWarning.remove();

               // Add new warning
               const warning = document.createElement('small');
               warning.className = 'text-warning d-block mt-1 filter-warning';
               warning.innerHTML = '<i class="ri-alert-line me-1"></i>No employees found with required job title for this level. Showing all employees.';
               parentDiv.appendChild(warning);
            }
         } else {
            // Remove warning if filter produced results
            const existingWarning = document.querySelector('.filter-warning');
            if(existingWarning) existingWarning.remove();
         }

         // Populate employee select
         employeeSelect.innerHTML = '<option value="">Select Employee</option>';
         filteredEmployees.forEach(employee => {
            const option = document.createElement('option');
            option.value = employee.ID;
            option.textContent = employee.employeeNameWithInitials || `${employee.FirstName} ${employee.Surname}`;
            employeeSelect.appendChild(option);
         });
      });
   }
})();
</script>

<style>
.relationship-card {
   transition: all 0.3s ease;
}

.relationship-card:hover {
   transform: translateY(-4px);
   box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15) !important;
}

.empty-state-icon {
   width: 80px;
   height: 80px;
   margin: 0 auto;
   background: #f8f9fa;
   border-radius: 50%;
   display: flex;
   align-items: center;
   justify-content: center;
}

.filter-warning {
   animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
   from {
      opacity: 0;
      transform: translateY(-5px);
   }
   to {
      opacity: 1;
      transform: translateY(0);
   }
}
</style>
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