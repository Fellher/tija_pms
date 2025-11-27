<!-- manage_project_task_assignments modal script -->
<div class="manage_project_task_assignments_form" id="manage_project_task_assignments_form">
   <div class="form-group d-none ">
      <label for="assigmentTaskID">Assigment Task ID</label>
      <input type="text" class="form-control form-control-sm form-control-palintext bg-light-blue" id="assigmentTaskID" name="assigmentTaskID" value="">   
      <label for="projectID">Project ID</label>
      <input type="text" class="form-control form-control-sm form-control-palintext bg-light-blue" id="projectID" name="projectID" value="">
 
      <label for="projectTaskID">Project Task ID</label>
      <input type="text" class="form-control form-control-sm form-control-palintext bg-light-blue" id="projectTaskID" name="projectTaskID" value="">
   </div>

 
   <div class="form-group">
      <label for="assigneeID">Assignee ID</label>
      <select class="form-control form-control-sm form-control-palintext bg-light-blue" id="assigneeID" name="assigneeID">
         <?= Form::populate_select_element_from_object($teamMembers, 'userID', 'teamMemberName', '' , '' , 'Select team Member') ?>
      </select>
   </div>

   <?php 
      $assignmentStatus = array(
      (object)["key"=>"pending", "value"=>"Pending"],
      (object)["key"=>"accepted", "value"=>"Accepted"],
      (object)["key"=>"rejected", "value"=>"Rejected"],
      (object)["key"=>"assigned", "value"=>"Assigned"],
      (object)["key"=>"edit-required", "value"=>"Edit Required"],
      (object)["key"=>"suspended", "value"=>"Suspended"],
      );
   ?>
   <div class="form-group">
     <label for="assignmentStatus">Assignment Status</label>
      <select class="form-control form-control-sm form-control-palintext bg-light-blue" id="assignmentStatus" name="assignmentStatus">
         <?= Form::populate_select_element_from_object($assignmentStatus, 'key', 'value', '' , '' , 'Select Assignment Status') ?>
      </select>
   </div>
   <div class="form-group d-none">
      <label for="suspended">Delete Assignment</label>
      <select class="form-control form-control-sm form-control-palintext bg-light-blue" id="suspended" name="suspended">
         <option value="">Delete Assignment</option>
         <option value="N">No</option>
         <option value="Y">Yes</option>
      </select>
   </div>
 

 <?php 
//  var_dump($projectData['phases'][0]['tasks']);
 ?>

 </div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const manageProjectTaskAssignments = document.getElementById('manage_project_task_assignments');
         const teamMembers  = <?= json_encode($teamMembers) ?>;
       //get the form
      const form = document.querySelector('#manage_project_task_assignments_form');
      if(!form) {
         console.log('Form not found');
         return;
      }
      console.log('Form found');
      const addAssigneeBtn = document.querySelectorAll('.addAssigneeBtn');
      addAssigneeBtn.forEach(btn => {
        btn.addEventListener('click', function() {
          const data = btn.dataset;
          console.log(data);

                  // Map form fields to their corresponding data attributes
                  const fieldMappings = {
                    'assigneeID': 'assigneeID',
                    'assignmentStatus': 'assignmentStatus',
                    'suspended': 'suspended',
                    'projectTaskID': 'projectTaskId',
                    'projectID': 'projectId',
                  };
                  for(const [fieldName, dataAttribute] of Object.entries(fieldMappings)) {
                    const input = form.querySelector(`[name="${fieldName}"]`);
                    if(input) {
                     console.log(input);
                      input.value = data[dataAttribute] || '';
                    }
                  }

                  //get the list of the current assignees
                  const assignees = data.assignees;
                  console.log(assignees);
                  //convert the assignees to an array
                  const assigneesArray = JSON.parse(assignees);
                  console.log(assigneesArray);
                  //populate the assignees select element
                  const assigneesSelect = form.querySelector('#assigneeID');
                  //populate the assignees select element with team members who are not in the assignees array
                  assigneesSelect.innerHTML = '';
                  //add the first blank option with the text "Select Assignee"
                  const option = document.createElement('option');
                  option.value = '';
                  option.textContent = 'Select Assignee';
                  assigneesSelect.appendChild(option);
                  teamMembers.forEach(member => {
                    if(!assigneesArray.includes(member.userID)) {
                      const option = document.createElement('option');
                      option.value = member.userID;
                      option.textContent = member.teamMemberName;
                      assigneesSelect.appendChild(option);
                    }
                  });
                  
        });
      });
    });
</script>
<!-- manage_project_task_assignments modal script -->