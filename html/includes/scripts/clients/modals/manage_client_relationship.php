<div id="manage_client_relationship_form">
   <input type="hidden" name="clientRelationshipID" id="clientRelationshipID" value="">
   <input type="hidden" class="form-control form-control-sm" name="action" id="action" value="add">
   <h6 class="modal-title mb-3" id="manageClientRelationshipModalLabel"><?= isset($clientDetails) ? "<span class='text-primary me-2'>Client: </span> {$clientDetails->clientName}" : ""?> </h6>
   <div class="form-group my-2 d-none">
      <label for="clientID">Client</label>
      <select class="form-control-sm form-control-plaintext border-bottom bg-light-blue px-2 " id="clientID" name="clientID" readonly >
         <?= Form::populate_select_element_from_object($clients, 'clientID', 'clientName', (isset($clientID) && $clientID != '') ? $clientID : '', '', 'Select Client') ?>
      </select>
   </div>

   <div class="form-group my-2">
      <label for="clientRelationshipType">Client Relationship Type</label>
      <select class="form-control-sm form-control-plaintext border-bottom bg-light-blue px-2 clientRelationshipType " id="clientRelationshipType" name="clientRelationshipType">
      <?= Form::populate_select_element_from_object($clientRelationshipTypes, 'key', 'value', '', '', 'Select Client Relationship Type') ?>
      </select>
   </div>
   <div class="form-group my-2">
      <div class="d-flex justify-content-between align-items-center mb-1">
         <label for="employeeID" class="mb-0">Employee</label>
         <div class="form-check form-check-sm">
            <input class="form-check-input" type="checkbox" id="showAllEmployees" title="Show all employees regardless of level">
            <label class="form-check-label small text-muted" for="showAllEmployees">
               Show all
            </label>
         </div>
      </div>
      <select class="form-control-sm form-control-plaintext border-bottom bg-light-blue px-2" id="employeeID" name="employeeID">
         <?= Form::populate_select_element_from_object($allEmployees, 'ID', 'employeeNameWithInitials', '', '', 'Select Employee') ?>
      </select>
   </div>

</div>

<script>
   document.addEventListener('DOMContentLoaded', function() {
      console.log('DOMContentLoaded on manage_client_relationship_form');
      let config = <?= json_encode($config) ?>;

      let clientRelationshipType = document.getElementById('clientRelationshipType');
      let employeeID = document.getElementById('employeeID');

      function populateEmployeeOptions(selectElement, employees) {
         selectElement.options.length = 0; // Clear existing options
         selectElement.appendChild(new Option('Select Employee', ''));
         employees.forEach(employee => {
            let option = document.createElement('option');
            option.text = employee.employeeNameWithInitials;
            option.value = employee.ID;
            selectElement.appendChild(option);
         });
      }

      // Handle "Show All Employees" checkbox
      const showAllCheckbox = document.getElementById('showAllEmployees');
      if(showAllCheckbox) {
         showAllCheckbox.addEventListener('change', function() {
            if(this.checked) {
               // Show all employees
               populateEmployeeOptions(employeeID, allEmployees);
               // Remove filter warning
               const existingWarning = document.querySelector('.filter-warning');
               if(existingWarning) existingWarning.remove();

               // Show info that filter is bypassed
               const parentDiv = employeeID.parentElement;
               if(parentDiv) {
                  const existingInfo = parentDiv.querySelector('.filter-info');
                  if(existingInfo) existingInfo.remove();

                  const info = document.createElement('small');
                  info.className = 'text-info d-block mt-1 filter-info';
                  info.innerHTML = '<i class="ri-information-line me-1"></i>Level filter bypassed. Showing all employees.';
                  parentDiv.appendChild(info);
               }
            } else {
               // Re-apply filter
               const relationshipType = document.getElementById('clientRelationshipType');
               if(relationshipType && relationshipType.value) {
                  relationshipType.dispatchEvent(new Event('change'));
               }
               // Remove info message
               const existingInfo = document.querySelector('.filter-info');
               if(existingInfo) existingInfo.remove();
            }
         });
      }

      clientRelationshipType.addEventListener('change', function() {
         console.log(this.value);

         // Uncheck "show all" checkbox when relationship type changes
         const showAllCheckbox = document.getElementById('showAllEmployees');
         if(showAllCheckbox && showAllCheckbox.checked) {
            showAllCheckbox.checked = false;
            const existingInfo = document.querySelector('.filter-info');
            if(existingInfo) existingInfo.remove();
         }

         let selectedType = config['clientRelationshipTypes'].find(item => item.key === this.value);
         if(!selectedType) {
            console.warn('Relationship type not found');
            return;
         }

         let level = selectedType.level;
         console.log('Selected level:', level);

         let filteredEmployees = [];

         if(level == 1 || level == 2) {
            filteredEmployees = allEmployees.filter(employee =>
               employee.jobTitle === 'Partner' || employee.jobTitle === 'Director'
            );
         } else if(level == 3) {
            filteredEmployees = allEmployees.filter(employee =>
               employee.jobTitle === 'Manager' ||
               employee.jobTitle === 'Senior Manager' ||
               employee.jobTitle === 'Director'
            );
         } else if(level == 4) {
            filteredEmployees = allEmployees.filter(employee =>
               employee.jobTitle === 'Associate' ||
               employee.jobTitle === 'Senior Associate'
            );
         } else if(level == 5) {
            filteredEmployees = allEmployees.filter(employee =>
               employee.jobTitle === 'Intern' ||
               employee.jobTitle === 'Junior Associate'
            );
         } else if(level == 6) {
            filteredEmployees = allEmployees;
         }

         console.log('Filtered employees:', filteredEmployees.length);

         // FALLBACK: If no employees match the filter, show all employees
         if(filteredEmployees.length === 0) {
            console.warn('No employees found for level ' + level + '. Showing all employees as fallback.');
            filteredEmployees = allEmployees;

            // Show notification to user
            const employeeSelect = document.getElementById('employeeID');
            if(employeeSelect && employeeSelect.parentElement) {
               // Remove any existing warning
               const existingWarning = employeeSelect.parentElement.querySelector('.filter-warning');
               if(existingWarning) existingWarning.remove();

               // Add warning message
               const warning = document.createElement('small');
               warning.className = 'text-warning d-block mt-1 filter-warning';
               warning.innerHTML = '<i class="ri-alert-line me-1"></i>No employees found with required job title. Showing all employees.';
               employeeSelect.parentElement.appendChild(warning);
            }
         } else {
            // Remove warning if it exists
            const existingWarning = document.querySelector('.filter-warning');
            if(existingWarning) existingWarning.remove();
         }

         populateEmployeeOptions(employeeID, filteredEmployees);
      });
   });
</script>