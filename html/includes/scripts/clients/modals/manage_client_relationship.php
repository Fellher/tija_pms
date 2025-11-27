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
      <label for="employeeID">Employee</label>
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

      clientRelationshipType.addEventListener('change', function() {
         console.log(this.value);
         let level = config['clientRelationshipTypes'].find(item => item.key === this.value).level;
         console.log(level);
         if(level == 1 || level == 2 ) {
            let partnerEmployees = allEmployees.filter(employee => employee.jobTitle === 'Partner' || employee.jobTitle === 'Director');
            console.log(partnerEmployees);
            populateEmployeeOptions(employeeID, partnerEmployees);
         } else if(level == 3) {
            let managerEmployees = allEmployees.filter(employee => employee.jobTitle === 'Manager' || employee.jobTitle === 'Senior Manager' || employee.jobTitle === 'Director');
            console.log(managerEmployees);
            populateEmployeeOptions(employeeID, managerEmployees);
         } else if(level == 4 ) {
            let associateEmployees = allEmployees.filter(employee => employee.jobTitle === 'Associate' || employee.jobTitle === 'Senior Associate');
            console.log(associateEmployees);
            populateEmployeeOptions(employeeID, associateEmployees);
         } else if(level == 5) {
            let internEmployees = allEmployees.filter(employee => employee.jobTitle === 'Intern');
            console.log(internEmployees);
            populateEmployeeOptions(employeeID, internEmployees);
         } else if(level == 6) {
            populateEmployeeOptions(employeeID, allEmployees);
         }
      });
   });
</script>