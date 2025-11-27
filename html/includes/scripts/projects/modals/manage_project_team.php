<div id="manage_project_team_form" class="manage_project_team_form">
   <div class="form-group d-none">
      <label for="projectID"> project ID</label>
      <input type="text" class="form-control" id="projectID" name="projectID" value="<?php echo $projectID; ?>" readonly>
   </div>
   <div class="form-group d-none">
      <label for="projectTeamMemberID"> Project Team Member ID</label>
      <input type="text" name="projectTeamMemberID" class="form-control"  value="" id="projectTeamMemberID" >
   </div>

   <div class="form-group">
      <label for="projectTeamRoleID" class="text-primary" >Project Team Role</label>
      <select class="form-control-sm form-control-plaintext border-bottom bg-light projectTeamRoleID" id="projectTeamRoleID" name="projectTeamRoleID">
         <option value=""> Select team member's role</option>
         <?php foreach ($projectTeamRoles as $role) { ?>
            <option value="<?php echo $role->projectTeamRoleID; ?>" ><?php echo htmlspecialchars($role->projectTeamRoleName); ?></option>
         <?php } ?>
         <option value="addRole">Add New Role</option>

      </select>
   </div>
   <div class="card card-body d-none newTeamRole" id="newTeamRole">
      <h5>Add New Role</h5>
      <div class="form-group">
         <label for="projectTeamRoleName">Project Team Role Name</label>
         <input type="text" class="form-control" id="projectTeamRoleName" name="projectTeamRoleName" placeholder="Enter Project Team Role Name">
      </div>
      <div class="form-group">
         <label for="projectTeamRoleDescription">Project Team Role Description</label>
         <textarea class="form-control borderless-mini" id="projectTeamRoleDescription" name="projectTeamRoleDescription" placeholder="Enter Project Team Role Description"></textarea>
      </div>
   </div>

   <div class="form-group">
      <label for="userID">Team Member</label>
      <select class="form-control-sm form-control-plaintext border-bottom bg-light" id="userID" name="userID">
         <?= Form::populate_select_element_from_object($employees, 'ID', 'employeeName', '', '', 'Select team member:') ?>
      </select>
   </div>

   <script>
   // ... existing script code ...
   document.addEventListener('DOMContentLoaded', function() {

      // // tomselect for userID
      // new TomSelect("#userID", {
      //    create: true,
      //    sortField: {
      //       field: "text",
      //       direction: "asc"
      //    }
      // });

      // New script to display newTeamRole when addRole is selected
      document.getElementById('projectTeamRoleID').addEventListener('change', function() {
         const newTeamRole = document.getElementById('newTeamRole');
         console.log(newTeamRole);
         if (this.value === 'addRole') {
            newTeamRole.classList.remove('d-none'); // Show newTeamRole
         } else {
            newTeamRole.classList.add('d-none'); // Hide newTeamRole
         }
      });
         console.log(`This part of the code is loaded`);

      document.querySelectorAll('.addProjectTeam').forEach(button => {
         button.addEventListener('click', function(){
            console.log('This is team')
            const form= document.getElementById('manage_project_team_form');
            if(!form) return;

            console.log(form);

            // get all data attributes from the button
            const data = this.dataset;
            console.log(data);

            // map form fields to their corresponding data attributes
            const fieldMappings = {
               'projectTeamMemberID': 'projectTeamMemberId',
               'projectID': 'projectId',
               'userID': 'userId',
               'projectTeamRoleID': 'projectTeamRoleId'
            };

            console.log(fieldMappings);

            // fill regular form inputs
            for (const [fieldName, dataAttribute] of Object.entries(fieldMappings)) {
               const input = form.querySelector(`[name="${fieldName}"]`);
               if(input) {
                  input.value = data[dataAttribute] || '';
               }
            }

            // fill select elements
            const selectElements = form.querySelectorAll('select');
            selectElements.forEach(select => {

               const option = select.querySelector(`option[value="${data[select.name]}"]`);
               if(option) {
                  option.selected = true;
               }
            });

         })
   });
   });

</script>



</div>